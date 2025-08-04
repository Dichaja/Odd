<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Quotations Management';
$activeNav = 'quotations';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quotations Management</h1>
                    <p class="text-gray-600 mt-1">Monitor and manage customer quotation requests</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button id="exportBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-download"></i>
                        <span>Export Data</span>
                    </button>
                    <button id="refreshBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Time Period</h2>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="daily">
                            <i class="fas fa-calendar-day mr-2"></i>Daily
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="weekly">
                            <i class="fas fa-calendar-week mr-2"></i>Weekly
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="monthly">
                            <i class="fas fa-calendar-alt mr-2"></i>Monthly
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button id="applyCustomRange"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">New Requests</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-900 truncate" id="newRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-file-invoice text-blue-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-4 sm:p-6 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Processing</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-900 truncate" id="processingRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-clock text-yellow-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 sm:p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Processed</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-900 truncate" id="processedRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-check text-green-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 sm:p-6 border border-red-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Cancelled</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-900 truncate" id="cancelledRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-times text-red-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 sm:p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Paid</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-900 truncate" id="paidRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-credit-card text-purple-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Quotation Requests</h3>
                        <p class="text-sm text-gray-600">Click on any row to view and manage quotation details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search requests..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Status</option>
                            <option value="New">New</option>
                            <option value="Processing">Processing</option>
                            <option value="Processed">Processed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Paid">Paid</option>
                        </select>
                        <button id="clearFilters"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full" id="quotationsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                User Details
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Items
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Fee Charged
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Delivery Charge
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Total Amount
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Created
                            </th>
                        </tr>
                    </thead>
                    <tbody id="quotationsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading quotations...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> requests
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="quotationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div
        class="relative w-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">RFQ Report</h3>
                <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Manage pricing and quotation details</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="printQuotationBtn"
                    class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white/50" title="Print PDF">
                    <i class="fas fa-print text-lg"></i>
                </button>
                <button onclick="closeQuotationModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div id="quotationContent" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Fetching quotation details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="confirmationModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900" id="confirmationTitle">Confirm Action</h3>
                    <p class="text-sm text-gray-600 mt-1" id="confirmationMessage">Are you sure you want to proceed with
                        this action?</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button id="cancelConfirmationBtn"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<div id="successModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Success</h3>
                    <p class="text-sm text-gray-600" id="successMessage">Operation completed successfully.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeSuccessModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<div id="errorModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Error</h3>
                    <p class="text-sm text-gray-600" id="errorMessage">An error occurred.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeErrorModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<div id="pdfContent"
    style="position: absolute; left: -9999px; top: -9999px; width: 297mm; background: white; font-family: Arial, sans-serif; color: #333; padding: 10mm;">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'daily';
    let currentQuotationId = null;
    let currentQuotationData = null;
    let pendingAction = null;

    document.addEventListener('DOMContentLoaded', function () {
        initializeDateFilters();
        setupEventListeners();
        loadQuotations();
    });

    function showSuccessModal(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }

    function showErrorModal(message) {
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('errorModal').classList.remove('hidden');
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.add('hidden');
    }

    function initializeDateFilters() {
        setDateRangeForPeriod('daily');
    }

    function setDateRangeForPeriod(period) {
        const today = new Date();
        let startDate, endDate;

        switch (period) {
            case 'daily':
                startDate = new Date(today);
                endDate = new Date(today);
                break;
            case 'weekly':
                const dayOfWeek = today.getDay();
                startDate = new Date(today);
                startDate.setDate(today.getDate() - dayOfWeek);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;
            case 'monthly':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;
            default:
                startDate = new Date(today);
                endDate = new Date(today);
        }

        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

        currentPeriod = period;
        loadQuotations();
    }

    function loadQuotations() {
        const params = new URLSearchParams({
            action: 'getQuotations',
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            search_term: document.getElementById('searchFilter').value,
            status_filter: document.getElementById('statusFilter').value,
            page: currentPage,
            limit: itemsPerPage
        });

        fetch(`fetch/manageQuotations.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderQuotationsTable(data.quotationData.data, data.quotationData.total, data.quotationData.page);
                    updateStatistics(data.stats);
                } else {
                    console.error('Error loading quotations:', data.error);
                    showError('Failed to load quotations');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred while loading quotations');
            });
    }

    function showError(message) {
        const tbody = document.getElementById('quotationsBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <div>${message}</div>
                </td>
            </tr>
        `;
    }

    function updateStatistics(stats) {
        document.getElementById('newRequests').textContent = parseInt(stats.new || 0).toLocaleString();
        document.getElementById('processingRequests').textContent = parseInt(stats.processing || 0).toLocaleString();
        document.getElementById('processedRequests').textContent = parseInt(stats.processed || 0).toLocaleString();
        document.getElementById('cancelledRequests').textContent = parseInt(stats.cancelled || 0).toLocaleString();
        document.getElementById('paidRequests').textContent = parseInt(stats.paid || 0).toLocaleString();
    }

    function renderQuotationsTable(data, total, page) {
        const tbody = document.getElementById('quotationsBody');

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <div>No quotations found</div>
                    </td>
                </tr>
            `;
            updatePagination(0, 1);
            return;
        }

        tbody.innerHTML = data.map(item => {
            const statusBadge = getStatusBadge(item.status);
            const totalAmount = calculateTotalAmount(item);

            return `
            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewQuotationDetails('${item.RFQ_ID}')">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-medium text-gray-900 text-sm">${item.full_name || 'N/A'}</div>
                    <div class="text-xs text-gray-500">${item.user_email || 'N/A'}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${item.items_count}
                    </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900">UGX ${formatCurrency(item.fee_charged)}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900">UGX ${formatCurrency(item.transport)}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-sm font-bold text-green-600">UGX ${formatCurrency(totalAmount)}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    ${statusBadge}
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="text-xs text-gray-900">${formatDate(item.created_at)}</div>
                    <div class="text-xs text-gray-500">${formatTime(item.created_at)}</div>
                </td>
            </tr>
        `;
        }).join('');

        updatePagination(total, page);
    }

    function calculateTotalAmount(item) {
        const itemsTotal = parseFloat(item.items_total || 0);
        const transport = parseFloat(item.transport || 0);
        return itemsTotal + transport;
    }

    function getStatusBadge(status) {
        const statusLower = status.toLowerCase();
        if (statusLower === 'new') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">New</span>';
        } else if (statusLower === 'processing') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Processing</span>';
        } else if (statusLower === 'processed') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Processed</span>';
        } else if (statusLower === 'cancelled') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>';
        } else if (statusLower === 'paid') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Paid</span>';
        }
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount || 0);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const day = date.getDate();
        const suffix = day === 1 || day === 21 || day === 31 ? 'st' :
            day === 2 || day === 22 ? 'nd' :
                day === 3 || day === 23 ? 'rd' : 'th';
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        return `${month} ${day}${suffix}, ${year}`;
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        let hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const minutesStr = minutes < 10 ? '0' + minutes : minutes;
        return `${hours}:${minutesStr}${ampm}`;
    }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);

        document.getElementById('showingCount').textContent = total > 0 ? `${startIndex + 1}-${endIndex}` : '0';
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;
    }

    function viewQuotationDetails(id) {
        currentQuotationId = id;
        document.getElementById('quotationModal').classList.remove('hidden');

        document.getElementById('quotationContent').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                    <p class="text-gray-600">Fetching quotation details...</p>
                </div>
            </div>
        `;

        fetch(`fetch/manageQuotations.php?action=getRFQDetails&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentQuotationData = data;
                    showQuotationModal(data.quotation, data.items);
                } else {
                    document.getElementById('quotationContent').innerHTML = `
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center text-red-500">
                                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                                <p>Failed to load quotation details</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('quotationContent').innerHTML = `
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                            <p>An error occurred while loading details</p>
                        </div>
                    </div>
                `;
            });
    }

    function createGoogleMapsLink(coordinates) {
        if (!coordinates) return '#';
        const coords = coordinates.match(/-?\d+\.?\d*/g);
        if (coords && coords.length >= 2) {
            return `https://www.google.com/maps?q=${coords[0]},${coords[1]}`;
        }
        return '#';
    }

    function showQuotationModal(quotation, items) {
        const content = document.getElementById('quotationContent');
        const status = quotation.status.toLowerCase();
        const isModified = parseInt(quotation.modified) === 1;
        const canEdit = !['paid', 'cancelled'].includes(status);

        let itemsTotal = 0;
        let allItemsPriced = true;

        items.forEach(item => {
            if (item.unit_price && item.unit_price > 0) {
                itemsTotal += parseFloat(item.unit_price) * parseInt(item.quantity);
            } else {
                allItemsPriced = false;
            }
        });

        const transport = parseFloat(quotation.transport || 0);
        const feeCharged = parseFloat(quotation.fee_charged || 0);
        const grandTotal = itemsTotal + transport;

        const emailLink = quotation.user_email && quotation.user_email !== 'N/A' ?
            `<a href="mailto:${quotation.user_email}" class="text-blue-600 hover:text-blue-800 underline">${quotation.user_email}</a>` :
            (quotation.user_email || 'N/A');

        const phoneLink = quotation.phone && quotation.phone !== 'N/A' ?
            `<a href="tel:${quotation.phone}" class="text-blue-600 hover:text-blue-800 underline">${quotation.phone}</a>` :
            (quotation.phone || 'N/A');

        const coordinatesLink = quotation.coordinates ?
            `<a href="${createGoogleMapsLink(quotation.coordinates)}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${quotation.coordinates}</a>` :
            '';

        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Request Information</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Name:</span> <span class="font-medium">${quotation.full_name || 'N/A'}</span></div>
                                <div><span class="text-gray-600">Email:</span> <span class="font-medium">${emailLink}</span></div>
                                <div><span class="text-gray-600">Phone:</span> <span class="font-medium">${phoneLink}</span></div>
                                <div><span class="text-gray-600">Status:</span> ${getStatusBadge(quotation.status)}</div>
                                ${isModified ? '<div class="text-xs text-orange-600 font-medium">⚠ This quotation has been modified by user</div>' : ''}
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Location Details</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Site Location:</span></div>
                                <div class="font-medium">${quotation.site_location}</div>
                                ${coordinatesLink ? `<div class="text-gray-500">${coordinatesLink}</div>` : ''}
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Dates</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Created:</span> <span class="font-medium">${formatDate(quotation.created_at)} ${formatTime(quotation.created_at)}</span></div>
                                <div><span class="text-gray-600">Updated:</span> <span class="font-medium">${formatDate(quotation.updated_at)} ${formatTime(quotation.updated_at)}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Requested Items</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Brand/Material</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Size/Specification</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Quantity</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Unit Price (UGX)</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Total (UGX)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${items.map((item, index) => {
            const unitPrice = parseFloat(item.unit_price || 0);
            const quantity = parseInt(item.quantity);
            const total = unitPrice * quantity;

            return `
                                        <tr>
                                            <td class="px-4 py-3 text-sm">${item.brand_name}</td>
                                            <td class="px-4 py-3 text-sm">
                                                ${canEdit ? `
                                                    <input type="text" 
                                                           value="${item.size}" 
                                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                                                           data-item-id="${item.RFQD_ID}"
                                                           onchange="updateItemSize('${item.RFQD_ID}', this.value)">
                                                ` : `
                                                    <span class="text-sm">${item.size}</span>
                                                `}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center">${quantity}</td>
                                            <td class="px-4 py-3 text-center">
                                                ${canEdit ? `
                                                    <input type="number" 
                                                           value="${unitPrice}" 
                                                           min="0" 
                                                           step="0.01"
                                                           class="w-24 px-2 py-1 text-sm border border-gray-300 rounded text-center unit-price-input"
                                                           data-item-id="${item.RFQD_ID}"
                                                           onchange="updateItemPrice('${item.RFQD_ID}', this.value)">
                                                ` : `
                                                    <span class="text-sm font-medium">${formatCurrency(unitPrice)}</span>
                                                `}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-center font-medium">
                                                ${formatCurrency(total)}
                                            </td>
                                        </tr>
                                    `;
        }).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Delivery Charge</h4>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">Delivery Charge:</span>
                                ${canEdit ? `
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">UGX</span>
                                        <input type="number" 
                                               value="${transport}" 
                                               min="0" 
                                               step="0.01"
                                               class="w-32 px-3 py-2 text-sm border border-gray-300 rounded"
                                               id="transportCost"
                                               onchange="updateTransportCost(this.value)">
                                    </div>
                                ` : `
                                    <span class="text-sm font-medium">UGX ${formatCurrency(transport)}</span>
                                `}
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Items Subtotal:</span>
                                    <span class="font-medium">UGX ${formatCurrency(itemsTotal)}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Delivery Charge:</span>
                                    <span class="font-medium">UGX ${formatCurrency(transport)}</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 text-lg font-bold">
                                    <span>Total Amount:</span>
                                    <span class="text-green-600">UGX ${formatCurrency(grandTotal)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-3">
                        ${getStatusDropdown(quotation.status, quotation.RFQ_ID, canEdit)}
                    </div>
                    <button onclick="closeQuotationModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        `;
    }

    function getStatusDropdown(currentStatus, rfqId, canEdit) {
        if (!canEdit) {
            return `<span class="text-sm text-gray-500">Status cannot be changed for ${currentStatus.toLowerCase()} quotations</span>`;
        }

        const statuses = ['New', 'Processing', 'Processed'];

        return `
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Status:</label>
                <select id="statusSelect" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="updateQuotationStatus('${rfqId}', this.value)">
                    ${statuses.map(status => `
                        <option value="${status}" ${status === currentStatus ? 'selected' : ''}>${status}</option>
                    `).join('')}
                </select>
            </div>
        `;
    }

    function updateQuotationStatus(rfqId, newStatus) {
        fetch('fetch/manageQuotations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateQuotationStatus',
                rfq_id: rfqId,
                status: newStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Status updated successfully');
                    setTimeout(() => {
                        closeSuccessModal();
                        viewQuotationDetails(rfqId);
                        loadQuotations();
                    }, 1500);
                } else {
                    showErrorModal('Failed to update status: ' + (data.error || 'Unknown error'));
                    document.getElementById('statusSelect').value = currentQuotationData.quotation.status;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating status');
                document.getElementById('statusSelect').value = currentQuotationData.quotation.status;
            });
    }

    function showConfirmationModal(id, action) {
        const modal = document.getElementById('confirmationModal');
        const titleElement = document.getElementById('confirmationTitle');
        const messageElement = document.getElementById('confirmationMessage');
        const confirmButton = document.getElementById('confirmActionBtn');

        pendingAction = { id, action };

        if (action === 'process') {
            titleElement.textContent = 'Mark as Processed';
            messageElement.textContent = 'Are you sure you want to mark this quotation as processed? This will finalize the quotation.';
            confirmButton.textContent = 'Yes, Process';
            confirmButton.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700';
        }

        modal.classList.remove('hidden');
    }

    function closeConfirmationModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
        pendingAction = null;
    }

    function executeConfirmedAction() {
        if (!pendingAction) return;

        const { id, action } = pendingAction;
        closeConfirmationModal();

        if (action === 'process') {
            updateQuotationStatus(id, 'Processed');
        }
    }

    function updateItemPrice(itemId, price) {
        fetch('fetch/manageQuotations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateItemPrice',
                item_id: itemId,
                price: parseFloat(price) || 0
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status_changed) {
                        viewQuotationDetails(currentQuotationId);
                        loadQuotations();
                    } else {
                        viewQuotationDetails(currentQuotationId);
                    }
                } else {
                    showErrorModal('Failed to update price: ' + (data.error || 'Unknown error'));
                    document.querySelector(`input[data-item-id="${itemId}"]`).value = data.old_price || 0;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating price');
            });
    }

    function updateItemSize(itemId, size) {
        fetch('fetch/manageQuotations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateItemSize',
                item_id: itemId,
                size: size.trim()
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Size/specification updated successfully');
                    setTimeout(() => {
                        closeSuccessModal();
                    }, 1500);
                } else {
                    showErrorModal('Failed to update size: ' + (data.error || 'Unknown error'));
                    document.querySelector(`input[data-item-id="${itemId}"]`).value = data.old_size || '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating size');
            });
    }

    function updateTransportCost(cost) {
        fetch('fetch/manageQuotations.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'updateTransportCost',
                rfq_id: currentQuotationId,
                transport: parseFloat(cost) || 0
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.status_changed) {
                        viewQuotationDetails(currentQuotationId);
                        loadQuotations();
                    } else {
                        viewQuotationDetails(currentQuotationId);
                    }
                } else {
                    showErrorModal('Failed to update transport cost: ' + (data.error || 'Unknown error'));
                    document.getElementById('transportCost').value = data.old_transport || 0;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating transport cost');
            });
    }

    function closeQuotationModal() {
        document.getElementById('quotationModal').classList.add('hidden');
        currentQuotationId = null;
        currentQuotationData = null;
    }

    function generatePDF() {
        if (!currentQuotationData) return;

        const { quotation, items } = currentQuotationData;
        const pdfContainer = document.getElementById('pdfContent');

        let itemsTotal = 0;
        items.forEach(item => {
            if (item.unit_price && item.unit_price > 0) {
                itemsTotal += parseFloat(item.unit_price) * parseInt(item.quantity);
            }
        });

        const transport = parseFloat(quotation.transport || 0);
        const grandTotal = itemsTotal + transport;

        pdfContainer.innerHTML = `
    <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.4; font-size: 12px;">
        <div style="text-align: center; margin-bottom: 20px; page-break-inside: avoid;">
            <h1 style="font-size: 24px; margin: 0 0 10px 0; color: #2563eb;">RFQ Report</h1>
            <p style="font-size: 14px; color: #666; margin: 0;">Generated on ${formatDateReadable(new Date())} at ${formatTimeReadable(new Date())}</p>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; page-break-inside: avoid;">
            <div style="width: 30%; min-width: 200px;">
                <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Customer Information</h3>
                <div style="margin-bottom: 8px;"><strong>Name:</strong> ${quotation.full_name || 'N/A'}</div>
                <div style="margin-bottom: 8px;"><strong>Email:</strong> ${quotation.user_email || 'N/A'}</div>
                <div style="margin-bottom: 8px;"><strong>Phone:</strong> ${quotation.phone || 'N/A'}</div>
                <div style="margin-bottom: 8px;"><strong>Status:</strong> ${quotation.status}</div>
            </div>
            
            <div style="width: 35%; min-width: 250px;">
                <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Location Details</h3>
                <div style="margin-bottom: 8px;"><strong>Site Location:</strong></div>
                <div style="margin-bottom: 8px; word-wrap: break-word;">${quotation.site_location}</div>
                ${quotation.coordinates ? `<div style="margin-bottom: 8px; color: #666; font-size: 11px;">${quotation.coordinates}</div>` : ''}
            </div>
            
            <div style="width: 30%; min-width: 200px;">
                <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Dates</h3>
                <div style="margin-bottom: 8px;"><strong>Created:</strong> ${formatDateReadable(new Date(quotation.created_at))} ${formatTimeReadable(new Date(quotation.created_at))}</div>
                <div style="margin-bottom: 8px;"><strong>Updated:</strong> ${formatDateReadable(new Date(quotation.updated_at))} ${formatTimeReadable(new Date(quotation.updated_at))}</div>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Requested Items</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto;">
                <thead>
                    <tr style="background-color: #f3f4f6;">
                        <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Brand/Material</th>
                        <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Size/Specification</th>
                        <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Quantity</th>
                        <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Unit Price (UGX)</th>
                        <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Total (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map((item, index) => {
            const unitPrice = parseFloat(item.unit_price || 0);
            const quantity = parseInt(item.quantity);
            const total = unitPrice * quantity;

            return `
                            <tr style="page-break-inside: avoid;">
                                <td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${item.brand_name}</td>
                                <td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${item.size}</td>
                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db;">${quantity}</td>
                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 500;">${formatCurrency(unitPrice)}</td>
                                <td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600;">${formatCurrency(total)}</td>
                            </tr>
                        `;
        }).join('')}
                </tbody>
            </table>
        </div>
        
        <div style="display: flex; justify-content: flex-end; page-break-inside: avoid;">
            <div style="width: 300px; border: 2px solid #d1d5db; padding: 15px; background-color: #f9fafb;">
                <h3 style="font-size: 16px; margin: 0 0 15px 0; color: #374151;">Summary</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 6px;">
                    <span style="font-size: 14px;">Items Subtotal:</span>
                    <span style="font-size: 14px; font-weight: 600;">UGX ${formatCurrency(itemsTotal)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px;">
                    <span style="font-size: 14px;">Delivery Charge:</span>
                    <span style="font-size: 14px; font-weight: 600;">UGX ${formatCurrency(transport)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid #374151; margin-top: 12px;">
                    <span style="font-size: 16px; font-weight: 700;">Total Amount:</span>
                    <span style="font-size: 16px; font-weight: 700; color: #047857;">UGX ${formatCurrency(grandTotal)}</span>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; color: #666; font-size: 11px; page-break-inside: avoid;">
            <p style="margin: 0;">This is a computer-generated document. No signature is required.</p>
        </div>
    </div>
`;

        function formatDateReadable(date) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const day = date.getDate();
            const suffix = day === 1 || day === 21 || day === 31 ? 'st' :
                day === 2 || day === 22 ? 'nd' :
                    day === 3 || day === 23 ? 'rd' : 'th';
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${month} ${day}${suffix}, ${year}`;
        }

        function formatTimeReadable(date) {
            let hours = date.getHours();
            const minutes = date.getMinutes();
            const seconds = date.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const minutesStr = minutes < 10 ? '0' + minutes : minutes;
            const secondsStr = seconds < 10 ? '0' + seconds : seconds;
            return `${hours}:${minutesStr}:${secondsStr} ${ampm}`;
        }

        setTimeout(() => {
            const { jsPDF } = window.jspdf;

            html2canvas(pdfContainer, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                width: pdfContainer.scrollWidth,
                height: pdfContainer.scrollHeight,
                scrollX: 0,
                scrollY: 0
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: 'a4'
                });

                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = pdf.internal.pageSize.getHeight();
                const imgProps = pdf.getImageProperties(imgData);
                const imgHeight = (imgProps.height * pdfWidth) / imgProps.width;

                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, imgHeight);
                heightLeft -= pdfHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, imgHeight);
                    heightLeft -= pdfHeight;
                }

                const fileName = `Quotation_${quotation.full_name || 'Customer'}_${new Date().toISOString().slice(0, 10)}.pdf`;
                pdf.save(fileName);
            }).catch(error => {
                console.error('PDF generation error:', error);
                showErrorModal('Failed to generate PDF. Please try again.');
            });
        }, 500);
    }

    function setupEventListeners() {
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                this.classList.add('active', 'bg-primary', 'text-white', 'border-primary');
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');

                const period = this.dataset.period;
                setDateRangeForPeriod(period);
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
                loadQuotations();
            }
        });

        document.getElementById('searchFilter').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadQuotations();
        }, 500));

        document.getElementById('statusFilter').addEventListener('change', () => {
            currentPage = 1;
            loadQuotations();
        });

        document.getElementById('clearFilters').addEventListener('click', function () {
            document.getElementById('searchFilter').value = '';
            document.getElementById('statusFilter').value = 'all';
            currentPage = 1;
            loadQuotations();
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadQuotations();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadQuotations();
        });

        document.getElementById('exportBtn').addEventListener('click', exportData);
        document.getElementById('refreshBtn').addEventListener('click', refreshData);
        document.getElementById('printQuotationBtn').addEventListener('click', generatePDF);

        document.getElementById('cancelConfirmationBtn').addEventListener('click', closeConfirmationModal);
        document.getElementById('confirmActionBtn').addEventListener('click', executeConfirmedAction);

        document.getElementById('quotationModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeQuotationModal();
            }
        });

        document.getElementById('confirmationModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeConfirmationModal();
            }
        });

        document.getElementById('successModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeSuccessModal();
            }
        });

        document.getElementById('errorModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeErrorModal();
            }
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function exportData() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        fetch(`fetch/manageQuotations.php?action=exportQuotationData&start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const headers = ['User', 'Email', 'Phone', 'Location', 'Items Count', 'Fee Charged', 'Transport', 'Items Total', 'Grand Total', 'Status', 'Modified', 'Created At'];
                    const csvContent = [
                        headers.join(','),
                        ...data.data.map(item => [
                            `"${item.full_name || 'N/A'}"`,
                            `"${item.user_email || 'N/A'}"`,
                            `"${item.phone || 'N/A'}"`,
                            `"${item.site_location}"`,
                            item.items_count,
                            item.fee_charged,
                            item.transport,
                            item.items_total || 0,
                            (parseFloat(item.items_total || 0) + parseFloat(item.transport || 0)),
                            `"${item.status}"`,
                            item.modified ? 'Yes' : 'No',
                            `"${item.created_at}"`
                        ].join(','))
                    ].join('\n');

                    const blob = new Blob([csvContent], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `quotations-${new Date().toISOString().split('T')[0]}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            })
            .catch(error => console.error('Export error:', error));
    }

    function refreshData() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadQuotations();

        setTimeout(() => {
            icon.classList.remove('fa-spin');
            refreshBtn.disabled = false;
        }, 1000);
    }
</script>

<style>
    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all 0.2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    #quotationsTable tbody tr:hover {
        background-color: #f9fafb;
    }

    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    #quotationsTable {
        min-width: 800px;
    }

    .unit-price-input:focus,
    .item-size-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    @media (max-width: 768px) {
        #quotationsTable {
            font-size: 0.875rem;
        }

        #quotationsTable th,
        #quotationsTable td {
            padding: 0.5rem 0.75rem;
        }
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>