<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/ZzimbaCreditModule.php';

use ZzimbaCreditModule\CreditService;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    http_response_code(401);
    die(json_encode(['error' => 'Authentication required']));
}

if ($_SESSION['user']['is_admin']) {
    http_response_code(403);
    die(json_encode(['error' => 'Admin accounts cannot submit quote requests']));
}

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS request_for_quote (
            RFQ_ID VARCHAR(26) PRIMARY KEY,
            user_id VARCHAR(26) NOT NULL,
            site_location VARCHAR(255) NOT NULL,
            coordinates VARCHAR(255) NULL,
            transport DECIMAL(10,2) NULL DEFAULT 0.00,
            fee_charged DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('New','Processing','Cancelled','Processed') NOT NULL DEFAULT 'New',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS request_for_quote_details ( 
            RFQD_ID VARCHAR(26) PRIMARY KEY,
            RFQ_ID VARCHAR(26) NOT NULL,
            brand_name VARCHAR(255) NOT NULL,
            size VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (RFQ_ID) REFERENCES request_for_quote(RFQ_ID) ON DELETE CASCADE
        )"
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Table creation failed: ' . $e->getMessage()]));
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'checkWalletBalance':
            $userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;

            if (!$userId) {
                http_response_code(400);
                die(json_encode(['error' => 'User ID not found in session']));
            }

            $stmt = $pdo->prepare(
                "SELECT current_balance FROM zzimba_wallets 
                 WHERE user_id = :user_id AND status = 'active' 
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([':user_id' => $userId]);
            $balance = $stmt->fetchColumn();

            if ($balance === false) {
                $balance = 0.00;
            }

            $feeStmt = $pdo->prepare(
                "SELECT setting_value FROM zzimba_credit_settings 
                 WHERE setting_key = 'request_for_quote' 
                 AND status = 'active' 
                 AND setting_type = 'flat'
                 AND category = 'quote'
                 AND (applicable_to = 'users' OR applicable_to = 'all')
                 ORDER BY applicable_to DESC LIMIT 1"
            );
            $feeStmt->execute();
            $fee = $feeStmt->fetchColumn();

            if ($fee === false) {
                $fee = 0.00;
            }

            $canSubmit = floatval($balance) >= floatval($fee);

            echo json_encode([
                'success' => true,
                'balance' => floatval($balance),
                'fee' => floatval($fee),
                'canSubmit' => $canSubmit
            ]);
            break;

        case 'submitRFQ':
            $data = json_decode(file_get_contents('php://input'), true);
            if (
                !$data ||
                !isset($data['location'], $data['items']) ||
                !is_array($data['items']) ||
                empty($data['location']) ||
                count($data['items']) === 0 ||
                count($data['items']) > 5
            ) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing or invalid required fields. Maximum 5 items allowed.']));
            }

            $userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;

            if (!$userId) {
                http_response_code(400);
                die(json_encode(['error' => 'User ID not found in session']));
            }

            $feeStmt = $pdo->prepare(
                "SELECT setting_value FROM zzimba_credit_settings 
                 WHERE setting_key = 'request_for_quote' 
                 AND status = 'active' 
                 AND setting_type = 'flat'
                 AND category = 'quote'
                 AND (applicable_to = 'users' OR applicable_to = 'all')
                 ORDER BY applicable_to DESC LIMIT 1"
            );
            $feeStmt->execute();
            $fee = $feeStmt->fetchColumn();

            if ($fee === false) {
                $fee = 0.00;
            }

            $fee = floatval($fee);

            if ($fee > 0) {
                $quoteResult = CreditService::processQuoteRequest([
                    'amount' => $fee,
                    'user_id' => $userId
                ]);

                if (!$quoteResult['success']) {
                    http_response_code(400);
                    die(json_encode([
                        'error' => $quoteResult['message'],
                        'balance' => $quoteResult['balance'] ?? 0,
                        'fee' => $fee,
                        'required' => isset($quoteResult['required']) ? $quoteResult['required'] : $fee
                    ]));
                }
            }

            $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
            $rfqId = generateUlid();
            $location = is_array($data['location']) ? $data['location']['address'] : $data['location'];
            $coordinates = null;

            if (is_array($data['location']) && isset($data['location']['lat'], $data['location']['lng'])) {
                $coordinates = $data['location']['lat'] . ',' . $data['location']['lng'];
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO request_for_quote
                    (RFQ_ID, user_id, site_location, coordinates, transport, fee_charged, status, created_at, updated_at)
                 VALUES
                    (:rfq_id, :user_id, :location, :coordinates, :transport, :fee, 'New', :created_at, :updated_at)"
            );
            $stmt->bindParam(':rfq_id', $rfqId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':coordinates', $coordinates);
            $stmt->bindValue(':transport', null);
            $stmt->bindParam(':fee', $fee);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            $stmt->execute();

            $stmtDetail = $pdo->prepare(
                "INSERT INTO request_for_quote_details
                    (RFQD_ID, RFQ_ID, brand_name, size, quantity, unit_price, created_at, updated_at)
                 VALUES
                    (:rfqd_id, :rfq_id, :brand, :size, :quantity, :unit_price, :created_at, :updated_at)"
            );

            foreach ($data['items'] as $item) {
                if (!isset($item['brand'], $item['size'], $item['quantity'])) {
                    $pdo->rollBack();
                    http_response_code(400);
                    die(json_encode(['error' => 'Missing item fields']));
                }

                $rfqdId = generateUlid();
                $brand = $item['brand'];
                $size = $item['size'];
                $quantity = (int) $item['quantity'];

                $stmtDetail->bindParam(':rfqd_id', $rfqdId);
                $stmtDetail->bindParam(':rfq_id', $rfqId);
                $stmtDetail->bindParam(':brand', $brand);
                $stmtDetail->bindParam(':size', $size);
                $stmtDetail->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmtDetail->bindValue(':unit_price', null);
                $stmtDetail->bindParam(':created_at', $now);
                $stmtDetail->bindParam(':updated_at', $now);
                $stmtDetail->execute();
            }

            $pdo->commit();

            $response = [
                'success' => true,
                'message' => 'RFQ submitted successfully.',
                'fee_charged' => $fee
            ];

            if ($fee > 0 && isset($quoteResult)) {
                $response['remaining_balance'] = $quoteResult['remaining_balance'];
                $response['transaction_id'] = $quoteResult['transaction_id'];
            } else {
                $balanceStmt = $pdo->prepare(
                    "SELECT current_balance FROM zzimba_wallets 
                     WHERE user_id = :user_id AND status = 'active' 
                     ORDER BY created_at DESC LIMIT 1"
                );
                $balanceStmt->execute([':user_id' => $userId]);
                $currentBalance = $balanceStmt->fetchColumn();
                $response['remaining_balance'] = floatval($currentBalance ?: 0);
            }

            echo json_encode($response);
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>