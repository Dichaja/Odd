<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';

// Fetch active cash accounts
$cashAccounts = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, type, provider, account_number FROM zzimba_cash_accounts WHERE status = 'active' ORDER BY type, name");
    $stmt->execute();
    $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching cash accounts: " . $e->getMessage());
}

ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50">
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

                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="add-money-btn" onclick="showPaymentMethodModal()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-plus"></i><span>Topup</span>
                    </button>
                    <button id="send-credit-btn" onclick="showSendCreditModal()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25">
                        <i class="fas fa-paper-plane"></i><span>Send Credit</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-primary/5 to-primary/10 p-6 border-b border-gray-100">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Statement</h3>
                        <p class="text-sm text-gray-text mt-1">Recent transactions and account activity</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <button id="viewColumnsBtn" onclick="toggleColumnSelector()"
                                class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
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
                                            data-column="entryid" checked>
                                        <span class="text-sm text-gray-700">Entry ID</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="description" checked>
                                        <span class="text-sm text-gray-700">Description</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="debit" checked>
                                        <span class="text-sm text-gray-700">Debit</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="credit" checked>
                                        <span class="text-sm text-gray-700">Credit</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="balance" checked>
                                        <span class="text-sm text-gray-700">Balance</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                            data-column="related" checked>
                                        <span class="text-sm text-gray-700">Related Entries</span>
                                    </label>
                                </div>
                            </div>
                        </div>

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

            <div id="transactionsLoading" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>

            <div id="transactionsTable" class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactionsTableElement">
                    <thead class="bg-user-accent border-b border-gray-200">
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
                    <tbody id="transactionsTableBody" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>

            <div id="transactionsMobile" class="lg:hidden p-4 space-y-4 hidden overflow-auto flex-1">
            </div>

            <div id="transactionsEmpty" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-receipt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                <p class="text-gray-500 mb-6">Start by adding money to your wallet</p>
                <button onclick="showPaymentMethodModal()"
                    class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                    Add Money
                </button>
            </div>

            <div id="transactionsPagination" class="hidden px-6 py-4 border-t border-gray-100 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600" id="paginationInfo">
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

    <!-- Payment Method Selection Modal -->
    <div id="paymentMethodModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-primary text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Choose Payment Method</h3>
                            <p class="text-sm text-gray-500">Select how you want to add money to your wallet</p>
                        </div>
                    </div>
                    <button onclick="hidePaymentMethodModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="paymentMethodCards">
                    <?php foreach ($cashAccounts as $account): ?>
                        <div class="payment-method-card border-2 border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all cursor-pointer"
                            onclick="selectPaymentMethod('<?= htmlspecialchars($account['id']) ?>', '<?= htmlspecialchars($account['type']) ?>', '<?= htmlspecialchars($account['name']) ?>', '<?= htmlspecialchars($account['account_number']) ?>', '<?= htmlspecialchars($account['provider']) ?>')">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <?php
                                    $iconClass = 'fas fa-university';
                                    switch ($account['type']) {
                                        case 'mobile_money':
                                            $iconClass = 'fas fa-mobile-alt';
                                            break;
                                        case 'bank':
                                            $iconClass = 'fas fa-university';
                                            break;
                                        case 'gateway':
                                            $iconClass = 'fas fa-credit-card';
                                            break;
                                    }
                                    ?>
                                    <i class="<?= $iconClass ?> text-gray-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($account['name']) ?></h4>
                                    <?php if ($account['type'] !== 'gateway'): ?>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($account['account_number']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-xs text-gray-400 capitalize">
                                        <?= str_replace('_', ' ', $account['type']) ?>
                                    </p>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-end mt-6">
                    <button onclick="hidePaymentMethodModal()"
                        class="px-6 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Money Form Modal -->
    <div id="mobileMoneyModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-primary text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Mobile Money Payment</h3>
                            <p class="text-sm text-gray-500" id="mobileMoneyAccountName"></p>
                        </div>
                    </div>
                    <button onclick="hideMobileMoneyModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="mobileMoneyForm" class="space-y-4">
                    <div>
                        <label for="mmPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone Number Used
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-3 text-gray-500 font-medium">+256</div>
                            <input type="tel" id="mmPhoneNumber" name="mmPhoneNumber"
                                class="w-full pl-16 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="771234567" maxlength="9" pattern="[0-9]{9}" required>
                        </div>
                    </div>

                    <div>
                        <label for="mmAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Amount Sent (UGX)
                        </label>
                        <input type="number" id="mmAmount" name="mmAmount" min="500" step="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter amount sent" required>
                    </div>

                    <div>
                        <label for="mmTransactionId" class="block text-sm font-semibold text-gray-700 mb-2">
                            Transaction ID
                        </label>
                        <input type="text" id="mmTransactionId" name="mmTransactionId"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter transaction ID" required>
                    </div>

                    <div>
                        <label for="mmDateTime" class="block text-sm font-semibold text-gray-700 mb-2">
                            Date & Time Sent
                        </label>
                        <input type="datetime-local" id="mmDateTime" name="mmDateTime"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                    </div>
                </form>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideMobileMoneyModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="button" onclick="submitMobileMoneyPayment()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                        Submit Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bank Transfer Form Modal -->
    <div id="bankTransferModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-university text-primary text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Bank Transfer</h3>
                            <p class="text-sm text-gray-500" id="bankAccountName"></p>
                        </div>
                    </div>
                    <button onclick="hideBankTransferModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="bankTransferForm" class="space-y-4">
                    <div>
                        <label for="btAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Amount Deposited (UGX)
                        </label>
                        <input type="number" id="btAmount" name="btAmount" min="500" step="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter amount deposited" required>
                    </div>

                    <div>
                        <label for="btReference" class="block text-sm font-semibold text-gray-700 mb-2">
                            Bank Reference/Receipt Number
                        </label>
                        <input type="text" id="btReference" name="btReference"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter reference number" required>
                    </div>

                    <div>
                        <label for="btDepositorName" class="block text-sm font-semibold text-gray-700 mb-2">
                            Depositor Name
                        </label>
                        <input type="text" id="btDepositorName" name="btDepositorName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Name used for deposit" required>
                    </div>

                    <div>
                        <label for="btDateTime" class="block text-sm font-semibold text-gray-700 mb-2">
                            Date & Time of Deposit
                        </label>
                        <input type="datetime-local" id="btDateTime" name="btDateTime"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                    </div>
                </form>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideBankTransferModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="button" onclick="submitBankTransferPayment()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                        Submit Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gateway Payment Modal (Original Implementation) -->
    <div id="gatewayPaymentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-primary text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Gateway Payment</h3>
                            <p class="text-sm text-gray-500" id="gatewayAccountName"></p>
                        </div>
                    </div>
                    <button onclick="hideGatewayPaymentModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="gatewayPaymentForm" class="space-y-4">
                    <div>
                        <label for="gwPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute left-3 top-3 text-gray-500 font-medium">+256</div>
                            <input type="tel" id="gwPhoneNumber" name="gwPhoneNumber"
                                class="w-full pl-16 pr-12 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="771234567" maxlength="9" pattern="[0-9]{9}" required>
                            <div id="gwPhoneValidationSpinner" class="absolute right-3 top-3 hidden">
                                <i class="fas fa-spinner fa-spin text-primary"></i>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">Enter exactly 9 digits (without the leading 0)</div>
                        <div id="gwCustomerName" class="mt-2 text-sm text-green-600 hidden"></div>
                        <div id="gwPhoneError" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <label for="gwAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Amount (UGX)
                        </label>
                        <input type="number" id="gwAmount" name="gwAmount" min="500" step="100"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter amount (minimum 500)" required>
                        <div id="gwAmountError" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>

                    <div>
                        <label for="gwDescription" class="block text-sm font-semibold text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <input type="text" id="gwDescription" name="gwDescription"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Payment description">
                    </div>

                    <div id="gwPaymentStatus" class="hidden p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div id="gwStatusIcon" class="w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <div>
                                <div id="gwStatusTitle" class="font-medium text-gray-900">Processing Payment</div>
                                <div id="gwStatusMessage" class="text-sm text-gray-600">Please wait...</div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="hideGatewayPaymentModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button type="button" id="gwSubmitPaymentBtn" onclick="submitGatewayPayment()" disabled
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Add Money
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Result Modal -->
    <div id="transactionResultModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content">
            <div class="p-6 text-center">
                <div id="resultIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                </div>

                <h3 id="resultTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                <p id="resultMessage" class="text-gray-600 mb-6 overflow-hidden"></p>

                <div id="resultDetails" class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                </div>

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
    #balanceText {
        white-space: nowrap !important;
        overflow: hidden;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const apiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const ownerName = <?= json_encode(
            trim(
                ($_SESSION['user']['first_name'] ?? '')
                . ' '
                . ($_SESSION['user']['last_name'] ?? '')
            )
        ) ?>;

        let validatedMsisdn = null;
        let customerName = null;
        let currentPaymentReference = null;
        let statusCheckInterval = null;
        let validationTimeout = null;
        let transactions = [];
        let selectedAccount = null;

        // Switch between table and mobile view based on screen width
        function displayTransactionsView() {
            const tableWrapper = document.getElementById('transactionsTable');
            const mobileWrapper = document.getElementById('transactionsMobile');
            if (window.innerWidth >= 1024) {
                tableWrapper.classList.remove('hidden');
                mobileWrapper.classList.add('hidden');
            } else {
                tableWrapper.classList.add('hidden');
                mobileWrapper.classList.remove('hidden');
            }
        }

        loadWalletData();
        loadTransactions();
        displayTransactionsView();

        window.addEventListener('resize', function () {
            adjustTableFontSize();
            displayTransactionsView();
        });

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
                        document.getElementById('walletId').textContent = w.wallet_number;
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

                        document.getElementById('walletLoading').classList.add('hidden');
                        document.getElementById('balanceLoading').classList.add('hidden');
                        document.getElementById('walletInfo').classList.remove('hidden');
                        document.getElementById('balanceInfo').classList.remove('hidden');

                        // Trigger balance text sizing after showing
                        setTimeout(adjustBalanceTextSize, 100);
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
                        transactions = transformStatementData(data.statement);
                        if (transactions.length > 0) {
                            renderTransactions(transactions);
                            updatePaginationInfo(transactions.length);
                            adjustTableFontSize();
                            displayTransactionsView();
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
                            entry_id: e.entry_id,
                            entry_type: e.entry_type,
                            amount: parseFloat(e.amount),
                            balance_after: parseFloat(e.balance_after),
                            entry_note: e.entry_note,
                            entry_date: e.created_at,
                            related_entries: e.related_entries || [],
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
                        entry_id: null,
                        entry_type: null,
                        amount: 0,
                        balance_after: 0,
                        entry_note: null,
                        entry_date: null,
                        related_entries: [],
                        is_first_in_group: true,
                        group_size: 1
                    });
                }
            });
            return transformed.sort((a, b) => new Date(b.transaction_date) - new Date(a.transaction_date));
        }

        function renderTransactions(entries) {
            const tbody = document.getElementById('transactionsTableBody');
            const mobile = document.getElementById('transactionsMobile');
            tbody.innerHTML = '';
            mobile.innerHTML = '';

            entries.forEach((entry, idx) => {
                const tr = document.createElement('tr');
                tr.className = `${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors`;
                if (entry.is_first_in_group && entry.group_size > 1) {
                    tr.classList.add('border-l-4', 'border-blue-400');
                }
                const dt = new Date(entry.transaction_date);
                const dateStr = dt.toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
                const debit = entry.entry_type === 'DEBIT' ? entry.amount : 0;
                const credit = entry.entry_type === 'CREDIT' ? entry.amount : 0;

                // multi-line description
                let raw = entry.entry_note || getTransactionDescription(entry);
                if (entry.status === 'FAILED') {
                    raw = `${entry.transaction_type} (TXN: ${entry.transaction_id}, UGX ${formatCurrency(entry.amount_total)}) - FAILED`;
                    if (entry.transaction_note && entry.transaction_note !== 'Request payment completed successfully.') {
                        raw += ` - ${entry.transaction_note}`;
                    }
                } else {
                    raw += ` (TXN: ${entry.transaction_id}, UGX ${formatCurrency(entry.amount_total)})`;
                }
                const parts = raw.split(',').map(s => s.trim());
                const descHtml = parts.map(line => `<div class="font-medium text-gray-900">${line}</div>`).join('') +
                    (entry.payment_method ? `<div class="text-xs text-gray-500 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>` : '');

                tr.innerHTML = `
                    <td data-column="datetime" class="px-4 py-3 text-sm whitespace-nowrap">
                        <div class="font-medium text-gray-900 whitespace-nowrap">${dateStr}</div>
                        <div class="text-xs text-gray-500 whitespace-nowrap">${timeStr}</div>
                    </td>
                    <td data-column="entryid" class="px-4 py-3 text-sm">
                        <div class="font-mono text-gray-700 truncate max-w-[10ch]" title="${entry.entry_id || ''}">
                            ${entry.entry_id || ''}
                        </div>
                    </td>
                    <td data-column="description" class="px-4 py-3 text-sm max-w-[20ch]">
                        <div class="overflow-hidden whitespace-normal" title="${raw}">
                            ${descHtml}
                        </div>
                    </td>
                    <td data-column="debit" class="px-4 py-3 text-sm text-right">
                        ${debit > 0 ? `<span class="font-semibold text-red-600">-${formatCurrency(debit)}</span>` : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td data-column="credit" class="px-4 py-3 text-sm text-right">
                        ${credit > 0 ? `<span class="font-semibold text-green-600">+${formatCurrency(credit)}</span>` : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td data-column="balance" class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                        ${entry.balance_after > 0 ? formatCurrency(entry.balance_after) : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td data-column="related" class="px-4 py-3 text-sm">
                        ${renderRelatedEntries(entry.related_entries)}
                    </td>
                `;
                tbody.appendChild(tr);

                // Mobile card remains unchanged
                const card = document.createElement('div');
                card.className = `bg-white rounded-lg p-4 border border-gray-200 ${entry.is_first_in_group && entry.group_size > 1 ? 'border-l-4 border-l-blue-400' : ''}`;
                card.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 text-sm mb-1">${raw.replace(/, /g, '\n')}</div>
                            <div class="text-xs text-gray-500">${dateStr} • ${timeStr}</div>
                            ${entry.payment_method ? `<div class="text-xs text-gray-500 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>` : ''}
                        </div>
                        <div class="text-right ml-3">
                            ${debit > 0 ? `<div class="font-semibold text-red-600 text-sm">-${formatCurrency(debit)}</div>` : ''}
                            ${credit > 0 ? `<div class="font-semibold text-green-600 text-sm">+${formatCurrency(credit)}</div>` : ''}
                            <div class="text-xs text-gray-500 mt-1">Balance: ${entry.balance_after > 0 ? formatCurrency(entry.balance_after) : '-'}</div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-2"><span class="font-mono">${entry.entry_id || 'No Entry ID'}</span></div>
                    ${entry.related_entries.length > 0 ? `
                        <div class="mt-3">
                            <div class="text-xs font-medium text-gray-700 mb-2">Related Entries:</div>
                            ${renderRelatedEntriesMobile(entry.related_entries)}
                        </div>
                    ` : ''}
                `;
                mobile.appendChild(card);
            });

            // Apply column visibility after rendering
            applyColumnVisibility();
        }

        function renderRelatedEntries(rel) {
            if (!rel?.length) return '<span class="text-gray-400 text-xs">None</span>';
            return rel.map(r => {
                const type = r.owner_type
                    ? `${r.owner_type.charAt(0).toUpperCase()}${r.owner_type.slice(1).toLowerCase()} Wallet`
                    : 'Cash Account';
                const amtCls = r.entry_type === 'CREDIT' ? 'text-green-600' : 'text-red-600';
                const sign = r.entry_type === 'CREDIT' ? '+' : '-';
                return `
                    <div class="bg-gray-50 rounded p-2 mb-1 text-xs">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-arrow-right text-gray-400"></i>
                            <span class="font-medium">${type}:</span>
                            <span class="text-gray-600">${r.account_or_wallet_name || 'Unknown'}</span>
                        </div>
                        <div class="mt-1">
                            <span class="font-semibold ${amtCls}">${sign}${formatCurrency(r.amount)}</span>
                            <span class="text-gray-500 ml-2">Balance: ${formatCurrency(r.balance_after)}</span>
                        </div>
                        ${r.entry_note ? `<div class="text-gray-600 mt-1">${r.entry_note}</div>` : ''}
                    </div>`;
            }).join('');
        }

        function renderRelatedEntriesMobile(rel) {
            if (!rel?.length) return '<span class="text-gray-400">None</span>';
            return rel.map(r => {
                const type = r.owner_type
                    ? `${r.owner_type.charAt(0).toUpperCase()}${r.owner_type.slice(1).toLowerCase()} Wallet`
                    : 'Cash Account';
                const amtCls = r.entry_type === 'CREDIT' ? 'text-green-600' : 'text-red-600';
                const sign = r.entry_type === 'CREDIT' ? '+' : '-';
                return `
                    <div class="bg-gray-50 rounded p-2 mb-2 text-xs">
                        <div class="font-medium text-gray-700">${type}: <span class="font-normal">${r.account_or_wallet_name || 'Unknown'}</span></div>
                        <div class="mt-1">
                            <span class="font-semibold ${amtCls}">${sign}${formatCurrency(r.amount)}</span>
                            <span class="text-gray-500 ml-2">Balance: ${formatCurrency(r.balance_after)}</span>
                        </div>
                        ${r.entry_note ? `<div class="text-gray-600 mt-1">${r.entry_note}</div>` : ''}
                    </div>`;
            }).join('');
        }

        function getTransactionDescription(e) {
            const types = { 'TOPUP': 'Wallet Top-up', 'TRANSFER': 'Transfer', 'PAYMENT': 'Payment', 'WITHDRAWAL': 'Withdrawal' };
            const methods = { 'MOBILE_MONEY_GATEWAY': 'Mobile Money', 'BANK_TRANSFER': 'Bank Transfer', 'CARD_PAYMENT': 'Card Payment' };
            let d = types[e.transaction_type] || e.transaction_type;
            if (e.payment_method) d += ` via ${methods[e.payment_method] || e.payment_method}`;
            return d;
        }

        function showTransactionsError(msg) {
            document.getElementById('transactionsLoading').innerHTML = `<div class="text-red-600 text-center p-6">${msg}</div>`;
        }

        function updatePaginationInfo(cnt) {
            document.getElementById('paginationInfo').textContent = `Showing 1-${cnt} of ${cnt} transactions`;
        }

        function formatCurrency(a) {
            return new Intl.NumberFormat('en-UG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(a);
        }

        function adjustTableFontSize() {
            const tbl = document.getElementById('transactionsTableElement');
            if (!tbl) return;
            const container = tbl.parentElement;
            let fs = 14;
            tbl.style.fontSize = fs + 'px';
            while ((tbl.scrollWidth > container.clientWidth || hasOverflowingTransactionDetails()) && fs > 8) {
                fs -= .5; tbl.style.fontSize = fs + 'px';
            }
            if (fs < 10) tbl.style.fontSize = '10px';
        }

        function hasOverflowingTransactionDetails() {
            for (const el of document.querySelectorAll('.transaction-details')) {
                if (el.scrollHeight > el.clientHeight) return true;
            }
            return false;
        }

        // Modal & Payment methods

        window.showPaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.remove('hidden');
        };
        window.hidePaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.add('hidden');
        };

        window.selectPaymentMethod = function (accountId, type, name, accountNumber, provider) {
            selectedAccount = { id: accountId, type, name, accountNumber, provider };
            hidePaymentMethodModal();
            switch (type) {
                case 'mobile_money': showMobileMoneyModal(); break;
                case 'bank': showBankTransferModal(); break;
                case 'gateway': showGatewayPaymentModal(); break;
                default: alert('This payment method is coming soon!');
            }
        };

        window.showMobileMoneyModal = function () {
            document.getElementById('mobileMoneyAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
            document.getElementById('mmDateTime').value = new Date().toISOString().slice(0, 16);
            document.getElementById('mobileMoneyModal').classList.remove('hidden');
        };
        window.hideMobileMoneyModal = function () {
            document.getElementById('mobileMoneyModal').classList.add('hidden');
            document.getElementById('mobileMoneyForm').reset();
        };

        window.showBankTransferModal = function () {
            document.getElementById('bankAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
            document.getElementById('btDateTime').value = new Date().toISOString().slice(0, 16);
            document.getElementById('bankTransferModal').classList.remove('hidden');
        };
        window.hideBankTransferModal = function () {
            document.getElementById('bankTransferModal').classList.add('hidden');
            document.getElementById('bankTransferForm').reset();
        };

        window.showGatewayPaymentModal = function () {
            document.getElementById('gatewayAccountName').textContent = selectedAccount.name;
            document.getElementById('gatewayPaymentModal').classList.remove('hidden');
            resetGatewayForm();
        };
        window.hideGatewayPaymentModal = function () {
            document.getElementById('gatewayPaymentModal').classList.add('hidden');
            resetGatewayForm();
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
                statusCheckInterval = null;
            }
        };

        function resetGatewayForm() {
            document.getElementById('gatewayPaymentForm').reset();
            ['gwCustomerName', 'gwPhoneError', 'gwAmountError', 'gwPaymentStatus', 'gwPhoneValidationSpinner']
                .forEach(id => document.getElementById(id).classList.add('hidden'));
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
            validatedMsisdn = null; customerName = null; currentPaymentReference = null;
            if (validationTimeout) { clearTimeout(validationTimeout); validationTimeout = null; }
        }

        window.submitMobileMoneyPayment = function () {
            const fd = new FormData(document.getElementById('mobileMoneyForm'));
            console.log('Mobile Money Payment:', Object.fromEntries(fd));
            alert('Mobile Money payment submitted! (Dummy)');
            hideMobileMoneyModal();
        };

        window.submitBankTransferPayment = function () {
            const fd = new FormData(document.getElementById('bankTransferForm'));
            console.log('Bank Transfer Payment:', Object.fromEntries(fd));
            alert('Bank transfer payment submitted! (Dummy)');
            hideBankTransferModal();
        };

        async function validateGatewayPhoneNumber(phone = null) {
            const inp = document.getElementById('gwPhoneNumber');
            const spinner = document.getElementById('gwPhoneValidationSpinner');
            const nameDiv = document.getElementById('gwCustomerName');
            const errDiv = document.getElementById('gwPhoneError');
            const val = phone || inp.value.trim();
            if (!val) return showGatewayPhoneError('Please enter a phone number');
            if (!/^\d{9}$/.test(val)) return showGatewayPhoneError('Please enter exactly 9 digits');
            const formatted = '+256' + val;
            spinner.classList.remove('hidden');
            errDiv.classList.add('hidden');
            nameDiv.classList.add('hidden');
            try {
                const resp = await fetch(`${apiUrl}?action=validateMsisdn`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ msisdn: formatted })
                });
                const data = await resp.json();
                if (data.success) {
                    validatedMsisdn = formatted;
                    customerName = data.customer_name;
                    nameDiv.textContent = `✓ ${data.customer_name}`;
                    nameDiv.classList.remove('hidden');
                    checkGatewayFormValidity();
                } else {
                    showGatewayPhoneError(data.message || 'Phone number validation failed');
                }
            } catch (_) {
                showGatewayPhoneError('Network error. Please try again.');
            } finally {
                spinner.classList.add('hidden');
            }
        }

        function showGatewayPhoneError(msg) {
            const err = document.getElementById('gwPhoneError');
            err.textContent = msg;
            err.classList.remove('hidden');
            document.getElementById('gwCustomerName').classList.add('hidden');
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
            validatedMsisdn = null; customerName = null;
        }

        function showGatewayAmountError(msg) {
            const err = document.getElementById('gwAmountError');
            err.textContent = msg;
            err.classList.remove('hidden');
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
        }

        function hideGatewayAmountError() {
            document.getElementById('gwAmountError').classList.add('hidden');
            checkGatewayFormValidity();
        }

        function checkGatewayFormValidity() {
            const amt = parseFloat(document.getElementById('gwAmount').value);
            const btn = document.getElementById('gwSubmitPaymentBtn');
            btn.disabled = !(validatedMsisdn && amt >= 500);
        }

        window.submitGatewayPayment = async function () {
            if (!validatedMsisdn) return showGatewayPhoneError('Please validate the phone number first');
            const amt = parseFloat(document.getElementById('gwAmount').value);
            const desc = document.getElementById('gwDescription').value.trim() || 'Zzimba wallet top-up';
            if (!amt || amt < 500) return showGatewayAmountError('Please enter a valid amount (minimum 500 UGX)');
            const btn = document.getElementById('gwSubmitPaymentBtn');
            btn.disabled = true; btn.textContent = 'Processing...';
            showGatewayPaymentStatus('processing', 'Processing Payment', 'Initiating payment request...');
            try {
                const resp = await fetch(`${apiUrl}?action=makePayment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ msisdn: validatedMsisdn, amount: amt, description: desc })
                });
                const data = await resp.json();
                if (data.success) {
                    currentPaymentReference = data.internal_reference;
                    showGatewayPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone...');
                    startGatewayStatusChecking();
                } else {
                    showGatewayPaymentStatus('error', 'Payment Failed', data.message || 'Failed to initiate payment');
                    btn.disabled = false; btn.textContent = 'Add Money';
                }
            } catch (_) {
                showGatewayPaymentStatus('error', 'Network Error', 'Please check your connection and try again.');
                btn.disabled = false; btn.textContent = 'Add Money';
            }
        };

        function showGatewayPaymentStatus(type, title, message) {
            const div = document.getElementById('gwPaymentStatus');
            const icon = document.getElementById('gwStatusIcon');
            const t = document.getElementById('gwStatusTitle');
            const m = document.getElementById('gwStatusMessage');
            t.textContent = title; m.textContent = message;
            div.className = 'p-4 rounded-xl';
            icon.className = 'w-8 h-8 rounded-full flex items-center justify-center';
            switch (type) {
                case 'processing':
                    div.classList.add('bg-blue-50', 'border', 'border-blue-200');
                    icon.classList.add('bg-blue-100');
                    icon.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600"></i>';
                    break;
                case 'pending':
                    div.classList.add('bg-yellow-50', 'border', 'border-yellow-200');
                    icon.classList.add('bg-yellow-100');
                    icon.innerHTML = '<i class="fas fa-clock text-yellow-600"></i>';
                    break;
                case 'success':
                    div.classList.add('bg-green-50', 'border', 'border-green-200');
                    icon.classList.add('bg-green-100');
                    icon.innerHTML = '<i class="fas fa-check text-green-600"></i>';
                    break;
                case 'error':
                    div.classList.add('bg-red-50', 'border', 'border-red-200');
                    icon.classList.add('bg-red-100');
                    icon.innerHTML = '<i class="fas fa-times text-red-600"></i>';
                    break;
            }
            div.classList.remove('hidden');
        }

        function startGatewayStatusChecking() {
            if (!currentPaymentReference) return;
            statusCheckInterval = setInterval(async () => {
                try {
                    const resp = await fetch(`${apiUrl}?action=checkStatus`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ internal_reference: currentPaymentReference })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        if (data.status === 'success') {
                            clearInterval(statusCheckInterval);
                            statusCheckInterval = null;
                            hideGatewayPaymentModal();
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
                            setTimeout(() => { loadWalletData(); loadTransactions(); }, 1000);
                        } else if (data.status === 'failed') {
                            clearInterval(statusCheckInterval);
                            statusCheckInterval = null;
                            hideGatewayPaymentModal();
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
                            setTimeout(() => {
                                const btn = document.getElementById('gwSubmitPaymentBtn');
                                btn.disabled = false;
                                btn.textContent = 'Add Money';
                            }, 1000);
                        }
                    }
                } catch (e) {
                    console.error('Status check error:', e);
                }
            }, 3000);
        }

        function showTransactionResultModal(type, data) {
            const modal = document.getElementById('transactionResultModal');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const det = document.getElementById('resultDetails');

            title.textContent = data.title;
            msg.textContent = data.message;

            const cont = modal.querySelector('.modal-content');
            cont.className = 'bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content';
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';

            if (type === 'success') {
                cont.classList.add('border-t-4', 'border-green-500');
                icon.classList.add('bg-green-100');
                icon.innerHTML = '<i class="fas fa-check text-green-600 text-2xl"></i>';
                det.innerHTML = `
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Amount:</span><span class="font-semibold">${data.currency} ${formatCurrency(data.amount)}</span></div>
                        ${data.charge ? `<div class="flex justify-between"><span class="text-gray-600">Transaction Fee:</span><span class="font-semibold">${data.currency} ${formatCurrency(data.charge)}</span></div>` : ''}
                        <div class="flex justify-between"><span class="text-gray-600">Provider:</span><span class="font-semibold">${data.provider?.replace('_', ' ') || 'N/A'}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Transaction ID:</span><span class="font-mono text-xs">${data.transactionId || 'N/A'}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Reference:</span><span class="font-mono text-xs">${data.reference || 'N/A'}</span></div>
                        ${data.completedAt && data.completedAt !== 'N/A' ? `<div class="flex justify-between"><span class="text-gray-600">Completed:</span><span class="text-xs">${new Date(data.completedAt).toLocaleString()}</span></div>` : ''}
                    </div>
                `;
            } else {
                cont.classList.add('border-t-4', 'border-red-500');
                icon.classList.add('bg-red-100');
                icon.innerHTML = '<i class="fas fa-times text-red-600 text-2xl"></i>';
                det.innerHTML = `
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Amount:</span><span class="font-semibold">${data.currency} ${formatCurrency(data.amount)}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Provider:</span><span class="font-semibold">${data.provider?.replace('_', ' ') || 'N/A'}</span></div>
                        <div class="flex justify-between"><span class="text-gray-600">Reference:</span><span class="font-mono text-xs">${data.reference || 'N/A'}</span></div>
                        ${data.reason ? `<div class="mt-4 p-3 bg-red-50 rounded-lg"><p class="text-red-800 text-xs overflow-hidden"><strong>Reason:</strong> ${data.reason}</p></div>` : ''}
                    </div>
                `;
            }
            modal.classList.remove('hidden');
        }

        function hideTransactionResultModal() {
            document.getElementById('transactionResultModal').classList.add('hidden');
        }

        // Gateway event listeners
        document.getElementById('gwPhoneNumber').addEventListener('blur', e => {
            const phone = e.target.value.trim();
            if (phone && phone !== validatedMsisdn) {
                if (validationTimeout) clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => validateGatewayPhoneNumber(phone), 500);
            }
        });
        document.getElementById('gwPhoneNumber').addEventListener('input', e => {
            const val = e.target.value.trim();
            if (validatedMsisdn && val !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null; customerName = null;
            }
        });
        document.getElementById('gwPhoneNumber').addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g, '').slice(0, 9);
            e.target.value = v;
            if (validatedMsisdn && ('+256' + v) !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null; customerName = null;
            }
        });
        document.getElementById('gwAmount').addEventListener('input', e => {
            const a = parseFloat(e.target.value);
            if (a && a < 500) showGatewayAmountError('Minimum amount is 500 UGX');
            else hideGatewayAmountError();
        });
        document.getElementById('gwPhoneNumber').addEventListener('keypress', e => {
            if (e.key === 'Enter') { e.preventDefault(); validateGatewayPhoneNumber(); }
        });

        // Column visibility management
        const STORAGE_KEY = 'zzimba_credit_table_columns';
        let visibleColumns = ['datetime', 'entryid', 'description', 'debit', 'credit', 'balance', 'related']; // All columns visible by default

        function loadColumnSettings() {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                try {
                    visibleColumns = JSON.parse(saved);
                } catch (e) {
                    console.error('Error loading column settings:', e);
                }
            }
            updateColumnCheckboxes();
            applyColumnVisibility();
        }

        function saveColumnSettings() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visibleColumns));
        }

        function updateColumnCheckboxes() {
            const checkboxes = document.querySelectorAll('.column-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = visibleColumns.includes(checkbox.dataset.column);
            });
        }

        function applyColumnVisibility() {
            const allColumns = ['datetime', 'entryid', 'description', 'debit', 'credit', 'balance', 'related'];

            allColumns.forEach(column => {
                const isVisible = visibleColumns.includes(column);
                const headers = document.querySelectorAll(`th[data-column="${column}"]`);
                const cells = document.querySelectorAll(`td[data-column="${column}"]`);

                headers.forEach(header => {
                    header.style.display = isVisible ? '' : 'none';
                });
                cells.forEach(cell => {
                    cell.style.display = isVisible ? '' : 'none';
                });
            });
        }

        function toggleColumnSelector() {
            const selector = document.getElementById('columnSelector');
            const isHidden = selector.classList.contains('hidden');

            if (isHidden) {
                selector.classList.remove('hidden');
                document.addEventListener('click', handleClickOutside);
            } else {
                selector.classList.add('hidden');
                document.removeEventListener('click', handleClickOutside);
            }
        }

        function handleClickOutside(event) {
            const selector = document.getElementById('columnSelector');
            const button = document.getElementById('viewColumnsBtn');

            if (!selector.contains(event.target) && !button.contains(event.target)) {
                selector.classList.add('hidden');
                document.removeEventListener('click', handleClickOutside);
            }
        }

        // Balance text auto-sizing
        function adjustBalanceTextSize() {
            const balanceInfo = document.getElementById('balanceInfo');
            const balanceText = document.getElementById('balanceText');

            if (!balanceInfo || !balanceText || balanceInfo.classList.contains('hidden')) {
                return;
            }

            const container = balanceInfo.parentElement;
            let fontSize = 48; // Start with lg:text-4xl equivalent (48px)

            // Prevent text wrapping
            balanceText.style.whiteSpace = 'nowrap';
            balanceText.style.fontSize = fontSize + 'px';

            // Check if text overflows container width
            while (balanceText.scrollWidth > container.clientWidth && fontSize > 16) {
                fontSize -= 2;
                balanceText.style.fontSize = fontSize + 'px';
            }

            // Ensure minimum readable size
            if (fontSize < 20) {
                balanceText.style.fontSize = '20px';
            }
        }

        loadColumnSettings();

        // Add event listeners for column checkboxes
        document.querySelectorAll('.column-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const column = this.dataset.column;
                const isChecked = this.checked;

                if (isChecked) {
                    if (!visibleColumns.includes(column)) {
                        visibleColumns.push(column);
                    }
                } else {
                    // Ensure at least 3 columns remain visible
                    if (visibleColumns.length > 3) {
                        visibleColumns = visibleColumns.filter(col => col !== column);
                    } else {
                        // Prevent unchecking if only 3 columns left
                        this.checked = true;
                        alert('At least 3 columns must be visible');
                        return;
                    }
                }

                saveColumnSettings();
                applyColumnVisibility(); // Apply immediately
                adjustTableFontSize(); // Readjust table font size after column changes
            });
        });

        // Add resize observer for balance text
        if (window.ResizeObserver) {
            const balanceObserver = new ResizeObserver(adjustBalanceTextSize);
            const balanceInfo = document.getElementById('balanceInfo');
            if (balanceInfo) {
                balanceObserver.observe(balanceInfo.parentElement);
            }
        }

        // Fallback for browsers without ResizeObserver
        window.addEventListener('resize', adjustBalanceTextSize);

        // Update renderTransactions function to include data attributes
        function renderTransactions(entries) {
            const tbody = document.getElementById('transactionsTableBody');
            const mobile = document.getElementById('transactionsMobile');
            tbody.innerHTML = '';
            mobile.innerHTML = '';

            entries.forEach((entry, idx) => {
                const tr = document.createElement('tr');
                tr.className = `${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors`;
                if (entry.is_first_in_group && entry.group_size > 1) {
                    tr.classList.add('border-l-4', 'border-blue-400');
                }

                const dt = new Date(entry.transaction_date);
                const dateStr = dt.toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' });
                const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                const debit = entry.entry_type === 'DEBIT' ? entry.amount : 0;
                const credit = entry.entry_type === 'CREDIT' ? entry.amount : 0;

                // ←── only use the transaction note (or entry note) and payment method
                const mainDesc = entry.transaction_note || entry.entry_note || '';
                const descHtml = `
            <div class="font-medium text-gray-900">${mainDesc}</div>
            ${entry.payment_method
                        ? `<div class="text-xs text-gray-500 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>`
                        : ''
                    }
        `;

                tr.innerHTML = `
            <td data-column="datetime" class="px-4 py-3 text-sm whitespace-nowrap">
                <div class="font-medium text-gray-900">${dateStr}</div>
                <div class="text-xs text-gray-500">${timeStr}</div>
            </td>
            <td data-column="entryid" class="px-4 py-3 text-sm">
                <div class="font-mono text-gray-700 truncate max-w-[10ch]" title="${entry.entry_id || ''}">
                    ${entry.entry_id || ''}
                </div>
            </td>
            <td data-column="description" class="px-4 py-3 text-sm max-w-[20ch]">
                <div class="overflow-hidden whitespace-normal" title="${mainDesc}">
                    ${descHtml}
                </div>
            </td>
            <td data-column="debit" class="px-4 py-3 text-sm text-right">
                ${debit > 0
                        ? `<span class="font-semibold text-red-600">-${formatCurrency(debit)}</span>`
                        : '<span class="text-gray-400">-</span>'
                    }
            </td>
            <td data-column="credit" class="px-4 py-3 text-sm text-right">
                ${credit > 0
                        ? `<span class="font-semibold text-green-600">+${formatCurrency(credit)}</span>`
                        : '<span class="text-gray-400">-</span>'
                    }
            </td>
            <td data-column="balance" class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                ${entry.balance_after > 0
                        ? formatCurrency(entry.balance_after)
                        : '<span class="text-gray-400">-</span>'
                    }
            </td>
            <td data-column="related" class="px-4 py-3 text-sm">
                ${renderRelatedEntries(entry.related_entries)}
            </td>
        `;
                tbody.appendChild(tr);

                // mobile view
                const card = document.createElement('div');
                card.className = `bg-white rounded-lg p-4 border border-gray-200
                    ${entry.is_first_in_group && entry.group_size > 1 ? 'border-l-4 border-l-blue-400' : ''}`;
                    card.innerHTML = `
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 text-sm mb-1 break-all max-w-full overflow-hidden">${mainDesc}</div>
                                <div class="text-xs text-gray-500">${dateStr} • ${timeStr}</div>
                                ${entry.payment_method
                                            ? `<div class="text-xs text-gray-500 mt-1">${entry.payment_method.replace(/_/g, ' ')}</div>`
                                            : ''
                                        }
                            </div>
                            <div class="text-right ml-3 shrink-0">
                                ${debit > 0 ? `<div class="font-semibold text-red-600 text-sm">-${formatCurrency(debit)}</div>` : ''}
                                ${credit > 0 ? `<div class="font-semibold text-green-600 text-sm">+${formatCurrency(credit)}</div>` : ''}
                                <div class="text-xs text-gray-500 mt-1">
                                    Balance: ${entry.balance_after > 0 ? formatCurrency(entry.balance_after) : '-'}
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 mb-2 break-all">
                            <span class="font-mono break-all max-w-full inline-block overflow-hidden">${entry.entry_id || 'No Entry ID'}</span>
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

            applyColumnVisibility();
        }

        // Make toggleColumnSelector globally available
        window.toggleColumnSelector = toggleColumnSelector;
    });
</script>

<?php
include __DIR__ . '/credit/send-credit.php';

$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>