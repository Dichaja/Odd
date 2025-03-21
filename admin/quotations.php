<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Quotations';
$activeNav = 'quotations';
ob_start();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Quotations</h1>
            <p class="text-sm text-gray-text mt-1">Manage and process customer quotation requests</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="export-btn" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="refresh-btn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Date Filter Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form id="filter-form" class="flex flex-col md:flex-row items-start md:items-center gap-4">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4 w-full md:w-auto">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-2 w-full md:w-auto">
                    <label class="text-sm font-medium text-gray-700">From:</label>
                    <input type="datetime-local" id="startDate" class="h-10 pl-3 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full md:w-auto">
                </div>
                <div class="flex flex-col md:flex-row items-start md:items-center gap-2 w-full md:w-auto">
                    <label class="text-sm font-medium text-gray-700">To:</label>
                    <input type="datetime-local" id="endDate" class="h-10 pl-3 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full md:w-auto">
                </div>
            </div>
            <button type="button" id="filter-btn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-filter"></i>
                <span>Apply Filter</span>
            </button>
        </form>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6" id="stats-overview">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">New Requests</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-new">0</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-500 text-lg"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="#" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <span>View all</span>
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Processing</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-processing">0</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-500 text-lg"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="#" class="text-sm text-yellow-600 hover:text-yellow-800 flex items-center gap-1">
                    <span>View all</span>
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Processed</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-completed">0</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-check text-green-500 text-lg"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="#" class="text-sm text-green-600 hover:text-green-800 flex items-center gap-1">
                    <span>View all</span>
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Cancelled</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-cancelled">0</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="fas fa-times text-red-500 text-lg"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="#" class="text-sm text-red-600 hover:text-red-800 flex items-center gap-1">
                    <span>View all</span>
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quotations Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-secondary">Quotation Requests</h2>
            <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="search-term" placeholder="Search requests..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <select id="status-filter" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-full md:w-auto">
                    <option value="all">All Status</option>
                    <option value="new">New</option>
                    <option value="processing">Processing</option>
                    <option value="processed">Processed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="quotations-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Contact Person</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Company</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Location</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Items</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Submitted</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody id="quotations-body"></tbody>
            </table>
        </div>

        <!-- Mobile List -->
        <div class="md:hidden p-4 space-y-4" id="quotations-mobile"></div>

        <!-- Empty State -->
        <div id="empty-state" class="hidden p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <i class="fas fa-file-invoice text-gray-400 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-700 mb-2">No quotations found</h3>
            <p class="text-sm text-gray-500 mb-4">Try adjusting your search or filter criteria</p>
            <button id="reset-filters" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Reset Filters
            </button>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-count">0</span> quotations
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quote Details Offcanvas -->
<div id="quoteDetailsOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideQuoteDetails()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-2xl bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="offcanvas-title"></h3>
                <button onclick="hideQuoteDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="quoteDetailsContent"></div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/20" onclick="closeConfirmModal()"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg p-6 opacity-0 transform scale-95" id="confirmModalBox">
        <h3 class="text-lg font-medium text-gray-800 mb-4" id="confirmModalTitle">Confirm Action</h3>
        <p class="text-sm text-gray-600 mb-6" id="confirmModalMessage">Are you sure?</p>
        <div class="flex justify-end gap-3">
            <button class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50" onclick="closeConfirmModal()">Cancel</button>
            <button class="px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary/90" id="confirmModalYesBtn">Confirm</button>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <div>
            <h4 class="font-medium" id="successToastTitle">Success</h4>
            <p class="text-sm" id="successToastMessage">Operation completed successfully!</p>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div id="errorToast" class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <div>
            <h4 class="font-medium" id="errorToastTitle">Error</h4>
            <p class="text-sm" id="errorToastMessage">Something went wrong!</p>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Loading data...</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const API_BASE = "<?php echo BASE_URL; ?>admin/fetch/manageQuotations";
    let currentPage = 1;
    let totalPages = 1;

    // Notifications system
    const notifications = {
        success: function(message, title = 'Success') {
            const toast = document.getElementById('successToast');
            document.getElementById('successToastTitle').textContent = title;
            document.getElementById('successToastMessage').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        },
        error: function(message, title = 'Error') {
            const toast = document.getElementById('errorToast');
            document.getElementById('errorToastTitle').textContent = title;
            document.getElementById('errorToastMessage').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }
    };

    // Loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
        };
        return d.toLocaleString('en-US', options);
    }

    function setDateTimeInputs(startDt, endDt) {
        document.getElementById('startDate').value = startDt;
        document.getElementById('endDate').value = endDt;
    }

    function getQuotations() {
        const filterBtn = document.getElementById('filter-btn');
        filterBtn.disabled = true;
        const originalText = filterBtn.innerHTML;
        filterBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Filtering...';

        showLoading();

        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const search = document.getElementById('search-term').value;
        const status = document.getElementById('status-filter').value;

        fetch(`${API_BASE}/getQuotations?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&search=${encodeURIComponent(search)}&status=${status}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update stats
                    document.getElementById('stat-new').innerText = data.stats.new;
                    document.getElementById('stat-processing').innerText = data.stats.processing;
                    document.getElementById('stat-completed').innerText = data.stats.processed;
                    document.getElementById('stat-cancelled').innerText = data.stats.cancelled;

                    // Update showing count
                    document.getElementById('showing-count').innerText = data.quotations.length;

                    // Clear existing data
                    const tbody = document.getElementById('quotations-body');
                    tbody.innerHTML = "";
                    const mobileContainer = document.getElementById('quotations-mobile');
                    mobileContainer.innerHTML = "";

                    // Show empty state if no results
                    if (data.quotations.length === 0) {
                        document.getElementById('empty-state').classList.remove('hidden');
                        document.getElementById('quotations-table').classList.add('hidden');
                    } else {
                        document.getElementById('empty-state').classList.add('hidden');
                        document.getElementById('quotations-table').classList.remove('hidden');
                    }

                    // Populate data
                    data.quotations.forEach(q => {
                        // Desktop row
                        const tr = document.createElement('tr');
                        tr.className = "border-b border-gray-100 hover:bg-gray-50 transition-colors";

                        const companyDisplay = q.company_name ? q.company_name : '<i class="text-gray-400 text-sm">No Company</i>';

                        tr.innerHTML = `
                            <td class="px-6 py-4">
                                <div class="font-medium text-secondary">${q.contact_person}</div>
                                <div class="text-sm text-gray-400">${q.phone}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-secondary">${companyDisplay}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">${q.site_location}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    ${q.items_count} items
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${q.status_class}">
                                    ${q.status.charAt(0).toUpperCase() + q.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">${formatDate(q.created_at)}</td>
                            <td class="px-6 py-4">
                                <button 
                                    onclick="showQuoteDetails('${q.id}')" 
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors"
                                    data-tippy-content="View Details"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        `;

                        tbody.appendChild(tr);

                        // Mobile card
                        const mobileRow = document.createElement('div');
                        mobileRow.className = "bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden";
                        mobileRow.setAttribute("data-quote-id", q.id);

                        mobileRow.innerHTML = `
                            <div class="mobile-row-header p-4 flex justify-between items-center cursor-pointer">
                                <div>
                                    <div class="font-medium text-secondary">${q.contact_person}</div>
                                    <div class="text-sm text-gray-400">${companyDisplay}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${q.status_class}">
                                        ${q.status.charAt(0).toUpperCase() + q.status.slice(1)}
                                    </span>
                                    <svg class="accordion-arrow w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="accordion-content hidden">
                                <div class="px-4 pb-4 space-y-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <div class="text-xs text-gray-500">Phone</div>
                                            <div class="text-sm font-medium">${q.phone}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Items</div>
                                            <div class="text-sm font-medium">${q.items_count} items</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Location</div>
                                            <div class="text-sm font-medium">${q.site_location}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500">Submitted</div>
                                            <div class="text-sm font-medium">${formatDate(q.created_at)}</div>
                                        </div>
                                    </div>
                                    <button 
                                        onclick="showQuoteDetails('${q.id}')" 
                                        class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <i class="fas fa-eye"></i>
                                        <span>View Details</span>
                                    </button>
                                </div>
                            </div>
                        `;

                        mobileContainer.appendChild(mobileRow);
                    });

                    // Initialize accordion functionality for mobile
                    initAccordion();

                    // Initialize tooltips
                    if (typeof tippy !== 'undefined') {
                        tippy('[data-tippy-content]', {
                            placement: 'top',
                            arrow: true,
                            theme: 'light',
                        });
                    }
                } else {
                    notifications.error('Failed to load quotations. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error fetching quotations:', error);
                notifications.error('An error occurred while fetching data.');
            })
            .finally(() => {
                filterBtn.disabled = false;
                filterBtn.innerHTML = originalText;
                hideLoading();
            });
    }

    function initAccordion() {
        document.querySelectorAll('.mobile-row-header').forEach(header => {
            header.addEventListener('click', function(e) {
                e.stopPropagation();

                const content = this.nextElementSibling;
                const arrow = this.querySelector('.accordion-arrow');

                // Close all other accordions
                document.querySelectorAll('.accordion-content').forEach(item => {
                    if (item !== content && !item.classList.contains('hidden')) {
                        item.classList.add('hidden');
                        const otherArrow = item.previousElementSibling.querySelector('.accordion-arrow');
                        if (otherArrow) otherArrow.classList.remove('active');
                    }
                });

                // Toggle current accordion
                content.classList.toggle('hidden');
                arrow.classList.toggle('active');
            });
        });
    }

    function filterQuotations() {
        getQuotations();
    }

    function showQuoteDetails(id) {
        showLoading();

        fetch(`${API_BASE}/getRFQDetails?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const offcanvas = document.getElementById('quoteDetailsOffcanvas');
                    const content = document.getElementById('quoteDetailsContent');

                    document.getElementById('offcanvas-title').innerText = data.quotation.company_name || 'Quotation Details';

                    const st = data.quotation.status.toLowerCase();
                    let primaryAction = '';
                    let secondaryAction = '';

                    if (st === "new") {
                        primaryAction = `<button onclick="showConfirmModal('process','${id}')" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-cog"></i>
                            <span>Process Quote</span>
                        </button>`;
                        secondaryAction = `<button onclick="showConfirmModal('cancel','${id}')" class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i>
                            <span>Cancel Quote</span>
                        </button>`;
                    } else if (st === "processing") {
                        primaryAction = `<button onclick="showConfirmModal('complete','${id}')" class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-check"></i>
                            <span>Mark Processed</span>
                        </button>`;
                        secondaryAction = `<button onclick="showConfirmModal('cancel','${id}')" class="flex-1 border border-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i>
                            <span>Cancel Quote</span>
                        </button>`;
                    } else {
                        primaryAction = `<button disabled class="flex-1 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-ban"></i>
                            <span>No Actions</span>
                        </button>`;
                        secondaryAction = `<button disabled class="flex-1 border border-gray-200 text-gray-400 px-4 py-2 rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="fas fa-ban"></i>
                            <span>No Actions</span>
                        </button>`;
                    }

                    content.innerHTML = `
                    <div class="space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xl font-semibold text-secondary">${data.quotation.company_name || '<i class="text-gray-400">No Company</i>'}</h4>
                                <p class="text-sm text-gray-text mt-1">Submitted on ${formatDate(data.quotation.created_at)}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${data.quotation.status_class}">
                                ${data.quotation.status.charAt(0).toUpperCase() + data.quotation.status.slice(1)}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-sm text-gray-text">Contact Person</label>
                                <div class="font-medium text-secondary">${data.quotation.contact_person}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-text">Phone/WhatsApp</label>
                                <div class="font-medium text-secondary">${data.quotation.phone}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-text">Email</label>
                                <div class="font-medium text-secondary">${data.quotation.email}</div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm text-gray-text">Site Location</label>
                                <div class="font-medium text-secondary">${data.quotation.site_location}</div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-6">
                            <h4 class="font-medium text-secondary mb-4">Requested Items</h4>
                            <div class="space-y-4">
                                ${data.items.map(item => `
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <div class="text-sm text-gray-text">Brand</div>
                                            <div class="font-medium text-secondary">${item.brand_name}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-text">Size</div>
                                            <div class="font-medium text-secondary">${item.size}</div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-text">Quantity</div>
                                            <div class="font-medium text-secondary">${item.quantity}</div>
                                        </div>
                                    </div>
                                </div>
                                `).join('')}
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-100 pt-6">
                            <h4 class="font-medium text-secondary mb-4">Actions</h4>
                            <div class="flex flex-col md:flex-row gap-4">
                                ${primaryAction}
                                ${secondaryAction}
                            </div>
                        </div>
                    </div>
                    `;

                    offcanvas.classList.remove('hidden');
                    setTimeout(() => {
                        offcanvas.querySelector('.transform').classList.remove('translate-x-full');
                    }, 10);
                } else {
                    notifications.error('Failed to load quotation details.');
                }
            })
            .catch(error => {
                console.error('Error fetching quotation details:', error);
                notifications.error('An error occurred while fetching details.');
            })
            .finally(() => {
                hideLoading();
            });
    }

    function hideQuoteDetails() {
        const offcanvas = document.getElementById('quoteDetailsOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(() => {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    let pendingAction = null;
    let pendingId = null;

    function showConfirmModal(action, id) {
        pendingAction = action;
        pendingId = id;
        const modal = document.getElementById('confirmModal');
        const box = document.getElementById('confirmModalBox');
        const title = document.getElementById('confirmModalTitle');
        const message = document.getElementById('confirmModalMessage');
        const confirmBtn = document.getElementById('confirmModalYesBtn');

        if (action === 'process') {
            title.innerText = "Process Quotation";
            message.innerText = "Are you sure you want to process this quotation?";
            confirmBtn.className = "px-4 py-2 rounded-lg bg-primary text-white hover:bg-primary/90";
        } else if (action === 'complete') {
            title.innerText = "Mark Quotation Processed";
            message.innerText = "Are you sure you want to mark this quotation as processed?";
            confirmBtn.className = "px-4 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600";
        } else if (action === 'cancel') {
            title.innerText = "Cancel Quotation";
            message.innerText = "Are you sure you want to cancel this quotation?";
            confirmBtn.className = "px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600";
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            box.classList.remove('opacity-0', 'scale-95');
        }, 10);
    }

    function closeConfirmModal() {
        const modal = document.getElementById('confirmModal');
        const box = document.getElementById('confirmModalBox');
        modal.classList.remove('opacity-100');
        box.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
        pendingAction = null;
        pendingId = null;
    }

    function updateQuotationStatus(action, id) {
        showLoading();

        fetch(`${API_BASE}/${action}RFQ?id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                closeConfirmModal();
                if (data.success) {
                    notifications.success('Quotation updated successfully.', 'Action Completed');
                    hideQuoteDetails();
                    getQuotations();
                } else {
                    notifications.error('Action failed. Please try again.', 'Error');
                }
            })
            .catch(error => {
                console.error('Error updating quotation:', error);
                notifications.error('An error occurred while updating the quotation.');
            })
            .finally(() => {
                hideLoading();
            });
    }

    function initServerTime() {
        showLoading();

        fetch(`${API_BASE}/getServerTime`)
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    notifications.error('Failed to get server time.');
                    return;
                }

                const nowStr = d.now;
                const parts = nowStr.split(' ');
                const datePart = parts[0].split('-');
                const timePart = parts[1].split(':');
                const currentYear = parseInt(datePart[0], 10);
                const currentMonth = parseInt(datePart[1], 10) - 1;
                const currentDay = parseInt(datePart[2], 10);
                const currentHour = parseInt(timePart[0], 10);
                const currentMin = parseInt(timePart[1], 10);
                const startOfMonth = new Date(currentYear, currentMonth, 1, 0, 0, 0);
                const endNow = new Date(currentYear, currentMonth, currentDay, currentHour, currentMin, 0);
                setDateTimeInputs(formatToDateTimeLocal(startOfMonth), formatToDateTimeLocal(endNow));
                getQuotations();
            })
            .catch(error => {
                console.error('Error fetching server time:', error);
                notifications.error('An error occurred while fetching server time.');
            })
            .finally(() => {
                hideLoading();
            });
    }

    function formatToDateTimeLocal(jsDate) {
        const yyyy = jsDate.getFullYear();
        const mm = String(jsDate.getMonth() + 1).padStart(2, '0');
        const dd = String(jsDate.getDate()).padStart(2, '0');
        const hh = String(jsDate.getHours()).padStart(2, '0');
        const min = String(jsDate.getMinutes()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
    }

    // Event Listeners
    document.getElementById('filter-btn').addEventListener('click', filterQuotations);
    document.getElementById('search-term').addEventListener('keyup', function(e) {
        if (e.key === "Enter") filterQuotations();
    });
    document.getElementById('status-filter').addEventListener('change', filterQuotations);
    document.getElementById('confirmModalYesBtn').addEventListener('click', function() {
        if (!pendingAction || !pendingId) {
            closeConfirmModal();
            return;
        }
        updateQuotationStatus(pendingAction, pendingId);
    });
    document.getElementById('reset-filters').addEventListener('click', function() {
        document.getElementById('search-term').value = '';
        document.getElementById('status-filter').value = 'all';
        initServerTime();
    });
    document.getElementById('refresh-btn').addEventListener('click', function() {
        getQuotations();
        notifications.success('Data refreshed successfully.');
    });
    document.getElementById('export-btn').addEventListener('click', function() {
        notifications.success('Export functionality will be implemented soon.');
    });

    // Initialize
    window.addEventListener('load', function() {
        initServerTime();
    });

    // Accordion functionality for mobile
    function initAccordion() {
        document.querySelectorAll('.mobile-row-header').forEach(header => {
            header.addEventListener('click', function(e) {
                e.stopPropagation();

                const content = this.nextElementSibling;
                const arrow = this.querySelector('.accordion-arrow');

                // Close all other accordions
                document.querySelectorAll('.accordion-content').forEach(item => {
                    if (item !== content && !item.classList.contains('hidden')) {
                        item.classList.add('hidden');
                        const otherArrow = item.previousElementSibling.querySelector('.accordion-arrow');
                        if (otherArrow) otherArrow.classList.remove('active');
                    }
                });

                // Toggle current accordion
                content.classList.toggle('hidden');
                arrow.classList.toggle('active');
            });
        });
    }
</script>

<style>
    /* Accordion styling */
    .accordion-content {
        display: none;
    }

    .accordion-content.hidden {
        display: none;
    }

    .accordion-arrow {
        transition: transform 0.2s ease;
    }

    .accordion-arrow.active {
        transform: rotate(180deg);
    }

    /* Status badge colors */
    .bg-new {
        background-color: #EFF6FF;
        color: #1E40AF;
    }

    .bg-processing {
        background-color: #FEF3C7;
        color: #92400E;
    }

    .bg-processed {
        background-color: #D1FAE5;
        color: #065F46;
    }

    .bg-cancelled {
        background-color: #FEE2E2;
        color: #B91C1C;
    }

    /* Animation for modals */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes scaleIn {
        from {
            transform: scale(0.95);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        #filter-form {
            flex-direction: column;
            width: 100%;
        }

        #filter-form>div {
            width: 100%;
        }

        #filter-btn {
            width: 100%;
        }

        .stats-card {
            margin-bottom: 1rem;
        }
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>