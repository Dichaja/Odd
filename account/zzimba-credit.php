<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';
ob_start();

// Dummy transaction data
$transactions = [
    [
        'transaction_id' => 'TXN001',
        'transaction_details' => 'Mobile Money Deposit - MTN',
        'payment_reference' => 'MM240001234',
        'value_date' => '2024-06-14',
        'credit' => 50000.00,
        'debit' => 0,
        'balance' => 125750.50
    ],
    // ... other dummy transactions ...
];

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
                    <button
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-plus"></i><span>Add Money</span>
                    </button>
                    <button
                        class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center gap-2 font-medium">
                        <i class="fas fa-download"></i><span>Download Statement</span>
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
                                <h2 id="walletName" class="text-xl font-bold text-secondary font-rubik mb-1">Loading...
                                </h2>
                                <p id="ownerName" class="text-gray-600 mb-2"></p>
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-id-card text-xs"></i>
                                        <span id="walletId">--</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-calendar text-xs"></i>
                                        <span id="createdDate">--</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Display -->
                    <div class="lg:text-right">
                        <p class="text-sm font-medium text-gray-600 mb-1">Current Balance</p>
                        <p id="balanceText" class="text-3xl lg:text-4xl font-bold text-primary mb-2">UGX 0.00</p>
                        <span id="statusBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            <i class="fas fa-hourglass-half mr-1"></i>Loading...
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Statement -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Statement</h3>
                        <p class="text-sm text-gray-text mt-1">Recent transactions and account activity</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select
                            class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm">
                            <option>Last 30 days</option>
                            <option>Last 3 months</option>
                            <option>Last 6 months</option>
                            <option>Last year</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Transaction Details</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Payment Reference</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Value Date</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Credit (Money In)</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Debit (Money Out)</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center <?= $transaction['credit'] > 0 ? 'bg-green-100' : 'bg-red-100' ?>">
                                            <i
                                                class="<?= $transaction['credit'] > 0 ? 'fas fa-arrow-down text-green-600' : 'fas fa-arrow-up text-red-600' ?> text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?= $transaction['transaction_details'] ?></div>
                                            <div class="text-xs text-gray-500"><?= $transaction['transaction_id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                    <?= $transaction['payment_reference'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?= date('M d, Y', strtotime($transaction['value_date'])) ?></td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <?php if ($transaction['credit'] > 0): ?>
                                        <span
                                            class="font-semibold text-green-600">+<?= formatCurrency($transaction['credit']) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <?php if ($transaction['debit'] > 0): ?>
                                        <span
                                            class="font-semibold text-red-600">-<?= formatCurrency($transaction['debit']) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    <?= formatCurrency($transaction['balance']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden p-4 space-y-4">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center <?= $transaction['credit'] > 0 ? 'bg-green-100' : 'bg-red-100' ?>">
                                    <i
                                        class="<?= $transaction['credit'] > 0 ? 'fas fa-arrow-down text-green-600' : 'fas fa-arrow-up text-red-600' ?>"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">
                                        <?= $transaction['transaction_details'] ?></div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('M d, Y', strtotime($transaction['value_date'])) ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php if ($transaction['credit'] > 0): ?>
                                    <div class="font-semibold text-green-600 text-sm">
                                        +<?= formatCurrency($transaction['credit']) ?></div>
                                <?php else: ?>
                                    <div class="font-semibold text-red-600 text-sm">
                                        -<?= formatCurrency($transaction['debit']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-gray-500 uppercase tracking-wide">Reference</span>
                                <div class="font-mono text-gray-700 mt-1"><?= $transaction['payment_reference'] ?></div>
                            </div>
                            <div class="text-right">
                                <span class="text-gray-500 uppercase tracking-wide">Balance</span>
                                <div class="font-semibold text-gray-900 mt-1"><?= formatCurrency($transaction['balance']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">Showing 1-8 of 8 transactions</div>
                    <div class="flex items-center gap-2">
                        <button
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-white transition-colors disabled:opacity-50"
                            disabled>
                            <i class="fas fa-chevron-left mr-1"></i>Previous
                        </button>
                        <button
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-white transition-colors disabled:opacity-50"
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
    .font-rubik {
        font-family: 'Rubik', sans-serif;
    }

    .text-primary {
        color: #8c5e2a;
    }

    .bg-primary {
        background-color: #8c5e2a;
    }

    .bg-primary\/10 {
        background-color: rgba(140, 94, 42, 0.1);
    }

    .bg-primary\/5 {
        background-color: rgba(140, 94, 42, 0.05);
    }

    .border-primary {
        border-color: #8c5e2a;
    }

    .text-secondary {
        color: #1f2937;
    }

    .text-gray-text {
        color: #6b7280;
    }

    .shadow-primary\/25 {
        box-shadow: 0 10px 15px -3px rgba(140, 94, 42, 0.25), 0 4px 6px -2px rgba(140, 94, 42, 0.1);
    }

    .hover\:shadow-primary\/30:hover {
        box-shadow: 0 20px 25px -5px rgba(140, 94, 42, 0.3), 0 10px 10px -5px rgba(140, 94, 42, 0.1);
    }

    .focus\:ring-primary\/20:focus {
        box-shadow: 0 0 0 3px rgba(140, 94, 42, 0.2);
    }

    .hover\:bg-primary\/90:hover {
        background-color: rgba(140, 94, 42, 0.9);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const ownerName = <?= json_encode(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?>;

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'getWallet' })
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
                    badge.textContent = w.status.charAt(0).toUpperCase() + w.status.slice(1);
                    badge.classList.toggle('bg-green-100', w.status === 'active');
                    badge.classList.toggle('text-green-800', w.status === 'active');
                    badge.classList.toggle('bg-gray-100', w.status !== 'active');
                    badge.classList.toggle('text-gray-600', w.status !== 'active');
                    badge.querySelector('i').className =
                        w.status === 'active' ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1';
                }
            })
            .catch(() => {
                // ignore
            });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
