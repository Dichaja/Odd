<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Cash Accounts';
$activeNav = 'cash-accounts';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <!-- Accounts View -->
    <div id="accounts-view" class="view-container active">
        <!-- Header Section -->
        <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-wallet text-primary text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-secondary font-rubik">Cash Accounts</h1>
                                <p class="text-sm text-gray-text">Manage your financial accounts and monitor balances
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Balance
                                    </p>
                                    <p class="text-lg font-bold text-blue-900" id="total-balance">UGX 0</p>
                                </div>
                                <div class="w-8 h-8 bg-blue-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-coins text-blue-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active
                                        Accounts</p>
                                    <p class="text-lg font-bold text-green-900" id="active-accounts">0</p>
                                </div>
                                <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200 col-span-2 lg:col-span-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Account Types
                                    </p>
                                    <p class="text-lg font-bold text-purple-900" id="account-types">0</p>
                                </div>
                                <div class="w-8 h-8 bg-purple-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-layer-group text-purple-600 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Controls Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-secondary font-rubik">Account Management</h2>
                            <p class="text-sm text-gray-text mt-1">View, search, and manage all your payment accounts
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 sm:w-80">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchAccounts"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Search accounts...">
                            </div>

                            <!-- Filter -->
                            <select id="filterAccounts"
                                class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium">
                                <option value="all">All Types</option>
                                <option value="bank">Bank Accounts</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="gateway">Payment Gateways</option>
                            </select>

                            <!-- Create Button -->
                            <button id="create-account-btn"
                                class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-plus"></i>
                                <span>Add Account</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts Grid/Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full" id="accounts-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Account Details</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Balance</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="accounts-table-body" class="divide-y divide-gray-100">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden p-4 space-y-4" id="accounts-mobile">
                    <!-- Populated via JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-wallet text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No accounts found</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first cash account</p>
                    <button onclick="showCreateAccountForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                        Create Account
                    </button>
                </div>

                <!-- Pagination -->
                <div
                    class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <span id="showing-start" class="font-medium">1</span> to <span id="showing-end"
                            class="font-medium">0</span> of <span id="total-accounts" class="font-medium">0</span>
                        accounts
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="prev-page"
                            class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-white hover:shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div id="pagination-numbers" class="flex items-center gap-1">
                            <!-- Populated via JavaScript -->
                        </div>
                        <button id="next-page"
                            class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-white hover:shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statement View -->
    <div id="statement-view" class="view-container hidden">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <button id="back-to-accounts"
                            class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors mb-4 font-medium">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Accounts</span>
                        </button>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice-dollar text-blue-600 text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-secondary font-rubik">Account Statement</h1>
                                <p class="text-sm text-gray-text" id="statement-account-info" data-account-id="">
                                    Transaction history and account details</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Summary Card -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Current Balance</p>
                                <h3 class="text-3xl font-bold" id="statement-balance">UGX 0</h3>
                                <p class="text-blue-200 text-xs mt-1">Last updated: <span id="last-updated">Now</span>
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-500/30 rounded-xl flex items-center justify-center">
                                <i class="fas fa-chart-line text-blue-100 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-secondary font-rubik">Transaction History</h2>
                            <p class="text-sm text-gray-text mt-1">
                                <span id="transaction-count" class="font-medium">0</span> transactions found
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative flex-1 sm:w-80">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchTransactions"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Search transactions...">
                            </div>

                            <button id="exportStatement"
                                class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 flex items-center gap-2 font-medium">
                                <i class="fas fa-download"></i>
                                <span>Export</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full" id="transactions-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Date & Time</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Transaction ID</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Description</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    User Account</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Amount</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Balance</th>
                            </tr>
                        </thead>
                        <tbody id="transactions-table-body" class="divide-y divide-gray-100">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View -->
                <div class="lg:hidden p-4 space-y-4" id="transactions-mobile">
                    <!-- Populated via JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="transactions-empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                    <p class="text-gray-500">This account has no transaction history yet</p>
                </div>

                <!-- Pagination -->
                <div
                    class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Showing <span id="trans-showing-start" class="font-medium">1</span> to <span
                            id="trans-showing-end" class="font-medium">0</span> of <span id="total-transactions"
                            class="font-medium">0</span> transactions
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="trans-prev-page"
                            class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-white hover:shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div id="trans-pagination-numbers" class="flex items-center gap-1">
                            <!-- Populated via JavaScript -->
                        </div>
                        <button id="trans-next-page"
                            class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-white hover:shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Account Modal -->
<div id="createAccountOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideCreateAccountForm()">
    </div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Create New Account</h3>
                </div>
                <button onclick="hideCreateAccountForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <!-- Form -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="createAccountForm" class="space-y-6" onsubmit="return false;">
                    <!-- Account Type -->
                    <div>
                        <label for="accountMode" class="block text-sm font-semibold text-gray-700 mb-3">Account
                            Type</label>
                        <div class="grid grid-cols-1 gap-3">
                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="accountMode" value="bank" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-university text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Bank Account</div>
                                        <div class="text-sm text-gray-500">Traditional banking accounts</div>
                                    </div>
                                </div>
                            </label>

                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="accountMode" value="mobile_money" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-mobile-alt text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Mobile Money</div>
                                        <div class="text-sm text-gray-500">MTN, Airtel mobile wallets</div>
                                    </div>
                                </div>
                            </label>

                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="accountMode" value="gateway" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cogs text-green-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Payment Gateway</div>
                                        <div class="text-sm text-gray-500">Online payment processors</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Dynamic Provider Field -->
                    <div id="providerContainer"></div>

                    <!-- Account Name -->
                    <div>
                        <label for="accountName" class="block text-sm font-semibold text-gray-700 mb-2">Account
                            Name</label>
                        <input type="text" id="accountName" name="accountName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter a descriptive name" required>
                    </div>

                    <!-- Account Number -->
                    <div>
                        <label for="accountNumber" class="block text-sm font-semibold text-gray-700 mb-2">Account
                            Number</label>
                        <input type="text" id="accountNumber" name="accountNumber"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter account number" required>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideCreateAccountForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="submitAccountForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Create Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Account Modal -->
<div id="editAccountOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideEditAccountForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Account</h3>
                </div>
                <button onclick="hideEditAccountForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <!-- Form -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="editAccountForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editAccountId">

                    <!-- Account Type -->
                    <div>
                        <label for="editAccountMode" class="block text-sm font-semibold text-gray-700 mb-3">Account
                            Type</label>
                        <div class="grid grid-cols-1 gap-3">
                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editAccountMode" value="bank" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-university text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Bank Account</div>
                                        <div class="text-sm text-gray-500">Traditional banking accounts</div>
                                    </div>
                                </div>
                            </label>

                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editAccountMode" value="mobile_money" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-mobile-alt text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Mobile Money</div>
                                        <div class="text-sm text-gray-500">MTN, Airtel mobile wallets</div>
                                    </div>
                                </div>
                            </label>

                            <label
                                class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editAccountMode" value="gateway" class="sr-only peer">
                                <div
                                    class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-3">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-cogs text-green-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">Payment Gateway</div>
                                        <div class="text-sm text-gray-500">Online payment processors</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Dynamic Provider Field -->
                    <div id="editProviderContainer"></div>

                    <!-- Account Name -->
                    <div>
                        <label for="editAccountName" class="block text-sm font-semibold text-gray-700 mb-2">Account
                            Name</label>
                        <input type="text" id="editAccountName" name="editAccountName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter a descriptive name" required>
                    </div>

                    <!-- Account Number -->
                    <div>
                        <label for="editAccountNumber" class="block text-sm font-semibold text-gray-700 mb-2">Account
                            Number</label>
                        <input type="text" id="editAccountNumber" name="editAccountNumber"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter account number" required>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideEditAccountForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="updateAccountForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Update Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideDeleteConfirm()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this account? All associated data will be
                permanently removed.</p>
            <div class="flex gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmDeleteBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .view-container {
        transition: all 0.3s ease-in-out;
    }

    .view-container.hidden {
        display: none;
        opacity: 0;
    }

    .view-container.active {
        display: block;
        opacity: 1;
    }

    .mobile-card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem;
        transition: all 0.2s;
    }

    .mobile-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }

    .mobile-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .mobile-card-content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .mobile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .mobile-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .mobile-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        margin-top: 0.25rem;
    }

    .mobile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .account-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-bank {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .badge-mobile_money {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .badge-gateway {
        background-color: #bbf7d0;
        color: #065f46;
    }

    .amount-credit {
        color: #16a34a;
        font-weight: 600;
    }

    .amount-debit {
        color: #dc2626;
        font-weight: 600;
    }

    .status-toggle {
        position: relative;
        display: inline-flex;
        height: 1.5rem;
        width: 2.75rem;
        align-items: center;
        border-radius: 9999px;
        transition: background-color 0.2s;
        outline: none;
    }

    .status-toggle.active {
        background-color: #8c5e2a;
    }

    .status-toggle.inactive {
        background-color: #e5e7eb;
    }

    .status-toggle-button {
        display: inline-block;
        height: 1rem;
        width: 1rem;
        background-color: white;
        border-radius: 9999px;
        transition: transform 0.2s;
    }

    .status-toggle.active .status-toggle-button {
        transform: translateX(1.5rem);
    }

    .status-toggle.inactive .status-toggle-button {
        transform: translateX(0.25rem);
    }

    input[type="radio"]:checked+div {
        border-color: #8c5e2a;
        background-color: #8c5e2a;
    }

    input[type="radio"]:checked+div>div {
        opacity: 1;
    }
</style>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashAccounts.php';
    let accounts = [];
    let currentTransactions = [];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            maximumFractionDigits: 0
        }).format(Math.abs(amount));
    }

    function formatDateTime(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function getAccountTypeBadge(type) {
        const badges = {
            'bank': '<span class="account-badge badge-bank"><i class="fas fa-university mr-2"></i>Bank Account</span>',
            'mobile_money': '<span class="account-badge badge-mobile_money"><i class="fas fa-mobile-alt mr-2"></i>Mobile Money</span>',
            'gateway': '<span class="account-badge badge-gateway"><i class="fas fa-cogs mr-2"></i>Payment Gateway</span>'
        };
        return badges[type] || '<span class="account-badge">Unknown</span>';
    }

    function getAccountIcon(type) {
        const icons = {
            'bank': 'fas fa-university text-blue-600',
            'mobile_money': 'fas fa-mobile-alt text-yellow-600',
            'gateway': 'fas fa-cogs text-green-600'
        };
        return icons[type] || 'fas fa-wallet text-gray-600';
    }

    function updateQuickStats() {
        const totalBalance = accounts.reduce((sum, acc) => sum + parseFloat(acc.balance || 0), 0);
        const activeAccounts = accounts.filter(acc => acc.status === 'active').length;
        const accountTypes = [...new Set(accounts.map(acc => acc.type))].length;

        document.getElementById('total-balance').textContent = `UGX ${formatCurrency(totalBalance)}`;
        document.getElementById('active-accounts').textContent = activeAccounts;
        document.getElementById('account-types').textContent = accountTypes;
    }

    async function fetchAccounts() {
        try {
            const res = await fetch(`${API_URL}?action=getCashAccounts`);
            const data = await res.json();
            if (data.success) {
                accounts = data.accounts || [];
                renderAccountsTable(accounts);
                updateQuickStats();
            }
        } catch (err) {
            console.error('Error fetching accounts:', err);
        }
    }

    function renderAccountsTable(list) {
        const tbody = document.getElementById('accounts-table-body');
        const mobile = document.getElementById('accounts-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach(acc => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center ${acc.type === 'bank' ? 'bg-blue-100' : acc.type === 'mobile_money' ? 'bg-yellow-100' : 'bg-green-100'}">
                            <i class="${getAccountIcon(acc.type)}"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">${acc.name}</div>
                            <div class="text-sm text-gray-500">${acc.number}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">${getAccountTypeBadge(acc.type)}</td>
                <td class="px-6 py-4">
                    <button onclick="toggleStatus('${acc.id}', ${acc.status !== 'active'})" 
                        class="status-toggle ${acc.status === 'active' ? 'active' : 'inactive'}">
                        <span class="status-toggle-button"></span>
                    </button>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="font-bold text-lg text-gray-900">UGX ${formatCurrency(acc.balance)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="showStatement('${acc.id}')" 
                            class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                            title="View Statement">
                            <i class="fas fa-file-alt text-sm"></i>
                        </button>
                        <button onclick="showEditAccountForm('${acc.id}')" 
                            class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                            title="Edit Account">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="showDeleteConfirm('${acc.id}')" 
                            class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                            title="Delete Account">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'mobile-card';
            card.innerHTML = `
                <div class="mobile-card-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center ${acc.type === 'bank' ? 'bg-blue-100' : acc.type === 'mobile_money' ? 'bg-yellow-100' : 'bg-green-100'}">
                            <i class="${getAccountIcon(acc.type)}"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">${acc.name}</div>
                            <div class="text-sm text-gray-500">${acc.number}</div>
                        </div>
                    </div>
                    ${getAccountTypeBadge(acc.type)}
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid">
                        <div>
                            <span class="mobile-label">Balance</span>
                            <span class="mobile-value">UGX ${formatCurrency(acc.balance)}</span>
                        </div>
                        <div>
                            <span class="mobile-label">Status</span>
                            <div class="mt-1">
                                <button onclick="toggleStatus('${acc.id}', ${acc.status !== 'active'})" 
                                    class="status-toggle ${acc.status === 'active' ? 'active' : 'inactive'}">
                                    <span class="status-toggle-button"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button onclick="showStatement('${acc.id}')" 
                            class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-file-alt mr-1"></i>Statement
                        </button>
                        <button onclick="showEditAccountForm('${acc.id}')" 
                            class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <button onclick="showDeleteConfirm('${acc.id}')" 
                            class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                            <i class="fas fa-trash-alt mr-1"></i>Delete
                        </button>
                    </div>
                </div>`;
            mobile.appendChild(card);
        });

        updatePaginationInfo(list.length);
    }

    function updatePaginationInfo(count) {
        document.getElementById('showing-start').textContent = count ? '1' : '0';
        document.getElementById('showing-end').textContent = count;
        document.getElementById('total-accounts').textContent = count;
    }

    async function toggleStatus(id, isActive) {
        try {
            const res = await fetch(`${API_URL}?action=updateCashAccountStatus`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: isActive ? 'active' : 'inactive' })
            });
            const data = await res.json();
            if (data.success) {
                const acc = accounts.find(a => a.id === id);
                if (acc) {
                    acc.status = isActive ? 'active' : 'inactive';
                    updateQuickStats();
                }
            } else {
                alert('Failed to update account status');
            }
        } catch (err) {
            console.error('Error updating status:', err);
            alert('Error updating account status');
        }
    }

    function renderTransactionsTable(list) {
        const tbody = document.getElementById('transactions-table-body');
        const mobile = document.getElementById('transactions-mobile');
        const emptyState = document.getElementById('transactions-empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach(tx => {
            const isCredit = tx.isCredit || tx.amount > 0;
            const amountClass = isCredit ? 'amount-credit' : 'amount-debit';
            const sign = isCredit ? '+' : '-';

            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${formatDateTime(tx.date)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-mono text-gray-900">${tx.id}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-700">${tx.reason}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-700">${tx.userAccount || 'N/A'}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="text-sm font-bold ${amountClass}">${sign} UGX ${formatCurrency(tx.amount)}</div>
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="text-sm font-semibold text-gray-900">UGX ${formatCurrency(tx.balance)}</div>
                </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'mobile-card';
            card.innerHTML = `
                <div class="mobile-card-header">
                    <div>
                        <div class="font-mono text-sm font-semibold text-gray-900">${tx.id}</div>
                        <div class="text-xs text-gray-500 mt-1">${formatDateTime(tx.date)}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold ${amountClass}">${sign} UGX ${formatCurrency(tx.amount)}</div>
                        <div class="text-xs text-gray-500 mt-1">Balance: UGX ${formatCurrency(tx.balance)}</div>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div>
                        <span class="mobile-label">Description</span>
                        <span class="mobile-value">${tx.reason}</span>
                    </div>
                    ${tx.userAccount ? `
                    <div>
                        <span class="mobile-label">User Account</span>
                        <span class="mobile-value">${tx.userAccount}</span>
                    </div>` : ''}
                </div>`;
            mobile.appendChild(card);
        });

        updateTransactionsPaginationInfo(list.length);
    }

    function updateTransactionsPaginationInfo(count) {
        document.getElementById('trans-showing-start').textContent = count ? '1' : '0';
        document.getElementById('trans-showing-end').textContent = count;
        document.getElementById('total-transactions').textContent = count;
        document.getElementById('transaction-count').textContent = count;
    }

    function filterAccounts(mode) {
        if (mode === 'all') return accounts;
        return accounts.filter(a => a.type === mode);
    }

    function searchAccounts(query, mode) {
        let filtered = filterAccounts(mode);
        if (!query) return filtered;

        query = query.toLowerCase();
        return filtered.filter(a =>
            a.name.toLowerCase().includes(query) ||
            a.number.toLowerCase().includes(query) ||
            (a.provider && a.provider.toLowerCase().includes(query))
        );
    }

    function showStatement(id) {
        const acc = accounts.find(a => a.id === id);
        if (!acc) return;

        document.getElementById('statement-account-info').textContent = `${acc.name} - Transaction History`;
        document.getElementById('statement-account-info').setAttribute('data-account-id', id);
        document.getElementById('statement-balance').textContent = `UGX ${formatCurrency(acc.balance)}`;
        document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();

        // Generate dummy transactions for demo
        const dummyTransactions = generateDummyTransactions(acc);
        currentTransactions = dummyTransactions;
        renderTransactionsTable(dummyTransactions);

        document.getElementById('accounts-view').classList.replace('active', 'hidden');
        document.getElementById('statement-view').classList.replace('hidden', 'active');
    }

    function generateDummyTransactions(account) {
        const transactions = [];
        const reasons = [
            'Deposit from customer',
            'Withdrawal by user',
            'Transfer to bank',
            'Payment received',
            'Service charge',
            'Interest earned',
            'Refund processed'
        ];

        let balance = parseFloat(account.balance);
        const numTransactions = Math.floor(Math.random() * 20) + 5;

        for (let i = 0; i < numTransactions; i++) {
            const isCredit = Math.random() > 0.4;
            const amount = Math.floor(Math.random() * 100000) + 1000;

            if (!isCredit) {
                balance += amount;
            } else {
                balance = Math.max(0, balance - amount);
            }

            const date = new Date();
            date.setDate(date.getDate() - Math.floor(Math.random() * 30));

            transactions.unshift({
                id: `TXN${String(i + 1).padStart(6, '0')}`,
                date: date.toISOString(),
                reason: reasons[Math.floor(Math.random() * reasons.length)],
                userAccount: `USER${String(Math.floor(Math.random() * 1000)).padStart(3, '0')}`,
                amount: amount,
                balance: balance,
                isCredit: isCredit
            });
        }

        return transactions;
    }

    function backToAccounts() {
        document.getElementById('statement-view').classList.replace('active', 'hidden');
        document.getElementById('accounts-view').classList.replace('hidden', 'active');
    }

    function updateProviderField(form, type, value = '') {
        const container = document.getElementById(form === 'edit' ? 'editProviderContainer' : 'providerContainer');

        if (type === 'bank') {
            container.innerHTML = `
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bank Branch</label>
                    <input type="text" id="${form}ProviderName" 
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200" 
                        value="${value}" placeholder="Enter branch name" required>
                </div>`;
        } else if (type === 'mobile_money') {
            container.innerHTML = `
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Operator</label>
                    <select id="${form}ProviderOperator" 
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200" required>
                        <option value="" disabled ${!value ? 'selected' : ''}>Select operator</option>
                        <option value="MTN" ${value === 'MTN' ? 'selected' : ''}>MTN Uganda</option>
                        <option value="Airtel" ${value === 'Airtel' ? 'selected' : ''}>Airtel Uganda</option>
                    </select>
                </div>`;
        } else if (type === 'gateway') {
            container.innerHTML = `
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gateway Provider</label>
                    <input type="text" id="${form}ProviderName" 
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200" 
                        value="${value}" placeholder="Enter gateway name" required>
                </div>`;
        } else {
            container.innerHTML = '';
        }
    }

    async function createAccount() {
        const form = document.getElementById('createAccountForm');
        const formData = new FormData(form);
        const type = formData.get('accountMode');
        const name = document.getElementById('accountName').value.trim();
        const number = document.getElementById('accountNumber').value.trim();

        let provider = '';
        if (type === 'bank' || type === 'gateway') {
            const providerInput = document.getElementById('createProviderName');
            provider = providerInput ? providerInput.value.trim() : '';
        } else if (type === 'mobile_money') {
            const providerSelect = document.getElementById('createProviderOperator');
            provider = providerSelect ? providerSelect.value : '';
        }

        if (!type || !name || !number || (type !== 'gateway' && !provider)) {
            alert('Please fill in all required fields');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=createCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, name, number, provider })
            });
            const data = await res.json();

            if (data.success) {
                hideCreateAccountForm();
                fetchAccounts();
            } else {
                alert(data.message || 'Failed to create account');
            }
        } catch (err) {
            console.error('Error creating account:', err);
            alert('Error creating account');
        }
    }

    async function updateAccount() {
        const id = document.getElementById('editAccountId').value;
        const form = document.getElementById('editAccountForm');
        const formData = new FormData(form);
        const type = formData.get('editAccountMode');
        const name = document.getElementById('editAccountName').value.trim();
        const number = document.getElementById('editAccountNumber').value.trim();

        let provider = '';
        if (type === 'bank' || type === 'gateway') {
            const providerInput = document.getElementById('editProviderName');
            provider = providerInput ? providerInput.value.trim() : '';
        } else if (type === 'mobile_money') {
            const providerSelect = document.getElementById('editProviderOperator');
            provider = providerSelect ? providerSelect.value : '';
        }

        if (!type || !name || !number || (type !== 'gateway' && !provider)) {
            alert('Please fill in all required fields');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=updateCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type, name, number, provider })
            });
            const data = await res.json();

            if (data.success) {
                hideEditAccountForm();
                fetchAccounts();
            } else {
                alert(data.message || 'Failed to update account');
            }
        } catch (err) {
            console.error('Error updating account:', err);
            alert('Error updating account');
        }
    }

    async function deleteAccount() {
        const id = document.getElementById('confirmDeleteBtn').getAttribute('data-account-id');

        try {
            const res = await fetch(`${API_URL}?action=deleteCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();

            if (data.success) {
                hideDeleteConfirm();
                fetchAccounts();
            } else {
                alert(data.message || 'Failed to delete account');
            }
        } catch (err) {
            console.error('Error deleting account:', err);
            alert('Error deleting account');
        }
    }

    function showCreateAccountForm() {
        const offcanvas = document.getElementById('createAccountOffcanvas');
        const form = document.getElementById('createAccountForm');

        // Reset form
        form.reset();
        document.getElementById('providerContainer').innerHTML = '';

        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideCreateAccountForm() {
        const offcanvas = document.getElementById('createAccountOffcanvas');
        const panel = offcanvas.querySelector('.transform');

        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    function showEditAccountForm(id) {
        const acc = accounts.find(a => a.id === id);
        if (!acc) return;

        document.getElementById('editAccountId').value = acc.id;
        document.getElementById('editAccountName').value = acc.name;
        document.getElementById('editAccountNumber').value = acc.number;

        // Set account type radio button
        const typeRadio = document.querySelector(`input[name="editAccountMode"][value="${acc.type}"]`);
        if (typeRadio) typeRadio.checked = true;

        updateProviderField('edit', acc.type, acc.provider);

        const offcanvas = document.getElementById('editAccountOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideEditAccountForm() {
        const offcanvas = document.getElementById('editAccountOffcanvas');
        const panel = offcanvas.querySelector('.transform');

        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    function showDeleteConfirm(id) {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('hidden');
        document.getElementById('confirmDeleteBtn').setAttribute('data-account-id', id);
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        fetchAccounts();

        // Main buttons
        document.getElementById('create-account-btn').addEventListener('click', showCreateAccountForm);
        document.getElementById('back-to-accounts').addEventListener('click', backToAccounts);

        // Form submissions
        document.getElementById('submitAccountForm').addEventListener('click', createAccount);
        document.getElementById('updateAccountForm').addEventListener('click', updateAccount);
        document.getElementById('confirmDeleteBtn').addEventListener('click', deleteAccount);

        // Account type changes
        document.addEventListener('change', (e) => {
            if (e.target.name === 'accountMode') {
                updateProviderField('create', e.target.value);
            } else if (e.target.name === 'editAccountMode') {
                updateProviderField('edit', e.target.value);
            }
        });

        // Search and filter
        document.getElementById('filterAccounts').addEventListener('change', () => {
            const query = document.getElementById('searchAccounts').value.trim();
            const mode = document.getElementById('filterAccounts').value;
            renderAccountsTable(searchAccounts(query, mode));
        });

        document.getElementById('searchAccounts').addEventListener('input', () => {
            const query = document.getElementById('searchAccounts').value.trim();
            const mode = document.getElementById('filterAccounts').value;
            renderAccountsTable(searchAccounts(query, mode));
        });

        // Transaction search
        document.getElementById('searchTransactions').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            if (!query) {
                renderTransactionsTable(currentTransactions);
                return;
            }

            const filtered = currentTransactions.filter(tx =>
                tx.id.toLowerCase().includes(query) ||
                tx.reason.toLowerCase().includes(query) ||
                (tx.userAccount && tx.userAccount.toLowerCase().includes(query))
            );
            renderTransactionsTable(filtered);
        });

        // Export statement
        document.getElementById('exportStatement').addEventListener('click', () => {
            const accountId = document.getElementById('statement-account-info').getAttribute('data-account-id');
            const account = accounts.find(a => a.id === accountId);

            if (account && currentTransactions.length > 0) {
                exportToCSV(account, currentTransactions);
            } else {
                alert('No transactions to export');
            }
        });
    });

    function exportToCSV(account, transactions) {
        const headers = ['Date', 'Transaction ID', 'Description', 'User Account', 'Amount', 'Balance'];
        const csvContent = [
            `Account Statement - ${account.name}`,
            `Generated on: ${new Date().toLocaleString()}`,
            '',
            headers.join(','),
            ...transactions.map(tx => [
                formatDateTime(tx.date),
                tx.id,
                `"${tx.reason}"`,
                tx.userAccount || 'N/A',
                `${tx.isCredit ? '+' : '-'}${tx.amount}`,
                tx.balance
            ].join(','))
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${account.name.replace(/[^a-z0-9]/gi, '_')}_statement_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    // Global functions for onclick handlers
    window.showStatement = showStatement;
    window.showEditAccountForm = showEditAccountForm;
    window.showDeleteConfirm = showDeleteConfirm;
    window.hideDeleteConfirm = hideDeleteConfirm;
    window.hideCreateAccountForm = hideCreateAccountForm;
    window.hideEditAccountForm = hideEditAccountForm;
    window.toggleStatus = toggleStatus;
    window.showCreateAccountForm = showCreateAccountForm;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>