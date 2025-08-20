<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../mail/Mailer.php';
require_once __DIR__ . '/../../sms/SMS.php';
require_once __DIR__ . '/../../lib/NotificationService.php';

use ZzimbaOnline\Mail\Mailer;

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

function generateOTP(int $length = 6): string
{
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $otp;
}

function isValidEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone(string $phone): bool
{
    return (bool) preg_match('/^\+256[0-9]{9}$/', $phone);
}

function isStrongPassword(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password)
        && preg_match('/[^A-Za-z0-9]/', $password);
}

function createOTP(string $type, string $account, PDO $pdo): string
{
    $stmt = $pdo->prepare('DELETE FROM otp_verifications WHERE account = :account AND type = :type');
    $stmt->execute([':account' => $account, ':type' => $type]);

    $otp = generateOTP();
    $hashedOTP = password_hash($otp, PASSWORD_DEFAULT);
    $id = generateUlid();
    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
    $expires = (new DateTime('now +10 minutes', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        'INSERT INTO otp_verifications (id, type, account, otp, created_at, expires_at)
     VALUES (:id, :type, :account, :otp, :created_at, :expires_at)'
    );
    $stmt->execute([
        ':id' => $id,
        ':type' => $type,
        ':account' => $account,
        ':otp' => $hashedOTP,
        ':created_at' => $now,
        ':expires_at' => $expires
    ]);

    return $otp;
}

function verifyOTP(string $type, string $account, string $otp, PDO $pdo): bool
{
    $stmt = $pdo->prepare(
        'SELECT otp, expires_at FROM otp_verifications WHERE account = :account AND type = :type'
    );
    $stmt->execute([':account' => $account, ':type' => $type]);

    if ($stmt->rowCount() === 0) {
        return false;
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $expires = new DateTime($row['expires_at'], new DateTimeZone('+03:00'));
    $now = new DateTime('now', new DateTimeZone('+03:00'));

    if ($now > $expires) {
        return false;
    }

    if (!password_verify($otp, $row['otp'])) {
        return false;
    }

    $stmt = $pdo->prepare('DELETE FROM otp_verifications WHERE account = :account AND type = :type');
    $stmt->execute([':account' => $account, ':type' => $type]);

    return true;
}

function sendEmailOTP(string $email, string $otp, string $purpose = 'verification'): bool
{
    $subject = 'Email Verification Code - Zzimba Online';
    $message = $purpose === 'existing' ?
        'Please use the verification code below to verify your current email address:' :
        'Please use the verification code below to verify your new email address:';

    $content = '
    <div style="text-align:center;padding:20px 0;">
        <h2>Email Verification</h2>
        <p>' . $message . '</p>
        <div style="margin:30px auto;padding:10px;background-color:#f5f5f5;border-radius:5px;width:200px;text-align:center;">
            <h1 style="letter-spacing:5px;font-size:32px;margin:0;">' . $otp . '</h1>
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this change, please ignore this email.</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendSmsOTP(string $phone, string $otp, string $purpose = 'verification'): bool
{
    try {
        $message = $purpose === 'existing' ?
            "Your Zzimba Online verification code for current phone is: $otp. This code will expire in 10 minutes." :
            "Your Zzimba Online verification code for new phone is: $otp. This code will expire in 10 minutes.";
        $result = SMS::send($phone, $message);
        return isset($result['success']) && $result['success'] === true;
    } catch (Exception $e) {
        error_log('SMS OTP Error: ' . $e->getMessage());
        return false;
    }
}

try {
    $ns = new NotificationService($pdo);
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($action) {
        case 'getUserDetails':
            getUserDetails($pdo, $userId);
            break;

        case 'updateNames':
            updateNames($pdo, $ns, $userId, $username, $data);
            break;

        case 'sendExistingEmailOTP':
            sendExistingEmailOTP($pdo, $userId);
            break;

        case 'verifyExistingEmailOTP':
            verifyExistingEmailOTP($pdo, $userId, $data);
            break;

        case 'sendNewEmailOTP':
            sendNewEmailOTP($pdo, $userId, $data);
            break;

        case 'verifyNewEmailOTP':
            verifyNewEmailOTPAndUpdate($pdo, $ns, $userId, $username, $data);
            break;

        case 'sendExistingPhoneOTP':
            sendExistingPhoneOTP($pdo, $userId);
            break;

        case 'verifyExistingPhoneOTP':
            verifyExistingPhoneOTP($pdo, $userId, $data);
            break;

        case 'sendNewPhoneOTP':
            sendNewPhoneOTP($pdo, $userId, $data);
            break;

        case 'verifyNewPhoneOTP':
            verifyNewPhoneOTPAndUpdate($pdo, $ns, $userId, $username, $data);
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

function updateNames(PDO $pdo, NotificationService $ns, string $userId, string $username, array $data): void
{
    if (!isset($data['first_name'], $data['last_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $firstName = trim($data['first_name']);
    $lastName = trim($data['last_name']);

    if (empty($firstName)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name is required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT first_name, last_name FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current['first_name'] === $firstName && $current['last_name'] === $lastName) {
        echo json_encode(['success' => true, 'message' => 'No changes made']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        UPDATE zzimba_users
        SET first_name = :first_name,
            last_name = :last_name,
            updated_at = :updated_at
        WHERE id = :user_id
    ");
    $stmt->execute([
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':updated_at' => $now,
        ':user_id' => $userId
    ]);

    $oldNames = trim($current['first_name'] . ' ' . $current['last_name']);
    $newNames = trim($firstName . ' $lastName');

    $message = "$username updated their name from '$oldNames' to '$newNames'";
    $ns->create(
        'info',
        'User Name Updated',
        [
            ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
        ],
        null,
        'normal',
        $userId
    );

    echo json_encode(['success' => true, 'message' => 'Names updated successfully']);
}

function sendExistingEmailOTP(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("SELECT email FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($user['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No existing email to verify']);
        return;
    }

    try {
        $otp = createOTP('email', $user['email'], $pdo);
        if (sendEmailOTP($user['email'], $otp, 'existing')) {
            echo json_encode(['success' => true, 'message' => 'Verification code sent to your current email']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
        }
    } catch (Exception $e) {
        error_log('Email verification error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
    }
}

function verifyExistingEmailOTP(PDO $pdo, string $userId, array $data): void
{
    if (!isset($data['otp'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP is required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT email FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!verifyOTP('email', $user['email'], $data['otp'], $pdo)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
        return;
    }

    $_SESSION['email_verified'] = true;
    echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
}

function sendNewEmailOTP(PDO $pdo, string $userId, array $data): void
{
    if (!isset($data['new_email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New email is required']);
        return;
    }

    $newEmail = trim($data['new_email']);

    if (!isValidEmail($newEmail)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    $stmt = $pdo->prepare("SELECT email FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user['email']) && empty($_SESSION['email_verified'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please verify your existing email first']);
        return;
    }

    if ($user['email'] === $newEmail) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New email is the same as current email']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = :email AND id != :user_id");
    $stmt->execute([':email' => $newEmail, ':user_id' => $userId]);
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email is already registered to another account']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
    $stmt->execute([':email' => $newEmail]);
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email is already registered to another account']);
        return;
    }

    try {
        $otp = createOTP('email', $newEmail, $pdo);
        if (sendEmailOTP($newEmail, $otp, 'new')) {
            $_SESSION['pending_email_change'] = $newEmail;
            echo json_encode(['success' => true, 'message' => 'Verification code sent to your new email']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
        }
    } catch (Exception $e) {
        error_log('Email verification error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
    }
}

function verifyNewEmailOTPAndUpdate(PDO $pdo, NotificationService $ns, string $userId, string $username, array $data): void
{
    if (!isset($data['otp'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP is required']);
        return;
    }

    if (empty($_SESSION['pending_email_change'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No pending email change found']);
        return;
    }

    $newEmail = $_SESSION['pending_email_change'];

    if (!verifyOTP('email', $newEmail, $data['otp'], $pdo)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
        return;
    }

    $stmt = $pdo->prepare("SELECT email FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE zzimba_users SET email = :email, updated_at = :updated_at WHERE id = :user_id");
    $stmt->execute([':email' => $newEmail, ':updated_at' => $now, ':user_id' => $userId]);

    $oldEmail = $user['email'] ?: 'Not set';
    $message = "$username changed their email from '$oldEmail' to '$newEmail'";
    $ns->create(
        'info',
        'User Email Updated',
        [
            ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
        ],
        null,
        'normal',
        $userId
    );

    $_SESSION['user']['email'] = $newEmail;
    unset($_SESSION['pending_email_change'], $_SESSION['email_verified']);

    echo json_encode(['success' => true, 'message' => 'Email updated successfully']);
}

function sendExistingPhoneOTP(PDO $pdo, string $userId): void
{
    $stmt = $pdo->prepare("SELECT phone FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($user['phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No existing phone to verify']);
        return;
    }

    try {
        $otp = createOTP('phone', $user['phone'], $pdo);
        if (sendSmsOTP($user['phone'], $otp, 'existing')) {
            echo json_encode(['success' => true, 'message' => 'Verification code sent to your current phone']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
        }
    } catch (Exception $e) {
        error_log('Phone verification error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
    }
}

function verifyExistingPhoneOTP(PDO $pdo, string $userId, array $data): void
{
    if (!isset($data['otp'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP is required']);
        return;
    }

    $stmt = $pdo->prepare("SELECT phone FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!verifyOTP('phone', $user['phone'], $data['otp'], $pdo)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
        return;
    }

    $_SESSION['phone_verified'] = true;
    echo json_encode(['success' => true, 'message' => 'Phone verified successfully']);
}

function sendNewPhoneOTP(PDO $pdo, string $userId, array $data): void
{
    if (!isset($data['new_phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New phone number is required']);
        return;
    }

    $newPhone = trim($data['new_phone']);

    if (!isValidPhone($newPhone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Must be +256XXXXXXXXX']);
        return;
    }

    $stmt = $pdo->prepare("SELECT phone FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!empty($user['phone']) && empty($_SESSION['phone_verified'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please verify your existing phone first']);
        return;
    }

    if ($user['phone'] === $newPhone) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'New phone number is the same as current phone number']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE phone = :phone AND id != :user_id");
    $stmt->execute([':phone' => $newPhone, ':user_id' => $userId]);
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Phone number is already registered to another account']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = :phone");
    $stmt->execute([':phone' => $newPhone]);
    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Phone number is already registered to another account']);
        return;
    }

    try {
        $otp = createOTP('phone', $newPhone, $pdo);
        if (sendSmsOTP($newPhone, $otp, 'new')) {
            $_SESSION['pending_phone_change'] = $newPhone;
            echo json_encode(['success' => true, 'message' => 'Verification code sent to your new phone']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
        }
    } catch (Exception $e) {
        error_log('Phone verification error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send verification code']);
    }
}

function verifyNewPhoneOTPAndUpdate(PDO $pdo, NotificationService $ns, string $userId, string $username, array $data): void
{
    if (!isset($data['otp'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'OTP is required']);
        return;
    }

    if (empty($_SESSION['pending_phone_change'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No pending phone change found']);
        return;
    }

    $newPhone = $_SESSION['pending_phone_change'];

    if (!verifyOTP('phone', $newPhone, $data['otp'], $pdo)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
        return;
    }

    $stmt = $pdo->prepare("SELECT phone FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE zzimba_users SET phone = :phone, updated_at = :updated_at WHERE id = :user_id");
    $stmt->execute([':phone' => $newPhone, ':updated_at' => $now, ':user_id' => $userId]);

    $oldPhone = $user['phone'] ?: 'Not set';
    $message = "$username changed their phone from '$oldPhone' to '$newPhone'";
    $ns->create(
        'info',
        'User Phone Updated',
        [
            ['type' => 'admin', 'id' => 'admin-global', 'message' => $message]
        ],
        null,
        'normal',
        $userId
    );

    $_SESSION['user']['phone'] = $newPhone;
    unset($_SESSION['pending_phone_change'], $_SESSION['phone_verified']);

    echo json_encode(['success' => true, 'message' => 'Phone number updated successfully']);
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

    if (!isStrongPassword($new)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character'
        ]);
        return;
    }

    $stmt = $pdo->prepare("SELECT password FROM zzimba_users WHERE id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current, $row['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        return;
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);
    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("UPDATE zzimba_users SET password = :password, updated_at = :updated_at WHERE id = :user_id");
    $stmt->execute([':password' => $hashed, ':updated_at' => $now, ':user_id' => $userId]);

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
    try {
        $pdo->beginTransaction();

        $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

        // Prefix core identity fields with "delete." and mark status deleted
        // Use COALESCE to avoid NULL -> NULL in CONCAT (which yields NULL)
        $stmt = $pdo->prepare("
            UPDATE zzimba_users
            SET
                username    = CONCAT('delete.', COALESCE(username, '')),
                first_name  = CASE WHEN first_name IS NULL OR first_name = '' THEN 'delete.' ELSE CONCAT('delete.', first_name) END,
                last_name   = CASE WHEN last_name  IS NULL OR last_name  = '' THEN 'delete.' ELSE CONCAT('delete.', last_name)  END,
                email       = CASE WHEN email      IS NULL OR email      = '' THEN 'delete.' ELSE CONCAT('delete.', email)      END,
                phone       = CASE WHEN phone      IS NULL OR phone      = '' THEN 'delete.' ELSE CONCAT('delete.', phone)      END,
                status      = 'deleted',
                updated_at  = :updated_at
            WHERE id = :user_id
        ");
        $stmt->execute([':updated_at' => $now, ':user_id' => $userId]);

        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }

        $pdo->commit();

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

        session_unset();
        session_destroy();

        echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
    }
}
?>