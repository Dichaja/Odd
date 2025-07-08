<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Cash Accounts';
$activeNav = 'cash-accounts';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cash Accounts</h1>
                <p class="text-gray-600 mt-1">Manage your financial accounts and monitor balances</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Balance</p>
                        <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="total-balance">UGX 0.00</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-coins text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active Accounts</p>
                        <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="active-accounts">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Account Types</p>
                        <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="account-types">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-layer-group text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Total Accounts</p>
                        <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="total-accounts-count">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-wallet text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-8">
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div id="desktop-nav">
                    <nav class="space-y-2" aria-label="Account Navigation">
                        <button id="accounts-tab"
                            class="tab-button active w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 bg-primary/10 text-primary border border-primary/20"
                            onclick="switchTab('accounts')">
                            <i class="fas fa-wallet"></i>
                            <span>All Accounts</span>
                        </button>
                        <button id="bank-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchTab('bank')">
                            <i class="fas fa-university"></i>
                            <span>Bank Accounts</span>
                        </button>
                        <button id="mobile-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchTab('mobile_money')">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile Money</span>
                        </button>
                        <button id="gateway-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchTab('gateway')">
                            <i class="fas fa-cogs"></i>
                            <span>Payment Gateways</span>
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <div class="lg:hidden mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                        <div class="relative">
                            <button id="mobile-tab-toggle"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-wallet text-primary"></i>
                                    <span id="mobile-tab-label" class="font-medium text-gray-900">All Accounts</span>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                    id="mobile-tab-chevron"></i>
                            </button>

                            <div id="mobile-tab-dropdown"
                                class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                                <div class="py-2">
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="accounts">
                                        <i class="fas fa-wallet text-gray-600"></i>
                                        <span>All Accounts</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="bank">
                                        <i class="fas fa-university text-blue-600"></i>
                                        <span>Bank Accounts</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="mobile_money">
                                        <i class="fas fa-mobile-alt text-yellow-600"></i>
                                        <span>Mobile Money</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="gateway">
                                        <i class="fas fa-cogs text-green-600"></i>
                                        <span>Payment Gateways</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="accounts-content" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="accounts-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchAccounts"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search accounts...">
                                    </div>
                                </div>

                                <div
                                    class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <div class="relative">
                                        <button id="viewColumnsBtn" onclick="toggleColumnSelector()"
                                            class="hidden lg:flex px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>View</span>
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </button>

                                        <div id="columnSelector"
                                            class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-48">
                                            <div class="p-3 border-b border-gray-100">
                                                <h4 class="text-sm font-semibold text-gray-900">Show Columns</h4>
                                                <p class="text-xs text-gray-500 mt-1">Select at least 3 columns</p>
                                            </div>
                                            <div class="p-2 space-y-1" id="columnCheckboxes">
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="details" checked>
                                                    <span class="text-sm text-gray-700">Account Details</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="type" checked>
                                                    <span class="text-sm text-gray-700">Account Type</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="status" checked>
                                                    <span class="text-sm text-gray-700">Status</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="balance" checked>
                                                    <span class="text-sm text-gray-700">Balance</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="actions" checked>
                                                    <span class="text-sm text-gray-700">Actions</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button id="create-account-btn"
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Account</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="accounts-grid">
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="statement-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="statement-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <button id="back-to-accounts"
                                        class="flex items-center gap-2 text-primary hover:text-primary/80 transition-colors font-medium">
                                        <i class="fas fa-arrow-left"></i>
                                        <span>Back to Accounts</span>
                                    </button>
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900 font-rubik">Account Statement
                                        </h2>
                                        <p class="text-sm text-gray-600 mt-1" id="statement-account-info">Transaction
                                            history and account details</p>
                                    </div>
                                </div>

                                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-4 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-blue-100 text-xs font-medium">Current Balance</p>
                                            <h3 class="text-2xl font-bold" id="statement-balance">UGX 0</h3>
                                        </div>
                                        <div
                                            class="w-10 h-10 bg-blue-500/30 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-chart-line text-blue-100"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchTransactions"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search transactions...">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button id="exportStatement"
                                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 flex items-center gap-2 font-medium">
                                        <i class="fas fa-download"></i>
                                        <span>Export</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="transactions-grid">
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="createAccountModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCreateAccountForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
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

        <div class="flex-1 overflow-y-auto p-6">
            <form id="createAccountForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Account Type</label>
                    <div class="grid grid-cols-1 gap-3">
                        <label
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="accountMode" value="bank" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="accountMode" value="mobile_money" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="accountMode" value="gateway" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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

                <div id="providerContainer"></div>

                <div>
                    <label for="accountName" class="block text-sm font-semibold text-gray-700 mb-2">Account Name</label>
                    <input type="text" id="accountName" name="accountName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter a descriptive name" required>
                </div>

                <div>
                    <label for="accountNumber" class="block text-sm font-semibold text-gray-700 mb-2">Account
                        Number</label>
                    <input type="text" id="accountNumber" name="accountNumber"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter account number" required>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideCreateAccountForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="submitAccountForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Create
                    Account</button>
            </div>
        </div>
    </div>
</div>

<div id="editAccountModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEditAccountForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
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

        <div class="flex-1 overflow-y-auto p-6">
            <form id="editAccountForm" class="space-y-6">
                <input type="hidden" id="editAccountId">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Account Type</label>
                    <div class="grid grid-cols-1 gap-3">
                        <label
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="editAccountMode" value="bank" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="editAccountMode" value="mobile_money" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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
                            class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 account-type-option">
                            <input type="radio" name="editAccountMode" value="gateway" class="sr-only">
                            <div
                                class="w-5 h-5 border-2 border-gray-300 rounded-full flex items-center justify-center mr-3 radio-indicator">
                                <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
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

                <div id="editProviderContainer"></div>

                <div>
                    <label for="editAccountName" class="block text-sm font-semibold text-gray-700 mb-2">Account
                        Name</label>
                    <input type="text" id="editAccountName" name="editAccountName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter a descriptive name" required>
                </div>

                <div>
                    <label for="editAccountNumber" class="block text-sm font-semibold text-gray-700 mb-2">Account
                        Number</label>
                    <input type="text" id="editAccountNumber" name="editAccountNumber"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter account number" required>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideEditAccountForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="updateAccountForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Update
                    Account</button>
            </div>
        </div>
    </div>
</div>

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
                    <p class="text-sm text-gray-500 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <div class="mb-6">
                <p class="text-gray-700">Are you sure you want to delete this account? All associated data will be
                    permanently removed.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Cancel</button>
                <button id="confirmDeleteBtn"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">Delete
                    Account</button>
            </div>
        </div>
    </div>
</div>

<div id="messageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideMessageModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="messageIcon" class="w-12 h-12 rounded-xl flex items-center justify-center">
                    <i id="messageIconClass" class="text-xl"></i>
                </div>
                <div>
                    <h3 id="messageTitle" class="text-lg font-semibold text-gray-900"></h3>
                    <p id="messageText" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="hideMessageModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashAccounts.php';
    let accounts = [];
    let currentTransactions = [];
    let currentTab = 'accounts';
    let currentStatementAccountId = null;

    const COLUMNS_STORAGE_KEY = 'cash_accounts_columns';
    let visibleColumns = ['details', 'type', 'status', 'balance', 'actions'];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
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
            'bank': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-university mr-1"></i>Bank Account</span>',
            'mobile_money': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-mobile-alt mr-1"></i>Mobile Money</span>',
            'gateway': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-cogs mr-1"></i>Payment Gateway</span>'
        };
        return badges[type] || '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function getAccountIcon(type) {
        const icons = {
            'bank': 'fas fa-university text-blue-600',
            'mobile_money': 'fas fa-mobile-alt text-yellow-600',
            'gateway': 'fas fa-cogs text-green-600'
        };
        return icons[type] || 'fas fa-wallet text-gray-600';
    }

    function getStatusBadge(status) {
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        const statusClasses = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-gray-100 text-gray-800'
        };
        const className = statusClasses[status] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${className}">${statusText}</span>`;
    }

    function updateTabHeights() {
        const desktopNav = document.getElementById('desktop-nav');
        const accountsContainer = document.getElementById('accounts-container');
        const statementContainer = document.getElementById('statement-container');

        if (desktopNav && window.innerWidth >= 1024) {
            let activeContainer;
            if (currentTab === 'statement') {
                activeContainer = statementContainer;
            } else {
                activeContainer = accountsContainer;
            }

            if (activeContainer) {
                const containerHeight = activeContainer.offsetHeight;
                desktopNav.style.height = containerHeight + 'px';
            }
        } else if (desktopNav) {
            desktopNav.style.height = 'auto';
        }
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
        });

        document.getElementById('accounts-content').classList.add('hidden');
        document.getElementById('statement-content').classList.add('hidden');

        if (tabName === 'statement') {
            document.getElementById('statement-content').classList.remove('hidden');
            currentTab = 'statement';
        } else {
            document.getElementById('accounts-content').classList.remove('hidden');
            currentTab = tabName;
            renderFilteredAccounts();

            const tabId = `${tabName}-tab`;
            const activeTab = document.getElementById(tabId);
            if (activeTab) {
                activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            }

            const tabLabels = {
                'accounts': { label: 'All Accounts', icon: 'fas fa-wallet' },
                'bank': { label: 'Bank Accounts', icon: 'fas fa-university' },
                'mobile_money': { label: 'Mobile Money', icon: 'fas fa-mobile-alt' },
                'gateway': { label: 'Payment Gateways', icon: 'fas fa-cogs' }
            };
            const tabInfo = tabLabels[tabName] || tabLabels['accounts'];
            updateMobileTabLabel(tabInfo.label, tabInfo.icon);
        }

        setTimeout(updateTabHeights, 100);
    }

    function updateMobileTabLabel(label, icon) {
        const labelElement = document.getElementById('mobile-tab-label');
        const toggleButton = document.getElementById('mobile-tab-toggle');
        if (labelElement && toggleButton) {
            labelElement.textContent = label;
            const iconElement = toggleButton.querySelector('i');
            if (iconElement) {
                iconElement.className = `${icon} text-primary`;
            }
        }
    }

    function toggleMobileTabDropdown() {
        const dropdown = document.getElementById('mobile-tab-dropdown');
        const chevron = document.getElementById('mobile-tab-chevron');
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    function updateQuickStats() {
        const totalBalance = accounts.reduce((sum, acc) => sum + parseFloat(acc.balance || 0), 0);
        const activeAccounts = accounts.filter(acc => acc.status === 'active').length;
        const accountTypes = [...new Set(accounts.map(acc => acc.type))].length;
        const totalAccounts = accounts.length;

        document.getElementById('total-balance').textContent = `UGX ${formatCurrency(totalBalance)}`;
        document.getElementById('active-accounts').textContent = activeAccounts;
        document.getElementById('account-types').textContent = accountTypes;
        document.getElementById('total-accounts-count').textContent = totalAccounts;
    }

    async function fetchAccounts() {
        try {
            const response = await fetch(`${API_URL}?action=getCashAccounts`);
            const data = await response.json();

            if (data.success) {
                accounts = data.accounts || [];
                renderFilteredAccounts();
                updateQuickStats();
            } else {
                showMessage('error', 'Error', data.message || 'Failed to load accounts');
            }
        } catch (error) {
            console.error('Error loading accounts:', error);
            showMessage('error', 'Error', 'Failed to load accounts');
        }
    }

    function renderFilteredAccounts() {
        let filteredAccounts = accounts;

        if (currentTab !== 'accounts') {
            filteredAccounts = accounts.filter(acc => acc.type === currentTab);
        }

        const query = document.getElementById('searchAccounts').value.trim().toLowerCase();
        if (query) {
            filteredAccounts = filteredAccounts.filter(acc =>
                acc.name.toLowerCase().includes(query) ||
                acc.number.toLowerCase().includes(query) ||
                (acc.provider && acc.provider.toLowerCase().includes(query))
            );
        }

        renderAccountsGrid(filteredAccounts);
    }

    function renderAccountsGrid(list) {
        const grid = document.getElementById('accounts-grid');
        grid.innerHTML = '';

        if (list.length === 0) {
            grid.innerHTML = `
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-wallet text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No accounts found</h3>
                <p class="text-gray-500 mb-6">Create a new account or adjust your search filters</p>
                <button onclick="showCreateAccountForm()" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Create Account</button>
            </div>
        `;
            return;
        }

        const isDesktop = window.innerWidth >= 1024;

        if (isDesktop) {
            const tableHtml = `
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full" id="accounts-table">
                    <thead class="bg-user-accent border-b border-gray-200 sticky top-0">
                        <tr>
                            <th data-column="details" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Account Details</th>
                            <th data-column="type" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Account Type</th>
                            <th data-column="status" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th data-column="balance" class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Balance</th>
                            <th data-column="actions" class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${list.map((account, index) => {
                const maxDetailsLength = 30;
                let displayName = account.name;
                if (displayName.length > maxDetailsLength) {
                    displayName = displayName.substring(0, maxDetailsLength) + '...';
                }

                return `
                                <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
                                    <td data-column="details" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center ${account.type === 'bank' ? 'bg-blue-100' :
                        account.type === 'mobile_money' ? 'bg-yellow-100' : 'bg-green-100'
                    }">
                                                <i class="${getAccountIcon(account.type)} text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-gray-900 leading-tight" title="${account.name}">${displayName}</div>
                                                <div class="text-xs text-gray-500 mt-0.5 font-mono">${account.number}</div>
                                                ${account.provider ? `<div class="text-xs text-gray-400 mt-0.5">${account.provider}</div>` : ''}
                                            </div>
                                        </div>
                                    </td>
                                    <td data-column="type" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${getAccountTypeBadge(account.type)}
                                    </td>
                                    <td data-column="status" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        ${getStatusBadge(account.status)}
                                    </td>
                                    <td data-column="balance" class="px-3 py-2 text-right text-xs font-semibold text-gray-900 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        UGX ${formatCurrency(account.balance)}
                                    </td>
                                    <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="showStatement('${account.id}')" 
                                                class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                                                title="View Statement">
                                                <i class="fas fa-file-alt text-xs"></i>
                                            </button>
                                            <button onclick="showEditAccountForm('${account.id}')" 
                                                class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                                                title="Edit Account">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button onclick="toggleStatus('${account.id}', '${account.status === 'active' ? 'inactive' : 'active'}')" 
                                                class="w-6 h-6 rounded-lg ${account.status === 'active' ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-green-100 text-green-600 hover:bg-green-200'} transition-colors flex items-center justify-center" 
                                                title="${account.status === 'active' ? 'Deactivate' : 'Activate'} Account">
                                                <i class="fas ${account.status === 'active' ? 'fa-pause' : 'fa-play'} text-xs"></i>
                                            </button>
                                            <button onclick="showDeleteConfirm('${account.id}')" 
                                                class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                                title="Delete Account">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
            }).join('')}
                    </tbody>
                </table>
            </div>
        `;
            grid.innerHTML = tableHtml;
            applyColumnVisibility('accounts-table', visibleColumns);
        } else {
            const gridHtml = `
            <div class="grid grid-cols-1 gap-4">
                ${list.map(account => `
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center ${account.type === 'bank' ? 'bg-blue-100' :
                    account.type === 'mobile_money' ? 'bg-yellow-100' : 'bg-green-100'
                }">
                                    <i class="${getAccountIcon(account.type)}"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">${account.name}</h3>
                                    <p class="text-sm text-gray-500 font-mono">${account.number}</p>
                                    ${account.provider ? `<p class="text-xs text-gray-400 mt-1">${account.provider}</p>` : ''}
                                </div>
                            </div>
                            ${getStatusBadge(account.status)}
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Account Type:</span>
                                ${getAccountTypeBadge(account.type)}
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Balance:</span>
                                <span class="font-semibold text-lg text-gray-900">UGX ${formatCurrency(account.balance)}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                            <button onclick="showStatement('${account.id}')" class="flex-1 px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                                <i class="fas fa-file-alt mr-1"></i>Statement
                            </button>
                            <button onclick="showEditAccountForm('${account.id}')" class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="toggleStatus('${account.id}', '${account.status === 'active' ? 'inactive' : 'active'}')" class="flex-1 px-3 py-2 text-sm ${account.status === 'active' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200'} rounded-lg transition-colors font-medium">
                                <i class="fas ${account.status === 'active' ? 'fa-pause' : 'fa-play'} mr-1"></i>${account.status === 'active' ? 'Pause' : 'Activate'}
                            </button>
                            <button onclick="showDeleteConfirm('${account.id}')" class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                <i class="fas fa-trash-alt mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
            grid.innerHTML = gridHtml;
        }

        setTimeout(updateTabHeights, 100);
    }

    function renderTransactionsGrid(list) {
        const grid = document.getElementById('transactions-grid');
        grid.innerHTML = '';

        if (list.length === 0) {
            grid.innerHTML = `
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                <p class="text-gray-500">This account has no transaction history yet</p>
            </div>
        `;
            return;
        }

        const isDesktop = window.innerWidth >= 1024;

        if (isDesktop) {
            const tableHtml = `
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full">
                    <thead class="bg-user-accent border-b border-gray-200 sticky top-0">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Date & Time</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Transaction ID</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Description</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">User Account</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Amount</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${list.map((tx, index) => {
                const isCredit = tx.isCredit || tx.amount > 0;
                const amountClass = isCredit ? 'text-green-600' : 'text-red-600';
                const sign = isCredit ? '+' : '-';

                return `
                                <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
                                    <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        <div class="font-medium text-gray-900">${formatDateTime(tx.date)}</div>
                                    </td>
                                    <td class="px-3 py-2 text-xs font-mono text-gray-900 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${tx.id}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        ${tx.reason}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-700 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${tx.userAccount || 'N/A'}
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-bold ${amountClass} ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        ${sign} UGX ${formatCurrency(Math.abs(tx.amount))}
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-semibold text-gray-900 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        UGX ${formatCurrency(tx.balance)}
                                    </td>
                                </tr>
                            `;
            }).join('')}
                    </tbody>
                </table>
            </div>
        `;
            grid.innerHTML = tableHtml;
        } else {
            const gridHtml = `
            <div class="grid grid-cols-1 gap-4">
                ${list.map(tx => {
                const isCredit = tx.isCredit || tx.amount > 0;
                const amountClass = isCredit ? 'text-green-600' : 'text-red-600';
                const sign = isCredit ? '+' : '-';

                return `
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <div class="font-mono text-sm font-semibold text-gray-900">${tx.id}</div>
                                    <div class="text-xs text-gray-500 mt-1">${formatDateTime(tx.date)}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold ${amountClass}">${sign} UGX ${formatCurrency(Math.abs(tx.amount))}</div>
                                    <div class="text-xs text-gray-500 mt-1">Balance: UGX ${formatCurrency(tx.balance)}</div>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Description:</span>
                                    <span class="font-medium text-gray-900">${tx.reason}</span>
                                </div>
                                
                                ${tx.userAccount ? `
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">User Account:</span>
                                    <span class="font-medium text-gray-900">${tx.userAccount}</span>
                                </div>` : ''}
                            </div>
                        </div>
                    `;
            }).join('')}
            </div>
        `;
            grid.innerHTML = gridHtml;
        }

        setTimeout(updateTabHeights, 100);
    }

    function loadColumnVisibility() {
        const savedColumns = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (savedColumns) {
            visibleColumns = JSON.parse(savedColumns);
        }
        updateColumnCheckboxes();
    }

    function saveColumnVisibility() {
        localStorage.setItem(COLUMNS_STORAGE_KEY, JSON.stringify(visibleColumns));
    }

    function updateColumnCheckboxes() {
        document.querySelectorAll('.column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleColumns.includes(column);
        });
    }

    function applyColumnVisibility(tableId, visibleCols) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const headers = table.querySelectorAll('thead th[data-column]');
        headers.forEach(header => {
            const column = header.getAttribute('data-column');
            if (visibleCols.includes(column)) {
                header.style.display = '';
            } else {
                header.style.display = 'none';
            }
        });

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td[data-column]');
            cells.forEach(cell => {
                const column = cell.getAttribute('data-column');
                if (visibleCols.includes(column)) {
                    cell.style.display = '';
                } else {
                    cell.style.display = 'none';
                }
            });
        });
    }

    function toggleColumnSelector() {
        const selector = document.getElementById('columnSelector');
        selector.classList.toggle('hidden');
    }

    async function toggleStatus(id, newStatus) {
        try {
            const response = await fetch(`${API_URL}?action=updateCashAccountStatus`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: newStatus })
            });
            const data = await response.json();

            if (data.success) {
                const account = accounts.find(a => a.id === id);
                if (account) {
                    account.status = newStatus;
                    renderFilteredAccounts();
                    updateQuickStats();
                }
                showMessage('success', 'Status Updated', `Account status changed to ${newStatus}`);
            } else {
                showMessage('error', 'Error', data.message || 'Failed to update account status');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showMessage('error', 'Error', 'Failed to update account status');
        }
    }

    function showStatement(id) {
        const account = accounts.find(a => a.id === id);
        if (!account) return;

        currentStatementAccountId = id;
        document.getElementById('statement-account-info').textContent = `${account.name} - Transaction History`;
        document.getElementById('statement-balance').textContent = `UGX ${formatCurrency(account.balance)}`;

        // Generate dummy transactions for demo
        const dummyTransactions = generateDummyTransactions(account);
        currentTransactions = dummyTransactions;
        renderTransactionsGrid(dummyTransactions);

        switchTab('statement');
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
        switchTab('accounts');
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
            showMessage('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=createCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type, name, number, provider })
            });
            const data = await response.json();

            if (data.success) {
                hideCreateAccountForm();
                await fetchAccounts();
                showMessage('success', 'Account Created', 'Cash account created successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to create account');
            }
        } catch (error) {
            console.error('Error creating account:', error);
            showMessage('error', 'Error', 'Failed to create account');
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
            showMessage('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=updateCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, type, name, number, provider })
            });
            const data = await response.json();

            if (data.success) {
                hideEditAccountForm();
                await fetchAccounts();
                showMessage('success', 'Account Updated', 'Cash account updated successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to update account');
            }
        } catch (error) {
            console.error('Error updating account:', error);
            showMessage('error', 'Error', 'Failed to update account');
        }
    }

    async function deleteAccount() {
        const id = document.getElementById('confirmDeleteBtn').getAttribute('data-account-id');

        try {
            const response = await fetch(`${API_URL}?action=deleteCashAccount`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await response.json();

            if (data.success) {
                hideDeleteConfirm();
                await fetchAccounts();
                showMessage('success', 'Account Deleted', 'Cash account deleted successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to delete account');
            }
        } catch (error) {
            console.error('Error deleting account:', error);
            showMessage('error', 'Error', 'Failed to delete account');
        }
    }

    function showCreateAccountForm() {
        const modal = document.getElementById('createAccountModal');
        const form = document.getElementById('createAccountForm');
        form.reset();
        document.getElementById('providerContainer').innerHTML = '';

        // Reset radio button styles
        document.querySelectorAll('.account-type-option').forEach(option => {
            option.classList.remove('border-primary', 'bg-primary/5');
            option.classList.add('border-gray-200');
            const indicator = option.querySelector('.radio-indicator');
            const dot = indicator.querySelector('div');
            indicator.classList.remove('border-primary', 'bg-primary');
            indicator.classList.add('border-gray-300');
            dot.classList.remove('opacity-100');
            dot.classList.add('opacity-0');
        });

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCreateAccountForm() {
        document.getElementById('createAccountModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showEditAccountForm(id) {
        const account = accounts.find(a => a.id === id);
        if (!account) return;

        document.getElementById('editAccountId').value = account.id;
        document.getElementById('editAccountName').value = account.name;
        document.getElementById('editAccountNumber').value = account.number;

        // Set account type radio button
        const typeRadio = document.querySelector(`input[name="editAccountMode"][value="${account.type}"]`);
        if (typeRadio) {
            typeRadio.checked = true;
            // Update visual state
            const option = typeRadio.closest('.account-type-option');
            document.querySelectorAll('#editAccountModal .account-type-option').forEach(opt => {
                opt.classList.remove('border-primary', 'bg-primary/5');
                opt.classList.add('border-gray-200');
                const indicator = opt.querySelector('.radio-indicator');
                const dot = indicator.querySelector('div');
                indicator.classList.remove('border-primary', 'bg-primary');
                indicator.classList.add('border-gray-300');
                dot.classList.remove('opacity-100');
                dot.classList.add('opacity-0');
            });

            option.classList.remove('border-gray-200');
            option.classList.add('border-primary', 'bg-primary/5');
            const indicator = option.querySelector('.radio-indicator');
            const dot = indicator.querySelector('div');
            indicator.classList.remove('border-gray-300');
            indicator.classList.add('border-primary', 'bg-primary');
            dot.classList.remove('opacity-0');
            dot.classList.add('opacity-100');
        }

        updateProviderField('edit', account.type, account.provider);

        const modal = document.getElementById('editAccountModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditAccountForm() {
        document.getElementById('editAccountModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showDeleteConfirm(id) {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('hidden');
        document.getElementById('confirmDeleteBtn').setAttribute('data-account-id', id);
        document.body.style.overflow = 'hidden';
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showMessage(type, title, message) {
        const modal = document.getElementById('messageModal');
        const icon = document.getElementById('messageIcon');
        const iconClass = document.getElementById('messageIconClass');
        const titleEl = document.getElementById('messageTitle');
        const textEl = document.getElementById('messageText');

        if (type === 'success') {
            icon.className = 'w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-check text-green-600 text-xl';
        } else if (type === 'error') {
            icon.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-exclamation-triangle text-red-600 text-xl';
        } else {
            icon.className = 'w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-info-circle text-blue-600 text-xl';
        }

        titleEl.textContent = title;
        textEl.textContent = message;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function filterAccounts() {
        renderFilteredAccounts();
    }

    function filterTransactions() {
        const query = document.getElementById('searchTransactions').value.toLowerCase();
        if (!query) {
            renderTransactionsGrid(currentTransactions);
            return;
        }

        const filtered = currentTransactions.filter(tx =>
            tx.id.toLowerCase().includes(query) ||
            tx.reason.toLowerCase().includes(query) ||
            (tx.userAccount && tx.userAccount.toLowerCase().includes(query))
        );
        renderTransactionsGrid(filtered);
    }

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

    // Event Listeners
    document.addEventListener('click', function (event) {
        const columnSelector = document.getElementById('columnSelector');
        const columnBtn = document.getElementById('viewColumnsBtn');
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (columnSelector && columnBtn && !columnSelector.contains(event.target) && !columnBtn.contains(event.target)) {
            columnSelector.classList.add('hidden');
        }

        if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
            mobileDropdown.classList.add('hidden');
            document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleColumns.includes(column)) {
                    visibleColumns.push(column);
                }
            } else {
                if (visibleColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleColumns = visibleColumns.filter(col => col !== column);
            }

            applyColumnVisibility('accounts-table', visibleColumns);
            saveColumnVisibility();
        }

        if (event.target.name === 'accountMode' || event.target.name === 'editAccountMode') {
            const form = event.target.name === 'accountMode' ? 'create' : 'edit';
            updateProviderField(form, event.target.value);

            // Update visual state
            const container = event.target.closest('#createAccountModal, #editAccountModal');
            container.querySelectorAll('.account-type-option').forEach(option => {
                option.classList.remove('border-primary', 'bg-primary/5');
                option.classList.add('border-gray-200');
                const indicator = option.querySelector('.radio-indicator');
                const dot = indicator.querySelector('div');
                indicator.classList.remove('border-primary', 'bg-primary');
                indicator.classList.add('border-gray-300');
                dot.classList.remove('opacity-100');
                dot.classList.add('opacity-0');
            });

            const selectedOption = event.target.closest('.account-type-option');
            selectedOption.classList.remove('border-gray-200');
            selectedOption.classList.add('border-primary', 'bg-primary/5');
            const indicator = selectedOption.querySelector('.radio-indicator');
            const dot = indicator.querySelector('div');
            indicator.classList.remove('border-gray-300');
            indicator.classList.add('border-primary', 'bg-primary');
            dot.classList.remove('opacity-0');
            dot.classList.add('opacity-100');
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        loadColumnVisibility();
        await fetchAccounts();
        switchTab('accounts');

        document.getElementById('create-account-btn').addEventListener('click', showCreateAccountForm);
        document.getElementById('back-to-accounts').addEventListener('click', backToAccounts);
        document.getElementById('submitAccountForm').addEventListener('click', createAccount);
        document.getElementById('updateAccountForm').addEventListener('click', updateAccount);
        document.getElementById('confirmDeleteBtn').addEventListener('click', deleteAccount);
        document.getElementById('searchAccounts').addEventListener('input', filterAccounts);
        document.getElementById('searchTransactions').addEventListener('input', filterTransactions);
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchTab(tab);
                toggleMobileTabDropdown();
            });
        });

        document.getElementById('exportStatement').addEventListener('click', () => {
            if (currentStatementAccountId && currentTransactions.length > 0) {
                const account = accounts.find(a => a.id === currentStatementAccountId);
                if (account) {
                    exportToCSV(account, currentTransactions);
                } else {
                    showMessage('error', 'Error', 'Account not found');
                }
            } else {
                showMessage('error', 'Error', 'No transactions to export');
            }
        });

        window.addEventListener('resize', () => {
            updateTabHeights();
            renderFilteredAccounts();
            if (currentTransactions.length > 0) {
                renderTransactionsGrid(currentTransactions);
            }
        });

        setTimeout(updateTabHeights, 500);
    });

    window.switchTab = switchTab;
    window.showCreateAccountForm = showCreateAccountForm;
    window.hideCreateAccountForm = hideCreateAccountForm;
    window.showEditAccountForm = showEditAccountForm;
    window.hideEditAccountForm = hideEditAccountForm;
    window.showDeleteConfirm = showDeleteConfirm;
    window.hideDeleteConfirm = hideDeleteConfirm;
    window.hideMessageModal = hideMessageModal;
    window.toggleStatus = toggleStatus;
    window.showStatement = showStatement;
    window.toggleColumnSelector = toggleColumnSelector;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>