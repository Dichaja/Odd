<?php
/**
 * ZzimbaCreditModule.php
 *
 * Namespace wrapper around Relworx mobile-money utilities plus the
 * local bookkeeping that Zzimba Online needs.
 *
 * Exposed public methods
 *   • validateMsisdn(string $msisdn): array
 *   • makeMobileMoneyPayment(array $opts): array
 *   • checkRequestStatus(string $internalRef): array
 *   • getWallet(string $ownerType, string $ownerId = null): array
 *
 * Requirements
 *   • config.php provides $pdo and generateUlid()
 *   • PHP 8.1+
 */
namespace ZzimbaCreditModule;

use PDO;
use PDOException;

require_once __DIR__ . '/../config/config.php';

final class CreditService
{
    /* ------------------------------------------------------------------ */
    /*  Static configuration / bootstrap                                  */
    /* ------------------------------------------------------------------ */
    private const API_KEY = '56cded6ede99ac.BYJV1ceTwWbN_NzaqIchUw';
    private const ACCOUNT_NO = 'REL2C6A94761B';
    private const DEFAULT_CURRENCY = 'UGX';

    private static bool $ready = false;
    private static PDO $pdo;

    private static function boot(): void
    {
        if (self::$ready) {
            return;
        }

        /** @var PDO $pdo */
        global $pdo;
        self::$pdo = $pdo;

        /* ---------- tables ---------- */
        $createFinancial = "
            CREATE TABLE IF NOT EXISTS zzimba_financial_transactions (
              transaction_id      VARCHAR(26) NOT NULL PRIMARY KEY,
              transaction_type    ENUM('TOPUP','PURCHASE','SUBSCRIPTION','SMS_PURCHASE','EMAIL_PURCHASE',
                                        'PREMIUM_FEATURE','REFUND','WITHDRAWAL') NOT NULL,
              status              ENUM('PENDING','SUCCESS','FAILED','REFUNDED','DISPUTED') NOT NULL DEFAULT 'PENDING',
              amount_total        DECIMAL(15,2) NOT NULL,
              payment_method      ENUM('MOBILE_MONEY_GATEWAY','MOBILE_MONEY','CARD','WALLET') DEFAULT NULL,
              external_reference  VARCHAR(100) DEFAULT NULL,
              external_metadata   TEXT DEFAULT NULL,
              user_id             VARCHAR(26) DEFAULT NULL,
              vendor_id           VARCHAR(26) DEFAULT NULL,
              original_txn_id     VARCHAR(26) DEFAULT NULL,
              note                VARCHAR(255) DEFAULT NULL,
              created_at          DATETIME NOT NULL,
              updated_at          DATETIME NOT NULL,
              CONSTRAINT fk_txn_user     FOREIGN KEY (user_id)     REFERENCES zzimba_users(id)
                                           ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_txn_vendor   FOREIGN KEY (vendor_id)   REFERENCES vendor_stores(id)
                                           ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_txn_original FOREIGN KEY (original_txn_id)
                                           REFERENCES zzimba_financial_transactions(transaction_id)
                                           ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createEntries = "
            CREATE TABLE IF NOT EXISTS zzimba_transaction_entries (
              entry_id           CHAR(26) NOT NULL,
              transaction_id     VARCHAR(26) NOT NULL,
              wallet_id          CHAR(26) DEFAULT NULL,
              cash_account_id    CHAR(26) DEFAULT NULL,
              ref_entry_id       CHAR(26) DEFAULT NULL,
              entry_type         ENUM('DEBIT','CREDIT') NOT NULL,
              amount             DECIMAL(18,2) NOT NULL,
              balance_after      DECIMAL(18,2) NOT NULL,
              entry_note         VARCHAR(255) DEFAULT NULL,
              created_at         DATETIME NOT NULL,
              PRIMARY KEY (entry_id),
              CONSTRAINT fk_entry_transaction FOREIGN KEY (transaction_id)
                         REFERENCES zzimba_financial_transactions(transaction_id)
                         ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_entry_wallet FOREIGN KEY (wallet_id)
                         REFERENCES zzimba_wallets(wallet_id)
                         ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_entry_cash FOREIGN KEY (cash_account_id)
                         REFERENCES zzimba_cash_accounts(id)
                         ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_entry_ref FOREIGN KEY (ref_entry_id)
                         REFERENCES zzimba_transaction_entries(entry_id)
                         ON UPDATE CASCADE ON DELETE RESTRICT,
              INDEX idx_entry_txn (transaction_id),
              INDEX idx_entry_wallet (wallet_id),
              INDEX idx_entry_cash (cash_account_id),
              INDEX idx_entry_ref (ref_entry_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createWallets = "
            CREATE TABLE IF NOT EXISTS zzimba_wallets (
              wallet_id       CHAR(26) NOT NULL PRIMARY KEY,
              owner_type      ENUM('USER','VENDOR','PLATFORM') NOT NULL,
              user_id         CHAR(26) DEFAULT NULL,
              vendor_id       CHAR(26) DEFAULT NULL,
              wallet_name     VARCHAR(100) NOT NULL,
              current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
              status          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
              created_at      DATETIME NOT NULL,
              updated_at      DATETIME NOT NULL,
              CONSTRAINT fk_wallet_user   FOREIGN KEY (user_id)
                         REFERENCES zzimba_users(id)
                         ON UPDATE CASCADE ON DELETE RESTRICT,
              CONSTRAINT fk_wallet_vendor FOREIGN KEY (vendor_id)
                         REFERENCES vendor_stores(id)
                         ON UPDATE CASCADE ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        try {
            self::$pdo->exec($createFinancial);
            self::$pdo->exec($createEntries);
            self::$pdo->exec($createWallets);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] table-creation error: ' . $e->getMessage());
        }

        date_default_timezone_set('Africa/Kampala');
        self::$ready = true;
    }

    /* ------------------------------------------------------------------ */
    /*  Public API                                                         */
    /* ------------------------------------------------------------------ */

    public static function validateMsisdn(string $msisdn): array
    {
        self::boot();
        $resp = self::apiRequest(
            'https://payments.relworx.com/api/mobile-money/validate',
            ['msisdn' => $msisdn]
        );
        return json_decode($resp, true);
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

        return $res;
    }

    public static function checkRequestStatus(string $internalRef): array
    {
        self::boot();

        // 1. Fetch status from Relworx
        $raw = self::apiRequest(
            'https://payments.relworx.com/api/mobile-money/check-request-status',
            [
                'internal_reference' => $internalRef,
                'account_no' => self::ACCOUNT_NO
            ],
            'GET'
        );
        $res = json_decode($raw, true) ?? [];
        $status = strtoupper($res['request_status'] ?? $res['status'] ?? 'PENDING');

        // 2. Update transaction record
        try {
            $updateTxn = self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status            = :st,
                       note              = :nt,
                       external_metadata = :md,
                       updated_at        = NOW()
                 WHERE external_reference = :er
            ");
            $updateTxn->execute([
                ':st' => $status,
                ':nt' => $res['message'] ?? null,
                ':md' => $raw,
                ':er' => $internalRef
            ]);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] txn update error: ' . $e->getMessage());
        }

        // 3. On SUCCESS, post ledger entries
        if ($status === 'SUCCESS') {
            try {
                self::$pdo->beginTransaction();

                // Fetch transaction context
                $stmt = self::$pdo->prepare("
                    SELECT transaction_id, amount_total, user_id
                      FROM zzimba_financial_transactions
                     WHERE external_reference = :er
                ");
                $stmt->execute([':er' => $internalRef]);
                $txn = $stmt->fetch(PDO::FETCH_ASSOC);
                $amount = (float) $txn['amount_total'];
                $txnId = $txn['transaction_id'];
                $userId = $txn['user_id'];

                // PLATFORM withholding wallet
                $withStmt = self::$pdo->query("
                    SELECT platform_account_id
                      FROM zzimba_platform_account_settings
                     WHERE type = 'withholding'
                     LIMIT 1
                ");
                $withholdingId = $withStmt->fetchColumn();
                $stmt = self::$pdo->prepare("
                    SELECT current_balance
                      FROM zzimba_wallets
                     WHERE wallet_id = :wid
                       AND owner_type = 'PLATFORM'
                       AND status     = 'active'
                     LIMIT 1
                ");
                $stmt->execute([':wid' => $withholdingId]);
                $withholdingBal = (float) $stmt->fetchColumn();

                // GATEWAY cash account
                $stmt = self::$pdo->query("
                    SELECT id, current_balance
                      FROM zzimba_cash_accounts
                     WHERE type   = 'gateway'
                       AND status = 'active'
                     LIMIT 1
                ");
                $cash = $stmt->fetch(PDO::FETCH_ASSOC);
                $cashId = $cash['id'];
                $cashBal = (float) $cash['current_balance'];

                // USER wallet
                $stmt = self::$pdo->prepare("
                    SELECT wallet_id, current_balance
                      FROM zzimba_wallets
                     WHERE owner_type = 'USER'
                       AND user_id    = :uid
                       AND status     = 'active'
                     LIMIT 1
                ");
                $stmt->execute([':uid' => $userId]);
                $userWallet = $stmt->fetch(PDO::FETCH_ASSOC);
                $userWid = $userWallet['wallet_id'];
                $userBal = (float) $userWallet['current_balance'];

                // ENTRY 1: CREDIT withholding wallet
                $newWithBal = $withholdingBal + $amount;
                $e1 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newWithBal,
                    'entry_note' => 'Mobile Money received via Gateway – Held in Withholding'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newWithBal, ':wid' => $withholdingId]);

                // ENTRY 2: DEBIT withholding → gateway cash
                $afterDebit = $newWithBal - $amount;
                $e2 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $afterDebit,
                    'entry_note' => 'Transferred to Gateway Cash Account from Withholding'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $afterDebit, ':wid' => $withholdingId]);

                // ENTRY 3: DEBIT withholding → user wallet
                $e3 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $afterDebit,
                    'entry_note' => 'Allocated to User Wallet from Withholding'
                ]);

                // ENTRY 4: CREDIT user wallet (refers to DEBIT entry 3)
                $newUserBal = $userBal + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $userWid,
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newUserBal,
                    'entry_note' => 'Top-up from Mobile Money via Withholding',
                    'ref_entry_id' => $e3
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newUserBal, ':wid' => $userWid]);

                // ENTRY 5: CREDIT gateway cash account (refers to DEBIT entry 2)
                $newCashBal = $cashBal + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'cash_account_id' => $cashId,
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newCashBal,
                    'entry_note' => 'Cash received from Mobile Money – Gateway Deposit',
                    'ref_entry_id' => $e2
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_cash_accounts
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE id = :cid
                ")->execute([':bal' => $newCashBal, ':cid' => $cashId]);

                self::$pdo->commit();
            } catch (PDOException $e) {
                self::$pdo->rollBack();
                error_log('[ZzimbaCreditModule] ledger processing error: ' . $e->getMessage());
            }
        }

        return $res;
    }

    /**
     * Fetch an existing active wallet or create one.
     *
     *   USER → binds user_id
     *   VENDOR → binds vendor_id
     *   PLATFORM → single platform wallet
     */
    public static function getWallet(string $ownerType, string $ownerId = null): array
    {
        self::boot();

        /* ---------- try to fetch ---------- */
        if ($ownerType === 'PLATFORM') {
            $stmt = self::$pdo->prepare("
                SELECT wallet_id, wallet_name, current_balance, status, created_at
                  FROM zzimba_wallets
                 WHERE owner_type = 'PLATFORM'
                   AND status     = 'active'
                 LIMIT 1");
            $stmt->execute();
        } else {
            $col = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
            $stmt = self::$pdo->prepare("
                SELECT wallet_id, wallet_name, current_balance, status, created_at
                  FROM zzimba_wallets
                 WHERE owner_type = :ot
                   AND {$col}     = :oid
                   AND status     = 'active'
                 LIMIT 1");
            $stmt->execute([':ot' => $ownerType, ':oid' => $ownerId]);
        }
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($wallet) {
            return ['success' => true, 'wallet' => $wallet];
        }

        /* ---------- build new wallet row ---------- */
        $wid = \generateUlid();
        $created = date('Y-m-d H:i:s');
        $walletName = 'My Wallet';

        if ($ownerType === 'USER') {
            $u = self::$pdo->prepare("SELECT first_name, last_name FROM zzimba_users WHERE id = :oid");
            $u->execute([':oid' => $ownerId]);
            if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
                $walletName = trim($row['first_name'] . ' ' . $row['last_name']);
            }
        }

        $fields = ['wallet_id', 'owner_type', 'wallet_name', 'current_balance', 'status', 'created_at', 'updated_at'];
        $placeholders = [':wid', ':ot', ':wname', ':bal', ':st', ':created', ':updated'];
        $bind = [
            ':wid' => $wid,
            ':ot' => $ownerType,
            ':wname' => $walletName,
            ':bal' => 0.00,
            ':st' => 'active',
            ':created' => $created,
            ':updated' => $created
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
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($bind);
            return [
                'success' => true,
                'wallet' => [
                    'wallet_id' => $wid,
                    'wallet_name' => $walletName,
                    'current_balance' => 0.00,
                    'status' => 'active',
                    'created_at' => $created
                ]
            ];
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] create wallet error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not create wallet'];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Internal helpers                                                   */
    /* ------------------------------------------------------------------ */
    private static function apiRequest(string $url, array $data, string $method = 'POST'): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            if ($data) {
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
        $sql = "INSERT INTO zzimba_financial_transactions ($cols, created_at, updated_at)
                   VALUES ($params, NOW(), NOW())";
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($row);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] insert txn error: ' . $e->getMessage());
        }
    }

    /**
     * Insert a ledger entry and return its entry_id.
     */
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
}
