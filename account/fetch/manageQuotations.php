<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    http_response_code(401);
    die(json_encode(['error' => 'Authentication required']));
}

if ($_SESSION['user']['is_admin']) {
    http_response_code(403);
    die(json_encode(['error' => 'Admin accounts cannot access user quotations']));
}

$userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;

if (!$userId) {
    http_response_code(400);
    die(json_encode(['error' => 'User ID not found in session']));
}

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS request_for_quote (
            RFQ_ID VARCHAR(26) NOT NULL PRIMARY KEY,
            user_id VARCHAR(26) NOT NULL,
            site_location VARCHAR(255) NOT NULL,
            coordinates VARCHAR(255) DEFAULT NULL,
            transport DECIMAL(10,2) DEFAULT 0.00,
            fee_charged DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            modified SMALLINT NOT NULL DEFAULT 0,
            status ENUM('New','Processing','Cancelled','Processed','Paid') NOT NULL DEFAULT 'New',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS request_for_quote_details (
            RFQD_ID VARCHAR(26) NOT NULL PRIMARY KEY,
            RFQ_ID VARCHAR(26) NOT NULL,
            brand_name VARCHAR(255) NOT NULL,
            size VARCHAR(255) NOT NULL,
            quantity INT(11) NOT NULL,
            unit_price DECIMAL(10,2) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (RFQ_ID) REFERENCES request_for_quote(RFQ_ID) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS action_logs (
            log_id VARCHAR(26) PRIMARY KEY,
            action TEXT NOT NULL,
            created_at DATETIME NOT NULL
        )"
    );
} catch (PDOException $e) {
    error_log('Table creation error: ' . $e->getMessage());
}

function logAction($pdo, $msg)
{
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $logId = generateUlid();
    $stmt = $pdo->prepare(
        "INSERT INTO action_logs (log_id, action, created_at) 
         VALUES (:id, :act, :cat)"
    );
    $stmt->execute([
        ':id' => $logId,
        ':act' => $msg,
        ':cat' => $now
    ]);
}

function getUserQuotationData($pdo, $userId, $searchTerm, $statusFilter, $page, $limit)
{
    $quotationQuery = "
        SELECT 
            r.RFQ_ID,
            r.user_id,
            r.site_location,
            r.coordinates,
            r.transport,
            r.fee_charged,
            r.modified,
            r.status,
            r.created_at,
            r.updated_at,
            (SELECT COUNT(*) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_count,
            (SELECT SUM(COALESCE(d.unit_price, 0) * d.quantity) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_total
        FROM request_for_quote r
        WHERE r.user_id = :user_id
    ";

    $params = [
        ':user_id' => $userId
    ];

    if ($searchTerm) {
        $quotationQuery .= " AND (r.site_location LIKE :search OR r.RFQ_ID LIKE :search2)";
        $params[':search'] = "%$searchTerm%";
        $params[':search2'] = "%$searchTerm%";
    }

    if ($statusFilter !== 'all') {
        $quotationQuery .= " AND r.status = :status";
        $params[':status'] = $statusFilter;
    }

    $quotationQuery .= " ORDER BY r.created_at DESC";

    $countQuery = "
        SELECT COUNT(*) as total
        FROM request_for_quote r
        WHERE r.user_id = :user_id
    ";

    $countParams = [
        ':user_id' => $userId
    ];

    if ($searchTerm) {
        $countQuery .= " AND (r.site_location LIKE :search OR r.RFQ_ID LIKE :search2)";
        $countParams[':search'] = "%$searchTerm%";
        $countParams[':search2'] = "%$searchTerm%";
    }

    if ($statusFilter !== 'all') {
        $countQuery .= " AND r.status = :status";
        $countParams[':status'] = $statusFilter;
    }

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($countParams);
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalCount = $countResult ? intval($countResult['total']) : 0;

    $offset = ($page - 1) * $limit;
    $quotationQuery .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare($quotationQuery);
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    $stmt->execute();
    $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['data' => $quotations, 'total' => $totalCount];
}

function getUserStatistics($pdo, $userId)
{
    $statsQuery = "
        SELECT 
            status,
            COUNT(*) as count
        FROM request_for_quote 
        WHERE user_id = :user_id
        GROUP BY status
    ";
    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute([
        ':user_id' => $userId
    ]);
    $statsResults = $statsStmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = ['new' => 0, 'processing' => 0, 'processed' => 0, 'cancelled' => 0, 'paid' => 0];
    foreach ($statsResults as $stat) {
        $statusKey = strtolower($stat['status']);
        if (isset($stats[$statusKey])) {
            $stats[$statusKey] = intval($stat['count']);
        }
    }

    return $stats;
}

function getUserWalletBalance($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT current_balance 
            FROM zzimba_wallets 
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? floatval($result['current_balance']) : 0.00;
    } catch (Exception $e) {
        error_log("Error fetching wallet balance: " . $e->getMessage());
        return 0.00;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
} else if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_GET['action'] ?? '';
}

try {
    switch ($action) {
        case 'getQuotations':
            $searchTerm = $_GET['search_term'] ?? '';
            $statusFilter = $_GET['status_filter'] ?? 'all';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));

            $quotationData = getUserQuotationData($pdo, $userId, $searchTerm, $statusFilter, $page, $limit);
            $stats = getUserStatistics($pdo, $userId);

            echo json_encode([
                'success' => true,
                'quotationData' => [
                    'data' => $quotationData['data'],
                    'total' => $quotationData['total'],
                    'page' => $page
                ],
                'stats' => $stats
            ]);
            break;

        case 'getRFQDetails':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing id']));
            }

            $stmt = $pdo->prepare(
                "SELECT 
                    r.RFQ_ID, 
                    r.user_id,
                    r.site_location, 
                    r.coordinates,
                    r.transport,
                    r.fee_charged,
                    r.modified,
                    r.status, 
                    r.created_at,
                    r.updated_at
                 FROM request_for_quote r
                 WHERE r.RFQ_ID = :id AND r.user_id = :user_id"
            );
            $stmt->execute([':id' => $id, ':user_id' => $userId]);
            $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$quotation) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found or access denied']));
            }

            $stmt = $pdo->prepare(
                "SELECT RFQD_ID, brand_name, size, quantity, unit_price
                 FROM request_for_quote_details
                 WHERE RFQ_ID = :id
                 ORDER BY created_at ASC"
            );
            $stmt->execute([':id' => $id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'quotation' => $quotation, 'items' => $items]);
            break;

        case 'getWalletBalance':
            $balance = getUserWalletBalance($pdo, $userId);
            echo json_encode([
                'success' => true,
                'balance' => $balance
            ]);
            break;

        case 'processQuotePayment':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $quotationId = $input['quotation_id'] ?? '';
            $amount = floatval($input['amount'] ?? 0);

            if (!$quotationId || $amount <= 0) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing quotation ID or invalid amount']));
            }

            $stmt = $pdo->prepare("
                SELECT status 
                FROM request_for_quote 
                WHERE RFQ_ID = :quotation_id AND user_id = :user_id
            ");
            $stmt->execute([':quotation_id' => $quotationId, ':user_id' => $userId]);
            $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$quotation) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found or access denied']));
            }

            if (strtolower($quotation['status']) !== 'processed') {
                http_response_code(400);
                die(json_encode(['error' => 'Can only pay for processed quotations']));
            }

            $result = CreditService::processQuotePayment([
                'user_id' => $userId,
                'amount' => $amount,
                'quotation_id' => $quotationId
            ]);

            if ($result['success']) {
                $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
                $stmt = $pdo->prepare("
                    UPDATE request_for_quote 
                    SET status = 'Paid', updated_at = :now 
                    WHERE RFQ_ID = :quotation_id AND user_id = :user_id
                ");
                $stmt->execute([':now' => $now, ':quotation_id' => $quotationId, ':user_id' => $userId]);

                logAction($pdo, "User paid for quotation $quotationId, amount: $amount UGX");
            }

            echo json_encode($result);
            break;

        case 'updateQuotation':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $rfqId = $input['rfq_id'] ?? '';
            $items = $input['items'] ?? [];
            $itemsToRemove = $input['items_to_remove'] ?? [];

            if (!$rfqId) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing RFQ ID']));
            }

            if (empty($items) && empty($itemsToRemove)) {
                http_response_code(400);
                die(json_encode(['error' => 'No changes to save']));
            }

            $stmt = $pdo->prepare("SELECT status, modified FROM request_for_quote WHERE RFQ_ID = :rfq_id AND user_id = :user_id");
            $stmt->execute([':rfq_id' => $rfqId, ':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found or access denied']));
            }

            $currentStatus = $result['status'];
            $isModified = intval($result['modified']);

            if (strtolower($currentStatus) !== 'processed') {
                http_response_code(400);
                die(json_encode(['error' => 'Can only edit quotations with Processed status']));
            }

            if ($isModified === 1) {
                http_response_code(400);
                die(json_encode(['error' => 'This quotation has already been modified and cannot be edited again']));
            }

            $pdo->beginTransaction();

            try {
                $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

                if (!empty($itemsToRemove)) {
                    $placeholders = str_repeat('?,', count($itemsToRemove) - 1) . '?';
                    $stmt = $pdo->prepare("DELETE FROM request_for_quote_details WHERE RFQD_ID IN ($placeholders) AND RFQ_ID = ?");
                    $params = array_merge($itemsToRemove, [$rfqId]);
                    $stmt->execute($params);
                }

                foreach ($items as $item) {
                    $stmt = $pdo->prepare("
                        UPDATE request_for_quote_details 
                        SET quantity = :quantity, updated_at = :now 
                        WHERE RFQD_ID = :id AND RFQ_ID = :rfq_id
                    ");
                    $stmt->execute([
                        ':quantity' => intval($item['quantity']),
                        ':now' => $now,
                        ':id' => $item['id'],
                        ':rfq_id' => $rfqId
                    ]);
                }

                $stmt = $pdo->prepare("
                    UPDATE request_for_quote 
                    SET status = 'Processing', modified = 1, updated_at = :now 
                    WHERE RFQ_ID = :rfq_id AND user_id = :user_id
                ");
                $stmt->execute([':now' => $now, ':rfq_id' => $rfqId, ':user_id' => $userId]);

                $pdo->commit();

                logAction($pdo, "User updated quotation $rfqId, status changed to Processing");

                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'cancelQuotation':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $rfqId = $input['rfq_id'] ?? '';

            if (!$rfqId) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing RFQ ID']));
            }

            $stmt = $pdo->prepare("SELECT status FROM request_for_quote WHERE RFQ_ID = :rfq_id AND user_id = :user_id");
            $stmt->execute([':rfq_id' => $rfqId, ':user_id' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found or access denied']));
            }

            $currentStatus = strtolower($result['status']);

            if (!in_array($currentStatus, ['new', 'processing', 'processed'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Can only cancel quotations with New, Processing, or Processed status']));
            }

            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("
                UPDATE request_for_quote 
                SET status = 'Cancelled', updated_at = :now 
                WHERE RFQ_ID = :rfq_id AND user_id = :user_id
            ");
            $stmt->execute([':now' => $now, ':rfq_id' => $rfqId, ':user_id' => $userId]);

            logAction($pdo, "User cancelled quotation $rfqId");

            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>