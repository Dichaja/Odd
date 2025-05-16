<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = $pageTitle ?? 'Zzimba Online Uganda';
$activeNav = $activeNav ?? 'home';

// Load homepage data from JSON file
function loadHomepageData() {
    $filePath = __DIR__ . '/page-data/homepage/index.json';

    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?: [];
    }

    return [];
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

// Fallback data for featured products (not in JSON)
$featuredProducts = [
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Premium Cement',
        'description' => 'High-quality Portland cement',
        'price' => 'UGX 29,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Reinforced Steel',
        'description' => 'Strong and durable steel',
        'price' => 'UGX 49,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Concrete Blocks',
        'description' => 'Reliable concrete blocks',
        'price' => 'UGX 19,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Bricks',
        'description' => 'Red clay bricks',
        'price' => 'UGX 9,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Roofing Sheets',
        'description' => 'Durable metal roofing',
        'price' => 'UGX 39,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'PVC Pipes',
        'description' => 'High-quality plumbing pipes',
        'price' => 'UGX 15,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Paint Buckets',
        'description' => 'Premium interior paint',
        'price' => 'UGX 24,999'
    ],
    [
        'image' => 'https://placehold.co/600x400',
        'title' => 'Electrical Wiring',
        'description' => 'Safe and reliable wiring',
        'price' => 'UGX 12,999'
    ]
];

// Fallback data for categories (not in JSON)
$categories = [
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Cement & Concrete'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Bricks & Blocks'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Steel & Metals'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Tiles & Flooring'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Plumbing Supplies'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Electrical Materials'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Paints & Finishes'
    ],
    [
        'image' => 'https://placehold.co/800x450',
        'title' => 'Tools & Equipment'
    ]
];

// Filter active hero slides and sort by order
$activeHeroSlides = array_filter($heroSlides, function($slide) {
    return isset($slide['active']) && $slide['active'] === true;
});
usort($activeHeroSlides, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active key features and sort by order
$activeKeyFeatures = array_filter($keyFeatures, function($feature) {
    return isset($feature['active']) && $feature['active'] === true;
});
usort($activeKeyFeatures, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active partners and sort by order
$activePartners = array_filter($partners, function($partner) {
    return isset($partner['active']) && $partner['active'] === true;
});
usort($activePartners, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

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
                        <img src="https://placehold.co/1800x600?text=<?= urlencode(strip_tags($slide['title'])) ?>" alt="<?= strip_tags($slide['title']) ?>"
                            class="w-full h-full object-cover">
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
            <a href="<?= BASE_URL . $featuredProductsSection['linkUrl'] ?>" class="text-primary hover:text-red-700 font-medium"><?= $featuredProductsSection['linkText'] ?></a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $featuredProductsSection['productsPerRow'] ?? 4 ?> gap-8" id="featured-products-container">
        <?php
        $initialProductCount = min(($featuredProductsSection['defaultRows'] ?? 1) * ($featuredProductsSection['productsPerRow'] ?? 4), count($featuredProducts));
        for ($i = 0; $i < $initialProductCount; $i++):
            $product = $featuredProducts[$i];
            ?>
            <div
                class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="relative">
                    <img src="<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-lg mb-2"><?= $product['title'] ?></h3>
                    <p class="text-gray-600 mb-4 h-12"><?= $product['description'] ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-primary font-bold text-xl"><?= $product['price'] ?></span>
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
            <a href="<?= BASE_URL . $categoriesSection['linkUrl'] ?>" class="text-primary hover:text-red-700 font-medium"><?= $categoriesSection['linkText'] ?></a>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $categoriesSection['categoriesPerRow'] ?? 4 ?> gap-8" id="categories-container">
        <?php
        $initialCategoryCount = min(($categoriesSection['defaultRows'] ?? 1) * ($categoriesSection['categoriesPerRow'] ?? 4), count($categories));
        for ($i = 0; $i < $initialCategoryCount; $i++):
            $category = $categories[$i];
            ?>
            <div class="relative rounded-xl overflow-hidden group cursor-pointer shadow-lg">
                <img src="<?= $category['image'] ?>" alt="<?= $category['title'] ?>"
                    class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h3 class="text-white text-xl font-bold mb-2"><?= $category['title'] ?></h3>
                        <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                        <button
                            class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100">
                            Explore <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
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
                                            <img src="https://placehold.co/200x100?text=<?= urlencode($partner['name']) ?>" alt="<?= $partner['name'] ?>"
                                                class="h-12 md:h-16 object-contain mb-2 md:mb-4">
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
                    for (let i = productsLoaded; i < productsLoaded + productsToLoad; i++) {
                        const product = <?= json_encode($featuredProducts) ?>[i];
                        const productElement = document.createElement('div');
                        productElement.className = 'bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 opacity-0';
                        productElement.innerHTML = `
                            <div class="relative">
                                <img src="${product.image}" alt="${product.title}" class="w-full h-48 object-cover">
                                <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT</div>
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-lg mb-2">${product.title}</h3>
                                <p class="text-gray-600 mb-4 h-12">${product.description}</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-primary font-bold text-xl">${product.price}</span>
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
                    for (let i = categoriesLoaded; i < categoriesLoaded + categoriesToLoad; i++) {
                        const category = <?= json_encode($categories) ?>[i];
                        const categoryElement = document.createElement('div');
                        categoryElement.className = 'relative rounded-xl overflow-hidden group cursor-pointer shadow-lg opacity-0';
                        categoryElement.innerHTML = `
                            <img src="${category.image}" alt="${category.title}" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                                <div class="absolute bottom-0 left-0 right-0 p-6">
                                    <h3 class="text-white text-xl font-bold mb-2">${category.title}</h3>
                                    <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                                    <button class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100">
                                        Explore <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
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