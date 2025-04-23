<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../mail/Mailer.php';

use ZzimbaOnline\Mail\Mailer;

header('Content-Type: application/json');

if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expired', 'session_expired' => true]);
    exit;
}

$currentUserId = $_SESSION['user']['user_id'] ?? null;
if (!$currentUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid user session']);
    exit;
}

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

        case 'getPendingInvitations':
            getPendingInvitations($pdo, $currentUserId);
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

        case 'approveManagerInvitation':
            approveManagerInvitation($pdo, $_POST['managerId'] ?? '', $currentUserId);
            break;

        case 'denyManagerInvitation':
            denyManagerInvitation($pdo, $_POST['managerId'] ?? '', $currentUserId);
            break;

        case 'getRegions':
            getRegions();
            break;

        case 'getNatureOfBusiness':
            getNatureOfBusiness($pdo);
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

function isValidUlid(string $id): bool
{
    return (bool) preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $id);
}

function deleteDirectory(string $dir): bool
{
    if (!file_exists($dir)) {
        return true;
    }
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..')
            continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    return rmdir($dir);
}

function isOwner(PDO $pdo, string $storeId, string $userId): bool
{
    $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
    $stmt->execute([$storeId]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);
    return $store && $store['owner_id'] === $userId;
}

function canAccessStore(PDO $pdo, string $storeId, string $userId): bool
{
    if (isOwner($pdo, $storeId, $userId)) {
        return true;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$storeId, $userId]);
    return $stmt->rowCount() > 0;
}

function storeFieldExists(PDO $pdo, string $field, string $value, string $excludeId = null, string $ownerId = null): bool
{
    $sql = "SELECT COUNT(*) FROM vendor_stores WHERE $field = ?";
    $params = [$value];
    if ($ownerId !== null) {
        $sql .= " AND owner_id != ?";
        $params[] = $ownerId;
    }
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

function initializeTables(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS nature_of_business (
            id VARCHAR(26) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            icon VARCHAR(100),
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY name_unique (name)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS vendor_stores (
            id VARCHAR(26) PRIMARY KEY,
            owner_id VARCHAR(26) NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            business_email VARCHAR(100) NOT NULL,
            business_phone VARCHAR(20) NOT NULL,
            nature_of_business VARCHAR(26) NULL,
            region VARCHAR(100) NOT NULL,
            district VARCHAR(100) NOT NULL,
            subcounty VARCHAR(100),
            parish VARCHAR(100),
            address TEXT NOT NULL,
            latitude DECIMAL(10,8) NOT NULL,
            longitude DECIMAL(11,8) NOT NULL,
            logo_url VARCHAR(255),
            website_url VARCHAR(255),
            social_media TEXT,
            status ENUM('active','pending','inactive','suspended') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (owner_id) REFERENCES zzimba_users(id) ON DELETE CASCADE,
            FOREIGN KEY (nature_of_business) REFERENCES nature_of_business(id)
                ON DELETE SET NULL ON UPDATE CASCADE,
            UNIQUE KEY store_name_unique  (name),
            UNIQUE KEY store_email_unique (business_email),
            UNIQUE KEY store_phone_unique (business_phone)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS store_categories (
            id VARCHAR(26) PRIMARY KEY,
            store_id VARCHAR(26) NOT NULL,
            category_id VARCHAR(26) NOT NULL,
            status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_id)     REFERENCES vendor_stores(id)    ON DELETE CASCADE,
            FOREIGN KEY (category_id)  REFERENCES product_categories(id),
            UNIQUE KEY store_category_unique (store_id, category_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS store_products (
            id VARCHAR(26) PRIMARY KEY,
            store_category_id VARCHAR(26) NOT NULL,
            product_id VARCHAR(26) NOT NULL,
            status ENUM('active','inactive','deleted') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_category_id) REFERENCES store_categories(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id)         REFERENCES products(id),
            UNIQUE KEY store_product_unique (store_category_id, product_id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS store_managers (
            id VARCHAR(26) PRIMARY KEY,
            store_id VARCHAR(26) NOT NULL,
            user_id VARCHAR(26) NOT NULL,
            role ENUM('manager','inventory_manager','sales_manager','content_manager') NOT NULL DEFAULT 'manager',
            status ENUM('active','inactive','removed') NOT NULL DEFAULT 'inactive',
            added_by VARCHAR(26) NOT NULL,
            approved TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (store_id) REFERENCES vendor_stores(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id)    REFERENCES zzimba_users(id) ON DELETE CASCADE,
            FOREIGN KEY (added_by)   REFERENCES zzimba_users(id),
            UNIQUE KEY store_manager_unique (store_id, user_id)
        )
    ");
}

function sendManagerApprovalNotification(string $email, string $firstName, string $lastName, string $storeName): bool
{
    $subject = "Store Manager Invitation Approved - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Invitation Approved</h2>
            <p>Hello ' . htmlspecialchars($firstName . ' ' . $lastName) . ',</p>
            <p>You have approved the invitation to manage the store <strong>' . htmlspecialchars($storeName) . '</strong> on Zzimba Online.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;text-align:center;">
                <h3 style="margin-top:0;color:#10B981;">You now have access to manage this store</h3>
            </div>
            
            <p>You can now access the store management features according to your assigned role.</p>
            
            <div style="margin:20px 0;text-align:center;">
                <a href="https://zzimbaonline.com/account/zzimba-stores" style="display:inline-block;padding:12px 24px;background-color:#D92B13;color:#ffffff;text-decoration:none;font-weight:500;border-radius:4px;">
                    Go to My Zzimba Stores
                </a>
            </div>
            
            <p>If you have any questions about your role or responsibilities, please contact the store owner or our support team.</p>
        </div>';

    return Mailer::sendMail($email, $subject, $content);
}

function sendManagerDenialNotification(string $ownerEmail, string $ownerName, string $managerName, string $storeName): bool
{
    $subject = "Store Manager Invitation Declined - Zzimba Online";

    $content = '
        <div style="padding:20px 0;">
            <h2>Store Manager Invitation Declined</h2>
            <p>Hello ' . htmlspecialchars($ownerName) . ',</p>
            <p>This email is to inform you that <strong>' . htmlspecialchars($managerName) . '</strong> has declined your invitation to manage the store <strong>' . htmlspecialchars($storeName) . '</strong> on Zzimba Online.</p>
            
            <div style="margin:20px 0;padding:15px;background-color:#f5f5f5;border-radius:5px;text-align:center;">
                <p style="font-size:18px;color:#F59E0B;">The invitation has been declined</p>
            </div>
            
            <p>If you believe this was done in error or would like to invite someone else, you can do so from your store management dashboard.</p>
            
            <div style="margin:20px 0;text-align:center;">
                <a href="https://zzimbaonline.com/account/zzimba-stores" style="display:inline-block;padding:12px 24px;background-color:#D92B13;color:#ffffff;text-decoration:none;font-weight:500;border-radius:4px;">
                    Manage Your Stores
                </a>
            </div>
        </div>';

    return Mailer::sendMail($ownerEmail, $subject, $content);
}

function getNatureOfBusiness(PDO $pdo): void
{
    $stmt = $pdo->prepare("
        SELECT id, name, description, icon, status 
        FROM nature_of_business 
        WHERE status = 'active'
        ORDER BY name ASC
    ");
    $stmt->execute();
    $natureOfBusiness = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'natureOfBusiness' => $natureOfBusiness]);
}

function getOwnedStores(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("
        SELECT 
            vs.id, vs.name, vs.description, vs.business_email,
            vs.business_phone, vs.nature_of_business, nob.name as nature_of_business_name,
            vs.region, vs.district, vs.address, vs.logo_url,
            vs.status, vs.created_at,
            (
                SELECT COUNT(*) 
                  FROM product_pricing pp
                  JOIN store_products sp ON pp.store_products_id = sp.id
                  JOIN store_categories sc ON sc.id = sp.store_category_id 
                 WHERE sc.store_id = vs.id AND sp.status = 'active'
            ) AS product_count
        FROM vendor_stores vs
        LEFT JOIN nature_of_business nob ON vs.nature_of_business = nob.id
        WHERE vs.owner_id = ?
        ORDER BY vs.created_at DESC
    ");
    $stmt->execute([$userId]);
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stores as &$s) {
        $s['uuid_id'] = $s['id'];
        $s['location'] = $s['district'] . ', ' . $s['address'];
        $s['subscription'] = $s['status'] === 'pending'
            ? 'Awaiting approval'
            : 'Active store';

        $catStmt = $pdo->prepare("
            SELECT pc.name
              FROM store_categories sc
              JOIN product_categories pc ON sc.category_id = pc.id
             WHERE sc.store_id = ? AND sc.status = 'active'
             LIMIT 5
        ");
        $catStmt->execute([$s['id']]);
        $s['categories'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);

        unset($s['id'], $s['district'], $s['address']);
    }

    echo json_encode(['success' => true, 'stores' => $stores]);
}

function getManagedStores(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("
        SELECT 
            vs.id, vs.name, vs.description, vs.business_email,
            vs.business_phone, vs.nature_of_business, nob.name as nature_of_business_name,
            vs.region, vs.district, vs.address, vs.logo_url,
            vs.status, sm.role, u.username AS owner,
            vs.created_at,
            (
                SELECT COUNT(*) 
                  FROM product_pricing pp
                  JOIN store_products sp ON pp.store_products_id = sp.id
                  JOIN store_categories sc ON sc.id = sp.store_category_id
                 WHERE sc.store_id = vs.id AND sp.status = 'active'
            ) AS product_count
        FROM vendor_stores vs
        LEFT JOIN nature_of_business nob ON vs.nature_of_business = nob.id
        JOIN store_managers sm ON vs.id = sm.store_id
        JOIN zzimba_users u      ON vs.owner_id = u.id
        WHERE sm.user_id = ? AND vs.owner_id != ? AND sm.status = 'active' AND sm.approved = 1
        ORDER BY vs.created_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stores as &$s) {
        $s['uuid_id'] = $s['id'];
        $s['role'] = ucwords(str_replace('_', ' ', $s['role']));
        $s['location'] = $s['district'] . ', ' . $s['address'];

        $catStmt = $pdo->prepare("
            SELECT pc.name
              FROM store_categories sc
              JOIN product_categories pc ON sc.category_id = pc.id
             WHERE sc.store_id = ? AND sc.status = 'active'
             LIMIT 5
        ");
        $catStmt->execute([$s['id']]);
        $s['categories'] = $catStmt->fetchAll(PDO::FETCH_COLUMN);

        unset($s['id'], $s['district'], $s['address']);
    }

    echo json_encode(['success' => true, 'stores' => $stores]);
}

function getPendingInvitations(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("
        SELECT 
            sm.id AS manager_id,
            sm.role,
            sm.created_at,
            vs.id AS store_id,
            vs.name AS store_name,
            vs.logo_url,
            u.first_name AS owner_first_name,
            u.last_name AS owner_last_name,
            u.email AS owner_email
        FROM store_managers sm
        JOIN vendor_stores vs ON sm.store_id = vs.id
        JOIN zzimba_users u ON vs.owner_id = u.id
        WHERE sm.user_id = ? AND sm.status = 'inactive' AND sm.approved = 0
        ORDER BY sm.created_at DESC
    ");
    $stmt->execute([$userId]);
    $invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($invitations as &$inv) {
        $inv['role_display'] = ucwords(str_replace('_', ' ', $inv['role']));
        $inv['owner_name'] = $inv['owner_first_name'] . ' ' . $inv['owner_last_name'];
        unset($inv['owner_first_name'], $inv['owner_last_name']);
    }

    echo json_encode(['success' => true, 'invitations' => $invitations]);
}

function getStoreDetails(PDO $pdo, string $storeId, string $userId): void
{
    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    if (!canAccessStore($pdo, $storeId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT vs.*, nob.name as nature_of_business_name, u.username AS owner_username, u.email AS owner_email, u.phone AS owner_phone
        FROM vendor_stores vs
        LEFT JOIN nature_of_business nob ON vs.nature_of_business = nob.id
        JOIN zzimba_users u ON vs.owner_id = u.id
        WHERE vs.id = ?
    ");
    $stmt->execute([$storeId]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$store) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Store not found']);
        return;
    }

    $store['uuid_id'] = $store['id'];
    $store['is_owner'] = $store['owner_id'] === $userId;

    $catStmt = $pdo->prepare("
        SELECT sc.id, pc.name, pc.description, sc.status, sc.created_at
        FROM store_categories sc
        JOIN product_categories pc ON sc.category_id = pc.id
        WHERE sc.store_id = ?
    ");
    $catStmt->execute([$storeId]);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as &$c) {
        $c['uuid_id'] = $c['id'];
        unset($c['id']);
    }
    $store['categories'] = $categories;

    $mgrStmt = $pdo->prepare("
        SELECT sm.id, sm.user_id, sm.role, sm.created_at, u.username, u.email, u.phone
        FROM store_managers sm
        JOIN zzimba_users u ON sm.user_id = u.id
        WHERE sm.store_id = ?
    ");
    $mgrStmt->execute([$storeId]);
    $managers = $mgrStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($managers as &$m) {
        $m['uuid_id'] = $m['id'];
        $m['user_uuid_id'] = $m['user_id'];
        unset($m['id'], $m['user_id']);
    }
    $store['managers'] = $managers;

    unset($store['id']);

    echo json_encode(['success' => true, 'store' => $store]);
}

function createStore(PDO $pdo, string $userId): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    foreach (['name', 'business_email', 'business_phone', 'nature_of_business', 'region', 'district', 'address', 'latitude', 'longitude'] as $f) {
        if (empty($data[$f])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Field '$f' is required"]);
            return;
        }
    }

    try {
        if (storeFieldExists($pdo, 'name', $data['name'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Name in use']);
            return;
        }
        if (storeFieldExists($pdo, 'business_email', $data['business_email'])) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Email in use']);
            return;
        }
        if (storeFieldExists($pdo, 'business_phone', $data['business_phone'], null, $userId)) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Business_phone in use']);
            return;
        }

        $storeId = generateUlid();
        $now = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            INSERT INTO vendor_stores (
                id, owner_id, name, description, business_email, business_phone,
                nature_of_business, region, district, subcounty, parish, address,
                latitude, longitude, logo_url, website_url, social_media,
                status, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?
            )
        ");
        $stmt->execute([
            $storeId,
            $userId,
            $data['name'],
            $data['description'] ?? '',
            $data['business_email'],
            $data['business_phone'],
            $data['nature_of_business'],
            $data['region'],
            $data['district'],
            $data['subcounty'] ?? null,
            $data['parish'] ?? null,
            $data['address'],
            $data['latitude'],
            $data['longitude'],
            $data['logo_url'] ?? null,
            $data['website_url'] ?? null,
            $data['social_media'] ?? null,
            $now,
            $now
        ]);

        if (
            !empty($data['temp_logo_path'])
            && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])
        ) {
            $ext = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
            $dir = __DIR__ . '/../../img/stores/' . $storeId . '/logo';
            mkdir($dir, 0755, true);
            $name = 'logo.' . $ext;
            rename(
                __DIR__ . '/../../' . $data['temp_logo_path'],
                "$dir/$name"
            );
            $url = 'img/stores/' . $storeId . '/logo/' . $name;
            $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?")
                ->execute([$url, $storeId]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Store created, pending approval',
            'store_id' => $storeId
        ]);
    } catch (Exception $e) {
        error_log("Error creating store: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Creation failed']);
    }
}

function updateStore(PDO $pdo, string $userId): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || !isValidUlid($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing or invalid store ID']);
        return;
    }

    $storeId = $data['id'];

    if (!isOwner($pdo, $storeId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }

    $stmt = $pdo->prepare("SELECT name, business_email, business_phone FROM vendor_stores WHERE id = ?");
    $stmt->execute([$storeId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    $fields = [
        'name',
        'description',
        'business_email',
        'business_phone',
        'nature_of_business',
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

    $updates = [];
    $params = [];

    foreach ($fields as $f) {
        if (isset($data[$f])) {
            if (in_array($f, ['name', 'business_email', 'business_phone'])) {
                if (
                    $data[$f] !== $current[$f]
                    && storeFieldExists($pdo, $f, $data[$f], $storeId, $userId)
                ) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'error' => ucfirst($f) . ' in use']);
                    return;
                }
            }
            $updates[] = "$f = ?";
            $params[] = $data[$f];
        }
    }

    if ($updates) {
        $updates[] = "updated_at = ?";
        $params[] = date('Y-m-d H:i:s');
        $params[] = $storeId;

        $sql = "UPDATE vendor_stores SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    if (
        !empty($data['temp_logo_path'])
        && file_exists(__DIR__ . '/../../' . $data['temp_logo_path'])
    ) {
        $ext = pathinfo($data['temp_logo_path'], PATHINFO_EXTENSION);
        $dir = __DIR__ . '/../../img/stores/' . $storeId . '/logo';
        $files = glob("$dir/*");
        foreach ($files as $f) {
            is_file($f) && unlink($f);
        }
        mkdir($dir, 0755, true);
        $name = 'logo.' . $ext;
        rename(__DIR__ . '/../../' . $data['temp_logo_path'], "$dir/$name");
        $url = 'img/stores/' . $storeId . '/logo/' . $name;
        $pdo->prepare("UPDATE vendor_stores SET logo_url = ? WHERE id = ?")
            ->execute([$url, $storeId]);
    } elseif (!empty($data['remove_logo'])) {
        $dir = __DIR__ . '/../../img/stores/' . $storeId . '/logo';
        deleteDirectory($dir);
        $pdo->prepare("UPDATE vendor_stores SET logo_url = NULL WHERE id = ?")
            ->execute([$storeId]);
    }

    echo json_encode(['success' => true, 'message' => 'Store updated']);
}

function deleteStore(PDO $pdo, string $storeId, string $userId): void
{
    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    if (!isOwner($pdo, $storeId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }

    $pdo->prepare("DELETE FROM vendor_stores WHERE id = ?")
        ->execute([$storeId]);

    deleteDirectory(__DIR__ . '/../../img/stores/' . $storeId);

    echo json_encode(['success' => true, 'message' => 'Store deleted']);
}

function uploadLogo(): void
{
    if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Upload error']);
        return;
    }

    $file = $_FILES['logo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed) || $file['size'] > 2_000_000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file']);
        return;
    }

    $tmpDir = __DIR__ . '/../../uploads/temp/';
    mkdir($tmpDir, 0755, true);
    $name = uniqid('temp_') . ".$ext";
    $path = $tmpDir . $name;

    $imgInfo = getimagesize($file['tmp_name']);
    $src = match ($ext) {
        'jpg', 'jpeg' => imagecreatefromjpeg($file['tmp_name']),
        'png' => imagecreatefrompng($file['tmp_name']),
        'webp' => imagecreatefromwebp($file['tmp_name']),
        'gif' => imagecreatefromgif($file['tmp_name']),
        default => null
    };
    if (!$src) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Image processing error']);
        return;
    }

    $size = 512;
    $dst = imagecreatetruecolor($size, $size);
    if (in_array($ext, ['png', 'gif'])) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $bg = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $size, $size, $bg);
    } else {
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    }

    [$w, $h] = $imgInfo;
    if ($w > $h) {
        $srcX = ($w - $h) / 2;
        $srcY = 0;
        $srcS = $h;
    } else {
        $srcX = 0;
        $srcY = ($h - $w) / 2;
        $srcS = $w;
    }

    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $srcS, $srcS);

    match ($ext) {
        'jpg', 'jpeg' => imagejpeg($dst, $path, 90),
        'png' => imagepng($dst, $path, 9),
        'webp' => imagewebp($dst, $path, 90),
        'gif' => imagegif($dst, $path),
    };

    imagedestroy($src);
    imagedestroy($dst);

    echo json_encode([
        'success' => true,
        'temp_path' => 'uploads/temp/' . $name,
        'url' => BASE_URL . 'uploads/temp/' . $name
    ]);
}

function getStoreCategories(PDO $pdo, string $storeId): void
{
    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    $stmt = $pdo->prepare("
        SELECT sc.id, pc.name, pc.description, sc.status, sc.created_at
        FROM store_categories sc
        JOIN product_categories pc ON sc.category_id = pc.id
        WHERE sc.store_id = ?
        ORDER BY pc.name
    ");
    $stmt->execute([$storeId]);
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cats as &$c) {
        $c['uuid_id'] = $c['id'];
        unset($c['id']);
    }

    echo json_encode(['success' => true, 'categories' => $cats]);
}

function getStoreManagers(PDO $pdo, string $storeId, string $userId): void
{
    if (!isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
        return;
    }
    $stmt = $pdo->prepare("
        SELECT sm.id, sm.user_id, sm.role, sm.created_at,
               u.username, u.email, u.phone
        FROM store_managers sm
        JOIN zzimba_users u ON sm.user_id = u.id
        WHERE sm.store_id = ?
        ORDER BY sm.created_at DESC
    ");
    $stmt->execute([$storeId]);
    $mgrs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mgrs as &$m) {
        $m['uuid_id'] = $m['id'];
        $m['user_uuid_id'] = $m['user_id'];
        unset($m['id'], $m['user_id']);
    }

    echo json_encode(['success' => true, 'managers' => $mgrs]);
}

function addStoreManager(PDO $pdo, string $storeId, string $managerId, string $userId): void
{
    if (!isValidUlid($storeId) || !isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid IDs']);
        return;
    }
    if (!isOwner($pdo, $storeId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM zzimba_users WHERE id = ?");
    $stmt->execute([$managerId]);
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    $stmt = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = ? AND user_id = ?");
    $stmt->execute([$storeId, $managerId]);
    if ($stmt->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'Already manager']);
        return;
    }
    $entryId = generateUlid();
    $role = $_POST['role'] ?? 'manager';
    $now = date('Y-m-d H:i:s');

    $pdo->prepare("
        INSERT INTO store_managers
          (id, store_id, user_id, role, status, added_by, approved, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'inactive', ?, 0, ?, ?)
    ")->execute([
                $entryId,
                $storeId,
                $managerId,
                $role,
                $userId,
                $now,
                $now
            ]);

    echo json_encode(['success' => true, 'manager_id' => $entryId]);
}

function removeStoreManager(PDO $pdo, string $storeId, string $managerId, string $userId): void
{
    if (!isValidUlid($storeId) || !isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid IDs']);
        return;
    }
    if (!isOwner($pdo, $storeId, $userId)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        return;
    }
    $stmt = $pdo->prepare("DELETE FROM store_managers WHERE id = ? AND store_id = ?");
    $stmt->execute([$managerId, $storeId]);
    if (!$stmt->rowCount()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Manager not found']);
        return;
    }
    echo json_encode(['success' => true, 'message' => 'Manager removed']);
}

function approveManagerInvitation(PDO $pdo, string $managerId, string $userId): void
{
    if (!isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid manager ID']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT 
            sm.id, sm.store_id, sm.role,
            vs.name AS store_name,
            u.email, u.first_name, u.last_name
        FROM store_managers sm
        JOIN vendor_stores vs ON sm.store_id = vs.id
        JOIN zzimba_users u ON u.id = ?
        WHERE sm.id = ? AND sm.user_id = ? AND sm.status = 'inactive' AND sm.approved = 0
    ");
    $stmt->execute([$userId, $managerId, $userId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Invitation not found or already processed']);
        return;
    }

    $updateStmt = $pdo->prepare("
        UPDATE store_managers 
        SET status = 'active', approved = 1, updated_at = NOW()
        WHERE id = ?
    ");
    $updateStmt->execute([$managerId]);

    $emailSent = sendManagerApprovalNotification(
        $manager['email'],
        $manager['first_name'],
        $manager['last_name'],
        $manager['store_name']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Manager invitation approved',
        'email_sent' => $emailSent
    ]);
}

function denyManagerInvitation(PDO $pdo, string $managerId, string $userId): void
{
    if (!isValidUlid($managerId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid manager ID']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT 
            sm.id, sm.store_id,
            vs.name AS store_name,
            o.email AS owner_email, 
            o.first_name AS owner_first_name,
            o.last_name AS owner_last_name,
            u.first_name AS manager_first_name,
            u.last_name AS manager_last_name
        FROM store_managers sm
        JOIN vendor_stores vs ON sm.store_id = vs.id
        JOIN zzimba_users o ON vs.owner_id = o.id
        JOIN zzimba_users u ON u.id = ?
        WHERE sm.id = ? AND sm.user_id = ? AND sm.status = 'inactive' AND sm.approved = 0
    ");
    $stmt->execute([$userId, $managerId, $userId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$manager) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Invitation not found or already processed']);
        return;
    }

    $deleteStmt = $pdo->prepare("DELETE FROM store_managers WHERE id = ?");
    $deleteStmt->execute([$managerId]);

    $managerName = $manager['manager_first_name'] . ' ' . $manager['manager_last_name'];
    $ownerName = $manager['owner_first_name'] . ' ' . $manager['owner_last_name'];

    $emailSent = sendManagerDenialNotification(
        $manager['owner_email'],
        $ownerName,
        $managerName,
        $manager['store_name']
    );

    echo json_encode([
        'success' => true,
        'message' => 'Manager invitation declined',
        'email_sent' => $emailSent
    ]);
}

function getRegions(): void
{
    $file = __DIR__ . '/../../locations/gadm41_UGA_4.json';
    if (!file_exists($file)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Regions file missing']);
        return;
    }
    $data = json_decode(file_get_contents($file), true);
    $options = [];
    foreach ($data['features'] as $f) {
        $name = $f['properties']['NAME_1'] ?? null;
        if ($name)
            $options[] = $name;
    }
    sort($options);
    echo json_encode(['success' => true, 'regions' => $options]);
}
