<?php
// Turn off output buffering to prevent any output before headers
ob_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set timezone to Africa/Kampala
date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_packages (
        id BINARY(16) PRIMARY KEY,
        package_name VARCHAR(100) NOT NULL,
        unit_of_measure VARCHAR(50) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY package_unit_unique (package_name, unit_of_measure)
    )");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

// Get action from query parameter instead of PATH_INFO
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getPackages':
            getPackages($pdo);
            break;

        case 'getPackage':
            getPackage($pdo);
            break;

        case 'createPackage':
            createPackage($pdo);
            break;

        case 'updatePackage':
            updatePackage($pdo);
            break;

        case 'deletePackage':
            deletePackage($pdo);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in manageProductPackages: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getPackages($pdo)
{
    try {
        // Use a query that doesn't rely on BIN_TO_UUID
        $stmt = $pdo->prepare("SELECT id, package_name, unit_of_measure, created_at, updated_at FROM product_packages ORDER BY package_name, unit_of_measure");
        $stmt->execute();

        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary UUIDs to string format in PHP
        foreach ($packages as &$package) {
            $package['uuid_id'] = binToUuid($package['id']);
            // Ensure id is not sent as binary data in JSON
            unset($package['id']);
        }

        // Prepare the response
        $response = ['success' => true, 'packages' => $packages];

        // Log the response for debugging
        error_log("getPackages response: " . json_encode($response));

        // Send the response
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Error in getPackages: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving packages: ' . $e->getMessage()]);
    }
}

function getPackage($pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing package ID']);
        return;
    }

    try {
        $packageId = $_GET['id'];

        // Convert UUID string to binary for database query
        $binaryId = uuidToBin($packageId);

        // Use a query that doesn't rely on UUID_TO_BIN
        $stmt = $pdo->prepare("SELECT id, package_name, unit_of_measure, created_at, updated_at FROM product_packages WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$package) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            return;
        }

        // Convert binary UUID to string format in PHP
        $package['uuid_id'] = binToUuid($package['id']);
        // Ensure id is not sent as binary data in JSON
        unset($package['id']);

        echo json_encode(['success' => true, 'data' => $package]);
    } catch (Exception $e) {
        error_log("Error in getPackage: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving package: ' . $e->getMessage()]);
    }
}

function createPackage($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['package_name']) || !isset($data['unit_of_measure'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $packageName = trim($data['package_name']);
        $unitOfMeasure = trim($data['unit_of_measure']);

        if (empty($packageName) || empty($unitOfMeasure)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Package name and unit of measure cannot be empty']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_packages WHERE package_name = :package_name AND unit_of_measure = :unit_of_measure");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':unit_of_measure', $unitOfMeasure);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name and unit of measure already exists']);
            return;
        }

        $packageId = uuidToBin(generateUUIDv7());
        // Use Africa/Kampala timezone
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_packages (id, package_name, unit_of_measure, created_at, updated_at) VALUES (:id, :package_name, :unit_of_measure, :created_at, :updated_at)");
        $stmt->bindParam(':id', $packageId, PDO::PARAM_LOB);
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':unit_of_measure', $unitOfMeasure);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        $response = [
            'success' => true,
            'message' => 'Package created successfully',
            'id' => binToUuid($packageId)
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Error in createPackage: " . $e->getMessage());
        if ($e->getCode() == 23000) { // Duplicate entry error
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name and unit of measure already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating package: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in createPackage: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating package: ' . $e->getMessage()]);
    }
}

function updatePackage($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['package_name']) || !isset($data['unit_of_measure'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $packageId = $data['id'];
        $packageName = trim($data['package_name']);
        $unitOfMeasure = trim($data['unit_of_measure']);

        if (empty($packageName) || empty($unitOfMeasure)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Package name and unit of measure cannot be empty']);
            return;
        }

        // Convert UUID string to binary for database query
        $binaryId = uuidToBin($packageId);

        $stmt = $pdo->prepare("SELECT id FROM product_packages WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_packages WHERE package_name = :package_name AND unit_of_measure = :unit_of_measure AND id != :id");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':unit_of_measure', $unitOfMeasure);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name and unit of measure already exists']);
            return;
        }

        // Use Africa/Kampala timezone
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE product_packages SET package_name = :package_name, unit_of_measure = :unit_of_measure, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':unit_of_measure', $unitOfMeasure);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Package updated successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in updatePackage: " . $e->getMessage());
        if ($e->getCode() == 23000) { // Duplicate entry error
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name and unit of measure already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating package: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in updatePackage: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating package: ' . $e->getMessage()]);
    }
}

function deletePackage($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing package ID']);
            return;
        }

        $packageId = $data['id'];

        // Convert UUID string to binary for database query
        $binaryId = uuidToBin($packageId);

        $stmt = $pdo->prepare("SELECT id FROM product_packages WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package not found']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM product_packages WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in deletePackage: " . $e->getMessage());
        if ($e->getCode() == '23000') { // Foreign key constraint error
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this package because it is being used by one or more products']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting package: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in deletePackage: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting package: ' . $e->getMessage()]);
    }
}
