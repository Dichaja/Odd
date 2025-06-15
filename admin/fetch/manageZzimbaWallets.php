<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

header('Content-Type: application/json');
session_start();

if (
    !isset($_SESSION['user']) ||
    !$_SESSION['user']['logged_in'] ||
    !$_SESSION['user']['is_admin']
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

// ───────────────────────────────────────────────────────────────────────────────
//  Ensure the wallets and platform account settings tables exist
// ───────────────────────────────────────────────────────────────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS zzimba_wallets (
            wallet_id CHAR(26) NOT NULL PRIMARY KEY,
            owner_type ENUM('USER','VENDOR','PLATFORM') NOT NULL,
            user_id VARCHAR(26) DEFAULT NULL,
            vendor_id VARCHAR(26) DEFAULT NULL,
            wallet_name VARCHAR(100) NOT NULL,
            current_balance DECIMAL(18,2) NOT NULL DEFAULT 0,
            status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,

            CONSTRAINT fk_wallet_user FOREIGN KEY (user_id)
                REFERENCES zzimba_users(id)
                ON DELETE SET NULL ON UPDATE CASCADE,

            CONSTRAINT fk_wallet_vendor FOREIGN KEY (vendor_id)
                REFERENCES vendor_stores(id)
                ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

        CREATE TABLE IF NOT EXISTS zzimba_platform_account_settings (
            id CHAR(26) NOT NULL PRIMARY KEY,
            platform_account_id CHAR(26) NOT NULL,
            type ENUM('withholding','services','operations','communications') NOT NULL UNIQUE,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,

            CONSTRAINT fk_platform_account_settings_wallet FOREIGN KEY (platform_account_id)
                REFERENCES zzimba_wallets(wallet_id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getZzimbaWallets':
            getZzimbaWallets($pdo);
            break;
        case 'getZzimbaWallet':
            getZzimbaWallet($pdo);
            break;
        case 'createZzimbaWallet':
            createZzimbaWallet($pdo);
            break;
        case 'updateZzimbaWallet':
            updateZzimbaWallet($pdo);
            break;
        case 'deleteZzimbaWallet':
            deleteZzimbaWallet($pdo);
            break;
        case 'getWalletStatement':
            getWalletStatement($pdo);
            break;
        case 'managePlatformAccounts':
            managePlatformAccounts($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Error in manageZzimbaWallets: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// ───────────────────────────────────────────────────────────────────────────────
//  Endpoints for wallets
// ───────────────────────────────────────────────────────────────────────────────

function getZzimbaWallets(PDO $pdo)
{
    $stmt = $pdo->prepare("
        SELECT wallet_id, owner_type, user_id, vendor_id, wallet_name,
               current_balance, status, created_at, updated_at
          FROM zzimba_wallets
         ORDER BY created_at DESC
    ");
    $stmt->execute();
    $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'wallets' => $wallets]);
}

function getZzimbaWallet(PDO $pdo)
{
    if (empty($_GET['wallet_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet ID']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT wallet_id, owner_type, user_id, vendor_id, wallet_name,
               current_balance, status, created_at, updated_at
          FROM zzimba_wallets
         WHERE wallet_id = :wallet_id
    ");
    $stmt->execute([':wallet_id' => $_GET['wallet_id']]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$w) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wallet not found']);
        return;
    }

    echo json_encode(['success' => true, 'wallet' => $w]);
}

function createZzimbaWallet(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $owner_type = $data['owner_type'] ?? '';
    $name = trim($data['wallet_name'] ?? '');

    if ($owner_type !== 'PLATFORM') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only create PLATFORM wallets']);
        return;
    }
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Wallet name is required']);
        return;
    }

    $wallet_id = generateUlid();
    $created_at = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $updated_at = $created_at;

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("
            INSERT INTO zzimba_wallets
                (wallet_id, owner_type, user_id, vendor_id, wallet_name, current_balance, status, created_at, updated_at)
            VALUES
                (:wallet_id, :owner_type, NULL, NULL, :wallet_name, 0, 'active', :created_at, :updated_at)
        ");
        $ins->execute([
            ':wallet_id' => $wallet_id,
            ':owner_type' => $owner_type,
            ':wallet_name' => $name,
            ':created_at' => $created_at,
            ':updated_at' => $updated_at,
        ]);
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Wallet created',
            'wallet_id' => $wallet_id
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating wallet: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating wallet']);
    }
}

function updateZzimbaWallet(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $wallet_id = trim($data['wallet_id'] ?? '');
    $wallet_name = trim($data['wallet_name'] ?? '');
    $status = $data['status'] ?? '';

    $allowed = ['active', 'inactive', 'suspended'];
    if ($wallet_id === '' || $wallet_name === '' || !in_array($status, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Wallet ID, name, and valid status are required']);
        return;
    }

    $chk = $pdo->prepare("SELECT wallet_id FROM zzimba_wallets WHERE wallet_id = :wallet_id");
    $chk->execute([':wallet_id' => $wallet_id]);
    if ($chk->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wallet not found']);
        return;
    }

    $updated_at = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare("
            UPDATE zzimba_wallets
               SET wallet_name = :wallet_name,
                   status      = :status,
                   updated_at  = :updated_at
             WHERE wallet_id  = :wallet_id
        ");
        $upd->execute([
            ':wallet_name' => $wallet_name,
            ':status' => $status,
            ':updated_at' => $updated_at,
            ':wallet_id' => $wallet_id,
        ]);
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Wallet updated']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating wallet: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating wallet']);
    }
}

function deleteZzimbaWallet(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $wallet_id = trim($data['wallet_id'] ?? '');

    if ($wallet_id === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet ID']);
        return;
    }

    $stmt = $pdo->prepare("SELECT owner_type FROM zzimba_wallets WHERE wallet_id = :wallet_id");
    $stmt->execute([':wallet_id' => $wallet_id]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$w) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wallet not found']);
        return;
    }
    if ($w['owner_type'] !== 'PLATFORM') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete non-platform wallets']);
        return;
    }

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM zzimba_wallets WHERE wallet_id = :wallet_id");
        $del->execute([':wallet_id' => $wallet_id]);
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Wallet deleted']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting wallet: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting wallet']);
    }
}

function getWalletStatement(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $walletId = trim($data['wallet_id'] ?? '');
    $filter = strtolower(trim($data['filter'] ?? 'all'));
    $start = $data['start'] ?? null;
    $end = $data['end'] ?? null;

    if ($walletId === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet ID']);
        return;
    }

    try {
        $result = CreditService::getWalletStatement($walletId, $filter, $start, $end);
        echo json_encode($result);
    } catch (Exception $e) {
        error_log("[getWalletStatement] " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch wallet statement']);
    }
}

// ───────────────────────────────────────────────────────────────────────────────
//  Endpoint for managing platform account settings
// ───────────────────────────────────────────────────────────────────────────────
function managePlatformAccounts(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $operation = $data['operation'] ?? '';

    // LIST: optional filter by platform_account_id
    if ($operation === 'list') {
        if (!empty($data['platform_account_id'])) {
            $stmt = $pdo->prepare("
                SELECT id, platform_account_id, type, created_at, updated_at
                  FROM zzimba_platform_account_settings
                 WHERE platform_account_id = :platform_account_id
                 ORDER BY created_at DESC
            ");
            $stmt->execute([':platform_account_id' => $data['platform_account_id']]);
        } else {
            $stmt = $pdo->query("
                SELECT id, platform_account_id, type, created_at, updated_at
                  FROM zzimba_platform_account_settings
                 ORDER BY created_at DESC
            ");
        }
        echo json_encode(['success' => true, 'settings' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        return;
    }

    // For add/update/remove, validate platform_account_id
    if (!in_array($operation, ['add', 'update', 'remove'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid operation']);
        return;
    }

    $accountId = trim($data['platform_account_id'] ?? '');
    $chk = $pdo->prepare("
        SELECT wallet_id FROM zzimba_wallets
         WHERE wallet_id = :wallet_id AND owner_type = 'PLATFORM'
    ");
    $chk->execute([':wallet_id' => $accountId]);
    if ($chk->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid platform account id']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    if ($operation === 'add') {
        $type = trim($data['type'] ?? '');
        if ($type === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Type is required']);
            return;
        }

        $id = generateUlid();
        try {
            $ins = $pdo->prepare("
                INSERT INTO zzimba_platform_account_settings
                    (id, platform_account_id, type, created_at, updated_at)
                VALUES
                    (:id, :platform_account_id, :type, :created_at, :updated_at)
            ");
            $ins->execute([
                ':id' => $id,
                ':platform_account_id' => $accountId,
                ':type' => $type,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
            echo json_encode([
                'success' => true,
                'message' => 'Setting added',
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log("Error adding platform setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error adding setting']);
        }
        return;
    }

    if ($operation === 'update') {
        $id = trim($data['id'] ?? '');
        $type = trim($data['type'] ?? '');
        if ($id === '' || $type === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID and new type are required']);
            return;
        }
        try {
            $upd = $pdo->prepare("
                UPDATE zzimba_platform_account_settings
                   SET type = :type,
                       updated_at = :updated_at
                 WHERE id = :id
                   AND platform_account_id = :platform_account_id
            ");
            $upd->execute([
                ':type' => $type,
                ':updated_at' => $now,
                ':id' => $id,
                ':platform_account_id' => $accountId,
            ]);
            echo json_encode(['success' => true, 'message' => 'Setting updated']);
        } catch (Exception $e) {
            error_log("Error updating platform setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating setting']);
        }
        return;
    }

    if ($operation === 'remove') {
        $id = trim($data['id'] ?? '');
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID is required to remove']);
            return;
        }
        try {
            $del = $pdo->prepare("
                DELETE FROM zzimba_platform_account_settings
                 WHERE id = :id
                   AND platform_account_id = :platform_account_id
            ");
            $del->execute([
                ':id' => $id,
                ':platform_account_id' => $accountId,
            ]);
            echo json_encode(['success' => true, 'message' => 'Setting removed']);
        } catch (Exception $e) {
            error_log("Error removing platform setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error removing setting']);
        }
        return;
    }
}
