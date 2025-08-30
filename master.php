<?php
require_once __DIR__ . '/config/config.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'data') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        header('Cache-Control: public, max-age=1800');
    }
    try {
        $products = $pdo->query("
            SELECT
                p.id,
                p.title,
                p.description,
                p.meta_title,
                p.meta_description,
                p.meta_keywords,
                p.category_id,
                c.name AS category_name
            FROM products p
            JOIN product_categories c ON c.id = p.category_id
            WHERE p.status = 'published'
            ORDER BY p.title ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $categories = $pdo->query("
            SELECT
                id,
                name,
                description,
                meta_title,
                meta_description,
                meta_keywords
            FROM product_categories
            WHERE status = 'active'
            ORDER BY name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'products' => $products,
            'categories' => $categories,
            'timestamp' => time()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error occurred',
            'products' => [],
            'categories' => []
        ]);
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'image') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';
    if (!$type || !$id) {
        echo json_encode(['error' => 'Missing parameters']);
        exit;
    }
    $basePath = $type === 'product' ? 'img/products/' : 'img/product-categories/';
    $fullPath = __DIR__ . '/' . $basePath . $id . '/';
    if (!is_dir($fullPath)) {
        echo json_encode(['image' => null]);
        exit;
    }
    $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    $images = [];
    $files = scandir($fullPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, $allowedExtensions, true)) {
            $images[] = $file;
        }
    }
    if (empty($images)) {
        echo json_encode(['image' => null]);
        exit;
    }
    $randomImage = $images[array_rand($images)];
    $imageUrl = BASE_URL . $basePath . $id . '/' . $randomImage;
    echo json_encode(['image' => $imageUrl]);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'session') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    $isLoggedIn = isset($_SESSION['user'], $_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
    if ($isLoggedIn) {
        echo json_encode([
            'logged_in' => true,
            'user' => [
                'username' => $_SESSION['user']['username'] ?? '',
                'email' => $_SESSION['user']['email'] ?? '',
                'is_admin' => isset($_SESSION['user']['is_admin']) ? (bool) $_SESSION['user']['is_admin'] : false,
                'last_login' => $_SESSION['user']['last_login'] ?? null
            ]
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
    exit;
}

$title = isset($pageTitle) ? $pageTitle . ' | Buy Online - Deliver On-site' : 'Zzimba Online Uganda | Buy Online - Deliver On-site';
$activeNav = $activeNav ?? 'home';
date_default_timezone_set('Africa/Kampala');
$sessionUlid = generateUlid();
$isLoggedIn = isset($_SESSION['user'], $_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];
$metaDescription = 'Zzimba Online Uganda - Your one-stop shop for construction materials and supplies. Buy online and get delivery on-site.';
$hasSeoTags = isset($seoTags) && is_array($seoTags);
if ($hasSeoTags) {
    $title = $seoTags['title'] ?? $title;
    $metaDescription = $seoTags['description'] ?? $metaDescription;
}
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
$searchQuery = isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">
    <?php if ($hasSeoTags): ?>
        <meta property="og:title" content="<?= htmlspecialchars($seoTags['og_title'] ?? $title) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($seoTags['og_description'] ?? $metaDescription) ?>">
        <meta property="og:image" content="<?= htmlspecialchars($seoTags['og_image'] ?? BASE_URL . 'img/logo_alt.png') ?>">
        <meta property="og:url" content="<?= htmlspecialchars($seoTags['og_url'] ?? $currentUrl) ?>">
        <meta property="og:type" content="<?= htmlspecialchars($seoTags['og_type'] ?? 'website') ?>">
    <?php else: ?>
        <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
        <meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
        <meta property="og:image" content="<?= BASE_URL ?>img/logo_alt.png">
        <meta property="og:url" content="<?= htmlspecialchars($currentUrl) ?>">
        <meta property="og:type" content="website">
    <?php endif; ?>
    <meta property="og:site_name" content="Zzimba Online">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title"
        content="<?= htmlspecialchars($hasSeoTags ? ($seoTags['og_title'] ?? $title) : $title) ?>">
    <meta name="twitter:description"
        content="<?= htmlspecialchars($hasSeoTags ? ($seoTags['og_description'] ?? $metaDescription) : $metaDescription) ?>">
    <meta name="twitter:image"
        content="<?= htmlspecialchars($hasSeoTags ? ($seoTags['og_image'] ?? BASE_URL . 'img/logo_alt.png') : BASE_URL . 'img/logo_alt.png') ?>">
    <link rel="icon" href="<?= BASE_URL ?>img/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VNZ06MKK8N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-VNZ06MKK8N');
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
        window.SESSION_ULID = "<?= $sessionUlid ?>";
        window.ACTIVE_NAV = <?= json_encode($activeNav) ?>;
        window.PAGE_TITLE = <?= json_encode($pageTitle ?? null) ?>;
        window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
        window.SEARCH_QUERY = "<?= $searchQuery ?>";
        const BASE_URL = window.BASE_URL;
        const SESSION_ULID = window.SESSION_ULID;
        const ACTIVE_NAV = window.ACTIVE_NAV;
        const PAGE_TITLE = window.PAGE_TITLE;
        let IS_LOGGED_IN = window.IS_LOGGED_IN;
        const SEARCH_QUERY = window.SEARCH_QUERY;
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#D92B13', secondary: '#1a1a1a' },
                    fontFamily: { rubik: ['Rubik', 'sans-serif'] },
                    zIndex: { 10: '10', 20: '20', 30: '30', 40: '40', 50: '50', 60: '60', 70: '70', 80: '80', 90: '90', 100: '100' }
                }
            }
        }
    </script>
    <script src="<?= BASE_URL ?>track/eventLog.js?v=<?= time() ?>"></script>
    <style>
        .container {
            max-width: 1200px !important;
            margin: 0 auto !important
        }

        .nav-link {
            position: relative;
            font-weight: 600
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: -6px;
            left: 0;
            background-color: #C00000;
            transition: width .25s ease
        }

        .nav-link:hover::after {
            width: 100%
        }

        .dropdown-icon {
            transition: transform .3s ease
        }

        .menu-item:hover .dropdown-icon {
            transform: rotate(180deg)
        }

        .nav-link.active {
            color: #C00000
        }

        .nav-link.active::after {
            width: 100%
        }

        @media (max-width:768px) {
            .mobile-menu {
                display: none
            }

            .mobile-menu.active {
                display: block
            }
        }

        .modal-container {
            transition: opacity .3s ease, transform .3s ease;
            opacity: 0;
            transform: scale(.95)
        }

        .modal-container.active {
            opacity: 1;
            transform: scale(1)
        }

        .auth-form {
            transition: opacity .3s ease, transform .3s ease;
            opacity: 0;
            transform: translateX(20px);
            position: absolute;
            width: 100%
        }

        .auth-form.active {
            opacity: 1;
            transform: translateX(0);
            position: relative
        }

        .toast {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            padding: .75rem 1.5rem;
            border-radius: .375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .1);
            color: #fff;
            font-weight: 500;
            opacity: 0;
            z-index: 10000;
            transition: opacity .3s ease-in-out
        }

        .toast-success {
            background-color: #10B981
        }

        .toast-error {
            background-color: #EF4444
        }

        .toast-show {
            opacity: 1
        }

        .notification-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1100;
            display: flex;
            flex-direction: column-reverse;
            gap: .5rem;
            pointer-events: none
        }

        .notification {
            padding: 1rem;
            border-radius: .5rem;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -1px rgba(0, 0, 0, .06);
            opacity: 0;
            transform: translateY(1rem);
            transition: opacity .3s ease, transform .3s ease;
            pointer-events: auto;
            max-width: 24rem;
            width: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0)
        }

        .notification.success {
            border-left: 4px solid #10B981
        }

        .notification.error {
            border-left: 4px solid #EF4444
        }

        .notification.warning {
            border-left: 4px solid #F59E0B
        }

        .notification.info {
            border-left: 4px solid #3B82F6
        }

        .notification .icon {
            flex-shrink: 0;
            width: 1.5rem;
            height: 1.5rem
        }

        .notification.success .icon {
            color: #10B981
        }

        .notification.error .icon {
            color: #EF4444
        }

        .notification.warning .icon {
            color: #F59E0B
        }

        .notification.info .icon {
            color: #3B82F6
        }

        .notification .content {
            flex-grow: 1
        }

        .notification .title {
            font-weight: 600;
            font-size: .875rem;
            margin-bottom: .25rem
        }

        .notification .message {
            font-size: .875rem;
            color: #4B5563
        }

        .notification .close {
            flex-shrink: 0;
            color: #9CA3AF;
            cursor: pointer;
            padding: .25rem;
            border-radius: .25rem;
            transition: background-color .2s ease
        }

        .notification .close:hover {
            background-color: #F3F4F6
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            height: 100%;
            background-color: #fff;
            transition: left .3s ease;
            z-index: 1000;
            overflow-y: auto
        }

        .mobile-menu.active {
            left: 0
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .5);
            display: none;
            z-index: 999
        }

        .mobile-menu-overlay.active {
            display: block
        }

        .mobile-search-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .7);
            backdrop-filter: blur(5px);
            display: none;
            z-index: 1200;
            padding: 1rem;
            align-items: flex-start;
            justify-content: center;
            padding-top: 2rem
        }

        .mobile-search-modal.active {
            display: flex
        }

        .mobile-search-container {
            width: 100%;
            max-width: 500px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, .1), 0 10px 10px -5px rgba(0, 0, 0, .04);
            overflow: hidden;
            position: relative
        }

        .mobile-search-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between
        }

        .mobile-search-form {
            padding: 1rem;
            position: relative
        }

        .mobile-search-input {
            width: 100%;
            padding: .75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: .5rem;
            font-size: 1rem;
            outline: none;
            transition: border-color .2s ease
        }

        .mobile-search-input:focus {
            border-color: #D92B13;
            box-shadow: 0 0 0 3px rgba(217, 43, 19, .1)
        }

        .mobile-search-dropdown {
            max-height: 400px;
            overflow-y: auto;
            border-top: 1px solid #e5e7eb
        }

        ::-webkit-scrollbar {
            width: 3px;
            height: 3px
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgb(0, 0, 0);
            border-radius: 3px
        }

        ::-webkit-scrollbar-track {
            background: transparent
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(135, 135, 135) transparent
        }

        #scroll-to-top {
            z-index: 1000
        }

        #scroll-to-top.visible {
            opacity: 1;
            visibility: visible
        }

        .scroll-indicator {
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset .2s ease
        }

        .ad-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, .8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity .3s ease, visibility .3s ease
        }

        .ad-popup.show {
            opacity: 1;
            visibility: visible
        }

        .ad-content {
            position: relative;
            max-width: 90%;
            max-height: 90%
        }

        .ad-image {
            width: 100%;
            height: auto;
            max-height: 60vh;
            object-fit: contain
        }

        .ad-close {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, .8);
            color: #000;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color .3s ease
        }

        .ad-close:hover {
            background-color: #fff
        }

        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 4px;
            background-color: #C00000;
            width: 0;
            transition: width 5s linear
        }

        .password-strength-meter {
            height: 5px;
            background-color: #ddd;
            border-radius: 3px;
            margin-top: 5px;
            position: relative;
            overflow: hidden
        }

        .password-strength-meter-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .3s ease, background-color .3s ease
        }

        .password-strength-text {
            font-size: .75rem;
            margin-top: 5px
        }

        .strength-weak .password-strength-meter-fill {
            background-color: #ef4444;
            width: 25%
        }

        .strength-fair .password-strength-meter-fill {
            background-color: #f59e0b;
            width: 50%
        }

        .strength-good .password-strength-meter-fill {
            background-color: #3b82f6;
            width: 75%
        }

        .strength-strong .password-strength-meter-fill {
            background-color: #10b981;
            width: 100%
        }

        .iti {
            width: 100%
        }

        .otp-input {
            width: 40px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem
        }

        @media (min-width:640px) {
            .otp-input {
                width: 50px;
                height: 60px
            }
        }

        .user-dropdown {
            position: relative
        }

        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 240px;
            background-color: #fff;
            border-radius: .5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
            padding: .5rem 0;
            z-index: 50;
            display: none
        }

        .user-dropdown:hover .user-dropdown-menu {
            display: block
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: .5rem 1rem;
            color: #1a1a1a;
            transition: background-color .2s ease
        }

        .user-dropdown-item:hover {
            background-color: #f3f4f6;
            color: #D92B13
        }

        .user-dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: .25rem 0
        }

        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 400px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: .5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
            z-index: 60;
            display: none
        }

        .search-dropdown.show {
            display: block
        }

        .search-dropdown-item {
            padding: .75rem 1rem;
            cursor: pointer;
            transition: background-color .2s ease;
            border-bottom: 1px solid #f3f4f6
        }

        .search-dropdown-item:hover {
            background-color: #f9fafb
        }

        .search-dropdown-item:last-child {
            border-bottom: none
        }

        .search-dropdown-header {
            padding: .5rem 1rem;
            font-size: .75rem;
            font-weight: 600;
            color: #6b7280;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(217, 43, 19, .2)
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

        .search-image {
            transition: opacity .3s ease
        }

        .search-image.loading {
            opacity: .5
        }
    </style>
</head>

<body class="font-rubik min-h-screen flex flex-col">
    <div class="bg-secondary text-white text-sm py-2 hidden md:block">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span class="flex items-center"><i data-lucide="mail"
                        class="w-4 h-4 mr-2"></i>halo@zzimbaonline.com</span>
                <span class="flex items-center"><i data-lucide="phone" class="w-4 h-4 mr-2"></i>+256 392 003-406</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-primary transition-colors flex items-center"><i data-lucide="truck"
                        class="w-4 h-4 mr-2"></i>Delivery Info</a>
                <a href="<?= BASE_URL ?>terms-and-conditions"
                    class="hover:text-primary transition-colors flex items-center"><i data-lucide="file-text"
                        class="w-4 h-4 mr-2"></i>Terms &amp; Conditions</a>
            </div>
        </div>
    </div>

    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-[auto_1fr_auto] items-center gap-4 py-3">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>">
                        <img src="<?= BASE_URL ?>img/logo_alt.png?height=40&width=150" alt="Logo" class="h-10">
                    </a>
                </div>

                <div id="nav-column" class="hidden md:flex flex-col items-center justify-center justify-self-center">
                    <div id="desktop-nav" class="flex items-center justify-center space-x-8 mb-2"></div>
                    <form id="desktop-search-bar" class="mt-2 flex items-stretch" style="width:auto">
                        <div class="relative w-full">
                            <input type="text" id="desktop-search-input" placeholder="Search for products..."
                                class="px-4 border-2 border-gray-300 rounded-l-lg h-11 focus:outline-none search-input w-full text-[15px] md:text-base"
                                autocomplete="off">
                            <div id="desktop-search-dropdown" class="search-dropdown"></div>
                        </div>
                        <button id="desktop-search-button"
                            class="bg-primary text-white px-5 rounded-r-lg hover:bg-red-600 transition-colors h-11 flex items-center justify-center">
                            <i data-lucide="search" class="w-5 h-5"></i>
                            <span class="sr-only">Search</span>
                        </button>
                    </form>
                </div>

                <div class="flex items-center justify-self-end">
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="#" class="text-secondary hover:text-primary transition-colors flex items-center">
                            <i data-lucide="shopping-cart" class="w-6 h-6" stroke-width="2.5"></i>
                        </a>
                        <div id="auth-section">
                            <?php if (isset($_SESSION['user'], $_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in']): ?>
                                <div class="user-dropdown">
                                    <button
                                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                                        <i data-lucide="user" class="w-5 h-5 mr-2" stroke-width="2.5"></i>Halo
                                        <?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>!
                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                    </button>
                                    <div class="user-dropdown-menu">
                                        <div class="px-4 py-3 bg-gray-50">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>
                                            </p>
                                            <?php if (isset($_SESSION['user']['last_login']) && $_SESSION['user']['last_login']): ?>
                                                <p class="text-xs text-gray-500 mt-1">Last login:
                                                    <?= date('M d, Y g:i A', strtotime($_SESSION['user']['last_login'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($_SESSION['user']['is_admin'])): ?>
                                            <a href="<?= BASE_URL ?>admin/dashboard" class="user-dropdown-item">
                                                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>Dashboard
                                            </a>
                                            <a href="<?= BASE_URL ?>admin/profile" class="user-dropdown-item">
                                                <i data-lucide="user-round" class="w-4 h-4 mr-2"></i>Profile
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>account/dashboard" class="user-dropdown-item">
                                                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>Dashboard
                                            </a>
                                            <a href="<?= BASE_URL ?>account/profile" class="user-dropdown-item">
                                                <i data-lucide="user-round" class="w-4 h-4 mr-2"></i>Profile
                                            </a>
                                        <?php endif; ?>
                                        <div class="border-t border-gray-200 my-1"></div>
                                        <a href="javascript:void(0);" onclick="logoutUser(); return false;"
                                            class="user-dropdown-item text-red-600">
                                            <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>Sign Out
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a href="#" onclick="openAuthModal(); return false;"
                                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                                    <i data-lucide="user" class="w-5 h-5 mr-2" stroke-width="2.5"></i>Login / Register
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex md:hidden items-center gap-0">
                        <button id="mobile-search-toggle"
                            class="text-secondary hover:text-primary transition-colors p-2">
                            <i data-lucide="search" class="w-6 h-6"></i>
                        </button>
                        <button class="text-secondary hover:text-primary p-2" id="mobile-menu-button">
                            <i data-lucide="menu" class="w-7 h-7"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="mobile-search-modal" class="mobile-search-modal">
        <div class="mobile-search-container">
            <div class="mobile-search-header">
                <h3 class="text-lg font-semibold text-gray-900">Search Products</h3>
                <button id="close-mobile-search" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <div class="mobile-search-form">
                <div class="flex">
                    <input type="text" id="mobile-search-input" placeholder="Search for products..."
                        class="mobile-search-input flex-1" autocomplete="off"
                        style="border-radius: 0.5rem 0 0 0.5rem; border-right: none;">
                    <button id="mobile-search-button"
                        class="bg-primary text-white px-4 py-3 hover:bg-red-600 transition-colors"
                        style="border-radius: 0 0.5rem 0.5rem 0; border: 2px solid #D92B13;">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div id="mobile-search-dropdown" class="mobile-search-dropdown" style="display: none;"></div>
        </div>
    </div>

    <?php include __DIR__ . '/vendor-sell.php'; ?>

    <div class="mobile-menu-overlay"></div>
    <div class="mobile-menu bg-white p-4">
        <div class="flex justify-between items-center mb-4">
            <img src="<?= BASE_URL ?>img/logo_alt.png?height=40&width=150" alt="Logo" class="h-8">
            <button id="close-mobile-menu" class="text-secondary hover:text-primary">
                <i data-lucide="x" class="w-7 h-7"></i>
            </button>
        </div>
        <div id="mobile-menu-items" class="space-y-4"></div>
        <div class="mt-6 space-y-4" id="mobile-auth-section">
            <a href="#"
                class="block text-center bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
                <i data-lucide="shopping-cart" class="w-5 h-5 mr-2 inline-block"></i>Cart
            </a>
            <?php if (isset($_SESSION['user'], $_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in']): ?>
                <?php if (!empty($_SESSION['user']['is_admin'])): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard"
                        class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 mr-2 inline-block"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>account/dashboard"
                        class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 mr-2 inline-block"></i>Dashboard
                    </a>
                <?php endif; ?>
                <a href="javascript:void(0);" onclick="logoutUser(); return false;"
                    class="block text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5 mr-2 inline-block"></i>Logout
                </a>
            <?php else: ?>
                <a href="#" onclick="openAuthModal(); return false;"
                    class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors"
                    id="mobile-login-button">
                    <i data-lucide="user" class="w-5 h-5 mr-2 inline-block"></i>Login / Register
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="auth-modal" style="z-index:1100"
        class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center hidden">
        <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div id="auth-forms-container"></div>
        </div>
    </div>

    <div class="main-area flex-grow">
        <?= $mainContent ?? '' ?>
    </div>

    <footer class="bg-secondary text-white mt-auto">
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h4 class="text-lg font-bold mb-4">Join Us</h4>
                    <p class="text-gray-400">Be a part of something special. Whether you're a buyer looking for unique
                        products or a vendor aiming to expand your business, Zzimba Online is the place for you.</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= BASE_URL ?>/about-us" class="text-gray-400 hover:text-primary">About Us</a>
                        </li>
                        <li><a href="<?= BASE_URL ?>/materials-yard"
                                class="text-gray-400 hover:text-primary">Materials</a></li>
                        <li><a href="<?= BASE_URL ?>/contact-us" class="text-gray-400 hover:text-primary">Contact</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Our Services</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>Product Profiling</li>
                        <li>Delivery Fulfilment</li>
                        <li>Construction Marketing</li>
                        <li>Procurement Support</li>
                        <li>Payment Processing</li>
                        <li>Supply Chain Optimization</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>📍 Plaza Building Luzira</li>
                        <li>📱 The Engineering Marksmen Ltd.</li>
                        <li>📧 P.O Box 129572 Kampala - Uganda</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800">
            <div class="container mx-auto px-4 py-6 text-center text-gray-400">
                © <span id="currentYear"></span> Zzimba Online. All rights reserved.
            </div>
        </div>
    </footer>

    <div id="notification-container" class="notification-container"></div>

    <button id="scroll-to-top"
        class="fixed bottom-6 right-6 bg-primary hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition-all duration-300 opacity-0 invisible"
        aria-label="Scroll to top">
        <svg class="w-7 h-7" viewBox="0 0 36 36">
            <path class="scroll-circle"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                stroke="rgba(255,255,255,0.2)" stroke-width="3" />
            <path class="scroll-indicator"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                stroke="#fff" stroke-width="3" />
        </svg>
        <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <i data-lucide="arrow-up" class="w-4 h-4"></i>
        </span>
    </button>

    <div id="adPopup" class="ad-popup">
        <div class="ad-content">
            <div class="progress-bar" id="adProgressBar"></div>
            <img src="https://placehold.co/800x450" alt="Advertisement" class="ad-image">
            <button id="adClose" class="ad-close" style="display:none">&times;</button>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="<?php echo BASE_URL; ?>js/google-analytics.js"></script>
    <script>
        let SEARCH_DATA = { products: [], categories: [] };
        let fuseProducts = null, fuseCategories = null, fuseWords = null;
        let searchInitialized = false;
        let imageCache = new Map();
        const LOGGED_USER = <?= isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null'; ?>;

        async function checkUserSession() {
            try {
                const response = await fetch(`${BASE_URL}fetch/check-session.php`);
                const data = await response.json();
                if (data.success) {
                    IS_LOGGED_IN = data.logged_in;
                    if (data.logged_in && data.user) { window.currentUser = data.user; }
                    return data.logged_in;
                }
                return false;
            } catch (error) { return false; }
        }

        function formatNumber(num) { return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","); }

        function ld(a, b) {
            if (a === b) return 0;
            if (!a.length || !b.length) return Math.max(a.length, b.length);
            const v = Array(b.length + 1).fill(0).map((_, i) => i);
            for (let i = 0; i < a.length; i++) {
                let prev = i + 1;
                for (let j = 0; j < b.length; j++) {
                    const val = a[i] === b[j] ? v[j] : Math.min(v[j], v[j + 1], prev) + 1;
                    v[j] = prev; prev = val
                }
                v[b.length] = prev
            }
            return v[b.length]
        }

        function getImageUrl(type, id) {
            const cacheKey = `${type}_${id}`;
            if (imageCache.has(cacheKey)) { return Promise.resolve(imageCache.get(cacheKey)); }
            return fetch(`${window.location.href}?ajax=image&type=${type}&id=${id}`)
                .then(r => r.json())
                .then(data => { const imageUrl = data.image || `https://placehold.co/60x60?text=No+Image`; imageCache.set(cacheKey, imageUrl); return imageUrl; })
                .catch(() => { const fallbackUrl = `https://placehold.co/60x60?text=No+Image`; imageCache.set(cacheKey, fallbackUrl); return fallbackUrl; });
        }

        function loadSearchData() {
            const cachedData = localStorage.getItem('zzimba_search_data');
            const cacheTimestamp = localStorage.getItem('zzimba_search_data_timestamp');
            const now = Date.now();
            const cacheAge = now - (cacheTimestamp ? parseInt(cacheTimestamp) : 0);
            const maxCacheAge = 30 * 60 * 1000;
            if (cachedData && cacheAge < maxCacheAge) {
                try {
                    const parsedData = JSON.parse(cachedData);
                    if (parsedData && parsedData.products && Array.isArray(parsedData.products) && parsedData.categories && Array.isArray(parsedData.categories)) {
                        SEARCH_DATA = parsedData; buildSearchIndexes(); searchInitialized = true; return Promise.resolve();
                    } else {
                        localStorage.removeItem('zzimba_search_data'); localStorage.removeItem('zzimba_search_data_timestamp');
                    }
                } catch (e) {
                    localStorage.removeItem('zzimba_search_data'); localStorage.removeItem('zzimba_search_data_timestamp');
                }
            }
            return fetch(window.location.href + '?ajax=data')
                .then(r => r.json())
                .then(data => {
                    if (data && data.products && Array.isArray(data.products) && data.categories && Array.isArray(data.categories)) {
                        SEARCH_DATA = data; localStorage.setItem('zzimba_search_data', JSON.stringify(data)); localStorage.setItem('zzimba_search_data_timestamp', now.toString()); buildSearchIndexes(); searchInitialized = true;
                    } else { SEARCH_DATA = { products: [], categories: [] }; searchInitialized = false; }
                })
                .catch(() => { SEARCH_DATA = { products: [], categories: [] }; searchInitialized = false; });
        }

        function buildSearchIndexes() {
            if (!window.Fuse) { setTimeout(buildSearchIndexes, 100); return; }
            try {
                if (!SEARCH_DATA || !SEARCH_DATA.products || !Array.isArray(SEARCH_DATA.products) || !SEARCH_DATA.categories || !Array.isArray(SEARCH_DATA.categories)) {
                    fuseProducts = null; fuseCategories = null; fuseWords = null; searchInitialized = false; return;
                }
                fuseProducts = new Fuse(SEARCH_DATA.products.map(p => ({ ...p })), {
                    includeScore: true, threshold: 0.4, ignoreLocation: true, keys: [
                        { name: 'title', weight: 0.4 }, { name: 'meta_title', weight: 0.3 }, { name: 'description', weight: 0.2 }, { name: 'meta_description', weight: 0.2 }, { name: 'meta_keywords', weight: 0.2 }, { name: 'category_name', weight: 0.1 }
                    ]
                });
                fuseCategories = new Fuse(SEARCH_DATA.categories.map(c => ({ ...c })), {
                    includeScore: true, threshold: 0.4, ignoreLocation: true, keys: [
                        { name: 'name', weight: 0.5 }, { name: 'meta_title', weight: 0.3 }, { name: 'description', weight: 0.2 }, { name: 'meta_description', weight: 0.2 }, { name: 'meta_keywords', weight: 0.2 }
                    ]
                });
                const bag = new Set(); const tokenize = s => s.toLowerCase().split(/\W+/).filter(w => w.length > 2);
                SEARCH_DATA.products.forEach(p => { [...tokenize(p.title || ''), ...tokenize(p.description || ''), ...tokenize(p.meta_title || ''), ...tokenize(p.meta_description || ''), ...tokenize(p.meta_keywords || '')].forEach(w => bag.add(w)); });
                SEARCH_DATA.categories.forEach(c => { [...tokenize(c.name || ''), ...tokenize(c.description || ''), ...tokenize(c.meta_title || ''), ...tokenize(c.meta_description || ''), ...tokenize(c.meta_keywords || '')].forEach(w => bag.add(w)); });
                fuseWords = new Fuse([...bag].map(w => ({ word: w })), { keys: ['word'], includeScore: true, threshold: 0.4, distance: 60 });
                searchInitialized = true;
            } catch (error) {
                fuseProducts = null; fuseCategories = null; fuseWords = null; searchInitialized = false;
            }
        }

        async function renderSearchDropdown(query, dropdownElement) {
            query = query.trim().toLowerCase();
            if (!query || !fuseProducts || !fuseCategories || !fuseWords || !searchInitialized) { dropdownElement.style.display = 'none'; return; }
            try {
                const suggestions = fuseWords.search(query, { limit: 5 }).map(x => x.item.word).filter(w => w !== query);
                const productResults = fuseProducts.search(query, { limit: 8 });
                const categoryResults = fuseCategories.search(query, { limit: 5 });
                let html = '';
                if (suggestions.length) {
                    html += '<div class="search-dropdown-header">Suggestions</div>';
                    suggestions.forEach(word => {
                        html += `
                        <div class="search-dropdown-item suggestion flex items-center" data-word="${escapeHtml(word)}">
                            <i data-lucide="search" class="w-4 h-4 text-gray-400 mr-2"></i>${escapeHtml(word)}
                        </div>`;
                    });
                }
                if (productResults.length) {
                    if (html) html += '<div class="border-t border-gray-200 my-1"></div>';
                    html += '<div class="search-dropdown-header">Products</div>';
                    for (const result of productResults) {
                        const product = result.item;
                        html += `
                            <a href="${BASE_URL}view/product/${product.id}" class="search-dropdown-item flex items-center" data-type="product" data-id="${product.id}" data-label="${escapeHtml(product.title)}">
                                <img src="https://placehold.co/40x40?text=Loading..." alt="Product" class="w-10 h-10 rounded mr-3 flex-shrink-0 object-cover search-image loading" data-type="product" data-id="${product.id}">
                                <div>
                                    <div class="font-medium text-sm">${escapeHtml(product.title)}</div>
                                    <div class="text-xs text-gray-500">${escapeHtml(product.category_name)}</div>
                                </div>
                            </a>`;
                    }
                }
                if (categoryResults.length) {
                    if (html) html += '<div class="border-t border-gray-200 my-1"></div>';
                    html += '<div class="search-dropdown-header">Categories</div>';
                    for (const result of categoryResults) {
                        const category = result.item;
                        html += `
                            <a href="${BASE_URL}view/category/${category.id}" class="search-dropdown-item flex items-center" data-type="category" data-id="${category.id}" data-label="${escapeHtml(category.name)}">
                                <img src="https://placehold.co/40x40?text=Loading..." alt="Category" class="w-10 h-10 rounded mr-3 flex-shrink-0 object-cover search-image loading" data-type="category" data-id="${category.id}">
                                <div>
                                    <div class="font-medium text-sm">${escapeHtml(category.name)}</div>
                                    <div class="text-xs text-gray-500">Browse category</div>
                                </div>
                            </a>`;
                    }
                }
                if (html) {
                    dropdownElement.innerHTML = html; dropdownElement.style.display = 'block';
                    const images = dropdownElement.querySelectorAll('.search-image.loading');
                    images.forEach(async (img) => {
                        const type = img.dataset.type; const id = img.dataset.id;
                        try { const imageUrl = await getImageUrl(type, id); img.src = imageUrl; img.classList.remove('loading'); }
                        catch (e) { img.src = 'https://placehold.co/40x40?text=No+Image'; img.classList.remove('loading'); }
                    });
                    if (window.lucide && lucide.createIcons) lucide.createIcons();
                } else { dropdownElement.style.display = 'none'; }
            } catch (error) { dropdownElement.style.display = 'none'; }
        }

        function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

        function debounce(func, wait) { let timeout; return function executedFunction(...args) { const later = () => { clearTimeout(timeout); func(...args); }; clearTimeout(timeout); timeout = setTimeout(later, wait); }; }

        function initializeSearch() {
            const desktopSearchInput = document.getElementById('desktop-search-input');
            const desktopSearchDropdown = document.getElementById('desktop-search-dropdown');
            const desktopSearchButton = document.getElementById('desktop-search-button');
            const mobileSearchInput = document.getElementById('mobile-search-input');
            const mobileSearchDropdown = document.getElementById('mobile-search-dropdown');
            if (SEARCH_QUERY) { if (desktopSearchInput) desktopSearchInput.value = SEARCH_QUERY; if (mobileSearchInput) mobileSearchInput.value = SEARCH_QUERY; }
            if (desktopSearchInput && searchInitialized) {
                desktopSearchInput.addEventListener('input', debounce((e) => { renderSearchDropdown(e.target.value, desktopSearchDropdown); }, 200));
                desktopSearchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); const query = desktopSearchInput.value.trim(); if (query) { window.location.href = BASE_URL + 'materials-yard?s=' + encodeURIComponent(query); } } });
                desktopSearchDropdown.addEventListener('click', (e) => { const item = e.target.closest('.search-dropdown-item'); if (!item) return; if (item.classList.contains('suggestion')) { desktopSearchInput.value = item.dataset.word; renderSearchDropdown(item.dataset.word, desktopSearchDropdown); e.preventDefault(); } });
                if (desktopSearchButton) { desktopSearchButton.addEventListener('click', (e) => { e.preventDefault(); const query = desktopSearchInput.value.trim(); if (query) { window.location.href = BASE_URL + 'materials-yard?s=' + encodeURIComponent(query); } }); }
            }
            if (mobileSearchInput && searchInitialized) {
                mobileSearchInput.addEventListener('input', debounce((e) => { renderSearchDropdown(e.target.value, mobileSearchDropdown); }, 200));
                mobileSearchInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') { e.preventDefault(); const query = mobileSearchInput.value.trim(); if (query) { window.location.href = BASE_URL + 'materials-yard?s=' + encodeURIComponent(query); } } });
                mobileSearchDropdown.addEventListener('click', (e) => { const item = e.target.closest('.search-dropdown-item'); if (!item) return; if (item.classList.contains('suggestion')) { mobileSearchInput.value = item.dataset.word; renderSearchDropdown(item.dataset.word, mobileSearchDropdown); e.preventDefault(); } });
                const mobileSearchButton = document.getElementById('mobile-search-button');
                if (mobileSearchButton) { mobileSearchButton.addEventListener('click', (e) => { e.preventDefault(); const query = mobileSearchInput.value.trim(); if (query) { window.location.href = BASE_URL + 'materials-yard?s=' + encodeURIComponent(query); } }); }
            }
            document.addEventListener('click', (e) => { if (!desktopSearchInput?.contains(e.target) && !desktopSearchDropdown?.contains(e.target)) { desktopSearchDropdown?.classList.remove('show'); } });
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type === 'success' ? 'toast-success' : 'toast-error'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => { toast.classList.add('toast-show'); });
            setTimeout(() => { toast.classList.remove('toast-show'); setTimeout(() => { toast.remove(); }, 300); }, 5000);
        }

        class NotificationSystem {
            constructor() { this.container = document.getElementById('notification-container'); this.notifications = new Map(); this.counter = 0; }
            show(o) { const { type = 'info', title, message, duration = 5000 } = o; const toast = document.createElement('div'); toast.className = `toast ${type === 'success' ? 'toast-success' : 'toast-error'}`; toast.textContent = message; document.body.appendChild(toast); requestAnimationFrame(() => { toast.classList.add('toast-show'); }); const id = this.counter++; this.notifications.set(id, toast); if (duration > 0) { setTimeout(() => this.close(id), duration); } return id; }
            close(id) { const toast = this.notifications.get(id); if (toast) { toast.classList.remove('toast-show'); setTimeout(() => { toast.remove(); this.notifications.delete(id); }, 300); } }
            success(m, t = 'Success') { return this.show({ type: 'success', title: t, message: m }); }
            error(m, t = 'Error') { return this.show({ type: 'error', title: t, message: m }); }
            warning(m, t = 'Warning') { return this.show({ type: 'warning', title: t, message: m }); }
            info(m, t = 'Info') { return this.show({ type: 'info', title: t, message: m }); }
        }
        const notifications = new NotificationSystem();

        function checkPasswordStrength(p, i = 'register-password') {
            const m = document.querySelector('#' + i).closest('div').nextElementSibling;
            const f = m.querySelector('.password-strength-meter-fill');
            const t = m.nextElementSibling;
            m.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            if (!p) { f.style.width = '0'; t.textContent = ''; return; }
            let s = 0; if (p.length >= 8) s++; if (p.length >= 12) s++; if (/[A-Z]/.test(p)) s++; if (/[a-z]/.test(p)) s++; if (/[0-9]/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++;
            let l = '', c = '';
            if (s < 3) { l = 'Weak'; c = 'strength-weak'; }
            else if (s < 4) { l = 'Fair'; c = 'strength-fair'; }
            else if (s < 6) { l = 'Good'; c = 'strength-good'; }
            else { l = 'Strong'; c = 'strength-strong'; }
            m.classList.add(c); t.textContent = 'Password strength: ' + l;
        }

        function togglePasswordVisibility(i) {
            const e = document.getElementById(i);
            const b = e.nextElementSibling;
            if (e.type === 'password') { e.type = 'text'; b.innerHTML = '<i data-lucide="eye-off" class="w-5 h-5"></i>'; }
            else { e.type = 'password'; b.innerHTML = '<i data-lucide="eye" class="w-5 h-5"></i>'; }
            if (window.lucide && lucide.createIcons) lucide.createIcons();
        }

        function isValidEmail(email) { const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; return re.test(email); }
        function isStrongPassword(password) { return (password.length >= 8 && /[A-Z]/.test(password) && /[a-z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)); }
        function generateOTP(length = 6) { let otp = ''; for (let i = 0; i < length; i++) { otp += Math.floor(Math.random() * 10).toString(); } return otp; }

        function updateUIAfterLogin(userData) {
            IS_LOGGED_IN = true;
            const desktopAuthSection = document.getElementById('auth-section');
            const mobileAuthSection = document.getElementById('mobile-auth-section');
            const dashboardUrl = userData.is_admin ? BASE_URL + 'admin/dashboard' : BASE_URL + 'account/dashboard';
            const profileUrl = userData.is_admin ? BASE_URL + 'admin/profile' : BASE_URL + 'account/profile';
            const lastLoginText = userData.last_login ? `Last login: ${new Date(userData.last_login).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })}` : '';
            if (desktopAuthSection) {
                desktopAuthSection.innerHTML = `
                    <div class="user-dropdown">
                        <button class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2"></i>Halo ${userData.username}!
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <div class="px-4 py-3 bg-gray-50">
                                <p class="text-sm font-medium text-gray-900">${userData.username}</p>
                                <p class="text-xs text-gray-500">${userData.email}</p>
                                ${lastLoginText ? `<p class="text-xs text-gray-500 mt-1">${lastLoginText}</p>` : ''}
                            </div>
                            <a href="${dashboardUrl}" class="user-dropdown-item">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>Dashboard
                            </a>
                            <a href="${profileUrl}" class="user-dropdown-item">
                                <i data-lucide="user-round" class="w-4 h-4 mr-2"></i>Profile
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="user-dropdown-item text-red-600">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>Sign Out
                            </a>
                        </div>
                    </div>`;
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            }
            if (mobileAuthSection) {
                mobileAuthSection.innerHTML = `
                    <a href="#" class="block text-center bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        <i data-lucide="shopping-cart" class="w-5 h-5 mr-2 inline-block"></i>Cart
                    </a>
                    <a href="${dashboardUrl}" class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 mr-2 inline-block"></i>Dashboard
                    </a>
                    <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="block text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5 mr-2 inline-block"></i>Logout
                    </a>`;
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            }
        }

        function checkSessionStatus() {
            return fetch(BASE_URL + '?ajax=session')
                .then(r => r.json())
                .then(data => {
                    if (data.logged_in && !IS_LOGGED_IN) { updateUIAfterLogin(data.user); return true; }
                    return data.logged_in;
                })
                .catch(() => { return false; });
        }

        function startSessionMonitoring() {
            const originalCloseAuthModal = window.closeAuthModal;
            window.closeAuthModal = function () {
                originalCloseAuthModal();
                setTimeout(() => {
                    checkSessionStatus().then(isLoggedIn => { if (isLoggedIn) { notifications.success('Welcome! You are now logged in.'); } });
                }, 500);
            };
        }

        document.getElementById("currentYear").textContent = new Date().getFullYear();
        const activeNavKey = "<?= $activeNav ?>";
        const navItems = {
            home: { icon: 'home', title: 'Home', url: BASE_URL },
            about: { icon: 'building-2', title: 'About Us', url: BASE_URL + 'about-us' },
            materials: { icon: 'package', title: 'Materials Yard', url: BASE_URL + 'materials-yard' },
            contact: { icon: 'mail', title: 'Contact Us', url: BASE_URL + 'contact-us' }
        };

        function generateNavigation(i) {
            let h = '';
            for (const [k, v] of Object.entries(i)) {
                const a = k === activeNavKey ? 'active' : '';
                h += `<a href="${v.url}" class="nav-link text-secondary hover:text-primary transition-colors ${a} flex items-center text-[15px] md:text-[16px]"><i data-lucide="${v.icon}" class="w-5 h-5 mr-2" stroke-width="2.5"></i>${v.title}</a>`;
            }
            return h;
        }

        function generateMobileNavigation(i) {
            let h = '';
            for (const [k, v] of Object.entries(i)) {
                const a = k === activeNavKey ? 'active' : '';
                h += `<a href="${v.url}" class="block py-2 px-4 text-secondary hover:text-primary hover:bg-gray-50 ${a} flex items-center"><i data-lucide="${v.icon}" class="w-5 h-5 mr-2"></i>${v.title}</a>`;
            }
            return h;
        }

        function adjustSearchWidth() {
            const nav = document.getElementById('desktop-nav');
            const form = document.getElementById('desktop-search-bar');
            if (nav && form) {
                const width = Math.ceil(nav.getBoundingClientRect().width);
                form.style.width = (width > 0 ? width : 0) + 'px';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('desktop-nav').innerHTML = generateNavigation(navItems);
            document.getElementById('mobile-menu-items').innerHTML = generateMobileNavigation(navItems);
            if (window.lucide && lucide.createIcons) lucide.createIcons();

            adjustSearchWidth();
            window.addEventListener('resize', adjustSearchWidth);
            const ro = ('ResizeObserver' in window) ? new ResizeObserver(adjustSearchWidth) : null;
            if (ro) { ro.observe(document.getElementById('desktop-nav')); }

            const userDropdownButton = document.getElementById('user-dropdown-button');
            if (userDropdownButton) {
                const userDropdownMenu = document.querySelector('.user-dropdown-menu');
                userDropdownButton.addEventListener('click', function () { userDropdownMenu.classList.toggle('active'); });
                document.addEventListener('click', function (e) { if (!userDropdownButton.contains(e.target) && !userDropdownMenu.contains(e.target)) { userDropdownMenu.classList.remove('active'); } });
            }

            loadSearchData().then(() => { if (searchInitialized) { initializeSearch(); } });
            startSessionMonitoring();
        });

        const mobileSearchToggle = document.getElementById('mobile-search-toggle');
        const mobileSearchModal = document.getElementById('mobile-search-modal');
        const closeMobileSearch = document.getElementById('close-mobile-search');

        function openMobileSearch() {
            mobileSearchModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const searchInput = document.getElementById('mobile-search-input');
            setTimeout(() => searchInput.focus(), 100);
        }
        function closeMobileSearchModal() {
            mobileSearchModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('mobile-search-dropdown').style.display = 'none';
        }

        mobileSearchToggle.addEventListener('click', openMobileSearch);
        closeMobileSearch.addEventListener('click', closeMobileSearchModal);
        mobileSearchModal.addEventListener('click', (e) => { if (e.target === mobileSearchModal) { closeMobileSearchModal(); } });

        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
        const closeMobileMenuButton = document.getElementById('close-mobile-menu');

        function openMobileMenu() { mobileMenu.classList.add('active'); mobileMenuOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
        function closeMobileMenu() { mobileMenu.classList.remove('active'); mobileMenuOverlay.classList.remove('active'); document.body.style.overflow = 'auto'; }
        mobileMenuButton.addEventListener('click', openMobileMenu);
        closeMobileMenuButton.addEventListener('click', closeMobileMenu);

        function openAuthModal() {
            const m = document.getElementById('auth-modal');
            const c = m.querySelector('.modal-container');
            m.classList.remove('hidden'); m.classList.add('flex'); m.style.display = 'flex'; c.offsetHeight; c.classList.add('active'); document.body.style.overflow = 'hidden';
        }

        function closeAuthModal() {
            const m = document.getElementById('auth-modal'); const c = m.querySelector('.modal-container');
            c.classList.remove('active'); setTimeout(() => { m.classList.remove('flex'); m.classList.add('hidden'); m.style.display = ''; document.body.style.overflow = 'auto'; }, 300);
        }

        const scrollToTopBtn = document.getElementById('scroll-to-top');
        const scrollIndicator = document.querySelector('.scroll-indicator');

        function initScrollIndicator() {
            if (!scrollIndicator) return;
            const length = scrollIndicator.getTotalLength ? scrollIndicator.getTotalLength() : 100;
            scrollIndicator.style.strokeDasharray = length;
            scrollIndicator.style.strokeDashoffset = length;
            function update() {
                const ws = document.documentElement.scrollTop || document.body.scrollTop;
                const h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const progress = h > 0 ? (ws / h) : 0;
                scrollIndicator.style.strokeDashoffset = length - (progress * length);
                if (ws > 200) { scrollToTopBtn.classList.add('visible'); }
                else { scrollToTopBtn.classList.remove('visible'); }
            }
            window.addEventListener('scroll', update, { passive: true });
            update();
        }

        scrollToTopBtn.addEventListener('click', () => { window.scrollTo({ top: 0, behavior: 'smooth' }); });
        document.addEventListener('DOMContentLoaded', initScrollIndicator);

        if (!IS_LOGGED_IN) {
            document.addEventListener('DOMContentLoaded', () => {
                const loginButton = document.getElementById('login-button');
                if (loginButton) { loginButton.addEventListener('click', function (e) { e.preventDefault(); openAuthModal(); }); }
                const mobileLoginButton = document.getElementById('mobile-login-button');
                if (mobileLoginButton) { mobileLoginButton.addEventListener('click', function (e) { e.preventDefault(); openAuthModal(); }); }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (!IS_LOGGED_IN) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var container = document.getElementById('auth-forms-container');
                        container.innerHTML = xhr.responseText;
                        var scripts = container.getElementsByTagName('script');
                        for (var i = 0; i < scripts.length; i++) { var newScript = document.createElement('script'); newScript.text = scripts[i].text; document.head.appendChild(newScript); }
                        initializePhoneInputs();
                        if (window.lucide && lucide.createIcons) lucide.createIcons();
                    }
                };
                xhr.open('GET', BASE_URL + 'login/login.php', true); xhr.send();
            }
        });

        function initializePhoneInputs() {
            const pi = document.querySelectorAll("input[type='tel']");
            if (pi.length > 0) {
                pi.forEach(ip => {
                    window.intlTelInput(ip, {
                        preferredCountries: ["ug", "rw", "ke", "tz"],
                        initialCountry: "ug",
                        separateDialCode: true,
                        allowDropdown: true,
                        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                    });
                });
            }
        }

        $(document).ready(function () {
            function setupOTPInputs() {
                $(document).on('keydown', '.otp-input', function (e) {
                    const $current = $(this);
                    const index = $('.otp-input[data-otp-target="' + $current.data('otp-target') + '"]').index($current);
                    const $inputs = $('.otp-input[data-otp-target="' + $current.data('otp-target') + '"]');
                    if (e.keyCode === 8 || e.keyCode === 46) {
                        if ($current.val() === '') {
                            if (index > 0) {
                                e.preventDefault();
                                $inputs.eq(index - 1).focus().val('');
                                updateOTPValue($current.data('otp-target'));
                            }
                        }
                    }
                    if (e.keyCode === 37 && index > 0) { e.preventDefault(); $inputs.eq(index - 1).focus(); }
                    if (e.keyCode === 39 && index < $inputs.length - 1) { e.preventDefault(); $inputs.eq(index + 1).focus(); }
                });
                $(document).on('input', '.otp-input', function () {
                    const $current = $(this);
                    const $inputs = $('.otp-input[data-otp-target="' + $current.data('otp-target') + '"]');
                    const index = $inputs.index($current);
                    $current.val($current.val().replace(/[^0-9]/g, ''));
                    if ($current.val().length === 1 && index < $inputs.length - 1) { $inputs.eq(index + 1).focus(); }
                    updateOTPValue($current.data('otp-target'));
                    const allFilled = $inputs.toArray().every(input => input.value.length === 1);
                    if (allFilled) {
                        const target = $current.data('otp-target');
                        if (target === 'email-otp') { handleEmailOTPSubmit(); }
                        else if (target === 'phone-otp') { handlePhoneOTPSubmit(); }
                        else if (target === 'reset-otp') { handleResetOTPSubmit(); }
                    }
                });
                $(document).on('paste', '.otp-input:first-of-type', function (e) {
                    e.preventDefault();
                    const $current = $(this);
                    const target = $current.data('otp-target');
                    const $inputs = $('.otp-input[data-otp-target="' + target + '"]');
                    const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
                    const digits = pasteData.replace(/\D/g, '').substring(0, 6);
                    for (let i = 0; i < Math.min(digits.length, $inputs.length); i++) { $inputs.eq(i).val(digits[i] || ''); }
                    $inputs.eq(Math.min(digits.length, $inputs.length) - 1).focus();
                    updateOTPValue(target);
                    if (digits.length >= 6) {
                        if (target === 'email-otp') { handleEmailOTPSubmit(); }
                        else if (target === 'phone-otp') { handlePhoneOTPSubmit(); }
                        else if (target === 'reset-otp') { handleResetOTPSubmit(); }
                    }
                });
            }
            setupOTPInputs();
        });

        function updateOTPValue(target) {
            const values = $('.otp-input[data-otp-target="' + target + '"]').map(function () { return this.value; }).get().join('');
            $('#' + target).val(values);
        }

        function logoutUser() {
            $.ajax({
                url: BASE_URL + 'auth/logout',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        notifications.success('You have been successfully logged out');
                        setTimeout(function () { window.location.href = BASE_URL; }, 1000);
                    } else { notifications.error(response.message || 'Failed to logout'); }
                },
                error: function () { notifications.error('Failed to connect to the server. Please try again.'); }
            });
        }
    </script>
</body>

</html>