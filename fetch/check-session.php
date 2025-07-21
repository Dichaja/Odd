<?php
require_once __DIR__ . '/../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Check if session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user session exists and is valid
    $isLoggedIn = isset($_SESSION['user']) && 
                  is_array($_SESSION['user']) && 
                  isset($_SESSION['user']['logged_in']) && 
                  $_SESSION['user']['logged_in'] === true;

    $response = [
        'success' => true,
        'logged_in' => $isLoggedIn,
        'timestamp' => time()
    ];

    // If logged in, include basic user info (optional)
    if ($isLoggedIn) {
        $response['user'] = [
            'user_id' => $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null,
            'is_admin' => $_SESSION['user']['is_admin'] ?? false,
            'username' => $_SESSION['user']['username'] ?? null,
            'email' => $_SESSION['user']['email'] ?? null
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'error' => 'Session check failed',
        'timestamp' => time()
    ]);
}
?>