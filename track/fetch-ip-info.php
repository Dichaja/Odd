<?php
declare(strict_types=1);

// Always return JSON
header('Content-Type: application/json');

// --- Configuration ---
$cacheFile = __DIR__ . '/ip_cache.json';
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

// --- 1) Serve from cache if fresh ---
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

// --- 2) Try each service in turn ---
$response = null;
$lastHttpCode = 0;
$errors = [];

foreach ($services as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, $curlOptions);

    $body = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    $httpCode = (int) ($info['http_code'] ?? 0);

    curl_close($ch);

    if ($body !== false && $httpCode >= 200 && $httpCode < 300) {
        // Got a good response
        $response = $body;
        $lastHttpCode = $httpCode;
        break;
    }

    $errors[] = [
        'url' => $url,
        'http_code' => $httpCode,
        'curl_error' => $err
    ];
}

// --- 3) Output result or error ---
if ($response !== null) {
    // Cache successful response
    @file_put_contents($cacheFile, $response);
    http_response_code(200);
    echo $response;
    exit;
}

// If we reach here, all services failed
http_response_code(502); // Bad Gateway
echo json_encode([
    'error' => 'Unable to fetch IP information from any service.',
    'details' => $errors
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
