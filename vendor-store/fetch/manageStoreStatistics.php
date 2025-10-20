<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
try {
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'session_expired' => true]);
        exit;
    }
    $action = strtolower(trim($_GET['action'] ?? ''));
    $storeId = $_SESSION['active_store'] ?? null;
    if (!$storeId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing store']);
        exit;
    }
    $userId = $_SESSION['user']['user_id'] ?? null;
    $isAdmin = !empty($_SESSION['user']['is_admin']);
    if (!canAccessStore($pdo, $storeId, $userId, $isAdmin)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }
    $from = trim($_GET['from'] ?? '');
    $to = trim($_GET['to'] ?? '');
    $granularity = strtolower(trim($_GET['granularity'] ?? 'daily'));
    $fromDt = $from !== '' ? normalizeDt($from, true) : null;
    $toDt = $to !== '' ? normalizeDt($to, false) : null;

    if ($action === 'list_profile_views') {
        $rows = fetchProfileViews($pdo, $storeId, $fromDt, $toDt);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }
    if ($action === 'list_price_views') {
        $rows = fetchPriceViews($pdo, $storeId, $fromDt, $toDt);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }
    if ($action === 'list_contact_views') {
        $rows = fetchContactViews($pdo, $storeId, $fromDt, $toDt);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }
    if ($action === 'series') {
        if (!$fromDt || !$toDt) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing date range']);
            exit;
        }
        $series = buildSeries($pdo, $storeId, $fromDt, $toDt, $granularity);
        echo json_encode(['success' => true, 'data' => $series]);
        exit;
    }
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
} catch (Throwable $e) {
    error_log('manageStoreStatistics: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
function canAccessStore(PDO $pdo, string $storeId, ?string $userId, bool $isAdmin): bool
{
    if ($isAdmin)
        return true;
    if (!$userId)
        return false;
    $q1 = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = :sid AND owner_id = :uid LIMIT 1");
    $q1->execute([':sid' => $storeId, ':uid' => $userId]);
    if ($q1->fetchColumn())
        return true;
    $q2 = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = :sid AND user_id = :uid AND status = 'active' AND approved = 1 LIMIT 1");
    $q2->execute([':sid' => $storeId, ':uid' => $userId]);
    return (bool) $q2->fetchColumn();
}
function normalizeDt(string $s, bool $start): string
{
    $s = preg_replace('/[T]/', ' ', $s);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
        return $start ? ($s . ' 00:00:00') : ($s . ' 23:59:59');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $s)) {
        return $s;
    }
    return date('Y-m-d H:i:s', time());
}
function fetchProfileViews(PDO $pdo, string $storeId, ?string $fromDt, ?string $toDt): array
{
    $sql = "
        SELECT
            u.username,
            TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))) AS full_name,
            DATE_FORMAT(spv.created_at, '%d/%b/%Y') AS date_f,
            DATE_FORMAT(spv.created_at, '%h:%i %p') AS time_f
        FROM store_profile_views spv
        LEFT JOIN zzimba_users u ON u.id = spv.user_id
        WHERE spv.store_id = :sid
    ";
    $params = [':sid' => $storeId];
    if ($fromDt && $toDt) {
        $sql .= " AND spv.created_at BETWEEN :from AND :to";
        $params[':from'] = $fromDt;
        $params[':to'] = $toDt;
    }
    $sql .= " ORDER BY spv.created_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $out = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $name = trim((string) $r['full_name']);
        $out[] = [
            'username' => $r['username'] ?: null,
            'full_name' => $name !== '' ? $name : null,
            'date' => $r['date_f'],
            'time' => $r['time_f']
        ];
    }
    return $out;
}
function fetchPriceViews(PDO $pdo, string $storeId, ?string $fromDt, ?string $toDt): array
{
    $sql = "
        SELECT
            p.title AS product_title,
            pp.price,
            pp.price_category,
            pp.package_size,
            si.si_unit,
            pn.package_name,
            u.username,
            TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))) AS full_name,
            DATE_FORMAT(ppv.created_at, '%d/%b/%Y') AS date_f,
            DATE_FORMAT(ppv.created_at, '%h:%i %p') AS time_f
        FROM product_price_views ppv
        JOIN product_pricing pp ON pp.id = ppv.pricing_id
        JOIN store_products sp ON sp.id = pp.store_products_id
        JOIN store_categories sc ON sc.id = sp.store_category_id
        JOIN products p ON p.id = sp.product_id
        LEFT JOIN product_package_name_mappings pm ON pm.id = pp.package_mapping_id
        LEFT JOIN product_package_name pn ON pn.id = pm.product_package_name_id
        LEFT JOIN product_si_units si ON si.id = pp.si_unit_id
        LEFT JOIN zzimba_users u ON u.id = ppv.user_id
        WHERE sc.store_id = :sid
    ";
    $params = [':sid' => $storeId];
    if ($fromDt && $toDt) {
        $sql .= " AND ppv.created_at BETWEEN :from AND :to";
        $params[':from'] = $fromDt;
        $params[':to'] = $toDt;
    }
    $sql .= " ORDER BY ppv.created_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $out = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $name = trim((string) $r['full_name']);
        $pkgText = buildPackageText($r['package_size'] ?? null, $r['si_unit'] ?? null, $r['package_name'] ?? null);
        $pkgHtml = buildPackageHtml($r['package_size'] ?? null, $r['si_unit'] ?? null, $r['package_name'] ?? null);
        $out[] = [
            'product_title' => $r['product_title'],
            'package' => $pkgText,
            'package_html' => $pkgHtml,
            'price' => $r['price'],
            'price_category' => $r['price_category'],
            'username' => $r['username'] ?: null,
            'full_name' => $name !== '' ? $name : null,
            'date' => $r['date_f'],
            'time' => $r['time_f']
        ];
    }
    return $out;
}
function fetchContactViews(PDO $pdo, string $storeId, ?string $fromDt, ?string $toDt): array
{
    $sql = "
        SELECT
            scv.entity,
            u.username,
            TRIM(CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,''))) AS full_name,
            DATE_FORMAT(scv.created_at, '%d/%b/%Y') AS date_f,
            DATE_FORMAT(scv.created_at, '%h:%i %p') AS time_f
        FROM store_contact_views scv
        LEFT JOIN zzimba_users u ON u.id = scv.user_id
        WHERE scv.store_id = :sid
    ";
    $params = [':sid' => $storeId];
    if ($fromDt && $toDt) {
        $sql .= " AND scv.created_at BETWEEN :from AND :to";
        $params[':from'] = $fromDt;
        $params[':to'] = $toDt;
    }
    $sql .= " ORDER BY scv.created_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $out = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $name = trim((string) $r['full_name']);
        $entity = $r['entity'] ? ($r['entity'] === 'contact' ? 'Phone' : ucfirst($r['entity'])) : null;
        $out[] = [
            'entity' => $entity,
            'username' => $r['username'] ?: null,
            'full_name' => $name !== '' ? $name : null,
            'date' => $r['date_f'],
            'time' => $r['time_f']
        ];
    }
    return $out;
}
function buildSeries(PDO $pdo, string $storeId, string $fromDt, string $toDt, string $gran): array
{
    $labels = labelsForGranularity($gran, $fromDt);
    $profile = array_fill(0, count($labels), 0);
    $price = array_fill(0, count($labels), 0);
    $contact = array_fill(0, count($labels), 0);

    $bucketExprProfile = bucketExpr($gran, 'spv.created_at');
    $qProfile = "
        SELECT {$bucketExprProfile} AS b, COUNT(*) AS c
        FROM store_profile_views spv
        WHERE spv.store_id = :sid
          AND spv.created_at BETWEEN :from AND :to
        GROUP BY b
        ORDER BY b
    ";
    $st = $pdo->prepare($qProfile);
    $st->execute([':sid' => $storeId, ':from' => $fromDt, ':to' => $toDt]);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $idx = bucketIndex((int) $r['b'], $gran, $fromDt);
        if ($idx !== null && isset($profile[$idx]))
            $profile[$idx] = (int) $r['c'];
    }

    $bucketExprPrice = bucketExpr($gran, 'ppv.created_at');
    $qPrice = "
        SELECT {$bucketExprPrice} AS b, COUNT(*) AS c
        FROM product_price_views ppv
        JOIN product_pricing pp ON pp.id = ppv.pricing_id
        JOIN store_products sp ON sp.id = pp.store_products_id
        JOIN store_categories sc ON sc.id = sp.store_category_id
        WHERE sc.store_id = :sid
          AND ppv.created_at BETWEEN :from AND :to
        GROUP BY b
        ORDER BY b
    ";
    $st = $pdo->prepare($qPrice);
    $st->execute([':sid' => $storeId, ':from' => $fromDt, ':to' => $toDt]);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $idx = bucketIndex((int) $r['b'], $gran, $fromDt);
        if ($idx !== null && isset($price[$idx]))
            $price[$idx] = (int) $r['c'];
    }

    $bucketExprContact = bucketExpr($gran, 'scv.created_at');
    $qContact = "
        SELECT {$bucketExprContact} AS b, COUNT(*) AS c
        FROM store_contact_views scv
        WHERE scv.store_id = :sid
          AND scv.created_at BETWEEN :from AND :to
        GROUP BY b
        ORDER BY b
    ";
    $st = $pdo->prepare($qContact);
    $st->execute([':sid' => $storeId, ':from' => $fromDt, ':to' => $toDt]);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $idx = bucketIndex((int) $r['b'], $gran, $fromDt);
        if ($idx !== null && isset($contact[$idx]))
            $contact[$idx] = (int) $r['c'];
    }

    $bd = ['location' => 0, 'contact' => 0, 'email' => 0];
    $qb = "
        SELECT scv.entity, COUNT(*) AS c
        FROM store_contact_views scv
        WHERE scv.store_id = :sid
          AND scv.created_at BETWEEN :from AND :to
        GROUP BY scv.entity
    ";
    $st = $pdo->prepare($qb);
    $st->execute([':sid' => $storeId, ':from' => $fromDt, ':to' => $toDt]);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $key = $r['entity'];
        if (isset($bd[$key]))
            $bd[$key] = (int) $r['c'];
    }

    return [
        'labels' => $labels,
        'profile' => $profile,
        'price' => $price,
        'contact' => $contact,
        'contact_breakdown' => $bd
    ];
}
function labelsForGranularity(string $gran, string $fromDt): array
{
    if ($gran === 'daily') {
        return ['12am', '2am', '4am', '6am', '8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm', '10pm'];
    }
    if ($gran === 'weekly') {
        return ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    }
    if ($gran === 'monthly') {
        $dt = new DateTime($fromDt);
        $days = (int) $dt->format('t');
        $labels = [];
        for ($i = 1; $i <= $days; $i++)
            $labels[] = (string) $i;
        return $labels;
    }
    if ($gran === 'yearly') {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }
    return [];
}
function bucketExpr(string $gran, string $col): string
{
    if ($gran === 'daily') {
        return "FLOOR(HOUR($col)/2)";
    }
    if ($gran === 'weekly') {
        return "DAYOFWEEK($col)";
    }
    if ($gran === 'monthly') {
        return "DAYOFMONTH($col)";
    }
    if ($gran === 'yearly') {
        return "MONTH($col)";
    }
    return "0";
}
function bucketIndex(int $bucket, string $gran, string $fromDt): ?int
{
    if ($gran === 'daily') {
        if ($bucket < 0 || $bucket > 11)
            return null;
        return $bucket;
    }
    if ($gran === 'weekly') {
        $i = $bucket - 1;
        if ($i < 0 || $i > 6)
            return null;
        return $i;
    }
    if ($gran === 'monthly') {
        $dt = new DateTime($fromDt);
        $days = (int) $dt->format('t');
        if ($bucket < 1 || $bucket > $days)
            return null;
        return $bucket - 1;
    }
    if ($gran === 'yearly') {
        if ($bucket < 1 || $bucket > 12)
            return null;
        return $bucket - 1;
    }
    return null;
}
function buildPackageText(?string $size, ?string $unit, ?string $pname): ?string
{
    $parts = [];
    if ($size !== null && $size !== '')
        $parts[] = trim($size);
    if ($unit)
        $parts[] = $unit;
    if ($pname)
        $parts[] = $pname;
    return count($parts) ? implode(' ', $parts) : null;
}
function buildPackageHtml(?string $size, ?string $unit, ?string $pname): ?string
{
    $sizeHtml = null;
    if ($size !== null && $size !== '') {
        $s = trim($size);
        if (preg_match('/^(\d+)\s+(\d+)\s*\/\s*(\d+)$/', $s, $m)) {
            $sizeHtml = '<span class="compound"><span class="whole">' . $m[1] . '</span><span class="frac"><sup>' . $m[2] . '</sup>/<sub>' . $m[3] . '</sub></span></span>';
        } elseif (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $s, $m)) {
            $sizeHtml = '<span class="compound"><span class="frac"><sup>' . $m[1] . '</sup>/<sub>' . $m[2] . '</sub></span></span>';
        } else {
            $sizeHtml = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        }
    }
    $parts = [];
    if ($sizeHtml !== null)
        $parts[] = $sizeHtml;
    if ($unit)
        $parts[] = htmlspecialchars($unit, ENT_QUOTES, 'UTF-8');
    if ($pname)
        $parts[] = htmlspecialchars($pname, ENT_QUOTES, 'UTF-8');
    return count($parts) ? implode(' ', $parts) : null;
}
