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
        case 'getVendorProductPricing':
            getVendorProductPricing($pdo);
            break;
        case 'getPackageNamesForProduct':
            getPackageNamesForProduct($pdo);
            break;
        case 'getSIUnits':
            getSIUnits($pdo);
            break;
        case 'saveVendorPricings':
            saveVendorPricings($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log('manageProductsPricing error: ' . $e->getMessage());
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
        WHERE pp.status <> 'deleted'
        GROUP BY p.id, p.title, p.status, p.featured, p.created_at, p.updated_at, p.category_id, c.name
        ORDER BY last_pricing_update DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $productIds = array_column($rows, 'id');
    $images = [];
    foreach ($productIds as $pid) {
        $images[$pid] = getProductImages($pid);
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
            pp.status,
            sp.id AS store_product_id,
            vs.id AS store_id,
            vs.name AS store_name,
            vs.region,
            vs.district,
            si.si_unit AS si_unit,
            ppn.package_name AS package_name,
            pp.updated_at
        FROM product_pricing pp
        INNER JOIN store_products sp ON pp.store_products_id = sp.id
        INNER JOIN store_categories sc ON sp.store_category_id = sc.id
        INNER JOIN vendor_stores vs ON sc.store_id = vs.id
        LEFT JOIN product_si_units si ON pp.si_unit_id = si.id
        LEFT JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
        LEFT JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
        WHERE sp.product_id = :pid
        ORDER BY vs.name ASC, pp.updated_at DESC, pp.id ASC
    ");
    $stmt->execute([':pid' => $productId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'product' => $product, 'rows' => $rows]);
}

function getVendorProductPricing(PDO $pdo)
{
    $productId = $_GET['product_id'] ?? '';
    $storeId = $_GET['store_id'] ?? '';

    if (!$productId || !$storeId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }

    $sp = $pdo->prepare("
        SELECT sp.id
        FROM store_products sp
        INNER JOIN store_categories sc ON sp.store_category_id = sc.id
        WHERE sp.product_id = :pid AND sc.store_id = :sid
        LIMIT 1
    ");
    $sp->execute([':pid' => $productId, ':sid' => $storeId]);
    $spRow = $sp->fetch(PDO::FETCH_ASSOC);

    if (!$spRow) {
        echo json_encode(['success' => true, 'items' => []]);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT
            pp.id AS pricing_id,
            pp.package_mapping_id,
            pp.si_unit_id,
            pp.package_size,
            pp.price_category,
            pp.price,
            pp.delivery_capacity,
            pp.commission_type,
            pp.commission_value,
            pp.status,
            si.si_unit,
            ppn.package_name
        FROM product_pricing pp
        LEFT JOIN product_si_units si ON pp.si_unit_id = si.id
        LEFT JOIN product_package_name_mappings ppm ON pp.package_mapping_id = ppm.id
        LEFT JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
        WHERE pp.store_products_id = :spid
        ORDER BY pp.updated_at DESC, pp.id ASC
    ");
    $stmt->execute([':spid' => $spRow['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $rows]);
}

function getPackageNamesForProduct(PDO $pdo)
{
    $productId = $_GET['product_id'] ?? '';

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT ppm.id, ppn.package_name
        FROM product_package_name_mappings ppm
        INNER JOIN product_package_name ppn ON ppm.product_package_name_id = ppn.id
        WHERE ppm.product_id = :pid
        ORDER BY ppn.package_name ASC
    ");
    $stmt->execute([':pid' => $productId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'mappings' => $rows]);
}

function getSIUnits(PDO $pdo)
{
    $stmt = $pdo->query("
        SELECT id, si_unit
        FROM product_si_units
        ORDER BY si_unit ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'siUnits' => $rows]);
}

function saveVendorPricings(PDO $pdo)
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    $productId = $data['product_id'] ?? '';
    $storeId = $data['store_id'] ?? '';
    $items = $data['line_items'] ?? [];

    if (!$productId || !$storeId || !is_array($items)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid payload']);
        return;
    }

    $sp = $pdo->prepare("
        SELECT sp.id
        FROM store_products sp
        INNER JOIN store_categories sc ON sp.store_category_id = sc.id
        WHERE sp.product_id = :pid AND sc.store_id = :sid
        LIMIT 1
    ");
    $sp->execute([':pid' => $productId, ':sid' => $storeId]);
    $spRow = $sp->fetch(PDO::FETCH_ASSOC);

    if (!$spRow) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Store is not selling this product']);
        return;
    }

    $spid = $spRow['id'];

    $pdo->beginTransaction();

    try {
        foreach ($items as $it) {
            $pricingId = $it['pricing_id'] ?? null;
            $packageMappingId = $it['package_mapping_id'] ?? null;
            $siUnitId = $it['si_unit_id'] ?? null;
            $packageSize = $it['package_size'] ?? '';
            $priceCategory = $it['price_category'] ?? '';
            $price = isset($it['price']) ? floatval($it['price']) : 0;
            $deliveryCapacity = array_key_exists('delivery_capacity', $it) && $it['delivery_capacity'] !== '' ? intval($it['delivery_capacity']) : null;
            $commissionType = $it['commission_type'] ?? 'percentage';
            $commissionValue = isset($it['commission_value']) ? floatval($it['commission_value']) : 1;
            $status = $it['status'] ?? 'active';
            $allowedStatuses = ['active', 'inactive', 'suspended', 'deleted'];
            if (!in_array(strtolower($status), $allowedStatuses, true)) {
                $status = 'active';
            }

            if ($pricingId) {
                $u = $pdo->prepare("
                    UPDATE product_pricing
                    SET package_mapping_id = :pm,
                        si_unit_id = :su,
                        package_size = :psz,
                        price_category = :pcat,
                        price = :prc,
                        delivery_capacity = :cap,
                        commission_type = :ct,
                        commission_value = :cv,
                        status = :st,
                        updated_at = NOW()
                    WHERE id = :id AND store_products_id = :spid
                ");
                $u->execute([
                    ':pm' => $packageMappingId,
                    ':su' => $siUnitId,
                    ':psz' => $packageSize,
                    ':pcat' => $priceCategory,
                    ':prc' => $price,
                    ':cap' => $deliveryCapacity,
                    ':ct' => $commissionType,
                    ':cv' => $commissionValue,
                    ':st' => $status,
                    ':id' => $pricingId,
                    ':spid' => $spid
                ]);
            } else {
                continue;
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getProductImages($uuid)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir)) {
        return [];
    }
    $json = $dir . '/images.json';
    if (!file_exists($json)) {
        return [];
    }
    $data = json_decode(file_get_contents($json), true);
    if (empty($data['images'])) {
        return [];
    }
    $out = [];
    foreach ($data['images'] as $f) {
        $url = filter_var($f, FILTER_VALIDATE_URL) ? $f : BASE_URL . "img/products/$uuid/$f";
        $out[] = $url;
    }
    return $out;
}

function getProductPriceCategoriesMap(PDO $pdo, array $productIds)
{
    if (empty($productIds)) {
        return [];
    }
    $in = implode(',', array_fill(0, count($productIds), '?'));
    $sql = "
        SELECT sp.product_id, pp.price_category
        FROM product_pricing pp
        INNER JOIN store_products sp ON pp.store_products_id = sp.id
        WHERE sp.product_id IN ($in) AND pp.status <> 'deleted'
        GROUP BY sp.product_id, pp.price_category
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($productIds);
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pid = $row['product_id'];
        if (!isset($map[$pid])) {
            $map[$pid] = [];
        }
        $map[$pid][] = $row['price_category'];
    }
    return $map;
}

function getVendorsJoined(PDO $pdo, array $productIds)
{
    if (empty($productIds)) {
        return [];
    }
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
