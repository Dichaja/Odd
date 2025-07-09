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

<!-- Payment Category Selection Modal -->
<div id="paymentCategoryModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Add Money to Wallet</h3>
                        <p class="text-sm text-gray-500">Choose how you want to send money</p>
                    </div>
                </div>
                <button onclick="hidePaymentCategoryModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Payment Categories -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Mobile Money Card -->
                <div class="payment-category-card border-2 border-gray-200 rounded-xl p-6 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer transform hover:scale-[1.02] text-center"
                    onclick="selectPaymentCategory('mobile_money')">
                    <div class="mb-4">
                        <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/mobile-money-aq5vAN7gjMzVCMAX13RgwGNsqBiHzA.png"
                            alt="Mobile Money" class="w-full h-20 object-contain rounded-lg">
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Send Mobile Money</h4>
                    <p class="text-sm text-gray-500 mb-4">Send money via Airtel Money or MTN MoMo</p>
                    <div class="text-primary">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>

                <!-- Bank Deposits Card -->
                <div class="payment-category-card border-2 border-gray-200 rounded-xl p-6 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer transform hover:scale-[1.02] text-center"
                    onclick="selectPaymentCategory('bank')">
                    <div class="mb-4 flex items-center justify-center">
                        <div
                            class="w-full h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-university text-white text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Bank Deposits</h4>
                    <p class="text-sm text-gray-500 mb-4">Deposit money directly to our bank accounts</p>
                    <div class="text-primary">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>

                <!-- Automated Instant Pay Card -->
                <div class="payment-category-card border-2 border-gray-200 rounded-xl p-6 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer transform hover:scale-[1.02] text-center"
                    onclick="selectPaymentCategory('gateway')">
                    <div class="mb-4">
                        <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/gateway-Af9MBR8GG0SKatA3E6O6Wiztol2kbE.png"
                            alt="Card Payment" class="w-full h-20 object-contain rounded-lg">
                    </div>
                    <h4 class="font-semibold text-gray-900 mb-2">Instant Pay</h4>
                    <p class="text-sm text-gray-500 mb-4">Pay instantly with mobile money or card</p>
                    <div class="text-primary">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Account Selection Modal -->
<div id="accountSelectionModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="accountSelectionBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i id="accountSelectionIcon" class="fas fa-mobile-alt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 id="accountSelectionTitle" class="text-xl font-semibold text-gray-900">Select Account</h3>
                        <p id="accountSelectionSubtitle" class="text-sm text-gray-500">Choose your preferred account</p>
                    </div>
                </div>
                <button onclick="hideAccountSelectionModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Account List -->
        <div class="overflow-y-auto max-h-[calc(90vh-200px)] p-6">
            <div class="grid grid-cols-1 gap-4" id="accountSelectionCards">
                <!-- Accounts will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Gateway Method Selection Modal -->
<div id="gatewayMethodModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="gatewayMethodBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Choose Payment Method</h3>
                        <p class="text-sm text-gray-500">Select how you want to pay</p>
                    </div>
                </div>
                <button onclick="hideGatewayMethodModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="p-6">
            <div class="space-y-4">
                <!-- Mobile Money Gateway -->
                <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer"
                    onclick="selectGatewayMethod('mobile_money')">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-gray-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">Mobile Money</h4>
                            <p class="text-sm text-gray-500">Pay with your mobile money account</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>

                <!-- Card Payment -->
                <div class="border-2 border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer"
                    onclick="selectGatewayMethod('card')">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-credit-card text-gray-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">Card Payment</h4>
                            <p class="text-sm text-gray-500">Pay with Visa or Mastercard</p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
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
                    <button onclick="mobileMoneyBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Account Number</h3>
                        <p class="text-sm text-gray-500" id="mobileMoneyAccountName"></p>
                    </div>
                </div>
                <button onclick="hideMobileMoneyModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Form -->
        <div class="overflow-y-auto max-h-[calc(100vh-300px)] p-6">

            <!-- Instructions -->
            <div class="px-6 py-4 mb-4 bg-blue-50 border-b border-blue-100">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info text-blue-600 text-xs"></i>
                    </div>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Instructions:</p>
                        <ol class="list-decimal list-inside space-y-1 text-xs">
                            <li>Send money to the account number above</li>
                            <li>Fill in the details below after sending</li>
                            <li>We'll verify and credit your account within the hour</li>
                        </ol>
                    </div>
                </div>
            </div>

            <form id="mobileMoneyForm" class="space-y-4">
                <div>
                    <label for="mmPhoneNumber" class="block text-sm font-semibold text-gray-700 mb-2">
                        Enter phone number used <span class="text-red-500">*</span>
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
                        Enter amount sent (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="mmAmount" name="mmAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount sent" required>
                </div>

                <div>
                    <label for="mmTransactionId" class="block text-sm font-semibold text-gray-700 mb-2">
                        Enter transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="mmTransactionId" name="mmTransactionId"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter transaction ID" required>
                </div>

                <div>
                    <label for="mmNote" class="block text-sm font-semibold text-gray-700 mb-2">Add a
                        note/Message/reason</label>
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

        <!-- Form -->
        <div class="overflow-y-auto max-h-[calc(100vh-300px)] p-6">

            <!-- Instructions -->
            <div class="px-6 py-4 mb-4 bg-green-50 border-b border-green-100">
                <div class="flex items-start gap-3">
                    <div
                        class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info text-green-600 text-xs"></i>
                    </div>
                    <div class="text-sm text-green-800">
                        <p class="font-medium mb-1">Instructions:</p>
                        <ol class="list-decimal list-inside space-y-1 text-xs">
                            <li>Deposit money to the bank account above</li>
                            <li>Keep your deposit slip/receipt</li>
                            <li>Fill in the details below</li>
                            <li>We'll verify and credit your account within the hour</li>
                        </ol>
                    </div>
                </div>
            </div>

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
                    <label for="btNote" class="block text-sm font-semibold text-gray-700 mb-2">Add a
                        note/Message/reason</label>
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

<!-- Gateway Payment Modal (Mobile Money) -->
<div id="gatewayPaymentModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="gatewayPaymentBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-mobile-alt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Mobile Money Payment</h3>
                        <p class="text-sm text-gray-500" id="gatewayAccountName"></p>
                    </div>
                </div>
                <button onclick="hideGatewayPaymentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Form -->
        <div class="overflow-y-auto max-h-[calc(100vh-300px)] p-6">

            <!-- Instructions -->
            <div class="px-6 py-4 mb-4 bg-purple-50 border-b border-purple-100">
                <div class="flex items-start gap-3">
                    <div
                        class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info text-purple-600 text-xs"></i>
                    </div>
                    <div class="text-sm text-purple-800">
                        <p class="font-medium mb-1">Instructions:</p>
                        <ol class="list-decimal list-inside space-y-1 text-xs">
                            <li>Enter your phone number and amount</li>
                            <li>You'll receive a payment prompt on your phone</li>
                            <li>Enter your mobile money PIN to complete</li>
                            <li>Your account will be credited instantly</li>
                        </ol>
                    </div>
                </div>
            </div>

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

<!-- Card Payment Modal -->
<div id="cardPaymentModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button onclick="cardPaymentBack()"
                        class="shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-credit-card text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Card Payment</h3>
                        <p class="text-sm text-gray-500">Pay with Visa or Mastercard</p>
                    </div>
                </div>
                <button onclick="hideCardPaymentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Form -->
        <div class="overflow-y-auto max-h-[calc(100vh-300px)] p-6">

            <!-- Instructions -->
            <div class="px-6 py-4 mb-4 bg-indigo-50 border-b border-indigo-100">
                <div class="flex items-start gap-3">
                    <div
                        class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-info text-indigo-600 text-xs"></i>
                    </div>
                    <div class="text-sm text-indigo-800">
                        <p class="font-medium mb-1">Instructions:</p>
                        <ol class="list-decimal list-inside space-y-1 text-xs">
                            <li>Enter the amount you want to add</li>
                            <li>Click "Proceed to Payment"</li>
                            <li>You'll be redirected to a secure payment page</li>
                            <li>Your account will be credited upon successful payment</li>
                        </ol>
                    </div>
                </div>
            </div>

            <form id="cardPaymentForm" class="space-y-4">
                <div>
                    <label for="cardAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Amount (UGX) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="cardAmount" name="cardAmount" min="500" step="100"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter amount (minimum 500)" required>
                    <div id="cardAmountError" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <i class="fas fa-shield-alt text-green-600"></i>
                        <span class="text-sm font-medium text-gray-900">Secure Payment</span>
                    </div>
                    <p class="text-xs text-gray-600">Your payment will be processed securely through our payment
                        gateway. We do not store your card details.</p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-100">
            <div class="flex gap-3">
                <button type="button" onclick="hideCardPaymentModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button type="button" id="cardSubmitPaymentBtn" onclick="confirmCardPayment()" disabled
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    Proceed to Payment
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
        const groupedAccounts = <?= json_encode($groupedAccounts) ?>;
        const walletId = <?= json_encode($walletId) ?>;

        let selectedAccount = null;
        let selectedCategory = null;
        let selectedGatewayMethod = null;
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

        // Payment Category Modal
        window.showPaymentCategoryModal = function () {
            showModal('paymentCategoryModal');
        };

        // Backward compatibility - add this function
        window.showPaymentMethodModal = function () {
            showPaymentCategoryModal();
        };

        window.hidePaymentCategoryModal = function () {
            hideModal('paymentCategoryModal');
        };

        window.selectPaymentCategory = function (category) {
            selectedCategory = category;
            hideModal('paymentCategoryModal');

            setTimeout(() => {
                if (category === 'gateway') {
                    showGatewayMethodModal();
                } else {
                    showAccountSelectionModal(category);
                }
            }, 300);
        };

        // Account Selection Modal
        function showAccountSelectionModal(category) {
            const accounts = groupedAccounts[category] || [];
            const modal = document.getElementById('accountSelectionModal');
            const title = document.getElementById('accountSelectionTitle');
            const subtitle = document.getElementById('accountSelectionSubtitle');
            const icon = document.getElementById('accountSelectionIcon');
            const cardsContainer = document.getElementById('accountSelectionCards');

            // Update header based on category
            switch (category) {
                case 'mobile_money':
                    title.textContent = 'Select Mobile Money Account';
                    subtitle.textContent = 'Choose your preferred mobile money provider';
                    icon.className = 'fas fa-mobile-alt text-primary text-xl';
                    break;
                case 'bank':
                    title.textContent = 'Select Bank Account';
                    subtitle.textContent = 'Choose your preferred bank account';
                    icon.className = 'fas fa-university text-primary text-xl';
                    break;
                case 'gateway':
                    title.textContent = 'Select Account';
                    subtitle.textContent = 'Choose your preferred account';
                    icon.className = 'fas fa-credit-card text-primary text-xl';
            }

            // Populate accounts
            cardsContainer.innerHTML = '';
            accounts.forEach(account => {
                const card = document.createElement('div');
                card.className = 'border-2 border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 cursor-pointer transform hover:scale-[1.02]';
                card.onclick = () => selectAccount(account);

                let iconClass = 'fas fa-university';
                switch (account.type) {
                    case 'mobile_money':
                        iconClass = 'fas fa-mobile-alt';
                        break;
                    case 'bank':
                        iconClass = 'fas fa-university';
                        break;
                    case 'gateway':
                        iconClass = 'fas fa-credit-card';
                        break;
                }

                card.innerHTML = `
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                            <i class="${iconClass} text-gray-600"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">${account.name}</h4>
                            <p class="text-sm text-gray-500">${account.account_number}</p>
                            <p class="text-xs text-gray-400 capitalize">
                                ${account.type.replace('_', ' ')}
                            </p>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                `;

                cardsContainer.appendChild(card);
            });

            showModal('accountSelectionModal');
        }

        window.hideAccountSelectionModal = function () {
            hideModal('accountSelectionModal');
        };

        window.accountSelectionBack = function () {
            hideModal('accountSelectionModal');
            setTimeout(() => {
                showModal('paymentCategoryModal');
            }, 300);
        };

        function selectAccount(account) {
            selectedAccount = account;
            hideModal('accountSelectionModal');

            setTimeout(() => {
                if (account.type === 'mobile_money') {
                    showMobileMoneyModal();
                } else if (account.type === 'bank') {
                    showBankTransferModal();
                }
            }, 300);
        }

        // Gateway Method Modal
        window.showGatewayMethodModal = function () {
            showModal('gatewayMethodModal');
        };

        window.hideGatewayMethodModal = function () {
            hideModal('gatewayMethodModal');
        };

        window.gatewayMethodBack = function () {
            hideModal('gatewayMethodModal');
            setTimeout(() => {
                showModal('paymentCategoryModal');
            }, 300);
        };

        window.selectGatewayMethod = function (method) {
            selectedGatewayMethod = method;
            hideModal('gatewayMethodModal');

            setTimeout(() => {
                if (method === 'mobile_money') {
                    // Use the first gateway account for mobile money
                    const gatewayAccounts = groupedAccounts['gateway'] || [];
                    if (gatewayAccounts.length > 0) {
                        selectedAccount = gatewayAccounts[0];
                        showGatewayPaymentModal();
                    }
                } else if (method === 'card') {
                    showCardPaymentModal();
                }
            }, 300);
        };

        // Mobile Money Modal
        window.showMobileMoneyModal = function () {
            document.getElementById('mobileMoneyAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.account_number}`;
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
                showAccountSelectionModal(selectedCategory);
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
                    wallet_id: walletId,
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

        // Bank Transfer Modal
        window.showBankTransferModal = function () {
            document.getElementById('bankAccountName').textContent =
                `${selectedAccount.name} - ${selectedAccount.account_number}`;
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
                showAccountSelectionModal(selectedCategory);
            }, 300);
        };

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
                `Amount: UGX ${new Intl.NumberFormat().format(fd.get('btAmount'))}<br>
                Reference: ${fd.get('btReference')}<br>
                Depositor: ${fd.get('btDepositorName')}<br>
                Account: ${selectedAccount.name}`
            );
        };

        // Gateway Payment Modal (Mobile Money)
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
                showGatewayMethodModal();
            }, 300);
        };

        // Card Payment Modal
        window.showCardPaymentModal = function () {
            showModal('cardPaymentModal');
            resetCardForm();
        };

        window.hideCardPaymentModal = function () {
            hideModal('cardPaymentModal');
            setTimeout(() => {
                resetCardForm();
            }, 300);
        };

        window.cardPaymentBack = function () {
            hideModal('cardPaymentModal');
            setTimeout(() => {
                showGatewayMethodModal();
            }, 300);
        };

        window.confirmCardPayment = function () {
            if (!validateForm('cardPaymentForm')) return;

            const amount = parseFloat(document.getElementById('cardAmount').value);
            if (!amount || amount < 500) {
                showCardAmountError('Please enter a valid amount (minimum 500 UGX)');
                return;
            }

            // Redirect to secure payment link
            const paymentUrl = `${BASE_URL}payment/card?amount=${amount}&description=Zzimba+wallet+top-up`;
            window.open(paymentUrl, '_blank');

            hideCardPaymentModal();
            showTransactionResultModal('success', {
                title: 'Redirected to Payment',
                message: 'You have been redirected to our secure payment page. Complete the payment to add money to your wallet.',
                amount: amount,
                currency: 'UGX'
            });
        };

        function resetCardForm() {
            document.getElementById('cardPaymentForm').reset();
            document.getElementById('cardAmountError').classList.add('hidden');
            document.getElementById('cardSubmitPaymentBtn').disabled = true;
        }

        function showCardAmountError(msg) {
            const err = document.getElementById('cardAmountError');
            err.textContent = msg;
            err.classList.remove('hidden');
            document.getElementById('cardSubmitPaymentBtn').disabled = true;
        }

        function hideCardAmountError() {
            document.getElementById('cardAmountError').classList.add('hidden');
            checkCardFormValidity();
        }

        function checkCardFormValidity() {
            const amt = parseFloat(document.getElementById('cardAmount').value);
            const btn = document.getElementById('cardSubmitPaymentBtn');
            btn.disabled = !(amt >= 500);
        }

        // Card amount validation
        document.getElementById('cardAmount').addEventListener('input', function (e) {
            const amount = parseFloat(e.target.value);
            if (amount && amount < 500) {
                showCardAmountError('Minimum amount is 500 UGX');
            } else {
                hideCardAmountError();
            }
        });

        // Rest of the existing JavaScript functions remain the same...
        // (Gateway functions, confirmation modal, transaction result modal, etc.)

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
                    wallet_id: walletId,
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

        // Submit functions
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
                        message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.',
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
                        message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.',
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
                                    message: 'Your Zzimba credit account will be updated upon confirmation of funds within the hour.',
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