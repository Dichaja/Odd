<?php
require_once __DIR__ . '/../../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

// Check if user is logged in
if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    http_response_code(401);
    die(json_encode(['error' => 'Authentication required']));
}

// Check if user is admin (admins shouldn't access user endpoints)
if ($_SESSION['user']['is_admin']) {
    http_response_code(403);
    die(json_encode(['error' => 'Admin accounts cannot access user quotations']));
}

$userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;

if (!$userId) {
    http_response_code(400);
    die(json_encode(['error' => 'User ID not found in session']));
}

// Create tables if they don't exist
try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS request_for_quote (
            RFQ_ID VARCHAR(26) NOT NULL PRIMARY KEY,
            user_id VARCHAR(26) NOT NULL,
            site_location VARCHAR(255) NOT NULL,
            coordinates VARCHAR(255) DEFAULT NULL,
            transport DECIMAL(10,2) DEFAULT 0.00,
            fee_charged DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('New','Processing','Cancelled','Processed') NOT NULL DEFAULT 'New',
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
    // Get quotation data with items total for specific user
    $quotationQuery = "
        SELECT 
            r.RFQ_ID,
            r.user_id,
            r.site_location,
            r.coordinates,
            r.transport,
            r.fee_charged,
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

    // Get total count first
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

    // Add pagination to main query
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

    $stats = ['new' => 0, 'processing' => 0, 'processed' => 0, 'cancelled' => 0];
    foreach ($statsResults as $stat) {
        $statusKey = strtolower($stat['status']);
        if (isset($stats[$statusKey])) {
            $stats[$statusKey] = intval($stat['count']);
        }
    }

    return $stats;
}

function checkAndUpdateStatusForUser($pdo, $rfqId, $userId)
{
    // Verify the RFQ belongs to the user
    $stmt = $pdo->prepare("SELECT status FROM request_for_quote WHERE RFQ_ID = :rfq_id AND user_id = :user_id");
    $stmt->execute([':rfq_id' => $rfqId, ':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result)
        return false;

    $currentStatus = $result['status'];

    // If status is New, change to Processing when user updates quantity
    // If status is Processed, change back to Processing when user updates quantity
    if ($currentStatus === 'New' || $currentStatus === 'Processed') {
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $updateStmt = $pdo->prepare("
            UPDATE request_for_quote 
            SET status = 'Processing', updated_at = :now 
            WHERE RFQ_ID = :rfq_id AND user_id = :user_id
        ");
        $updateStmt->execute([':now' => $now, ':rfq_id' => $rfqId, ':user_id' => $userId]);

        logAction($pdo, "User updated quantity for RFQ $rfqId, status changed from $currentStatus to Processing");
        return true;
    }

    return false;
}

// Handle different request methods
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

            // Verify the RFQ belongs to the user
            $stmt = $pdo->prepare(
                "SELECT 
                    r.RFQ_ID, 
                    r.user_id,
                    r.site_location, 
                    r.coordinates,
                    r.transport,
                    r.fee_charged,
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

        case 'updateItemQuantity':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $itemId = $input['item_id'] ?? '';
            $quantity = intval($input['quantity'] ?? 1);

            if (!$itemId || $quantity < 1) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing or invalid item ID or quantity']));
            }

            // Get current quantity and verify ownership
            $stmt = $pdo->prepare("
                SELECT d.quantity, d.RFQ_ID 
                FROM request_for_quote_details d
                JOIN request_for_quote r ON d.RFQ_ID = r.RFQ_ID
                WHERE d.RFQD_ID = :id AND r.user_id = :user_id
            ");
            $stmt->execute([':id' => $itemId, ':user_id' => $userId]);
            $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentItem) {
                http_response_code(404);
                die(json_encode(['error' => 'Item not found or access denied']));
            }

            $oldQuantity = $currentItem['quantity'];
            $rfqId = $currentItem['RFQ_ID'];

            // Check if RFQ can be modified (only Cancelled status cannot be modified)
            $statusStmt = $pdo->prepare("SELECT status FROM request_for_quote WHERE RFQ_ID = :rfq_id");
            $statusStmt->execute([':rfq_id' => $rfqId]);
            $statusResult = $statusStmt->fetch(PDO::FETCH_ASSOC);

            if (!$statusResult || strtolower($statusResult['status']) === 'cancelled') {
                http_response_code(400);
                die(json_encode(['error' => 'Cannot modify items in cancelled quotations']));
            }

            // Update the quantity
            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare(
                "UPDATE request_for_quote_details 
                 SET quantity = :quantity, updated_at = :now 
                 WHERE RFQD_ID = :id"
            );
            $stmt->execute([
                ':quantity' => $quantity,
                ':now' => $now,
                ':id' => $itemId
            ]);

            // Check if status should be updated
            $statusChanged = checkAndUpdateStatusForUser($pdo, $rfqId, $userId);

            logAction($pdo, "User updated quantity for item $itemId from $oldQuantity to $quantity");

            echo json_encode([
                'success' => true,
                'status_changed' => $statusChanged,
                'old_quantity' => $oldQuantity
            ]);
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