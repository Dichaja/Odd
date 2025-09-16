<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = $pageTitle ?? 'Zzimba Online Uganda';
$activeNav = $activeNav ?? 'home';

function loadHomepageData()
{
    $filePath = __DIR__ . '/page-data/homepage/index.json';
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?: [];
    }
    return [];
}
function getFeaturedProducts($pdo, $limit = 8)
{
    $stmt = $pdo->prepare("SELECT p.id, p.title, p.description, p.category_id, c.name AS category_name,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) AS views, EXISTS(SELECT 1 FROM store_products sp JOIN store_categories sc ON sc.id = sp.store_category_id JOIN vendor_stores vs ON vs.id = sc.store_id JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id AND vs.status = 'active') AS has_pricing,(SELECT MIN(pp.price) FROM store_products sp JOIN store_categories sc ON sc.id = sp.store_category_id JOIN vendor_stores vs ON vs.id = sc.store_id JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id AND vs.status = 'active') AS lowest_price FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'published' ORDER BY has_pricing DESC, p.created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as &$product) {
        $product['images'] = getProductImages($product['id']);
        $product['has_pricing'] = (bool) $product['has_pricing'];
        $product['lowest_price'] = $product['lowest_price'] ? (float) $product['lowest_price'] : null;
    }
    return $products;
}
function getCategories($pdo, $limit = 8)
{
    $stmt = $pdo->prepare("SELECT id, name, description, meta_title, meta_description, meta_keywords, status FROM product_categories WHERE status = 'active' AND featured = 1 ORDER BY name ASC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($categories as &$category) {
        $category['image'] = getCategoryImage($category['id']);
    }
    return $categories;
}
function getProductImages($uuid)
{
    $dir = __DIR__ . '/img/products/' . $uuid;
    $placeholder = ['https://placehold.co/600x400?text=No+Image'];
    if (!is_dir($dir))
        return $placeholder;
    $json = $dir . '/images.json';
    if (!file_exists($json))
        return $placeholder;
    $data = json_decode(file_get_contents($json), true);
    if (empty($data['images']))
        return $placeholder;
    $out = [];
    foreach ($data['images'] as $f) {
        $out[] = filter_var($f, FILTER_VALIDATE_URL) ? $f : BASE_URL . "img/products/$uuid/$f";
    }
    return $out;
}
function getCategoryImage($uuid)
{
    $dir = __DIR__ . '/img/product-categories/' . $uuid;
    if (is_dir($dir)) {
        $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return BASE_URL . 'img/product-categories/' . $uuid . '/' . basename($files[0]);
        }
    }
    return 'https://placehold.co/800x450?text=Category';
}
function formatPrice($price)
{
    if ($price === null || $price <= 0)
        return null;
    return 'UGX ' . number_format($price, 0) . '/=';
}

$homepageData = loadHomepageData();
$heroSlides = $homepageData['heroSlides'] ?? [];
$requestQuoteSection = $homepageData['requestQuoteSection'] ?? [];
$keyFeatures = $homepageData['keyFeatures'] ?? [];
$featuredProductsSection = $homepageData['featuredProductsSection'] ?? [];
$categoriesSection = $homepageData['categoriesSection'] ?? [];
$partnersSection = $homepageData['partnersSection'] ?? [];
$partners = $homepageData['partners'] ?? [];

$activeHeroSlides = array_filter($heroSlides, fn($s) => !empty($s['active']));
usort($activeHeroSlides, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));
$activeKeyFeatures = array_filter($keyFeatures, fn($f) => !empty($f['active']));
usort($activeKeyFeatures, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));
$activePartners = array_filter($partners, fn($p) => !empty($p['active']));
usort($activePartners, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));

$featuredProducts = getFeaturedProducts($pdo, 24);
$categories = getCategories($pdo, 24);

$fpPerRow = $featuredProductsSection['productsPerRow'] ?? 4;
$fpDefaultRows = $featuredProductsSection['defaultRows'] ?? 1;
$catPerRow = $categoriesSection['categoriesPerRow'] ?? 4;
$catDefaultRows = $categoriesSection['defaultRows'] ?? 1;

ob_start();
?>
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto
    }

    .hero-aspect-ratio {
        position: relative
    }

    @media (min-width:768px) {
        .hero-aspect-ratio {
            padding-bottom: 33.33%
        }
    }

    @media (max-width:767px) {
        .hero-aspect-ratio {
            padding-bottom: 56.25%
        }
    }

    .hero-aspect-ratio>* {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0
    }

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
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, .6);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all .3s ease;
        z-index: 10
    }

    .product-card:hover .product-details-btn {
        opacity: 1;
        visibility: visible
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

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .fade-in {
        animation: fadeIn .5s ease-out forwards
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #ef4444 !important;
        width: 30px !important;
        height: 30px !important
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 14px !important
    }

    .partners-next,
    .partners-prev {
        width: 30px !important;
        height: 30px !important
    }
</style>

<div x-data="IndexPage()" x-init="init()" class="space-y-0">
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
                                        class="bg-primary text-white px-4 md:px-8 py-2 md:py-3 rounded-lg text-sm md:text-lg hover:bg-red-600 transition-colors inline-flex items-center">
                                        <i data-lucide="mouse-pointer-click"
                                            class="w-5 h-5 mr-2"></i><?= $slide['buttonText'] ?>
                                    </a>
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

    <?php if (!empty($requestQuoteSection['active'])): ?>
        <div class="bg-gray-50 py-8">
            <div class="container mx-auto px-4 text-center">
                <a href="<?= BASE_URL . $requestQuoteSection['buttonUrl'] ?>"
                    class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 transition-shadow shadow-md">
                    <i data-lucide="file-text" class="w-5 h-5 mr-2"></i><?= $requestQuoteSection['buttonText'] ?>
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

    <?php if (!empty($featuredProductsSection['active'])): ?>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold"><?= $featuredProductsSection['title'] ?></h2>
                <?php if (!empty($featuredProductsSection['linkText']) && !empty($featuredProductsSection['linkUrl'])): ?>
                    <a href="<?= BASE_URL . $featuredProductsSection['linkUrl'] ?>"
                        class="text-red-600 hover:text-red-700 font-medium inline-flex items-center">
                        <?= $featuredProductsSection['linkText'] ?><i data-lucide="arrow-right" class="w-5 h-5 ml-1"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= $fpPerRow ?> gap-6"
                id="featured-products-container">
                <template x-for="(p, idx) in products.slice(0, shownProducts)" :key="p.id">
                    <div
                        class="product-card relative border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                        <div class="relative">
                            <img :src="(p.images && p.images[0]) ? p.images[0] : 'https://placehold.co/600x400?text=No+Image'"
                                :alt="p.title" class="w-full h-40 md:h-48 object-cover">
                            <div
                                class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-lg text-xs font-semibold">
                                HOT</div>
                            <div class="product-details-btn">
                                <a :href="'<?= BASE_URL ?>view/product/' + p.id"
                                    class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">View
                                    Details</a>
                            </div>
                        </div>
                        <div class="p-3 md:p-5 flex flex-col flex-1">
                            <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base" x-text="p.title">
                            </h3>
                            <div class="flex-1 flex flex-col justify-end">
                                <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block"
                                    x-text="p.description"></p>
                                <div class="flex items-center text-gray-500 text-xs md:text-sm mb-3">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    <span x-text="Number(p.views || 0).toLocaleString() + ' views'"></span>
                                </div>
                                <div class="text-center mb-3" x-show="p.has_pricing && p.lowest_price">
                                    <span class="price-text font-bold text-[#D92B13]"
                                        x-text="formatPrice(p.lowest_price)"></span>
                                </div>
                                <div class="flex gap-2">
                                    <template x-if="p.has_pricing">
                                        <a :href="'<?= BASE_URL ?>view/product/' + p.id + '?action=buy'"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                            <i data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i> Buy
                                        </a>
                                    </template>
                                    <button @click="openSell(p)"
                                        class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-md transition-colors flex items-center justify-center flex-1 text-xs md:text-sm font-medium">
                                        <i data-lucide="tags" class="w-4 h-4 mr-1"></i> Sell
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="text-center mt-10" x-show="shownProducts < products.length">
                <button @click="moreProducts"
                    class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium inline-flex items-center">
                    <span><?= $featuredProductsSection['loadMoreButtonText'] ?? 'Load more' ?></span>
                    <i data-lucide="chevrons-down" class="w-5 h-5 ml-2"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($categoriesSection['active'])): ?>
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold"><?= $categoriesSection['title'] ?></h2>
                <?php if (!empty($categoriesSection['linkText']) && !empty($categoriesSection['linkUrl'])): ?>
                    <a href="<?= BASE_URL . $categoriesSection['linkUrl'] ?>"
                        class="text-red-600 hover:text-red-700 font-medium inline-flex items-center">
                        <?= $categoriesSection['linkText'] ?><i data-lucide="arrow-right" class="w-5 h-5 ml-1"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= $catPerRow ?> gap-8" id="categories-container">
                <template x-for="(c, i) in categories.slice(0, shownCategories)" :key="c.id">
                    <a :href="'<?= BASE_URL ?>view/category/' + c.id"
                        class="block relative rounded-xl overflow-hidden group cursor-pointer shadow-lg">
                        <img :src="c.image" :alt="c.name"
                            class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-white text-xl font-bold mb-2 truncate" :title="c.name" x-text="c.name"></h3>
                                <div
                                    class="w-10 h-1 bg-red-600 mb-4 transform transition-all duration-300 group-hover:w-20">
                                </div>
                                <div
                                    class="text-white bg-red-600 bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100 inline-flex items-center">
                                    Explore <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
            <div class="text-center mt-10" x-show="shownCategories < categories.length">
                <button @click="moreCategories"
                    class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
                    <span><?= $categoriesSection['loadMoreButtonText'] ?? 'Load more' ?></span>
                    <i data-lucide="chevrons-down" class="w-5 h-5 ml-2"></i>
                </button>
            </div>
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
                            <?php
                            $isMobile = isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile|Android|iP(hone|od|ad)/i', $_SERVER['HTTP_USER_AGENT']);
                            $mobileChunkSize = 2;
                            $desktopChunkSize = 5;
                            $chunkSize = $isMobile ? $mobileChunkSize : $desktopChunkSize;
                            $partnerChunks = array_chunk($activePartners, $chunkSize);
                            ?>
                            <?php foreach ($partnerChunks as $partnerGroup): ?>
                                <div class="swiper-slide">
                                    <div
                                        class="grid grid-cols-<?= $isMobile ? 2 : 2 ?> md:grid-cols-<?= $desktopChunkSize ?> gap-4 md:gap-6">
                                        <?php foreach ($partnerGroup as $partner): ?>
                                            <?php
                                            $partnerLink = '#';
                                            $targetAttr = '';
                                            if (isset($partner['hasLink']) && $partner['hasLink'] && !empty($partner['redirectLink'])) {
                                                $partnerLink = $partner['redirectLink'];
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
                            <?= $partnersSection['ctaButtonText'] ?><i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    window.__FP__ = <?= json_encode($featuredProducts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.__CAT__ = <?= json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.__FP_PER_ROW__ = <?= (int) $fpPerRow ?>;
    window.__FP_DEFAULT_ROWS__ = <?= (int) $fpDefaultRows ?>;
    window.__CAT_PER_ROW__ = <?= (int) $catPerRow ?>;
    window.__CAT_DEFAULT_ROWS__ = <?= (int) $catDefaultRows ?>;
    function IndexPage() {
        return {
            products: window.__FP__ || [],
            categories: window.__CAT__ || [],
            perRow: window.__FP_PER_ROW__ || 4,
            catPerRow: window.__CAT_PER_ROW__ || 4,
            shownProducts: Math.min((window.__FP_DEFAULT_ROWS__ || 1) * (window.__FP_PER_ROW__ || 4), (window.__FP__ || []).length),
            shownCategories: Math.min((window.__CAT_DEFAULT_ROWS__ || 1) * (window.__CAT_PER_ROW__ || 4), (window.__CAT__ || []).length),
            init() {
                if (typeof Swiper !== 'undefined') {
                    new Swiper('.hero-slider', { loop: true, autoplay: { delay: 5000, disableOnInteraction: false }, effect: 'fade', fadeEffect: { crossFade: true }, speed: 1000, pagination: { el: '.swiper-pagination', clickable: true }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
                    new Swiper('.partners-slider', { slidesPerView: 1, spaceBetween: 30, loop: true, autoplay: { delay: 7000, disableOnInteraction: true }, speed: 1200, navigation: { nextEl: '.partners-next', prevEl: '.partners-prev' }, breakpoints: { 768: { slidesPerView: 1 } } });
                }
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            },
            moreProducts() { this.shownProducts = Math.min(this.shownProducts + this.perRow, this.products.length); this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); }); },
            moreCategories() { this.shownCategories = Math.min(this.shownCategories + this.catPerRow, this.categories.length); this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); }); },
            formatPrice(price) { if (!price || price <= 0) return ''; return 'UGX ' + Number(price).toLocaleString() + '/='; },
            openSell(p) { if (typeof openVendorSellModal === 'function') { openVendorSellModal(String(p.id), String(p.title || '')); } }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>