<?php
require_once __DIR__ . '/../config/config.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS request_for_quote (
        RFQ_ID BINARY(16) PRIMARY KEY,
        company_name VARCHAR(255),
        contact_person VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        site_location VARCHAR(255) NOT NULL,
        status ENUM('New','Processing','Cancelled','Processed') NOT NULL DEFAULT 'New',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS request_for_quote_details (
        RFQD_ID BINARY(16) PRIMARY KEY,
        RFQ_ID BINARY(16) NOT NULL,
        brand_name VARCHAR(255) NOT NULL,
        size VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (RFQ_ID) REFERENCES request_for_quote(RFQ_ID) ON DELETE CASCADE
    )");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Table creation failed: ' . $e->getMessage()]));
}

$action = $_GET['action'] ?? '';

function generateUuidV7()
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x70);
    $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
    return $bytes;
}

try {
    switch ($action) {
        case 'submitRFQ':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid JSON input']));
            }
            if (!isset($data['contact']) || !isset($data['email']) || !isset($data['phone']) || !isset($data['location']) || !isset($data['items']) || !is_array($data['items'])) {
                http_response_code(400);
                die(json_encode(['error' => 'Missing required fields']));
            }
            if (empty($data['contact']) || empty($data['email']) || empty($data['phone']) || empty($data['location']) || count($data['items']) === 0) {
                http_response_code(400);
                die(json_encode(['error' => 'One or more required fields are empty']));
            }
            $now = (new DateTime('now', new DateTimeZone('+03:00')))->format('Y-m-d H:i:s');
            $rfqId = generateUuidV7();
            $company = isset($data['company']) ? $data['company'] : null;
            $contact = $data['contact'];
            $email = $data['email'];
            $phone = $data['phone'];
            $location = $data['location'];

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO request_for_quote (RFQ_ID, company_name, contact_person, email, phone, site_location, status, created_at, updated_at) VALUES (:rfq_id, :company, :contact, :email, :phone, :location, 'New', :created_at, :updated_at)");
            $stmt->bindParam(':rfq_id', $rfqId, PDO::PARAM_LOB);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            $stmt->execute();

            $stmtDetail = $pdo->prepare("INSERT INTO request_for_quote_details (RFQD_ID, RFQ_ID, brand_name, size, quantity, created_at, updated_at) VALUES (:rfqd_id, :rfq_id, :brand, :size, :quantity, :created_at, :updated_at)");
            foreach ($data['items'] as $item) {
                if (!isset($item['brand']) || !isset($item['size']) || !isset($item['quantity'])) {
                    $pdo->rollBack();
                    http_response_code(400);
                    die(json_encode(['error' => 'Missing item fields']));
                }
                $rfqdId = generateUuidV7();
                $brand = $item['brand'];
                $size = $item['size'];
                $quantity = intval($item['quantity']);
                $stmtDetail->bindParam(':rfqd_id', $rfqdId, PDO::PARAM_LOB);
                $stmtDetail->bindParam(':rfq_id', $rfqId, PDO::PARAM_LOB);
                $stmtDetail->bindParam(':brand', $brand);
                $stmtDetail->bindParam(':size', $size);
                $stmtDetail->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stmtDetail->bindParam(':created_at', $now);
                $stmtDetail->bindParam(':updated_at', $now);
                $stmtDetail->execute();
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'RFQ submitted successfully.']);
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
