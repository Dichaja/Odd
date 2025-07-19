<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
    if ($_REQUEST['action'] === 'stream') {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: text/event-stream');
        echo "event: error\ndata: Unauthorized\n\n";
        exit;
    }
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

date_default_timezone_set('Africa/Kampala');

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'stream':
            streamSearchData($pdo);
            break;
        case 'exportSearchData':
            exportSearchData($pdo);
            break;
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    error_log("Error in manageSearchLog: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

function streamSearchData(PDO $pdo)
{
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    session_write_close();
    set_time_limit(0);

    while (!connection_aborted()) {
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $searchTerm = $_GET['search_term'] ?? '';
        $performanceFilter = $_GET['performance_filter'] ?? 'all';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(1, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $period = $_GET['period'] ?? 'daily';

        $whereConditions = [];
        $params = [];

        if ($startDate && $endDate) {
            $whereConditions[] = "created_at BETWEEN ? AND ?";
            $params[] = $startDate . ' 00:00:00';
            $params[] = $endDate . ' 23:59:59';
        }

        if ($searchTerm) {
            $whereConditions[] = "search_query LIKE ?";
            $params[] = '%' . $searchTerm . '%';
        }

        if ($performanceFilter !== 'all') {
            switch ($performanceFilter) {
                case 'good':
                    $whereConditions[] = "max_match_score >= 0.70";
                    break;
                case 'fair':
                    $whereConditions[] = "max_match_score >= 0.50 AND max_match_score < 0.70";
                    break;
                case 'poor':
                    $whereConditions[] = "max_match_score < 0.50";
                    break;
            }
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM search_log $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();

        $dataStmt = $pdo->prepare("
            SELECT 
                id,
                search_query,
                results_count,
                max_match_score,
                min_match_score,
                average_match_score,
                duration_ms,
                created_at
            FROM search_log 
            $whereClause
            ORDER BY created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $dataStmt->execute($params);
        $searchLogs = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($searchLogs as &$log) {
            $log['max_match_score'] = round($log['max_match_score'] * 100, 2);
            $log['min_match_score'] = round($log['min_match_score'] * 100, 2);
            $log['average_match_score'] = round($log['average_match_score'] * 100, 2);
        }

        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_searches,
                AVG(duration_ms) as avg_response_time,
                AVG(average_match_score) as avg_match_score,
                SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as zero_results
            FROM search_log 
            $whereClause
        ");
        $statsStmt->execute($params);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        $stats['avg_response_time'] = round($stats['avg_response_time'] ?? 0);
        $stats['avg_match_score'] = round(($stats['avg_match_score'] ?? 0) * 100);

        $chartData = getChartData($pdo, $startDate, $endDate, $period, $whereClause, $params);

        $topTermsStmt = $pdo->prepare("
            SELECT 
                search_query,
                COUNT(*) as search_count
            FROM search_log 
            $whereClause
            GROUP BY search_query
            ORDER BY search_count DESC
            LIMIT 5
        ");
        $topTermsStmt->execute($params);
        $topTerms = $topTermsStmt->fetchAll(PDO::FETCH_ASSOC);

        $distributionStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN max_match_score >= 0.70 THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN max_match_score >= 0.50 AND max_match_score < 0.70 THEN 1 ELSE 0 END) as fair_count,
                SUM(CASE WHEN max_match_score < 0.50 THEN 1 ELSE 0 END) as poor_count
            FROM search_log 
            $whereClause
        ");
        $distributionStmt->execute($params);
        $distribution = $distributionStmt->fetch(PDO::FETCH_ASSOC);

        $responseTimeStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN duration_ms < 100 THEN 1 ELSE 0 END) as fast_count,
                SUM(CASE WHEN duration_ms >= 100 AND duration_ms <= 300 THEN 1 ELSE 0 END) as medium_count,
                SUM(CASE WHEN duration_ms > 300 THEN 1 ELSE 0 END) as slow_count,
                COUNT(*) as total_count
            FROM search_log 
            $whereClause
        ");
        $responseTimeStmt->execute($params);
        $responseTime = $responseTimeStmt->fetch(PDO::FETCH_ASSOC);

        $total = $responseTime['total_count'];
        if ($total > 0) {
            $responseTime['fast_percent'] = round(($responseTime['fast_count'] / $total) * 100);
            $responseTime['medium_percent'] = round(($responseTime['medium_count'] / $total) * 100);
            $responseTime['slow_percent'] = round(($responseTime['slow_count'] / $total) * 100);
        } else {
            $responseTime['fast_percent'] = 0;
            $responseTime['medium_percent'] = 0;
            $responseTime['slow_percent'] = 0;
        }

        $responseData = [
            'searchData' => [
                'data' => $searchLogs,
                'total' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'hasMore' => ($offset + $limit) < $totalCount
            ],
            'stats' => $stats,
            'chartData' => $chartData,
            'topTerms' => $topTerms,
            'distribution' => $distribution,
            'responseTime' => $responseTime,
            'timestamp' => time()
        ];

        $json = json_encode($responseData);
        echo "data: {$json}\n\n";
        @ob_flush();
        @flush();

        sleep(2);
    }
    exit;
}

function getChartData(PDO $pdo, $startDate, $endDate, $period, $whereClause, $params)
{
    if ($period === 'daily') {
        $stmt = $pdo->prepare("
            SELECT 
                HOUR(created_at) as time_unit,
                COUNT(*) as total_count,
                SUM(CASE WHEN max_match_score >= 0.70 THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN max_match_score >= 0.50 AND max_match_score < 0.70 THEN 1 ELSE 0 END) as fair_count,
                SUM(CASE WHEN max_match_score < 0.50 THEN 1 ELSE 0 END) as poor_count
            FROM search_log 
            $whereClause
            GROUP BY HOUR(created_at)
            ORDER BY time_unit
        ");
    } elseif ($period === 'weekly') {
        $stmt = $pdo->prepare("
            SELECT 
                DAYOFWEEK(created_at) - 1 as time_unit,
                COUNT(*) as total_count,
                SUM(CASE WHEN max_match_score >= 0.70 THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN max_match_score >= 0.50 AND max_match_score < 0.70 THEN 1 ELSE 0 END) as fair_count,
                SUM(CASE WHEN max_match_score < 0.50 THEN 1 ELSE 0 END) as poor_count
            FROM search_log 
            $whereClause
            GROUP BY DAYOFWEEK(created_at)
            ORDER BY time_unit
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as time_unit,
                COUNT(*) as total_count,
                SUM(CASE WHEN max_match_score >= 0.70 THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN max_match_score >= 0.50 AND max_match_score < 0.70 THEN 1 ELSE 0 END) as fair_count,
                SUM(CASE WHEN max_match_score < 0.50 THEN 1 ELSE 0 END) as poor_count
            FROM search_log 
            $whereClause
            GROUP BY DATE(created_at)
            ORDER BY time_unit
        ");
    }

    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function exportSearchData(PDO $pdo)
{
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    $whereConditions = [];
    $params = [];

    if ($startDate && $endDate) {
        $whereConditions[] = "created_at BETWEEN ? AND ?";
        $params[] = $startDate . ' 00:00:00';
        $params[] = $endDate . ' 23:59:59';
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $stmt = $pdo->prepare("
        SELECT 
            search_query,
            results_count,
            max_match_score,
            min_match_score,
            average_match_score,
            duration_ms,
            created_at
        FROM search_log 
        $whereClause
        ORDER BY created_at DESC
    ");
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$row) {
        $row['max_match_score'] = round($row['max_match_score'] * 100, 2);
        $row['min_match_score'] = round($row['min_match_score'] * 100, 2);
        $row['average_match_score'] = round($row['average_match_score'] * 100, 2);
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
}
?>