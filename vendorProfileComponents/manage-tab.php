<div id="manage-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md px-6 mb-6">
        <div class="border-b border-gray-200 mb-6">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-red-600 rounded-t-lg text-red-600 font-medium"
                        id="products-tab" data-tabs-target="#products" type="button" role="tab" aria-controls="products"
                        aria-selected="true">
                        <i class="fas fa-box mr-2"></i>Products
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300"
                        id="categories-tab" data-tabs-target="#categories" type="button" role="tab"
                        aria-controls="categories" aria-selected="false">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </button>
                </li>
            </ul>
        </div>

        <div id="tab-content">
            <div class="block" id="products" role="tabpanel" aria-labelledby="products-tab">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold mb-2">Store Products</h2>
                        <p class="text-gray-600">Add new products to your store or manage existing ones</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Add New Product</h3>
                    <form id="addProductForm">
                        <div class="mb-4" id="productListContainer">
                            <label for="productSearchInput" class="block text-sm font-medium text-gray-700 mb-1">Select
                                Product *</label>
                            <div class="relative">
                                <div class="custom-select-container">
                                    <input type="text" id="productSearchInput"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Type to search for products..." autocomplete="off">
                                    <input type="hidden" id="selectedProductId" name="product_id">
                                    <input type="hidden" id="selectedCategoryId" name="category_id">
                                    <div id="productDropdown" class="custom-select-dropdown hidden">
                                        <div class="p-2 text-center text-gray-500">Loading products...</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Products shown here are those not already in your store.
                            </p>
                        </div>

                        <div id="unitPricingContainer" class="mt-4 hidden">
                            <p class="font-semibold text-sm mb-2">Add one or more unit/price entries:</p>
                            <div id="lineItemsWrapper" class="space-y-4"></div>
                            <button type="button" id="addLineItemBtn"
                                class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                                + Add Another Unit
                            </button>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                                Add Product
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold mb-4">Current Products</h3>
                    <div class="bg-gray-50 rounded-lg p-8 text-center">
                        <p class="text-gray-500">Your products will appear here</p>
                    </div>
                </div>
            </div>

            <div class="hidden" id="categories" role="tabpanel" aria-labelledby="categories-tab">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold mb-2">Store Categories</h2>
                        <p class="text-gray-600">Manage your store's product categories</p>
                    </div>
                    <button id="add-category-btn"
                        class="mt-4 md:mt-0 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Add New Category
                    </button>
                </div>

                <div id="store-categories-container" class="max-h-[600px] overflow-y-auto mb-4">
                    <div class="flex justify-center items-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Add New Category</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('addCategoryModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6">
                <p class="text-gray-600 mb-4">Add new categories to your store:</p>
                <div id="available-categories-container"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-96 overflow-y-auto mb-4">
                    <div class="flex justify-center items-center py-8 col-span-full">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end p-6 border-t">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('addCategoryModal')">
                    Cancel
                </button>
                <button type="button" id="saveCategoriesBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-select-container {
        position: relative;
        width: 100%;
    }

    .custom-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 300px;
        overflow-y: auto;
        background-color: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        z-index: 50;
        margin-top: 0.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .custom-select-option {
        padding: 0.5rem 1rem;
        cursor: pointer;
    }

    .custom-select-option:hover {
        background-color: #f7fafc;
    }

    .custom-select-category {
        padding: 0.25rem 1rem;
        font-weight: 600;
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.875rem;
    }

    .custom-select-no-results {
        padding: 1rem;
        text-align: center;
        color: #6b7280;
    }
</style>

<script>
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    window.openModal = function (modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    };

    window.closeModal = function (modalId) {
        document.getElementById(modalId).classList.add('hidden');
    };

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} transition-opacity duration-300 z-50`;

        toast.innerHTML = `
            <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 ${type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'} rounded-lg">
                <i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-xmark'}"></i>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:text-green-700' : 'bg-red-50 text-red-500 hover:text-red-700'} rounded-lg p-1.5 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }

    function loadStoreData() {
        fetch(`${BASE_URL}fetch/manageProfile?action=getStoreDetails&id=${vendorId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;
                    loadStoreCategoriesForDisplay();
                } else {
                    showToast(data.error || "Failed to load store data", "error");
                }
            })
            .catch(error => {
                console.error('Error loading store data:', error);
                showToast("Failed to load store data", "error");
            });
    }

    function loadStoreCategoriesForDisplay() {
        const container = document.getElementById('store-categories-container');
        container.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div></div>';

        fetch(`${BASE_URL}fetch/manageProfile?action=getStoreDetails&id=${vendorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;

                    if (!storeData.categories || storeData.categories.length === 0) {
                        container.innerHTML = '<div class="text-center py-8"><p class="text-gray-500">No categories added to this store yet.</p><p class="mt-2 text-sm text-gray-400">Click "Add New Category" to get started</p></div>';
                        return;
                    }

                    container.innerHTML = '';
                    const categoryList = document.createElement('div');
                    categoryList.className = 'space-y-4';

                    storeData.categories.forEach(category => {
                        const categoryItem = document.createElement('div');
                        categoryItem.className = 'bg-white border border-gray-200 rounded-lg shadow-sm p-4 transition-all hover:shadow-md';
                        categoryItem.dataset.id = category.id;

                        const statusClass = category.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                        const statusText = category.status === 'active' ? 'Active' : 'Inactive';

                        categoryItem.innerHTML = `
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div class="mb-3 sm:mb-0">
                                    <div class="flex items-center">
                                        <h3 class="font-bold text-lg">${escapeHtml(category.name)}</h3>
                                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                            ${statusText}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 text-sm mt-1 line-clamp-2 overflow-hidden text-ellipsis">
                                        ${escapeHtml(category.description || 'No description available')}
                                    </p>
                                    <div class="mt-2 text-sm text-gray-500">
                                        <span><i class="fas fa-box"></i> ${category.product_count || 0} Products</span>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <label class="inline-flex items-center cursor-pointer mr-4">
                                        <input type="checkbox" class="sr-only peer category-toggle" 
                                            data-id="${category.id}" 
                                            ${category.status === 'active' ? 'checked' : ''}>
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                        <span class="ml-2 text-sm font-medium text-gray-900">
                                            ${category.status === 'active' ? 'Active' : 'Inactive'}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        `;

                        categoryList.appendChild(categoryItem);
                    });

                    container.appendChild(categoryList);

                    document.querySelectorAll('.category-toggle').forEach(toggle => {
                        toggle.addEventListener('change', function () {
                            const categoryId = this.dataset.id;
                            const newStatus = this.checked ? 'active' : 'inactive';
                            const statusLabel = this.parentElement.querySelector('span');
                            const loadingIndicator = document.createElement('span');

                            statusLabel.textContent = 'Updating...';
                            this.disabled = true;

                            const badge = this.closest('.flex').parentElement.querySelector('.rounded-full');
                            badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
                            badge.textContent = 'Updating...';

                            fetch(`${BASE_URL}fetch/manageProfile?action=updateCategoryStatus`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    store_id: vendorId,
                                    category_updates: { [categoryId]: newStatus }
                                })
                            })
                                .then(response => response.json())
                                .then(data => {
                                    this.disabled = false;

                                    if (data.success) {
                                        statusLabel.textContent = newStatus === 'active' ? 'Active' : 'Inactive';

                                        if (newStatus === 'active') {
                                            badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                            badge.textContent = 'Active';
                                        } else {
                                            badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                                            badge.textContent = 'Inactive';
                                        }

                                        showToast(`Category status updated to ${newStatus}`, 'success');
                                    } else {
                                        this.checked = !this.checked;
                                        statusLabel.textContent = !this.checked ? 'Active' : 'Inactive';

                                        if (!this.checked) {
                                            badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                            badge.textContent = 'Active';
                                        } else {
                                            badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                                            badge.textContent = 'Inactive';
                                        }

                                        showToast(data.error || 'Failed to update category status', 'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error updating category status:', error);
                                    this.disabled = false;
                                    this.checked = !this.checked;
                                    statusLabel.textContent = !this.checked ? 'Active' : 'Inactive';

                                    if (!this.checked) {
                                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                                        badge.textContent = 'Active';
                                    } else {
                                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                                        badge.textContent = 'Inactive';
                                    }

                                    showToast('Failed to update category status. Please try again.', 'error');
                                });
                        });
                    });
                } else {
                    container.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load categories.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                container.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load categories.</p>';
            });
    }

    function loadAvailableCategories() {
        const container = document.getElementById('available-categories-container');
        container.innerHTML = '<div class="flex justify-center items-center py-8 col-span-full"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div></div>';

        fetch(`${BASE_URL}fetch/manageProfile?action=getAvailableCategories&store_id=${vendorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.categories && data.categories.length > 0) {
                    const activeCategories = data.categories.filter(cat => cat.status === 'active');
                    if (activeCategories.length === 0) {
                        container.innerHTML = '<p class="text-center text-gray-500 py-4 col-span-full">No additional active categories available.</p>';
                        return;
                    }

                    container.innerHTML = '';
                    fetch(`${BASE_URL}fetch/manageProfile?action=getCategoryProductCounts`)
                        .then(r => r.json())
                        .then(countData => {
                            const productCounts = countData.success ? countData.counts : {};
                            activeCategories.forEach(category => {
                                const categoryCard = document.createElement('div');
                                categoryCard.className = 'border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow';
                                const productCount = productCounts[category.id] || 0;
                                categoryCard.innerHTML = `
                                    <div class="flex items-start">
                                        <input type="checkbox" id="cat-${category.id}" class="category-checkbox mt-1 h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500" value="${category.id}">
                                        <div class="ml-3">
                                            <label for="cat-${category.id}" class="font-medium text-gray-900 cursor-pointer">${escapeHtml(category.name)}</label>
                                            <p class="text-gray-600 text-sm mt-1 line-clamp-2 overflow-hidden text-ellipsis">${escapeHtml(category.description || 'No description available')}</p>
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
                            activeCategories.forEach(category => {
                                const categoryCard = document.createElement('div');
                                categoryCard.className = 'border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow';
                                categoryCard.innerHTML = `
                                    <div class="flex items-start">
                                        <input type="checkbox" id="cat-${category.id}" class="category-checkbox mt-1 h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500" value="${category.id}">
                                        <div class="ml-3">
                                            <label for="cat-${category.id}" class="font-medium text-gray-900 cursor-pointer">${escapeHtml(category.name)}</label>
                                            <p class="text-gray-600 text-sm mt-1 line-clamp-2 overflow-hidden text-ellipsis">${escapeHtml(category.description || 'No description available')}</p>
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

    function saveCategories() {
        if (selectedCategories.length === 0) {
            showToast('Please select at least one category to add', 'error');
            return;
        }

        const button = document.getElementById('saveCategoriesBtn');
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Saving...';

        fetch(`${BASE_URL}fetch/manageProfile?action=updateStoreCategories`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
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
                    showToast('Categories added successfully', 'success');
                    selectedCategories = [];
                    loadStoreCategoriesForDisplay();
                } else {
                    showToast(data.error || 'Failed to add categories', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding categories:', error);
                button.disabled = false;
                button.textContent = originalText;
                showToast('Failed to add categories. Please try again.', 'error');
            });
    }

    function loadProductsForStore() {
        const dropdown = document.getElementById('productDropdown');
        dropdown.innerHTML = '<div class="p-2 text-center text-gray-500">Loading products...</div>';

        fetch(`${BASE_URL}fetch/manageProfile?action=getProductsNotInStore&store_id=${vendorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products && data.products.length > 0) {
                    // Store all products for search functionality
                    window.allProducts = data.products;

                    // Group products by category
                    const productsByCategory = {};
                    data.products.forEach(p => {
                        if (!productsByCategory[p.category_name]) {
                            productsByCategory[p.category_name] = [];
                        }
                        productsByCategory[p.category_name].push(p);
                    });

                    // Populate dropdown
                    populateProductDropdown(productsByCategory);
                } else {
                    dropdown.innerHTML = '<div class="custom-select-no-results">No products available</div>';
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                dropdown.innerHTML = '<div class="custom-select-no-results">Error loading products</div>';
            });
    }

    function populateProductDropdown(productsByCategory, filter = '') {
        const dropdown = document.getElementById('productDropdown');
        dropdown.innerHTML = '';

        let hasResults = false;

        // Sort categories alphabetically
        const sortedCategories = Object.keys(productsByCategory).sort();

        sortedCategories.forEach(category => {
            const products = productsByCategory[category].filter(p =>
                p.name.toLowerCase().includes(filter.toLowerCase())
            );

            if (products.length > 0) {
                hasResults = true;

                // Add category header
                const categoryHeader = document.createElement('div');
                categoryHeader.className = 'custom-select-category';
                categoryHeader.textContent = category;
                dropdown.appendChild(categoryHeader);

                // Add products under this category
                products.forEach(product => {
                    const option = document.createElement('div');
                    option.className = 'custom-select-option';
                    option.textContent = product.name;
                    option.dataset.id = product.id;
                    option.dataset.categoryId = product.category_id;
                    option.dataset.name = product.name;

                    option.addEventListener('click', function () {
                        selectProduct(this.dataset.id, this.dataset.categoryId, this.dataset.name);
                    });

                    dropdown.appendChild(option);
                });
            }
        });

        if (!hasResults) {
            dropdown.innerHTML = '<div class="custom-select-no-results">No matching products found</div>';
        }
    }

    function selectProduct(productId, categoryId, productName) {
        document.getElementById('selectedProductId').value = productId;
        document.getElementById('selectedCategoryId').value = categoryId;
        document.getElementById('productSearchInput').value = productName;
        document.getElementById('productDropdown').classList.add('hidden');

        // Load units for pricing
        loadUnitsForProduct();
    }

    function initProductSearch() {
        const searchInput = document.getElementById('productSearchInput');
        const dropdown = document.getElementById('productDropdown');

        // Helper to group and filter products
        function groupAndFilterProducts(filterValue) {
            const productsByCategory = {};

            window.allProducts.forEach(p => {
                if (!productsByCategory[p.category_name]) {
                    productsByCategory[p.category_name] = [];
                }
                productsByCategory[p.category_name].push(p);
            });

            populateProductDropdown(productsByCategory, filterValue);
        }

        // Show dropdown and populate on focus
        searchInput.addEventListener('focus', function () {
            if (window.allProducts && window.allProducts.length > 0) {
                dropdown.classList.remove('hidden');
                groupAndFilterProducts(this.value);
            } else {
                loadProductsForStore(); // assumed to load and update `window.allProducts`
            }
        });

        // Filter products as user types
        searchInput.addEventListener('input', function () {
            if (window.allProducts && window.allProducts.length > 0) {
                dropdown.classList.remove('hidden');
                groupAndFilterProducts(this.value);
            }
        });

        // Hide dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function loadUnitsForProduct() {
        fetch(`${BASE_URL}fetch/manageProfile?action=getUnitsForProduct`)
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
        row.classList.add('flex', 'flex-wrap', 'gap-2', 'items-center');

        const unitSelect = document.createElement('select');
        unitSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        unitSelect.required = true;
        availableUnits.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.product_unit_of_measure_id;
            opt.textContent = `${u.si_unit} ${u.package_name}`;
            unitSelect.appendChild(opt);
        });

        const packageSizeInput = document.createElement('input');
        packageSizeInput.type = 'number';
        packageSizeInput.min = '1';
        packageSizeInput.value = '1';
        packageSizeInput.placeholder = 'Package Size';
        packageSizeInput.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        packageSizeInput.required = true;

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

        priceCatSelect.addEventListener('change', function () {
            if (this.value === 'wholesale' || this.value === 'factory') {
                capacityInput.placeholder = 'Max. Capacity';
            } else {
                capacityInput.placeholder = 'Capacity';
            }
        });

        row.appendChild(unitSelect);
        row.appendChild(packageSizeInput);
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

    function addProductToStore(formData) {
        const submitBtn = document.querySelector('#addProductForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';

        fetch(`${BASE_URL}fetch/manageProfile?action=addStoreProduct`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                if (data.success) {
                    showToast('Product & pricing added successfully', 'success');
                    document.getElementById('addProductForm').reset();
                    document.getElementById('unitPricingContainer').classList.add('hidden');
                    document.getElementById('productSearchInput').value = '';
                    document.getElementById('selectedProductId').value = '';
                    document.getElementById('selectedCategoryId').value = '';
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

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize variables
        window.selectedCategories = [];
        window.availableUnits = [];
        window.lineItemCount = 0;
        window.allProducts = [];

        loadStoreData();
        loadProductsForStore();
        initProductSearch();

        const tabButtons = document.querySelectorAll('[role="tab"]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');

        tabButtons.forEach(button => {
            button.addEventListener('click', function () {
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-red-600', 'text-red-600');
                    btn.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                    btn.setAttribute('aria-selected', 'false');
                });

                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                this.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                this.classList.add('border-red-600', 'text-red-600');
                this.setAttribute('aria-selected', 'true');

                const tabId = this.getAttribute('data-tabs-target').substring(1);
                document.getElementById(tabId).classList.remove('hidden');
            });
        });

        document.getElementById('add-category-btn').addEventListener('click', function () {
            openModal('addCategoryModal');
            loadAvailableCategories();
            selectedCategories = [];
        });

        document.getElementById('saveCategoriesBtn').addEventListener('click', saveCategories);

        document.getElementById('addLineItemBtn').addEventListener('click', addLineItemRow);

        document.getElementById('addProductForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const productId = document.getElementById('selectedProductId').value;
            const categoryId = document.getElementById('selectedCategoryId').value;

            if (!productId || !categoryId) {
                showToast('Please select a Product', 'error');
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
                if (selects.length < 2 || inputs.length < 3) return;

                const unitUuid = selects[0].value;
                const packageSize = inputs[0].value || 1;
                const priceCategory = selects[1].value;
                const priceVal = inputs[1].value;
                const capacityVal = inputs[2].value || null;

                if (!unitUuid || !priceVal) return;

                lineItems.push({
                    unit_id: unitUuid,
                    package_size: packageSize,
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
            formData.append('product_id', productId);
            formData.append('line_items', JSON.stringify(lineItems));

            addProductToStore(formData);
        });

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                closeModal(event.target.id);
            }
        });
    });
</script>