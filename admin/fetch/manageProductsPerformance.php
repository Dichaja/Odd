<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';
date_default_timezone_set('Africa/Kampala');

function getProductsData($page = 1, $limit = 20, $startDate = null, $endDate = null, $period = 'month', $viewType = 'unique', $category = 'all', $sort = 'unique_desc', $status = 'all', $search = '')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE p.status != 'deleted'";
        $params = [];

        if ($status !== 'all') {
            switch ($status) {
                case 'published':
                    $whereClause .= " AND p.status = 'published'";
                    break;
                case 'featured':
                    $whereClause .= " AND p.featured = 1";
                    break;
                case 'with_pricing':
                    $whereClause .= " AND EXISTS(SELECT 1 FROM store_products sp JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id)";
                    break;
            }
        }

        if ($category !== 'all') {
            $whereClause .= " AND p.category_id = ?";
            $params[] = $category;
        }

        if (!empty($search)) {
            $whereClause .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $orderBy = "ORDER BY ";
        switch ($sort) {
            case 'unique_desc':
                $orderBy .= "unique_views DESC";
                break;
            case 'unique_asc':
                $orderBy .= "unique_views ASC";
                break;
            case 'cumulative_desc':
                $orderBy .= "total_views DESC";
                break;
            case 'cumulative_asc':
                $orderBy .= "total_views ASC";
                break;
            case 'recent':
                $orderBy .= "last_viewed DESC NULLS LAST";
                break;
            case 'title':
                $orderBy .= "p.title ASC";
                break;
            default:
                $orderBy .= "unique_views DESC";
        }

        $countStmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id) as total
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            $whereClause
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        $offset = ($page - 1) * $limit;
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.title,
                p.description,
                p.status,
                p.featured,
                p.created_at,
                pc.name as category_name,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                EXISTS(SELECT 1 FROM store_products sp JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id) as has_pricing,
                COALESCE((SELECT COUNT(DISTINCT session_id) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as unique_views,
                COALESCE((SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as total_views,
                (SELECT MAX(created_at) FROM product_views pv WHERE pv.product_id = p.id $dateFilter) as last_viewed
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            $whereClause
            $orderBy
            LIMIT ? OFFSET ?
        ");

        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['unique_views'] = (int) $product['unique_views'];
            $product['total_views'] = (int) $product['total_views'];
            $product['has_pricing'] = (bool) $product['has_pricing'];
            $product['featured'] = (bool) $product['featured'];
        }

        $stats = getProductStats($period, $startDate, $endDate);

        return [
            'success' => true,
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit),
            'stats' => $stats
        ];

    } catch (PDOException $e) {
        error_log("Database error in getProductsData: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function getProductDetails($productId, $startDate = null, $endDate = null, $period = 'month')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.title,
                p.description,
                p.status,
                p.featured,
                p.created_at,
                pc.name as category_name,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
                EXISTS(SELECT 1 FROM store_products sp JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id) as has_pricing,
                COALESCE((SELECT COUNT(DISTINCT session_id) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as unique_views,
                COALESCE((SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as total_views
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            WHERE p.id = ?
        ");

        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return [
                'success' => false,
                'error' => 'Product not found'
            ];
        }

        $product['unique_views'] = (int) $product['unique_views'];
        $product['total_views'] = (int) $product['total_views'];
        $product['has_pricing'] = (bool) $product['has_pricing'];
        $product['featured'] = (bool) $product['featured'];

        $daysInPeriod = 30;
        if ($period === 'today')
            $daysInPeriod = 1;
        elseif ($period === 'week')
            $daysInPeriod = 7;
        elseif ($startDate && $endDate) {
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $daysInPeriod = $end->diff($start)->days + 1;
        }

        $product['avg_daily_views'] = $daysInPeriod > 0 ? $product['total_views'] / $daysInPeriod : 0;

        $timelineStmt = $pdo->prepare("
            SELECT 
                DATE(CONVERT_TZ(created_at, '+00:00', '+03:00')) as date,
                COUNT(*) as views
            FROM product_views 
            WHERE product_id = ? $dateFilter
            GROUP BY DATE(CONVERT_TZ(created_at, '+00:00', '+03:00'))
            ORDER BY date ASC
        ");
        $timelineStmt->execute([$productId]);
        $timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

        $timeline = [
            'labels' => array_column($timelineData, 'date'),
            'values' => array_map('intval', array_column($timelineData, 'views'))
        ];

        $sessionsStmt = $pdo->prepare("
            SELECT 
                session_id,
                COUNT(*) as view_count,
                MIN(created_at) as first_view,
                MAX(created_at) as last_view
            FROM product_views 
            WHERE product_id = ? $dateFilter
            GROUP BY session_id
            ORDER BY last_view DESC
            LIMIT 10
        ");
        $sessionsStmt->execute([$productId]);
        $recentSessions = $sessionsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recentSessions as &$session) {
            $session['view_count'] = (int) $session['view_count'];
        }

        $product['timeline'] = $timeline;
        $product['recent_sessions'] = $recentSessions;

        return [
            'success' => true,
            'data' => $product
        ];

    } catch (PDOException $e) {
        error_log("Database error in getProductDetails: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function getChartData($startDate = null, $endDate = null, $period = 'month', $viewType = 'unique')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "WHERE DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "WHERE DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "WHERE CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "WHERE CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $timelineStmt = $pdo->prepare("
            SELECT 
                DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) as date,
                COUNT(DISTINCT pv.session_id) as unique_views,
                COUNT(*) as total_views
            FROM product_views pv
            $dateFilter
            GROUP BY DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00'))
            ORDER BY date ASC
        ");
        $timelineStmt->execute();
        $timelineData = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

        $timeline = [
            'labels' => array_column($timelineData, 'date'),
            'unique_views' => array_map('intval', array_column($timelineData, 'unique_views')),
            'total_views' => array_map('intval', array_column($timelineData, 'total_views'))
        ];

        $categoryStmt = $pdo->prepare("
            SELECT 
                COALESCE(pc.name, 'Uncategorized') as category_name,
                COUNT(DISTINCT pv.session_id) as unique_views,
                COUNT(*) as total_views
            FROM product_views pv
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            $dateFilter
            GROUP BY pc.id, pc.name
            ORDER BY unique_views DESC
            LIMIT 10
        ");
        $categoryStmt->execute();
        $categoryData = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = [
            'labels' => array_column($categoryData, 'category_name'),
            'values' => array_map('intval', array_column($categoryData, $viewType === 'unique' ? 'unique_views' : 'total_views'))
        ];

        return [
            'success' => true,
            'timeline' => $timeline,
            'categories' => $categories
        ];

    } catch (PDOException $e) {
        error_log("Database error in getChartData: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function getProductStats($period = 'month', $startDate = null, $endDate = null)
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $totalProductsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE status = 'published'");
        $totalProductsStmt->execute();
        $totalProducts = $totalProductsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $uniqueViewsStmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as count FROM product_views pv WHERE 1=1 $dateFilter");
        $uniqueViewsStmt->execute();
        $totalUniqueViews = $uniqueViewsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $cumulativeViewsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_views pv WHERE 1=1 $dateFilter");
        $cumulativeViewsStmt->execute();
        $totalCumulativeViews = $cumulativeViewsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $todayViewsStmt = $pdo->prepare("SELECT COUNT(*) as count FROM product_views pv WHERE DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))");
        $todayViewsStmt->execute();
        $todayViews = $todayViewsStmt->fetch(PDO::FETCH_ASSOC)['count'];

        $avgViewsPerProduct = $totalProducts > 0 ? $totalUniqueViews / $totalProducts : 0;

        return [
            'total_products' => (int) $totalProducts,
            'total_unique_views' => (int) $totalUniqueViews,
            'total_cumulative_views' => (int) $totalCumulativeViews,
            'today_views' => (int) $todayViews,
            'avg_views_per_product' => (float) $avgViewsPerProduct
        ];

    } catch (PDOException $e) {
        error_log("Database error in getProductStats: " . $e->getMessage());
        return [
            'total_products' => 0,
            'total_unique_views' => 0,
            'total_cumulative_views' => 0,
            'today_views' => 0,
            'avg_views_per_product' => 0.0
        ];
    }
}

function getCategories()
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            SELECT id, name 
            FROM product_categories 
            WHERE status = 'active' 
            ORDER BY name ASC
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'categories' => $categories
        ];

    } catch (PDOException $e) {
        error_log("Database error in getCategories: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Database error occurred'
        ];
    }
}

function exportProductsData($startDate = null, $endDate = null, $period = 'month', $viewType = 'unique', $category = 'all', $sort = 'unique_desc')
{
    global $pdo;

    try {
        $pdo->exec("SET time_zone = '+03:00'");

        $whereClause = "WHERE p.status != 'deleted'";
        $params = [];

        if ($category !== 'all') {
            $whereClause .= " AND p.category_id = ?";
            $params[] = $category;
        }

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) BETWEEN '$startDate' AND '$endDate'";
        } elseif ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(CONVERT_TZ(pv.created_at, '+00:00', '+03:00')) = DATE(CONVERT_TZ(NOW(), '+00:00', '+03:00'))";
                    break;
                case 'week':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 WEEK)";
                    break;
                case 'month':
                    $dateFilter = "AND CONVERT_TZ(pv.created_at, '+00:00', '+03:00') >= DATE_SUB(CONVERT_TZ(NOW(), '+00:00', '+03:00'), INTERVAL 1 MONTH)";
                    break;
            }
        }

        $orderBy = "ORDER BY ";
        switch ($sort) {
            case 'unique_desc':
                $orderBy .= "unique_views DESC";
                break;
            case 'unique_asc':
                $orderBy .= "unique_views ASC";
                break;
            case 'cumulative_desc':
                $orderBy .= "total_views DESC";
                break;
            case 'cumulative_asc':
                $orderBy .= "total_views ASC";
                break;
            case 'recent':
                $orderBy .= "last_viewed DESC NULLS LAST";
                break;
            case 'title':
                $orderBy .= "p.title ASC";
                break;
            default:
                $orderBy .= "unique_views DESC";
        }

        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.title,
                p.status,
                p.featured,
                p.created_at,
                pc.name as category_name,
                COALESCE((SELECT COUNT(DISTINCT session_id) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as unique_views,
                COALESCE((SELECT COUNT(*) FROM product_views pv WHERE pv.product_id = p.id $dateFilter), 0) as total_views,
                (SELECT MAX(created_at) FROM product_views pv WHERE pv.product_id = p.id $dateFilter) as last_viewed
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            $whereClause
            $orderBy
        ");

        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_performance_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Product ID',
            'Product Title',
            'Category',
            'Status',
            'Featured',
            'Unique Views',
            'Total Views',
            'Engagement Rate (%)',
            'Last Viewed',
            'Created At'
        ]);

        foreach ($products as $product) {
            $engagementRate = $product['total_views'] > 0 ?
                round(($product['unique_views'] / $product['total_views']) * 100, 2) : 0;

            fputcsv($output, [
                $product['id'],
                $product['title'],
                $product['category_name'] ?: 'Uncategorized',
                $product['status'],
                $product['featured'] ? 'Yes' : 'No',
                $product['unique_views'],
                $product['total_views'],
                $engagementRate,
                $product['last_viewed'] ?: 'Never',
                $product['created_at']
            ]);
        }

        fclose($output);
        exit;

    } catch (PDOException $e) {
        error_log("Database error in exportProductsData: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Export failed'
        ]);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_products';

switch ($action) {
    case 'get_products':
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'month';
        $viewType = $_GET['view_type'] ?? 'unique';
        $category = $_GET['category'] ?? 'all';
        $sort = $_GET['sort'] ?? 'unique_desc';
        $status = $_GET['status'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $result = getProductsData($page, $limit, $startDate, $endDate, $period, $viewType, $category, $sort, $status, $search);
        echo json_encode($result);
        break;

    case 'get_product_details':
        $productId = $_GET['id'] ?? '';
        if (empty($productId)) {
            echo json_encode([
                'success' => false,
                'error' => 'Product ID is required'
            ]);
            break;
        }

        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'month';

        $result = getProductDetails($productId, $startDate, $endDate, $period);
        echo json_encode($result);
        break;

    case 'get_chart_data':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'month';
        $viewType = $_GET['view_type'] ?? 'unique';

        $result = getChartData($startDate, $endDate, $period, $viewType);
        echo json_encode($result);
        break;

    case 'get_categories':
        $result = getCategories();
        echo json_encode($result);
        break;

    case 'export':
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $period = $_GET['period'] ?? 'month';
        $viewType = $_GET['view_type'] ?? 'unique';
        $category = $_GET['category'] ?? 'all';
        $sort = $_GET['sort'] ?? 'unique_desc';

        exportProductsData($startDate, $endDate, $period, $viewType, $category, $sort);
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
}
?>