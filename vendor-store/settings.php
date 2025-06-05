<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Store Settings';
$activeNav = 'settings';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}

$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}

ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>