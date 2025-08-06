<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$action = $_REQUEST['action'] ?? '';
$storeId = $_SESSION['active_store'] ?? null;

if (!$storeId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No active store']);
    exit;
}

try {
    switch ($action) {
        case 'getStats':
            $sql = "
                SELECT bisr.status, COUNT(*) AS cnt
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                WHERE sc.store_id = :store_id
                GROUP BY bisr.status
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':store_id' => $storeId]);

            $stats = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats[$row['status']] = (int) $row['cnt'];
            }

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        case 'getRequests':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $startDate = $_GET['start_date'] ?? date('Y-m-d');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');
            $searchTerm = trim($_GET['search_term'] ?? '');
            $statusFilter = $_GET['status_filter'] ?? '';

            $where = ["sc.store_id = :store_id"];
            $params = [':store_id' => $storeId];

            if ($startDate && $endDate) {
                $where[] = "DATE(bisr.created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            if ($searchTerm) {
                $where[] = "(zu.first_name LIKE :search OR zu.last_name LIKE :search OR zu.email LIKE :search OR zu.phone LIKE :search OR p.title LIKE :search)";
                $params[':search'] = "%$searchTerm%";
            }

            if ($statusFilter && $statusFilter !== 'all') {
                $where[] = "bisr.status = :status";
                $params[':status'] = $statusFilter;
            }

            $whereSql = 'WHERE ' . implode(' AND ', $where);

            $countSql = "
                SELECT COUNT(*) AS total
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                $whereSql
            ";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $k => $v) {
                $countStmt->bindValue($k, $v);
            }
            $countStmt->execute();
            $total = (int) $countStmt->fetchColumn();

            $dataSql = "
                SELECT bisr.*, zu.first_name, zu.last_name, zu.email, zu.phone,
                       p.title AS product_title, pp.price, pp.price_category,
                       (pp.price * bisr.quantity) AS total_value
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                $whereSql
                ORDER BY bisr.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            $stmt = $pdo->prepare($dataSql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'requestData' => [
                    'data' => $requests,
                    'total' => $total,
                    'page' => $page
                ]
            ]);
            break;

        case 'getRequestDetails':
            $requestId = $_GET['id'] ?? null;
            if (!$requestId) {
                echo json_encode(['success' => false, 'message' => 'Missing request ID']);
                break;
            }

            $sql = "
                SELECT bisr.*, zu.first_name, zu.last_name, zu.email, zu.phone,
                       p.title AS product_title, p.description AS product_description,
                       pp.price, pp.price_category, pp.package_size,
                       psu.si_unit, ppn.package_name, vs.name AS store_name
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN product_si_units psu ON pp.si_unit_id = psu.id
                JOIN product_package_name_mappings ppnm ON pp.package_mapping_id = ppnm.id
                JOIN product_package_name ppn ON ppnm.product_package_name_id = ppn.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                WHERE bisr.id = :request_id AND sc.store_id = :store_id
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':request_id' => $requestId, ':store_id' => $storeId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                $request['total_value'] = $request['price'] * $request['quantity'];
                echo json_encode(['success' => true, 'request' => $request]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Request not found']);
            }
            break;

        case 'updateRequestStatus':
            $requestId = $_POST['request_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];

            if ($requestId && in_array($status, $allowed, true)) {
                $sql = "
                    UPDATE buy_in_store_requests bisr
                    JOIN store_products sp ON bisr.store_product_id = sp.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    SET bisr.status = :status, bisr.updated_at = NOW()
                    WHERE bisr.id = :request_id AND sc.store_id = :store_id
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':request_id' => $requestId,
                    ':store_id' => $storeId
                ]);
                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request or status']);
            }
            break;

        case 'updateVisitDate':
            $requestId = $_POST['request_id'] ?? null;
            $visitDate = $_POST['visit_date'] ?? null;

            if ($requestId && $visitDate) {
                // Validate that the date is not in the past
                $today = date('Y-m-d');
                if ($visitDate < $today) {
                    echo json_encode(['success' => false, 'message' => 'Visit date cannot be in the past']);
                    break;
                }

                $sql = "
                    UPDATE buy_in_store_requests bisr
                    JOIN store_products sp ON bisr.store_product_id = sp.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    SET bisr.visit_date = :visit_date, bisr.updated_at = NOW()
                    WHERE bisr.id = :request_id AND sc.store_id = :store_id
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':visit_date' => $visitDate,
                    ':request_id' => $requestId,
                    ':store_id' => $storeId
                ]);
                echo json_encode(['success' => true, 'message' => 'Visit date updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request or visit date']);
            }
            break;

        case 'getSmsBalance':
            $sql = "SELECT current_balance FROM zzimba_sms_wallet WHERE vendor_id = :store_id AND status = 'active'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':store_id' => $storeId]);
            $balance = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'balance' => (int) ($balance ?? 0)]);
            break;

        case 'sendSMS':
            $requestId = $_POST['request_id'] ?? null;
            $message = trim($_POST['message'] ?? '');

            if (!$requestId || !$message) {
                echo json_encode(['success' => false, 'message' => 'Missing request ID or message']);
                break;
            }

            $requestSql = "
                SELECT bisr.*, zu.phone, zu.first_name, zu.last_name
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                WHERE bisr.id = :request_id AND sc.store_id = :store_id
            ";
            $stmt = $pdo->prepare($requestSql);
            $stmt->execute([':request_id' => $requestId, ':store_id' => $storeId]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                echo json_encode(['success' => false, 'message' => 'Request not found']);
                break;
            }

            if (!$request['phone']) {
                echo json_encode(['success' => false, 'message' => 'Customer phone number not available']);
                break;
            }

            $balanceSql = "SELECT current_balance FROM zzimba_sms_wallet WHERE vendor_id = :store_id AND status = 'active'";
            $balanceStmt = $pdo->prepare($balanceSql);
            $balanceStmt->execute([':store_id' => $storeId]);
            $balance = (int) ($balanceStmt->fetchColumn() ?? 0);

            if ($balance < 1) {
                echo json_encode(['success' => false, 'message' => 'Insufficient SMS credits. Please purchase credits to send SMS.']);
                break;
            }

            $smsData = [
                'vendor_id' => $storeId,
                'message' => $message,
                'recipients' => [$request['phone']],
                'type' => 'single'
            ];

            $smsResponse = sendSmsViaCenter($smsData);

            if ($smsResponse['success']) {
                echo json_encode(['success' => true, 'message' => 'SMS sent successfully', 'new_balance' => $smsResponse['new_balance']]);
            } else {
                echo json_encode(['success' => false, 'message' => $smsResponse['message'] ?? 'Failed to send SMS']);
            }
            break;

        case 'sendEmail':
            $requestId = $_POST['request_id'] ?? null;
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($requestId && $subject && $message) {
                // Simulate email sending
                sleep(1);
                echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function sendSmsViaCenter($data)
{
    global $pdo;

    try {
        $vendorId = $data['vendor_id'];
        $message = $data['message'];
        $recipients = $data['recipients'];
        $type = $data['type'] ?? 'single';

        $smsWalletSql = "SELECT id, current_balance FROM zzimba_sms_wallet WHERE vendor_id = :vendor_id AND status = 'active'";
        $stmt = $pdo->prepare($smsWalletSql);
        $stmt->execute([':vendor_id' => $vendorId]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$wallet || $wallet['current_balance'] < count($recipients)) {
            return ['success' => false, 'message' => 'Insufficient SMS credits'];
        }

        $smsRate = 50.00;
        $smsParts = ceil(strlen($message) / 160);
        $creditsNeeded = count($recipients) * $smsParts;
        $totalCost = $creditsNeeded * $smsRate;

        if ($wallet['current_balance'] < $creditsNeeded) {
            return ['success' => false, 'message' => 'Insufficient SMS credits'];
        }

        $pdo->beginTransaction();

        $newBalance = $wallet['current_balance'] - $creditsNeeded;
        $updateWalletSql = "UPDATE zzimba_sms_wallet SET current_balance = :new_balance, updated_at = NOW() WHERE id = :wallet_id";
        $stmt = $pdo->prepare($updateWalletSql);
        $stmt->execute([':new_balance' => $newBalance, ':wallet_id' => $wallet['id']]);

        $historyId = generateUlid();
        $historySql = "
            INSERT INTO zzimba_sms_history 
            (id, vendor_id, sms_wallet_id, message, recipients, recipient_count, sms_parts, sms_rate, total_cost, credits_used, status, type, sent_at, balance_before, balance_after, created_at, updated_at)
            VALUES 
            (:id, :vendor_id, :wallet_id, :message, :recipients, :recipient_count, :sms_parts, :sms_rate, :total_cost, :credits_used, 'sent', :type, NOW(), :balance_before, :balance_after, NOW(), NOW())
        ";
        $stmt = $pdo->prepare($historySql);
        $stmt->execute([
            ':id' => $historyId,
            ':vendor_id' => $vendorId,
            ':wallet_id' => $wallet['id'],
            ':message' => $message,
            ':recipients' => json_encode($recipients),
            ':recipient_count' => count($recipients),
            ':sms_parts' => $smsParts,
            ':sms_rate' => $smsRate,
            ':total_cost' => $totalCost,
            ':credits_used' => $creditsNeeded,
            ':type' => $type,
            ':balance_before' => $wallet['current_balance'],
            ':balance_after' => $newBalance
        ]);

        $pdo->commit();

        return ['success' => true, 'message' => 'SMS sent successfully', 'credits_used' => $creditsNeeded, 'new_balance' => $newBalance];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        error_log("SMS sending error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to send SMS'];
    }
}
?>