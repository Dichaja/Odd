<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

/* Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
*/

// Decode raw JSON if Content-Type is application/json
if (empty($_POST)) {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    if (is_array($jsonData)) {
        $_POST = $jsonData;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        listReviews($pdo);
        break;
    case 'stats':
        getStats($pdo);
        break;
    case 'update_status':
        updateStatus($pdo);
        break;
    case 'delete':
        deleteReview($pdo);
        break;
    case 'search_products':
        searchProducts($pdo);
        break;
    case 'search_vendors':
        searchVendors($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function listReviews(PDO $pdo)
{
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
    $offset = ($page - 1) * $perPage;

    $status = $_GET['status'] ?? '';
$rating = $_GET['rating'] ?? '';
$search = $_GET['search'] ?? '';
$productId = $_GET['product_id'] ?? '';
$vendorId = $_GET['vendor_id'] ?? '';
$filterType = $_GET['filter_type'] ?? '';

$where = [];
$params = [];

if (!empty($status)) {
    $where[] = "r.status = ?";
    $params[] = $status;
}

if (!empty($rating)) {
    $where[] = "r.rating = ?";
    $params[] = intval($rating);
}

if ($filterType === 'product') {
    $where[] = "r.entity_type = 'product'";
} elseif ($filterType === 'store') {
    $where[] = "r.entity_type = 'store'";
}

if (!empty($search)) {
    $where[] = "(r.comment LIKE ? OR u.username LIKE ? OR p.title LIKE ? OR vs.name LIKE ?)";
    $searchTerm = "%{$search}%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

if (!empty($productId)) {
    $where[] = "r.review_entity = ?";
    $params[] = $productId;
    $where[] = "r.entity_type = 'product'";
}

if (!empty($vendorId)) {
    $where[] = "r.review_entity = ?";
    $params[] = $vendorId;
    $where[] = "r.entity_type = 'store'";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM general_reviews r
        LEFT JOIN zzimba_users u ON r.user_id = u.id
        LEFT JOIN products p ON r.review_entity = p.id AND r.entity_type = 'product'
        LEFT JOIN vendor_stores vs ON r.review_entity = vs.id AND r.entity_type = 'store'
        {$whereClause}
    ");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];



 // Get reviews
 $stmt = $pdo->prepare("
    SELECT 
        r.id,
        r.entity_type,
        r.review_entity,
        r.user_id,
        r.rating,
        r.comment,
        r.is_verified,
        r.status,
        r.created_at,
        COALESCE(u.username, 'Anonymous') AS username,
        CASE 
            WHEN r.entity_type = 'product' THEN p.title
            WHEN r.entity_type = 'store' THEN vs.name
            ELSE 'General Review'
        END AS review_target,
        CASE 
            WHEN r.entity_type = 'product' THEN (
                SELECT image_url 
                FROM product_images 
                WHERE product_id = p.id AND is_primary = 1 
                LIMIT 1
            )
            
            ELSE NULL
        END AS review_image,
        p.id as product_id,
        p.title as product_title,
        vs.id as store_id,
        vs.name as store_name
    FROM general_reviews r
    LEFT JOIN zzimba_users u ON r.user_id = u.id
    LEFT JOIN products p ON r.review_entity = p.id AND r.entity_type = 'product'
    LEFT JOIN vendor_stores vs ON r.review_entity = vs.id AND r.entity_type = 'store'
    {$whereClause}
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");
        
$stmt->execute(array_merge($params, [$perPage, $offset]));
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);


        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => intval($total),
                'totalPages' => ceil($total / $perPage)
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error listing reviews: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to load reviews'] . $e->getMessage());
    }
}

function getStats(PDO $pdo)
{
    try {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                AVG(rating) as avg_rating
            FROM general_reviews
        ");
        
        $stats = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => intval($stats['total']),
                'pending' => intval($stats['pending']),
                'approved' => intval($stats['approved']),
                'rejected' => intval($stats['rejected']),
                'avgRating' => round(floatval($stats['avg_rating']), 1)
            ]
        ]);
    } catch (Exception $e) {
        error_log('Error getting stats: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to load stats']);
    }
}

function updateStatus(PDO $pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = $input['review_id'] ?? '';
    $status = $input['status'] ?? '';

    if (empty($reviewId) || !in_array($status, ['approved', 'pending', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE general_reviews 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $reviewId]);

        echo json_encode(['success' => true, 'message' => 'Review status updated successfully']);
    } catch (Exception $e) {
        error_log('Error updating review status: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update review status']);
    }
}

function deleteReview(PDO $pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = $input['review_id'] ?? '';

    if (empty($reviewId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid review ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM general_reviews WHERE id = ?");
        $stmt->execute([$reviewId]);

        echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
    } catch (Exception $e) {
        error_log('Error deleting review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete review']);
    }
}

function searchProducts(PDO $pdo) {
    $search = $_GET['q'] ?? '';
    
    if (empty($search)) {
        echo json_encode(['success' => true, 'products' => []]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                title,
                (SELECT image_url FROM product_images WHERE product_id = products.id AND is_primary = 1 LIMIT 1) as image_url
            FROM products 
            WHERE title LIKE ? 
            LIMIT 10
        ");
        
        $stmt->execute(["%{$search}%"]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        error_log('Error searching products: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to search products']);
    }
}

function searchVendors(PDO $pdo) {
    $search = $_GET['q'] ?? '';
    
    if (empty($search)) {
        echo json_encode(['success' => true, 'vendors' => []]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT 
                id,
                name,
                store_image
            FROM vendor_stores 
            WHERE name LIKE ? OR business_name LIKE ?
            LIMIT 10
        ");
        
        $stmt->execute(["%{$search}%", "%{$search}%"]);
        $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'vendors' => $vendors]);
    } catch (Exception $e) {
        error_log('Error searching vendors: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to search vendors']);
    }
}
?>
