<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_REQUEST['action'] ?? '';

if (empty($_SESSION['user']) && empty($_SESSION['admin'])) {
    if ($action === 'stream') {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: text/event-stream');
        echo "event: error\ndata: Unauthorized\n\n";
        exit;
    }
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$ns = new NotificationService($pdo);

if ($action === 'stream') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    session_write_close();
    $limit = (int) ($_GET['limit'] ?? 50);
    $offset = (int) ($_GET['offset'] ?? 0);
    $since = $_GET['since'] ?? null;
    $data = $ns->fetchForCurrent($limit, $offset, $since);
    $latest = !empty($data) ? max(array_column($data, 'created_at')) : ($since ?? null);
    $unread = $ns->countUnreadForCurrent();
    $payload = json_encode(['data' => $data, 'unread_count' => $unread, 'latest_ts' => $latest]);
    echo "data: {$payload}\n\n";
    @ob_flush();
    @flush();
    exit;
}

header('Content-Type: application/json');

switch ($action) {
    case 'fetch':
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = (int) ($_GET['offset'] ?? 0);
        $since = $_GET['since'] ?? null;
        $data = $ns->fetchForCurrent($limit, $offset, $since);
        $unread = $ns->countUnreadForCurrent();
        $latest = !empty($data) ? max(array_column($data, 'created_at')) : ($since ?? null);
        echo json_encode(['status' => 'success', 'data' => $data, 'unread_count' => $unread, 'latest_ts' => $latest]);
        break;

    case 'markSeen':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }
        $targetIds = $_POST['target_id'] ?? null;
        if ($targetIds) {
            $ns->markSeen($targetIds);
        }
        $unread = $ns->countUnreadForCurrent();
        echo json_encode(['status' => 'success', 'unread_count' => $unread]);
        break;

    case 'dismiss':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }
        $targetIds = $_POST['target_id'] ?? null;
        if ($targetIds) {
            $ns->dismiss($targetIds);
        }
        $unread = $ns->countUnreadForCurrent();
        echo json_encode(['status' => 'success', 'unread_count' => $unread]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
