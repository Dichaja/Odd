<?php
$pageTitle = 'Manage Contact Us';
$activeNav = 'contact-us';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>