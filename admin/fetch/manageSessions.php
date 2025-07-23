<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');

function getSessionData()
{
    $liveSessions = getLiveSessionsFromJson();
    $expiredSessions = getExpiredSessionsFromDatabase();
    $allSessions = array_merge($liveSessions, $expiredSessions);

    usort($allSessions, function ($a, $b) {
        if ($a['isActive'] !== $b['isActive']) {
            return $b['isActive'] - $a['isActive'];
        }
        return $b['lastActivityTime'] - $a['lastActivityTime'];
    });

    return $allSessions;
}

function getSpecificSessionData($sessionId)
{
    $allSessions = getSessionData();

    foreach ($allSessions as $session) {
        if ($session['sessionID'] === $sessionId) {
            return $session;
        }
    }

    return null;
}

function getLiveSessionsFromJson()
{
    $jsonFile = __DIR__ . '/../../track/session_log.json';

    if (!file_exists($jsonFile)) {
        return [];
    }

    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        return [];
    }

    $sessions = json_decode($jsonContent, true);
    if ($sessions === null) {
        return [];
    }

    $formattedSessions = [];
    $now = time();

    foreach ($sessions as $session) {
        $session['shortName'] = strtolower($session['shortName']);

        if (!isset($session['loggedUser']) || $session['loggedUser'] === null) {
            $session['loggedUser'] = extractLoggedUserFromEvents($session['logs'] ?? []);
        }

        if (isset($session['logs']) && is_array($session['logs']) && count($session['logs']) > 0) {
            usort($session['logs'], function ($a, $b) {
                return strtotime($a['timestamp']) - strtotime($b['timestamp']);
            });
            $firstLog = reset($session['logs']);
            $lastLog = end($session['logs']);

            $firstLogTime = strtotime($firstLog['timestamp']);
            $lastLogTime = strtotime($lastLog['timestamp']);

            $duration = $now - $firstLogTime;
            $session['activeDuration'] = formatDuration($duration);

            $session['lastActivity'] = $lastLog['timestamp'];
            $session['lastActivityTime'] = $lastLogTime;
        } else {
            $sessionStartTime = strtotime($session['timestamp']);
            $duration = $now - $sessionStartTime;
            $session['activeDuration'] = formatDuration($duration);
            $session['lastActivity'] = $session['timestamp'];
            $session['lastActivityTime'] = $sessionStartTime;
        }

        $timeSinceLastActivity = $now - $session['lastActivityTime'];
        $session['isActive'] = ($timeSinceLastActivity < 1800);
        $session['isExpired'] = false;

        $session['authStats'] = calculateAuthStats($session['logs'] ?? []);

        $formattedSessions[] = $session;
    }

    return $formattedSessions;
}

function getExpiredSessionsFromDatabase()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.*,
                COALESCE(MAX(se.event_timestamp), s.created_at) as last_activity_time
            FROM sessions s
            LEFT JOIN session_events se ON s.session_id = se.session_id
            GROUP BY s.session_id
            ORDER BY last_activity_time DESC
        ");
        $stmt->execute();
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedSessions = [];

        foreach ($sessions as $session) {
            $eventsStmt = $pdo->prepare("
                SELECT * FROM session_events 
                WHERE session_id = ? 
                ORDER BY event_timestamp ASC
            ");
            $eventsStmt->execute([$session['session_id']]);
            $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

            $formattedSession = formatExpiredSessionData($session, $events);
            $formattedSessions[] = $formattedSession;
        }

        return $formattedSessions;

    } catch (PDOException $e) {
        error_log("Database error in getExpiredSessionsFromDatabase: " . $e->getMessage());
        return [];
    }
}

function formatExpiredSessionData($session, $events)
{
    $sessionStart = strtotime($session['created_at']);
    $lastActivityTime = strtotime($session['last_activity_time']);

    if (count($events) > 0) {
        $firstEventTime = strtotime($events[0]['event_timestamp']);
        $lastEventTime = strtotime($events[count($events) - 1]['event_timestamp']);
        $duration = $lastEventTime - $firstEventTime;
    } else {
        $duration = 0;
    }

    $activeDuration = formatDuration($duration);

    $loggedUser = null;
    if ($session['logged_in'] && $session['user_id']) {
        $loggedUser = [
            'logged_in' => true,
            'user_id' => $session['user_id'],
            'username' => $session['username'],
            'email' => $session['email'],
            'phone' => $session['phone'],
            'is_admin' => (bool) $session['is_admin'],
            'last_login' => $session['last_login']
        ];
    }

    $coords = null;
    if ($session['latitude'] && $session['longitude']) {
        $coords = [
            'latitude' => (float) $session['latitude'],
            'longitude' => (float) $session['longitude']
        ];
    }

    $logs = [];
    foreach ($events as $event) {
        $log = [
            'event' => $event['event_name'],
            'timestamp' => date('c', strtotime($event['event_timestamp']))
        ];

        if ($event['referrer'])
            $log['referrer'] = $event['referrer'];
        if ($event['url'])
            $log['url'] = $event['url'];
        if ($event['active_navigation'])
            $log['activeNavigation'] = $event['active_navigation'];
        if ($event['page_title'])
            $log['pageTitle'] = $event['page_title'];
        if ($event['identifier'])
            $log['identifier'] = $event['identifier'];
        if ($event['identifier_type'])
            $log['identifierType'] = $event['identifier_type'];
        if ($event['username'])
            $log['username'] = $event['username'];
        if ($event['email'])
            $log['email'] = $event['email'];
        if ($event['phone'])
            $log['phone'] = $event['phone'];
        if ($event['error_message'])
            $log['errorMessage'] = $event['error_message'];
        if ($event['status'])
            $log['status'] = $event['status'];
        if ($event['from_form'])
            $log['fromForm'] = $event['from_form'];
        if ($event['to_form'])
            $log['toForm'] = $event['to_form'];
        if ($event['form_type'])
            $log['formType'] = $event['form_type'];
        if ($event['step'])
            $log['step'] = $event['step'];
        if ($event['action'])
            $log['action'] = $event['action'];
        if ($event['method'])
            $log['method'] = $event['method'];
        if ($event['element'])
            $log['element'] = $event['element'];
        if ($event['scroll_position'])
            $log['scrollPosition'] = $event['scroll_position'];
        if ($event['query'])
            $log['query'] = $event['query'];
        if ($event['search_results'])
            $log['searchResults'] = $event['search_results'];
        if ($event['product_id'])
            $log['productId'] = $event['product_id'];
        if ($event['product_name'])
            $log['productName'] = $event['product_name'];
        if ($event['category_id'])
            $log['categoryId'] = $event['category_id'];
        if ($event['category_name'])
            $log['categoryName'] = $event['category_name'];
        if ($event['quantity'])
            $log['quantity'] = $event['quantity'];
        if ($event['price'])
            $log['price'] = $event['price'];
        if ($event['cart_value'])
            $log['cartValue'] = $event['cart_value'];
        if ($event['amount'])
            $log['amount'] = $event['amount'];
        if ($event['order_id'])
            $log['orderId'] = $event['order_id'];
        if ($event['payment_method'])
            $log['paymentMethod'] = $event['payment_method'];
        if ($event['filter_type'])
            $log['filterType'] = $event['filter_type'];
        if ($event['filter_value'])
            $log['filterValue'] = $event['filter_value'];
        if ($event['sort_by'])
            $log['sortBy'] = $event['sort_by'];
        if ($event['sort_order'])
            $log['sortOrder'] = $event['sort_order'];
        if ($event['contact_type'])
            $log['contactType'] = $event['contact_type'];
        if ($event['contact_value'])
            $log['contactValue'] = $event['contact_value'];
        if ($event['message'])
            $log['message'] = $event['message'];
        if ($event['otp_type'])
            $log['otpType'] = $event['otp_type'];
        if ($event['otp_attempts'])
            $log['otpAttempts'] = $event['otp_attempts'];
        if ($event['extra_data']) {
            $extraData = json_decode($event['extra_data'], true);
            if ($extraData) {
                $log = array_merge($log, $extraData);
            }
        }

        $logs[] = $log;
    }

    return [
        'sessionID' => $session['session_id'],
        'timestamp' => date('c', $sessionStart),
        'ipAddress' => $session['ip_address'],
        'country' => $session['country'],
        'shortName' => strtolower($session['short_name']),
        'phoneCode' => $session['phone_code'],
        'browser' => $session['browser'],
        'device' => $session['device'],
        'coords' => $coords,
        'loggedUser' => $loggedUser,
        'logs' => $logs,
        'lastActivity' => null,
        'lastActivityTime' => $lastActivityTime,
        'activeDuration' => $activeDuration,
        'isActive' => false,
        'isExpired' => true,
        'authStats' => calculateAuthStats($logs)
    ];
}

function formatDuration($seconds)
{
    if ($seconds <= 0) {
        return '0s';
    }

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    $parts = [];
    if ($hours > 0)
        $parts[] = $hours . 'h';
    if ($minutes > 0)
        $parts[] = $minutes . 'm';
    if ($secs > 0)
        $parts[] = $secs . 's';

    return empty($parts) ? '0s' : implode(' ', $parts);
}

function extractLoggedUserFromEvents($logs)
{
    if (!is_array($logs)) {
        return null;
    }

    foreach ($logs as $log) {
        if ($log['event'] === 'login_success') {
            foreach (array_reverse($logs) as $prevLog) {
                if ($prevLog['event'] === 'login_identifier_success' && isset($prevLog['identifier'])) {
                    return $prevLog['identifier'];
                }
                if ($prevLog['event'] === 'login_success' && $prevLog !== $log) {
                    break;
                }
            }
        }
    }

    return null;
}

function calculateAuthStats($logs)
{
    if (!is_array($logs)) {
        return [
            'login_attempts' => 0,
            'login_successes' => 0,
            'login_failures' => 0,
            'registration_attempts' => 0,
            'password_reset_attempts' => 0,
            'last_login_attempt' => null,
            'failed_identifiers' => [],
            'successful_identifiers' => []
        ];
    }

    $stats = [
        'login_attempts' => 0,
        'login_successes' => 0,
        'login_failures' => 0,
        'registration_attempts' => 0,
        'password_reset_attempts' => 0,
        'last_login_attempt' => null,
        'failed_identifiers' => [],
        'successful_identifiers' => []
    ];

    foreach ($logs as $log) {
        switch ($log['event']) {
            case 'login_identifier_submit':
                $stats['login_attempts']++;
                $stats['last_login_attempt'] = $log['timestamp'];
                break;

            case 'login_success':
                $stats['login_successes']++;
                break;

            case 'login_identifier_failed':
            case 'login_password_failed':
                $stats['login_failures']++;
                if (isset($log['identifier'])) {
                    $stats['failed_identifiers'][] = [
                        'identifier' => $log['identifier'],
                        'type' => $log['identifierType'] ?? 'unknown',
                        'error' => $log['errorMessage'] ?? 'Unknown error',
                        'timestamp' => $log['timestamp']
                    ];
                }
                break;

            case 'login_identifier_success':
                if (isset($log['identifier'])) {
                    $stats['successful_identifiers'][] = [
                        'identifier' => $log['identifier'],
                        'type' => $log['identifierType'] ?? 'unknown',
                        'timestamp' => $log['timestamp']
                    ];
                }
                break;

            case 'register_username_submit':
            case 'register_email_submit':
                $stats['registration_attempts']++;
                break;

            case 'password_reset_requested':
                $stats['password_reset_attempts']++;
                break;
        }
    }

    return $stats;
}

function getCountryInfo($countryName)
{
    static $countryCache = [];

    if (isset($countryCache[$countryName])) {
        return $countryCache[$countryName];
    }

    $apiUrl = "https://restcountries.com/v3.1/name/" . urlencode($countryName) . "?fields=name,cca2,flag,idd";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (compatible; SessionMonitor/1.0)',
        ],
    ]);

    $response = @file_get_contents($apiUrl, false, $context);
    if ($response === false) {
        $countryCache[$countryName] = [
            'shortName' => strtoupper(substr($countryName, 0, 2)),
            'flag' => '🏳️',
            'phoneCode' => '+000',
        ];
        return $countryCache[$countryName];
    }

    $data = json_decode($response, true);
    if (!$data || !is_array($data) || empty($data)) {
        $countryCache[$countryName] = [
            'shortName' => strtoupper(substr($countryName, 0, 2)),
            'flag' => '🏳️',
            'phoneCode' => '+000',
        ];
        return $countryCache[$countryName];
    }

    $country = $data[0];
    $countryInfo = [
        'shortName' => $country['cca2'] ?? strtoupper(substr($countryName, 0, 2)),
        'flag' => $country['flag'] ?? '🏳️',
        'phoneCode' => (isset($country['idd']['root'], $country['idd']['suffixes'][0]))
            ? $country['idd']['root'] . $country['idd']['suffixes'][0]
            : '+000',
    ];

    $countryCache[$countryName] = $countryInfo;
    return $countryInfo;
}

function streamAllSessions()
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    ignore_user_abort(true);
    set_time_limit(0);

    $lastSessionsUpdate = 0;
    $lastHeartbeat = 0;

    while (true) {
        if (connection_aborted()) {
            break;
        }

        $currentTime = time();

        if ($currentTime >= $lastSessionsUpdate + 2) {
            $lastSessionsUpdate = $currentTime;

            $sessions = getSessionData();

            if (!isset($sessions['error'])) {
                foreach ($sessions as &$session) {
                    if (isset($session['country']) && !isset($session['flag'])) {
                        $countryInfo = getCountryInfo($session['country']);
                        $session['shortName'] = strtolower($countryInfo['shortName']);
                        $session['flag'] = $countryInfo['flag'];
                        if (!isset($session['phoneCode'])) {
                            $session['phoneCode'] = $countryInfo['phoneCode'];
                        }
                    }
                }
                unset($session);
            }

            echo "data: " . json_encode([
                'type' => 'sessions_update',
                'data' => $sessions,
                'timestamp' => $currentTime,
            ]) . "\n\n";

            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }

        if ($currentTime >= $lastHeartbeat + 2) {
            $lastHeartbeat = $currentTime;
            echo "data: " . json_encode([
                'type' => 'heartbeat',
                'timestamp' => $currentTime,
            ]) . "\n\n";

            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }

        usleep(100000);
    }
}

function streamSpecificSession($sessionId)
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    ignore_user_abort(true);
    set_time_limit(0);

    $lastSessionUpdate = 0;
    $lastHeartbeat = 0;

    while (true) {
        if (connection_aborted()) {
            break;
        }

        $currentTime = time();

        if ($currentTime >= $lastSessionUpdate + 1) { // Update every 1 second for specific session
            $lastSessionUpdate = $currentTime;

            $session = getSpecificSessionData($sessionId);

            if ($session) {
                if (isset($session['country']) && !isset($session['flag'])) {
                    $countryInfo = getCountryInfo($session['country']);
                    $session['shortName'] = strtolower($countryInfo['shortName']);
                    $session['flag'] = $countryInfo['flag'];
                    if (!isset($session['phoneCode'])) {
                        $session['phoneCode'] = $countryInfo['phoneCode'];
                    }
                }

                echo "data: " . json_encode([
                    'type' => 'session_update',
                    'session_id' => $sessionId,
                    'data' => $session,
                    'timestamp' => $currentTime,
                ]) . "\n\n";

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
        }

        if ($currentTime >= $lastHeartbeat + 5) {
            $lastHeartbeat = $currentTime;
            echo "data: " . json_encode([
                'type' => 'heartbeat',
                'session_id' => $sessionId,
                'timestamp' => $currentTime,
            ]) . "\n\n";

            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }

        usleep(100000);
    }
}

$action = $_GET['action'] ?? 'get';
$sessionId = $_GET['session_id'] ?? null;

switch ($action) {
    case 'stream':
        if ($sessionId) {
            streamSpecificSession($sessionId);
        } else {
            streamAllSessions();
        }
        break;

    case 'get':
    default:
        $sessions = getSessionData();

        if (!isset($sessions['error'])) {
            foreach ($sessions as &$session) {
                if (isset($session['country']) && !isset($session['flag'])) {
                    $countryInfo = getCountryInfo($session['country']);
                    $session['shortName'] = strtolower($countryInfo['shortName']);
                    $session['flag'] = $countryInfo['flag'];
                    if (!isset($session['phoneCode'])) {
                        $session['phoneCode'] = $countryInfo['phoneCode'];
                    }
                }
            }
            unset($session);
        }

        echo json_encode([
            'success' => !isset($sessions['error']),
            'data' => $sessions,
            'timestamp' => time(),
            'stats' => !isset($sessions['error']) ? [
                'total_sessions' => count($sessions),
                'active_sessions' => count(array_filter($sessions, fn($s) => $s['isActive'])),
                'logged_users' => count(array_filter($sessions, fn($s) => $s['loggedUser'] !== null)),
                'unique_countries' => count(array_unique(array_column($sessions, 'country'))),
                'total_login_attempts' => array_sum(array_map(fn($s) => $s['authStats']['login_attempts'] ?? 0, $sessions)),
                'total_login_successes' => array_sum(array_map(fn($s) => $s['authStats']['login_successes'] ?? 0, $sessions)),
                'total_login_failures' => array_sum(array_map(fn($s) => $s['authStats']['login_failures'] ?? 0, $sessions)),
            ] : null,
        ]);
        break;
}
?>