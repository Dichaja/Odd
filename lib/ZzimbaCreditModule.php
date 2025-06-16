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
 *   • getWalletStatement(string $walletId, string $filter = 'all', ?string $start = null, ?string $end = null): array
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
    /* ------------------------------------------------------------------
     *  Static configuration / bootstrap
     * ------------------------------------------------------------------ */
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
        $createWallets = "
            CREATE TABLE IF NOT EXISTS zzimba_wallets (
                wallet_id       CHAR(26) NOT NULL PRIMARY KEY,
                wallet_number   CHAR(10) NOT NULL UNIQUE COMMENT 'Public 10-digit wallet number: Y₁S₀Y₂S₁S₂S₃S₄M₁S₅M₂',
                owner_type      ENUM('USER','VENDOR','PLATFORM') NOT NULL,
                user_id         VARCHAR(26) DEFAULT NULL,
                vendor_id       VARCHAR(26) DEFAULT NULL,
                wallet_name     VARCHAR(100) NOT NULL,
                current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
                status          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
                created_at      DATETIME NOT NULL,
                updated_at      DATETIME NOT NULL,
                CONSTRAINT fk_wallet_user FOREIGN KEY (user_id)
                    REFERENCES zzimba_users(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_wallet_vendor FOREIGN KEY (vendor_id)
                    REFERENCES vendor_stores(id)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

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
                CONSTRAINT fk_txn_user FOREIGN KEY (user_id)
                    REFERENCES zzimba_users(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_txn_vendor FOREIGN KEY (vendor_id)
                    REFERENCES vendor_stores(id)
                    ON UPDATE CASCADE ON DELETE SET NULL,
                CONSTRAINT fk_txn_original FOREIGN KEY (original_txn_id)
                    REFERENCES zzimba_financial_transactions(transaction_id)
                    ON UPDATE CASCADE ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

        $createEntries = "
            CREATE TABLE IF NOT EXISTS zzimba_transaction_entries (
                entry_id           CHAR(26) NOT NULL PRIMARY KEY,
                transaction_id     VARCHAR(26) NOT NULL,
                wallet_id          CHAR(26) DEFAULT NULL,
                cash_account_id    CHAR(26) DEFAULT NULL,
                ref_entry_id       CHAR(26) DEFAULT NULL,
                entry_type         ENUM('DEBIT','CREDIT') NOT NULL,
                amount             DECIMAL(18,2) NOT NULL,
                balance_after      DECIMAL(18,2) NOT NULL,
                entry_note         VARCHAR(255) DEFAULT NULL,
                created_at         DATETIME NOT NULL,
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

    /* ------------------------------------------------------------------
     *  Wallet-number generator (10-digit Y₁S₀Y₂S₁S₂S₃S₄M₁S₅M₂)
     * ------------------------------------------------------------------ */
    private static function generateWalletNumber(string $walletId): string
    {
        // Year “yy” (e.g. “26”), Month “mm” (e.g. “11”)
        $yy = date('y');       // "26"
        $y1 = $yy[0];          // "2"
        $y2 = $yy[1];          // "6"
        $mm = date('m');       // "11"
        $m1 = $mm[0];          // "1"
        $m2 = $mm[1];          // "1"

        do {
            // 6-digit random sequence
            $seq = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Assemble: Y₁ S₀ Y₂ S₁ S₂ S₃ S₄ M₁ S₅ M₂
            $walletNumber =
                $y1         // pos 1
                . $seq[0]      // pos 2
                . $y2         // pos 3
                . $seq[1]      // pos 4
                . $seq[2]      // pos 5
                . $seq[3]      // pos 6
                . $seq[4]      // pos 7
                . $m1         // pos 8
                . $seq[5]      // pos 9
                . $m2;         // pos 10

            // Ensure uniqueness against UNIQUE index
            $check = self::$pdo
                ->prepare('SELECT 1 FROM zzimba_wallets WHERE wallet_number = ? LIMIT 1');
            $check->execute([$walletNumber]);
            $exists = (bool) $check->fetchColumn();
        } while ($exists);

        return $walletNumber;
    }

    /* ------------------------------------------------------------------
     *  Public API
     * ------------------------------------------------------------------ */

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

        // 1) Fetch status from Relworx
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

        // 2) Update financial_transactions
        $upd = self::$pdo->prepare("
            UPDATE zzimba_financial_transactions
               SET status = :st,
                   note   = :nt,
                   external_metadata = :md,
                   updated_at = NOW()
             WHERE external_reference = :er
        ");
        $upd->execute([
            ':st' => $status,
            ':nt' => $res['message'] ?? null,
            ':md' => $raw,
            ':er' => $internalRef
        ]);

        // 3) On SUCCESS, one DEBIT then two CREDITs referencing it
        if ($status === 'SUCCESS') {
            $txnRow = self::$pdo->prepare("
                SELECT transaction_id, amount_total, user_id
                  FROM zzimba_financial_transactions
                 WHERE external_reference = :er
            ");
            $txnRow->execute([':er' => $internalRef]);
            $txn = $txnRow->fetch(PDO::FETCH_ASSOC);
            $txnId = $txn['transaction_id'];
            $amount = (float) $txn['amount_total'];
            $userId = $txn['user_id'];

            // get withholding wallet
            $wst = self::$pdo->query("
                SELECT platform_account_id
                  FROM zzimba_platform_account_settings
                 WHERE type = 'withholding'
                 LIMIT 1
            ");
            $withholdingId = $wst->fetchColumn();

            // idempotency: skip if already credited withholding
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

                // fetch balances
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
                    SELECT wallet_id, current_balance
                      FROM zzimba_wallets
                     WHERE owner_type = 'USER'
                       AND user_id    = :uid
                       AND status     = 'active'
                ");
                $uStmt->execute([':uid' => $userId]);
                $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);

                // 3.1) CREDIT withholding
                $newWithBal = $withBal + $amount;
                self::insertEntry([
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

                // 3.2) SINGLE DEBIT withholding
                $debitBal = $newWithBal - $amount;
                $debitId = self::insertEntry([
                    'transaction_id' => $txnId,
                    'wallet_id' => $withholdingId,
                    'entry_type' => 'DEBIT',
                    'amount' => $amount,
                    'balance_after' => $debitBal,
                    'entry_note' => 'Disbursed from Withholding'
                ]);
                self::$pdo->prepare("
                    UPDATE zzimba_wallets
                       SET current_balance = :bal, updated_at = NOW()
                     WHERE wallet_id = :wid
                ")->execute([':bal' => $debitBal, ':wid' => $withholdingId]);

                // 3.3) CREDIT user wallet (ref to debit)
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

                // 3.4) CREDIT gateway cash (ref to debit)
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

    public static function getWallet(string $ownerType, string $ownerId = null): array
    {
        self::boot();

        // 1) Try fetching existing
        if ($ownerType === 'PLATFORM') {
            $sql = "
                SELECT wallet_id,
                       wallet_number,
                       wallet_name,
                       current_balance,
                       status,
                       created_at
                  FROM zzimba_wallets
                 WHERE owner_type = 'PLATFORM'
                   AND status     = 'active'
                 LIMIT 1
            ";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute();
        } else {
            $col = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
            $sql = "
                SELECT wallet_id,
                       wallet_number,
                       wallet_name,
                       current_balance,
                       status,
                       created_at
                  FROM zzimba_wallets
                 WHERE owner_type = :ot
                   AND {$col}    = :oid
                   AND status    = 'active'
                 LIMIT 1
            ";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute([':ot' => $ownerType, ':oid' => $ownerId]);
        }

        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($wallet) {
            return ['success' => true, 'wallet' => $wallet];
        }

        // 2) Create a new wallet (with wallet_number)
        $wid = \generateUlid();
        $wn = self::generateWalletNumber($wid);
        $now = date('Y-m-d H:i:s');
        $wname = 'My Wallet';

        if ($ownerType === 'USER') {
            $u = self::$pdo
                ->prepare("SELECT first_name, last_name FROM zzimba_users WHERE id = :uid");
            $u->execute([':uid' => $ownerId]);
            if ($r = $u->fetch(PDO::FETCH_ASSOC)) {
                $wname = trim($r['first_name'] . ' ' . $r['last_name']);
            }
        }

        // Build INSERT
        $fields = [
            'wallet_id',
            'wallet_number',
            'owner_type',
            'wallet_name',
            'current_balance',
            'status',
            'created_at',
            'updated_at'
        ];
        $placeholders = [
            ':wid',
            ':wn',
            ':ot',
            ':wname',
            ':bal',
            ':st',
            ':created',
            ' :updated'
        ];
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
            return [
                'success' => true,
                'wallet' => [
                    'wallet_id' => $wid,
                    'wallet_number' => $wn,
                    'wallet_name' => $wname,
                    'current_balance' => 0.00,
                    'status' => 'active',
                    'created_at' => $now
                ]
            ];
        } catch (PDOException $e) {
            error_log('[ZzimbaCreditModule] create wallet error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not create wallet'];
        }
    }

    /**
     * Retrieve a wallet's full statement by wallet ID.
     *
     * Includes ALL transactions (PENDING, FAILED, SUCCESS) from
     * zzimba_financial_transactions, and any related entries
     * from zzimba_transaction_entries, grouping child credits
     * by ref_entry_id under their debit.
     *
     * @param string      $walletId The ID of the wallet (USER, VENDOR, or PLATFORM)
     * @param string      $filter   'all' or 'range'
     * @param string|null $start    'YYYY-MM-DD' or full datetime
     * @param string|null $end      'YYYY-MM-DD' or full datetime
     * @return array
     */
    public static function getWalletStatement(string $walletId, string $filter = 'all', ?string $start = null, ?string $end = null): array
    {
        self::boot();

        // 1) Fetch wallet metadata (including wallet_number)
        $wStmt = self::$pdo->prepare("
            SELECT wallet_id,
                   wallet_number,
                   owner_type,
                   user_id,
                   vendor_id,
                   wallet_name
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

        // 2) build the transaction query + params
        $txnParams = [];
        if ($wallet['owner_type'] === 'USER') {
            $txnSql = "
            SELECT *
            FROM zzimba_financial_transactions
            WHERE user_id = :uid
        ";
            $txnParams[':uid'] = $wallet['user_id'];
        } elseif ($wallet['owner_type'] === 'VENDOR') {
            $txnSql = "
            SELECT *
            FROM zzimba_financial_transactions
            WHERE vendor_id = :vid
        ";
            $txnParams[':vid'] = $wallet['vendor_id'];
        } else { // PLATFORM: pull any txn that has an entry on this wallet
            $txnSql = "
            SELECT DISTINCT ft.*
            FROM zzimba_financial_transactions ft
            JOIN zzimba_transaction_entries e
              ON ft.transaction_id = e.transaction_id
            WHERE e.wallet_id = :wid
        ";
            $txnParams[':wid'] = $walletId;
        }

        // 2a) date‐range filter if requested
        if ($filter === 'range') {
            if (!$start || !$end) {
                return [
                    'success' => false,
                    'statement' => [],
                    'message' => 'When using range filter, both start and end are required'
                ];
            }
            $txnSql .= " AND created_at BETWEEN :startDate AND :endDate";
            $txnParams[':startDate'] = $start;
            $txnParams[':endDate'] = $end;
        }

        $txnSql .= " ORDER BY created_at DESC";

        $txnStmt = self::$pdo->prepare($txnSql);
        $txnStmt->execute($txnParams);
        $txns = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3) prepare statements for entries and related entries
        $entryStmt = self::$pdo->prepare("
        SELECT *
        FROM zzimba_transaction_entries
        WHERE transaction_id = :tid
          AND wallet_id = :wid
        ORDER BY created_at ASC
    ");
        $relatedStmt = self::$pdo->prepare("
        SELECT
            e.*,
            COALESCE(ca.name, w.wallet_name) AS account_or_wallet_name,
            w.owner_type
        FROM zzimba_transaction_entries e
        LEFT JOIN zzimba_cash_accounts ca
          ON e.cash_account_id = ca.id
        LEFT JOIN zzimba_wallets w
          ON e.wallet_id = w.wallet_id
        WHERE e.ref_entry_id = :eid
        ORDER BY e.created_at ASC
    ");

        // 4) build the statement array
        $statement = [];
        foreach ($txns as $txn) {
            // fetch all primary entries for this wallet
            $entryStmt->execute([
                ':tid' => $txn['transaction_id'],
                ':wid' => $walletId
            ]);
            $entries = $entryStmt->fetchAll(PDO::FETCH_ASSOC);

            // for each entry, fetch its related entries
            foreach ($entries as &$entry) {
                $relatedStmt->execute([':eid' => $entry['entry_id']]);
                $entry['related_entries'] = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // inject entries directly into the transaction object
            $txn['entries'] = $entries;

            $statement[] = [
                'transaction' => $txn
            ];
        }

        return [
            'success' => true,
            'wallet' => [
                'wallet_id' => $wallet['wallet_id'],
                'wallet_number' => $wallet['wallet_number'],
                'owner_type' => $wallet['owner_type'],
                'wallet_name' => $wallet['wallet_name'],
            ],
            'statement' => $statement,
        ];
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
