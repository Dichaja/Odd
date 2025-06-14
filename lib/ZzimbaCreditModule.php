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
 *   • getWalletStatement(string $userId, string $filter = 'all', ?string $start = null, ?string $end = null): array
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

        // create tables if not exist
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
            $tx = self::$pdo->prepare("
                UPDATE zzimba_financial_transactions
                   SET status            = :st,
                       note              = :nt,
                       external_metadata = :md,
                       updated_at        = NOW()
                 WHERE external_reference = :er
            ");
            $tx->execute([
                ':st' => $status,
                ':nt' => $res['message'] ?? null,
                ':md' => $raw,
                ':er' => $internalRef
            ]);
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] txn update error: ' . $e->getMessage());
        }

        // 3. On SUCCESS, idempotent ledger processing
        if ($status === 'SUCCESS') {
            // fetch transaction
            $stmt = self::$pdo->prepare("
                SELECT transaction_id, amount_total, user_id
                  FROM zzimba_financial_transactions
                 WHERE external_reference = :er
            ");
            $stmt->execute([':er' => $internalRef]);
            $txn = $stmt->fetch(PDO::FETCH_ASSOC);
            $txnId = $txn['transaction_id'];
            $amount = (float) $txn['amount_total'];
            $userId = $txn['user_id'];

            // withholding wallet id
            $wst = self::$pdo->query("
                SELECT platform_account_id
                  FROM zzimba_platform_account_settings
                 WHERE type = 'withholding'
                 LIMIT 1
            ");
            $withholdingId = $wst->fetchColumn();

            // idempotency: credited withholding?
            $chk = self::$pdo->prepare("
                SELECT COUNT(*) FROM zzimba_transaction_entries
                 WHERE transaction_id = :tid
                   AND wallet_id      = :wid
                   AND entry_type     = 'CREDIT'
                   AND entry_note     = 'Mobile Money received via Gateway – Held in Withholding'
            ");
            $chk->execute([':tid' => $txnId, ':wid' => $withholdingId]);
            if ((int) $chk->fetchColumn() > 0) {
                return $res;
            }

            try {
                self::$pdo->beginTransaction();

                // balances
                $stmt = self::$pdo->prepare("
                    SELECT current_balance
                      FROM zzimba_wallets
                     WHERE wallet_id = :wid
                       AND owner_type = 'PLATFORM'
                       AND status     = 'active'
                ");
                $stmt->execute([':wid' => $withholdingId]);
                $withBal = (float) $stmt->fetchColumn();

                $cash = self::$pdo->query("
                    SELECT id, current_balance
                      FROM zzimba_cash_accounts
                     WHERE type   = 'gateway'
                       AND status = 'active'
                     LIMIT 1
                ")->fetch(PDO::FETCH_ASSOC);

                $stmt = self::$pdo->prepare("
                    SELECT wallet_id, current_balance
                      FROM zzimba_wallets
                     WHERE owner_type = 'USER'
                       AND user_id    = :uid
                       AND status     = 'active'
                ");
                $stmt->execute([':uid' => $userId]);
                $uw = $stmt->fetch(PDO::FETCH_ASSOC);

                // 1) CREDIT withholding
                $newW = $withBal + $amount;
                $e1 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newW,
                    'entry_note' => 'Mobile Money received via Gateway – Held in Withholding'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newW, ':wid' => $withholdingId]);

                // 2) DEBIT withholding → gateway cash
                $after = $newW - $amount;
                $e2 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $after,
                    'entry_note' => 'Transferred to Gateway Cash Account from Withholding'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $after, ':wid' => $withholdingId]);

                // 3) DEBIT withholding → user wallet
                $e3 = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $after,
                    'entry_note' => 'Allocated to User Wallet from Withholding'
                ]);

                // 4) CREDIT user wallet
                $newU = (float) $uw['current_balance'] + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $uw['wallet_id'],
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newU,
                    'entry_note' => 'Top-up from Mobile Money via Withholding',
                    'ref_entry_id' => $e3
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $newU, ':wid' => $uw['wallet_id']]);

                // 5) CREDIT cash account
                $newC = (float) $cash['current_balance'] + $amount;
                self::insertEntry([
                    'transaction_id' => $txnId,
                    'cash_account_id' => $cash['id'],
                    'entry_type' => 'CREDIT',
                    'amount' => $amount,
                    'balance_after' => $newC,
                    'entry_note' => 'Cash received from Mobile Money – Gateway Deposit',
                    'ref_entry_id' => $e2
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_cash_accounts
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE id = :cid
                ")->execute([':bal' => $newC, ':cid' => $cash['id']]);

                self::$pdo->commit();
            } catch (PDOException $e) {
                self::$pdo->rollBack();
                error_log('[ZzimbaCreditModule] ledger processing error: ' . $e->getMessage());
            }
        }

        return $res;
    }

    public static function getWallet(string $ownerType, string $ownerId = null): array
    {
        self::boot();
        if ($ownerType === 'PLATFORM') {
            $stmt = self::$pdo->prepare("
                SELECT wallet_id, wallet_name, current_balance, status, created_at
                  FROM zzimba_wallets
                 WHERE owner_type = 'PLATFORM'
                   AND status     = 'active'
                 LIMIT 1"
            );
            $stmt->execute();
        } else {
            $col = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
            $stmt = self::$pdo->prepare(
                "
                SELECT wallet_id, wallet_name, current_balance, status, created_at
                  FROM zzimba_wallets
                 WHERE owner_type = :ot
                   AND {$col}     = :oid
                   AND status     = 'active'
                 LIMIT 1"
            );
            $stmt->execute([':ot' => $ownerType, ':oid' => $ownerId]);
        }
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($wallet) {
            return ['success' => true, 'wallet' => $wallet];
        }
        // create if missing...
        $wid = \generateUlid();
        $created = date('Y-m-d H:i:s');
        $wname = 'My Wallet';
        if ($ownerType === 'USER') {
            $u = self::$pdo->prepare("SELECT first_name, last_name FROM zzimba_users WHERE id = :oid");
            $u->execute([':oid' => $ownerId]);
            if ($r = $u->fetch(PDO::FETCH_ASSOC)) {
                $wname = trim($r['first_name'] . ' ' . $r['last_name']);
            }
        }
        $fields = ['wallet_id', 'owner_type', 'wallet_name', 'current_balance', 'status', 'created_at', 'updated_at'];
        $placeholders = [':wid', ':ot', ':wname', ':bal', ':st', ':created', ':updated'];
        $bind = [
            ':wid' => $wid,
            ':ot' => $ownerType,
            ':wname' => $wname,
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
                    'wallet_name' => $wname,
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

    /**
     * Retrieve a user's wallet statement.
     *
     * @param string      $userId Database user_id
     * @param string      $filter 'all' or 'range'
     * @param string|null $start  'YYYY-MM-DD' or full datetime
     * @param string|null $end    'YYYY-MM-DD' or full datetime
     * @return array
     */
    public static function getWalletStatement(string $userId, string $filter = 'all', ?string $start = null, ?string $end = null): array
    {
        self::boot();

        // 1) find user wallet
        $stmt = self::$pdo->prepare("
            SELECT wallet_id
              FROM zzimba_wallets
             WHERE owner_type = 'USER'
               AND user_id    = :uid
               AND status     = 'active'
             LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($row['wallet_id'])) {
            return ['success' => false, 'statement' => [], 'message' => 'No active wallet found'];
        }
        $wid = $row['wallet_id'];

        // 2) fetch transactions
        $sql = "
            SELECT
              transaction_id,
              transaction_type,
              payment_method,
              status,
              amount_total,
              note,
              created_at
            FROM zzimba_financial_transactions
           WHERE user_id = :uid
        ";
        $params = [':uid' => $userId];
        if ($filter === 'range' && $start && $end) {
            $sql .= " AND created_at BETWEEN :start AND :end";
            $params[':start'] = $start;
            $params[':end'] = $end;
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        $txns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) build statement
        $statement = [];
        foreach ($txns as $txn) {
            // fetch entries for this wallet
            $eStmt = self::$pdo->prepare("
                SELECT
                  entry_id,
                  cash_account_id,
                  entry_type,
                  amount,
                  balance_after,
                  entry_note,
                  created_at
                FROM zzimba_transaction_entries
               WHERE transaction_id = :tid
                 AND wallet_id      = :wid
               ORDER BY created_at ASC
            ");
            $eStmt->execute([
                ':tid' => $txn['transaction_id'],
                ':wid' => $wid
            ]);
            $entries = $eStmt->fetchAll(PDO::FETCH_ASSOC);

            $statement[] = [
                'transaction_id' => $txn['transaction_id'],
                'type' => $txn['transaction_type'],
                'payment_method' => $txn['payment_method'],
                'status' => $txn['status'],
                'amount_total' => $txn['amount_total'],
                'note' => $txn['note'],
                'created_at' => $txn['created_at'],
                'entries' => $entries
            ];
        }

        return ['success' => true, 'statement' => $statement];
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
        $sql = "
            INSERT INTO zzimba_financial_transactions
              ($cols, created_at, updated_at)
            VALUES
              ($params, NOW(), NOW())
        ";
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
        $sql = "
            INSERT INTO zzimba_transaction_entries
              ($cols)
            VALUES
              ($params)
        ";
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
