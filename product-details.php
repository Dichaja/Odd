<?php
require_once __DIR__ . '/config/config.php';

$productId = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($productId)) {
    header('Location: ' . BASE_URL . 'materials-yard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'log_view') {
        header('Content-Type: application/json');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        if ($origin && stripos($origin, $host) !== 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid origin']);
            exit;
        }
        $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (strpos($ua, 'bot') !== false || strpos($ua, 'spider') !== false || strpos($ua, 'crawler') !== false || strpos($ua, 'headless') !== false) {
            echo json_encode(['success' => true, 'unique_views' => null, 'total_views' => null]);
            exit;
        }
        $sessionId = trim($_POST['session_id'] ?? '');
        $productIdPost = trim($_POST['product_id'] ?? '');
        if ($sessionId === '' || $productIdPost === '') {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        try {
            $tz = new DateTimeZone('Africa/Kampala');
            $createdAt = (new DateTime('now', $tz))->format('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO product_views (id,product_id,session_id,created_at,view_count) VALUES (?,?,?,?,1) ON DUPLICATE KEY UPDATE view_count=view_count+1");
            $ok = $stmt->execute([generateUlid(), $productIdPost, $sessionId, $createdAt]);
            if (!$ok) {
                echo json_encode(['success' => false, 'message' => 'Failed to update view count']);
                exit;
            }
            $countStmt = $pdo->prepare("SELECT COUNT(*) AS unique_views, COALESCE(SUM(view_count),0) AS total_views FROM product_views WHERE product_id=?");
            $countStmt->execute([$productIdPost]);
            $row = $countStmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'unique_views' => (int) ($row['unique_views'] ?? 0), 'total_views' => (int) ($row['total_views'] ?? 0)]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }
}

function generateSeoMetaTags($product)
{
    $title = htmlspecialchars($product['title'] ?? 'Product') . ' | Zzimba Online';
    if (!empty($product['description'])) {
        $d = strip_tags($product['description']);
        $meta = strlen($d) > 160 ? substr($d, 0, 157) . '...' : $d;
    } else {
        $meta = 'Discover quality ' . ($product['title'] ?? 'products') . ' on Zzimba Online. Your trusted marketplace for construction materials and more.';
    }
    $meta = htmlspecialchars($meta);
    $ogDescription = !empty($product['description']) ? htmlspecialchars(strip_tags($product['description'])) : 'Discover quality ' . ($product['title'] ?? 'products') . ' on Zzimba Online. Your trusted marketplace for construction materials and more.';
    if (!empty($product['primary_image']) && !strpos($product['primary_image'], 'placehold.co')) {
        $ogImage = $product['primary_image'];
    } else {
        $ogImage = "https://placehold.co/1200x630/e2e8f0/1e293b?text=" . urlencode($product['title'] ?? 'Product');
    }
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return [
        'title' => $title,
        'description' => $meta,
        'og_title' => $title,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'og_url' => $currentUrl,
        'og_type' => 'product'
    ];
}

function getProductImages($productId)
{
    $dir = __DIR__ . '/img/products/' . $productId . '/';
    $images = [];
    if (is_dir($dir)) {
        $json = $dir . 'images.json';
        if (file_exists($json)) {
            $data = json_decode(file_get_contents($json), true);
            if (!empty($data['images'])) {
                foreach ($data['images'] as $name) {
                    if (file_exists($dir . $name)) {
                        $images[] = BASE_URL . 'img/products/' . $productId . '/' . $name;
                    }
                }
            }
        } else {
            foreach (scandir($dir) as $f) {
                if ($f !== '.' && $f !== '..') {
                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $images[] = BASE_URL . 'img/products/' . $productId . '/' . $f;
                    }
                }
            }
        }
    }
    if (empty($images)) {
        $images[] = "https://placehold.co/800x600/e2e8f0/1e293b?text=" . urlencode("Product Image");
    }
    return $images;
}

function getShortDescription($description, $maxLength = 150)
{
    if (strlen($description) <= $maxLength) {
        return $description;
    }
    $s = substr($description, 0, $maxLength);
    $p = strrpos($s, ' ');
    if ($p !== false) {
        $s = substr($s, 0, $p);
    }
    return $s . '...';
}

function formatPrice($price)
{
    if ($price === null || $price <= 0) {
        return null;
    }
    return 'UGX ' . number_format($price, 0) . '/=';
}

function getSupplierRegions($pdo, $productId)
{
    $stmt = $pdo->prepare("
        SELECT vs.region,vs.district,COUNT(DISTINCT vs.id) as vendor_count
        FROM vendor_stores vs
        INNER JOIN store_categories sc ON vs.id=sc.store_id
        INNER JOIN store_products sp ON sc.id=sp.store_category_id
        INNER JOIN product_pricing pp ON sp.id=pp.store_products_id
        WHERE sp.product_id=? 
          AND vs.status='active' 
          AND sc.status='active' 
          AND sp.status='active'
          AND pp.status='active'
        GROUP BY vs.region,vs.district 
        ORDER BY vs.region,vendor_count DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.title,
        p.description,
        p.featured,
        p.category_id,
        pc.name as category_name,
        (SELECT image_url 
         FROM product_images 
         WHERE product_id=p.id AND is_primary=1 
         LIMIT 1) AS primary_image,
        EXISTS(
            SELECT 1 
            FROM store_products sp 
            JOIN store_categories sc ON sc.id=sp.store_category_id
            JOIN vendor_stores vs ON vs.id=sc.store_id 
            JOIN product_pricing pp ON pp.store_products_id=sp.id
            WHERE sp.product_id=p.id 
              AND vs.status='active'
              AND pp.status='active'
        ) AS has_pricing,
        (SELECT MIN(pp.price) 
         FROM store_products sp 
         JOIN store_categories sc ON sc.id=sp.store_category_id
         JOIN vendor_stores vs ON vs.id=sc.store_id 
         JOIN product_pricing pp ON pp.store_products_id=sp.id
         WHERE sp.product_id=p.id 
           AND vs.status='active'
           AND pp.status='active'
        ) AS min_price,
        (SELECT COUNT(*) 
         FROM product_views 
         WHERE product_id=p.id
        ) AS unique_views,
        (SELECT COALESCE(SUM(view_count),0) 
         FROM product_views 
         WHERE product_id=p.id
        ) AS total_views
    FROM products p 
    LEFT JOIN product_categories pc ON p.category_id=pc.id
    WHERE p.id=? 
      AND p.status='published'
");
$stmt->execute([$productId]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: ' . BASE_URL . 'materials-yard');
    exit;
}

$productImages = getProductImages($productId);
$product['primary_image'] = $productImages[0];
$seoTags = generateSeoMetaTags($product);
$pageTitle = $seoTags['title'];
$shortDescription = getShortDescription($product['description']);
$supplierRegions = getSupplierRegions($pdo, $productId);

$relatedStmt = $pdo->prepare("
    SELECT 
        p.id,
        p.title,
        p.description,
        p.featured,
        (SELECT image_url 
         FROM product_images 
         WHERE product_id=p.id AND is_primary=1 
         LIMIT 1) AS primary_image,
        EXISTS(
            SELECT 1 
            FROM store_products sp 
            JOIN store_categories sc ON sc.id=sp.store_category_id
            JOIN vendor_stores vs ON vs.id=sc.store_id 
            JOIN product_pricing pp ON pp.store_products_id=sp.id
            WHERE sp.product_id=p.id 
              AND vs.status='active'
              AND pp.status='active'
        ) AS has_pricing,
        (SELECT MIN(pp.price) 
         FROM store_products sp 
         JOIN store_categories sc ON sc.id=sp.store_category_id
         JOIN vendor_stores vs ON vs.id=sc.store_id 
         JOIN product_pricing pp ON pp.store_products_id=sp.id
         WHERE sp.product_id=p.id 
           AND vs.status='active'
           AND pp.status='active'
        ) AS lowest_price,
        (SELECT COUNT(*) 
         FROM product_views 
         WHERE product_id=p.id
        ) AS unique_views,
        (SELECT COALESCE(SUM(view_count),0) 
         FROM product_views 
         WHERE product_id=p.id
        ) AS total_views
    FROM products p
    WHERE p.category_id=? 
      AND p.id!=? 
      AND p.status='published'
    ORDER BY RAND() 
    LIMIT 8
");
$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = [];
while ($row = $relatedStmt->fetch()) {
    $imgs = getProductImages($row['id']);
    $row['primary_image'] = $imgs[0];
    $row['has_pricing'] = (bool) $row['has_pricing'];
    $row['lowest_price'] = $row['lowest_price'] ? (float) $row['lowest_price'] : null;
    $relatedProducts[] = $row;
}

$activeNav = "materials";

$reviews = [];
$reviewStats = [
    'total_reviews' => 0,
    'average_rating' => 0,
    'rating_breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
];

try {
    $reviewStmt = $pdo->prepare("
        SELECT 
            u.username,
            r.rating,
            r.comment,
            r.is_verified,
            r.created_at,
            DATE_FORMAT(r.created_at, '%Y-%m-%d') as review_date
        FROM product_reviews r, zzimba_users u 
        WHERE u.id = r.user_id 
          AND r.product_id = ? 
          AND r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $reviewStmt->execute([$productId]);
    $reviews = $reviewStmt->fetchAll();

    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            AVG(rating) as average_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM product_reviews 
        WHERE product_id = ? 
          AND status = 'approved'
    ");
    $statsStmt->execute([$productId]);
    $stats = $statsStmt->fetch();

    if ($stats) {
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
    }
} catch (Exception $e) {
    error_log('Error fetching reviews: ' . $e->getMessage());
}

$imagesJson = json_encode($productImages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$regionsJson = json_encode($supplierRegions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$relatedJson = json_encode($relatedProducts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

ob_start();
?>

<style>
    .share-container {
        display: flex;
        align-items: center;
        gap: .5rem
    }

    .share-label {
        font-size: 12px;
        font-weight: 500;
        color: #fff
    }

    .share-buttons {
        display: flex;
        gap: .5rem
    }

    .tooltip {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        background: #1F2937;
        color: #fff;
        padding: .5rem;
        border-radius: .25rem;
        font-size: .75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity .2s, visibility .2s;
        z-index: 10
    }

    .tooltip::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 8px;
        height: 8px;
        background: #1F2937
    }

    .share-button:hover .tooltip {
        opacity: 1;
        visibility: visible
    }

    .gallery-container {
        position: relative;
        overflow: hidden
    }

    .gallery-scroll {
        display: flex;
        transition: transform .5s ease
    }

    .gallery-scroll img {
        flex-shrink: 0
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .fade-in {
        animation: fadeIn .5s ease forwards
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

    .region-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(217, 43, 19, .1), transparent);
        transition: left .5s
    }

    .region-card:hover::before {
        left: 100%
    }

    [x-cloak] {
        display: none !important
    }

    .share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        min-width: 2rem;
        min-height: 2rem;
        aspect-ratio: 1/1;
        border-radius: 9999px;
        color: #fff;
        border: 1px solid #fff;
        background: transparent;
        transition: all .2s ease;
        position: relative;
        line-height: 0;
        box-sizing: border-box
    }

    .share-button svg {
        display: block;
        width: 16px;
        height: 16px
    }

    .review-form-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        padding: 1.5rem;
    }

    .review-submit-btn {
        background: linear-gradient(135deg, #D92B13 0%, #B91C1C 100%);
        border: none;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
        width: 100%;
        min-height: 48px;
    }

    .review-submit-btn:hover {
        background: linear-gradient(135deg, #B91C1C 0%, #991B1B 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(217, 43, 19, 0.3);
    }

    .review-submit-btn:active {
        transform: translateY(0);
    }

    .review-submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .star-rating {
        display: flex;
        gap: 0.25rem;
    }

    .star-rating button {
        padding: 0.25rem;
        border: none;
        background: transparent;
        cursor: pointer;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }

    .star-rating button:hover {
        background-color: rgba(217, 43, 19, 0.1);
    }
</style>

<script>
    window.__pendingVendorAction = null;
    window.setPendingVendorAction = (a) => { window.__pendingVendorAction = a || null };
    (function () { const wrap = () => { const orig = window.updateUIAfterLogin; window.updateUIAfterLogin = function (user) { try { typeof orig === 'function' && orig(user) } catch (e) { } try { window.dispatchEvent(new CustomEvent('zz:session-login', { detail: user || {} })) } catch (e) { } const el = document.querySelector('[x-data="productDetails()"]'); if (el && el.__x) { try { el.__x.$data.handlePostLogin(user || {}) } catch (e) { } } }; }; if (document.readyState === 'complete' || document.readyState === 'interactive') { wrap() } else { document.addEventListener('DOMContentLoaded', wrap) } })();
</script>

<div x-data="productDetails()" x-init="init()" x-cloak>
    <div class="relative h-50 md:h-64 w-full overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-800/70 to-gray-900/90 z-10"></div>
        <img src="<?= $productImages[0] ?>" alt="<?= htmlspecialchars($product['title']) ?> Banner"
            class="w-full h-full object-cover">
        <div class="container mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 pb-10 md:pt-12 z-20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl md:text-3xl font-bold text-white mb-2"><?= htmlspecialchars($product['title']) ?>
                    </h1>
                    <nav class="flex text-xs md:text-sm text-gray-300 whitespace-nowrap">
                        <a href="<?= BASE_URL ?>" class="hover:text-white truncate max-w-[30%]">Zzimba Online</a><span
                            class="mx-2">/</span>
                        <a href="<?= BASE_URL ?>materials-yard" class="hover:text-white truncate max-w-[30%]">Materials
                            Yard</a><span class="mx-2">/</span>
                        <span
                            class="text-white font-medium truncate max-w-[40%]"><?= htmlspecialchars($product['title']) ?></span>
                    </nav>
                </div>
                <div class="share-container mt-3 md:mt-0">
                    <span class="share-label">SHARE</span>
                    <div class="share-buttons">
                        <button @click="copyLink" class="share-button" aria-label="Copy link">
                            <span class="hidden md:block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.07 0l3.54-3.54a5 5 0 0 0-7.07-7.07L12 3" />
                                    <path d="M14 11a5 5 0 0 0-7.07 0L3.39 14.54a5 5 0 1 0 7.07 7.07L12 21" />
                                </svg>
                            </span>
                            <span class="md:hidden">
                                <i class="fa-solid fa-link" style="color:#ffffff;"></i>
                            </span>
                            <span class="tooltip">Copy link to clipboard</span>
                        </button>
                        <button @click="shareOnWhatsApp" class="share-button" aria-label="Share on WhatsApp">
                            <span class="hidden md:block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 1 1-3.46-6.86L21 3" />
                                    <path d="M22 2l-1 6-6-1" />
                                </svg>
                            </span>
                            <span class="md:hidden">
                                <i class="fa-brands fa-whatsapp" style="color:#ffffff;"></i>
                            </span>
                            <span class="tooltip">Share on WhatsApp</span>
                        </button>
                        <button @click="shareOnFacebook" class="share-button" aria-label="Share on Facebook">
                            <span class="hidden md:block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 1 0-6 6h6" />
                                    <path d="M18 12v10" />
                                    <path d="M22 12h-4" />
                                </svg>
                            </span>
                            <span class="md:hidden">
                                <i class="fa-brands fa-facebook-f" style="color:#ffffff;"></i>
                            </span>
                            <span class="tooltip">Share on Facebook</span>
                        </button>
                        <button @click="shareOnTwitter" class="share-button" aria-label="Post on X">
                            <span class="hidden md:block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 2L11 13" />
                                    <path d="M22 2l-6 20-5-9-9-5 20-6z" />
                                </svg>
                            </span>
                            <span class="md:hidden">
                                <i class="fa-brands fa-x-twitter" style="color:#ffffff;"></i>
                            </span>
                            <span class="tooltip">Post on X</span>
                        </button>
                        <button @click="shareOnLinkedIn" class="share-button" aria-label="Share on LinkedIn">
                            <span class="hidden md:block">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="9" width="7" height="13" />
                                    <circle cx="5.5" cy="5.5" r="2.5" />
                                    <path d="M15 9h7v13h-7z" />
                                    <path d="M15 13c0-2 2-3 4-3" />
                                </svg>
                            </span>
                            <span class="md:hidden">
                                <i class="fa-brands fa-linkedin-in" style="color:#ffffff;"></i>
                            </span>
                            <span class="tooltip">Share on LinkedIn</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="md:hidden px-4 py-4 bg-white dark:bg-secondary">
        <div class="grid grid-cols-2 gap-2">
            <button onclick="openMobileSearch&&openMobileSearch()"
                class="flex flex-col items-center justify-center rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10">
                    <i data-lucide="search" class="w-5 h-5 text-secondary dark:text-white"></i>
                </span>
                <span class="mt-1 text-xs text-secondary dark:text-white">Search</span>
            </button>
            <a href="<?= BASE_URL ?>request-for-quote"
                class="flex flex-col items-center justify-center rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-primary/10 text-primary">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </span>
                <span class="mt-1 text-xs text-secondary dark:text-white">Request a Quote Now</span>
            </a>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-10 lg:-mt-20 relative z-30">
        <div class="bg-white dark:bg-secondary rounded-xl p-6 md:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <div class="space-y-6">
                    <div class="relative rounded-2xl overflow-hidden bg-white dark:bg-secondary">
                        <span class="absolute top-4 right-4 text-white text-xs font-bold px-3 py-1 rounded-full z-20"
                            style="background-color:#D92B13;"><?= $product['featured'] ? 'FEATURED' : 'POPULAR' ?></span>
                        <div class="relative w-full rounded-2xl overflow-hidden">
                            <template x-for="(img,idx) in images" :key="idx">
                                <img :src="img" x-show="selectedImage===idx"
                                    class="absolute inset-0 w-full h-auto object-cover rounded-2xl transition-opacity duration-700"
                                    :class="imageFade && selectedImage===idx ? 'opacity-100' : 'opacity-0'">
                            </template>
                            <img :src="images[selectedImage]" class="invisible w-full h-auto rounded-2xl" alt="">
                        </div>
                    </div>
                    <?php if (count($productImages) > 1): ?>
                        <div class="gallery-container">
                            <div class="gallery-scroll flex gap-2 overflow-x-auto hide-scrollbar">
                                <template x-for="(img,idx) in images" :key="idx">
                                    <button type="button"
                                        class="cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 transition-colors flex-shrink-0"
                                        :class="idx===selectedImage ? 'border-[#D92B13]' : 'border-gray-200 dark:border-white/10 hover:border-[#D92B13]'"
                                        @click="selectImage(idx)">
                                        <img :src="img" alt="" class="w-full h-full object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="space-y-6">
                    <div
                        class="inline-flex items-center px-3 py-1 bg-gray-100 dark:bg-white/5 text-gray-800 dark:text-white rounded-full text-sm">
                        <span class="font-medium text-gray-500 dark:text-white/70">Category:</span>
                        <a href="<?= BASE_URL ?>view/category/<?= $product['category_id'] ?>"
                            class="font-semibold hover:underline ml-1"
                            style="color:#D92B13;"><?= htmlspecialchars($product['category_name']) ?></a>
                    </div>

                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?= htmlspecialchars($product['title']) ?>
                    </h2>

                    <div class="flex flex-wrap items-center gap-6 text-sm">
                        <div class="flex items-center">
                            <div class="flex mr-2">
                                <?php
                                $avgRating = $reviewStats['average_rating'];
                                for ($i = 1; $i <= 5; $i++):
                                    ?>
                                    <i data-lucide="star"
                                        class="w-4 h-4 <?= $i <= round($avgRating) ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="text-gray-600">
                                <?php if ($reviewStats['total_reviews'] > 0): ?>
                                    <?= $avgRating ?>/5 (<?= $reviewStats['total_reviews'] ?>
                                    Review<?= $reviewStats['total_reviews'] != 1 ? 's' : '' ?>)
                                <?php else: ?>
                                    No reviews yet
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="flex items-center text-gray-600">
                            <i data-lucide="eye" class="w-4 h-4 mr-1" style="color:#D92B13;\"></i>
                            <span id="view-count"><?= number_format($product['unique_views']) ?> Views</span>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-secondary rounded-xl p-6">
                        <p class="text-gray-700 dark:text-white/80 leading-relaxed mb-6 line-clamp-2">
                            <?= htmlspecialchars($shortDescription) ?>
                        </p>
                        <div class="flex items-center mb-6">
                            <span class="text-sm font-medium text-gray-500 dark:text-white/70 mr-2">Brand:</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">GENERIC Construction
                                Materials</span>
                        </div>

                        <?php if ($product['has_pricing'] && $product['min_price']): ?>
                            <div class="text-3xl font-bold mb-6 text-[#D92B13]">
                                <span class="block text-sm font-medium text-gray-500 dark:text-white/70 mb-1">Starting
                                    Price:</span>
                                <?= formatPrice($product['min_price']) ?>
                            </div>
                        <?php else: ?>
                            <div class="text-lg text-gray-600 dark:text-white/70 font-medium mb-6">Contact Us for pricing
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-2">
                            <?php if ($product['has_pricing']): ?>
                                <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=buy"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1">
                                    <i data-lucide="shopping-cart" class="w-5 h-5 mr-2"></i>Buy
                                </a>
                            <?php endif; ?>
                            <button
                                @click="sellProduct('<?= $product['id'] ?>','<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>')"
                                class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1">
                                <i data-lucide="tags" class="w-5 h-5 mr-2"></i>Sell
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="border-b border-gray-200 dark:border-white/10 mb-6">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <button @click="activeTab='store'"
                    :class="activeTab==='store' ? 'border-[#D92B13] text-[#D92B13]' : 'border-transparent text-gray-500 dark:text-white/70 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-white/20'"
                    class="font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button">
                    <i data-lucide="store" class="w-4 h-4 mr-2 inline"></i>Find Supplier
                </button>
                <button @click="activeTab='description'"
                    :class="activeTab==='description' ? 'border-[#D92B13] text-[#D92B13]' : 'border-transparent text-gray-500 dark:text-white/70 hover:text-gray-700 dark:hover:text-white hover:border-gray-300 dark:hover:border-white/20'"
                    class="font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button">
                    <i data-lucide="info" class="w-4 h-4 mr-2 inline"></i>Description
                </button>
                <button @click="activeTab='reviews'"
                    :class="activeTab==='reviews' ? 'border-[#D92B13] text-[#D92B13]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button">
                    <i data-lucide="star" class="w-4 h-4 mr-2 inline"></i> Reviews
                    (<?= $reviewStats['total_reviews'] ?>)
                </button>
            </nav>
        </div>

        <div x-show="activeTab==='description'" class="tab-content">
            <div
                class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6 lg:p-8 border border-gray-200 dark:border-white/10">
                <h3 class="text-xl font-semibold mb-6 text-gray-800 dark:text-white">Product Description</h3>
                <div class="text-gray-700 dark:text-white/80 leading-relaxed space-y-4">
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
            </div>
        </div>

        <div x-show="activeTab==='store'" class="tab-content">
            <div
                class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6 lg:p-8 border border-gray-200 dark:border-white/10">
                <h3 class="text-xl font-semibold mb-6 text-gray-800 dark:text-white">
                    <i data-lucide="map-pin" class="w-5 h-5 mr-2 inline" style="color:#D92B13;"></i>Supplier Regions
                </h3>
                <?php if (!empty($supplierRegions)): ?>
                    <div class="mb-6">
                        <p class="text-gray-600 dark:text-white/70 mb-4">This product is available from suppliers in
                            <strong><?= count($supplierRegions) ?></strong> region(s). Click on a region to view available
                            suppliers.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($supplierRegions as $region): ?>
                            <div class="bg-gradient-to-br from-slate-50 to-slate-200 dark:from-white/5 dark:to-white/10 border-2 border-slate-200 dark:border-white/10 rounded-xl p-5 cursor-pointer transition-all duration-300 hover:border-[#D92B13] hover:-translate-y-0.5 hover:shadow-lg relative overflow-hidden region-card"
                                @click="showVendorsInRegion('<?= htmlspecialchars($region['region']) ?>')">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-bold text-lg text-gray-800 dark:text-white">
                                        <?= htmlspecialchars($region['region']) ?>
                                    </h4>
                                    <span
                                        class="bg-[#D92B13] text-white text-sm font-bold px-3 py-1 rounded-full"><?= $region['vendor_count'] ?>
                                        <?= $region['vendor_count'] == 1 ? 'Vendor' : 'Vendors' ?></span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-white/70 mb-3">
                                    <i data-lucide="map-pin" class="w-4 h-4 mr-1 inline" style="color:#D92B13;"></i>Districts:
                                    <?= htmlspecialchars($region['district']) ?>
                                </div>
                                <div class="flex items-center justify-end">
                                    <div class="text-[#D92B13] font-medium text-sm">View Suppliers <i data-lucide="arrow-right"
                                            class="w-4 h-4 ml-1 inline"></i></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6">
                        <div class="mb-4">
                            <i data-lucide="store" class="w-16 h-16 text-gray-300 mx-auto"></i>
                        </div>
                        <h5 class="text-sm font-semibold text-gray-600 dark:text-white/80 mb-2">No suppliers found at the
                            moment, please try again later</h5>
                        <a href="<?= BASE_URL; ?>request-for-quote" class="inline-block">
                            <button
                                class="bg-[#D92B13] hover:bg-[#B91C1C] text-white px-6 py-3 rounded-lg transition-colors">
                                <i data-lucide="mail" class="w-5 h-5 mr-2 inline"></i>Request a Quote Now
                            </button>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div x-show="activeTab==='reviews'" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div
                        class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6 lg:p-8 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-800">Customer Reviews</h3>
                            <div class="flex items-center gap-4">
                                <?php if ($reviewStats['average_rating'] > 0): ?>
                                    <div class="flex items-center">
                                        <div class="flex mr-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i data-lucide="star"
                                                    class="w-4 h-4 <?= $i <= round($reviewStats['average_rating']) ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-sm text-gray-600"><?= $reviewStats['average_rating'] ?>/5</span>
                                    </div>
                                <?php endif; ?>
                                <span
                                    class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <?= $reviewStats['total_reviews'] ?> Reviews
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($reviews)): ?>
                            <div class="mb-6 max-h-[500px] overflow-y-auto pr-2 space-y-6">
                                <?php foreach ($reviews as $review): ?>
                                    <div class="border-b border-gray-200 pb-6 mb-6 last:border-0 last:pb-0 last:mb-0 fade-in">
                                        <div class="flex items-center mb-1">
                                            <span
                                                class="font-semibold text-gray-800"><?= htmlspecialchars($review['username']) ?></span>
                                            <?php if ($review['is_verified']): ?>
                                                <span
                                                    class="ml-2 bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-0.5 rounded-full">
                                                    Verified Purchase
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-gray-500 text-sm mb-2"><?= $review['review_date'] ?></div>
                                        <div class="flex mb-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i data-lucide="star"
                                                    class="w-4 h-4 <?= $i <= $review['rating'] ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-gray-700"><?= htmlspecialchars($review['comment']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="mb-4">
                                    <i data-lucide="message-circle" class="w-16 h-16 text-gray-300 mx-auto"></i>
                                </div>
                                <h4 class="text-xl font-semibold text-gray-600 mb-2">No Reviews Yet</h4>
                                <p class="text-gray-500 mb-4">Be the first to review this product!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

                <div>
                    <div class="review-form-container sticky top-4">
    <h4 class="text-lg font-semibold mb-4 text-gray-800">Write a Review</h4>
    <form @submit.prevent="submitReview" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating</label>
            <div class="star-rating flex space-x-1">
                <template x-for="i in 5" :key="i">
                    <button  type="button" @click="reviewRating = i" @mouseover="hoverRating=i" @mouseleave="hoverRating=0">
                        <i  data-lucide="star" class="w-5 h-5" :class="(hoverRating ? i <= hoverRating : i <= reviewRating) ? 'fill-amber-400 stroke-amber-400' 
                                : 'stroke-gray-300'" ></i>
                    </button>
                </template>
            </div>
            <p x-show="reviewRating > 0" class="text-sm text-gray-600 mt-1">
                You rated this product <span x-text="reviewRating"></span> out of 5 stars
            </p>
        </div>
        <div>
            <label for="reviewComment" class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
            <textarea rows="4" maxlength="500" x-model="reviewComment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D92B13] focus:border-transparent resize-none" placeholder="Share your experience with this product... (minimum 10 characters)" required ></textarea>
            <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span  x-show="reviewComment.length > 0 && reviewComment.length < 10" class="text-red-500">
                    Minimum 10 characters required
                </span>
                <span class="ml-auto">
                    <span x-text="reviewComment?.length || 0"></span>/500 characters
                </span>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" :disabled="reviewRating < 1 || reviewComment.length < 10 || isSubmitting" :class="reviewRating < 1 || reviewComment.length < 10 || isSubmitting ? 'opacity-50 cursor-not-allowed' : ''" class="review-submit-btn w-full bg-[#D92B13] hover:bg-[#B91C1C] text-white py-2 rounded-lg font-medium transition-colors">
            <span x-show="!isSubmitting" class="flex items-center justify-center">
                <i data-lucide="send" class="w-5 h-5 mr-2"></i> 
                Submit Review
            </span>
            <span x-show="isSubmitting" class="flex items-center justify-center">
                <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                Submitting...
            </span>
        </button>
    </form>
</div>

                </div>
            </div>
        </div>
    </div>

    <div x-show="vendorModalOpen" x-transition.opacity
        class="fixed inset-0 z-[1000] overflow-auto bg-black/50 backdrop-blur-sm" @click.self="closeVendorModal">
        <div
            class="bg-white dark:bg-secondary my-[5%] mx-auto p-0 border-none rounded-xl w-[90%] max-w-[600px] max-h-[80vh] overflow-hidden shadow-2xl">
            <div class="bg-gradient-to-r from-[#D92B13] to-red-700 text-white p-5 flex items-center justify-between">
                <h2 class="text-xl font-bold">
                    <i data-lucide="store" class="w-5 h-5 mr-2 inline"></i>Suppliers in <span
                        x-text="vendorRegion"></span>
                </h2>
                <button @click="closeVendorModal" class="text-white opacity-80 hover:opacity-100">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div x-show="vendorModalLoading" class="text-center py-8">
                    <div
                        class="inline-block w-5 h-5 border-2 border-gray-300 dark:border-white/20 border-t-[#D92B13] rounded-full animate-spin mx-auto mb-4">
                    </div>
                    <p class="text-gray-600 dark:text-white/70">Loading suppliers...</p>
                </div>
                <div x-show="!vendorModalLoading">
                    <template x-if="vendors.length>0">
                        <div>
                            <div class="mb-4">
                                <p class="text-gray-600 dark:text-white/70">
                                    Found <strong x-text="vendors.length"></strong> supplier<span
                                        x-text="vendors.length===1?'':'s'"></span> in this region:
                                </p>
                            </div>
                            <template x-for="v in vendors" :key="v.id">
                                <div class="bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 rounded-xl p-4 mb-4 transition-all duration-200 hover:border-[#D92B13] hover:shadow-lg hover:shadow-red-50 hover:-translate-y-0.5 cursor-pointer flex items-center gap-4"
                                    @click="gotoVendor(v.id)">
                                    <img :src="v.logo_url ? `${BASE_URL}${v.logo_url}` : `https://placehold.co/56x56/e2e8f0/1e293b?text=${encodeURIComponent((v.name||'')[0]||'V')}`"
                                        :alt="v.name"
                                        class="w-14 h-14 rounded-xl object-cover bg-gray-100 flex-shrink-0">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-lg text-gray-800 dark:text-white mb-1"
                                            x-text="v.name"></h4>
                                        <p class="text-sm text-gray-600 dark:text-white/70">
                                            <i data-lucide="map-pin" class="w-4 h-4 mr-1 inline"
                                                style="color:#D92B13;"></i>
                                            <span x-text="v.district"></span>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="vendors.length===0">
                        <div class="text-center py-8">
                            <i data-lucide="store" class="w-10 h-10 text-gray-300 mx-auto mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-600 dark:text-white/80 mb-2">No Suppliers Found
                            </h4>
                            <p class="text-gray-500 dark:text-white/60">No suppliers found in <span
                                    x-text="vendorRegion"></span> for this product.</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
        <div class="bg-gray-50 dark:bg-[#262626] py-12">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">You May Also Like</h2>
                </div>

                <div class="md:hidden overflow-x-auto hide-scrollbar">
                    <div class="flex gap-4 md:gap-6 snap-x snap-mandatory">
                        <?php foreach ($relatedProducts as $rp): ?>
                            <div
                                class="snap-start shrink-0 w-64 relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden">
                                <div class="relative">
                                    <img src="<?= $rp['primary_image'] ?>" alt="<?= htmlspecialchars($rp['title']) ?>"
                                        class="w-full h-44 object-cover">
                                    <div
                                        class="absolute inset-0 bg-black/70 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                        <a href="<?= BASE_URL ?>view/product/<?= $rp['id'] ?>"
                                            class="bg-white dark:bg-secondary text-gray-800 dark:text-white px-3 py-2 rounded-lg font-medium hover:text-white hover:bg-[#D92B13] transition-colors text-sm">View
                                            Details</a>
                                    </div>
                                </div>
                                <div class="p-4 flex flex-col h-[210px]">
                                    <h3 class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-sm">
                                        <?= htmlspecialchars($rp['title']) ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-white/70 text-xs mb-3 line-clamp-2">
                                        <?= htmlspecialchars(getShortDescription($rp['description'], 100)) ?>
                                    </p>
                                    <div class="flex items-center text-gray-500 dark:text-white/70 text-xs mb-3">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1" style="color:#D92B13;"></i>
                                        <span><?= number_format($rp['unique_views']) ?> views</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div
                                            class="text-sm font-bold text-[#D92B13] h-5 flex items-center <?= ($rp['has_pricing'] && $rp['lowest_price']) ? '' : 'invisible' ?>">
                                            <?= $rp['has_pricing'] && $rp['lowest_price'] ? formatPrice($rp['lowest_price']) : '' ?>
                                        </div>
                                        <div class="mt-2 flex gap-2">
                                            <?php if ($rp['has_pricing']): ?>
                                                <a href="<?= BASE_URL ?>view/product/<?= $rp['id'] ?>?action=buy"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs font-medium">
                                                    <i data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i>Buy
                                                </a>
                                            <?php endif; ?>
                                            <button
                                                @click="sellProduct('<?= $rp['id'] ?>','<?= htmlspecialchars($rp['title'], ENT_QUOTES) ?>')"
                                                class="bg-sky-600 hover:bg-sky-700 text-white px-3 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs font-medium">
                                                <i data-lucide="tags" class="w-4 h-4 mr-1"></i>Sell
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php foreach ($relatedProducts as $rp): ?>
                            <div
                                class="relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden h-full flex flex-col">
                                <div class="relative">
                                    <img src="<?= $rp['primary_image'] ?>" alt="<?= htmlspecialchars($rp['title']) ?>"
                                        class="w-full h-48 object-cover">
                                    <div
                                        class="absolute inset-0 bg-black/70 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
                                        <a href="<?= BASE_URL ?>view/product/<?= $rp['id'] ?>"
                                            class="bg-white dark:bg-secondary text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium hover:text-white hover:bg-[#D92B13] transition-colors text-sm">View
                                            Details</a>
                                    </div>
                                </div>
                                <div class="p-5 flex flex-col flex-1">
                                    <h3 class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-base">
                                        <?= htmlspecialchars($rp['title']) ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-white/70 text-sm mb-3 line-clamp-2">
                                        <?= htmlspecialchars(getShortDescription($rp['description'], 100)) ?>
                                    </p>
                                    <div class="flex items-center text-gray-500 dark:text-white/70 text-sm mb-3">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1" style="color:#D92B13;"></i>
                                        <span><?= number_format($rp['unique_views']) ?> views</span>
                                    </div>
                                    <div class="mt-auto">
                                        <div
                                            class="text-sm font-bold text-[#D92B13] h-5 flex items-center <?= ($rp['has_pricing'] && $rp['lowest_price']) ? '' : 'invisible' ?>">
                                            <?= $rp['has_pricing'] && $rp['lowest_price'] ? formatPrice($rp['lowest_price']) : '' ?>
                                        </div>
                                        <div class="mt-2 flex gap-2">
                                            <?php if ($rp['has_pricing']): ?>
                                                <a href="<?= BASE_URL ?>view/product/<?= $rp['id'] ?>?action=buy"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-sm font-medium">
                                                    <i data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i>Buy
                                                </a>
                                            <?php endif; ?>
                                            <button
                                                @click="sellProduct('<?= $rp['id'] ?>','<?= htmlspecialchars($rp['title'], ENT_QUOTES) ?>')"
                                                class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-sm font-medium">
                                                <i data-lucide="tags" class="w-4 h-4 mr-1"></i>Sell
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function productDetails() {
        return {
            BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
            productId: '<?= $productId ?>',
            productTitle: '<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>',
            images: <?= $imagesJson ?>,
            selectedImage: 0,
            imageFade: true,
            autoRotate: true,
            slideTimer: null,
            shuffledOrder: [],
            activeTab: 'store',
            vendorModalOpen: false,
            vendorModalLoading: false,
            vendorRegion: '',
            vendors: [],
            reviewName: '',
            reviewRating: 0,
            hoverRating: 0,
            reviewComment: '',
            isSubmitting: false,
            init() {
                this.$nextTick(() => {
                    this.logProductView();
                    this.refreshIcons();
                    if (IS_LOGGED_IN && typeof LOGGED_USER !== 'undefined' && LOGGED_USER) {
                        this.reviewName = LOGGED_USER.username || '';
                    }
                });
                window.addEventListener('zz:session-login', e => this.handlePostLogin(e.detail || {}));
                const pending = window.__pendingVendorAction;
                if (pending && pending.type === 'view-suppliers' && pending.region) { this.resumeViewSuppliers(pending.region); window.setPendingVendorAction(null); }
                else if (pending && pending.type === 'sell' && pending.product_id === this.productId) { this.resumeSell(pending.title); }
                const urlAction = new URLSearchParams(window.location.search).get('action');
                if (urlAction === 'sell') this.sellProduct(this.productId, this.productTitle);
                document.addEventListener('visibilitychange', () => { if (document.hidden) this.stopAuto(false); else this.startAuto(); });
            },
            triggerFade() { this.imageFade = false; requestAnimationFrame(() => { this.imageFade = true; }); },
            shuffleImages() {
                const n = this.images.length;
                this.shuffledOrder = Array.from({ length: n }, (_, i) => i).filter(i => i !== this.selectedImage);
                for (let i = this.shuffledOrder.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1));[this.shuffledOrder[i], this.shuffledOrder[j]] = [this.shuffledOrder[j], this.shuffledOrder[i]]; }
            },
            startAuto() {
                if (!this.autoRotate || this.slideTimer) return;
                this.slideTimer = setInterval(() => { if (!this.autoRotate) return; this.nextImage(); }, 5000);
            },
            stopAuto(disable = true) {
                if (this.slideTimer) { clearInterval(this.slideTimer); this.slideTimer = null; }
                if (disable) this.autoRotate = false;
            },
            nextImage() {
                if (!this.shuffledOrder.length) this.shuffleImages();
                const nextIdx = this.shuffledOrder.shift();
                if (typeof nextIdx === 'number') { this.setImage(nextIdx, false); }
            },
            handlePostLogin(user) {
                const a = window.__pendingVendorAction;
                if (a && a.type === 'view-suppliers' && a.region) {
                    this.resumeViewSuppliers(a.region);
                    window.setPendingVendorAction(null);
                }
                if (user && user.username) {
                    this.reviewName = user.username;
                }
                this.$nextTick(() => {
                    this.refreshIcons();
                });
            },
            refreshIcons() { try { if (window.lucide && lucide.createIcons) lucide.createIcons(); } catch (e) { } },
            sellProduct(id, title) { if (typeof openVendorSellModal === 'function') { openVendorSellModal(id, title); } },
            copyLink() {
                const url = window.location.href;
                navigator.clipboard.writeText(url).then(() => { showToast('Link copied to clipboard!', 'success'); }).catch(() => { showToast('Failed to copy link', 'error'); });
            },
            shareOnWhatsApp() {
                const url = window.location.href;
                const message = `Check out *${this.productTitle}* available on Zzimba Online:\n\n${url}`;
                window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
            },
            shareOnFacebook() {
                const url = window.location.href;
                window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
            },
            shareOnTwitter() {
                const url = window.location.href;
                const message = `Check out ${this.productTitle} on Zzimba Online:`;
                window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(url)}`, '_blank');
            },
            shareOnLinkedIn() {
                const url = window.location.href;
                const message = `Check out ${this.productTitle} on Zzimba Online.`;
                window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&summary=${encodeURIComponent(message)}`, '_blank');
            },
            submitReview() {
                if (!IS_LOGGED_IN) {
                    if (typeof openAuthModal === 'function') {
                        openAuthModal();
                    } else {
                        alert('Please log in to submit a review.');
                    }
                    return;
                }

                if (this.reviewRating < 1) {
                    alert('Please select a rating.');
                    return;
                }

                if (!this.reviewComment.trim() || this.reviewComment.length < 10) {
                    alert('Review must be at least 10 characters long.');
                    return;
                }

                if (this.isSubmitting) {
                    return;
                }

                this.isSubmitting = true;

                const formData = new FormData();
                formData.append('action', 'submit_review');
                formData.append('product_id', this.productId);
                formData.append('rating', this.reviewRating);
                formData.append('comment', this.reviewComment.trim());

                fetch(BASE_URL + 'fetch/manageProductReviews.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    this.isSubmitting = false;
                    console.log(data);
                    if (data.success) {
                        
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Review submitted successfully!', 'success');
                        } else if (typeof notifications !== 'undefined') {
                            notifications.success(data.message || 'Review submitted successfully!');
                        } else {
                            alert(data.message || 'Review submitted successfully!');
                        }
                        
                        // Reset form
                        this.reviewComment = '';
                        this.reviewRating = 0;
                        this.hoverRating = 0;
                        this.refreshIcons();
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        
                        let errorMessage = data.error || 'Failed to submit review';
                        
                        if (data.error && data.error.includes('Authentication required')) {
                            errorMessage = 'Please log in to submit a review.';
                            if (typeof openAuthModal === 'function') {
                                openAuthModal();
                            }
                        }
                        
                        if (typeof showToast === 'function') {
                            showToast(errorMessage, 'error');
                        } else if (typeof notifications !== 'undefined') {
                            notifications.error(errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Error submitting review:', error);
                    
                    let errorMessage = 'Network error. Please check your connection and try again.';
                    if (error.message.includes('401')) {
                        errorMessage = 'Please log in to submit a review.';
                        if (typeof openAuthModal === 'function') {
                            openAuthModal();
                        }
                    }
                    
                    if (typeof showToast === 'function') {
                        showToast(errorMessage, 'error');
                    } else if (typeof notifications !== 'undefined') {
                        notifications.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                });
            },
            async showVendorsInRegion(region) {
                const ok = await this.ensureSession({ type: 'view-suppliers', region });
                if (!ok) return;
                this.resumeViewSuppliers(region);
            },
            resumeViewSuppliers(region) {
                this.vendorRegion = region; this.vendorModalOpen = true; this.vendorModalLoading = true;
                fetch((this.BASE_URL) + 'fetch/getVendors.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ product_id: this.productId, region }) })
                    .then(r => r.json()).then(d => { this.vendorModalLoading = false; this.vendors = (d.success && Array.isArray(d.vendors)) ? d.vendors : []; this.$nextTick(() => this.refreshIcons()); })
                    .catch(() => { this.vendorModalLoading = false; this.vendors = []; });
            },
            closeVendorModal() { this.vendorModalOpen = false; },
            gotoVendor(id) { try { localStorage.setItem('zz_last_product_id', String(this.productId)); } catch (e) { } window.location.href = (this.BASE_URL) + 'view/profile/vendor/' + id; },
            checkSession() { return fetch((this.BASE_URL) + 'fetch/check-session.php', { credentials: 'include' }).then(r => r.json()).then(d => d.success ? d : { logged_in: false }).catch(() => ({ logged_in: false })); },
            async ensureSession(pending) { try { const s = await this.checkSession(); if (!s.logged_in) { if (pending) window.setPendingVendorAction(pending); if (typeof openAuthModal === 'function') openAuthModal(); else alert('Please log in to continue.'); return false; } return true; } catch (e) { return false; } },
            logProductView() {
                if (navigator.webdriver) return;
                if (document.visibilityState !== 'visible') { const onV = () => { if (document.visibilityState === 'visible') { document.removeEventListener('visibilitychange', onV); this.logProductView(); } }; document.addEventListener('visibilitychange', onV); return; }
                const sd = localStorage.getItem('session_event_log'); if (!sd) return; let s; try { s = JSON.parse(sd) } catch (e) { return } if (!s || !s.sessionID) return;
                const key = 'view_logged_product_' + this.productId + '_' + s.sessionID; if (sessionStorage.getItem(key)) return;
                const params = new URLSearchParams(); params.append('action', 'log_view'); params.append('session_id', s.sessionID); params.append('product_id', this.productId);
                const url = window.location.href;
                if (navigator.sendBeacon) { navigator.sendBeacon(url, params); sessionStorage.setItem(key, '1'); return; }
                fetch(url, { method: 'POST', body: params, credentials: 'same-origin' }).then(r => r.json()).then(d => { if (d && d.success && typeof d.unique_views === 'number') { const el = document.getElementById('view-count'); if (el) el.textContent = new Intl.NumberFormat().format(d.unique_views) + ' Views'; } }).finally(() => sessionStorage.setItem(key, '1'));
            }
        }
    }
    function showToast(message, type = 'success') { const t = document.createElement('div'); t.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity duration-300`; t.textContent = message; document.body.appendChild(t); setTimeout(() => t.classList.add('opacity-100'), 10); setTimeout(() => { t.classList.remove('opacity-100'); setTimeout(() => t.remove(), 300); }, 3000); }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>