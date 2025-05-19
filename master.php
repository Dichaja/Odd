<?php
require_once __DIR__ . '/config/config.php';
$title = isset($pageTitle) ? $pageTitle . ' | Buy Online - Deliver On-site' : 'Zzimba Online Uganda | Buy Online - Deliver On-site';
$activeNav = $activeNav ?? 'home';
date_default_timezone_set('Africa/Kampala');
$js_url = BASE_URL . "track/eventLog.js";
$ch = curl_init($js_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$js_code = curl_exec($ch);
curl_close($ch);

$isLoggedIn = isset($_SESSION['user']) && isset($_SESSION['user']['logged_in']) && $_SESSION['user']['logged_in'];

// Default meta description if none is provided
$metaDescription = 'Zzimba Online Uganda - Your one-stop shop for construction materials and services. Buy online and get delivery on-site.';

// Check if SEO tags are provided from vendor-profile.php or other pages
$hasSeoTags = isset($seoTags) && is_array($seoTags);

// If SEO tags are provided, use them
if ($hasSeoTags) {
    $title = $seoTags['title'] ?? $title;
    $metaDescription = $seoTags['description'] ?? $metaDescription;
}

// Get current URL for canonical and OG URL
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($currentUrl) ?>">

    <!-- Open Graph Meta Tags -->
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

    <!-- Twitter Card Meta Tags -->
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
    <script src="<?= BASE_URL ?>track/eventLog.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
        const ACTIVE_NAV = <?php echo ($activeNav !== null) ? json_encode($activeNav) : "null"; ?>;
        const PAGE_TITLE = <?php echo ($pageTitle !== null) ? json_encode($pageTitle) : "null"; ?>;
        const IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D92B13',
                        secondary: '#1a1a1a'
                    },
                    fontFamily: {
                        rubik: ['Rubik', 'sans-serif']
                    },
                    zIndex: {
                        10: '10',
                        20: '20',
                        30: '30',
                        40: '40',
                        50: '50',
                        60: '60',
                        70: '70',
                        80: '80',
                        90: '90',
                        100: '100'
                    }
                }
            }
        }
    </script>
    <style>
        .nav-link {
            position: relative
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #C00000;
            transition: width 0.3s ease
        }

        .nav-link:hover::after {
            width: 100%
        }

        .dropdown-icon {
            transition: transform 0.3s ease
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
            transition: opacity 0.3s ease, transform 0.3s ease;
            opacity: 0;
            transform: scale(0.95)
        }

        .modal-container.active {
            opacity: 1;
            transform: scale(1)
        }

        .auth-form {
            transition: opacity 0.3s ease, transform 0.3s ease;
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

        /* New toast styles */
        .toast {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: white;
            font-weight: 500;
            opacity: 0;
            z-index: 10000;
            transition: opacity 0.3s ease-in-out;
        }

        .toast-success {
            background-color: #10B981;
        }

        .toast-error {
            background-color: #EF4444;
        }

        .toast-show {
            opacity: 1;
        }

        /* Keep the notification container for backward compatibility */
        .notification-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1100;
            display: flex;
            flex-direction: column-reverse;
            gap: 0.5rem;
            pointer-events: none
        }

        .notification {
            padding: 1rem;
            border-radius: 0.5rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            opacity: 0;
            transform: translateY(1rem);
            transition: opacity 0.3s ease, transform 0.3s ease;
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
            font-size: 0.875rem;
            margin-bottom: 0.25rem
        }

        .notification .message {
            font-size: 0.875rem;
            color: #4B5563
        }

        .notification .close {
            flex-shrink: 0;
            color: #9CA3AF;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease
        }

        .notification .close:hover {
            background-color: #F3F4F6
        }

        .search-form {
            display: none;
            transition: all 0.3s ease
        }

        .search-form.active {
            display: flex
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            left: -100%;
            width: 80%;
            height: 100%;
            background-color: white;
            transition: left 0.3s ease;
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
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999
        }

        .mobile-menu-overlay.active {
            display: block
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
            scrollbar-color: rgb(135, 135, 135)transparent
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
            transition: stroke-dashoffset 0.3s ease
        }

        .ad-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease
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
            background-color: rgba(255, 255, 255, 0.8);
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
            transition: background-color 0.3s ease
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
            transition: width 0.3s ease, background-color 0.3s ease
        }

        .password-strength-text {
            font-size: 0.75rem;
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
            position: relative;
        }

        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            width: 240px;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
            z-index: 50;
            display: none;
        }

        .user-dropdown:hover .user-dropdown-menu {
            display: block;
        }

        .user-dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            color: #1a1a1a;
            transition: background-color 0.2s ease;
        }

        .user-dropdown-item:hover {
            background-color: #f3f4f6;
            color: #D92B13;
        }

        .user-dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.25rem 0;
        }
    </style>
</head>

<body class="font-rubik min-h-screen flex flex-col">
    <div class="bg-secondary text-white text-sm py-2 hidden md:block">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <span><i class="fas fa-envelope mr-2"></i>halo@zzimbaonline.com</span>
                <span><i class="fas fa-phone mr-2"></i>+256 392 003-406</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="#" class="hover:text-primary transition-colors"><i class="fas fa-truck mr-2"></i>Delivery
                    Info</a>
                <a href="<?= BASE_URL ?>terms-and-conditions" class="hover:text-primary transition-colors"><i
                        class="fas fa-file-contract mr-2"></i>Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-[70px]">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>">
                        <img src="<?= BASE_URL ?>img/logo_alt.png?height=40&width=150" alt="Logo" class="h-10">
                    </a>
                </div>
                <div id="nav-and-search-container" class="hidden md:flex items-center space-x-8">
                    <div id="desktop-nav" class="flex items-center space-x-8"></div>
                    <div class="search-form hidden items-center">
                        <input type="text" placeholder="Search for products..."
                            class="px-4 py-2 rounded-l-lg focus:outline-none border border-gray-300">
                        <button class="bg-primary text-white px-6 py-2 rounded-r-lg hover:bg-red-600 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center space-x-4">
                        <button id="search-toggle" class="text-secondary hover:text-primary transition-colors">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                        <a href="#" class="text-secondary hover:text-primary transition-colors">
                            <i class="fas fa-shopping-cart text-xl"></i>
                        </a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): ?>
                            <div class="user-dropdown">
                                <button
                                    class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                                    <i class="fas fa-user mr-2"></i>Halo
                                    <?= htmlspecialchars($_SESSION['user']['username']) ?>!
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div class="user-dropdown-menu">
                                    <div class="px-4 py-3 bg-gray-50">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($_SESSION['user']['username']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user']['email']) ?>
                                        </p>
                                        <?php if (isset($_SESSION['user']['last_login']) && $_SESSION['user']['last_login']): ?>
                                            <p class="text-xs text-gray-500 mt-1">Last login:
                                                <?= date('M d, Y g:i A', strtotime($_SESSION['user']['last_login'])) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($_SESSION['user']['is_admin']): ?>
                                        <a href="<?= BASE_URL ?>admin/dashboard" class="user-dropdown-item">
                                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                        </a>
                                        <a href="<?= BASE_URL ?>admin/profile" class="user-dropdown-item">
                                            <i class="fas fa-user-circle mr-2"></i>Profile
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>account/dashboard" class="user-dropdown-item">
                                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                        </a>
                                        <a href="<?= BASE_URL ?>account/profile" class="user-dropdown-item">
                                            <i class="fas fa-user-circle mr-2"></i>Profile
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="javascript:void(0);" onclick="logoutUser(); return false;"
                                        class="user-dropdown-item text-red-600">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="#" onclick="openAuthModal(); return false;"
                                class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                                <i class="fas fa-user mr-2"></i>Login / Register
                            </a>
                        <?php endif; ?>
                    </div>
                    <button class="md:hidden text-secondary hover:text-primary" id="mobile-menu-button">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>
    <div class="mobile-menu-overlay"></div>
    <div class="mobile-menu bg-white p-4">
        <div class="flex justify-between items-center mb-4">
            <img src="<?= BASE_URL ?>img/logo_alt.png?height=40&width=150" alt="Logo" class="h-8">
            <button id="close-mobile-menu" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="mobile-menu-items" class="space-y-4"></div>
        <div class="mt-6 space-y-4">
            <div class="relative">
                <input type="text" placeholder="Search for products..."
                    class="w-full px-4 py-2 rounded-lg focus:outline-none border border-gray-300">
                <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <a href="#"
                class="block text-center bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-shopping-cart mr-2"></i>Cart
            </a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): ?>
                <?php if ($_SESSION['user']['is_admin']): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard"
                        class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>account/dashboard"
                        class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                <?php endif; ?>
                <a href="javascript:void(0);" onclick="logoutUser(); return false;"
                    class="block text-center bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            <?php else: ?>
                <a href="#" onclick="openAuthModal(); return false;"
                    class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors"
                    id="mobile-login-button">
                    <i class="fas fa-user mr-2"></i>Login / Register
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div id="auth-modal" style="z-index:1100"
        class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div id="auth-forms-container"></div>
        </div>
    </div>
    <div class="main-area flex-grow">
        <?= $mainContent ?? '' ?>
    </div>
    <footer class="hidden sm:block bg-secondary text-white mt-auto">
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
                        <li>Procurement Support</li>
                        <li>Construction Advice</li>
                        <li>Project Consultation</li>
                        <li>Site Inspections</li>
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
        <svg class="w-6 h-6" viewBox="0 0 36 36">
            <path class="scroll-circle"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                stroke="rgba(255,255,255,0.2)" stroke-width="3" />
            <path class="scroll-indicator"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                stroke="#fff" stroke-width="3" stroke-dasharray="100" stroke-dashoffset="100" />
            <i class="fas fa-arrow-up absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
        </svg>
    </button>
    <div id="adPopup" class="ad-popup">
        <div class="ad-content">
            <div class="progress-bar" id="adProgressBar"></div>
            <img src="https://placehold.co/800x450" alt="Advertisement" class="ad-image">
            <button id="adClose" class="ad-close" style="display:none">&times;</button>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        // New toast function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type === 'success' ? 'toast-success' : 'toast-error'}`;
            toast.textContent = message;

            document.body.appendChild(toast);

            // Trigger reflow and show
            requestAnimationFrame(() => {
                toast.classList.add('toast-show');
            });

            // Hide and remove after 5 seconds
            setTimeout(() => {
                toast.classList.remove('toast-show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // Keep the original notification system but update it to use the new toast design
        class NotificationSystem {
            constructor() {
                this.container = document.getElementById('notification-container');
                this.notifications = new Map();
                this.counter = 0;
            }
            show(o) {
                const {
                    type = 'info', title, message, duration = 5000
                } = o;

                // Use the new toast design
                const toast = document.createElement('div');
                toast.className = `toast ${type === 'success' ? 'toast-success' : 'toast-error'}`;
                toast.textContent = message;

                document.body.appendChild(toast);

                // Trigger reflow and show
                requestAnimationFrame(() => {
                    toast.classList.add('toast-show');
                });

                const id = this.counter++;
                this.notifications.set(id, toast);

                // Hide and remove after duration
                if (duration > 0) {
                    setTimeout(() => this.close(id), duration);
                }

                return id;
            }
            close(id) {
                const toast = this.notifications.get(id);
                if (toast) {
                    toast.classList.remove('toast-show');
                    setTimeout(() => {
                        toast.remove();
                        this.notifications.delete(id);
                    }, 300);
                }
            }
            success(m, t = 'Success') {
                return this.show({
                    type: 'success',
                    title: t,
                    message: m
                });
            }
            error(m, t = 'Error') {
                return this.show({
                    type: 'error',
                    title: t,
                    message: m
                });
            }
            warning(m, t = 'Warning') {
                return this.show({
                    type: 'warning',
                    title: t,
                    message: m
                });
            }
            info(m, t = 'Info') {
                return this.show({
                    type: 'info',
                    title: t,
                    message: m
                });
            }
        }
        const notifications = new NotificationSystem();

        function checkPasswordStrength(p, i = 'register-password') {
            const m = document.querySelector('#' + i).closest('div').nextElementSibling;
            const f = m.querySelector('.password-strength-meter-fill');
            const t = m.nextElementSibling;
            m.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');
            if (!p) {
                f.style.width = '0';
                t.textContent = '';
                return;
            }
            let s = 0;
            if (p.length >= 8) s++;
            if (p.length >= 12) s++;
            if (/[A-Z]/.test(p)) s++;
            if (/[a-z]/.test(p)) s++;
            if (/[0-9]/.test(p)) s++;
            if (/[^A-Za-z0-9]/.test(p)) s++;
            let l = '',
                c = '';
            if (s < 3) {
                l = 'Weak';
                c = 'strength-weak';
            } else if (s < 4) {
                l = 'Fair';
                c = 'strength-fair';
            } else if (s < 6) {
                l = 'Good';
                c = 'strength-good';
            } else {
                l = 'Strong';
                c = 'strength-strong';
            }

            m.classList.add(c);
            t.textContent = 'Password strength: ' + l;
        }

        function togglePasswordVisibility(i) {
            const e = document.getElementById(i);
            const b = e.nextElementSibling;
            const ic = b.querySelector('i');
            if (e.type === 'password') {
                e.type = 'text';
                ic.classList.remove('fa-eye');
                ic.classList.add('fa-eye-slash');
                notifications.info('Password visible');
            } else {
                e.type = 'password';
                ic.classList.remove('fa-eye-slash');
                ic.classList.add('fa-eye');
                notifications.info('Password hidden');
            }
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function isStrongPassword(password) {
            return (password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[^A-Za-z0-9]/.test(password));
        }

        function generateOTP(length = 6) {
            let otp = '';
            for (let i = 0; i < length; i++) {
                otp += Math.floor(Math.random() * 10).toString();
            }
            return otp;
        }
        document.getElementById("currentYear").textContent = new Date().getFullYear();
        const activeNavKey = "<?= $activeNav ?>";
        const navItems = {
            home: {
                icon: 'fa-home',
                title: 'Home',
                url: BASE_URL
            },
            about: {
                icon: 'fa-building',
                title: 'About Us',
                url: BASE_URL + 'about-us'
            },
            materials: {
                icon: 'fa-warehouse',
                title: 'Materials Yard',
                url: BASE_URL + 'materials-yard'
            },
            contact: {
                icon: 'fa-envelope',
                title: 'Contact Us',
                url: BASE_URL + 'contact-us'
            }
        };

        function generateNavigation(i) {
            let h = '';
            for (const [k, v] of Object.entries(i)) {
                const a = k === activeNavKey ? 'active' : '';
                h += `<a href="${v.url}" class="nav-link text-secondary hover:text-primary transition-colors ${a}"><i class="fas ${v.icon} mr-2"></i>${v.title}</a>`;
            }
            return h;
        }

        function generateMobileNavigation(i) {
            let h = '';
            for (const [k, v] of Object.entries(i)) {
                const a = k === activeNavKey ? 'active' : '';
                h += `<a href="${v.url}" class="block py-2 px-4 text-secondary hover:text-primary hover:bg-gray-50 ${a}"><i class="fas ${v.icon} mr-2"></i>${v.title}</a>`;
            }
            return h;
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('desktop-nav').innerHTML = generateNavigation(navItems);
            document.getElementById('mobile-menu-items').innerHTML = generateMobileNavigation(navItems);

            const userDropdownButton = document.getElementById('user-dropdown-button');
            if (userDropdownButton) {
                const userDropdownMenu = document.querySelector('.user-dropdown-menu');
                userDropdownButton.addEventListener('click', function () {
                    userDropdownMenu.classList.toggle('active');
                });

                document.addEventListener('click', function (e) {
                    if (!userDropdownButton.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                        userDropdownMenu.classList.remove('active');
                    }
                });
            }
        });
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
        const closeMobileMenuButton = document.getElementById('close-mobile-menu');

        function openMobileMenu() {
            mobileMenu.classList.add('active');
            mobileMenuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileMenuOverlay.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        mobileMenuButton.addEventListener('click', openMobileMenu);
        closeMobileMenuButton.addEventListener('click', closeMobileMenu);
        mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        function openAuthModal() {
            const m = document.getElementById('auth-modal');
            const c = m.querySelector('.modal-container');

            m.classList.remove('hidden');
            m.classList.add('flex');

            m.style.display = 'flex';

            c.offsetHeight;
            c.classList.add('active');

            document.body.style.overflow = 'hidden';
        }


        function closeAuthModal() {
            const m = document.getElementById('auth-modal');
            const c = m.querySelector('.modal-container');
            c.classList.remove('active');
            setTimeout(() => {
                m.classList.remove('flex');
                m.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
        const searchToggle = document.getElementById('search-toggle');
        const searchForm = document.querySelector('.search-form');
        const desktopNav = document.getElementById('desktop-nav');
        searchToggle.addEventListener('click', () => {
            searchForm.classList.toggle('active');
            desktopNav.classList.toggle('hidden');
        });
        document.addEventListener('click', e => {
            if (!searchForm.contains(e.target) && !searchToggle.contains(e.target)) {
                const si = searchForm.querySelector('input[type="text"]');
                if (searchForm.classList.contains('active') && !si.value) {
                    searchForm.classList.remove('active');
                    desktopNav.classList.remove('hidden');
                }
            }
        });
        const scrollToTopBtn = document.getElementById('scroll-to-top');
        const scrollIndicator = document.querySelector('.scroll-indicator');

        function updateScrollProgress() {
            const ws = document.documentElement.scrollTop || document.body.scrollTop;
            const h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const sc = (ws / h) * 100;
            const d = 100 - sc;
            if (ws > 200) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
            scrollIndicator.style.strokeDashoffset = d;
        }
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        window.addEventListener('scroll', updateScrollProgress);
        updateScrollProgress();

        if (!IS_LOGGED_IN) {
            document.addEventListener('DOMContentLoaded', () => {
                const loginButton = document.getElementById('login-button');
                if (loginButton) {
                    loginButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        openAuthModal();
                    });
                }

                const mobileLoginButton = document.getElementById('mobile-login-button');
                if (mobileLoginButton) {
                    mobileLoginButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        openAuthModal();
                    });
                }
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
                        for (var i = 0; i < scripts.length; i++) {
                            var newScript = document.createElement('script');
                            newScript.text = scripts[i].text;
                            document.head.appendChild(newScript);
                        }
                        initializePhoneInputs();
                    }
                };
                xhr.open('GET', BASE_URL + 'login/login.php', true);
                xhr.send();
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

                    if (e.keyCode === 37 && index > 0) {
                        e.preventDefault();
                        $inputs.eq(index - 1).focus();
                    }
                    if (e.keyCode === 39 && index < $inputs.length - 1) {
                        e.preventDefault();
                        $inputs.eq(index + 1).focus();
                    }
                });

                $(document).on('input', '.otp-input', function () {
                    const $current = $(this);
                    const $inputs = $('.otp-input[data-otp-target="' + $current.data('otp-target') + '"]');
                    const index = $inputs.index($current);

                    $current.val($current.val().replace(/[^0-9]/g, ''));

                    if ($current.val().length === 1 && index < $inputs.length - 1) {
                        $inputs.eq(index + 1).focus();
                    }

                    updateOTPValue($current.data('otp-target'));

                    const allFilled = $inputs.toArray().every(input => input.value.length === 1);
                    if (allFilled) {
                        const target = $current.data('otp-target');
                        if (target === 'email-otp') {
                            handleEmailOTPSubmit();
                        } else if (target === 'phone-otp') {
                            handlePhoneOTPSubmit();
                        } else if (target === 'reset-otp') {
                            handleResetOTPSubmit();
                        }
                    }
                });

                $(document).on('paste', '.otp-input:first-of-type', function (e) {
                    e.preventDefault();

                    const $current = $(this);
                    const target = $current.data('otp-target');
                    const $inputs = $('.otp-input[data-otp-target="' + target + '"]');

                    const pasteData = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
                    const digits = pasteData.replace(/\D/g, '').substring(0, 6);

                    for (let i = 0; i < Math.min(digits.length, $inputs.length); i++) {
                        $inputs.eq(i).val(digits[i] || '');
                    }

                    $inputs.eq(Math.min(digits.length, $inputs.length) - 1).focus();

                    updateOTPValue(target);

                    if (digits.length >= 6) {
                        if (target === 'email-otp') {
                            handleEmailOTPSubmit();
                        } else if (target === 'phone-otp') {
                            handlePhoneOTPSubmit();
                        } else if (target === 'reset-otp') {
                            handleResetOTPSubmit();
                        }
                    }
                });
            }

            setupOTPInputs();
        });

        function updateOTPValue(target) {
            const values = $('.otp-input[data-otp-target="' + target + '"]').map(function () {
                return this.value;
            }).get().join('');
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
                        setTimeout(function () {
                            window.location.href = BASE_URL;
                        }, 1000);
                    } else {
                        notifications.error(response.message || 'Failed to logout');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Logout error:", xhr, status, error);
                    notifications.error('Failed to connect to the server. Please try again.');
                }
            });
        }
    </script>
</body>

</html>