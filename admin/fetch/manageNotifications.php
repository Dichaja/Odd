<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');

function generateNotificationId(): string
{
    return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 26);
}

function getNotificationsData($page = 1, $limit = 20, $startDate = null, $endDate = null, $period = 'week', $type = 'all', $priority = 'all', $sort = 'newest', $search = '')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($type !== 'all') {
            $whereClause .= " AND n.type = ?";
            $params[] = $type;
        }

        if ($priority !== 'all') {
            $whereClause .= " AND n.priority = ?";
            $params[] = $priority;
        }

        if (!empty($search)) {
            $whereClause .= " AND (n.title LIKE ? OR n.link_url LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $orderBy = "ORDER BY ";
        switch ($sort) {
            case 'newest':
                $orderBy .= "n.created_at DESC";
                break;
            case 'oldest':
                $orderBy .= "n.created_at ASC";
                break;
            case 'priority_desc':
                $orderBy .= "FIELD(n.priority, 'high', 'normal', 'low'), n.created_at DESC";
                break;
            case 'priority_asc':
                $orderBy .= "FIELD(n.priority, 'low', 'normal', 'high'), n.created_at DESC";
                break;
            case 'type':
                $orderBy .= "n.type ASC, n.created_at DESC";
                break;
            default:
                $orderBy .= "n.created_at DESC";
        }

        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT n.id) as total
            FROM notifications n
            $whereClause $dateFilter
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare("
            SELECT 
                n.id,
                n.type,
                n.title,
                n.link_url,
                n.priority,
                n.created_by,
                n.created_at,
                COUNT(nt.id) as recipient_count,
                COUNT(CASE WHEN nt.is_seen = 0 THEN 1 END) as unread_count,
                COUNT(CASE WHEN nt.is_seen = 1 THEN 1 END) as read_count,
                COUNT(CASE WHEN nt.is_dismissed = 1 THEN 1 END) as dismissed_count,
                ROUND((COUNT(CASE WHEN nt.is_seen = 1 THEN 1 END) / COUNT(nt.id)) * 100, 1) as read_percentage,
                MIN(nt.is_seen) as is_seen,
                CASE 
                    WHEN nt.recipient_type = 'admin' THEN CONCAT(au.first_name, ' ', au.last_name)
                    WHEN nt.recipient_type = 'user' THEN CONCAT(zu.first_name, ' ', zu.last_name)
                    WHEN nt.recipient_type = 'store' THEN vs.name
                    ELSE 'System'
                END as triggered_by_name,
                nt.recipient_type as triggered_by_type
            FROM notifications n
            LEFT JOIN notification_targets nt ON n.id = nt.notification_id
            LEFT JOIN admin_users au ON nt.recipient_type = 'admin' AND nt.recipient_id = au.id
            LEFT JOIN zzimba_users zu ON nt.recipient_type = 'user' AND nt.recipient_id = zu.id
            LEFT JOIN vendor_stores vs ON nt.recipient_type = 'store' AND nt.recipient_id = vs.id
            $whereClause $dateFilter
            GROUP BY n.id, n.type, n.title, n.link_url, n.priority, n.created_by, n.created_at, triggered_by_name, triggered_by_type
            $orderBy
            LIMIT ? OFFSET ?
        ");

        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($notifications as &$notification) {
            $notification['recipient_count'] = (int) $notification['recipient_count'];
            $notification['unread_count'] = (int) $notification['unread_count'];
            $notification['read_count'] = (int) $notification['read_count'];
            $notification['dismissed_count'] = (int) $notification['dismissed_count'];
            $notification['read_percentage'] = (float) $notification['read_percentage'];
            $notification['is_seen'] = (bool) $notification['is_seen'];
        }

        $stats = getNotificationStats($period, $startDate, $endDate);

        return [
            'success' => true,
            'data' => $notifications,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'stats' => $stats
        ];

    } catch (PDOException $e) {
        error_log("Database error in getNotificationsData: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

// Add new function to get all notification IDs for select all functionality
function getAllNotificationIds($startDate = null, $endDate = null, $period = 'week', $type = 'all', $priority = 'all', $search = '')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($type !== 'all') {
            $whereClause .= " AND n.type = ?";
            $params[] = $type;
        }

        if ($priority !== 'all') {
            $whereClause .= " AND n.priority = ?";
            $params[] = $priority;
        }

        if (!empty($search)) {
            $whereClause .= " AND (n.title LIKE ? OR n.link_url LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $stmt = $pdo->prepare("
            SELECT DISTINCT n.id
            FROM notifications n
            $whereClause $dateFilter
            ORDER BY n.created_at DESC
        ");

        $stmt->execute($params);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return [
            'success' => true,
            'ids' => $ids
        ];

    } catch (PDOException $e) {
        error_log("Database error in getAllNotificationIds: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

// Add functions for marking selected notifications as read/unread
function markSelectedNotificationsAsRead($notificationIds)
{
    global $pdo;

    try {
        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';

        $stmt = $pdo->prepare("
            UPDATE notification_targets 
            SET is_seen = 1, seen_at = NOW(), updated_at = NOW()
            WHERE notification_id IN ($placeholders) AND is_seen = 0
        ");
        $stmt->execute($notificationIds);

        return [
            'success' => true,
            'updated_count' => $stmt->rowCount()
        ];

    } catch (PDOException $e) {
        error_log("Database error in markSelectedNotificationsAsRead: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to mark notifications as read'
        ];
    }
}

function markSelectedNotificationsAsUnread($notificationIds)
{
    global $pdo;

    try {
        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';

        $stmt = $pdo->prepare("
            UPDATE notification_targets 
            SET is_seen = 0, seen_at = NULL, updated_at = NOW()
            WHERE notification_id IN ($placeholders) AND is_seen = 1
        ");
        $stmt->execute($notificationIds);

        return [
            'success' => true,
            'updated_count' => $stmt->rowCount()
        ];

    } catch (PDOException $e) {
        error_log("Database error in markSelectedNotificationsAsUnread: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to mark notifications as unread'
        ];
    }
}

function getNotificationDetails($notificationId)
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $stmt = $pdo->prepare("
            SELECT 
                n.id,
                n.type,
                n.title,
                n.link_url,
                n.priority,
                n.created_by,
                n.created_at
            FROM notifications n
            WHERE n.id = ?
        ");

        $stmt->execute([$notificationId]);
        $notification = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$notification) {
            return [
                'success' => false,
                'error' => 'Notification not found'
            ];
        }

        // Get recipients and their status
        $recipientsStmt = $pdo->prepare("
            SELECT 
                nt.id,
                nt.recipient_type,
                nt.recipient_id,
                nt.message,
                nt.is_seen,
                nt.seen_at,
                nt.is_dismissed,
                nt.created_at,
                nt.updated_at
            FROM notification_targets nt
            WHERE nt.notification_id = ?
            ORDER BY nt.created_at DESC
        ");
        $recipientsStmt->execute([$notificationId]);
        $recipients = $recipientsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get summary counts
        $summaryStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as recipient_count,
                COUNT(CASE WHEN is_seen = 0 THEN 1 END) as unread_count,
                COUNT(CASE WHEN is_seen = 1 THEN 1 END) as read_count,
                COUNT(CASE WHEN is_dismissed = 1 THEN 1 END) as dismissed_count
            FROM notification_targets
            WHERE notification_id = ?
        ");
        $summaryStmt->execute([$notificationId]);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

        $notification['recipients'] = $recipients;
        $notification['recipient_count'] = (int) $summary['recipient_count'];
        $notification['unread_count'] = (int) $summary['unread_count'];
        $notification['read_count'] = (int) $summary['read_count'];
        $notification['dismissed_count'] = (int) $summary['dismissed_count'];

        // Get the first recipient's message for display
        if (!empty($recipients)) {
            $notification['message'] = $recipients[0]['message'];
        }

        foreach ($notification['recipients'] as &$recipient) {
            $recipient['is_seen'] = (bool) $recipient['is_seen'];
            $recipient['is_dismissed'] = (bool) $recipient['is_dismissed'];
        }

        return [
            'success' => true,
            'data' => $notification
        ];

    } catch (PDOException $e) {
        error_log("Database error in getNotificationDetails: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred: ' . $e->getMessage()
        ];
    }
}

function getChartData($startDate = null, $endDate = null, $period = 'week')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $dateCondition = "1=1";
        $dateParams = [];

        if ($startDate && $endDate) {
            $dateCondition = "DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) BETWEEN ? AND ?";
            $dateParams = [$startDate, $endDate];
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateCondition = "DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateCondition = "CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateCondition = "CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        // Generate timeline with proper intervals
        $timeline = generateNotificationTimelineData($pdo, $period, $startDate, $endDate, $dateCondition, $dateParams);

        // Get type data
        $typeStmt = $pdo->prepare("
            SELECT 
                n.type,
                COUNT(*) as count
            FROM notifications n
            WHERE $dateCondition
            GROUP BY n.type
            ORDER BY count DESC
        ");
        $typeStmt->execute($dateParams);
        $typeData = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

        $types = [
            'labels' => array_map(function ($type) {
                return ucfirst(str_replace('_', ' ', $type['type']));
            }, $typeData),
            'values' => array_map('intval', array_column($typeData, 'count'))
        ];

        return [
            'success' => true,
            'timeline' => $timeline,
            'types' => $types
        ];

    } catch (PDOException $e) {
        error_log("Database error in getChartData: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function generateNotificationTimelineData($pdo, $period, $startDate, $endDate, $dateCondition, $dateParams)
{
    // Get actual data from database
    if ($period === 'today') {
        // For daily: show 2-hour intervals
        $stmt = $pdo->prepare("
            SELECT 
                HOUR(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) as hour_val,
                COUNT(*) as count
            FROM notifications n
            WHERE $dateCondition
            GROUP BY HOUR(CONVERT_TZ(n.created_at, '+00:00', '+03:00'))
            ORDER BY hour_val ASC
        ");
        $stmt->execute($dateParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create 2-hour intervals (0-1, 2-3, 4-5, etc.)
        $intervals = [];
        $values = [];

        for ($i = 0; $i < 24; $i += 2) {
            $intervalLabel = sprintf("%02d:00-%02d:59", $i, $i + 1);
            $intervals[] = $intervalLabel;

            $intervalCount = 0;
            foreach ($data as $row) {
                if ($row['hour_val'] >= $i && $row['hour_val'] <= $i + 1) {
                    $intervalCount += $row['count'];
                }
            }
            $values[] = $intervalCount;
        }

        return [
            'labels' => $intervals,
            'values' => $values
        ];

    } elseif ($period === 'week') {
        // For weekly: show each day of the week
        $stmt = $pdo->prepare("
            SELECT 
                DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) as date,
                COUNT(*) as count
            FROM notifications n
            WHERE $dateCondition
            GROUP BY DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00'))
            ORDER BY date ASC
        ");
        $stmt->execute($dateParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create array indexed by date
        $dataByDate = [];
        foreach ($data as $row) {
            $dataByDate[$row['date']] = $row;
        }

        // Generate last 7 days
        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dayName = date('D', strtotime($date));

            $labels[] = $dayName;
            $values[] = isset($dataByDate[$date]) ? (int) $dataByDate[$date]['count'] : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];

    } else {
        // For monthly or custom: show by date
        $stmt = $pdo->prepare("
            SELECT 
                DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) as date,
                COUNT(*) as count
            FROM notifications n
            WHERE $dateCondition
            GROUP BY DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00'))
            ORDER BY date ASC
        ");
        $stmt->execute($dateParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'labels' => array_column($data, 'date'),
            'values' => array_map('intval', array_column($data, 'count'))
        ];
    }
}

function getNotificationStats($period = 'week', $startDate = null, $endDate = null)
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $dateCondition = "1=1";
        $dateParams = [];

        if ($startDate && $endDate) {
            $dateCondition = "DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) BETWEEN ? AND ?";
            $dateParams = [$startDate, $endDate];
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateCondition = "DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateCondition = "CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateCondition = "CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $totalNotificationsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications n WHERE $dateCondition");
        $totalNotificationsStmt->execute($dateParams);
        $totalNotifications = $totalNotificationsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $todayNotificationsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications n WHERE DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))");
        $todayNotificationsStmt->execute();
        $todayNotifications = $todayNotificationsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $highPriorityStmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications n WHERE n.priority = 'high' AND $dateCondition");
        $highPriorityStmt->execute($dateParams);
        $highPriorityNotifications = $highPriorityStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $unreadStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT nt.notification_id) as count 
            FROM notification_targets nt 
            JOIN notifications n ON nt.notification_id = n.id 
            WHERE nt.is_seen = 0 AND $dateCondition
        ");
        $unreadStmt->execute($dateParams);
        $unreadNotifications = $unreadStmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'total_notifications' => (int) $totalNotifications,
            'today_notifications' => (int) $todayNotifications,
            'high_priority_notifications' => (int) $highPriorityNotifications,
            'unread_notifications' => (int) $unreadNotifications
        ];

    } catch (PDOException $e) {
        error_log("Database error in getNotificationStats: " . $e->getMessage());
        return [
            'total_notifications' => 0,
            'today_notifications' => 0,
            'high_priority_notifications' => 0,
            'unread_notifications' => 0
        ];
    }
}

function createNotification($type, $title, $message, $linkUrl = null, $priority = 'normal', $recipientType = 'admin', $createdBy = null)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $notificationId = generateNotificationId();
        $now = date('Y-m-d H:i:s');

        // Insert notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (id, type, title, link_url, priority, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$notificationId, $type, $title, $linkUrl, $priority, $createdBy, $now]);

        // Get recipients based on type
        $recipients = getRecipientsByType($recipientType);

        // Insert notification targets
        foreach ($recipients as $recipient) {
            $targetId = generateNotificationId();
            $targetStmt = $pdo->prepare("
                INSERT INTO notification_targets (id, notification_id, recipient_type, recipient_id, message, is_seen, seen_at, is_dismissed, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 0, NULL, 0, ?, ?)
            ");
            $targetStmt->execute([$targetId, $notificationId, $recipientType, $recipient['id'], $message, $now, $now]);
        }

        $pdo->commit();

        return [
            'success' => true,
            'notification_id' => $notificationId,
            'recipients_count' => count($recipients)
        ];

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in createNotification: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to create notification'
        ];
    }
}

function getRecipientsByType($recipientType)
{
    global $pdo;

    try {
        switch ($recipientType) {
            case 'admin':
                // Get all admin users - adjust this query based on your user system
                $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'user':
                // Get all regular users
                $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'user' AND status = 'active'");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            case 'store':
                // Get all stores
                $stmt = $pdo->prepare("SELECT id FROM stores WHERE status = 'active'");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);

            default:
                return [];
        }
    } catch (PDOException $e) {
        error_log("Database error in getRecipientsByType: " . $e->getMessage());
        return [];
    }
}

function deleteNotification($notificationId)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Delete notification targets first
        $stmt = $pdo->prepare("DELETE FROM notification_targets WHERE notification_id = ?");
        $stmt->execute([$notificationId]);

        // Delete notification
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->execute([$notificationId]);

        $pdo->commit();

        return [
            'success' => true
        ];

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in deleteNotification: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to delete notification'
        ];
    }
}

function markNotificationAsRead($notificationId)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE notification_targets 
            SET is_seen = 1, seen_at = NOW(), updated_at = NOW()
            WHERE notification_id = ? AND is_seen = 0
        ");
        $stmt->execute([$notificationId]);

        return [
            'success' => true,
            'updated_count' => $stmt->rowCount()
        ];

    } catch (PDOException $e) {
        error_log("Database error in markNotificationAsRead: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to mark notification as read'
        ];
    }
}

function markAllNotificationsAsRead()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            UPDATE notification_targets 
            SET is_seen = 1, seen_at = NOW(), updated_at = NOW()
            WHERE is_seen = 0
        ");
        $stmt->execute();

        return [
            'success' => true,
            'updated_count' => $stmt->rowCount()
        ];

    } catch (PDOException $e) {
        error_log("Database error in markAllNotificationsAsRead: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to mark all notifications as read'
        ];
    }
}

function bulkDeleteNotifications($notificationIds)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';

        // Delete notification targets first
        $stmt = $pdo->prepare("DELETE FROM notification_targets WHERE notification_id IN ($placeholders)");
        $stmt->execute($notificationIds);

        // Delete notifications
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id IN ($placeholders)");
        $stmt->execute($notificationIds);

        $pdo->commit();

        return [
            'success' => true,
            'deleted_count' => count($notificationIds)
        ];

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in bulkDeleteNotifications: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to delete notifications'
        ];
    }
}

function exportNotificationsData($startDate = null, $endDate = null, $period = 'week', $type = 'all', $priority = 'all', $status = 'all', $sort = 'newest')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE 1=1";
        $params = [];

        if ($type !== 'all') {
            $whereClause .= " AND n.type = ?";
            $params[] = $type;
        }

        if ($priority !== 'all') {
            $whereClause .= " AND n.priority = ?";
            $params[] = $priority;
        }

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(n.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(n.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $statusFilter = "";
        if ($status !== 'all') {
            switch ($status) {
                case 'unread':
                    $statusFilter = "AND EXISTS(SELECT 1 FROM notification_targets nt WHERE nt.notification_id = n.id AND nt.is_seen = 0)";
                    break;
                case 'read':
                    $statusFilter = "AND NOT EXISTS(SELECT 1 FROM notification_targets nt WHERE nt.notification_id = n.id AND nt.is_seen = 0)";
                    break;
                case 'dismissed':
                    $statusFilter = "AND EXISTS(SELECT 1 FROM notification_targets nt WHERE nt.notification_id = n.id AND nt.is_dismissed = 1)";
                    break;
            }
        }

        $orderBy = "ORDER BY ";
        switch ($sort) {
            case 'newest':
                $orderBy .= "n.created_at DESC";
                break;
            case 'oldest':
                $orderBy .= "n.created_at ASC";
                break;
            case 'priority_desc':
                $orderBy .= "FIELD(n.priority, 'high', 'normal', 'low'), n.created_at DESC";
                break;
            case 'priority_asc':
                $orderBy .= "FIELD(n.priority, 'low', 'normal', 'high'), n.created_at DESC";
                break;
            case 'type':
                $orderBy .= "n.type ASC, n.created_at DESC";
                break;
            default:
                $orderBy .= "n.created_at DESC";
        }

        $stmt = $pdo->prepare("
            SELECT 
                n.id,
                n.type,
                n.title,
                n.link_url,
                n.priority,
                n.created_by,
                n.created_at,
                COUNT(nt.id) as recipient_count,
                COUNT(CASE WHEN nt.is_seen = 0 THEN 1 END) as unread_count,
                COUNT(CASE WHEN nt.is_seen = 1 THEN 1 END) as read_count,
                COUNT(CASE WHEN nt.is_dismissed = 1 THEN 1 END) as dismissed_count,
                ROUND((COUNT(CASE WHEN nt.is_seen = 1 THEN 1 END) / COUNT(nt.id)) * 100, 1) as read_percentage
            FROM notifications n
            LEFT JOIN notification_targets nt ON n.id = nt.notification_id
            $whereClause $dateFilter $statusFilter
            GROUP BY n.id, n.type, n.title, n.link_url, n.priority, n.created_by, n.created_at
            $orderBy
        ");

        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="system_notifications_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Notification ID',
            'Type',
            'Title',
            'Priority',
            'Recipients',
            'Read Count',
            'Unread Count',
            'Dismissed Count',
            'Read Percentage (%)',
            'Link URL',
            'Created By',
            'Created At'
        ]);

        foreach ($notifications as $notification) {
            fputcsv($output, [
                $notification['id'],
                ucfirst(str_replace('_', ' ', $notification['type'])),
                $notification['title'],
                ucfirst($notification['priority']),
                $notification['recipient_count'],
                $notification['read_count'],
                $notification['unread_count'],
                $notification['dismissed_count'],
                $notification['read_percentage'],
                $notification['link_url'] ?: 'None',
                $notification['created_by'] ?: 'System',
                $notification['created_at']
            ]);
        }

        fclose($output);
        exit;

    } catch (PDOException $e) {
        error_log("Database error in exportNotificationsData: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Export failed'
        ]);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_notifications';

switch ($action) {
    case 'get_notifications':
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'week';
        $type = $_GET['type'] ?? 'all';
        $priority = $_GET['priority'] ?? 'all';
        $sort = $_GET['sort'] ?? 'newest';
        $search = $_GET['search'] ?? '';

        $result = getNotificationsData($page, $limit, $startDate, $endDate, $period, $type, $priority, $sort, $search);
        echo json_encode($result);
        break;

    case 'get_notification_details':
        $notificationId = $_GET['id'] ?? '';
        if (empty($notificationId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification ID is required'
            ]);
            break;
        }

        $result = getNotificationDetails($notificationId);
        echo json_encode($result);
        break;

    case 'get_chart_data':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'week';

        $result = getChartData($startDate, $endDate, $period);
        echo json_encode($result);
        break;

    case 'create_notification':
        $type = $_POST['type'] ?? '';
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $linkUrl = $_POST['link_url'] ?? null;
        $priority = $_POST['priority'] ?? 'normal';
        $recipientType = $_POST['recipient_type'] ?? 'admin';
        $createdBy = $_SESSION['admin_id'] ?? null; // Adjust based on your session management

        if (empty($type) || empty($title) || empty($message)) {
            echo json_encode([
                'success' => false,
                'error' => 'Type, title, and message are required'
            ]);
            break;
        }

        $result = createNotification($type, $title, $message, $linkUrl, $priority, $recipientType, $createdBy);
        echo json_encode($result);
        break;

    case 'delete_notification':
        $notificationId = $_POST['id'] ?? '';
        if (empty($notificationId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification ID is required'
            ]);
            break;
        }

        $result = deleteNotification($notificationId);
        echo json_encode($result);
        break;

    case 'mark_as_read':
        $notificationId = $_POST['id'] ?? '';
        if (empty($notificationId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification ID is required'
            ]);
            break;
        }

        $result = markNotificationAsRead($notificationId);
        echo json_encode($result);
        break;

    case 'mark_all_read':
        $result = markAllNotificationsAsRead();
        echo json_encode($result);
        break;

    case 'bulk_delete':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (empty($ids) || !is_array($ids)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification IDs are required'
            ]);
            break;
        }

        $result = bulkDeleteNotifications($ids);
        echo json_encode($result);
        break;

    case 'export':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'week';
        $type = $_GET['type'] ?? 'all';
        $priority = $_GET['priority'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        $sort = $_GET['sort'] ?? 'newest';

        exportNotificationsData($startDate, $endDate, $period, $type, $priority, $status, $sort);
        break;
    case 'get_all_notification_ids':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'week';
        $type = $_GET['type'] ?? 'all';
        $priority = $_GET['priority'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $result = getAllNotificationIds($startDate, $endDate, $period, $type, $priority, $search);
        echo json_encode($result);
        break;

    case 'mark_selected_read':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (empty($ids) || !is_array($ids)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification IDs are required'
            ]);
            break;
        }

        $result = markSelectedNotificationsAsRead($ids);
        echo json_encode($result);
        break;

    case 'mark_selected_unread':
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (empty($ids) || !is_array($ids)) {
            echo json_encode([
                'success' => false,
                'error' => 'Notification IDs are required'
            ]);
            break;
        }

        $result = markSelectedNotificationsAsUnread($ids);
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