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
            <button id="addNewProductBtn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i>
                <span>Add Product</span>
            </button>
            <a href="product-package" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                <i class="fas fa-box"></i>
                <span>Package Definition</span>
            </a>
            <a href="product-categories" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
        </div>
    </div>

    <!-- Filter/Search Panel -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-auto">
                <input type="text" id="searchProducts" placeholder="Search products..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <label for="sortProducts" class="text-sm text-gray-700 whitespace-nowrap">Sort By:</label>
                <select id="sortProducts" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="filterCategory" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">All Categories</option>
                </select>
            </div>
            <div>
                <label for="filterUnitOfMeasure" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                <select id="filterUnitOfMeasure" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">All Units of Measure</option>
                </select>
            </div>
            <div>
                <label for="filterFeatured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                <select id="filterFeatured" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">All Products</option>
                    <option value="featured">Featured Only</option>
                    <option value="not-featured">Not Featured</option>
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button id="resetFilters" class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                    Reset Filters
                </button>
                <button id="applyFilters" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
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
                    Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalProducts">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
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
                        <label for="productTitle" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" id="productTitle" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter product title">
                    </div>
                    <div>
                        <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select id="productCategory" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <!-- dynamically populate from DB -->
                            <option value="">Select Category</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="productDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="productDescription" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter product description"></textarea>
                </div>

                <!-- SEO fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="productMetaTitle" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                        <input type="text" id="productMetaTitle" class="w-full px-3 py-2 rounded-lg border border-gray-200" placeholder="For SEO...">
                    </div>
                    <div>
                        <label for="productMetaDescription" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea id="productMetaDescription" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200" placeholder="For SEO..."></textarea>
                    </div>
                </div>
                <div>
                    <label for="productMetaKeywords" class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                    <input type="text" id="productMetaKeywords" class="w-full px-3 py-2 rounded-lg border border-gray-200" placeholder="keyword1, keyword2...">
                </div>

                <!-- Images Upload/Preview -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Images (16:9 recommended)</label>
                    <button type="button" id="addImageBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-upload mr-1"></i> Upload Images
                    </button>
                    <input type="file" id="imageUploadInput" class="hidden" accept="image/*" multiple>

                    <div id="imagePreviewContainer" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 sortable-images">
                        <!-- Image previews will appear here -->
                    </div>
                </div>

                <!-- Units of Measure & Pricing -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit of Measure & Pricing</label>
                    <div id="unitOfMeasurePricingContainer" class="space-y-3">
                        <!-- Each row is (Unit of Measure Select) + (Price) + remove button -->
                    </div>
                    <button type="button" id="addUnitOfMeasureBtn" class="mt-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        <i class="fas fa-plus mr-1"></i> Add Unit of Measure
                    </button>
                </div>

                <!-- Status & Featured -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="productStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="productStatus" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none">
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
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-sm text-gray-700">Mark as featured</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" onclick="hideProductModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="button" id="saveProductBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Save Product</button>
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
            <p class="text-gray-600 mb-4">Are you sure you want to delete this product? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div class="text-gray-500">Product:</div>
                    <div class="font-medium text-gray-900" id="delete-product-title"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
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
                    <img id="image-to-crop" src="/placeholder.svg" alt="Image to crop">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCropperModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="cropImageBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Crop & Save</button>
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
                <button onclick="redirectToLogin()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Login Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Notifications -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>
<div id="errorNotification" class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
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
        unitOfMeasure: '',
        featured: '',
        search: '',
        sort: ''
    };

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
        loadUnitsOfMeasure();
        loadProducts();

        document.getElementById('addNewProductBtn').addEventListener('click', () => showProductModal(null));
        document.getElementById('saveProductBtn').addEventListener('click', saveProduct);
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
        document.getElementById('cropImageBtn').addEventListener('click', cropAndSaveImage);

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

        document.getElementById('addUnitOfMeasureBtn').addEventListener('click', () => addUnitOfMeasureRow());

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

        document.getElementById('filterUnitOfMeasure').addEventListener('change', (e) => {
            filterData.unitOfMeasure = e.target.value;
        });

        document.getElementById('filterFeatured').addEventListener('change', (e) => {
            filterData.featured = e.target.value;
        });

        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        initSortable();
    });

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

    function loadCategories() {
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
            })
            .catch(err => console.error('Error loading categories:', err));
    }

    function populateCategoriesDropdown(catList) {
        const dropdown = document.getElementById('productCategory');
        dropdown.innerHTML = '<option value="">Select Category</option>';
        catList.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.uuid_id;
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
            opt.value = cat.uuid_id;
            opt.textContent = cat.name;
            catFilter.appendChild(opt);
        });

        // Populate unit of measure filter - ensure unitsOfMeasureList is available
        if (unitsOfMeasureList && unitsOfMeasureList.length > 0) {
            const uomFilter = document.getElementById('filterUnitOfMeasure');
            uomFilter.innerHTML = '<option value="">All Units of Measure</option>';
            unitsOfMeasureList.forEach(uom => {
                const opt = document.createElement('option');
                opt.value = uom.uuid_id;
                opt.textContent = uom.unit_of_measure;
                uomFilter.appendChild(opt);
            });
        }
    }

    let unitsOfMeasureList = [];

    function loadUnitsOfMeasure() {
        fetch(`${BASE_URL}admin/fetch/manageProductPackages/getUnitsOfMeasure`)
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    unitsOfMeasureList = data.unitsOfMeasure || [];
                    populateFilterDropdowns(); // Ensure filter is populated after units of measure are loaded
                } else {
                    showErrorNotification(data.message || 'Failed to load units of measure');
                }
            })
            .catch(err => {
                console.error('Error loading units of measure:', err);
                showErrorNotification('Error loading units of measure. Please try again.');
            });
    }

    function loadProducts() {
        fetch(`${BASE_URL}admin/fetch/manageProducts/getProducts`)
            .then(res => {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return res.json();
            })
            .then(data => {
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
                console.error('Error loading products:', err);
                showErrorNotification('Failed to load products.');
            });
    }

    function renderPagination() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        // Pagination numbers
        const pagNums = document.getElementById('pagination-numbers');
        pagNums.innerHTML = '';
        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                pagNums.appendChild(createPagButton(i));
            }
        } else {
            // Render condensed with ellipsis
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

        // Initialize Swiper for each product card
        initProductSwipers();
    }

    function filterProducts(products) {
        return products.filter(prod => {
            // Search filter
            if (filterData.search && !prod.title.toLowerCase().includes(filterData.search.toLowerCase()) &&
                !prod.description.toLowerCase().includes(filterData.search.toLowerCase())) {
                return false;
            }

            // Category filter
            if (filterData.category && prod.uuid_category !== filterData.category) {
                return false;
            }

            // Unit of Measure filter
            if (filterData.unitOfMeasure && !prod.units_of_measure.some(uom => uom.unit_of_measure_id === filterData.unitOfMeasure)) {
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
            // Sort products
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
        document.getElementById('filterUnitOfMeasure').value = '';
        document.getElementById('filterFeatured').value = '';
        document.getElementById('searchProducts').value = '';
        document.getElementById('sortProducts').value = '';

        filterData = {
            category: '',
            unitOfMeasure: '',
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

    // Creates a product "card" or tile
    function createProductCard(prod) {
        const card = document.createElement('div');
        card.className = 'product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow';

        // Get main image or use placeholder
        let mainImage = 'https://placehold.co/600x400?text=No+Image';
        if (prod.images && prod.images.length > 0) {
            mainImage = prod.images[0];
        }

        // Create image slider HTML
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
            <div class="swiper-button-prev custom-nav-btn"></div>
            <div class="swiper-button-next custom-nav-btn"></div>
        </div>
    `;

        // Create unit of measure info HTML
        let unitsOfMeasureHtml = '';
        if (prod.units_of_measure && prod.units_of_measure.length > 0) {
            unitsOfMeasureHtml = `<div class="mt-2 space-y-1">`;
            prod.units_of_measure.forEach(uom => {
                const price = Number(uom.price).toLocaleString();
                unitsOfMeasureHtml += `
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">${uom.unit_of_measure}</span>
                    <span class="font-medium text-primary">USh ${price}</span>
                </div>
            `;
            });
            unitsOfMeasureHtml += `</div>`;
        } else {
            unitsOfMeasureHtml = `<div class="mt-2 text-sm text-gray-500">No pricing available</div>`;
        }

        card.innerHTML = `
        <div class="relative bg-gray-100 h-64">
            <div class="product-image-slider h-full">
                ${sliderHtml}
            </div>
            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center ${prod.featured ? 'text-red-500' : 'text-gray-400'} hover:text-primary transition-colors z-10 toggle-featured" data-id="${prod.uuid_id}" data-featured="${prod.featured ? 'true' : 'false'}">
                <i class="${prod.featured ? 'fas' : 'far'} fa-heart text-lg"></i>
            </button>
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                <div class="text-white font-medium truncate">${escapeHtml(prod.title)}</div>
                <div class="text-white/80 text-sm truncate">${escapeHtml(prod.category_name || '')}</div>
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-lg font-semibold mb-1 truncate">${escapeHtml(prod.title)}</h3>
            
            <div class="flex items-center text-gray-500 mb-2">
                <i class="fas fa-tag mr-2"></i>
                <span>${escapeHtml(prod.category_name || '')}</span>
            </div>
            
            <div class="text-sm text-gray-600 mb-3 line-clamp-2">${escapeHtml(prod.description || 'No description available')}</div>
            
            <div class="border-t border-gray-100 pt-2">
                <div class="text-sm font-medium text-gray-700 mb-1">Pricing:</div>
                ${unitsOfMeasureHtml}
            </div>
            
            <div class="flex justify-end gap-2 mt-3">
                <button class="btn-edit w-10 h-10 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center justify-center" data-id="${prod.uuid_id}" title="Edit Product">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-delete w-10 h-10 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center" data-id="${prod.uuid_id}" title="Delete Product">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `;

        // Wire up events
        card.querySelector('.btn-edit').addEventListener('click', () => {
            showProductModal(prod.uuid_id);
        });

        card.querySelector('.btn-delete').addEventListener('click', () => {
            showDeleteModal(prod.uuid_id);
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
                if (data.success) {
                    // Update local data
                    const product = productsData.find(p => p.uuid_id === productId);
                    if (product) {
                        product.featured = featured;
                    }

                    // Update UI
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
                console.error('Error toggling featured status:', err);
                showErrorNotification('Failed to update featured status');
            });
    }

    function showProductModal(productId) {
        resetProductForm();

        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modalTitle');

        if (productId) {
            // Editing existing product: fetch details
            modalTitle.textContent = 'Edit Product';
            fetch(`${BASE_URL}admin/fetch/manageProducts/getProduct&id=${productId}`)
                .then(res => {
                    if (res.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        populateProductForm(data.data);
                        modal.classList.remove('hidden');
                        initSortable();
                    } else {
                        showErrorNotification(data.message || 'Failed to load product');
                    }
                })
                .catch(err => {
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

        // Clear out the image preview container
        document.getElementById('imagePreviewContainer').innerHTML = '';
        // Clear units of measure
        document.getElementById('unitOfMeasurePricingContainer').innerHTML = '';
        // Add one empty unit of measure row
        addUnitOfMeasureRow();
    }

    function populateProductForm(prod) {
        document.getElementById('edit-product-id').value = prod.uuid_id;
        document.getElementById('productTitle').value = prod.title;
        document.getElementById('productCategory').value = prod.uuid_category;
        document.getElementById('productDescription').value = prod.description || '';
        document.getElementById('productMetaTitle').value = prod.meta_title || '';
        document.getElementById('productMetaDescription').value = prod.meta_description || '';
        document.getElementById('productMetaKeywords').value = prod.meta_keywords || '';
        document.getElementById('productStatus').value = prod.status;
        document.getElementById('productFeatured').checked = (prod.featured == 1);

        // units of measure
        if (prod.units_of_measure && prod.units_of_measure.length > 0) {
            prod.units_of_measure.forEach(uom => {
                addUnitOfMeasureRow(uom.unit_of_measure_id, uom.price);
            });
        } else {
            addUnitOfMeasureRow();
        }

        // images
        if (prod.images && prod.images.length > 0) {
            prod.images.forEach((url, index) => {
                addImagePreview(url, index + 1);
            });
        }
    }

    function saveProduct() {
        // Gather form data
        const productId = document.getElementById('edit-product-id').value;
        const title = document.getElementById('productTitle').value.trim();
        const category = document.getElementById('productCategory').value.trim();
        const desc = document.getElementById('productDescription').value.trim();
        const metaTitle = document.getElementById('productMetaTitle').value.trim();
        const metaDesc = document.getElementById('productMetaDescription').value.trim();
        const keywords = document.getElementById('productMetaKeywords').value.trim();
        const status = document.getElementById('productStatus').value;
        const featured = document.getElementById('productFeatured').checked;

        if (!title || !category) {
            showErrorNotification('Title and Category are required');
            return;
        }

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
                // This is a cropped image that needs to be uploaded
                const blob = dataURLtoBlob(imgSrc);
                const formData = new FormData();
                formData.append('image', blob, 'cropped-image.jpg');

                // Upload the cropped image
                fetch(`${BASE_URL}admin/fetch/manageProducts/uploadImage`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            tempImages.push({
                                temp_path: data.temp_path
                            });
                        }
                    })
                    .catch(err => console.error('Error uploading cropped image:', err));
            } else if (!imgSrc.includes('placehold.co')) {
                // This is an existing image URL
                images.push(imgSrc);
            }
        });

        // Gather units of measure
        const uomRows = document.querySelectorAll('#unitOfMeasurePricingContainer .uom-row');
        const unitsOfMeasure = [];
        uomRows.forEach(row => {
            const uomSel = row.querySelector('.unit-of-measure-select');
            const uomPrice = row.querySelector('.unit-of-measure-price');
            if (uomSel.value && uomPrice.value) {
                unitsOfMeasure.push({
                    unit_of_measure_id: uomSel.value,
                    price: parseFloat(uomPrice.value)
                });
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
            units_of_measure: unitsOfMeasure,
            temp_images: tempImages,
            existing_images: images,
            update_images: imageDivs.length > 0 // Only update images if there are any in the form
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
                if (data.success) {
                    showSuccessNotification(data.message || 'Saved successfully');
                    hideProductModal();
                    loadProducts();
                } else {
                    showErrorNotification(data.message || 'Failed to save product');
                }
            })
            .catch(err => {
                console.error('Error saving product:', err);
                showErrorNotification('Failed to save product. Check console.');
            });
    }

    // Convert data URL to Blob for uploading
    function dataURLtoBlob(dataURL) {
        const arr = dataURL.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {
            type: mime
        });
    }

    // Deletion
    let deleteProductId = null;

    function showDeleteModal(productId) {
        deleteProductId = productId;
        const product = productsData.find(p => p.uuid_id === productId);

        if (product) {
            document.getElementById('delete-product-title').textContent = product.title;
            document.getElementById('deleteProductModal').classList.remove('hidden');
        }
    }

    function hideDeleteModal() {
        document.getElementById('deleteProductModal').classList.add('hidden');
    }

    function confirmDelete() {
        if (!deleteProductId) return;

        fetch(`${BASE_URL}admin/fetch/manageProducts/deleteProduct`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: deleteProductId
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
                if (data.success) {
                    showSuccessNotification(data.message || 'Product deleted');
                    hideDeleteModal();
                    loadProducts();
                } else {
                    showErrorNotification(data.message || 'Failed to delete product');
                }
            })
            .catch(err => {
                console.error('Error deleting product:', err);
                showErrorNotification('Could not delete product.');
            });
    }

    // Images Handling
    function handleImageUpload(e) {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const reader = new FileReader();

            reader.onload = function(e) {
                showCropperModal(e.target.result);
            };

            reader.readAsDataURL(file);
        }

        // Reset input
        e.target.value = '';
    }

    function showCropperModal(imageUrl) {
        const modal = document.getElementById('cropperModal');
        const imageElement = document.getElementById('image-to-crop');

        imageElement.src = imageUrl;
        modal.classList.remove('hidden');

        // Initialize cropper after image is loaded
        imageElement.onload = function() {
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

        // Add the cropped image to the preview
        addImagePreview(croppedImageUrl, order);

        // Hide the cropper modal
        hideCropperModal();

        // Upload the cropped image to the server
        canvas.toBlob(blob => {
            const formData = new FormData();
            formData.append('image', blob, 'cropped-image.jpg');

            fetch(`${BASE_URL}admin/fetch/manageProducts/uploadImage`, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update the image preview with the temp path
                        const lastImage = container.lastElementChild;
                        if (lastImage) {
                            lastImage.setAttribute('data-temp-path', data.temp_path);
                        }
                    } else {
                        showErrorNotification(data.message || 'Failed to upload image');
                    }
                })
                .catch(err => {
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

        // Add event listeners
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

    // Units of Measure & Pricing UI
    function addUnitOfMeasureRow(selectedUomId = null, priceVal = null) {
        const container = document.getElementById('unitOfMeasurePricingContainer');
        const row = document.createElement('div');
        row.className = 'uom-row grid grid-cols-1 md:grid-cols-3 gap-4 items-center';

        // Build unit of measure dropdown
        let uomOptions = '<option value="">Select Unit of Measure</option>';
        unitsOfMeasureList.forEach(uom => {
            uomOptions += `<option value="${uom.uuid_id}">${uom.unit_of_measure}</option>`;
        });

        row.innerHTML = `
        <div>
            <label class="block text-xs text-gray-500 mb-1">Unit of Measure</label>
            <select class="unit-of-measure-select w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none">
                ${uomOptions}
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Price (UGX)</label>
            <input type="number" class="unit-of-measure-price w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none" placeholder="0">
        </div>
        <div class="flex items-end">
            <button type="button" class="remove-unit-of-measure px-3 py-2 text-red-500 hover:text-red-700">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;
        container.appendChild(row);

        if (selectedUomId) {
            row.querySelector('.unit-of-measure-select').value = selectedUomId;
        }
        if (priceVal) {
            row.querySelector('.unit-of-measure-price').value = priceVal;
        }

        row.querySelector('.remove-unit-of-measure').addEventListener('click', () => {
            row.remove();
        });
    }

    // Helper functions
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