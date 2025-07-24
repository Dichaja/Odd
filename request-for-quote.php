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
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

    .btn-topup {
        background-image: linear-gradient(to right, #f59e0b, #d97706);
        color: white;
        transition: all 0.3s ease;
    }

    .btn-topup:hover {
        background-image: linear-gradient(to right, #d97706, #b45309);
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

    .map-modal-content {
        width: 90%;
        max-width: 800px;
        height: 80vh;
        max-height: 600px;
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

    #map {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
    }

    .map-search-container {
        position: relative;
        margin-bottom: 1rem;
    }

    .map-search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 1rem;
        outline: none;
        transition: border-color 0.2s ease;
    }

    .map-search-input:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .map-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .map-search-result {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.2s ease;
    }

    .map-search-result:hover {
        background-color: #f9fafb;
    }

    .map-search-result:last-child {
        border-bottom: none;
    }

    .location-display {
        background-color: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
    }

    .item-limit-notice {
        background-color: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .mobile-hide {
            display: none !important;
        }

        .mobile-hide-title {
            display: none !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="form-card p-6 md:p-8 fade-in">
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Request Details</h2>
                <p class="text-gray-600 mb-6">Fields marked with <span class="required-star">*</span> are required</p>
                <form id="rfq-form" class="space-y-6" novalidate autocomplete="off">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-800 mobile-hide-title">List of Items <span
                                    class="required-star">*</span></h2>
                            <button type="button" id="add-item-btn"
                                class="btn-secondary inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md focus:outline-none transition-colors shadow-sm">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        </div>
                        <div id="item-limit-notice" class="item-limit-notice" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <span>You can add upto 5 items per quote request.</span>
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
                                <div id="empty-items-state"
                                    class="flex flex-col items-center justify-center text-center py-10 px-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="text-gray-400 mb-4">
                                        <i class="fas fa-clipboard-list text-5xl"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2">No Items Added</h3>
                                    <p class="text-sm text-gray-600 mb-6">Click the "Add Item" button to add materials
                                        to your quote request. Maximum 5 items allowed.</p>
                                    <button type="button" id="empty-add-item-btn"
                                        class="bg-indigo-600 hover:bg-indigo-700 inline-flex items-center px-5 py-2.5 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md transition-colors">
                                        <i class="fas fa-plus mr-2"></i> Add First Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="location" name="location" required placeholder=" " readonly
                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none cursor-pointer"
                            autocomplete="new-address" data-field-id="<?= uniqid('location_') ?>">
                        <label for="location" class="floating-label text-gray-500">Site Location <span
                                class="required-star">*</span></label>
                        <div id="location-display" class="location-display" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium" id="selected-address"></div>
                                    <div class="text-xs text-gray-500" id="selected-coordinates"></div>
                                </div>
                                <button type="button" onclick="openLocationModal()"
                                    class="text-red-500 hover:text-red-700 text-sm">
                                    <i class="fas fa-edit mr-1"></i>Change
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="reset" id="reset-form"
                            class="px-5 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors shadow-sm">
                            <i class="fas fa-times-circle mr-2"></i> Cancel
                        </button>
                        <button type="submit" id="submit-btn"
                            class="btn-primary px-5 py-3 text-sm font-medium text-white rounded-md focus:outline-none transition-colors shadow-sm">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="lg:col-span-1">
            <div
                class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 fade-in mb-6 border-l-4 border-red-500 shadow-sm mobile-hide">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-info-circle text-red-500"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Note</h2>
                </div>
                <div class="space-y-4 text-sm text-gray-600">
                    <p class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 text-red-500 mr-3"></i>
                        <span>Click on the site location field to select your delivery location on the map.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-plus-circle mt-1 text-red-500 mr-3"></i>
                        <span>Use the "Add Item" button to request multiple items (maximum 5 items).</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-edit mt-1 text-red-500 mr-3"></i>
                        <span>Edit items by clicking the pencil icon in the actions column.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-save mt-1 text-red-500 mr-3"></i>
                        <span>Your form data is automatically saved and will be retained for 10 minutes.</span>
                    </p>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-6 fade-in mobile-hide">
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

<div id="location-modal" class="modal">
    <div class="modal-content map-modal-content p-0">
        <div
            class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Select Delivery Location</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none"
                id="close-location-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4">
            <div class="map-search-container">
                <input type="text" id="map-search-input" placeholder="Search for a location..."
                    class="map-search-input">
                <div id="map-search-results" class="map-search-results"></div>
            </div>
            <div id="map"></div>
            <div class="mt-4 flex justify-end space-x-3">
                <button type="button" id="cancel-location"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-location"
                    class="btn-primary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors"
                    disabled>
                    <i class="fas fa-map-marker-alt mr-2"></i> Confirm Location
                </button>
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

<div id="confirmation-modal" class="modal">
    <div class="modal-content confirmation-modal p-0">
        <div
            class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Confirm Quote Request</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none"
                id="close-confirmation-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-credit-card text-blue-500 text-2xl"></i>
                </div>
            </div>
            <div class="text-center mb-6">
                <h4 class="text-lg font-medium text-gray-900 mb-2">Zzimba Credit Deduction</h4>
                <p class="text-gray-600 mb-4">A fee will be charged for processing this quote request.</p>
                <div id="confirmation-details" class="space-y-3"></div>
            </div>
            <div class="flex justify-center space-x-3">
                <button type="button" id="cancel-confirmation"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                    Cancel
                </button>
                <button type="button" id="confirm-submission"
                    class="btn-primary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors">
                    <i class="fas fa-check mr-2"></i> Confirm & Submit
                </button>
            </div>
        </div>
    </div>
</div>

<div id="no-wallet-modal" class="modal">
    <div class="modal-content p-0">
        <div
            class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-4 flex justify-between items-center border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">No Zzimba Wallet Found</h3>
            <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-none"
                id="close-no-wallet-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 text-center">
            <p class="text-gray-700 mb-4">You need to activate your Zzimba Wallet before submitting a quote request.</p>
            <button type="button" id="activate-wallet-btn"
                class="btn-primary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors">
                Activate Wallet
            </button>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const API_BASE = "<?php echo BASE_URL; ?>fetch/manageRFQ";
        const BASE_URL = "<?php echo BASE_URL; ?>";
        const MAX_ITEMS = 5;
        let IS_LOGGED_IN = <?php echo (isset($_SESSION['user']) && $_SESSION['user']['logged_in']) ? 'true' : 'false'; ?>;
        const IS_ADMIN = <?php echo (isset($_SESSION['user']) && $_SESSION['user']['logged_in'] && $_SESSION['user']['is_admin']) ? 'true' : 'false'; ?>;
        let SEARCH_DATA = { products: [] };
        let fuseProducts = null;
        let searchInitialized = false;
        let imageCache = new Map();
        let isSelectionMade = false;
        let lastActivityTime = Date.now();
        let activityTimer;
        let map;
        let marker;
        let selectedLocation = null;
        let walletInfo = { balance: 0, fee: 0, canSubmit: false, noWallet: false };

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-UG', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        function checkSession() {
            return fetch(`${BASE_URL}fetch/check-session.php`)
                .then(res => res.json())
                .then(data => {
                    IS_LOGGED_IN = data.logged_in || false;
                    return IS_LOGGED_IN;
                })
                .catch(() => false);
        }

        function checkWalletBalance() {
            if (!IS_LOGGED_IN) return Promise.resolve();
            return fetch(`${API_BASE}?action=checkWalletBalance`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        walletInfo = {
                            balance: data.balance,
                            fee: data.fee,
                            canSubmit: data.canSubmit,
                            noWallet: false
                        };
                    } else if (data.error === 'No Zzimba Wallet found') {
                        walletInfo.noWallet = true;
                    }
                    updateSubmitButton();
                })
                .catch(() => { });
        }

        function updateSubmitButton() {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Submit Request';
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        function showNoWalletModal() {
            const modal = document.getElementById('no-wallet-modal');
            modal.classList.add('active');
            document.getElementById('activate-wallet-btn').onclick = function () {
                window.open(`${BASE_URL}account/zzimba-credit`, '_self');
            };
            document.getElementById('close-no-wallet-modal').onclick = function () {
                modal.classList.remove('active');
            };
        }

        function showConfirmationModal() {
            const modal = document.getElementById('confirmation-modal');
            const details = document.getElementById('confirmation-details');
            const insufficient = !walletInfo.canSubmit && walletInfo.fee > 0;
            details.innerHTML = `
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Quote Request Fee:</span>
                        <span class="font-medium text-gray-900">UGX ${formatCurrency(walletInfo.fee)}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Current Wallet Balance:</span>
                        <span class="font-medium ${insufficient ? 'text-red-600' : 'text-green-600'}">UGX ${formatCurrency(walletInfo.balance)}</span>
                    </div>
                    ${insufficient ? `
                        <div class="flex justify-between items-center border-t pt-2">
                            <span class="text-red-600 font-medium">Amount Needed:</span>
                            <span class="font-medium text-red-600">UGX ${formatCurrency(walletInfo.fee - walletInfo.balance)}</span>
                        </div>
                        <div class="text-center text-red-600 text-sm mt-3">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Please top up your wallet to continue
                        </div>
                    ` : `
                        <div class="flex justify-between items-center border-t pt-2">
                            <span class="text-gray-600">Remaining Balance:</span>
                            <span class="font-medium text-green-600">UGX ${formatCurrency(walletInfo.balance - walletInfo.fee)}</span>
                        </div>
                    `}
                </div>
            `;
            const btn = document.getElementById('confirm-submission');
            if (insufficient) {
                btn.innerHTML = '<i class="fas fa-wallet mr-2"></i> Top Up Wallet';
                btn.className = 'btn-topup px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors';
                btn.onclick = function () {
                    window.open(`${BASE_URL}account/zzimba-credit`, '_self');
                };
            } else {
                btn.innerHTML = '<i class="fas fa-check mr-2"></i> Confirm & Submit';
                btn.className = 'btn-primary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors';
                btn.onclick = function () {
                    document.getElementById('confirmation-modal').classList.remove('active');
                    submitRFQ();
                };
            }
            modal.classList.add('active');
        }

        function updateActivity() {
            lastActivityTime = Date.now();
            clearTimeout(activityTimer);
            activityTimer = setTimeout(checkInactivity, 60000);
        }

        function checkInactivity() {
            const now = Date.now();
            const tenMin = 10 * 60 * 1000;
            if (now - lastActivityTime > tenMin) {
                localStorage.removeItem('rfq_form_data');
            } else {
                activityTimer = setTimeout(checkInactivity, 60000);
            }
        }

        function saveFormData() {
            const data = {
                location: selectedLocation,
                items: items,
                lastActivity: lastActivityTime
            };
            localStorage.setItem('rfq_form_data', JSON.stringify(data));
        }

        function loadFormData() {
            const raw = localStorage.getItem('rfq_form_data');
            if (!raw) return;
            try {
                const data = JSON.parse(raw);
                const now = Date.now();
                if (now - data.lastActivity > 10 * 60 * 1000) {
                    localStorage.removeItem('rfq_form_data');
                    return;
                }
                if (data.location) {
                    selectedLocation = data.location;
                    updateLocationDisplay();
                }
                if (Array.isArray(data.items)) {
                    items = data.items;
                    updateItemsDisplay();
                }
            } catch { }
        }

        function checkAuthenticationBeforeSubmit() {
            return checkSession().then(ok => {
                if (!ok) {
                    saveFormData();
                    return false;
                }
                if (IS_ADMIN) {
                    saveFormData();
                    return false;
                }
                return true;
            });
        }

        function initializeMap() {
            map = L.map('map').setView([0.3476, 32.5825], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            map.on('click', e => setMapMarker(e.latlng.lat, e.latlng.lng));
        }

        function setMapMarker(lat, lng) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    const addr = data.display_name || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    selectedLocation = { lat, lng, address: addr };
                    document.getElementById('confirm-location').disabled = false;
                })
                .catch(() => {
                    selectedLocation = { lat, lng, address: `${lat.toFixed(6)}, ${lng.toFixed(6)}` };
                    document.getElementById('confirm-location').disabled = false;
                });
        }

        function searchLocation(query) {
            if (query.length < 3) {
                document.getElementById('map-search-results').style.display = 'none';
                return;
            }
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                .then(res => res.json())
                .then(data => {
                    const cont = document.getElementById('map-search-results');
                    cont.innerHTML = '';
                    if (data.length) {
                        data.forEach(r => {
                            const div = document.createElement('div');
                            div.className = 'map-search-result';
                            div.textContent = r.display_name;
                            div.addEventListener('click', () => {
                                const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                                map.setView([lat, lng], 15);
                                setMapMarker(lat, lng);
                                cont.style.display = 'none';
                                document.getElementById('map-search-input').value = r.display_name;
                            });
                            cont.appendChild(div);
                        });
                        cont.style.display = 'block';
                    } else {
                        cont.style.display = 'none';
                    }
                })
                .catch(() => {
                    document.getElementById('map-search-results').style.display = 'none';
                });
        }

        function openLocationModal() {
            const m = document.getElementById('location-modal');
            m.classList.add('active');
            setTimeout(() => {
                if (!map) initializeMap();
                else map.invalidateSize();
                if (selectedLocation) {
                    map.setView([selectedLocation.lat, selectedLocation.lng], 15);
                    setMapMarker(selectedLocation.lat, selectedLocation.lng);
                }
            }, 100);
        }

        function updateLocationDisplay() {
            if (!selectedLocation) return;
            document.getElementById('location').value = selectedLocation.address;
            document.getElementById('selected-address').textContent = selectedLocation.address;
            document.getElementById('selected-coordinates').textContent = `${selectedLocation.lat.toFixed(6)}, ${selectedLocation.lng.toFixed(6)}`;
            document.getElementById('location-display').style.display = 'block';
        }

        function getImageUrl(type, id) {
            const key = `${type}_${id}`;
            if (imageCache.has(key)) return Promise.resolve(imageCache.get(key));
            return fetch(`${window.location.href}?ajax=image&type=${type}&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    const url = data.image || `https://placehold.co/60x60?text=No+Image`;
                    imageCache.set(key, url);
                    return url;
                })
                .catch(() => {
                    const fallback = `https://placehold.co/60x60?text=No+Image`;
                    imageCache.set(key, fallback);
                    return fallback;
                });
        }

        function loadSearchData() {
            return fetch(window.location.href + '?ajax=data')
                .then(res => res.json())
                .then(data => {
                    if (data && Array.isArray(data.products)) {
                        SEARCH_DATA = data;
                        buildSearchIndexes();
                        searchInitialized = true;
                    } else {
                        SEARCH_DATA = { products: [] };
                        searchInitialized = false;
                    }
                })
                .catch(() => {
                    SEARCH_DATA = { products: [] };
                    searchInitialized = false;
                });
        }

        function buildSearchIndexes() {
            if (!window.Fuse) {
                setTimeout(buildSearchIndexes, 100);
                return;
            }
            try {
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
                    }
                );
                searchInitialized = true;
            } catch {
                fuseProducts = null;
                searchInitialized = false;
            }
        }

        async function renderProductDropdown(query, dropdown, input) {
            query = query.trim().toLowerCase();
            if (!query || !fuseProducts || !searchInitialized || isSelectionMade) {
                dropdown.style.display = 'none';
                return;
            }
            try {
                const results = fuseProducts.search(query, { limit: 8 });
                let html = '';
                if (results.length) {
                    html += '<div class="search-dropdown-header">Available Products</div>';
                    for (const r of results) {
                        const p = r.item;
                        html += `
                            <div class="search-dropdown-item" data-product-title="${escapeHtml(p.title)}">
                                <img src="https://placehold.co/40x40?text=Loading..." alt="Product" class="w-10 h-10 rounded flex-shrink-0 object-cover search-image loading" data-type="product" data-id="${p.id}">
                                <div>
                                    <div class="font-medium text-sm">${escapeHtml(p.title)}</div>
                                    <div class="text-xs text-gray-500">${escapeHtml(p.category_name)}</div>
                                </div>
                            </div>`;
                    }
                }
                if (html) {
                    dropdown.innerHTML = html;
                    dropdown.style.display = 'block';
                    const imgs = dropdown.querySelectorAll('.search-image.loading');
                    imgs.forEach(async img => {
                        const type = img.dataset.type, id = img.dataset.id;
                        try {
                            const url = await getImageUrl(type, id);
                            img.src = url;
                            img.classList.remove('loading');
                        } catch {
                            img.src = 'https://placehold.co/40x40?text=No+Image';
                            img.classList.remove('loading');
                        }
                    });
                    const items = dropdown.querySelectorAll('.search-dropdown-item');
                    items.forEach(item => {
                        item.addEventListener('click', function () {
                            const title = this.dataset.productTitle;
                            isSelectionMade = true;
                            input.value = title;
                            dropdown.style.display = 'none';
                            setTimeout(() => { isSelectionMade = false; }, 100);
                        });
                    });
                } else {
                    dropdown.style.display = 'none';
                }
            } catch {
                dropdown.style.display = 'none';
            }
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        function debounce(fn, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn.apply(this, args), wait);
            };
        }

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
                updateItemLimitNotice();
                return;
            }
            emptyState.style.display = 'none';
            items.forEach((i, idx) => {
                const row = document.createElement('tr');
                row.className = 'fade-in';
                row.innerHTML = `
                    <td class="align-middle">${i.brand}</td>
                    <td class="align-middle">${i.size}</td>
                    <td class="align-middle">${i.quantity}</td>
                    <td class="align-middle text-center">
                        <div class="flex justify-center space-x-3">
                            <i class="fas fa-edit text-gray-500 hover:text-blue-500 cursor-pointer action-icon edit-icon" data-index="${idx}" title="Edit Item"></i>
                            <i class="fas fa-trash-alt text-gray-500 hover:text-red-500 cursor-pointer action-icon delete-icon" data-index="${idx}" title="Remove Item"></i>
                        </div>
                    </td>
                `;
                itemsList.appendChild(row);
            });
            document.querySelectorAll('.edit-icon').forEach(el => {
                el.addEventListener('click', function () {
                    editItem(parseInt(this.getAttribute('data-index')));
                });
            });
            document.querySelectorAll('.delete-icon').forEach(el => {
                el.addEventListener('click', function () {
                    showDeleteModal(parseInt(this.getAttribute('data-index')));
                });
            });
            updateItemLimitNotice();
            updateActivity();
            saveFormData();
        }

        function updateItemLimitNotice() {
            const notice = document.getElementById('item-limit-notice');
            const addBtn = document.getElementById('add-item-btn');
            const emptyBtn = document.getElementById('empty-add-item-btn');
            if (items.length >= MAX_ITEMS) {
                notice.style.display = 'flex';
                addBtn.style.display = 'none';
                emptyBtn.style.display = 'none';
            } else {
                notice.style.display = 'none';
                addBtn.style.display = 'inline-flex';
                emptyBtn.style.display = items.length === 0 ? 'inline-flex' : 'none';
            }
        }

        function showAddItemModal() {
            if (items.length >= MAX_ITEMS) return;
            modalTitle.textContent = 'Add New Item';
            itemForm.reset();
            itemIndex.value = -1;
            isSelectionMade = false;
            itemModal.classList.add('active');
            brandSearchDropdown.style.display = 'none';
        }

        function editItem(idx) {
            const it = items[idx];
            modalTitle.textContent = 'Edit Item';
            itemBrand.value = it.brand;
            itemSize.value = it.size;
            itemQuantity.value = it.quantity;
            itemIndex.value = idx;
            isSelectionMade = false;
            itemModal.classList.add('active');
            brandSearchDropdown.style.display = 'none';
        }

        function showDeleteModal(idx) {
            deleteItemIndex.value = idx;
            deleteModal.classList.add('active');
        }

        function saveItem(e) {
            e.preventDefault();
            const brand = itemBrand.value.trim();
            const size = itemSize.value.trim();
            const qty = parseInt(itemQuantity.value.trim());
            if (!brand || !size || !qty || qty <= 0) return;
            const idx = parseInt(itemIndex.value);
            const obj = { brand, size, quantity: qty };
            if (idx === -1) {
                if (items.length < MAX_ITEMS) items.push(obj);
            } else {
                items[idx] = obj;
            }
            updateItemsDisplay();
            itemModal.classList.remove('active');
            brandSearchDropdown.style.display = 'none';
            isSelectionMade = false;
        }

        function deleteItem() {
            const idx = parseInt(deleteItemIndex.value);
            if (idx >= 0 && idx < items.length) items.splice(idx, 1);
            updateItemsDisplay();
            deleteModal.classList.remove('active');
        }

        function submitRFQ() {
            const payload = { location: selectedLocation, items: items };
            const btn = document.getElementById('submit-btn');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';
            fetch(`${API_BASE}?action=submitRFQ`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                    if (data.success) {
                        let msg = 'Thank you! Your quote request has been received. We will contact you shortly.';
                        if (data.fee_charged > 0) {
                            msg += ` A fee of UGX ${formatCurrency(data.fee_charged)} has been deducted from your wallet. Your remaining balance is UGX ${formatCurrency(data.remaining_balance)}.`;
                        }
                        showSuccessModal(msg);
                        document.getElementById('rfq-form').reset();
                        items = [];
                        selectedLocation = null;
                        document.getElementById('location-display').style.display = 'none';
                        updateItemsDisplay();
                        localStorage.removeItem('rfq_form_data');
                        checkWalletBalance();
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                });
        }

        function showSuccessModal(message) {
            const html = `
                <div id="success-modal" class="modal active">
                    <div class="modal-content p-0">
                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 flex justify-between items-center border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Quote Request Submitted</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                                </div>
                            </div>
                            <div class="text-center mb-6">
                                <p class="text-gray-700">${message}</p>
                            </div>
                            <div class="flex justify-center space-x-3">
                                <button type="button" id="close-success-modal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                                    Close
                                </button>
                                <button type="button" id="view-quotations"
                                    class="btn-primary px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none transition-colors">
                                    <i class="fas fa-eye mr-2"></i> View My Quotations
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', html);
            document.getElementById('close-success-modal').addEventListener('click', () => {
                document.getElementById('success-modal').remove();
            });
            document.getElementById('view-quotations').addEventListener('click', () => {
                window.location.href = BASE_URL + 'account/quotations';
            });
        }

        document.addEventListener('click', updateActivity);
        document.addEventListener('keypress', updateActivity);

        document.getElementById('location').addEventListener('click', openLocationModal);
        document.getElementById('close-location-modal').addEventListener('click', () => {
            document.getElementById('location-modal').classList.remove('active');
        });
        document.getElementById('cancel-location').addEventListener('click', () => {
            document.getElementById('location-modal').classList.remove('active');
        });
        document.getElementById('confirm-location').addEventListener('click', () => {
            updateLocationDisplay();
            document.getElementById('location-modal').classList.remove('active');
            updateActivity();
            saveFormData();
        });

        document.getElementById('map-search-input').addEventListener('input', debounce(e => {
            searchLocation(e.target.value);
        }, 300));

        document.addEventListener('click', e => {
            if (!document.getElementById('map-search-input').contains(e.target) &&
                !document.getElementById('map-search-results').contains(e.target)) {
                document.getElementById('map-search-results').style.display = 'none';
            }
        });

        loadSearchData().then(() => {
            if (searchInitialized && fuseProducts) {
                itemBrand.addEventListener('input', debounce(e => {
                    if (!isSelectionMade) renderProductDropdown(e.target.value, brandSearchDropdown, itemBrand);
                }, 200));
                itemBrand.addEventListener('focus', () => {
                    if (itemBrand.value.trim() && !isSelectionMade) {
                        renderProductDropdown(itemBrand.value, brandSearchDropdown, itemBrand);
                    }
                });
                itemBrand.addEventListener('keydown', () => isSelectionMade = false);
                document.addEventListener('click', e => {
                    if (!itemBrand.contains(e.target) && !brandSearchDropdown.contains(e.target)) {
                        brandSearchDropdown.style.display = 'none';
                    }
                });
            }
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

        document.getElementById('close-confirmation-modal').addEventListener('click', () => {
            document.getElementById('confirmation-modal').classList.remove('active');
        });
        document.getElementById('cancel-confirmation').addEventListener('click', () => {
            document.getElementById('confirmation-modal').classList.remove('active');
        });

        window.addEventListener('click', e => {
            if (e.target === itemModal) {
                itemModal.classList.remove('active'); brandSearchDropdown.style.display = 'none'; isSelectionMade = false;
            }
            if (e.target === deleteModal) deleteModal.classList.remove('active');
            if (e.target === document.getElementById('location-modal')) document.getElementById('location-modal').classList.remove('active');
            if (e.target === document.getElementById('confirmation-modal')) document.getElementById('confirmation-modal').classList.remove('active');
        });

        document.getElementById('reset-form').addEventListener('click', e => {
            e.preventDefault();
            const form = document.getElementById('rfq-form');
            form.reset();
            items = [];
            selectedLocation = null;
            document.getElementById('location-display').style.display = 'none';
            updateItemsDisplay();
            localStorage.removeItem('rfq_form_data');
        });

        const form = document.getElementById('rfq-form');
        form.addEventListener('submit', e => {
            e.preventDefault();
            checkAuthenticationBeforeSubmit().then(auth => {
                if (!auth) return;
                if (walletInfo.noWallet) {
                    showNoWalletModal();
                    return;
                }
                let err = false;
                if (!selectedLocation) err = true;
                if (items.length === 0) err = true;
                if (items.length > MAX_ITEMS) err = true;
                if (err) return;
                if (walletInfo.fee > 0) showConfirmationModal();
                else submitRFQ();
            });
        });

        updateItemsDisplay();
        loadFormData();
        checkWalletBalance();
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>