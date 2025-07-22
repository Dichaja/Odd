<?php
require_once __DIR__ . '/../config/config.php';  // must define $pdo and generateUlid()
header('Content-Type: application/json');
date_default_timezone_set('Africa/Kampala');

$jsonFile = __DIR__ . '/../track/session_log.json';
$expirySeconds = 30 * 60;

try {
    // 1) Ensure sessions table exists with comprehensive session data
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sessions` (
          `session_id`     VARCHAR(26)    NOT NULL,
          `last_update_ms` BIGINT         NOT NULL,
          `ip_address`     VARCHAR(45)    NOT NULL,
          `country`        VARCHAR(100)   NOT NULL,
          `short_name`     CHAR(2)        NOT NULL,
          `phone_code`     VARCHAR(10)    NOT NULL,
          `browser`        VARCHAR(50)    NOT NULL,
          `device`         VARCHAR(50)    NOT NULL,
          `latitude`       DECIMAL(10,7)  NULL,
          `longitude`      DECIMAL(10,7)  NULL,
          `user_id`        VARCHAR(26)    NULL,
          `admin_id`       VARCHAR(26)    NULL,
          `logged_in`      BOOLEAN        DEFAULT FALSE,
          `username`       VARCHAR(100)   NULL,
          `email`          VARCHAR(255)   NULL,
          `phone`          VARCHAR(20)    NULL,
          `is_admin`       BOOLEAN        DEFAULT FALSE,
          `last_login`     DATETIME       NULL,
          `created_at`     DATETIME       DEFAULT CURRENT_TIMESTAMP,
          `updated_at`     DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`session_id`),
          INDEX `idx_sessions_user`  (`user_id`),
          INDEX `idx_sessions_admin` (`admin_id`),
          INDEX `idx_sessions_country` (`country`),
          INDEX `idx_sessions_device` (`device`),
          INDEX `idx_sessions_logged_in` (`logged_in`),
          INDEX `idx_sessions_is_admin` (`is_admin`),
          INDEX `idx_sessions_username` (`username`),
          INDEX `idx_sessions_email` (`email`),
          INDEX `idx_sessions_last_update` (`last_update_ms`),
          CONSTRAINT `fk_sessions_user`
            FOREIGN KEY (`user_id`)
            REFERENCES `zzimba_users` (`id`)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
          CONSTRAINT `fk_sessions_admin`
            FOREIGN KEY (`admin_id`)
            REFERENCES `admin_users` (`id`)
            ON UPDATE CASCADE
            ON DELETE SET NULL
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_general_ci;
    ");

    // 2) Ensure session_events table exists with all possible event fields
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `session_events` (
          `event_id`         VARCHAR(26)    NOT NULL,
          `session_id`       VARCHAR(26)    NOT NULL,
          `event_name`       VARCHAR(50)    NOT NULL,
          `event_timestamp`  DATETIME(3)    NOT NULL,
          
          /* Navigation and page data */
          `referrer`         TEXT           NULL,
          `url`              TEXT           NULL,
          `active_navigation` VARCHAR(100)  NULL,
          `page_title`       VARCHAR(200)   NULL,
          
          /* Authentication and user data */
          `identifier`       VARCHAR(100)   NULL,
          `identifier_type`  ENUM('username', 'email', 'phone') NULL,
          `username`         VARCHAR(100)   NULL,
          `email`            VARCHAR(255)   NULL,
          `phone`            VARCHAR(20)    NULL,
          `error_message`    TEXT           NULL,
          `status`           ENUM('success', 'failed', 'pending') NULL,
          
          /* Form interaction data */
          `from_form`        VARCHAR(50)    NULL,
          `to_form`          VARCHAR(50)    NULL,
          `form_type`        VARCHAR(50)    NULL,
          `step`             VARCHAR(50)    NULL,
          `action`           VARCHAR(50)    NULL,
          
          /* User interaction data */
          `method`           VARCHAR(50)    NULL,
          `element`          VARCHAR(200)   NULL,
          `scroll_position`  INT            NULL,
          
          /* Search and query data */
          `query`            TEXT           NULL,
          `search_results`   INT            NULL,
          
          /* Product and commerce data */
          `product_id`       VARCHAR(26)    NULL,
          `product_name`     VARCHAR(255)   NULL,
          `category_id`      VARCHAR(26)    NULL,
          `category_name`    VARCHAR(100)   NULL,
          `quantity`         INT            NULL,
          `price`            DECIMAL(10,2)  NULL,
          `cart_value`       DECIMAL(10,2)  NULL,
          `amount`           DECIMAL(10,2)  NULL,
          `order_id`         VARCHAR(26)    NULL,
          `payment_method`   VARCHAR(50)    NULL,
          
          /* Filter and sorting data */
          `filter_type`      VARCHAR(50)    NULL,
          `filter_value`     VARCHAR(255)   NULL,
          `sort_by`          VARCHAR(50)    NULL,
          `sort_order`       ENUM('asc', 'desc') NULL,
          
          /* Contact and communication data */
          `contact_type`     ENUM('email', 'phone') NULL,
          `contact_value`    VARCHAR(255)   NULL,
          `message`          TEXT           NULL,
          
          /* OTP and verification data */
          `otp_type`         ENUM('email', 'phone', 'reset') NULL,
          `otp_attempts`     INT            NULL,
          
          /* Additional metadata */
          `user_agent`       TEXT           NULL,
          `ip_address`       VARCHAR(45)    NULL,
          `session_duration` INT            NULL,
          `extra_data`       JSON           NULL,
          
          PRIMARY KEY (`event_id`),
          INDEX `idx_events_session`     (`session_id`),
          INDEX `idx_events_name_time`   (`event_name`, `event_timestamp`),
          INDEX `idx_events_identifier`  (`identifier`),
          INDEX `idx_events_username`    (`username`),
          INDEX `idx_events_email`       (`email`),
          INDEX `idx_events_phone`       (`phone`),
          INDEX `idx_events_status`      (`status`),
          INDEX `idx_events_product`     (`product_id`),
          INDEX `idx_events_order`       (`order_id`),
          INDEX `idx_events_timestamp`   (`event_timestamp`),
          INDEX `idx_events_url`         (`url`(100)),
          CONSTRAINT `fk_events_session`
            FOREIGN KEY (`session_id`)
            REFERENCES `sessions` (`session_id`)
            ON UPDATE CASCADE
            ON DELETE CASCADE
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4
          COLLATE=utf8mb4_general_ci;
    ");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Table creation failed: ' . $e->getMessage()
    ]));
}

// 3) Load in‑memory sessions from JSON
$sessions = [];
if (file_exists($jsonFile)) {
    $contents = file_get_contents($jsonFile);
    $sessions = json_decode($contents, true);
    if (!is_array($sessions)) {
        $sessions = [];
    }
}

// 4) Split into expired vs remaining
$expiredSessions = [];
$remainingSessions = [];
$currentTime = time();

foreach ($sessions as $session) {
    $ts = isset($session['timestamp'])
        ? @strtotime($session['timestamp'])
        : false;

    if ($ts === false || ($currentTime - $ts) > $expirySeconds) {
        $expiredSessions[] = $session;
    } else {
        $remainingSessions[] = $session;
    }
}

$loggedCount = 0;

// 5) Persist each expired session + its logs to your DB
foreach ($expiredSessions as $session) {
    try {
        $pdo->beginTransaction();

        // --- Extract user information from loggedUser object ---
        $sessionID = $session['sessionID'];
        $lastUpdateMs = isset($session['timestamp'])
            ? (strtotime($session['timestamp']) * 1000)
            : ($currentTime * 1000);

        $lat = $session['coords']['latitude'] ?? null;
        $lng = $session['coords']['longitude'] ?? null;

        // Parse loggedUser object
        $loggedIn = false;
        $userId = null;
        $adminId = null;
        $username = null;
        $email = null;
        $phone = null;
        $isAdmin = false;
        $lastLogin = null;

        if (isset($session['loggedUser']) && is_array($session['loggedUser'])) {
            $loggedUser = $session['loggedUser'];

            $loggedIn = $loggedUser['logged_in'] ?? false;
            $isAdmin = $loggedUser['is_admin'] ?? false;
            $username = $loggedUser['username'] ?? null;
            $email = $loggedUser['email'] ?? null;
            $phone = $loggedUser['phone'] ?? null;

            // Parse last_login datetime
            if (isset($loggedUser['last_login']) && $loggedUser['last_login']) {
                $lastLogin = date('Y-m-d H:i:s', strtotime($loggedUser['last_login']));
            }

            // Assign to appropriate user type based on is_admin flag
            if ($loggedIn && isset($loggedUser['user_id'])) {
                if ($isAdmin) {
                    $adminId = $loggedUser['user_id'];
                } else {
                    $userId = $loggedUser['user_id'];
                }
            }
        }

        // --- Upsert into sessions ---
        $stmt = $pdo->prepare("
            INSERT INTO `sessions` (
                session_id, last_update_ms, ip_address, country,
                short_name, phone_code, browser, device,
                latitude, longitude, user_id, admin_id,
                logged_in, username, email, phone, is_admin, last_login
            ) VALUES (
                :session_id, :last_update_ms, :ip_address, :country,
                :short_name, :phone_code, :browser, :device,
                :latitude, :longitude, :user_id, :admin_id,
                :logged_in, :username, :email, :phone, :is_admin, :last_login
            )
            ON DUPLICATE KEY UPDATE
                last_update_ms = VALUES(last_update_ms),
                ip_address     = VALUES(ip_address),
                country        = VALUES(country),
                short_name     = VALUES(short_name),
                phone_code     = VALUES(phone_code),
                browser        = VALUES(browser),
                device         = VALUES(device),
                latitude       = VALUES(latitude),
                longitude      = VALUES(longitude),
                user_id        = VALUES(user_id),
                admin_id       = VALUES(admin_id),
                logged_in      = VALUES(logged_in),
                username       = VALUES(username),
                email          = VALUES(email),
                phone          = VALUES(phone),
                is_admin       = VALUES(is_admin),
                last_login     = VALUES(last_login)
        ");

        $stmt->execute([
            ':session_id' => $sessionID,
            ':last_update_ms' => $lastUpdateMs,
            ':ip_address' => $session['ipAddress'] ?? 'Unknown',
            ':country' => $session['country'] ?? 'Unknown',
            ':short_name' => strtoupper(substr($session['shortName'] ?? 'UN', 0, 2)),
            ':phone_code' => $session['phoneCode'] ?? '+000',
            ':browser' => $session['browser'] ?? 'Unknown',
            ':device' => $session['device'] ?? 'Unknown',
            ':latitude' => $lat,
            ':longitude' => $lng,
            ':user_id' => $userId,
            ':admin_id' => $adminId,
            ':logged_in' => $loggedIn,
            ':username' => $username,
            ':email' => $email,
            ':phone' => $phone,
            ':is_admin' => $isAdmin,
            ':last_login' => $lastLogin,
        ]);

        // --- Insert each log into session_events ---
        if (!empty($session['logs']) && is_array($session['logs'])) {
            $stmtLog = $pdo->prepare("
                INSERT INTO `session_events` (
                    event_id, session_id, event_name, event_timestamp,
                    referrer, url, active_navigation, page_title,
                    identifier, identifier_type, username, email, phone,
                    error_message, status, from_form, to_form, form_type,
                    step, action, method, element, scroll_position,
                    query, search_results, product_id, product_name,
                    category_id, category_name, quantity, price,
                    cart_value, amount, order_id, payment_method,
                    filter_type, filter_value, sort_by, sort_order,
                    contact_type, contact_value, message, otp_type,
                    otp_attempts, user_agent, ip_address, session_duration,
                    extra_data
                ) VALUES (
                    :event_id, :session_id, :event_name, :event_timestamp,
                    :referrer, :url, :active_navigation, :page_title,
                    :identifier, :identifier_type, :username, :email, :phone,
                    :error_message, :status, :from_form, :to_form, :form_type,
                    :step, :action, :method, :element, :scroll_position,
                    :query, :search_results, :product_id, :product_name,
                    :category_id, :category_name, :quantity, :price,
                    :cart_value, :amount, :order_id, :payment_method,
                    :filter_type, :filter_value, :sort_by, :sort_order,
                    :contact_type, :contact_value, :message, :otp_type,
                    :otp_attempts, :user_agent, :ip_address, :session_duration,
                    :extra_data
                )
            ");

            foreach ($session['logs'] as $log) {
                $logID = generateUlid();
                $evtTime = isset($log['timestamp'])
                    ? date('Y-m-d H:i:s.u', strtotime($log['timestamp']))
                    : null;

                // Extract status from event names
                $status = null;
                if (strpos($log['event'], '_success') !== false) {
                    $status = 'success';
                } elseif (strpos($log['event'], '_failed') !== false) {
                    $status = 'failed';
                } elseif (in_array($log['event'], ['login_identifier_submit', 'login_password_submit', 'register_username_submit'])) {
                    $status = 'pending';
                }

                // Normalize identifier type
                $identifierType = null;
                if (isset($log['identifierType'])) {
                    $identifierType = strtolower($log['identifierType']);
                    if (!in_array($identifierType, ['username', 'email', 'phone'])) {
                        $identifierType = null;
                    }
                }

                // Handle contact type for password reset events
                $contactType = null;
                if (isset($log['contactType'])) {
                    $contactType = strtolower($log['contactType']);
                    if (!in_array($contactType, ['email', 'phone'])) {
                        $contactType = null;
                    }
                }

                // Determine OTP type from event context
                $otpType = null;
                if (strpos($log['event'], 'email_otp') !== false || strpos($log['event'], 'register_email') !== false) {
                    $otpType = 'email';
                } elseif (strpos($log['event'], 'phone_otp') !== false || strpos($log['event'], 'register_phone') !== false) {
                    $otpType = 'phone';
                } elseif (strpos($log['event'], 'reset_otp') !== false || strpos($log['event'], 'password_reset') !== false) {
                    $otpType = 'reset';
                }

                // Prepare extra data for any additional fields not covered by specific columns
                $extraData = [];
                $knownFields = [
                    'event',
                    'timestamp',
                    'referrer',
                    'url',
                    'activeNavigation',
                    'pageTitle',
                    'identifier',
                    'identifierType',
                    'username',
                    'email',
                    'phone',
                    'errorMessage',
                    'fromForm',
                    'toForm',
                    'formType',
                    'step',
                    'action',
                    'method',
                    'element',
                    'scrollPosition',
                    'query',
                    'searchResults',
                    'productId',
                    'productName',
                    'categoryId',
                    'categoryName',
                    'quantity',
                    'price',
                    'cartValue',
                    'amount',
                    'orderId',
                    'paymentMethod',
                    'filterType',
                    'filterValue',
                    'sortBy',
                    'sortOrder',
                    'contactType',
                    'contactValue',
                    'message',
                    'otpType',
                    'otpAttempts',
                    'status'
                ];

                foreach ($log as $key => $value) {
                    if (!in_array($key, $knownFields) && $value !== null) {
                        $extraData[$key] = $value;
                    }
                }

                $stmtLog->execute([
                    ':event_id' => $logID,
                    ':session_id' => $sessionID,
                    ':event_name' => $log['event'] ?? null,
                    ':event_timestamp' => $evtTime,

                    // Navigation and page data
                    ':referrer' => $log['referrer'] ?? null,
                    ':url' => $log['url'] ?? null,
                    ':active_navigation' => $log['activeNavigation'] ?? null,
                    ':page_title' => $log['pageTitle'] ?? null,

                    // Authentication and user data
                    ':identifier' => $log['identifier'] ?? null,
                    ':identifier_type' => $identifierType,
                    ':username' => $log['username'] ?? null,
                    ':email' => $log['email'] ?? null,
                    ':phone' => $log['phone'] ?? null,
                    ':error_message' => $log['errorMessage'] ?? null,
                    ':status' => $status,

                    // Form interaction data
                    ':from_form' => $log['fromForm'] ?? null,
                    ':to_form' => $log['toForm'] ?? null,
                    ':form_type' => $log['formType'] ?? null,
                    ':step' => $log['step'] ?? null,
                    ':action' => $log['action'] ?? null,

                    // User interaction data
                    ':method' => $log['method'] ?? null,
                    ':element' => $log['element'] ?? null,
                    ':scroll_position' => $log['scrollPosition'] ?? null,

                    // Search and query data
                    ':query' => $log['query'] ?? null,
                    ':search_results' => $log['searchResults'] ?? null,

                    // Product and commerce data
                    ':product_id' => $log['productId'] ?? null,
                    ':product_name' => $log['productName'] ?? null,
                    ':category_id' => $log['categoryId'] ?? null,
                    ':category_name' => $log['categoryName'] ?? null,
                    ':quantity' => $log['quantity'] ?? null,
                    ':price' => $log['price'] ?? null,
                    ':cart_value' => $log['cartValue'] ?? null,
                    ':amount' => $log['amount'] ?? null,
                    ':order_id' => $log['orderId'] ?? null,
                    ':payment_method' => $log['paymentMethod'] ?? null,

                    // Filter and sorting data
                    ':filter_type' => $log['filterType'] ?? null,
                    ':filter_value' => $log['filterValue'] ?? null,
                    ':sort_by' => $log['sortBy'] ?? null,
                    ':sort_order' => (isset($log['sortOrder']) && in_array(strtolower($log['sortOrder']), ['asc', 'desc']))
                        ? strtolower($log['sortOrder']) : null,

                    // Contact and communication data
                    ':contact_type' => $contactType,
                    ':contact_value' => $log['contactValue'] ?? null,
                    ':message' => $log['message'] ?? null,

                    // OTP and verification data
                    ':otp_type' => $otpType,
                    ':otp_attempts' => $log['otpAttempts'] ?? null,

                    // Additional metadata
                    ':user_agent' => $log['userAgent'] ?? null,
                    ':ip_address' => $log['ipAddress'] ?? $session['ipAddress'] ?? null,
                    ':session_duration' => $log['sessionDuration'] ?? null,
                    ':extra_data' => !empty($extraData) ? json_encode($extraData) : null,
                ]);
            }
        }

        $pdo->commit();
        $loggedCount++;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Failed to log session {$sessionID}: " . $e->getMessage());
    }
}

// 6) Overwrite JSON file with only the still‑alive sessions
file_put_contents(
    $jsonFile,
    json_encode($remainingSessions, JSON_PRETTY_PRINT),
    LOCK_EX
);

// 7) Respond with detailed statistics
$totalEvents = 0;
$loggedInSessions = 0;
$adminSessions = 0;
$guestSessions = 0;

foreach ($expiredSessions as $session) {
    if (isset($session['logs']) && is_array($session['logs'])) {
        $totalEvents += count($session['logs']);
    }

    if (isset($session['loggedUser']) && is_array($session['loggedUser'])) {
        if ($session['loggedUser']['logged_in'] ?? false) {
            $loggedInSessions++;
            if ($session['loggedUser']['is_admin'] ?? false) {
                $adminSessions++;
            }
        }
    } else {
        $guestSessions++;
    }
}

echo json_encode([
    'status' => 'success',
    'message' => "Successfully processed {$loggedCount} expired session(s)",
    'statistics' => [
        'expired_sessions' => count($expiredSessions),
        'logged_sessions' => $loggedCount,
        'remaining_sessions' => count($remainingSessions),
        'total_events_logged' => $totalEvents,
        'logged_in_sessions' => $loggedInSessions,
        'admin_sessions' => $adminSessions,
        'guest_sessions' => $guestSessions,
        'processing_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    ]
]);
exit;
