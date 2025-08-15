<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS zzimba_sms_providers (
        id CHAR(26) NOT NULL PRIMARY KEY,
        name ENUM('collecto','speedamobile') NOT NULL UNIQUE,
        credentials_json TEXT NULL,
        status ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS zzimba_email_settings (
        id CHAR(26) NOT NULL PRIMARY KEY,
        host VARCHAR(200) NOT NULL,
        port INT NOT NULL,
        username VARCHAR(200) NOT NULL,
        from_name VARCHAR(200) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB");
} catch (PDOException $e) {
    error_log("Settings table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getSmsSettings':
            getSmsSettings($pdo);
            break;
        case 'saveCollecto':
            saveCollecto($pdo);
            break;
        case 'saveSpeedamobile':
            saveSpeedamobile($pdo);
            break;
        case 'setActiveProvider':
            setActiveProvider($pdo);
            break;
        case 'getEmailSettings':
            getEmailSettings($pdo);
            break;
        case 'saveEmailSettings':
            saveEmailSettings($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    error_log("manageSettings error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function maskCollecto(array $creds): array
{
    $u = $creds['username'] ?? '';
    $usernameMask = $u === '' ? '' : substr($u, 0, 1) . str_repeat('*', max(3, strlen($u) - 1));
    return [
        'username' => $usernameMask,
        'api_key' => str_repeat('*', 10),
        'base_url' => $creds['base_url'] ?? ''
    ];
}

function maskSpeedamobile(array $creds): array
{
    $id = $creds['api_id'] ?? '';
    $apiIdMask = $id === '' ? '' : substr($id, 0, 2) . str_repeat('*', max(4, strlen($id) - 2));
    return [
        'api_id' => $apiIdMask,
        'api_password' => str_repeat('*', 10),
        'sender_id' => $creds['sender_id'] ?? '',
        'api_url' => $creds['api_url'] ?? ''
    ];
}

function getSmsSettings(PDO $pdo)
{
    $stmt = $pdo->query("SELECT name,status,credentials_json,updated_at FROM zzimba_sms_providers");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $active = null;
    $mask = [
        'collecto' => ['username' => '', 'api_key' => '**********', 'base_url' => ''],
        'speedamobile' => ['api_id' => '', 'api_password' => '**********', 'sender_id' => '', 'api_url' => '']
    ];
    $providers = [
        'collecto' => ['configured' => false],
        'speedamobile' => ['configured' => false]
    ];
    $lastUpdated = null;
    foreach ($rows as $r) {
        if ($r['status'] === 'active')
            $active = $r['name'];
        $creds = json_decode($r['credentials_json'] ?: '{}', true);
        if ($r['name'] === 'collecto') {
            $mask['collecto'] = maskCollecto($creds);
            $providers['collecto']['configured'] = !empty(array_filter($creds, fn($v) => $v !== '' && $v !== null));
        }
        if ($r['name'] === 'speedamobile') {
            $mask['speedamobile'] = maskSpeedamobile($creds);
            $providers['speedamobile']['configured'] = !empty(array_filter($creds, fn($v) => $v !== '' && $v !== null));
        }
        if ($r['updated_at'] && (!$lastUpdated || $r['updated_at'] > $lastUpdated))
            $lastUpdated = $r['updated_at'];
    }
    $configuredCount = (int) $providers['collecto']['configured'] + (int) $providers['speedamobile']['configured'];
    echo json_encode([
        'success' => true,
        'active_provider' => $active,
        'mask' => $mask,
        'providers' => $providers,
        'meta' => [
            'configured_count' => $configuredCount,
            'last_updated' => $lastUpdated ?: null
        ]
    ]);
}

function saveCollecto(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    if (($data['username'] ?? '') === '' && ($data['api_key'] ?? '') === '' && ($data['base_url'] ?? '') === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Provide at least one field to update']);
        return;
    }
    $row = $pdo->query("SELECT id,credentials_json,status FROM zzimba_sms_providers WHERE name='collecto' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    if (!$row) {
        $new = [
            'username' => $data['username'] ?? '',
            'api_key' => $data['api_key'] ?? '',
            'base_url' => $data['base_url'] ?? ''
        ];
        $pdo->prepare("INSERT INTO zzimba_sms_providers (id,name,credentials_json,status,created_at,updated_at) VALUES (:id,'collecto',:cjson,'inactive',:c,:u)")
            ->execute([':id' => generateUlid(), ':cjson' => json_encode($new), ':c' => $now, ':u' => $now]);
    } else {
        $current = json_decode($row['credentials_json'] ?: '{}', true);
        $new = [
            'username' => ($data['username'] ?? null) !== null && $data['username'] !== '' ? $data['username'] : ($current['username'] ?? ''),
            'api_key' => ($data['api_key'] ?? null) !== null && $data['api_key'] !== '' ? $data['api_key'] : ($current['api_key'] ?? ''),
            'base_url' => ($data['base_url'] ?? null) !== null && $data['base_url'] !== '' ? $data['base_url'] : ($current['base_url'] ?? '')
        ];
        $pdo->prepare("UPDATE zzimba_sms_providers SET credentials_json=:c, updated_at=:u WHERE id=:id")
            ->execute([':c' => json_encode($new), ':u' => $now, ':id' => $row['id']]);
    }

    $activeChk = $pdo->query("SELECT COUNT(*) FROM zzimba_sms_providers WHERE status='active'")->fetchColumn();
    if ((int) $activeChk === 0) {
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='inactive'")->execute();
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='active', updated_at=:u WHERE name='collecto'")->execute([':u' => $now]);
    }
    echo json_encode(['success' => true, 'message' => 'Collecto saved']);
}

function saveSpeedamobile(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    if (($data['api_id'] ?? '') === '' && ($data['api_password'] ?? '') === '' && ($data['sender_id'] ?? '') === '' && ($data['api_url'] ?? '') === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Provide at least one field to update']);
        return;
    }
    $row = $pdo->query("SELECT id,credentials_json,status FROM zzimba_sms_providers WHERE name='speedamobile' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    if (!$row) {
        $new = [
            'api_id' => $data['api_id'] ?? '',
            'api_password' => $data['api_password'] ?? '',
            'sender_id' => $data['sender_id'] ?? '',
            'api_url' => $data['api_url'] ?? ''
        ];
        $pdo->prepare("INSERT INTO zzimba_sms_providers (id,name,credentials_json,status,created_at,updated_at) VALUES (:id,'speedamobile',:cjson,'inactive',:c,:u)")
            ->execute([':id' => generateUlid(), ':cjson' => json_encode($new), ':c' => $now, ':u' => $now]);
    } else {
        $current = json_decode($row['credentials_json'] ?: '{}', true);
        $new = [
            'api_id' => ($data['api_id'] ?? null) !== null && $data['api_id'] !== '' ? $data['api_id'] : ($current['api_id'] ?? ''),
            'api_password' => ($data['api_password'] ?? null) !== null && $data['api_password'] !== '' ? $data['api_password'] : ($current['api_password'] ?? ''),
            'sender_id' => ($data['sender_id'] ?? null) !== null && $data['sender_id'] !== '' ? $data['sender_id'] : ($current['sender_id'] ?? ''),
            'api_url' => ($data['api_url'] ?? null) !== null && $data['api_url'] !== '' ? $data['api_url'] : ($current['api_url'] ?? '')
        ];
        $pdo->prepare("UPDATE zzimba_sms_providers SET credentials_json=:c, updated_at=:u WHERE id=:id")
            ->execute([':c' => json_encode($new), ':u' => $now, ':id' => $row['id']]);
    }

    $activeChk = $pdo->query("SELECT COUNT(*) FROM zzimba_sms_providers WHERE status='active'")->fetchColumn();
    if ((int) $activeChk === 0) {
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='inactive'")->execute();
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='active', updated_at=:u WHERE name='speedamobile'")->execute([':u' => $now]);
    }
    echo json_encode(['success' => true, 'message' => 'Speedamobile saved']);
}

function setActiveProvider(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $provider = $data['provider'] ?? '';
    if (!in_array($provider, ['collecto', 'speedamobile'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid provider']);
        return;
    }
    $exists = $pdo->prepare("SELECT id FROM zzimba_sms_providers WHERE name=:n LIMIT 1");
    $exists->execute([':n' => $provider]);
    if ($exists->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Provider not configured yet']);
        return;
    }
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='inactive', updated_at=:u")->execute([':u' => $now]);
        $pdo->prepare("UPDATE zzimba_sms_providers SET status='active', updated_at=:u WHERE name=:n")->execute([':u' => $now, ':n' => $provider]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Active provider updated']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("setActiveProvider error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update active provider']);
    }
}

function getEmailSettings(PDO $pdo)
{
    $row = $pdo->query("SELECT host,port,username,from_name FROM zzimba_email_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => true, 'settings' => ['host' => '', 'port' => 0, 'username' => '', 'from_name' => '']]);
        return;
    }
    echo json_encode(['success' => true, 'settings' => $row]);
}

function saveEmailSettings(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $row = $pdo->query("SELECT id FROM zzimba_email_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    if (!$row) {
        $pdo->prepare("INSERT INTO zzimba_email_settings (id,host,port,username,from_name,created_at,updated_at) VALUES (:id,:h,:p,:u,:f,:c,:u2)")
            ->execute([
                ':id' => generateUlid(),
                ':h' => $data['host'] ?? '',
                ':p' => isset($data['port']) ? (int) $data['port'] : 0,
                ':u' => $data['username'] ?? '',
                ':f' => $data['from_name'] ?? '',
                ':c' => $now,
                ':u2' => $now
            ]);
    } else {
        $pdo->prepare("UPDATE zzimba_email_settings SET host=:h, port=:p, username=:u, from_name=:f, updated_at=:up WHERE id=:id")->execute([
            ':h' => $data['host'] ?? '',
            ':p' => isset($data['port']) ? (int) $data['port'] : 0,
            ':u' => $data['username'] ?? '',
            ':f' => $data['from_name'] ?? '',
            ':up' => $now,
            ':id' => $row['id']
        ]);
    }
    echo json_encode(['success' => true, 'message' => 'Email settings saved']);
}
