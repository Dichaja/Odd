<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
require_once __DIR__ . '/../config/config.php';
use Ulid\Ulid;
header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

ensureProductPricingTable($pdo);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getUserStores':
            requireLogin();
            getUserStores($pdo, $currentUser);
            break;
        case 'getExistingPricing':
            getExistingPricing($pdo, $_GET['store_id'] ?? '', $_GET['product_id'] ?? '');
            break;
        case 'getPackageNamesForProduct':
            getPackageNamesForProduct($pdo, $_GET['product_id'] ?? '');
            break;
        case 'getSIUnits':
            getSIUnits($pdo);
            break;
        case 'addProductToStore':
            requireLogin();
            addProductToStore($pdo, $currentUser);
            break;
        case 'deletePricing':
            requireLogin();
            deletePricing($pdo, $currentUser);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageVendorSell.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
ob_end_flush();

function ensureProductPricingTable(PDO $pdo)
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `product_pricing` (
            `id` VARCHAR(26) NOT NULL,
            `store_products_id` VARCHAR(26) NOT NULL,
            `package_mapping_id` VARCHAR(26) NOT NULL,
            `si_unit_id` VARCHAR(26) NOT NULL,
            `package_size` VARCHAR(20) NOT NULL DEFAULT '1',
            `created_by` VARCHAR(26) NOT NULL,
            `price` DECIMAL(10,2) NOT NULL,
            `price_category` ENUM('retail','wholesale','factory') NOT NULL DEFAULT 'retail',
            `delivery_capacity` INT DEFAULT NULL,
            `commission_type` ENUM('flat','percentage') NOT NULL DEFAULT 'percentage',
            `commission_value` DECIMAL(10,2) NOT NULL DEFAULT '1.00',
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`store_products_id`) REFERENCES `store_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`package_mapping_id`) REFERENCES `product_package_name_mappings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`si_unit_id`) REFERENCES `product_si_units` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `zzimba_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    $existsType = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_pricing' AND COLUMN_NAME = 'commission_type'");
    $existsType->execute();
    if (!$existsType->fetchColumn()) {
        $pdo->exec("ALTER TABLE `product_pricing` ADD COLUMN `commission_type` ENUM('flat','percentage') NOT NULL DEFAULT 'percentage' AFTER `delivery_capacity`");
    }
    $existsValue = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_pricing' AND COLUMN_NAME = 'commission_value'");
    $existsValue->execute();
    if (!$existsValue->fetchColumn()) {
        $pdo->exec("ALTER TABLE `product_pricing` ADD COLUMN `commission_value` DECIMAL(10,2) NOT NULL DEFAULT '1.00' AFTER `commission_type`");
    }
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

function isStoreOwner(PDO $pdo, string $storeId, ?string $userId): bool
{
    if (!$userId)
        return false;
    $stmt = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ? LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function canManageStore(PDO $pdo, string $storeId, ?string $userId): bool
{
    if (!$userId)
        return false;
    if (isStoreOwner($pdo, $storeId, $userId))
        return true;
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active' AND approved = 1 LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function getUserStores(PDO $pdo, string $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT vs.id, vs.name, vs.district, vs.logo_url, vs.status, 'owner' AS role
            FROM vendor_stores vs
            WHERE vs.owner_id = ? AND vs.status != 'deleted'
            UNION
            SELECT vs.id, vs.name, vs.district, vs.logo_url, vs.status, 'manager' AS role
            FROM vendor_stores vs
            JOIN store_managers sm ON vs.id = sm.store_id
            WHERE sm.user_id = ? AND sm.status = 'active' AND sm.approved = 1 AND vs.status != 'deleted'
            ORDER BY name
        ");
        $stmt->execute([$userId, $userId]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'stores' => $stores]);
    } catch (Exception $e) {
        error_log('Error in getUserStores: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving stores']);
    }
}

function getExistingPricing(PDO $pdo, string $storeId, string $productId)
{
    if (!$storeId || !$productId || !isValidUlid($storeId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT
                pp.id AS pricing_id,
                pp.price,
                pp.price_category,
                pp.delivery_capacity,
                pp.package_size,
                pp.package_mapping_id,
                pp.si_unit_id,
                pp.commission_type,
                pp.commission_value,
                psu.si_unit,
                ppn.package_name,
                CONCAT(psu.si_unit, ' ', ppn.package_name) AS unit_name
            FROM product_pricing pp
            JOIN store_products sp ON pp.store_products_id = sp.id
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
            JOIN product_si_units psu ON pp.si_unit_id = psu.id
            WHERE sc.store_id = ? AND sp.product_id = ? AND sp.status = 'active' AND sc.status = 'active'
            ORDER BY pp.price_category, pp.price
        ");
        $stmt->execute([$storeId, $productId]);
        $pricing = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($pricing as &$p) {
            $p['price'] = (float) $p['price'];
            $p['commission_value'] = isset($p['commission_value']) ? (float) $p['commission_value'] : null;
            $p['delivery_capacity'] = $p['delivery_capacity'] !== null ? (int) $p['delivery_capacity'] : null;
        }
        echo json_encode(['success' => true, 'pricing' => $pricing]);
    } catch (Exception $e) {
        error_log('Error in getExistingPricing: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving existing pricing']);
    }
}

function getPackageNamesForProduct(PDO $pdo, string $productId)
{
    if (!$productId || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT ppm.id, ppn.package_name
            FROM product_package_name_mappings ppm
            JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
            WHERE ppm.product_id = ?
            ORDER BY ppn.package_name
        ");
        $stmt->execute([$productId]);
        $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'mappings' => $mappings]);
    } catch (Exception $e) {
        error_log('Error in getPackageNamesForProduct: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving package mappings']);
    }
}

function getSIUnits(PDO $pdo)
{
    try {
        $stmt = $pdo->query("SELECT id, si_unit FROM product_si_units ORDER BY si_unit");
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'siUnits' => $units]);
    } catch (Exception $e) {
        error_log('Error in getSIUnits: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving SI units']);
    }
}

function validateCommission($typeRaw, $valueRaw, $price)
{
    $type = in_array(strtolower((string) $typeRaw), ['flat', 'percentage'], true) ? strtolower((string) $typeRaw) : 'percentage';
    if ($type === 'percentage') {
        $value = is_null($valueRaw) || $valueRaw === '' ? 1.00 : floatval($valueRaw);
        if ($value < 1 || $value > 5) {
            throw new Exception('Commission percentage must be between 1 and 5');
        }
        return ['percentage', round($value, 2)];
    } else {
        $price = floatval($price);
        if ($price <= 0) {
            throw new Exception('Price must be greater than 0 for flat commission');
        }
        $min = round($price * 0.01, 2);
        $max = round($price * 0.05, 2);
        $value = is_null($valueRaw) || $valueRaw === '' ? $min : round(floatval($valueRaw), 2);
        if ($value < $min || $value > $max) {
            throw new Exception('Flat commission must be between ' . number_format($min, 2, '.', '') . ' and ' . number_format($max, 2, '.', ''));
        }
        return ['flat', $value];
    }
}

function addProductToStore(PDO $pdo, string $currentUser)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $storeId = $input['store_id'] ?? '';
    $productId = $input['product_id'] ?? '';
    $lineItems = $input['line_items'] ?? [];
    if (!isValidUlid($storeId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }
    if (!canManageStore($pdo, $storeId, $currentUser)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }
    if (empty($lineItems) || !is_array($lineItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No pricing entries provided']);
        return;
    }
    try {
        $pdo->beginTransaction();
        $categoryStmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
        $categoryStmt->execute([$productId]);
        $categoryId = $categoryStmt->fetchColumn();
        if (!$categoryId) {
            throw new Exception('Product not found');
        }
        $scStmt = $pdo->prepare("SELECT id FROM store_categories WHERE store_id = ? AND category_id = ? AND status != 'deleted'");
        $scStmt->execute([$storeId, $categoryId]);
        $scId = $scStmt->fetchColumn();
        if (!$scId) {
            $scId = Ulid::generate();
            $pdo->prepare("INSERT INTO store_categories (id, store_id, category_id, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())")->execute([$scId, $storeId, $categoryId]);
        } else {
            $pdo->prepare("UPDATE store_categories SET status = 'active', updated_at = NOW() WHERE id = ? AND status != 'active'")->execute([$scId]);
        }
        $spStmt = $pdo->prepare("SELECT id, status FROM store_products WHERE store_category_id = ? AND product_id = ?");
        $spStmt->execute([$scId, $productId]);
        $spResult = $spStmt->fetch(PDO::FETCH_ASSOC);
        $spId = null;
        if ($spResult) {
            $spId = $spResult['id'];
            if ($spResult['status'] !== 'active') {
                $pdo->prepare("UPDATE store_products SET status = 'active', updated_at = NOW() WHERE id = ?")->execute([$spId]);
            }
        } else {
            $spId = Ulid::generate();
            $pdo->prepare("INSERT INTO store_products (id, store_category_id, product_id, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())")->execute([$spId, $scId, $productId]);
        }
        $insertPricing = $pdo->prepare("
            INSERT INTO product_pricing 
                (id, store_products_id, package_mapping_id, si_unit_id, package_size, created_by, price, price_category, delivery_capacity, commission_type, commission_value, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $updatePricing = $pdo->prepare("
            UPDATE product_pricing
            SET package_mapping_id = ?, si_unit_id = ?, package_size = ?, price = ?, price_category = ?, delivery_capacity = ?, commission_type = ?, commission_value = ?, updated_at = NOW()
            WHERE id = ?
        ");
        foreach ($lineItems as $item) {
            $pmId = $item['package_mapping_id'] ?? '';
            $siId = $item['si_unit_id'] ?? '';
            $packageSize = trim($item['package_size'] ?? '1');
            $price = floatval($item['price'] ?? 0);
            $category = $item['price_category'] ?? 'retail';
            $capacity = isset($item['delivery_capacity']) && $item['delivery_capacity'] !== '' ? intval($item['delivery_capacity']) : null;
            if (!isValidUlid($pmId) || !isValidUlid($siId) || !in_array($category, ['retail', 'wholesale', 'factory'], true)) {
                throw new Exception('Invalid line item data');
            }
            $ctRaw = $item['commission_type'] ?? 'percentage';
            $cvRaw = $item['commission_value'] ?? null;
            [$ctype, $cvalue] = validateCommission($ctRaw, $cvRaw, $price);
            $pricingId = $item['pricing_id'] ?? '';
            if ($pricingId && isValidUlid($pricingId)) {
                $ownStmt = $pdo->prepare("
                    SELECT vs.id AS store_id
                    FROM product_pricing pp
                    JOIN store_products sp ON pp.store_products_id = sp.id
                    JOIN store_categories sc ON sp.store_category_id = sc.id
                    JOIN vendor_stores vs ON sc.store_id = vs.id
                    WHERE pp.id = ?
                    LIMIT 1
                ");
                $ownStmt->execute([$pricingId]);
                $row = $ownStmt->fetch(PDO::FETCH_ASSOC);
                if (!$row || $row['store_id'] !== $storeId) {
                    throw new Exception('Cannot update pricing that does not belong to this store');
                }
                $updatePricing->execute([
                    $pmId,
                    $siId,
                    $packageSize,
                    $price,
                    $category,
                    $capacity,
                    $ctype,
                    $cvalue,
                    $pricingId
                ]);
            } else {
                $ppId = Ulid::generate();
                $insertPricing->execute([
                    $ppId,
                    $spId,
                    $pmId,
                    $siId,
                    $packageSize,
                    $currentUser,
                    $price,
                    $category,
                    $capacity,
                    $ctype,
                    $cvalue
                ]);
            }
        }
        updateEmptyCategories($pdo);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Pricing saved successfully', 'submitted_for_approval' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error in addProductToStore: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error saving pricing: ' . $e->getMessage()]);
    }
}

function deletePricing(PDO $pdo, string $currentUser)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $pricingId = $input['pricing_id'] ?? '';
    if (!isValidUlid($pricingId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid pricing ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT vs.id AS store_id
            FROM product_pricing pp
            JOIN store_products sp ON pp.store_products_id = sp.id
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN vendor_stores vs ON sc.store_id = vs.id
            WHERE pp.id = ?
        ");
        $stmt->execute([$pricingId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Pricing entry not found']);
            return;
        }
        if (!canManageStore($pdo, $result['store_id'], $currentUser)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            return;
        }
        $deleteStmt = $pdo->prepare("DELETE FROM product_pricing WHERE id = ?");
        $deleteStmt->execute([$pricingId]);
        echo json_encode(['success' => true, 'message' => 'Pricing deleted successfully']);
    } catch (Exception $e) {
        error_log('Error in deletePricing: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error deleting pricing']);
    }
}

function updateEmptyCategories(PDO $pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT sc.id
            FROM store_categories sc
            LEFT JOIN store_products sp ON sc.id = sp.store_category_id AND sp.status != 'deleted'
            WHERE sc.status != 'deleted'
            GROUP BY sc.id
            HAVING COUNT(sp.id) = 0
        ");
        $stmt->execute();
        $emptyCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($emptyCategories)) {
            $placeholders = implode(',', array_fill(0, count($emptyCategories), '?'));
            $deleteStmt = $pdo->prepare("UPDATE store_categories SET status = 'deleted', updated_at = NOW() WHERE id IN ($placeholders)");
            $deleteStmt->execute($emptyCategories);
        }
    } catch (Exception $e) {
        error_log('Error in updateEmptyCategories: ' . $e->getMessage());
    }
}
?>