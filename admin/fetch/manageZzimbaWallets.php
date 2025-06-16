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

// helper to generate the 10-digit wallet number
function generateWalletNumber(PDO $pdo, string $walletId): string
{
    $yy = date('y');       // e.g. "26"
    $y1 = $yy[0];
    $y2 = $yy[1];

    $mm = date('m');       // e.g. "11"
    $m1 = $mm[0];
    $m2 = $mm[1];

    do {
        $seq = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $walletNumber =
            $y1
            . $seq[0]
            . $y2
            . $seq[1]
            . $seq[2]
            . $seq[3]
            . $seq[4]
            . $m1
            . $seq[5]
            . $m2;

        $check = $pdo->prepare('SELECT 1 FROM zzimba_wallets WHERE wallet_number = ? LIMIT 1');
        $check->execute([$walletNumber]);
        $exists = (bool) $check->fetchColumn();
    } while ($exists);

    return $walletNumber;
}

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

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS zzimba_wallets (
            wallet_id       CHAR(26) NOT NULL PRIMARY KEY,
            wallet_number   CHAR(10) NOT NULL UNIQUE,
            owner_type      ENUM('USER','VENDOR','PLATFORM') NOT NULL,
            user_id         VARCHAR(26) DEFAULT NULL,
            vendor_id       VARCHAR(26) DEFAULT NULL,
            wallet_name     VARCHAR(100) NOT NULL,
            current_balance DECIMAL(18,2) NOT NULL DEFAULT 0,
            status          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
            created_at      DATETIME NOT NULL,
            updated_at      DATETIME NOT NULL,
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

$action = $_REQUEST['action'] ?? '';

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
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    error_log("Error in manageZzimbaWallets: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function getZzimbaWallets(PDO $pdo)
{
    $stmt = $pdo->prepare("
        SELECT
            wallet_id,
            wallet_number,
            owner_type,
            user_id,
            vendor_id,
            wallet_name,
            current_balance,
            status,
            created_at,
            updated_at
        FROM zzimba_wallets
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $wallets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'wallets' => $wallets]);
}

function getZzimbaWallet(PDO $pdo)
{
    $walletNumber = trim($_REQUEST['wallet_number'] ?? '');
    if ($walletNumber === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet_number']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT
            wallet_id,
            wallet_number,
            owner_type,
            user_id,
            vendor_id,
            wallet_name,
            current_balance,
            status,
            created_at,
            updated_at
        FROM zzimba_wallets
        WHERE wallet_number = :wallet_number
    ");
    $stmt->execute([':wallet_number' => $walletNumber]);
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

    if ($owner_type !== 'PLATFORM' || $name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid owner_type or wallet_name']);
        return;
    }

    $wallet_id = generateUlid();
    $wallet_number = generateWalletNumber($pdo, $wallet_id);
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO zzimba_wallets
              (wallet_id, wallet_number, owner_type, user_id, vendor_id,
               wallet_name, current_balance, status, created_at, updated_at)
            VALUES
              (:wallet_id, :wallet_number, :owner_type, NULL, NULL,
               :wallet_name, 0, 'active', :now, :now)
        ");
        $stmt->execute([
            ':wallet_id' => $wallet_id,
            ':wallet_number' => $wallet_number,
            ':owner_type' => $owner_type,
            ':wallet_name' => $name,
            ':now' => $now,
        ]);
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'wallet_number' => $wallet_number
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
    $walletNumber = trim($data['wallet_number'] ?? '');
    $wallet_name = trim($data['wallet_name'] ?? '');
    $status = $data['status'] ?? '';

    if ($walletNumber === '' || $wallet_name === '' || !in_array($status, ['active', 'inactive', 'suspended'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    $stmt = $pdo->prepare("SELECT wallet_id FROM zzimba_wallets WHERE wallet_number = :wallet_number");
    $stmt->execute([':wallet_number' => $walletNumber]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wallet not found']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare("
            UPDATE zzimba_wallets
               SET wallet_name = :wallet_name,
                   status      = :status,
                   updated_at  = :now
             WHERE wallet_number = :wallet_number
        ");
        $upd->execute([
            ':wallet_name' => $wallet_name,
            ':status' => $status,
            ':now' => $now,
            ':wallet_number' => $walletNumber,
        ]);
        $pdo->commit();
        echo json_encode(['success' => true]);
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
    $walletNumber = trim($data['wallet_number'] ?? '');

    if ($walletNumber === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet_number']);
        return;
    }

    $stmt = $pdo->prepare("SELECT owner_type FROM zzimba_wallets WHERE wallet_number = :wallet_number");
    $stmt->execute([':wallet_number' => $walletNumber]);
    $w = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$w || $w['owner_type'] !== 'PLATFORM') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete this wallet']);
        return;
    }

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM zzimba_wallets WHERE wallet_number = :wallet_number");
        $del->execute([':wallet_number' => $walletNumber]);
        $pdo->commit();
        echo json_encode(['success' => true]);
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
    $walletNumber = trim($data['wallet_number'] ?? '');
    $filter = strtolower(trim($data['filter'] ?? 'all'));
    $start = $data['start'] ?? null;
    $end = $data['end'] ?? null;

    if ($walletNumber === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing wallet_number']);
        return;
    }

    $stmt = $pdo->prepare("SELECT wallet_id FROM zzimba_wallets WHERE wallet_number = :wallet_number");
    $stmt->execute([':wallet_number' => $walletNumber]);
    $walletId = $stmt->fetchColumn();
    if (!$walletId) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Wallet not found']);
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

function managePlatformAccounts(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $operation = $data['operation'] ?? '';
    $walletNumber = trim($data['wallet_number'] ?? '');

    //
    // LIST
    //
    if ($operation === 'list') {
        if ($walletNumber !== '') {
            // get the internal wallet_id for this wallet_number
            $stmt1 = $pdo->prepare("
                SELECT wallet_id
                  FROM zzimba_wallets
                 WHERE wallet_number = :num
            ");
            $stmt1->execute([':num' => $walletNumber]);
            $pid = $stmt1->fetchColumn();
            if (!$pid) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid wallet_number'
                ]);
                return;
            }

            // return all settings for that wallet, plus wallet_number
            $stmt = $pdo->prepare("
                SELECT
                    s.id,
                    s.platform_account_id AS wallet_id,
                    w.wallet_number,
                    s.type,
                    s.created_at,
                    s.updated_at
                  FROM zzimba_platform_account_settings s
                  JOIN zzimba_wallets w
                    ON s.platform_account_id = w.wallet_id
                 WHERE s.platform_account_id = :pid
                 ORDER BY s.created_at DESC
            ");
            $stmt->execute([':pid' => $pid]);
        } else {
            // list *all* settings across all platform wallets
            $stmt = $pdo->query("
                SELECT
                    s.id,
                    s.platform_account_id AS wallet_id,
                    w.wallet_number,
                    s.type,
                    s.created_at,
                    s.updated_at
                  FROM zzimba_platform_account_settings s
                  JOIN zzimba_wallets w
                    ON s.platform_account_id = w.wallet_id
                 ORDER BY s.created_at DESC
            ");
        }

        echo json_encode([
            'success' => true,
            'settings' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
        return;
    }

    //
    // VALIDATE OPERATION + WALLET NUMBER
    //
    if (!in_array($operation, ['add', 'update', 'remove'], true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid operation'
        ]);
        return;
    }

    if ($walletNumber === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing wallet_number'
        ]);
        return;
    }

    // resolve the internal platform_account_id
    $stmt2 = $pdo->prepare("
        SELECT wallet_id
          FROM zzimba_wallets
         WHERE wallet_number = :num
           AND owner_type   = 'PLATFORM'
    ");
    $stmt2->execute([':num' => $walletNumber]);
    $walletId = $stmt2->fetchColumn();

    if (!$walletId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid wallet_number'
        ]);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))
        ->format('Y-m-d H:i:s');

    //
    // ADD
    //
    if ($operation === 'add') {
        $type = trim($data['type'] ?? '');
        if ($type === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Type is required'
            ]);
            return;
        }

        $id = generateUlid(); // your ULID helper

        try {
            $ins = $pdo->prepare("
                INSERT INTO zzimba_platform_account_settings
                    (id, platform_account_id, type, created_at, updated_at)
                VALUES
                    (:id, :pid,           :type, :now,       :now)
            ");
            $ins->execute([
                ':id' => $id,
                ':pid' => $walletId,
                ':type' => $type,
                ':now' => $now
            ]);

            echo json_encode([
                'success' => true,
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log("Error adding setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error adding setting'
            ]);
        }
        return;
    }

    //
    // UPDATE
    //
    if ($operation === 'update') {
        $id = trim($data['id'] ?? '');
        $type = trim($data['type'] ?? '');
        if ($id === '' || $type === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID and type required'
            ]);
            return;
        }

        try {
            $upd = $pdo->prepare("
                UPDATE zzimba_platform_account_settings
                   SET type       = :type,
                       updated_at = :now
                 WHERE id                  = :id
                   AND platform_account_id = :pid
            ");
            $upd->execute([
                ':type' => $type,
                ':now' => $now,
                ':id' => $id,
                ':pid' => $walletId
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Error updating setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error updating setting'
            ]);
        }
        return;
    }

    //
    // REMOVE
    //
    if ($operation === 'remove') {
        $id = trim($data['id'] ?? '');
        if ($id === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID required'
            ]);
            return;
        }

        try {
            $del = $pdo->prepare("
                DELETE
                  FROM zzimba_platform_account_settings
                 WHERE id                  = :id
                   AND platform_account_id = :pid
            ");
            $del->execute([
                ':id' => $id,
                ':pid' => $walletId
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Error removing setting: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error removing setting'
            ]);
        }
        return;
    }
}
