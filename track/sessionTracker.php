<?php
// track/sessionTracker.php
header("Content-Type: application/json");
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . "/session_log.json";
$expirySeconds = 2 * 60;

// Read current sessions from file.
$sessions = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

// Helper: Remove expired sessions (based on header timestamp in ms).
function cleanExpiredSessions($sessions, $expirySeconds)
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
    if (!$data || !isset($data["sessionID"])) {
        echo json_encode(["status" => "error", "message" => "Invalid data."]);
        exit;
    }
    $found = false;
    // Update the session block if it exists; otherwise, append it.
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
    // Write updated sessions to file.
    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);
    echo json_encode(["status" => "success", "message" => "Session logged."]);
    exit;
}

if ($method === "GET") {
    $sessions = cleanExpiredSessions($sessions, $expirySeconds);
    // Sort sessions by header timestamp descending (most recent first).
    usort($sessions, function ($a, $b) {
        return ($b["timestamp"] - $a["timestamp"]);
    });
    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);
    echo json_encode($sessions);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request method."]);
exit;
