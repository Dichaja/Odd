<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

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

// ————————————————————————————————————————————————————————————————————————
//  Ensure the cash accounts table exists
// ————————————————————————————————————————————————————————————————————————
try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS zzimba_cash_accounts (
            id CHAR(26) NOT NULL PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            type ENUM('mobile_money','bank','gateway') NOT NULL,
            provider VARCHAR(100) DEFAULT NULL,
            account_number VARCHAR(50) NOT NULL UNIQUE,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            current_balance DECIMAL(18,2) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB"
    );
} catch (PDOException $e) {
    error_log("Table creation error (cash_accounts): " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getCashAccounts':
            getCashAccounts($pdo);
            break;
        case 'getCashAccount':
            getCashAccount($pdo);
            break;
        case 'createCashAccount':
            createCashAccount($pdo);
            break;
        case 'updateCashAccount':
            updateCashAccount($pdo);
            break;
        case 'updateCashAccountStatus':
            updateCashAccountStatus($pdo);
            break;
        case 'deleteCashAccount':
            deleteCashAccount($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Error in manageCashAccounts: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// ───────────────────────────────────────────────────────────────────────────────
//  Endpoints
// ───────────────────────────────────────────────────────────────────────────────

function getCashAccounts(PDO $pdo)
{
    $stmt = $pdo->prepare(
        "SELECT id, name, type, provider, account_number AS number, status, current_balance AS balance, created_at, updated_at
           FROM zzimba_cash_accounts
          ORDER BY created_at DESC"
    );
    $stmt->execute();
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'accounts' => $accounts]);
}

function getCashAccount(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing account ID']);
        return;
    }
    $id = $_GET['id'];
    $stmt = $pdo->prepare(
        "SELECT id, name, type, provider, account_number AS number, status, current_balance AS balance, created_at, updated_at
           FROM zzimba_cash_accounts
          WHERE id = :id"
    );
    $stmt->execute([':id' => $id]);
    $acc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acc) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        return;
    }

    echo json_encode(['success' => true, 'account' => $acc]);
}

function createCashAccount(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $required = ['type', 'name', 'number'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst($field) . ' is required']);
            return;
        }
    }

    $type = $data['type'];
    $name = trim($data['name']);
    $number = trim($data['number']);
    $provider = trim($data['provider'] ?? '');

    $allowed = ['mobile_money', 'bank', 'gateway'];
    if (!in_array($type, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid account type']);
        return;
    }

    $chk = $pdo->prepare(
        "SELECT id FROM zzimba_cash_accounts
         WHERE name = :name OR account_number = :number"
    );
    $chk->execute([':name' => $name, ':number' => $number]);
    if ($chk->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name or number already in use']);
        return;
    }

    $id = generateUlid();
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare(
            "INSERT INTO zzimba_cash_accounts
                (id, name, type, provider, account_number, status, current_balance, created_at, updated_at)
             VALUES
                (:id, :name, :type, :provider, :number, 'active', 0, :created, :updated)"
        );
        $ins->execute([
            ':id' => $id,
            ':name' => $name,
            ':type' => $type,
            ':provider' => $provider,
            ':number' => $number,
            ':created' => $now,
            ':updated' => $now
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Account created', 'id' => $id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating cash account: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating account']);
    }
}

function updateCashAccount(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing account ID']);
        return;
    }
    $id = $data['id'];

    $chkExist = $pdo->prepare("SELECT id FROM zzimba_cash_accounts WHERE id = :id");
    $chkExist->execute([':id' => $id]);
    if ($chkExist->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        return;
    }

    $type = $data['type'] ?? null;
    $name = trim($data['name'] ?? '');
    $number = trim($data['number'] ?? '');
    $provider = trim($data['provider'] ?? '');

    if (!$type || !$name || !$number) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Type, name, and number are required']);
        return;
    }

    $allowed = ['mobile_money', 'bank', 'gateway'];
    if (!in_array($type, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid account type']);
        return;
    }

    $chk = $pdo->prepare(
        "SELECT id FROM zzimba_cash_accounts
         WHERE (name = :name OR account_number = :number)
           AND id <> :id"
    );
    $chk->execute([':name' => $name, ':number' => $number, ':id' => $id]);
    if ($chk->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name or number already in use']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare(
            "UPDATE zzimba_cash_accounts
                SET name = :name,
                    type = :type,
                    provider = :provider,
                    account_number = :number,
                    updated_at = :updated
              WHERE id = :id"
        );
        $upd->execute([
            ':name' => $name,
            ':type' => $type,
            ':provider' => $provider,
            ':number' => $number,
            ':updated' => $now,
            ':id' => $id
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Account updated']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating cash account: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating account']);
    }
}

function updateCashAccountStatus(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing account ID or status']);
        return;
    }
    $id = $data['id'];
    $status = $data['status'] === 'active' ? 'active' : 'inactive';

    $chk = $pdo->prepare("SELECT id FROM zzimba_cash_accounts WHERE id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare(
            "UPDATE zzimba_cash_accounts
                SET status = :status,
                    updated_at = :updated
              WHERE id = :id"
        );
        $upd->execute([
            ':status' => $status,
            ':updated' => $now,
            ':id' => $id
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Status updated']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating account status: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating status']);
    }
}

function deleteCashAccount(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing account ID']);
        return;
    }
    $id = $data['id'];

    $chk = $pdo->prepare("SELECT id FROM zzimba_cash_accounts WHERE id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Account not found']);
        return;
    }

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM zzimba_cash_accounts WHERE id = :id");
        $del->execute([':id' => $id]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Account deleted']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting cash account: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting account']);
    }
}
