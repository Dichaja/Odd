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
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Zzimba Wallets</h1>
                <p class="text-gray-600 mt-1">Manage platform, user, and vendor wallets</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">User Wallets</p>
                        <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="user-wallets-count">0</p>
                        <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="user-wallets-total">UGX 0.00
                        </p>
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
                        <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="vendor-wallets-count">0</p>
                        <p class="text-sm font-medium text-purple-700 whitespace-nowrap" id="vendor-wallets-total">UGX
                            0.00</p>
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
                        <p class="text-lg font-bold text-cyan-900 whitespace-nowrap" id="platform-wallets-count">0</p>
                        <p class="text-sm font-medium text-cyan-700 whitespace-nowrap" id="platform-wallets-total">UGX
                            0.00</p>
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

        <div class="flex gap-8">
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div id="desktop-nav">
                    <nav class="space-y-2" aria-label="Wallet Navigation">
                        <button id="platform-tab"
                            class="tab-button active w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 bg-primary/10 text-primary border border-primary/20"
                            onclick="switchWalletTab('PLATFORM')">
                            <i class="fas fa-building"></i>
                            <span>Platform Wallets</span>
                        </button>
                        <button id="user-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchWalletTab('USER')">
                            <i class="fas fa-user"></i>
                            <span>User Wallets</span>
                        </button>
                        <button id="vendor-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchWalletTab('VENDOR')">
                            <i class="fas fa-store"></i>
                            <span>Vendor Wallets</span>
                        </button>
                        <button id="settings-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchWalletTab('settings')">
                            <i class="fas fa-cogs"></i>
                            <span>Platform Settings</span>
                        </button>
                        <button id="credit-assignments-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchWalletTab('credit-assignments')">
                            <i class="fas fa-link"></i>
                            <span>Credit Assignments</span>
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
                                    <i class="fas fa-building text-primary"></i>
                                    <span id="mobile-tab-label" class="font-medium text-gray-900">Platform
                                        Wallets</span>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                    id="mobile-tab-chevron"></i>
                            </button>

                            <div id="mobile-tab-dropdown"
                                class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                                <div class="py-2">
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="PLATFORM">
                                        <i class="fas fa-building text-cyan-600"></i>
                                        <span>Platform Wallets</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="USER">
                                        <i class="fas fa-user text-green-600"></i>
                                        <span>User Wallets</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="VENDOR">
                                        <i class="fas fa-store text-purple-600"></i>
                                        <span>Vendor Wallets</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="settings">
                                        <i class="fas fa-cogs text-gray-600"></i>
                                        <span>Platform Account Settings</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="credit-assignments">
                                        <i class="fas fa-link text-indigo-600"></i>
                                        <span>Credit Assignments</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="wallets-content" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="wallets-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchWallets"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search wallets...">
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

                                    <button id="create-wallet-btn"
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                        <i class="fas fa-plus"></i>
                                        <span>Create Wallet</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="wallets-grid">
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="settings-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="settings-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchSettings"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search settings...">
                                    </div>
                                </div>

                                <div
                                    class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <div class="relative">
                                        <button id="viewSettingsColumnsBtn" onclick="toggleSettingsColumnSelector()"
                                            class="hidden lg:flex px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
                                            <i class="fas fa-eye text-xs"></i>
                                            <span>View</span>
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </button>

                                        <div id="settingsColumnSelector"
                                            class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-48">
                                            <div class="p-3 border-b border-gray-100">
                                                <h4 class="text-sm font-semibold text-gray-900">Show Columns</h4>
                                                <p class="text-xs text-gray-500 mt-1">Select at least 3 columns</p>
                                            </div>
                                            <div class="p-2 space-y-1" id="settingsColumnCheckboxes">
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="settings-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="platformaccount" checked>
                                                    <span class="text-sm text-gray-700">Platform Account</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="settings-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="type" checked>
                                                    <span class="text-sm text-gray-700">Type</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="settings-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="created" checked>
                                                    <span class="text-sm text-gray-700">Created</span>
                                                </label>
                                                <label
                                                    class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                                    <input type="checkbox"
                                                        class="settings-column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                        data-column="actions" checked>
                                                    <span class="text-sm text-gray-700">Actions</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button id="create-setting-btn"
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Setting</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="settings-grid">
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="credit-assignments-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200"
                        id="credit-assignments-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchCreditAssignments"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search assignments...">
                                    </div>
                                </div>

                                <div
                                    class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <button id="create-credit-assignment-btn"
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                        <i class="fas fa-plus"></i>
                                        <span>Assign Credit Setting</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="credit-assignments-grid">
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

<div id="walletStatementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideWalletStatementModal()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-7xl relative z-10 overflow-hidden max-h-[95vh] flex flex-col">
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
                            class="hidden sm:flex px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
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

        <div class="flex-1 overflow-auto">
            <div id="statementLoading" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>

            <div id="statementTable" class="hidden lg:block overflow-auto flex-1 h-full">
                <table class="w-full" id="statementTableElement">
                    <thead class="bg-white border-b border-gray-200 sticky top-0">
                        <tr>
                            <th data-column="datetime"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Date/Time</th>
                            <th data-column="entryid"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Entry ID</th>
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

            <div id="statementMobile" class="lg:hidden p-4 space-y-4 overflow-auto flex-1 h-full">
            </div>

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

<div id="createWalletModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCreateWalletForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
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
            <form id="createWalletForm" class="space-y-6">
                <div>
                    <label for="walletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet Name</label>
                    <input type="text" id="walletName" name="walletName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter a descriptive name" required>
                    <p class="mt-2 text-sm text-gray-500">This wallet will be created as a platform wallet.</p>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideCreateWalletForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="submitWalletForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Create
                    Wallet</button>
            </div>
        </div>
    </div>
</div>

<div id="editWalletModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEditWalletForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
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
            <form id="editWalletForm" class="space-y-6">
                <input type="hidden" id="editWalletId">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Owner Type</label>
                    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700">
                        <span id="editWalletOwnerTypeDisplay" class="font-medium"></span>
                    </div>
                </div>

                <div>
                    <label for="editWalletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet
                        Name</label>
                    <input type="text" id="editWalletName" name="editWalletName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter wallet name" required>
                </div>

                <div>
                    <label for="editWalletStatus" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select id="editWalletStatus" name="editWalletStatus"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideEditWalletForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="updateWalletForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Update
                    Wallet</button>
            </div>
        </div>
    </div>
</div>

<div id="createSettingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCreateSettingForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Add Platform Account Setting</h3>
            </div>
            <button onclick="hideCreateSettingForm()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="createSettingForm" class="space-y-6">
                <div>
                    <label for="platformAccountSelect" class="block text-sm font-semibold text-gray-700 mb-2">Platform
                        Account</label>
                    <select id="platformAccountSelect" name="platformAccountSelect"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                        <option value="">Select Platform Account</option>
                    </select>
                </div>

                <div>
                    <label for="settingType" class="block text-sm font-semibold text-gray-700 mb-2">Setting Type</label>
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

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideCreateSettingForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="submitSettingForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Add
                    Setting</button>
            </div>
        </div>
    </div>
</div>

<div id="editSettingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEditSettingForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Platform Account Setting</h3>
            </div>
            <button onclick="hideEditSettingForm()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="editSettingForm" class="space-y-6">
                <input type="hidden" id="editSettingId">

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

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideEditSettingForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="updateSettingForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Update
                    Setting</button>
            </div>
        </div>
    </div>
</div>

<div id="createCreditAssignmentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCreateCreditAssignmentForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-link text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Assign Credit Setting to Wallet</h3>
            </div>
            <button onclick="hideCreateCreditAssignmentForm()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="createCreditAssignmentForm" class="space-y-6">
                <div>
                    <label for="assignmentCreditSettingSelect"
                        class="block text-sm font-semibold text-gray-700 mb-2">Credit Setting</label>
                    <select id="assignmentCreditSettingSelect" name="assignmentCreditSettingSelect"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                        <option value="">Select Credit Setting</option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">Select the credit setting first to see its details</p>
                </div>

                <div id="creditSettingDetails" class="hidden p-4 bg-gray-50 rounded-xl">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Credit Setting Details</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Category:</span>
                            <span id="settingCategory" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Value:</span>
                            <span id="settingValue" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Applicable To:</span>
                            <span id="settingApplicableTo" class="font-medium"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="assignmentWalletSelect" class="block text-sm font-semibold text-gray-700 mb-2">Platform
                        Wallet</label>
                    <select id="assignmentWalletSelect" name="assignmentWalletSelect"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                        <option value="">Select Platform Wallet</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideCreateCreditAssignmentForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="submitCreditAssignmentForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Assign
                    Setting</button>
            </div>
        </div>
    </div>
</div>

<div id="confirmDeleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideConfirmDeleteModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Removal</h3>
                    <p class="text-sm text-gray-500 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <div class="mb-6">
                <p class="text-gray-700" id="confirmDeleteMessage">Are you sure you want to remove this credit
                    assignment?</p>
            </div>
            <div class="flex gap-3">
                <button onclick="hideConfirmDeleteModal()"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Cancel</button>
                <button id="confirmDeleteButton"
                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">Remove</button>
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
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageZzimbaWallets.php';
    let wallets = [];
    let platformSettings = [];
    let creditSettings = [];
    let creditAssignments = [];
    let currentWalletTab = 'PLATFORM';
    let currentWalletStatement = [];
    let currentStatementWalletId = null;
    let pendingDeleteAssignmentId = null;

    const WALLETS_COLUMNS_STORAGE_KEY = 'zzimba_wallets_columns';
    const STATEMENT_COLUMNS_STORAGE_KEY = 'zzimba_statement_columns';
    const SETTINGS_COLUMNS_STORAGE_KEY = 'zzimba_settings_columns';

    let visibleWalletColumns = ['details', 'owner', 'status', 'balance', 'actions'];
    let visibleStatementColumns = ['datetime', 'entryid', 'description', 'debit', 'credit', 'balance', 'related'];
    let visibleSettingsColumns = ['platformaccount', 'type', 'created', 'actions'];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getOwnerTypeBadge(type) {
        const badges = {
            'USER': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-user mr-1"></i>User</span>',
            'VENDOR': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-store mr-1"></i>Vendor</span>',
            'PLATFORM': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800"><i class="fas fa-building mr-1"></i>Platform</span>'
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

    function getCategoryBadge(category) {
        const categoryText = category.charAt(0).toUpperCase() + category.slice(1);
        const categoryClasses = {
            'sms': 'bg-blue-100 text-blue-800',
            'bonus': 'bg-green-100 text-green-800',
            'access': 'bg-purple-100 text-purple-800',
            'commission': 'bg-orange-100 text-orange-800',
            'transfer': 'bg-red-100 text-red-800',
            'withdrawal': 'bg-indigo-100 text-indigo-800',
            'subscription': 'bg-pink-100 text-pink-800'
        };
        const className = categoryClasses[category] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${className}">${categoryText}</span>`;
    }

    function formatValue(type, value) {
        if (type === 'percentage') {
            return `${value}%`;
        } else {
            return `Sh. ${formatCurrency(value)}`;
        }
    }

    function updateTabHeights() {
        const desktopNav = document.getElementById('desktop-nav');
        const walletsContainer = document.getElementById('wallets-container');
        const settingsContainer = document.getElementById('settings-container');
        const creditAssignmentsContainer = document.getElementById('credit-assignments-container');

        if (desktopNav && window.innerWidth >= 1024) {
            let activeContainer;
            if (currentWalletTab === 'settings') {
                activeContainer = settingsContainer;
            } else if (currentWalletTab === 'credit-assignments') {
                activeContainer = creditAssignmentsContainer;
            } else {
                activeContainer = walletsContainer;
            }

            if (activeContainer) {
                const containerHeight = activeContainer.offsetHeight;
                desktopNav.style.height = containerHeight + 'px';
            }
        } else if (desktopNav) {
            desktopNav.style.height = 'auto';
        }
    }

    function switchWalletTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
        });

        document.getElementById('wallets-content').classList.add('hidden');
        document.getElementById('settings-content').classList.add('hidden');
        document.getElementById('credit-assignments-content').classList.add('hidden');

        if (tabName === 'settings') {
            document.getElementById('settings-content').classList.remove('hidden');
            const activeTab = document.getElementById('settings-tab');
            if (activeTab) {
                activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            }
            loadPlatformSettings();
            updateMobileTabLabel('Platform Account Settings', 'fas fa-cogs');
        } else if (tabName === 'credit-assignments') {
            document.getElementById('credit-assignments-content').classList.remove('hidden');
            const activeTab = document.getElementById('credit-assignments-tab');
            if (activeTab) {
                activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            }
            loadCreditAssignments();
            updateMobileTabLabel('Credit Assignments', 'fas fa-link');
        } else {
            document.getElementById('wallets-content').classList.remove('hidden');

            const tabId = `${tabName.toLowerCase()}-tab`;
            const activeTab = document.getElementById(tabId);
            if (activeTab) {
                activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
                activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            }

            currentWalletTab = tabName;
            renderFilteredWallets();

            const tabLabels = {
                'USER': { label: 'User Wallets', icon: 'fas fa-user' },
                'VENDOR': { label: 'Vendor Wallets', icon: 'fas fa-store' },
                'PLATFORM': { label: 'Platform Wallets', icon: 'fas fa-building' }
            };
            const tabInfo = tabLabels[tabName] || tabLabels['PLATFORM'];
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

    async function loadWallets() {
        try {
            const response = await fetch(`${API_URL}?action=getZzimbaWallets`);
            const data = await response.json();

            if (data.success) {
                wallets = data.wallets || [];
                renderFilteredWallets();
                updateQuickStats();
                loadPlatformAccountsDropdown();
                loadCreditAssignmentDropdowns();
            } else {
                showMessage('error', 'Error', data.message || 'Failed to load wallets');
            }
        } catch (error) {
            console.error('Error loading wallets:', error);
            showMessage('error', 'Error', 'Failed to load wallets');
        }
    }

    async function loadPlatformSettings() {
        try {
            const response = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ operation: 'list' })
            });
            const data = await response.json();

            if (data.success) {
                platformSettings = data.settings || [];
                renderPlatformSettings();
            } else {
                showMessage('error', 'Error', data.message || 'Failed to load platform settings');
            }
        } catch (error) {
            console.error('Error loading platform settings:', error);
            showMessage('error', 'Error', 'Failed to load platform settings');
        }
    }

    async function loadCreditSettings() {
        try {
            const response = await fetch('<?= BASE_URL ?>admin/fetch/manageZzimbaCreditSettings.php?action=getSettings');
            const data = await response.json();

            if (data.success) {
                creditSettings = data.settings || [];
                loadCreditAssignmentDropdowns();
            } else {
                console.error('Failed to load credit settings:', data.message);
            }
        } catch (error) {
            console.error('Error loading credit settings:', error);
        }
    }

    async function loadCreditAssignments() {
        try {
            const response = await fetch(`${API_URL}?action=getCreditAssignments`);
            const data = await response.json();

            if (data.success) {
                creditAssignments = data.assignments || [];
                renderCreditAssignments();
            } else {
                showMessage('error', 'Error', data.message || 'Failed to load credit assignments');
            }
        } catch (error) {
            console.error('Error loading credit assignments:', error);
            showMessage('error', 'Error', 'Failed to load credit assignments');
        }
    }

    function loadPlatformAccountsDropdown() {
        const platformWallets = wallets.filter(w => w.owner_type === 'PLATFORM');
        const select = document.getElementById('platformAccountSelect');
        if (!select) return;

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

    function loadCreditAssignmentDropdowns() {
        const platformWallets = wallets.filter(w => w.owner_type === 'PLATFORM');
        const walletSelect = document.getElementById('assignmentWalletSelect');
        const creditSelect = document.getElementById('assignmentCreditSettingSelect');

        if (walletSelect) {
            const firstOption = walletSelect.querySelector('option');
            walletSelect.innerHTML = '';
            if (firstOption) walletSelect.appendChild(firstOption);

            platformWallets.forEach(wallet => {
                const option = document.createElement('option');
                option.value = wallet.wallet_id;
                option.textContent = wallet.wallet_name;
                walletSelect.appendChild(option);
            });
        }

        if (creditSelect && creditSettings.length > 0) {
            const firstOption = creditSelect.querySelector('option');
            creditSelect.innerHTML = '';
            if (firstOption) creditSelect.appendChild(firstOption);

            // Get assigned credit setting IDs to filter them out
            const assignedCreditSettingIds = creditAssignments.map(assignment => assignment.credit_setting_id);

            creditSettings.forEach(setting => {
                // Only add settings that are not already assigned
                if (!assignedCreditSettingIds.includes(setting.id)) {
                    const option = document.createElement('option');
                    option.value = setting.id;
                    option.textContent = `${setting.setting_name} (${setting.category})`;
                    option.dataset.category = setting.category;
                    option.dataset.type = setting.setting_type;
                    option.dataset.value = setting.setting_value;
                    option.dataset.description = setting.description || '';
                    option.dataset.applicableTo = setting.applicable_to || '';
                    creditSelect.appendChild(option);
                }
            });
        }
    }

    function renderFilteredWallets() {
        let filteredWallets = wallets.filter(w => w.owner_type === currentWalletTab);

        const query = document.getElementById('searchWallets').value.trim().toLowerCase();
        if (query) {
            filteredWallets = filteredWallets.filter(w =>
                w.wallet_name.toLowerCase().includes(query) ||
                w.wallet_number.toLowerCase().includes(query)
            );
        }

        renderWalletsGrid(filteredWallets);
    }

    function renderWalletsGrid(list) {
        const grid = document.getElementById('wallets-grid');
        grid.innerHTML = '';

        if (list.length === 0) {
            grid.innerHTML = `
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-wallet text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No wallets found</h3>
                <p class="text-gray-500 mb-6">Create a new wallet or adjust your search filters</p>
                <button onclick="showCreateWalletForm()" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Create Wallet</button>
            </div>
        `;
            return;
        }

        const isDesktop = window.innerWidth >= 1024;

        if (isDesktop) {
            const tableHtml = `
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full" id="wallets-table">
                    <thead class="bg-user-accent border-b border-gray-200 sticky top-0">
                        <tr>
                            <th data-column="details" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Wallet Details</th>
                            <th data-column="owner" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Owner Type</th>
                            <th data-column="status" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th data-column="balance" class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Balance</th>
                            <th data-column="actions" class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${list.map((wallet, index) => {
                const maxDetailsLength = 30;
                let displayName = wallet.wallet_name;
                if (displayName.length > maxDetailsLength) {
                    displayName = displayName.substring(0, maxDetailsLength) + '...';
                }

                return `
                                <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
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
                                    <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="showWalletStatement('${wallet.wallet_number}', '${wallet.wallet_name}')" 
                                                class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                                                title="View Statement">
                                                <i class="fas fa-receipt text-xs"></i>
                                            </button>
                                            <button onclick="editWallet('${wallet.wallet_number}')" 
                                                class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                                                title="Edit Wallet">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            ${wallet.owner_type === 'PLATFORM' ? `
                                                <button onclick="deleteWallet('${wallet.wallet_number}')" 
                                                    class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                                    title="Delete Wallet">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            ` : ''}
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
            applyColumnVisibility('wallets-table', visibleWalletColumns);
        } else {
            const gridHtml = `
            <div class="grid grid-cols-1 gap-4">
                ${list.map(wallet => `
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center ${wallet.owner_type === 'USER' ? 'bg-green-100' :
                    wallet.owner_type === 'VENDOR' ? 'bg-purple-100' : 'bg-cyan-100'
                }">
                                    <i class="${wallet.owner_type === 'USER' ? 'fas fa-user text-green-600' :
                    wallet.owner_type === 'VENDOR' ? 'fas fa-store text-purple-600' : 'fas fa-building text-cyan-600'
                }"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">${wallet.wallet_name}</h3>
                                    <p class="text-sm text-gray-500 font-mono">${wallet.wallet_number}</p>
                                </div>
                            </div>
                            ${getStatusBadge(wallet.status)}
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Owner Type:</span>
                                ${getOwnerTypeBadge(wallet.owner_type)}
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Balance:</span>
                                <span class="font-semibold text-lg text-gray-900">UGX ${formatCurrency(wallet.current_balance)}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Created:</span>
                                <span class="text-sm text-gray-500">${new Date(wallet.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                            <button onclick="showWalletStatement('${wallet.wallet_number}', '${wallet.wallet_name}')" class="flex-1 px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                                <i class="fas fa-receipt mr-1"></i>Statement
                            </button>
                            <button onclick="editWallet('${wallet.wallet_number}')" class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            ${wallet.owner_type === 'PLATFORM' ? `
                                <button onclick="deleteWallet('${wallet.wallet_number}')" class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
            grid.innerHTML = gridHtml;
        }

        setTimeout(updateTabHeights, 100);
    }

    function renderPlatformSettings() {
        const grid = document.getElementById('settings-grid');
        grid.innerHTML = '';

        if (platformSettings.length === 0) {
            grid.innerHTML = `
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cogs text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No platform settings found</h3>
                <p class="text-gray-500 mb-6">Add platform account settings to get started</p>
                <button onclick="showCreateSettingForm()" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Add Setting</button>
            </div>
        `;
            return;
        }

        const isDesktop = window.innerWidth >= 1024;

        if (isDesktop) {
            const tableHtml = `
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full" id="settings-table">
                    <thead class="bg-user-accent border-b border-gray-200 sticky top-0">
                        <tr>
                            <th data-column="platformaccount" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Platform Account</th>
                            <th data-column="type" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Type</th>
                            <th data-column="created" class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Created</th>
                            <th data-column="actions" class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        ${platformSettings.map((setting, index) => {
                const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
                const walletName = wallet ? wallet.wallet_name : setting.wallet_number;

                const maxDetailsLength = 25;
                let displayName = walletName;
                if (displayName.length > maxDetailsLength) {
                    displayName = displayName.substring(0, maxDetailsLength) + '...';
                }

                return `
                                <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
                                    <td data-column="platformaccount" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center bg-cyan-100">
                                                <i class="fas fa-building text-cyan-600 text-xs"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-medium text-gray-900 leading-tight" title="${walletName}">${displayName}</div>
                                                <div class="text-xs text-gray-500 mt-0.5 font-mono">${setting.wallet_number}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-column="type" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${getTypeBadge(setting.type)}
                                    </td>
                                    <td data-column="created" class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        ${new Date(setting.created_at).toLocaleDateString()}
                                    </td>
                                    <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        <div class="flex items-center justify-center gap-1">
                                            <button onclick="editSetting('${setting.id}')" 
                                                class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                                                title="Edit Setting">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button onclick="deleteSetting('${setting.id}')" 
                                                class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                                title="Delete Setting">
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
            applyColumnVisibility('settings-table', visibleSettingsColumns);
        } else {
            const gridHtml = `
            <div class="grid grid-cols-1 gap-4">
                ${platformSettings.map(setting => {
                const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
                const walletName = wallet ? wallet.wallet_name : setting.wallet_number;

                return `
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-cyan-100">
                                        <i class="fas fa-building text-cyan-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">${walletName}</h3>
                                        <p class="text-sm text-gray-500 font-mono">${setting.wallet_number}</p>
                                    </div>
                                </div>
                                ${getTypeBadge(setting.type)}
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Created:</span>
                                    <span class="text-sm text-gray-500">${new Date(setting.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                                <button onclick="editSetting('${setting.id}')" class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button onclick="deleteSetting('${setting.id}')" class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                </button>
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

    function renderCreditAssignments() {
        const grid = document.getElementById('credit-assignments-grid');
        const searchTerm = document.getElementById('searchCreditAssignments').value.toLowerCase();

        const filteredAssignments = creditAssignments.filter(assignment => {
            const setting = creditSettings.find(s => s.id === assignment.credit_setting_id);

            return (assignment.wallet_name && assignment.wallet_name.toLowerCase().includes(searchTerm)) ||
                (setting && setting.setting_name.toLowerCase().includes(searchTerm)) ||
                (setting && setting.category.toLowerCase().includes(searchTerm));
        });

        if (filteredAssignments.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-link text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No assignments found</h3>
                    <p class="text-gray-500 mb-6">No credit setting assignments available</p>
                    <button onclick="showCreateCreditAssignmentForm()" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Assign Credit Setting</button>
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
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Platform Wallet</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Credit Setting</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Category</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Value</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Assigned</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            ${filteredAssignments.map((assignment, index) => {
                const setting = creditSettings.find(s => s.id === assignment.credit_setting_id);

                if (!setting) return '';

                return `
                                    <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
                                        <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-lg flex items-center justify-center bg-cyan-100">
                                                    <i class="fas fa-building text-cyan-600 text-xs"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-medium text-gray-900 leading-tight">${assignment.wallet_name}</div>
                                                    <div class="text-xs text-gray-500 mt-0.5 font-mono">${assignment.wallet_number}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                            <div class="font-medium text-gray-900">${setting.setting_name}</div>
                                            ${setting.description ? `<div class="text-gray-500 mt-0.5">${setting.description}</div>` : ''}
                                        </td>
                                        <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                            ${getCategoryBadge(setting.category)}
                                        </td>
                                        <td class="px-3 py-2 text-xs font-medium ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                            ${formatValue(setting.setting_type, setting.setting_value)}
                                        </td>
                                        <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                            ${new Date(assignment.created_at).toLocaleDateString()}
                                        </td>
                                        <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                            <div class="flex items-center justify-center">
                                                <button onclick="showConfirmDeleteModal('${assignment.id}')" 
                                                    class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                                    title="Remove Assignment">
                                                    <i class="fas fa-unlink text-xs"></i>
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
        } else {
            const gridHtml = `
                <div class="grid grid-cols-1 gap-4">
                    ${filteredAssignments.map(assignment => {
                const setting = creditSettings.find(s => s.id === assignment.credit_setting_id);

                if (!setting) return '';

                return `
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-cyan-100">
                                            <i class="fas fa-building text-cyan-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-1">${assignment.wallet_name}</h3>
                                            <p class="text-sm text-gray-500 font-mono">${assignment.wallet_number}</p>
                                        </div>
                                    </div>
                                    ${getCategoryBadge(setting.category)}
                                </div>
                                
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Credit Setting:</span>
                                        <span class="font-medium text-gray-900">${setting.setting_name}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Value:</span>
                                        <span class="font-semibold text-gray-900">${formatValue(setting.setting_type, setting.setting_value)}</span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Assigned:</span>
                                        <span class="text-sm text-gray-500">${new Date(assignment.created_at).toLocaleDateString()}</span>
                                    </div>
                                    
                                    ${setting.description ? `
                                        <div class="pt-2 border-t border-gray-200">
                                            <span class="text-sm text-gray-600">Description:</span>
                                            <p class="text-sm text-gray-800 mt-1">${setting.description}</p>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                                    <button onclick="showConfirmDeleteModal('${assignment.id}')" class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                        <i class="fas fa-unlink mr-1"></i>Remove Assignment
                                    </button>
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
        const savedWalletColumns = localStorage.getItem(WALLETS_COLUMNS_STORAGE_KEY);
        const savedStatementColumns = localStorage.getItem(STATEMENT_COLUMNS_STORAGE_KEY);
        const savedSettingsColumns = localStorage.getItem(SETTINGS_COLUMNS_STORAGE_KEY);

        if (savedWalletColumns) {
            visibleWalletColumns = JSON.parse(savedWalletColumns);
        }

        if (savedStatementColumns) {
            visibleStatementColumns = JSON.parse(savedStatementColumns);
        }

        if (savedSettingsColumns) {
            visibleSettingsColumns = JSON.parse(savedSettingsColumns);
        }

        updateColumnCheckboxes();
    }

    function saveColumnVisibility() {
        localStorage.setItem(WALLETS_COLUMNS_STORAGE_KEY, JSON.stringify(visibleWalletColumns));
        localStorage.setItem(STATEMENT_COLUMNS_STORAGE_KEY, JSON.stringify(visibleStatementColumns));
        localStorage.setItem(SETTINGS_COLUMNS_STORAGE_KEY, JSON.stringify(visibleSettingsColumns));
    }

    function updateColumnCheckboxes() {
        document.querySelectorAll('.column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleWalletColumns.includes(column);
        });

        document.querySelectorAll('.statement-column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleStatementColumns.includes(column);
        });

        document.querySelectorAll('.settings-column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleSettingsColumns.includes(column);
        });
    }

    function applyColumnVisibility(tableId, visibleColumns) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const headers = table.querySelectorAll('thead th[data-column]');
        headers.forEach(header => {
            const column = header.getAttribute('data-column');
            if (visibleColumns.includes(column)) {
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

    function toggleSettingsColumnSelector() {
        const selector = document.getElementById('settingsColumnSelector');
        selector.classList.toggle('hidden');
    }

    function showCreateWalletForm() {
        const modal = document.getElementById('createWalletModal');
        const form = document.getElementById('createWalletForm');
        form.reset();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCreateWalletForm() {
        document.getElementById('createWalletModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showCreateSettingForm() {
        loadPlatformAccountsDropdown();

        const modal = document.getElementById('createSettingModal');
        const form = document.getElementById('createSettingForm');
        form.reset();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCreateSettingForm() {
        document.getElementById('createSettingModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showCreateCreditAssignmentForm() {
        loadCreditAssignmentDropdowns();

        const modal = document.getElementById('createCreditAssignmentModal');
        const form = document.getElementById('createCreditAssignmentForm');
        form.reset();

        // Hide credit setting details initially
        document.getElementById('creditSettingDetails').classList.add('hidden');

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCreateCreditAssignmentForm() {
        document.getElementById('createCreditAssignmentModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showConfirmDeleteModal(assignmentId) {
        pendingDeleteAssignmentId = assignmentId;
        const modal = document.getElementById('confirmDeleteModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideConfirmDeleteModal() {
        document.getElementById('confirmDeleteModal').classList.add('hidden');
        document.body.style.overflow = '';
        pendingDeleteAssignmentId = null;
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

    async function createWallet() {
        const name = document.getElementById('walletName').value.trim();
        if (!name) {
            showMessage('error', 'Validation Error', 'Please enter a wallet name');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=createZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ owner_type: 'PLATFORM', wallet_name: name })
            });
            const data = await response.json();

            if (data.success) {
                hideCreateWalletForm();
                await loadWallets();
                showMessage('success', 'Wallet Created', 'Platform wallet created successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to create wallet');
            }
        } catch (error) {
            console.error('Error creating wallet:', error);
            showMessage('error', 'Error', 'Failed to create wallet');
        }
    }

    function editWallet(walletNumber) {
        const wallet = wallets.find(w => w.wallet_number === walletNumber);
        if (!wallet) return;

        document.getElementById('editWalletId').value = wallet.wallet_number;
        document.getElementById('editWalletOwnerTypeDisplay').textContent = wallet.owner_type === 'USER' ? 'User Wallet' :
            wallet.owner_type === 'VENDOR' ? 'Vendor Wallet' : 'Platform Wallet';
        document.getElementById('editWalletName').value = wallet.wallet_name;
        document.getElementById('editWalletStatus').value = wallet.status;

        const modal = document.getElementById('editWalletModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditWalletForm() {
        document.getElementById('editWalletModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    async function updateWallet() {
        const id = document.getElementById('editWalletId').value;
        const name = document.getElementById('editWalletName').value.trim();
        const status = document.getElementById('editWalletStatus').value;

        if (!name || !status) {
            showMessage('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=updateZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_number: id, wallet_name: name, status: status })
            });
            const data = await response.json();

            if (data.success) {
                hideEditWalletForm();
                await loadWallets();
                showMessage('success', 'Wallet Updated', 'Wallet updated successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to update wallet');
            }
        } catch (error) {
            console.error('Error updating wallet:', error);
            showMessage('error', 'Error', 'Failed to update wallet');
        }
    }

    async function deleteWallet(walletNumber) {
        if (!confirm('Are you sure you want to delete this wallet? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=deleteZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_number: walletNumber })
            });
            const data = await response.json();

            if (data.success) {
                await loadWallets();
                showMessage('success', 'Wallet Deleted', 'Wallet deleted successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to delete wallet');
            }
        } catch (error) {
            console.error('Error deleting wallet:', error);
            showMessage('error', 'Error', 'Failed to delete wallet');
        }
    }

    async function createPlatformSetting() {
        const accountId = document.getElementById('platformAccountSelect').value;
        const type = document.getElementById('settingType').value;

        if (!accountId || !type) {
            showMessage('error', 'Validation Error', 'Please select both platform account and setting type');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'add',
                    wallet_number: accountId,
                    type: type
                })
            });
            const data = await response.json();

            if (data.success) {
                hideCreateSettingForm();
                await loadPlatformSettings();
                showMessage('success', 'Setting Created', 'Platform account setting created successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to create platform setting');
            }
        } catch (error) {
            console.error('Error creating platform setting:', error);
            showMessage('error', 'Error', 'Failed to create platform setting');
        }
    }

    function editSetting(settingId) {
        const setting = platformSettings.find(s => s.id === settingId);
        if (!setting) return;

        const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
        const walletName = wallet ? wallet.wallet_name : setting.wallet_number;

        document.getElementById('editSettingId').value = setting.id;
        document.getElementById('editSettingAccountDisplay').textContent = walletName;
        document.getElementById('editSettingType').value = setting.type;

        const modal = document.getElementById('editSettingModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditSettingForm() {
        document.getElementById('editSettingModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    async function updatePlatformSetting() {
        const id = document.getElementById('editSettingId').value;
        const type = document.getElementById('editSettingType').value;

        if (!type) {
            showMessage('error', 'Validation Error', 'Please select a setting type');
            return;
        }

        const setting = platformSettings.find(s => s.id === id);
        if (!setting) return;

        try {
            const response = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'update',
                    id: id,
                    wallet_number: setting.wallet_number,
                    type: type
                })
            });
            const data = await response.json();

            if (data.success) {
                hideEditSettingForm();
                await loadPlatformSettings();
                showMessage('success', 'Setting Updated', 'Platform account setting updated successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to update platform setting');
            }
        } catch (error) {
            console.error('Error updating platform setting:', error);
            showMessage('error', 'Error', 'Failed to update platform setting');
        }
    }

    async function deleteSetting(settingId) {
        if (!confirm('Are you sure you want to delete this platform setting? This action cannot be undone.')) {
            return;
        }

        const setting = platformSettings.find(s => s.id === settingId);
        if (!setting) return;

        try {
            const response = await fetch(`${API_URL}?action=managePlatformAccounts`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    operation: 'remove',
                    id: settingId,
                    wallet_number: setting.wallet_number
                })
            });
            const data = await response.json();

            if (data.success) {
                await loadPlatformSettings();
                showMessage('success', 'Setting Deleted', 'Platform account setting deleted successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to delete platform setting');
            }
        } catch (error) {
            console.error('Error deleting platform setting:', error);
            showMessage('error', 'Error', 'Failed to delete platform setting');
        }
    }

    async function createCreditAssignment() {
        const creditSettingId = document.getElementById('assignmentCreditSettingSelect').value;
        const walletId = document.getElementById('assignmentWalletSelect').value;

        if (!creditSettingId || !walletId) {
            showMessage('error', 'Validation Error', 'Please select both credit setting and platform wallet');
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=createCreditAssignment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    credit_setting_id: creditSettingId,
                    wallet_id: walletId
                })
            });
            const data = await response.json();

            if (data.success) {
                hideCreateCreditAssignmentForm();
                await loadCreditAssignments();
                await loadCreditSettings(); // Reload to update available settings
                showMessage('success', 'Assignment Created', 'Credit setting assigned to wallet successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to create credit assignment');
            }
        } catch (error) {
            console.error('Error creating credit assignment:', error);
            showMessage('error', 'Error', 'Failed to create credit assignment');
        }
    }

    async function deleteCreditAssignment(assignmentId) {
        if (!assignmentId) {
            assignmentId = pendingDeleteAssignmentId;
        }

        if (!assignmentId) return;

        try {
            const response = await fetch(`${API_URL}?action=deleteCreditAssignment`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: assignmentId })
            });
            const data = await response.json();

            if (data.success) {
                hideConfirmDeleteModal();
                await loadCreditAssignments();
                await loadCreditSettings(); // Reload to update available settings
                showMessage('success', 'Assignment Removed', 'Credit assignment removed successfully');
            } else {
                showMessage('error', 'Error', data.message || 'Failed to remove credit assignment');
            }
        } catch (error) {
            console.error('Error removing credit assignment:', error);
            showMessage('error', 'Error', 'Failed to remove credit assignment');
        }
    }

    async function showWalletStatement(walletNumber, walletName) {
        currentStatementWalletId = walletNumber;
        document.getElementById('statementWalletName').textContent = walletName;
        document.getElementById('walletStatementModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        document.getElementById('statementDateFilter').value = 'all';
        await loadWalletStatement();
    }

    function hideWalletStatementModal() {
        document.getElementById('walletStatementModal').classList.add('hidden');
        document.body.style.overflow = '';
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
            const response = await fetch(`${API_URL}?action=getWalletStatement`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(params)
            });
            const data = await response.json();

            document.getElementById('statementLoading').classList.add('hidden');

            if (data.success && data.statement) {
                const transformedTransactions = transformStatementData(data.statement);
                currentWalletStatement = transformedTransactions;

                if (currentWalletStatement.length > 0) {
                    renderWalletStatement(currentWalletStatement);
                    displayStatementView();
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

    function displayStatementView() {
        const tableWrapper = document.getElementById('statementTable');
        const mobileWrapper = document.getElementById('statementMobile');
        if (window.innerWidth >= 1024) {
            tableWrapper.classList.remove('hidden');
            mobileWrapper.classList.add('hidden');
        } else {
            tableWrapper.classList.add('hidden');
            mobileWrapper.classList.remove('hidden');
        }
    }

    function transformStatementData(statement) {
        const transformedEntries = [];

        const sortedStatement = statement.sort((a, b) =>
            new Date(b.transaction.created_at) - new Date(a.transaction.created_at)
        );

        sortedStatement.forEach(transactionBlock => {
            const transaction = transactionBlock.transaction;

            if (transaction.entries && transaction.entries.length > 0) {
                const reversedEntries = [...transaction.entries].reverse();

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
                <div class="max-w-[250px] overflow-hidden text-ellipsis" title="${description}">
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

        description += ` (TXN: ${entry.transaction_id}, Total: UGX ${formatCurrency(entry.amount_total)})`;

        return description;
    }

    function filterWallets() {
        renderFilteredWallets();
    }

    function filterSettings() {
        const query = document.getElementById('searchSettings').value.trim().toLowerCase();
        if (query) {
            const filtered = platformSettings.filter(setting => {
                const wallet = wallets.find(w => w.wallet_number === setting.wallet_number);
                const walletName = wallet ? wallet.wallet_name : setting.wallet_number;
                return walletName.toLowerCase().includes(query) ||
                    setting.wallet_number.toLowerCase().includes(query) ||
                    setting.type.toLowerCase().includes(query);
            });
            renderPlatformSettings(filtered);
        } else {
            renderPlatformSettings();
        }
    }

    function filterCreditAssignments() {
        renderCreditAssignments();
    }

    function onCreditSettingChange() {
        const select = document.getElementById('assignmentCreditSettingSelect');
        const detailsDiv = document.getElementById('creditSettingDetails');

        if (select.value) {
            const option = select.options[select.selectedIndex];

            document.getElementById('settingCategory').textContent = option.dataset.category || '';
            document.getElementById('settingValue').textContent = formatValue(option.dataset.type, parseFloat(option.dataset.value));
            document.getElementById('settingApplicableTo').textContent = option.dataset.applicableTo || 'All';

            detailsDiv.classList.remove('hidden');
        } else {
            detailsDiv.classList.add('hidden');
        }
    }

    document.addEventListener('click', function (event) {
        const walletSelector = document.getElementById('columnSelector');
        const walletBtn = document.getElementById('viewColumnsBtn');
        const statementSelector = document.getElementById('statementColumnSelector');
        const statementBtn = document.getElementById('viewStatementColumnsBtn');
        const settingsSelector = document.getElementById('settingsColumnSelector');
        const settingsBtn = document.getElementById('viewSettingsColumnsBtn');
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (walletSelector && walletBtn && !walletSelector.contains(event.target) && !walletBtn.contains(event.target)) {
            walletSelector.classList.add('hidden');
        }

        if (statementSelector && statementBtn && !statementSelector.contains(event.target) && !statementBtn.contains(event.target)) {
            statementSelector.classList.add('hidden');
        }

        if (settingsSelector && settingsBtn && !settingsSelector.contains(event.target) && !settingsBtn.contains(event.target)) {
            settingsSelector.classList.add('hidden');
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
                if (!visibleWalletColumns.includes(column)) {
                    visibleWalletColumns.push(column);
                }
            } else {
                if (visibleWalletColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleWalletColumns = visibleWalletColumns.filter(col => col !== column);
            }

            applyColumnVisibility('wallets-table', visibleWalletColumns);
            saveColumnVisibility();
        }

        if (event.target.classList.contains('statement-column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleStatementColumns.includes(column)) {
                    visibleStatementColumns.push(column);
                }
            } else {
                if (visibleStatementColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleStatementColumns = visibleStatementColumns.filter(col => col !== column);
            }

            applyColumnVisibility('statementTableElement', visibleStatementColumns);
            saveColumnVisibility();
        }

        if (event.target.classList.contains('settings-column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleSettingsColumns.includes(column)) {
                    visibleSettingsColumns.push(column);
                }
            } else {
                if (visibleSettingsColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleSettingsColumns = visibleSettingsColumns.filter(col => col !== column);
            }

            applyColumnVisibility('settings-table', visibleSettingsColumns);
            saveColumnVisibility();
        }

        if (event.target.id === 'assignmentCreditSettingSelect') {
            onCreditSettingChange();
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        loadColumnVisibility();
        await loadWallets();
        await loadCreditSettings();
        switchWalletTab('PLATFORM');

        document.getElementById('create-wallet-btn').addEventListener('click', showCreateWalletForm);
        document.getElementById('create-setting-btn').addEventListener('click', showCreateSettingForm);
        document.getElementById('create-credit-assignment-btn').addEventListener('click', showCreateCreditAssignmentForm);
        document.getElementById('submitWalletForm').addEventListener('click', createWallet);
        document.getElementById('updateWalletForm').addEventListener('click', updateWallet);
        document.getElementById('submitSettingForm').addEventListener('click', createPlatformSetting);
        document.getElementById('updateSettingForm').addEventListener('click', updatePlatformSetting);
        document.getElementById('submitCreditAssignmentForm').addEventListener('click', createCreditAssignment);
        document.getElementById('confirmDeleteButton').addEventListener('click', () => deleteCreditAssignment());
        document.getElementById('searchWallets').addEventListener('input', filterWallets);
        document.getElementById('searchSettings').addEventListener('input', filterSettings);
        document.getElementById('searchCreditAssignments').addEventListener('input', filterCreditAssignments);
        document.getElementById('statementDateFilter').addEventListener('change', loadWalletStatement);
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchWalletTab(tab);
                toggleMobileTabDropdown();
            });
        });

        window.addEventListener('resize', () => {
            updateTabHeights();
            displayStatementView();
            renderFilteredWallets();
            renderPlatformSettings();
            renderCreditAssignments();
        });

        setTimeout(updateTabHeights, 500);
    });

    window.switchWalletTab = switchWalletTab;
    window.showCreateWalletForm = showCreateWalletForm;
    window.hideCreateWalletForm = hideCreateWalletForm;
    window.showCreateSettingForm = showCreateSettingForm;
    window.hideCreateSettingForm = hideCreateSettingForm;
    window.showCreateCreditAssignmentForm = showCreateCreditAssignmentForm;
    window.hideCreateCreditAssignmentForm = hideCreateCreditAssignmentForm;
    window.hideEditWalletForm = hideEditWalletForm;
    window.hideEditSettingForm = hideEditSettingForm;
    window.hideMessageModal = hideMessageModal;
    window.hideConfirmDeleteModal = hideConfirmDeleteModal;
    window.showConfirmDeleteModal = showConfirmDeleteModal;
    window.editWallet = editWallet;
    window.deleteWallet = deleteWallet;
    window.editSetting = editSetting;
    window.deleteSetting = deleteSetting;
    window.showWalletStatement = showWalletStatement;
    window.hideWalletStatementModal = hideWalletStatementModal;
    window.toggleColumnSelector = toggleColumnSelector;
    window.toggleStatementColumnSelector = toggleStatementColumnSelector;
    window.toggleSettingsColumnSelector = toggleSettingsColumnSelector;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>