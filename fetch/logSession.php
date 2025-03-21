<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$logFile = __DIR__ . '/../track/session_log.json';

$expirySeconds = 2 * 60;

// Create the tables if they do not exist
try {
    // Table for general session data
    $pdo->exec("CREATE TABLE IF NOT EXISTS logged_sessions (
        sessionID BINARY(16) PRIMARY KEY,
        session_timestamp BIGINT NOT NULL,
        ipAddress VARCHAR(45) NOT NULL,
        country VARCHAR(255) NOT NULL,
        shortName VARCHAR(10) NOT NULL,
        phoneCode VARCHAR(10) NOT NULL,
        browser VARCHAR(100) NOT NULL,
        device VARCHAR(50) NOT NULL,
        logged_at DATETIME NOT NULL
    )");

    // Table for the session logs/details
    $pdo->exec("CREATE TABLE IF NOT EXISTS logged_session_details (
        logID BINARY(16) PRIMARY KEY,
        sessionID BINARY(16) NOT NULL,
        event VARCHAR(255) NOT NULL,
        log_timestamp VARCHAR(50) NOT NULL,
        activeNavigation VARCHAR(255) NOT NULL,
        pageTitle VARCHAR(255) NOT NULL,
        FOREIGN KEY (sessionID) REFERENCES logged_sessions(sessionID) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Table creation failed: ' . $e->getMessage()]));
}

// Helper function to generate a UUIDv7 (stored as binary 16)
function generateUuidV7()
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x70);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
    return $bytes;
}

// Helper function to convert a UUID string (with dashes) to binary (16 bytes)
function uuidToBinary($uuid)
{
    $hex = str_replace('-', '', $uuid);
    return pack("H*", $hex);
}

// Read current sessions from session_log.json
$sessions = [];
if (file_exists($logFile)) {
    $contents = file_get_contents($logFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

// Separate expired sessions from those still active
$expiredSessions = [];
$remainingSessions = [];
$currentTime = time(); // seconds since epoch
foreach ($sessions as $session) {
    $sessionTimeSeconds = isset($session['timestamp']) ? ($session['timestamp'] / 1000) : $currentTime;
    if (($currentTime - $sessionTimeSeconds) > $expirySeconds) {
        $expiredSessions[] = $session;
    } else {
        $remainingSessions[] = $session;
    }
}

$loggedCount = 0;

// Process each expired session
foreach ($expiredSessions as $session) {
    try {
        $pdo->beginTransaction();

        // Convert the sessionID (UUID string) to binary (16 bytes)
        $binarySessionID = uuidToBinary($session['sessionID']);

        // Current timestamp (Africa/Kampala) when logging the session
        $loggedAt = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        // Insert general session data into logged_sessions table
        $stmtSession = $pdo->prepare("INSERT INTO logged_sessions 
            (sessionID, session_timestamp, ipAddress, country, shortName, phoneCode, browser, device, logged_at)
            VALUES (:sessionID, :session_timestamp, :ipAddress, :country, :shortName, :phoneCode, :browser, :device, :logged_at)");
        $stmtSession->bindParam(':sessionID', $binarySessionID, PDO::PARAM_LOB);
        $stmtSession->bindParam(':session_timestamp', $session['timestamp']);
        $stmtSession->bindParam(':ipAddress', $session['ipAddress']);
        $stmtSession->bindParam(':country', $session['country']);
        $stmtSession->bindParam(':shortName', $session['shortName']);
        $stmtSession->bindParam(':phoneCode', $session['phoneCode']);
        $stmtSession->bindParam(':browser', $session['browser']);
        $stmtSession->bindParam(':device', $session['device']);
        $stmtSession->bindParam(':logged_at', $loggedAt);
        $stmtSession->execute();

        // Insert each log entry from the session into logged_session_details table
        if (isset($session['logs']) && is_array($session['logs'])) {
            $stmtLog = $pdo->prepare("INSERT INTO logged_session_details 
                (logID, sessionID, event, log_timestamp, activeNavigation, pageTitle)
                VALUES (:logID, :sessionID, :event, :log_timestamp, :activeNavigation, :pageTitle)");
            foreach ($session['logs'] as $log) {
                $logID = generateUuidV7();
                $event = $log['event'] ?? '';
                $logTimestamp = $log['timestamp'] ?? '';
                $activeNavigation = $log['activeNavigation'] ?? '';
                $pageTitle = $log['pageTitle'] ?? '';
                $stmtLog->bindParam(':logID', $logID, PDO::PARAM_LOB);
                $stmtLog->bindParam(':sessionID', $binarySessionID, PDO::PARAM_LOB);
                $stmtLog->bindParam(':event', $event);
                $stmtLog->bindParam(':log_timestamp', $logTimestamp);
                $stmtLog->bindParam(':activeNavigation', $activeNavigation);
                $stmtLog->bindParam(':pageTitle', $pageTitle);
                $stmtLog->execute();
            }
        }

        $pdo->commit();
        $loggedCount++;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Optionally log the error for this session and continue processing the next one.
    }
}

// Update session_log.json with the sessions that are not expired
file_put_contents($logFile, json_encode($remainingSessions, JSON_PRETTY_PRINT), LOCK_EX);

// Return a JSON response
echo json_encode([
    'status'  => 'success',
    'message' => "Logged {$loggedCount} expired session(s)."
]);
exit;
