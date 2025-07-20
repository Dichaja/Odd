<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../mail/Mailer.php';
require_once __DIR__ . '/../sms/SMS.php';
require_once __DIR__ . '/../lib/NotificationService.php';

use ZzimbaOnline\Mail\Mailer;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admin_users (
        id VARCHAR(26) PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        role ENUM('super_admin','admin','editor') NOT NULL DEFAULT 'admin',
        status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
        profile_pic_url VARCHAR(255),
        current_login DATETIME,
        last_login DATETIME,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS zzimba_users (
        id VARCHAR(26) PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
        profile_pic_url VARCHAR(255),
        current_login DATETIME,
        last_login DATETIME,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS otp_verifications (
        id VARCHAR(26) PRIMARY KEY,
        type ENUM('email','phone') NOT NULL,
        account VARCHAR(100) NOT NULL,
        otp VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        INDEX (account, type)
    )"
    );
} catch (PDOException $e) {
    error_log('Table creation error: ' . $e->getMessage());
    die(json_encode(['success' => false, 'errors' => ['Database setup failed']]));
}

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

function sendEmailOTP(string $email, string $otp): bool
{
    $subject = 'Your Verification Code - Zzimba Online';
    $content = '
    <div style="text-align:center;padding:20px 0;">
        <h2>Email Verification</h2>
        <p>Thank you for registering with Zzimba Online. Please use the verification code below to complete your registration:</p>
        <div style="margin:30px auto;padding:10px;background-color:#f5f5f5;border-radius:5px;width:200px;text-align:center;">
            <h1 style="letter-spacing:5px;font-size:32px;margin:0;">' . $otp . '</h1>
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this code, please ignore this email.</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendSmsOTP(string $phone, string $otp): bool
{
    try {
        $message = "Your Zzimba Online verification code is: $otp. This code will expire in 10 minutes.";
        $result = SMS::send($phone, $message);
        return isset($result['success']) && $result['success'] === true;
    } catch (Exception $e) {
        error_log('SMS OTP Error: ' . $e->getMessage());
        return false;
    }
}

function sendLoginOTP(string $email, string $otp): bool
{
    $subject = 'Your OTP Verification Code - Zzimba Online';
    $content = '
    <div style="text-align:center;padding:20px 0;">
        <h2>OTP Verification</h2>
        <p>Please use the following OTP to verify your account ownership and set up a new password:</p>
        <div style="margin:30px auto;padding:10px;background-color:#f5f5f5;border-radius:5px;width:200px;text-align:center;">
            <h1 style="letter-spacing:5px;font-size:32px;margin:0;">' . $otp . '</h1>
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not initiate this request, please ignore this email.</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendLoginSmsOTP(string $phone, string $otp): bool
{
    try {
        $message = "Your Zzimba Online login verification code is: $otp. This code will expire in 10 minutes.";
        $result = SMS::send($phone, $message);
        return isset($result['success']) && $result['success'] === true;
    } catch (Exception $e) {
        error_log('Login SMS OTP Error: ' . $e->getMessage());
        return false;
    }
}

function sendWelcomeEmail(string $username, string $email, string $phone): bool
{
    $subject = 'Welcome to Zzimba Online!';
    $content = '
    <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;">
        <h2 style="color:#d32f2f;text-align:center;">Welcome to Zzimba Online!</h2>
        <p>Dear ' . htmlspecialchars($username) . ',</p>
        <p>Thank you for creating an account with Zzimba Online. We\'re excited to have you join our community!</p>
        <div style="background-color:#f5f5f5;border-radius:5px;padding:15px;margin:20px 0;">
            <h3 style="margin-top:0;">Your Account Information:</h3>
            <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
            <p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>
        </div>
        <p>We recommend updating your profile information after you log in to enhance your experience with our platform.</p>
        <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendPasswordResetOTP(string $email, string $otp): bool
{
    $subject = 'Password Reset Code - Zzimba Online';
    $content = '
    <div style="text-align:center;padding:20px 0;">
        <h2>Password Reset</h2>
        <p>You have requested to reset your password. Please use the verification code below to continue:</p>
        <div style="margin:30px auto;padding:10px;background-color:#f5f5f5;border-radius:5px;width:200px;text-align:center;">
            <h1 style="letter-spacing:5px;font-size:32px;margin:0;">' . $otp . '</h1>
        </div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you did not request this code, please ignore this email or contact our support team if you believe this is unauthorized activity.</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendPasswordResetSmsOTP(string $phone, string $otp): bool
{
    try {
        $message = "Your Zzimba Online password reset code is: $otp. This code will expire in 10 minutes.";
        $result = SMS::send($phone, $message);
        return isset($result['success']) && $result['success'] === true;
    } catch (Exception $e) {
        error_log('Password Reset SMS Error: ' . $e->getMessage());
        return false;
    }
}

function sendPasswordChangedEmail(string $email, ?string $username = null): bool
{
    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
    $subject = 'Password Changed - Zzimba Online';
    $greeting = $username ? 'Dear ' . htmlspecialchars($username) . ',' : 'Hello,';
    $content = '
    <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;">
        <h2 style="color:#d32f2f;text-align:center;">Password Changed</h2>
        <p>' . $greeting . '</p>
        <p>Your password for Zzimba Online has been successfully changed on ' . $now . ' (East Africa Time).</p>
        <div style="background-color:#f5f5f5;border-radius:5px;padding:15px;margin:20px 0;">
            <p><strong>If you made this change:</strong> You can disregard this email. Your account is secure.</p>
            <p><strong>If you did NOT make this change:</strong> Please contact our support team immediately as your account may have been compromised.</p>
        </div>
        <p>For security reasons, we recommend:</p>
        <ul>
            <li>Regularly updating your password</li>
            <li>Not sharing your password with others</li>
            <li>Using unique passwords for different websites</li>
        </ul>
        <p>If you have any questions or concerns, please don\'t hesitate to contact our support team.</p>
        <p>Best regards,<br>The Zzimba Online Team</p>
    </div>';
    return Mailer::sendMail($email, $subject, $content);
}

function sendPasswordChangedSms(string $phone, ?string $username = null): bool
{
    try {
        $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
        $greeting = $username ? "Dear $username," : "Hello,";
        $message = "$greeting Your Zzimba Online password was changed on $now. If you didn't make this change, please contact support immediately.";
        $result = SMS::send($phone, $message);
        return isset($result['success']) && $result['success'] === true;
    } catch (Exception $e) {
        error_log('Password Changed SMS Error: ' . $e->getMessage());
        return false;
    }
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

function maskEmail($email)
{
    if (empty($email))
        return null;

    $parts = explode("@", $email);
    $name = $parts[0];
    $domain = $parts[1];

    $maskedName = substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 2, 1)) . substr($name, -1);
    return $maskedName . '@' . $domain;
}

function maskPhone($phone)
{
    if (empty($phone))
        return null;

    // Show first 4 digits and last 2 digits only
    $digits = preg_replace('/\D/', '', $phone); // Strip non-digits
    $len = strlen($digits);

    if ($len < 6)
        return str_repeat('*', $len); // Too short to reveal anything

    return substr($digits, 0, 4) . str_repeat('*', $len - 6) . substr($digits, -2);
}

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

try {
    $ns = new NotificationService($pdo);

    switch ($action) {
        case 'checkUser':
            if (!isset($data['identifier'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing identifier']));
            }

            $identifier = $data['identifier'];
            $identifierType = $data['identifierType'] ?? 'auto';

            $isEmail = isValidEmail($identifier);
            $isPhone = isValidPhone($identifier);

            $isAdmin = false;
            if (!$isEmail && !$isPhone && str_starts_with($identifier, 'Admin:')) {
                $isAdmin = true;
                $identifier = substr($identifier, 6);
            }

            if ($isAdmin) {
                $stmt = $pdo->prepare(
                    $isEmail
                    ? 'SELECT id FROM admin_users WHERE email = :identifier'
                    : 'SELECT id FROM admin_users WHERE username = :identifier'
                );
                $stmt->execute([':identifier' => $identifier]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'userType' => 'admin']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Admin user not found. Please check your credentials.']);
                }
                break;
            }

            // Determine query based on identifier type
            if ($identifierType === 'email' || $isEmail) {
                $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE email = :identifier');
            } elseif ($identifierType === 'phone' || $isPhone) {
                $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE phone = :identifier');
            } else {
                $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE username = :identifier');
            }

            $stmt->execute([':identifier' => $identifier]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'userType' => 'user']);
            } else {
                http_response_code(404);
                $errorMessage = 'User not found. Please check your credentials or register a new account.';
                if ($identifierType === 'phone' || $isPhone) {
                    $errorMessage = 'Phone number not found. Please check your number or register a new account.';
                } elseif ($identifierType === 'email' || $isEmail) {
                    $errorMessage = 'Email not found. Please check your email or register a new account.';
                }
                echo json_encode(['success' => false, 'message' => $errorMessage]);
            }
            break;

        case 'login':
            if (!isset($data['identifier'], $data['password'], $data['userType'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $identifier = $data['identifier'];
            $identifierType = $data['identifierType'] ?? 'auto';
            $password = $data['password'];
            $userType = $data['userType'];

            $isEmail = isValidEmail($identifier);
            $isPhone = isValidPhone($identifier);
            $isAdminFlag = false;

            if (!$isEmail && !$isPhone && str_starts_with($identifier, 'Admin:')) {
                $isAdminFlag = true;
                $identifier = substr($identifier, 6);
            }

            $table = ($userType === 'admin' || $isAdminFlag) ? 'admin_users' : 'zzimba_users';

            // Determine query based on identifier type
            if ($identifierType === 'email' || $isEmail) {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email, phone, last_login FROM $table WHERE email = :identifier");
            } elseif ($identifierType === 'phone' || $isPhone) {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email, phone, last_login FROM $table WHERE phone = :identifier");
            } else {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email, phone, last_login FROM $table WHERE username = :identifier");
            }

            $stmt->execute([':identifier' => $identifier]);

            if ($stmt->rowCount() === 0) {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed login attempt for non-existent user: $identifier"
                    ]
                ];
                $ns->create(
                    'login',
                    'Failed Login Attempt',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(404);
                echo json_encode(['success' => false, 'message' => ($table === 'admin_users' ? 'Admin user not found. Please check your credentials.' : 'User not found. Please check your credentials or register a new account.')]);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['status'] !== 'active') {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Login attempt for {$user['status']} account: {$user['username']}"
                    ]
                ];
                $ns->create(
                    'login',
                    'Login Attempt - Inactive Account',
                    $recipients,
                    null,
                    'high'
                );

                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Your account is ' . $user['status'] . '. Please contact support.']);
                break;
            }

            if (trim($user['password']) === '') {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'errorCode' => 'EMPTY_PASSWORD',
                    'email' => maskEmail($user['email']),
                    'phone' => maskPhone($user['phone'])
                ]);
                break;
            }

            if (!password_verify($password, $user['password'])) {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed login attempt with incorrect password for user: {$user['username']}"
                    ]
                ];
                $ns->create(
                    'login',
                    'Failed Login Attempt',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
                break;
            }

            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            $update = $pdo->prepare("UPDATE $table SET last_login = current_login, current_login = :now WHERE id = :id");
            $update->execute([':now' => $now, ':id' => $user['id']]);

            $_SESSION['user'] = [
                'logged_in' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'is_admin' => ($table === 'admin_users'),
                'last_login' => $user['last_login']
            ];

            $recipients = [
                [
                    'type' => 'admin',
                    'id' => 'admin-global',
                    'message' => "Successful login: {$user['username']} logged in at $now"
                ]
            ];
            $ns->create(
                'login',
                'Successful Login',
                $recipients,
                null,
                'low',
                $user['id']
            );

            $redirect = ($table === 'admin_users')
                ? BASE_URL . 'admin/dashboard'
                : BASE_URL . 'account/dashboard';

            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect]);
            break;

        case 'checkUsername':
            if (!isset($data['username'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing username']));
            }

            $username = $data['username'];

            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE username = :username');
            $stmt->execute([':username' => $username]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username is already taken']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE username = :username');
            $stmt->execute([':username' => $username]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username is already taken']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Username is available']);
            break;

        case 'checkEmail':
            if (!isset($data['email'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing email']));
            }

            $email = $data['email'];

            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE email = :email');
            $stmt->execute([':email' => $email]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE email = :email');
            $stmt->execute([':email' => $email]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Email is available']);
            break;

        case 'checkPhone':
            if (!isset($data['phone'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing phone number']));
            }

            $phone = $data['phone'];

            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format. Must be +256XXXXXXXXX']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM admin_users WHERE phone = :phone');
            $stmt->execute([':phone' => $phone]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE phone = :phone');
            $stmt->execute([':phone' => $phone]);

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Phone number is available']);
            break;

        case 'sendEmailOTP':
            if (!isset($data['email'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing email']));
            }

            $email = $data['email'];

            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            $otp = createOTP('email', $email, $pdo);

            if (!sendEmailOTP($email, $otp)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send verification code. Please try again.']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'OTP sent to email']);
            break;

        case 'verifyEmailOTP':
            if (!isset($data['email'], $data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $email = $data['email'];
            $otp = $data['otp'];

            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            if (!verifyOTP('email', $email, $otp, $pdo)) {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed OTP verification attempt for email: $email"
                    ]
                ];
                $ns->create(
                    'login',
                    'Failed OTP Verification',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
            break;

        case 'sendPhoneOTP':
            if (!isset($data['phone'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing phone number']));
            }

            $phone = $data['phone'];

            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            $otp = createOTP('phone', $phone, $pdo);

            if (!sendSmsOTP($phone, $otp)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send verification code. Please try again.']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'OTP sent to phone']);
            break;

        case 'verifyPhoneOTP':
            if (!isset($data['phone'], $data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $phone = $data['phone'];
            $otp = $data['otp'];

            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            if (!verifyOTP('phone', $phone, $otp, $pdo)) {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed OTP verification attempt for phone: $phone"
                    ]
                ];
                $ns->create(
                    'login',
                    'Failed OTP Verification',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Phone verified successfully']);
            break;

        case 'sendResetPhone':
            // Now require username + phone, and ensure they match
            if (!isset($data['username'], $data['phone'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing username or phone number']));
            }

            $username = $data['username'];
            $phone = $data['phone'];

            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            // Check admin_users first
            $stmt = $pdo->prepare('SELECT id, phone FROM admin_users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($adminUser) {
                if ($adminUser['phone'] !== $phone) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Phone number does not match the specified username']);
                    break;
                }
                $userId = $adminUser['id'];
            } else {
                // Check zzimba_users
                $stmt = $pdo->prepare('SELECT id, phone FROM zzimba_users WHERE username = :username');
                $stmt->execute([':username' => $username]);
                $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$zzimbaUser) {
                    $recipients = [
                        [
                            'type' => 'admin',
                            'id' => 'admin-global',
                            'message' => "Password reset attempt for non-existent user: $username"
                        ]
                    ];
                    $ns->create(
                        'password_reset',
                        'Failed Password Reset Attempt',
                        $recipients,
                        null,
                        'normal'
                    );

                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }

                if ($zzimbaUser['phone'] !== $phone) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Phone number does not match the specified username']);
                    break;
                }
                $userId = $zzimbaUser['id'];
            }

            // All good: generate OTP and send
            $otp = createOTP('phone', $phone, $pdo);

            if (!sendPasswordResetSmsOTP($phone, $otp)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send reset code. Please try again.']);
                break;
            }

            $recipients = [
                [
                    'type' => 'admin',
                    'id' => 'admin-global',
                    'message' => "Password reset initiated via phone for user: {$username}"
                ]
            ];
            $ns->create(
                'password_reset',
                'Password Reset Initiated',
                $recipients,
                null,
                'normal',
                $userId
            );

            echo json_encode(['success' => true, 'message' => 'Reset code sent to phone']);
            break;

        case 'register':
            if (!isset($data['username'], $data['email'], $data['phone'], $data['password'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $username = $data['username'];
            $email = $data['email'];
            $phone = $data['phone'];
            $password = $data['password'];

            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
                break;
            }

            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            if (!isStrongPassword($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            $stmt = $pdo->prepare('SELECT id FROM zzimba_users WHERE username = :username OR email = :email OR phone = :phone');
            $stmt->execute([':username' => $username, ':email' => $email, ':phone' => $phone]);

            if ($stmt->rowCount() > 0) {
                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed signup attempt with existing credentials: $username, $email, $phone"
                    ]
                ];
                $ns->create(
                    'signup',
                    'Failed Signup Attempt',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username, email, or phone number is already registered']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = generateUlid();
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                'INSERT INTO zzimba_users (id, username, email, phone, password, status, created_at, updated_at)
             VALUES (:id, :username, :email, :phone, :password, "active", :created_at, :updated_at)'
            );
            $stmt->execute([
                ':id' => $userId,
                ':username' => $username,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashedPassword,
                ':created_at' => $now,
                ':updated_at' => $now
            ]);

            sendWelcomeEmail($username, $email, $phone);

            $recipients = [
                [
                    'type' => 'admin',
                    'id' => 'admin-global',
                    'message' => "New user registered successfully: $username ($email)"
                ]
            ];
            $ns->create(
                'signup',
                'Successful User Registration',
                $recipients,
                null,
                'normal',
                $userId
            );

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Please login with your new credentials.',
                'redirect' => false
            ]);
            break;

        case 'sendResetEmail':
            // Now require username + email, and ensure they match
            if (!isset($data['username'], $data['email'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing username or email']));
            }

            $username = $data['username'];
            $email = $data['email'];

            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            // Check admin_users first
            $stmt = $pdo->prepare('SELECT id, email FROM admin_users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($adminUser) {
                if ($adminUser['email'] !== $email) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email does not match the specified username']);
                    break;
                }
                $userId = $adminUser['id'];
            } else {
                // Check zzimba_users
                $stmt = $pdo->prepare('SELECT id, email FROM zzimba_users WHERE username = :username');
                $stmt->execute([':username' => $username]);
                $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$zzimbaUser) {
                    $recipients = [
                        [
                            'type' => 'admin',
                            'id' => 'admin-global',
                            'message' => "Password reset attempt for non-existent user: $username"
                        ]
                    ];
                    $ns->create(
                        'password_reset',
                        'Failed Password Reset Attempt',
                        $recipients,
                        null,
                        'normal'
                    );

                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found']);
                    break;
                }

                if ($zzimbaUser['email'] !== $email) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email does not match the specified username']);
                    break;
                }
                $userId = $zzimbaUser['id'];
            }

            // All good: generate OTP and send
            $otp = createOTP('email', $email, $pdo);

            if (!sendPasswordResetOTP($email, $otp)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send reset code. Please try again.']);
                break;
            }

            $recipients = [
                [
                    'type' => 'admin',
                    'id' => 'admin-global',
                    'message' => "Password reset initiated via email for user: {$username}"
                ]
            ];
            $ns->create(
                'password_reset',
                'Password Reset Initiated',
                $recipients,
                null,
                'normal',
                $userId
            );

            echo json_encode(['success' => true, 'message' => 'Reset code sent to email']);
            break;

        case 'verifyResetOTP':
            if (!isset($data['contact'], $data['contactType'], $data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $contact = $data['contact'];
            $contactType = $data['contactType'];
            $otp = $data['otp'];

            if ($contactType === 'email' && !isValidEmail($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            } else if ($contactType === 'phone' && !isValidPhone($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            if (!verifyOTP($contactType, $contact, $otp, $pdo)) {
                $field = $contactType === 'email' ? 'email' : 'phone';
                $stmt = $pdo->prepare("SELECT username FROM admin_users WHERE $field = :contact");
                $stmt->execute([':contact' => $contact]);
                $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("SELECT username FROM zzimba_users WHERE $field = :contact");
                $stmt->execute([':contact' => $contact]);
                $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);

                $user = $adminUser ?: $zzimbaUser;
                $username = $user ? $user['username'] : $contact;

                $recipients = [
                    [
                        'type' => 'admin',
                        'id' => 'admin-global',
                        'message' => "Failed password reset OTP verification for user: $username"
                    ]
                ];
                $ns->create(
                    'password_reset',
                    'Failed Password Reset OTP',
                    $recipients,
                    null,
                    'normal'
                );

                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
            break;

        case 'resetPassword':
            if (!isset($data['username'], $data['contact'], $data['contactType'], $data['password'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $usernameInput = $data['username'];
            $contact = $data['contact'];
            $contactType = $data['contactType'];
            $password = $data['password'];

            if ($contactType === 'email' && !isValidEmail($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            } else if ($contactType === 'phone' && !isValidPhone($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            if (!isStrongPassword($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            // Look up by username + contact
            if ($contactType === 'email') {
                $stmt = $pdo->prepare('SELECT id, username, phone FROM admin_users WHERE username = :username AND email = :contact');
                $stmt->execute([':username' => $usernameInput, ':contact' => $contact]);
                $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare('SELECT id, username, phone FROM zzimba_users WHERE username = :username AND email = :contact');
                $stmt->execute([':username' => $usernameInput, ':contact' => $contact]);
                $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare('SELECT id, username, email FROM admin_users WHERE username = :username AND phone = :contact');
                $stmt->execute([':username' => $usernameInput, ':contact' => $contact]);
                $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare('SELECT id, username, email FROM zzimba_users WHERE username = :username AND phone = :contact');
                $stmt->execute([':username' => $usernameInput, ':contact' => $contact]);
                $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$adminUser && !$zzimbaUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'No matching user for that username and contact']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            if ($adminUser) {
                $stmt = $pdo->prepare('UPDATE admin_users SET password = :password, updated_at = :updated_at WHERE id = :id');
                $stmt->execute([':password' => $hashedPassword, ':updated_at' => $now, ':id' => $adminUser['id']]);
                $foundUsername = $adminUser['username'];
                $userId = $adminUser['id'];

                if ($contactType === 'email') {
                    sendPasswordChangedEmail($contact, $foundUsername);
                    if (!empty($adminUser['phone'])) {
                        sendPasswordChangedSms($adminUser['phone'], $foundUsername);
                    }
                } else {
                    sendPasswordChangedSms($contact, $foundUsername);
                    if (!empty($adminUser['email'])) {
                        sendPasswordChangedEmail($adminUser['email'], $foundUsername);
                    }
                }
            } else {
                $stmt = $pdo->prepare('UPDATE zzimba_users SET password = :password, updated_at = :updated_at WHERE id = :id');
                $stmt->execute([':password' => $hashedPassword, ':updated_at' => $now, ':id' => $zzimbaUser['id']]);
                $foundUsername = $zzimbaUser['username'];
                $userId = $zzimbaUser['id'];

                if ($contactType === 'email') {
                    sendPasswordChangedEmail($contact, $foundUsername);
                    if (!empty($zzimbaUser['phone'])) {
                        sendPasswordChangedSms($zzimbaUser['phone'], $foundUsername);
                    }
                } else {
                    sendPasswordChangedSms($contact, $foundUsername);
                    if (!empty($zzimbaUser['email'])) {
                        sendPasswordChangedEmail($zzimbaUser['email'], $foundUsername);
                    }
                }
            }

            $recipients = [
                [
                    'type' => 'user',
                    'id' => $userId,
                    'message' => "Your password has been successfully reset."
                ],
                [
                    'type' => 'admin',
                    'id' => 'admin-global',
                    'message' => "Password successfully reset for user: $foundUsername"
                ]
            ];
            $ns->create(
                'password_reset',
                'Password Reset Successful',
                $recipients,
                null,
                'normal',
                $userId
            );

            echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            break;

        case 'logout':
            $_SESSION = [];
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Successfully logged out']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    error_log('Auth Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}