<?php
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$userId = $_SESSION['user']['user_id'];
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'getUserDetails':
            $stmt = $pdo->prepare("SELECT 
                username, 
                email, 
                phone, 
                first_name, 
                last_name, 
                role,
                status,
                profile_pic_url,
                DATE_FORMAT(current_login, '%Y-%m-%d %H:%i:%s') as current_login,
                DATE_FORMAT(last_login, '%Y-%m-%d %H:%i:%s') as last_login,
                DATE_FORMAT(created_at, '%Y-%m-%d') as created_at
                FROM admin_users 
                WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $user]);
            break;

        case 'updateProfile':
            if (!isset($data['first_name']) || !isset($data['last_name']) || !isset($data['email']) || !isset($data['phone'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }

            $firstName = $data['first_name'];
            $lastName = $data['last_name'];
            $email = $data['email'];
            $phone = $data['phone'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            if (!preg_match('/^\+[0-9]{10,15}$/', $phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email AND id != :user_id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered to another admin account']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = :phone AND id != :user_id");
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered to another admin account']);
                break;
            }

            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE admin_users SET 
                first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                phone = :phone, 
                updated_at = :updated_at 
                WHERE id = :user_id");
            $stmt->bindParam(':first_name', $firstName);
            $stmt->bindParam(':last_name', $lastName);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':updated_at', $now);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();

            $_SESSION['user']['email'] = $email;

            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            break;

        case 'changePassword':
            if (!isset($data['current_password']) || !isset($data['new_password'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }

            $currentPassword = $data['current_password'];
            $newPassword = $data['new_password'];

            if (!(strlen($newPassword) >= 8 &&
                preg_match('/[A-Z]/', $newPassword) &&
                preg_match('/[a-z]/', $newPassword) &&
                preg_match('/[0-9]/', $newPassword) &&
                preg_match('/[^A-Za-z0-9]/', $newPassword))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPassword, $user['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                break;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE admin_users SET password = :password, updated_at = :updated_at WHERE id = :user_id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':updated_at', $now);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_LOB);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
