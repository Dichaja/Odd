<?php
require_once __DIR__ . '/../config/config.php';  // must define $pdo and generateUlid()
header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$jsonFile = __DIR__ . '/../track/session_log.json';
$expirySeconds = 30 * 60;

try {
    // 1) Ensure sessions table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sessions` (
          `session_id`     VARCHAR(26)    NOT NULL,
          `last_update_ms` BIGINT         NOT NULL,
          `ip_address`     VARCHAR(45)    NOT NULL,
          `country`        VARCHAR(100)   NOT NULL,
          `short_name`     CHAR(2)        NOT NULL,
          `phone_code`     VARCHAR(10)    NOT NULL,
          `browser`        VARCHAR(50)    NOT NULL,
          `device`         VARCHAR(50)    NOT NULL,
          `latitude`       DECIMAL(10,7)  NULL,
          `longitude`      DECIMAL(10,7)  NULL,
          `user_id`        VARCHAR(26)    NULL,
          `admin_id`       VARCHAR(26)    NULL,
          PRIMARY KEY (`session_id`),
          CONSTRAINT `chk_sessions_not_both_user_and_admin`
            CHECK (`user_id` IS NULL OR `admin_id` IS NULL),
          INDEX `idx_sessions_user`  (`user_id`),
          INDEX `idx_sessions_admin` (`admin_id`),
          CONSTRAINT `fk_sessions_user`
            FOREIGN KEY (`user_id`)
            REFERENCES `zzimba_users` (`id`)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
          CONSTRAINT `fk_sessions_admin`
            FOREIGN KEY (`admin_id`)
            REFERENCES `admin_users` (`id`)
            ON UPDATE CASCADE
            ON DELETE SET NULL
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_general_ci;
    ");

    // 2) Ensure session_events table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `session_events` (
          `event_id`         VARCHAR(26)    NOT NULL,
          `session_id`       VARCHAR(26)    NOT NULL,
          `event_name`       VARCHAR(50)    NOT NULL,
          `event_timestamp`  DATETIME(3)    NOT NULL,
          `referrer`         TEXT           NULL,
          `url`              TEXT           NULL,
          `active_navigation` VARCHAR(100)  NULL,
          `page_title`       VARCHAR(200)   NULL,
          `identifier`       VARCHAR(100)   NULL,
          `status`           VARCHAR(20)    NULL,
          PRIMARY KEY (`event_id`),
          INDEX `idx_events_session`   (`session_id`),
          INDEX `idx_events_name_time` (`event_name`, `event_timestamp`),
          CONSTRAINT `fk_events_session`
            FOREIGN KEY (`session_id`)
            REFERENCES `sessions` (`session_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Table creation failed: ' . $e->getMessage()
    ]));
}

// 3) Load in‑memory sessions from JSON
$sessions = [];
if (file_exists($jsonFile)) {
    $contents = file_get_contents($jsonFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

// 4) Split into expired vs remaining
$expiredSessions = [];
$remainingSessions = [];
$currentTime = time();

foreach ($sessions as $session) {
    $ts = isset($session['timestamp'])
        ? @strtotime($session['timestamp'])
        : false;

    if ($ts === false || ($currentTime - $ts) > $expirySeconds) {
        $expiredSessions[] = $session;
    } else {
        $remainingSessions[] = $session;
    }
}

$loggedCount = 0;

// 5) Persist each expired session + its logs to your DB
foreach ($expiredSessions as $session) {
    try {
        $pdo->beginTransaction();

        // --- Upsert into sessions ---
        $sessionID = $session['sessionID'];
        // convert ISO timestamp -> ms
        $lastUpdateMs = isset($session['timestamp'])
            ? (strtotime($session['timestamp']) * 1000)
            : ($currentTime * 1000);

        $lat = $session['coords']['latitude'] ?? null;
        $lng = $session['coords']['longitude'] ?? null;
        $userId = $session['loggedUser'] ?? null;
        $adminId = null; // adapt if you know which is which

        $stmt = $pdo->prepare("
            INSERT INTO `sessions` (
                session_id, last_update_ms, ip_address, country,
                short_name, phone_code, browser, device,
                latitude, longitude, user_id, admin_id
            ) VALUES (
                :session_id, :last_update_ms, :ip_address, :country,
                :short_name, :phone_code, :browser, :device,
                :latitude, :longitude, :user_id, :admin_id
            )
            ON DUPLICATE KEY UPDATE
                last_update_ms = VALUES(last_update_ms),
                ip_address     = VALUES(ip_address),
                country        = VALUES(country),
                short_name     = VALUES(short_name),
                phone_code     = VALUES(phone_code),
                browser        = VALUES(browser),
                device         = VALUES(device),
                latitude       = VALUES(latitude),
                longitude      = VALUES(longitude),
                user_id        = VALUES(user_id),
                admin_id       = VALUES(admin_id)
        ");

        $stmt->execute([
            ':session_id' => $sessionID,
            ':last_update_ms' => $lastUpdateMs,
            ':ip_address' => $session['ipAddress'],
            ':country' => $session['country'],
            ':short_name' => $session['shortName'],
            ':phone_code' => $session['phoneCode'],
            ':browser' => $session['browser'],
            ':device' => $session['device'],
            ':latitude' => $lat,
            ':longitude' => $lng,
            ':user_id' => $userId,
            ':admin_id' => $adminId,
        ]);

        // --- Insert each log into session_events ---
        if (!empty($session['logs']) && is_array($session['logs'])) {
            $stmtLog = $pdo->prepare("
                INSERT INTO `session_events` (
                    event_id, session_id, event_name, event_timestamp,
                    referrer, url, active_navigation, page_title,
                    identifier, status
                ) VALUES (
                    :event_id, :session_id, :event_name, :event_timestamp,
                    :referrer, :url, :active_navigation, :page_title,
                    :identifier, :status
                )
            ");

            foreach ($session['logs'] as $log) {
                $logID = generateUlid();
                $evtTime = isset($log['timestamp'])
                    ? date('Y-m-d H:i:s.u', strtotime($log['timestamp']))
                    : null;

                $stmtLog->execute([
                    ':event_id' => $logID,
                    ':session_id' => $sessionID,
                    ':event_name' => $log['event'] ?? null,
                    ':event_timestamp' => $evtTime,
                    ':referrer' => $log['referrer'] ?? null,
                    ':url' => $log['url'] ?? null,
                    ':active_navigation' => $log['activeNavigation'] ?? null,
                    ':page_title' => $log['pageTitle'] ?? null,
                    ':identifier' => $log['identifier'] ?? null,
                    ':status' => $log['status'] ?? null,
                ]);
            }
        }

        $pdo->commit();
        $loggedCount++;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // optionally: log $e->getMessage()
    }
}

// 6) Overwrite JSON file with only the still‑alive sessions
file_put_contents(
    $jsonFile,
    json_encode($remainingSessions, JSON_PRETTY_PRINT),
    LOCK_EX
);

// 7) Respond
echo json_encode([
    'status' => 'success',
    'message' => "Logged {$loggedCount} expired session(s)."
]);
exit;
