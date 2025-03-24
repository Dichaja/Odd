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
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_categories (
        id BINARY(16) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        meta_title VARCHAR(100),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        UNIQUE KEY name_unique (name)
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
        case 'getCategories':
            getCategories($pdo);
            break;

        case 'getCategory':
            getCategory($pdo);
            break;

        case 'createCategory':
            createCategory($pdo);
            break;

        case 'updateCategory':
            updateCategory($pdo);
            break;

        case 'deleteCategory':
            deleteCategory($pdo);
            break;

        case 'uploadImage':
            uploadImage();
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found: ' . $action]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in manageProductCategories: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getCategories($pdo)
{
    try {
        $stmt = $pdo->prepare("SELECT id, name, description, meta_title, meta_description, meta_keywords, status, created_at, updated_at FROM product_categories ORDER BY name");
        $stmt->execute();

        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as &$category) {
            $category['uuid_id'] = binToUuid($category['id']);
            $category['image_url'] = getCategoryImageUrl($category['uuid_id'], $category['name']);
            unset($category['id']);
        }

        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (Exception $e) {
        error_log("Error in getCategories: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving categories: ' . $e->getMessage()]);
    }
}

function getCategory($pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing category ID']);
        return;
    }

    try {
        $categoryId = $_GET['id'];
        $binaryId = uuidToBin($categoryId);

        $stmt = $pdo->prepare("SELECT id, name, description, meta_title, meta_description, meta_keywords, status, created_at, updated_at FROM product_categories WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        $category['uuid_id'] = binToUuid($category['id']);
        $category['image_url'] = getCategoryImageUrl($category['uuid_id'], $category['name']);
        $category['has_image'] = doesCategoryImageExist($category['uuid_id'], $category['name']);
        unset($category['id']);

        echo json_encode(['success' => true, 'data' => $category]);
    } catch (Exception $e) {
        error_log("Error in getCategory: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving category: ' . $e->getMessage()]);
    }
}

function createCategory($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['name']) || trim($data['name']) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            return;
        }

        $name = trim($data['name']);
        $description = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDescription = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status = isset($data['status']) && in_array($data['status'], ['active', 'inactive']) ? $data['status'] : 'active';
        $tempImagePath = isset($data['temp_image_path']) ? trim($data['temp_image_path']) : '';

        $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
            return;
        }

        $categoryId = generateUUIDv7();
        $binaryCategoryId = uuidToBin($categoryId);
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO product_categories (id, name, description, meta_title, meta_description, meta_keywords, status, created_at, updated_at) VALUES (:id, :name, :description, :meta_title, :meta_description, :meta_keywords, :status, :created_at, :updated_at)");
        $stmt->bindParam(':id', $binaryCategoryId, PDO::PARAM_LOB);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':meta_title', $metaTitle);
        $stmt->bindParam(':meta_description', $metaDescription);
        $stmt->bindParam(':meta_keywords', $metaKeywords);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_at', $now);
        $stmt->bindParam(':updated_at', $now);
        $stmt->execute();

        // Move temp image to permanent location if it exists
        if (!empty($tempImagePath) && file_exists(__DIR__ . '/../../' . $tempImagePath)) {
            $fileExt = pathinfo($tempImagePath, PATHINFO_EXTENSION);
            $categoryDirPath = __DIR__ . '/../../img/product-categories/' . $categoryId;

            // Create category directory if it doesn't exist
            if (!file_exists($categoryDirPath)) {
                mkdir($categoryDirPath, 0755, true);
            }

            // Sanitize category name for filename
            $safeFileName = sanitizeFileName($name) . '.' . $fileExt;
            $newImagePath = $categoryDirPath . '/' . $safeFileName;

            // Move the file
            rename(__DIR__ . '/../../' . $tempImagePath, $newImagePath);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'id' => $categoryId
        ]);
    } catch (PDOException $e) {
        error_log("Error in createCategory: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in createCategory: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
    }
}

function updateCategory($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['name']) || trim($data['name']) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
            return;
        }

        $categoryId = $data['id'];
        $name = trim($data['name']);
        $description = isset($data['description']) ? trim($data['description']) : '';
        $metaTitle = isset($data['meta_title']) ? trim($data['meta_title']) : '';
        $metaDescription = isset($data['meta_description']) ? trim($data['meta_description']) : '';
        $metaKeywords = isset($data['meta_keywords']) ? trim($data['meta_keywords']) : '';
        $status = isset($data['status']) && in_array($data['status'], ['active', 'inactive']) ? $data['status'] : 'active';
        $tempImagePath = isset($data['temp_image_path']) ? trim($data['temp_image_path']) : '';
        $removeImage = isset($data['remove_image']) ? (bool)$data['remove_image'] : false;

        $binaryId = uuidToBin($categoryId);

        $stmt = $pdo->prepare("SELECT id, name FROM product_categories WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        $oldName = $category['name'];

        $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE name = :name AND id != :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
            return;
        }

        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("UPDATE product_categories SET name = :name, description = :description, meta_title = :meta_title, meta_description = :meta_description, meta_keywords = :meta_keywords, status = :status, updated_at = :updated_at WHERE id = :id");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':meta_title', $metaTitle);
        $stmt->bindParam(':meta_description', $metaDescription);
        $stmt->bindParam(':meta_keywords', $metaKeywords);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':updated_at', $now);
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        // Handle image updates
        $categoryDirPath = __DIR__ . '/../../img/product-categories/' . $categoryId;

        // Create category directory if it doesn't exist
        if (!file_exists($categoryDirPath)) {
            mkdir($categoryDirPath, 0755, true);
        }

        // If remove image flag is set, delete all images in the category directory
        if ($removeImage) {
            deleteAllCategoryImages($categoryId);
        }
        // If a new image was uploaded, move it to the permanent location
        else if (!empty($tempImagePath) && file_exists(__DIR__ . '/../../' . $tempImagePath)) {
            // First, remove any existing images
            deleteAllCategoryImages($categoryId);

            // Then move the new image
            $fileExt = pathinfo($tempImagePath, PATHINFO_EXTENSION);
            $safeFileName = sanitizeFileName($name) . '.' . $fileExt;
            $newImagePath = $categoryDirPath . '/' . $safeFileName;

            rename(__DIR__ . '/../../' . $tempImagePath, $newImagePath);
        }
        // If the name changed but no new image was uploaded, rename the existing image if it exists
        else if ($oldName !== $name) {
            renameExistingCategoryImage($categoryId, $oldName, $name);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in updateCategory: " . $e->getMessage());
        if ($e->getCode() == 23000) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in updateCategory: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
    }
}

function deleteCategory($pdo)
{
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing category ID']);
            return;
        }

        $categoryId = $data['id'];
        $binaryId = uuidToBin($categoryId);

        $stmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = :id");
        $stmt->bindParam(':id', $binaryId, PDO::PARAM_LOB);
        $stmt->execute();

        // Delete category image directory
        deleteAllCategoryImages($categoryId, true);

        echo json_encode([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    } catch (PDOException $e) {
        error_log("Error in deleteCategory: " . $e->getMessage());
        if ($e->getCode() == '23000') {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete this category because it is being used by one or more products']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        error_log("Error in deleteCategory: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]);
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
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($fileExt, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, WebP, and GIF files are allowed.']);
            return;
        }

        if ($fileSize > 5000000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.']);
            return;
        }

        // Create temp directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newFileName = uniqid('temp_') . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;
        $relativePath = 'uploads/temp/' . $newFileName;

        list($width, $height) = getimagesize($fileTmpName);
        $targetWidth = 1200;
        $targetHeight = 675;

        $sourceImage = null;
        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                $sourceImage = imagecreatefromjpeg($fileTmpName);
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($fileTmpName);
                break;
            case 'webp':
                $sourceImage = imagecreatefromwebp($fileTmpName);
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($fileTmpName);
                break;
        }

        if (!$sourceImage) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to process image']);
            return;
        }

        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        imagefill($targetImage, 0, 0, imagecolorallocate($targetImage, 255, 255, 255));

        $sourceRatio = $width / $height;
        $targetRatio = $targetWidth / $targetHeight;

        if ($sourceRatio > $targetRatio) {
            $newWidth = $height * $targetRatio;
            $newHeight = $height;
            $sourceX = ($width - $newWidth) / 2;
            $sourceY = 0;
        } else {
            $newWidth = $width;
            $newHeight = $width / $targetRatio;
            $sourceX = 0;
            $sourceY = ($height - $newHeight) / 2;
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            $sourceX,
            $sourceY,
            $targetWidth,
            $targetHeight,
            $newWidth,
            $newHeight
        );

        switch ($fileExt) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($targetImage, $uploadPath, 90);
                break;
            case 'png':
                imagepng($targetImage, $uploadPath, 9);
                break;
            case 'webp':
                imagewebp($targetImage, $uploadPath, 90);
                break;
            case 'gif':
                imagegif($targetImage, $uploadPath);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

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

// Helper functions

function sanitizeFileName($name)
{
    // Replace spaces with hyphens
    $name = str_replace(' ', '-', $name);
    // Remove any non-alphanumeric characters except hyphens and underscores
    $name = preg_replace('/[^A-Za-z0-9\-_]/', '', $name);
    // Convert to lowercase
    $name = strtolower($name);
    return $name;
}

function getCategoryImageUrl($categoryId, $categoryName)
{
    $baseDir = 'img/product-categories/' . $categoryId . '/';
    $safeName = sanitizeFileName($categoryName);

    // Check for common image extensions
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

    foreach ($extensions as $ext) {
        $filePath = $baseDir . $safeName . '.' . $ext;
        if (file_exists(__DIR__ . '/../../' . $filePath)) {
            return BASE_URL . $filePath;
        }
    }

    // Return null if no image found
    return null;
}

function doesCategoryImageExist($categoryId, $categoryName)
{
    $baseDir = __DIR__ . '/../../img/product-categories/' . $categoryId . '/';
    $safeName = sanitizeFileName($categoryName);

    // Check for common image extensions
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

    foreach ($extensions as $ext) {
        $filePath = $baseDir . $safeName . '.' . $ext;
        if (file_exists($filePath)) {
            return true;
        }
    }

    return false;
}

function deleteAllCategoryImages($categoryId, $removeDir = false)
{
    $categoryDir = __DIR__ . '/../../img/product-categories/' . $categoryId;

    if (file_exists($categoryDir)) {
        $files = glob($categoryDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if ($removeDir) {
            rmdir($categoryDir);
        }
    }
}

function renameExistingCategoryImage($categoryId, $oldName, $newName)
{
    $categoryDir = __DIR__ . '/../../img/product-categories/' . $categoryId;
    $safeOldName = sanitizeFileName($oldName);
    $safeNewName = sanitizeFileName($newName);

    // Check for common image extensions
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];

    foreach ($extensions as $ext) {
        $oldPath = $categoryDir . '/' . $safeOldName . '.' . $ext;
        if (file_exists($oldPath)) {
            $newPath = $categoryDir . '/' . $safeNewName . '.' . $ext;
            rename($oldPath, $newPath);
            break;
        }
    }
}
