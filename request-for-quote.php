<?php
$pageTitle = 'Request for Quote';
$activeNav = 'quote';
require_once __DIR__ . '/config/config.php';
ob_start();

if (isset($_GET['ajax']) && $_GET['ajax'] === 'data') {
    header('Content-Type: application/json');
    header('Cache-Control: public, max-age=1800');
    try {
        $products = $pdo->query("
            SELECT p.id,p.title,p.description,p.meta_title,p.meta_description,p.meta_keywords,p.category_id,c.name AS category_name
            FROM products p
            JOIN product_categories c ON c.id=p.category_id
            WHERE p.status='published'
            ORDER BY p.title ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['products' => $products, 'timestamp' => time()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error occurred', 'products' => []]);
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'image') {
    header('Content-Type: application/json');
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';
    if (!$type || !$id) {
        echo json_encode(['image' => null]);
        exit;
    }
    $basePath = 'img/products/';
    $fullPath = __DIR__ . '/' . $basePath . $id . '/';
    if (!is_dir($fullPath)) {
        echo json_encode(['image' => null]);
        exit;
    }
    $allowed = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    $images = [];
    foreach (scandir($fullPath) as $f) {
        if ($f === '.' || $f === '..')
            continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed))
            $images[] = $f;
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

$loggedIn = !empty($_SESSION['user']['logged_in']);
$isAdmin = !empty($_SESSION['user']['is_admin']);
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="preconnect" href="https://cdn.jsdelivr.net" />
<style>
    :root {
        --bg: #ffffff;
        --fg: #111827;
        --muted: #6b7280;
        --card: #ffffff;
        --soft: #f9fafb;
        --soft2: #f3f4f6;
        --border: #e5e7eb;
        --accent: #ef4444;
        --accent2: #dc2626;
        --ok: #10b981;
        --warn: #f59e0b;
        --link: #2563eb
    }

    .dark {
        --bg: #0d0d0f;
        --fg: #e5e7eb;
        --muted: #a3a3a3;
        --card: #16171a;
        --soft: #111214;
        --soft2: #0f1113;
        --border: #2a2b2f;
        --accent: #ef4444;
        --accent2: #b91c1c;
        --ok: #10b981;
        --warn: #d97706;
        --link: #60a5fa
    }

    html,
    body {
        background: var(--bg);
        color: var(--fg)
    }

    .container {
        max-width: 1200px;
        margin: 0 auto
    }

    .form-group {
        position: relative
    }

    .floating-label {
        position: absolute;
        left: 1rem;
        top: .8rem;
        padding: 0 .25rem;
        background: var(--card);
        transition: .2s;
        pointer-events: none;
        color: var(--muted)
    }

    .dark .floating-label {
        background: transparent
    }

    .form-input {
        background: var(--card);
        color: var(--fg)
    }

    .form-input:focus {
        border-color: var(--accent);
        outline: none
    }

    .form-input:focus~.floating-label,
    .form-input:not(:placeholder-shown)~.floating-label {
        transform: translateY(-1.4rem) scale(.85);
        color: var(--fg)
    }

    input::placeholder,
    textarea::placeholder {
        color: #9aa0a6;
        opacity: .9
    }

    .dark input::placeholder,
    .dark textarea::placeholder {
        color: #cbd5e1;
        opacity: .55
    }

    .map-search-input::placeholder {
        color: #9aa0a6;
        opacity: .9
    }

    .dark .map-search-input::placeholder {
        color: #cbd5e1;
        opacity: .55
    }

    .form-card {
        background: var(--card);
        border-radius: .75rem;
        box-shadow: 0 4px 10px -2px rgba(0, 0, 0, .08)
    }

    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: .5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .08)
    }

    .item-report {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 600px;
        border-radius: .5rem;
        overflow: hidden
    }

    .item-report thead {
        background: var(--soft2)
    }

    .item-report th {
        padding: .75rem 1rem;
        font-weight: 600;
        text-align: left;
        color: var(--fg);
        border-bottom: 1px solid var(--border);
        white-space: nowrap
    }

    .item-report td {
        padding: .75rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--muted);
        max-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .item-report .col-brand {
        width: 40%;
        min-width: 200px
    }

    .item-report .col-size {
        width: 35%;
        min-width: 150px
    }

    .item-report .col-quantity {
        width: 15%;
        min-width: 80px
    }

    .item-report .col-actions {
        width: 10%;
        min-width: 80px;
        text-align: center
    }

    .item-report tbody tr {
        transition: .2s
    }

    .item-report tbody tr:hover {
        background: var(--soft)
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        transition: .2s;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .06);
        gap: .5rem
    }

    .btn-primary {
        background: linear-gradient(90deg, var(--accent), var(--accent2));
        color: #fff
    }

    .btn-primary:hover {
        filter: brightness(.95)
    }

    .btn-secondary {
        background: linear-gradient(90deg, var(--ok), #059669);
        color: #fff
    }

    .btn-topup {
        background: linear-gradient(90deg, var(--warn), #b45309);
        color: #fff
    }

    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: .5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .15);
        z-index: 60;
        display: none
    }

    .search-dropdown.show {
        display: block
    }

    .search-dropdown-item {
        padding: .75rem 1rem;
        cursor: pointer;
        transition: .2s;
        border-bottom: 1px solid var(--soft2);
        display: flex;
        align-items: center;
        gap: .75rem;
        color: var(--fg)
    }

    .search-dropdown-item:hover {
        background: var(--soft)
    }

    .search-dropdown-header {
        padding: .5rem 1rem;
        font-size: .75rem;
        font-weight: 700;
        color: var(--muted);
        background: var(--soft);
        border-bottom: 1px solid var(--border)
    }

    .search-note {
        background: #eff6ff;
        border-left: 4px solid var(--link);
        padding: .75rem 1rem;
        margin-bottom: 1rem;
        border-radius: 0 .375rem .375rem 0;
        color: #1d4ed8
    }

    .search-image {
        transition: opacity .3s
    }

    .search-image.loading {
        opacity: .5
    }

    .required-star {
        color: var(--accent);
        font-weight: 700
    }

    .action-icon {
        cursor: pointer;
        transition: .2s;
        padding: .25rem;
        border-radius: .25rem
    }

    .action-icon:hover {
        transform: scale(1.06);
        background: rgba(0, 0, 0, .05)
    }

    #map {
        height: 350px;
        width: 100%;
        border-radius: .5rem;
        flex: 1;
        min-height: 250px
    }

    .map-search-container {
        position: relative;
        margin-bottom: 1rem;
        flex-shrink: 0
    }

    .map-search-input {
        width: 100%;
        padding: .75rem 1rem;
        border: 2px solid var(--border);
        border-radius: .5rem;
        font-size: 1rem;
        outline: none;
        background: var(--card);
        color: var(--fg)
    }

    .map-search-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, .1)
    }

    .map-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: .5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none
    }

    .map-search-result {
        padding: .75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid var(--soft2);
        transition: .2s;
        color: var(--fg)
    }

    .map-search-result:hover {
        background: var(--soft)
    }

    .location-display {
        background: var(--soft);
        border: 1px solid var(--border);
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin-top: .5rem;
        font-size: .875rem;
        color: var(--muted)
    }

    .item-limit-notice {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin-bottom: 1rem;
        font-size: .875rem;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: .5rem
    }

    .cors-notice {
        background: #fef2f2;
        border: 1px solid #f87171;
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin-bottom: 1rem;
        font-size: .875rem;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: .5rem
    }

    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem
    }

    #mobile-app {
        display: none
    }

    .m-head {
        position: sticky;
        top: 0;
        z-index: 30;
        background: var(--bg);
        border-bottom: 1px solid var(--border);
        padding: .8rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between
    }

    .m-title {
        font-weight: 700
    }

    .m-wrap {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem
    }

    .m-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: .75rem;
        padding: 1rem
    }

    .m-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem
    }

    .m-btn {
        padding: .85rem 1rem;
        border-radius: .75rem;
        min-height: 48px
    }

    .m-primary {
        background: linear-gradient(90deg, var(--accent), var(--accent2));
        color: #fff
    }

    .m-ghost {
        border: 1px solid var(--border);
        background: transparent;
        color: var(--fg)
    }

    .m-list {
        display: grid;
        gap: .75rem
    }

    .m-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        border: 1px solid var(--border);
        border-radius: .75rem;
        padding: .75rem;
        background: var(--card)
    }

    .m-meta {
        font-size: .8rem;
        color: var(--muted)
    }

    .m-fab {
        position: fixed;
        right: 1rem;
        z-index: 40;
        bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px))
    }

    .m-fab button {
        height: 3.25rem;
        width: 3.25rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(90deg, var(--ok), #059669);
        color: #fff;
        box-shadow: 0 10px 18px rgba(0, 0, 0, .18)
    }

    .error-note {
        background: #fef2f2;
        border: 1px solid #dc2626;
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin: .5rem 0;
        font-size: .875rem;
        color: #b91c1c;
        display: flex;
        align-items: center;
        gap: .5rem
    }

    [x-cloak] {
        display: none !important
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

    @media (max-width:768px) {
        #mobile-app {
            display: block
        }

        #web-ui {
            display: none
        }

        .map-modal-content {
            height: 80vh
        }

        #map {
            height: 250px;
            min-height: 200px
        }

        .item-report th,
        .item-report td {
            padding: .5rem .75rem;
            font-size: .875rem
        }

        .action-icon {
            padding: .5rem
        }
    }

    @media (max-width:480px) {
        #map {
            height: 200px;
            min-height: 180px
        }

        .item-report th,
        .item-report td {
            padding: .5rem;
            font-size: .8rem
        }

        .item-report .col-brand {
            min-width: 150px
        }

        .item-report .col-size {
            min-width: 120px
        }

        .item-report .col-quantity {
            min-width: 60px
        }

        .item-report .col-actions {
            min-width: 60px
        }
    }
</style>

<div x-data="rfqApp" x-init="init()" class="relative">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="topbar">
            <h1 class="text-xl font-bold">Request for Quote</h1>
            <div></div>
        </div>

        <div id="web-ui" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="form-card p-6 md:p-8">
                    <h2 class="text-2xl font-semibold mb-2">RFQ Details</h2>
                    <p class="text-sm mb-6">Fields marked with <span class="required-star">*</span> are required</p>
                    <form @submit.prevent="onSubmit" id="rfq-form" class="space-y-6" novalidate autocomplete="off">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium">List of Items <span class="required-star">*</span></h2>
                                <button type="button" @click="openItemModal()"
                                    class="btn btn-secondary px-4 py-2 text-sm">
                                    <i class="fas fa-plus"></i>
                                    Add Item
                                </button>
                            </div>

                            <div class="error-note" x-show="errors.items" x-cloak>
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.items"></span>
                            </div>

                            <div id="item-limit-notice" class="item-limit-notice" x-show="items.length>=MAX_ITEMS"
                                x-cloak>
                                <i class="fas fa-info-circle"></i>
                                <span>You can add upto 5 items per quote request.</span>
                            </div>

                            <div class="bg-white dark:bg-[var(--card)] rounded-lg overflow-hidden"
                                style="background: var(--card)">
                                <div id="items-container" class="w-full">
                                    <div class="table-container" x-show="items.length>0">
                                        <table class="item-report">
                                            <thead>
                                                <tr>
                                                    <th class="col-brand">Brand/Material</th>
                                                    <th class="col-size">Size/Specification</th>
                                                    <th class="col-quantity">Quantity</th>
                                                    <th class="col-actions">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(it, idx) in items" :key="idx">
                                                    <tr>
                                                        <td class="col-brand" :title="it.brand" x-text="it.brand"></td>
                                                        <td class="col-size" :title="it.size" x-text="it.size"></td>
                                                        <td class="col-quantity" x-text="it.quantity"></td>
                                                        <td class="col-actions">
                                                            <div class="flex justify-center gap-2">
                                                                <i class="fas fa-edit action-icon"
                                                                    @click="openItemModal(idx)"></i>
                                                                <i class="fas fa-trash-alt action-icon"
                                                                    @click="openDeleteModal(idx)"></i>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div id="empty-items-state"
                                        class="flex flex-col items-center justify-center text-center py-10 px-4 rounded-lg border"
                                        style="background: var(--card); border-color: var(--border)"
                                        x-show="items.length===0">
                                        <div class="mb-4" style="color: #9ca3af">
                                            <i class="fas fa-clipboard-list text-5xl"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold mb-2">No Items Added</h3>
                                        <p class="text-sm mb-6">Click the "Add Item" button to add materials. Maximum 5
                                            items allowed.</p>
                                        <button type="button" @click="openItemModal()"
                                            class="btn btn-primary px-5 py-2.5 text-sm">
                                            <i class="fas fa-plus"></i>
                                            Add First Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="text" id="location" name="location" required placeholder=" " readonly
                                class="form-input block w-full px-3 py-3 border rounded-md cursor-pointer"
                                style="border-color: var(--border)" autocomplete="new-address"
                                :value="selectedLocation?.address || ''" @click="openLocationModal" />
                            <label for="location" class="floating-label">Site Location <span
                                    class="required-star">*</span></label>

                            <div class="mt-2 text-sm text-red-600" x-text="errors.location" x-show="errors.location"
                                x-cloak></div>

                            <div id="location-display" class="location-display" x-show="selectedLocation" x-cloak>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium" id="selected-address"
                                            x-text="selectedLocation?.address"></div>
                                        <div class="text-xs" style="color: var(--muted)" id="selected-coordinates"
                                            x-text="selectedLocation ? (selectedLocation.lat.toFixed(6)+', '+selectedLocation.lng.toFixed(6)) : ''">
                                        </div>
                                    </div>
                                    <button type="button" @click="openLocationModal" class="text-sm"
                                        style="color: var(--link)">
                                        <i class="fas fa-edit mr-1"></i>
                                        Change
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="resetForm" class="btn m-ghost px-5 py-3 text-sm">
                                <i class="fas fa-times-circle"></i>
                                Cancel
                            </button>
                            <button type="submit" id="submit-btn" class="btn btn-primary px-5 py-3 text-sm"
                                :disabled="submitting">
                                <span x-show="!submitting"><i class="fas fa-paper-plane"></i> Submit Request</span>
                                <span x-show="submitting"><i class="fas fa-spinner fa-spin"></i> Submitting...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="m-card mb-6 border-l-4" style="border-color: var(--accent)">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3"
                            style="background: rgba(239,68,68,.15)">
                            <i class="fas fa-info-circle" style="color: var(--accent)"></i>
                        </div>
                        <h2 class="text-lg font-semibold">Note</h2>
                    </div>
                    <div class="space-y-3 text-sm" style="color: var(--muted)">
                        <p class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3" style="color: var(--accent)"></i>
                            <span>Click on the site location field to select your delivery location on the map.</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-plus-circle mt-1 mr-3" style="color: var(--accent)"></i>
                            <span>Use the "Add Item" button to request multiple items (maximum 5).</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-edit mt-1 mr-3" style="color: var(--accent)"></i>
                            <span>Edit items via the actions column.</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-save mt-1 mr-3" style="color: var(--accent)"></i>
                            <span>Your form data is auto-saved for a short while.</span>
                        </p>
                    </div>
                </div>

                <div class="m-card">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-content mr-3"
                            style="background: rgba(37,99,235,.12)">
                            <i class="fas fa-clipboard-check" style="color: var(--link)"></i>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm" style="color: var(--muted)">
                        <p class="font-medium">For each item, specify:</p>
                        <ul class="space-y-1 pl-6">
                            <li class="flex items-start">
                                <i class="fas fa-trademark mt-1 mr-3" style="color: #9ca3af"></i>
                                <span>Brand or material</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-ruler-combined mt-1 mr-3" style="color: #9ca3af"></i>
                                <span>Size/specification</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-sort-amount-up mt-1 mr-3" style="color: #9ca3af"></i>
                                <span>Quantity</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div id="mobile-app">
            <div class="m-head">
                <div class="m-title">RFQ</div>
            </div>

            <div class="m-wrap">
                <div class="m-card">
                    <div class="m-row">
                        <div class="font-semibold">Site Location <span class="required-star">*</span></div>
                        <button class="m-ghost m-btn px-3 py-1 text-sm" @click="openLocationModal">
                            <i class="fas fa-map-marker-alt"></i>
                            Set
                        </button>
                    </div>
                    <div id="m-location" class="mt-2 text-sm" style="color: var(--muted)"
                        x-text="selectedLocation?.address || 'Tap Set to choose on map'"></div>
                    <div class="error-note mt-2" x-show="errors.location" x-cloak>
                        <i class="fas fa-exclamation-circle"></i>
                        <span x-text="errors.location"></span>
                    </div>
                </div>

                <div class="m-card">
                    <div class="m-row">
                        <div class="font-semibold">Items <span class="required-star">*</span></div>
                        <button @click="openItemModal()" class="m-btn m-ghost px-3 py-1 text-sm">
                            <i class="fas fa-plus"></i>
                            Add
                        </button>
                    </div>
                    <div id="m-empty" class="mt-3 text-sm" style="color: var(--muted)" x-show="items.length===0">No
                        items added yet.</div>
                    <div id="m-items" class="m-list mt-3">
                        <template x-for="(it, i) in items" :key="i">
                            <div class="m-item">
                                <div>
                                    <div class="font-medium" x-text="it.brand"></div>
                                    <div class="m-meta" x-text="it.size"></div>
                                    <div class="m-meta" x-text="'Qty: '+it.quantity"></div>
                                </div>
                                <div class="flex gap-2">
                                    <button class="m-btn m-ghost px-3 py-1 text-sm" @click="openItemModal(i)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="m-btn m-ghost px-3 py-1 text-sm" @click="openDeleteModal(i)">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="error-note mt-2" x-show="errors.items" x-cloak>
                        <i class="fas fa-exclamation-circle"></i>
                        <span x-text="errors.items"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <button @click="resetForm" class="m-btn m-ghost">
                            <i class="fas fa-times-circle"></i>
                            Cancel
                        </button>
                        <button @click="onSubmit" class="m-btn m-primary" :disabled="submitting">
                            <span x-show="!submitting"><i class="fas fa-paper-plane"></i> Submit</span>
                            <span x-show="submitting"><i class="fas fa-spinner fa-spin"></i> Submitting...</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="m-fab">
                <button id="m-fab" aria-label="add item" @click="openItemModal()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <div x-show="modals.location.visible" x-cloak class="fixed inset-0 z-[1200]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeLocationModal"></div>
        <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[96%] lg:max-w-3xl bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-slate-100">Select Delivery
                        Location</h3>
                    <button @click="closeLocationModal"
                        class="text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white p-2 rounded-full hover:bg-white/60 dark:hover:bg-white/10">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-scroll p-4 sm:p-6">
                    <div id="cors-notice" class="cors-notice" x-show="corsIssue" x-cloak>
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <span class="flex-1">Map search unavailable. Click the map to select your location in
                            Uganda.</span>
                        <button @click="corsIssue=false" class="px-2">×</button>
                    </div>
                    <div class="map-search-container">
                        <input type="text" id="map-search-input" placeholder="Search for a location in Uganda..."
                            class="map-search-input" :disabled="corsIssue" @input="onMapSearchInput">
                        <div id="map-search-results" class="map-search-results"></div>
                    </div>
                    <div id="map"></div>
                </div>
                <div
                    class="md:hidden sticky bottom-0 inset-x-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="closeLocationModal"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-200">Cancel</button>
                        <button @click="confirmLocation" :disabled="!selectedLocation"
                            class="px-3 py-2 rounded-lg bg-primary text-white disabled:opacity-50">Confirm
                            Location</button>
                    </div>
                </div>
                <div class="hidden md:block border-t border-gray-200 dark:border-slate-800 p-4">
                    <div class="flex justify-end gap-3">
                        <button @click="closeLocationModal" class="m-btn m-ghost px-4 py-2 text-sm">Cancel</button>
                        <button @click="confirmLocation" :disabled="!selectedLocation"
                            class="btn btn-primary px-4 py-2 text-sm"><i class="fas fa-map-marker-alt mr-2"></i>Confirm
                            Location</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.item.visible" x-cloak class="fixed inset-0 z-[1200]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeItemModal"></div>
        <div class="fixed inset-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[94%] lg:max-w-xl bg-white dark:bg-slate-900 rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="p-4 sm:p-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-slate-100"
                        x-text="modals.item.index===-1?'Add New Item':'Edit Item'"></h3>
                    <button @click="closeItemModal"
                        class="text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white p-2 rounded-full hover:bg-white/60 dark:hover:bg-white/10">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-scroll p-4 sm:p-6">
                    <div class="search-note">
                        <p class="text-sm"><i class="fas fa-info-circle mr-2"></i>Start typing to see product
                            suggestions or type your own.</p>
                    </div>
                    <form @submit.prevent="saveItem" class="space-y-4">
                        <div class="form-group">
                            <input type="text" id="item-brand" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border rounded-md"
                                style="border-color: var(--border)" autocomplete="off" x-model="itemForm.brand"
                                @input="onBrandInput" @focus="onBrandFocus" />
                            <label for="item-brand" class="floating-label">Brand/Material <span
                                    class="required-star">*</span></label>
                            <div id="brand-search-dropdown" class="search-dropdown"></div>
                        </div>
                        <div class="form-group">
                            <input type="text" id="item-size" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border rounded-md"
                                style="border-color: var(--border)" autocomplete="off" x-model="itemForm.size" />
                            <label for="item-size" class="floating-label">Size/Specification <span
                                    class="required-star">*</span></label>
                        </div>
                        <div class="form-group">
                            <input type="number" id="item-quantity" required placeholder=" " min="1"
                                class="form-input block w-full px-3 py-3 border rounded-md"
                                style="border-color: var(--border)" autocomplete="off"
                                x-model.number="itemForm.quantity" />
                            <label for="item-quantity" class="floating-label">Quantity <span
                                    class="required-star">*</span></label>
                        </div>
                        <div class="hidden md:flex justify-end gap-3 pt-3">
                            <button type="button" @click="closeItemModal"
                                class="m-btn m-ghost px-4 py-2 text-sm">Cancel</button>
                            <button type="submit" class="btn btn-secondary px-4 py-2 text-sm"><i
                                    class="fas fa-save"></i> Save Item</button>
                        </div>
                    </form>
                </div>
                <div
                    class="md:hidden sticky bottom-0 inset-x-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="closeItemModal"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-200">Cancel</button>
                        <button @click="saveItem" class="px-3 py-2 rounded-lg bg-green-600 text-white">Save
                            Item</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.delete.visible" x-cloak
        class="fixed inset-0 z-[1200] bg-black/60 backdrop-blur-sm flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16"
                    style="background:#fee2e2;border-radius:9999px;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-exclamation-triangle" style="color: var(--accent)"></i>
                </div>
            </div>
            <p class="text-center mb-6">Remove this item from your quote request?</p>
            <div class="flex justify-between">
                <button @click="modals.delete.visible=false"
                    class="px-4 py-2 rounded-md border border-gray-300 dark:border-slate-700">Cancel</button>
                <button @click="confirmDelete" class="px-4 py-2 rounded-md text-white"
                    style="background: linear-gradient(90deg, #ef4444, #b91c1c)"><i
                        class="fas fa-trash-alt mr-1"></i>Delete Item</button>
            </div>
        </div>
    </div>

    <div x-show="modals.limit.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1300] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div
                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 mb-4">
                <i class="fas fa-info text-yellow-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-slate-100 mb-2">Item Limit Reached</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-4">You can add up to 5 items to a single request.</p>
            <button @click="modals.limit.visible=false"
                class="w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90">OK</button>
        </div>
    </div>

    <div x-show="modals.confirm.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1300] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-2">Review & Confirm</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-4">A processing fee will be charged for this quote request.
            </p>
            <div class="rounded-md bg-gray-50 dark:bg-slate-800 p-4 mb-4">
                <div class="flex justify-between"><span class="text-gray-600 dark:text-slate-300">Fee</span><span
                        class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX '+nf(walletInfo.fee||0)"></span></div>
                <div class="flex justify-between"><span class="text-gray-600 dark:text-slate-300">Current
                        Balance</span><span class="font-medium text-gray-900 dark:text-slate-100"
                        x-text="'UGX '+nf(walletInfo.balance||0)"></span></div>
                <div class="flex justify-between"><span class="text-gray-600 dark:text-slate-300">Balance
                        After</span><span
                        :class="(walletInfo.balance - walletInfo.fee)<0?'text-red-600 font-semibold':'font-medium text-gray-900 dark:text-slate-100'"
                        x-text="'UGX '+nf((walletInfo.balance - walletInfo.fee) || 0)"></span></div>
                <div class="flex justify-between text-red-600" x-show="!walletInfo.canSubmit">
                    <span>Shortfall</span><span class="font-semibold"
                        x-text="'UGX '+nf(Math.max(0,(walletInfo.fee - walletInfo.balance)))"></span></div>
            </div>
            <p class="text-sm text-red-600 mt-1" x-show="!walletInfo.canSubmit">Insufficient balance. Please top up your
                wallet to continue.</p>
            <div class="mt-6 flex justify-between">
                <button @click="modals.confirm.visible=false"
                    class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-md text-gray-700 dark:text-slate-200 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800">Back</button>
                <template x-if="walletInfo.canSubmit">
                    <button @click="submitRFQ" :disabled="submitting"
                        class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 disabled:opacity-50"><span
                            x-show="!submitting">Confirm & Submit</span><span x-show="submitting"
                            class="inline-flex items-center"><i
                                class="fas fa-spinner fa-spin mr-2"></i>Submitting...</span></button>
                </template>
                <template x-if="!walletInfo.canSubmit">
                    <button @click="goTopUp" class="px-4 py-2 btn-topup rounded-md">Top Up Wallet</button>
                </template>
            </div>
        </div>
    </div>

    <div x-show="modals.nowallet.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1200] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-2">No Zzimba Wallet Found</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-6">Activate your Zzimba Wallet to submit a quote request.</p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="modals.nowallet.visible=false"
                    class="px-4 py-2 rounded-md border border-gray-300 dark:border-slate-700">Close</button>
                <button @click="goTopUp" class="px-4 py-2 rounded-md bg-primary text-white">Activate Wallet</button>
            </div>
        </div>
    </div>

    <div x-show="modals.success.visible" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1200] flex items-center justify-center"
        x-transition.opacity>
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md p-6 text-center">
            <div
                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 mb-4">
                <i class="fas fa-check text-green-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-slate-100 mb-2">Quote Request Submitted</h3>
            <p class="text-gray-600 dark:text-slate-300 mb-4" x-text="successMessage"></p>
            <div class="grid grid-cols-2 gap-2">
                <button @click="modals.success.visible=false"
                    class="px-4 py-2 rounded-md border border-gray-300 dark:border-slate-700">Close</button>
                <button @click="viewQuotations" class="px-4 py-2 rounded-md bg-primary text-white">View My
                    Quotations</button>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
<script>
    window.__pendingRFQAction = null;
    window.setPendingRFQAction = (a) => { window.__pendingRFQAction = a || null; };
    (function () {
        const wrap = () => {
            const orig = window.updateUIAfterLogin;
            window.updateUIAfterLogin = function (user) {
                try { typeof orig === 'function' && orig(user); } catch (e) { }
                try { window.dispatchEvent(new CustomEvent('zz:session-login', { detail: user || {} })); } catch (e) { }
                const el = document.querySelector('[x-data="rfqApp"]');
                if (el && el.__x) { el.__x.$data.handlePostLogin(user || {}) }
            };
        };
        if (document.readyState === 'complete' || document.readyState === 'interactive') { wrap(); } else { document.addEventListener('DOMContentLoaded', wrap); }
    })();
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rfqApp', () => ({
            BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
            API_BASE: (window.BASE_URL || '<?= BASE_URL ?>') + 'fetch/manageRFQ',
            PAGE_TITLE: '<?= addslashes($pageTitle) ?>',
            MAX_ITEMS: 5,
            IS_LOGGED_IN: <?= $loggedIn ? 'true' : 'false' ?>,
            IS_ADMIN: <?= $isAdmin ? 'true' : 'false' ?>,
            items: [],
            itemForm: { brand: '', size: '', quantity: 1 },
            modals: { location: { visible: false }, item: { visible: false, index: -1 }, delete: { visible: false, index: -1 }, confirm: { visible: false }, nowallet: { visible: false }, success: { visible: false }, limit: { visible: false } },
            selectedLocation: null,
            map: null,
            marker: null,
            corsIssue: false,
            submitting: false,
            walletInfo: { balance: 0, fee: 0, canSubmit: false, noWallet: false },
            SEARCH_DATA: { products: [] },
            fuseProducts: null,
            searchInitialized: false,
            imageCache: new Map(),
            isSelectionMade: false,
            lastActivityTime: Date.now(),
            successMessage: '',
            errors: { location: '', items: '' },
            get UGANDA_BOUNDS() { return { north: 4.234077, south: -1.484456, east: 35.036133, west: 29.573252 } },

            init() {
                this.updateItemsDisplay();
                this.loadFormData();
                this.clearErrors();
                this.checkWalletBalance();
                this.loadSearchData();
                document.addEventListener('click', (e) => {
                    const brand = this.$root.querySelector('#item-brand');
                    const dd = this.$root.querySelector('#brand-search-dropdown');
                    if (dd && !dd.contains(e.target) && brand && !brand.contains(e.target)) dd.style.display = 'none';
                    const a = this.$root.querySelector('#map-search-input');
                    const b = this.$root.querySelector('#map-search-results');
                    if (a && b && !a.contains(e.target) && !b.contains(e.target)) b.style.display = 'none';
                });
                window.addEventListener('zz:session-login', e => this.handlePostLogin(e.detail || {}));
            },

            handlePostLogin(user) {
                this.IS_LOGGED_IN = true;
                this.checkWalletBalance().then(() => {
                    if (window.__pendingRFQAction && window.__pendingRFQAction.type === 'submit-rfq') {
                        if (this.IS_ADMIN) { window.setPendingRFQAction(null); return }
                        this.continueSubmitAfterAuth();
                        window.setPendingRFQAction(null);
                    }
                });
            },

            openItemModal(idx = -1) {
                if (idx === -1 && this.items.length >= this.MAX_ITEMS) { this.errors.items = `You can add up to ${this.MAX_ITEMS} items.`; this.modals.limit.visible = true; return }
                this.clearErrors();
                this.modals.item.index = idx;
                if (idx === -1) { this.itemForm = { brand: '', size: '', quantity: 1 }; } else { const it = this.items[idx]; this.itemForm = { brand: it.brand, size: it.size, quantity: it.quantity }; }
                this.isSelectionMade = false;
                this.modals.item.visible = true;
                this.$nextTick(() => { });
            },
            closeItemModal() { this.modals.item.visible = false; this.isSelectionMade = false; const dd = this.$root.querySelector('#brand-search-dropdown'); if (dd) dd.style.display = 'none' },
            saveItem() {
                const b = (this.itemForm.brand || '').trim();
                const s = (this.itemForm.size || '').trim();
                const q = parseInt(this.itemForm.quantity || 0);
                if (!b || !s || !q || q <= 0) { return }
                const obj = { brand: b, size: s, quantity: q };
                if (this.modals.item.index === -1) {
                    if (this.items.length < this.MAX_ITEMS) {
                        this.items.push(obj);
                    } else {
                        this.errors.items = `You can add up to ${this.MAX_ITEMS} items.`;
                        this.modals.limit.visible = true;
                    }
                } else {
                    this.items[this.modals.item.index] = obj;
                }
                this.modals.item.visible = false;
                this.isSelectionMade = false;
                this.updateItemsDisplay();
                this.saveFormData();
                this.clearErrors();
            },

            openDeleteModal(idx) { this.modals.delete.index = idx; this.modals.delete.visible = true },
            confirmDelete() {
                const i = this.modals.delete.index;
                if (i >= 0 && i < this.items.length) this.items.splice(i, 1);
                this.modals.delete.visible = false;
                this.updateItemsDisplay();
                this.saveFormData();
            },

            openLocationModal() {
                this.clearErrors();
                this.modals.location.visible = true;
                this.$nextTick(() => {
                    if (!this.map) {
                        this.initializeMap();
                    } else {
                        this.map.invalidateSize();
                        if (this.selectedLocation) {
                            this.map.setView([this.selectedLocation.lat, this.selectedLocation.lng], 15);
                            this.setMapMarker(this.selectedLocation.lat, this.selectedLocation.lng);
                        }
                    }
                });
            },
            closeLocationModal() { this.modals.location.visible = false },
            confirmLocation() { this.updateLocationDisplay(); this.modals.location.visible = false; this.saveFormData(); this.clearErrors() },

            onSubmit() {
                this.clearErrors();
                let hasError = false;
                if (!this.selectedLocation) { this.errors.location = 'Please select a delivery location.'; hasError = true }
                if (this.items.length === 0) { this.errors.items = 'Please add at least one item.'; hasError = true }
                if (this.items.length > this.MAX_ITEMS) { this.errors.items = `You can add up to ${this.MAX_ITEMS} items.`; hasError = true }
                if (hasError) { return }
                this.ensureSession({ type: 'submit-rfq' }).then(ok => {
                    if (!ok) return;
                    if (this.walletInfo.noWallet) { this.modals.nowallet.visible = true; return }
                    if (this.walletInfo.fee > 0) { this.modals.confirm.visible = true } else { this.submitRFQ() }
                });
            },

            submitRFQ() {
                const payload = { location: this.selectedLocation, items: this.items };
                this.submitting = true;
                fetch(this.API_BASE + '?action=submitRFQ', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
                    .then(r => r.json()).then(d => {
                        this.submitting = false;
                        if (d && d.success) {
                            let msg = 'Thank you! Your quote request has been received.';
                            if (d.fee_charged > 0) { msg += ` A fee of UGX ${this.nf(d.fee_charged)} has been deducted. Remaining balance UGX ${this.nf(d.remaining_balance)}.` }
                            this.successMessage = msg;
                            this.modals.confirm.visible = false;
                            this.modals.success.visible = true;
                            this.items = [];
                            this.selectedLocation = null;
                            this.updateItemsDisplay();
                            this.saveFormData();
                            localStorage.removeItem('rfq_form_data');
                            this.checkWalletBalance();
                            this.clearErrors();
                        } else if (d && d.error === 'User wallet not found') {
                            this.modals.confirm.visible = false;
                            this.modals.nowallet.visible = true;
                        }
                    }).catch(() => { this.submitting = false; });
            },

            continueSubmitAfterAuth() {
                if (!this.selectedLocation || this.items.length === 0 || this.items.length > this.MAX_ITEMS) return;
                if (this.walletInfo.noWallet) { this.modals.nowallet.visible = true; return }
                if (this.walletInfo.fee > 0) { this.modals.confirm.visible = true } else { this.submitRFQ() }
            },

            goTopUp() {
                try { localStorage.setItem('return_url', window.location.href); localStorage.setItem('return_title', this.PAGE_TITLE) } catch (e) { }
                window.location.href = this.BASE_URL + 'account/zzimba-credit';
            },

            viewQuotations() { window.location.href = this.BASE_URL + 'account/quotations' },

            resetForm() {
                this.items = [];
                this.selectedLocation = null;
                this.updateItemsDisplay();
                localStorage.removeItem('rfq_form_data');
                this.clearErrors();
            },

            updateItemsDisplay() { },

            escapeHtml(text) { const d = document.createElement('div'); d.textContent = text; return d.innerHTML },

            nf(n) { try { return new Intl.NumberFormat('en-UG').format(n) } catch (e) { return n } },

            checkSession() {
                return fetch(this.BASE_URL + 'fetch/check-session.php').then(r => r.json()).then(d => {
                    this.IS_LOGGED_IN = !!d.logged_in;
                    return { logged_in: !!d.logged_in, user: d.user || {} };
                }).catch(() => ({ logged_in: false }));
            },

            ensureSession(pending) {
                return this.checkSession().then(s => {
                    if (!s.logged_in) {
                        this.saveFormData();
                        window.setPendingRFQAction(pending);
                        if (typeof openAuthModal === 'function') { openAuthModal() } else { alert('Please log in to continue.') }
                        return false;
                    }
                    if (s.user && s.user.is_admin) { alert('Admin users cannot submit quote requests.'); return false }
                    return true;
                });
            },

            checkWalletBalance() {
                if (!this.IS_LOGGED_IN) return Promise.resolve();
                return fetch(this.API_BASE + '?action=checkWalletBalance').then(r => r.json()).then(d => {
                    if (d && d.success) { this.walletInfo = { balance: d.balance, fee: d.fee, canSubmit: d.canSubmit, noWallet: false } }
                    else if (d && d.error === 'No Zzimba Wallet found') { this.walletInfo.noWallet = true }
                }).catch(() => { });
            },

            saveFormData() { try { localStorage.setItem('rfq_form_data', JSON.stringify({ location: this.selectedLocation, items: this.items, ts: Date.now() })) } catch (e) { } },
            loadFormData() {
                try {
                    const raw = localStorage.getItem('rfq_form_data'); if (!raw) return;
                    const d = JSON.parse(raw);
                    if (Date.now() - (d.ts || 0) > 10 * 60 * 1000) { localStorage.removeItem('rfq_form_data'); return }
                    if (d.location) { this.selectedLocation = d.location }
                    if (Array.isArray(d.items)) { this.items = d.items }
                } catch (e) { }
            },

            initializeMap() {
                this.map = L.map('map').setView([0.3476, 32.5825], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(this.map);
                const b = L.latLngBounds([this.UGANDA_BOUNDS.south, this.UGANDA_BOUNDS.west], [this.UGANDA_BOUNDS.north, this.UGANDA_BOUNDS.east]);
                this.map.setMaxBounds(b);
                this.map.setMinZoom(6);
                this.map.on('click', (e) => {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;
                    if (!this.isWithinUganda(lat, lng)) { alert('Please select a location within Uganda.'); return }
                    this.setMapMarker(lat, lng);
                });
                if (this.selectedLocation) { this.map.setView([this.selectedLocation.lat, this.selectedLocation.lng], 15); this.setMapMarker(this.selectedLocation.lat, this.selectedLocation.lng) }
            },

            isWithinUganda(lat, lng) { const b = this.UGANDA_BOUNDS; return (lat >= b.south && lat <= b.north && lng >= b.west && lng <= b.east) },

            setMapMarker(lat, lng) {
                if (!this.isWithinUganda(lat, lng)) return;
                if (this.marker) this.map.removeLayer(this.marker);
                this.marker = L.marker([lat, lng]).addTo(this.map);
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&countrycodes=ug`)
                    .then(r => r.json()).then(d => {
                        if (d && d.address && d.address.country_code === 'ug') {
                            const addr = d.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            this.selectedLocation = { lat, lng, address: addr };
                        } else {
                            alert('Selected location is not within Uganda.');
                            if (this.marker) this.map.removeLayer(this.marker);
                            this.marker = null; this.selectedLocation = null;
                        }
                    }).catch(() => {
                        this.selectedLocation = { lat, lng, address: `${lat.toFixed(6)}, ${lng.toFixed(6)}` };
                    });
            },

            updateLocationDisplay() { },

            onMapSearchInput(e) {
                const q = (e.target.value || '').trim();
                if (q.length < 3) { const c = document.getElementById('map-search-results'); if (c) c.style.display = 'none'; return }
                if (this.corsIssue) return;
                const uq = `${q}, Uganda`;
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(uq)}&limit=5&countrycodes=ug&bounded=1&viewbox=${this.UGANDA_BOUNDS.west},${this.UGANDA_BOUNDS.north},${this.UGANDA_BOUNDS.east},${this.UGANDA_BOUNDS.south}`)
                    .then(r => { if (!r.ok) throw new Error('err'); return r.json() })
                    .then(data => {
                        const c = document.getElementById('map-search-results'); if (!c) return;
                        c.innerHTML = ''; const res = (data || []).filter(r => this.isWithinUganda(parseFloat(r.lat), parseFloat(r.lon)));
                        if (res.length) {
                            res.forEach(r => {
                                const d = document.createElement('div'); d.className = 'map-search-result'; d.textContent = r.display_name;
                                d.addEventListener('click', () => {
                                    const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                                    if (this.isWithinUganda(lat, lng)) { this.map.setView([lat, lng], 15); this.setMapMarker(lat, lng); c.style.display = 'none'; document.getElementById('map-search-input').value = r.display_name }
                                });
                                c.appendChild(d);
                            });
                            c.style.display = 'block';
                        } else {
                            const d = document.createElement('div'); d.className = 'map-search-result'; d.style.color = '#6b7280'; d.style.fontStyle = 'italic'; d.textContent = 'No locations found in Uganda.'; c.appendChild(d); c.style.display = 'block';
                        }
                    }).catch(() => {
                        this.corsIssue = true;
                        const n = document.getElementById('cors-notice'); if (n) n.style.display = 'flex';
                        const i = document.getElementById('map-search-input'); if (i) { i.disabled = true; i.placeholder = 'Search unavailable - click map' }
                        const c = document.getElementById('map-search-results'); if (c) c.style.display = 'none';
                    });
            },

            getImageUrl(type, id) {
                const k = `${type}_${id}`;
                if (this.imageCache.has(k)) return Promise.resolve(this.imageCache.get(k));
                return fetch(window.location.origin + window.location.pathname + `?ajax=image&type=${type}&id=${id}`)
                    .then(r => r.json()).then(d => {
                        const u = (d && d.image) ? d.image : 'https://placehold.co/60x60?text=No+Image';
                        this.imageCache.set(k, u); return u;
                    }).catch(() => { const f = 'https://placehold.co/60x60?text=No+Image'; this.imageCache.set(k, f); return f; });
            },

            loadSearchData() {
                return fetch(window.location.origin + window.location.pathname + '?ajax=data')
                    .then(r => r.json()).then(d => {
                        if (d && Array.isArray(d.products)) { this.SEARCH_DATA = d; this.buildSearch() }
                        else { this.SEARCH_DATA = { products: [] }; this.searchInitialized = false }
                    }).catch(() => { this.SEARCH_DATA = { products: [] }; this.searchInitialized = false });
            },

            buildSearch() {
                if (!window.Fuse) { setTimeout(() => this.buildSearch(), 100); return }
                try {
                    this.fuseProducts = new Fuse(this.SEARCH_DATA.products.map(p => ({ ...p })), {
                        includeScore: true, threshold: 0.4, ignoreLocation: true,
                        keys: [
                            { name: 'title', weight: 0.4 }, { name: 'meta_title', weight: 0.3 }, { name: 'description', weight: 0.2 },
                            { name: 'meta_description', weight: 0.2 }, { name: 'meta_keywords', weight: 0.2 }, { name: 'category_name', weight: 0.1 }
                        ]
                    });
                    this.searchInitialized = true;
                } catch (e) { this.fuseProducts = null; this.searchInitialized = false }
            },

            onBrandInput(e) {
                if (!this.searchInitialized || !this.fuseProducts) return;
                if (this.isSelectionMade) return;
                const dd = this.$root.querySelector('#brand-search-dropdown');
                this.renderProductDropdown((e.target.value || '').toLowerCase(), dd, e.target);
            },
            onBrandFocus() {
                if (!this.searchInitialized || !this.fuseProducts) return;
                const input = this.$root.querySelector('#item-brand');
                if (input && input.value.trim() && !this.isSelectionMade) {
                    const dd = this.$root.querySelector('#brand-search-dropdown');
                    this.renderProductDropdown(input.value.toLowerCase(), dd, input);
                }
            },
            async renderProductDropdown(q, dd, input) {
                q = (q || '').trim();
                if (!q) { dd.style.display = 'none'; return }
                const res = this.fuseProducts.search(q, { limit: 8 });
                let html = '';
                if (res.length) {
                    html += '<div class="search-dropdown-header">Available Products</div>';
                    for (const r of res) {
                        const p = r.item;
                        html += `
                            <div class="search-dropdown-item" data-product-title="${this.escapeHtml(p.title)}">
                                <img src="https://placehold.co/40x40?text=..." class="w-10 h-10 rounded object-cover search-image loading" data-type="product" data-id="${p.id}">
                                <div>
                                    <div class="font-medium text-sm">${this.escapeHtml(p.title)}</div>
                                    <div class="text-xs" style="color:var(--muted)">${this.escapeHtml(p.category_name)}</div>
                                </div>
                            </div>
                        `;
                    }
                }
                if (html) {
                    dd.innerHTML = html;
                    dd.style.display = 'block';
                    dd.querySelectorAll('.search-image.loading').forEach(async (im) => {
                        const t = im.dataset.type, id = im.dataset.id;
                        try { const u = await this.getImageUrl(t, id); im.src = u; im.classList.remove('loading') } catch (e) { im.src = 'https://placehold.co/40x40?text=No+Image'; im.classList.remove('loading') }
                    });
                    dd.querySelectorAll('.search-dropdown-item').forEach(el => {
                        el.addEventListener('click', () => {
                            const title = el.dataset.productTitle;
                            this.isSelectionMade = true;
                            input.value = title;
                            this.itemForm.brand = title;
                            dd.style.display = 'none';
                            setTimeout(() => { this.isSelectionMade = false }, 100);
                        });
                    });
                } else {
                    dd.style.display = 'none';
                }
            },

            clearErrors() { this.errors = { location: '', items: '' } }
        }));
    });

    function checkSessionStatus() {
        return fetch((window.BASE_URL || '<?= BASE_URL ?>') + 'fetch/check-session.php').then(r => r.json()).then(j => !!j.logged_in).catch(() => false);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>