<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 0);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
    }
    $_SESSION['last_activity'] = time();
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Ulid\Ulid;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (!defined('BASE_URL')) {
    $base = $_ENV['BASE_URL'] ?? '';
    $base = $base === '' ? '/' : rtrim($base, '/') . '/';
    define('BASE_URL', $base);
}

$db_host = $_ENV['DB_HOST'] ?? '';
$db_name = $_ENV['DB_NAME'] ?? '';
$db_user = $_ENV['DB_USER'] ?? '';
$db_pass = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $pdo->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die('Database connection failed');
}

$activeProvider = '';
$collecto = ['username' => '', 'api_key' => '', 'base_url' => ''];
$speed = ['api_id' => '', 'api_password' => '', 'sender_id' => '', 'api_url' => ''];

try {
    $stmt = $pdo->query("SELECT name, credentials_json FROM zzimba_sms_providers WHERE status='active' LIMIT 1");
    $row = $stmt->fetch();
    if ($row && !empty($row['name'])) {
        $activeProvider = strtolower(trim((string) $row['name']));
        $creds = [];
        if (!empty($row['credentials_json'])) {
            $decoded = json_decode($row['credentials_json'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $creds = array_map(static function ($v) {
                    return is_string($v) ? trim($v) : $v;
                }, $decoded);
            }
        }
        if ($activeProvider === 'collecto') {
            $collecto['username'] = (string) ($creds['username'] ?? '');
            $collecto['api_key'] = (string) ($creds['api_key'] ?? '');
            $collecto['base_url'] = rtrim((string) ($creds['base_url'] ?? ''), '/');
        } elseif ($activeProvider === 'speedamobile') {
            $speed['api_id'] = (string) ($creds['api_id'] ?? '');
            $speed['api_password'] = (string) ($creds['api_password'] ?? '');
            $speed['sender_id'] = (string) ($creds['sender_id'] ?? '');
            $speed['api_url'] = (string) ($creds['api_url'] ?? '');
        }
    }
} catch (Throwable $e) {
    error_log("Config load settings error: " . $e->getMessage());
}

define('SMS_PROVIDER', $activeProvider ?: '');
define('CISSY_USERNAME', $collecto['username']);
define('CISSY_API_KEY', $collecto['api_key']);
define('CISSY_COLLECTO_BASE_URL', $collecto['base_url']);

define('SPEEDMOBILE_API_ID', $speed['api_id']);
define('SPEEDMOBILE_API_PASSWORD', $speed['api_password']);
define('SPEEDMOBILE_SENDER_ID', $speed['sender_id']);
define('SPEEDMOBILE_API_URL', $speed['api_url']);

$missing = [];
if (SMS_PROVIDER === '') {
    $missing[] = 'active_provider';
} elseif (SMS_PROVIDER === 'collecto') {
    if (trim(CISSY_USERNAME) === '')
        $missing[] = 'collecto.username';
    if (trim(CISSY_API_KEY) === '')
        $missing[] = 'collecto.api_key';
    if (trim(CISSY_COLLECTO_BASE_URL) === '')
        $missing[] = 'collecto.base_url';
} elseif (SMS_PROVIDER === 'speedamobile') {
    if (trim(SPEEDMOBILE_API_ID) === '')
        $missing[] = 'speedamobile.api_id';
    if (trim(SPEEDMOBILE_API_PASSWORD) === '')
        $missing[] = 'speedamobile.api_password';
    if (trim(SPEEDMOBILE_SENDER_ID) === '')
        $missing[] = 'speedamobile.sender_id';
    if (trim(SPEEDMOBILE_API_URL) === '')
        $missing[] = 'speedamobile.api_url';
} else {
    $missing[] = 'active_provider_invalid';
}

global $SMS_CONFIG_STATE;
$SMS_CONFIG_STATE = [
    'ok' => empty($missing),
    'provider' => SMS_PROVIDER,
    'missing' => $missing
];

if (!empty($missing)) {
    error_log('SMS configuration incomplete: ' . implode(', ', $missing));
}

function getSmsConfigState(): array
{
    global $SMS_CONFIG_STATE;
    return $SMS_CONFIG_STATE;
}

function requireSmsConfigOrJsonFail(): void
{
    $state = getSmsConfigState();
    if (!$state['ok']) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'errorCode' => 'SMS_CONFIG_INCOMPLETE',
            'message' => 'Missing or invalid SMS configuration for active provider',
            'provider' => $state['provider'],
            'missing' => $state['missing']
        ]);
        exit;
    }
}

define('CONFIG_PATH', BASE_URL . 'config/');
define('ASSETS_PATH', BASE_URL . 'assets/');
define('LIBS_PATH', BASE_URL . 'libs/');
define('TEMPLATES_PATH', BASE_URL . 'templates/');
define('UPLOADS_PATH', BASE_URL . 'uploads/');

function generateUlid(): string
{
    return strtolower((string) Ulid::generate());
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Access forbidden');
}
