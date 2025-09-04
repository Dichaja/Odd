<div id="sendCreditModal" x-data="sendCredit()" x-init="init()" x-show="open" x-cloak @keydown.escape.prevent="close()"
    class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300" @click="close()"></div>
    <div id="sendCreditModalContent"
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden transform transition-all duration-300 scale-95 opacity-0"
        :class="{'modal-show': open}">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <button x-show="canGoBack" @click="back()"
                        class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-white/10 grid place-items-center hover:bg-gray-200 dark:hover:bg-white/20">
                        <i data-lucide="chevron-left" class="w-5 h-5 text-gray-600 dark:text-white/80"></i>
                    </button>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white" x-text="title"></h3>
                        <p class="text-sm text-gray-text dark:text-white/70" x-text="subtitle"></p>
                    </div>
                </div>
                <button @click="close()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10">
                    <i data-lucide="x" class="w-5 h-5 text-gray-500"></i>
                </button>
            </div>

            <div x-show="step==='type'" x-transition>
                <div class="grid grid-cols-1 gap-3">
                    <div class="selector-item" @click="selectType('vendor')">
                        <div>
                            <div class="selector-title text-secondary dark:text-white">Vendor Wallet</div>
                            <div class="selector-sub text-gray-text dark:text-white/70">Send to a business/vendor wallet
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                    </div>
                    <div class="selector-item" @click="selectType('user')">
                        <div>
                            <div class="selector-title text-secondary dark:text-white">User Wallet</div>
                            <div class="selector-sub text-gray-text dark:text-white/70">Send to a personal/user wallet
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div x-show="step==='search'" x-transition>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-secondary dark:text-white mb-2">Search By</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="id" class="sr-only peer" x-model="searchType">
                            <div
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-secondary dark:text-white transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center justify-center">
                                Account No.
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="searchType" value="name" class="sr-only peer"
                                x-model="searchType">
                            <div
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border-2 border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-secondary dark:text-white transition-all duration-200 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white hover:border-primary/50 hover:bg-primary/5 flex items-center justify-center">
                                Name
                            </div>
                        </label>
                    </div>
                </div>

                <form @submit.prevent="performSearch" class="grid gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-secondary dark:text-white mb-2"
                            x-text="`Search ${typeLabel} Wallet by ${searchType === 'id' ? 'Account No.' : 'Name'}`"></label>
                        <input id="sendCreditSearchInput" type="text" autocomplete="off" class="form-input"
                            :placeholder="searchType==='id' ? 'Enter account number...' : 'Enter wallet name...'"
                            x-model.trim="query" required>
                        <div class="hidden mt-1 text-sm text-red-600" :class="{'hidden': !errors.search}"
                            x-text="errors.search"></div>
                    </div>
                    <button type="submit"
                        class="w-full px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium flex items-center justify-center gap-2"
                        :disabled="loading.search">
                        <template x-if="!loading.search"><span>Search Wallet</span></template>
                        <template x-if="loading.search"><i data-lucide="loader-2"
                                class="w-4 h-4 animate-spin"></i></template>
                    </button>
                </form>
            </div>

            <div x-show="step==='multi'" x-transition>
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Multiple Wallets Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Select the correct wallet from the list</p>
                </div>
                <div class="space-y-3 mb-5 max-h-64 overflow-y-auto">
                    <template x-for="w in results" :key="w.wallet_number">
                        <div class="selector-item" @click="selectWallet(w)">
                            <div>
                                <div class="selector-title text-secondary dark:text-white" x-text="w.wallet_name"></div>
                                <div class="selector-sub text-gray-text dark:text-white/70"
                                    x-text="`Account No: ${w.wallet_number}`"></div>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                        </div>
                    </template>
                </div>
                <div class="flex gap-3">
                    <button @click="go('search')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Search
                        Again</button>
                </div>
            </div>

            <div x-show="step==='notfound'" x-transition>
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Wallet Not Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">No wallet matches your search criteria</p>
                </div>
                <div
                    class="bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10 rounded-xl p-4 mb-5 text-red-800 dark:text-red-300 text-sm">
                    <div class="space-y-1">
                        <p><span class="font-medium">Type:</span> <span x-text="typeLabel"></span> Wallet</p>
                        <p><span class="font-medium">Search By:</span> <span
                                x-text="searchType==='id' ? 'Account No.' : 'Name'"></span></p>
                        <p><span class="font-medium">Query:</span> "<span x-text="query"></span>"</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button @click="go('search')"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Search
                        Again</button>
                    <button @click="go('type')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Change
                        Type</button>
                </div>
            </div>

            <div x-show="step==='confirm'" x-transition>
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Wallet Found</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Please confirm this is the correct wallet</p>
                </div>
                <div class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4 mb-5">
                    <p class="font-semibold text-secondary dark:text-white" x-text="wallet?.wallet_name"></p>
                    <p class="text-sm text-gray-text dark:text-white/70"
                        x-text="`Account No: ${wallet?.wallet_number || ''}`"></p>
                </div>
                <div class="flex gap-3">
                    <button @click="go('search')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Search
                        Again</button>
                    <button @click="go('amount')"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium">Confirm
                        Wallet</button>
                </div>
            </div>

            <div x-show="step==='amount'" x-transition>
                <form @submit.prevent="checkBalance" class="grid gap-4">
                    <div>
                        <label for="sendCreditAmount"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Enter Amount to
                            Send</label>
                        <div class="relative">
                            <input id="sendCreditAmount" type="number" min="500" step="1" autocomplete="off"
                                class="form-input pr-16 text-lg font-semibold" placeholder="500" x-model.number="amount"
                                required>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">UGX</div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-white/60 mt-1">Minimum amount: 500 UGX</p>
                        <div class="hidden mt-1 text-sm text-red-600" :class="{'hidden': !errors.amount}"
                            x-text="errors.amount"></div>
                    </div>
                    <div
                        class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4 mb-2">
                        <div class="space-y-1">
                            <p class="font-medium text-secondary dark:text-white" x-text="wallet?.wallet_name"></p>
                            <p class="text-xs text-gray-text dark:text-white/70"
                                x-text="`Account No: ${wallet?.wallet_number || ''}`"></p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="back()"
                            class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Back</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium flex items-center justify-center gap-2"
                            :disabled="loading.balance">
                            <template x-if="!loading.balance"><span>Continue</span></template>
                            <template x-if="loading.balance"><i data-lucide="loader-2"
                                    class="w-4 h-4 animate-spin"></i></template>
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="step==='balance'" x-transition>
                <div class="text-center mb-5">
                    <div class="w-14 h-14 rounded-full grid place-items-center mx-auto mb-3"
                        :class="balance.ok ? 'bg-green-100' : 'bg-red-100'">
                        <i :data-lucide="balance.ok ? 'check' : 'alert-triangle'" class="w-5 h-5"
                            :class="balance.ok ? 'text-green-600' : 'text-red-600'"></i>
                    </div>
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1"
                        x-text="balance.ok ? 'Balance Sufficient' : 'Insufficient Balance'"></h4>
                    <p class="text-sm text-gray-text dark:text-white/70"
                        x-text="balance.ok ? 'You have enough balance for this transfer' : `You don't have enough balance for this transfer`">
                    </p>
                </div>
                <div class="rounded-xl p-4 mb-5"
                    :class="balance.ok ? 'bg-green-50 dark:bg-white/5 border border-green-200 dark:border-white/10' : 'bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10'">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span :class="balance.ok ? 'text-green-700' : 'text-red-700'">Transfer Amount:</span>
                            <span :class="balance.ok ? 'text-green-900' : 'text-red-900'" class="font-semibold"
                                x-text="format(amount) + ' UGX'"></span>
                        </div>
                        <template x-if="fee>0">
                            <div>
                                <div class="flex justify-between">
                                    <span :class="balance.ok ? 'text-green-700' : 'text-red-700'"
                                        x-text="feeLabel"></span>
                                    <span :class="balance.ok ? 'text-green-900' : 'text-red-900'" class="font-semibold"
                                        x-text="format(fee) + ' UGX'"></span>
                                </div>
                                <div class="flex justify-between border-t pt-2"
                                    :class="balance.ok ? 'border-green-300' : 'border-red-300'">
                                    <span :class="balance.ok ? 'text-green-800' : 'text-red-800'"
                                        class="font-medium">Total Required:</span>
                                    <span :class="balance.ok ? 'text-green-900' : 'text-red-900'" class="font-bold"
                                        x-text="format(totalRequired) + ' UGX'"></span>
                                </div>
                            </div>
                        </template>
                        <div class="flex justify-between border-t pt-2"
                            :class="balance.ok ? 'border-green-300' : 'border-red-300'">
                            <span :class="balance.ok ? 'text-green-800' : 'text-red-800'" class="font-medium">Available
                                Balance:</span>
                            <span :class="balance.ok ? 'text-green-900' : 'text-red-900'" class="font-bold"
                                x-text="format(balance.available) + ' UGX'"></span>
                        </div>
                        <div class="flex justify-between" x-show="!balance.ok">
                            <span class="text-red-800 font-medium">Shortfall:</span>
                            <span class="text-red-900 font-bold"
                                x-text="format(totalRequired - balance.available) + ' UGX'"></span>
                        </div>
                        <div class="flex justify-between" x-show="balance.ok">
                            <span class="font-medium text-green-800">Remaining After:</span>
                            <span class="font-bold text-green-900"
                                x-text="format(balance.available - totalRequired) + ' UGX'"></span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3" x-show="balance.ok">
                    <button @click="go('amount')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Change
                        Amount</button>
                    <button @click="go('password')"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Proceed
                        to Confirm</button>
                </div>
                <div class="flex gap-3" x-show="!balance.ok">
                    <button @click="go('amount')"
                        class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 font-medium">Change
                        Amount</button>
                    <button @click="topUp()"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Top
                        Up Wallet</button>
                </div>
            </div>

            <div x-show="step==='password'" x-transition>
                <form @submit.prevent="verifyPassword" class="grid gap-4">
                    <div class="bg-blue-50 dark:bg-white/5 border border-blue-200 dark:border-white/10 rounded-xl p-4">
                        <div class="text-sm">
                            <p class="font-medium text-blue-900 dark:text-white">Logged in as</p>
                            <p class="text-blue-700 dark:text-white/80">
                                <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Unknown') ?>
                            </p>
                        </div>
                    </div>
                    <div>
                        <label for="confirmPassword"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Enter Your
                            Password</label>
                        <div class="relative">
                            <input id="confirmPassword" type="password" autocomplete="current-password"
                                class="form-input pr-10" placeholder="Enter your password..." x-model="password"
                                required>
                            <button type="button" @click="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :data-lucide="showPwd ? 'eye-off' : 'eye'" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div class="hidden mt-1 text-sm text-red-600" :class="{'hidden': !errors.password}"
                            x-text="errors.password"></div>
                        <div class="hidden mt-1 text-sm text-orange-600" :class="{'hidden': attemptsRemaining===null}"
                            x-text="attemptsRemainingText"></div>
                    </div>
                    <div class="bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10 rounded-xl p-4"
                        x-html="passwordSummary"></div>
                    <div class="flex gap-3">
                        <button type="button" @click="back()"
                            class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Back</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 font-medium flex items-center justify-center gap-2"
                            :disabled="loading.verify">
                            <template x-if="!loading.verify"><span>Verify Password</span></template>
                            <template x-if="loading.verify"><i data-lucide="loader-2"
                                    class="w-4 h-4 animate-spin"></i></template>
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="step==='confirmSend'" x-transition>
                <div class="text-center mb-5">
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1">Confirm Transfer</h4>
                    <p class="text-sm text-gray-text dark:text-white/70">Please review the details before proceeding</p>
                </div>
                <div class="bg-yellow-50 dark:bg-white/5 border border-yellow-200 dark:border-white/10 rounded-xl p-4 mb-5"
                    x-html="finalSummary"></div>
                <div class="flex gap-3">
                    <button @click="go('password')"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Cancel</button>
                    <button @click="send()"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium flex items-center justify-center gap-2"
                        :disabled="loading.send">
                        <template x-if="!loading.send"><span>Confirm Send</span></template>
                        <template x-if="loading.send"><i data-lucide="loader-2"
                                class="w-4 h-4 animate-spin"></i></template>
                    </button>
                </div>
            </div>

            <div x-show="step==='response'" x-transition>
                <div class="text-center mb-5">
                    <div class="w-16 h-16 rounded-full grid place-items-center mx-auto mb-3"
                        :class="response.ok ? 'bg-green-100' : 'bg-red-100'">
                        <i :data-lucide="response.ok ? 'check' : 'x'" class="w-6 h-6"
                            :class="response.ok ? 'text-green-600' : 'text-red-600'"></i>
                    </div>
                    <h4 class="text-base font-semibold text-secondary dark:text-white mb-1" x-text="response.title">
                    </h4>
                    <p class="text-sm text-gray-text dark:text-white/70" x-text="response.subtitle"></p>
                </div>
                <div class="rounded-xl p-4 mb-5"
                    :class="response.ok ? 'bg-green-50 dark:bg-white/5 border border-green-200 dark:border-white/10' : 'bg-red-50 dark:bg-white/5 border border-red-200 dark:border-white/10'"
                    x-html="response.details"></div>
                <div class="flex gap-3" x-show="!response.ok">
                    <button @click="restart()"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-white/10 text-secondary dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-all duration-200 font-medium">Try
                        Again</button>
                    <button @click="close()"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Close</button>
                </div>
                <div x-show="response.ok">
                    <button @click="close()"
                        class="w-full px-4 py-2.5 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-all duration-200 font-medium">Close</button>
                    <div class="hidden text-center mt-2" :class="{'hidden': !autoClose}">
                        <p class="text-sm text-gray-500 dark:text-white/60">This window will close automatically in
                            <span class="font-semibold" x-text="countdown"></span> seconds</p>
                    </div>
                </div>
            </div>

            <div x-show="step==='blocked'" x-transition>
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
                    <button @click="close()"
                        class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none
    }

    .selector-item {
        border: 1px solid rgb(229 231 235);
        border-radius: .75rem;
        padding: .875rem 1rem;
        background: #fff;
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
        background: #fff;
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
        color: #fff;
        border-color: rgba(255, 255, 255, .2)
    }

    .dark .form-input::placeholder {
        color: rgba(255, 255, 255, .6)
    }

    .modal-show {
        transform: scale(1) !important;
        opacity: 1 !important
    }
</style>

<script>
    const sendCreditApiUrl = <?= json_encode(BASE_URL . 'account/fetch/manageSendCredit.php') ?>;
    function sendCredit() {
        return {
            open: false,
            step: 'type',
            title: 'Send Credit',
            subtitle: 'Select destination wallet type',
            canGoBack: false,
            type: null,
            get typeLabel() { return this.type === 'vendor' ? 'Vendor' : 'User' },
            searchType: 'id',
            query: '',
            results: [],
            wallet: null,
            amount: null,
            fee: 0,
            totalRequired: 0,
            feeSettings: null,
            feeLabel: 'Transfer Fee',
            balance: { ok: false, available: 0 },
            password: '',
            showPwd: false,
            securityToken: null,
            loading: { search: false, balance: false, verify: false, send: false },
            errors: { search: '', amount: '', password: '' },
            autoClose: false,
            countdown: 30,
            countdownTimer: null,
            response: { ok: false, title: '', subtitle: '', details: '' },
            attemptsKey: 'sendCredit_attempts',
            attemptsMax: 3,
            attemptsRemaining: null,
            get attemptsRemainingText() { return this.attemptsRemaining === null ? '' : `Warning: ${this.attemptsRemaining} attempt${this.attemptsRemaining !== 1 ? 's' : ''} remaining before account is blocked.`; },
            init() {
                this.$nextTick(() => { this.renderIcons() });
            },
            renderIcons() { window.lucide && window.lucide.createIcons(); },
            show() { this.resetAll(); this.open = true; this.$nextTick(() => { this.renderIcons() }); },
            close() { this.open = false; this.stopCountdown(); },
            restart() { this.go('search'); },
            go(step) {
                this.step = step;
                this.title = this.getTitleFor(step);
                this.subtitle = this.getSubtitleFor(step);
                this.canGoBack = !['type', 'response', 'blocked'].includes(step);
                this.$nextTick(() => { this.renderIcons() });
            },
            back() {
                if (this.step === 'search') { this.go('type') }
                else if (this.step === 'multi') { this.go('search') }
                else if (this.step === 'notfound') { this.go('search') }
                else if (this.step === 'confirm') { this.go('search') }
                else if (this.step === 'amount') { this.go('confirm') }
                else if (this.step === 'balance') { this.go('amount') }
                else if (this.step === 'password') { this.go('balance') }
                else if (this.step === 'confirmSend') { this.go('password') }
                else if (this.step === 'response') { this.close() }
            },
            getTitleFor(step) {
                if (step === 'type') return 'Send Credit';
                if (step === 'search') return `Send to ${this.typeLabel}`;
                if (step === 'multi') return 'Multiple Results';
                if (step === 'notfound') return 'Wallet Not Found';
                if (step === 'confirm') return 'Confirm Wallet';
                if (step === 'amount') return 'Enter Amount';
                if (step === 'balance') return this.balance.ok ? 'Balance Sufficient' : 'Insufficient Balance';
                if (step === 'password') return 'Confirm with Password';
                if (step === 'confirmSend') return 'Confirm Transfer';
                if (step === 'response') return this.response.title || 'Result';
                if (step === 'blocked') return 'Access Blocked';
                return 'Send Credit';
            },
            getSubtitleFor(step) {
                if (step === 'type') return 'Select destination wallet type';
                if (step === 'search') return `Search for ${this.typeLabel.toLowerCase()} wallet`;
                if (step === 'multi') return `Found ${this.results.length} matching wallets`;
                if (step === 'notfound') return 'No matching wallet found';
                if (step === 'confirm') return 'Verify this is the correct wallet';
                if (step === 'amount') return 'Specify the amount to send';
                if (step === 'balance') return this.balance.ok ? 'You have enough balance for this transfer' : `You don't have enough balance for this transfer`;
                if (step === 'password') return 'Enter your password to authorize';
                if (step === 'confirmSend') return 'Review details before sending';
                if (step === 'response') return this.response.subtitle || '';
                if (step === 'blocked') return 'Contact administrator for assistance';
                return '';
            },
            selectType(t) { this.type = t; this.query = ''; this.searchType = 'id'; this.go('search') },
            async performSearch() {
                this.errors.search = '';
                if (!this.query) { this.errors.search = 'Please enter a search term'; return }
                this.loading.search = true;
                try {
                    const fd = new FormData();
                    fd.append('action', 'searchWallet');
                    fd.append('type', this.type);
                    fd.append('searchType', this.searchType);
                    fd.append('searchValue', this.query);
                    const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    const wallets = Array.isArray(d.wallets) ? d.wallets : (d.wallet ? [d.wallet] : []);
                    if (d.success && wallets.length > 1) { this.results = wallets; this.go('multi') }
                    else if (d.success && wallets.length === 1) { this.selectWallet(wallets[0]) }
                    else { this.go('notfound') }
                } catch (_) { this.errors.search = 'Error searching for wallet. Please try again.' }
                finally { this.loading.search = false; this.$nextTick(() => this.renderIcons()) }
            },
            selectWallet(w) { this.wallet = w; this.go('confirm') },
            async fetchFeeSettings() {
                try {
                    const fd = new FormData(); fd.append('action', 'getTransferFeeSettings');
                    const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) { this.feeSettings = d.feeSettings; this.feeLabel = this.feeSettings.setting_type === 'flat' ? 'Transfer Fee (Flat)' : `Transfer Fee (${this.feeSettings.setting_value}%)` }
                } catch (_) { }
            },
            calcFee(amount) {
                if (!this.feeSettings) return 0;
                if (this.feeSettings.setting_type === 'flat') return parseFloat(this.feeSettings.setting_value || 0);
                if (this.feeSettings.setting_type === 'percentage') return (parseFloat(amount || 0) * parseFloat(this.feeSettings.setting_value || 0)) / 100;
                return 0;
            },
            async checkBalance() {
                this.errors.amount = '';
                if (!this.amount || Number(this.amount) < 500) { this.errors.amount = 'Please enter a valid amount (minimum 500 UGX)'; return }
                if (!this.wallet) { this.errors.amount = 'No destination wallet selected'; return }
                if (!this.feeSettings) await this.fetchFeeSettings();
                this.loading.balance = true;
                try {
                    const fd = new FormData();
                    fd.append('action', 'validateTransferBalance');
                    fd.append('amount', this.amount);
                    const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) {
                        this.fee = Number(d.validation.fee || 0);
                        this.totalRequired = Number(d.validation.totalRequired || this.amount);
                        this.balance = { ok: !!d.validation.isValid, available: Number(d.validation.availableBalance || 0) };
                        this.go('balance');
                    } else {
                        this.balance = { ok: false, available: 0 };
                        this.go('balance');
                    }
                } catch (_) {
                    this.balance = { ok: false, available: 0 };
                    this.go('balance');
                } finally {
                    this.loading.balance = false; this.$nextTick(() => this.renderIcons());
                }
            },
            passwordSummary() {
                const feeLine = this.fee > 0 ? `<div class='flex justify-between'><span class='text-sm font-medium text-gray-600 dark:text-white/70'>Transfer Fee:</span><span class='text-sm font-semibold text-secondary dark:text-white'>${this.format(this.fee)} UGX</span></div><div class='flex justify-between border-t border-gray-200 dark:border-white/10 pt-2'><span class='text-sm font-bold text-gray-800 dark:text-white'>Total Required:</span><span class='text-sm font-bold text-secondary dark:text-white'>${this.format(this.totalRequired)} UGX</span></div>` : '';
                return `
            <div class="space-y-2 text-sm">
                <div class="text-center pb-3 border-b border-gray-200">
                    <p class="text-lg font-bold text-secondary dark:text-white">${this.format(this.amount)} UGX</p>
                    <p class="text-xs text-gray-text dark:text-white/70">to ${this.typeLabel} Wallet</p>
                </div>
                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-gray-600 dark:text-white/70">Recipient:</span>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-secondary dark:text-white">${this.wallet?.wallet_name || ''}</p>
                        <p class="text-xs text-gray-text dark:text-white/60">Account: ${this.wallet?.wallet_number || ''}</p>
                    </div>
                </div>
                ${feeLine}
            </div>`;
            },
            togglePassword() { this.showPwd = !this.showPwd; const el = document.getElementById('confirmPassword'); if (el) { el.type = this.showPwd ? 'text' : 'password'; } this.$nextTick(() => this.renderIcons()) },
            getAttempts() {
                try { return JSON.parse(localStorage.getItem(this.attemptsKey)) || { count: 0, ts: Date.now() } } catch (_) { return { count: 0, ts: Date.now() } }
            },
            setAttempts(obj) { localStorage.setItem(this.attemptsKey, JSON.stringify(obj)) },
            resetAttempts() { localStorage.removeItem(this.attemptsKey); this.attemptsRemaining = null },
            async verifyPassword() {
                this.errors.password = '';
                if (!this.password) { this.errors.password = 'Please enter your password'; return }
                const a = this.getAttempts();
                if (a.count >= this.attemptsMax) { this.go('blocked'); return }
                this.loading.verify = true;
                try {
                    const fd = new FormData(); fd.append('action', 'verifyPassword'); fd.append('password', this.password);
                    const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) {
                        this.securityToken = d.token;
                        this.resetAttempts();
                        this.go('confirmSend');
                    } else {
                        a.count += 1; a.ts = Date.now(); this.setAttempts(a);
                        if (a.count >= this.attemptsMax) { this.errors.password = 'Too many failed attempts. Access blocked.'; setTimeout(() => this.go('blocked'), 800) }
                        else { this.attemptsRemaining = this.attemptsMax - a.count; this.errors.password = `Incorrect password. ${this.attemptsRemaining} attempt${this.attemptsRemaining !== 1 ? 's' : ''} remaining.`; }
                    }
                } catch (_) {
                    this.errors.password = 'Error verifying password. Please try again.';
                } finally {
                    this.loading.verify = false; this.password = ''; this.$nextTick(() => this.renderIcons());
                }
            },
            finalSummary() {
                const feeLine = this.fee > 0 ? `<div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Transfer Fee:</span><span class="text-sm font-bold text-secondary dark:text-white">${this.format(this.fee)} UGX</span></div><div class="flex justify-between border-t border-yellow-300 pt-2"><span class="text-sm font-bold text-gray-800 dark:text-white">Total Deducted:</span><span class="text-sm font-bold text-secondary dark:text-white">${this.format(this.totalRequired)} UGX</span></div>` : '';
                return `
            <div class="space-y-3 text-sm">
                <div class="text-center pb-3 border-b border-yellow-200">
                    <h5 class="font-semibold text-secondary dark:text-white mb-1">Transfer Summary</h5>
                    <p class="text-2xl font-bold text-secondary dark:text-white">${this.format(this.amount)} UGX</p>
                </div>
                <div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Transfer Type:</span><span class="text-sm font-semibold text-secondary dark:text-white">Zzimba Credit - ${this.typeLabel}</span></div>
                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-gray-600 dark:text-white/70">Recipient:</span>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-secondary dark:text-white">${this.wallet?.wallet_name || ''}</p>
                        <p class="text-xs text-gray-text dark:text-white/60">Account: ${this.wallet?.wallet_number || ''}</p>
                    </div>
                </div>
                <div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Amount:</span><span class="text-sm font-bold text-secondary dark:text-white">${this.format(this.amount)} UGX</span></div>
                ${feeLine}
                <div class="flex justify-between"><span class="text-sm font-medium text-gray-600 dark:text-white/70">Status:</span><span class="text-sm font-semibold text-green-700">✓ Password Verified</span></div>
            </div>`;
            },
            async send() {
                if (!this.securityToken) { this.showResult(false, 'Error', 'Security verification required', 'Please verify your password again'); return }
                this.loading.send = true;
                try {
                    const fd = new FormData();
                    fd.append('action', 'sendCredit');
                    fd.append('wallet_to', this.wallet.wallet_number);
                    fd.append('amount', this.amount);
                    fd.append('security_token', this.securityToken);
                    const r = await fetch(sendCreditApiUrl, { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.success) {
                        const html = this.successDetails({
                            transactionId: d.transaction_id || 'N/A',
                            amount: this.amount,
                            fee: this.fee,
                            totalDeducted: this.totalRequired,
                            recipient: this.wallet.wallet_name,
                            recipientAccount: this.wallet.wallet_number,
                            newBalance: d.balance
                        });
                        this.showResult(true, 'Transfer Successful!', 'Credit has been sent successfully', html, true);
                    } else {
                        this.showResult(false, 'Transfer Failed', d.message || 'The transfer could not be completed', 'Please check your details and try again')
                    }
                } catch (_) {
                    this.showResult(false, 'Connection Error', 'Unable to process the transfer', 'Please check your internet connection and try again')
                } finally {
                    this.loading.send = false;
                    this.securityToken = null;
                    this.$nextTick(() => this.renderIcons());
                }
            },
            successDetails(d) {
                const feeBlock = this.fee > 0 ? `
                <div><p class="text-green-600 font-medium">Transfer Fee</p><p class="text-green-800 font-bold">${this.format(d.fee)} UGX</p></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-green-600 font-medium">Total Deducted</p><p class="text-green-800 font-bold">${this.format(d.totalDeducted)} UGX</p></div>` : `
            </div>
            <div class="grid grid-cols-2 gap-4">`;
                return `
            <div class="space-y-3 text-sm">
                <div class="text-center pb-3 border-b border-green-200">
                    <div class="flex items-center justify-center gap-2 mb-1">
                        <span class="font-semibold text-green-800">Transaction Completed</span>
                    </div>
                    <p class="text-green-700 text-xs">Transaction ID: ${d.transactionId}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-green-600 font-medium">Amount Sent</p><p class="text-green-800 font-bold">${this.format(d.amount)} UGX</p></div>
                    ${feeBlock}
                    <div><p class="text-green-600 font-medium">Available Balance</p><p class="text-green-800 font-bold">${d.newBalance ? this.format(d.newBalance) : 'N/A'} UGX</p></div>
                </div>
                <div class="pt-2 border-t border-green-200">
                    <p class="text-green-600 font-medium text-sm">Sent to:</p>
                    <p class="text-green-800 font-semibold">${d.recipient}</p>
                    <p class="text-green-600 text-xs">Account: ${d.recipientAccount}</p>
                </div>
            </div>`;
            },
            showResult(ok, title, subtitle, details, autoClose = false) {
                this.response.ok = ok;
                this.response.title = title;
                this.response.subtitle = subtitle;
                this.response.details = typeof details === 'string' ? `<div class="text-center text-sm ${ok ? 'text-green-700' : 'text-red-700'}">${details}</div>` : details;
                this.autoClose = !!autoClose;
                if (this.autoClose) { this.startCountdown() } else { this.stopCountdown() }
                this.go('response');
            },
            startCountdown() {
                this.stopCountdown();
                this.countdown = 30;
                this.countdownTimer = setInterval(() => {
                    this.countdown--;
                    if (this.countdown <= 0) { this.stopCountdown(); this.close() }
                }, 1000);
            },
            stopCountdown() { if (this.countdownTimer) { clearInterval(this.countdownTimer); this.countdownTimer = null } },
            topUp() { this.close(); window.location.href = 'zzimba-credit' },
            resetAll() {
                this.step = 'type'; this.title = this.getTitleFor('type'); this.subtitle = this.getSubtitleFor('type');
                this.canGoBack = false; this.type = null; this.searchType = 'id'; this.query = ''; this.results = [];
                this.wallet = null; this.amount = null; this.fee = 0; this.totalRequired = 0; this.balance = { ok: false, available: 0 };
                this.password = ''; this.showPwd = false; this.securityToken = null;
                this.loading = { search: false, balance: false, verify: false, send: false };
                this.errors = { search: '', amount: '', password: '' }; this.response = { ok: false, title: '', subtitle: '', details: '' };
                this.autoClose = false; this.stopCountdown(); this.attemptsRemaining = null;
            },
            format(n) { const x = Number(n || 0); return x.toLocaleString('en-UG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }
        }
    }
    (function () {
        function getSendCreditCmp() {
            const el = document.getElementById('sendCreditModal');
            if (!el) return null;
            if (window.Alpine && typeof Alpine.$data === 'function') return Alpine.$data(el);
            return el.__x && el.__x.$data ? el.__x.$data : null;
        }
        function registerShim() {
            if (window.__sendCreditShimReady) return;
            window.__sendCreditShimReady = true;
            window.showSendCreditModal = function () {
                const cmp = getSendCreditCmp();
                const el = document.getElementById('sendCreditModal');
                if (el && el.classList.contains('hidden')) el.classList.remove('hidden');
                if (cmp && typeof cmp.show === 'function') { cmp.show(); if (window.lucide) { window.lucide.createIcons(); } }
            };
            window.hideSendCreditModal = function () {
                const cmp = getSendCreditCmp();
                if (cmp && typeof cmp.close === 'function') { cmp.close(); }
            };
            window.sendCreditBack = function () {
                const cmp = getSendCreditCmp();
                if (cmp && typeof cmp.back === 'function') { cmp.back(); }
            };
        }
        document.addEventListener('alpine:init', registerShim);
        document.addEventListener('DOMContentLoaded', function () { if (window.Alpine) registerShim(); });
    })();
</script>