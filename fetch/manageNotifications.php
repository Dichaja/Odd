<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) session_start();

$ns     = new NotificationService($pdo);
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'fetch':
        $limit  = (int)($_GET['limit']  ?? 20);
        $offset = (int)($_GET['offset'] ?? 0);
        echo json_encode(['status' => 'success', 'data' => $ns->fetchForCurrent($limit, $offset)]);
        break;

    case 'markSeen':
        if (!empty($_POST['target_id'])) $ns->markSeen($_POST['target_id']);
        echo json_encode(['status' => 'success']);
        break;

    case 'dismiss':
        if (!empty($_POST['target_id'])) $ns->dismiss($_POST['target_id']);
        echo json_encode(['status' => 'success']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
