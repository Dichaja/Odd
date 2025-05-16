<?php
$pageTitle = 'Manage About Us Page';
$activeNav = 'about-us';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>