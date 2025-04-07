<?php
ob_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expired', 'session_expired' => true]);
    exit;
}

// Current user
$currentUserId = $_SESSION['user']['user_id'] ?? null;
if (!$currentUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid user session']);
    exit;
}

// Initialize tables
initializeTables($pdo);

// Determine action
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getOwnedStores':
            getOwnedStores($pdo, $currentUserId);
            break;

        case 'getManagedStores':
            getManagedStores($pdo, $currentUserId);
            break;

        case 'getStoreDetails':
            getStoreDetails($pdo, $_GET['id'] ?? '', $currentUserId);
            break;

        case 'createStore':
            createStore($pdo, $currentUserId);
            break;

        case 'updateStore':
            updateStore($pdo, $currentUserId);
            break;

        case 'deleteStore':
            deleteStore($pdo, $_POST['id'] ?? '', $currentUserId);
            break;

        case 'uploadLogo':
            uploadLogo();
            break;

        case 'getStoreCategories':
            getStoreCategories($pdo, $_GET['storeId'] ?? '');
            break;

        case 'getStoreManagers':
            getStoreManagers($pdo, $_GET['storeId'] ?? '', $currentUserId);
            break;

        case 'addStoreManager':
            addStoreManager($pdo, $_POST['storeId'] ?? '', $_POST['userId'] ?? '', $currentUserId);
            break;

        case 'removeStoreManager':
            removeStoreManager($pdo, $_POST['storeId'] ?? '', $_POST['userId'] ?? '', $currentUserId);
            break;

        case 'getRegions':
            getRegions();
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageZzimbaStores.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();

/**
 * ------------------------------------------------------------------
 * Helper Functions
 * ------------------------------------------------------------------
 */

/**
 * Check if user is the store owner
 */
function isOwner($pdo, $binaryStoreId, $binaryUserId)
{
    $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
    $stmt->execute([$binaryStoreId]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$store) {
        return false;
    }
    return $store['owner_id'] === $binaryUserId;
}

/**
 * Check if user can access store as manager or owner
 */
function canAccessStore($pdo, $binaryStoreId, $binaryUserId)
{
    if (isOwner($pdo, $binaryStoreId, $binaryUserId)) {
        return true;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$binaryStoreId, $binaryUserId]);
    return $stmt->rowCount() > 0;
}

/**
 * Check if a store field (name/email/phone) already exists
 */
function storeFieldExists($pdo, $field, $value, $excludeBinaryStoreId = null)
{
    $query = "SELECT COUNT(*) FROM vendor_stores WHERE $field = ?";
    $params = [$value];
    if ($excludeBinaryStoreId) {
        $query .= " AND id != ?";
        $params[] = $excludeBinaryStoreId;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

/**
 * Generate all needed tables
 */
function initializeTables($pdo)
{
    try {
        // vendor_stores
        $pdo->exec("CREATE TABLE IF NOT EXISTS vendor_stores (
            id BINARY(16) PRIMARY KEY,
            owner_id BINARY(16) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            business_email VARCHAR(100) NOT NULL,
            business_phone VARCHAR(20) NOT NULL,
            nature_of_operation ENUM('Manufacturer','Hardware Store','Earth materials','Plant & Equipment','Transporter','Wholesale Store','Distributor') NOT NULL,
            region VARCHAR(100) NOT NULL,
            district VARCHAR(100) NOT NULL,
            subcounty VARCHAR(100),
            parish VARCHAR(100),
            address TEXT NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            logo_url VARCHAR(255),
            website_url VARCHAR(255),
            social_media TEXT,
            status ENUM('active','pending','inactive','suspended') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (owner_id) REFERENCES zzimba_users(id) ON DELETE CASCADE,
            UNIQUE KEY store_name_unique (name),
            UNIQUE KEY store_email_unique (business_email),
            UNIQUE KEY store_phone_unique (business_phone)
        )");

        // store_categories
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_categories (
            id BINARY(16) PRIMARY KEY,
            store_id BINARY(16) NOT NULL,
            category_id BINARY(16) NOT NULL,
            status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_id) REFERENCES vendor_stores(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES product_categories(id),
            UNIQUE KEY store_category_unique (store_id, category_id)
        )");

        // store_products
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_products (
            id BINARY(16) PRIMARY KEY,
            store_category_id BINARY(16) NOT NULL,
            product_id BINARY(16) NOT NULL,
            status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_category_id) REFERENCES store_categories(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id),
            UNIQUE KEY store_product_unique (store_category_id, product_id)
        )");

        // store_managers
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_managers (
            id BINARY(16) PRIMARY KEY,
            store_id BINARY(16) NOT NULL,
            user_id BINARY(16) NOT NULL,
            role ENUM('manager','inventory_manager','sales_manager','content_manager') NOT NULL DEFAULT 'manager',
            status ENUM('active','inactive','removed') NOT NULL DEFAULT 'active',
            added_by BINARY(16) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_id) REFERENCES vendor_stores(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES zzimba_users(id) ON DELETE CASCADE,
            FOREIGN KEY (added_by) REFERENCES zzimba_users(id),
            UNIQUE KEY store_manager_unique (store_id, user_id)
        )");
    } catch (PDOException $e) {
        error_log("Table creation error: " . $e->getMessage());
        throw new Exception("Database setup failed: " . $e->getMessage());
    }
}

/**
 * -----------------------------------------------------------------------------
 * Endpoints
 * -----------------------------------------------------------------------------
 */

/**
 * Get stores owned by this user
 */
function getOwnedStores($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                vs.id,
                vs.name,
                vs.description,
                vs.business_email,
                vs.business_phone,
                vs.nature_of_operation,
                vs.region,
                vs.district,
                vs.address,
                vs.logo_url,
                vs.status,
                vs.created_at,
                (
                    SELECT COUNT(*) FROM store_categories sc 
                    JOIN store_products sp ON sc.id = sp.store_category_id 
                    WHERE sc.store_id = vs.id AND sp.status = 'active'
                ) as product_count
            FROM vendor_stores vs
            WHERE vs.owner_id = ?
            ORDER BY vs.created_at DESC
        ");
        $stmt->execute([$userId]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($stores as &$store) {
            $store['uuid_id'] = binToUuid($store['id']);
            $store['location'] = $store['district'] . ', ' . $store['address'];
            $store['subscription'] = ($store['status'] === 'pending') ? 'Awaiting approval' : 'Active store';
            $catStmt = $pdo->prepare("
                SELECT pc.name 
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status = 'active' 
                LIMIT 5
            ");
            $catStmt->execute([$store['id']]);
            $store['categories'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);
            unset($store['id']);
        }
        echo json_encode(['success' => true, 'stores' => $stores]);
    } catch (Exception $e) {
        error_log("Error getting owned stores: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving stores: ' . $e->getMessage()]);
    }
}

/**
 * Get stores this user manages (but does not own)
 */
function getManagedStores($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                vs.id,
                vs.name,
                vs.description,
                vs.business_email,
                vs.business_phone,
                vs.nature_of_operation,
                vs.region,
                vs.district,
                vs.address,
                vs.logo_url,
                vs.status,
                sm.role,
                u.username as owner_username,
                u.id as owner_id,
                vs.created_at,
                (
                    SELECT COUNT(*) FROM store_categories sc 
                    JOIN store_products sp ON sc.id = sp.store_category_id 
                    WHERE sc.store_id = vs.id AND sp.status = 'active'
                ) as product_count
            FROM vendor_stores vs
            JOIN store_managers sm ON vs.id = sm.store_id
            JOIN zzimba_users u ON vs.owner_id = u.id
            WHERE sm.user_id = ? AND vs.owner_id != ?
            ORDER BY vs.created_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($stores as &$store) {
            $store['uuid_id'] = binToUuid($store['id']);
            $store['owner'] = $store['owner_username'];
            $store['location'] = $store['district'] . ', ' . $store['address'];
            $catStmt = $pdo->prepare("
                SELECT pc.name 
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status = 'active'
                LIMIT 5
            ");
            $catStmt->execute([$store['id']]);
            $store['categories'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);
            $store['role'] = ucwords(str_replace('_', ' ', $store['role']));
            unset($store['id'], $store['owner_id'], $store['owner_username']);
        }
        echo json_encode(['success' => true, 'stores' => $stores]);
    } catch (Exception $e) {
        error_log("Error getting managed stores: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving managed stores: ' . $e->getMessage()]);
    }
}

/**
 * Get detailed store info
 */
function getStoreDetails($pdo, $storeId, $userId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        $binaryStoreId = uuidToBin($storeId);
        if (!canAccessStore($pdo, $binaryStoreId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to access this store']);
            return;
        }
        $stmt = $pdo->prepare("
            SELECT 
                vs.*,
                u.username as owner_username,
                u.email as owner_email,
                u.phone as owner_phone
            FROM vendor_stores vs
            JOIN zzimba_users u ON vs.owner_id = u.id
            WHERE vs.id = ?
        ");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }
        $store['uuid_id'] = binToUuid($store['id']);
        $store['owner_id'] = binToUuid($store['owner_id']);
        $store['is_owner'] = (binToUuid($store['owner_id']) === binToUuid($userId));
        if (!$store['is_owner']) {
            $roleStmt = $pdo->prepare("SELECT role FROM store_managers WHERE store_id = ? AND user_id = ?");
            $roleStmt->execute([$binaryStoreId, $userId]);
            $role = $roleStmt->fetchColumn();
            $store['manager_role'] = $role ?: null;
        }
        $catStmt = $pdo->prepare("
            SELECT 
                sc.id, 
                pc.name, 
                pc.description, 
                sc.status, 
                sc.created_at 
            FROM store_categories sc
            JOIN product_categories pc ON sc.category_id = pc.id
            WHERE sc.store_id = ?
        ");
        $catStmt->execute([$binaryStoreId]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$cat) {
            $cat['uuid_id'] = binToUuid($cat['id']);
            unset($cat['id']);
        }
        $store['categories'] = $categories;
        $mgrStmt = $pdo->prepare("
            SELECT 
                sm.id, sm.user_id, sm.role, sm.created_at,
                u.username, u.email, u.phone
            FROM store_managers sm
            JOIN zzimba_users u ON sm.user_id = u.id
            WHERE sm.store_id = ?
        ");
        $mgrStmt->execute([$binaryStoreId]);
        $managers = $mgrStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($managers as &$mgr) {
            $mgr['uuid_id'] = binToUuid($mgr['id']);
            $mgr['user_uuid_id'] = binToUuid($mgr['user_id']);
            unset($mgr['id'], $mgr['user_id']);
        }
        $store['managers'] = $managers;
        $prodStmt = $pdo->prepare("
            SELECT COUNT(*) FROM store_categories sc 
            JOIN store_products sp ON sc.id = sp.store_category_id 
            WHERE sc.store_id = ?
        ");
        $prodStmt->execute([$binaryStoreId]);
        $store['product_count'] = $prodStmt->fetchColumn();
        unset($store['id']);
        echo json_encode(['success' => true, 'store' => $store]);
    } catch (Exception $e) {
        error_log("Error getting store details: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store details: ' . $e->getMessage()]);
    }
}

/**
 * Create store
 */
function createStore($pdo, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        return;
    }
    $required = ['name', 'business_email', 'business_phone', 'nature_of_operation', 'region', 'district', 'address', 'latitude', 'longitude'];
    foreach ($required as $f) {
        if (empty($data[$f])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Field '$f' is required"]);
            return;
        }
    }
    try {
        if (storeFieldExists($pdo, 'name', $data['name'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this name already exists']);
            return;
        }
        if (storeFieldExists($pdo, 'business_email', $data['business_email'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this email already exists']);
            return;
        }
        if (storeFieldExists($pdo, 'business_phone', $data['business_phone'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this phone number already exists']);
            return;
        }
        $storeId = generateUUIDv7();
        $binStoreId = uuidToBin($storeId);
        $binUserId = $userId;
        $description = $data['description'] ?? '';
        $subcounty = $data['subcounty'] ?? null;
        $parish = $data['parish'] ?? null;
        $logoUrl = $data['logo_url'] ?? null;
        $websiteUrl = $data['website_url'] ?? null;
        $socialMedia = $data['social_media'] ?? null;
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO vendor_stores (
                id, owner_id, name, description, business_email, business_phone,
                nature_of_operation, region, district, subcounty, parish, address,
                latitude, longitude, logo_url, website_url, social_media,
                status, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?
            )
        ");
        $stmt->execute([
            $binStoreId,
            $binUserId,
            $data['name'],
            $description,
            $data['business_email'],
            $data['business_phone'],
            $data['nature_of_operation'],
            $data['region'],
            $data['district'],
            $subcounty,
            $parish,
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $logoUrl,
            $websiteUrl,
            $socialMedia,
            $now,
            $now
        ]);
        if (!empty($data['temp_logo_path']) && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])) {
            $fileExt = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
            $storeDirPath = __DIR__ . '/../../img/stores/' . $storeId . '/logo';
            if (!file_exists($storeDirPath)) {
                mkdir($storeDirPath, 0755, true);
            }
            $safeFileName = 'logo.' . $fileExt;
            $newLogoPath = $storeDirPath . '/' . $safeFileName;
            rename(__DIR__ . '/../../' . $data['temp_logo_path'], $newLogoPath);
            $logoUrl = 'img/stores/' . $storeId . '/logo/' . $safeFileName;
            $upd = $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?");
            $upd->execute([$logoUrl, $binStoreId]);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Store created successfully! It is now pending approval.',
            'store_id' => $storeId
        ]);
    } catch (Exception $e) {
        error_log("Error creating store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error creating store: ' . $e->getMessage()]);
    }
}

/**
 * Update store
 */
function updateStore($pdo, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data or missing store ID']);
        return;
    }
    try {
        $binStoreId = uuidToBin($data['id']);
        if (!isOwner($pdo, $binStoreId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to update this store']);
            return;
        }
        if (!empty($data['name']) && storeFieldExists($pdo, 'name', $data['name'], $binStoreId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this name already exists']);
            return;
        }
        if (!empty($data['business_email']) && storeFieldExists($pdo, 'business_email', $data['business_email'], $binStoreId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this email already exists']);
            return;
        }
        if (!empty($data['business_phone']) && storeFieldExists($pdo, 'business_phone', $data['business_phone'], $binStoreId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this phone number already exists']);
            return;
        }
        $allowed = [
            'name',
            'description',
            'business_email',
            'business_phone',
            'nature_of_operation',
            'region',
            'district',
            'subcounty',
            'parish',
            'address',
            'latitude',
            'longitude',
            'website_url',
            'social_media'
        ];
        $updateFields = [];
        $params = [];
        foreach ($allowed as $fld) {
            if (isset($data[$fld])) {
                $updateFields[] = "$fld = ?";
                $params[] = $data[$fld];
            }
        }
        $updateFields[] = "updated_at = ?";
        $params[] = date('Y-m-d H:i:s');
        $params[] = $binStoreId;
        if ($updateFields) {
            $sql = "UPDATE vendor_stores SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        if (!empty($data['temp_logo_path']) && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])) {
            $fileExt = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
            $storeDirPath = __DIR__ . '/../../img/stores/' . $data['id'] . '/logo';
            if (!file_exists($storeDirPath)) {
                mkdir($storeDirPath, 0755, true);
            }
            $oldFiles = glob($storeDirPath . '/*');
            foreach ($oldFiles as $f) {
                if (is_file($f)) unlink($f);
            }
            $safeFileName = 'logo.' . $fileExt;
            $newLogoPath = $storeDirPath . '/' . $safeFileName;
            rename(__DIR__ . '/../../' . $data['temp_logo_path'], $newLogoPath);
            $logoUrl = 'img/stores/' . $data['id'] . '/logo/' . $safeFileName;
            $upd = $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?");
            $upd->execute([$logoUrl, $binStoreId]);
        } elseif (isset($data['remove_logo']) && $data['remove_logo']) {
            $storeDirPath = __DIR__ . '/../../img/stores/' . $data['id'] . '/logo';
            if (file_exists($storeDirPath)) {
                $oldFiles = glob($storeDirPath . '/*');
                foreach ($oldFiles as $file) {
                    if (is_file($file)) unlink($file);
                }
            }
            $upd = $pdo->prepare("UPDATE vendor_stores SET logo_url = NULL WHERE id = ?");
            $upd->execute([$binStoreId]);
        }
        echo json_encode(['success' => true, 'message' => 'Store updated successfully']);
    } catch (Exception $e) {
        error_log("Error updating store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating store: ' . $e->getMessage()]);
    }
}

/**
 * Delete store
 */
function deleteStore($pdo, $storeId, $userId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        $binStoreId = uuidToBin($storeId);
        if (!isOwner($pdo, $binStoreId, $userId)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this store']);
            return;
        }
        $stmt = $pdo->prepare("DELETE FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binStoreId]);
        $storeDirPath = __DIR__ . '/../../img/stores/' . $storeId;
        if (file_exists($storeDirPath)) {
            deleteDirectory($storeDirPath);
        }
        echo json_encode(['success' => true, 'message' => 'Store deleted successfully']);
    } catch (Exception $e) {
        error_log("Error deleting store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error deleting store: ' . $e->getMessage()]);
    }
}

/**
 * Upload store logo (temp)
 */
function uploadLogo()
{
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No logo uploaded or upload error']);
        return;
    }
    try {
        $file = $_FILES['logo'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($fileExt, $allowed)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, WebP, GIF allowed.']);
            return;
        }
        if ($fileSize > 2000000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size too large. Max 2MB.']);
            return;
        }
        $uploadDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $newFileName = uniqid('temp_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        $relativePath = 'uploads/temp/' . $newFileName;
        list($width, $height) = getimagesize($fileTmp);
        $targetSize = 512;
        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($fileTmp);
                break;
            case 'png':
                $source = imagecreatefrompng($fileTmp);
                break;
            case 'webp':
                $source = imagecreatefromwebp($fileTmp);
                break;
            case 'gif':
                $source = imagecreatefromgif($fileTmp);
                break;
            default:
                $source = null;
        }
        if (!$source) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to process image']);
            return;
        }
        $targetImage = imagecreatetruecolor($targetSize, $targetSize);
        if ($fileExt === 'png' || $fileExt === 'gif') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $targetSize, $targetSize, $transparent);
        } else {
            imagefill($targetImage, 0, 0, imagecolorallocate($targetImage, 255, 255, 255));
        }
        if ($width > $height) {
            $srcX = ($width - $height) / 2;
            $srcY = 0;
            $srcSize = $height;
        } else {
            $srcX = 0;
            $srcY = ($height - $width) / 2;
            $srcSize = $width;
        }
        imagecopyresampled(
            $targetImage,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            $targetSize,
            $targetSize,
            $srcSize,
            $srcSize
        );
        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($targetImage, $uploadPath, 90);
                break;
            case 'png':
                imagepng($targetImage, $uploadPath, 9);
                break;
            case 'webp':
                imagewebp($targetImage, $uploadPath, 90);
                break;
            case 'gif':
                imagegif($targetImage, $uploadPath);
                break;
        }
        imagedestroy($source);
        imagedestroy($targetImage);
        echo json_encode([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'temp_path' => $relativePath,
            'url' => BASE_URL . $relativePath
        ]);
    } catch (Exception $e) {
        error_log("Error in uploadLogo: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error uploading logo: ' . $e->getMessage()]);
    }
}

/**
 * Get categories for a given store
 */
function getStoreCategories($pdo, $storeId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        $binStoreId = uuidToBin($storeId);
        $stmt = $pdo->prepare("
            SELECT sc.id, pc.name, pc.description, sc.status, sc.created_at, sc.updated_at
            FROM store_categories sc
            JOIN product_categories pc ON sc.category_id = pc.id
            WHERE sc.store_id = ?
            ORDER BY pc.name ASC
        ");
        $stmt->execute([$binStoreId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$category) {
            $category['uuid_id'] = binToUuid($category['id']);
            unset($category['id']);
        }
        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (Exception $e) {
        error_log("Error getting store categories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving categories: ' . $e->getMessage()]);
    }
}

/**
 * Get managers for a given store
 */
function getStoreManagers($pdo, $storeId, $userId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }
    try {
        $binStoreId = uuidToBin($storeId);
        $stmt = $pdo->prepare("
            SELECT 
                sm.id, sm.user_id, sm.role, sm.created_at,
                u.username, u.email, u.phone
            FROM store_managers sm
            JOIN zzimba_users u ON sm.user_id = u.id
            WHERE sm.store_id = ?
            ORDER BY sm.created_at DESC
        ");
        $stmt->execute([$binStoreId]);
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($managers as &$manager) {
            $manager['uuid_id'] = binToUuid($manager['id']);
            $manager['user_uuid_id'] = binToUuid($manager['user_id']);
            unset($manager['id'], $manager['user_id']);
        }
        echo json_encode(['success' => true, 'managers' => $managers]);
    } catch (Exception $e) {
        error_log("Error getting store managers: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving managers: ' . $e->getMessage()]);
    }
}

/**
 * Add a manager to a store
 */
function addStoreManager($pdo, $storeId, $managerId, $userId)
{
    if (empty($storeId) || empty($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID and manager user ID are required']);
        return;
    }
    try {
        $binStoreId = uuidToBin($storeId);
        $binManagerId = uuidToBin($managerId);
        $binUserId = $userId;
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }
        if ($store['owner_id'] !== $binUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to add managers to this store']);
            return;
        }
        $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE id = ?");
        $stmt->execute([$binManagerId]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
            return;
        }
        $stmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ?");
        $stmt->execute([$binStoreId, $binManagerId]);
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'User is already a manager for this store']);
            return;
        }
        $managerEntryId = generateUUIDv7();
        $binManagerEntryId = uuidToBin($managerEntryId);
        $role = $_POST['role'] ?? 'manager';
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("
            INSERT INTO store_managers (
                id, store_id, user_id, role, added_by, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?
            )
        ");
        $stmt->execute([
            $binManagerEntryId,
            $binStoreId,
            $binManagerId,
            $role,
            $binUserId,
            $now,
            $now
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Manager added successfully',
            'manager_id' => $managerEntryId
        ]);
    } catch (Exception $e) {
        error_log("Error adding store manager: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error adding manager: ' . $e->getMessage()]);
    }
}

/**
 * Remove a manager from a store
 */
function removeStoreManager($pdo, $storeId, $managerId, $userId)
{
    if (empty($storeId) || empty($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID and manager ID are required']);
        return;
    }
    try {
        $binStoreId = uuidToBin($storeId);
        $binManagerId = uuidToBin($managerId);
        $binUserId = $userId;
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }
        if ($store['owner_id'] !== $binUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to remove managers from this store']);
            return;
        }
        $stmt = $pdo->prepare("DELETE FROM store_managers WHERE id = ? AND store_id = ?");
        $stmt->execute([$binManagerId, $binStoreId]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Manager not found for this store']);
            return;
        }
        echo json_encode(['success' => true, 'message' => 'Manager removed successfully']);
    } catch (Exception $e) {
        error_log("Error removing store manager: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error removing manager: ' . $e->getMessage()]);
    }
}

/**
 * Get regions data for location selection
 */
function getRegions()
{
    $jsonFile = __DIR__ . '/../../locations/gadm41_UGA_4.json';
    if (!file_exists($jsonFile)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Regions data file not found']);
        return;
    }
    try {
        $jsonContent = file_get_contents($jsonFile);
        $data = json_decode($jsonContent, true);
        if (!$data) {
            throw new Exception('Failed to parse regions data');
        }
        $level1Options = [];
        foreach ($data['features'] as $feature) {
            $name = $feature['properties']['NAME_1'] ?? null;
            if ($name) {
                $level1Options[$name] = $name;
            }
        }
        asort($level1Options);
        echo json_encode([
            'success' => true,
            'regions' => array_values($level1Options)
        ]);
    } catch (Exception $e) {
        error_log("Error getting regions: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving regions: ' . $e->getMessage()]);
    }
}

/**
 * Recursively delete a directory
 */
function deleteDirectory($dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}
