<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check if user is authenticated and is admin
if (
    !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] ||
    !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit;
}

/**
 * Fetch and output dashboard stats JSON.
 */
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
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    } catch (Exception $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch dashboard statistics']);
    }

    exit;
}

/**
 * Get quote statistics.
 *
 * @return array
 */
function getQuoteStatistics(): array
{
    global $pdo;

    $stats = [
        'pending_quotes' => 0,
        'total_users' => 0,
        'active_users' => 0,
        'total_vendors' => 0,
        'active_vendors' => 0,
    ];

    try {
        // Pending quotes
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM request_for_quote
             WHERE status IN ('Processing', 'Processed')"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['pending_quotes'] = (int) $row['count'];

        // Total users
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM zzimba_users");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_users'] = (int) $row['count'];

        // Active users
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM zzimba_users
             WHERE status = 'active'"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_users'] = (int) $row['count'];

        // Total vendors
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM vendor_stores");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_vendors'] = (int) $row['count'];

        // Active vendors
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM vendor_stores
             WHERE status = 'active'"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_vendors'] = (int) $row['count'];
    } catch (Exception $e) {
        error_log("Error fetching quote statistics: " . $e->getMessage());
    }

    return $stats;
}

/**
 * Get recent quotes.
 *
 * @param int $limit
 * @return array
 */
function getRecentQuotes(int $limit = 5): array
{
    global $pdo;

    try {
        $stmt = $pdo->prepare(
            "SELECT RFQ_ID, site_location, status, fee_charged, created_at
             FROM request_for_quote
             ORDER BY created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching recent quotes: " . $e->getMessage());
        return [];
    }
}

/**
 * Get system status.
 *
 * @return array
 */
function getSystemStatus(): array
{
    global $pdo;

    $status = [
        'active_users' => 0,
        'pending_transactions' => 0,
        'total_products' => 0,
        'active_vendors' => 0,
    ];

    try {
        // Active users (last 30 days)
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM zzimba_users
             WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['active_users'] = (int) $row['count'];

        // Pending transactions
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM zzimba_financial_transactions
             WHERE status = 'PENDING'"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['pending_transactions'] = (int) $row['count'];

        // Total products
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM products");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['total_products'] = (int) $row['count'];

        // Active vendors
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS count
             FROM vendor_stores
             WHERE status = 'active'"
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $status['active_vendors'] = (int) $row['count'];
    } catch (Exception $e) {
        error_log("Error fetching system status: " . $e->getMessage());
    }

    return $status;
}
