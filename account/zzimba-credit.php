<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';
ob_start();

// Dummy user wallet data
$userWallet = [
    'wallet_id' => 'ZW001234567890',
    'wallet_name' => 'Personal Wallet',
    'owner_name' => 'John Doe Mukasa',
    'current_balance' => 125750.50,
    'currency' => 'UGX',
    'status' => 'active',
    'created_date' => '2023-08-15'
];

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
    [
        'transaction_id' => 'TXN002',
        'transaction_details' => 'Purchase - Nakumatt Supermarket',
        'payment_reference' => 'POS240005678',
        'value_date' => '2024-06-13',
        'credit' => 0,
        'debit' => 25000.00,
        'balance' => 75750.50
    ],
    [
        'transaction_id' => 'TXN003',
        'transaction_details' => 'Bank Transfer - Equity Bank',
        'payment_reference' => 'BT240009876',
        'value_date' => '2024-06-12',
        'credit' => 100000.00,
        'debit' => 0,
        'balance' => 100750.50
    ],
    [
        'transaction_id' => 'TXN004',
        'transaction_details' => 'Online Payment - Jumia Uganda',
        'payment_reference' => 'ON240012345',
        'value_date' => '2024-06-11',
        'credit' => 0,
        'debit' => 15500.00,
        'balance' => 750.50
    ],
    [
        'transaction_id' => 'TXN005',
        'transaction_details' => 'Salary Credit - ABC Company Ltd',
        'payment_reference' => 'SAL240067890',
        'value_date' => '2024-06-10',
        'credit' => 16250.00,
        'debit' => 0,
        'balance' => 16250.50
    ],
    [
        'transaction_id' => 'TXN006',
        'transaction_details' => 'Utility Payment - UMEME',
        'payment_reference' => 'UTL240054321',
        'value_date' => '2024-06-09',
        'credit' => 0,
        'debit' => 45000.00,
        'balance' => 0.50
    ],
    [
        'transaction_id' => 'TXN007',
        'transaction_details' => 'Mobile Money Deposit - Airtel',
        'payment_reference' => 'AM240098765',
        'value_date' => '2024-06-08',
        'credit' => 45000.00,
        'debit' => 0,
        'balance' => 45000.50
    ],
    [
        'transaction_id' => 'TXN008',
        'transaction_details' => 'Initial Deposit',
        'payment_reference' => 'INIT240000001',
        'value_date' => '2024-06-07',
        'credit' => 0.50,
        'debit' => 0,
        'balance' => 0.50
    ]
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
                                <h2 class="text-xl font-bold text-secondary font-rubik mb-1" id="walletName">
                                    <?= $userWallet['wallet_name'] ?>
                                </h2>
                                <p class="text-gray-600 mb-2" id="ownerName"><?= $userWallet['owner_name'] ?></p>
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-id-card text-xs"></i>
                                        <strong>Wallet ID:</strong> <span
                                            id="walletId"><?= $userWallet['wallet_id'] ?></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-calendar text-xs"></i>
                                        <strong>Created:</strong> <span
                                            id="createdDate"><?= date('M d, Y', strtotime($userWallet['created_date'])) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance Display -->
                    <div class="lg:text-right">
                        <p class="text-sm font-medium text-gray-600 mb-1">Current Balance</p>
                        <p class="text-3xl lg:text-4xl font-bold text-primary mb-2" id="balanceText">
                            <?= $userWallet['currency'] ?> <?= formatCurrency($userWallet['current_balance']) ?>
                        </p>
                        <span id="statusBadge"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Active
                        </span>
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
                <table class="w-full" id="transactionsTable">
                    <thead class="bg-user-accent border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Transaction Details
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Payment Reference
                            </th>
                            <th
                                class="px-4 py-3 text-left font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Value Date
                            </th>
                            <th
                                class="px-4 py-3 text-right font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Credit (Money In)
                            </th>
                            <th
                                class="px-4 py-3 text-right font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Debit (Money Out)
                            </th>
                            <th
                                class="px-4 py-3 text-right font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                Balance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <tr
                                class="<?= $index % 2 === 0 ? 'bg-user-content' : 'bg-white' ?> hover:bg-user-secondary/20 transition-colors">
                                <td
                                    class="px-4 py-3 <?= $index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10' ?>">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center <?= $transaction['credit'] > 0 ? 'bg-green-100' : 'bg-red-100' ?>">
                                            <i
                                                class="<?= $transaction['credit'] > 0 ? 'fas fa-arrow-down text-green-600' : 'fas fa-arrow-up text-red-600' ?> text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                <?= $transaction['transaction_details'] ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?= $transaction['transaction_id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-gray-600 font-mono whitespace-nowrap <?= $index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20' ?>">
                                    <?= $transaction['payment_reference'] ?>
                                </td>
                                <td
                                    class="px-4 py-3 text-gray-600 whitespace-nowrap <?= $index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10' ?>">
                                    <?= date('M d, Y', strtotime($transaction['value_date'])) ?>
                                </td>
                                <td
                                    class="px-4 py-3 text-right whitespace-nowrap <?= $index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20' ?>">
                                    <?php if ($transaction['credit'] > 0): ?>
                                        <span
                                            class="font-semibold text-green-600">+<?= formatCurrency($transaction['credit']) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td
                                    class="px-4 py-3 text-right whitespace-nowrap <?= $index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10' ?>">
                                    <?php if ($transaction['debit'] > 0): ?>
                                        <span
                                            class="font-semibold text-red-600">-<?= formatCurrency($transaction['debit']) ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-semibold text-gray-900 whitespace-nowrap <?= $index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20' ?>">
                                    <?= formatCurrency($transaction['balance']) ?>
                                </td>
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
                                        <?= $transaction['transaction_details'] ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('M d, Y', strtotime($transaction['value_date'])) ?>
                                    </div>
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
                    <div class="text-sm text-gray-600">
                        Showing 1-8 of 8 transactions
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
                            <input type="tel" id="phoneNumber" name="phoneNumber"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="+256771234567" required>
                            <div id="phoneValidationSpinner" class="absolute right-3 top-3 hidden">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                            </div>
                        </div>
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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const ownerName = <?= json_encode(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?>;

        let validatedMsisdn = null;
        let customerName = null;
        let currentPaymentReference = null;
        let statusCheckInterval = null;
        let validationTimeout = null;

        // Load wallet data
        loadWalletData();

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
        }

        function adjustTableFontSize() {
            const table = document.getElementById('transactionsTable');
            if (!table) return;

            const container = table.parentElement;
            let fontSize = 14; // Start with base font size

            table.style.fontSize = fontSize + 'px';

            // Check if table overflows horizontally
            while (table.scrollWidth > container.clientWidth && fontSize > 8) {
                fontSize -= 0.5;
                table.style.fontSize = fontSize + 'px';
            }

            // Ensure minimum readable font size
            if (fontSize < 10) {
                table.style.fontSize = '10px';
            }
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
        async function validatePhoneNumber(phone = null) {
            const phoneInput = document.getElementById('phoneNumber');
            const spinner = document.getElementById('phoneValidationSpinner');
            const customerNameDiv = document.getElementById('customerName');
            const phoneErrorDiv = document.getElementById('phoneError');
            const submitBtn = document.getElementById('submitPaymentBtn');

            const phoneValue = phone || phoneInput.value.trim();
            if (!phoneValue) {
                showPhoneError('Please enter a phone number');
                return;
            }

            // Format phone number
            let formattedPhone = phoneValue;
            if (phoneValue.startsWith('0')) {
                formattedPhone = '+256' + phoneValue.substring(1);
            } else if (!phoneValue.startsWith('+')) {
                formattedPhone = '+256' + phoneValue;
            }

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
                    phoneInput.value = formattedPhone;
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
                        if (data.status === 'completed') {
                            clearInterval(statusCheckInterval);
                            showPaymentStatus('success', 'Payment Successful', `Payment of UGX ${data.amount} has been completed successfully.`);

                            // Reload wallet data
                            setTimeout(() => {
                                loadWalletData();
                                hideAddMoneyModal();
                            }, 3000);
                        } else if (data.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            showPaymentStatus('error', 'Payment Failed', data.message || 'Payment was not completed');
                            document.getElementById('submitPaymentBtn').disabled = false;
                            document.getElementById('submitPaymentBtn').textContent = 'Add Money';
                        }
                        // If status is still pending, continue checking
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                }
            }, 3000); // Check every 3 seconds
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
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>