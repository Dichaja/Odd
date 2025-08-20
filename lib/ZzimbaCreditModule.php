<?php

namespace ZzimbaCreditModule;

use PDO;
use PDOException;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../sms/SMS.php';
require_once __DIR__ . '/NotificationService.php';

final class CreditService
{
    private const API_KEY = '56cded6ede99ac.BYJV1ceTwWbN_NzaqIchUw';
    private const ACCOUNT_NO = 'REL2C6A94761B';
    private const DEFAULT_CURRENCY = 'UGX';

    private static bool $ready = false;
    private static PDO $pdo;
    private static \NotificationService $ns;

    private static function boot(): void
    {
        if (self::$ready) {
            return;
        }

        global $pdo;
        self::$pdo = $pdo;
        self::$ns = new \NotificationService($pdo);

        $createWallets = "
            CREATE TABLE IF NOT EXISTS zzimba_wallets (
                wallet_id CHAR(26) NOT NULL PRIMARY KEY,
                wallet_number CHAR(10) NOT NULL UNIQUE,
                owner_type ENUM('USER','VENDOR','PLATFORM') NOT NULL,
                user_id VARCHAR(26) DEFAULT NULL,
                vendor_id VARCHAR(26) DEFAULT NULL,
                wallet_name VARCHAR(100) NOT NULL,
                current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                CONSTRAINT fk_wallet_user FOREIGN KEY (user_id)
                    REFERENCES zzimba_users(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_wallet_vendor FOREIGN KEY (vendor_id)
                    REFERENCES vendor_stores(id)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createFinancial = "
            CREATE TABLE IF NOT EXISTS zzimba_financial_transactions (
                transaction_id VARCHAR(26) NOT NULL PRIMARY KEY,
                transaction_type ENUM('TOPUP','PURCHASE','SUBSCRIPTION','SMS_PURCHASE','EMAIL_PURCHASE','PREMIUM_FEATURE','REFUND','WITHDRAWAL','TRANSFER','CHARGES','QUOTE') NOT NULL,
                status ENUM('PENDING','SUCCESS','FAILED','REFUNDED','DISPUTED') NOT NULL DEFAULT 'PENDING',
                amount_total DECIMAL(15,2) NOT NULL,
                payment_method ENUM('MOBILE_MONEY_GATEWAY','CARD_GATEWAY','MOBILE_MONEY','BANK','WALLET') DEFAULT NULL,
                external_reference VARCHAR(100) DEFAULT NULL,
                external_metadata TEXT DEFAULT NULL,
                wallet_id CHAR(26) DEFAULT NULL,
                user_id VARCHAR(26) DEFAULT NULL,
                vendor_id VARCHAR(26) DEFAULT NULL,
                original_txn_id VARCHAR(26) DEFAULT NULL,
                note VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                CONSTRAINT fk_txn_wallet FOREIGN KEY (wallet_id) REFERENCES zzimba_wallets(wallet_id) ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_txn_user FOREIGN KEY (user_id) REFERENCES zzimba_users(id) ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_txn_vendor FOREIGN KEY (vendor_id) REFERENCES vendor_stores(id) ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_txn_original FOREIGN KEY (original_txn_id) REFERENCES zzimba_financial_transactions(transaction_id) ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createEntries = "
            CREATE TABLE IF NOT EXISTS zzimba_transaction_entries (
                entry_id CHAR(26) NOT NULL PRIMARY KEY,
                transaction_id VARCHAR(26) NOT NULL,
                wallet_id CHAR(26) DEFAULT NULL,
                cash_account_id CHAR(26) DEFAULT NULL,
                ref_entry_id CHAR(26) DEFAULT NULL,
                entry_type ENUM('DEBIT','CREDIT') NOT NULL,
                amount DECIMAL(18,2) NOT NULL,
                balance_after DECIMAL(18,2) NOT NULL,
                entry_note VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                CONSTRAINT fk_entry_transaction FOREIGN KEY (transaction_id)
                    REFERENCES zzimba_financial_transactions(transaction_id)
                    ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT fk_entry_wallet FOREIGN KEY (wallet_id)
                    REFERENCES zzimba_wallets(wallet_id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_entry_cash FOREIGN KEY (cash_account_id)
                    REFERENCES zzimba_cash_accounts(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_entry_ref FOREIGN KEY (ref_entry_id)
                    REFERENCES zzimba_transaction_entries(entry_id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                INDEX idx_entry_txn (transaction_id),
                INDEX idx_entry_wallet (wallet_id),
                INDEX idx_entry_cash (cash_account_id),
                INDEX idx_entry_ref (ref_entry_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createTransfers = "
            CREATE TABLE IF NOT EXISTS zzimba_wallet_transfers (
                id CHAR(26) NOT NULL PRIMARY KEY,
                wallet_from CHAR(26) NOT NULL,
                wallet_to CHAR(26) NOT NULL,
                transaction_id VARCHAR(26) NOT NULL,
                created_at DATETIME NOT NULL,
                CONSTRAINT fk_transfer_from FOREIGN KEY (wallet_from)
                    REFERENCES zzimba_wallets(wallet_id)
                    ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT fk_transfer_to FOREIGN KEY (wallet_to)
                    REFERENCES zzimba_wallets(wallet_id)
                    ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT fk_transfer_txn FOREIGN KEY (transaction_id)
                    REFERENCES zzimba_financial_transactions(transaction_id)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            self::$pdo->exec($createFinancial);
            self::$pdo->exec($createEntries);
            self::$pdo->exec($createWallets);
            self::$pdo->exec($createTransfers);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] table-creation error: ' . $e->getMessage());
        }

        date_default_timezone_set('Africa/Kampala');
        self::$ready = true;
    }

    private static function getEntityName(string $entityType, ?string $entityId): string
    {
        if (empty($entityId)) {
            return 'Unknown';
        }

        try {
            if ($entityType === 'USER') {
                $stmt = self::$pdo->prepare('SELECT username FROM zzimba_users WHERE id = :id LIMIT 1');
            } elseif ($entityType === 'VENDOR') {
                $stmt = self::$pdo->prepare('SELECT name FROM vendor_stores WHERE id = :id LIMIT 1');
            } else {
                return 'Unknown';
            }

            $stmt->execute([':id' => $entityId]);
            return $stmt->fetchColumn() ?: 'Unknown';
        } catch (\Throwable $e) {
            return 'Unknown';
        }
    }

    private static function getEntityInfo(?string $userId, ?string $vendorId): array
    {
        if (!empty($userId)) {
            return [
                'type' => 'User',
                'name' => self::getEntityName('USER', $userId),
                'id' => $userId
            ];
        } elseif (!empty($vendorId)) {
            return [
                'type' => 'Store',
                'name' => self::getEntityName('VENDOR', $vendorId),
                'id' => $vendorId
            ];
        }

        return ['type' => 'Unknown', 'name' => 'Unknown', 'id' => null];
    }

    private static function sendNotification(string $title, array $recipients, string $priority = 'normal', ?string $relatedUserId = null): void
    {
        self::$ns->create('system', $title, $recipients, null, $priority, $relatedUserId);
    }

    private static function buildRecipients(array $adminMessage, ?array $userMessage = null, ?array $vendorMessage = null): array
    {
        $recipients = [
            [
                'type' => 'admin',
                'id' => 'admin-global',
                'message' => $adminMessage['message']
            ]
        ];

        if ($userMessage && !empty($userMessage['id'])) {
            $recipients[] = [
                'type' => 'user',
                'id' => $userMessage['id'],
                'message' => $userMessage['message']
            ];
        }

        if ($vendorMessage && !empty($vendorMessage['id'])) {
            $recipients[] = [
                'type' => 'store',
                'id' => $vendorMessage['id'],
                'message' => $vendorMessage['message']
            ];
        }

        return $recipients;
    }

    private static function generateWalletNumber(string $walletId): string
    {
        $yy = date('y');
        $y1 = $yy[0];
        $y2 = $yy[1];
        $mm = date('m');
        $m1 = $mm[0];
        $m2 = $mm[1];

        do {
            $seq = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $walletNumber = $y1 . $seq[0] . $y2 . $seq[1] . $seq[2] . $seq[3] . $seq[4] . $m1 . $seq[5] . $m2;
            $chk = self::$pdo->prepare('SELECT 1 FROM zzimba_wallets WHERE wallet_number = ? LIMIT 1');
            $chk->execute([$walletNumber]);
            $exists = (bool) $chk->fetchColumn();
        } while ($exists);

        return $walletNumber;
    }

    private static function getFeeSettingsForUser(): ?array
    {
        try {
            $stmt = self::$pdo->prepare("
                SELECT setting_key, setting_name, setting_value, setting_type, applicable_to, status, id
                FROM zzimba_credit_settings 
                WHERE setting_key = 'transfer_fee' 
                AND category = 'transfer' 
                AND status = 'active'
                AND (applicable_to = 'all' OR applicable_to = 'users')
                ORDER BY 
                    CASE 
                        WHEN applicable_to = 'all' THEN 1 
                        WHEN applicable_to = 'users' THEN 2 
                        ELSE 3 
                    END
                LIMIT 1
            ");

            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log("Error fetching fee settings: " . $e->getMessage());
            return null;
        }
    }

    private static function calculateTransferFee(float $amount, ?array $feeSettings): float
    {
        if (!$feeSettings)
            return 0;

        $feeValue = floatval($feeSettings['setting_value']);

        if ($feeSettings['setting_type'] === 'flat') {
            return $feeValue;
        } elseif ($feeSettings['setting_type'] === 'percentage') {
            return ($amount * $feeValue) / 100;
        }

        return 0;
    }

    private static function getChargeDestinationWallet(string $creditSettingId): ?array
    {
        try {
            $stmt = self::$pdo->prepare("
                SELECT w.wallet_id, w.wallet_number, w.current_balance
                FROM zzimba_wallets w
                JOIN zzimba_wallet_credit_assignments a ON a.wallet_id = w.wallet_id
                WHERE a.credit_setting_id = :cid
                AND w.status = 'active'
                LIMIT 1
            ");

            $stmt->execute([':cid' => $creditSettingId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log("Error fetching charge destination wallet: " . $e->getMessage());
            return null;
        }
    }

    public static function processQuoteRequest(array $opts): array
    {
        self::boot();

        $amount = (float) ($opts['amount'] ?? 0);
        $userId = trim($opts['user_id'] ?? '');

        if ($amount <= 0 || empty($userId)) {
            return ['success' => false, 'message' => 'Invalid amount or user ID'];
        }

        $userWalletStmt = self::$pdo->prepare("
            SELECT wallet_id, wallet_number, current_balance
            FROM zzimba_wallets
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $userWalletStmt->execute([':user_id' => $userId]);
        $userWallet = $userWalletStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userWallet) {
            return ['success' => false, 'message' => 'User wallet not found'];
        }

        if ((float) $userWallet['current_balance'] < $amount) {
            return [
                'success' => false,
                'message' => 'Insufficient wallet balance',
                'balance' => (float) $userWallet['current_balance'],
                'required' => $amount
            ];
        }

        $quoteSettingStmt = self::$pdo->prepare("
            SELECT id, setting_value
            FROM zzimba_credit_settings
            WHERE setting_key = 'request_for_quote'
            AND status = 'active'
            AND setting_type = 'flat'
            AND category = 'quote'
            AND (applicable_to = 'users' OR applicable_to = 'all')
            ORDER BY applicable_to DESC
            LIMIT 1
        ");
        $quoteSettingStmt->execute();
        $quoteSetting = $quoteSettingStmt->fetch(PDO::FETCH_ASSOC);

        if (!$quoteSetting) {
            return ['success' => false, 'message' => 'Quote fee setting not found'];
        }

        $feeAmount = (float) $quoteSetting['setting_value'];
        if ($amount != $feeAmount) {
            return ['success' => false, 'message' => 'Amount does not match quote fee setting'];
        }

        $destinationWallet = self::getChargeDestinationWallet($quoteSetting['id']);
        if (!$destinationWallet) {
            return ['success' => false, 'message' => 'Quote fee destination wallet not configured'];
        }

        $txnId = \generateUlid();

        try {
            self::$pdo->beginTransaction();

            self::insertTransaction([
                'transaction_id' => $txnId,
                'transaction_type' => 'QUOTE',
                'status' => 'SUCCESS',
                'amount_total' => $amount,
                'payment_method' => 'WALLET',
                'wallet_id' => $userWallet['wallet_id'],
                'user_id' => $userId,
                'note' => 'Quote request fee'
            ]);

            self::insertTransfer([
                'id' => \generateUlid(),
                'wallet_from' => $userWallet['wallet_id'],
                'wallet_to' => $destinationWallet['wallet_id'],
                'transaction_id' => $txnId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $withholdingId = self::getWithholdingAccountId();

            $balStmt = self::$pdo->prepare("SELECT current_balance FROM zzimba_wallets WHERE wallet_id = :wid");

            $balStmt->execute([':wid' => $userWallet['wallet_id']]);
            $userBalance = (float) $balStmt->fetchColumn();

            $balStmt->execute([':wid' => $withholdingId]);
            $withholdingBalance = (float) $balStmt->fetchColumn();

            $balStmt->execute([':wid' => $destinationWallet['wallet_id']]);
            $destinationBalance = (float) $balStmt->fetchColumn();

            $debitId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $userWallet['wallet_id'],
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => $userBalance - $amount,
                'entry_note' => 'Quote request fee charged to ' . $userWallet['wallet_number']
            ]);
            self::updateWalletBalance($userWallet['wallet_id'], $userBalance - $amount);

            $creditWithholdingId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $withholdingBalance + $amount,
                'entry_note' => 'Quote fee collection from ' . $userWallet['wallet_number'],
                'ref_entry_id' => $debitId
            ]);
            self::updateWalletBalance($withholdingId, $withholdingBalance + $amount);

            $debitWithholdingId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => ($withholdingBalance + $amount) - $amount,
                'entry_note' => 'Quote fee disbursement to ' . $destinationWallet['wallet_number'],
                'ref_entry_id' => $creditWithholdingId
            ]);
            self::updateWalletBalance($withholdingId, ($withholdingBalance + $amount) - $amount);

            self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $destinationWallet['wallet_id'],
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $destinationBalance + $amount,
                'entry_note' => 'Quote fee earned from ' . $userWallet['wallet_number'],
                'ref_entry_id' => $debitWithholdingId
            ]);
            self::updateWalletBalance($destinationWallet['wallet_id'], $destinationBalance + $amount);

            self::$pdo->commit();

            $entity = self::getEntityInfo($userId, null);
            $recipients = self::buildRecipients(
                ['message' => "{$entity['type']} {$entity['name']} submitted a Request for Quote and was charged {$amount} UGX"],
                ['id' => $userId, 'message' => "Quote request fee of {$amount} UGX has been charged to your wallet"]
            );

            self::sendNotification('Quote Request Fee Processed', $recipients, 'normal', $userId);

            return [
                'success' => true,
                'transaction_id' => $txnId,
                'fee_charged' => $amount,
                'remaining_balance' => $userBalance - $amount
            ];

        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] Quote request processing error: ' . $e->getMessage());

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                SET status = 'FAILED', updated_at = NOW()
                WHERE transaction_id = :tid
            ")->execute([':tid' => $txnId]);

            return ['success' => false, 'message' => 'Quote request processing failed'];
        }
    }

    public static function processQuotePayment(array $opts): array
    {
        self::boot();

        $amount = (float) ($opts['amount'] ?? 0);
        $userId = trim($opts['user_id'] ?? '');
        $quotationId = trim($opts['quotation_id'] ?? '');

        if ($amount <= 0 || empty($userId) || empty($quotationId)) {
            return ['success' => false, 'message' => 'Invalid amount, user ID, or quotation ID'];
        }

        $userWalletStmt = self::$pdo->prepare("
            SELECT wallet_id, wallet_number, current_balance
            FROM zzimba_wallets
            WHERE user_id = :user_id 
            AND owner_type = 'USER'
            AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $userWalletStmt->execute([':user_id' => $userId]);
        $userWallet = $userWalletStmt->fetch(PDO::FETCH_ASSOC);

        if (!$userWallet) {
            return ['success' => false, 'message' => 'User wallet not found'];
        }

        if ((float) $userWallet['current_balance'] < $amount) {
            return [
                'success' => false,
                'message' => 'Insufficient wallet balance',
                'balance' => (float) $userWallet['current_balance'],
                'required' => $amount
            ];
        }

        $operationsWalletStmt = self::$pdo->prepare("
            SELECT platform_account_id
            FROM zzimba_platform_account_settings
            WHERE type = 'operations'
            LIMIT 1
        ");
        $operationsWalletStmt->execute();
        $operationsWalletId = $operationsWalletStmt->fetchColumn();

        if (!$operationsWalletId) {
            return ['success' => false, 'message' => 'Operations account not configured'];
        }

        $operationsWalletDetailsStmt = self::$pdo->prepare("
            SELECT wallet_id, wallet_number, current_balance
            FROM zzimba_wallets
            WHERE wallet_id = :wallet_id
            AND status = 'active'
            LIMIT 1
        ");
        $operationsWalletDetailsStmt->execute([':wallet_id' => $operationsWalletId]);
        $operationsWallet = $operationsWalletDetailsStmt->fetch(PDO::FETCH_ASSOC);

        if (!$operationsWallet) {
            return ['success' => false, 'message' => 'Operations wallet not found'];
        }

        $txnId = \generateUlid();

        try {
            self::$pdo->beginTransaction();

            self::insertTransaction([
                'transaction_id' => $txnId,
                'transaction_type' => 'QUOTE',
                'status' => 'SUCCESS',
                'amount_total' => $amount,
                'payment_method' => 'WALLET',
                'wallet_id' => $userWallet['wallet_id'],
                'user_id' => $userId,
                'note' => 'Quote payment for quotation ID: ' . $quotationId
            ]);

            self::insertTransfer([
                'id' => \generateUlid(),
                'wallet_from' => $userWallet['wallet_id'],
                'wallet_to' => $operationsWallet['wallet_id'],
                'transaction_id' => $txnId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $withholdingId = self::getWithholdingAccountId();

            $balStmt = self::$pdo->prepare("SELECT current_balance FROM zzimba_wallets WHERE wallet_id = :wid");

            $balStmt->execute([':wid' => $userWallet['wallet_id']]);
            $userBalance = (float) $balStmt->fetchColumn();

            $balStmt->execute([':wid' => $withholdingId]);
            $withholdingBalance = (float) $balStmt->fetchColumn();

            $balStmt->execute([':wid' => $operationsWallet['wallet_id']]);
            $operationsBalance = (float) $balStmt->fetchColumn();

            $debitId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $userWallet['wallet_id'],
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => $userBalance - $amount,
                'entry_note' => 'Quote payment for quotation ' . $quotationId
            ]);
            self::updateWalletBalance($userWallet['wallet_id'], $userBalance - $amount);

            $creditWithholdingId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $withholdingBalance + $amount,
                'entry_note' => 'Quote payment collection from ' . $userWallet['wallet_number'],
                'ref_entry_id' => $debitId
            ]);
            self::updateWalletBalance($withholdingId, $withholdingBalance + $amount);

            $debitWithholdingId = self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => ($withholdingBalance + $amount) - $amount,
                'entry_note' => 'Quote payment disbursement to operations account',
                'ref_entry_id' => $creditWithholdingId
            ]);
            self::updateWalletBalance($withholdingId, ($withholdingBalance + $amount) - $amount);

            self::insertEntry([
                'transaction_id' => $txnId,
                'wallet_id' => $operationsWallet['wallet_id'],
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $operationsBalance + $amount,
                'entry_note' => 'Quote payment received from ' . $userWallet['wallet_number'],
                'ref_entry_id' => $debitWithholdingId
            ]);
            self::updateWalletBalance($operationsWallet['wallet_id'], $operationsBalance + $amount);

            self::$pdo->commit();

            $entity = self::getEntityInfo($userId, null);
            $recipients = self::buildRecipients(
                ['message' => "{$entity['type']} {$entity['name']} paid {$amount} UGX for the processed quote with ID {$quotationId}"],
                ['id' => $userId, 'message' => "Payment of {$amount} UGX for quote {$quotationId} has been processed successfully"]
            );

            self::sendNotification('Quote Payment Processed', $recipients, 'normal', $userId);

            return [
                'success' => true,
                'transaction_id' => $txnId,
                'amount_paid' => $amount,
                'quotation_id' => $quotationId,
                'remaining_balance' => $userBalance - $amount
            ];

        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] Quote payment processing error: ' . $e->getMessage());

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                SET status = 'FAILED', updated_at = NOW()
                WHERE transaction_id = :tid
            ")->execute([':tid' => $txnId]);

            return ['success' => false, 'message' => 'Quote payment processing failed'];
        }
    }

    public static function validateMsisdn(string $msisdn): array
    {
        self::boot();
        $resp = self::apiRequest(
            'https://payments.relworx.com/api/mobile-money/validate',
            ['msisdn' => $msisdn]
        );
        $result = json_decode($resp, true);

        $isValid = $result['success'] ?? false;
        $customerName = $result['customer_name'] ?? 'Unknown';

        $recipients = self::buildRecipients(
            ['message' => "MSISDN validation request: {$msisdn}. Customer Name: {$customerName}"]
        );

        self::sendNotification('MSISDN Validation', $recipients);

        return $result;
    }

    public static function makeMobileMoneyPayment(array $opts): array
    {
        self::boot();

        $msisdn = trim($opts['msisdn'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);

        if ($msisdn === '' || $amount <= 0) {
            return ['success' => false, 'message' => 'MSISDN and positive amount required'];
        }
        if ($amount < 500) {
            return ['success' => false, 'message' => 'Minimum amount is 500 UGX'];
        }

        $reference = \generateUlid();
        $payload = [
            'account_no' => self::ACCOUNT_NO,
            'reference' => $reference,
            'msisdn' => $msisdn,
            'currency' => self::DEFAULT_CURRENCY,
            'amount' => $amount,
            'description' => trim($opts['description'] ?? 'Payment Request.')
        ];

        $raw = self::apiRequest(
            'https://payments.relworx.com/api/mobile-money/request-payment',
            $payload
        );
        $res = json_decode($raw, true) ?? [];

        $externalRef = $res['internal_reference'] ?? $reference;
        self::insertTransaction([
            'transaction_id' => $reference,
            'transaction_type' => 'TOPUP',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'MOBILE_MONEY_GATEWAY',
            'external_reference' => $externalRef,
            'external_metadata' => $raw,
            'user_id' => $opts['user_id'] ?? null,
            'vendor_id' => $opts['vendor_id'] ?? null
        ]);

        $entity = self::getEntityInfo($opts['user_id'] ?? null, $opts['vendor_id'] ?? null);

        $recipients = self::buildRecipients(
            ['message' => "{$entity['type']} {$entity['name']} initiated Mobile Money top-up of {$amount} UGX from {$msisdn}. Transaction ID: {$reference}"],
            !empty($opts['user_id']) ? [
                'id' => $opts['user_id'],
                'message' => "You initiated a Mobile Money top-up of {$amount} UGX from {$msisdn}. Transaction ID: {$reference}"
            ] : null,
            !empty($opts['vendor_id']) ? [
                'id' => $opts['vendor_id'],
                'message' => "Mobile Money top-up of {$amount} UGX was initiated successfully. Transaction ID: {$reference}"
            ] : null
        );

        self::sendNotification(
            'Mobile Money Payment Initiated',
            $recipients,
            'normal',
            $entity['id']
        );

        return $res;
    }

    public static function checkRequestStatus(string $internalRef): array
    {
        self::boot();

        $currentStatusStmt = self::$pdo->prepare("
            SELECT status
              FROM zzimba_financial_transactions 
             WHERE external_reference = :er
             LIMIT 1
        ");
        $currentStatusStmt->execute([':er' => $internalRef]);
        $previousStatus = $currentStatusStmt->fetchColumn();

        $raw = self::apiRequest(
            'https://payments.relworx.com/api/mobile-money/check-request-status',
            [
                'internal_reference' => $internalRef,
                'account_no' => self::ACCOUNT_NO
            ],
            'GET'
        );
        $res = json_decode($raw, true) ?? [];
        $newStatus = strtoupper($res['request_status'] ?? $res['status'] ?? 'PENDING');

        $upd = self::$pdo->prepare("
            UPDATE zzimba_financial_transactions
               SET status            = :st,
                   note              = :nt,
                   external_metadata = :md,
                   updated_at        = NOW()
             WHERE external_reference = :er
        ");
        $upd->execute([
            ':st' => $newStatus,
            ':nt' => $res['message'] ?? null,
            ':md' => $raw,
            ':er' => $internalRef
        ]);

        if ($previousStatus !== $newStatus) {
            $txnRow = self::$pdo->prepare("
                SELECT transaction_id, amount_total, user_id, vendor_id
                  FROM zzimba_financial_transactions
                 WHERE external_reference = :er
            ");
            $txnRow->execute([':er' => $internalRef]);
            $txn = $txnRow->fetch(PDO::FETCH_ASSOC);

            if ($txn) {
                $entity = self::getEntityInfo($txn['user_id'], $txn['vendor_id']);

                $adminMessage = [
                    'message' => "{$entity['type']} {$entity['name']}'s transaction {$txn['transaction_id']} status changed from {$previousStatus} to {$newStatus}. Amount: {$txn['amount_total']} UGX"
                ];

                $userMessage = null;
                $vendorMessage = null;

                if ($newStatus === 'SUCCESS') {
                    if (!empty($txn['user_id'])) {
                        $userMessage = [
                            'id' => $txn['user_id'],
                            'message' => "Payment successful! {$txn['amount_total']} UGX has been credited to your wallet. (Transaction ID: {$txn['transaction_id']})"
                        ];
                    }
                    if (!empty($txn['vendor_id'])) {
                        $vendorMessage = [
                            'id' => $txn['vendor_id'],
                            'message' => "Payment successful! {$txn['amount_total']} UGX has been credited to your store wallet. (Transaction ID: {$txn['transaction_id']})"
                        ];
                    }
                } elseif ($newStatus === 'FAILED') {
                    if (!empty($txn['user_id'])) {
                        $userMessage = [
                            'id' => $txn['user_id'],
                            'message' => "Payment failed for Transaction {$txn['transaction_id']}. Amount: {$txn['amount_total']} UGX"
                        ];
                    }
                    if (!empty($txn['vendor_id'])) {
                        $vendorMessage = [
                            'id' => $txn['vendor_id'],
                            'message' => "Payment failed for Transaction {$txn['transaction_id']}. Amount: {$txn['amount_total']} UGX"
                        ];
                    }
                }

                $recipients = self::buildRecipients($adminMessage, $userMessage, $vendorMessage);

                self::sendNotification(
                    'Transaction Status Update',
                    $recipients,
                    $newStatus === 'FAILED' ? 'high' : 'normal',
                    $txn['user_id'] ?? $txn['vendor_id'] ?? null
                );
            }
        }

        if ($newStatus === 'SUCCESS' && !empty($txn)) {
            $txnId = $txn['transaction_id'];
            $amount = (float) $txn['amount_total'];
            $userId = $txn['user_id'];

            $wst = self::$pdo->query("
                SELECT platform_account_id
                  FROM zzimba_platform_account_settings
                 WHERE type = 'withholding'
                 LIMIT 1
            ");
            $withholdingId = $wst->fetchColumn();

            $chk = self::$pdo->prepare("
                SELECT COUNT(*)
                  FROM zzimba_transaction_entries
                 WHERE transaction_id = :tid
                   AND wallet_id      = :wid
                   AND entry_type     = 'CREDIT'
                   AND entry_note     = 'Zzimba Credit top-up'
            ");
            $chk->execute([':tid' => $txnId, ':wid' => $withholdingId]);
            if ((int) $chk->fetchColumn() > 0) {
                return $res;
            }

            try {
                self::$pdo->beginTransaction();

                $balStmt = self::$pdo->prepare("
                    SELECT current_balance
                      FROM zzimba_wallets
                     WHERE wallet_id = :wid
                       AND owner_type = 'PLATFORM'
                       AND status     = 'active'
                ");
                $balStmt->execute([':wid' => $withholdingId]);
                $withBal = (float) $balStmt->fetchColumn();

                $cash = self::$pdo->query("
                    SELECT id, current_balance
                      FROM zzimba_cash_accounts
                     WHERE type   = 'gateway'
                       AND status = 'active'
                     LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);

                $uStmt = self::$pdo->prepare("
                    SELECT wallet_id, wallet_number, current_balance
                      FROM zzimba_wallets
                     WHERE owner_type = 'USER'
                       AND user_id    = :uid
                       AND status     = 'active'
                ");
                $uStmt->execute([':uid' => $userId]);
                $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);

                $newWithBal = $withBal + $amount;
                $creditId = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newWithBal,
                    'entry_note' => 'Zzimba Credit top-up'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newWithBal, ':wid' => $withholdingId]);

                $debitBal = $newWithBal - $amount;
                $receiverNo = $uRow['wallet_number'];
                $debitId = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $debitBal,
                    'entry_note' => 'Disbursed to ' . $receiverNo,
                    'ref_entry_id' => $creditId
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $debitBal, ':wid' => $withholdingId]);

                $newUserBal = (float) $uRow['current_balance'] + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $uRow['wallet_id'],
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newUserBal,
                    'entry_note' => 'Credited User Wallet from Withholding',
                    'ref_entry_id' => $debitId
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newUserBal, ':wid' => $uRow['wallet_id']]);

                $newCashBal = (float) $cash['current_balance'] + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'cash_account_id' => $cash['id'],
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newCashBal,
                    'entry_note' => 'Credited Gateway Cash from Withholding',
                    'ref_entry_id' => $debitId
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_cash_accounts
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE id = :cid
                ")->execute([':bal' => $newCashBal, ':cid' => $cash['id']]);

                self::$pdo->commit();
            } catch (PDOException $e) {
                self::$pdo->rollBack();
                error_log('[ZzimbaCreditModule] ledger processing error: ' . $e->getMessage());
            }
        }

        return $res;
    }

    public static function getWallet(string $ownerType, ?string $ownerId = null): array
    {
        self::boot();

        if ($ownerType === 'PLATFORM') {
            $sql = "
            SELECT wallet_id, wallet_number, wallet_name,
                   current_balance, status, created_at
              FROM zzimba_wallets
             WHERE owner_type = 'PLATFORM'
               AND status     = 'active'
             LIMIT 1";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute();
        } else {
            $col = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
            $sql = "
            SELECT wallet_id, wallet_number, wallet_name,
                   current_balance, status, created_at
              FROM zzimba_wallets
             WHERE owner_type = :ot
               AND {$col}    = :oid
               AND status    = 'active'
             LIMIT 1";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([':ot' => $ownerType, ':oid' => $ownerId]);
        }

        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($wallet) {
            return ['success' => true, 'newAccount' => false, 'wallet' => $wallet];
        }

        $wid = \generateUlid();
        $wn = self::generateWalletNumber($wid);
        $now = date('Y-m-d H:i:s');
        $wname = 'My Wallet';

        if ($ownerType === 'USER') {
            $u = self::$pdo->prepare("SELECT first_name, last_name FROM zzimba_users WHERE id = :uid");
            $u->execute([':uid' => $ownerId]);
            if ($r = $u->fetch(PDO::FETCH_ASSOC)) {
                $wname = trim($r['first_name'] . ' ' . $r['last_name']);
            }
        } elseif ($ownerType === 'VENDOR') {
            $v = self::$pdo->prepare("SELECT name FROM vendor_stores WHERE id = :vid");
            $v->execute([':vid' => $ownerId]);
            if ($r = $v->fetch(PDO::FETCH_ASSOC)) {
                $wname = trim($r['name']);
            }
        }

        $fields = ['wallet_id', 'wallet_number', 'owner_type', 'wallet_name', 'current_balance', 'status', 'created_at', 'updated_at'];
        $placeholders = [':wid', ':wn', ':ot', ':wname', ':bal', ':st', ':created', ':updated'];
        $bind = [
            ':wid' => $wid,
            ':wn' => $wn,
            ':ot' => $ownerType,
            ':wname' => $wname,
            ':bal' => 0.00,
            ':st' => 'active',
            ':created' => $now,
            ':updated' => $now
        ];

        if ($ownerType === 'USER') {
            $fields[] = 'user_id';
            $placeholders[] = ':oid';
            $bind[':oid'] = $ownerId;
        } elseif ($ownerType === 'VENDOR') {
            $fields[] = 'vendor_id';
            $placeholders[] = ':oid';
            $bind[':oid'] = $ownerId;
        }

        $sql = sprintf(
            "INSERT INTO zzimba_wallets (%s) VALUES (%s)",
            implode(',', $fields),
            implode(',', $placeholders)
        );
        try {
            $ins = self::$pdo->prepare($sql);
            $ins->execute($bind);
        } catch (PDOException $e) {
            error_log("[ZzimbaCreditModule][getWallet] create wallet error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Could not create wallet'];
        }

        $recipients = self::buildRecipients(
            ['message' => "New wallet created for {$ownerType}: {$wname} (#{$wn})"],
            $ownerType === 'USER' ? ['id' => $ownerId, 'message' => "Welcome! Your wallet has been created successfully. Wallet Number: {$wn}"] : null,
            $ownerType === 'VENDOR' ? ['id' => $ownerId, 'message' => "Welcome! Your store wallet has been created successfully. Wallet Number: {$wn}"] : null
        );

        if (in_array($ownerType, ['USER', 'VENDOR'], true)) {
            $appKey = $ownerType === 'USER' ? 'users' : 'vendors';
            $bonusSql = "
            SELECT id, setting_value
              FROM zzimba_credit_settings
             WHERE setting_key  = 'welcome_bonus'
               AND status       = 'active'
               AND applicable_to IN ('all', :ap1)
             ORDER BY (applicable_to = 'all') DESC,
                      (applicable_to = :ap2) DESC
             LIMIT 1";
            $bst = self::$pdo->prepare($bonusSql);
            $bst->execute([':ap1' => $appKey, ':ap2' => $appKey]);
            $setting = $bst->fetch(PDO::FETCH_ASSOC);

            if ($setting && ($amount = (float) $setting['setting_value']) > 0) {
                $src = self::$pdo->prepare("
                SELECT w.wallet_id, w.current_balance
                  FROM zzimba_wallets w
                  JOIN zzimba_wallet_credit_assignments a
                    ON a.wallet_id = w.wallet_id
                 WHERE a.credit_setting_id = :cid
                   AND w.status            = 'active'
                 LIMIT 1
            ");
                $src->execute([':cid' => $setting['id']]);
                $source = $src->fetch(PDO::FETCH_ASSOC);

                if ($source && (float) $source['current_balance'] >= $amount) {
                    $sourceWalletId = $source['wallet_id'];
                    $txnId = \generateUlid();

                    self::insertTransaction([
                        'transaction_id' => $txnId,
                        'transaction_type' => 'TRANSFER',
                        'status' => 'SUCCESS',
                        'amount_total' => $amount,
                        'payment_method' => 'WALLET',
                        'wallet_id' => $wid,
                        'user_id' => $ownerType === 'USER' ? $ownerId : null,
                        'vendor_id' => $ownerType === 'VENDOR' ? $ownerId : null,
                        'note' => 'Welcome bonus',
                    ]);

                    self::insertTransfer([
                        'id' => \generateUlid(),
                        'wallet_from' => $sourceWalletId,
                        'wallet_to' => $wid,
                        'transaction_id' => $txnId,
                        'created_at' => $now
                    ]);

                    $notes = [
                        'debit' => "Welcome bonus request to {$wn}",
                        'credit1' => "Welcome bonus instruction for {$wn}",
                        'debit2' => "Welcome bonus executed for {$wn}",
                        'credit2' => "Welcome bonus for {$wn}"
                    ];

                    try {
                        self::$pdo->beginTransaction();
                        self::performTransferEntries($txnId, $sourceWalletId, $wid, $amount, $notes);
                        self::$pdo->commit();

                        $bonusRecipients = self::buildRecipients(
                            ['message' => "Welcome bonus of {$amount} UGX credited to new {$ownerType} wallet: {$wname} (#{$wn})"],
                            $ownerType === 'USER' ? ['id' => $ownerId, 'message' => "Congratulations! You've received a welcome bonus of {$amount} UGX in your new wallet."] : null,
                            $ownerType === 'VENDOR' ? ['id' => $ownerId, 'message' => "Congratulations! Your store has received a welcome bonus of {$amount} UGX in your new wallet."] : null
                        );

                        self::sendNotification('Welcome Bonus Credited', $bonusRecipients, 'normal', $ownerId);

                    } catch (\Throwable $e) {
                        self::$pdo->rollBack();
                        error_log("[ZzimbaCreditModule][getWallet] welcome‐bonus ledger error: " . $e->getMessage());
                        self::$pdo->prepare("
                        UPDATE zzimba_financial_transactions
                           SET status='FAILED', updated_at=NOW()
                         WHERE transaction_id = :tid
                    ")->execute([':tid' => $txnId]);
                    }
                }
            }
        }

        self::sendNotification('New Wallet Created', $recipients, 'normal', $ownerId);

        return [
            'success' => true,
            'newAccount' => true,
            'wallet' => [
                'wallet_id' => $wid,
                'wallet_number' => $wn,
                'wallet_name' => $wname,
                'current_balance' => 0.00,
                'status' => 'active',
                'created_at' => $now
            ]
        ];
    }

    public static function getWalletStatement(
        string $walletId,
        string $filter = 'all',
        ?string $start = null,
        ?string $end = null
    ): array {
        self::boot();

        $wStmt = self::$pdo->prepare("
            SELECT wallet_id, wallet_number, owner_type, user_id, vendor_id, wallet_name
              FROM zzimba_wallets
             WHERE wallet_id = :wid
               AND status    = 'active'
             LIMIT 1
        ");
        $wStmt->execute([':wid' => $walletId]);
        $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);
        if (!$wallet) {
            return [
                'success' => false,
                'statement' => [],
                'message' => 'Wallet not found or inactive'
            ];
        }

        $ftStmt = match ($wallet['owner_type']) {
            'USER' => self::$pdo->prepare("SELECT transaction_id FROM zzimba_financial_transactions WHERE user_id = :uid"),
            'VENDOR' => self::$pdo->prepare("SELECT transaction_id FROM zzimba_financial_transactions WHERE vendor_id = :vid"),
            default => self::$pdo->prepare("
                SELECT DISTINCT ft.transaction_id
                  FROM zzimba_financial_transactions ft
                  JOIN zzimba_transaction_entries e
                    ON ft.transaction_id = e.transaction_id
                 WHERE e.wallet_id = :wid
            "),
        };
        $param = $wallet['owner_type'] === 'USER' ? [':uid' => $wallet['user_id']] :
            ($wallet['owner_type'] === 'VENDOR' ? [':vid' => $wallet['vendor_id']] : [':wid' => $walletId]);
        $ftStmt->execute($param);
        $ftTxnIds = $ftStmt->fetchAll(PDO::FETCH_COLUMN);

        $etStmt = self::$pdo->prepare("
            SELECT DISTINCT transaction_id
              FROM zzimba_transaction_entries
             WHERE wallet_id = :wid
        ");
        $etStmt->execute([':wid' => $walletId]);
        $etTxnIds = $etStmt->fetchAll(PDO::FETCH_COLUMN);

        $allTxnIds = array_unique(array_merge($ftTxnIds, $etTxnIds));
        if (empty($allTxnIds)) {
            return [
                'success' => true,
                'wallet' => [
                    'wallet_id' => $wallet['wallet_id'],
                    'wallet_number' => $wallet['wallet_number'],
                    'owner_type' => $wallet['owner_type'],
                    'wallet_name' => $wallet['wallet_name'],
                ],
                'statement' => []
            ];
        }

        $placeholders = [];
        $params = [];
        foreach ($allTxnIds as $i => $tid) {
            $key = ":tid{$i}";
            $placeholders[] = $key;
            $params[$key] = $tid;
        }
        $inList = implode(',', $placeholders);
        $sql = "SELECT * FROM zzimba_financial_transactions WHERE transaction_id IN ($inList)";
        if ($filter === 'range') {
            if (!$start || !$end) {
                return ['success' => false, 'statement' => [], 'message' => 'Both start and end required for range'];
            }
            $sql .= " AND created_at BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $start;
            $params[':endDate'] = $end;
        }

        $sql .= " ORDER BY created_at DESC,
                   CASE 
                     WHEN original_txn_id IS NOT NULL THEN 1 
                     ELSE 2 
                   END ASC";

        $txnStmt = self::$pdo->prepare($sql);
        $txnStmt->execute($params);
        $txns = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

        $statement = [];
        foreach ($txns as $txn) {
            $txn['entries'] = self::fetchEntriesRecursively($txn['transaction_id'], $walletId);
            $statement[] = ['transaction' => $txn];
        }

        return [
            'success' => true,
            'wallet' => [
                'wallet_id' => $wallet['wallet_id'],
                'wallet_number' => $wallet['wallet_number'],
                'owner_type' => $wallet['owner_type'],
                'wallet_name' => $wallet['wallet_name'],
            ],
            'statement' => $statement
        ];
    }

    private static function fetchEntriesRecursively(string $txnId, string $walletId): array
    {
        $stmt = self::$pdo->prepare("
            SELECT
                e.*,
                COALESCE(ca.name, w.wallet_name) AS account_or_wallet_name,
                w.owner_type
              FROM zzimba_transaction_entries e
              LEFT JOIN zzimba_cash_accounts ca ON e.cash_account_id = ca.id
              LEFT JOIN zzimba_wallets      w  ON e.wallet_id        = w.wallet_id
             WHERE e.transaction_id = :tid
               AND e.wallet_id      = :wid
             ORDER BY e.created_at ASC
        ");
        $stmt->execute([':tid' => $txnId, ':wid' => $walletId]);

        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $visited = [];

        foreach ($entries as &$entry) {
            $entry = self::buildEntryTree($entry, $visited);
        }

        return $entries;
    }

    private static function buildEntryTree(array $entry, array &$visited): array
    {
        $id = $entry['entry_id'];
        if (isset($visited[$id])) {
            return $entry;
        }
        $visited[$id] = true;

        $ancestors = [];
        $parentId = $entry['ref_entry_id'];
        while ($parentId) {
            $pStmt = self::$pdo->prepare("
                SELECT
                    e.*,
                    COALESCE(ca.name, w.wallet_name) AS account_or_wallet_name,
                    w.owner_type
                  FROM zzimba_transaction_entries e
                  LEFT JOIN zzimba_cash_accounts ca ON e.cash_account_id = ca.id
                  LEFT JOIN zzimba_wallets      w  ON e.wallet_id        = w.wallet_id
                 WHERE e.entry_id = :eid
                 LIMIT 1
            ");
            $pStmt->execute([':eid' => $parentId]);
            $parent = $pStmt->fetch(PDO::FETCH_ASSOC);
            if (!$parent || isset($visited[$parent['entry_id']])) {
                break;
            }
            $visited[$parent['entry_id']] = true;
            $ancestors[] = $parent;
            $parentId = $parent['ref_entry_id'];
        }

        $childrenStmt = self::$pdo->prepare("
            SELECT
                e.*,
                COALESCE(ca.name, w.wallet_name) AS account_or_wallet_name,
                w.owner_type
              FROM zzimba_transaction_entries e
              LEFT JOIN zzimba_cash_accounts ca ON e.cash_account_id = ca.id
              LEFT JOIN zzimba_wallets      w  ON e.wallet_id        = w.wallet_id
             WHERE e.ref_entry_id = :eid
             ORDER BY e.created_at ASC
        ");
        $childrenStmt->execute([':eid' => $id]);
        $children = $childrenStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($children as &$child) {
            $child = self::buildEntryTree($child, $visited);
        }

        $entry['related_entries'] = array_merge($ancestors, $children);

        return $entry;
    }

    public static function transfer(array $opts): array
    {
        $walletTo = trim($opts['wallet_to'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);

        if ($walletTo === '' || $amount < 500) {
            return ['success' => false, 'message' => 'Destination Account No. and amount ≥ 500 required'];
        }

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        self::boot();

        $feeSettings = self::getFeeSettingsForUser();
        $transferFee = self::calculateTransferFee($amount, $feeSettings);
        $totalRequired = $amount + $transferFee;

        $sourceStmt = self::$pdo->prepare("
            SELECT wallet_id, current_balance
              FROM zzimba_wallets
             WHERE owner_type = 'USER'
               AND user_id    = :uid
               AND status     = 'active'
             LIMIT 1
        ");
        $sourceStmt->execute([':uid' => $userId]);
        $sourceRow = $sourceStmt->fetch(PDO::FETCH_ASSOC);
        if (!$sourceRow) {
            return ['success' => false, 'message' => 'Source wallet not found'];
        }
        $fromWalletId = $sourceRow['wallet_id'];
        $fromBalance = (float) $sourceRow['current_balance'];

        $destStmt = self::$pdo->prepare("
            SELECT wallet_id, owner_type, user_id, vendor_id, current_balance
              FROM zzimba_wallets
             WHERE wallet_number = :wn
               AND status        = 'active'
             LIMIT 1
        ");
        $destStmt->execute([':wn' => $walletTo]);
        $destRow = $destStmt->fetch(PDO::FETCH_ASSOC);
        if (!$destRow) {
            return ['success' => false, 'message' => 'Destination wallet not found or inactive'];
        }
        $toWalletId = $destRow['wallet_id'];
        $toOwnerType = $destRow['owner_type'];

        if ($fromWalletId === $toWalletId) {
            return ['success' => false, 'message' => 'Cannot transfer to the same wallet'];
        }
        if ($fromBalance < $totalRequired) {
            return ['success' => false, 'message' => 'Insufficient funds for transfer and fees'];
        }

        $chargeDestinationWallet = null;
        if ($transferFee > 0 && $feeSettings) {
            $chargeDestinationWallet = self::getChargeDestinationWallet($feeSettings['id']);
            if (!$chargeDestinationWallet) {
                return ['success' => false, 'message' => 'Charge destination wallet not configured'];
            }
        }

        $transferTxnId = \generateUlid();
        self::insertTransaction([
            'transaction_id' => $transferTxnId,
            'transaction_type' => 'TRANSFER',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'WALLET',
            'user_id' => $userId,
            'vendor_id' => $toOwnerType === 'VENDOR' ? $destRow['vendor_id'] : null,
            'note' => 'Zzimba Credit transfer'
        ]);

        self::insertTransfer([
            'id' => \generateUlid(),
            'wallet_from' => $fromWalletId,
            'wallet_to' => $toWalletId,
            'transaction_id' => $transferTxnId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $chargesTxnId = null;
        if ($transferFee > 0 && $chargeDestinationWallet) {
            $chargesTxnId = \generateUlid();
            self::insertTransaction([
                'transaction_id' => $chargesTxnId,
                'transaction_type' => 'CHARGES',
                'status' => 'PENDING',
                'amount_total' => $transferFee,
                'payment_method' => 'WALLET',
                'user_id' => $userId,
                'original_txn_id' => $transferTxnId,
                'note' => 'Transfer fee charges'
            ]);

            self::insertTransfer([
                'id' => \generateUlid(),
                'wallet_from' => $fromWalletId,
                'wallet_to' => $chargeDestinationWallet['wallet_id'],
                'transaction_id' => $chargesTxnId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        try {
            self::$pdo->beginTransaction();

            $wnStmt = self::$pdo->prepare("
                SELECT wallet_number 
                  FROM zzimba_wallets 
                 WHERE wallet_id = :wid
            ");
            $wnStmt->execute([':wid' => $fromWalletId]);
            $fromNo = $wnStmt->fetchColumn();
            $wnStmt->execute([':wid' => $toWalletId]);
            $toNo = $wnStmt->fetchColumn();

            $transferNotes = [
                'debit' => 'Zzimba Credit transfer to ' . $toNo,
                'credit1' => 'Credit transfer instruction from ' . $fromNo,
                'debit2' => 'Credit transfer executed for ' . $toNo,
                'credit2' => 'Zzimba Credit transfer from ' . $fromNo,
            ];

            self::performTransferEntries($transferTxnId, $fromWalletId, $toWalletId, $amount, $transferNotes);

            if ($transferFee > 0 && $chargeDestinationWallet && $chargesTxnId) {
                $chargeNotes = [
                    'debit' => 'Transfer fee charge for transaction to ' . $toNo,
                    'credit1' => 'Transfer fee collection from ' . $fromNo,
                    'debit2' => 'Transfer fee disbursement for ' . $fromNo,
                    'credit2' => 'Transfer fee earned from ' . $fromNo,
                ];

                self::performTransferEntries($chargesTxnId, $fromWalletId, $chargeDestinationWallet['wallet_id'], $transferFee, $chargeNotes);

                self::$pdo->prepare("
                    UPDATE zzimba_financial_transactions
                       SET status     = 'SUCCESS',
                           updated_at = NOW()
                     WHERE transaction_id = :tid
                ")->execute([':tid' => $chargesTxnId]);
            }

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status     = 'SUCCESS',
                       updated_at = NOW()
                 WHERE transaction_id = :tid
            ")->execute([':tid' => $transferTxnId]);

            self::$pdo->commit();

            $ownerStmt = self::$pdo->prepare("
                SELECT owner_type,
                       CASE WHEN owner_type = 'USER' THEN user_id ELSE vendor_id END AS entity_id
                  FROM zzimba_wallets
                 WHERE wallet_id = :wid
                 LIMIT 1
            ");

            $ownerStmt->execute([':wid' => $fromWalletId]);
            $fromOwner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
            $senderEntity = self::getEntityInfo(
                $fromOwner['owner_type'] === 'USER' ? $fromOwner['entity_id'] : null,
                $fromOwner['owner_type'] === 'VENDOR' ? $fromOwner['entity_id'] : null
            );

            $ownerStmt->execute([':wid' => $toWalletId]);
            $toOwner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
            $recipientEntity = self::getEntityInfo(
                $toOwner['owner_type'] === 'USER' ? $toOwner['entity_id'] : null,
                $toOwner['owner_type'] === 'VENDOR' ? $toOwner['entity_id'] : null
            );

            $feeMessage = $transferFee > 0 ? " (Fee: {$transferFee} UGX)" : "";
            $recipients = self::buildRecipients(
                ['message' => "{$senderEntity['type']} {$senderEntity['name']} sent {$amount} UGX to {$recipientEntity['type']} {$recipientEntity['name']}.{$feeMessage}"],
                ['id' => $senderEntity['id'], 'message' => "You sent {$amount} UGX to {$recipientEntity['type']} {$recipientEntity['name']}.{$feeMessage}"],
                $recipientEntity['type'] === 'Store' ? ['id' => $recipientEntity['id'], 'message' => "{$senderEntity['type']} {$senderEntity['name']} sent you {$amount} UGX."] : null
            );

            if ($recipientEntity['type'] === 'User') {
                $recipients[] = [
                    'type' => 'user',
                    'id' => $recipientEntity['id'],
                    'message' => "{$senderEntity['type']} {$senderEntity['name']} sent you {$amount} UGX."
                ];
            }

            self::sendNotification('Wallet Transfer Completed', $recipients, 'normal', $senderEntity['id']);

        } catch (PDOException $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] transfer error: ' . $e->getMessage());

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status     = 'FAILED',
                       updated_at = NOW()
                 WHERE transaction_id = :tid
            ")->execute([':tid' => $transferTxnId]);

            if ($chargesTxnId) {
                self::$pdo->prepare("
                    UPDATE zzimba_financial_transactions
                       SET status     = 'FAILED',
                           updated_at = NOW()
                     WHERE transaction_id = :tid
                ")->execute([':tid' => $chargesTxnId]);
            }

            return ['success' => false, 'message' => 'Transfer failed'];
        }

        $balanceStmt = self::$pdo->prepare("
            SELECT current_balance
              FROM zzimba_wallets
             WHERE wallet_id = :wid
             LIMIT 1
        ");
        $balanceStmt->execute([':wid' => $fromWalletId]);
        $newBalance = (float) $balanceStmt->fetchColumn();

        return [
            'success' => true,
            'transaction_id' => $transferTxnId,
            'charges_transaction_id' => $chargesTxnId,
            'balance' => $newBalance,
            'fee_charged' => $transferFee,
            'total_deducted' => $totalRequired
        ];
    }

    private static function getWithholdingAccountId(): string
    {
        return self::$pdo->query("
            SELECT platform_account_id
              FROM zzimba_platform_account_settings
             WHERE type = 'withholding'
             LIMIT 1
        ")->fetchColumn();
    }

    private static function updateWalletBalance(string $walletId, float $balance): void
    {
        self::$pdo->prepare("
            UPDATE zzimba_wallets
               SET current_balance = :bal, updated_at = NOW()
             WHERE wallet_id = :wid
        ")->execute([':bal' => $balance, ':wid' => $walletId]);
    }

    private static function insertTransfer(array $row): void
    {
        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_wallet_transfers ($cols) VALUES ($params)";
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($row);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] insert transfer error: ' . $e->getMessage());
        }
    }

    private static function performTransferEntries(
        string $txnId,
        string $fromWalletId,
        string $toWalletId,
        float $amount,
        array $entryNotes
    ): void {

        $withholdingId = self::getWithholdingAccountId();

        $balStmt = self::$pdo->prepare("SELECT current_balance FROM zzimba_wallets WHERE wallet_id = :wid");
        $balStmt->execute([':wid' => $fromWalletId]);
        $fromBal = (float) $balStmt->fetchColumn();

        $balStmt->execute([':wid' => $withholdingId]);
        $withBal = (float) $balStmt->fetchColumn();

        $balStmt->execute([':wid' => $toWalletId]);
        $toBal = (float) $balStmt->fetchColumn();

        $debitId = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $fromWalletId,
            'entry_type' => 'DEBIT',
            'amount' => $amount,
            'balance_after' => $fromBal - $amount,
            'entry_note' => $entryNotes['debit']
        ]);
        self::updateWalletBalance($fromWalletId, $fromBal - $amount);

        $credit1Id = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $withholdingId,
            'entry_type' => 'CREDIT',
            'amount' => $amount,
            'balance_after' => $withBal + $amount,
            'entry_note' => $entryNotes['credit1'],
            'ref_entry_id' => $debitId
        ]);
        self::updateWalletBalance($withholdingId, $withBal + $amount);

        $debit2Id = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $withholdingId,
            'entry_type' => 'DEBIT',
            'amount' => $amount,
            'balance_after' => ($withBal + $amount) - $amount,
            'entry_note' => $entryNotes['debit2'],
            'ref_entry_id' => $credit1Id
        ]);
        self::updateWalletBalance($withholdingId, ($withBal + $amount) - $amount);

        self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $toWalletId,
            'entry_type' => 'CREDIT',
            'amount' => $amount,
            'balance_after' => $toBal + $amount,
            'entry_note' => $entryNotes['credit2'],
            'ref_entry_id' => $debit2Id
        ]);
        self::updateWalletBalance($toWalletId, $toBal + $amount);
    }

    private static function apiRequest(string $url, array $data, string $method = 'POST'): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/vnd.relworx.v2',
            'Authorization: Bearer ' . self::API_KEY
        ]);

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http !== 200) {
            error_log('[ZzimbaCreditModule] API error: ' . ($resp ?: $err));
            return json_encode(['success' => false, 'message' => 'API request failed']);
        }

        return $resp;
    }

    private static function insertTransaction(array $row): void
    {
        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_financial_transactions ($cols, created_at, updated_at) VALUES ($params, NOW(), NOW())";

        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($row);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] insert txn error: ' . $e->getMessage());
            throw new \RuntimeException('Transaction insert failed: ' . $e->getMessage());
        }
    }

    private static function insertEntry(array $row): string
    {
        $row['entry_id'] = \generateUlid();
        $row['created_at'] = date('Y-m-d H:i:s');

        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_transaction_entries ($cols) VALUES ($params)";
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($row);
            return $row['entry_id'];
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] insert entry error: ' . $e->getMessage());
            return '';
        }
    }

    public static function logCashTopup(array $opts): array
    {
        self::boot();

        $walletId = trim($opts['wallet_id'] ?? '');
        $cashAccountId = trim($opts['cash_account_id'] ?? '');
        $amount = (float) ($opts['amount_total'] ?? 0);
        $paymentMethod = strtoupper(trim($opts['payment_method'] ?? ''));
        $externalRef = trim($opts['external_reference'] ?? '');
        $note = trim($opts['note'] ?? '');

        $userId = $opts['user_id'] ?? null;
        $vendorId = $opts['vendor_id'] ?? null;

        $mmPhoneNumber = trim($opts['mmPhoneNumber'] ?? '');
        $mmDateTime = trim($opts['mmDateTime'] ?? '');
        $btDepositorName = trim($opts['btDepositorName'] ?? '');
        $btDateTime = trim($opts['btDateTime'] ?? '');

        if (
            !$walletId ||
            !$cashAccountId ||
            $amount <= 0 ||
            !in_array($paymentMethod, ['BANK', 'MOBILE_MONEY'], true)
        ) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        if ($paymentMethod === 'MOBILE_MONEY' && (!$mmPhoneNumber || !$mmDateTime)) {
            return ['success' => false, 'message' => 'Phone & date/time required'];
        }
        if ($paymentMethod === 'BANK' && (!$btDepositorName || !$btDateTime)) {
            return ['success' => false, 'message' => 'Depositor & date/time required'];
        }

        $w = self::$pdo->prepare("
            SELECT owner_type, wallet_number
              FROM zzimba_wallets
             WHERE wallet_id = :wid
               AND status    = 'active'
             LIMIT 1
        ");
        $w->execute([':wid' => $walletId]);
        $walletRow = $w->fetch(PDO::FETCH_ASSOC);
        if (!$walletRow) {
            return ['success' => false, 'message' => 'Wallet not found or inactive'];
        }
        $ownerType = $walletRow['owner_type'];

        $txnId = \generateUlid();
        $insertPayload = [
            'transaction_id' => $txnId,
            'transaction_type' => 'TOPUP',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => $paymentMethod,
            'external_reference' => $externalRef,
            'external_metadata' => json_encode($opts),
            'wallet_id' => $walletId,
            'note' => $note,
        ];

        if ($ownerType === 'USER' && !empty($userId)) {
            $insertPayload['user_id'] = $userId;
        } elseif ($ownerType === 'VENDOR' && !empty($vendorId)) {
            $insertPayload['vendor_id'] = $vendorId;
        }

        try {
            self::insertTransaction($insertPayload);
        } catch (\Throwable $e) {
            error_log('[ZzimbaCreditModule] Txn insert error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not save transaction'];
        }

        $recipients = self::buildRecipients(
            ['message' => "Cash top-up request logged: {$amount} UGX via {$paymentMethod}. Transaction ID: {$txnId}. Awaiting confirmation."],
            ($ownerType === 'USER' && !empty($userId)) ? ['id' => $userId, 'message' => "Your cash top-up request of {$amount} UGX has been logged and is pending admin confirmation."] : null,
            ($ownerType === 'VENDOR' && !empty($vendorId)) ? ['id' => $vendorId, 'message' => "Your store's cash top-up request of {$amount} UGX has been logged and is pending admin confirmation."] : null
        );

        self::sendNotification('Cash Top-up Request Logged', $recipients, 'normal', $userId ?? $vendorId ?? null);

        try {
            $walletNameStmt = self::$pdo->prepare("
                SELECT wallet_name
                  FROM zzimba_wallets
                 WHERE wallet_id = :wid
                 LIMIT 1
            ");
            $walletNameStmt->execute([':wid' => $walletId]);
            $initiator = $walletNameStmt->fetchColumn() ?: $walletId;

            $adminPhones = self::$pdo
                ->query("SELECT phone FROM admin_users WHERE status = 'active'")
                ->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($adminPhones)) {
                $message = sprintf(
                    "%s initiated a cash top-up of %s UGX. Txn ID: %s. Login to confirm.",
                    $initiator,
                    number_format($amount, 2),
                    $txnId
                );
                \SMS::sendBulk($adminPhones, $message);
            }
        } catch (\Throwable $e) {
            error_log('[ZzimbaCreditModule] SMS error: ' . $e->getMessage());
        }

        return ['success' => true, 'transaction_id' => $txnId];
    }

    public static function acknowledgeCashTopup(string $transactionId, string $newStatus): array
    {
        self::boot();
        $newStatus = strtoupper($newStatus);
        if (!in_array($newStatus, ['SUCCESS', 'FAILED'], true)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $upd = self::$pdo->prepare("
            UPDATE zzimba_financial_transactions
               SET status = :st, updated_at = NOW()
             WHERE transaction_id = :tid
        ");
        $upd->execute([':st' => $newStatus, ':tid' => $transactionId]);

        $stmt = self::$pdo->prepare("
            SELECT amount_total, external_metadata, wallet_id, user_id, vendor_id
              FROM zzimba_financial_transactions
             WHERE transaction_id = :tid
        ");
        $stmt->execute([':tid' => $transactionId]);
        $txn = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$txn) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        $amount = (float) $txn['amount_total'];

        if ($newStatus === 'FAILED') {
            $recipients = self::buildRecipients(
                ['message' => "Cash top-up {$transactionId} marked as {$newStatus}. Amount: {$amount} UGX"],
                !empty($txn['user_id']) ? ['id' => $txn['user_id'], 'message' => "Your cash top-up request of {$amount} UGX has been declined. Please contact support for assistance."] : null,
                !empty($txn['vendor_id']) ? ['id' => $txn['vendor_id'], 'message' => "Your store's cash top-up request of {$amount} UGX has been declined. Please contact support for assistance."] : null
            );

            self::sendNotification('Cash Top-up Declined', $recipients, 'high', $txn['user_id'] ?? $txn['vendor_id'] ?? null);
            return ['success' => true, 'message' => 'Top-up marked as failed'];
        }

        $meta = json_decode($txn['external_metadata'], true);
        $cashAccountId = $meta['cash_account_id'] ?? null;

        $receiverWalletId = $txn['wallet_id'] ?? null;
        if (!$receiverWalletId) {
            $ownerType = $txn['user_id'] ? 'USER' : 'VENDOR';
            $ownerId = $txn['user_id'] ?? $txn['vendor_id'] ?? null;
            $walletRes = self::getWallet($ownerType, $ownerId);
            if (!$walletRes['success']) {
                return ['success' => false, 'message' => 'Wallet not found'];
            }
            $wallet = $walletRes['wallet'];
        } else {
            $stmtWallet = self::$pdo->prepare("
                SELECT wallet_id, wallet_number, current_balance
                  FROM zzimba_wallets
                 WHERE wallet_id = :wid AND status = 'active'
            ");
            $stmtWallet->execute([':wid' => $receiverWalletId]);
            $wallet = $stmtWallet->fetch(PDO::FETCH_ASSOC);
            if (!$wallet) {
                return ['success' => false, 'message' => 'Wallet not found or inactive'];
            }
        }

        $receiverWalletId = $wallet['wallet_id'];
        $receiverWalletNo = $wallet['wallet_number'];
        $currBal = (float) $wallet['current_balance'];

        if (!$cashAccountId) {
            return ['success' => false, 'message' => 'Cash account not specified'];
        }

        try {
            self::$pdo->beginTransaction();

            $withholdingId = self::getWithholdingAccountId();
            $balStmt = self::$pdo->prepare("
                SELECT current_balance
                  FROM zzimba_wallets
                 WHERE wallet_id = :wid
            ");
            $balStmt->execute([':wid' => $withholdingId]);
            $withBal = (float) $balStmt->fetchColumn();

            $newWithBal = $withBal + $amount;
            $creditId = self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $newWithBal,
                'entry_note' => 'Zzimba Credit top-up'
            ]);
            self::updateWalletBalance($withholdingId, $newWithBal);

            $debitBal = $newWithBal - $amount;
            $receiverNo = $receiverWalletNo;
            $debitId = self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => $debitBal,
                'entry_note' => 'Disbursed to ' . $receiverNo,
                'ref_entry_id' => $creditId
            ]);
            self::updateWalletBalance($withholdingId, $debitBal);

            $newWBal = $currBal + $amount;
            self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $receiverWalletId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $newWBal,
                'entry_note' => 'Zzimba Credit top-up',
                'ref_entry_id' => $debitId
            ]);
            self::$pdo->prepare("
                UPDATE zzimba_wallets
                   SET current_balance = :bal, updated_at = NOW()
                 WHERE wallet_id = :wid
            ")->execute([':bal' => $newWBal, ':wid' => $receiverWalletId]);

            $cStmt = self::$pdo->prepare("
                SELECT current_balance
                  FROM zzimba_cash_accounts
                 WHERE id = :cid FOR UPDATE
            ");
            $cStmt->execute([':cid' => $cashAccountId]);
            $cashBal = (float) $cStmt->fetchColumn();
            $newCash = $cashBal + $amount;

            self::insertEntry([
                'transaction_id' => $transactionId,
                'cash_account_id' => $cashAccountId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $newCash,
                'entry_note' => 'Cash account credited from withholding',
                'ref_entry_id' => $debitId
            ]);
            self::$pdo->prepare("
                UPDATE zzimba_cash_accounts
                   SET current_balance = :bal, updated_at = NOW()
                 WHERE id = :cid
            ")->execute([':bal' => $newCash, ':cid' => $cashAccountId]);

            self::$pdo->commit();

            $recipients = self::buildRecipients(
                ['message' => "Cash top-up {$transactionId} marked as {$newStatus}. Amount: {$amount} UGX"],
                !empty($txn['user_id']) ? ['id' => $txn['user_id'], 'message' => "Great news! Your cash top-up of {$amount} UGX has been approved and credited to your wallet."] : null,
                !empty($txn['vendor_id']) ? ['id' => $txn['vendor_id'], 'message' => "Great news! Your store's cash top-up of {$amount} UGX has been approved and credited to your wallet."] : null
            );

            self::sendNotification('Cash Top-up Approved', $recipients, 'normal', $txn['user_id'] ?? $txn['vendor_id'] ?? null);

        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] top-up ledger error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ledger processing failed'];
        }

        return ['success' => true];
    }

    public static function purchaseSmsCredits(array $opts): array
    {
        self::boot();

        $walletId = trim($opts['wallet_id'] ?? '');
        $ownerType = strtoupper(trim($opts['owner_type'] ?? ''));
        $amount = isset($opts['amount']) ? (float) $opts['amount'] : 0;
        if (!$walletId || !in_array($ownerType, ['USER', 'VENDOR'], true) || $amount <= 0) {
            return ['success' => false, 'message' => 'Invalid wallet, owner_type or amount'];
        }
        $ownerIdKey = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
        $ownerId = trim($opts[$ownerIdKey] ?? '');
        if (!$ownerId) {
            return ['success' => false, 'message' => "Missing {$ownerIdKey}"];
        }

        $stmt = self::$pdo->prepare("
            SELECT wallet_number, current_balance
              FROM zzimba_wallets
             WHERE wallet_id   = :wid
               AND owner_type  = :ot
               AND status      = 'active'
             LIMIT 1
        ");
        $stmt->execute([':wid' => $walletId, ':ot' => $ownerType]);
        $debitWallet = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$debitWallet) {
            return ['success' => false, 'message' => 'Debit wallet not found or inactive'];
        }
        if ($debitWallet['current_balance'] < $amount) {
            return ['success' => false, 'message' => 'Insufficient funds'];
        }

        $appKey = strtolower($ownerType) . 's';
        $bst = self::$pdo->prepare("
            SELECT id
              FROM zzimba_credit_settings
             WHERE setting_key = 'sms_cost'
               AND status      = 'active'
               AND applicable_to IN ('all', :ap1)
             ORDER BY (applicable_to = 'all') DESC,
                      (applicable_to = :ap2) DESC
             LIMIT 1
        ");
        $bst->execute([':ap1' => $appKey, ':ap2' => $appKey]);
        $setting = $bst->fetch(PDO::FETCH_ASSOC);
        if (!$setting) {
            return ['success' => false, 'message' => 'SMS cost setting not found'];
        }
        $settingId = $setting['id'];

        $src = self::$pdo->prepare("
            SELECT w.wallet_id, w.wallet_number, w.current_balance
              FROM zzimba_wallets w
              JOIN zzimba_wallet_credit_assignments a
                ON a.wallet_id = w.wallet_id
             WHERE a.credit_setting_id = :cid
               AND w.status            = 'active'
             LIMIT 1
        ");
        $src->execute([':cid' => $settingId]);
        $creditWallet = $src->fetch(PDO::FETCH_ASSOC);
        if (!$creditWallet) {
            return ['success' => false, 'message' => 'SMS-sink wallet not configured'];
        }

        $txnId = \generateUlid();
        self::insertTransaction([
            'transaction_id' => $txnId,
            'transaction_type' => 'SMS_PURCHASE',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'WALLET',
            'wallet_id' => $walletId,
            'user_id' => $ownerType === 'USER' ? $ownerId : null,
            'vendor_id' => $ownerType === 'VENDOR' ? $ownerId : null,
            'note' => 'SMS credits purchase'
        ]);

        self::insertTransfer([
            'id' => \generateUlid(),
            'wallet_from' => $walletId,
            'wallet_to' => $creditWallet['wallet_id'],
            'transaction_id' => $txnId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $debitNo = $debitWallet['wallet_number'];
        $creditNo = $creditWallet['wallet_number'];
        $notes = [
            'debit' => "SMS credits purchase",
            'credit1' => "SMS purchase instruction from {$debitNo}",
            'debit2' => "SMS purchase, credit sent {$creditNo}",
            'credit2' => "SMS credits purchased by {$debitNo}",
        ];

        try {
            self::$pdo->beginTransaction();

            self::performTransferEntries($txnId, $walletId, $creditWallet['wallet_id'], $amount, $notes);

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status     = 'SUCCESS',
                       updated_at = NOW()
                 WHERE transaction_id = :tid
            ")->execute([':tid' => $txnId]);

            self::$pdo->commit();

            $smsCredits = $amount / 100;
            $entity = self::getEntityInfo(
                $ownerType === 'USER' ? $ownerId : null,
                $ownerType === 'VENDOR' ? $ownerId : null
            );

            $recipients = self::buildRecipients(
                ['message' => "{$entity['type']} {$entity['name']} purchased {$smsCredits} SMS credits for {$amount} UGX."],
                $ownerType === 'USER' ? ['id' => $ownerId, 'message' => "SMS credits purchase successful! You've purchased {$smsCredits} SMS credits for {$amount} UGX."] : null,
                $ownerType === 'VENDOR' ? ['id' => $ownerId, 'message' => "SMS credits purchase successful! Your store has purchased {$smsCredits} SMS credits for {$amount} UGX."] : null
            );

            self::sendNotification('SMS Credits Purchase Completed', $recipients, 'normal', $ownerId);

        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] SMS purchase error: ' . $e->getMessage());

            self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status     = 'FAILED',
                       updated_at = NOW()
                 WHERE transaction_id = :tid
            ")->execute([':tid' => $txnId]);

            return ['success' => false, 'message' => 'SMS purchase failed'];
        }

        return [
            'success' => true,
            'transaction_id' => $txnId,
            'amount' => $amount
        ];
    }
}
