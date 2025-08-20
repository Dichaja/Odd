<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';
require_once __DIR__ . '/../../lib/NotificationService.php';
require_once __DIR__ . '/../../sms/SMS.php';

use ZzimbaCreditModule\CreditService;

$notificationService = new NotificationService($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

createSMSTables();

switch ($action) {
    case 'purchaseSmsCredits':
        handlePurchaseSmsCredits();
        break;
    case 'getSmsStats':
        handleGetSmsStats();
        break;
    case 'getWalletBalance':
        handleGetWalletBalance();
        break;
    case 'sendSms':
        handleSendSms();
        break;
    case 'getSmsHistory':
        handleGetSmsHistory();
        break;
    case 'getSmsTemplates':
        handleGetSmsTemplates();
        break;
    case 'saveTemplate':
        handleSaveTemplate();
        break;
    case 'deleteTemplate':
        handleDeleteTemplate();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function createSMSTables()
{
    global $pdo;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_wallet` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `owner_type` enum('USER','VENDOR') NOT NULL,
            `user_id` char(26) DEFAULT NULL,
            `vendor_id` char(26) DEFAULT NULL,
            `current_balance` int(11) NOT NULL DEFAULT 0,
            `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_owner` (`owner_type`, `user_id`, `vendor_id`),
            INDEX `idx_owner_type` (`owner_type`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_history` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `vendor_id` char(26) DEFAULT NULL,
            `user_id` char(26) DEFAULT NULL,
            `sms_wallet_id` char(26) DEFAULT NULL,
            `transaction_id` char(26) DEFAULT NULL,
            `message` text NOT NULL,
            `recipients` json NOT NULL,
            `recipient_count` int(11) NOT NULL,
            `sms_parts` int(11) NOT NULL DEFAULT 1,
            `sms_rate` decimal(10,2) NOT NULL,
            `total_cost` decimal(10,2) NOT NULL,
            `credits_used` int(11) NOT NULL,
            `status` enum('sent','scheduled','failed','cancelled') NOT NULL DEFAULT 'sent',
            `type` enum('single','bulk') NOT NULL DEFAULT 'single',
            `sent_at` datetime DEFAULT NULL,
            `scheduled_at` datetime DEFAULT NULL,
            `balance_before` int(11) NOT NULL DEFAULT 0,
            `balance_after` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_sms_wallet_id` (`sms_wallet_id`),
            INDEX `idx_transaction_id` (`transaction_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_sent_at` (`sent_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_templates` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `vendor_id` char(26) DEFAULT NULL,
            `user_id` char(26) DEFAULT NULL,
            `name` varchar(200) NOT NULL,
            `message` text NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_topup_history` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `sms_wallet_id` char(26) NOT NULL,
            `vendor_id` char(26) DEFAULT NULL,
            `user_id` char(26) DEFAULT NULL,
            `transaction_id` char(26) DEFAULT NULL,
            `amount_paid` decimal(10,2) NOT NULL,
            `sms_rate` decimal(10,2) NOT NULL,
            `credits_purchased` int(11) NOT NULL,
            `status` enum('completed','pending','failed') NOT NULL DEFAULT 'completed',
            `balance_before` int(11) NOT NULL DEFAULT 0,
            `balance_after` int(11) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sms_wallet_id` (`sms_wallet_id`),
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_transaction_id` (`transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $pdo->exec("ALTER TABLE zzimba_sms_history MODIFY vendor_id char(26) NULL");
        $pdo->exec("ALTER TABLE zzimba_sms_templates MODIFY vendor_id char(26) NULL");
        $pdo->exec("ALTER TABLE zzimba_sms_topup_history MODIFY vendor_id char(26) NULL");
    } catch (Exception $e) {
        error_log("Error creating SMS tables: " . $e->getMessage());
    }
}

function getSmsRate()
{
    global $pdo;
    try {
        $appKey = 'users';
        $sql = "SELECT setting_value FROM zzimba_credit_settings 
                WHERE setting_key='sms_cost' AND status='active' AND category='sms'
                  AND applicable_to IN ('all', ?)
                ORDER BY (applicable_to='all') DESC, (applicable_to=?) DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$appKey, $appKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($row['setting_value'] ?? 0);
    } catch (Exception $e) {
        error_log("Error getting SMS rate: " . $e->getMessage());
        return 0;
    }
}

function getUserWallet($userId)
{
    global $pdo;
    try {
        $sql = "SELECT * FROM zzimba_wallets 
                WHERE user_id = ? AND owner_type = 'USER' AND status = 'active'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting user wallet: " . $e->getMessage());
        return null;
    }
}

function getSmsWalletForUser($userId, $createIfNotExists = true)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM zzimba_sms_wallet WHERE owner_type='USER' AND user_id=? AND vendor_id IS NULL LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row && $createIfNotExists) {
            $id = generateUlid();
            $ins = $pdo->prepare("INSERT INTO zzimba_sms_wallet (id, owner_type, user_id, vendor_id, current_balance, status, created_at, updated_at) VALUES (?, 'USER', ?, NULL, 0, 'active', NOW(), NOW())");
            $ins->execute([$id, $userId]);
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $row;
    } catch (Exception $e) {
        error_log("Error getting/creating user SMS wallet: " . $e->getMessage());
        return null;
    }
}

function updateSmsWalletBalance($smsWalletId, $amountChange)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT current_balance FROM zzimba_sms_wallet WHERE id = ? FOR UPDATE");
        $stmt->execute([$smsWalletId]);
        $curr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$curr)
            return false;
        $before = (int) $curr['current_balance'];
        $after = $before + $amountChange;
        if ($after < 0)
            return false;
        $upd = $pdo->prepare("UPDATE zzimba_sms_wallet SET current_balance=?, updated_at=NOW() WHERE id=?");
        $upd->execute([$after, $smsWalletId]);
        return ['balance_before' => $before, 'balance_after' => $after, 'amount_change' => $amountChange];
    } catch (Exception $e) {
        error_log("Error updating SMS wallet: " . $e->getMessage());
        return false;
    }
}

function handlePurchaseSmsCredits()
{
    global $pdo, $notificationService;
    $userId = $_SESSION['user']['user_id'] ?? null;
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        return;
    }
    if ($amount < 1000) {
        echo json_encode(['success' => false, 'message' => 'Minimum top-up amount is Sh. 1,000']);
        return;
    }

    $wallet = getUserWallet($userId);
    if (!$wallet) {
        echo json_encode(['success' => false, 'message' => 'User wallet not found']);
        return;
    }
    $walletBalance = (float) $wallet['current_balance'];
    if ($walletBalance < $amount) {
        echo json_encode([
            'success' => false,
            'message' => "Insufficient wallet balance. Need Sh. {$amount}, have Sh. {$walletBalance}",
            'data' => [
                'required_amount' => $amount,
                'wallet_balance' => $walletBalance,
                'topup_url' => BASE_URL . 'account/zzimba-credit'
            ]
        ]);
        return;
    }

    $smsWallet = getSmsWalletForUser($userId, true);
    if (!$smsWallet) {
        echo json_encode(['success' => false, 'message' => 'Failed to create SMS wallet']);
        return;
    }

    $payload = [
        'wallet_id' => $wallet['wallet_id'],
        'owner_type' => 'USER',
        'amount' => $amount,
        'vendor_id' => null,
        'user_id' => $userId
    ];

    $result = CreditService::purchaseSmsCredits($payload);
    if (!$result['success']) {
        echo json_encode($result);
        return;
    }

    try {
        $pdo->beginTransaction();
        $smsRate = getSmsRate();
        $creditsPurchased = $smsRate > 0 ? floor($amount / $smsRate) : 0;

        $balanceUpdate = updateSmsWalletBalance($smsWallet['id'], $creditsPurchased);
        if (!$balanceUpdate) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to update SMS wallet balance']);
            return;
        }

        $topupId = generateUlid();
        $stmt = $pdo->prepare("INSERT INTO zzimba_sms_topup_history
            (id, sms_wallet_id, vendor_id, user_id, transaction_id, amount_paid, sms_rate, credits_purchased, status, balance_before, balance_after, created_at)
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'completed', ?, ?, NOW())");
        $stmt->execute([
            $topupId,
            $smsWallet['id'],
            $userId,
            $result['transaction_id'] ?? null,
            $amount,
            $smsRate,
            $creditsPurchased,
            $balanceUpdate['balance_before'],
            $balanceUpdate['balance_after']
        ]);

        $pdo->commit();

        $notificationService->create(
            'system',
            "Purchased {$creditsPurchased} SMS credits",
            [
                ['type' => 'admin', 'id' => '', 'message' => "User {$userId} purchased {$creditsPurchased} SMS credits"],
                ['type' => 'user', 'id' => $userId, 'message' => "You purchased {$creditsPurchased} SMS credits"]
            ],
            null,
            'normal',
            $userId
        );

        echo json_encode([
            'success' => true,
            'message' => "Successfully purchased {$creditsPurchased} SMS credits",
            'data' => [
                'credits_purchased' => $creditsPurchased,
                'amount_paid' => $amount,
                'transaction_id' => $result['transaction_id'] ?? null,
                'sms_rate' => $smsRate,
                'new_balance' => $balanceUpdate['balance_after'],
                'sms_wallet_id' => $smsWallet['id']
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error processing user SMS credit purchase: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to process SMS credit purchase']);
    }
}

function handleGetSmsStats()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    try {
        $smsWallet = getSmsWalletForUser($userId, true);
        $currentCredits = $smsWallet ? (int) $smsWallet['current_balance'] : 0;

        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) sent_count, SUM(credits_used) credits_used, SUM(total_cost) total_cost
                               FROM zzimba_sms_history
                               WHERE user_id = ? AND vendor_id IS NULL AND DATE(sent_at) = ? AND status='sent'");
        $stmt->execute([$userId, $today]);
        $todayStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['sent_count' => 0, 'credits_used' => 0, 'total_cost' => 0];

        $stmt = $pdo->prepare("SELECT COUNT(*) scheduled_count FROM zzimba_sms_history
                               WHERE user_id = ? AND vendor_id IS NULL AND status='scheduled'");
        $stmt->execute([$userId]);
        $scheduled = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['scheduled_count' => 0];

        echo json_encode([
            'success' => true,
            'data' => [
                'current_credits' => (int) $currentCredits,
                'sent_today' => (int) $todayStats['sent_count'],
                'sent_today_credits' => (int) $todayStats['credits_used'],
                'sent_today_cost' => (float) $todayStats['total_cost'],
                'scheduled_count' => (int) $scheduled['scheduled_count']
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error getting user SMS stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
    }
}

function handleGetWalletBalance()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    try {
        $wallet = getUserWallet($userId);
        if (!$wallet) {
            echo json_encode(['success' => false, 'message' => 'User wallet not found']);
            return;
        }
        $balance = (float) $wallet['current_balance'];
        $smsRate = getSmsRate();
        $equivalentCredits = $smsRate > 0 ? floor($balance / $smsRate) : 0;

        echo json_encode([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'equivalent_credits' => $equivalentCredits,
                'sms_rate' => $smsRate
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error getting user wallet balance: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get wallet balance']);
    }
}

function handleSendSms()
{
    global $pdo, $notificationService;
    $userId = $_SESSION['user']['user_id'] ?? null;

    $message = trim($_POST['message'] ?? '');
    $recipients = json_decode($_POST['recipients'] ?? '[]', true);
    $sendType = $_POST['send_type'] ?? 'single';
    $sendOption = $_POST['send_option'] ?? 'now';
    $scheduledAt = $_POST['scheduled_at'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    if ($message === '' || empty($recipients)) {
        echo json_encode(['success' => false, 'message' => 'Message and recipients are required']);
        return;
    }

    $smsWallet = getSmsWalletForUser($userId, true);
    if (!$smsWallet) {
        echo json_encode(['success' => false, 'message' => 'SMS wallet not found']);
        return;
    }

    $smsRate = getSmsRate();
    $smsParts = max(1, ceil(strlen($message) / 160));
    $creditsNeeded = count($recipients) * $smsParts;
    $currentBalance = (int) $smsWallet['current_balance'];

    if ($sendOption === 'now' && $creditsNeeded > $currentBalance) {
        echo json_encode(['success' => false, 'message' => "Insufficient credits. Need {$creditsNeeded}, have {$currentBalance}"]);
        return;
    }

    try {
        $pdo->beginTransaction();

        $smsId = generateUlid();
        $status = $sendOption === 'schedule' ? 'scheduled' : 'sent';
        $sentAt = $sendOption === 'now' ? date('Y-m-d H:i:s') : null;
        $balanceAfter = $currentBalance;

        if ($sendOption === 'now') {
            $balanceUpdate = updateSmsWalletBalance($smsWallet['id'], -$creditsNeeded);
            if (!$balanceUpdate) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to deduct SMS credits from wallet']);
                return;
            }
            $balanceAfter = $balanceUpdate['balance_after'];
        }

        $ins = $pdo->prepare("INSERT INTO zzimba_sms_history
            (id, vendor_id, user_id, sms_wallet_id, transaction_id, message, recipients, recipient_count, sms_parts, sms_rate, total_cost, credits_used, status, type, sent_at, scheduled_at, balance_before, balance_after, created_at)
            VALUES (?, NULL, ?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $ins->execute([
            $smsId,
            $userId,
            $smsWallet['id'],
            $message,
            json_encode($recipients),
            count($recipients),
            $smsParts,
            $smsRate,
            $creditsNeeded * $smsRate,
            $creditsNeeded,
            $status,
            $sendType,
            $sentAt,
            $scheduledAt,
            $currentBalance,
            $balanceAfter
        ]);

        $pdo->commit();

        $notificationService->create(
            'system',
            $sendOption === 'schedule' ? "Scheduled {$creditsNeeded} SMS message(s)" : "Sent {$creditsNeeded} SMS message(s)",
            [
                ['type' => 'admin', 'id' => '', 'message' => "User {$userId} {$status} {$creditsNeeded} SMS message(s)"]
            ],
            null,
            'normal',
            $userId
        );

        if ($sendOption === 'now') {
            try {
                if (count($recipients) === 1) {
                    $res = SMS::send($recipients[0], $message);
                    if (!$res['success'])
                        error_log("SMS send failed: " . ($res['error'] ?? 'Unknown'));
                } else {
                    $res = SMS::sendBulk($recipients, $message);
                    if (!empty($res['failure_count']))
                        error_log("Bulk SMS failures: {$res['failure_count']}/{$res['total']}");
                }
            } catch (Exception $e) {
                error_log("SMS provider error: " . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => $sendOption === 'schedule' ? 'SMS scheduled successfully' : 'SMS sent successfully',
            'data' => [
                'sms_id' => $smsId,
                'credits_used' => $creditsNeeded,
                'total_cost' => $creditsNeeded * $smsRate,
                'new_balance' => $balanceAfter,
                'new_credits' => $balanceAfter,
                'sms_wallet_id' => $smsWallet['id']
            ]
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error sending user SMS: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send SMS']);
    }
}

function handleGetSmsHistory()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    $page = (int) ($_GET['page'] ?? 1);
    $limit = (int) ($_GET['limit'] ?? 20);
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }

    try {
        $offset = ($page - 1) * $limit;
        $where = ["user_id = ?", "vendor_id IS NULL"];
        $params = [$userId];

        if ($search !== '') {
            $where[] = "message LIKE ?";
            $params[] = "%{$search}%";
        }
        if ($status !== '') {
            $where[] = "status = ?";
            $params[] = $status;
        }
        if ($dateFrom !== '') {
            $where[] = "DATE(COALESCE(sent_at, scheduled_at, created_at)) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[] = "DATE(COALESCE(sent_at, scheduled_at, created_at)) <= ?";
            $params[] = $dateTo;
        }

        $whereSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) total FROM zzimba_sms_history WHERE {$whereSql}");
        $stmt->execute($params);
        $total = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $stmt = $pdo->prepare("SELECT * FROM zzimba_sms_history WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['recipients'] = json_decode($r['recipients'], true);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'history' => $rows,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / max(1, $limit))
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error getting user SMS history: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get history']);
    }
}

function handleGetSmsTemplates()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM zzimba_sms_templates WHERE user_id = ? AND vendor_id IS NULL AND is_active = 1 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $templates]);
    } catch (Exception $e) {
        error_log("Error getting user SMS templates: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get templates']);
    }
}

function handleSaveTemplate()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    $templateId = trim($_POST['template_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        return;
    }
    if ($name === '' || $message === '') {
        echo json_encode(['success' => false, 'message' => 'Name and message are required']);
        return;
    }

    try {
        if ($templateId) {
            $stmt = $pdo->prepare("UPDATE zzimba_sms_templates SET name=?, message=?, updated_at=NOW() WHERE id=? AND user_id=? AND vendor_id IS NULL");
            $stmt->execute([$name, $message, $templateId, $userId]);
            $msg = 'Template updated successfully';
        } else {
            $id = generateUlid();
            $stmt = $pdo->prepare("INSERT INTO zzimba_sms_templates (id, vendor_id, user_id, name, message, is_active, created_at, updated_at) VALUES (?, NULL, ?, ?, ?, 1, NOW(), NOW())");
            $stmt->execute([$id, $userId, $name, $message]);
            $msg = 'Template created successfully';
        }
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (Exception $e) {
        error_log("Error saving user SMS template: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save template']);
    }
}

function handleDeleteTemplate()
{
    global $pdo;
    $userId = $_SESSION['user']['user_id'] ?? null;
    $templateId = trim($_POST['template_id'] ?? '');
    if (!$userId || !$templateId) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        return;
    }
    try {
        $stmt = $pdo->prepare("UPDATE zzimba_sms_templates SET is_active=0, updated_at=NOW() WHERE id=? AND user_id=? AND vendor_id IS NULL");
        $stmt->execute([$templateId, $userId]);
        echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
    } catch (Exception $e) {
        error_log("Error deleting user SMS template: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete template']);
    }
}
