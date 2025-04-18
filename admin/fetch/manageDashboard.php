<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) ||
    !$_SESSION['user']['logged_in'] ||
    !$_SESSION['user']['is_admin']
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expired', 'session_expired' => true]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStats':
            $stats = [];

            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM zzimba_users");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->query("SELECT COUNT(*) AS active FROM zzimba_users WHERE status = 'active'");
            $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

            $stmt = $pdo->query("SELECT COUNT(*) AS inactive FROM zzimba_users WHERE status IN ('inactive','suspended')");
            $stats['inactive_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $stmt = $pdo->prepare("SELECT COUNT(*) AS new_users FROM zzimba_users WHERE created_at >= ?");
            $stmt->execute([$thirtyDaysAgo]);
            $stats['new_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];

            $stats['total_change']    = 8;
            $stats['active_change']   = 12;
            $stats['inactive_change'] = 3;
            $stats['new_change']      = 24;

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        case 'getUsers':
            $sort   = $_GET['sort']  ?? 'created_at';
            $order  = strtolower($_GET['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $search = $_GET['search'] ?? '';
            $page   = intval($_GET['page']  ?? 1);
            $limit  = intval($_GET['limit'] ?? 10);
            $offset = ($page - 1) * $limit;

            $allowed = ['username', 'email', 'created_at', 'current_login', 'status'];
            if (!in_array($sort, $allowed)) {
                $sort = 'created_at';
            }

            $baseQuery  = "FROM zzimba_users";
            $baseCount  = "SELECT COUNT(*) AS total $baseQuery";
            $baseSelect = "SELECT id, username, email, phone, status, created_at, current_login, last_login $baseQuery";
            $params     = [];

            if ($search !== '') {
                $cond  = " WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";
                $like  = "%$search%";
                $params = [$like, $like, $like];
                $baseSelect .= $cond;
                $baseCount  .= $cond;
            }

            $baseSelect .= " ORDER BY $sort $order LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($baseCount);
            if (!empty($params) && $search !== '') {
                $stmt->execute(array_slice($params, 0, 3));
            } else {
                $stmt->execute();
            }
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $stmt = $pdo->prepare($baseSelect);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success'    => true,
                'users'      => $users,
                'pagination' => [
                    'total' => $totalUsers,
                    'page'  => $page,
                    'limit' => $limit,
                    'pages' => ceil($totalUsers / $limit)
                ]
            ]);
            break;

        case 'getUserDetails':
            $id = $_GET['id'] ?? '';
            if ($id === '') {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT * FROM zzimba_users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $user]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageDashboard.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
