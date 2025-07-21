<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function generateId()
{
    return bin2hex(random_bytes(13)); // 26 character hex string
}

function sendResponse($success, $message = '', $admins = [], $stats = [], $total = 0, $page = 1, $totalPages = 1)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'admins' => $admins,
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
        case 'getAdmins':
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = trim($_GET['search'] ?? '');
            $sortBy = $_GET['sortBy'] ?? 'last_login';
            $sortOrder = $_GET['sortOrder'] ?? 'DESC';

            $whereConditions = ["1=1"];
            $params = [];

            if ($search) {
                $whereConditions[] = "(username LIKE ? OR email LIKE ? OR phone LIKE ? OR 
                                   first_name LIKE ? OR last_name LIKE ?)";
                $searchParam = "%$search%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            // Valid sort columns
            $validSortColumns = [
                'last_login' => 'last_login',
                'created_at' => 'created_at',
                'username' => 'username',
                'email' => 'email',
                'role' => 'role'
            ];

            $sortColumn = $validSortColumns[$sortBy] ?? 'last_login';
            $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

            // Base query for admin users
            $baseQuery = "
                SELECT 
                    id,
                    username,
                    first_name,
                    last_name,
                    email,
                    phone,
                    role,
                    status,
                    created_at,
                    last_login,
                    current_login
                FROM admin_users
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
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get statistics
            $statsQuery = "
                SELECT 
                    COUNT(*) as totalAdmins,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as activeAdmins,
                    SUM(CASE WHEN role = 'super_admin' THEN 1 ELSE 0 END) as superAdmins,
                    SUM(CASE WHEN role = 'editor' THEN 1 ELSE 0 END) as editors
                FROM admin_users
            ";

            $stmt = $pdo->query($statsQuery);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalPages = ceil($totalRecords / $limit);

            sendResponse(true, 'Admin users loaded successfully', $admins, $stats, $totalRecords, $page, $totalPages);
            break;

        case 'getAdminDetails':
            $adminId = $_GET['id'] ?? '';

            if (!$adminId) {
                throw new Exception('Admin ID is required');
            }

            // Get admin details
            $stmt = $pdo->prepare("
                SELECT *
                FROM admin_users
                WHERE id = ?
            ");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                throw new Exception('Admin user not found');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Admin details loaded successfully',
                'admin' => $admin,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            exit;

        case 'createAdmin':
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$username) {
                throw new Exception('Username is required');
            }

            if (!$email) {
                throw new Exception('Email is required');
            }

            if (!$phone) {
                throw new Exception('Phone is required');
            }

            if (!$role) {
                throw new Exception('Role is required');
            }

            if (!$password) {
                throw new Exception('Password is required');
            }

            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters long');
            }

            // Validate phone format
            if (!preg_match('/^\+256[0-9]{9}$/', $phone)) {
                throw new Exception('Phone must be in format +256XXXXXXXXX');
            }

            // Check if username exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }

            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists');
            }

            // Check if phone exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = ?");
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                throw new Exception('Phone number already exists');
            }

            $adminId = generateId();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO admin_users 
                (id, username, first_name, last_name, email, phone, role, password, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
            ");

            $result = $stmt->execute([
                $adminId,
                $username,
                $firstName,
                $lastName,
                $email,
                $phone,
                $role,
                $hashedPassword
            ]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user created successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Failed to create admin user');
            }
            exit;

        case 'updateAdmin':
            $adminId = $_POST['id'] ?? '';
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$adminId) {
                throw new Exception('Admin ID is required');
            }

            if (!$username) {
                throw new Exception('Username is required');
            }

            if (!$email) {
                throw new Exception('Email is required');
            }

            if (!$role) {
                throw new Exception('Role is required');
            }

            // Validate phone format if provided
            if ($phone && !preg_match('/^\+256[0-9]{9}$/', $phone)) {
                throw new Exception('Phone must be in format +256XXXXXXXXX');
            }

            // Check if username exists for other users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $adminId]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }

            // Check if email exists for other users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $adminId]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists');
            }

            // Check if phone exists for other users
            if ($phone) {
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = ? AND id != ?");
                $stmt->execute([$phone, $adminId]);
                if ($stmt->fetch()) {
                    throw new Exception('Phone number already exists');
                }
            }

            // Update query
            if ($password) {
                if (strlen($password) < 6) {
                    throw new Exception('Password must be at least 6 characters long');
                }
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE admin_users 
                    SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ?, role = ?, password = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$username, $firstName, $lastName, $email, $phone, $role, $hashedPassword, $adminId]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE admin_users 
                    SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ?, role = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $result = $stmt->execute([$username, $firstName, $lastName, $email, $phone, $role, $adminId]);
            }

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user updated successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Admin user not found or no changes made');
            }
            exit;

        case 'suspend':
            $id = $_GET['id'] ?? '';

            if (!$id) {
                throw new Exception('Admin ID is required');
            }

            $stmt = $pdo->prepare("UPDATE admin_users SET status = 'suspended', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user suspended successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Admin user not found');
            }
            exit;

        case 'activate':
            $id = $_GET['id'] ?? '';

            if (!$id) {
                throw new Exception('Admin ID is required');
            }

            $stmt = $pdo->prepare("UPDATE admin_users SET status = 'active', updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user activated successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Admin user not found');
            }
            exit;

        case 'deleteAdmin':
            $id = $_GET['id'] ?? '';

            if (!$id) {
                throw new Exception('Admin ID is required');
            }

            $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Admin user deleted successfully',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                throw new Exception('Admin user not found');
            }
            exit;

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'admins' => [],
        'stats' => [],
        'total' => 0,
        'page' => 1,
        'totalPages' => 1,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
?>