<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (
    !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in']
    || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getDashboardStats':
            getDashboardStats();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function getDashboardStats()
{
    global $pdo;

    try {
        $quoteStats = getQuoteStatistics();
        $recentQuotes = getRecentQuotes();
        $systemStatus = getSystemStatus();

        echo json_encode([
            'success' => true,
            'stats' => $quoteStats,
            'recent_quotes' => $recentQuotes,
            'system_status' => $systemStatus,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch dashboard statistics']);
    }
}

function getQuoteStatistics()
{
    global $pdo;

    $stats = [
        'pending_quotes' => 0,
        'pending_quotes_value' => 0,
        'completed_quotes' => 0,
        'completed_quotes_value' => 0,
        'total_users' => 0,
        'active_users' => 0,
        'total_vendors' => 0,
        'active_vendors' => 0
    ];

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(fee_charged), 0) as total_value FROM request_for_quote WHERE status = 'New'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_quotes'] = (int) $result['count'];
        $stats['pending_quotes_value'] = (float) $result['total_value'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(SUM(fee_charged), 0) as total_value FROM request_for_quote WHERE status IN ('Processing', 'Processed')");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed_quotes'] = (int) $result['count'];
        $stats['completed_quotes_value'] = (float) $result['total_value'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM zzimba_users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_users'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM zzimba_users WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_users'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vendor_stores");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_vendors'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vendor_stores WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_vendors'] = (int) $result['count'];

    } catch (Exception $e) {
        error_log("Error fetching quote statistics: " . $e->getMessage());
    }

    return $stats;
}

function getRecentQuotes($limit = 5)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT RFQ_ID, site_location, status, fee_charged, created_at FROM request_for_quote ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Error fetching recent quotes: " . $e->getMessage());
        return [];
    }
}

function getSystemStatus()
{
    global $pdo;

    $status = [
        'active_users' => 0,
        'pending_transactions' => 0,
        'total_products' => 0,
        'active_vendors' => 0
    ];

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM zzimba_users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['active_users'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM zzimba_financial_transactions WHERE status = 'PENDING'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['pending_transactions'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['total_products'] = (int) $result['count'];

        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vendor_stores WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['active_vendors'] = (int) $result['count'];

    } catch (Exception $e) {
        error_log("Error fetching system status: " . $e->getMessage());
    }

    return $status;
}
?>