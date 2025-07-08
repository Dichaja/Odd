<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit Management';
$activeNav = 'zzimba-credit';
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
                <h1 class="text-2xl font-bold text-gray-900">Zzimba Credit Management</h1>
                <p class="text-gray-600 mt-1">Manage platform account top-ups and credit transactions</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Credits</p>
                        <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="total-credits">UGX 2,450,000.00
                        </p>
                        <p class="text-sm font-medium text-blue-700 whitespace-nowrap">156 Transactions</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus-circle text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">This Month</p>
                        <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="month-credits">UGX 485,000.00
                        </p>
                        <p class="text-sm font-medium text-green-700 whitespace-nowrap">32 Transactions</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Pending</p>
                        <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="pending-credits">UGX
                            125,000.00</p>
                        <p class="text-sm font-medium text-purple-700 whitespace-nowrap">8 Transactions</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Average</p>
                        <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="avg-credits">UGX 15,705.00
                        </p>
                        <p class="text-sm font-medium text-orange-700 whitespace-nowrap">Per Transaction</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-8">
            <!-- Desktop Navigation -->
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div id="desktop-nav">
                    <nav class="space-y-2" aria-label="Credit Navigation">
                        <button id="transactions-tab"
                            class="tab-button active w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 bg-primary/10 text-primary border border-primary/20"
                            onclick="switchCreditTab('transactions')">
                            <i class="fas fa-list"></i>
                            <span>Credit Transactions</span>
                        </button>
                        <button id="topup-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchCreditTab('topup')">
                            <i class="fas fa-plus-circle"></i>
                            <span>New Top-up</span>
                        </button>
                        <button id="reports-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchCreditTab('reports')">
                            <i class="fas fa-chart-bar"></i>
                            <span>Reports</span>
                        </button>
                        <button id="settings-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchCreditTab('settings')">
                            <i class="fas fa-cogs"></i>
                            <span>Credit Settings</span>
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <!-- Mobile Navigation -->
                <div class="lg:hidden mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                        <div class="relative">
                            <button id="mobile-tab-toggle"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-list text-primary"></i>
                                    <span id="mobile-tab-label" class="font-medium text-gray-900">Credit
                                        Transactions</span>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                    id="mobile-tab-chevron"></i>
                            </button>

                            <div id="mobile-tab-dropdown"
                                class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                                <div class="py-2">
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="transactions">
                                        <i class="fas fa-list text-blue-600"></i>
                                        <span>Credit Transactions</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="topup">
                                        <i class="fas fa-plus-circle text-green-600"></i>
                                        <span>New Top-up</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="reports">
                                        <i class="fas fa-chart-bar text-purple-600"></i>
                                        <span>Reports</span>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="settings">
                                        <i class="fas fa-cogs text-gray-600"></i>
                                        <span>Credit Settings</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Transactions Tab -->
                <div id="transactions-content" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="transactions-container">
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

                                <div
                                    class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <select id="statusFilter"
                                        class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm">
                                        <option value="all">All Status</option>
                                        <option value="completed">Completed</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                    </select>

                                    <select id="dateFilter"
                                        class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                    </select>

                                    <button id="export-btn"
                                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 flex items-center justify-center gap-2 font-medium w-full sm:w-auto">
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

                <!-- New Top-up Tab -->
                <div id="topup-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-plus-circle text-primary"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-secondary font-rubik">Create New Top-up</h3>
                            </div>
                        </div>

                        <div class="p-6">
                            <form id="topupForm" class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <label for="platformAccount"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Platform
                                            Account</label>
                                        <select id="platformAccount" name="platformAccount"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                            required>
                                            <option value="">Select Platform Account</option>
                                            <option value="operations">Operations Account</option>
                                            <option value="communications">Communications Account</option>
                                            <option value="services">Services Account</option>
                                            <option value="withholding">Withholding Account</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="topupAmount"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Amount (UGX)</label>
                                        <input type="number" id="topupAmount" name="topupAmount"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                            placeholder="Enter amount" min="1000" step="100" required>
                                    </div>

                                    <div>
                                        <label for="paymentMethod"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Payment
                                            Method</label>
                                        <select id="paymentMethod" name="paymentMethod"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                            required>
                                            <option value="">Select Payment Method</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="card_payment">Card Payment</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="reference"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Reference
                                            Number</label>
                                        <input type="text" id="reference" name="reference"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                            placeholder="Enter reference number">
                                    </div>
                                </div>

                                <div>
                                    <label for="description"
                                        class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                    <textarea id="description" name="description" rows="3"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                        placeholder="Enter description or notes..."></textarea>
                                </div>

                                <div class="flex gap-3 pt-4">
                                    <button type="button" onclick="resetTopupForm()"
                                        class="flex-1 px-6 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                                        Reset
                                    </button>
                                    <button type="submit"
                                        class="flex-1 px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                                        Process Top-up
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reports Tab -->
                <div id="reports-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-chart-bar text-primary"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-secondary font-rubik">Credit Reports</h3>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                    <h4 class="text-lg font-semibold text-blue-900 mb-4">Monthly Summary</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">January 2024:</span>
                                            <span class="font-semibold text-blue-900">UGX 425,000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">February 2024:</span>
                                            <span class="font-semibold text-blue-900">UGX 380,500</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">March 2024:</span>
                                            <span class="font-semibold text-blue-900">UGX 485,000</span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                    <h4 class="text-lg font-semibold text-green-900 mb-4">Account Breakdown</h4>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-green-700">Operations:</span>
                                            <span class="font-semibold text-green-900">UGX 1,200,000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-green-700">Communications:</span>
                                            <span class="font-semibold text-green-900">UGX 850,000</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-green-700">Services:</span>
                                            <span class="font-semibold text-green-900">UGX 400,000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button
                                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                                    <i class="fas fa-download mr-2"></i>Download Report
                                </button>
                                <button
                                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                                    <i class="fas fa-print mr-2"></i>Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div id="settings-content" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-cogs text-primary"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-secondary font-rubik">Credit Settings</h3>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <h4 class="font-semibold text-gray-900 mb-3">Minimum Top-up Amounts</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Operations Account:</span>
                                                <span class="font-medium">UGX 10,000</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Communications Account:</span>
                                                <span class="font-medium">UGX 5,000</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Services Account:</span>
                                                <span class="font-medium">UGX 15,000</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4">
                                        <h4 class="font-semibold text-gray-900 mb-3">Auto Top-up Settings</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Auto Top-up:</span>
                                                <span class="font-medium text-green-600">Enabled</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Threshold:</span>
                                                <span class="font-medium">UGX 50,000</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Top-up Amount:</span>
                                                <span class="font-medium">UGX 200,000</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <button
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                                        <i class="fas fa-save mr-2"></i>Update Settings
                                    </button>
                                    <button
                                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-medium">
                                        <i class="fas fa-undo mr-2"></i>Reset to Default
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="transactionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideTransactionModal()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Details</h3>
            </div>
            <button onclick="hideTransactionModal()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <div id="transactionDetails" class="space-y-6">
                <!-- Transaction details will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
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
    let currentTab = 'transactions';
    let transactions = [];
    let filteredTransactions = [];

    // Dummy data for transactions
    const dummyTransactions = [
        {
            id: 'TXN001',
            account: 'Operations Account',
            amount: 150000,
            status: 'completed',
            method: 'Mobile Money',
            reference: 'MM240315001',
            description: 'Monthly operations top-up',
            date: '2024-03-15T10:30:00Z',
            processedBy: 'John Doe'
        },
        {
            id: 'TXN002',
            account: 'Communications Account',
            amount: 75000,
            status: 'pending',
            method: 'Bank Transfer',
            reference: 'BT240314002',
            description: 'SMS service credit',
            date: '2024-03-14T14:20:00Z',
            processedBy: 'Jane Smith'
        },
        {
            id: 'TXN003',
            account: 'Services Account',
            amount: 200000,
            status: 'completed',
            method: 'Card Payment',
            reference: 'CP240313003',
            description: 'Service delivery top-up',
            date: '2024-03-13T09:15:00Z',
            processedBy: 'Mike Johnson'
        },
        {
            id: 'TXN004',
            account: 'Withholding Account',
            amount: 50000,
            status: 'failed',
            method: 'Mobile Money',
            reference: 'MM240312004',
            description: 'Tax withholding credit',
            date: '2024-03-12T16:45:00Z',
            processedBy: 'Sarah Wilson'
        },
        {
            id: 'TXN005',
            account: 'Operations Account',
            amount: 300000,
            status: 'completed',
            method: 'Bank Transfer',
            reference: 'BT240311005',
            description: 'Quarterly operations budget',
            date: '2024-03-11T11:00:00Z',
            processedBy: 'David Brown'
        },
        {
            id: 'TXN006',
            account: 'Communications Account',
            amount: 25000,
            status: 'pending',
            method: 'Mobile Money',
            reference: 'MM240310006',
            description: 'Emergency communication credit',
            date: '2024-03-10T13:30:00Z',
            processedBy: 'Lisa Davis'
        }
    ];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getStatusBadge(status) {
        const statusClasses = {
            'completed': 'bg-green-100 text-green-800',
            'pending': 'bg-yellow-100 text-yellow-800',
            'failed': 'bg-red-100 text-red-800'
        };
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        const className = statusClasses[status] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${className}">${statusText}</span>`;
    }

    function getMethodBadge(method) {
        const methodClasses = {
            'Mobile Money': 'bg-blue-100 text-blue-800',
            'Bank Transfer': 'bg-purple-100 text-purple-800',
            'Card Payment': 'bg-indigo-100 text-indigo-800'
        };
        const className = methodClasses[method] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${className}">${method}</span>`;
    }

    function updateTabHeights() {
        const desktopNav = document.getElementById('desktop-nav');
        const transactionsContainer = document.getElementById('transactions-container');

        if (desktopNav && window.innerWidth >= 1024 && transactionsContainer) {
            const containerHeight = transactionsContainer.offsetHeight;
            desktopNav.style.height = containerHeight + 'px';
        } else if (desktopNav) {
            desktopNav.style.height = 'auto';
        }
    }

    function switchCreditTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
        });

        // Hide all content
        document.getElementById('transactions-content').classList.add('hidden');
        document.getElementById('topup-content').classList.add('hidden');
        document.getElementById('reports-content').classList.add('hidden');
        document.getElementById('settings-content').classList.add('hidden');

        // Show selected content
        document.getElementById(`${tabName}-content`).classList.remove('hidden');

        // Update active tab
        const activeTab = document.getElementById(`${tabName}-tab`);
        if (activeTab) {
            activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
            activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
        }

        currentTab = tabName;

        // Update mobile tab label
        const tabLabels = {
            'transactions': { label: 'Credit Transactions', icon: 'fas fa-list' },
            'topup': { label: 'New Top-up', icon: 'fas fa-plus-circle' },
            'reports': { label: 'Reports', icon: 'fas fa-chart-bar' },
            'settings': { label: 'Credit Settings', icon: 'fas fa-cogs' }
        };
        const tabInfo = tabLabels[tabName];
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);

        if (tabName === 'transactions') {
            loadTransactions();
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

    function loadTransactions() {
        transactions = [...dummyTransactions];
        applyFilters();
    }

    function applyFilters() {
        const searchTerm = document.getElementById('searchTransactions').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;

        filteredTransactions = transactions.filter(transaction => {
            // Search filter
            const matchesSearch = !searchTerm ||
                transaction.id.toLowerCase().includes(searchTerm) ||
                transaction.account.toLowerCase().includes(searchTerm) ||
                transaction.reference.toLowerCase().includes(searchTerm) ||
                transaction.description.toLowerCase().includes(searchTerm);

            // Status filter
            const matchesStatus = statusFilter === 'all' || transaction.status === statusFilter;

            // Date filter (simplified for demo)
            const matchesDate = dateFilter === 'all' || true; // In real app, implement date filtering

            return matchesSearch && matchesStatus && matchesDate;
        });

        renderTransactions();
    }

    function renderTransactions() {
        const grid = document.getElementById('transactions-grid');

        if (filteredTransactions.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                    <p class="text-gray-500">Try adjusting your search or filter criteria</p>
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
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Transaction</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Account</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Amount</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Date</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            ${filteredTransactions.map((transaction, index) => `
                                <tr class="${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors">
                                    <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        <div class="text-xs font-medium text-gray-900">${transaction.id}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">${transaction.reference}</div>
                                    </td>
                                    <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        <div class="font-medium text-gray-900">${transaction.account}</div>
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs font-semibold text-gray-900 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        UGX ${formatCurrency(transaction.amount)}
                                    </td>
                                    <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${getStatusBadge(transaction.status)}
                                    </td>
                                    <td class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        ${getMethodBadge(transaction.method)}
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                                        ${new Date(transaction.date).toLocaleDateString()}
                                    </td>
                                    <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                                        <div class="flex items-center justify-center">
                                            <button onclick="showTransactionDetails('${transaction.id}')" 
                                                class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                                                title="View Details">
                                                <i class="fas fa-eye text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            grid.innerHTML = tableHtml;
        } else {
            const gridHtml = `
                <div class="grid grid-cols-1 gap-4">
                    ${filteredTransactions.map(transaction => `
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900 mb-1">${transaction.id}</h3>
                                    <p class="text-sm text-gray-500">${transaction.account}</p>
                                </div>
                                ${getStatusBadge(transaction.status)}
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Amount:</span>
                                    <span class="font-semibold text-lg text-gray-900">UGX ${formatCurrency(transaction.amount)}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Method:</span>
                                    ${getMethodBadge(transaction.method)}
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Reference:</span>
                                    <span class="text-sm text-gray-500 font-mono">${transaction.reference}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Date:</span>
                                    <span class="text-sm text-gray-500">${new Date(transaction.date).toLocaleDateString()}</span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                                <button onclick="showTransactionDetails('${transaction.id}')" class="flex-1 px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                                    <i class="fas fa-eye mr-1"></i>View Details
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

    function showTransactionDetails(transactionId) {
        const transaction = transactions.find(t => t.id === transactionId);
        if (!transaction) return;

        const detailsHtml = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm font-mono">${transaction.id}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Platform Account</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${transaction.account}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm font-semibold">UGX ${formatCurrency(transaction.amount)}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${getStatusBadge(transaction.status)}</div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${getMethodBadge(transaction.method)}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm font-mono">${transaction.reference}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${new Date(transaction.date).toLocaleString()}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Processed By</label>
                        <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${transaction.processedBy}</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm">${transaction.description}</div>
            </div>
        `;

        document.getElementById('transactionDetails').innerHTML = detailsHtml;
        document.getElementById('transactionModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideTransactionModal() {
        document.getElementById('transactionModal').classList.add('hidden');
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

    function resetTopupForm() {
        document.getElementById('topupForm').reset();
    }

    function processTopup(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const topupData = {
            account: formData.get('platformAccount'),
            amount: formData.get('topupAmount'),
            method: formData.get('paymentMethod'),
            reference: formData.get('reference'),
            description: formData.get('description')
        };

        // Simulate processing
        showMessage('success', 'Top-up Processed', 'The top-up request has been submitted successfully and is being processed.');

        // Reset form
        resetTopupForm();
    }

    // Event listeners
    document.addEventListener('click', function (event) {
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
            mobileDropdown.classList.add('hidden');
            document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        switchCreditTab('transactions');

        // Event listeners
        document.getElementById('searchTransactions').addEventListener('input', applyFilters);
        document.getElementById('statusFilter').addEventListener('change', applyFilters);
        document.getElementById('dateFilter').addEventListener('change', applyFilters);
        document.getElementById('topupForm').addEventListener('submit', processTopup);
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchCreditTab(tab);
                toggleMobileTabDropdown();
            });
        });

        window.addEventListener('resize', () => {
            updateTabHeights();
            if (currentTab === 'transactions') {
                renderTransactions();
            }
        });

        setTimeout(updateTabHeights, 500);
    });

    // Global functions
    window.switchCreditTab = switchCreditTab;
    window.showTransactionDetails = showTransactionDetails;
    window.hideTransactionModal = hideTransactionModal;
    window.hideMessageModal = hideMessageModal;
    window.resetTopupForm = resetTopupForm;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>