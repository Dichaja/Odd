<?php
// This is a debug file to check routing and redirect issues
// Remember to remove this file in production

$baseUrl = isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : 'Not set in server';
$config = file_exists(__DIR__ . '/../config/config.php') ? 'Config file exists' : 'Config file missing';

header('Content-Type: application/json');
echo json_encode([
    'server' => $_SERVER,
    'baseUrl' => $baseUrl,
    'config' => $config,
    'requestUri' => $_SERVER['REQUEST_URI'] ?? 'Not set',
    'documentRoot' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set',
    'phpSelf' => $_SERVER['PHP_SELF'] ?? 'Not set'
]);
