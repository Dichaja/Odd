<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'listPending':
        handleListPending();
        break;
    case 'verifyAdminPassword':
        verifyAdminPassword($pdo);
        break;
    case 'acknowledge':
        handleAcknowledge();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleListPending()
{
    global $pdo;

    $sql = "
        SELECT
            transaction_id,
            amount_total,
            payment_method,
            external_reference,
            external_metadata,
            note,
            user_id,
            vendor_id,
            wallet_id,
            created_at
        FROM zzimba_financial_transactions
        WHERE transaction_type = 'TOPUP'
          AND payment_method   IN ('BANK','MOBILE_MONEY')
          AND status           = 'PENDING'
        ORDER BY created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($rows as $row) {
        $meta = json_decode($row['external_metadata'], true) ?: [];
        $cashAccountId = $meta['cash_account_id'] ?? null;

        // Get cash account name
        $accountName = null;
        if ($cashAccountId) {
            $aStmt = $pdo->prepare("SELECT name FROM zzimba_cash_accounts WHERE id = :id LIMIT 1");
            $aStmt->execute([':id' => $cashAccountId]);
            $accountName = $aStmt->fetchColumn();
        }

        // Determine account type and get account information
        $accountType = 'user'; // default
        $userInfo = null;
        $vendorInfo = null;
        $platformAccountInfo = null;

        if (!empty($row['user_id'])) {
            // User account
            $accountType = 'user';
            $uStmt = $pdo->prepare("
                SELECT first_name, last_name, email, phone
                  FROM zzimba_users
                 WHERE id = :id
                 LIMIT 1
            ");
            $uStmt->execute([':id' => $row['user_id']]);
            if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                $userInfo = [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'email' => $u['email'],
                    'phone' => $u['phone'],
                ];
            }
        } elseif (!empty($row['vendor_id'])) {
            // Vendor account
            $accountType = 'user'; // vendors are still considered user accounts
            $vStmt = $pdo->prepare("
                SELECT name AS vendor_name, business_email, business_phone, contact_person_name
                  FROM vendor_stores
                 WHERE id = :id
                 LIMIT 1
            ");
            $vStmt->execute([':id' => $row['vendor_id']]);
            if ($v = $vStmt->fetch(PDO::FETCH_ASSOC)) {
                $vendorInfo = [
                    'vendor_name' => $v['vendor_name'],
                    'email' => $v['business_email'],
                    'business_phone' => $v['business_phone'],
                    'contact_person_name' => $v['contact_person_name'],
                ];
            }
        } elseif (!empty($row['wallet_id'])) {
            // Check if this is a platform account
            $pStmt = $pdo->prepare("
                SELECT 
                    w.wallet_id,
                    w.wallet_name,
                    w.wallet_number,
                    w.owner_type,
                    pas.type as platform_type
                FROM zzimba_wallets w
                LEFT JOIN zzimba_platform_account_settings pas ON w.wallet_id = pas.platform_account_id
                WHERE w.wallet_id = :wallet_id
                LIMIT 1
            ");
            $pStmt->execute([':wallet_id' => $row['wallet_id']]);
            if ($p = $pStmt->fetch(PDO::FETCH_ASSOC)) {
                if ($p['owner_type'] === 'PLATFORM') {
                    $accountType = 'platform';
                    $platformAccountInfo = [
                        'wallet_id' => $p['wallet_id'],
                        'wallet_name' => $p['wallet_name'],
                        'wallet_number' => $p['wallet_number'],
                        'type' => $p['platform_type'] ?: 'platform'
                    ];
                }
            }
        }

        $results[] = [
            'transaction_id' => $row['transaction_id'],
            'amount_total' => $row['amount_total'],
            'payment_method' => $row['payment_method'],
            'external_reference' => $row['external_reference'],
            'external_metadata' => $meta,
            'note' => $row['note'],
            'cash_account_id' => $cashAccountId,
            'cash_account_name' => $accountName,
            'account_type' => $accountType,
            'user_id' => $row['user_id'],
            'vendor_id' => $row['vendor_id'],
            'wallet_id' => $row['wallet_id'],
            'user' => $userInfo,
            'vendor' => $vendorInfo,
            'platform_account' => $platformAccountInfo,
            'created_at' => $row['created_at'],
        ];
    }

    echo json_encode(['success' => true, 'pending' => $results]);
}

function verifyAdminPassword(PDO $pdo)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
        return;
    }

    $password = $_POST['password'] ?? '';
    $userId = $_SESSION['user']['user_id'];

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        return;
    }

    try {
        $sql = "SELECT password FROM admin_users WHERE id = :user_id AND status = 'active' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            echo json_encode(['success' => false, 'message' => 'Admin not found or inactive']);
            return;
        }

        if (password_verify($password, $admin['password'])) {
            $token = generateAdminSecurityToken($userId);

            $_SESSION['admin_security_token'] = [
                'token' => $token,
                'user_id' => $userId,
                'created_at' => time(),
                'expires_at' => time() + 300 // 5 minutes
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Admin password verified successfully',
                'token' => $token
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password']);
        }

    } catch (PDOException $e) {
        error_log('Error verifying admin password: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function generateAdminSecurityToken($userId)
{
    $data = 'admin_' . $userId . time() . bin2hex(random_bytes(16));
    return hash('sha256', $data);
}

function verifyAdminSecurityToken($token, $userId)
{
    if (!isset($_SESSION['admin_security_token'])) {
        return false;
    }

    $sessionToken = $_SESSION['admin_security_token'];

    if ($sessionToken['token'] !== $token) {
        return false;
    }

    if ($sessionToken['user_id'] !== $userId) {
        return false;
    }

    if (time() > $sessionToken['expires_at']) {
        unset($_SESSION['admin_security_token']);
        return false;
    }

    return true;
}

function handleAcknowledge()
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
        echo json_encode(['success' => false, 'message' => 'Admin authentication required']);
        return;
    }

    $transactionId = trim($_POST['transaction_id'] ?? '');
    $status = strtoupper(trim($_POST['status'] ?? ''));
    $adminSecurityToken = $_POST['admin_security_token'] ?? '';
    $userId = $_SESSION['user']['user_id'];

    if (!$transactionId || !in_array($status, ['SUCCESS', 'FAILED'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    if (empty($adminSecurityToken)) {
        echo json_encode(['success' => false, 'message' => 'Admin security token required']);
        return;
    }

    if (!verifyAdminSecurityToken($adminSecurityToken, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired admin security token. Please verify your password again.']);
        return;
    }

    try {
        // Clear the security token after use
        unset($_SESSION['admin_security_token']);

        $result = CreditService::acknowledgeCashTopup($transactionId, $status);
        echo json_encode($result);

    } catch (Exception $e) {
        error_log('Error in admin acknowledge: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Transaction processing failed. Please try again.']);
    }
}
?>