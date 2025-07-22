<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

function getSessionData()
{
    $jsonFile = __DIR__ . '/../../track/session_log.json';

    if (!file_exists($jsonFile)) {
        return ['error' => 'Session log file not found'];
    }

    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        return ['error' => 'Unable to read session log file'];
    }

    $sessions = json_decode($jsonContent, true);
    if ($sessions === null) {
        return ['error' => 'Invalid JSON format'];
    }

    foreach ($sessions as &$session) {
        // normalize country code
        $session['shortName'] = strtoupper($session['shortName']);

        // Extract logged user from successful login events
        if (!isset($session['loggedUser']) || $session['loggedUser'] === null) {
            $session['loggedUser'] = extractLoggedUserFromEvents($session['logs'] ?? []);
        }

        // if we have logs, sort them and take first & last as respectively session start and last activity
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
            // no logs: use session timestamp for both start and last activity
            $sessionStartTime = strtotime($session['timestamp']);
            $session['lastActivity'] = $session['timestamp'];
            $session['lastActivityTime'] = strtotime($session['timestamp']);
        }

        // compute how long the session has been active, from the session start
        $now = time();
        $duration = $now - $sessionStartTime;

        if ($duration >= 3600) {
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            $session['activeDuration'] = sprintf('%dh %dm', $hours, $minutes);
        } elseif ($duration >= 60) {
            $minutes = floor($duration / 60);
            $seconds = $duration % 60;
            $session['activeDuration'] = sprintf('%dm %ds', $minutes, $seconds);
        } else {
            $session['activeDuration'] = sprintf('%ds', $duration);
        }

        // consider session active if started less than 30 minutes ago
        $session['isActive'] = ($duration < 1800);

        // Add authentication statistics
        $session['authStats'] = calculateAuthStats($session['logs'] ?? []);
    }
    unset($session);

    // newest activity first
    usort($sessions, function ($a, $b) {
        return $b['lastActivityTime'] - $a['lastActivityTime'];
    });

    return $sessions;
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

    $lastModified = 0;

    while (true) {
        if (connection_aborted()) {
            break;
        }

        clearstatcache();
        $jsonFile = __DIR__ . '/../../track/session_log.json';

        // Check if file was modified
        $currentModified = file_exists($jsonFile) ? filemtime($jsonFile) : 0;

        if ($currentModified > $lastModified) {
            $lastModified = $currentModified;

            $sessions = getSessionData();

            // enrich with country flags/codes
            if (!isset($sessions['error'])) {
                foreach ($sessions as &$session) {
                    if (isset($session['country'])) {
                        $countryInfo = getCountryInfo($session['country']);
                        $session['shortName'] = $countryInfo['shortName'];
                        $session['flag'] = $countryInfo['flag'];
                        if (!isset($session['phoneCode'])) {
                            $session['phoneCode'] = $countryInfo['phoneCode'];
                        }
                    }
                }
                unset($session);
            }

            // send sessions update
            echo "data: " . json_encode([
                'type' => 'sessions_update',
                'data' => $sessions,
                'timestamp' => time(),
            ]) . "\n\n";
        }

        // send heartbeat every 5 seconds
        echo "data: " . json_encode([
            'type' => 'heartbeat',
            'timestamp' => time(),
        ]) . "\n\n";

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        sleep(2);
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
                if (isset($session['country'])) {
                    $countryInfo = getCountryInfo($session['country']);
                    $session['shortName'] = $countryInfo['shortName'];
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