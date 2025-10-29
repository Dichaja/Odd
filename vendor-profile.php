<?php
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

$vendorId = $_GET['id'] ?? null;
$storeData = null;
$canEdit = false;

if (isset($_GET['ajax']) && $_GET['ajax'] === 'storeRole') {
    if (!headers_sent())
        header('Content-Type: application/json');
    $resp = ['logged_in' => false, 'is_admin' => false, 'is_owner_or_manager' => false, 'can_edit' => false];
    try {
        $storeId = $_GET['id'] ?? null;
        if (!empty($_SESSION['user']['logged_in']) && $storeId) {
            $userId = $_SESSION['user']['user_id'] ?? null;
            $isAdmin = !empty($_SESSION['user']['is_admin']);
            $resp['logged_in'] = true;
            $resp['is_admin'] = $isAdmin ? true : false;
            if ($isAdmin) {
                $resp['is_owner_or_manager'] = true;
                $resp['can_edit'] = true;
            } else {
                $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
                $stmt->execute([$storeId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && (string) $row['owner_id'] === (string) $userId) {
                    $resp['is_owner_or_manager'] = true;
                    $resp['can_edit'] = true;
                } else {
                    $stmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active'");
                    $stmt->execute([$storeId, $userId]);
                    if ($stmt->fetch()) {
                        $resp['is_owner_or_manager'] = true;
                        $resp['can_edit'] = true;
                    }
                }
            }
        }
    } catch (Throwable $e) {
    }
    echo json_encode($resp);
    exit;
}

if (!empty($_SESSION['user']['logged_in'])) {
    $userId = $_SESSION['user']['user_id'];
    $isAdmin = $_SESSION['user']['is_admin'] ?? false;
    if ($isAdmin) {
        $canEdit = true;
    } elseif ($vendorId) {
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$vendorId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($store && (string) $store['owner_id'] === (string) $userId) {
            $canEdit = true;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active'");
            $stmt->execute([$vendorId, $userId]);
            if ($stmt->fetch()) {
                $canEdit = true;
            }
        }
    }
}

function generateSeoMetaTags($store)
{
    $title = htmlspecialchars($store['name'] ?? 'Vendor Store') . ' | Zzimba Store';
    $description = htmlspecialchars($store['description'] ?? 'Discover quality products and services at ' . ($store['name'] ?? 'this vendor store') . ' on Zzimba Online.');
    $ogImage = '';
    if (!empty($store['logo_url'])) {
        $ogImage = BASE_URL . $store['logo_url'];
    } elseif (!empty($store['vendor_cover_url'])) {
        $ogImage = BASE_URL . $store['vendor_cover_url'];
    } else {
        $storeName = urlencode($store['name'] ?? 'Vendor Store');
        $ogImage = "https://placehold.co/1200x630?text={$storeName}";
    }
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return [
        'title' => $title,
        'description' => $description,
        'og_title' => $title,
        'og_description' => $description,
        'og_image' => $ogImage,
        'og_url' => $currentUrl,
        'og_type' => 'website'
    ];
}

if ($vendorId) {
    try {
        $storeId = $vendorId;
        $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $stmt->execute([$storeId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($store) {
            $stmt = $pdo->prepare("SELECT * FROM vendor_stores WHERE id = ?");
            $stmt->execute([$storeId]);
            $storeData = $stmt->fetch(PDO::FETCH_ASSOC);
            $seoTags = generateSeoMetaTags($storeData);
            $pageTitle = $seoTags['title'];
        }
    } catch (Exception $e) {
        error_log("Error fetching vendor data: " . $e->getMessage());
    }
}

$isLoggedIn = !empty($_SESSION['user']['logged_in']);
$isAdmin = $_SESSION['user']['is_admin'] ?? false;
$isOwnerOrManager = false;

if ($isLoggedIn && $vendorId) {
    $userId = $_SESSION['user']['user_id'];
    $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
    $stmt->execute([$vendorId]);
    $store = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($store && (string) $store['owner_id'] === (string) $userId) {
        $isOwnerOrManager = true;
    } else {
        $stmt = $pdo->prepare("SELECT id FROM store_managers WHERE store_id = ? AND user_id = ? AND status = 'active'");
        $stmt->execute([$vendorId, $userId]);
        if ($stmt->fetch()) {
            $isOwnerOrManager = true;
        }
    }
}

ob_start();
?>
<style>
    [x-cloak] {
        display: none !important
    }

    .container {
        max-width: 1200px;
        margin: 0 auto
    }

    .masonry-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem
    }

    @media (min-width:640px) {
        .masonry-grid {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    @media (min-width:1024px) {
        .masonry-grid {
            grid-template-columns: repeat(3, 1fr)
        }
    }

    .price-hidden {
        display: none
    }

    .line-clamp-1 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden
    }

    .line-clamp-2 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden
    }

    .line-clamp-3 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden
    }

    .datepicker {
        font-family: inherit;
        background-color: #fff;
        border-radius: .5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1), 0 2px 4px -1px rgba(0, 0, 0, .06);
        width: 100%;
        overflow: hidden
    }

    .datepicker-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .5rem 1rem;
        border-bottom: 1px solid #e5e7eb
    }

    .datepicker-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        padding: .5rem
    }

    .datepicker-day-header {
        text-align: center;
        font-size: .75rem;
        font-weight: 500;
        color: #6b7280;
        padding: .5rem 0
    }

    .datepicker-day {
        text-align: center;
        padding: .5rem;
        border-radius: .25rem;
        cursor: pointer;
        color: #1f2937;
        font-size: .875rem
    }

    .datepicker-day:hover:not(.disabled):not(.selected) {
        background-color: #f3f4f6
    }

    .datepicker-day.selected {
        background-color: #D92B13;
        color: #fff;
        font-weight: 500
    }

    .datepicker-day.today:not(.selected) {
        border: 1px solid #D92B13;
        color: #D92B13
    }

    .datepicker-day.disabled {
        color: #d1d5db;
        cursor: not-allowed
    }

    .soft-bounce {
        animation: soft-bounce 1.2s ease-in-out infinite
    }

    @keyframes soft-bounce {

        0%,
        100% {
            transform: translateY(0)
        }

        50% {
            transform: translateY(-4px)
        }
    }

    .fade-in-up {
        animation: fade-in-up .35s ease-out both
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(6px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    .btn-ghost {
        transition: all .2s ease;
        box-shadow: 0 0 0 0 rgba(217, 43, 19, 0)
    }

    .btn-ghost:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 14px -6px rgba(217, 43, 19, .35)
    }

    .modal-panel {
        display: flex;
        flex-direction: column;
        max-height: calc(100dvh - 2rem);
        overscroll-behavior: contain;
        border-radius: 1rem
    }

    @supports not (height:1dvh) {
        .modal-panel {
            max-height: calc(100vh - 2rem)
        }
    }

    .modal-scroll {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch
    }

    @media (max-width:640px) {
        .space-y-4>*+* {
            margin-top: 1rem
        }

        button {
            min-height: 44px;
            padding: .75rem 1rem
        }

        input,
        select {
            min-height: 44px;
            padding: .75rem
        }
    }

    html.dark .datepicker {
        background-color: #0f172a;
        color: #e5e7eb;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .35)
    }

    html.dark .datepicker-controls {
        border-bottom-color: #1f2937
    }

    html.dark .datepicker-day-header {
        color: #94a3b8
    }

    html.dark .datepicker-day {
        color: #e5e7eb
    }

    html.dark .datepicker-day:hover:not(.disabled):not(.selected) {
        background-color: #111827
    }

    html.dark .btn-ghost:hover {
        box-shadow: 0 6px 14px -6px rgba(217, 43, 19, .6)
    }
</style>

<div x-data="vendorProfile" x-init="init()" class="relative">
    <div class="relative h-40 md:h-64 w-full bg-gray-100 dark:bg-slate-800 overflow-hidden" id="vendor-cover-photo"
        x-show="!loading && !error && !notFound" x-cloak>
        <div id="vendor-cover" class="w-full h-full bg-center bg-cover" :style="coverStyle"></div>
        <?php if ($canEdit): ?>
            <button @click="openCoverEditor"
                class="absolute top-4 right-4 bg-white dark:bg-slate-900 rounded-full w-10 h-10 flex items-center justify-center shadow-md cursor-pointer text-primary border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors p-0 sm:p-2">
                <i data-lucide="camera" class="w-5 h-5"></i>
            </button>
        <?php endif; ?>
    </div>

    <div x-show="loading" class="flex flex-col items-center justify-center py-16">
        <div
            class="border-4 border-gray-200 dark:border-slate-700 border-l-primary rounded-full w-12 h-12 animate-spin mb-5">
        </div>
        <p class="text-gray-600 dark:text-slate-300">Loading vendor profile...</p>
    </div>

    <div x-show="notFound" x-cloak class="max-w-3xl mx-auto my-14 px-6">
        <div
            class="bg-gradient-to-br from-red-50 to-rose-50 dark:from-slate-800 dark:to-slate-900 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-2xl p-8 md:p-10 shadow-sm fade-in-up">
            <div class="flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center soft-bounce mb-4">
                    <i data-lucide="circle-alert" class="w-7 h-7 text-red-600 dark:text-red-400"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">Store Not Found or Inactive</h2>
                <p class="text-red-700/80 dark:text-red-200/80 mb-6">This store may not exist or has not been activated
                    by an administrator yet.</p>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <a href="<?= BASE_URL ?>"
                        class="bg-primary text-white px-5 py-2.5 rounded-lg hover:bg-primary/90 btn-ghost">Go to
                        Home</a>
                    <a href="<?= BASE_URL ?>vendors"
                        class="px-5 py-2.5 rounded-lg border border-red-300 dark:border-red-800 text-red-700 dark:text-red-300 hover:bg-white dark:hover:bg-slate-900">Browse
                        Vendors</a>
                </div>
            </div>
        </div>
    </div>

    <div x-show="error" x-cloak class="max-w-3xl mx-auto my-14 px-6">
        <div
            class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-slate-800 dark:to-slate-900 border border-yellow-200 dark:border-yellow-800 text-yellow-900 dark:text-yellow-200 rounded-2xl p-8 md:p-10 shadow-sm fade-in-up">
            <div class="flex flex-col items-center text-center">
                <div
                    class="h-14 w-14 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center soft-bounce mb-4">
                    <i data-lucide="shield-alert" class="w-7 h-7 text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold mb-2">Profile Not Found</h2>
                <p class="text-yellow-800/80 dark:text-yellow-200/80 mb-6">We could not load this vendor profile. It
                    might have been moved or is temporarily unavailable.</p>
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <a href="<?= BASE_URL ?>"
                        class="bg-primary text-white px-5 py-2.5 rounded-lg hover:bg-primary/90 btn-ghost">Go to
                        Home</a>
                    <button onclick="location.reload()"
                        class="px-5 py-2.5 rounded-lg border border-yellow-300 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 hover:bg-white dark:hover:bg-slate-900">Try
                        Again</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="!loading && !error && !notFound" x-cloak id="content-state"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 md:-mt-16 relative z-10">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-lg p-6 fade-in-up">
            <div class="flex flex-col md:flex-row">
                <div class="flex-shrink-0 flex md:block justify-center">
                    <div class="relative">
                        <div
                            class="h-32 w-32 rounded-full border-4 border-white dark:border-slate-800 shadow-md overflow-hidden bg-white dark:bg-slate-800 flex items-center justify-center">
                            <template x-if="logoUrl">
                                <img :src="logoUrl" :alt="store?.name || 'Store Logo'"
                                    class="w-full h-full object-cover rounded-full">
                            </template>
                            <template x-if="!logoUrl">
                                <div
                                    class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-slate-700">
                                    <i data-lucide="store" class="w-10 h-10 text-gray-400 dark:text-slate-300"></i>
                                </div>
                            </template>
                        </div>
                        <?php if ($canEdit): ?>
                            <button @click="openLogoEditor"
                                class="absolute bottom-0 right-0 bg-white dark:bg-slate-900 rounded-full w-8 h-8 flex items-center justify-center shadow-sm cursor-pointer text-primary border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors p-0 sm:p-2">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-6 md:mt-0 md:ml-6 flex-grow text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div>
                            <h1
                                class="text-3xl font-bold text-secondary dark:text-white flex items-center justify-center md:justify-start">
                                <span x-text="store?.name || 'Store Name'"></span>
                                <?php if ($canEdit): ?>
                                    <button @click="openNameEditor"
                                        class="ml-2 text-gray-500 dark:text-slate-300 hover:text-primary transition-colors p-0 sm:p-2">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </h1>
                            <p
                                class="text-gray-600 dark:text-slate-300 mt-1 flex items-start justify-center md:justify-start line-clamp-3 md:line-clamp-2">
                                <span x-text="store?.description || 'Premium Construction Materials & Services'"></span>
                                <?php if ($canEdit): ?>
                                    <button @click="openDescriptionEditor"
                                        class="ml-2 text-gray-500 dark:text-slate-300 hover:text-primary transition-colors p-0 sm:p-2">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </p>

                            <div class="md:hidden mt-3">
                                <div class="flex items-center justify-center gap-1">
                                    <div class="text-xl font-bold text-secondary dark:text-white">4.8</div>
                                    <div class="ml-2 flex items-center">
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                                        <i data-lucide="star-half" class="w-4 h-4 text-yellow-400"></i>
                                        <span class="ml-1 text-sm text-gray-600 dark:text-slate-300">(128
                                            reviews)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-y-4 justify-center md:justify-start">
                        <div class="mr-8 flex items-center">
                            <i data-lucide="calendar" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                            <span class="text-gray-700 dark:text-slate-200" x-text="'Joined ' + joinedAt"></span>
                        </div>

                        <div class="mr-8 flex items-center">
                            <template x-if="canSeeContacts">
                                <div class="flex items-center">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                    <span class="text-gray-700 dark:text-slate-200" x-text="locationText"></span>
                                </div>
                            </template>
                            <template x-if="!canSeeContacts">
                                <div>
                                    <button x-show="!viewed.location" x-cloak @click="revealLocation()"
                                        class="flex items-center text-primary text-sm font-medium hover:underline transition-all btn-ghost px-2 py-1 rounded">
                                        <i data-lucide="map-pin"
                                            class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                        <span>View Location</span>
                                    </button>
                                    <div x-show="viewed.location" x-cloak class="fade-in-up">
                                        <div class="flex items-center">
                                            <i data-lucide="map-pin"
                                                class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                            <span class="text-gray-700 dark:text-slate-200"
                                                x-text="locationText"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mr-8 flex items-center">
                            <template x-if="canSeeContacts">
                                <div class="flex items-center">
                                    <i data-lucide="phone" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                    <span class="text-gray-700 dark:text-slate-200" x-text="phoneText"></span>
                                </div>
                            </template>
                            <template x-if="!canSeeContacts">
                                <div>
                                    <button x-show="!viewed.contact" x-cloak @click="revealPhone()"
                                        class="flex items-center text-primary text-sm font-medium hover:underline transition-all btn-ghost px-2 py-1 rounded">
                                        <i data-lucide="phone"
                                            class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                        <span>View Contact</span>
                                    </button>
                                    <div x-show="viewed.contact" x-cloak class="fade-in-up">
                                        <div class="flex items-center">
                                            <i data-lucide="phone"
                                                class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                            <span class="text-gray-700 dark:text-slate-200" x-text="phoneText"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mr-8 flex items-center">
                            <template x-if="canSeeContacts">
                                <div class="flex items-center">
                                    <i data-lucide="mail" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                    <span class="text-gray-700 dark:text-slate-200" x-text="emailText"></span>
                                </div>
                            </template>
                            <template x-if="!canSeeContacts">
                                <div>
                                    <button x-show="!viewed.email" x-cloak @click="revealEmail()"
                                        class="flex items-center text-primary text-sm font-medium hover:underline transition-all btn-ghost px-2 py-1 rounded">
                                        <i data-lucide="mail"
                                            class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                        <span>View Email</span>
                                    </button>
                                    <div x-show="viewed.email" x-cloak class="fade-in-up">
                                        <div class="flex items-center">
                                            <i data-lucide="mail"
                                                class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                                            <span class="text-gray-700 dark:text-slate-200" x-text="emailText"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mr-8 flex items-center">
                            <i data-lucide="boxes" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                            <span class="text-gray-700 dark:text-slate-200" x-text="productCountText"></span>
                        </div>

                        <div class="mr-8 flex items-center">
                            <i data-lucide="tags" class="w-4 h-4 text-gray-500 dark:text-slate-400 mr-2"></i>
                            <span class="text-gray-700 dark:text-slate-200" x-text="categoryCountText"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="mt-6 pt-6 border-t border-gray-200 dark:border-slate-800 flex flex-wrap gap-x-8 gap-y-4 justify-center md:justify-start">
                <div class="flex items-center">
                    <div :class="statusBadgeClass" x-text="statusText"></div>
                    <div class="ml-2 bg-primary text-white px-3 py-1 rounded-full text-sm"
                        x-text="store?.nature_of_business_name || 'Operation Type'"></div>
                </div>

                <div class="hidden md:flex items-center">
                    <div class="text-xl font-bold text-secondary dark:text-white">4.8</div>
                    <div class="ml-2 flex items-center">
                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                        <i data-lucide="star" class="w-4 h-4 text-yellow-400 fill-yellow-400"></i>
                        <i data-lucide="star-half" class="w-4 h-4 text-yellow-400"></i>
                        <span class="ml-1 text-sm text-gray-600 dark:text-slate-300">(128 reviews)</span>
                    </div>
                </div>

                <div class="ml-0 sm:ml-auto flex items-center gap-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-slate-400">SHARE</span>
                    <div class="flex gap-2">
                        <button @click="copyLink"
                            class="flex items-center justify-center w-6 h-6 rounded-full text-primary border-[1.5px] border-primary bg-transparent hover:bg-red-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                            <span class="hidden md:block"><i data-lucide="link" class="w-3 h-3"></i></span>
                            <span class="md:hidden"><i class="fa-solid fa-link text-xs"></i></span>
                        </button>
                        <button @click="shareWhatsApp"
                            class="flex items-center justify-center w-6 h-6 rounded-full text-primary border-[1.5px] border-primary bg-transparent hover:bg-red-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                            <span class="hidden md:block"><i data-lucide="message-circle" class="w-3 h-3"></i></span>
                            <span class="md:hidden"><i class="fa-brands fa-whatsapp text-xs"></i></span>
                        </button>
                        <button @click="shareFacebook"
                            class="flex items-center justify-center w-6 h-6 rounded-full text-primary border-[1.5px] border-primary bg-transparent hover:bg-red-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                            <span class="hidden md:block"><i data-lucide="facebook" class="w-3 h-3"></i></span>
                            <span class="md:hidden"><i class="fa-brands fa-facebook-f text-xs"></i></span>
                        </button>
                        <button @click="shareTwitter"
                            class="flex items-center justify-center w-6 h-6 rounded-full text-primary border-[1.5px] border-primary bg-transparent hover:bg-red-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                            <span class="hidden md:block"><i data-lucide="twitter" class="w-3 h-3"></i></span>
                            <span class="md:hidden"><i class="fa-brands fa-x-twitter text-xs"></i></span>
                        </button>
                        <button @click="shareLinkedIn"
                            class="flex items-center justify-center w-6 h-6 rounded-full text-primary border-[1.5px] border-primary bg-transparent hover:bg-red-50 dark:hover:bg-slate-800 hover:-translate-y-0.5 transition-all">
                            <span class="hidden md:block"><i data-lucide="linkedin" class="w-3 h-3"></i></span>
                            <span class="md:hidden"><i class="fa-brands fa-linkedin-in text-xs"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <main class="py-8">
            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200 dark:border-slate-700">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'products'" 
                        :class="activeTab === 'products' ? 'border-primary text-primary' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300 hover:border-gray-300 dark:hover:border-slate-600'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i data-lucide="package-open" class="w-4 h-4 inline-block mr-2"></i>
                        Products
                    </button>
                    <button @click="activeTab = 'reviews'" 
                        :class="activeTab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-slate-300 hover:border-gray-300 dark:hover:border-slate-600'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <i data-lucide="star" class="w-4 h-4 inline-block mr-2"></i>
                        Reviews
                    </button>
                </nav>
            </div>

            <!-- Products Tab -->
            <div x-show="activeTab === 'products'" x-cloak>
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center">
                        <i data-lucide="package-open" class="w-5 h-5 mr-2 text-primary"></i>
                        Products
                    </h2>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <select x-model="selectedCategory" @change="applyFilters"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 rounded-lg text-sm w-full sm:w-auto">
                            <option value="">All Categories</option>
                            <template x-for="c in filterCategories" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                        <input type="text" x-model.debounce.200ms="searchTerm" @input="applyFilters"
                            placeholder="Search products..."
                            class="px-4 py-2 border border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 rounded-lg text-sm w-full sm:w-auto">
                    </div>
                </div>

                <div id="products-container" class="masonry-grid">
                    <template x-if="filteredProducts.length === 0 && productsLoaded">
                        <div class="col-span-full text-center py-8 text-gray-500 dark:text-slate-300">No products found
                            for this vendor.</div>
                    </template>

                    <template x-for="p in visibleProducts" :key="p.id">
                        <div
                            class="transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col bg-white dark:bg-slate-900 rounded-xl shadow-lg border border-gray-200 dark:border-slate-800 overflow-hidden">
                            <div class="relative group">
                                <img :src="p._img || placeholderFor(p)" :alt="p.name"
                                    class="w-full h-40 md:h-48 object-cover">
                                <div
                                    class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 md:flex items-center justify-center transition-opacity hidden">
                                    <a :href="BASE_URL + 'view/product/' + p.id"
                                        class="bg-white dark:bg-slate-900 text-gray-800 dark:text-slate-100 px-4 py-2 rounded-lg font-medium hover:bg-primary hover:text-white transition-colors text-sm">View
                                        Details</a>
                                </div>
                            </div>
                            <div class="p-3 md:p-5 flex flex-col flex-1">
                                <div>
                                    <h3 class="font-bold text-gray-800 dark:text-white mb-2 line-clamp-2 text-sm md:text-base"
                                        x-text="p.name"></h3>
                                    <p class="text-gray-600 dark:text-slate-300 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block"
                                        x-text="p.description || ''"></p>
                                </div>
                                <div class="flex-1"></div>

                                <div class="hidden md:block border-t border-gray-200 dark:border-slate-800 pt-3 mb-3">
                                    <template x-if="(p._viewPricing.length === 0)">
                                        <div>
                                            <template x-if="!auth.canSeeAllCategories">
                                                <button @click="setPendingAndLogin({type:'view-categories'})"
                                                    class="block w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 px-3 py-2 rounded-md text-sm">View
                                                    Price Categories</button>
                                            </template>
                                            <template x-if="auth.canSeeAllCategories">
                                                <div class="text-sm text-gray-600 dark:text-slate-300 italic p-2">No
                                                    price data</div>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="p._viewPricing.length > 0">
                                        <div>
                                            <template x-for="(pr, idx) in p._viewPricing" :key="pr.pricing_id">
                                                <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-slate-800 rounded"
                                                    :class="idx >= 2 && !p._showAll ? 'hidden' : ''">
                                                    <div class="flex flex-col min-w-0">
                                                        <span class="font-medium text-gray-700 dark:text-slate-200"
                                                            x-text="formatUnit(pr)"></span>
                                                        <div class="flex items-center text-xs text-gray-500 dark:text-slate-400"
                                                            x-show="auth.canSeeAllCategories">
                                                            <span class="truncate"
                                                                x-text="capitalize(pr.price_category)"></span>
                                                            <span class="ml-2"
                                                                x-show="pr.delivery_capacity && pr.price_category === 'retail'">•
                                                                Max Capacity: <span
                                                                    x-text="pr.delivery_capacity"></span></span>
                                                            <span class="ml-2"
                                                                x-show="pr.delivery_capacity && pr.price_category !== 'retail'">•
                                                                Min Capacity: <span
                                                                    x-text="pr.delivery_capacity"></span></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <template
                                                            x-if="auth.showPriceDirectly || viewed.prices.includes(pr.pricing_id)">
                                                            <span class="text-primary font-bold"
                                                                x-text="'UGX ' + nf(pr.price)"></span>
                                                        </template>
                                                        <template
                                                            x-if="!auth.showPriceDirectly && !viewed.prices.includes(pr.pricing_id)">
                                                            <button class="text-blue-600 underline text-sm"
                                                                @click="revealPrice(pr, $event)">View Price</button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            <button
                                                class="view-more-prices text-blue-600 underline text-sm w-full text-center mt-2 pt-2 border-t border-dashed border-gray-200 dark:border-slate-700"
                                                x-show="p._viewPricing.length > 2 && !p._showAll"
                                                @click="expandPrices(p)">View More Prices</button>
                                            <div class="login-note text-center text-gray-500 dark:text-slate-400 text-sm"
                                                x-show="!auth.canSeeAllCategories && p._hasRetail">Login to view more
                                                price categories</div>
                                        </div>
                                    </template>
                                </div>

                                <div class="md:hidden border-t border-gray-200 dark:border-slate-800 pt-3 mb-3">
                                    <template x-if="Array.isArray(p.pricing) && p.pricing.length>0 && !auth.loggedIn">
                                        <button @click="setPendingAndLogin({type:'view-categories'})"
                                            class="w-full text-center bg-gray-100 dark:bg-slate-800 text-gray-700 dark:text-slate-200 px-3 py-2 rounded-md text-sm">Login
                                            to View Price</button>
                                    </template>
                                    <template x-if="(p._viewPricing.length > 0) && auth.loggedIn">
                                        <button @click="openPriceSheet(p)"
                                            class="w-full text-center bg-gray-900 dark:bg-black text-white px-3 py-2 rounded-md text-sm">View
                                            Prices</button>
                                    </template>
                                    <template x-if="(p._viewPricing.length === 0) && auth.loggedIn">
                                        <div class="text-sm text-gray-600 dark:text-slate-300 italic p-2">No price data
                                        </div>
                                    </template>
                                </div>

                                <div class="flex space-x-2" x-show="!auth.isAdminOrManager">
                                    <template x-if="auth.loggedIn">
                                        <button @click="openBuyInStore(p)"
                                            class="bg-primary hover:bg-primary/90 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                            <i data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i>Buy in Store
                                        </button>
                                    </template>
                                    <template x-if="!auth.loggedIn">
                                        <button @click="setPendingAndLogin({type:'buy', productId:p.store_product_id})"
                                            class="bg-primary hover:bg-primary/90 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                            <i data-lucide="shopping-cart" class="w-4 h-4 mr-1"></i>Buy in Store
                                        </button>
                                    </template>
                                    <button @click="openVendorSell(p)"
                                        class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                        <i data-lucide="tags" class="w-4 h-4"></i>Sell
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button x-show="pagination.page < pagination.pages" @click="loadMore"
                    class="mx-auto mt-8 block bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-slate-700 transition-colors">Load
                    More Products</button>
            </div>

            <!-- Reviews Tab -->
            <div x-show="activeTab === 'reviews'" x-cloak>
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center mb-4">
                        <i data-lucide="star" class="w-5 h-5 mr-2 text-primary"></i>
                        Customer Reviews
                    </h2>
                    
                    <div x-show="reviewsLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
                        <p class="mt-2 text-gray-600 dark:text-slate-300">Loading reviews...</p>
                    </div>

                    <div x-show="!reviewsLoading && reviews.length === 0" class="text-center py-8 text-gray-500 dark:text-slate-300">
                        No reviews yet for this store.
                    </div>

                    <div x-show="!reviewsLoading && reviews.length > 0" class="space-y-4">
                        <template x-for="review in reviews" :key="review.id">
                            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-md border border-gray-200 dark:border-slate-800 p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div class="flex items-center">
                                                <template x-for="i in 5" :key="i">
                                                    <i data-lucide="star" 
                                                        :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300 dark:text-slate-600'"
                                                        class="w-4 h-4"></i>
                                                </template>
                                            </div>
                                            <span class="ml-2 text-sm font-medium text-gray-700 dark:text-slate-200" x-text="review.rating + '/5'"></span>
                                        </div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white" x-text="review.product_name"></h3>
                                        <p class="text-sm text-gray-500 dark:text-slate-400" x-text="'By ' + review.reviewer_name + ' • ' + formatDate(review.created_at)"></p>
                                    </div>
                                </div>
                                <p class="text-gray-700 dark:text-slate-300" x-text="review.review_text"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-show="modals.prices.visible" x-cloak class="fixed inset-0 z-[1200]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closePriceSheet"></div>
        <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[94%] lg:max-w-2xl bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="p-4 sm:p-6 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-slate-100 whitespace-nowrap overflow-hidden text-ellipsis max-w-[calc(100%-3rem)]"
                        x-text="modals.prices.product?.name || 'Prices'"></h3>
                    <button @click="closePriceSheet"
                        class="text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white p-2 rounded-full hover:bg-white/60 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="modal-scroll p-4 sm:p-6">
                    <template x-if="!modals.prices.product">
                        <div class="text-center text-gray-500 dark:text-slate-300 py-10">No product selected</div>
                    </template>
                    <template x-if="modals.prices.product">
                        <div class="space-y-3">
                            <template x-if="modals.prices.entries.length===0 && !auth.loggedIn">
                                <div class="text-center">
                                    <button @click="setPendingAndLogin({type:'view-categories'})"
                                        class="px-4 py-2 rounded-lg bg-gray-900 dark:bg-black text-white">Login to View
                                        Price</button>
                                </div>
                            </template>
                            <template x-if="modals.prices.entries.length===0 && auth.loggedIn">
                                <div class="text-center text-gray-500 dark:text-slate-300 py-6">No price data</div>
                            </template>
                            <template x-for="pr in modals.prices.entries" :key="pr.pricing_id">
                                <div
                                    class="flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-slate-100 line-clamp-1"
                                            x-text="formatUnit(pr)"></div>
                                        <div class="text-xs text-gray-600 dark:text-slate-300">
                                            <span class="uppercase" x-text="pr.price_category"></span>
                                            <span x-show="pr.delivery_capacity && pr.price_category==='retail'"> • Max:
                                                <span x-text="pr.delivery_capacity"></span></span>
                                            <span x-show="pr.delivery_capacity && pr.price_category!=='retail'"> • Min:
                                                <span x-text="pr.delivery_capacity"></span></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <template
                                            x-if="auth.showPriceDirectly || viewed.prices.includes(pr.pricing_id)">
                                            <span class="text-primary font-bold whitespace-nowrap"
                                                x-text="'UGX ' + nf(pr.price)"></span>
                                        </template>
                                        <template
                                            x-if="!auth.showPriceDirectly && !viewed.prices.includes(pr.pricing_id)">
                                            <button class="text-blue-600 underline text-sm whitespace-nowrap"
                                                @click="revealPriceMobile(pr)">View Price</button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div
                    class="md:hidden sticky bottom-0 inset-x-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                    <button @click="closePriceSheet"
                        class="w-full px-3 py-2 rounded-lg bg-gray-900 dark:bg-black text-white">Done</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.buy.visible" x-cloak class="fixed inset-0 z-[1200]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeBuy"></div>
        <div class="fixed inset-0 flex items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[94%] lg:max-w-6xl bg-white dark:bg-slate-900 rounded-none sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="p-4 sm:p-6 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-slate-100">Complete Your Request
                    </h3>
                    <button @click="closeBuy"
                        class="text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white p-2 rounded-full hover:bg-white/60 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="modal-scroll">
                    <div class="flex flex-col md:flex-row">
                        <div
                            class="w-full md:w-1/2 border-b md:border-b-0 md:border-r border-gray-100 dark:border-slate-800">
                            <div x-show="modals.buy.loading" class="text-center py-8">
                                <div
                                    class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary">
                                </div>
                                <p class="mt-2 text-gray-600 dark:text-slate-300">Loading your information...</p>
                            </div>

                            <form x-show="!modals.buy.loading" @submit.prevent="submitBuy" class="p-4 sm:p-6 space-y-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Visit
                                        Date <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div id="datepicker-container" class="w-full"></div>
                                        <input type="hidden" x-model="buyForm.visitDate" required>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Please select a date when
                                        you plan to visit our store</p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Package
                                        <span class="text-red-500">*</span></label>
                                    <select x-model="buyForm.packageId" @change="updateCapacity"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                        required>
                                        <option value="">Select a package</option>
                                        <template x-for="pr in modals.buy.packages" :key="pr.pricing_id">
                                            <option :value="pr.pricing_id" :data-category="pr.price_category"
                                                :data-capacity="pr.delivery_capacity || 1" :data-price="pr.price"
                                                :data-unit="formatUnit(pr)"
                                                x-text="formatUnit(pr) + ' (' + capitalize(pr.price_category) + ') - UGX ' + nf(pr.price)">
                                            </option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Quantity
                                        <span class="text-red-500">*</span></label>
                                    <div class="flex items-center">
                                        <button type="button" @click="decQty"
                                            class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-l-md bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700">
                                            <i data-lucide="minus" class="w-4 h-4"></i>
                                        </button>
                                        <input type="number" x-model.number="buyForm.quantity"
                                            class="w-full px-3 py-2 border-t border-b border-gray-300 dark:border-slate-700 text-center focus:ring-0 focus:border-gray-300 dark:focus:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                            min="1">
                                        <button type="button" @click="incQty"
                                            class="px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-r-md bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1" x-text="capacityNote"></p>
                                </div>

                                <div>
                                    <label class="flex items-center mb-2">
                                        <input type="checkbox" x-model="buyForm.showAlt"
                                            class="h-4 w-4 text-primary focus:ring-primary/20 border-gray-300 dark:border-slate-700 rounded">
                                        <span class="ml-2 block text-sm text-gray-700 dark:text-slate-200">Add
                                            alternative contact details (optional)</span>
                                    </label>

                                    <div class="space-y-4" x-show="buyForm.showAlt">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Alternative
                                                Phone</label>
                                            <input type="text" x-model="buyForm.altPhone"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                                placeholder="Alternative phone number">
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Alternative
                                                Email</label>
                                            <input type="email" x-model="buyForm.altEmail"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                                placeholder="Alternative email address">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Notes
                                        (Optional)</label>
                                    <textarea x-model="buyForm.notes" rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                        placeholder="Any special requests or notes for your visit"></textarea>
                                </div>

                                <div class="pb-20 md:pb-0"></div>
                            </form>
                        </div>

                        <div class="md:w-1/2 hidden md:block">
                            <div class="h-full flex flex-col">
                                <div class="p-6 flex-1 overflow-y-auto bg-gray-50 dark:bg-slate-800">
                                    <div class="mb-6">
                                        <h3 class="text-xl font-bold text-gray-800 dark:text-slate-100 mb-2"
                                            x-text="modals.buy.product?.name || ''"></h3>
                                        <div
                                            class="bg-white dark:bg-slate-900 rounded-lg shadow-sm overflow-hidden mb-4">
                                            <img :src="modals.buy.product?._img || placeholderFor(modals.buy.product)"
                                                alt="Product Image" class="w-full h-48 object-cover">
                                        </div>
                                        <p class="text-gray-600 dark:text-slate-300 text-sm line-clamp-2 mb-2"
                                            x-text="modals.buy.product?.description || 'No description available.'"></p>
                                    </div>

                                    <div class="border-t border-gray-200 dark:border-slate-700 pt-6 mb-6">
                                        <h4
                                            class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                                            Order Summary</h4>
                                        <div class="space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 dark:text-slate-300">Selected Package:</span>
                                                <span class="font-medium line-clamp-1"
                                                    x-text="summaryPackage || '-'"></span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 dark:text-slate-300">Quantity:</span>
                                                <span class="font-medium" x-text="buyForm.quantity || '-'"></span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600 dark:text-slate-300">Visit Date:</span>
                                                <span class="font-medium" x-text="summaryDate || '-'"></span>
                                            </div>
                                            <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-slate-700"
                                                x-show="buyForm.showAlt && (buyForm.altPhone || buyForm.altEmail)">
                                                <h5 class="text-sm font-medium text-gray-500 dark:text-slate-400">
                                                    Alternative Contact</h5>
                                                <div class="text-sm" x-show="buyForm.altPhone">
                                                    <span class="text-gray-600 dark:text-slate-300">Phone:</span>
                                                    <span class="ml-2 font-medium" x-text="buyForm.altPhone"></span>
                                                </div>
                                                <div class="text-sm" x-show="buyForm.altEmail">
                                                    <span class="text-gray-600 dark:text-slate-300">Email:</span>
                                                    <span class="ml-2 font-medium" x-text="buyForm.altEmail"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm p-4">
                                        <h4
                                            class="text-sm font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-4">
                                            Your Information</h4>
                                        <div class="grid grid-cols-1 gap-4">
                                            <div class="flex items-center">
                                                <i data-lucide="user"
                                                    class="h-5 w-5 text-gray-400 dark:text-slate-300 mr-3"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-slate-400">Name</p>
                                                    <p class="font-medium text-gray-900 dark:text-slate-100"
                                                        x-text="userSummary.name || '-'"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i data-lucide="mail"
                                                    class="h-5 w-5 text-gray-400 dark:text-slate-300 mr-3"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-slate-400">Email</p>
                                                    <p class="font-medium text-gray-900 dark:text-slate-100"
                                                        x-text="userSummary.email || '-'"></p>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i data-lucide="phone"
                                                    class="h-5 w-5 text-gray-400 dark:text-slate-300 mr-3"></i>
                                                <div>
                                                    <p class="text-xs text-gray-500 dark:text-slate-400">Phone</p>
                                                    <p class="font-medium text-gray-900 dark:text-slate-100"
                                                        x-text="userSummary.phone || '-'"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="md:hidden sticky bottom-0 inset-x-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="closeBuy"
                                    class="px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-200">Cancel</button>
                                <button @click="submitBuy" :disabled="modals.buy.submitting"
                                    class="px-3 py-2 rounded-lg bg-primary text-white disabled:opacity-50">
                                    <span x-show="!modals.buy.submitting">Submit Order</span>
                                    <span x-show="modals.buy.submitting" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>Loading...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.confirm.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1300] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-2">Review & Confirm</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-4">A processing fee will be charged to your account and
                refunded upon completion of the order</p>

            <div class="rounded-md bg-gray-50 dark:bg-slate-800 p-4 mb-4">
                <div class="font-medium text-gray-900 dark:text-slate-100"
                    x-text="modals.confirm.payload?.product?.name || '-'"></div>
                <div class="text-sm text-gray-600 dark:text-slate-300 mt-1"
                    x-text="'Store: ' + (modals.confirm.payload?.product?.store_name || '-')"></div>
            </div>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-slate-300">Fee</span>
                    <span class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX ' + nf(modals.confirm.payload?.fee || 0)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-slate-300">Current Balance</span>
                    <span class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX ' + nf(modals.confirm.payload?.balance || 0)"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-slate-300">Balance After</span>
                    <span
                        :class="(modals.confirm.payload?.remaining_balance ?? 0) < 0 ? 'text-red-600 font-semibold' : 'font-medium text-gray-900 dark:text-slate-100'"
                        x-text="'UGX ' + nf((modals.confirm.payload?.remaining_balance) || 0)"></span>
                </div>
                <div class="flex justify-between text-red-600" x-show="!modals.confirm.payload?.can_submit">
                    <span>Shortfall</span>
                    <span class="font-semibold" x-text="'UGX ' + nf(modals.confirm.payload?.shortfall || 0)"></span>
                </div>
            </div>

            <p class="text-sm text-red-600 mt-3" x-show="!modals.confirm.payload?.can_submit">Insufficient balance.
                Please top up your wallet to continue.</p>

            <div class="mt-6 flex justify-between">
                <button @click="modals.confirm.visible=false"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-md text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800">Back</button>
                <button @click="submitBuyConfirmed"
                    :disabled="modals.confirm.submitting || !modals.confirm.payload?.can_submit"
                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 disabled:opacity-50">
                    <span x-show="!modals.confirm.submitting">Confirm and Submit</span>
                    <span x-show="modals.confirm.submitting" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>Submitting...
                    </span>
                </button>
            </div>
        </div>
    </div>

    <div x-show="modals.error.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1200] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 dark:border-red-600 p-4 mb-6">
                <div class="flex">
                    <i data-lucide="shield-alert" class="h-5 w-5 text-red-500"></i>
                    <p class="text-sm text-red-700 dark:text-red-200 ml-3"
                        x-text="modals.error.message || 'An error occurred. Please try again.'"></p>
                </div>
            </div>
            <button @click="closeError"
                class="w-full px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-md hover:bg-gray-300 dark:hover:bg-slate-700">Close</button>
        </div>
    </div>

    <div x-show="modals.success.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1200] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div
                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                <i data-lucide="check" class="h-6 w-6 text-green-600 dark:text-green-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-slate-100 mb-2">Request Submitted</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-4" x-text="modals.success.message || defaultSuccessMessage">
            </p>
            <div class="text-left space-y-2 mb-4"
                x-show="modals.success.payload && (('fee_charged' in modals.success.payload) || ('remaining_balance' in modals.success.payload) || ('transaction_id' in modals.success.payload))">
                <div class="flex justify-between"
                    x-show="modals.success.payload && ('fee_charged' in modals.success.payload)">
                    <span class="text-gray-600 dark:text-slate-300">Fee Charged</span>
                    <span class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX ' + nf((modals.success.payload && modals.success.payload.fee_charged) || 0)"></span>
                </div>
                <div class="flex justify-between"
                    x-show="modals.success.payload && ('remaining_balance' in modals.success.payload)">
                    <span class="text-gray-600 dark:text-slate-300">Remaining Balance</span>
                    <span class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX ' + nf((modals.success.payload && modals.success.payload.remaining_balance) || 0)"></span>
                </div>
                <div class="flex justify-between"
                    x-show="modals.success.payload && ('transaction_id' in modals.success.payload)">
                    <span class="text-gray-600 dark:text-slate-300">Transaction ID</span>
                    <span class="font-mono text-sm text-gray-900 dark:text-slate-100"
                        x-text="(modals.success.payload && modals.success.payload.transaction_id) || '-'"></span>
                </div>
            </div>
            <button @click="closeSuccess"
                class="w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 focus:outline-none">Done</button>
        </div>
    </div>

    <?php if ($canEdit): ?>
        <div x-show="modals.name.visible" x-cloak class="fixed inset-0 z-[1400] bg-black/60 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
                <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200 dark:border-slate-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Edit Store Name</h2>
                    <button @click="modals.name.visible=false"
                        class="text-gray-400 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Store Name</label>
                    <input type="text" x-model="editForms.name"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-slate-800">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-md hover:bg-gray-300 dark:hover:bg-slate-700"
                        @click="modals.name.visible=false">Cancel</button>
                    <button type="button" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90"
                        @click="saveName">Save</button>
                </div>
            </div>
        </div>

        <div x-show="modals.description.visible" x-cloak class="fixed inset-0 z-[1400] bg-black/60 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
                <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200 dark:border-slate-800">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Edit Store Description</h2>
                    <button @click="modals.description.visible=false"
                        class="text-gray-400 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Store
                        Description</label>
                    <textarea x-model="editForms.description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-slate-800">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-md hover:bg-gray-300 dark:hover:bg-slate-700"
                        @click="modals.description.visible=false">Cancel</button>
                    <button type="button" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90"
                        @click="saveDescription">Save</button>
                </div>
            </div>
        </div>

        <div x-show="modals.logo.visible" x-cloak class="fixed inset-0 z-[1400] bg-black/60 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
                <div class="flex justify-between items-center pb-2 mb-4 border-gray-200 dark:border-slate-800 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Edit Store Logo</h2>
                    <button @click="closeLogoEditor"
                        class="text-gray-400 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Upload Logo</label>
                    <input type="file" accept="image/*" @change="onLogoFile"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Select a square image for best results.</p>
                </div>
                <div x-show="logoCrop.visible" class="h-[300px] mb-4">
                    <img id="cropper-image" src="https://placehold.co/600x400?text=Image+to+Crop" alt="Image to crop">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-slate-800">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-md hover:bg-gray-300 dark:hover:bg-slate-700"
                        @click="closeLogoEditor">Cancel</button>
                    <button type="button" x-show="logoCrop.visible"
                        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90" @click="cropAndSaveLogo">Crop
                        - Save</button>
                </div>
            </div>
        </div>

        <div x-show="modals.cover.visible" x-cloak class="fixed inset-0 z-[1400] bg-black/60 backdrop-blur-sm">
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
                <div class="flex justify-between items-center pb-2 mb-4 border-gray-200 dark:border-slate-800 border-b">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Edit Cover Photo</h2>
                    <button @click="closeCoverEditor"
                        class="text-gray-400 dark:text-slate-300 hover:text-gray-900 dark:hover:text-white">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Upload Cover
                        Photo</label>
                    <input type="file" accept="image/*" @change="onCoverFile"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100 focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Select an image that will be cropped to a 3:1
                        ratio.</p>
                </div>
                <div x-show="coverCrop.visible" class="h-[300px] mb-4">
                    <img id="cover-cropper-image" src="https://placehold.co/1200x400?text=Cover+Image+to+Crop"
                        alt="Image to crop">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-slate-800">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 dark:bg-slate-800 text-gray-800 dark:text-slate-200 rounded-md hover:bg-gray-300 dark:hover:bg-slate-700"
                        @click="closeCoverEditor">Cancel</button>
                    <button type="button" x-show="coverCrop.visible"
                        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90"
                        @click="cropAndSaveCover">Crop - Save</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div x-show="toast.visible" x-cloak
        class="fixed top-4 left-1/2 -translate-x-1/2 px-4 py-2 rounded-md shadow-md z-[1500] text-white"
        :class="toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
        <span x-text="toast.message"></span>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

<script>
    window.__pendingVendorAction = null;
    window.setPendingVendorAction = (a) => { window.__pendingVendorAction = a || null };
</script>

<script>
    (function () {
        const wrap = () => {
            const orig = window.updateUIAfterLogin;
            window.updateUIAfterLogin = function (user) {
                try { typeof orig === 'function' && orig(user) } catch (e) { }
                try { window.dispatchEvent(new CustomEvent('zz:session-login', { detail: user || {} })) } catch (e) { }
                const el = document.querySelector('[x-data="vendorProfile"]');
                if (el && el.__x) { el.__x.$data.handlePostLogin(user || {}) }
            };
        };
        if (document.readyState === 'complete' || document.readyState === 'interactive') { wrap() } else { document.addEventListener('DOMContentLoaded', wrap) }
    })();
    async function fetchStoreRole(storeId) {
        try {
            const r = await fetch((window.BASE_URL || '<?= BASE_URL ?>') + <?= json_encode(basename(__FILE__)) ?> + '?ajax=storeRole&id=' + encodeURIComponent(storeId));
            const j = await r.json();
            return { loggedIn: !!j.logged_in, isAdmin: !!j.is_admin, isOwnerOrManager: !!j.is_owner_or_manager, canEdit: !!j.can_edit };
        } catch (e) { return { loggedIn: false, isAdmin: false, isOwnerOrManager: false, canEdit: false } }
    }
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('vendorProfile', () => ({
            BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
            vendorId: '<?= $vendorId ?>',
            defaultSuccessMessage: 'Your buy-in-store request has been submitted successfully.',
            activeTab: 'products',
            reviews: [],
            reviewsLoading: false,
            auth: {
                loggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
                canEdit: <?= $canEdit ? 'true' : 'false' ?>,
                isAdmin: <?= $isAdmin ? 'true' : 'false' ?>,
                isOwnerOrManager: <?= $isOwnerOrManager ? 'true' : 'false' ?>,
                get isAdminOrManager() { return this.isAdmin || this.isOwnerOrManager },
                get canSeeAllCategories() { return this.isAdmin || this.isOwnerOrManager || this.loggedIn },
                get showPriceDirectly() { return this.isAdmin || this.isOwnerOrManager }
            },
            store: null,
            logoUrl: '',
            coverUrl: '',
            loading: true,
            error: false,
            notFound: false,
            products: [],
            productsLoaded: false,
            pagination: { page: 1, pages: 1 },
            filterCategories: [],
            selectedCategory: '',
            searchTerm: '',
            viewed: { contacts: [], prices: [], location: false, contact: false, email: false },
            modals: {
                buy: { visible: false, loading: true, submitting: false, product: null, packages: [] },
                confirm: { visible: false, submitting: false, payload: null, form: null },
                error: { visible: false, message: '' },
                success: { visible: false, message: '', payload: null },
                name: { visible: false },
                description: { visible: false },
                logo: { visible: false },
                cover: { visible: false },
                prices: { visible: false, product: null, entries: [] }
            },
            editForms: { name: '', description: '' },
            logoCrop: { visible: false, cropper: null, file: null },
            coverCrop: { visible: false, cropper: null, file: null },
            buyForm: { visitDate: '', packageId: '', quantity: 1, showAlt: false, altPhone: '', altEmail: '', notes: '' },
            userSummary: { name: '-', email: '-', phone: '-' },
            toast: { visible: false, message: '', type: 'success' },
            pendingPriorityId: '',
            priorityApplied: false,

            get coverStyle() { return this.coverUrl ? `background-image:url(${this.coverUrl})` : '' },
            get joinedAt() { if (!this.store?.created_at) return '-'; const d = new Date(this.store.created_at); return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) },
            get locationText() { return `${this.store?.district || ''}${this.store?.address ? ', ' + this.store.address : ''}`.trim() },
            get phoneText() { return this.store?.business_phone || 'Not provided' },
            get emailText() { return this.store?.business_email || 'Not provided' },
            get productCountText() { const n = Number(this.store?.product_count || 0); return `${n} ${n === 1 ? 'Product' : 'Products'}` },
            get categoryCountText() { const n = Number(this.store?.category_count || 0); return `${n} ${n === 1 ? 'Category' : 'Categories'}` },
            get canSeeContacts() { return this.auth.isAdminOrManager },
            get statusText() { const s = (this.store?.status || '').toLowerCase(); if (s === 'active') return 'Active'; if (s === 'pending') return 'Pending Verification'; return s ? s.charAt(0).toUpperCase() + s.slice(1) : 'Status' },
            get statusBadgeClass() { const s = (this.store?.status || '').toLowerCase(); if (s === 'active') return 'px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'; if (s === 'pending') return 'px-3 py-1 rounded-full text-sm bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'; return 'px-3 py-1 rounded-full text-sm bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' },

            get filteredProducts() {
                let arr = this.products.slice();
                if (this.selectedCategory) arr = arr.filter(p => String(p.store_category_id) === String(this.selectedCategory));
                if (this.searchTerm) { const q = this.searchTerm.toLowerCase(); arr = arr.filter(p => (p.name || '').toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q)) }
                return arr;
            },
            get visibleProducts() { const pageSize = this.pagination.page * 1e6; return this.filteredProducts.slice(0, pageSize) },

            async syncAuthOnLoad() {
                if (!this.vendorId) return;
                const role = await fetchStoreRole(this.vendorId);
                this.auth.loggedIn = role.loggedIn || this.auth.loggedIn;
                this.auth.isAdmin = role.isAdmin || this.auth.isAdmin;
                this.auth.isOwnerOrManager = role.isOwnerOrManager || this.auth.isOwnerOrManager;
                this.auth.canEdit = role.canEdit || this.auth.canEdit;
                if (this.auth.isAdminOrManager) { this.revealPhone(true); this.revealEmail(true) }
                this.recomputePricingVisibility();
            },

            init() {
                if (window.__zz_vendor_profile_init_guard) { return }
                window.__zz_vendor_profile_init_guard = true;
                window.__vendorProfile = this;
                window.__zz_init_counter = (window.__zz_init_counter || 0) + 1;
                try {
                    const v = localStorage.getItem('zz_last_product_id');
                    this.pendingPriorityId = (v || '').trim();
                } catch (e) {
                    this.pendingPriorityId = '';
                }
                this.loadViewedEntities();
                this.loadProfile().then(() => this.loadProducts(1)).finally(() => { this.loading = false; this.$nextTick(() => this.renderIcons()) });
                this.syncAuthOnLoad();
                window.addEventListener('zz:session-login', e => this.handlePostLogin(e.detail || {}));
                if (this.auth.isAdminOrManager) { this.revealPhone(true); this.revealEmail(true) }
                this.logProfileView();
                
                // Watch for tab changes
                this.$watch('activeTab', (value) => {
                    if (value === 'reviews' && this.reviews.length === 0) {
                        this.loadReviews();
                    }
                    this.$nextTick(() => this.renderIcons());
                });
            },

            async handlePostLogin(user) {
                this.auth.loggedIn = true;
                const role = await fetchStoreRole(this.vendorId);
                this.auth.isAdmin = role.isAdmin;
                this.auth.isOwnerOrManager = role.isOwnerOrManager;
                this.auth.canEdit = role.canEdit;
                if (this.auth.isAdminOrManager) {
                    if (window.__pendingVendorAction?.type === 'buy') { this.showToast('You cannot place store orders with this account.', 'error') }
                    if (['phone', 'email', 'location', 'view-categories'].includes(window.__pendingVendorAction?.type)) { this.showToast('You now have access to contact details.', 'success'); this.revealPhone(true); this.revealEmail(true); this.viewed.location = true }
                    window.setPendingVendorAction(null);
                } else {
                    const a = window.__pendingVendorAction;
                    if (a) {
                        if (a.type === 'buy') { const p = this.products.find(pp => String(pp.store_product_id) === String(a.productId)); if (p) this.openBuyInStore(p) }
                        else if (a.type === 'phone') { this.revealPhone(true) }
                        else if (a.type === 'email') { this.revealEmail(true) }
                        else if (a.type === 'location') { this.viewed.location = true }
                        else if (a.type === 'view-categories' && this.modals.prices.visible && this.modals.prices.product) { this.openPriceSheet(this.modals.prices.product) }
                        window.setPendingVendorAction(null);
                    }
                }
                this.recomputePricingVisibility();
            },

            recomputePricingVisibility() {
                for (const p of this.products) {
                    const pricing = Array.isArray(p.pricing) ? p.pricing : [];
                    const filtered = this.auth.canSeeAllCategories ? pricing : pricing.filter(x => x.price_category === 'retail');
                    p._viewPricing = filtered;
                    p._hasRetail = pricing.some(x => x.price_category === 'retail');
                }
                this.$nextTick(() => this.renderIcons());
            },

            renderIcons() { if (window.lucide && window.lucide.createIcons) window.lucide.createIcons() },

            async loadProfile() {
                if (!this.vendorId) { this.error = true; return }
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=getStoreDetails&id=' + encodeURIComponent(this.vendorId));
                    const data = await r.json();
                    if (data.success && data.store) {
                        this.store = data.store;
                        this.logoUrl = this.store.logo_url ? this.BASE_URL + this.store.logo_url : '';
                        this.coverUrl = this.store.vendor_cover_url ? this.BASE_URL + this.store.vendor_cover_url : `https://placehold.co/1200x400/e5e7eb/6b7280?text=${encodeURIComponent(this.store.name)}`;
                        this.prepareCategories(this.store.categories || []);
                    } else {
                        if (data.error === 'Store not found or not active') { this.notFound = true } else { this.error = true }
                    }
                } catch (e) { this.error = true }
            },

            prepareCategories(cats) { const list = (cats || []).filter(c => c.status === 'active' && Number(c.product_count) > 0); this.filterCategories = list; },

            async loadProducts(page = 1) {
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=getStoreProducts&id=' + encodeURIComponent(this.vendorId) + '&page=' + page);
                    const data = await r.json();
                    if (data.success) {
                        const products = data.products || [];
                        for (const p of products) {
                            p._img = await this.firstProductImage(p);
                            const pricing = Array.isArray(p.pricing) ? p.pricing : [];
                            const filtered = this.auth.canSeeAllCategories ? pricing : pricing.filter(x => x.price_category === 'retail');
                            p._viewPricing = filtered;
                            p._hasRetail = pricing.some(x => x.price_category === 'retail');
                            p._showAll = false;
                        }
                        if (page === 1) this.products = products; else this.products = this.products.concat(products);
                        this.applyPriorityProduct();
                        this.pagination.page = data.pagination?.page || page;
                        this.pagination.pages = data.pagination?.pages || 1;
                        this.productsLoaded = true;
                        this.$nextTick(() => this.renderIcons());
                    }
                } catch (e) { }
            },

            applyPriorityProduct() {
                const wanted = (this.pendingPriorityId || '').trim();
                if (!wanted || this.priorityApplied) return;
                const idx = this.products.findIndex(p => String(p.id) === String(wanted));
                if (idx >= 0) {
                    const [it] = this.products.splice(idx, 1);
                    this.products.unshift(it);
                    this.products = this.products.slice();
                    try { localStorage.removeItem('zz_last_product_id') } catch (e) { }
                    this.pendingPriorityId = '';
                    this.priorityApplied = true;
                }
            },

            loadMore() { if (this.pagination.page < this.pagination.pages) { this.loadProducts(this.pagination.page + 1) } },

            applyFilters() { this.$nextTick(() => this.renderIcons()) },

            async firstProductImage(product) {
                const ph = `https://placehold.co/400x300/f0f0f0/808080?text=${encodeURIComponent((product.name || '').substring(0, 2))}`;
                try {
                    const res = await fetch(this.BASE_URL + 'img/products/' + product.id + '/images.json');
                    if (!res.ok) return ph;
                    const json = await res.json();
                    if (Array.isArray(json.images) && json.images.length > 0) return this.BASE_URL + 'img/products/' + product.id + '/' + json.images[0];
                } catch (e) { }
                return ph;
            },

            placeholderFor(p) { if (!p) return 'https://placehold.co/400x300/f0f0f0/808080?text=NA'; return `https://placehold.co/400x300/f0f0f0/808080?text=${encodeURIComponent((p.name || '').substring(0, 2))}` },

            formatUnit(pr) { const parts = (pr.unit_name || '').split(' '); const si = parts[0] || ''; const pkg = parts.slice(1).join(' ') || ''; return `${pr.package_size} ${si} ${pkg}`.trim(); },

            capitalize(s) { s = s || ''; return s.charAt(0).toUpperCase() + s.slice(1) },
            nf(n) { try { return new Intl.NumberFormat().format(n) } catch (e) { return n } },

            async revealLocation(skipSession = false) {
                if (this.canSeeContacts) { this.viewed.location = true; return }
                if (skipSession) { this.viewed.location = true; if (!this.viewed.contacts.includes('location')) this.viewed.contacts.push('location'); return }
                if (this.viewed.contacts?.includes('location')) { this.viewed.location = true; return }
                const ok = await this.ensureSession({ type: 'location' });
                if (!ok) return;
                await this.logContact('location');
                this.viewed.contacts.push('location');
                this.viewed.location = true;
            },

            async revealPhone(skipSession = false) {
                if (this.canSeeContacts) { this.viewed.contact = true; return }
                if (skipSession) { await this.fetchContact('phone'); this.viewed.contact = true; if (!this.viewed.contacts.includes('contact')) this.viewed.contacts.push('contact'); return }
                if (this.viewed.contacts?.includes('contact')) { await this.fetchContact('phone'); this.viewed.contact = true; return }
                const ok = await this.ensureSession({ type: 'phone' });
                if (!ok) return;
                await this.logContact('contact');
                await this.fetchContact('phone');
                this.viewed.contacts.push('contact');
                this.viewed.contact = true;
            },

            async revealEmail(skipSession = false) {
                if (this.canSeeContacts) { this.viewed.email = true; return }
                if (skipSession) { await this.fetchContact('email'); this.viewed.email = true; if (!this.viewed.contacts.includes('email')) this.viewed.contacts.push('email'); return }
                if (this.viewed.contacts?.includes('email')) { await this.fetchContact('email'); this.viewed.email = true; return }
                const ok = await this.ensureSession({ type: 'email' });
                if (!ok) return;
                await this.logContact('email');
                await this.fetchContact('email');
                this.viewed.contacts.push('email');
                this.viewed.email = true;
            },

            async fetchContact(type) {
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=getStoreContact&id=' + encodeURIComponent(this.vendorId) + '&type=' + encodeURIComponent(type));
                    const data = await r.json();
                    if (data.success) {
                        if (type === 'phone') this.store.business_phone = data.phone || this.store.business_phone;
                        if (type === 'email') this.store.business_email = data.email || this.store.business_email;
                    }
                } catch (e) { }
            },

            async revealPrice(pr, evt) {
                const ok = await this.ensureSession({ type: 'price', pricingId: pr?.pricing_id });
                if (!ok) return;
                try {
                    const sid = this.sessionId();
                    if (pr?.pricing_id) await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=logPriceView', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'pricing_id=' + encodeURIComponent(pr.pricing_id) + '&session_id=' + encodeURIComponent(sid || '') });
                } catch (e) { }
                if (!this.viewed.prices.includes(pr.pricing_id)) this.viewed.prices.push(pr.pricing_id);
            },

            async revealPriceMobile(pr) {
                const ok = await this.ensureSession({ type: 'price', pricingId: pr?.pricing_id });
                if (!ok) return;
                try {
                    const sid = this.sessionId();
                    if (pr?.pricing_id) await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=logPriceView', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'pricing_id=' + encodeURIComponent(pr.pricing_id) + '&session_id=' + encodeURIComponent(sid || '') });
                } catch (e) { }
                if (!this.viewed.prices.includes(pr.pricing_id)) this.viewed.prices.push(pr.pricing_id);
                this.openPriceSheet(this.modals.prices.product);
            },

            expandPrices(p) { this.ensureSession({ type: 'expand-prices' }).then(ok => { if (ok) p._showAll = true }) },

            sessionId() { try { const s = JSON.parse(localStorage.getItem('session_event_log') || '{}'); return s.sessionID || null } catch (e) { return null } },

            async logProfileView() {
                if (this.auth.isAdminOrManager) return;
                const sid = this.sessionId();
                if (!sid || !this.vendorId) return;
                try {
                    await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=logProfileView', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'store_id=' + encodeURIComponent(this.vendorId) + '&session_id=' + encodeURIComponent(sid) });
                } catch (e) { }
            },

            async logContact(entity) {
                if (this.auth.isAdminOrManager) return;
                const sid = this.sessionId();
                if (!sid || !this.vendorId) return;
                try {
                    await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=logContactView', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'store_id=' + encodeURIComponent(this.vendorId) + '&entity=' + encodeURIComponent(entity) + '&session_id=' + encodeURIComponent(sid) });
                } catch (e) { }
            },

            async loadViewedEntities() {
                if (this.auth.isAdminOrManager) return;
                if (!this.auth.loggedIn) return;
                const sid = this.sessionId();
                if (!sid || !this.vendorId) return;
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageProfile.php?action=getViewedEntities&store_id=' + encodeURIComponent(this.vendorId) + '&session_id=' + encodeURIComponent(sid));
                    const data = await r.json();
                    if (data.success) {
                        this.viewed.contacts = data.viewed_contacts || [];
                        this.viewed.prices = data.viewed_prices || [];
                        if (this.viewed.contacts.includes('location')) this.viewed.location = true;
                        if (this.viewed.contacts.includes('contact')) { this.viewed.contact = true; await this.fetchContact('phone') }
                        if (this.viewed.contacts.includes('email')) { this.viewed.email = true; await this.fetchContact('email') }
                    }
                } catch (e) { }
            },

            async ensureSession(pending) {
                try {
                    const res = await checkSessionStatus();
                    if (!res) { if (pending) window.setPendingVendorAction(pending); this.promptLogin(); return false }
                    this.auth.loggedIn = true;
                    return true;
                } catch (e) { return false }
            },

            setPendingAndLogin(p) { window.setPendingVendorAction(p); this.promptLogin() },

            promptLogin() { if (typeof openAuthModal === 'function') openAuthModal() },

            copyLink() { navigator.clipboard.writeText(window.location.href).then(() => this.showToast('Link copied to clipboard!', 'success')).catch(() => this.showToast('Failed to copy link', 'error')) },
            shareWhatsApp() { const msg = `*${this.store?.name || 'Vendor'}* is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.\n\n${window.location.href}`; window.open(`https://wa.me/?text=${encodeURIComponent(msg)}`, '_blank') },
            shareFacebook() { const msg = `${this.store?.name || 'Vendor'} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`; window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}&quote=${encodeURIComponent(msg)}`, '_blank') },
            shareTwitter() { const msg = `${this.store?.name || 'Vendor'} is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.`; window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(msg)}&url=${encodeURIComponent(window.location.href)}`, '_blank') },
            shareLinkedIn() { const msg = `${this.store?.name || 'Vendor'} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`; window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}&title=${encodeURIComponent(this.store?.name || 'Vendor')}&summary=${encodeURIComponent(msg)}`, '_blank') },

            openVendorSell(p) { if (typeof openVendorSellModal === 'function') openVendorSellModal(p.id, p.name) },

            openPriceSheet(p) {
                this.modals.prices.product = p;
                const source = this.auth.canSeeAllCategories ? (Array.isArray(p.pricing) ? p.pricing : []) : p._viewPricing;
                this.modals.prices.entries = Array.isArray(source) ? source : [];
                this.modals.prices.visible = true;
                this.$nextTick(() => this.renderIcons());
            },
            closePriceSheet() {
                this.modals.prices.visible = false;
                this.modals.prices.product = null;
                this.modals.prices.entries = [];
            },

            async openBuyInStore(p) {
                if (this.auth.isAdmin) { this.showToast('Administrators cannot place store orders.', 'error'); return }
                if (this.auth.isOwnerOrManager) { this.showToast('Store owners or managers cannot place store orders.', 'error'); return }
                const ok = await this.ensureSession({ type: 'buy', productId: p.store_product_id });
                if (!ok) return;
                this.modals.buy.visible = true;
                this.modals.buy.loading = true;
                this.modals.buy.product = p;
                this.buyForm = { visitDate: '', packageId: '', quantity: 1, showAlt: false, altPhone: '', altEmail: '', notes: '' };
                this.initDatepicker();
                this.modals.buy.packages = (p.pricing || []).map(pr => pr);
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageBuyInStore.php?action=getUserInfo');
                    const data = await r.json();
                    if (data.success) {
                        const u = data.user || {};
                        this.userSummary.name = u.name || u.username || '';
                        this.userSummary.email = u.email || '';
                        this.userSummary.phone = u.phone || '';
                        this.modals.buy.loading = false;
                        this.$nextTick(() => this.renderIcons());
                    } else {
                        this.modals.buy.visible = false;
                        this.showErrorModal(data.message || 'Failed to load user information');
                    }
                } catch (e) {
                    this.modals.buy.visible = false;
                    this.showErrorModal('Network error. Please try again.');
                }
            },

            closeBuy() { this.modals.buy.visible = false; this.modals.error.visible = false },

            get capacityNote() {
                const opt = this.selectedPackageOption();
                if (!opt) return 'Minimum quantity: 1';
                const cat = opt.getAttribute('data-category');
                const cap = parseInt(opt.getAttribute('data-capacity')) || 1;
                if (cat === 'retail') return `Maximum quantity: ${cap}`;
                return `Minimum quantity: ${cap}`;
            },

            updateCapacity() {
                const opt = this.selectedPackageOption();
                if (!opt) { this.buyForm.quantity = 1; return }
                const cat = opt.getAttribute('data-category');
                const cap = parseInt(opt.getAttribute('data-capacity')) || 1;
                if (cat === 'retail') { this.buyForm.quantity = 1 } else { this.buyForm.quantity = cap }
            },

            decQty() {
                const opt = this.selectedPackageOption();
                const min = opt && opt.getAttribute('data-category') !== 'retail' ? parseInt(opt.getAttribute('data-capacity')) || 1 : 1;
                if (this.buyForm.quantity > min) this.buyForm.quantity--;
            },
            incQty() {
                const opt = this.selectedPackageOption();
                if (!opt) { this.buyForm.quantity++; return }
                const cat = opt.getAttribute('data-category');
                const cap = parseInt(opt.getAttribute('data-capacity')) || 9999;
                if (cat === 'retail') { if (this.buyForm.quantity < cap) this.buyForm.quantity++ } else { this.buyForm.quantity++ }
            },

            selectedPackageOption() {
                const sel = this.$root.querySelector('select[x-model="buyForm.packageId"]');
                if (!sel) return null;
                return sel.options[sel.selectedIndex] || null;
            },

            get summaryPackage() {
                const opt = this.selectedPackageOption();
                if (!opt) return '';
                const unit = opt.getAttribute('data-unit') || '';
                const cat = this.capitalize(opt.getAttribute('data-category') || '');
                const price = this.nf(opt.getAttribute('data-price') || 0);
                return `${unit} (${cat}) - UGX ${price}`;
            },

            get summaryDate() {
                if (!this.buyForm.visitDate) return '';
                const d = new Date(this.buyForm.visitDate + 'T00:00:00');
                return d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            initDatepicker() {
                const container = this.$root.querySelector('#datepicker-container');
                const today = new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Kampala' }));
                today.setHours(0, 0, 0, 0);
                let currentMonth = today.getMonth();
                let currentYear = today.getFullYear();
                this.buyForm.visitDate = this.ymd(today.getFullYear(), today.getMonth(), today.getDate());
                container.innerHTML = `
                <div class="datepicker">
                    <div class="datepicker-controls">
                        <button type="button" class="prev p-1 rounded hover:bg-gray-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                        <span class="month font-medium"></span>
                        <button type="button" class="next p-1 rounded hover:bg-gray-100 dark:hover:bg-slate-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                    </div>
                    <div class="datepicker-grid">
                        <div class="datepicker-day-header">Sun</div>
                        <div class="datepicker-day-header">Mon</div>
                        <div class="datepicker-day-header">Tue</div>
                        <div class="datepicker-day-header">Wed</div>
                        <div class="datepicker-day-header">Thu</div>
                        <div class="datepicker-day-header">Fri</div>
                        <div class="datepicker-day-header">Sat</div>
                    </div>
                </div>`;
                const monthEl = container.querySelector('.month');
                const grid = container.querySelector('.datepicker-grid');
                const prev = container.querySelector('.prev');
                const next = container.querySelector('.next');
                prev.addEventListener('click', () => { currentMonth--; if (currentMonth < 0) { currentMonth = 11; currentYear-- } render() });
                next.addEventListener('click', () => { currentMonth++; if (currentMonth > 11) { currentMonth = 0; currentYear++ } render() });
                const render = () => {
                    const names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    monthEl.textContent = `${names[currentMonth]} ${currentYear}`;
                    const headers = Array.from(grid.querySelectorAll('.datepicker-day-header'));
                    grid.innerHTML = '';
                    headers.forEach(h => grid.appendChild(h));
                    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
                    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
                    for (let i = 0; i < firstDay; i++) { const d = document.createElement('div'); d.className = 'datepicker-day'; grid.appendChild(d) }
                    for (let d = 1; d <= daysInMonth; d++) {
                        const el = document.createElement('div'); el.className = 'datepicker-day'; el.textContent = d;
                        const date = new Date(currentYear, currentMonth, d); date.setHours(0, 0, 0, 0);
                        const dateStr = this.ymd(currentYear, currentMonth, d);
                        if (date.toDateString() === today.toDateString()) el.classList.add('today');
                        if (date < today && date.toDateString() !== today.toDateString()) { el.classList.add('disabled') }
                        else {
                            el.addEventListener('click', () => { grid.querySelectorAll('.datepicker-day.selected').forEach(x => x.classList.remove('selected')); el.classList.add('selected'); this.buyForm.visitDate = dateStr; });
                        }
                        if (this.buyForm.visitDate === dateStr) el.classList.add('selected');
                        grid.appendChild(el);
                    }
                };
                render();
            },

            ymd(y, m, d) { const mm = String(m + 1).padStart(2, '0'); const dd = String(d).padStart(2, '0'); return `${y}-${mm}-${dd}` },

            async submitBuy() {
                if (!this.buyForm.visitDate) { this.showToast('Please select a visit date', 'error'); return }
                if (!this.buyForm.packageId) { this.showToast('Please select a package', 'error'); return }
                if (!this.buyForm.quantity || parseInt(this.buyForm.quantity) < 1) { this.showToast('Please enter a valid quantity', 'error'); return }
                this.modals.buy.submitting = true;
                const payload = {
                    productId: this.modals.buy.product.store_product_id,
                    visitDate: this.buyForm.visitDate,
                    packageId: this.buyForm.packageId,
                    quantity: this.buyForm.quantity,
                    altContact: this.buyForm.altPhone,
                    altEmail: this.buyForm.altEmail,
                    notes: this.buyForm.notes
                };
                try {
                    const res = await fetch(this.BASE_URL + 'fetch/manageBuyInStore.php?action=previewBuyInStore', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const data = await res.json();
                    this.modals.buy.submitting = false;
                    if (data && data.success) {
                        const fee = Number(data.fee || 0);
                        const bal = Number(data.balance || 0);
                        const remaining = bal - fee;
                        this.modals.confirm.payload = {
                            fee,
                            balance: bal,
                            remaining_balance: remaining,
                            can_submit: !!data.can_submit,
                            shortfall: Number(data.shortfall ?? Math.max(0, fee - bal)),
                            product: data.product || null
                        };
                        this.modals.confirm.form = payload;
                        this.modals.confirm.visible = true;
                    } else {
                        this.showToast(data.error || data.message || 'Could not load charges', 'error');
                    }
                } catch (e) {
                    this.modals.buy.submitting = false;
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            async submitBuyConfirmed() {
                if (!this.modals.confirm.form) return;
                this.modals.confirm.submitting = true;
                try {
                    const res = await fetch(this.BASE_URL + 'fetch/manageBuyInStore.php?action=submitBuyInStore', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.modals.confirm.form)
                    });
                    const data = await res.json();
                    this.modals.confirm.submitting = false;
                    if (data.success) {
                        this.modals.confirm.visible = false;
                        this.modals.buy.visible = false;
                        this.modals.success.payload = data || null;
                        this.modals.success.message = data.message || '';
                        this.modals.success.visible = true;
                    } else {
                        this.showToast(data.error || data.message || 'Failed to submit request', 'error');
                    }
                } catch (e) {
                    this.modals.confirm.submitting = false;
                    this.showToast('Network error. Please try again.', 'error');
                }
            },

            closeError() { this.modals.error.visible = false },
            closeSuccess() { this.modals.success.visible = false },

            showErrorModal(msg) { this.modals.error.message = msg; this.modals.error.visible = true },

            showToast(msg, type = 'success') { this.toast.message = msg; this.toast.type = type; this.toast.visible = true; setTimeout(() => { this.toast.visible = false }, 3000) },

            async onLogoFile(e) {
                const file = e.target.files?.[0]; if (!file) return;
                this.modals.logo.visible = true;
                this.logoCrop.file = file;
                const reader = new FileReader();
                reader.onload = () => {
                    const img = document.getElementById('cropper-image');
                    img.src = reader.result;
                    if (this.logoCrop.cropper) this.logoCrop.cropper.destroy();
                    this.logoCrop.cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, restore: false, guides: true, center: true, highlight: false, cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false });
                    this.logoCrop.visible = true;
                };
                reader.readAsDataURL(file);
            },

            closeLogoEditor() { this.modals.logo.visible = false; if (this.logoCrop.cropper) { this.logoCrop.cropper.destroy(); this.logoCrop.cropper = null } this.logoCrop.visible = false },

            async cropAndSaveLogo() {
                if (!this.logoCrop.cropper) { this.showToast('Please select an image first', 'error'); return }
                const canvas = this.logoCrop.cropper.getCroppedCanvas({ width: 512, height: 512, fillColor: '#fff', imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
                if (!canvas) { this.showToast('Failed to crop image', 'error'); return }
                canvas.toBlob(async (blob) => {
                    const fd = new FormData(); fd.append('logo', blob, 'logo.png');
                    try {
                        const up = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=uploadLogo', { method: 'POST', body: fd }).then(r => r.json());
                        if (up.success) {
                            const upd = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=updateStore', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: this.vendorId, temp_logo_path: up.temp_path }) }).then(r => r.json());
                            if (upd.success) { this.logoUrl = canvas.toDataURL(); this.closeLogoEditor(); this.showToast('Store logo updated successfully', 'success'); setTimeout(() => { window.location.reload() }, 1200) } else { this.showToast(upd.error || 'Failed to update store logo', 'error') }
                        } else { this.showToast(up.message || 'Failed to upload logo', 'error') }
                    } catch (e) { this.showToast('Failed to update store logo', 'error') }
                }, 'image/png');
            },

            async onCoverFile(e) {
                const file = e.target.files?.[0]; if (!file) return;
                const reader = new FileReader();
                reader.onload = () => {
                    const img = document.getElementById('cover-cropper-image');
                    img.src = reader.result;
                    if (this.coverCrop.cropper) this.coverCrop.cropper.destroy();
                    this.coverCrop.cropper = new Cropper(img, { aspectRatio: 3 / 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, restore: false, guides: true, center: true, highlight: false, cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false });
                    this.coverCrop.visible = true;
                };
                reader.readAsDataURL(file);
            },

            openLogoEditor() { this.modals.logo.visible = true },
            openCoverEditor() {
                this.modals.cover.visible = true;
                if (this.store?.vendor_cover_url) {
                    fetch(this.BASE_URL + this.store.vendor_cover_url).then(res => res.blob()).then(blob => {
                        const reader = new FileReader();
                        reader.onload = () => {
                            const img = document.getElementById('cover-cropper-image');
                            img.src = reader.result;
                            if (this.coverCrop.cropper) this.coverCrop.cropper.destroy();
                            this.coverCrop.cropper = new Cropper(img, { aspectRatio: 3 / 1, viewMode: 1, dragMode: 'move', autoCropArea: 1, restore: false, guides: true, center: true, highlight: false, cropBoxMovable: true, cropBoxResizable: true, toggleDragModeOnDblclick: false });
                            this.coverCrop.visible = true;
                        };
                        reader.readAsDataURL(blob);
                    }).catch(() => { });
                }
            },
            closeCoverEditor() { this.modals.cover.visible = false; if (this.coverCrop.cropper) { this.coverCrop.cropper.destroy(); this.coverCrop.cropper = null } this.coverCrop.visible = false },

            async cropAndSaveCover() {
                if (!this.coverCrop.cropper) { this.showToast('Please select an image first', 'error'); return }
                const canvas = this.coverCrop.cropper.getCroppedCanvas({ width: 1200, height: 400, fillColor: '#fff', imageSmoothingEnabled: true, imageSmoothingQuality: 'high' });
                if (!canvas) { this.showToast('Failed to crop image', 'error'); return }
                canvas.toBlob(async (blob) => {
                    const fd = new FormData(); fd.append('cover', blob, 'cover.png');
                    try {
                        const up = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=uploadVendorCover', { method: 'POST', body: fd }).then(r => r.json());
                        if (up.success) {
                            const upd = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=updateStore', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: this.vendorId, temp_cover_path: up.temp_path }) }).then(r => r.json());
                            if (upd.success) { this.coverUrl = canvas.toDataURL(); this.closeCoverEditor(); this.showToast('Cover photo updated successfully', 'success'); setTimeout(() => { window.location.reload() }, 1200) } else { this.showToast(upd.error || 'Failed to update cover photo', 'error') }
                        } else { this.showToast(up.message || 'Failed to upload cover photo', 'error') }
                    } catch (e) { this.showToast('Failed to update cover photo', 'error') }
                }, 'image/png');
            },

            openNameEditor() { this.editForms.name = this.store?.name || ''; this.modals.name.visible = true },
            async saveName() {
                const name = (this.editForms.name || '').trim();
                if (!name) { this.showToast('Store name cannot be empty', 'error'); return }
                try {
                    const res = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=updateStore', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: this.vendorId, name }) });
                    const data = await res.json();
                    if (data.success) { this.store.name = name; this.modals.name.visible = false; this.showToast('Store name updated successfully', 'success') }
                    else { this.showToast(data.error || 'Failed to update store name', 'error') }
                } catch (e) { this.showToast('Failed to update store name', 'error') }
            },

            openDescriptionEditor() { this.editForms.description = this.store?.description || ''; this.modals.description.visible = true },
            async saveDescription() {
                try {
                    const res = await fetch(this.BASE_URL + 'account/fetch/manageZzimbaStores.php?action=updateStore', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: this.vendorId, description: this.editForms.description || '' }) });
                    const data = await res.json();
                    if (data.success) { this.store.description = this.editForms.description || 'No description provided.'; this.modals.description.visible = false; this.showToast('Store description updated successfully', 'success') }
                    else { this.showToast(data.error || 'Failed to update store description', 'error') }
                } catch (e) { this.showToast('Failed to update store description', 'error') }
            },

            async loadReviews() {
                if (!this.vendorId) return;
                this.reviewsLoading = true;
                try {
                    const r = await fetch(this.BASE_URL + 'fetch/manageProductReviews.php?action=getStoreReviews&store_id=' + encodeURIComponent(this.vendorId));
                    const data = await r.json();
                    if (data.success) {
                        this.reviews = data.reviews || [];
                    }
                } catch (e) {
                    console.error('Failed to load reviews:', e);
                } finally {
                    this.reviewsLoading = false;
                    this.$nextTick(() => this.renderIcons());
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            }
        }));
    });
</script>

<?php
$mainContent = ob_get_clean();

$seoTags = [];
if ($storeData) {
    $seoTags = generateSeoMetaTags($storeData);
}

include __DIR__ . '/master.php';
?>