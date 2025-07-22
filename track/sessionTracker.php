<?php
header("Content-Type: application/json");
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . "/session_log.json";
$expirySeconds = 30 * 60; // 30 minutes

// 1) Load existing sessions from disk
$sessions = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

// 2) Helper to drop any sessions older than $expirySeconds
function cleanExpiredSessions(array $sessions, int $expirySeconds): array
{
    $now = new DateTime("now", new DateTimeZone("Africa/Kampala"));
    foreach ($sessions as $key => $session) {
        if (empty($session['timestamp'])) {
            unset($sessions[$key]);
            continue;
        }
        try {
            $then = new DateTime($session['timestamp']);
        } catch (Exception $e) {
            unset($sessions[$key]);
            continue;
        }
        $interval = $now->getTimestamp() - $then->getTimestamp();
        if ($interval > $expirySeconds) {
            unset($sessions[$key]);
        }
    }
    return array_values($sessions);
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    // 3) Accept JS payload, upsert into $sessions, write file
    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !$data
        || !isset($data["sessionID"])
        || !is_string($data["sessionID"])
        || trim($data["sessionID"]) === ''
    ) {
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
    echo json_encode(["status" => "success", "message" => "Logged"]);
    exit;
}

if ($method === "GET") {
    // 4) Clean out expired sessions
    $sessions = cleanExpiredSessions($sessions, $expirySeconds);

    // 5) Sort newest-first
    usort($sessions, function ($a, $b) {
        $tA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $tB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $tB - $tA;
    });

    // 6) Persist cleaned list
    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);

    // 7) Check if caller's sessionID still exists
    $expired = false;
    $localID = $_GET['sessionID'] ?? null;
    if ($localID !== null) {
        $found = false;
        foreach ($sessions as $s) {
            if (isset($s['sessionID']) && $s['sessionID'] === $localID) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $expired = true;
        }
    }

    // 8) Return both the cleaned sessions and an expired flag
    echo json_encode([
        "expired" => $expired,
        "sessions" => $sessions
    ]);
    exit;
}

// 9) Other methods not allowed
echo json_encode(["status" => "error", "message" => "Invalid request method."]);
exit;
