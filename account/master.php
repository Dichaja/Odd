<?php
require_once __DIR__ . '/../config/config.php';
$appName = 'Zzimba';
if (isset($_GET['manifest'])) {
    header('Content-Type: application/manifest+json');
    $base = rtrim(BASE_URL, '/') . '/account/';
    $icon = rtrim(BASE_URL, '/') . '/img/favicon.png';
    echo json_encode([
        'name' => $appName,
        'short_name' => $appName,
        'start_url' => $base,
        'scope' => $base,
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => '#D92B13',
        'icons' => [
            ['src' => $icon, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $icon, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable']
        ]
    ], JSON_UNESCAPED_SLASHES);
    exit;
}
if (isset($_GET['sw'])) {
    header('Content-Type: application/javascript');
    $scope = rtrim(BASE_URL, '/') . '/account/';
    $cache = 'zzimba-user-v3';
    $core = json_encode([
        $scope,
        $scope . 'dashboard',
        rtrim(BASE_URL, '/') . '/img/favicon.png'
    ], JSON_UNESCAPED_SLASHES);
    echo <<<JS
const CACHE_NAME='$cache';
const CORE_ASSETS=$core;
self.addEventListener('install',e=>{
  e.waitUntil((async()=>{
    self.skipWaiting();
    const cache=await caches.open(CACHE_NAME);
    await Promise.all(CORE_ASSETS.map(async url=>{
      try{
        const res=await fetch(url,{cache:'no-store'});
        if(res && res.ok) await cache.put(url,res.clone());
      }catch(_){}
    }));
  })());
});
self.addEventListener('activate',e=>{
  e.waitUntil((async()=>{
    const keys=await caches.keys();
    await Promise.all(keys.filter(k=>k!==CACHE_NAME).map(k=>caches.delete(k)));
    await self.clients.claim();
  })());
});
self.addEventListener('fetch',e=>{
  const u=new URL(e.request.url);
  if(u.origin!==location.origin) return;
  e.respondWith((async()=>{
    try{
      const net=await fetch(e.request);
      const cache=await caches.open(CACHE_NAME);
      cache.put(e.request,net.clone()).catch(()=>{});
      return net;
    }catch(_){
      const cached=await caches.match(e.request);
      return cached || Response.error();
    }
  })());
});
JS;
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
if (!empty($_SESSION['user']['is_admin'])) {
    header('Location: ' . BASE_URL . 'admin/dashboard');
    exit;
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$_SESSION['last_activity'] = time();
$stmt = $pdo->prepare("
    SELECT 
        first_name,
        email,
        phone,
        last_login
    FROM zzimba_users
    WHERE id = :user_id
");
$stmt->execute([':user_id' => $_SESSION['user']['user_id']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
$needsProfileCompletion = empty($userRow['first_name']) || empty($userRow['email']) || empty($userRow['phone']);
$hasWallet = false;
$hasStore = false;
$hasPurchase = false;
try {
    $stmtW = $pdo->prepare("SELECT wallet_id FROM zzimba_wallets WHERE owner_type='USER' AND user_id = :uid LIMIT 1");
    $stmtW->execute([':uid' => $_SESSION['user']['user_id']]);
    $hasWallet = (bool) $stmtW->fetchColumn();
    $stmtS = $pdo->prepare("SELECT id FROM vendor_stores WHERE owner_id = :uid LIMIT 1");
    $stmtS->execute([':uid' => $_SESSION['user']['user_id']]);
    $hasStore = (bool) $stmtS->fetchColumn();
    $stmtP = $pdo->prepare("SELECT id FROM request_for_quote WHERE user_id = :uid LIMIT 1");
    $stmtP->execute([':uid' => $_SESSION['user']['user_id']]);
    $hasPurchase = (bool) $stmtP->fetchColumn();
} catch (Throwable $e) {
    $hasWallet = $hasWallet ?? false;
    $hasStore = $hasStore ?? false;
    $hasPurchase = $hasPurchase ?? false;
}
$lastLogin = $userRow['last_login'] ?? '';
$formattedLastLogin = $lastLogin ? date('M d, Y g:i A', strtotime($lastLogin)) : 'First login';
$title = isset($pageTitle) ? $pageTitle . ' | User Dashboard' : 'User Dashboard';
$activeNav = $activeNav ?? 'dashboard';
$userName = $_SESSION['user']['username'];
$userEmail = $_SESSION['user']['email'];
$userInitials = '';
foreach (explode(' ', $userName) as $part) {
    if ($part !== '')
        $userInitials .= strtoupper($part[0]);
}
$sessionUlid = generateUlid();
$steps = [
    'profile' => [
        'label' => 'Complete your profile',
        'done' => !$needsProfileCompletion,
        'url' => BASE_URL . 'account/profile',
        'icon' => 'fa-id-card',
        'optional' => false
    ],
    'wallet' => [
        'label' => 'Activate Zzimba Wallet',
        'done' => $hasWallet,
        'url' => BASE_URL . 'account/zzimba-credit',
        'icon' => 'fa-wallet',
        'optional' => false
    ],
    'purchase' => [
        'label' => 'Make 1st Purchase (Optional)',
        'done' => $hasPurchase,
        'url' => BASE_URL . 'request-for-quote',
        'icon' => 'fa-cart-shopping',
        'optional' => true
    ],
    'store' => [
        'label' => 'Create your Store (Optional)',
        'done' => $hasStore,
        'url' => BASE_URL . 'account/zzimba-stores',
        'icon' => 'fa-shop',
        'optional' => true
    ],
];
$orderedKeys = ['profile', 'wallet', 'purchase', 'store'];
$completed = 0;
foreach ($steps as $s) {
    if ($s['done'])
        $completed++;
}
$total = count($steps);
$progressPercent = (int) round(($completed / max(1, $total)) * 100);
$showOnboarding = ($completed < $total);
$firstIncompleteKey = null;
foreach ($orderedKeys as $k) {
    if (!$steps[$k]['done']) {
        $firstIncompleteKey = $k;
        break;
    }
}
$requiredDone = $steps['profile']['done'] && $steps['wallet']['done'];
$onlyOptionalRemain = $requiredDone && (!$steps['purchase']['done'] || !$steps['store']['done']);

/**
 * MENU: added "Communication" with sms-center & email-center
 */
$menuItems = [
    'main' => [
        'title' => 'Main',
        'items' => [
            'dashboard' => ['title' => 'Dashboard', 'icon' => 'fa-home', 'notifications' => 0],
            'order-history' => ['title' => 'Order History', 'icon' => 'fa-history', 'notifications' => 0],
        ],
    ],
    'finance' => [
        'title' => 'Finance',
        'items' => [
            'zzimba-credit' => ['title' => 'Zzimba Credit', 'icon' => 'fa-credit-card', 'notifications' => 0],
        ],
    ],
    'communication' => [
        'title' => 'Communication',
        'items' => [
            'sms-center' => ['title' => 'SMS Center', 'icon' => 'fa-comment-dots', 'notifications' => 0],
            'email-center' => ['title' => 'Email Center', 'icon' => 'fa-envelope', 'notifications' => 0],
        ],
    ],
    'shopping' => [
        'title' => 'Shopping',
        'items' => [
            'zzimba-stores' => ['title' => 'Zzimba Store', 'icon' => 'fa-shopping-cart', 'notifications' => 0],
            'quotations' => ['title' => 'My RFQs', 'icon' => 'fa-file-invoice', 'notifications' => 0],
        ],
    ],
];

if ($needsProfileCompletion) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '';
    if (strpos($currentPath, '/account/profile') === false) {
        header('Location: ' . BASE_URL . 'account/profile');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title><?= htmlspecialchars($title) ?></title>
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('zzimba_theme') || 'system';
                var dark = (mode === 'dark') || (mode === 'system' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (dark) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark');
                var m = document.createElement('meta'); m.name = 'theme-color'; m.id = 'meta-theme-color'; m.content = dark ? '#1a1a1a' : '#ffffff'; document.head.appendChild(m);
            } catch (e) { }
        })();
    </script>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>img/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>img/favicon.png">
    <link rel="manifest" href="master.php?manifest=1">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Zzimba Online">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="<?= BASE_URL ?>track/eventLog.js?v=<?= time() ?>"></script>
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const SESSION_ULID = "<?= $sessionUlid ?>";
        const PAGE_TITLE = "<?= addslashes($pageTitle ?? '') ?>";
        const APP_SCOPE = (new URL('account/', BASE_URL)).toString();
        const APP_ICON = (new URL('img/favicon.png', BASE_URL)).toString();
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { primary: '#D92B13', secondary: '#1a1a1a', 'gray-text': '#4B5563', 'user-primary': '#D92B13', 'user-secondary': '#F8C2BC', 'user-accent': '#E6F2FF', 'user-content': '#F5F9FF' },
                    fontFamily: { rubik: ['Rubik', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        * {
            -webkit-tap-highlight-color: transparent
        }

        body {
            font-family: 'Rubik', sans-serif
        }

        ::-webkit-scrollbar {
            width: 4px;
            height: 4px
        }

        ::-webkit-scrollbar-thumb {
            background-color: #0070C0;
            border-radius: 4px
        }

        ::-webkit-scrollbar-track {
            background-color: #f1f1f1
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
            border-radius: 9999px;
            background-color: #D92B13;
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

        .user-sidebar {
            background-color: #F8FAFC;
            border-right: 1px solid #E2E8F0
        }

        .user-nav-item.active {
            background-color: rgba(192, 26, 0, .1);
            color: #D92B13
        }

        .user-header {
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05)
        }

        .main-content-area {
            background-color: #F0F7FF
        }

        .mobile-tabbar {
            height: 60px;
            padding-bottom: max(10px, env(safe-area-inset-bottom))
        }

        .safe-bottom {
            padding-bottom: max(60px, env(safe-area-inset-bottom))
        }

        .sheet {
            transform: translateY(100%);
            transition: transform .25s ease
        }

        .sheet.open {
            transform: translateY(0)
        }

        .theme-pill {
            border: 1px solid rgba(0, 0, 0, .08)
        }

        .dark .user-sidebar {
            background-color: var(--tw-color-secondary, #1a1a1a);
            border-right-color: rgba(255, 255, 255, .08)
        }

        .dark .nav-category {
            color: #c5c5c5
        }

        .dark .user-header {
            background: var(--tw-color-secondary, #1a1a1a);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            box-shadow: none
        }

        .dark .user-nav-item.active {
            background-color: rgba(255, 255, 255, .08);
            color: #fff
        }

        .dark .user-nav-item {
            color: #e5e5e5
        }

        .dark .user-nav-item:hover {
            background: rgba(255, 255, 255, .06);
            color: #fff
        }

        .dark .main-content-area {
            background-color: var(--tw-color-secondary, #1a1a1a)
        }

        .dark .theme-pill {
            border-color: rgba(255, 255, 255, .12)
        }

        .dark .mobile-tabbar {
            border-top-color: rgba(255, 255, 255, .12)
        }

        .splash {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 90
        }

        .splash--light {
            background: radial-gradient(1200px 600px at 10% -10%, rgba(217, 43, 19, .10), transparent 60%), radial-gradient(900px 500px at 100% 0%, rgba(217, 43, 19, .12), transparent 60%), linear-gradient(135deg, #fff7ed 0%, #f7efe9 100%)
        }

        .splash--dark {
            background: radial-gradient(1200px 600px at 10% -10%, rgba(217, 43, 19, .08), transparent 60%), radial-gradient(900px 500px at 100% 0%, rgba(217, 43, 19, .10), transparent 60%), linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%)
        }

        .logo-pulse {
            animation: logoPulse 1.6s ease-in-out infinite
        }

        @keyframes logoPulse {
            0% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(0, 0, 0, .25))
            }

            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 6px 12px rgba(0, 0, 0, .25))
            }

            100% {
                transform: scale(1);
                filter: drop-shadow(0 0 0 rgba(0, 0, 0, .25))
            }
        }

        .bar {
            height: 4px;
            width: 180px;
            border-radius: 9999px;
            overflow: hidden;
            background: rgba(0, 0, 0, .12)
        }

        .dark .bar {
            background: rgba(255, 255, 255, .15)
        }

        .bar::after {
            content: '';
            display: block;
            height: 100%;
            width: 40%;
            border-radius: 9999px;
            background: linear-gradient(90deg, #ffb4a8, #D92B13, #8f1406);
            animation: slide 1.2s ease-in-out infinite
        }

        @keyframes slide {
            0% {
                transform: translateX(-40%)
            }

            50% {
                transform: translateX(80%)
            }

            100% {
                transform: translateX(180%)
            }
        }

        .install-banner {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 70;
            padding: 12px max(12px, env(safe-area-inset-bottom))
        }

        .install-card {
            margin: 0 auto;
            max-width: 560px;
            background: rgba(255, 255, 255, .98);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .12)
        }

        .install-card .title {
            color: #1a1a1a
        }

        .install-card .sub {
            color: #4B5563
        }

        .install-card .later {
            border: 1px solid rgba(0, 0, 0, .2);
            color: #1a1a1a;
            background: #ffffff
        }

        .install-card .install {
            background: #D92B13;
            color: #ffffff
        }

        .dark .install-card {
            background: rgba(26, 26, 26, .95);
            border-color: rgba(255, 255, 255, .10)
        }

        .dark .install-card .title {
            color: #ffffff
        }

        .dark .install-card .sub {
            color: rgba(255, 255, 255, .7)
        }

        .dark .install-card .later {
            border: 1px solid rgba(255, 255, 255, .2);
            color: #ffffff;
            background: transparent
        }

        .sheet-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4)
        }

        .main-fixed {
            height: calc(100vh - 64px);
            overflow: auto
        }

        .sheet a .fas {
            color: #1a1a1a
        }

        .dark .sheet a .fas {
            color: #ffffff
        }

        .sheet .sheet-close {
            color: #1a1a1a;
            border-color: #e5e7eb
        }

        .dark .sheet .sheet-close {
            color: #ffffff;
            border-color: rgba(255, 255, 255, .1)
        }

        input[type="checkbox"] {
            accent-color: #D92B13
        }

        .gs-card-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #fff3f1 100%)
        }

        .dark .gs-card-gradient {
            background: linear-gradient(135deg, #161616 0%, #1f1b1a 100%)
        }

        .gs-progress {
            height: 10px
        }

        .gs-step {
            transition: transform .15s ease
        }

        .gs-step:hover {
            transform: translateY(-1px)
        }

        .locked {
            filter: grayscale(1);
            opacity: .6
        }
    </style>
</head>

<body class="bg-user-content dark:bg-secondary font-rubik min-h-screen" x-data="themeRoot()" x-init="init()">
    <div id="zz-splash" class="splash splash--light">
        <div class="flex flex-col items-center gap-5">
            <div class="relative">
                <img src="<?= BASE_URL ?>img/favicon.png" alt="Zzimba Online"
                    class="relative h-16 w-16 rounded logo-pulse">
            </div>
            <div class="bar"></div>
        </div>
    </div>
    <div id="install-banner" class="install-banner hidden">
        <div class="install-card">
            <div class="flex items-center gap-3 p-3 sm:p-4">
                <img src="<?= BASE_URL ?>img/favicon.png" alt="Zzimba Online" class="h-9 w-9 rounded">
                <div class="flex-1">
                    <div class="font-medium text-[15px] title">Zzimba Online</div>
                    <div class="text-xs sub">Install for quicker access & a full-screen experience.</div>
                </div>
                <button id="install-later"
                    class="text-[13px] px-3 py-1.5 rounded-md later hidden sm:block">Later</button>
                <button id="install-now"
                    class="text-[13px] px-3 py-1.5 rounded-md install hidden sm:block">Install</button>
            </div>
            <div class="sm:hidden grid grid-cols-2 gap-2 p-3 pt-0">
                <button id="install-later-m" class="w-full text-sm py-2 rounded-md later">Later</button>
                <button id="install-now-m" class="w-full text-sm py-2 rounded-md install">Install</button>
            </div>
        </div>
    </div>
    <div id="sheetOverlay" class="sheet-overlay hidden z-[48]"></div>
    <div class="flex min-h-screen">
        <aside id="sidebar"
            class="hidden lg:block user-sidebar dark:text-white fixed inset-y-0 left-0 z-50 w-64 transition-transform duration-300 ease-in-out">
            <div class="flex flex-col h-full">
                <div class="h-16 px-6 flex items-center border-b border-gray-100 dark:border-white/10">
                    <a href="<?= BASE_URL ?>" class="flex items-center space-x-3">
                        <img src="<?= BASE_URL ?>img/logo_alt.png" alt="Logo" class="h-8 w-auto">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Zzimba Online</span>
                    </a>
                </div>
                <nav id="sidebarNav" class="flex-1 overflow-y-auto py-6 px-4 pb-1">
                    <?php foreach ($menuItems as $category): ?>
                        <div class="nav-category"><?= htmlspecialchars($category['title']) ?></div>
                        <div class="space-y-1 mb-2">
                            <?php foreach ($category['items'] as $key => $item): ?>
                                <a href="<?= BASE_URL ?>account/<?= $key ?>"
                                    class="user-nav-item group flex items-center justify-between px-4 py-2.5 text-sm rounded-lg transition-all duration-200 <?= $activeNav === $key ? 'active' : 'text-gray-text hover:bg-gray-50 hover:text-user-primary dark:text-white/80 dark:hover:text-white dark:hover:bg-white/10' ?>">
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
                <div class="p-4 border-t border-gray-100 dark:border-white/10">
                    <div class="space-y-2">
                        <a href="<?= BASE_URL ?>account/profile"
                            class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-user-primary dark:text-white/80 dark:hover:bg-white/10 dark:hover:text-white"><i
                                class="fas fa-user w-5 h-5 mr-3"></i>My Profile</a>
                        <a href="<?= BASE_URL ?>account/settings"
                            class="flex items-center px-4 py-2.5 text-sm rounded-lg text-gray-text hover:bg-gray-50 hover:text-user-primary dark:text-white/80 dark:hover:bg-white/10 dark:hover:text-white"><i
                                class="fas fa-cog w-5 h-5 mr-3"></i>Settings</a>
                    </div>
                </div>
            </div>
        </aside>
        <div class="flex-1 lg:ml-64">
            <header
                class="user-header sticky top-0 z-40 border-b border-gray-100 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="flex h-16 items-center justify-end sm:justify-between px-4 sm:px-6">
                    <h1 class="hidden md:block text-xl font-semibold text-secondary dark:text-white">
                        <?= htmlspecialchars($pageTitle ?? 'My Dashboard') ?>
                    </h1>
                    <div class="flex items-center gap-1 sm:gap-2">
                        <div class="relative" x-data="{open:false}">
                            <button @click="open=!open"
                                class="w-10 h-10 flex items-center justify-center rounded-lg theme-pill bg-white text-gray-700 hover:bg-gray-50 dark:bg-secondary dark:text-white dark:hover:bg-white/10">
                                <span x-show="mode==='light'"><i class="fa-solid fa-sun"></i></span>
                                <span x-show="mode==='dark'"><i class="fa-solid fa-moon"></i></span>
                                <span x-show="mode==='system'"><i class="fa-solid fa-circle-half-stroke"></i></span>
                            </button>
                            <div x-show="open" @click.away="open=false" x-transition
                                class="absolute right-0 mt-2 w-44 rounded-lg bg-white dark:bg-secondary shadow-lg border border-gray-100 dark:border-white/10 py-1 z-50">
                                <button
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10"
                                    :class="{'bg-gray-100 dark:bg-white/10': mode==='light'}"
                                    @click="setTheme('light');open=false"><span><i
                                            class="fa-solid fa-sun mr-2"></i>Light</span><i class="fa-solid fa-check"
                                        x-show="mode==='light'"></i></button>
                                <button
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10"
                                    :class="{'bg-gray-100 dark:bg-white/10': mode==='dark'}"
                                    @click="setTheme('dark');open=false"><span><i
                                            class="fa-solid fa-moon mr-2"></i>Dark</span><i class="fa-solid fa-check"
                                        x-show="mode==='dark'"></i></button>
                                <button
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10"
                                    :class="{'bg-gray-100 dark:bg-white/10': mode==='system'}"
                                    @click="setTheme('system');open=false"><span><i
                                            class="fa-solid fa-circle-half-stroke mr-2"></i>System</span><i
                                        class="fa-solid fa-check" x-show="mode==='system'"></i></button>
                            </div>
                        </div>
                        <div x-data="notifComponent()" x-init="init()" class="relative hidden md:block">
                            <button @click="toggle"
                                class="relative w-10 h-10 flex items-center justify-center rounded-lg theme-pill bg-white text-gray-700 hover:bg-gray-50 dark:bg-secondary dark:text-white dark:hover:bg-white/10">
                                <i class="fas fa-bell text-lg"></i>
                                <span x-show="count > 0" x-text="count"
                                    class="absolute -top-1 -right-1 text-[10px] font-semibold text-white bg-user-primary rounded-full h-4 w-4 grid place-items-center"></span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                class="fixed top-16 left-2 right-2 w-auto max-w-full sm:absolute sm:top-auto sm:left-auto sm:right-0 sm:mt-2 sm:w-80 sm:max-w-none bg-white dark:bg-secondary rounded-lg shadow-lg border border-gray-100 dark:border-white/10 z-50 max-h-96 overflow-auto">
                                <div
                                    class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-white/10">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" id="selectAll" @change="selectAll($event)"
                                            class="h-4 w-4 text-user-primary rounded">
                                        <label for="selectAll" class="text-xs text-gray-600 dark:text-white/80">Select
                                            All</label>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="markBulkSeen"
                                            class="text-xs px-2 py-1 bg-user-primary text-white rounded">Mark
                                            Read</button>
                                        <button @click="dismissBulk"
                                            class="text-xs px-2 py-1 bg-red-500 text-white rounded">Dismiss</button>
                                    </div>
                                </div>
                                <template x-for="note in notes" :key="note.target_id">
                                    <div :class="note.is_seen == 0 ? 'bg-user-secondary/20 dark:bg-white/5' : 'bg-white dark:bg-secondary'"
                                        class="relative group border-b border-gray-100 dark:border-white/10 last:border-none flex items-start">
                                        <div class="px-3 py-3"><input type="checkbox" :value="note.target_id"
                                                x-model="selected" class="h-4 w-4 text-user-primary rounded"></div>
                                        <div class="flex-1">
                                            <a :href="note.link_url || '#'" class="block px-0 py-3"
                                                @click.prevent="handleClick(note)">
                                                <p class="text-sm font-medium text-secondary dark:text-white"
                                                    x-text="note.title"></p>
                                                <p class="text-xs mt-1"
                                                    :class="note.is_seen == 0 ? 'text-secondary dark:text-white' : 'text-gray-500 dark:text-white/70'"
                                                    x-text="note.message"></p>
                                                <span class="text-[10px] text-gray-400 dark:text-white/60"
                                                    x-text="formatDate(note.created_at)"></span>
                                            </a>
                                        </div>
                                        <button @click.stop="dismiss(note.target_id)"
                                            class="absolute top-2 right-2 text-secondary/60 hover:text-user-primary dark:text-white/60 dark:hover:text-white transition"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                </template>
                                <div x-show="notes.length === 0"
                                    class="p-4 text-sm text-center text-gray-500 dark:text-white/70">No notifications
                                </div>
                            </div>
                        </div>
                        <div class="relative hidden md:block" id="userDropdown">
                            <button
                                class="flex items-center gap-3 rounded-lg px-3 py-2 theme-pill bg-white text-gray-700 hover:bg-gray-50 dark:bg-secondary dark:text-white dark:hover:bg-white/10"
                                title="Last login: <?= htmlspecialchars($formattedLastLogin) ?>">
                                <div class="user-initials"><?= htmlspecialchars($userInitials) ?></div>
                                <span class="text-sm font-medium"><?= htmlspecialchars($userName) ?></span>
                                <i class="fas fa-chevron-down text-sm opacity-70"></i>
                            </button>
                            <div id="userDropdownMenu"
                                class="hidden absolute right-0 mt-2 w-56 rounded-lg bg-white dark:bg-secondary shadow-lg border border-gray-100 dark:border-white/10 py-2 z-50">
                                <div class="px-4 py-3 bg-gray-50 dark:bg:white/5 dark:bg-white/5">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($userName) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-white/70">
                                        <?= htmlspecialchars($userEmail) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text:white/70 dark:text-white/70 mt-1">Last
                                        login:
                                        <?= htmlspecialchars($formattedLastLogin) ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>account/profile"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-white dark:hover:bg-white/10"><i
                                        class="fas fa-user w-5 h-5 text-gray-400 dark:text-white/60"></i>My Profile</a>
                                <a href="<?= BASE_URL ?>account/order-history"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-white dark:hover:bg-white/10"><i
                                        class="fas fa-shopping-bag w-5 h-5 text-gray-400 dark:text-white/60"></i>My
                                    Orders</a>
                                <a href="<?= BASE_URL ?>account/settings"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-white dark:hover:bg-white/10"><i
                                        class="fas fa-cog w-5 h-5 text-gray-400 dark:text-white/60"></i>Settings</a>
                                <div class="my-2 border-t border-gray-100 dark:border:white/10 dark:border-white/10">
                                </div>
                                <a href="javascript:void(0);" onclick="logoutUser(); return false;"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-user-primary hover:bg-gray-50 dark:hover:bg-white/10"><i
                                        class="fas fa-sign-out-alt w-5 h-5"></i>Logout</a>
                            </div>
                        </div>
                        <button id="mobileNotifBtn"
                            class="md:hidden relative w-10 h-10 flex items-center justify-center rounded-lg theme-pill bg-white text-gray-700 hover:bg-gray-50 dark:bg-secondary dark:text-white dark:hover:bg:white/10 dark:hover:bg-white/10">
                            <i class="fas fa-bell text-lg"></i>
                            <span id="mobileNotifCount"
                                class="hidden absolute -top-1 -right-1 text-[10px] font-semibold text-white bg-user-primary rounded-full h-4 w-4 grid place-items-center">0</span>
                        </button>
                        <button id="mobileAccountBtn"
                            class="md:hidden w-10 h-10 flex items-center justify-center rounded-lg theme-pill bg-white text-gray-700 hover:bg-gray-50 dark:bg-secondary dark:text-white dark:hover:bg-white/10">
                            <span class="user-initials w-8 h-8"><?= htmlspecialchars($userInitials) ?></span>
                        </button>
                    </div>
                </div>
            </header>
            <div class="flex flex-col min-h-[calc(100vh-64px)]">
                <main
                    class="main-content-area dark:bg-secondary p-4 sm:p-6 safe-bottom text-gray-900 dark:text-white main-fixed">
                    <?php if ($showOnboarding):
                        $hideKey = 'zz_gs_hide_until_' . htmlspecialchars($_SESSION['user']['user_id']);
                        ?>
                        <div id="getting-started"
                            class="gs-card-gradient border border-gray-200 dark:border-white/10 rounded-2xl p-4 sm:p-5 mb-4 sm:mb-6"
                            x-data="{
                            hiddenUntil: parseInt(localStorage.getItem('<?= $hideKey ?>') || '0',10),
                            now: Date.now(),
                            dismissed() { return this.hiddenUntil > this.now; },
                            percent: <?= (int) $progressPercent ?>,
                            canDismiss: <?= $onlyOptionalRemain ? 'true' : 'false' ?>,
                            hide30() {
                                if(!this.canDismiss) return;
                                const until = Date.now() + (30*60*1000);
                                localStorage.setItem('<?= $hideKey ?>', String(until));
                                this.hiddenUntil = until;
                            }
                         }" x-show="!dismissed()" x-transition>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-base sm:text-lg font-semibold text-secondary dark:text-white">Getting
                                        started</h2>
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-white/70">
                                        Finish these steps to unlock the best Zzimba experience.
                                        <span
                                            class="inline-block ml-1 text-[11px] sm:text-xs px-2 py-0.5 rounded-full bg-user-primary/10 text-user-primary font-medium">Profile
                                            & Wallet are required</span>
                                    </p>
                                </div>
                                <template x-if="canDismiss">
                                    <button
                                        class="text-xs px-3 py-1.5 rounded-md border border-gray-300/70 dark:border-white/20 text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10"
                                        @click="hide30()">Hide 30 min</button>
                                </template>
                            </div>
                            <div class="mt-3 sm:mt-4">
                                <div
                                    class="flex items-center justify-between text-xs text-gray-600 dark:text-white/70 mb-1.5">
                                    <span><span class="font-semibold"><?= $completed ?></span> / <?= $total ?> steps
                                        completed</span>
                                    <span x-text="percent + '%'"></span>
                                </div>
                                <div
                                    class="w-full bg-gray-200/70 dark:bg-white/10 rounded-full gs-progress overflow-hidden">
                                    <div class="h-full bg-user-primary rounded-full transition-all duration-300"
                                        :style="{ width: percent + '%' }"></div>
                                </div>
                            </div>
                            <div class="mt-4 grid gap-2 sm:grid-cols-4">
                                <?php foreach ($orderedKeys as $key):
                                    $s = $steps[$key];
                                    $isDone = $s['done'];
                                    $isCurrent = (!$isDone && $key === $firstIncompleteKey);
                                    $isLocked = (!$isDone && $firstIncompleteKey !== null && array_search($key, $orderedKeys, true) > array_search($firstIncompleteKey, $orderedKeys, true));
                                    $badge = $s['optional'] ? '<span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-gray-200/70 dark:bg-white/10 text-gray-700 dark:text-white/70">Optional</span>' : '';
                                    $wrapClasses = 'rounded-xl border gs-step transition-colors';
                                    if ($isDone) {
                                        $wrapClasses .= ' border-green-200 dark:border-green-800/40 bg-green-50/60 dark:bg-green-900/10';
                                    } elseif ($isCurrent) {
                                        $wrapClasses .= ' border-transparent bg-user-primary text-white';
                                    } elseif ($isLocked) {
                                        $wrapClasses .= ' border-gray-200 dark:border-white/10 bg-white/60 dark:bg-white/5 locked';
                                    } else {
                                        $wrapClasses .= ' border-gray-200 dark:border-white/10 bg-white/60 dark:bg-white/5';
                                    }
                                    $iconClasses = 'inline-flex h-9 w-9 items-center justify-center rounded-lg';
                                    if ($isDone) {
                                        $iconClasses .= ' bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
                                    } elseif ($isCurrent) {
                                        $iconClasses .= ' bg-white/20 text-white';
                                    } else {
                                        $iconClasses .= ' bg-gray-100 text-secondary dark:bg-white/10 dark:text-white';
                                    }
                                    $canClick = $isCurrent;
                                    $startTag = $canClick ? '<a href="' . htmlspecialchars($s['url']) . '" class="block ' . $wrapClasses . '">' : '<div class="block ' . $wrapClasses . ' ' . ($isLocked ? 'cursor-not-allowed' : '') . '">';
                                    $endTag = $canClick ? '</a>' : '</div>';
                                    echo $startTag;
                                    ?>
                                    <div class="p-3.5 sm:p-4 flex items-center gap-3">
                                        <span class="<?= $iconClasses ?>"><i
                                                class="fa-solid <?= htmlspecialchars($s['icon']) ?>"></i></span>
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="text-sm font-medium <?= $isCurrent ? 'text-white' : 'text-secondary dark:text-white' ?> truncate">
                                                <?= htmlspecialchars($s['label']) ?>         <?= $badge ?>
                                            </div>
                                            <div
                                                class="text-[11px] mt-0.5 <?= $isDone ? 'text-green-700 dark:text-green-300' : ($isCurrent ? 'text-white/90' : 'text-gray-600 dark:text-white/70') ?>">
                                                <?php if ($isDone): ?>
                                                    <i class="fa-solid fa-circle-check mr-1"></i> Completed
                                                <?php elseif ($isCurrent): ?>
                                                    <i class="fa-solid fa-arrow-right mr-1"></i> Continue
                                                <?php else: ?>
                                                    <i class="fa-solid fa-lock mr-1"></i> Locked
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($isDone): ?>
                                            <i class="fa-solid fa-check text-green-600 dark:text-green-400"></i>
                                        <?php elseif ($isCurrent): ?>
                                            <i
                                                class="fa-solid fa-chevron-right <?= $isCurrent ? 'text-white' : 'text-gray-400' ?>"></i>
                                        <?php else: ?>
                                            <i class="fa-solid fa-ban text-gray-300 dark:text-white/40"></i>
                                        <?php endif; ?>
                                    </div>
                                    <?= $endTag; endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?= $mainContent ?? '' ?>
                </main>
            </div>
        </div>
    </div>

    <!-- MOBILE TABBAR: Home (BASE_URL), SMS, Credit, Stores, More -->
    <div id="mobileTabbar"
        class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-secondary border-t border-gray-200 dark:border-white/10 mobile-tabbar">
        <div class="grid grid-cols-5 h-full">
            <a href="<?= BASE_URL ?>"
                class="flex flex-col items-center justify-center text-xs text-gray-500 dark:text-white/70">
                <i class="fa-solid fa-house mb-0.5"></i><span class="leading-none">Home</span>
            </a>
            <a href="<?= BASE_URL ?>account/sms-center"
                class="flex flex-col items-center justify-center text-xs <?= $activeNav === 'sms-center' ? 'text-secondary dark:text-white' : 'text-gray-500 dark:text-white/70' ?>">
                <i class="fa-solid fa-comment-dots mb-0.5"></i><span class="leading-none">SMS</span>
            </a>
            <a href="<?= BASE_URL ?>account/zzimba-credit"
                class="flex flex-col items-center justify-center text-xs <?= $activeNav === 'zzimba-credit' ? 'text-secondary dark:text-white' : 'text-gray-500 dark:text-white/70' ?>">
                <i class="fa-solid fa-wallet mb-0.5"></i><span class="leading-none">Credit</span>
            </a>
            <a href="<?= BASE_URL ?>account/zzimba-stores"
                class="flex flex-col items-center justify-center text-xs <?= $activeNav === 'zzimba-stores' ? 'text-secondary dark:text-white' : 'text-gray-500 dark:text-white/70' ?>">
                <i class="fa-solid fa-shop mb-0.5"></i><span class="leading-none">Stores</span>
            </a>
            <button id="mobileMoreBtn"
                class="flex flex-col items-center justify-center text-xs <?= in_array($activeNav, ['sms-center', 'zzimba-stores', 'zzimba-credit']) ? 'text-gray-500 dark:text-white/70' : 'text-secondary dark:text-white' ?>">
                <i class="fa-solid fa-ellipsis mb-0.5"></i><span class="leading-none">More</span>
            </button>
        </div>
    </div>

    <div id="mobileMoreSheet"
        class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-secondary rounded-t-2xl border-t border-gray-200 dark:border-white/10 shadow-2xl sheet">
        <div class="px-4 pt-3 pb-4">
            <div class="mx-auto h-1 w-10 rounded-full bg-gray-300 dark:bg-white/20 mb-3"></div>
            <div class="grid grid-cols-2 gap-2">
                <?php foreach ($menuItems as $category): ?>
                    <?php foreach ($category['items'] as $key => $item): ?>
                        <a href="<?= BASE_URL ?>account/<?= $key ?>"
                            class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                            <span
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                                    class="fas <?= $item['icon'] ?> text-secondary dark:text-white"></i></span>
                            <div>
                                <div class="text-sm font-medium text-secondary dark:text-white">
                                    <?= htmlspecialchars($item['title']) ?>
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-white/70">
                                    <?= htmlspecialchars(ucfirst($category['title'])) ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <a href="<?= BASE_URL ?>account/settings"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fas fa-cog text-secondary dark:text-white"></i></span>
                    <div>
                        <div class="text-sm font-medium text-secondary dark:text-white">Settings</div>
                        <div class="text-[11px] text-gray-500 dark:text-white/70">Preferences</div>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>account/profile"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fas fa-user text-secondary dark:text-white"></i></span>
                    <div>
                        <div class="text-sm font-medium text-secondary dark:text-white">My Profile</div>
                        <div class="text-[11px] text-gray-500 dark:text-white/70">Account</div>
                    </div>
                </a>
            </div>
            <button id="mobileMoreClose" class="mt-4 w-full py-2.5 rounded-xl border text-sm sheet-close">Close</button>
        </div>
    </div>

    <div id="mobileAccountSheet"
        class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-secondary rounded-t-2xl border-t border-gray-200 dark:border-white/10 shadow-2xl sheet">
        <div class="px-4 pt-3 pb-4">
            <div class="mx-auto h-1 w-10 rounded-full bg-gray-300 dark:bg-white/20 mb-3"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="user-initials w-10 h-10"><?= htmlspecialchars($userInitials) ?></div>
                <div class="min-w-0">
                    <div class="text-sm font-medium text-secondary dark:text-white truncate">
                        <?= htmlspecialchars($userName) ?>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-white/70 truncate"><?= htmlspecialchars($userEmail) ?>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <a href="<?= BASE_URL ?>account/profile"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fas fa-user text-secondary dark:text-white"></i></span>
                    <div>
                        <div class="text-sm font-medium text-secondary dark:text-white">My Profile</div>
                        <div class="text-[11px] text-gray-500 dark:text-white/70">Account</div>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>account/settings"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fas fa-cog text-secondary dark:text-white"></i></span>
                    <div class="text-left">
                        <div class="text-sm font-medium text-secondary dark:text-white">Settings</div>
                        <div id="mobileThemeLabel" class="text-[11px] text-gray-500 dark:text-white/70">Preferences
                        </div>
                    </div>
                </a>
                <button id="mobileThemeBtn"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fa-solid fa-circle-half-stroke text-secondary dark:text-white"></i></span>
                    <div class="text-left">
                        <div class="text-sm font-medium text-secondary dark:text-white">Theme</div>
                        <div id="mobileThemeLabel2" class="text-[11px] text-gray-500 dark:text-white/70">System</div>
                    </div>
                </button>
                <a href="<?= BASE_URL ?>account/order-history"
                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10">
                    <span
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10"><i
                            class="fas fa-shopping-bag text-secondary dark:text-white"></i></span>
                    <div>
                        <div class="text-sm font-medium text-secondary dark:text-white">My Orders</div>
                        <div class="text-[11px] text-gray-500 dark:text-white/70">History</div>
                    </div>
                </a>
            </div>
            <button id="mobileLogout"
                class="mt-4 w-full py-2.5 rounded-xl bg-user-primary text-white text-sm">Logout</button>
            <button id="mobileAccountClose"
                class="mt-2 w-full py-2.5 rounded-xl border text-sm sheet-close">Close</button>
        </div>
    </div>

    <div id="mobileNotifSheet"
        class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-secondary rounded-t-2xl border-t border-gray-200 dark:border-white/10 shadow-2xl sheet">
        <div class="px-4 pt-3 pb-2">
            <div class="mx-auto h-1 w-10 rounded-full bg-gray-300 dark:bg-white/20 mb-3"></div>
            <div x-data="notifComponent()" x-init="init()">
                <div class="flex items-center justify-between px-1 pb-2">
                    <div class="text-sm font-medium text-secondary dark:text-white">Notifications</div>
                    <div class="flex items-center gap-2">
                        <button @click="markBulkSeen" class="text-xs px-2 py-1 bg-user-primary text-white rounded">Mark
                            Read</button>
                        <button @click="dismissBulk"
                            class="text-xs px-2 py-1 bg-red-500 text-white rounded">Dismiss</button>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-1 pb-2">
                    <input type="checkbox" id="selectAllM" @change="selectAll($event)"
                        class="h-4 w-4 text-user-primary rounded">
                    <label for="selectAllM" class="text-xs text-gray-600 dark:text-white/80">Select All</label>
                </div>
                <div class="max-h-[60vh] overflow-auto border-t border-gray-100 dark:border-white/10">
                    <template x-for="note in notes" :key="note.target_id">
                        <div :class="note.is_seen == 0 ? 'bg-user-secondary/20 dark:bg-white/5' : 'bg-white dark:bg-secondary'"
                            class="relative group border-b border-gray-100 dark:border-white/10 last:border-none flex items-start">
                            <div class="px-3 py-3"><input type="checkbox" :value="note.target_id" x-model="selected"
                                    class="h-4 w-4 text-user-primary rounded"></div>
                            <div class="flex-1">
                                <a :href="note.link_url || '#'" class="block px-0 py-3"
                                    @click.prevent="handleClick(note)">
                                    <p class="text-sm font-medium text-secondary dark:text-white" x-text="note.title">
                                    </p>
                                    <p class="text-xs mt-1"
                                        :class="note.is_seen == 0 ? 'text-secondary dark:text-white' : 'text-gray-500 dark:text-white/70'"
                                        x-text="note.message"></p>
                                    <span class="text-[10px] text-gray-400 dark:text-white/60"
                                        x-text="formatDate(note.created_at)"></span>
                                </a>
                            </div>
                            <button @click.stop="dismiss(note.target_id)"
                                class="absolute top-2 right-2 text-secondary/60 hover:text-user-primary dark:text-white/60 dark:hover:text-white transition"><i
                                    class="fas fa-times"></i></button>
                        </div>
                    </template>
                    <div x-show="notes.length === 0" class="p-4 text-sm text-center text-gray-500 dark:text-white/70">No
                        notifications</div>
                </div>
            </div>
            <button id="mobileNotifClose"
                class="mt-3 w-full py-2.5 rounded-xl border text-sm sheet-close">Close</button>
        </div>
    </div>

    <div id="return-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-secondary rounded-xl p-8 max-w-md mx-4 shadow-2xl">
            <h3 class="text-xl font-rubik font-semibold text-secondary dark:text-white mb-6">Resume Your Progress</h3>
            <p class="text-gray-text dark:text-white/80 mb-8 leading-relaxed">You have an incomplete session on <span
                    id="return-page-title" class="font-medium text-secondary dark:text-white"></span>. Would you like to
                pick up where you left off?</p>
            <div class="flex justify-end space-x-3">
                <button id="return-ignore"
                    class="px-6 py-2.5 bg-secondary hover:opacity-90 text-white rounded-lg font-medium">Ignore</button>
                <button id="return-later"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-text dark:bg-white/10 dark:hover:bg-white/20 dark:text-white rounded-lg font-medium">Later</button>
                <button id="return-continue"
                    class="px-6 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium">Continue</button>
            </div>
        </div>
    </div>

    <script>
        const LOGGED_USER = <?= isset($_SESSION['user']) ? json_encode($_SESSION['user']) : 'null'; ?>;
        const userDropdown = document.getElementById('userDropdown');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        if (userDropdown) {
            userDropdown.addEventListener('click', e => { e.stopPropagation(); userDropdownMenu.classList.toggle('hidden') });
            document.addEventListener('click', () => userDropdownMenu.classList.add('hidden'));
        }
        function logoutUser() {
            $.ajax({
                url: BASE_URL + 'auth/logout', type: 'POST', contentType: 'application/json', dataType: 'json',
                success(data) { data.success ? location.href = BASE_URL : alert(data.message || 'Failed to logout') },
                error() { alert('Failed to connect to server.') }
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
                toggle() { this.open = !this.open },
                init() {
                    this.fetchNow();
                    this.timer = setInterval(() => this.fetchNow(), 20000);
                    document.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'visible') this.fetchNow();
                    });
                },
                setBadge(c) {
                    this.count = c;
                    const badge = document.getElementById('mobileNotifCount');
                    if (badge) {
                        if (this.count > 0) { badge.textContent = this.count; badge.classList.remove('hidden'); }
                        else { badge.classList.add('hidden'); }
                    }
                },
                mergeIncoming(arr) {
                    if (!Array.isArray(arr) || !arr.length) return;
                    const existing = new Map(this.notes.map(n => [n.target_id, n]));
                    for (const n of arr) {
                        if (!existing.has(n.target_id)) {
                            this.notes.unshift(n);
                            existing.set(n, n);
                        } else {
                            const idx = this.notes.findIndex(x => x.target_id === n.target_id);
                            if (idx >= 0) this.notes[idx] = n;
                        }
                    }
                    this.notes = this.notes.slice(0, 100);
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
                                this.setBadge(nextCount);
                                this.selected = this.selected.filter(id => this.notes.some(n => n.target_id === id));
                                const selAll = document.getElementById('selectAll'); if (selAll) selAll.checked = (this.selected.length && this.selected.length === this.notes.length);
                                const selAllM = document.getElementById('selectAllM'); if (selAllM) selAllM.checked = (this.selected.length && this.selected.length === this.notes.length);
                            }
                        }).catch(() => { });
                },
                selectAll(event) { this.selected = event.target.checked ? this.notes.map(n => n.target_id) : [] },
                markSeen(id) {
                    const p = new URLSearchParams(); p.append('action', 'markSeen'); p.append('target_id', id);
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json()).then(res => {
                            const n = this.notes.find(n => n.target_id === id); if (n) n.is_seen = 1;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.setBadge(c);
                        }).catch(() => { });
                },
                markBulkSeen() {
                    if (!this.selected.length) return;
                    const ids = this.selected.slice();
                    const p = new URLSearchParams(); p.append('action', 'markSeen'); ids.forEach(id => p.append('target_id[]', id));
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json()).then(res => {
                            this.notes.forEach(n => { if (ids.includes(n.target_id)) n.is_seen = 1 });
                            this.selected = [];
                            const b = document.getElementById('selectAll'); if (b) b.checked = false;
                            const b2 = document.getElementById('selectAllM'); if (b2) b2.checked = false;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.setBadge(c);
                        }).catch(() => { });
                },
                handleClick(note) { if (note.is_seen == 0) this.markSeen(note.target_id); if (note.link_url) location.href = note.link_url },
                dismiss(id) {
                    const p = new URLSearchParams(); p.append('action', 'dismiss'); p.append('target_id', id);
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json()).then(res => {
                            this.notes = this.notes.filter(n => n.target_id !== id);
                            this.selected = this.selected.filter(sid => sid !== id);
                            const b = document.getElementById('selectAll'); if (b) b.checked = (this.selected.length === this.notes.length && this.notes.length > 0);
                            const b2 = document.getElementById('selectAllM'); if (b2) b2.checked = (this.selected.length === this.notes.length && this.notes.length > 0);
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.setBadge(c);
                        }).catch(() => { });
                },
                dismissBulk() {
                    if (!this.selected.length) return;
                    const ids = this.selected.slice();
                    const p = new URLSearchParams(); p.append('action', 'dismiss'); ids.forEach(id => p.append('target_id[]', id));
                    fetch(BASE_URL + 'fetch/manageNotifications.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: p })
                        .then(r => r.json()).then(res => {
                            this.notes = this.notes.filter(n => !ids.includes(n.target_id));
                            this.selected = [];
                            const b = document.getElementById('selectAll'); if (b) b.checked = false;
                            const b2 = document.getElementById('selectAllM'); if (b2) b2.checked = false;
                            const c = Number.isInteger(res?.unread_count) ? res.unread_count : this.notes.filter(n => n.is_seen == 0).length;
                            this.setBadge(c);
                        }).catch(() => { });
                },
                formatDate(ts) {
                    const d = new Date(ts.replace(' ', 'T')), now = new Date(), diff = (now - d) / 1000;
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
                    const time = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                    if (diff < 60) return 'Now';
                    if (d >= today) return 'Today ' + time;
                    if (d >= yesterday && d < today) return 'Yesterday ' + time;
                    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
                }
            };
        }
        const overlay = document.getElementById('sheetOverlay');
        const body = document.body;
        const sheets = { account: document.getElementById('mobileAccountSheet'), more: document.getElementById('mobileMoreSheet'), notif: document.getElementById('mobileNotifSheet') };
        function isAnyOpen() { return Object.values(sheets).some(el => el.classList.contains('open')) }
        function openSheet(el) { el.classList.add('open'); overlay.classList.remove('hidden'); body.style.overflow = 'hidden' }
        function closeSheet(el) { el.classList.remove('open'); if (!isAnyOpen()) { overlay.classList.add('hidden'); body.style.overflow = '' } }
        function closeAllSheets() { Object.values(sheets).forEach(el => el.classList.remove('open')); overlay.classList.add('hidden'); body.style.overflow = '' }
        overlay.addEventListener('click', closeAllSheets);
        const mobileAccountBtn = document.getElementById('mobileAccountBtn');
        const mobileAccountSheet = sheets.account;
        const mobileAccountClose = document.getElementById('mobileAccountClose');
        const mobileMoreBtn = document.getElementById('mobileMoreBtn');
        const mobileMoreSheet = sheets.more;
        const mobileMoreClose = document.getElementById('mobileMoreClose');
        const mobileLogout = document.getElementById('mobileLogout');
        const mobileNotifBtn = document.getElementById('mobileNotifBtn');
        const mobileNotifSheet = sheets.notif;
        const mobileNotifClose = document.getElementById('mobileNotifClose');
        mobileAccountBtn.addEventListener('click', e => { e.stopPropagation(); openSheet(mobileAccountSheet) });
        mobileAccountClose.addEventListener('click', () => closeSheet(mobileAccountSheet));
        mobileMoreBtn.addEventListener('click', e => { e.stopPropagation(); openSheet(mobileMoreSheet) });
        mobileMoreClose.addEventListener('click', () => closeSheet(mobileMoreSheet));
        mobileNotifBtn.addEventListener('click', e => { e.stopPropagation(); openSheet(mobileNotifSheet) });
        mobileNotifClose.addEventListener('click', () => closeSheet(mobileNotifSheet));
        mobileLogout.addEventListener('click', () => logoutUser());
        function themeRoot() {
            return {
                mode: 'system',
                init() {
                    const saved = localStorage.getItem('zzimba_theme') || 'system';
                    this.setTheme(saved, false);
                    const media = window.matchMedia('(prefers-color-scheme: dark)');
                    media.addEventListener('change', () => { if (this.mode === 'system') this.applySystem() });
                    const themeBtn = document.getElementById('mobileThemeBtn');
                    if (themeBtn) { themeBtn.addEventListener('click', () => { const next = this.mode === 'light' ? 'dark' : this.mode === 'dark' ? 'system' : 'light'; this.setTheme(next); }); }
                    this.syncMobileLabel();
                    this.syncMetaThemeColor();
                    this.skinSplash();
                    initPWA();
                    setupSplashNavigation();
                },
                setTheme(val, persist = true) {
                    this.mode = val;
                    if (persist) localStorage.setItem('zzimba_theme', val);
                    if (val === 'dark') document.documentElement.classList.add('dark');
                    else if (val === 'light') document.documentElement.classList.remove('dark');
                    else this.applySystem();
                    this.syncMobileLabel();
                    this.syncMetaThemeColor();
                    this.skinSplash();
                },
                applySystem() {
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) document.documentElement.classList.add('dark');
                    else document.documentElement.classList.remove('dark');
                },
                syncMobileLabel() {
                    const lbl = document.getElementById('mobileThemeLabel');
                    const lbl2 = document.getElementById('mobileThemeLabel2');
                    if (lbl) lbl.textContent = this.mode.charAt(0).toUpperCase() + this.mode.slice(1);
                    if (lbl2) lbl2.textContent = this.mode.charAt(0).toUpperCase() + this.mode.slice(1);
                },
                syncMetaThemeColor() {
                    const meta = document.getElementById('meta-theme-color');
                    const isDark = document.documentElement.classList.contains('dark');
                    meta.setAttribute('content', isDark ? '#1a1a1a' : '#ffffff');
                },
                skinSplash() {
                    const splash = document.getElementById('zz-splash');
                    if (!splash) return;
                    const isDark = document.documentElement.classList.contains('dark');
                    splash.classList.remove('splash--light', 'splash--dark');
                    splash.classList.add(isDark ? 'splash--dark' : 'splash--light');
                }
            }
        }
        let _deferredPrompt = null;
        function initPWA() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('master.php?sw=1', { scope: APP_SCOPE }).catch(() => { });
            }
            const banner = document.getElementById('install-banner');
            const laterBtn = document.getElementById('install-later');
            const laterBtnM = document.getElementById('install-later-m');
            const nowBtn = document.getElementById('install-now');
            const nowBtnM = document.getElementById('install-now-m');
            const canShow = () => { const until = parseInt(localStorage.getItem('zz_install_later_until') || '0', 10); return Date.now() > until; };
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                _deferredPrompt = e;
                const installed = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
                if (!installed && canShow()) banner.classList.remove('hidden');
            });
            window.addEventListener('appinstalled', () => {
                banner.classList.add('hidden');
                _deferredPrompt = null;
                localStorage.removeItem('zz_install_later_until');
            });
            function closeFor30Min() { localStorage.setItem('zz_install_later_until', String(Date.now() + 30 * 60 * 1000)); banner.classList.add('hidden'); }
            function doInstall() {
                if (!_deferredPrompt) { banner.classList.add('hidden'); return; }
                _deferredPrompt.prompt();
                _deferredPrompt.userChoice.finally(() => { _deferredPrompt = null; banner.classList.add('hidden'); });
            }
            if (laterBtn) laterBtn.addEventListener('click', closeFor30Min);
            if (laterBtnM) laterBtnM.addEventListener('click', closeFor30Min);
            if (nowBtn) nowBtn.addEventListener('click', doInstall);
            if (nowBtnM) nowBtnM.addEventListener('click', doInstall);
        }
        function setupSplashNavigation() {
            const splash = document.getElementById('zz-splash');
            if (!splash) return;
            window.addEventListener('load', () => { setTimeout(() => { splash.style.display = 'none'; }, 60); });
            document.addEventListener('click', function (e) {
                const a = e.target.closest('a'); if (!a) return;
                if (a.hasAttribute('data-no-loader')) return;
                const href = a.getAttribute('href') || ''; if (href.startsWith('#')) return;
                if (a.target || a.hasAttribute('download')) return;
                const url = new URL(a.href, location.href); if (url.origin !== location.origin) return;
                document.getElementById('zz-splash').style.display = '';
                e.preventDefault();
                setTimeout(() => { window.location.href = a.href; }, 50);
            });
            window.addEventListener('beforeunload', () => { document.getElementById('zz-splash').style.display = ''; });
        }
        window.addEventListener('load', function () {
            const url = localStorage.getItem('return_url');
            const title = localStorage.getItem('return_title');
            if (url && title && title !== PAGE_TITLE) {
                function showReturnModal() {
                    document.getElementById('return-page-title').textContent = title;
                    const modal = document.getElementById('return-modal');
                    modal.classList.remove('hidden'); modal.classList.add('flex');
                }
                setTimeout(showReturnModal, 120000);
                document.getElementById('return-later').addEventListener('click', function () {
                    const modal = document.getElementById('return-modal');
                    modal.classList.remove('flex'); modal.classList.add('hidden'); setTimeout(showReturnModal, 300000);
                });
                document.getElementById('return-continue').addEventListener('click', function () {
                    localStorage.removeItem('return_url'); localStorage.removeItem('return_title'); window.location.href = url;
                });
                document.getElementById('return-ignore').addEventListener('click', function () {
                    localStorage.removeItem('return_url'); localStorage.removeItem('return_title');
                    const modal = document.getElementById('return-modal'); modal.classList.remove('flex'); modal.classList.add('hidden');
                });
            }
        });
    </script>
</body>

</html>