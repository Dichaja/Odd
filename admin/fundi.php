<?php
$pageTitle = 'Fundi';
$activeNav = 'fundi';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>