<?php
require_once __DIR__ . '/../config/config.php';

// Auth: must be logged in
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}

// Store context
$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}

// Fetch store and resolve role
$storeStmt = $pdo->prepare("
    SELECT id, name, owner_id 
    FROM vendor_stores
    WHERE id = :sid 
      AND status IN ('active','pending','inactive','suspended')
");
$storeStmt->execute([':sid' => $storeId]);
$store = $storeStmt->fetch(PDO::FETCH_ASSOC);
if (!$store) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$storeName = $store['name'];

$isAdmin = !empty($_SESSION['user']['is_admin']);
$isOwner = $store['owner_id'] === $_SESSION['user']['user_id'];
$isManager = false;
if (!$isAdmin && !$isOwner) {
    $mgr = $pdo->prepare("
        SELECT 1 
        FROM store_managers 
        WHERE store_id = :sid 
          AND user_id = :uid 
          AND status = 'active' 
          AND approved = 1 
        LIMIT 1
    ");
    $mgr->execute([
        ':sid' => $storeId,
        ':uid' => $_SESSION['user']['user_id']
    ]);
    $isManager = (bool) $mgr->fetchColumn();
}
if (!$isAdmin && !$isOwner && !$isManager) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$userRole = $isAdmin ? 'Admin' : ($isOwner ? 'Owner' : 'Manager');

// Session timeout
if (
    isset($_SESSION['last_activity'])
    && time() - $_SESSION['last_activity'] > 1800
) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$_SESSION['last_activity'] = time();

// Ensure store_session_id for notifications
if (
    !isset($_SESSION['store_session_id'])
    || $_SESSION['store_session_id'] !== $storeId
) {
    $_SESSION['store_session_id'] = $storeId;
}

// Page settings
$title = isset($pageTitle)
    ? "{$pageTitle} - {$storeName} | Zzimba Online"
    : "{$storeName} Dashboard | Zzimba Online";
$activeNav = $activeNav ?? 'dashboard';
$userName = $_SESSION['user']['username'];
// Store initials for avatar
$storeInitials = '';
foreach (explode(' ', $storeName) as $part) {
    if ($part !== '') {
        $storeInitials .= strtoupper($part[0]);
    }
}

$menuItems = [
    'main' => [
        'title' => 'Main',
        'items' => [
            'dashboard' => ['title' => 'Dashboard', 'icon' => 'fa-home', 'notifications' => 0],
            'orders' => ['title' => 'Orders', 'icon' => 'fa-shopping-bag', 'notifications' => 0],
            'order-history' => ['title' => 'Order History', 'icon' => 'fa-history', 'notifications' => 0],
        ]
    ],
    'finance' => [
        'title' => 'Finance',
        'items' => [
            'zzimba-credit' => ['title' => 'Zzimba Credit', 'icon' => 'fa-credit-card', 'notifications' => 0],
        ]
    ],
    'store' => [
        'title' => 'Store Management',
        'items' => [
            'requests' => ['title' => 'Requests', 'icon' => 'fa-calendar-check', 'notifications' => 0],
            'products' => ['title' => 'Products', 'icon' => 'fa-box-open', 'notifications' => 0],
            'categories' => ['title' => 'Categories', 'icon' => 'fa-tags', 'notifications' => 0],
            'managers' => ['title' => 'Managers', 'icon' => 'fa-users', 'notifications' => 0],
        ]
    ],
    'settings' => [
        'title' => 'Settings',
        'items' => [
            'store-profile' => ['title' => 'Store Profile', 'icon' => 'fa-store', 'notifications' => 0],
            'settings' => ['title' => 'Settings', 'icon' => 'fa-cog', 'notifications' => 0],
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Tailwind & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/favicon.png">

    <!-- jQuery & Alpine -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#D92B13',
                        'user-primary': '#D92B13',
                        'user-secondary': '#F8C2BC',
                        'user-content': '#F5F9FF'
                    },
                    fontFamily: {
                        rubik: ['Rubik', 'sans-serif']
                    }
                }
            }
        };
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
            background: #0070C0;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
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
            animation: slideIn .3s ease-out;
        }

        .user-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: #D92B13;
            color: #fff;
            font-weight: 600;
            font-size: .875rem;
        }

        .nav-category {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #9CA3AF;
            margin: 1.25rem 0 .5rem .75rem;
            letter-spacing: .05em;
        }

        .nav-category:first-of-type {
            margin-top: 0;
        }

        .user-sidebar {
            background: #F8FAFC;
            border-right: 1px solid #E2E8F0;
        }

        .user-nav-item.active {
            background: rgba(192, 26, 0, .1);
            color: #D92B13;
        }

        .user-header {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
        }

        .main-content-area {
            background: #F0F7FF;
        }
    </style>
</head>

<body class="bg-user-content font-rubik">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside id="sidebar" class="user-sidebar fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full
                  lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <div class="h-16 px-6 flex items-center border-b border-gray-100">
                    <a href="<?= BASE_URL ?>vendor-store/dashboard" class="flex items-center space-x-3">
                        <img src="<?= BASE_URL ?>img/logo_alt.png" alt="Logo" class="h-8 w-auto">
                    </a>
                </div>
                <nav id="sidebarNav" class="flex-1 overflow-y-auto py-6 px-4 pt-0 pb-1">
                    <?php foreach ($menuItems as $category): ?>
                        <div class="nav-category"><?= htmlspecialchars($category['title']) ?></div>
                        <div class="space-y-1 mb-2">
                            <?php foreach ($category['items'] as $key => $item): ?>
                                <a href="<?= BASE_URL ?>vendor-store/<?= $key ?>" class="user-nav-item group flex items-center justify-between px-4 py-2.5 text-sm rounded-lg
                                      transition duration-200 <?= $activeNav === $key
                                          ? 'active' : 'text-gray-text hover:bg-gray-50 hover:text-user-primary' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fas <?= $item['icon'] ?> w-5 h-5"></i>
                                        <span><?= htmlspecialchars($item['title']) ?></span>
                                    </div>
                                    <?php if ($item['notifications'] > 0): ?>
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium
                                               rounded-full bg-user-primary text-white min-w-[1.5rem]">
                                            <?= $item['notifications'] ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 lg:ml-64">

            <!-- Header -->
            <header class="user-header sticky top-0 z-40 border-b border-gray-100">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <button id="sidebarToggle" class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500
                                   hover:text-user-primary rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-secondary truncate"
                            title="<?= htmlspecialchars($storeName) ?>">
                            <?= htmlspecialchars($storeName) ?>
                        </h1>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- SSE Notifications -->
                        <div x-data="notifComponent()" x-init="init()" class="relative mr-2">
                            <button @click="open = !open" class="relative w-10 h-10 flex items-center justify-center text-gray-500
                                       hover:text-user-primary rounded-lg hover:bg-gray-50">
                                <i class="fas fa-bell text-xl"></i>
                                <span x-show="count>0" x-text="count" class="absolute -top-1 -right-1 text-[10px] font-semibold text-white
                                         bg-user-primary rounded-full h-4 w-4 grid place-items-center"></span>
                            </button>
                            <div x-show="open" @click.away="open=false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-100
                                    z-50 max-h-96 overflow-auto">
                                <template x-for="note in notes" :key="note.target_id">
                                    <div :class="note.is_seen==0 ? 'bg-user-secondary/20' : 'bg-white'"
                                        class="relative group border-b last:border-none">
                                        <a :href="note.link_url||'#'" class="block px-4 py-3"
                                            @click.prevent="handleClick(note)">
                                            <p class="text-sm font-medium" x-text="note.title"></p>
                                            <p class="text-xs mt-1"
                                                :class="note.is_seen==0?'text-secondary':'text-gray-500'"
                                                x-text="note.message"></p>
                                            <span class="text-[10px] text-gray-400"
                                                x-text="formatDate(note.created_at)"></span>
                                        </a>
                                        <button @click.stop="dismiss(note.target_id)" class="absolute top-2 right-2 text-gray-300 hover:text-user-primary
                                                   opacity-0 group-hover:opacity-100 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                                <div x-show="notes.length===0" class="p-4 text-sm text-center text-gray-500">
                                    No notifications
                                </div>
                            </div>
                        </div>

                        <!-- User dropdown -->
                        <div class="relative" id="userDropdown">
                            <button class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-3 py-2"
                                title="<?= htmlspecialchars($storeName) ?> (<?= $userRole ?>)">
                                <div class="user-initials"><?= htmlspecialchars($storeInitials) ?></div>
                                <span class="hidden md:block text-sm font-medium text-gray-700">
                                    <?= htmlspecialchars($userName) ?>
                                </span>
                                <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                            </button>
                            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg
                                    border border-gray-100 py-2 z-50">
                                <div class="px-4 py-3 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($userName) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($storeName) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Role: <?= $userRole ?></p>
                                </div>
                                <a href="<?= BASE_URL ?>vendor-store/store-profile"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-store w-5 h-5 text-gray-400"></i>Store Profile
                                </a>
                                <a href="<?= BASE_URL ?>vendor-store/orders"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-shopping-bag w-5 h-5 text-gray-400"></i>Orders
                                </a>
                                <a href="<?= BASE_URL ?>vendor-store/settings"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog w-5 h-5 text-gray-400"></i>Settings
                                </a>
                                <div class="my-2 border-t border-gray-100"></div>
                                <a href="javascript:void(0);" onclick="logoutUser();"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-user-primary hover:bg-gray-50">
                                    <i class="fas fa-sign-out-alt w-5 h-5"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="main-content-area p-6">
                <?= $mainContent ?? '' ?>
            </main>
            <footer class="bg-white border-t border-gray-100 py-4 px-6 text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> Zzimba Online. All rights reserved.
            </footer>
        </div>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar'),
            sidebarToggle = document.getElementById('sidebarToggle'),
            overlay = Object.assign(document.createElement('div'), {
                className: 'fixed inset-0 bg-black/20 z-40 lg:hidden hidden'
            });
        document.body.appendChild(overlay);
        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        function toggleSidebar() {
            const open = !sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full', open);
            overlay.classList.toggle('hidden', open);
            document.body.classList.toggle('overflow-hidden', !open);
            if (!open) {
                sidebar.classList.add('animate-slide-in');
                setTimeout(() => sidebar.classList.remove('animate-slide-in'), 300);
            }
        }

        // User dropdown
        const userDropdown = document.getElementById('userDropdown'),
            userDropdownMenu = document.getElementById('userDropdownMenu');
        userDropdown.addEventListener('click', e => {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => userDropdownMenu.classList.add('hidden'));
        window.addEventListener('resize', () => {
            if (innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        // Logout
        function logoutUser() {
            $.ajax({
                url: BASE_URL + 'auth/logout',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success(d) {
                    if (d.success) location.href = BASE_URL;
                    else alert(d.message || 'Logout failed');
                },
                error() {
                    alert('Server error');
                }
            });
        }

        // SSE notifications component
        function notifComponent() {
            return {
                open: false,
                notes: [],
                count: 0,
                evtSource: null,

                init() {
                    this.evtSource = new EventSource(
                        BASE_URL + 'fetch/manageNotifications.php?action=stream'
                    );
                    this.evtSource.onmessage = e => {
                        try {
                            const data = JSON.parse(e.data);
                            this.notes = data;
                            this.count = this.notes.filter(n => n.is_seen == 0).length;
                        } catch (err) {
                            console.error('SSE parse error', err);
                        }
                    };
                    this.evtSource.onerror = err => console.error('SSE error', err);
                },

                handleClick(note) {
                    if (note.is_seen == 0) this.markSeen(note.target_id);
                    if (note.link_url) location.href = note.link_url;
                },

                markSeen(id) {
                    fetch(BASE_URL + 'fetch/manageNotifications.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ action: 'markSeen', target_id: id })
                    });
                },

                dismiss(id) {
                    fetch(BASE_URL + 'fetch/manageNotifications.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ action: 'dismiss', target_id: id })
                    });
                    this.notes = this.notes.filter(n => n.target_id !== id);
                    this.count = this.notes.filter(n => n.is_seen == 0).length;
                },

                formatDate(ts) {
                    const d = new Date(ts.replace(' ', 'T'));
                    const now = new Date();
                    const diff = (now - d) / 1000;
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const yesterday = new Date(today);
                    yesterday.setDate(today.getDate() - 1);
                    const time = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                    if (diff < 60) return 'Now';
                    if (d >= today) return 'Today ' + time;
                    if (d >= yesterday && d < today) return 'Yesterday ' + time;
                    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                }
            };
        }
    </script>
</body>

</html>