<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Approve Transactions';
$activeNav = 'approve-transactions';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}

function formatDateTime($dateTime)
{
    $date = new DateTime($dateTime);
    return $date->format('M j, Y g:i A');
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-4 border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Pending Transactions
                            </p>
                            <p class="text-lg font-bold text-yellow-900 whitespace-nowrap" id="pending-count">0</p>
                            <p class="text-sm font-medium text-yellow-700 whitespace-nowrap" id="pending-total">UGX 0.00
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-yellow-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Bank Transfers</p>
                            <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="bank-count">0</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="bank-total">UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-university text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Mobile Money</p>
                            <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="mobile-count">0</p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap" id="mobile-total">UGX 0.00
                            </p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="w-full lg:w-1/3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchTransactions"
                                class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Search transactions...">
                        </div>
                    </div>

                    <div class="hidden lg:block lg:flex-1"></div>

                    <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
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
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="datetime" checked>
                                        <span class="text-sm text-gray-700">Date/Time</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="amount" checked>
                                        <span class="text-sm text-gray-700">Amount</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="method" checked>
                                        <span class="text-sm text-gray-700">Payment Method</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="account" checked>
                                        <span class="text-sm text-gray-700">Cash Account</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="user" checked>
                                        <span class="text-sm text-gray-700">User/Vendor</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="actions" checked>
                                        <span class="text-sm text-gray-700">Actions</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <select id="filterTransactions"
                            class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium w-full sm:w-auto">
                            <option value="all">All Methods</option>
                            <option value="BANK">Bank Transfers</option>
                            <option value="MOBILE_MONEY">Mobile Money</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactions-table">
                    <thead class="bg-user-accent border-b border-gray-200">
                        <tr>
                            <th data-column="datetime"
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Date/Time</th>
                            <th data-column="amount"
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Amount</th>
                            <th data-column="method"
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Payment Method</th>
                            <th data-column="account"
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Cash Account</th>
                            <th data-column="user"
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                User/Vendor</th>
                            <th data-column="actions"
                                class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden p-4 space-y-4" id="transactions-mobile">
            </div>

            <div id="empty-state" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No pending transactions</h3>
                <p class="text-gray-500">All transactions have been processed</p>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="transactionDetailsModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden max-h-[95vh] flex flex-col">
        <div class="p-6 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-receipt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Details</h3>
                        <p class="text-sm text-gray-500" id="transactionReference"></p>
                    </div>
                </div>
                <button onclick="hideTransactionDetailsModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <div id="transactionDetailsContent" class="space-y-6">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideTransactionDetailsModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Close
                </button>
                <button id="rejectTransactionBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    Reject Transaction
                </button>
                <button id="approveTransactionBtn"
                    class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium">
                    Affirm Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="confirmationIcon" class="w-12 h-12 rounded-xl flex items-center justify-center">
                    <i id="confirmationIconClass" class="text-xl"></i>
                </div>
                <div>
                    <h3 id="confirmationTitle" class="text-lg font-semibold text-gray-900">Confirm Action</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p id="confirmationMessage" class="text-gray-600 mb-6">Are you sure you want to proceed?</p>
            <div class="flex gap-3">
                <button onclick="hideConfirmationModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="flex-1 px-4 py-3 rounded-xl transition-colors font-medium">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashTransactions.php';
    let pendingTransactions = [];
    let currentTransaction = null;
    let visibleColumns = ['datetime', 'amount', 'method', 'account', 'user', 'actions'];
    const COLUMNS_STORAGE_KEY = 'approve_transactions_columns';

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function getPaymentMethodBadge(method) {
        const badges = {
            'BANK': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-university mr-2"></i>Bank Transfer</span>',
            'MOBILE_MONEY': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-mobile-alt mr-2"></i>Mobile Money</span>'
        };
        return badges[method] || `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${method}</span>`;
    }

    async function fetchPendingTransactions() {
        try {
            const formData = new FormData();
            formData.append('action', 'listPending');

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                pendingTransactions = data.pending || [];
                renderTransactionsTable(pendingTransactions);
                updateQuickStats();
                adjustTableFontSize();
            }
        } catch (error) {
            console.error('Error fetching pending transactions:', error);
        }
    }

    function updateQuickStats() {
        const bankTransactions = pendingTransactions.filter(t => t.payment_method === 'BANK');
        const mobileTransactions = pendingTransactions.filter(t => t.payment_method === 'MOBILE_MONEY');

        const bankTotal = bankTransactions.reduce((sum, t) => sum + parseFloat(t.amount_total || 0), 0);
        const mobileTotal = mobileTransactions.reduce((sum, t) => sum + parseFloat(t.amount_total || 0), 0);
        const totalAmount = bankTotal + mobileTotal;

        document.getElementById('pending-count').textContent = pendingTransactions.length;
        document.getElementById('pending-total').textContent = `UGX ${formatCurrency(totalAmount)}`;

        document.getElementById('bank-count').textContent = bankTransactions.length;
        document.getElementById('bank-total').textContent = `UGX ${formatCurrency(bankTotal)}`;

        document.getElementById('mobile-count').textContent = mobileTransactions.length;
        document.getElementById('mobile-total').textContent = `UGX ${formatCurrency(mobileTotal)}`;
    }

    function renderTransactionsTable(list) {
        const tbody = document.getElementById('transactions-table-body');
        const mobile = document.getElementById('transactions-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach((transaction, index) => {
            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;

            const userVendorName = transaction.user
                ? `${transaction.user.first_name} ${transaction.user.last_name}`
                : transaction.vendor
                    ? transaction.vendor.vendor_name
                    : 'N/A';

            const transactionDateTime = transaction.external_metadata?.btDateTime || transaction.external_metadata?.mmDateTime || transaction.created_at;

            tr.innerHTML = `
                    <td data-column="datetime" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                        <div class="text-xs font-medium text-gray-900 leading-tight">${formatDateTime(transactionDateTime)}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Submitted: ${formatDateTime(transaction.created_at)}</div>
                    </td>
                    <td data-column="amount" class="px-3 py-2 text-left text-xs font-semibold text-gray-900 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                        UGX ${formatCurrency(transaction.amount_total)}
                    </td>
                    <td data-column="method" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                        ${getPaymentMethodBadge(transaction.payment_method)}
                    </td>
                    <td data-column="account" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                        <div class="font-medium text-gray-900">${transaction.cash_account_name}</div>
                    </td>
                    <td data-column="user" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                        <div class="font-medium text-gray-900">${userVendorName}</div>
                        <div class="text-gray-500">${transaction.user?.email || transaction.vendor?.email || ''}</div>
                    </td>
                    <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                        <div class="flex items-center justify-center">
                            <button onclick="showTransactionDetails('${transaction.transaction_id}')" 
                                class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" 
                                title="View Details">
                                <i class="fas fa-info-circle text-xs"></i>
                            </button>
                        </div>
                    </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'bg-gray-50 rounded-xl p-4 border border-gray-100';
            card.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center ${transaction.payment_method === 'BANK' ? 'bg-green-100' : 'bg-purple-100'}">
                                <i class="${transaction.payment_method === 'BANK' ? 'fas fa-university text-green-600' : 'fas fa-mobile-alt text-purple-600'}"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 text-sm">UGX ${formatCurrency(transaction.amount_total)}</div>
                                <div class="text-xs text-gray-500">${formatDateTime(transactionDateTime)}</div>
                            </div>
                        </div>
                        ${getPaymentMethodBadge(transaction.payment_method)}
                    </div>
                    
                    <div class="grid grid-cols-1 gap-2 text-xs mb-4">
                        <div>
                            <span class="text-gray-500 uppercase tracking-wide">Account</span>
                            <div class="font-medium text-gray-900 mt-1">${transaction.cash_account_name}</div>
                        </div>
                        <div>
                            <span class="text-gray-500 uppercase tracking-wide">User/Vendor</span>
                            <div class="font-medium text-gray-900 mt-1">${userVendorName}</div>
                            <div class="text-gray-500">${transaction.user?.email || transaction.vendor?.email || ''}</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-center">
                        <button onclick="showTransactionDetails('${transaction.transaction_id}')" 
                            class="px-4 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-info-circle mr-1"></i>View Details
                        </button>
                    </div>`;
            mobile.appendChild(card);
        });

        applyColumnVisibility();
    }

    function showTransactionDetails(transactionId) {
        currentTransaction = pendingTransactions.find(t => t.transaction_id === transactionId);
        if (!currentTransaction) return;

        const referenceLabel = currentTransaction.payment_method === 'BANK' ? 'Bank Ref' : 'MM TXN ID';
        document.getElementById('transactionReference').textContent = `${referenceLabel}: ${currentTransaction.external_reference}`;

        const content = document.getElementById('transactionDetailsContent');
        const metadata = currentTransaction.external_metadata;

        let detailsHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Transaction Information</h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">System Transaction ID</label>
                                <div class="text-sm font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">${currentTransaction.transaction_id}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Amount</label>
                                <div class="text-lg font-semibold text-gray-900">UGX ${formatCurrency(currentTransaction.amount_total)}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Payment Method</label>
                                <div class="mt-1">${getPaymentMethodBadge(currentTransaction.payment_method)}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Cash Account</label>
                                <div class="text-sm font-medium text-gray-900">${currentTransaction.cash_account_name}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Transaction Date/Time</label>
                                <div class="text-sm font-medium text-gray-900">${formatDateTime(metadata?.btDateTime || metadata?.mmDateTime || currentTransaction.created_at)}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Submitted</label>
                                <div class="text-sm text-gray-600">${formatDateTime(currentTransaction.created_at)}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">User Information</h4>
                        
                        <div class="space-y-3">`;

        if (currentTransaction.user) {
            detailsHTML += `
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">User Name</label>
                                <div class="text-sm font-medium text-gray-900">${currentTransaction.user.first_name} ${currentTransaction.user.last_name}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                <div class="text-sm text-gray-600">${currentTransaction.user.email}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Phone</label>
                                <div class="text-sm text-gray-600">${currentTransaction.user.phone}</div>
                            </div>`;
        } else if (currentTransaction.vendor) {
            detailsHTML += `
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Vendor Name</label>
                                <div class="text-sm font-medium text-gray-900">${currentTransaction.vendor.vendor_name}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                <div class="text-sm text-gray-600">${currentTransaction.vendor.email}</div>
                            </div>`;
        }

        detailsHTML += `
                        </div>
                    </div>
                </div>`;

        // Add payment-specific details
        if (currentTransaction.payment_method === 'BANK' && metadata?.btDepositorName) {
            detailsHTML += `
                    <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                        <h5 class="text-sm font-semibold text-green-800 mb-3">Bank Transfer Details</h5>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-green-600 uppercase tracking-wide">Bank Reference/Receipt Number</label>
                                <div class="text-sm font-mono text-green-800 bg-green-100 px-2 py-1 rounded mt-1">${currentTransaction.external_reference}</div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-green-600 uppercase tracking-wide">Depositor Name</label>
                                <div class="text-sm font-medium text-green-800">${metadata.btDepositorName}</div>
                            </div>
                        </div>
                    </div>`;
        } else if (currentTransaction.payment_method === 'MOBILE_MONEY' && metadata?.mmPhoneNumber) {
            detailsHTML += `
                    <div class="mt-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                        <h5 class="text-sm font-semibold text-purple-800 mb-3">Mobile Money Details</h5>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-purple-600 uppercase tracking-wide">Mobile Money Transaction ID</label>
                                <div class="text-sm font-mono text-purple-800 bg-purple-100 px-2 py-1 rounded mt-1">${currentTransaction.external_reference}</div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-purple-600 uppercase tracking-wide">Sender Phone Number</label>
                                <div class="text-sm font-medium text-purple-800">${metadata.mmPhoneNumber}</div>
                            </div>
                        </div>
                    </div>`;
        }

        if (currentTransaction.note) {
            detailsHTML += `
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-sm font-semibold text-gray-800 mb-2">Transaction Note</h5>
                        <div class="text-sm text-gray-700">${currentTransaction.note}</div>
                    </div>`;
        }

        content.innerHTML = detailsHTML;

        // Set up action buttons
        document.getElementById('approveTransactionBtn').onclick = () => showConfirmation('approve');
        document.getElementById('rejectTransactionBtn').onclick = () => showConfirmation('reject');

        document.getElementById('transactionDetailsModal').classList.remove('hidden');
    }

    function hideTransactionDetailsModal() {
        document.getElementById('transactionDetailsModal').classList.add('hidden');
        currentTransaction = null;
    }

    function showConfirmation(action) {
        const modal = document.getElementById('confirmationModal');
        const title = document.getElementById('confirmationTitle');
        const message = document.getElementById('confirmationMessage');
        const icon = document.getElementById('confirmationIcon');
        const iconClass = document.getElementById('confirmationIconClass');
        const confirmBtn = document.getElementById('confirmActionBtn');

        if (action === 'approve') {
            title.textContent = 'Affirm Transaction';
            message.textContent = 'Are you sure you want to affirm that this money was indeed sent? This will approve the transaction on the platform.';
            icon.className = 'w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-check text-green-600 text-xl';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium';
            confirmBtn.textContent = 'Affirm';
            confirmBtn.onclick = () => processTransaction('SUCCESS');
        } else {
            title.textContent = 'Reject Transaction';
            message.textContent = 'Are you sure you want to reject this transaction? This indicates the information provided is insufficient or incorrect.';
            icon.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-times text-red-600 text-xl';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium';
            confirmBtn.textContent = 'Reject';
            confirmBtn.onclick = () => processTransaction('FAILED');
        }

        modal.classList.remove('hidden');
    }

    function hideConfirmationModal() {
        document.getElementById('confirmationModal').classList.add('hidden');
    }

    async function processTransaction(status) {
        if (!currentTransaction) return;

        hideConfirmationModal();

        try {
            const formData = new FormData();
            formData.append('action', 'acknowledge');
            formData.append('transaction_id', currentTransaction.transaction_id);
            formData.append('status', status);

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                hideTransactionDetailsModal();
                fetchPendingTransactions();

                // Show success message
                const actionText = status === 'SUCCESS' ? 'approved' : 'rejected';
                showNotification(`Transaction has been ${actionText} successfully`, 'success');
            } else {
                showNotification(data.message || 'Failed to process transaction', 'error');
            }
        } catch (error) {
            console.error('Error processing transaction:', error);
            showNotification('An error occurred while processing the transaction', 'error');
        }
    }

    function showNotification(message, type) {
        // Create a simple notification
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
            }`;
        notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function loadColumnVisibility() {
        const saved = localStorage.getItem(COLUMNS_STORAGE_KEY);
        if (saved) {
            visibleColumns = JSON.parse(saved);
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

    function applyColumnVisibility() {
        const table = document.getElementById('transactions-table');
        if (!table) return;

        const headers = table.querySelectorAll('thead th[data-column]');
        headers.forEach(header => {
            const column = header.getAttribute('data-column');
            header.style.display = visibleColumns.includes(column) ? '' : 'none';
        });

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td[data-column]');
            cells.forEach(cell => {
                const column = cell.getAttribute('data-column');
                cell.style.display = visibleColumns.includes(column) ? '' : 'none';
            });
        });
    }

    function toggleColumnSelector() {
        const selector = document.getElementById('columnSelector');
        selector.classList.toggle('hidden');
    }

    function adjustTableFontSize() {
        const table = document.getElementById('transactions-table');
        if (!table) return;

        const container = table.parentElement;
        let fontSize = 14;

        table.style.fontSize = fontSize + 'px';

        while ((table.scrollWidth > container.clientWidth) && fontSize > 8) {
            fontSize -= 0.5;
            table.style.fontSize = fontSize + 'px';
        }

        if (fontSize < 10) {
            table.style.fontSize = '10px';
        }
    }

    function filterTransactions() {
        const query = document.getElementById('searchTransactions').value.trim().toLowerCase();
        const method = document.getElementById('filterTransactions').value;

        let filtered = pendingTransactions;

        if (method !== 'all') {
            filtered = filtered.filter(t => t.payment_method === method);
        }

        if (query) {
            filtered = filtered.filter(t =>
                t.cash_account_name.toLowerCase().includes(query) ||
                (t.user && `${t.user.first_name} ${t.user.last_name}`.toLowerCase().includes(query)) ||
                (t.vendor && t.vendor.vendor_name.toLowerCase().includes(query)) ||
                t.external_reference.toLowerCase().includes(query) ||
                (t.note && t.note.toLowerCase().includes(query))
            );
        }

        renderTransactionsTable(filtered);
        adjustTableFontSize();
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        loadColumnVisibility();
        fetchPendingTransactions();

        document.getElementById('searchTransactions').addEventListener('input', filterTransactions);
        document.getElementById('filterTransactions').addEventListener('change', filterTransactions);

        // Column visibility event listeners
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

                applyColumnVisibility();
                saveColumnVisibility();
                adjustTableFontSize();
            }
        });

        // Close column selector when clicking outside
        document.addEventListener('click', function (event) {
            const selector = document.getElementById('columnSelector');
            const btn = document.getElementById('viewColumnsBtn');

            if (selector && btn && !selector.contains(event.target) && !btn.contains(event.target)) {
                selector.classList.add('hidden');
            }
        });

        window.addEventListener('resize', adjustTableFontSize);
    });

    // Global functions
    window.showTransactionDetails = showTransactionDetails;
    window.hideTransactionDetailsModal = hideTransactionDetailsModal;
    window.hideConfirmationModal = hideConfirmationModal;
    window.toggleColumnSelector = toggleColumnSelector;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>