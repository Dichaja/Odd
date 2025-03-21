<?php
require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Ensure tables exist before proceeding
try {
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

try {
    switch ($action) {
        case 'getPastSessions':
            $start = $_GET['start'] ?? '';
            $end = $_GET['end'] ?? '';

            if (!$start || !$end) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing date range']));
            }

            // Format dates for SQL (assuming ISO format from datetime-local input)
            $startDt = str_replace('T', ' ', $start) . ':00';
            $endDt = str_replace('T', ' ', $end) . ':59';

            $stmt = $pdo->prepare("SELECT * FROM logged_sessions WHERE logged_at BETWEEN :start AND :end ORDER BY logged_at DESC");
            $stmt->bindParam(':start', $startDt);
            $stmt->bindParam(':end', $endDt);
            $stmt->execute();
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach ($sessions as $session) {
                $sessionIDHex = bin2hex($session['sessionID']);
                $sessionStart = $session['session_timestamp']; // stored as ms
                $session['sessionID'] = $sessionIDHex;
                $session['timestamp'] = (int)$sessionStart; // raw timestamp (ms)

                // Convert session start (from ms) and session end (logged_at) to the desired format.
                $session['formatted_start'] = date("M jS, Y g:i:s A", $sessionStart / 1000);
                $session['formatted_end'] = date("M jS, Y g:i:s A", strtotime($session['logged_at']));

                // Fetch logs for this session.
                $stmtDetails = $pdo->prepare("SELECT event, log_timestamp, activeNavigation, pageTitle FROM logged_session_details WHERE sessionID = :sessionID ORDER BY log_timestamp ASC");
                $stmtDetails->bindValue(':sessionID', hex2bin($sessionIDHex), PDO::PARAM_LOB);
                $stmtDetails->execute();
                $logs = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

                // For each log, convert the stored log_timestamp ("18/02/2025, 23:54:48") 
                // to the format "Feb 18th, 2025 11:54:48 PM"
                foreach ($logs as &$log) {
                    $dt = DateTime::createFromFormat("d/m/Y, H:i:s", $log['log_timestamp']);
                    if ($dt !== false) {
                        $log['timestamp'] = $dt->format("M jS, Y g:i:s A");
                    } else {
                        $log['timestamp'] = $log['log_timestamp'];
                    }
                }

                $session['logs'] = $logs;
                unset($session['session_timestamp']);
                $result[] = $session;
            }

            echo json_encode($result);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
