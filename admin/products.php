<?php
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL);
    exit;
}

$pageTitle = 'Manage Products';
$activeNav = 'products';

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Manage Products</h1>
            <p class="text-sm text-gray-text mt-1">View, edit, and manage products</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="addNewProductBtn"
                class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Product</span>
            </button>
            <a href="product-package"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                <i class="fas fa-box"></i>
                <span>Package Definition</span>
            </a>
            <a href="product-categories"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
        </div>
    </div>

    <!-- Filter/Search Panel -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-auto">
                <input type="text" id="searchProducts" placeholder="Search products..."
                    class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <label for="sortProducts" class="text-sm text-gray-700 whitespace-nowrap">Sort By:</label>
                <select id="sortProducts"
                    class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                    <option value="" selected>Select</option>
                    <option value="latest">Latest</option>
                    <option value="verify">Verified</option>
                    <option value="pending">Pending</option>
                    <option value="usr">User Entries</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div id="filterPanel" class="bg-white rounded-lg shadow-sm border border-gray-100 px-6 py-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="filterCategory"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div>
                <label for="filterFeatured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                <select id="filterFeatured"
                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">All Products</option>
                    <option value="featured">Featured Only</option>
                    <option value="not-featured">Not Featured</option>
                </select>
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button id="resetFilters"
                    class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                    Reset Filters
                </button>
                <button id="applyFilters"
                    class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Products List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Products</h2>
                <p class="text-sm text-gray-text mt-1"><span id="productCount">0</span> products found</p>
            </div>
        </div>

        <div class="p-6">
            <div id="products-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Product cards will be injected here via JS -->
            </div>

            <!-- Pagination placeholder -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span
                        id="totalProducts">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[999] hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span id="loadingMessage" class="text-gray-700 font-medium">Loading...</span>
    </div>
</div>

<!-- Product Modal (Add/Edit) -->
<div id="productModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideProductModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="modalTitle">Add New Product</h3>
            <button onclick="hideProductModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <form id="productForm" class="space-y-6">
                <input type="hidden" id="edit-product-id" value="">

                <!-- Title & Category -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="productTitle" class="block text-sm font-medium text-gray-700 mb-1">Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="productTitle"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter product title">
                    </div>
                    <div>
                        <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category <span
                                class="text-red-500">*</span></label>
                        <select id="productCategory"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <!-- dynamically populate from DB -->
                            <option value="">Select Category</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="productDescription"
                        class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="productDescription" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter product description"></textarea>
                </div>

                <!-- Package Names -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Package Names</label>
                    <div class="space-y-3">
                        <div class="custom-dropdown-container">
                            <div class="relative">
                                <input type="text" id="packageNameSearch"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Click to select package names" autocomplete="off">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div id="packageNameDropdown" class="custom-dropdown-menu hidden">
                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" id="packageNameFilter"
                                            class="w-full px-3 py-1 text-sm rounded-md border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                            placeholder="Search package names...">
                                    </div>
                                    <div id="packageNameOptions" class="max-h-60 overflow-y-auto p-1">
                                        <!-- Package name options will be populated here -->
                                        <div class="p-2 text-center text-gray-500 text-sm">Loading package names...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" id="newPackageName"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter new package name">
                            <button type="button" id="addPackageNameBtn"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                Add
                            </button>
                        </div>
                        <div id="selectedPackageNames" class="flex flex-wrap gap-2 mt-2">
                            <!-- Selected package names will appear here -->
                        </div>
                    </div>
                </div>

                <!-- SEO fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="productMetaTitle" class="block text-sm font-medium text-gray-700 mb-1">Meta
                            Title</label>
                        <input type="text" id="productMetaTitle"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200" placeholder="For SEO...">
                    </div>
                    <div>
                        <label for="productMetaDescription" class="block text-sm font-medium text-gray-700 mb-1">Meta
                            Description</label>
                        <textarea id="productMetaDescription" rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200"
                            placeholder="For SEO..."></textarea>
                    </div>
                </div>
                <div>
                    <label for="productMetaKeywords" class="block text-sm font-medium text-gray-700 mb-1">Meta
                        Keywords</label>
                    <input type="text" id="productMetaKeywords"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200" placeholder="keyword1, keyword2...">
                </div>

                <!-- Images Upload/Preview -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Images (16:9
                        recommended)</label>
                    <button type="button" id="addImageBtn"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-upload mr-1"></i> Upload Images
                    </button>
                    <input type="file" id="imageUploadInput" class="hidden" accept="image/*" multiple>

                    <div id="imagePreviewContainer" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 sortable-images">
                        <!-- Image previews will appear here -->
                    </div>
                </div>

                <!-- Status & Featured -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="productStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="productStatus"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none">
                            <option value="published" selected>Published</option>
                            <option value="pending">Pending</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                        <div class="flex items-center h-10">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="productFeatured" class="sr-only peer">
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                </div>
                                <span class="ml-3 text-sm text-gray-700">Mark as featured</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="hideProductModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="saveProductBtn"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Save Product</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteProductModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Product</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this product? This action cannot be undone.
            </p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div class="text-gray-500">Product:</div>
                    <div class="font-medium text-gray-900" id="delete-product-title"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="confirmDeleteBtn"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<!-- Image Cropper Modal -->
<div id="cropperModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="hideCropperModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Crop Image (16:9)</h3>
            <button onclick="hideCropperModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <div id="image-cropper-container" class="max-h-[60vh] overflow-hidden">
                    <img id="image-to-crop" src="" alt="Image to crop">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCropperModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="cropImageBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Crop &
                Save</button>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired.</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Login Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Notifications -->
<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>
<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<!-- Include required libraries -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
<link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<style>
    .swiper-container {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        text-align: center;
        background: #f8f8f8;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .custom-nav-btn {
        width: 40px !important;
        height: 40px !important;
        background: #2196F3 !important;
        border-radius: 50% !important;
        color: white !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
        opacity: 0.9 !important;
        transition: all 0.3s ease !important;
    }

    .custom-nav-btn:hover {
        background: #1976D2 !important;
        transform: scale(1.05) !important;
    }

    .custom-nav-btn:after {
        font-size: 18px !important;
        font-weight: bold !important;
    }

    .swiper-pagination-bullet {
        width: 8px !important;
        height: 8px !important;
        background: #ccc !important;
        opacity: 0.7 !important;
    }

    .swiper-pagination-bullet-active {
        background: #2196F3 !important;
        opacity: 1 !important;
        width: 10px !important;
        height: 10px !important;
    }

    .sortable-images .image-preview-item {
        cursor: grab;
    }

    .sortable-images .image-preview-item:active {
        cursor: grabbing;
    }

    .cropper-container {
        width: 100%;
        height: 100%;
    }

    /* Custom Dropdown Styles */
    .custom-dropdown-container {
        position: relative;
        width: 100%;
    }

    .custom-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        z-index: 50;
        margin-top: 0.25rem;
    }

    .custom-dropdown-option {
        padding: 0.5rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
    }

    .custom-dropdown-option:hover {
        background-color: #f3f4f6;
    }

    .custom-dropdown-option.selected {
        background-color: #e6f7ff;
    }

    .custom-dropdown-option .checkbox {
        width: 16px;
        height: 16px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        margin-right: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .custom-dropdown-option.selected .checkbox {
        background-color: #2196F3;
        border-color: #2196F3;
    }

    .custom-dropdown-option.selected .checkbox i {
        color: white;
        font-size: 10px;
    }

    .package-tag {
        display: inline-flex;
        align-items: center;
        background-color: #e2e8f0;
        color: #2d3748;
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
        margin: 0.125rem;
        font-size: 0.875rem;
    }

    .package-tag button {
        margin-left: 0.25rem;
        color: #4a5568;
        font-size: 0.75rem;
        border: none;
        background: transparent;
        cursor: pointer;
    }

    .package-tag button:hover {
        color: #e53e3e;
    }

    .no-results {
        padding: 0.75rem;
        text-align: center;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .add-new-option {
        padding: 0.5rem 1rem;
        border-top: 1px solid #e2e8f0;
        color: #2196F3;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .add-new-option:hover {
        background-color: #f3f4f6;
    }

    .add-new-option i {
        margin-right: 0.5rem;
    }
</style>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let productsData = [];
    let currentPage = 1;
    let itemsPerPage = 6;
    let totalPages = 1;
    let cropper = null;
    let currentImageIndex = null;
    let currentImageElement = null;
    let filterData = {
        category: '',
        featured: '',
        search: '',
        sort: ''
    };
    let packageNamesData = [];
    let selectedPackageNames = [];

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Sortable === 'undefined') {
            console.error('Sortable library not loaded. Loading it now...');
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js';
            script.onload = () => {
                console.log('Sortable library loaded successfully');
                initSortable();
            };
            document.head.appendChild(script);
        } else {
            initSortable();
        }

        if (typeof Cropper === 'undefined') {
            console.error('Cropper library not loaded. Loading it now...');
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js';
            document.head.appendChild(script);
        }

        if (typeof Swiper === 'undefined') {
            console.error('Swiper library not loaded. Loading it now...');
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/swiper@8/swiper-bundle.min.js';
            document.head.appendChild(script);
        }

        loadCategories();
        loadProducts();
        loadPackageNames();
        initPackageNameDropdown();

        document.getElementById('addNewProductBtn').addEventListener('click', () => showProductModal(null));
        document.getElementById('saveProductBtn').addEventListener('click', saveProduct);
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
        document.getElementById('cropImageBtn').addEventListener('click', cropAndSaveImage);
        document.getElementById('addPackageNameBtn').addEventListener('click', addNewPackageName);

        document.getElementById('prev-page').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderProducts(productsData);
            }
        });
        document.getElementById('next-page').addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderProducts(productsData);
            }
        });

        document.getElementById('addImageBtn').addEventListener('click', () => {
            document.getElementById('imageUploadInput').click();
        });
        document.getElementById('imageUploadInput').addEventListener('change', handleImageUpload);

        document.getElementById('searchProducts').addEventListener('input', (e) => {
            filterData.search = e.target.value;
            applyFilters();
        });

        document.getElementById('sortProducts').addEventListener('change', (e) => {
            filterData.sort = e.target.value;
            applyFilters();
        });

        document.getElementById('filterCategory').addEventListener('change', (e) => {
            filterData.category = e.target.value;
        });

        document.getElementById('filterFeatured').addEventListener('change', (e) => {
            filterData.featured = e.target.value;
        });

        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        initSortable();
    });

    function initPackageNameDropdown() {
        const searchInput = document.getElementById('packageNameSearch');
        const dropdown = document.getElementById('packageNameDropdown');
        const filterInput = document.getElementById('packageNameFilter');

        // Show dropdown when clicking on the search input
        searchInput.addEventListener('click', function () {
            dropdown.classList.remove('hidden');
            renderPackageNameOptions();
        });

        // Filter package names as user types in the filter input
        filterInput.addEventListener('input', function () {
            renderPackageNameOptions(this.value);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) &&
                !dropdown.contains(e.target) &&
                !e.target.closest('#selectedPackageNames')) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function renderPackageNameOptions(filterText = '') {
        const container = document.getElementById('packageNameOptions');
        container.innerHTML = '';

        if (!packageNamesData || packageNamesData.length === 0) {
            container.innerHTML = '<div class="no-results">No package names available</div>';
            return;
        }

        // Filter out already selected package names and apply text filter
        const availablePackageNames = packageNamesData.filter(pkg => {
            const isSelected = selectedPackageNames.some(selected => selected.id === pkg.id);
            const matchesFilter = !filterText || pkg.package_name.toLowerCase().includes(filterText.toLowerCase());
            return !isSelected && matchesFilter;
        });

        if (availablePackageNames.length === 0) {
            container.innerHTML = '<div class="no-results">No matching package names found</div>';
            return;
        }

        // Render available package names
        availablePackageNames.forEach(pkg => {
            const option = document.createElement('div');
            option.className = 'custom-dropdown-option';
            option.dataset.id = pkg.id;
            option.dataset.name = pkg.package_name;
            option.innerHTML = `
                <div class="checkbox">
                    <i class="fas fa-check"></i>
                </div>
                <span>${escapeHtml(pkg.package_name)}</span>
            `;

            option.addEventListener('click', function () {
                selectPackageName(pkg);
                renderPackageNameOptions(filterText);
            });

            container.appendChild(option);
        });
    }

    function selectPackageName(pkg) {
        // Add to selected package names if not already selected
        if (!selectedPackageNames.some(selected => selected.id === pkg.id)) {
            selectedPackageNames.push({
                id: pkg.id,
                name: pkg.package_name
            });
            renderSelectedPackageNames();

            // Update the search input placeholder
            updatePackageNameSearchPlaceholder();
        }
    }

    function updatePackageNameSearchPlaceholder() {
        const searchInput = document.getElementById('packageNameSearch');
        if (selectedPackageNames.length > 0) {
            searchInput.placeholder = `${selectedPackageNames.length} package name(s) selected`;
        } else {
            searchInput.placeholder = 'Click to select package names';
        }
    }

    function renderSelectedPackageNames() {
        const container = document.getElementById('selectedPackageNames');
        container.innerHTML = '';

        selectedPackageNames.forEach(pkg => {
            const tag = document.createElement('div');
            tag.className = 'package-tag';
            tag.innerHTML = `
                ${escapeHtml(pkg.name)}
                <button type="button" data-id="${pkg.id}">×</button>
            `;

            tag.querySelector('button').addEventListener('click', function () {
                removePackageName(pkg.id);
            });

            container.appendChild(tag);
        });
    }

    function removePackageName(packageNameId) {
        selectedPackageNames = selectedPackageNames.filter(pkg => pkg.id !== packageNameId);
        renderSelectedPackageNames();
        renderPackageNameOptions(document.getElementById('packageNameFilter').value);
        updatePackageNameSearchPlaceholder();
    }

    function addNewPackageName() {
        const input = document.getElementById('newPackageName');
        const packageName = input.value.trim();

        if (!packageName) {
            showErrorNotification('Please enter a package name');
            return;
        }

        showLoading('Adding new package name...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createPackageName`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                package_name: packageName
            })
        })
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Package name created successfully');
                    input.value = '';

                    // Add the new package name to the data
                    const newPackage = {
                        id: data.id,
                        package_name: packageName
                    };
                    packageNamesData.push(newPackage);

                    // Select the newly created package name
                    selectPackageName(newPackage);
                    renderPackageNameOptions(document.getElementById('packageNameFilter').value);
                } else {
                    showErrorNotification(data.message || 'Failed to create package name');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error creating package name:', err);
                showErrorNotification('Failed to create package name');
            });
    }

    function initSortable() {
        const container = document.getElementById('imagePreviewContainer');
        if (container && typeof Sortable !== 'undefined') {
            new Sortable(container, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                onEnd: updateImageOrder
            });
        } else if (container && typeof Sortable === 'undefined') {
            console.error('Sortable library not loaded');
        }
    }

    function updateImageOrder() {
        const container = document.getElementById('imagePreviewContainer');
        const items = container.querySelectorAll('.image-preview-item');
        items.forEach((item, index) => {
            const orderLabel = item.querySelector('.image-order');
            if (orderLabel) {
                orderLabel.textContent = index + 1;
            }
        });
    }

    let categoriesList = [];

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function loadPackageNames() {
        showLoading('Loading package names...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=getPackageNames`)
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    packageNamesData = data.packageNames || [];
                    renderPackageNameOptions();
                } else {
                    showErrorNotification(data.message || 'Failed to load package names');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error loading package names:', err);
                showErrorNotification('Failed to load package names');
            });
    }

    function loadCategories() {
        showLoading('Loading categories...');

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/getCategories`)
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    categoriesList = data.categories || [];
                    populateCategoriesDropdown(categoriesList);
                    populateFilterDropdowns();
                } else {
                    showErrorNotification(data.message || 'Failed to load categories');
                }
                hideLoading();
            })
            .catch(err => {
                hideLoading();
                console.error('Error loading categories:', err);
            });
    }

    function populateCategoriesDropdown(catList) {
        const dropdown = document.getElementById('productCategory');
        dropdown.innerHTML = '<option value="">Select Category</option>';
        catList.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            dropdown.appendChild(opt);
        });
    }

    function populateFilterDropdowns() {
        // Populate category filter
        const catFilter = document.getElementById('filterCategory');
        catFilter.innerHTML = '<option value="">All Categories</option>';
        categoriesList.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            catFilter.appendChild(opt);
        });
    }

    function loadProducts() {
        showLoading('Loading products...');

        fetch(`${BASE_URL}admin/fetch/manageProducts/getProducts`)
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    productsData = data.products || [];
                    totalPages = Math.ceil(productsData.length / itemsPerPage);
                    currentPage = 1;
                    renderPagination();
                    renderProducts(productsData);
                } else {
                    showErrorNotification(data.message || 'Failed to load products');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error loading products:', err);
                showErrorNotification('Failed to load products.');
            });
    }

    function renderPagination() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        const pagNums = document.getElementById('pagination-numbers');
        pagNums.innerHTML = '';
        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                pagNums.appendChild(createPagButton(i));
            }
        } else {
            pagNums.appendChild(createPagButton(1));
            if (currentPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.classList.add('px-2');
                pagNums.appendChild(ellipsis);
            }
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                pagNums.appendChild(createPagButton(i));
            }
            if (currentPage < totalPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.classList.add('px-2');
                pagNums.appendChild(ellipsis);
            }
            pagNums.appendChild(createPagButton(totalPages));
        }
    }

    function createPagButton(page) {
        const btn = document.createElement('button');
        btn.className = (page === currentPage) ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        btn.textContent = page;
        btn.addEventListener('click', () => {
            currentPage = page;
            renderPagination();
            renderProducts(productsData);
        });
        return btn;
    }

    function renderProducts(list) {
        const container = document.getElementById('products-container');
        container.innerHTML = '';

        // Apply filters
        const filteredList = filterProducts(list);
        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, filteredList.length);

        document.getElementById('productCount').textContent = filteredList.length;
        document.getElementById('showingStart').textContent = filteredList.length > 0 ? start + 1 : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalProducts').textContent = filteredList.length;

        if (filteredList.length === 0) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-500">No products found</div>';
            return;
        }

        const paginated = filteredList.slice(start, end);
        paginated.forEach(prod => {
            container.appendChild(createProductCard(prod));
        });

        initProductSwipers();
    }

    function filterProducts(products) {
        return products.filter(prod => {
            // Search filter
            if (filterData.search &&
                !prod.title.toLowerCase().includes(filterData.search.toLowerCase()) &&
                !prod.description.toLowerCase().includes(filterData.search.toLowerCase())) {
                return false;
            }
            // Category filter
            if (filterData.category && prod.category !== filterData.category) {
                return false;
            }
            // Featured filter
            if (filterData.featured === 'featured' && !prod.featured) {
                return false;
            }
            if (filterData.featured === 'not-featured' && prod.featured) {
                return false;
            }
            return true;
        }).sort((a, b) => {
            switch (filterData.sort) {
                case 'latest':
                    return new Date(b.created_at) - new Date(a.created_at);
                case 'verify':
                    return b.status === 'published' ? 1 : -1;
                case 'pending':
                    return b.status === 'pending' ? 1 : -1;
                default:
                    return 0;
            }
        });
    }

    function applyFilters() {
        currentPage = 1;
        renderPagination();
        renderProducts(productsData);
    }

    function resetFilters() {
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterFeatured').value = '';
        document.getElementById('searchProducts').value = '';
        document.getElementById('sortProducts').value = '';

        filterData = {
            category: '',
            featured: '',
            search: '',
            sort: ''
        };

        applyFilters();
    }

    // Initialize Swiper for product images
    function initProductSwipers() {
        document.querySelectorAll('.swiper-container').forEach(container => {
            new Swiper(container, {
                loop: true,
                pagination: {
                    el: container.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: container.querySelector('.swiper-button-next'),
                    prevEl: container.querySelector('.swiper-button-prev')
                }
            });
        });
    }

    function createProductCard(prod) {
        const card = document.createElement('div');
        card.className = 'product-item bg-white/70 rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col';

        // Main image or placeholder
        let mainImage = 'https://placehold.co/600x400?text=No+Image';
        if (prod.images && prod.images.length > 0) {
            mainImage = prod.images[0];
        }

        // Image slider HTML
        let sliderHtml = `
        <div class="swiper-container h-full">
            <div class="swiper-wrapper">
    `;

        if (prod.images && prod.images.length > 0) {
            prod.images.forEach(img => {
                sliderHtml += `
                <div class="swiper-slide">
                    <img src="${img}" alt="${escapeHtml(prod.title)}" class="w-full h-64 object-cover">
                </div>
            `;
            });
        } else {
            sliderHtml += `
            <div class="swiper-slide">
                <img src="https://placehold.co/600x400?text=No+Image" alt="No Image" class="w-full h-64 object-cover">
            </div>
        `;
        }

        sliderHtml += `
            </div>
            <div class="swiper-pagination"></div>
        </div>
    `;

        // Package names display
        let packageNamesHtml = '';
        if (prod.package_names && prod.package_names.length > 0) {
            packageNamesHtml = `
            <div class="flex flex-wrap gap-1 mt-2">
                ${prod.package_names.map(pkg => `
                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                        ${escapeHtml(pkg.package_name)}
                    </span>
                `).join('')}
            </div>
        `;
        }

        card.innerHTML = `
            <div class="relative bg-gray-100 h-64 shrink-0">
                <div class="product-image-slider h-full">
                    ${sliderHtml}
                </div>
                <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center ${prod.featured ? 'text-red-500' : 'text-gray-400'} hover:text-primary transition-colors z-10 toggle-featured" data-id="${prod.id}" data-featured="${prod.featured ? 'true' : 'false'}">
                    <i class="${prod.featured ? 'fas' : 'far'} fa-heart text-lg"></i>
                </button>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                    <div class="text-white font-medium truncate">${escapeHtml(prod.title)}</div>
                    <div class="text-white/80 text-sm truncate">${escapeHtml(prod.category_name || '')}</div>
                </div>
            </div>

            <div class="flex flex-col justify-between flex-1 p-4"> <!-- Flex wrapper for content and buttons -->
                <div>
                <h3 class="text-lg font-semibold mb-1 truncate">${escapeHtml(prod.title)}</h3>

                <div class="flex items-center text-gray-500 mb-2">
                    <i class="fas fa-tag mr-2"></i>
                    <span>${escapeHtml(prod.category_name || '')}</span>
                </div>

                <div class="text-sm text-gray-600 mb-3 line-clamp-2">${escapeHtml(prod.description || 'No description available')}</div>

                ${packageNamesHtml}
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button class="btn-edit w-10 h-10 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center justify-center" data-id="${prod.id}" title="Edit Product">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-delete w-10 h-10 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center" data-id="${prod.id}" title="Delete Product">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `;

        // Wire up events
        card.querySelector('.btn-edit').addEventListener('click', () => {
            showProductModal(prod.id);
        });
        card.querySelector('.btn-delete').addEventListener('click', () => {
            showDeleteModal(prod.id);
        });
        card.querySelector('.toggle-featured').addEventListener('click', (e) => {
            const button = e.currentTarget;
            const productId = button.getAttribute('data-id');
            const featured = button.getAttribute('data-featured') === 'true';
            toggleProductFeatured(productId, !featured);
        });

        return card;
    }

    function toggleProductFeatured(productId, featured) {
        showLoading(featured ? 'Marking as featured...' : 'Removing from featured...');

        fetch(`${BASE_URL}admin/fetch/manageProducts/toggleFeatured`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: productId,
                featured: featured
            })
        })
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    const product = productsData.find(p => p.id === productId);
                    if (product) {
                        product.featured = featured;
                    }

                    const button = document.querySelector(`.toggle-featured[data-id="${productId}"]`);
                    if (button) {
                        button.setAttribute('data-featured', featured ? 'true' : 'false');
                        if (featured) {
                            button.classList.remove('text-gray-400');
                            button.classList.add('text-red-500');
                            button.querySelector('i').classList.remove('far');
                            button.querySelector('i').classList.add('fas');
                        } else {
                            button.classList.add('text-gray-400');
                            button.classList.remove('text-red-500');
                            button.querySelector('i').classList.add('far');
                            button.querySelector('i').classList.remove('fas');
                        }
                    }
                    showSuccessNotification(featured ? 'Product marked as featured' : 'Product removed from featured');
                } else {
                    showErrorNotification(data.message || 'Failed to update featured status');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error toggling featured status:', err);
                showErrorNotification('Failed to update featured status');
            });
    }

    function showProductModal(productId) {
        resetProductForm();

        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modalTitle');

        if (productId) {
            modalTitle.textContent = 'Edit Product';
            showLoading('Loading product details...');

            fetch(`${BASE_URL}admin/fetch/manageProducts/getProduct?id=${productId}`)
                .then(res => {
                    if (res.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    return res.json();
                })
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        populateProductForm(data.data);
                        modal.classList.remove('hidden');
                        initSortable();
                    } else {
                        showErrorNotification(data.message || 'Failed to load product');
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error loading product:', err);
                    showErrorNotification('Failed to load product details.');
                });
        } else {
            modalTitle.textContent = 'Add New Product';
            modal.classList.remove('hidden');
            initSortable();
        }
    }

    function hideProductModal() {
        document.getElementById('productModal').classList.add('hidden');
    }

    function resetProductForm() {
        document.getElementById('productForm').reset();
        document.getElementById('edit-product-id').value = '';
        document.getElementById('imagePreviewContainer').innerHTML = '';
        document.getElementById('selectedPackageNames').innerHTML = '';
        document.getElementById('packageNameFilter').value = '';
        document.getElementById('packageNameSearch').placeholder = 'Click to select package names';

        selectedPackageNames = [];
    }

    function populateProductForm(prod) {
        document.getElementById('edit-product-id').value = prod.id;
        document.getElementById('productTitle').value = prod.title;
        if (prod.category) {
            document.getElementById('productCategory').value = prod.category;
        } else {
            console.warn('No category found for product:', prod);
        }
        document.getElementById('productDescription').value = prod.description || '';
        document.getElementById('productMetaTitle').value = prod.meta_title || '';
        document.getElementById('productMetaDescription').value = prod.meta_description || '';
        document.getElementById('productMetaKeywords').value = prod.meta_keywords || '';
        document.getElementById('productStatus').value = prod.status;
        document.getElementById('productFeatured').checked = (prod.featured == 1);

        // Set package names
        if (prod.package_names && prod.package_names.length > 0) {
            selectedPackageNames = prod.package_names.map(pkg => ({
                id: pkg.id,
                name: pkg.package_name
            }));
            renderSelectedPackageNames();
            updatePackageNameSearchPlaceholder();
        }

        // images
        if (prod.images && prod.images.length > 0) {
            prod.images.forEach((url, index) => {
                addImagePreview(url, index + 1);
            });
        }
    }

    function saveProduct() {
        const productId = document.getElementById('edit-product-id').value;
        const title = document.getElementById('productTitle').value.trim();
        const category = document.getElementById('productCategory').value.trim();
        const desc = document.getElementById('productDescription').value.trim();
        const metaTitle = document.getElementById('productMetaTitle').value.trim();
        const metaDesc = document.getElementById('productMetaDescription').value.trim();
        const keywords = document.getElementById('productMetaKeywords').value.trim();
        const status = document.getElementById('productStatus').value;
        const featured = document.getElementById('productFeatured').checked;

        // Get selected package names
        const packageNames = selectedPackageNames.map(pkg => pkg.id);

        if (!title || !category) {
            showErrorNotification('Title and Category are required');
            return;
        }

        showLoading(productId ? 'Updating product...' : 'Creating product...');

        // Gather images from preview container
        const imageDivs = document.querySelectorAll('#imagePreviewContainer .image-preview-item');
        const images = [];
        const tempImages = [];

        imageDivs.forEach(div => {
            const img = div.querySelector('img');
            const imgSrc = img.src;
            const tmpPath = div.getAttribute('data-temp-path');

            if (tmpPath) {
                tempImages.push({
                    temp_path: tmpPath
                });
            } else if (imgSrc.startsWith('data:')) {
                // If there's a base64 image, we could upload it, but the script here
                // queues it for upload. We'll handle it the same as normal: pass the data along.
            } else if (!imgSrc.includes('placehold.co')) {
                images.push(imgSrc);
            }
        });

        const payload = {
            id: productId,
            title: title,
            category_id: category,
            description: desc,
            meta_title: metaTitle,
            meta_description: metaDesc,
            meta_keywords: keywords,
            status: status,
            featured: featured,
            temp_images: tempImages,
            existing_images: images,
            update_images: imageDivs.length > 0,
            package_names: packageNames
        };

        const endpoint = productId ? 'updateProduct' : 'createProduct';

        fetch(`${BASE_URL}admin/fetch/manageProducts/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Saved successfully');
                    hideProductModal();
                    loadProducts();
                } else {
                    showErrorNotification(data.message || 'Failed to save product');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error saving product:', err);
                showErrorNotification('Failed to save product. Check console.');
            });
    }

    function showDeleteModal(productId) {
        const product = productsData.find(p => p.id === productId);
        if (product) {
            document.getElementById('delete-product-title').textContent = product.title;
            document.getElementById('deleteProductModal').classList.remove('hidden');
        }
        window.deleteProductId = productId;
    }

    function hideDeleteModal() {
        document.getElementById('deleteProductModal').classList.add('hidden');
    }

    function confirmDelete() {
        if (!window.deleteProductId) return;

        showLoading('Deleting product...');
        hideDeleteModal();

        fetch(`${BASE_URL}admin/fetch/manageProducts/deleteProduct`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: window.deleteProductId
            })
        })
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Product deleted');
                    loadProducts();
                } else {
                    showErrorNotification(data.message || 'Failed to delete product');
                }
            })
            .catch(err => {
                hideLoading();
                console.error('Error deleting product:', err);
                showErrorNotification('Could not delete product.');
            });
    }

    function handleImageUpload(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();
            reader.onload = function (ev) {
                showCropperModal(ev.target.result);
            };
            reader.readAsDataURL(file);
        }
        e.target.value = '';
    }

    function showCropperModal(imageUrl) {
        const modal = document.getElementById('cropperModal');
        const imageElement = document.getElementById('image-to-crop');

        imageElement.src = imageUrl;
        modal.classList.remove('hidden');

        imageElement.onload = function () {
            if (typeof Cropper === 'undefined') {
                console.error('Cropper library not loaded');
                return;
            }
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(imageElement, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 1,
                zoomable: true,
                scalable: true,
                movable: true,
                guides: true
            });
        };
    }

    function hideCropperModal() {
        const modal = document.getElementById('cropperModal');
        modal.classList.add('hidden');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function cropAndSaveImage() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 1600,
            height: 900,
            minWidth: 800,
            minHeight: 450,
            maxWidth: 1920,
            maxHeight: 1080,
            fillColor: '#fff'
        });
        if (!canvas) return;

        const croppedImageUrl = canvas.toDataURL('image/jpeg');
        const container = document.getElementById('imagePreviewContainer');
        const order = container.children.length + 1;

        addImagePreview(croppedImageUrl, order);
        hideCropperModal();

        canvas.toBlob(blob => {
            const formData = new FormData();
            formData.append('image', blob, 'cropped-image.jpg');

            showLoading('Uploading image...');

            fetch(`${BASE_URL}admin/fetch/manageProducts/uploadImage`, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        const lastImage = container.lastElementChild;
                        if (lastImage) {
                            lastImage.setAttribute('data-temp-path', data.temp_path);
                        }
                    } else {
                        showErrorNotification(data.message || 'Failed to upload image');
                    }
                })
                .catch(err => {
                    hideLoading();
                    console.error('Error uploading image:', err);
                    showErrorNotification('Error uploading image');
                });
        }, 'image/jpeg', 0.9);
    }

    function addImagePreview(url, order) {
        const container = document.getElementById('imagePreviewContainer');
        const div = document.createElement('div');
        div.className = 'image-preview-item relative border border-gray-200 rounded-lg overflow-hidden';

        div.innerHTML = `
            <img src="${url}" alt="product image" class="w-full h-32 object-cover">
            <div class="absolute top-0 right-0 p-2 flex gap-2">
                <button type="button" class="bg-primary text-white rounded-full w-6 h-6 flex items-center justify-center edit-image-btn">
                    <i class="fas fa-crop-alt text-xs"></i>
                </button>
                <button type="button" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center remove-image-btn">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-1 text-center">
                <span class="image-order">${order}</span>
            </div>
        `;
        container.appendChild(div);

        div.querySelector('.edit-image-btn').addEventListener('click', () => {
            const img = div.querySelector('img');
            showCropperModal(img.src);
            currentImageElement = img;
        });
        div.querySelector('.remove-image-btn').addEventListener('click', () => {
            div.remove();
            updateImageOrder();
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showSessionExpiredModal() {
        document.getElementById('sessionExpiredModal').classList.remove('hidden');
    }

    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    function showSuccessNotification(message) {
        const notif = document.getElementById('successNotification');
        const msgEl = document.getElementById('successMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(() => notif.classList.add('hidden'), 3000);
    }

    function showErrorNotification(message) {
        const notif = document.getElementById('errorNotification');
        const msgEl = document.getElementById('errorMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(() => notif.classList.add('hidden'), 5000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>