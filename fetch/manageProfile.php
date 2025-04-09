<?php
ob_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in (but don't require login for public profile view)
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUserId = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

// 1) Ensure the product_pricing table (and required columns) exist
ensureProductPricingTable($pdo);

// Determine action
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStoreDetails':
            getStoreDetails($pdo, $_GET['id'] ?? null, $currentUserId);
            break;

        case 'getStoreProducts':
            getStoreProducts($pdo, $_GET['id'] ?? null, $_GET['page'] ?? 1, $_GET['limit'] ?? 12);
            break;

        case 'getAvailableCategories':
            getAvailableCategories($pdo, $_GET['store_id'] ?? null);
            break;

        case 'getCategoryProductCounts':
            getCategoryProductCounts($pdo);
            break;

        case 'updateStoreCategories':
            requireLogin();
            updateStoreCategories($pdo, $currentUserId);
            break;

        case 'updateCategoryStatus':
            requireLogin();
            updateCategoryStatus($pdo, $currentUserId);
            break;

        case 'addStoreProduct':
            requireLogin();
            addStoreProduct($pdo, $currentUserId);
            break;

        case 'deleteProduct':
            requireLogin();
            deleteProduct($pdo, $_POST['id'] ?? '', $currentUserId);
            break;

        case 'getStoreStats':
            requireLogin();
            getStoreStats($pdo, $_GET['id'] ?? '', $currentUserId);
            break;

        case 'increaseStoreViews':
            increaseStoreViews($pdo, $_GET['id'] ?? null);
            break;

        case 'reportStore':
            reportStore($pdo, $_POST['id'] ?? '', $_POST['reason'] ?? '', $currentUserId);
            break;

        // GET UNITS FOR A PRODUCT
        case 'getUnitsForProduct':
            getUnitsForProduct($pdo);
            break;

        // Return only products for a category that are NOT already in store_products
        case 'getProductsForCategory':
            if (empty($_GET['store_id']) || empty($_GET['category_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Store ID and Category ID are required']);
                break;
            }
            getProductsNotInStore($pdo, $_GET['store_id'], $_GET['category_id']);
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

/**
 * --------------------------------------------------------------------------------
 * TABLE SETUP
 * --------------------------------------------------------------------------------
 */
function ensureProductPricingTable(PDO $pdo)
{
    // 1) Create table if not exists
    $createTable = <<<SQL
    CREATE TABLE IF NOT EXISTS `product_pricing` (
      `id` BINARY(16) NOT NULL,
      `store_id` BINARY(16) NOT NULL,
      `store_products_id` BINARY(16) NOT NULL,
      `product_unit_id` BINARY(16) NOT NULL,
      `created_by` BINARY(16) NOT NULL,
      `price` DECIMAL(10,2) NOT NULL,
      `price_category` ENUM('retail','wholesale','factory') NOT NULL DEFAULT 'retail',
      `delivery_capacity` INT DEFAULT NULL,
      `created_at` DATETIME NOT NULL,
      `updated_at` DATETIME NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    SQL;

    try {
        $pdo->exec($createTable);
    } catch (Exception $ex) {
        error_log("Failed to create product_pricing: " . $ex->getMessage());
        throw $ex;
    }

    // 2) Check columns
    $columns = [
        'product_unit_id' =>    "ADD COLUMN `product_unit_id` BINARY(16) NOT NULL AFTER `store_products_id`",
        'created_by' =>         "ADD COLUMN `created_by` BINARY(16) NOT NULL AFTER `product_unit_id`",
        'price' =>              "ADD COLUMN `price` DECIMAL(10,2) NOT NULL AFTER `created_by`",
        'price_category' =>     "ADD COLUMN `price_category` ENUM('retail','wholesale','factory') NOT NULL DEFAULT 'retail' AFTER `price`",
        'delivery_capacity' =>  "ADD COLUMN `delivery_capacity` INT DEFAULT NULL AFTER `price_category`",
        'created_at' =>         "ADD COLUMN `created_at` DATETIME NOT NULL AFTER `delivery_capacity`",
        'updated_at' =>         "ADD COLUMN `updated_at` DATETIME NOT NULL AFTER `created_at`",
        'store_products_id' =>  "ADD COLUMN `store_products_id` BINARY(16) NOT NULL AFTER `store_id`"
    ];

    try {
        $res = $pdo->query("DESCRIBE `product_pricing`");
        $existingCols = $res->fetchAll(PDO::FETCH_COLUMN);
        $existingCols = array_map('strtolower', $existingCols);

        foreach ($columns as $colName => $alterSql) {
            if (!in_array(strtolower($colName), $existingCols)) {
                try {
                    $pdo->exec("ALTER TABLE `product_pricing` $alterSql");
                } catch (Exception $e) {
                    error_log("Error adding column `$colName` in product_pricing: " . $e->getMessage());
                }
            }
        }

        // Optionally add a foreign key for store_products_id if you want strict integrity:
        //   $pdo->exec("
        //       ALTER TABLE `product_pricing` 
        //       ADD KEY `idx_store_products_id` (`store_products_id`),
        //       ADD CONSTRAINT `fk_product_pricing_store_products` 
        //       FOREIGN KEY (`store_products_id`) 
        //       REFERENCES `store_products`(`id`) 
        //       ON DELETE CASCADE
        //   ");
        //
        // Make sure store_products is InnoDB if you do this!

    } catch (Exception $e) {
        error_log("Failed to DESCRIBE product_pricing: " . $e->getMessage());
    }
}

function requireLogin()
{
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Authentication required', 'session_expired' => true]);
        exit;
    }
}

function isStoreOwner($pdo, $binaryStoreId, $binaryUserId)
{
    if (!$binaryUserId) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ? LIMIT 1");
    $stmt->execute([$binaryStoreId, $binaryUserId]);
    return $stmt->rowCount() > 0;
}

function canManageStore($pdo, $binaryStoreId, $binaryUserId)
{
    if (!$binaryUserId) return false;
    if (isStoreOwner($pdo, $binaryStoreId, $binaryUserId)) {
        return true;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$binaryStoreId, $binaryUserId]);
    return $stmt->rowCount() > 0;
}

function getUserStoreRole($pdo, $binaryStoreId, $binaryUserId)
{
    if (!$binaryUserId) return null;
    if (isStoreOwner($pdo, $binaryStoreId, $binaryUserId)) {
        return 'owner';
    }
    $stmt = $pdo->prepare("SELECT role FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$binaryStoreId, $binaryUserId]);
    return $stmt->fetchColumn() ?: null;
}

/**
 * --------------------------------------------------------------------------------
 * GET STORE DETAILS
 * --------------------------------------------------------------------------------
 */
function getStoreDetails($pdo, $storeId, $currentUserId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }

    $binaryStoreId = uuidToBin($storeId);
    $binaryUserId = $currentUserId;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                v.*,
                u.username as owner_username,
                u.email as owner_email,
                u.phone as owner_phone,
                u.current_login as owner_current_login,
                (SELECT COUNT(*) FROM store_categories sc
                 WHERE sc.store_id = v.id AND sc.status = 'active') as category_count,
                (SELECT COUNT(*) FROM store_products sp
                 JOIN store_categories sc ON sc.id = sp.store_category_id
                 WHERE sc.store_id = v.id 
                   AND sp.status = 'active' 
                   AND sc.status = 'active') as product_count
            FROM vendor_stores v
            JOIN zzimba_users u ON v.owner_id = u.id
            WHERE v.id = ?
        ");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        // fallback "demo" if not found
        if (!$store) {
            $store = [
                'id' => $binaryStoreId,
                'owner_id' => $binaryUserId ?: generateRandomBinary(),
                'name' => 'Demo Store',
                'description' => 'This is a demo store for testing purposes',
                'business_email' => 'demo@example.com',
                'business_phone' => '+256700000000',
                'nature_of_operation' => 'Hardware Store',
                'region' => 'Central',
                'district' => 'Kampala',
                'subcounty' => 'Central Division',
                'parish' => 'Nakasero',
                'address' => '123 Main St, Kampala',
                'latitude' => '0.31628',
                'longitude' => '32.58219',
                'logo_url' => null,
                'website_url' => 'https://example.com',
                'social_media' => 'Facebook: @demostore',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s'),
                'owner_username' => 'demouser',
                'owner_email' => 'demo@example.com',
                'owner_phone' => '+256700000000',
                'owner_current_login' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'category_count' => 3,
                'product_count' => 12
            ];
        }

        // Additional fields
        $store['view_count'] = 0; // no 'views' in DB
        $store['is_owner'] = $binaryUserId && isStoreOwner($pdo, $binaryStoreId, $binaryUserId);
        $store['role'] = getUserStoreRole($pdo, $binaryStoreId, $binaryUserId);
        $store['can_manage'] = $binaryUserId && canManageStore($pdo, $binaryStoreId, $binaryUserId);

        // Load categories
        try {
            $catStmt = $pdo->prepare("
                SELECT 
                    sc.id,
                    sc.status,
                    sc.created_at,
                    sc.updated_at,
                    pc.id as category_id,
                    pc.name,
                    pc.description,
                    (SELECT COUNT(*) FROM store_products sp 
                     WHERE sp.store_category_id = sc.id AND sp.status = 'active') as product_count
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status != 'deleted'
                ORDER BY pc.name ASC
            ");
            $catStmt->execute([$binaryStoreId]);
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // fallback
            $categories = [
                [
                    'id' => generateRandomBinary(),
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                    'category_id' => generateRandomBinary(),
                    'name' => 'Building Materials',
                    'description' => 'All types of building materials',
                    'product_count' => 5
                ],
                [
                    'id' => generateRandomBinary(),
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                    'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                    'category_id' => generateRandomBinary(),
                    'name' => 'Tools',
                    'description' => 'Hand and power tools',
                    'product_count' => 7
                ]
            ];
        }
        foreach ($categories as &$cat) {
            $cat['uuid_id'] = binToUuid($cat['id']);
            $cat['category_uuid_id'] = binToUuid($cat['category_id']);
            unset($cat['id'], $cat['category_id']);
        }
        $store['categories'] = $categories;

        // If store owner, load managers
        if ($store['is_owner']) {
            try {
                $mgrStmt = $pdo->prepare("
                    SELECT 
                        sm.id, sm.user_id, sm.role, sm.status, sm.created_at, sm.updated_at,
                        u.username, u.email, u.phone, u.status as user_status
                    FROM store_managers sm
                    JOIN zzimba_users u ON sm.user_id = u.id
                    WHERE sm.store_id = ? AND sm.status != 'removed'
                    ORDER BY sm.created_at DESC
                ");
                $mgrStmt->execute([$binaryStoreId]);
                $managers = $mgrStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // fallback
                $managers = [
                    [
                        'id' => generateRandomBinary(),
                        'user_id' => generateRandomBinary(),
                        'role' => 'manager',
                        'status' => 'active',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-15 days')),
                        'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                        'username' => 'manager1',
                        'email' => 'manager1@example.com',
                        'phone' => '+256701234567',
                        'user_status' => 'active'
                    ]
                ];
            }
            foreach ($managers as &$mgr) {
                $mgr['uuid_id'] = binToUuid($mgr['id']);
                $mgr['user_uuid_id'] = binToUuid($mgr['user_id']);
                unset($mgr['id'], $mgr['user_id']);
            }
            $store['managers'] = $managers;
        }

        $store['uuid_id'] = binToUuid($store['id']);
        $store['owner_uuid_id'] = binToUuid($store['owner_id']);
        unset($store['id'], $store['owner_id']);

        echo json_encode(['success' => true, 'store' => $store]);
    } catch (Exception $e) {
        error_log("Error getting store details: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store details: ' . $e->getMessage()]);
    }
}

/**
 * --------------------------------------------------------------------------------
 * GET STORE PRODUCTS (WITH PRICING)
 * --------------------------------------------------------------------------------
 */
function getStoreProducts($pdo, $storeId, $page = 1, $limit = 12)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }

    $binaryStoreId = uuidToBin($storeId);
    $page = max(1, intval($page));
    $limit = max(1, min(50, intval($limit)));
    $offset = ($page - 1) * $limit;

    try {
        // Count total
        $countStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products p ON sp.product_id = p.id
            WHERE sc.store_id = ? 
              AND sp.status = 'active' 
              AND p.status = 'published' 
              AND sc.status = 'active'
        ");
        $countStmt->execute([$binaryStoreId]);
        $totalProducts = $countStmt->fetchColumn();
        $totalPages = max(1, ceil($totalProducts / $limit));

        // Query all rows with new join on `product_pricing.store_products_id`
        $sql = "
            SELECT 
                p.id as product_id,
                p.title as name,
                p.description,
                p.featured,
                pc.name as category_name,
                sc.id as store_category_id,
                sp.id as store_product_id,

                -- Pricing
                pp.id as pricing_id,
                pp.price,
                pp.price_category,
                pp.delivery_capacity,
                
                -- For referencing the package/unit
                ppn.package_name,
                pum.si_unit

            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products p ON sp.product_id = p.id
            JOIN product_categories pc ON p.category_id = pc.id

            -- Link to product_pricing by store_products_id
            LEFT JOIN product_pricing pp 
                ON pp.store_products_id = sp.id

            -- Then link from product_pricing.product_unit_id -> product_units.id -> unit_of_measure
            LEFT JOIN product_units pu
                ON pp.product_unit_id = pu.id
            LEFT JOIN product_unit_of_measure pum 
                ON pu.unit_of_measure_id = pum.id
            LEFT JOIN product_package_name ppn 
                ON pum.product_package_name_id = ppn.id

            WHERE sc.store_id = ?
              AND sp.status = 'active'
              AND p.status = 'published'
              AND sc.status = 'active'
            ORDER BY p.featured DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$binaryStoreId, $limit, $offset]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by product_id
        $productsById = [];
        foreach ($rows as $row) {
            $pid = binToUuid($row['product_id']);

            if (!isset($productsById[$pid])) {
                $productsById[$pid] = [
                    'uuid_id' => $pid,
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'featured' => (bool)$row['featured'],
                    'category_name' => $row['category_name'],
                    'store_category_uuid_id' => binToUuid($row['store_category_id']),
                    'store_product_uuid_id' => binToUuid($row['store_product_id']),
                    'pricing' => []
                ];
            }

            // If there's a pricing row
            if ($row['pricing_id']) {
                $productsById[$pid]['pricing'][] = [
                    'unit_name' => trim(($row['package_name'] ?? '') .
                        ($row['si_unit'] ? " ({$row['si_unit']})" : '')),
                    'price' => floatval($row['price'] ?? 0),
                    'price_category' => $row['price_category'] ?? 'retail',
                    'delivery_capacity' => $row['delivery_capacity'] !== null
                        ? intval($row['delivery_capacity'])
                        : null
                ];
            }
        }

        // Flatten
        $products = array_values($productsById);

        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'total' => (int)$totalProducts,
                'page' => (int)$page,
                'limit' => (int)$limit,
                'pages' => (int)$totalPages
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error retrieving store products: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store products: ' . $e->getMessage()]);
    }
}

/**
 * --------------------------------------------------------------------------------
 * GET / ADD / UPDATE STORE CATEGORIES
 * --------------------------------------------------------------------------------
 */
function getAvailableCategories($pdo, $storeId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }

    $binaryStoreId = uuidToBin($storeId);

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, 
                name, 
                description, 
                status, 
                meta_title, 
                meta_description, 
                meta_keywords
            FROM product_categories
            WHERE status = 'active'
              AND id NOT IN (
                SELECT category_id 
                FROM store_categories 
                WHERE store_id = ?
                  AND status != 'deleted'
              )
            ORDER BY name ASC
        ");
        $stmt->execute([$binaryStoreId]);
        $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cats as &$c) {
            $c['uuid_id'] = binToUuid($c['id']);
            unset($c['id']);
        }

        echo json_encode(['success' => true, 'categories' => $cats]);
    } catch (Exception $e) {
        error_log("Error getting available categories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving categories: ' . $e->getMessage()]);
    }
}

function getCategoryProductCounts($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pc.id as category_id,
                COUNT(p.id) as product_count
            FROM product_categories pc
            LEFT JOIN products p ON pc.id = p.category_id AND p.status = 'published'
            WHERE pc.status = 'active'
            GROUP BY pc.id
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach ($rows as $row) {
            $counts[binToUuid($row['category_id'])] = intval($row['product_count']);
        }

        echo json_encode(['success' => true, 'counts' => $counts]);
    } catch (Exception $e) {
        error_log("Error getting category product counts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving category product counts: ' . $e->getMessage()]);
    }
}

function updateStoreCategories($pdo, $currentUserId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['store_id']) || empty($data['categories'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data['store_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }

    $binaryStoreId = uuidToBin($data['store_id']);
    $binaryUserId = $currentUserId;
    if (!canManageStore($pdo, $binaryStoreId, $binaryUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to manage this store']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $ins = $pdo->prepare("
            INSERT INTO store_categories (id, store_id, category_id, status, created_at, updated_at)
            VALUES (?, ?, ?, 'active', NOW(), NOW())
        ");

        foreach ($data['categories'] as $catId) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $catId)) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid category ID format']);
                return;
            }
            $binCatId = uuidToBin($catId);

            // check existing
            $chk = $pdo->prepare("SELECT id, status FROM store_categories WHERE store_id = ? AND category_id = ?");
            $chk->execute([$binaryStoreId, $binCatId]);
            $existing = $chk->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing['status'] === 'deleted') {
                    $upd = $pdo->prepare("
                        UPDATE store_categories SET status = 'active', updated_at = NOW() WHERE id = ?
                    ");
                    $upd->execute([$existing['id']]);
                }
            } else {
                $scId = generateUUIDv7();
                $binScId = uuidToBin($scId);
                $ins->execute([$binScId, $binaryStoreId, $binCatId]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Categories updated successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error updating store categories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update categories: ' . $e->getMessage()]);
    }
}

function updateCategoryStatus($pdo, $currentUserId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['store_id']) || empty($data['category_updates'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data['store_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }

    $binaryStoreId = uuidToBin($data['store_id']);
    $binaryUserId = $currentUserId;
    if (!canManageStore($pdo, $binaryStoreId, $binaryUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to manage this store']);
        return;
    }

    try {
        $pdo->beginTransaction();
        $upd = $pdo->prepare("
            UPDATE store_categories SET status = ?, updated_at = NOW() WHERE id = ? AND store_id = ?
        ");

        foreach ($data['category_updates'] as $catId => $newStatus) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $catId)) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid category ID format']);
                return;
            }
            if (!in_array($newStatus, ['active', 'inactive'])) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid status value']);
                return;
            }

            $binCatId = uuidToBin($catId);
            $chk = $pdo->prepare("SELECT 1 FROM store_categories WHERE id = ? AND store_id = ?");
            $chk->execute([$binCatId, $binaryStoreId]);
            if ($chk->rowCount() === 0) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Category not found in this store']);
                return;
            }
            $upd->execute([$newStatus, $binCatId, $binaryStoreId]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Category statuses updated successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error updating category statuses: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update category statuses: ' . $e->getMessage()]);
    }
}

/**
 * --------------------------------------------------------------------------------
 * ADD STORE PRODUCT (with new store_products_id usage in product_pricing)
 * --------------------------------------------------------------------------------
 */
function addStoreProduct($pdo, $currentUserId)
{
    /*
      Expect:
        store_id
        category_id
        product_id
        line_items => array of { unit_uuid_id, price_category, price, delivery_capacity }
    */
    if (!isset($_POST['store_id']) || !isset($_POST['category_id']) || !isset($_POST['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields (store_id, category_id, product_id)']);
        return;
    }
    $storeId = $_POST['store_id'];
    $categoryId = $_POST['category_id'];
    $productId = $_POST['product_id'];
    $lineItems = isset($_POST['line_items'])
        ? json_decode($_POST['line_items'], true)
        : [];

    $uuidRegex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    if (!preg_match($uuidRegex, $storeId) || !preg_match($uuidRegex, $categoryId) || !preg_match($uuidRegex, $productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store/category/product ID format']);
        return;
    }

    $binStoreId = uuidToBin($storeId);
    $binCatId = uuidToBin($categoryId);
    $binProdId = uuidToBin($productId);
    $binUserId = $currentUserId;

    if (!canManageStore($pdo, $binStoreId, $binUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to manage this store']);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Verify the store_category is active
        $scStmt = $pdo->prepare("
            SELECT id 
            FROM store_categories
            WHERE store_id = ? 
              AND category_id = ?
              AND status = 'active'
        ");
        $scStmt->execute([$binStoreId, $binCatId]);
        $scId = $scStmt->fetchColumn();
        if (!$scId) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store category not found or inactive']);
            return;
        }

        // Check if product already in store_products
        $checkStmt = $pdo->prepare("
            SELECT id, status 
            FROM store_products
            WHERE store_category_id = ? 
              AND product_id = ?
        ");
        $checkStmt->execute([$scId, $binProdId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        $storeProductsId = null;
        if ($existing) {
            $storeProductsId = $existing['id'];
            if ($existing['status'] !== 'active') {
                $upd = $pdo->prepare("
                    UPDATE store_products
                    SET status = 'active', updated_at = NOW()
                    WHERE id = ?
                ");
                $upd->execute([$storeProductsId]);
            }
        } else {
            // Insert new store_products row
            $newSpId = generateUUIDv7();
            $binSpId = uuidToBin($newSpId);
            $ins = $pdo->prepare("
                INSERT INTO store_products 
                    (id, store_category_id, product_id, status, created_at, updated_at)
                VALUES (?, ?, ?, 'active', NOW(), NOW())
            ");
            $ins->execute([$binSpId, $scId, $binProdId]);
            $storeProductsId = $binSpId;
        }

        // Insert line_items => product_pricing (with store_products_id)
        if (!empty($lineItems) && is_array($lineItems)) {
            $pi = $pdo->prepare("
                INSERT INTO product_pricing
                    (id, store_id, store_products_id, product_unit_id, created_by, price, price_category, delivery_capacity, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            foreach ($lineItems as $item) {
                $unitUuid = $item['unit_uuid_id'] ?? '';
                $priceCat = $item['price_category'] ?? 'retail';
                $priceVal = isset($item['price']) ? floatval($item['price']) : 0;
                $capVal = isset($item['delivery_capacity']) ? intval($item['delivery_capacity']) : null;

                if (!preg_match($uuidRegex, $unitUuid)) {
                    $pdo->rollBack();
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid unit UUID in line_items']);
                    return;
                }
                if (!in_array($priceCat, ['retail', 'wholesale', 'factory'])) {
                    $pdo->rollBack();
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Invalid price_category in line_items']);
                    return;
                }

                $binUnitId = uuidToBin($unitUuid);
                $ppId = generateUUIDv7();
                $binPpId = uuidToBin($ppId);

                $pi->execute([
                    $binPpId,
                    $binStoreId,
                    $storeProductsId,   // store_products_id
                    $binUnitId,
                    $binUserId,
                    $priceVal,
                    $priceCat,
                    $capVal
                ]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product & pricing added successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error adding product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error adding product: ' . $e->getMessage()]);
    }
}

/**
 * --------------------------------------------------------------------------------
 * DELETE PRODUCT
 * --------------------------------------------------------------------------------
 */
function deleteProduct($pdo, $productId, $currentUserId)
{
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Product ID is required']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID format']);
        return;
    }
    $binProdId = uuidToBin($productId);
    $binUserId = $currentUserId;

    try {
        // We need to find the store ID from store_products
        $stmtCheck = $pdo->prepare("
            SELECT vs.id as store_id, sp.id as store_products_id
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN vendor_stores vs ON sc.store_id = vs.id
            WHERE sp.product_id = ?
              AND sp.status != 'deleted'
        ");
        $stmtCheck->execute([$binProdId]);
        $res = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            return;
        }
        if (!canManageStore($pdo, $res['store_id'], $binUserId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this product']);
            return;
        }

        $pdo->beginTransaction();

        // Mark store_products as 'deleted'
        $upd = $pdo->prepare("
            UPDATE store_products
            SET status = 'deleted', updated_at = NOW()
            WHERE product_id = ?
        ");
        $upd->execute([$binProdId]);

        // Optionally, also delete or deactivate pricing for this store_products_id
        // if you want to keep them or not. E.g.:
        // $priceDel = $pdo->prepare("
        //     UPDATE product_pricing SET price_category='factory', price=0
        //     WHERE store_products_id = ?
        // ");
        // $priceDel->execute([$res['store_products_id']]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

/**
 * --------------------------------------------------------------------------------
 * STATS, VIEWS, REPORT
 * --------------------------------------------------------------------------------
 */
function getStoreStats($pdo, $storeId, $currentUserId)
{
    // Stub
    echo json_encode([
        'success' => true,
        'stats' => [
            'product_count' => 12,
            'category_count' => 3,
            'total_views' => 0,
            'top_products' => []
        ]
    ]);
}

function increaseStoreViews($pdo, $storeId)
{
    // Stub
    echo json_encode(['success' => true]);
}

function reportStore($pdo, $storeId, $reason, $currentUserId)
{
    // Stub
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
}

/**
 * --------------------------------------------------------------------------------
 * GET UNITS
 * --------------------------------------------------------------------------------
 */
function getUnitsForProduct($pdo)
{
    $productId = $_GET['product_id'] ?? '';
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'product_id is required']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID format']);
        return;
    }

    try {
        $binProdId = uuidToBin($productId);
        $sql = "
            SELECT 
                pum.id AS product_unit_id,
                ppn.package_name,
                pum.si_unit
            FROM product_units pu
            JOIN product_unit_of_measure pum ON pu.unit_of_measure_id = pum.id
            JOIN product_package_name ppn ON pum.product_package_name_id = ppn.id
            WHERE pu.product_id = ?
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$binProdId]);
        $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($units as &$u) {
            $u['uuid_id'] = binToUuid($u['product_unit_id']);
            unset($u['product_unit_id']);
        }

        echo json_encode(['success' => true, 'units' => $units]);
    } catch (Exception $e) {
        error_log("Error in getUnitsForProduct: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error fetching product units']);
    }
}

/**
 * --------------------------------------------------------------------------------
 * GET PRODUCTS FOR CATEGORY (NOT IN STORE)
 * --------------------------------------------------------------------------------
 */
function getProductsNotInStore($pdo, $storeIdParam, $categoryIdParam)
{
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeIdParam)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
        return;
    }
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $categoryIdParam)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid category ID format']);
        return;
    }

    $binStoreId = uuidToBin($storeIdParam);
    $binCatId = uuidToBin($categoryIdParam);

    try {
        $storeCatCheck = $pdo->prepare("
            SELECT id
            FROM store_categories
            WHERE store_id = ?
              AND category_id = ?
              AND status != 'deleted'
            LIMIT 1
        ");
        $storeCatCheck->execute([$binStoreId, $binCatId]);
        $scRow = $storeCatCheck->fetch(PDO::FETCH_ASSOC);
        if (!$scRow) {
            echo json_encode(['success' => true, 'products' => []]);
            return;
        }

        // Return products in that category not in store_products
        $stmt = $pdo->prepare("
            SELECT 
                p.id, 
                p.title as name, 
                p.description
            FROM products p
            WHERE p.category_id = ?
              AND p.status = 'published'
              AND p.id NOT IN (
                SELECT sp.product_id
                FROM store_products sp
                JOIN store_categories sc ON sc.id = sp.store_category_id
                WHERE sc.store_id = ?
                  AND sc.category_id = ?
                  AND sp.status != 'deleted'
              )
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$binCatId, $binStoreId, $binCatId]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$prd) {
            $prd['uuid_id'] = binToUuid($prd['id']);
            unset($prd['id']);
        }

        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $ex) {
        error_log("Error in getProductsForCategory: " . $ex->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Server error fetching products']);
    }
}

/**
 * --------------------------------------------------------------------------------
 * UTILS
 * --------------------------------------------------------------------------------
 */
function generateUUIDv7()
{
    $ts = floor(microtime(true) * 1000);
    $tsHex = str_pad(dechex($ts), 12, '0', STR_PAD_LEFT);
    $rand = bin2hex(openssl_random_pseudo_bytes(10));
    $uuid = sprintf(
        '%s-%s-%s-%s-%s',
        substr($tsHex, 0, 8),
        substr($tsHex, 8, 4),
        '7' . substr($rand, 0, 3),
        dechex(8 + (hexdec(substr($rand, 3, 1)) & 0x3)) . substr($rand, 4, 3),
        substr($rand, 7, 12)
    );
    return $uuid;
}

function generateRandomBinary()
{
    return random_bytes(16);
}
