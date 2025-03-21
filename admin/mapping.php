<?php
$pageTitle = 'Mapping';
$activeNav = 'mapping';
ob_start();
?>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>