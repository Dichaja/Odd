<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';

use Ulid\Ulid;

header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

// Ensure buy_in_store_requests table exists
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

/**
 * Ensure the buy_in_store_requests table exists
 */
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
            `alt_email` VARCHAR(100) DEFAULT NULL,
            `notes` TEXT DEFAULT NULL,
            `status` ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_buy_in_store_user` (`user_id`),
            KEY `fk_buy_in_store_product` (`store_product_id`),
            KEY `fk_buy_in_store_pricing` (`pricing_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
}

/**
 * Require user to be logged in
 */
function requireLogin()
{
    if (empty($_SESSION['user']['logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'session_expired' => true]);
        exit;
    }
}

/**
 * Check if ULID is valid
 */
function isValidUlid(string $id): bool
{
    return (bool) preg_match('/^[0-9A-Z]{26}$/i', $id);
}

/**
 * Get user information for the Buy in Store form
 * Retrieves information from the session
 */
function getUserInfo()
{
    // Check if user session exists
    if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'User not logged in',
            'session_expired' => true
        ]);
        return;
    }

    // Get user information from session
    $user = [
        'username' => $_SESSION['user']['username'] ?? null,
        'email' => $_SESSION['user']['email'] ?? null,
        'phone' => $_SESSION['user']['phone'] ?? null,
        'first_name' => $_SESSION['user']['first_name'] ?? null,
        'last_name' => $_SESSION['user']['last_name'] ?? null,
        'name' => isset($_SESSION['user']['first_name']) && isset($_SESSION['user']['last_name'])
            ? $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']
            : ($_SESSION['user']['username'] ?? null)
    ];

    // Return user information
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}

/**
 * Get product packages for a specific product
 * Used to populate the package selection dropdown
 */
function getProductPackages(PDO $pdo)
{
    $productId = $_GET['productId'] ?? '';

    if (empty($productId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }

    try {
        // Query to get product pricing information
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
                product_pricing pp
            JOIN 
                product_si_units psu ON pp.si_unit_id = psu.id
            JOIN 
                product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN 
                product_package_name ppn ON ppm.product_package_name_id = ppn.id
            WHERE 
                pp.store_products_id = ?
            ORDER BY 
                pp.price_category, pp.price
        ");
        $stmt->execute([$productId]);
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'packages' => $packages
        ]);
    } catch (Exception $e) {
        error_log('Error fetching product packages: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error fetching product packages']);
    }
}

/**
 * Submit Buy in Store request
 * Creates a new record in the buy_in_store_requests table
 */
function submitBuyInStore(PDO $pdo, string $currentUser)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data submitted']);
        return;
    }

    // Validate required fields
    $requiredFields = ['productId', 'packageId', 'visitDate', 'quantity'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required field: ' . $field]);
            return;
        }
    }

    // Validate product ID and package ID
    if (!isValidUlid($data['productId']) || !isValidUlid($data['packageId'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }

    // Validate visit date (must be in the future)
    $visitDate = new DateTime($data['visitDate']);
    $today = new DateTime();
    if ($visitDate <= $today) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Visit date must be in the future']);
        return;
    }

    // Validate quantity
    $quantity = intval($data['quantity']);
    if ($quantity < 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Quantity must be at least 1']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Generate a unique ID for the request
        $requestId = (string) Ulid::generate();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        // Insert into buy_in_store_requests table
        $stmt = $pdo->prepare("
            INSERT INTO buy_in_store_requests (
                id, 
                user_id, 
                store_product_id, 
                pricing_id, 
                visit_date, 
                quantity, 
                alt_contact,
                alt_email,
                notes,
                status, 
                created_at, 
                updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)
        ");

        $altContact = $data['altContact'] ?? null;
        $altEmail = $data['altEmail'] ?? null;
        $notes = $data['notes'] ?? null;

        $stmt->execute([
            $requestId,
            $currentUser,
            $data['productId'],
            $data['packageId'],
            $data['visitDate'],
            $quantity,
            $altContact,
            $altEmail,
            $notes,
            $now,
            $now
        ]);

        // Log the action
        logAction($pdo, "User {$currentUser} submitted a buy-in-store request for product {$data['productId']}");

        $pdo->commit();

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Your in-store purchase request has been submitted successfully!',
            'requestId' => $requestId
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error submitting buy-in-store request: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error submitting request']);
    }
}

/**
 * Get buy in store request history for the current user
 */
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
                p.title AS product_name,
                pp.price,
                pp.price_category,
                pp.package_size,
                psu.si_unit,
                ppn.package_name,
                vs.name AS store_name
            FROM 
                buy_in_store_requests bir
            JOIN 
                product_pricing pp ON bir.pricing_id = pp.id
            JOIN 
                store_products sp ON bir.store_product_id = sp.id
            JOIN 
                products p ON sp.product_id = p.id
            JOIN 
                store_categories sc ON sp.store_category_id = sc.id
            JOIN 
                vendor_stores vs ON sc.store_id = vs.id
            JOIN 
                product_si_units psu ON pp.si_unit_id = psu.id
            JOIN 
                product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN 
                product_package_name ppn ON ppm.product_package_name_id = ppn.id
            WHERE 
                bir.user_id = ?
            ORDER BY 
                bir.created_at DESC
        ");
        $stmt->execute([$currentUser]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
    } catch (Exception $e) {
        error_log('Error fetching buy-in-store history: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error fetching request history']);
    }
}

/**
 * Log an action in the action_logs table
 */
function logAction(PDO $pdo, string $action)
{
    try {
        $logId = (string) Ulid::generate();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO action_logs (log_id, action, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$logId, $action, $now]);
    } catch (Exception $e) {
        // Silently fail - logging should not interrupt the main flow
        error_log('Error logging action: ' . $e->getMessage());
    }
}
?>