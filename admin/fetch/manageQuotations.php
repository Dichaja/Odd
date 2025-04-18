<?php
require_once __DIR__ . '/../../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS action_logs (
        log_id VARCHAR(26) PRIMARY KEY,
        action TEXT NOT NULL,
        created_at DATETIME NOT NULL
    )"
);

function logAction($pdo, $msg)
{
    $now   = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $logId = generateUlid();
    $stmt  = $pdo->prepare(
        "INSERT INTO action_logs (log_id, action, created_at) 
         VALUES (:id, :act, :cat)"
    );
    $stmt->execute([
        ':id'  => $logId,
        ':act' => $msg,
        ':cat' => $now
    ]);
}

$action = $_GET['action'] ?? '';

try {

    switch ($action) {

        case 'getServerTime':
            $now = new DateTime('now', new DateTimeZone('Africa/Kampala'));
            echo json_encode(['success' => true, 'now' => $now->format('Y-m-d H:i:s')]);
            break;

        case 'getQuotations':
            $start  = $_GET['start']  ?? '';
            $end    = $_GET['end']    ?? '';
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? 'all';

            if (!$start || !$end) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing date range']));
            }

            $startDt = str_replace('T', ' ', $start) . ':00';
            $endDt   = str_replace('T', ' ', $end)   . ':59';

            $query = "
                SELECT 
                    RFQ_ID, company_name, contact_person, email, phone,
                    site_location, status, created_at,
                    (SELECT COUNT(*) 
                     FROM request_for_quote_details d 
                     WHERE d.RFQ_ID = r.RFQ_ID
                    ) AS items_count
                FROM request_for_quote r
                WHERE r.created_at BETWEEN :start AND :end
            ";

            $params = [':start' => $startDt, ':end' => $endDt];

            if ($search) {
                $query .= "
                    AND (
                        r.company_name   LIKE :search1 OR
                        r.contact_person LIKE :search2 OR
                        r.site_location  LIKE :search3
                    )
                ";
                $params[':search1'] = "%$search%";
                $params[':search2'] = "%$search%";
                $params[':search3'] = "%$search%";
            }

            if ($status !== 'all') {
                $query .= " AND r.status = :status";
                $params[':status'] = $status;
            }

            $query .= " ORDER BY r.created_at DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stats = ['new' => 0, 'processing' => 0, 'processed' => 0, 'cancelled' => 0];

            foreach ($quotations as &$q) {
                $q['id'] = $q['RFQ_ID'];
                unset($q['RFQ_ID']);

                $statusLower = strtolower($q['status']);

                if ($statusLower === 'new') {
                    $q['status_class'] = 'bg-blue-100 text-blue-800';
                    $stats['new']++;
                } elseif ($statusLower === 'processing') {
                    $q['status_class'] = 'bg-yellow-100 text-yellow-800';
                    $stats['processing']++;
                } elseif ($statusLower === 'processed') {
                    $q['status_class'] = 'bg-green-100 text-green-800';
                    $stats['processed']++;
                } elseif ($statusLower === 'cancelled') {
                    $q['status_class'] = 'bg-red-100 text-red-800';
                    $stats['cancelled']++;
                } else {
                    $q['status_class'] = 'bg-gray-100 text-gray-800';
                }
            }

            echo json_encode(['success' => true, 'stats' => $stats, 'quotations' => $quotations]);
            break;

        case 'getRFQDetails':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing id']));
            }

            $stmt = $pdo->prepare(
                "SELECT RFQ_ID, company_name, contact_person, email, phone,
                        site_location, status, created_at
                 FROM request_for_quote
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$quotation) {
                http_response_code(404);
                die(json_encode(['error' => 'Quotation not found']));
            }

            $quotation['id'] = $quotation['RFQ_ID'];
            unset($quotation['RFQ_ID']);

            $statusLower = strtolower($quotation['status']);

            if ($statusLower === 'new') {
                $quotation['status_class'] = 'bg-blue-100 text-blue-800';
            } elseif ($statusLower === 'processing') {
                $quotation['status_class'] = 'bg-yellow-100 text-yellow-800';
            } elseif ($statusLower === 'processed') {
                $quotation['status_class'] = 'bg-green-100 text-green-800';
            } elseif ($statusLower === 'cancelled') {
                $quotation['status_class'] = 'bg-red-100 text-red-800';
            } else {
                $quotation['status_class'] = 'bg-gray-100 text-gray-800';
            }

            $stmt = $pdo->prepare(
                "SELECT brand_name, size, quantity
                 FROM request_for_quote_details
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'quotation' => $quotation, 'items' => $items]);
            break;

        case 'processRFQ':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing id']));
            }

            $stmt = $pdo->prepare(
                "SELECT status FROM request_for_quote WHERE RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || strtolower($row['status']) !== 'new') {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid action']));
            }

            $oldStatus = $row['status'];
            $now       = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "UPDATE request_for_quote
                 SET status = 'processing', updated_at = :now
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':now' => $now, ':id' => $id]);

            logAction($pdo, "Changed status for RFQ $id from $oldStatus to Processing");

            echo json_encode(['success' => true]);
            break;

        case 'completeRFQ':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing id']));
            }

            $stmt = $pdo->prepare(
                "SELECT status FROM request_for_quote WHERE RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || strtolower($row['status']) !== 'processing') {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid action']));
            }

            $oldStatus = $row['status'];
            $now       = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "UPDATE request_for_quote
                 SET status = 'processed', updated_at = :now
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':now' => $now, ':id' => $id]);

            logAction($pdo, "Changed status for RFQ $id from $oldStatus to Processed");

            echo json_encode(['success' => true]);
            break;

        case 'cancelRFQ':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing id']));
            }

            $stmt = $pdo->prepare(
                "SELECT status FROM request_for_quote WHERE RFQ_ID = :id"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || strtolower($row['status']) === 'processed') {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid action']));
            }

            $oldStatus = $row['status'];
            $now       = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "UPDATE request_for_quote
                 SET status = 'cancelled', updated_at = :now
                 WHERE RFQ_ID = :id"
            );
            $stmt->execute([':now' => $now, ':id' => $id]);

            logAction($pdo, "Changed status for RFQ $id from $oldStatus to Cancelled");

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
