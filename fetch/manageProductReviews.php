<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../lib/NotificationService.php';

use Ulid\Ulid;

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$currentUser = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;

if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid user session']);
    exit;
}

$userStmt = $pdo->prepare("SELECT username FROM zzimba_users WHERE id = ? ");
        $userStmt->execute([$currentUser]);
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $userData['username'];

// Create reviews table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_reviews (
        id VARCHAR(26) PRIMARY KEY,
        product_id VARCHAR(26) NOT NULL,
        user_id VARCHAR(64) NOT NULL,
        rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT NOT NULL,
        is_verified TINYINT(1) DEFAULT 0,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    )");
} catch (PDOException $e) {
    error_log("Reviews table creation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database setup failed']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'submit_review':
        submitReview($pdo, $currentUser, $username);
        break;
    case 'get_reviews':
        getReviews($pdo);
        break;
    case 'get_user_review':
        getUserReview($pdo, $currentUser);
        break;
    case 'getStoreReviews':
        try {
            $storeId = $_GET['store_id'] ?? null;
            
            if (!$storeId) {
                echo json_encode(['success' => false, 'error' => 'Store ID is required']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT 
                    pr.id,
                    pr.rating,
                    pr.review_text,
                    pr.created_at,
                    pr.status,
                    p.name as product_name,
                    COALESCE(u.name, u.username, 'Anonymous') as reviewer_name
                FROM product_reviews pr
                INNER JOIN products p ON pr.product_id = p.id
                INNER JOIN vendor_stores vs ON p.store_id = vs.id
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE vs.id = ? AND pr.status = 'approved'
                ORDER BY pr.created_at DESC
            ");
            
            $stmt->execute([$storeId]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("Error fetching store reviews: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch reviews'
            ]);
            exit;
        }
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function submitReview(PDO $pdo, string $currentUser, string $username)
{
    $productId = trim($_POST['product_id'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $comment = strip_tags($comment);                       
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    $comment = preg_replace('/\bhttps?:\/\/[^\s]+/i', '[link removed]', $comment);
    $comment = str_replace(['<', '>', '{', '}'], '', $comment);


    if (empty($productId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }

    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Rating must be between 1 and 5']);
        return;
    }

    if (empty($comment) || strlen($comment) < 10) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Comment must be at least 10 characters long']);
        return;
    }

    if (strlen($comment) > 500) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Comment must be less than 500 characters']);
        return;
    }

    // Check if product exists
    $productStmt = $pdo->prepare("SELECT id, title FROM products WHERE id = ? AND status = 'published'");
    $productStmt->execute([$productId]);
    $product = $productStmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $reviewId = generateUlid();

        
        $insertStmt = $pdo->prepare("
            INSERT INTO product_reviews (id, product_id, user_id, rating, comment, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([$reviewId, $productId, $currentUser, $rating, $comment, $now, $now]);

        // Send admin notification
        try {

            $notificationService = new NotificationService($pdo);
            $adminMessage = "Product Review: {$username} Commented and Rated \"{$product['title']}\" {$rating}/5 stars.";

            $recipients = [
                 [
                    'type' => 'admin',
                    'id' => 'admin',
                    'message' => $adminMessage
                ]
            ];

            $notificationService->create(
                'info',
                'Product Review',
                $recipients,
                BASE_URL . "/product-details.php?id={$productId}#reviews",
                'normal',
                $currentUser
            );

        } catch (Exception $notifError) {
            error_log('Review notification creation failed: ' . $notifError->getMessage());  
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error submitting review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to submit review']);
    }
}

function getReviews(PDO $pdo)
{
    $productId = trim($_GET['product_id'] ?? '');
    
    if (empty($productId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }

    try {
        // Get reviews with pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $pdo->prepare("
            SELECT 
                id,
                username,
                rating,
                comment,
                is_verified,
                created_at,
                DATE_FORMAT(created_at, '%Y-%m-%d') as review_date
            FROM product_reviews 
            WHERE product_id = ? AND status = 'approved'
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$productId, $limit, $offset]);
        $reviews = $stmt->fetchAll();

        // Get total count and average rating
        $countStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM product_reviews 
            WHERE product_id = ? AND status = 'approved'
        ");
        $countStmt->execute([$productId]);
        $stats = $countStmt->fetch();

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'stats' => [
                'total_reviews' => intval($stats['total_reviews']),
                'average_rating' => round(floatval($stats['average_rating']), 1),
                'rating_breakdown' => [
                    5 => intval($stats['five_star']),
                    4 => intval($stats['four_star']),
                    3 => intval($stats['three_star']),
                    2 => intval($stats['two_star']),
                    1 => intval($stats['one_star'])
                ]
            ],
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil(intval($stats['total_reviews']) / $limit),
                'has_more' => (intval($stats['total_reviews']) > ($page * $limit))
            ]
        ]);

    } catch (Exception $e) {
        error_log('Error fetching reviews: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch reviews']);
    }
}

function getUserReview(PDO $pdo, string $currentUser)
{
    $productId = trim($_GET['product_id'] ?? '');
    
    if (empty($productId) || !isValidUlid($productId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT rating, comment, created_at
            FROM product_reviews 
            WHERE user_id = ? AND product_id = ?
        ");
        $stmt->execute([$currentUser, $productId]);
        $review = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'has_review' => !empty($review),
            'review' => $review ?: null
        ]);

    } catch (Exception $e) {
        error_log('Error fetching user review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to fetch user review']);
    }
}

function isValidUlid(string $ulid): bool
{
    return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $ulid) === 1;
}
?>