<?php
ob_start();

// Error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Ensure only logged-in admin can proceed
if (
    !isset($_SESSION['user'])
    || !isset($_SESSION['user']['logged_in'])
    || !$_SESSION['user']['logged_in']
    || !isset($_SESSION['user']['is_admin'])
    || !$_SESSION['user']['is_admin']
) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set timezone
date_default_timezone_set('Africa/Kampala');

try {
    // Create products table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id BINARY(16) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        category_id BINARY(16) NOT NULL,
        description TEXT,
        meta_title VARCHAR(100),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        views INT UNSIGNED NOT NULL DEFAULT 0,
        status ENUM('published', 'pending', 'draft') NOT NULL DEFAULT 'published',
        featured BOOLEAN NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
    )");

    // Create product_pricing table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_pricing (
        id BINARY(16) PRIMARY KEY,
        product_id BINARY(16) NOT NULL,
        package_id BINARY(16) NOT NULL,
        price DECIMAL(12, 2) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (package_id) REFERENCES product_packages(id) ON DELETE RESTRICT,
        UNIQUE KEY product_package_unique (product_id, package_id)
    )");
} catch (PDOException $e) {
    error_log("Table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database setup failed']);
    exit;
}

// Which action to take?
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

/**
 * Retrieve ALL products (with minimal data).
 */
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

            // Load image paths from local folder & JSON
            $prod['images'] = getProductImages($prod['uuid_id']);

            // We'll fetch product pricing from product_pricing
            $prod['packages'] = getProductPackages($pdo, $prod['uuid_id']);

            // If category_name is null, it means the category was missing or deleted
            $prod['category_name'] = $prod['category_name'] ?? '(Unknown)';
        }

        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        error_log("Error in getProducts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving products: ' . $e->getMessage()]);
    }
}

/**
 * Retrieve ONE product by ID (UUID).
 */
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

        // Load images from local folder & JSON
        $product['images'] = getProductImages($product['uuid_id']);

        // Load product packages
        $product['packages'] = getProductPackages($pdo, $product['uuid_id']);

        // Provide response
        echo json_encode(['success' => true, 'data' => $product]);
    } catch (Exception $e) {
        error_log("Error in getProduct: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving product: ' . $e->getMessage()]);
    }
}

/**
 * Create a new product
 */
function createProduct($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate mandatory fields
        if (
            empty($data['title'])
            || empty($data['category_id'])
        ) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
            return;
        }

        // Prepare data
        $title           = trim($data['title']);
        $categoryIdStr   = trim($data['category_id']);
        $description     = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle       = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDesc        = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords    = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status          = !empty($data['status']) ? $data['status'] : 'published';
        $featured        = !empty($data['featured']) ? 1 : 0;

        $binaryCategoryId = uuidToBin($categoryIdStr);

        // Check if category actually exists
        $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat_id");
        $stmt->bindParam(':cat_id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }

        // Generate new UUID
        $productId       = generateUUIDv7();
        $binaryProductId = uuidToBin($productId);
        $now             = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        // Insert into products
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

        // Insert product pricing
        if (!empty($data['packages']) && is_array($data['packages'])) {
            foreach ($data['packages'] as $pkg) {
                // e.g. $pkg = ['package_id' => '...', 'price' => 12000]
                $pkgIdStr = $pkg['package_id'] ?? '';
                $price    = $pkg['price'] ?? 0;
                if (!$pkgIdStr || !$price) {
                    continue;
                }

                $binaryPkgId = uuidToBin($pkgIdStr);

                // Insert into product_pricing
                $priceId = generateUUIDv7();
                $binaryPriceId = uuidToBin($priceId);
                $stmt2 = $pdo->prepare("
                    INSERT INTO product_pricing (
                        id, product_id, package_id, price,
                        created_at, updated_at
                    ) VALUES (
                        :id, :product_id, :package_id, :price,
                        :created_at, :updated_at
                    )
                ");
                $stmt2->bindParam(':id', $binaryPriceId, PDO::PARAM_LOB);
                $stmt2->bindParam(':product_id', $binaryProductId, PDO::PARAM_LOB);
                $stmt2->bindParam(':package_id', $binaryPkgId, PDO::PARAM_LOB);
                $stmt2->bindParam(':price', $price);
                $stmt2->bindParam(':created_at', $now);
                $stmt2->bindParam(':updated_at', $now);
                $stmt2->execute();
            }
        }

        // Handle images: we expect them to be moved from temp and a JSON with order
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

/**
 * Update an existing product
 */
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

        // Check product existence
        $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmtCheck->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Validate mandatory fields
        if (empty($data['title']) || empty($data['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product title and category are required']);
            return;
        }

        // Prepare data
        $title           = trim($data['title']);
        $categoryIdStr   = trim($data['category_id']);
        $binaryCategoryId = uuidToBin($categoryIdStr);
        $description     = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle       = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDesc        = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords    = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status          = !empty($data['status']) ? $data['status'] : 'published';
        $featured        = !empty($data['featured']) ? 1 : 0;

        // Check if category actually exists
        $stmtCat = $pdo->prepare("SELECT id FROM product_categories WHERE id = :cat_id");
        $stmtCat->bindParam(':cat_id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmtCat->execute();
        if ($stmtCat->rowCount() === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        // Perform update
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

        // Update packages (wipe old pricing, re-insert or use a more surgical approach)
        // For simplicity, let's remove all existing product_pricing for this product and re-insert
        $stmtDel = $pdo->prepare("DELETE FROM product_pricing WHERE product_id = :prod_id");
        $stmtDel->bindParam(':prod_id', $binaryProdId, PDO::PARAM_LOB);
        $stmtDel->execute();

        if (!empty($data['packages']) && is_array($data['packages'])) {
            foreach ($data['packages'] as $pkg) {
                $pkgIdStr = $pkg['package_id'] ?? '';
                $price    = $pkg['price'] ?? 0;
                if (!$pkgIdStr || !$price) {
                    continue;
                }

                $binaryPkgId = uuidToBin($pkgIdStr);

                // Insert new row
                $priceId = generateUUIDv7();
                $binaryPriceId = uuidToBin($priceId);
                $stmt2 = $pdo->prepare("
                    INSERT INTO product_pricing (
                        id, product_id, package_id, price,
                        created_at, updated_at
                    ) VALUES (
                        :id, :product_id, :package_id, :price,
                        :created_at, :updated_at
                    )
                ");
                $stmt2->bindParam(':id', $binaryPriceId, PDO::PARAM_LOB);
                $stmt2->bindParam(':product_id', $binaryProdId, PDO::PARAM_LOB);
                $stmt2->bindParam(':package_id', $binaryPkgId, PDO::PARAM_LOB);
                $stmt2->bindParam(':price', $price);
                $stmt2->bindParam(':created_at', $now);
                $stmt2->bindParam(':updated_at', $now);
                $stmt2->execute();
            }
        }

        // Only update images if explicitly requested
        // This fixes the issue where images were being deleted unintentionally
        if (isset($data['update_images']) && $data['update_images']) {
            // Handle existing images and temp images
            $existingImages = $data['existing_images'] ?? [];
            $tempImages = $data['temp_images'] ?? [];

            if (!empty($existingImages) || !empty($tempImages)) {
                // Remove existing images only if we're updating them
                deleteAllProductImages($prodIdStr, false);

                // Save existing images to JSON
                if (!empty($existingImages)) {
                    saveExistingImages($prodIdStr, $existingImages);
                }

                // Move temp images if present
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

/**
 * Delete a product (removes DB row and the entire folder).
 */
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

        // Check if product exists
        $stmtCheck = $pdo->prepare("SELECT id FROM products WHERE id = :id");
        $stmtCheck->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtCheck->execute();
        if ($stmtCheck->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }

        // Delete row
        $stmtDel = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmtDel->bindParam(':id', $binaryProdId, PDO::PARAM_LOB);
        $stmtDel->execute();

        // Remove product images
        deleteAllProductImages($prodIdStr, true);

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (PDOException $e) {
        error_log("Error in deleteProduct: " . $e->getMessage());
        if ($e->getCode() === '23000') {
            // Possibly a foreign key constraint from an order referencing this product
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

/**
 * Toggle featured status for a product
 */
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

        // Check if product exists
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

        // Update featured status
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

/**
 * Handle image upload (similar to categories).
 * Receives file via POST multipart/form-data. 
 * Saves to temporary folder, returns path for front-end usage.
 */
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

        // Create a temp dir if not exist
        $tempDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $newFileName = uniqid('temp_') . '.' . $ext;
        $uploadPath = $tempDir . $newFileName;
        $relativePath = 'uploads/temp/' . $newFileName;

        // Crop image to 16:9 aspect ratio
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

/**
 * Crop and resize image to specified dimensions
 */
function cropAndResizeImage($sourcePath, $destPath, $width, $height)
{
    list($origWidth, $origHeight) = getimagesize($sourcePath);

    $ratio = $width / $height;
    $origRatio = $origWidth / $origHeight;

    if ($origRatio > $ratio) {
        // Image is wider than target ratio
        $newWidth = $origHeight * $ratio;
        $cropX = ($origWidth - $newWidth) / 2;
        $cropY = 0;
        $cropWidth = $newWidth;
        $cropHeight = $origHeight;
    } else {
        // Image is taller than target ratio
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

    // Preserve transparency for PNG and GIF
    if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $width, $height, $transparent);
    }

    // Crop and resize
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

    // Save the image
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

/** 
 * Utility: Retrieve the product's images based on JSON order 
 */
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

    // Convert each path to a full URL
    $images = [];
    foreach ($jsonData['images'] as $imgFile) {
        // Check if it's a full URL or a relative path
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

/** 
 * Utility: Retrieve (package, price) pairs for a product from `product_pricing`.
 */
function getProductPackages($pdo, $productUuid)
{
    $binaryProdId = uuidToBin($productUuid);
    $stmt = $pdo->prepare("SELECT id, package_id, price FROM product_pricing WHERE product_id = :pid");
    $stmt->bindParam(':pid', $binaryProdId, PDO::PARAM_LOB);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($rows as $row) {
        $priceId = binToUuid($row['id']);
        $pkgUuid = binToUuid($row['package_id']);
        $price   = $row['price'];
        $result[] = [
            'id'         => $priceId,
            'package_id' => $pkgUuid,
            'price'      => $price
        ];
    }
    return $result;
}

/** 
 * Utility: Move product images from temp path to the product folder and record a JSON with the order. 
 */
function moveProductImages($productUuid, array $tempImages)
{
    $productDir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($productDir)) {
        mkdir($productDir, 0755, true);
    }

    $movedFiles = [];
    // In the array, each item might have: { temp_path: "...", filename: "..." } or something similar
    foreach ($tempImages as $imgData) {
        if (empty($imgData['temp_path'])) {
            continue;
        }
        $tmpPath = __DIR__ . '/../../' . $imgData['temp_path'];
        if (!file_exists($tmpPath)) {
            continue;
        }

        // Generate a new safe name. Or keep same extension as original
        $ext = pathinfo($tmpPath, PATHINFO_EXTENSION);
        $uniqueName = uniqid('prod_') . '.' . $ext;
        $newFullPath = $productDir . '/' . $uniqueName;
        rename($tmpPath, $newFullPath);

        // Collect the final filename for JSON
        $movedFiles[] = $uniqueName;
    }

    // Create or update images.json with the order
    updateProductImagesJson($productDir, $movedFiles);
}

/**
 * Save existing image URLs to the product's images.json file
 */
function saveExistingImages($productUuid, array $existingImages)
{
    $productDir = __DIR__ . '/../../img/products/' . $productUuid;
    if (!file_exists($productDir)) {
        mkdir($productDir, 0755, true);
    }

    // Create or update images.json with the existing URLs
    updateProductImagesJson($productDir, $existingImages);
}

/**
 * Update the product's images.json file with the provided image list
 */
function updateProductImagesJson($productDir, array $images)
{
    $jsonPath = $productDir . '/images.json';
    file_put_contents($jsonPath, json_encode(['images' => $images], JSON_PRETTY_PRINT));
}

/**
 * Utility: remove all images for a given product. 
 * If $removeDir is true, also remove the product folder.
 */
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
