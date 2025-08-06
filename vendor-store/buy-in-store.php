<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Store Visit Requests';
$activeNav = 'buy-in-store';

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
<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <h1 class="text-lg sm:text-2xl font-bold text-gray-900">Store Visit Requests</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor and manage customer store
                        visit requests</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Pending</p>
                        <p class="text-xl font-bold text-yellow-900 truncate" id="pendingRequests">0</p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Confirmed</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="confirmedRequests">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Completed</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="completedRequests">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-double text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Cancelled</p>
                        <p class="text-xl font-bold text-red-900 truncate" id="cancelledRequests">0</p>
                    </div>
                    <div class="w-10 h-10 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Store Visit Requests</h3>
                        <p class="text-sm text-gray-600">Click on any request to view and manage details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search requests..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button id="clearFilters"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="requestsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Customer Details</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Product</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Visit Date</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Quantity</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Total Value</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody id="requestsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading requests...</div>
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

            <div class="lg:hidden" id="requestsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading requests...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingCount">0</span> of <span id="mobileTotalCount">0</span> requests
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Desktop Request Modal -->
<div id="requestModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeRequestModal()"></div>
    <div
        class="relative w-full h-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Store Visit Request</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Manage customer visit request</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div id="modalStatusIndicator"
                    class="flex items-center gap-2 px-3 py-1 bg-green-100 border border-green-200 rounded-full">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-green-700">Active</span>
                </div>
                <button onclick="closeRequestModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="flex h-[calc(95vh-120px)]">
            <div class="w-80 border-r border-gray-200 bg-gray-50 overflow-y-auto">
                <div class="p-4" id="requestInfo">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500 text-sm">Loading...</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto p-6" id="requestDetails">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading request details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Request Modal -->
<div id="mobileRequestModal" class="fixed inset-0 z-50 hidden lg:hidden">
    <div class="absolute inset-0 bg-white">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="closeMobileRequestModal()" class="text-white hover:text-blue-100">
                    <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <div>
                    <h3 class="font-bold text-lg" id="mobileModalTitle">Visit Request</h3>
                    <p class="text-blue-100 text-sm" id="mobileModalSubtitle">Customer details</p>
                </div>
            </div>
            <div id="mobileModalStatusIndicator" class="flex items-center gap-2 px-2 py-1 bg-green-500 rounded-full">
                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                <span class="text-xs font-medium">Active</span>
            </div>
        </div>

        <div class="flex flex-col h-[calc(100vh-80px)] overflow-y-auto" id="mobileRequestDetails">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading request details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Date Picker Modal -->
<div id="datePickerModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDatePickerModal()"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Select Visit Date</h3>
                <button onclick="closeDatePickerModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Visit Date:</label>
                <input type="date" id="newVisitDate"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                <p class="text-xs text-gray-500 mt-1">Please select a future date for the visit</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDatePickerModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button" onclick="confirmDateChange()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-calendar-check mr-2"></i>Update Date
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SMS Modal -->
<div id="smsModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeSmsModal()"></div>
    <div class="relative w-full max-w-lg mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Send SMS</h3>
                <button onclick="closeSmsModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <form id="smsForm" class="p-6 space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-900">SMS Credits</p>
                        <p class="text-xs text-blue-700">Available balance</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-blue-900" id="smsBalance">0</p>
                        <p class="text-xs text-blue-700">credits</p>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                <input type="tel" id="smsTo" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                <textarea id="smsMessage" rows="4" maxlength="160"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    placeholder="Type your SMS message here..."></textarea>
                <div class="text-right text-sm text-gray-500 mt-1">
                    <span id="smsCharCount">0</span>/160 characters
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeSmsModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" id="sendSmsBtn"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-sms mr-2"></i>Send SMS
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Email Modal -->
<div id="emailModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeEmailModal()"></div>
    <div class="relative w-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Send Email</h3>
                <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <form id="emailForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">To:</label>
                <input type="email" id="emailTo" readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                <input type="text" id="emailSubject"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message:</label>
                <textarea id="emailMessage" rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                    placeholder="Type your message here..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEmailModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeSuccessModal()"></div>
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

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeErrorModal()"></div>
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

<script>
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'daily';
    let currentRequestId = null;
    let currentRequestData = null;
    let smsBalance = 0;

    document.addEventListener('DOMContentLoaded', function () {
        initializeDateFilters();
        setupEventListeners();
        loadRequests();
        loadStats();
        setInterval(() => {
            loadStats();
            if (!currentRequestId) {
                loadRequests();
            }
        }, 30000);
    });

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
                loadRequests();
            }
        });

        document.getElementById('searchFilter').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadRequests();
        }, 500));

        document.getElementById('statusFilter').addEventListener('change', () => {
            currentPage = 1;
            loadRequests();
        });

        document.getElementById('clearFilters').addEventListener('click', function () {
            document.getElementById('searchFilter').value = '';
            document.getElementById('statusFilter').value = 'all';
            currentPage = 1;
            loadRequests();
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadRequests();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadRequests();
        });

        document.getElementById('mobilePrevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadRequests();
            }
        });

        document.getElementById('mobileNextPage').addEventListener('click', function () {
            currentPage++;
            loadRequests();
        });

        document.getElementById('refreshBtn').addEventListener('click', refreshData);

        document.getElementById('emailForm').addEventListener('submit', function (e) {
            e.preventDefault();
            sendEmail();
        });

        document.getElementById('smsForm').addEventListener('submit', function (e) {
            e.preventDefault();
            sendSms();
        });

        document.getElementById('smsMessage').addEventListener('input', updateSmsCharCount);

        // Set minimum date for date picker to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('newVisitDate').setAttribute('min', today);
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
        currentPage = 1;
        loadRequests();
        loadStats();
    }

    function loadStats() {
        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getStats`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateStatistics(data.stats);
                }
            })
            .catch(error => console.error('Error loading stats:', error));
    }

    function updateStatistics(stats) {
        document.getElementById('pendingRequests').textContent = parseInt(stats.pending || 0).toLocaleString();
        document.getElementById('confirmedRequests').textContent = parseInt(stats.confirmed || 0).toLocaleString();
        document.getElementById('completedRequests').textContent = parseInt(stats.completed || 0).toLocaleString();
        document.getElementById('cancelledRequests').textContent = parseInt(stats.cancelled || 0).toLocaleString();
    }

    function loadRequests() {
        const params = new URLSearchParams({
            action: 'getRequests',
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            search_term: document.getElementById('searchFilter').value,
            status_filter: document.getElementById('statusFilter').value,
            page: currentPage
        });

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderRequestsTable(data.requestData.data);
                    renderRequestsCards(data.requestData.data);
                    updatePagination(data.requestData.total, data.requestData.page);
                } else {
                    showError('Failed to load requests');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred while loading requests');
            });
    }

    function renderRequestsTable(requests) {
        const tbody = document.getElementById('requestsBody');

        if (requests.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <div>No requests found</div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = requests.map(request => {
            const statusBadge = getStatusBadge(request.status);
            const visitDate = new Date(request.visit_date);
            const visitInfo = getVisitDateInfo(visitDate, request.status);

            return `
            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewRequestDetails('${request.id}')">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-medium text-gray-900 text-sm">${request.first_name} ${request.last_name}</div>
                    <div class="text-xs text-gray-500">${request.email}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900 line-clamp-1">${request.product_title}</div>
                    <div class="text-xs text-gray-500">${request.price_category.charAt(0).toUpperCase() + request.price_category.slice(1)} - UGX ${formatCurrency(request.price)}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="text-sm text-gray-900">${formatDate(request.visit_date)}</div>
                    <div class="text-xs ${visitInfo.color}">${visitInfo.text}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        ${request.quantity}
                    </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-sm font-bold text-green-600">UGX ${formatCurrency(request.total_value)}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    ${statusBadge}
                </td>
            </tr>
        `;
        }).join('');
    }

    function renderRequestsCards(requests) {
        const container = document.getElementById('requestsCards');

        if (requests.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <div>No requests found</div>
                </div>
            `;
            return;
        }

        container.innerHTML = requests.map(request => {
            const statusBadge = getStatusBadge(request.status);
            const visitDate = new Date(request.visit_date);
            const visitInfo = getVisitDateInfo(visitDate, request.status);

            return `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewRequestDetails('${request.id}')">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-medium text-gray-900 truncate">${request.first_name} ${request.last_name}</h4>
                            <span class="text-xs ${visitInfo.color}">${visitInfo.text}</span>
                        </div>
                        <div class="text-sm text-gray-600 mb-1 line-clamp-1">${request.product_title}</div>
                        <div class="text-xs text-gray-500 mb-2">UGX ${formatCurrency(request.price)} per unit</div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                ${statusBadge}
                                <span class="text-xs text-gray-500">Qty: ${request.quantity}</span>
                            </div>
                            <span class="text-sm font-bold text-green-600">UGX ${formatCurrency(request.total_value)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    }

    function getVisitDateInfo(visitDate, status) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        visitDate.setHours(0, 0, 0, 0);

        const diffTime = visitDate.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (status === 'completed') {
            return { text: 'Completed', color: 'text-green-600' };
        }

        if (diffDays > 0) {
            return { text: `In ${diffDays} day${diffDays > 1 ? 's' : ''}`, color: 'text-blue-600' };
        } else if (diffDays === 0) {
            return { text: 'Today', color: 'text-orange-600' };
        } else {
            return { text: `${Math.abs(diffDays)} day${Math.abs(diffDays) > 1 ? 's' : ''} overdue`, color: 'text-red-600' };
        }
    }

    function getStatusBadge(status) {
        const statusLower = status.toLowerCase();
        if (statusLower === 'pending') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>';
        } else if (statusLower === 'confirmed') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>';
        } else if (statusLower === 'cancelled') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>';
        } else if (statusLower === 'completed') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>';
        }
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function viewRequestDetails(requestId) {
        currentRequestId = requestId;

        if (window.innerWidth < 1024) {
            document.getElementById('mobileRequestModal').classList.remove('hidden');
            loadMobileRequestDetails(requestId);
        } else {
            document.getElementById('requestModal').classList.remove('hidden');
            loadRequestDetails(requestId);
        }

        document.body.style.overflow = 'hidden';
    }

    function loadRequestDetails(requestId) {
        document.getElementById('requestInfo').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-xl text-gray-400 mb-2"></i>
                <p class="text-gray-500 text-sm">Loading...</p>
            </div>
        `;

        document.getElementById('requestDetails').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading request details...</p>
            </div>
        `;

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getRequestDetails&id=${requestId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentRequestData = data.request;
                    renderRequestDetails(data.request);
                } else {
                    showErrorModal('Failed to load request details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while loading details');
            });
    }

    function loadMobileRequestDetails(requestId) {
        document.getElementById('mobileRequestDetails').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading request details...</p>
            </div>
        `;

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getRequestDetails&id=${requestId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentRequestData = data.request;
                    renderMobileRequestDetails(data.request);
                } else {
                    showErrorModal('Failed to load request details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while loading details');
            });
    }

    function renderRequestDetails(request) {
        const visitDate = new Date(request.visit_date);
        const visitInfo = getVisitDateInfo(visitDate, request.status);

        document.getElementById('modalTitle').textContent = `${request.first_name} ${request.last_name}`;
        document.getElementById('modalSubtitle').textContent = `Visit Request - ${request.product_title}`;

        const statusIndicator = document.getElementById('modalStatusIndicator');
        const statusColors = {
            'pending': { bg: 'bg-yellow-100 border-yellow-200', text: 'text-yellow-700', dot: 'bg-yellow-500' },
            'confirmed': { bg: 'bg-blue-100 border-blue-200', text: 'text-blue-700', dot: 'bg-blue-500' },
            'completed': { bg: 'bg-green-100 border-green-200', text: 'text-green-700', dot: 'bg-green-500' },
            'cancelled': { bg: 'bg-red-100 border-red-200', text: 'text-red-700', dot: 'bg-red-500' }
        };
        const statusColor = statusColors[request.status] || statusColors['pending'];

        statusIndicator.innerHTML = `
            <div class="w-2 h-2 ${statusColor.dot} rounded-full animate-pulse"></div>
            <span class="text-xs font-medium ${statusColor.text}">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
        `;
        statusIndicator.className = `flex items-center gap-2 px-3 py-1 ${statusColor.bg} border rounded-full`;

        document.getElementById('requestInfo').innerHTML = `
            <div class="bg-white rounded-lg p-4 border border-gray-200 mb-4">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Customer Details</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Name:</span>
                        <span class="font-medium">${request.first_name} ${request.last_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">${request.email}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Phone:</span>
                        <span class="font-medium">${request.phone}</span>
                    </div>
                    ${request.alt_contact ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Alt Contact:</span>
                            <span class="font-medium">${request.alt_contact}</span>
                        </div>
                    ` : ''}
                    ${request.alt_email ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Alt Email:</span>
                            <span class="font-medium">${request.alt_email}</span>
                        </div>
                    ` : ''}
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border border-gray-200 mb-4">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Visit Information</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Visit Date:</span>
                        <span class="font-medium">${formatDate(request.visit_date)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="${visitInfo.color} font-medium">${visitInfo.text}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm">Product Details</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Quantity:</span>
                        <span class="font-medium">${request.quantity}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Unit Price:</span>
                        <span class="font-medium">UGX ${formatCurrency(request.price)}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-900 font-medium">Total Value:</span>
                        <span class="font-bold text-green-600">UGX ${formatCurrency(request.total_value)}</span>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('requestDetails').innerHTML = `
            <div class="space-y-6">
                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-box mr-3 text-blue-600"></i>Product Information
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <h5 class="font-semibold text-gray-900 text-lg">${request.product_title}</h5>
                            ${request.product_description ? `<p class="text-gray-600 text-sm mt-1">${request.product_description}</p>` : ''}
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Package</label>
                                <p class="font-medium">${request.package_size} ${request.si_unit} ${request.package_name}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Category</label>
                                <p class="font-medium">${request.price_category.charAt(0).toUpperCase() + request.price_category.slice(1)}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar-check mr-3 text-green-600"></i>Visit Details
                    </h4>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Requested Date</label>
                                <div class="flex items-center gap-2">
                                    <p class="font-medium">${formatDate(request.visit_date)}</p>
                                    <button onclick="openDatePicker('${request.id}', '${request.visit_date}')" 
                                        class="text-blue-600 hover:text-blue-800 text-sm p-1 rounded hover:bg-blue-50">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                                <p class="text-sm ${visitInfo.color}">${visitInfo.text}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Request Made</label>
                                <p class="font-medium">${formatDate(request.created_at)}</p>
                                <p class="text-sm text-gray-500">${formatTime(request.created_at)}</p>
                            </div>
                        </div>
                        ${request.notes ? `
                            <div class="border-t pt-4">
                                <label class="text-sm font-medium text-gray-600">Customer Notes</label>
                                <p class="mt-1 text-gray-900 bg-gray-50 p-3 rounded border">${request.notes}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-phone mr-3 text-purple-600"></i>Communication
                        </h4>
                        <div class="grid grid-cols-1 gap-3">
                            <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition-colors cursor-pointer" onclick="callCustomer('${request.phone}')">
                                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-phone text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-green-900">Call Customer</p>
                                    <p class="text-sm text-green-700">${request.phone}</p>
                                </div>
                                <i class="fas fa-chevron-right text-green-600"></i>
                            </div>
                            
                            <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors cursor-pointer" onclick="openEmailModal('${request.email}','${request.first_name} ${request.last_name}','${request.product_title}')">
                                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-envelope text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-blue-900">Send Email</p>
                                    <p class="text-sm text-blue-700">${request.email}</p>
                                </div>
                                <i class="fas fa-chevron-right text-blue-600"></i>
                            </div>
                            
                            <div class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors cursor-pointer" onclick="openSmsModal('${request.phone}','${request.first_name}')">
                                <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-sms text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-purple-900">Send SMS</p>
                                    <p class="text-sm text-purple-700">Text message</p>
                                </div>
                                <i class="fas fa-chevron-right text-purple-600"></i>
                            </div>
                            
                            ${request.alt_contact ? `
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors cursor-pointer" onclick="callCustomer('${request.alt_contact}')">
                                    <div class="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-phone text-white text-sm"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Call Alt. Number</p>
                                        <p class="text-sm text-gray-700">${request.alt_contact}</p>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-600"></i>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-edit mr-3 text-orange-600"></i>Status Management
                        </h4>
                        <div class="grid grid-cols-1 gap-3">
                            ${getStatusCards(request.id, request.status)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderMobileRequestDetails(request) {
        const visitDate = new Date(request.visit_date);
        const visitInfo = getVisitDateInfo(visitDate, request.status);

        document.getElementById('mobileModalTitle').textContent = `${request.first_name} ${request.last_name}`;
        document.getElementById('mobileModalSubtitle').textContent = request.product_title;

        const mobileStatusIndicator = document.getElementById('mobileModalStatusIndicator');
        const statusColors = {
            'pending': 'bg-yellow-500',
            'confirmed': 'bg-blue-500',
            'completed': 'bg-green-500',
            'cancelled': 'bg-red-500'
        };
        const statusColor = statusColors[request.status] || statusColors['pending'];

        mobileStatusIndicator.innerHTML = `
            <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            <span class="text-xs font-medium">${request.status.charAt(0).toUpperCase() + request.status.slice(1)}</span>
        `;
        mobileStatusIndicator.className = `flex items-center gap-2 px-2 py-1 ${statusColor} rounded-full`;

        document.getElementById('mobileRequestDetails').innerHTML = `
            <div class="p-4 space-y-4">
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Customer Information</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email:</span>
                            <span class="font-medium">${request.email}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Phone:</span>
                            <span class="font-medium">${request.phone}</span>
                        </div>
                        ${request.alt_contact ? `
                            <div class="flex justify-between">
                                <span class="text-gray-600">Alt Contact:</span>
                                <span class="font-medium">${request.alt_contact}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Product Details</h4>
                    <div class="space-y-3">
                        <div>
                            <h5 class="font-medium text-gray-900">${request.product_title}</h5>
                            <p class="text-sm text-gray-600">${request.package_size} ${request.si_unit} ${request.package_name}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-medium ml-1">${request.quantity}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-600">Unit Price:</span>
                                <span class="font-medium ml-1">UGX ${formatCurrency(request.price)}</span>
                            </div>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-900">Total Value:</span>
                                <span class="font-bold text-green-600">UGX ${formatCurrency(request.total_value)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Visit Information</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Visit Date:</span>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">${formatDate(request.visit_date)}</span>
                                <button onclick="openDatePicker('${request.id}', '${request.visit_date}')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm p-1 rounded hover:bg-blue-50">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="${visitInfo.color} font-medium">${visitInfo.text}</span>
                        </div>
                    </div>
                    ${request.notes ? `
                        <div class="mt-4 pt-4 border-t">
                            <label class="text-sm font-medium text-gray-600">Customer Notes</label>
                            <p class="mt-1 text-gray-900 bg-gray-50 p-3 rounded text-sm">${request.notes}</p>
                        </div>
                    ` : ''}
                </div>

                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Quick Actions</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="callCustomer('${request.phone}')" 
                            class="flex items-center justify-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <i class="fas fa-phone mr-2"></i>Call
                        </button>
                        <button onclick="openSmsModal('${request.phone}','${request.first_name}')" 
                            class="flex items-center justify-center px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                            <i class="fas fa-sms mr-2"></i>SMS
                        </button>
                        <button onclick="openEmailModal('${request.email}','${request.first_name} ${request.last_name}','${request.product_title}')" 
                            class="col-span-2 flex items-center justify-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <i class="fas fa-envelope mr-2"></i>Send Email
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Update Status</h4>
                    <div class="grid grid-cols-2 gap-2">
                        ${getStatusCards(request.id, request.status, true)}
                    </div>
                </div>
            </div>
        `;
    }

    function getStatusCards(requestId, currentStatus, isMobile = false) {
        const statuses = [
            { key: 'pending', label: 'Pending', color: 'bg-yellow-50 border-yellow-200 hover:bg-yellow-100', textColor: 'text-yellow-900', iconColor: 'bg-yellow-500', icon: 'fa-clock' },
            { key: 'confirmed', label: 'Confirmed', color: 'bg-blue-50 border-blue-200 hover:bg-blue-100', textColor: 'text-blue-900', iconColor: 'bg-blue-500', icon: 'fa-check-circle' },
            { key: 'completed', label: 'Completed', color: 'bg-green-50 border-green-200 hover:bg-green-100', textColor: 'text-green-900', iconColor: 'bg-green-500', icon: 'fa-check-double' },
            { key: 'cancelled', label: 'Cancelled', color: 'bg-red-50 border-red-200 hover:bg-red-100', textColor: 'text-red-900', iconColor: 'bg-red-500', icon: 'fa-times-circle' }
        ];

        return statuses
            .filter(status => status.key !== currentStatus.toLowerCase())
            .map(status => {
                if (isMobile) {
                    return `
                        <button onclick="updateRequestStatus('${requestId}','${status.key}')" 
                            class="flex items-center justify-center px-3 py-2 ${status.color} border rounded-lg transition-colors text-sm ${status.textColor}">
                            <i class="fas ${status.icon} mr-2"></i>${status.label.substring(0, 4)}
                        </button>
                    `;
                } else {
                    return `
                        <div class="flex items-center p-3 ${status.color} border rounded-lg hover:shadow-sm transition-all cursor-pointer" onclick="updateRequestStatus('${requestId}','${status.key}')">
                            <div class="w-10 h-10 ${status.iconColor} rounded-lg flex items-center justify-center mr-3">
                                <i class="fas ${status.icon} text-white text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium ${status.textColor}">Mark as ${status.label}</p>
                                <p class="text-sm ${status.textColor} opacity-75">Update request status</p>
                            </div>
                            <i class="fas fa-chevron-right ${status.textColor}"></i>
                        </div>
                    `;
                }
            }).join('');
    }

    function openDatePicker(requestId, currentDate) {
        currentRequestId = requestId;
        document.getElementById('newVisitDate').value = currentDate;
        document.getElementById('datePickerModal').classList.remove('hidden');
    }

    function confirmDateChange() {
        const newDate = document.getElementById('newVisitDate').value;
        const today = new Date().toISOString().split('T')[0];

        if (!newDate) {
            showErrorModal('Please select a date');
            return;
        }

        if (newDate < today) {
            showErrorModal('Visit date cannot be in the past');
            return;
        }

        const formData = new FormData();
        formData.append('request_id', currentRequestId);
        formData.append('visit_date', newDate);

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=updateVisitDate`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDatePickerModal();
                    showSuccessModal('Visit date updated successfully');
                    setTimeout(() => {
                        closeSuccessModal();
                        if (window.innerWidth < 1024) {
                            loadMobileRequestDetails(currentRequestId);
                        } else {
                            loadRequestDetails(currentRequestId);
                        }
                        loadRequests();
                    }, 1500);
                } else {
                    showErrorModal(data.message || 'Failed to update visit date');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating visit date');
            });
    }

    function updateRequestStatus(requestId, newStatus) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('status', newStatus);

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=updateRequestStatus`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Status updated successfully');
                    setTimeout(() => {
                        closeSuccessModal();
                        if (window.innerWidth < 1024) {
                            loadMobileRequestDetails(requestId);
                        } else {
                            loadRequestDetails(requestId);
                        }
                        loadRequests();
                        loadStats();
                    }, 1500);
                } else {
                    showErrorModal(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while updating status');
            });
    }

    function callCustomer(phone) {
        window.location.href = `tel:${phone}`;
    }

    function openEmailModal(email, customerName, productTitle) {
        document.getElementById('emailTo').value = email;
        document.getElementById('emailSubject').value = `Store Visit Request - ${productTitle}`;
        document.getElementById('emailMessage').value = `Dear ${customerName},\n\nThank you for your store visit request regarding ${productTitle}.\n\nWe look forward to meeting with you.\n\nBest regards,\nStore Team`;
        document.getElementById('emailModal').classList.remove('hidden');
    }

    function openSmsModal(phone, customerName) {
        document.getElementById('smsTo').value = phone;
        document.getElementById('smsMessage').value = `Hello ${customerName}, regarding your store visit request. Please contact us for more details.`;
        updateSmsCharCount();

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getSmsBalance`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    smsBalance = data.balance;
                    document.getElementById('smsBalance').textContent = smsBalance;

                    if (smsBalance < 1) {
                        document.getElementById('sendSmsBtn').disabled = true;
                        document.getElementById('sendSmsBtn').innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Insufficient Credits';
                        document.getElementById('sendSmsBtn').className = 'px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed';
                    } else {
                        document.getElementById('sendSmsBtn').disabled = false;
                        document.getElementById('sendSmsBtn').innerHTML = '<i class="fas fa-sms mr-2"></i>Send SMS';
                        document.getElementById('sendSmsBtn').className = 'px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors';
                    }
                }
            })
            .catch(error => console.error('Error loading SMS balance:', error));

        document.getElementById('smsModal').classList.remove('hidden');
    }

    function updateSmsCharCount() {
        const message = document.getElementById('smsMessage').value;
        document.getElementById('smsCharCount').textContent = message.length;
    }

    function sendEmail() {
        const formData = new FormData();
        formData.append('request_id', currentRequestId);
        formData.append('subject', document.getElementById('emailSubject').value);
        formData.append('message', document.getElementById('emailMessage').value);

        fetch(`${BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=sendEmail`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal('Email sent successfully');
                    closeEmailModal();
                } else {
                    showErrorModal(data.message || 'Failed to send email');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('An error occurred while sending email');
            });
    }

    function sendSms() {
        if (smsBalance < 1) {
            showErrorModal('Insufficient SMS credits. Please purchase credits to send SMS.');
            return;
        }

        const message = document.getElementById('smsMessage').value.trim();
        const phone = document.getElementById('smsTo').value;
        const sendBtn = document.getElementById('sendSmsBtn');

        if (!message) {
            showErrorModal('Please enter an SMS message.');
            return;
        }

        if (!phone) {
            showErrorModal('Recipient phone number is missing.');
            return;
        }

        // Disable and show "Sending..."
        sendBtn.disabled = true;
        sendBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Sending...`;
        sendBtn.classList.add('cursor-not-allowed', 'opacity-75');

        const recipients = JSON.stringify([phone]);

        const formData = new FormData();
        formData.append('action', 'sendSms'); // For manageSmsCenter.php
        formData.append('message', message);
        formData.append('recipients', recipients);
        formData.append('send_type', 'single');
        formData.append('send_option', 'now');

        fetch(`${BASE_URL}vendor-store/fetch/manageSmsCenter.php`, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    smsBalance = data.data?.new_balance ?? (smsBalance - 1);
                    document.getElementById('smsBalance').textContent = smsBalance;
                    showSuccessModal('SMS sent successfully');
                    closeSmsModal();
                } else {
                    showErrorModal(data.message || 'Failed to send SMS');
                }
            })
            .catch(error => {
                console.error('Error sending SMS:', error);
                showErrorModal('An error occurred while sending SMS');
            })
            .finally(() => {
                // Restore button
                sendBtn.disabled = false;
                sendBtn.innerHTML = `<i class="fas fa-sms mr-2"></i>Send SMS`;
                sendBtn.classList.remove('cursor-not-allowed', 'opacity-75');
            });
    }

    function closeRequestModal() {
        document.getElementById('requestModal').classList.add('hidden');
        document.body.style.overflow = '';
        currentRequestId = null;
        currentRequestData = null;
    }

    function closeMobileRequestModal() {
        document.getElementById('mobileRequestModal').classList.add('hidden');
        document.body.style.overflow = '';
        currentRequestId = null;
        currentRequestData = null;
    }

    function closeDatePickerModal() {
        document.getElementById('datePickerModal').classList.add('hidden');
    }

    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
        document.getElementById('emailMessage').value = '';
    }

    function closeSmsModal() {
        document.getElementById('smsModal').classList.add('hidden');
        document.getElementById('smsMessage').value = '';
        document.getElementById('smsCharCount').textContent = '0';
    }

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

    function showError(message) {
        const tbody = document.getElementById('requestsBody');
        const cards = document.getElementById('requestsCards');

        const errorContent = `
            <div class="px-4 py-8 text-center text-red-500">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <div>${message}</div>
            </div>
        `;

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

    function refreshData() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadRequests();
        loadStats();

        setTimeout(() => {
            icon.classList.remove('fa-spin');
            refreshBtn.disabled = false;
        }, 1000);
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

    window.viewRequestDetails = viewRequestDetails;
    window.closeRequestModal = closeRequestModal;
    window.closeMobileRequestModal = closeMobileRequestModal;
    window.closeDatePickerModal = closeDatePickerModal;
    window.closeEmailModal = closeEmailModal;
    window.closeSmsModal = closeSmsModal;
    window.closeSuccessModal = closeSuccessModal;
    window.closeErrorModal = closeErrorModal;
    window.callCustomer = callCustomer;
    window.openEmailModal = openEmailModal;
    window.openSmsModal = openSmsModal;
    window.openDatePicker = openDatePicker;
    window.confirmDateChange = confirmDateChange;
    window.updateRequestStatus = updateRequestStatus;
</script>

<style>
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .5;
        }
    }

    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all 0.2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #D92B13;
        color: white;
        border-color: #D92B13;
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

    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #requestsTable {
        min-width: 700px;
    }

    @media (max-width: 768px) {
        #requestsTable {
            font-size: 0.875rem;
        }

        #requestsTable th,
        #requestsTable td {
            padding: 0.5rem 0.75rem;
        }
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>