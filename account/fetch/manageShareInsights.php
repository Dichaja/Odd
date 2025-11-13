<?php
require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in']) || !isset($_SESSION['user']['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}
$userId = $_SESSION['user']['user_id'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input['action'])) {
    $action = $input['action'];
}

$requestCache = [];
$pendingRequests = [];

function jsonOut($data)
{
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function dtStart($s)
{
    return $s ? $s . ' 00:00:00' : null;
}

function dtEnd($s)
{
    return $s ? $s . ' 23:59:59' : null;
}

function getTargetName($pdo, $targetType, $targetId)
{
    if (!$targetId)
        return null;

    if ($targetType === 'product') {
        $stmt = $pdo->prepare("SELECT title FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $targetId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['title'] : null;
    } elseif ($targetType === 'category') {
        $stmt = $pdo->prepare("SELECT name FROM product_categories WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $targetId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['name'] : null;
    } elseif ($targetType === 'store') {
        $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $targetId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['name'] : null;
    }
    return null;
}

function dateRange()
{
    $sd = $_GET['start_date'] ?? null;
    $ed = $_GET['end_date'] ?? null;
    if (!$sd || !$ed) {
        $end = new DateTime('today');
        $start = (new DateTime('today'))->modify('-29 days');
        $sd = $start->format('Y-m-d');
        $ed = $end->format('Y-m-d');
    }
    return [dtStart($sd), dtEnd($ed)];
}

function whereClause(&$params)
{
    $w = " l.owner_user_id = :u ";
    $params[':u'] = $params[':u'] ?? null;
    $linkId = $_GET['link_id'] ?? null;
    if ($linkId) {
        $w .= " AND l.id = :lid ";
        $params[':lid'] = $linkId;
    }
    $sd = $_GET['start_date'] ?? null;
    $ed = $_GET['end_date'] ?? null;
    if ($sd && $ed) {
        $w .= " AND c.created_at BETWEEN :sd AND :ed ";
        $params[':sd'] = dtStart($sd);
        $params[':ed'] = dtEnd($ed);
    }
    return $w;
}

if ($action === 'filters') {
    $stmt = $pdo->prepare("SELECT DISTINCT COALESCE(campaign, '') AS c FROM share_links WHERE owner_user_id = :u ORDER BY c ASC");
    $stmt->execute([':u' => $userId]);
    $campaigns = array_values(array_map(fn($r) => $r['c'], $stmt->fetchAll(PDO::FETCH_ASSOC)));
    $s2 = $pdo->prepare("SELECT id, code, target_type, campaign, target_url, active FROM share_links WHERE owner_user_id = :u ORDER BY created_at DESC LIMIT 200");
    $s2->execute([':u' => $userId]);
    $links = $s2->fetchAll(PDO::FETCH_ASSOC);
    jsonOut(['success' => true, 'campaigns' => $campaigns, 'links' => $links]);
}

if ($action === 'overview') {
    [$sd, $ed] = dateRange();
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $q1 = $pdo->prepare("SELECT COUNT(*) total_links, SUM(active = 1 AND (expires_at IS NULL OR expires_at > NOW())) active_links, SUM(expires_at IS NOT NULL AND expires_at <= NOW()) expired_links FROM share_links WHERE owner_user_id = :u");
    $q1->execute([':u' => $userId]);
    $links = $q1->fetch(PDO::FETCH_ASSOC) ?: ['total_links' => 0, 'active_links' => 0, 'expired_links' => 0];
    $q2 = $pdo->prepare("SELECT COUNT(*) clicks, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE l.owner_user_id = :u AND c.created_at BETWEEN :sd AND :ed");
    $q2->execute($params);
    $r2 = $q2->fetch(PDO::FETCH_ASSOC) ?: ['clicks' => 0, 'uniques' => 0];
    $prevStart = (new DateTime(substr($sd, 0, 10)))->modify('-' . (1 + ((new DateTime(substr($ed, 0, 10)))->diff(new DateTime(substr($sd, 0, 10)))->days)) . ' days')->format('Y-m-d') . ' 00:00:00';
    $prevEnd = (new DateTime(substr($sd, 0, 10)))->modify('-1 day')->format('Y-m-d') . ' 23:59:59';
    $q3 = $pdo->prepare("SELECT COUNT(*) clicks, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE l.owner_user_id = :u AND c.created_at BETWEEN :ps AND :pe");
    $q3->execute([':u' => $userId, ':ps' => $prevStart, ':pe' => $prevEnd]);
    $r3 = $q3->fetch(PDO::FETCH_ASSOC) ?: ['clicks' => 0, 'uniques' => 0];
    $q4 = $pdo->prepare("SELECT country, country_code, SUM(cnt) t FROM (SELECT country, country_code, COUNT(*) cnt FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE l.owner_user_id = :u AND c.created_at BETWEEN :sd AND :ed GROUP BY country, country_code) x ORDER BY t DESC LIMIT 1");
    $q4->execute($params);
    $g = $q4->fetch(PDO::FETCH_ASSOC) ?: ['country' => null, 'country_code' => null, 't' => 0];
    $q5 = $pdo->prepare("SELECT COALESCE(utm_source, '') s, COALESCE(utm_medium, '') m, COUNT(*) t FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE l.owner_user_id = :u AND c.created_at BETWEEN :sd AND :ed GROUP BY s, m ORDER BY t DESC LIMIT 1");
    $q5->execute($params);
    $u = $q5->fetch(PDO::FETCH_ASSOC) ?: ['s' => null, 'm' => null, 't' => 0];
    jsonOut([
        'success' => true,
        'data' => [
            'clicks' => intval($r2['clicks']),
            'unique_clicks' => intval($r2['uniques']),
            'clicks_delta' => intval($r2['clicks']) - intval($r3['clicks']),
            'unique_delta' => intval($r2['uniques']) - intval($r3['uniques']),
            'total_links' => intval($links['total_links']),
            'active_links' => intval($links['active_links']),
            'expired_links' => intval($links['expired_links']),
            'top_country' => $g['country'],
            'top_country_code' => $g['country_code'],
            'top_source' => $u['s'],
            'top_medium' => $u['m']
        ]
    ]);
}

if ($action === 'timeseries') {
    [$sd, $ed] = dateRange();
    $period = strtolower($_GET['period'] ?? 'monthly');
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT DATE(c.created_at) d, COUNT(*) total FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE l.owner_user_id = :u AND c.created_at BETWEEN :sd AND :ed GROUP BY d ORDER BY d ASC");
    $stmt->execute($params);
    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $map[$r['d']] = intval($r['total']);
    }
    $labels = [];
    $data = [];
    $startDate = new DateTime(substr($sd, 0, 10));
    $endDate = new DateTime(substr($ed, 0, 10));

    if ($period === 'daily') {
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $cur = clone $startDate;
        while ($cur <= $endDate) {
            $k = $cur->format('Y-m-d');
            $dayNum = $cur->format('N') - 1;
            $labels[] = $dayNames[$dayNum];
            $data[] = $map[$k] ?? 0;
            $cur->modify('+1 day');
        }
    } elseif ($period === 'weekly') {
        $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $cur = clone $startDate;
        while ($cur <= $endDate) {
            $k = $cur->format('Y-m-d');
            $dayNum = $cur->format('N') - 1;
            $labels[] = $dayNames[$dayNum];
            $data[] = $map[$k] ?? 0;
            $cur->modify('+1 day');
        }
    } elseif ($period === 'monthly') {
        $cur = clone $startDate;
        $lastDay = intval($endDate->format('t'));
        for ($d = 1; $d <= $lastDay; $d++) {
            $dateStr = $endDate->format('Y-m') . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
            $labels[] = (string) $d;
            $data[] = $map[$dateStr] ?? 0;
        }
    } elseif ($period === 'yearly') {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearData = array_fill(0, 12, 0);
        foreach ($map as $date => $count) {
            $month = intval(substr($date, 5, 2)) - 1;
            $yearData[$month] += $count;
        }
        $labels = $months;
        $data = $yearData;
    }

    jsonOut(['success' => true, 'labels' => $labels, 'data' => $data]);
}

if ($action === 'top_links') {
    [$sd, $ed] = dateRange();
    $limit = max(1, intval($_GET['limit'] ?? 10));
    $cursor = $_GET['cursor'] ?? null;
    $search = trim($_GET['search'] ?? '');
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $extra = "";
    if ($cursor) {
        $extra .= " AND l.created_at < :cursor ";
        $params[':cursor'] = $cursor;
    }
    if ($search !== '') {
        $extra .= " AND (l.code LIKE :q OR l.note LIKE :q) ";
        $params[':q'] = '%' . $search . '%';
    }
    $stmt = $pdo->prepare("SELECT l.id, l.code, l.target_type, l.target_id, l.campaign, l.note, l.target_url, l.active, COUNT(c.id) clicks, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques, MAX(c.created_at) last_click FROM share_links l LEFT JOIN share_link_clicks c ON c.share_link_id = l.id AND c.created_at BETWEEN :sd AND :ed WHERE l.owner_user_id = :u $extra GROUP BY l.id ORDER BY clicks DESC, l.created_at DESC LIMIT " . ($limit + 1));
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasMore = count($rows) > $limit;
    if ($hasMore) {
        array_pop($rows);
    }
    $maxClicks = 0;
    foreach ($rows as $r) {
        $maxClicks = max($maxClicks, intval($r['clicks']));
    }
    $out = [];
    foreach ($rows as $r) {
        $share = $maxClicks > 0 ? round((intval($r['clicks']) / $maxClicks) * 100, 2) : 0;
        $targetName = getTargetName($pdo, $r['target_type'], $r['target_id']);
        $out[] = [
            'id' => $r['id'],
            'code' => $r['code'],
            'target_type' => $r['target_type'],
            'target_name' => $targetName,
            'campaign' => $r['campaign'],
            'note' => $r['note'],
            'target_url' => $r['target_url'],
            'active' => intval($r['active']) === 1,
            'clicks' => intval($r['clicks']),
            'uniques' => intval($r['uniques']),
            'last_click' => $r['last_click'],
            'share' => $share
        ];
    }
    $next = null;
    if ($hasMore && count($rows) > 0) {
        $next = $rows[count($rows) - 1]['created_at'];
    }
    jsonOut(['success' => true, 'rows' => $out, 'next_cursor' => $next]);
}

if ($action === 'utm_breakdown') {
    [$sd, $ed] = dateRange();
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT COALESCE(utm_source, '') k, COUNT(*) total, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE " . whereClause($params) . " GROUP BY k ORDER BY total DESC LIMIT 15");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mx = 0;
    foreach ($rows as $r) {
        $mx = max($mx, intval($r['total']));
    }
    $out = array_map(function ($r) use ($mx) {
        return ['k' => $r['k'], 'total' => intval($r['total']), 'uniques' => intval($r['uniques']), 'share' => $mx > 0 ? round((intval($r['total']) / $mx) * 100, 2) : 0];
    }, $rows);
    jsonOut(['success' => true, 'rows' => $out]);
}

if ($action === 'geo_breakdown') {
    [$sd, $ed] = dateRange();
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT COALESCE(country, '') k, COUNT(*) total, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE " . whereClause($params) . " GROUP BY k ORDER BY total DESC LIMIT 15");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mx = 0;
    foreach ($rows as $r) {
        $mx = max($mx, intval($r['total']));
    }
    $out = array_map(function ($r) use ($mx) {
        return ['k' => $r['k'], 'total' => intval($r['total']), 'uniques' => intval($r['uniques']), 'share' => $mx > 0 ? round((intval($r['total']) / $mx) * 100, 2) : 0];
    }, $rows);
    jsonOut(['success' => true, 'rows' => $out]);
}

if ($action === 'device_breakdown') {
    [$sd, $ed] = dateRange();
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT CONCAT(COALESCE(device, ''), ' • ', COALESCE(browser, '')) k, COUNT(*) total, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE " . whereClause($params) . " GROUP BY k ORDER BY total DESC LIMIT 15");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mx = 0;
    foreach ($rows as $r) {
        $mx = max($mx, intval($r['total']));
    }
    $out = array_map(function ($r) use ($mx) {
        return ['k' => $r['k'], 'total' => intval($r['total']), 'uniques' => intval($r['uniques']), 'share' => $mx > 0 ? round((intval($r['total']) / $mx) * 100, 2) : 0];
    }, $rows);
    jsonOut(['success' => true, 'rows' => $out]);
}

if ($action === 'referrers') {
    [$sd, $ed] = dateRange();
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(referer, ''), 'direct') k, COUNT(*) total, COUNT(DISTINCT COALESCE(NULLIF(c.session_id, ''), CONCAT('id-', c.id))) uniques FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE " . whereClause($params) . " GROUP BY k ORDER BY total DESC LIMIT 15");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mx = 0;
    foreach ($rows as $r) {
        $mx = max($mx, intval($r['total']));
    }
    $out = array_map(function ($r) use ($mx) {
        return ['k' => $r['k'], 'total' => intval($r['total']), 'uniques' => intval($r['uniques']), 'share' => $mx > 0 ? round((intval($r['total']) / $mx) * 100, 2) : 0];
    }, $rows);
    jsonOut(['success' => true, 'rows' => $out]);
}

if ($action === 'click_stream') {
    [$sd, $ed] = dateRange();
    $limit = max(1, intval($_GET['limit'] ?? 100));
    $params = [':u' => $userId, ':sd' => $sd, ':ed' => $ed];
    $stmt = $pdo->prepare("SELECT c.id, c.created_at, c.country, c.country_code, c.browser, c.device, COALESCE(NULLIF(c.referer, ''), 'direct') referer, l.code, l.target_type FROM share_link_clicks c JOIN share_links l ON c.share_link_id = l.id WHERE " . whereClause($params) . " ORDER BY c.created_at DESC LIMIT " . $limit);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    jsonOut(['success' => true, 'rows' => $rows]);
}

if ($action === 'link_details') {
    [$sd, $ed] = dateRange();
    $linkId = $_GET['link_id'] ?? null;
    if (!$linkId) {
        jsonOut(['success' => false, 'message' => 'Missing link']);
    }
    $s = $pdo->prepare("SELECT id, code, target_type, campaign, note, target_url, active, created_at, expires_at FROM share_links WHERE id = :id AND owner_user_id = :u LIMIT 1");
    $s->execute([':id' => $linkId, ':u' => $userId]);
    $link = $s->fetch(PDO::FETCH_ASSOC);
    if (!$link) {
        jsonOut(['success' => false, 'message' => 'Not found']);
    }
    $m = $pdo->prepare("SELECT COUNT(*) clicks, COUNT(DISTINCT COALESCE(NULLIF(session_id, ''), CONCAT('id-', id))) uniques FROM share_link_clicks WHERE share_link_id = :id AND created_at BETWEEN :sd AND :ed");
    $m->execute([':id' => $linkId, ':sd' => $sd, ':ed' => $ed]);
    $metrics = $m->fetch(PDO::FETCH_ASSOC);
    $t = $pdo->prepare("SELECT DATE(created_at) d, COUNT(*) total FROM share_link_clicks WHERE share_link_id = :id AND created_at BETWEEN :sd AND :ed GROUP BY d ORDER BY d ASC");
    $t->execute([':id' => $linkId, ':sd' => $sd, ':ed' => $ed]);
    $map = [];
    foreach ($t->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $map[$r['d']] = intval($r['total']);
    }
    $days = [];
    $cur = new DateTime(substr($sd, 0, 10));
    $end = new DateTime(substr($ed, 0, 10));
    while ($cur <= $end) {
        $k = $cur->format('Y-m-d');
        $days[] = ['date' => $k, 'total' => $map[$k] ?? 0];
        $cur->modify('+1 day');
    }
    $g = $pdo->prepare("SELECT COALESCE(country, '') k, COUNT(*) total FROM share_link_clicks WHERE share_link_id = :id AND created_at BETWEEN :sd AND :ed GROUP BY k ORDER BY total DESC LIMIT 8");
    $g->execute([':id' => $linkId, ':sd' => $sd, ':ed' => $ed]);
    $geo = $g->fetchAll(PDO::FETCH_ASSOC);
    $mx = 0;
    foreach ($geo as $r) {
        $mx = max($mx, intval($r['total']));
    }
    $geo = array_map(fn($r) => ['k' => $r['k'], 'total' => intval($r['total']), 'share' => $mx > 0 ? round(intval($r['total']) / $mx * 100, 2) : 0], $geo);
    $u = $pdo->prepare("SELECT COALESCE(utm_source, '') k, COUNT(*) total FROM share_link_clicks WHERE share_link_id = :id AND created_at BETWEEN :sd AND :ed GROUP BY k ORDER BY total DESC LIMIT 8");
    $u->execute([':id' => $linkId, ':sd' => $sd, ':ed' => $ed]);
    $utm = $u->fetchAll(PDO::FETCH_ASSOC);
    $mx2 = 0;
    foreach ($utm as $r) {
        $mx2 = max($mx2, intval($r['total']));
    }
    $utm = array_map(fn($r) => ['k' => $r['k'], 'total' => intval($r['total']), 'share' => $mx2 > 0 ? round(intval($r['total']) / $mx2 * 100, 2) : 0], $utm);
    jsonOut(['success' => true, 'details' => $link, 'metrics' => ['clicks' => intval($metrics['clicks'] ?? 0), 'uniques' => intval($metrics['uniques'] ?? 0)], 'series' => $days, 'geo' => $geo, 'utm' => $utm]);
}

if ($action === 'toggle_active' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $linkId = $input['link_id'] ?? null;
    $active = isset($input['active']) ? intval($input['active']) : 1;
    if (!$linkId) {
        jsonOut(['success' => false, 'message' => 'Missing link']);
    }
    $s = $pdo->prepare("UPDATE share_links SET active = :a WHERE id = :id AND owner_user_id = :u");
    $s->execute([':a' => $active, ':id' => $linkId, ':u' => $userId]);
    jsonOut(['success' => true, 'message' => 'Link updated', 'active' => $active === 1]);
}

if ($action === 'update_note' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $linkId = $input['link_id'] ?? null;
    $note = substr(trim($input['note'] ?? ''), 0, 255);
    if (!$linkId) {
        jsonOut(['success' => false, 'message' => 'Missing link']);
    }
    $s = $pdo->prepare("UPDATE share_links SET note = :n WHERE id = :id AND owner_user_id = :u");
    $s->execute([':n' => $note, ':id' => $linkId, ':u' => $userId]);
    jsonOut(['success' => true, 'message' => 'Note saved']);
}

jsonOut(['success' => false, 'message' => 'Unknown action']);
