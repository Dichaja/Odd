<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/NotificationService.php';

header('Content-Type: application/json');

// Ensure session is running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication check
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Your session has expired due to inactivity. Please log in again.',
        'session_expired' => true
    ]);
    exit;
}

$userId = $_SESSION['user']['user_id'];
$username = $_SESSION['user']['username'];

date_default_timezone_set('Africa/Kampala');

try {
    // instantiate notification service once
    $ns = new NotificationService($pdo);

    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {

        case 'getUserDetails':
            getUserDetails($pdo, $userId);
            break;

        case 'updateProfile':
            updateProfile($pdo, $ns, $userId, $username, $data);
            break;

        case 'changePassword':
            changePassword($pdo, $ns, $userId, $username, $data);
            break;

        case 'deleteAccount':
            deleteAccount($pdo, $ns, $userId, $username);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}


function getUserDetails(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("
        SELECT
            username,
            email,
            phone,
            first_name,
            last_name,
            status,
            profile_pic_url,
            DATE_FORMAT(current_login, '%Y-%m-%d %H:%i:%s') AS current_login,
            DATE_FORMAT(last_login, '%Y-%m-%d %H:%i:%s')   AS last_login,
            DATE_FORMAT(created_at, '%Y-%m-%d')            AS created_at
        FROM zzimba_users
        WHERE id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $user]);
}


function updateProfile(PDO $pdo, NotificationService $ns, string $userId, string $username, array $data): void
{
    if (!isset($data['first_name'], $data['last_name'], $data['email'], $data['phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    // fetch current values
    $stmtCur = $pdo->prepare("
        SELECT first_name, last_name, email, phone
        FROM zzimba_users
        WHERE id = :user_id
    ");
    $stmtCur->execute([':user_id' => $userId]);
    $current = $stmtCur->fetch(PDO::FETCH_ASSOC);

    $firstName = $data['first_name'];
    $lastName = $data['last_name'];
    $email = $data['email'];
    $phone = $data['phone'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    if (!preg_match('/^\+[0-9]{10,15}$/', $phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Use format: +256XXXXXXXXX']);
        return;
    }

    // determine what changed
    $changes = [];
    if ($current['first_name'] !== $firstName || $current['last_name'] !== $lastName) {
        $oldNames = trim($current['first_name'] . ' ' . $current['last_name']);
        $newNames = trim($firstName . ' ' . $lastName);
        $changes[] = "names: '{$oldNames}' → '{$newNames}'";
    }
    if ($current['email'] !== $email) {
        $changes[] = "email: '{$current['email']}' → '{$email}'";
    }
    if ($current['phone'] !== $phone) {
        $changes[] = "phone: '{$current['phone']}' → '{$phone}'";
    }

    // perform update only if there are changes
    if (!empty($changes)) {
        $now = (new DateTime('now'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            UPDATE zzimba_users
            SET first_name = :first_name,
                last_name  = :last_name,
                email      = :email,
                phone      = :phone,
                updated_at = :updated_at
            WHERE id = :user_id
        ");
        $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':phone' => $phone,
            ':updated_at' => $now,
            ':user_id' => $userId
        ]);

        // notify admin
        $message = "$username updated their profile: " . implode(', ', $changes);
        $ns->create(
            'info',
            'User Profile Updated',
            [
                ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
            ],
            null,
            'normal',
            $userId
        );
    }

    // keep session in sync
    $_SESSION['user']['email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
}


function changePassword(PDO $pdo, NotificationService $ns, string $userId, string $username, array $data): void
{
    if (!isset($data['current_password'], $data['new_password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $current = $data['current_password'];
    $new = $data['new_password'];

    if (
        !(
            strlen($new) >= 8 &&
            preg_match('/[A-Z]/', $new) &&
            preg_match('/[a-z]/', $new) &&
            preg_match('/[0-9]/', $new) &&
            preg_match('/[^A-Za-z0-9]/', $new)
        )
    ) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
        ]);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT password
        FROM zzimba_users
        WHERE id = :user_id
    ");
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current, $row['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $now = (new DateTime('now'))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        UPDATE zzimba_users
        SET password   = :password,
            updated_at = :updated_at
        WHERE id = :user_id
    ");
    $stmt->execute([
        ':password' => $hashed,
        ':updated_at' => $now,
        ':user_id' => $userId
    ]);

    // notify admin of password change
    $message = "$username changed their password.";
    $ns->create(
        'info',
        'User Password Changed',
        [
            ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
        ],
        null,
        'high',
        $userId
    );

    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
}


function deleteAccount(PDO $pdo, NotificationService $ns, string $userId, string $username): void
{
    // record deletion
    $now = (new DateTime('now'))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        UPDATE zzimba_users
        SET status     = 'deleted',
            updated_at = :updated_at
        WHERE id = :user_id
    ");
    $stmt->execute([
        ':updated_at' => $now,
        ':user_id' => $userId
    ]);

    // notify admin
    $message = "$username deleted their account.";
    $ns->create(
        'info',
        'Account Deleted',
        [
            ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
        ],
        null,
        'high',
        $userId
    );

    // destroy user session
    session_unset();
    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
}
