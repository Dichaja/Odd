<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Products Performance Analytics';
$activeNav = 'products-performance';
ob_start();
?>

<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Products Performance Analytics</h1>
                        <div id="connectionStatus"
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-green-700">Live Updates</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor product views and
                        engagement metrics</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <button id="exportBtn"
                        class="px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-download text-sm"></i>
                        <span class="hidden sm:inline">Export</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Products</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalProducts">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-box text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Unique Views</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="totalUniqueViews">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-eye text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Total Views</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="totalCumulativeViews">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chart-bar text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Today's Views</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="todayViews">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-day text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Analytics Controls</h2>
                    <p class="text-sm text-gray-600">Configure your analytics view and filters</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="today">
                            <i class="fas fa-calendar-day mr-2"></i>Today
                        </button>
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="week">
                            <i class="fas fa-calendar-week mr-2"></i> Weekly
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="month">
                            <i class="fas fa-calendar-alt mr-2"></i> Monthly
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" id="startDate"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" id="endDate"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <button id="applyCustomRange"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">Apply</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">View Type</label>
                    <select id="viewTypeFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="both">Both View Types</option>
                        <option value="unique">Unique Views (by session)</option>
                        <option value="cumulative">Cumulative Views (all)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category Filter</label>
                    <select id="categoryFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="all">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="unique_desc">Highest Unique Views</option>
                        <option value="unique_asc">Lowest Unique Views</option>
                        <option value="cumulative_desc">Highest Total Views</option>
                        <option value="cumulative_asc">Lowest Total Views</option>
                        <option value="recent">Most Recently Viewed</option>
                        <option value="title">Product Name (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-secondary">Views Over Time</h3>
                    <div class="flex items-center gap-2">
                        <button id="chartTypeToggle"
                            class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-1"></i> Line Chart
                        </button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-secondary">Top Categories</h3>
                    <span class="text-sm text-gray-500" id="categoryChartPeriod">This Week</span>
                </div>
                <div class="h-80">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Product Performance</h3>
                        <p class="text-sm text-gray-600">Click on any product to view detailed analytics</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search products..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="statusFilter"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Products</option>
                            <option value="published">Published Only</option>
                            <option value="featured">Featured Products</option>
                            <option value="with_pricing">With Pricing</option>
                        </select>
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
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                id="uniqueViewsHeader">Unique Views</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                id="totalViewsHeader">Total Views</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                id="engagementHeader">Engagement Rate</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Last Viewed</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading products...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Previous</button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>

            <div class="lg:hidden" id="productsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading products...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingCount">0</span> of <span id="mobileTotalCount">0</span> products
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

<!-- Modal -->
<div id="productModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeProductModal()"></div>
    <div
        class="relative w-full h-full max-w-4xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div id="modalProductImage" class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 overflow-hidden">
                    <img src="https://placehold.co/48x48/e2e8f0/1e293b?text=Product" alt=""
                        class="w-full h-full object-cover">
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Product Analytics</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalSubtitle"></p>
                </div>
            </div>
            <button onclick="closeProductModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-100px)]" id="productContent">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading product analytics...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let products = [];
    let currentProductId = null;
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'week';
    let currentViewType = 'both';
    let totalProducts = 0;
    let viewsChart = null;
    let categoryChart = null;

    // Modal-local state
    let modalPeriod = 'week';
    let modalStart = null;  // 'YYYY-MM-DD'
    let modalEnd = null;    // 'YYYY-MM-DD'
    let modalMonth = null;  // 'YYYY-MM'
    let modalChart = null;

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        initializeDateFilters();
        loadProductsData();
        loadChartData();
        loadCategories();
    });

    // ---------- Utilities ----------
    const tzOffsetMinutes = 3 * 60; // Africa/Kampala UTC+3
    const todayISO = () => toISODate(new Date(new Date().getTime() + tzOffsetMinutes * 60000));
    function toISODate(d) {
        // strip time, return YYYY-MM-DD in UTC (stable for comparisons)
        const t = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        return t.toISOString().slice(0, 10);
    }
    function parseISODate(s) {
        const [y, m, d] = s.split('-').map(Number);
        return new Date(Date.UTC(y, m - 1, d));
    }
    function endOfWeekFromAny(dateISO) { // returns {startISO, endISO} Sun..Sat
        const d = parseISODate(dateISO);
        const dow = d.getUTCDay(); // 0 Sun
        const start = new Date(d); start.setUTCDate(start.getUTCDate() - dow);
        const end = new Date(start); end.setUTCDate(start.getUTCDate() + 6);
        return { startISO: toISODate(start), endISO: toISODate(end) };
    }
    function monthBounds(yyyyMM) { // 'YYYY-MM' -> {startISO,lastISO}
        const [y, m] = yyyyMM.split('-').map(Number);
        const first = new Date(Date.UTC(y, m - 1, 1));
        const last = new Date(Date.UTC(y, m, 0));
        return { startISO: toISODate(first), lastISO: toISODate(last) };
    }
    function clampToToday(nextStartISO, nextEndISO) {
        // returns {startISO,endISO, atMax:boolean}
        const today = todayISO();
        const end = nextEndISO || nextStartISO;
        if (end > today) {
            return { startISO: nextStartISO, endISO: today, atMax: true };
        }
        return { startISO: nextStartISO, endISO: nextEndISO, atMax: false };
    }
    function ordinal(n) { const s = ["th", "st", "nd", "rd"], v = n % 100; return n + (s[(v - 20) % 10] || s[v] || s[0]); }

    // ---------- Event wiring ----------
    function setupEventListeners() {
        document.getElementById('refreshBtn').addEventListener('click', refreshData);
        document.getElementById('exportBtn').addEventListener('click', exportData);
        document.getElementById('searchFilter').addEventListener('input', debounce(applyFilters, 300));
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('viewTypeFilter').addEventListener('change', applyFilters);
        document.getElementById('categoryFilter').addEventListener('change', applyFilters);
        document.getElementById('sortFilter').addEventListener('change', applyFilters);
        document.getElementById('chartTypeToggle').addEventListener('click', toggleChartType);

        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });
                this.classList.add('active', 'bg-primary', 'text-white', 'border-primary');
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                setDateRangeForPeriod(this.dataset.period);
            });
        });

        document.getElementById('applyCustomRange').addEventListener('click', function () {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if (startDate && endDate) {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });
                currentPeriod = 'custom';
                currentPage = 1;
                updateCategoryChartLabel();
                loadProductsData();
                loadChartData();
            }
        });

        ['prevPage', 'nextPage', 'mobilePrevPage', 'mobileNextPage'].forEach(id => {
            document.getElementById(id).addEventListener('click', function () {
                if (id.includes('prev') && currentPage > 1) { currentPage--; loadProductsData(); }
                else if (id.includes('next')) { currentPage++; loadProductsData(); }
            });
        });
    }

    // ---------- Filters / loads ----------
    function applyFilters() {
        currentPage = 1;
        currentViewType = document.getElementById('viewTypeFilter').value;
        updateTableHeaders();
        loadProductsData();
        loadChartData();
    }

    function updateTableHeaders() {
        const uniqueHeader = document.getElementById('uniqueViewsHeader');
        const totalHeader = document.getElementById('totalViewsHeader');
        const engagementHeader = document.getElementById('engagementHeader');
        if (currentViewType === 'unique') { uniqueHeader.style.display = ''; totalHeader.style.display = 'none'; engagementHeader.style.display = 'none'; }
        else if (currentViewType === 'cumulative') { uniqueHeader.style.display = 'none'; totalHeader.style.display = ''; engagementHeader.style.display = 'none'; }
        else { uniqueHeader.style.display = ''; totalHeader.style.display = ''; engagementHeader.style.display = ''; }
    }

    function updateCategoryChartLabel() {
        const label = document.getElementById('categoryChartPeriod');
        switch (currentPeriod) {
            case 'today': label.textContent = 'Today'; label.style.display = ''; break;
            case 'week': label.textContent = 'This Week'; label.style.display = ''; break;
            case 'month': label.textContent = 'This Month'; label.style.display = ''; break;
            case 'custom': label.style.display = 'none'; break;
            default: label.textContent = 'This Week'; label.style.display = '';
        }
    }

    async function loadProductsData() {
        try {
            const params = new URLSearchParams({
                action: 'get_products',
                page: currentPage,
                limit: itemsPerPage,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod,
                view_type: currentViewType,
                category: document.getElementById('categoryFilter').value,
                sort: document.getElementById('sortFilter').value,
                status: document.getElementById('statusFilter').value,
                search: document.getElementById('searchFilter').value
            });
            const response = await fetch(`fetch/manageProductsPerformance.php?${params}`);
            const data = await response.json();
            if (data.success) {
                products = data.data;
                totalProducts = data.total;
                updateStatistics(data.stats);
                renderProductsTable();
                renderProductsCards();
                updatePagination(data.total, data.page);
            } else {
                showError('Failed to load products data');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            showError('Failed to load products data');
        }
    }

    async function loadChartData() {
        try {
            const params = new URLSearchParams({
                action: 'get_chart_data',
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod,
                view_type: currentViewType
            });
            const response = await fetch(`fetch/manageProductsPerformance.php?${params}`);
            const data = await response.json();
            if (data.success) {
                // Normalize general chart as well (daily/week/month rules)
                const normalized = buildGlobalTimeline(data.timeline);
                updateViewsChart(normalized);
                updateCategoryChart(data.categories);
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    async function loadCategories() {
        try {
            const response = await fetch('fetch/manageProductsPerformance.php?action=get_categories');
            const data = await response.json();
            if (data.success) {
                const categoryFilter = document.getElementById('categoryFilter');
                categoryFilter.innerHTML = '<option value="all">All Categories</option>';
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categoryFilter.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    function updateStatistics(stats) {
        if (!stats) return;
        document.getElementById('totalProducts').textContent = stats.total_products.toLocaleString();
        document.getElementById('totalUniqueViews').textContent = stats.total_unique_views.toLocaleString();
        document.getElementById('totalCumulativeViews').textContent = stats.total_cumulative_views.toLocaleString();
        document.getElementById('todayViews').textContent = stats.today_views.toLocaleString();
    }

    // ---------- Charts (general) ----------
    function updateViewsChart(norm) {
        const ctx = document.getElementById('viewsChart').getContext('2d');
        if (viewsChart) viewsChart.destroy();

        const chartType = document.getElementById('chartTypeToggle').textContent.includes('Line') ? 'line' : 'bar';
        const datasets = [];
        if (currentViewType === 'both' || currentViewType === 'unique') {
            datasets.push({
                label: 'Unique Views',
                data: norm.unique,
                borderColor: '#10B981',
                backgroundColor: chartType === 'bar' ? '#10B981' : 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: chartType === 'line'
            });
        }
        if (currentViewType === 'both' || currentViewType === 'cumulative') {
            datasets.push({
                label: 'Total Views',
                data: norm.total,
                borderColor: '#8B5CF6',
                backgroundColor: chartType === 'bar' ? '#8B5CF6' : 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: chartType === 'line'
            });
        }

        viewsChart = new Chart(ctx, {
            type: chartType,
            data: { labels: norm.labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() } } }
            }
        });
    }

    function updateCategoryChart(categoryData) {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        if (categoryChart) categoryChart.destroy();
        categoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categoryData.labels,
                datasets: [{ data: categoryData.values, backgroundColor: ['#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#84CC16', '#6366F1'] }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // ---------- Products list render ----------
    function renderProductsTable() {
        const tbody = document.getElementById('productsBody');
        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-box-open text-2xl mb-2"></i>
                        <div>No products found</div>
                    </td>
                </tr>`;
            return;
        }
        tbody.innerHTML = products.map(product => {
            const engagementRate = product.total_views > 0 ? ((product.unique_views / product.total_views) * 100).toFixed(1) : '0.0';
            let viewsColumns = '';
            if (currentViewType === 'both') {
                viewsColumns = `
                    <td class="px-4 py-3 text-center"><span class="text-sm font-medium text-green-600">${product.unique_views.toLocaleString()}</span></td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-medium text-purple-600">${product.total_views.toLocaleString()}</span></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: ${Math.min(engagementRate, 100)}%"></div>
                            </div>
                            <span class="text-xs text-gray-600">${engagementRate}%</span>
                        </div>
                    </td>`;
            } else if (currentViewType === 'unique') {
                viewsColumns = `<td class="px-4 py-3 text-center"><span class="text-sm font-medium text-green-600">${product.unique_views.toLocaleString()}</span></td>`;
            } else {
                viewsColumns = `<td class="px-4 py-3 text-center"><span class="text-sm font-medium text-purple-600">${product.total_views.toLocaleString()}</span></td>`;
            }
            return `
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewProductDetails('${product.id}')">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-lg overflow-hidden bg-gray-100">
                                <img src="${product.primary_image}" alt="${product.title}" class="w-full h-full object-cover">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-secondary max-w-xs truncate">${product.title}</div>
                                ${product.featured ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">Featured</span>' : ''}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center"><span class="text-sm text-gray-900">${product.category_name || 'Uncategorized'}</span></td>
                    ${viewsColumns}
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="text-sm text-gray-900">${product.last_viewed ? formatDate(product.last_viewed) : 'Never'}</div>
                        <div class="text-xs text-gray-500">${product.last_viewed ? formatTime(product.last_viewed) : ''}</div>
                    </td>
                </tr>`;
        }).join('');
    }

    function renderProductsCards() {
        const container = document.getElementById('productsCards');
        if (products.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-box-open text-2xl mb-2"></i>
                    <div>No products found</div>
                </div>`;
            return;
        }
        container.innerHTML = products.map(product => {
            const engagementRate = product.total_views > 0 ? ((product.unique_views / product.total_views) * 100).toFixed(1) : '0.0';
            let viewsSection = '';
            if (currentViewType === 'both') {
                viewsSection = `
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div><span class="text-xs text-gray-500">Unique Views</span><div class="text-sm font-medium text-green-600">${product.unique_views.toLocaleString()}</div></div>
                        <div><span class="text-xs text-gray-500">Total Views</span><div class="text-sm font-medium text-purple-600">${product.total_views.toLocaleString()}</div></div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 bg-gray-200 rounded-full h-1.5 mr-2">
                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: ${Math.min(engagementRate, 100)}%"></div>
                            </div>
                            <span class="text-xs text-gray-600">${engagementRate}%</span>
                        </div>
                        <span class="text-xs text-gray-500">${product.last_viewed ? formatDate(product.last_viewed) : 'Never viewed'}</span>
                    </div>`;
            } else if (currentViewType === 'unique') {
                viewsSection = `
                    <div class="flex items-center justify-between">
                        <div><span class="text-xs text-gray-500">Unique Views</span><div class="text-sm font-medium text-green-600">${product.unique_views.toLocaleString()}</div></div>
                        <span class="text-xs text-gray-500">${product.last_viewed ? formatDate(product.last_viewed) : 'Never viewed'}</span>
                    </div>`;
            } else {
                viewsSection = `
                    <div class="flex items-center justify-between">
                        <div><span class="text-xs text-gray-500">Total Views</span><div class="text-sm font-medium text-purple-600">${product.total_views.toLocaleString()}</div></div>
                        <span class="text-xs text-gray-500">${product.last_viewed ? formatDate(product.last_viewed) : 'Never viewed'}</span>
                    </div>`;
            }
            return `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewProductDetails('${product.id}')">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100">
                            <img src="${product.primary_image}" alt="${product.title}" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-medium text-secondary truncate">${product.title}</h4>
                                ${product.featured ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Featured</span>' : ''}
                            </div>
                            <div class="text-xs text-gray-500 mb-2">${product.category_name || 'Uncategorized'}</div>
                            ${viewsSection}
                        </div>
                    </div>
                </div>`;
        }).join('');
    }

    // ---------- Modal ----------
    async function viewProductDetails(productId) {
        currentProductId = productId;

        // Initialize modal range from main filters
        modalPeriod = currentPeriod;
        modalStart = document.getElementById('startDate').value || todayISO();
        modalEnd = document.getElementById('endDate').value || todayISO();

        if (modalPeriod === 'today') { modalStart = todayISO(); modalEnd = todayISO(); }
        if (modalPeriod === 'week') { const w = endOfWeekFromAny(modalStart); modalStart = w.startISO; modalEnd = w.endISO; }
        if (modalPeriod === 'month') {
            const d = parseISODate(modalStart);
            modalMonth = `${d.getUTCFullYear()}-${String(d.getUTCMonth() + 1).padStart(2, '0')}`;
            const mb = monthBounds(modalMonth); modalStart = mb.startISO; modalEnd = mb.lastISO;
        }

        document.getElementById('productModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        await fetchAndRenderModal();
    }

    async function fetchAndRenderModal() {
        try {
            // Avoid future navigation
            const capped = clampToToday(modalStart, modalEnd);
            modalStart = capped.startISO; modalEnd = capped.endISO;

            const params = new URLSearchParams({
                action: 'get_product_details',
                id: currentProductId,
                start_date: modalStart,
                end_date: modalEnd,
                period: modalPeriod
            });
            const response = await fetch(`fetch/manageProductsPerformance.php?${params}`);
            const data = await response.json();
            if (data.success) {
                loadProductDetails(data.data);
            } else {
                showError('Failed to load product details');
            }
        } catch (e) {
            console.error(e);
            showError('Failed to load product details');
        }
    }

    function loadProductDetails(product) {
        document.getElementById('modalTitle').textContent = product.title;
        document.getElementById('modalSubtitle').textContent = `${product.category_name || 'Uncategorized'}`;

        const modalImage = document.getElementById('modalProductImage').querySelector('img');
        modalImage.src = product.primary_image; modalImage.alt = product.title;

        const engagementRate = product.total_views > 0 ? ((product.unique_views / product.total_views) * 100).toFixed(1) : '0.0';

        // Build a normalized, zero-filled timeline for the modal
        const fullTimeline = buildModalTimeline(product.timeline);

        const productContent = document.getElementById('productContent');
        productContent.innerHTML = `
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200"><div class="text-2xl font-bold text-green-900">${product.unique_views.toLocaleString()}</div><div class="text-sm text-green-600">Unique Views</div></div>
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200"><div class="text-2xl font-bold text-purple-900">${product.total_views.toLocaleString()}</div><div class="text-sm text-purple-600">Total Views</div></div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200"><div class="text-2xl font-bold text-blue-900">${engagementRate}%</div><div class="text-sm text-blue-600">Engagement Rate</div></div>
                    <div class="bg-orange-50 rounded-lg p-4 border border-orange-200"><div class="text-2xl font-bold text-orange-900">${(product.avg_daily_views ?? 0).toFixed(1)}</div><div class="text-sm text-orange-600">Avg Daily Views</div></div>
                </div>

                <div class="bg-white rounded-lg p-4 md:p-6 border border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <h4 class="text-lg font-semibold text-secondary flex items-center gap-2">
                            <span>View Timeline</span>
                            <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 capitalize">${modalPeriod === 'today' ? 'daily' : modalPeriod}</span>
                        </h4>
                        <div class="flex flex-wrap items-center gap-2">
                            <button id="modalPrev" class="px-3 py-1.5 border rounded hover:bg-gray-50"><i class="fas fa-chevron-left"></i></button>
                            <div id="modalRangeLabel" class="text-sm text-gray-700 font-medium"></div>
                            <button id="modalNext" class="px-3 py-1.5 border rounded hover:bg-gray-50"><i class="fas fa-chevron-right"></i></button>

                            <div id="modalPickers" class="flex items-center gap-2"></div>
                            <button id="modalApply" class="px-3 py-1.5 bg-primary text-white rounded hover:bg-primary/90 text-sm">Apply</button>
                        </div>
                    </div>

                    <div class="h-64"><canvas id="productTimelineChart"></canvas></div>
                </div>

                ${product.recent_sessions && product.recent_sessions.length > 0 ? `
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h4 class="text-lg font-semibold text-secondary mb-4">Recent Viewing Sessions</h4>
                    <div class="space-y-3">
                        ${product.recent_sessions.map(session => `
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Session: ${session.session_id.substring(0, 8)}...</div>
                                    <div class="text-xs text-gray-500">${session.view_count} views in this session</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-900">${formatDate(session.first_view)}</div>
                                    <div class="text-xs text-gray-500">${formatTime(session.last_view)}</div>
                                </div>
                            </div>`).join('')}
                    </div>
                </div>` : ''}
            </div>`;

        // Inject pickers + range label and draw chart
        renderModalPickersAndLabel();
        const ctx = document.getElementById('productTimelineChart').getContext('2d');
        if (modalChart) modalChart.destroy();

        const datasets = [];
        if (currentViewType === 'both' || currentViewType === 'unique') {
            datasets.push({ label: modalPeriod === 'today' ? 'Unique (2-hr)' : 'Unique', data: fullTimeline.unique, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.12)', tension: 0.4, fill: true });
        }
        if (currentViewType === 'both' || currentViewType === 'cumulative') {
            datasets.push({ label: modalPeriod === 'today' ? 'Total (2-hr)' : 'Total', data: fullTimeline.total, borderColor: '#8B5CF6', backgroundColor: 'rgba(139,92,246,0.12)', tension: 0.4, fill: true });
        }

        modalChart = new Chart(ctx, {
            type: 'line',
            data: { labels: fullTimeline.labels, datasets },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });

        // Wire modal controls with future-guarding
        document.getElementById('modalPrev').onclick = () => shiftModalRange(-1);
        document.getElementById('modalNext').onclick = () => shiftModalRange(1);
        document.getElementById('modalApply').onclick = applyModalPickers;
        // Disable next if at max
        const atMax = isModalAtMax();
        document.getElementById('modalNext').disabled = atMax;
        document.getElementById('modalNext').classList.toggle('opacity-50', atMax);
    }

    function closeProductModal() {
        document.getElementById('productModal').classList.add('hidden');
        document.body.style.overflow = '';
        currentProductId = null;
    }

    // ---------- Global timeline normalization ----------
    function buildGlobalTimeline(server) {
        // server: {labels, unique_views, total_views}
        const startISO = document.getElementById('startDate').value;
        const endISO = document.getElementById('endDate').value;
        return normalizeTimelineForPeriod(server, currentPeriod, startISO, endISO);
    }

    // ---------- Modal timeline normalization ----------
    function buildModalTimeline(server) {
        return normalizeTimelineForPeriod(server, modalPeriod, modalStart, modalEnd);
    }

    // Core normalizer used by both charts
    function normalizeTimelineForPeriod(serverTimeline, period, startISO, endISO) {
        const labelsFromServer = serverTimeline?.labels || [];
        const uRaw = serverTimeline?.unique_views ?? serverTimeline?.unique ?? serverTimeline?.values ?? [];
        const tRaw = serverTimeline?.total_views ?? serverTimeline?.total ?? serverTimeline?.values ?? [];
        const asInt = arr => arr.map(v => parseInt(v || 0, 10));

        if (period === 'today') {
            // 12 bins across 24h => 13 labels: 12,2,4,...,10,12 (duplicate first label at end)
            const baseLabels = [...Array(12)].map((_, i) => {
                const h = (i * 2) % 12;
                return h === 0 ? '12' : String(h);
            });
            const labels = [...baseLabels, '12'];
            // If server provided 12 points (2-hour bins), use them; otherwise zero-fill
            const uniq12 = (uRaw.length === 12) ? asInt(uRaw) : Array(12).fill(0);
            const tot12 = (tRaw.length === 12) ? asInt(tRaw) : Array(12).fill(0);
            // close the loop by repeating first point
            const unique = [...uniq12, uniq12[0] ?? 0];
            const total = [...tot12, tot12[0] ?? 0];
            return { labels, unique, total };
        }

        if (period === 'week') {
            // Always Sun..Sat from the selected week
            const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const idxByDate = new Map();
            labelsFromServer.forEach((d, i) => idxByDate.set(d, i));
            const uniq = []; const tot = []; const labels = weekDays.slice();

            const { startISO: sISO } = endOfWeekFromAny(startISO || todayISO());
            for (let i = 0; i < 7; i++) {
                const d = parseISODate(sISO); d.setUTCDate(d.getUTCDate() + i);
                const key = toISODate(d);
                const idx = idxByDate.get(key);
                uniq.push(idx !== undefined ? parseInt(uRaw[idx] || 0, 10) : 0);
                tot.push(idx !== undefined ? parseInt(tRaw[idx] || 0, 10) : 0);
            }
            return { labels, unique: uniq, total: tot };
        }

        if (period === 'month') {
            // 1st..last (ordinal labels)
            const start = startISO ? parseISODate(startISO) : parseISODate(todayISO());
            const end = endISO ? parseISODate(endISO) : parseISODate(todayISO());
            const daysInMonth = new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth() + 1, 0)).getUTCDate();
            const idxByDate = new Map();
            labelsFromServer.forEach((d, i) => idxByDate.set(d, i));

            const labels = []; const uniq = []; const tot = [];
            for (let day = 1; day <= daysInMonth; day++) {
                const d = new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth(), day));
                const key = toISODate(d);
                const idx = idxByDate.get(key);
                labels.push(ordinal(day));
                uniq.push(idx !== undefined ? parseInt(uRaw[idx] || 0, 10) : 0);
                tot.push(idx !== undefined ? parseInt(tRaw[idx] || 0, 10) : 0);
            }
            return { labels, unique: uniq, total: tot };
        }

        // custom / fallback: pass through
        return { labels: labelsFromServer, unique: asInt(uRaw), total: asInt(tRaw) };
    }

    // ---------- Modal controls / pickers ----------
    function renderModalPickersAndLabel() {
        const wrap = document.getElementById('modalPickers');
        wrap.innerHTML = '';
        const label = document.getElementById('modalRangeLabel');

        if (modalPeriod === 'today') {
            const input = document.createElement('input');
            input.type = 'date';
            input.id = 'modalDay';
            input.className = 'px-2 py-1.5 border rounded text-sm';
            input.max = todayISO();
            input.value = modalStart || todayISO();
            wrap.appendChild(input);
            modalStart = input.value; modalEnd = input.value;
            label.textContent = new Date(modalStart).toDateString();
        } else if (modalPeriod === 'week') {
            // ONE picker: pick any day; we compute Sun..Sat around it
            const wk = document.createElement('input');
            wk.type = 'date';
            wk.id = 'modalWeekAnyDay';
            wk.className = 'px-2 py-1.5 border rounded text-sm';
            wk.max = todayISO();
            wk.value = modalStart || todayISO();
            wrap.appendChild(wk);
            const range = endOfWeekFromAny(wk.value);
            modalStart = range.startISO; modalEnd = range.endISO;
            label.textContent = `${new Date(modalStart).toDateString()} – ${new Date(modalEnd).toDateString()}`;
        } else if (modalPeriod === 'month') {
            const m = document.createElement('input');
            m.type = 'month';
            m.id = 'modalMonth';
            m.className = 'px-2 py-1.5 border rounded text-sm';
            const cur = modalMonth || `${parseISODate(modalStart).getUTCFullYear()}-${String(parseISODate(modalStart).getUTCMonth() + 1).padStart(2, '0')}`;
            // max is current month
            const now = new Date(); const maxMonth = `${now.getUTCFullYear()}-${String(now.getUTCMonth() + 1).padStart(2, '0')}`;
            m.max = maxMonth; m.value = cur;
            wrap.appendChild(m);
            const mb = monthBounds(m.value); modalStart = mb.startISO; modalEnd = mb.lastISO; modalMonth = m.value;
            const [yy, mm] = m.value.split('-').map(Number);
            const first = new Date(Date.UTC(yy, mm - 1, 1));
            label.textContent = `${first.toLocaleString('en-US', { month: 'long' })} ${yy}`;
        }
    }

    function shiftModalRange(step) {
        if (modalPeriod === 'today') {
            const d = parseISODate(modalStart); d.setUTCDate(d.getUTCDate() + step);
            let ns = toISODate(d); let ne = ns;
            const capped = clampToToday(ns, ne); modalStart = capped.startISO; modalEnd = capped.endISO;
        } else if (modalPeriod === 'week') {
            const s = parseISODate(modalStart); s.setUTCDate(s.getUTCDate() + (7 * step));
            const e = new Date(s); e.setUTCDate(s.getUTCDate() + 6);
            const capped = clampToToday(toISODate(s), toISODate(e)); modalStart = capped.startISO; modalEnd = capped.endISO;
        } else if (modalPeriod === 'month') {
            const [yy, mm] = modalMonth.split('-').map(Number);
            const d = new Date(Date.UTC(yy, mm - 1 + step, 1));
            const nextMonth = `${d.getUTCFullYear()}-${String(d.getUTCMonth() + 1).padStart(2, '0')}`;
            const mb = monthBounds(nextMonth);
            const capped = clampToToday(mb.startISO, mb.lastISO);
            modalMonth = `${parseISODate(capped.startISO).getUTCFullYear()}-${String(parseISODate(capped.startISO).getUTCMonth() + 1).padStart(2, '0')}`;
            modalStart = capped.startISO; modalEnd = capped.endISO;
        }
        fetchAndRenderModal();
    }

    function applyModalPickers() {
        if (modalPeriod === 'today') {
            const v = document.getElementById('modalDay').value;
            if (v) { const capped = clampToToday(v, v); modalStart = capped.startISO; modalEnd = capped.endISO; }
        } else if (modalPeriod === 'week') {
            const d = document.getElementById('modalWeekAnyDay').value || todayISO();
            const range = endOfWeekFromAny(d);
            const capped = clampToToday(range.startISO, range.endISO); modalStart = capped.startISO; modalEnd = capped.endISO;
        } else if (modalPeriod === 'month') {
            const m = document.getElementById('modalMonth').value;
            if (m) { const mb = monthBounds(m); const capped = clampToToday(mb.startISO, mb.lastISO); modalMonth = m; modalStart = capped.startISO; modalEnd = capped.endISO; }
        }
        fetchAndRenderModal();
    }

    function isModalAtMax() {
        const today = todayISO();
        if (modalPeriod === 'today') return modalEnd >= today;
        if (modalPeriod === 'week') return modalEnd >= today;
        if (modalPeriod === 'month') {
            const now = new Date(); const curMonth = `${now.getUTCFullYear()}-${String(now.getUTCMonth() + 1).padStart(2, '0')}`;
            return modalMonth >= curMonth;
        }
        return false;
    }

    // ---------- Page helpers ----------
    function toggleChartType() {
        const btn = document.getElementById('chartTypeToggle');
        const isLine = btn.textContent.includes('Line');
        btn.innerHTML = isLine ? '<i class="fas fa-chart-bar mr-1"></i> Bar Chart' : '<i class="fas fa-chart-line mr-1"></i> Line Chart';
        loadChartData();
    }

    function refreshData() {
        const btn = document.getElementById('refreshBtn'); const icon = btn.querySelector('i');
        icon.classList.add('fa-spin'); btn.disabled = true;
        Promise.all([loadProductsData(), loadChartData()]).finally(() => {
            setTimeout(() => { icon.classList.remove('fa-spin'); btn.disabled = false; }, 800);
        });
    }

    function exportData() {
        const params = new URLSearchParams({
            action: 'export',
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            period: currentPeriod,
            view_type: currentViewType,
            category: document.getElementById('categoryFilter').value,
            sort: document.getElementById('sortFilter').value
        });
        window.open(`fetch/manageProductsPerformance.php?${params}`, '_blank');
    }

    function initializeDateFilters() { setDateRangeForPeriod('week'); }

    function setDateRangeForPeriod(period) {
        const now = new Date(new Date().getTime() + tzOffsetMinutes * 60000);
        let startDate, endDate;
        switch (period) {
            case 'today':
                startDate = new Date(now); endDate = new Date(now); break;
            case 'week':
                const dow = now.getDay(); startDate = new Date(now); startDate.setDate(now.getDate() - dow);
                endDate = new Date(startDate); endDate.setDate(startDate.getDate() + 6); break;
            case 'month':
                startDate = new Date(now.getFullYear(), now.getMonth(), 1);
                endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0); break;
            default:
                startDate = new Date(now); endDate = new Date(now);
        }
        // Cap to today
        const today = toISODate(now);
        let sISO = toISODate(startDate), eISO = toISODate(endDate);
        if (eISO > today) eISO = today;

        document.getElementById('startDate').value = sISO;
        document.getElementById('endDate').value = eISO;

        currentPeriod = (period === 'today') ? 'today' : period;
        currentPage = 1;
        updateCategoryChartLabel();
        loadProductsData();
        loadChartData();
    }

    function showError(message) {
        const tbody = document.getElementById('productsBody');
        const cards = document.getElementById('productsCards');
        const errorContent = `<div class="px-4 py-8 text-center text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><div>${message}</div></div>`;
        tbody.innerHTML = `<tr><td colspan="6">${errorContent}</td></tr>`;
        cards.innerHTML = errorContent;
    }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);
        document.getElementById('showingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;
        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;

        document.getElementById('mobileShowingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('mobileTotalCount').textContent = total;
        document.getElementById('mobilePageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;
        document.getElementById('mobilePrevPage').disabled = page === 1;
        document.getElementById('mobileNextPage').disabled = page === totalPages || totalPages === 0;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
    }
    function debounce(func, wait) {
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => func(...args), wait); };
    }

    // Expose for row clicks
    window.viewProductDetails = viewProductDetails;
    window.closeProductModal = closeProductModal;
</script>

<style>
    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all .2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #D92B13;
        color: #fff;
        border-color: #D92B13;
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>