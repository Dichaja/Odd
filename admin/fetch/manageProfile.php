<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function sendResponse($success, $message = '', $profile = null)
{
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'profile' => $profile,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

try {
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['username'])) {
        throw new Exception('User not authenticated');
    }

    $userName = $_SESSION['user']['username'];

    if (!isset($_POST['action']) && !isset($_GET['action'])) {
        throw new Exception('Action parameter is required');
    }

    $action = $_POST['action'] ?? $_GET['action'];

    switch ($action) {
        case 'getProfile':
            $stmt = $pdo->prepare("
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
                    updated_at,
                    current_login,
                    last_login
                FROM admin_users
                WHERE username = ?
            ");
            $stmt->execute([$userName]);
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$profile) {
                throw new Exception('Profile not found');
            }

            sendResponse(true, 'Profile loaded successfully', $profile);

        case 'updateProfile':
            $username = trim($_POST['username'] ?? '');
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');

            if (!$username) {
                throw new Exception('Username is required');
            }

            if (!$email) {
                throw new Exception('Email is required');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            if ($phone && !preg_match('/^\+256[0-9]{9}$/', $phone)) {
                throw new Exception('Phone must be in format +256XXXXXXXXX');
            }

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([$userName]);
            $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentUser) {
                throw new Exception('Current user not found');
            }

            $userId = $currentUser['id'];

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists');
            }

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                throw new Exception('Email already exists');
            }

            if ($phone) {
                $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = ? AND id != ?");
                $stmt->execute([$phone, $userId]);
                if ($stmt->fetch()) {
                    throw new Exception('Phone number already exists');
                }
            }

            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET username = ?, first_name = ?, last_name = ?, email = ?, phone = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$username, $firstName, $lastName, $email, $phone, $userId]);

            if ($stmt->rowCount() > 0) {
                if ($userName !== $username) {
                    $_SESSION['user']['username'] = $username;
                }

                sendResponse(true, 'Profile updated successfully');
            } else {
                throw new Exception('No changes made or profile not found');
            }

        case 'changePassword':
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';

            if (!$currentPassword) {
                throw new Exception('Current password is required');
            }

            if (!$newPassword) {
                throw new Exception('New password is required');
            }

            if (strlen($newPassword) < 6) {
                throw new Exception('New password must be at least 6 characters long');
            }

            $stmt = $pdo->prepare("SELECT id, password FROM admin_users WHERE username = ?");
            $stmt->execute([$userName]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found');
            }

            if (!password_verify($currentPassword, $user['password'])) {
                throw new Exception('Current password is incorrect');
            }

            if (password_verify($newPassword, $user['password'])) {
                throw new Exception('New password must be different from current password');
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                UPDATE admin_users 
                SET password = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $result = $stmt->execute([$hashedPassword, $user['id']]);

            if ($stmt->rowCount() > 0) {
                sendResponse(true, 'Password changed successfully');
            } else {
                throw new Exception('Failed to update password');
            }

        default:
            throw new Exception('Invalid action specified');
    }

} catch (Exception $e) {
    sendResponse(false, $e->getMessage());
}
?>