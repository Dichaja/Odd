<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'listPending':
        handleListPending();
        break;
    case 'acknowledge':
        handleAcknowledge();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleListPending()
{
    global $pdo;

    $sql = "
        SELECT
            transaction_id,
            amount_total,
            payment_method,
            external_reference,
            external_metadata,
            note,
            user_id,
            vendor_id,
            created_at
        FROM zzimba_financial_transactions
        WHERE transaction_type = 'TOPUP'
          AND payment_method   IN ('BANK','MOBILE_MONEY')
          AND status           = 'PENDING'
        ORDER BY created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    foreach ($rows as $row) {
        // parse metadata JSON
        $meta = json_decode($row['external_metadata'], true) ?: [];
        $cashAccountId = $meta['cash_account_id'] ?? null;

        // fetch cash account name
        $accountName = null;
        if ($cashAccountId) {
            $aStmt = $pdo->prepare("SELECT name FROM zzimba_cash_accounts WHERE id = :id LIMIT 1");
            $aStmt->execute([':id' => $cashAccountId]);
            $accountName = $aStmt->fetchColumn();
        }

        // fetch user or vendor details
        $userInfo = null;
        $vendorInfo = null;
        if (!empty($row['user_id'])) {
            $uStmt = $pdo->prepare("
                SELECT first_name, last_name, email, phone
                  FROM zzimba_users
                 WHERE id = :id
                 LIMIT 1
            ");
            $uStmt->execute([':id' => $row['user_id']]);
            if ($u = $uStmt->fetch(PDO::FETCH_ASSOC)) {
                $userInfo = [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'email' => $u['email'],
                    'phone' => $u['phone'],
                ];
            }
        } elseif (!empty($row['vendor_id'])) {
            $vStmt = $pdo->prepare("
                SELECT name AS vendor_name, business_email, business_phone, contact_person_name
                  FROM vendor_stores
                 WHERE id = :id
                 LIMIT 1
            ");
            $vStmt->execute([':id' => $row['vendor_id']]);
            if ($v = $vStmt->fetch(PDO::FETCH_ASSOC)) {
                $vendorInfo = [
                    'vendor_name' => $v['vendor_name'],
                    'business_email' => $v['business_email'],
                    'business_phone' => $v['business_phone'],
                    'contact_person_name' => $v['contact_person_name'],
                ];
            }
        }

        $results[] = [
            'transaction_id' => $row['transaction_id'],
            'amount_total' => $row['amount_total'],
            'payment_method' => $row['payment_method'],
            'external_reference' => $row['external_reference'],
            'external_metadata' => $meta,
            'note' => $row['note'],
            'cash_account_id' => $cashAccountId,
            'cash_account_name' => $accountName,
            'user_id' => $row['user_id'],
            'vendor_id' => $row['vendor_id'],
            'user' => $userInfo,
            'vendor' => $vendorInfo,
            'created_at' => $row['created_at'],
        ];
    }

    echo json_encode(['success' => true, 'pending' => $results]);
}

function handleAcknowledge()
{
    $transactionId = trim($_POST['transaction_id'] ?? '');
    $status = strtoupper(trim($_POST['status'] ?? ''));

    if (!$transactionId || !in_array($status, ['SUCCESS', 'FAILED'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        return;
    }

    $result = CreditService::acknowledgeCashTopup($transactionId, $status);
    echo json_encode($result);
}
