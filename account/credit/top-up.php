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
                <button onclick="hidePaymentMethodModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
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

<!-- Mobile Money Payment Modal -->
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
                <button onclick="hideMobileMoneyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="mobileMoneyForm" class="space-y-4">
                <div>
                    <label for="mmPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">Phone Number
                        Used</label>
                    <div class="relative">
                        <div class="absolute left-3 top-3 text-gray-500 font-medium">+256</div>
                        <input type="tel" id="mmPhoneNumber" name="mmPhoneNumber"
                            class="w-full pl-16 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="771234567" maxlength="9" pattern="[0-9]{9}" required>
                    </div>
                </div>

                <div>
                    <label for="mmAmount" class="block text-sm font-semibold text-gray-700 mb-2">Amount Sent
                        (UGX)</label>
                    <input type="number" id="mmAmount" name="mmAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount sent" required>
                </div>

                <div>
                    <label for="mmTransactionId" class="block text-sm font-semibold text-gray-700 mb-2">Transaction
                        ID</label>
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
                    <label for="mmDateTime" class="block text-sm font-semibold text-gray-700 mb-2">Date & Time
                        Sent</label>
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

<!-- Bank Transfer Payment Modal -->
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
                <button onclick="hideBankTransferModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="bankTransferForm" class="space-y-4">
                <div>
                    <label for="btAmount" class="block text-sm font-semibold text-gray-700 mb-2">Amount Deposited
                        (UGX)</label>
                    <input type="number" id="btAmount" name="btAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount deposited" required>
                </div>

                <div>
                    <label for="btReference" class="block text-sm font-semibold text-gray-700 mb-2">Bank
                        Reference/Receipt Number</label>
                    <input type="text" id="btReference" name="btReference"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter reference number" required>
                </div>

                <div>
                    <label for="btDepositorName" class="block text-sm font-semibold text-gray-700 mb-2">Depositor
                        Name</label>
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
                    <label for="btDateTime" class="block text-sm font-semibold text-gray-700 mb-2">Date & Time of
                        Deposit</label>
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

<!-- Gateway Payment Modal -->
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
                <button onclick="hideGatewayPaymentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="gatewayPaymentForm" class="space-y-4">
                <div>
                    <label for="gwPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">Phone
                        Number</label>
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
                    <label for="gwAmount" class="block text-sm font-semibold text-gray-700 mb-2">Amount (UGX)</label>
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

        // Payment Method Modal
        window.showPaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.remove('hidden');
        };
        window.hidePaymentMethodModal = function () {
            document.getElementById('paymentMethodModal').classList.add('hidden');
        };

        window.selectPaymentMethod = function (accountId, type, name, accountNumber, provider) {
            selectedAccount = { id: accountId, type, name, accountNumber, provider };
            hidePaymentMethodModal();
            if (type === 'mobile_money') showMobileMoneyModal();
            else if (type === 'bank') showBankTransferModal();
            else if (type === 'gateway') showGatewayPaymentModal();
            else alert('This payment method is coming soon!');
        };

        // Mobile Money
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
        window.submitMobileMoneyPayment = async function () {
            const fd = new FormData(document.getElementById('mobileMoneyForm'));
            const payload = {
                action: 'logTopup',
                cash_account_id: selectedAccount.id,
                payment_method: 'MOBILE_MONEY',
                amount_total: fd.get('mmAmount'),
                external_reference: fd.get('mmTransactionId'),
                note: fd.get('mmNote'),
                mmPhoneNumber: fd.get('mmPhoneNumber'),
                mmDateTime: fd.get('mmDateTime')
            };
            try {
                const resp = await fetch(topupUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(payload)
                });
                const data = await resp.json();
                if (data.success) {
                    alert('Top-up logged! Transaction ID: ' + data.transaction_id);
                    hideMobileMoneyModal();
                    if (window.loadWalletData) window.loadWalletData();
                    if (window.loadTransactions) window.loadTransactions();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('Top-up error:', e);
                alert('Network error. Please try again.');
            }
        };

        // Bank Transfer
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
        window.submitBankTransferPayment = async function () {
            const fd = new FormData(document.getElementById('bankTransferForm'));
            const payload = {
                action: 'logTopup',
                cash_account_id: selectedAccount.id,
                payment_method: 'BANK',
                amount_total: fd.get('btAmount'),
                external_reference: fd.get('btReference'),
                note: fd.get('btNote'),
                btDepositorName: fd.get('btDepositorName'),
                btDateTime: fd.get('btDateTime')
            };
            try {
                const resp = await fetch(topupUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(payload)
                });
                const data = await resp.json();
                if (data.success) {
                    alert('Top-up logged! Transaction ID: ' + data.transaction_id);
                    hideBankTransferModal();
                    if (window.loadWalletData) window.loadWalletData();
                    if (window.loadTransactions) window.loadTransactions();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (e) {
                console.error('Top-up error:', e);
                alert('Network error. Please try again.');
            }
        };

        // Gateway Payment Modal and Logic (unchanged)
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
                const resp = await fetch(`${gatewayApiUrl}?action=makePayment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ msisdn: validatedMsisdn, amount: amt, description: desc })
                });
                const data = await resp.json();
                if (data.success) {
                    currentPaymentReference = data.internal_reference;
                    showGatewayPaymentStatus('pending', 'Payment Request Sent', 'Please check your phone…');
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
            function formatCurrency(a) { return new Intl.NumberFormat('en-UG', { style: 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(a); }
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
        window.hideTransactionResultModal = function () {
            document.getElementById('transactionResultModal').classList.add('hidden');
        };

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
            if (validatedMsisdn && ('+256' + val) !== validatedMsisdn) {
                document.getElementById('gwCustomerName').classList.add('hidden');
                document.getElementById('gwPhoneError').classList.add('hidden');
                document.getElementById('gwSubmitPaymentBtn').disabled = true;
                validatedMsisdn = null; customerName = null;
            }
        });
        document.getElementById('gwPhoneNumber').addEventListener('keypress', e => {
            if (e.key === 'Enter') { e.preventDefault(); validateGatewayPhoneNumber(); }
        });
        document.getElementById('gwAmount').addEventListener('input', e => {
            const a = parseFloat(e.target.value);
            if (a && a < 500) showGatewayAmountError('Minimum amount is 500 UGX');
            else hideGatewayAmountError();
        });
    });
</script>