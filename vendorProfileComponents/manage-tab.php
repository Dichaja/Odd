<style>
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
</style>
<!-- Manage Products & Categories Tab -->
<div id="manage-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xl font-bold mb-4">Manage Categories</h2>
                <p class="text-gray-600 mb-4">Add or manage product categories for your store.</p>
                <button id="manage-categories-btn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    <i class="fas fa-tags mr-2"></i> Manage Categories
                </button>
            </div>
            <div>
                <h2 class="text-xl font-bold mb-4">Manage Products</h2>
                <p class="text-gray-600 mb-4">Add new products to your store or manage existing ones.</p>
                <button id="add-product-btn"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    <i class="fas fa-plus-circle mr-2"></i> Add Product
                </button>
            </div>
        </div>
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
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                onclick="closeModal('manageCategoriesModal')">Cancel</button>
            <button type="button" id="saveCategoriesBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">Save
                Changes</button>
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
                <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Product
                    *</label>
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
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('addProductModal')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">Add Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Global variables
    // let vendorId = '<?= $vendorId ?>';

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Modal functions
    window.openModal = function (modalId) {
        document.getElementById(modalId).style.display = 'block';
    };

    window.closeModal = function (modalId) {
        document.getElementById(modalId).style.display = 'none';
    };

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} transition-opacity duration-300`;

        toast.innerHTML = `
                <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 ${type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'} rounded-lg">
                    <i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-xmark'}"></i>
                </div>
                <div class="ml-3 text-sm font-normal">${message}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:text-green-700' : 'bg-red-50 text-red-500 hover:text-red-700'} rounded-lg p-1.5 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

        document.getElementById('toast-container').appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    // Load store data
    function loadStoreData() {
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreDetails&id=${vendorId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;
                } else {
                    showToast(data.error || "Failed to load store data", "error");
                }
            })
            .catch(error => {
                console.error('Error loading store data:', error);
                showToast("Failed to load store data", "error");
            });
    }

    // ==================== CATEGORY MANAGEMENT ====================

    // Load store categories for management
    function loadStoreCategoriesForManagement() {
        const container = document.getElementById('store-categories-container');
        container.innerHTML = '<div class="flex justify-center items-center py-8 col-span-full"><div class="loader"></div></div>';

        fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreDetails&id=${vendorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;

                    if (!storeData.categories || storeData.categories.length === 0) {
                        container.innerHTML = '<p class="text-center text-gray-500 py-4 col-span-full">No categories added to this store yet.</p>';
                        return;
                    }

                    container.innerHTML = '';
                    storeData.categories.forEach(category => {
                        const categoryCard = document.createElement('div');
                        categoryCard.className = 'category-card';
                        categoryCard.dataset.id = category.id;
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
                                    <select class="category-status-select px-2 py-1 border border-gray-300 rounded text-sm" data-id="${category.id}" data-original="${category.status}">
                                        <option value="active" ${category.status === 'active' ? 'selected' : ''}>Active</option>
                                        <option value="inactive" ${category.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    </select>
                                </div>
                            `;
                        container.appendChild(categoryCard);
                    });

                    // Add event listeners to status selects
                    document.querySelectorAll('.category-status-select').forEach(select => {
                        select.addEventListener('change', function () {
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
                } else {
                    container.innerHTML = '<p class="text-center text-red-500 py-4 col-span-full">Failed to load categories.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                container.innerHTML = '<p class="text-center text-red-500 py-4 col-span-full">Failed to load categories.</p>';
            });
    }

    // Load available categories that can be added to the store
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
                                const productCount = productCounts[category.id] || 0;
                                categoryCard.innerHTML = `
                                        <div class="flex items-start mb-2">
                                            <input type="checkbox" id="cat-${category.id}" class="category-checkbox mr-2 mt-1" value="${category.id}">
                                            <div>
                                                <label for="cat-${category.id}" class="font-bold text-lg cursor-pointer">${escapeHtml(category.name)}</label>
                                                <p class="text-gray-600 text-sm category-description">${escapeHtml(category.description || 'No description available')}</p>
                                                <p class="text-sm text-gray-500 mt-2"><i class="fas fa-box"></i> ${productCount} Products</p>
                                            </div>
                                        </div>
                                    `;
                                container.appendChild(categoryCard);
                                const checkbox = categoryCard.querySelector('input');
                                checkbox.addEventListener('change', function () {
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
                            // Fallback if counts can't be loaded
                            activeCategories.forEach(category => {
                                const categoryCard = document.createElement('div');
                                categoryCard.className = 'category-card';
                                categoryCard.innerHTML = `
                                        <div class="flex items-start mb-2">
                                            <input type="checkbox" id="cat-${category.id}" class="category-checkbox mr-2 mt-1" value="${category.id}">
                                            <div>
                                                <label for="cat-${category.id}" class="font-bold text-lg cursor-pointer">${escapeHtml(category.name)}</label>
                                                <p class="text-gray-600 text-sm category-description">${escapeHtml(category.description || 'No description available')}</p>
                                            </div>
                                        </div>
                                    `;
                                container.appendChild(categoryCard);
                                const checkbox = categoryCard.querySelector('input');
                                checkbox.addEventListener('change', function () {
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

    // Save category changes
    function saveCategories() {
        const activeTab = document.querySelector('.tab-button.active').dataset.tab;

        if (activeTab === 'add-tab' && selectedCategories.length === 0) {
            showToast('Please select at least one category to add', 'error');
            return;
        }

        if (activeTab === 'manage-tab' && Object.keys(categoryStatusChanges).length === 0) {
            showToast('No changes detected', 'error');
            return;
        }

        const button = document.getElementById('saveCategoriesBtn');
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
                    showToast('Categories updated successfully', 'success');
                    // Reset the selection/changes
                    selectedCategories = [];
                    categoryStatusChanges = {};
                    // Reload the page after a short delay to show the changes
                    setTimeout(() => {
                        loadStoreData();
                        loadStoreCategoriesForManagement();
                        loadAvailableCategories();
                    }, 1000);
                } else {
                    showToast(data.error || 'Failed to update categories', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating categories:', error);
                button.disabled = false;
                button.textContent = originalText;
                showToast('Failed to update categories. Please try again.', 'error');
            });
    }

    // ==================== PRODUCT MANAGEMENT ====================

    // Load store categories for product selection
    function loadStoreCategories() {
        const categorySelect = document.getElementById('productCategory');
        categorySelect.innerHTML = '<option value="">Select a category</option>';

        if (!storeData) {
            fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreDetails&id=${vendorId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.store) {
                        storeData = data.store;
                        populateCategorySelect(categorySelect);
                    } else {
                        showToast(data.error || "Failed to load store data", "error");
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                        categorySelect.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error loading store data:', error);
                    categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    categorySelect.disabled = true;
                });
        } else {
            populateCategorySelect(categorySelect);
        }
    }

    function populateCategorySelect(categorySelect) {
        if (storeData.categories && storeData.categories.length > 0) {
            const activeCats = storeData.categories.filter(cat => cat.status === 'active');
            if (activeCats.length === 0) {
                categorySelect.innerHTML = '<option value="">No active categories available</option>';
                categorySelect.disabled = true;
                return;
            }

            categorySelect.innerHTML = '<option value="">Select a category</option>';
            activeCats.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
            categorySelect.disabled = false;
        } else {
            categorySelect.innerHTML = '<option value="">No categories available</option>';
            categorySelect.disabled = true;
        }
    }

    // Load products for selected category
    function loadProductsForCategory(categoryId) {
        const productSelect = document.getElementById('productSelect');
        productSelect.innerHTML = '<option value="">Loading products...</option>';
        productSelect.disabled = true;

        fetch(`${BASE_URL}fetch/manageProfile.php?action=getProductsForCategory&store_id=${vendorId}&category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                productSelect.innerHTML = '<option value="">Select a product</option>';
                if (data.success && data.products && data.products.length > 0) {
                    data.products.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.id;
                        opt.textContent = p.name;
                        productSelect.appendChild(opt);
                    });
                    productSelect.disabled = false;
                } else {
                    productSelect.innerHTML = '<option value="">No products available for this category</option>';
                    productSelect.disabled = true;
                    document.getElementById('unitPricingContainer').classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading products for category:', error);
                productSelect.innerHTML = '<option value="">Error loading products</option>';
                productSelect.disabled = true;
            });
    }

    // Load units for pricing
    function loadUnitsForProduct() {
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getUnitsForProduct`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availableUnits = data.units;
                    prepareUnitPricingUI();
                } else {
                    availableUnits = [];
                    document.getElementById('unitPricingContainer').classList.add('hidden');
                    showToast('Failed to load units', 'error');
                }
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                availableUnits = [];
                document.getElementById('unitPricingContainer').classList.add('hidden');
                showToast('Error loading units', 'error');
            });
    }

    // Prepare UI for adding unit pricing
    function prepareUnitPricingUI() {
        const container = document.getElementById('unitPricingContainer');
        container.classList.remove('hidden');
        const wrapper = document.getElementById('lineItemsWrapper');
        wrapper.innerHTML = '';
        lineItemCount = 0;
        addLineItemRow();
    }

    // Add a new line item row for pricing
    function addLineItemRow() {
        lineItemCount++;
        const wrapper = document.getElementById('lineItemsWrapper');
        const row = document.createElement('div');
        row.classList.add('flex', 'gap-2', 'items-center', 'flex-wrap');

        // Unit select
        const unitSelect = document.createElement('select');
        unitSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        unitSelect.required = true;
        availableUnits.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.product_unit_of_measure_id;
            opt.textContent = `${u.si_unit} ${u.package_name}`;
            unitSelect.appendChild(opt);
        });

        // Price category select
        const priceCatSelect = document.createElement('select');
        priceCatSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        ['retail', 'wholesale', 'factory'].forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            priceCatSelect.appendChild(opt);
        });

        // Price input
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.min = '0';
        priceInput.step = 'any';
        priceInput.placeholder = 'Price';
        priceInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        priceInput.required = true;

        // Capacity input
        const capacityInput = document.createElement('input');
        capacityInput.type = 'number';
        capacityInput.min = '0';
        capacityInput.placeholder = 'Capacity';
        capacityInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');

        // Listen to price category change to update placeholder
        priceCatSelect.addEventListener('change', function () {
            if (this.value === 'wholesale' || this.value === 'factory') {
                capacityInput.placeholder = 'Max. Capacity';
            } else {
                capacityInput.placeholder = 'Capacity';
            }
        });

        // Add elements to row
        row.appendChild(unitSelect);
        row.appendChild(priceCatSelect);
        row.appendChild(priceInput);
        row.appendChild(capacityInput);

        // Remove button
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

    // Add product to store
    function addProductToStore(formData) {
        const submitBtn = document.querySelector('#addProductForm button[type="submit"]');
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
                    showToast('Product & pricing added successfully', 'success');
                    // Reset form
                    document.getElementById('addProductForm').reset();
                    document.getElementById('unitPricingContainer').classList.add('hidden');
                } else {
                    showToast(data.error || 'Failed to add product', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding product:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showToast('Failed to add product. Please try again.', 'error');
            });
    }

    // ==================== EVENT LISTENERS ====================

    document.addEventListener('DOMContentLoaded', function () {
        // Load initial store data
        loadStoreData();

        // Tab switching for category modal
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function () {
                const tabId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Manage Categories button
        document.getElementById('manage-categories-btn').addEventListener('click', function () {
            openModal('manageCategoriesModal');
            loadStoreCategoriesForManagement();
            loadAvailableCategories();
            // Reset selections
            selectedCategories = [];
            categoryStatusChanges = {};
        });

        // Save Categories button
        document.getElementById('saveCategoriesBtn').addEventListener('click', saveCategories);

        // Add Product button
        document.getElementById('add-product-btn').addEventListener('click', function () {
            openModal('addProductModal');
            loadStoreCategories();
        });

        // Category selection change
        document.getElementById('productCategory').addEventListener('change', function () {
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

        // Product selection change
        document.getElementById('productSelect').addEventListener('change', function () {
            const prodId = this.value;
            if (prodId) {
                loadUnitsForProduct();
            } else {
                document.getElementById('unitPricingContainer').classList.add('hidden');
            }
        });

        // Add Line Item button
        document.getElementById('addLineItemBtn').addEventListener('click', addLineItemRow);

        // Add Product Form submission
        document.getElementById('addProductForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const categoryId = document.getElementById('productCategory').value;
            const productId = document.getElementById('productSelect').value;

            if (!categoryId || !productId) {
                showToast('Please select Category and Product', 'error');
                return;
            }

            const rows = document.querySelectorAll('#lineItemsWrapper > div');
            if (rows.length === 0) {
                showToast('Please add at least one pricing entry', 'error');
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
                    unit_id: unitUuid,
                    price_category: priceCategory,
                    price: priceVal,
                    delivery_capacity: capacityVal
                });
            });

            if (lineItems.length === 0) {
                showToast('Please ensure you have filled the price for at least one line item', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('store_id', vendorId);
            formData.append('category_id', categoryId);
            formData.append('product_id', productId);
            formData.append('line_items', JSON.stringify(lineItems));

            addProductToStore(formData);
        });

        // Close modal when clicking outside
        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
    });
</script>