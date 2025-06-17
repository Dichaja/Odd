<!-- Send Credit Modal -->
<div id="sendCreditModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden transform transition-all duration-300 scale-95 opacity-0"
        id="sendCreditModalContent">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <!-- Back Button -->
                    <button id="sendCreditBackBtn" onclick="sendCreditBack()"
                        class="hidden shrink-0 w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-all duration-200 transform hover:scale-105">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>

                    <div
                        class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center transition-colors duration-200">
                        <i class="fas fa-paper-plane text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 id="sendCreditTitle"
                            class="text-xl font-semibold text-gray-900 transition-all duration-300">Send Credit</h3>
                        <p id="sendCreditSubtitle" class="text-sm text-gray-500 transition-all duration-300">Select
                            destination wallet type</p>
                    </div>
                </div>
                <button onclick="hideSendCreditModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200 hover:rotate-90 transform">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Step 1: Choose wallet type -->
            <div id="sendCreditStep1" class="transition-all duration-300 transform">
                <div class="grid grid-cols-1 gap-4">
                    <button type="button" onclick="selectSendDestination('vendor')"
                        class="w-full px-4 py-3 bg-primary/10 text-primary rounded-xl flex items-center justify-center gap-2 hover:bg-primary/20 transition-all duration-200 font-medium transform hover:scale-[1.02] hover:shadow-md">
                        <i class="fas fa-store"></i> Vendor Wallet
                    </button>
                    <button type="button" onclick="selectSendDestination('user')"
                        class="w-full px-4 py-3 bg-primary/10 text-primary rounded-xl flex items-center justify-center gap-2 hover:bg-primary/20 transition-all duration-200 font-medium transform hover:scale-[1.02] hover:shadow-md">
                        <i class="fas fa-user"></i> User Wallet
                    </button>
                </div>
            </div>

            <!-- Step 2: Search for the target wallet -->
            <div id="sendCreditSearchStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <!-- Search Options -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Search By</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="id" class="sr-only peer" checked
                                onchange="clearSearchInput()">
                            <div
                                class="w-full px-4 py-3 text-sm font-medium rounded-lg border-2 border-gray-200 bg-white text-gray-700 transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center gap-2">
                                <i class="fas fa-hashtag"></i>
                                <span>Account No.</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="name" class="sr-only peer"
                                onchange="clearSearchInput()">
                            <div
                                class="w-full px-4 py-3 text-sm font-medium rounded-lg border-2 border-gray-200 bg-white text-gray-700 transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center gap-2">
                                <i class="fas fa-user-tag"></i>
                                <span>Name</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Search Input -->
                <form id="searchForm" onsubmit="handleSearchSubmit(event)">
                    <div class="mb-4">
                        <label for="sendCreditSearchInput" class="block text-sm font-semibold text-gray-700 mb-2"
                            id="sendCreditSearchLabel">
                            <!-- injected by JS -->
                        </label>
                        <div class="relative">
                            <input type="text" id="sendCreditSearchInput" autocomplete="off"
                                class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 placeholder-gray-400"
                                placeholder="Enter search term..." required>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <div id="searchInputError" class="hidden mt-1 text-sm text-red-600">
                            <!-- Error message will be shown here -->
                        </div>
                    </div>

                    <!-- Search Button -->
                    <button type="submit" id="searchWalletBtn"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i>
                        <span>Search Wallet</span>
                    </button>
                </form>
            </div>

            <!-- Step 3: Multiple Results Selection -->
            <div id="sendCreditMultipleResultsStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-list text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Multiple Wallets Found</h4>
                    <p class="text-sm text-gray-500">Select the correct wallet from the results below</p>
                </div>

                <div id="multipleResultsList" class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                    <!-- Multiple results will be populated by JS -->
                </div>

                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Search Again
                    </button>
                </div>
            </div>

            <!-- Step 4: Wallet Not Found -->
            <div id="sendCreditNotFoundStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Wallet Not Found</h4>
                    <p class="text-sm text-gray-500">No wallet matches your search criteria</p>
                </div>

                <div id="searchDetails" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <!-- Search details will be populated by JS -->
                </div>

                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">
                        Search Again
                    </button>
                    <button onclick="changeWalletType()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Change Type
                    </button>
                </div>
            </div>

            <!-- Step 5: Confirm Wallet -->
            <div id="sendCreditConfirmStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Wallet Found</h4>
                    <p class="text-sm text-gray-500">Please confirm this is the correct wallet</p>
                </div>

                <div id="walletDetails" class="bg-gray-50 rounded-xl p-4 mb-6">
                    <!-- Wallet details will be populated by JS -->
                </div>

                <div class="flex gap-3">
                    <button onclick="searchAgain()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Search Again
                    </button>
                    <button onclick="proceedToAmount()"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">
                        Confirm Wallet
                    </button>
                </div>
            </div>

            <!-- Step 6: Enter Amount -->
            <div id="sendCreditAmountStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <form id="amountForm" onsubmit="handleAmountSubmit(event)">
                    <div class="mb-6">
                        <label for="sendCreditAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            Enter Amount to Send
                        </label>
                        <div class="relative">
                            <input type="number" id="sendCreditAmount" min="500" step="1" autocomplete="off"
                                class="w-full px-4 py-3 pr-16 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 placeholder-gray-400 text-lg font-semibold"
                                placeholder="500" required>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">
                                UGX
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Minimum amount: 500 UGX</p>
                        <div id="amountError" class="hidden mt-1 text-sm text-red-600">
                            <!-- Error message will be shown here -->
                        </div>
                    </div>

                    <!-- Selected Wallet Summary -->
                    <div id="selectedWalletSummary" class="bg-gray-50 rounded-xl p-4 mb-6">
                        <!-- Summary will be populated by JS -->
                    </div>

                    <div class="flex gap-3">
                        <button type="button" onclick="sendCreditBack()"
                            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                            Back
                        </button>
                        <button type="submit" id="sendCreditBtn"
                            class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">
                            Send Credit
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 7: Confirmation Modal -->
            <div id="sendCreditConfirmationStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Confirm Transfer</h4>
                    <p class="text-sm text-gray-500">Please review the details before proceeding</p>
                </div>

                <div id="confirmationDetails" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <!-- Confirmation details will be populated by JS -->
                </div>

                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-6">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-triangle text-red-600 text-sm mt-0.5"></i>
                        <p class="text-sm text-red-700">
                            <strong>Warning:</strong> This action cannot be undone. Please verify all details are
                            correct.
                        </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button onclick="cancelConfirmation()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button onclick="confirmSendCredit()" id="confirmSendBtn"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">
                        Confirm Send
                    </button>
                </div>
            </div>

            <!-- Step 8: Response/Result -->
            <div id="sendCreditResponseStep"
                class="hidden transition-all duration-300 transform translate-x-full opacity-0">
                <div class="text-center mb-6">
                    <div id="responseIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <!-- Icon will be populated by JS -->
                    </div>
                    <h4 id="responseTitle" class="text-lg font-semibold text-gray-900 mb-2">
                        <!-- Title will be populated by JS -->
                    </h4>
                    <p id="responseSubtitle" class="text-sm text-gray-500">
                        <!-- Subtitle will be populated by JS -->
                    </p>
                </div>

                <div id="responseDetails" class="rounded-xl p-4 mb-6">
                    <!-- Response details will be populated by JS -->
                </div>

                <div id="responseActions" class="flex gap-3">
                    <!-- Actions will be populated by JS -->
                </div>

                <!-- Auto-close countdown for success -->
                <div id="autoCloseCountdown" class="hidden text-center mt-4">
                    <p class="text-sm text-gray-500">
                        This window will close automatically in <span id="countdownTimer"
                            class="font-semibold">30</span> seconds
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom animations for smooth transitions */
    .slide-in-right {
        animation: slideInRight 0.3s ease-out forwards;
    }

    .slide-out-left {
        animation: slideOutLeft 0.3s ease-out forwards;
    }

    .slide-in-left {
        animation: slideInLeft 0.3s ease-out forwards;
    }

    .slide-out-right {
        animation: slideOutRight 0.3s ease-out forwards;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutLeft {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(-100%);
            opacity: 0;
        }
    }

    @keyframes slideInLeft {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Smooth modal appearance */
    .modal-show {
        transform: scale(1) !important;
        opacity: 1 !important;
    }

    /* Loading animation */
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* Shake animation for validation errors */
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }

    /* Pulse animation for processing */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>

<script>
    const sendCreditApiUrl = <?= json_encode(BASE_URL . 'vendor-store/fetch/manageSendCredit.php') ?>;

    let currentStep = 1;
    let maxStep = 1;
    let selectedWallet = null;
    let lastSearchParams = null;
    let searchResults = [];
    let pendingTransfer = null;
    let autoCloseTimer = null;

    window.showSendCreditModal = function () {
        const modal = document.getElementById('sendCreditModal');
        const modalContent = document.getElementById('sendCreditModalContent');

        modal.classList.remove('hidden');

        // Trigger animation after a small delay
        setTimeout(() => {
            modalContent.classList.add('modal-show');
        }, 10);

        // Reset to step 1
        resetToStep1();
    };

    window.hideSendCreditModal = function () {
        const modal = document.getElementById('sendCreditModal');
        const modalContent = document.getElementById('sendCreditModalContent');

        // Clear any auto-close timer
        if (autoCloseTimer) {
            clearInterval(autoCloseTimer);
            autoCloseTimer = null;
        }

        modalContent.classList.remove('modal-show');

        setTimeout(() => {
            modal.classList.add('hidden');
            resetToStep1();

            location.reload();
        }, 300);
    };

    function resetToStep1() {
        currentStep = 1;
        maxStep = 1;
        selectedWallet = null;
        lastSearchParams = null;
        searchResults = [];
        pendingTransfer = null;

        // Clear any auto-close timer
        if (autoCloseTimer) {
            clearInterval(autoCloseTimer);
            autoCloseTimer = null;
        }

        // Reset visibility
        document.getElementById('sendCreditStep1').classList.remove('hidden', 'slide-out-left');
        document.getElementById('sendCreditSearchStep').classList.add('hidden');
        document.getElementById('sendCreditMultipleResultsStep').classList.add('hidden');
        document.getElementById('sendCreditNotFoundStep').classList.add('hidden');
        document.getElementById('sendCreditConfirmStep').classList.add('hidden');
        document.getElementById('sendCreditAmountStep').classList.add('hidden');
        document.getElementById('sendCreditConfirmationStep').classList.add('hidden');
        document.getElementById('sendCreditResponseStep').classList.add('hidden');

        // Reset titles
        document.getElementById('sendCreditTitle').textContent = 'Send Credit';
        document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type';

        // Hide back button
        document.getElementById('sendCreditBackBtn').classList.add('hidden');

        // Clear all forms
        clearAllForms();

        // Reset radio buttons
        document.querySelector('input[name="searchType"][value="id"]').checked = true;

        // Reset search button
        resetSearchButton();

        // Reset send button
        resetSendButton();

        // Clear error messages
        clearErrorMessages();
    }

    function clearAllForms() {
        document.getElementById('sendCreditSearchInput').value = '';
        document.getElementById('sendCreditAmount').value = '';
    }

    function clearSearchInput() {
        document.getElementById('sendCreditSearchInput').value = '';
        clearErrorMessages();
    }

    function clearErrorMessages() {
        document.getElementById('searchInputError').classList.add('hidden');
        document.getElementById('amountError').classList.add('hidden');

        // Remove error styling
        document.getElementById('sendCreditSearchInput').classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
        document.getElementById('sendCreditAmount').classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
    }

    function resetSearchButton() {
        const searchBtn = document.getElementById('searchWalletBtn');
        searchBtn.innerHTML = '<i class="fas fa-search"></i><span>Search Wallet</span>';
        searchBtn.disabled = false;
    }

    function resetSendButton() {
        const sendBtn = document.getElementById('sendCreditBtn');
        if (sendBtn) {
            sendBtn.innerHTML = 'Send Credit';
            sendBtn.disabled = false;
        }
    }

    window.selectSendDestination = function (type) {
        // Store selection for later
        window.sendCreditType = type;
        maxStep = Math.max(maxStep, 2);

        // Clear forms when switching wallet types
        clearAllForms();
        clearErrorMessages();

        // Animate step 1 out
        const step1 = document.getElementById('sendCreditStep1');
        step1.classList.add('slide-out-left');

        setTimeout(() => {
            step1.classList.add('hidden');

            // Update titles
            const walletTypeText = type === 'vendor' ? 'Vendor' : 'User';
            document.getElementById('sendCreditTitle').textContent = `Send to ${walletTypeText}`;
            document.getElementById('sendCreditSubtitle').textContent = `Search for ${walletTypeText.toLowerCase()} wallet`;

            // Update search label
            updateSearchLabel();

            // Show back button
            document.getElementById('sendCreditBackBtn').classList.remove('hidden');

            // Show search step with animation
            const searchStep = document.getElementById('sendCreditSearchStep');
            searchStep.classList.remove('hidden', 'translate-x-full', 'opacity-0');
            searchStep.classList.add('slide-in-right');

            currentStep = 2;

            // Focus on search input
            setTimeout(() => {
                document.getElementById('sendCreditSearchInput').focus();
            }, 400);
        }, 300);
    };

    window.sendCreditBack = function () {
        if (currentStep === 8) {
            // From response step, close modal
            hideSendCreditModal();
        } else if (currentStep === 7) {
            // Go back to amount step
            animateToStep('sendCreditConfirmationStep', 'sendCreditAmountStep', 6);
        } else if (currentStep === 6) {
            // Go back to confirm step
            animateToStep('sendCreditAmountStep', 'sendCreditConfirmStep', 5);
        } else if (currentStep === 5) {
            // Go back to search step (clear form)
            clearAllForms();
            clearErrorMessages();
            resetSearchButton();
            animateToStep('sendCreditConfirmStep', 'sendCreditSearchStep', 2);
        } else if (currentStep === 4) {
            // Go back to search step (clear form)
            clearAllForms();
            clearErrorMessages();
            resetSearchButton();
            animateToStep('sendCreditNotFoundStep', 'sendCreditSearchStep', 2);
        } else if (currentStep === 3) {
            // Go back to search step (clear form)
            clearAllForms();
            clearErrorMessages();
            resetSearchButton();
            animateToStep('sendCreditMultipleResultsStep', 'sendCreditSearchStep', 2);
        } else if (currentStep === 2) {
            // Go back to step 1
            const searchStep = document.getElementById('sendCreditSearchStep');
            searchStep.classList.add('slide-out-right');

            setTimeout(() => {
                searchStep.classList.add('hidden', 'translate-x-full', 'opacity-0');
                searchStep.classList.remove('slide-out-right');

                // Reset titles
                document.getElementById('sendCreditTitle').textContent = 'Send Credit';
                document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type';

                // Hide back button
                document.getElementById('sendCreditBackBtn').classList.add('hidden');

                // Show step 1 with animation
                const step1 = document.getElementById('sendCreditStep1');
                step1.classList.remove('hidden', 'slide-out-left');
                step1.classList.add('slide-in-left');

                currentStep = 1;
            }, 300);
        }
    };

    function animateToStep(fromStepId, toStepId, stepNumber) {
        const fromStep = document.getElementById(fromStepId);
        fromStep.classList.add('slide-out-right');

        setTimeout(() => {
            fromStep.classList.add('hidden', 'translate-x-full', 'opacity-0');
            fromStep.classList.remove('slide-out-right');

            const toStep = document.getElementById(toStepId);
            toStep.classList.remove('hidden', 'translate-x-full', 'opacity-0');
            toStep.classList.add('slide-in-left');

            currentStep = stepNumber;

            // Focus appropriate input
            if (stepNumber === 2) {
                setTimeout(() => {
                    document.getElementById('sendCreditSearchInput').focus();
                }, 400);
            } else if (stepNumber === 6) {
                setTimeout(() => {
                    document.getElementById('sendCreditAmount').focus();
                }, 400);
            }
        }, 300);
    }

    function updateSearchLabel() {
        const selectedSearchType = document.querySelector('input[name="searchType"]:checked').value;
        const walletType = window.sendCreditType === 'vendor' ? 'Vendor' : 'User';
        const searchTypeText = selectedSearchType === 'id' ? 'Account No.' : 'Name';

        document.getElementById('sendCreditSearchLabel').textContent =
            `Search ${walletType} Wallet by ${searchTypeText}`;
    }

    // Update search label when radio button changes
    document.addEventListener('change', function (e) {
        if (e.target.name === 'searchType') {
            updateSearchLabel();

            // Update placeholder
            const searchInput = document.getElementById('sendCreditSearchInput');
            const searchType = e.target.value;

            switch (searchType) {
                case 'id':
                    searchInput.placeholder = 'Enter account number...';
                    break;
                case 'name':
                    searchInput.placeholder = 'Enter wallet name...';
                    break;
            }
        }
    });

    // Handle search form submission
    function handleSearchSubmit(event) {
        event.preventDefault();
        performSendCreditSearch();
    }

    // Handle amount form submission
    function handleAmountSubmit(event) {
        event.preventDefault();
        showConfirmation();
    }

    window.performSendCreditSearch = async function () {
        const query = document.getElementById('sendCreditSearchInput').value.trim();
        const searchType = document.querySelector('input[name="searchType"]:checked').value;

        // Clear previous errors
        clearErrorMessages();

        if (!query) {
            showInputError('sendCreditSearchInput', 'searchInputError', 'Please enter a search term');
            return;
        }

        // Store search params for error display
        lastSearchParams = {
            query: query,
            searchType: searchType,
            walletType: window.sendCreditType
        };

        // Show loading state
        const button = document.getElementById('searchWalletBtn');
        button.innerHTML = '<i class="fas fa-spinner animate-spin"></i><span>Searching...</span>';
        button.disabled = true;

        try {
            // Simulate 3-second delay
            await new Promise(resolve => setTimeout(resolve, 3000));

            // Prepare payload
            const payload = new FormData();
            payload.append('action', 'searchWallet');
            payload.append('type', window.sendCreditType);
            payload.append('searchType', searchType);
            payload.append('searchValue', query);

            // Make API call
            const response = await fetch(sendCreditApiUrl, {
                method: 'POST',
                body: payload
            });

            const data = await response.json();

            if (data.success) {
                if (Array.isArray(data.wallets) && data.wallets.length > 1) {
                    // Multiple results found
                    searchResults = data.wallets;
                    showMultipleResults(data.wallets);
                } else if (Array.isArray(data.wallets) && data.wallets.length === 1) {
                    // Single result found
                    selectedWallet = data.wallets[0];
                    showWalletConfirmation(data.wallets[0]);
                } else if (data.wallet) {
                    // Single wallet object (backward compatibility)
                    selectedWallet = data.wallet;
                    showWalletConfirmation(data.wallet);
                } else {
                    // No results found
                    showWalletNotFound();
                }
            } else {
                showWalletNotFound();
            }

        } catch (error) {
            console.error('Search error:', error);
            showInputError('sendCreditSearchInput', 'searchInputError', 'Error searching for wallet. Please try again.');
        } finally {
            // Reset button
            resetSearchButton();
        }
    };

    function showInputError(inputId, errorId, message) {
        const input = document.getElementById(inputId);
        const errorDiv = document.getElementById(errorId);

        input.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200', 'animate-shake');
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');

        // Remove shake animation after it completes
        setTimeout(() => {
            input.classList.remove('animate-shake');
        }, 500);

        input.focus();
    }

    function showMultipleResults(wallets) {
        // Populate multiple results list
        const resultsList = document.getElementById('multipleResultsList');
        resultsList.innerHTML = '';

        wallets.forEach((wallet, index) => {
            const resultCard = document.createElement('div');
            resultCard.className = 'border border-gray-200 rounded-xl p-4 hover:border-primary/50 hover:bg-primary/5 transition-all cursor-pointer';
            resultCard.onclick = () => selectWalletFromResults(wallet);

            resultCard.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-${window.sendCreditType === 'vendor' ? 'store' : 'user'} text-primary"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="font-semibold text-gray-900">${wallet.wallet_name}</h5>
                        <p class="text-sm text-gray-500">Account No: ${wallet.wallet_number}</p>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </div>
            `;

            resultsList.appendChild(resultCard);
        });

        // Update titles
        document.getElementById('sendCreditTitle').textContent = 'Multiple Results';
        document.getElementById('sendCreditSubtitle').textContent = `Found ${wallets.length} matching wallets`;

        // Animate to multiple results step
        const searchStep = document.getElementById('sendCreditSearchStep');
        searchStep.classList.add('slide-out-left');

        setTimeout(() => {
            searchStep.classList.add('hidden', 'translate-x-full', 'opacity-0');
            searchStep.classList.remove('slide-out-left');

            const multipleStep = document.getElementById('sendCreditMultipleResultsStep');
            multipleStep.classList.remove('hidden', 'translate-x-full', 'opacity-0');
            multipleStep.classList.add('slide-in-right');

            currentStep = 3;
        }, 300);
    }

    function selectWalletFromResults(wallet) {
        selectedWallet = wallet;
        showWalletConfirmation(wallet);
    }

    function showWalletNotFound() {
        // Populate search details
        const searchDetails = document.getElementById('searchDetails');
        const searchTypeText = lastSearchParams.searchType === 'id' ? 'Account No.' : 'Name';
        const walletTypeText = lastSearchParams.walletType === 'vendor' ? 'Vendor' : 'User';

        searchDetails.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-search text-red-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-red-900 mb-1">Search Details</p>
                    <div class="text-sm text-red-700 space-y-1">
                        <p><span class="font-medium">Type:</span> ${walletTypeText} Wallet</p>
                        <p><span class="font-medium">Search By:</span> ${searchTypeText}</p>
                        <p><span class="font-medium">Query:</span> "${lastSearchParams.query}"</p>
                    </div>
                </div>
            </div>
        `;

        // Update titles
        document.getElementById('sendCreditTitle').textContent = 'Wallet Not Found';
        document.getElementById('sendCreditSubtitle').textContent = 'No matching wallet found';

        // Animate to not found step
        const searchStep = document.getElementById('sendCreditSearchStep');
        searchStep.classList.add('slide-out-left');

        setTimeout(() => {
            searchStep.classList.add('hidden', 'translate-x-full', 'opacity-0');
            searchStep.classList.remove('slide-out-left');

            const notFoundStep = document.getElementById('sendCreditNotFoundStep');
            notFoundStep.classList.remove('hidden', 'translate-x-full', 'opacity-0');
            notFoundStep.classList.add('slide-in-right');

            currentStep = 4;
        }, 300);
    }

    function showWalletConfirmation(wallet) {
        // Populate wallet details
        const walletDetails = document.getElementById('walletDetails');
        walletDetails.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-${window.sendCreditType === 'vendor' ? 'store' : 'user'} text-primary"></i>
                </div>
                <div>
                    <h5 class="font-semibold text-gray-900">${wallet.wallet_name}</h5>
                    <p class="text-sm text-gray-500">Account No: ${wallet.wallet_number}</p>
                </div>
            </div>
        `;

        // Update titles
        document.getElementById('sendCreditTitle').textContent = 'Confirm Wallet';
        document.getElementById('sendCreditSubtitle').textContent = 'Verify this is the correct wallet';

        // Determine which step to animate from
        let fromStepId = 'sendCreditSearchStep';
        if (currentStep === 3) {
            fromStepId = 'sendCreditMultipleResultsStep';
        }

        // Animate to confirmation step
        const fromStep = document.getElementById(fromStepId);
        fromStep.classList.add('slide-out-left');

        setTimeout(() => {
            fromStep.classList.add('hidden', 'translate-x-full', 'opacity-0');
            fromStep.classList.remove('slide-out-left');

            const confirmStep = document.getElementById('sendCreditConfirmStep');
            confirmStep.classList.remove('hidden', 'translate-x-full', 'opacity-0');
            confirmStep.classList.add('slide-in-right');

            currentStep = 5;
        }, 300);
    }

    window.searchAgain = function () {
        // Clear forms and go back to search step
        clearAllForms();
        clearErrorMessages();
        resetSearchButton();
        searchResults = [];

        let fromStepId = '';
        if (currentStep === 4) {
            fromStepId = 'sendCreditNotFoundStep';
        } else if (currentStep === 5) {
            fromStepId = 'sendCreditConfirmStep';
        } else if (currentStep === 3) {
            fromStepId = 'sendCreditMultipleResultsStep';
        }

        if (fromStepId) {
            animateToStep(fromStepId, 'sendCreditSearchStep', 2);
        }
    };

    window.changeWalletType = function () {
        // Go back to step 1 to change wallet type
        const currentStepElement = document.getElementById('sendCreditNotFoundStep');
        currentStepElement.classList.add('slide-out-right');

        setTimeout(() => {
            currentStepElement.classList.add('hidden', 'translate-x-full', 'opacity-0');
            currentStepElement.classList.remove('slide-out-right');

            // Reset titles
            document.getElementById('sendCreditTitle').textContent = 'Send Credit';
            document.getElementById('sendCreditSubtitle').textContent = 'Select destination wallet type';

            // Hide back button
            document.getElementById('sendCreditBackBtn').classList.add('hidden');

            // Clear forms
            clearAllForms();
            clearErrorMessages();

            // Show step 1 with animation
            const step1 = document.getElementById('sendCreditStep1');
            step1.classList.remove('hidden', 'slide-out-left');
            step1.classList.add('slide-in-left');

            currentStep = 1;
        }, 300);
    };

    window.proceedToAmount = function () {
        // Update titles
        document.getElementById('sendCreditTitle').textContent = 'Enter Amount';
        document.getElementById('sendCreditSubtitle').textContent = 'Specify the amount to send';

        // Populate selected wallet summary
        const summary = document.getElementById('selectedWalletSummary');
        summary.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-${window.sendCreditType === 'vendor' ? 'store' : 'user'} text-primary text-sm"></i>
                </div>
                <div>
                    <p class="font-medium text-gray-900">${selectedWallet.wallet_name}</p>
                    <p class="text-xs text-gray-500">Account No: ${selectedWallet.wallet_number}</p>
                </div>
            </div>
        `;

        // Animate to amount step
        animateToStep('sendCreditConfirmStep', 'sendCreditAmountStep', 6);
    };

    function showConfirmation() {
        const amountInput = document.getElementById('sendCreditAmount');
        const amount = amountInput.value.trim();
        clearErrorMessages();

        if (!amount || parseFloat(amount) < 500) {
            showInputError('sendCreditAmount', 'amountError', 'Please enter a valid amount (minimum 500 UGX)');
            return;
        }

        if (!selectedWallet || !selectedWallet.wallet_number) {
            showInputError('sendCreditAmount', 'amountError', 'No destination wallet selected');
            return;
        }

        // Store pending transfer details
        pendingTransfer = {
            amount: amount,
            wallet: selectedWallet
        };

        // Populate confirmation details
        const confirmationDetails = document.getElementById('confirmationDetails');
        confirmationDetails.innerHTML = `
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">Amount:</span>
                    <span class="text-lg font-bold text-gray-900">${amount} UGX</span>
                </div>
                <div class="border-t border-yellow-200 pt-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-${window.sendCreditType === 'vendor' ? 'store' : 'user'} text-primary text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">${selectedWallet.wallet_name}</p>
                            <p class="text-xs text-gray-500">Account No: ${selectedWallet.wallet_number}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Update titles
        document.getElementById('sendCreditTitle').textContent = 'Confirm Transfer';
        document.getElementById('sendCreditSubtitle').textContent = 'Review details before sending';

        // Animate to confirmation step
        animateToStep('sendCreditAmountStep', 'sendCreditConfirmationStep', 7);
    }

    window.cancelConfirmation = function () {
        // Go back to amount step
        animateToStep('sendCreditConfirmationStep', 'sendCreditAmountStep', 6);
    };

    window.confirmSendCredit = async function () {
        if (!pendingTransfer) {
            showResponse(false, 'Error', 'No transfer details found', 'Please try again');
            return;
        }

        // Show processing state on button
        const confirmBtn = document.getElementById('confirmSendBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i><span class="ml-2">Processing...</span>';
        confirmBtn.disabled = true;

        try {
            // Build payload
            const payload = new FormData();
            payload.append('action', 'sendCredit');
            payload.append('wallet_to', pendingTransfer.wallet.wallet_number);
            payload.append('amount', pendingTransfer.amount);

            // Make API call
            const response = await fetch(sendCreditApiUrl, {
                method: 'POST',
                body: payload
            });

            const data = await response.json();

            if (data.success) {
                showResponse(
                    true,
                    'Transfer Successful!',
                    'Credit has been sent successfully',
                    `Transaction ID: ${data.transaction_id || 'N/A'}`,
                    true // Enable auto-close
                );
            } else {
                showResponse(
                    false,
                    'Transfer Failed',
                    data.message || 'The transfer could not be completed',
                    'Please check your details and try again'
                );
            }

        } catch (error) {
            console.error('Transfer error:', error);
            showResponse(
                false,
                'Connection Error',
                'Unable to process the transfer',
                'Please check your internet connection and try again'
            );
        } finally {
            // Reset button
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        }
    };

    function showResponse(success, title, subtitle, details, autoClose = false) {
        // Update response elements
        const responseIcon = document.getElementById('responseIcon');
        const responseTitle = document.getElementById('responseTitle');
        const responseSubtitle = document.getElementById('responseSubtitle');
        const responseDetails = document.getElementById('responseDetails');
        const responseActions = document.getElementById('responseActions');
        const autoCloseCountdown = document.getElementById('autoCloseCountdown');

        // Set icon and colors based on success
        if (success) {
            responseIcon.className = 'w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4';
            responseIcon.innerHTML = '<i class="fas fa-check text-green-600 text-xl"></i>';
            responseDetails.className = 'bg-green-50 border border-green-200 rounded-xl p-4 mb-6';
        } else {
            responseIcon.className = 'w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4';
            responseIcon.innerHTML = '<i class="fas fa-times text-red-600 text-xl"></i>';
            responseDetails.className = 'bg-red-50 border border-red-200 rounded-xl p-4 mb-6';
        }

        // Set content
        responseTitle.textContent = title;
        responseSubtitle.textContent = subtitle;
        responseDetails.innerHTML = `
            <div class="text-center">
                <p class="text-sm ${success ? 'text-green-700' : 'text-red-700'}">${details}</p>
            </div>
        `;

        // Set actions
        if (success) {
            responseActions.innerHTML = `
                <button onclick="hideSendCreditModal()" 
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">
                    Close
                </button>
            `;
        } else {
            responseActions.innerHTML = `
                <button onclick="sendCreditBack()" 
                    class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                    Try Again
                </button>
                <button onclick="hideSendCreditModal()" 
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">
                    Close
                </button>
            `;
        }

        // Update titles
        document.getElementById('sendCreditTitle').textContent = title;
        document.getElementById('sendCreditSubtitle').textContent = subtitle;

        // Handle auto-close for success
        if (autoClose && success) {
            autoCloseCountdown.classList.remove('hidden');
            let countdown = 30;
            const countdownTimer = document.getElementById('countdownTimer');

            autoCloseTimer = setInterval(() => {
                countdown--;
                countdownTimer.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(autoCloseTimer);
                    hideSendCreditModal();
                }
            }, 1000);
        } else {
            autoCloseCountdown.classList.add('hidden');
        }

        // Animate to response step
        animateToStep('sendCreditConfirmationStep', 'sendCreditResponseStep', 8);
    }

    // Real-time amount validation
    document.getElementById('sendCreditAmount').addEventListener('input', function (e) {
        const value = e.target.value;
        const errorDiv = document.getElementById('amountError');

        if (value && parseFloat(value) < 500) {
            e.target.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
            errorDiv.textContent = 'Amount must be at least 500 UGX';
            errorDiv.classList.remove('hidden');
        } else {
            e.target.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
            errorDiv.classList.add('hidden');
        }
    });
</script>