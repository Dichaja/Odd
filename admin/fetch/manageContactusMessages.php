<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');

function getMessagesData($page = 1, $limit = 20, $startDate = null, $endDate = null, $period = 'month')
{
    global $pdo;

    try {
        // Set timezone for this session
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($startDate && $endDate) {
            $whereClause .= " AND DATE(CONVERT_TZ(c.created_at, '+00:00', '+03:00')) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $whereClause .= " AND DATE(CONVERT_TZ(c.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $whereClause .= " AND CONVERT_TZ(c.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $whereClause .= " AND CONVERT_TZ(c.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM contact_us c
            LEFT JOIN zzimba_users u ON c.user_id = u.id
            $whereClause
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                u.username,
                u.first_name,
                u.last_name,
                u.status as user_status,
                u.last_login,
                u.created_at as user_created_at
            FROM contact_us c
            LEFT JOIN zzimba_users u ON c.user_id = u.id
            $whereClause
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ");

        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessage = [
                'id' => $message['id'],
                'user_id' => $message['user_id'],
                'user_name' => $message['user_name'],
                'name' => $message['name'],
                'phone' => $message['phone'],
                'email' => $message['email'],
                'subject' => $message['subject'],
                'message' => $message['message'],
                'created_at' => $message['created_at']
            ];

            if ($message['user_id']) {
                $formattedMessage['user_info'] = [
                    'username' => $message['username'],
                    'first_name' => $message['first_name'],
                    'last_name' => $message['last_name'],
                    'status' => $message['user_status'],
                    'last_login' => $message['last_login'],
                    'created_at' => $message['user_created_at']
                ];
            }

            $formattedMessages[] = $formattedMessage;
        }

        $stats = getMessageStats($period, $startDate, $endDate);

        return [
            'success' => true,
            'data' => $formattedMessages,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'stats' => $stats
        ];

    } catch (PDOException $e) {
        error_log("Database error in getMessagesData: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function getSingleMessage($messageId)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                u.username,
                u.first_name,
                u.last_name,
                u.status as user_status,
                u.last_login,
                u.created_at as user_created_at,
                u.profile_pic_url
            FROM contact_us c
            LEFT JOIN zzimba_users u ON c.user_id = u.id
            WHERE c.id = ?
        ");

        $stmt->execute([$messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$message) {
            return [
                'success' => false,
                'error' => 'Message not found'
            ];
        }

        $formattedMessage = [
            'id' => $message['id'],
            'user_id' => $message['user_id'],
            'user_name' => $message['user_name'],
            'name' => $message['name'],
            'phone' => $message['phone'],
            'email' => $message['email'],
            'subject' => $message['subject'],
            'message' => $message['message'],
            'created_at' => $message['created_at']
        ];

        if ($message['user_id']) {
            $formattedMessage['user_info'] = [
                'username' => $message['username'],
                'first_name' => $message['first_name'],
                'last_name' => $message['last_name'],
                'status' => $message['user_status'],
                'last_login' => $message['last_login'],
                'created_at' => $message['user_created_at'],
                'profile_pic_url' => $message['profile_pic_url']
            ];
        }

        return [
            'success' => true,
            'data' => $formattedMessage
        ];

    } catch (PDOException $e) {
        error_log("Database error in getSingleMessage: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function getMessageStats($period = 'month', $startDate = null, $endDate = null)
{
    global $pdo;

    try {
        // Set timezone
        $pdo->exec("SET time_zone = '+03:00'");

        $totalStmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_us");
        $totalStmt->execute();
        $totalMessages = $totalStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $todayStmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_us WHERE DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))");
        $todayStmt->execute();
        $todayMessages = $todayStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $weekStmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_us WHERE CONVERT_TZ(created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)");
        $weekStmt->execute();
        $weekMessages = $weekStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $registeredStmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_us WHERE user_id IS NOT NULL");
        $registeredStmt->execute();
        $registeredUsers = $registeredStmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'total_messages' => (int) $totalMessages,
            'today_messages' => (int) $todayMessages,
            'week_messages' => (int) $weekMessages,
            'registered_users' => (int) $registeredUsers
        ];

    } catch (PDOException $e) {
        error_log("Database error in getMessageStats: " . $e->getMessage());
        return [
            'total_messages' => 0,
            'today_messages' => 0,
            'week_messages' => 0,
            'registered_users' => 0
        ];
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {
    case 'get':
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'month';

        $result = getMessagesData($page, $limit, $startDate, $endDate, $period);
        echo json_encode($result);
        break;

    case 'get_single':
        $messageId = $_GET['id'] ?? '';
        if (empty($messageId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Message ID is required'
            ]);
            break;
        }

        $result = getSingleMessage($messageId);
        echo json_encode($result);
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
}
?>