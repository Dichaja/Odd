<?php
$pageTitle = 'Vendor Profile';
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

// Get the vendor/store ID from URL
$vendorId = isset($_GET['id']) ? $_GET['id'] : null;

if ($vendorId) {
    try {
        // If vendorId is a valid UUID string, convert to binary; otherwise assume it's already binary.
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $vendorId)) {
            $binaryStoreId = uuidToBin($vendorId);
        } else {
            $binaryStoreId = $vendorId;
        }
        $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $stmt->execute([$binaryStoreId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($store) {
            $pageTitle = htmlspecialchars($store['name']); // Set page title to vendor name
        }
    } catch (Exception $e) {
        error_log("Error fetching vendor name: " . $e->getMessage());
    }
}

ob_start();
?>

<style>
    .vendor-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2rem;
    }

    .vendor-header {
        position: relative;
        margin-bottom: 2rem;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .vendor-cover {
        height: 250px;
        width: 100%;
        object-fit: cover;
        background-color: #f3f4f6;
    }

    .vendor-profile-info {
        display: flex;
        align-items: flex-end;
        padding: 2rem;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 100%);
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        color: white;
    }

    .vendor-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin-right: 2rem;
        object-fit: cover;
        background-color: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verification-wrapper {
        margin-top: 2rem;
    }

    .verification-track {
        height: 0.5rem;
        background-color: #E5E7EB;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
    }

    .verification-indicator {
        height: 100%;
        background-color: #C00000;
        border-radius: 0.25rem;
        transition: width 0.5s ease-in-out;
    }

    .step-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .step-icon.completed {
        background-color: #10B981;
        color: white;
    }

    .step-icon.pending {
        background-color: #E5E7EB;
        color: #4B5563;
    }

    .loader {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: #C00000;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 90%;
        max-width: 800px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    .tab-container {
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: none;
        position: relative;
    }

    .tab-button.active {
        color: #C00000;
        border-bottom: 2px solid #C00000;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .category-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .category-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .category-description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-success {
        background-color: #10B981;
        color: white;
    }

    .badge-warning {
        background-color: #F59E0B;
        color: white;
    }

    .badge-danger {
        background-color: #EF4444;
        color: white;
    }

    #products-container {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 640px) {
        #products-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        #products-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 640px) {
        .vendor-avatar {
            width: 80px;
            height: 80px;
        }
    }
</style>

<!-- Loading State -->
<div id="loading-state" class="flex flex-col items-center justify-center py-12">
    <div class="loader mb-4"></div>
    <p class="text-gray-600">Loading vendor profile...</p>
</div>

<!-- Error State -->
<div id="error-state" class="hidden bg-red-50 border border-red-200 text-red-700 p-8 rounded-lg text-center max-w-2xl mx-auto my-12">
    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
    <h2 class="text-xl font-bold mb-2">Profile Not Found</h2>
    <p class="mb-4">Sorry, we couldn't find the vendor profile you're looking for.</p>
    <a href="<?= BASE_URL ?>" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Return to Home
    </a>
</div>

<!-- Content State -->
<div id="content-state" class="hidden vendor-container">
    <!-- Vendor Header -->
    <div class="vendor-header">
        <div class="vendor-cover" id="vendor-cover"></div>
        <div class="vendor-profile-info">
            <div class="vendor-avatar" id="vendor-avatar">
                <i class="fas fa-store text-gray-400 text-4xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2" id="vendor-name">Store Name</h1>
                <div>
                    <span id="vendor-operation-type" class="bg-red-600 text-white px-3 py-1 rounded-full text-sm mr-2">Operation Type</span>
                    <span id="vendor-status" class="bg-yellow-300 text-yellow-800 px-3 py-1 rounded-full text-sm">Status</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
            <h2 class="text-xl text-red-600 font-bold mb-6">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-map-marker-alt text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Location</h3>
                        <p class="text-gray-600" id="vendor-location">Loading...</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-envelope text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Email</h3>
                        <p id="email-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-email" class="text-sm text-blue-600 hover:underline">Show Email</button>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-phone-alt text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Contact</h3>
                        <p id="phone-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-phone" class="text-sm text-blue-600 hover:underline">Show Contact</button>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-user text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Owner</h3>
                        <p class="text-gray-600" id="vendor-owner">Loading...</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-calendar-alt text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Registered</h3>
                        <p class="text-gray-600" id="vendor-registered">Loading...</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-clock text-lg"></i></div>
                    <div>
                        <h3 class="font-bold mb-1">Last Seen</h3>
                        <p class="text-gray-600" id="vendor-last-seen">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="verification-wrapper">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl text-red-600 font-bold">Verification Status</h2>
                    <div id="owner-actions" class="hidden">
                        <button id="manage-categories-btn" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition mr-2"><i class="fas fa-tags mr-1"></i> Manage Categories</button>
                        <button id="add-product-btn" class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition"><i class="fas fa-plus-circle mr-1"></i> Add Product</button>
                    </div>
                </div>
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2"><span class="text-sm font-medium text-gray-700"><span id="completion-percentage">0</span>% Complete</span><span class="text-sm font-medium text-gray-700"><span id="completion-steps">0</span>/4 Steps</span></div>
                    <div class="verification-track">
                        <div class="verification-indicator" id="verification-progress" style="width: 0%"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="step-basic-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending"><span>1</span></div>
                        <div>
                            <div class="font-bold">Basic Store Details</div>
                            <div class="text-gray-600 text-sm" id="basic-details-status">Pending</div>
                        </div>
                    </div>
                    <div id="step-location-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending"><span>2</span></div>
                        <div>
                            <div class="font-bold">Location Details</div>
                            <div class="text-gray-600 text-sm" id="location-details-status">Pending</div>
                        </div>
                    </div>
                    <div id="step-categories" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending"><span>3</span></div>
                        <div>
                            <div class="font-bold">Product Categories</div>
                            <div class="text-gray-600 text-sm" id="categories-status">Pending</div>
                        </div>
                    </div>
                    <div id="step-products" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending"><span>4</span></div>
                        <div>
                            <div class="font-bold">Products For Sale</div>
                            <div class="text-gray-600 text-sm" id="products-status">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Account Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl text-red-600 font-bold mb-6">Account Summary</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span class="font-medium">Products</span><span class="text-lg font-bold" id="product-count">0</span></div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span class="font-medium">Categories</span><span class="text-lg font-bold" id="category-count">0</span></div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span class="font-medium">Total Views</span><span class="text-lg font-bold" id="view-count">0</span></div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span class="font-medium">Member Since</span><span class="text-lg font-bold" id="member-since">2024</span></div>
                <div class="flex justify-between items-center"><span class="font-medium">Account Status</span><span id="account-status" class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium">Pending</span></div>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-semibold mb-3">Store Description</h3>
                <p id="store-description" class="text-gray-600 text-sm">Loading...</p>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200" id="website-section">
                <h3 class="font-semibold mb-3">Website</h3>
                <a id="store-website" href="#" target="_blank" class="text-blue-600 hover:underline text-sm break-all">Loading...</a>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="mt-12">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl text-red-600 font-bold">Products (<span id="products-heading-count">0</span>)</h2>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <input type="text" id="search-products" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                    <option value="">All Categories</option>
                </select>
                <select id="sort-products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                    <option value="default">Default Sorting</option>
                    <option value="latest">Latest</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>
        <div id="products-container">
            <div class="col-span-full text-center py-8 text-gray-500">
                No products found for this vendor.
            </div>
        </div>
        <button id="loadMoreBtn" class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
            Load More Products
        </button>
    </div>
</div>

<!-- Manage Categories Modal -->
<div id="manageCategoriesModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Manage Categories</h2>
            <span class="close" onclick="closeModal('manageCategoriesModal')">&times;</span>
        </div>
        <div class="tab-container">
            <button class="tab-button active" data-tab="manage-tab">Manage Store Categories</button>
            <button class="tab-button" data-tab="add-tab">Add New Categories</button>
        </div>
        <!-- Manage Tab -->
        <div id="manage-tab" class="tab-content active">
            <p class="text-gray-600 mb-4">Manage your store's existing categories:</p>
            <div id="store-categories-container" class="category-grid max-h-96 overflow-y-auto mb-4">
                <div class="flex justify-center items-center py-8 col-span-full">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
        <!-- Add Tab -->
        <div id="add-tab" class="tab-content">
            <p class="text-gray-600 mb-4">Add new categories to your store:</p>
            <div id="available-categories-container" class="category-grid max-h-96 overflow-y-auto mb-4">
                <div class="flex justify-center items-center py-8 col-span-full">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2" onclick="closeModal('manageCategoriesModal')">Cancel</button>
            <button type="button" id="saveCategoriesBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">Save Changes</button>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Product</h2>
            <span class="close" onclick="closeModal('addProductModal')">&times;</span>
        </div>
        <form id="addProductForm">
            <!-- Category selection -->
            <div class="mb-4">
                <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select id="productCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">Select a category</option>
                </select>
            </div>
            <!-- Product selection -->
            <div class="mb-4" id="productListContainer">
                <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Product *</label>
                <select id="productSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required disabled>
                    <option value="">Select a product</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Products shown here are those in that category, but <strong>not</strong> already in your store.
                </p>
            </div>
            <!-- Unit/Price line items -->
            <div id="unitPricingContainer" class="mt-4 hidden">
                <p class="font-semibold text-sm mb-2">Add one or more unit/price entries:</p>
                <div id="lineItemsWrapper" class="space-y-4"></div>
                <button type="button" id="addLineItemBtn" class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                    + Add Another Unit
                </button>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Edit Product Pricing</h2>
            <span class="close" onclick="closeModal('editProductModal')">&times;</span>
        </div>
        <form id="editProductForm">
            <input type="hidden" id="editStoreProductId" name="store_product_id">
            <div id="editUnitPricingContainer" class="mt-4">
                <p class="font-semibold text-sm mb-2">Edit unit/price entries:</p>
                <div id="editLineItemsWrapper" class="space-y-4"></div>
                <button type="button" id="editAddLineItemBtn" class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                    + Add Another Unit
                </button>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2" onclick="closeModal('editProductModal')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content">
        <h2 class="text-xl font-bold mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete this product?</p>
        <div class="flex justify-end">
            <button id="deleteCancelBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2">Cancel</button>
            <button id="deleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
        </div>
    </div>
</div>

<script>
    window.openModal = function(modalId) {
        document.getElementById(modalId).style.display = 'block';
    };
    window.closeModal = function(modalId) {
        document.getElementById(modalId).style.display = 'none';
    };

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
    let categoryStatusChanges = {};

    document.addEventListener('DOMContentLoaded', function() {
        if (vendorId) {
            loadVendorProfile(vendorId);
        } else {
            showError("No vendor ID provided");
        }

        // Manage Category tabs
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        function loadVendorProfile(id) {
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreDetails&id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.store) {
                        storeData = data.store;
                        renderVendorProfile(storeData);
                        loadProducts(id);
                        populateCategoryFilter(storeData.categories);
                    } else {
                        showError(data.error || "Failed to load vendor profile");
                    }
                })
                .catch(error => {
                    console.error('Error loading vendor profile:', error);
                    showError("Failed to load vendor profile");
                });
        }

        function loadProducts(id, page = 1) {
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreProducts&id=${id}&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.products, page === 1);
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
                option.value = category.category_uuid_id;
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
                filtered = filtered.filter(p => p.store_category_uuid_id === categoryId);
            }
            if (searchTerm) {
                filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
            }
            if (filtered.length === 0) {
                container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found matching your criteria.</div>`;
                return;
            }
            renderProducts(filtered, true);
        }

        function renderVendorProfile(store) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('content-state').classList.remove('hidden');
            document.getElementById('vendor-name').textContent = store.name;
            document.getElementById('vendor-operation-type').textContent = store.nature_of_operation;
            const statusBadge = document.getElementById('vendor-status');
            const accountStatus = document.getElementById('account-status');
            if (store.status === 'active') {
                statusBadge.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm';
                statusBadge.textContent = 'Active';
                accountStatus.className = 'inline-block bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-medium';
                accountStatus.textContent = 'Active';
            } else if (store.status === 'pending') {
                statusBadge.className = 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm';
                statusBadge.textContent = 'Pending Verification';
                accountStatus.className = 'inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium';
                accountStatus.textContent = 'Pending';
            } else {
                statusBadge.className = 'bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm';
                statusBadge.textContent = store.status.charAt(0).toUpperCase() + store.status.slice(1);
                accountStatus.className = 'inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-medium';
                accountStatus.textContent = store.status.charAt(0).toUpperCase() + store.status.slice(1);
            }
            document.getElementById('vendor-location').textContent = `${store.district}, ${store.address}`;
            document.getElementById('vendor-owner').textContent = store.owner_username;
            storeEmail = store.business_email;
            storePhone = store.business_phone;
            const regDate = new Date(store.created_at);
            document.getElementById('vendor-registered').textContent = regDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            if (store.owner_current_login) {
                const lastSeen = new Date(store.owner_current_login);
                document.getElementById('vendor-last-seen').textContent = formatTimeAgo(lastSeen);
            } else {
                document.getElementById('vendor-last-seen').textContent = 'Not available';
            }
            if (store.logo_url) {
                const logoImg = document.createElement('img');
                logoImg.src = BASE_URL + store.logo_url;
                logoImg.alt = store.name;
                logoImg.className = 'w-full h-full object-cover rounded-full';
                const avatarContainer = document.getElementById('vendor-avatar');
                avatarContainer.innerHTML = '';
                avatarContainer.appendChild(logoImg);
            }
            document.getElementById('vendor-cover').style.backgroundImage = 'linear-gradient(45deg, #f3f4f6 25%, #e5e7eb 25%, #e5e7eb 50%, #f3f4f6 50%, #f3f4f6 75%, #e5e7eb 75%, #e5e7eb 100%)';
            document.getElementById('vendor-cover').style.backgroundSize = '20px 20px';
            const activeCategories = store.categories ? store.categories.filter(cat => cat.status === 'active') : [];
            const activeProductsCount = store.product_count || 0;
            document.getElementById('product-count').textContent = activeProductsCount;
            document.getElementById('products-heading-count').textContent = activeProductsCount;
            document.getElementById('category-count').textContent = activeCategories.length;
            document.getElementById('view-count').textContent = '0';
            const createdYear = new Date(store.created_at).getFullYear();
            document.getElementById('member-since').textContent = createdYear;
            document.getElementById('store-description').textContent = store.description || 'No description provided.';
            if (store.website_url) {
                document.getElementById('store-website').textContent = store.website_url;
                document.getElementById('store-website').href = store.website_url.startsWith('http') ? store.website_url : 'https://' + store.website_url;
            } else {
                document.getElementById('website-section').classList.add('hidden');
            }
            isOwner = store.is_owner;
            if (isOwner) {
                document.getElementById('owner-actions').classList.remove('hidden');
            }
            updateVerificationProgress(store);
        }

        function updateVerificationProgress(store) {
            let completedSteps = 0;
            const totalSteps = 4;
            const hasBasicDetails = store.name && store.business_email && store.business_phone && store.nature_of_operation;
            completedSteps += hasBasicDetails ? 1 : 0;
            updateStepStatus('basic-details', hasBasicDetails);
            const hasLocationDetails = store.region && store.district && store.address && store.latitude && store.longitude;
            completedSteps += hasLocationDetails ? 1 : 0;
            updateStepStatus('location-details', hasLocationDetails);
            const activeCats = store.categories ? store.categories.filter(cat => cat.status === 'active') : [];
            completedSteps += activeCats.length > 0 ? 1 : 0;
            updateStepStatus('categories', activeCats.length > 0);
            completedSteps += (store.product_count && store.product_count > 0) ? 1 : 0;
            updateStepStatus('products', store.product_count && store.product_count > 0);
            const percentage = Math.round((completedSteps / totalSteps) * 100);
            document.getElementById('completion-percentage').textContent = percentage;
            document.getElementById('completion-steps').textContent = completedSteps;
            document.getElementById('verification-progress').style.width = `${percentage}%`;
        }

        function updateStepStatus(stepId, isCompleted) {
            const stepElement = document.getElementById(`step-${stepId}`);
            const statusElement = document.getElementById(`${stepId}-status`);
            const iconElement = stepElement.querySelector('.step-icon');
            if (isCompleted) {
                stepElement.classList.remove('bg-gray-50');
                stepElement.classList.add('bg-green-50');
                statusElement.textContent = 'Completed';
                statusElement.classList.remove('text-gray-600');
                statusElement.classList.add('text-green-600');
                iconElement.classList.remove('pending');
                iconElement.classList.add('completed');
                iconElement.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                stepElement.classList.remove('bg-green-50');
                stepElement.classList.add('bg-gray-50');
                statusElement.textContent = 'Pending';
                statusElement.classList.remove('text-green-600');
                statusElement.classList.add('text-gray-600');
                iconElement.classList.remove('completed');
                iconElement.classList.add('pending');
                iconElement.innerHTML = stepId === 'basic-details' ? '1' : stepId === 'location-details' ? '2' : stepId === 'categories' ? '3' : '4';
            }
        }

        // Render Products with pricing and Edit/Delete buttons
        function renderProducts(products, clearExisting = true) {
            const container = document.getElementById('products-container');
            if (clearExisting) {
                container.innerHTML = '';
            }
            if (!products || products.length === 0) {
                container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found for this vendor.</div>`;
                return;
            }
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1 flex flex-col';
                const productNameShort = encodeURIComponent(product.name.substring(0, 2));
                const imageUrl = `https://placehold.co/400x200/f0f0f0/808080?text=${productNameShort}`;
                let pricingLines = (product.pricing && product.pricing.length > 0) ?
                    product.pricing.map(pr => `
                        <div class="text-sm text-gray-700 my-1">
                            <strong>${escapeHtml(pr.unit_name)}</strong>: UGX ${formatNumber(pr.price)}
                            <em class="text-xs text-gray-500">(${escapeHtml(pr.price_category)})</em>
                            ${pr.delivery_capacity ? `<span class="ml-2 text-xs text-gray-400">Cap: ${pr.delivery_capacity}</span>` : ''}
                        </div>
                    `).join('') :
                    `<div class="text-sm text-gray-600 italic">No price data</div>`;

                productCard.innerHTML = `
                    <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-40 object-cover">
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">${escapeHtml(product.name)}</h3>
                        <p class="text-gray-500 text-sm mb-4 flex-grow">
                            ${escapeHtml(product.description || '').substring(0,100)}...
                        </p>
                        <div class="border-t border-gray-200 pt-2">
                            ${pricingLines}
                        </div>
                        <div class="flex justify-end items-center pt-4 mt-auto space-x-2">
                            <button onclick="openEditProductModal('${product.store_product_uuid_id}')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">Edit</button>
                            <button onclick="initiateDeleteProduct('${product.store_product_uuid_id}')" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">Delete</button>
                        </div>
                    </div>
                `;
                container.appendChild(productCard);
            });
        }

        function showError(message) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.remove('hidden');
            console.error(message);
        }

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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

        const toggleEmail = document.getElementById('toggle-email');
        const emailDisplay = document.getElementById('email-display');
        let emailVisible = false;
        toggleEmail.addEventListener('click', function() {
            if (emailVisible) {
                emailDisplay.textContent = '••••••••••';
                toggleEmail.textContent = 'Show Email';
            } else {
                emailDisplay.textContent = storeEmail || 'Not provided';
                toggleEmail.textContent = 'Hide Email';
            }
            emailVisible = !emailVisible;
        });

        const togglePhone = document.getElementById('toggle-phone');
        const phoneDisplay = document.getElementById('phone-display');
        let phoneVisible = false;
        togglePhone.addEventListener('click', function() {
            if (phoneVisible) {
                phoneDisplay.textContent = '••••••••••';
                togglePhone.textContent = 'Show Contact';
            } else {
                phoneDisplay.textContent = storePhone || 'Not provided';
                togglePhone.textContent = 'Hide Contact';
            }
            phoneVisible = !phoneVisible;
        });

        document.getElementById('loadMoreBtn').addEventListener('click', function() {
            if (currentPage < totalPages) {
                loadProducts(vendorId, currentPage + 1);
            }
        });

        document.getElementById('search-products').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const categoryId = document.getElementById('filter-category').value;
            filterProducts(categoryId, searchTerm);
        });

        document.getElementById('sort-products').addEventListener('change', function(e) {
            const sortValue = e.target.value;
            const container = document.getElementById('products-container');
            const productCards = Array.from(container.children);
            if (productCards.length <= 1) return;
            productCards.sort((a, b) => {
                if (sortValue === 'latest') {
                    return 0;
                } else if (sortValue === 'price-low') {
                    const aPrice = parseInt((a.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    const bPrice = parseInt((b.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    return aPrice - bPrice;
                } else if (sortValue === 'price-high') {
                    const aPrice = parseInt((a.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    const bPrice = parseInt((b.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    return bPrice - aPrice;
                }
                return 0;
            });
            container.innerHTML = '';
            productCards.forEach(card => container.appendChild(card));
        });

        // Manage Categories
        document.getElementById('manage-categories-btn').addEventListener('click', function() {
            openModal('manageCategoriesModal');
            loadStoreCategoriesForManagement();
            loadAvailableCategories();
        });

        function loadStoreCategoriesForManagement() {
            const container = document.getElementById('store-categories-container');
            container.innerHTML = '<div class="flex justify-center items-center py-8 col-span-full"><div class="loader"></div></div>';
            if (!storeData || !storeData.categories || storeData.categories.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500 py-4 col-span-full">No categories added to this store yet.</p>';
                return;
            }
            container.innerHTML = '';
            storeData.categories.forEach(category => {
                const categoryCard = document.createElement('div');
                categoryCard.className = 'category-card';
                categoryCard.dataset.id = category.uuid_id;
                const statusClass = category.status === 'active' ? 'badge-success' : 'badge-warning';
                const statusText = category.status === 'active' ? 'Active' : 'Inactive';
                categoryCard.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg">${escapeHtml(category.name)}</h3>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </div>
                    <p class="text-gray-600 text-sm category-description mb-3">
                        ${escapeHtml(category.description || 'No description available')}
                    </p>
                    <div class="flex justify-between items-center mt-auto">
                        <span class="text-sm text-gray-500"><i class="fas fa-box"></i> ${category.product_count || 0} Products</span>
                        <select class="category-status-select px-2 py-1 border border-gray-300 rounded text-sm" data-id="${category.uuid_id}" data-original="${category.status}">
                            <option value="active" ${category.status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${category.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                `;
                container.appendChild(categoryCard);
            });
            document.querySelectorAll('.category-status-select').forEach(select => {
                select.addEventListener('change', function() {
                    const categoryId = this.dataset.id;
                    const newStatus = this.value;
                    const originalStatus = this.dataset.original;
                    if (newStatus !== originalStatus) {
                        categoryStatusChanges[categoryId] = newStatus;
                        const card = this.closest('.category-card');
                        const badge = card.querySelector('.badge');
                        badge.className = `badge ${newStatus === 'active' ? 'badge-success' : 'badge-warning'}`;
                        badge.textContent = newStatus === 'active' ? 'Active' : 'Inactive';
                    } else {
                        delete categoryStatusChanges[categoryId];
                    }
                });
            });
        }

        function loadAvailableCategories() {
            const container = document.getElementById('available-categories-container');
            container.innerHTML = '<div class="flex justify-center items-center py-8 col-span-full"><div class="loader"></div></div>';
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getAvailableCategories&store_id=${vendorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories && data.categories.length > 0) {
                        const activeCategories = data.categories.filter(cat => cat.status === 'active');
                        if (activeCategories.length === 0) {
                            container.innerHTML = '<p class="text-center text-gray-500 py-4 col-span-full">No additional active categories available.</p>';
                            return;
                        }
                        container.innerHTML = '';
                        fetch(`${BASE_URL}fetch/manageProfile.php?action=getCategoryProductCounts`)
                            .then(r => r.json())
                            .then(countData => {
                                const productCounts = countData.success ? countData.counts : {};
                                activeCategories.forEach(category => {
                                    const categoryCard = document.createElement('div');
                                    categoryCard.className = 'category-card';
                                    const productCount = productCounts[category.uuid_id] || 0;
                                    categoryCard.innerHTML = `
                                        <div class="flex items-start mb-2">
                                            <input type="checkbox" id="cat-${category.uuid_id}" class="category-checkbox mr-2 mt-1" value="${category.uuid_id}">
                                            <div>
                                                <label for="cat-${category.uuid_id}" class="font-bold text-lg cursor-pointer">${escapeHtml(category.name)}</label>
                                                <p class="text-gray-600 text-sm category-description">${escapeHtml(category.description || 'No description available')}</p>
                                                <p class="text-sm text-gray-500 mt-2"><i class="fas fa-box"></i> ${productCount} Products</p>
                                            </div>
                                        </div>
                                    `;
                                    container.appendChild(categoryCard);
                                    const checkbox = categoryCard.querySelector('input');
                                    checkbox.addEventListener('change', function() {
                                        if (this.checked) {
                                            if (!selectedCategories.includes(this.value)) {
                                                selectedCategories.push(this.value);
                                            }
                                        } else {
                                            selectedCategories = selectedCategories.filter(id => id !== this.value);
                                        }
                                    });
                                });
                            })
                            .catch(error => {
                                console.error('Error loading category product counts:', error);
                                activeCategories.forEach(category => {
                                    const categoryCard = document.createElement('div');
                                    categoryCard.className = 'category-card';
                                    categoryCard.innerHTML = `
                                        <div class="flex items-start mb-2">
                                            <input type="checkbox" id="cat-${category.uuid_id}" class="category-checkbox mr-2 mt-1" value="${category.uuid_id}">
                                            <div>
                                                <label for="cat-${category.uuid_id}" class="font-bold text-lg cursor-pointer">${escapeHtml(category.name)}</label>
                                                <p class="text-gray-600 text-sm category-description">${escapeHtml(category.description || 'No description available')}</p>
                                            </div>
                                        </div>
                                    `;
                                    container.appendChild(categoryCard);
                                    const checkbox = categoryCard.querySelector('input');
                                    checkbox.addEventListener('change', function() {
                                        if (this.checked) {
                                            if (!selectedCategories.includes(this.value)) {
                                                selectedCategories.push(this.value);
                                            }
                                        } else {
                                            selectedCategories = selectedCategories.filter(id => id !== this.value);
                                        }
                                    });
                                });
                            });
                    } else {
                        container.innerHTML = '<p class="text-center text-gray-500 py-4 col-span-full">No additional categories available.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    container.innerHTML = '<p class="text-center text-red-500 py-4 col-span-full">Failed to load categories.</p>';
                });
        }

        document.getElementById('saveCategoriesBtn').addEventListener('click', function() {
            const activeTab = document.querySelector('.tab-button.active').dataset.tab;
            if (activeTab === 'add-tab' && selectedCategories.length === 0) {
                alert('Please select at least one category to add');
                return;
            }
            if (activeTab === 'manage-tab' && Object.keys(categoryStatusChanges).length === 0) {
                alert('No changes detected');
                return;
            }
            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Saving...';
            let endpoint, payload;
            if (activeTab === 'add-tab') {
                endpoint = `${BASE_URL}fetch/manageProfile.php?action=updateStoreCategories`;
                payload = {
                    store_id: vendorId,
                    categories: selectedCategories
                };
            } else {
                endpoint = `${BASE_URL}fetch/manageProfile.php?action=updateCategoryStatus`;
                payload = {
                    store_id: vendorId,
                    category_updates: categoryStatusChanges
                };
            }
            fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    button.disabled = false;
                    button.textContent = originalText;
                    if (data.success) {
                        closeModal('manageCategoriesModal');
                        notifications.success('Categories updated successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert(data.error || 'Failed to update categories');
                    }
                })
                .catch(error => {
                    console.error('Error updating categories:', error);
                    button.disabled = false;
                    button.textContent = originalText;
                    alert('Failed to update categories. Please try again.');
                });
        });

        // Add Product Flow
        document.getElementById('add-product-btn').addEventListener('click', function() {
            openModal('addProductModal');
            loadStoreCategories();
        });

        function loadStoreCategories() {
            const categorySelect = document.getElementById('productCategory');
            categorySelect.innerHTML = '<option value="">Select a category</option>';
            if (storeData.categories && storeData.categories.length > 0) {
                const activeCats = storeData.categories.filter(cat => cat.status === 'active');
                activeCats.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.category_uuid_id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
                categorySelect.disabled = false;
                categorySelect.addEventListener('change', function() {
                    const catId = this.value;
                    if (catId) {
                        loadProductsForCategory(catId);
                    } else {
                        const productSelect = document.getElementById('productSelect');
                        productSelect.innerHTML = '<option value="">Select a product</option>';
                        productSelect.disabled = true;
                        document.getElementById('unitPricingContainer').classList.add('hidden');
                    }
                });
            } else {
                categorySelect.innerHTML = '<option value="">No categories available</option>';
                categorySelect.disabled = true;
            }
        }

        function loadProductsForCategory(categoryId) {
            const productSelect = document.getElementById('productSelect');
            productSelect.innerHTML = '<option value="">Loading products...</option>';
            productSelect.disabled = true;
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getProductsForCategory&store_id=${vendorId}&category_id=${categoryId}`)
                .then(r => r.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select a product</option>';
                    if (data.success && data.products && data.products.length > 0) {
                        data.products.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.uuid_id;
                            opt.textContent = p.name;
                            productSelect.appendChild(opt);
                        });
                        productSelect.disabled = false;
                        productSelect.addEventListener('change', function() {
                            const prodId = this.value;
                            if (prodId) {
                                // Fetch units from product_unit_of_measure
                                fetch(`${BASE_URL}fetch/manageProfile.php?action=getUnitsForProduct`)
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            availableUnits = data.units;
                                            prepareUnitPricingUI();
                                        } else {
                                            availableUnits = [];
                                            document.getElementById('unitPricingContainer').classList.add('hidden');
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Error fetching units:', err);
                                        availableUnits = [];
                                        document.getElementById('unitPricingContainer').classList.add('hidden');
                                    });
                            } else {
                                document.getElementById('unitPricingContainer').classList.add('hidden');
                            }
                        });
                    } else {
                        productSelect.innerHTML = '<option value="">No products available for this category</option>';
                        productSelect.disabled = true;
                        document.getElementById('unitPricingContainer').classList.add('hidden');
                    }
                })
                .catch(err => {
                    console.error('Error loading products for category:', err);
                    productSelect.innerHTML = '<option value="">Error loading products</option>';
                    productSelect.disabled = true;
                });
        }

        function prepareUnitPricingUI() {
            const container = document.getElementById('unitPricingContainer');
            container.classList.remove('hidden');
            const wrapper = document.getElementById('lineItemsWrapper');
            wrapper.innerHTML = '';
            lineItemCount = 0;
            addLineItemRow();
        }

        function addLineItemRow() {
            lineItemCount++;
            const wrapper = document.getElementById('lineItemsWrapper');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-2', 'items-center', 'flex-wrap');
            const unitSelect = document.createElement('select');
            unitSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            unitSelect.required = true;
            availableUnits.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.uuid_id;
                opt.textContent = `${u.si_unit} ${u.package_name}`;
                unitSelect.appendChild(opt);
            });
            const priceCatSelect = document.createElement('select');
            priceCatSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            ['retail', 'wholesale', 'factory'].forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
                priceCatSelect.appendChild(opt);
            });
            const priceInput = document.createElement('input');
            priceInput.type = 'number';
            priceInput.min = '0';
            priceInput.step = 'any';
            priceInput.placeholder = 'Price';
            priceInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            priceInput.required = true;
            const capacityInput = document.createElement('input');
            capacityInput.type = 'number';
            capacityInput.min = '0';
            capacityInput.placeholder = 'Capacity';
            capacityInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            // Listen to price category change to update placeholder
            priceCatSelect.addEventListener('change', function() {
                if (this.value === 'wholesale' || this.value === 'factory') {
                    capacityInput.placeholder = 'Max. Capacity';
                } else {
                    capacityInput.placeholder = 'Capacity';
                }
            });
            row.appendChild(unitSelect);
            row.appendChild(priceCatSelect);
            row.appendChild(priceInput);
            row.appendChild(capacityInput);
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.classList.add('px-2', 'py-1', 'text-xs', 'bg-red-200', 'text-red-800', 'rounded');
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', () => {
                wrapper.removeChild(row);
            });
            row.appendChild(removeBtn);
            wrapper.appendChild(row);
        }

        // ---------- Edit Product Functions ----------
        function openEditProductModal(storeProductUuid) {
            const product = allProducts.find(p => p.store_product_uuid_id === storeProductUuid);
            if (!product) {
                notifications.error("Product not found.");
                return;
            }
            // Ensure availableUnits are populated for edit modal
            if (!availableUnits || availableUnits.length === 0) {
                fetch(`${BASE_URL}fetch/manageProfile.php?action=getUnitsForProduct`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            availableUnits = data.units;
                        } else {
                            availableUnits = [];
                        }
                        populateEditModal(product);
                    })
                    .catch(error => {
                        console.error("Error fetching units for edit modal", error);
                        availableUnits = [];
                        populateEditModal(product);
                    });
            } else {
                populateEditModal(product);
            }
        }

        function populateEditModal(product) {
            document.getElementById('editStoreProductId').value = product.store_product_uuid_id;
            const wrapper = document.getElementById('editLineItemsWrapper');
            wrapper.innerHTML = '';
            if (product.pricing && product.pricing.length > 0) {
                product.pricing.forEach(item => {
                    addEditLineItemRow(item);
                });
            } else {
                addEditLineItemRow();
            }
            openModal('editProductModal');
        }

        function addEditLineItemRow(item) {
            const wrapper = document.getElementById('editLineItemsWrapper');
            const row = document.createElement('div');
            row.classList.add('flex', 'gap-2', 'items-center', 'flex-wrap');
            const unitSelect = document.createElement('select');
            unitSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            unitSelect.required = true;
            availableUnits.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.uuid_id;
                opt.textContent = `${u.si_unit} ${u.package_name}`;
                unitSelect.appendChild(opt);
            });
            // Preselect the unit if available
            if (item && item.unit_id) {
                for (let i = 0; i < unitSelect.options.length; i++) {
                    if (unitSelect.options[i].value === item.unit_id) {
                        unitSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            const priceCatSelect = document.createElement('select');
            priceCatSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            ['retail', 'wholesale', 'factory'].forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat;
                opt.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
                priceCatSelect.appendChild(opt);
            });
            if (item && item.price_category) {
                priceCatSelect.value = item.price_category;
            }
            const priceInput = document.createElement('input');
            priceInput.type = 'number';
            priceInput.min = '0';
            priceInput.step = 'any';
            priceInput.placeholder = 'Price';
            priceInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            priceInput.required = true;
            if (item && item.price) {
                priceInput.value = item.price;
            }
            const capacityInput = document.createElement('input');
            capacityInput.type = 'number';
            capacityInput.min = '0';
            capacityInput.placeholder = (item && (item.price_category === 'wholesale' || item.price_category === 'factory')) ? 'Max. Capacity' : 'Capacity';
            capacityInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
            if (item && item.delivery_capacity) {
                capacityInput.value = item.delivery_capacity;
            }
            // Update placeholder on change
            priceCatSelect.addEventListener('change', function() {
                if (this.value === 'wholesale' || this.value === 'factory') {
                    capacityInput.placeholder = 'Max. Capacity';
                } else {
                    capacityInput.placeholder = 'Capacity';
                }
            });
            row.appendChild(unitSelect);
            row.appendChild(priceCatSelect);
            row.appendChild(priceInput);
            row.appendChild(capacityInput);
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.classList.add('px-2', 'py-1', 'text-xs', 'bg-red-200', 'text-red-800', 'rounded');
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', () => {
                wrapper.removeChild(row);
            });
            row.appendChild(removeBtn);
            wrapper.appendChild(row);
        }

        document.getElementById('addLineItemBtn').addEventListener('click', function() {
            addLineItemRow();
        });
        document.getElementById('editAddLineItemBtn').addEventListener('click', function() {
            addEditLineItemRow();
        });

        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const categoryId = document.getElementById('productCategory').value;
            const productId = document.getElementById('productSelect').value;
            if (!categoryId || !productId) {
                alert('Please select Category and Product');
                return;
            }
            const rows = document.querySelectorAll('#lineItemsWrapper > div');
            if (rows.length === 0) {
                alert('Please add at least one pricing entry');
                return;
            }
            const lineItems = [];
            rows.forEach(row => {
                const selects = row.querySelectorAll('select');
                const inputs = row.querySelectorAll('input');
                if (selects.length < 2 || inputs.length < 2) return;
                const unitUuid = selects[0].value;
                const priceCategory = selects[1].value;
                const priceVal = inputs[0].value;
                const capacityVal = inputs[1].value || null;
                if (!unitUuid || !priceVal) return;
                lineItems.push({
                    unit_uuid_id: unitUuid,
                    price_category: priceCategory,
                    price: priceVal,
                    delivery_capacity: capacityVal
                });
            });
            if (lineItems.length === 0) {
                alert('Please ensure you have filled the price for at least one line item');
                return;
            }
            const formData = new FormData();
            formData.append('store_id', vendorId);
            formData.append('category_id', categoryId);
            formData.append('product_id', productId);
            formData.append('line_items', JSON.stringify(lineItems));
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';
            fetch(`${BASE_URL}fetch/manageProfile.php?action=addStoreProduct`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    if (data.success) {
                        closeModal('addProductModal');
                        notifications.success('Product & pricing added successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert(data.error || 'Failed to add product');
                    }
                })
                .catch(error => {
                    console.error('Error adding product:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    alert('Failed to add product. Please try again.');
                });
        });

        // Edit Product Form
        document.getElementById('editProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const storeProductId = document.getElementById('editStoreProductId').value;
            const rows = document.querySelectorAll('#editLineItemsWrapper > div');
            if (rows.length === 0) {
                alert("Please add at least one pricing entry");
                return;
            }
            const lineItems = [];
            rows.forEach(function(row) {
                const selects = row.querySelectorAll('select');
                const inputs = row.querySelectorAll('input');
                if (selects.length < 1 || inputs.length < 2) return;
                const unitUuid = selects[0].value;
                const priceCategory = selects[1].value;
                const priceVal = inputs[0].value;
                const capacityVal = inputs[1].value || null;
                if (!unitUuid || !priceVal) return;
                lineItems.push({
                    unit_uuid_id: unitUuid,
                    price_category: priceCategory,
                    price: priceVal,
                    delivery_capacity: capacityVal
                });
            });
            if (lineItems.length === 0) {
                alert("Please fill in valid pricing entry");
                return;
            }
            const formData = new FormData();
            formData.append('store_id', vendorId);
            formData.append('store_product_id', storeProductId);
            formData.append('line_items', JSON.stringify(lineItems));
            const submitBtn = document.querySelector('#editProductForm button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = "Updating...";
            fetch(`${BASE_URL}fetch/manageProfile.php?action=updateStoreProduct`, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    if (data.success) {
                        closeModal('editProductModal');
                        notifications.success('Product pricing updated successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert(data.error || 'Update failed');
                    }
                })
                .catch(err => {
                    console.error('Error updating product:', err);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    alert('Update failed. Please try again.');
                });
        });

        // ---------- Delete Product via Confirmation Modal ----------
        function initiateDeleteProduct(storeProductUuid) {
            pendingDeleteId = storeProductUuid;
            openModal('deleteConfirmModal');
        }

        document.getElementById('deleteCancelBtn').addEventListener('click', function() {
            pendingDeleteId = null;
            closeModal('deleteConfirmModal');
        });

        document.getElementById('deleteConfirmBtn').addEventListener('click', function() {
            if (!pendingDeleteId) {
                return;
            }
            fetch(`${BASE_URL}fetch/manageProfile.php?action=deleteProduct`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: pendingDeleteId
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        notifications.success('Product deleted successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert(data.error || "Failed to delete product");
                    }
                })
                .catch(err => {
                    console.error("Error deleting product:", err);
                    alert("Failed to delete product. Please try again.");
                })
                .finally(() => {
                    pendingDeleteId = null;
                    closeModal('deleteConfirmModal');
                });
        });

        window.openEditProductModal = openEditProductModal;
        window.initiateDeleteProduct = initiateDeleteProduct;

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>