<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
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

    if (!empty($search)) {
        $where[] = "(r.comment LIKE ? OR u.username LIKE ? OR p.title LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    try {
        // Get total count
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as total
            FROM product_reviews r
            INNER JOIN zzimba_users u ON r.user_id = u.id
            INNER JOIN products p ON r.product_id = p.id
            {$whereClause}
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Get reviews
        $stmt = $pdo->prepare("
            SELECT 
                r.id,
                r.product_id,
                r.user_id,
                r.rating,
                r.comment,
                r.is_verified,
                r.status,
                r.created_at,
                u.username,
                p.title as product_title,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
            FROM product_reviews r
            INNER JOIN zzimba_users u ON r.user_id = u.id
            INNER JOIN products p ON r.product_id = p.id
            {$whereClause}
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $reviews = $stmt->fetchAll();

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
        echo json_encode(['success' => false, 'error' => 'Failed to load reviews']);
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
            FROM product_reviews
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
            UPDATE product_reviews 
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
        $stmt = $pdo->prepare("DELETE FROM product_reviews WHERE id = ?");
        $stmt->execute([$reviewId]);

        echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
    } catch (Exception $e) {
        error_log('Error deleting review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete review']);
    }
}
?>
