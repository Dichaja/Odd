<div id="manage-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md px-6 mb-6">
        <div class="border-b border-gray-200 mb-6">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 border-red-600 rounded-t-lg text-red-600 font-medium"
                        id="categories-tab" data-tabs-target="#categories" type="button" role="tab"
                        aria-controls="categories" aria-selected="true">
                        <i class="fas fa-tags mr-2"></i>Categories
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button
                        class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300"
                        id="products-tab" data-tabs-target="#products" type="button" role="tab" aria-controls="products"
                        aria-selected="false">
                        <i class="fas fa-box mr-2"></i>Products
                    </button>
                </li>
            </ul>
        </div>

        <div id="tab-content">
            <div class="block" id="categories" role="tabpanel" aria-labelledby="categories-tab">
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

            <div class="hidden" id="products" role="tabpanel" aria-labelledby="products-tab">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold mb-2">Store Products</h2>
                        <p class="text-gray-600">Add new products to your store or manage existing ones</p>
                    </div>
                    <button id="add-product-btn"
                        class="mt-4 md:mt-0 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i> Add Product
                    </button>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <p class="text-gray-500">Select a category and add products to your store</p>
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

<div id="addProductModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Add Product</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('addProductModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <form id="addProductForm" class="p-6">
                <div class="mb-4">
                    <label for="productCategory" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select id="productCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="">Select a category</option>
                    </select>
                </div>

                <div class="mb-4" id="productListContainer">
                    <label for="productSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Product
                        *</label>
                    <select id="productSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required
                        disabled>
                        <option value="">Select a product</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Products shown here are those in that category, but <strong>not</strong> already in your store.
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
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                        onclick="closeModal('addProductModal')">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    function loadStoreCategories() {
        const categorySelect = document.getElementById('productCategory');
        categorySelect.innerHTML = '<option value="">Select a category</option>';

        if (!storeData) {
            fetch(`${BASE_URL}fetch/manageProfile?action=getStoreDetails&id=${vendorId}`)
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

    function loadProductsForCategory(categoryId) {
        const productSelect = document.getElementById('productSelect');
        productSelect.innerHTML = '<option value="">Loading products...</option>';
        productSelect.disabled = true;

        fetch(`${BASE_URL}fetch/manageProfile?action=getProductsForCategory&store_id=${vendorId}&category_id=${categoryId}`)
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
                    closeModal('addProductModal');
                    showToast('Product & pricing added successfully', 'success');
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

    document.addEventListener('DOMContentLoaded', function () {
        loadStoreData();

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

        document.getElementById('add-product-btn').addEventListener('click', function () {
            openModal('addProductModal');
            loadStoreCategories();
        });

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

        document.getElementById('productSelect').addEventListener('change', function () {
            const prodId = this.value;
            if (prodId) {
                loadUnitsForProduct();
            } else {
                document.getElementById('unitPricingContainer').classList.add('hidden');
            }
        });

        document.getElementById('addLineItemBtn').addEventListener('click', addLineItemRow);

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

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                closeModal(event.target.id);
            }
        });
    });
</script>