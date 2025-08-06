<?php
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

$vendorId = $_GET['id'] ?? null;
$storeData = null;
$canEdit = false;

// Check if user can edit this store
if (!empty($_SESSION['user']['logged_in'])) {
    $userId = $_SESSION['user']['user_id'];
    $isAdmin = $_SESSION['user']['is_admin'] ?? false;

    if ($isAdmin) {
        $canEdit = true;
    } elseif ($vendorId) {
        // Check if user is the store owner
        $stmt = $pdo->prepare("SELECT owner_id FROM vendor_stores WHERE id = ?");
        $stmt->execute([$vendorId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($store && $store['owner_id'] === $userId) {
            $canEdit = true;
        } else {
            // Check if user is a store manager
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

ob_start();
?>

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .masonry-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 640px) {
        .masonry-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .masonry-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .price-container {
        position: relative;
        display: inline-block;
    }

    .price-hidden {
        display: none;
    }

    .view-btn {
        color: #2563eb;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: underline;
    }

    .view-btn:hover {
        color: #1d4ed8;
    }

    .view-more-prices {
        color: #2563eb;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
        padding: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px dashed #e5e7eb;
    }

    .view-more-prices:hover {
        color: #1d4ed8;
        background-color: #f9fafb;
    }

    .product-card {
        break-inside: avoid;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .login-note {
        text-align: center;
        font-size: 0.875rem;
        color: #6b7280;
        padding: 0.5rem;
        border-top: 1px dashed #e5e7eb;
    }

    .login-btn {
        display: block;
        text-align: center;
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .login-btn:hover {
        background-color: #e5e7eb;
        color: #374151;
    }

    .line-clamp-2 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-1 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
    }

    .datepicker {
        font-family: inherit;
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        width: 100%;
        overflow: hidden;
    }

    .datepicker-header {
        background-color: #f9fafb;
        padding: 1rem;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .datepicker-title {
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
        margin: 0;
    }

    .datepicker-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .datepicker-prev-btn,
    .datepicker-next-btn {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
    }

    .datepicker-prev-btn:hover,
    .datepicker-next-btn:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    .datepicker-month-year {
        font-weight: 500;
        color: #111827;
    }

    .datepicker-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        padding: 0.5rem;
    }

    .datepicker-day-header {
        text-align: center;
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        padding: 0.5rem 0;
    }

    .datepicker-day {
        text-align: center;
        padding: 0.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .datepicker-day:hover:not(.disabled):not(.selected) {
        background-color: #f3f4f6;
    }

    .datepicker-day.selected {
        background-color: #ef4444;
        color: white;
        font-weight: 500;
    }

    .datepicker-day.today:not(.selected) {
        border: 1px solid #ef4444;
        color: #ef4444;
    }

    .datepicker-day.disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }
</style>

<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden" id="vendor-cover-photo">
    <div id="vendor-cover" class="w-full h-full bg-center bg-cover"></div>
    <?php if ($canEdit): ?>
        <div id="cover-edit-button"
            class="absolute top-4 right-4 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors">
            <i class="fas fa-camera"></i>
        </div>
    <?php endif; ?>
</div>

<div id="loading-state" class="flex flex-col items-center justify-center py-12">
    <div class="border-4 border-gray-200 border-l-red-600 rounded-full w-10 h-10 animate-spin mb-4"></div>
    <p class="text-gray-600">Loading vendor profile...</p>
</div>

<div id="not-found-state"
    class="hidden bg-red-50 border border-red-200 text-red-700 p-8 rounded-lg text-center max-w-2xl mx-auto my-12">
    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
    <h2 class="text-xl font-bold mb-2">Store Not Found or Not Active</h2>
    <p class="mb-4">This store may not exist or has not been activated by an administrator yet.</p>
    <p class="mb-6">If you believe this is an error, please contact the system administrator for assistance.</p>
    <a href="<?= BASE_URL ?>"
        class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Return to Home
    </a>
</div>

<div id="error-state"
    class="hidden bg-red-50 border border-red-200 text-red-700 p-8 rounded-lg text-center max-w-2xl mx-auto my-12">
    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
    <h2 class="text-xl font-bold mb-2">Profile Not Found</h2>
    <p class="mb-4">Sorry, we couldn't find the vendor profile you're looking for.</p>
    <a href="<?= BASE_URL ?>"
        class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Return to Home
    </a>
</div>

<div id="content-state" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 md:-mt-16 relative z-10">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col md:flex-row">
            <div class="flex-shrink-0 flex md:block justify-center">
                <div id="vendor-avatar-container" class="relative">
                    <div id="vendor-avatar"
                        class="h-32 w-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-white flex items-center justify-center">
                        <i class="fas fa-store text-gray-400 text-4xl"></i>
                    </div>
                    <?php if ($canEdit): ?>
                        <div id="avatar-edit-button"
                            class="absolute bottom-0 right-0 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-camera"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 md:mt-0 md:ml-6 flex-grow text-center md:text-left">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h1 id="vendor-name-container" class="text-3xl font-bold text-secondary">
                            <span id="vendor-name">Store Name</span>
                            <?php if ($canEdit): ?>
                                <i id="edit-name-button"
                                    class="fas fa-pen ml-2 text-gray-500 hover:text-red-600 text-sm cursor-pointer transition-colors"></i>
                            <?php endif; ?>
                        </h1>
                        <p id="vendor-description-container" class="text-gray-600 mt-1">
                            <span id="vendor-description">Premium Construction Materials & Services</span>
                            <?php if ($canEdit): ?>
                                <i id="edit-description-button"
                                    class="fas fa-pen ml-2 text-gray-500 hover:text-red-600 text-sm cursor-pointer transition-colors"></i>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-y-4 justify-center md:justify-start">
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-calendar-days text-gray-500 mr-2"></i>
                        <span id="vendor-registered" class="text-gray-700">Joined March 2008</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <div id="location-section">
                            <button id="view-location-btn"
                                class="flex items-center text-red-600 text-sm font-medium hover:underline transition-all view-btn"
                                onclick="showLocation()">
                                <i class="fa-solid fa-location-dot text-gray-500 mr-2"></i>
                                <span>View Location</span>
                            </button>
                            <div id="location-container" class="hidden">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-location-dot text-gray-500 mr-2"></i>
                                    <span id="vendor-location" class="text-gray-700">Building City, BC 12345</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mr-8 flex items-center">
                        <div id="phone-section">
                            <button id="view-phone-btn"
                                class="flex items-center text-red-600 text-sm font-medium hover:underline transition-all view-btn"
                                onclick="showPhone()">
                                <i class="fa-solid fa-phone text-gray-500 mr-2"></i>
                                <span>View Contact</span>
                            </button>
                            <div id="phone-container" class="hidden">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-phone text-gray-500 mr-2"></i>
                                    <span id="phone-display" class="text-gray-700">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mr-8 flex items-center">
                        <div id="email-section">
                            <button id="view-email-btn"
                                class="flex items-center text-red-600 text-sm font-medium hover:underline transition-all view-btn"
                                onclick="showEmail()">
                                <i class="fa-solid fa-envelope text-gray-500 mr-2"></i>
                                <span>View Email</span>
                            </button>
                            <div id="email-container" class="hidden">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-envelope text-gray-500 mr-2"></i>
                                    <span id="email-display" class="text-gray-700">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-box text-gray-500 mr-2"></i>
                        <span id="product-count" class="text-gray-700">0 Products</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-tags text-gray-500 mr-2"></i>
                        <span id="category-count" class="text-gray-700">0 Categories</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-x-8 gap-y-4 justify-center md:justify-start">
            <div class="flex items-center">
                <div id="vendor-status" class="bg-yellow-300 text-yellow-800 px-3 py-1 rounded-full text-sm">Status
                </div>
                <div id="vendor-operation-type" class="ml-2 bg-red-600 text-white px-3 py-1 rounded-full text-sm">
                    Operation Type</div>
            </div>
            <div class="flex items-center">
                <div class="text-xl font-bold text-secondary">4.8</div>
                <div class="ml-2 flex">
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                    <span class="ml-1 text-sm text-gray-600">(128 reviews)</span>
                </div>
            </div>
            <div class="ml-0 sm:ml-auto flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">SHARE</span>
                <div class="flex gap-2">
                    <button onclick="copyLink()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-solid fa-link text-xs"></i>
                    </button>
                    <button onclick="shareOnWhatsApp()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-whatsapp text-xs"></i>
                    </button>
                    <button onclick="shareOnFacebook()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-facebook-f text-xs"></i>
                    </button>
                    <button onclick="shareOnTwitter()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-x-twitter text-xs"></i>
                    </button>
                    <button onclick="shareOnLinkedIn()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-linkedin-in text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <main class="py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                <i class="fa-solid fa-box-open mr-2 text-red-600"></i>
                Products
            </h2>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div class="flex flex-col sm:flex-row gap-4 w-full">
                    <select id="filter-category"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                        <option value="">All Categories</option>
                    </select>
                    <select id="sort-products"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                        <option value="default">Default Sorting</option>
                        <option value="latest">Latest</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                    <input type="text" id="search-products" placeholder="Search products..."
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                </div>
            </div>

            <div id="products-container" class="masonry-grid">
                <div class="col-span-full text-center py-8 text-gray-500">
                    No products found for this vendor.
                </div>
            </div>
            <button id="loadMoreBtn"
                class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
                Load More Products
            </button>
        </div>
    </main>
</div>

<div id="buyInStoreModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="flex h-full">
            <div class="w-full md:w-1/2 border-r border-gray-100 overflow-y-auto max-h-[90vh]">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 md:hidden" id="buyInStoreTitle">Buy In Store</h3>
                        <h3 class="text-xl font-bold text-gray-800 hidden md:block">Complete Your Request</h3>
                        <button onclick="closeBuyInStoreModal()"
                            class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="buyInStoreLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-red-600">
                    </div>
                    <p class="mt-2 text-gray-600">Loading your information...</p>
                </div>

                <form id="buyInStoreForm" class="p-6 space-y-6 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visit Date <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <div id="datepicker-container" class="w-full"></div>
                            <input type="hidden" id="visitDate" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Please select a date when you plan to visit our store</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Package <span
                                class="text-red-500">*</span></label>
                        <select id="packageSelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            required>
                            <option value="">Select a package</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <button type="button" id="decreaseQuantity"
                                class="px-3 py-2 border border-gray-300 rounded-l-md bg-gray-100 hover:bg-gray-200">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                            </button>
                            <input type="number" id="quantityInput"
                                class="w-full px-3 py-2 border-t border-b border-gray-300 text-center focus:ring-0 focus:border-gray-300"
                                value="1" min="1">
                            <button type="button" id="increaseQuantity"
                                class="px-3 py-2 border border-gray-300 rounded-r-md bg-gray-100 hover:bg-gray-200">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="capacityNote">Minimum quantity: 1</p>
                    </div>

                    <div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="showAltContact"
                                class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="showAltContact" class="ml-2 block text-sm text-gray-700">Add alternative contact
                                details (optional)</label>
                        </div>

                        <div id="altContactFields" class="space-y-4 hidden">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label>
                                <input type="text" id="altPhone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Alternative phone number">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Email</label>
                                <input type="email" id="altEmail"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Alternative email address">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Any special requests or notes for your visit"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between">
                            <button type="button" onclick="closeBuyInStoreModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" id="submitBuyInStore"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="hidden md:block md:w-1/2">
                <div id="productDetailPanel" class="h-full flex flex-col">
                    <div class="p-6 flex-1 overflow-y-auto bg-gray-50 max-h-[90vh] overflow-hidden">
                        <div class="mb-6">
                            <h3 id="productName" class="text-xl font-bold text-gray-800 mb-2"></h3>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
                                <img id="productImage" src="" alt="Product Image" class="w-full h-48 object-cover">
                            </div>
                            <p id="productDescription" class="text-gray-600 text-sm line-clamp-2 mb-2"></p>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mb-6">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Order Summary
                            </h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Selected Package:</span>
                                    <span id="summaryPackage" class="font-medium line-clamp-1">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Quantity:</span>
                                    <span id="summaryQuantity" class="font-medium">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Visit Date:</span>
                                    <span id="summaryDate" class="font-medium">-</span>
                                </div>
                                <div id="summaryAltContactContainer"
                                    class="hidden space-y-2 pt-2 border-t border-gray-200">
                                    <h5 class="text-sm font-medium text-gray-500">Alternative Contact</h5>
                                    <div id="summaryAltPhone" class="text-sm hidden">
                                        <span class="text-gray-600">Phone:</span>
                                        <span class="ml-2 font-medium">-</span>
                                    </div>
                                    <div id="summaryAltEmail" class="text-sm hidden">
                                        <span class="text-gray-600">Email:</span>
                                        <span class="ml-2 font-medium">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Your Information
                            </h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Name</p>
                                        <p id="summaryName" class="font-medium">-</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Email</p>
                                        <p id="summaryEmail" class="font-medium">-</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Phone</p>
                                        <p id="summaryPhone" class="font-medium">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="buyInStoreError" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="errorMessage">
                        An error occurred. Please try again.
                    </p>
                </div>
            </div>
        </div>
        <button onclick="closeBuyInStoreModal()"
            class="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none">
            Close
        </button>
    </div>
</div>

<div id="buyInStoreSuccess" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Request Submitted!</h3>
        <p class="text-gray-600 mb-6">Your in-store purchase request has been submitted successfully. We'll be
            expecting you on your selected date.</p>
        <button onclick="closeBuyInStoreModal()"
            class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">
            Done
        </button>
    </div>
</div>

<?php if ($canEdit): ?>
    <div id="edit-name-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
            <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Store Name</h2>
                <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                    onclick="closeModal('edit-name-modal')">&times;</span>
            </div>
            <div class="mb-4">
                <div class="mb-4">
                    <label for="store-name-input" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                    <input type="text" id="store-name-input"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                    onclick="closeModal('edit-name-modal')">Cancel</button>
                <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    onclick="saveName()">Save</button>
            </div>
        </div>
    </div>

    <div id="edit-description-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
            <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Store Description</h2>
                <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                    onclick="closeModal('edit-description-modal')">&times;</span>
            </div>
            <div class="mb-4">
                <div class="mb-4">
                    <label for="store-description-input" class="block text-sm font-medium text-gray-700 mb-1">Store
                        Description</label>
                    <textarea id="store-description-input" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                    onclick="closeModal('edit-description-modal')">Cancel</button>
                <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    onclick="saveDescription()">Save</button>
            </div>
        </div>
    </div>

    <div id="edit-logo-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
            <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Store Logo</h2>
                <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                    onclick="closeModal('edit-logo-modal')">&times;</span>
            </div>
            <div class="mb-4">
                <div class="mb-4">
                    <label for="logo-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Logo</label>
                    <input type="file" id="logo-file-input" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="text-sm text-gray-500 mt-1">Select a square image for best results. The image will be cropped
                        to a 1:1 ratio.</p>
                </div>
                <div id="cropper-container" class="hidden h-[300px] mb-4">
                    <img id="cropper-image" src="https://placehold.co/600x400?text=Image+to+Crop" alt="Image to crop">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                    onclick="closeModal('edit-logo-modal')">Cancel</button>
                <button type="button" id="crop-button"
                    class="hidden px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    onclick="cropAndSaveLogo()">Crop & Save</button>
            </div>
        </div>
    </div>

    <div id="edit-cover-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
            <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Cover Photo</h2>
                <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                    onclick="closeModal('edit-cover-modal')">&times;</span>
            </div>
            <div class="mb-4">
                <div class="mb-4">
                    <label for="cover-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Cover
                        Photo</label>
                    <input type="file" id="cover-file-input" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <p class="text-sm text-gray-500 mt-1">Select an image that will be cropped to a 3:1 ratio for best
                        results.</p>
                </div>
                <div id="cover-cropper-container" class="hidden h-[300px] mb-4">
                    <img id="cover-cropper-image" src="https://placehold.co/1200x400?text=Cover+Image+to+Crop"
                        alt="Image to crop">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                    onclick="closeModal('edit-cover-modal')">Cancel</button>
                <button type="button" id="crop-cover-button"
                    class="hidden px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    onclick="cropAndSaveCover()">Crop & Save</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

<script>
    <?php if ($canEdit): ?>
        window.openModal = function (modalId) {
            document.getElementById(modalId).style.display = 'block';
        };
    <?php endif; ?>

    let vendorId = '<?= $vendorId ?>';
    let storeData = null;
    let isOwner = false;
    let storeEmail = '';
    let storePhone = '';
    let currentPage = 1;
    let totalPages = 1;
    let selectedCategories = [];
    let allProducts = [];
    let availableUnits = [];
    let lineItemCount = 0;
    let pendingDeleteId = null;
    let pendingDeleteType = null;
    let categoryStatusChanges = {};
    let cropper = null;
    let coverCropper = null;
    let isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    let canEdit = <?= $canEdit ? 'true' : 'false' ?>;
    let currentProduct = null;

    async function getProductImageUrl(product) {
        const placeholderText = encodeURIComponent((product.name || '').substring(0, 2));
        const placeholder = `https://placehold.co/400x300/f0f0f0/808080?text=${placeholderText}`;

        try {
            const res = await fetch(`${BASE_URL}img/products/${product.id}/images.json`);
            if (!res.ok) return placeholder;
            const json = await res.json();
            if (Array.isArray(json.images) && json.images.length > 0) {
                return `${BASE_URL}img/products/${product.id}/${json.images[0]}`;
            }
        } catch (e) {
        }
        return placeholder;
    }

    async function showLocation() {
        const sessionActive = await checkUserSession();

        if (!sessionActive) {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            }
            return;
        }

        const locationContainer = document.getElementById('location-container');
        const viewLocationBtn = document.getElementById('view-location-btn');

        locationContainer.classList.remove('hidden');
        viewLocationBtn.style.display = 'none';
    }

    async function showPhone() {
        const sessionActive = await checkUserSession();

        if (!sessionActive) {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            }
            return;
        }

        const phoneContainer = document.getElementById('phone-container');
        const viewPhoneBtn = document.getElementById('view-phone-btn');
        const phoneDisplay = document.getElementById('phone-display');

        // Show loading state
        phoneDisplay.textContent = 'Loading...';
        phoneContainer.classList.remove('hidden');
        viewPhoneBtn.style.display = 'none';

        // Fetch actual phone data
        try {
            const response = await fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=getStoreContact&id=${vendorId}&type=phone`);
            const data = await response.json();

            if (data.success && data.phone) {
                phoneDisplay.textContent = data.phone;
            } else {
                phoneDisplay.textContent = storePhone || 'Not provided';
            }
        } catch (error) {
            console.error('Error fetching phone:', error);
            phoneDisplay.textContent = storePhone || 'Not provided';
        }
    }

    async function showEmail() {
        const sessionActive = await checkUserSession();

        if (!sessionActive) {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            }
            return;
        }

        const emailContainer = document.getElementById('email-container');
        const viewEmailBtn = document.getElementById('view-email-btn');
        const emailDisplay = document.getElementById('email-display');

        // Show loading state
        emailDisplay.textContent = 'Loading...';
        emailContainer.classList.remove('hidden');
        viewEmailBtn.style.display = 'none';

        // Fetch actual email data
        try {
            const response = await fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=getStoreContact&id=${vendorId}&type=email`);
            const data = await response.json();

            if (data.success && data.email) {
                emailDisplay.textContent = data.email;
            } else {
                emailDisplay.textContent = storeEmail || 'Not provided';
            }
        } catch (error) {
            console.error('Error fetching email:', error);
            emailDisplay.textContent = storeEmail || 'Not provided';
        }
    }

    function ymd(year, month, day) {
        const mm = String(month + 1).padStart(2, '0');
        const dd = String(day).padStart(2, '0');
        return `${year}-${mm}-${dd}`;
    }

    function initDatepicker() {
        const container = document.getElementById('datepicker-container');
        const hiddenInput = document.getElementById('visitDate');

        const eatNow = new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Kampala' }));
        eatNow.setHours(0, 0, 0, 0);
        const today = eatNow;
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();

        const todayString = ymd(today.getFullYear(), today.getMonth(), today.getDate());
        hiddenInput.value = todayString;

        container.innerHTML = `
            <div class="datepicker">
                <div class="datepicker-controls">
                    <button type="button" class="datepicker-prev-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="datepicker-month-year"></span>
                    <button type="button" class="datepicker-next-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
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
            </div>
        `;

        const datepicker = container.querySelector('.datepicker');
        const monthYearDisplay = container.querySelector('.datepicker-month-year');
        const daysGrid = container.querySelector('.datepicker-grid');
        const prevBtn = container.querySelector('.datepicker-prev-btn');
        const nextBtn = container.querySelector('.datepicker-next-btn');

        prevBtn.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });

        nextBtn.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });

        function renderCalendar() {
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            monthYearDisplay.textContent = `${monthNames[currentMonth]} ${currentYear}`;

            const dayHeaders = Array.from(daysGrid.querySelectorAll('.datepicker-day-header'));
            daysGrid.innerHTML = '';

            dayHeaders.forEach(header => daysGrid.appendChild(header));

            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'datepicker-day';
                daysGrid.appendChild(emptyDay);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'datepicker-day';
                dayElement.textContent = day;

                const date = new Date(currentYear, currentMonth, day);
                date.setHours(0, 0, 0, 0);
                const dateString = ymd(currentYear, currentMonth, day);

                const isToday = date.toDateString() === today.toDateString();
                if (isToday) {
                    dayElement.classList.add('today');
                }

                if (date < today && !isToday) {
                    dayElement.classList.add('disabled');
                } else {
                    dayElement.addEventListener('click', () => {
                        document.querySelectorAll('.datepicker-day.selected').forEach(el => {
                            el.classList.remove('selected');
                        });

                        dayElement.classList.add('selected');
                        hiddenInput.value = dateString;
                        updateSummary();
                    });
                }

                if (hiddenInput.value === dateString) {
                    dayElement.classList.add('selected');
                }

                daysGrid.appendChild(dayElement);
            }
        }

        renderCalendar();
        updateSummary();
    }

    function loadProductsForDisplay(id, page = 1) {
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreProducts&id=${id}&page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderProductsForDisplay(data.products, page === 1);
                    if (page === 1) {
                        allProducts = data.products;
                    } else {
                        allProducts = [...allProducts, ...data.products];
                    }
                    currentPage = data.pagination?.page || 1;
                    totalPages = data.pagination?.pages || 1;
                    const loadMoreBtn = document.getElementById('loadMoreBtn');
                    if (currentPage < totalPages) {
                        loadMoreBtn.classList.remove('hidden');
                    } else {
                        loadMoreBtn.classList.add('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
    }

    function populateCategoryFilter(categories) {
        if (!categories || categories.length === 0) return;
        const filterSelect = document.getElementById('filter-category');
        filterSelect.innerHTML = '<option value="">All Categories</option>';
        const activeCats = categories.filter(cat => cat.status === 'active');
        activeCats.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            filterSelect.appendChild(option);
        });
        filterSelect.addEventListener('change', filterProductsByCategory);
    }

    function filterProductsByCategory() {
        const categoryId = document.getElementById('filter-category').value;
        const searchTerm = document.getElementById('search-products').value.toLowerCase();
        filterProducts(categoryId, searchTerm);
    }

    function filterProducts(categoryId = '', searchTerm = '') {
        const container = document.getElementById('products-container');
        container.innerHTML = '';
        let filtered = [...allProducts];
        if (categoryId) {
            filtered = filtered.filter(p => p.store_category_id === categoryId);
        }
        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
        }
        if (filtered.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found matching your criteria.</div>`;
            return;
        }
        renderProductsForDisplay(filtered, true);
    }

    async function renderProductsForDisplay(products, clearExisting = true) {
        const container = document.getElementById('products-container');
        if (clearExisting) {
            container.innerHTML = '';
        }
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found for this vendor.</div>`;
            return;
        }
        for (const product of products) {
            const imageUrl = await getProductImageUrl(product);
            let lowestPrice = 0;
            if (product.pricing && product.pricing.length > 0) {
                lowestPrice = Math.min(...product.pricing.map(p => parseFloat(p.price)));
            }
            let filteredPricing = product.pricing || [];
            let hasRetailPrice = false;
            <?php if (!$isLoggedIn): ?>
                filteredPricing = filteredPricing.filter(p => p.price_category === 'retail');
                if (filteredPricing.length > 0) {
                    hasRetailPrice = true;
                }
            <?php endif; ?>
            let pricingLines = '';
            let hasHiddenPrices = false;
            if (filteredPricing.length > 0) {
                filteredPricing.forEach((pr, index) => {
                    const unitParts = pr.unit_name.split(' ');
                    const siUnit = unitParts[0] || '';
                    const packageName = unitParts.slice(1).join(' ') || '';
                    const formattedUnit = `${pr.package_size} ${siUnit} ${packageName}`.trim();
                    let categoryDisplay = '';
                    <?php if ($isLoggedIn): ?>
                        categoryDisplay = pr.price_category.charAt(0).toUpperCase() + pr.price_category.slice(1);
                    <?php endif; ?>
                    let deliveryCapacity = '';
                    <?php if ($isLoggedIn): ?>
                        if (pr.delivery_capacity) {
                            deliveryCapacity = `<span class="ml-2">• ${pr.price_category === 'retail' ? 'Max' : 'Min'} Capacity: ${pr.delivery_capacity}</span>`;
                        }
                    <?php endif; ?>
                    const hiddenClass = index >= 2 ? 'hidden hidden-price-row' : '';
                    if (index >= 2) hasHiddenPrices = true;
                    pricingLines += `
<div class="flex justify-between items-center p-2 bg-gray-50 rounded ${hiddenClass}">
    <div class="flex flex-col">
        <span class="font-medium text-gray-700">${escapeHtml(formattedUnit)}</span>
        ${(categoryDisplay || deliveryCapacity) ? `
            <div class="flex items-center text-xs text-gray-500">
                ${categoryDisplay ? `<span>${categoryDisplay}</span>` : ''}
                ${deliveryCapacity}
            </div>` : ''}
    </div>
    <div class="flex items-center">
        <span class="view-btn view-price-btn mr-2">View Price</span>
        <span class="price-hidden text-red-600 font-bold">UGX ${formatNumber(pr.price)}</span>
    </div>
</div>`;
                });
                if (hasHiddenPrices) {
                    pricingLines = `
<div class="pricing-rows">
    ${pricingLines}
</div>
<div class="view-more-prices view-btn">View More Prices</div>`;
                }
                <?php if (!$isLoggedIn): ?>
                    if (hasRetailPrice) {
                        pricingLines += `<div class="login-note text-center text-gray-500 text-sm">Login to view more price categories</div>`;
                    }
                <?php endif; ?>
            } else {
                <?php if (!$isLoggedIn): ?>
                    pricingLines = `<button class="login-btn view-btn w-full text-center">View Price Categories</button>`;
                <?php else: ?>
                    pricingLines = `<div class="text-sm text-gray-600 italic p-2">No price data</div>`;
                <?php endif; ?>
            }
            const productCard = document.createElement('div');
            productCard.className = 'product-card transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden';
            productCard.innerHTML = `
            <div class="relative group">
                <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-40 md:h-48 object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                    <a href="${BASE_URL}view/product/${product.id}" target="_blank" class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm">View Details</a>
                </div>
            </div>
            <div class="p-3 md:p-5 flex flex-col flex-1">
                <div class="">
                    <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">${escapeHtml(product.name)}</h3>
                    <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">${escapeHtml(product.description || '')}</p>
                </div>
                <div class="flex-1"></div>
                <div class="border-t border-gray-200 pt-3 mb-3 flex flex-col justify-between min-h-[20px]">${pricingLines}</div>
                <div class="flex space-x-2">
                    <?php if ($isLoggedIn): ?>
                    <button onclick="buyInStore('${product.store_product_id}')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm"><i class="fas fa-shopping-cart mr-1"></i>Buy in Store</button>
                    <?php else: ?>
                    <button onclick="openAuthModal(); return false;" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm"><i class="fas fa-shopping-cart mr-1"></i>Buy in Store</button>
                    <?php endif; ?>
                    <button onclick="openVendorSellModal('${product.id}', '${escapeHtml(product.name)}')" class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm"><i class="fas fa-tag mr-1"></i>Sell</button>
                </div>
            </div>`;
            productCard.dataset.lowestPrice = lowestPrice;
            container.appendChild(productCard);
        }
    }

    async function buyInStore(productId) {
        const sessionActive = await checkUserSession();

        if (!sessionActive) {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            }
            return;
        }

        const product = allProducts.find(p => p.store_product_id === productId);
        if (!product) {
            showToast("Product details not found.", "error");
            return;
        }

        currentProduct = product;

        document.getElementById('buyInStoreForm').reset();
        document.getElementById('buyInStoreTitle').textContent = `Complete Your Request`;

        document.getElementById('buyInStoreLoading').classList.remove('hidden');
        document.getElementById('buyInStoreForm').classList.add('hidden');
        document.getElementById('buyInStoreError').classList.add('hidden');
        document.getElementById('buyInStoreSuccess').classList.add('hidden');

        document.getElementById('buyInStoreModal').classList.remove('hidden');

        fetchUserInfo();
        populatePackageOptions(product);
        initDatepicker();
    }

    function closeBuyInStoreModal() {
        document.getElementById('buyInStoreModal').classList.add('hidden');
        document.getElementById('buyInStoreError').classList.add('hidden');
        document.getElementById('buyInStoreSuccess').classList.add('hidden');
        currentProduct = null;
    }

    function fetchUserInfo() {
        fetch(`${BASE_URL}fetch/manageBuyInStore.php?action=getUserInfo`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const userName = data.user.name || data.user.username || '';
                    const userEmail = data.user.email || '';
                    const userPhone = data.user.phone || '';

                    document.getElementById('summaryName').textContent = userName;
                    document.getElementById('summaryEmail').textContent = userEmail;
                    document.getElementById('summaryPhone').textContent = userPhone;

                    populateProductDetails();

                    document.getElementById('buyInStoreLoading').classList.add('hidden');
                    document.getElementById('buyInStoreForm').classList.remove('hidden');
                } else {
                    showBuyInStoreError(data.message || 'Failed to load user information');
                }
            })
            .catch(error => {
                console.error('Error fetching user info:', error);
                showBuyInStoreError('Network error. Please try again.');
            });
    }

    async function populateProductDetails() {
        if (!currentProduct) return;

        const imageUrl = await getProductImageUrl(currentProduct);

        document.getElementById('productImage').src = imageUrl;
        document.getElementById('productImage').alt = currentProduct.name;
        document.getElementById('productName').textContent = currentProduct.name;
        document.getElementById('productDescription').textContent = currentProduct.description || 'No description available.';
    }

    function populatePackageOptions(product) {
        const packageSelect = document.getElementById('packageSelect');
        packageSelect.innerHTML = '<option value="">Select a package</option>';

        if (!product.pricing || product.pricing.length === 0) {
            showBuyInStoreError('No package options available for this product');
            return;
        }

        product.pricing.forEach(pricing => {
            const unitParts = pricing.unit_name.split(' ');
            const siUnit = unitParts[0] || '';
            const packageName = unitParts.slice(1).join(' ') || '';
            const formattedUnit = `${pricing.package_size} ${siUnit} ${packageName}`.trim();
            const categoryDisplay = pricing.price_category.charAt(0).toUpperCase() + pricing.price_category.slice(1);

            const option = document.createElement('option');
            option.value = pricing.pricing_id || pricing.id || '';
            option.textContent = `${formattedUnit} (${categoryDisplay}) - UGX ${formatNumber(pricing.price)}`;
            option.dataset.category = pricing.price_category;
            option.dataset.capacity = pricing.delivery_capacity || '1';
            option.dataset.price = pricing.price;
            option.dataset.formattedUnit = formattedUnit;
            option.dataset.categoryDisplay = categoryDisplay;
            packageSelect.appendChild(option);
        });

        updateCapacityLimits();
    }

    function updateCapacityLimits() {
        const packageSelect = document.getElementById('packageSelect');
        const quantityInput = document.getElementById('quantityInput');
        const capacityNote = document.getElementById('capacityNote');

        if (packageSelect.value === '') {
            quantityInput.min = '1';
            quantityInput.value = '1';
            capacityNote.textContent = 'Minimum quantity: 1';
            return;
        }

        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        const category = selectedOption.dataset.category;
        const capacity = parseInt(selectedOption.dataset.capacity) || 1;

        if (category === 'retail') {
            quantityInput.min = '1';
            quantityInput.max = capacity > 0 ? capacity.toString() : '';
            quantityInput.value = '1';
            capacityNote.textContent = `Maximum quantity: ${capacity}`;
        } else {
            quantityInput.min = capacity > 0 ? capacity.toString() : '1';
            quantityInput.value = capacity > 0 ? capacity.toString() : '1';
            capacityNote.textContent = `Minimum quantity: ${capacity}`;
        }

        updateSummary();
    }

    function updateSummary() {
        const packageSelect = document.getElementById('packageSelect');
        const summaryPackage = document.getElementById('summaryPackage');

        if (packageSelect.value) {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            const formattedUnit = selectedOption.dataset.formattedUnit;
            const categoryDisplay = selectedOption.dataset.categoryDisplay;
            const price = selectedOption.dataset.price;

            summaryPackage.textContent = `${formattedUnit} (${categoryDisplay}) - UGX ${formatNumber(price)}`;
        } else {
            summaryPackage.textContent = '-';
        }

        const quantityInput = document.getElementById('quantityInput');
        const summaryQuantity = document.getElementById('summaryQuantity');
        summaryQuantity.textContent = quantityInput.value || '-';

        const visitDate = document.getElementById('visitDate').value;
        const summaryDate = document.getElementById('summaryDate');

        if (visitDate) {
            const date = new Date(new Date(visitDate).toLocaleString('en-US', { timeZone: 'Africa/Kampala' }));
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            summaryDate.textContent = date.toLocaleDateString('en-US', options);
        } else {
            summaryDate.textContent = '-';
        }

        const altPhone = document.getElementById('altPhone').value;
        const altEmail = document.getElementById('altEmail').value;
        const summaryAltPhone = document.getElementById('summaryAltPhone');
        const summaryAltEmail = document.getElementById('summaryAltEmail');

        if (altPhone) {
            summaryAltPhone.classList.remove('hidden');
            summaryAltPhone.querySelector('span:last-child').textContent = altPhone;
        } else {
            summaryAltPhone.classList.add('hidden');
        }

        if (altEmail) {
            summaryAltEmail.classList.remove('hidden');
            summaryAltEmail.querySelector('span:last-child').textContent = altEmail;
        } else {
            summaryAltEmail.classList.add('hidden');
        }
    }

    function showBuyInStoreError(message) {
        document.getElementById('buyInStoreModal').classList.add('hidden');
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('buyInStoreError').classList.remove('hidden');
    }

    function submitBuyInStoreRequest() {
        if (!currentProduct) {
            showBuyInStoreError('Product information not found');
            return;
        }

        const visitDate = document.getElementById('visitDate').value;
        const packageId = document.getElementById('packageSelect').value;
        const quantity = document.getElementById('quantityInput').value;
        const altPhone = document.getElementById('altPhone').value;
        const altEmail = document.getElementById('altEmail').value;
        const notes = document.getElementById('notes').value;

        if (!visitDate) {
            showToast('Please select a visit date', 'error');
            return;
        }

        if (!packageId) {
            showToast('Please select a package', 'error');
            return;
        }

        if (!quantity || parseInt(quantity) < 1) {
            showToast('Please enter a valid quantity', 'error');
            return;
        }

        const submitBtn = document.getElementById('submitBuyInStore');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...';

        fetch(`${BASE_URL}fetch/manageBuyInStore.php?action=submitBuyInStore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                productId: currentProduct.store_product_id,
                visitDate: visitDate,
                packageId: packageId,
                quantity: quantity,
                altContact: altPhone,
                altEmail: altEmail,
                notes: notes
            })
        })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                if (data.success) {
                    document.getElementById('buyInStoreModal').classList.add('hidden');
                    document.getElementById('buyInStoreSuccess').classList.remove('hidden');
                } else {
                    showToast(data.message || 'Failed to submit request', 'error');
                }
            })
            .catch(error => {
                console.error('Error submitting request:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showToast('Network error. Please try again.', 'error');
            });
    }

    <?php if ($canEdit): ?>
        function editCover() {
            openModal('edit-cover-modal');

            if (storeData && storeData.vendor_cover_url) {
                fetch(BASE_URL + storeData.vendor_cover_url)
                    .then(res => res.blob())
                    .then(blob => {
                        const file = new File([blob], "cover.png", { type: blob.type });
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            initCoverCropper(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    })
                    .catch(err => {
                        console.error('Error loading existing cover for crop:', err);
                        showToast('Could not load existing cover', 'error');
                    });
            }
        }

        function initCoverCropper(image) {
            if (coverCropper) {
                coverCropper.destroy();
            }

            const cropperContainer = document.getElementById('cover-cropper-container');
            cropperContainer.classList.remove('hidden');

            const cropperImage = document.getElementById('cover-cropper-image');
            cropperImage.src = image;

            coverCropper = new Cropper(cropperImage, {
                aspectRatio: 3 / 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });

            document.getElementById('crop-cover-button').classList.remove('hidden');
        }

        function cropAndSaveCover() {
            if (!coverCropper) {
                showToast('Please select an image first', 'error');
                return;
            }

            const canvas = coverCropper.getCroppedCanvas({
                width: 1200,
                height: 400,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!canvas) {
                showToast('Failed to crop image', 'error');
                return;
            }

            canvas.toBlob(function (blob) {
                const formData = new FormData();
                formData.append('cover', blob, 'cover.png');

                fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadVendorCover`, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const updateData = {
                                id: vendorId,
                                temp_cover_path: data.temp_path
                            };

                            return fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(updateData)
                            });
                        } else {
                            throw new Error(data.message || 'Failed to upload cover photo');
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const coverUrl = canvas.toDataURL();
                            const coverContainer = document.getElementById('vendor-cover');
                            coverContainer.style.backgroundImage = `url(${coverUrl})`;

                            closeModal('edit-cover-modal');
                            showToast('Cover photo updated successfully');

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showToast(data.error || 'Failed to update cover photo', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cover photo:', error);
                        showToast('Failed to update cover photo', 'error');
                    });
            }, 'image/png');
        }

        function editName() {
            const nameInput = document.getElementById('store-name-input');
            nameInput.value = document.getElementById('vendor-name').textContent;
            openModal('edit-name-modal');
        }

        function saveName() {
            const newName = document.getElementById('store-name-input').value.trim();
            if (!newName) {
                showToast('Store name cannot be empty', 'error');
                return;
            }

            const data = {
                id: vendorId,
                name: newName
            };

            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('vendor-name').textContent = newName;
                        closeModal('edit-name-modal');
                        showToast('Store name updated successfully');
                    } else {
                        showToast(data.error || 'Failed to update store name', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating store name:', error);
                    showToast('Failed to update store name', 'error');
                });
        }

        function editDescription() {
            const descriptionInput = document.getElementById('store-description-input');
            descriptionInput.value = document.getElementById('vendor-description').textContent;
            openModal('edit-description-modal');
        }

        function saveDescription() {
            const newDescription = document.getElementById('store-description-input').value.trim();

            const data = {
                id: vendorId,
                description: newDescription
            };

            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('vendor-description').textContent = newDescription || 'No description provided.';
                        closeModal('edit-description-modal');
                        showToast('Store description updated successfully');
                    } else {
                        showToast(data.error || 'Failed to update store description', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating store description:', error);
                    showToast('Failed to update store description', 'error');
                });
        }

        function editLogo() {
            openModal('edit-logo-modal');

            if (storeData && storeData.logo_url) {
                fetch(BASE_URL + storeData.logo_url)
                    .then(res => res.blob())
                    .then(blob => {
                        const file = new File([blob], "logo.png", { type: blob.type });
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            initCropper(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    })
                    .catch(err => {
                        console.error('Error loading existing logo for crop:', err);
                        showToast('Could not load existing logo', 'error');
                    });
            }
        }

        function initCropper(image) {
            if (cropper) {
                cropper.destroy();
            }

            const cropperContainer = document.getElementById('cropper-container');
            cropperContainer.classList.remove('hidden');

            const cropperImage = document.getElementById('cropper-image');
            cropperImage.src = image;

            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false
            });

            document.getElementById('crop-button').classList.remove('hidden');
        }

        function cropAndSaveLogo() {
            if (!cropper) {
                showToast('Please select an image first', 'error');
                return;
            }

            const canvas = cropper.getCroppedCanvas({
                width: 512,
                height: 512,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!canvas) {
                showToast('Failed to crop image', 'error');
                return;
            }

            canvas.toBlob(function (blob) {
                const formData = new FormData();
                formData.append('logo', blob, 'logo.png');

                fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadLogo`, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const updateData = {
                                id: vendorId,
                                temp_logo_path: data.temp_path
                            };

                            return fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(updateData)
                            });
                        } else {
                            throw new Error(data.message || 'Failed to upload logo');
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const logoUrl = canvas.toDataURL();
                            const avatarContainer = document.getElementById('vendor-avatar');
                            avatarContainer.innerHTML = '';
                            const logoImg = document.createElement('img');
                            logoImg.src = logoUrl;
                            logoImg.alt = document.getElementById('vendor-name').textContent;
                            logoImg.className = 'w-full h-full object-cover rounded-full';
                            avatarContainer.appendChild(logoImg);

                            closeModal('edit-logo-modal');
                            showToast('Store logo updated successfully');

                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            showToast(data.error || 'Failed to update store logo', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating store logo:', error);
                        showToast('Failed to update store logo', 'error');
                    });
            }, 'image/png');
        }

        window.closeModal = function (modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';

            if (modalId === 'edit-logo-modal') {
                if (window.cropper) {
                    window.cropper.destroy();
                    window.cropper = null;
                }
                document.getElementById('cropper-container').classList.add('hidden');
                document.getElementById('crop-button').classList.add('hidden');
                document.getElementById('logo-file-input').value = '';
            }

            if (modalId === 'edit-cover-modal') {
                if (window.coverCropper) {
                    window.coverCropper.destroy();
                    window.coverCropper = null;
                }
                document.getElementById('cover-cropper-container').classList.add('hidden');
                document.getElementById('crop-cover-button').classList.add('hidden');
                document.getElementById('cover-file-input').value = '';
            }
        };
    <?php endif; ?>

    function copyLink() {
        const currentUrl = window.location.href;
        navigator.clipboard.writeText(currentUrl).then(() => {
            showToast('Link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    function shareOnWhatsApp() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `*${vendorName}* is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.\n\n${currentUrl}`;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`;
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}&quote=${encodeURIComponent(message)}`;
        window.open(facebookUrl, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.`;
        const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`;
        window.open(twitterUrl, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`;
        const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(vendorName)}&summary=${encodeURIComponent(message)}`;
        window.open(linkedinUrl, '_blank');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (vendorId) {
            loadVendorProfile(vendorId);
        } else {
            showError("No vendor ID provided");
        }

        if (vendorId) {
            loadProductsForDisplay(vendorId);
        }

        document.getElementById('filter-category').addEventListener('change', filterProductsByCategory);
        document.getElementById('search-products').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const categoryId = document.getElementById('filter-category').value;
            filterProducts(categoryId, searchTerm);
        });
        document.getElementById('sort-products').addEventListener('change', function (e) {
            const sortValue = e.target.value;
            const container = document.getElementById('products-container');
            const productCards = Array.from(container.children);
            if (productCards.length <= 1) return;
            productCards.sort((a, b) => {
                if (sortValue === 'latest') {
                    return 0;
                } else if (sortValue === 'price-low') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return aPrice - bPrice;
                } else if (sortValue === 'price-high') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return bPrice - aPrice;
                }
                return 0;
            });
            container.innerHTML = '';
            productCards.forEach(card => container.appendChild(card));
        });

        document.getElementById('loadMoreBtn').addEventListener('click', function () {
            if (currentPage < totalPages) {
                loadProductsForDisplay(vendorId, currentPage + 1);
            }
        });

        document.getElementById('buyInStoreForm').addEventListener('submit', function (e) {
            e.preventDefault();
            submitBuyInStoreRequest();
        });

        document.getElementById('decreaseQuantity').addEventListener('click', function () {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > parseInt(input.min)) {
                input.value = currentValue - 1;
                updateSummary();
            }
        });

        document.getElementById('increaseQuantity').addEventListener('click', function () {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 9999;
            if (currentValue < max) {
                input.value = currentValue + 1;
                updateSummary();
            }
        });

        document.getElementById('packageSelect').addEventListener('change', function () {
            updateCapacityLimits();
            updateSummary();
        });

        document.getElementById('quantityInput').addEventListener('change', updateSummary);
        document.getElementById('notes').addEventListener('input', updateSummary);

        document.getElementById('showAltContact').addEventListener('change', function () {
            const altContactFields = document.getElementById('altContactFields');
            const summaryAltContactContainer = document.getElementById('summaryAltContactContainer');

            if (this.checked) {
                altContactFields.classList.remove('hidden');
                summaryAltContactContainer.classList.remove('hidden');
            } else {
                altContactFields.classList.add('hidden');
                summaryAltContactContainer.classList.add('hidden');
                document.getElementById('altPhone').value = '';
                document.getElementById('altEmail').value = '';
                updateSummary();
            }
        });

        document.getElementById('altPhone').addEventListener('input', updateSummary);
        document.getElementById('altEmail').addEventListener('input', updateSummary);

        document.addEventListener('click', async function (e) {
            if (e.target.classList.contains('view-price-btn')) {
                const sessionActive = await checkUserSession();

                if (!sessionActive) {
                    if (typeof openAuthModal === 'function') {
                        openAuthModal();
                    }
                    return false;
                } else {
                    const priceValue = e.target.nextElementSibling;
                    priceValue.classList.remove('price-hidden');
                    e.target.classList.add('price-hidden');
                }
            }

            if (e.target.classList.contains('view-more-prices')) {
                const sessionActive = await checkUserSession();

                if (!sessionActive) {
                    if (typeof openAuthModal === 'function') {
                        openAuthModal();
                    }
                    return false;
                } else {
                    const hiddenPrices = e.target.previousElementSibling.querySelectorAll('.hidden-price-row');
                    hiddenPrices.forEach(row => row.classList.remove('hidden'));
                    e.target.classList.add('hidden');
                }
            }

            if (e.target.classList.contains('login-btn')) {
                if (typeof openAuthModal === 'function') {
                    openAuthModal();
                }
                return false;
            }
        });

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });

        <?php if ($canEdit): ?>
            document.getElementById('logo-file-input').addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        initCropper(e.target.result);
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            document.getElementById('cover-file-input').addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        initCoverCropper(e.target.result);
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });

            document.getElementById('edit-name-button').addEventListener('click', editName);
            document.getElementById('edit-description-button').addEventListener('click', editDescription);
            document.getElementById('avatar-edit-button').addEventListener('click', editLogo);
            document.getElementById('cover-edit-button').addEventListener('click', editCover);
        <?php endif; ?>
    });

    function loadVendorProfile(id) {
        fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=getStoreDetails&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;
                    renderVendorProfile(storeData);
                    populateCategoryFilter(storeData.categories);
                } else {
                    if (data.error === "Store not found or not active") {
                        showNotFoundOrInactive();
                    } else {
                        showError(data.error || "Failed to load vendor profile");
                    }
                }
            })
            .catch(error => {
                console.error('Error loading vendor profile:', error);
                showError("Failed to load vendor profile");
            });
    }

    function showNotFoundOrInactive() {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('not-found-state').classList.remove('hidden');
    }

    function renderVendorProfile(store) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('content-state').classList.remove('hidden');
        document.getElementById('vendor-name').textContent = store.name;
        document.getElementById('vendor-operation-type').textContent = store.nature_of_business_name;

        const statusBadge = document.getElementById('vendor-status');

        if (store.status === 'active') {
            statusBadge.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = 'Active';
        } else if (store.status === 'pending') {
            statusBadge.className = 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = 'Pending Verification';
        } else {
            statusBadge.className = 'bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = store.status.charAt(0).toUpperCase() + store.status.slice(1);
        }

        document.getElementById('vendor-location').textContent = `${store.district}, ${store.address}`;
        document.getElementById('vendor-description').textContent = store.description || 'No description provided.';
        storeEmail = store.business_email;
        storePhone = store.business_phone;

        const regDate = new Date(store.created_at);
        const formattedDate = regDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('vendor-registered').textContent = `Joined ${formattedDate}`;

        if (store.logo_url) {
            const logoImg = document.createElement('img');
            logoImg.src = BASE_URL + store.logo_url;
            logoImg.alt = store.name;
            logoImg.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoImg);
        } else {
            const firstWord = store.name.split(' ')[0];
            const logoPlaceholder = document.createElement('img');
            logoPlaceholder.src = `https://placehold.co/400x400/e5e7eb/6b7280?text=${encodeURIComponent(firstWord)}`;
            logoPlaceholder.alt = store.name;
            logoPlaceholder.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoPlaceholder);
        }

        const coverContainer = document.getElementById('vendor-cover');
        if (store.vendor_cover_url) {
            coverContainer.style.backgroundImage = `url(${BASE_URL + store.vendor_cover_url})`;
        } else {
            coverContainer.style.backgroundImage = `url(https://placehold.co/1200x400/e5e7eb/6b7280?text=${encodeURIComponent(store.name)})`;
        }

        const productCount = Number(store.product_count) || 0;
        const categoryCount = Number(store.category_count) || 0;
        const productLabel = productCount === 1 ? 'Product' : 'Products';
        const categoryLabel = categoryCount === 1 ? 'Category' : 'Categories';
        document.getElementById('product-count').textContent = `${productCount} ${productLabel}`;
        document.getElementById('category-count').textContent = `${categoryCount} ${categoryLabel}`;

        isOwner = store.is_owner;
    }

    function showError(message) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        console.error(message);
    }

    function formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        if (diffInSeconds < 60) {
            return `${diffInSeconds} seconds ago`;
        }
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
        }
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
        }
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
        }
        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;
        }
        const diffInYears = Math.floor(diffInMonths / 12);
        return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity duration-300`;
        toast.textContent = message;

        document.body.appendChild(toast);

        toast.offsetHeight;

        toast.classList.add('opacity-100');

        setTimeout(() => {
            toast.classList.remove('opacity-100');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }
</script>

<?php
$mainContent = ob_get_clean();

$seoTags = [];
if ($storeData) {
    $seoTags = generateSeoMetaTags($storeData);
}

include __DIR__ . '/master.php';
?>