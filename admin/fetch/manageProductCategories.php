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
    // Ensure 'featured' column exists in creation
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS product_categories (
            id VARCHAR(26) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            meta_title VARCHAR(100),
            meta_description TEXT,
            meta_keywords VARCHAR(255),
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            featured TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE KEY name_unique (name)
        )"
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
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
        case 'updateFeatured':
            updateFeatured($pdo);
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
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function getCategories(PDO $pdo)
{
    try {
        // Fetch categories along with the count of products and featured flag
        $stmt = $pdo->query(
            "SELECT 
                pc.id,
                pc.name,
                pc.description,
                pc.meta_title,
                pc.meta_description,
                pc.meta_keywords,
                pc.status,
                pc.featured,
                pc.created_at,
                pc.updated_at,
                (
                    SELECT COUNT(*) 
                    FROM products p 
                    WHERE p.category_id = pc.id
                ) AS product_count
             FROM product_categories pc
             ORDER BY pc.name"
        );
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as &$c) {
            // Add image URL without removing the ID
            $c['image_url'] = getCategoryImageUrl($c['id'], $c['name']);
            // Ensure product_count and featured are proper types
            $c['product_count'] = (int) $c['product_count'];
            $c['featured'] = (int) $c['featured'];
        }
        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving categories']);
    }
}

function getCategory(PDO $pdo)
{
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing category ID']);
        return;
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT 
                pc.id,
                pc.name,
                pc.description,
                pc.meta_title,
                pc.meta_description,
                pc.meta_keywords,
                pc.status,
                pc.featured,
                pc.created_at,
                pc.updated_at,
                (
                    SELECT COUNT(*) 
                    FROM products p 
                    WHERE p.category_id = pc.id
                ) AS product_count
             FROM product_categories pc
             WHERE pc.id = :id'
        );
        $stmt->execute([':id' => $_GET['id']]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$c) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        // Add image URL and has_image flag without removing the ID
        $c['image_url'] = getCategoryImageUrl($c['id'], $c['name']);
        $c['has_image'] = doesCategoryImageExist($c['id'], $c['name']);
        $c['product_count'] = (int) $c['product_count'];
        $c['featured'] = (int) $c['featured'];

        echo json_encode(['success' => true, 'data' => $c]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error retrieving category']);
    }
}

function createCategory(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['name']) || trim($data['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        return;
    }
    $name = trim($data['name']);
    $description = trim($data['description'] ?? '');
    $metaTitle = trim($data['meta_title'] ?? '');
    $metaDescription = trim($data['meta_description'] ?? '');
    $metaKeywords = trim($data['meta_keywords'] ?? '');
    $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $featured = isset($data['featured']) && (int) $data['featured'] === 1 ? 1 : 0;
    $tempImagePath = trim($data['temp_image_path'] ?? '');
    try {
        $stmt = $pdo->prepare('SELECT id FROM product_categories WHERE name = :name');
        $stmt->execute([':name' => $name]);
        if ($stmt->rowCount()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
            return;
        }
        $id = generateUlid();
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'INSERT INTO product_categories
             (id,name,description,meta_title,meta_description,meta_keywords,status,featured,created_at,updated_at)
             VALUES
             (:id,:name,:description,:meta_title,:meta_description,:meta_keywords,:status,:featured,:created_at,:updated_at)'
        );
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':meta_keywords' => $metaKeywords,
            ':status' => $status,
            ':featured' => $featured,
            ':created_at' => $now,
            ':updated_at' => $now
        ]);
        if ($tempImagePath && file_exists(__DIR__ . '/../../' . $tempImagePath)) {
            $ext = pathinfo($tempImagePath, PATHINFO_EXTENSION);
            $dir = __DIR__ . '/../../img/product-categories/' . $id;
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $safe = sanitizeFileName($name) . ".$ext";
            rename(__DIR__ . '/../../' . $tempImagePath, "$dir/$safe");
        }
        echo json_encode(['success' => true, 'message' => 'Category created', 'id' => $id]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creating category']);
    }
}

function updateCategory(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['name']) || trim($data['name']) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
        return;
    }
    $id = $data['id'];
    $name = trim($data['name']);
    $description = trim($data['description'] ?? '');
    $metaTitle = trim($data['meta_title'] ?? '');
    $metaDescription = trim($data['meta_description'] ?? '');
    $metaKeywords = trim($data['meta_keywords'] ?? '');
    $status = in_array($data['status'] ?? '', ['active', 'inactive']) ? $data['status'] : 'active';
    $featured = isset($data['featured']) && (int) $data['featured'] === 1 ? 1 : 0;
    $tempImagePath = trim($data['temp_image_path'] ?? '');
    $removeImage = !empty($data['remove_image']);
    try {
        $stmt = $pdo->prepare('SELECT name FROM product_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $old = $stmt->fetchColumn();
        if (!$old) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        $stmt = $pdo->prepare(
            'SELECT id FROM product_categories WHERE name = :name AND id != :id'
        );
        $stmt->execute([':name' => $name, ':id' => $id]);
        if ($stmt->rowCount()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'A category with this name already exists']);
            return;
        }
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'UPDATE product_categories SET
             name = :name,
             description = :description,
             meta_title = :meta_title,
             meta_description = :meta_description,
             meta_keywords = :meta_keywords,
             status = :status,
             featured = :featured,
             updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':meta_title' => $metaTitle,
            ':meta_description' => $metaDescription,
            ':meta_keywords' => $metaKeywords,
            ':status' => $status,
            ':featured' => $featured,
            ':updated_at' => $now,
            ':id' => $id
        ]);
        $dir = __DIR__ . '/../../img/product-categories/' . $id;
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        if ($removeImage) {
            deleteAllCategoryImages($id, true);
        } elseif ($tempImagePath && file_exists(__DIR__ . '/../../' . $tempImagePath)) {
            deleteAllCategoryImages($id);
            $ext = pathinfo($tempImagePath, PATHINFO_EXTENSION);
            $safe = sanitizeFileName($name) . ".$ext";
            rename(__DIR__ . '/../../' . $tempImagePath, "$dir/$safe");
        } elseif ($old !== $name) {
            renameExistingCategoryImage($id, $old, $name);
        }
        echo json_encode(['success' => true, 'message' => 'Category updated']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating category']);
    }
}

function deleteCategory(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing category ID']);
        return;
    }
    $id = $data['id'];
    try {
        $stmt = $pdo->prepare('SELECT id FROM product_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        $stmt = $pdo->prepare('DELETE FROM product_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        deleteAllCategoryImages($id, true);
        echo json_encode(['success' => true, 'message' => 'Category deleted']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error deleting category']);
    }
}

function updateFeatured(PDO $pdo)
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || !isset($data['featured'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing category ID or featured flag']);
        return;
    }
    $id = $data['id'];
    $featured = (int) $data['featured'] === 1 ? 1 : 0;
    try {
        $stmt = $pdo->prepare('SELECT id FROM product_categories WHERE id = :id');
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Category not found']);
            return;
        }
        $now = (new DateTime('now', new DateTimeZone('Africa/Kampala')))->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            'UPDATE product_categories
             SET featured = :featured, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':featured' => $featured,
            ':updated_at' => $now,
            ':id' => $id
        ]);
        echo json_encode(['success' => true, 'message' => 'Featured flag updated']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating featured flag']);
    }
}

function uploadImage()
{
    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No image uploaded']);
        return;
    }
    try {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed) || $file['size'] > 5_000_000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid file']);
            return;
        }
        $dir = __DIR__ . '/../../uploads/temp/';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = uniqid('temp_') . ".$ext";
        $dest = $dir . $name;
        $rel = 'uploads/temp/' . $name;
        list($w, $h) = getimagesize($file['tmp_name']);
        $tw = 1200;
        $th = 675;
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $src = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'png':
                $src = imagecreatefrompng($file['tmp_name']);
                break;
            case 'webp':
                $src = imagecreatefromwebp($file['tmp_name']);
                break;
            case 'gif':
                $src = imagecreatefromgif($file['tmp_name']);
                break;
        }
        $dst = imagecreatetruecolor($tw, $th);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        $sr = $w / $h;
        $tr = $tw / $th;
        if ($sr > $tr) {
            $nw = $h * $tr;
            $nh = $h;
            $sx = ($w - $nw) / 2;
            $sy = 0;
        } else {
            $nw = $w;
            $nh = $w / $tr;
            $sx = 0;
            $sy = ($h - $nh) / 2;
        }
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $nw, $nh);
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($dst, $dest, 90);
                break;
            case 'png':
                imagepng($dst, $dest, 9);
                break;
            case 'webp':
                imagewebp($dst, $dest, 90);
                break;
            case 'gif':
                imagegif($dst, $dest);
                break;
        }
        imagedestroy($src);
        imagedestroy($dst);
        echo json_encode([
            'success' => true,
            'temp_path' => $rel,
            'url' => BASE_URL . $rel
        ]);
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error uploading image']);
    }
}

function sanitizeFileName($name)
{
    $name = str_replace(' ', '-', $name);
    $name = preg_replace('/[^A-Za-z0-9\-_]/', '', $name);
    return strtolower($name);
}

function getCategoryImageUrl($categoryId, $categoryName)
{
    $base = 'img/product-categories/' . $categoryId . '/';
    $safe = sanitizeFileName($categoryName);
    foreach (['webp', 'jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $file = "$base$safe.$ext";
        if (file_exists(__DIR__ . '/../../' . $file)) {
            return BASE_URL . $file;
        }
    }
    return null;
}

function doesCategoryImageExist($categoryId, $categoryName)
{
    $dir = __DIR__ . '/../../img/product-categories/' . $categoryId . '/';
    $safe = sanitizeFileName($categoryName);
    foreach (['webp', 'jpg', 'jpeg', 'png', 'gif'] as $ext) {
        if (file_exists("$dir$safe.$ext")) {
            return true;
        }
    }
    return false;
}

function deleteAllCategoryImages($categoryId, $removeDir = false)
{
    $dir = __DIR__ . '/../../img/product-categories/' . $categoryId;
    if (file_exists($dir)) {
        foreach (glob("$dir/*") as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if ($removeDir) {
            rmdir($dir);
        }
    }
}

function renameExistingCategoryImage($categoryId, $oldName, $newName)
{
    $dir = __DIR__ . '/../../img/product-categories/' . $categoryId;
    $safeO = sanitizeFileName($oldName);
    $safeN = sanitizeFileName($newName);
    foreach (['webp', 'jpg', 'jpeg', 'png', 'gif'] as $ext) {
        $old = "$dir/$safeO.$ext";
        if (file_exists($old)) {
            rename($old, "$dir/$safeN.$ext");
            break;
        }
    }
}
