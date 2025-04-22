<!-- Manage Products & Categories Tab -->
<div id="manage-tab" class="tab-pane hidden">
    <!-- Sub-tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button
                class="border-primary text-primary font-medium py-4 px-1 border-b-2 whitespace-nowrap manage-subtab-btn"
                data-subtab="categories">
                <i class="fa-solid fa-tags mr-2"></i> Categories
            </button>
            <button
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap manage-subtab-btn"
                data-subtab="products">
                <i class="fa-solid fa-box-open mr-2"></i> Products
            </button>
        </nav>
    </div>

    <!-- Categories Sub-tab -->
    <div id="categories-subtab" class="manage-subtab">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Manage Categories</h2>
            <button id="add-category-btn"
                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition">
                <i class="fas fa-plus-circle mr-1"></i> Add Category
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Description</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Products</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading categories...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products Sub-tab -->
    <div id="products-subtab" class="manage-subtab hidden">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Manage Products</h2>
            <button id="add-product-btn"
                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition">
                <i class="fas fa-plus-circle mr-1"></i> Add Product
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price
                            Range</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Units
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading products...</td>
                    </tr>
                </tbody>
            </table>
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

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Add Category</h2>
            <span class="close" onclick="closeModal('addCategoryModal')">&times;</span>
        </div>
        <form id="addCategoryForm">
            <div class="mb-4">
                <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                <input type="text" id="categoryName" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    required>
            </div>
            <div class="mb-4">
                <label for="categoryDescription"
                    class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="categoryDescription" class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                    rows="3"></textarea>
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('addCategoryModal')">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Add Category</button>
            </div>
        </form>
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
        <p class="mb-4" id="delete-confirm-message">Are you sure you want to delete this item?</p>
        <div class="flex justify-end">
            <button id="deleteCancelBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2">Cancel</button>
            <button id="deleteConfirmBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">Delete</button>
        </div>
    </div>
</div>

<script>
    // Manage Tab JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // Sub-tab switching
        const subTabs = document.querySelectorAll('.manage-subtab-btn');
        const subTabPanes = document.querySelectorAll('.manage-subtab');

        subTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Remove active class from all tabs
                subTabs.forEach(t => {
                    t.classList.remove('border-primary', 'text-primary');
                    t.classList.add('border-transparent', 'text-gray-500');
                });

                // Add active class to clicked tab
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-primary', 'text-primary');

                // Hide all tab panes
                subTabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                });

                // Show the selected tab pane
                const tabName = this.getAttribute('data-subtab');
                document.getElementById(tabName + '-subtab').classList.remove('hidden');

                // Load data for the selected tab
                if (tabName === 'categories') {
                    loadCategoriesForManagement();
                } else if (tabName === 'products') {
                    loadProductsForManagement();
                }
            });
        });

        // Manage Categories
        document.getElementById('add-category-btn')?.addEventListener('click', function () {
            openModal('addCategoryModal');
        });

        // Add Category Form Submit
        document.getElementById('addCategoryForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const categoryName = document.getElementById('categoryName').value;
            const categoryDescription = document.getElementById('categoryDescription').value;

            if (!categoryName) {
                alert('Please enter a category name');
                return;
            }

            const formData = new FormData();
            formData.append('name', categoryName);
            formData.append('description', categoryDescription);

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            fetch(`${BASE_URL}fetch/manageProfile.php?action=addCategory`, {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    if (data.success) {
                        closeModal('addCategoryModal');
                        showToast('Category added successfully', 'success');
                        loadCategoriesForManagement();
                    } else {
                        alert(data.error || 'Failed to add category');
                    }
                })
                .catch(error => {
                    console.error('Error adding category:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    alert('Failed to add category. Please try again.');
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
                        loadProductsForManagement();
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
                        loadProductsForManagement();
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

        // Delete Confirmation
        document.getElementById('deleteCancelBtn')?.addEventListener('click', function () {
            pendingDeleteId = null;
            pendingDeleteType = null;
            closeModal('deleteConfirmModal');
        });

        document.getElementById('deleteConfirmBtn')?.addEventListener('click', function () {
            if (!pendingDeleteId || !pendingDeleteType) {
                return;
            }

            let endpoint = '';
            let payload = {};

            if (pendingDeleteType === 'product') {
                endpoint = `${BASE_URL}fetch/manageProfile.php?action=deleteProduct`;
                payload = { id: pendingDeleteId };
            } else if (pendingDeleteType === 'category') {
                endpoint = `${BASE_URL}fetch/manageProfile.php?action=deleteCategory`;
                payload = { id: pendingDeleteId, store_id: vendorId };
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(`${pendingDeleteType.charAt(0).toUpperCase() + pendingDeleteType.slice(1)} deleted successfully`, 'success');
                        if (pendingDeleteType === 'product') {
                            loadProductsForManagement();
                        } else if (pendingDeleteType === 'category') {
                            loadCategoriesForManagement();
                        }
                    } else {
                        alert(data.error || `Failed to delete ${pendingDeleteType}`);
                    }
                })
                .catch(err => {
                    console.error(`Error deleting ${pendingDeleteType}:`, err);
                    alert(`Failed to delete ${pendingDeleteType}. Please try again.`);
                })
                .finally(() => {
                    pendingDeleteId = null;
                    pendingDeleteType = null;
                    closeModal('deleteConfirmModal');
                });
        });

        // Load initial data if we're on the manage tab
        if (document.getElementById('manage-tab').classList.contains('active')) {
            loadCategoriesForManagement();
        }
    });

    // Management Tab Functions
    // Fix the loadCategoriesForManagement function to properly fetch categories from the API
    function loadCategoriesForManagement() {
        const tableBody = document.getElementById('categories-table-body');
        tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading categories...</td></tr>';

        // Fetch available categories from the API
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getAvailableCategories&store_id=${vendorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.categories && data.categories.length > 0) {
                    // Fetch category product counts
                    fetch(`${BASE_URL}fetch/manageProfile.php?action=getCategoryProductCounts`)
                        .then(r => r.json())
                        .then(countData => {
                            const productCounts = countData.success ? countData.counts : {};
                            tableBody.innerHTML = '';

                            data.categories.forEach(category => {
                                const row = document.createElement('tr');
                                const statusClass = category.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                const statusText = category.status === 'active' ? 'Active' : 'Inactive';
                                const productCount = productCounts[category.id] || 0;

                                row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">${escapeHtml(category.name)}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500">${escapeHtml(category.description || 'No description')}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">${productCount}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                                        ${statusText}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="editCategory('${category.id}')">Edit</button>
                                    <button class="text-red-600 hover:text-red-900" onclick="initiateDeleteCategory('${category.id}', '${escapeHtml(category.name)}')">Delete</button>
                                </td>
                            `;
                                tableBody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading category product counts:', error);
                            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading category product counts</td></tr>';
                        });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No categories found</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading categories</td></tr>';
            });
    }

    // Fix the loadProductsForManagement function to properly fetch products
    function loadProductsForManagement() {
        const tableBody = document.getElementById('products-table-body');
        tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading products...</td></tr>';

        // First, get all categories to use for product loading
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getAvailableCategories&store_id=${vendorId}`)
            .then(response => response.json())
            .then(categoryData => {
                if (categoryData.success && categoryData.categories && categoryData.categories.length > 0) {
                    const categories = categoryData.categories;
                    let allProducts = [];
                    let loadedCategories = 0;
                    const totalCategories = categories.length;

                    // For each category, fetch its products
                    categories.forEach(category => {
                        fetch(`${BASE_URL}fetch/manageProfile.php?action=getProductsForCategory&store_id=${vendorId}&category_id=${category.id}`)
                            .then(response => response.json())
                            .then(data => {
                                loadedCategories++;

                                if (data.success && data.products && data.products.length > 0) {
                                    // Add category name to each product
                                    const productsWithCategory = data.products.map(product => {
                                        return {
                                            ...product,
                                            category_name: category.name,
                                            category_id: category.id
                                        };
                                    });

                                    allProducts = [...allProducts, ...productsWithCategory];
                                }

                                // When all categories are loaded, render the products table
                                if (loadedCategories === totalCategories) {
                                    renderProductsTable(allProducts);
                                }
                            })
                            .catch(error => {
                                console.error(`Error loading products for category ${category.id}:`, error);
                                loadedCategories++;

                                if (loadedCategories === totalCategories) {
                                    renderProductsTable(allProducts);
                                }
                            });
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No categories found to load products from</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading categories for products:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading categories for products</td></tr>';
            });

        // Helper function to render the products table
        function renderProductsTable(products) {
            if (products.length > 0) {
                tableBody.innerHTML = '';
                products.forEach(product => {
                    const row = document.createElement('tr');

                    row.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                ${product.name.substring(0, 2).toUpperCase()}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${escapeHtml(product.name)}</div>
                                <div class="text-sm text-gray-500">${escapeHtml(product.description || '').substring(0, 50)}${product.description && product.description.length > 50 ? '...' : ''}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${escapeHtml(product.category_name || 'N/A')}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">Contact for pricing</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">Various</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-blue-600 hover:text-blue-900 mr-3" onclick="addProductToStore('${product.id}', '${product.category_id}')">Add</button>
                        <button class="text-red-600 hover:text-red-900" onclick="initiateDeleteProduct('${product.id}', '${escapeHtml(product.name)}')">Delete</button>
                    </td>
                `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No products found</td></tr>';
            }
        }
    }

    // Add function to add a product to the store
    function addProductToStore(productId, categoryId) {
        openModal('addProductModal');
        document.getElementById('productCategory').value = categoryId;
        loadProductsForCategory(categoryId);

        // Pre-select the product in the dropdown once it's loaded
        const checkProductLoaded = setInterval(() => {
            const productSelect = document.getElementById('productSelect');
            if (!productSelect.disabled) {
                for (let i = 0; i < productSelect.options.length; i++) {
                    if (productSelect.options[i].value === productId) {
                        productSelect.selectedIndex = i;
                        productSelect.dispatchEvent(new Event('change'));
                        clearInterval(checkProductLoaded);
                        break;
                    }
                }
            }
        }, 100);
    }

    // Make this function globally available
    window.addProductToStore = addProductToStore;

    function editCategory(categoryId) {
        // Implement category editing functionality
        showToast("Category editing not implemented yet", "error");
    }

    function initiateDeleteCategory(categoryId, categoryName) {
        pendingDeleteId = categoryId;
        pendingDeleteType = 'category';
        document.getElementById('delete-confirm-message').textContent = `Are you sure you want to delete the category "${categoryName}"?`;
        openModal('deleteConfirmModal');
    }

    function initiateDeleteProduct(productId, productName) {
        pendingDeleteId = productId;
        pendingDeleteType = 'product';
        document.getElementById('delete-confirm-message').textContent = `Are you sure you want to delete the product "${productName}"?`;
        openModal('deleteConfirmModal');
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

    // Make these functions globally available
    window.openEditProductModal = openEditProductModal;
    window.initiateDeleteProduct = initiateDeleteProduct;
    window.initiateDeleteCategory = initiateDeleteCategory;
    window.editCategory = editCategory;
</script>