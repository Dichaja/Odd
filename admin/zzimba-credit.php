<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit Transactions';
$activeNav = 'zzimba-credit';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Zzimba Credit Transactions</h1>
            <p class="text-sm text-gray-text mt-1">View and manage all credit transactions</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportTransactions" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="filterTransactions" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Transactions Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">All Transactions</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="transaction-count">0</span> transactions found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchTransactions" placeholder="Search transactions..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="dateRangeSelect" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="7days">Last 7 Days</option>
                        <option value="30days" selected>Last 30 Days</option>
                        <option value="90days">Last 90 Days</option>
                        <option value="thisYear">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Custom Date Range (initially hidden) -->
        <div id="customDateRange" class="px-6 py-4 border-b border-gray-100 hidden">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="w-full md:w-auto">
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="startDate" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="w-full md:w-auto">
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="endDate" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="w-full md:w-auto self-end">
                    <button id="applyDateRange" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors w-full">
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Panel (initially hidden) -->
        <div id="filterPanel" class="px-6 py-4 border-b border-gray-100 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filterTransactionType" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                    <select id="filterTransactionType" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Types</option>
                        <option value="Deposit">Deposit</option>
                        <option value="Withdraw">Withdraw</option>
                        <option value="Credit Transfer">Credit Transfer</option>
                        <option value="Business / Investment">Business / Investment</option>
                        <option value="Educational / Seminars">Educational / Seminars</option>
                        <option value="Shopping Voucher">Shopping Voucher</option>
                        <option value="Transfer Charge">Transfer Charge</option>
                        <option value="Agent Commission">Agent Commission</option>
                        <option value="Subscription">Subscription</option>
                        <option value="Bank Fees">Bank Fees</option>
                        <option value="Mobile Fees">Mobile Fees</option>
                    </select>
                </div>
                <div>
                    <label for="filterUserSearch" class="block text-sm font-medium text-gray-700 mb-1">User Search</label>
                    <div class="relative">
                        <input type="text" id="filterUserSearch" class="w-full h-10 px-3 pl-10 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Search users...">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <div id="userSearchResults" class="absolute z-10 w-full bg-white shadow-lg rounded-lg border border-gray-200 max-h-60 overflow-y-auto hidden"></div>
                    </div>
                </div>
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="filterStatus" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                        <option value="Initiated">Initiated</option>
                    </select>
                </div>
                <div>
                    <label for="filterAmountMin" class="block text-sm font-medium text-gray-700 mb-1">Min Amount (UGX)</label>
                    <input type="number" id="filterAmountMin" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Min amount...">
                </div>
                <div>
                    <label for="filterAmountMax" class="block text-sm font-medium text-gray-700 mb-1">Max Amount (UGX)</label>
                    <input type="number" id="filterAmountMax" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Max amount...">
                </div>
                <div>
                    <label for="filterTransactionID" class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                    <input type="text" id="filterTransactionID" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Search by ID...">
                </div>
                <div class="md:col-span-3 flex justify-end mt-4">
                    <button id="resetFilters" class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                        Reset Filters
                    </button>
                    <button id="applyFilters" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="transactions-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date Approved</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">User</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Transaction</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Target Account</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Amount</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Balance</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text"></th>
                    </tr>
                </thead>
                <tbody id="transactions-table-body">
                    <!-- Table rows will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="responsive-table-mobile p-4" id="transactions-mobile">
            <!-- Mobile cards will be populated by JavaScript -->
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-transactions">100</span> transactions
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                    <span class="px-2">...</span>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">10</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Preview Modal -->
<div id="transactionPreviewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideTransactionPreview()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="preview-title">Transaction Details</h3>
            <button onclick="hideTransactionPreview()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6" id="preview-content">
            <!-- Content will be populated by JavaScript -->
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideTransactionPreview()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Close
            </button>
            <button id="printTransaction" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>
    </div>
</div>

<!-- Approve Transaction Modal -->
<div id="approveTransactionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideApproveTransaction()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Approve Transaction</h3>
            <button onclick="hideApproveTransaction()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to approve this transaction?</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Transaction ID:</div>
                    <div class="font-medium text-gray-900" id="approve-transaction-id"></div>
                    <div class="text-gray-500">User:</div>
                    <div class="font-medium text-gray-900" id="approve-user"></div>
                    <div class="text-gray-500">Type:</div>
                    <div class="font-medium text-gray-900" id="approve-type"></div>
                    <div class="text-gray-500">Amount:</div>
                    <div class="font-medium text-gray-900" id="approve-amount"></div>
                </div>
            </div>
            <div class="mb-4">
                <label for="approveNote" class="block text-sm font-medium text-gray-700 mb-1">Note (Optional)</label>
                <textarea id="approveNote" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideApproveTransaction()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmApprove" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Approve
            </button>
        </div>
    </div>
</div>

<style>
    .responsive-table-mobile {
        display: none;
    }

    @media (max-width: 768px) {
        .responsive-table-desktop {
            display: none;
        }

        .responsive-table-mobile {
            display: block;
        }

        .mobile-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .mobile-card-content {
            padding: 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .mobile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
    }

    .text-success {
        color: #16a34a;
    }

    .text-danger {
        color: #dc2626;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-initiated {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-completed {
        background-color: #dcfce7;
        color: #166534;
    }
</style>

<script>
    // Sample transaction data - in a real application, this would come from an API
    const transactions = [{
            id: "08471/080321",
            approveDate: "2025-03-08 08:33:47",
            user: "Lee",
            transaction: "Business / Investment",
            targetAccount: "Ange",
            targetDetails: "",
            date: "2025-03-08 08:33:47",
            amount: 5000,
            isCredit: false,
            balance: 458834,
            status: "completed",
            actions: []
        },
        {
            id: "08471/080321",
            approveDate: "2025-03-08 08:33:47",
            user: "Lee",
            transaction: "Transfer Charge",
            targetAccount: "OPERATIONS",
            targetDetails: "",
            date: "2025-03-08 08:33:47",
            amount: 50,
            isCredit: false,
            balance: 458834,
            status: "completed",
            actions: []
        },
        {
            id: "08122/08122",
            approveDate: "2025-03-08 08:31:00",
            user: "Lee",
            transaction: "Deposit",
            targetAccount: "Masika",
            targetDetails: "0392003406 Zzimba online Momo",
            date: "2025-03-08 08:31:00",
            amount: 15000,
            isCredit: true,
            balance: 458834,
            status: "completed",
            actions: ["preview"]
        },
        {
            id: "08318/080359",
            approveDate: "2025-03-08 07:54:23",
            user: "Ange",
            transaction: "Educational / Seminars",
            targetAccount: "Coxy",
            targetDetails: "",
            date: "2025-03-08 07:54:23",
            amount: 5000,
            isCredit: false,
            balance: 443834,
            status: "completed",
            actions: ["preview"]
        },
        {
            id: "08318/080359",
            approveDate: "2025-03-08 07:54:23",
            user: "Ange",
            transaction: "Transfer Charge",
            targetAccount: "OPERATIONS",
            targetDetails: "",
            date: "2025-03-08 07:54:23",
            amount: 50,
            isCredit: false,
            balance: 443834,
            status: "completed",
            actions: ["preview"]
        },
        {
            id: "07956/070366",
            approveDate: "2025-03-07 23:09:04",
            user: "OPERATIONS",
            transaction: "Credit Transfer",
            targetAccount: "Tedrick",
            targetDetails: "",
            date: "2025-03-07 23:09:04",
            amount: 10000,
            isCredit: false,
            balance: 443834,
            status: "completed",
            actions: ["preview"]
        },
        {
            id: "07650/070381",
            approveDate: "2025-03-07 09:34:08",
            user: "Ange",
            transaction: "Business / Investment",
            targetAccount: "Lee",
            targetDetails: "",
            date: "2025-03-07 09:34:08",
            amount: 5000,
            isCredit: false,
            balance: 443834,
            status: "completed",
            actions: ["preview"]
        },
        {
            id: "18930/-",
            approveDate: "",
            user: "Ange",
            transaction: "Withdraw",
            targetAccount: "null",
            targetDetails: "null null",
            date: "2025-02-18 15:57:00",
            amount: 5000,
            isCredit: false,
            balance: 443834,
            status: "initiated",
            actions: ["approve", "preview"]
        },
        {
            id: "18259/-",
            approveDate: "",
            user: "Ange",
            transaction: "Deposit",
            targetAccount: "null",
            targetDetails: "null null",
            date: "2025-02-18 15:57:00",
            amount: 50000,
            isCredit: true,
            balance: 443834,
            status: "pending",
            actions: ["approve", "preview"]
        },
        {
            id: "18171/-",
            approveDate: "",
            user: "Ange",
            transaction: "Withdraw",
            targetAccount: "null",
            targetDetails: "null null",
            date: "2025-02-18 15:53:00",
            amount: 5000,
            isCredit: false,
            balance: 443834,
            status: "initiated",
            actions: ["approve", "preview"]
        },
        {
            id: "14819/ID:31116296550",
            approveDate: "2025-02-14 19:03:00",
            user: "Ange",
            transaction: "Withdraw",
            targetAccount: "Masika",
            targetDetails: "0392003406 Zzimba online Momo",
            date: "2025-02-14 19:03:00",
            amount: 20000,
            isCredit: false,
            balance: 443834,
            status: "completed",
            actions: ["approve", "preview"]
        },
        {
            id: "14840/140210",
            approveDate: "2025-02-14 12:57:05",
            user: "Herbert",
            transaction: "Shopping Voucher",
            targetAccount: "Ange",
            targetDetails: "",
            date: "2025-02-14 12:57:05",
            amount: 20000,
            isCredit: false,
            balance: 463834,
            status: "completed",
            actions: ["approve", "preview"]
        },
        {
            id: "13906/130283",
            approveDate: "2025-02-13 22:34:49",
            user: "mozepro",
            transaction: "Shopping Voucher",
            targetAccount: "Herbert",
            targetDetails: "",
            date: "2025-02-13 22:34:49",
            amount: 100000,
            isCredit: false,
            balance: 463834,
            status: "completed",
            actions: ["approve", "preview"]
        },
        {
            id: "13993/-",
            approveDate: "",
            user: "mozepro",
            transaction: "Withdraw",
            targetAccount: "null",
            targetDetails: "null null",
            date: "2025-02-13 22:17:00",
            amount: 70000,
            isCredit: false,
            balance: 463834,
            status: "initiated",
            actions: ["approve", "preview"]
        },
        {
            id: "13881/130285",
            approveDate: "2025-02-13 11:35:26",
            user: "SERVICE",
            transaction: "Credit Transfer",
            targetAccount: "TEM",
            targetDetails: "",
            date: "2025-02-13 11:35:26",
            amount: 10000,
            isCredit: false,
            balance: 463834,
            status: "completed",
            actions: ["approve", "preview"]
        }
    ];

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Format date to a readable format
    function formatDate(dateString) {
        if (!dateString || dateString === '-' || dateString.includes('0000-00-00')) {
            return '-';
        }

        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Format date and time to a more readable format
    function formatDateTime(dateString) {
        if (!dateString || dateString === '-' || dateString.includes('0000-00-00')) {
            return '-';
        }

        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Intl.DateTimeFormat('en-US', options).format(date);
    }

    // Get status badge
    function getStatusBadge(status, transaction) {
        let statusText = status.charAt(0).toUpperCase() + status.slice(1);

        if (status === 'initiated' || status === 'pending') {
            const badgeClass = status === 'initiated' ? 'status-initiated' : 'status-pending';
            return `<span class="status-badge ${badgeClass}"><span class="w-1.5 h-1.5 rounded-full ${status === 'initiated' ? 'bg-red-600' : 'bg-yellow-600'} mr-1"></span>${transaction}<br><span style="font-size:11px;">(${statusText})</span></span>`;
        }

        return transaction;
    }

    // Format transaction ID to be more readable
    function formatTransactionID(id) {
        // Extract the numeric part from the existing ID format
        const parts = id.split('/');
        const mainId = parts[0];
        const subId = parts[1] || '';

        // Generate a formatted transaction ID
        // Format: TRX-YYMMDD-XXXXX where XXXXX is the numeric ID
        const date = new Date();
        const year = date.getFullYear().toString().substr(-2);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `TRX-${year}${month}${day}-${mainId}`;
    }

    // Render transactions table
    function renderTransactionsTable(transactionList) {
        const tableBody = document.getElementById('transactions-table-body');
        const mobileContainer = document.getElementById('transactions-mobile');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        transactionList.forEach((transaction, index) => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const amountClass = transaction.isCredit ? 'text-success' : transaction.amount > 0 ? 'text-danger' : '';
            const transactionStatus = getStatusBadge(transaction.status, transaction.transaction);

            tr.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${transaction.approveDate || '-'}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${formatTransactionID(transaction.id)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${transaction.user}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${transactionStatus}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${transaction.targetAccount}<br><span style="font-size:12px;">${transaction.targetDetails}</span></td>
                <td class="px-6 py-4 text-sm text-gray-text">${formatDateTime(transaction.date)}</td>
                <td class="px-6 py-4 text-sm font-medium ${amountClass}">UGX ${formatCurrency(transaction.amount)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">UGX ${formatCurrency(transaction.balance)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        ${transaction.actions.includes('preview') ? `<button onclick="showTransactionPreview(${index})" class="text-blue-600 hover:text-blue-800" title="Preview"><i class="fas fa-eye"></i></button>` : ''}
                        ${transaction.actions.includes('approve') ? `<button onclick="showApproveTransaction(${index})" class="text-green-600 hover:text-green-800" title="Approve"><i class="fas fa-check-circle"></i></button>` : ''}
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);

            // Mobile card
            const mobileCard = document.createElement('div');
            mobileCard.className = 'mobile-card';

            mobileCard.innerHTML = `
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">${formatTransactionID(transaction.id)}</div>
                        <div class="text-xs text-gray-500 mt-1">${formatDateTime(transaction.date)}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium ${amountClass}">UGX ${formatCurrency(transaction.amount)}</div>
                        <div class="text-xs text-gray-500 mt-1">Balance: UGX ${formatCurrency(transaction.balance)}</div>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">User</span>
                            <span class="mobile-value">${transaction.user}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Transaction</span>
                            <span class="mobile-value">${transaction.transaction}</span>
                            ${transaction.status !== 'completed' ? `<span class="text-xs text-red-600">(${transaction.status})</span>` : ''}
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Target Account</span>
                            <span class="mobile-value">${transaction.targetAccount}</span>
                            ${transaction.targetDetails ? `<span class="text-xs text-gray-500">${transaction.targetDetails}</span>` : ''}
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Date Approved</span>
                            <span class="mobile-value">${transaction.approveDate || '-'}</span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        ${transaction.actions.includes('preview') ? `<button onclick="showTransactionPreview(${index})" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100"><i class="fas fa-eye mr-1"></i> Preview</button>` : ''}
                        ${transaction.actions.includes('approve') ? `<button onclick="showApproveTransaction(${index})" class="px-3 py-1.5 text-xs bg-green-50 text-green-600 rounded-lg hover:bg-green-100"><i class="fas fa-check-circle mr-1"></i> Approve</button>` : ''}
                    </div>
                </div>
            `;

            mobileContainer.appendChild(mobileCard);
        });

        // Update pagination info
        document.getElementById('showing-start').textContent = '1';
        document.getElementById('showing-end').textContent = transactionList.length;
        document.getElementById('total-transactions').textContent = transactionList.length;
        document.getElementById('transaction-count').textContent = transactionList.length;

        // Add event listeners to action selects
        document.querySelectorAll('.action-select').forEach(select => {
            select.addEventListener('change', function() {
                const action = this.value;
                const index = parseInt(this.getAttribute('data-index'));

                if (action === 'preview') {
                    showTransactionPreview(index);
                } else if (action === 'approve') {
                    showApproveTransaction(index);
                }

                // Reset select after action
                setTimeout(() => {
                    this.value = '';
                }, 100);
            });
        });
    }

    // Show transaction preview
    function showTransactionPreview(index) {
        const transaction = transactions[index];
        if (!transaction) return;

        document.getElementById('preview-title').textContent = `Transaction: ${transaction.id}`;

        const amountClass = transaction.isCredit ? 'text-success' : transaction.amount > 0 ? 'text-danger' : '';
        const amountPrefix = transaction.isCredit ? '+' : '';

        const content = `
            <div class="space-y-6">
                <div class="flex flex-col items-center sm:flex-row sm:items-start gap-4">
                    <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-xl font-medium">
                        ${transaction.user.substring(0, 2).toUpperCase()}
                    </div>
                    <div class="text-center sm:text-left">
                        <h4 class="text-xl font-semibold text-gray-900">${transaction.transaction}</h4>
                        <p class="text-gray-500">${transaction.user}</p>
                        <div class="mt-2">
                            <span class="status-badge ${transaction.status === 'completed' ? 'status-completed' : transaction.status === 'pending' ? 'status-pending' : 'status-initiated'}">
                                <span class="w-1.5 h-1.5 rounded-full ${transaction.status === 'completed' ? 'bg-green-600' : transaction.status === 'pending' ? 'bg-yellow-600' : 'bg-red-600'} mr-1"></span>
                                ${transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1)}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">Transaction Details</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Transaction ID</p>
                            <p class="text-sm font-medium text-gray-900">${transaction.id}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="text-sm font-medium text-gray-900">${formatDateTime(transaction.date)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Amount</p>
                            <p class="text-sm font-medium ${amountClass}">${amountPrefix}${formatCurrency(transaction.amount)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Balance After Transaction</p>
                            <p class="text-sm font-medium text-gray-900">${formatCurrency(transaction.balance)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Target Account</p>
                            <p class="text-sm font-medium text-gray-900">${transaction.targetAccount}</p>
                            ${transaction.targetDetails ? `<p class="text-xs text-gray-500">${transaction.targetDetails}</p>` : ''}
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Approval Date</p>
                            <p class="text-sm font-medium text-gray-900">${transaction.approveDate || 'Not Approved'}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">Additional Information</h5>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-700">
                            ${transaction.status === 'completed' 
                                ? 'This transaction has been completed and approved.' 
                                : transaction.status === 'pending' 
                                    ? 'This transaction is pending approval.' 
                                    : 'This transaction has been initiated and is awaiting approval.'}
                        </p>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('preview-content').innerHTML = content;

        const modal = document.getElementById('transactionPreviewModal');
        modal.classList.remove('hidden');
    }

    // Hide transaction preview
    function hideTransactionPreview() {
        const modal = document.getElementById('transactionPreviewModal');
        modal.classList.add('hidden');
    }

    // Show approve transaction
    function showApproveTransaction(index) {
        const transaction = transactions[index];
        if (!transaction) return;

        document.getElementById('approve-transaction-id').textContent = transaction.id;
        document.getElementById('approve-user').textContent = transaction.user;
        document.getElementById('approve-type').textContent = transaction.transaction;
        document.getElementById('approve-amount').textContent = formatCurrency(transaction.amount);

        // Store transaction index for approval
        document.getElementById('confirmApprove').setAttribute('data-index', index);

        const modal = document.getElementById('approveTransactionModal');
        modal.classList.remove('hidden');
    }

    // Hide approve transaction
    function hideApproveTransaction() {
        const modal = document.getElementById('approveTransactionModal');
        modal.classList.add('hidden');
    }

    // User search functionality for large user databases
    function initUserSearch() {
        const userSearchInput = document.getElementById('filterUserSearch');
        const userSearchResults = document.getElementById('userSearchResults');

        // Sample user database - in a real application, this would be loaded dynamically or paginated
        const users = [
            'Ange', 'Lee', 'Tedrick', 'Taron', 'OPERATIONS', 'SERVICE', 'TEM',
            'mozepro', 'Herbert', 'Coxy', 'Mavick',
            // Add many more users to demonstrate large database handling
            'User001', 'User002', 'User003', 'User004', 'User005',
            'Admin001', 'Admin002', 'Admin003', 'Admin004', 'Admin005',
            'Agent001', 'Agent002', 'Agent003', 'Agent004', 'Agent005',
            'Customer001', 'Customer002', 'Customer003', 'Customer004', 'Customer005'
        ];

        // Show search results as user types
        userSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();

            if (searchTerm.length < 2) {
                userSearchResults.innerHTML = '';
                userSearchResults.classList.add('hidden');
                return;
            }

            // Filter users based on search term
            const filteredUsers = users.filter(user =>
                user.toLowerCase().includes(searchTerm)
            ).slice(0, 10); // Limit to 10 results

            if (filteredUsers.length === 0) {
                userSearchResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No users found</div>';
            } else {
                userSearchResults.innerHTML = filteredUsers.map(user =>
                    `<div class="p-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer user-result">${user}</div>`
                ).join('');

                // Add click event to user results
                document.querySelectorAll('.user-result').forEach(item => {
                    item.addEventListener('click', function() {
                        userSearchInput.value = this.textContent;
                        userSearchResults.classList.add('hidden');
                    });
                });
            }

            userSearchResults.classList.remove('hidden');
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target !== userSearchInput && !userSearchResults.contains(e.target)) {
                userSearchResults.classList.add('hidden');
            }
        });

        // Show results when input is focused
        userSearchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                userSearchResults.classList.remove('hidden');
            }
        });
    }

    // Filter transactions with enhanced options
    function filterTransactions(filters) {
        let filteredTransactions = [...transactions];

        // Basic search query
        if (filters.query) {
            const query = filters.query.toLowerCase();
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.id.toLowerCase().includes(query) ||
                transaction.user.toLowerCase().includes(query) ||
                transaction.transaction.toLowerCase().includes(query) ||
                transaction.targetAccount.toLowerCase().includes(query)
            );
        }

        // Transaction type filter
        if (filters.transactionType) {
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.transaction.includes(filters.transactionType)
            );
        }

        // User filter
        if (filters.user) {
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.user.toLowerCase().includes(filters.user.toLowerCase())
            );
        }

        // Status filter
        if (filters.status) {
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.status === filters.status.toLowerCase()
            );
        }

        // Transaction ID filter
        if (filters.transactionId) {
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.id.includes(filters.transactionId)
            );
        }

        // Amount range filter
        if (filters.amountMin) {
            const minAmount = parseFloat(filters.amountMin);
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.amount >= minAmount
            );
        }

        if (filters.amountMax) {
            const maxAmount = parseFloat(filters.amountMax);
            filteredTransactions = filteredTransactions.filter(transaction =>
                transaction.amount <= maxAmount
            );
        }

        // Date range filter
        if (filters.dateRange) {
            const now = new Date();
            let startDate;

            switch (filters.dateRange) {
                case '7days':
                    startDate = new Date(now.setDate(now.getDate() - 7));
                    break;
                case '30days':
                    startDate = new Date(now.setDate(now.getDate() - 30));
                    break;
                case '90days':
                    startDate = new Date(now.setDate(now.getDate() - 90));
                    break;
                case 'thisYear':
                    startDate = new Date(now.getFullYear(), 0, 1);
                    break;
                case 'custom':
                    if (filters.startDate && filters.endDate) {
                        startDate = new Date(filters.startDate);
                        const endDate = new Date(filters.endDate);
                        endDate.setHours(23, 59, 59, 999); // End of day

                        filteredTransactions = filteredTransactions.filter(transaction => {
                            const transDate = new Date(transaction.date);
                            return transDate >= startDate && transDate <= endDate;
                        });

                        return filteredTransactions;
                    }
                    break;
            }

            if (startDate) {
                filteredTransactions = filteredTransactions.filter(transaction => {
                    const transDate = new Date(transaction.date);
                    return transDate >= startDate;
                });
            }
        }

        return filteredTransactions;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize user search functionality
        initUserSearch();

        // Reset filters button
        document.getElementById('resetFilters').addEventListener('click', function() {
            // Reset all form inputs
            document.getElementById('filterTransactionType').value = '';
            document.getElementById('filterUserSearch').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterAmountMin').value = '';
            document.getElementById('filterAmountMax').value = '';
            document.getElementById('filterTransactionID').value = '';
            document.getElementById('dateRangeSelect').value = '30days';
            document.getElementById('customDateRange').classList.add('hidden');

            // Reset and apply filters
            const filters = {
                dateRange: '30days'
            };
            const filteredTransactions = filterTransactions(filters);
            renderTransactionsTable(filteredTransactions);
        });

        // Initial render
        renderTransactionsTable(transactions);

        // Search functionality
        document.getElementById('searchTransactions').addEventListener('input', function() {
            const query = this.value.trim();
            const filters = {
                query: query,
                dateRange: document.getElementById('dateRangeSelect').value,
                transactionType: document.getElementById('filterTransactionType').value,
                user: document.getElementById('filterUserSearch').value,
                status: document.getElementById('filterStatus').value,
                transactionId: document.getElementById('filterTransactionID').value,
                amountMin: document.getElementById('filterAmountMin').value,
                amountMax: document.getElementById('filterAmountMax').value
            };

            const filteredTransactions = filterTransactions(filters);
            renderTransactionsTable(filteredTransactions);
        });

        // Date range select
        document.getElementById('dateRangeSelect').addEventListener('change', function() {
            const value = this.value;
            const customDateRange = document.getElementById('customDateRange');

            if (value === 'custom') {
                customDateRange.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');

                const filters = {
                    query: document.getElementById('searchTransactions').value.trim(),
                    dateRange: value,
                    transactionType: document.getElementById('filterTransactionType').value,
                    user: document.getElementById('filterUserSearch').value,
                    status: document.getElementById('filterStatus').value,
                    transactionId: document.getElementById('filterTransactionID').value,
                    amountMin: document.getElementById('filterAmountMin').value,
                    amountMax: document.getElementById('filterAmountMax').value
                };

                const filteredTransactions = filterTransactions(filters);
                renderTransactionsTable(filteredTransactions);
            }
        });

        // Apply custom date range
        document.getElementById('applyDateRange').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                const filters = {
                    query: document.getElementById('searchTransactions').value.trim(),
                    dateRange: 'custom',
                    startDate: startDate,
                    endDate: endDate,
                    transactionType: document.getElementById('filterTransactionType').value,
                    user: document.getElementById('filterUserSearch').value,
                    status: document.getElementById('filterStatus').value,
                    transactionId: document.getElementById('filterTransactionID').value,
                    amountMin: document.getElementById('filterAmountMin').value,
                    amountMax: document.getElementById('filterAmountMax').value
                };

                const filteredTransactions = filterTransactions(filters);
                renderTransactionsTable(filteredTransactions);
            }
        });

        // Filter button
        document.getElementById('filterTransactions').addEventListener('click', function() {
            const filterPanel = document.getElementById('filterPanel');
            filterPanel.classList.toggle('hidden');
        });

        // Apply filters
        document.getElementById('applyFilters').addEventListener('click', function() {
            const filters = {
                query: document.getElementById('searchTransactions').value.trim(),
                dateRange: document.getElementById('dateRangeSelect').value,
                transactionType: document.getElementById('filterTransactionType').value,
                user: document.getElementById('filterUserSearch').value,
                status: document.getElementById('filterStatus').value,
                transactionId: document.getElementById('filterTransactionID').value,
                amountMin: document.getElementById('filterAmountMin').value,
                amountMax: document.getElementById('filterAmountMax').value
            };

            if (filters.dateRange === 'custom') {
                filters.startDate = document.getElementById('startDate').value;
                filters.endDate = document.getElementById('endDate').value;
            }

            const filteredTransactions = filterTransactions(filters);
            renderTransactionsTable(filteredTransactions);

            // Hide filter panel
            document.getElementById('filterPanel').classList.add('hidden');
        });

        // Confirm approve
        document.getElementById('confirmApprove').addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            const note = document.getElementById('approveNote').value;

            // In a real application, you would send an approval request to the server
            alert(`Transaction ${transactions[index].id} would be approved here with note: ${note || 'No note provided'}`);

            // Update transaction status (for demo purposes)
            transactions[index].status = 'completed';
            transactions[index].approveDate = new Date().toISOString().replace('T', ' ').substring(0, 19);

            // Hide modal
            hideApproveTransaction();

            // Re-render table
            const filters = {
                query: document.getElementById('searchTransactions').value.trim(),
                dateRange: document.getElementById('dateRangeSelect').value,
                transactionType: document.getElementById('filterTransactionType').value,
                user: document.getElementById('filterUserSearch').value,
                status: document.getElementById('filterStatus').value,
                transactionId: document.getElementById('filterTransactionID').value,
                amountMin: document.getElementById('filterAmountMin').value,
                amountMax: document.getElementById('filterAmountMax').value
            };

            const filteredTransactions = filterTransactions(filters);
            renderTransactionsTable(filteredTransactions);
        });

        // Print transaction
        document.getElementById('printTransaction').addEventListener('click', function() {
            const content = document.getElementById('preview-content').innerHTML;
            const title = document.getElementById('preview-title').textContent;

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .text-success { color: #16a34a; }
                        .text-danger { color: #dc2626; }
                        .status-badge {
                            display: inline-flex;
                            align-items: center;
                            padding: 4px 8px;
                            border-radius: 9999px;
                            font-size: 12px;
                            font-weight: 500;
                        }
                        .status-pending { background-color: #fef3c7; color: #92400e; }
                        .status-initiated { background-color: #fee2e2; color: #991b1b; }
                        .status-completed { background-color: #dcfce7; color: #166534; }
                        h4 { margin-top: 0; }
                        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
                        .border-t { border-top: 1px solid #e5e7eb; padding-top: 24px; margin-top: 24px; }
                        .bg-gray-50 { background-color: #f9fafb; padding: 16px; border-radius: 8px; }
                    </style>
                </head>
                <body>
                    <h2>${title}</h2>
                    ${content}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            // Print after a short delay to ensure content is loaded
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        });

        // Export transactions
        document.getElementById('exportTransactions').addEventListener('click', function() {
            alert('Export functionality would be implemented here');
        });

        // Pagination buttons
        document.getElementById('prev-page').addEventListener('click', function() {
            alert('Previous page functionality would be implemented here');
        });

        document.getElementById('next-page').addEventListener('click', function() {
            alert('Next page functionality would be implemented here');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>