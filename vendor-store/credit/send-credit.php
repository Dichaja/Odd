<div x-data="sendCredit()" x-init="init()">
    <template x-teleport="body">
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-[1200] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="hide()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="p-6 border-b flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button x-show="step>1 && step<8" @click="back()"
                                class="w-10 h-10 rounded-xl bg-gray-100 grid place-items-center hover:bg-gray-200">
                                <i data-lucide="chevron-left" class="w-5 h-5 text-gray-700"></i>
                            </button>
                            <div class="w-12 h-12 bg-gray-100 rounded-xl grid place-items-center">
                                <i data-lucide="send" class="w-6 h-6 text-gray-900"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900" x-text="title"></h3>
                                <p class="text-sm text-gray-500" x-text="subtitle"></p>
                            </div>
                        </div>
                        <button @click="hide()" class="p-2 rounded-lg hover:bg-gray-100">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <div x-show="step===1" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="grid grid-cols-1 gap-4">
                                <button @click="selectType('vendor')"
                                    class="w-full px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl flex items-center justify-center gap-2">
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                    Vendor Wallet
                                </button>
                                <button @click="selectType('user')"
                                    class="w-full px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl flex items-center justify-center gap-2">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                    User Wallet
                                </button>
                            </div>
                        </div>

                        <div x-show="step===2" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search By</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" class="sr-only" value="id" x-model="searchBy">
                                        <div :class="searchBy==='id' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                                            class="w-full px-4 py-3 text-sm font-medium rounded-lg border flex items-center gap-2">
                                            <i data-lucide="hash" class="w-4 h-4"></i>
                                            Account No.
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" class="sr-only" value="name" x-model="searchBy">
                                        <div :class="searchBy==='name' ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
                                            class="w-full px-4 py-3 text-sm font-medium rounded-lg border flex items-center gap-2">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                            Name
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <form @submit.prevent="search()">
                                <label class="block text-sm font-medium text-gray-700 mb-2"
                                    x-text="`Search ${typeLabel} Wallet by ${searchBy==='id'?'Account No.':'Name'}`"></label>
                                <div class="relative">
                                    <input x-model.trim="searchTerm"
                                        :placeholder="searchBy==='id'?'Enter account number...':'Enter wallet name...'"
                                        :class="errors.search ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                        class="w-full px-4 py-3 pr-10 border rounded-xl transition">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <template x-if="!loadingSearch"><i data-lucide="search"
                                                class="w-4 h-4 text-gray-400"></i></template>
                                        <template x-if="loadingSearch"><i data-lucide="loader-2"
                                                class="w-4 h-4 text-gray-400 animate-spin"></i></template>
                                    </div>
                                </div>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.search" x-text="errors.search"></p>
                                <button type="submit" class="mt-4 w-full px-4 py-3 rounded-xl text-white"
                                    :class="loadingSearch ? 'bg-gray-400 cursor-not-allowed' : 'bg-gray-900 hover:bg-black'">
                                    <span x-show="!loadingSearch" class="inline-flex items-center gap-2">
                                        <i data-lucide="search" class="w-4 h-4"></i> Search Wallet
                                    </span>
                                    <span x-show="loadingSearch" class="inline-flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Searching...
                                    </span>
                                </button>
                            </form>
                        </div>

                        <div x-show="step===3" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-blue-100 rounded-full grid place-items-center mx-auto mb-4">
                                    <i data-lucide="list" class="w-7 h-7 text-blue-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Multiple Wallets Found</h4>
                                <p class="text-sm text-gray-500">Select the correct wallet from the results below</p>
                            </div>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <template x-for="w in results" :key="w.wallet_number">
                                    <button @click="selectFound(w)"
                                        class="w-full border border-gray-200 hover:border-gray-900 hover:bg-gray-50 rounded-xl p-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-gray-100 rounded-xl grid place-items-center">
                                                <i :data-lucide="type==='vendor' ? 'store' : 'user'"
                                                    class="w-6 h-6 text-gray-700"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900" x-text="w.wallet_name"></div>
                                                <div class="text-sm text-gray-500">Account No: <span
                                                        x-text="w.wallet_number"></span></div>
                                            </div>
                                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-900"></i>
                                        </div>
                                    </button>
                                </template>
                            </div>
                            <div class="mt-4">
                                <button @click="searchAgain()"
                                    class="w-full px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Search
                                    Again</button>
                            </div>
                        </div>

                        <div x-show="step===4" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-red-100 rounded-full grid place-items-center mx-auto mb-4">
                                    <i data-lucide="alert-triangle" class="w-7 h-7 text-red-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Wallet Not Found</h4>
                                <p class="text-sm text-gray-500">No wallet matches your search</p>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
                                <div class="font-medium text-red-900 mb-1">Search Details</div>
                                <div class="space-y-1">
                                    <div><span class="font-medium">Type:</span> <span
                                            x-text="typeLabel+' Wallet'"></span></div>
                                    <div><span class="font-medium">Search By:</span> <span
                                            x-text="searchBy==='id'?'Account No.':'Name'"></span></div>
                                    <div><span class="font-medium">Query:</span> "<span
                                            x-text="lastSearch?.query||''"></span>"</div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <button @click="searchAgain()"
                                    class="px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl">Search
                                    Again</button>
                                <button @click="changeType()"
                                    class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Change
                                    Type</button>
                            </div>
                        </div>

                        <div x-show="step===5" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-green-100 rounded-full grid place-items-center mx-auto mb-4">
                                    <i data-lucide="check" class="w-7 h-7 text-green-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Wallet Found</h4>
                                <p class="text-sm text-gray-500">Confirm this is the correct wallet</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 rounded-xl grid place-items-center">
                                        <i :data-lucide="type==='vendor' ? 'store' : 'user'"
                                            class="w-6 h-6 text-gray-700"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900" x-text="selected?.wallet_name"></div>
                                        <div class="text-sm text-gray-500">Account No: <span
                                                x-text="selected?.wallet_number"></span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <button @click="searchAgain()"
                                    class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Search
                                    Again</button>
                                <button @click="proceedToAmount()"
                                    class="px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl">Confirm
                                    Wallet</button>
                            </div>
                        </div>

                        <div x-show="step===6" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <form @submit.prevent="confirmAmount()">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Enter Amount to Send</label>
                                <div class="relative">
                                    <input x-model.number="amount" type="number" min="500" step="1"
                                        :class="errors.amount ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                        class="w-full px-4 py-3 pr-14 border rounded-xl text-lg font-semibold"
                                        placeholder="500">
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">UGX
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Minimum amount: 500 UGX</p>
                                <p class="mt-1 text-sm text-red-600" x-show="errors.amount" x-text="errors.amount"></p>

                                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg grid place-items-center">
                                            <i :data-lucide="type==='vendor' ? 'store' : 'user'"
                                                class="w-5 h-5 text-gray-700"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900" x-text="selected?.wallet_name"></div>
                                            <div class="text-xs text-gray-500">Account No: <span
                                                    x-text="selected?.wallet_number"></span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <button type="button" @click="back()"
                                        class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Back</button>
                                    <button type="submit"
                                        class="px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl">Continue</button>
                                </div>
                            </form>
                        </div>

                        <div x-show="step===7" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-x-2"
                            x-transition:enter-end="opacity-100 translate-x-0">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-yellow-100 rounded-full grid place-items-center mx-auto mb-4">
                                    <i data-lucide="alert-circle" class="w-7 h-7 text-yellow-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Confirm Transfer</h4>
                                <p class="text-sm text-gray-500">Review the details before proceeding</p>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm space-y-3">
                                <div class="flex justify-between"><span class="text-gray-700">Amount:</span><span
                                        class="text-gray-900 font-semibold" x-text="format(amount)+' UGX'"></span></div>
                                <div class="pt-2 border-t border-yellow-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg grid place-items-center">
                                            <i :data-lucide="type==='vendor' ? 'store' : 'user'"
                                                class="w-5 h-5 text-gray-700"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900" x-text="selected?.wallet_name"></div>
                                            <div class="text-xs text-gray-600">Account No: <span
                                                    x-text="selected?.wallet_number"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-xl p-3">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 mt-0.5"></i>
                                    <p class="text-sm text-red-700">This action cannot be undone. Please verify all
                                        details are correct.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="back()"
                                    class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Cancel</button>
                                <button @click="confirmSend()" class="px-4 py-3 rounded-xl text-white"
                                    :class="confirming ? 'bg-gray-400 cursor-wait' : 'bg-red-600 hover:bg-red-700'">
                                    <span x-show="!confirming">Confirm Send</span>
                                    <span x-show="confirming" class="inline-flex items-center gap-2"><i
                                            data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                        Processing...</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="step===8" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 rounded-full grid place-items-center mx-auto mb-4"
                                    :class="response.success ? 'bg-green-100' : 'bg-red-100'">
                                    <i :data-lucide="response.success ? 'check' : 'x'"
                                        :class="response.success ? 'text-green-600' : 'text-red-600'"
                                        class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900" x-text="response.title"></h4>
                                <p class="text-sm text-gray-500" x-text="response.subtitle"></p>
                            </div>
                            <div class="rounded-xl p-4"
                                :class="response.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                                <p class="text-sm" :class="response.success ? 'text-green-700' : 'text-red-700'"
                                    x-text="response.details"></p>
                            </div>
                            <div class="mt-4" x-show="response.success">
                                <button @click="hide()"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl">Close</button>
                                <div class="text-center mt-3 text-sm text-gray-500" x-show="countdown>0">This window
                                    will close automatically in <span class="font-semibold" x-text="countdown"></span>
                                    seconds</div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3" x-show="!response.success">
                                <button @click="resetToSearch()"
                                    class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Try
                                    Again</button>
                                <button @click="hide()"
                                    class="px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl">Close</button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute inset-x-0 -bottom-1 h-1 bg-gradient-to-r from-gray-900 via-gray-700 to-gray-900">
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function sendCredit() {
        return {
            apiUrl: <?= json_encode(BASE_URL . 'vendor-store/fetch/manageSendCredit.php') ?>,
            open: false,
            step: 1,
            type: null,
            searchBy: 'id',
            searchTerm: '',
            results: [],
            selected: null,
            lastSearch: null,
            loadingSearch: false,
            amount: null,
            confirming: false,
            response: { success: null, title: '', subtitle: '', details: '' },
            countdown: 0,
            timer: null,
            get typeLabel() { return this.type === 'vendor' ? 'Vendor' : 'User'; },
            get title() {
                return this.step === 1 ? 'Send Credit'
                    : this.step === 2 ? `Send to ${this.typeLabel}`
                        : this.step === 3 ? 'Multiple Results'
                            : this.step === 4 ? 'Wallet Not Found'
                                : this.step === 5 ? 'Confirm Wallet'
                                    : this.step === 6 ? 'Enter Amount'
                                        : this.step === 7 ? 'Confirm Transfer'
                                            : 'Transfer Status';
            },
            get subtitle() {
                return this.step === 1 ? 'Select destination wallet type'
                    : this.step === 2 ? `Search for ${this.typeLabel.toLowerCase()} wallet`
                        : this.step === 3 ? `Found ${this.results.length} matching wallets`
                            : this.step === 4 ? 'No matching wallet found'
                                : this.step === 5 ? 'Verify this is the correct wallet'
                                    : this.step === 6 ? 'Specify the amount to send'
                                        : this.step === 7 ? 'Review details before sending'
                                            : this.response.subtitle || '';
            },
            errors: { search: '', amount: '' },
            init() {
                window.showSendCreditModal = () => { this.show(); };
                window.hideSendCreditModal = () => { this.hide(); };
                this.$nextTick(() => this.refreshIcons());
            },
            refreshIcons() { if (window.lucide && lucide.createIcons) lucide.createIcons(); },
            show() { this.open = true; this.reset(); this.$nextTick(() => this.refreshIcons()); },
            hide() {
                this.open = false;
                this.clearTimer();
                this.reset();
                if (window.location && window.location.reload) setTimeout(() => { }, 0);
            },
            reset() {
                this.step = 1; this.type = null; this.searchBy = 'id'; this.searchTerm = ''; this.results = [];
                this.selected = null; this.lastSearch = null; this.loadingSearch = false; this.amount = null;
                this.confirming = false; this.response = { success: null, title: '', subtitle: '', details: '' };
                this.errors = { search: '', amount: '' }; this.countdown = 0; this.clearTimer();
            },
            resetToSearch() { this.response = { success: null, title: '', subtitle: '', details: '' }; this.step = 2; this.$nextTick(() => this.refreshIcons()); },
            selectType(t) { this.type = t; this.step = 2; this.$nextTick(() => this.refreshIcons()); },
            back() {
                if (this.step === 2) { this.step = 1; }
                else if (this.step === 3 || this.step === 4) { this.step = 2; }
                else if (this.step === 5) { this.step = 2; }
                else if (this.step === 6) { this.step = 5; }
                else if (this.step === 7) { this.step = 6; }
                else if (this.step === 8) { this.hide(); }
                this.$nextTick(() => this.refreshIcons());
            },
            async search() {
                this.errors.search = '';
                if (!this.searchTerm) { this.errors.search = 'Please enter a search term'; return; }
                this.loadingSearch = true;
                this.lastSearch = { query: this.searchTerm, searchBy: this.searchBy, type: this.type };
                try {
                    const fd = new FormData();
                    fd.append('action', 'searchWallet');
                    fd.append('type', this.type);
                    fd.append('searchType', this.searchBy);
                    fd.append('searchValue', this.searchTerm);
                    const r = await fetch(this.apiUrl, { method: 'POST', body: fd });
                    const j = await r.json();
                    const wallets = Array.isArray(j.wallets) ? j.wallets : (j.wallet ? [j.wallet] : []);
                    if (j.success && wallets.length > 1) { this.results = wallets; this.step = 3; }
                    else if (j.success && wallets.length === 1) { this.selected = wallets[0]; this.step = 5; }
                    else { this.step = 4; }
                } catch (_) { this.errors.search = 'Error searching for wallet. Please try again.'; }
                this.loadingSearch = false;
                this.$nextTick(() => this.refreshIcons());
            },
            selectFound(w) { this.selected = w; this.step = 5; this.$nextTick(() => this.refreshIcons()); },
            searchAgain() { this.searchTerm = ''; this.errors.search = ''; this.step = 2; this.$nextTick(() => this.refreshIcons()); },
            changeType() { this.type = null; this.searchTerm = ''; this.errors.search = ''; this.step = 1; this.$nextTick(() => this.refreshIcons()); },
            proceedToAmount() { this.step = 6; this.$nextTick(() => this.refreshIcons()); },
            confirmAmount() {
                this.errors.amount = '';
                if (!this.amount || Number(this.amount) < 500) { this.errors.amount = 'Please enter a valid amount (minimum 500 UGX)'; return; }
                if (!this.selected || !this.selected.wallet_number) { this.errors.amount = 'No destination wallet selected'; return; }
                this.step = 7; this.$nextTick(() => this.refreshIcons());
            },
            async confirmSend() {
                if (this.confirming) return;
                this.confirming = true;
                try {
                    const fd = new FormData();
                    fd.append('action', 'sendCredit');
                    fd.append('wallet_to', this.selected.wallet_number);
                    fd.append('amount', this.amount);
                    const r = await fetch(this.apiUrl, { method: 'POST', body: fd });
                    const j = await r.json();
                    if (j.success) {
                        this.showResult(true, 'Transfer Successful!', 'Credit has been sent successfully', `Transaction ID: ${j.transaction_id || 'N/A'}`);
                        this.startCountdown(30);
                    } else {
                        this.showResult(false, 'Transfer Failed', j.message || 'The transfer could not be completed', 'Please check your details and try again');
                    }
                } catch (_) {
                    this.showResult(false, 'Connection Error', 'Unable to process the transfer', 'Please check your connection and try again');
                }
                this.confirming = false;
                this.$nextTick(() => this.refreshIcons());
            },
            showResult(ok, t, s, d) { this.response = { success: ok, title: t, subtitle: s, details: d }; this.step = 8; },
            startCountdown(sec) {
                this.clearTimer();
                this.countdown = sec;
                this.timer = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) { this.clearTimer(); this.hide(); }
                }, 1000);
            },
            clearTimer() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
            format(v) { return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(Number(v || 0)); }
        }
    }
</script>