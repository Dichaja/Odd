<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'searchWallet':
        handleSearchWallet($pdo);
        break;
    case 'verifyPassword':
        verifyUserPassword($pdo);
        break;
    case 'sendCredit':
        sendCredit($pdo);
        break;
    case 'getTransferFeeSettings':
        getTransferFeeSettings($pdo);
        break;
    case 'getCurrentBalance':
        getCurrentBalance($pdo);
        break;
    case 'validateTransferBalance':
        validateTransferBalance($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleSearchWallet(PDO $pdo)
{
    $type = $_POST['type'] ?? '';
    $searchType = $_POST['searchType'] ?? '';
    $searchValue = $_POST['searchValue'] ?? '';

    if (!in_array($type, ['vendor', 'user'], true) || !in_array($searchType, ['id', 'name'], true) || $searchValue === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    try {
        if ($searchType === 'name') {
            $wallets = fetchWalletsByName($pdo, $type, $searchValue);
            if (!empty($wallets)) {
                echo json_encode(['success' => true, 'wallets' => $wallets]);
            } else {
                echo json_encode(['success' => false, 'message' => ucfirst($type) . ' wallets not found']);
            }
        } else {
            $wallet = fetchWalletByNumber($pdo, $type, $searchValue);
            if ($wallet) {
                echo json_encode(['success' => true, 'wallet' => $wallet]);
            } else {
                echo json_encode(['success' => false, 'message' => ucfirst($type) . ' wallet not found']);
            }
        }
    } catch (PDOException $e) {
        error_log('Error searching wallet: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function verifyUserPassword(PDO $pdo)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        return;
    }

    $password = $_POST['password'] ?? '';
    $userId = $_SESSION['user']['user_id'];

    if (empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Password is required']);
        return;
    }

    try {
        $sql = "SELECT password FROM zzimba_users WHERE id = :user_id AND status = 'active' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
            return;
        }

        if (password_verify($password, $user['password'])) {
            $token = generateSecurityToken($userId);

            $_SESSION['security_token'] = [
                'token' => $token,
                'user_id' => $userId,
                'created_at' => time(),
                'expires_at' => time() + 300
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Password verified successfully',
                'token' => $token
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password']);
        }

    } catch (PDOException $e) {
        error_log('Error verifying password: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function getTransferFeeSettings(PDO $pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT setting_key, setting_name, setting_value, setting_type, applicable_to, status 
            FROM zzimba_credit_settings 
            WHERE setting_key = 'transfer_fee' 
            AND category = 'transfer' 
            AND status = 'active'
            AND (applicable_to = 'all' OR applicable_to = 'users')
            ORDER BY 
                CASE 
                    WHEN applicable_to = 'all' THEN 1 
                    WHEN applicable_to = 'users' THEN 2 
                    ELSE 3 
                END
            LIMIT 1
        ");

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'feeSettings' => $result ?: null
        ]);
    } catch (Exception $e) {
        error_log("Error fetching transfer fee settings: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching fee settings'
        ]);
    }
}

function getCurrentBalance(PDO $pdo)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT current_balance 
            FROM zzimba_wallets 
            WHERE user_id = ? AND status = 'active'
        ");

        $stmt->execute([$_SESSION['user']['user_id']]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($wallet) {
            echo json_encode([
                'success' => true,
                'balance' => floatval($wallet['current_balance'])
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Wallet not found'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error fetching current balance: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching balance'
        ]);
    }
}

function validateTransferBalance(PDO $pdo)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        return;
    }

    $amount = floatval($_POST['amount'] ?? 0);
    $userId = $_SESSION['user']['user_id'];

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        return;
    }

    try {
        $feeSettings = getFeeSettingsForUser($pdo);
        $transferFee = calculateTransferFee($amount, $feeSettings);

        $stmt = $pdo->prepare("
            SELECT current_balance 
            FROM zzimba_wallets 
            WHERE user_id = ? AND status = 'active'
        ");

        $stmt->execute([$userId]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            echo json_encode(['success' => false, 'message' => 'Wallet not found']);
            return;
        }

        $currentBalance = floatval($wallet['current_balance']);
        $totalRequired = $amount + $transferFee;

        $validation = [
            'isValid' => $totalRequired <= $currentBalance,
            'fee' => $transferFee,
            'totalRequired' => $totalRequired,
            'availableBalance' => $currentBalance
        ];

        echo json_encode([
            'success' => true,
            'validation' => $validation
        ]);

    } catch (Exception $e) {
        error_log("Error validating transfer balance: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error validating balance'
        ]);
    }
}

function calculateTransferFee($amount, $feeSettings)
{
    if (!$feeSettings)
        return 0;

    $amount = floatval($amount);
    $feeValue = floatval($feeSettings['setting_value']);

    if ($feeSettings['setting_type'] === 'flat') {
        return $feeValue;
    } elseif ($feeSettings['setting_type'] === 'percentage') {
        return ($amount * $feeValue) / 100;
    }

    return 0;
}

function getFeeSettingsForUser($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT setting_key, setting_name, setting_value, setting_type, applicable_to, status 
            FROM zzimba_credit_settings 
            WHERE setting_key = 'transfer_fee' 
            AND category = 'transfer' 
            AND status = 'active'
            AND (applicable_to = 'all' OR applicable_to = 'users')
            ORDER BY 
                CASE 
                    WHEN applicable_to = 'all' THEN 1 
                    WHEN applicable_to = 'users' THEN 2 
                    ELSE 3 
                END
            LIMIT 1
        ");

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Exception $e) {
        error_log("Error fetching fee settings: " . $e->getMessage());
        return null;
    }
}

function generateSecurityToken($userId)
{
    $data = $userId . time() . bin2hex(random_bytes(16));
    return hash('sha256', $data);
}

function verifySecurityToken($token, $userId)
{
    if (!isset($_SESSION['security_token'])) {
        return false;
    }

    $sessionToken = $_SESSION['security_token'];

    if ($sessionToken['token'] !== $token) {
        return false;
    }

    if ($sessionToken['user_id'] !== $userId) {
        return false;
    }

    if (time() > $sessionToken['expires_at']) {
        unset($_SESSION['security_token']);
        return false;
    }

    return true;
}

function fetchWalletByNumber(PDO $pdo, string $type, string $number): ?array
{
    $ownerType = $type === 'vendor' ? 'VENDOR' : 'USER';
    $sql = "SELECT wallet_id, wallet_number, wallet_name, current_balance, status, created_at FROM zzimba_wallets WHERE owner_type = :owner AND wallet_number = :value LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner' => $ownerType, ':value' => $number]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchWalletsByName(PDO $pdo, string $type, string $name): array
{
    $ownerType = $type === 'vendor' ? 'VENDOR' : 'USER';
    $sql = "SELECT wallet_id, wallet_number, wallet_name, current_balance, status, created_at FROM zzimba_wallets WHERE owner_type = :owner AND wallet_name = :value ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner' => $ownerType, ':value' => $name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendCredit(PDO $pdo)
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        return;
    }

    $walletTo = trim($_POST['wallet_to'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $securityToken = $_POST['security_token'] ?? '';
    $userId = $_SESSION['user']['user_id'];

    if (empty($walletTo) || $amount <= 0 || empty($securityToken)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }

    if (!verifySecurityToken($securityToken, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please verify your password again.']);
        return;
    }

    try {
        unset($_SESSION['security_token']);

        $feeSettings = getFeeSettingsForUser($pdo);
        $transferFee = calculateTransferFee($amount, $feeSettings);

        $stmt = $pdo->prepare("
            SELECT current_balance 
            FROM zzimba_wallets 
            WHERE user_id = ? AND status = 'active'
        ");

        $stmt->execute([$userId]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet) {
            echo json_encode(['success' => false, 'message' => 'Sender wallet not found']);
            return;
        }

        $currentBalance = floatval($wallet['current_balance']);
        $totalRequired = $amount + $transferFee;

        if ($totalRequired > $currentBalance) {
            echo json_encode([
                'success' => false,
                'message' => "Insufficient balance. Required: " . number_format($totalRequired, 2) . " UGX, Available: " . number_format($currentBalance, 2) . " UGX"
            ]);
            return;
        }

        $result = CreditService::transfer([
            'wallet_to' => $walletTo,
            'amount' => $amount,
            'transfer_fee' => $transferFee,
            'fee_settings' => $feeSettings
        ]);

        if ($result['success']) {
            $stmt = $pdo->prepare("
                SELECT current_balance 
                FROM zzimba_wallets 
                WHERE user_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId]);
            $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

            $result['balance'] = $wallet ? floatval($wallet['current_balance']) : 0;
            $result['fee_charged'] = $transferFee;
            $result['total_deducted'] = $amount + $transferFee;
        }

        echo json_encode($result);

    } catch (Exception $e) {
        error_log('Error in sendCredit: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Transfer failed. Please try again.']);
    }
}
