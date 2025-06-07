<?php
header("Content-Type: application/json");
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . "/session_log.json";
$expirySeconds = 30 * 60; // 30 minutes

$sessions = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

function cleanExpiredSessions(array $sessions, int $expirySeconds): array
{
    $now = time();
    foreach ($sessions as $key => $session) {
        $lastUpdate = isset($session['timestamp']) ? ($session['timestamp'] / 1000) : $now;
        if (($now - $lastUpdate) > $expirySeconds) {
            unset($sessions[$key]);
        }
    }
    return array_values($sessions);
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["sessionID"]) || !is_string($data["sessionID"]) || trim($data["sessionID"]) === '') {
        echo json_encode(["status" => "error", "message" => "Invalid session data."]);
        exit;
    }

    $found = false;
    foreach ($sessions as &$session) {
        if ($session["sessionID"] === $data["sessionID"]) {
            $session = $data;
            $found = true;
            break;
        }
    }
    unset($session);

    if (!$found) {
        $sessions[] = $data;
    }

    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);
    echo json_encode(["status" => "success", "message" => "Session logged."]);
    exit;
}

if ($method === "GET") {
    $sessions = cleanExpiredSessions($sessions, $expirySeconds);

    usort($sessions, fn($a, $b) => $b["timestamp"] - $a["timestamp"]);

    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);
    echo json_encode($sessions);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request method."]);
exit;
