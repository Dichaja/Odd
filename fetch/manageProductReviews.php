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

$userStmt = $pdo->prepare("
    SELECT username FROM zzimba_users WHERE id = ?
    UNION
    SELECT username FROM admin_users WHERE id = ?
");
$userStmt->execute([$currentUser, $currentUser]);
$userData = $userStmt->fetch(PDO::FETCH_ASSOC);
$username = $userData ? $userData['username'] : 'Unknown';

try {

    $tableExists = $pdo->query("SHOW TABLES LIKE 'general_reviews'")->rowCount() > 0;

    if (!$tableExists) {
        $pdo->exec("
            CREATE TABLE general_reviews (
                id VARCHAR(26) PRIMARY KEY,
                review_entity VARCHAR(36) NOT NULL,
                user_id VARCHAR(64) NOT NULL,
                rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
                comment TEXT NOT NULL,
                is_verified TINYINT(1) DEFAULT 0,
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
                entity_type ENUM('product', 'store') DEFAULT 'product',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL
            )
        ");
    } else {
        $columnCheck = $pdo->query("SHOW COLUMNS FROM general_reviews LIKE 'product_id'")->fetch();
        if ($columnCheck) {
            $pdo->exec("ALTER TABLE general_reviews CHANGE product_id review_entity VARCHAR(36) NOT NULL");
        }

    $entityTypeCheck = $pdo->query("SHOW COLUMNS FROM general_reviews LIKE 'entity_type'")->fetch();
        if (!$entityTypeCheck) {
            $pdo->exec("ALTER TABLE general_reviews ADD COLUMN entity_type ENUM('product', 'store') DEFAULT 'product' AFTER review_entity");
        }
    }
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
    case 'list':
        listReviews($pdo);
        break;
    case 'stats':
        getReviewStats($pdo);
        break;
    case 'update_status':
        updateReviewStatus($pdo);
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
    case 'getStoreReviews':
        try {
            $storeId = $_GET['store_id'] ?? null;
            
            if (!$storeId) {
                echo json_encode(['success' => false, 'error' => 'Store ID is required']);
                exit;
            }

            // Get reviews for this store
            $stmt = $pdo->prepare("
                SELECT 
                    pr.id,
                    pr.rating,
                    pr.comment as review_text,
                    pr.created_at,
                    pr.status,
                    COALESCE(u.username, 'Anonymous') as reviewer_name,
                    'Store Review' as product_name
                FROM general_reviews pr
                LEFT JOIN zzimba_users u ON pr.user_id = u.id
                WHERE pr.review_entity = ? AND pr.status = 'approved'
                ORDER BY pr.created_at DESC
            ");
            
            $stmt->execute([$storeId]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get review statistics
            $statsStmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_reviews,
                    AVG(pr.rating) as average_rating,
                    SUM(CASE WHEN pr.rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN pr.rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN pr.rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN pr.rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN pr.rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM general_reviews pr
                WHERE pr.review_entity = ? AND pr.status = 'approved'
            ");
            $statsStmt->execute([$storeId]);
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

            $reviewStats = [
                'total_reviews' => intval($stats['total_reviews']),
                'average_rating' => round(floatval($stats['average_rating']), 1),
                'rating_breakdown' => [
                    5 => intval($stats['five_star']),
                    4 => intval($stats['four_star']),
                    3 => intval($stats['three_star']),
                    2 => intval($stats['two_star']),
                    1 => intval($stats['one_star'])
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'stats' => $reviewStats
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("Error fetching store reviews: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch reviews' . $e->getMessage()
            ]);
            exit;
        }
    case 'submit_store_review':
        submitStoreReview($pdo, $currentUser, $username);
        break;
    case 'submit_platform_review':
        submitPlatformReview($pdo, $currentUser, $username);
        break;
    case 'getPlatformReviews':
        getPlatformReviews($pdo);
        break;
    case 'approve':
        approveReview($pdo);
        break;
    case 'verify':
        verifyReview($pdo);
        break;
    case 'reject':
        rejectReview($pdo);
        break;
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
            INSERT INTO general_reviews (id, review_entity, user_id, rating, comment, created_at, updated_at)
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
            FROM general_reviews 
            WHERE review_entity = ? AND status = 'approved'
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
            FROM general_reviews 
            WHERE review_entity = ? AND status = 'approved'
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
            FROM general_reviews 
            WHERE user_id = ? AND review_entity = ?
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

function listReviews(PDO $pdo) {
    try {
        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = max(1, min(100, intval($_GET['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        // Filter by product
        if (!empty($_GET['product_id'])) {
            $where[] = "pr.product_id = ?";
            $params[] = $_GET['product_id'];
        }

        // Filter by vendor/store
        if (!empty($_GET['vendor_id'])) {
            $where[] = "pr.store_id = ?";
            $params[] = $_GET['vendor_id'];
        }

        // Filter by review type (platform, product, store)
        if (!empty($_GET['review_type'])) {
            if ($_GET['review_type'] === 'platform') {
                $where[] = "pr.review_type = 'platform'";
            } elseif ($_GET['review_type'] === 'product') {
                $where[] = "pr.product_id IS NOT NULL";
            } elseif ($_GET['review_type'] === 'store') {
                $where[] = "pr.store_id IS NOT NULL AND pr.review_type != 'platform'";
            }
        }

        // Filter by status
        if (!empty($_GET['status'])) {
            $where[] = "pr.status = ?";
            $params[] = $_GET['status'];
        }

        // Filter by rating
        if (!empty($_GET['rating'])) {
            $where[] = "pr.rating = ?";
            $params[] = intval($_GET['rating']);
        }

        // Search
        if (!empty($_GET['search'])) {
            $where[] = "(pr.comment LIKE ? OR p.title LIKE ? OR vs.name LIKE ? OR u.username LIKE ?)";
            $searchTerm = '%' . $_GET['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countSql = "SELECT COUNT(*) as total 
                     FROM product_reviews pr
                     LEFT JOIN products p ON pr.product_id = p.id
                     LEFT JOIN vendor_stores vs ON pr.store_id = vs.id
                     LEFT JOIN zzimba_users u ON pr.user_id = u.id
                     $whereClause";
        
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Get reviews
        $sql = "SELECT 
                    pr.id,
                    pr.product_id,
                    pr.store_id,
                    pr.rating,
                    pr.comment,
                    pr.status,
                    pr.is_verified,
                    pr.created_at,
                    pr.review_type,
                    p.title as product_title,
                    vs.name as store_name,
                    u.username,
                    CASE 
                        WHEN pr.review_type = 'platform' THEN 'platform'
                        WHEN pr.product_id IS NOT NULL THEN 'product'
                        WHEN pr.store_id IS NOT NULL THEN 'store'
                        ELSE 'unknown'
                    END as review_type_display,
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as product_image
                FROM product_reviews pr
                LEFT JOIN products p ON pr.product_id = p.id
                LEFT JOIN vendor_stores vs ON pr.store_id = vs.id
                LEFT JOIN zzimba_users u ON pr.user_id = u.id
                $whereClause
                ORDER BY pr.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($params, [$perPage, $offset]));
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert is_verified to boolean
        foreach ($reviews as &$review) {
            $review['is_verified'] = (bool)($review['is_verified'] ?? false);
        }

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'pagination' => [
                'total' => $total,
                'totalPages' => ceil($total / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage
            ]
        ]);

    } catch (Exception $e) {
        error_log('Error listing reviews: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to load reviews']);
    }
}

function getReviewStats(PDO $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                AVG(rating) as avgRating
            FROM general_reviews
        ");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'stats' => [
                'total' => intval($stats['total']),
                'pending' => intval($stats['pending']),
                'approved' => intval($stats['approved']),
                'avgRating' => round(floatval($stats['avgRating']), 1)
            ]
        ]);

    } catch (Exception $e) {
        error_log('Error getting review stats: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to load stats']);
    }
}

function updateReviewStatus(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewId = $data['review_id'] ?? '';
    $status = $data['status'] ?? '';

    if (!in_array($status, ['approved', 'pending', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE general_reviews SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $reviewId]);

        echo json_encode(['success' => true, 'message' => "Review $status successfully"]);

    } catch (Exception $e) {
        error_log('Error updating review status: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update review']);
    }
}

function deleteReview(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewId = $data['review_id'] ?? '';

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
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => true, 'products' => []]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, title 
            FROM products 
            WHERE title LIKE ? AND status = 'published'
            LIMIT 10
        ");
        $stmt->execute(['%' . $query . '%']);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'products' => $products]);

    } catch (Exception $e) {
        error_log('Error searching products: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Search failed']);
    }
}

function searchVendors(PDO $pdo) {
    $query = $_GET['q'] ?? '';
    
    if (strlen($query) < 2) {
        echo json_encode(['success' => true, 'vendors' => []]);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, name 
            FROM vendor_stores 
            WHERE name LIKE ? AND status = 'active'
            LIMIT 10
        ");
        $stmt->execute(['%' . $query . '%']);
        $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'vendors' => $vendors]);

    } catch (Exception $e) {
        error_log('Error searching vendors: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Search failed']);
    }
}

function submitStoreReview(PDO $pdo, string $currentUser, string $username)
{
    $storeId = trim($_POST['store_id'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $comment = strip_tags($comment);                       
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    $comment = preg_replace('/\bhttps?:\/\/[^\s]+/i', '[link removed]', $comment);
    $comment = str_replace(['<', '>', '{', '}'], '', $comment);

    if (empty($storeId) || !isValidUlid($storeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid store ID']);
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

    // Check if store exists
    $storeStmt = $pdo->prepare("SELECT id, name FROM vendor_stores WHERE id = ? AND status = 'active'");
    $storeStmt->execute([$storeId]);
    $store = $storeStmt->fetch();

    if (!$store) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Store not found']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $reviewId = generateUlid();

        // Insert review with store_id instead of product_id
        $insertStmt = $pdo->prepare("
            INSERT INTO general_reviews (id, review_entity, user_id, rating, comment, created_at, updated_at, entity_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'store')");
        $insertStmt->execute([$reviewId, $storeId, $currentUser, $rating, $comment, $now, $now]);

        // Send admin notification
        try {
            $notificationService = new NotificationService($pdo);

           // Check if store exists and get owner
           $storeStmt = $pdo->prepare("SELECT id, name, owner_id FROM vendor_stores WHERE id = ? AND status = 'active'");
           $storeStmt->execute([$storeId]);
           $store = $storeStmt->fetch();
           $ownerId = $store['owner_id'] ?? null;

           if (!$store) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Store not found']);
                return;
             }

            $reviewUrl = BASE_URL . "/vendor-profile.php?id={$storeId}#reviews";

            $adminMessage = "Store Review: {$username} has just reviewed and rated \"{$store['name']}\" with {$rating}/5 stars."; 
            $vendorMessage = "{$username} has just reviewed your store \"{$store['name']}\" with a {$rating}-star rating.";

           $recipients = [
               [
                 'type' => 'admin',
                 'id' => 'admin',
                 'message' => $adminMessage
               ]
             ];

            if ($ownerId) {
               $recipients[] = [
                 'type' => 'user',
                 'id' => $ownerId,
                 'message' => $vendorMessage
              ];
            }

        foreach ($recipients as $recipient) {
                    $notificationService->create(
                        'info', 
                        'Store Review', 
                        [$recipient], 
                        $reviewUrl, 
                        'normal', 
                        $currentUser);
                }

        } catch (Exception $notifError) {
            error_log('Store review notification creation failed: ' . $notifError->getMessage());  
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully and is pending approval']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error submitting store review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to submit review']);
    }
}

function submitPlatformReview(PDO $pdo, string $currentUser, string $username)
{
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $comment = strip_tags($comment);                       
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    $comment = preg_replace('/\bhttps?:\/\/[^\s]+/i', '[link removed]', $comment);
    $comment = str_replace(['<', '>', '{', '}'], '', $comment);

    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Rating must be between 1 and 5']);
        return;
    }

    if (empty($comment) || strlen($comment) < 10) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Review must be at least 10 characters long']);
        return;
    }

    if (strlen($comment) > 500) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Review must be less than 500 characters']);
        return;
    }

    try {
        $pdo->beginTransaction();

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $reviewId = generateUlid();

        // Insert platform review (no product_id or store_id)
        $insertStmt = $pdo->prepare("
            INSERT INTO general_reviews (id, review_entity, user_id, rating, comment, created_at, updated_at, status, entity_type)
            VALUES (?, 'platform', ?, ?, ?, ?, ?, 'approved', 'platform')
        ");
        $insertStmt->execute([$reviewId, $currentUser, $rating, $comment, $now, $now]);

        // Send admin notification
        try {
            $notificationService = new NotificationService($pdo);
            $adminMessage = "Platform Review: {$username} rated Zzimba Online {$rating}/5 stars.";

            $recipients = [
                [
                    'type' => 'admin',
                    'id' => 'admin',
                    'message' => $adminMessage
                ]
            ];

            $notificationService->create(
                'info',
                'Platform Review',
                $recipients,
                BASE_URL . "faq",
                'normal',
                $currentUser
            );

        } catch (Exception $notifError) {
            error_log('Platform review notification creation failed: ' . $notifError->getMessage());  
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Thank you for your review! It will be published after approval.']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error submitting platform review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to submit review']);
    }
}

function getPlatformReviews(PDO $pdo)
{
    try {
        $limit = intval($_GET['limit'] ?? 6);
        $limit = max(1, min(50, $limit));

        $stmt = $pdo->prepare("
            SELECT 
                pr.id,
                pr.rating,
                pr.comment as review_text,
                pr.created_at,
                pr.is_verified,
                COALESCE(u.name, u.username, 'Anonymous') as reviewer_name
            FROM product_reviews pr
            LEFT JOIN zzimba_users u ON pr.user_id = u.id
            WHERE pr.review_type = 'platform' AND pr.status = 'approved'
            ORDER BY pr.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert is_verified to boolean
        foreach ($reviews as &$review) {
            $review['is_verified'] = (bool)($review['is_verified'] ?? false);
        }

        echo json_encode([
            'success' => true,
            'reviews' => $reviews
        ]);

    } catch (Exception $e) {
        error_log("Error fetching platform reviews: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Failed to fetch reviews'
        ]);
    }
}

function approveReview(PDO $pdo)
{
    $reviewId = $_POST['review_id'] ?? null;

    if (!$reviewId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Review ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE product_reviews SET status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$reviewId]);

        echo json_encode(['success' => true, 'message' => 'Review approved successfully']);
    } catch (Exception $e) {
        error_log('Error approving review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to approve review']);
    }
}

function verifyReview(PDO $pdo)
{
    $reviewId = $_POST['review_id'] ?? null;

    if (!$reviewId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Review ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE product_reviews SET is_verified = 1, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$reviewId]);

        echo json_encode(['success' => true, 'message' => 'Review verified successfully']);
    } catch (Exception $e) {
        error_log('Error verifying review: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to verify review']);
    }
}

function rejectReview(PDO $pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $reviewId = $data['review_id'] ?? '';
    $status = $data['status'] ?? '';

    if (!in_array($status, ['approved', 'pending', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE general_reviews SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $reviewId]);

        echo json_encode(['success' => true, 'message' => "Review $status successfully"]);

    } catch (Exception $e) {
        error_log('Error updating review status: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update review']);
    }
}

function isValidUlid(string $ulid): bool
{
    return preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/i', $ulid) === 1;
}
?>