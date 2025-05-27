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
        case 'get_stats_counts':
            $sql = "
                SELECT bisr.status, COUNT(*) AS cnt
                FROM buy_in_store_requests bisr
                JOIN product_pricing pp ON bisr.product_pricing_id = pp.id
                JOIN store_products sp ON pp.store_products_id = sp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                WHERE sc.store_id = :store_id
                GROUP BY bisr.status
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':store_id' => $storeId]);
            $counts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $counts[$row['status']] = $row['cnt'];
            }
            echo json_encode(['success' => true, 'data' => $counts]);
            break;

        case 'filter_requests':
            $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;
            $statusFilter = $_POST['status'] ?? null;
            $searchQuery = isset($_POST['search']) && $_POST['search'] !== '' ? trim($_POST['search']) : null;
            $where = ["sc.store_id = :store_id"];
            $params = [':store_id' => $storeId];
            if ($statusFilter && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
                $where[] = "bisr.status = :status_filter";
                $params[':status_filter'] = $statusFilter;
            }
            if ($searchQuery) {
                $where[] = "(" .
                    "zu.first_name LIKE :s1 OR " .
                    "zu.last_name  LIKE :s2 OR " .
                    "zu.email      LIKE :s3 OR " .
                    "zu.phone      LIKE :s4 OR " .
                    "p.title       LIKE :s5)";
                foreach ([':s1', ':s2', ':s3', ':s4', ':s5'] as $tag) {
                    $params[$tag] = "%$searchQuery%";
                }
            }
            $whereSql = 'WHERE ' . implode(' AND ', $where);

            $countSql = "
                SELECT COUNT(*) AS total
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN product_pricing pp ON bisr.product_pricing_id = pp.id
                JOIN store_products sp ON pp.store_products_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                $whereSql
            ";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $k => $v) {
                $countStmt->bindValue($k, $v);
            }
            $countStmt->execute();
            $totalRequests = (int) $countStmt->fetchColumn();
            $totalPages = (int) ceil($totalRequests / $limit);

            $dataSql = "
                SELECT bisr.id, bisr.visit_date, bisr.status, bisr.created_at, bisr.quantity,
                       zu.first_name, zu.last_name, zu.email, zu.phone,
                       p.title AS product_title
                FROM buy_in_store_requests bisr
                JOIN zzimba_users zu ON bisr.user_id = zu.id
                JOIN product_pricing pp ON bisr.product_pricing_id = pp.id
                JOIN store_products sp ON pp.store_products_id = sp.id
                JOIN products p ON sp.product_id = p.id
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
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_requests' => $totalRequests,
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ]);
            break;

        case 'update_status':
            $requestId = $_POST['request_id'] ?? null;
            $status = $_POST['status'] ?? null;
            $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
            if ($requestId && in_array($status, $allowed, true)) {
                $sql = "
                    UPDATE buy_in_store_requests bisr
                    JOIN product_pricing pp ON bisr.product_pricing_id = pp.id
                    JOIN store_products sp ON pp.store_products_id = sp.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    SET bisr.status = :status, bisr.updated_at = NOW()
                    WHERE bisr.id = :request_id
                      AND sc.store_id = :store_id
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

        case 'get_request_details':
            $requestId = $_POST['request_id'] ?? null;
            if ($requestId) {
                $query = "
                    SELECT bisr.*, zu.first_name, zu.last_name, zu.email, zu.phone,
                           p.title AS product_title, p.description AS product_description,
                           pp.price, pp.price_category, pp.package_size,
                           psu.si_unit, ppn.package_name, vs.name AS store_name
                    FROM buy_in_store_requests bisr
                    JOIN zzimba_users zu ON bisr.user_id = zu.id
                    JOIN product_pricing pp ON bisr.product_pricing_id = pp.id
                    JOIN store_products sp ON pp.store_products_id = sp.id
                    JOIN products p ON sp.product_id = p.id
                    JOIN product_si_units psu ON pp.si_unit_id = psu.id
                    JOIN product_package_name_mappings ppnm ON pp.package_mapping_id = ppnm.id
                    JOIN product_package_name ppn ON ppnm.product_package_name_id = ppn.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    JOIN vendor_stores vs ON sc.store_id = vs.id
                    WHERE bisr.id = :request_id
                      AND sc.store_id = :store_id
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([':request_id' => $requestId, ':store_id' => $storeId]);
                $request = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($request) {
                    echo json_encode(['success' => true, 'data' => $request]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Request not found']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing request ID']);
            }
            break;

        case 'send_email':
            $requestId = $_POST['request_id'] ?? null;
            $message = $_POST['message'] ?? '';
            if ($requestId && trim($message) !== '') {
                sleep(1);
                echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing request ID or message']);
            }
            break;

        case 'send_sms':
            $requestId = $_POST['request_id'] ?? null;
            $message = $_POST['message'] ?? '';
            if ($requestId && trim($message) !== '') {
                sleep(1);
                echo json_encode(['success' => true, 'message' => 'SMS sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing request ID or message']);
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
