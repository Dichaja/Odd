<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';
require_once __DIR__ . '/../sms/SMS.php';
require_once __DIR__ . '/../lib/ZzimbaCreditModule.php';

use Ulid\Ulid;

header('Content-Type: application/json');

date_default_timezone_set('Africa/Kampala');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? ($_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null) : null;

ensureBuyInStoreTable($pdo);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getUserInfo':
            getUserInfo();
            break;
        case 'getProductPackages':
            getProductPackages($pdo);
            break;
        case 'getWalletBalance':
            requireLogin();
            getWalletBalance($currentUser);
            break;
        case 'getBuyInStoreCharge':
            requireLogin();
            getBuyInStoreCharge();
            break;
        case 'checkWalletBalance':
            requireLogin();
            checkWalletBalanceCombined($currentUser);
            break;
        case 'previewBuyInStore':
            requireLogin();
            previewBuyInStore($pdo, $currentUser);
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

function ensureBuyInStoreTable(PDO $pdo)
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `buy_in_store_requests` (
            `id` VARCHAR(26) NOT NULL,
            `user_id` VARCHAR(26) NOT NULL,
            `store_product_id` VARCHAR(26) NOT NULL,
            `pricing_id` VARCHAR(26) NOT NULL,
            `visit_date` DATE NOT NULL,
            `quantity` INT NOT NULL,
            `alt_contact` VARCHAR(20) DEFAULT NULL,
            `alt_email`   VARCHAR(100) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `status` ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`user_id`) REFERENCES `zzimba_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`store_product_id`) REFERENCES `store_products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`pricing_id`) REFERENCES `product_pricing`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

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

function jsonInput(): array
{
    static $cache;
    if ($cache !== null)
        return $cache;
    $raw = file_get_contents('php://input');
    $cache = $raw ? (json_decode($raw, true) ?: []) : [];
    return $cache;
}

function param(string $key, $default = null)
{
    $j = jsonInput();
    if (array_key_exists($key, $_GET))
        return $_GET[$key];
    if (array_key_exists($key, $j))
        return $j[$key];
    return $default;
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
    $productId = param('productId', '');
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

function getWalletBalance(string $userId)
{
    $res = \ZzimbaCreditModule\CreditService::getWallet('USER', $userId);
    if (empty($res['success'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No Zzimba Wallet found']);
        return;
    }
    $wallet = $res['wallet'];
    echo json_encode([
        'success' => true,
        'wallet' => [
            'wallet_id' => $wallet['wallet_id'],
            'wallet_number' => $wallet['wallet_number'],
            'balance' => isset($wallet['current_balance']) ? (float) $wallet['current_balance'] : 0.0
        ]
    ]);
}

function getBuyInStoreCharge()
{
    $info = \ZzimbaCreditModule\CreditService::buyInStoreChargeInfo();
    echo json_encode([
        'success' => true,
        'fee' => (float) ($info['fee'] ?? 0.0),
        'setting_id' => $info['setting_id'] ?? null
    ]);
}

function checkWalletBalanceCombined(string $userId)
{
    $w = \ZzimbaCreditModule\CreditService::getWallet('USER', $userId);
    if (empty($w['success'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'No Zzimba Wallet found']);
        return;
    }
    $feeInfo = \ZzimbaCreditModule\CreditService::buyInStoreChargeInfo();
    $fee = (float) ($feeInfo['fee'] ?? 0.0);
    $balance = (float) ($w['wallet']['current_balance'] ?? 0.0);
    $canSubmit = $balance >= $fee;
    echo json_encode([
        'success' => true,
        'balance' => $balance,
        'fee' => $fee,
        'canSubmit' => $canSubmit
    ]);
}

function previewBuyInStore(PDO $pdo, string $currentUser)
{
    if (($_SESSION['user']['is_admin'] ?? false) || ($_SESSION['user']['is_manager'] ?? false) || ($_SESSION['user']['is_owner'] ?? false)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Owners, managers and admins cannot submit buy-in-store requests']);
        return;
    }
    $productId = param('productId', '');
    $packageId = param('packageId', '');
    if (!isValidUlid($productId) || !isValidUlid($packageId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT 
                vs.id   AS store_id,
                vs.name AS store_name,
                p.title AS product_name
            FROM   product_pricing pp
            JOIN   store_products  sp  ON pp.store_products_id = sp.id
            JOIN   products        p   ON sp.product_id        = p.id
            JOIN   store_categories sc  ON sp.store_category_id = sc.id
            JOIN   vendor_stores   vs  ON sc.store_id = vs.id
            WHERE  pp.id = :pricingId AND sp.id = :productId
            LIMIT 1
        ");
        $stmt->execute([':pricingId' => $packageId, ':productId' => $productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid product or package']);
            return;
        }
        $w = \ZzimbaCreditModule\CreditService::getWallet('USER', $currentUser);
        if (empty($w['success'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No Zzimba Wallet found']);
            return;
        }
        $feeInfo = \ZzimbaCreditModule\CreditService::buyInStoreChargeInfo();
        $fee = (float) ($feeInfo['fee'] ?? 0.0);
        $balance = (float) ($w['wallet']['current_balance'] ?? 0.0);
        $canSubmit = $balance >= $fee;
        $shortfall = $canSubmit ? 0.0 : max(0.0, $fee - $balance);
        echo json_encode([
            'success' => true,
            'fee' => $fee,
            'balance' => $balance,
            'can_submit' => $canSubmit,
            'shortfall' => $shortfall,
            'product' => [
                'id' => $productId,
                'pricing_id' => $packageId,
                'name' => $row['product_name'],
                'store_id' => $row['store_id'],
                'store_name' => $row['store_name']
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error in previewBuyInStore: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error preparing preview']);
    }
}

function submitBuyInStore(PDO $pdo, string $currentUser)
{
    if (($_SESSION['user']['is_admin'] ?? false) || ($_SESSION['user']['is_manager'] ?? false) || ($_SESSION['user']['is_owner'] ?? false)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Owners, managers and admins cannot submit buy-in-store requests']);
        return;
    }
    $data = jsonInput();
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data submitted']);
        return;
    }
    $requiredFields = ['productId', 'packageId', 'visitDate', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required field: ' . $field]);
            return;
        }
    }
    if (!isValidUlid($data['productId']) || !isValidUlid($data['packageId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }
    try {
        $visitDate = new DateTime($data['visitDate'], new DateTimeZone('Africa/Kampala'));
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
        $pricingStmt = $pdo->prepare("
            SELECT 
                vs.id   AS store_id,
                vs.name AS store_name,
                vs.business_phone,
                p.title AS product_name,
                ppn.package_name,
                pp.package_size,
                psu.si_unit,
                sp.id   AS store_product_id
            FROM   product_pricing pp
            JOIN   store_products  sp  ON pp.store_products_id = sp.id
            JOIN   products        p   ON sp.product_id        = p.id
            JOIN   product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN   product_package_name     ppn ON ppm.product_package_name_id = ppn.id
            JOIN   product_si_units         psu ON pp.si_unit_id = psu.id
            JOIN   store_categories         sc  ON sp.store_category_id = sc.id
            JOIN   vendor_stores            vs  ON sc.store_id = vs.id
            WHERE  pp.id = :pricingId AND sp.id = :productId
            LIMIT 1
        ");
        $pricingStmt->execute([':pricingId' => $data['packageId'], ':productId' => $data['productId']]);
        $storeData = $pricingStmt->fetch(PDO::FETCH_ASSOC);
        if (!$storeData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid product or package']);
            return;
        }
        $charge = \ZzimbaCreditModule\CreditService::chargeBuyInStoreFee($currentUser);
        if (empty($charge['success'])) {
            http_response_code(400);
            echo json_encode($charge);
            return;
        }
        $requestId = (string) Ulid::generate();
        $visitDateStr = $visitDate->format('Y-m-d');
        $insReq = $pdo->prepare("
            INSERT INTO buy_in_store_requests (
                id, user_id, store_product_id, pricing_id, visit_date, quantity,
                alt_contact, alt_email, notes, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())
        ");
        $insReq->execute([
            $requestId,
            $currentUser,
            $storeData['store_product_id'],
            $data['packageId'],
            $visitDateStr,
            $quantity,
            $data['altContact'] ?? null,
            $data['altEmail'] ?? null,
            $data['notes'] ?? null
        ]);
        $ns = new \NotificationService($pdo);
        $userName = trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? '')) ?: ($_SESSION['user']['username'] ?? 'User');
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
            ],
            [
                'type' => 'user',
                'id' => $currentUser,
                'message' => "Your Buy In Store request to \"{$storeData['store_name']}\" on $visitDatePretty has been submitted."
            ]
        ];
        $link = defined('BASE_URL') ? rtrim(\BASE_URL, '/') . "/vendor-store/requests?id={$storeData['store_id']}" : null;
        $ns->create('visit_request', 'New Visit Request', $recipients, $link, 'high', $currentUser);
        $productLabelParts = [];
        if (!empty($storeData['product_name']))
            $productLabelParts[] = $storeData['product_name'];
        $pkg = trim(($storeData['package_name'] ?? '') . ' ' . ($storeData['package_size'] ?? '') . ($storeData['si_unit'] ? ' ' . $storeData['si_unit'] : ''));
        if ($pkg !== '')
            $productLabelParts[] = "($pkg)";
        $productLabel = trim(implode(' ', $productLabelParts));
        $storePhone = $storeData['business_phone'] ?? '';
        $smsResult = null;
        if ($storePhone !== '') {
            $base = defined('BASE_URL') ? rtrim(\BASE_URL, '/') : '';
            $smsText = $base . ': You have a buy-in-store request for "' . $productLabel . ' (' . (int) $quantity . ' units)" log in to confirm pick-up order';
            $send = \SMS::send($storePhone, $smsText, true);
            $smsResult = ['success' => !empty($send['success']), 'error' => $send['error'] ?? null];
        }
        logAction($pdo, "User {$currentUser} submitted a buy-in-store request for pricing ID {$data['packageId']}");
        $response = [
            'success' => true,
            'message' => 'Your in-store purchase request has been submitted successfully!',
            'requestId' => $requestId,
            'fee_charged' => (float) ($charge['fee_charged'] ?? 0.0),
            'remaining_balance' => (float) ($charge['remaining_balance'] ?? 0.0),
            'transaction_id' => $charge['transaction_id'] ?? null,
            'sms' => $smsResult
        ];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log('Error submitting buy-in-store request: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error submitting request']);
    }
}

function getBuyInStoreHistory(PDO $pdo, string $currentUser)
{
    try {
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