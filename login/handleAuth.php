<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../mail/Mailer.php';

use ZzimbaOnline\Mail\Mailer;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id BINARY(16) PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        role ENUM('super_admin', 'admin', 'editor') NOT NULL DEFAULT 'admin',
        status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
        profile_pic_url VARCHAR(255),
        current_login DATETIME,
        last_login DATETIME,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS zzimba_users (
        id BINARY(16) PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
        profile_pic_url VARCHAR(255),
        current_login DATETIME,
        last_login DATETIME,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )");

    // Create OTP table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS otp_verifications (
        id BINARY(16) PRIMARY KEY,
        type ENUM('email', 'phone') NOT NULL,
        account VARCHAR(100) NOT NULL,
        otp VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        INDEX (account, type)
    )");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    die(json_encode(['success' => false, 'errors' => ['Database setup failed']]));
}

function generateUuidV7()
{
    $time = microtime(true);
    $time = floor($time * 1000);
    $time = dechex($time);
    $time = str_pad($time, 12, '0', STR_PAD_LEFT);

    $random = bin2hex(random_bytes(10));

    $uuid = $time . $random;
    $uuid = substr($uuid, 0, 8) . '-' .
        substr($uuid, 8, 4) . '-' .
        '7' . substr($uuid, 13, 3) . '-' .
        dechex(rand(8, 11)) . substr($uuid, 17, 3) . '-' .
        substr($uuid, 20, 12);

    return hex2bin(str_replace('-', '', $uuid));
}

function generateOTP($length = 6)
{
    $digits = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, strlen($digits) - 1)];
    }
    return $otp;
}

function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone)
{
    return preg_match('/^\+[0-9]{10,15}$/', $phone);
}

function isStrongPassword($password)
{
    return (strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password));
}

function sendEmailOTP($email, $otp)
{
    $subject = 'Your Verification Code - Zzimba Online';
    $content = '
        <div style="text-align: center; padding: 20px 0;">
            <h2>Email Verification</h2>
            <p>Thank you for registering with Zzimba Online. Please use the verification code below to complete your registration:</p>
            <div style="margin: 30px auto; padding: 10px; background-color: #f5f5f5; border-radius: 5px; width: 200px; text-align: center;">
                <h1 style="letter-spacing: 5px; font-size: 32px; margin: 0;">' . $otp . '</h1>
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you did not request this code, please ignore this email.</p>
        </div>
    ';

    return Mailer::sendMail($email, $subject, $content);
}

/**
 * New function to send an OTP for a login attempt when the account’s password is empty.
 */
function sendLoginOTP($email, $otp)
{
    $subject = 'Your OTP Verification Code - Zzimba Online';
    $content = '
        <div style="text-align: center; padding: 20px 0;">
            <h2>OTP Verification</h2>
            <p>Please use the following OTP to verify your account ownership and set up a new password:</p>
            <div style="margin: 30px auto; padding: 10px; background-color: #f5f5f5; border-radius: 5px; width: 200px; text-align: center;">
                <h1 style="letter-spacing: 5px; font-size: 32px; margin: 0;">' . $otp . '</h1>
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you did not initiate this request, please ignore this email.</p>
        </div>
    ';

    return Mailer::sendMail($email, $subject, $content);
}

function sendWelcomeEmail($username, $email, $phone)
{
    $subject = 'Welcome to Zzimba Online!';
    $content = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #d32f2f; text-align: center;">Welcome to Zzimba Online!</h2>
            
            <p>Dear ' . htmlspecialchars($username) . ',</p>
            
            <p>Thank you for creating an account with Zzimba Online. We\'re excited to have you join our community!</p>
            
            <div style="background-color: #f5f5f5; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Your Account Information:</h3>
                <p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>
                <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                <p><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</p>
            </div>
            
            <p>We recommend updating your profile information after you log in to enhance your experience with our platform.</p>
            
            <p>If you have any questions or need assistance, please don\'t hesitate to contact our support team.</p>
        </div>
    ';

    return Mailer::sendMail($email, $subject, $content);
}

function sendPasswordResetOTP($email, $otp)
{
    $subject = 'Password Reset Code - Zzimba Online';
    $content = '
        <div style="text-align: center; padding: 20px 0;">
            <h2>Password Reset</h2>
            <p>You have requested to reset your password. Please use the verification code below to continue:</p>
            <div style="margin: 30px auto; padding: 10px; background-color: #f5f5f5; border-radius: 5px; width: 200px; text-align: center;">
                <h1 style="letter-spacing: 5px; font-size: 32px; margin: 0;">' . $otp . '</h1>
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you did not request this code, please ignore this email or contact our support team if you believe this is unauthorized activity.</p>
        </div>
    ';

    return Mailer::sendMail($email, $subject, $content);
}

function sendPasswordChangedEmail($email, $username = null)
{
    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
    $subject = 'Password Changed - Zzimba Online';

    $greeting = $username ? 'Dear ' . htmlspecialchars($username) . ',' : 'Hello,';

    $content = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #d32f2f; text-align: center;">Password Changed</h2>
            
            <p>' . $greeting . '</p>
            
            <p>Your password for Zzimba Online has been successfully changed on ' . $now . ' (East Africa Time).</p>
            
            <div style="background-color: #f5f5f5; border-radius: 5px; padding: 15px; margin: 20px 0;">
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
            
            <p>Best regards,<br>
            The Zzimba Online Team</p>
        </div>
    ';

    return Mailer::sendMail($email, $subject, $content);
}

/**
 * Creates a new OTP for the given type/account.
 * This function first deletes any existing OTP for the same account and type.
 */
function createOTP($type, $account, $pdo)
{
    // Delete any existing OTP for this account and type
    $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE account = :account AND type = :type");
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':type', $type);
    $stmt->execute();

    // Generate a new OTP
    $otp = generateOTP();
    $hashedOTP = password_hash($otp, PASSWORD_DEFAULT);
    $id = generateUuidV7();
    $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
    $expires = (new DateTime('now +10 minutes', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

    // Insert the new OTP
    $stmt = $pdo->prepare("INSERT INTO otp_verifications (id, type, account, otp, created_at, expires_at) VALUES (:id, :type, :account, :otp, :created_at, :expires_at)");
    $stmt->bindParam(':id', $id, PDO::PARAM_LOB);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':otp', $hashedOTP);
    $stmt->bindParam(':created_at', $now);
    $stmt->bindParam(':expires_at', $expires);
    $stmt->execute();

    return $otp;
}

function verifyOTP($type, $account, $otp, $pdo)
{
    $stmt = $pdo->prepare("SELECT otp, expires_at FROM otp_verifications WHERE account = :account AND type = :type");
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':type', $type);
    $stmt->execute();

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

    // Delete the OTP after successful verification
    $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE account = :account AND type = :type");
    $stmt->bindParam(':account', $account);
    $stmt->bindParam(':type', $type);
    $stmt->execute();

    return true;
}

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'checkUser':
            if (!isset($data['identifier'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing identifier']));
            }

            $identifier = $data['identifier'];
            $isEmail = isValidEmail($identifier);

            $isAdmin = false;
            if (!$isEmail && strpos($identifier, 'Admin:') === 0) {
                $isAdmin = true;
                $identifier = substr($identifier, 6);
            }

            if ($isAdmin) {
                if ($isEmail) {
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :identifier");
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :identifier");
                }
                $stmt->bindParam(':identifier', $identifier);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'userType' => 'admin']);
                    break;
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Admin user not found. Please check your credentials.']);
                    break;
                }
            } else {
                if ($isEmail) {
                    $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = :identifier");
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE username = :identifier");
                }
                $stmt->bindParam(':identifier', $identifier);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'userType' => 'user']);
                    break;
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'User not found. Please check your credentials or register a new account.']);
                    break;
                }
            }

        case 'login':
            if (!isset($data['identifier']) || !isset($data['password']) || !isset($data['userType'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $identifier = $data['identifier'];
            $password = $data['password'];
            $userType = $data['userType'];

            $isEmail = isValidEmail($identifier);

            $isAdmin = false;
            if (!$isEmail && strpos($identifier, 'Admin:') === 0) {
                $isAdmin = true;
                $identifier = substr($identifier, 6);
            }

            $table = ($userType === 'admin' || $isAdmin) ? 'admin_users' : 'zzimba_users';

            if ($isEmail) {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email, last_login FROM $table WHERE email = :identifier");
            } else {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email, last_login FROM $table WHERE username = :identifier");
            }
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => ($isAdmin ? 'Admin user not found. Please check your credentials.' : 'User not found. Please check your credentials or register a new account.')]);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['status'] !== 'active') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Your account is ' . $user['status'] . '. Please contact support.']);
                break;
            }

            // Check if the stored password is empty. If so, trigger OTP verification silently.
            if (trim($user['password']) === '') {
                $otp = createOTP('email', $user['email'], $pdo);
                $emailSent = sendLoginOTP($user['email'], $otp);
                if (!$emailSent) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
                    break;
                }
                http_response_code(401);
                echo json_encode(['success' => false, 'errorCode' => 'EMPTY_PASSWORD', 'email' => $user['email']]);
                break;
            }

            if (!password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
                break;
            }

            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE $table SET last_login = current_login, current_login = :now WHERE id = :id");
            $stmt->bindParam(':now', $now);
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_LOB);
            $stmt->execute();

            $_SESSION['user'] = [
                'logged_in'   => true,
                'user_id'     => $user['id'],
                'uuid_user_id' => binToUuid($user['id']),
                'username'    => $user['username'],
                'email'       => $user['email'],
                'is_admin'    => ($table === 'admin_users'),
                'last_login'  => $user['last_login']
            ];

            $redirect = ($table === 'admin_users') ? 'admin/dashboard' : 'account/dashboard';

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

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username is already taken']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

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

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

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
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = :phone");
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered']);
                break;
            }

            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE phone = :phone");
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

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

            // Create OTP and store it in the database (this removes old OTP first)
            $otp = createOTP('email', $email, $pdo);

            // Send the OTP via email
            $emailSent = sendEmailOTP($email, $otp);

            if (!$emailSent) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send verification code. Please try again.']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'OTP sent to email']);
            break;

        case 'verifyEmailOTP':
            if (!isset($data['email']) || !isset($data['otp'])) {
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

            // Verify the OTP
            $isValid = verifyOTP('email', $email, $otp, $pdo);

            if (!$isValid) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
            break;

        case 'sendPhoneOTP':
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Phone verification is currently under maintenance. Please use email verification.']);
            break;

        case 'verifyPhoneOTP':
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Phone verification is currently under maintenance. Please use email verification.']);
            break;

        case 'register':
            if (!isset($data['username']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['password'])) {
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

            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE username = :username OR email = :email OR phone = :phone");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username, email, or phone number is already registered']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = generateUuidV7();
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare("INSERT INTO zzimba_users (id, username, email, phone, password, status, created_at, updated_at) VALUES (:id, :username, :email, :phone, :password, 'active', :created_at, :updated_at)");
            $stmt->bindParam(':id', $userId, PDO::PARAM_LOB);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            $stmt->execute();

            // Send welcome email to the user
            sendWelcomeEmail($username, $email, $phone);

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! Please login with your new credentials.',
                'redirect' => false
            ]);
            break;

        case 'sendResetEmail':
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

            // Check if email exists in admin_users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if email exists in zzimba_users
            $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$adminUser && !$zzimbaUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Email not found']);
                break;
            }

            // Create OTP and store it in the database (this removes old OTP first)
            $otp = createOTP('email', $email, $pdo);

            // Send the OTP via email
            $emailSent = sendPasswordResetOTP($email, $otp);

            if (!$emailSent) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to send reset code. Please try again.']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'Reset code sent to email']);
            break;

        case 'sendResetPhone':
            http_response_code(503);
            echo json_encode(['success' => false, 'message' => 'Phone verification is currently under maintenance. Please use email verification.']);
            break;

        case 'verifyResetOTP':
            if (!isset($data['contact']) || !isset($data['contactType']) || !isset($data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $contact = $data['contact'];
            $contactType = $data['contactType'];
            $otp = $data['otp'];

            if ($contactType !== 'email') {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'Phone verification is currently under maintenance. Please use email verification.']);
                break;
            }

            if (!isValidEmail($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            // Verify the OTP
            $isValid = verifyOTP('email', $contact, $otp, $pdo);

            if (!$isValid) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
                break;
            }

            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
            break;

        case 'resetPassword':
            if (!isset($data['contact']) || !isset($data['contactType']) || !isset($data['password'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $contact = $data['contact'];
            $contactType = $data['contactType'];
            $password = $data['password'];

            if ($contactType !== 'email') {
                http_response_code(503);
                echo json_encode(['success' => false, 'message' => 'Phone verification is currently under maintenance. Please use email verification.']);
                break;
            }

            if (!isValidEmail($contact)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            if (!isStrongPassword($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            // Check if email exists in admin_users
            $stmt = $pdo->prepare("SELECT id, username FROM admin_users WHERE email = :email");
            $stmt->bindParam(':email', $contact);
            $stmt->execute();
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if email exists in zzimba_users
            $stmt = $pdo->prepare("SELECT id, username FROM zzimba_users WHERE email = :email");
            $stmt->bindParam(':email', $contact);
            $stmt->execute();
            $zzimbaUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$adminUser && !$zzimbaUser) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                break;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            $username = null;
            if ($adminUser) {
                $stmt = $pdo->prepare("UPDATE admin_users SET password = :password, updated_at = :updated_at WHERE id = :id");
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':updated_at', $now);
                $stmt->bindParam(':id', $adminUser['id'], PDO::PARAM_LOB);
                $stmt->execute();
                $username = $adminUser['username'];
            } else {
                $stmt = $pdo->prepare("UPDATE zzimba_users SET password = :password, updated_at = :updated_at WHERE id = :id");
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':updated_at', $now);
                $stmt->bindParam(':id', $zzimbaUser['id'], PDO::PARAM_LOB);
                $stmt->execute();
                $username = $zzimbaUser['username'];
            }

            // Send password changed notification email
            sendPasswordChangedEmail($contact, $username);

            echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            break;

        case 'logout':
            $_SESSION = array();
            session_destroy();

            echo json_encode([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
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
