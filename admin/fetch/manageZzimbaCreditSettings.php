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
        "CREATE TABLE IF NOT EXISTS zzimba_credit_settings (
            id CHAR(26) NOT NULL PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL,
            setting_name VARCHAR(200) NOT NULL,
            setting_value DECIMAL(10,2) NOT NULL,
            setting_type ENUM('flat','percentage') NOT NULL,
            category ENUM('sms','bonus','access','commission','transfer','withdrawal','subscription','quote','buy_in_store') NOT NULL,
            description TEXT,
            applicable_to ENUM('users','vendors','all') NOT NULL,
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY unique_key_applicable (setting_key, applicable_to)
        ) ENGINE=InnoDB"
    );
} catch (PDOException $e) {
    error_log("Table creation error (credit_settings): " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getSettings':
            getSettings($pdo);
            break;
        case 'getSetting':
            getSetting($pdo);
            break;
        case 'createSetting':
            createSetting($pdo);
            break;
        case 'updateSetting':
            updateSetting($pdo);
            break;
        case 'updateSettingStatus':
            updateSettingStatus($pdo);
            break;
        case 'deleteSetting':
            deleteSetting($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Error in manageZzimbaCreditSettings: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function generateSettingKey($name)
{
    $key = strtolower($name);
    $key = preg_replace('/[^a-z0-9\s]/', '', $key);
    $key = preg_replace('/\s+/', '_', trim($key));
    return $key;
}

function validateCategoryCoverage(PDO $pdo, $category, $settingKey, $applicableTo, $excludeId = null)
{
    $sql = "SELECT setting_key, applicable_to FROM zzimba_credit_settings 
            WHERE category = :category AND status = 'active'";
    $params = [':category' => $category];

    if ($excludeId) {
        $sql .= " AND id != :excludeId";
        $params[':excludeId'] = $excludeId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $existingSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $settingsByKey = [];
    foreach ($existingSettings as $setting) {
        $key = $setting['setting_key'];
        if (!isset($settingsByKey[$key])) {
            $settingsByKey[$key] = [];
        }
        $settingsByKey[$key][] = $setting['applicable_to'];
    }

    if (!isset($settingsByKey[$settingKey])) {
        $settingsByKey[$settingKey] = [];
    }
    $settingsByKey[$settingKey][] = $applicableTo;

    foreach ($settingsByKey as $key => $applicableToList) {
        $hasAll = in_array('all', $applicableToList);
        $hasUsers = in_array('users', $applicableToList);
        $hasVendors = in_array('vendors', $applicableToList);

        if (!$hasAll && (!$hasUsers || !$hasVendors)) {
            return [
                'valid' => false,
                'message' => "Setting key '{$key}' must either have 'All Users' setting or both 'Users' and 'Vendors' settings active"
            ];
        }
    }

    return ['valid' => true];
}

function getSettings(PDO $pdo)
{
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';

    $sql = "SELECT id, setting_key, setting_name, setting_value, setting_type, category, description, applicable_to, status, created_at, updated_at FROM zzimba_credit_settings";
    $params = [];
    $conditions = [];

    if ($category) {
        $conditions[] = "category = :category";
        $params[':category'] = $category;
    }

    if ($search) {
        $conditions[] = "(setting_name LIKE :search OR description LIKE :search OR setting_key LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($conditions) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY category, setting_name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'settings' => $settings]);
}

function getSetting(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing setting ID']);
        return;
    }

    $id = $_GET['id'];
    $stmt = $pdo->prepare(
        "SELECT id, setting_key, setting_name, setting_value, setting_type, category, description, applicable_to, status, created_at, updated_at
         FROM zzimba_credit_settings
         WHERE id = :id"
    );
    $stmt->execute([':id' => $id]);
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$setting) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Setting not found']);
        return;
    }

    echo json_encode(['success' => true, 'setting' => $setting]);
}

function createSetting(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    $required = ['setting_name', 'setting_value', 'setting_type', 'category', 'applicable_to'];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }

    $settingName = trim($data['setting_name']);
    $settingKey = generateSettingKey($settingName);
    $settingValue = floatval($data['setting_value']);
    $settingType = $data['setting_type'];
    $category = $data['category'];
    $description = trim($data['description'] ?? '');
    $applicableTo = $data['applicable_to'];

    $allowedTypes = ['flat', 'percentage'];
    $allowedCategories = ['sms', 'bonus', 'access', 'commission', 'transfer', 'withdrawal', 'subscription', 'quote', 'buy_in_store'];
    $allowedApplicable = ['users', 'vendors', 'all'];

    if (!in_array($settingType, $allowedTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid setting type']);
        return;
    }

    if (!in_array($category, $allowedCategories, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        return;
    }

    if ($category === 'buy_in_store' && $settingType !== 'flat') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Buy In Store supports only flat amount type']);
        return;
    }

    if (!in_array($applicableTo, $allowedApplicable, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid applicable to value']);
        return;
    }

    $chk = $pdo->prepare("SELECT id FROM zzimba_credit_settings WHERE setting_key = :key AND applicable_to = :applicable");
    $chk->execute([':key' => $settingKey, ':applicable' => $applicableTo]);
    if ($chk->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Setting with this key and applicable group already exists']);
        return;
    }

    $coverageValidation = validateCategoryCoverage($pdo, $category, $settingKey, $applicableTo);
    if (!$coverageValidation['valid']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $coverageValidation['message']]);
        return;
    }

    $id = generateUlid();
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        if ($applicableTo === 'all') {
            $updateStmt = $pdo->prepare(
                "UPDATE zzimba_credit_settings 
                 SET status = 'inactive', updated_at = :updated 
                 WHERE setting_key = :key AND status = 'active'"
            );
            $updateStmt->execute([':key' => $settingKey, ':updated' => $now]);
        } else {
            $allSettingStmt = $pdo->prepare(
                "UPDATE zzimba_credit_settings 
                 SET status = 'inactive', updated_at = :updated 
                 WHERE setting_key = :key AND applicable_to = 'all' AND status = 'active'"
            );
            $allSettingStmt->execute([':key' => $settingKey, ':updated' => $now]);
        }

        $ins = $pdo->prepare(
            "INSERT INTO zzimba_credit_settings
                (id, setting_key, setting_name, setting_value, setting_type, category, description, applicable_to, status, created_at, updated_at)
             VALUES
                (:id, :key, :name, :value, :type, :category, :description, :applicable, 'active', :created, :updated)"
        );
        $ins->execute([
            ':id' => $id,
            ':key' => $settingKey,
            ':name' => $settingName,
            ':value' => $settingValue,
            ':type' => $settingType,
            ':category' => $category,
            ':description' => $description,
            ':applicable' => $applicableTo,
            ':created' => $now,
            ':updated' => $now
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Setting created successfully', 'id' => $id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating credit setting: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating setting']);
    }
}

function updateSetting(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing setting ID']);
        return;
    }

    $id = $data['id'];
    $settingValue = floatval($data['setting_value'] ?? 0);
    $settingType = $data['setting_type'] ?? '';
    $applicableTo = $data['applicable_to'] ?? '';
    $settingName = isset($data['setting_name']) ? trim($data['setting_name']) : null;
    $description = isset($data['description']) ? trim($data['description']) : null;

    if (!$settingValue || !$settingType || !$applicableTo) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Value, type, and applicable to are required']);
        return;
    }

    if ($settingName !== null && $settingName === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Setting name cannot be empty']);
        return;
    }

    $allowedTypes = ['flat', 'percentage'];
    $allowedApplicable = ['users', 'vendors', 'all'];

    if (!in_array($settingType, $allowedTypes, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid setting type']);
        return;
    }

    if (!in_array($applicableTo, $allowedApplicable, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid applicable to value']);
        return;
    }

    $chkExist = $pdo->prepare("SELECT category, setting_key, applicable_to, status FROM zzimba_credit_settings WHERE id = :id");
    $chkExist->execute([':id' => $id]);
    $existing = $chkExist->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Setting not found']);
        return;
    }

    if ($existing['category'] === 'buy_in_store' && $settingType !== 'flat') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Buy In Store supports only flat amount type']);
        return;
    }

    if ($existing['applicable_to'] !== $applicableTo) {
        $chkDuplicate = $pdo->prepare("SELECT id FROM zzimba_credit_settings WHERE setting_key = :key AND applicable_to = :applicable AND id != :id");
        $chkDuplicate->execute([':key' => $existing['setting_key'], ':applicable' => $applicableTo, ':id' => $id]);
        if ($chkDuplicate->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Setting with this key and applicable group already exists']);
            return;
        }

        $coverageValidation = validateCategoryCoverage($pdo, $existing['category'], $existing['setting_key'], $applicableTo, $id);
        if (!$coverageValidation['valid']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $coverageValidation['message']]);
            return;
        }
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();

    try {
        if ($existing['status'] === 'active' && $existing['applicable_to'] !== $applicableTo) {
            if ($applicableTo === 'all') {
                $updateStmt = $pdo->prepare(
                    "UPDATE zzimba_credit_settings 
                     SET status = 'inactive', updated_at = :updated 
                     WHERE setting_key = :key AND id != :id AND status = 'active'"
                );
                $updateStmt->execute([':key' => $existing['setting_key'], ':id' => $id, ':updated' => $now]);
            } else {
                $allSettingStmt = $pdo->prepare(
                    "UPDATE zzimba_credit_settings 
                     SET status = 'inactive', updated_at = :updated 
                     WHERE setting_key = :key AND applicable_to = 'all' AND status = 'active'"
                );
                $allSettingStmt->execute([':key' => $existing['setting_key'], ':updated' => $now]);
            }
        }

        $fields = "
            setting_value = :value,
            setting_type = :type,
            applicable_to = :applicable,
            updated_at = :updated
        ";
        if ($settingName !== null) {
            $fields .= ", setting_name = :name";
        }
        if ($description !== null) {
            $fields .= ", description = :description";
        }

        $sql = "UPDATE zzimba_credit_settings SET {$fields} WHERE id = :id";
        $upd = $pdo->prepare($sql);

        $params = [
            ':value' => $settingValue,
            ':type' => $settingType,
            ':applicable' => $applicableTo,
            ':updated' => $now,
            ':id' => $id
        ];
        if ($settingName !== null) {
            $params[':name'] = $settingName;
        }
        if ($description !== null) {
            $params[':description'] = $description;
        }

        $upd->execute($params);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Setting updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating credit setting: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating setting']);
    }
}

function updateSettingStatus(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing setting ID or status']);
        return;
    }

    $id = $data['id'];
    $status = $data['status'] === 'active' ? 'active' : 'inactive';

    $chk = $pdo->prepare("SELECT category, setting_key, applicable_to FROM zzimba_credit_settings WHERE id = :id");
    $chk->execute([':id' => $id]);
    $setting = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$setting) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Setting not found']);
        return;
    }

    if ($status === 'active') {
        $coverageValidation = validateCategoryCoverage($pdo, $setting['category'], $setting['setting_key'], $setting['applicable_to'], $id);
        if (!$coverageValidation['valid']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $coverageValidation['message']]);
            return;
        }
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();

    try {
        if ($status === 'active') {
            if ($setting['applicable_to'] === 'all') {
                $updateStmt = $pdo->prepare(
                    "UPDATE zzimba_credit_settings 
                     SET status = 'inactive', updated_at = :updated 
                     WHERE setting_key = :key AND id != :id AND status = 'active'"
                );
                $updateStmt->execute([':key' => $setting['setting_key'], ':id' => $id, ':updated' => $now]);
            } else {
                $allSettingStmt = $pdo->prepare(
                    "UPDATE zzimba_credit_settings 
                     SET status = 'inactive', updated_at = :updated 
                     WHERE setting_key = :key AND applicable_to = 'all' AND status = 'active'"
                );
                $allSettingStmt->execute([':key' => $setting['setting_key'], ':updated' => $now]);
            }
        }

        $upd = $pdo->prepare(
            "UPDATE zzimba_credit_settings
                SET status = :status,
                    updated_at = :updated
              WHERE id = :id"
        );
        $upd->execute([
            ':status' => $status,
            ':updated' => $now,
            ':id' => $id
        ]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating setting status: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating status']);
    }
}

function deleteSetting(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing setting ID']);
        return;
    }

    $id = $data['id'];

    $chk = $pdo->prepare("SELECT id FROM zzimba_credit_settings WHERE id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Setting not found']);
        return;
    }

    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM zzimba_credit_settings WHERE id = :id");
        $del->execute([':id' => $id]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Setting deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting credit setting: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting setting']);
    }
}
