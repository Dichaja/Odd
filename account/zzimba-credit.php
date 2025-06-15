<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-wallet text-primary text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-secondary font-rubik">Zzimba Credit</h1>
                            <p class="text-sm text-gray-text">Your wallet balance and transaction history</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="add-money-btn" onclick="showAddMoneyModal()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-plus"></i><span>Add Money</span>
                    </button>
                    <button
                        class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center gap-2 font-medium">
                        <i class="fas fa-download"></i>
                        <span>Download Statement</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Wallet Overview Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-primary/5 to-primary/10 p-6 border-b border-gray-100">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Wallet Info -->
                    <div class="lg:col-span-2">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-wallet text-primary text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <div id="walletLoading" class="animate-pulse">
                                    <div class="h-6 bg-gray-200 rounded w-48 mb-2"></div>
                                    <div class="h-4 bg-gray-200 rounded w-32 mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-64"></div>
                                </div>
                                <div id="walletInfo" class="hidden">
                                    <h2 class="text-xl font-bold text-secondary font-rubik mb-1" id="walletName"></h2>
                                    <p class="text-gray-600 mb-2" id="ownerName"></p>
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-500">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-id-card text-xs"></i>
                                            <span id="walletId"></span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-calendar text-xs"></i>
                                            <span id="createdDate"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Display -->
                    <div class="lg:text-right">
                        <p class="text-sm font-medium text-gray-600 mb-1">Current Balance</p>
                        <div id="balanceLoading" class="animate-pulse">
                            <div class="h-10 bg-gray-200 rounded w-32 mb-2 ml-auto"></div>
                            <div class="h-6 bg-gray-200 rounded w-20 ml-auto"></div>
                        </div>
                        <div id="balanceInfo" class="hidden">
                            <p class="text-3xl lg:text-4xl font-bold text-primary mb-2" id="balanceText"></p>
                            <span id="statusBadge"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                                <i class="mr-1"></i>
                                <span></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Statement -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Statement Header -->
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Statement</h3>
                        <p class="text-sm text-gray-text mt-1">Recent transactions and account activity</p>
                    </div>

                    <!-- Date Filter -->
                    <div class="flex items-center gap-3">
                        <select id="dateFilter" onchange="loadTransactions()"
                            class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm">
                            <option value="all">All transactions</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 3 months</option>
                            <option value="180">Last 6 months</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="transactionsLoading" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div id="transactionsTable" class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactionsTableElement">
                    <thead class="bg-user-accent border-b border-gray-200">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Transaction Details
                            </th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Transaction ID
                            </th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Date/Time
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Amount
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Credit
                            </th>
                            <th
                                class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody" class="divide-y divide-gray-100">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div id="transactionsMobile" class="lg:hidden p-4 space-y-4 hidden">
                <!-- Populated via JavaScript -->
            </div>

            <!-- Empty State -->
            <div id="transactionsEmpty" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                <p class="text-gray-500 mb-6">Start by adding money to your wallet</p>
                <button onclick="showAddMoneyModal()"
                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                    Add Money
                </button>
            </div>

            <!-- Pagination -->
            <div id="transactionsPagination" class="hidden px-6 py-4 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600" id="paginationInfo">
                        <!-- Populated via JavaScript -->
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-white transition-colors disabled:opacity-50"
                            disabled>
                            <i class="fas fa-chevron-left mr-1"></i>
                            Previous
                        </button>
                        <button
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-white transition-colors disabled:opacity-50"
                            disabled>
                            Next
                            <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Money Modal -->
    <div id="addMoneyModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideAddMoneyModal()"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-plus text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Add Money</h3>
                        <p class="text-sm text-gray-500">Top up your Zzimba wallet</p>
                    </div>
                </div>

                <!-- Form -->
                <form id="addMoneyForm" class="space-y-4">
                    <!-- Phone Number -->
                    <div>
                        <label for="phoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-3 text-gray-500 font-medium">+256</div>
                            <input type="tel" id="phoneNumber" name="phoneNumber"
                                class="w-full pl-16 pr-12 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="771234567" maxlength="9" pattern="[0-9]{9}" required>
                            <div id="phoneValidationSpinner" class="absolute right-3 top-3 hidden">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">Enter exactly 9 digits (without the leading 0)</div>
                        <div id="customerName" class="mt-2 text-sm text-green-600 hidden"></div>
                        <div id="phoneError" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Amount (UGX)
                        </label>
                        <input type="number" id="amount" name="amount" min="500" step="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter amount (minimum 500)" required>
                        <div id="amountError" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <input type="text" id="description" name="description"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Payment description">
                    </div>

                    <!-- Status Display -->
                    <div id="paymentStatus" class="hidden p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div id="statusIcon" class="w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <div>
                                <div id="statusTitle" class="font-medium text-gray-900">Processing Payment</div>
                                <div id="statusMessage" class="text-sm text-gray-600">Please wait...</div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Actions -->
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideAddMoneyModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="button" id="submitPaymentBtn" onclick="submitPayment()" disabled
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Add Money
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Result Modal -->
    <div id="transactionResultModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideTransactionResultModal()"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content">
            <div class="p-6 text-center">
                <!-- Icon -->
                <div id="resultIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <!-- Icon populated via JavaScript -->
                </div>

                <!-- Title and Message -->
                <h3 id="resultTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                <p id="resultMessage" class="text-gray-600 mb-6"></p>

                <!-- Transaction Details -->
                <div id="resultDetails" class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                    <!-- Details populated via JavaScript -->
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button onclick="hideTransactionResultModal()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transaction-details {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        max-height: 2.8em;
        /* 2 lines * 1.4 line-height */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const ownerName = <?= json_encode(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?>;

        let validatedMsisdn = null;
        let customerName = null;
        let currentPaymentReference = null;
        let statusCheckInterval = null;
        let validationTimeout = null;
        let transactions = [];

        // Load initial data
        loadWalletData();
        loadTransactions();

        // Initialize table font sizing
        adjustTableFontSize();
        window.addEventListener('resize', adjustTableFontSize);

        function loadWalletData() {
            fetch(`${apiUrl}?action=getWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: ''
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.wallet) {
                        const w = data.wallet;
                        document.getElementById('walletName').textContent = w.wallet_name;
                        document.getElementById('ownerName').textContent = ownerName;
                        document.getElementById('walletId').textContent = w.wallet_id;
                        document.getElementById('createdDate').textContent =
                            new Date(w.created_at).toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                        document.getElementById('balanceText').textContent =
                            'UGX ' + parseFloat(w.current_balance).toLocaleString(undefined, { minimumFractionDigits: 2 });

                        const badge = document.getElementById('statusBadge');
                        const badgeText = badge.querySelector('span');
                        const badgeIcon = badge.querySelector('i');

                        badgeText.textContent = w.status.charAt(0).toUpperCase() + w.status.slice(1);
                        badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium';

                        if (w.status === 'active') {
                            badge.classList.add('bg-green-100', 'text-green-800');
                            badgeIcon.className = 'fas fa-check-circle mr-1';
                        } else {
                            badge.classList.add('bg-gray-100', 'text-gray-600');
                            badgeIcon.className = 'fas fa-times-circle mr-1';
                        }

                        // Show wallet info and hide loading
                        document.getElementById('walletLoading').classList.add('hidden');
                        document.getElementById('balanceLoading').classList.add('hidden');
                        document.getElementById('walletInfo').classList.remove('hidden');
                        document.getElementById('balanceInfo').classList.remove('hidden');
                    } else {
                        showWalletError('Failed to load wallet data');
                    }
                })
                .catch(error => {
                    console.error('Error loading wallet:', error);
                    showWalletError('Network error loading wallet data');
                });
        }

        function showWalletError(message) {
            document.getElementById('walletLoading').innerHTML = `<div class="text-red-600 text-sm">${message}</div>`;
            document.getElementById('balanceLoading').innerHTML = `<div class="text-red-600 text-sm">Error loading balance</div>`;
        }

        function loadTransactions() {
            const filter = document.getElementById('dateFilter').value;
            let params = { action: 'getWalletStatement' };

            if (filter !== 'all') {
                const days = parseInt(filter);
                const endDate = new Date();
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - days);

                params.filter = 'range';
                params.start = startDate.toISOString().split('T')[0];
                params.end = endDate.toISOString().split('T')[0];
            } else {
                params.filter = 'all';
            }

            // Show loading state
            document.getElementById('transactionsLoading').classList.remove('hidden');
            document.getElementById('transactionsTable').classList.add('hidden');
            document.getElementById('transactionsMobile').classList.add('hidden');
            document.getElementById('transactionsEmpty').classList.add('hidden');
            document.getElementById('transactionsPagination').classList.add('hidden');

            fetch(`${apiUrl}?action=getWalletStatement`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(params)
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('transactionsLoading').classList.add('hidden');

                    if (data.success && data.statement) {
                        // Transform the statement data into the expected format
                        const transformedTransactions = transformStatementData(data.statement);
                        transactions = transformedTransactions;

                        if (transactions.length > 0) {
                            renderTransactions(transactions);
                            document.getElementById('transactionsTable').classList.remove('hidden');
                            document.getElementById('transactionsMobile').classList.remove('hidden');
                            document.getElementById('transactionsPagination').classList.remove('hidden');
                            updatePaginationInfo(transactions.length);
                            adjustTableFontSize();
                        } else {
                            document.getElementById('transactionsEmpty').classList.remove('hidden');
                        }
                    } else {
                        showTransactionsError(data.message || 'Failed to load transactions');
                    }
                })
                .catch(error => {
                    console.error('Error loading transactions:', error);
                    document.getElementById('transactionsLoading').classList.add('hidden');
                    showTransactionsError('Network error loading transactions');
                });
        }

        function transformStatementData(statement) {
            const transformedTransactions = [];

            statement.forEach(transaction => {
                if (transaction.entries && transaction.entries.length > 0) {
                    // Process each entry for successful transactions
                    transaction.entries.forEach(entry => {
                        transformedTransactions.push({
                            transaction_id: transaction.transaction_id,
                            transaction_details: getDetailedTransactionDescription(transaction, entry),
                            payment_reference: transaction.transaction_id,
                            value_date: transaction.created_at,
                            entry_date: entry.created_at,
                            credit: entry.entry_type === 'CREDIT' ? parseFloat(entry.amount) : 0,
                            debit: entry.entry_type === 'DEBIT' ? parseFloat(entry.amount) : 0,
                            balance: parseFloat(entry.balance_after),
                            amount_total: parseFloat(transaction.amount_total),
                            status: transaction.status,
                            type: transaction.type,
                            payment_method: transaction.payment_method,
                            note: transaction.note,
                            entry_note: entry.entry_note
                        });
                    });
                } else {
                    // For failed transactions or transactions without entries
                    transformedTransactions.push({
                        transaction_id: transaction.transaction_id,
                        transaction_details: getDetailedTransactionDescription(transaction, null),
                        payment_reference: transaction.transaction_id,
                        value_date: transaction.created_at,
                        entry_date: null,
                        credit: 0,
                        debit: 0,
                        balance: 0,
                        amount_total: parseFloat(transaction.amount_total),
                        status: transaction.status,
                        type: transaction.type,
                        payment_method: transaction.payment_method,
                        note: transaction.note,
                        entry_note: null
                    });
                }
            });

            return transformedTransactions.sort((a, b) => new Date(b.value_date) - new Date(a.value_date));
        }

        function getDetailedTransactionDescription(transaction, entry) {
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

            let description = typeMap[transaction.type] || transaction.type;

            if (transaction.payment_method) {
                description += ` via ${methodMap[transaction.payment_method] || transaction.payment_method}`;
            }

            // Add entry note for successful transactions
            if (entry && entry.entry_note) {
                description = entry.entry_note;
            }

            // Add status and reason for failed transactions
            if (transaction.status === 'FAILED') {
                description += ' (Failed)';
                if (transaction.note && transaction.note !== 'Request payment completed successfully.') {
                    // Clean up the note for display
                    let reason = transaction.note.replace(/_/g, ' ').toLowerCase();
                    reason = reason.charAt(0).toUpperCase() + reason.slice(1);
                    description += ` - ${reason}`;
                }
            }

            return description;
        }

        function renderTransactions(transactionsList) {
            const tbody = document.getElementById('transactionsTableBody');
            const mobile = document.getElementById('transactionsMobile');

            tbody.innerHTML = '';
            mobile.innerHTML = '';

            transactionsList.forEach((transaction, index) => {
                // Desktop row
                const tr = document.createElement('tr');
                tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;

                const credit = parseFloat(transaction.credit || 0);
                const debit = parseFloat(transaction.debit || 0);
                const balance = parseFloat(transaction.balance || 0);
                const amountTotal = parseFloat(transaction.amount_total || 0);

                // Format dates
                const valueDate = new Date(transaction.value_date);
                const dateStr = valueDate.toLocaleDateString('en-GB', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                const timeStr = valueDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                // Truncate transaction details if too long
                const maxDetailsLength = 45;
                let displayDetails = transaction.transaction_details || 'N/A';
                if (displayDetails.length > maxDetailsLength) {
                    displayDetails = displayDetails.substring(0, maxDetailsLength) + '...';
                }

                tr.innerHTML = `
            <td class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center ${credit > 0 ? 'bg-green-100' : transaction.status === 'FAILED' ? 'bg-gray-100' : 'bg-red-100'}">
                        <i class="${credit > 0 ? 'fas fa-arrow-down text-green-600' : transaction.status === 'FAILED' ? 'fas fa-times text-gray-600' : 'fas fa-arrow-up text-red-600'} text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-medium text-gray-900 leading-tight" title="${transaction.transaction_details}">${displayDetails}</div>
                        <div class="text-xs text-gray-500 mt-0.5">${transaction.type} • ${transaction.payment_method?.replace('_', ' ') || 'N/A'}</div>
                    </div>
                </div>
            </td>
            <td class="px-3 py-2 text-gray-600 font-mono text-xs whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                <div class="truncate max-w-24" title="${transaction.transaction_id}">${transaction.transaction_id}</div>
            </td>
            <td class="px-3 py-2 text-gray-600 text-xs whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                <div class="leading-tight">
                    <div class="font-medium">${dateStr}</div>
                    <div class="text-gray-500">${timeStr}</div>
                </div>
            </td>
            <td class="px-3 py-2 text-right text-xs whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                <div class="font-semibold ${transaction.status === 'FAILED' ? 'text-red-600' : 'text-gray-900'}">
                    ${transaction.status === 'FAILED' ? '-' : ''}${formatCurrency(amountTotal)}
                </div>
                ${transaction.status === 'FAILED' ? '<div class="text-xs text-red-500">(Failed)</div>' : ''}
            </td>
            <td class="px-3 py-2 text-right text-xs whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                ${credit > 0 ? `<span class="font-semibold text-green-600">+${formatCurrency(credit)}</span>` : '<span class="text-gray-400">-</span>'}
            </td>
            <td class="px-3 py-2 text-right font-semibold text-gray-900 text-xs whitespace-nowrap ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                ${balance > 0 ? formatCurrency(balance) : '<span class="text-gray-400">-</span>'}
            </td>
        `;
                tbody.appendChild(tr);

                // Mobile card
                const card = document.createElement('div');
                card.className = 'bg-gray-50 rounded-xl p-4 border border-gray-100';
                card.innerHTML = `
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center ${credit > 0 ? 'bg-green-100' : transaction.status === 'FAILED' ? 'bg-gray-100' : 'bg-red-100'}">
                        <i class="${credit > 0 ? 'fas fa-arrow-down text-green-600' : transaction.status === 'FAILED' ? 'fas fa-times text-gray-600' : 'fas fa-arrow-up text-red-600'}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="font-medium text-gray-900 text-sm truncate" title="${transaction.transaction_details}">${displayDetails}</div>
                        <div class="text-xs text-gray-500">${dateStr} • ${timeStr}</div>
                    </div>
                </div>
                <div class="text-right ml-2">
                    <div class="font-semibold text-sm ${transaction.status === 'FAILED' ? 'text-red-600' : credit > 0 ? 'text-green-600' : 'text-gray-900'}">
                        ${transaction.status === 'FAILED' ? 'Failed' : credit > 0 ? `+${formatCurrency(credit)}` : `-${formatCurrency(debit)}`}
                    </div>
                    <div class="text-xs text-gray-500">
                        Total: ${formatCurrency(amountTotal)}
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-500 uppercase tracking-wide">Transaction ID</span>
                    <div class="font-mono text-gray-700 mt-1 truncate" title="${transaction.transaction_id}">${transaction.transaction_id}</div>
                </div>
                <div class="text-right">
                    <span class="text-gray-500 uppercase tracking-wide">Balance</span>
                    <div class="font-semibold text-gray-900 mt-1">${balance > 0 ? formatCurrency(balance) : '-'}</div>
                </div>
            </div>
            
            ${transaction.status === 'FAILED' && transaction.note ? `
            <div class="mt-3 p-2 bg-red-50 rounded-lg">
                <div class="text-xs text-red-700">
                    <strong>Reason:</strong> ${transaction.note.replace(/_/g, ' ').toLowerCase()}
                </div>
            </div>
            ` : ''}
        `;
                mobile.appendChild(card);
            });
        }

        async function validatePhoneNumber(phone = null) {
            const phoneInput = document.getElementById('phoneNumber');
            const spinner = document.getElementById('phoneValidationSpinner');
            const customerNameDiv = document.getElementById('customerName');
            const phoneErrorDiv = document.getElementById('phoneError');

            const phoneValue = phone || phoneInput.value.trim();
            if (!phoneValue) {
                showPhoneError('Please enter a phone number');
                return;
            }

            // Validate that it's exactly 9 digits
            if (!/^\d{9}$/.test(phoneValue)) {
                showPhoneError('Please enter exactly 9 digits');
                return;
            }

            // Format phone number with +256
            const formattedPhone = '+256' + phoneValue;

            spinner.classList.remove('hidden');
            phoneErrorDiv.classList.add('hidden');
            customerNameDiv.classList.add('hidden');

            try {
                const response = await fetch(`${apiUrl}?action=validateMsisdn`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        msisdn: formattedPhone
                    })
                });

                const data = await response.json();

                if (data.success) {
                    validatedMsisdn = formattedPhone;
                    customerName = data.customer_name;
                    customerNameDiv.textContent = `✓ ${data.customer_name}`;
                    customerNameDiv.classList.remove('hidden');
                    checkFormValidity();
                } else {
                    showPhoneError(data.message || 'Phone number validation failed');
                }
            } catch (error) {
                showPhoneError('Network error. Please try again.');
            } finally {
                spinner.classList.add('hidden');
            }
        }

        function showTransactionsError(message) {
            document.getElementById('transactionsLoading').innerHTML = `<div class="text-red-600 text-center p-6">${message}</div>`;
        }

        function updatePaginationInfo(count) {
            document.getElementById('paginationInfo').textContent = `Showing 1-${count} of ${count} transactions`;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-UG', {
                style: 'decimal',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        function adjustTableFontSize() {
            const table = document.getElementById('transactionsTableElement');
            if (!table) return;

            const container = table.parentElement;
            let fontSize = 14; // Start with base font size

            table.style.fontSize = fontSize + 'px';

            // Check if table overflows horizontally or if transaction details exceed 2 lines
            while ((table.scrollWidth > container.clientWidth || hasOverflowingTransactionDetails()) && fontSize > 8) {
                fontSize -= 0.5;
                table.style.fontSize = fontSize + 'px';
            }

            // Ensure minimum readable font size
            if (fontSize < 10) {
                table.style.fontSize = '10px';
            }
        }

        function hasOverflowingTransactionDetails() {
            const detailElements = document.querySelectorAll('.transaction-details');
            for (let element of detailElements) {
                if (element.scrollHeight > element.clientHeight) {
                    return true;
                }
            }
            return false;
        }

        // Modal functions
        window.showAddMoneyModal = function () {
            document.getElementById('addMoneyModal').classList.remove('hidden');
            resetForm();
        };

        window.hideAddMoneyModal = function () {
            document.getElementById('addMoneyModal').classList.add('hidden');
            resetForm();
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
                statusCheckInterval = null;
            }
        };

        function resetForm() {
            document.getElementById('addMoneyForm').reset();
            document.getElementById('customerName').classList.add('hidden');
            document.getElementById('phoneError').classList.add('hidden');
            document.getElementById('amountError').classList.add('hidden');
            document.getElementById('paymentStatus').classList.add('hidden');
            document.getElementById('phoneValidationSpinner').classList.add('hidden');
            document.getElementById('submitPaymentBtn').disabled = true;
            validatedMsisdn = null;
            customerName = null;
            currentPaymentReference = null;
            if (validationTimeout) {
                clearTimeout(validationTimeout);
                validationTimeout = null;
            }
        }

        // Phone validation with auto-trigger on blur

        function showPhoneError(message) {
            const phoneErrorDiv = document.getElementById('phoneError');
            phoneErrorDiv.textContent = message;
            phoneErrorDiv.classList.remove('hidden');
            document.getElementById('customerName').classList.add('hidden');
            document.getElementById('submitPaymentBtn').disabled = true;
            validatedMsisdn = null;
            customerName = null;
        }

        function showAmountError(message) {
            const amountErrorDiv = document.getElementById('amountError');
            amountErrorDiv.textContent = message;
            amountErrorDiv.classList.remove('hidden');
            document.getElementById('submitPaymentBtn').disabled = true;
        }

        function hideAmountError() {
            document.getElementById('amountError').classList.add('hidden');
            checkFormValidity();
        }

        function checkFormValidity() {
            const amount = parseFloat(document.getElementById('amount').value);
            const submitBtn = document.getElementById('submitPaymentBtn');

            if (validatedMsisdn && amount >= 500) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Payment submission
        window.submitPayment = async function () {
            if (!validatedMsisdn) {
                showPhoneError('Please validate the phone number first');
                return;
            }

            const amount = parseFloat(document.getElementById('amount').value);
            const description = document.getElementById('description').value.trim() || 'Zzimba wallet top-up';

            if (!amount || amount < 500) {
                showAmountError('Please enter a valid amount (minimum 500 UGX)');
                return;
            }

            const submitBtn = document.getElementById('submitPaymentBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            showPaymentStatus('processing', 'Processing Payment', 'Initiating payment request...');

            try {
                const response = await fetch(`${apiUrl}?action=makePayment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        msisdn: validatedMsisdn,
                        amount: amount,
                        description: description
                    })
                });

                const data = await response.json();

                if (data.success) {
                    currentPaymentReference = data.internal_reference;
                    showPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone and enter your PIN to complete the payment.');
                    startStatusChecking();
                } else {
                    showPaymentStatus('error', 'Payment Failed', data.message || 'Failed to initiate payment');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Money';
                }
            } catch (error) {
                showPaymentStatus('error', 'Network Error', 'Please check your connection and try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Money';
            }
        };

        function showPaymentStatus(type, title, message) {
            const statusDiv = document.getElementById('paymentStatus');
            const statusIcon = document.getElementById('statusIcon');
            const statusTitle = document.getElementById('statusTitle');
            const statusMessage = document.getElementById('statusMessage');

            statusTitle.textContent = title;
            statusMessage.textContent = message;

            // Reset classes
            statusDiv.className = 'p-4 rounded-xl';
            statusIcon.className = 'w-8 h-8 rounded-full flex items-center justify-center';

            switch (type) {
                case 'processing':
                    statusDiv.classList.add('bg-blue-50', 'border', 'border-blue-200');
                    statusIcon.classList.add('bg-blue-100');
                    statusIcon.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600"></i>';
                    break;
                case 'pending':
                    statusDiv.classList.add('bg-yellow-50', 'border', 'border-yellow-200');
                    statusIcon.classList.add('bg-yellow-100');
                    statusIcon.innerHTML = '<i class="fas fa-clock text-yellow-600"></i>';
                    break;
                case 'success':
                    statusDiv.classList.add('bg-green-50', 'border', 'border-green-200');
                    statusIcon.classList.add('bg-green-100');
                    statusIcon.innerHTML = '<i class="fas fa-check text-green-600"></i>';
                    break;
                case 'error':
                    statusDiv.classList.add('bg-red-50', 'border', 'border-red-200');
                    statusIcon.classList.add('bg-red-100');
                    statusIcon.innerHTML = '<i class="fas fa-times text-red-600"></i>';
                    break;
            }

            statusDiv.classList.remove('hidden');
        }

        function startStatusChecking() {
            if (!currentPaymentReference) return;

            statusCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`${apiUrl}?action=checkStatus`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            internal_reference: currentPaymentReference
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.status === 'success') {
                            clearInterval(statusCheckInterval);
                            statusCheckInterval = null;

                            // Hide the add money modal first
                            hideAddMoneyModal();

                            // Show success modal with transaction details
                            setTimeout(() => {
                                showTransactionResultModal('success', {
                                    title: 'Payment Successful!',
                                    message: data.message || 'Your payment has been completed successfully.',
                                    amount: data.amount,
                                    currency: data.currency || 'UGX',
                                    provider: data.provider,
                                    transactionId: data.provider_transaction_id,
                                    reference: data.customer_reference,
                                    charge: data.charge,
                                    completedAt: data.completed_at
                                });
                            }, 300);

                            // Reload wallet and transaction data
                            setTimeout(() => {
                                loadWalletData();
                                loadTransactions();
                            }, 1000);

                        } else if (data.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            statusCheckInterval = null;

                            // Hide the add money modal first
                            hideAddMoneyModal();

                            // Show failure modal with error details
                            setTimeout(() => {
                                showTransactionResultModal('failed', {
                                    title: 'Payment Failed',
                                    message: data.message || 'Payment could not be completed.',
                                    amount: data.amount,
                                    currency: data.currency || 'UGX',
                                    provider: data.provider,
                                    reference: data.customer_reference,
                                    reason: data.message
                                });
                            }, 300);

                            // Reset the form for retry
                            setTimeout(() => {
                                document.getElementById('submitPaymentBtn').disabled = false;
                                document.getElementById('submitPaymentBtn').textContent = 'Add Money';
                            }, 1000);
                        }
                        // If status is still pending, continue checking
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                }
            }, 3000); // Check every 3 seconds
        }

        function showTransactionResultModal(type, data) {
            const modal = document.getElementById('transactionResultModal');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const message = document.getElementById('resultMessage');
            const details = document.getElementById('resultDetails');

            title.textContent = data.title;
            message.textContent = data.message;

            // Reset classes
            modal.querySelector('.modal-content').className = 'bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content';
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';

            if (type === 'success') {
                modal.querySelector('.modal-content').classList.add('border-t-4', 'border-green-500');
                icon.classList.add('bg-green-100');
                icon.innerHTML = '<i class="fas fa-check text-green-600 text-2xl"></i>';

                details.innerHTML = `
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-semibold">${data.currency} ${formatCurrency(data.amount)}</span>
                        </div>
                        ${data.charge ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction Fee:</span>
                            <span class="font-semibold">${data.currency} ${formatCurrency(data.charge)}</span>
                        </div>
                        ` : ''}
                        <div class="flex justify-between">
                            <span class="text-gray-600">Provider:</span>
                            <span class="font-semibold">${data.provider?.replace('_', ' ') || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-mono text-xs">${data.transactionId || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reference:</span>
                            <span class="font-mono text-xs">${data.reference || 'N/A'}</span>
                        </div>
                        ${data.completedAt && data.completedAt !== 'N/A' ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completed:</span>
                            <span class="text-xs">${new Date(data.completedAt).toLocaleString()}</span>
                        </div>
                        ` : ''}
                    </div>
                `;
            } else {
                modal.querySelector('.modal-content').classList.add('border-t-4', 'border-red-500');
                icon.classList.add('bg-red-100');
                icon.innerHTML = '<i class="fas fa-times text-red-600 text-2xl"></i>';

                details.innerHTML = `
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-semibold">${data.currency} ${formatCurrency(data.amount)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Provider:</span>
                            <span class="font-semibold">${data.provider?.replace('_', ' ') || 'N/A'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Reference:</span>
                            <span class="font-mono text-xs">${data.reference || 'N/A'}</span>
                        </div>
                        ${data.reason ? `
                        <div class="mt-4 p-3 bg-red-50 rounded-lg">
                            <p class="text-red-800 text-xs"><strong>Reason:</strong> ${data.reason}</p>
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            modal.classList.remove('hidden');
        }

        function hideTransactionResultModal() {
            document.getElementById('transactionResultModal').classList.add('hidden');
        }

        // Event listeners
        document.getElementById('phoneNumber').addEventListener('blur', function (e) {
            const phone = e.target.value.trim();
            if (phone && phone !== validatedMsisdn) {
                // Clear previous validation timeout
                if (validationTimeout) {
                    clearTimeout(validationTimeout);
                }
                // Add small delay to avoid too frequent API calls
                validationTimeout = setTimeout(() => {
                    validatePhoneNumber(phone);
                }, 500);
            }
        });

        document.getElementById('phoneNumber').addEventListener('input', function (e) {
            // Reset validation when phone number changes
            if (validatedMsisdn && e.target.value.trim() !== validatedMsisdn) {
                document.getElementById('customerName').classList.add('hidden');
                document.getElementById('phoneError').classList.add('hidden');
                document.getElementById('submitPaymentBtn').disabled = true;
                validatedMsisdn = null;
                customerName = null;
            }
        });

        // Restrict phone number input to digits only
        document.getElementById('phoneNumber').addEventListener('input', function (e) {
            // Remove any non-digit characters
            let value = e.target.value.replace(/\D/g, '');

            // Limit to 9 digits
            if (value.length > 9) {
                value = value.substring(0, 9);
            }

            e.target.value = value;

            // Reset validation when phone number changes
            if (validatedMsisdn && ('+256' + value) !== validatedMsisdn) {
                document.getElementById('customerName').classList.add('hidden');
                document.getElementById('phoneError').classList.add('hidden');
                document.getElementById('submitPaymentBtn').disabled = true;
                validatedMsisdn = null;
                customerName = null;
            }
        });

        document.getElementById('amount').addEventListener('input', function (e) {
            const amount = parseFloat(e.target.value);
            if (amount && amount < 500) {
                showAmountError('Minimum amount is 500 UGX');
            } else {
                hideAmountError();
            }
        });

        // Enable Enter key for validation
        document.getElementById('phoneNumber').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                validatePhoneNumber();
            }
        });

        // Make functions globally available
        window.loadTransactions = loadTransactions;
        window.showTransactionResultModal = showTransactionResultModal;
        window.hideTransactionResultModal = hideTransactionResultModal;
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>