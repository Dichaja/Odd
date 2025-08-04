<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';

use Ulid\Ulid;

header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getUserInfo':
            getUserInfo();
            break;
        case 'getProductPackages':
            getProductPackages($pdo);
            break;
        case 'submitBuyInStore':
            requireLogin();
            submitBuyInStore($pdo, $currentUser);
            break;
        case 'getBuyInStoreHistory':
            requireLogin();
            getBuyInStoreHistory($pdo, $currentUser);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageBuyInStore.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();

function requireLogin()
{
    if (empty($_SESSION['user']['logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'session_expired' => true]);
        exit;
    }
}

function isValidUlid(string $id): bool
{
    return (bool) preg_match('/^[0-9A-Z]{26}$/i', $id);
}

function getUserInfo()
{
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'User not logged in', 'session_expired' => true]);
        return;
    }

    $user = [
        'username' => $_SESSION['user']['username'] ?? null,
        'email' => $_SESSION['user']['email'] ?? null,
        'phone' => $_SESSION['user']['phone'] ?? null,
        'first_name' => $_SESSION['user']['first_name'] ?? null,
        'last_name' => $_SESSION['user']['last_name'] ?? null,
        'name' => (isset($_SESSION['user']['first_name'], $_SESSION['user']['last_name']))
            ? $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']
            : ($_SESSION['user']['username'] ?? null)
    ];

    echo json_encode(['success' => true, 'user' => $user]);
}

function getProductPackages(PDO $pdo)
{
    $productId = $_GET['productId'] ?? '';

    if (empty($productId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                pp.id,
                pp.price,
                pp.price_category,
                pp.delivery_capacity,
                pp.package_size,
                psu.si_unit,
                ppn.package_name
            FROM 
                product_pricing            pp
            JOIN product_si_units          psu ON pp.si_unit_id       = psu.id
            JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN product_package_name      ppn ON ppm.product_package_name_id = ppn.id
            WHERE 
                pp.store_products_id = ?
            ORDER BY 
                pp.price_category, pp.price
        ");
        $stmt->execute([$productId]);
        echo json_encode(['success' => true, 'packages' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('Error fetching product packages: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error fetching product packages']);
    }
}

function submitBuyInStore(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data submitted']);
        return;
    }

    $requiredFields = ['packageId', 'visitDate', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required field: ' . $field]);
            return;
        }
    }

    if (!isValidUlid($data['packageId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }

    $visitDate = new DateTime($data['visitDate']);
    $today = new DateTime('today', new DateTimeZone('Africa/Kampala'));
    if ($visitDate < $today) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Visit date must be today or later']);
        return;
    }

    $quantity = intval($data['quantity']);
    if ($quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Quantity must be at least 1']);
        return;
    }

    try {
        $requestId = (string) Ulid::generate();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        // Fixed: Use 'pricing_id' instead of 'product_pricing_id' to match database schema
        $stmt = $pdo->prepare("
            INSERT INTO buy_in_store_requests (
                id, user_id, pricing_id, visit_date, quantity,
                alt_contact, alt_email, notes, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");

        $stmt->execute([
            $requestId,
            $currentUser,
            $data['packageId'],
            $data['visitDate'],
            $quantity,
            $data['altContact'] ?? null,
            $data['altEmail'] ?? null,
            $data['notes'] ?? null,
            $now,
            $now
        ]);

        logAction($pdo, "User {$currentUser} submitted a buy-in-store request for pricing ID {$data['packageId']}");

        $storeStmt = $pdo->prepare("
            SELECT vs.id   AS store_id,
                   vs.name AS store_name
            FROM   product_pricing pp
            JOIN   store_products  sp ON pp.store_products_id = sp.id
            JOIN   store_categories sc ON sp.store_category_id = sc.id
            JOIN   vendor_stores   vs ON sc.store_id = vs.id
            WHERE  pp.id = ?
            LIMIT 1
        ");
        $storeStmt->execute([$data['packageId']]);
        $storeData = $storeStmt->fetch(PDO::FETCH_ASSOC);

        if (!$storeData) {
            throw new Exception('Linked store not found for pricing ID ' . $data['packageId']);
        }

        $ns = new NotificationService($pdo);

        $userName = trim(
            ($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? '')
        ) ?: ($_SESSION['user']['username'] ?? 'User');

        $visitDatePretty = $visitDate->format('j M Y');

        $recipients = [
            [
                'type' => 'store',
                'id' => $storeData['store_id'],
                'message' => "$userName wants to visit your store \"{$storeData['store_name']}\" on $visitDatePretty."
            ],
            [
                'type' => 'admin',
                'id' => 'admin-global',
                'message' => "$userName submitted a visit request to \"{$storeData['store_name']}\" on $visitDatePretty."
            ]
        ];

        $ns->create(
            'visit_request',
            'New Visit Request',
            $recipients,
            BASE_URL . "/vendor-store/requests?id={$storeData['store_id']}",
            'high',
            $currentUser
        );

        echo json_encode([
            'success' => true,
            'message' => 'Your in-store purchase request has been submitted successfully!',
            'requestId' => $requestId
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        error_log('Error submitting buy-in-store request: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error submitting request']);
    }
}

function getBuyInStoreHistory(PDO $pdo, string $currentUser)
{
    try {
        // Fixed: Use 'pricing_id' instead of 'product_pricing_id' to match database schema
        $stmt = $pdo->prepare("
            SELECT 
                bir.id,
                bir.visit_date,
                bir.quantity,
                bir.status,
                bir.created_at,
                p.title         AS product_name,
                pp.price,
                pp.price_category,
                pp.package_size,
                psu.si_unit,
                ppn.package_name,
                vs.name         AS store_name
            FROM   buy_in_store_requests bir
            JOIN   product_pricing          pp  ON bir.pricing_id = pp.id
            JOIN   store_products           sp  ON pp.store_products_id = sp.id
            JOIN   products                 p   ON sp.product_id        = p.id
            JOIN   store_categories         sc  ON sp.store_category_id = sc.id
            JOIN   vendor_stores            vs  ON sc.store_id          = vs.id
            JOIN   product_si_units         psu ON pp.si_unit_id        = psu.id
            JOIN   product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN   product_package_name     ppn ON ppm.product_package_name_id = ppn.id
            WHERE  bir.user_id = ?
            ORDER BY bir.created_at DESC
        ");
        $stmt->execute([$currentUser]);
        echo json_encode(['success' => true, 'history' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        error_log('Error fetching buy-in-store history: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error fetching request history']);
    }
}

function logAction(PDO $pdo, string $action)
{
    try {
        $logId = (string) Ulid::generate();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare("INSERT INTO action_logs (log_id, action, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$logId, $action, $now]);
    } catch (Exception $e) {
        error_log('Error logging action: ' . $e->getMessage());
    }
}
?>