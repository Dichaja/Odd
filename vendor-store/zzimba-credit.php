<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit';
$activeNav = 'zzimba-credit';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}

$cashAccounts = [];
try {
    $stmt = $pdo->prepare("SELECT id, name, type, provider, account_number FROM zzimba_cash_accounts WHERE status = 'active' ORDER BY type, name");
    $stmt->execute();
    $cashAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching cash accounts: " . $e->getMessage());
}

ob_start();
?>
<style>
    [x-cloak] {
        display: none !important
    }
</style>
<script>
    if (!window.$ && !window.jQuery) {
        (function () {
            function $shim(sel) { const n = typeof sel === 'string' ? document.querySelectorAll(sel) : [sel]; n.on = function (ev, cb) { n.forEach(el => el.addEventListener(ev, cb)); return n; }; n.ready = function (fn) { if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }; return n; }
            $shim.ajax = $shim.get = $shim.post = function () { return Promise.resolve(); };
            window.$ = window.jQuery = $shim;
        })();
    }
    window.addEventListener('error', function (e) { if (e && /(\$ is not defined|jQuery is not defined)/.test(e.message || '')) { e.preventDefault(); } }, true);
</script>

<div x-data="creditPage()" x-init="init()" x-cloak class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-user-primary/5 to-user-primary/10 border-b border-gray-100">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-user-primary/10 grid place-items-center">
                        <i data-lucide="wallet" class="w-7 h-7 text-user-primary"></i>
                    </div>
                    <div class="flex-1">
                        <div x-show="loadingWallet" class="animate-pulse space-y-2">
                            <div class="h-6 bg-gray-200 rounded w-56"></div>
                            <div class="h-4 bg-gray-200 rounded w-40"></div>
                            <div class="h-3 bg-gray-200 rounded w-64"></div>
                        </div>
                        <div x-show="!loadingWallet" class="space-y-1">
                            <h2 class="text-xl font-bold text-secondary" x-text="wallet.name"></h2>
                            <p class="text-gray-600" x-text="ownerName"></p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1"><i data-lucide="hash"
                                        class="w-3.5 h-3.5"></i><span x-text="wallet.number"></span></span>
                                <span class="inline-flex items-center gap-1"><i data-lucide="calendar"
                                        class="w-3.5 h-3.5"></i><span x-text="wallet.created"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:text-right">
                    <p class="text-sm font-medium text-gray-600 mb-1">Current Balance</p>
                    <div x-show="loadingWallet" class="animate-pulse">
                        <div class="h-10 bg-gray-200 rounded w-40 ml-auto mb-2"></div>
                        <div class="h-6 bg-gray-200 rounded w-24 ml-auto"></div>
                    </div>
                    <div x-show="!loadingWallet">
                        <p id="balanceText" class="text-3xl lg:text-4xl font-bold text-user-primary mb-2"
                            x-text="wallet.balance"></p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                            :class="wallet.status==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                            <i :data-lucide="wallet.status==='active' ? 'check-circle' : 'x-circle'"
                                class="w-3.5 h-3.5 mr-1"></i>
                            <span x-text="ucFirst(wallet.status)"></span>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <button @click="openTopup()"
                    class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white inline-flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i> Top up
                </button>
                <button onclick="window.showSendCreditModal && window.showSendCreditModal()"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 hover:bg-gray-50 text-gray-900 inline-flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Send Credit
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-secondary">Transaction Statement</h3>
                    <p class="text-sm text-gray-500">Recent transactions and account activity</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative" x-data="{open:false}" @keydown.escape.window="open=false">
                        <button @click="open=!open"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm flex items-center gap-2 hover:bg-gray-50">
                            <i data-lucide="eye" class="w-4 h-4"></i> View <i data-lucide="chevron-down"
                                class="w-4 h-4"></i>
                        </button>
                        <div x-show="open" x-transition @click.outside="open=false"
                            class="absolute right-0 mt-2 w-60 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                            <div class="p-3 border-b">
                                <h4 class="text-sm font-semibold text-gray-900">Show Columns</h4>
                                <p class="text-xs text-gray-500">Select at least 3 columns</p>
                            </div>
                            <div class="p-2 space-y-1">
                                <template x-for="col in allColumns" :key="col.key">
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-user-primary focus:ring-user-primary"
                                            :value="col.key" @change="toggleColumn($event)"
                                            :checked="visibleColumns.includes(col.key)">
                                        <span class="text-sm text-gray-700" x-text="col.label"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    <select x-model="dateFilter" @change="loadTransactions()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="all">All transactions</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="180">Last 6 months</option>
                        <option value="365">Last year</option>
                    </select>
                </div>
            </div>

            <div x-show="loadingTx" class="p-6">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                    <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                </div>
            </div>

            <div x-show="!loadingTx && entries.length>0" class="hidden lg:block overflow-x-auto">
                <table id="transactionsTableElement" class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
                                :class="!showCol('datetime') && 'hidden'">Date/Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
                                :class="!showCol('description') && 'hidden'">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
                                :class="!showCol('debit') && 'hidden'">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
                                :class="!showCol('credit') && 'hidden'">Credit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap"
                                :class="!showCol('balance') && 'hidden'">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(e,idx) in entries" :key="idx">
                            <tr :class="idx%2===0 ? 'bg-white' : 'bg-gray-50'">
                                <td class="px-4 py-3 text-sm whitespace-nowrap align-top"
                                    :class="!showCol('datetime') && 'hidden'">
                                    <div class="font-medium text-gray-900" x-text="e.date"></div>
                                    <div class="text-xs text-gray-500" x-text="e.time"></div>
                                </td>
                                <td class="px-4 py-3 text-sm align-top max-w-[28ch]"
                                    :class="!showCol('description') && 'hidden'">
                                    <div class="font-medium text-gray-900 break-words" x-text="e.desc"></div>
                                    <div class="text-xs text-gray-500 mt-1" x-show="e.method" x-text="e.method"></div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right align-top"
                                    :class="!showCol('debit') && 'hidden'">
                                    <template x-if="e.debit>0"><span class="font-semibold text-red-600"
                                            x-text="'-'+formatCurrency(e.debit)"></span></template>
                                    <template x-if="e.debit===0"><span class="text-gray-400">-</span></template>
                                </td>
                                <td class="px-4 py-3 text-sm text-right align-top"
                                    :class="!showCol('credit') && 'hidden'">
                                    <template x-if="e.credit>0"><span class="font-semibold text-green-600"
                                            x-text="'+'+formatCurrency(e.credit)"></span></template>
                                    <template x-if="e.credit===0"><span class="text-gray-400">-</span></template>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900 align-top"
                                    :class="!showCol('balance') && 'hidden'">
                                    <span x-text="e.balance>0 ? formatCurrency(e.balance) : '-'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="!loadingTx && entries.length>0" class="lg:hidden p-4 space-y-4">
                <template x-for="(e,idx) in entries" :key="'m'+idx">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 text-sm mb-1 break-words" x-text="e.desc"></div>
                                <div class="text-xs text-gray-500" x-text="e.date+' • '+e.time"></div>
                                <div class="text-xs text-gray-500" x-show="e.method" x-text="e.method"></div>
                            </div>
                            <div class="text-right shrink-0">
                                <template x-if="e.debit>0">
                                    <div class="font-semibold text-red-600 text-sm"
                                        x-text="'-'+formatCurrency(e.debit)"></div>
                                </template>
                                <template x-if="e.credit>0">
                                    <div class="font-semibold text-green-600 text-sm"
                                        x-text="'+'+formatCurrency(e.credit)"></div>
                                </template>
                                <div class="text-xs text-gray-500 mt-1">Balance: <span
                                        x-text="e.balance>0?formatCurrency(e.balance):'-'"></span></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!loadingTx && entries.length===0" class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full grid place-items-center mx-auto mb-4">
                    <i data-lucide="receipt" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No transactions found</h3>
                <p class="text-gray-500 mb-6">Start by adding money to your wallet</p>
                <button @click="openTopup()"
                    class="px-6 py-3 bg-user-primary text-white rounded-xl hover:bg-user-primary/90">Add Money</button>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div id="alertContainer" x-cloak class="fixed top-4 right-4 z-[1100] space-y-2 pointer-events-none"></div>
    </template>

    <template x-teleport="body">
        <div x-show="topup.open" x-cloak x-transition.opacity class="fixed inset-0 z-[1200] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="closeTopup()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
                    <div class="p-6 border-b flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button x-show="topup.step>1 && topup.step<4" @click="topupBack()"
                                class="w-10 h-10 rounded-xl bg-gray-100 grid place-items-center hover:bg-gray-200">
                                <i data-lucide="chevron-left" class="w-5 h-5"></i>
                            </button>
                            <div class="w-12 h-12 bg-gray-100 rounded-xl grid place-items-center">
                                <i data-lucide="credit-card" class="w-6 h-6 text-gray-900"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900" x-text="topupTitle"></h3>
                                <p class="text-sm text-gray-500" x-text="topupSubtitle"></p>
                            </div>
                        </div>
                        <button @click="closeTopup()" class="p-2 rounded-lg hover:bg-gray-100">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <div x-show="topup.step===1" x-transition>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <template x-for="acc in topup.accounts" :key="acc.id">
                                    <button @click="selectTopupAccount(acc)"
                                        class="w-full border-2 border-gray-200 rounded-xl p-4 hover:border-gray-900 hover:bg-gray-50 text-left">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-gray-100 rounded-xl grid place-items-center">
                                                <i :data-lucide="acc.type==='mobile_money' ? 'smartphone' : acc.type==='gateway' ? 'credit-card' : 'banknote'"
                                                    class="w-6 h-6 text-gray-700"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900" x-text="acc.name"></div>
                                                <div class="text-sm text-gray-500" x-show="acc.type!=='gateway'">Account
                                                    No: <span x-text="acc.account_number"></span></div>
                                                <div class="text-[11px] text-gray-400 capitalize"
                                                    x-text="acc.type.replace('_',' ')"></div>
                                            </div>
                                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-900"></i>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div x-show="topup.step===2 && topup.method==='mobile_money'" x-transition>
                            <div class="space-y-4">
                                <div class="text-sm text-gray-600" x-text="fmtAccount(topup.account)"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number
                                        Used</label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                            +256</div>
                                        <input x-model="topup.mm.phone" type="tel" maxlength="9"
                                            class="w-full pl-16 pr-3 py-3 border rounded-xl"
                                            :class="topup.errors.mm_phone ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                            placeholder="771234567">
                                    </div>
                                    <p class="mt-1 text-sm text-red-600" x-show="topup.errors.mm_phone"
                                        x-text="topup.errors.mm_phone"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Sent
                                        (UGX)</label>
                                    <input x-model.number="topup.mm.amount" type="number" min="500" step="100"
                                        class="w-full px-3 py-3 border rounded-xl"
                                        :class="topup.errors.mm_amount ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                        placeholder="Enter amount sent">
                                    <p class="mt-1 text-sm text-red-600" x-show="topup.errors.mm_amount"
                                        x-text="topup.errors.mm_amount"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                                    <input x-model="topup.mm.txId" type="text"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900"
                                        placeholder="Enter transaction ID">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time Sent</label>
                                    <input x-model="topup.mm.when" type="datetime-local"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900">
                                </div>
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <button @click="topupBack()"
                                        class="px-4 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Back</button>
                                    <button @click="topupConfirmPrep()"
                                        class="px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl">Continue</button>
                                </div>
                            </div>
                        </div>

                        <div x-show="topup.step===2 && topup.method==='bank'" x-transition>
                            <div class="space-y-4">
                                <div class="text-sm text-gray-600" x-text="fmtAccount(topup.account)"></div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Deposited
                                        (UGX)</label>
                                    <input x-model.number="topup.bank.amount" type="number" min="500" step="100"
                                        class="w-full px-3 py-3 border rounded-xl"
                                        :class="topup.errors.bank_amount ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                        placeholder="Enter amount deposited">
                                    <p class="mt-1 text-sm text-red-600" x-show="topup.errors.bank_amount"
                                        x-text="topup.errors.bank_amount"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bank Reference/Receipt
                                        Number</label>
                                    <input x-model="topup.bank.reference" type="text"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900"
                                        placeholder="Enter reference number">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Depositor Name</label>
                                    <input x-model="topup.bank.depositor" type="text"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900"
                                        placeholder="Name used for deposit">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time of
                                        Deposit</label>
                                    <input x-model="topup.bank.when" type="datetime-local"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900">
                                </div>
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <button @click="topupBack()"
                                        class="px-4 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Back</button>
                                    <button @click="topupConfirmPrep()"
                                        class="px-4 py-3 bg-gray-900 hover:bg-black text-white rounded-xl">Continue</button>
                                </div>
                            </div>
                        </div>

                        <div x-show="topup.step===2 && topup.method==='gateway'" x-transition>
                            <div class="space-y-4">
                                <div class="text-sm text-gray-600" x-text="topup.account ? topup.account.name : ''">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <div class="relative">
                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">
                                            +256</div>
                                        <input x-model="topup.gateway.phone" @blur="validateMsisdn()"
                                            @input="resetGatewayPhone()" type="tel" maxlength="9"
                                            class="w-full pl-16 pr-10 py-3 border rounded-xl"
                                            :class="topup.gateway.phoneError ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                            placeholder="771234567">
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2"
                                            x-show="topup.gateway.validating">
                                            <i data-lucide="loader-2"
                                                class="w-4 h-4 animate-spin text-user-primary"></i>
                                        </div>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500">Enter exactly 9 digits (without the leading
                                        0)</div>
                                    <div class="mt-2 text-sm text-green-600" x-show="topup.gateway.customerName"
                                        x-text="'✓ '+topup.gateway.customerName"></div>
                                    <div class="mt-2 text-sm text-red-600" x-show="topup.gateway.phoneError"
                                        x-text="topup.gateway.phoneError"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (UGX)</label>
                                    <input x-model.number="topup.gateway.amount" @input="validateGatewayAmount()"
                                        type="number" min="500" step="100" class="w-full px-3 py-3 border rounded-xl"
                                        :class="topup.gateway.amountError ? 'border-red-300 focus:ring-red-200 focus:border-red-500' : 'border-gray-300 focus:ring-gray-900/20 focus:border-gray-900'"
                                        placeholder="Minimum 500">
                                    <div class="mt-2 text-sm text-red-600" x-show="topup.gateway.amountError"
                                        x-text="topup.gateway.amountError"></div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description
                                        (Optional)</label>
                                    <input x-model="topup.gateway.desc" type="text"
                                        class="w-full px-3 py-3 border rounded-xl border-gray-300 focus:ring-gray-900/20 focus:border-gray-900"
                                        placeholder="Payment description">
                                </div>
                                <template x-if="topup.gateway.status.type">
                                    <div class="p-4 rounded-xl border"
                                        :class="topup.gateway.status.type==='processing' ? 'bg-blue-50 border-blue-200' : topup.gateway.status.type==='pending' ? 'bg-yellow-50 border-yellow-200' : topup.gateway.status.type==='success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full grid place-items-center"
                                                :class="topup.gateway.status.type==='processing' ? 'bg-blue-100' : topup.gateway.status.type==='pending' ? 'bg-yellow-100' : topup.gateway.status.type==='success' ? 'bg-green-100' : 'bg-red-100'">
                                                <template x-if="topup.gateway.status.icon">
                                                    <i :data-lucide="topup.gateway.status.icon" class="w-4 h-4"
                                                        :class="topup.gateway.status.type==='processing' ? 'text-blue-600 animate-spin' : topup.gateway.status.type==='pending' ? 'text-yellow-600' : topup.gateway.status.type==='success' ? 'text-green-600' : 'text-red-600'"></i>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"
                                                    x-text="topup.gateway.status.title"></div>
                                                <div class="text-sm text-gray-600"
                                                    x-text="topup.gateway.status.message"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div class="grid grid-cols-2 gap-3 pt-2">
                                    <button @click="topupBack()"
                                        class="px-4 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Back</button>
                                    <button @click="topupConfirmPrep()" :disabled="!gatewayReady"
                                        class="px-4 py-3 rounded-xl text-white"
                                        :class="gatewayReady ? 'bg-gray-900 hover:bg-black' : 'bg-gray-400 cursor-not-allowed'">Continue</button>
                                </div>
                            </div>
                        </div>

                        <div x-show="topup.step===3" x-transition>
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-yellow-100 rounded-full grid place-items-center mx-auto mb-4">
                                    <i data-lucide="alert-circle" class="w-7 h-7 text-yellow-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Confirm Top Up</h4>
                                <p class="text-sm text-gray-500">Review the details before proceeding</p>
                            </div>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm space-y-3">
                                <div class="flex justify-between"><span class="text-gray-700">Method:</span><span
                                        class="font-medium capitalize" x-text="topup.method.replace('_',' ')"></span>
                                </div>
                                <template x-if="topup.method!=='gateway'">
                                    <div class="flex justify-between"><span class="text-gray-700">Account:</span><span
                                            class="font-medium" x-text="fmtAccount(topup.account)"></span></div>
                                </template>
                                <div class="flex justify-between"><span class="text-gray-700">Amount:</span><span
                                        class="font-semibold text-gray-900"
                                        x-text="format(topupAmount) + ' UGX'"></span></div>
                                <template x-if="topup.method==='mobile_money'">
                                    <div class="flex justify-between"><span class="text-gray-700">Phone:</span><span
                                            class="font-medium">+256<span x-text="topup.mm.phone"></span></span></div>
                                </template>
                                <template x-if="topup.method==='mobile_money' && topup.mm.txId">
                                    <div class="flex justify-between"><span class="text-gray-700">Transaction
                                            ID:</span><span class="font-mono text-xs" x-text="topup.mm.txId"></span>
                                    </div>
                                </template>
                                <template x-if="topup.method==='bank'">
                                    <div class="flex justify-between"><span class="text-gray-700">Reference:</span><span
                                            class="font-mono text-xs" x-text="topup.bank.reference||'N/A'"></span></div>
                                </template>
                                <template x-if="topup.method==='bank'">
                                    <div class="flex justify-between"><span class="text-gray-700">Depositor:</span><span
                                            class="font-medium" x-text="topup.bank.depositor||'N/A'"></span></div>
                                </template>
                                <template x-if="topup.method==='gateway'">
                                    <div class="flex justify-between"><span class="text-gray-700">Phone:</span><span
                                            class="font-medium">+256<span x-text="topup.gateway.phone"></span></span>
                                    </div>
                                </template>
                            </div>
                            <div class="bg-red-50 border border-red-200 rounded-xl p-3 mt-3">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 mt-0.5"></i>
                                    <p class="text-sm text-red-700">This action cannot be undone. Verify all details are
                                        correct.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-4">
                                <button @click="topupBack()"
                                    class="px-4 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Cancel</button>
                                <button @click="confirmTopup()" class="px-4 py-3 rounded-xl text-white"
                                    :class="topup.confirming ? 'bg-gray-400 cursor-wait' : 'bg-user-primary hover:bg-user-primary/90'">
                                    <span x-show="!topup.confirming">Confirm</span>
                                    <span x-show="topup.confirming" class="inline-flex items-center gap-2"><i
                                            data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                        Processing...</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="topup.step===4" x-transition>
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 rounded-full grid place-items-center mx-auto mb-4"
                                    :class="topup.response.success ? 'bg-green-100' : 'bg-red-100'">
                                    <i :data-lucide="topup.response.success ? 'check' : 'x'"
                                        :class="topup.response.success ? 'text-green-600' : 'text-red-600'"
                                        class="w-7 h-7"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900" x-text="topup.response.title"></h4>
                                <p class="text-sm text-gray-500" x-text="topup.response.subtitle"></p>
                            </div>
                            <div class="rounded-xl p-4"
                                :class="topup.response.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                                <template x-for="row in topup.response.details" :key="row.label">
                                    <div class="flex justify-between gap-3 text-sm py-1">
                                        <span class="text-gray-600" x-text="row.label"></span>
                                        <span class="font-medium" x-text="row.value"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-4" x-show="topup.response.success">
                                <button @click="closeTopup()"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl">Close</button>
                                <div class="text-center mt-3 text-sm text-gray-500" x-show="topup.countdown>0">This
                                    window will close automatically in <span class="font-semibold"
                                        x-text="topup.countdown"></span> seconds</div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3" x-show="!topup.response.success">
                                <button @click="resetTopupToForm()"
                                    class="px-4 py-3 border border-gray-300 hover:bg-gray-50 rounded-xl">Try
                                    Again</button>
                                <button @click="closeTopup()"
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
    function creditPage() {
        return {
            apiUrl: <?= json_encode(BASE_URL . 'vendor-store/fetch/manageZzimbaCredit.php') ?>,
            ownerName: <?= json_encode(trim(($_SESSION['user']['first_name'] ?? '') . ' ' . ($_SESSION['user']['last_name'] ?? ''))) ?>,
            accounts: <?= json_encode($cashAccounts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            loadingWallet: true, loadingTx: true,
            wallet: { name: '', number: '', created: '', balance: '', status: 'inactive' },
            dateFilter: 'all', entries: [],
            allColumns: [{ key: 'datetime', label: 'Date/Time' }, { key: 'description', label: 'Description' }, { key: 'debit', label: 'Debit' }, { key: 'credit', label: 'Credit' }, { key: 'balance', label: 'Balance' }],
            visibleColumns: JSON.parse(localStorage.getItem('zzimba_credit_table_columns') || '["datetime","description","debit","credit","balance"]'),
            topup: {
                open: false, step: 1, method: '', account: null, confirming: false,
                mm: { phone: '', amount: null, txId: '', when: '' },
                bank: { amount: null, reference: '', depositor: '', when: '' },
                gateway: { phone: '', amount: null, desc: 'Zzimba wallet top-up', validating: false, customerName: '', phoneError: '', amountError: '', status: { type: '', icon: '', title: '', message: '' }, reference: null, interval: null },
                errors: { mm_phone: '', mm_amount: '', bank_amount: '' },
                response: { success: null, title: '', subtitle: '', details: [] },
                countdown: 0, timer: null,
                accounts: []
            },
            init() { this.topup.accounts = this.accounts; this.loadWallet(); this.loadTransactions(); this.refreshIcons(); window.addEventListener('resize', () => this.fitBalance()); },
            refreshIcons() { if (window.lucide && lucide.createIcons) lucide.createIcons(); },
            ucFirst(s) { return (s || '').charAt(0).toUpperCase() + (s || '').slice(1); },
            showCol(k) { return this.visibleColumns.includes(k); },
            toggleColumn(e) {
                const col = e.target.value, checked = e.target.checked;
                if (checked) { if (!this.visibleColumns.includes(col)) this.visibleColumns.push(col); }
                else { if (this.visibleColumns.length <= 3) { e.target.checked = true; this.showAlert('error', 'At least 3 columns must be visible'); return; } this.visibleColumns = this.visibleColumns.filter(c => c !== col); }
                localStorage.setItem('zzimba_credit_table_columns', JSON.stringify(this.visibleColumns));
            },
            formatCurrency(v) { return new Intl.NumberFormat('en-UG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v || 0); },
            format(v) { return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(Number(v || 0)); },
            fitBalance() {
                const el = document.getElementById('balanceText'); if (!el) return;
                const p = el.parentElement; let fs = 40; el.style.whiteSpace = 'nowrap'; el.style.fontSize = fs + 'px';
                while (el.scrollWidth > p.clientWidth && fs > 18) { fs -= 2; el.style.fontSize = fs + 'px'; }
            },
            fmtAccount(a) { if (!a) return ''; const num = a.account_number ? (' • ' + a.account_number) : ''; return (a.name || '') + num; },
            async loadWallet() {
                this.loadingWallet = true;
                try {
                    const r = await fetch(`${this.apiUrl}?action=getWallet`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' } });
                    const j = await r.json();
                    if (j.success && j.wallet) {
                        const w = j.wallet;
                        this.wallet = { name: w.wallet_name, number: w.wallet_number, created: new Date(w.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }), balance: 'UGX ' + this.formatCurrency(parseFloat(w.current_balance)), status: w.status || 'inactive' };
                        this.$nextTick(() => { this.fitBalance(); this.refreshIcons(); });
                    } else { this.showAlert('error', 'Failed to load wallet'); }
                } catch (_) { this.showAlert('error', 'Network error loading wallet'); }
                this.loadingWallet = false;
            },
            async loadTransactions() {
                this.loadingTx = true;
                try {
                    const params = new URLSearchParams({ action: 'getWalletStatement' });
                    if (this.dateFilter !== 'all') {
                        const d = parseInt(this.dateFilter); const end = new Date(); const start = new Date(); start.setDate(start.getDate() - d);
                        params.set('filter', 'range'); params.set('start', start.toISOString().slice(0, 10)); params.set('end', end.toISOString().slice(0, 10));
                    } else { params.set('filter', 'all'); }
                    const r = await fetch(`${this.apiUrl}?action=getWalletStatement`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params });
                    const j = await r.json();
                    if (j.success && j.statement) { this.entries = this.transformStatement(j.statement); } else { this.entries = []; }
                    this.$nextTick(() => this.refreshIcons());
                } catch (_) { this.entries = []; this.showAlert('error', 'Network error loading transactions'); }
                this.loadingTx = false;
            },
            transformStatement(statement) {
                const out = [];
                statement.forEach(tx => {
                    if (tx.transaction && tx.transaction.entries?.length) {
                        const rev = [...tx.transaction.entries].reverse();
                        rev.forEach(e => {
                            const dt = new Date(tx.transaction.created_at);
                            out.push({ date: dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }), time: dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }), method: tx.transaction.payment_method ? tx.transaction.payment_method.replace(/_/g, ' ') : '', desc: tx.transaction.note || e.entry_note || '', debit: e.entry_type === 'DEBIT' ? parseFloat(e.amount) : 0, credit: e.entry_type === 'CREDIT' ? parseFloat(e.amount) : 0, balance: parseFloat(e.balance_after) || 0 });
                        });
                    } else if (tx.transaction) {
                        const dt = new Date(tx.transaction.created_at);
                        out.push({ date: dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }), time: dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }), method: tx.transaction.payment_method ? tx.transaction.payment_method.replace(/_/g, ' ') : '', desc: tx.transaction.note || '', debit: 0, credit: 0, balance: 0 });
                    }
                });
                return out.sort((a, b) => new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time));
            },
            openTopup() { this.resetTopup(); this.topup.open = true; this.$nextTick(() => this.refreshIcons()); },
            closeTopup() { this.topup.open = false; this.clearGatewayInterval(); this.clearTopupTimer(); this.resetTopup(); },
            resetTopup() {
                this.topup.step = 1; this.topup.method = ''; this.topup.account = null; this.topup.confirming = false;
                this.topup.mm = { phone: '', amount: null, txId: '', when: new Date().toISOString().slice(0, 16) };
                this.topup.bank = { amount: null, reference: '', depositor: '', when: new Date().toISOString().slice(0, 16) };
                this.topup.gateway = { phone: '', amount: null, desc: 'Zzimba wallet top-up', validating: false, customerName: '', phoneError: '', amountError: '', status: { type: '', icon: '', title: '', message: '' }, reference: null, interval: null };
                this.topup.errors = { mm_phone: '', mm_amount: '', bank_amount: '' };
                this.topup.response = { success: null, title: '', subtitle: '', details: [] };
                this.topup.countdown = 0; this.clearTopupTimer();
            },
            get topupTitle() {
                return this.topup.step === 1 ? 'Choose Payment Method'
                    : this.topup.step === 2 ? (this.topup.method === 'gateway' ? 'Gateway Payment' : this.topup.method === 'mobile_money' ? 'Mobile Money Payment' : 'Bank Transfer')
                        : this.topup.step === 3 ? 'Confirm Top Up'
                            : 'Top Up Status';
            },
            get topupSubtitle() {
                return this.topup.step === 1 ? 'Select how you want to add money'
                    : this.topup.step === 2 ? (this.topup.method === 'gateway' ? 'Enter phone and amount' : 'Fill in the details')
                        : this.topup.step === 3 ? 'Review details before sending'
                            : this.topup.response.subtitle || '';
            },
            selectTopupAccount(acc) { this.topup.account = acc; this.topup.method = acc.type; this.topup.step = 2; this.$nextTick(() => this.refreshIcons()); },
            topupBack() {
                if (this.topup.step === 2) { this.topup.step = 1; }
                else if (this.topup.step === 3) { this.topup.step = 2; }
                else if (this.topup.step === 4) { this.closeTopup(); }
                this.$nextTick(() => this.refreshIcons());
            },
            get topupAmount() {
                if (this.topup.method === 'mobile_money') return Number(this.topup.mm.amount || 0);
                if (this.topup.method === 'bank') return Number(this.topup.bank.amount || 0);
                if (this.topup.method === 'gateway') return Number(this.topup.gateway.amount || 0);
                return 0;
            },
            topupConfirmPrep() {
                if (this.topup.method === 'mobile_money') {
                    this.topup.errors.mm_phone = ''; this.topup.errors.mm_amount = '';
                    const p = (this.topup.mm.phone || '').replace(/\D/g, '');
                    if (!/^\d{9}$/.test(p)) { this.topup.errors.mm_phone = 'Enter exactly 9 digits'; return; }
                    if (!this.topup.mm.amount || Number(this.topup.mm.amount) < 500) { this.topup.errors.mm_amount = 'Minimum amount is 500 UGX'; return; }
                }
                if (this.topup.method === 'bank') {
                    this.topup.errors.bank_amount = '';
                    if (!this.topup.bank.amount || Number(this.topup.bank.amount) < 500) { this.topup.errors.bank_amount = 'Minimum amount is 500 UGX'; return; }
                }
                if (this.topup.method === 'gateway') { if (!this.gatewayReady) return; }
                this.topup.step = 3; this.$nextTick(() => this.refreshIcons());
            },
            async confirmTopup() {
                if (this.topup.confirming) return;
                this.topup.confirming = true;
                if (this.topup.method === 'mobile_money') {
                    this.showTopupResult(true, 'Submitted', 'Mobile money top up submitted', [
                        { label: 'Amount', value: `UGX ${this.format(this.topup.mm.amount)}` },
                        { label: 'Phone', value: '+256' + String(this.topup.mm.phone || '') },
                        { label: 'Transaction ID', value: this.topup.mm.txId || 'N/A' },
                        { label: 'When', value: this.topup.mm.when || 'N/A' }
                    ], true);
                    this.topup.confirming = false;
                } else if (this.topup.method === 'bank') {
                    this.showTopupResult(true, 'Submitted', 'Bank deposit submitted', [
                        { label: 'Amount', value: `UGX ${this.format(this.topup.bank.amount)}` },
                        { label: 'Reference', value: this.topup.bank.reference || 'N/A' },
                        { label: 'Depositor', value: this.topup.bank.depositor || 'N/A' },
                        { label: 'When', value: this.topup.bank.when || 'N/A' }
                    ], true);
                    this.topup.confirming = false;
                } else if (this.topup.method === 'gateway') {
                    const msisdn = '+256' + (String(this.topup.gateway.phone || '').replace(/\D/g, '')); const amt = this.topup.gateway.amount; const desc = this.topup.gateway.desc || 'Zzimba wallet top-up';
                    this.setGatewayStatus('processing', 'loader-2', 'Processing Payment', 'Initiating payment request...');
                    try {
                        const r = await fetch(`${this.apiUrl}?action=makePayment`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ msisdn, amount: amt, description: desc }) });
                        const j = await r.json();
                        if (j.success) {
                            this.topup.gateway.reference = j.internal_reference;
                            this.setGatewayStatus('pending', 'clock', 'Payment Request Sent', 'Check your phone to approve the payment.');
                            this.startGatewayPolling();
                        } else {
                            this.setGatewayStatus('error', 'x', 'Payment Failed', j.message || 'Failed to initiate payment');
                            this.showTopupResult(false, 'Payment Failed', j.message || 'The payment could not be started', [], false);
                            this.topup.confirming = false;
                        }
                    } catch (_) {
                        this.setGatewayStatus('error', 'x', 'Network Error', 'Please check your connection and try again.');
                        this.showTopupResult(false, 'Connection Error', 'Unable to process the payment', [], false);
                        this.topup.confirming = false;
                    }
                }
            },
            validateMsisdn() {
                const v = (this.topup.gateway.phone || '').replace(/\D/g, '');
                if (!/^\d{9}$/.test(v)) { this.topup.gateway.phoneError = 'Please enter exactly 9 digits'; this.topup.gateway.customerName = ''; return; }
                const msisdn = '+256' + v; this.topup.gateway.validating = true; this.topup.gateway.phoneError = ''; this.topup.gateway.customerName = '';
                fetch(`${this.apiUrl}?action=validateMsisdn`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ msisdn }) })
                    .then(r => r.json()).then(j => { if (j.success) { this.topup.gateway.customerName = j.customer_name; } else { this.topup.gateway.phoneError = j.message || 'Validation failed'; } })
                    .catch(_ => { this.topup.gateway.phoneError = 'Network error. Try again.'; })
                    .finally(() => { this.topup.gateway.validating = false; this.$nextTick(() => this.refreshIcons()); });
            },
            resetGatewayPhone() { this.topup.gateway.customerName = ''; this.topup.gateway.phoneError = ''; },
            validateGatewayAmount() { const a = Number(this.topup.gateway.amount || 0); this.topup.gateway.amountError = (!a || a < 500) ? 'Minimum amount is 500 UGX' : ''; },
            get gatewayReady() { return this.topup.gateway.customerName && this.topup.gateway.amount && Number(this.topup.gateway.amount) >= 500; },
            setGatewayStatus(type, icon, title, message) { this.topup.gateway.status = { type, icon, title, message }; this.$nextTick(() => this.refreshIcons()); },
            startGatewayPolling() {
                this.clearGatewayInterval();
                if (!this.topup.gateway.reference) return;
                this.topup.gateway.interval = setInterval(async () => {
                    try {
                        const r = await fetch(`${this.apiUrl}?action=checkStatus`, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ internal_reference: this.topup.gateway.reference }) });
                        const j = await r.json();
                        if (j.success) {
                            if (j.status === 'success') {
                                this.clearGatewayInterval();
                                this.setGatewayStatus('success', 'check', 'Payment Successful', 'Your payment has been completed.');
                                this.showTopupResult(true, 'Payment Successful!', 'Your payment has been completed successfully.', [
                                    { label: 'Amount', value: `${j.currency || 'UGX'} ${this.format(j.amount)}` },
                                    ...(j.charge ? [{ label: 'Fee', value: `${j.currency || 'UGX'} ${this.format(j.charge)}` }] : []),
                                    { label: 'Provider', value: (j.provider || '').replace('_', ' ') || 'N/A' },
                                    { label: 'Transaction ID', value: j.provider_transaction_id || 'N/A' },
                                    { label: 'Reference', value: j.customer_reference || 'N/A' },
                                    ...(j.completed_at && j.completed_at !== 'N/A' ? [{ label: 'Completed', value: new Date(j.completed_at).toLocaleString() }] : [])
                                ], true);
                                setTimeout(() => { this.loadWallet(); this.loadTransactions(); }, 600);
                                this.topup.confirming = false;
                            } else if (j.status === 'failed') {
                                this.clearGatewayInterval();
                                this.setGatewayStatus('error', 'x', 'Payment Failed', j.message || 'Payment failed.');
                                this.showTopupResult(false, 'Payment Failed', j.message || 'Payment could not be completed.', [
                                    { label: 'Amount', value: `${j.currency || 'UGX'} ${this.format(j.amount)}` },
                                    { label: 'Provider', value: (j.provider || '').replace('_', ' ') || 'N/A' },
                                    { label: 'Reference', value: j.customer_reference || 'N/A' },
                                    ...(j.reason ? [{ label: 'Reason', value: j.reason }] : [])
                                ], false);
                                this.topup.confirming = false;
                            }
                        }
                    } catch (_) { }
                }, 3000);
            },
            clearGatewayInterval() { if (this.topup.gateway.interval) { clearInterval(this.topup.gateway.interval); this.topup.gateway.interval = null; } },
            showTopupResult(ok, title, subtitle, details, autoClose) {
                this.topup.response = { success: ok, title, subtitle, details };
                this.topup.step = 4;
                if (autoClose) { this.startTopupCountdown(30); }
                this.$nextTick(() => this.refreshIcons());
            },
            startTopupCountdown(sec) {
                this.clearTopupTimer(); this.topup.countdown = sec;
                this.topup.timer = setInterval(() => { this.topup.countdown--; if (this.topup.countdown <= 0) { this.clearTopupTimer(); this.closeTopup(); } }, 1000);
            },
            clearTopupTimer() { if (this.topup.timer) { clearInterval(this.topup.timer); this.topup.timer = null; } },
            resetTopupToForm() { this.topup.response = { success: null, title: '', subtitle: '', details: [] }; this.topup.step = 2; this.$nextTick(() => this.refreshIcons()); },
            showAlert(type, message) {
                const c = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
                const icon = type === 'success' ? 'check-circle' : 'alert-triangle';
                const el = document.getElementById('alertContainer');
                el.innerHTML = `<div class="${c} pointer-events-auto border px-4 py-3 rounded-lg shadow flex items-center gap-2"><i data-lucide="${icon}" class="w-4 h-4"></i><span>${message}</span></div>`;
                this.refreshIcons(); setTimeout(() => { el.innerHTML = ''; }, 4000);
            }
        }
    }
</script>

<?php
include __DIR__ . '/credit/send-credit.php';

$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>