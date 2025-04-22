<!-- Products Tab -->
<div id="products-tab" class="tab-pane active">
    <!-- Filter and Sort -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div id="owner-actions" class="hidden mb-4 sm:mb-0">
            <button id="manage-categories-btn"
                class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition mr-2"><i
                    class="fas fa-tags mr-1"></i> Manage Categories</button>
            <button id="add-product-btn"
                class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition"><i
                    class="fas fa-plus-circle mr-1"></i> Add Product</button>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
            <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                <option value="">All Categories</option>
            </select>
            <select id="sort-products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                <option value="default">Default Sorting</option>
                <option value="latest">Latest</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
            </select>
            <input type="text" id="search-products" placeholder="Search products..."
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
        </div>
    </div>

    <!-- Products Grid -->
    <div id="products-container">
        <div class="col-span-full text-center py-8 text-gray-500">
            No products found for this vendor.
        </div>
    </div>
    <button id="loadMoreBtn"
        class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
        Load More Products
    </button>
</div>

<!-- Manage Categories Modal -->
<div id="manageCategoriesModal" class="modal z-100">
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
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('addProductModal')">Cancel</button>
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
                <button type="button" id="editAddLineItemBtn"
                    class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                    + Add Another Unit
                </button>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('editProductModal')">Cancel</button>
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
    // Products Tab JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // Load products when the page loads
        if (vendorId) {
            loadProducts(vendorId);
        }

        // Filter and sort products
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

        // Load more products
        document.getElementById('loadMoreBtn').addEventListener('click', function () {
            if (currentPage < totalPages) {
                loadProducts(vendorId, currentPage + 1);
            }
        });

        // Manage Categories
        document.getElementById('manage-categories-btn')?.addEventListener('click', function () {
            openModal('manageCategoriesModal');
            loadStoreCategoriesForManagement();
            loadAvailableCategories();
        });

        // Save Categories
        document.getElementById('saveCategoriesBtn')?.addEventListener('click', function () {
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
                        showToast('Categories updated successfully', 'success');
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

        // Add Product
        document.getElementById('add-product-btn')?.addEventListener('click', function () {
            openModal('addProductModal');
            loadStoreCategories();
        });

        // Add Product Form Submit
        document.getElementById('addProductForm')?.addEventListener('submit', function (e) {
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
                    unit_id: unitUuid,
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
                        showToast('Product & pricing added successfully', 'success');
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

        // Add Line Item
        document.getElementById('addLineItemBtn')?.addEventListener('click', function () {
            addLineItemRow();
        });

        // Edit Product Form
        document.getElementById('editProductForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const storeProductId = document.getElementById('editStoreProductId').value;
            const rows = document.querySelectorAll('#editLineItemsWrapper > div');
            if (rows.length === 0) {
                alert("Please add at least one pricing entry");
                return;
            }
            const lineItems = [];
            rows.forEach(function (row) {
                const selects = row.querySelectorAll('select');
                const inputs = row.querySelectorAll('input');
                if (selects.length < 1 || inputs.length < 2) return;
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
                        showToast('Product pricing updated successfully', 'success');
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

        // Edit Add Line Item
        document.getElementById('editAddLineItemBtn')?.addEventListener('click', function () {
            addEditLineItemRow();
        });

        // Delete Product
        document.getElementById('deleteCancelBtn')?.addEventListener('click', function () {
            pendingDeleteId = null;
            closeModal('deleteConfirmModal');
        });

        document.getElementById('deleteConfirmBtn')?.addEventListener('click', function () {
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
                        showToast('Product deleted successfully', 'success');
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
    });

    // Products Tab Functions
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
        renderProducts(filtered, true);
    }

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
                        ${escapeHtml(product.description || '').substring(0, 100)}...
                    </p>
                    <div class="border-t border-gray-200 pt-2">
                        ${pricingLines}
                    </div>
                    <div class="flex justify-end items-center pt-4 mt-auto space-x-2">
                        <button onclick="openEditProductModal('${product.store_product_id}')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors">Edit</button>
                        <button onclick="initiateDeleteProduct('${product.store_product_id}')" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">Delete</button>
                    </div>
                </div>
            `;
            container.appendChild(productCard);
        });
    }

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

    function loadStoreCategories() {
        const categorySelect = document.getElementById('productCategory');
        categorySelect.innerHTML = '<option value="">Select a category</option>';
        if (storeData.categories && storeData.categories.length > 0) {
            const activeCats = storeData.categories.filter(cat => cat.status === 'active');
            activeCats.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
            categorySelect.disabled = false;
            categorySelect.addEventListener('change', function () {
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
                        opt.value = p.id;
                        opt.textContent = p.name;
                        productSelect.appendChild(opt);
                    });
                    productSelect.disabled = false;
                    productSelect.addEventListener('change', function () {
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
        // Listen to price category change to update placeholder
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

    function addEditLineItemRow(item) {
        const wrapper = document.getElementById('editLineItemsWrapper');
        const row = document.createElement('div');
        row.classList.add('flex', 'gap-2', 'items-center', 'flex-wrap');
        const unitSelect = document.createElement('select');
        unitSelect.classList.add('border', 'rounded', 'px-2', 'py-1', 'text-sm');
        unitSelect.required = true;
        availableUnits.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.product_unit_of_measure_id;
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

    function openEditProductModal(storeProductUuid) {
        const product = allProducts.find(p => p.store_product_id === storeProductUuid);
        if (!product) {
            showToast("Product not found.", "error");
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
        document.getElementById('editStoreProductId').value = product.store_product_id;
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

    function initiateDeleteProduct(storeProductUuid) {
        pendingDeleteId = storeProductUuid;
        openModal('deleteConfirmModal');
    }

    // Make these functions globally available
    window.openEditProductModal = openEditProductModal;
    window.initiateDeleteProduct = initiateDeleteProduct;
</script>