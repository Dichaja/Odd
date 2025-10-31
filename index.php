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
    $stmt = $pdo->prepare("SELECT p.id, p.title, p.description, p.category_id, c.name AS category_name,(SELECT COUNT(DISTINCT session_id) FROM product_views WHERE product_id = p.id) AS views, EXISTS(SELECT 1 FROM store_products sp JOIN store_categories sc ON sc.id = sp.store_category_id JOIN vendor_stores vs ON vs.id = sc.store_id JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id AND vs.status = 'active' AND pp.status = 'active') AS has_pricing,(SELECT MIN(pp.price) FROM store_products sp JOIN store_categories sc ON sc.id = sp.store_category_id JOIN vendor_stores vs ON vs.id = sc.store_id JOIN product_pricing pp ON pp.store_products_id = sp.id WHERE sp.product_id = p.id AND vs.status = 'active' AND pp.status = 'active') AS lowest_price FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'published' ORDER BY has_pricing DESC, p.created_at DESC LIMIT :limit");
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
        if (!empty($files))
            return BASE_URL . 'img/product-categories/' . $uuid . '/' . basename($files[0]);
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
            padding-bottom: 62.5%
        }
    }

    .hero-aspect-ratio>* {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%
    }

    .line-clamp-2 {
        -webkit-box-orient: vertical;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .line-clamp-3 {
        -webkit-box-orient: vertical;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .product-details-btn {
        position: absolute;
        inset: 0;
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
            font-size: clamp(1.1rem, 2.2vw, 1.25rem)
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
        width: 26px !important;
        height: 26px !important
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 13px !important
    }

    .partners-next,
    .partners-prev {
        width: 30px !important;
        height: 30px !important
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none
    }

    .hide-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none
    }

    .hero-layer {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: clamp(12px, 2vw, 24px);
        padding-top: calc(clamp(12px, 2vw, 24px) + env(safe-area-inset-top, 0px));
        padding-bottom: calc(clamp(12px, 2vw, 24px) + env(safe-area-inset-bottom, 0px))
    }

    .hero-cta {
        background: #D92B13;
        color: #fff;
        padding: .5rem 1rem;
        border-radius: .5rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        box-shadow: 0 8px 18px rgba(217, 43, 19, .3);
        transition: transform .2s, box-shadow .2s, background-color .2s;
        font-size: clamp(.85rem, 1.1vw, .95rem);
        line-height: 1
    }

    .hero-cta:hover {
        background: #B91C1C;
        transform: translateY(-1px);
        box-shadow: 0 12px 24px rgba(217, 43, 19, .38)
    }

    .rq-cta {
        background: #D92B13;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .6rem;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        font-weight: 800;
        box-shadow: 0 16px 38px rgba(217, 43, 19, .5);
        text-decoration: none
    }

    .rq-cta:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 46px rgba(217, 43, 19, .6)
    }

    .hero-slider .swiper-pagination,
    .hero-slider-m .swiper-pagination {
        bottom: 10px
    }

    @media (max-width:767px) {
        .hero-cta {
            padding: .4rem .8rem;
            border-radius: .5rem;
            font-size: .8rem
        }

        .hero-cta i {
            width: 18px;
            height: 18px
        }
    }
</style>

<script>
    window.__pendingVendorAction = null;
    window.setPendingVendorAction = (a) => { window.__pendingVendorAction = a || null };
    (function () {
        const wrap = () => {
            const orig = window.updateUIAfterLogin;
            window.updateUIAfterLogin = function (user) {
                try { typeof orig === 'function' && orig(user) } catch (e) { }
                try { window.dispatchEvent(new CustomEvent('zz:session-login', { detail: user || {} })) } catch (e) { }
                const root = document.querySelector('[x-data="IndexPage()"]');
                if (root && root.__x) { try { root.__x.$data.handlePostLogin(user || {}) } catch (e) { } }
            }
        };
        if (document.readyState === 'complete' || document.readyState === 'interactive') { wrap() } else { document.addEventListener('DOMContentLoaded', wrap) }
    })();
</script>

<div x-data="IndexPage()" x-init="init()" class="space-y-0">
    <div class="hidden md:block">
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
                            <div class="absolute inset-0 bg-black/70"></div>
                            <div class="hero-layer">
                                <div class="container mx-auto px-4 py-12 md:py-16">
                                    <div class="text-white max-w-3xl">
                                        <h1 class="text-3xl md:text-5xl font-bold mb-3 leading-tight"><?= $slide['title'] ?>
                                        </h1>
                                        <p class="text-base md:text-lg mb-5 opacity-95"><?= $slide['subtitle'] ?></p>
                                        <div x-show="!loggedIn">
                                            <button @click="openHeroLogin" class="hero-cta"><i
                                                    data-lucide="mouse-pointer-click"
                                                    class="w-5 h-5"></i><?= $slide['buttonText'] ?></button>
                                        </div>
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
            <div class="py-8 bg-gray-50 dark:bg白/5">
                <div class="container mx-auto px-4 text-center">
                    <a href="<?= BASE_URL . $requestQuoteSection['buttonUrl'] ?>"
                        class="inline-flex items-center px-8 py-4 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-shadow shadow-[0_16px_36px_rgba(217,43,19,0.45)]"><i
                            data-lucide="file-text" class="w-5 h-5 mr-2"></i><?= $requestQuoteSection['buttonText'] ?></a>
                    <?php if (!empty($requestQuoteSection['description'])): ?>
                        <p class="mt-3 text-gray-600 dark:text-white/70"><?= $requestQuoteSection['description'] ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeKeyFeatures)): ?>
            <div class="container mx-auto px-4 py-8">
                <div class="grid grid-cols-1 md:grid-cols-<?= min(count($activeKeyFeatures), 3) ?> gap-8">
                    <?php foreach ($activeKeyFeatures as $feature): ?>
                        <div
                            class="text-center bg-white dark:bg-secondary/80 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                            <div class="text-4xl mb-4"><?= $feature['icon'] ?></div>
                            <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white"><?= $feature['title'] ?></h3>
                            <p class="text-gray-600 dark:text-white/70"><?= $feature['description'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($featuredProductsSection['active'])): ?>
            <div class="container mx-auto px-4 py-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white"><?= $featuredProductsSection['title'] ?>
                    </h2>
                    <?php if (!empty($featuredProductsSection['linkText']) && !empty($featuredProductsSection['linkUrl'])): ?>
                        <a href="<?= BASE_URL . $featuredProductsSection['linkUrl'] ?>"
                            class="text-red-600 hover:text-red-700 font-medium inline-flex items-center"><?= $featuredProductsSection['linkText'] ?><i
                                data-lucide="arrow-right" class="w-5 h-5 ml-1"></i></a>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-<?= $fpPerRow ?> gap-6"
                    id="featured-products-container">
                    <template x-for="(p, idx) in products.slice(0, shownProducts)" :key="p.id">
                        <div
                            class="product-card relative border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary shadow-sm overflow-hidden transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                            <div class="relative">
                                <img :src="(p.images && p.images[0]) ? p.images[0] : 'https://placehold.co/600x400?text=No+Image'"
                                    :alt="p.title" class="w-full h-40 md:h-48 object-cover">
                                <div
                                    class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-lg text-xs font-semibold">
                                    HOT</div>
                                <div class="product-details-btn">
                                    <a :href="'<?= BASE_URL ?>view/product/' + p.id"
                                        class="bg-white dark:bg-secondary text-gray-800 dark:text-white px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm shadow-lg">View
                                        Details</a>
                                </div>
                            </div>
                            <div class="p-3 md:p-5 flex flex-col flex-1">
                                <h3 class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-sm md:text-base"
                                    x-text="p.title"></h3>
                                <div class="flex-1 flex flex-col justify-end">
                                    <p class="text-gray-600 dark:text-white/70 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block"
                                        x-text="p.description"></p>
                                    <div class="flex items-center text-gray-500 dark:text-white/70 text-xs md:text-sm mb-3">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i><span
                                            x-text="Number(p.views || 0).toLocaleString() + ' views'"></span>
                                    </div>
                                    <div class="text-center mb-3"
                                        x-bind:class="(p.has_pricing && p.lowest_price) ? '' : 'invisible'">
                                        <span class="price-text font-bold text-[#D92B13]"
                                            x-text="formatPrice(p.lowest_price)"></span>
                                    </div>
                                    <div class="mt-auto flex gap-2">
                                        <template x-if="p.has_pricing">
                                            <a :href="'<?= BASE_URL ?>view/product/' + p.id + '?action=buy'"
                                                class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-md transition-colors bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium"><i
                                                    data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i> Buy</a>
                                        </template>
                                        <button @click="openSell(p)"
                                            class="flex-1 inline-flex items-center justify-center h-10 px-4 rounded-md transition-colors bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium"><i
                                                data-lucide="tags" class="w-4 h-4 mr-1"></i> Sell</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="text-center mt-10" x-show="shownProducts < products.length">
                    <button @click="moreProducts"
                        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-medium inline-flex items-center"><span><?= $featuredProductsSection['loadMoreButtonText'] ?? 'Load more' ?></span><i
                            data-lucide="chevrons-down" class="w-5 h-5 ml-2"></i></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($categoriesSection['active'])): ?>
            <div class="container mx-auto px-4 py-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white"><?= $categoriesSection['title'] ?></h2>
                    <?php if (!empty($categoriesSection['linkText']) && !empty($categoriesSection['linkUrl'])): ?>
                        <a href="<?= BASE_URL . $categoriesSection['linkUrl'] ?>"
                            class="text-red-600 hover:text-red-700 font-medium inline-flex items-center"><?= $categoriesSection['linkText'] ?><i
                                data-lucide="arrow-right" class="w-5 h-5 ml-1"></i></a>
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
                                    <h3 class="text-white text-xl font-bold mb-2 truncate" :title="c.name" x-text="c.name">
                                    </h3>
                                    <div
                                        class="w-10 h-1 bg-red-600 mb-4 transform transition-all duration-300 group-hover:w-20">
                                    </div>
                                    <div
                                        class="text-white bg-red-600 bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100 inline-flex items-center">
                                        Explore <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></div>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
                <div class="text-center mt-10" x-show="shownCategories < categories.length">
                    <button @click="moreCategories"
                        class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center"><span><?= $categoriesSection['loadMoreButtonText'] ?? 'Load more' ?></span><i
                            data-lucide="chevrons-down" class="w-5 h-5 ml-2"></i></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($partnersSection['active']) && $partnersSection['active'] && !empty($activePartners)): ?>
            <div class="py-8 bg-gray-50 dark:bg-white/5">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4"><?= $partnersSection['title'] ?>
                        </h2>
                        <?php if (!empty($partnersSection['description'])): ?>
                            <p class="text-gray-600 dark:text-white/70 max-w-2xl mx-auto"><?= $partnersSection['description'] ?>
                            </p>
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
                                                    class="partner-card bg-white dark:bg-secondary rounded-lg p-4 md:p-6 shadow-md hover:shadow-lg transition-all duration-300 flex flex-col items-center justify-center h-32 md:h-40">
                                                    <?php if (!empty($partner['logo'])): ?>
                                                        <img src="<?= BASE_URL . $partner['logo'] ?>" alt="<?= $partner['name'] ?>"
                                                            class="h-12 md:h-16 object-contain mb-2 md:mb-4">
                                                    <?php else: ?>
                                                        <img src="https://placehold.co/200x100?text=<?= urlencode($partner['name']) ?>"
                                                            alt="<?= $partner['name'] ?>"
                                                            class="h-12 md:h-16 object-contain mb-2 md:mb-4">
                                                    <?php endif; ?>
                                                    <p
                                                        class="text-center font-medium text-gray-800 dark:text-white text-sm md:text-base">
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
                            class="swiper-button-next partners-next absolute right-0 top-1/2 -translate-y-1/2 bg-white dark:bg-secondary rounded-full shadow-md p-3 z-10 hidden md:flex">
                        </div>
                        <div
                            class="swiper-button-prev partners-prev absolute left-0 top-1/2 -translate-y-1/2 bg-white dark:bg-secondary rounded-full shadow-md p-3 z-10 hidden md:flex">
                        </div>
                    </div>
                    <?php if (!empty($partnersSection['ctaButtonText']) && !empty($partnersSection['ctaButtonUrl'])): ?>
                        <div class="text-center mt-10">
                            <a href="<?= BASE_URL . $partnersSection['ctaButtonUrl'] ?>"
                                class="inline-flex items-center px-6 py-3 border border-primary text-primary font-medium rounded-lg hover:bg-primary hover:text-white transition-colors duration-300"><?= $partnersSection['ctaButtonText'] ?><i
                                    data-lucide="arrow-right" class="w-5 h-5 ml-2"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="md:hidden">
        <div class="swiper hero-slider-m">
            <div class="swiper-wrapper">
                <?php foreach ($activeHeroSlides as $slide): ?>
                    <div class="swiper-slide relative">
                        <div class="hero-aspect-ratio w-full">
                            <?php if (!empty($slide['image'])): ?>
                                <img src="<?= BASE_URL . $slide['image'] ?>" alt="<?= strip_tags($slide['title']) ?>"
                                    class="w-full h-full object-cover">
                            <?php else: ?>
                                <img src="https://placehold.co/1200x675?text=<?= urlencode(strip_tags($slide['title'])) ?>"
                                    alt="<?= strip_tags($slide['title']) ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/70"></div>
                            <div class="hero-layer">
                                <div class="container mx-auto px-4 py-10">
                                    <div class="text-white max-w-3xl">
                                        <div class="text-xl font-bold mb-2 leading-tight"><?= $slide['title'] ?></div>
                                        <div class="text-sm opacity-90 mb-4"><?= $slide['subtitle'] ?></div>
                                        <div x-show="!loggedIn">
                                            <button @click="openHeroLogin" class="hero-cta w-auto"><i
                                                    data-lucide="mouse-pointer-click"
                                                    class="w-5 h-5"></i><?= $slide['buttonText'] ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>

        <?php if (!empty($requestQuoteSection['active'])): ?>
            <div class="px-4 py-4">
                <a href="<?= BASE_URL . $requestQuoteSection['buttonUrl'] ?>" class="rq-cta w-full"><i
                        data-lucide="file-text" class="w-5 h-5"></i><?= $requestQuoteSection['buttonText'] ?></a>
                <?php if (!empty($requestQuoteSection['description'])): ?>
                    <p class="text-center text-gray-600 dark:text-white/70 text-xs mt-2">
                        <?= $requestQuoteSection['description'] ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($featuredProductsSection['active'])): ?>
            <div class="px-4 pt-2">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-base font-semibold text-secondary dark:text-white">
                        <?= $featuredProductsSection['title'] ?>
                    </div>
                    <?php if (!empty($featuredProductsSection['linkText']) && !empty($featuredProductsSection['linkUrl'])): ?>
                        <a href="<?= BASE_URL . $featuredProductsSection['linkUrl'] ?>"
                            class="text-xs text-primary inline-flex items-center"><?= $featuredProductsSection['linkText'] ?><i
                                data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                    <?php endif; ?>
                </div>
                <div class="flex gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory -mx-4 px-4 pb-2">
                    <template x-for="p in products.slice(0, 12)" :key="p.id">
                        <div
                            class="snap-start shrink-0 w-64 rounded-xl border border-gray-200 dark:border白/10 bg-white dark:bg-secondary overflow-hidden flex flex-col">
                            <a :href="'<?= BASE_URL ?>view/product/' + p.id" class="block">
                                <div class="relative">
                                    <img :src="(p.images && p.images[0]) ? p.images[0] : 'https://placehold.co/600x400?text=No+Image'"
                                        :alt="p.title" class="w-full h-40 object-cover">
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
                                <div class="mt-1 text-[11px] text-gray-500 dark:text-white/70 flex items-center"><i
                                        data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i><span
                                        x-text="Number(p.views || 0).toLocaleString()"></span></div>
                                <div class="mt-auto flex flex-col items-center">
                                    <div class="text-sm font-bold text-primary h-5 flex items-center"
                                        x-bind:class="(p.has_pricing && p.lowest_price) ? '' : 'invisible'"
                                        x-text="formatPrice(p.lowest_price)"></div>
                                    <div class="mt-2 flex items-center gap-2 w-full">
                                        <template x-if="p.has_pricing">
                                            <a :href="'<?= BASE_URL ?>view/product/' + p.id + '?action=buy'"
                                                class="flex-1 inline-flex items-center justify-center h-10 rounded-lg bg-emerald-600 text-white text-sm font-medium">Buy</a>
                                        </template>
                                        <button @click="openSell(p)"
                                            class="flex-1 inline-flex items-center justify-center h-10 rounded-lg bg-sky-600 text白 text-sm font-medium">Sell</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeKeyFeatures)): ?>
            <div class="px-4 pt-4">
                <div class="text-base font-semibold text-secondary dark:text-white mb-3">
                    <?= $homepageData['benefitsTitle'] ?? 'Benefits' ?>
                </div>
                <div class="grid grid-cols-1 gap-3">
                    <?php foreach ($activeKeyFeatures as $feature): ?>
                        <div
                            class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-4">
                            <div class="text-2xl shrink-0"><?= $feature['icon'] ?></div>
                            <div>
                                <div class="text-sm font-semibold text-secondary dark:text-white mb-1"><?= $feature['title'] ?>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-white/70"><?= $feature['description'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($categoriesSection['active'])): ?>
            <div class="px-4 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-base font-semibold text-secondary dark:text-white"><?= $categoriesSection['title'] ?>
                    </div>
                    <?php if (!empty($categoriesSection['linkText']) && !empty($categoriesSection['linkUrl'])): ?>
                        <a href="<?= BASE_URL . $categoriesSection['linkUrl'] ?>"
                            class="text-xs text-primary inline-flex items-center"><?= $categoriesSection['linkText'] ?><i
                                data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                    <?php endif; ?>
                </div>
                <div class="flex gap-3 overflow-x-auto hide-scrollbar snap-x snap-mandatory -mx-4 px-4 pb-2">
                    <template x-for="c in categories.slice(0, 12)" :key="c.id">
                        <a :href="'<?= BASE_URL ?>view/category/' + c.id"
                            class="snap-start shrink-0 w-48 rounded-xl overflow-hidden relative">
                            <div class="h-28 w-full relative">
                                <img :src="c.image" :alt="c.name" class="absolute inset-0 w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/35"></div>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-3">
                                <div class="text-white text-sm font-semibold line-clamp-2" x-text="c.name"></div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($partnersSection['active']) && $partnersSection['active'] && !empty($activePartners)): ?>
            <div class="px-4 pt-6 pb-8">
                <div class="text-base font-semibold text-secondary dark:text-white text-center mb-3">
                    <?= $partnersSection['title'] ?>
                </div>
                <div class="swiper partners-slider-m">
                    <div class="swiper-wrapper">
                        <?php foreach (array_chunk($activePartners, 6) as $chunk): ?>
                            <div class="swiper-slide">
                                <div class="grid grid-cols-3 gap-3">
                                    <?php foreach ($chunk as $partner): ?>
                                        <?php
                                        $partnerLink = '#';
                                        $targetAttr = '';
                                        if (isset($partner['hasLink']) && $partner['hasLink'] && !empty($partner['redirectLink'])) {
                                            $partnerLink = $partner['redirectLink'];
                                            $targetAttr = 'target="_blank"';
                                        }
                                        ?>
                                        <a href="<?= $partnerLink ?>" <?= $targetAttr ?>
                                            class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3 flex items-center justify-center">
                                            <?php if (!empty($partner['logo'])): ?>
                                                <img src="<?= BASE_URL . $partner['logo'] ?>" alt="<?= $partner['name'] ?>"
                                                    class="h-8 object-contain">
                                            <?php else: ?>
                                                <img src="https://placehold.co/160x60?text=<?= urlencode($partner['name']) ?>"
                                                    alt="<?= $partner['name'] ?>" class="h-8 object-contain">
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <?php if (!empty($partnersSection['ctaButtonText']) && !empty($partnersSection['ctaButtonUrl'])): ?>
                    <div class="text-center mt-4">
                        <a href="<?= BASE_URL . $partnersSection['ctaButtonUrl'] ?>"
                            class="inline-flex items-center px-4 py-2 border border-primary text-primary rounded-lg text-sm"><?= $partnersSection['ctaButtonText'] ?><i
                                data-lucide="arrow-right" class="w-4 h-4 ml-1"></i></a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
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
            BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
            products: window.__FP__ || [],
            categories: window.__CAT__ || [],
            perRow: window.__FP_PER_ROW__ || 4,
            catPerRow: window.__CAT_PER_ROW__ || 4,
            shownProducts: Math.min((window.__FP_DEFAULT_ROWS__ || 1) * (window.__FP_PER_ROW__ || 4), (window.__FP__ || []).length),
            shownCategories: Math.min((window.__CAT_DEFAULT_ROWS__ || 1) * (window.__CAT_PER_ROW__ || 4), (window.__CAT__ || []).length),
            loggedIn: false,
            init() {
                if (typeof Swiper !== 'undefined') {
                    new Swiper('.hero-slider', { loop: true, autoplay: { delay: 5000, disableOnInteraction: false }, effect: 'fade', fadeEffect: { crossFade: true }, speed: 1000, pagination: { el: '.swiper-pagination', clickable: true }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
                    new Swiper('.partners-slider', { slidesPerView: 1, spaceBetween: 30, loop: true, autoplay: { delay: 7000, disableOnInteraction: true }, speed: 1200, navigation: { nextEl: '.partners-next', prevEl: '.partners-prev' }, breakpoints: { 768: { slidesPerView: 1 } } });
                    new Swiper('.hero-slider-m', { loop: true, autoplay: { delay: 4500, disableOnInteraction: false }, speed: 800, pagination: { el: '.hero-slider-m .swiper-pagination', clickable: true } });
                    new Swiper('.partners-slider-m', { loop: true, autoplay: { delay: 3000, disableOnInteraction: false }, speed: 700, pagination: { el: '.partners-slider-m .swiper-pagination', clickable: true } });
                }
                if (window.lucide && lucide.createIcons) lucide.createIcons();
                this.checkSession().then(s => { this.loggedIn = !!s.logged_in });
                window.addEventListener('zz:session-login', e => { this.loggedIn = true; this.handlePostLogin(e.detail || {}) });
                const pending = window.__pendingVendorAction;
                if (pending && pending.type === 'sell' && pending.product_id && pending.title) {
                    this.resumeSell(pending.product_id, pending.title);
                    window.setPendingVendorAction(null);
                }
            },
            openHeroLogin() { if (typeof openAuthModal === 'function') openAuthModal(); else alert('Please log in to continue.'); },
            moreProducts() { this.shownProducts = Math.min(this.shownProducts + this.perRow, this.products.length); this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); }); },
            moreCategories() { this.shownCategories = Math.min(this.shownCategories + this.catPerRow, this.categories.length); this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); }); },
            formatPrice(price) { if (!price || price <= 0) return ''; return 'UGX ' + Number(price).toLocaleString() + '/='; },
            openSell(p) { this.sellProduct(String(p.id), String(p.title || '')); },
            isAdminUser(payload) { if (!payload) return false; if (payload.is_admin === true) return true; if (payload.user && payload.user.is_admin === true) return true; if (payload.role_slug && (String(payload.role_slug).toLowerCase() === 'admin' || String(payload.role_slug).toLowerCase() === 'super-admin')) return true; if (payload.role && (String(payload.role).toLowerCase() === 'admin' || String(payload.role).toLowerCase() === 'super-admin')) return true; return false; },
            async sellProduct(id, title) { const ok = await this.ensureSession({ type: 'sell', product_id: id, title: title }); if (!ok) return; const s = await this.checkSession().catch(() => ({})); const isAdmin = !!(s && (s.is_admin === true || (s.user && s.user.is_admin === true) || (s.role_slug && String(s.role_slug).toLowerCase() === 'super-admin'))); if (isAdmin) return; this.resumeSell(id, title); },
            resumeSell(id, title) { if (typeof openVendorSellModal === 'function') { openVendorSellModal(id, title); } },
            async handlePostLogin(user) { const a = window.__pendingVendorAction; if (!a) return; if (a.type === 'sell' && a.product_id && a.title) { let isAdmin = this.isAdminUser(user); if (!isAdmin) { try { const s = await this.checkSession(); if (typeof s.is_admin !== 'undefined') isAdmin = !!s.is_admin; else if (s.user && typeof s.user.is_admin !== 'undefined') isAdmin = !!s.user.is_admin; } catch (e) { } } if (!isAdmin) this.resumeSell(a.product_id, a.title); window.setPendingVendorAction(null); } },
            checkSession() { return fetch((this.BASE_URL) + 'fetch/check-session.php', { credentials: 'include' }).then(res => res.json()).then(d => d.success ? d : { logged_in: false }).catch(() => ({ logged_in: false })); },
            async ensureSession(pending) { try { const s = await this.checkSession(); if (!s.logged_in) { if (pending) window.setPendingVendorAction(pending); if (typeof openAuthModal === 'function') openAuthModal(); else alert('Please log in to continue.'); return false; } return true; } catch (e) { return false } }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>