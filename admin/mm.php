<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';

// header('Content-Type: application/json');

date_default_timezone_set('Africa/Kampala');

//
// Ensure the financial transactions table exists
//
try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS zzimba_financial_transactions (
            transaction_id      VARCHAR(26) NOT NULL PRIMARY KEY,
            transaction_type    ENUM('TOPUP','PURCHASE','SUBSCRIPTION','SMS_PURCHASE','EMAIL_PURCHASE','PREMIUM_FEATURE','REFUND','WITHDRAWAL') NOT NULL,
            status              ENUM('PENDING','SUCCESS','FAILED','REFUNDED','DISPUTED') NOT NULL DEFAULT 'PENDING',
            amount_total        DECIMAL(15,2) NOT NULL,
            payment_method      ENUM('MOBILE_MONEY_GATEWAY','MOBILE_MONEY','CARD','WALLET') DEFAULT NULL,
            external_reference  VARCHAR(100) DEFAULT NULL,
            external_metadata   TEXT DEFAULT NULL,
            user_id             VARCHAR(26) DEFAULT NULL,
            vendor_id           VARCHAR(26) DEFAULT NULL,
            admin_id            VARCHAR(26) DEFAULT NULL,
            original_txn_id     VARCHAR(26) DEFAULT NULL,
            note                VARCHAR(255) DEFAULT NULL,
            created_at          DATETIME NOT NULL,
            updated_at          DATETIME NOT NULL,

            CONSTRAINT fk_txn_user     FOREIGN KEY (user_id)          REFERENCES zzimba_users(id)                    ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_txn_vendor   FOREIGN KEY (vendor_id)        REFERENCES vendor_stores(id)                  ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_txn_admin    FOREIGN KEY (admin_id)         REFERENCES admin_users(id)                    ON UPDATE CASCADE ON DELETE RESTRICT,
            CONSTRAINT fk_txn_original FOREIGN KEY (original_txn_id)  REFERENCES zzimba_financial_transactions(transaction_id) ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
} catch (PDOException $e) {
    error_log("Table creation error (financial_transactions): " . $e->getMessage());
}

//
// Common variables
//
$API_KEY = "56cded6ede99ac.BYJV1ceTwWbN_NzaqIchUw";
$ACCOUNT_NO = "REL2C6A94761B";
$DEFAULT_CURRENCY = "UGX";

//
// Helper functions
//
function getFriendlyError($rawError)
{
    $decoded = json_decode($rawError, true);
    return ($decoded && isset($decoded['message'])) ? $decoded['message'] : $rawError;
}

function makeApiRequest($url, $data, $method = 'POST')
{
    global $API_KEY;

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
        "Content-Type: application/json",
        "Accept: application/vnd.relworx.v2",
        "Authorization: Bearer $API_KEY"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        $errorDetails = $response ?: $curlError;
        $friendlyError = getFriendlyError($errorDetails);
        return json_encode(["error" => "API request failed: " . $friendlyError]);
    }

    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {

        case 'makeCardPayment':
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 10000.00;
            if ($amount < 2000) {
                echo json_encode(["error" => "Minimum amount for card payment is 2,000 UGX."]);
                exit;
            }
            if ($amount > 5000000) {
                echo json_encode(["error" => "Maximum amount for card payment is 5,000,000 UGX."]);
                exit;
            }

            $reference = generateUlid();
            $data = [
                "account_no" => $ACCOUNT_NO,
                "reference" => $reference,
                "currency" => $DEFAULT_CURRENCY,
                "amount" => $amount,
                "description" => "Payment Request."
            ];

            $response = makeApiRequest(
                "https://payments.relworx.com/api/visa/request-session",
                $data
            );
            $result = json_decode($response, true);

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO zzimba_financial_transactions
                        (transaction_id, transaction_type, status, amount_total, payment_method, external_reference, external_metadata, user_id, created_at, updated_at)
                     VALUES
                        (:transaction_id, :transaction_type, :status, :amount_total, :payment_method, :external_reference, :external_metadata, :user_id, NOW(), NOW())"
                );
                $stmt->execute([
                    ':transaction_id' => $reference,
                    ':transaction_type' => 'TOPUP',
                    ':status' => 'PENDING',
                    ':amount_total' => $amount,
                    ':payment_method' => 'CARD',
                    ':external_reference' => null,
                    ':external_metadata' => $response,
                    ':user_id' => $_SESSION['user']['id'] ?? null,
                ]);
            } catch (PDOException $e) {
                error_log("Card payment log insert error: " . $e->getMessage());
            }

            echo $response;
            break;

        case 'makeMobileMoneyPayment':
            $msisdn = trim($_POST['msisdn'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $description = trim($_POST['description'] ?? "Payment Request.");

            if (empty($msisdn) || $amount <= 0) {
                echo json_encode(["error" => "Mobile number and a valid amount are required."]);
                exit;
            }
            if ($amount < 500) {
                echo json_encode(["error" => "Minimum amount for mobile money payment is 500."]);
                exit;
            }

            $reference = generateUlid();
            $data = [
                "account_no" => $ACCOUNT_NO,
                "reference" => $reference,
                "msisdn" => $msisdn,
                "currency" => $DEFAULT_CURRENCY,
                "amount" => $amount,
                "description" => $description
            ];

            $response = makeApiRequest(
                "https://payments.relworx.com/api/mobile-money/request-payment",
                $data
            );
            $result = json_decode($response, true);
            $externalRef = $result['internal_reference'] ?? $reference;

            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO zzimba_financial_transactions
                        (transaction_id, transaction_type, status, amount_total, payment_method, external_reference, external_metadata, user_id, created_at, updated_at)
                     VALUES
                        (:transaction_id, :transaction_type, :status, :amount_total, :payment_method, :external_reference, :external_metadata, :user_id, NOW(), NOW())"
                );
                $stmt->execute([
                    ':transaction_id' => $reference,
                    ':transaction_type' => 'TOPUP',
                    ':status' => 'PENDING',
                    ':amount_total' => $amount,
                    ':payment_method' => 'MOBILE_MONEY_GATEWAY',
                    ':external_reference' => $externalRef,
                    ':external_metadata' => $response,
                    ':user_id' => $_SESSION['user']['id'] ?? null,
                ]);
            } catch (PDOException $e) {
                error_log("Mobile money log insert error: " . $e->getMessage());
            }

            echo $response;
            break;

        case 'validateMsisdn':
            $msisdn = trim($_POST['msisdn'] ?? '');
            if (empty($msisdn)) {
                echo json_encode(["error" => "Mobile number is required for validation."]);
                exit;
            }
            $response = makeApiRequest(
                "https://payments.relworx.com/api/mobile-money/validate",
                ["msisdn" => $msisdn]
            );
            $result = json_decode($response, true);
            if (isset($result['customer_name'])) {
                $result['customer_name'] = strtoupper($result['customer_name']);
                echo json_encode($result);
            } else {
                echo $response;
            }
            break;

        case 'checkRequestStatus':
            $internal_reference = trim($_POST['internal_reference'] ?? '');
            if (empty($internal_reference)) {
                echo json_encode(["error" => "Internal reference is required for status check."]);
                exit;
            }

            $data = [
                "internal_reference" => $internal_reference,
                "account_no" => $ACCOUNT_NO
            ];

            $response = makeApiRequest(
                "https://payments.relworx.com/api/mobile-money/check-request-status",
                $data,
                'GET'
            );
            $result = json_decode($response, true);

            $newStatus = null;
            if (!empty($result['request_status'])) {
                $newStatus = strtoupper($result['request_status']);
            } elseif (!empty($result['status'])) {
                $newStatus = strtoupper($result['status']);
            }

            try {
                $stmt = $pdo->prepare(
                    "UPDATE zzimba_financial_transactions
                        SET status            = :status,
                            note              = :note,
                            external_metadata = :external_metadata,
                            updated_at        = NOW()
                      WHERE external_reference = :external_reference"
                );
                $stmt->execute([
                    ':status' => $newStatus,
                    ':note' => $result['message'] ?? null,
                    ':external_metadata' => $response,
                    ':external_reference' => $internal_reference,
                ]);
            } catch (PDOException $e) {
                error_log("Request status log update error: " . $e->getMessage());
            }

            echo $response;
            break;

        case 'checkWalletBalance':
            $data = [
                "account_no" => $ACCOUNT_NO,
                "currency" => $DEFAULT_CURRENCY
            ];
            echo makeApiRequest(
                "https://payments.relworx.com/api/mobile-money/check-wallet-balance",
                $data,
                'GET'
            );
            break;

        case 'sendPayment':
            $msisdn = trim($_POST['msisdn'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $description = trim($_POST['description'] ?? "Send Payment.");

            if (empty($msisdn)) {
                echo json_encode(["error" => "Recipient mobile number is required."]);
                exit;
            }
            if ($amount <= 0) {
                echo json_encode(["error" => "Amount must be greater than zero."]);
                exit;
            }

            $reference = generateUlid();
            $data = [
                "account_no" => $ACCOUNT_NO,
                "reference" => $reference,
                "msisdn" => $msisdn,
                "currency" => $DEFAULT_CURRENCY,
                "amount" => $amount,
                "description" => $description
            ];
            echo makeApiRequest(
                "https://payments.relworx.com/api/mobile-money/send-payment",
                $data
            );
            break;

        default:
            echo json_encode(["error" => "Invalid action specified."]);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment API Testing</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        success: {
                            500: '#22c55e',
                            600: '#16a34a',
                        },
                        warning: {
                            500: '#f59e0b',
                        },
                        danger: {
                            500: '#ef4444',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Payment API Testing</h1>
            <p class="text-gray-600 mt-2">Test various payment API endpoints</p>
        </header>

        <!-- Notifications Area -->
        <div id="notifications" class="mb-8"></div>

        <!-- API Endpoints Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <a href="#" class="tab-link inline-block p-4 border-b-2 border-primary-500 rounded-t-lg active"
                        data-tab="wallet">Wallet Balance</a>
                </li>
                <li class="mr-2">
                    <a href="#"
                        class="tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                        data-tab="send">Send Payment</a>
                </li>
                <li class="mr-2">
                    <a href="#"
                        class="tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                        data-tab="card">Card Payment</a>
                </li>
                <li class="mr-2">
                    <a href="#"
                        class="tab-link inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:border-gray-300"
                        data-tab="mobile">Mobile Money</a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Wallet Balance Tab -->
            <div id="wallet-tab" class="tab-pane active">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Check Wallet Balance (UGX)</h2>
                    <form id="walletBalanceForm" class="space-y-4">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Check Balance
                        </button>
                    </form>
                    <div id="balanceResult" class="mt-6 hidden">
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <h3 class="text-lg font-medium text-gray-700 mb-2">Current Balance</h3>
                            <div class="text-3xl font-bold text-primary-600" id="balanceAmount">0.00</div>
                            <div class="text-sm text-gray-500 mt-2">UGX</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send Payment Tab -->
            <div id="send-tab" class="tab-pane hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Send Payment (UGX)</h2>
                    <form id="sendPaymentForm" class="space-y-4">
                        <div>
                            <label for="sendMsisdn" class="block text-sm font-medium text-gray-700 mb-1">Recipient
                                Mobile Number (international format)</label>
                            <input type="text" id="sendMsisdn" name="msisdn"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                placeholder="+256701345672" required>
                            <div id="sendMsisdnValidationResult" class="mt-2"></div>
                        </div>
                        <div>
                            <label for="sendAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                            <input type="number" id="sendAmount" name="amount"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                min="500" step="100" required>
                        </div>
                        <div>
                            <label for="sendDescription"
                                class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" id="sendDescription" name="description"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Send Payment to John Doe.">
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-success-600 text-white font-medium rounded-md hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2">
                            Send Payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card Payment Tab -->
            <div id="card-tab" class="tab-pane hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Card Payment (UGX)</h2>
                    <form id="cardPaymentForm" class="space-y-4">
                        <div>
                            <label for="cardAmount" class="block text-sm font-medium text-gray-700 mb-1">Amount (2,000 -
                                5,000,000 UGX)</label>
                            <input type="number" id="cardAmount" name="amount"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                min="2000" max="5000000" value="10000" step="100">
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Make Card Payment
                        </button>
                    </form>
                    <div id="cardPaymentRedirect" class="mt-6 hidden">
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <div class="mb-4">
                                <div
                                    class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8 text-primary-600 animate-pulse" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800">Redirecting to Payment Page</h3>
                            </div>
                            <p class="text-gray-600 mb-4">You will be redirected in <span id="countdown"
                                    class="font-bold text-primary-600">5</span> seconds.</p>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                                <div id="progressBar"
                                    class="bg-primary-600 h-2.5 rounded-full w-0 transition-all duration-1000"></div>
                            </div>
                            <div class="flex space-x-4">
                                <button id="openPaymentNow"
                                    class="flex-1 px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                    Open Payment Page Now
                                </button>
                                <button id="cancelCardPayment"
                                    class="flex-1 px-4 py-2 bg-gray-600 text-white font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Money Tab -->
            <div id="mobile-tab" class="tab-pane hidden">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Mobile Money Payment (UGX)</h2>
                    <form id="mobileMoneyForm" class="space-y-4">
                        <div>
                            <label for="msisdn" class="block text-sm font-medium text-gray-700 mb-1">Mobile Number
                                (international format)</label>
                            <input type="text" id="msisdn" name="msisdn"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                placeholder="+256701345672" required>
                            <div id="msisdnValidationResult" class="mt-2"></div>
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount (min:
                                500)</label>
                            <input type="number" id="amount" name="amount"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                min="500" step="100" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description
                                (optional)</label>
                            <input type="text" id="description" name="description"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Payment Request.">
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                            Submit Mobile Money Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Response Log -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Response Log</h2>
            <div id="responseLog" class="bg-white rounded-lg shadow-md p-6 h-64 overflow-y-auto font-mono text-sm">
                <div class="text-gray-500">API responses will appear here...</div>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-4 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>© 2025 Payment API Testing. All rights reserved.</p>
        </div>
    </footer>

    <script>
        $(document).ready(function () {
            // Variables
            let paymentUrl = '';
            let paymentWindow = null;
            let pollingIntervalId = null;

            // Tab Navigation
            $('.tab-link').click(function (e) {
                e.preventDefault();
                const tabId = $(this).data('tab');

                // Update active tab link
                $('.tab-link').removeClass('active border-primary-500').addClass('border-transparent');
                $(this).addClass('active border-primary-500').removeClass('border-transparent');

                // Show selected tab content
                $('.tab-pane').addClass('hidden').removeClass('active');
                $(`#${tabId}-tab`).removeClass('hidden').addClass('active');
            });

            // Helper Functions
            function showNotification(type, title, message, reference = null) {
                const colors = {
                    success: 'bg-green-50 border-green-500 text-green-700',
                    error: 'bg-red-50 border-red-500 text-red-700',
                    info: 'bg-blue-50 border-blue-500 text-blue-700',
                    warning: 'bg-yellow-50 border-yellow-400 text-yellow-800'
                };

                const icons = {
                    success: '<svg class="h-6 w-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    error: '<svg class="h-6 w-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    info: '<svg class="h-6 w-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                    warning: '<svg class="h-6 w-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
                };

                let referenceHtml = '';
                if (reference) {
                    referenceHtml = `<p class="text-xs mt-1 font-mono bg-gray-100 p-1 rounded">Reference: ${reference}</p>`;
                }

                // Generate a unique ID for this notification
                const notificationId = 'notification-' + Date.now();

                const html = `
                    <div id="${notificationId}" class="notification ${colors[type]} border-l-4 p-4 rounded-r-lg mb-4 flex items-start transition-opacity duration-500 opacity-100">
                        ${icons[type]}
                        <div>
                            <p class="font-medium">${title}</p>
                            <p class="text-sm">${message}</p>
                            ${referenceHtml}
                        </div>
                    </div>
                `;

                $('#notifications').append(html);

                // Scroll to notification
                $('html, body').animate({
                    scrollTop: $('#notifications').offset().top - 20
                }, 300);

                // Auto-remove notification after 10 seconds
                setTimeout(function () {
                    $(`#${notificationId}`).addClass('opacity-0');
                    setTimeout(function () {
                        $(`#${notificationId}`).remove();
                    }, 500);
                }, 10000);
            }

            function logResponse(action, data) {
                const timestamp = new Date().toLocaleTimeString();
                const formattedData = typeof data === 'string' ? data : JSON.stringify(data, null, 2);

                $('#responseLog').prepend(`
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-semibold">${action}</span>
                            <span class="text-xs text-gray-500">${timestamp}</span>
                        </div>
                        <pre class="whitespace-pre-wrap break-words">${formattedData}</pre>
                    </div>
                `);
            }

            function setButtonLoading(button, isLoading, originalText) {
                if (isLoading) {
                    button.addClass('opacity-75 cursor-wait').prop('disabled', true);
                    button.html('<svg class="animate-spin -ml-1 mr-2 h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...');
                } else {
                    button.removeClass('opacity-75 cursor-wait').prop('disabled', false);
                    button.html(originalText);
                }
            }

            function makeApiRequest(action, data, successCallback, errorCallback) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: { action: action, ...data },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            logResponse(action, result);

                            if (result.error) {
                                showNotification('error', 'Error', result.error);
                                if (errorCallback) errorCallback(result);
                            } else {
                                if (successCallback) successCallback(result);
                            }
                        } catch (e) {
                            logResponse(action, { error: 'Failed to parse response', raw: response });
                            showNotification('error', 'Error', 'Unexpected error occurred.');
                            if (errorCallback) errorCallback({ error: 'Failed to parse response' });
                        }
                    },
                    error: function () {
                        logResponse(action, { error: 'Request failed' });
                        showNotification('error', 'Error', 'Failed to communicate with the server.');
                        if (errorCallback) errorCallback({ error: 'Request failed' });
                    }
                });
            }

            function startPollingStatus(internalReference) {
                // Create status indicator
                showNotification('info', 'Checking payment status...', 'Please wait while we confirm your payment.');

                pollingIntervalId = setInterval(function () {
                    makeApiRequest('checkRequestStatus', { internal_reference: internalReference },
                        function (data) {
                            if (data.request_status === "success") {
                                clearInterval(pollingIntervalId);
                                showNotification('success', 'Payment Completed!', data.message || 'Transaction was successful.');
                            } else if (data.request_status === "failed") {
                                clearInterval(pollingIntervalId);
                                showNotification('error', 'Payment Failed!', data.message || 'Transaction failed.');
                            }
                            // For pending status, we just continue polling
                        },
                        function () {
                            clearInterval(pollingIntervalId);
                            showNotification('error', 'Status Check Failed', 'Could not verify payment status.');
                        }
                    );
                }, 5000);
            }

            function startCountdown() {
                let count = 5;
                let width = 0;

                // Update progress bar
                function updateProgress() {
                    width += 20;
                    $('#progressBar').css('width', width + '%');
                }

                // Initial progress
                updateProgress();

                // Start countdown
                const timer = setInterval(function () {
                    count--;
                    $('#countdown').text(count);
                    updateProgress();

                    if (count <= 0) {
                        clearInterval(timer);
                        openPaymentWindow();
                    }
                }, 1000);
            }

            function openPaymentWindow() {
                if (paymentUrl) {
                    // Open in a new window
                    paymentWindow = window.open(paymentUrl, '_blank', 'width=800,height=700');

                    // If popup is blocked, alert the user
                    if (!paymentWindow || paymentWindow.closed || typeof paymentWindow.closed == 'undefined') {
                        showNotification('warning', 'Popup Blocked!', 'Please allow popups for this website and click "Open Payment Page Now" button.');
                    }
                }
            }

            // Form Submissions

            // Wallet Balance Form
            $('#walletBalanceForm').submit(function (e) {
                e.preventDefault();
                const submitBtn = $(this).find('button[type="submit"]');

                setButtonLoading(submitBtn, true);

                makeApiRequest('checkWalletBalance', {},
                    function (data) {
                        if (data.success) {
                            // Show balance result
                            $('#balanceResult').removeClass('hidden');
                            $('#balanceAmount').text(parseFloat(data.balance).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));

                            showNotification('success', 'Success!', 'Wallet balance retrieved successfully.');
                        }
                        setButtonLoading(submitBtn, false, 'Check Balance');
                    },
                    function () {
                        setButtonLoading(submitBtn, false, 'Check Balance');
                    }
                );
            });

            // Send Payment Form
            $('#sendPaymentForm').submit(function (e) {
                e.preventDefault();

                const msisdn = $('#sendMsisdn').val().trim();
                const amount = parseFloat($('#sendAmount').val());
                const description = $('#sendDescription').val().trim() || "Send Payment.";
                const submitBtn = $(this).find('button[type="submit"]');

                setButtonLoading(submitBtn, true);

                makeApiRequest('sendPayment', {
                    msisdn: msisdn,
                    amount: amount,
                    description: description
                },
                    function (data) {
                        if (data.success) {
                            showNotification('success', 'Success!', data.message || 'Payment sent successfully.', data.internal_reference);
                            $('#sendPaymentForm')[0].reset();

                            // Start polling for payment status
                            if (data.internal_reference) {
                                startPollingStatus(data.internal_reference);
                            }
                        }
                        setButtonLoading(submitBtn, false, 'Send Payment');
                    },
                    function () {
                        setButtonLoading(submitBtn, false, 'Send Payment');
                    });
            });

            // Card Payment Form
            $('#cardPaymentForm').submit(function (e) {
                e.preventDefault();

                const amount = parseFloat($('#cardAmount').val());
                const submitBtn = $(this).find('button[type="submit"]');

                setButtonLoading(submitBtn, true);

                makeApiRequest('makeCardPayment', { amount: amount },
                    function (data) {
                        if (data.success && data.payment_url) {
                            showNotification('success', 'Success!', data.message || 'Card payment session created.');

                            // Store the payment URL
                            paymentUrl = data.payment_url;

                            // Show the redirect info
                            $('#cardPaymentRedirect').removeClass('hidden');

                            // Start countdown and progress bar
                            startCountdown();
                        }
                        setButtonLoading(submitBtn, false, 'Make Card Payment');
                    },
                    function () {
                        setButtonLoading(submitBtn, false, 'Make Card Payment');
                    }
                );
            });

            // Mobile Money Form
            $('#mobileMoneyForm').submit(function (e) {
                e.preventDefault();

                const submitBtn = $(this).find('button[type="submit"]');
                setButtonLoading(submitBtn, true);

                const formData = {
                    msisdn: $('#msisdn').val().trim(),
                    amount: parseFloat($('#amount').val()),
                    description: $('#description').val().trim() || "Payment Request."
                };

                makeApiRequest('makeMobileMoneyPayment', formData,
                    function (data) {
                        if (data.success) {
                            showNotification('success', 'Success!', data.message || 'Mobile money payment initiated.', data.internal_reference);

                            // Start polling for payment status
                            if (data.internal_reference) {
                                startPollingStatus(data.internal_reference);
                            }

                            // Reset form
                            $('#mobileMoneyForm')[0].reset();
                        }
                        setButtonLoading(submitBtn, false, 'Submit Mobile Money Payment');
                    },
                    function () {
                        setButtonLoading(submitBtn, false, 'Submit Mobile Money Payment');
                    }
                );
            });

            // Mobile Number Validation
            function validateMobileNumber(inputField, resultContainer) {
                const msisdn = $(inputField).val().trim();
                if (msisdn === "") return;

                // Show loading indicator
                $(resultContainer).html(`
                    <div class="flex items-center text-gray-500 text-sm">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Validating mobile number...
                    </div>
                `);

                makeApiRequest('validateMsisdn', { msisdn: msisdn },
                    function (data) {
                        if (data.customer_name) {
                            $(resultContainer).html(`
                                <div class="flex items-center text-green-600 bg-green-50 p-2 rounded-md">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="font-medium">${data.customer_name}</span>
                                </div>
                            `);

                            // Auto-fill description if it's the send payment form
                            if (inputField === '#sendMsisdn' && !$('#sendDescription').val()) {
                                $('#sendDescription').val(`Send Payment to ${data.customer_name}.`);
                            }
                        }
                    },
                    function (error) {
                        $(resultContainer).html(`
                            <div class="flex items-center text-red-600 bg-red-50 p-2 rounded-md">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>${error.error || 'Failed to validate mobile number'}</span>
                            </div>
                        `);
                    }
                );
            }

            // Validate mobile numbers on blur
            $('#msisdn').blur(function () {
                validateMobileNumber('#msisdn', '#msisdnValidationResult');
            });

            $('#sendMsisdn').blur(function () {
                validateMobileNumber('#sendMsisdn', '#sendMsisdnValidationResult');
            });

            // Card Payment Buttons
            $('#openPaymentNow').click(function () {
                openPaymentWindow();
            });

            $('#cancelCardPayment').click(function () {
                // Close the payment window if it's open
                if (paymentWindow && !paymentWindow.closed) {
                    paymentWindow.close();
                }

                // Clear the payment URL
                paymentUrl = '';

                // Hide the redirect info
                $('#cardPaymentRedirect').addClass('hidden');

                // Reset progress bar
                $('#progressBar').css('width', '0%');
                $('#countdown').text('5');
            });
        });
    </script>
</body>

</html>