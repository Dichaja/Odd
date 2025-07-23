<?php
declare(strict_types=1);
session_start();

// Always return JSON
header('Content-Type: application/json');

// --- Configuration ---
$cacheTTL = 600; // seconds (10 minutes)
$services = [
    'https://ipapi.co/json/',
    'https://ipwho.is/',
    'https://api.db-ip.com/v2/free/self'
];
$curlOptions = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; ZzimbaTracker/1.0)',
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 10,
];

// --- 1) Serve from session cache if still fresh ---
if (
    isset($_SESSION['ip_info']['timestamp'], $_SESSION['ip_info']['data'])
    && (time() - $_SESSION['ip_info']['timestamp'] < $cacheTTL)
) {
    echo $_SESSION['ip_info']['data'];
    exit;
}

// --- 2) Attempt each service until one succeeds ---
$errors = [];
$response = null;

foreach ($services as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, $curlOptions);

    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    $httpCode = (int) ($info['http_code'] ?? 0);

    curl_close($ch);

    if ($body !== false && $httpCode >= 200 && $httpCode < 300) {
        $response = $body;
        break;
    }

    $errors[] = [
        'service' => $url,
        'http_code' => $httpCode,
        'curl_error' => $err
    ];
}

// --- 3) Cache & return success, or error out ---
if ($response !== null) {
    // store in session
    $_SESSION['ip_info'] = [
        'timestamp' => time(),
        'data' => $response
    ];

    http_response_code(200);
    echo $response;
    exit;
}

// All services failed
http_response_code(502); // Bad Gateway
echo json_encode([
    'error' => 'Unable to fetch IP information from any service.',
    'details' => $errors
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
