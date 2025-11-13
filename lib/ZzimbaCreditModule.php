<?php
namespace ZzimbaCreditModule;

use PDO;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../sms/SMS.php';
require_once __DIR__ . '/NotificationService.php';

final class ModuleConfig
{
    public const API_KEY = '56cded6ede99ac.BYJV1ceTwWbN_NzaqIchUw';
    public const ACCOUNT_NO = 'REL2C6A94761B';
    public const DEFAULT_CURRENCY = 'UGX';
    public const TZ = 'Africa/Kampala';
}

final class Boot
{
    private static bool $ready = false;
    private static PDO $pdo;

    public static function pdo(): PDO
    {
        return self::$pdo;
    }

    public static function init(): void
    {
        if (self::$ready)
            return;
        global $pdo;
        self::$pdo = $pdo;
        date_default_timezone_set(ModuleConfig::TZ);
        self::$ready = true;
    }
}

final class Ids
{
    public static function ulid(): string
    {
        return \generateUlid();
    }
}

final class NotificationBus
{
    private \NotificationService $ns;

    public function __construct(PDO $pdo)
    {
        $this->ns = new \NotificationService($pdo);
    }

    public function recipients(array $adminMessage, ?array $userMessage = null, ?array $vendorMessage = null): array
    {
        $r = [['type' => 'admin', 'id' => 'admin-global', 'message' => $adminMessage['message']]];
        if ($userMessage && !empty($userMessage['id']))
            $r[] = ['type' => 'user', 'id' => $userMessage['id'], 'message' => $userMessage['message']];
        if ($vendorMessage && !empty($vendorMessage['id']))
            $r[] = ['type' => 'store', 'id' => $vendorMessage['id'], 'message' => $vendorMessage['message']];
        return $r;
    }

    public function send(string $title, array $recipients, string $priority = 'normal', ?string $relatedUserId = null, ?string $link = null): void
    {
        $this->ns->create('system', $title, $recipients, $link, $priority, $relatedUserId);
    }
}

final class WalletRepo
{
    public function __construct(private PDO $pdo)
    {
    }

    public function byId(string $walletId): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM zzimba_wallets WHERE wallet_id=:id AND status='active' LIMIT 1");
        $st->execute([':id' => $walletId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function byNumber(string $walletNo): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM zzimba_wallets WHERE wallet_number=:no AND status='active' LIMIT 1");
        $st->execute([':no' => $walletNo]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function userWallet(string $userId): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM zzimba_wallets WHERE owner_type='USER' AND user_id=:id AND status='active' ORDER BY created_at DESC LIMIT 1");
        $st->execute([':id' => $userId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function vendorWallet(string $vendorId): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM zzimba_wallets WHERE owner_type='VENDOR' AND vendor_id=:id AND status='active' ORDER BY created_at DESC LIMIT 1");
        $st->execute([':id' => $vendorId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function withholdingWalletId(): string
    {
        return $this->pdo->query("SELECT platform_account_id FROM zzimba_platform_account_settings WHERE type='withholding' LIMIT 1")->fetchColumn();
    }

    public function operationsWallet(): ?array
    {
        $id = $this->pdo->query("SELECT platform_account_id FROM zzimba_platform_account_settings WHERE type='operations' LIMIT 1")->fetchColumn();
        if (!$id)
            return null;
        return $this->byId($id);
    }

    public function gatewayCashAccount(): ?array
    {
        return $this->pdo->query("SELECT id, current_balance FROM zzimba_cash_accounts WHERE type='gateway' AND status='active' LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function destinationForCreditSetting(string $settingId): ?array
    {
        $st = $this->pdo->prepare("SELECT w.wallet_id,w.wallet_number,w.current_balance FROM zzimba_wallets w JOIN zzimba_wallet_credit_assignments a ON a.wallet_id=w.wallet_id WHERE a.credit_setting_id=:cid AND w.status='active' LIMIT 1");
        $st->execute([':cid' => $settingId]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function balance(string $walletId): float
    {
        $st = $this->pdo->prepare("SELECT current_balance FROM zzimba_wallets WHERE wallet_id=:w");
        $st->execute([':w' => $walletId]);
        return (float) $st->fetchColumn();
    }

    public function updateBalance(string $walletId, float $balance): void
    {
        $st = $this->pdo->prepare("UPDATE zzimba_wallets SET current_balance=:b, updated_at=NOW() WHERE wallet_id=:w");
        $st->execute([':b' => $balance, ':w' => $walletId]);
    }

    public function generateWalletNumber(string $walletId): string
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
            $chk = $this->pdo->prepare('SELECT 1 FROM zzimba_wallets WHERE wallet_number=? LIMIT 1');
            $chk->execute([$walletNumber]);
            $exists = (bool) $chk->fetchColumn();
        } while ($exists);

        return $walletNumber;
    }

    public function create(string $ownerType, ?string $ownerId, string $name): array
    {
        $wid = Ids::ulid();
        $wn = $this->generateWalletNumber($wid);
        $now = date('Y-m-d H:i:s');

        $fields = ['wallet_id', 'wallet_number', 'owner_type', 'wallet_name', 'current_balance', 'status', 'created_at', 'updated_at'];
        $values = [':wid', ':wn', ':ot', ':name', ':bal', ':st', ':c', ':u'];
        $bind = [':wid' => $wid, ':wn' => $wn, ':ot' => $ownerType, ':name' => $name, ':bal' => 0.00, ':st' => 'active', ':c' => $now, ':u' => $now];

        if ($ownerType === 'USER') {
            $fields[] = 'user_id';
            $values[] = ':oid';
            $bind[':oid'] = $ownerId;
        }

        if ($ownerType === 'VENDOR') {
            $fields[] = 'vendor_id';
            $values[] = ':oid';
            $bind[':oid'] = $ownerId;
        }

        $sql = "INSERT INTO zzimba_wallets (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
        $st = $this->pdo->prepare($sql);
        $st->execute($bind);

        return ['wallet_id' => $wid, 'wallet_number' => $wn, 'wallet_name' => $name, 'current_balance' => 0.00, 'status' => 'active', 'created_at' => $now];
    }
}

final class EntityLookup
{
    public function __construct(private PDO $pdo)
    {
    }

    public function entityInfo(?string $userId, ?string $vendorId): array
    {
        if ($userId)
            return ['type' => 'User', 'name' => $this->userName($userId), 'id' => $userId];
        if ($vendorId)
            return ['type' => 'Store', 'name' => $this->vendorName($vendorId), 'id' => $vendorId];
        return ['type' => 'Unknown', 'name' => 'Unknown', 'id' => null];
    }

    public function userName(string $id): string
    {
        $st = $this->pdo->prepare('SELECT username FROM zzimba_users WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        return $st->fetchColumn() ?: 'Unknown';
    }

    public function vendorName(string $id): string
    {
        $st = $this->pdo->prepare('SELECT name FROM vendor_stores WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        return $st->fetchColumn() ?: 'Unknown';
    }
}

final class TxRepo
{
    public function __construct(private PDO $pdo)
    {
    }

    public function insert(array $row): void
    {
        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_financial_transactions ($cols, created_at, updated_at) VALUES ($params, NOW(), NOW())";
        $st = $this->pdo->prepare($sql);
        $st->execute($row);
    }

    public function updateStatusByExtRef(string $externalRef, string $status, ?string $note, string $metadata): void
    {
        $st = $this->pdo->prepare("UPDATE zzimba_financial_transactions SET status=:st, note=:nt, external_metadata=:md, updated_at=NOW() WHERE external_reference=:er");
        $st->execute([':st' => $status, ':nt' => $note, ':md' => $metadata, ':er' => $externalRef]);
    }

    public function updateStatus(string $txnId, string $status): void
    {
        $st = $this->pdo->prepare("UPDATE zzimba_financial_transactions SET status=:st, updated_at=NOW() WHERE transaction_id=:id");
        $st->execute([':st' => $status, ':id' => $txnId]);
    }

    public function byExternalRef(string $er): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM zzimba_financial_transactions WHERE external_reference=:er LIMIT 1");
        $st->execute([':er' => $er]);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function insertTransfer(array $row): void
    {
        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_wallet_transfers ($cols) VALUES ($params)";
        $st = $this->pdo->prepare($sql);
        $st->execute($row);
    }
}

final class EntryRepo
{
    public function __construct(private PDO $pdo)
    {
    }

    public function insert(array $row): string
    {
        $row['entry_id'] = Ids::ulid();
        $row['created_at'] = date('Y-m-d H:i:s');
        $cols = implode(',', array_keys($row));
        $params = ':' . implode(',:', array_keys($row));
        $sql = "INSERT INTO zzimba_transaction_entries ($cols) VALUES ($params)";
        $st = $this->pdo->prepare($sql);
        $st->execute($row);
        return $row['entry_id'];
    }

    public function entriesForStatement(string $txnId, string $walletId): array
    {
        $st = $this->pdo->prepare("SELECT e.*, COALESCE(ca.name, w.wallet_name) AS account_or_wallet_name, w.owner_type FROM zzimba_transaction_entries e LEFT JOIN zzimba_cash_accounts ca ON e.cash_account_id=ca.id LEFT JOIN zzimba_wallets w ON e.wallet_id=w.wallet_id WHERE e.transaction_id=:t AND e.wallet_id=:w ORDER BY e.created_at ASC");
        $st->execute([':t' => $txnId, ':w' => $walletId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }
}

final class SettingsRepo
{
    public function __construct(private PDO $pdo)
    {
    }

    public function activeSetting(string $key, string $category, array $applicable): ?array
    {
        $vals = array_values(array_unique(array_merge(['all'], $applicable)));
        $ph = [];
        $params = [':k' => $key, ':c' => $category];
        foreach ($vals as $i => $v) {
            $ph[] = ":ap$i";
            $params[":ap$i"] = $v;
        }
        $sql = "SELECT id,setting_key,setting_name,setting_value,setting_type,applicable_to FROM zzimba_credit_settings WHERE setting_key=:k AND category=:c AND status='active' AND applicable_to IN (" . implode(',', $ph) . ") ORDER BY CASE WHEN applicable_to='all' THEN 1 ELSE 2 END LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}

final class FeeEngine
{
    public function __construct(private SettingsRepo $settings, private WalletRepo $wallets)
    {
    }

    public function transferFeeForUser(float $amount): array
    {
        $s = $this->settings->activeSetting('transfer_fee', 'transfer', ['users']);
        if (!$s)
            return ['fee' => 0.0, 'sink' => null, 'settingId' => null, 'type' => null];
        $fee = $s['setting_type'] === 'flat' ? (float) $s['setting_value'] : ($amount * (float) $s['setting_value'] / 100.0);
        $sink = $this->wallets->destinationForCreditSetting($s['id']);
        return ['fee' => $fee, 'sink' => $sink, 'settingId' => $s['id'], 'type' => $s['setting_type']];
    }

    public function smsCostSink(string $ownerType): ?array
    {
        $s = $this->settings->activeSetting('sms_cost', 'sms', [strtolower($ownerType) . 's']);
        if (!$s)
            return null;
        return $this->wallets->destinationForCreditSetting($s['id']);
    }

    public function quoteFeeSetting(): ?array
    {
        return $this->settings->activeSetting('request_for_quote', 'quote', ['users', 'all']);
    }

    public function welcomeBonusSetting(string $ownerType): ?array
    {
        return $this->settings->activeSetting('welcome_bonus', 'bonus', [strtolower($ownerType) . 's', 'all']);
    }

    public function buyInStoreChargeSetting(): ?array
    {
        return $this->settings->activeSetting('buy_in_store_charge', 'buy_in_store', ['users', 'all']);
    }

    public function priceViewSetting(): ?array
    {
        return $this->settings->activeSetting('price_view', 'price_view', ['users', 'all']);
    }

    public function contactDetailsViewSetting(): ?array
    {
        return $this->settings->activeSetting('contact_details_view', 'contact_details_view', ['users', 'all']);
    }
}

final class CommissionEngine
{
    public function __construct(private WalletRepo $wallets)
    {
    }

    public function breakdown(string $txnType, float $amount, array $context): array
    {
        return [];
    }
}

final class LedgerService
{
    public function __construct(private PDO $pdo, private WalletRepo $wallets, private EntryRepo $entries)
    {
    }

    public function move(string $txnId, string $fromWalletId, string $toWalletId, float $amount, array $notes): void
    {
        $withId = $this->wallets->withholdingWalletId();

        $fromBal = $this->wallets->balance($fromWalletId);
        $withBal = $this->wallets->balance($withId);
        $toBal = $this->wallets->balance($toWalletId);

        $debitId = $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $fromWalletId, 'entry_type' => 'DEBIT', 'amount' => $amount, 'balance_after' => $fromBal - $amount, 'entry_note' => $notes['debit']]);
        $this->wallets->updateBalance($fromWalletId, $fromBal - $amount);

        $credit1Id = $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $withId, 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $withBal + $amount, 'entry_note' => $notes['credit1'], 'ref_entry_id' => $debitId]);
        $this->wallets->updateBalance($withId, $withBal + $amount);

        $debit2Id = $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $withId, 'entry_type' => 'DEBIT', 'amount' => $amount, 'balance_after' => ($withBal + $amount) - $amount, 'entry_note' => $notes['debit2'], 'ref_entry_id' => $credit1Id]);
        $this->wallets->updateBalance($withId, ($withBal + $amount) - $amount);

        $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $toWalletId, 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $toBal + $amount, 'entry_note' => $notes['credit2'], 'ref_entry_id' => $debit2Id]);
        $this->wallets->updateBalance($toWalletId, $toBal + $amount);
    }

    public function creditFromWithholding(string $txnId, string $toWalletId, float $amount, string $noteToWithholding, string $noteToReceiver): void
    {
        $withId = $this->wallets->withholdingWalletId();

        $withBal = $this->wallets->balance($withId);
        $toBal = $this->wallets->balance($toWalletId);

        $creditWith = $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $withId, 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $withBal + $amount, 'entry_note' => $noteToWithholding]);
        $this->wallets->updateBalance($withId, $withBal + $amount);

        $debitWith = $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $withId, 'entry_type' => 'DEBIT', 'amount' => $amount, 'balance_after' => ($withBal + $amount) - $amount, 'entry_note' => 'Disbursed', 'ref_entry_id' => $creditWith]);
        $this->wallets->updateBalance($withId, ($withBal + $amount) - $amount);

        $this->entries->insert(['transaction_id' => $txnId, 'wallet_id' => $toWalletId, 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $toBal + $amount, 'entry_note' => $noteToReceiver, 'ref_entry_id' => $debitWith]);
        $this->wallets->updateBalance($toWalletId, $toBal + $amount);
    }
}

final class GatewayClient
{
    public function request(string $url, array $data, string $method = 'POST'): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            if (!empty($data))
                curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/vnd.relworx.v2', 'Authorization: Bearer ' . ModuleConfig::API_KEY]);

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http !== 200)
            return ['success' => false, 'message' => 'API request failed', 'error' => $err, 'http' => $http];

        return json_decode($resp, true) ?? ['success' => false, 'message' => 'Invalid response'];
    }

    public function validateMsisdn(string $msisdn): array
    {
        return $this->request('https://payments.relworx.com/api/mobile-money/validate', ['msisdn' => $msisdn]);
    }

    public function requestPayment(string $msisdn, float $amount, string $reference, string $description): array
    {
        $payload = ['account_no' => ModuleConfig::ACCOUNT_NO, 'reference' => $reference, 'msisdn' => $msisdn, 'currency' => ModuleConfig::DEFAULT_CURRENCY, 'amount' => $amount, 'description' => $description];
        return $this->request('https://payments.relworx.com/api/mobile-money/request-payment', $payload);
    }

    public function checkStatus(string $internalRef): array
    {
        return $this->request('https://payments.relworx.com/api/mobile-money/check-request-status', ['internal_reference' => $internalRef, 'account_no' => ModuleConfig::ACCOUNT_NO], 'GET');
    }
}

final class StatementService
{
    public function __construct(private PDO $pdo, private EntryRepo $entries)
    {
    }

    public function walletStatement(array $wallet, string $filter = 'all', ?string $start = null, ?string $end = null): array
    {
        if ($wallet['owner_type'] === 'USER')
            $st = $this->pdo->prepare("SELECT transaction_id FROM zzimba_financial_transactions WHERE user_id=:id");
        elseif ($wallet['owner_type'] === 'VENDOR')
            $st = $this->pdo->prepare("SELECT transaction_id FROM zzimba_financial_transactions WHERE vendor_id=:id");
        else
            $st = $this->pdo->prepare("SELECT DISTINCT ft.transaction_id FROM zzimba_financial_transactions ft JOIN zzimba_transaction_entries e ON ft.transaction_id=e.transaction_id WHERE e.wallet_id=:id");

        $param = $wallet['owner_type'] === 'USER' ? [':id' => $wallet['user_id']] : ($wallet['owner_type'] === 'VENDOR' ? [':id' => $wallet['vendor_id']] : [':id' => $wallet['wallet_id']]);
        $st->execute($param);
        $ft = $st->fetchAll(\PDO::FETCH_COLUMN);

        $et = $this->pdo->prepare("SELECT DISTINCT transaction_id FROM zzimba_transaction_entries WHERE wallet_id=:w");
        $et->execute([':w' => $wallet['wallet_id']]);

        $allTxnIds = array_unique(array_merge($ft, $et->fetchAll(\PDO::FETCH_COLUMN)));
        if (empty($allTxnIds))
            return [];

        $ph = [];
        $pr = [];
        foreach ($allTxnIds as $i => $tid) {
            $k = ":t$i";
            $ph[] = $k;
            $pr[$k] = $tid;
        }

        $sql = "SELECT * FROM zzimba_financial_transactions WHERE transaction_id IN (" . implode(',', $ph) . ")";
        if ($filter === 'range') {
            $sql .= " AND created_at BETWEEN :s AND :e";
            $pr[':s'] = $start;
            $pr[':e'] = $end;
        }
        $sql .= " ORDER BY created_at DESC, CASE WHEN original_txn_id IS NOT NULL THEN 1 ELSE 2 END ASC";

        $tx = $this->pdo->prepare($sql);
        $tx->execute($pr);
        $rows = $tx->fetchAll(\PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $r['entries'] = $this->entries->entriesForStatement($r['transaction_id'], $wallet['wallet_id']);
            $out[] = ['transaction' => $r];
        }

        return $out;
    }
}

final class CreditService
{
    private static bool $booted = false;
    private static PDO $pdo;
    private static NotificationBus $bus;
    private static WalletRepo $wallets;
    private static TxRepo $tx;
    private static EntryRepo $entries;
    private static SettingsRepo $settings;
    private static FeeEngine $fees;
    private static CommissionEngine $commissions;
    private static LedgerService $ledger;
    private static GatewayClient $gateway;
    private static EntityLookup $entities;
    private static StatementService $statements;

    private static function boot(): void
    {
        if (self::$booted)
            return;

        Boot::init();
        self::$pdo = Boot::pdo();
        self::$bus = new NotificationBus(self::$pdo);
        self::$wallets = new WalletRepo(self::$pdo);
        self::$tx = new TxRepo(self::$pdo);
        self::$entries = new EntryRepo(self::$pdo);
        self::$settings = new SettingsRepo(self::$pdo);
        self::$fees = new FeeEngine(self::$settings, self::$wallets);
        self::$commissions = new CommissionEngine(self::$wallets);
        self::$ledger = new LedgerService(self::$pdo, self::$wallets, self::$entries);
        self::$gateway = new GatewayClient();
        self::$entities = new EntityLookup(self::$pdo);
        self::$statements = new StatementService(self::$pdo, self::$entries);
        self::$booted = true;
    }

    public static function validateMsisdn(string $msisdn): array
    {
        self::boot();

        $res = self::$gateway->validateMsisdn($msisdn);
        $name = $res['customer_name'] ?? 'Unknown';

        $rec = self::$bus->recipients(['message' => "MSISDN validation request: {$msisdn}. Customer Name: {$name}"]);
        self::$bus->send('MSISDN Validation', $rec);

        return $res;
    }

    public static function makeMobileMoneyPayment(array $opts): array
    {
        self::boot();

        $msisdn = trim($opts['msisdn'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);

        if ($msisdn === '' || $amount <= 0)
            return ['success' => false, 'message' => 'MSISDN and positive amount required'];
        if ($amount < 500)
            return ['success' => false, 'message' => 'Minimum amount is 500 UGX'];

        $reference = Ids::ulid();
        $res = self::$gateway->requestPayment($msisdn, $amount, $reference, trim($opts['description'] ?? 'Payment Request.'));
        $externalRef = $res['internal_reference'] ?? $reference;

        self::$tx->insert([
            'transaction_id' => $reference,
            'transaction_type' => 'TOPUP',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'MOBILE_MONEY_GATEWAY',
            'external_reference' => $externalRef,
            'external_metadata' => json_encode($res),
            'user_id' => $opts['user_id'] ?? null,
            'vendor_id' => $opts['vendor_id'] ?? null
        ]);

        $entity = self::$entities->entityInfo($opts['user_id'] ?? null, $opts['vendor_id'] ?? null);

        $rec = self::$bus->recipients(
            ['message' => "{$entity['type']} {$entity['name']} initiated Mobile Money top-up of {$amount} UGX from {$msisdn}."],
            !empty($opts['user_id']) ? ['id' => $opts['user_id'], 'message' => "You initiated a Mobile Money top-up of {$amount} UGX from {$msisdn}."] : null,
            !empty($opts['vendor_id']) ? ['id' => $opts['vendor_id'], 'message' => "Mobile Money top-up of {$amount} UGX was initiated successfully."] : null
        );

        self::$bus->send('Mobile Money Payment Initiated', $rec, 'normal', $entity['id']);

        return $res;
    }

    public static function checkRequestStatus(string $internalRef): array
    {
        self::boot();

        $prev = self::$pdo->prepare("SELECT status FROM zzimba_financial_transactions WHERE external_reference=:er LIMIT 1");
        $prev->execute([':er' => $internalRef]);
        $previousStatus = $prev->fetchColumn();

        $res = self::$gateway->checkStatus($internalRef);
        $newStatus = strtoupper($res['request_status'] ?? $res['status'] ?? 'PENDING');

        self::$tx->updateStatusByExtRef($internalRef, $newStatus, $res['message'] ?? null, json_encode($res));

        if ($previousStatus !== $newStatus) {
            $txnRow = self::$pdo->prepare("SELECT transaction_id, amount_total, user_id, vendor_id FROM zzimba_financial_transactions WHERE external_reference=:er");
            $txnRow->execute([':er' => $internalRef]);
            $txn = $txnRow->fetch(\PDO::FETCH_ASSOC);

            if ($txn) {
                $entity = self::$entities->entityInfo($txn['user_id'], $txn['vendor_id']);

                $adminMessage = ['message' => "{$entity['type']} {$entity['name']}'s payment status changed from {$previousStatus} to {$newStatus}. Amount: {$txn['amount_total']} UGX"];
                $userMessage = null;
                $vendorMessage = null;

                if ($newStatus === 'SUCCESS') {
                    if (!empty($txn['user_id']))
                        $userMessage = ['id' => $txn['user_id'], 'message' => "Payment successful! {$txn['amount_total']} UGX has been credited to your wallet."];
                    if (!empty($txn['vendor_id']))
                        $vendorMessage = ['id' => $txn['vendor_id'], 'message' => "Payment successful! {$txn['amount_total']} UGX has been credited to your store wallet."];
                } elseif ($newStatus === 'FAILED') {
                    if (!empty($txn['user_id']))
                        $userMessage = ['id' => $txn['user_id'], 'message' => "Payment failed. Amount: {$txn['amount_total']} UGX"];
                    if (!empty($txn['vendor_id']))
                        $vendorMessage = ['id' => $txn['vendor_id'], 'message' => "Payment failed. Amount: {$txn['amount_total']} UGX"];
                }

                $rec = self::$bus->recipients($adminMessage, $userMessage, $vendorMessage);
                self::$bus->send('Transaction Status Update', $rec, $newStatus === 'FAILED' ? 'high' : 'normal', $txn['user_id'] ?? $txn['vendor_id'] ?? null);

                if ($newStatus === 'SUCCESS' && !empty($txn['user_id'])) {
                    $txnId = $txn['transaction_id'];
                    $amount = (float) $txn['amount_total'];
                    $userId = $txn['user_id'];
                    $u = self::$wallets->userWallet($userId);

                    if ($u) {
                        try {
                            self::$pdo->beginTransaction();

                            self::$ledger->creditFromWithholding($txnId, $u['wallet_id'], $amount, 'Zzimba Credit top-up', 'Credited User Wallet from Withholding');

                            $cash = self::$wallets->gatewayCashAccount();
                            if ($cash) {
                                $withId = self::$wallets->withholdingWalletId();
                                $withBal = self::$wallets->balance($withId);
                                $debitId = self::$entries->insert(['transaction_id' => $txnId, 'wallet_id' => $withId, 'entry_type' => 'DEBIT', 'amount' => 0, 'balance_after' => $withBal, 'entry_note' => '']);
                                $newCash = (float) $cash['current_balance'] + $amount;
                                self::$entries->insert(['transaction_id' => $txnId, 'cash_account_id' => $cash['id'], 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $newCash, 'entry_note' => 'Credited Gateway Cash from Withholding', 'ref_entry_id' => $debitId]);
                                $st = self::$pdo->prepare("UPDATE zzimba_cash_accounts SET current_balance=:b, updated_at=NOW() WHERE id=:id");
                                $st->execute([':b' => $newCash, ':id' => $cash['id']]);
                            }

                            self::$pdo->commit();
                        } catch (\Throwable $e) {
                            self::$pdo->rollBack();
                        }
                    }
                }
            }
        }

        return $res;
    }

    public static function getWallet(string $ownerType, ?string $ownerId = null): array
    {
        self::boot();

        $wallet = $ownerType === 'PLATFORM'
            ? self::$pdo->query("SELECT wallet_id,wallet_number,wallet_name,current_balance,status,created_at FROM zzimba_wallets WHERE owner_type='PLATFORM' AND status='active' LIMIT 1")->fetch(\PDO::FETCH_ASSOC)
            : ($ownerType === 'USER' ? self::$wallets->userWallet($ownerId) : self::$wallets->vendorWallet($ownerId));

        if ($wallet)
            return ['success' => true, 'newAccount' => false, 'wallet' => $wallet];

        $name = 'My Wallet';
        if ($ownerType === 'USER') {
            $st = self::$pdo->prepare("SELECT first_name,last_name FROM zzimba_users WHERE id=:id LIMIT 1");
            $st->execute([':id' => $ownerId]);
            $r = $st->fetch(\PDO::FETCH_ASSOC);
            if (!$r)
                return ['success' => false, 'newAccount' => false, 'message' => 'User not found'];
            $first = trim((string) ($r['first_name'] ?? ''));
            $last = trim((string) ($r['last_name'] ?? ''));
            if ($first === '' || $last === '')
                return ['success' => false, 'newAccount' => false, 'message' => 'Please add your first name and last name to your profile before creating a wallet'];
            $name = trim($first . ' ' . $last);
        }
        if ($ownerType === 'VENDOR') {
            $st = self::$pdo->prepare("SELECT name FROM vendor_stores WHERE id=:id");
            $st->execute([':id' => $ownerId]);
            if ($r = $st->fetch(\PDO::FETCH_ASSOC))
                $name = trim($r['name']);
        }

        $new = self::$wallets->create($ownerType, $ownerId, $name);

        $rec = self::$bus->recipients(
            ['message' => "New wallet created for {$ownerType}: {$name} (#{$new['wallet_number']})"],
            $ownerType === 'USER' ? ['id' => $ownerId, 'message' => "Welcome! Your wallet has been created successfully. Wallet Number: {$new['wallet_number']}"] : null,
            $ownerType === 'VENDOR' ? ['id' => $ownerId, 'message' => "Welcome! Your store wallet has been created successfully. Wallet Number: {$new['wallet_number']}"] : null
        );
        self::$bus->send('New Wallet Created', $rec, 'normal', $ownerId);

        if (in_array($ownerType, ['USER', 'VENDOR'], true)) {
            $setting = self::$fees->welcomeBonusSetting($ownerType);
            if ($setting && ($amount = (float) $setting['setting_value']) > 0) {
                $source = self::$wallets->destinationForCreditSetting($setting['id']);
                if ($source && (float) $source['current_balance'] >= $amount) {
                    $txnId = Ids::ulid();
                    self::$tx->insert([
                        'transaction_id' => $txnId,
                        'transaction_type' => 'TRANSFER',
                        'status' => 'PENDING',
                        'amount_total' => $amount,
                        'payment_method' => 'WALLET',
                        'wallet_id' => $new['wallet_id'],
                        'user_id' => $ownerType === 'USER' ? $ownerId : null,
                        'vendor_id' => $ownerType === 'VENDOR' ? $ownerId : null,
                        'note' => 'Welcome bonus'
                    ]);

                    self::$tx->insertTransfer([
                        'id' => Ids::ulid(),
                        'wallet_from' => $source['wallet_id'],
                        'wallet_to' => $new['wallet_id'],
                        'transaction_id' => $txnId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $notes = [
                        'debit' => "Welcome bonus request to {$new['wallet_number']}",
                        'credit1' => "Welcome bonus instruction for {$new['wallet_number']}",
                        'debit2' => "Welcome bonus executed for {$new['wallet_number']}",
                        'credit2' => "Welcome bonus for {$new['wallet_number']}"
                    ];

                    try {
                        self::$pdo->beginTransaction();
                        self::$ledger->move($txnId, $source['wallet_id'], $new['wallet_id'], $amount, $notes);
                        self::$tx->updateStatus($txnId, 'SUCCESS');
                        self::$pdo->commit();

                        $bonusRecipients = self::$bus->recipients(
                            ['message' => "Welcome bonus of {$amount} UGX credited to new {$ownerType} wallet: {$name} (#{$new['wallet_number']})"],
                            $ownerType === 'USER' ? ['id' => $ownerId, 'message' => "Congratulations! You have received a welcome bonus of {$amount} UGX in your new wallet."] : null,
                            $ownerType === 'VENDOR' ? ['id' => $ownerId, 'message' => "Congratulations! Your store has received a welcome bonus of {$amount} UGX in your new wallet."] : null
                        );

                        self::$bus->send('Welcome Bonus Credited', $bonusRecipients, 'normal', $ownerId);
                    } catch (\Throwable $e) {
                        if (self::$pdo->inTransaction())
                            self::$pdo->rollBack();
                        self::$tx->updateStatus($txnId, 'FAILED');
                    }
                }
            }
        }

        return ['success' => true, 'newAccount' => true, 'wallet' => $new];
    }

    public static function getWalletStatement(string $walletId, string $filter = 'all', ?string $start = null, ?string $end = null): array
    {
        self::boot();

        $w = self::$wallets->byId($walletId);
        if (!$w)
            return ['success' => false, 'statement' => [], 'message' => 'Wallet not found or inactive'];

        $statement = self::$statements->walletStatement($w, $filter, $start, $end);

        return ['success' => true, 'wallet' => ['wallet_id' => $w['wallet_id'], 'wallet_number' => $w['wallet_number'], 'owner_type' => $w['owner_type'], 'wallet_name' => $w['wallet_name']], 'statement' => $statement];
    }

    public static function transfer(array $opts): array
    {
        self::boot();

        $walletTo = trim($opts['wallet_to'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);

        if ($walletTo === '' || $amount < 500)
            return ['success' => false, 'message' => 'Destination Account No. and amount >= 500 required'];

        $userId = $_SESSION['user']['user_id'] ?? null;
        if (!$userId)
            return ['success' => false, 'message' => 'Not authenticated'];

        $from = self::$wallets->userWallet($userId);
        if (!$from)
            return ['success' => false, 'message' => 'Source wallet not found'];

        $to = self::$wallets->byNumber($walletTo);
        if (!$to)
            return ['success' => false, 'message' => 'Destination wallet not found or inactive'];

        if ($from['wallet_id'] === $to['wallet_id'])
            return ['success' => false, 'message' => 'Cannot transfer to the same wallet'];

        $fee = self::$fees->transferFeeForUser($amount);
        $transferFee = (float) $fee['fee'];
        $totalRequired = $amount + $transferFee;

        if ((float) $from['current_balance'] < $totalRequired)
            return ['success' => false, 'message' => 'Insufficient funds for transfer and fees'];

        $transferTxnId = Ids::ulid();

        self::$tx->insert([
            'transaction_id' => $transferTxnId,
            'transaction_type' => 'TRANSFER',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'WALLET',
            'user_id' => $userId,
            'vendor_id' => $to['owner_type'] === 'VENDOR' ? $to['vendor_id'] : null,
            'note' => 'Zzimba Credit transfer'
        ]);

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $from['wallet_id'], 'wallet_to' => $to['wallet_id'], 'transaction_id' => $transferTxnId, 'created_at' => date('Y-m-d H:i:s')]);

        $chargesTxnId = null;

        try {
            self::$pdo->beginTransaction();

            $fromNo = $from['wallet_number'];
            $toNo = $to['wallet_number'];

            self::$ledger->move($transferTxnId, $from['wallet_id'], $to['wallet_id'], $amount, [
                'debit' => "Zzimba Credit transfer to {$toNo}",
                'credit1' => "Credit transfer instruction from {$fromNo}",
                'debit2' => "Credit transfer executed for {$toNo}",
                'credit2' => "Zzimba Credit transfer from {$fromNo}"
            ]);

            if ($transferFee > 0 && $fee['sink']) {
                $chargesTxnId = Ids::ulid();
                self::$tx->insert([
                    'transaction_id' => $chargesTxnId,
                    'transaction_type' => 'CHARGES',
                    'status' => 'PENDING',
                    'amount_total' => $transferFee,
                    'payment_method' => 'WALLET',
                    'user_id' => $userId,
                    'original_txn_id' => $transferTxnId,
                    'note' => 'Transfer fee charges'
                ]);

                self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $from['wallet_id'], 'wallet_to' => $fee['sink']['wallet_id'], 'transaction_id' => $chargesTxnId, 'created_at' => date('Y-m-d H:i:s')]);

                self::$ledger->move($chargesTxnId, $from['wallet_id'], $fee['sink']['wallet_id'], $transferFee, [
                    'debit' => "Transfer fee charge for transaction to {$toNo}",
                    'credit1' => "Transfer fee collection from {$fromNo}",
                    'debit2' => "Transfer fee disbursement to {$toNo}",
                    'credit2' => "Transfer fee earned from {$fromNo}"
                ]);

                self::$tx->updateStatus($chargesTxnId, 'SUCCESS');
            }

            self::$tx->updateStatus($transferTxnId, 'SUCCESS');

            self::$pdo->commit();

            $ownerStmt = self::$pdo->prepare("SELECT owner_type, CASE WHEN owner_type='USER' THEN user_id ELSE vendor_id END AS entity_id FROM zzimba_wallets WHERE wallet_id=:w LIMIT 1");
            $ownerStmt->execute([':w' => $from['wallet_id']]);
            $fromOwner = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
            $senderEntity = self::$entities->entityInfo($fromOwner['owner_type'] === 'USER' ? $fromOwner['entity_id'] : null, $fromOwner['owner_type'] === 'VENDOR' ? $fromOwner['entity_id'] : null);

            $ownerStmt->execute([':w' => $to['wallet_id']]);
            $toOwner = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
            $recipientEntity = self::$entities->entityInfo($toOwner['owner_type'] === 'USER' ? $toOwner['entity_id'] : null, $toOwner['owner_type'] === 'VENDOR' ? $toOwner['entity_id'] : null);

            $feeMessage = $transferFee > 0 ? " (Fee: {$transferFee} UGX)" : "";
            $rec = self::$bus->recipients(
                ['message' => "{$senderEntity['type']} {$senderEntity['name']} sent {$amount} UGX to {$recipientEntity['type']} {$recipientEntity['name']}.{$feeMessage}"],
                ['id' => $senderEntity['id'], 'message' => "You sent {$amount} UGX to {$recipientEntity['type']} {$recipientEntity['name']}.{$feeMessage}"],
                $recipientEntity['type'] === 'Store' ? ['id' => $recipientEntity['id'], 'message' => "{$senderEntity['type']} {$senderEntity['name']} sent you {$amount} UGX."] : null
            );
            if ($recipientEntity['type'] === 'User')
                $rec[] = ['type' => 'user', 'id' => $recipientEntity['id'], 'message' => "{$senderEntity['type']} {$senderEntity['name']} sent you {$amount} UGX."];
            self::$bus->send('Wallet Transfer Completed', $rec, 'normal', $senderEntity['id']);
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            self::$tx->updateStatus($transferTxnId, 'FAILED');
            if ($chargesTxnId)
                self::$tx->updateStatus($chargesTxnId, 'FAILED');
            return ['success' => false, 'message' => 'Transfer failed'];
        }

        $newBalance = self::$wallets->balance($from['wallet_id']);

        return ['success' => true, 'transaction_id' => $transferTxnId, 'charges_transaction_id' => $chargesTxnId, 'balance' => $newBalance, 'fee_charged' => $transferFee, 'total_deducted' => $totalRequired];
    }

    public static function processQuoteRequest(array $opts): array
    {
        self::boot();

        $amount = (float) ($opts['amount'] ?? 0);
        $userId = trim($opts['user_id'] ?? '');

        if ($amount <= 0 || $userId === '')
            return ['success' => false, 'message' => 'Invalid amount or user ID'];

        $userWallet = self::$wallets->userWallet($userId);
        if (!$userWallet)
            return ['success' => false, 'message' => 'User wallet not found'];

        if ((float) $userWallet['current_balance'] < $amount)
            return ['success' => false, 'message' => 'Insufficient wallet balance', 'balance' => (float) $userWallet['current_balance'], 'required' => $amount];

        $setting = self::$fees->quoteFeeSetting();
        if (!$setting)
            return ['success' => false, 'message' => 'Quote fee setting not found'];

        $feeAmount = (float) $setting['setting_value'];
        if ($amount != $feeAmount)
            return ['success' => false, 'message' => 'Amount does not match quote fee setting'];

        $destination = self::$wallets->destinationForCreditSetting($setting['id']);
        if (!$destination)
            return ['success' => false, 'message' => 'Quote fee destination wallet not configured'];

        $txnId = Ids::ulid();

        try {
            self::$pdo->beginTransaction();

            self::$tx->insert(['transaction_id' => $txnId, 'transaction_type' => 'QUOTE', 'status' => 'SUCCESS', 'amount_total' => $amount, 'payment_method' => 'WALLET', 'wallet_id' => $userWallet['wallet_id'], 'user_id' => $userId, 'note' => 'Quote request fee']);
            self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $userWallet['wallet_id'], 'wallet_to' => $destination['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

            self::$ledger->move($txnId, $userWallet['wallet_id'], $destination['wallet_id'], $amount, [
                'debit' => "Quote request fee charged to {$userWallet['wallet_number']}",
                'credit1' => "Quote fee collection from {$userWallet['wallet_number']}",
                'debit2' => "Quote fee disbursement to {$destination['wallet_number']}",
                'credit2' => "Quote fee earned from {$userWallet['wallet_number']}"
            ]);

            self::$pdo->commit();

            return ['success' => true, 'transaction_id' => $txnId, 'fee_charged' => $amount, 'remaining_balance' => (float) $userWallet['current_balance'] - $amount];
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Quote request processing failed'];
        }
    }

    public static function processQuotePayment(array $opts): array
    {
        self::boot();

        $amount = (float) ($opts['amount'] ?? 0);
        $userId = trim($opts['user_id'] ?? '');
        $quotationId = trim($opts['quotation_id'] ?? '');

        if ($amount <= 0 || $userId === '' || $quotationId === '')
            return ['success' => false, 'message' => 'Invalid amount, user ID, or quotation ID'];

        $userWallet = self::$wallets->userWallet($userId);
        if (!$userWallet)
            return ['success' => false, 'message' => 'User wallet not found'];

        if ((float) $userWallet['current_balance'] < $amount)
            return ['success' => false, 'message' => 'Insufficient wallet balance', 'balance' => (float) $userWallet['current_balance'], 'required' => $amount];

        $ops = self::$wallets->operationsWallet();
        if (!$ops)
            return ['success' => false, 'message' => 'Operations account not configured'];

        $txnId = Ids::ulid();

        try {
            self::$pdo->beginTransaction();

            self::$tx->insert(['transaction_id' => $txnId, 'transaction_type' => 'QUOTE', 'status' => 'SUCCESS', 'amount_total' => $amount, 'payment_method' => 'WALLET', 'wallet_id' => $userWallet['wallet_id'], 'user_id' => $userId, 'note' => 'Quote payment for quotation ID: ' . $quotationId]);
            self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $userWallet['wallet_id'], 'wallet_to' => $ops['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

            self::$ledger->move($txnId, $userWallet['wallet_id'], $ops['wallet_id'], $amount, [
                'debit' => "Quote payment for quotation {$quotationId}",
                'credit1' => "Quote payment collection from {$userWallet['wallet_number']}",
                'debit2' => "Quote payment disbursement to operations account",
                'credit2' => "Quote payment received from {$userWallet['wallet_number']}"
            ]);

            self::$pdo->commit();

            return ['success' => true, 'transaction_id' => $txnId, 'amount_paid' => $amount, 'quotation_id' => $quotationId, 'remaining_balance' => (float) $userWallet['current_balance'] - $amount];
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Quote payment processing failed'];
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

        if (!$walletId || !$cashAccountId || $amount <= 0 || !in_array($paymentMethod, ['BANK', 'MOBILE_MONEY'], true))
            return ['success' => false, 'message' => 'Invalid parameters'];
        if ($paymentMethod === 'MOBILE_MONEY' && (!$mmPhoneNumber || !$mmDateTime))
            return ['success' => false, 'message' => 'Phone & date/time required'];
        if ($paymentMethod === 'BANK' && (!$btDepositorName || !$btDateTime))
            return ['success' => false, 'message' => 'Depositor & date/time required'];

        $w = self::$wallets->byId($walletId);
        if (!$w)
            return ['success' => false, 'message' => 'Wallet not found or inactive'];

        $ownerType = $w['owner_type'];

        $txnId = Ids::ulid();

        $payload = ['transaction_id' => $txnId, 'transaction_type' => 'TOPUP', 'status' => 'PENDING', 'amount_total' => $amount, 'payment_method' => $paymentMethod, 'external_reference' => $externalRef, 'external_metadata' => json_encode($opts), 'wallet_id' => $walletId, 'note' => $note];
        if ($ownerType === 'USER' && !empty($userId))
            $payload['user_id'] = $userId;
        if ($ownerType === 'VENDOR' && !empty($vendorId))
            $payload['vendor_id'] = $vendorId;

        try {
            self::$tx->insert($payload);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Could not save transaction'];
        }

        $rec = self::$bus->recipients(
            ['message' => "Cash top-up request logged: {$amount} UGX via {$paymentMethod}. Awaiting confirmation."],
            ($ownerType === 'USER' && !empty($userId)) ? ['id' => $userId, 'message' => "Your cash top-up request of {$amount} UGX has been logged and is pending admin confirmation."] : null,
            ($ownerType === 'VENDOR' && !empty($vendorId)) ? ['id' => $vendorId, 'message' => "Your store's cash top-up request of {$amount} UGX has been logged and is pending admin confirmation."] : null
        );
        self::$bus->send('Cash Top-up Request Logged', $rec, 'normal', $userId ?? $vendorId ?? null);

        try {
            $walletNameStmt = self::$pdo->prepare("SELECT wallet_name FROM zzimba_wallets WHERE wallet_id=:w LIMIT 1");
            $walletNameStmt->execute([':w' => $walletId]);
            $initiator = $walletNameStmt->fetchColumn() ?: $walletId;

            $adminPhones = self::$pdo->query("SELECT phone FROM admin_users WHERE status='active'")->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($adminPhones)) {
                $message = sprintf("%s initiated a cash top-up of %s UGX. Login to confirm.", $initiator, number_format($amount, 2));
                \SMS::sendBulk($adminPhones, $message);
            }
        } catch (\Throwable $e) {
        }

        return ['success' => true, 'transaction_id' => $txnId];
    }

    public static function acknowledgeCashTopup(string $transactionId, string $newStatus): array
    {
        self::boot();

        $newStatus = strtoupper($newStatus);
        if (!in_array($newStatus, ['SUCCESS', 'FAILED'], true))
            return ['success' => false, 'message' => 'Invalid status'];

        self::$tx->updateStatus($transactionId, $newStatus);

        $stmt = self::$pdo->prepare("SELECT amount_total, external_metadata, wallet_id, user_id, vendor_id FROM zzimba_financial_transactions WHERE transaction_id=:id");
        $stmt->execute([':id' => $transactionId]);
        $txn = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$txn)
            return ['success' => false, 'message' => 'Transaction not found'];

        $amount = (float) $txn['amount_total'];

        if ($newStatus === 'FAILED') {
            $rec = self::$bus->recipients(
                ['message' => "Cash top-up has been marked as {$newStatus}. Amount: {$amount} UGX"],
                !empty($txn['user_id']) ? ['id' => $txn['user_id'], 'message' => "Your cash top-up request of {$amount} UGX has been declined. Please contact support for assistance."] : null,
                !empty($txn['vendor_id']) ? ['id' => $txn['vendor_id'], 'message' => "Your store's cash top-up request of {$amount} UGX has been declined. Please contact support for assistance."] : null
            );
            self::$bus->send('Cash Top-up Declined', $rec, 'high', $txn['user_id'] ?? $txn['vendor_id'] ?? null);
            return ['success' => true, 'message' => 'Top-up marked as failed'];
        }

        $meta = json_decode($txn['external_metadata'], true);
        $cashAccountId = $meta['cash_account_id'] ?? null;
        if (!$cashAccountId)
            return ['success' => false, 'message' => 'Cash account not specified'];

        $wallet = $txn['wallet_id'] ? self::$wallets->byId($txn['wallet_id']) : null;
        if (!$wallet) {
            $ownerType = $txn['user_id'] ? 'USER' : 'VENDOR';
            $ownerId = $txn['user_id'] ?? $txn['vendor_id'] ?? null;
            $res = self::getWallet($ownerType, $ownerId);
            if (!$res['success'])
                return ['success' => false, 'message' => 'Wallet not found'];
            $wallet = $res['wallet'];
        }

        try {
            self::$pdo->beginTransaction();

            self::$ledger->creditFromWithholding($transactionId, $wallet['wallet_id'], $amount, 'Zzimba Credit top-up', 'Zzimba Credit top-up');

            $cStmt = self::$pdo->prepare("SELECT current_balance FROM zzimba_cash_accounts WHERE id=:id FOR UPDATE");
            $cStmt->execute([':id' => $cashAccountId]);
            $cashBal = (float) $cStmt->fetchColumn();

            $debitId = self::$entries->insert(['transaction_id' => $transactionId, 'wallet_id' => self::$wallets->withholdingWalletId(), 'entry_type' => 'DEBIT', 'amount' => 0, 'balance_after' => self::$wallets->balance(self::$wallets->withholdingWalletId()), 'entry_note' => '']);

            $newCash = $cashBal + $amount;
            self::$entries->insert(['transaction_id' => $transactionId, 'cash_account_id' => $cashAccountId, 'entry_type' => 'CREDIT', 'amount' => $amount, 'balance_after' => $newCash, 'entry_note' => 'Cash account credited from withholding', 'ref_entry_id' => $debitId]);
            $up = self::$pdo->prepare("UPDATE zzimba_cash_accounts SET current_balance=:b, updated_at=NOW() WHERE id=:id");
            $up->execute([':b' => $newCash, ':id' => $cashAccountId]);

            self::$pdo->commit();

            $rec = self::$bus->recipients(
                ['message' => "Cash top-up has been approved. Amount: {$amount} UGX"],
                !empty($txn['user_id']) ? ['id' => $txn['user_id'], 'message' => "Great news! Your cash top-up of {$amount} UGX has been approved and credited to your wallet."] : null,
                !empty($txn['vendor_id']) ? ['id' => $txn['vendor_id'], 'message' => "Great news! Your store's cash top-up of {$amount} UGX has been approved and credited to your wallet."] : null
            );
            self::$bus->send('Cash Top-up Approved', $rec, 'normal', $txn['user_id'] ?? $txn['vendor_id'] ?? null);
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
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

        if (!$walletId || !in_array($ownerType, ['USER', 'VENDOR'], true) || $amount <= 0)
            return ['success' => false, 'message' => 'Invalid wallet, owner_type or amount'];

        $ownerIdKey = $ownerType === 'USER' ? 'user_id' : 'vendor_id';
        $ownerId = trim($opts[$ownerIdKey] ?? '');
        if (!$ownerId)
            return ['success' => false, 'message' => "Missing {$ownerIdKey}"];

        $debitWallet = self::$wallets->byId($walletId);
        if (!$debitWallet || $debitWallet['owner_type'] !== $ownerType)
            return ['success' => false, 'message' => 'Debit wallet not found or inactive'];

        if ($debitWallet['current_balance'] < $amount)
            return ['success' => false, 'message' => 'Insufficient funds'];

        $sink = self::$fees->smsCostSink($ownerType);
        if (!$sink)
            return ['success' => false, 'message' => 'SMS-sink wallet not configured'];

        $txnId = Ids::ulid();

        self::$tx->insert([
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

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $walletId, 'wallet_to' => $sink['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

        try {
            self::$pdo->beginTransaction();

            self::$ledger->move($txnId, $walletId, $sink['wallet_id'], $amount, [
                'debit' => "SMS credits purchase",
                'credit1' => "SMS purchase instruction from {$debitWallet['wallet_number']}",
                'debit2' => "SMS purchase, credit sent {$sink['wallet_number']}",
                'credit2' => "SMS credits purchased by {$debitWallet['wallet_number']}"
            ]);

            self::$tx->updateStatus($txnId, 'SUCCESS');

            self::$pdo->commit();

            return ['success' => true, 'transaction_id' => $txnId, 'amount' => $amount];
        } catch (\Throwable $e) {
            self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'SMS purchase failed'];
        }
    }

    public static function buyInStoreChargeInfo(): array
    {
        self::boot();

        $s = self::$fees->buyInStoreChargeSetting();
        if (!$s)
            return ['success' => true, 'fee' => 0.0, 'setting_id' => null];

        return ['success' => true, 'fee' => (float) $s['setting_value'], 'setting_id' => $s['id']];
    }

    public static function chargeBuyInStoreFee(string $userId): array
    {
        self::boot();

        $setting = self::$fees->buyInStoreChargeSetting();
        $fee = $setting ? (float) $setting['setting_value'] : 0.0;

        if ($fee <= 0) {
            $uw = self::$wallets->userWallet($userId);
            $bal = $uw ? (float) $uw['current_balance'] : 0.0;
            return ['success' => true, 'fee_charged' => 0.0, 'transaction_id' => null, 'remaining_balance' => $bal];
        }

        if (!$setting)
            return ['success' => false, 'message' => 'Buy-in-store charge setting not found'];

        $uw = self::$wallets->userWallet($userId);
        if (!$uw)
            return ['success' => false, 'message' => 'No Zzimba Wallet found'];

        if ((float) $uw['current_balance'] < $fee)
            return ['success' => false, 'message' => 'Insufficient wallet balance', 'balance' => (float) $uw['current_balance'], 'fee' => $fee, 'required' => $fee];

        $sink = self::$wallets->destinationForCreditSetting($setting['id']);
        if (!$sink)
            return ['success' => false, 'message' => 'Charge destination wallet not configured'];

        $txnId = Ids::ulid();

        self::$tx->insert([
            'transaction_id' => $txnId,
            'transaction_type' => 'CHARGES',
            'status' => 'PENDING',
            'amount_total' => $fee,
            'payment_method' => 'WALLET',
            'wallet_id' => $uw['wallet_id'],
            'user_id' => $userId,
            'note' => 'Buy In Store submission fee'
        ]);

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $uw['wallet_id'], 'wallet_to' => $sink['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

        try {
            self::$pdo->beginTransaction();

            self::$ledger->move($txnId, $uw['wallet_id'], $sink['wallet_id'], $fee, [
                'debit' => "Buy In Store submission fee charged to {$uw['wallet_number']}",
                'credit1' => "BIS fee collection from {$uw['wallet_number']}",
                'debit2' => "BIS fee disbursement to {$sink['wallet_number']}",
                'credit2' => "BIS fee earned from {$uw['wallet_number']}"
            ]);

            self::$tx->updateStatus($txnId, 'SUCCESS');

            self::$pdo->commit();

            $remaining = self::$wallets->balance($uw['wallet_id']);

            return ['success' => true, 'transaction_id' => $txnId, 'fee_charged' => $fee, 'remaining_balance' => $remaining];
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction())
                self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Error processing charge'];
        }
    }

    public static function chargeVendorCommission(array $opts): array
    {
        self::boot();

        $vendorId = trim($opts['vendor_id'] ?? '');
        $amount = (float) ($opts['amount'] ?? 0);
        $context = $opts['context'] ?? [];

        if ($vendorId === '' || $amount <= 0)
            return ['success' => false, 'message' => 'Invalid vendor or amount'];

        $vw = self::$wallets->vendorWallet($vendorId);
        if (!$vw) {
            $res = self::getWallet('VENDOR', $vendorId);
            if (empty($res['success']))
                return ['success' => false, 'message' => 'Vendor wallet not found'];
            $vw = $res['wallet'];
        }

        $ops = self::$wallets->operationsWallet();
        if (!$ops)
            return ['success' => false, 'message' => 'Operations account not configured'];

        $txnId = Ids::ulid();

        self::$tx->insert([
            'transaction_id' => $txnId,
            'transaction_type' => 'COMMISSION',
            'status' => 'PENDING',
            'amount_total' => $amount,
            'payment_method' => 'WALLET',
            'vendor_id' => $vendorId,
            'wallet_id' => $vw['wallet_id'],
            'note' => 'BIS commission charge'
        ]);

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $vw['wallet_id'], 'wallet_to' => $ops['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

        try {
            self::$pdo->beginTransaction();

            $reqId = $context['request_id'] ?? '';
            $prod = $context['product_title'] ?? '';
            $qty = $context['quantity'] ?? null;

            $fromNo = $vw['wallet_number'];
            $toNo = $ops['wallet_number'];

            $debitNote = "BIS commission charge";
            if ($reqId !== '')
                $debitNote .= " for {$reqId}";

            $credit1 = "Commission collection from {$fromNo}";
            $debit2 = "Commission disbursement to {$toNo}";

            $credit2 = "Commission received";
            if ($prod !== '')
                $credit2 .= " for {$prod}";
            if ($qty !== null)
                $credit2 .= " x{$qty}";

            self::$ledger->move($txnId, $vw['wallet_id'], $ops['wallet_id'], $amount, [
                'debit' => $debitNote,
                'credit1' => $credit1,
                'debit2' => $debit2,
                'credit2' => $credit2
            ]);

            self::$tx->updateStatus($txnId, 'SUCCESS');

            self::$pdo->commit();

            $newBal = self::$wallets->balance($vw['wallet_id']);

            $vendorEntity = self::$entities->entityInfo(null, $vendorId);
            $adminMessage = ['message' => "Commission of {$amount} UGX charged to {$vendorEntity['name']} for BIS completion" . ($reqId ? " ({$reqId})" : "") . "."];
            $vendorMessage = ['id' => $vendorId, 'message' => "Commission of {$amount} UGX has been charged on your wallet for Buy In Store completion" . ($reqId ? " ({$reqId})" : "") . "."];

            $rec = self::$bus->recipients($adminMessage, null, $vendorMessage);
            self::$bus->send('BIS Commission Charged', $rec, 'normal', $vendorId);

            return ['success' => true, 'transaction_id' => $txnId, 'amount_charged' => $amount, 'balance' => $newBal];
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction())
                self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Commission charge failed'];
        }
    }

    public static function priceViewChargeInfo(): array
    {
        self::boot();

        $s = self::$fees->priceViewSetting();
        if (!$s)
            return ['success' => true, 'fee' => 0.0, 'setting_id' => null];

        return ['success' => true, 'fee' => (float) $s['setting_value'], 'setting_id' => $s['id']];
    }

    public static function chargePriceView(string $userId, array $context = []): array
    {
        self::boot();

        $setting = self::$fees->priceViewSetting();
        $fee = $setting ? (float) $setting['setting_value'] : 0.0;

        $vendorId = trim($context['vendor_id'] ?? '');
        $productTitle = trim($context['product_title'] ?? '');
        $priceCategory = trim($context['price_category'] ?? '');
        $unitName = trim($context['unit_name'] ?? '');
        $packageSize = trim($context['package_size'] ?? '');
        $storeLink = $context['link'] ?? null;

        $detail = $productTitle !== '' ? $productTitle : 'a product';
        if ($unitName !== '' || $packageSize !== '') {
            $unitDesc = trim(($packageSize !== '' ? $packageSize . ' ' : '') . $unitName);
            if ($unitDesc !== '')
                $detail .= " ({$unitDesc})";
        }
        if ($priceCategory !== '')
            $detail .= " - " . ucfirst($priceCategory);

        if ($fee <= 0) {
            $uw = self::$wallets->userWallet($userId);
            $bal = $uw ? (float) $uw['current_balance'] : 0.0;

            $userEntity = self::$entities->entityInfo($userId, null);
            $storeEntity = $vendorId !== '' ? self::$entities->entityInfo(null, $vendorId) : null;

            $adminMsg = ['message' => "{$userEntity['name']} viewed price for {$detail}" . ($storeEntity ? " at {$storeEntity['name']}" : "") . "."];
            $userMsg = ['id' => $userId, 'message' => "You viewed price for {$detail}" . ($storeEntity ? " at {$storeEntity['name']}" : "") . "."];
            $vendorMsg = $vendorId !== '' ? ['id' => $vendorId, 'message' => "A customer viewed product price: {$detail}."] : null;

            $rec = self::$bus->recipients($adminMsg, $userMsg, $vendorMsg);
            self::$bus->send('Price View', $rec, 'normal', $userId, $storeLink);

            return ['success' => true, 'fee_charged' => 0.0, 'transaction_id' => null, 'remaining_balance' => $bal];
        }

        if (!$setting)
            return ['success' => false, 'message' => 'Price view setting not found'];

        $uw = self::$wallets->userWallet($userId);
        if (!$uw)
            return ['success' => false, 'message' => 'No Zzimba Wallet found'];

        if ((float) $uw['current_balance'] < $fee)
            return ['success' => false, 'message' => 'Insufficient wallet balance', 'balance' => (float) $uw['current_balance'], 'fee' => $fee, 'required' => $fee];

        $sink = self::$wallets->destinationForCreditSetting($setting['id']);
        if (!$sink)
            return ['success' => false, 'message' => 'Charge destination wallet not configured'];

        $txnId = Ids::ulid();

        self::$tx->insert([
            'transaction_id' => $txnId,
            'transaction_type' => 'CHARGES',
            'status' => 'PENDING',
            'amount_total' => $fee,
            'payment_method' => 'WALLET',
            'wallet_id' => $uw['wallet_id'],
            'user_id' => $userId,
            'note' => 'Price view fee'
        ]);

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $uw['wallet_id'], 'wallet_to' => $sink['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

        try {
            self::$pdo->beginTransaction();

            self::$ledger->move($txnId, $uw['wallet_id'], $sink['wallet_id'], $fee, [
                'debit' => "Price view fee charged to {$uw['wallet_number']}",
                'credit1' => "Price view fee collection from {$uw['wallet_number']}",
                'debit2' => "Price view fee disbursement to {$sink['wallet_number']}",
                'credit2' => "Price view fee earned from {$uw['wallet_number']}"
            ]);

            self::$tx->updateStatus($txnId, 'SUCCESS');

            self::$pdo->commit();

            $remaining = self::$wallets->balance($uw['wallet_id']);

            $userEntity = self::$entities->entityInfo($userId, null);
            $storeEntity = $vendorId !== '' ? self::$entities->entityInfo(null, $vendorId) : null;

            $adminMsg = ['message' => "{$userEntity['name']} viewed price for {$detail}" . ($storeEntity ? " at {$storeEntity['name']}" : "") . ". Fee charged: {$fee} UGX."];
            $userMsg = ['id' => $userId, 'message' => "You were charged {$fee} UGX for viewing price for {$detail}" . ($storeEntity ? " at {$storeEntity['name']}" : "") . ". Your new balance is {$remaining} UGX."];
            $vendorMsg = $vendorId !== '' ? ['id' => $vendorId, 'message' => "A customer viewed product price: {$detail}."] : null;

            $rec = self::$bus->recipients($adminMsg, $userMsg, $vendorMsg);
            self::$bus->send('Price View', $rec, 'normal', $userId, $storeLink);

            return ['success' => true, 'transaction_id' => $txnId, 'fee_charged' => $fee, 'remaining_balance' => $remaining];
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction())
                self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Error processing price view charge'];
        }
    }

    public static function contactDetailsViewChargeInfo(): array
    {
        self::boot();

        $s = self::$fees->contactDetailsViewSetting();
        if (!$s)
            return ['success' => true, 'fee' => 0.0, 'setting_id' => null];

        return ['success' => true, 'fee' => (float) $s['setting_value'], 'setting_id' => $s['id']];
    }

    public static function chargeContactDetailsView(string $userId, array $context = []): array
    {
        self::boot();

        $setting = self::$fees->contactDetailsViewSetting();
        $fee = $setting ? (float) $setting['setting_value'] : 0.0;

        $vendorId = trim($context['vendor_id'] ?? '');
        $storeLink = $context['link'] ?? null;
        $contactType = strtolower(trim($context['contact_type'] ?? 'contact'));
        $contactLabel = $contactType === 'phone' ? 'phone number' : ($contactType === 'email' ? 'email address' : ($contactType === 'location' ? 'location' : 'contact'));

        $storeEntity = $vendorId !== '' ? self::$entities->entityInfo(null, $vendorId) : null;
        $userEntity = self::$entities->entityInfo($userId, null);

        if ($fee <= 0) {
            $uw = self::$wallets->userWallet($userId);
            $bal = $uw ? (float) $uw['current_balance'] : 0.0;

            $adminMsg = ['message' => "{$userEntity['name']} viewed {$contactLabel}" . ($storeEntity ? " for {$storeEntity['name']}" : "") . "."];
            $userMsg = ['id' => $userId, 'message' => "You viewed {$contactLabel}" . ($storeEntity ? " for {$storeEntity['name']}" : "") . "."];
            $vendorMsg = $vendorId !== '' ? ['id' => $vendorId, 'message' => "A customer viewed your {$contactLabel}."] : null;

            $rec = self::$bus->recipients($adminMsg, $userMsg, $vendorMsg);
            self::$bus->send('Contact Details Viewed', $rec, 'normal', $userId, $storeLink);

            return ['success' => true, 'fee_charged' => 0.0, 'transaction_id' => null, 'remaining_balance' => $bal];
        }

        if (!$setting)
            return ['success' => false, 'message' => 'Contact details view setting not found'];

        $uw = self::$wallets->userWallet($userId);
        if (!$uw)
            return ['success' => false, 'message' => 'No Zzimba Wallet found'];

        if ((float) $uw['current_balance'] < $fee)
            return ['success' => false, 'message' => 'Insufficient wallet balance', 'balance' => (float) $uw['current_balance'], 'fee' => $fee, 'required' => $fee];

        $sink = self::$wallets->destinationForCreditSetting($setting['id']);
        if (!$sink)
            return ['success' => false, 'message' => 'Charge destination wallet not configured'];

        $txnId = Ids::ulid();

        self::$tx->insert([
            'transaction_id' => $txnId,
            'transaction_type' => 'CHARGES',
            'status' => 'PENDING',
            'amount_total' => $fee,
            'payment_method' => 'WALLET',
            'wallet_id' => $uw['wallet_id'],
            'user_id' => $userId,
            'note' => 'Contact details view fee'
        ]);

        self::$tx->insertTransfer(['id' => Ids::ulid(), 'wallet_from' => $uw['wallet_id'], 'wallet_to' => $sink['wallet_id'], 'transaction_id' => $txnId, 'created_at' => date('Y-m-d H:i:s')]);

        try {
            self::$pdo->beginTransaction();

            self::$ledger->move($txnId, $uw['wallet_id'], $sink['wallet_id'], $fee, [
                'debit' => "Contact details view fee charged to {$uw['wallet_number']}",
                'credit1' => "Contact details view fee collection from {$uw['wallet_number']}",
                'debit2' => "Contact details view fee disbursement to {$sink['wallet_number']}",
                'credit2' => "Contact details view fee earned from {$uw['wallet_number']}"
            ]);

            self::$tx->updateStatus($txnId, 'SUCCESS');

            self::$pdo->commit();

            $remaining = self::$wallets->balance($uw['wallet_id']);

            $adminMsg = ['message' => "{$userEntity['name']} viewed {$contactLabel}" . ($storeEntity ? " for {$storeEntity['name']}" : "") . ". Fee charged: {$fee} UGX."];
            $userMsg = ['id' => $userId, 'message' => "You were charged {$fee} UGX for viewing {$contactLabel}" . ($storeEntity ? " for {$storeEntity['name']}" : "") . ". Your new balance is {$remaining} UGX."];
            $vendorMsg = $vendorId !== '' ? ['id' => $vendorId, 'message' => "A customer viewed your {$contactLabel}."] : null;

            $rec = self::$bus->recipients($adminMsg, $userMsg, $vendorMsg);
            self::$bus->send('Contact Details Viewed', $rec, 'normal', $userId, $storeLink);

            return ['success' => true, 'transaction_id' => $txnId, 'fee_charged' => $fee, 'remaining_balance' => $remaining];
        } catch (\Throwable $e) {
            if (self::$pdo->inTransaction())
                self::$pdo->rollBack();
            self::$tx->updateStatus($txnId, 'FAILED');
            return ['success' => false, 'message' => 'Error processing contact details view charge'];
        }
    }
}
