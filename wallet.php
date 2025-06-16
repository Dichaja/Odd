<?php
// populate_wallet_numbers.php
// Run: php populate_wallet_numbers.php

// 1) Bootstrap your existing config (must expose $pdo + generateUlid())
require_once __DIR__ . '/config/config.php';

if (!($pdo instanceof PDO)) {
    echo "❌ \$pdo is not configured correctly in config.php\n";
    exit(1);
}

/**
 * Generate a 10-digit wallet number based on
 * created_at timestamp + a 6-digit random sequence.
 */
function generateWalletNumber(PDO $pdo, string $walletId, string $createdAt): string
{
    // derive yy & mm from created_at
    $ts   = strtotime($createdAt);
    $yy   = date('y', $ts);     // e.g. "26"
    $mm   = date('m', $ts);     // e.g. "11"

    $y1   = $yy[0];             // first digit of year
    $y2   = $yy[1];             // second digit of year
    $m1   = $mm[0];             // first digit of month
    $m2   = $mm[1];             // second digit of month

    do {
        // 6-digit random sequence
        $seq = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // assemble pattern: Y₁ S₀ Y₂ S₁ S₂ S₃ S₄ M₁ S₅ M₂
        $walletNumber =
              $y1      // pos1
            . $seq[0]  // pos2
            . $y2      // pos3
            . $seq[1]  // pos4
            . $seq[2]  // pos5
            . $seq[3]  // pos6
            . $seq[4]  // pos7
            . $m1      // pos8
            . $seq[5]  // pos9
            . $m2;     // pos10

        // check uniqueness
        $stmt = $pdo->prepare(
            'SELECT 1 FROM zzimba_wallets WHERE wallet_number = ? LIMIT 1'
        );
        $stmt->execute([$walletNumber]);
        $exists = (bool) $stmt->fetchColumn();

    } while ($exists);

    return $walletNumber;
}

// 2) Fetch all wallets missing a wallet_number
$sql = "
    SELECT wallet_id, created_at
      FROM zzimba_wallets
     WHERE wallet_number IS NULL
        OR wallet_number = ''
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "✅ All wallets already have a wallet_number.\n";
    exit(0);
}

echo "Updating " . count($rows) . " wallets...\n";

// 3) Loop & update
$update = $pdo->prepare("
    UPDATE zzimba_wallets
       SET wallet_number = :wn
     WHERE wallet_id     = :wid
");

foreach ($rows as $r) {
    $wid = $r['wallet_id'];
    $ca  = $r['created_at'];

    $wn = generateWalletNumber($pdo, $wid, $ca);
    $update->execute([
        ':wn'  => $wn,
        ':wid' => $wid
    ]);

    echo "  ✔ {$wid} → {$wn}\n";
}

echo "Done.\n";
