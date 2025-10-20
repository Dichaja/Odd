<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL);
    exit;
}
$pageTitle = 'Products Pricing';
$activeNav = 'products-pricing';
ob_start();
?>
<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Products Pricing</h1>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">View products with pricing and
                        which vendors sell them</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="products"
                        class="px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-box"></i>
                        <span class="hidden sm:inline">Products</span>
                    </a>
                    <a href="product-categories"
                        class="px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tags"></i>
                        <span class="hidden sm:inline">Categories</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Priced Products</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="statPricedProducts">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Vendors Selling</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="statVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Pricing Options</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="statPricingOptions">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-list text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Filter & Search</h2>
                    <p class="text-sm text-gray-600">Find priced products and drill into vendor pricing</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="searchProducts" placeholder="Search product or vendor..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="filterCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Category</label>
                    <select id="filterPriceCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="factory">Factory</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortProducts"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Select</option>
                        <option value="last_update">Last Pricing Update</option>
                        <option value="vendors">Vendor Count</option>
                        <option value="min_price">Lowest Price</option>
                        <option value="max_price">Highest Price</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="filterStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All</option>
                        <option value="published">Published</option>
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Priced Products</h3>
                        <p class="text-sm text-gray-600"><span id="productCount">0</span> products found</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button id="resetFilters"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Reset
                            Filters</button>
                        <button id="applyFilters"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Apply
                            Filters</button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="productsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Product</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Category</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Vendors</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Price Range</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Updated</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading priced products...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span
                        id="totalPricedProductsFooter">0</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Previous</button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>

            <div class="lg:hidden" id="productsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading priced products...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingStart">0</span> to <span id="mobileShowingEnd">0</span> of <span
                        id="mobileTotalPricedProducts">0</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Previous</button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pricingModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hidePricingModal()"></div>
    <div
        class="relative w-full h-full max-w-5xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                    <i class="fas fa-tags text-gray-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Product Pricing</h3>
                    <p class="text-sm text-gray-600 mt-1">Vendors and pricing options</p>
                </div>
            </div>
            <button onclick="hidePricingModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-140px)]">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="col-span-2">
                    <h4 class="text-base font-semibold text-secondary" id="modalProductName"></h4>
                    <div class="text-sm text-gray-600" id="modalCategoryName"></div>
                </div>
                <div class="flex items-center gap-2 md:justify-end">
                    <select id="modalFilterPriceCategory"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All price categories</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="factory">Factory</option>
                    </select>
                    <input id="modalSearchStore" type="text" placeholder="Search store..."
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                </div>
            </div>
            <div id="vendorPricingContainer" class="space-y-4"></div>
        </div>
        <div class="p-2 border-t border-gray-100 flex justify-end gap-3"></div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[999] hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span id="loadingMessage" class="text-gray-700 font-medium">Loading...</span>
    </div>
</div>

<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>
<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<style>
    .vendor-card {
        border: 1px solid rgba(229, 231, 235, 1);
        border-radius: 0.75rem;
    }

    .vendor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(243, 244, 246, 1);
        background: rgba(249, 250, 251, 1);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .vendor-body {
        padding: 0.75rem 1rem;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-green {
        background: rgba(220, 252, 231, 1);
        color: rgba(21, 128, 61, 1);
    }

    .badge-yellow {
        background: rgba(254, 249, 195, 1);
        color: rgba(133, 77, 14, 1);
    }

    .badge-purple {
        background: rgba(237, 233, 254, 1);
        color: rgba(91, 33, 182, 1);
    }
</style>

<script>
    let pricedProducts = [];
    let categoriesList = [];
    let currentPage = 1;
    let itemsPerPage = 20;
    let totalPages = 1;
    let filterData = { search: '', category: '', priceCategory: '', status: '', sort: '' };
    let modalData = { product: null, rows: [], filtered: [] };

    document.addEventListener('DOMContentLoaded', () => {
        loadCategories();
        loadPricedProducts();
        document.getElementById('searchProducts').addEventListener('input', e => { filterData.search = e.target.value; applyFilters(); });
        document.getElementById('filterCategory').addEventListener('change', e => { filterData.category = e.target.value; });
        document.getElementById('filterPriceCategory').addEventListener('change', e => { filterData.priceCategory = e.target.value; });
        document.getElementById('filterStatus').addEventListener('change', e => { filterData.status = e.target.value; });
        document.getElementById('sortProducts').addEventListener('change', e => { filterData.sort = e.target.value; });
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', resetFilters);
        document.getElementById('prev-page').addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderPagination(); renderProducts(pricedProducts); } });
        document.getElementById('next-page').addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; renderPagination(); renderProducts(pricedProducts); } });
        document.getElementById('mobilePrevPage').addEventListener('click', () => { if (currentPage > 1) { currentPage--; renderPagination(); renderProducts(pricedProducts); } });
        document.getElementById('mobileNextPage').addEventListener('click', () => { if (currentPage < totalPages) { currentPage++; renderPagination(); renderProducts(pricedProducts); } });
        document.getElementById('modalFilterPriceCategory').addEventListener('change', () => { filterModalRows(); });
        document.getElementById('modalSearchStore').addEventListener('input', () => { filterModalRows(); });
    });

    function loadCategories() {
        showLoading('Loading categories...');
        fetch(`${BASE_URL}admin/fetch/manageProductCategories.php?action=getCategories`)
            .then(res => { if (res.status === 401) { showSessionExpiredModal(); throw new Error('unauth'); } return res.json(); })
            .then(data => {
                hideLoading();
                if (data.success) {
                    categoriesList = data.categories || [];
                    const catFilter = document.getElementById('filterCategory');
                    catFilter.innerHTML = '<option value="">All Categories</option>';
                    categoriesList.forEach(cat => {
                        const opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        catFilter.appendChild(opt);
                    });
                }
            })
            .catch(() => { hideLoading(); });
    }

    function loadPricedProducts() {
        showLoading('Loading priced products...');
        fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getPricedProducts`)
            .then(res => { if (res.status === 401) { showSessionExpiredModal(); throw new Error('unauth'); } return res.json(); })
            .then(data => {
                hideLoading();
                if (data.success) {
                    pricedProducts = (data.products || []).map(p => normalizeRow(p));
                    updateStats(pricedProducts);
                    currentPage = 1;
                    renderProducts(pricedProducts);
                    renderPagination();
                } else {
                    showErrorNotification(data.message || 'Failed to load priced products');
                }
            })
            .catch(() => { hideLoading(); showErrorNotification('Failed to load priced products'); });
    }

    function normalizeRow(r) {
        return Object.assign({}, r, {
            min_price: parseFloat(r.min_price || 0),
            max_price: parseFloat(r.max_price || 0),
            stores_count: parseInt(r.stores_count || 0, 10),
            pricing_count: parseInt(r.pricing_count || 0, 10),
            last_pricing_update: r.last_pricing_update || r.updated_at || r.created_at || null,
            category: r.category_id || r.category || null
        });
    }

    function updateStats(rows) {
        const total = rows.length;
        const vendorSum = rows.reduce((a, b) => a + (b.stores_count || 0), 0);
        const pricingSum = rows.reduce((a, b) => a + (b.pricing_count || 0), 0);
        document.getElementById('statPricedProducts').textContent = total.toLocaleString();
        document.getElementById('statVendors').textContent = vendorSum.toLocaleString();
        document.getElementById('statPricingOptions').textContent = pricingSum.toLocaleString();
    }

    function applyFilters() {
        currentPage = 1;
        renderProducts(pricedProducts);
        renderPagination();
    }

    function resetFilters() {
        filterData = { search: '', category: '', priceCategory: '', status: '', sort: '' };
        document.getElementById('searchProducts').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterPriceCategory').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('sortProducts').value = '';
        applyFilters();
    }

    function filterRows(rows) {
        let out = rows.slice();
        if (filterData.search) {
            const q = filterData.search.toLowerCase();
            out = out.filter(r => {
                const t = (r.title || '').toLowerCase();
                const cat = (r.category_name || '').toLowerCase();
                const vendors = (r.vendor_names_join || '').toLowerCase();
                return t.includes(q) || cat.includes(q) || vendors.includes(q);
            });
        }
        if (filterData.category) out = out.filter(r => (r.category || '') == filterData.category);
        if (filterData.status) out = out.filter(r => (r.status || '') === filterData.status);
        if (filterData.priceCategory) out = out.filter(r => (r.price_categories || []).includes(filterData.priceCategory));
        switch (filterData.sort) {
            case 'last_update':
                out.sort((a, b) => new Date(b.last_pricing_update || 0) - new Date(a.last_pricing_update || 0));
                break;
            case 'vendors':
                out.sort((a, b) => (b.stores_count || 0) - (a.stores_count || 0));
                break;
            case 'min_price':
                out.sort((a, b) => (a.min_price || 0) - (b.min_price || 0));
                break;
            case 'max_price':
                out.sort((a, b) => (b.max_price || 0) - (a.max_price || 0));
                break;
        }
        return out;
    }

    function renderProducts(rows) {
        const filtered = filterRows(rows);
        const tbody = document.getElementById('productsBody');
        const cards = document.getElementById('productsCards');
        const total = filtered.length;
        totalPages = Math.ceil(total / itemsPerPage);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, total);
        document.getElementById('productCount').textContent = total;
        document.getElementById('showingStart').textContent = total ? start + 1 : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalPricedProductsFooter').textContent = total;
        document.getElementById('mobileShowingStart').textContent = total ? start + 1 : 0;
        document.getElementById('mobileShowingEnd').textContent = end;
        document.getElementById('mobileTotalPricedProducts').textContent = total;
        document.getElementById('mobilePageInfo').textContent = `Page ${Math.max(1, currentPage)} of ${Math.max(1, totalPages)}`;
        document.getElementById('mobilePrevPage').disabled = currentPage === 1;
        document.getElementById('mobileNextPage').disabled = currentPage === totalPages || totalPages === 0;

        if (end - start <= 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No priced products found</div></td></tr>';
            cards.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No priced products found</div></div>';
            renderPagination();
            return;
        }

        const pageRows = filtered.slice(start, end);
        tbody.innerHTML = pageRows.map(r => {
            const img = r.main_image || 'https://placehold.co/48x48/e2e8f0/1e293b?text=P';
            const range = priceRangeBlock(r.min_price, r.max_price);
            const updated = r.last_pricing_update ? formatDateTimeBlock(r.last_pricing_update) : '';
            return '<tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="openPricingModal(\'' + r.id + '\')">' +
                '<td class="px-4 py-3 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10 rounded-lg overflow-hidden bg-gray-100"><img src="' + img + '" alt="' + escapeHtml(r.title) + '" class="w-full h-full object-cover"></div><div class="ml-4"><div class="text-sm font-medium text-secondary max-w-xs hover:text-primary"><span class="hidden sm:block truncate">' + escapeHtml(r.title) + '</span><span class="sm:hidden break-words">' + escapeHtml(r.title) + '</span></div><div class="text-xs text-gray-500 hidden sm:block">ID: ' + r.id + '</div></div></div></td>' +
                '<td class="px-4 py-3 text-center"><span class="text-sm text-gray-900">' + escapeHtml(r.category_name || '(Uncategorized)') + '</span></td>' +
                '<td class="px-4 py-3 text-center"><span class="text-sm text-gray-900">' + (r.stores_count || 0) + '</span></td>' +
                '<td class="px-4 py-3 text-center"><div class="text-sm text-gray-900 leading-tight">' + range + '</div></td>' +
                '<td class="px-4 py-3 text-center"><div class="text-xs text-gray-600 leading-tight">' + updated + '</div></td>' +
                '</tr>';
        }).join('');

        cards.innerHTML = pageRows.map(r => {
            const img = r.main_image || 'https://placehold.co/64x64/e2e8f0/1e293b?text=P';
            const range = priceRangeBlock(r.min_price, r.max_price);
            const updated = r.last_pricing_update ? formatDateTimeBlock(r.last_pricing_update) : '';
            return '<div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="openPricingModal(\'' + r.id + '\')">' +
                '<div class="flex items-start gap-3">' +
                '<div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100"><img src="' + img + '" alt="' + escapeHtml(r.title) + '" class="w-full h-full object-cover"></div>' +
                '<div class="flex-1 min-w-0">' +
                '<div class="mb-1"><div class="flex items-center gap-1 mb-1"><span class="badge badge-green">' + (r.stores_count || 0) + '</span></div>' +
                '<h4 class="text-sm font-medium text-secondary hover:text-primary pr-2 break-words">' + escapeHtml(r.title) + '</h4></div>' +
                '<div class="text-xs text-gray-500 mb-1">' + escapeHtml(r.category_name || '(Uncategorized)') + '</div>' +
                '<div class="text-sm text-gray-900 leading-tight">' + range + '</div>' +
                '<div class="text-xs text-gray-500 mt-1 leading-tight">' + updated + '</div>' +
                '</div></div></div>';
        }).join('');

        renderPagination();
    }

    function renderPagination() {
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        const pagNums = document.getElementById('pagination-numbers');
        pagNums.innerHTML = '';
        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) pagNums.appendChild(createPagButton(i));
        } else {
            pagNums.appendChild(createPagButton(1));
            if (currentPage > 3) {
                const e1 = document.createElement('span');
                e1.textContent = '...';
                e1.classList.add('px-2');
                pagNums.appendChild(e1);
            }
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                pagNums.appendChild(createPagButton(i));
            }
            if (currentPage < totalPages - 2) {
                const e2 = document.createElement('span');
                e2.textContent = '...';
                e2.classList.add('px-2');
                pagNums.appendChild(e2);
            }
            if (totalPages > 1) pagNums.appendChild(createPagButton(totalPages));
        }
    }

    function createPagButton(page) {
        const btn = document.createElement('button');
        btn.className = (page === currentPage) ? 'px-3 py-2 rounded-lg bg-primary text-white' : 'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        btn.textContent = page;
        btn.addEventListener('click', () => { currentPage = page; renderPagination(); renderProducts(pricedProducts); });
        return btn;
    }

    function priceRangeBlock(minP, maxP) {
        if (minP == null || typeof minP === 'undefined' || maxP == null || typeof maxP === 'undefined') return '';
        return '<div>Min - ' + formatMoneyClean(minP) + '</div><div>Max - ' + formatMoneyClean(maxP) + '</div>';
    }

    function formatMoneyClean(n) {
        const v = Number(n);
        if (!isFinite(v)) return '';
        if (Math.abs(v - Math.round(v)) < 1e-9) return Math.round(v).toLocaleString();
        const s = v.toFixed(2).replace(/\.?0+$/, '');
        const parts = s.split('.');
        parts[0] = Number(parts[0]).toLocaleString();
        return parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
    }

    function capFirst(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    }

    function formatDateTimeBlock(s) {
        if (!s) return '';
        const d = new Date(s);
        if (isNaN(d.getTime())) return '';
        const dd = String(d.getDate()).padStart(2, '0');
        const mmmArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const mmm = mmmArr[d.getMonth()];
        const yyyy = d.getFullYear();
        let h = d.getHours();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        const mm = String(d.getMinutes()).padStart(2, '0');
        const datePart = `${dd}/${mmm}/${yyyy}`;
        const timePart = `${h}:${mm} ${ampm}`;
        return `<div>${datePart}</div><div>${timePart}</div>`;
    }

    function openPricingModal(productId) {
        showLoading('Loading pricing details...');
        fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getProductPricing&product_id=${encodeURIComponent(productId)}`)
            .then(res => { if (res.status === 401) { showSessionExpiredModal(); throw new Error('unauth'); } return res.json(); })
            .then(data => {
                hideLoading();
                if (!data.success) { showErrorNotification(data.message || 'Failed to load pricing'); return; }
                modalData.product = data.product || {};
                modalData.rows = data.rows || [];
                document.getElementById('modalTitle').textContent = 'Product Pricing';
                document.getElementById('modalProductName').textContent = modalData.product.title || '';
                document.getElementById('modalCategoryName').textContent = modalData.product.category_name || '(Uncategorized)';
                document.getElementById('modalFilterPriceCategory').value = '';
                document.getElementById('modalSearchStore').value = '';
                filterModalRows();
                document.getElementById('pricingModal').classList.remove('hidden');
            })
            .catch(() => { hideLoading(); showErrorNotification('Failed to load pricing details'); });
    }

    function hidePricingModal() {
        document.getElementById('pricingModal').classList.add('hidden');
    }

    function filterModalRows() {
        const pc = document.getElementById('modalFilterPriceCategory').value;
        const q = document.getElementById('modalSearchStore').value.toLowerCase();
        const filtered = modalData.rows.filter(r => {
            const okPC = pc ? r.price_category === pc : true;
            const okStore = q ? (r.store_name || '').toLowerCase().includes(q) : true;
            return okPC && okStore;
        });
        renderVendorPricing(filtered);
    }

    function formatPackageSize(val) {
        const t = String(val || '').trim();
        const mixed = /^(\d+)\s+(\d+)\/(\d+)$/;
        const frac = /^(\d+)\/(\d+)$/;
        if (mixed.test(t)) {
            const m = t.match(mixed);
            const whole = m[1], num = m[2], den = m[3];
            return `<span class="whitespace-nowrap">${whole} <span class="align-text-top text-[11px]">${num}</span>/<span class="align-text-bottom text-[11px]">${den}</span></span>`;
        } else if (frac.test(t)) {
            const m = t.match(frac);
            const num = m[1], den = m[2];
            return `<span class="whitespace-nowrap"><span class="align-text-top text-[11px]">${num}</span>/<span class="align-text-bottom text-[11px]">${den}</span></span>`;
        }
        return escapeHtml(t);
    }

    function renderVendorPricing(rows) {
        const container = document.getElementById('vendorPricingContainer');
        if (!rows.length) {
            container.innerHTML = '<div class="p-4 text-center text-gray-500">No pricing matched the filters</div>';
            return;
        }
        const grouped = {};
        rows.forEach(r => {
            const sid = r.store_id;
            if (!grouped[sid]) grouped[sid] = { store_id: sid, store_name: r.store_name, region: r.region, district: r.district, items: [] };
            grouped[sid].items.push(r);
        });
        const blocks = Object.values(grouped).map(g => {
            const itemsHtml = g.items.map(it => {
                const commission = it.commission_type === 'percentage' ? `${formatMoneyClean(it.commission_value)}%` : `${formatMoneyClean(it.commission_value)}`;
                const pkgHTML = [escapeHtml(it.package_name || ''), formatPackageSize(it.package_size), escapeHtml(it.si_unit || '')].filter(Boolean).join(' ');
                const updated = it.updated_at ? formatDateTimeBlock(it.updated_at) : '';
                const pcBadge = it.price_category === 'retail' ? 'badge badge-green' : it.price_category === 'wholesale' ? 'badge badge-yellow' : 'badge badge-purple';
                return '<tr class="border-b last:border-b-0 align-top">' +
                    '<td class="px-3 py-2"><span class="' + pcBadge + '">' + capFirst(it.price_category) + '</span></td>' +
                    '<td class="px-3 py-2">' + formatMoneyClean(it.price) + '</td>' +
                    '<td class="px-3 py-2">' + pkgHTML + '</td>' +
                    '<td class="px-3 py-2">' + commission + '</td>' +
                    '<td class="px-3 py-2">' + (it.delivery_capacity !== null ? it.delivery_capacity : '') + '</td>' +
                    '<td class="px-3 py-2 text-xs text-gray-600 leading-tight">' + updated + '</td>' +
                    '</tr>';
            }).join('');
            return '<div class="vendor-card">' +
                '<div class="vendor-header">' +
                '<div class="text-sm font-semibold text-secondary">' + escapeHtml(g.store_name || 'Store') + '</div>' +
                '<div class="text-xs text-gray-500">' + escapeHtml([g.region, g.district].filter(Boolean).join(', ')) + '</div>' +
                '</div>' +
                '<div class="vendor-body overflow-x-auto">' +
                '<table class="w-full text-sm">' +
                '<thead class="bg-gray-50"><tr>' +
                '<th class="text-left px-3 py-2">Category</th>' +
                '<th class="text-left px-3 py-2">Price</th>' +
                '<th class="text-left px-3 py-2">Package</th>' +
                '<th class="text-left px-3 py-2">Commission</th>' +
                '<th class="text-left px-3 py-2">Capacity</th>' +
                '<th class="text-left px-3 py-2">Updated</th>' +
                '</tr></thead>' +
                '<tbody>' + itemsHtml + '</tbody>' +
                '</table>' +
                '</div>' +
                '</div>';
        }).join('');
        container.innerHTML = blocks;
    }

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
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

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function showSessionExpiredModal() {
        window.location.href = BASE_URL;
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>