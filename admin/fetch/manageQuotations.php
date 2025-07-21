<?php
require_once __DIR__ . '/../../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

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

function getQuotationData($pdo, $startDateTime, $endDateTime, $searchTerm, $statusFilter, $page, $limit)
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
            COALESCE(u.username, 'Unknown User') as user_name,
            COALESCE(CONCAT(u.first_name, ' ', u.last_name), u.username, 'Unknown User') as full_name,
            COALESCE(u.email, 'No Email') as user_email,
            COALESCE(u.phone, 'N/A') as phone,
            (SELECT COUNT(*) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_count,
            (SELECT SUM(COALESCE(d.unit_price, 0) * d.quantity) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_total
        FROM request_for_quote r
        LEFT JOIN zzimba_users u ON r.user_id = u.id
        WHERE r.created_at BETWEEN :start_date AND :end_date
    ";

    $params = [
        ':start_date' => $startDateTime,
        ':end_date' => $endDateTime
    ];

    if ($searchTerm) {
        $quotationQuery .= " AND (r.site_location LIKE :search OR u.username LIKE :search2 OR u.email LIKE :search3 OR u.phone LIKE :search4 OR r.RFQ_ID LIKE :search5 OR u.first_name LIKE :search6 OR u.last_name LIKE :search7)";
        $params[':search'] = "%$searchTerm%";
        $params[':search2'] = "%$searchTerm%";
        $params[':search3'] = "%$searchTerm%";
        $params[':search4'] = "%$searchTerm%";
        $params[':search5'] = "%$searchTerm%";
        $params[':search6'] = "%$searchTerm%";
        $params[':search7'] = "%$searchTerm%";
    }

    if ($statusFilter !== 'all') {
        $quotationQuery .= " AND r.status = :status";
        $params[':status'] = $statusFilter;
    }

    $quotationQuery .= " ORDER BY r.created_at DESC";

    $countQuery = "
        SELECT COUNT(*) as total
        FROM request_for_quote r
        LEFT JOIN zzimba_users u ON r.user_id = u.id
        WHERE r.created_at BETWEEN :start_date AND :end_date
    ";

    $countParams = [
        ':start_date' => $startDateTime,
        ':end_date' => $endDateTime
    ];

    if ($searchTerm) {
        $countQuery .= " AND (r.site_location LIKE :search OR u.username LIKE :search2 OR u.email LIKE :search3 OR u.phone LIKE :search4 OR r.RFQ_ID LIKE :search5 OR u.first_name LIKE :search6 OR u.last_name LIKE :search7)";
        $countParams[':search'] = "%$searchTerm%";
        $countParams[':search2'] = "%$searchTerm%";
        $countParams[':search3'] = "%$searchTerm%";
        $countParams[':search4'] = "%$searchTerm%";
        $countParams[':search5'] = "%$searchTerm%";
        $countParams[':search6'] = "%$searchTerm%";
        $countParams[':search7'] = "%$searchTerm%";
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

function getStatistics($pdo, $startDateTime, $endDateTime)
{
    $statsQuery = "
        SELECT 
            status,
            COUNT(*) as count
        FROM request_for_quote 
        WHERE created_at BETWEEN :start_date AND :end_date
        GROUP BY status
    ";
    $statsStmt = $pdo->prepare($statsQuery);
    $statsStmt->execute([
        ':start_date' => $startDateTime,
        ':end_date' => $endDateTime
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

function checkAndUpdateStatus($pdo, $rfqId)
{
    $stmt = $pdo->prepare("
        SELECT 
            r.status,
            r.transport,
            COUNT(d.RFQD_ID) as total_items,
            COUNT(CASE WHEN d.unit_price IS NOT NULL AND d.unit_price > 0 THEN 1 END) as priced_items
        FROM request_for_quote r
        LEFT JOIN request_for_quote_details d ON r.RFQ_ID = d.RFQ_ID
        WHERE r.RFQ_ID = :rfq_id
        GROUP BY r.RFQ_ID, r.status, r.transport
    ");
    $stmt->execute([':rfq_id' => $rfqId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result)
        return false;

    $currentStatus = $result['status'];
    $transport = floatval($result['transport']);
    $totalItems = intval($result['total_items']);
    $pricedItems = intval($result['priced_items']);

    if ($currentStatus === 'New' && ($pricedItems > 0 || $transport > 0)) {
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $updateStmt = $pdo->prepare("
            UPDATE request_for_quote 
            SET status = 'Processing', updated_at = :now 
            WHERE RFQ_ID = :rfq_id
        ");
        $updateStmt->execute([':now' => $now, ':rfq_id' => $rfqId]);

        logAction($pdo, "Status automatically changed to Processing for RFQ $rfqId due to pricing update");
        return true;
    }

    return false;
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
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            $searchTerm = $_GET['search_term'] ?? '';
            $statusFilter = $_GET['status_filter'] ?? 'all';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));

            if (!$startDate || !$endDate) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing date range']));
            }

            $startDateTime = $startDate . ' 00:00:00';
            $endDateTime = $endDate . ' 23:59:59';

            $quotationData = getQuotationData($pdo, $startDateTime, $endDateTime, $searchTerm, $statusFilter, $page, $limit);
            $stats = getStatistics($pdo, $startDateTime, $endDateTime);

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
                    r.updated_at,
                    COALESCE(u.username, 'Unknown User') as user_name,
                    COALESCE(CONCAT(u.first_name, ' ', u.last_name), u.username, 'Unknown User') as full_name,
                    COALESCE(u.email, 'No Email') as user_email,
                    COALESCE(u.phone, 'N/A') as phone
                 FROM request_for_quote r
                 LEFT JOIN zzimba_users u ON r.user_id = u.id
                 WHERE r.RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$quotation) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found']));
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

        case 'updateItemPrice':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $itemId = $input['item_id'] ?? '';
            $price = floatval($input['price'] ?? 0);

            if (!$itemId) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing item ID']));
            }

            $stmt = $pdo->prepare("
                SELECT d.unit_price, d.RFQ_ID, r.status 
                FROM request_for_quote_details d
                JOIN request_for_quote r ON d.RFQ_ID = r.RFQ_ID
                WHERE d.RFQD_ID = :id
            ");
            $stmt->execute([':id' => $itemId]);
            $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentItem) {
                http_response_code(404);
                die(json_encode(['error' => 'Item not found']));
            }

            $currentStatus = strtolower($currentItem['status']);
            if (in_array($currentStatus, ['paid', 'cancelled'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Cannot edit items for paid or cancelled quotations']));
            }

            $oldPrice = $currentItem['unit_price'];
            $rfqId = $currentItem['RFQ_ID'];

            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare(
                "UPDATE request_for_quote_details 
                 SET unit_price = :price, updated_at = :now 
                 WHERE RFQD_ID = :id"
            );
            $stmt->execute([
                ':price' => $price,
                ':now' => $now,
                ':id' => $itemId
            ]);

            $statusChanged = checkAndUpdateStatus($pdo, $rfqId);

            logAction($pdo, "Admin updated unit price for item $itemId from " . ($oldPrice ?: '0') . " to $price");

            echo json_encode([
                'success' => true,
                'status_changed' => $statusChanged,
                'old_price' => $oldPrice
            ]);
            break;

        case 'updateTransportCost':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $rfqId = $input['rfq_id'] ?? '';
            $transport = floatval($input['transport'] ?? 0);

            if (!$rfqId) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing RFQ ID']));
            }

            $stmt = $pdo->prepare("SELECT transport, status FROM request_for_quote WHERE RFQ_ID = :id");
            $stmt->execute([':id' => $rfqId]);
            $currentRfq = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentRfq) {
                http_response_code(404);
                die(json_encode(['error' => 'RFQ not found']));
            }

            $currentStatus = strtolower($currentRfq['status']);
            if (in_array($currentStatus, ['paid', 'cancelled'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Cannot edit transport cost for paid or cancelled quotations']));
            }

            $oldTransport = $currentRfq['transport'];

            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare(
                "UPDATE request_for_quote 
                 SET transport = :transport, updated_at = :now 
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([
                ':transport' => $transport,
                ':now' => $now,
                ':id' => $rfqId
            ]);

            $statusChanged = checkAndUpdateStatus($pdo, $rfqId);

            logAction($pdo, "Admin updated transport cost for RFQ $rfqId from " . ($oldTransport ?: '0') . " to $transport");

            echo json_encode([
                'success' => true,
                'status_changed' => $statusChanged,
                'old_transport' => $oldTransport
            ]);
            break;

        case 'updateQuotationStatus':
            if ($method !== 'POST') {
                http_response_code(405);
                die(json_encode(['error' => 'Method not allowed']));
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $rfqId = $input['rfq_id'] ?? '';
            $newStatus = $input['status'] ?? '';

            if (!$rfqId || !$newStatus) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing RFQ ID or status']));
            }

            $validStatuses = ['New', 'Processing', 'Processed'];
            if (!in_array($newStatus, $validStatuses)) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid status']));
            }

            $stmt = $pdo->prepare("SELECT status FROM request_for_quote WHERE RFQ_ID = :id");
            $stmt->execute([':id' => $rfqId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found']));
            }

            $currentStatus = strtolower($row['status']);
            if (in_array($currentStatus, ['paid', 'cancelled'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Cannot change status of paid or cancelled quotations']));
            }

            $oldStatus = $row['status'];
            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "UPDATE request_for_quote
                 SET status = :status, updated_at = :now
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':status' => $newStatus, ':now' => $now, ':id' => $rfqId]);

            logAction($pdo, "Admin changed status for RFQ $rfqId from $oldStatus to $newStatus");

            echo json_encode(['success' => true]);
            break;

        case 'exportQuotationData':
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';

            if (!$startDate || !$endDate) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing date range']));
            }

            $startDateTime = $startDate . ' 00:00:00';
            $endDateTime = $endDate . ' 23:59:59';

            $query = "
                SELECT 
                    r.user_id,
                    r.site_location,
                    r.coordinates,
                    r.transport,
                    r.fee_charged,
                    r.modified,
                    r.status,
                    r.created_at,
                    r.updated_at,
                    COALESCE(u.username, 'Unknown User') as user_name,
                    COALESCE(CONCAT(u.first_name, ' ', u.last_name), u.username, 'Unknown User') as full_name,
                    COALESCE(u.email, 'No Email') as user_email,
                    COALESCE(u.phone, 'N/A') as phone,
                    (SELECT COUNT(*) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_count,
                    (SELECT SUM(COALESCE(d.unit_price, 0) * d.quantity) FROM request_for_quote_details d WHERE d.RFQ_ID = r.RFQ_ID) as items_total
                FROM request_for_quote r
                LEFT JOIN zzimba_users u ON r.user_id = u.id
                WHERE r.created_at BETWEEN :start_date AND :end_date
                ORDER BY r.created_at DESC
            ";

            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':start_date' => $startDateTime,
                ':end_date' => $endDateTime
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $data]);
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