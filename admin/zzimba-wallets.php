<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Wallets';
$activeNav = 'zzimba-wallets';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">User Wallets</p>
                            <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="user-wallets-count">0</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="user-wallets-total">UGX
                                0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Vendor Wallets</p>
                            <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="vendor-wallets-count">0
                            </p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap" id="vendor-wallets-total">
                                UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-cyan-50 to-cyan-100 rounded-xl p-4 border border-cyan-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-cyan-600 uppercase tracking-wide">Platform Wallets</p>
                            <p class="text-lg font-bold text-cyan-900 whitespace-nowrap" id="platform-wallets-count">0
                            </p>
                            <p class="text-sm font-medium text-cyan-700 whitespace-nowrap" id="platform-wallets-total">
                                UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-cyan-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-cyan-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Balance</p>
                            <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="total-balance">UGX 0.00</p>
                            <p class="text-sm font-medium text-blue-700 whitespace-nowrap" id="total-wallets-count">0
                                Wallets</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-coins text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button id="wallets-tab"
                        class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-primary text-primary"
                        onclick="switchTab('wallets')">
                        <i class="fas fa-wallet mr-2"></i>
                        Wallet Management
                    </button>
                    <button id="platform-accounts-tab"
                        class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                        onclick="switchTab('platform-accounts')">
                        <i class="fas fa-cogs mr-2"></i>
                        Platform Account Settings
                    </button>
                </nav>
            </div>
        </div>

        <div id="wallets-content" class="tab-content block">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Search Input on Left -->
                        <div class="w-full lg:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchWallets"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Search wallets...">
                            </div>
                        </div>

                        <!-- Empty flexible space (only visible on large screens) -->
                        <div class="hidden lg:block lg:flex-1"></div>

                        <!-- Type selector and button on Right -->
                        <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative">
                                <button id="viewColumnsBtn" onclick="toggleColumnSelector()"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
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
                                            <span class="text-sm text-gray-700">Wallet Details</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="owner" checked>
                                            <span class="text-sm text-gray-700">Owner Type</span>
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

                            <select id="filterWallets"
                                class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium w-full sm:w-auto">
                                <option value="all">All Types</option>
                                <option value="USER">User Wallets</option>
                                <option value="VENDOR">Vendor Wallets</option>
                                <option value="PLATFORM">Platform Wallets</option>
                            </select>

                            <button id="create-wallet-btn"
                                class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                <i class="fas fa-plus"></i>
                                <span>Create</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full" id="wallets-table">
                        <thead class="bg-user-accent border-b border-gray-200">
                            <tr>
                                <th data-column="details"
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Wallet Details</th>
                                <th data-column="owner"
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Owner Type</th>
                                <th data-column="status" id="statusHeader"
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                    Status
                                    <i id="statusSortIcon" class="fas fa-sort text-gray-400 ml-1"></i>
                                </th>
                                <th data-column="balance"
                                    class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Balance</th>
                                <th data-column="actions"
                                    class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="wallets-table-body" class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>

                <div class="lg:hidden p-4 space-y-4" id="wallets-mobile">
                </div>

                <div id="empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-wallet text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No wallets found</h3>
                    <p class="text-gray-500 mb-6">Create a new wallet or adjust your search filters</p>
                    <button onclick="showCreateWalletForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                        Create Wallet
                    </button>
                </div>
            </div>
        </div>

        <div id="platform-accounts-content" class="tab-content hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                        <!-- Left: Select -->
                        <div class="w-full sm:w-auto">
                            <select id="filterPlatformAccounts"
                                class="w-full sm:w-auto px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium">
                                <option value="">Select Platform Account</option>
                            </select>
                        </div>

                        <!-- Right: Button -->
                        <div class="w-full sm:w-auto flex justify-end">
                            <button id="create-platform-setting-btn"
                                class="w-full sm:w-auto px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center sm:justify-start gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-plus"></i>
                                <span>Add Setting</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full" id="platform-settings-table">
                        <thead class="bg-user-accent border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Platform Account</th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Type</th>
                                <th
                                    class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Created</th>
                                <th
                                    class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="platform-settings-table-body" class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>

                <div class="lg:hidden p-4 space-y-4" id="platform-settings-mobile">
                </div>

                <div id="platform-settings-empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No platform settings found</h3>
                    <p class="text-gray-500 mb-6">Add a new platform account setting to get started</p>
                    <button onclick="showCreatePlatformSettingForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                        Add Setting
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Wallet Statement Modal -->
<div id="walletStatementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl relative z-10 overflow-hidden max-h-[100vh] flex flex-col">
        <div class="p-6 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-receipt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Detailed Wallet Statement</h3>
                        <p class="text-sm text-gray-500" id="statementWalletName"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <button id="viewStatementColumnsBtn" onclick="toggleStatementColumnSelector()"
                            class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
                            <i class="fas fa-eye text-xs"></i>
                            <span>View</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <div id="statementColumnSelector"
                            class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-48">
                            <div class="p-3 border-b border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-900">Show Columns</h4>
                                <p class="text-xs text-gray-500 mt-1">Select at least 3 columns</p>
                            </div>
                            <div class="p-2 space-y-1" id="statementColumnCheckboxes">
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="datetime" checked>
                                    <span class="text-sm text-gray-700">Date/Time</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="entryid" checked>
                                    <span class="text-sm text-gray-700">Entry ID</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="description" checked>
                                    <span class="text-sm text-gray-700">Description</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="debit" checked>
                                    <span class="text-sm text-gray-700">Debit</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="credit" checked>
                                    <span class="text-sm text-gray-700">Credit</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="balance" checked>
                                    <span class="text-sm text-gray-700">Balance</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox"
                                        class="statement-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                        data-column="related" checked>
                                    <span class="text-sm text-gray-700">Related Entries</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <select id="statementDateFilter"
                        class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm">
                        <option value="all">All transactions</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="180">Last 6 months</option>
                        <option value="365">Last year</option>
                    </select>
                    <button onclick="hideWalletStatementModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 overflow-hidden">
            <div id="statementLoading" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>

            <div id="statementTable" class="hidden lg:block overflow-auto flex-1 max-h-[calc(100vh-200px)]">
                <table class="w-full" id="statementTableElement">
                    <thead class="bg-white border-b border-gray-200 sticky top-0">
                        <tr>
                            <th data-column="datetime"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Date/Time</th>
                            <th data-column="entryid"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Entry ID
                            </th>
                            <th data-column="description"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Description</th>
                            <th data-column="debit"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Debit</th>
                            <th data-column="credit"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Credit</th>
                            <th data-column="balance"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Balance</th>
                            <th data-column="related"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Related Entries</th>
                        </tr>
                    </thead>
                    <tbody id="statementTableBody" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>

            <div id="statementMobile"
                class="hidden block lg:hidden p-4 space-y-4 overflow-auto flex-1 max-h-[calc(90vh-200px)]">

                <div id="statementEmpty" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                    <p class="text-gray-500">No transactions available for the selected period</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Wallet Modal -->
<div id="createWalletOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideCreateWalletForm()">
    </div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Create Platform Wallet</h3>
                </div>
                <button onclick="hideCreateWalletForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="createWalletForm" class="space-y-6" onsubmit="return false;">
                    <div>
                        <label for="walletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet
                            Name</label>
                        <input type="text" id="walletName" name="walletName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter a descriptive name" required>
                        <p class="mt-2 text-sm text-gray-500">This wallet will be created as a platform wallet.</p>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideCreateWalletForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="submitWalletForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Create Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Wallet Modal -->
<div id="editWalletOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideEditWalletForm()">
    </div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Wallet</h3>
                </div>
                <button onclick="hideEditWalletForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="editWalletForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editWalletId">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Owner Type</label>
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700">
                            <span id="editWalletOwnerTypeDisplay" class="font-medium"></span>
                        </div>
                        <input type="hidden" id="editWalletOwnerType">
                    </div>

                    <div>
                        <label for="editWalletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet
                            Name</label>
                        <input type="text" id="editWalletName" name="editWalletName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter wallet name" required>
                    </div>

                    <div>
                        <label for="editWalletStatus"
                            class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="editWalletStatus" value="active" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Active</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="editWalletStatus" value="inactive" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Inactive</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="editWalletStatus" value="suspended" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Suspended</div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideEditWalletForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="updateWalletForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Update Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Platform Setting Modal -->
<div id="createPlatformSettingOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        onclick="hideCreatePlatformSettingForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Add Platform Setting</h3>
                </div>
                <button onclick="hideCreatePlatformSettingForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="createPlatformSettingForm" class="space-y-6" onsubmit="return false;">
                    <div>
                        <label for="platformAccountSelect"
                            class="block text-sm font-semibold text-gray-700 mb-2">Platform Account</label>
                        <select id="platformAccountSelect" name="platformAccountSelect"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Platform Account</option>
                        </select>
                    </div>

                    <div>
                        <label for="settingType" class="block text-sm font-semibold text-gray-700 mb-2">Setting
                            Type</label>
                        <select id="settingType" name="settingType"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Type</option>
                            <option value="withholding">Withholding</option>
                            <option value="services">Services</option>
                            <option value="operations">Operations</option>
                            <option value="communications">Communications</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideCreatePlatformSettingForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="submitPlatformSettingForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Add Setting
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Platform Setting Modal -->
<div id="editPlatformSettingOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        onclick="hideEditPlatformSettingForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Platform Setting</h3>
                </div>
                <button onclick="hideEditPlatformSettingForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="editPlatformSettingForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editSettingId">
                    <input type="hidden" id="editSettingAccountId">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Platform Account</label>
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700">
                            <span id="editSettingAccountDisplay" class="font-medium"></span>
                        </div>
                    </div>

                    <div>
                        <label for="editSettingType" class="block text-sm font-semibold text-gray-700 mb-2">Setting
                            Type</label>
                        <select id="editSettingType" name="editSettingType"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Type</option>
                            <option value="withholding">Withholding</option>
                            <option value="services">Services</option>
                            <option value="operations">Operations</option>
                            <option value="communications">Communications</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideEditPlatformSettingForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="updatePlatformSettingForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Update Setting
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
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
            <p class="text-gray-600 mb-6" id="deleteMessage">Are you sure you want to delete this item?</p>
            <div class="flex gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmDeleteBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageZzimbaWallets.php';
    let wallets = [];
    let platformSettings = [];
    let statusSortAsc = true;
    let currentTab = 'wallets';
    let currentWalletStatement = [];
    let currentStatementWalletId = null;

    // Column visibility storage keys
    const WALLETS_COLUMNS_STORAGE_KEY = 'zzimba_wallets_columns';
    const STATEMENT_COLUMNS_STORAGE_KEY = 'zzimba_statement_columns';

    // Default visible columns
    let visibleWalletColumns = ['details', 'owner', 'status', 'balance', 'actions'];
    let visibleStatementColumns = ['datetime', 'entryid', 'description', 'debit', 'credit', 'balance', 'related'];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getOwnerTypeBadge(type) {
        const badges = {
            'USER': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-user mr-2"></i>User</span>',
            'VENDOR': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-store mr-2"></i>Vendor</span>',
            'PLATFORM': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800"><i class="fas fa-building mr-2"></i>Platform</span>'
        };
        return badges[type] || '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function getStatusBadge(status) {
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        const statusClasses = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-gray-100 text-gray-800',
            'suspended': 'bg-red-100 text-red-800'
        };
        const className = statusClasses[status] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${className}">${statusText}</span>`;
    }

    function getTypeBadge(type) {
        const typeText = type.charAt(0).toUpperCase() + type.slice(1);
        const typeClasses = {
            'withholding': 'bg-yellow-100 text-yellow-800',
            'services': 'bg-blue-100 text-blue-800',
            'operations': 'bg-green-100 text-green-800',
            'communications': 'bg-indigo-100 text-indigo-800'
        };
        const className = typeClasses[type] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${className}">${typeText}</span>`;
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-b-primary', 'text-primary');
            btn.classList.add('border-b-transparent', 'text-gray-500');
        });
        const activeTab = document.getElementById(`${tabName}-tab`);
        activeTab.classList.remove('border-b-transparent', 'text-gray-500');
        activeTab.classList.add('border-b-primary', 'text-primary');

        document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
        document.getElementById(`${tabName}-content`).classList.remove('hidden');

        currentTab = tabName;

        if (tabName === 'wallets') {
            fetchWallets();
        } else if (tabName === 'platform-accounts') {
            fetchPlatformSettings();
            loadPlatformAccountsDropdown();
        }
    }

    function updateQuickStats() {
        const userWallets = wallets.filter(w => w.owner_type === 'USER');
        const vendorWallets = wallets.filter(w => w.owner_type === 'VENDOR');
        const platformWallets = wallets.filter(w => w.owner_type === 'PLATFORM');

        const userTotal = userWallets.reduce((sum, w) => sum + parseFloat(w.current_balance || 0), 0);
        const vendorTotal = vendorWallets.reduce((sum, w) => sum + parseFloat(w.current_balance || 0), 0);
        const platformTotal = platformWallets.reduce((sum, w) => sum + parseFloat(w.current_balance || 0), 0);
        const totalBalance = userTotal + vendorTotal + platformTotal;

        document.getElementById('user-wallets-count').textContent = userWallets.length;
        document.getElementById('user-wallets-total').textContent = `UGX ${formatCurrency(userTotal)}`;

        document.getElementById('vendor-wallets-count').textContent = vendorWallets.length;
        document.getElementById('vendor-wallets-total').textContent = `UGX ${formatCurrency(vendorTotal)}`;

        document.getElementById('platform-wallets-count').textContent = platformWallets.length;
        document.getElementById('platform-wallets-total').textContent = `UGX ${formatCurrency(platformTotal)}`;

        document.getElementById('total-balance').textContent = `UGX ${formatCurrency(totalBalance)}`;
        document.getElementById('total-wallets-count').textContent = `${wallets.length} Wallets`;
    }

    async function fetchWallets() {
        try {
            const res = await fetch(`${API_URL}?action=getZzimbaWallets`);
            const data = await res.json();
            if (data.success) {
                wallets = data.wallets || [];
                renderWalletsTable(wallets);
                updateQuickStats();
                adjustTableFontSize();
            }
        } catch (err) {
            console.error('Error fetching wallets:', err);
        }
    }

    async function fetchPlatformSettings(accountId = '') {
        try {
            const body = { operation: 'list' };
            if (accountId) body.platform_account_id = accountId;

            const res = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) {
                platformSettings = data.settings || [];
                renderPlatformSettingsTable(platformSettings);
                adjustTableFontSize();
            }
        } catch (err) {
            console.error('Error fetching platform settings:', err);
        }
    }

    function loadPlatformAccountsDropdown() {
        const platformWallets = wallets.filter(w => w.owner_type === 'PLATFORM');
        const selects = ['filterPlatformAccounts', 'platformAccountSelect'];

        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const firstOption = select.querySelector('option');
                select.innerHTML = '';
                if (firstOption) select.appendChild(firstOption);

                platformWallets.forEach(wallet => {
                    const option = document.createElement('option');
                    option.value = wallet.wallet_number;
                    option.textContent = wallet.wallet_name;
                    select.appendChild(option);
                });
            }
        });
    }

    function renderWalletsTable(list) {
        const tbody = document.getElementById('wallets-table-body');
        const mobile = document.getElementById('wallets-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach((wallet, index) => {
            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;

            const maxDetailsLength = 30;
            let displayName = wallet.wallet_name;
            if (displayName.length > maxDetailsLength) {
                displayName = displayName.substring(0, maxDetailsLength) + '...';
            }

            tr.innerHTML = `
                <td data-column="details" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center ${wallet.owner_type === 'USER' ? 'bg-green-100' :
                    wallet.owner_type === 'VENDOR' ? 'bg-purple-100' : 'bg-cyan-100'
                }">
                            <i class="${wallet.owner_type === 'USER' ? 'fas fa-user text-green-600' :
                    wallet.owner_type === 'VENDOR' ? 'fas fa-store text-purple-600' : 'fas fa-building text-cyan-600'
                } text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-900 leading-tight" title="${wallet.wallet_name}">${displayName}</div>
                            <div class="text-xs text-gray-500 mt-0.5 font-mono">${wallet.wallet_number}</div>
                        </div>
                    </div>
                </td>
                <td data-column="owner" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    ${getOwnerTypeBadge(wallet.owner_type)}
                </td>
                <td data-column="status" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    ${getStatusBadge(wallet.status)}
                </td>
                <td data-column="balance" class="px-3 py-2 text-right text-xs font-semibold text-gray-900 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    UGX ${formatCurrency(wallet.current_balance)}
                </td>
                <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="showWalletStatement('${wallet.wallet_number}', '${wallet.wallet_name}')" 
                            class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                            title="View Statement">
                            <i class="fas fa-receipt text-xs"></i>
                        </button>
                        <button onclick="showEditWalletForm('${wallet.wallet_number}')" 
                            class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                            title="Edit Wallet">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        ${wallet.owner_type === 'PLATFORM' ? `
                            <button onclick="showDeleteConfirm('wallet', '${wallet.wallet_number}')" 
                                class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                title="Delete Wallet">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>`;
            tbody.appendChild(tr);

            const card = document.createElement('div');
            card.className = 'bg-gray-50 rounded-xl p-4 border border-gray-100';
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center ${wallet.owner_type === 'USER' ? 'bg-green-100' :
                    wallet.owner_type === 'VENDOR' ? 'bg-purple-100' : 'bg-cyan-100'
                }">
                            <i class="${wallet.owner_type === 'USER' ? 'fas fa-user text-green-600' :
                    wallet.owner_type === 'VENDOR' ? 'fas fa-store text-purple-600' : 'fas fa-building text-cyan-600'
                }"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate" title="${wallet.wallet_name}">${wallet.wallet_name}</div>
                            <div class="text-xs text-gray-500 font-mono">${wallet.wallet_number}</div>
                        </div>
                    </div>
                    ${getOwnerTypeBadge(wallet.owner_type)}
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Status</span>
                        <div class="mt-1">${getStatusBadge(wallet.status)}</div>
                    </div>
                    <div class="text-right">
                        <span class="text-gray-500 uppercase tracking-wide">Balance</span>
                        <div class="font-semibold text-gray-900 mt-1">UGX ${formatCurrency(wallet.current_balance)}</div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button onclick="showWalletStatement('${wallet.wallet_number}', '${wallet.wallet_name}')" 
                        class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                        <i class="fas fa-receipt mr-1"></i>Statement
                    </button>
                    <button onclick="showEditWalletForm('${wallet.wallet_number}')" 
                        class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    ${wallet.owner_type === 'PLATFORM' ? `
                        <button onclick="showDeleteConfirm('wallet', '${wallet.wallet_number}')" 
                            class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                            <i class="fas fa-trash-alt mr-1"></i>Delete
                        </button>
                    ` : ''}
                </div>`;
            mobile.appendChild(card);
        });

        // Apply column visibility
        applyColumnVisibility('wallets-table', visibleWalletColumns);
    }

    function renderPlatformSettingsTable(list) {
        const tbody = document.getElementById('platform-settings-table-body');
        const mobile = document.getElementById('platform-settings-mobile');
        const emptyState = document.getElementById('platform-settings-empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach((setting, index) => {
            const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
            const walletName = wallet ? wallet.wallet_name : setting.wallet_number;

            const maxDetailsLength = 25;
            let displayName = walletName;
            if (displayName.length > maxDetailsLength) {
                displayName = displayName.substring(0, maxDetailsLength) + '...';
            }

            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;
            tr.innerHTML = `
                <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center bg-cyan-100">
                            <i class="fas fa-building text-cyan-600 text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-900 leading-tight" title="${walletName}">${displayName}</div>
                            <div class="text-xs text-gray-500 mt-0.5 font-mono">${setting.platform_account_id}</div>
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    ${getTypeBadge(setting.type)}
                </td>
                <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    ${new Date(setting.created_at).toLocaleDateString()}
                </td>
                <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="showEditPlatformSettingForm(${setting.id})" 
                            class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                            title="Edit Setting">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="showDeleteConfirm('setting', ${setting.id})" 
                            class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                            title="Delete Setting">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>`;
            tbody.appendChild(tr);

            const card = document.createElement('div');
            card.className = 'bg-gray-50 rounded-xl p-4 border border-gray-100';
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-cyan-100">
                            <i class="fas fa-building text-cyan-600"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate" title="${walletName}">${walletName}</div>
                            <div class="text-xs text-gray-500 font-mono">${setting.platform_account_id}</div>
                        </div>
                    </div>
                    ${getTypeBadge(setting.type)}
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Created</span>
                        <div class="font-semibold text-gray-900 mt-1">${new Date(setting.created_at).toLocaleDateString()}</div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button onclick="showEditPlatformSettingForm(${setting.id})" 
                        class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button onclick="showDeleteConfirm('setting', ${setting.id})" 
                        class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                        <i class="fas fa-trash-alt mr-1"></i>Delete
                    </button>
                </div>`;
            mobile.appendChild(card);
        });
    }

    // Column visibility functions
    function loadColumnVisibility() {
        const savedWalletColumns = localStorage.getItem(WALLETS_COLUMNS_STORAGE_KEY);
        const savedStatementColumns = localStorage.getItem(STATEMENT_COLUMNS_STORAGE_KEY);

        if (savedWalletColumns) {
            visibleWalletColumns = JSON.parse(savedWalletColumns);
        }

        if (savedStatementColumns) {
            visibleStatementColumns = JSON.parse(savedStatementColumns);
        }

        updateColumnCheckboxes();
    }

    function saveColumnVisibility() {
        localStorage.setItem(WALLETS_COLUMNS_STORAGE_KEY, JSON.stringify(visibleWalletColumns));
        localStorage.setItem(STATEMENT_COLUMNS_STORAGE_KEY, JSON.stringify(visibleStatementColumns));
    }

    function updateColumnCheckboxes() {
        // Update wallet columns checkboxes
        document.querySelectorAll('.column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleWalletColumns.includes(column);
        });

        // Update statement columns checkboxes
        document.querySelectorAll('.statement-column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleStatementColumns.includes(column);
        });
    }

    function applyColumnVisibility(tableId, visibleColumns) {
        const table = document.getElementById(tableId);
        if (!table) return;

        // Show/hide header columns
        const headers = table.querySelectorAll('thead th[data-column]');
        headers.forEach(header => {
            const column = header.getAttribute('data-column');
            if (visibleColumns.includes(column)) {
                header.style.display = '';
            } else {
                header.style.display = 'none';
            }
        });

        // Show/hide body columns
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td[data-column]');
            cells.forEach(cell => {
                const column = cell.getAttribute('data-column');
                if (visibleColumns.includes(column)) {
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

    function toggleStatementColumnSelector() {
        const selector = document.getElementById('statementColumnSelector');
        selector.classList.toggle('hidden');
    }

    // Close column selectors when clicking outside
    document.addEventListener('click', function (event) {
        const walletSelector = document.getElementById('columnSelector');
        const walletBtn = document.getElementById('viewColumnsBtn');
        const statementSelector = document.getElementById('statementColumnSelector');
        const statementBtn = document.getElementById('viewStatementColumnsBtn');

        if (!walletSelector.contains(event.target) && !walletBtn.contains(event.target)) {
            walletSelector.classList.add('hidden');
        }

        if (!statementSelector.contains(event.target) && !statementBtn.contains(event.target)) {
            statementSelector.classList.add('hidden');
        }
    });

    // Handle column checkbox changes
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleWalletColumns.includes(column)) {
                    visibleWalletColumns.push(column);
                }
            } else {
                // Prevent unchecking if it would result in less than 3 columns
                if (visibleWalletColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleWalletColumns = visibleWalletColumns.filter(col => col !== column);
            }

            applyColumnVisibility('wallets-table', visibleWalletColumns);
            saveColumnVisibility();
            adjustTableFontSize();
        }

        if (event.target.classList.contains('statement-column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleStatementColumns.includes(column)) {
                    visibleStatementColumns.push(column);
                }
            } else {
                // Prevent unchecking if it would result in less than 3 columns
                if (visibleStatementColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleStatementColumns = visibleStatementColumns.filter(col => col !== column);
            }

            applyColumnVisibility('statementTableElement', visibleStatementColumns);
            saveColumnVisibility();
        }
    });

    async function showWalletStatement(walletId, walletName) {
        currentStatementWalletId = walletId;
        document.getElementById('statementWalletName').textContent = walletName;
        document.getElementById('walletStatementModal').classList.remove('hidden');

        document.getElementById('statementDateFilter').value = 'all';
        await loadWalletStatement();
    }

    function hideWalletStatementModal() {
        document.getElementById('walletStatementModal').classList.add('hidden');
        currentStatementWalletId = null;
        currentWalletStatement = [];
    }

    async function loadWalletStatement() {
        if (!currentStatementWalletId) return;

        const filter = document.getElementById('statementDateFilter').value;
        let params = { wallet_number: currentStatementWalletId, filter: 'all' };

        if (filter !== 'all') {
            const days = parseInt(filter);
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - days);

            params.filter = 'range';
            params.start = startDate.toISOString().split('T')[0];
            params.end = endDate.toISOString().split('T')[0];
        }

        const loading = document.getElementById('statementLoading');
        const table = document.getElementById('statementTable');
        const mobile = document.getElementById('statementMobile');
        const empty = document.getElementById('statementEmpty');

        if (loading) loading.classList.remove('hidden');
        if (table) table.classList.add('hidden');
        if (mobile) mobile.classList.add('hidden');
        if (empty) empty.classList.add('hidden');

        try {
            const res = await fetch(`${API_URL}?action=getWalletStatement`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(params)
            });
            const data = await res.json();

            document.getElementById('statementLoading').classList.add('hidden');

            if (data.success && data.statement) {
                const transformedTransactions = transformStatementData(data.statement);
                currentWalletStatement = transformedTransactions;

                if (currentWalletStatement.length > 0) {
                    renderWalletStatement(currentWalletStatement);
                    document.getElementById('statementTable').classList.remove('hidden');
                    document.getElementById('statementMobile').classList.remove('hidden');
                    applyColumnVisibility('statementTableElement', visibleStatementColumns);
                } else {
                    document.getElementById('statementEmpty').classList.remove('hidden');
                }
            } else {
                document.getElementById('statementEmpty').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error loading wallet statement:', error);
            document.getElementById('statementLoading').classList.add('hidden');
            document.getElementById('statementEmpty').classList.remove('hidden');
        }
    }

    function transformStatementData(statement) {
        const transformedEntries = [];

        // Sort transactions by date (newest first) before processing
        const sortedStatement = statement.sort((a, b) =>
            new Date(b.transaction.created_at) - new Date(a.transaction.created_at)
        );

        sortedStatement.forEach(transactionBlock => {
            const transaction = transactionBlock.transaction;

            if (transaction.entries && transaction.entries.length > 0) {
                // Reverse the entries array to show last entry first
                const reversedEntries = [...transaction.entries].reverse();

                // Process each entry in reverse order
                reversedEntries.forEach((entry, entryIndex) => {
                    const transformedEntry = {
                        transaction_id: transaction.transaction_id,
                        transaction_type: transaction.transaction_type,
                        payment_method: transaction.payment_method,
                        status: transaction.status,
                        amount_total: parseFloat(transaction.amount_total),
                        transaction_note: transaction.note,
                        transaction_date: transaction.created_at,
                        entry_id: entry.entry_id,
                        entry_type: entry.entry_type,
                        amount: parseFloat(entry.amount),
                        balance_after: parseFloat(entry.balance_after),
                        entry_note: entry.entry_note,
                        entry_date: entry.created_at,
                        related_entries: entry.related_entries || [],
                        is_first_in_group: entryIndex === 0,
                        group_size: transaction.entries.length
                    };
                    transformedEntries.push(transformedEntry);
                });
            } else {
                // Handle transactions with no entries (usually failed transactions)
                transformedEntries.push({
                    transaction_id: transaction.transaction_id,
                    transaction_type: transaction.transaction_type,
                    payment_method: transaction.payment_method,
                    status: transaction.status,
                    amount_total: parseFloat(transaction.amount_total),
                    transaction_note: transaction.note,
                    transaction_date: transaction.created_at,
                    entry_id: null,
                    entry_type: null,
                    amount: 0,
                    balance_after: null,
                    entry_note: null,
                    entry_date: null,
                    related_entries: [],
                    is_first_in_group: true,
                    group_size: 1
                });
            }
        });

        return transformedEntries;
    }

    function renderWalletStatement(entries) {
        const tbody = document.getElementById('statementTableBody');
        const mobile = document.getElementById('statementMobile');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        entries.forEach((entry, index) => {
            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors`;

            // Add visual indicator for transaction groups
            if (entry.is_first_in_group && entry.group_size > 1) {
                tr.classList.add('border-l-4', 'border-blue-400');
            }

            const transactionDate = new Date(entry.transaction_date);
            const dateStr = transactionDate.toLocaleDateString('en-GB', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            const timeStr = transactionDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const debitAmount = entry.entry_type === 'DEBIT' ? entry.amount : 0;
            const creditAmount = entry.entry_type === 'CREDIT' ? entry.amount : 0;

            // Create description with transaction context
            let description = entry.entry_note || getTransactionDescription(entry);
            if (entry.status === 'FAILED') {
                description = `${entry.transaction_type} (FAILED) - ${entry.transaction_note}`;
            }

            tr.innerHTML = `
                <td data-column="datetime" class="px-4 py-3 text-sm">
                    <div class="font-medium text-gray-900">${dateStr}</div>
                    <div class="text-xs text-gray-500">${timeStr}</div>
                </td>
                <td data-column="entryid" class="px-4 py-3 text-sm">
                    <div class="max-w-[120px] overflow-hidden text-ellipsis whitespace-nowrap font-mono text-gray-700" title="${entry.entry_id || ''}">
                        ${entry.entry_id || ''}
                    </div>
                </td>
                <td data-column="description" class="px-4 py-3 text-sm">
                    <div class="max-w-[250px] overflow-hidden text-ellipsis whitespace-nowrap" title="${description}">
                        <div class="font-medium text-gray-900">${description}</div>
                        ${entry.payment_method ? `<div class="text-xs text-gray-500">${entry.payment_method.replace(/_/g, ' ')}</div>` : ''}
                    </div>
                </td>
                <td data-column="debit" class="px-4 py-3 text-sm text-right">
                    ${debitAmount > 0 ? `<span class="text-red-600 font-semibold">-${formatCurrency(debitAmount)}</span>` : '<span class="text-gray-400">-</span>'}
                </td>
                <td data-column="credit" class="px-4 py-3 text-sm text-right">
                    ${creditAmount > 0 ? `<span class="text-green-600 font-semibold">+${formatCurrency(creditAmount)}</span>` : '<span class="text-gray-400">-</span>'}
                </td>
                <td data-column="balance" class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                    ${entry.balance_after !== null ? formatCurrency(entry.balance_after) : '<span class="text-gray-400">-</span>'}
                </td>
                <td data-column="related" class="px-4 py-3 text-sm">
                    ${renderRelatedEntries(entry.related_entries)}
                </td>
            `;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = `bg-white rounded-lg p-4 border border-gray-200 ${entry.is_first_in_group && entry.group_size > 1 ? 'border-l-4 border-l-blue-400' : ''}`;
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 text-sm mb-1">${description}</div>
                        <div class="text-xs text-gray-500">${dateStr} • ${timeStr}</div>
                        ${entry.payment_method ? `<div class="text-xs text-gray-500 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>` : ''}
                    </div>
                    <div class="text-right ml-3">
                        ${debitAmount > 0 ? `<div class="text-red-600 font-semibold text-sm">-${formatCurrency(debitAmount)}</div>` : ''}
                        ${creditAmount > 0 ? `<div class="text-green-600 font-semibold text-sm">+${formatCurrency(creditAmount)}</div>` : ''}
                        <div class="text-xs text-gray-500 mt-1">Balance: ${entry.balance_after !== null ? formatCurrency(entry.balance_after) : '-'}</div>
                    </div>
                </div>
                
                <div class="text-xs text-gray-500 mb-2">
                    <span class="font-mono">${entry.entry_id || 'No Entry ID'}</span>
                </div>
                
                ${entry.related_entries.length > 0 ? `
                    <div class="mt-3">
                        <div class="text-xs font-medium text-gray-700 mb-2">Related Entries:</div>
                        ${renderRelatedEntriesMobile(entry.related_entries)}
                    </div>
                ` : ''}
            `;
            mobile.appendChild(card);
        });
    }

    function renderRelatedEntries(relatedEntries) {
        if (!relatedEntries || relatedEntries.length === 0) {
            return '<span class="text-gray-400 text-xs">None</span>';
        }

        return relatedEntries.map(related => {
            let accountTypeLabel = 'Cash Account';
            if (related.owner_type) {
                const ownerType = related.owner_type.charAt(0).toUpperCase() + related.owner_type.slice(1).toLowerCase();
                accountTypeLabel = `${ownerType} Wallet`;
            }

            const accountName = related.account_or_wallet_name || (related.wallet_number || related.cash_account_id);
            const fullAccountName = `${accountTypeLabel}: ${accountName}`;
            const entryTypeClass = related.entry_type === 'CREDIT' ? 'text-green-600' : 'text-red-600';
            const sign = related.entry_type === 'CREDIT' ? '+' : '-';

            return `
            <div class="bg-gray-50 border-l-2 border-gray-300 ml-4 pl-3 py-2 mb-2 text-xs">
                <div class="flex items-center gap-1 mb-1">
                    <i class="fas fa-arrow-right text-gray-400"></i>
                    <span class="font-medium text-gray-700">${fullAccountName}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="${entryTypeClass} font-semibold">${sign}${formatCurrency(related.amount)}</span>
                    <span class="text-gray-500">Balance: ${formatCurrency(related.balance_after)}</span>
                </div>
                ${related.entry_note ? `<div class="text-gray-600 mt-1 italic">${related.entry_note}</div>` : ''}
            </div>
        `;
        }).join('');
    }

    function renderRelatedEntriesMobile(relatedEntries) {
        if (!relatedEntries || relatedEntries.length === 0) {
            return '<span class="text-gray-400">None</span>';
        }

        return relatedEntries.map(related => {
            let accountTypeLabel = 'Cash Account';
            if (related.owner_type) {
                const ownerType = related.owner_type.charAt(0).toUpperCase() + related.owner_type.slice(1).toLowerCase();
                accountTypeLabel = `${ownerType} Wallet`;
            }

            const accountName = related.account_or_wallet_name || (related.wallet_number || related.cash_account_id);
            const fullAccountName = `${accountTypeLabel}: ${accountName}`;
            const entryTypeClass = related.entry_type === 'CREDIT' ? 'text-green-600' : 'text-red-600';
            const sign = related.entry_type === 'CREDIT' ? '+' : '-';

            return `
            <div class="bg-gray-50 rounded p-2 mb-2 text-xs border-l-2 border-gray-300">
                <div class="font-medium text-gray-700 mb-1">${fullAccountName}</div>
                <div class="flex justify-between items-center">
                    <span class="${entryTypeClass} font-semibold">${sign}${formatCurrency(related.amount)}</span>
                    <span class="text-gray-500">Balance: ${formatCurrency(related.balance_after)}</span>
                </div>
                ${related.entry_note ? `<div class="text-gray-600 mt-1 italic">${related.entry_note}</div>` : ''}
            </div>
        `;
        }).join('');
    }

    function getTransactionDescription(entry) {
        const typeMap = {
            'TOPUP': 'Wallet Top-up',
            'TRANSFER': 'Transfer',
            'PAYMENT': 'Payment',
            'WITHDRAWAL': 'Withdrawal'
        };

        const methodMap = {
            'MOBILE_MONEY_GATEWAY': 'Mobile Money',
            'BANK_TRANSFER': 'Bank Transfer',
            'CARD_PAYMENT': 'Card Payment'
        };

        let description = typeMap[entry.transaction_type] || entry.transaction_type;

        if (entry.payment_method) {
            description += ` via ${methodMap[entry.payment_method] || entry.payment_method}`;
        }

        // Add transaction ID and amount total
        description += ` (TXN: ${entry.transaction_id}, Total: UGX ${formatCurrency(entry.amount_total)})`;

        return description;
    }

    function adjustTableFontSize() {
        const tables = ['wallets-table', 'platform-settings-table'];
        tables.forEach(tableId => {
            const table = document.getElementById(tableId);
            if (!table) return;

            const container = table.parentElement;
            let fontSize = 14;

            table.style.fontSize = fontSize + 'px';

            while ((table.scrollWidth > container.clientWidth || hasOverflowingContent(table)) && fontSize > 8) {
                fontSize -= 0.5;
                table.style.fontSize = fontSize + 'px';
            }

            if (fontSize < 10) {
                table.style.fontSize = '10px';
            }
        });
    }

    function hasOverflowingContent(table) {
        const cells = table.querySelectorAll('td');
        for (let cell of cells) {
            if (cell.scrollHeight > cell.clientHeight || cell.scrollWidth > cell.clientWidth) {
                return true;
            }
        }
        return false;
    }

    function sortByStatus() {
        wallets.sort((a, b) => {
            const sa = a.status.toLowerCase();
            const sb = b.status.toLowerCase();
            if (sa < sb) return statusSortAsc ? -1 : 1;
            if (sa > sb) return statusSortAsc ? 1 : -1;
            return 0;
        });
        statusSortAsc = !statusSortAsc;
        document.getElementById('statusSortIcon').className = statusSortAsc
            ? 'fas fa-sort-up text-gray-400 ml-1'
            : 'fas fa-sort-down text-gray-400 ml-1';
        renderWalletsTable(wallets);
        adjustTableFontSize();
    }

    function showCreateWalletForm() {
        const offcanvas = document.getElementById('createWalletOffcanvas');
        const form = document.getElementById('createWalletForm');
        form.reset();
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideCreateWalletForm() {
        const offcanvas = document.getElementById('createWalletOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function createWallet() {
        const name = document.getElementById('walletName').value.trim();
        if (!name) {
            alert('Please enter a wallet name');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=createZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ owner_type: 'PLATFORM', wallet_name: name })
            });
            const data = await res.json();

            if (data.success) {
                hideCreateWalletForm();
                fetchWallets();
                loadPlatformAccountsDropdown();
            } else {
                alert(data.message || 'Failed to create wallet');
            }
        } catch (err) {
            console.error('Error creating wallet:', err);
            alert('Error creating wallet');
        }
    }

    function showEditWalletForm(id) {
        const wallet = wallets.find(w => w.wallet_number === id);
        if (!wallet) return;

        document.getElementById('editWalletId').value = wallet.wallet_number;
        document.getElementById('editWalletOwnerType').value = wallet.owner_type;
        document.getElementById('editWalletOwnerTypeDisplay').textContent = wallet.owner_type === 'USER' ? 'User Wallet' :
            wallet.owner_type === 'VENDOR' ? 'Vendor Wallet' : 'Platform Wallet';
        document.getElementById('editWalletName').value = wallet.wallet_name;

        const statusRadio = document.querySelector(`input[name="editWalletStatus"][value="${wallet.status}"]`);
        if (statusRadio) statusRadio.checked = true;

        const offcanvas = document.getElementById('editWalletOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideEditWalletForm() {
        const offcanvas = document.getElementById('editWalletOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function updateWallet() {
        const id = document.getElementById('editWalletId').value;
        const name = document.getElementById('editWalletName').value.trim();
        const status = document.querySelector('input[name="editWalletStatus"]:checked')?.value;

        if (!name || !status) {
            alert('Please fill in all required fields');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=updateZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_number: id, wallet_name: name, status: status })
            });
            const data = await res.json();

            if (data.success) {
                hideEditWalletForm();
                fetchWallets();
            } else {
                alert(data.message || 'Failed to update wallet');
            }
        } catch (err) {
            console.error('Error updating wallet:', err);
            alert('Error updating wallet');
        }
    }

    function showCreatePlatformSettingForm() {
        const offcanvas = document.getElementById('createPlatformSettingOffcanvas');
        const form = document.getElementById('createPlatformSettingForm');
        form.reset();
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideCreatePlatformSettingForm() {
        const offcanvas = document.getElementById('createPlatformSettingOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function createPlatformSetting() {
        const accountId = document.getElementById('platformAccountSelect').value;
        const type = document.getElementById('settingType').value;

        if (!accountId || !type) {
            alert('Please select both platform account and setting type');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'add',
                    platform_account_id: accountId,
                    type: type
                })
            });
            const data = await res.json();

            if (data.success) {
                hideCreatePlatformSettingForm();
                fetchPlatformSettings();
            } else {
                alert(data.message || 'Failed to create platform setting');
            }
        } catch (err) {
            console.error('Error creating platform setting:', err);
            alert('Error creating platform setting');
        }
    }

    function showEditPlatformSettingForm(id) {
        const setting = platformSettings.find(s => s.id == id);
        if (!setting) return;

        const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
        const walletName = wallet ? wallet.wallet_name : setting.wallet_number;

        document.getElementById('editSettingId').value = setting.id;
        document.getElementById('editSettingAccountId').value = setting.platform_account_id;
        document.getElementById('editSettingAccountDisplay').textContent = walletName;
        document.getElementById('editSettingType').value = setting.type;

        const offcanvas = document.getElementById('editPlatformSettingOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideEditPlatformSettingForm() {
        const offcanvas = document.getElementById('editPlatformSettingOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function updatePlatformSetting() {
        const id = document.getElementById('editSettingId').value;
        const accountId = document.getElementById('editSettingAccountId').value;
        const type = document.getElementById('editSettingType').value;

        if (!type) {
            alert('Please select a setting type');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'update',
                    id: parseInt(id),
                    platform_account_id: accountId,
                    type: type
                })
            });
            const data = await res.json();

            if (data.success) {
                hideEditPlatformSettingForm();
                fetchPlatformSettings();
            } else {
                alert(data.message || 'Failed to update platform setting');
            }
        } catch (err) {
            console.error('Error updating platform setting:', err);
            alert('Error updating platform setting');
        }
    }

    function showDeleteConfirm(type, id) {
        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteMessage');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        if (type === 'wallet') {
            message.textContent = 'Are you sure you want to delete this wallet? All associated data will be permanently removed.';
            confirmBtn.setAttribute('data-type', 'wallet');
            confirmBtn.setAttribute('data-id', id);
        } else if (type === 'setting') {
            message.textContent = 'Are you sure you want to delete this platform setting? This action cannot be undone.';
            confirmBtn.setAttribute('data-type', 'setting');
            confirmBtn.setAttribute('data-id', id);
        }

        modal.classList.remove('hidden');
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    async function confirmDelete() {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const type = confirmBtn.getAttribute('data-type');
        const id = confirmBtn.getAttribute('data-id');

        if (type === 'wallet') {
            await deleteWallet(id);
        } else if (type === 'setting') {
            await deletePlatformSetting(id);
        }
    }

    async function deleteWallet(id) {
        try {
            const res = await fetch(`${API_URL}?action=deleteZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_number: id })
            });
            const data = await res.json();

            if (data.success) {
                hideDeleteConfirm();
                fetchWallets();
                loadPlatformAccountsDropdown();
            } else {
                alert(data.message || 'Failed to delete wallet');
            }
        } catch (err) {
            console.error('Error deleting wallet:', err);
            alert('Error deleting wallet');
        }
    }

    async function deletePlatformSetting(id) {
        const setting = platformSettings.find(s => s.id == id);
        if (!setting) return;

        try {
            const res = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'remove',
                    id: parseInt(id),
                    platform_account_id: setting.platform_account_id
                })
            });
            const data = await res.json();

            if (data.success) {
                hideDeleteConfirm();
                fetchPlatformSettings();
            } else {
                alert(data.message || 'Failed to delete platform setting');
            }
        } catch (err) {
            console.error('Error deleting platform setting:', err);
            alert('Error deleting platform setting');
        }
    }

    function filterWallets() {
        const query = document.getElementById('searchWallets').value.trim().toLowerCase();
        const type = document.getElementById('filterWallets').value;

        let filtered = wallets;

        if (type !== 'all') {
            filtered = filtered.filter(w => w.owner_type === type);
        }

        if (query) {
            filtered = filtered.filter(w =>
                w.wallet_name.toLowerCase().includes(query) ||
                w.wallet_number.toLowerCase().includes(query)
            );
        }

        renderWalletsTable(filtered);
        adjustTableFontSize();
    }

    function filterPlatformSettings() {
        const accountId = document.getElementById('filterPlatformAccounts').value;
        fetchPlatformSettings(accountId);
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadColumnVisibility();
        switchTab('wallets');

        document.getElementById('create-wallet-btn').addEventListener('click', showCreateWalletForm);
        document.getElementById('create-platform-setting-btn').addEventListener('click', showCreatePlatformSettingForm);
        document.getElementById('statusHeader').addEventListener('click', sortByStatus);

        document.getElementById('submitWalletForm').addEventListener('click', createWallet);
        document.getElementById('updateWalletForm').addEventListener('click', updateWallet);
        document.getElementById('submitPlatformSettingForm').addEventListener('click', createPlatformSetting);
        document.getElementById('updatePlatformSettingForm').addEventListener('click', updatePlatformSetting);
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);

        document.getElementById('filterWallets').addEventListener('change', filterWallets);
        document.getElementById('searchWallets').addEventListener('input', filterWallets);
        document.getElementById('filterPlatformAccounts').addEventListener('change', filterPlatformSettings);
        document.getElementById('statementDateFilter').addEventListener('change', loadWalletStatement);

        window.addEventListener('resize', () => {
            adjustTableFontSize();
        });
    });

    window.switchTab = switchTab;
    window.showEditWalletForm = showEditWalletForm;
    window.showEditPlatformSettingForm = showEditPlatformSettingForm;
    window.showDeleteConfirm = showDeleteConfirm;
    window.hideDeleteConfirm = hideDeleteConfirm;
    window.hideCreateWalletForm = hideCreateWalletForm;
    window.hideEditWalletForm = hideEditWalletForm;
    window.showCreateWalletForm = showCreateWalletForm;
    window.hideCreatePlatformSettingForm = hideCreatePlatformSettingForm;
    window.hideEditPlatformSettingForm = hideEditPlatformSettingForm;
    window.showCreatePlatformSettingForm = showCreatePlatformSettingForm;
    window.showWalletStatement = showWalletStatement;
    window.hideWalletStatementModal = hideWalletStatementModal;
    window.toggleColumnSelector = toggleColumnSelector;
    window.toggleStatementColumnSelector = toggleStatementColumnSelector;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>