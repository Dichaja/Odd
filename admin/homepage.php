<?php
$pageTitle = 'Manage Index Page';
$activeNav = 'homepage';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>