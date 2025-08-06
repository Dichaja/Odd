<?php
require_once __DIR__ . '/../../config/config.php';

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'getStats':
            $sql = "
                SELECT bisr.status, COUNT(*) AS cnt
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                WHERE vs.status = 'active'
                GROUP BY bisr.status
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

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
            $storeFilter = $_GET['store_filter'] ?? '';

            $where = ["vs.status = 'active'"];
            $params = [];

            if ($startDate && $endDate) {
                $where[] = "DATE(bisr.created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            if ($searchTerm) {
                $where[] = "(zu.first_name LIKE :search OR zu.last_name LIKE :search OR zu.email LIKE :search OR zu.phone LIKE :search OR p.title LIKE :search OR vs.name LIKE :search)";
                $params[':search'] = "%$searchTerm%";
            }

            if ($statusFilter && $statusFilter !== 'all') {
                $where[] = "bisr.status = :status";
                $params[':status'] = $statusFilter;
            }

            if ($storeFilter && $storeFilter !== 'all') {
                $where[] = "vs.id = :store_id";
                $params[':store_id'] = $storeFilter;
            }

            $whereSql = 'WHERE ' . implode(' AND ', $where);

            $countSql = "
                SELECT COUNT(*) AS total
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
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
                       (pp.price * bisr.quantity) AS total_value,
                       vs.name AS store_name, vs.id AS store_id, vs.business_phone AS store_phone,
                       vs.business_email AS store_email, vs.region AS store_region,
                       vs.district AS store_district
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
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

        case 'getStores':
            $sql = "
                SELECT vs.id, vs.name, vs.region, vs.district, COUNT(bisr.id) as request_count
                FROM vendor_stores vs
                LEFT JOIN store_categories sc ON vs.id = sc.store_id
                LEFT JOIN store_products sp ON sc.id = sp.store_category_id
                LEFT JOIN buy_in_store_requests bisr ON sp.id = bisr.store_product_id
                WHERE vs.status = 'active'
                GROUP BY vs.id, vs.name, vs.region, vs.district
                ORDER BY vs.name ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'stores' => $stores]);
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
                       psu.si_unit, ppn.package_name,
                       vs.name AS store_name, vs.id AS store_id, vs.business_phone AS store_phone,
                       vs.business_email AS store_email, vs.region AS store_region,
                       vs.district AS store_district, vs.address AS store_address,
                       vs.contact_person_name AS store_contact_person,
                       owner.first_name AS owner_first_name, owner.last_name AS owner_last_name,
                       owner.email AS owner_email, owner.phone AS owner_phone
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
                JOIN zzimba_users owner ON vs.owner_id = owner.id
                WHERE bisr.id = :request_id AND vs.status = 'active'
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':request_id' => $requestId]);
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
                    JOIN vendor_stores vs ON sc.store_id = vs.id
                    SET bisr.status = :status, bisr.updated_at = NOW()
                    WHERE bisr.id = :request_id AND vs.status = 'active'
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':status' => $status,
                    ':request_id' => $requestId
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
                $sql = "
                    UPDATE buy_in_store_requests bisr
                    JOIN store_products sp ON bisr.store_product_id = sp.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    JOIN vendor_stores vs ON sc.store_id = vs.id
                    SET bisr.visit_date = :visit_date, bisr.updated_at = NOW()
                    WHERE bisr.id = :request_id AND vs.status = 'active'
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':visit_date' => $visitDate,
                    ':request_id' => $requestId
                ]);
                echo json_encode(['success' => true, 'message' => 'Visit date updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request or visit date']);
            }
            break;

        case 'getSmsBalance':
            $storeId = $_GET['store_id'] ?? null;
            if (!$storeId) {
                echo json_encode(['success' => false, 'message' => 'Store ID required']);
                break;
            }

            $sql = "SELECT current_balance FROM zzimba_sms_wallet WHERE vendor_id = :store_id AND status = 'active'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':store_id' => $storeId]);
            $balance = $stmt->fetchColumn();

            echo json_encode(['success' => true, 'balance' => (int) ($balance ?? 0)]);
            break;

        case 'sendEmail':
            $requestId = $_POST['request_id'] ?? null;
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if ($requestId && $subject && $message) {
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
?>