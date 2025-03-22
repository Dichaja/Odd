<?php
require_once __DIR__ . '/../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

// Helper functions
function generateUuidV7()
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x70);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
    return $bytes;
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
    // In a real application, you would send an actual email here
    // For this demo, we'll just return true
    return true;
}

function sendSMSOTP($phone, $otp)
{
    // In a real application, you would send an actual SMS here
    // For this demo, we'll just return true
    return true;
}

// Get the action from the request
$action = $_GET['action'] ?? '';

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);

try {
    switch ($action) {
        case 'checkUser':
            if (!isset($data['identifier'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing identifier']));
            }

            $identifier = $data['identifier'];

            // Check if the identifier is an email
            $isEmail = isValidEmail($identifier);

            // Check in admin_users table
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
            }

            // Check in zzimba_users table
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
            }

            // User not found
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            break;

        case 'login':
            if (!isset($data['identifier']) || !isset($data['password']) || !isset($data['userType'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $identifier = $data['identifier'];
            $password = $data['password'];
            $userType = $data['userType'];

            // Check if the identifier is an email
            $isEmail = isValidEmail($identifier);

            // Check if username has Admin: prefix
            $isAdmin = false;
            $username = $identifier;
            if (strpos($username, 'Admin:') === 0) {
                $isAdmin = true;
                $username = substr($username, 6); // Remove the Admin: prefix
            }

            // Get the user from the appropriate table
            $table = ($userType === 'admin') ? 'admin_users' : 'zzimba_users';

            if ($isEmail) {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email FROM $table WHERE email = :identifier");
            } else {
                $stmt = $pdo->prepare("SELECT id, username, password, status, email FROM $table WHERE username = :identifier");
            }
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found']);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if the user is active
            if ($user['status'] !== 'active') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Your account is ' . $user['status'] . '. Please contact support.']);
                break;
            }

            // Verify the password
            if (!password_verify($password, $user['password'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
                break;
            }

            // Update login timestamps
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE $table SET last_login = current_login, current_login = :now WHERE id = :id");
            $stmt->bindParam(':now', $now);
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_LOB);
            $stmt->execute();

            // Set session variables
            session_start();
            // $_SESSION['user_id'] = bin2hex($user['id']);
            // $_SESSION['username'] = $user['username'];
            // $_SESSION['user_type'] = $userType;

            $_SESSION['user'] = [
                'logged_in' => true,
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'is_admin' => $isAdmin,
                'last_login' => date('Y-m-d H:i:s')
            ];

            // Determine redirect URL
            $redirect = ($userType === 'admin') ? 'admin/dashboard' : 'account/dashboard';

            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect]);
            break;

        case 'checkUsername':
            if (!isset($data['username'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing username']));
            }

            $username = $data['username'];

            // Check if username is valid
            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters long']);
                break;
            }

            // Check if username exists in admin_users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Username is already taken']);
                break;
            }

            // Check if username exists in zzimba_users
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

            // Check if email is valid
            if (!isValidEmail($email)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            // Check if email exists in admin_users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already registered']);
                break;
            }

            // Check if email exists in zzimba_users
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

            // Check if phone is valid
            if (!isValidPhone($phone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                break;
            }

            // Check if phone exists in admin_users
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE phone = :phone");
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Phone number is already registered']);
                break;
            }

            // Check if phone exists in zzimba_users
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

            // Generate OTP
            $otp = generateOTP();

            // Store OTP in session
            session_start();
            $_SESSION['email_otp'] = $otp;
            $_SESSION['email_otp_time'] = time();
            $_SESSION['email_for_otp'] = $email;

            // Send OTP via email
            sendEmailOTP($email, $otp);

            echo json_encode(['success' => true, 'message' => 'OTP sent to email', 'otp' => $otp]);
            break;

        case 'verifyEmailOTP':
            if (!isset($data['email']) || !isset($data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $email = $data['email'];
            $otp = $data['otp'];

            // Verify OTP from session
            session_start();
            if (!isset($_SESSION['email_otp']) || !isset($_SESSION['email_for_otp']) || $_SESSION['email_for_otp'] !== $email) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid verification attempt']);
                break;
            }

            // Check if OTP is expired (10 minutes)
            if (time() - $_SESSION['email_otp_time'] > 600) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
                break;
            }

            // Verify OTP
            if ($_SESSION['email_otp'] !== $otp) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
                break;
            }

            // Clear OTP from session
            unset($_SESSION['email_otp']);
            unset($_SESSION['email_otp_time']);

            echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
            break;

        case 'sendPhoneOTP':
            if (!isset($data['phone'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing phone number']));
            }

            $phone = $data['phone'];

            // Generate OTP
            $otp = generateOTP();

            // Store OTP in session
            session_start();
            $_SESSION['phone_otp'] = $otp;
            $_SESSION['phone_otp_time'] = time();
            $_SESSION['phone_for_otp'] = $phone;

            // Send OTP via SMS
            sendSMSOTP($phone, $otp);

            echo json_encode(['success' => true, 'message' => 'OTP sent to phone', 'otp' => $otp]);
            break;

        case 'verifyPhoneOTP':
            if (!isset($data['phone']) || !isset($data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $phone = $data['phone'];
            $otp = $data['otp'];

            // Verify OTP from session
            session_start();
            if (!isset($_SESSION['phone_otp']) || !isset($_SESSION['phone_for_otp']) || $_SESSION['phone_for_otp'] !== $phone) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid verification attempt']);
                break;
            }

            // Check if OTP is expired (10 minutes)
            if (time() - $_SESSION['phone_otp_time'] > 600) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
                break;
            }

            // Verify OTP
            if ($_SESSION['phone_otp'] !== $otp) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
                break;
            }

            // Clear OTP from session
            unset($_SESSION['phone_otp']);
            unset($_SESSION['phone_otp_time']);

            echo json_encode(['success' => true, 'message' => 'Phone verified successfully']);
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

            // Validate inputs
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

            // Check if username, email, or phone already exists
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

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Generate UUID
            $userId = generateUuidV7();

            // Get current timestamp
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO zzimba_users (id, username, email, phone, password, status, created_at, updated_at) VALUES (:id, :username, :email, :phone, :password, 'active', :created_at, :updated_at)");
            $stmt->bindParam(':id', $userId, PDO::PARAM_LOB);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            $stmt->execute();

            // Set session variables
            // session_start();
            // $_SESSION['user_id'] = bin2hex($userId);
            // $_SESSION['username'] = $username;
            // $_SESSION['user_type'] = 'user';

            // Registration successful
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

            // Check if email exists in any user table
            $stmt = $pdo->prepare("SELECT id, 'admin' as type FROM admin_users WHERE email = :email UNION SELECT id, 'user' as type FROM zzimba_users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Email not found']);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate OTP
            $otp = generateOTP();

            // Store OTP in session
            session_start();
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_otp_time'] = time();
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_user_type'] = $user['type'];
            $_SESSION['reset_user_id'] = $user['id'];

            // Send OTP via email
            sendEmailOTP($email, $otp);

            echo json_encode(['success' => true, 'message' => 'Reset code sent to email', 'otp' => $otp]);
            break;

        case 'sendResetPhone':
            if (!isset($data['phone'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing phone number']));
            }

            $phone = $data['phone'];

            // Check if phone exists in any user table
            $stmt = $pdo->prepare("SELECT id, 'admin' as type FROM admin_users WHERE phone = :phone UNION SELECT id, 'user' as type FROM zzimba_users WHERE phone = :phone");
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Phone number not found']);
                break;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate OTP
            $otp = generateOTP();

            // Store OTP in session
            session_start();
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_otp_time'] = time();
            $_SESSION['reset_phone'] = $phone;
            $_SESSION['reset_user_type'] = $user['type'];
            $_SESSION['reset_user_id'] = $user['id'];

            // Send OTP via SMS
            sendSMSOTP($phone, $otp);

            echo json_encode(['success' => true, 'message' => 'Reset code sent to phone', 'otp' => $otp]);
            break;

        case 'verifyResetOTP':
            if (!isset($data['contact']) || !isset($data['contactType']) || !isset($data['otp'])) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Missing required fields']));
            }

            $contact = $data['contact'];
            $contactType = $data['contactType'];
            $otp = $data['otp'];

            // Verify OTP from session
            session_start();

            if ($contactType === 'email') {
                if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $contact) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid verification attempt']);
                    break;
                }
            } else {
                if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_phone']) || $_SESSION['reset_phone'] !== $contact) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid verification attempt']);
                    break;
                }
            }

            // Check if OTP is expired (10 minutes)
            if (time() - $_SESSION['reset_otp_time'] > 600) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
                break;
            }

            // Verify OTP
            if ($_SESSION['reset_otp'] !== $otp) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
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

            // Verify session data
            session_start();

            if ($contactType === 'email') {
                if (!isset($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $contact) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid reset attempt']);
                    break;
                }
            } else {
                if (!isset($_SESSION['reset_phone']) || $_SESSION['reset_phone'] !== $contact) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid reset attempt']);
                    break;
                }
            }

            if (!isset($_SESSION['reset_user_type']) || !isset($_SESSION['reset_user_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid reset attempt']);
                break;
            }

            // Validate password
            if (!isStrongPassword($password)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character']);
                break;
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Get current timestamp
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');

            // Update password
            $table = ($_SESSION['reset_user_type'] === 'admin') ? 'admin_users' : 'zzimba_users';
            $stmt = $pdo->prepare("UPDATE $table SET password = :password, updated_at = :updated_at WHERE id = :id");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':updated_at', $now);
            $stmt->bindParam(':id', $_SESSION['reset_user_id'], PDO::PARAM_LOB);
            $stmt->execute();

            // Clear session data
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_otp_time']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_phone']);
            unset($_SESSION['reset_user_type']);
            unset($_SESSION['reset_user_id']);

            echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
            break;

        case 'logout':
            // Clear all session variables
            $_SESSION = array();

            // Destroy the session
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
