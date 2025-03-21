<?php
require_once __DIR__ . '/config/config.php';
$title = isset($pageTitle) ? $pageTitle . ' | Buy Online - Deliver On-site' : 'Zzimba Online Uganda | Buy Online - Deliver On-site';
$activeNav = $activeNav ?? 'home';

date_default_timezone_set('Africa/Kampala');
$js_url = BASE_URL . "track/eventLog.js";

// Use cURL to fetch the JavaScript code from eventLog.js
$ch = curl_init($js_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$js_code = curl_exec($ch);
curl_close($ch);
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" href="<?= BASE_URL ?>img/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
    <script src="<?= BASE_URL ?>track/eventLog.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
        const ACTIVE_NAV = <?php echo ($activeNav !== NULL) ? json_encode($activeNav) : "null"; ?>;
        const PAGE_TITLE = <?php echo ($pageTitle !== NULL) ? json_encode($pageTitle) : "null"; ?>;
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D92B13',
                        secondary: '#1a1a1a'
                    },
                    fontFamily: {
                        rubik: ['Rubik', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        .mega-menu {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: 100%;
            left: 0;
            width: 150%;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            transform: translateY(10px);
            z-index: 50;
            white-space: nowrap;
        }

        .menu-item:hover .mega-menu {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: #C00000;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .dropdown-icon {
            transition: transform 0.3s ease;
        }

        .menu-item:hover .dropdown-icon {
            transform: rotate(180deg);
        }

        .nav-link.active {
            color: #C00000;
        }

        .nav-link.active::after {
            width: 100%;
        }

        @media (max-width:768px) {
            .mobile-menu {
                display: none;
            }

            .mobile-menu.active {
                display: block;
            }
        }

        .modal-container {
            transition: opacity 0.3s ease, transform 0.3s ease;
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-container.active {
            opacity: 1;
            transform: scale(1);
        }

        .auth-form {
            transition: opacity 0.3s ease, transform 0.3s ease;
            opacity: 0;
            transform: translateX(20px);
            position: absolute;
            width: 100%;
        }

        .auth-form.active {
            opacity: 1;
            transform: translateX(0);
            position: relative;
        }

        .notification-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1100;
            display: flex;
            flex-direction: column-reverse;
            gap: 0.5rem;
            pointer-events: none;
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
            gap: 1rem;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notification.success {
            border-left: 4px solid #10B981;
        }

        .notification.error {
            border-left: 4px solid #EF4444;
        }

        .notification.warning {
            border-left: 4px solid #F59E0B;
        }

        .notification.info {
            border-left: 4px solid #3B82F6;
        }

        .notification .icon {
            flex-shrink: 0;
            width: 1.5rem;
            height: 1.5rem;
        }

        .notification.success .icon {
            color: #10B981;
        }

        .notification.error .icon {
            color: #EF4444;
        }

        .notification.warning .icon {
            color: #F59E0B;
        }

        .notification.info .icon {
            color: #3B82F6;
        }

        .notification .content {
            flex-grow: 1;
        }

        .notification .title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .notification .message {
            font-size: 0.875rem;
            color: #4B5563;
        }

        .notification .close {
            flex-shrink: 0;
            color: #9CA3AF;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease;
        }

        .notification .close:hover {
            background-color: #F3F4F6;
        }

        .search-form {
            display: none;
            transition: all 0.3s ease;
        }

        .search-form.active {
            display: flex;
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
            overflow-y: auto;
        }

        .mobile-menu.active {
            left: 0;
        }

        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        ::-webkit-scrollbar {
            width: 3px;
            height: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: rgb(0, 0, 0);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: rgb(135, 135, 135) transparent;
        }

        #scroll-to-top {
            z-index: 1000;
        }

        #scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .scroll-indicator {
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
            transition: stroke-dashoffset 0.3s ease;
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
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .ad-popup.show {
            opacity: 1;
            visibility: visible;
        }

        .ad-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .ad-image {
            width: 100%;
            height: auto;
            max-height: 60vh;
            object-fit: contain;
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
            transition: background-color 0.3s ease;
        }

        .ad-close:hover {
            background-color: #fff;
        }

        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 4px;
            background-color: #C00000;
            width: 0;
            transition: width 5s linear;
        }

        /* Password strength meter styles */
        .password-strength-meter {
            height: 5px;
            background-color: #ddd;
            border-radius: 3px;
            margin-top: 5px;
            position: relative;
            overflow: hidden;
        }

        .password-strength-meter-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-strength-text {
            font-size: 0.75rem;
            margin-top: 5px;
        }

        .strength-weak .password-strength-meter-fill {
            background-color: #ef4444;
            width: 25%;
        }

        .strength-fair .password-strength-meter-fill {
            background-color: #f59e0b;
            width: 50%;
        }

        .strength-good .password-strength-meter-fill {
            background-color: #3b82f6;
            width: 75%;
        }

        .strength-strong .password-strength-meter-fill {
            background-color: #10b981;
            width: 100%;
        }

        .iti {
            width: 100%;
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
                <a href="#" class="hover:text-primary transition-colors"><i class="fas fa-truck mr-2"></i>Delivery Info</a>
                <a href="<?= BASE_URL ?>terms-and-conditions" class="hover:text-primary transition-colors"><i class="fas fa-file-contract mr-2"></i>Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
    <nav class="bg-white shadow-lg sticky top-0 z-10">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="<?= BASE_URL ?>">
                        <img src="<?= BASE_URL ?>img/logo_alt.png?height=40&width=150" alt="Logo" class="h-10">
                    </a>
                </div>
                <div id="nav-and-search-container" class="hidden md:flex items-center space-x-8">
                    <div id="desktop-nav" class="flex items-center space-x-8"></div>
                    <div class="search-form hidden items-center">
                        <input type="text" placeholder="Search for products..." class="px-4 py-2 rounded-l-lg focus:outline-none border border-gray-300">
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
                        <a href="#" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center" onclick="openAuthModal()">
                            <i class="fas fa-user mr-2"></i>Login / Register
                        </a>
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
            <img src="img/logo_alt.png?height=40&width=150" alt="Logo" class="h-8">
            <button id="close-mobile-menu" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="mobile-menu-items" class="space-y-4"></div>
        <div class="mt-6 space-y-4">
            <div class="relative">
                <input type="text" placeholder="Search for products..." class="w-full px-4 py-2 rounded-lg focus:outline-none border border-gray-300">
                <button class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <a href="#" class="block text-center bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-shopping-cart mr-2"></i>Cart
            </a>
            <a href="#" class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors" onclick="openAuthModal()">
                <i class="fas fa-user mr-2"></i>Login / Register
            </a>
        </div>
    </div>
    <div id="auth-modal" style="z-index:1100" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="modal-container bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
            <div id="login-form" class="auth-form active">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-secondary">Login</h2>
                        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    <p class="mb-4 text-center text-sm text-gray-600">Don't have an account?
                        <a href="#" onclick="toggleAuthForms()" class="text-primary hover:text-red-700 font-medium">Create Account</a>
                    </p>
                    <form class="space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autocomplete="new-email">
                            </div>
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your password" id="login-password" autocomplete="new-password">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('login-password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" onclick="showForgotPasswordForm()" class="text-sm text-primary hover:text-red-700">Forgot Password?</a>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Login</button>
                    </form>
                </div>
            </div>
            <div id="register-form" class="auth-form" style="display: none;">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-secondary">Create Account</h2>
                        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    <p class="mb-4 text-center text-sm text-gray-600">Already have an account?
                        <a href="#" onclick="toggleAuthForms()" class="text-primary hover:text-red-700 font-medium">Login</a>
                    </p>
                    <form class="space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Choose a username" autocomplete="new-username">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="Phone Number" class="w-full py-2 border border-gray-300 rounded-lg" autocomplete="new-phone">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autocomplete="new-email">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Create a password" id="register-password" autocomplete="new-password" oninput="checkPasswordStrength(this.value)">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('register-password')"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="password-strength-meter mt-2">
                                <div class="password-strength-meter-fill"></div>
                            </div>
                            <div class="password-strength-text text-xs text-gray-500"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm your password" id="register-confirm-password" autocomplete="new-password">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('register-confirm-password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <input type="checkbox" required class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-gray-600">I agree to the
                                <a href="<?= BASE_URL ?>terms-and-conditions" class="text-primary hover:text-red-700">Terms of Service</a> and
                                <a href="#" class="text-primary hover:text-red-700">Privacy Policy</a>
                            </span>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Create Account</button>
                    </form>
                </div>
            </div>
            <div id="forgot-password-form" class="auth-form" style="display: none;">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-secondary">Forgot Password</h2>
                        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    <form class="space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="email" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter your email" autocomplete="new-email">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Send Reset Code</button>
                    </form>
                    <p class="mt-4 text-center text-sm text-gray-600">Remember your password?
                        <a href="#" onclick="showLoginForm()" class="text-primary hover:text-red-700 font-medium">Back to Login</a>
                    </p>
                </div>
            </div>
            <div id="reset-password-form" class="auth-form" style="display: none;">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-secondary">Reset Password</h2>
                        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times text-xl"></i></button>
                    </div>
                    <form class="space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                            <div class="relative">
                                <i class="fas fa-key absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" required class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter verification code" autocomplete="off">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Enter new password" id="new-password" autocomplete="new-password" oninput="checkPasswordStrength(this.value, 'new-password')">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('new-password')"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="password-strength-meter mt-2">
                                <div class="password-strength-meter-fill"></div>
                            </div>
                            <div class="password-strength-text text-xs text-gray-500"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="password" required class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Confirm new password" id="confirm-new-password" autocomplete="new-password">
                                <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePasswordVisibility('confirm-new-password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg hover:bg-red-600 transition-colors">Reset Password</button>
                    </form>
                </div>
            </div>
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
                    <p class="text-gray-400">Be a part of something special. Whether you're a buyer looking for unique products or a vendor aiming to expand your business, Zzimba Online is the place for you.</p>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="<?= BASE_URL ?>/about-us" class="text-gray-400 hover:text-primary">About Us</a></li>
                        <li><a href="<?= BASE_URL ?>/materials-yard" class="text-gray-400 hover:text-primary">Materials</a></li>
                        <li><a href="<?= BASE_URL ?>/contact-us" class="text-gray-400 hover:text-primary">Contact</a></li>
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
    <button id="scroll-to-top" class="fixed bottom-6 right-6 bg-primary hover:bg-red-600 text-white rounded-full p-3 shadow-lg transition-all duration-300 opacity-0 invisible" aria-label="Scroll to top">
        <svg class="w-6 h-6" viewBox="0 0 36 36">
            <path class="scroll-circle" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="rgba(255, 255, 255, 0.2)" stroke-width="3" />
            <path class="scroll-indicator" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#fff" stroke-width="3" stroke-dasharray="100" stroke-dashoffset="100" />
            <i class="fas fa-arrow-up absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
        </svg>
    </button>
    <div id="adPopup" class="ad-popup">
        <div class="ad-content">
            <div class="progress-bar" id="adProgressBar"></div>
            <img src="https://placehold.co/800x450" alt="Advertisement" class="ad-image">
            <button id="adClose" class="ad-close" style="display: none;">&times;</button>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        // Prevent form autofill
        document.addEventListener('DOMContentLoaded', function() {
            // Add random attributes to prevent autofill
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.setAttribute('autocomplete', 'off');
                const randomStr = Math.random().toString(36).substring(2);
                form.setAttribute('data-form-id', randomStr);
            });

            // Initialize phone input with East African countries prioritized
            const phoneInputField = document.querySelector("#phone");
            if (phoneInputField) {
                const iti = window.intlTelInput(phoneInputField, {
                    preferredCountries: ["ug", "rw", "ke", "tz"],
                    initialCountry: "ug",
                    separateDialCode: true,
                    allowDropdown: true,
                    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
                });
            }
        });

        // Password strength checker
        function checkPasswordStrength(password, inputId = 'register-password') {
            const meter = document.querySelector(`#${inputId}`).closest('div').nextElementSibling;
            const meterFill = meter.querySelector('.password-strength-meter-fill');
            const strengthText = meter.nextElementSibling;

            // Remove all strength classes
            meter.classList.remove('strength-weak', 'strength-fair', 'strength-good', 'strength-strong');

            if (!password) {
                meterFill.style.width = '0';
                strengthText.textContent = '';
                return;
            }

            // Check password strength
            let strength = 0;

            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;

            // Character variety checks
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[a-z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;

            // Set strength level
            let strengthLevel = '';
            let strengthClass = '';

            if (strength < 3) {
                strengthLevel = 'Weak';
                strengthClass = 'strength-weak';
            } else if (strength < 4) {
                strengthLevel = 'Fair';
                strengthClass = 'strength-fair';
            } else if (strength < 6) {
                strengthLevel = 'Good';
                strengthClass = 'strength-good';
            } else {
                strengthLevel = 'Strong';
                strengthClass = 'strength-strong';
            }

            meter.classList.add(strengthClass);
            strengthText.textContent = `Password strength: ${strengthLevel}`;
        }

        const baseURL = "<?= BASE_URL ?>";
        document.getElementById("currentYear").textContent = new Date().getFullYear();
        const activeNavKey = "<?= $activeNav ?>";
        const navItems = {
            home: {
                icon: 'fa-home',
                title: 'Home',
                url: baseURL
            },
            about: {
                icon: 'fa-building',
                title: 'About Us',
                url: baseURL + 'about-us'
            },
            materials: {
                icon: 'fa-warehouse',
                title: 'Materials Yard',
                children: {
                    cement: {
                        title: 'Cement & Concrete',
                        icon: 'fa-angle-right',
                        url: '#cement'
                    },
                    bricks: {
                        title: 'Bricks & Blocks',
                        icon: 'fa-angle-right',
                        url: '#bricks'
                    },
                    steel: {
                        title: 'Steel & Metals',
                        icon: 'fa-angle-right',
                        url: '#steel'
                    }
                }
            },
            contact: {
                icon: 'fa-envelope',
                title: 'Contact Us',
                url: baseURL + 'contact-us'
            }
        };

        function generateNavigation(items) {
            let html = '';
            for (const [key, item] of Object.entries(items)) {
                if (item.children) {
                    const isActive = key === activeNavKey ? 'active' : '';
                    html += `
                        <div class="menu-item relative group">
                            <button class="flex items-center space-x-2 text-secondary hover:text-primary transition-colors nav-link ${isActive}">
                                <i class="fas ${item.icon} mr-2"></i>
                                <span>${item.title}</span>
                                <i class="fas fa-chevron-down text-xs ml-1 dropdown-icon"></i>
                            </button>
                            <div class="mega-menu p-6">
                                <div class="grid grid-cols-1 gap-8">
                                    <div>
                                        <h3 class="font-bold text-lg mb-4 text-secondary border-b pb-2">${item.title}</h3>
                                        <ul class="space-y-2">
                                            ${Object.entries(item.children).map(([childKey, child])=>`
                                                <li>
                                                    <a href="${child.url}" class="flex items-center text-gray-600 hover:text-primary transition-colors">
                                                        <i class="fas ${child.icon} mr-2 text-sm"></i>
                                                        ${child.title}
                                                    </a>
                                                </li>`).join('')}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                } else {
                    const isActive = key === activeNavKey ? 'active' : '';
                    html += `
                        <a href="${item.url}" class="nav-link text-secondary hover:text-primary transition-colors ${isActive}">
                            <i class="fas ${item.icon} mr-2"></i>${item.title}
                        </a>`;
                }
            }
            return html;
        }

        function generateMobileNavigation(items) {
            let html = '';
            for (const [key, item] of Object.entries(items)) {
                if (item.children) {
                    const isActive = key === activeNavKey ? 'active' : '';
                    html += `
                        <div class="py-2 px-4 text-secondary">
                            <div class="flex items-center justify-between ${isActive}">
                                <span><i class="fas ${item.icon} mr-2"></i>${item.title}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                            <div class="mt-2 ml-4 space-y-2">
                                ${Object.entries(item.children).map(([childKey, child])=>`
                                    <a href="${child.url}" class="block py-2 text-secondary hover:text-primary hover:bg-gray-50">
                                        <i class="fas ${child.icon} mr-2"></i>${child.title}
                                    </a>`).join('')}
                            </div>
                        </div>`;
                } else {
                    const isActive = key === activeNavKey ? 'active' : '';
                    html += `
                        <a href="${item.url}" class="block py-2 px-4 text-secondary hover:text-primary hover:bg-gray-50 ${isActive}">
                            <i class="fas ${item.icon} mr-2"></i>${item.title}
                        </a>`;
                }
            }
            return html;
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('desktop-nav').innerHTML = generateNavigation(navItems);
            document.getElementById('mobile-menu-items').innerHTML = generateMobileNavigation(navItems);
        });
        class NotificationSystem {
            constructor() {
                this.container = document.getElementById('notification-container');
                this.notifications = new Map();
                this.counter = 0;
            }
            show(options) {
                const {
                    type = 'info', title, message, duration = 10000
                } = options;
                const id = this.counter++;
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.setAttribute('role', 'alert');
                let icon;
                switch (type) {
                    case 'success':
                        icon = '<i class="fas fa-check-circle"></i>';
                        break;
                    case 'error':
                        icon = '<i class="fas fa-exclamation-circle"></i>';
                        break;
                    case 'warning':
                        icon = '<i class="fas fa-exclamation-triangle"></i>';
                        break;
                    default:
                        icon = '<i class="fas fa-info-circle"></i>';
                }
                notification.innerHTML = `
                    <div class="icon">${icon}</div>
                    <div class="content">
                        ${title ? `<div class="title">${title}</div>` : ''}
                        <div class="message">${message}</div>
                    </div>
                    <button class="close" aria-label="Close notification"><i class="fas fa-times"></i></button>
                `;
                this.container.appendChild(notification);
                this.notifications.set(id, notification);
                notification.offsetHeight;
                notification.classList.add('show');
                const closeBtn = notification.querySelector('.close');
                closeBtn.addEventListener('click', () => this.close(id));
                if (duration > 0) {
                    setTimeout(() => this.close(id), duration);
                }
                return id;
            }
            close(id) {
                const notification = this.notifications.get(id);
                if (notification) {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                        this.notifications.delete(id);
                    }, 300);
                }
            }
            success(message, title = 'Success') {
                return this.show({
                    type: 'success',
                    title,
                    message
                });
            }
            error(message, title = 'Error') {
                return this.show({
                    type: 'error',
                    title,
                    message
                });
            }
            warning(message, title = 'Warning') {
                return this.show({
                    type: 'warning',
                    title,
                    message
                });
            }
            info(message, title = 'Info') {
                return this.show({
                    type: 'info',
                    title,
                    message
                });
            }
        }
        const notifications = new NotificationSystem();
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (form.closest('#login-form')) {
                    const email = form.querySelector('input[type="email"]').value;
                    const password = form.querySelector('input[type="password"]').value;
                    if (password.length < 8) {
                        notifications.error('Password must be at least 8 characters long');
                        return;
                    }
                    notifications.success('Login successful! Redirecting...');
                    setTimeout(() => {
                        notifications.info('Welcome back!');
                    }, 1500);
                }
                if (form.closest('#register-form')) {
                    const password = form.querySelector('#register-password').value;
                    const confirmPassword = form.querySelector('#register-confirm-password').value;
                    if (password.length < 8) {
                        notifications.error('Password must be at least 8 characters long');
                        return;
                    }
                    if (password !== confirmPassword) {
                        notifications.error('Passwords do not match');
                        return;
                    }
                    notifications.success('Account created successfully!');
                }
                if (form.closest('#forgot-password-form')) {
                    notifications.success('Reset code sent to your email');
                    showResetPasswordForm();
                }
                if (form.closest('#reset-password-form')) {
                    const code = form.querySelector('input[type="text"]').value;
                    const newPassword = form.querySelector('#new-password').value;
                    const confirmPassword = form.querySelector('#confirm-new-password').value;
                    if (code.length !== 6) {
                        notifications.error('Please enter a valid 6-digit code');
                        return;
                    }
                    if (newPassword.length < 8) {
                        notifications.error('Password must be at least 8 characters long');
                        return;
                    }
                    if (newPassword !== confirmPassword) {
                        notifications.error('Passwords do not match');
                        return;
                    }
                    notifications.success('Password reset successfully!');
                    setTimeout(() => {
                        showLoginForm();
                        notifications.info('Please login with your new password');
                    }, 1500);
                }
            });
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
            const modal = document.getElementById('auth-modal');
            const modalContainer = modal.querySelector('.modal-container');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modalContainer.offsetHeight;
            modalContainer.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeAuthModal() {
            const modal = document.getElementById('auth-modal');
            const modalContainer = modal.querySelector('.modal-container');
            modalContainer.classList.remove('active');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }

        function switchForm(showFormId) {
            const forms = ['login-form', 'register-form', 'forgot-password-form', 'reset-password-form'];
            forms.forEach(formId => {
                const form = document.getElementById(formId);
                if (formId === showFormId) {
                    form.style.display = 'block';
                    form.offsetHeight;
                    form.classList.add('active');
                } else {
                    form.classList.remove('active');
                    setTimeout(() => {
                        form.style.display = 'none';
                    }, 300);
                }
            });
        }

        function toggleAuthForms() {
            const loginForm = document.getElementById('login-form');
            if (loginForm.classList.contains('active')) {
                switchForm('register-form');
            } else {
                switchForm('login-form');
            }
        }

        function showForgotPasswordForm() {
            switchForm('forgot-password-form');
        }

        function showResetPasswordForm() {
            switchForm('reset-password-form');
        }

        function showLoginForm() {
            switchForm('login-form');
        }
        document.getElementById('auth-modal').addEventListener('click', (e) => {
            if (e.target.id === 'auth-modal') {
                closeAuthModal();
            }
        });
        document.querySelector('a[href="#"].bg-primary').addEventListener('click', (e) => {
            e.preventDefault();
            openAuthModal();
        });

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                notifications.info('Password visible');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                notifications.info('Password hidden');
            }
        }
        const searchToggle = document.getElementById('search-toggle');
        const searchForm = document.querySelector('.search-form');
        const desktopNav = document.getElementById('desktop-nav');
        searchToggle.addEventListener('click', () => {
            searchForm.classList.toggle('active');
            desktopNav.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!searchForm.contains(e.target) && !searchToggle.contains(e.target)) {
                const searchInput = searchForm.querySelector('input[type="text"]');
                if (searchForm.classList.contains('active') && !searchInput.value) {
                    searchForm.classList.remove('active');
                    desktopNav.classList.remove('hidden');
                }
            }
        });
        const scrollToTopBtn = document.getElementById('scroll-to-top');
        const scrollIndicator = document.querySelector('.scroll-indicator');

        function updateScrollProgress() {
            const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            const dashOffset = 100 - scrolled;
            if (winScroll > 200) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
            scrollIndicator.style.strokeDashoffset = dashOffset;
        }
        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        window.addEventListener('scroll', updateScrollProgress);
        updateScrollProgress();
        document.addEventListener('DOMContentLoaded', () => {
            const adPopup = document.getElementById('adPopup');
            const adClose = document.getElementById('adClose');
            const adProgressBar = document.getElementById('adProgressBar');
            setTimeout(() => {
                adPopup.classList.add('show');
                document.body.style.overflow = 'hidden';
                adProgressBar.style.width = '100%';
            }, 5000);
            setTimeout(() => {
                adClose.style.display = 'flex';
            }, 10000);
            adClose.addEventListener('click', () => {
                adPopup.classList.remove('show');
                document.body.style.overflow = 'auto';
            });
        });
    </script>
</body>

</html>