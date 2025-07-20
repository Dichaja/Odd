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
            SELECT
                p.id,
                p.title,
                p.description,
                p.meta_title,
                p.meta_description,
                p.meta_keywords,
                p.category_id,
                c.name AS category_name
            FROM products p
            JOIN product_categories c ON c.id = p.category_id
            WHERE p.status = 'published'
            ORDER BY p.title ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'products' => $products,
            'timestamp' => time()
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error occurred',
            'products' => []
        ]);
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'image') {
    header('Content-Type: application/json');

    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';

    if (!$type || !$id) {
        echo json_encode(['error' => 'Missing parameters']);
        exit;
    }

    $basePath = 'img/products/';
    $fullPath = __DIR__ . '/' . $basePath . $id . '/';

    if (!is_dir($fullPath)) {
        echo json_encode(['image' => null]);
        exit;
    }

    $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
    $images = [];

    $files = scandir($fullPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..')
            continue;

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, $allowedExtensions)) {
            $images[] = $file;
        }
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

$recaptcha_site_key = '6LdtJdcqAAAAADWom9IW8lSg7L41BQbAJPrAW-Hf';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .form-group {
        position: relative;
    }

    .floating-label {
        position: absolute;
        left: 1rem;
        top: 0.8rem;
        padding: 0 0.25rem;
        background-color: white;
        transition: all 0.2s ease-in-out;
        pointer-events: none;
    }

    .form-input:focus~.floating-label,
    .form-input:not(:placeholder-shown)~.floating-label {
        transform: translateY(-1.4rem) scale(0.85);
        background-color: white;
        color: #000000;
    }

    .form-input:focus {
        border-color: #ef4444;
    }

    .iti {
        width: 100%;
    }

    .page-header {
        background-image: linear-gradient(to right, rgba(239, 68, 68, 0.9), rgba(185, 28, 28, 0.8)),
            url('https://dummyimage.com/1920x350/e3e3e3/ffffff&text=Request+Quote');
        background-size: cover;
        background-position: center;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }

    .form-card {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .item-report {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .item-report thead {
        background-color: #f3f4f6;
    }

    .item-report th {
        padding: 0.75rem 1rem;
        font-weight: 600;
        text-align: left;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .item-report td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        color: #4b5563;
    }

    .item-report tbody tr {
        transition: all 0.2s ease;
    }

    .item-report tbody tr:hover {
        background-color: #f9fafb;
    }

    .item-report tbody tr:last-child td {
        border-bottom: none;
    }

    .btn-primary {
        background-image: linear-gradient(to right, #ef4444, #dc2626);
        color: white;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-image: linear-gradient(to right, #dc2626, #b91c1c);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-image: linear-gradient(to right, #10b981, #059669);
        color: white;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-image: linear-gradient(to right, #059669, #047857);
        transform: translateY(-1px);
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-content {
        background-color: white;
        border-radius: 0.75rem;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: scale(0.95);
        transition: all 0.3s ease;
    }

    .modal.active .modal-content {
        transform: scale(1);
    }

    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 60;
        display: none;
    }

    .search-dropdown.show {
        display: block;
    }

    .search-dropdown-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .search-dropdown-item:hover {
        background-color: #f9fafb;
    }

    .search-dropdown-item:last-child {
        border-bottom: none;
    }

    .search-dropdown-header {
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .search-note {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        border-radius: 0 0.375rem 0.375rem 0;
    }

    .search-image {
        transition: opacity 0.3s ease;
    }

    .search-image.loading {
        opacity: 0.5;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem 1rem;
        text-align: center;
        background-color: #f9fafb;
        border-radius: 0.5rem;
        border: 2px dashed #e5e7eb;
    }

    .required-star {
        color: #ef4444;
        font-weight: bold;
    }

    .action-icon {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-icon:hover {
        transform: scale(1.2);
    }

    .edit-icon:hover {
        color: #3b82f6;
    }

    .delete-icon:hover {
        color: #ef4444;
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="form-card p-6 md:p-8 fade-in">
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Request Details</h2>
                <p class="text-gray-600 mb-6">Fields marked with <span class="required-star">*</span> are required</p>

                <form id="rfq-form" class="space-y-6" novalidate autocomplete="off">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <input type="text" id="company" name="company" placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-company" data-field-id="<?= uniqid('company_') ?>">
                            <label for="company" class="floating-label text-gray-500">Company Name (optional)</label>
                        </div>

                        <div class="form-group">
                            <input type="text" id="contact" name="contact" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-name" data-field-id="<?= uniqid('contact_') ?>">
                            <label for="contact" class="floating-label text-gray-500">Contact Person <span
                                    class="required-star">*</span></label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <input type="email" id="email" name="email" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-email" data-field-id="<?= uniqid('email_') ?>">
                            <label for="email" class="floating-label text-gray-500">Email <span
                                    class="required-star">*</span></label>
                        </div>

                        <div class="form-group">
                            <input type="tel" id="phone-whatsapp" name="phone" required
                                placeholder="Phone/WhatsApp Contact *"
                                class="block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                autocomplete="new-phone" data-field-id="<?= uniqid('phone_') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" id="location" name="location" required placeholder=" "
                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                            autocomplete="new-address" data-field-id="<?= uniqid('location_') ?>">
                        <label for="location" class="floating-label text-gray-500">Site Location <span
                                class="required-star">*</span></label>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-800">List of Items <span
                                    class="required-star">*</span></h2>
                            <button type="button" id="add-item-btn"
                                class="btn-secondary inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md focus:outline-none transition-colors shadow-sm">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        </div>

                        <div class="bg-white rounded-lg overflow-hidden">
                            <div id="items-container" class="w-full">
                                <table class="item-report">
                                    <thead>
                                        <tr>
                                            <th class="w-5/12">Brand/Material</th>
                                            <th class="w-4/12">Size/Specification</th>
                                            <th class="w-2/12">Quantity</th>
                                            <th class="w-1/12 text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-list">
                                    </tbody>
                                </table>

                                <div id="empty-items-state" class="empty-state">
                                    <div class="text-gray-400 mb-3">
                                        <i class="fas fa-clipboard-list text-4xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-700 mb-1">No Items Added</h3>
                                    <p class="text-sm text-gray-500 mb-4">Click the "Add Item" button to add materials
                                        to your quote request</p>
                                    <button type="button" id="empty-add-item-btn"
                                        class="btn-secondary inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md focus:outline-none transition-colors shadow-sm">
                                        <i class="fas fa-plus mr-2"></i> Add First Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="reset" id="reset-form"
                            class="px-5 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors shadow-sm">
                            <i class="fas fa-times-circle mr-2"></i> Cancel
                        </button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 text-sm font-medium text-white rounded-md focus:outline-none transition-colors shadow-sm">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div
                class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 fade-in mb-6 border-l-4 border-red-500 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-info-circle text-red-500"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Note</h2>
                </div>
                <div class="space-y-4 text-sm text-gray-600">
                    <p class="flex items-start">
                        <i class="fas fa-mobile-alt mt-1 text-red-500 mr-3"></i>
                        <span>Add a valid phone number or WhatsApp contact for quick contact.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 text-red-500 mr-3"></i>
                        <span>Specify the exact site location for delivery purposes.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-plus-circle mt-1 text-red-500 mr-3"></i>
                        <span>Use the "Add Item" button to request multiple items.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-edit mt-1 text-red-500 mr-3"></i>
                        <span>Edit items by clicking the pencil icon in the actions column.</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 fade-in">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-clipboard-check text-blue-500"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Item Details</h2>
                </div>
                <div class="space-y-3 text-sm text-gray-600">
                    <p class="font-medium">For each item, specify:</p>
                    <ul class="space-y-2 pl-6">
                        <li class="flex items-start">
                            <i class="fas fa-trademark mt-1 text-gray-400 mr-3"></i>
                            <span>Brand name or specifications</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-ruler-combined mt-1 text-gray-400 mr-3"></i>
                            <span>Required size or dimensions</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-sort-amount-up mt-1 text-gray-400 mr-3"></i>
                            <span>Quantity needed</span>
                        </li>
                    </ul>
                    <div class="mt-6 p-4 bg-yellow-50 rounded-md border-l-4 border-yellow-400">
                        <p class="text-yellow-700 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span>All fields marked with <span class="required-star">*</span> are required.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="item-modal" class="modal">
    <div class="modal-content p-0">
        <div
            class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800" id="modal-title">Add New Item</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" id="close-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="search-note">
                <p class="text-sm text-blue-700 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span>Start typing to see product suggestions. You can select from the list or continue typing your
                        own brand/material name.</span>
                </p>
            </div>

            <form id="item-form" class="space-y-4">
                <input type="hidden" id="item-index" value="-1">

                <div class="form-group">
                    <input type="text" id="item-brand" name="brand" required placeholder=" "
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="off">
                    <label for="item-brand" class="floating-label text-gray-500">Brand/Material <span
                            class="required-star">*</span></label>
                    <div id="brand-search-dropdown" class="search-dropdown"></div>
                </div>

                <div class="form-group">
                    <input type="text" id="item-size" name="size" required placeholder=" "
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="off">
                    <label for="item-size" class="floating-label text-gray-500">Size/Specification <span
                            class="required-star">*</span></label>
                </div>

                <div class="form-group">
                    <input type="number" id="item-quantity" name="quantity" required placeholder=" " min="1"
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="off">
                    <label for="item-quantity" class="floating-label text-gray-500">Quantity <span
                            class="required-star">*</span></label>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-item"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="save-item"
                        class="btn-secondary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors">
                        <i class="fas fa-save mr-2"></i> Save Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="delete-modal" class="modal">
    <div class="modal-content p-0">
        <div
            class="bg-gradient-to-r from-red-50 to-red-100 p-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Confirm Deletion</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none" id="close-delete-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
            </div>
            <p class="text-center text-gray-700 mb-6">Are you sure you want to remove this item from your quote request?
            </p>
            <input type="hidden" id="delete-item-index" value="-1">
            <div class="flex justify-center space-x-3">
                <button type="button" id="cancel-delete"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-delete"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md focus:outline-none transition-colors">
                    <i class="fas fa-trash-alt mr-2"></i> Delete Item
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_site_key ?>"></script>
<script defer src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const API_BASE = "<?php echo BASE_URL; ?>fetch/handleRFQ";

        let SEARCH_DATA = { products: [] };
        let fuseProducts = null;
        let searchInitialized = false;
        let imageCache = new Map();
        let isSelectionMade = false; // Flag to track if a selection was just made

        function getImageUrl(type, id) {
            const cacheKey = `${type}_${id}`;

            if (imageCache.has(cacheKey)) {
                return Promise.resolve(imageCache.get(cacheKey));
            }

            return fetch(`${window.location.href}?ajax=image&type=${type}&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    const imageUrl = data.image || `https://placehold.co/60x60?text=No+Image`;
                    imageCache.set(cacheKey, imageUrl);
                    return imageUrl;
                })
                .catch(() => {
                    const fallbackUrl = `https://placehold.co/60x60?text=No+Image`;
                    imageCache.set(cacheKey, fallbackUrl);
                    return fallbackUrl;
                });
        }

        function loadSearchData() {
            return fetch(window.location.href + '?ajax=data')
                .then(response => response.json())
                .then(data => {
                    SEARCH_DATA = data;
                    buildSearchIndexes();
                    searchInitialized = true;
                })
                .catch(error => {
                    console.error('Failed to load search data:', error);
                });
        }

        function buildSearchIndexes() {
            if (!window.Fuse) {
                setTimeout(buildSearchIndexes, 100);
                return;
            }

            fuseProducts = new Fuse(
                SEARCH_DATA.products.map(p => ({ ...p })),
                {
                    includeScore: true,
                    threshold: 0.4,
                    ignoreLocation: true,
                    keys: [
                        { name: 'title', weight: 0.4 },
                        { name: 'meta_title', weight: 0.3 },
                        { name: 'description', weight: 0.2 },
                        { name: 'meta_description', weight: 0.2 },
                        { name: 'meta_keywords', weight: 0.2 },
                        { name: 'category_name', weight: 0.1 }
                    ]
                });
        }

        async function renderProductDropdown(query, dropdownElement, inputElement) {
            query = query.trim().toLowerCase();

            // Don't show dropdown if a selection was just made or if query is empty
            if (!query || !fuseProducts || !searchInitialized || isSelectionMade) {
                dropdownElement.style.display = 'none';
                return;
            }

            const productResults = fuseProducts.search(query, { limit: 8 });
            let html = '';

            if (productResults.length) {
                html += '<div class="search-dropdown-header">Available Products</div>';

                for (const result of productResults) {
                    const product = result.item;
                    html += `
                        <div class="search-dropdown-item" data-product-title="${escapeHtml(product.title)}">
                            <img src="https://placehold.co/40x40?text=Loading..." alt="Product" class="w-10 h-10 rounded flex-shrink-0 object-cover search-image loading" data-type="product" data-id="${product.id}">
                            <div>
                                <div class="font-medium text-sm">${escapeHtml(product.title)}</div>
                                <div class="text-xs text-gray-500">${escapeHtml(product.category_name)}</div>
                            </div>
                        </div>`;
                }
            }

            if (html) {
                dropdownElement.innerHTML = html;
                dropdownElement.style.display = 'block';

                const images = dropdownElement.querySelectorAll('.search-image.loading');
                images.forEach(async (img) => {
                    const type = img.dataset.type;
                    const id = img.dataset.id;
                    try {
                        const imageUrl = await getImageUrl(type, id);
                        img.src = imageUrl;
                        img.classList.remove('loading');
                    } catch (error) {
                        img.src = 'https://placehold.co/40x40?text=No+Image';
                        img.classList.remove('loading');
                    }
                });

                const items = dropdownElement.querySelectorAll('.search-dropdown-item');
                items.forEach(item => {
                    item.addEventListener('click', function () {
                        const productTitle = this.dataset.productTitle;
                        isSelectionMade = true; // Set flag to prevent dropdown from showing
                        inputElement.value = productTitle;
                        dropdownElement.style.display = 'none';

                        // Reset the flag after a short delay to allow normal typing behavior
                        setTimeout(() => {
                            isSelectionMade = false;
                        }, 100);
                    });
                });
            } else {
                dropdownElement.style.display = 'none';
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        const phoneInputField = document.querySelector("#phone-whatsapp");
        const iti = window.intlTelInput(phoneInputField, {
            preferredCountries: ["ug", "rw", "ke", "tz"],
            initialCountry: "ug",
            separateDialCode: true,
            allowDropdown: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        const formInputs = document.querySelectorAll('input');
        formInputs.forEach(input => {
            const randomAttr = Math.random().toString(36).substring(2);
            input.setAttribute('data-random', randomAttr);
        });

        let items = [];
        const itemsList = document.getElementById('items-list');
        const emptyState = document.getElementById('empty-items-state');

        const itemModal = document.getElementById('item-modal');
        const modalTitle = document.getElementById('modal-title');
        const itemForm = document.getElementById('item-form');
        const itemIndex = document.getElementById('item-index');
        const itemBrand = document.getElementById('item-brand');
        const itemSize = document.getElementById('item-size');
        const itemQuantity = document.getElementById('item-quantity');
        const brandSearchDropdown = document.getElementById('brand-search-dropdown');

        const deleteModal = document.getElementById('delete-modal');
        const deleteItemIndex = document.getElementById('delete-item-index');

        function updateItemsDisplay() {
            itemsList.innerHTML = '';

            if (items.length === 0) {
                emptyState.style.display = 'flex';
                return;
            }

            emptyState.style.display = 'none';

            items.forEach((item, index) => {
                const row = document.createElement('tr');
                row.className = 'fade-in';
                row.innerHTML = `
                    <td class="align-middle">${item.brand}</td>
                    <td class="align-middle">${item.size}</td>
                    <td class="align-middle">${item.quantity}</td>
                    <td class="align-middle text-center">
                        <div class="flex justify-center space-x-3">
                            <i class="fas fa-edit text-gray-500 hover:text-blue-500 cursor-pointer action-icon edit-icon" data-index="${index}" title="Edit Item"></i>
                            <i class="fas fa-trash-alt text-gray-500 hover:text-red-500 cursor-pointer action-icon delete-icon" data-index="${index}" title="Remove Item"></i>
                        </div>
                    </td>
                `;
                itemsList.appendChild(row);
            });

            document.querySelectorAll('.edit-icon').forEach(icon => {
                icon.addEventListener('click', function () {
                    const index = parseInt(this.getAttribute('data-index'));
                    editItem(index);
                });
            });

            document.querySelectorAll('.delete-icon').forEach(icon => {
                icon.addEventListener('click', function () {
                    const index = parseInt(this.getAttribute('data-index'));
                    showDeleteModal(index);
                });
            });
        }

        function showAddItemModal() {
            modalTitle.textContent = 'Add New Item';
            itemForm.reset();
            itemIndex.value = -1;
            isSelectionMade = false; // Reset selection flag when opening modal
            itemModal.classList.add('active');
            brandSearchDropdown.style.display = 'none';
        }

        function editItem(index) {
            const item = items[index];
            modalTitle.textContent = 'Edit Item';
            itemBrand.value = item.brand;
            itemSize.value = item.size;
            itemQuantity.value = item.quantity;
            itemIndex.value = index;
            isSelectionMade = false; // Reset selection flag when editing
            itemModal.classList.add('active');
            brandSearchDropdown.style.display = 'none';
        }

        function showDeleteModal(index) {
            deleteItemIndex.value = index;
            deleteModal.classList.add('active');
        }

        function saveItem(e) {
            e.preventDefault();

            const brand = itemBrand.value.trim();
            const size = itemSize.value.trim();
            const quantity = itemQuantity.value.trim();

            if (!brand || !size || !quantity || parseInt(quantity) <= 0) {
                notifications.error('Please fill in all required fields with valid values.', 'Validation Error');
                return;
            }

            const index = parseInt(itemIndex.value);
            const item = {
                brand: brand,
                size: size,
                quantity: quantity
            };

            if (index === -1) {
                items.push(item);
            } else {
                items[index] = item;
            }

            updateItemsDisplay();
            itemModal.classList.remove('active');
            brandSearchDropdown.style.display = 'none';
            isSelectionMade = false; // Reset selection flag after saving
        }

        function deleteItem() {
            const index = parseInt(deleteItemIndex.value);
            if (index >= 0 && index < items.length) {
                items.splice(index, 1);
                updateItemsDisplay();
            }
            deleteModal.classList.remove('active');
        }

        loadSearchData().then(() => {
            itemBrand.addEventListener('input', debounce((e) => {
                // Only show dropdown if it's not a selection-triggered input
                if (!isSelectionMade) {
                    renderProductDropdown(e.target.value, brandSearchDropdown, itemBrand);
                }
            }, 200));

            itemBrand.addEventListener('focus', () => {
                // Only show dropdown on focus if there's a value and no recent selection
                if (itemBrand.value.trim() && !isSelectionMade) {
                    renderProductDropdown(itemBrand.value, brandSearchDropdown, itemBrand);
                }
            });

            // Reset selection flag when user starts typing manually
            itemBrand.addEventListener('keydown', () => {
                isSelectionMade = false;
            });

            document.addEventListener('click', (e) => {
                if (!itemBrand.contains(e.target) && !brandSearchDropdown.contains(e.target)) {
                    brandSearchDropdown.style.display = 'none';
                }
            });
        });

        document.getElementById('add-item-btn').addEventListener('click', showAddItemModal);
        document.getElementById('empty-add-item-btn').addEventListener('click', showAddItemModal);
        document.getElementById('close-modal').addEventListener('click', () => {
            itemModal.classList.remove('active');
            brandSearchDropdown.style.display = 'none';
            isSelectionMade = false;
        });
        document.getElementById('cancel-item').addEventListener('click', () => {
            itemModal.classList.remove('active');
            brandSearchDropdown.style.display = 'none';
            isSelectionMade = false;
        });
        document.getElementById('close-delete-modal').addEventListener('click', () => deleteModal.classList.remove('active'));
        document.getElementById('cancel-delete').addEventListener('click', () => deleteModal.classList.remove('active'));
        document.getElementById('confirm-delete').addEventListener('click', deleteItem);
        itemForm.addEventListener('submit', saveItem);

        window.addEventListener('click', function (e) {
            if (e.target === itemModal) {
                itemModal.classList.remove('active');
                brandSearchDropdown.style.display = 'none';
                isSelectionMade = false;
            }
            if (e.target === deleteModal) {
                deleteModal.classList.remove('active');
            }
        });

        document.getElementById('reset-form').addEventListener('click', function (e) {
            e.preventDefault();
            const form = document.getElementById('rfq-form');
            form.reset();
            iti.setNumber('');
            items = [];
            updateItemsDisplay();
        });

        const form = document.getElementById('rfq-form');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let hasError = false;
            const contactInput = document.getElementById('contact');
            const emailInput = document.getElementById('email');
            const locationInput = document.getElementById('location');

            if (contactInput.value.trim() === "") {
                notifications.error('Contact person is required.', 'Input Required');
                hasError = true;
            }

            if (emailInput.value.trim() === "") {
                notifications.error('Email is required.', 'Input Required');
                hasError = true;
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    notifications.error('Please enter a valid email address.', 'Input Required');
                    hasError = true;
                }
            }

            if (phoneInputField.value.trim() === "" || !iti.isValidNumber()) {
                notifications.error('Please enter a valid phone number.', 'Input Required');
                hasError = true;
            }

            if (locationInput.value.trim() === "") {
                notifications.error('Site location is required.', 'Input Required');
                hasError = true;
            }

            if (items.length === 0) {
                notifications.error('Please add at least one item.', 'Input Required');
                hasError = true;
            }

            if (hasError) return;

            const payload = {
                company: document.getElementById('company').value.trim(),
                contact: contactInput.value.trim(),
                email: emailInput.value.trim(),
                phone: iti.getNumber(),
                location: locationInput.value.trim(),
                items: items,
                "g-recaptcha-response": document.getElementById('g-recaptcha-response').value
            };

            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';

            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'submit_rfq'
            }).then(function (token) {
                payload["g-recaptcha-response"] = token;

                fetch(`${API_BASE}/submitRFQ`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifications.success('Thank you! Your quote request has been received. We will contact you shortly.', 'RFQ Submitted');
                            form.reset();
                            iti.setNumber('');
                            items = [];
                            updateItemsDisplay();
                        } else {
                            notifications.error('Submission failed. Please try again.', 'RFQ Error');
                        }
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    })
                    .catch(error => {
                        notifications.error('Submission failed. Please try again.', 'RFQ Error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    });
            });
        });

        updateItemsDisplay();
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>