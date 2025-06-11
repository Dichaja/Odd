<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Cash Accounts';
$activeNav = 'cash-accounts';
ob_start();
?>

<div class="space-y-6" id="app-container">
    <!-- Accounts View -->
    <div id="accounts-view" class="view-container active">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-secondary">Cash Accounts</h1>
                <p class="text-sm text-gray-text mt-1">Manage your bank, mobile money, and gateway accounts</p>
            </div>
            <button id="create-account-btn"
                class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
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
                    <div class="relative w-full md:w-64">
                        <input type="text" id="searchAccounts" placeholder="Search accounts..."
                            class="w-full h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select id="filterAccounts"
                        class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full md:w-auto">
                        <option value="all">All Types</option>
                        <option value="bank">Bank</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="gateway">Gateway</option>
                    </select>
                </div>
            </div>

            <!-- Desktop Table -->
            <div class="overflow-x-auto">
                <table class="w-full" id="accounts-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Type</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-900">Balance (UGX)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="accounts-table-body">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="responsive-table-mobile p-4" id="accounts-mobile">
                <!-- Populated via JavaScript -->
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start">1</span> to <span id="showing-end">0</span> of <span
                        id="total-accounts">0</span> accounts
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers" class="flex items-center">
                        <!-- Populated via JavaScript if needed -->
                    </div>
                    <button id="next-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" disabled>
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
                <p class="text-sm text-gray-text mt-1" id="statement-account-info" data-account-id="">View transaction
                    history for this account</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-sm text-gray-text">Current Balance</p>
                    <h3 class="text-xl font-semibold text-secondary" id="statement-balance">UGX 0</h3>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Transaction History</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="transaction-count">0</span> transactions found
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative w-full md:w-64">
                        <input type="text" id="searchTransactions" placeholder="Search transactions..."
                            class="w-full h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <button id="exportStatement"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <table class="w-full" id="transactions-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Transaction ID</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Reason</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">User Account</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Amount (UGX)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-900">Balance (UGX)</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body">
                        <!-- Populated via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="responsive-table-mobile p-4" id="transactions-mobile">
                <!-- Populated via JavaScript -->
            </div>

            <!-- Pagination (Transactions) -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="trans-showing-start">1</span> to <span id="trans-showing-end">0</span> of <span
                        id="total-transactions">0</span> transactions
                </div>
                <div class="flex items-center gap-2">
                    <button id="trans-prev-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="trans-pagination-numbers" class="flex items-center">
                        <!-- Populated via JavaScript if needed -->
                    </div>
                    <button id="trans-next-page"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50" disabled>
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
    <div
        class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Create New Account</h3>
                <button onclick="hideCreateAccountForm()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="createAccountForm" class="space-y-6" onsubmit="return false;">
                    <div>
                        <label for="accountMode" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select id="accountMode" name="accountMode"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                            <option value="" disabled selected>Select Type</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="gateway">Gateway</option>
                        </select>
                    </div>

                    <div id="providerContainer"><!-- Populated based on type --></div>

                    <div>
                        <label for="accountName" class="block text-sm font-medium text-gray-700 mb-1">Account
                            Name</label>
                        <input type="text" id="accountName" name="accountName"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter account name" required>
                    </div>

                    <div>
                        <label for="accountNumber" class="block text-sm font-medium text-gray-700 mb-1">Account
                            Number</label>
                        <input type="text" id="accountNumber" name="accountNumber"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter account number" required>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-gray-100">
                <button id="submitAccountForm"
                    class="w-full h-10 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Create Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Account Offcanvas -->
<div id="editAccountOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideEditAccountForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Edit Account</h3>
                <button onclick="hideEditAccountForm()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="editAccountForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editAccountId">
                    <div>
                        <label for="editAccountMode" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select id="editAccountMode" name="editAccountMode"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                            <option value="" disabled>Select Type</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="gateway">Gateway</option>
                        </select>
                    </div>

                    <div id="editProviderContainer"><!-- Populated based on type --></div>

                    <div>
                        <label for="editAccountName" class="block text-sm font-medium text-gray-700 mb-1">Account
                            Name</label>
                        <input type="text" id="editAccountName" name="editAccountName"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter account name" required>
                    </div>

                    <div>
                        <label for="editAccountNumber" class="block text-sm font-medium text-gray-700 mb-1">Account
                            Number</label>
                        <input type="text" id="editAccountNumber" name="editAccountNumber"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter account number" required>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-gray-100">
                <button id="updateAccountForm"
                    class="w-full h-10 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
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
            <p class="text-gray-600 mb-6">Are you sure you want to delete this account? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
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

    .badge-bank {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .badge-mobile_money {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-gateway {
        background-color: #dcfce7;
        color: #166534;
    }

    .amount-credit {
        color: #16a34a;
    }

    .amount-debit {
        color: #dc2626;
    }

    /* Toggle Switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:checked+.slider:before {
        transform: translateX(20px);
    }
</style>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashAccounts.php';
    let accounts = [];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', { style: 'decimal', maximumFractionDigits: 0 }).format(amount);
    }
    function formatDateTime(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
    }
    function getAccountTypeBadge(type) {
        switch (type) {
            case 'bank': return '<span class="account-badge badge-bank"><i class="fas fa-university mr-1"></i>Bank</span>';
            case 'mobile_money': return '<span class="account-badge badge-mobile_money"><i class="fas fa-mobile-alt mr-1"></i>Mobile Money</span>';
            case 'gateway': return '<span class="account-badge badge-gateway"><i class="fas fa-cogs mr-1"></i>Gateway</span>';
            default: return '<span class="account-badge">Unknown</span>';
        }
    }

    async function fetchAccounts() {
        try {
            const res = await fetch(`${API_URL}?action=getCashAccounts`);
            const data = await res.json();
            if (data.success) {
                accounts = data.accounts;
                renderAccountsTable(accounts);
            }
        } catch (err) {
            console.error(err);
        }
    }

    function renderAccountsTable(list) {
        const tbody = document.getElementById('accounts-table-body');
        const mobile = document.getElementById('accounts-mobile');
        tbody.innerHTML = '';
        mobile.innerHTML = '';

        list.forEach(acc => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4 text-sm">${acc.name}</td>
                <td class="px-6 py-4 text-sm">${getAccountTypeBadge(acc.type)}</td>
                <td class="px-6 py-4 text-sm">
                    <label class="switch">
                        <input type="checkbox" ${acc.status === 'active' ? 'checked' : ''} onchange="toggleStatus('${acc.id}', this.checked)">
                        <span class="slider"></span>
                    </label>
                </td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${formatCurrency(acc.balance)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button onclick="showStatement('${acc.id}')" class="text-blue-600 hover:text-blue-800"><i class="fas fa-file-alt"></i></button>
                        <button onclick="showEditAccountForm('${acc.id}')" class="text-gray-600 hover:text-gray-800"><i class="fas fa-edit"></i></button>
                        <button onclick="showDeleteConfirm('${acc.id}')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'mobile-card';
            card.innerHTML = `
                <div class="mobile-card-header flex justify-between">
                    <div class="font-medium text-gray-900">${acc.name}</div>
                    <div>${getAccountTypeBadge(acc.type)}</div>
                </div>
                <div class="mobile-card-content mt-2">
                    <div class="mobile-grid grid grid-cols-2 gap-4">
                        <div class="mobile-grid-item"><span class="mobile-label block text-xs text-gray-500">Balance</span><span class="mobile-value block font-medium">${formatCurrency(acc.balance)}</span></div>
                        <div class="mobile-grid-item"><span class="mobile-label block text-xs text-gray-500">Status</span><span class="mobile-value block"><label class="switch"><input type="checkbox" ${acc.status === 'active' ? 'checked' : ''} onchange="toggleStatus('${acc.id}', this.checked)"><span class="slider"></span></label></span></div>
                    </div>
                    <div class="mobile-actions flex gap-2 mt-4">
                        <button onclick="showStatement('${acc.id}')" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100"><i class="fas fa-file-alt mr-1"></i>Statement</button>
                        <button onclick="showEditAccountForm('${acc.id}')" class="px-3 py-1.5 text-xs bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100"><i class="fas fa-edit mr-1"></i>Edit</button>
                        <button onclick="showDeleteConfirm('${acc.id}')" class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100"><i class="fas fa-trash-alt mr-1"></i>Delete</button>
                    </div>
                </div>`;
            mobile.appendChild(card);
        });

        document.getElementById('showing-start').textContent = list.length ? '1' : '0';
        document.getElementById('showing-end').textContent = list.length;
        document.getElementById('total-accounts').textContent = list.length;
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
                if (acc) acc.status = isActive ? 'active' : 'inactive';
            } else {
                alert('Failed to update status');
            }
        } catch (err) {
            console.error(err);
            alert('Error updating status');
        }
    }

    function renderTransactionsTable(list) {
        const tbody = document.getElementById('transactions-table-body');
        const mobile = document.getElementById('transactions-mobile');
        tbody.innerHTML = '';
        mobile.innerHTML = '';
        list.forEach(tx => {
            const cls = tx.isCredit ? 'amount-credit' : 'amount-debit';
            const sign = tx.isCredit ? '+' : '-';
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-500">${formatDateTime(tx.date)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${tx.id}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${tx.reason}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${tx.userAccount}</td>
                <td class="px-6 py-4 text-sm font-medium ${cls}">${sign} UGX ${formatCurrency(tx.amount)}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">UGX ${formatCurrency(tx.balance)}</td>`;
            tbody.appendChild(tr);

            const card = document.createElement('div');
            card.className = 'mobile-card';
            card.innerHTML = `
                <div class="mobile-card-header">
                    <div><div class="font-medium text-gray-900">${tx.id}</div><div class="text-xs text-gray-500 mt-1">${formatDateTime(tx.date)}</div></div>
                    <div class="${cls} font-medium">${sign} UGX ${formatCurrency(tx.amount)}</div>
                </div>
                <div class="mobile-card-content">
                    <div><span class="mobile-label">Reason</span><span class="mobile-value block">${tx.reason}</span></div>
                    <div><span class="mobile-label">Balance</span><span class="mobile-value">UGX ${formatCurrency(tx.balance)}</span></div>
                </div>`;
            mobile.appendChild(card);
        });
        document.getElementById('trans-showing-start').textContent = list.length ? '1' : '0';
        document.getElementById('trans-showing-end').textContent = list.length;
        document.getElementById('total-transactions').textContent = list.length;
        document.getElementById('transaction-count').textContent = list.length;
    }

    function filterAccounts(mode) {
        if (mode === 'all') return accounts;
        return accounts.filter(a => a.type === mode);
    }
    function searchAccounts(q, mode) {
        let f = filterAccounts(mode);
        if (!q) return f;
        q = q.toLowerCase();
        return f.filter(a => a.name.toLowerCase().includes(q));
    }

    function showStatement(id) {
        const acc = accounts.find(a => a.id === id);
        if (!acc) return;
        document.getElementById('statement-account-info').textContent = acc.name;
        document.getElementById('statement-account-info').setAttribute('data-account-id', id);
        document.getElementById('statement-balance').textContent = `UGX ${formatCurrency(acc.balance)}`;
        const txs = (window.dummyTransactions || {})[id] || [];
        renderTransactionsTable(txs);
        document.getElementById('accounts-view').classList.replace('active', 'hidden');
        document.getElementById('statement-view').classList.replace('hidden', 'active');
    }
    function backToAccounts() {
        document.getElementById('statement-view').classList.replace('active', 'hidden');
        document.getElementById('accounts-view').classList.replace('hidden', 'active');
    }

    function updateProviderField(form, type, value = '') {
        const c = document.getElementById(form === 'edit' ? 'editProviderContainer' : 'providerContainer');
        if (type === 'bank') {
            c.innerHTML = `<div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Branch</label>
                <input type="text" id="${form}ProviderName" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" value="${value}" placeholder="Enter branch name" required>
            </div>`;
        } else if (type === 'mobile_money') {
            c.innerHTML = `<div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Operator</label>
                <select id="${form}ProviderOperator" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                    <option value="" disabled ${!value ? 'selected' : ''}>Select operator</option>
                    <option value="MTN" ${value === 'MTN' ? 'selected' : ''}>MTN</option>
                    <option value="Airtel" ${value === 'Airtel' ? 'selected' : ''}>Airtel</option>
                </select>
            </div>`;
        } else if (type === 'gateway') {
            c.innerHTML = `<div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gateway Name</label>
                <input type="text" id="${form}ProviderName" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" value="${value}" placeholder="Enter gateway name" required>
            </div>`;
        } else {
            c.innerHTML = '';
        }
    }

    async function createAccount() {
        const type = document.getElementById('accountMode').value;
        const name = document.getElementById('accountName').value.trim();
        const number = document.getElementById('accountNumber').value.trim();
        let provider = '';
        if (type === 'bank' || type === 'gateway') {
            provider = document.getElementById('createProviderName').value.trim();
        } else if (type === 'mobile_money') {
            provider = document.getElementById('createProviderOperator').value;
        }
        const res = await fetch(`${API_URL}?action=createCashAccount`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type, name, number, provider })
        });
        const d = await res.json();
        if (d.success) {
            hideCreateAccountForm();
            fetchAccounts();
        } else {
            alert(d.message);
        }
    }

    async function updateAccount() {
        const id = document.getElementById('editAccountId').value;
        const type = document.getElementById('editAccountMode').value;
        const name = document.getElementById('editAccountName').value.trim();
        const number = document.getElementById('editAccountNumber').value.trim();
        let provider = '';
        if (type === 'bank' || type === 'gateway') {
            provider = document.getElementById('editProviderName').value.trim();
        } else if (type === 'mobile_money') {
            provider = document.getElementById('editProviderOperator').value;
        }
        const res = await fetch(`${API_URL}?action=updateCashAccount`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, type, name, number, provider })
        });
        const d = await res.json();
        if (d.success) {
            hideEditAccountForm();
            fetchAccounts();
        } else {
            alert(d.message);
        }
    }

    async function deleteAccount() {
        const id = document.getElementById('confirmDeleteBtn').getAttribute('data-account-id');
        const res = await fetch(`${API_URL}?action=deleteCashAccount`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const d = await res.json();
        if (d.success) {
            hideDeleteConfirm();
            fetchAccounts();
        } else {
            alert(d.message);
        }
    }

    function showCreateAccountForm() {
        const off = document.getElementById('createAccountOffcanvas');
        off.classList.remove('hidden');
        setTimeout(() => off.querySelector('.translate-x-full')?.classList.remove('translate-x-full'), 10);
    }
    function hideCreateAccountForm() {
        const off = document.getElementById('createAccountOffcanvas');
        off.querySelector('.transform')?.classList.add('translate-x-full');
        setTimeout(() => off.classList.add('hidden'), 300);
    }
    function showEditAccountForm(id) {
        const acc = accounts.find(a => a.id === id);
        if (!acc) return;
        document.getElementById('editAccountId').value = acc.id;
        document.getElementById('editAccountMode').value = acc.type;
        document.getElementById('editAccountName').value = acc.name;
        document.getElementById('editAccountNumber').value = acc.number;
        updateProviderField('edit', acc.type, acc.provider);
        const off = document.getElementById('editAccountOffcanvas');
        off.classList.remove('hidden');
        setTimeout(() => off.querySelector('.translate-x-full')?.classList.remove('translate-x-full'), 10);
    }
    function hideEditAccountForm() {
        const off = document.getElementById('editAccountOffcanvas');
        off.querySelector('.transform')?.classList.add('translate-x-full');
        setTimeout(() => off.classList.add('hidden'), 300);
    }
    function showDeleteConfirm(id) {
        const m = document.getElementById('deleteConfirmModal');
        m.classList.remove('hidden');
        document.getElementById('confirmDeleteBtn').setAttribute('data-account-id', id);
    }
    function hideDeleteConfirm() {
        document.getElementById('deleteConfirmModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchAccounts();
        document.getElementById('create-account-btn').addEventListener('click', showCreateAccountForm);
        document.getElementById('accountMode').addEventListener('change', e => updateProviderField('create', e.target.value));
        document.getElementById('submitAccountForm').addEventListener('click', createAccount);
        document.getElementById('editAccountMode').addEventListener('change', e => updateProviderField('edit', e.target.value));
        document.getElementById('updateAccountForm').addEventListener('click', updateAccount);
        document.getElementById('confirmDeleteBtn').addEventListener('click', deleteAccount);
        document.getElementById('back-to-accounts').addEventListener('click', backToAccounts);

        document.getElementById('filterAccounts').addEventListener('change', () => {
            const q = document.getElementById('searchAccounts').value.trim();
            const m = document.getElementById('filterAccounts').value;
            renderAccountsTable(searchAccounts(q, m));
        });
        document.getElementById('searchAccounts').addEventListener('input', () => {
            const q = document.getElementById('searchAccounts').value.trim();
            const m = document.getElementById('filterAccounts').value;
            renderAccountsTable(searchAccounts(q, m));
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>