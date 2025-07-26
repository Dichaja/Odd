<?php
require_once __DIR__ . '/config/config.php';

if (isset($_GET['ajax']) && ($_GET['ajax'] === 'search' || $_GET['ajax'] === 'products')) {
    header('Content-Type: application/json');

    $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
    $categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 12;
    $offset = ($page - 1) * $limit;

    $response = ['products' => [], 'categories' => [], 'hasMore' => false, 'total' => 0];

    if (!empty($searchQuery)) {
        $searchStartTime = microtime(true);

        $allProductsStmt = $pdo->prepare("
            SELECT 
                p.id, 
                p.title, 
                p.description, 
                p.meta_title,
                p.meta_description,
                p.meta_keywords,
                (SELECT COUNT(DISTINCT session_id) 
                 FROM product_views 
                 WHERE product_id = p.id) AS views,
                p.category_id,
                c.name AS category_name,
                (SELECT image_url 
                 FROM product_images 
                 WHERE product_id = p.id 
                   AND is_primary = 1 
                 LIMIT 1) AS primary_image,
                EXISTS(
                    SELECT 1 
                    FROM store_products sp
                    JOIN product_pricing pp ON pp.store_products_id = sp.id
                    WHERE sp.product_id = p.id
                ) AS has_pricing,
                (SELECT MIN(pp.price)
                 FROM store_products sp
                 JOIN product_pricing pp ON pp.store_products_id = sp.id
                 WHERE sp.product_id = p.id
                ) AS lowest_price
            FROM products p
            JOIN product_categories c ON c.id = p.category_id
            WHERE p.status = 'published'
        ");
        $allProductsStmt->execute();
        $allProducts = $allProductsStmt->fetchAll(PDO::FETCH_ASSOC);

        $scoredProducts = advancedProductSearch($allProducts, $searchQuery);

        $resultsCount = count($scoredProducts);
        $maxMatchScore = 0.00;
        $minMatchScore = 0.00;
        $averageMatchScore = 0.00;

        if ($resultsCount > 0) {
            $scores = array_column($scoredProducts, 'search_score');
            $maxMatchScore = max($scores);
            $minMatchScore = min($scores);
            $averageMatchScore = array_sum($scores) / count($scores);
        }

        $searchEndTime = microtime(true);
        $durationMs = round(($searchEndTime - $searchStartTime) * 1000);

        if ($page === 1) {
            logSearchActivity($pdo, $searchQuery, $resultsCount, $maxMatchScore, $minMatchScore, $averageMatchScore, $durationMs);
        }

        $totalProducts = count($scoredProducts);
        $products = array_slice($scoredProducts, $offset, $limit);

        if ($page === 1) {
            $allCategoriesStmt = $pdo->prepare("
                SELECT id, name, description, meta_title, meta_description, meta_keywords
                FROM product_categories 
                WHERE status = 'active'
            ");
            $allCategoriesStmt->execute();
            $allCategories = $allCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);

            $scoredCategories = advancedCategorySearch($allCategories, $searchQuery);
            $response['categories'] = array_slice($scoredCategories, 0, 10);
        }
    } else {
        if (!empty($categoryId)) {
            $productStmt = $pdo->prepare("
                SELECT 
                    p.id, 
                    p.title, 
                    p.description, 
                    (SELECT COUNT(DISTINCT session_id) 
                     FROM product_views 
                     WHERE product_id = p.id) AS views,
                    p.category_id,
                    c.name AS category_name,
                    (SELECT image_url 
                     FROM product_images 
                     WHERE product_id = p.id 
                       AND is_primary = 1 
                     LIMIT 1) AS primary_image,
                    EXISTS(
                        SELECT 1 
                        FROM store_products sp
                        JOIN product_pricing pp ON pp.store_products_id = sp.id
                        WHERE sp.product_id = p.id
                    ) AS has_pricing,
                    (SELECT MIN(pp.price)
                     FROM store_products sp
                     JOIN product_pricing pp ON pp.store_products_id = sp.id
                     WHERE sp.product_id = p.id
                    ) AS lowest_price
                FROM products p
                JOIN product_categories c ON c.id = p.category_id
                WHERE p.category_id = ? 
                  AND p.status = 'published'
                ORDER BY has_pricing DESC, p.featured DESC, (SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) DESC
                LIMIT ? OFFSET ?
            ");
            $productStmt->execute([$categoryId, $limit, $offset]);

            $countStmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM products p
                WHERE p.category_id = ? AND p.status = 'published'
            ");
            $countStmt->execute([$categoryId]);
            $totalProducts = $countStmt->fetch()['total'];
        } else {
            $productStmt = $pdo->prepare("
                SELECT 
                    p.id, 
                    p.title, 
                    p.description, 
                    (SELECT COUNT(DISTINCT session_id) 
                     FROM product_views 
                     WHERE product_id = p.id) AS views,
                    p.category_id,
                    c.name AS category_name,
                    (SELECT image_url 
                     FROM product_images 
                     WHERE product_id = p.id 
                       AND is_primary = 1 
                     LIMIT 1) AS primary_image,
                    EXISTS(
                        SELECT 1 
                        FROM store_products sp
                        JOIN product_pricing pp ON pp.store_products_id = sp.id
                        WHERE sp.product_id = p.id
                    ) AS has_pricing,
                    (SELECT MIN(pp.price)
                     FROM store_products sp
                     JOIN product_pricing pp ON pp.store_products_id = sp.id
                     WHERE sp.product_id = p.id
                    ) AS lowest_price
                FROM products p
                JOIN product_categories c ON c.id = p.category_id
                WHERE p.status = 'published'
                ORDER BY has_pricing DESC, p.featured DESC, (SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) DESC
                LIMIT ? OFFSET ?
            ");
            $productStmt->execute([$limit, $offset]);

            $countStmt = $pdo->prepare("
                SELECT COUNT(*) as total
                FROM products p
                WHERE p.status = 'published'
            ");
            $countStmt->execute();
            $totalProducts = $countStmt->fetch()['total'];
        }

        $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($products as &$product) {
        $productImageUrl = getProductImage($product['id']);

        if ($productImageUrl) {
            $product['primary_image'] = $productImageUrl;
        } elseif (empty($product['primary_image'])) {
            $product['primary_image'] = "https://placehold.co/600x400/e2e8f0/1e293b?text=" . urlencode($product['title']);
        }
        $product['has_pricing'] = (bool) $product['has_pricing'];
        $product['lowest_price'] = $product['lowest_price'] ? (float) $product['lowest_price'] : null;
    }

    $response['products'] = $products;
    $response['hasMore'] = ($offset + $limit) < $totalProducts;
    $response['total'] = $totalProducts;

    echo json_encode($response);
    exit;
}

function logSearchActivity($pdo, $searchQuery, $resultsCount, $maxMatchScore, $minMatchScore, $averageMatchScore, $durationMs)
{
    try {
        $logId = generateUlid();

        $timezone = new DateTimeZone('Africa/Kampala');
        $currentDateTime = new DateTime('now', $timezone);
        $createdAt = $currentDateTime->format('Y-m-d H:i:s');

        $logStmt = $pdo->prepare("
            INSERT INTO search_log (
                id, 
                session_id, 
                search_query, 
                results_count, 
                max_match_score, 
                min_match_score, 
                average_match_score, 
                duration_ms, 
                created_at
            ) VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)
        ");

        $logStmt->execute([
            $logId,
            $searchQuery,
            $resultsCount,
            round($maxMatchScore, 2),
            round($minMatchScore, 2),
            round($averageMatchScore, 2),
            $durationMs,
            $createdAt
        ]);

        error_log("Search logged: Query='{$searchQuery}', Results={$resultsCount}, Duration={$durationMs}ms");

    } catch (Exception $e) {
        error_log("Search logging failed: " . $e->getMessage());
    }
}

function levenshteinDistance($a, $b)
{
    if ($a === $b)
        return 0;
    if (strlen($a) === 0 || strlen($b) === 0)
        return max(strlen($a), strlen($b));

    $v = range(0, strlen($b));
    for ($i = 0; $i < strlen($a); $i++) {
        $prev = $i + 1;
        for ($j = 0; $j < strlen($b); $j++) {
            $val = $a[$i] === $b[$j] ? $v[$j] : min($v[$j], $v[$j + 1], $prev) + 1;
            $v[$j] = $prev;
            $prev = $val;
        }
        $v[strlen($b)] = $prev;
    }
    return $v[strlen($b)];
}

function soundex_similarity($word1, $word2)
{
    return soundex($word1) === soundex($word2);
}

function metaphone_similarity($word1, $word2)
{
    return metaphone($word1) === metaphone($word2);
}

function tokenize($text)
{
    return array_filter(
        array_map(
            'trim',
            preg_split('/\W+/', strtolower($text))
        ),
        function ($word) {
            return strlen($word) > 2;
        }
    );
}

function calculateFieldScore($fieldValue, $searchQuery, $weight = 1.0)
{
    if (empty($fieldValue) || empty($searchQuery))
        return 0;

    $fieldValue = strtolower($fieldValue);
    $searchQuery = strtolower($searchQuery);
    $score = 0;

    if (strpos($fieldValue, $searchQuery) !== false) {
        $score += 1.0;

        if (
            strpos($fieldValue, ' ' . $searchQuery . ' ') !== false ||
            strpos($fieldValue, $searchQuery . ' ') === 0 ||
            strpos($fieldValue, ' ' . $searchQuery) === strlen($fieldValue) - strlen($searchQuery) - 1
        ) {
            $score += 0.5;
        }
    }

    $fieldTokens = tokenize($fieldValue);
    $queryTokens = tokenize($searchQuery);

    foreach ($queryTokens as $queryToken) {
        $bestTokenScore = 0;

        foreach ($fieldTokens as $fieldToken) {
            $tokenScore = 0;

            if ($fieldToken === $queryToken) {
                $tokenScore = 1.0;
            } elseif (strpos($fieldToken, $queryToken) !== false || strpos($queryToken, $fieldToken) !== false) {
                $tokenScore = 0.7;
            } elseif (soundex_similarity($fieldToken, $queryToken) || metaphone_similarity($fieldToken, $queryToken)) {
                $tokenScore = 0.6;
            } else {
                $distance = levenshteinDistance($fieldToken, $queryToken);
                $maxLen = max(strlen($fieldToken), strlen($queryToken));
                if ($maxLen > 0 && $distance <= 2) {
                    $tokenScore = max(0, 1 - ($distance / $maxLen)) * 0.5;
                }
            }

            $bestTokenScore = max($bestTokenScore, $tokenScore);
        }

        $score += $bestTokenScore;
    }

    return $score * $weight;
}

function advancedProductSearch($products, $searchQuery)
{
    $scoredProducts = [];

    foreach ($products as $product) {
        $totalScore = 0;

        $totalScore += calculateFieldScore($product['title'], $searchQuery, 0.4);
        $totalScore += calculateFieldScore($product['meta_title'], $searchQuery, 0.3);
        $totalScore += calculateFieldScore($product['description'], $searchQuery, 0.2);
        $totalScore += calculateFieldScore($product['meta_description'], $searchQuery, 0.2);
        $totalScore += calculateFieldScore($product['meta_keywords'], $searchQuery, 0.2);
        $totalScore += calculateFieldScore($product['category_name'], $searchQuery, 0.1);

        if ($totalScore > 0.1) {
            $product['search_score'] = min($totalScore, 1.0);
            $scoredProducts[] = $product;
        }
    }

    usort($scoredProducts, function ($a, $b) {
        if ($a['search_score'] !== $b['search_score']) {
            return $b['search_score'] <=> $a['search_score'];
        }

        if ($a['has_pricing'] !== $b['has_pricing']) {
            return $b['has_pricing'] <=> $a['has_pricing'];
        }

        return $b['views'] <=> $a['views'];
    });

    return $scoredProducts;
}

function advancedCategorySearch($categories, $searchQuery)
{
    $scoredCategories = [];

    foreach ($categories as $category) {
        $totalScore = 0;

        $totalScore += calculateFieldScore($category['name'], $searchQuery, 0.5);
        $totalScore += calculateFieldScore($category['meta_title'], $searchQuery, 0.3);
        $totalScore += calculateFieldScore($category['description'], $searchQuery, 0.2);
        $totalScore += calculateFieldScore($category['meta_description'], $searchQuery, 0.2);
        $totalScore += calculateFieldScore($category['meta_keywords'], $searchQuery, 0.2);

        if ($totalScore > 0.1) {
            $category['search_score'] = min($totalScore, 1.0);
            $scoredCategories[] = $category;
        }
    }

    usort($scoredCategories, function ($a, $b) {
        return $b['search_score'] <=> $a['search_score'];
    });

    return $scoredCategories;
}

function getProductImage($productId)
{
    $productDir = __DIR__ . '/img/products/' . $productId . '/';
    $jsonFile = $productDir . 'images.json';

    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $imageData = json_decode($jsonContent, true);

        if (isset($imageData['images']) && !empty($imageData['images'])) {
            $firstImage = $imageData['images'][0];
            $imagePath = $productDir . $firstImage;

            if (file_exists($imagePath)) {
                return BASE_URL . 'img/products/' . $productId . '/' . $firstImage;
            }
        }
    }

    return null;
}

function getCategoryImage($categoryId)
{
    $categoryDir = __DIR__ . '/img/product-categories/' . $categoryId . '/';

    if (is_dir($categoryDir)) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($categoryDir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    return BASE_URL . 'img/product-categories/' . $categoryId . '/' . $file;
                }
            }
        }
    }

    return null;
}

function formatPrice($price)
{
    if ($price === null || $price <= 0) {
        return null;
    }
    return 'UGX ' . number_format($price, 0) . '/=';
}

$categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';
$searchQuery = isset($_GET['s']) ? trim($_GET['s']) : '';

if (!empty($searchQuery)) {
    $pageTitle = "Search: " . htmlspecialchars($searchQuery);
} else {
    $pageTitle = "Building Materials";
}

$activeNav = "materials";

if (!empty($categoryId)) {
    $stmt = $pdo->prepare("
        SELECT id, name, description, meta_title, meta_description, featured
        FROM product_categories 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if ($category) {
        $pageTitle = $category['name'];
    } else {
        $categoryId = '';
    }
}

$categoryImageUrl = BASE_URL . 'img/materials-yard.jpg';

if (!empty($categoryId) && isset($category)) {
    $specificCategoryImage = getCategoryImage($categoryId);
    if ($specificCategoryImage) {
        $categoryImageUrl = $specificCategoryImage;
    }
}

$allCategoriesStmt = $pdo->prepare("
    SELECT id, name, description
    FROM product_categories 
    WHERE status = 'active'
    ORDER BY CASE WHEN id = ? THEN 0 ELSE 1 END, name ASC
");
$allCategoriesStmt->execute([$categoryId]);
$allCategories = $allCategoriesStmt->fetchAll();

$products = [];
if (empty($searchQuery)) {
    if (!empty($categoryId)) {
        $productsStmt = $pdo->prepare("
            SELECT 
                p.id, 
                p.title, 
                p.description, 
                (SELECT COUNT(DISTINCT session_id) 
                 FROM product_views 
                 WHERE product_id = p.id) AS views,
                (SELECT image_url 
                 FROM product_images 
                 WHERE product_id = p.id 
                   AND is_primary = 1 
                 LIMIT 1) AS primary_image,
                EXISTS(
                    SELECT 1 
                    FROM store_products sp
                    JOIN product_pricing pp ON pp.store_products_id = sp.id
                    WHERE sp.product_id = p.id
                ) AS has_pricing,
                (SELECT MIN(pp.price)
                 FROM store_products sp
                 JOIN product_pricing pp ON pp.store_products_id = sp.id
                 WHERE sp.product_id = p.id
                ) AS lowest_price
            FROM products p
            WHERE p.category_id = ? 
              AND p.status = 'published'
            ORDER BY has_pricing DESC, p.featured DESC, (SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) DESC
            LIMIT 12
        ");
        $productsStmt->execute([$categoryId]);
    } else {
        $productsStmt = $pdo->prepare("
            SELECT 
                p.id, 
                p.title, 
                p.description, 
                (SELECT COUNT(DISTINCT session_id) 
                 FROM product_views 
                 WHERE product_id = p.id) AS views,
                (SELECT image_url 
                 FROM product_images 
                 WHERE product_id = p.id 
                   AND is_primary = 1 
                 LIMIT 1) AS primary_image,
                EXISTS(
                    SELECT 1 
                    FROM store_products sp
                    JOIN product_pricing pp ON pp.store_products_id = sp.id
                    WHERE sp.product_id = p.id
                ) AS has_pricing,
                (SELECT MIN(pp.price)
                 FROM store_products sp
                 JOIN product_pricing pp ON pp.store_products_id = sp.id
                 WHERE sp.product_id = p.id
                ) AS lowest_price
            FROM products p
            WHERE p.status = 'published'
            ORDER BY has_pricing DESC, p.featured DESC, (SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) DESC
            LIMIT 12
        ");
        $productsStmt->execute();
    }

    while ($row = $productsStmt->fetch()) {
        $productImageUrl = getProductImage($row['id']);

        if ($productImageUrl) {
            $row['primary_image'] = $productImageUrl;
        } elseif (empty($row['primary_image'])) {
            $row['primary_image'] = "https://placehold.co/600x400/e2e8f0/1e293b?text=" . urlencode($row['title']);
        }

        $row['has_pricing'] = (bool) $row['has_pricing'];
        $row['lowest_price'] = $row['lowest_price'] ? (float) $row['lowest_price'] : null;
        $products[] = $row;
    }
}

ob_start();
?>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 2;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 3;
    }

    .tooltip {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1F2937;
        color: white;
        padding: 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
        z-index: 10;
    }

    .tooltip::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 8px;
        height: 8px;
        background-color: #1F2937;
    }

    .share-button:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }

    .product-details-btn {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .product-card:hover .product-details-btn {
        opacity: 1;
        visibility: visible;
    }

    .checkbox-custom {
        width: 16px;
        height: 16px;
        border: 2px solid #D1D5DB;
        border-radius: 3px;
        position: relative;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .checkbox-custom.checked {
        background-color: #DC2626;
        border-color: #DC2626;
    }

    .checkbox-custom.checked::after {
        content: '✓';
        position: absolute;
        top: -2px;
        left: 1px;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .skeleton {
        background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0;
        }

        100% {
            background-position: 200% 0;
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .loader {
        border-top-color: #D92B13;
        animation: spinner 0.6s linear infinite;
    }

    @keyframes spinner {
        to {
            transform: rotate(360deg);
        }
    }

    .price-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        font-size: clamp(1rem, 4vw, 1.5rem);
    }

    @media (min-width: 768px) {
        .price-text {
            font-size: clamp(1.25rem, 3vw, 1.4rem);
        }
    }
</style>

<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-black/50 via-black/30 to-black/50 z-10"></div>

    <img src="<?= $categoryImageUrl ?>" alt="<?= htmlspecialchars($pageTitle) ?> Banner"
        class="w-full h-full object-cover opacity-60">

    <div class="container max-w-6xl mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 md:pt-12 z-20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white mb-4 drop-shadow-lg">
                    <?= htmlspecialchars($pageTitle) ?>
                </h1>
                <nav class="flex text-xs md:text-sm text-gray-200 overflow-hidden whitespace-nowrap drop-shadow-md">
                    <a href="<?= BASE_URL ?>" class="hover:text-white transition-colors truncate max-w-[30%]">Zzimba
                        Online</a>
                    <span class="mx-2">/</span>
                    <a href="<?= BASE_URL ?>materials-yard"
                        class="hover:text-white transition-colors truncate max-w-[30%]">Building Materials</a>
                    <?php if (!empty($searchQuery)): ?>
                        <span class="mx-2">/</span>
                        <span class="text-white font-medium truncate max-w-[40%]">Search Results</span>
                    <?php elseif (!empty($categoryId) && isset($category)): ?>
                        <span class="mx-2">/</span>
                        <span
                            class="text-white font-medium truncate max-w-[40%]"><?= htmlspecialchars($category['name']) ?></span>
                    <?php endif; ?>
                </nav>
                <?php if (!empty($searchQuery)): ?>
                    <p class="text-gray-100 mt-2 line-clamp-2 max-w-2xl hidden md:block drop-shadow-md">
                        Search results for "<?= htmlspecialchars($searchQuery) ?>" - Find the best building materials and
                        construction products.
                    </p>
                <?php elseif (!empty($categoryId) && isset($category) && !empty($category['description'])): ?>
                    <p class="text-gray-100 mt-2 line-clamp-2 max-w-2xl hidden md:block drop-shadow-md">
                        <?= htmlspecialchars($category['description']) ?>
                    </p>
                <?php elseif (empty($categoryId)): ?>
                    <p class="text-gray-100 mt-2 line-clamp-2 max-w-2xl hidden md:block drop-shadow-md">
                        Discover a wide range of genuine building materials and supplies for all your construction needs.
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2 mt-4 md:mt-0 hidden md:flex">
                <span class="text-xs font-medium text-white drop-shadow-lg">SHARE</span>
                <div class="flex gap-2">
                    <button onclick="copyLink()"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white bg-transparent transition-all duration-200 hover:bg-red-600/10 hover:-translate-y-0.5 relative">
                        <i class="fa-solid fa-link text-[10px]"></i>
                        <span class="tooltip">Copy link to clipboard</span>
                    </button>
                    <button onclick="shareOnWhatsApp()"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white bg-transparent transition-all duration-200 hover:bg-red-600/10 hover:-translate-y-0.5 relative">
                        <i class="fa-brands fa-whatsapp text-[10px]"></i>
                        <span class="tooltip">Share on WhatsApp</span>
                    </button>
                    <button onclick="shareOnFacebook()"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white bg-transparent transition-all duration-200 hover:bg-red-600/10 hover:-translate-y-0.5 relative">
                        <i class="fa-brands fa-facebook-f text-[10px]"></i>
                        <span class="tooltip">Share on Facebook</span>
                    </button>
                    <button onclick="shareOnTwitter()"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white bg-transparent transition-all duration-200 hover:bg-red-600/10 hover:-translate-y-0.5 relative">
                        <i class="fa-brands fa-x-twitter text-[10px]"></i>
                        <span class="tooltip">Post on X</span>
                    </button>
                    <button onclick="shareOnLinkedIn()"
                        class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white bg-transparent transition-all duration-200 hover:bg-red-600/10 hover:-translate-y-0.5 relative">
                        <i class="fa-brands fa-linkedin-in text-[10px]"></i>
                        <span class="tooltip">Share on LinkedIn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="w-full lg:w-1/4 order-2 lg:order-1 hidden lg:block">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 sticky top-4">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Categories</h2>

                    <?php if (!empty($categoryId) || !empty($searchQuery)): ?>
                        <button onclick="clearSelection()"
                            class="flex items-center justify-between px-3 py-2 rounded-md mb-4 bg-gray-50 hover:bg-gray-100 w-full text-left transition-all duration-300 cursor-pointer">
                            <span class="font-medium text-gray-600">
                                <i class="fas fa-times-circle mr-2"></i> Clear Selection
                            </span>
                        </button>
                    <?php endif; ?>

                    <div class="relative mb-4">
                        <input type="text" id="categorySearch" placeholder="Search categories..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                    </div>
                </div>

                <div class="p-4 max-h-[500px] overflow-y-auto">
                    <?php foreach ($allCategories as $cat): ?>
                        <a href="<?= BASE_URL ?>view/category/<?= $cat['id'] ?>"
                            class="flex items-center justify-between px-3 py-2 rounded-md mb-1 transition-all duration-300 cursor-pointer hover:bg-gray-50 <?= ($cat['id'] === $categoryId) ? 'bg-red-50 text-red-600' : '' ?>"
                            data-category-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>"
                            title="<?= htmlspecialchars($cat['name']) ?>">
                            <span
                                class="font-medium flex-1 truncate pr-3 <?= ($cat['id'] === $categoryId) ? 'border-b-2 border-red-600 pb-0.5' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </span>
                            <div class="checkbox-custom ml-2 <?= ($cat['id'] === $categoryId) ? 'checked' : '' ?>"></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-3/4 order-1 lg:order-2">
            <div class="lg:hidden mb-6">
                <div class="relative">
                    <input type="text" id="mobileSearch" placeholder="Search categories..."
                        value="<?= !empty($categoryId) && isset($category) ? htmlspecialchars($category['name']) : '' ?>"
                        class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">

                    <?php if (!empty($categoryId) && isset($category)): ?>
                        <button onclick="clearSelection()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php else: ?>
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    <?php endif; ?>

                    <div id="mobileDropdown"
                        class="absolute top-full left-0 right-0 bg-white border border-gray-200 border-t-0 rounded-b-lg max-h-48 overflow-y-auto z-50 hidden">
                        <?php foreach ($allCategories as $cat): ?>
                            <div class="px-4 py-3 cursor-pointer transition-colors duration-200 border-b border-gray-100 hover:bg-gray-50 last:border-b-0"
                                data-category-id="<?= $cat['id'] ?>"
                                data-category-name="<?= htmlspecialchars($cat['name']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 md:p-6">
                <div id="categoriesSection" class="mb-8 hidden">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Categories</h2>
                    <div id="categoriesGrid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-500">
                        <span id="productCount">
                            <?php
                            if (!empty($searchQuery)) {
                                echo '0';
                            } else {
                                if (!empty($categoryId)) {
                                    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ? AND status = 'published'");
                                    $totalStmt->execute([$categoryId]);
                                } else {
                                    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE status = 'published'");
                                    $totalStmt->execute();
                                }
                                $totalCount = $totalStmt->fetch()['total'];
                                echo $totalCount;
                            }
                            ?>
                        </span> products found
                        <?php if (!empty($searchQuery)): ?>
                            for "<span class="font-medium"><?= htmlspecialchars($searchQuery) ?></span>"
                        <?php endif; ?>
                    </div>
                </div>

                <div id="loadingSkeleton"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 <?= !empty($searchQuery) ? '' : 'hidden' ?>">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <div class="relative border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden">
                            <div class="skeleton h-40 md:h-48"></div>
                            <div class="p-3 md:p-5">
                                <div class="skeleton h-4 w-3/4 mb-2"></div>
                                <div class="skeleton h-3 w-1/2 mb-3"></div>
                                <div class="skeleton h-3 w-full mb-1"></div>
                                <div class="skeleton h-3 w-2/3 mb-4"></div>
                                <div class="flex space-x-2">
                                    <div class="skeleton h-8 flex-1"></div>
                                    <div class="skeleton h-8 flex-1"></div>
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <div id="productsGrid"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 <?= !empty($searchQuery) ? 'hidden' : '' ?>">
                    <?php if (empty($searchQuery)): ?>
                        <?php if (count($products) === 0): ?>
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-box-open text-4xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No products found</p>
                                <p class="text-gray-500 text-sm mt-1">Try selecting a different category or check back later</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <div
                                    class="product-card relative border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                                    <div class="relative">
                                        <img src="<?= $product['primary_image'] ?>" alt="<?= htmlspecialchars($product['title']) ?>"
                                            class="w-full h-40 md:h-48 object-cover">

                                        <div class="product-details-btn">
                                            <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>"
                                                class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">
                                                View Details
                                            </a>
                                        </div>
                                    </div>

                                    <div class="p-3 md:p-5 flex flex-col flex-1">
                                        <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                                            <?= htmlspecialchars($product['title']) ?>
                                        </h3>

                                        <div class="flex-1 flex flex-col justify-end">
                                            <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                                                <?= htmlspecialchars($product['description']) ?>
                                            </p>

                                            <div class="flex items-center text-gray-500 text-xs md:text-sm mb-3">
                                                <i class="fas fa-eye mr-1"></i>
                                                <span><?= number_format($product['views']) ?> views</span>
                                            </div>

                                            <?php if ($product['has_pricing'] && $product['lowest_price']): ?>
                                                <div class="text-center mb-3">
                                                    <span class="price-text font-bold" style="color: #D92B13;">
                                                        <?= formatPrice($product['lowest_price']) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex gap-2">
                                                <?php if ($product['has_pricing']): ?>
                                                    <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=buy"
                                                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                                        <i class="fas fa-shopping-cart mr-1"></i> Buy
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=sell"
                                                    class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                                    <i class="fas fa-tag mr-1"></i> Sell
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div id="loadingMore" class="text-center py-8 hidden">
                    <div class="loader mx-auto w-8 h-8 border-4 border-gray-200 rounded-full"></div>
                    <p class="mt-4 text-gray-500">Loading more products...</p>
                </div>

                <div id="noResults" class="text-center py-12 hidden">
                    <div class="text-gray-400 mb-3">
                        <i class="fas fa-search text-4xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">No results found</p>
                    <p class="text-gray-500 text-sm mt-1">Try different keywords or browse our categories</p>
                </div>

                <div id="quoteRequestSection"
                    class="bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-2xl p-8 text-center mt-12">
                    <div class="max-w-md mx-auto">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Not found what you're looking for?</h3>
                        <p class="text-gray-600 text-sm mb-6">Get a custom quote for your specific building material
                            needs</p>
                        <a href="<?= BASE_URL ?>request-for-quote"
                            class="inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-red-600/30">
                            <i class="fas fa-file-invoice mr-2"></i>
                            Request a Quote Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let isLoading = false;
    let hasMoreProducts = true;
    let currentSearchQuery = '<?= addslashes($searchQuery) ?>';
    let currentCategoryId = '<?= addslashes($categoryId) ?>';
    let searchTimeout = null;
    let totalProductsLoaded = 0;

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('categorySearch');
        const categoryItems = document.querySelectorAll('a[data-category-name]');
        const mobileSearch = document.getElementById('mobileSearch');
        const mobileDropdown = document.getElementById('mobileDropdown');

        if (currentSearchQuery) {
            performSearch(currentSearchQuery, 1);
            setupInfiniteScroll();
        } else if (!currentSearchQuery && !currentCategoryId) {
            setupInfiniteScroll();
            totalProductsLoaded = <?= count($products) ?>;
            currentPage = 2;
        } else if (currentCategoryId) {
            setupInfiniteScroll();
            totalProductsLoaded = <?= count($products) ?>;
            currentPage = 2;
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                categoryItems.forEach(item => {
                    const categoryName = item.getAttribute('data-category-name');
                    item.style.display = categoryName.includes(searchTerm) ? 'flex' : 'none';
                });
            });
        }

        if (mobileSearch && mobileDropdown) {
            mobileSearch.addEventListener('focus', function () {
                mobileDropdown.classList.remove('hidden');
            });

            mobileSearch.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const dropdownItems = mobileDropdown.querySelectorAll('div[data-category-name]');

                dropdownItems.forEach(item => {
                    const categoryName = item.getAttribute('data-category-name').toLowerCase();
                    item.style.display = categoryName.includes(searchTerm) ? 'block' : 'none';
                });

                mobileDropdown.classList.remove('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!mobileSearch.contains(e.target) && !mobileDropdown.contains(e.target)) {
                    mobileDropdown.classList.add('hidden');
                }
            });

            const dropdownItems = mobileDropdown.querySelectorAll('div[data-category-id]');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function () {
                    const categoryId = this.getAttribute('data-category-id');
                    window.location.href = `<?= BASE_URL ?>view/category/${categoryId}`;
                });
            });
        }
    });

    function setupInfiniteScroll() {
        window.addEventListener('scroll', function () {
            if (!isLoading && hasMoreProducts) {
                const scrollPosition = window.innerHeight + window.scrollY;
                const bodyHeight = document.body.offsetHeight;

                if (scrollPosition >= bodyHeight * 0.8) {
                    if (currentSearchQuery) {
                        performSearch(currentSearchQuery, currentPage, true);
                    } else {
                        loadMoreProducts();
                    }
                }
            }
        });
    }

    function loadMoreProducts() {
        if (isLoading || !hasMoreProducts) return;

        isLoading = true;
        const loadingMore = document.getElementById('loadingMore');

        loadingMore.classList.remove('hidden');

        const endpoint = currentSearchQuery ? 'search' : 'products';
        const queryParams = new URLSearchParams({
            ajax: endpoint,
            page: currentPage,
            limit: 12
        });

        if (currentCategoryId) {
            queryParams.append('categoryId', currentCategoryId);
        }

        fetch(`?${queryParams.toString()}`)
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    loadingMore.classList.add('hidden');

                    if (data.products && data.products.length > 0) {
                        renderProducts(data.products, true);
                        currentPage++;
                        totalProductsLoaded += data.products.length;
                        hasMoreProducts = data.hasMore;

                        const productCount = document.getElementById('productCount');
                        if (productCount) {
                            productCount.textContent = data.total || totalProductsLoaded;
                        }
                    } else {
                        hasMoreProducts = false;
                    }

                    isLoading = false;
                }, 800);
            })
            .catch(error => {
                console.error('Load more error:', error);
                isLoading = false;
                loadingMore.classList.add('hidden');
            });
    }

    function performSearch(query, page = 1, append = false) {
        if (isLoading) return;

        isLoading = true;
        const loadingSkeleton = document.getElementById('loadingSkeleton');
        const productsGrid = document.getElementById('productsGrid');
        const loadingMore = document.getElementById('loadingMore');
        const noResults = document.getElementById('noResults');
        const categoriesSection = document.getElementById('categoriesSection');

        if (!append) {
            loadingSkeleton.classList.remove('hidden');
            productsGrid.classList.add('hidden');
            noResults.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            currentPage = 1;
            totalProductsLoaded = 0;
        } else {
            loadingMore.classList.remove('hidden');
        }

        fetch(`?ajax=search&q=${encodeURIComponent(query)}&page=${page}&limit=12`)
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    if (!append) {
                        loadingSkeleton.classList.add('hidden');
                        productsGrid.classList.remove('hidden');

                        if (data.categories && data.categories.length > 0) {
                            renderCategories(data.categories);
                            categoriesSection.classList.remove('hidden');
                        }

                        productsGrid.innerHTML = '';
                        totalProductsLoaded = 0;
                    } else {
                        loadingMore.classList.add('hidden');
                    }

                    if (data.products && data.products.length > 0) {
                        renderProducts(data.products, append);
                        currentPage = page;
                        totalProductsLoaded += data.products.length;
                        hasMoreProducts = data.hasMore;
                        if (append) {
                            currentPage = page + 1;
                        } else {
                            currentPage = 2;
                        }
                    } else if (!append) {
                        noResults.classList.remove('hidden');
                        productsGrid.classList.add('hidden');
                    }

                    const productCount = document.getElementById('productCount');
                    if (productCount) {
                        productCount.textContent = data.total || totalProductsLoaded;
                    }

                    isLoading = false;
                }, append ? 800 : 1200);
            })
            .catch(error => {
                console.error('Search error:', error);
                isLoading = false;
                loadingSkeleton.classList.add('hidden');
                loadingMore.classList.add('hidden');
                if (!append) {
                    noResults.classList.remove('hidden');
                }
            });
    }

    function renderCategories(categories) {
        const categoriesGrid = document.getElementById('categoriesGrid');
        const html = categories.map(category => `
            <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-all duration-200 border border-gray-200 cursor-pointer hover:-translate-y-0.5" 
                 onclick="window.location.href='<?= BASE_URL ?>view/category/${category.id}'">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-tag text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">${escapeHtml(category.name)}</h3>
                    </div>
                </div>
            </div>
        `).join('');
        categoriesGrid.innerHTML = html;
    }

    function formatPrice(price) {
        if (!price || price <= 0) return null;
        return 'UGX ' + parseInt(price).toLocaleString() + '/=';
    }

    function renderProducts(products, append = false) {
        const productsGrid = document.getElementById('productsGrid');
        const html = products.map(product => `
            <div class="product-card relative border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col fade-in">
                <div class="relative">
                    <img src="${product.primary_image}" alt="${escapeHtml(product.title)}"
                        class="w-full h-40 md:h-48 object-cover">
                    ${product.search_score ? `
                        <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                            ${Math.min(Math.round(product.search_score * 100), 100)}% match
                        </div>
                    ` : ''}

                    <div class="product-details-btn">
                        <a href="<?= BASE_URL ?>view/product/${product.id}"
                            class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">
                            View Details
                        </a>
                    </div>
                </div>

                <div class="p-3 md:p-5 flex flex-col flex-1">
                    <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                        ${escapeHtml(product.title)}
                    </h3>

                    <div class="flex-1 flex flex-col justify-end">
                        <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                            ${escapeHtml(product.description || 'No description available')}
                        </p>

                        <div class="flex items-center text-gray-500 text-xs md:text-sm mb-3">
                            <i class="fas fa-eye mr-1"></i>
                            <span>${parseInt(product.views).toLocaleString()} views</span>
                        </div>

                        ${product.has_pricing && product.lowest_price ? `
                            <div class="text-center mb-3">
                                <span class="price-text font-bold" style="color: #D92B13;">
                                    ${formatPrice(product.lowest_price)}
                                </span>
                            </div>
                        ` : ''}

                        <div class="flex gap-2">
                            ${product.has_pricing ? `
                                <a href="<?= BASE_URL ?>view/product/${product.id}?action=buy"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                    <i class="fas fa-shopping-cart mr-1"></i> Buy
                                </a>
                            ` : ''}
                            <a href="<?= BASE_URL ?>view/product/${product.id}?action=sell"
                                class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                <i class="fas fa-tag mr-1"></i> Sell
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        if (append) {
            productsGrid.insertAdjacentHTML('beforeend', html);
        } else {
            productsGrid.innerHTML = html;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function clearSelection() {
        window.location.href = '<?= BASE_URL ?>materials-yard';
    }

    function copyLink() {
        const currentUrl = window.location.href;
        navigator.clipboard.writeText(currentUrl).then(() => {
            showToast('Link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    function shareOnWhatsApp() {
        const currentUrl = window.location.href;
        const pageTitle = "<?= addslashes($pageTitle) ?>";
        const message = `Check out ${pageTitle} on Zzimba Online: ${currentUrl}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const pageTitle = "<?= addslashes($pageTitle) ?>";
        const message = `Check out ${pageTitle} on Zzimba Online:`;
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const pageTitle = "<?= addslashes($pageTitle) ?>";
        const message = `Check out ${pageTitle} on Zzimba Online.`;
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(pageTitle)}&summary=${encodeURIComponent(message)}`, '_blank');
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity duration-300`;
        toast.textContent = message;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('opacity-100'), 10);

        setTimeout(() => {
            toast.classList.remove('opacity-100');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>