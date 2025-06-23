<?php
$cashAccounts = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, type, provider, account_number FROM zzimba_cash_accounts WHERE status = 'active' ORDER BY type, name");
    $stmt->execute();
    $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching cash accounts: " . $e->getMessage());
}
?>

<!-- Payment Method Selection Modal -->
<div id="paymentMethodModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Choose Payment Method</h3>
                        <p class="text-sm text-gray-500">Select how you want to add money to your wallet</p>
                    </div>
                </div>
                <button onclick="hidePaymentMethodModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="paymentMethodCards">
                <?php foreach ($cashAccounts as $account): ?>
                    <div class="payment-method-card border-2 border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer transform hover:scale-[1.02]"
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
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex justify-end">
                <button onclick="hidePaymentMethodModal()"
                    class="px-6 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Money Payment Modal -->
<div id="mobileMoneyModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <button onclick="mobileMoneyBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Mobile Money Payment</h3>
                        <p class="text-sm text-gray-500" id="mobileMoneyAccountName"></p>
                    </div>
                </div>
                <button onclick="hideMobileMoneyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <form id="mobileMoneyForm" class="space-y-4">
                <div>
                    <label for="mmPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                        Phone Number Used <span class="text-red-500">*</span>
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
                        Amount Sent (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="mmAmount" name="mmAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount sent" required>
                </div>

                <div>
                    <label for="mmTransactionId" class="block text-sm font-semibold text-gray-700 mb-2">
                        Transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="mmTransactionId" name="mmTransactionId"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter transaction ID" required>
                </div>

                <div>
                    <label for="mmNote" class="block text-sm font-semibold text-gray-700 mb-2">Note/Message</label>
                    <textarea id="mmNote" name="mmNote" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Optional note or message"></textarea>
                </div>

                <div>
                    <label for="mmDateTime" class="block text-sm font-semibold text-gray-700 mb-2">
                        Date & Time Sent <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="mmDateTime" name="mmDateTime"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex gap-3">
                <button type="button" onclick="hideMobileMoneyModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" onclick="confirmMobileMoneyPayment()"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                    Submit Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bank Transfer Payment Modal -->
<div id="bankTransferModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <button onclick="bankTransferBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-university text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Bank Transfer</h3>
                        <p class="text-sm text-gray-500" id="bankAccountName"></p>
                    </div>
                </div>
                <button onclick="hideBankTransferModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <form id="bankTransferForm" class="space-y-4">
                <div>
                    <label for="btAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Amount Deposited (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="btAmount" name="btAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount deposited" required>
                </div>

                <div>
                    <label for="btReference" class="block text-sm font-semibold text-gray-700 mb-2">
                        Bank Reference/Receipt Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="btReference" name="btReference"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter reference number" required>
                </div>

                <div>
                    <label for="btDepositorName" class="block text-sm font-semibold text-gray-700 mb-2">
                        Depositor Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="btDepositorName" name="btDepositorName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Name used for deposit" required>
                </div>

                <div>
                    <label for="btNote" class="block text-sm font-semibold text-gray-700 mb-2">Note/Message</label>
                    <textarea id="btNote" name="btNote" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Optional note or message"></textarea>
                </div>

                <div>
                    <label for="btDateTime" class="block text-sm font-semibold text-gray-700 mb-2">
                        Date & Time of Deposit <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="btDateTime" name="btDateTime"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex gap-3">
                <button type="button" onclick="hideBankTransferModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" onclick="confirmBankTransferPayment()"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                    Submit Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gateway Payment Modal -->
<div id="gatewayPaymentModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <button onclick="gatewayPaymentBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Gateway Payment</h3>
                        <p class="text-sm text-gray-500" id="gatewayAccountName"></p>
                    </div>
                </div>
                <button onclick="hideGatewayPaymentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Content -->
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <form id="gatewayPaymentForm" class="space-y-4">
                <div>
                    <label for="gwPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
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
                        Amount (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="gwAmount" name="gwAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount (minimum 500)" required>
                    <div id="gwAmountError" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div>
                    <label for="gwDescription" class="block text-sm font-semibold text-gray-700 mb-2">Description
                        (Optional)</label>
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
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex gap-3">
                <button type="button" onclick="hideGatewayPaymentModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" id="gwSubmitPaymentBtn" onclick="confirmGatewayPayment()" disabled
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Add Money
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-sm relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Confirm Payment</h3>
                <p class="text-gray-600 mb-6" id="confirmationMessage"></p>
                <div id="confirmationDetails" class="bg-gray-50 rounded-xl p-4 mb-6 text-left"></div>
                <div class="flex gap-3">
                    <button onclick="hideConfirmationModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="confirmSubmitBtn" onclick="executePaymentSubmission()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Result Modal -->
<div id="transactionResultModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content transform transition-all duration-300 scale-95">
        <div class="p-6 text-center">
            <div id="resultIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"></div>
            <h3 id="resultTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
            <p id="resultMessage" class="text-gray-600 mb-6 overflow-hidden"></p>
            <div id="resultDetails" class="bg-gray-50 rounded-xl p-4 mb-6 text-left"></div>
            <div class="flex gap-3">
                <button onclick="hideTransactionResultModal()"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const topupUrl = <?= json_encode(BASE_URL . 'account/fetch/manageTopup.php') ?>;
        const gatewayApiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageZzimbaCredit.php') ?>;

        let selectedAccount = null;
        let validatedMsisdn = null;
        let customerName = null;
        let currentPaymentReference = null;
        let statusCheckInterval = null;
        let validationTimeout = null;
        let pendingPaymentData = null;

        function getKampalaDateTimeLocal() {
            const parts = new Date()
                .toLocaleString('sv', { timeZone: 'Africa/Kampala', hour12: false })
                .split(' ');
            const date = parts[0];
            const time = parts[1].slice(0, 5);
            return `${date}T${time}`;
        }

        // Modal Animation Functions
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.transform').classList.remove('scale-95');
                modal.querySelector('.transform').classList.add('scale-100');
            }, 10);
        }

        function hideModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Form Validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-300', 'bg-red-50');
                    if (!firstInvalidField) firstInvalidField = field;
                    isValid = false;
                } else {
                    field.classList.remove('border-red-300', 'bg-red-50');
                }
            });

            if (!isValid && firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return isValid;
        }

        // Payment Method Modal
        window.showPaymentMethodModal = function () {
            showModal('paymentMethodModal');
        };

        window.hidePaymentMethodModal = function () {
            hideModal('paymentMethodModal');
        };

        window.selectPaymentMethod = function (accountId, type, name, accountNumber, provider) {
            selectedAccount = { id: accountId, type, name, accountNumber, provider };
            hideModal('paymentMethodModal');

            setTimeout(() => {
                if (type === 'mobile_money') showMobileMoneyModal();
                else if (type === 'bank') showBankTransferModal();
                else if (type === 'gateway') showGatewayPaymentModal();
                else showNotificationModal('info', 'Coming Soon', 'This payment method will be available soon!');
            }, 300);
        };

        // Mobile Money Modal
        window.showMobileMoneyModal = function () {
            document.getElementById('mobileMoneyAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
            document.getElementById('mmDateTime').value = getKampalaDateTimeLocal();
            showModal('mobileMoneyModal');
        };

        window.hideMobileMoneyModal = function () {
            hideModal('mobileMoneyModal');
            setTimeout(() => {
                document.getElementById('mobileMoneyForm').reset();
            }, 300);
        };

        window.mobileMoneyBack = function () {
            hideModal('mobileMoneyModal');
            setTimeout(() => {
                showModal('paymentMethodModal');
            }, 300);
        };

        window.confirmMobileMoneyPayment = function () {
            if (!validateForm('mobileMoneyForm')) return;

            const fd = new FormData(document.getElementById('mobileMoneyForm'));
            const rawPhone = fd.get('mmPhoneNumber');
            const formattedPhone = rawPhone ? '+256' + rawPhone : '';

            pendingPaymentData = {
                type: 'mobile_money',
                payload: {
                    action: 'logTopup',
                    cash_account_id: selectedAccount.id,
                    payment_method: 'MOBILE_MONEY',
                    amount_total: fd.get('mmAmount'),
                    external_reference: fd.get('mmTransactionId'),
                    note: fd.get('mmNote'),
                    mmPhoneNumber: formattedPhone,
                    mmDateTime: fd.get('mmDateTime')
                }
            };

            showConfirmationModal(
                'You have initiated the following mobile money topup to your Zzimba Credit Account.',
                `Amount: UGX ${new Intl.NumberFormat().format(fd.get('mmAmount'))}<br>
                Phone: ${formattedPhone}<br>
                Transaction ID: ${fd.get('mmTransactionId')}<br>
                Account: ${selectedAccount.name}`
            );
        };

        window.submitMobileMoneyPayment = async function () {
            try {
                const resp = await fetch(topupUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(pendingPaymentData.payload)
                });
                const data = await resp.json();

                if (data.success) {
                    showTransactionResultModal('success', {
                        title: 'Payment Submitted Successfully!',
                        message: 'Your mobile money payment has been submitted and is being processed.',
                        transactionId: data.transaction_id,
                        amount: pendingPaymentData.payload.amount_total,
                        currency: 'UGX'
                    });
                    hideMobileMoneyModal();
                    if (window.loadWalletData) window.loadWalletData();
                    if (window.loadTransactions) window.loadTransactions();
                } else {
                    showTransactionResultModal('failed', {
                        title: 'Payment Failed',
                        message: data.message || 'Failed to process your payment.',
                        reason: data.message
                    });
                }
            } catch (e) {
                console.error('Top-up error:', e);
                showTransactionResultModal('failed', {
                    title: 'Network Error',
                    message: 'Please check your connection and try again.',
                    reason: 'Network connection failed'
                });
            }
        };

        // Bank Transfer Modal
        window.showBankTransferModal = function () {
            document.getElementById('bankAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.accountNumber}`;
            document.getElementById('btDateTime').value = getKampalaDateTimeLocal();
            showModal('bankTransferModal');
        };

        window.hideBankTransferModal = function () {
            hideModal('bankTransferModal');
            setTimeout(() => {
                document.getElementById('bankTransferForm').reset();
            }, 300);
        };

        window.bankTransferBack = function () {
            hideModal('bankTransferModal');
            setTimeout(() => {
                showModal('paymentMethodModal');
            }, 300);
        };

        window.confirmBankTransferPayment = function () {
            if (!validateForm('bankTransferForm')) return;

            const fd = new FormData(document.getElementById('bankTransferForm'));

            pendingPaymentData = {
                type: 'bank_transfer',
                payload: {
                    action: 'logTopup',
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
                `Amount: UGX ${new Intl.NumberFormat().format(fd.get('btAmount'))}<br>
                Reference: ${fd.get('btReference')}<br>
                Depositor: ${fd.get('btDepositorName')}<br>
                Account: ${selectedAccount.name}`
            );
        };

        window.submitBankTransferPayment = async function () {
            try {
                const resp = await fetch(topupUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(pendingPaymentData.payload)
                });
                const data = await resp.json();

                if (data.success) {
                    showTransactionResultModal('success', {
                        title: 'Payment Submitted Successfully!',
                        message: 'Your bank transfer payment has been submitted and is being processed.',
                        transactionId: data.transaction_id,
                        amount: pendingPaymentData.payload.amount_total,
                        currency: 'UGX'
                    });
                    hideBankTransferModal();
                    if (window.loadWalletData) window.loadWalletData();
                    if (window.loadTransactions) window.loadTransactions();
                } else {
                    showTransactionResultModal('failed', {
                        title: 'Payment Failed',
                        message: data.message || 'Failed to process your payment.',
                        reason: data.message
                    });
                }
            } catch (e) {
                console.error('Top-up error:', e);
                showTransactionResultModal('failed', {
                    title: 'Network Error',
                    message: 'Please check your connection and try again.',
                    reason: 'Network connection failed'
                });
            }
        };

        // Gateway Payment Modal
        window.showGatewayPaymentModal = function () {
            document.getElementById('gatewayAccountName').textContent = selectedAccount.name;
            showModal('gatewayPaymentModal');
            resetGatewayForm();
        };

        window.hideGatewayPaymentModal = function () {
            hideModal('gatewayPaymentModal');
            setTimeout(() => {
                resetGatewayForm();
            }, 300);
            if (statusCheckInterval) {
                clearInterval(statusCheckInterval);
                statusCheckInterval = null;
            }
        };

        window.gatewayPaymentBack = function () {
            hideModal('gatewayPaymentModal');
            setTimeout(() => {
                showModal('paymentMethodModal');
            }, 300);
        };

        window.confirmGatewayPayment = function () {
            if (!validateForm('gatewayPaymentForm')) return;
            if (!validatedMsisdn) {
                showGatewayPhoneError('Please validate the phone number first');
                return;
            }

            const amt = parseFloat(document.getElementById('gwAmount').value);
            const desc = document.getElementById('gwDescription').value.trim() || 'Zzimba wallet top-up';

            if (!amt || amt < 500) {
                showGatewayAmountError('Please enter a valid amount (minimum 500 UGX)');
                return;
            }

            pendingPaymentData = {
                type: 'gateway',
                payload: {
                    msisdn: validatedMsisdn,
                    amount: amt,
                    description: desc
                }
            };

            showConfirmationModal(
                'Are you sure you want to proceed with this payment?',
                `Amount: UGX ${new Intl.NumberFormat().format(amt)}<br>
                Phone: ${validatedMsisdn}<br>
                Customer: ${customerName}<br>
                Provider: ${selectedAccount.name}`
            );
        };

        window.submitGatewayPayment = async function () {
            const btn = document.getElementById('gwSubmitPaymentBtn');
            btn.disabled = true;
            btn.textContent = 'Processing...';
            showGatewayPaymentStatus('processing', 'Processing Payment', 'Initiating payment request...');

            try {
                const resp = await fetch(`${gatewayApiUrl}?action=makePayment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(pendingPaymentData.payload)
                });
                const data = await resp.json();

                if (data.success) {
                    currentPaymentReference = data.internal_reference;
                    showGatewayPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone…');
                    startGatewayStatusChecking();
                } else {
                    showGatewayPaymentStatus('error', 'Payment Failed', data.message || 'Failed to initiate payment');
                    btn.disabled = false;
                    btn.textContent = 'Add Money';
                }
            } catch (_) {
                showGatewayPaymentStatus('error', 'Network Error', 'Please check your connection and try again.');
                btn.disabled = false;
                btn.textContent = 'Add Money';
            }
        };

        // Confirmation Modal
        function showConfirmationModal(message, details) {
            document.getElementById('confirmationMessage').textContent = message;
            document.getElementById('confirmationDetails').innerHTML = details;
            showModal('confirmationModal');
        }

        window.hideConfirmationModal = function () {
            hideModal('confirmationModal');
        };

        window.executePaymentSubmission = function () {
            hideModal('confirmationModal');

            setTimeout(() => {
                if (pendingPaymentData.type === 'mobile_money') {
                    submitMobileMoneyPayment();
                } else if (pendingPaymentData.type === 'bank_transfer') {
                    submitBankTransferPayment();
                } else if (pendingPaymentData.type === 'gateway') {
                    submitGatewayPayment();
                }
            }, 300);
        };

        // Transaction Result Modal
        function showTransactionResultModal(type, data) {
            const modal = document.getElementById('transactionResultModal');
            const icon = document.getElementById('resultIcon');
            const title = document.getElementById('resultTitle');
            const msg = document.getElementById('resultMessage');
            const det = document.getElementById('resultDetails');

            title.textContent = data.title;
            msg.textContent = data.message;

            const cont = modal.querySelector('.modal-content');
            cont.className = 'bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden modal-content transform transition-all duration-300 scale-95';
            icon.className = 'w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4';

            function formatCurrency(a) {
                return new Intl.NumberFormat('en-UG', {
                    style: 'decimal',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(a);
            }

            if (type === 'success') {
                cont.classList.add('border-t-4', 'border-green-500');
                icon.classList.add('bg-green-100');
                icon.innerHTML = '<i class="fas fa-check text-green-600 text-2xl"></i>';
                det.innerHTML = `
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between"><span class="text-gray-600">Amount:</span><span class="font-semibold">${data.currency || 'UGX'} ${formatCurrency(data.amount)}</span></div>
                        ${data.charge ? `<div class="flex justify-between"><span class="text-gray-600">Transaction Fee:</span><span class="font-semibold">${data.currency || 'UGX'} ${formatCurrency(data.charge)}</span></div>` : ''}
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
                        ${data.amount ? `<div class="flex justify-between"><span class="text-gray-600">Amount:</span><span class="font-semibold">${data.currency || 'UGX'} ${formatCurrency(data.amount)}</span></div>` : ''}
                        ${data.provider ? `<div class="flex justify-between"><span class="text-gray-600">Provider:</span><span class="font-semibold">${data.provider.replace('_', ' ')}</span></div>` : ''}
                        ${data.reference ? `<div class="flex justify-between"><span class="text-gray-600">Reference:</span><span class="font-mono text-xs">${data.reference}</span></div>` : ''}
                        ${data.reason ? `<div class="mt-4 p-3 bg-red-50 rounded-lg"><p class="text-red-800 text-xs overflow-hidden"><strong>Reason:</strong> ${data.reason}</p></div>` : ''}
                    </div>
                `;
            }

            showModal('transactionResultModal');
        }

        window.hideTransactionResultModal = function () {
            hideModal('transactionResultModal');
        };

        // Gateway Helper Functions
        function resetGatewayForm() {
            document.getElementById('gatewayPaymentForm').reset();
            ['gwCustomerName', 'gwPhoneError', 'gwAmountError', 'gwPaymentStatus', 'gwPhoneValidationSpinner']
                .forEach(id => document.getElementById(id).classList.add('hidden'));
            document.getElementById('gwSubmitPaymentBtn').disabled = true;
            validatedMsisdn = null;
            customerName = null;
            currentPaymentReference = null;
            if (validationTimeout) {
                clearTimeout(validationTimeout);
                validationTimeout = null;
            }
        }

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
                const resp = await fetch(`${gatewayApiUrl}?action=validateMsisdn`, {
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
            validatedMsisdn = null;
            customerName = null;
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

        function showGatewayPaymentStatus(type, title, message) {
            const div = document.getElementById('gwPaymentStatus');
            const icon = document.getElementById('gwStatusIcon');
            const t = document.getElementById('gwStatusTitle');
            const m = document.getElementById('gwStatusMessage');

            t.textContent = title;
            m.textContent = message;
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
                    const resp = await fetch(`${gatewayApiUrl}?action=checkStatus`, {
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
                            setTimeout(() => {
                                if (window.loadWalletData) window.loadWalletData();
                                if (window.loadTransactions) window.loadTransactions();
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

        // Gateway Event Listeners
        document.getElementById('gwPhoneNumber').addEventListener('blur', e => {
            const phone = e.target.value.trim();
            if (phone && phone !== validatedMsisdn) {
                if (validationTimeout) clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => validateGatewayPhoneNumber(phone), 500);
            }
        });

        document.getElementById('gwPhoneNumber').addEventListener('input', e => {
            const val = e.target.value.trim();
            if (validatedMsisdn && ('+256' + val) !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null;
                customerName = null;
            }
        });

        document.getElementById('gwPhoneNumber').addEventListener('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                validateGatewayPhoneNumber();
            }
        });

        document.getElementById('gwAmount').addEventListener('input', e => {
            const a = parseFloat(e.target.value);
            if (a && a < 500) showGatewayAmountError('Minimum amount is 500 UGX');
            else hideGatewayAmountError();
        });

        // Form validation on input
        document.querySelectorAll('input[required], textarea[required]').forEach(field => {
            field.addEventListener('input', function () {
                if (this.value.trim()) {
                    this.classList.remove('border-red-300', 'bg-red-50');
                }
            });
        });
    });
</script>