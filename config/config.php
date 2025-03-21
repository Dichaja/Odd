<?php
// Function to dynamically detect the base URL
function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path = substr($script_name, 0, strrpos($script_name, '/'));
    $base_path = str_replace('/login', '', str_replace('/admin', '', str_replace('/account', '', $path)));

    // If we're in a subdirectory, make sure to include it
    if ($base_path !== '') {
        return $protocol . $host . $base_path . '/';
    } else {
        return $protocol . $host . '/';
    }
}

// Define base URL - dynamically detect in production, use hardcoded for localhost
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
    define('BASE_URL', 'http://localhost/newzzimba/');
} else {
    define('BASE_URL', getBaseUrl());
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db_host = $_ENV['DB_HOST'];
$db_name = $_ENV['DB_NAME'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASS'];

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    $pdo->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die(json_encode(['error' => 'Database connection failed']));
}

define('CONFIG_PATH', BASE_URL . 'config/');
define('ASSETS_PATH', BASE_URL . 'assets/');
define('LIBS_PATH', BASE_URL . 'libs/');
define('TEMPLATES_PATH', BASE_URL . 'templates/');
define('UPLOADS_PATH', BASE_URL . 'uploads/');

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    http_response_code(403);
    exit('Access forbidden');
}
