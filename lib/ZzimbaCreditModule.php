<?php

namespace ZzimbaCreditModule;

use PDO;
use PDOException;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../sms/SMS.php';

final class CreditService
{
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

        global $pdo;
        self::$pdo = $pdo;

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
                transaction_type ENUM('TOPUP','PURCHASE','SUBSCRIPTION','SMS_PURCHASE','EMAIL_PURCHASE','PREMIUM_FEATURE','REFUND','WITHDRAWAL','TRANSFER') NOT NULL,
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

    public static function getWallet(string $ownerType, string $ownerId = null): array
    {
        self::boot();

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

        if ($ownerType === 'VENDOR') {
            $v = self::$pdo
                ->prepare("SELECT name FROM vendor_stores WHERE id = :vid");
            $v->execute([':vid' => $ownerId]);
            if ($r = $v->fetch(PDO::FETCH_ASSOC)) {
                $wname = trim($r['name']);
            }
        }

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
            ':updated'
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


    public static function getWalletStatement(
        string $walletId,
        string $filter = 'all',
        ?string $start = null,
        ?string $end = null
    ): array {
        self::boot();

        // 1) Fetch wallet metadata
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

        // 2) Collect all transaction IDs touching this wallet
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

        // 3) Fetch those transactions
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
        $sql .= " ORDER BY created_at DESC";

        $txnStmt = self::$pdo->prepare($sql);
        $txnStmt->execute($params);
        $txns = $txnStmt->fetchAll(PDO::FETCH_ASSOC);

        // 4) Attach entries recursively _inside_ each transaction
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

        // 1) Gather ancestor chain
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

        // 2) Recursively gather descendants
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

        // 3) Merge ancestors + children under related_entries
        $entry['related_entries'] = array_merge($ancestors, $children);

        return $entry;
    }

    public static function transfer(array $opts): array
    {
        $walletTo = trim($opts['wallet_to'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);

        if ($walletTo === '' || $amount < 500) {
            return ['success' => false, 'message' => 'Destination Account No. and amount greater or equal to 500 required'];
        }

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId) {
            return ['success' => false, 'message' => 'Not authenticated'];
        }

        self::boot();

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
        if ($fromBalance < $amount) {
            return ['success' => false, 'message' => 'Insufficient funds'];
        }

        $txnId = \generateUlid();
        self::insertTransaction([
            'transaction_id' => $txnId,
            'transaction_type' => 'TRANSFER',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'WALLET',
            'external_reference' => null,
            'external_metadata' => null,
            'user_id' => $userId,
            'vendor_id' => $toOwnerType === 'VENDOR' ? $destRow['vendor_id'] : null
        ]);

        $transferId = \generateUlid();
        self::insertTransfer([
            'id' => $transferId,
            'wallet_from' => $fromWalletId,
            'wallet_to' => $toWalletId,
            'transaction_id' => $txnId,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        try {
            self::$pdo->beginTransaction();

            self::performTransferEntries($txnId, $fromWalletId, $toWalletId, $amount);

            self::$pdo->prepare("
            UPDATE zzimba_financial_transactions
               SET status     = 'SUCCESS',
                   updated_at = NOW()
             WHERE transaction_id = :tid
        ")->execute([':tid' => $txnId]);

            self::$pdo->commit();
        } catch (PDOException $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] transfer error: ' . $e->getMessage());
            self::$pdo->prepare("
            UPDATE zzimba_financial_transactions
               SET status     = 'FAILED',
                   updated_at = NOW()
             WHERE transaction_id = :tid
        ")->execute([':tid' => $txnId]);
            return ['success' => false, 'message' => 'Transfer failed'];
        }

        $balanceStmt = self::$pdo->prepare("
        SELECT current_balance
          FROM zzimba_wallets
         WHERE wallet_id = :wid
         LIMIT 1
    ");
        $balanceStmt->execute([':wid' => $fromWalletId]);
        $balanceRow = $balanceStmt->fetch(PDO::FETCH_ASSOC);
        $newBalance = isset($balanceRow['current_balance'])
            ? (float) $balanceRow['current_balance']
            : null;

        return [
            'success' => true,
            'transaction_id' => $txnId,
            'balance' => $newBalance
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
        float $amount
    ): void {

        $withholdingId = self::getWithholdingAccountId();

        $balStmt = self::$pdo->prepare("SELECT current_balance FROM zzimba_wallets WHERE wallet_id = :wid");
        $balStmt->execute([':wid' => $fromWalletId]);
        $fromBal = (float) $balStmt->fetchColumn();

        $balStmt->execute([':wid' => $withholdingId]);
        $withBal = (float) $balStmt->fetchColumn();

        $balStmt->execute([':wid' => $toWalletId]);
        $toBal = (float) $balStmt->fetchColumn();

        /* fetch wallet numbers for notes */
        $wnStmt = self::$pdo->prepare("SELECT wallet_number FROM zzimba_wallets WHERE wallet_id = :wid");
        $wnStmt->execute([':wid' => $fromWalletId]);
        $fromNo = $wnStmt->fetchColumn();
        $wnStmt->execute([':wid' => $toWalletId]);
        $toNo = $wnStmt->fetchColumn();

        /* 1) Debit sender wallet */
        $debitSenderId = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $fromWalletId,
            'entry_type' => 'DEBIT',
            'amount' => $amount,
            'balance_after' => $fromBal - $amount,
            'entry_note' => 'Zzimba Credit transfer to ' . $toNo
        ]);
        self::updateWalletBalance($fromWalletId, $fromBal - $amount);

        /* 2) Credit withholding referencing sender debit */
        $creditWithId = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $withholdingId,
            'entry_type' => 'CREDIT',
            'amount' => $amount,
            'balance_after' => $withBal + $amount,
            'entry_note' => 'Credit transfer instruction from ' . $fromNo,
            'ref_entry_id' => $debitSenderId
        ]);
        self::updateWalletBalance($withholdingId, $withBal + $amount);

        /* 3) Debit withholding referencing previous credit */
        $debitWithId = self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $withholdingId,
            'entry_type' => 'DEBIT',
            'amount' => $amount,
            'balance_after' => ($withBal + $amount) - $amount,
            'entry_note' => 'Credit transfer executed for ' . $toNo,
            'ref_entry_id' => $creditWithId
        ]);
        self::updateWalletBalance($withholdingId, ($withBal + $amount) - $amount);

        /* 4) Credit receiver wallet referencing withholding debit */
        self::insertEntry([
            'transaction_id' => $txnId,
            'wallet_id' => $toWalletId,
            'entry_type' => 'CREDIT',
            'amount' => $amount,
            'balance_after' => $toBal + $amount,
            'entry_note' => 'Zzimba Credit transfer from ' . $fromNo,
            'ref_entry_id' => $debitWithId
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

        // ────────────────────────────────────────────────────── extract values
        $walletId = trim($opts['wallet_id'] ?? '');
        $cashAccountId = trim($opts['cash_account_id'] ?? '');
        $amount = (float) ($opts['amount_total'] ?? 0);
        $paymentMethod = strtoupper(trim($opts['payment_method'] ?? ''));
        $externalRef = trim($opts['external_reference'] ?? '');
        $note = trim($opts['note'] ?? '');

        // legacy (optional) IDs
        $userId = $opts['user_id'] ?? null;
        $vendorId = $opts['vendor_id'] ?? null;

        // method-specific extras
        $mmPhoneNumber = trim($opts['mmPhoneNumber'] ?? '');
        $mmDateTime = trim($opts['mmDateTime'] ?? '');
        $btDepositorName = trim($opts['btDepositorName'] ?? '');
        $btDateTime = trim($opts['btDateTime'] ?? '');

        // ────────────────────────────────────────────────────── validation
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

        // ────────────────────────────────────────────────────── fetch wallet & owner_type
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
        $ownerType = $walletRow['owner_type'];  // 'USER' | 'VENDOR' | 'PLATFORM'

        // ────────────────────────────────────────────────────── prepare insert payload
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

        // ◀︎ ONLY add the matching FK: user_id for USER-wallets, vendor_id for VENDOR-wallets
        if ($ownerType === 'USER' && !empty($userId)) {
            $insertPayload['user_id'] = $userId;
        } elseif ($ownerType === 'VENDOR' && !empty($vendorId)) {
            $insertPayload['vendor_id'] = $vendorId;
        }
        // for PLATFORM wallets we add neither user_id nor vendor_id

        // ────────────────────────────────────────────────────── do the insert
        try {
            self::insertTransaction($insertPayload);
        } catch (\Throwable $e) {
            error_log('[ZzimbaCreditModule] Txn insert error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Could not save transaction'];
        }

        // ────────────────────────────────────────────────────── SMS alert
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

        if ($newStatus === 'FAILED') {
            return ['success' => true, 'message' => 'Top-up marked as failed'];
        }

        // Fetch transaction
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

        $meta = json_decode($txn['external_metadata'], true);
        $cashAccountId = $meta['cash_account_id'] ?? null;
        $amount = (float) $txn['amount_total'];

        // Determine receiver wallet
        $receiverWalletId = $txn['wallet_id'] ?? null;
        if (!$receiverWalletId) {
            // fallback to legacy lookup
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

            // WITHHOLDING WALLET
            $withholdingId = self::getWithholdingAccountId();
            $balStmt = self::$pdo->prepare("
            SELECT current_balance
              FROM zzimba_wallets
             WHERE wallet_id = :wid
        ");
            $balStmt->execute([':wid' => $withholdingId]);
            $withBal = (float) $balStmt->fetchColumn();

            // CREDIT withholding
            $creditWithId = self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $withBal + $amount,
                'entry_note' => 'Zzimba Credit top-up'
            ]);
            self::updateWalletBalance($withholdingId, $withBal + $amount);

            // DEBIT withholding → Disburse to receiver
            $debitWithId = self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $withholdingId,
                'entry_type' => 'DEBIT',
                'amount' => $amount,
                'balance_after' => $withBal,
                'entry_note' => 'Disbursed to ' . $receiverWalletNo,
                'ref_entry_id' => $creditWithId
            ]);
            self::updateWalletBalance($withholdingId, $withBal);

            // CREDIT receiver wallet
            $newWBal = $currBal + $amount;
            self::insertEntry([
                'transaction_id' => $transactionId,
                'wallet_id' => $receiverWalletId,
                'entry_type' => 'CREDIT',
                'amount' => $amount,
                'balance_after' => $newWBal,
                'entry_note' => 'Zzimba Credit top-up',
                'ref_entry_id' => $debitWithId
            ]);
            self::$pdo->prepare("
            UPDATE zzimba_wallets
               SET current_balance = :bal, updated_at = NOW()
             WHERE wallet_id = :wid
        ")->execute([':bal' => $newWBal, ':wid' => $receiverWalletId]);

            // CASH ACCOUNT CREDIT
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
                'ref_entry_id' => $debitWithId
            ]);
            self::$pdo->prepare("
            UPDATE zzimba_cash_accounts
               SET current_balance = :bal, updated_at = NOW()
             WHERE id = :cid
        ")->execute([':bal' => $newCash, ':cid' => $cashAccountId]);

            self::$pdo->commit();
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            error_log('[ZzimbaCreditModule] top-up ledger error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ledger processing failed'];
        }

        return ['success' => true];
    }
}
