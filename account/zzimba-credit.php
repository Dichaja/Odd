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

<div class="min-h-screen bg-user-content dark:bg-secondary/10">
    <div class="bg-white dark:bg-secondary border-b border-gray-200 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-5">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl grid place-items-center">
                            <i class="fas fa-wallet text-primary text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-secondary dark:text-white font-rubik">Zzimba
                                Credit</h1>
                            <p class="text-sm text-gray-text dark:text-white/70">Wallet balance & transactions</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:flex gap-3">
                    <button id="add-money-btn" onclick="showPaymentMethodModal()"
                        class="px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-plus"></i><span>Top Up</span>
                    </button>
                    <button id="send-credit-btn" onclick="showSendCreditModal()"
                        class="px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-paper-plane"></i><span>Send Credit</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 mb-6 overflow-hidden">
            <div
                class="bg-gradient-to-r from-primary/5 to-primary/10 dark:from-white/5 dark:to-white/10 p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl grid place-items-center">
                                <i class="fas fa-wallet text-primary text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <div id="walletLoading" class="animate-pulse">
                                    <div class="h-6 bg-gray-200 dark:bg-white/10 rounded w-48 mb-2"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-32 mb-2"></div>
                                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-64"></div>
                                </div>
                                <div id="walletInfo" class="hidden">
                                    <h2 class="text-xl font-bold text-secondary dark:text-white font-rubik mb-1"
                                        id="walletName"></h2>
                                    <p class="text-gray-600 dark:text-white/70 mb-2" id="ownerName"></p>
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-500 dark:text-white/70">
                                        <span class="flex items-center gap-1"><i
                                                class="fas fa-id-card text-xs"></i><span id="walletId"></span></span>
                                        <span class="flex items-center gap-1"><i
                                                class="fas fa-calendar text-xs"></i><span
                                                id="createdDate"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:text-right">
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 mb-1">Current Balance</p>
                        <div id="balanceLoading" class="animate-pulse">
                            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-32 mb-2 ml-auto"></div>
                            <div class="h-6 bg-gray-200 dark:bg-white/10 rounded w-20 ml-auto"></div>
                        </div>
                        <div id="balanceInfo" class="hidden">
                            <p class="text-3xl lg:text-4xl font-bold text-primary mb-2" id="balanceText"></p>
                            <span id="statusBadge"
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium">
                                <i class="mr-1"></i><span></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-secondary dark:text-white font-rubik">Transaction
                            Statement</h3>
                        <p class="text-sm text-gray-text dark:text-white/70 mt-1">Recent transactions and account
                            activity</p>
                    </div>
                    <div class="flex items-stretch sm:items-center w-full sm:w-auto gap-2 sm:gap-3">
                        <button id="viewColumnsBtn" onclick="toggleColumnSelector()"
                            class="hidden lg:inline-flex items-center gap-2 px-4 py-2 border border-gray-200 dark:border-white/20 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-white/10 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <i class="fas fa-eye text-xs"></i><span>View</span><i
                                class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="relative">
                            <div id="columnSelector"
                                class="hidden absolute right-0 top-full mt-2 bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 rounded-lg shadow-lg z-50 min-w-48">
                                <div class="p-3 border-b border-gray-100 dark:border-white/10">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Show Columns</h4>
                                    <p class="text-xs text-gray-500 dark:text-white/60 mt-1">Select at least 3 columns
                                    </p>
                                </div>
                                <div class="p-2 space-y-1" id="columnCheckboxes">
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-white/10 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="datetime" checked>
                                        <span class="text-sm text-gray-700 dark:text-white">Date/Time</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-white/10 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="description" checked>
                                        <span class="text-sm text-gray-700 dark:text-white">Description</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-white/10 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="debit" checked>
                                        <span class="text-sm text-gray-700 dark:text-white">Debit</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-white/10 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="credit" checked>
                                        <span class="text-sm text-gray-700 dark:text-white">Credit</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-white/10 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="balance" checked>
                                        <span class="text-sm text-gray-700 dark:text-white">Balance</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <select id="dateFilter" onchange="loadTransactions()"
                            class="w-full sm:w-auto px-2 py-1 text-sm border border-gray-300 rounded item-size-input dark:bg-secondary dark:text-white dark:border-white/20">
                            <option value="all">All transactions</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 3 months</option>
                            <option value="180">Last 6 months</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="transactionsLoading" class="p-5 sm:p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-5/6"></div>
                </div>
            </div>

            <div id="transactionsTable" class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactionsTableElement">
                    <thead class="bg-user-accent dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th data-column="datetime"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary dark:text-white uppercase tracking-wider whitespace-nowrap">
                                Date/Time</th>
                            <th data-column="description"
                                class="px-4 py-3 text-left text-xs font-semibold text-secondary dark:text-white uppercase tracking-wider whitespace-nowrap">
                                Description</th>
                            <th data-column="debit"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary dark:text-white uppercase tracking-wider whitespace-nowrap">
                                Debit</th>
                            <th data-column="credit"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary dark:text-white uppercase tracking-wider whitespace-nowrap">
                                Credit</th>
                            <th data-column="balance"
                                class="px-4 py-3 text-right text-xs font-semibold text-secondary dark:text-white uppercase tracking-wider whitespace-nowrap">
                                Balance</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsTableBody" class="divide-y divide-gray-100 dark:divide-white/10"></tbody>
                </table>
            </div>

            <div id="transactionsMobile" class="lg:hidden p-4 space-y-4 hidden overflow-auto flex-1"></div>

            <div id="transactionsEmpty" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 dark:bg-white/10 rounded-full grid place-items-center mx-auto mb-4">
                    <i class="fas fa-receipt text-gray-400 dark:text-white/60 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No transactions found</h3>
                <p class="text-gray-500 dark:text-white/70 mb-6">Start by adding money to your wallet</p>
                <button onclick="showPaymentMethodModal()"
                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Add
                    Money</button>
            </div>

            <div id="transactionsPagination"
                class="hidden px-6 py-4 border-t border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-white/70" id="paginationInfo"></div>
                    <div class="flex items-center gap-2">
                        <button
                            class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-600 dark:text-white/80 hover:bg-white dark:hover:bg-white/10 transition-colors disabled:opacity-50"
                            disabled>
                            <i class="fas fa-chevron-left mr-1"></i>Previous
                        </button>
                        <button
                            class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-600 dark:text-white/80 hover:bg-white dark:hover:bg-white/10 transition-colors disabled:opacity-50"
                            disabled>
                            Next<i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #balanceText {
        white-space: nowrap !important;
        overflow: hidden
    }

    .transaction-pending {
        background-color: #fef3c7 !important
    }

    .transaction-failed {
        background-color: #fee2e2 !important
    }

    .transaction-pending:hover {
        background-color: #fde68a !important
    }

    .transaction-failed:hover {
        background-color: #fecaca !important
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const ownerName = <?= json_encode(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?>;
        let transactions = [];

        function displayTransactionsView() {
            const tableWrapper = document.getElementById('transactionsTable');
            const mobileWrapper = document.getElementById('transactionsMobile');
            if (window.innerWidth >= 1024) { tableWrapper.classList.remove('hidden'); mobileWrapper.classList.add('hidden'); }
            else { tableWrapper.classList.add('hidden'); mobileWrapper.classList.remove('hidden'); }
        }

        loadWalletData();
        loadTransactions();
        displayTransactionsView();

        window.addEventListener('resize', function () { adjustTableFontSize(); displayTransactionsView(); });

        function loadWalletData() {
            fetch(`${apiUrl}?action=getWallet`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: '' })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.wallet) {
                        const w = data.wallet;
                        document.getElementById('walletName').textContent = w.wallet_name;
                        document.getElementById('ownerName').textContent = ownerName;
                        document.getElementById('walletId').textContent = w.wallet_number;
                        document.getElementById('createdDate').textContent = new Date(w.created_at).toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                        document.getElementById('balanceText').textContent = 'UGX ' + parseFloat(w.current_balance).toLocaleString(undefined, { minimumFractionDigits: 2 });
                        const badge = document.getElementById('statusBadge'); const badgeText = badge.querySelector('span'); const badgeIcon = badge.querySelector('i');
                        badgeText.textContent = w.status.charAt(0).toUpperCase() + w.status.slice(1);
                        badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium';
                        if (w.status === 'active') { badge.classList.add('bg-green-100', 'text-green-800'); badgeIcon.className = 'fas fa-check-circle mr-1'; }
                        else { badge.classList.add('bg-gray-100', 'text-gray-600'); badgeIcon.className = 'fas fa-times-circle mr-1'; }
                        document.getElementById('walletLoading').classList.add('hidden');
                        document.getElementById('balanceLoading').classList.add('hidden');
                        document.getElementById('walletInfo').classList.remove('hidden');
                        document.getElementById('balanceInfo').classList.remove('hidden');
                        setTimeout(adjustBalanceTextSize, 100);
                    } else { showWalletError('Failed to load wallet data'); }
                })
                .catch(() => { showWalletError('Network error loading wallet data'); });
        }

        function showWalletError(message) {
            document.getElementById('walletLoading').innerHTML = `<div class="text-red-600 text-sm">${message}</div>`;
            document.getElementById('balanceLoading').innerHTML = `<div class="text-red-600 text-sm">Error loading balance</div>`;
        }

        function loadTransactions() {
            const filter = document.getElementById('dateFilter').value;
            let params = { action: 'getWalletStatement' };
            if (filter !== 'all') {
                const days = parseInt(filter); const endDate = new Date(); const startDate = new Date(); startDate.setDate(startDate.getDate() - days);
                params.filter = 'range'; params.start = startDate.toISOString().split('T')[0]; params.end = endDate.toISOString().split('T')[0];
            } else { params.filter = 'all'; }

            document.getElementById('transactionsLoading').classList.remove('hidden');
            document.getElementById('transactionsTable').classList.add('hidden');
            document.getElementById('transactionsMobile').classList.add('hidden');
            document.getElementById('transactionsEmpty').classList.add('hidden');
            document.getElementById('transactionsPagination').classList.add('hidden');

            fetch(`${apiUrl}?action=getWalletStatement`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(params) })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('transactionsLoading').classList.add('hidden');
                    if (data.success && data.statement) {
                        transactions = transformStatementData(data.statement);
                        if (transactions.length > 0) { renderTransactions(transactions); updatePaginationInfo(transactions.length); adjustTableFontSize(); displayTransactionsView(); }
                        else { document.getElementById('transactionsEmpty').classList.remove('hidden'); }
                    } else { showTransactionsError(data.message || 'Failed to load transactions'); }
                })
                .catch(() => { document.getElementById('transactionsLoading').classList.add('hidden'); showTransactionsError('Network error loading transactions'); });
        }

        function transformStatementData(statement) {
            const transformed = [];
            statement.forEach(tx => {
                if (tx.transaction && tx.transaction.entries?.length) {
                    const rev = [...tx.transaction.entries].reverse();
                    rev.forEach((e, i) => {
                        transformed.push({
                            transaction_id: tx.transaction.transaction_id,
                            transaction_type: tx.transaction.transaction_type,
                            payment_method: tx.transaction.payment_method,
                            status: tx.transaction.status,
                            amount_total: parseFloat(tx.transaction.amount_total),
                            transaction_note: tx.transaction.note,
                            transaction_date: tx.transaction.created_at,
                            entry_type: e.entry_type,
                            amount: parseFloat(e.amount),
                            balance_after: parseFloat(e.balance_after),
                            entry_note: e.entry_note,
                            entry_date: e.created_at,
                            is_first_in_group: i === 0,
                            group_size: tx.transaction.entries.length
                        });
                    });
                } else if (tx.transaction) {
                    transformed.push({
                        transaction_id: tx.transaction.transaction_id,
                        transaction_type: tx.transaction.transaction_type,
                        payment_method: tx.transaction.payment_method,
                        status: tx.transaction.status,
                        amount_total: parseFloat(tx.transaction.amount_total),
                        transaction_note: tx.transaction.note,
                        transaction_date: tx.transaction.created_at,
                        entry_type: null, amount: 0, balance_after: 0, entry_note: null, entry_date: null, is_first_in_group: true, group_size: 1
                    });
                }
            });
            return transformed.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date));
        }

        function renderTransactions(entries) {
            const tbody = document.getElementById('transactionsTableBody');
            const mobile = document.getElementById('transactionsMobile');
            tbody.innerHTML = ''; mobile.innerHTML = '';

            entries.forEach((entry, idx) => {
                const tr = document.createElement('tr');
                let base = 'transition-colors';
                let light = `${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50`;
                let dark = `${idx % 2 === 0 ? 'dark:bg-secondary' : 'dark:bg-white/5'} dark:hover:bg-white/10`;
                let rowClass = `${base} ${light} ${dark}`;
                if (entry.status === 'PENDING') { rowClass = 'transaction-pending'; }
                else if (entry.status === 'FAILED') { rowClass = 'transaction-failed'; }
                tr.className = rowClass;
                if (entry.is_first_in_group && entry.group_size > 1) { tr.classList.add('border-l-4', 'border-blue-400'); }

                const dt = new Date(entry.transaction_date);
                const dateStr = dt.toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                const debit = entry.entry_type === 'DEBIT' ? entry.amount : 0;
                const credit = entry.entry_type === 'CREDIT' ? entry.amount : 0;
                const mainDesc = entry.transaction_note || entry.entry_note || '';

                let descHtml = `<div class="font-medium text-gray-900 dark:text-white">${mainDesc}</div>`;
                if (entry.amount_total > 0) { descHtml += `<div class="text-xs text-gray-600 dark:text-white/70 mt-1">UGX ${formatCurrency(entry.amount_total)}</div>`; }
                if (entry.payment_method) { descHtml += `<div class="text-xs text-gray-500 dark:text-white/60 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>`; }

                tr.innerHTML = `
                <td data-column="datetime" class="px-4 py-3 text-sm whitespace-nowrap">
                    <div class="font-medium text-gray-900 dark:text-white">${dateStr}</div>
                    <div class="text-xs text-gray-500 dark:text-white/70">${timeStr}</div>
                </td>
                <td data-column="description" class="px-4 py-3 text-sm max-w-[28ch]">
                    <div class="overflow-hidden whitespace-normal" title="${mainDesc}">${descHtml}</div>
                </td>
                <td data-column="debit" class="px-4 py-3 text-sm text-right">
                    ${debit > 0 ? `<span class="font-semibold text-red-600">-${formatCurrency(debit)}</span>` : '<span class="text-gray-400 dark:text-white/50">-</span>'}
                </td>
                <td data-column="credit" class="px-4 py-3 text-sm text-right">
                    ${credit > 0 ? `<span class="font-semibold text-green-600">+${formatCurrency(credit)}</span>` : '<span class="text-gray-400 dark:text-white/50">-</span>'}
                </td>
                <td data-column="balance" class="px-4 py-3 text-sm text-right font-semibold text-gray-900 dark:text-white">
                    ${(entry.entry_type === 'DEBIT' || entry.entry_type === 'CREDIT') ? formatCurrency(entry.balance_after) : '<span class="text-gray-400 dark:text-white/50">-</span>'}
                </td>
            `;
                tbody.appendChild(tr);

                const card = document.createElement('div');
                let cardClass = `bg-white dark:bg-secondary rounded-xl p-4 border border-gray-200 dark:border-white/10 ${entry.is_first_in_group && entry.group_size > 1 ? 'border-l-4 border-l-blue-400' : ''}`;
                if (entry.status === 'PENDING') { cardClass = cardClass.replace('bg-white', 'bg-yellow-50').replace('border-gray-200', 'border-yellow-200'); }
                else if (entry.status === 'FAILED') { cardClass = cardClass.replace('bg-white', 'bg-red-50').replace('border-gray-200', 'border-red-200'); }
                card.className = cardClass;

                let mobileDescHtml = `<div class="font-medium text-secondary dark:text-white text-sm mb-1 break-all">${mainDesc}</div>`;
                if (entry.amount_total > 0) { mobileDescHtml += `<div class="text-xs text-gray-600 dark:text-white/70 mb-1">UGX ${formatCurrency(entry.amount_total)}</div>`; }

                card.innerHTML = `
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1 min-w-0">
                        ${mobileDescHtml}
                        <div class="text-xs text-gray-500 dark:text-white/70">${dateStr} • ${timeStr}</div>
                        ${entry.payment_method ? `<div class="text-xs text-gray-500 dark:text-white/60 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>` : ''}
                    </div>
                    <div class="text-right ml-3 shrink-0">
                        ${debit > 0 ? `<div class="font-semibold text-red-600 text-sm">-${formatCurrency(debit)}</div>` : ''}
                        ${credit > 0 ? `<div class="font-semibold text-green-600 text-sm">+${formatCurrency(credit)}</div>` : ''}
                        <div class="text-xs text-gray-500 dark:text-white/70 mt-1">Balance: ${(entry.entry_type === 'DEBIT' || entry.entry_type === 'CREDIT') ? formatCurrency(entry.balance_after) : '-'}</div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium ${getStatusColor(entry.status)}">${entry.status}</span>
                    <span class="text-[11px] text-gray-500 dark:text-white/60">${entry.transaction_type || ''}</span>
                </div>
            `;
                mobile.appendChild(card);
            });

            applyColumnVisibility();
        }

        function showTransactionsError(msg) { document.getElementById('transactionsLoading').innerHTML = `<div class="text-red-600 text-center p-6">${msg}</div>`; }
        function updatePaginationInfo(cnt) { document.getElementById('paginationInfo').textContent = `Showing 1-${cnt} of ${cnt} transactions`; }
        function formatCurrency(a) { return new Intl.NumberFormat('en-UG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(a); }

        function adjustTableFontSize() {
            const tbl = document.getElementById('transactionsTableElement'); if (!tbl) return;
            const container = tbl.parentElement; let fs = 14; tbl.style.fontSize = fs + 'px';
            while ((tbl.scrollWidth > container.clientWidth || hasOverflowingTransactionDetails()) && fs > 8) { fs -= .5; tbl.style.fontSize = fs + 'px'; }
            if (fs < 10) tbl.style.fontSize = '10px';
        }
        function hasOverflowingTransactionDetails() { for (const el of document.querySelectorAll('.transaction-details')) { if (el.scrollHeight > el.clientHeight) return true; } return false; }

        const STORAGE_KEY = 'zzimba_credit_table_columns';
        let visibleColumns = ['datetime', 'description', 'debit', 'credit', 'balance'];

        function loadColumnSettings() { const saved = localStorage.getItem(STORAGE_KEY); if (saved) { try { visibleColumns = JSON.parse(saved); } catch (e) { } } updateColumnCheckboxes(); applyColumnVisibility(); }
        function saveColumnSettings() { localStorage.setItem(STORAGE_KEY, JSON.stringify(visibleColumns)); }
        function updateColumnCheckboxes() { document.querySelectorAll('.column-checkbox').forEach(cb => { cb.checked = visibleColumns.includes(cb.dataset.column); }); }

        function applyColumnVisibility() {
            const all = ['datetime', 'description', 'debit', 'credit', 'balance'];
            all.forEach(col => {
                const vis = visibleColumns.includes(col);
                document.querySelectorAll(`th[data-column="${col}"]`).forEach(h => h.style.display = vis ? '' : 'none');
                document.querySelectorAll(`td[data-column="${col}"]`).forEach(c => c.style.display = vis ? '' : 'none');
            });
        }

        function toggleColumnSelector() {
            const selector = document.getElementById('columnSelector'); const hidden = selector.classList.contains('hidden');
            if (hidden) { selector.classList.remove('hidden'); document.addEventListener('click', handleClickOutside); }
            else { selector.classList.add('hidden'); document.removeEventListener('click', handleClickOutside); }
        }
        function handleClickOutside(e) {
            const selector = document.getElementById('columnSelector'); const btn = document.getElementById('viewColumnsBtn');
            if (!selector.contains(e.target) && !btn.contains(e.target)) { selector.classList.add('hidden'); document.removeEventListener('click', handleClickOutside); }
        }

        function adjustBalanceTextSize() {
            const balanceInfo = document.getElementById('balanceInfo'); const balanceText = document.getElementById('balanceText');
            if (!balanceInfo || !balanceText || balanceInfo.classList.contains('hidden')) return;
            const container = balanceInfo.parentElement; let fontSize = 48;
            balanceText.style.whiteSpace = 'nowrap'; balanceText.style.fontSize = fontSize + 'px';
            while (balanceText.scrollWidth > container.clientWidth && fontSize > 16) { fontSize -= 2; balanceText.style.fontSize = fontSize + 'px'; }
            if (fontSize < 20) balanceText.style.fontSize = '20px';
        }

        loadColumnSettings();

        document.querySelectorAll('.column-checkbox').forEach(cb => {
            cb.addEventListener('change', function () {
                const column = this.dataset.column; const on = this.checked;
                if (on) { if (!visibleColumns.includes(column)) visibleColumns.push(column); }
                else {
                    if (visibleColumns.length > 3) { visibleColumns = visibleColumns.filter(c => c !== column); }
                    else { this.checked = true; alert('At least 3 columns must be visible'); return; }
                }
                saveColumnSettings(); applyColumnVisibility(); adjustTableFontSize();
            });
        });

        if (window.ResizeObserver) {
            const balanceObserver = new ResizeObserver(adjustBalanceTextSize);
            const balanceInfo = document.getElementById('balanceInfo'); if (balanceInfo) { balanceObserver.observe(balanceInfo.parentElement); }
        }
        window.addEventListener('resize', adjustBalanceTextSize);

        window.toggleColumnSelector = toggleColumnSelector;
        window.loadWalletData = loadWalletData;
        window.loadTransactions = loadTransactions;

        function getStatusColor(status) {
            const colors = { 'New': 'bg-blue-100 text-blue-800', 'Processing': 'bg-yellow-100 text-yellow-800', 'Processed': 'bg-green-100 text-green-800', 'Paid': 'bg-purple-100 text-purple-800', 'SUCCESS': 'bg-green-100 text-green-800', 'PENDING': 'bg-yellow-100 text-yellow-800', 'FAILED': 'bg-red-100 text-red-800' };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }
    });
</script>

<?php
include __DIR__ . '/credit/top-up.php';
include __DIR__ . '/credit/send-credit.php';

$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>