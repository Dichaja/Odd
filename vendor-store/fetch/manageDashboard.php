<?php
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in']) || !isset($_SESSION['user']['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing active store']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getDashboardStats':
            getDashboardStats($storeId);
            break;
        case 'getRecentActivity':
            getRecentActivity($storeId);
            break;
        case 'getWalletBalance':
            getWalletBalance($storeId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Vendor Dashboard API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function getDashboardStats(string $storeId): void
{
    $stats = [
        'requests' => getRequestStats($storeId),
        'products' => getProductStats($storeId),
        'managers' => getManagersStats($storeId),
        'transactions' => getTransactionStatsMonthlyFromEntries($storeId),
        'wallet' => getWalletStatsVendor($storeId),
        'monthly_store_views' => getMonthlyStoreViewsUgandaFromSession(),
        'top_products' => getTopProductsByViews($storeId, 6)
    ];
    echo json_encode(['success' => true, 'stats' => $stats, 'timestamp' => date('Y-m-d H:i:s')]);
}

function getRequestStats(string $storeId): array
{
    global $pdo;
    $stats = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => 0];
    try {
        $sql = "
            SELECT bisr.status, COUNT(*) AS count
            FROM buy_in_store_requests bisr
            JOIN store_products sp   ON bisr.store_product_id = sp.id
            JOIN store_categories sc ON sp.store_category_id = sc.id
            WHERE sc.store_id = :sid
            GROUP BY bisr.status
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => $storeId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $key = strtolower($r['status']);
            if (isset($stats[$key])) {
                $stats[$key] = (int) $r['count'];
                $stats['total'] += (int) $r['count'];
            }
        }
    } catch (Exception $e) {
        error_log("Vendor getRequestStats error: " . $e->getMessage());
    }
    return $stats;
}

function getProductStats(string $storeId): array
{
    global $pdo;
    $stats = ['total_products' => 0, 'active_products' => 0, 'inactive_products' => 0];
    try {
        $sql = "
            SELECT sp.status, COUNT(DISTINCT sp.id) AS cnt
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            WHERE sc.store_id = :sid
            GROUP BY sp.status
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':sid' => $storeId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $stats['total_products'] += (int) $r['cnt'];
            if ($r['status'] === 'active')
                $stats['active_products'] = (int) $r['cnt'];
            if ($r['status'] === 'inactive')
                $stats['inactive_products'] = (int) $r['cnt'];
        }
    } catch (Exception $e) {
        error_log("Vendor getProductStats error: " . $e->getMessage());
    }
    return $stats;
}

function getManagersStats(string $storeId): array
{
    global $pdo;
    $stats = ['total_managers' => 0];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM store_managers WHERE store_id = :sid AND status = 'active' AND approved = 1");
        $stmt->execute([':sid' => $storeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_managers'] = (int) ($row['cnt'] ?? 0);
    } catch (Exception $e) {
        error_log("Vendor getManagersStats error: " . $e->getMessage());
    }
    return $stats;
}

function getTransactionStatsMonthlyFromEntries(string $storeId): array
{
    global $pdo;
    $stats = ['month_total_amount' => 0.0, 'month_credits' => 0.0, 'month_debits' => 0.0];

    try {
        $walletStmt = $pdo->prepare("SELECT wallet_id FROM zzimba_wallets WHERE owner_type = 'VENDOR' AND vendor_id = :sid AND status = 'active'");
        $walletStmt->execute([':sid' => $storeId]);
        $walletIds = array_column($walletStmt->fetchAll(PDO::FETCH_ASSOC), 'wallet_id');

        if (empty($walletIds)) {
            return $stats;
        }

        $placeholders = implode(',', array_fill(0, count($walletIds), '?'));
        $sql = "
            SELECT e.entry_type, SUM(e.amount) AS amt
            FROM zzimba_transaction_entries e
            WHERE e.wallet_id IN ($placeholders)
              AND CONVERT_TZ(e.created_at,'UTC','Africa/Kampala') >= DATE_FORMAT(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','Africa/Kampala'), '%Y-%m-01 00:00:00')
              AND CONVERT_TZ(e.created_at,'UTC','Africa/Kampala') < DATE_ADD(DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','Africa/Kampala')), INTERVAL 1 DAY)
            GROUP BY e.entry_type
        ";
        $stmt = $pdo->prepare($sql);
        foreach ($walletIds as $i => $id) {
            $stmt->bindValue($i + 1, $id, PDO::PARAM_STR);
        }
        $stmt->execute();

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if ($r['entry_type'] === 'CREDIT')
                $stats['month_credits'] = (float) $r['amt'];
            if ($r['entry_type'] === 'DEBIT')
                $stats['month_debits'] = (float) $r['amt'];
        }

        $stats['month_total_amount'] = $stats['month_credits'] + $stats['month_debits'];
    } catch (Exception $e) {
        error_log("Vendor getTransactionStatsMonthlyFromEntries error: " . $e->getMessage());
    }

    return $stats;
}

function getWalletStatsVendor(string $storeId): array
{
    global $pdo;
    $stats = ['main_balance' => 0.0, 'sms_balance' => 0.0, 'total_wallets' => 0];
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(current_balance),0) AS total_bal FROM zzimba_wallets WHERE owner_type = 'VENDOR' AND vendor_id = :sid AND status = 'active'");
        $stmt->execute([':sid' => $storeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_wallets'] = (int) ($row['cnt'] ?? 0);
        $stats['main_balance'] = (float) ($row['total_bal'] ?? 0);

        $stmt = $pdo->prepare("SELECT COALESCE(current_balance, 0) AS bal FROM zzimba_sms_wallet WHERE owner_type = 'VENDOR' AND vendor_id = :sid AND status = 'active' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([':sid' => $storeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['sms_balance'] = (int) ($row['bal'] ?? 0);
    } catch (Exception $e) {
        error_log("Vendor getWalletStatsVendor error: " . $e->getMessage());
    }
    return $stats;
}

function getMonthlyStoreViewsUgandaFromSession(): int
{
    global $pdo;
    $sid = $_SESSION['active_store'] ?? null;
    if (!$sid)
        return 0;

    $count = 0;
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM store_profile_views
            WHERE store_id = :sid
              AND CONVERT_TZ(created_at,'UTC','Africa/Kampala') >= DATE_FORMAT(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','Africa/Kampala'), '%Y-%m-01 00:00:00')
              AND CONVERT_TZ(created_at,'UTC','Africa/Kampala') < DATE_ADD(DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','Africa/Kampala')), INTERVAL 1 DAY)
        ");
        $stmt->execute([':sid' => $sid]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = (int) ($row['cnt'] ?? 0);
    } catch (Exception $e) {
        error_log("Vendor getMonthlyStoreViewsUgandaFromSession error: " . $e->getMessage());
    }
    return $count;
}

function getTopProductsByViews(string $storeId, int $limit = 6): array
{
    global $pdo;
    $items = [];
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.id AS product_id,
                p.title AS title,
                MIN(pp.price) AS price,
                COUNT(ppv.id) AS views_all,
                SUM(CASE WHEN CONVERT_TZ(ppv.created_at,'UTC','Africa/Kampala') >= DATE_SUB(DATE(CONVERT_TZ(UTC_TIMESTAMP(),'UTC','Africa/Kampala')), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS views_30d
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id AND sc.store_id = :sid
            JOIN products p ON sp.product_id = p.id
            LEFT JOIN product_pricing pp ON pp.store_products_id = sp.id
            LEFT JOIN product_price_views ppv ON ppv.pricing_id = pp.id
            GROUP BY p.id, p.title
            ORDER BY views_30d DESC, views_all DESC, title ASC
            LIMIT :lim
        ");
        $stmt->bindValue(':sid', $storeId, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $items[] = [
                'product_id' => $r['product_id'],
                'title' => $r['title'],
                'price' => isset($r['price']) ? (float) $r['price'] : null,
                'views_all' => (int) $r['views_all'],
                'views_30d' => (int) $r['views_30d'],
                'image_url' => getProductImageUrl($r['product_id'])
            ];
        }
    } catch (Exception $e) {
        error_log("Vendor getTopProductsByViews error: " . $e->getMessage());
    }
    return $items;
}

function getProductImageUrl(string $productId): string
{
    $dir = __DIR__ . '/../../img/products/' . $productId . '/';
    $webBase = BASE_URL . 'img/products/' . $productId . '/';
    if (is_dir($dir)) {
        $files = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return $webBase . basename($files[0]);
        }
    }
    return 'https://placehold.co/80x80/f0f0f0/808080?text=Product';
}

function getRecentActivity(string $storeId): void
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT
                'request' AS type,
                bisr.id AS reference,
                CONCAT('Buy in Store • Qty ', bisr.quantity, ' • ', DATE_FORMAT(CONVERT_TZ(bisr.created_at,'UTC','Africa/Kampala'), '%b %e')) AS description,
                bisr.status AS status,
                bisr.created_at
            FROM buy_in_store_requests bisr
            JOIN store_products sp   ON bisr.store_product_id = sp.id
            JOIN store_categories sc ON sp.store_category_id = sc.id
            WHERE sc.store_id = :sid
            ORDER BY bisr.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([':sid' => $storeId]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
            SELECT
                'transaction' AS type,
                zft.transaction_id AS reference,
                CONCAT(zft.transaction_type, ' • UGX ', FORMAT(zft.amount_total, 0)) AS description,
                zft.status AS status,
                zft.created_at
            FROM zzimba_financial_transactions zft
            WHERE zft.vendor_id = :sid
            ORDER BY zft.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([':sid' => $storeId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = array_merge($requests, $transactions);
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']); });
        $activities = array_slice($activities, 0, 5);
        echo json_encode(['success' => true, 'activities' => $activities]);
    } catch (Exception $e) {
        error_log("Vendor getRecentActivity error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch recent activity']);
    }
}

function getWalletBalance(string $storeId): void
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT wallet_name, current_balance, status FROM zzimba_wallets WHERE owner_type = 'VENDOR' AND vendor_id = :sid ORDER BY created_at DESC");
        $stmt->execute([':sid' => $storeId]);
        $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'wallets' => $wallets]);
    } catch (Exception $e) {
        error_log("Vendor getWalletBalance error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch wallet balance']);
    }
}
