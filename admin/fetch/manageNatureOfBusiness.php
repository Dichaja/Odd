<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) ||
    !$_SESSION['user']['logged_in'] ||
    !$_SESSION['user']['is_admin']
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

try {
    // Create table if it doesn't exist
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS nature_of_business (
            id VARCHAR(26) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            icon VARCHAR(100),
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY name_unique (name)
        )"
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getBusinessTypes':
            getBusinessTypes($pdo);
            break;
        case 'getBusinessType':
            getBusinessType($pdo);
            break;
        case 'createBusinessType':
            createBusinessType($pdo);
            break;
        case 'updateBusinessType':
            updateBusinessType($pdo);
            break;
        case 'deleteBusinessType':
            deleteBusinessType($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function getBusinessTypes(PDO $pdo)
{
    try {
        // Get vendor counts for each business type
        $vendorCountQuery = $pdo->query(
            'SELECT nature_of_business as business_id, COUNT(*) as vendor_count 
             FROM vendor_stores 
             WHERE nature_of_business IS NOT NULL
             GROUP BY nature_of_business'
        );
        $vendorCounts = [];
        while ($row = $vendorCountQuery->fetch(PDO::FETCH_ASSOC)) {
            $vendorCounts[$row['business_id']] = $row['vendor_count'];
        }

        $stmt = $pdo->query(
            'SELECT id, name, description, status, icon, created_at, updated_at
             FROM nature_of_business
             ORDER BY name'
        );
        $businessTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add vendor count to each business type
        foreach ($businessTypes as &$type) {
            $type['vendor_count'] = $vendorCounts[$type['id']] ?? 0;
        }

        echo json_encode(['success' => true, 'businessTypes' => $businessTypes]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving business types']);
    }
}

function getBusinessType(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing business type ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT id, name, description, status, icon, created_at, updated_at
             FROM nature_of_business
             WHERE id = :id'
        );
        $stmt->execute([':id' => $_GET['id']]);
        $businessType = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$businessType) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Business type not found']);
            return;
        }

        // Get vendor count for this business type
        $vendorCountQuery = $pdo->prepare(
            'SELECT COUNT(*) as vendor_count 
             FROM vendor_stores 
             WHERE nature_of_business = :id'
        );
        $vendorCountQuery->execute([':id' => $_GET['id']]);
        $vendorCount = $vendorCountQuery->fetchColumn();
        $businessType['vendor_count'] = $vendorCount;

        echo json_encode(['success' => true, 'data' => $businessType]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving business type']);
    }
}

function createBusinessType(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['name']) || trim($data['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Business type name is required']);
        return;
    }
    $name = trim($data['name']);
    $description = trim($data['description'] ?? '');
    $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $icon = trim($data['icon'] ?? '');

    try {
        $stmt = $pdo->prepare('SELECT id FROM nature_of_business WHERE name = :name');
        $stmt->execute([':name' => $name]);
        if ($stmt->rowCount()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A business type with this name already exists']);
            return;
        }

        $id = generateUlid();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'INSERT INTO nature_of_business
             (id, name, description, status, icon, created_at, updated_at)
             VALUES
             (:id, :name, :description, :status, :icon, :created_at, :updated_at)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description,
            ':status' => $status,
            ':icon' => $icon,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);

        echo json_encode(['success' => true, 'message' => 'Business type created', 'id' => $id]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating business type']);
    }
}

function updateBusinessType(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['name']) || trim($data['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Business type ID and name are required']);
        return;
    }

    $id = $data['id'];
    $name = trim($data['name']);
    $description = trim($data['description'] ?? '');
    $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $icon = trim($data['icon'] ?? '');

    try {
        $stmt = $pdo->prepare('SELECT id FROM nature_of_business WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Business type not found']);
            return;
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM nature_of_business WHERE name = :name AND id != :id'
        );
        $stmt->execute([':name' => $name, ':id' => $id]);
        if ($stmt->rowCount()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A business type with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'UPDATE nature_of_business SET
             name = :name,
             description = :description,
             status = :status,
             icon = :icon,
             updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':status' => $status,
            ':icon' => $icon,
            ':updated_at' => $now,
            ':id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Business type updated']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating business type']);
    }
}

function deleteBusinessType(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing business type ID']);
        return;
    }

    $id = $data['id'];

    try {
        // Check if business type exists
        $stmt = $pdo->prepare('SELECT id FROM nature_of_business WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Business type not found']);
            return;
        }

        // Check if any vendors are using this business type
        $vendorCheckStmt = $pdo->prepare('SELECT COUNT(*) FROM vendor_stores WHERE nature_of_business = :id');
        $vendorCheckStmt->execute([':id' => $id]);
        $vendorCount = $vendorCheckStmt->fetchColumn();

        if ($vendorCount > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete business type. It is being used by ' . $vendorCount . ' vendor(s).'
            ]);
            return;
        }

        // Delete the business type
        $stmt = $pdo->prepare('DELETE FROM nature_of_business WHERE id = :id');
        $stmt->execute([':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Business type deleted']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting business type']);
    }
}