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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout to 30 minutes (1800 seconds) of inactivity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

$_SESSION['last_activity'] = time();
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

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-width: 250px;
            z-index: 50;
            overflow: hidden;
        }

        .user-dropdown:hover .user-dropdown-menu {
            display: block;
        }

        .user-dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #4B5563;
            transition: all 0.3s ease;
        }

        .user-dropdown-item:hover {
            background-color: #F3F4F6;
            color: #D92B13;
        }

        .user-dropdown-divider {
            border-top: 1px solid #E5E7EB;
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
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): ?>
                            <div class="user-dropdown">
                                <button class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
                                    <i class="fas fa-user mr-2"></i>Halo <?= htmlspecialchars($_SESSION['user']['username']) ?>!
                                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                                </button>
                                <div class="user-dropdown-menu">
                                    <div class="px-4 py-3 bg-gray-50">
                                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['user']['username']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
                                        <?php if (isset($_SESSION['user']['last_login']) && $_SESSION['user']['last_login']): ?>
                                            <p class="text-xs text-gray-500 mt-1">Last login: <?= date('M d, Y g:i A', strtotime($_SESSION['user']['last_login'])) ?></p>
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
                                    <div class="user-dropdown-divider"></div>
                                    <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="user-dropdown-item text-red-600">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="#" onclick="openAuthModal(); return false;" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center">
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
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['logged_in']): ?>
                <div class="bg-secondary text-white px-6 py-2 rounded-lg">
                    <p class="font-medium">Halo <?= htmlspecialchars($_SESSION['user']['username']) ?>!</p>
                </div>
                <?php if ($_SESSION['user']['is_admin']): ?>
                    <a href="<?= BASE_URL ?>admin/dashboard" class="block text-center border border-gray-300 px-6 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>account/dashboard" class="block text-center border border-gray-300 px-6 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                <?php endif; ?>
                <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="block text-center border border-red-300 text-red-600 px-6 py-2 rounded-lg hover:bg-red-50 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                </a>
            <?php else: ?>
                <a href="#" onclick="openAuthModal(); return false;" class="block text-center bg-secondary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition-colors">
                    <i class="fas fa-user mr-2"></i>Login / Register
                </a>
            <?php endif; ?>
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
                    </div>  : ''}
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
            const authModal = document.getElementById('auth-modal');
            if (authModal) {
                authModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                const modalContainer = authModal.querySelector('.modal-container');
                if (modalContainer) {
                    setTimeout(() => {
                        modalContainer.classList.add('active');
                    }, 10);
                }
            }
        }

        function closeAuthModal() {
            const authModal = document.getElementById('auth-modal');
            if (authModal) {
                const modalContainer = authModal.querySelector('.modal-container');
                if (modalContainer) {
                    modalContainer.classList.remove('active');
                }

                setTimeout(() => {
                    authModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        document.getElementById('auth-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'auth-modal') {
                closeAuthModal();
            }
        });

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

        function openAuthModal() {
            const authModal = document.getElementById('auth-modal');
            if (authModal) {
                authModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';

                const modalContainer = authModal.querySelector('.modal-container');
                if (modalContainer) {
                    setTimeout(() => {
                        modalContainer.classList.add('active');
                    }, 10);
                }
            }
        }

        function closeAuthModal() {
            const authModal = document.getElementById('auth-modal');
            if (authModal) {
                const modalContainer = authModal.querySelector('.modal-container');
                if (modalContainer) {
                    modalContainer.classList.remove('active');
                }

                setTimeout(() => {
                    authModal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        function logoutUser() {
            fetch('<?= BASE_URL ?>logout', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '<?= BASE_URL ?>';
                });
        }
    </script>
    <?php require_once 'login/login.php'; ?>
</body>

</html>