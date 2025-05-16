<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = $pageTitle ?? 'Zzimba Online Uganda';
$activeNav = $activeNav ?? 'home';

// Load homepage data from JSON file
function loadHomepageData()
{
    $filePath = __DIR__ . '/page-data/homepage/index.json';

    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?: [];
    }

    return [];
}

// Function to fetch featured products from database
function getFeaturedProducts($pdo, $limit = 8)
{
    try {
        $stmt = $pdo->prepare(
            "SELECT p.id, p.title, p.description, p.category_id, c.name AS category_name
             FROM products p
             LEFT JOIN product_categories c ON p.category_id = c.id
             WHERE p.featured = 1 AND p.status = 'published'
             ORDER BY p.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add image URLs to each product
        foreach ($products as &$product) {
            $product['images'] = getProductImages($product['id']);
        }

        return $products;
    } catch (Exception $e) {
        error_log("Error fetching featured products: " . $e->getMessage());
        return [];
    }
}

// Function to fetch categories from database based on the provided schema
function getCategories($pdo, $limit = 8)
{
    try {
        $stmt = $pdo->prepare(
            "SELECT id, name, description, meta_title, meta_description, meta_keywords, status
             FROM product_categories 
             WHERE status = 'active' 
             ORDER BY name ASC
             LIMIT :limit"
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add image URLs to each category
        foreach ($categories as &$category) {
            $category['image'] = getCategoryImage($category['id']);
        }

        return $categories;
    } catch (Exception $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return [];
    }
}

// Function to get product images
function getProductImages($uuid)
{
    $dir = __DIR__ . '/img/products/' . $uuid;
    if (!is_dir($dir)) {
        return ['https://placehold.co/600x400?text=No+Image'];
    }

    $json = $dir . '/images.json';
    if (!file_exists($json)) {
        return ['https://placehold.co/600x400?text=No+Image'];
    }

    $data = json_decode(file_get_contents($json), true);
    if (empty($data['images'])) {
        return ['https://placehold.co/600x400?text=No+Image'];
    }

    $out = [];
    foreach ($data['images'] as $f) {
        if (filter_var($f, FILTER_VALIDATE_URL)) {
            $out[] = $f;
        } else {
            $out[] = BASE_URL . "img/products/$uuid/$f";
        }
    }

    return $out;
}

// Function to get category image
function getCategoryImage($uuid)
{
    $dir = __DIR__ . '/img/categories/' . $uuid;

    // Check if directory exists and has images
    if (is_dir($dir)) {
        $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return BASE_URL . 'img/categories/' . $uuid . '/' . basename($files[0]);
        }
    }

    // Return placeholder if no image found
    return 'https://placehold.co/800x450?text=Category';
}

// Load data from JSON
$homepageData = loadHomepageData();

// Extract data from the loaded JSON
$heroSlides = $homepageData['heroSlides'] ?? [];
$requestQuoteSection = $homepageData['requestQuoteSection'] ?? [];
$keyFeatures = $homepageData['keyFeatures'] ?? [];
$featuredProductsSection = $homepageData['featuredProductsSection'] ?? [];
$categoriesSection = $homepageData['categoriesSection'] ?? [];
$partnersSection = $homepageData['partnersSection'] ?? [];
$partners = $homepageData['partners'] ?? [];

// Filter active hero slides and sort by order
$activeHeroSlides = array_filter($heroSlides, function ($slide) {
    return isset($slide['active']) && $slide['active'] === true;
});
usort($activeHeroSlides, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active key features and sort by order
$activeKeyFeatures = array_filter($keyFeatures, function ($feature) {
    return isset($feature['active']) && $feature['active'] === true;
});
usort($activeKeyFeatures, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active partners and sort by order
$activePartners = array_filter($partners, function ($partner) {
    return isset($partner['active']) && $partner['active'] === true;
});
usort($activePartners, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Fetch featured products from database
$featuredProducts = getFeaturedProducts($pdo, 8);

// Fetch categories from database
$categories = getCategories($pdo, 8);

ob_start();
?>
<div class="swiper hero-slider">
    <div class="swiper-wrapper" id="hero-slider-wrapper">
        <?php foreach ($activeHeroSlides as $slide): ?>
            <div class="swiper-slide relative">
                <div class="hero-aspect-ratio w-full">
                    <?php if (!empty($slide['image'])): ?>
                        <img src="<?= BASE_URL . $slide['image'] ?>" alt="<?= strip_tags($slide['title']) ?>"
                            class="w-full h-full object-cover">
                    <?php else: ?>
                        <img src="https://placehold.co/1800x600?text=<?= urlencode(strip_tags($slide['title'])) ?>"
                            alt="<?= strip_tags($slide['title']) ?>" class="w-full h-full object-cover">
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                    <div class="absolute inset-0 flex items-center">
                        <div class="container mx-auto px-4">
                            <div class="text-white max-w-2xl">
                                <h1 class="text-2xl md:text-5xl font-bold mb-3 md:mb-6"><?= $slide['title'] ?></h1>
                                <p class="text-base md:text-xl mb-4 md:mb-8"><?= $slide['subtitle'] ?></p>
                                <a href="<?= BASE_URL . $slide['buttonUrl'] ?>"
                                    class="bg-primary text-white px-4 md:px-8 py-2 md:py-3 rounded-lg text-sm md:text-lg hover:bg-red-600 transition-colors"><?= $slide['buttonText'] ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next text-white hidden md:flex"></div>
    <div class="swiper-button-prev text-white hidden md:flex"></div>
</div>

<?php if (isset($requestQuoteSection['active']) && $requestQuoteSection['active']): ?>
    <div class="bg-gray-50 py-8">
        <div class="container mx-auto px-4 text-center">
            <a href="<?= BASE_URL . $requestQuoteSection['buttonUrl'] ?>"
                class="inline-flex items-center px-6 py-3 border border-transparent text-lg font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <?= $requestQuoteSection['buttonText'] ?>
            </a>
            <?php if (!empty($requestQuoteSection['description'])): ?>
                <p class="mt-3 text-gray-600"><?= $requestQuoteSection['description'] ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($activeKeyFeatures)): ?>
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-<?= min(count($activeKeyFeatures), 3) ?> gap-8">
            <?php foreach ($activeKeyFeatures as $feature): ?>
                <div class="text-center bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="text-4xl mb-4"><?= $feature['icon'] ?></div>
                    <h3 class="text-xl font-semibold mb-2"><?= $feature['title'] ?></h3>
                    <p class="text-gray-600"><?= $feature['description'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($featuredProductsSection['active']) && $featuredProductsSection['active']): ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold"><?= $featuredProductsSection['title'] ?></h2>
            <?php if (!empty($featuredProductsSection['linkText']) && !empty($featuredProductsSection['linkUrl'])): ?>
                <a href="<?= BASE_URL . $featuredProductsSection['linkUrl'] ?>"
                    class="text-primary hover:text-red-700 font-medium"><?= $featuredProductsSection['linkText'] ?></a>
            <?php endif; ?>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $featuredProductsSection['productsPerRow'] ?? 4 ?> gap-8"
            id="featured-products-container">
            <?php
            $initialProductCount = min(($featuredProductsSection['defaultRows'] ?? 1) * ($featuredProductsSection['productsPerRow'] ?? 4), count($featuredProducts));
            for ($i = 0; $i < $initialProductCount; $i++):
                if (!isset($featuredProducts[$i]))
                    continue;
                $product = $featuredProducts[$i];
                $productImage = !empty($product['images']) ? $product['images'][0] : 'https://placehold.co/600x400?text=No+Image';
                ?>
                <div
                    class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="relative product-image-container">
                        <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>">
                            <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($product['title']) ?>"
                                class="w-full h-48 object-cover">
                            <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT
                            </div>
                            <div
                                class="product-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transition-opacity flex items-center justify-center">
                                <div class="text-white bg-primary px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                    Details <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="p-5">
                        <h3 class="font-bold text-lg mb-2 truncate" title="<?= htmlspecialchars($product['title']) ?>">
                            <?= htmlspecialchars($product['title']) ?>
                        </h3>
                        <p class="text-gray-600 mb-4 line-clamp-2 h-12"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="flex justify-end items-center">
                            <div class="flex space-x-2">
                                <button
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                    <i class="fas fa-shopping-cart mr-1"></i> Buy
                                </button>
                                <button
                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                    <i class="fas fa-tag mr-1"></i> Sell
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
        <?php if (count($featuredProducts) > $initialProductCount && !empty($featuredProductsSection['loadMoreButtonText'])): ?>
            <div class="text-center mt-10">
                <button id="load-more-products"
                    class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                    <span><?= $featuredProductsSection['loadMoreButtonText'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 animate-bounce" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($categoriesSection['active']) && $categoriesSection['active']): ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold"><?= $categoriesSection['title'] ?></h2>
            <?php if (!empty($categoriesSection['linkText']) && !empty($categoriesSection['linkUrl'])): ?>
                <a href="<?= BASE_URL . $categoriesSection['linkUrl'] ?>"
                    class="text-primary hover:text-red-700 font-medium"><?= $categoriesSection['linkText'] ?></a>
            <?php endif; ?>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $categoriesSection['categoriesPerRow'] ?? 4 ?> gap-8"
            id="categories-container">
            <?php
            $initialCategoryCount = min(($categoriesSection['defaultRows'] ?? 1) * ($categoriesSection['categoriesPerRow'] ?? 4), count($categories));
            for ($i = 0; $i < $initialCategoryCount; $i++):
                if (!isset($categories[$i]))
                    continue;
                $category = $categories[$i];
                ?>
                <a href="<?= BASE_URL ?>view/category/<?= $category['id'] ?>"
                    class="block relative rounded-xl overflow-hidden group cursor-pointer shadow-lg">
                    <img src="<?= $category['image'] ?>" alt="<?= htmlspecialchars($category['name']) ?>"
                        class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                        <div class="absolute bottom-0 left-0 right-0 p-6">
                            <h3 class="text-white text-xl font-bold mb-2 truncate"
                                title="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?>
                            </h3>
                            <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                            <div
                                class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100 inline-block">
                                Explore <i class="fas fa-arrow-right ml-1"></i>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endfor; ?>
        </div>
        <?php if (count($categories) > $initialCategoryCount && !empty($categoriesSection['loadMoreButtonText'])): ?>
            <div class="text-center mt-10">
                <button id="load-more-categories"
                    class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                    <span><?= $categoriesSection['loadMoreButtonText'] ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 animate-bounce" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($partnersSection['active']) && $partnersSection['active'] && !empty($activePartners)): ?>
    <div class="bg-gray-50 py-8">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-4"><?= $partnersSection['title'] ?></h2>
                <?php if (!empty($partnersSection['description'])): ?>
                    <p class="text-gray-600 max-w-2xl mx-auto"><?= $partnersSection['description'] ?></p>
                <?php endif; ?>
            </div>

            <div class="partners-carousel relative">
                <div class="swiper partners-slider">
                    <div class="swiper-wrapper">
                        <?php foreach (array_chunk($activePartners, 5) as $partnerGroup): ?>
                            <div class="swiper-slide">
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 md:gap-6">
                                    <?php foreach ($partnerGroup as $partner): ?>
                                        <?php
                                        $partnerLink = '#';
                                        $targetAttr = '';
                                        if (isset($partner['hasLink']) && $partner['hasLink'] && !empty($partner['redirectLink'])) {
                                            $partnerLink = $partner['redirectLink']; // Direct URL, not prefixed with BASE_URL
                                            $targetAttr = 'target="_blank"';
                                        }
                                        ?>
                                        <a href="<?= $partnerLink ?>" <?= $targetAttr ?>
                                            class="partner-card bg-white rounded-lg p-4 md:p-6 shadow-md hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center h-32 md:h-40">
                                            <?php if (!empty($partner['logo'])): ?>
                                                <img src="<?= BASE_URL . $partner['logo'] ?>" alt="<?= $partner['name'] ?>"
                                                    class="h-12 md:h-16 object-contain mb-2 md:mb-4">
                                            <?php else: ?>
                                                <img src="https://placehold.co/200x100?text=<?= urlencode($partner['name']) ?>"
                                                    alt="<?= $partner['name'] ?>" class="h-12 md:h-16 object-contain mb-2 md:mb-4">
                                            <?php endif; ?>
                                            <p class="text-center font-medium text-gray-800 text-sm md:text-base">
                                                <?= $partner['name'] ?>
                                            </p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div
                    class="swiper-button-next partners-next absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-md p-3 z-10 hidden md:flex">
                </div>
                <div
                    class="swiper-button-prev partners-prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-md p-3 z-10 hidden md:flex">
                </div>
            </div>

            <?php if (!empty($partnersSection['ctaButtonText']) && !empty($partnersSection['ctaButtonUrl'])): ?>
                <div class="text-center mt-10">
                    <a href="<?= BASE_URL . $partnersSection['ctaButtonUrl'] ?>"
                        class="inline-flex items-center px-6 py-3 border border-primary text-primary font-medium rounded-lg hover:bg-primary hover:text-white transition-colors duration-300">
                        <?= $partnersSection['ctaButtonText'] ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<style>
    .hero-aspect-ratio {
        position: relative;
    }

    @media (min-width: 768px) {
        .hero-aspect-ratio {
            padding-bottom: calc(1 / 3 * 100%);
            /* 3:1 aspect ratio for desktop */
        }
    }

    @media (max-width: 767px) {
        .hero-aspect-ratio {
            padding-bottom: calc(9 / 16 * 100%);
            /* 16:9 aspect ratio for mobile */
        }
    }

    .hero-aspect-ratio>* {
        position: absolute;
        height: 100%;
        width: 100%;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #ef4444 !important;
        width: 30px !important;
        height: 30px !important;
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 14px !important;
    }

    .partners-next,
    .partners-prev {
        width: 30px !important;
        height: 30px !important;
    }

    .partners-next:after,
    .partners-prev:after {
        font-size: 14px !important;
    }

    /* Text truncation utilities */
    .truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-clamp: 2;
    }

    /* Product image hover effects */
    .product-image-container {
        position: relative;
        overflow: hidden;
    }

    .product-overlay {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-image-container:hover .product-overlay {
        opacity: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        var heroSwiper = new Swiper('.hero-slider', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            speed: 1000,
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            }
        });

        var partnersSwiper = new Swiper('.partners-slider', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 7000,
                disableOnInteraction: true
            },
            speed: 1200,
            navigation: {
                nextEl: '.partners-next',
                prevEl: '.partners-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 1
                }
            }
        });

        const partnerCards = document.querySelectorAll('.partner-card');
        partnerCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                partnersSwiper.autoplay.stop();
            });
            card.addEventListener('mouseleave', () => {
                partnersSwiper.autoplay.start();
            });
        });

        const loadMoreProductsBtn = document.getElementById('load-more-products');
        if (loadMoreProductsBtn) {
            let productsLoaded = <?= $initialProductCount ?>;
            const totalProducts = <?= count($featuredProducts) ?>;
            const productsPerRow = <?= $featuredProductsSection['productsPerRow'] ?? 4 ?>;

            loadMoreProductsBtn.addEventListener('click', function () {
                const productsContainer = document.getElementById('featured-products-container');
                const productsToLoad = Math.min(productsPerRow, totalProducts - productsLoaded);

                if (productsToLoad <= 0) {
                    loadMoreProductsBtn.disabled = true;
                    loadMoreProductsBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    loadMoreProductsBtn.querySelector('span').textContent = 'No More Products';
                    loadMoreProductsBtn.querySelector('svg').classList.remove('animate-bounce');
                    return;
                }

                loadMoreProductsBtn.querySelector('span').textContent = 'Loading...';

                setTimeout(() => {
                    const products = <?= json_encode($featuredProducts) ?>;

                    for (let i = productsLoaded; i < productsLoaded + productsToLoad; i++) {
                        if (!products[i]) continue;

                        const product = products[i];
                        const productImage = product.images && product.images.length > 0 ?
                            product.images[0] : 'https://placehold.co/600x400?text=No+Image';

                        const productElement = document.createElement('div');
                        productElement.className = 'bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 opacity-0';
                        productElement.innerHTML = `
                            <div class="relative product-image-container">
                                <a href="<?= BASE_URL ?>view/product/${product.id}">
                                    <img src="${productImage}" alt="${product.title}" class="w-full h-48 object-cover">
                                    <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT</div>
                                    <div class="product-overlay absolute inset-0 bg-black bg-opacity-60 opacity-0 transition-opacity flex items-center justify-center">
                                        <div class="text-white bg-primary px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                                            Details <i class="fas fa-arrow-right ml-1"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-lg mb-2 truncate" title="${product.title}">${product.title}</h3>
                                <p class="text-gray-600 mb-4 line-clamp-2 h-12">${product.description}</p>
                                <div class="flex justify-end items-center">
                                    <div class="flex space-x-2">
                                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                            <i class="fas fa-shopping-cart mr-1"></i> Buy
                                        </button>
                                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                            <i class="fas fa-tag mr-1"></i> Sell
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        productsContainer.appendChild(productElement);

                        setTimeout(() => {
                            productElement.classList.add('fade-in');
                        }, (i - productsLoaded) * 150);
                    }

                    productsLoaded += productsToLoad;

                    loadMoreProductsBtn.querySelector('span').textContent = '<?= $featuredProductsSection['loadMoreButtonText'] ?? 'Load More' ?>';

                    if (productsLoaded >= totalProducts) {
                        loadMoreProductsBtn.disabled = true;
                        loadMoreProductsBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        loadMoreProductsBtn.querySelector('span').textContent = 'No More Products';
                        loadMoreProductsBtn.querySelector('svg').classList.remove('animate-bounce');
                    }
                }, 800);
            });
        }

        const loadMoreCategoriesBtn = document.getElementById('load-more-categories');
        if (loadMoreCategoriesBtn) {
            let categoriesLoaded = <?= $initialCategoryCount ?>;
            const totalCategories = <?= count($categories) ?>;
            const categoriesPerRow = <?= $categoriesSection['categoriesPerRow'] ?? 4 ?>;

            loadMoreCategoriesBtn.addEventListener('click', function () {
                const categoriesContainer = document.getElementById('categories-container');
                const categoriesToLoad = Math.min(categoriesPerRow, totalCategories - categoriesLoaded);

                if (categoriesToLoad <= 0) {
                    loadMoreCategoriesBtn.disabled = true;
                    loadMoreCategoriesBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    loadMoreCategoriesBtn.querySelector('span').textContent = 'No More Categories';
                    loadMoreCategoriesBtn.querySelector('svg').classList.remove('animate-bounce');
                    return;
                }

                loadMoreCategoriesBtn.querySelector('span').textContent = 'Loading...';

                setTimeout(() => {
                    const categories = <?= json_encode($categories) ?>;

                    for (let i = categoriesLoaded; i < categoriesLoaded + categoriesToLoad; i++) {
                        if (!categories[i]) continue;

                        const category = categories[i];
                        const categoryElement = document.createElement('a');
                        categoryElement.href = `<?= BASE_URL ?>view/category/${category.id}`;
                        categoryElement.className = 'block relative rounded-xl overflow-hidden group cursor-pointer shadow-lg opacity-0';
                        categoryElement.innerHTML = `
                            <img src="${category.image}" alt="${category.name}" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                                <div class="absolute bottom-0 left-0 right-0 p-6">
                                    <h3 class="text-white text-xl font-bold mb-2 truncate" title="${category.name}">${category.name}</h3>
                                    <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                                    <div class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100 inline-block">
                                        Explore <i class="fas fa-arrow-right ml-1"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                        categoriesContainer.appendChild(categoryElement);

                        setTimeout(() => {
                            categoryElement.classList.add('fade-in');
                        }, (i - categoriesLoaded) * 150);
                    }

                    categoriesLoaded += categoriesToLoad;

                    loadMoreCategoriesBtn.querySelector('span').textContent = '<?= $categoriesSection['loadMoreButtonText'] ?? 'Load More' ?>';

                    if (categoriesLoaded >= totalCategories) {
                        loadMoreCategoriesBtn.disabled = true;
                        loadMoreCategoriesBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        loadMoreCategoriesBtn.querySelector('span').textContent = 'No More Categories';
                        loadMoreCategoriesBtn.querySelector('svg').classList.remove('animate-bounce');
                    }
                }, 800);
            });
        }
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>