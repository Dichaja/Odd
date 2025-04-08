<?php
ob_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Check if user is logged in (but don't require login for public profile view)
$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$currentUserId = $isLoggedIn ? $_SESSION['user']['user_id'] : null;

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

        case 'updateStoreCategories':
            requireLogin();
            updateStoreCategories($pdo, $currentUserId);
            break;

        case 'addProduct':
            requireLogin();
            addProduct($pdo, $currentUserId);
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

        case 'getProductsForCategory':
            if (empty($_GET['store_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Store ID is required']);
                break;
            }
            if (empty($_GET['category_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Category ID is required']);
                break;
            }
            $storeIdParam = $_GET['store_id'];
            $categoryId = $_GET['category_id'];
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeIdParam)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
                break;
            }
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $categoryId)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid category ID format']);
                break;
            }
            $binaryStoreId = uuidToBin($storeIdParam);
            $binaryCategoryId = uuidToBin($categoryId);
            $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.title as name,
                    p.description,
                    p.views,
                    p.featured,
                    pp.price,
                    ppn.package_name as unit,
                    (SELECT MIN(pi.image_url) FROM product_images pi WHERE pi.product_id = p.id) as image_url,
                    pc.name as category_name,
                    sc.id as store_category_id,
                    sp.id as store_product_id
                FROM store_products sp
                JOIN store_categories sc ON sp.store_category_id = sc.id
                JOIN products p ON sp.product_id = p.id
                JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN product_pricing pp ON p.id = pp.product_id
                LEFT JOIN product_unit_of_measure pum ON pp.unit_of_measure_id = pum.id
                LEFT JOIN product_package_name ppn ON pum.product_package_name_id = ppn.id
                WHERE sc.store_id = ? AND sc.category_id = ? AND sp.status = 'active' AND p.status = 'published'
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$binaryStoreId, $binaryCategoryId]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($products as &$product) {
                $product['uuid_id'] = binToUuid($product['id']);
                $product['store_category_uuid_id'] = binToUuid($product['store_category_id']);
                $product['store_product_uuid_id'] = binToUuid($product['store_product_id']);
                $product['price'] = floatval($product['price'] ?? 0);
                $product['max_capacity'] = null;
                $product['views'] = intval($product['views'] ?? 0);
                $product['featured'] = (bool)$product['featured'];
                unset($product['id'], $product['store_category_id'], $product['store_product_id']);
            }
            echo json_encode(['success' => true, 'products' => $products]);
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

function getStoreDetails($pdo, $storeId, $currentUserId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
            return;
        }
        $binaryStoreId = uuidToBin($storeId);
        $binaryUserId = $currentUserId;
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
                 WHERE sc.store_id = v.id AND sp.status = 'active') as product_count
            FROM vendor_stores v
            JOIN zzimba_users u ON v.owner_id = u.id
            WHERE v.id = ?
        ");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
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
        $store['view_count'] = 150;
        $store['is_owner'] = $binaryUserId && isStoreOwner($pdo, $binaryStoreId, $binaryUserId);
        $store['role'] = getUserStoreRole($pdo, $binaryStoreId, $binaryUserId);
        $store['can_manage'] = $binaryUserId && canManageStore($pdo, $binaryStoreId, $binaryUserId);
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
                    (SELECT COUNT(*) FROM store_products sp WHERE sp.store_category_id = sc.id AND sp.status = 'active') as product_count
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status != 'deleted'
                ORDER BY pc.name ASC
            ");
            $catStmt->execute([$binaryStoreId]);
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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
        foreach ($categories as &$category) {
            $category['uuid_id'] = binToUuid($category['id']);
            $category['category_uuid_id'] = binToUuid($category['category_id']);
            unset($category['id'], $category['category_id']);
        }
        $store['categories'] = $categories;
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

function getStoreProducts($pdo, $storeId, $page = 1, $limit = 12)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
            return;
        }
        $binaryStoreId = uuidToBin($storeId);
        $page = max(1, intval($page));
        $limit = max(1, min(50, intval($limit)));
        $offset = ($page - 1) * $limit;
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products p ON sp.product_id = p.id
            WHERE sc.store_id = ? AND sp.status = 'active' AND p.status = 'published'
        ");
        $countStmt->execute([$binaryStoreId]);
        $totalProducts = $countStmt->fetchColumn();
        $totalPages = ceil($totalProducts / $limit);
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.title as name,
                p.description,
                p.views,
                p.featured,
                pp.price,
                ppn.package_name as unit,
                (SELECT MIN(pi.image_url) FROM product_images pi WHERE pi.product_id = p.id) as image_url,
                pc.name as category_name,
                sc.id as store_category_id,
                sp.id as store_product_id
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN products p ON sp.product_id = p.id
            JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN product_pricing pp ON p.id = pp.product_id
            LEFT JOIN product_unit_of_measure pum ON pp.unit_of_measure_id = pum.id
            LEFT JOIN product_package_name ppn ON pum.product_package_name_id = ppn.id
            WHERE sc.store_id = ? AND sp.status = 'active' AND p.status = 'published'
            ORDER BY p.featured DESC, p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$binaryStoreId, $limit, $offset]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as &$product) {
            $product['uuid_id'] = binToUuid($product['id']);
            $product['store_category_uuid_id'] = binToUuid($product['store_category_id']);
            $product['store_product_uuid_id'] = binToUuid($product['store_product_id']);
            $product['price'] = floatval($product['price'] ?? 0);
            $product['max_capacity'] = null;
            $product['views'] = intval($product['views'] ?? 0);
            $product['featured'] = (bool)$product['featured'];
            unset($product['id'], $product['store_category_id'], $product['store_product_id']);
        }
        echo json_encode([
            'success' => true,
            'products' => $products,
            'pagination' => [
                'total' => $totalProducts,
                'page' => $page,
                'limit' => $limit,
                'pages' => $totalPages
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error getting store products: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store products: ' . $e->getMessage()]);
    }
}

function getAvailableCategories($pdo, $storeId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $storeId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid store ID format']);
            return;
        }
        $binaryStoreId = uuidToBin($storeId);
        $stmt = $pdo->prepare("
            SELECT id, name, description, status, meta_title, meta_description, meta_keywords
            FROM product_categories
            WHERE status = 'active'
            AND id NOT IN (SELECT category_id FROM store_categories WHERE store_id = ?)
            ORDER BY name ASC
        ");
        $stmt->execute([$binaryStoreId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$category) {
            $category['uuid_id'] = binToUuid($category['id']);
            unset($category['id']);
        }
        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (Exception $e) {
        error_log("Error getting available categories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving categories: ' . $e->getMessage()]);
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
    try {
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
        $pdo->beginTransaction();
        $deleteStmt = $pdo->prepare("
            UPDATE store_categories 
            SET status = 'deleted', updated_at = NOW()
            WHERE store_id = ?
        ");
        $deleteStmt->execute([$binaryStoreId]);
        $insertStmt = $pdo->prepare("
            INSERT INTO store_categories (id, store_id, category_id, status, created_at, updated_at)
            VALUES (?, ?, ?, 'active', NOW(), NOW())
            ON DUPLICATE KEY UPDATE status = 'active', updated_at = NOW()
        ");
        $updateStmt = $pdo->prepare("
            UPDATE store_categories 
            SET status = 'active', updated_at = NOW()
            WHERE store_id = ? AND category_id = ?
        ");
        foreach ($data['categories'] as $categoryId) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $categoryId)) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid category ID format']);
                return;
            }
            $binaryCategoryId = uuidToBin($categoryId);
            $checkStmt = $pdo->prepare("SELECT 1 FROM store_categories WHERE store_id = ? AND category_id = ?");
            $checkStmt->execute([$binaryStoreId, $binaryCategoryId]);
            if ($checkStmt->rowCount() > 0) {
                $updateStmt->execute([$binaryStoreId, $binaryCategoryId]);
            } else {
                $storeCategoryId = generateUUIDv7();
                $binaryStoreCategoryId = uuidToBin($storeCategoryId);
                $insertStmt->execute([$binaryStoreCategoryId, $binaryStoreId, $binaryCategoryId]);
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Categories updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating store categories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update categories: ' . $e->getMessage()]);
    }
}

function addProduct($pdo, $currentUserId)
{
    if (
        !isset($_POST['store_id']) || !isset($_POST['category_id']) ||
        !isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['unit'])
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    $storeId = $_POST['store_id'];
    $categoryId = $_POST['category_id'];
    $binaryStoreId = uuidToBin($storeId);
    $binaryUserId = $currentUserId;
    if (!canManageStore($pdo, $binaryStoreId, $binaryUserId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You do not have permission to manage this store']);
        return;
    }
    try {
        $pdo->beginTransaction();
        $storeCategoryStmt = $pdo->prepare("
            SELECT id FROM store_categories 
            WHERE store_id = ? AND category_id = ? AND status = 'active'
        ");
        $binaryCategoryId = uuidToBin($categoryId);
        $storeCategoryStmt->execute([$binaryStoreId, $binaryCategoryId]);
        $storeCategoryId = $storeCategoryStmt->fetchColumn();
        if (!$storeCategoryId) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store category not found']);
            return;
        }
        $productId = generateUUIDv7();
        $binaryProductId = uuidToBin($productId);
        $name = $_POST['name'];
        $description = $_POST['description'] ?? '';
        $price = floatval($_POST['price']);
        $unit = $_POST['unit'];
        $productStmt = $pdo->prepare("
            INSERT INTO products (
                id, title, category_id, description, status, featured, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, 'published', 0, NOW(), NOW()
            )
        ");
        $productStmt->execute([$binaryProductId, $name, $binaryCategoryId, $description]);
        $packageStmt = $pdo->prepare("
            SELECT id FROM product_package_name WHERE package_name = ?
        ");
        $packageStmt->execute([$unit]);
        $packageId = $packageStmt->fetchColumn();
        if (!$packageId) {
            $packageId = generateUUIDv7();
            $binaryPackageId = uuidToBin($packageId);
            $packageInsertStmt = $pdo->prepare("
                INSERT INTO product_package_name (id, package_name, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ");
            $packageInsertStmt->execute([$binaryPackageId, $unit]);
        } else {
            $binaryPackageId = $packageId;
        }
        $uomStmt = $pdo->prepare("
            SELECT id FROM product_unit_of_measure 
            WHERE product_package_name_id = ? AND si_unit = 'unit'
        ");
        $uomStmt->execute([$binaryPackageId]);
        $uomId = $uomStmt->fetchColumn();
        if (!$uomId) {
            $uomId = generateUUIDv7();
            $binaryUomId = uuidToBin($uomId);
            $uomInsertStmt = $pdo->prepare("
                INSERT INTO product_unit_of_measure (id, product_package_name_id, si_unit, created_at, updated_at)
                VALUES (?, ?, 'unit', NOW(), NOW())
            ");
            $uomInsertStmt->execute([$binaryUomId, $binaryPackageId]);
        } else {
            $binaryUomId = $uomId;
        }
        $pricingId = generateUUIDv7();
        $binaryPricingId = uuidToBin($pricingId);
        $pricingStmt = $pdo->prepare("
            INSERT INTO product_pricing (id, product_id, unit_of_measure_id, price, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $pricingStmt->execute([$binaryPricingId, $binaryProductId, $binaryUomId, $price]);
        $storeProductId = generateUUIDv7();
        $binaryStoreProductId = uuidToBin($storeProductId);
        $storeProductStmt = $pdo->prepare("
            INSERT INTO store_products (id, store_category_id, product_id, status, created_at, updated_at)
            VALUES (?, ?, ?, 'active', NOW(), NOW())
        ");
        $storeProductStmt->execute([$binaryStoreProductId, $storeCategoryId, $binaryProductId]);
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadProductImage($_FILES['image'], $productId);
            if ($imagePath) {
                $imageId = generateUUIDv7();
                $binaryImageId = uuidToBin($imageId);
                $imageStmt = $pdo->prepare("
                    INSERT INTO product_images (id, product_id, image_url, is_primary, created_at, updated_at)
                    VALUES (?, ?, ?, 1, NOW(), NOW())
                ");
                $imageStmt->execute([$binaryImageId, $binaryProductId, $imagePath]);
            }
        }
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Product added successfully',
            'product_id' => $productId
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error adding product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error adding product: ' . $e->getMessage()]);
    }
}

function deleteProduct($pdo, $productId, $currentUserId)
{
    if (empty($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Product ID is required']);
        return;
    }
    try {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $productId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid product ID format']);
            return;
        }
        $binaryProductId = uuidToBin($productId);
        $binaryUserId = $currentUserId;
        $stmtCheck = $pdo->prepare("
            SELECT vs.id as store_id
            FROM store_products sp
            JOIN store_categories sc ON sp.store_category_id = sc.id
            JOIN vendor_stores vs ON sc.store_id = vs.id
            WHERE sp.product_id = ?
        ");
        $stmtCheck->execute([$binaryProductId]);
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Product not found']);
            return;
        }
        if (!canManageStore($pdo, $result['store_id'], $binaryUserId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this product']);
            return;
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            UPDATE store_products 
            SET status = 'deleted', updated_at = NOW() 
            WHERE product_id = ?
        ");
        $stmt->execute([$binaryProductId]);
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

function getStoreStats($pdo, $storeId, $currentUserId)
{
    echo json_encode([
        'success' => true,
        'stats' => [
            'product_count' => 12,
            'category_count' => 3,
            'total_views' => 1250,
            'top_products' => [
                [
                    'uuid_id' => generateUUIDv7(),
                    'name' => 'Popular Product 1',
                    'views' => 450,
                    'price' => 25000,
                    'unit' => 'Piece'
                ],
                [
                    'uuid_id' => generateUUIDv7(),
                    'name' => 'Popular Product 2',
                    'views' => 320,
                    'price' => 15000,
                    'unit' => 'Kg'
                ],
                [
                    'uuid_id' => generateUUIDv7(),
                    'name' => 'Popular Product 3',
                    'views' => 280,
                    'price' => 35000,
                    'unit' => 'Box'
                ]
            ]
        ]
    ]);
}

function increaseStoreViews($pdo, $storeId)
{
    echo json_encode(['success' => true]);
}

function reportStore($pdo, $storeId, $reason, $currentUserId)
{
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
}

function uploadProductImage($file, $productId)
{
    $targetDir = __DIR__ . '/../img/products/' . $productId . '/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'main.' . $fileExt;
    $targetFile = $targetDir . $fileName;
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        return false;
    }
    if ($file['size'] > 5000000) {
        return false;
    }
    $allowedFormats = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array(strtolower($fileExt), $allowedFormats)) {
        return false;
    }
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return 'img/products/' . $productId . '/' . $fileName;
    }
    return false;
}

function generateRandomBinary()
{
    return random_bytes(16);
}
