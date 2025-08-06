<?php
require_once __DIR__ . '/config/config.php';

$productId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($productId)) {
    header('Location: ' . BASE_URL . 'materials-yard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'log_view') {
    header('Content-Type: application/json');

    $sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : '';
    $productId = isset($_POST['product_id']) ? $_POST['product_id'] : '';

    if (empty($sessionId) || empty($productId)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    try {
        $viewId = generateUlid();
        $timezone = new DateTimeZone('Africa/Kampala');
        $createdAt = new DateTime('now', $timezone);

        $insertStmt = $pdo->prepare("INSERT INTO product_views (id, product_id, session_id, created_at) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$viewId, $productId, $sessionId, $createdAt->format('Y-m-d H:i:s')]);

        $countStmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) as unique_views FROM product_views WHERE product_id = ?");
        $countStmt->execute([$productId]);
        $viewCount = $countStmt->fetch()['unique_views'];

        echo json_encode(['success' => true, 'unique_views' => $viewCount]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

function generateSeoMetaTags($product)
{
    $title = htmlspecialchars($product['title'] ?? 'Product') . ' | Zzimba Online';

    $metaDescription = '';
    if (!empty($product['description'])) {
        $description = strip_tags($product['description']);
        if (strlen($description) > 160) {
            $metaDescription = substr($description, 0, 157) . '...';
        } else {
            $metaDescription = $description;
        }
    } else {
        $metaDescription = 'Discover quality ' . ($product['title'] ?? 'products') . ' on Zzimba Online. Your trusted marketplace for construction materials and more.';
    }
    $metaDescription = htmlspecialchars($metaDescription);

    $ogDescription = '';
    if (!empty($product['description'])) {
        $ogDescription = htmlspecialchars(strip_tags($product['description']));
    } else {
        $ogDescription = 'Discover quality ' . ($product['title'] ?? 'products') . ' on Zzimba Online. Your trusted marketplace for construction materials and more.';
    }

    $ogImage = '';
    if (!empty($product['primary_image']) && !strpos($product['primary_image'], 'placehold.co')) {
        $ogImage = $product['primary_image'];
    } else {
        $productName = urlencode($product['title'] ?? 'Product');
        $ogImage = "https://placehold.co/1200x630/e2e8f0/1e293b?text={$productName}";
    }

    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    return [
        'title' => $title,
        'description' => $metaDescription,
        'og_title' => $title,
        'og_description' => $ogDescription,
        'og_image' => $ogImage,
        'og_url' => $currentUrl,
        'og_type' => 'product'
    ];
}

function getProductImages($productId)
{
    $productDir = __DIR__ . '/img/products/' . $productId . '/';
    $images = [];

    if (is_dir($productDir)) {
        $jsonFile = $productDir . 'images.json';

        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $imageData = json_decode($jsonContent, true);

            if (isset($imageData['images']) && !empty($imageData['images'])) {
                foreach ($imageData['images'] as $imageName) {
                    $imagePath = $productDir . $imageName;
                    if (file_exists($imagePath)) {
                        $images[] = BASE_URL . 'img/products/' . $productId . '/' . $imageName;
                    }
                }
            }
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $files = scandir($productDir);

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($extension, $allowedExtensions)) {
                        $images[] = BASE_URL . 'img/products/' . $productId . '/' . $file;
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

    $shortened = substr($description, 0, $maxLength);
    $lastSpace = strrpos($shortened, ' ');

    if ($lastSpace !== false) {
        $shortened = substr($shortened, 0, $lastSpace);
    }

    return $shortened . '...';
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
        SELECT 
            vs.region,
            vs.district,
            COUNT(DISTINCT vs.id) as vendor_count
        FROM vendor_stores vs
        INNER JOIN store_categories sc ON vs.id = sc.store_id
        INNER JOIN store_products sp ON sc.id = sp.store_category_id
        INNER JOIN product_pricing pp ON sp.id = pp.store_products_id
        WHERE sp.product_id = ? 
          AND vs.status = 'active'
          AND sc.status = 'active'
          AND sp.status = 'active'
        GROUP BY vs.region, vs.district
        ORDER BY vs.region, vendor_count DESC
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
        ) AS min_price,
        (SELECT COUNT(DISTINCT session_id) 
         FROM product_views 
         WHERE product_id = p.id) AS unique_views
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.id
    WHERE p.id = ? AND p.status = 'published'
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
        ) AS lowest_price,
        (SELECT COUNT(DISTINCT session_id) 
         FROM product_views 
         WHERE product_id = p.id) AS unique_views
    FROM products p
    WHERE p.category_id = ? 
      AND p.id != ? 
      AND p.status = 'published'
    ORDER BY RAND()
    LIMIT 4
");

$relatedStmt->execute([$product['category_id'], $productId]);
$relatedProducts = [];

while ($row = $relatedStmt->fetch()) {
    $relatedProductImages = getProductImages($row['id']);
    $row['primary_image'] = $relatedProductImages[0];
    $row['has_pricing'] = (bool) $row['has_pricing'];
    $row['lowest_price'] = $row['lowest_price'] ? (float) $row['lowest_price'] : null;
    $relatedProducts[] = $row;
}

$activeNav = "materials";

$reviews = [
    [
        'name' => 'John Doe',
        'rating' => 4,
        'comment' => 'Great product, exactly what I needed for my construction project! The quality is consistent and it performs well.',
        'date' => '2025-01-15',
        'verified' => true
    ],
    [
        'name' => 'Jane Smith',
        'rating' => 5,
        'comment' => 'Excellent quality and quick delivery! This product has saved me a lot of trouble. Highly recommended.',
        'date' => '2025-02-01',
        'verified' => true
    ]
];

$storeOutlets = [
    [
        'name' => 'Zzimba Online Warehouse (Main Branch)',
        'address' => 'Plot 123, Industrial Area, Kampala',
        'phone' => '+256 700 123456',
        'hours' => 'Mon-Sat: 8:00 AM - 6:00 PM'
    ],
    [
        'name' => 'Kampala Hardware Market',
        'address' => '45 Hardware Avenue, Central Business District',
        'phone' => '+256 700 789012',
        'hours' => 'Mon-Fri: 8:30 AM - 5:30 PM, Sat: 9:00 AM - 3:00 PM'
    ],
    [
        'name' => 'Entebbe Road Outlet',
        'address' => 'Entebbe Road, Next to Fuel Station',
        'phone' => '+256 700 345678',
        'hours' => 'Mon-Sun: 8:00 AM - 7:00 PM'
    ]
];

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

    .share-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .share-label {
        font-size: 12px;
        font-weight: 500;
        color: #ffffff;
    }

    .share-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
        color: #ffffff;
        border: 1.5px solid#ffffff;
        background-color: transparent;
        transition: all 0.2s ease;
        position: relative;
    }

    .share-button .fa-solid,
    .share-button .fa-brands {
        font-size: 10px !important;
    }

    .share-button:hover {
        background-color: rgba(217, 43, 19, 0.1);
        transform: translateY(-2px);
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

    .product-card {
        position: relative;
        border: 1px solid #E5E7EB;
        border-radius: 0.75rem;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .product-details-btn {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-details-btn {
        opacity: 1;
    }

    .gallery-container {
        position: relative;
        overflow: hidden;
    }

    .gallery-scroll {
        display: flex;
        transition: transform 0.5s ease;
    }

    .gallery-scroll img {
        flex-shrink: 0;
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

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
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

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border: none;
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .modal-header {
        background: linear-gradient(135deg, #D92B13 0%, #B91C1C 100%);
        color: white;
        padding: 20px 24px;
        border-bottom: none;
    }

    .modal-body {
        padding: 24px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .close {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .close:hover,
    .close:focus {
        opacity: 1;
    }

    .region-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .region-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(217, 43, 19, 0.1), transparent);
        transition: left 0.5s;
    }

    .region-card:hover::before {
        left: 100%;
    }

    .region-card:hover {
        border-color: #D92B13;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(217, 43, 19, 0.2);
    }

    .vendor-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .vendor-card:hover {
        border-color: #D92B13;
        box-shadow: 0 4px 12px -2px rgba(217, 43, 19, 0.1);
        transform: translateY(-1px);
    }

    .vendor-logo {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        object-fit: cover;
        background-color: #f3f4f6;
        flex-shrink: 0;
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #D92B13;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }

        .vendor-card {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .vendor-logo {
            width: 48px;
            height: 48px;
        }
    }
</style>

<div class="relative h-50 md:h-64 w-full bg-gray-100 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-800/70 to-gray-900/90 z-10"></div>
    <img src="<?= $productImages[0] ?>" alt="<?= htmlspecialchars($product['title']) ?> Banner"
        class="w-full h-full object-cover">
    <div class="container mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 pb-10 md:pt-12 z-20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white mb-4">
                    <?= htmlspecialchars($product['title']) ?>
                </h1>
                <nav class="flex text-xs md:text-sm text-gray-300 overflow-hidden whitespace-nowrap">
                    <a href="<?= BASE_URL ?>" class="hover:text-white transition-colors truncate max-w-[30%]">Zzimba
                        Online</a>
                    <span class="mx-2">/</span>
                    <a href="<?= BASE_URL ?>materials-yard"
                        class="hover:text-white transition-colors truncate max-w-[30%]">Materials Yard</a>
                    <span class="mx-2">/</span>
                    <span
                        class="text-white font-medium truncate max-w-[40%]"><?= htmlspecialchars($product['title']) ?></span>
                </nav>
            </div>

            <div class="share-container mt-4 md:mt-0 hidden md:flex">
                <span class="share-label">SHARE</span>
                <div class="share-buttons">
                    <button onclick="copyLink()" class="share-button">
                        <i class="fa-solid fa-link"></i>
                        <span class="tooltip">Copy link to clipboard</span>
                    </button>
                    <button onclick="shareOnWhatsApp()" class="share-button">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span class="tooltip">Share on WhatsApp</span>
                    </button>
                    <button onclick="shareOnFacebook()" class="share-button">
                        <i class="fa-brands fa-facebook-f"></i>
                        <span class="tooltip">Share on Facebook</span>
                    </button>
                    <button onclick="shareOnTwitter()" class="share-button">
                        <i class="fa-brands fa-x-twitter"></i>
                        <span class="tooltip">Post on X</span>
                    </button>
                    <button onclick="shareOnLinkedIn()" class="share-button">
                        <i class="fa-brands fa-linkedin-in"></i>
                        <span class="tooltip">Share on LinkedIn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 -mt-10 lg:-mt-20 relative z-30">
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div class="space-y-6">
                <div class="relative rounded-2xl overflow-hidden bg-white shadow-lg">
                    <span class="absolute top-4 right-4 text-white text-xs font-bold px-3 py-1 rounded-full z-10"
                        style="background-color: #D92B13;">
                        <?= $product['featured'] ? 'FEATURED' : 'POPULAR' ?>
                    </span>
                    <img id="main-product-image" src="<?= $productImages[0] ?>"
                        alt="<?= htmlspecialchars($product['title']) ?>"
                        class="w-full h-auto object-cover rounded-2xl" />
                </div>

                <?php if (count($productImages) > 1): ?>
                    <div class="gallery-container">
                        <div class="gallery-scroll flex gap-2" id="gallery-scroll">
                            <?php foreach ($productImages as $index => $image): ?>
                                <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 <?= $index === 0 ? 'border-[#D92B13]' : 'border-transparent hover:border-[#D92B13]' ?> transition-colors flex-shrink-0"
                                    data-image="<?= $image ?>">
                                    <img src="<?= $image ?>" alt="Product view <?= $index + 1 ?>"
                                        class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-6">
                <div class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                    <span class="font-medium text-gray-500">Category:</span>
                    <a href="<?= BASE_URL ?>view/category/<?= $product['category_id'] ?>"
                        class="font-semibold hover:underline ml-1"
                        style="color: #D92B13;"><?= htmlspecialchars($product['category_name']) ?></a>
                </div>

                <h2 class="text-3xl font-bold text-gray-900">
                    <?= htmlspecialchars($product['title']) ?>
                </h2>

                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center">
                        <div class="flex text-amber-400 mr-1">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="text-gray-600">(<?= count($reviews) ?> Reviews)</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-eye mr-1" style="color: #D92B13;"></i>
                        <span id="view-count"><?= number_format($product['unique_views']) ?> Views</span>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <p class="text-gray-700 leading-relaxed mb-6 line-clamp-2">
                        <?= htmlspecialchars($shortDescription) ?>
                    </p>

                    <div class="flex items-center mb-6">
                        <span class="text-sm font-medium text-gray-500 mr-2">Brand:</span>
                        <span class="text-sm font-semibold text-gray-800">GENERIC Construction Materials</span>
                    </div>

                    <?php if ($product['has_pricing'] && $product['min_price']): ?>
                        <div class="text-3xl font-bold mb-6" style="color: #D92B13;">
                            <span class="block text-sm font-medium text-gray-500 mb-1">Starting Price:</span>
                            <?= formatPrice($product['min_price']) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-lg text-gray-600 font-medium mb-6">
                            Contact for pricing
                        </div>
                    <?php endif; ?>

                    <div class="flex gap-2">
                        <?php if ($product['has_pricing']): ?>
                            <button
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1">
                                <i class="fas fa-shopping-cart mr-2"></i> Buy
                            </button>
                        <?php endif; ?>
                        <button onclick="openVendorSellModal(
                                '<?= $product['id'] ?>',
                                '<?= htmlspecialchars($product['title'], ENT_QUOTES) ?>'
                            )"
                            class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1">
                            <i class="fas fa-tag mr-2"></i> Sell
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            <button class="font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                style="border-color: #D92B13; color: #D92B13;" data-tab="description-tab">
                <i class="fa-solid fa-circle-info mr-2"></i> Description
            </button>
            <button
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="store-tab">
                <i class="fa-solid fa-store mr-2"></i> Find Supplier
            </button>
            <button
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="reviews-tab">
                <i class="fa-solid fa-star mr-2"></i> Reviews (<?= count($reviews) ?>)
            </button>
        </nav>
    </div>

    <div id="description-tab" class="tab-content block">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Product Description</h3>
            <div class="text-gray-700 leading-relaxed space-y-4">
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
        </div>
    </div>

    <div id="store-tab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">
                <i class="fas fa-map-marker-alt mr-2" style="color: #D92B13;"></i>
                Supplier Regions
            </h3>

            <?php if (!empty($supplierRegions)): ?>
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">
                        This product is available from suppliers in <strong><?= count($supplierRegions) ?></strong>
                        region(s).
                        Click on a region to view available suppliers.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($supplierRegions as $region): ?>
                        <div class="region-card" onclick="showVendorsInRegion('<?= htmlspecialchars($region['region']) ?>')">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-lg text-gray-800">
                                    <?= htmlspecialchars($region['region']) ?>
                                </h4>
                                <span class="bg-[#D92B13] text-white text-sm font-bold px-3 py-1 rounded-full">
                                    <?= $region['vendor_count'] ?>         <?= $region['vendor_count'] == 1 ? 'Vendor' : 'Vendors' ?>
                                </span>
                            </div>

                            <div class="text-sm text-gray-600 mb-3">
                                <i class="fas fa-map-pin mr-1"></i>
                                Districts: <?= htmlspecialchars($region['district']) ?>
                            </div>

                            <div class="flex items-center justify-end">
                                <div class="text-[#D92B13] font-medium text-sm">
                                    View Suppliers <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-6">
                    <div class="mb-4">
                        <i class="fas fa-store text-6xl text-gray-300"></i>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-600 mb-2">No Suppliers Found</h4>
                    <a href="<?php echo BASE_URL; ?>request-for-quote" class="inline-block">
                        <button class="bg-[#D92B13] hover:bg-[#B91C1C] text-white px-6 py-3 rounded-lg transition-colors">
                            <i class="fas fa-envelope mr-2"></i> Request a Quote
                        </button>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="reviews-tab" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Customer Reviews</h3>
                        <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= count($reviews) ?> Reviews
                        </span>
                    </div>

                    <div id="reviews-list" class="mb-6 max-h-[500px] overflow-y-auto pr-2 space-y-6">
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-b border-gray-200 pb-6 mb-6 last:border-0 last:pb-0 last:mb-0 fade-in">
                                <div class="flex items-center mb-1">
                                    <span
                                        class="font-semibold text-gray-800"><?= htmlspecialchars($review['name']) ?></span>
                                    <?php if ($review['verified']): ?>
                                        <span
                                            class="ml-2 bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-0.5 rounded-full">Verified
                                            Purchase</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-gray-500 text-sm mb-2"><?= $review['date'] ?></div>
                                <div class="flex text-amber-400 mb-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-gray-700"><?= htmlspecialchars($review['comment']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8 sticky top-4">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">Write a Review</h4>
                    <form id="review-form" class="space-y-4">
                        <div>
                            <label for="reviewerName" class="block text-sm font-medium text-gray-700 mb-1">Your
                                Name</label>
                            <input type="text" id="reviewerName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D92B13] focus:border-transparent"
                                placeholder="Enter your name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating</label>
                            <div id="review-stars" class="flex text-gray-400">
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="1"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="2"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="3"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="4"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="5"></i>
                            </div>
                            <input type="hidden" id="reviewRating" value="0">
                        </div>
                        <div>
                            <label for="reviewComment" class="block text-sm font-medium text-gray-700 mb-1">Your
                                Review</label>
                            <textarea id="reviewComment" rows="4" maxlength="200"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#D92B13] focus:border-transparent"
                                placeholder="Share your experience with this product..." required></textarea>
                            <div class="text-right text-xs text-gray-500 mt-1">
                                <span id="char-count">200</span> characters left
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center bg-[#D92B13] hover:bg-[#B91C1C]">
                            <i class="far fa-paper-plane mr-2"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="vendorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close" onclick="closeVendorModal()">&times;</span>
            <h2 id="modalTitle" class="text-xl font-bold">
                <i class="fas fa-store mr-2"></i>
                Suppliers in <span id="modalRegionName"></span>
            </h2>
        </div>
        <div class="modal-body">
            <div id="modalLoading" class="text-center py-8">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Loading suppliers...</p>
            </div>
            <div id="modalContent" class="hidden">
            </div>
        </div>
    </div>
</div>

<?php if (!empty($relatedProducts)): ?>
    <div class="bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-800">You May Also Like</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div
                        class="product-card transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                        <div class="relative">
                            <img src="<?= $relatedProduct['primary_image'] ?>"
                                alt="<?= htmlspecialchars($relatedProduct['title']) ?>"
                                class="w-full h-40 md:h-48 object-cover">

                            <div class="product-details-btn">
                                <a href="<?= BASE_URL ?>view/product/<?= $relatedProduct['id'] ?>"
                                    class="bg-white text-gray-800 px-3 md:px-4 py-2 rounded-lg font-medium hover:text-white hover:bg-[#D92B13] transition-colors text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                        <div class="p-3 md:p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                                <?= htmlspecialchars($relatedProduct['title']) ?>
                            </h3>
                            <div class="flex-1 flex flex-col justify-end">
                                <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                                    <?= htmlspecialchars(getShortDescription($relatedProduct['description'], 100)) ?>
                                </p>
                                <div class="flex items-center text-gray-500 text-xs md:text-sm mb-3">
                                    <i class="fas fa-eye mr-1 text-[#D92B13]"></i>
                                    <span><?= number_format($relatedProduct['unique_views']) ?> views</span>
                                </div>
                                <?php if ($relatedProduct['has_pricing'] && $relatedProduct['lowest_price']): ?>
                                    <div class="text-center mb-3">
                                        <span class="price-text font-bold text-[#D92B13]">
                                            <?= formatPrice($relatedProduct['lowest_price']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex gap-2">
                                    <?php if ($relatedProduct['has_pricing']): ?>
                                        <a href="<?= BASE_URL ?>view/product/<?= $relatedProduct['id'] ?>?action=buy"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                            <i class="fas fa-shopping-cart mr-1"></i> Buy
                                        </a>
                                    <?php endif; ?>
                                    <button onclick="openVendorSellModal(
                                            '<?= $relatedProduct['id'] ?>',
                                            '<?= htmlspecialchars($relatedProduct['title'], ENT_QUOTES) ?>'
                                        )" class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                        <i class="fas fa-tag mr-1"></i> Sell
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>

    function checkSession() {
        return fetch(`${BASE_URL}fetch/check-session.php`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    IS_LOGGED_IN = data.logged_in || false;
                    return data;
                }
                return { logged_in: false };
            })
            .catch(() => ({ logged_in: false }));
    }

    document.addEventListener('DOMContentLoaded', function () {
        logProductView();

        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                tabButtons.forEach(b => {
                    b.classList.remove('text-[#D92B13]', 'border-[#D92B13]');
                    b.classList.add('border-transparent', 'text-gray-500');
                    b.style.color = '';
                    b.style.borderColor = '';
                });

                this.classList.remove('border-transparent', 'text-gray-500');
                this.style.color = '#D92B13';
                this.style.borderColor = '#D92B13';

                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('block');
                });

                const tabId = this.getAttribute('data-tab');
                const targetContent = document.getElementById(tabId);
                targetContent.classList.remove('hidden');
                targetContent.classList.add('block');
            });
        });

        const galleryThumbs = document.querySelectorAll('.gallery-thumb');
        const mainImage = document.getElementById('main-product-image');

        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                const imageUrl = this.getAttribute('data-image');
                mainImage.src = imageUrl;

                galleryThumbs.forEach(t => {
                    t.classList.remove('border-[#D92B13]');
                    t.classList.add('border-transparent');
                });
                this.classList.add('border-[#D92B13]');
                this.classList.remove('border-transparent');
            });
        });

        const starElements = document.querySelectorAll('#review-stars i');
        const ratingInput = document.getElementById('reviewRating');

        starElements.forEach(star => {
            star.addEventListener('mouseover', () => {
                const val = parseInt(star.getAttribute('data-value'));
                starElements.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-value'));
                    if (sVal <= val) {
                        s.classList.add('text-amber-400');
                    }
                });
            });

            star.addEventListener('mouseout', () => {
                if (ratingInput.value === '0') {
                    starElements.forEach(s => s.classList.remove('text-amber-400'));
                } else {
                    starElements.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-value'));
                        if (sVal > parseInt(ratingInput.value)) {
                            s.classList.remove('text-amber-400');
                        }
                    });
                }
            });

            star.addEventListener('click', () => {
                const val = parseInt(star.getAttribute('data-value'));
                ratingInput.value = val;
                starElements.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-value'));
                    s.classList.remove('fas', 'far', 'text-amber-400');
                    if (sVal <= val) {
                        s.classList.add('fas', 'text-amber-400');
                    } else {
                        s.classList.add('far');
                    }
                });
            });
        });

        const reviewComment = document.getElementById('reviewComment');
        const charCount = document.getElementById('char-count');

        reviewComment.addEventListener('input', () => {
            const remaining = 200 - reviewComment.value.length;
            charCount.textContent = remaining;
        });

        document.getElementById('review-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const name = document.getElementById('reviewerName').value.trim();
            const rating = parseInt(document.getElementById('reviewRating').value);
            const comment = document.getElementById('reviewComment').value.trim();

            if (!name || !comment || rating < 1) {
                alert('Please fill all fields and select a rating.');
                return;
            }

            alert('Thank you for your review! (This is a demo - review not actually saved)');

            this.reset();
            document.getElementById('reviewRating').value = 0;
            document.getElementById('char-count').textContent = '200';
            starElements.forEach(s => {
                s.classList.remove('fas', 'text-amber-400');
                s.classList.add('far');
            });
        });
    });

    function logProductView() {
        const sessionData = localStorage.getItem('session_event_log');
        if (!sessionData) {
            console.warn('No session data found in localStorage');
            return;
        }

        let session;
        try {
            session = JSON.parse(sessionData);
        } catch (e) {
            console.error('Failed to parse session data:', e);
            return;
        }

        if (!session.sessionID) {
            console.warn('No session ID found in session data');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'log_view');
        formData.append('session_id', session.sessionID);
        formData.append('product_id', '<?= $productId ?>');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const viewCountElement = document.getElementById('view-count');
                    if (viewCountElement && data.unique_views) {
                        viewCountElement.textContent = new Intl.NumberFormat().format(data.unique_views) + ' Views';
                    }
                } else {
                    console.error('Failed to log product view:', data.message);
                }
            })
            .catch(error => {
                console.error('Error logging product view:', error);
            });
    }

    function showVendorsInRegion(region) {
        checkSession().then(sessionData => {
            if (!sessionData.logged_in) {
                if (typeof openAuthModal === 'function') {
                    openAuthModal();
                } else {
                    alert('Please log in to view suppliers.');
                }
                return;
            }

            const modal = document.getElementById('vendorModal');
            const modalRegionName = document.getElementById('modalRegionName');
            const modalLoading = document.getElementById('modalLoading');
            const modalContent = document.getElementById('modalContent');

            modalRegionName.textContent = region;
            modal.style.display = 'block';
            modalLoading.classList.remove('hidden');
            modalContent.classList.add('hidden');

            fetch('<?= BASE_URL ?>fetch/getVendors.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: '<?= $productId ?>',
                    region: region
                })
            })
                .then(response => response.json())
                .then(data => {
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');

                    if (data.success && data.vendors.length > 0) {
                        displayVendors(data.vendors);
                    } else {
                        modalContent.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-store text-4xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-600 mb-2">No Suppliers Found</h4>
                            <p class="text-gray-500">No suppliers found in ${region} for this product.</p>
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalLoading.classList.add('hidden');
                    modalContent.classList.remove('hidden');
                    modalContent.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                        <h4 class="text-lg font-semibold text-red-600 mb-2">Error Loading Suppliers</h4>
                        <p class="text-gray-500">Please try again later.</p>
                    </div>
                `;
                });
        });
    }

    function displayVendors(vendors) {
        const modalContent = document.getElementById('modalContent');
        let vendorsHtml = `
        <div class="mb-4">
            <p class="text-gray-600">
                Found <strong>${vendors.length}</strong> supplier${vendors.length !== 1 ? 's' : ''} in this region:
            </p>
        </div>
    `;

        vendors.forEach(vendor => {
            const logoUrl = vendor.logo_url
                ? `<?= BASE_URL ?>${vendor.logo_url}`
                : `https://placehold.co/56x56/e2e8f0/1e293b?text=${encodeURIComponent(vendor.name.charAt(0))}`;

            // Use vendor.id here
            vendorsHtml += `
            <div class="vendor-card cursor-pointer" 
                 onclick="window.location.href='<?= BASE_URL ?>view/profile/vendor/${vendor.id}'">
                <img src="${logoUrl}" alt="${vendor.name}" class="vendor-logo" 
                     onerror="this.src='https://placehold.co/56x56/e2e8f0/1e293b?text=${encodeURIComponent(vendor.name.charAt(0))}'">
                
                <div class="flex-1">
                    <h4 class="font-bold text-lg text-gray-800 mb-1">${vendor.name}</h4>
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1 text-[#D92B13]"></i>
                        ${vendor.district}
                    </p>
                </div>
            </div>
        `;
        });

        modalContent.innerHTML = vendorsHtml;
    }

    function closeVendorModal() {
        document.getElementById('vendorModal').style.display = 'none';
    }

    window.onclick = function (event) {
        const modal = document.getElementById('vendorModal');
        if (event.target === modal) {
            closeVendorModal();
        }
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
        const productName = "<?= addslashes($product['title']) ?>";
        const message = `Check out *${productName}* on Zzimba Online:\n\n${currentUrl}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const productName = "<?= addslashes($product['title']) ?>";
        const message = `Check out ${productName} on Zzimba Online:`;
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const productName = "<?= addslashes($product['title']) ?>";
        const message = `Check out ${productName} on Zzimba Online.`;
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(productName)}&summary=${encodeURIComponent(message)}`, '_blank');
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