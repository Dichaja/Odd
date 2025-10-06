<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

if (session_status() === PHP_SESSION_NONE)
    session_start();

$action = $_REQUEST['action'] ?? '';
$user = $_SESSION['user'] ?? null;
$userLogged = $user && !empty($user['logged_in']);
$userId = $user['user_id'] ?? null;

if (!$userLogged || !$userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    switch ($action) {
        case 'getStats':
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            $where = ["user_id = :uid"];
            $params = [':uid' => $userId];
            if ($startDate && $endDate) {
                $where[] = "DATE(created_at) BETWEEN :start AND :end";
                $params[':start'] = $startDate;
                $params[':end'] = $endDate;
            }
            $sql = "SELECT status, COUNT(*) cnt FROM buy_in_store_requests WHERE " . implode(' AND ', $where) . " GROUP BY status";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stats = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $k = strtolower($row['status']);
                if (isset($stats[$k]))
                    $stats[$k] = (int) $row['cnt'];
            }
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        case 'getRequests':
            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            $startDate = $_GET['start_date'] ?? '';
            $endDate = $_GET['end_date'] ?? '';
            $searchTerm = trim($_GET['search_term'] ?? '');
            $statusFilter = $_GET['status_filter'] ?? 'all';

            $where = ["bisr.user_id = :uid"];
            $params = [':uid' => $userId];

            if ($startDate && $endDate) {
                $where[] = "DATE(bisr.created_at) BETWEEN :start AND :end";
                $params[':start'] = $startDate;
                $params[':end'] = $endDate;
            }
            if ($searchTerm !== '') {
                $where[] = "(vs.name LIKE :q OR vs.region LIKE :q OR vs.district LIKE :q OR p.title LIKE :q OR pp.price_category LIKE :q)";
                $params[':q'] = "%{$searchTerm}%";
            }
            if ($statusFilter && $statusFilter !== 'all') {
                $where[] = "bisr.status = :status";
                $params[':status'] = $statusFilter;
            }
            $whereSql = 'WHERE ' . implode(' AND ', $where);

            $countSql = "
                SELECT COUNT(*)
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                $whereSql
            ";
            $c = $pdo->prepare($countSql);
            foreach ($params as $k => $v)
                $c->bindValue($k, $v);
            $c->execute();
            $total = (int) $c->fetchColumn();

            $dataSql = "
                SELECT 
                    bisr.id, bisr.user_id, bisr.store_product_id, bisr.pricing_id, bisr.visit_date, bisr.quantity, bisr.alt_contact, bisr.alt_email, bisr.notes, bisr.status, bisr.created_at, bisr.updated_at,
                    p.title AS product_title,
                    pp.price, pp.price_category, pp.package_size, psu.si_unit, ppn.package_name,
                    vs.name AS store_name, vs.region, vs.district, vs.subcounty, vs.parish, vs.address, vs.latitude, vs.longitude, vs.business_email, vs.business_phone
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN product_si_units psu ON pp.si_unit_id = psu.id
                JOIN product_package_name_mappings ppnm ON pp.package_mapping_id = ppnm.id
                JOIN product_package_name ppn ON ppnm.product_package_name_id = ppn.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                $whereSql
                ORDER BY bisr.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            $s = $pdo->prepare($dataSql);
            foreach ($params as $k => $v)
                $s->bindValue($k, $v);
            $s->bindValue(':limit', $limit, PDO::PARAM_INT);
            $s->bindValue(':offset', $offset, PDO::PARAM_INT);
            $s->execute();
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r)
                $r['total_value'] = (float) $r['price'] * (int) $r['quantity'];

            echo json_encode([
                'success' => true,
                'requestData' => [
                    'data' => $rows,
                    'total' => $total,
                    'page' => $page
                ]
            ]);
            break;

        case 'getRequestDetails':
            $rid = $_GET['id'] ?? '';
            if (!$rid) {
                echo json_encode(['success' => false, 'message' => 'Missing request ID']);
                break;
            }
            $sql = "
                SELECT 
                    bisr.*, 
                    p.title AS product_title, p.description AS product_description,
                    pp.price, pp.price_category, pp.package_size,
                    psu.si_unit, ppn.package_name,
                    vs.name AS store_name, vs.region, vs.district, vs.subcounty, vs.parish, vs.address, vs.latitude, vs.longitude,
                    vs.business_email, vs.business_phone
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_pricing pp ON bisr.pricing_id = pp.id
                JOIN product_si_units psu ON pp.si_unit_id = psu.id
                JOIN product_package_name_mappings ppnm ON pp.package_mapping_id = ppnm.id
                JOIN product_package_name ppn ON ppnm.product_package_name_id = ppn.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                WHERE bisr.id = :rid AND bisr.user_id = :uid
                LIMIT 1
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':rid' => $rid, ':uid' => $userId]);
            $req = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$req) {
                echo json_encode(['success' => false, 'message' => 'Request not found']);
                break;
            }
            $req['total_value'] = (float) $req['price'] * (int) $req['quantity'];
            echo json_encode(['success' => true, 'request' => $req]);
            break;

        case 'requestReschedule':
            $rid = $_POST['request_id'] ?? '';
            $visitDate = $_POST['visit_date'] ?? '';
            $note = trim($_POST['note'] ?? '');
            if (!$rid || !$visitDate) {
                echo json_encode(['success' => false, 'message' => 'Missing fields']);
                break;
            }
            if ($visitDate < date('Y-m-d')) {
                echo json_encode(['success' => false, 'message' => 'Visit date cannot be in the past']);
                break;
            }
            $chk = $pdo->prepare("SELECT status, notes FROM buy_in_store_requests WHERE id = :rid AND user_id = :uid LIMIT 1");
            $chk->execute([':rid' => $rid, ':uid' => $userId]);
            $row = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => 'Request not found']);
                break;
            }
            if (!in_array($row['status'], ['pending', 'confirmed'], true)) {
                echo json_encode(['success' => false, 'message' => 'Cannot reschedule this request']);
                break;
            }
            $newNotes = $row['notes'];
            if ($note !== '') {
                $prefix = ($newNotes && trim($newNotes) !== '') ? "\n" : "";
                $newNotes = ($newNotes ?? '') . $prefix . "[User] " . $note;
            }
            $u = $pdo->prepare("UPDATE buy_in_store_requests SET visit_date = :vd, notes = :n, updated_at = NOW() WHERE id = :rid AND user_id = :uid");
            $u->execute([':vd' => $visitDate, ':n' => $newNotes, ':rid' => $rid, ':uid' => $userId]);
            echo json_encode(['success' => true, 'message' => 'Reschedule submitted']);
            break;

        case 'cancelRequest':
            $rid = $_POST['request_id'] ?? '';
            if (!$rid) {
                echo json_encode(['success' => false, 'message' => 'Missing request ID']);
                break;
            }
            $u = $pdo->prepare("UPDATE buy_in_store_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :rid AND user_id = :uid AND status IN ('pending','confirmed')");
            $u->execute([':rid' => $rid, ':uid' => $userId]);
            if ($u->rowCount() > 0)
                echo json_encode(['success' => true, 'message' => 'Request cancelled']);
            else
                echo json_encode(['success' => false, 'message' => 'Unable to cancel request']);
            break;

        case 'sendEmailToStore':
            $rid = $_POST['request_id'] ?? '';
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            if (!$rid || $subject === '' || $message === '') {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }
            $q = $pdo->prepare("
                SELECT vs.business_email
                FROM buy_in_store_requests bisr
                JOIN store_products sp ON bisr.store_product_id = sp.id
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN vendor_stores vs ON sc.store_id = vs.id
                WHERE bisr.id = :rid AND bisr.user_id = :uid
                LIMIT 1
            ");
            $q->execute([':rid' => $rid, ':uid' => $userId]);
            $email = $q->fetchColumn();
            if (!$email) {
                echo json_encode(['success' => false, 'message' => 'Store email not found']);
                break;
            }
            usleep(300000);
            echo json_encode(['success' => true, 'message' => 'Email sent']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
