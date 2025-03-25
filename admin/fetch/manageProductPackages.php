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

    // Create product_unit_of_measure table with foreign key
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_unit_of_measure (
        id BINARY(16) PRIMARY KEY,
        product_package_name_id BINARY(16) NOT NULL,
        si_unit VARCHAR(50) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY package_unit_unique (product_package_name_id, si_unit),
        FOREIGN KEY (product_package_name_id) REFERENCES product_package_name(id) ON DELETE CASCADE
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

        // Check if there are any SI units using this package name
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
        $packageNameId = isset($_GET['package_name_id']) ? $_GET['package_name_id'] : null;

        if ($packageNameId) {
            $binaryId = uuidToBin($packageNameId);
            $stmt = $pdo->prepare("
                SELECT u.id, u.si_unit, u.created_at, u.updated_at, 
                       p.id as package_name_id, p.package_name
                FROM product_unit_of_measure u
                JOIN product_package_name p ON u.product_package_name_id = p.id
                WHERE u.product_package_name_id = :package_name_id
                ORDER BY u.si_unit
            ");
            $stmt->bindParam(':package_name_id', $binaryId, PDO::PARAM_LOB);
        } else {
            $stmt = $pdo->prepare("
                SELECT u.id, u.si_unit, u.created_at, u.updated_at, 
                       p.id as package_name_id, p.package_name
                FROM product_unit_of_measure u
                JOIN product_package_name p ON u.product_package_name_id = p.id
                ORDER BY p.package_name, u.si_unit
            ");
        }

        $stmt->execute();
        $siUnits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($siUnits as &$unit) {
            $unit['uuid_id'] = binToUuid($unit['id']);
            $unit['package_name_uuid_id'] = binToUuid($unit['package_name_id']);
            $unit['unit_of_measure'] = $unit['package_name'] . ' (' . $unit['si_unit'] . ')';
            unset($unit['id']);
            unset($unit['package_name_id']);
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

        $stmt = $pdo->prepare("
            SELECT u.id, u.si_unit, u.created_at, u.updated_at, 
                   p.id as package_name_id, p.package_name
            FROM product_unit_of_measure u
            JOIN product_package_name p ON u.product_package_name_id = p.id
            WHERE u.id = :id
        ");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $unit = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        $unit['uuid_id'] = binToUuid($unit['id']);
        $unit['package_name_uuid_id'] = binToUuid($unit['package_name_id']);
        $unit['unit_of_measure'] = $unit['package_name'] . ' (' . $unit['si_unit'] . ')';
        unset($unit['id']);
        unset($unit['package_name_id']);

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

        if (!isset($data['package_name_id']) || !isset($data['si_unit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $packageNameId = $data['package_name_id'];
        $siUnit = trim($data['si_unit']);

        if (empty($siUnit)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
            return;
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

        // Check if SI unit already exists for this package name
        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE product_package_name_id = :package_name_id AND si_unit = :si_unit");
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This SI unit already exists for the selected package name']);
            return;
        }

        $unitId = uuidToBin(generateUUIDv7());
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_unit_of_measure (id, product_package_name_id, si_unit, created_at, updated_at) VALUES (:id, :package_name_id, :si_unit, :created_at, :updated_at)");
        $stmt->bindParam(':id', $unitId, PDO::PARAM_LOB);
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        // Get the package name for the response
        $stmt = $pdo->prepare("SELECT package_name FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->execute();
        $packageName = $stmt->fetchColumn();

        $response = [
            'success' => true,
            'message' => 'SI unit created successfully',
            'id' => binToUuid($unitId),
            'unit_of_measure' => $packageName . ' (' . $siUnit . ')'
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("Error in createSIUnit: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This SI unit already exists for the selected package name']);
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

        if (!isset($data['id']) || !isset($data['package_name_id']) || !isset($data['si_unit'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            return;
        }

        $unitId = $data['id'];
        $packageNameId = $data['package_name_id'];
        $siUnit = trim($data['si_unit']);

        if (empty($siUnit)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
            return;
        }

        $binaryUnitId = uuidToBin($unitId);
        $binaryPackageNameId = uuidToBin($packageNameId);

        // Check if SI unit exists
        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE id = :id");
        $stmt->bindParam(':id', $binaryUnitId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        // Check if package name exists
        $stmt = $pdo->prepare("SELECT id FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        // Check if SI unit already exists for this package name (excluding current record)
        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE product_package_name_id = :package_name_id AND si_unit = :si_unit AND id != :id");
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':id', $binaryUnitId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This SI unit already exists for the selected package name']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE product_unit_of_measure SET product_package_name_id = :package_name_id, si_unit = :si_unit, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':package_name_id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->bindParam(':si_unit', $siUnit);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryUnitId, PDO::PARAM_LOB);
        $stmt->execute();

        // Get the package name for the response
        $stmt = $pdo->prepare("SELECT package_name FROM product_package_name WHERE id = :id");
        $stmt->bindParam(':id', $binaryPackageNameId, PDO::PARAM_LOB);
        $stmt->execute();
        $packageName = $stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'SI unit updated successfully',
            'unit_of_measure' => $packageName . ' (' . $siUnit . ')'
        ]);
    } catch (PDOException $e) {
        error_log("Error in updateSIUnit: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This SI unit already exists for the selected package name']);
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

        $unitId = $data['id'];
        $binaryId = uuidToBin($unitId);

        $stmt = $pdo->prepare("SELECT id FROM product_unit_of_measure WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        // Check if this SI unit is being used by any products
        // This would require checking against your products table
        // For now, we'll just delete the SI unit

        $stmt = $pdo->prepare("DELETE FROM product_unit_of_measure WHERE id = :id");
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
            echo json_encode(['success' => false, 'message' => 'Cannot delete this SI unit because it is being used by one or more products']);
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
            SELECT u.id, u.si_unit, p.package_name, 
                   CONCAT(p.package_name, ' (', u.si_unit, ')') as unit_of_measure
            FROM product_unit_of_measure u
            JOIN product_package_name p ON u.product_package_name_id = p.id
            ORDER BY p.package_name, u.si_unit
        ");
        $stmt->execute();
        $unitsOfMeasure = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($unitsOfMeasure as &$unit) {
            $unit['uuid_id'] = binToUuid($unit['id']);
            unset($unit['id']);
        }

        $response = ['success' => true, 'unitsOfMeasure' => $unitsOfMeasure];
        echo json_encode($response);
    } catch (Exception $e) {
        error_log("Error in getUnitsOfMeasure: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving units of measure: ' . $e->getMessage()]);
    }
}
