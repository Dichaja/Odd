<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_REQUEST['action'] ?? '';

// --- Authorization ---
if (empty($_SESSION['user']) && empty($_SESSION['admin'])) {
    if ($action === 'stream') {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: text/event-stream');
        // SSE‐style error event
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
    // --- SSE Stream ---
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    // Release session lock so POSTs (markSeen/dismiss) can run in parallel
    session_write_close();
    set_time_limit(0);

    while (!connection_aborted()) {
        $notifications = $ns->fetchForCurrent(50, 0);
        $json = json_encode($notifications);
        echo "data: {$json}\n\n";
        @ob_flush();
        @flush();
        sleep(2);
    }
    exit;
}

// --- All other actions return JSON ---
header('Content-Type: application/json');

switch ($action) {
    case 'fetch':
        $limit = (int) ($_GET['limit'] ?? 20);
        $offset = (int) ($_GET['offset'] ?? 0);
        $data = $ns->fetchForCurrent($limit, $offset);
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'markSeen':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }
        $targetId = $_POST['target_id'] ?? '';
        if ($targetId) {
            $ns->markSeen($targetId);
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'dismiss':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }
        $targetId = $_POST['target_id'] ?? '';
        if ($targetId) {
            $ns->dismiss($targetId);
        }
        echo json_encode(['status' => 'success']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
