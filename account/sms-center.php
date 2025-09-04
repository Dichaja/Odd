<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'SMS Center';
$activeNav = 'sms-center';
ob_start();
?>
<div x-data="smsCenter()" x-init="init()" x-cloak class="min-h-screen">
    <style>
        [x-cloak] {
            display: none
        }
    </style>

    <div class="border-b bg-white dark:bg-secondary/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 grid gap-4 md:grid-cols-3">
            <div
                class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-4">
                <div>
                    <div class="text-xs font-bold uppercase text-gray-500 dark:text-white/70">SMS Credit</div>
                    <div class="text-xl font-extrabold text-gray-900 dark:text-white"
                        x-text="stats.currentCredits.toLocaleString()"></div>
                    <div class="text-sm text-gray-500 dark:text-white/70">Messages Available</div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center bg-blue-50 text-blue-600">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                </div>
            </div>
            <div
                class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-4">
                <div>
                    <div class="text-xs font-bold uppercase text-gray-500 dark:text-white/70">Sent Today</div>
                    <div class="text-xl font-extrabold text-gray-900 dark:text-white"
                        x-text="stats.sentToday.toLocaleString()"></div>
                    <div class="text-sm text-gray-500 dark:text-white/70"
                        x-text="`Sh. ${formatMoney(stats.sentTodayCost)}`"></div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center bg-green-50 text-green-600">
                    <i data-lucide="send" class="w-5 h-5"></i>
                </div>
            </div>
            <div
                class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-4">
                <div>
                    <div class="text-xs font-bold uppercase text-gray-500 dark:text-white/70">Scheduled</div>
                    <div class="text-xl font-extrabold text-gray-900 dark:text-white"
                        x-text="stats.scheduledCount.toLocaleString()"></div>
                    <div class="text-sm text-gray-500 dark:text-white/70">Pending Messages</div>
                </div>
                <div class="w-10 h-10 rounded-lg grid place-items-center bg-purple-50 text-purple-600">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary mb-6">
            <div class="flex gap-4 overflow-auto p-2">
                <button @click="switchTab('send')"
                    :class="tab==='send'?'text-primary border-b-2 border-primary':'text-gray-500 dark:text-white/70 border-b-2 border-transparent'"
                    class="px-2 py-2 font-semibold flex items-center gap-2"><i data-lucide="send"
                        class="w-4 h-4"></i>Send</button>
                <button @click="switchTab('history')"
                    :class="tab==='history'?'text-primary border-b-2 border-primary':'text-gray-500 dark:text-white/70 border-b-2 border-transparent'"
                    class="px-2 py-2 font-semibold flex items-center gap-2"><i data-lucide="history"
                        class="w-4 h-4"></i>History</button>
                <button @click="switchTab('templates')"
                    :class="tab==='templates'?'text-primary border-b-2 border-primary':'text-gray-500 dark:text-white/70 border-b-2 border-transparent'"
                    class="px-2 py-2 font-semibold flex items-center gap-2"><i data-lucide="file-text"
                        class="w-4 h-4"></i>Templates</button>
                <button @click="switchTab('topup')"
                    :class="tab==='topup'?'text-primary border-b-2 border-primary':'text-gray-500 dark:text-white/70 border-b-2 border-transparent'"
                    class="px-2 py-2 font-semibold flex items-center gap-2"><i data-lucide="credit-card"
                        class="w-4 h-4"></i>Top Up</button>
            </div>
        </div>

        <div x-show="tab==='send'" x-transition
            class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-6">
            <form @submit.prevent="beginConfirm" class="grid gap-6">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <div class="text-sm font-semibold text-gray-800 dark:text-white">Send Type</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="sendType='single';bulkRecipients=[]"
                                :class="sendType==='single'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border">Single</button>
                            <button type="button" @click="sendType='bulk'"
                                :class="sendType==='bulk'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border">Bulk</button>
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4">
                        <div class="flex items-center justify-between text-sm"><span
                                class="text-gray-500 dark:text-white/70">Recipients</span><span class="font-semibold"
                                x-text="recipientCount"></span></div>
                        <div class="flex items-center justify-between text-sm"><span
                                class="text-gray-500 dark:text-white/70">Credits Needed</span><span
                                class="font-semibold" x-text="creditsNeeded"></span></div>
                        <div class="flex items-center justify-between text-sm"><span
                                class="text-gray-500 dark:text-white/70">Estimated Cost</span><span
                                class="font-semibold" x-text="`Sh. ${formatMoney(estimatedCost)}`"></span></div>
                    </div>
                </div>

                <div x-show="sendType==='single'" class="grid gap-2">
                    <div class="text-sm font-semibold text-gray-800 dark:text-white">Recipient</div>
                    <input type="tel" x-model.trim="recipient"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm"
                        placeholder="0700123456" autocomplete="off">
                </div>

                <div x-show="sendType==='bulk'" class="grid gap-3">
                    <div class="text-sm font-semibold text-gray-800 dark:text-white">Recipients</div>
                    <div class="grid gap-2 sm:grid-cols-[1fr_auto]">
                        <input type="tel" x-model.trim="bulkInput" @keyup.enter.prevent="addBulk()"
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm"
                            placeholder="700123456 or 0700123456">
                        <button type="button" @click="addBulk()"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary text-white px-4 py-2.5"><i
                                data-lucide="plus" class="w-4 h-4"></i>Add</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="n in bulkRecipients" :key="n">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-white/10 px-3 py-1.5 text-sm text-gray-800 dark:text-white">
                                <span x-text="n"></span>
                                <button type="button" @click="removeRecipient(n)"
                                    class="text-gray-500 hover:text-red-600"><i data-lucide="x"
                                        class="w-4 h-4"></i></button>
                            </span>
                        </template>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <button type="button" @click="pasteCsv()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-800 dark:text-white px-4 py-2.5"><i
                                data-lucide="clipboard-paste" class="w-4 h-4"></i>Paste CSV</button>
                        <div>
                            <input type="file" x-ref="csv" accept=".csv" class="hidden" @change="handleCsvUpload">
                            <button type="button" @click="$refs.csv.click()"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-800 dark:text-white px-4 py-2.5"><i
                                    data-lucide="upload" class="w-4 h-4"></i>Upload CSV</button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-800 dark:text-white">Message</div>
                        <div class="text-xs text-gray-500 dark:text-white/70"
                            x-text="`${message.length}/160 • ${parts} part(s)`"></div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tok in tokens" :key="tok">
                            <button type="button" @click="insertToken(tok)"
                                class="rounded-full bg-gray-100 dark:bg-white/10 px-3 py-1 text-xs text-gray-700 dark:text-white/80"
                                x-text="tok"></button>
                        </template>
                    </div>
                    <textarea x-model="message"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm min-h-[130px]"
                        placeholder="Type your message..."></textarea>
                    <div class="text-xs text-gray-500 dark:text-white/60">Long messages split automatically</div>
                    <div>
                        <button type="button" @click="openTemplateSelector()"
                            class="inline-flex items-center gap-2 text-primary font-medium"><i data-lucide="magnet"
                                class="w-4 h-4"></i>Use Template</button>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <div class="text-sm font-semibold text-gray-800 dark:text-white">Send Options</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="sendOption='now'"
                                :class="sendOption==='now'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border">Send Now</button>
                            <button type="button" @click="sendOption='schedule'"
                                :class="sendOption==='schedule'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                                class="w-full px-4 py-2.5 text-sm font-medium rounded-lg border">Schedule</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2" x-show="sendOption==='schedule'">
                        <input type="date" x-model="scheduleDate"
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm">
                        <input type="time" x-model="scheduleTime"
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-4 py-3 font-semibold">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span x-text="sendOption==='schedule'?'Schedule SMS':'Send SMS'"></span>
                    </button>
                </div>
            </form>
        </div>

        <div x-show="tab==='history'" x-transition class="grid gap-4">
            <div
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-4 lg:grid-cols-[1fr_auto_auto_auto]">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" x-model.debounce.300ms="filters.search" @input="loadHistory()"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white pl-9 pr-3 py-3 text-sm"
                        placeholder="Search SMS history...">
                </div>
                <select x-model="filters.status" @change="loadHistory()"
                    class="rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-3 text-sm">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="failed">Failed</option>
                </select>
                <input type="date" x-model="filters.dateFrom" @change="loadHistory()"
                    class="rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-3 text-sm">
                <input type="date" x-model="filters.dateTo" @change="loadHistory()"
                    class="rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-3 text-sm">
            </div>

            <div
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary overflow-hidden">
                <div class="hidden lg:block max-h-[70vh] overflow-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-white/5 sticky top-0">
                            <tr class="text-xs uppercase text-gray-500 dark:text-white/70">
                                <th class="text-left p-3">Message</th>
                                <th class="text-left p-3">Recipients</th>
                                <th class="text-left p-3">Status</th>
                                <th class="text-left p-3">Cost</th>
                                <th class="text-left p-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="!history.length">
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-500 dark:text-white/70">No SMS
                                        history</td>
                                </tr>
                            </template>
                            <template x-for="h in history" :key="h.id">
                                <tr class="border-t border-gray-200 dark:border-white/10 hover:bg-primary/5 cursor-pointer"
                                    @click="viewSms(h)">
                                    <td class="p-3">
                                        <div class="truncate" x-text="h.message"></div>
                                    </td>
                                    <td class="p-3">
                                        <div
                                            x-text="h.recipient_count + (h.recipient_count>1?' recipients':' recipient')">
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-white/70" x-text="h.type"></div>
                                    </td>
                                    <td class="p-3">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs"
                                            :class="badge(h.status).bg">
                                            <span x-show="badge(h.status).icon">
                                                <i :data-lucide="badge(h.status).icon" class="w-3.5 h-3.5"></i>
                                            </span>
                                            <span x-text="h.status"></span>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div x-text="`Sh. ${formatMoney(h.total_cost)}`"></div>
                                        <div class="text-xs text-gray-500 dark:text-white/70"
                                            x-text="`${h.credits_used} credits`"></div>
                                    </td>
                                    <td class="p-3"
                                        x-text="formatDateTime(h.sent_at || h.scheduled_at || h.created_at)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="lg:hidden p-4 space-y-3 max-h-[70vh] overflow-auto">
                    <template x-if="!history.length">
                        <div class="py-16 text-center text-gray-500 dark:text-white/70">
                            <div
                                class="w-20 h-20 rounded-full grid place-items-center mx-auto mb-3 bg-gray-50 dark:bg-white/5">
                                <i data-lucide="history" class="w-8 h-8"></i>
                            </div>
                            No SMS history
                            <div class="mt-4">
                                <button @click="switchTab('send')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-primary text-white px-4 py-2.5"><i
                                        data-lucide="send" class="w-4 h-4"></i>Send SMS</button>
                            </div>
                        </div>
                    </template>
                    <template x-for="h in history" :key="h.id">
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-4"
                            @click="viewSms(h)">
                            <div class="flex items-start justify-between">
                                <div class="line-clamp-2 text-gray-900 dark:text-white" x-text="h.message"></div>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs"
                                    :class="badge(h.status).bg">
                                    <span x-show="badge(h.status).icon"><i :data-lucide="badge(h.status).icon"
                                            class="w-3.5 h-3.5"></i></span>
                                    <span x-text="h.status"></span>
                                </span>
                            </div>
                            <div
                                class="flex items-center justify-between mt-2 text-xs text-gray-500 dark:text-white/70">
                                <span
                                    x-text="`${h.recipient_count} ${h.recipient_count>1?'recipients':'recipient'} • ${h.type}`"></span>
                                <span x-text="formatDateTime(h.sent_at || h.scheduled_at || h.created_at)"></span>
                            </div>
                            <div class="mt-1 text-sm text-gray-900 dark:text-white"
                                x-text="`Sh. ${formatMoney(h.total_cost)} • ${h.credits_used} credits`"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="tab==='templates'" x-transition class="grid gap-4">
            <div
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-4 lg:grid-cols-[1fr_auto]">
                <input type="text" x-model.debounce.300ms="templateSearch" @input="renderTemplates()"
                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm"
                    placeholder="Search templates...">
                <button @click="openTemplateForm()"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary text-white px-4 py-2.5"><i
                        data-lucide="plus" class="w-4 h-4"></i>Create Template</button>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <template x-if="!filteredTemplates.length">
                    <div class="col-span-full py-16 text-center text-gray-500 dark:text-white/70">
                        <div
                            class="w-20 h-20 rounded-full grid place-items-center mx-auto mb-3 bg-gray-50 dark:bg-white/5">
                            <i data-lucide="file-text" class="w-8 h-8"></i>
                        </div>
                        No templates
                        <div class="mt-4">
                            <button @click="openTemplateForm()"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary text-white px-4 py-2.5"><i
                                    data-lucide="plus" class="w-4 h-4"></i>Create Template</button>
                        </div>
                    </div>
                </template>
                <template x-for="t in filteredTemplates" :key="t.id">
                    <div
                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-3">
                        <div class="flex items-start justify-between">
                            <div class="font-semibold truncate text-gray-900 dark:text-white" x-text="t.name"></div>
                            <div class="flex gap-2">
                                <button @click="editTemplate(t)"
                                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"><i
                                        data-lucide="pencil" class="w-4 h-4"></i></button>
                                <button @click="deleteTemplate(t)" class="rounded-lg bg-red-600 text-white px-3 py-2"><i
                                        data-lucide="trash" class="w-4 h-4"></i></button>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-white/70 line-clamp-3" x-text="t.message"></div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-white/70"
                                x-text="`${t.message.length} chars`"></span>
                            <button @click="applyTemplate(t)" class="text-primary font-medium">Use</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="tab==='topup'" x-transition class="max-w-2xl mx-auto grid gap-6">
            <div
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid place-items-center">
                <div class="text-sm text-gray-500 dark:text-white/70">Current SMS Rate</div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white" x-text="`Sh. ${formatMoney(rate)}`">
                </div>
                <div class="text-xs text-gray-500 dark:text-white/70">per SMS</div>
            </div>
            <div
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-3">
                <div class="font-semibold text-gray-900 dark:text-white">Wallet</div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-white/70">Balance</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white"
                            x-text="`Sh. ${formatMoney(wallet.balance)}`"></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-white/70">Equivalent Credits</div>
                        <div class="text-2xl font-bold text-blue-600" x-text="wallet.credits"></div>
                    </div>
                </div>
            </div>
            <form @submit.prevent="purchaseCredits"
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-5 grid gap-5">
                <div class="grid gap-2">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Select Package</div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <button type="button" @click="selectPackage('100')"
                            :class="pkg==='100'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                            class="w-full flex items-center justify-between rounded-xl border px-4 py-3">
                            <div>
                                <div class="font-semibold">100 SMS</div>
                                <div class="text-sm text-gray-500 dark:text-white/70"
                                    x-text="`Sh. ${formatMoney(100*rate)}`"></div>
                            </div>
                            <span x-show="pkg==='100'"><i data-lucide="check" class="w-5 h-5"></i></span>
                        </button>

                        <button type="button" @click="selectPackage('500')"
                            :class="pkg==='500'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                            class="w-full flex items-center justify-between rounded-xl border px-4 py-3">
                            <div>
                                <div class="font-semibold">500 SMS</div>
                                <div class="text-sm text-gray-500 dark:text-white/70"
                                    x-text="`Sh. ${formatMoney(500*rate)}`"></div>
                            </div>
                            <span x-show="pkg==='500'"><i data-lucide="check" class="w-5 h-5"></i></span>
                        </button>

                        <button type="button" @click="selectPackage('1000')"
                            :class="pkg==='1000'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                            class="w-full flex items-center justify-between rounded-xl border px-4 py-3">
                            <div>
                                <div class="font-semibold">1,000 SMS</div>
                                <div class="text-sm text-gray-500 dark:text-white/70"
                                    x-text="`Sh. ${formatMoney(1000*rate)}`"></div>
                            </div>
                            <span x-show="pkg==='1000'"><i data-lucide="check" class="w-5 h-5"></i></span>
                        </button>

                        <button type="button" @click="selectPackage('custom')"
                            :class="pkg==='custom'?'border-primary bg-primary/5 text-primary':'border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70'"
                            class="w-full flex items-center justify-between rounded-xl border px-4 py-3">
                            <div>
                                <div class="font-semibold">Custom</div>
                                <div class="text-sm text-gray-500 dark:text-white/70">Enter amount</div>
                            </div>
                            <span x-show="pkg==='custom'"><i data-lucide="check" class="w-5 h-5"></i></span>
                        </button>
                    </div>
                </div>

                <div class="grid gap-2" x-show="pkg==='custom'">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Custom Amount (Sh.)</div>
                    <input type="number" min="1000" step="100" x-model.number="customAmount" @input="updateTopup()"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm"
                        placeholder="Enter amount">
                </div>

                <div
                    class="rounded-xl border border-gray-200 dark:border-white/10 p-4 grid gap-2 bg-white dark:bg-transparent">
                    <div class="flex items-center justify-between"><span class="text-gray-500 dark:text-white/70">SMS
                            Credits</span><span class="font-semibold" x-text="topup.credits"></span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500 dark:text-white/70">Total
                            Cost</span><span class="font-semibold" x-text="`Sh. ${formatMoney(topup.cost)}`"></span>
                    </div>
                    <div class="flex items-center justify-between"><span class="text-gray-500 dark:text-white/70">Wallet
                            Balance</span><span class="font-semibold"
                            :class="topup.cost>wallet.balance?'text-red-600':'text-green-600'"
                            x-text="`Sh. ${formatMoney(wallet.balance)} ${topup.cost>wallet.balance?'(Insufficient)':'(Sufficient)'}`"></span>
                    </div>
                </div>

                <div x-show="topup.cost>wallet.balance"
                    class="rounded-xl border border-red-200 bg-red-50 text-red-700 p-4">Insufficient Wallet Balance. Top
                    up your wallet before purchasing credits.</div>

                <button type="submit" :disabled="topup.cost>wallet.balance || topup.cost<=0"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-4 py-3 font-semibold disabled:opacity-60">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>Purchase Credits
                </button>
            </form>
        </div>
    </div>

    <div x-show="modal.bulk" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50" @click="modal.bulk=false"></div>
        <div
            class="relative z-10 max-w-2xl mx-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary shadow-2xl max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="font-semibold text-gray-900 dark:text-white">Bulk Upload Results</div>
                <button
                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"
                    @click="modal.bulk=false"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[60vh]">
                <div class="grid gap-3">
                    <div class="rounded-xl border border-green-200 bg-green-50 p-3">
                        <div class="font-semibold text-green-700" x-text="`Added: ${bulkReport.valid.length}`"></div>
                    </div>
                    <template x-if="bulkReport.invalid.length">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                            <div class="font-semibold text-red-700" x-text="`Invalid: ${bulkReport.invalid.length}`">
                            </div>
                            <div class="text-sm text-red-700 max-h-32 overflow-auto"
                                x-html="bulkReport.invalid.slice(0,10).map(x=>`<div>${x}</div>`).join('') + (bulkReport.invalid.length>10?`<div>...and ${bulkReport.invalid.length-10} more</div>`:'')">
                            </div>
                        </div>
                    </template>
                    <template x-if="bulkReport.duplicates.length">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                            <div class="font-semibold text-amber-700"
                                x-text="`Duplicates: ${bulkReport.duplicates.length}`"></div>
                        </div>
                    </template>
                    <div class="rounded-xl border border-gray-200 dark:border-white/10 p-3">
                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-800 dark:text-white">
                            <div x-text="`Total: ${bulkReport.total}`"></div>
                            <div x-text="`Added: ${bulkReport.valid.length}`"></div>
                            <div x-text="`Invalid: ${bulkReport.invalid.length}`"></div>
                            <div x-text="`Duplicates: ${bulkReport.duplicates.length}`"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <button
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-4 py-3 font-semibold"
                    @click="modal.bulk=false">Continue</button>
            </div>
        </div>
    </div>

    <div x-show="modal.templateSelector" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50" @click="modal.templateSelector=false"></div>
        <div
            class="relative z-10 max-w-2xl mx-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary shadow-2xl max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="font-semibold text-gray-900 dark:text-white">Select Template</div>
                <button
                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"
                    @click="modal.templateSelector=false"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[60vh] grid gap-2">
                <template x-if="!templates.length">
                    <div class="py-8 text-center text-gray-500 dark:text-white/70">No templates</div>
                </template>
                <template x-for="t in templates" :key="t.id">
                    <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary p-3 hover:bg-primary/5 cursor-pointer"
                        @click="applyTemplate(t); modal.templateSelector=false">
                        <div class="font-medium truncate text-gray-900 dark:text-white" x-text="t.name"></div>
                        <div class="text-sm text-gray-600 dark:text-white/70 line-clamp-2" x-text="t.message"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="modal.template" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50" @click="modal.template=false"></div>
        <div
            class="relative z-10 max-w-lg mx-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="font-semibold text-gray-900 dark:text-white"
                    x-text="templateForm.id?'Edit Template':'Create Template'"></div>
                <button
                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"
                    @click="modal.template=false"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form @submit.prevent="saveTemplate" class="p-5 grid gap-4">
                <div class="grid gap-1">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Template Name</div>
                    <input type="text" x-model.trim="templateForm.name"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm"
                        required>
                </div>
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white">Message</div>
                        <div class="text-xs text-gray-500 dark:text-white/70"
                            x-text="`${templateForm.message.length}/160`"></div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tok in tokens" :key="tok">
                            <button type="button" @click="templateForm.message += tok"
                                class="rounded-full bg-gray-100 dark:bg-white/10 px-3 py-1 text-xs text-gray-700 dark:text-white/80"
                                x-text="tok"></button>
                        </template>
                    </div>
                    <textarea x-model="templateForm.message"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm min-h-[130px]"
                        required placeholder="Enter template message"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="modal.template=false"
                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3">Cancel</button>
                    <button type="submit" class="rounded-xl bg-primary text-white px-4 py-3 font-semibold">Save
                        Template</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modal.message" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50" @click="modal.message=false"></div>
        <div
            class="relative z-10 max-w-md mx-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="font-semibold text-gray-900 dark:text-white" x-text="messageModal.title"></div>
                <button
                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"
                    @click="modal.message=false"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5 grid gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg grid place-items-center" :class="messageModal.bg">
                        <i :data-lucide="messageModal.icon" class="w-5 h-5" :class="messageModal.fg"></i>
                    </div>
                    <div class="text-sm text-gray-800 dark:text-white" x-html="messageModal.html"></div>
                </div>
                <button class="rounded-xl bg-primary text-white px-4 py-3 font-semibold w-full"
                    @click="modal.message=false">OK</button>
            </div>
        </div>
    </div>

    <div x-show="modal.confirm" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50" @click="modal.confirm=false"></div>
        <div
            class="relative z-10 max-w-lg mx-auto rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="font-semibold text-gray-900 dark:text-white">Confirm SMS Details</div>
                <button
                    class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent px-3 py-2"
                    @click="modal.confirm=false"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5 grid gap-2 text-sm text-gray-800 dark:text-white">
                <div><b>Type:</b> <span x-text="confirmData.type==='single'?'Single':'Bulk'"></span></div>
                <div><b>Recipients:</b> <span x-text="confirmData.recipients.length"></span>
                    <span
                        x-text="`(${confirmData.recipients.slice(0,3).join(', ')}${confirmData.recipients.length>3?'...':''})`"></span>
                </div>
                <div class="truncate"><b>Message:</b> <span x-text="confirmData.message"></span></div>
                <div><b>SMS Parts:</b> <span x-text="confirmData.parts"></span></div>
                <div><b>Credits Needed:</b> <span x-text="confirmData.credits"></span></div>
                <div><b>Total Cost:</b> <span x-text="`Sh. ${formatMoney(confirmData.total)}`"></span></div>
                <div x-show="confirmData.schedule"><b>Scheduled:</b> <span
                        x-text="formatDateTime(confirmData.schedule)"></span></div>
                <div x-show="!confirmData.schedule"><b>Send:</b> Immediately</div>
            </div>
            <div class="p-5 grid grid-cols-2 gap-3">
                <button
                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-4 py-3"
                    @click="modal.confirm=false">Cancel</button>
                <button
                    class="rounded-xl bg-primary text-white px-4 py-3 font-semibold inline-flex items-center justify-center gap-2"
                    @click="confirmSend"><i data-lucide="send" class="w-4 h-4"></i>Send SMS</button>
            </div>
        </div>
    </div>
</div>

<script>
    function smsCenter() {
        return {
            tab: 'send',
            stats: { currentCredits: 0, sentToday: 0, sentTodayCost: 0, scheduledCount: 0 },
            rate: 0,
            wallet: { balance: 0, credits: 0 },
            sendType: 'single',
            sendOption: 'now',
            recipient: '',
            bulkInput: '',
            bulkRecipients: [],
            message: '',
            tokens: ['{name}', '{order}', '{amount}', '{date}', '{store}', '{otp}'],
            scheduleDate: '',
            scheduleTime: '',
            modal: { bulk: false, templateSelector: false, template: false, message: false, confirm: false },
            bulkReport: { valid: [], invalid: [], duplicates: [], total: 0 },
            templates: [],
            templateSearch: '',
            get filteredTemplates() {
                const q = this.templateSearch.toLowerCase();
                return this.templates.filter(t => !q || t.name.toLowerCase().includes(q) || t.message.toLowerCase().includes(q));
            },
            templateForm: { id: '', name: '', message: '' },
            history: [],
            filters: { search: '', status: '', dateFrom: '', dateTo: '' },
            confirmData: { type: 'single', message: '', recipients: [], parts: 1, credits: 0, total: 0, schedule: null },
            messageModal: { title: '', html: '', bg: 'bg-blue-100', fg: 'text-blue-600', icon: 'info' },
            pkg: '100',
            customAmount: null,
            topup: { credits: 0, cost: 0 },

            init() {
                const now = new Date(), first = new Date(now.getFullYear(), now.getMonth(), 1);
                this.filters.dateFrom = this.isoDate(first);
                this.filters.dateTo = this.isoDate(now);
                this.loadAll();
                this.switchTab('send');
                this.$nextTick(() => this.renderIcons());
            },
            renderIcons() { if (window.lucide) { window.lucide.createIcons(); } },
            switchTab(t) { this.tab = t; if (t === 'history') this.loadHistory(); if (t === 'templates') this.loadTemplates(); this.$nextTick(() => this.renderIcons()); },

            get parts() { return Math.max(1, Math.ceil(this.message.length / 160)); },
            get recipientCount() { return this.sendType === 'single' ? (this.recipient.trim() ? 1 : 0) : this.bulkRecipients.length; },
            get creditsNeeded() { return this.recipientCount * this.parts; },
            get estimatedCost() { return this.creditsNeeded * this.rate; },

            insertToken(tok) {
                const el = this.getTextarea();
                if (!el) { this.message += tok; return; }
                const s = el.selectionStart || 0, e = el.selectionEnd || 0, v = this.message;
                this.message = v.substring(0, s) + tok + v.substring(e);
                this.$nextTick(() => { el.focus(); const pos = s + tok.length; el.setSelectionRange(pos, pos); });
            },
            getTextarea() { return document.querySelector('textarea[x-model="message"]'); },

            validatePhone(n) { const x = (n || '').replace(/\s+/g, ''); return /^0[7]\d{8}$/.test(x) || /^[7]\d{8}$/.test(x); },
            normalizePhone(n) { const x = (n || '').replace(/\s+/g, ''); return /^[7]\d{8}$/.test(x) ? ('0' + x) : x; },
            addBulk() {
                const num = this.bulkInput.trim();
                if (!num) return;
                if (!this.validatePhone(num)) return this.toast('Invalid Number', 'Enter a valid 10-digit phone number', 'error');
                const norm = this.normalizePhone(num);
                if (this.bulkRecipients.includes(norm)) return this.toast('Duplicate', 'This number is already added', 'warning');
                this.bulkRecipients.push(norm);
                this.bulkInput = '';
                this.$nextTick(() => this.renderIcons());
            },
            removeRecipient(n) { this.bulkRecipients = this.bulkRecipients.filter(x => x !== n); },

            pasteCsv() {
                const s = prompt('Paste phone numbers separated by commas or new lines:', '700123456, 701234567');
                if (!s) return;
                const arr = s.split(/[\n,]+/).map(x => x.trim()).filter(Boolean);
                this.processNumbers(arr);
            },
            handleCsvUpload(e) {
                const f = e.target.files[0]; if (!f) return;
                if ((f.name.split('.').pop() || '').toLowerCase() !== 'csv') return this.toast('Invalid File', 'Upload a CSV file', 'error');
                const reader = new FileReader();
                reader.onload = ev => {
                    const lines = (ev.target.result || '').split('\n');
                    const nums = [];
                    for (let i = 1; i < lines.length; i++) { const line = lines[i].trim(); if (!line) continue; const num = line.split(',')[0].trim().replace(/['"]/g, ''); if (num) nums.push(num); }
                    this.processNumbers(nums);
                };
                reader.readAsText(f);
                e.target.value = '';
            },
            processNumbers(arr) {
                const r = { valid: [], invalid: [], duplicates: [], total: arr.length };
                arr.forEach(n => {
                    const clean = n.replace(/\s+/g, ''); const norm = this.normalizePhone(clean);
                    if (this.bulkRecipients.includes(norm) || r.valid.includes(norm)) r.duplicates.push(norm);
                    else if (this.validatePhone(clean)) r.valid.push(norm);
                    else r.invalid.push(clean);
                });
                this.bulkRecipients = [...this.bulkRecipients, ...r.valid];
                this.bulkReport = r;
                this.modal.bulk = true;
                this.$nextTick(() => this.renderIcons());
            },

            beginConfirm() {
                if (!this.message.trim()) return this.toast('Missing Message', 'Please enter a message', 'error');
                let recipients = [];
                if (this.sendType === 'single') {
                    if (!this.recipient.trim()) return this.toast('Missing Recipient', 'Please enter a recipient phone number', 'error');
                    if (!this.validatePhone(this.recipient)) return this.toast('Invalid Number', 'Please enter a valid 10-digit phone number', 'error');
                    recipients = [this.normalizePhone(this.recipient)];
                } else {
                    if (!this.bulkRecipients.length) return this.toast('No Recipients', 'Add at least one recipient', 'error');
                    recipients = [...this.bulkRecipients];
                }
                let schedule = null;
                if (this.sendOption === 'schedule') {
                    if (!this.scheduleDate || !this.scheduleTime) return this.toast('Missing Schedule', 'Select both date and time', 'error');
                    schedule = `${this.scheduleDate} ${this.scheduleTime}:00`;
                }
                const parts = Math.max(1, Math.ceil(this.message.length / 160));
                const credits = recipients.length * parts;
                const total = credits * this.rate;
                this.confirmData = { type: this.sendType, message: this.message, recipients, parts, credits, total, schedule };
                this.modal.confirm = true;
                this.$nextTick(() => this.renderIcons());
            },

            async confirmSend() {
                this.modal.confirm = false;
                const payload = {
                    message: this.confirmData.message,
                    recipients: JSON.stringify(this.confirmData.recipients),
                    send_type: this.confirmData.type,
                    send_option: this.confirmData.schedule ? 'schedule' : 'now',
                    scheduled_at: this.confirmData.schedule
                };
                this.toast('Processing', 'Queuing SMS...', 'info');
                const resp = await this.api('sendSms', payload, 'POST');
                if (resp?.success) {
                    this.toast('Success', resp.message || 'SMS queued', 'success');
                    this.resetSendForm();
                    this.loadAll();
                } else {
                    this.toast('Error', resp?.message || 'Failed to send SMS', 'error');
                }
            },
            resetSendForm() {
                this.sendType = 'single'; this.sendOption = 'now'; this.recipient = ''; this.bulkInput = ''; this.bulkRecipients = [];
                this.message = ''; this.scheduleDate = ''; this.scheduleTime = '';
            },

            async loadAll() {
                const [s, w] = await Promise.all([this.api('getSmsStats', {}, 'POST'), this.api('getWalletBalance', {}, 'POST')]);
                if (s?.success) {
                    this.stats.currentCredits = s.data?.current_credits || 0;
                    this.stats.sentToday = s.data?.sent_today || 0;
                    this.stats.sentTodayCost = s.data?.sent_today_cost || 0;
                    this.stats.scheduledCount = s.data?.scheduled_count || 0;
                }
                if (w?.success) {
                    this.wallet.balance = w.data?.balance || 0;
                    this.rate = w.data?.sms_rate || 0;
                    this.wallet.credits = w.data?.equivalent_credits || 0;
                }
                this.updateTopup();
                this.loadHistory();
                this.loadTemplates();
                this.$nextTick(() => this.renderIcons());
            },

            async loadHistory() {
                const params = {
                    page: 1, limit: 50,
                    search: this.filters.search || '',
                    status: this.filters.status || '',
                    date_from: this.filters.dateFrom || '',
                    date_to: this.filters.dateTo || ''
                };
                const resp = await this.api('getSmsHistory', params, 'GET');
                this.history = (resp?.success ? resp.data?.history : []) || [];
                this.$nextTick(() => this.renderIcons());
            },

            viewSms(h) {
                const rec = Array.isArray(h.recipients) ? h.recipients : (h.recipients ? JSON.parse(h.recipients) : []);
                const html = `<div class="grid gap-2 text-sm">
                <div><b>Message:</b> ${this.escape(h.message)}</div>
                <div><b>Recipients:</b> ${rec.length ? rec.join(', ') : h.recipient_count}</div>
                <div><b>Status:</b> ${h.status}</div>
                <div><b>Cost:</b> Sh. ${this.formatMoney(h.total_cost)}</div>
                <div><b>Credits:</b> ${h.credits_used}</div>
                <div><b>Date:</b> ${this.formatDateTime(h.sent_at || h.scheduled_at || h.created_at)}</div>
            </div>`;
                this.toast('SMS Details', html, 'info', true);
            },

            badge(s) {
                if (s === 'sent') return { bg: 'bg-green-100 text-green-700', icon: 'check' };
                if (s === 'scheduled') return { bg: 'bg-blue-100 text-blue-700', icon: 'clock' };
                if (s === 'failed') return { bg: 'bg-red-100 text-red-700', icon: 'x' };
                return { bg: 'bg-slate-200 text-slate-700', icon: '' };
            },

            async loadTemplates() {
                const resp = await this.api('getSmsTemplates', {}, 'POST');
                this.templates = (resp?.success ? resp.data : []) || [];
                this.$nextTick(() => this.renderIcons());
            },
            renderTemplates() { this.$nextTick(() => this.renderIcons()); },
            openTemplateSelector() { if (!this.templates.length) this.loadTemplates(); this.modal.templateSelector = true; this.$nextTick(() => this.renderIcons()); },
            applyTemplate(t) { this.message = t.message; this.switchTab('send'); this.toast('Template Applied', `Template "${this.escape(t.name)}" inserted`, 'success'); },
            openTemplateForm() { this.templateForm = { id: '', name: '', message: '' }; this.modal.template = true; this.$nextTick(() => this.renderIcons()); },
            editTemplate(t) { this.templateForm = { id: t.id, name: t.name, message: t.message }; this.modal.template = true; this.$nextTick(() => this.renderIcons()); },
            async saveTemplate() {
                if (!this.templateForm.name.trim() || !this.templateForm.message.trim()) return this.toast('Missing Info', 'Fill all fields', 'error');
                const resp = await this.api('saveTemplate', { template_id: this.templateForm.id, name: this.templateForm.name, message: this.templateForm.message }, 'POST');
                if (resp?.success) { this.modal.template = false; await this.loadTemplates(); this.toast('Saved', resp.message || 'Template saved', 'success'); }
                else this.toast('Error', resp?.message || 'Failed to save template', 'error');
            },
            async deleteTemplate(t) {
                if (!confirm('Delete this template?')) return;
                const resp = await this.api('deleteTemplate', { template_id: t.id }, 'POST');
                if (resp?.success) { this.toast('Deleted', 'Template removed', 'success'); this.loadTemplates(); }
                else this.toast('Error', resp?.message || 'Failed to delete template', 'error');
            },

            selectPackage(v) { this.pkg = v; this.updateTopup(); this.$nextTick(() => this.renderIcons()); },
            updateTopup() {
                let credits = 0, cost = 0;
                if (this.pkg === 'custom') { const amt = Number(this.customAmount || 0); cost = amt; credits = this.rate > 0 ? Math.floor(cost / this.rate) : 0; }
                else { credits = parseInt(this.pkg || 0); cost = credits * this.rate; }
                this.topup = { credits, cost };
            },
            async purchaseCredits() {
                if (this.topup.cost > this.wallet.balance || this.topup.cost <= 0) return;
                const resp = await this.api('purchaseSmsCredits', { amount: this.topup.cost }, 'POST');
                if (resp?.success) { this.toast('Success', resp.message || 'Credits purchased', 'success'); this.pkg = '100'; this.customAmount = null; await this.loadAll(); }
                else this.toast('Error', resp?.message || 'Failed to purchase credits', 'error');
            },

            toast(title, html, type = 'info', raw = false) {
                const map = { success: ['bg-green-100', 'text-green-600', 'check'], error: ['bg-red-100', 'text-red-600', 'x'], warning: ['bg-amber-100', 'text-amber-700', 'alert-triangle'], info: ['bg-blue-100', 'text-blue-600', 'info'] };
                const [bg, fg, icon] = map[type] || map.info;
                this.messageModal.title = title;
                this.messageModal.html = raw ? html : this.escape(html);
                this.messageModal.bg = bg;
                this.messageModal.fg = fg;
                this.messageModal.icon = icon;
                this.modal.message = true;
                this.$nextTick(() => this.renderIcons());
            },

            api(action, data = {}, method = 'POST') {
                const url = 'fetch/manageSmsCenter.php';
                if (method === 'GET') {
                    const p = new URLSearchParams({ action, ...data });
                    return fetch(`${url}?${p.toString()}`).then(r => r.json()).catch(() => ({}));
                } else {
                    const fd = new FormData(); fd.append('action', action);
                    Object.keys(data).forEach(k => { if (data[k] !== undefined && data[k] !== null) fd.append(k, data[k]); });
                    return fetch(url, { method: 'POST', body: fd }).then(r => r.json()).catch(() => ({}));
                }
            },

            isoDate(d) { return new Date(d).toISOString().split('T')[0]; },
            formatDateTime(dt) { if (!dt) return ''; const d = new Date(dt); return d.toLocaleString([], { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }); },
            formatMoney(n) { return (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
            escape(s) { return (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])); }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>