<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'logTopup':
        handleLogTopup();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

function handleLogTopup()
{
    // ───────────────────────────────────────────────────────── common inputs
    $walletId = trim($_POST['wallet_id'] ?? '');
    $cashAccountId = trim($_POST['cash_account_id'] ?? '');
    $paymentMethod = strtoupper(trim($_POST['payment_method'] ?? ''));
    $amount = (float) ($_POST['amount_total'] ?? 0);
    $externalRef = trim($_POST['external_reference'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // conditional inputs
    $mmPhoneNumber = trim($_POST['mmPhoneNumber'] ?? '');
    $mmDateTime = trim($_POST['mmDateTime'] ?? '');
    $btDepositorName = trim($_POST['btDepositorName'] ?? '');
    $btDateTime = trim($_POST['btDateTime'] ?? '');

    // build payload
    $payload = [
        'wallet_id' => $walletId,
        'cash_account_id' => $cashAccountId,
        'payment_method' => $paymentMethod,
        'amount_total' => $amount,
        'external_reference' => $externalRef,
        'note' => $note,
        'user_id' => $_SESSION['user']['user_id'] ?? null,
        'vendor_id' => $_SESSION['active_store'] ?? null,
    ];

    // method-specific fields
    if ($paymentMethod === 'MOBILE_MONEY') {
        $payload['mmPhoneNumber'] = $mmPhoneNumber;
        $payload['mmDateTime'] = $mmDateTime;
    } elseif ($paymentMethod === 'BANK') {
        $payload['btDepositorName'] = $btDepositorName;
        $payload['btDateTime'] = $btDateTime;
    }

    // ◀︎ NEW: log exactly what we're sending
    error_log('[handleLogTopup] payload: ' . json_encode($payload));

    // delegate to service
    $result = CreditService::logCashTopup($payload);

    // ◀︎ OPTIONAL: also log the service response
    error_log('[handleLogTopup] result: ' . json_encode($result));

    echo json_encode($result);
}
