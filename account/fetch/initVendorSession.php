<?php
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['store_uuid'])) {
    
    $_SESSION['active_store'] = $_POST['store_uuid'];

    echo json_encode([
        'success' => true,
        'redirect_url' => BASE_URL . 'vendor-store/dashboard'
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
exit;
