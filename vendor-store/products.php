<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Store Products';
$activeNav = 'products';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}

$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}

ob_start();
?>
<script>
    const vendorId = '<?= $storeId ?>';
    let productsCache = [];
    let isEditMode = false;
</script>

<div class="space-y-6">
    <div id="alertContainer"></div>

    <div
        class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Store Products</h2>
            <p class="text-sm text-gray-600 mt-1">Manage your store's product catalog</p>
        </div>
        <button onclick="openProductModal(false)"
            class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i>Add Product
        </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <input type="text" id="searchInput" placeholder="Search products..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
            <div class="flex gap-2">
                <button id="filterBtn"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <button id="clearBtn"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
            </div>
        </div>

        <div id="loadingIndicator" class="hidden text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Loading products...</p>
        </div>

        <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"></div>
        <div id="paginationContainer" class="mt-6"></div>
    </div>
</div>

<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[95vh] flex flex-col">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 id="productModalTitle" class="text-lg font-semibold text-gray-900"></h3>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="overflow-y-auto p-6 min-h-[40vh]">
            <form id="productForm" class="space-y-6">
                <input type="hidden" id="storeProductId" name="store_product_id">

                <div id="productSelectionSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Product *</label>
                    <div class="relative">
                        <input type="text" id="productSearchInput"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Type to search for products..." autocomplete="off">
                        <input type="hidden" id="selectedProductId" name="product_id">
                        <input type="hidden" id="selectedCategoryId" name="category_id">
                        <div id="productDropdown"
                            class="absolute top-full left-0 right-0 max-h-72 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-50">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Choose from existing products on the platform</p>
                </div>

                <div id="pricingSection">
                    <h4 class="text-sm font-semibold text-gray-900 mb-4">Pricing Configuration</h4>
                    <div id="lineItemsWrapper" class="space-y-4"></div>
                    <button type="button" id="addLineItemBtn"
                        class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Pricing Entry
                    </button>
                </div>
            </form>
        </div>

        <div class="flex justify-end p-6 border-t border-gray-200 gap-3">
            <button onclick="closeProductModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                Cancel
            </button>
            <button id="saveProductBtn"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                <i class="fas fa-save mr-2"></i><span id="saveButtonText">Save</span>
            </button>
        </div>
    </div>
</div>

<div id="deleteConfirmModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
            </div>
            <p class="text-gray-600 mb-4">Are you sure you want to delete this product from your store? This action
                cannot be undone.</p>
            <p id="deleteProductName" class="font-medium text-gray-900"></p>
        </div>
        <div class="flex justify-end p-6 border-t border-gray-200 gap-3">
            <button onclick="closeDeleteModal()"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                Cancel
            </button>
            <button id="confirmDeleteBtn"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                <i class="fas fa-trash mr-2"></i>Delete
            </button>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentSearch = '';
    let availablePackageMappings = [];
    let availableSIUnits = [];
    let allProducts = [];
    let lineItemCount = 0;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function showAlert(type, message) {
        const alertClass = type === 'success'
            ? 'bg-green-50 border-green-200 text-green-800'
            : 'bg-red-50 border-red-200 text-red-800';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        document.getElementById('alertContainer').innerHTML = `
            <div class="${alertClass} border px-4 py-3 rounded-lg mb-4">
                <i class="fas ${iconClass} mr-2"></i>${message}
            </div>`;
        setTimeout(() => { document.getElementById('alertContainer').innerHTML = ''; }, 5000);
    }

    async function getProductImage(productId) {
        try {
            const response = await fetch(`${BASE_URL}img/products/${productId}/images.json`);
            const data = await response.json();
            if (data.images && data.images.length > 0) {
                const randomImage = data.images[Math.floor(Math.random() * data.images.length)];
                return `${BASE_URL}img/products/${productId}/${randomImage}`;
            }
        } catch (error) {
            console.log('No images found for product:', productId);
        }
        return 'https://placehold.co/400x300/f3f4f6/9ca3af?text=No+Image';
    }

    async function loadCurrentProducts(page = 1, limit = 12) {
        const grid = document.getElementById('productsGrid');
        const loading = document.getElementById('loadingIndicator');

        loading.classList.remove('hidden');
        grid.innerHTML = '';

        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getStoreProducts&id=${vendorId}&page=${page}&limit=${limit}`);
            const data = await response.json();

            loading.classList.add('hidden');

            if (data.success && data.products) {
                productsCache = data.products;
                await renderProducts();
                createPagination(data.pagination);
            } else {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-red-500 mb-2">Failed to load products</h3>
                        <p class="text-red-400">Please try refreshing the page.</p>
                    </div>`;
            }
        } catch (error) {
            loading.classList.add('hidden');
            grid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-wifi text-6xl text-red-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-red-500 mb-2">Connection Error</h3>
                    <p class="text-red-400">Unable to load products. Please check your connection.</p>
                </div>`;
        }
    }

    async function renderProducts() {
        const grid = document.getElementById('productsGrid');
        grid.innerHTML = '';

        const filtered = productsCache.filter(product =>
            product.name.toLowerCase().includes(currentSearch.toLowerCase()) ||
            product.category_name.toLowerCase().includes(currentSearch.toLowerCase())
        );

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-500 mb-2">No products found</h3>
                    <p class="text-gray-400 mb-6">${currentSearch ? 'Try adjusting your search terms.' : 'Start building your catalog by adding products to your store.'}</p>
                    ${!currentSearch ? '<button onclick="openProductModal(false)" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"><i class="fas fa-plus mr-2"></i>Add Your First Product</button>' : ''}
                </div>`;
            return;
        }

        for (const product of filtered) {
            const imageUrl = await getProductImage(product.id);
            const card = createProductCard(product, imageUrl);
            grid.appendChild(card);
        }
    }

    function createProductCard(product, imageUrl) {
        const card = document.createElement('div');
        card.className = 'bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200 flex flex-col h-full';

        const pricingBadges = product.pricing?.map(price => {
            const colorClass = price.price_category === 'retail' ? 'bg-blue-100 text-blue-800' :
                price.price_category === 'wholesale' ? 'bg-green-100 text-green-800' :
                    'bg-orange-100 text-orange-800';
            return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${colorClass}">UGX ${formatNumber(price.price)}</span>`;
        }).join('') || '<span class="text-sm text-gray-500">No pricing</span>';

        card.innerHTML = `
            <div class="relative">
                <img src="${imageUrl}" 
                     alt="${escapeHtml(product.name)}"
                     class="w-full h-48 object-cover rounded-t-xl bg-gray-100"
                     onerror="this.src='https://placehold.co/400x300/f3f4f6/9ca3af?text=No+Image'">
                <button onclick="openProductModal(true, '${product.store_product_id}')"
                    class="absolute top-2 right-2 bg-white p-2 rounded-full shadow-md text-blue-600 hover:bg-blue-50 transition-colors">
                    <i class="fas fa-edit text-sm"></i>
                </button>
            </div>
            <div class="p-4 flex flex-col flex-grow">
                <div class="flex-grow">
                    <h3 class="font-semibold text-lg text-gray-900 mb-1">${escapeHtml(product.name)}</h3>
                    <p class="text-sm text-gray-600 mb-3">${escapeHtml(product.category_name)}</p>
                    <div class="flex flex-wrap gap-1 mb-4">${pricingBadges}</div>
                </div>
                <div class="flex justify-between items-center mt-auto pt-2 border-t border-gray-100">
                    <span class="text-sm text-gray-500">${product.pricing ? product.pricing.length : 0} pricing option${product.pricing && product.pricing.length !== 1 ? 's' : ''}</span>
                    <button onclick="openDeleteModal('${product.store_product_id}', '${escapeHtml(product.name)}')"
                        class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-full transition-colors">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>`;

        return card;
    }

    function createPagination(pagination) {
        const container = document.getElementById('paginationContainer');

        if (pagination.pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = `
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                    Showing ${pagination.offset + 1} to ${Math.min(pagination.offset + pagination.limit, pagination.total_products)} of ${pagination.total_products} products
                </div>
                <div class="flex gap-1">`;

        if (pagination.page > 1) {
            html += `<button onclick="changePage(${pagination.page - 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Previous</button>`;
        }

        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.pages, pagination.page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === pagination.page;
            html += `<button onclick="changePage(${i})" class="px-3 py-2 text-sm border rounded-lg transition-colors ${isActive ? 'bg-red-600 text-white border-red-600' : 'border-gray-300 hover:bg-gray-50'}">${i}</button>`;
        }

        if (pagination.page < pagination.pages) {
            html += `<button onclick="changePage(${pagination.page + 1})" class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Next</button>`;
        }

        html += '</div></div>';
        container.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        loadCurrentProducts(page);
    }

    async function openProductModal(edit = false, storeProductId = '') {
        isEditMode = edit;
        document.getElementById('productModalTitle').textContent = edit ? 'Edit Product Pricing' : 'Add New Product';
        document.getElementById('saveButtonText').textContent = edit ? 'Update' : 'Save';
        document.getElementById('productForm').reset();
        document.getElementById('lineItemsWrapper').innerHTML = '';

        const productSelectionSection = document.getElementById('productSelectionSection');

        if (edit) {
            productSelectionSection.style.display = 'none';
            const product = productsCache.find(p => p.store_product_id === storeProductId);

            if (product) {
                document.getElementById('storeProductId').value = storeProductId;
                document.getElementById('selectedProductId').value = product.id;
                document.getElementById('selectedCategoryId').value = product.category_id;

                try {
                    const [pkgResponse, siResponse] = await Promise.all([
                        fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getPackageNamesForProduct&product_id=${product.id}`),
                        fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getSIUnits`)
                    ]);

                    const [pkgData, siData] = await Promise.all([
                        pkgResponse.json(),
                        siResponse.json()
                    ]);

                    if (pkgData.success) availablePackageMappings = pkgData.mappings;
                    if (siData.success) availableSIUnits = siData.siUnits;

                    if (product.pricing && product.pricing.length > 0) {
                        product.pricing.forEach(pricing => addLineItemRow(pricing));
                    } else {
                        addLineItemRow();
                    }
                } catch (error) {
                    showAlert('error', 'Failed to load product data');
                    return;
                }
            }
        } else {
            productSelectionSection.style.display = 'block';
            document.getElementById('storeProductId').value = '';
            await loadProductsForStore();
            await ensureSIUnits();
            addLineItemRow();
        }

        document.getElementById('productModal').classList.remove('hidden');
    }

    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
    }

    function openDeleteModal(storeProductId, productName) {
        document.getElementById('deleteProductName').textContent = productName;
        document.getElementById('confirmDeleteBtn').dataset.id = storeProductId;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    async function loadProductsForStore() {
        const dropdown = document.getElementById('productDropdown');
        dropdown.innerHTML = '<div class="p-3 text-center text-gray-500">Loading products...</div>';

        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getProductsNotInStore&store_id=${vendorId}`);
            const data = await response.json();

            if (data.success && data.products.length) {
                allProducts = data.products;
                const byCat = {};
                data.products.forEach(p => {
                    byCat[p.category_name] = byCat[p.category_name] || [];
                    byCat[p.category_name].push(p);
                });
                populateProductDropdown(byCat);
            } else {
                dropdown.innerHTML = '<div class="p-3 text-center text-gray-500">No products available to add</div>';
            }
        } catch (error) {
            dropdown.innerHTML = '<div class="p-3 text-center text-red-500">Error loading products</div>';
        }
    }

    function populateProductDropdown(byCat, filter = '') {
        const dropdown = document.getElementById('productDropdown');
        dropdown.innerHTML = '';
        let found = false;

        Object.keys(byCat).sort().forEach(cat => {
            const list = byCat[cat].filter(p => p.name.toLowerCase().includes(filter.toLowerCase()));
            if (list.length) {
                found = true;
                const header = document.createElement('div');
                header.className = 'px-4 py-2 text-xs font-semibold text-gray-500 uppercase bg-gray-50 border-b';
                header.textContent = cat;
                dropdown.appendChild(header);

                list.forEach(p => {
                    const option = document.createElement('div');
                    option.className = 'px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                    option.textContent = p.name;
                    option.addEventListener('click', () => selectProduct(p.id, p.category_id, p.name));
                    dropdown.appendChild(option);
                });
            }
        });

        if (!found) {
            dropdown.innerHTML = '<div class="p-3 text-center text-gray-500 italic">No matching products found</div>';
        }
    }

    function selectProduct(productId, categoryId, productName) {
        document.getElementById('selectedProductId').value = productId;
        document.getElementById('selectedCategoryId').value = categoryId;
        document.getElementById('productSearchInput').value = productName;
        document.getElementById('productDropdown').classList.add('hidden');
        loadPackageMappingsForProduct(productId);
    }

    async function loadPackageMappingsForProduct(productId) {
        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getPackageNamesForProduct&product_id=${productId}`);
            const data = await response.json();

            if (data.success) {
                availablePackageMappings = data.mappings;
                await ensureSIUnits();

                const wrapper = document.getElementById('lineItemsWrapper');
                if (wrapper.children.length === 0) {
                    addLineItemRow();
                }
            } else {
                showAlert('error', 'Failed to load package mappings');
            }
        } catch (error) {
            showAlert('error', 'Error loading package mappings');
        }
    }

    async function ensureSIUnits() {
        if (!availableSIUnits || !availableSIUnits.length) {
            try {
                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=getSIUnits`);
                const data = await response.json();
                if (data.success) availableSIUnits = data.siUnits;
            } catch (error) {
                console.error('Failed to load SI units');
            }
        }
    }

    function addLineItemRow(existingData = null) {
        lineItemCount++;
        const wrapper = document.getElementById('lineItemsWrapper');
        const row = document.createElement('div');
        row.className = 'bg-gray-50 border border-gray-200 rounded-lg p-4 relative';

        row.innerHTML = `
            <button type="button" class="absolute top-2 right-2 text-red-600 hover:text-red-800 hover:bg-red-50 p-1 rounded transition-colors" onclick="removeLineItem(this)">
                <i class="fas fa-times text-sm"></i>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Package</label>
                    <div class="relative">
                        <input type="text" class="pkg-search-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Search package..." autocomplete="off"/>
                        <input type="hidden" name="package_mapping_id" class="pkg-mapping-id"/>
                        <div class="pkg-dropdown absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-40"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SI Unit</label>
                    <div class="relative">
                        <input type="text" class="si-search-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Search SI unit..." autocomplete="off"/>
                        <input type="hidden" name="si_unit_id" class="si-unit-id"/>
                        <div class="si-dropdown absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-40"></div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit Size</label>
                    <input type="number" name="package_size" value="1" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Category</label>
                    <select name="price_category" class="price-category-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <option value="">-- Select Category --</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="factory">Factory</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price (UGX)</label>
                    <input type="number" step="any" name="price" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
                <div class="delivery-capacity-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2 delivery-capacity-label">Capacity</label>
                    <input type="number" name="delivery_capacity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent">
                </div>
            </div>
        `;

        wrapper.appendChild(row);
        initPackageDropdown(row);
        initSiDropdown(row);

        const priceSelect = row.querySelector('.price-category-select');
        priceSelect.addEventListener('change', function () {
            const capacityLabel = row.querySelector('.delivery-capacity-label');
            capacityLabel.textContent = this.value === 'retail' ? 'Max. Capacity' :
                (this.value === 'wholesale' || this.value === 'factory' ? 'Min. Capacity' : 'Capacity');
        });

        if (existingData) {
            populateExistingData(row, existingData);
        }
    }

    function removeLineItem(button) {
        const wrapper = document.getElementById('lineItemsWrapper');
        const rows = wrapper.querySelectorAll('.bg-gray-50');

        if (rows.length <= 1) {
            showAlert('error', 'A product must have at least one packaging definition');
            return;
        }

        button.closest('.bg-gray-50').remove();
    }

    function populateExistingData(row, existingData) {
        const pkgInput = row.querySelector('.pkg-search-input');
        const pkgId = row.querySelector('.pkg-mapping-id');
        const siInput = row.querySelector('.si-search-input');
        const siId = row.querySelector('.si-unit-id');
        const pkgSize = row.querySelector('input[name="package_size"]');
        const priceCategory = row.querySelector('select[name="price_category"]');
        const price = row.querySelector('input[name="price"]');
        const capacity = row.querySelector('input[name="delivery_capacity"]');
        const capacityLabel = row.querySelector('.delivery-capacity-label');

        if (existingData.package_mapping_id && availablePackageMappings) {
            const pkg = availablePackageMappings.find(p => p.id == existingData.package_mapping_id);
            if (pkg) {
                pkgInput.value = pkg.package_name;
                pkgId.value = pkg.id;
            }
        }

        if (existingData.si_unit_id && availableSIUnits) {
            const si = availableSIUnits.find(s => s.id == existingData.si_unit_id);
            if (si) {
                siInput.value = si.si_unit;
                siId.value = si.id;
            }
        }

        pkgSize.value = existingData.package_size || 1;
        priceCategory.value = existingData.price_category || '';
        price.value = existingData.price || '';

        if (existingData.delivery_capacity !== null && existingData.delivery_capacity !== undefined) {
            capacity.value = existingData.delivery_capacity;
        }

        if (existingData.price_category === 'retail') {
            capacityLabel.textContent = 'Max. Capacity';
        } else if (existingData.price_category === 'wholesale' || existingData.price_category === 'factory') {
            capacityLabel.textContent = 'Min. Capacity';
        }
    }

    function initPackageDropdown(row) {
        const input = row.querySelector('.pkg-search-input');
        const hiddenInput = row.querySelector('.pkg-mapping-id');
        const dropdown = row.querySelector('.pkg-dropdown');

        function showList(filter = '') {
            dropdown.innerHTML = '';
            let found = false;

            availablePackageMappings.forEach(mapping => {
                if (mapping.package_name.toLowerCase().includes(filter.toLowerCase())) {
                    found = true;
                    const option = document.createElement('div');
                    option.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                    option.textContent = mapping.package_name;
                    option.addEventListener('click', () => {
                        hiddenInput.value = mapping.id;
                        input.value = mapping.package_name;
                        dropdown.classList.add('hidden');
                    });
                    dropdown.appendChild(option);
                }
            });

            if (!found) {
                dropdown.innerHTML = '<div class="p-3 text-center text-gray-500">No matching packages</div>';
            }
        }

        input.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
            showList(input.value);
        });

        input.addEventListener('input', () => {
            dropdown.classList.remove('hidden');
            showList(input.value);
        });

        document.addEventListener('click', (e) => {
            if (!row.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function initSiDropdown(row) {
        const input = row.querySelector('.si-search-input');
        const hiddenInput = row.querySelector('.si-unit-id');
        const dropdown = row.querySelector('.si-dropdown');

        function showList(filter = '') {
            dropdown.innerHTML = '';
            let found = false;

            availableSIUnits.forEach(unit => {
                if (unit.si_unit.toLowerCase().includes(filter.toLowerCase())) {
                    found = true;
                    const option = document.createElement('div');
                    option.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0';
                    option.textContent = unit.si_unit;
                    option.addEventListener('click', () => {
                        hiddenInput.value = unit.id;
                        input.value = unit.si_unit;
                        dropdown.classList.add('hidden');
                    });
                    dropdown.appendChild(option);
                }
            });

            if (!found) {
                dropdown.innerHTML = '<div class="p-3 text-center text-gray-500">No matching SI units found</div>';
            }
        }

        input.addEventListener('focus', () => {
            dropdown.classList.remove('hidden');
            showList(input.value);
        });

        input.addEventListener('input', () => {
            dropdown.classList.remove('hidden');
            showList(input.value);
        });

        document.addEventListener('click', (e) => {
            if (!row.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    function initProductSearch() {
        const input = document.getElementById('productSearchInput');
        const dropdown = document.getElementById('productDropdown');

        function groupAndFilter(val) {
            const byCat = {};
            allProducts.forEach(p => {
                byCat[p.category_name] = byCat[p.category_name] || [];
                byCat[p.category_name].push(p);
            });
            populateProductDropdown(byCat, val);
        }

        input.addEventListener('focus', function () {
            if (allProducts && allProducts.length) {
                dropdown.classList.remove('hidden');
                groupAndFilter(this.value);
            }
        });

        input.addEventListener('input', function () {
            if (allProducts && allProducts.length) {
                dropdown.classList.remove('hidden');
                groupAndFilter(this.value);
            }
        });

        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }

    async function handleSaveProduct() {
        const productId = document.getElementById('selectedProductId').value;
        const categoryId = document.getElementById('selectedCategoryId').value;

        if (!isEditMode && (!productId || !categoryId)) {
            showAlert('error', 'Please select a product first');
            return;
        }

        const rows = document.querySelectorAll('#lineItemsWrapper .bg-gray-50');
        if (rows.length === 0) {
            showAlert('error', 'Please add at least one pricing entry');
            return;
        }

        const lineItems = [];
        let hasError = false;

        rows.forEach(row => {
            const pmId = row.querySelector('input[name="package_mapping_id"]').value;
            const siId = row.querySelector('input[name="si_unit_id"]').value;
            const pkgSize = row.querySelector('input[name="package_size"]').value;
            const priceCat = row.querySelector('select[name="price_category"]').value;
            const price = row.querySelector('input[name="price"]').value;
            const cap = row.querySelector('input[name="delivery_capacity"]').value;

            if (!pmId || !siId || !price || !priceCat) {
                hasError = true;
                return;
            }

            lineItems.push({
                package_mapping_id: pmId,
                si_unit_id: siId,
                package_size: pkgSize,
                price_category: priceCat,
                price: price,
                delivery_capacity: cap || null
            });
        });

        if (hasError || lineItems.length === 0) {
            showAlert('error', 'Please complete all required fields in pricing entries');
            return;
        }

        const btn = document.getElementById('saveProductBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

        try {
            const formData = new FormData();

            if (isEditMode) {
                const storeProductId = document.getElementById('storeProductId').value;
                formData.append('store_product_id', storeProductId);
                formData.append('line_items', JSON.stringify(lineItems));

                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=updateStoreProduct`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', 'Product updated successfully');
                    closeProductModal();
                    await loadCurrentProducts(currentPage);
                } else {
                    showAlert('error', data.error || 'Failed to update product');
                }
            } else {
                formData.append('store_id', vendorId);
                formData.append('product_id', productId);
                formData.append('line_items', JSON.stringify(lineItems));

                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=addStoreProduct`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('success', 'Product added successfully');
                    closeProductModal();
                    await loadCurrentProducts(1);
                } else {
                    showAlert('error', data.error || 'Failed to add product');
                }
            }
        } catch (error) {
            showAlert('error', 'Error saving product');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function handleDeleteProduct() {
        const productId = document.getElementById('confirmDeleteBtn').dataset.id;
        const btn = document.getElementById('confirmDeleteBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';

        try {
            const formData = new FormData();
            formData.append('id', productId);

            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageProducts.php?action=deleteProduct`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', 'Product deleted successfully');
                closeDeleteModal();
                await loadCurrentProducts(currentPage);
            } else {
                showAlert('error', data.error || 'Failed to delete product');
            }
        } catch (error) {
            showAlert('error', 'Error deleting product');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCurrentProducts(1);
        initProductSearch();

        document.getElementById('filterBtn').addEventListener('click', function () {
            currentSearch = document.getElementById('searchInput').value.trim();
            renderProducts();
        });

        document.getElementById('clearBtn').addEventListener('click', function () {
            document.getElementById('searchInput').value = '';
            currentSearch = '';
            renderProducts();
        });

        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filterBtn').click();
            }
        });

        document.getElementById('addLineItemBtn').addEventListener('click', () => addLineItemRow());
        document.getElementById('saveProductBtn').addEventListener('click', handleSaveProduct);
        document.getElementById('confirmDeleteBtn').addEventListener('click', handleDeleteProduct);

        document.querySelectorAll('#productModal, #deleteConfirmModal').forEach(modal => {
            modal.addEventListener('click', function (event) {
                if (event.target === this) {
                    this.classList.add('hidden');
                }
            });
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>