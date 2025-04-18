<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . '/../track/session_log.json';

$expirySeconds = 2 * 60;

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS logged_sessions (
            sessionID VARCHAR(26) PRIMARY KEY,
            session_timestamp BIGINT NOT NULL,
            ipAddress VARCHAR(45) NOT NULL,
            country VARCHAR(255) NOT NULL,
            shortName VARCHAR(10) NOT NULL,
            phoneCode VARCHAR(10) NOT NULL,
            browser VARCHAR(100) NOT NULL,
            device VARCHAR(50) NOT NULL,
            logged_at DATETIME NOT NULL
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS logged_session_details (
            logID VARCHAR(26) PRIMARY KEY,
            sessionID VARCHAR(26) NOT NULL,
            event VARCHAR(255) NOT NULL,
            log_timestamp VARCHAR(50) NOT NULL,
            activeNavigation VARCHAR(255) NOT NULL,
            pageTitle VARCHAR(255) NOT NULL,
            FOREIGN KEY (sessionID) REFERENCES logged_sessions(sessionID) ON DELETE CASCADE
        )"
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Table creation failed: ' . $e->getMessage()]));
}


$sessions = [];

if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}


$expiredSessions   = [];
$remainingSessions = [];
$currentTime       = time();

foreach ($sessions as $session) {
    $sessionTimeSeconds = isset($session['timestamp']) ? ($session['timestamp'] / 1000) : $currentTime;
    if (($currentTime - $sessionTimeSeconds) > $expirySeconds) {
        $expiredSessions[] = $session;
    } else {
        $remainingSessions[] = $session;
    }
}


$loggedCount = 0;

foreach ($expiredSessions as $session) {
    try {
        $pdo->beginTransaction();

        $sessionID = $session['sessionID'];

        $loggedAt = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmtSession = $pdo->prepare(
            "INSERT INTO logged_sessions 
                (sessionID, session_timestamp, ipAddress, country, shortName, phoneCode, browser, device, logged_at)
             VALUES 
                (:sessionID, :session_timestamp, :ipAddress, :country, :shortName, :phoneCode, :browser, :device, :logged_at)"
        );
        $stmtSession->execute([
            ':sessionID'       => $sessionID,
            ':session_timestamp' => $session['timestamp'],
            ':ipAddress'       => $session['ipAddress'],
            ':country'         => $session['country'],
            ':shortName'       => $session['shortName'],
            ':phoneCode'       => $session['phoneCode'],
            ':browser'         => $session['browser'],
            ':device'          => $session['device'],
            ':logged_at'       => $loggedAt
        ]);

        if (!empty($session['logs']) && is_array($session['logs'])) {
            $stmtLog = $pdo->prepare(
                "INSERT INTO logged_session_details 
                    (logID, sessionID, event, log_timestamp, activeNavigation, pageTitle)
                 VALUES 
                    (:logID, :sessionID, :event, :log_timestamp, :activeNavigation, :pageTitle)"
            );

            foreach ($session['logs'] as $log) {
                $logID             = generateUlid();
                $event             = $log['event']             ?? '';
                $logTimestamp      = $log['timestamp']         ?? '';
                $activeNavigation  = $log['activeNavigation']  ?? '';
                $pageTitle         = $log['pageTitle']         ?? '';

                $stmtLog->execute([
                    ':logID'            => $logID,
                    ':sessionID'        => $sessionID,
                    ':event'            => $event,
                    ':log_timestamp'    => $logTimestamp,
                    ':activeNavigation' => $activeNavigation,
                    ':pageTitle'        => $pageTitle
                ]);
            }
        }

        $pdo->commit();
        $loggedCount++;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    }
}


file_put_contents($logFile, json_encode($remainingSessions, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode([
    'status'  => 'success',
    'message' => "Logged {$loggedCount} expired session(s)."
]);
exit;
