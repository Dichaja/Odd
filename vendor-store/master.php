<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
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
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 7200) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$_SESSION['last_activity'] = time();
if (!isset($_SESSION['store_session_id']) || $_SESSION['store_session_id'] !== $storeId) {
    $_SESSION['store_session_id'] = $storeId;
}
$title = isset($pageTitle) ? "{$pageTitle} - {$storeName} | Store Dashboard" : "{$storeName} Store Dashboard";
$activeNav = $activeNav ?? 'dashboard';
$userName = $_SESSION['user']['username'];
$storeInitials = '';
$parts = array_filter(explode(' ', $storeName));
$limitedParts = array_slice($parts, 0, 2);
foreach ($limitedParts as $part) {
    $storeInitials .= strtoupper($part[0]);
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
    'communication' => [
        'title' => 'Communication',
        'items' => [
            'sms-center' => ['title' => 'SMS Center', 'icon' => 'fa-comment-dots', 'notifications' => 0],
            'email-center' => ['title' => 'Email Center', 'icon' => 'fa-envelope', 'notifications' => 0],
        ],
    ],
    'store' => [
        'title' => 'Store Management',
        'items' => [
            'buy-in-store' => ['title' => 'Buy in Store', 'icon' => 'fa-calendar-check', 'notifications' => 0],
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
$sessionUlid = generateUlid();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="<?= BASE_URL ?>track/eventLog.js?v=<?= time() ?>"></script>
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const SESSION_ULID = "<?= $sessionUlid; ?>";
        tailwind.config = {
            theme: {
                extend: {
                    colors: { primary: '#D92B13', 'user-primary': '#D92B13', 'user-secondary': '#F8C2BC', 'user-content': '#F5F9FF', 'gray-text': '#4B5563' },
                    fontFamily: { rubik: ['Rubik', 'sans-serif'] }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Rubik', sans-serif
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px
        }

        ::-webkit-scrollbar-thumb {
            background: #0070C0;
            border-radius: 4px
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%)
            }

            to {
                transform: translateX(0)
            }
        }

        .animate-slide-in {
            animation: slideIn .3s ease-out
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
            font-size: .875rem
        }

        .nav-category {
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #9CA3AF;
            margin: 1.25rem 0 .5rem .75rem;
            letter-spacing: .05em
        }

        .nav-category:first-of-type {
            margin-top: 0
        }

        .user-sidebar {
            background: #F8FAFC;
            border-right: 1px solid #E2E8F0
        }

        .user-nav-item.active {
            background: rgba(192, 26, 0, .1);
            color: #D92B13
        }

        .user-header {
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05)
        }

        .main-content-area {
            background: #F0F7FF
        }
    </style>
</head>

<body class="bg-user-content font-rubik">
    <div class="flex min-h-screen">
        <aside id="sidebar"
            class="user-sidebar fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
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
                                <a href="<?= BASE_URL ?>vendor-store/<?=
                                      $key ?>"
                                    class="user-nav-item group flex items-center justify-between px-4 py-2.5 text-sm rounded-lg transition duration-200 <?= $activeNav === $key ? 'active' : 'text-gray-text hover:bg-gray-50 hover:text-user-primary' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fas <?= $item['icon'] ?> w-5 h-5"></i>
                                        <span><?= htmlspecialchars($item['title']) ?></span>
                                    </div>
                                    <?php if ($item['notifications'] > 0): ?>
                                        <span
                                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-user-primary text-white min-w-[1.5rem]"><?= $item['notifications'] ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>
        <div class="flex-1 lg:ml-64 flex flex-col justify-between">
            <header class="user-header sticky top-0 z-40 border-b border-gray-100">
                <div class="flex h-16 items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <button id="sidebarToggle"
                            class="lg:hidden w-10 h-10 flex items-center justify-center text-gray-500 hover:text-user-primary rounded-lg hover:bg-gray-50">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-secondary truncate whitespace-nowrap overflow-hidden max-w-[8rem] sm:max-w-none"
                            title="<?= htmlspecialchars($storeName) ?>">
                            <?= htmlspecialchars($storeName) ?>
                        </h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <div x-data="notifComponent()" x-init="init()" class="relative mr-2">
                            <button @click="open = !open"
                                class="relative w-10 h-10 flex items-center justify-center text-gray-500 hover:text-user-primary rounded-lg hover:bg-gray-50">
                                <i class="fas fa-bell text-xl"></i>
                                <span x-show="count > 0" x-text="count"
                                    class="absolute -top-1 -right-1 text-[10px] font-semibold text-white bg-user-primary rounded-full h-4 w-4 grid place-items-center"></span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                class="fixed top-14 left-2 right-2 w-auto max-w-full sm:absolute sm:top-auto sm:left-auto sm:right-0 sm:mt-2 sm:w-80 sm:max-w-none bg-white rounded-lg shadow-lg border border-gray-100 z-50 max-h-96 overflow-auto">
                                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="selectAll" @change="selectAll($event)"
                                            class="h-4 w-4 text-user-primary border-gray-300 rounded">
                                        <label for="selectAll" class="text-xs text-gray-600">Select All</label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="markBulkSeen"
                                            class="text-xs px-2 py-1 bg-user-primary text-white rounded hover:bg-opacity-90 transition">Mark
                                            Read</button>
                                        <button @click="dismissBulk"
                                            class="text-xs px-2 py-1 bg-red-500 text-white rounded hover:bg-opacity-90 transition">Dismiss</button>
                                    </div>
                                </div>
                                <template x-for="note in notes" :key="note.target_id">
                                    <div :class="note.is_seen == 0 ? 'bg-user-secondary/20' : 'bg-white'"
                                        class="relative group border-b last:border-none flex items-start">
                                        <div class="px-3 py-3">
                                            <input type="checkbox" :value="note.target_id" x-model="selected"
                                                class="h-4 w-4 text-user-primary border-gray-300 rounded">
                                        </div>
                                        <div class="flex-1">
                                            <a :href="note.link_url || '#'" class="block px-0 py-3"
                                                @click.prevent="handleClick(note)">
                                                <p class="text-sm font-medium" x-text="note.title"></p>
                                                <p class="text-xs mt-1"
                                                    :class="note.is_seen == 0 ? 'text-secondary' : 'text-gray-500'"
                                                    x-text="note.message"></p>
                                                <span class="text-[10px] text-gray-400"
                                                    x-text="formatDate(note.created_at)"></span>
                                            </a>
                                        </div>
                                        <button @click.stop="dismiss(note.target_id)"
                                            class="absolute top-2 right-2 text-gray-300 hover:text-user-primary opacity-0 group-hover:opacity-100 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                                <div x-show="notes.length === 0" class="p-4 text-sm text-center text-gray-500">No
                                    notifications</div>
                            </div>
                        </div>
                        <div class="relative" id="userDropdown">
                            <button class="flex items-center gap-3 hover:bg-gray-50 rounded-lg px-3 py-2"
                                title="<?= htmlspecialchars($storeName) ?> (<?= $userRole ?>)">
                                <div class="user-initials"><?= htmlspecialchars($storeInitials) ?></div>
                                <span
                                    class="hidden md:block text-sm font-medium text-gray-700"><?= htmlspecialchars($userName) ?></span>
                                <i class="fas fa-chevron-down text-sm text-gray-400"></i>
                            </button>
                            <div id="userDropdownMenu"
                                class="hidden absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-lg border border-gray-100 py-2 z-50">
                                <div class="px-4 py-3 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($userName) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($storeName) ?></p>
                                    <p class="text-xs text-gray-500 mt-1">Role: <?= $userRole ?></p>
                                </div>
                                <a href="<?= BASE_URL ?>account/dashboard"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user-circle w-5 h-5 text-gray-400"></i>User Dashboard
                                </a>
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
            <main class="main-content-area p-6 h-[100%]">
                <?= $mainContent ?? '' ?>
            </main>
            <footer class="bg-white border-t border-gray-100 py-4 px-6 text-center text-sm text-gray-500">
                &copy; <?= date('Y') ?> Zzimba Online. All rights reserved.
            </footer>
        </div>
    </div>
    <script>
        const LOGGED_USER = <?= isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null'; ?>;
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const overlay = Object.assign(document.createElement('div'), { className: 'fixed inset-0 bg-black/20 z-40 lg:hidden hidden' });
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
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        userDropdown.addEventListener('click', e => { e.stopPropagation(); userDropdownMenu.classList.toggle('hidden'); });
        document.addEventListener('click', () => userDropdownMenu.classList.add('hidden'));
        window.addEventListener('resize', () => {
            if (innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
        function logoutUser() {
            $.ajax({
                url: BASE_URL + 'auth/logout',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                success(d) { if (d.success) location.href = BASE_URL; else alert(d.message || 'Logout failed') },
                error() { alert('Server error') }
            });
        }
        function notifComponent() {
            return {
                open: false,
                notes: [],
                count: 0,
                selected: [],
                lastTs: null,
                timer: null,
                init() {
                    this.fetchNow();
                    this.timer = setInterval(() => this.fetchNow(), 20000);
                    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') this.fetchNow(); });
                },
                fetchNow() {
                    const url = new URL(BASE_URL + 'fetch/manageNotifications.php');
                    url.searchParams.set('action', 'fetch');
                    if (this.lastTs) url.searchParams.set('since', this.lastTs);
                    fetch(url.toString(), { cache: 'no-store' })
                        .then(r => r.json())
                        .then(res => {
                            if (res && res.status === 'success') {
                                const incoming = res.data || [];
                                if (this.lastTs) this.mergeIncoming(incoming);
                                else this.notes = incoming;
                                const latest = res.latest_ts || (incoming[0]?.created_at ?? this.lastTs);
                                if (latest) this.lastTs = latest;
                                const nextCount = Number.isInteger(res.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                                this.count = nextCount;
                                this.selected = this.selected.filter(id => this.notes.some(n => n.target_id === id));
                                const selAll = document.getElementById('selectAll');
                                if (selAll) selAll.checked = (this.selected.length && this.selected.length === this.notes.length);
                            }
                        })
                        .catch(() => { });
                },
                mergeIncoming(arr) {
                    if (!Array.isArray(arr) || !arr.length) return;
                    const byId = new Map(this.notes.map(n => [n.target_id, n]));
                    for (const n of arr) {
                        if (!byId.has(n.target_id)) {
                            this.notes.unshift(n);
                            byId.set(n.target_id, n);
                        } else {
                            const idx = this.notes.findIndex(x => x.target_id === n.target_id);
                            if (idx >= 0) this.notes[idx] = n;
                        }
                    }
                    this.notes = this.notes.slice(0, 100);
                },
                selectAll(e) { this.selected = e.target.checked ? this.notes.map(n => n.target_id) : [] },
                markSeen(id) {
                    const p = new URLSearchParams(); p.append('action', 'markSeen'); p.append('target_id', id);
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json())
                        .then(res => {
                            const note = this.notes.find(n => n.target_id === id);
                            if (note) note.is_seen = 1;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.count = c;
                        }).catch(() => { });
                },
                markBulkSeen() {
                    if (!this.selected.length) return;
                    const ids = this.selected.slice();
                    const p = new URLSearchParams(); p.append('action', 'markSeen'); ids.forEach(id => p.append('target_id[]', id));
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json())
                        .then(res => {
                            this.notes.forEach(n => { if (ids.includes(n.target_id)) n.is_seen = 1; });
                            this.selected = [];
                            const b = document.getElementById('selectAll'); if (b) b.checked = false;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.count = c;
                        }).catch(() => { });
                },
                handleClick(note) { if (note.is_seen == 0) this.markSeen(note.target_id); if (note.link_url) location.href = note.link_url },
                dismiss(id) {
                    const p = new URLSearchParams(); p.append('action', 'dismiss'); p.append('target_id', id);
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json())
                        .then(res => {
                            this.notes = this.notes.filter(n => n.target_id !== id);
                            this.selected = this.selected.filter(sid => sid !== id);
                            const b = document.getElementById('selectAll'); if (b) b.checked = (this.selected.length === this.notes.length && this.notes.length > 0);
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.count = c;
                        }).catch(() => { });
                },
                dismissBulk() {
                    if (!this.selected.length) return;
                    const ids = this.selected.slice();
                    const p = new URLSearchParams(); p.append('action', 'dismiss'); ids.forEach(id => p.append('target_id[]', id));
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json())
                        .then(res => {
                            this.notes = this.notes.filter(n => !ids.includes(n.target_id));
                            this.selected = [];
                            const b = document.getElementById('selectAll'); if (b) b.checked = false;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.count = c;
                        }).catch(() => { });
                },
                formatDate(ts) {
                    const d = new Date(ts.replace(' ', 'T')); const now = new Date(); const diff = (now - d) / 1000;
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
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