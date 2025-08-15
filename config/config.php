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

define('BASE_URL', 'http://localhost/newzzimba/');

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Ulid\Ulid;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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
    die(json_encode(['error' => 'Database connection failed']));
}

$activeProvider = '';
$collecto = ['username' => '', 'api_key' => '', 'base_url' => ''];
$speed = ['api_id' => '', 'api_password' => '', 'sender_id' => '', 'api_url' => ''];

try {
    $stmt = $pdo->query("SELECT provider FROM sms_providers WHERE is_active = 1 LIMIT 1");
    $row = $stmt->fetch();
    if ($row && !empty($row['provider']))
        $activeProvider = $row['provider'];

    $stmtC = $pdo->prepare("SELECT username, api_key, base_url FROM sms_providers WHERE provider = 'collecto' ORDER BY updated_at DESC LIMIT 1");
    $stmtC->execute();
    $rC = $stmtC->fetch();
    if ($rC) {
        $collecto['username'] = $rC['username'] ?? '';
        $collecto['api_key'] = $rC['api_key'] ?? '';
        $collecto['base_url'] = $rC['base_url'] ?? '';
    }

    $stmtS = $pdo->prepare("SELECT api_id, api_password, sender_id, api_url FROM sms_providers WHERE provider = 'speedamobile' ORDER BY updated_at DESC LIMIT 1");
    $stmtS->execute();
    $rS = $stmtS->fetch();
    if ($rS) {
        $speed['api_id'] = $rS['api_id'] ?? '';
        $speed['api_password'] = $rS['api_password'] ?? '';
        $speed['sender_id'] = $rS['sender_id'] ?? '';
        $speed['api_url'] = $rS['api_url'] ?? '';
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
