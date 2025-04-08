<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id BINARY(16) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category_id BINARY(16) NOT NULL,
        description TEXT,
        meta_title VARCHAR(100),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        status ENUM('published', 'pending', 'draft') NOT NULL DEFAULT 'published',
        featured BOOLEAN NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_pricing (
        id BINARY(16) PRIMARY KEY,
        product_id BINARY(16) NOT NULL,
        unit_of_measure_id BINARY(16) NOT NULL,
        price DECIMAL(12, 2) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_of_measure_id) REFERENCES product_unit_of_measure(id) ON DELETE RESTRICT,
        UNIQUE KEY product_uom_unique (product_id, unit_of_measure_id)
    )");
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

function getProducts($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT 
                p.id, p.title, p.category_id, p.description, 
                p.meta_title, p.meta_description, p.meta_keywords,
                p.views, p.status, p.featured,
                p.created_at, p.updated_at,
                c.name AS category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$prod) {
            $prod['uuid_id'] = binToUuid($prod['id']);
            unset($prod['id']);

            $prod['uuid_category'] = binToUuid($prod['category_id']);
            unset($prod['category_id']);

            $prod['images'] = getProductImages($prod['uuid_id']);

            $prod['units_of_measure'] = getProductUnitsOfMeasure($pdo, $prod['uuid_id']);

            $prod['category_name'] = $prod['category_name'] ?? '(Unknown)';
        }

        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        error_log("Error in getProducts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving products: ' . $e->getMessage()]);
    }
}

function getProduct($pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing product ID']);
        return;
    }

    $productId = $_GET['id'];
    $binaryId = uuidToBin($productId);

    try {
        $stmt = $pdo->prepare("SELECT 
                p.id, p.title, p.category_id, p.description, 
                p.meta_title, p.meta_description, p.meta_keywords,
                p.views, p.status, p.featured,
                p.created_at, p.updated_at,
                c.name AS category_name
            FROM products p
            LEFT JOIN product_categories c ON p.category_id = c.id
            WHERE p.id = :id
        ");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $product['uuid_id'] = binToUuid($product['id']);
        unset($product['id']);

        $product['uuid_category'] = binToUuid($product['category_id']);
        unset($product['category_id']);

        $product['images'] = getProductImages($product['uuid_id']);

        $product['units_of_measure'] = getProductUnitsOfMeasure($pdo, $product['uuid_id']);

        echo json_encode(['success' => true, 'data' => $product]);
    } catch (Exception $e) {
        error_log("Error in getProduct: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving product: ' . $e->getMessage()]);
    }
}

function createProduct($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title']) || empty($data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
            return;
        }

        $title           = trim($data['title']);
        $categoryIdStr   = trim($data['category_id']);
        $description     = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle       = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDesc        = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords    = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status          = !empty($data['status']) ? $data['status'] : 'published';
        $featured        = !empty($data['featured']) ? 1 : 0;

        $binaryCategoryId = uuidToBin($categoryIdStr);

        $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat_id");
        $stmt->bindParam(':cat_id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }

        $productId       = generateUUIDv7();
        $binaryProductId = uuidToBin($productId);
        $now             = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $insert = $pdo->prepare("
            INSERT INTO products (
                id, title, category_id, description,
                meta_title, meta_description, meta_keywords,
                status, featured,
                created_at, updated_at
            ) VALUES (
                :id, :title, :category_id, :description,
                :meta_title, :meta_description, :meta_keywords,
                :status, :featured,
                :created_at, :updated_at
            )
        ");
        $insert->bindParam(':id', $binaryProductId, PDO::PARAM_LOB);
        $insert->bindParam(':title', $title);
        $insert->bindParam(':category_id', $binaryCategoryId, PDO::PARAM_LOB);
        $insert->bindParam(':description', $description);
        $insert->bindParam(':meta_title', $metaTitle);
        $insert->bindParam(':meta_description', $metaDesc);
        $insert->bindParam(':meta_keywords', $metaKeywords);
        $insert->bindParam(':status', $status);
        $insert->bindParam(':featured', $featured, PDO::PARAM_INT);
        $insert->bindParam(':created_at', $now);
        $insert->bindParam(':updated_at', $now);
        $insert->execute();

        if (!empty($data['units_of_measure']) && is_array($data['units_of_measure'])) {
            foreach ($data['units_of_measure'] as $uom) {
                $uomIdStr = $uom['unit_of_measure_id'] ?? '';
                $price    = $uom['price'] ?? 0;
                if (!$uomIdStr || !$price) {
                    continue;
                }

                $binaryUomId = uuidToBin($uomIdStr);

                $priceId = generateUUIDv7();
                $binaryPriceId = uuidToBin($priceId);
                $stmt2 = $pdo->prepare("
                    INSERT INTO product_pricing (
                        id, product_id, unit_of_measure_id, price,
                        created_at, updated_at
                    ) VALUES (
                        :id, :product_id, :unit_of_measure_id, :price,
                        :created_at, :updated_at
                    )
                ");
                $stmt2->bindParam(':id', $binaryPriceId, PDO::PARAM_LOB);
                $stmt2->bindParam(':product_id', $binaryProductId, PDO::PARAM_LOB);
                $stmt2->bindParam(':unit_of_measure_id', $binaryUomId, PDO::PARAM_LOB);
                $stmt2->bindParam(':price', $price);
                $stmt2->bindParam(':created_at', $now);
                $stmt2->bindParam(':updated_at', $now);
                $stmt2->execute();
            }
        }

        if (!empty($data['temp_images']) && is_array($data['temp_images'])) {
            moveProductImages($productId, $data['temp_images']);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Product created successfully',
            'id'      => $productId
        ]);
    } catch (PDOException $e) {
        error_log("Error in createProduct: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
    } catch (Exception $ex) {
        error_log("Error in createProduct: " . $ex->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $ex->getMessage()]);
    }
}

function updateProduct($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            return;
        }

        $prodIdStr = $data['id'];
        $binaryProdId = uuidToBin($prodIdStr);

        $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmtCheck->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        if (empty($data['title']) || empty($data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
            return;
        }

        $title           = trim($data['title']);
        $categoryIdStr   = trim($data['category_id']);
        $binaryCategoryId = uuidToBin($categoryIdStr);
        $description     = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle       = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDesc        = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords    = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status          = !empty($data['status']) ? $data['status'] : 'published';
        $featured        = !empty($data['featured']) ? 1 : 0;

        $stmtCat = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat_id");
        $stmtCat->bindParam(':cat_id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmtCat->execute();
        if ($stmtCat->rowCount() === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE products 
            SET title = :title,
                category_id = :cat_id,
                description = :description,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                status = :status,
                featured = :featured,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':cat_id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':meta_title', $metaTitle);
        $stmt->bindParam(':meta_description', $metaDesc);
        $stmt->bindParam(':meta_keywords', $metaKeywords);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmt->execute();

        $stmtDel = $pdo->prepare("DELETE FROM product_pricing WHERE product_id = :prod_id");
        $stmtDel->bindParam(':prod_id', $binaryProdId, PDO::PARAM_LOB);
        $stmtDel->execute();

        if (!empty($data['units_of_measure']) && is_array($data['units_of_measure'])) {
            foreach ($data['units_of_measure'] as $uom) {
                $uomIdStr = $uom['unit_of_measure_id'] ?? '';
                $price    = $uom['price'] ?? 0;
                if (!$uomIdStr || !$price) {
                    continue;
                }

                $binaryUomId = uuidToBin($uomIdStr);

                $priceId = generateUUIDv7();
                $binaryPriceId = uuidToBin($priceId);
                $stmt2 = $pdo->prepare("
                    INSERT INTO product_pricing (
                        id, product_id, unit_of_measure_id, price,
                        created_at, updated_at
                    ) VALUES (
                        :id, :product_id, :unit_of_measure_id, :price,
                        :created_at, :updated_at
                    )
                ");
                $stmt2->bindParam(':id', $binaryPriceId, PDO::PARAM_LOB);
                $stmt2->bindParam(':product_id', $binaryProdId, PDO::PARAM_LOB);
                $stmt2->bindParam(':unit_of_measure_id', $binaryUomId, PDO::PARAM_LOB);
                $stmt2->bindParam(':price', $price);
                $stmt2->bindParam(':created_at', $now);
                $stmt2->bindParam(':updated_at', $now);
                $stmt2->execute();
            }
        }

        if (isset($data['update_images']) && $data['update_images']) {
            $existingImages = $data['existing_images'] ?? [];
            $tempImages = $data['temp_images'] ?? [];

            if (!empty($existingImages) || !empty($tempImages)) {
                deleteAllProductImages($prodIdStr, false);

                if (!empty($existingImages)) {
                    saveExistingImages($prodIdStr, $existingImages);
                }

                if (!empty($tempImages)) {
                    moveProductImages($prodIdStr, $tempImages);
                }
            }
        }

        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } catch (PDOException $e) {
        error_log("Error in updateProduct: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
    } catch (Exception $ex) {
        error_log("Error in updateProduct: " . $ex->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $ex->getMessage()]);
    }
}

function deleteProduct($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            return;
        }

        $prodIdStr = $data['id'];
        $binaryProdId = uuidToBin($prodIdStr);

        $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmtCheck->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmtDel->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtDel->execute();

        deleteAllProductImages($prodIdStr, true);

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (PDOException $e) {
        error_log("Error in deleteProduct: " . $e->getMessage());
        if ($e->getCode() === '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this product because it is used elsewhere']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
        }
    } catch (Exception $ex) {
        error_log("Error in deleteProduct: " . $ex->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $ex->getMessage()]);
    }
}

function toggleFeatured($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing product ID']);
            return;
        }

        $prodIdStr = $data['id'];
        $featured = isset($data['featured']) ? (bool)$data['featured'] : false;
        $binaryProdId = uuidToBin($prodIdStr);

        $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmtCheck->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $featuredInt = $featured ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE products SET featured = :featured, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':featured', $featuredInt, PDO::PARAM_INT);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => $featured ? 'Product marked as featured' : 'Product removed from featured'
        ]);
    } catch (Exception $e) {
        error_log("Error in toggleFeatured: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating featured status: ' . $e->getMessage()]);
    }
}

function uploadImage()
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
        return;
    }

    try {
        $file = $_FILES['image'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WebP, and GIF allowed.']);
            return;
        }
        if ($fileSize > 5 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB allowed.']);
            return;
        }

        $tempDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $newFileName = uniqid('temp_') . '.' . $ext;
        $uploadPath = $tempDir . $newFileName;
        $relativePath = 'uploads/temp/' . $newFileName;

        cropAndResizeImage($fileTmpName, $uploadPath, 1600, 900);

        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'temp_path' => $relativePath,
            'url' => BASE_URL . $relativePath
        ]);
    } catch (Exception $e) {
        error_log("Error in uploadImage: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error uploading image: ' . $e->getMessage()]);
    }
}

function cropAndResizeImage($sourcePath, $destPath, $width, $height)
{
    list($origWidth, $origHeight) = getimagesize($sourcePath);

    $ratio = $width / $height;
    $origRatio = $origWidth / $origHeight;

    if ($origRatio > $ratio) {
        $newWidth = $origHeight * $ratio;
        $cropX = ($origWidth - $newWidth) / 2;
        $cropY = 0;
        $cropWidth = $newWidth;
        $cropHeight = $origHeight;
    } else {
        $newHeight = $origWidth / $ratio;
        $cropX = 0;
        $cropY = ($origHeight - $newHeight) / 2;
        $cropWidth = $origWidth;
        $cropHeight = $newHeight;
    }

    $imageType = exif_imagetype($sourcePath);

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            throw new Exception("Unsupported image type");
    }

    $destination = imagecreatetruecolor($width, $height);

    if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $width, $height, $transparent);
    }

    imagecopyresampled(
        $destination,
        $source,
        0,
        0,
        $cropX,
        $cropY,
        $width,
        $height,
        $cropWidth,
        $cropHeight
    );

    switch ($imageType) {
        case IMAGETYPE_JPEG:
            imagejpeg($destination, $destPath, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($destination, $destPath, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($destination, $destPath);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($destination, $destPath, 90);
            break;
    }

    imagedestroy($source);
    imagedestroy($destination);
}

function getProductImages($productUuid)
{
    $dir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($dir) || !is_dir($dir)) {
        return [];
    }

    $jsonPath = $dir . '/images.json';
    if (!file_exists($jsonPath)) {
        return [];
    }

    $jsonData = json_decode(file_get_contents($jsonPath), true);
    if (!$jsonData || !isset($jsonData['images'])) {
        return [];
    }

    $images = [];
    foreach ($jsonData['images'] as $imgFile) {
        if (filter_var($imgFile, FILTER_VALIDATE_URL)) {
            $images[] = $imgFile;
        } else {
            $fullPath = 'img/products/' . $productUuid . '/' . $imgFile;
            $absUrl = BASE_URL . $fullPath;
            $images[] = $absUrl;
        }
    }
    return $images;
}

function getProductUnitsOfMeasure($pdo, $productUuid)
{
    $binaryProdId = uuidToBin($productUuid);
    $stmt = $pdo->prepare("
        SELECT pp.id, pp.unit_of_measure_id, pp.price,
               uom.si_unit,
               pn.package_name,
               CONCAT(pn.package_name, ' (', uom.si_unit, ')') as unit_of_measure
        FROM product_pricing pp
        JOIN product_unit_of_measure uom ON pp.unit_of_measure_id = uom.id
        JOIN product_package_name pn ON uom.product_package_name_id = pn.id
        WHERE pp.product_id = :pid
    ");
    $stmt->bindParam(':pid', $binaryProdId, PDO::PARAM_LOB);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $priceId = binToUuid($row['id']);
        $uomUuid = binToUuid($row['unit_of_measure_id']);
        $price   = $row['price'];
        $result[] = [
            'id'                => $priceId,
            'unit_of_measure_id' => $uomUuid,
            'price'             => $price,
            'si_unit'           => $row['si_unit'],
            'package_name'      => $row['package_name'],
            'unit_of_measure'   => $row['unit_of_measure']
        ];
    }
    return $result;
}

function moveProductImages($productUuid, array $tempImages)
{
    $productDir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($productDir)) {
        mkdir($productDir, 0755, true);
    }

    $movedFiles = [];
    foreach ($tempImages as $imgData) {
        if (empty($imgData['temp_path'])) {
            continue;
        }
        $tmpPath = __DIR__ . '/../../' . $imgData['temp_path'];
        if (!file_exists($tmpPath)) {
            continue;
        }

        $ext = pathinfo($tmpPath, PATHINFO_EXTENSION);
        $uniqueName = uniqid('prod_') . '.' . $ext;
        $newFullPath = $productDir . '/' . $uniqueName;
        rename($tmpPath, $newFullPath);

        $movedFiles[] = $uniqueName;
    }

    updateProductImagesJson($productDir, $movedFiles);
}

function saveExistingImages($productUuid, array $existingImages)
{
    $productDir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($productDir)) {
        mkdir($productDir, 0755, true);
    }

    updateProductImagesJson($productDir, $existingImages);
}

function updateProductImagesJson($productDir, array $images)
{
    $jsonPath = $productDir . '/images.json';
    file_put_contents($jsonPath, json_encode(['images' => $images], JSON_PRETTY_PRINT));
}

function deleteAllProductImages($productUuid, $removeDir = false)
{
    $dir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($dir)) {
        return;
    }

    $files = glob($dir . '/*');
    foreach ($files as $f) {
        if (is_file($f)) {
            unlink($f);
        }
    }

    if ($removeDir) {
        rmdir($dir);
    }
}
