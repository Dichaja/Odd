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
        $session['shortName'] = strtoupper($session['shortName']);

        $sessionStart = strtotime($session['timestamp']);
        $now = time();
        $duration = $now - $sessionStart;

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            $session['activeDuration'] = sprintf('%dh %dm', $hours, $minutes);
        } elseif ($minutes > 0) {
            $session['activeDuration'] = sprintf('%dm %ds', $minutes, $seconds);
        } else {
            $session['activeDuration'] = sprintf('%ds', $seconds);
        }

        $session['isActive'] = ($duration < 1800);

        if (isset($session['logs']) && is_array($session['logs'])) {
            usort($session['logs'], function ($a, $b) {
                return strtotime($a['timestamp']) - strtotime($b['timestamp']);
            });

            $lastLog = end($session['logs']);
            $session['lastActivity'] = $lastLog['timestamp'];
            $session['lastActivityTime'] = strtotime($lastLog['timestamp']);
        } else {
            $session['lastActivity'] = $session['timestamp'];
            $session['lastActivityTime'] = strtotime($session['timestamp']);
        }
    }

    usort($sessions, function ($a, $b) {
        return $b['lastActivityTime'] - $a['lastActivityTime'];
    });

    return $sessions;
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
            'user_agent' => 'Mozilla/5.0 (compatible; SessionMonitor/1.0)'
        ]
    ]);

    $response = @file_get_contents($apiUrl, false, $context);

    if ($response === false) {
        $countryCache[$countryName] = [
            'shortName' => strtoupper(substr($countryName, 0, 2)),
            'flag' => '🏳️',
            'phoneCode' => '+000'
        ];
        return $countryCache[$countryName];
    }

    $data = json_decode($response, true);
    if (!$data || !is_array($data) || empty($data)) {
        $countryCache[$countryName] = [
            'shortName' => strtoupper(substr($countryName, 0, 2)),
            'flag' => '🏳️',
            'phoneCode' => '+000'
        ];
        return $countryCache[$countryName];
    }

    $country = $data[0];
    $countryInfo = [
        'shortName' => $country['cca2'] ?? strtoupper(substr($countryName, 0, 2)),
        'flag' => $country['flag'] ?? '🏳️',
        'phoneCode' => isset($country['idd']['root'], $country['idd']['suffixes'][0])
            ? $country['idd']['root'] . $country['idd']['suffixes'][0]
            : '+000'
    ];

    $countryCache[$countryName] = $countryInfo;
    return $countryInfo;
}

function streamSessions()
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    // Keep the script running even if the client disconnects
    ignore_user_abort(true);
    set_time_limit(0);

    while (true) {
        if (connection_aborted()) {
            break;
        }

        clearstatcache();
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
        }

        // Always send sessions_update
        echo "data: " . json_encode([
            'type' => 'sessions_update',
            'data' => $sessions,
            'timestamp' => time()
        ]) . "\n\n";

        // Then send heartbeat
        echo "data: " . json_encode([
            'type' => 'heartbeat',
            'timestamp' => time()
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
                'total_events' => array_sum(array_map(fn($s) => count($s['logs'] ?? []), $sessions))
            ] : null
        ]);
        break;
}
