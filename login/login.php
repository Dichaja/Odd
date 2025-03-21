<?php
require_once __DIR__ . '/../config/config.php';

// Set CORS headers to allow requests from the same domain
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

// Only set content type headers if they haven't been sent yet
if (!headers_sent()) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
}

date_default_timezone_set('Africa/Kampala');

function generateUuidV7()
{
    $time = microtime(true) * 1000;
    $time = sprintf('%016x', $time);

    $uuid = sprintf(
        '%s-%s-%s-%s-%s',
        substr($time, 0, 8),
        substr($time, 8, 4),
        '7' . substr($time, 13, 3),
        sprintf('%04x', random_int(0, 0x3fff) | 0x8000),
        sprintf('%012x', random_int(0, 0xffffffffffff))
    );

    return $uuid;
}

function uuidToBin($uuid)
{
    return hex2bin(str_replace('-', '', $uuid));
}

function binToUuid($bin)
{
    $uuid = bin2hex($bin);
    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($uuid, 0, 8),
        substr($uuid, 8, 4),
        substr($uuid, 12, 4),
        substr($uuid, 16, 4),
        substr($uuid, 20, 12)
    );
}

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
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    die(json_encode(['success' => false, 'errors' => ['Database setup failed']]));
}

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout to 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

$_SESSION['last_activity'] = time();

$response = [
    'success' => false,
    'errors' => [],
    'html' => '',
    'redirect' => '',
    'message' => ''
];

$isAjax = isset($_POST['ajax_request']) && $_POST['ajax_request'] === '1';
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'check-identifier':
        checkIdentifier();
        break;
    case 'login':
        processLogin();
        break;
    case 'register':
        processRegistration();
        break;
    case 'switch-mode':
        switchAuthMode();
        break;
    case 'go-back':
        goBack();
        break;
    case 'resend-otp':
        resendOTP();
        break;
    default:
        if (!isset($_SESSION['auth_state'])) {
            $_SESSION['auth_state'] = [
                'mode' => 'login',
                'step' => 'username',
                'data' => [],
                'errors' => [],
                'message' => ''
            ];
        }

        if ($isAjax) {
            $auth_state = $_SESSION['auth_state'];
            $mode = $auth_state['mode'];
            $step = $auth_state['step'];
            $errors = $auth_state['errors'];
            $message = $auth_state['message'] ?? '';

            ob_start();
            include __DIR__ . '/template.php';
            $response['html'] = ob_get_clean();
            $response['success'] = true;

            echo json_encode($response);
            exit;
        }
}

function checkIdentifier()
{
    global $pdo, $response;

    $identifier = trim($_POST['identifier'] ?? '');
    if (empty($identifier)) {
        $response['errors'][] = 'Username or email is required';
        echo json_encode($response);
        exit;
    }

    $isAdminLogin = false;
    if (strpos($identifier, 'Admin:') === 0) {
        $isAdminLogin = true;
        $identifier = trim(substr($identifier, 6));
    }

    $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

    try {
        if ($isAdminLogin) {
            $field = $is_email ? 'email' : 'username';
            $stmt = $pdo->prepare("SELECT id, username, email, status FROM admin_users WHERE $field = :identifier");
            $stmt->bindParam(':identifier', $identifier);
            $stmt->execute();
            $user = $stmt->fetch();

            if (!$user) {
                $response['errors'][] = 'Admin account not found';
                echo json_encode($response);
                exit;
            }

            if ($user['status'] !== 'active') {
                $response['errors'][] = 'This account is ' . $user['status'];
                echo json_encode($response);
                exit;
            }

            $_SESSION['auth_state']['data']['identifier'] = $identifier;
            $_SESSION['auth_state']['data']['is_email'] = $is_email;
            $_SESSION['auth_state']['data']['is_admin'] = true;
            $_SESSION['auth_state']['data']['user_id'] = binToUuid($user['id']);
            $_SESSION['auth_state']['step'] = 'password';
        } else {
            if ($is_email) {
                $stmt = $pdo->prepare("SELECT id, username, email, status FROM zzimba_users WHERE email = :identifier");
                $stmt->bindParam(':identifier', $identifier);
            } else {
                // Remove BINARY and COLLATE for username check to make it more flexible
                $stmt = $pdo->prepare("SELECT id, username, email, status FROM zzimba_users WHERE username = :identifier");
                $stmt->bindParam(':identifier', $identifier);
            }

            $stmt->execute();
            $user = $stmt->fetch();

            if (!$user) {
                $response['errors'][] = 'Account not found';
                echo json_encode($response);
                exit;
            }

            if ($user['status'] !== 'active') {
                $response['errors'][] = 'This account is ' . $user['status'];
                echo json_encode($response);
                exit;
            }

            $_SESSION['auth_state']['data']['identifier'] = $identifier;
            $_SESSION['auth_state']['data']['is_email'] = $is_email;
            $_SESSION['auth_state']['data']['is_admin'] = false;
            $_SESSION['auth_state']['data']['user_id'] = binToUuid($user['id']);
            $_SESSION['auth_state']['step'] = 'password';
        }

        $response['success'] = true;

        $auth_state = $_SESSION['auth_state'];
        $mode = $auth_state['mode'];
        $step = $auth_state['step'];
        $errors = $auth_state['errors'];
        $message = $auth_state['message'] ?? '';

        ob_start();
        include __DIR__ . '/template.php';
        $response['html'] = ob_get_clean();

        echo json_encode($response);
        exit;
    } catch (PDOException $e) {
        error_log("Database error in checkIdentifier: " . $e->getMessage() . " - SQL: " . $e->getTraceAsString());
        if (strpos($e->getMessage(), 'Connection') !== false) {
            $response['errors'][] = 'Database connection error. Please try again later.';
        } else {
            $response['errors'][] = 'Error checking username. Please try again.';
        }
        echo json_encode($response);
        exit;
    }
}

function processLogin()
{
    global $pdo, $response;

    $auth_state = &$_SESSION['auth_state'];

    if ($auth_state['step'] === 'password') {
        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            $response['errors'][] = 'Password is required';
            echo json_encode($response);
            exit;
        }

        $identifier = $auth_state['data']['identifier'];
        $is_email = $auth_state['data']['is_email'];
        $is_admin = $auth_state['data']['is_admin'];
        $user_id = $auth_state['data']['user_id'];

        try {
            if ($is_admin) {
                $field = $is_email ? 'email' : 'username';
                $stmt = $pdo->prepare("SELECT id, username, email, password, role, first_name, last_name, last_login FROM admin_users WHERE $field = :identifier");
                $stmt->bindParam(':identifier', $identifier);
                $stmt->execute();
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password'])) {
                    $response['errors'][] = 'Invalid password';
                    echo json_encode($response);
                    exit;
                }

                $now = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("UPDATE admin_users SET last_login = current_login, current_login = :now WHERE id = :id");
                $stmt->bindParam(':now', $now);
                $stmt->bindParam(':id', $user['id'], PDO::PARAM_LOB);
                $stmt->execute();

                $_SESSION['user'] = [
                    'id' => binToUuid($user['id']),
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'is_admin' => true,
                    'logged_in' => true,
                    'last_login' => $user['last_login'],
                    'login_time' => date('Y-m-d H:i:s')
                ];

                $auth_state['mode'] = 'login';
                $auth_state['step'] = 'username';
                $auth_state['data'] = [];
                $auth_state['errors'] = [];
                $auth_state['message'] = '';

                $response['success'] = true;
                $response['redirect'] = BASE_URL . 'admin/dashboard';
            } else {
                // Fix for username login
                if ($is_email) {
                    $stmt = $pdo->prepare("SELECT id, username, email, password, first_name, last_name, last_login, phone FROM zzimba_users WHERE email = :identifier");
                    $stmt->bindParam(':identifier', $identifier);
                } else {
                    // Use case-sensitive comparison for username
                    $stmt = $pdo->prepare("SELECT id, username, email, password, first_name, last_name, last_login, phone FROM zzimba_users WHERE username = :identifier");
                    $stmt->bindParam(':identifier', $identifier);
                }

                $stmt->execute();
                $user = $stmt->fetch();

                if (!$user) {
                    $response['errors'][] = 'Account not found';
                    echo json_encode($response);
                    exit;
                }

                if (!password_verify($password, $user['password'])) {
                    $response['errors'][] = 'Invalid password';
                    echo json_encode($response);
                    exit;
                }

                $now = date('Y-m-d H:i:s');
                $stmt = $pdo->prepare("UPDATE zzimba_users SET last_login = current_login, current_login = :now WHERE id = :id");
                $stmt->bindParam(':now', $now);
                $stmt->bindParam(':id', $user['id'], PDO::PARAM_LOB);
                $stmt->execute();

                $_SESSION['user'] = [
                    'id' => binToUuid($user['id']),
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'phone' => $user['phone'], // This already includes country code from registration
                    'first_name' => $user['first_name'] ?? '',
                    'last_name' => $user['last_name'] ?? '',
                    'is_admin' => false,
                    'logged_in' => true,
                    'last_login' => $user['last_login'],
                    'login_time' => date('Y-m-d H:i:s')
                ];

                $auth_state['mode'] = 'login';
                $auth_state['step'] = 'username';
                $auth_state['data'] = [];
                $auth_state['errors'] = [];
                $auth_state['message'] = '';

                $response['success'] = true;
                $response['redirect'] = BASE_URL . 'account/dashboard';
            }

            echo json_encode($response);
            exit;
        } catch (PDOException $e) {
            error_log("Database error in processLogin: " . $e->getMessage() . " - SQL: " . $e->getTraceAsString());
            if (strpos($e->getMessage(), 'Connection') !== false) {
                $response['errors'][] = 'Database connection error. Please try again later.';
            } else {
                $response['errors'][] = 'Login error. Please try again.';
            }
            echo json_encode($response);
            exit;
        }
    } else {
        $response['errors'][] = 'Invalid authentication step';
        echo json_encode($response);
        exit;
    }
}

function processRegistration()
{
    global $pdo, $response;

    $auth_state = &$_SESSION['auth_state'];

    switch ($auth_state['step']) {
        case 'username':
            $username = trim($_POST['username'] ?? '');
            if (empty($username)) {
                $response['errors'][] = 'Username is required';
                echo json_encode($response);
                exit;
            }

            if (strlen($username) < 3) {
                $response['errors'][] = 'Username must be at least 3 characters';
                echo json_encode($response);
                exit;
            }

            if (!preg_match('/^[a-zA-Z]+$/', $username)) {
                $response['errors'][] = 'Username must contain only letters (no numbers or special characters)';
                echo json_encode($response);
                exit;
            }

            try {
                $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE username = :username COLLATE utf8mb4_bin");
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $response['errors'][] = 'Username already exists';
                    echo json_encode($response);
                    exit;
                }

                $auth_state['data']['username'] = $username;
                $auth_state['step'] = 'email';

                $response['success'] = true;

                $mode = $auth_state['mode'];
                $step = $auth_state['step'];
                $errors = $auth_state['errors'];
                $message = $auth_state['message'] ?? '';

                ob_start();
                include __DIR__ . '/template.php';
                $response['html'] = ob_get_clean();

                echo json_encode($response);
                exit;
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $response['errors'][] = 'System error. Please try again later.';
                echo json_encode($response);
                exit;
            }
            break;

        case 'email':
            $email = trim($_POST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['errors'][] = 'Valid email is required';
                echo json_encode($response);
                exit;
            }

            try {
                $stmt = $pdo->prepare("SELECT id FROM zzimba_users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $response['errors'][] = 'Email already exists';
                    echo json_encode($response);
                    exit;
                }

                $auth_state['data']['email'] = $email;

                $otp = generateOTP();
                $auth_state['data']['email_otp'] = $otp;

                $auth_state['step'] = 'verify_email';

                $response['success'] = true;

                $mode = $auth_state['mode'];
                $step = $auth_state['step'];
                $errors = $auth_state['errors'];
                $message = $auth_state['message'] ?? '';

                ob_start();
                include __DIR__ . '/template.php';
                $response['html'] = ob_get_clean();

                echo json_encode($response);
                exit;
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $response['errors'][] = 'System error. Please try again later.';
                echo json_encode($response);
                exit;
            }
            break;

        case 'verify_email':
            $otp = trim($_POST['email_otp'] ?? '');
            if (empty($otp)) {
                $response['errors'][] = 'OTP is required';
                echo json_encode($response);
                exit;
            }

            if ($otp === $auth_state['data']['email_otp']) {
                $auth_state['step'] = 'phone';

                $response['success'] = true;

                $mode = $auth_state['mode'];
                $step = $auth_state['step'];
                $errors = $auth_state['errors'];
                $message = $auth_state['message'] ?? '';

                ob_start();
                include __DIR__ . '/template.php';
                $response['html'] = ob_get_clean();

                echo json_encode($response);
                exit;
            } else {
                $response['errors'][] = 'Invalid OTP';
                echo json_encode($response);
                exit;
            }
            break;

        case 'phone':
            $phone = trim($_POST['full_phone'] ?? '');
            if (empty($phone)) {
                $response['errors'][] = 'Phone number is required';
                echo json_encode($response);
                exit;
            }

            $auth_state['data']['phone'] = $phone;

            $otp = generateOTP();
            $auth_state['data']['phone_otp'] = $otp;

            $auth_state['step'] = 'verify_phone';

            $response['success'] = true;

            $mode = $auth_state['mode'];
            $step = $auth_state['step'];
            $errors = $auth_state['errors'];
            $message = $auth_state['message'] ?? '';

            ob_start();
            include __DIR__ . '/template.php';
            $response['html'] = ob_get_clean();

            echo json_encode($response);
            exit;
            break;

        case 'verify_phone':
            $otp = trim($_POST['phone_otp'] ?? '');
            if (empty($otp)) {
                $response['errors'][] = 'OTP is required';
                echo json_encode($response);
                exit;
            }

            if ($otp === $auth_state['data']['phone_otp']) {
                $auth_state['step'] = 'password';

                $response['success'] = true;

                $mode = $auth_state['mode'];
                $step = $auth_state['step'];
                $errors = $auth_state['errors'];
                $message = $auth_state['message'] ?? '';

                ob_start();
                include __DIR__ . '/template.php';
                $response['html'] = ob_get_clean();

                echo json_encode($response);
                exit;
            } else {
                $response['errors'][] = 'Invalid OTP';
                echo json_encode($response);
                exit;
            }
            break;

        case 'password':
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($password)) {
                $response['errors'][] = 'Password is required';
                echo json_encode($response);
                exit;
            }

            if (strlen($password) < 8) {
                $response['errors'][] = 'Password must be at least 8 characters';
                echo json_encode($response);
                exit;
            }

            $strength = checkPasswordStrength($password);
            if ($strength < 3) {
                $response['errors'][] = 'Password is too weak';
                echo json_encode($response);
                exit;
            }

            if ($password !== $confirm_password) {
                $response['errors'][] = 'Passwords do not match';
                echo json_encode($response);
                exit;
            }

            try {
                $now = date('Y-m-d H:i:s');
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $uuid = generateUuidV7();
                $binUuid = uuidToBin($uuid);

                // Ensure phone number includes country code (already handled by intl-tel-input)
                $phone = $auth_state['data']['phone']; // This already includes the country code

                $stmt = $pdo->prepare("INSERT INTO zzimba_users (id, username, email, phone, password, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', ?, ?)");
                $stmt->bindParam(1, $binUuid, PDO::PARAM_LOB);
                $stmt->bindParam(2, $auth_state['data']['username']);
                $stmt->bindParam(3, $auth_state['data']['email']);
                $stmt->bindParam(4, $phone);
                $stmt->bindParam(5, $hashed_password);
                $stmt->bindParam(6, $now);
                $stmt->bindParam(7, $now);
                $stmt->execute();

                $auth_state['mode'] = 'login';
                $auth_state['step'] = 'username';
                $auth_state['data'] = [];
                $auth_state['errors'] = [];
                $auth_state['message'] = 'Account created successfully. Please login to continue.';

                $response['success'] = true;

                $mode = $auth_state['mode'];
                $step = $auth_state['step'];
                $errors = $auth_state['errors'];
                $message = $auth_state['message'];

                ob_start();
                include __DIR__ . '/template.php';
                $response['html'] = ob_get_clean();

                echo json_encode($response);
                exit;
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $response['errors'][] = 'Account creation failed. Please try again later.';
                echo json_encode($response);
                exit;
            }
            break;
    }
}

function switchAuthMode()
{
    global $response;

    $mode = $_POST['switch-mode'] ?? '';
    $valid_modes = ['login', 'register', 'forgot_password'];

    if (in_array($mode, $valid_modes)) {
        $_SESSION['auth_state'] = [
            'mode' => $mode,
            'step' => ($mode === 'login' || $mode === 'register') ? 'username' : 'select_method',
            'data' => [],
            'errors' => [],
            'message' => ''
        ];

        $auth_state = $_SESSION['auth_state'];
        $mode = $auth_state['mode'];
        $step = $auth_state['step'];
        $errors = $auth_state['errors'];
        $message = $auth_state['message'] ?? '';

        ob_start();
        include __DIR__ . '/template.php';
        $response['html'] = ob_get_clean();
        $response['success'] = true;

        echo json_encode($response);
        exit;
    } else {
        $response['errors'][] = 'Invalid mode';
        echo json_encode($response);
        exit;
    }
}

function goBack()
{
    global $response;

    if (!isset($_SESSION['auth_state'])) {
        $_SESSION['auth_state'] = [
            'mode' => 'login',
            'step' => 'username',
            'data' => [],
            'errors' => [],
            'message' => ''
        ];
    }

    $auth_state = &$_SESSION['auth_state'];

    $auth_state['errors'] = [];

    if (isset($auth_state['mode']) && isset($auth_state['step'])) {
        if ($auth_state['mode'] === 'login' && $auth_state['step'] === 'password') {
            $auth_state['step'] = 'username';
        } else if ($auth_state['mode'] === 'register') {
            switch ($auth_state['step']) {
                case 'email':
                    $auth_state['step'] = 'username';
                    break;
                case 'verify_email':
                    $auth_state['step'] = 'email';
                    break;
                case 'phone':
                    $auth_state['step'] = 'verify_email';
                    break;
                case 'verify_phone':
                    $auth_state['step'] = 'phone';
                    break;
                case 'password':
                    $auth_state['step'] = 'verify_phone';
                    break;
            }
        } else if ($auth_state['mode'] === 'forgot_password') {
            switch ($auth_state['step']) {
                case 'email':
                case 'phone':
                    $auth_state['step'] = 'select_method';
                    break;
                case 'verify_otp':
                    if (isset($auth_state['data']['reset_method']) && $auth_state['data']['reset_method'] === 'email') {
                        $auth_state['step'] = 'email';
                    } else {
                        $auth_state['step'] = 'phone';
                    }
                    break;
            }
        } else if ($auth_state['mode'] === 'reset_password') {
            $auth_state['mode'] = 'forgot_password';
            $auth_state['step'] = 'verify_otp';
        }
    } else {
        $auth_state['mode'] = 'login';
        $auth_state['step'] = 'username';
    }

    $mode = $auth_state['mode'];
    $step = $auth_state['step'];
    $errors = $auth_state['errors'] ?? [];
    $message = $auth_state['message'] ?? '';

    ob_start();
    include __DIR__ . '/template.php';
    $response['html'] = ob_get_clean();
    $response['success'] = true;

    echo json_encode($response);
    exit;
}

function resendOTP()
{
    global $response;

    $type = $_POST['resend-otp'] ?? '';
    $auth_state = &$_SESSION['auth_state'];

    if ($type === 'email' && $auth_state['step'] === 'verify_email') {
        $otp = generateOTP();
        $auth_state['data']['email_otp'] = $otp;
    } else if ($type === 'phone' && $auth_state['step'] === 'verify_phone') {
        $otp = generateOTP();
        $auth_state['data']['phone_otp'] = $otp;
    } else if ($type === 'reset' && $auth_state['step'] === 'verify_otp') {
        $otp = generateOTP();
        $auth_state['data']['reset_otp'] = $otp;
    }

    $response['success'] = true;
    echo json_encode($response);
    exit;
}

function generateOTP($length = 6)
{
    return str_pad(mt_rand(1, 999999), $length, '0', STR_PAD_LEFT);
}

function checkPasswordStrength($password)
{
    $strength = 0;

    if (strlen($password) >= 8) $strength += 1;
    if (strlen($password) >= 12) $strength += 1;

    if (preg_match('/[A-Z]/', $password)) $strength += 1;
    if (preg_match('/[a-z]/', $password)) $strength += 1;
    if (preg_match('/[0-9]/', $password)) $strength += 1;
    if (preg_match('/[^A-Za-z0-9]/', $password)) $strength += 1;

    return $strength;
}

function getStepTitle($mode, $step)
{
    $titles = [
        'login' => [
            'username' => 'Sign In',
            'password' => 'Enter Your Password'
        ],
        'register' => [
            'username' => 'Create Your Account',
            'email' => 'Enter Your Email',
            'verify_email' => 'Verify Your Email',
            'phone' => 'Enter Your Phone Number',
            'verify_phone' => 'Verify Your Phone Number',
            'password' => 'Create a Strong Password'
        ],
        'forgot_password' => [
            'select_method' => 'Reset Your Password',
            'email' => 'Enter Your Email',
            'phone' => 'Enter Your Phone Number',
            'verify_otp' => 'Enter Verification Code'
        ],
        'reset_password' => [
            'new_password' => 'Create New Password'
        ]
    ];

    return $titles[$mode][$step] ?? 'Authentication';
}

function getFormButtons($mode, $step)
{
    $html = '<div class="flex justify-between mt-6">';

    if (
        ($mode === 'login' && $step === 'password') ||
        ($mode === 'register' && $step !== 'username') ||
        ($mode === 'forgot_password' && ($step === 'email' || $step === 'phone' || $step === 'verify_otp')) ||
        ($mode === 'reset_password')
    ) {
        $html .= '<button type="button" onclick="goBack()" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Back</button>';
    } else {
        $html .= '<div></div>';
    }

    $button_text = 'Continue';

    if ($mode === 'login' && $step === 'password') {
        $button_text = 'Sign In';
    } else if ($mode === 'register' && $step === 'password') {
        $button_text = 'Create Account';
    } else if ($mode === 'reset_password' && $step === 'new_password') {
        $button_text = 'Reset Password';
    }

    $html .= '<button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-red-600 transition-colors">' . $button_text . '</button>';
    $html .= '</div>';

    return $html;
}

$auth_state = $_SESSION['auth_state'] ?? [
    'mode' => 'login',
    'step' => 'username',
    'data' => [],
    'errors' => [],
    'message' => ''
];
$mode = $auth_state['mode'];
$step = $auth_state['step'];
$errors = $auth_state['errors'] ?? [];
$message = $auth_state['message'] ?? '';
?>

<div id="auth-modal" style="z-index:1100; display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <?php include __DIR__ . '/template.php'; ?>
    </div>
</div>

<script>
    // Store the base URL for use in all AJAX requests
    const baseUrl = '<?= BASE_URL ?>';

    document.addEventListener('DOMContentLoaded', function() {
        const phoneInputField = document.querySelector("#phone");
        if (phoneInputField) {
            const iti = window.intlTelInput(phoneInputField, {
                preferredCountries: ["ug", "rw", "ke", "tz"],
                initialCountry: "ug",
                separateDialCode: true,
                allowDropdown: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });

            const form = phoneInputField.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const fullNumber = iti.getNumber();
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'full_phone';
                    hiddenInput.value = fullNumber;
                    this.appendChild(hiddenInput);
                });
            }
        }

        const authForm = document.querySelector('#auth-modal form');
        if (authForm) {
            authForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('ajax_request', '1');

                let action = '';
                const currentStep = '<?= $step ?>';
                const currentMode = '<?= $mode ?>';

                if (currentMode === 'login') {
                    if (currentStep === 'username') {
                        action = 'check-identifier';
                    } else if (currentStep === 'password') {
                        action = 'login';
                    }
                } else if (currentMode === 'register') {
                    action = 'register';
                }

                fetch(baseUrl + 'auth/' + action, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else if (data.html) {
                                document.querySelector('#auth-modal .modal-container').innerHTML = data.html;
                                initializeEventListeners();
                            }
                        } else {
                            const errorContainer = document.createElement('div');
                            errorContainer.className = 'mb-4 p-3 bg-red-50 text-red-700 rounded-lg';

                            let errorHtml = '<ul class="list-disc pl-5">';
                            data.errors.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                            errorHtml += '</ul>';

                            errorContainer.innerHTML = errorHtml;

                            const existingError = authForm.querySelector('.bg-red-50');
                            if (existingError) {
                                existingError.replaceWith(errorContainer);
                            } else {
                                authForm.prepend(errorContainer);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        }
    });

    // Update all other JavaScript functions to use baseUrl
    function initializeEventListeners() {
        const phoneInputField = document.querySelector("#phone");
        if (phoneInputField) {
            const iti = window.intlTelInput(phoneInputField, {
                preferredCountries: ["ug", "rw", "ke", "tz"],
                initialCountry: "ug",
                separateDialCode: true,
                allowDropdown: true,
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
            });

            const form = phoneInputField.closest('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const fullNumber = iti.getNumber();
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'full_phone';
                    hiddenInput.value = fullNumber;
                    this.appendChild(hiddenInput);
                });
            }
        }

        const authForm = document.querySelector('#auth-modal form');
        if (authForm) {
            authForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                formData.append('ajax_request', '1');

                let action = '';
                const currentStep = authForm.getAttribute('data-step');
                const currentMode = authForm.getAttribute('data-mode');

                if (currentMode === 'login') {
                    if (currentStep === 'username') {
                        action = 'check-identifier';
                    } else if (currentStep === 'password') {
                        action = 'login';
                    }
                } else if (currentMode === 'register') {
                    action = 'register';
                }

                fetch(baseUrl + 'auth/' + action, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else if (data.html) {
                                document.querySelector('#auth-modal .modal-container').innerHTML = data.html;
                                initializeEventListeners();
                            }
                        } else {
                            const errorContainer = document.createElement('div');
                            errorContainer.className = 'mb-4 p-3 bg-red-50 text-red-700 rounded-lg';

                            let errorHtml = '<ul class="list-disc pl-5">';
                            data.errors.forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                            errorHtml += '</ul>';

                            errorContainer.innerHTML = errorHtml;

                            const existingError = authForm.querySelector('.bg-red-50');
                            if (existingError) {
                                existingError.replaceWith(errorContainer);
                            } else {
                                authForm.prepend(errorContainer);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        }

        // Add event listener for admin shortcut (3 spaces)
        const usernameInput = document.querySelector("input[name='identifier']");
        if (usernameInput) {
            usernameInput.addEventListener('input', function(e) {
                const value = this.value;
                if (value.endsWith('   ')) { // Check for 3 spaces
                    // Replace 3 spaces with "Admin:"
                    this.value = value.slice(0, -3) + 'Admin:';

                    // Position cursor at the end
                    const end = this.value.length;
                    this.setSelectionRange(end, end);
                    this.focus();
                }
            });
        }
    }

    function switchAuthMode(mode) {
        fetch(baseUrl + 'auth/switch-mode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `switch-mode=${mode}&ajax_request=1`,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    document.querySelector('#auth-modal .modal-container').innerHTML = data.html;
                    initializeEventListeners();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function checkPasswordStrength(password, inputId = 'register-password') {
        const meter = document.querySelector(`#${inputId}`).closest('div').nextElementSibling;
        const meterFill = meter.querySelector('.password-strength-meter-fill');
        const strengthText = meter.nextElementSibling;

        meter.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');

        if (!password) {
            meterFill.style.width = '0';
            strengthText.textContent = '';
            return;
        }

        let strength = 0;

        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;

        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;

        let strengthLevel = '';
        let strengthClass = '';

        if (strength < 3) {
            strengthLevel = 'Weak';
            strengthClass = 'strength-weak';
        } else if (strength < 4) {
            strengthLevel = 'Fair';
            strengthClass = 'strength-fair';
        } else if (strength < 6) {
            strengthLevel = 'Good';
            strengthClass = 'strength-good';
        } else {
            strengthLevel = 'Strong';
            strengthClass = 'strength-strong';
        }

        meter.classList.add(strengthClass);
        strengthText.textContent = `Password strength: ${strengthLevel}`;
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function goBack() {
        fetch(baseUrl + 'auth/go-back', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'go_back=1&ajax_request=1',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    document.querySelector('#auth-modal .modal-container').innerHTML = data.html;
                    initializeEventListeners();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function resendOTP(type) {
        fetch(baseUrl + 'auth/resend-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `resend-otp=${type}&ajax_request=1`,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'p-3 bg-green-50 text-green-700 rounded-lg mb-4';
                    notification.textContent = 'Verification code resent successfully';

                    const form = document.querySelector('#auth-modal form');
                    if (form) {
                        form.prepend(notification);

                        setTimeout(() => {
                            notification.remove();
                        }, 3000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function openAuthModal() {
        const authModal = document.getElementById('auth-modal');
        if (authModal) {
            authModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            const modalContainer = authModal.querySelector('.modal-container');
            if (modalContainer) {
                setTimeout(() => {
                    modalContainer.classList.add('active');
                }, 10);
            }
        }
    }

    function closeAuthModal() {
        const authModal = document.getElementById('auth-modal');
        if (authModal) {
            const modalContainer = authModal.querySelector('.modal-container');
            if (modalContainer) {
                modalContainer.classList.remove('active');
            }

            setTimeout(() => {
                authModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', initializeEventListeners);
</script>