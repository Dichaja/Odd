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

// Get current user ID
$currentUserId = $_SESSION['user']['user_id'] ?? null;
if (!$currentUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid user session']);
    exit;
}

// Initialize database tables if they don't exist
initializeTables($pdo);

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
    // Log the error
    error_log('Error in manageZzimbaStores.php: ' . $e->getMessage());
    // Return a proper JSON response
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

// Flush the output buffer
ob_end_flush();

/**
 * Initialize database tables if they don't exist
 */
function initializeTables($pdo)
{
    try {
        // Create vendor_stores table
        $pdo->exec("CREATE TABLE IF NOT EXISTS vendor_stores (
            id BINARY(16) PRIMARY KEY,
            owner_id BINARY(16) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            business_email VARCHAR(100) NOT NULL,
            business_phone VARCHAR(20) NOT NULL,
            nature_of_operation ENUM('Manufacturer', 'Hardware Store', 'Earth materials', 'Plant & Equipment', 'Transporter', 'Wholesale Store', 'Distributor') NOT NULL,
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
            status ENUM('active', 'pending', 'inactive', 'suspended') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (owner_id) REFERENCES zzimba_users(id) ON DELETE CASCADE,
            UNIQUE KEY store_name_unique (name),
            UNIQUE KEY store_email_unique (business_email),
            UNIQUE KEY store_phone_unique (business_phone)
        )");

        // Create store_categories table with status column
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_categories (
            id BINARY(16) PRIMARY KEY,
            store_id BINARY(16) NOT NULL,
            category_id BINARY(16) NOT NULL,
            status ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_id) REFERENCES vendor_stores(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES product_categories(id),
            UNIQUE KEY store_category_unique (store_id, category_id)
        )");

        // Create store_products table with status column
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_products (
            id BINARY(16) PRIMARY KEY,
            store_category_id BINARY(16) NOT NULL,
            product_id BINARY(16) NOT NULL,
            status ENUM('active', 'inactive', 'deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_category_id) REFERENCES store_categories(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id),
            UNIQUE KEY store_product_unique (store_category_id, product_id)
        )");

        // Create store_managers table with status column
        $pdo->exec("CREATE TABLE IF NOT EXISTS store_managers (
            id BINARY(16) PRIMARY KEY,
            store_id BINARY(16) NOT NULL,
            user_id BINARY(16) NOT NULL,
            role ENUM('manager', 'inventory_manager', 'sales_manager', 'content_manager') NOT NULL DEFAULT 'manager',
            status ENUM('active', 'inactive', 'removed') NOT NULL DEFAULT 'active',
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
 * Get stores owned by the current user
 */
function getOwnedStores($pdo, $userId)
{
    try {
        $binaryUserId = $userId;

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
                (SELECT COUNT(*) FROM store_categories sc 
                 JOIN store_products sp ON sc.id = sp.store_category_id 
                 WHERE sc.store_id = vs.id AND sp.status = 'active') as product_count,
                (SELECT COUNT(*) FROM store_categories WHERE store_id = vs.id AND status = 'active') as category_count
            FROM 
                vendor_stores vs
            WHERE 
                vs.owner_id = ?
            ORDER BY 
                vs.created_at DESC
        ");

        $stmt->execute([$binaryUserId]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary IDs to UUID strings
        foreach ($stores as &$store) {
            $store['uuid_id'] = binToUuid($store['id']);

            // Format location for display
            $store['location'] = $store['district'] . ', ' . $store['address'];

            // Set status message
            $store['subscription'] = $store['status'] === 'pending' ? 'Awaiting approval' : 'Active store';

            // Get store categories by joining with product_categories table
            $catStmt = $pdo->prepare("
                SELECT pc.name 
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status = 'active' 
                LIMIT 5
            ");
            $catStmt->execute([$store['id']]);
            $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
            $store['categories'] = $categories;

            // Remove binary ID and other unnecessary fields
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
 * Get stores managed by the current user
 */
function getManagedStores($pdo, $userId)
{
    try {
        $binaryUserId = $userId;

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
                (SELECT COUNT(*) FROM store_categories sc 
                 JOIN store_products sp ON sc.id = sp.store_category_id 
                 WHERE sc.store_id = vs.id AND sp.status = 'active') as product_count,
                (SELECT COUNT(*) FROM store_categories WHERE store_id = vs.id AND status = 'active') as category_count
            FROM 
                vendor_stores vs
            JOIN 
                store_managers sm ON vs.id = sm.store_id
            JOIN 
                zzimba_users u ON vs.owner_id = u.id
            WHERE 
                sm.user_id = ? AND vs.owner_id != ?
            ORDER BY 
                vs.created_at DESC
        ");

        $stmt->execute([$binaryUserId, $binaryUserId]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary IDs to UUID strings
        foreach ($stores as &$store) {
            $store['uuid_id'] = binToUuid($store['id']);
            $store['owner'] = $store['owner_username'];

            // Format location for display
            $store['location'] = $store['district'] . ', ' . $store['address'];

            // Get store categories by joining with product_categories table
            $catStmt = $pdo->prepare("
                SELECT pc.name 
                FROM store_categories sc
                JOIN product_categories pc ON sc.category_id = pc.id
                WHERE sc.store_id = ? AND sc.status = 'active' 
                LIMIT 5
            ");
            $catStmt->execute([$store['id']]);
            $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
            $store['categories'] = $categories;

            // Format role for display
            $store['role'] = ucwords(str_replace('_', ' ', $store['role']));

            // Remove binary ID and other unnecessary fields
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
 * Get detailed information about a specific store
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
        $binaryUserId = $userId;

        // Check if user owns or manages this store
        $stmt = $pdo->prepare("
            SELECT 1 FROM vendor_stores WHERE id = ? AND owner_id = ?
            UNION
            SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$binaryStoreId, $binaryUserId, $binaryStoreId, $binaryUserId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to access this store']);
            return;
        }

        // Get store details
        $stmt = $pdo->prepare("
            SELECT 
                vs.*,
                u.username as owner_username,
                u.email as owner_email,
                u.phone as owner_phone
            FROM 
                vendor_stores vs
            JOIN 
                zzimba_users u ON vs.owner_id = u.id
            WHERE 
                vs.id = ?
        ");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        // Convert binary ID to UUID string
        $store['uuid_id'] = binToUuid($store['id']);
        $store['owner_id'] = binToUuid($store['owner_id']);

        // Check if current user is the owner
        $store['is_owner'] = ($userId === $store['owner_id']);

        // Get user's role if they are a manager
        if (!$store['is_owner']) {
            $roleStmt = $pdo->prepare("SELECT role FROM store_managers WHERE store_id = ? AND user_id = ?");
            $roleStmt->execute([$binaryStoreId, $binaryUserId]);
            $role = $roleStmt->fetchColumn();
            $store['manager_role'] = $role ?: null;
        }

        // Get store categories with product_categories join
        $catStmt = $pdo->prepare("
            SELECT 
                sc.id, 
                pc.name, 
                pc.description, 
                sc.status, 
                sc.created_at 
            FROM 
                store_categories sc
            JOIN 
                product_categories pc ON sc.category_id = pc.id
            WHERE 
                sc.store_id = ?
        ");
        $catStmt->execute([$binaryStoreId]);
        $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as &$category) {
            $category['uuid_id'] = binToUuid($category['id']);
            unset($category['id']);
        }

        $store['categories'] = $categories;

        // Get store managers
        $managerStmt = $pdo->prepare("
            SELECT 
                sm.id, sm.user_id, sm.role, sm.created_at,
                u.username, u.email, u.phone
            FROM 
                store_managers sm
            JOIN 
                zzimba_users u ON sm.user_id = u.id
            WHERE 
                sm.store_id = ?
        ");
        $managerStmt->execute([$binaryStoreId]);
        $managers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($managers as &$manager) {
            $manager['uuid_id'] = binToUuid($manager['id']);
            $manager['user_uuid_id'] = binToUuid($manager['user_id']);
            unset($manager['id'], $manager['user_id']);
        }

        $store['managers'] = $managers;

        // Get product count
        $productStmt = $pdo->prepare("
            SELECT COUNT(*) FROM store_categories sc 
            JOIN store_products sp ON sc.id = sp.store_category_id 
            WHERE sc.store_id = ?
        ");
        $productStmt->execute([$binaryStoreId]);
        $store['product_count'] = $productStmt->fetchColumn();

        // Remove binary ID
        unset($store['id']);

        echo json_encode(['success' => true, 'store' => $store]);
    } catch (Exception $e) {
        error_log("Error getting store details: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error retrieving store details: ' . $e->getMessage()]);
    }
}

/**
 * Create a new store
 */
function createStore($pdo, $userId)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        return;
    }

    // Validate required fields
    $requiredFields = [
        'name',
        'business_email',
        'business_phone',
        'nature_of_operation',
        'region',
        'district',
        'address',
        'latitude',
        'longitude'
    ];

    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
            return;
        }
    }

    try {
        $binaryUserId = $userId;

        // Check if store name already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE name = ?");
        $stmt->execute([$data['name']]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this name already exists']);
            return;
        }

        // Check if store email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE business_email = ?");
        $stmt->execute([$data['business_email']]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this email already exists']);
            return;
        }

        // Check if store phone already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE business_phone = ?");
        $stmt->execute([$data['business_phone']]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'A store with this phone number already exists']);
            return;
        }

        // Generate UUID for the new store
        $storeId = generateUUIDv7();
        $binaryStoreId = uuidToBin($storeId);

        // Set default values for optional fields
        $description = $data['description'] ?? '';
        $subcounty = $data['subcounty'] ?? null;
        $parish = $data['parish'] ?? null;
        $logoUrl = $data['logo_url'] ?? null;
        $websiteUrl = $data['website_url'] ?? null;
        $socialMedia = $data['social_media'] ?? null;

        $now = date('Y-m-d H:i:s');

        // Insert the new store
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
            $binaryStoreId,
            $binaryUserId,
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

        // Move temp logo to permanent location if it exists
        if (!empty($data['temp_logo_path']) && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])) {
            $fileExt = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
            $storeDirPath = __DIR__ . '/../../img/stores/' . $storeId . '/logo';

            // Create store directory if it doesn't exist
            if (!file_exists($storeDirPath)) {
                mkdir($storeDirPath, 0755, true);
            }

            // Sanitize store name for filename
            $safeFileName = 'logo.' . $fileExt;
            $newLogoPath = $storeDirPath . '/' . $safeFileName;

            // Move the file
            rename(__DIR__ . '/../../' . $data['temp_logo_path'], $newLogoPath);

            // Update the logo URL in the database
            $logoUrl = 'img/stores/' . $storeId . '/logo/' . $safeFileName;
            $updateStmt = $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?");
            $updateStmt->execute([$logoUrl, $binaryStoreId]);
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
 * Update an existing store
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
        $binaryStoreId = uuidToBin($data['id']);
        $binaryUserId = $userId;

        // Check if user owns this store
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        if ($store['owner_id'] !== $binaryUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to update this store']);
            return;
        }

        // Check if new store name already exists (if name is being changed)
        if (isset($data['name'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE name = ? AND id != ?");
            $stmt->execute([$data['name'], $binaryStoreId]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'A store with this name already exists']);
                return;
            }
        }

        // Check if new store email or phone already exists (if being changed)
        if (isset($data['business_email'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE business_email = ? AND id != ?");
            $stmt->execute([$data['business_email'], $binaryStoreId]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'A store with this email already exists']);
                return;
            }
        }

        if (isset($data['business_phone'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_stores WHERE business_phone = ? AND id != ?");
            $stmt->execute([$data['business_phone'], $binaryStoreId]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'A store with this phone number already exists']);
                return;
            }
        }

        // Build update query dynamically based on provided fields
        $updateFields = [];
        $params = [];

        $allowedFields = [
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

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        // Always update the updated_at timestamp
        $updateFields[] = "updated_at = ?";
        $params[] = date('Y-m-d H:i:s');

        // Add store ID as the last parameter
        $params[] = $binaryStoreId;

        // Execute update query
        $stmt = $pdo->prepare("UPDATE vendor_stores SET " . implode(", ", $updateFields) . " WHERE id = ?");
        $stmt->execute($params);

        // Handle logo update if provided
        if (!empty($data['temp_logo_path']) && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])) {
            $fileExt = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
            $storeDirPath = __DIR__ . '/../../img/stores/' . $data['id'] . '/logo';

            // Create store directory if it doesn't exist
            if (!file_exists($storeDirPath)) {
                mkdir($storeDirPath, 0755, true);
            }

            // Remove old logo files
            $oldFiles = glob($storeDirPath . '/*');
            foreach ($oldFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            // Set logo filename
            $safeFileName = 'logo.' . $fileExt;
            $newLogoPath = $storeDirPath . '/' . $safeFileName;

            // Move the file
            rename(__DIR__ . '/../../' . $data['temp_logo_path'], $newLogoPath);

            // Update the logo URL in the database
            $logoUrl = 'img/stores/' . $data['id'] . '/logo/' . $safeFileName;
            $updateStmt = $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?");
            $updateStmt->execute([$logoUrl, $binaryStoreId]);
        } else if (isset($data['remove_logo']) && $data['remove_logo']) {
            // Remove logo if requested
            $storeDirPath = __DIR__ . '/../../img/stores/' . $data['id'] . '/logo';
            if (file_exists($storeDirPath)) {
                $oldFiles = glob($storeDirPath . '/*');
                foreach ($oldFiles as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }

            // Update the database to remove logo URL
            $updateStmt = $pdo->prepare("UPDATE vendor_stores SET logo_url = NULL WHERE id = ?");
            $updateStmt->execute([$binaryStoreId]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Store updated successfully'
        ]);
    } catch (Exception $e) {
        error_log("Error updating store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error updating store: ' . $e->getMessage()]);
    }
}

/**
 * Delete a store
 */
function deleteStore($pdo, $storeId, $userId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }

    try {
        $binaryStoreId = uuidToBin($storeId);
        $binaryUserId = $userId;

        // Check if user owns this store
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        if ($store['owner_id'] !== $binaryUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to delete this store']);
            return;
        }

        // Delete the store (foreign key constraints will handle related records)
        $stmt = $pdo->prepare("DELETE FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);

        // Delete store logo directory
        $storeDirPath = __DIR__ . '/../../img/stores/' . $storeId;
        if (file_exists($storeDirPath)) {
            deleteDirectory($storeDirPath);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Store deleted successfully'
        ]);
    } catch (Exception $e) {
        error_log("Error deleting store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error deleting store: ' . $e->getMessage()]);
    }
}

/**
 * Upload store logo
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
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($fileExt, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, WebP, and GIF files are allowed.']);
            return;
        }

        if ($fileSize > 2000000) { // 2MB
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 2MB allowed.']);
            return;
        }

        // Create temp directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid('temp_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        $relativePath = 'uploads/temp/' . $newFileName;

        // Process and resize image to a square (512x512)
        list($width, $height) = getimagesize($fileTmpName);
        $targetSize = 512;

        $sourceImage = null;
        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($fileTmpName);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($fileTmpName);
                break;
            case 'webp':
                $sourceImage = imagecreatefromwebp($fileTmpName);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($fileTmpName);
                break;
        }

        if (!$sourceImage) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to process image']);
            return;
        }

        $targetImage = imagecreatetruecolor($targetSize, $targetSize);

        // Preserve transparency for PNG and GIF
        if ($fileExt == 'png' || $fileExt == 'gif') {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $targetSize, $targetSize, $transparent);
        } else {
            imagefill($targetImage, 0, 0, imagecolorallocate($targetImage, 255, 255, 255));
        }

        // Calculate dimensions to maintain aspect ratio and crop to square
        if ($width > $height) {
            $sourceX = ($width - $height) / 2;
            $sourceY = 0;
            $sourceSize = $height;
        } else {
            $sourceX = 0;
            $sourceY = ($height - $width) / 2;
            $sourceSize = $width;
        }

        // Copy and resize the image to a square
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetSize,
            $targetSize,
            $sourceSize,
            $sourceSize
        );

        // Save the image
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

        imagedestroy($sourceImage);
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
 * Get categories for a specific store
 */
function getStoreCategories($pdo, $storeId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }

    try {
        $binaryStoreId = uuidToBin($storeId);

        $stmt = $pdo->prepare("
            SELECT 
                sc.id, 
                pc.name, 
                pc.description, 
                sc.status, 
                sc.created_at, 
                sc.updated_at
            FROM 
                store_categories sc
            JOIN 
                product_categories pc ON sc.category_id = pc.id
            WHERE 
                sc.store_id = ?
            ORDER BY 
                pc.name ASC
        ");

        $stmt->execute([$binaryStoreId]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary IDs to UUID strings
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
 * Get managers for a specific store
 */
function getStoreManagers($pdo, $storeId, $userId)
{
    if (empty($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Store ID is required']);
        return;
    }

    try {
        $binaryStoreId = uuidToBin($storeId);
        $binaryUserId = $userId;

        // Check if user owns or manages this store
        $stmt = $pdo->prepare("
            SELECT owner_id FROM vendor_stores WHERE id = ?
            UNION
            SELECT user_id FROM store_managers WHERE store_id = ? AND user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$binaryStoreId, $binaryStoreId, $binaryUserId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to access this store']);
            return;
        }

        // Get store managers
        $stmt = $pdo->prepare("
            SELECT 
                sm.id, sm.user_id, sm.role, sm.created_at,
                u.username, u.email, u.phone
            FROM 
                store_managers sm
            JOIN 
                zzimba_users u ON sm.user_id = u.id
            WHERE 
                sm.store_id = ?
            ORDER BY 
                sm.created_at DESC
        ");

        $stmt->execute([$binaryStoreId]);
        $managers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary IDs to UUID strings
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
        $binaryStoreId = uuidToBin($storeId);
        $binaryManagerId = uuidToBin($managerId);
        $binaryUserId = $userId;

        // Check if user owns this store
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        if ($store['owner_id'] !== $binaryUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to add managers to this store']);
            return;
        }

        // Check if the manager user exists
        $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE id = ?");
        $stmt->execute([$binaryManagerId]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
            return;
        }

        // Check if user is already a manager
        $stmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ?");
        $stmt->execute([$binaryStoreId, $binaryManagerId]);
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'User is already a manager for this store']);
            return;
        }

        // Add the manager
        $managerId = generateUUIDv7();
        $binaryManagerEntryId = uuidToBin($managerId);
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
            $binaryManagerEntryId,
            $binaryStoreId,
            $binaryManagerId,
            $role,
            $binaryUserId,
            $now,
            $now
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Manager added successfully',
            'manager_id' => $managerId
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
        $binaryStoreId = uuidToBin($storeId);
        $binaryManagerId = uuidToBin($managerId);
        $binaryUserId = $userId;

        // Check if user owns this store
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$store) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Store not found']);
            return;
        }

        if ($store['owner_id'] !== $binaryUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You do not have permission to remove managers from this store']);
            return;
        }

        // Remove the manager
        $stmt = $pdo->prepare("DELETE FROM store_managers WHERE id = ? AND store_id = ?");
        $stmt->execute([$binaryManagerId, $binaryStoreId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Manager not found for this store']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Manager removed successfully'
        ]);
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

        // Extract unique level 1 regions (provinces)
        $level1Options = [];
        foreach ($data['features'] as $feature) {
            $name = $feature['properties']['NAME_1'] ?? null;
            if ($name) {
                $level1Options[$name] = $name;
            }
        }

        // Sort alphabetically
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
 * Helper function to sanitize filenames
 */
function sanitizeFileName($name)
{
    // Replace spaces with hyphens
    $name = str_replace(' ', '-', $name);
    // Remove any non-alphanumeric characters except hyphens and underscores
    $name = preg_replace('/[^A-Za-z0-9\-_]/', '', $name);
    // Convert to lowercase
    $name = strtolower($name);
    return $name;
}

/**
 * Helper function to get store name by ID
 */
function getStoreName($pdo, $binaryStoreId)
{
    $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
    $stmt->execute([$binaryStoreId]);
    return $stmt->fetchColumn() ?: 'store';
}

// Add helper function to recursively delete directories
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
