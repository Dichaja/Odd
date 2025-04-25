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
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS product_package_name (
            id VARCHAR(26) PRIMARY KEY,
            package_name VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY package_name_unique (package_name)
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS product_si_units (
            id VARCHAR(26) PRIMARY KEY,
            si_unit VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY si_unit_unique (si_unit)
        )"
    );
} catch (PDOException $e) {
    error_log('Table creation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
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

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageProductPackages: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getPackageNames(PDO $pdo)
{
    try {
        $stmt = $pdo->query(
            'SELECT id, package_name, created_at, updated_at
             FROM product_package_name
             ORDER BY package_name'
        );
        echo json_encode(['success' => true, 'packageNames' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        error_log('Error in getPackageNames: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving package names']);
    }
}

function getPackageName(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing package name ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, package_name, created_at, updated_at
             FROM product_package_name
             WHERE id = :id'
        );
        $stmt->execute([':id' => $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $row]);
    } catch (Exception $e) {
        error_log('Error in getPackageName: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving package name']);
    }
}

function createPackageName(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['package_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $packageName = trim($data['package_name']);

    if ($packageName === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Package name cannot be empty']);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM product_package_name WHERE package_name = :package_name'
        );
        $stmt->execute([':package_name' => $packageName]);
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
            return;
        }

        $id = generateUlid();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'INSERT INTO product_package_name (id, package_name, created_at, updated_at)
             VALUES (:id, :package_name, :created_at, :updated_at)'
        );
        $stmt->execute([
            ':id' => $id,
            ':package_name' => $packageName,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);

        echo json_encode(['success' => true, 'message' => 'Package name created', 'id' => $id]);
    } catch (Exception $e) {
        error_log('Error in createPackageName: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating package name']);
    }
}

function updatePackageName(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'], $data['package_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $id = $data['id'];
    $packageName = trim($data['package_name']);

    if ($packageName === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Package name cannot be empty']);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM product_package_name WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM product_package_name
             WHERE package_name = :package_name AND id != :id'
        );
        $stmt->execute([':package_name' => $packageName, ':id' => $id]);
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A package with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'UPDATE product_package_name
             SET package_name = :package_name, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([':package_name' => $packageName, ':updated_at' => $now, ':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Package name updated']);
    } catch (Exception $e) {
        error_log('Error in updatePackageName: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating package name']);
    }
}

function deletePackageName(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing package name ID']);
        return;
    }

    $id = $data['id'];

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM product_package_name WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Package name not found']);
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM product_package_name WHERE id = :id');
        $stmt->execute([':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'Package name deleted']);
    } catch (Exception $e) {
        error_log('Error in deletePackageName: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting package name']);
    }
}

function getSIUnits(PDO $pdo)
{
    try {
        $stmt = $pdo->query(
            'SELECT id, si_unit, created_at, updated_at
             FROM product_si_units
             ORDER BY si_unit'
        );
        echo json_encode(['success' => true, 'siUnits' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        error_log('Error in getSIUnits: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving SI units']);
    }
}

function getSIUnit(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing SI unit ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, si_unit, created_at, updated_at
             FROM product_si_units
             WHERE id = :id'
        );
        $stmt->execute([':id' => $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $row]);
    } catch (Exception $e) {
        error_log('Error in getSIUnit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving SI unit']);
    }
}

function createSIUnit(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['si_unit'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $siUnit = trim($data['si_unit']);

    if ($siUnit === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM product_si_units WHERE si_unit = :si_unit'
        );
        $stmt->execute([':si_unit' => $siUnit]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            echo json_encode(['success' => true, 'message' => 'SI unit already exists', 'id' => $row['id']]);
            return;
        }

        $id = generateUlid();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'INSERT INTO product_si_units (id, si_unit, created_at, updated_at)
             VALUES (:id, :si_unit, :created_at, :updated_at)'
        );
        $stmt->execute([
            ':id' => $id,
            ':si_unit' => $siUnit,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);

        echo json_encode(['success' => true, 'message' => 'SI unit created', 'id' => $id]);
    } catch (Exception $e) {
        error_log('Error in createSIUnit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating SI unit']);
    }
}

function updateSIUnit(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'], $data['si_unit'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $id = $data['id'];
    $siUnit = trim($data['si_unit']);

    if ($siUnit === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'SI unit cannot be empty']);
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT id FROM product_si_units WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM product_si_units
             WHERE si_unit = :si_unit AND id != :id'
        );
        $stmt->execute([':si_unit' => $siUnit, ':id' => $id]);
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'An SI unit with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare(
            'UPDATE product_si_units
             SET si_unit = :si_unit, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([':si_unit' => $siUnit, ':updated_at' => $now, ':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'SI unit updated']);
    } catch (Exception $e) {
        error_log('Error in updateSIUnit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating SI unit']);
    }
}

function deleteSIUnit(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing SI unit ID']);
        return;
    }

    $id = $data['id'];

    try {
        $stmt = $pdo->prepare(
            'SELECT id FROM product_si_units WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'SI unit not found']);
            return;
        }

        $stmt = $pdo->prepare('DELETE FROM product_si_units WHERE id = :id');
        $stmt->execute([':id' => $id]);

        echo json_encode(['success' => true, 'message' => 'SI unit deleted']);
    } catch (Exception $e) {
        error_log('Error in deleteSIUnit: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting SI unit']);
    }
}
