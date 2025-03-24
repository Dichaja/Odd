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
        case 'getUsers':
            // Simple query to get all users
            $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convert binary IDs to UUID strings
            foreach ($users as &$user) {
                $user['uuid_id'] = binToUuid($user['id']);
                // Remove binary ID from response to avoid JSON encoding issues
                unset($user['id']);
            }

            echo json_encode(['success' => true, 'users' => $users]);
            break;

        case 'getUser':
            $id = $_GET['id'] ?? '';

            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            try {
                $binaryId = uuidToBin($id);
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
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

        case 'createUser':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $role = $data['role'] ?? '';
            $password = $data['password'] ?? '';
            $status = $data['status'] ?? 'active';

            if (empty($username) || empty($email) || empty($role) || empty($password)) {
                echo json_encode(['success' => false, 'error' => 'All fields are required']);
                exit;
            }

            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                exit;
            }

            // Generate UUID and hash password
            $uuid = generateUUIDv7();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');

            try {
                $binaryId = uuidToBin($uuid);
                $stmt = $pdo->prepare("INSERT INTO admin_users (id, username, email, phone, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$binaryId, $username, $email, $phone, $hashedPassword, $role, $status, $now, $now]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'User created successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to create user']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error creating user: ' . $e->getMessage()]);
            }
            break;

        case 'updateUser':
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
                exit;
            }

            $id = $data['id'] ?? '';
            $username = $data['username'] ?? '';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $role = $data['role'] ?? '';
            $status = $data['status'] ?? '';
            $password = $data['password'] ?? '';

            if (empty($id) || empty($username) || empty($email) || empty($role) || empty($status)) {
                echo json_encode(['success' => false, 'error' => 'Required fields are missing']);
                exit;
            }

            try {
                $binaryId = uuidToBin($id);

                // Check if username or email already exists for other users
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $binaryId]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    echo json_encode(['success' => false, 'error' => 'Username or email already exists']);
                    exit;
                }

                $now = date('Y-m-d H:i:s');

                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ?, phone = ?, password = ?, role = ?, status = ?, updated_at = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $phone, $hashedPassword, $role, $status, $now, $binaryId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, email = ?, phone = ?, role = ?, status = ?, updated_at = ? WHERE id = ?");
                    $result = $stmt->execute([$username, $email, $phone, $role, $status, $now, $binaryId]);
                }

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update user']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error updating user: ' . $e->getMessage()]);
            }
            break;

        case 'deleteUser':
            $id = $_POST['id'] ?? '';

            if (empty($id)) {
                echo json_encode(['success' => false, 'error' => 'User ID is required']);
                exit;
            }

            try {
                $binaryId = uuidToBin($id);
                $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                $result = $stmt->execute([$binaryId]);

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Error deleting user: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Log the error
    error_log('Error in manageUsers.php: ' . $e->getMessage());
    // Return a proper JSON response
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

// Flush the output buffer
ob_end_flush();
