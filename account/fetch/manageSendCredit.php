<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

// Start session if not already started
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
    // Check if user is logged in
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
        // Fetch user's password hash from database
        $sql = "SELECT password FROM zzimba_users WHERE id = :user_id AND status = 'active' LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found or inactive']);
            return;
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Generate security token
            $token = generateSecurityToken($userId);

            // Store token in session with timestamp
            $_SESSION['security_token'] = [
                'token' => $token,
                'user_id' => $userId,
                'created_at' => time(),
                'expires_at' => time() + 300 // 5 minutes expiry
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

function generateSecurityToken($userId)
{
    // Generate a secure token using user ID, current time, and random bytes
    $data = $userId . time() . bin2hex(random_bytes(16));
    return hash('sha256', $data);
}

function verifySecurityToken($token, $userId)
{
    // Check if token exists in session
    if (!isset($_SESSION['security_token'])) {
        return false;
    }

    $sessionToken = $_SESSION['security_token'];

    // Verify token matches
    if ($sessionToken['token'] !== $token) {
        return false;
    }

    // Verify user ID matches
    if ($sessionToken['user_id'] !== $userId) {
        return false;
    }

    // Check if token has expired
    if (time() > $sessionToken['expires_at']) {
        // Clear expired token
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
    // Check if user is logged in
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        echo json_encode(['success' => false, 'message' => 'User not authenticated']);
        return;
    }

    $walletTo = trim($_POST['wallet_to'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $securityToken = $_POST['security_token'] ?? '';
    $userId = $_SESSION['user']['user_id'];

    // Validate required parameters
    if (empty($walletTo) || $amount <= 0 || empty($securityToken)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }

    // Verify security token
    if (!verifySecurityToken($securityToken, $userId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired security token. Please verify your password again.']);
        return;
    }

    try {
        // Clear the used token
        unset($_SESSION['security_token']);

        // Proceed with credit transfer
        $result = CreditService::transfer([
            'wallet_to' => $walletTo,
            'amount' => $amount
        ]);

        echo json_encode($result);

    } catch (Exception $e) {
        error_log('Error in sendCredit: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Transfer failed. Please try again.']);
    }
}
