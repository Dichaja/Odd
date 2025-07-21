<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['user']) ||
    empty($_SESSION['user']['logged_in']) ||
    !isset($_SESSION['user']['user_id'])
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = $_SESSION['user']['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getDashboardStats':
            getDashboardStats($userId);
            break;
        case 'getRecentActivity':
            getRecentActivity($userId);
            break;
        case 'getWalletBalance':
            getWalletBalance($userId);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("User Dashboard API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function getDashboardStats($userId)
{
    global $pdo;

    try {
        $stats = [
            'quotations' => getQuotationStats($userId),
            'stores' => getStoreStats($userId),
            'transactions' => getTransactionStats($userId),
            'wallet' => getWalletStats($userId),
            'sms' => getSMSStats($userId)
        ];

        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

    } catch (Exception $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch dashboard statistics']);
    }
}

function getQuotationStats($userId)
{
    global $pdo;

    $stats = [
        'total' => 0,
        'new' => 0,
        'processing' => 0,
        'processed' => 0,
        'paid' => 0,
        'cancelled' => 0,
        'total_value' => 0
    ];

    try {
        // Get quotation counts by status
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(fee_charged + transport) as total_amount
            FROM request_for_quote 
            WHERE user_id = :user_id 
            GROUP BY status
        ");
        $stmt->execute([':user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $statusKey = strtolower($result['status']);
            if (isset($stats[$statusKey])) {
                $stats[$statusKey] = (int) $result['count'];
                $stats['total_value'] += (float) $result['total_amount'];
            }
            $stats['total'] += (int) $result['count'];
        }

    } catch (Exception $e) {
        error_log("Error fetching quotation stats: " . $e->getMessage());
    }

    return $stats;
}

function getStoreStats($userId)
{
    global $pdo;

    $stats = [
        'total_stores' => 0,
        'active_stores' => 0,
        'pending_stores' => 0,
        'total_products' => 0,
        'active_products' => 0
    ];

    try {
        // Get store counts
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count
            FROM vendor_stores 
            WHERE owner_id = :user_id 
            GROUP BY status
        ");
        $stmt->execute([':user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats['total_stores'] += (int) $result['count'];
            if ($result['status'] === 'active') {
                $stats['active_stores'] = (int) $result['count'];
            } elseif ($result['status'] === 'pending') {
                $stats['pending_stores'] = (int) $result['count'];
            }
        }

        // Get product counts for user's stores
        $stmt = $pdo->prepare("
            SELECT 
                p.status,
                COUNT(*) as count
            FROM products p
            INNER JOIN vendor_stores vs ON p.user_id = vs.owner_id
            WHERE vs.owner_id = :user_id
            GROUP BY p.status
        ");
        $stmt->execute([':user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats['total_products'] += (int) $result['count'];
            if ($result['status'] === 'published') {
                $stats['active_products'] = (int) $result['count'];
            }
        }

    } catch (Exception $e) {
        error_log("Error fetching store stats: " . $e->getMessage());
    }

    return $stats;
}

function getTransactionStats($userId)
{
    global $pdo;

    $stats = [
        'total_transactions' => 0,
        'successful_transactions' => 0,
        'pending_transactions' => 0,
        'failed_transactions' => 0,
        'total_amount' => 0,
        'this_month_amount' => 0
    ];

    try {
        // Get transaction counts and amounts
        $stmt = $pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count,
                SUM(amount_total) as total_amount
            FROM zzimba_financial_transactions 
            WHERE user_id = :user_id 
            GROUP BY status
        ");
        $stmt->execute([':user_id' => $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $stats['total_transactions'] += (int) $result['count'];
            $stats['total_amount'] += (float) $result['total_amount'];

            if ($result['status'] === 'SUCCESS') {
                $stats['successful_transactions'] = (int) $result['count'];
            } elseif ($result['status'] === 'PENDING') {
                $stats['pending_transactions'] = (int) $result['count'];
            } elseif ($result['status'] === 'FAILED') {
                $stats['failed_transactions'] = (int) $result['count'];
            }
        }

        // Get this month's transaction amount
        $stmt = $pdo->prepare("
            SELECT SUM(amount_total) as month_amount
            FROM zzimba_financial_transactions 
            WHERE user_id = :user_id 
            AND status = 'SUCCESS'
            AND MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month_amount'] = (float) ($result['month_amount'] ?? 0);

    } catch (Exception $e) {
        error_log("Error fetching transaction stats: " . $e->getMessage());
    }

    return $stats;
}

function getWalletStats($userId)
{
    global $pdo;

    $stats = [
        'main_balance' => 0,
        'sms_balance' => 0,
        'total_wallets' => 0
    ];

    try {
        // Get main wallet balance
        $stmt = $pdo->prepare("
            SELECT current_balance
            FROM zzimba_wallets 
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            AND status = 'active'
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['main_balance'] = (float) ($result['current_balance'] ?? 0);

        // Get SMS wallet balance
        $stmt = $pdo->prepare("
            SELECT current_balance
            FROM zzimba_sms_wallet 
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            AND status = 'active'
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['sms_balance'] = (float) ($result['current_balance'] ?? 0);

        // Count total wallets
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM zzimba_wallets 
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_wallets'] = (int) ($result['count'] ?? 0);

    } catch (Exception $e) {
        error_log("Error fetching wallet stats: " . $e->getMessage());
    }

    return $stats;
}

function getSMSStats($userId)
{
    global $pdo;

    $stats = [
        'total_sent' => 0,
        'this_month_sent' => 0,
        'total_cost' => 0,
        'templates_count' => 0
    ];

    try {
        // Get SMS history stats
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_sent,
                SUM(total_cost) as total_cost,
                SUM(recipient_count) as total_recipients
            FROM zzimba_sms_history 
            WHERE user_id = :user_id 
            AND status = 'sent'
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_sent'] = (int) ($result['total_sent'] ?? 0);
        $stats['total_cost'] = (float) ($result['total_cost'] ?? 0);

        // Get this month's SMS count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as month_sent
            FROM zzimba_sms_history 
            WHERE user_id = :user_id 
            AND status = 'sent'
            AND MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month_sent'] = (int) ($result['month_sent'] ?? 0);

        // Get templates count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM zzimba_sms_templates 
            WHERE user_id = :user_id 
            AND is_active = 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['templates_count'] = (int) ($result['count'] ?? 0);

    } catch (Exception $e) {
        error_log("Error fetching SMS stats: " . $e->getMessage());
    }

    return $stats;
}

function getRecentActivity($userId)
{
    global $pdo;

    try {
        $activities = [];

        // Recent quotations
        $stmt = $pdo->prepare("
            SELECT 
                'quotation' as type,
                RFQ_ID as reference,
                site_location as description,
                status,
                created_at
            FROM request_for_quote 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 3
        ");
        $stmt->execute([':user_id' => $userId]);
        $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent transactions
        $stmt = $pdo->prepare("
            SELECT 
                'transaction' as type,
                transaction_id as reference,
                CONCAT(transaction_type, ' - UGX ', FORMAT(amount_total, 2)) as description,
                status,
                created_at
            FROM zzimba_financial_transactions 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT 3
        ");
        $stmt->execute([':user_id' => $userId]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Merge and sort activities
        $activities = array_merge($quotations, $transactions);
        usort($activities, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Limit to 5 most recent
        $activities = array_slice($activities, 0, 5);

        echo json_encode([
            'success' => true,
            'activities' => $activities
        ]);

    } catch (Exception $e) {
        error_log("Error fetching recent activity: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch recent activity']);
    }
}

function getWalletBalance($userId)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                wallet_name,
                current_balance,
                status
            FROM zzimba_wallets 
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'wallets' => $wallets
        ]);

    } catch (Exception $e) {
        error_log("Error fetching wallet balance: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch wallet balance']);
    }
}
?>