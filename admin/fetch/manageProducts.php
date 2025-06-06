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

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS product_package_name_mappings (
            id VARCHAR(26) PRIMARY KEY,
            product_id VARCHAR(26) NOT NULL,
            product_package_name_id VARCHAR(26) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (product_package_name_id) REFERENCES product_package_name(id) ON DELETE CASCADE,
            UNIQUE KEY product_package_unique (product_id, product_package_name_id)
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
        case 'getProductPackageNames':
            getProductPackageNames($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Error in manageProducts: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/* --------------------------------------------------------------------------
   ┃  End-points
   -------------------------------------------------------------------------- */
function getProducts(PDO $pdo)
{
    $stmt = $pdo->prepare(
        "SELECT p.id,p.title,p.category_id,p.description,
                   p.meta_title,p.meta_description,p.meta_keywords,
                   p.status,p.featured,p.created_at,p.updated_at,
                   c.name AS category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC"
    );
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$prod) {
        $prod['featured'] = (int) $prod['featured'];
        $prod['category'] = $prod['category_id'];
        unset($prod['category_id']);
        $prod['images'] = getProductImages($prod['id']);
        $prod['category_name'] = $prod['category_name'] ?? '(Unknown)';
        $prod['package_names'] = getProductPackageNamesById($pdo, $prod['id']);
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
        "SELECT p.id,p.title,p.category_id,p.description,
                p.meta_title,p.meta_description,p.meta_keywords,
                p.status,p.featured,p.created_at,p.updated_at,
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

    $product['featured'] = (int) $product['featured'];
    $product['category'] = $product['category_id'];
    unset($product['category_id']);
    $product['images'] = getProductImages($product['id']);
    $product['package_names'] = getProductPackageNamesById($pdo, $product['id']);

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

    $title = trim($data['title']);
    $categoryId = trim($data['category_id']);
    $description = $data['description'] ?? '';
    $metaTitle = $data['meta_title'] ?? '';
    $metaDesc = $data['meta_description'] ?? '';
    $metaKeywords = $data['meta_keywords'] ?? '';
    $status = $data['status'] ?? 'published';
    $featured = !empty($data['featured']) ? 1 : 0;
    $packageNames = $data['package_names'] ?? [];

    // validate category
    $check = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat");
    $check->execute([':cat' => $categoryId]);
    if ($check->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
        return;
    }

    $productId = generateUlid();
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare(
            "INSERT INTO products (
                 id,title,category_id,description,
                 meta_title,meta_description,meta_keywords,
                 status,featured,created_at,updated_at
             ) VALUES (
                 :id,:title,:cat,:description,
                 :mt,:md,:mk,
                 :status,:featured,:created_at,:updated_at
             )"
        );
        $ins->execute([
            ':id' => $productId,
            ':title' => $title,
            ':cat' => $categoryId,
            ':description' => $description,
            ':mt' => $metaTitle,
            ':md' => $metaDesc,
            ':mk' => $metaKeywords,
            ':status' => $status,
            ':featured' => $featured,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);

        if ($packageNames) {
            saveProductPackageNames($pdo, $productId, $packageNames);
        }
        if (!empty($data['temp_images'])) {
            moveProductImages($productId, $data['temp_images']);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product created successfully', 'id' => $productId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error creating product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
    }
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

    // ensure product exists
    $chk = $pdo->prepare("SELECT id FROM products WHERE id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    if (empty($data['title']) || empty($data['category_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
        return;
    }

    $title = trim($data['title']);
    $categoryId = trim($data['category_id']);
    $description = $data['description'] ?? '';
    $metaTitle = $data['meta_title'] ?? '';
    $metaDesc = $data['meta_description'] ?? '';
    $metaKeywords = $data['meta_keywords'] ?? '';
    $status = $data['status'] ?? 'published';
    $featured = !empty($data['featured']) ? 1 : 0;
    $packageNames = $data['package_names'] ?? [];

    // validate category
    $checkCat = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat");
    $checkCat->execute([':cat' => $categoryId]);
    if ($checkCat->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
        return;
    }

    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
    $pdo->beginTransaction();
    try {
        // update core product row
        $pdo->prepare(
            "UPDATE products SET
                 title            = :title,
                 category_id      = :cat,
                 description      = :description,
                 meta_title       = :mt,
                 meta_description = :md,
                 meta_keywords    = :mk,
                 status           = :status,
                 featured         = :featured,
                 updated_at       = :updated_at
             WHERE id = :id"
        )->execute([
                    ':title' => $title,
                    ':cat' => $categoryId,
                    ':description' => $description,
                    ':mt' => $metaTitle,
                    ':md' => $metaDesc,
                    ':mk' => $metaKeywords,
                    ':status' => $status,
                    ':featured' => $featured,
                    ':updated_at' => $now,
                    ':id' => $id
                ]);

        /*--------------------------------------------------
         |  🔄  Package-name mappings diff
         *-------------------------------------------------*/
        if (isset($data['package_names'])) {
            // fetch existing
            $stmt = $pdo->prepare(
                "SELECT id, product_package_name_id
                   FROM product_package_name_mappings
                  WHERE product_id = :pid"
            );
            $stmt->execute([':pid' => $id]);
            $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $existingPkgIds = array_column($existing, 'product_package_name_id');
            $existingMap = [];
            foreach ($existing as $row) {
                $existingMap[$row['product_package_name_id']] = $row['id'];
            }

            // to delete: old ones not in new list
            $toDelete = array_diff($existingPkgIds, $packageNames);
            if ($toDelete) {
                $delStmt = $pdo->prepare(
                    "DELETE FROM product_package_name_mappings
                      WHERE product_id = :pid
                        AND product_package_name_id = :pkg"
                );
                foreach ($toDelete as $pkgId) {
                    $delStmt->execute([':pid' => $id, ':pkg' => $pkgId]);
                }
            }

            // to insert: new ones not in existing
            $toInsert = array_diff($packageNames, $existingPkgIds);
            if ($toInsert) {
                saveProductPackageNames($pdo, $id, $toInsert);
            }
        }

        /*--------------------------------------------------
         |  🖼️  Image handling (unchanged from last fix)
         *-------------------------------------------------*/
        if (!empty($data['update_images'])) {
            $existing = $data['existing_images'] ?? [];
            $temp = $data['temp_images'] ?? [];

            if ($existing || $temp) {
                $keepBasenames = array_map('basename', $existing);
                cleanProductImagesDirectory($id, $keepBasenames);
                $moved = $temp ? moveProductImages($id, $temp) : [];
                $final = array_merge($keepBasenames, $moved);
                saveExistingImages($id, $final);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error updating product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
    }
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

    $pdo->beginTransaction();

    try {
        $pdo->prepare("DELETE FROM product_package_name_mappings WHERE product_id = :id")
            ->execute([':id' => $id]);

        $pdo->prepare("DELETE FROM products WHERE id = :id")
            ->execute([':id' => $id]);

        deleteAllProductImages($id, true);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error deleting product: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
}

function toggleFeatured(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $id = $data['id'];
    $featured = !empty($data['featured']) ? 1 : 0;
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    $pdo->prepare(
        "UPDATE products
         SET featured = :featured, updated_at = :updated_at
         WHERE id = :id"
    )->execute([
                ':featured' => $featured,
                ':updated_at' => $now,
                ':id' => $id
            ]);

    echo json_encode([
        'success' => true,
        'message' => $featured
            ? 'Product marked as featured'
            : 'Product removed from featured'
    ]);
}

/* --------------------------------------------------------------------------
   ┃  Image upload & processing
   -------------------------------------------------------------------------- */
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

    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $size = $file['size'];

    if (!in_array($ext, $allowed) || $size > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file or too large']);
        return;
    }

    $tempDir = __DIR__ . '/../../uploads/temp/';
    if (!file_exists($tempDir))
        mkdir($tempDir, 0755, true);

    $newName = uniqid('temp_') . ".$ext";
    $destPath = $tempDir . $newName;
    $relPath = 'uploads/temp/' . $newName;

    cropAndResizeImage($file['tmp_name'], $destPath, 1600, 900);

    echo json_encode([
        'success' => true,
        'temp_path' => $relPath,
        'url' => BASE_URL . $relPath
    ]);
}

function cropAndResizeImage($src, $dst, $w, $h)
{
    list($ow, $oh) = getimagesize($src);
    $ratio = $w / $h;
    $origR = $ow / $oh;

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

    imagecopyresampled($dstImg, $srcImg, 0, 0, $cropX, $cropY, $w, $h, $cw, $ch);

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

/* --------------------------------------------------------------------------
   ┃  Gallery helpers
   -------------------------------------------------------------------------- */
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
    if (!file_exists($dir))
        mkdir($dir, 0755, true);

    $moved = [];
    foreach ($temps as $t) {
        if (empty($t['temp_path']))
            continue;
        $src = __DIR__ . '/../../' . $t['temp_path'];
        if (!file_exists($src))
            continue;
        $ext = pathinfo($src, PATHINFO_EXTENSION);
        $dst = "$dir/" . uniqid('prod_') . ".$ext";
        rename($src, $dst);
        $moved[] = basename($dst);
    }

    // Append to / overwrite existing list
    $current = getProductImages($uuid);
    $currentBasenames = array_map('basename', $current);
    $newList = array_merge($currentBasenames, $moved);
    saveExistingImages($uuid, $newList);

    return $moved;
}

function saveExistingImages($uuid, array $imgs)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!file_exists($dir))
        mkdir($dir, 0755, true);
    file_put_contents("$dir/images.json", json_encode(['images' => $imgs], JSON_PRETTY_PRINT));
}

function deleteAllProductImages($uuid, $rmDir = false)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir))
        return;
    foreach (glob("$dir/*") as $f) {
        if (is_file($f))
            unlink($f);
    }
    if ($rmDir)
        rmdir($dir);
}

function cleanProductImagesDirectory($uuid, array $keepList)
{
    $dir = __DIR__ . '/../../img/products/' . $uuid;
    if (!is_dir($dir))
        return;

    $keep = array_map('basename', $keepList);
    foreach (glob("$dir/*") as $f) {
        if (is_file($f) && !in_array(basename($f), $keep, true)) {
            unlink($f);
        }
    }
}

/* --------------------------------------------------------------------------
   ┃  Package-name helpers
   -------------------------------------------------------------------------- */
function getProductPackageNames(PDO $pdo)
{
    if (!isset($_GET['product_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $productId = $_GET['product_id'];
    $packageNames = getProductPackageNamesById($pdo, $productId);

    echo json_encode(['success' => true, 'packageNames' => $packageNames]);
}

function getProductPackageNamesById(PDO $pdo, $productId)
{
    $stmt = $pdo->prepare(
        "SELECT ppn.id, ppn.package_name
         FROM product_package_name ppn
         JOIN product_package_name_mappings ppnm ON ppn.id = ppnm.product_package_name_id
         WHERE ppnm.product_id = :product_id
         ORDER BY ppn.package_name"
    );
    $stmt->execute([':product_id' => $productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveProductPackageNames(PDO $pdo, $productId, array $packageNames)
{
    $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

    foreach ($packageNames as $packageNameId) {
        $mappingId = generateUlid();

        $stmt = $pdo->prepare(
            "INSERT INTO product_package_name_mappings (
                id, product_id, product_package_name_id, created_at, updated_at
            ) VALUES (
                :id, :product_id, :package_name_id, :created_at, :updated_at
            ) ON DUPLICATE KEY UPDATE updated_at = :updated_at_2"
        );

        $stmt->execute([
            ':id' => $mappingId,
            ':product_id' => $productId,
            ':package_name_id' => $packageNameId,
            ':created_at' => $now,
            ':updated_at' => $now,
            ':updated_at_2' => $now
        ]);
    }
}
