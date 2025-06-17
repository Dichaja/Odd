<?php
// account/fetch/manageZzimbaCredit.php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user']['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

// Support both GET and POST for parameters
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'validateMsisdn':
            $msisdn = trim($_REQUEST['msisdn'] ?? '');
            $result = CreditService::validateMsisdn($msisdn);
            echo json_encode($result);
            break;

        case 'makePayment':
            $opts = [
                'msisdn' => trim($_REQUEST['msisdn'] ?? ''),
                'amount' => (float) ($_REQUEST['amount'] ?? 0),
                'description' => trim($_REQUEST['description'] ?? ''),
                'user_id' => $_SESSION['user']['user_id'],
            ];
            $result = CreditService::makeMobileMoneyPayment($opts);
            echo json_encode($result);
            break;

        case 'checkStatus':
            $internalRef = trim($_REQUEST['internal_reference'] ?? '');
            $result = CreditService::checkRequestStatus($internalRef);
            echo json_encode($result);
            break;

        case 'getWallet':
            $vendorId = $_SESSION['active_store'];
            $result = CreditService::getWallet('VENDOR', $vendorId);
            echo json_encode($result);
            break;

        case 'getWalletStatement':
            $vendorId = $_SESSION['active_store'];
            $filter = strtolower(trim($_REQUEST['filter'] ?? 'all'));
            $start = $_REQUEST['start'] ?? null;
            $end = $_REQUEST['end'] ?? null;

            try {
                // Ensure vendor wallet exists or create it
                $walletResult = CreditService::getWallet('VENDOR', $vendorId);
                if (!$walletResult['success']) {
                    echo json_encode(['success' => false, 'message' => 'Unable to retrieve or create vendor wallet.']);
                    break;
                }

                $walletId = $walletResult['wallet']['wallet_id'];

                // Fetch statement for the wallet
                $result = CreditService::getWalletStatement($walletId, $filter, $start, $end);
                echo json_encode($result);

            } catch (Exception $e) {
                error_log("[getWalletStatement] Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to fetch wallet statement.']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log('Error in manageZzimbaCredit.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

exit;
