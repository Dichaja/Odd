<?php
$cashAccounts = [];
$walletId = null;

try {
    $stmt = $pdo->prepare("
        SELECT id, name, type, provider, account_number
        FROM zzimba_cash_accounts
        WHERE status = 'active'
        ORDER BY type, name
    ");
    $stmt->execute();
    $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching cash accounts: " . $e->getMessage());
}

$groupedAccounts = [];
foreach ($cashAccounts as $account) {
    $groupedAccounts[$account['type']][] = $account;
}

try {
    if (!isset($pdo)) {
        throw new Exception("PDO connection not set");
    }
    if (!empty($_SESSION['user']['user_id'])) {
        $userId = $_SESSION['user']['user_id'];
        $stmt = $pdo->prepare("
            SELECT wallet_id
            FROM zzimba_wallets
            WHERE user_id = :uid AND owner_type = 'USER' AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $walletId = $stmt->fetchColumn();
    }
} catch (Exception $e) {
    error_log("Error fetching wallet ID: " . $e->getMessage());
}
?>

<style>
    .form-group {
        display: grid;
        gap: .375rem;
    }

    .form-label {
        font-size: .875rem;
        font-weight: 600;
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: .625rem .75rem;
        font-size: .875rem;
        border: 1px solid rgb(209 213 219);
        border-radius: .5rem;
        background: white;
        color: rgb(17 24 39);
        line-height: 1.25rem;
    }

    .form-textarea {
        resize: vertical;
        min-height: 2.75rem;
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgb(217 43 19 / .15);
        border-color: rgb(217 43 19);
    }

    .dark .form-input,
    .dark .form-textarea,
    .dark .form-select {
        background: transparent;
        color: white;
        border-color: rgba(255, 255, 255, .2);
    }

    .dark .form-input::placeholder,
    .dark .form-textarea::placeholder {
        color: rgba(255, 255, 255, .6);
    }

    .selector-item {
        border: 1px solid rgb(229 231 235);
        border-radius: .75rem;
        padding: .875rem 1rem;
        background: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        transition: border-color .2s, background-color .2s, transform .12s;
        cursor: pointer;
    }

    .selector-item:hover {
        border-color: rgb(217 43 19 / .5);
        background: rgb(217 43 19 / .04);
    }

    .selector-item:active {
        transform: scale(.995);
    }

    .selector-title {
        font-weight: 600;
    }

    .selector-sub {
        font-size: .75rem;
        opacity: .7;
        margin-top: .125rem;
    }

    .dark .selector-item {
        background: transparent;
        border-color: rgba(255, 255, 255, .12);
    }

    .dark .selector-item:hover {
        background: rgba(255, 255, 255, .06);
        border-color: rgba(255, 255, 255, .25);
    }
</style>

<div id="paymentCategoryModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Add Money to Wallet</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Choose how you want to send money</p>
                </div>
                <button onclick="hidePaymentCategoryModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="p-5 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div class="grid grid-cols-1 gap-3">
                <div class="selector-item" onclick="selectPaymentCategory('mobile_money')">
                    <div>
                        <div class="selector-title text-secondary dark:text-white">Send Mobile Money</div>
                        <div class="selector-sub text-gray-text dark:text-white/70">Airtel Money • MTN MoMo</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>

                <div class="selector-item" onclick="selectPaymentCategory('bank')">
                    <div>
                        <div class="selector-title text-secondary dark:text-white">Bank Deposits</div>
                        <div class="selector-sub text-gray-text dark:text-white/70">Deposit to our bank accounts</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>

                <div class="selector-item" onclick="selectPaymentCategory('gateway')">
                    <div>
                        <div class="selector-title text-secondary dark:text-white">Instant Pay</div>
                        <div class="selector-sub text-gray-text dark:text-white/70">Mobile money prompt or Card</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="accountSelectionModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="accountSelectionBack()"
                        class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10 grid place-items-center hover:bg-gray-200 dark:hover:bg-white/20">
                        <i class="fas fa-chevron-left text-gray-600 dark:text-white/80"></i>
                    </button>
                    <div>
                        <h3 id="accountSelectionTitle" class="text-lg font-semibold text-secondary dark:text-white">
                            Select Account</h3>
                        <p id="accountSelectionSubtitle" class="text-sm text-gray-text dark:text-white/70">Choose your
                            preferred account</p>
                    </div>
                </div>
                <button onclick="hideAccountSelectionModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-120px)] p-5">
            <div class="grid grid-cols-1 gap-3" id="accountSelectionCards"></div>
        </div>
    </div>
</div>

<div id="gatewayMethodModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Choose Payment Method</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Select how you want to pay</p>
                </div>
                <button onclick="hideGatewayMethodModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 gap-3">
                <div class="selector-item" onclick="selectGatewayMethod('mobile_money')">
                    <div>
                        <div class="selector-title text-secondary dark:text-white">Mobile Money</div>
                        <div class="selector-sub text-gray-text dark:text-white/70">Receive a payment prompt</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>

                <div class="selector-item" onclick="selectGatewayMethod('card')">
                    <div>
                        <div class="selector-title text-secondary dark:text-white">Card Payment</div>
                        <div class="selector-sub text-gray-text dark:text-white/70">Visa • Mastercard</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mobileMoneyModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="mobileMoneyBack()"
                        class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10 grid place-items-center hover:bg-gray-200 dark:hover:bg-white/20">
                        <i class="fas fa-chevron-left text-gray-600 dark:text-white/80"></i>
                    </button>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Account Number</h3>
                        <p class="text-sm text-gray-text dark:text-white/70" id="mobileMoneyAccountName"></p>
                    </div>
                </div>
                <button onclick="hideMobileMoneyModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-220px)] p-5">
            <div
                class="p-3 mb-4 rounded-lg border border-blue-100 dark:border-white/10 bg-blue-50 dark:bg-white/5 text-xs text-blue-900 dark:text-white/80">
                Send money to the number above, then fill the details below. We’ll verify and credit your account within
                the hour.
            </div>

            <form id="mobileMoneyForm" class="grid gap-4">
                <div class="form-group">
                    <label for="mmPhoneNumber" class="form-label text-secondary dark:text-white">Phone number used <span
                            class="text-red-500">*</span></label>
                    <div class="flex">
                        <span
                            class="inline-flex items-center px-3 rounded-l-lg border border-gray-300 dark:border-white/20 bg-gray-50 dark:bg-white/10 text-sm text-gray-600 dark:text-white/80">+256</span>
                        <input type="tel" id="mmPhoneNumber" name="mmPhoneNumber" class="form-input rounded-l-none"
                            placeholder="771234567" maxlength="9" pattern="[0-9]{9}" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label for="mmAmount" class="form-label text-secondary dark:text-white">Amount (UGX) <span
                                class="text-red-500">*</span></label>
                        <input type="number" id="mmAmount" name="mmAmount" min="500" step="100" class="form-input"
                            placeholder="e.g. 100000" required>
                    </div>
                    <div class="form-group">
                        <label for="mmDateTime" class="form-label text-secondary dark:text-white">Date & Time <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" id="mmDateTime" name="mmDateTime" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mmTransactionId" class="form-label text-secondary dark:text-white">Transaction ID <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="mmTransactionId" name="mmTransactionId" class="form-input"
                        placeholder="Reference from provider" required>
                </div>

                <div class="form-group">
                    <label for="mmNote" class="form-label text-secondary dark:text-white">Note (optional)</label>
                    <textarea id="mmNote" name="mmNote" rows="3" class="form-textarea"
                        placeholder="Message or reason"></textarea>
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-white/10">
            <div class="flex gap-3">
                <button type="button" onclick="hideMobileMoneyModal()"
                    class="flex-1 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button type="button" onclick="confirmMobileMoneyPayment()"
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Submit
                    Payment</button>
            </div>
        </div>
    </div>
</div>

<div id="bankTransferModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="bankTransferBack()"
                        class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10 grid place-items-center hover:bg-gray-200 dark:hover:bg-white/20">
                        <i class="fas fa-chevron-left text-gray-600 dark:text-white/80"></i>
                    </button>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Bank Transfer</h3>
                        <p class="text-sm text-gray-text dark:text-white/70" id="bankAccountName"></p>
                    </div>
                </div>
                <button onclick="hideBankTransferModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-220px)] p-5">
            <div
                class="p-3 mb-4 rounded-lg border border-green-100 dark:border-white/10 bg-green-50 dark:bg-white/5 text-xs text-green-900 dark:text-white/80">
                Deposit to the bank account above, then fill the details. We’ll verify and credit your account within
                the hour.
            </div>

            <form id="bankTransferForm" class="grid gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label for="btAmount" class="form-label text-secondary dark:text-white">Amount (UGX) <span
                                class="text-red-500">*</span></label>
                        <input type="number" id="btAmount" name="btAmount" min="500" step="100" class="form-input"
                            placeholder="e.g. 250000" required>
                    </div>
                    <div class="form-group">
                        <label for="btDateTime" class="form-label text-secondary dark:text-white">Date & Time <span
                                class="text-red-500">*</span></label>
                        <input type="datetime-local" id="btDateTime" name="btDateTime" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="btReference" class="form-label text-secondary dark:text-white">Bank Reference/Receipt
                        No. <span class="text-red-500">*</span></label>
                    <input type="text" id="btReference" name="btReference" class="form-input"
                        placeholder="From deposit slip/receipt" required>
                </div>

                <div class="form-group">
                    <label for="btDepositorName" class="form-label text-secondary dark:text-white">Depositor Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="btDepositorName" name="btDepositorName" class="form-input"
                        placeholder="Name used for deposit" required>
                </div>

                <div class="form-group">
                    <label for="btNote" class="form-label text-secondary dark:text-white">Note (optional)</label>
                    <textarea id="btNote" name="btNote" rows="3" class="form-textarea"
                        placeholder="Message or reason"></textarea>
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-white/10">
            <div class="flex gap-3">
                <button type="button" onclick="hideBankTransferModal()"
                    class="flex-1 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button type="button" onclick="confirmBankTransferPayment()"
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Submit
                    Payment</button>
            </div>
        </div>
    </div>
</div>

<div id="gatewayPaymentModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Mobile Money Payment</h3>
                    <p class="text-sm text-gray-text dark:text-white/70" id="gatewayAccountName"></p>
                </div>
                <button onclick="hideGatewayPaymentModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-220px)] p-5">
            <div
                class="p-3 mb-4 rounded-lg border border-purple-100 dark:border-white/10 bg-purple-50 dark:bg-white/5 text-xs text-purple-900 dark:text-white/80">
                Enter your number and amount. You’ll receive a prompt on your phone to approve the payment.
            </div>

            <form id="gatewayPaymentForm" class="grid gap-4">
                <div class="form-group">
                    <label for="gwPhoneNumber" class="form-label text-secondary dark:text-white">Phone Number <span
                            class="text-red-500">*</span></label>
                    <div class="flex items-stretch">
                        <span
                            class="inline-flex items-center px-3 rounded-l-lg border border-gray-300 dark:border-white/20 bg-gray-50 dark:bg-white/10 text-sm text-gray-600 dark:text-white/80">+256</span>
                        <input type="tel" id="gwPhoneNumber" name="gwPhoneNumber"
                            class="form-input rounded-l-none pr-10" placeholder="771234567" maxlength="9"
                            pattern="[0-9]{9}" required>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-white/60">Enter exactly 9 digits (without the
                        leading 0)</div>
                    <div id="gwCustomerName" class="mt-2 text-sm text-green-600 hidden"></div>
                    <div id="gwPhoneError" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="form-group">
                        <label for="gwAmount" class="form-label text-secondary dark:text-white">Amount (UGX) <span
                                class="text-red-500">*</span></label>
                        <input type="number" id="gwAmount" name="gwAmount" min="500" step="100" class="form-input"
                            placeholder="e.g. 5000" required>
                        <div id="gwAmountError" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>
                    <div class="form-group">
                        <label for="gwDescription" class="form-label text-secondary dark:text-white">Description</label>
                        <input type="text" id="gwDescription" name="gwDescription" class="form-input"
                            placeholder="Optional">
                    </div>
                </div>

                <div id="gwPaymentStatus" class="hidden p-3 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div id="gwStatusIcon" class="w-7 h-7 rounded-full grid place-items-center"><i
                                class="fas fa-spinner fa-spin"></i></div>
                        <div>
                            <div id="gwStatusTitle" class="text-sm font-semibold text-secondary dark:text-white">
                                Processing Payment</div>
                            <div id="gwStatusMessage" class="text-xs text-gray-text dark:text-white/70">Please wait…
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-white/10">
            <div class="flex gap-3">
                <button type="button" onclick="hideGatewayPaymentModal()"
                    class="flex-1 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button type="button" id="gwSubmitPaymentBtn" onclick="confirmGatewayPayment()" disabled
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">Add
                    Money</button>
            </div>
        </div>
    </div>
</div>

<div id="cardPaymentModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Card Payment</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Pay securely with your card</p>
                </div>
                <button onclick="hideCardPaymentModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(90vh-220px)] p-5">
            <div
                class="p-3 mb-4 rounded-lg border border-indigo-100 dark:border-white/10 bg-indigo-50 dark:bg-white/5 text-xs text-indigo-900 dark:text-white/80">
                Enter the amount and proceed. You’ll be redirected to a secure payment page.
            </div>

            <form id="cardPaymentForm" class="grid gap-4">
                <div class="form-group">
                    <label for="cardAmount" class="form-label text-secondary dark:text-white">Amount (UGX) <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="cardAmount" name="cardAmount" min="500" step="100" class="form-input"
                        placeholder="e.g. 75000" required>
                    <div id="cardAmountError" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div
                    class="p-3 rounded-lg border border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-xs text-gray-600 dark:text-white/80">
                    We do not store your card details. Processing is handled by our PCI-compliant gateway.
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-white/10">
            <div class="flex gap-3">
                <button type="button" onclick="hideCardPaymentModal()"
                    class="flex-1 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button type="button" id="cardSubmitPaymentBtn" onclick="confirmCardPayment()" disabled
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed">Proceed
                    to Payment</button>
            </div>
        </div>
    </div>
</div>

<div id="confirmationModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6 text-center">
            <div class="w-14 h-14 bg-yellow-100 rounded-full grid place-items-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-secondary dark:text-white mb-1">Confirm Payment</h3>
            <p class="text-gray-text dark:text-white/80 mb-5" id="confirmationMessage"></p>
            <div id="confirmationDetails"
                class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 mb-5 text-left border border-gray-100 dark:border-white/10 text-sm">
            </div>
            <div class="flex gap-3">
                <button onclick="hideConfirmationModal()"
                    class="flex-1 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button id="confirmSubmitBtn" onclick="executePaymentSubmission()"
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div id="transactionResultModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content transform transition-all duration-300 scale-95">
        <div class="p-6 text-center">
            <div id="resultIcon" class="w-16 h-16 rounded-full grid place-items-center mx-auto mb-4"></div>
            <h3 id="resultTitle" class="text-lg font-semibold text-secondary dark:text-white mb-1"></h3>
            <p id="resultMessage" class="text-gray-text dark:text-white/80 mb-5 overflow-hidden text-sm"></p>
            <div id="resultDetails"
                class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 mb-5 text-left border border-gray-100 dark:border-white/10 text-sm">
            </div>
            <div class="flex gap-3">
                <button onclick="hideTransactionResultModal()"
                    class="flex-1 px-4 py-2 text-sm bg-primary text-white rounded-xl hover:bg-primary/90">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topupUrl = <?= json_encode(BASE_URL . 'account/fetch/manageTopup.php') ?>;
        const gatewayApiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;
        const groupedAccounts = <?= json_encode($groupedAccounts) ?>;
        const walletId = <?= json_encode($walletId) ?>;

        let selectedAccount = null, selectedCategory = null, selectedGatewayMethod = null, validatedMsisdn = null, customerName = null, currentPaymentReference = null, statusCheckInterval = null, validationTimeout = null, pendingPaymentData = null;

        function getKampalaDateTimeLocal() { const parts = new Date().toLocaleString('sv', { timeZone: 'Africa/Kampala', hour12: false }).split(' '); return `${parts[0]}T${parts[1].slice(0, 5)}`; }
        function showModal(id) { const m = document.getElementById(id); m.classList.remove('hidden'); setTimeout(() => { m.classList.remove('opacity-0'); m.querySelector('.transform').classList.remove('scale-95'); m.querySelector('.transform').classList.add('scale-100'); }, 10); }
        function hideModal(id) { const m = document.getElementById(id); m.classList.add('opacity-0'); m.querySelector('.transform').classList.remove('scale-100'); m.querySelector('.transform').classList.add('scale-95'); setTimeout(() => { m.classList.add('hidden'); }, 300); }

        function validateForm(id) {
            const f = document.getElementById(id);
            const req = f.querySelectorAll('[required]');
            let ok = true, first = null;
            req.forEach(el => {
                if (!el.value.trim()) { el.classList.add('ring-2', 'ring-red-300'); if (!first) first = el; ok = false; }
                else { el.classList.remove('ring-2', 'ring-red-300'); }
            });
            if (!ok && first) { first.focus(); first.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            return ok;
        }

        window.showPaymentCategoryModal = function () { showModal('paymentCategoryModal'); };
        window.showPaymentMethodModal = function () { showModal('paymentCategoryModal'); };
        window.hidePaymentCategoryModal = function () { hideModal('paymentCategoryModal'); };

        window.selectPaymentCategory = function (category) {
            selectedCategory = category; hideModal('paymentCategoryModal');
            setTimeout(() => { if (category === 'gateway') { showGatewayMethodModal(); } else { showAccountSelectionModal(category); } }, 300);
        };

        function showAccountSelectionModal(category) {
            const accounts = groupedAccounts[category] || [];
            const title = document.getElementById('accountSelectionTitle');
            const subtitle = document.getElementById('accountSelectionSubtitle');
            const cards = document.getElementById('accountSelectionCards');

            if (category === 'mobile_money') { title.textContent = 'Select Mobile Money Account'; subtitle.textContent = 'Choose your preferred provider'; }
            else if (category === 'bank') { title.textContent = 'Select Bank Account'; subtitle.textContent = 'Choose where you deposited'; }
            else { title.textContent = 'Select Account'; subtitle.textContent = 'Choose your account'; }

            cards.innerHTML = '';
            accounts.forEach(a => {
                const el = document.createElement('div');
                el.className = 'selector-item';
                el.onclick = () => selectAccount(a);
                el.innerHTML = `<div>
        <div class="selector-title text-secondary dark:text-white">${a.name}</div>
        <div class="selector-sub text-gray-text dark:text-white/70">${a.account_number} • ${a.type.replace('_', ' ')}</div>
      </div>
      <i class="fas fa-chevron-right text-gray-400"></i>`;
                cards.appendChild(el);
            });
            showModal('accountSelectionModal');
        }

        window.hideAccountSelectionModal = function () { hideModal('accountSelectionModal'); };
        window.accountSelectionBack = function () { hideModal('accountSelectionModal'); setTimeout(() => showModal('paymentCategoryModal'), 300); };
        function selectAccount(a) { selectedAccount = a; hideModal('accountSelectionModal'); setTimeout(() => { if (a.type === 'mobile_money') showMobileMoneyModal(); else if (a.type === 'bank') showBankTransferModal(); }, 300); }

        window.showGatewayMethodModal = function () { showModal('gatewayMethodModal'); };
        window.hideGatewayMethodModal = function () { hideModal('gatewayMethodModal'); };
        window.gatewayMethodBack = function () { hideModal('gatewayMethodModal'); setTimeout(() => showModal('paymentCategoryModal'), 300); };

        window.selectGatewayMethod = function (method) {
            selectedGatewayMethod = method; hideModal('gatewayMethodModal');
            setTimeout(() => { if (method === 'mobile_money') { const g = groupedAccounts['gateway'] || []; if (g.length > 0) { selectedAccount = g[0]; showGatewayPaymentModal(); } } else if (method === 'card') { showCardPaymentModal(); } }, 300);
        };

        window.showMobileMoneyModal = function () { document.getElementById('mobileMoneyAccountName').textContent = `${selectedAccount.name} • ${selectedAccount.account_number}`; document.getElementById('mmDateTime').value = getKampalaDateTimeLocal(); showModal('mobileMoneyModal'); };
        window.hideMobileMoneyModal = function () { hideModal('mobileMoneyModal'); setTimeout(() => { document.getElementById('mobileMoneyForm').reset(); }, 300); };
        window.mobileMoneyBack = function () { hideModal('mobileMoneyModal'); setTimeout(() => showAccountSelectionModal(selectedCategory), 300); };

        window.confirmMobileMoneyPayment = function () {
            if (!validateForm('mobileMoneyForm')) return;
            const fd = new FormData(document.getElementById('mobileMoneyForm'));
            const rawPhone = fd.get('mmPhoneNumber'); const formatted = rawPhone ? '+256' + rawPhone : '';
            pendingPaymentData = {
                type: 'mobile_money',
                payload: {
                    action: 'logTopup',
                    wallet_id: walletId,
                    cash_account_id: selectedAccount.id,
                    payment_method: 'MOBILE_MONEY',
                    amount_total: fd.get('mmAmount'),
                    external_reference: fd.get('mmTransactionId'),
                    note: fd.get('mmNote'),
                    mmPhoneNumber: formatted,
                    mmDateTime: fd.get('mmDateTime')
                }
            };
            showConfirmationModal(
                'You have initiated the following mobile money topup to your Zzimba Credit Account.',
                `Amount: UGX ${new Intl.NumberFormat().format(fd.get('mmAmount'))}<br>Phone: ${formatted}<br>Transaction ID: ${fd.get('mmTransactionId')}<br>Account: ${selectedAccount.name}`
            );
        };

        window.showBankTransferModal = function () { document.getElementById('bankAccountName').textContent = `${selectedAccount.name} • ${selectedAccount.account_number}`; document.getElementById('btDateTime').value = getKampalaDateTimeLocal(); showModal('bankTransferModal'); };
        window.hideBankTransferModal = function () { hideModal('bankTransferModal'); setTimeout(() => { document.getElementById('bankTransferForm').reset(); }, 300); };
        window.bankTransferBack = function () { hideModal('bankTransferModal'); setTimeout(() => showAccountSelectionModal(selectedCategory), 300); };

        window.confirmBankTransferPayment = function () {
            if (!validateForm('bankTransferForm')) return;
            const fd = new FormData(document.getElementById('bankTransferForm'));
            pendingPaymentData = {
                type: 'bank_transfer',
                payload: {
                    action: 'logTopup',
                    wallet_id: walletId,
                    cash_account_id: selectedAccount.id,
                    payment_method: 'BANK',
                    amount_total: fd.get('btAmount'),
                    external_reference: fd.get('btReference'),
                    note: fd.get('btNote'),
                    btDepositorName: fd.get('btDepositorName'),
                    btDateTime: fd.get('btDateTime')
                }
            };
            showConfirmationModal(
                'You have initiated the following Bank topup to your Zzimba Credit Account.',
                `Amount: UGX ${new Intl.NumberFormat().format(fd.get('btAmount'))}<br>Reference: ${fd.get('btReference')}<br>Depositor: ${fd.get('btDepositorName')}<br>Account: ${selectedAccount.name}`
            );
        };

        window.showGatewayPaymentModal = function () { document.getElementById('gatewayAccountName').textContent = selectedAccount.name; showModal('gatewayPaymentModal'); resetGatewayForm(); };
        window.hideGatewayPaymentModal = function () { hideModal('gatewayPaymentModal'); setTimeout(() => resetGatewayForm(), 300); if (statusCheckInterval) { clearInterval(statusCheckInterval); statusCheckInterval = null; } };
        window.gatewayPaymentBack = function () { hideModal('gatewayPaymentModal'); setTimeout(() => showGatewayMethodModal(), 300); };

        window.showCardPaymentModal = function () { showModal('cardPaymentModal'); resetCardForm(); };
        window.hideCardPaymentModal = function () { hideModal('cardPaymentModal'); setTimeout(() => resetCardForm(), 300); };
        window.cardPaymentBack = function () { hideModal('cardPaymentModal'); setTimeout(() => showGatewayMethodModal(), 300); };

        window.confirmCardPayment = function () {
            if (!validateForm('cardPaymentForm')) return;
            const amount = parseFloat(document.getElementById('cardAmount').value);
            if (!amount || amount < 500) { showCardAmountError('Please enter a valid amount (minimum 500 UGX)'); return; }
            const paymentUrl = `${BASE_URL}payment/card?amount=${amount}&description=Zzimba+wallet+top-up`;
            window.open(paymentUrl, '_blank');
            hideCardPaymentModal();
            showTransactionResultModal('success', { title: 'Redirected to Payment', message: 'You have been redirected to our secure payment page. Complete the payment to add money to your wallet.', amount: amount, currency: 'UGX' });
        };

        function resetCardForm() { document.getElementById('cardPaymentForm').reset(); document.getElementById('cardAmountError').classList.add('hidden'); document.getElementById('cardSubmitPaymentBtn').disabled = true; }
        function showCardAmountError(msg) { const e = document.getElementById('cardAmountError'); e.textContent = msg; e.classList.remove('hidden'); document.getElementById('cardSubmitPaymentBtn').disabled = true; }
        function hideCardAmountError() { document.getElementById('cardAmountError').classList.add('hidden'); checkCardFormValidity(); }
        function checkCardFormValidity() { const a = parseFloat(document.getElementById('cardAmount').value); document.getElementById('cardSubmitPaymentBtn').disabled = !(a >= 500); }
        document.getElementById('cardAmount').addEventListener('input', e => { const a = parseFloat(e.target.value); if (a && a < 500) showCardAmountError('Minimum amount is 500 UGX'); else hideCardAmountError(); });

        window.confirmGatewayPayment = function () {
            if (!validateForm('gatewayPaymentForm')) return;
            if (!validatedMsisdn) { showGatewayPhoneError('Please validate the phone number first'); return; }
            const amt = parseFloat(document.getElementById('gwAmount').value);
            const desc = document.getElementById('gwDescription').value.trim() || 'Zzimba wallet top-up';
            if (!amt || amt < 500) { showGatewayAmountError('Please enter a valid amount (minimum 500 UGX)'); return; }
            pendingPaymentData = { type: 'gateway', payload: { wallet_id: walletId, msisdn: validatedMsisdn, amount: amt, description: desc } };
            showConfirmationModal('Are you sure you want to proceed with this payment?', `Amount: UGX ${new Intl.NumberFormat().format(amt)}<br>Phone: ${validatedMsisdn}<br>Customer: ${customerName}<br>Provider: ${selectedAccount.name}`);
        };

        function showConfirmationModal(message, details) { document.getElementById('confirmationMessage').textContent = message; document.getElementById('confirmationDetails').innerHTML = details; showModal('confirmationModal'); }
        window.hideConfirmationModal = function () { hideModal('confirmationModal'); };

        window.executePaymentSubmission = function () {
            hideModal('confirmationModal');
            setTimeout(() => { if (pendingPaymentData.type === 'mobile_money') submitMobileMoneyPayment(); else if (pendingPaymentData.type === 'bank_transfer') submitBankTransferPayment(); else if (pendingPaymentData.type === 'gateway') submitGatewayPayment(); }, 300);
        };

        window.submitMobileMoneyPayment = async function () {
            try {
                const resp = await fetch(topupUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(pendingPaymentData.payload) });
                const data = await resp.json();
                if (data.success) {
                    showTransactionResultModal('success', { title: 'Payment Submitted Successfully!', message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.', transactionId: data.transaction_id, amount: pendingPaymentData.payload.amount_total, currency: 'UGX' });
                    hideMobileMoneyModal(); if (window.loadWalletData) window.loadWalletData(); if (window.loadTransactions) window.loadTransactions();
                } else { showTransactionResultModal('failed', { title: 'Payment Failed', message: data.message || 'Failed to process your payment.', reason: data.message }); }
            } catch (e) { showTransactionResultModal('failed', { title: 'Network Error', message: 'Please check your connection and try again.', reason: 'Network connection failed' }); }
        };

        window.submitBankTransferPayment = async function () {
            try {
                const resp = await fetch(topupUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(pendingPaymentData.payload) });
                const data = await resp.json();
                if (data.success) {
                    showTransactionResultModal('success', { title: 'Payment Submitted Successfully!', message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.', transactionId: data.transaction_id, amount: pendingPaymentData.payload.amount_total, currency: 'UGX' });
                    hideBankTransferModal(); if (window.loadWalletData) window.loadWalletData(); if (window.loadTransactions) window.loadTransactions();
                } else { showTransactionResultModal('failed', { title: 'Payment Failed', message: data.message || 'Failed to process your payment.', reason: data.message }); }
            } catch (e) { showTransactionResultModal('failed', { title: 'Network Error', message: 'Please check your connection and try again.', reason: 'Network connection failed' }); }
        };

        window.submitGatewayPayment = async function () {
            const btn = document.getElementById('gwSubmitPaymentBtn');
            btn.disabled = true; btn.textContent = 'Processing...'; showGatewayPaymentStatus('processing', 'Processing Payment', 'Initiating payment request...');
            try {
                const resp = await fetch(`${gatewayApiUrl}?action=makePayment`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams(pendingPaymentData.payload) });
                const data = await resp.json();
                if (data.success) { currentPaymentReference = data.internal_reference; showGatewayPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone…'); startGatewayStatusChecking(); }
                else { showGatewayPaymentStatus('error', 'Payment Failed', data.message || 'Failed to initiate payment'); btn.disabled = false; btn.textContent = 'Add Money'; }
            } catch (_) { showGatewayPaymentStatus('error', 'Network Error', 'Please check your connection and try again.'); btn.disabled = false; btn.textContent = 'Add Money'; }
        };

        function showTransactionResultModal(type, data) {
            const modal = document.getElementById('transactionResultModal');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const det = document.getElementById('resultDetails');
            title.textContent = data.title; msg.textContent = data.message;
            const cont = modal.querySelector('.modal-content');
            cont.className = 'bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content transform transition-all duration-300 scale-95';
            icon.className = 'w-16 h-16 rounded-full grid place-items-center mx-auto mb-4';
            function fmt(a) { return new Intl.NumberFormat('en-UG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(a); }
            if (type === 'success') {
                cont.classList.add('border-t-4', 'border-green-500'); icon.classList.add('bg-green-100'); icon.innerHTML = '<i class="fas fa-check text-green-600 text-2xl"></i>';
                det.innerHTML = `<div class="space-y-3 text-sm">
        <div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Amount:</span><span class="font-semibold">${data.currency || 'UGX'} ${fmt(data.amount)}</span></div>
        ${data.charge ? `<div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Transaction Fee:</span><span class="font-semibold">${data.currency || 'UGX'} ${fmt(data.charge)}</span></div>` : ''}
        <div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Provider:</span><span class="font-semibold">${data.provider?.replace('_', ' ') || 'N/A'}</span></div>
        <div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Transaction ID:</span><span class="font-mono text-xs">${data.transactionId || 'N/A'}</span></div>
        <div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Reference:</span><span class="font-mono text-xs">${data.reference || 'N/A'}</span></div>
        ${data.completedAt && data.completedAt !== 'N/A' ? `<div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Completed:</span><span class="text-xs">${new Date(data.completedAt).toLocaleString()}</span></div>` : ''}
      </div>`;
            } else {
                cont.classList.add('border-t-4', 'border-red-500'); icon.classList.add('bg-red-100'); icon.innerHTML = '<i class="fas fa-times text-red-600 text-2xl"></i>';
                det.innerHTML = `<div class="space-y-3 text-sm">
        ${data.amount ? `<div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Amount:</span><span class="font-semibold">${data.currency || 'UGX'} ${fmt(data.amount)}</span></div>` : ''}
        ${data.provider ? `<div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Provider:</span><span class="font-semibold">${data.provider.replace('_', ' ')}</span></div>` : ''}
        ${data.reference ? `<div class="flex justify-between"><span class="text-gray-600 dark:text-white/70">Reference:</span><span class="font-mono text-xs">${data.reference}</span></div>` : ''}
        ${data.reason ? `<div class="mt-2 p-3 bg-red-50 dark:bg-white/10 rounded-lg text-red-800 dark:text-red-300 text-xs"><strong>Reason:</strong> ${data.reason}</div>` : ''}
      </div>`;
            }
            showModal('transactionResultModal');
        }
        window.hideTransactionResultModal = function () { hideModal('transactionResultModal'); };

        function resetGatewayForm() {
            document.getElementById('gatewayPaymentForm').reset();
            ['gwCustomerName', 'gwPhoneError', 'gwAmountError', 'gwPaymentStatus'].forEach(id => document.getElementById(id).classList.add('hidden'));
            document.getElementById('gwSubmitPaymentBtn').disabled = true; validatedMsisdn = null; customerName = null; currentPaymentReference = null;
            if (validationTimeout) { clearTimeout(validationTimeout); validationTimeout = null; }
        }

        async function validateGatewayPhoneNumber(phone = null) {
            const inp = document.getElementById('gwPhoneNumber');
            const nameDiv = document.getElementById('gwCustomerName');
            const errDiv = document.getElementById('gwPhoneError');
            const val = phone || inp.value.trim();
            if (!val) return showGatewayPhoneError('Please enter a phone number');
            if (!/^\d{9}$/.test(val)) return showGatewayPhoneError('Please enter exactly 9 digits');
            const formatted = '+256' + val; errDiv.classList.add('hidden'); nameDiv.classList.add('hidden');
            try {
                const resp = await fetch(`${gatewayApiUrl}?action=validateMsisdn`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ msisdn: formatted }) });
                const data = await resp.json();
                if (data.success) { validatedMsisdn = formatted; customerName = data.customer_name; nameDiv.textContent = `✓ ${data.customer_name}`; nameDiv.classList.remove('hidden'); checkGatewayFormValidity(); }
                else { showGatewayPhoneError(data.message || 'Phone number validation failed'); }
            } catch (_) { showGatewayPhoneError('Network error. Please try again.'); }
        }

        function showGatewayPhoneError(msg) { const err = document.getElementById('gwPhoneError'); err.textContent = msg; err.classList.remove('hidden'); document.getElementById('gwCustomerName').classList.add('hidden'); document.getElementById('gwSubmitPaymentBtn').disabled = true; validatedMsisdn = null; customerName = null; }
        function showGatewayAmountError(msg) { const err = document.getElementById('gwAmountError'); err.textContent = msg; err.classList.remove('hidden'); document.getElementById('gwSubmitPaymentBtn').disabled = true; }
        function hideGatewayAmountError() { document.getElementById('gwAmountError').classList.add('hidden'); checkGatewayFormValidity(); }
        function checkGatewayFormValidity() { const amt = parseFloat(document.getElementById('gwAmount').value); document.getElementById('gwSubmitPaymentBtn').disabled = !(validatedMsisdn && amt >= 500); }

        function showGatewayPaymentStatus(type, title, message) {
            const div = document.getElementById('gwPaymentStatus'); const icon = document.getElementById('gwStatusIcon'); const t = document.getElementById('gwStatusTitle'); const m = document.getElementById('gwStatusMessage');
            t.textContent = title; m.textContent = message; div.className = 'p-3 rounded-lg'; icon.className = 'w-7 h-7 rounded-full grid place-items-center';
            switch (type) {
                case 'processing': div.classList.add('bg-blue-50', 'border', 'border-blue-200'); icon.classList.add('bg-blue-100'); icon.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600"></i>'; break;
                case 'pending': div.classList.add('bg-yellow-50', 'border', 'border-yellow-200'); icon.classList.add('bg-yellow-100'); icon.innerHTML = '<i class="fas fa-clock text-yellow-600"></i>'; break;
                case 'success': div.classList.add('bg-green-50', 'border', 'border-green-200'); icon.classList.add('bg-green-100'); icon.innerHTML = '<i class="fas fa-check text-green-600"></i>'; break;
                case 'error': div.classList.add('bg-red-50', 'border', 'border-red-200'); icon.classList.add('bg-red-100'); icon.innerHTML = '<i class="fas fa-times text-red-600"></i>';
            }
            div.classList.remove('hidden');
        }

        function startGatewayStatusChecking() {
            if (!currentPaymentReference) return;
            statusCheckInterval = setInterval(async () => {
                try {
                    const resp = await fetch(`${gatewayApiUrl}?action=checkStatus`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ internal_reference: currentPaymentReference }) });
                    const data = await resp.json();
                    if (data.success) {
                        if (data.status === 'success') {
                            clearInterval(statusCheckInterval); statusCheckInterval = null; hideGatewayPaymentModal();
                            setTimeout(() => { showTransactionResultModal('success', { title: 'Payment Successful!', message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.', amount: data.amount, currency: data.currency || 'UGX', provider: data.provider, transactionId: data.provider_transaction_id, reference: data.customer_reference, charge: data.charge, completedAt: data.completed_at }); }, 300);
                            setTimeout(() => { if (window.loadWalletData) window.loadWalletData(); if (window.loadTransactions) window.loadTransactions(); }, 1000);
                        } else if (data.status === 'failed') {
                            clearInterval(statusCheckInterval); statusCheckInterval = null; hideGatewayPaymentModal();
                            setTimeout(() => { showTransactionResultModal('failed', { title: 'Payment Failed', message: data.message || 'Payment could not be completed.', amount: data.amount, currency: data.currency || 'UGX', provider: data.provider, reference: data.customer_reference, reason: data.message }); }, 300);
                            setTimeout(() => { const btn = document.getElementById('gwSubmitPaymentBtn'); btn.disabled = false; btn.textContent = 'Add Money'; }, 1000);
                        }
                    }
                } catch (e) { }
            }, 3000);
        }

        document.getElementById('gwPhoneNumber').addEventListener('blur', e => { const phone = e.target.value.trim(); if (phone && phone !== validatedMsisdn) { if (validationTimeout) clearTimeout(validationTimeout); validationTimeout = setTimeout(() => validateGatewayPhoneNumber(phone), 400); } });
        document.getElementById('gwPhoneNumber').addEventListener('input', e => { const val = e.target.value.trim(); if (validatedMsisdn && ('+256' + val) !== validatedMsisdn) { document.getElementById('gwCustomerName').classList.add('hidden'); document.getElementById('gwPhoneError').classList.add('hidden'); document.getElementById('gwSubmitPaymentBtn').disabled = true; validatedMsisdn = null; customerName = null; } });
        document.getElementById('gwPhoneNumber').addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); validateGatewayPhoneNumber(); } });
        document.getElementById('gwAmount').addEventListener('input', e => { const a = parseFloat(e.target.value); if (a && a < 500) showGatewayAmountError('Minimum amount is 500 UGX'); else hideGatewayAmountError(); });

        document.querySelectorAll('input[required], textarea[required]').forEach(f => { f.addEventListener('input', function () { if (this.value.trim()) { this.classList.remove('ring-2', 'ring-red-300'); } }); });
    });
</script>