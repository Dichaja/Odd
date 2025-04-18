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
        case 'getUsers':
            $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['uuid_id'] = $user['id'];
                unset($user['id']);
            }

            echo json_encode(['success' => true, 'users' => $users]);
            break;

        case 'getUser':
            $id = $_GET['id'] ?? '';

            if ($id === '') {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['success' => false, 'error' => 'User not found']);
                exit;
            }

            $user['uuid_id'] = $id;
            unset($user['id']);

            echo json_encode(['success' => true, 'data' => $user]);
            break;

        case 'createUser':
            $data = json_decode(file_get_contents('php://input'), true);

            $username = $data['username'] ?? '';
            $email    = $data['email']    ?? '';
            $phone    = $data['phone']    ?? '';
            $role     = $data['role']     ?? '';
            $password = $data['password'] ?? '';
            $status   = $data['status']   ?? 'active';

            if (
                $username === '' ||
                $email === '' ||
                $role === '' ||
                $password === ''
            ) {
                echo json_encode(['success' => false, 'error' => 'All fields are required']);
                exit;
            }

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?"
            );
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit;
            }

            $id             = generateUlid();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $now            = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                "INSERT INTO admin_users
                 (id, username, email, phone, password, role, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $result = $stmt->execute([
                $id,
                $username,
                $email,
                $phone,
                $hashedPassword,
                $role,
                $status,
                $now,
                $now
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User created successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create user']);
            }
            break;

        case 'updateUser':
            $data = json_decode(file_get_contents('php://input'), true);

            $id       = $data['id']       ?? '';
            $username = $data['username'] ?? '';
            $email    = $data['email']    ?? '';
            $phone    = $data['phone']    ?? '';
            $role     = $data['role']     ?? '';
            $status   = $data['status']   ?? '';
            $password = $data['password'] ?? '';

            if (
                $id === '' ||
                $username === '' ||
                $email === '' ||
                $role === '' ||
                $status === ''
            ) {
                echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
                exit;
            }

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM admin_users
                 WHERE (username = ? OR email = ?) AND id != ?"
            );
            $stmt->execute([$username, $email, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit;
            }

            $now = date('Y-m-d H:i:s');

            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare(
                    "UPDATE admin_users
                     SET username = ?, email = ?, phone = ?, password = ?, role = ?, status = ?, updated_at = ?
                     WHERE id = ?"
                );
                $result = $stmt->execute([
                    $username,
                    $email,
                    $phone,
                    $hashedPassword,
                    $role,
                    $status,
                    $now,
                    $id
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE admin_users
                     SET username = ?, email = ?, phone = ?, role = ?, status = ?, updated_at = ?
                     WHERE id = ?"
                );
                $result = $stmt->execute([
                    $username,
                    $email,
                    $phone,
                    $role,
                    $status,
                    $now,
                    $id
                ]);
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update user']);
            }
            break;

        case 'deleteUser':
            $id = $_POST['id'] ?? '';

            if ($id === '') {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageUsers.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
