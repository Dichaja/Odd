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
                    <button
                        class="px-6 py-3 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-200 flex items-center gap-2 font-medium">
                        <i class="fas fa-download"></i>
                        <span>Download Statement</span>
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
                    </tbody>
                </table>
            </div>

            <div id="transactionsMobile" class="lg:hidden p-4 space-y-4 hidden">
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
    .transaction-details {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.4;
        max-height: 2.8em;
    }

    .payment-method-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
        let selectedAccount = null;

        loadWalletData();
        loadTransactions();

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

            if (entry && entry.entry_note) {
                description = entry.entry_note;
            }

            if (transaction.status === 'FAILED') {
                description += ' (Failed)';
                if (transaction.note && transaction.note !== 'Request payment completed successfully.') {
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
                const tr = document.createElement('tr');
                tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;

                const credit = parseFloat(transaction.credit || 0);
                const debit = parseFloat(transaction.debit || 0);
                const balance = parseFloat(transaction.balance || 0);
                const amountTotal = parseFloat(transaction.amount_total || 0);

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
            let fontSize = 14;

            table.style.fontSize = fontSize + 'px';

            while ((table.scrollWidth > container.clientWidth || hasOverflowingTransactionDetails()) && fontSize > 8) {
                fontSize -= 0.5;
                table.style.fontSize = fontSize + 'px';
            }

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
        window.showPaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.remove('hidden');
        };

        window.hidePaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.add('hidden');
        };

        window.selectPaymentMethod = function (accountId, type, name, accountNumber, provider) {
            selectedAccount = { id: accountId, type: type, name: name, accountNumber: accountNumber, provider: provider };
            hidePaymentMethodModal();

            switch (type) {
                case 'mobile_money':
                    showMobileMoneyModal();
                    break;
                case 'bank':
                    showBankTransferModal();
                    break;
                case 'gateway':
                    showGatewayPaymentModal();
                    break;
                default:
                    alert('This payment method is coming soon!');
                    break;
            }
        };

        window.showMobileMoneyModal = function () {
            document.getElementById('mobileMoneyAccountName').textContent = `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
            document.getElementById('mmDateTime').value = new Date().toISOString().slice(0, 16);
            document.getElementById('mobileMoneyModal').classList.remove('hidden');
        };

        window.hideMobileMoneyModal = function () {
            document.getElementById('mobileMoneyModal').classList.add('hidden');
            document.getElementById('mobileMoneyForm').reset();
        };

        window.showBankTransferModal = function () {
            document.getElementById('bankAccountName').textContent = `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
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
            document.getElementById('gwCustomerName').classList.add('hidden');
            document.getElementById('gwPhoneError').classList.add('hidden');
            document.getElementById('gwAmountError').classList.add('hidden');
            document.getElementById('gwPaymentStatus').classList.add('hidden');
            document.getElementById('gwPhoneValidationSpinner').classList.add('hidden');
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
            validatedMsisdn = null;
            customerName = null;
            currentPaymentReference = null;
            if (validationTimeout) {
                clearTimeout(validationTimeout);
                validationTimeout = null;
            }
        }

        window.submitMobileMoneyPayment = function () {
            const formData = new FormData(document.getElementById('mobileMoneyForm'));
            console.log('Mobile Money Payment:', Object.fromEntries(formData));
            alert('Mobile Money payment submitted! (This is a dummy implementation)');
            hideMobileMoneyModal();
        };

        window.submitBankTransferPayment = function () {
            const formData = new FormData(document.getElementById('bankTransferForm'));
            console.log('Bank Transfer Payment:', Object.fromEntries(formData));
            alert('Bank transfer payment submitted! (This is a dummy implementation)');
            hideBankTransferModal();
        };

        async function validateGatewayPhoneNumber(phone = null) {
            const phoneInput = document.getElementById('gwPhoneNumber');
            const spinner = document.getElementById('gwPhoneValidationSpinner');
            const customerNameDiv = document.getElementById('gwCustomerName');
            const phoneErrorDiv = document.getElementById('gwPhoneError');

            const phoneValue = phone || phoneInput.value.trim();
            if (!phoneValue) {
                showGatewayPhoneError('Please enter a phone number');
                return;
            }

            if (!/^\d{9}$/.test(phoneValue)) {
                showGatewayPhoneError('Please enter exactly 9 digits');
                return;
            }

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
                    checkGatewayFormValidity();
                } else {
                    showGatewayPhoneError(data.message || 'Phone number validation failed');
                }
            } catch (error) {
                showGatewayPhoneError('Network error. Please try again.');
            } finally {
                spinner.classList.add('hidden');
            }
        }

        function showGatewayPhoneError(message) {
            const phoneErrorDiv = document.getElementById('gwPhoneError');
            phoneErrorDiv.textContent = message;
            phoneErrorDiv.classList.remove('hidden');
            document.getElementById('gwCustomerName').classList.add('hidden');
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
            validatedMsisdn = null;
            customerName = null;
        }

        function showGatewayAmountError(message) {
            const amountErrorDiv = document.getElementById('gwAmountError');
            amountErrorDiv.textContent = message;
            amountErrorDiv.classList.remove('hidden');
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
        }

        function hideGatewayAmountError() {
            document.getElementById('gwAmountError').classList.add('hidden');
            checkGatewayFormValidity();
        }

        function checkGatewayFormValidity() {
            const amount = parseFloat(document.getElementById('gwAmount').value);
            const submitBtn = document.getElementById('gwSubmitPaymentBtn');

            if (validatedMsisdn && amount >= 500) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        window.submitGatewayPayment = async function () {
            if (!validatedMsisdn) {
                showGatewayPhoneError('Please validate the phone number first');
                return;
            }

            const amount = parseFloat(document.getElementById('gwAmount').value);
            const description = document.getElementById('gwDescription').value.trim() || 'Zzimba wallet top-up';

            if (!amount || amount < 500) {
                showGatewayAmountError('Please enter a valid amount (minimum 500 UGX)');
                return;
            }

            const submitBtn = document.getElementById('gwSubmitPaymentBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            showGatewayPaymentStatus('processing', 'Processing Payment', 'Initiating payment request...');

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
                    showGatewayPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone and enter your PIN to complete the payment.');
                    startGatewayStatusChecking();
                } else {
                    showGatewayPaymentStatus('error', 'Payment Failed', data.message || 'Failed to initiate payment');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Money';
                }
            } catch (error) {
                showGatewayPaymentStatus('error', 'Network Error', 'Please check your connection and try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Money';
            }
        };

        function showGatewayPaymentStatus(type, title, message) {
            const statusDiv = document.getElementById('gwPaymentStatus');
            const statusIcon = document.getElementById('gwStatusIcon');
            const statusTitle = document.getElementById('gwStatusTitle');
            const statusMessage = document.getElementById('gwStatusMessage');

            statusTitle.textContent = title;
            statusMessage.textContent = message;

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

        function startGatewayStatusChecking() {
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

                            setTimeout(() => {
                                loadWalletData();
                                loadTransactions();
                            }, 1000);

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
                                document.getElementById('gwSubmitPaymentBtn').disabled = false;
                                document.getElementById('gwSubmitPaymentBtn').textContent = 'Add Money';
                            }, 1000);
                        }
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                }
            }, 3000);
        }

        function showTransactionResultModal(type, data) {
            const modal = document.getElementById('transactionResultModal');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const message = document.getElementById('resultMessage');
            const details = document.getElementById('resultDetails');

            title.textContent = data.title;
            message.textContent = data.message;

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
                            <p class="text-red-800 text-xs overflow-hidden"><strong>Reason:</strong> ${data.reason}</p>
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

        // Event listeners for gateway form
        document.getElementById('gwPhoneNumber').addEventListener('blur', function (e) {
            const phone = e.target.value.trim();
            if (phone && phone !== validatedMsisdn) {
                if (validationTimeout) {
                    clearTimeout(validationTimeout);
                }
                validationTimeout = setTimeout(() => {
                    validateGatewayPhoneNumber(phone);
                }, 500);
            }
        });

        document.getElementById('gwPhoneNumber').addEventListener('input', function (e) {
            if (validatedMsisdn && e.target.value.trim() !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null;
                customerName = null;
            }
        });

        document.getElementById('gwPhoneNumber').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length > 9) {
                value = value.substring(0, 9);
            }

            e.target.value = value;

            if (validatedMsisdn && ('+256' + value) !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null;
                customerName = null;
            }
        });

        document.getElementById('gwAmount').addEventListener('input', function (e) {
            const amount = parseFloat(e.target.value);
            if (amount && amount < 500) {
                showGatewayAmountError('Minimum amount is 500 UGX');
            } else {
                hideGatewayAmountError();
            }
        });

        document.getElementById('gwPhoneNumber').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                validateGatewayPhoneNumber();
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