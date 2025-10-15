<?php
date_default_timezone_set('Africa/Kampala');
require_once __DIR__ . '/../config/config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id VARCHAR(26) PRIMARY KEY,
        type ENUM('signup','login','password_reset','store_update','visit_request','system','info') NOT NULL,
        title VARCHAR(255) NOT NULL,
        link_url VARCHAR(512) DEFAULT NULL,
        priority ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
        created_by VARCHAR(26) DEFAULT NULL,
        created_at DATETIME NOT NULL,
        INDEX(type),
        INDEX(created_at)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notification_targets (
        id VARCHAR(26) PRIMARY KEY,
        notification_id VARCHAR(26) NOT NULL,
        recipient_type ENUM('user','store','admin') NOT NULL,
        recipient_id VARCHAR(26) NOT NULL,
        message TEXT NOT NULL,
        is_seen TINYINT(1) NOT NULL DEFAULT 0,
        seen_at DATETIME DEFAULT NULL,
        is_dismissed TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        CONSTRAINT fk_nt_notification FOREIGN KEY (notification_id)
            REFERENCES notifications(id) ON DELETE CASCADE,
        INDEX(recipient_type, recipient_id, is_seen),
        INDEX(recipient_id, is_dismissed, is_seen, created_at),
        INDEX(recipient_id, created_at)
    )");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

class NotificationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function resolveCurrentPrincipal(): array
    {
        $sess = $_SESSION['user'] ?? [];
        $userId = $sess['user_id'] ?? null;
        $storeId = $_SESSION['store_session_id'] ?? null;
        $isAdmin = !empty($sess['is_admin']);
        return [$userId, $storeId, $isAdmin];
    }

    public function create(
        string $type,
        string $title,
        array $recipients,
        ?string $linkUrl = null,
        string $priority = 'normal',
        ?string $createdBy = null
    ): string {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->db->beginTransaction();
        $notificationId = generateUlid();
        $this->db->prepare(
            "INSERT INTO notifications (id,type,title,link_url,priority,created_by,created_at)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([$notificationId, $type, $title, $linkUrl, $priority, $createdBy, $now]);

        $insertTgt = $this->db->prepare(
            "INSERT INTO notification_targets
             (id,notification_id,recipient_type,recipient_id,message,is_seen,is_dismissed,created_at,updated_at)
             VALUES (?,?,?,?,?,0,0,?,?)"
        );

        foreach ($recipients as $r) {
            $insertTgt->execute([
                generateUlid(),
                $notificationId,
                $r['type'],
                $r['id'],
                $r['message'],
                $now,
                $now
            ]);
        }

        $this->db->commit();
        return $notificationId;
    }

    public function fetchForCurrent(int $limit = 20, int $offset = 0, ?string $since = null): array
    {
        [$userId, $storeId, $isAdmin] = $this->resolveCurrentPrincipal();
        if (!$userId) {
            return [];
        }

        $clauses = [];
        $params = [];

        $clauses[] = "(nt.recipient_type='user' AND nt.recipient_id=:uid)";
        $params[':uid'] = $userId;

        if ($storeId) {
            $clauses[] = "(nt.recipient_type='store' AND nt.recipient_id=:sid)";
            $params[':sid'] = $storeId;
        }

        if ($isAdmin) {
            $clauses[] = "(nt.recipient_type='admin')";
        }

        $sinceSql = '';
        if ($since) {
            $sinceSql = " AND nt.created_at > :since";
            $params[':since'] = $since;
        }

        $sql = "
            SELECT
                nt.id AS target_id,
                n.id AS notification_id,
                n.type,
                n.title,
                nt.message,
                n.link_url,
                n.priority,
                nt.created_at,
                nt.is_seen,
                nt.is_dismissed
            FROM notification_targets nt
            JOIN notifications n ON n.id = nt.notification_id
            WHERE (" . implode(' OR ', $clauses) . ")
              AND nt.is_dismissed=0
              $sinceSql
            ORDER BY nt.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnreadForCurrent(): int
    {
        [$userId, $storeId, $isAdmin] = $this->resolveCurrentPrincipal();
        if (!$userId) {
            return 0;
        }

        $clauses = [];
        $params = [];

        $clauses[] = "(recipient_type='user' AND recipient_id=:uid)";
        $params[':uid'] = $userId;

        if ($storeId) {
            $clauses[] = "(recipient_type='store' AND recipient_id=:sid)";
            $params[':sid'] = $storeId;
        }

        if ($isAdmin) {
            $clauses[] = "(recipient_type='admin')";
        }

        $sql = "
            SELECT COUNT(*)
            FROM notification_targets
            WHERE (" . implode(' OR ', $clauses) . ")
              AND is_dismissed=0
              AND is_seen=0
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function markSeen($targetIds): void
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        if (is_array($targetIds)) {
            if (empty($targetIds))
                return;
            $placeholders = implode(',', array_fill(0, count($targetIds), '?'));
            $params = array_merge([$now, $now], $targetIds);
            $sql = "UPDATE notification_targets
                    SET is_seen = 1, seen_at = ?, updated_at = ?
                    WHERE id IN ($placeholders)";
            $this->db->prepare($sql)->execute($params);
        } else {
            $sql = "UPDATE notification_targets
                    SET is_seen = 1, seen_at = ?, updated_at = ?
                    WHERE id = ?";
            $this->db->prepare($sql)->execute([$now, $now, $targetIds]);
        }
    }

    public function dismiss($targetIds): void
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        if (is_array($targetIds)) {
            if (empty($targetIds))
                return;
            $placeholders = implode(',', array_fill(0, count($targetIds), '?'));
            $params = array_merge([$now], $targetIds);
            $sql = "UPDATE notification_targets
                    SET is_dismissed = 1, updated_at = ?
                    WHERE id IN ($placeholders)";
            $this->db->prepare($sql)->execute($params);
        } else {
            $sql = "UPDATE notification_targets
                    SET is_dismissed = 1, updated_at = ?
                    WHERE id = ?";
            $this->db->prepare($sql)->execute([$now, $targetIds]);
        }
    }
}
