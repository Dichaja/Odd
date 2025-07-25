<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['region'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$productId = $input['product_id'];
$region = $input['region'];

try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            vs.id,
            vs.name,
            vs.district,
            vs.logo_url
        FROM vendor_stores vs
        INNER JOIN store_categories sc ON vs.id = sc.store_id
        INNER JOIN store_products sp ON sc.id = sp.store_category_id
        INNER JOIN product_pricing pp ON sp.id = pp.store_products_id
        WHERE sp.product_id = ? 
          AND vs.region = ?
          AND vs.status = 'active'
          AND sc.status = 'active'
          AND sp.status = 'active'
        GROUP BY vs.id
        ORDER BY vs.name
    ");

    $stmt->execute([$productId, $region]);
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'vendors' => $vendors,
        'count' => count($vendors)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]);
}
?>