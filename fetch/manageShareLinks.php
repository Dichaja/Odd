<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

function out($data)
{
    echo json_encode($data);
    exit;
}

function q($sql, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function runSilent($sql, $params = [])
{
    try {
        q($sql, $params);
    } catch (Throwable $e) {
    }
}

function dbName()
{
    return q("SELECT DATABASE()")->fetchColumn();
}

function tableExists($table)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=:s AND table_name=:t", [':s' => dbName(), ':t' => $table])->fetchColumn() > 0;
}

function columnExists($table, $column)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=:s AND table_name=:t AND column_name=:c", [':s' => dbName(), ':t' => $table, ':c' => $column])->fetchColumn() > 0;
}

function indexExists($table, $index)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=:s AND table_name=:t AND index_name=:i", [':s' => dbName(), ':t' => $table, ':i' => $index])->fetchColumn() > 0;
}

function fkExists($name)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema=:s AND constraint_name=:n AND constraint_type='FOREIGN KEY'", [':s' => dbName(), ':n' => $name])->fetchColumn() > 0;
}

function ensureShareTables()
{
    q("CREATE TABLE IF NOT EXISTS share_links (
        id VARCHAR(26) PRIMARY KEY,
        code VARCHAR(16) NOT NULL UNIQUE,
        owner_user_id VARCHAR(26) NULL,
        target_type ENUM('product','store','home','category','search','custom','request-for-quote') NOT NULL,
        target_id VARCHAR(26) NULL,
        target_url TEXT NOT NULL,
        campaign VARCHAR(120) NULL,
        note VARCHAR(255) NULL,
        active TINYINT(1) DEFAULT 1,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_owner (owner_user_id),
        INDEX idx_target (target_type, target_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    q("CREATE TABLE IF NOT EXISTS share_link_clicks (
        id VARCHAR(26) PRIMARY KEY,
        share_link_id VARCHAR(26) NOT NULL,
        session_id VARCHAR(64) NULL,
        ip VARCHAR(45) NULL,
        user_agent TEXT NULL,
        referer TEXT NULL,
        utm_source VARCHAR(80) NULL,
        utm_medium VARCHAR(80) NULL,
        utm_campaign VARCHAR(120) NULL,
        utm_content VARCHAR(120) NULL,
        utm_term VARCHAR(120) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_clicks_link (share_link_id),
        INDEX idx_clicks_session (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    if (!fkExists('fk_share_clicks_link')) {
        runSilent("ALTER TABLE share_link_clicks ADD CONSTRAINT fk_share_clicks_link FOREIGN KEY (share_link_id) REFERENCES share_links(id) ON UPDATE CASCADE ON DELETE CASCADE");
    }
}

function ensureShareColumnsAndFKs()
{
    $defs = [
        ['t' => 'product_views', 'fk' => 'fk_pv_sl'],
        ['t' => 'store_profile_views', 'fk' => 'fk_spv_sl'],
        ['t' => 'store_contact_views', 'fk' => 'fk_scv_sl'],
        ['t' => 'product_price_views', 'fk' => 'fk_ppv_sl'],
        ['t' => 'request_for_quote', 'fk' => 'fk_rfq_sl'],
        ['t' => 'buy_in_store_requests', 'fk' => 'fk_bis_sl'],
        ['t' => 'pesapal_payments', 'fk' => 'fk_pay_sl'],
    ];
    foreach ($defs as $d) {
        $t = $d['t'];
        if (!tableExists($t))
            continue;
        if (!columnExists($t, 'share_link_id')) {
            runSilent("ALTER TABLE `$t` ADD COLUMN share_link_id VARCHAR(26) NULL");
        }
        $idx = "idx_{$t}_share_link_id";
        if (!indexExists($t, $idx)) {
            runSilent("CREATE INDEX $idx ON `$t` (share_link_id)");
        }
        if (!fkExists($d['fk'])) {
            runSilent("ALTER TABLE `$t` ADD CONSTRAINT {$d['fk']} FOREIGN KEY (share_link_id) REFERENCES share_links(id) ON UPDATE CASCADE ON DELETE SET NULL");
        }
    }
}

function ensureMigrations()
{
    ensureShareTables();
    ensureShareColumnsAndFKs();
}

function resolveTargetUrl(string $type, ?string $id, ?string $url): ?string
{
    if ($url && trim($url) !== '')
        return normalizeUrl($url);
    $base = rtrim(BASE_URL, '/');
    if ($type === 'home')
        return $base . '/';
    if ($type === 'product' && $id)
        return $base . '/view/product/' . rawurlencode($id);
    if ($type === 'store' && $id)
        return $base . '/view/profile/vendor/' . rawurlencode($id);
    if ($type === 'category' && $id)
        return $base . '/view/category/' . rawurlencode($id);
    if ($type === 'search' && $url)
        return normalizeUrl($url);
    if ($type === 'custom' && $url)
        return normalizeUrl($url);
    if ($type === 'request-for-quote')
        return $base . '/request-for-quote';
    return null;
}

function normalizeUrl(string $u): string
{
    $u = trim($u);
    if ($u === '')
        return rtrim(BASE_URL, '/') . '/';
    if (preg_match('~^https?://~i', $u))
        return $u;
    if ($u[0] === '/')
        return rtrim(BASE_URL, '/') . $u;
    return rtrim(BASE_URL, '/') . '/' . ltrim($u, '/');
}

function generateCode(): string
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $len = 8;
    $buf = '';
    for ($i = 0; $i < $len; $i++)
        $buf .= $chars[random_int(0, strlen($chars) - 1)];
    return $buf;
}

ensureMigrations();

$action = $_REQUEST['action'] ?? '';
$session = $_SESSION['user'] ?? null;
$loggedIn = is_array($session) && !empty($session['logged_in']);
$isAdmin = $loggedIn && !empty($session['is_admin']);
$userId = $loggedIn ? ($session['user_id'] ?? null) : null;

if ($action === 'create') {
    $targetType = $_POST['target_type'] ?? '';
    $targetId = $_POST['target_id'] ?? null;
    $targetUrl = $_POST['target_url'] ?? null;
    $campaign = $_POST['campaign'] ?? null;
    $note = $_POST['note'] ?? null;
    $expiresAt = $_POST['expires_at'] ?? null;
    $currentUrl = $_POST['current_url'] ?? null;

    if (!$loggedIn || $isAdmin) {
        $use = $currentUrl ? normalizeUrl($currentUrl) : resolveTargetUrl($targetType, $targetId, $targetUrl);
        if (!$use)
            out(['success' => false, 'message' => 'target_url required']);
        out(['success' => true, 'data' => ['id' => null, 'code' => null, 'short_url' => $use, 'target_url' => $use]]);
    }

    $allowed = ['product', 'store', 'home', 'category', 'search', 'custom', 'request-for-quote'];
    if (!in_array($targetType, $allowed, true))
        out(['success' => false, 'message' => 'invalid target_type']);

    $resolved = resolveTargetUrl($targetType, $targetId, $targetUrl);
    if (!$resolved)
        out(['success' => false, 'message' => 'target_url required']);

    $existing = q("SELECT id, code, target_url FROM share_links WHERE owner_user_id=:uid AND target_type=:tt AND target_id <=> :tid ORDER BY created_at DESC LIMIT 1", [
        ':uid' => $userId,
        ':tt' => $targetType,
        ':tid' => $targetId
    ])->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $short = rtrim(BASE_URL, '/') . '/r/' . $existing['code'];
        out(['success' => true, 'data' => ['id' => $existing['id'], 'code' => $existing['code'], 'short_url' => $short, 'target_url' => $existing['target_url']]]);
    }

    do {
        $code = generateCode();
        $dup = q("SELECT 1 FROM share_links WHERE code=:c LIMIT 1", [':c' => $code])->fetchColumn();
    } while ($dup);

    $id = generateUlid();
    q("INSERT INTO share_links (id, code, owner_user_id, target_type, target_id, target_url, campaign, note, active, expires_at) VALUES (:id,:code,:uid,:tt,:tid,:turl,:camp,:note,1,:exp)", [
        ':id' => $id,
        ':code' => $code,
        ':uid' => $userId,
        ':tt' => $targetType,
        ':tid' => $targetId,
        ':turl' => $resolved,
        ':camp' => $campaign,
        ':note' => $note,
        ':exp' => $expiresAt ?: null
    ]);

    $short = rtrim(BASE_URL, '/') . '/r/' . $code;
    out(['success' => true, 'data' => ['id' => $id, 'code' => $code, 'short_url' => $short, 'target_url' => $resolved]]);
}

if ($action === 'list') {
    $limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    if (!$loggedIn)
        out(['success' => true, 'data' => []]);
    $stmt = $pdo->prepare("SELECT id, code, owner_user_id, target_type, target_id, target_url, campaign, note, active, expires_at, created_at FROM share_links WHERE owner_user_id=:uid ORDER BY created_at DESC LIMIT :off,:lim");
    $stmt->bindValue(':uid', $userId);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $base = rtrim(BASE_URL, '/');
    foreach ($rows as &$r)
        $r['short_url'] = $base . '/r/' . $r['code'];
    out(['success' => true, 'data' => $rows]);
}

out(['success' => false, 'message' => 'unknown action']);
