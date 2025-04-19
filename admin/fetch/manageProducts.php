<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (
    !isset($_SESSION['user']) ||
    !$_SESSION['user']['logged_in'] ||
    !$_SESSION['user']['is_admin']
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS products (
            id VARCHAR(26) PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            category_id VARCHAR(26) NOT NULL,
            description TEXT,
            meta_title VARCHAR(100),
            meta_description TEXT,
            meta_keywords VARCHAR(255),
            status ENUM('published','pending','draft') NOT NULL DEFAULT 'published',
            featured BOOLEAN NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
        )"
    );
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getProducts':
            getProducts($pdo);
            break;

        case 'getProduct':
            getProduct($pdo);
            break;

        case 'createProduct':
            createProduct($pdo);
            break;

        case 'updateProduct':
            updateProduct($pdo);
            break;

        case 'deleteProduct':
            deleteProduct($pdo);
            break;

        case 'uploadImage':
            uploadImage();
            break;

        case 'toggleFeatured':
            toggleFeatured($pdo);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in manageProducts: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getProducts(PDO $pdo)
{
    $stmt = $pdo->prepare(
        "SELECT
             p.id, p.title, p.category_id, p.description,
             p.meta_title, p.meta_description, p.meta_keywords,
             p.status, p.featured,
             p.created_at, p.updated_at,
             c.name AS category_name
         FROM products p
         LEFT JOIN product_categories c ON p.category_id = c.id
         ORDER BY p.created_at DESC"
    );
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$prod) {
        $prod['id']       = $prod['id'];
        unset($prod['id']);

        $prod['category'] = $prod['category_id'];
        unset($prod['category_id']);

        $prod['images']        = getProductImages($prod['id']);
        $prod['category_name'] = $prod['category_name'] ?? '(Unknown)';
    }

    echo json_encode(['success' => true, 'products' => $products]);
}

function getProduct(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $id = $_GET['id'];

    $stmt = $pdo->prepare(
        "SELECT
             p.id, p.title, p.category_id, p.description,
             p.meta_title, p.meta_description, p.meta_keywords,
             p.status, p.featured,
             p.created_at, p.updated_at,
             c.name AS category_name
         FROM products p
         LEFT JOIN product_categories c ON p.category_id = c.id
         WHERE p.id = :id"
    );
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    $product['id']       = $product['id'];
    unset($product['id']);

    $product['category'] = $product['category_id'];
    unset($product['category_id']);

    $product['images']        = getProductImages($product['id']);

    echo json_encode(['success' => true, 'data' => $product]);
}

function createProduct(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['title']) || empty($data['category_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
        return;
    }

    $title        = trim($data['title']);
    $categoryId   = trim($data['category_id']);
    $description  = $data['description']      ?? '';
    $metaTitle    = $data['meta_title']       ?? '';
    $metaDesc     = $data['meta_description'] ?? '';
    $metaKeywords = $data['meta_keywords']    ?? '';
    $status       = $data['status']           ?? 'published';
    $featured     = !empty($data['featured']) ? 1 : 0;

    $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat");
    $stmt->execute([':cat' => $categoryId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
        return;
    }

    $productId = generateUlid();
    $now       = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $insert = $pdo->prepare(
        "INSERT INTO products (
             id, title, category_id, description,
             meta_title, meta_description, meta_keywords,
             status, featured,
             created_at, updated_at
         ) VALUES (
             :id, :title, :cat, :description,
             :meta_title, :meta_description, :meta_keywords,
             :status, :featured,
             :created_at, :updated_at
         )"
    );
    $insert->execute([
        ':id'              => $productId,
        ':title'           => $title,
        ':cat'             => $categoryId,
        ':description'     => $description,
        ':meta_title'      => $metaTitle,
        ':meta_description' => $metaDesc,
        ':meta_keywords'   => $metaKeywords,
        ':status'          => $status,
        ':featured'        => $featured,
        ':created_at'      => $now,
        ':updated_at'      => $now
    ]);

    if (!empty($data['temp_images']) && is_array($data['temp_images'])) {
        moveProductImages($productId, $data['temp_images']);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Product created successfully',
        'id'      => $productId
    ]);
}

function updateProduct(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $id = $data['id'];

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    if (empty($data['title']) || empty($data['category_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
        return;
    }

    $title       = trim($data['title']);
    $categoryId  = trim($data['category_id']);
    $description = $data['description']      ?? '';
    $metaTitle   = $data['meta_title']       ?? '';
    $metaDesc    = $data['meta_description'] ?? '';
    $metaKeywords = $data['meta_keywords']    ?? '';
    $status      = $data['status']           ?? 'published';
    $featured    = !empty($data['featured']) ? 1 : 0;

    $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat");
    $stmt->execute([':cat' => $categoryId]);
    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        "UPDATE products SET
             title = :title,
             category_id = :cat,
             description = :description,
             meta_title = :meta_title,
             meta_description = :meta_description,
             meta_keywords = :meta_keywords,
             status = :status,
             featured = :featured,
             updated_at = :updated_at
         WHERE id = :id"
    );
    $stmt->execute([
        ':title'           => $title,
        ':cat'             => $categoryId,
        ':description'     => $description,
        ':meta_title'      => $metaTitle,
        ':meta_description' => $metaDesc,
        ':meta_keywords'   => $metaKeywords,
        ':status'          => $status,
        ':featured'        => $featured,
        ':updated_at'      => $now,
        ':id'              => $id
    ]);

    if (!empty($data['update_images'])) {
        $existing = $data['existing_images'] ?? [];
        $temp     = $data['temp_images']     ?? [];

        deleteAllProductImages($id, false);

        if (!empty($existing)) {
            saveExistingImages($id, $existing);
        }
        if (!empty($temp)) {
            moveProductImages($id, $temp);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
}

function deleteProduct(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $id = $data['id'];

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    $pdo->prepare("DELETE FROM products WHERE id = :id")
        ->execute([':id' => $id]);

    deleteAllProductImages($id, true);

    echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
}

function toggleFeatured(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $id       = $data['id'];
    $featured = !empty($data['featured']) ? 1 : 0;

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->prepare(
        "UPDATE products
         SET featured = :featured, updated_at = :updated_at
         WHERE id = :id"
    )->execute([
        ':featured'   => $featured,
        ':updated_at' => $now,
        ':id'         => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => $featured
            ? 'Product marked as featured'
            : 'Product removed from featured'
    ]);
}

function uploadImage()
{
    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] !== UPLOAD_ERR_OK
    ) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
        return;
    }

    $file     = $_FILES['image'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $size     = $file['size'];

    if (!in_array($ext, $allowed) || $size > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file or too large']);
        return;
    }

    $tempDir   = __DIR__ . '/../../uploads/temp/';
    if (!file_exists($tempDir)) mkdir($tempDir, 0755, true);

    $newName   = uniqid('temp_') . ".$ext";
    $destPath  = $tempDir . $newName;
    $relPath   = 'uploads/temp/' . $newName;

    cropAndResizeImage($file['tmp_name'], $destPath, 1600, 900);

    echo json_encode([
        'success'   => true,
        'temp_path' => $relPath,
        'url'       => BASE_URL . $relPath
    ]);
}

function cropAndResizeImage($src, $dst, $w, $h)
{
    list($ow, $oh) = getimagesize($src);
    $ratio  = $w / $h;
    $origR  = $ow / $oh;

    if ($origR > $ratio) {
        $nw = $oh * $ratio;
        $cropX = ($ow - $nw) / 2;
        $cropY = 0;
        $cw = $nw;
        $ch = $oh;
    } else {
        $nh = $ow / $ratio;
        $cropX = 0;
        $cropY = ($oh - $nh) / 2;
        $cw = $ow;
        $ch = $nh;
    }

    $type = exif_imagetype($src);
    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImg = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $srcImg = imagecreatefrompng($src);
            break;
        case IMAGETYPE_GIF:
            $srcImg = imagecreatefromgif($src);
            break;
        case IMAGETYPE_WEBP:
            $srcImg = imagecreatefromwebp($src);
            break;
        default:
            throw new Exception("Unsupported image type");
    }

    $dstImg = imagecreatetruecolor($w, $h);
    if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF])) {
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
        $trans = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
        imagefilledrectangle($dstImg, 0, 0, $w, $h, $trans);
    }

    imagecopyresampled(
        $dstImg,
        $srcImg,
        0,
        0,
        $cropX,
        $cropY,
        $w,
        $h,
        $cw,
        $ch
    );

    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dstImg, $dst, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($dstImg, $dst, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($dstImg, $dst);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($dstImg, $dst, 90);
            break;
    }

    imagedestroy($srcImg);
    imagedestroy($dstImg);
}

function getProductImages($uuid)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir)) return [];

    $json = $dir . '/images.json';
    if (!file_exists($json)) return [];

    $data = json_decode(file_get_contents($json), true);
    if (empty($data['images'])) return [];

    $out = [];
    foreach ($data['images'] as $f) {
        $url = filter_var($f, FILTER_VALIDATE_URL)
            ? $f
            : BASE_URL . "img/products/$uuid/$f";
        $out[] = $url;
    }
    return $out;
}

function moveProductImages($uuid, array $temps)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!file_exists($dir)) mkdir($dir, 0755, true);

    $moved = [];
    foreach ($temps as $t) {
        if (empty($t['temp_path'])) continue;
        $src = __DIR__ . '/../../' . $t['temp_path'];
        if (!file_exists($src)) continue;
        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $dst = "$dir/" . uniqid('prod_') . ".$ext";
        rename($src, $dst);
        $moved[] = basename($dst);
    }

    file_put_contents("$dir/images.json", json_encode(['images' => $moved], JSON_PRETTY_PRINT));
}

function saveExistingImages($uuid, array $imgs)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!file_exists($dir)) mkdir($dir, 0755, true);
    file_put_contents("$dir/images.json", json_encode(['images' => $imgs], JSON_PRETTY_PRINT));
}

function deleteAllProductImages($uuid, $rmDir = false)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir)) return;
    foreach (glob("$dir/*") as $f) {
        if (is_file($f)) unlink($f);
    }
    if ($rmDir) rmdir($dir);
}
