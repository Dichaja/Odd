<?php
header("Content-Type: application/json");
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . "/session_log.json";
$expirySeconds = 30 * 60;

$sessions = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions))
        $sessions = [];
}

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
        if ($now->getTimestamp() - $then->getTimestamp() > $expirySeconds) {
            unset($sessions[$key]);
        }
    }
    return array_values($sessions);
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if (
        !$data ||
        !isset($data["sessionID"]) ||
        !is_string($data["sessionID"]) ||
        trim($data["sessionID"]) === ''
    ) {
        echo json_encode(["status" => "error", "message" => "Invalid session data."]);
        exit;
    }

    $sid = $data["sessionID"];
    $found = false;
    foreach ($sessions as $idx => $session) {
        if ($session["sessionID"] === $sid) {
            if (
                isset($session['loggedUser'], $data['loggedUser']) &&
                $session['loggedUser'] &&
                $data['loggedUser'] &&
                $session['loggedUser']['user_id'] !== $data['loggedUser']['user_id']
            ) {
                $found = false;
            } else {
                $sessions[$idx] = $data;
                $found = true;
            }
            break;
        }
    }

    if (!$found) {
        $sessions[] = $data;
    }

    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);
    echo json_encode(["status" => "success", "message" => "Logged"]);
    exit;
}

if ($method === "GET") {
    $sessions = cleanExpiredSessions($sessions, $expirySeconds);
    usort($sessions, function ($a, $b) {
        $tA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $tB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $tB - $tA;
    });
    file_put_contents($logFile, json_encode($sessions, JSON_PRETTY_PRINT), LOCK_EX);

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

    echo json_encode([
        "expired" => $expired,
        "sessions" => $sessions
    ]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request method."]);
exit;
