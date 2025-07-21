<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'System Settings';
$activeNav = 'settings';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>