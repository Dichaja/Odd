<?php
ob_start();

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

date_default_timezone_set('Africa/Kampala');

try {
    // Create product_package_name table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_package_name (
        id BINARY(16) PRIMARY KEY,
        package_name VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY package_name_unique (package_name)
    )");

    // Create product_si_units table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_si_units (
        id BINARY(16) PRIMARY KEY,
        si_unit VARCHAR(50) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY si_unit_unique (si_unit)
    )");

    // Create product_unit_of_measure table with foreign keys to both tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_unit_of_measure (
        id BINARY(16) PRIMARY KEY,
        product_package_name_id BINARY(16) NOT NULL,
        product_si_unit_id BINARY(16) NOT NULL,
        status ENUM('Approved', 'Pending') NOT NULL DEFAULT 'Approved',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (product_package_name_id) REFERENCES product_package_name(id) ON DELETE CASCADE,
        FOREIGN KEY (product_si_unit_id) REFERENCES product_si_units(id) ON DELETE CASCADE,
        UNIQUE KEY package_si_unit_unique (product_package_name_id, product_si_unit_id)
    )");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // Package Name endpoints
        case 'getPackageNames':
            getPackageNames($pdo);
            break;

        case 'getPackageName':
            getPackageName($pdo);
            break;

        case 'createPackageName':
            createPackageName($pdo);
            break;

        case 'updatePackageName':
            updatePackageName($pdo);
            break;

        case 'deletePackageName':
            deletePackageName($pdo);
            break;

        // SI Unit endpoints
        case 'getSIUnits':
            getSIUnits($pdo);
            break;

        case 'getSIUnit':
            getSIUnit($pdo);
            break;

        case 'createSIUnit':
            createSIUnit($pdo);
            break;

        case 'updateSIUnit':
            updateSIUnit($pdo);
            break;

        case 'deleteSIUnit':
            deleteSIUnit($pdo);
            break;

        // Combined Unit of Measure endpoints
        case 'getUnitsOfMeasure':
            getUnitsOfMeasure($pdo);
            break;

        case 'createUnitOfMeasure':
            createUnitOfMeasure($pdo);
            break;

        case 'updateUnitOfMeasure':
            updateUnitOfMeasure($pdo);
            break;

        case 'deleteUnitOfMeasure':
            deleteUnitOfMeasure($pdo);
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

// Package Name Functions
function getPackageNames($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT id, package_name, created_at, updated_at FROM product_package_name ORDER BY package_name");
        $stmt->execute();

        $packageNames = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($packageNames as &$packageName) {
            $packageName['uuid_id'] = binToUuid($packageName['id']);
            unset($packageName['id']);
        }

        $response = ['success' => true, 'packageNames' => $packageNames];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Error in getPackageNames: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving package names: ' . $e->getMessage()]);
    }
}

function getPackageName($pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing package name ID']);
        return;
    }

    try {
        $packageNameId = $_GET['id'];
        $binaryId = uuidToBin($packageNameId);

        $stmt = $pdo->prepare("SELECT id, package_name, created_at, updated_at FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $packageName = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$packageName) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        $packageName['uuid_id'] = binToUuid($packageName['id']);
        unset($packageName['id']);

        echo json_encode(['success' => true, 'data' => $packageName]);
    } catch (Exception $e) {
        error_log("Error in getPackageName: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving package name: ' . $e->getMessage()]);
    }
}

function createPackageName($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['package_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $packageName = trim($data['package_name']);

        if (empty($packageName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Package name cannot be empty']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE package_name = :package_name");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
            return;
        }

        $packageId = uuidToBin(generateUUIDv7());
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_package_name (id, package_name, created_at, updated_at) VALUES (:id, :package_name, :created_at, :updated_at)");
        $stmt->bindParam(':id', $packageId, PDO::PARAM_LOB);
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        $response = [
            'success' => true,
            'message' => 'Package name created successfully',
            'id' => binToUuid($packageId)
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Error in createPackageName: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating package name: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in createPackageName: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating package name: ' . $e->getMessage()]);
    }
}

function updatePackageName($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['package_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $packageId = $data['id'];
        $packageName = trim($data['package_name']);

        if (empty($packageName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Package name cannot be empty']);
            return;
        }

        $binaryId = uuidToBin($packageId);

        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE package_name = :package_name AND id != :id");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE product_package_name SET package_name = :package_name, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':package_name', $packageName);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Package name updated successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in updatePackageName: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating package name: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in updatePackageName: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating package name: ' . $e->getMessage()]);
    }
}

function deletePackageName($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing package name ID']);
            return;
        }

        $packageId = $data['id'];
        $binaryId = uuidToBin($packageId);

        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        // Check if there are any units of measure using this package name
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_unit_of_measure WHERE product_package_name_id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this package name because it is being used by one or more SI units']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Package name deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in deletePackageName: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this package name because it is being used by one or more SI units']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting package name: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in deletePackageName: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting package name: ' . $e->getMessage()]);
    }
}

// SI Unit Functions
function getSIUnits($pdo)
{
    try {
        // Get all SI units from product_si_units table
        $stmt = $pdo->prepare("SELECT id, si_unit, created_at, updated_at FROM product_si_units ORDER BY si_unit");
        $stmt->execute();
        $siUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($siUnits as &$unit) {
            $unit['uuid_id'] = binToUuid($unit['id']);
            unset($unit['id']);
        }

        $response = ['success' => true, 'siUnits' => $siUnits];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Error in getSIUnits: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving SI units: ' . $e->getMessage()]);
    }
}

function getSIUnit($pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing SI unit ID']);
        return;
    }

    try {
        $unitId = $_GET['id'];
        $binaryId = uuidToBin($unitId);

        $stmt = $pdo->prepare("SELECT id, si_unit, created_at, updated_at FROM product_si_units WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        $unit['uuid_id'] = binToUuid($unit['id']);
        unset($unit['id']);

        echo json_encode(['success' => true, 'data' => $unit]);
    } catch (Exception $e) {
        error_log("Error in getSIUnit: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving SI unit: ' . $e->getMessage()]);
    }
}

function createSIUnit($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['si_unit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $siUnit = trim($data['si_unit']);

        if (empty($siUnit)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE si_unit = :si_unit");
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $siUnitId = binToUuid($row['id']);

            echo json_encode([
                'success' => true,
                'message' => 'SI unit already exists',
                'id' => $siUnitId
            ]);
            return;
        }

        $siUnitId = uuidToBin(generateUUIDv7());
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_si_units (id, si_unit, created_at, updated_at) VALUES (:id, :si_unit, :created_at, :updated_at)");
        $stmt->bindParam(':id', $siUnitId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        $response = [
            'success' => true,
            'message' => 'SI unit created successfully',
            'id' => binToUuid($siUnitId)
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Error in createSIUnit: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'An SI unit with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating SI unit: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in createSIUnit: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating SI unit: ' . $e->getMessage()]);
    }
}

function updateSIUnit($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['si_unit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $siUnitId = $data['id'];
        $siUnit = trim($data['si_unit']);

        if (empty($siUnit)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
            return;
        }

        $binaryId = uuidToBin($siUnitId);

        $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE si_unit = :si_unit AND id != :id");
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'An SI unit with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE product_si_units SET si_unit = :si_unit, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'SI unit updated successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in updateSIUnit: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'An SI unit with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating SI unit: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in updateSIUnit: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating SI unit: ' . $e->getMessage()]);
    }
}

function deleteSIUnit($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing SI unit ID']);
            return;
        }

        $siUnitId = $data['id'];
        $binaryId = uuidToBin($siUnitId);

        $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        // Check if this SI unit is being used by any units of measure
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_unit_of_measure WHERE product_si_unit_id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this SI unit because it is being used by one or more units of measure']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM product_si_units WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'SI unit deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in deleteSIUnit: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this SI unit because it is being used by one or more units of measure']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting SI unit: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in deleteSIUnit: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting SI unit: ' . $e->getMessage()]);
    }
}

// Combined Unit of Measure Functions
function getUnitsOfMeasure($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT uom.id, si.id as si_unit_id, si.si_unit, p.id as package_name_id, p.package_name, 
                   CONCAT(si.si_unit, ' ', p.package_name) as unit_of_measure,
                   uom.status, uom.created_at, uom.updated_at
            FROM product_unit_of_measure uom
            JOIN product_package_name p ON uom.product_package_name_id = p.id
            JOIN product_si_units si ON uom.product_si_unit_id = si.id
            ORDER BY uom.created_at DESC
        ");
        $stmt->execute();
        $unitsOfMeasure = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($unitsOfMeasure as &$unit) {
            $unit['uuid_id'] = binToUuid($unit['id']);
            $unit['si_unit_uuid_id'] = binToUuid($unit['si_unit_id']);
            $unit['package_name_uuid_id'] = binToUuid($unit['package_name_id']);
            unset($unit['id']);
            unset($unit['si_unit_id']);
            unset($unit['package_name_id']);
        }

        $response = ['success' => true, 'unitsOfMeasure' => $unitsOfMeasure];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Error in getUnitsOfMeasure: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving units of measure: ' . $e->getMessage()]);
    }
}

function createUnitOfMeasure($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['package_name_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing package name ID']);
            return;
        }

        $packageNameId = $data['package_name_id'];
        $siUnitId = $data['si_unit_id'] ?? null;
        $siUnitName = isset($data['si_unit_name']) ? trim($data['si_unit_name']) : null;
        $status = isset($data['status']) ? $data['status'] : 'Approved';

        if (!in_array($status, ['Approved', 'Pending'])) {
            $status = 'Approved';
        }

        $binaryPackageNameId = uuidToBin($packageNameId);

        // Check if package name exists
        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        // Handle SI unit - either use existing or create new
        $binarySiUnitId = null;

        if ($siUnitId) {
            // Try to use existing SI unit by ID
            $binarySiUnitId = uuidToBin($siUnitId);

            $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE id = :id");
            $stmt->bindParam(':id', $binarySiUnitId, PDO::PARAM_LOB);
            $stmt->execute();

            if ($stmt->rowCount() === 0 && !$siUnitName) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'SI unit not found']);
                return;
            }
        }

        // If we have a name but no valid ID, try to find by name or create new
        if ($siUnitName) {
            $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE si_unit = :si_unit");
            $stmt->bindParam(':si_unit', $siUnitName);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Use existing SI unit with this name
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $binarySiUnitId = $row['id'];
            } else {
                // Create new SI unit
                $newSiUnitId = generateUUIDv7();
                $binarySiUnitId = uuidToBin($newSiUnitId);
                $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

                $stmt = $pdo->prepare("INSERT INTO product_si_units (id, si_unit, created_at, updated_at) VALUES (:id, :si_unit, :created_at, :updated_at)");
                $stmt->bindParam(':id', $binarySiUnitId, PDO::PARAM_LOB);
                $stmt->bindParam(':si_unit', $siUnitName);
                $stmt->bindParam(':created_at', $now);
                $stmt->bindParam(':updated_at', $now);
                $stmt->execute();
            }
        }

        if (!$binarySiUnitId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'SI unit not provided']);
            return;
        }

        // Check if combination already exists
        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE product_package_name_id = :package_name_id AND product_si_unit_id = :si_unit_id");
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit_id', $binarySiUnitId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This combination of package name and SI unit already exists']);
            return;
        }

        $uomId = uuidToBin(generateUUIDv7());
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_unit_of_measure (id, product_package_name_id, product_si_unit_id, status, created_at, updated_at) VALUES (:id, :package_name_id, :si_unit_id, :status, :created_at, :updated_at)");
        $stmt->bindParam(':id', $uomId, PDO::PARAM_LOB);
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit_id', $binarySiUnitId, PDO::PARAM_LOB);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        // Get the package name and SI unit for the response
        $stmt = $pdo->prepare("
            SELECT p.package_name, si.si_unit
            FROM product_package_name p, product_si_units si
            WHERE p.id = :package_name_id AND si.id = :si_unit_id
        ");
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit_id', $binarySiUnitId, PDO::PARAM_LOB);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'message' => 'Unit of measure created successfully',
            'id' => binToUuid($uomId),
            'unit_of_measure' => $result['si_unit'] . ' ' . $result['package_name'],
            'status' => $status
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Error in createUnitOfMeasure: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This combination of package name and SI unit already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating unit of measure: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in createUnitOfMeasure: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating unit of measure: ' . $e->getMessage()]);
    }
}

function updateUnitOfMeasure($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing unit of measure ID']);
            return;
        }

        $uomId = $data['id'];
        $binaryId = uuidToBin($uomId);

        // Check if unit of measure exists
        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Unit of measure not found']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $updateFields = [];
        $params = [':id' => $binaryId, ':updated_at' => $now];

        // Update status if provided
        if (isset($data['status'])) {
            $status = $data['status'];
            if (!in_array($status, ['Approved', 'Pending'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid status value']);
                return;
            }
            $updateFields[] = "status = :status";
            $params[':status'] = $status;
        }

        // Update package name if provided
        if (isset($data['package_name_id'])) {
            $packageNameId = $data['package_name_id'];
            $binaryPackageNameId = uuidToBin($packageNameId);

            // Check if package name exists
            $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE id = :id");
            $stmt->bindParam(':id', $binaryPackageNameId, PDO::PARAM_LOB);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Package name not found']);
                return;
            }

            $updateFields[] = "product_package_name_id = :package_name_id";
            $params[':package_name_id'] = $binaryPackageNameId;
        }

        // Update SI unit if provided
        if (isset($data['si_unit_id']) || isset($data['si_unit_name'])) {
            $binarySiUnitId = null;

            if (isset($data['si_unit_id'])) {
                $siUnitId = $data['si_unit_id'];
                $binarySiUnitId = uuidToBin($siUnitId);

                // Check if SI unit exists
                $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE id = :id");
                $stmt->bindParam(':id', $binarySiUnitId, PDO::PARAM_LOB);
                $stmt->execute();

                if ($stmt->rowCount() === 0 && !isset($data['si_unit_name'])) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'SI unit not found']);
                    return;
                }
            }

            // If we have a name, try to find by name or create new
            if (isset($data['si_unit_name'])) {
                $siUnitName = trim($data['si_unit_name']);

                if (!empty($siUnitName)) {
                    $stmt = $pdo->prepare("SELECT id FROM product_si_units WHERE si_unit = :si_unit");
                    $stmt->bindParam(':si_unit', $siUnitName);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        // Use existing SI unit with this name
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $binarySiUnitId = $row['id'];
                    } else {
                        // Create new SI unit
                        $newSiUnitId = generateUUIDv7();
                        $binarySiUnitId = uuidToBin($newSiUnitId);
                        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

                        $stmt = $pdo->prepare("INSERT INTO product_si_units (id, si_unit, created_at, updated_at) VALUES (:id, :si_unit, :created_at, :updated_at)");
                        $stmt->bindParam(':id', $binarySiUnitId, PDO::PARAM_LOB);
                        $stmt->bindParam(':si_unit', $siUnitName);
                        $stmt->bindParam(':created_at', $now);
                        $stmt->bindParam(':updated_at', $now);
                        $stmt->execute();
                    }
                }
            }

            if ($binarySiUnitId) {
                $updateFields[] = "product_si_unit_id = :si_unit_id";
                $params[':si_unit_id'] = $binarySiUnitId;
            }
        }

        // If there are fields to update
        if (!empty($updateFields)) {
            // Check for duplicate combination if both package name and SI unit are being updated
            if (isset($params[':package_name_id']) && isset($params[':si_unit_id'])) {
                $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE product_package_name_id = :package_name_id AND product_si_unit_id = :si_unit_id AND id != :id");
                $stmt->bindParam(':package_name_id', $params[':package_name_id'], PDO::PARAM_LOB);
                $stmt->bindParam(':si_unit_id', $params[':si_unit_id'], PDO::PARAM_LOB);
                $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'This combination of package name and SI unit already exists']);
                    return;
                }
            }

            $sql = "UPDATE product_unit_of_measure SET " . implode(", ", $updateFields) . ", updated_at = :updated_at WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            foreach ($params as $key => $value) {
                if (in_array($key, [':id', ':package_name_id', ':si_unit_id'])) {
                    $stmt->bindParam($key, $value, PDO::PARAM_LOB);
                } else {
                    $stmt->bindParam($key, $value);
                }
            }

            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Unit of measure updated successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
        }
    } catch (PDOException $e) {
        error_log("Error in updateUnitOfMeasure: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This combination of package name and SI unit already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating unit of measure: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in updateUnitOfMeasure: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating unit of measure: ' . $e->getMessage()]);
    }
}

function deleteUnitOfMeasure($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing unit of measure ID']);
            return;
        }

        $uomId = $data['id'];
        $binaryId = uuidToBin($uomId);

        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Unit of measure not found']);
            return;
        }

        // Check if this unit of measure is being used by any products
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_units WHERE unit_of_measure_id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this unit of measure because it is being used by one or more products']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM product_unit_of_measure WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Unit of measure deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in deleteUnitOfMeasure: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this unit of measure because it is being used by one or more products']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting unit of measure: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in deleteUnitOfMeasure: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting unit of measure: ' . $e->getMessage()]);
    }
}

// Helper function to convert binary UUID to string
function binToUuid($binary)
{
    $hex = bin2hex($binary);
    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

// Helper function to convert string UUID to binary
function uuidToBin($uuid)
{
    return hex2bin(str_replace('-', '', $uuid));
}

// Helper function to generate UUIDv7
function generateUUIDv7()
{
    $time = microtime(true) * 1000;
    $time = sprintf('%016x', (int)$time);

    $uuid = sprintf(
        '%s-%s-%s-%s-%s',
        substr($time, 0, 8),
        substr($time, 8, 4),
        '7' . substr($time, 13, 3),
        sprintf('%04x', mt_rand(0, 0x0fff) | 0x8000),
        sprintf('%012x', mt_rand(0, 0xffffffffffff))
    );

    return $uuid;
}
