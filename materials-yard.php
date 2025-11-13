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
            SELECT p.id,p.title,p.description,p.meta_title,p.meta_description,p.meta_keywords,
                   (SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) AS views,
                   p.category_id,c.name AS category_name,
                   (SELECT image_url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image,
                   EXISTS(SELECT 1 FROM store_products sp
                          JOIN store_categories sc ON sc.id=sp.store_category_id
                          JOIN vendor_stores vs ON vs.id=sc.store_id
                          JOIN product_pricing pp ON pp.store_products_id=sp.id
                          WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS has_pricing,
                   (SELECT MIN(pp.price) FROM store_products sp
                    JOIN store_categories sc ON sc.id=sp.store_category_id
                    JOIN vendor_stores vs ON vs.id=sc.store_id
                    JOIN product_pricing pp ON pp.store_products_id=sp.id
                    WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS lowest_price
            FROM products p JOIN product_categories c ON c.id=p.category_id
            WHERE p.status='published'
        ");
        $allProductsStmt->execute();
        $allProducts = $allProductsStmt->fetchAll(PDO::FETCH_ASSOC);
        $scoredProducts = advancedProductSearch($allProducts, $searchQuery);
        $resultsCount = count($scoredProducts);
        $maxMatchScore = $resultsCount ? max(array_column($scoredProducts, 'search_score')) : 0.0;
        $minMatchScore = $resultsCount ? min(array_column($scoredProducts, 'search_score')) : 0.0;
        $averageMatchScore = $resultsCount ? array_sum(array_column($scoredProducts, 'search_score')) / $resultsCount : 0.0;
        $durationMs = round((microtime(true) - $searchStartTime) * 1000);
        if ($page === 1)
            logSearchActivity($pdo, $searchQuery, $resultsCount, $maxMatchScore, $minMatchScore, $averageMatchScore, $durationMs);
        $totalProducts = count($scoredProducts);
        $products = array_slice($scoredProducts, $offset, $limit);
        if ($page === 1) {
            $allCategoriesStmt = $pdo->prepare("SELECT id,name,description,meta_title,meta_description,meta_keywords FROM product_categories WHERE status='active'");
            $allCategoriesStmt->execute();
            $scoredCategories = advancedCategorySearch($allCategoriesStmt->fetchAll(PDO::FETCH_ASSOC), $searchQuery);
            $response['categories'] = array_slice($scoredCategories, 0, 10);
        }
    } else {
        if (!empty($categoryId)) {
            $productStmt = $pdo->prepare("
                SELECT p.id,p.title,p.description,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) AS views,
                       p.category_id,c.name AS category_name,
                       (SELECT image_url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image,
                       EXISTS(SELECT 1 FROM store_products sp
                              JOIN store_categories sc ON sc.id=sp.store_category_id
                              JOIN vendor_stores vs ON vs.id=sc.store_id
                              JOIN product_pricing pp ON pp.store_products_id=sp.id
                              WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS has_pricing,
                       (SELECT MIN(pp.price) FROM store_products sp
                        JOIN store_categories sc ON sc.id=sp.store_category_id
                        JOIN vendor_stores vs ON vs.id=sc.store_id
                        JOIN product_pricing pp ON pp.store_products_id=sp.id
                        WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS lowest_price
                FROM products p JOIN product_categories c ON c.id=p.category_id
                WHERE p.category_id=? AND p.status='published'
                ORDER BY has_pricing DESC,p.featured DESC,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) DESC
                LIMIT ? OFFSET ?
            ");
            $productStmt->execute([$categoryId, $limit, $offset]);
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products p WHERE p.category_id=? AND p.status='published'");
            $countStmt->execute([$categoryId]);
            $totalProducts = $countStmt->fetch()['total'];
        } else {
            $productStmt = $pdo->prepare("
                SELECT p.id,p.title,p.description,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) AS views,
                       p.category_id,c.name AS category_name,
                       (SELECT image_url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image,
                       EXISTS(SELECT 1 FROM store_products sp
                              JOIN store_categories sc ON sc.id=sp.store_category_id
                              JOIN vendor_stores vs ON vs.id=sc.store_id
                              JOIN product_pricing pp ON pp.store_products_id=sp.id
                              WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS has_pricing,
                       (SELECT MIN(pp.price) FROM store_products sp
                        JOIN store_categories sc ON sc.id=sp.store_category_id
                        JOIN vendor_stores vs ON vs.id=sc.store_id
                        JOIN product_pricing pp ON pp.store_products_id=sp.id
                        WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS lowest_price
                FROM products p JOIN product_categories c ON c.id=p.category_id
                WHERE p.status='published'
                ORDER BY has_pricing DESC,p.featured DESC,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) DESC
                LIMIT ? OFFSET ?
            ");
            $productStmt->execute([$limit, $offset]);
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products p WHERE p.status='published'");
            $countStmt->execute();
            $totalProducts = $countStmt->fetch()['total'];
        }
        $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach ($products as &$product) {
        $img = getProductImage($product['id']);
        if ($img)
            $product['primary_image'] = $img;
        elseif (empty($product['primary_image']))
            $product['primary_image'] = "https://placehold.co/600x400/e2e8f0/1e293b?text=" . urlencode($product['title']);
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
        $createdAt = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');
        $logStmt = $pdo->prepare("INSERT INTO search_log (id,session_id,search_query,results_count,max_match_score,min_match_score,average_match_score,duration_ms,created_at) VALUES (?,?,?,?,?,?,?,?,?)");
        $logStmt->execute([$logId, null, $searchQuery, $resultsCount, round($maxMatchScore, 2), round($minMatchScore, 2), round($averageMatchScore, 2), $durationMs, $createdAt]);
    } catch (Exception $e) {
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
    return array_filter(array_map('trim', preg_split('/\W+/', strtolower($text))), fn($w) => strlen($w) > 2);
}
function calculateFieldScore($fieldValue, $searchQuery, $weight = 1.0)
{
    if (empty($fieldValue) || empty($searchQuery))
        return 0;
    $f = strtolower($fieldValue);
    $q = strtolower($searchQuery);
    $s = 0;
    if (strpos($f, $q) !== false) {
        $s += 1.0;
        if (strpos($f, ' ' . $q . ' ') !== false || strpos($f, $q . ' ') === 0 || strpos($f, ' ' . $q) === strlen($f) - strlen($q) - 1) {
            $s += 0.5;
        }
    }
    $ft = tokenize($f);
    $qt = tokenize($q);
    foreach ($qt as $t) {
        $best = 0;
        foreach ($ft as $x) {
            $z = 0;
            if ($x === $t)
                $z = 1;
            elseif (strpos($x, $t) !== false || strpos($t, $x) !== false)
                $z = .7;
            elseif (soundex_similarity($x, $t) || metaphone_similarity($x, $t))
                $z = .6;
            else {
                $d = levenshteinDistance($x, $t);
                $m = max(strlen($x), strlen($t));
                if ($m > 0 && $d <= 2)
                    $z = max(0, 1 - ($d / $m)) * .5;
            }
            $best = max($best, $z);
        }
        $s += $best;
    }
    return $s * $weight;
}
function advancedProductSearch($products, $searchQuery)
{
    $out = [];
    foreach ($products as $p) {
        $t = 0;
        $t += calculateFieldScore($p['title'], $searchQuery, .4);
        $t += calculateFieldScore($p['meta_title'], $searchQuery, .3);
        $t += calculateFieldScore($p['description'], $searchQuery, .2);
        $t += calculateFieldScore($p['meta_description'], $searchQuery, .2);
        $t += calculateFieldScore($p['meta_keywords'], $searchQuery, .2);
        $t += calculateFieldScore($p['category_name'], $searchQuery, .1);
        if ($t > 0.1) {
            $p['search_score'] = min($t, 1.0);
            $out[] = $p;
        }
    }
    usort($out, function ($a, $b) {
        if ($a['search_score'] !== $b['search_score'])
            return $b['search_score'] <=> $a['search_score'];
        if ($a['has_pricing'] !== $b['has_pricing'])
            return $b['has_pricing'] <=> $a['has_pricing'];
        return $b['views'] <=> $a['views'];
    });
    return $out;
}
function advancedCategorySearch($categories, $searchQuery)
{
    $out = [];
    foreach ($categories as $c) {
        $t = 0;
        $t += calculateFieldScore($c['name'], $searchQuery, .5);
        $t += calculateFieldScore($c['meta_title'], $searchQuery, .3);
        $t += calculateFieldScore($c['description'], $searchQuery, .2);
        $t += calculateFieldScore($c['meta_description'], $searchQuery, .2);
        $t += calculateFieldScore($c['meta_keywords'], $searchQuery, .2);
        if ($t > 0.1) {
            $c['search_score'] = min($t, 1.0);
            $out[] = $c;
        }
    }
    usort($out, fn($a, $b) => $b['search_score'] <=> $a['search_score']);
    return $out;
}
function getProductImage($productId)
{
    $dir = __DIR__ . '/img/products/' . $productId . '/';
    $json = $dir . 'images.json';
    if (file_exists($json)) {
        $data = json_decode(file_get_contents($json), true);
        if (isset($data['images'][0])) {
            $f = $data['images'][0];
            if (file_exists($dir . $f))
                return BASE_URL . 'img/products/' . $productId . '/' . $f;
        }
    }
    return null;
}
function getCategoryImage($categoryId)
{
    $dir = __DIR__ . '/img/product-categories/' . $categoryId . '/';
    if (is_dir($dir)) {
        foreach (scandir($dir) as $f) {
            if ($f !== '.' && $f !== '..') {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    return BASE_URL . 'img/product-categories/' . $categoryId . '/' . $f;
            }
        }
    }
    return null;
}
function formatPrice($price)
{
    if ($price === null || $price <= 0)
        return null;
    return 'UGX ' . number_format($price, 0) . '/=';
}
function generateSeoMetaTags($mode, $data, $imageUrl)
{
    $siteName = 'Zzimba Online';
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $title = '';
    $desc = '';
    if ($mode === 'search') {
        $q = trim($data['query'] ?? '');
        $title = ($q !== '' ? 'Search: ' . $q : 'Search') . ' | ' . $siteName;
        $desc = $q !== '' ? 'Explore building materials matching "' . $q . '" on Zzimba Online.' : 'Explore building materials search results on Zzimba Online.';
    } elseif ($mode === 'category') {
        $name = trim($data['name'] ?? 'Category');
        $raw = trim($data['description'] ?? '');
        $title = $name . ' | ' . $siteName;
        $desc = $raw !== '' ? $raw : 'Browse ' . $name . ' building materials on Zzimba Online.';
    } else {
        $title = 'Building Materials | ' . $siteName;
        $desc = 'Discover genuine building materials and supplies from trusted suppliers across Uganda.';
    }
    $clean = strip_tags($desc);
    if (mb_strlen($clean) > 160)
        $clean = mb_substr($clean, 0, 157) . '...';
    $ogImage = $imageUrl ?: ('https://placehold.co/1200x630/e2e8f0/1e293b?text=' . urlencode('Building Materials'));
    return [
        'title' => htmlspecialchars($title),
        'description' => htmlspecialchars($clean),
        'og_title' => htmlspecialchars($title),
        'og_description' => htmlspecialchars($clean),
        'og_image' => $ogImage,
        'og_url' => $currentUrl,
        'og_type' => 'website'
    ];
}

$categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';
$searchQuery = isset($_GET['s']) ? trim($_GET['s']) : '';
$pageTitle = !empty($searchQuery) ? "Search: " . htmlspecialchars($searchQuery) : "Building Materials";
$activeNav = "materials";
if (!empty($categoryId)) {
    $stmt = $pdo->prepare("SELECT id,name,description,meta_title,meta_description,featured FROM product_categories WHERE id=? AND status='active'");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    if ($category)
        $pageTitle = $category['name'];
    else
        $categoryId = '';
}
$categoryImageUrl = BASE_URL . 'img/materials-yard.jpg';
if (!empty($categoryId) && isset($category)) {
    $img = getCategoryImage($categoryId);
    if ($img)
        $categoryImageUrl = $img;
}

$seoTags = [];
if (!empty($searchQuery)) {
    $seoTags = generateSeoMetaTags('search', ['query' => $searchQuery], $categoryImageUrl);
    $pageTitle = $seoTags['title'];
} elseif (!empty($categoryId) && isset($category)) {
    $seoTags = generateSeoMetaTags('category', ['name' => $category['name'] ?? '', 'description' => $category['description'] ?? ''], $categoryImageUrl);
    $pageTitle = $seoTags['title'];
} else {
    $seoTags = generateSeoMetaTags('page', [], $categoryImageUrl);
    $pageTitle = $seoTags['title'];
}

$allCategoriesStmt = $pdo->prepare("SELECT id,name,description FROM product_categories WHERE status='active' ORDER BY CASE WHEN id=? THEN 0 ELSE 1 END, name ASC");
$allCategoriesStmt->execute([$categoryId]);
$allCategories = $allCategoriesStmt->fetchAll();

$products = [];
if (empty($searchQuery)) {
    if (!empty($categoryId)) {
        $productsStmt = $pdo->prepare("
            SELECT p.id,p.title,p.description,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) AS views,
                   (SELECT image_url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image,
                   EXISTS(SELECT 1 FROM store_products sp JOIN store_categories sc ON sc.id=sp.store_category_id
                          JOIN vendor_stores vs ON vs.id=sc.store_id JOIN product_pricing pp ON pp.store_products_id=sp.id
                          WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS has_pricing,
                   (SELECT MIN(pp.price) FROM store_products sp JOIN store_categories sc ON sc.id=sp.store_category_id
                    JOIN vendor_stores vs ON vs.id=sc.store_id JOIN product_pricing pp ON pp.store_products_id=sp.id
                    WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS lowest_price
            FROM products p WHERE p.category_id=? AND p.status='published'
            ORDER BY has_pricing DESC,p.featured DESC,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) DESC
            LIMIT 12
        ");
        $productsStmt->execute([$categoryId]);
    } else {
        $productsStmt = $pdo->prepare("
            SELECT p.id,p.title,p.description,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) AS views,
                   (SELECT image_url FROM product_images WHERE product_id=p.id AND is_primary=1 LIMIT 1) AS primary_image,
                   EXISTS(SELECT 1 FROM store_products sp JOIN store_categories sc ON sc.id=sp.store_category_id
                          JOIN vendor_stores vs ON vs.id=sc.store_id JOIN product_pricing pp ON pp.store_products_id=sp.id
                          WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS has_pricing,
                   (SELECT MIN(pp.price) FROM store_products sp JOIN store_categories sc ON sc.id=sp.store_category_id
                    JOIN vendor_stores vs ON vs.id=sc.store_id JOIN product_pricing pp ON pp.store_products_id=sp.id
                    WHERE sp.product_id=p.id AND vs.status='active' AND pp.status='active') AS lowest_price
            FROM products p WHERE p.status='published'
            ORDER BY has_pricing DESC,p.featured DESC,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id=p.id) DESC
            LIMIT 12
        ");
        $productsStmt->execute();
    }
    while ($row = $productsStmt->fetch()) {
        $img = getProductImage($row['id']);
        if ($img) {
            $row['primary_image'] = $img;
        } elseif (empty($row['primary_image'])) {
            $row['primary_image'] = "https://placehold.co/600x400/e2e8f0/1e293b?text=" . urlencode($row['title']);
        }
        $row['has_pricing'] = (bool) $row['has_pricing'];
        $row['lowest_price'] = $row['lowest_price'] ? (float) $row['lowest_price'] : null;
        $products[] = $row;
    }
}

$categoriesJson = json_encode($allCategories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$categoryNameForMobile = (!empty($categoryId) && isset($category)) ? htmlspecialchars($category['name']) : '';
ob_start();
?>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 2
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 3
    }

    .product-details-btn {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all .3s
    }

    .product-card:hover .product-details-btn {
        opacity: 1;
        visibility: visible
    }

    .skeleton {
        background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite
    }

    @keyframes shimmer {
        0% {
            background-position: -200% 0
        }

        100% {
            background-position: 200% 0
        }
    }

    .loader {
        border-top-color: #D92B13;
        animation: spinner .6s linear infinite
    }

    @keyframes spinner {
        to {
            transform: rotate(360deg)
        }
    }

    .price-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        font-size: clamp(1rem, 4vw, 1.5rem)
    }

    @media (min-width:768px) {
        .price-text {
            font-size: clamp(1.25rem, 3vw, 1.4rem)
        }
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none
    }

    .hide-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none
    }
</style>

<script>
    window.__pendingVendorAction = null;
    window.setPendingVendorAction = (a) => { window.__pendingVendorAction = a || null };
    (function () { const wrap = () => { const o = window.updateUIAfterLogin; window.updateUIAfterLogin = function (u) { try { typeof o === 'function' && o(u) } catch (e) { } try { window.dispatchEvent(new CustomEvent('zz:session-login', { detail: u || {} })) } catch (e) { } const r = document.querySelector('[x-data="materialsYard()"]'); if (r && r.__x) { try { r.__x.$data.handlePostLogin(u || {}) } catch (e) { } } }; }; if (document.readyState === 'complete' || document.readyState === 'interactive') { wrap() } else { document.addEventListener('DOMContentLoaded', wrap) } })();
</script>

<div x-data="materialsYard()" x-init="init()">
    <div class="relative h-64 md:h-64 w-full overflow-hidden">
        <div class="absolute inset-0 bg-black/70 z-10"></div>
        <img src="<?= $categoryImageUrl ?>" alt="<?= htmlspecialchars($pageTitle) ?> Banner"
            class="w-full h-full object-cover">
        <div
            class="container max-w-6xl mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 md:pt-12 pb-6 z-20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold text-white mb-2"><?= htmlspecialchars($pageTitle) ?></h1>
                    <nav class="flex text-xs md:text-sm text-gray-200 overflow-hidden whitespace-nowrap">
                        <a href="<?= BASE_URL ?>" class="hover:text-white truncate max-w-[30%]">Zzimba Online</a><span
                            class="mx-2">/</span>
                        <a href="<?= BASE_URL ?>materials-yard" class="hover:text-white truncate max-w-[30%]">Building
                            Materials</a>
                        <?php if (!empty($searchQuery)): ?><span class="mx-2">/</span><span
                                class="text-white font-medium truncate max-w-[40%]">Search Results</span>
                        <?php elseif (!empty($categoryId) && isset($category)): ?><span class="mx-2">/</span><span
                                class="text-white font-medium truncate max-w-[40%]"><?= htmlspecialchars($category['name']) ?></span>
                        <?php endif; ?>
                    </nav>
                    <?php if (!empty($searchQuery)): ?>
                        <p class="text-gray-100 mt-1 line-clamp-2 max-w-2xl hidden md:block">Search results for
                            "<?= htmlspecialchars($searchQuery) ?>"</p>
                    <?php elseif (!empty($categoryId) && isset($category) && !empty($category['description'])): ?>
                        <p class="text-gray-100 mt-1 line-clamp-2 max-w-2xl hidden md:block">
                            <?= htmlspecialchars($category['description']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-gray-100 mt-1 line-clamp-2 max-w-2xl hidden md:block">Discover genuine building
                            materials and supplies.</p>
                    <?php endif; ?>
                </div>
                <div class="items-center gap-2 mt-3 md:mt-0 hidden md:flex">
                    <span class="text-xs font-medium text-white">SHARE</span>
                    <div class="flex gap-2">
                        <button @click="copyLink"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white"><i
                                data-lucide="link" class="w-3 h-3"></i></button>
                        <button @click="shareOnWhatsApp"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white"><i
                                data-lucide="message-circle" class="w-3 h-3"></i></button>
                        <button @click="shareOnFacebook"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white"><i
                                data-lucide="share-2" class="w-3 h-3"></i></button>
                        <button @click="shareOnTwitter"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white"><i
                                data-lucide="send" class="w-3 h-3"></i></button>
                        <button @click="shareOnLinkedIn"
                            class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white border-2 border-white"><i
                                data-lucide="share" class="w-3 h-3"></i></button>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:hidden">
                <span class="text-xs font-medium text-white">SHARE</span>
                <div class="flex gap-2">
                    <button @click="copyLink"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-white"
                        aria-label="Copy link"><i class="fa-solid fa-link" style="color:#ffffff;"></i></button>
                    <button @click="shareOnWhatsApp"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-white"
                        aria-label="Share on WhatsApp"><i class="fa-brands fa-whatsapp"
                            style="color:#ffffff;"></i></button>
                    <button @click="shareOnFacebook"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-white"
                        aria-label="Share on Facebook"><i class="fa-brands fa-facebook-f"
                            style="color:#ffffff;"></i></button>
                    <button @click="shareOnTwitter"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-white"
                        aria-label="Post on X"><i class="fa-brands fa-x-twitter" style="color:#ffffff;"></i></button>
                    <button @click="shareOnLinkedIn"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white border-2 border-white"
                        aria-label="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"
                            style="color:#ffffff;"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="md:hidden px-4 py-4 bg-white dark:bg-secondary">
        <div class="grid grid-cols-2 gap-2">
            <button onclick="openMobileSearch&&openMobileSearch()"
                class="flex flex-col items-center justify-center rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10"><i
                        data-lucide="search" class="w-5 h-5 text-secondary dark:text-white"></i></span>
                <span class="mt-1 text-xs text-secondary dark:text-white">Search</span>
            </button>
            <a href="<?= BASE_URL ?>request-for-quote"
                class="flex flex-col items-center justify-center rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary"><i
                        data-lucide="file-text" class="w-5 h-5"></i></span>
                <span class="mt-1 text-xs text-secondary dark:text-white">Request a Quote Now</span>
            </a>
        </div>
    </div>

    <div class="container max-w-6xl mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-1/4 order-2 lg:order-1 hidden lg:block">
                <div
                    class="bg-white dark:bg-secondary rounded-xl shadow-lg border border-gray-200 dark:border-white/10 sticky top-4">
                    <div class="p-4 border-b border-gray-200 dark:border-white/10">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Categories</h2>
                        <?php if (!empty($categoryId) || !empty($searchQuery)): ?>
                            <button @click="clearSelection"
                                class="flex items-center justify-between px-3 py-2 rounded-md mb-4 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 w-full text-left">
                                <span class="font-medium text-gray-600 dark:text-white/80"><i data-lucide="x-circle"
                                        class="w-4 h-4 mr-2 inline"></i>Clear Selection</span>
                            </button>
                        <?php endif; ?>
                        <div class="relative mb-4">
                            <input type="text" x-ref="categorySearch" placeholder="Search categories..."
                                @input="filterCategories($event.target.value)"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-md text-sm bg-white dark:bg-secondary dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-2.5 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="p-4 max-h-[500px] overflow-y-auto">
                        <?php foreach ($allCategories as $cat): ?>
                            <a href="<?= BASE_URL ?>view/category/<?= $cat['id'] ?>"
                                class="cat-item flex items-center justify-between px-3 py-2 rounded-md mb-1 transition-all hover:bg-gray-50 dark:hover:bg-white/5 <?= ($cat['id'] === $categoryId) ? 'bg-red-50 text-red-600 dark:bg-white/10' : '' ?>"
                                data-category-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>"
                                title="<?= htmlspecialchars($cat['name']) ?>">
                                <span
                                    class="font-medium flex-1 truncate pr-3 <?= ($cat['id'] === $categoryId) ? 'border-b-2 border-red-600 pb-0.5' : '' ?>"><?= htmlspecialchars($cat['name']) ?></span>
                                <div
                                    class="w-4 h-4 rounded-sm border <?= ($cat['id'] === $categoryId) ? 'bg-red-600 border-red-600' : 'border-gray-300 dark:border-white/20' ?>">
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-3/4 order-1 lg:order-2">
                <div class="lg:hidden mb-6">
                    <div class="relative" @click.outside="mobileDropdownOpen=false">
                        <input type="text" x-model="mobileQuery" placeholder="Search categories..."
                            value="<?= $categoryNameForMobile ?>" @focus="mobileDropdownOpen=true"
                            @input="mobileDropdownOpen=true"
                            class="w-full px-4 py-3 pr-10 border border-gray-300 dark:border-white/10 rounded-lg text-sm bg-white dark:bg-secondary dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <?php if (!empty($categoryId) && isset($category)): ?>
                            <button @click="clearSelection"
                                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"><i data-lucide="x"
                                    class="w-4 h-4"></i></button>
                        <?php else: ?>
                            <i data-lucide="search" class="w-4 h-4 absolute right-3 top-3 text-gray-400"></i>
                        <?php endif; ?>
                        <div x-show="mobileDropdownOpen" x-transition
                            class="absolute top-full left-0 right-0 bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 border-t-0 rounded-b-lg max-h-48 overflow-y-auto z-50">
                            <template x-for="c in filteredMobileCategories()" :key="c.id">
                                <div class="px-4 py-3 cursor-pointer border-b border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5"
                                    @click="gotoCategory(c.id)" x-text="c.name"></div>
                            </template>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-secondary rounded-xl shadow-lg border border-gray-200 dark:border-white/10 p-4 md:p-6">
                    <div id="categoriesSection" class="mb-8 hidden">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-6">Categories</h2>
                        <div id="categoriesGrid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <div class="text-sm text-gray-500 dark:text-white/70">
                            <span id="productCount">
                                <?php
                                if (!empty($searchQuery)) {
                                    echo '0';
                                } else {
                                    if (!empty($categoryId)) {
                                        $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE category_id=? AND status='published'");
                                        $totalStmt->execute([$categoryId]);
                                    } else {
                                        $totalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE status='published'");
                                        $totalStmt->execute();
                                    }
                                    echo $totalStmt->fetch()['total'];
                                }
                                ?>
                            </span> products found
                            <?php if (!empty($searchQuery)): ?> for "<span
                                    class="font-medium"><?= htmlspecialchars($searchQuery) ?></span>"<?php endif; ?>
                        </div>
                    </div>

                    <div id="loadingSkeleton"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 <?= !empty($searchQuery) ? '' : 'hidden' ?>">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <div
                                class="relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden">
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
                                    <div class="text-gray-400 dark:text-white/40 mb-3"><i data-lucide="box"
                                            class="w-10 h-10 mx-auto"></i></div>
                                    <p class="text-gray-600 dark:text-white/80 font-medium">No products found</p>
                                    <p class="text-gray-500 dark:text-white/60 text-sm mt-1">Try a different category</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <div
                                        class="product-card relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                                        <div class="relative">
                                            <img src="<?= $product['primary_image'] ?>"
                                                alt="<?= htmlspecialchars($product['title']) ?>"
                                                class="w-full h-40 md:h-48 object-cover">
                                            <div class="product-details-btn">
                                                <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>"
                                                    class="bg-white dark:bg-secondary text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">View
                                                    Details</a>
                                            </div>
                                        </div>
                                        <div class="p-3 md:p-5 flex flex-col flex-1">
                                            <h3
                                                class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-sm md:text-base">
                                                <?= htmlspecialchars($product['title']) ?>
                                            </h3>
                                            <div class="flex-1 flex flex-col">
                                                <p
                                                    class="text-gray-600 dark:text-white/70 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                                                    <?= htmlspecialchars($product['description']) ?>
                                                </p>
                                                <div
                                                    class="text-gray-500 dark:text-white/70 text-xs md:text-sm mb-3 flex items-center">
                                                    <i data-lucide="eye"
                                                        class="w-4 h-4 mr-1"></i><span><?= number_format($product['views']) ?>
                                                        views</span>
                                                </div>
                                                <div class="mt-auto flex flex-col items-center">
                                                    <div
                                                        class="text-sm font-bold text-[#D92B13] h-5 flex items-center <?= ($product['has_pricing'] && $product['lowest_price']) ? '' : 'invisible' ?>">
                                                        <?= $product['has_pricing'] && $product['lowest_price'] ? formatPrice($product['lowest_price']) : '' ?>
                                                    </div>
                                                    <div class="mt-2 flex gap-2 w-full">
                                                        <?php if ($product['has_pricing']): ?>
                                                            <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=buy"
                                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md flex items-center justify-center flex-1 text-xs md:text-sm font-medium"><i
                                                                    data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i>Buy</a>
                                                        <?php endif; ?>
                                                        <button
                                                            @click="sellProduct('<?= $product['id'] ?>','<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>')"
                                                            class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md flex items-center justify-center flex-1 text-xs md:text-sm font-medium"><i
                                                                data-lucide="tags" class="w-4 h-4 mr-1"></i>Sell</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div id="loadingMore" class="text-center py-8 hidden">
                        <div class="loader mx-auto w-8 h-8 border-4 border-gray-200 dark:border-white/10 rounded-full">
                        </div>
                        <p class="mt-4 text-gray-500 dark:text-white/70">Loading more products...</p>
                    </div>

                    <div id="noResults" class="text-center py-12 hidden">
                        <div class="text-gray-400 dark:text-white/40 mb-3"><i data-lucide="search"
                                class="w-10 h-10 mx-auto"></i></div>
                        <p class="text-gray-600 dark:text-white/80 font-medium">No results found</p>
                        <p class="text-gray-500 dark:text-white/60 text-sm mt-1">Try different keywords</p>
                    </div>

                    <div id="quoteRequestSection"
                        class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-white/5 dark:to-white/10 border border-slate-200 dark:border-white/10 rounded-2xl p-8 text-center mt-12">
                        <div class="max-w-md mx-auto">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Not found what you're
                                looking for?</h3>
                            <p class="text-gray-600 dark:text-white/70 text-sm mb-6">Get a custom quote for your
                                specific building material needs</p>
                            <a href="<?= BASE_URL ?>request-for-quote"
                                class="inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-all">
                                <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>Request a Quote Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="md:hidden mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-base font-semibold text-secondary dark:text-white">Materials</div>
                        <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'})"
                            class="text-xs text-primary inline-flex items-center">Top<i data-lucide="arrow-up"
                                class="w-4 h-4 ml-1"></i></a>
                    </div>
                    <div class="flex gap-3 overflow-x-auto hide-scrollbar -mx-4 px-4 pb-2">
                        <template x-for="p in miniProducts" :key="p.id">
                            <div
                                class="snap-start shrink-0 w-64 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary overflow-hidden flex flex-col">
                                <a :href="'<?= BASE_URL ?>view/product/' + p.id" class="block">
                                    <div class="relative">
                                        <img :src="p.primary_image" :alt="p.title" class="w-full h-40 object-cover">
                                        <div
                                            class="absolute top-2 left-2 bg-black/70 text-white text-[10px] px-2 py-0.5 rounded">
                                            Featured</div>
                                    </div>
                                </a>
                                <div class="p-3 flex-1 flex flex-col">
                                    <a :href="'<?= BASE_URL ?>view/product/' + p.id" class="block">
                                        <div class="text-sm font-medium text-secondary dark:text-white line-clamp-2"
                                            x-text="p.title"></div>
                                    </a>
                                    <div class="mt-1 text+[11px] text-gray-500 dark:text-white/70 flex items-center"><i
                                            data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i><span
                                            x-text="Number(p.views||0).toLocaleString()"></span></div>
                                    <div class="mt-auto flex flex-col items-center">
                                        <div class="text-sm font-bold text-primary h-5 flex items-center"
                                            :class="(p.has_pricing&&p.lowest_price)?'':'invisible'"
                                            x-text="formatPrice(p.lowest_price)"></div>
                                        <div class="mt-2 flex items-center gap-2 w-full">
                                            <template x-if="p.has_pricing">
                                                <a :href="'<?= BASE_URL ?>view/product/' + p.id + '?action=buy'"
                                                    class="flex-1 inline-flex items-center justify-center h-9 rounded-lg bg-emerald-600 text-white text-xs font-medium">Buy</a>
                                            </template>
                                            <button @click="sellProduct(p.id,p.title)"
                                                class="flex-1 inline-flex items-center justify-center h-9 rounded-lg bg-sky-600 text-white text-xs font-medium">Sell</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function materialsYard() {
        return {
            BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
            currentPage: 1, isLoading: false, hasMoreProducts: true,
            currentSearchQuery: '<?= addslashes($searchQuery) ?>',
            currentCategoryId: '<?= addslashes($categoryId) ?>',
            totalProductsLoaded: 0, mobileDropdownOpen: false,
            mobileQuery: '<?= $categoryNameForMobile ?>',
            allCategories: <?= $categoriesJson ?>,
            miniProducts: <?= json_encode(array_slice($products, 0, 12), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>,
            shareCache: {}, sharePromises: {},
            init() {
                this.$nextTick(() => {
                    if (this.currentSearchQuery) { this.performSearch(this.currentSearchQuery, 1); this.setupInfiniteScroll(); }
                    else { this.setupInfiniteScroll(); this.totalProductsLoaded = <?= count($products) ?>; this.currentPage = 2; }
                    this.refreshIcons();
                    window.addEventListener('zz:session-login', e => this.handlePostLogin(e.detail || {}));
                    window.zzSellProduct = (id, title) => this.sellProduct(id, title);
                    const p = window.__pendingVendorAction; if (p && p.type === 'sell' && p.product_id && p.title) { this.resumeSell(p.product_id, p.title); window.setPendingVendorAction(null); }
                });
            },
            refreshIcons() { try { if (window.lucide && lucide.createIcons) lucide.createIcons() } catch (e) { } },
            filterCategories(t) { const q = (t || '').toLowerCase(); document.querySelectorAll('.cat-item').forEach(el => { const n = el.getAttribute('data-category-name') || ''; el.style.display = n.includes(q) ? 'flex' : 'none'; }); },
            filteredMobileCategories() { const q = (this.mobileQuery || '').toLowerCase(); return this.allCategories.filter(c => (c.name || '').toLowerCase().includes(q)); },
            gotoCategory(id) { window.location.href = '<?= BASE_URL ?>view/category/' + id; },
            setupInfiniteScroll() { window.addEventListener('scroll', () => { if (this.isLoading || !this.hasMoreProducts) return; const sp = window.innerHeight + window.scrollY; const h = document.body.offsetHeight; if (sp >= h * 0.8) { if (this.currentSearchQuery) { this.performSearch(this.currentSearchQuery, this.currentPage, true) } else { this.loadMoreProducts() } } }); },
            loadMoreProducts() {
                if (this.isLoading || !this.hasMoreProducts) return;
                this.isLoading = true;
                const lm = document.getElementById('loadingMore'); lm.classList.remove('hidden');
                const endpoint = this.currentSearchQuery ? 'search' : 'products';
                const params = new URLSearchParams({ ajax: endpoint, page: this.currentPage, limit: 12 }); if (this.currentCategoryId) params.append('categoryId', this.currentCategoryId);
                fetch(`?${params.toString()}`).then(r => r.json()).then(d => {
                    setTimeout(() => { lm.classList.add('hidden'); if (d.products && d.products.length > 0) { this.renderProducts(d.products, true); this.currentPage++; this.totalProductsLoaded += d.products.length; this.hasMoreProducts = d.hasMore; const pc = document.getElementById('productCount'); if (pc) pc.textContent = d.total || this.totalProductsLoaded; } else { this.hasMoreProducts = false; } this.isLoading = false; }, 800);
                }).catch(() => { this.isLoading = false; lm.classList.add('hidden') });
            },
            performSearch(q, page = 1, append = false) {
                if (this.isLoading) return; this.isLoading = true;
                const sk = document.getElementById('loadingSkeleton'), grid = document.getElementById('productsGrid'), lm = document.getElementById('loadingMore'), nr = document.getElementById('noResults'), cs = document.getElementById('categoriesSection');
                if (!append) { sk.classList.remove('hidden'); grid.classList.add('hidden'); nr.classList.add('hidden'); cs.classList.add('hidden'); this.currentPage = 1; this.totalProductsLoaded = 0; } else { lm.classList.remove('hidden'); }
                fetch(`?ajax=search&q=${encodeURIComponent(q)}&page=${page}&limit=12`).then(r => r.json()).then(d => {
                    setTimeout(() => {
                        if (!append) { sk.classList.add('hidden'); grid.classList.remove('hidden'); if (d.categories && d.categories.length > 0) { this.renderCategories(d.categories); cs.classList.remove('hidden'); } grid.innerHTML = ''; this.totalProductsLoaded = 0; } else { lm.classList.add('hidden'); }
                        if (d.products && d.products.length > 0) { this.renderProducts(d.products, append); this.currentPage = append ? page + 1 : 2; this.totalProductsLoaded += d.products.length; this.hasMoreProducts = d.hasMore; } else if (!append) { nr.classList.remove('hidden'); grid.classList.add('hidden'); }
                        const pc = document.getElementById('productCount'); if (pc) pc.textContent = d.total || this.totalProductsLoaded; this.isLoading = false;
                    }, append ? 800 : 1200);
                }).catch(() => { this.isLoading = false; sk.classList.add('hidden'); lm.classList.add('hidden'); if (!append) nr.classList.remove('hidden') });
            },
            renderCategories(cats) {
                const el = document.getElementById('categoriesGrid');
                el.innerHTML = cats.map(c => `<div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-4 hover:shadow-md border border-gray-200 dark:border-white/10 cursor-pointer" onclick="window.location.href='<?= BASE_URL ?>view/category/${c.id}'"><div class="flex items-center"><div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4 flex-shrink-0"><i data-lucide='tag' class='w-6 h-6 text-red-600'></i></div><div><h3 class="font-medium text-gray-900 dark:text-white">${this.escapeHtml(c.name)}</h3></div></div></div>`).join('');
                this.refreshIcons();
            },
            renderProducts(items, append = false) {
                const grid = document.getElementById('productsGrid');
                const html = items.map(p => `
                <div class="product-card relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                    <div class="relative">
                        <img src="${p.primary_image}" alt="${this.escapeHtml(p.title)}" class="w-full h-40 md:h-48 object-cover">
                        ${p.search_score ? `<div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">${Math.min(Math.round(p.search_score * 100), 100)}% match</div>` : ''}
                        <div class="product-details-btn"><a href="<?= BASE_URL ?>view/product/${p.id}" class="bg-white dark:bg-secondary text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">View Details</a></div>
                    </div>
                    <div class="p-3 md:p-5 flex flex-col flex-1">
                        <h3 class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-sm md:text-base">${this.escapeHtml(p.title)}</h3>
                        <div class="flex-1 flex flex-col">
                            <p class="text-gray-600 dark:text-white/70 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">${this.escapeHtml(p.description || '')}</p>
                            <div class="flex items-center text-gray-500 dark:text-white/70 text-xs md:text-sm mb-3"><i data-lucide="eye" class="w-4 h-4 mr-1"></i><span>${parseInt(p.views || 0).toLocaleString()} views</span></div>
                            <div class="mt-auto flex flex-col items-center">
                                <div class="text-sm font-bold text-[#D92B13] h-5 flex items-center ${p.has_pricing && p.lowest_price ? '' : 'invisible'}">${p.has_pricing && p.lowest_price ? this.formatPrice(p.lowest_price) : ''}</div>
                                <div class="mt-2 flex gap-2 w-full">
                                    ${p.has_pricing ? `<a href="<?= BASE_URL ?>view/product/${p.id}?action=buy" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md flex items-center justify-center flex-1 text-xs md:text-sm font-medium"><i data-lucide='shopping-cart' class='w-4 h-4 mr-1'></i>Buy</a>` : ''}
                                    <button class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md flex items-center justify-center flex-1 text-xs md:text-sm font-medium" onclick="window.zzSellProduct('${p.id}','${this.escapeAttr(p.title)}')"><i data-lucide='tags' class='w-4 h-4 mr-1'></i>Sell</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`).join('');
                if (append) grid.insertAdjacentHTML('beforeend', html); else grid.innerHTML = html;
                this.refreshIcons();
            },
            formatPrice(price) { if (!price || price <= 0) return null; return 'UGX ' + parseInt(price).toLocaleString() + '/=' },
            escapeHtml(t) { const d = document.createElement('div'); d.textContent = t || ''; return d.innerHTML },
            escapeAttr(t) { return (t || '').replace(/[&<>"']/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[s])) },
            clearSelection() { window.location.href = '<?= BASE_URL ?>materials-yard'; },
            async getShareUrl() {
                const key = this.currentCategoryId ? `category:${this.currentCategoryId}` : 'page';
                if (this.shareCache[key]) return this.shareCache[key];
                if (this.sharePromises[key]) return await this.sharePromises[key];
                const payload = new URLSearchParams();
                payload.set('action', 'create');
                if (this.currentCategoryId) {
                    payload.set('target_type', 'category');
                    payload.set('target_id', this.currentCategoryId);
                    payload.set('target_url', this.BASE_URL + 'view/category/' + this.currentCategoryId);
                } else {
                    payload.set('target_type', 'custom');
                    payload.set('target_url', window.location.href);
                }
                const req = fetch(this.BASE_URL + 'fetch/manageShareLinks.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: payload
                }).then(r => r.json()).then(d => {
                    if (d && d.success && d.data && d.data.short_url) return d.data.short_url;
                    throw new Error(d && d.message ? d.message : 'Failed to create link');
                });
                this.sharePromises[key] = req;
                try {
                    const u = await req;
                    this.shareCache[key] = u;
                    return u;
                } finally {
                    delete this.sharePromises[key];
                }
            },
            async copyLink() {
                try {
                    const u = await this.getShareUrl();
                    await navigator.clipboard.writeText(u);
                    if (typeof showToast === 'function') showToast('Link copied', 'success');
                } catch (e) {
                    if (typeof showToast === 'function') showToast('Failed to copy', 'error');
                }
            },
            async shareOnWhatsApp() {
                try {
                    const url = await this.getShareUrl();
                    const t = "<?= addslashes($pageTitle) ?>";
                    const m = `Check out ${this.currentCategoryId ? '*' + t + '*' : '*' + t + '*'} on Zzimba Online:\n\n${url}`;
                    window.open(`https://wa.me/?text=${encodeURIComponent(m)}`, '_blank');
                } catch (e) { }
            },
            async shareOnFacebook() {
                try {
                    const u = await this.getShareUrl();
                    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(u)}`, '_blank');
                } catch (e) { }
            },
            async shareOnTwitter() {
                try {
                    const u = await this.getShareUrl();
                    const t = "<?= addslashes($pageTitle) ?>";
                    const m = `Check out ${t} on Zzimba Online:`;
                    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(m)}&url=${encodeURIComponent(u)}`, '_blank');
                } catch (e) { }
            },
            async shareOnLinkedIn() {
                try {
                    const u = await this.getShareUrl();
                    const t = "<?= addslashes($pageTitle) ?>";
                    window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(u)}&title=${encodeURIComponent(t)}`, '_blank');
                } catch (e) { }
            },
            isAdminUser(p) { if (!p) return false; if (p.is_admin === true) return true; if (p.user && p.user.is_admin === true) return true; const r = (p.role_slug || p.role || '').toString().toLowerCase(); return r === 'admin' || r === 'super-admin'; },
            async sellProduct(id, title) { const ok = await this.ensureSession({ type: 'sell', product_id: id, title: title }); if (!ok) return; const s = await this.checkSession().catch(() => ({})); const isAdmin = !!(s && (s.is_admin === true || (s.user && s.user.is_admin === true))); if (isAdmin) return; this.resumeSell(id, title); },
            resumeSell(id, title) { if (typeof openVendorSellModal === 'function') { openVendorSellModal(id, title) } },
            async handlePostLogin(user) { const a = window.__pendingVendorAction; if (!a) return; if (a.type === 'sell' && a.product_id && a.title) { let isAdmin = this.isAdminUser(user); if (!isAdmin) { try { const s = await this.checkSession(); if (typeof s.is_admin !== 'undefined') isAdmin = !!s.is_admin; else if (s.user && typeof s.user.is_admin !== 'undefined') isAdmin = !!s.user.is_admin; } catch (e) { } } if (!isAdmin) this.resumeSell(a.product_id, a.title); window.setPendingVendorAction(null); } },
            checkSession() { return fetch((this.BASE_URL) + 'fetch/check-session.php', { credentials: 'include' }).then(r => r.json()).then(d => d.success ? d : { logged_in: false }).catch(() => ({ logged_in: false })) },
            async ensureSession(p) { try { const s = await this.checkSession(); if (!s.logged_in) { if (p) window.setPendingVendorAction(p); if (typeof openAuthModal === 'function') openAuthModal(); else alert('Please log in to continue.'); return false; } return true; } catch (e) { return false } }
        }
    }
    function showToast(m, t = 'success') { const el = document.createElement('div'); el.className = `fixed top-4 left-1/2 -translate-x-1/2 ${t === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity`; el.textContent = m; document.body.appendChild(el); setTimeout(() => el.classList.add('opacity-100'), 10); setTimeout(() => { el.classList.remove('opacity-100'); setTimeout(() => el.remove(), 300) }, 3000); }
</script>

<?php
$mainContent = ob_get_clean();

include __DIR__ . '/master.php';
?>