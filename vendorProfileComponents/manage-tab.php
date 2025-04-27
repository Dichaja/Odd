<div id="manage-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md px-6 mb-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-2">Store Products</h2>
            <p class="text-gray-600">Add new products to your store or manage existing ones</p>
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
                    <p class="font-semibold text-sm mb-2">Add one or more pricing entries:</p>
                    <div id="lineItemsWrapper" class="space-y-4"></div>
                    <button type="button" id="addLineItemBtn"
                        class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                        + Add Another Entry
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
            <div id="productsList" class="space-y-4">
                <div class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
                </div>
            </div>
            <div id="pagination" class="mt-6 flex justify-center space-x-2"></div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Edit Product Pricing</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('editProductModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <form id="editProductForm">
                    <input type="hidden" id="editStoreProductId" name="store_product_id">
                    <div class="mb-4">
                        <h3 id="editProductName" class="text-lg font-medium"></h3>
                        <p id="editProductCategory" class="text-sm text-gray-600"></p>
                    </div>

                    <div id="editPricingContainer" class="mt-4">
                        <p class="font-semibold text-sm mb-2">Pricing entries:</p>
                        <div id="editLineItemsWrapper" class="space-y-4"></div>
                        <button type="button" id="editAddLineItemBtn"
                            class="mt-2 px-3 py-1 bg-blue-500 text-white text-sm rounded">
                            + Add Another Entry
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex justify-end p-6 border-t">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('editProductModal')">
                    Cancel
                </button>
                <button type="button" id="saveProductChangesBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Confirm Delete</h3>
                <p class="text-gray-600">Are you sure you want to delete this product from your store? This action
                    cannot be undone.</p>
                <p id="deleteProductName" class="font-medium mt-2"></p>
            </div>
            <div class="flex justify-end p-6 border-t">
                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                    onclick="closeModal('deleteConfirmModal')">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                    Delete
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

    .product-card {
        transition: all 0.2s ease;
    }

    .product-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .price-tag {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .price-retail {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .price-wholesale {
        background-color: #dcfce7;
        color: #15803d;
    }

    .price-factory {
        background-color: #fef3c7;
        color: #92400e;
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
                } else {
                    showToast(data.error || "Failed to load store data", "error");
                }
            })
            .catch(error => {
                console.error('Error loading store data:', error);
                showToast("Failed to load store data", "error");
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

        // Load package mappings for pricing
        loadPackageMappingsForProduct(productId);
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

    function loadPackageMappingsForProduct(productId) {
        fetch(`${BASE_URL}fetch/manageProfile?action=getPackageNamesForProduct&product_id=${productId}`)
            .then(r => r.json()).then(data => {
                if (data.success) {
                    availablePackageMappings = data.mappings;
                    ensureSIUnits();
                    preparePricingUI();
                } else {
                    showToast('Failed to load package mappings', 'error');
                }
            }).catch(err => {
                console.error(err);
                showToast('Error loading mappings', 'error');
            });
    }

    function ensureSIUnits() {
        if (!availableSIUnits || availableSIUnits.length === 0) {
            fetch(`${BASE_URL}fetch/manageProfile?action=getSIUnits`)
                .then(r => r.json()).then(data => {
                    if (data.success) {
                        availableSIUnits = data.siUnits;
                    }
                }).catch(console.error);
        }
    }

    function preparePricingUI() {
        const container = document.getElementById('unitPricingContainer');
        container.classList.remove('hidden');
        const wrapper = document.getElementById('lineItemsWrapper');
        wrapper.innerHTML = '';
        lineItemCount = 0;
        addLineItemRow();
    }

    function addLineItemRow(container = 'lineItemsWrapper', existingData = null) {
        lineItemCount++;
        const wrapper = document.getElementById(container);
        const row = document.createElement('div');
        row.classList.add('space-y-2', 'p-4', 'border', 'rounded-lg', 'relative');

        // PACKAGE MAPPING dropdown
        const pkgContainer = document.createElement('div');
        pkgContainer.innerHTML = `
            <label class="block text-sm font-medium text-gray-700">Package</label>
            <div class="relative custom-select-container">
                <input type="text" class="pkg-search-input w-full px-3 py-2 border rounded" placeholder="Search package..." autocomplete="off"/>
                <input type="hidden" name="package_mapping_id" class="pkg-mapping-id"/>
                <div class="pkg-dropdown custom-select-dropdown hidden"></div>
            </div>`;
        row.appendChild(pkgContainer);

        // SI UNIT dropdown + fallback
        const siContainer = document.createElement('div');
        siContainer.innerHTML = `
            <label class="block text-sm font-medium text-gray-700">SI Unit</label>
            <div class="relative custom-select-container">
                <input type="text" class="si-search-input w-full px-3 py-2 border rounded" placeholder="Search SI unit..." autocomplete="off"/>
                <input type="hidden" name="si_unit_id" class="si-unit-id"/>
                <div class="si-dropdown custom-select-dropdown hidden"></div>
            </div>
            <div class="si-fallback hidden mt-2">
                <input type="text" class="new-si-input w-full px-3 py-2 border rounded" placeholder="Enter new SI unit"/>
                <button type="button" class="mt-2 px-3 py-1 bg-green-500 text-white rounded">Add SI Unit</button>
            </div>`;
        row.appendChild(siContainer);

        // PACKAGE SIZE, PRICE CATEGORY, PRICE, CAPACITY
        const other = document.createElement('div');
        other.classList.add('grid', 'grid-cols-1', 'md:grid-cols-4', 'gap-4');
        other.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Package Size</label>
                <input type="number" name="package_size" value="1" min="1" required class="w-full px-2 py-1 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Price Category</label>
                <select name="price_category" class="w-full px-2 py-1 border rounded">
                    <option value="retail">Retail</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="factory">Factory</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Price</label>
                <input type="number" step="any" name="price" required class="w-full px-2 py-1 border rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Delivery Capacity</label>
                <input type="number" name="delivery_capacity" class="w-full px-2 py-1 border rounded">
            </div>`;
        row.appendChild(other);

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'absolute top-2 right-2 px-2 py-1 text-xs bg-red-200 text-red-800 rounded';
        removeBtn.addEventListener('click', () => row.remove());
        row.appendChild(removeBtn);

        wrapper.appendChild(row);

        // Initialize dropdown behaviors
        initPackageDropdown(row);
        initSiDropdown(row);

        // If we have existing data, populate the fields
        if (existingData) {
            const pkgInput = row.querySelector('.pkg-search-input');
            const pkgId = row.querySelector('.pkg-mapping-id');
            const siInput = row.querySelector('.si-search-input');
            const siId = row.querySelector('.si-unit-id');
            const pkgSize = row.querySelector('input[name="package_size"]');
            const priceCategory = row.querySelector('select[name="price_category"]');
            const price = row.querySelector('input[name="price"]');
            const capacity = row.querySelector('input[name="delivery_capacity"]');

            // Find the package name from the mapping ID
            if (existingData.package_mapping_id && availablePackageMappings) {
                const pkg = availablePackageMappings.find(p => p.id === existingData.package_mapping_id);
                if (pkg) {
                    pkgInput.value = pkg.package_name;
                    pkgId.value = pkg.id;
                }
            }

            // Find the SI unit from the ID
            if (existingData.si_unit_id && availableSIUnits) {
                const si = availableSIUnits.find(s => s.id === existingData.si_unit_id);
                if (si) {
                    siInput.value = si.si_unit;
                    siId.value = si.id;
                }
            }

            // Set other values
            pkgSize.value = existingData.package_size || 1;
            priceCategory.value = existingData.price_category || 'retail';
            price.value = existingData.price || '';
            if (existingData.delivery_capacity !== null) {
                capacity.value = existingData.delivery_capacity;
            }
        }
    }

    function initPackageDropdown(row) {
        const input = row.querySelector('.pkg-search-input');
        const hid = row.querySelector('.pkg-mapping-id');
        const dd = row.querySelector('.pkg-dropdown');

        function showList(filter = '') {
            dd.innerHTML = '';
            let found = false;
            availablePackageMappings.forEach(m => {
                if (m.package_name.toLowerCase().includes(filter.toLowerCase())) {
                    found = true;
                    const opt = document.createElement('div');
                    opt.className = 'custom-select-option';
                    opt.textContent = m.package_name;
                    opt.dataset.id = m.id;
                    opt.addEventListener('click', () => {
                        hid.value = m.id;
                        input.value = m.package_name;
                        dd.classList.add('hidden');
                        // clear any SI-unit rows if needed
                    });
                    dd.appendChild(opt);
                }
            });
            if (!found) {
                dd.innerHTML = `<div class="custom-select-no-results">No matching packages</div>`;
            }
        }

        input.addEventListener('focus', () => { dd.classList.remove('hidden'); showList(input.value); });
        input.addEventListener('input', () => { dd.classList.remove('hidden'); showList(input.value); });
        document.addEventListener('click', e => {
            if (!row.contains(e.target)) dd.classList.add('hidden');
        });
    }

    function initSiDropdown(row) {
        const input = row.querySelector('.si-search-input');
        const hid = row.querySelector('.si-unit-id');
        const dd = row.querySelector('.si-dropdown');
        const fallback = row.querySelector('.si-fallback');
        const newInput = fallback.querySelector('.new-si-input');
        const addBtn = fallback.querySelector('button');

        function showList(filter = '') {
            dd.innerHTML = '';
            let found = false;
            availableSIUnits.forEach(u => {
                if (u.si_unit.toLowerCase().includes(filter.toLowerCase())) {
                    found = true;
                    const opt = document.createElement('div');
                    opt.className = 'custom-select-option';
                    opt.textContent = u.si_unit;
                    opt.dataset.id = u.id;
                    opt.addEventListener('click', () => {
                        hid.value = u.id;
                        input.value = u.si_unit;
                        dd.classList.add('hidden');
                        fallback.classList.add('hidden');
                    });
                    dd.appendChild(opt);
                }
            });
            if (!found) {
                dd.innerHTML = `<div class="custom-select-no-results">No matching SI Units found</div>`;
                fallback.classList.remove('hidden');
            } else {
                fallback.classList.add('hidden');
            }
        }

        input.addEventListener('focus', () => { dd.classList.remove('hidden'); ensureSIUnits(); showList(input.value); });
        input.addEventListener('input', () => { dd.classList.remove('hidden'); showList(input.value); });
        document.addEventListener('click', e => {
            if (!row.contains(e.target)) dd.classList.add('hidden');
        });

        addBtn.addEventListener('click', () => {
            const name = newInput.value.trim();
            if (!name) return showToast('Enter SI unit', 'error');
            addBtn.disabled = true;
            fetch(`${BASE_URL}fetch/manageProfile?action=createSIUnit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ si_unit: name })
            })
                .then(r => r.json()).then(data => {
                    addBtn.disabled = false;
                    if (data.success) {
                        availableSIUnits.push({ id: data.id, si_unit: name });
                        hid.value = data.id;
                        input.value = name;
                        dd.classList.add('hidden');
                        fallback.classList.add('hidden');
                    } else {
                        showToast(data.message || 'Failed to create', 'error');
                    }
                }).catch(err => {
                    addBtn.disabled = false;
                    console.error(err);
                    showToast('Error creating SI unit', 'error');
                });
        });
    }

    async function handleAddProduct(e) {
        e.preventDefault();
        const productId = document.getElementById('selectedProductId').value;
        const categoryId = document.getElementById('selectedCategoryId').value;
        if (!productId || !categoryId) return showToast('Select product first', 'error');

        const rows = document.querySelectorAll('#lineItemsWrapper > div');
        if (rows.length === 0) return showToast('Add at least one pricing entry', 'error');

        const lineItems = [];
        for (const row of rows) {
            const pmId = row.querySelector('input[name="package_mapping_id"]').value;
            const siId = row.querySelector('input[name="si_unit_id"]').value;
            const pkgSize = row.querySelector('input[name="package_size"]').value;
            const priceCat = row.querySelector('select[name="price_category"]').value;
            const price = row.querySelector('input[name="price"]').value;
            const cap = row.querySelector('input[name="delivery_capacity"]').value;
            if (!pmId || !siId || !price) continue;
            lineItems.push({
                package_mapping_id: pmId,
                si_unit_id: siId,
                package_size: pkgSize,
                price_category: priceCat,
                price,
                delivery_capacity: cap
            });
        }
        if (lineItems.length === 0) return showToast('Complete all fields', 'error');

        const formData = new FormData();
        formData.append('store_id', vendorId);
        formData.append('product_id', productId);
        formData.append('line_items', JSON.stringify(lineItems));

        const btn = document.querySelector('#addProductForm button[type="submit"]');
        const orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Adding...';

        fetch(`${BASE_URL}fetch/manageProfile?action=addStoreProduct`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json()).then(data => {
                btn.disabled = false;
                btn.textContent = orig;
                if (data.success) {
                    showToast('Product & pricing added', 'success');
                    document.getElementById('addProductForm').reset();
                    document.getElementById('unitPricingContainer').classList.add('hidden');
                    availableSIUnits = [];
                    availablePackageMappings = [];
                    loadProductsForStore();
                    loadCurrentProducts(1); // Refresh the product list
                } else {
                    showToast(data.error || 'Failed to add product', 'error');
                }
            }).catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = orig;
                showToast('Error adding product', 'error');
            });
    }

    function loadCurrentProducts(page = 1, limit = 10) {
        const container = document.getElementById('productsList');
        container.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div></div>';

        fetch(`${BASE_URL}fetch/manageProfile?action=getStoreProducts&id=${vendorId}&page=${page}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products) {
                    if (data.products.length === 0) {
                        container.innerHTML = '<div class="text-center py-8"><p class="text-gray-500">No products added to this store yet.</p></div>';
                        return;
                    }

                    container.innerHTML = '';
                    data.products.forEach(product => {
                        const card = createProductCard(product);
                        container.appendChild(card);
                    });

                    // Create pagination
                    createPagination(data.pagination);
                } else {
                    container.innerHTML = '<div class="text-center py-8"><p class="text-red-500">Failed to load products.</p></div>';
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
                container.innerHTML = '<div class="text-center py-8"><p class="text-red-500">Error loading products.</p></div>';
            });
    }

    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow';

        let pricingHtml = '';
        if (product.pricing && product.pricing.length > 0) {
            pricingHtml = '<div class="mt-3 space-y-2">';
            pricingHtml += '<h4 class="text-sm font-medium text-gray-700">Pricing:</h4>';
            pricingHtml += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-2">';

            product.pricing.forEach(price => {
                const priceClass = `price-${price.price_category}`;
                pricingHtml += `
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <div>
                            <span class="text-sm">${escapeHtml(price.unit_name)}</span>
                            ${price.package_size > 1 ? `<span class="text-xs text-gray-500"> (x${price.package_size})</span>` : ''}
                        </div>
                        <div class="flex items-center">
                            <span class="price-tag ${priceClass}">${formatNumber(price.price)} UGX</span>
                        </div>
                    </div>
                `;
            });

            pricingHtml += '</div></div>';
        } else {
            pricingHtml = '<p class="text-sm text-gray-500 mt-2">No pricing information available</p>';
        }

        card.innerHTML = `
            <div class="flex justify-between">
                <div>
                    <h3 class="font-semibold text-lg">${escapeHtml(product.name)}</h3>
                    <p class="text-sm text-gray-600">${escapeHtml(product.category_name)}</p>
                </div>
                <div class="flex space-x-2">
                    <button type="button" class="edit-product-btn px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200" data-id="${product.store_product_id}" data-product='${JSON.stringify(product)}'>
                        Edit
                    </button>
                    <button type="button" class="delete-product-btn px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200" data-id="${product.store_product_id}" data-name="${escapeHtml(product.name)}">
                        Delete
                    </button>
                </div>
            </div>
            ${pricingHtml}
        `;

        // Add event listeners
        const editBtn = card.querySelector('.edit-product-btn');
        const deleteBtn = card.querySelector('.delete-product-btn');

        editBtn.addEventListener('click', function () {
            const productData = JSON.parse(this.dataset.product);
            openEditProductModal(productData);
        });

        deleteBtn.addEventListener('click', function () {
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            openDeleteConfirmModal(productId, productName);
        });

        return card;
    }

    function createPagination(pagination) {
        const container = document.getElementById('pagination');
        container.innerHTML = '';

        if (pagination.pages <= 1) return;

        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = `px-3 py-1 rounded ${pagination.page === 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
        prevBtn.textContent = 'Previous';
        prevBtn.disabled = pagination.page === 1;
        if (pagination.page > 1) {
            prevBtn.addEventListener('click', () => loadCurrentProducts(pagination.page - 1, pagination.limit));
        }
        container.appendChild(prevBtn);

        // Page numbers
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.pages, pagination.page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `px-3 py-1 rounded ${i === pagination.page ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => loadCurrentProducts(i, pagination.limit));
            container.appendChild(pageBtn);
        }

        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = `px-3 py-1 rounded ${pagination.page === pagination.pages ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'}`;
        nextBtn.textContent = 'Next';
        nextBtn.disabled = pagination.page === pagination.pages;
        if (pagination.page < pagination.pages) {
            nextBtn.addEventListener('click', () => loadCurrentProducts(pagination.page + 1, pagination.limit));
        }
        container.appendChild(nextBtn);
    }

    function openEditProductModal(product) {
        document.getElementById('editStoreProductId').value = product.store_product_id;
        document.getElementById('editProductName').textContent = product.name;
        document.getElementById('editProductCategory').textContent = product.category_name;

        const wrapper = document.getElementById('editLineItemsWrapper');
        wrapper.innerHTML = '';

        // Load package mappings for this product
        fetch(`${BASE_URL}fetch/manageProfile?action=getPackageNamesForProduct&product_id=${product.id}`)
            .then(r => r.json()).then(data => {
                if (data.success) {
                    availablePackageMappings = data.mappings;
                    ensureSIUnits();

                    // Add existing pricing entries
                    if (product.pricing && product.pricing.length > 0) {
                        product.pricing.forEach(price => {
                            addLineItemRow('editLineItemsWrapper', price);
                        });
                    } else {
                        addLineItemRow('editLineItemsWrapper');
                    }

                    openModal('editProductModal');
                } else {
                    showToast('Failed to load package mappings', 'error');
                }
            }).catch(err => {
                console.error(err);
                showToast('Error loading mappings', 'error');
            });
    }

    function openDeleteConfirmModal(productId, productName) {
        document.getElementById('deleteProductName').textContent = productName;
        document.getElementById('confirmDeleteBtn').dataset.id = productId;
        openModal('deleteConfirmModal');
    }

    function handleEditProduct() {
        const storeProductId = document.getElementById('editStoreProductId').value;
        const rows = document.querySelectorAll('#editLineItemsWrapper > div');
        if (rows.length === 0) return showToast('Add at least one pricing entry', 'error');

        const lineItems = [];
        for (const row of rows) {
            const pmId = row.querySelector('input[name="package_mapping_id"]').value;
            const siId = row.querySelector('input[name="si_unit_id"]').value;
            const pkgSize = row.querySelector('input[name="package_size"]').value;
            const priceCat = row.querySelector('select[name="price_category"]').value;
            const price = row.querySelector('input[name="price"]').value;
            const cap = row.querySelector('input[name="delivery_capacity"]').value;
            if (!pmId || !siId || !price) continue;
            lineItems.push({
                package_mapping_id: pmId,
                si_unit_id: siId,
                package_size: pkgSize,
                price_category: priceCat,
                price,
                delivery_capacity: cap
            });
        }
        if (lineItems.length === 0) return showToast('Complete all fields', 'error');

        const formData = new FormData();
        formData.append('store_product_id', storeProductId);
        formData.append('line_items', JSON.stringify(lineItems));

        const btn = document.getElementById('saveProductChangesBtn');
        const orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        fetch(`${BASE_URL}fetch/manageProfile?action=updateStoreProduct`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json()).then(data => {
                btn.disabled = false;
                btn.textContent = orig;
                if (data.success) {
                    showToast('Product pricing updated', 'success');
                    closeModal('editProductModal');
                    loadCurrentProducts(1); // Refresh the product list
                } else {
                    showToast(data.error || 'Failed to update product', 'error');
                }
            }).catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = orig;
                showToast('Error updating product', 'error');
            });
    }

    function handleDeleteProduct() {
        const productId = document.getElementById('confirmDeleteBtn').dataset.id;
        const btn = document.getElementById('confirmDeleteBtn');
        const orig = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Deleting...';

        const formData = new FormData();
        formData.append('id', productId);

        fetch(`${BASE_URL}fetch/manageProfile?action=deleteProduct`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json()).then(data => {
                btn.disabled = false;
                btn.textContent = orig;
                if (data.success) {
                    showToast('Product deleted', 'success');
                    closeModal('deleteConfirmModal');
                    loadCurrentProducts(1); // Refresh the product list
                } else {
                    showToast(data.error || 'Failed to delete product', 'error');
                }
            }).catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = orig;
                showToast('Error deleting product', 'error');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize variables
        window.availablePackageMappings = [];
        window.availableSIUnits = [];
        window.lineItemCount = 0;
        window.allProducts = [];

        loadStoreData();
        loadProductsForStore();
        loadCurrentProducts(1);
        initProductSearch();

        document.getElementById('addLineItemBtn').addEventListener('click', () => addLineItemRow('lineItemsWrapper'));
        document.getElementById('editAddLineItemBtn').addEventListener('click', () => addLineItemRow('editLineItemsWrapper'));
        document.getElementById('addProductForm').addEventListener('submit', handleAddProduct);
        document.getElementById('saveProductChangesBtn').addEventListener('click', handleEditProduct);
        document.getElementById('confirmDeleteBtn').addEventListener('click', handleDeleteProduct);

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('fixed') && event.target.classList.contains('inset-0')) {
                closeModal(event.target.id);
            }
        });
    });
</script>