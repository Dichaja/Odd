<?php
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || !$_SESSION['user']['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    include_once __DIR__ . '/../403.php';
    exit;
}

$title = isset($pageTitle) ? $pageTitle . ' | Admin Console - Zzimba Online' : 'Admin Dashboard';
$activeNav = $activeNav ?? 'dashboard';

$userName = $_SESSION['user']['username'];
$userInitials = '';
$nameParts = explode(' ', $userName);
foreach ($nameParts as $part) {
    if (!empty($part)) {
        $userInitials .= strtoupper(substr($part, 0, 1));
    }
}

$lastLogin = isset($_SESSION['user']['last_login']) ? date('M d, Y g:i A', strtotime($_SESSION['user']['last_login'])) : 'First login';

$menuItems = [
    'overview' => [
        'title' => 'Overview',
        'items' => [
            'dashboard' => ['title' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'notifications' => 0],
            'mapping' => ['title' => 'Mapping', 'icon' => 'fa-map-marked-alt', 'notifications' => 0],
        ]
    ],
    'finance' => [
        'title' => 'Finance',
        'items' => [
            'cash-account' => ['title' => 'Cash Account', 'icon' => 'fa-wallet', 'notifications' => 0],
            'zzimba-credit' => ['title' => 'Zzimba Credit', 'icon' => 'fa-credit-card', 'notifications' => 0],
        ]
    ],
    'management' => [
        'title' => 'Management',
        'items' => [
            'manage-products' => ['title' => 'Products', 'icon' => 'fa-box', 'notifications' => 0],
            'manage-vendors' => ['title' => 'Vendors', 'icon' => 'fa-store', 'notifications' => 0],
            'fundi' => ['title' => 'Fundi', 'icon' => 'fa-hard-hat', 'notifications' => 0],
            'order-catalogue' => ['title' => 'Order Catalogue', 'icon' => 'fa-book', 'notifications' => 0],
            'member-subscription' => ['title' => 'Subscriptions', 'icon' => 'fa-users', 'notifications' => 0],
            'quotations' => ['title' => 'Quotations', 'icon' => 'fa-file-invoice-dollar', 'notifications' => 0],
        ]
    ],
    'marketing' => [
        'title' => 'Marketing',
        'items' => [
            'schedule-ads' => ['title' => 'Schedule Ads', 'icon' => 'fa-calendar-alt', 'notifications' => 0],
            'ads-management' => ['title' => 'Ads Management', 'icon' => 'fa-ad', 'notifications' => 0],
        ]
    ],
    'analytics' => [
        'title' => 'Analytics',
        'items' => [
            'search-log' => ['title' => 'Search Log', 'icon' => 'fa-search', 'notifications' => 0],
            'page-activity' => ['title' => 'Page Activity', 'icon' => 'fa-chart-line', 'notifications' => 0],
            'sessions' => ['title' => 'Sessions', 'icon' => 'fa-history', 'notifications' => 0],
        ]
    ],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/favicon.png">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D92B13',
                        secondary: '#1a1a1a',
                        'gray-text': '#4B5563',
                    },
                    fontFamily: {
                        rubik: ['Rubik', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Rubik', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #C00000;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .user-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background-color: #C00000;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .nav-category {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #9CA3AF;
            margin: 1.25rem 0 0.5rem 0.75rem;
            letter-spacing: 0.05em;
        }

        .nav-category:first-of-type {
            margin-top: 0;
        }
    </style>
</head>

<body class="bg-gray-50 font-rubik">
    <div class="flex min-h-screen">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <div class="h-16 px-6 flex items-center border-b border-gray-100">
                    <a href="<?= BASE_URL ?>admin/dashboard" class="flex items-center space-x-3">
                        <img src="<?= BASE_URL ?>img/logo_alt.png" alt="Logo" class="h-8 w-auto">
                    </a>
                </div>

                <nav id="sidebarNav" class="flex-1 overflow-y-auto py-6 px-4 pt-0 pb-1">
                    <?php foreach ($menuItems as $categoryKey => $category): ?>
                        <div class="nav-category"><?= htmlspecialchars($category['title']) ?></div>
                        <div class="space-y-1 mb-2">
                            <?php foreach ($category['items'] as $itemKey => $item): ?>
                                <a href="<?= BASE_URL ?>admin/<?= $itemKey ?>"
                                    id="nav-<?= $itemKey ?>"
                                    class="group flex items-center justify-between px-4 py-2.5 text-sm rounded-lg transition-all duration-200 <?= $activeNav === $itemKey ? 'bg-primary/10 text-primary active-nav-item' : 'text-gray-text hover:bg-gray-50 hover:text-primary' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fas <?= $item['icon'] ?> w-5 h-5"></i>
                                        <span><?= htmlspecialchars($item['title']) ?></span>
                                    </div>
                                    <?php if ($item['notifications'] > 0): ?>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-primary text-white min-w-[1.5rem]">
                                            <?= $item['notifications'] ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>

                <div class="p-4 border-t border-gray-100">
                    <div class="space-y-2">
                        <a href="users" class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-primary transition-colors duration-200">
                            <i class="fas fa-users w-5 h-5 mr-3"></i>
                            Users
                        </a>
                        <a href="profile" class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-primary transition-colors duration-200">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i>
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 lg:ml-72">
            <header class="sticky top-0 z-40 bg-white border-b border-gray-100">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <button id="sidebarToggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500 hover:text-primary rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-secondary"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <a href="<?= BASE_URL ?>admin/notifications">
                                <button class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-50 relative">
                                    <i class="fas fa-bell text-gray-500"></i>
                                    <span class="absolute top-2 right-2 w-2 h-2 bg-primary rounded-full"></span>
                                </button>
                            </a>
                        </div>

                        <div class="relative" id="userDropdown">
                            <button class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-3 py-2">
                                <div class="user-initials"><?= htmlspecialchars($userInitials) ?></div>
                                <div class="hidden md:block text-left">
                                    <span class="block text-sm font-medium text-gray-700"><?= htmlspecialchars($userName) ?></span>
                                    <span class="block text-xs text-gray-500">Last login: <?= htmlspecialchars($lastLogin) ?></span>
                                </div>
                                <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                            </button>
                            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg border border-gray-100 py-2 z-50">
                                <a href="profile" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user w-5 h-5 text-gray-400"></i>
                                    Profile
                                </a>
                                <a href="<?= BASE_URL ?>admin/settings" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog w-5 h-5 text-gray-400"></i>
                                    Settings
                                </a>
                                <div class="my-2 border-t border-gray-100"></div>
                                <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="flex items-center gap-3 px-4 py-2.5 text-sm text-primary hover:bg-gray-50">
                                    <i class="fas fa-sign-out-alt w-5 h-5"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <?= $mainContent ?? '' ?>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black/20 z-40 lg:hidden hidden';
        document.body.appendChild(overlay);

        function toggleSidebar() {
            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                sidebar.classList.add('animate-slide-in');
                setTimeout(() => {
                    sidebar.classList.remove('animate-slide-in');
                }, 300);
            }
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');

        userDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            userDropdownMenu.classList.add('hidden');
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const activeNavItem = document.querySelector('.active-nav-item');
            const sidebarNav = document.getElementById('sidebarNav');

            if (activeNavItem && sidebarNav) {
                const scrollTop = activeNavItem.offsetTop - sidebarNav.offsetTop - 20;
                sidebarNav.scrollTo({
                    top: scrollTop,
                    behavior: 'smooth'
                });
            }
        });

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
</body>

</html>