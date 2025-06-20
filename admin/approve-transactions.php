<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Approve Transactions';
$activeNav = 'approve-transactions';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Approve Transactions</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactions-table">
                    <thead class="bg-user-accent border-b border-gray-200">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Date/Time</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Transaction ID</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Amount</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Method</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Account</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                User/Vendor</th>
                            <th
                                class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Note</th>
                            <th
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
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions pending</h3>
                <p class="text-gray-500">You have no pending cash transactions.</p>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <h3 id="confirmModalTitle" class="text-lg font-semibold text-gray-900 mb-4">Confirm Action</h3>
            <p id="confirmModalMessage" class="text-gray-600 mb-6"></p>
            <div class="flex gap-3">
                <button onclick="hideConfirmModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmBtn"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashTransactions.php';
    let pendingTxns = [];

    async function fetchPending() {
        try {
            const form = new URLSearchParams();
            form.append('action', 'listPending');
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: form
            });
            const data = await res.json();
            if (data.success) {
                pendingTxns = data.pending;
                renderTable(pendingTxns);
            }
        } catch (err) {
            console.error('Error fetching pending transactions:', err);
        }
    }

    function renderTable(list) {
        const tbody = document.getElementById('transactions-table-body');
        const mobile = document.getElementById('transactions-mobile');
        const empty = document.getElementById('empty-state');
        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            empty.classList.remove('hidden');
            return;
        } else {
            empty.classList.add('hidden');
        }

        list.forEach(tx => {
            const dateObj = new Date(tx.created_at);
            const dateStr = dateObj.toLocaleDateString('en-UG', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            const amount = parseFloat(tx.amount_total).toLocaleString('en-UG', { minimumFractionDigits: 2 });
            const account = tx.cash_account_name || '';
            let userVendor = '';
            if (tx.user) {
                userVendor = `${tx.user.first_name} ${tx.user.last_name}`;
            } else if (tx.vendor) {
                userVendor = tx.vendor.vendor_name;
            }
            const note = tx.note || '';

            // Desktop row
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-3 py-2 text-xs whitespace-nowrap">
                    <div class="font-medium text-gray-900">${dateStr}</div>
                    <div class="text-gray-500 text-xs">${timeStr}</div>
                </td>
                <td class="px-3 py-2 text-xs font-mono whitespace-nowrap">${tx.transaction_id}</td>
                <td class="px-3 py-2 text-xs whitespace-nowrap">UGX ${amount}</td>
                <td class="px-3 py-2 text-xs whitespace-nowrap">${tx.payment_method.replace(/_/g, ' ')}</td>
                <td class="px-3 py-2 text-xs whitespace-nowrap">${account}</td>
                <td class="px-3 py-2 text-xs whitespace-nowrap">${userVendor}</td>
                <td class="px-3 py-2 text-xs">${note}</td>
                <td class="px-3 py-2 text-center text-xs whitespace-nowrap">
                    <button onclick="showConfirm('${tx.transaction_id}','FAILED')"
                        class="px-2 py-1 bg-red-100 text-red-600 rounded transition-colors hover:bg-red-200">Deny</button>
                    <button onclick="showConfirm('${tx.transaction_id}','SUCCESS')"
                        class="px-2 py-1 bg-green-100 text-green-600 rounded ml-2 transition-colors hover:bg-green-200">Approve</button>
                </td>
            `;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl p-4 border border-gray-100';
            card.innerHTML = `
                <div class="grid grid-cols-1 gap-2 text-xs">
                    <div><span class="font-medium">Date:</span> ${dateStr} ${timeStr}</div>
                    <div><span class="font-medium">ID:</span> ${tx.transaction_id}</div>
                    <div><span class="font-medium">Amount:</span> UGX ${amount}</div>
                    <div><span class="font-medium">Method:</span> ${tx.payment_method.replace(/_/g, ' ')}</div>
                    <div><span class="font-medium">Account:</span> ${account}</div>
                    <div><span class="font-medium">User/Vendor:</span> ${userVendor}</div>
                    <div><span class="font-medium">Note:</span> ${note}</div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button onclick="showConfirm('${tx.transaction_id}','FAILED')"
                        class="flex-1 px-4 py-2 bg-red-100 text-red-600 rounded transition-colors hover:bg-red-200">Deny</button>
                    <button onclick="showConfirm('${tx.transaction_id}','SUCCESS')"
                        class="flex-1 px-4 py-2 bg-green-100 text-green-600 rounded transition-colors hover:bg-green-200">Approve</button>
                </div>
            `;
            mobile.appendChild(card);
        });
    }

    function showConfirm(id, status) {
        document.getElementById('confirmModalTitle').textContent = status === 'SUCCESS'
            ? 'Approve Transaction'
            : 'Deny Transaction';
        document.getElementById('confirmModalMessage').textContent = status === 'SUCCESS'
            ? 'Are you sure you want to approve this transaction?'
            : 'Are you sure you want to deny this transaction?';
        const btn = document.getElementById('confirmBtn');
        btn.onclick = () => acknowledge(id, status);
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function hideConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    async function acknowledge(transactionId, status) {
        hideConfirmModal();
        try {
            const form = new URLSearchParams();
            form.append('action', 'acknowledge');
            form.append('transaction_id', transactionId);
            form.append('status', status);
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: form
            });
            const data = await res.json();
            if (data.success) {
                fetchPending();
            } else {
                alert(data.message || 'Failed to update transaction');
            }
        } catch (err) {
            console.error('Error acknowledging transaction:', err);
            alert('An error occurred. Please try again.');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchPending();
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>