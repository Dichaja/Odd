<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function sendResponse($success, $message = '', $users = [], $stats = [], $total = 0, $page = 1, $totalPages = 1)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'users' => $users,
        'stats' => $stats,
        'total' => $total,
        'page' => $page,
        'totalPages' => $totalPages,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

try {
    if (!isset($_POST['action']) && !isset($_GET['action'])) {
        throw new Exception('Action parameter is required');
    }

    $action = $_POST['action'] ?? $_GET['action'];

    switch ($action) {
        case 'getUsers':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = trim($_GET['search'] ?? '');
            $sortBy = $_GET['sortBy'] ?? 'current_login';
            $sortOrder = $_GET['sortOrder'] ?? 'DESC';

            $whereConditions = ["u.status != 'deleted'"];
            $params = [];

            if ($search) {
                $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR 
                                   u.first_name LIKE ? OR u.last_name LIKE ?)";
                $searchParam = "%$search%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            // Valid sort columns
            $validSortColumns = [
                'current_login' => 'u.current_login',
                'created_at' => 'u.created_at',
                'username' => 'u.username',
                'email' => 'u.email',
                'stores_owned' => 'stores_owned',
                'stores_managed' => 'stores_managed'
            ];

            $sortColumn = $validSortColumns[$sortBy] ?? 'u.current_login';
            $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

            // Base query for users with store counts
            $baseQuery = "
                SELECT 
                    u.id,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.status,
                    u.created_at,
                    u.current_login,
                    COALESCE(store_counts.stores_owned, 0) as stores_owned,
                    COALESCE(manager_counts.stores_managed, 0) as stores_managed
                FROM zzimba_users u
                LEFT JOIN (
                    SELECT owner_id, COUNT(*) as stores_owned
                    FROM vendor_stores 
                    WHERE status != 'deleted'
                    GROUP BY owner_id
                ) store_counts ON u.id = store_counts.owner_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as stores_managed
                    FROM store_managers 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) manager_counts ON u.id = manager_counts.user_id
                WHERE " . implode(' AND ', $whereConditions);

            // Get total count
            $countQuery = "SELECT COUNT(*) FROM ($baseQuery) as count_table";
            $stmt = $pdo->prepare($countQuery);
            $stmt->execute($params);
            $totalRecords = $stmt->fetchColumn();

            // Get paginated results with sorting
            $finalQuery = $baseQuery . " ORDER BY $sortColumn $sortOrder LIMIT $limit OFFSET $offset";
            $stmt = $pdo->prepare($finalQuery);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get statistics
            $statsQuery = "
                SELECT 
                    COUNT(*) as totalUsers,
                    SUM(CASE WHEN u.status = 'active' THEN 1 ELSE 0 END) as activeUsers,
                    SUM(CASE WHEN store_counts.stores_owned > 0 THEN 1 ELSE 0 END) as storeOwners,
                    SUM(CASE WHEN manager_counts.stores_managed > 0 THEN 1 ELSE 0 END) as storeManagers
                FROM zzimba_users u
                LEFT JOIN (
                    SELECT owner_id, COUNT(*) as stores_owned
                    FROM vendor_stores 
                    WHERE status != 'deleted'
                    GROUP BY owner_id
                ) store_counts ON u.id = store_counts.owner_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as stores_managed
                    FROM store_managers 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) manager_counts ON u.id = manager_counts.user_id
                WHERE u.status != 'deleted'
            ";

            $stmt = $pdo->query($statsQuery);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalPages = ceil($totalRecords / $limit);

            sendResponse(true, 'Users loaded successfully', $users, $stats, $totalRecords, $page, $totalPages);
            break;

        case 'getUserDetails':
            $userId = $_GET['id'] ?? '';

            if (!$userId) {
                throw new Exception('User ID is required');
            }

            // Get user details
            $stmt = $pdo->prepare("
                SELECT u.*, 
                       COALESCE(store_counts.stores_owned, 0) as stores_owned,
                       COALESCE(manager_counts.stores_managed, 0) as stores_managed
                FROM zzimba_users u
                LEFT JOIN (
                    SELECT owner_id, COUNT(*) as stores_owned
                    FROM vendor_stores 
                    WHERE status != 'deleted'
                    GROUP BY owner_id
                ) store_counts ON u.id = store_counts.owner_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) as stores_managed
                    FROM store_managers 
                    WHERE status = 'active'
                    GROUP BY user_id
                ) manager_counts ON u.id = manager_counts.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            // Get owned stores
            $stmt = $pdo->prepare("
                SELECT vs.id, vs.name, vs.status, vs.district, vs.region, vs.created_at
                FROM vendor_stores vs
                WHERE vs.owner_id = ? AND vs.status != 'deleted'
                ORDER BY vs.created_at DESC
            ");
            $stmt->execute([$userId]);
            $user['owned_stores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get managed stores
            $stmt = $pdo->prepare("
                SELECT sm.role, sm.status, sm.created_at, vs.name as store_name
                FROM store_managers sm
                JOIN vendor_stores vs ON sm.store_id = vs.id
                WHERE sm.user_id = ? AND sm.status != 'removed'
                ORDER BY sm.created_at DESC
            ");
            $stmt->execute([$userId]);
            $user['managed_stores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'User details loaded successfully',
                'user' => $user,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;

        case 'updateUser':
            $userId = $_POST['id'] ?? '';
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (!$userId) {
                throw new Exception('User ID is required');
            }

            if (!$username) {
                throw new Exception('Username is required');
            }

            if (!$email) {
                throw new Exception('Email is required');
            }

            // Check if username exists for other users
            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }

            // Check if email exists for other users
            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists');
            }

            $stmt = $pdo->prepare("
                UPDATE zzimba_users 
                SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$username, $firstName, $lastName, $email, $phone, $userId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('User not found or no changes made');
            }
            exit;

        case 'suspend':
            $id = $_GET['id'] ?? '';

            if (!$id) {
                throw new Exception('User ID is required');
            }

            $stmt = $pdo->prepare("UPDATE zzimba_users SET status = 'suspended', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User suspended successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('User not found');
            }
            exit;

        case 'activate':
            $id = $_GET['id'] ?? '';

            if (!$id) {
                throw new Exception('User ID is required');
            }

            $stmt = $pdo->prepare("UPDATE zzimba_users SET status = 'active', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'User activated successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('User not found');
            }
            exit;

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'users' => [],
        'stats' => [],
        'total' => 0,
        'page' => 1,
        'totalPages' => 1,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
?>