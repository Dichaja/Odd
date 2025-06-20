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
    // common inputs
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

    // base payload
    $payload = [
        'cash_account_id' => $cashAccountId,
        'payment_method' => $paymentMethod,
        'amount_total' => $amount,
        'external_reference' => $externalRef,
        'note' => $note,
        'user_id' => $_SESSION['user']['user_id'] ?? null,
        'vendor_id' => $_SESSION['active_store'] ?? null,
    ];

    // add method-specific fields
    if ($paymentMethod === 'MOBILE_MONEY') {
        $payload['mmPhoneNumber'] = $mmPhoneNumber;
        $payload['mmDateTime'] = $mmDateTime;
    } elseif ($paymentMethod === 'BANK') {
        $payload['btDepositorName'] = $btDepositorName;
        $payload['btDateTime'] = $btDateTime;
    }

    // delegate to service
    $result = CreditService::logCashTopup($payload);
    echo json_encode($result);
}
