<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Cash Accounts';
$activeNav = 'cash-account';
ob_start();
?>

<div class="space-y-6" id="app-container">
    <!-- Accounts View -->
    <div id="accounts-view" class="view-container active">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-secondary">Cash Accounts</h1>
                <p class="text-sm text-gray-text mt-1">Manage your bank, mobile money, and credit accounts</p>
            </div>
            <button id="create-account-btn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Create Account</span>
            </button>
        </div>

        <!-- Accounts Table Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">All Accounts</h2>
                    <p class="text-sm text-gray-text mt-1">View and manage all your payment accounts</p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchAccounts" placeholder="Search accounts..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select id="filterAccounts" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                            <option value="all">All Account Types</option>
                            <option value="bank">Bank Accounts</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="credit">Zzimba Credit</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="responsive-table-desktop overflow-x-auto">
                <table class="w-full" id="accounts-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Mode</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Branch/Operator</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Account Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Account Number</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Balance (UGX)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accounts-table-body">
                        <!-- Table rows will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="responsive-table-mobile p-4" id="accounts-mobile">
                <!-- Mobile cards will be populated by JavaScript -->
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-accounts">100</span> accounts
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

    <!-- Statement View -->
    <div id="statement-view" class="view-container hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <button id="back-to-accounts" class="flex items-center gap-2 text-primary hover:text-primary/80 mb-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Accounts</span>
                </button>
                <h1 class="text-2xl font-semibold text-secondary">Account Statement</h1>
                <p class="text-sm text-gray-text mt-1" id="statement-account-info">View transaction history for this account</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-sm text-gray-text">Current Balance</p>
                    <h3 class="text-xl font-semibold text-secondary" id="statement-balance">UGX 0</h3>
                </div>
            </div>
        </div>

        <!-- Statement Table Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Transaction History</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="transaction-count">0</span> transactions found in the selected period
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
                    <button id="exportStatement" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
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

            <!-- Desktop Table -->
            <div class="responsive-table-desktop overflow-x-auto">
                <table class="w-full" id="transactions-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Transaction ID</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Reason</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">User Account</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Amount (UGX)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Balance (UGX)</th>
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
                    Showing <span id="trans-showing-start">1</span> to <span id="trans-showing-end">10</span> of <span id="total-transactions">100</span> transactions
                </div>
                <div class="flex items-center gap-2">
                    <button id="trans-prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="trans-pagination-numbers" class="flex items-center">
                        <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                        <span class="px-2">...</span>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">10</button>
                    </div>
                    <button id="trans-next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Account Offcanvas -->
<div id="createAccountOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideCreateAccountForm()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Create New Account</h3>
                <button onclick="hideCreateAccountForm()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="createAccountForm" class="space-y-6">
                    <div>
                        <label for="accountMode" class="block text-sm font-medium text-gray-700 mb-1">Account Mode</label>
                        <select id="accountMode" name="accountMode" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                            <option value="" disabled selected>Select Account Mode</option>
                            <option value="bank">Bank</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="credit">Zzimba Credit</option>
                        </select>
                    </div>

                    <!-- Dynamic field for Branch/Operator -->
                    <div id="branchOperatorContainer">
                        <!-- Will be populated based on selected mode -->
                    </div>

                    <div>
                        <label for="accountName" class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                        <input type="text" id="accountName" name="accountName" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter account name" required>
                    </div>

                    <div>
                        <label for="accountNumber" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                        <input type="text" id="accountNumber" name="accountNumber" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter account number" required>
                        <p id="accountNumberHelp" class="text-xs text-gray-500 mt-1">Enter bank account number or phone number</p>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-gray-100">
                <button id="submitAccountForm" class="w-full h-10 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Create Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Account Offcanvas -->
<div id="editAccountOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideEditAccountForm()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Edit Account</h3>
                <button onclick="hideEditAccountForm()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="editAccountForm" class="space-y-6">
                    <input type="hidden" id="editAccountId">
                    <div>
                        <label for="editAccountMode" class="block text-sm font-medium text-gray-700 mb-1">Account Mode</label>
                        <select id="editAccountMode" name="editAccountMode" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                            <option value="" disabled>Select Account Mode</option>
                            <option value="bank">Bank</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="credit">Zzimba Credit</option>
                        </select>
                    </div>

                    <!-- Dynamic field for Branch/Operator -->
                    <div id="editBranchOperatorContainer">
                        <!-- Will be populated based on selected mode -->
                    </div>

                    <div>
                        <label for="editAccountName" class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                        <input type="text" id="editAccountName" name="editAccountName" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter account name" required>
                    </div>

                    <div>
                        <label for="editAccountNumber" class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                        <input type="text" id="editAccountNumber" name="editAccountNumber" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter account number" required>
                        <p class="text-xs text-gray-500 mt-1">Enter bank account number or phone number</p>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-gray-100">
                <button id="updateAccountForm" class="w-full h-10 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Update Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteConfirm()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-secondary mb-2">Confirm Delete</h3>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this account? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button onclick="hideDeleteConfirm()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </div>
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

    .view-container {
        transition: opacity 0.3s ease-in-out;
    }

    .view-container.hidden {
        display: none;
        opacity: 0;
    }

    .view-container.active {
        display: block;
        opacity: 1;
    }

    .account-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .account-bank {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .account-mobile {
        background-color: #fef3c7;
        color: #92400e;
    }

    .account-credit {
        background-color: #dcfce7;
        color: #166534;
    }

    .amount-credit {
        color: #16a34a;
    }

    .amount-debit {
        color: #dc2626;
    }
</style>

<script>
    // Sample account data - in a real application, this would come from an API
    const accounts = [{
            id: 1,
            mode: "bank",
            branchOperator: "Stanbic Bank - Main Branch",
            accountName: "Zzimba Operations Account",
            accountNumber: "9876543210",
            balance: 15750000
        },
        {
            id: 2,
            mode: "mobile",
            branchOperator: "MTN",
            accountName: "Zzimba Mobile Payments",
            accountNumber: "0772123456",
            balance: 3450000
        },
        {
            id: 3,
            mode: "credit",
            branchOperator: "Zzimba Credit",
            accountName: "Customer Credit Account",
            accountNumber: "ZC-12345",
            balance: 8900000
        },
        {
            id: 4,
            mode: "bank",
            branchOperator: "Equity Bank - Kampala",
            accountName: "Zzimba Savings Account",
            accountNumber: "1234567890",
            balance: 25600000
        },
        {
            id: 5,
            mode: "mobile",
            branchOperator: "Airtel",
            accountName: "Zzimba Airtel Payments",
            accountNumber: "0700987654",
            balance: 1890000
        }
    ];

    // Sample transactions data
    const transactions = {
        1: [{
                id: "TRX-10001",
                date: "2025-03-07T14:22:10",
                reason: "Customer Payment - Invoice #INV-2025-001",
                userAccount: "johndoe",
                amount: 1500000,
                isCredit: true,
                balance: 15750000
            },
            {
                id: "TRX-10002",
                date: "2025-03-05T09:15:30",
                reason: "Vendor Payment - Office Supplies",
                userAccount: "janesmith",
                amount: 450000,
                isCredit: false,
                balance: 14250000
            },
            {
                id: "TRX-10003",
                date: "2025-03-01T11:30:45",
                reason: "Customer Payment - Invoice #INV-2025-002",
                userAccount: "johndoe",
                amount: 2200000,
                isCredit: true,
                balance: 14700000
            },
            {
                id: "TRX-10004",
                date: "2025-02-28T16:45:20",
                reason: "Salary Payments - February 2025",
                userAccount: "janesmith",
                amount: 3500000,
                isCredit: false,
                balance: 12500000
            },
            {
                id: "TRX-10005",
                date: "2025-02-25T10:20:15",
                reason: "Customer Payment - Invoice #INV-2025-003",
                userAccount: "johndoe",
                amount: 1800000,
                isCredit: true,
                balance: 16000000
            },
            {
                id: "TRX-10006",
                date: "2025-02-20T13:10:30",
                reason: "Rent Payment - March 2025",
                userAccount: "janesmith",
                amount: 2000000,
                isCredit: false,
                balance: 14200000
            },
            {
                id: "TRX-10007",
                date: "2025-02-15T09:45:00",
                reason: "Customer Payment - Invoice #INV-2025-004",
                userAccount: "johndoe",
                amount: 3200000,
                isCredit: true,
                balance: 16200000
            }
        ],
        2: [{
                id: "TRX-20001",
                date: "2025-03-08T10:15:20",
                reason: "Mobile Payment Collection - Customer #C1001",
                userAccount: "johndoe",
                amount: 350000,
                isCredit: true,
                balance: 3450000
            },
            {
                id: "TRX-20002",
                date: "2025-03-06T14:30:45",
                reason: "Utility Bill Payment - Electricity",
                userAccount: "janesmith",
                amount: 180000,
                isCredit: false,
                balance: 3100000
            },
            {
                id: "TRX-20003",
                date: "2025-03-04T09:20:10",
                reason: "Mobile Payment Collection - Customer #C1002",
                userAccount: "johndoe",
                amount: 420000,
                isCredit: true,
                balance: 3280000
            },
            {
                id: "TRX-20004",
                date: "2025-03-01T16:45:30",
                reason: "Internet Subscription Payment",
                userAccount: "janesmith",
                amount: 250000,
                isCredit: false,
                balance: 2860000
            },
            {
                id: "TRX-20005",
                date: "2025-02-28T11:10:15",
                reason: "Mobile Payment Collection - Customer #C1003",
                userAccount: "johndoe",
                amount: 380000,
                isCredit: true,
                balance: 3110000
            }
        ],
        3: [{
                id: "TRX-30001",
                date: "2025-03-07T15:30:20",
                reason: "Credit Disbursement - Loan #L2025-001",
                userAccount: "janesmith",
                amount: 1500000,
                isCredit: false,
                balance: 8900000
            },
            {
                id: "TRX-30002",
                date: "2025-03-05T10:15:45",
                reason: "Loan Repayment - Loan #L2025-001",
                userAccount: "johndoe",
                amount: 300000,
                isCredit: true,
                balance: 10400000
            },
            {
                id: "TRX-30003",
                date: "2025-03-02T14:20:30",
                reason: "Credit Disbursement - Loan #L2025-002",
                userAccount: "janesmith",
                amount: 2000000,
                isCredit: false,
                balance: 10100000
            },
            {
                id: "TRX-30004",
                date: "2025-02-28T09:45:15",
                reason: "Loan Repayment - Loan #L2025-002",
                userAccount: "johndoe",
                amount: 400000,
                isCredit: true,
                balance: 12100000
            },
            {
                id: "TRX-30005",
                date: "2025-02-25T16:30:40",
                reason: "Credit Disbursement - Loan #L2025-003",
                userAccount: "janesmith",
                amount: 1800000,
                isCredit: false,
                balance: 11700000
            }
        ],
        4: [{
                id: "TRX-40001",
                date: "2025-03-08T09:30:15",
                reason: "Customer Deposit - Contract #CON-2025-001",
                userAccount: "johndoe",
                amount: 3500000,
                isCredit: true,
                balance: 25600000
            },
            {
                id: "TRX-40002",
                date: "2025-03-06T14:15:30",
                reason: "Vendor Payment - Office Equipment",
                userAccount: "janesmith",
                amount: 1200000,
                isCredit: false,
                balance: 22100000
            },
            {
                id: "TRX-40003",
                date: "2025-03-04T11:45:20",
                reason: "Customer Deposit - Contract #CON-2025-002",
                userAccount: "johndoe",
                amount: 2800000,
                isCredit: true,
                balance: 23300000
            },
            {
                id: "TRX-40004",
                date: "2025-03-01T16:20:45",
                reason: "Insurance Premium Payment",
                userAccount: "janesmith",
                amount: 950000,
                isCredit: false,
                balance: 20500000
            },
            {
                id: "TRX-40005",
                date: "2025-02-27T10:10:30",
                reason: "Customer Deposit - Contract #CON-2025-003",
                userAccount: "johndoe",
                amount: 4200000,
                isCredit: true,
                balance: 21450000
            }
        ],
        5: [{
                id: "TRX-50001",
                date: "2025-03-08T11:20:30",
                reason: "Mobile Payment Collection - Customer #C2001",
                userAccount: "johndoe",
                amount: 280000,
                isCredit: true,
                balance: 1890000
            },
            {
                id: "TRX-50002",
                date: "2025-03-06T15:45:10",
                reason: "Utility Bill Payment - Water",
                userAccount: "janesmith",
                amount: 150000,
                isCredit: false,
                balance: 1610000
            },
            {
                id: "TRX-50003",
                date: "2025-03-04T10:30:45",
                reason: "Mobile Payment Collection - Customer #C2002",
                userAccount: "johndoe",
                amount: 320000,
                isCredit: true,
                balance: 1760000
            },
            {
                id: "TRX-50004",
                date: "2025-03-02T14:15:20",
                reason: "Office Supplies Payment",
                userAccount: "janesmith",
                amount: 180000,
                isCredit: false,
                balance: 1440000
            },
            {
                id: "TRX-50005",
                date: "2025-02-28T09:50:15",
                reason: "Mobile Payment Collection - Customer #C2003",
                userAccount: "johndoe",
                amount: 290000,
                isCredit: true,
                balance: 1620000
            }
        ]
    };

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Format date to a readable format
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Format date and time to a readable format
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Get account mode badge
    function getAccountModeBadge(mode) {
        switch (mode) {
            case 'bank':
                return '<span class="account-badge account-bank"><i class="fas fa-university mr-1"></i>Bank</span>';
            case 'mobile':
                return '<span class="account-badge account-mobile"><i class="fas fa-mobile-alt mr-1"></i>Mobile Money</span>';
            case 'credit':
                return '<span class="account-badge account-credit"><i class="fas fa-credit-card mr-1"></i>Zzimba Credit</span>';
            default:
                return '<span class="account-badge">Unknown</span>';
        }
    }

    // Render accounts table
    function renderAccountsTable(accountList) {
        const tableBody = document.getElementById('accounts-table-body');
        const mobileContainer = document.getElementById('accounts-mobile');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        accountList.forEach(account => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const modeBadge = getAccountModeBadge(account.mode);

            tr.innerHTML = `
                <td class="px-6 py-4 text-sm">${modeBadge}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${account.branchOperator}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${account.accountName}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${account.accountNumber}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">UGX ${formatCurrency(account.balance)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button onclick="showStatement(${account.id})" class="text-blue-600 hover:text-blue-800" title="View Statement">
                            <i class="fas fa-file-alt"></i>
                        </button>
                        <button onclick="showEditAccountForm(${account.id})" class="text-gray-600 hover:text-gray-800" title="Edit Account">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="showDeleteConfirm(${account.id})" class="text-red-600 hover:text-red-800" title="Delete Account">
                            <i class="fas fa-trash-alt"></i>
                        </button>
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
                        <div class="font-medium text-gray-900">${account.accountName}</div>
                        <div class="text-xs text-gray-500 mt-1">${account.accountNumber}</div>
                    </div>
                    <div>
                        ${modeBadge}
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Branch/Operator</span>
                            <span class="mobile-value">${account.branchOperator}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Balance</span>
                            <span class="mobile-value">UGX ${formatCurrency(account.balance)}</span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button onclick="showStatement(${account.id})" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">
                            <i class="fas fa-file-alt mr-1"></i> Statement
                        </button>
                        <button onclick="showEditAccountForm(${account.id})" class="px-3 py-1.5 text-xs bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button onclick="showDeleteConfirm(${account.id})" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;

            mobileContainer.appendChild(mobileCard);
        });

        // Update pagination info
        document.getElementById('showing-start').textContent = '1';
        document.getElementById('showing-end').textContent = accountList.length;
        document.getElementById('total-accounts').textContent = accountList.length;
    }

    // Render transactions table
    function renderTransactionsTable(transactionList) {
        const tableBody = document.getElementById('transactions-table-body');
        const mobileContainer = document.getElementById('transactions-mobile');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        transactionList.forEach(transaction => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const amountClass = transaction.isCredit ? 'amount-credit' : 'amount-debit';
            const amountPrefix = transaction.isCredit ? '+' : '-';

            tr.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${formatDateTime(transaction.date)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${transaction.id}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${transaction.reason}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${transaction.userAccount}</td>
                <td class="px-6 py-4 text-sm font-medium ${amountClass}">${amountPrefix} UGX ${formatCurrency(transaction.amount)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">UGX ${formatCurrency(transaction.balance)}</td>
            `;

            tableBody.appendChild(tr);

            // Mobile card
            const mobileCard = document.createElement('div');
            mobileCard.className = 'mobile-card';

            mobileCard.innerHTML = `
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">${transaction.id}</div>
                        <div class="text-xs text-gray-500 mt-1">${formatDateTime(transaction.date)}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-medium ${amountClass}">${amountPrefix} UGX ${formatCurrency(transaction.amount)}</div>
                        <div class="text-xs text-gray-500 mt-1">Balance: UGX ${formatCurrency(transaction.balance)}</div>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="space-y-3">
                        <div>
                            <span class="mobile-label">Reason</span>
                            <span class="mobile-value block">${transaction.reason}</span>
                        </div>
                        <div>
                            <span class="mobile-label">User Account</span>
                            <span class="mobile-value">${transaction.userAccount}</span>
                        </div>
                    </div>
                </div>
            `;

            mobileContainer.appendChild(mobileCard);
        });

        // Update pagination info
        document.getElementById('trans-showing-start').textContent = '1';
        document.getElementById('trans-showing-end').textContent = transactionList.length;
        document.getElementById('total-transactions').textContent = transactionList.length;
        document.getElementById('transaction-count').textContent = transactionList.length;
    }

    // Filter accounts
    function filterAccounts(mode) {
        if (mode === 'all') {
            return accounts;
        }

        return accounts.filter(account => account.mode === mode);
    }

    // Search accounts
    function searchAccounts(query) {
        if (!query) return accounts;

        query = query.toLowerCase();
        return accounts.filter(account =>
            account.accountName.toLowerCase().includes(query) ||
            account.accountNumber.toLowerCase().includes(query) ||
            account.branchOperator.toLowerCase().includes(query)
        );
    }

    // Search transactions
    function searchTransactions(accountId, query) {
        if (!query) return transactions[accountId];

        query = query.toLowerCase();
        return transactions[accountId].filter(transaction =>
            transaction.id.toLowerCase().includes(query) ||
            transaction.reason.toLowerCase().includes(query) ||
            transaction.userAccount.toLowerCase().includes(query)
        );
    }

    // Show statement view
    function showStatement(accountId) {
        const account = accounts.find(a => a.id === accountId);
        if (!account) return;

        // Update statement header
        document.getElementById('statement-account-info').textContent = `${account.accountName} (${account.accountNumber})`;
        document.getElementById('statement-balance').textContent = `UGX ${formatCurrency(account.balance)}`;

        // Render transactions
        renderTransactionsTable(transactions[accountId]);

        // Switch views
        document.getElementById('accounts-view').classList.remove('active');
        document.getElementById('accounts-view').classList.add('hidden');
        document.getElementById('statement-view').classList.remove('hidden');

        // Delay to ensure smooth transition
        setTimeout(() => {
            document.getElementById('statement-view').classList.add('active');
        }, 10);
    }

    // Back to accounts view
    function backToAccounts() {
        // Switch views
        document.getElementById('statement-view').classList.remove('active');
        document.getElementById('statement-view').classList.add('hidden');
        document.getElementById('accounts-view').classList.remove('hidden');

        // Delay to ensure smooth transition
        setTimeout(() => {
            document.getElementById('accounts-view').classList.add('active');
        }, 10);
    }

    // Show create account form
    function showCreateAccountForm() {
        const offcanvas = document.getElementById('createAccountOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(function() {
            offcanvas.querySelector('.transform').classList.remove('translate-x-full');
        }, 10);
    }

    // Hide create account form
    function hideCreateAccountForm() {
        const offcanvas = document.getElementById('createAccountOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(function() {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    // Show edit account form
    function showEditAccountForm(accountId) {
        const account = accounts.find(a => a.id === accountId);
        if (!account) return;

        // Populate form
        document.getElementById('editAccountId').value = account.id;
        document.getElementById('editAccountMode').value = account.mode;
        document.getElementById('editAccountName').value = account.accountName;
        document.getElementById('editAccountNumber').value = account.accountNumber;

        // Update branch/operator field
        updateBranchOperatorField('edit', account.mode, account.branchOperator);

        // Show offcanvas
        const offcanvas = document.getElementById('editAccountOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(function() {
            offcanvas.querySelector('.transform').classList.remove('translate-x-full');
        }, 10);
    }

    // Hide edit account form
    function hideEditAccountForm() {
        const offcanvas = document.getElementById('editAccountOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(function() {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    // Show delete confirmation
    function showDeleteConfirm(accountId) {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('hidden');

        // Set account ID for delete button
        document.getElementById('confirmDeleteBtn').setAttribute('data-account-id', accountId);
    }

    // Hide delete confirmation
    function hideDeleteConfirm() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.add('hidden');
    }

    // Update branch/operator field based on selected mode
    function updateBranchOperatorField(formType, mode, value = '') {
        const containerId = formType === 'edit' ? 'editBranchOperatorContainer' : 'branchOperatorContainer';
        const container = document.getElementById(containerId);

        switch (mode) {
            case 'bank':
                container.innerHTML = `
                    <div>
                        <label for="${formType}BranchName" class="block text-sm font-medium text-gray-700 mb-1">Bank Branch</label>
                        <input type="text" id="${formType}BranchName" name="${formType}BranchName" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter bank branch name" value="${value}" required>
                    </div>
                `;
                break;
            case 'mobile':
                container.innerHTML = `
                    <div>
                        <label for="${formType}MobileOperator" class="block text-sm font-medium text-gray-700 mb-1">Mobile Operator</label>
                        <select id="${formType}MobileOperator" name="${formType}MobileOperator" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                            <option value="" disabled ${!value ? 'selected' : ''}>Select Mobile Operator</option>
                            <option value="MTN" ${value === 'MTN' ? 'selected' : ''}>MTN</option>
                            <option value="Airtel" ${value === 'Airtel' ? 'selected' : ''}>Airtel</option>
                        </select>
                    </div>
                `;
                break;
            case 'credit':
                container.innerHTML = `
                    <div>
                        <label for="${formType}CreditType" class="block text-sm font-medium text-gray-700 mb-1">Credit Type</label>
                        <input type="text" id="${formType}CreditType" name="${formType}CreditType" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" value="Zzimba Credit" readonly>
                    </div>
                `;
                break;
            default:
                container.innerHTML = '';
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initial render
        renderAccountsTable(accounts);

        // Create account button
        document.getElementById('create-account-btn').addEventListener('click', showCreateAccountForm);

        // Back to accounts button
        document.getElementById('back-to-accounts').addEventListener('click', backToAccounts);

        // Account mode change
        document.getElementById('accountMode').addEventListener('change', function() {
            updateBranchOperatorField('create', this.value);
        });

        // Edit account mode change
        document.getElementById('editAccountMode').addEventListener('change', function() {
            updateBranchOperatorField('edit', this.value);
        });

        // Filter accounts
        document.getElementById('filterAccounts').addEventListener('change', function() {
            const mode = this.value;
            const filteredAccounts = filterAccounts(mode);

            // Apply search filter if there's a search query
            const searchQuery = document.getElementById('searchAccounts').value.trim();
            const finalAccounts = searchQuery ?
                filteredAccounts.filter(account =>
                    account.accountName.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    account.accountNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
                    account.branchOperator.toLowerCase().includes(searchQuery.toLowerCase())
                ) : filteredAccounts;

            renderAccountsTable(finalAccounts);
        });

        // Search accounts
        document.getElementById('searchAccounts').addEventListener('input', function() {
            const query = this.value.trim();
            const mode = document.getElementById('filterAccounts').value;

            // First filter by mode, then by search query
            const filteredByMode = filterAccounts(mode);
            const filteredAccounts = query ?
                filteredByMode.filter(account =>
                    account.accountName.toLowerCase().includes(query.toLowerCase()) ||
                    account.accountNumber.toLowerCase().includes(query.toLowerCase()) ||
                    account.branchOperator.toLowerCase().includes(query.toLowerCase())
                ) : filteredByMode;

            renderAccountsTable(filteredAccounts);
        });

        // Search transactions
        document.getElementById('searchTransactions').addEventListener('input', function() {
            const query = this.value.trim();
            const accountId = parseInt(document.getElementById('statement-account-info').getAttribute('data-account-id'));

            if (accountId) {
                const filteredTransactions = searchTransactions(accountId, query);
                renderTransactionsTable(filteredTransactions);
            }
        });

        // Date range select
        document.getElementById('dateRangeSelect').addEventListener('change', function() {
            const value = this.value;
            const customDateRange = document.getElementById('customDateRange');

            if (value === 'custom') {
                customDateRange.classList.remove('hidden');
            } else {
                customDateRange.classList.add('hidden');

                // Here you would filter transactions based on the selected date range
                // For this demo, we'll just use the existing transactions
                const accountId = parseInt(document.getElementById('statement-account-info').getAttribute('data-account-id'));
                if (accountId) {
                    renderTransactionsTable(transactions[accountId]);
                }
            }
        });

        // Apply custom date range
        document.getElementById('applyDateRange').addEventListener('click', function() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                const accountId = parseInt(document.getElementById('statement-account-info').getAttribute('data-account-id'));
                if (accountId) {
                    // Here you would filter transactions based on the date range
                    // For this demo, we'll just use the existing transactions
                    renderTransactionsTable(transactions[accountId]);
                }
            }
        });

        // Create account form submit
        document.getElementById('submitAccountForm').addEventListener('click', function() {
            // In a real application, you would submit the form data to the server
            alert('Account creation functionality would be implemented here');
            hideCreateAccountForm();
        });

        // Update account form submit
        document.getElementById('updateAccountForm').addEventListener('click', function() {
            // In a real application, you would submit the form data to the server
            alert('Account update functionality would be implemented here');
            hideEditAccountForm();
        });

        // Confirm delete
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            const accountId = parseInt(this.getAttribute('data-account-id'));

            // In a real application, you would send a delete request to the server
            alert(`Account ${accountId} would be deleted here`);
            hideDeleteConfirm();
        });

        // Export statement
        document.getElementById('exportStatement').addEventListener('click', function() {
            alert('Statement export functionality would be implemented here');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>