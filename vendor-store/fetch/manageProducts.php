<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
require_once __DIR__ . '/../../config/config.php';
use Ulid\Ulid;
header('Content-Type: application/json');

$isLoggedIn = isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUser = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

ensureProductPricingTable($pdo);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStoreDetails':
            getStoreDetails($pdo, $_GET['id'] ?? null, $currentUser);
            break;
        case 'getStoreProducts':
            getStoreProducts($pdo, $_GET['id'] ?? null, $_GET['page'] ?? 1, $_GET['limit'] ?? 12);
            break;
        case 'getPackageNamesForProduct':
            getPackageNamesForProduct($pdo);
            break;
        case 'getSIUnits':
            getSIUnits($pdo);
            break;
        case 'createSIUnit':
            requireLogin();
            createSIUnit($pdo);
            break;
        case 'addStoreProduct':
            requireLogin();
            addStoreProduct($pdo, $currentUser);
            break;
        case 'updateStoreProduct':
            requireLogin();
            updateStoreProduct($pdo, $currentUser);
            break;
        case 'deleteProduct':
            requireLogin();
            deleteProduct($pdo, $_POST['id'] ?? '', $currentUser);
            break;
        case 'getProductsNotInStore':
            if (empty($_GET['store_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Store ID is required']);
                break;
            }
            getAllProductsNotInStore($pdo, $_GET['store_id']);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageProfile.php: ' . $e->getMessage());
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
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return (bool) $stmt->fetchColumn();
}

function getPackageNamesForProduct(PDO $pdo)
{
    $pid = $_GET['product_id'] ?? '';
    if (!$pid || !isValidUlid($pid)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }
    $stmt = $pdo->prepare("
        SELECT ppm.id, ppn.package_name
        FROM product_package_name_mappings ppm
        JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
        WHERE ppm.product_id = ?
    ");
    $stmt->execute([$pid]);
    $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'mappings' => $mappings]);
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

function createSIUnit(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['si_unit'] ?? '');
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE si_unit = ?");
        $stmt->execute([$name]);
        if ($row = $stmt->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Already exists', 'id' => $row['id']]);
            return;
        }
        $id = (string) Ulid::generate();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $ins = $pdo->prepare("INSERT INTO product_si_units (id, si_unit, created_at, updated_at) VALUES (?, ?, ?, ?)");
        $ins->execute([$id, $name, $now, $now]);
        echo json_encode(['success' => true, 'message' => 'Created', 'id' => $id]);
    } catch (Exception $e) {
        error_log('Error in createSIUnit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating SI unit']);
    }
}

function getStoreDetails(PDO $pdo, ?string $storeId, ?string $currentUserId)
{
    if (!$storeId || !isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT
                v.*,
                nob.name AS nature_of_business_name,
                u.username AS owner_username,
                u.email    AS owner_email,
                u.phone    AS owner_phone,
                u.current_login AS owner_current_login,
                (SELECT COUNT(*) FROM store_categories sc WHERE sc.store_id = v.id AND sc.status = 'active') AS category_count,
                (SELECT COUNT(*) FROM product_pricing pp 
                    JOIN store_products sp ON pp.store_products_id = sp.id
                    JOIN store_categories sc ON sc.id = sp.store_category_id
                 WHERE sc.store_id = v.id AND sp.status = 'active' AND sc.status = 'active') AS product_count
            FROM vendor_stores v
            LEFT JOIN nature_of_business nob ON v.nature_of_business = nob.id
            JOIN zzimba_users u ON v.owner_id = u.id
            WHERE v.id = ?
        ");
        $stmt->execute([$storeId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }
        $store['view_count'] = 0;
        $store['is_owner'] = isStoreOwner($pdo, $storeId, $currentUserId);
        $store['role'] = $store['is_owner'] ? 'owner' : null;
        $store['can_manage'] = canManageStore($pdo, $storeId, $currentUserId);
        echo json_encode(['success' => true, 'store' => $store]);
    } catch (Exception $e) {
        error_log('Error getting store details: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store details']);
    }
}

function getStoreProducts(PDO $pdo, ?string $storeId, int $page = 1, int $limit = 12)
{
    if (!$storeId || !isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    $page = max(1, $page);
    $limit = max(1, min(50, $limit));
    $offset = ($page - 1) * $limit;
    try {
        $countStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products p          ON sp.product_id        = p.id
            WHERE sc.store_id = ?
              AND sp.status  = 'active'
              AND p.status   = 'published'
              AND sc.status  = 'active'
        ");
        $countStmt->execute([$storeId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT
                p.id               AS product_id,
                p.title            AS name,
                p.description,
                p.featured,
                pc.name            AS category_name,
                sc.id              AS store_category_id,
                sp.id              AS store_product_id,
                pp.id              AS pricing_id,
                pp.price,
                pp.price_category,
                pp.delivery_capacity,
                pp.package_size,
                pp.commission_type,
                pp.commission_value,
                ppm.id             AS package_mapping_id,
                ppn.package_name,
                psu.id             AS si_unit_id,
                psu.si_unit
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products        p   ON sp.product_id        = p.id
            JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN product_pricing pp   ON pp.store_products_id = sp.id
            LEFT JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
            LEFT JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
            LEFT JOIN product_si_units psu ON pp.si_unit_id = psu.id
            WHERE sc.store_id = ?
              AND sp.status   = 'active'
              AND p.status    = 'published'
              AND sc.status   = 'active'
            ORDER BY p.featured DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$storeId, $limit, $offset]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $products = [];
        foreach ($rows as $r) {
            $pid = $r['product_id'];
            if (!isset($products[$pid])) {
                $products[$pid] = [
                    'id' => $pid,
                    'name' => $r['name'],
                    'description' => $r['description'],
                    'featured' => (bool) $r['featured'],
                    'category_name' => $r['category_name'],
                    'store_category_id' => $r['store_category_id'],
                    'store_product_id' => $r['store_product_id'],
                    'pricing' => []
                ];
            }
            if ($r['pricing_id']) {
                $products[$pid]['pricing'][] = [
                    'pricing_id' => $r['pricing_id'],
                    'unit_name' => trim(($r['si_unit'] ?? '') . ' ' . ($r['package_name'] ?? '')),
                    'price' => (float) ($r['price'] ?? 0),
                    'price_category' => $r['price_category'] ?? 'retail',
                    'delivery_capacity' => $r['delivery_capacity'] !== null ? (int) $r['delivery_capacity'] : null,
                    'package_size' => $r['package_size'] ?? '1',
                    'package_mapping_id' => $r['package_mapping_id'] ?? null,
                    'si_unit_id' => $r['si_unit_id'] ?? null,
                    'commission_type' => $r['commission_type'] ?? 'percentage',
                    'commission_value' => isset($r['commission_value']) ? (float) $r['commission_value'] : 1.00
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'products' => array_values($products),
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int) ceil($total / $limit)
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error retrieving store products: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store products']);
    }
}

function validateCommission($typeRaw, $valueRaw, $price)
{
    $type = in_array(strtolower((string) $typeRaw), ['flat', 'percentage'], true) ? strtolower((string) $typeRaw) : 'percentage';
    if ($type === 'percentage') {
        $value = is_null($valueRaw) || $valueRaw === '' ? 1.00 : floatval($valueRaw);
        if ($value < 1 || $value > 3) {
            throw new Exception('Commission percentage must be between 1 and 3');
        }
        return ['percentage', round($value, 2)];
    } else {
        $price = floatval($price);
        if ($price <= 0) {
            throw new Exception('Price must be greater than 0 for flat commission');
        }
        $min = round($price * 0.01, 2);
        $max = round($price * 0.03, 2);
        $value = is_null($valueRaw) || $valueRaw === '' ? $min : round(floatval($valueRaw), 2);
        if ($value < $min || $value > $max) {
            throw new Exception('Flat commission must be between ' . number_format($min, 2, '.', '') . ' and ' . number_format($max, 2, '.', ''));
        }
        return ['flat', $value];
    }
}

function addStoreProduct(PDO $pdo, string $currentUser)
{
    $storeId = $_POST['store_id'] ?? '';
    $productId = $_POST['product_id'] ?? '';
    $lineItems = isset($_POST['line_items']) ? json_decode($_POST['line_items'], true) : [];
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
    try {
        $pdo->beginTransaction();
        $prodCatStmt = $pdo->prepare("SELECT category_id FROM products WHERE id = ?");
        $prodCatStmt->execute([$productId]);
        $categoryId = $prodCatStmt->fetchColumn();
        if (!$categoryId) {
            throw new Exception('Product category not found');
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
        $check = $pdo->prepare("SELECT id, status FROM store_products WHERE store_category_id = ? AND product_id = ?");
        $check->execute([$scId, $productId]);
        $ex = $check->fetch(PDO::FETCH_ASSOC);
        if ($ex) {
            $spId = $ex['id'];
            if ($ex['status'] !== 'active') {
                $pdo->prepare("UPDATE store_products SET status = 'active', updated_at = NOW() WHERE id = ?")->execute([$spId]);
            }
        } else {
            $spId = Ulid::generate();
            $pdo->prepare("INSERT INTO store_products (id, store_category_id, product_id, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())")->execute([$spId, $scId, $productId]);
        }
        if (is_array($lineItems) && count($lineItems) > 0) {
            $pi = $pdo->prepare("
                INSERT INTO product_pricing
                    (id, store_products_id, package_mapping_id, si_unit_id, package_size, created_by, price, price_category, delivery_capacity, commission_type, commission_value, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            foreach ($lineItems as $item) {
                $pmId = $item['package_mapping_id'] ?? '';
                $siId = $item['si_unit_id'] ?? '';
                $packageSize = trim($item['package_size'] ?? '1');
                $price = floatval($item['price'] ?? 0);
                $cat = $item['price_category'] ?? 'retail';
                $cap = isset($item['delivery_capacity']) ? intval($item['delivery_capacity']) : null;
                if (!isValidUlid($pmId) || !isValidUlid($siId) || !in_array($cat, ['retail', 'wholesale', 'factory'], true)) {
                    throw new Exception('Invalid line item data');
                }
                $ctRaw = $item['commission_type'] ?? 'percentage';
                $cvRaw = $item['commission_value'] ?? null;
                [$ctype, $cvalue] = validateCommission($ctRaw, $cvRaw, $price);
                $ppId = Ulid::generate();
                $pi->execute([
                    $ppId,
                    $spId,
                    $pmId,
                    $siId,
                    $packageSize,
                    $currentUser,
                    $price,
                    $cat,
                    $cap,
                    $ctype,
                    $cvalue
                ]);
            }
        }
        updateEmptyCategories($pdo);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product & pricing added']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error adding product: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error adding product: ' . $e->getMessage()]);
    }
}

function updateStoreProduct(PDO $pdo, string $currentUser)
{
    $storeProductId = $_POST['store_product_id'] ?? '';
    $lineItems = isset($_POST['line_items']) ? json_decode($_POST['line_items'], true) : [];
    if (!isValidUlid($storeProductId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }
    try {
        $storeIdStmt = $pdo->prepare("SELECT sc.store_id FROM store_products sp JOIN store_categories sc ON sp.store_category_id = sc.id WHERE sp.id = ?");
        $storeIdStmt->execute([$storeProductId]);
        $storeId = $storeIdStmt->fetchColumn();
        if (!$storeId || !canManageStore($pdo, $storeId, $currentUser)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            return;
        }
        $pdo->beginTransaction();
        $existingStmt = $pdo->prepare("SELECT id FROM product_pricing WHERE store_products_id = ?");
        $existingStmt->execute([$storeProductId]);
        $existingPricingIds = $existingStmt->fetchAll(PDO::FETCH_COLUMN);
        $updateStmt = $pdo->prepare("
            UPDATE product_pricing
            SET package_mapping_id = ?, si_unit_id = ?, package_size = ?, price = ?, 
                price_category = ?, delivery_capacity = ?, commission_type = ?, commission_value = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $insertStmt = $pdo->prepare("
            INSERT INTO product_pricing
                (id, store_products_id, package_mapping_id, si_unit_id, package_size, created_by, price, price_category, delivery_capacity, commission_type, commission_value, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $postedIds = [];
        foreach ($lineItems as $item) {
            $pricingId = $item['pricing_id'] ?? '';
            $pmId = $item['package_mapping_id'] ?? '';
            $siId = $item['si_unit_id'] ?? '';
            $packageSize = trim($item['package_size'] ?? '1');
            $price = floatval($item['price'] ?? 0);
            $cat = $item['price_category'] ?? 'retail';
            $cap = isset($item['delivery_capacity']) ? intval($item['delivery_capacity']) : null;
            if (!isValidUlid($pmId) || !isValidUlid($siId) || !in_array($cat, ['retail', 'wholesale', 'factory'], true)) {
                throw new Exception('Invalid line item data');
            }
            $ctRaw = $item['commission_type'] ?? 'percentage';
            $cvRaw = $item['commission_value'] ?? null;
            [$ctype, $cvalue] = validateCommission($ctRaw, $cvRaw, $price);
            if ($pricingId && in_array($pricingId, $existingPricingIds)) {
                $updateStmt->execute([
                    $pmId,
                    $siId,
                    $packageSize,
                    $price,
                    $cat,
                    $cap,
                    $ctype,
                    $cvalue,
                    $pricingId
                ]);
                $postedIds[] = $pricingId;
            } else {
                $ppId = Ulid::generate();
                $insertStmt->execute([
                    $ppId,
                    $storeProductId,
                    $pmId,
                    $siId,
                    $packageSize,
                    $currentUser,
                    $price,
                    $cat,
                    $cap,
                    $ctype,
                    $cvalue
                ]);
                $postedIds[] = $ppId;
            }
        }
        if (!empty($existingPricingIds)) {
            $toDelete = array_diff($existingPricingIds, $postedIds);
            if (!empty($toDelete)) {
                $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
                $deleteStmt = $pdo->prepare("DELETE FROM product_pricing WHERE id IN ($placeholders)");
                $deleteStmt->execute($toDelete);
            }
        }
        updateEmptyCategories($pdo);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product pricing updated']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error updating product: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating product']);
    }
}

function deleteProduct(PDO $pdo, string $storeProductId, ?string $currentUser)
{
    if (!isValidUlid($storeProductId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT vs.id AS store_id
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN vendor_stores vs ON sc.store_id = vs.id
            WHERE sp.id = ? AND sp.status != 'deleted'
        ");
        $stmt->execute([$storeProductId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            return;
        }
        if (!canManageStore($pdo, $res['store_id'], $currentUser)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            return;
        }
        $pdo->prepare("UPDATE store_products SET status = 'deleted', updated_at = NOW() WHERE id = ?")->execute([$storeProductId]);
        echo json_encode(['success' => true, 'message' => 'Product deleted']);
    } catch (Exception $e) {
        error_log('Error deleting product: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error deleting product']);
    }
}

function getAllProductsNotInStore(PDO $pdo, string $storeId)
{
    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid ID format']);
        return;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.id,
                p.title       AS name,
                p.description,
                p.category_id,
                pc.name       AS category_name
            FROM products p
            JOIN product_categories pc ON p.category_id = pc.id
            WHERE p.status = 'published'
              AND p.id NOT IN (
                  SELECT sp.product_id
                  FROM store_products sp
                  JOIN store_categories sc ON sc.id = sp.store_category_id
                  WHERE sc.store_id = ? AND sp.status != 'deleted'
              )
            ORDER BY pc.name, p.title
        ");
        $stmt->execute([$storeId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        error_log('Error in getAllProductsNotInStore: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error fetching products']);
    }
}

function updateEmptyCategories(PDO $pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT sc.id
            FROM store_categories sc
            LEFT JOIN store_products sp ON sc.id = sp.store_category_id
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
