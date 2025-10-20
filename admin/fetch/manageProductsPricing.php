<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
require_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getPricedProducts':
            getPricedProducts($pdo);
            break;
        case 'getProductPricing':
            getProductPricing($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log('Error in manageProductsPricing: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getPricedProducts(PDO $pdo)
{
    $sql = "
        SELECT
            p.id,
            p.title,
            p.status,
            p.featured,
            p.created_at,
            p.updated_at,
            p.category_id,
            c.name AS category_name,
            COUNT(DISTINCT sp.id) AS stores_count,
            COUNT(pp.id) AS pricing_count,
            MIN(pp.price) AS min_price,
            MAX(pp.price) AS max_price,
            MAX(pp.updated_at) AS last_pricing_update
        FROM product_pricing pp
        INNER JOIN store_products sp ON pp.store_products_id = sp.id
        INNER JOIN products p ON sp.product_id = p.id
        LEFT JOIN product_categories c ON p.category_id = c.id
        GROUP BY p.id, p.title, p.status, p.featured, p.created_at, p.updated_at, p.category_id, c.name
        ORDER BY last_pricing_update DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $productIds = array_column($rows, 'id');
    $images = [];
    foreach ($productIds as $pid) {
        $imgs = getProductImages($pid);
        $images[$pid] = $imgs;
    }

    $priceCats = getProductPriceCategoriesMap($pdo, $productIds);
    $vendorNames = getVendorsJoined($pdo, $productIds);

    $out = [];
    foreach ($rows as $r) {
        $pid = $r['id'];
        $r['main_image'] = isset($images[$pid][0]) ? $images[$pid][0] : null;
        $r['price_categories'] = $priceCats[$pid] ?? [];
        $r['vendor_names_join'] = $vendorNames[$pid] ?? '';
        $out[] = $r;
    }

    echo json_encode(['success' => true, 'products' => $out]);
}

function getProductPricing(PDO $pdo)
{
    if (!isset($_GET['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }
    $productId = $_GET['product_id'];

    $pstmt = $pdo->prepare("
        SELECT p.id, p.title, c.name AS category_name
        FROM products p
        LEFT JOIN product_categories c ON p.category_id = c.id
        WHERE p.id = :pid
    ");
    $pstmt->execute([':pid' => $productId]);
    $product = $pstmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT
            pp.id,
            pp.price,
            pp.price_category,
            pp.package_size,
            pp.delivery_capacity,
            pp.commission_type,
            pp.commission_value,
            pp.created_at,
            pp.updated_at,
            sp.id AS store_product_id,
            vs.id AS store_id,
            vs.name AS store_name,
            vs.region,
            vs.district,
            si.si_unit AS si_unit,
            ppn.package_name AS package_name
        FROM product_pricing pp
        INNER JOIN store_products sp ON pp.store_products_id = sp.id
        INNER JOIN store_categories sc ON sp.store_category_id = sc.id
        INNER JOIN vendor_stores vs ON sc.store_id = vs.id
        LEFT JOIN product_si_units si ON pp.si_unit_id = si.id
        LEFT JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
        LEFT JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
        WHERE sp.product_id = :pid
        ORDER BY vs.name ASC, pp.price_category ASC, pp.updated_at DESC
    ");
    $stmt->execute([':pid' => $productId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'product' => $product, 'rows' => $rows]);
}

function getProductImages($uuid)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir))
        return [];
    $json = $dir . '/images.json';
    if (!file_exists($json))
        return [];
    $data = json_decode(file_get_contents($json), true);
    if (empty($data['images']))
        return [];
    $out = [];
    foreach ($data['images'] as $f) {
        $url = filter_var($f, FILTER_VALIDATE_URL) ? $f : BASE_URL . "img/products/$uuid/$f";
        $out[] = $url;
    }
    return $out;
}

function getProductPriceCategoriesMap(PDO $pdo, array $productIds)
{
    if (empty($productIds))
        return [];
    $in = implode(',', array_fill(0, count($productIds), '?'));
    $sql = "
        SELECT sp.product_id, pp.price_category
        FROM product_pricing pp
        INNER JOIN store_products sp ON pp.store_products_id = sp.id
        WHERE sp.product_id IN ($in)
        GROUP BY sp.product_id, pp.price_category
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($productIds);
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pid = $row['product_id'];
        if (!isset($map[$pid]))
            $map[$pid] = [];
        $map[$pid][] = $row['price_category'];
    }
    return $map;
}

function getVendorsJoined(PDO $pdo, array $productIds)
{
    if (empty($productIds))
        return [];
    $in = implode(',', array_fill(0, count($productIds), '?'));
    $sql = "
        SELECT sp.product_id, GROUP_CONCAT(DISTINCT vs.name ORDER BY vs.name SEPARATOR ', ') AS vendor_names
        FROM store_products sp
        INNER JOIN store_categories sc ON sp.store_category_id = sc.id
        INNER JOIN vendor_stores vs ON sc.store_id = vs.id
        WHERE sp.product_id IN ($in)
        GROUP BY sp.product_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($productIds);
    $out = [];
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $out[$r['product_id']] = $r['vendor_names'] ?? '';
    }
    return $out;
}
