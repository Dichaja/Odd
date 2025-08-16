<div id="sendCreditModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
    <div id="sendCreditModalContent"
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <button id="sendCreditBackBtn" onclick="sendCreditBack()"
                        class="hidden w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10 grid place-items-center hover:bg-gray-200 dark:hover:bg-white/20">
                        <i class="fas fa-chevron-left text-gray-600 dark:text-white/80"></i>
                    </button>
                    <div>
                        <h3 id="sendCreditTitle" class="text-lg font-semibold text-secondary dark:text-white">Send
                            Credit</h3>
                        <p id="sendCreditSubtitle" class="text-sm text-gray-text dark:text-white/70">Select destination
                            wallet type</p>
                    </div>
                </div>
                <button onclick="hideSendCreditModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div id="sendCreditStep1" class="transition-all duration-300 transform">
                <div class="grid grid-cols-1 gap-3">
                    <div class="selector-item" onclick="selectSendDestination('vendor')">
                        <div>
                            <div class="selector-title text-secondary dark:text-white">Vendor Wallet</div>
                            <div class="selector-sub text-gray-text dark:text-white/70">Send to a business/vendor wallet
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                    <div class="selector-item" onclick="selectSendDestination('user')">
                        <div>
                            <div class="selector-title text-secondary dark:text-white">User Wallet</div>
                            <div class="selector-sub text-gray-text dark:text-white/70">Send to a personal/user wallet
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div id="sendCreditSearchStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-secondary dark:text-white mb-2">Search By</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="id" class="sr-only peer" checked
                                onchange="clearSearchInput()">
                            <div
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-secondary dark:text-white transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center justify-center">
                                Account No.
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="name" class="sr-only peer"
                                onchange="clearSearchInput()">
                            <div
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-secondary dark:text-white transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center justify-center">
                                Name
                            </div>
                        </label>
                    </div>
                </div>

                <form id="searchForm" onsubmit="handleSearchSubmit(event)" class="grid gap-4">
                    <div>
                        <label id="sendCreditSearchLabel" for="sendCreditSearchInput"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2"></label>
                        <input id="sendCreditSearchInput" type="text" autocomplete="off" class="form-input"
                            placeholder="Enter search term..." required>
                        <div id="searchInputError" class="hidden mt-1 text-sm text-red-600"></div>
                    </div>
                    <button id="searchWalletBtn" type="submit"
                        class="w-full px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Search
                        Wallet</button>
                </form>
            </div>

            <div id="sendCreditMultipleResultsStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Multiple Wallets Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Select the correct wallet from the list</p>
                </div>
                <div id="multipleResultsList" class="space-y-3 mb-5 max-h-64 overflow-y-auto"></div>
                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Search
                        Again</button>
                </div>
            </div>

            <div id="sendCreditNotFoundStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Wallet Not Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">No wallet matches your search criteria</p>
                </div>
                <div id="searchDetails"
                    class="bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 mb-5 text-red-800 dark:text-red-300">
                </div>
                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Search
                        Again</button>
                    <button onclick="changeWalletType()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Change
                        Type</button>
                </div>
            </div>

            <div id="sendCreditConfirmStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Wallet Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Please confirm this is the correct wallet</p>
                </div>
                <div id="walletDetails"
                    class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4 mb-5">
                </div>
                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Search
                        Again</button>
                    <button onclick="proceedToAmount()"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Confirm
                        Wallet</button>
                </div>
            </div>

            <div id="sendCreditAmountStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <form id="amountForm" onsubmit="handleAmountSubmit(event)" class="grid gap-4">
                    <div>
                        <label for="sendCreditAmount"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Enter Amount to
                            Send</label>
                        <div class="relative">
                            <input id="sendCreditAmount" type="number" min="500" step="1" autocomplete="off"
                                class="form-input pr-16 text-lg font-semibold" placeholder="500" required>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">UGX</div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-white/60 mt-1">Minimum amount: 500 UGX</p>
                        <div id="amountError" class="hidden mt-1 text-sm text-red-600"></div>
                    </div>
                    <div id="selectedWalletSummary"
                        class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4 mb-2">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="sendCreditBack()"
                            class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Back</button>
                        <button id="sendCreditBtn" type="submit"
                            class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Continue</button>
                    </div>
                </form>
            </div>

            <div id="sendCreditBalanceCheckStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <div id="balanceCheckIcon"
                        class="w-14 h-14 bg-blue-100 rounded-full grid place-items-center mx-auto mb-3">
                        <i class="fas fa-spinner animate-spin text-blue-600 text-lg"></i>
                    </div>
                    <h4 id="balanceCheckTitle" class="text-base font-semibold text-secondary dark:text-white mb-1">
                        Checking Balance</h4>
                    <p id="balanceCheckSubtitle" class="text-sm text-gray-text dark:text-white/70">Validating your
                        wallet balance...</p>
                </div>
                <div id="balanceCheckDetails" class="rounded-xl p-4 mb-5"></div>
                <div id="balanceCheckActions" class="flex gap-3"></div>
            </div>

            <div id="sendCreditInsufficientBalanceStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Insufficient Balance</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">You don't have enough balance for this transfer
                    </p>
                </div>
                <div id="insufficientBalanceDetails"
                    class="bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 mb-5">
                </div>
                <div class="flex gap-3">
                    <button onclick="changeTransferAmount()"
                        class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-medium">Change
                        Amount</button>
                    <button onclick="topUpWallet()"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Top
                        Up Wallet</button>
                </div>
            </div>

            <div id="sendCreditPasswordStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <form id="passwordForm" onsubmit="handlePasswordSubmit(event)" class="grid gap-4">
                    <div class="bg-blue-50 dark:bg-white/5 border border-blue-200 dark:border-white/10 rounded-xl p-4">
                        <div class="text-sm">
                            <p class="font-medium text-blue-900 dark:text-white">Logged in as</p>
                            <p id="currentUsername" class="text-blue-700 dark:text-white/80">
                                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Unknown') ?></p>
                        </div>
                    </div>
                    <div>
                        <label for="confirmPassword"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Enter Your
                            Password</label>
                        <div class="relative">
                            <input id="confirmPassword" type="password" autocomplete="current-password"
                                class="form-input pr-10" placeholder="Enter your password..." required>
                            <button type="button" onclick="togglePasswordVisibility()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i id="passwordToggleIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="passwordError" class="hidden mt-1 text-sm text-red-600"></div>
                        <div id="attemptsWarning" class="hidden mt-1 text-sm text-orange-600"></div>
                    </div>
                    <div id="passwordTransferSummary"
                        class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="sendCreditBack()"
                            class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Back</button>
                        <button id="verifyPasswordBtn" type="submit"
                            class="flex-1 px-4 py-2.5 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 font-medium">Verify
                            Password</button>
                    </div>
                </form>
            </div>

            <div id="sendCreditConfirmationStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Confirm Transfer</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Please review the details before proceeding</p>
                </div>
                <div id="confirmationSummaryDetails"
                    class="bg-yellow-50 dark:bg-white/5 border border-yellow-200 dark:border-white/10 rounded-xl p-4 mb-5">
                </div>
                <div class="flex gap-3">
                    <button onclick="cancelConfirmation()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Cancel</button>
                    <button id="confirmSendBtn" onclick="confirmSendCredit()"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Confirm
                        Send</button>
                </div>
            </div>

            <div id="sendCreditResponseStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <div id="responseIcon" class="w-16 h-16 rounded-full grid place-items-center mx-auto mb-3"></div>
                    <h4 id="responseTitle" class="text-base font-semibold text-secondary dark:text-white mb-1"></h4>
                    <p id="responseSubtitle" class="text-sm text-gray-text dark:text-white/70"></p>
                </div>
                <div id="responseDetails" class="rounded-xl p-4 mb-5"></div>
                <div id="responseActions" class="flex gap-3"></div>
                <div id="autoCloseCountdown" class="hidden text-center mt-2">
                    <p class="text-sm text-gray-500 dark:text-white/60">This window will close automatically in <span
                            id="countdownTimer" class="font-semibold">30</span> seconds</p>
                </div>
            </div>

            <div id="sendCreditBlockedStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Access Blocked</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Too many failed password attempts</p>
                </div>
                <div
                    class="bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 mb-5 text-center">
                    <p class="text-red-800 dark:text-red-300 font-medium mb-1">Transfer Feature Temporarily Blocked</p>
                    <p class="text-red-700 dark:text-red-300 text-sm mb-1">You have exceeded the maximum number of
                        password verification attempts (3).</p>
                    <p class="text-red-600 dark:text-red-300 text-sm">Please contact the administrator to restore
                        access.</p>
                </div>
                <div class="flex justify-center">
                    <button onclick="hideSendCreditModal()"
                        class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
        cursor: pointer
    }

    .selector-item:hover {
        border-color: rgb(217 43 19 / .5);
        background: rgb(217 43 19 / .04)
    }

    .selector-title {
        font-weight: 600
    }

    .selector-sub {
        font-size: .75rem;
        opacity: .7;
        margin-top: .125rem
    }

    .dark .selector-item {
        background: transparent;
        border-color: rgba(255, 255, 255, .12)
    }

    .dark .selector-item:hover {
        background: rgba(255, 255, 255, .06);
        border-color: rgba(255, 255, 255, .25)
    }

    .form-input {
        width: 100%;
        padding: .625rem .75rem;
        font-size: .875rem;
        border: 1px solid rgb(209 213 219);
        border-radius: .75rem;
        background: white;
        color: rgb(17 24 39);
        line-height: 1.25rem
    }

    .form-input:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgb(217 43 19 / .15);
        border-color: rgb(217 43 19)
    }

    .dark .form-input {
        background: transparent;
        color: white;
        border-color: rgba(255, 255, 255, .2)
    }

    .dark .form-input::placeholder {
        color: rgba(255, 255, 255, .6)
    }

    .slide-in-right {
        animation: slideInRight .3s ease-out forwards
    }

    .slide-out-left {
        animation: slideOutLeft .3s ease-out forwards
    }

    .slide-in-left {
        animation: slideInLeft .3s ease-out forwards
    }

    .slide-out-right {
        animation: slideOutRight .3s ease-out forwards
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0
        }

        to {
            transform: translateX(0);
            opacity: 1
        }
    }

    @keyframes slideOutLeft {
        from {
            transform: translateX(0);
            opacity: 1
        }

        to {
            transform: translateX(-100%);
            opacity: 0
        }
    }

    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
            opacity: 0
        }

        to {
            transform: translateX(0);
            opacity: 1
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1
        }

        to {
            transform: translateX(100%);
            opacity: 0
        }
    }

    .modal-show {
        transform: scale(1) !important;
        opacity: 1 !important
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0)
        }

        25% {
            transform: translateX(-5px)
        }

        75% {
            transform: translateX(5px)
        }
    }

    .animate-shake {
        animation: shake .5s ease-in-out
    }
</style>

<script>
    const sendCreditApiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageSendCredit.php') ?>;
    let currentStep = 1, maxStep = 1, selectedWallet = null, lastSearchParams = null, searchResults = [], pendingTransfer = null, autoCloseTimer = null, currentBalance = null, securityToken = null, transferFeeSettings = null, calculatedFee = 0;
    const STORAGE_KEY = 'sendCredit_attempts', MAX_ATTEMPTS = 3;

    function getPasswordAttempts() { const s = localStorage.getItem(STORAGE_KEY); return s ? JSON.parse(s) : { count: 0, timestamp: Date.now() } }
    function incrementPasswordAttempts() { const a = getPasswordAttempts(); a.count += 1; a.timestamp = Date.now(); localStorage.setItem(STORAGE_KEY, JSON.stringify(a)); return a }
    function resetPasswordAttempts() { localStorage.removeItem(STORAGE_KEY) }
    function isUserBlocked() { return getPasswordAttempts().count >= MAX_ATTEMPTS }

    async function fetchTransferFeeSettings() { try { const fd = new FormData(); fd.append('action', 'getTransferFeeSettings'); const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd }); const d = await r.json(); if (d.success) { transferFeeSettings = d.feeSettings } } catch (_) { } }
    function calculateTransferFee(amount) { if (!transferFeeSettings) return 0; if (transferFeeSettings.setting_type === 'flat') return parseFloat(transferFeeSettings.setting_value); if (transferFeeSettings.setting_type === 'percentage') return (parseFloat(amount) * parseFloat(transferFeeSettings.setting_value)) / 100; return 0 }

    window.showSendCreditModal = async function () { if (isUserBlocked()) { showBlockedUserStep(); return } await fetchTransferFeeSettings(); const m = document.getElementById('sendCreditModal'); const c = document.getElementById('sendCreditModalContent'); m.classList.remove('hidden'); setTimeout(() => { c.classList.add('modal-show') }, 10); resetToStep1() }
    function showBlockedUserStep() { const m = document.getElementById('sendCreditModal'); const c = document.getElementById('sendCreditModalContent'); m.classList.remove('hidden'); setTimeout(() => { c.classList.add('modal-show') }, 10); document.querySelectorAll('[id^="sendCredit"][id$="Step"]').forEach(s => s.classList.add('hidden')); document.getElementById('sendCreditBlockedStep').classList.remove('hidden', 'translate-x-full', 'opacity-0'); document.getElementById('sendCreditBackBtn').classList.add('hidden'); document.getElementById('sendCreditTitle').textContent = 'Access Blocked'; document.getElementById('sendCreditSubtitle').textContent = 'Contact administrator for assistance' }
    window.hideSendCreditModal = function () { const m = document.getElementById('sendCreditModal'); const c = document.getElementById('sendCreditModalContent'); if (autoCloseTimer) { clearInterval(autoCloseTimer); autoCloseTimer = null } c.classList.remove('modal-show'); setTimeout(() => { m.classList.add('hidden'); resetToStep1(); location.reload() }, 300) }

    function resetToStep1() {
        currentStep = 1; maxStep = 1; selectedWallet = null; lastSearchParams = null; searchResults = []; pendingTransfer = null; currentBalance = null; securityToken = null; transferFeeSettings = transferFeeSettings; calculatedFee = 0; if (autoCloseTimer) { clearInterval(autoCloseTimer); autoCloseTimer = null }
        document.getElementById('sendCreditStep1').classList.remove('hidden', 'slide-out-left');
        ['sendCreditSearchStep', 'sendCreditMultipleResultsStep', 'sendCreditNotFoundStep', 'sendCreditConfirmStep', 'sendCreditAmountStep', 'sendCreditBalanceCheckStep', 'sendCreditInsufficientBalanceStep', 'sendCreditPasswordStep', 'sendCreditConfirmationStep', 'sendCreditResponseStep', 'sendCreditBlockedStep'].forEach(id => document.getElementById(id).classList.add('hidden'));
        document.getElementById('sendCreditTitle').textContent = 'Send Credit'; document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type'; document.getElementById('sendCreditBackBtn').classList.add('hidden');
        clearAllForms(); document.querySelector('input[name="searchType"][value="id"]').checked = true; resetSearchButton(); resetSendButton(); resetPasswordButton(); clearErrorMessages()
    }

    function clearAllForms() { document.getElementById('sendCreditSearchInput').value = ''; document.getElementById('sendCreditAmount').value = ''; document.getElementById('confirmPassword').value = '' }
    function clearSearchInput() { document.getElementById('sendCreditSearchInput').value = ''; clearErrorMessages() }
    function clearErrorMessages() { ['searchInputError', 'amountError', 'passwordError', 'attemptsWarning'].forEach(id => document.getElementById(id).classList.add('hidden'));['sendCreditSearchInput', 'sendCreditAmount', 'confirmPassword'].forEach(id => document.getElementById(id).classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200')) }
    function resetSearchButton() { const b = document.getElementById('searchWalletBtn'); b.textContent = 'Search Wallet'; b.disabled = false }
    function resetSendButton() { const b = document.getElementById('sendCreditBtn'); if (b) { b.textContent = 'Continue'; b.disabled = false } }
    function resetPasswordButton() { const b = document.getElementById('verifyPasswordBtn'); if (b) { b.textContent = 'Verify Password'; b.disabled = false } }

    window.togglePasswordVisibility = function () { const i = document.getElementById('confirmPassword'); const t = document.getElementById('passwordToggleIcon'); if (i.type === 'password') { i.type = 'text'; t.classList.remove('fa-eye'); t.classList.add('fa-eye-slash') } else { i.type = 'password'; t.classList.remove('fa-eye-slash'); t.classList.add('fa-eye') } }

    window.selectSendDestination = function (type) { window.sendCreditType = type; maxStep = Math.max(maxStep, 2); clearAllForms(); clearErrorMessages(); const s1 = document.getElementById('sendCreditStep1'); s1.classList.add('slide-out-left'); setTimeout(() => { s1.classList.add('hidden'); const t = type === 'vendor' ? 'Vendor' : 'User'; document.getElementById('sendCreditTitle').textContent = `Send to ${t}`; document.getElementById('sendCreditSubtitle').textContent = `Search for ${t.toLowerCase()} wallet`; updateSearchLabel(); document.getElementById('sendCreditBackBtn').classList.remove('hidden'); const st = document.getElementById('sendCreditSearchStep'); st.classList.remove('hidden', 'translate-x-full', 'opacity-0'); st.classList.add('slide-in-right'); currentStep = 2; setTimeout(() => { document.getElementById('sendCreditSearchInput').focus() }, 400) }, 300) }

    window.sendCreditBack = function () { if (currentStep === 9) { hideSendCreditModal() } else if (currentStep === 8) { animateToStep('sendCreditConfirmationStep', 'sendCreditPasswordStep', 7) } else if (currentStep === 7) { animateToStep('sendCreditPasswordStep', 'sendCreditBalanceCheckStep', 6) } else if (currentStep === 6) { animateToStep('sendCreditBalanceCheckStep', 'sendCreditAmountStep', 5) } else if (currentStep === 5) { animateToStep('sendCreditAmountStep', 'sendCreditConfirmStep', 4) } else if (currentStep === 4) { clearAllForms(); clearErrorMessages(); resetSearchButton(); animateToStep('sendCreditConfirmStep', 'sendCreditSearchStep', 2) } else if (currentStep === 3) { clearAllForms(); clearErrorMessages(); resetSearchButton(); animateToStep('sendCreditNotFoundStep', 'sendCreditSearchStep', 2) } else if (currentStep === 2) { clearAllForms(); clearErrorMessages(); resetSearchButton(); const st = document.getElementById('sendCreditSearchStep'); st.classList.add('slide-out-right'); setTimeout(() => { st.classList.add('hidden', 'translate-x-full', 'opacity-0'); st.classList.remove('slide-out-right'); document.getElementById('sendCreditTitle').textContent = 'Send Credit'; document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type'; document.getElementById('sendCreditBackBtn').classList.add('hidden'); const s1 = document.getElementById('sendCreditStep1'); s1.classList.remove('hidden', 'slide-out-left'); s1.classList.add('slide-in-left'); currentStep = 1 }, 300) } }

    function animateToStep(fromId, toId, num) { const f = document.getElementById(fromId); f.classList.add('slide-out-right'); setTimeout(() => { f.classList.add('hidden', 'translate-x-full', 'opacity-0'); f.classList.remove('slide-out-right'); const t = document.getElementById(toId); t.classList.remove('hidden', 'translate-x-full', 'opacity-0'); t.classList.add('slide-in-left'); currentStep = num; if (num === 2) setTimeout(() => document.getElementById('sendCreditSearchInput').focus(), 400); else if (num === 5) setTimeout(() => document.getElementById('sendCreditAmount').focus(), 400); else if (num === 7) setTimeout(() => document.getElementById('confirmPassword').focus(), 400) }, 300) }

    function updateSearchLabel() { const st = document.querySelector('input[name="searchType"]:checked').value; const wt = window.sendCreditType === 'vendor' ? 'Vendor' : 'User'; const txt = st === 'id' ? 'Account No.' : 'Name'; document.getElementById('sendCreditSearchLabel').textContent = `Search ${wt} Wallet by ${txt}` }
    document.addEventListener('change', e => { if (e.target.name === 'searchType') { updateSearchLabel(); const inp = document.getElementById('sendCreditSearchInput'); inp.placeholder = e.target.value === 'id' ? 'Enter account number...' : 'Enter wallet name...' } })

    function handleSearchSubmit(e) { e.preventDefault(); performSendCreditSearch() }
    function handleAmountSubmit(e) { e.preventDefault(); checkBalanceAndProceed() }
    function handlePasswordSubmit(e) { e.preventDefault(); verifyPassword() }

    window.performSendCreditSearch = async function () {
        const q = document.getElementById('sendCreditSearchInput').value.trim();
        const st = document.querySelector('input[name="searchType"]:checked').value;
        clearErrorMessages();
        if (!q) { showInputError('sendCreditSearchInput', 'searchInputError', 'Please enter a search term'); return }
        lastSearchParams = { query: q, searchType: st, walletType: window.sendCreditType };
        const btn = document.getElementById('searchWalletBtn'); btn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Searching...'; btn.disabled = true;
        try {
            await new Promise(r => setTimeout(r, 600));
            const fd = new FormData(); fd.append('action', 'searchWallet'); fd.append('type', window.sendCreditType); fd.append('searchType', st); fd.append('searchValue', q);
            const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd }); const d = await r.json();
            if (d.success) {
                if (Array.isArray(d.wallets) && d.wallets.length > 1) { searchResults = d.wallets; showMultipleResults(d.wallets) }
                else if (Array.isArray(d.wallets) && d.wallets.length === 1) { selectedWallet = d.wallets[0]; showWalletConfirmation(d.wallets[0]) }
                else if (d.wallet) { selectedWallet = d.wallet; showWalletConfirmation(d.wallet) }
                else { showWalletNotFound() }
            } else { showWalletNotFound() }
        } catch (_) { showInputError('sendCreditSearchInput', 'searchInputError', 'Error searching for wallet. Please try again.') }
        finally { resetSearchButton() }
    };

    function showInputError(inputId, errorId, msg) { const i = document.getElementById(inputId), e = document.getElementById(errorId); i.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200', 'animate-shake'); e.textContent = msg; e.classList.remove('hidden'); setTimeout(() => i.classList.remove('animate-shake'), 500); i.focus() }

    function showMultipleResults(ws) {
        const list = document.getElementById('multipleResultsList'); list.innerHTML = '';
        ws.forEach(w => {
            const row = document.createElement('div');
            row.className = 'selector-item';
            row.onclick = () => selectWalletFromResults(w);
            row.innerHTML = `<div><div class="selector-title text-secondary dark:text-white">${w.wallet_name}</div><div class="selector-sub text-gray-text dark:text-white/70">Account No: ${w.wallet_number}</div></div><i class="fas fa-chevron-right text-gray-400"></i>`;
            list.appendChild(row);
        });
        document.getElementById('sendCreditTitle').textContent = 'Multiple Results';
        document.getElementById('sendCreditSubtitle').textContent = `Found ${ws.length} matching wallets`;
        const s = document.getElementById('sendCreditSearchStep'); s.classList.add('slide-out-left');
        setTimeout(() => { s.classList.add('hidden', 'translate-x-full', 'opacity-0'); s.classList.remove('slide-out-left'); const m = document.getElementById('sendCreditMultipleResultsStep'); m.classList.remove('hidden', 'translate-x-full', 'opacity-0'); m.classList.add('slide-in-right'); currentStep = 3 }, 300)
    }

    function selectWalletFromResults(w) { selectedWallet = w; showWalletConfirmation(w) }

    function showWalletNotFound() {
        const sd = document.getElementById('searchDetails'); const t = lastSearchParams.searchType === 'id' ? 'Account No.' : 'Name'; const wt = lastSearchParams.walletType === 'vendor' ? 'Vendor' : 'User';
        sd.innerHTML = `<div class="space-y-1 text-sm"><p><span class="font-medium">Type:</span> ${wt} Wallet</p><p><span class="font-medium">Search By:</span> ${t}</p><p><span class="font-medium">Query:</span> "${lastSearchParams.query}"</p></div>`;
        document.getElementById('sendCreditTitle').textContent = 'Wallet Not Found'; document.getElementById('sendCreditSubtitle').textContent = 'No matching wallet found';
        const s = document.getElementById('sendCreditSearchStep'); s.classList.add('slide-out-left');
        setTimeout(() => { s.classList.add('hidden', 'translate-x-full', 'opacity-0'); s.classList.remove('slide-out-left'); const n = document.getElementById('sendCreditNotFoundStep'); n.classList.remove('hidden', 'translate-x-full', 'opacity-0'); n.classList.add('slide-in-right'); currentStep = 4 }, 300)
    }

    function showWalletConfirmation(w) {
        const d = document.getElementById('walletDetails');
        d.innerHTML = `<div class="space-y-1"><p class="font-semibold text-secondary dark:text-white">${w.wallet_name}</p><p class="text-sm text-gray-text dark:text-white/70">Account No: ${w.wallet_number}</p></div>`;
        document.getElementById('sendCreditTitle').textContent = 'Confirm Wallet';
        document.getElementById('sendCreditSubtitle').textContent = 'Verify this is the correct wallet';
        let from = 'sendCreditSearchStep'; if (currentStep === 3) from = 'sendCreditMultipleResultsStep';
        const f = document.getElementById(from); f.classList.add('slide-out-left');
        setTimeout(() => { f.classList.add('hidden', 'translate-x-full', 'opacity-0'); f.classList.remove('slide-out-left'); const c = document.getElementById('sendCreditConfirmStep'); c.classList.remove('hidden', 'translate-x-full', 'opacity-0'); c.classList.add('slide-in-right'); currentStep = 4 }, 300)
    }

    window.searchAgain = function () { clearAllForms(); clearErrorMessages(); resetSearchButton(); searchResults = []; let from = ''; if (currentStep === 4) from = 'sendCreditNotFoundStep'; else if (currentStep === 5) from = 'sendCreditConfirmStep'; else if (currentStep === 3) from = 'sendCreditMultipleResultsStep'; if (from) animateToStep(from, 'sendCreditSearchStep', 2) }
    window.changeWalletType = function () { const cur = document.getElementById('sendCreditNotFoundStep'); cur.classList.add('slide-out-right'); setTimeout(() => { cur.classList.add('hidden', 'translate-x-full', 'opacity-0'); cur.classList.remove('slide-out-right'); document.getElementById('sendCreditTitle').textContent = 'Send Credit'; document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type'; document.getElementById('sendCreditBackBtn').classList.add('hidden'); clearAllForms(); clearErrorMessages(); const s1 = document.getElementById('sendCreditStep1'); s1.classList.remove('hidden', 'slide-out-left'); s1.classList.add('slide-in-left'); currentStep = 1 }, 300) }

    window.proceedToAmount = function () {
        document.getElementById('sendCreditTitle').textContent = 'Enter Amount';
        document.getElementById('sendCreditSubtitle').textContent = 'Specify the amount to send';
        const sum = document.getElementById('selectedWalletSummary');
        sum.innerHTML = `<div class="space-y-1"><p class="font-medium text-secondary dark:text-white">${selectedWallet.wallet_name}</p><p class="text-xs text-gray-text dark:text-white/70">Account No: ${selectedWallet.wallet_number}</p></div>`;
        animateToStep('sendCreditConfirmStep', 'sendCreditAmountStep', 5)
    }

    async function checkBalanceAndProceed() {
        const input = document.getElementById('sendCreditAmount'); const amount = input.value.trim(); clearErrorMessages();
        if (!amount || parseFloat(amount) < 500) { showInputError('sendCreditAmount', 'amountError', 'Please enter a valid amount (minimum 500 UGX)'); return }
        if (!selectedWallet || !selectedWallet.wallet_number) { showInputError('sendCreditAmount', 'amountError', 'No destination wallet selected'); return }
        document.getElementById('sendCreditTitle').textContent = 'Checking Balance'; document.getElementById('sendCreditSubtitle').textContent = 'Validating your wallet balance...';
        const icon = document.getElementById('balanceCheckIcon'), title = document.getElementById('balanceCheckTitle'), sub = document.getElementById('balanceCheckSubtitle'), det = document.getElementById('balanceCheckDetails'), act = document.getElementById('balanceCheckActions');
        icon.className = 'w-14 h-14 bg-blue-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-spinner animate-spin text-blue-600 text-lg"></i>'; title.textContent = 'Checking Balance'; sub.textContent = 'Validating your wallet balance...'; det.innerHTML = ''; act.innerHTML = '';
        animateToStep('sendCreditAmountStep', 'sendCreditBalanceCheckStep', 6);
        try {
            await new Promise(r => setTimeout(r, 700));
            const fd = new FormData(); fd.append('action', 'validateTransferBalance'); fd.append('amount', amount);
            const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd }); const d = await r.json();
            if (d.success) { if (d.validation.isValid) { showBalanceCheckSuccess(amount, d.validation.fee, d.validation.totalRequired, d.validation.availableBalance) } else { showInsufficientBalance(amount, d.validation.fee, d.validation.totalRequired, d.validation.availableBalance) } }
            else { showBalanceCheckError(d.message || 'Unable to validate balance') }
        } catch (_) { showBalanceCheckError('Error checking balance. Please try again.') }
    }

    function showBalanceCheckSuccess(amount, fee, total, avail) {
        const icon = document.getElementById('balanceCheckIcon'), title = document.getElementById('balanceCheckTitle'), sub = document.getElementById('balanceCheckSubtitle'), det = document.getElementById('balanceCheckDetails'), act = document.getElementById('balanceCheckActions');
        icon.className = 'w-14 h-14 bg-green-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-check text-green-600 text-lg"></i>'; title.textContent = 'Balance Sufficient'; sub.textContent = 'You have enough balance for this transfer';
        let html = `<div class="bg-green-50 dark:bg-white/5 border border-green-200 dark:border-white/10 rounded-xl p-4 space-y-2 text-sm"><div class="flex justify-between"><span class="text-green-700">Transfer Amount:</span><span class="font-semibold text-green-900">${parseFloat(amount).toLocaleString()} UGX</span></div>`;
        if (fee > 0) { const feeType = transferFeeSettings && transferFeeSettings.setting_type === 'flat' ? 'Transfer Fee (Flat)' : `Transfer Fee (${transferFeeSettings ? transferFeeSettings.setting_value : '0'}%)`; html += `<div class="flex justify-between"><span class="text-green-700">${feeType}:</span><span class="font-semibold text-green-900">${fee.toLocaleString()} UGX</span></div><div class="flex justify-between border-t border-green-300 pt-2"><span class="font-medium text-green-800">Total Required:</span><span class="font-bold text-green-900">${total.toLocaleString()} UGX</span></div>` }
        html += `<div class="flex justify-between border-t border-green-300 pt-2"><span class="font-medium text-green-800">Available Balance:</span><span class="font-bold text-green-900">${avail.toLocaleString()} UGX</span></div><div class="flex justify-between"><span class="font-medium text-green-800">Remaining After:</span><span class="font-bold text-green-900">${(avail - total).toLocaleString()} UGX</span></div></div>`;
        det.innerHTML = html;
        act.innerHTML = `<button onclick="changeTransferAmount()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Change Amount</button><button onclick="proceedToPasswordConfirmation()" class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Proceed to Confirm</button>`;
        currentBalance = avail; calculatedFee = fee; pendingTransfer = { amount: amount, formattedAmount: parseFloat(amount).toLocaleString('en-UG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }), wallet: selectedWallet, walletType: window.sendCreditType, searchType: lastSearchParams ? lastSearchParams.searchType : 'id', fee: calculatedFee, totalRequired: total }
    }

    function showInsufficientBalance(amount, fee, total, avail) {
        const icon = document.getElementById('balanceCheckIcon'), title = document.getElementById('balanceCheckTitle'), sub = document.getElementById('balanceCheckSubtitle');
        icon.className = 'w-14 h-14 bg-red-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>'; title.textContent = 'Insufficient Balance'; sub.textContent = "You don't have enough balance for this transfer";
        setTimeout(() => { animateToStep('sendCreditBalanceCheckStep', 'sendCreditInsufficientBalanceStep', 7); const d = document.getElementById('insufficientBalanceDetails'); let html = `<div class="space-y-2 text-sm"><div class="flex justify-between"><span class="text-red-700">Transfer Amount:</span><span class="font-semibold text-red-900">${parseFloat(amount).toLocaleString()} UGX</span></div>`; if (fee > 0) { const feeType = transferFeeSettings && transferFeeSettings.setting_type === 'flat' ? 'Transfer Fee (Flat)' : `Transfer Fee (${transferFeeSettings ? transferFeeSettings.setting_value : '0'}%)`; html += `<div class="flex justify-between"><span class="text-red-700">${feeType}:</span><span class="font-semibold text-red-900">${fee.toLocaleString()} UGX</span></div><div class="flex justify-between border-t border-red-300 pt-2"><span class="font-medium text-red-800">Total Required:</span><span class="font-bold text-red-900">${total.toLocaleString()} UGX</span></div>` } html += `<div class="flex justify-between border-t border-red-300 pt-2"><span class="font-medium text-red-800">Available Balance:</span><span class="font-bold text-red-900">${avail.toLocaleString()} UGX</span></div><div class="flex justify-between"><span class="font-medium text-red-800">Shortfall:</span><span class="font-bold text-red-900">${(total - avail).toLocaleString()} UGX</span></div></div>`; d.innerHTML = html }, 300)
    }

    function showBalanceCheckError(msg) {
        const icon = document.getElementById('balanceCheckIcon'), title = document.getElementById('balanceCheckTitle'), sub = document.getElementById('balanceCheckSubtitle'), det = document.getElementById('balanceCheckDetails'), act = document.getElementById('balanceCheckActions');
        icon.className = 'w-14 h-14 bg-red-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-times text-red-600 text-lg"></i>'; title.textContent = 'Balance Check Failed'; sub.textContent = msg;
        det.innerHTML = `<div class="bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 text-center text-sm text-red-700 dark:text-red-300">${msg}</div>`;
        act.innerHTML = `<button onclick="sendCreditBack()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Back</button><button onclick="checkBalanceAndProceed()" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Try Again</button>`
    }

    window.changeTransferAmount = function () { animateToStep('sendCreditInsufficientBalanceStep', 'sendCreditAmountStep', 5) }
    window.topUpWallet = function () { hideSendCreditModal(); window.location.href = 'zzimba-credit' }

    window.proceedToPasswordConfirmation = function () {
        const sum = document.getElementById('passwordTransferSummary'); const wt = window.sendCreditType === 'vendor' ? 'Vendor' : 'User'; let html = `<div class="space-y-2 text-sm"><div class="text-center pb-3 border-b border-gray-200"><p class="text-lg font-bold text-secondary dark:text-white">${pendingTransfer.formattedAmount} UGX</p><p class="text-xs text-gray-text dark:text-white/70">to ${wt} Wallet</p></div><div class="flex justify-between items-start"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Recipient:</span><div class="text-right"><p class="text-sm font-semibold text-secondary dark:text-white">${selectedWallet.wallet_name}</p><p class="text-xs text-gray-text dark:text-white/60">Account: ${selectedWallet.wallet_number}</p></div></div>`; if (calculatedFee > 0) { html += `<div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Transfer Fee:</span><span class="text-sm font-semibold text-secondary dark:text-white">${calculatedFee.toLocaleString()} UGX</span></div><div class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-2"><span class="text-sm font-bold text-gray-800 dark:text-white">Total Required:</span><span class="text-sm font-bold text-secondary dark:text-white">${pendingTransfer.totalRequired.toLocaleString()} UGX</span></div>` } sum.innerHTML = html + `</div>`;
        const attempts = getPasswordAttempts(); if (attempts.count > 0) { const warn = document.getElementById('attemptsWarning'); const rem = MAX_ATTEMPTS - attempts.count; warn.textContent = `Warning: ${rem} attempt${rem !== 1 ? 's' : ''} remaining before account is blocked.`; warn.classList.remove('hidden') }
        document.getElementById('sendCreditTitle').textContent = 'Confirm with Password'; document.getElementById('sendCreditSubtitle').textContent = 'Enter your password to authorize'; animateToStep('sendCreditBalanceCheckStep', 'sendCreditPasswordStep', 7)
    }

    document.getElementById('sendCreditAmount').addEventListener('input', e => { const v = e.target.value; const err = document.getElementById('amountError'); clearErrorMessages(); if (v) { const a = parseFloat(v); if (a < 500) { e.target.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200'); err.textContent = 'Amount must be at least 500 UGX'; err.classList.remove('hidden') } } })

    async function verifyPassword() {
        const inp = document.getElementById('confirmPassword'); const pwd = inp.value.trim(); clearErrorMessages(); if (!pwd) { showInputError('confirmPassword', 'passwordError', 'Please enter your password'); return }
        const btn = document.getElementById('verifyPasswordBtn'); const txt = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Verifying...'; btn.disabled = true;
        try {
            const fd = new FormData(); fd.append('action', 'verifyPassword'); fd.append('password', pwd); const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd }); const d = await r.json();
            if (d.success) { securityToken = d.token; resetPasswordAttempts(); showConfirmation() }
            else {
                const a = incrementPasswordAttempts();
                if (a.count >= MAX_ATTEMPTS) { showInputError('confirmPassword', 'passwordError', 'Too many failed attempts. Access blocked.'); setTimeout(() => { hideSendCreditModal(); setTimeout(() => showBlockedUserStep(), 500) }, 2000) }
                else { const rem = MAX_ATTEMPTS - a.count; showInputError('confirmPassword', 'passwordError', `Incorrect password. ${rem} attempt${rem !== 1 ? 's' : ''} remaining.`); const warn = document.getElementById('attemptsWarning'); warn.textContent = `Warning: ${rem} attempt${rem !== 1 ? 's' : ''} remaining before account is blocked.`; warn.classList.remove('hidden') }
                inp.value = ''
            }
        } catch (_) { showInputError('confirmPassword', 'passwordError', 'Error verifying password. Please try again.') }
        finally { btn.innerHTML = txt; btn.disabled = false }
    }

    function showConfirmation() {
        const c = document.getElementById('confirmationSummaryDetails'); const wt = window.sendCreditType === 'vendor' ? 'Vendor' : 'User'; let html = `<div class="space-y-3 text-sm"><div class="text-center pb-3 border-b border-yellow-200"><h5 class="font-semibold text-secondary dark:text-white mb-1">Transfer Summary</h5><p class="text-2xl font-bold text-secondary dark:text-white">${pendingTransfer.formattedAmount} UGX</p></div><div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Transfer Type:</span><span class="text-sm font-semibold text-secondary dark:text-white">Zzimba Credit - ${wt}</span></div><div class="flex justify-between items-start"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Recipient:</span><div class="text-right"><p class="text-sm font-semibold text-secondary dark:text-white">${selectedWallet.wallet_name}</p><p class="text-xs text-gray-text dark:text-white/60">Account: ${selectedWallet.wallet_number}</p></div></div><div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Amount:</span><span class="text-sm font-bold text-secondary dark:text-white">${pendingTransfer.amount} UGX</span></div>`;
        if (calculatedFee > 0) { html += `<div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Transfer Fee:</span><span class="text-sm font-bold text-secondary dark:text-white">${calculatedFee.toLocaleString()} UGX</span></div><div class="flex justify-between border-t border-yellow-300 pt-2"><span class="text-sm font-bold text-gray-800 dark:text-white">Total Deducted:</span><span class="text-sm font-bold text-secondary dark:text-white">${pendingTransfer.totalRequired.toLocaleString()} UGX</span></div>` }
        html += `<div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Status:</span><span class="text-sm font-semibold text-green-700">✓ Password Verified</span></div></div>`;
        c.innerHTML = html; document.getElementById('sendCreditTitle').textContent = 'Confirm Transfer'; document.getElementById('sendCreditSubtitle').textContent = 'Review details before sending'; animateToStep('sendCreditPasswordStep', 'sendCreditConfirmationStep', 8)
    }

    window.cancelConfirmation = function () { animateToStep('sendCreditConfirmationStep', 'sendCreditPasswordStep', 7) }

    window.confirmSendCredit = async function () {
        if (!pendingTransfer || !securityToken) { showResponse(false, 'Error', 'Security verification required', 'Please verify your password again'); return }
        const btn = document.getElementById('confirmSendBtn'); const txt = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner animate-spin"></i> Processing...'; btn.disabled = true;
        try {
            const fd = new FormData(); fd.append('action', 'sendCredit'); fd.append('wallet_to', pendingTransfer.wallet.wallet_number); fd.append('amount', pendingTransfer.amount); fd.append('security_token', securityToken);
            const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd }); const d = await r.json();
            if (d.success) {
                currentBalance = d.balance;
                showResponse(true, 'Transfer Successful!', 'Credit has been sent successfully', { transactionId: d.transaction_id || 'N/A', amount: pendingTransfer.amount, fee: calculatedFee, totalDeducted: pendingTransfer.totalRequired, recipient: pendingTransfer.wallet.wallet_name, recipientAccount: pendingTransfer.wallet.wallet_number, newBalance: d.balance }, true)
            } else {
                showResponse(false, 'Transfer Failed', d.message || 'The transfer could not be completed', 'Please check your details and try again')
            }
        } catch (_) {
            showResponse(false, 'Connection Error', 'Unable to process the transfer', 'Please check your internet connection and try again')
        } finally { btn.innerHTML = txt; btn.disabled = false; securityToken = null }
    }

    function showResponse(success, title, subtitle, details, autoClose = false) {
        const icon = document.getElementById('responseIcon'), rt = document.getElementById('responseTitle'), rs = document.getElementById('responseSubtitle'), rd = document.getElementById('responseDetails'), ra = document.getElementById('responseActions'), ac = document.getElementById('autoCloseCountdown');
        if (success) { icon.className = 'w-16 h-16 bg-green-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-check text-green-600 text-xl"></i>'; rd.className = 'bg-green-50 dark:bg-white/5 border border-green-200 dark:border-white/10 rounded-xl p-4 mb-5' } else { icon.className = 'w-16 h-16 bg-red-100 rounded-full grid place-items-center mx-auto mb-3'; icon.innerHTML = '<i class="fas fa-times text-red-600 text-xl"></i>'; rd.className = 'bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 mb-5' }
        rt.textContent = title; rs.textContent = subtitle;
        if (success && typeof details === 'object') {
            let html = `<div class="space-y-3 text-sm"><div class="text-center pb-3 border-b ${success ? 'border-green-200' : 'border-red-200'}"><div class="flex items-center justify-center gap-2 mb-1"><span class="font-semibold ${success ? 'text-green-800' : 'text-red-800'}">Transaction Completed</span></div><p class="${success ? 'text-green-700' : 'text-red-700'} text-xs">Transaction ID: ${details.transactionId}</p></div><div class="grid grid-cols-2 gap-4"><div><p class="${success ? 'text-green-600' : 'text-red-600'} font-medium">Amount Sent</p><p class="${success ? 'text-green-800' : 'text-red-800'} font-bold">${details.amount} UGX</p></div>`;
            if (details.fee > 0) { html += `<div><p class="${success ? 'text-green-600' : 'text-red-600'} font-medium">Transfer Fee</p><p class="${success ? 'text-green-800' : 'text-red-800'} font-bold">${details.fee.toLocaleString()} UGX</p></div></div><div class="grid grid-cols-2 gap-4"><div><p class="${success ? 'text-green-600' : 'text-red-600'} font-medium">Total Deducted</p><p class="${success ? 'text-green-800' : 'text-red-800'} font-bold">${details.totalDeducted.toLocaleString()} UGX</p></div>` }
            html += `<div><p class="${success ? 'text-green-600' : 'text-red-600'} font-medium">Available Balance</p><p class="${success ? 'text-green-800' : 'text-red-800'} font-bold">${details.newBalance ? details.newBalance.toLocaleString() : 'N/A'} UGX</p></div></div><div class="pt-2 border-t ${success ? 'border-green-200' : 'border-red-200'}"><p class="${success ? 'text-green-600' : 'text-red-600'} font-medium text-sm">Sent to:</p><p class="${success ? 'text-green-800' : 'text-red-800'} font-semibold">${details.recipient}</p><p class="${success ? 'text-green-600' : 'text-red-600'} text-xs">Account: ${details.recipientAccount}</p></div></div>`;
            rd.innerHTML = html
        } else {
            rd.innerHTML = `<div class="text-center text-sm ${success ? 'text-green-700' : 'text-red-700'}">${typeof details === 'string' ? details : 'Transaction details unavailable'}</div>`
        }
        if (success) { ra.innerHTML = `<button onclick="hideSendCreditModal()" class="w-full px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Close</button>` }
        else { ra.innerHTML = `<button onclick="sendCreditBack()" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg白/20 transition-all duration-200 font-medium">Try Again</button><button onclick="hideSendCreditModal()" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Close</button>` }
        document.getElementById('sendCreditTitle').textContent = title; document.getElementById('sendCreditSubtitle').textContent = subtitle;
        if (autoClose && success) { ac.classList.remove('hidden'); let c = 30; const span = document.getElementById('countdownTimer'); autoCloseTimer = setInterval(() => { c--; span.textContent = c; if (c <= 0) { clearInterval(autoCloseTimer); hideSendCreditModal() } }, 1000) } else { ac.classList.add('hidden') }
        animateToStep('sendCreditConfirmationStep', 'sendCreditResponseStep', 9)
    }
</script>