<?php
ob_start();

// Set error reporting to log errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

// Ensure we're sending JSON response
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Session expired', 'session_expired' => true]);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getStats':
            // Get user statistics
            $stats = [];

            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM zzimba_users");
            $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Active users
            $stmt = $pdo->query("SELECT COUNT(*) as active FROM zzimba_users WHERE status = 'active'");
            $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

            // Inactive users
            $stmt = $pdo->query("SELECT COUNT(*) as inactive FROM zzimba_users WHERE status = 'inactive' OR status = 'suspended'");
            $stats['inactive_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

            // New users in last 30 days
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $stmt = $pdo->prepare("SELECT COUNT(*) as new_users FROM zzimba_users WHERE created_at >= ?");
            $stmt->execute([$thirtyDaysAgo]);
            $stats['new_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];

            // Calculate percentage changes (mock data for now)
            $stats['total_change'] = 8;
            $stats['active_change'] = 12;
            $stats['inactive_change'] = 3;
            $stats['new_change'] = 24;

            echo json_encode(['success' => true, 'stats' => $stats]);
            break;

        case 'getUsers':
            // Get sorting parameters
            $sort = $_GET['sort'] ?? 'created_at';
            $order = $_GET['order'] ?? 'desc';
            $search = $_GET['search'] ?? '';
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $offset = ($page - 1) * $limit;

            // Validate sort field
            $allowedSortFields = ['username', 'email', 'created_at', 'current_login', 'status'];
            if (!in_array($sort, $allowedSortFields)) {
                $sort = 'created_at';
            }

            // Validate order
            $order = strtolower($order) === 'asc' ? 'ASC' : 'DESC';

            // Build query
            $query = "SELECT id, username, email, phone, status, created_at, current_login, last_login FROM zzimba_users";
            $countQuery = "SELECT COUNT(*) as total FROM zzimba_users";
            $params = [];

            // Add search condition if provided
            if (!empty($search)) {
                $searchCondition = "WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?";
                $searchParam = "%$search%";
                $query .= " $searchCondition";
                $countQuery .= " $searchCondition";
                $params = [$searchParam, $searchParam, $searchParam];
            }

            // Add sorting
            $query .= " ORDER BY $sort $order";

            // Add pagination
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            // Get total count
            $stmt = $pdo->prepare($countQuery);
            if (!empty($params) && !empty($search)) {
                $stmt->execute([$searchParam, $searchParam, $searchParam]);
            } else {
                $stmt->execute();
            }
            $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get users
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert binary IDs to UUID strings
            foreach ($users as &$user) {
                $user['uuid_id'] = binToUuid($user['id']);
                // Remove binary ID from response to avoid JSON encoding issues
                unset($user['id']);
            }

            echo json_encode([
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'total' => $totalUsers,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($totalUsers / $limit)
                ]
            ]);
            break;

        case 'getUserDetails':
            $id = $_GET['id'] ?? '';

            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            try {
                $binaryId = uuidToBin($id);
                $stmt = $pdo->prepare("SELECT * FROM zzimba_users WHERE id = ?");
                $stmt->execute([$binaryId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    echo json_encode(['success' => false, 'error' => 'User not found']);
                    exit;
                }

                // Convert binary ID to UUID string
                $user['uuid_id'] = $id;
                // Remove binary ID from response
                unset($user['id']);

                echo json_encode(['success' => true, 'data' => $user]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error fetching user: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Log the error
    error_log('Error in manageDashboard.php: ' . $e->getMessage());
    // Return a proper JSON response
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

// Flush the output buffer
ob_end_flush();
