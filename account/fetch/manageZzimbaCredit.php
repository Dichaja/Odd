<?php
// account/fetch/manageZzimbaCredit.php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user']['logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

$action = $_POST['action'] ?? '';

if ($action === 'getWallet') {
    $userId = $_SESSION['user']['user_id'];
    $result = CreditService::getWallet('USER', $userId);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
