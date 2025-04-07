<?php
$pageTitle = 'Vendor Profile';
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

// Get the vendor/store ID from URL
$vendorId = isset($_GET['id']) ? $_GET['id'] : null;

if ($vendorId) {
    try {
        $binaryStoreId = uuidToBin($vendorId);
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
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
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
    <!-- Vendor Header with Cover Photo and Profile Info -->
    <div class="vendor-header">
        <div class="vendor-cover" id="vendor-cover">
            <!-- Cover image will be set via JavaScript -->
        </div>
        <div class="vendor-profile-info">
            <div class="vendor-avatar" id="vendor-avatar">
                <!-- Logo will be set via JavaScript -->
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

    <!-- Vendor Details Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
            <h2 class="text-xl text-red-600 font-bold mb-6">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-map-marker-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Location</h3>
                        <p class="text-gray-600" id="vendor-location">Loading...</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-envelope text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Email</h3>
                        <p id="email-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-email" class="text-sm text-blue-600 hover:underline">Show Email</button>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-phone-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Contact</h3>
                        <p id="phone-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-phone" class="text-sm text-blue-600 hover:underline">Show Contact</button>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Owner</h3>
                        <p class="text-gray-600" id="vendor-owner">Loading...</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-calendar-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Registered</h3>
                        <p class="text-gray-600" id="vendor-registered">Loading...</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Last Seen</h3>
                        <p class="text-gray-600" id="vendor-last-seen">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="verification-wrapper">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl text-red-600 font-bold">Verification Status</h2>
                    <div id="owner-actions" class="hidden">
                        <button id="add-category-btn" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition mr-2">
                            <i class="fas fa-plus-circle mr-1"></i> Add Category
                        </button>
                        <button id="add-product-btn" class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition">
                            <i class="fas fa-plus-circle mr-1"></i> Add Product
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700"><span id="completion-percentage">0</span>% Complete</span>
                        <span class="text-sm font-medium text-gray-700"><span id="completion-steps">0</span>/4 Steps</span>
                    </div>
                    <div class="verification-track">
                        <div class="verification-indicator" id="verification-progress" style="width: 0%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="step-basic-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending">
                            <span>1</span>
                        </div>
                        <div>
                            <div class="font-bold">Basic Store Details</div>
                            <div class="text-gray-600 text-sm" id="basic-details-status">Pending</div>
                        </div>
                    </div>

                    <div id="step-location-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending">
                            <span>2</span>
                        </div>
                        <div>
                            <div class="font-bold">Location Details</div>
                            <div class="text-gray-600 text-sm" id="location-details-status">Pending</div>
                        </div>
                    </div>

                    <div id="step-categories" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending">
                            <span>3</span>
                        </div>
                        <div>
                            <div class="font-bold">Product Categories</div>
                            <div class="text-gray-600 text-sm" id="categories-status">Pending</div>
                        </div>
                    </div>

                    <div id="step-products" class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending">
                            <span>4</span>
                        </div>
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
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Products</span>
                    <span class="text-lg font-bold" id="product-count">0</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Categories</span>
                    <span class="text-lg font-bold" id="category-count">0</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Total Views</span>
                    <span class="text-lg font-bold" id="view-count">0</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Member Since</span>
                    <span class="text-lg font-bold" id="member-since">2024</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium">Account Status</span>
                    <span id="account-status" class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium">
                        Pending
                    </span>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-semibold mb-3">Store Description</h3>
                <p id="store-description" class="text-gray-600 text-sm">Loading...</p>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200" id="website-section">
                <h3 class="font-semibold mb-3">Website</h3>
                <a id="store-website" href="#" target="_blank" class="text-blue-600 hover:underline text-sm break-all">
                    Loading...
                </a>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="mt-12">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl text-red-600 font-bold">Products (<span id="products-heading-count">0</span>)</h2>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <input type="text" id="search-products" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                <select id="sort-products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                    <option value="default">Default Sorting</option>
                    <option value="latest">Latest</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Products will be loaded here -->
            <div class="col-span-full text-center py-8 text-gray-500">
                No products found for this vendor.
            </div>
        </div>

        <button id="loadMoreBtn" class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
            Load More Products
        </button>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Categories</h2>
            <span class="close" onclick="closeModal('addCategoryModal')">&times;</span>
        </div>
        <p class="text-gray-600 mb-4">Select categories for your store:</p>
        <div id="categories-container" class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-center items-center py-8">
                <div class="loader"></div>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2" onclick="closeModal('addCategoryModal')">Cancel</button>
            <button type="button" id="saveCategoriesBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">Save Categories</button>
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
            <div class="mb-4">
                <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select id="productCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    <option value="">Select a category</option>
                </select>
            </div>
            <div class="mb-4" id="productListContainer">
                <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Product *</label>
                <select id="productSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required disabled>
                    <option value="">Select a product</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">First select a category to see available products</p>
            </div>
            <div class="mb-4">
                <label for="productPrice" class="block text-sm font-medium text-gray-700 mb-1">Price (UGX) *</label>
                <input type="number" id="productPrice" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="mb-4">
                <label for="productQuantity" class="block text-sm font-medium text-gray-700 mb-1">Available Quantity *</label>
                <input type="number" id="productQuantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div class="flex justify-end">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2" onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Add Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const vendorId = '<?= $vendorId ?>';
        let storeData = null;
        let isOwner = false;
        let storeEmail = '';
        let storePhone = '';
        let currentPage = 1;
        let totalPages = 1;
        let selectedCategories = [];
        let availableProducts = {};

        // Initialize
        if (vendorId) {
            loadVendorProfile(vendorId);
        } else {
            showError("No vendor ID provided");
        }

        // Load vendor profile data
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
                    } else {
                        showError(data.error || "Failed to load vendor profile");
                    }
                })
                .catch(error => {
                    console.error('Error loading vendor profile:', error);
                    showError("Failed to load vendor profile");
                });
        }

        // Load products for this vendor
        function loadProducts(id, page = 1) {
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreProducts&id=${id}&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.products, page === 1);
                        currentPage = data.pagination?.page || 1;
                        totalPages = data.pagination?.pages || 1;

                        // Show/hide load more button
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

        // Render the vendor profile with the fetched data
        function renderVendorProfile(store) {
            // Hide loading, show content
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('content-state').classList.remove('hidden');

            // Basic info
            document.getElementById('vendor-name').textContent = store.name;
            document.getElementById('vendor-operation-type').textContent = store.nature_of_operation;

            // Set status badge
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

            // Contact info
            document.getElementById('vendor-location').textContent = `${store.district}, ${store.address}`;
            document.getElementById('vendor-owner').textContent = store.owner_username;

            // Store the email and phone for the toggle buttons
            storeEmail = store.business_email;
            storePhone = store.business_phone;

            // Registration date
            const regDate = new Date(store.created_at);
            document.getElementById('vendor-registered').textContent = regDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            // Last seen (owner's current login)
            if (store.owner_current_login) {
                const lastSeen = new Date(store.owner_current_login);
                document.getElementById('vendor-last-seen').textContent = formatTimeAgo(lastSeen);
            } else {
                document.getElementById('vendor-last-seen').textContent = 'Not available';
            }

            // Store logo
            if (store.logo_url) {
                const logoImg = document.createElement('img');
                logoImg.src = BASE_URL + store.logo_url;
                logoImg.alt = store.name;
                logoImg.className = 'w-full h-full object-cover rounded-full';

                const avatarContainer = document.getElementById('vendor-avatar');
                avatarContainer.innerHTML = '';
                avatarContainer.appendChild(logoImg);
            }

            // Cover image (placeholder for now)
            document.getElementById('vendor-cover').style.backgroundImage = 'linear-gradient(45deg, #f3f4f6 25%, #e5e7eb 25%, #e5e7eb 50%, #f3f4f6 50%, #f3f4f6 75%, #e5e7eb 75%, #e5e7eb 100%)';
            document.getElementById('vendor-cover').style.backgroundSize = '20px 20px';

            // Account summary
            document.getElementById('product-count').textContent = store.product_count || '0';
            document.getElementById('products-heading-count').textContent = store.product_count || '0';
            document.getElementById('category-count').textContent = store.categories?.length || '0';
            document.getElementById('view-count').textContent = store.view_count || '0';

            const createdYear = new Date(store.created_at).getFullYear();
            document.getElementById('member-since').textContent = createdYear;

            // Store description
            if (store.description) {
                document.getElementById('store-description').textContent = store.description;
            } else {
                document.getElementById('store-description').textContent = 'No description provided.';
            }

            // Website
            if (store.website_url) {
                document.getElementById('store-website').textContent = store.website_url;
                document.getElementById('store-website').href = store.website_url.startsWith('http') ?
                    store.website_url : 'https://' + store.website_url;
            } else {
                document.getElementById('website-section').classList.add('hidden');
            }

            // Check if current user is the owner
            isOwner = store.is_owner;
            if (isOwner) {
                document.getElementById('owner-actions').classList.remove('hidden');
            }

            // Calculate verification progress
            updateVerificationProgress(store);
        }

        // Update verification progress based on store data
        function updateVerificationProgress(store) {
            let completedSteps = 0;
            let totalSteps = 4;

            // Step 1: Basic Store Details
            const hasBasicDetails = store.name && store.business_email && store.business_phone && store.nature_of_operation;
            if (hasBasicDetails) {
                completedSteps++;
                updateStepStatus('basic-details', true);
            } else {
                updateStepStatus('basic-details', false);
            }

            // Step 2: Location Details
            const hasLocationDetails = store.region && store.district && store.address &&
                store.latitude && store.longitude;
            if (hasLocationDetails) {
                completedSteps++;
                updateStepStatus('location-details', true);
            } else {
                updateStepStatus('location-details', false);
            }

            // Step 3: Product Categories
            const hasCategories = store.categories && store.categories.length > 0;
            if (hasCategories) {
                completedSteps++;
                updateStepStatus('categories', true);
            } else {
                updateStepStatus('categories', false);
            }

            // Step 4: Products
            const hasProducts = store.product_count && store.product_count > 0;
            if (hasProducts) {
                completedSteps++;
                updateStepStatus('products', true);
            } else {
                updateStepStatus('products', false);
            }

            // Update progress bar and text
            const percentage = Math.round((completedSteps / totalSteps) * 100);
            document.getElementById('completion-percentage').textContent = percentage;
            document.getElementById('completion-steps').textContent = completedSteps;
            document.getElementById('verification-progress').style.width = `${percentage}%`;
        }

        // Update individual step status
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

                // Set the step number
                if (stepId === 'basic-details') iconElement.innerHTML = '1';
                else if (stepId === 'location-details') iconElement.innerHTML = '2';
                else if (stepId === 'categories') iconElement.innerHTML = '3';
                else if (stepId === 'products') iconElement.innerHTML = '4';
            }
        }

        // Render products
        function renderProducts(products, clearExisting = true) {
            const container = document.getElementById('products-container');

            if (clearExisting) {
                container.innerHTML = '';
            }

            if (!products || products.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        No products found for this vendor.
                    </div>
                `;
                return;
            }

            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1';

                const imageUrl = product.image_url ?
                    BASE_URL + product.image_url :
                    `https://placehold.co/400x200/f0f0f0/808080?text=${encodeURIComponent(product.name.substring(0, 2))}`;

                productCard.innerHTML = `
                    <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-48 object-cover">
                    <div class="p-4 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">${escapeHtml(product.name)}</h3>
                        <p class="text-gray-500 text-sm mb-4">Per ${escapeHtml(product.unit)}</p>
                        <p class="text-xl font-bold text-red-600 mb-2">UGX ${formatNumber(product.price)}</p>
                        <p class="text-gray-500 text-sm mb-4">Max Capacity: ${product.max_capacity || 'N/A'}</p>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-auto">
                            <span class="text-gray-500 text-sm">
                                <i class="fas fa-eye"></i> ${product.views || 0} Views
                            </span>
                            <a href="#" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">View Details</a>
                        </div>
                    </div>
                `;

                container.appendChild(productCard);
            });
        }

        // Show error state
        function showError(message) {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('error-state').classList.remove('hidden');
            console.error(message);
        }

        // Format number with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Format time ago
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

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Toggle email visibility
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

        // Toggle phone visibility
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

        // Load more products
        document.getElementById('loadMoreBtn').addEventListener('click', function() {
            if (currentPage < totalPages) {
                loadProducts(vendorId, currentPage + 1);
            }
        });

        // Search products
        document.getElementById('search-products').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const productCards = document.querySelectorAll('#products-container > div');

            productCards.forEach(card => {
                const productName = card.querySelector('h3')?.textContent.toLowerCase() || '';
                if (productName.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Sort products
        document.getElementById('sort-products').addEventListener('change', function(e) {
            const sortValue = e.target.value;
            const container = document.getElementById('products-container');
            const productCards = Array.from(container.children);

            if (productCards.length <= 1) return;

            productCards.sort((a, b) => {
                if (sortValue === 'latest') {
                    // This would require additional data, using default for now
                    return 0;
                } else if (sortValue === 'price-low') {
                    const priceA = parseInt(a.querySelector('.text-red-600').textContent.replace(/[^\d]/g, '')) || 0;
                    const priceB = parseInt(b.querySelector('.text-red-600').textContent.replace(/[^\d]/g, '')) || 0;
                    return priceA - priceB;
                } else if (sortValue === 'price-high') {
                    const priceA = parseInt(a.querySelector('.text-red-600').textContent.replace(/[^\d]/g, '')) || 0;
                    const priceB = parseInt(b.querySelector('.text-red-600').textContent.replace(/[^\d]/g, '')) || 0;
                    return priceB - priceA;
                }
                return 0;
            });

            // Clear and re-append sorted cards
            container.innerHTML = '';
            productCards.forEach(card => container.appendChild(card));
        });

        // Add Category Modal
        document.getElementById('add-category-btn').addEventListener('click', function() {
            openModal('addCategoryModal');
            loadAvailableCategories();
        });

        // Add Product Modal
        document.getElementById('add-product-btn').addEventListener('click', function() {
            openModal('addProductModal');
            loadStoreCategories();
        });

        // Load available categories for the Add Category modal
        function loadAvailableCategories() {
            const container = document.getElementById('categories-container');

            fetch(`${BASE_URL}fetch/manageProfile.php?action=getAvailableCategories`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.categories) {
                        container.innerHTML = '';

                        // Get store's existing categories
                        const existingCategoryIds = storeData.categories.map(cat => cat.category_uuid_id);
                        selectedCategories = [...existingCategoryIds];

                        data.categories.forEach(category => {
                            const isSelected = existingCategoryIds.includes(category.uuid_id);

                            const categoryItem = document.createElement('div');
                            categoryItem.className = 'flex items-center mb-2';
                            categoryItem.innerHTML = `
                                <input type="checkbox" id="cat-${category.uuid_id}" 
                                       class="category-checkbox mr-2" 
                                       value="${category.uuid_id}" 
                                       ${isSelected ? 'checked' : ''}>
                                <label for="cat-${category.uuid_id}" class="cursor-pointer">
                                    ${escapeHtml(category.name)}
                                </label>
                            `;

                            container.appendChild(categoryItem);

                            // Add event listener to update selectedCategories array
                            const checkbox = categoryItem.querySelector('input');
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
                    } else {
                        container.innerHTML = '<p class="text-center text-gray-500">No categories available</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    container.innerHTML = '<p class="text-center text-red-500">Failed to load categories</p>';
                });
        }

        // Load store categories for the Add Product modal
        function loadStoreCategories() {
            const categorySelect = document.getElementById('productCategory');
            categorySelect.innerHTML = '<option value="">Select a category</option>';

            if (storeData.categories && storeData.categories.length > 0) {
                storeData.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.uuid_id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });

                // Enable category selection
                categorySelect.disabled = false;

                // Add event listener to load products when category changes
                categorySelect.addEventListener('change', function() {
                    const categoryId = this.value;
                    if (categoryId) {
                        loadProductsForCategory(categoryId);
                    } else {
                        const productSelect = document.getElementById('productSelect');
                        productSelect.innerHTML = '<option value="">Select a product</option>';
                        productSelect.disabled = true;
                    }
                });
            } else {
                categorySelect.innerHTML = '<option value="">No categories available</option>';
                categorySelect.disabled = true;
            }
        }

        // Load products for a specific category
        function loadProductsForCategory(categoryId) {
            const productSelect = document.getElementById('productSelect');
            productSelect.innerHTML = '<option value="">Loading products...</option>';
            productSelect.disabled = true;

            fetch(`${BASE_URL}fetch/manageProfile.php?action=getProductsForCategory&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    productSelect.innerHTML = '<option value="">Select a product</option>';

                    if (data.success && data.products && data.products.length > 0) {
                        data.products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.uuid_id;
                            option.textContent = product.name;
                            option.dataset.price = product.price || 0;
                            productSelect.appendChild(option);
                        });

                        productSelect.disabled = false;

                        // Add event listener to set price when product is selected
                        productSelect.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            if (selectedOption && selectedOption.dataset.price) {
                                document.getElementById('productPrice').value = selectedOption.dataset.price;
                            }
                        });
                    } else {
                        productSelect.innerHTML = '<option value="">No products available for this category</option>';
                        productSelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading products for category:', error);
                    productSelect.innerHTML = '<option value="">Error loading products</option>';
                    productSelect.disabled = true;
                });
        }

        // Save selected categories
        document.getElementById('saveCategoriesBtn').addEventListener('click', function() {
            if (selectedCategories.length === 0) {
                alert('Please select at least one category');
                return;
            }

            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Saving...';

            fetch(`${BASE_URL}fetch/manageProfile.php?action=updateStoreCategories`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        store_id: vendorId,
                        categories: selectedCategories
                    })
                })
                .then(response => response.json())
                .then(data => {
                    button.disabled = false;
                    button.textContent = originalText;

                    if (data.success) {
                        closeModal('addCategoryModal');
                        notifications.success('Categories updated successfully');
                        // Reload the page to reflect changes
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

        // Add product form submission
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const categoryId = document.getElementById('productCategory').value;
            const productId = document.getElementById('productSelect').value;
            const price = document.getElementById('productPrice').value;
            const quantity = document.getElementById('productQuantity').value;

            if (!categoryId || !productId || !price || !quantity) {
                alert('Please fill in all required fields');
                return;
            }

            const formData = new FormData();
            formData.append('store_id', vendorId);
            formData.append('category_id', categoryId);
            formData.append('product_id', productId);
            formData.append('price', price);
            formData.append('quantity', quantity);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            fetch(`${BASE_URL}fetch/manageProfile.php?action=addStoreProduct`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;

                    if (data.success) {
                        closeModal('addProductModal');
                        notifications.success('Product added successfully');
                        // Reload the page to reflect changes
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

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modals when clicking outside
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