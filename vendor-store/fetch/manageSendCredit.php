<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'searchWallet':
        handleSearchWallet($pdo);
        break;
    case 'sendCredit':
        sendCredit($pdo);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleSearchWallet(PDO $pdo)
{
    $type = $_POST['type'] ?? '';
    $searchType = $_POST['searchType'] ?? '';
    $searchValue = $_POST['searchValue'] ?? '';

    if (!in_array($type, ['vendor', 'user'], true) || !in_array($searchType, ['id', 'name'], true) || $searchValue === '') {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    try {
        if ($searchType === 'name') {
            $wallets = fetchWalletsByName($pdo, $type, $searchValue);
            if (!empty($wallets)) {
                echo json_encode(['success' => true, 'wallets' => $wallets]);
            } else {
                echo json_encode(['success' => false, 'message' => ucfirst($type) . ' wallets not found']);
            }
        } else {
            $wallet = fetchWalletByNumber($pdo, $type, $searchValue);
            if ($wallet) {
                echo json_encode(['success' => true, 'wallet' => $wallet]);
            } else {
                echo json_encode(['success' => false, 'message' => ucfirst($type) . ' wallet not found']);
            }
        }
    } catch (PDOException $e) {
        error_log('Error searching wallet: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function fetchWalletByNumber(PDO $pdo, string $type, string $number): ?array
{
    $ownerType = $type === 'vendor' ? 'VENDOR' : 'USER';
    $sql = "SELECT wallet_id, wallet_number, wallet_name, current_balance, status, created_at FROM zzimba_wallets WHERE owner_type = :owner AND wallet_number = :value LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner' => $ownerType, ':value' => $number]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchWalletsByName(PDO $pdo, string $type, string $name): array
{
    $ownerType = $type === 'vendor' ? 'VENDOR' : 'USER';
    $sql = "SELECT wallet_id, wallet_number, wallet_name, current_balance, status, created_at FROM zzimba_wallets WHERE owner_type = :owner AND wallet_name = :value ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':owner' => $ownerType, ':value' => $name]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendCredit(PDO $pdo)
{
    $walletTo = trim($_POST['wallet_to'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $result = CreditService::transfer(['wallet_to' => $walletTo, 'amount' => $amount]);
    echo json_encode($result);
}
