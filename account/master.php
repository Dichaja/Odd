<?php
require_once __DIR__ . '/../config/config.php';

session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if ($_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php?session_expired=1');
    exit;
}

$_SESSION['last_activity'] = time();

$stmt = $pdo->prepare("SELECT last_login FROM zzimba_users WHERE id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user']['user_id'], PDO::PARAM_LOB);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$lastLogin = $result['last_login'] ?? '';
$formattedLastLogin = $lastLogin ? date('M d, Y g:i A', strtotime($lastLogin)) : 'First login';

$title = isset($pageTitle) ? $pageTitle . ' | User Dashboard - Zzimba Online' : 'User Dashboard';
$activeNav = $activeNav ?? 'dashboard';

$userName = $_SESSION['user']['username'];
$userEmail = $_SESSION['user']['email'];
$userInitials = '';
$nameParts = explode(' ', $userName);
foreach ($nameParts as $part) {
    if (!empty($part)) {
        $userInitials .= strtoupper(substr($part, 0, 1));
    }
}

$menuItems = [
    'main' => [
        'title' => 'Main',
        'items' => [
            'dashboard' => ['title' => 'Dashboard', 'icon' => 'fa-home', 'notifications' => 0],
            'order-history' => ['title' => 'Order History', 'icon' => 'fa-history', 'notifications' => 0],
        ]
    ],
    'finance' => [
        'title' => 'Finance',
        'items' => [
            'zzimba-credit' => ['title' => 'Zzimba Credit', 'icon' => 'fa-credit-card', 'notifications' => 0],
        ]
    ],
    'shopping' => [
        'title' => 'Shopping',
        'items' => [
            'zzimba-store' => ['title' => 'Zzimba Store', 'icon' => 'fa-shopping-cart', 'notifications' => 0],
            'wishlist' => ['title' => 'Wishlist', 'icon' => 'fa-heart', 'notifications' => 0],
            'saved-items' => ['title' => 'Saved Items', 'icon' => 'fa-bookmark', 'notifications' => 0],
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D92B13',
                        secondary: '#1a1a1a',
                        'gray-text': '#4B5563',
                        'user-primary': '#D92B13',
                        'user-secondary': '#F8C2BC',
                        'user-accent': '#E6F2FF',
                        'user-content': '#F5F9FF',
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
            background-color: #0070C0;
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
            background-color: #D92B13;
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

        .user-sidebar {
            background-color: #F8FAFC;
            border-right: 1px solid #E2E8F0;
        }

        .user-nav-item.active {
            background-color: rgba(192, 26, 0, 0.1);
            color: #D92B13;
        }

        .user-header {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .content-section {
            background-color: #F5F9FF;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #E6F2FF;
        }

        .content-header {
            background-color: #E6F2FF;
            border-bottom: 1px solid #CCE5FF;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .user-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #E6F2FF;
            transition: all 0.2s ease;
        }

        .user-card:hover {
            box-shadow: 0 4px 6px rgba(0, 112, 192, 0.1);
            transform: translateY(-2px);
        }

        .main-content-area {
            background-color: #F0F7FF;
        }
    </style>
</head>

<body class="bg-user-content font-rubik">
    <div class="flex min-h-screen">
        <aside id="sidebar" class="user-sidebar fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <div class="h-16 px-6 flex items-center border-b border-gray-100">
                    <a href="<?= BASE_URL ?>account/dashboard" class="flex items-center space-x-3">
                        <img src="<?= BASE_URL ?>img/logo_alt.png" alt="Logo" class="h-8 w-auto">
                    </a>
                </div>

                <nav id="sidebarNav" class="flex-1 overflow-y-auto py-6 px-4 pt-0 pb-1">
                    <?php foreach ($menuItems as $categoryKey => $category): ?>
                        <div class="nav-category"><?= htmlspecialchars($category['title']) ?></div>
                        <div class="space-y-1 mb-2">
                            <?php foreach ($category['items'] as $itemKey => $item): ?>
                                <a href="<?= BASE_URL ?>account/<?= $itemKey ?>"
                                    id="nav-<?= $itemKey ?>"
                                    class="user-nav-item group flex items-center justify-between px-4 py-2.5 text-sm rounded-lg transition-all duration-200 <?= $activeNav === $itemKey ? 'active' : 'text-gray-text hover:bg-gray-50 hover:text-user-primary' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fas <?= $item['icon'] ?> w-5 h-5"></i>
                                        <span><?= htmlspecialchars($item['title']) ?></span>
                                    </div>
                                    <?php if ($item['notifications'] > 0): ?>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-user-primary text-white min-w-[1.5rem]">
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
                        <a href="profile" class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-user-primary transition-colors duration-200">
                            <i class="fas fa-user w-5 h-5 mr-3"></i>
                            My Profile
                        </a>
                        <a href="settings" class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-user-primary transition-colors duration-200">
                            <i class="fas fa-cog w-5 h-5 mr-3"></i>
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 lg:ml-64">
            <header class="user-header sticky top-0 z-40 border-b border-gray-100">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <button id="sidebarToggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500 hover:text-user-primary rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-secondary"><?= htmlspecialchars($pageTitle ?? 'My Dashboard') ?></h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <a href="<?= BASE_URL ?>account/notifications">
                                <button class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-50 relative">
                                    <i class="fas fa-bell text-gray-500"></i>
                                    <span class="absolute top-2 right-2 w-2 h-2 bg-user-primary rounded-full"></span>
                                </button>
                            </a>
                        </div>

                        <div class="relative">
                            <a href="<?= BASE_URL ?>cart">
                                <button class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-50 relative">
                                    <i class="fas fa-shopping-cart text-gray-500"></i>
                                    <span class="absolute top-2 right-2 w-2 h-2 bg-user-primary rounded-full"></span>
                                </button>
                            </a>
                        </div>

                        <div class="relative" id="userDropdown">
                            <button class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-3 py-2" title="Last login: <?= htmlspecialchars($formattedLastLogin) ?>">
                                <div class="user-initials"><?= htmlspecialchars($userInitials) ?></div>
                                <span class="hidden md:block text-sm font-medium text-gray-700"><?= htmlspecialchars($userName) ?></span>
                                <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                            </button>
                            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg border border-gray-100 py-2 z-50">
                                <div class="px-4 py-3 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($userName) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($userEmail) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Last login: <?= htmlspecialchars($formattedLastLogin) ?></p>
                                </div>
                                <a href="profile" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user w-5 h-5 text-gray-400"></i>
                                    My Profile
                                </a>
                                <a href="orders" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-shopping-bag w-5 h-5 text-gray-400"></i>
                                    My Orders
                                </a>
                                <a href="settings" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog w-5 h-5 text-gray-400"></i>
                                    Settings
                                </a>
                                <div class="my-2 border-t border-gray-100"></div>
                                <a href="javascript:void(0);" onclick="logoutUser(); return false;" class="flex items-center gap-3 px-4 py-2.5 text-sm text-user-primary hover:bg-gray-50">
                                    <i class="fas fa-sign-out-alt w-5 h-5"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="main-content-area p-6">
                <?= $mainContent ?? '' ?>
            </main>

            <footer class="bg-white border-t border-gray-100 py-4 px-6 text-center text-sm text-gray-500">
                <p>&copy; <?= date('Y') ?> Zzimba Online. All rights reserved.</p>
            </footer>
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
            const activeNavItem = document.querySelector('.user-nav-item.active');
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
            $.ajax({
                url: BASE_URL + 'auth/logout',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = BASE_URL;
                    } else {
                        alert(response.message || 'Failed to logout');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Logout error:", xhr, status, error);
                    alert('Failed to connect to the server. Please try again.');
                }
            });
        }
    </script>
</body>

</html>