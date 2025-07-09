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

use ZzimbaCreditModule\CreditService;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Create tables if they don't exist
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
        // SMS Wallet table - tracks SMS balance in currency amount for users/vendors
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_wallet` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `owner_type` enum('USER','VENDOR') NOT NULL,
            `user_id` char(26) DEFAULT NULL,
            `vendor_id` char(26) DEFAULT NULL,
            `current_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
            `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_owner` (`owner_type`, `user_id`, `vendor_id`),
            INDEX `idx_owner_type` (`owner_type`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // SMS History table - tracks all SMS sending activity
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_history` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `vendor_id` char(26) NOT NULL,
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
            `balance_before` decimal(10,2) NOT NULL DEFAULT 0.00,
            `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_sms_wallet_id` (`sms_wallet_id`),
            INDEX `idx_transaction_id` (`transaction_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_sent_at` (`sent_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // SMS Templates table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_templates` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `vendor_id` char(26) NOT NULL,
            `user_id` char(26) DEFAULT NULL,
            `name` varchar(200) NOT NULL,
            `message` text NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // SMS Topup History table - tracks SMS credit purchases
        $pdo->exec("CREATE TABLE IF NOT EXISTS `zzimba_sms_topup_history` (
            `id` char(26) NOT NULL PRIMARY KEY,
            `sms_wallet_id` char(26) NOT NULL,
            `vendor_id` char(26) NOT NULL,
            `user_id` char(26) DEFAULT NULL,
            `transaction_id` char(26) DEFAULT NULL,
            `amount_paid` decimal(10,2) NOT NULL,
            `sms_rate` decimal(10,2) NOT NULL,
            `credits_purchased` int(11) NOT NULL,
            `status` enum('completed','pending','failed') NOT NULL DEFAULT 'completed',
            `balance_before` decimal(10,2) NOT NULL DEFAULT 0.00,
            `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_sms_wallet_id` (`sms_wallet_id`),
            INDEX `idx_vendor_id` (`vendor_id`),
            INDEX `idx_transaction_id` (`transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    } catch (Exception $e) {
        error_log("Error creating SMS tables: " . $e->getMessage());
    }
}

function getSmsRate($vendorId = null)
{
    global $pdo;

    try {
        $appKey = 'vendors';
        $sql = "SELECT setting_value FROM zzimba_credit_settings 
                WHERE setting_key = 'sms_cost' 
                AND status = 'active' 
                AND applicable_to IN ('all', ?)
                ORDER BY (applicable_to = 'all') DESC, (applicable_to = ?) DESC
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$appKey, $appKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (float) $result['setting_value'] : 35.00; // Default rate
    } catch (Exception $e) {
        error_log("Error getting SMS rate: " . $e->getMessage());
        return 35.00; // Default fallback
    }
}

function getVendorWallet($vendorId)
{
    global $pdo;

    try {
        $sql = "SELECT * FROM zzimba_wallets 
                WHERE vendor_id = ? AND owner_type = 'VENDOR' AND status = 'active' 
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting vendor wallet: " . $e->getMessage());
        return null;
    }
}

function getSmsWallet($ownerType, $userId = null, $vendorId = null, $createIfNotExists = true)
{
    global $pdo;

    try {
        // Build query based on owner type
        if ($ownerType === 'USER' && $userId) {
            $sql = "SELECT * FROM zzimba_sms_wallet 
                    WHERE owner_type = 'USER' AND user_id = ? AND vendor_id IS NULL 
                    LIMIT 1";
            $params = [$userId];
        } elseif ($ownerType === 'VENDOR' && $vendorId) {
            $sql = "SELECT * FROM zzimba_sms_wallet 
                    WHERE owner_type = 'VENDOR' AND vendor_id = ? AND user_id IS NULL 
                    LIMIT 1";
            $params = [$vendorId];
        } else {
            return null;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $smsWallet = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create SMS wallet if it doesn't exist and createIfNotExists is true
        if (!$smsWallet && $createIfNotExists) {
            $smsWallet = createSmsWallet($ownerType, $userId, $vendorId);
        }

        return $smsWallet;

    } catch (Exception $e) {
        error_log("Error getting SMS wallet: " . $e->getMessage());
        return null;
    }
}

function createSmsWallet($ownerType, $userId = null, $vendorId = null)
{
    global $pdo;

    try {
        $walletId = generateUlid();

        $sql = "INSERT INTO zzimba_sms_wallet 
                (id, owner_type, user_id, vendor_id, current_balance, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 0.00, 'active', NOW(), NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$walletId, $ownerType, $userId, $vendorId]);

        // Return the created wallet
        return getSmsWallet($ownerType, $userId, $vendorId, false);

    } catch (Exception $e) {
        error_log("Error creating SMS wallet: " . $e->getMessage());
        return null;
    }
}

function updateSmsWalletBalance($smsWalletId, $amountChange)
{
    global $pdo;

    try {
        // Get current balance with row lock (no transaction here since parent handles it)
        $sql = "SELECT current_balance FROM zzimba_sms_wallet WHERE id = ? FOR UPDATE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$smsWalletId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current) {
            return false;
        }

        $balanceBefore = (float) $current['current_balance'];
        $balanceAfter = $balanceBefore + $amountChange;

        // Ensure balance doesn't go negative
        if ($balanceAfter < 0) {
            return false;
        }

        // Update the wallet balance
        $sql = "UPDATE zzimba_sms_wallet 
                SET current_balance = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$balanceAfter, $smsWalletId]);

        return [
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'amount_change' => $amountChange
        ];

    } catch (Exception $e) {
        error_log("Error updating SMS wallet balance: " . $e->getMessage());
        return false;
    }
}

function handlePurchaseSmsCredits()
{
    global $pdo;

    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $vendorId = $_SESSION['active_store'] ?? null;
    $userId = $_SESSION['user']['user_id'] ?? null;

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
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

    // Get vendor wallet for payment
    $wallet = getVendorWallet($vendorId);
    if (!$wallet) {
        echo json_encode(['success' => false, 'message' => 'Vendor wallet not found']);
        return;
    }

    // Check if wallet has sufficient balance
    $walletBalance = (float) $wallet['current_balance'];
    if ($walletBalance < $amount) {
        echo json_encode([
            'success' => false,
            'message' => "Insufficient wallet balance. Need Sh. {$amount}, have Sh. {$walletBalance}",
            'data' => [
                'required_amount' => $amount,
                'wallet_balance' => $walletBalance,
                'topup_url' => BASE_URL . 'vendor-store/zzimba-credit'
            ]
        ]);
        return;
    }

    // Get or create SMS wallet
    $smsWallet = getSmsWallet('VENDOR', null, $vendorId, true);
    if (!$smsWallet) {
        echo json_encode(['success' => false, 'message' => 'Failed to create SMS wallet']);
        return;
    }

    // Build payload for CreditService to handle the payment (no payment_method needed)
    $payload = [
        'wallet_id' => $wallet['wallet_id'],
        'owner_type' => 'VENDOR',
        'amount' => $amount,
        'vendor_id' => $vendorId,
        'user_id' => $userId
    ];

    // Log the payload for debugging
    error_log('[handlePurchaseSmsCredits] payload: ' . json_encode($payload));

    // Call the CreditService to handle payment
    $result = CreditService::purchaseSmsCredits($payload);

    // Log the result for debugging
    error_log('[handlePurchaseSmsCredits] CreditService result: ' . json_encode($result));

    if (!$result['success']) {
        echo json_encode($result);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Get current SMS rate for calculation
        $smsRate = getSmsRate($vendorId);
        $creditsPurchased = floor($amount / $smsRate);

        // Update SMS wallet balance by adding the amount paid
        $balanceUpdate = updateSmsWalletBalance($smsWallet['id'], $amount);
        if (!$balanceUpdate) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Failed to update SMS wallet balance']);
            return;
        }

        // Record topup history (without payment_method)
        $topupId = generateUlid();
        $sql = "INSERT INTO zzimba_sms_topup_history 
                (id, sms_wallet_id, vendor_id, user_id, transaction_id, amount_paid, 
                 sms_rate, credits_purchased, status, balance_before, balance_after, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $topupId,
            $smsWallet['id'],
            $vendorId,
            $userId,
            $result['transaction_id'] ?? null,
            $amount,
            $smsRate,
            $creditsPurchased,
            $balanceUpdate['balance_before'],
            $balanceUpdate['balance_after']
        ]);

        $pdo->commit();

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
        error_log("Error processing SMS credit purchase: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to process SMS credit purchase']);
    }
}

function handleGetSmsStats()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;
    $userId = $_SESSION['user']['user_id'] ?? null;

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    try {
        // Get SMS wallet
        $smsWallet = getSmsWallet('VENDOR', null, $vendorId, true);
        $currentBalance = $smsWallet ? (float) $smsWallet['current_balance'] : 0.00;

        // Get current SMS rate to calculate available credits
        $smsRate = getSmsRate($vendorId);
        $currentCredits = $smsRate > 0 ? floor($currentBalance / $smsRate) : 0;

        // Get today's stats
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as sent_count, SUM(credits_used) as credits_used, SUM(total_cost) as total_cost
                FROM zzimba_sms_history 
                WHERE vendor_id = ? AND DATE(sent_at) = ? AND status = 'sent'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendorId, $today]);
        $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get scheduled count
        $sql = "SELECT COUNT(*) as scheduled_count FROM zzimba_sms_history 
                WHERE vendor_id = ? AND status = 'scheduled'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendorId]);
        $scheduledStats = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'current_credits' => $currentCredits,
                'current_balance' => $currentBalance,
                'credit_value' => $currentBalance,
                'sms_rate' => $smsRate,
                'sent_today' => (int) $todayStats['sent_count'],
                'sent_today_cost' => (float) $todayStats['total_cost'],
                'scheduled_count' => (int) $scheduledStats['scheduled_count']
            ]
        ]);

    } catch (Exception $e) {
        error_log("Error getting SMS stats: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get stats']);
    }
}

function handleGetWalletBalance()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    try {
        // Get vendor wallet
        $wallet = getVendorWallet($vendorId);
        if (!$wallet) {
            echo json_encode(['success' => false, 'message' => 'Vendor wallet not found']);
            return;
        }

        $balance = (float) $wallet['current_balance'];

        // Get current SMS rate to calculate equivalent credits
        $smsRate = getSmsRate($vendorId);
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
        error_log("Error getting wallet balance: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get wallet balance']);
    }
}

function handleSendSms()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;
    $userId = $_SESSION['user']['user_id'] ?? null;

    $message = trim($_POST['message'] ?? '');
    $recipients = json_decode($_POST['recipients'] ?? '[]', true);
    $sendType = $_POST['send_type'] ?? 'single';
    $sendOption = $_POST['send_option'] ?? 'now';
    $scheduledAt = $_POST['scheduled_at'] ?? null;

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    if (empty($message) || empty($recipients)) {
        echo json_encode(['success' => false, 'message' => 'Message and recipients are required']);
        return;
    }

    // Get SMS wallet
    $smsWallet = getSmsWallet('VENDOR', null, $vendorId, true);
    if (!$smsWallet) {
        echo json_encode(['success' => false, 'message' => 'SMS wallet not found']);
        return;
    }

    // Get current SMS rate and calculate cost
    $smsRate = getSmsRate($vendorId);
    $smsParts = max(1, ceil(strlen($message) / 160));
    $creditsNeeded = count($recipients) * $smsParts;
    $totalCost = $creditsNeeded * $smsRate;
    $currentBalance = (float) $smsWallet['current_balance'];

    // Check if sufficient balance for immediate sending
    if ($sendOption === 'now' && $totalCost > $currentBalance) {
        $availableCredits = floor($currentBalance / $smsRate);
        echo json_encode([
            'success' => false,
            'message' => "Insufficient balance. Need Sh. {$totalCost}, have Sh. {$currentBalance} ({$availableCredits} credits)"
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        $smsId = generateUlid();
        $status = $sendOption === 'schedule' ? 'scheduled' : 'sent';
        $sentAt = $sendOption === 'now' ? date('Y-m-d H:i:s') : null;

        $balanceAfter = $currentBalance;

        // If sending now, deduct cost from SMS wallet
        if ($sendOption === 'now') {
            $balanceUpdate = updateSmsWalletBalance($smsWallet['id'], -$totalCost);
            if (!$balanceUpdate) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to deduct SMS cost from wallet']);
                return;
            }
            $balanceAfter = $balanceUpdate['balance_after'];
        }

        // Insert SMS history
        $sql = "INSERT INTO zzimba_sms_history 
                (id, vendor_id, user_id, sms_wallet_id, message, recipients, recipient_count, sms_parts, 
                 sms_rate, total_cost, credits_used, status, type, sent_at, scheduled_at, 
                 balance_before, balance_after, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $smsId,
            $vendorId,
            $userId,
            $smsWallet['id'],
            $message,
            json_encode($recipients),
            count($recipients),
            $smsParts,
            $smsRate,
            $totalCost,
            $creditsNeeded,
            $status,
            $sendType,
            $sentAt,
            $scheduledAt,
            $currentBalance,
            $balanceAfter
        ]);

        $pdo->commit();

        // Calculate new available credits
        $newCredits = $smsRate > 0 ? floor($balanceAfter / $smsRate) : 0;

        echo json_encode([
            'success' => true,
            'message' => $sendOption === 'schedule' ? 'SMS scheduled successfully' : 'SMS sent successfully',
            'data' => [
                'sms_id' => $smsId,
                'credits_used' => $creditsNeeded,
                'total_cost' => $totalCost,
                'new_balance' => $balanceAfter,
                'new_credits' => $newCredits,
                'sms_wallet_id' => $smsWallet['id']
            ]
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error sending SMS: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to send SMS']);
    }
}

function handleGetSmsHistory()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;
    $page = (int) ($_GET['page'] ?? 1);
    $limit = (int) ($_GET['limit'] ?? 20);
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo = trim($_GET['date_to'] ?? '');

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    try {
        $offset = ($page - 1) * $limit;

        $whereConditions = ["vendor_id = ?"];
        $params = [$vendorId];

        if ($search) {
            $whereConditions[] = "message LIKE ?";
            $params[] = "%{$search}%";
        }

        if ($status) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }

        if ($dateFrom) {
            $whereConditions[] = "DATE(COALESCE(sent_at, scheduled_at)) >= ?";
            $params[] = $dateFrom;
        }

        if ($dateTo) {
            $whereConditions[] = "DATE(COALESCE(sent_at, scheduled_at)) <= ?";
            $params[] = $dateTo;
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM zzimba_sms_history WHERE {$whereClause}";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get records
        $sql = "SELECT * FROM zzimba_sms_history 
                WHERE {$whereClause}
                ORDER BY created_at DESC 
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode recipients JSON
        foreach ($history as &$record) {
            $record['recipients'] = json_decode($record['recipients'], true);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'history' => $history,
                'total' => (int) $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        error_log("Error getting SMS history: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get history']);
    }
}

function handleGetSmsTemplates()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    try {
        $sql = "SELECT * FROM zzimba_sms_templates 
                WHERE vendor_id = ? AND is_active = 1
                ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendorId]);
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $templates
        ]);

    } catch (Exception $e) {
        error_log("Error getting SMS templates: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to get templates']);
    }
}

function handleSaveTemplate()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;
    $userId = $_SESSION['user']['user_id'] ?? null;

    $templateId = trim($_POST['template_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$vendorId) {
        echo json_encode(['success' => false, 'message' => 'No active store found']);
        return;
    }

    if (empty($name) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Name and message are required']);
        return;
    }

    try {
        if ($templateId) {
            // Update existing template
            $sql = "UPDATE zzimba_sms_templates 
                    SET name = ?, message = ?, updated_at = NOW()
                    WHERE id = ? AND vendor_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $message, $templateId, $vendorId]);

            $responseMessage = 'Template updated successfully';
        } else {
            // Create new template
            $newId = generateUlid();
            $sql = "INSERT INTO zzimba_sms_templates 
                    (id, vendor_id, user_id, name, message, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$newId, $vendorId, $userId, $name, $message]);

            $responseMessage = 'Template created successfully';
        }

        echo json_encode([
            'success' => true,
            'message' => $responseMessage
        ]);

    } catch (Exception $e) {
        error_log("Error saving SMS template: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to save template']);
    }
}

function handleDeleteTemplate()
{
    global $pdo;

    $vendorId = $_SESSION['active_store'] ?? null;
    $templateId = trim($_POST['template_id'] ?? '');

    if (!$vendorId || !$templateId) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        return;
    }

    try {
        $sql = "UPDATE zzimba_sms_templates 
                SET is_active = 0, updated_at = NOW()
                WHERE id = ? AND vendor_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$templateId, $vendorId]);

        echo json_encode([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);

    } catch (Exception $e) {
        error_log("Error deleting SMS template: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete template']);
    }
}
?>