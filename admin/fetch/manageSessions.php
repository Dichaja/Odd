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
    // Get live sessions from JSON file
    $liveSessions = getLiveSessionsFromJson();

    // Get expired sessions from database
    $expiredSessions = getExpiredSessionsFromDatabase();

    // Combine and sort by last activity (live sessions first)
    $allSessions = array_merge($liveSessions, $expiredSessions);

    // Sort: active sessions first, then by last activity time
    usort($allSessions, function ($a, $b) {
        if ($a['isActive'] !== $b['isActive']) {
            return $b['isActive'] - $a['isActive']; // Active sessions first
        }
        return $b['lastActivityTime'] - $a['lastActivityTime'];
    });

    return $allSessions;
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

    foreach ($sessions as $session) {
        // Normalize country code
        $session['shortName'] = strtolower($session['shortName']);

        // Extract logged user from successful login events
        if (!isset($session['loggedUser']) || $session['loggedUser'] === null) {
            $session['loggedUser'] = extractLoggedUserFromEvents($session['logs'] ?? []);
        }

        // Calculate session timing for live sessions
        if (isset($session['logs']) && is_array($session['logs']) && count($session['logs']) > 0) {
            usort($session['logs'], function ($a, $b) {
                return strtotime($a['timestamp']) - strtotime($b['timestamp']);
            });
            $firstLog = reset($session['logs']);
            $lastLog = end($session['logs']);

            $sessionStartTime = strtotime($firstLog['timestamp']);
            $session['lastActivity'] = $lastLog['timestamp'];
            $session['lastActivityTime'] = strtotime($lastLog['timestamp']);
        } else {
            // No logs: use session timestamp for both start and last activity
            $sessionStartTime = strtotime($session['timestamp']);
            $session['lastActivity'] = $session['timestamp'];
            $session['lastActivityTime'] = strtotime($session['timestamp']);
        }

        // For live sessions, compute active duration from session start to now
        $now = time();
        $duration = $now - $sessionStartTime;

        // Format duration (never show seconds for live sessions, minimum 1 minute)
        if ($duration < 60) {
            $session['activeDuration'] = '1m';
        } elseif ($duration >= 3600) {
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $session['activeDuration'] = $minutes > 0 ? sprintf('%dh %dm', $hours, $minutes) : sprintf('%dh', $hours);
        } else {
            $minutes = floor($duration / 60);
            $session['activeDuration'] = sprintf('%dm', $minutes);
        }

        // Live sessions are active if last activity was within 30 minutes
        $timeSinceLastActivity = $now - $session['lastActivityTime'];
        $session['isActive'] = ($timeSinceLastActivity < 1800); // 30 minutes
        $session['isExpired'] = false;

        // Add authentication statistics
        $session['authStats'] = calculateAuthStats($session['logs'] ?? []);

        $formattedSessions[] = $session;
    }

    return $formattedSessions;
}

function getExpiredSessionsFromDatabase()
{
    global $pdo;

    try {
        // Get all sessions from database with their latest activity
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
            // Get all events for this session
            $eventsStmt = $pdo->prepare("
                SELECT * FROM session_events 
                WHERE session_id = ? 
                ORDER BY event_timestamp ASC
            ");
            $eventsStmt->execute([$session['session_id']]);
            $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Format expired session data
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
    // Calculate session timing for expired sessions
    $sessionStart = strtotime($session['created_at']);
    $lastActivityTime = strtotime($session['last_activity_time']);

    // For expired sessions, duration is static - from session start to last event
    $duration = $lastActivityTime - $sessionStart;

    // Format duration (never negative, never in seconds)
    if ($duration <= 0) {
        $activeDuration = '1m';
    } elseif ($duration >= 3600) {
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $activeDuration = $minutes > 0 ? sprintf('%dh %dm', $hours, $minutes) : sprintf('%dh', $hours);
    } else {
        $minutes = floor($duration / 60);
        $activeDuration = $minutes > 0 ? sprintf('%dm', $minutes) : '1m';
    }

    // Format logged user data
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

    // Format coordinates
    $coords = null;
    if ($session['latitude'] && $session['longitude']) {
        $coords = [
            'latitude' => (float) $session['latitude'],
            'longitude' => (float) $session['longitude']
        ];
    }

    // Format events
    $logs = [];
    foreach ($events as $event) {
        $log = [
            'event' => $event['event_name'],
            'timestamp' => date('c', strtotime($event['event_timestamp']))
        ];

        // Add event-specific data
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
        'lastActivity' => null, // No last activity for expired sessions
        'lastActivityTime' => $lastActivityTime,
        'activeDuration' => $activeDuration,
        'isActive' => false, // All database sessions are expired
        'isExpired' => true,
        'authStats' => calculateAuthStats($logs)
    ];
}

function extractLoggedUserFromEvents($logs)
{
    if (!is_array($logs)) {
        return null;
    }

    // Look for successful login events to extract username
    foreach ($logs as $log) {
        if ($log['event'] === 'login_success') {
            // Look backwards for the identifier that was successfully used
            foreach (array_reverse($logs) as $prevLog) {
                if ($prevLog['event'] === 'login_identifier_success' && isset($prevLog['identifier'])) {
                    return $prevLog['identifier'];
                }
                // Stop looking if we hit another login attempt
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

function streamSessions()
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    ignore_user_abort(true);
    set_time_limit(0);

    $lastCheck = 0;

    while (true) {
        if (connection_aborted()) {
            break;
        }

        $currentTime = time();

        // Check for updates every 2 seconds
        if ($currentTime > $lastCheck + 2) {
            $lastCheck = $currentTime;

            $sessions = getSessionData();

            // Enrich with country flags/codes if needed
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

            // Send sessions update
            echo "data: " . json_encode([
                'type' => 'sessions_update',
                'data' => $sessions,
                'timestamp' => $currentTime,
            ]) . "\n\n";
        }

        // Send heartbeat every 5 seconds
        if ($currentTime % 5 == 0) {
            echo "data: " . json_encode([
                'type' => 'heartbeat',
                'timestamp' => $currentTime,
            ]) . "\n\n";
        }

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        sleep(1);
    }
}

$action = $_GET['action'] ?? 'get';

switch ($action) {
    case 'stream':
        streamSessions();
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
                'total_events' => array_sum(array_map(fn($s) => count($s['logs'] ?? []), $sessions)),
                'total_login_attempts' => array_sum(array_map(fn($s) => $s['authStats']['login_attempts'] ?? 0, $sessions)),
                'total_login_successes' => array_sum(array_map(fn($s) => $s['authStats']['login_successes'] ?? 0, $sessions)),
                'total_login_failures' => array_sum(array_map(fn($s) => $s['authStats']['login_failures'] ?? 0, $sessions)),
            ] : null,
        ]);
        break;
}
?>