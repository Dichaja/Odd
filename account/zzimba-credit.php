<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';

// Function to format date with suffix
function formatDateWithSuffix($timestamp)
{
    $day = date('j', $timestamp);
    $suffix = '';

    if ($day % 10 == 1 && $day != 11) {
        $suffix = 'st';
    } elseif ($day % 10 == 2 && $day != 12) {
        $suffix = 'nd';
    } elseif ($day % 10 == 3 && $day != 13) {
        $suffix = 'rd';
    } else {
        $suffix = 'th';
    }

    return date('F ' . $day . $suffix . ', Y g:iA', $timestamp);
}

// Sample data for payment accounts
$paymentAccounts = [
    [
        'id' => '7935',
        'type' => 'Mtn',
        'name' => 'TEM',
        'number' => '256392003406',
        'active' => true
    ],
    [
        'id' => '4622',
        'type' => 'Zzimba online Momo',
        'name' => 'Masika',
        'number' => '0392003406',
        'active' => false
    ],
    [
        'id' => '6023',
        'type' => 'DFCU Bugolobi',
        'name' => 'The Engineering Marksmen',
        'number' => '01411023713141',
        'active' => false
    ]
];

// Sample data for transactions
$transactions = [
    [
        'date' => '2025-02-13 11:35:26',
        'id' => '13881',
        'reason' => 'Credit Transfer',
        'account' => 'Zzimba Online',
        'amount' => '10,000',
        'isCredit' => false,
        'balance' => '98,500'
    ],
    [
        'date' => '2025-02-01 21:04:47',
        'id' => '01627',
        'reason' => 'Credit Transfer',
        'account' => 'HC Consult',
        'amount' => '500',
        'isCredit' => false,
        'balance' => '88,500'
    ],
    [
        'date' => '2025-02-01 11:25:51',
        'id' => '01757',
        'reason' => 'Credit Transfer',
        'account' => 'Zzimba Online',
        'amount' => '500',
        'isCredit' => false,
        'balance' => '89,000'
    ],
    [
        'date' => '2025-01-31 15:10:00',
        'id' => '31815',
        'reason' => 'Credit Transfer',
        'account' => 'Zzimba Online',
        'amount' => '500',
        'isCredit' => false,
        'balance' => '88,500'
    ],
    [
        'date' => '2024-11-06 01:19:57',
        'id' => '74064',
        'reason' => 'Agent Commission',
        'account' => 'Zzimba Wallet',
        'amount' => '5,000',
        'isCredit' => true,
        'balance' => '88,000'
    ],
    [
        'date' => '2024-11-06 01:19:57',
        'id' => '74064',
        'reason' => 'HC Consult Subscription',
        'account' => 'Zzimba Wallet',
        'amount' => '30,000',
        'isCredit' => false,
        'balance' => '83,000'
    ],
    [
        'date' => '2024-10-03 12:25:00',
        'id' => '6148',
        'reason' => 'Agent Commission',
        'account' => 'Zzimba Wallet',
        'amount' => '5,000',
        'isCredit' => true,
        'balance' => '113,000'
    ],
    [
        'date' => '2024-10-03 12:25:00',
        'id' => '6148',
        'reason' => 'TEM Subscription',
        'account' => 'Zzimba Wallet',
        'amount' => '30,000',
        'isCredit' => false,
        'balance' => '108,000'
    ],
    [
        'date' => '2024-08-07 06:51:00',
        'id' => 'Z100195',
        'reason' => 'Pay Off',
        'account' => 'Zzimba Credit',
        'amount' => '82,000',
        'isCredit' => true,
        'balance' => '138,000'
    ],
    [
        'date' => '2024-08-06 21:08:00',
        'id' => 'Z102836',
        'reason' => 'Pay Off',
        'account' => 'Zzimba Credit',
        'amount' => '56,000',
        'isCredit' => true,
        'balance' => '56,000'
    ]
];

ob_start();
?>

<div class="space-y-6">
    <!-- Credit Balance Card -->
    <div class="content-section bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-user-primary/10 flex items-center justify-center">
                        <i class="fas fa-credit-card text-user-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-semibold text-secondary">Zzimba Credit</h1>
                        <p class="text-sm text-gray-text">Manage your credit balance and transactions</p>
                    </div>
                </div>
                <div class="flex flex-col items-center">
                    <div class="text-2xl font-bold text-green-600">UGX 98,500</div>
                    <button id="manageAccount" class="mt-2 px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-cog"></i>
                        <span>Manage Account</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Accounts Section -->
    <div class="content-section bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-semibold text-secondary">Payment Accounts</h2>
                <button id="addPaymentAccount" class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Add Payment Account</span>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($paymentAccounts as $account): ?>
                    <div class="border border-gray-100 rounded-lg p-4 <?= $account['active'] ? 'bg-user-secondary/10' : 'bg-white' ?>">
                        <div class="flex flex-col">
                            <div class="font-semibold text-secondary"><?= htmlspecialchars($account['type']) ?></div>
                            <div class="text-sm text-gray-text mt-1"><?= htmlspecialchars($account['name']) ?></div>
                            <div class="text-sm text-gray-text"><?= htmlspecialchars($account['number']) ?></div>
                            <div class="flex justify-end mt-2">
                                <button class="text-gray-500 hover:text-user-primary transition-colors" data-account-id="<?= $account['id'] ?>">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Transactions Section -->
    <div class="content-section bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <h2 class="text-xl font-semibold text-secondary">Zzimba Credit Transaction Statement</h2>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="searchTransactions" placeholder="Search transactions..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select id="filterTransactions" class="h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        <option value="all">All Transactions</option>
                        <option value="credit">Credits Only</option>
                        <option value="debit">Debits Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="transactions-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Trx - ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Reason</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Preferred Account</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Amount</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Balance</th>
                    </tr>
                </thead>
                <tbody id="transactions-table-body">
                    <?php foreach ($transactions as $transaction):
                        $timestamp = strtotime($transaction['date']);
                        $formattedDate = formatDateWithSuffix($timestamp);
                        $amountClass = $transaction['isCredit'] ? 'text-green-600' : 'text-red-600';
                    ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-transaction-id="<?= $transaction['id'] ?>">
                            <td class="px-6 py-4 text-sm text-gray-text"><?= $formattedDate ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-secondary">TRX-<?= $transaction['id'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-text"><?= htmlspecialchars($transaction['reason']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-text"><?= htmlspecialchars($transaction['account']) ?></td>
                            <td class="px-6 py-4 text-sm font-medium <?= $amountClass ?>">
                                <?= $transaction['isCredit'] ? '+' : '-' ?> UGX <?= $transaction['amount'] ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-secondary">UGX <?= $transaction['balance'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden">
            <?php foreach ($transactions as $transaction):
                $timestamp = strtotime($transaction['date']);
                $formattedDate = formatDateWithSuffix($timestamp);
                $amountClass = $transaction['isCredit'] ? 'text-green-600' : 'text-red-600';
            ?>
                <div class="border-b border-gray-100 p-4" data-transaction-id="<?= $transaction['id'] ?>">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-medium text-secondary">TRX-<?= $transaction['id'] ?></div>
                            <div class="text-xs text-gray-text"><?= $formattedDate ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium <?= $amountClass ?>">
                                <?= $transaction['isCredit'] ? '+' : '-' ?> UGX <?= $transaction['amount'] ?>
                            </div>
                            <div class="text-xs text-gray-text">Balance: UGX <?= $transaction['balance'] ?></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <div class="text-xs text-gray-text">Reason</div>
                            <div class="text-gray-700"><?= htmlspecialchars($transaction['reason']) ?></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-text">Account</div>
                            <div class="text-gray-700"><?= htmlspecialchars($transaction['account']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-transactions"><?= count($transactions) ?></span> transactions
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-user-primary text-white">1</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Account Modal -->
<div id="manageAccountModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('manageAccountModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Manage Your Credit Account</h3>
                <button onclick="hideModal('manageAccountModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="manageWalletForm">
                <input name="otp" type="hidden" id="otp" value="2149">
                <div class="space-y-4">
                    <div>
                        <label for="cash_trans" class="block text-sm font-medium text-gray-700 mb-1">Cash Transaction</label>
                        <select class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary" name="cash_trans" id="cash_trans">
                            <option value="" selected="selected">Select</option>
                            <option value="03_9129">Business / Investment</option>
                            <option value="03_1212">Credit Transfer</option>
                            <option value="02_0002">Deposit</option>
                            <option value="03_3331">Educational / Seminars</option>
                            <option value="03_5656">Gift / Donation</option>
                            <option value="03_1111">Shopping Voucher</option>
                            <option value="04_0700">Subscriptions</option>
                            <option value="01_0001">Withdraw</option>
                        </select>
                    </div>
                    <div class="trans hidden">
                        <label for="getUser" class="block text-sm font-medium text-gray-700 mb-1">Target Account</label>
                        <div class="relative">
                            <input type="text" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary" name="getUser" id="getUser" autocomplete="off">
                            <input type="hidden" id="dataVal" value="" name="dataVal">
                            <div id="drop_list" class="absolute z-10 w-full bg-white shadow-lg rounded-lg border border-gray-200 max-h-60 overflow-y-auto hidden mt-1">
                                <!-- User suggestions will appear here -->
                            </div>
                        </div>
                    </div>
                    <div class="prefAcc">
                        <label for="account_id" class="block text-sm font-medium text-gray-700 mb-1">Preferred Account</label>
                        <select class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary" name="account_id" id="account_id">
                            <option value="" selected="selected">Select</option>
                            <?php foreach ($paymentAccounts as $account): ?>
                                <option value="<?= $account['id'] ?>">
                                    (<?= htmlspecialchars($account['type']) ?>) <?= htmlspecialchars($account['number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="transCode hidden">
                        <label for="trans_code" class="block text-sm font-medium text-gray-700 mb-1">Transaction Code</label>
                        <input type="text" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary" name="trans_code" id="trans_code">
                    </div>
                    <div>
                        <label for="cash_amt" class="block text-sm font-medium text-gray-700 mb-1">Cash Amount <span id="trans_response" class="text-red-600 text-xs"></span></label>
                        <input type="text" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary" name="cash_amt" id="cash_amt">
                    </div>
                    <div class="mt-4">
                        <h4 class="text-center font-medium text-secondary mb-3">Payment Options</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="payment-option" data-method="credit">
                                <div class="w-full aspect-square rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary/20 transition-colors">
                                    <i class="fas fa-credit-card text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Zzimba Credit</p>
                            </div>
                            <div class="payment-option" data-method="bank">
                                <div class="w-full aspect-square rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary/20 transition-colors">
                                    <i class="fas fa-university text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Bank</p>
                            </div>
                            <div class="payment-option" data-method="mobile">
                                <div class="w-full aspect-square rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary/20 transition-colors">
                                    <i class="fas fa-mobile-alt text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Mobile Money</p>
                            </div>
                        </div>
                    </div>
                    <div id="paymentDetails" class="hidden mt-4"></div>
                    <div class="text-center text-xs text-gray-text mt-4">
                        Account is Activated Upon Payment Confirmation
                    </div>
                    <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        SUBMIT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Payment Account Modal -->
<div id="addPaymentAccountModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('addPaymentAccountModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Set Payment Account</h3>
                <button onclick="hideModal('addPaymentAccountModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addPaymentAccountForm">
                <input type="hidden" name="post_mop" id="post_mop" value="1365985">
                <div class="space-y-4">
                    <div>
                        <label for="mop_type" class="block text-sm font-medium text-gray-700 mb-1">Mode of Payment</label>
                        <select name="mop_type" id="mop_type" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option selected="selected" value="">Select</option>
                            <option value="02">Bank</option>
                            <option value="03">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label for="branch" class="block text-sm font-medium text-gray-700 mb-1">Branch / Mobile Operator</label>
                        <input type="text" name="branch" id="branch" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                        <input type="text" name="name" id="name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="accNo" class="block text-sm font-medium text-gray-700 mb-1">Account No</label>
                        <input type="text" name="accNo" id="accNo" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        SUBMIT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="transactionDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('transactionDetailsModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Transaction Details</h3>
                <button onclick="hideModal('transactionDetailsModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="transactionDetails" class="space-y-4">
                <!-- Transaction details will be populated here -->
            </div>
            <div class="mt-6 flex justify-end">
                <button onclick="hideModal('transactionDetailsModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Processing...</p>
    </div>
</div>

<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
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

        .mobile-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .mobile-card-content {
            padding: 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .mobile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
    }

    .text-success {
        color: #16a34a;
    }

    .text-danger {
        color: #dc2626;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-initiated {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-completed {
        background-color: #dcfce7;
        color: #166534;
    }
</style>

<script>
    $(document).ready(function() {
        // Manage Account Modal
        $('#manageAccount').click(function() {
            showModal('manageAccountModal');
        });

        // Add Payment Account Modal
        $('#addPaymentAccount').click(function() {
            showModal('addPaymentAccountModal');
        });

        // Transaction Type Change
        $('#cash_trans').change(function() {
            const value = $(this).val();

            // Reset fields
            $('.trans, .transCode').addClass('hidden');
            $('#trans_response').text('');

            if (value.startsWith('03_')) {
                // Credit Transfer, Business/Investment, Educational, Gift, Shopping
                $('.trans').removeClass('hidden');
            } else if (value.startsWith('01_')) {
                // Withdraw
                $('.transCode').removeClass('hidden');
            }
        });

        // Payment method selection
        $('.payment-option').click(function() {
            $('.payment-option').removeClass('active');
            $(this).addClass('active');

            const method = $(this).data('method');
            let detailsHtml = '';

            if (method === 'credit') {
                detailsHtml = `
                    <div class="p-4 bg-user-secondary/10 rounded-lg">
                        <p class="text-sm">Your Zzimba Credit Balance: <strong>UGX 98,500</strong></p>
                    </div>
                `;
            } else if (method === 'bank') {
                detailsHtml = `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm mb-2">Bank: <strong>Stanbic Bank</strong></p>
                        <p class="text-sm mb-2">Account Name: <strong>Zzimba Online Ltd</strong></p>
                        <p class="text-sm">Account Number: <strong>9030012345678</strong></p>
                    </div>
                `;
            } else if (method === 'mobile') {
                detailsHtml = `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm mb-2">Mobile Money Number: <strong>0772 123456</strong></p>
                        <p class="text-sm">Name: <strong>Zzimba Online Ltd</strong></p>
                    </div>
                `;
            }

            $('#paymentDetails').html(detailsHtml).removeClass('hidden');
        });

        // Form submissions
        $('#manageWalletForm').submit(function(e) {
            e.preventDefault();
            showLoading();

            // Simulate form submission
            setTimeout(function() {
                hideLoading();
                hideModal('manageAccountModal');
                showSuccessNotification('Transaction submitted successfully!');
            }, 1500);
        });

        $('#addPaymentAccountForm').submit(function(e) {
            e.preventDefault();
            showLoading();

            // Simulate form submission
            setTimeout(function() {
                hideLoading();
                hideModal('addPaymentAccountModal');
                showSuccessNotification('Payment account added successfully!');
            }, 1500);
        });

        // Transaction row click to show details
        $(document).on('click', 'tr[data-transaction-id], div[data-transaction-id]', function() {
            const transactionId = $(this).data('transaction-id');
            showTransactionDetails(transactionId);
        });

        // Pagination functionality
        let currentPage = 1;
        const itemsPerPage = 5;
        const totalItems = $('#transactions-table-body tr').length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        updatePagination();

        $('#prev-page').click(function() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });

        $('#next-page').click(function() {
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
            }
        });

        // Search functionality
        $('#searchTransactions').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterTransactions(searchTerm);
        });

        // Filter dropdown
        $('#filterTransactions').change(function() {
            const filterValue = $(this).val();
            const searchTerm = $('#searchTransactions').val().toLowerCase();
            filterTransactions(searchTerm, filterValue);
        });
    });

    // Show modal
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Hide modal
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Show loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    // Hide loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Show success notification
    function showSuccessNotification(message) {
        document.getElementById('successMessage').textContent = message;
        const notification = document.getElementById('successNotification');
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Show transaction details
    function showTransactionDetails(transactionId) {
        // In a real application, you would fetch transaction details from the server
        // For this example, we'll use the data from the table row

        let date, reason, account, amount, balance, isCredit;

        // Find the transaction in the table
        const row = $(`tr[data-transaction-id="${transactionId}"]`);
        if (row.length) {
            date = row.find('td:nth-child(1)').text();
            reason = row.find('td:nth-child(3)').text();
            account = row.find('td:nth-child(4)').text();
            amount = row.find('td:nth-child(5)').text();
            balance = row.find('td:nth-child(6)').text();
            isCredit = row.find('td:nth-child(5)').hasClass('text-green-600');
        } else {
            // Try mobile view
            const mobileRow = $(`div[data-transaction-id="${transactionId}"]`);
            if (mobileRow.length) {
                date = mobileRow.find('.text-xs.text-gray-text').first().text();
                reason = mobileRow.find('.grid .text-gray-700').first().text();
                account = mobileRow.find('.grid .text-gray-700').last().text();
                amount = mobileRow.find('.font-medium').first().text();
                balance = mobileRow.find('.text-xs.text-gray-text').last().text().replace('Balance: ', '');
                isCredit = mobileRow.find('.font-medium').first().hasClass('text-green-600');
            }
        }

        const amountClass = isCredit ? 'text-green-600' : 'text-red-600';

        const detailsHtml = `
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Transaction ID</p>
                        <p class="font-medium">TRX-${transactionId}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="font-medium">${date}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Reason</p>
                        <p class="font-medium">${reason}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Account</p>
                        <p class="font-medium">${account}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Amount</p>
                        <p class="font-medium ${amountClass}">${amount}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Balance</p>
                        <p class="font-medium">${balance}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-medium text-secondary mb-2">Transaction Notes</h4>
                <p class="text-sm text-gray-600">
                    This transaction was processed on ${date} via ${account}.
                    ${isCredit ? 'Funds were added to your account.' : 'Funds were deducted from your account.'}
                </p>
            </div>
        `;

        $('#transactionDetails').html(detailsHtml);
        showModal('transactionDetailsModal');
    }

    // Update pagination
    function updatePagination() {
        const itemsPerPage = 5;
        const totalItems = $('#transactions-table-body tr').length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Update pagination info
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        $('#showing-start').text(start);
        $('#showing-end').text(end);
        $('#total-transactions').text(totalItems);

        // Update pagination buttons
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);

        // Update pagination numbers
        let paginationHtml = '';

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${i}</button>`;
                } else {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${i})">${i}</button>`;
                }
            }
        } else {
            // First page
            if (currentPage === 1) {
                paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">1</button>`;
            } else {
                paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(1)">1</button>`;
            }

            // Ellipsis or pages
            if (currentPage > 3) {
                paginationHtml += `<span class="px-2">...</span>`;
            }

            // Pages around current
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                if (i === currentPage) {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${i}</button>`;
                } else {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${i})">${i}</button>`;
                }
            }

            // Ellipsis or pages
            if (currentPage < totalPages - 2) {
                paginationHtml += `<span class="px-2">...</span>`;
            }

            // Last page
            if (currentPage === totalPages) {
                paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${totalPages}</button>`;
            } else {
                paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }
        }

        $('#pagination-numbers').html(paginationHtml);

        // Show/hide rows based on current page
        $('#transactions-table-body tr').hide();
        $('#transactions-table-body tr').slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage).show();

        // Mobile view
        $('.md\\:hidden > div[data-transaction-id]').hide();
        $('.md\\:hidden > div[data-transaction-id]').slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage).show();
    }

    // Go to specific page
    function goToPage(page) {
        currentPage = page;
        updatePagination();
    }

    // Filter transactions
    function filterTransactions(searchTerm, filterType = 'all') {
        // Reset current page
        currentPage = 1;

        // Show all rows first
        $('#transactions-table-body tr').show();
        $('.md\\:hidden > div[data-transaction-id]').show();

        if (searchTerm || filterType !== 'all') {
            // Hide rows that don't match search term
            $('#transactions-table-body tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                const isCredit = $(this).find('td:nth-child(5)').hasClass('text-green-600');

                let showRow = true;

                // Apply search filter
                if (searchTerm && !rowText.includes(searchTerm)) {
                    showRow = false;
                }

                // Apply type filter
                if (filterType === 'credit' && !isCredit) {
                    showRow = false;
                } else if (filterType === 'debit' && isCredit) {
                    showRow = false;
                }

                $(this).toggle(showRow);
            });

            // Mobile view filtering
            $('.md\\:hidden > div[data-transaction-id]').each(function() {
                const rowText = $(this).text().toLowerCase();
                const isCredit = $(this).find('.font-medium').first().hasClass('text-green-600');

                let showRow = true;

                // Apply search filter
                if (searchTerm && !rowText.includes(searchTerm)) {
                    showRow = false;
                }

                // Apply type filter
                if (filterType === 'credit' && !isCredit) {
                    showRow = false;
                } else if (filterType === 'debit' && isCredit) {
                    showRow = false;
                }

                $(this).toggle(showRow);
            });
        }

        // Update pagination
        const totalItems = $('#transactions-table-body tr:visible').length;
        $('#total-transactions').text(totalItems);

        updatePagination();
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>