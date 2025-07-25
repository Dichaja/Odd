<?php
require_once __DIR__ . '/config/config.php';

// Get product ID from URL parameter
$productId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($productId)) {
    header('Location: ' . BASE_URL . 'materials-yard');
    exit;
}

// Function to generate SEO meta tags for products
function generateSeoMetaTags($product)
{
    $title = htmlspecialchars($product['title'] ?? 'Product') . ' | Zzimba Online';

    // Truncated description for meta description (max 160 characters for SEO)
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

    // Full description for og_description (no truncation)
    $ogDescription = '';
    if (!empty($product['description'])) {
        $ogDescription = htmlspecialchars(strip_tags($product['description']));
    } else {
        $ogDescription = 'Discover quality ' . ($product['title'] ?? 'products') . ' on Zzimba Online. Your trusted marketplace for construction materials and more.';
    }

    // Determine OG image with fallback mechanism
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

// Function to get product images
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
            // Scan directory for images if no JSON file
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

    // If no images found, use placeholder
    if (empty($images)) {
        $images[] = "https://placehold.co/800x600/e2e8f0/1e293b?text=" . urlencode("Product Image");
    }

    return $images;
}

// Function to get short description (2 lines max)
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

// Fetch product details
$stmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.title, 
        p.description, 
        p.views, 
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
        ) AS min_price
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

// Update view count
$updateViews = $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
$updateViews->execute([$productId]);
$product['views'] = $product['views'] + 1;

// Get product images
$productImages = getProductImages($productId);

// Add primary image to product array for SEO
$product['primary_image'] = $productImages[0];

// Generate SEO meta tags
$seoTags = generateSeoMetaTags($product);
$pageTitle = $seoTags['title'];

// Get short description
$shortDescription = getShortDescription($product['description']);

// Fetch 4 random products from same category (excluding current product)
$relatedStmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.title, 
        p.description, 
        p.views, 
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
        ) AS has_pricing
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
    $relatedProducts[] = $row;
}

// Set active navigation
$activeNav = "materials";

// Define dummy reviews for future expansion
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

// Define dummy store outlets for future expansion
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
        background-color: rgba(220, 38, 38, 0.1);
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

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
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
</style>

<!-- Hero Banner with Share Buttons and Gradient Overlay -->
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

            <!-- Share buttons in hero section -->
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

<!-- Main Product Section -->
<div class="container mx-auto px-4 -mt-10 lg:-mt-20 relative z-30">
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Product Gallery -->
            <div class="space-y-6">
                <div class="relative rounded-2xl overflow-hidden bg-white shadow-lg">
                    <span
                        class="absolute top-4 right-4 bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded-full z-10">
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
                                <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 <?= $index === 0 ? 'border-rose-600' : 'border-transparent hover:border-rose-600' ?> transition-colors flex-shrink-0"
                                    data-image="<?= $image ?>">
                                    <img src="<?= $image ?>" alt="Product view <?= $index + 1 ?>"
                                        class="w-full h-full object-cover">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <div class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                    <span class="font-medium text-gray-500">Category:</span>
                    <a href="<?= BASE_URL ?>view/category/<?= $product['category_id'] ?>"
                        class="font-semibold text-rose-600 hover:underline ml-1"><?= htmlspecialchars($product['category_name']) ?></a>
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
                        <i class="fas fa-eye mr-1"></i>
                        <span><?= number_format($product['views']) ?> Views</span>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <!-- Short description (2 lines max) -->
                    <p class="text-gray-700 leading-relaxed mb-6 line-clamp-2">
                        <?= htmlspecialchars($shortDescription) ?>
                    </p>

                    <!-- Brand placeholder for future expansion -->
                    <div class="flex items-center mb-6">
                        <span class="text-sm font-medium text-gray-500 mr-2">Brand:</span>
                        <span class="text-sm font-semibold text-gray-800">GENERIC Construction Materials</span>
                    </div>

                    <?php if ($product['has_pricing'] && $product['min_price']): ?>
                        <div class="text-3xl text-rose-600 font-bold mb-6">
                            UGX <?= number_format($product['min_price']) ?> <span
                                class="text-base font-normal ml-2 text-gray-500">Starting Price</span>
                        </div>
                    <?php else: ?>
                        <div class="text-lg text-gray-600 font-medium mb-6">
                            Contact for pricing
                        </div>
                    <?php endif; ?>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-fit">
                            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors"
                                id="decrease-quantity">
                                <i class="fas fa-minus text-sm"></i>
                            </button>
                            <input type="number" id="quantity"
                                class="quantity-input w-12 text-center border-none focus:ring-0" value="1" min="1">
                            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors"
                                id="increase-quantity">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <?php if ($product['has_pricing']): ?>
                            <button
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1 md:flex-none">
                                <i class="fas fa-shopping-cart mr-2"></i> Buy
                            </button>
                        <?php endif; ?>
                        <button
                            class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1 md:flex-none">
                            <i class="fas fa-tag mr-2"></i> Sell
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Tabs Section -->
<div class="container mx-auto px-4 py-8">
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            <button class="border-rose-600 text-rose-600 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="description-tab">
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

    <!-- Description Tab -->
    <div id="description-tab" class="tab-content block">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Product Description</h3>
            <div class="text-gray-700 leading-relaxed space-y-4">
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Reviews Tab -->
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
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
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                placeholder="Share your experience with this product..." required></textarea>
                            <div class="text-right text-xs text-gray-500 mt-1">
                                <span id="char-count">200</span> characters left
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center">
                            <i class="far fa-paper-plane mr-2"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Tab -->
    <div id="store-tab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Available Store Outlets</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($storeOutlets as $outlet): ?>
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow bg-white">
                        <h4 class="font-semibold text-lg mb-3 text-gray-800"><?= htmlspecialchars($outlet['name']) ?></h4>
                        <div class="space-y-3 text-gray-600">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['address']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone-alt mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['phone']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-clock mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['hours']) ?></span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button
                                class="text-rose-600 hover:text-rose-700 font-medium flex items-center transition-colors">
                                <i class="fas fa-directions mr-2"></i> Get Directions
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- You May Also Like Section -->
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
                                    class="bg-white text-gray-800 px-3 md:px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm">
                                    View Details
                                </a>
                            </div>
                        </div>

                        <div class="p-3 md:p-5 flex flex-col justify-between flex-1">
                            <div>
                                <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                                    <?= htmlspecialchars($relatedProduct['title']) ?>
                                </h3>

                                <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                                    <?= htmlspecialchars(getShortDescription($relatedProduct['description'], 100)) ?>
                                </p>

                                <div class="flex items-center text-gray-500 text-xs md:text-sm mb-4">
                                    <i class="fas fa-eye mr-1"></i>
                                    <span><?= number_format($relatedProduct['views']) ?> views</span>
                                </div>
                            </div>

                            <div class="flex space-x-2 mt-auto">
                                <?php if ($relatedProduct['has_pricing']): ?>
                                    <a href="<?= BASE_URL ?>view/product/<?= $relatedProduct['id'] ?>?action=buy"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                        <i class="fas fa-shopping-cart mr-1"></i> Buy
                                    </a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>view/product/<?= $relatedProduct['id'] ?>?action=sell"
                                    class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                    <i class="fas fa-tag mr-1"></i> Sell
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tab switching functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                // Remove active state from all buttons
                tabButtons.forEach(b => {
                    b.classList.remove('text-rose-600', 'border-rose-600');
                    b.classList.add('border-transparent', 'text-gray-500');
                });

                // Set active state for clicked button
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('text-rose-600', 'border-rose-600');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('block');
                });

                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab');
                const targetContent = document.getElementById(tabId);
                targetContent.classList.remove('hidden');
                targetContent.classList.add('block');
            });
        });

        // Gallery functionality
        const galleryThumbs = document.querySelectorAll('.gallery-thumb');
        const mainImage = document.getElementById('main-product-image');

        galleryThumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                const imageUrl = this.getAttribute('data-image');
                mainImage.src = imageUrl;

                // Update active state
                galleryThumbs.forEach(t => {
                    t.classList.remove('border-rose-600');
                    t.classList.add('border-transparent');
                });
                this.classList.add('border-rose-600');
                this.classList.remove('border-transparent');
            });
        });

        // Quantity controls
        const quantityInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-quantity');
        const increaseBtn = document.getElementById('increase-quantity');

        decreaseBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        increaseBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            quantityInput.value = currentValue + 1;
        });

        // Review functionality
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

        // Character counter for review
        const reviewComment = document.getElementById('reviewComment');
        const charCount = document.getElementById('char-count');

        reviewComment.addEventListener('input', () => {
            const remaining = 200 - reviewComment.value.length;
            charCount.textContent = remaining;
        });

        // Review form submission
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

            // Reset form
            this.reset();
            document.getElementById('reviewRating').value = 0;
            document.getElementById('char-count').textContent = '200';
            starElements.forEach(s => {
                s.classList.remove('fas', 'text-amber-400');
                s.classList.add('far');
            });
        });
    });

    // Social sharing functions
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
        const message = `Check out ${productName} on Zzimba Online: ${currentUrl}`;
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