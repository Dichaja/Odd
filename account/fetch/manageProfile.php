<?php
require_once __DIR__ . '/../../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in']) {
    header('Location: ' . BASE_URL);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user']['user_id'];

// Get action from request
$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'getUserDetails':
            try {
                // Enable detailed error logging for development
                ini_set('display_errors', 1);
                error_reporting(E_ALL);

                // Log the user ID for debugging
                error_log("Fetching user details for ID: " . $userId);

                // Fetch user details from database - handle UUID correctly if necessary
                $stmt = $pdo->prepare("SELECT 
                    username, 
                    email, 
                    phone, 
                    first_name, 
                    last_name, 
                    status,
                    profile_pic_url,
                    DATE_FORMAT(current_login, '%Y-%m-%d %H:%i:%s') as current_login,
                    DATE_FORMAT(last_login, '%Y-%m-%d %H:%i:%s') as last_login,
                    DATE_FORMAT(created_at, '%Y-%m-%d') as created_at
                    FROM zzimba_users 
                    WHERE id = ?");

                $stmt->execute([$userId]);

                if ($stmt->rowCount() === 0) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }

                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $user]);
            } catch (Exception $e) {
                error_log("Error in getUserDetails: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            }
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

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            // Validate phone (using the pattern from handleAuth.php)
            if (!preg_match('/^\+[0-9]{10,15}$/', $phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Use format: +256XXXXXXXXX']);
                break;
            }

            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered to another account']);
                break;
            }

            // Check if phone is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE phone = ? AND id != ?");
            $stmt->execute([$phone, $userId]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered to another account']);
                break;
            }

            // Update user profile
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE zzimba_users SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                updated_at = ? 
                WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $email, $phone, $now, $userId]);

            // Update session data
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

            // Validate password strength
            if (!(strlen($newPassword) >= 8 &&
                preg_match('/[A-Z]/', $newPassword) &&
                preg_match('/[a-z]/', $newPassword) &&
                preg_match('/[0-9]/', $newPassword) &&
                preg_match('/[^A-Za-z0-9]/', $newPassword))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM zzimba_users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!password_verify($currentPassword, $user['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                break;
            }

            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE zzimba_users SET password = ?, updated_at = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $now, $userId]);

            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    error_log("Error in manageProfile.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
