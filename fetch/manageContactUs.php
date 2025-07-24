<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Africa/Kampala');
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';
require_once __DIR__ . '/../sms/SMS.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `contact_us` (
          `id`          VARCHAR(26)    NOT NULL PRIMARY KEY,
          `user_id`     VARCHAR(26)    NULL,
          `user_name`   VARCHAR(255)   NULL,
          `name`        VARCHAR(255)   NULL,
          `phone`       VARCHAR(50)    NULL,
          `email`       VARCHAR(255)   NULL,
          `subject`     TEXT           NOT NULL,
          `message`     TEXT           NOT NULL,
          `created_at`  DATETIME       NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject)) {
        throw new Exception('Please provide a subject for your message.');
    }

    if (empty($message)) {
        throw new Exception('Please provide a message before submitting.');
    }

    if (strlen($subject) > 255) {
        throw new Exception('Subject must be less than 255 characters.');
    }

    if (strlen($message) > 5000) {
        throw new Exception('Message must be less than 5000 characters.');
    }

    $user = $_SESSION['user'] ?? [];
    $loggedIn = !empty($user['logged_in']);
    $isAdmin = !empty($user['is_admin']);

    if ($loggedIn && !$isAdmin) {
        $name = $user['username'] ?? '';
        $email = $user['email'] ?? null;
        $phone = $user['phone'] ?? null;
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            throw new Exception('Please provide your full name.');
        }

        if (empty($email)) {
            throw new Exception('Please provide your email address.');
        }

        if (empty($phone)) {
            throw new Exception('Please provide your phone number.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please provide a valid email address.');
        }

        if (strlen($name) > 255) {
            throw new Exception('Name must be less than 255 characters.');
        }

        if (strlen($email) > 255) {
            throw new Exception('Email address must be less than 255 characters.');
        }

        if (strlen($phone) > 50) {
            throw new Exception('Phone number must be less than 50 characters.');
        }
    }

    $id = generateUlid();
    $createdAt = (new DateTime('now', new DateTimeZone('Africa/Kampala')))
        ->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO contact_us
        (id, user_id, user_name, name, phone, email, subject, message, created_at)
        VALUES
        (:id, :user_id, :user_name, :name, :phone, :email, :subject, :message, :created_at)
    ");

    $stmt->execute([
        ':id' => $id,
        ':user_id' => $loggedIn ? $user['user_id'] : null,
        ':user_name' => $loggedIn ? $user['username'] : null,
        ':name' => $name,
        ':phone' => $phone,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message,
        ':created_at' => $createdAt,
    ]);

    $ns = new NotificationService($pdo);

    $admins = $pdo->query("SELECT id, phone FROM admin_users")
        ->fetchAll(PDO::FETCH_ASSOC);

    $adminRecipients = [];
    foreach ($admins as $admin) {
        $adminRecipients[] = [
            'type' => 'admin',
            'id' => $admin['id'],
            'message' => "New contact inquiry received from {$name}. Subject: {$subject}"
        ];

        if (!empty($admin['phone'])) {
            try {
                $smsText = "New contact message from {$name}. Subject: {$subject}. Please check your admin panel for details.";
                SMS::send($admin['phone'], $smsText);
            } catch (Exception $e) {
                error_log('SMS notification failed: ' . $e->getMessage());
            }
        }
    }

    if (!empty($adminRecipients)) {
        $ns->create(
            'contact_message',
            'New Contact Inquiry',
            $adminRecipients,
            null,
            'high',
            $loggedIn ? $user['user_id'] : null
        );
    }

    if ($loggedIn) {
        $userRecipients = [
            [
                'type' => 'user',
                'id' => $user['user_id'],
                'message' => "Thank you for contacting us. We have received your message regarding: {$subject}. Our team will respond within 1 hour."
            ]
        ];
        $ns->create(
            'info',
            'Message Received - We\'ll Be In Touch Soon',
            $userRecipients,
            null,
            'normal',
            $user['user_id']
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We have received your inquiry and will respond within 1 hour.'
    ]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>