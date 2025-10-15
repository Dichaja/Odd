<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Store Visit Requests';
$activeNav = 'buy-in-store';
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
ob_start();
?>
<div class="min-h-screen bg-user-content dark:bg-secondary text-gray-900 dark:text-white" x-data="userVisitsPage()"
    x-init="init()" x-on:keydown.escape="closeAnyModal()">
    <style>
        [x-cloak] {
            display: none
        }
    </style>
    <script>
        if (typeof window.BASE_URL === 'undefined') { window.BASE_URL = '<?= BASE_URL ?>' }
        const USER_ENDPOINT = `${window.BASE_URL}account/fetch/manageUserBuyInStore.php`;
    </script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 p-4 sm:p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full">
                    <div class="hidden sm:flex flex-wrap gap-2">
                        <button :class="dateBtnClass('daily')" @click="setPeriod('daily')"
                            class="flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm">
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar-days"
                                    class="w-4 h-4"></i>Daily</span>
                        </button>
                        <button :class="dateBtnClass('weekly')" @click="setPeriod('weekly')"
                            class="flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm">
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar"
                                    class="w-4 h-4"></i>Weekly</span>
                        </button>
                        <button :class="dateBtnClass('monthly')" @click="setPeriod('monthly')"
                            class="flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm">
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar-range"
                                    class="w-4 h-4"></i>Monthly</span>
                        </button>
                        <button :class="dateBtnClass('yearly')" @click="setPeriod('yearly')"
                            class="flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm">
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar-clock"
                                    class="w-4 h-4"></i>Yearly</span>
                        </button>
                    </div>
                    <div class="sm:hidden w-full">
                        <label class="sr-only">Period</label>
                        <select x-model="filters.period" @change="setPeriod(filters.period)"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg text-sm bg-white dark:bg-secondary text-gray-900 dark:text-white">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <button @click="openCalendar()"
                        class="px-4 py-2 rounded-lg border text-sm bg-white dark:bg-secondary text-gray-900 dark:text-white border-gray-300 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 inline-flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                        <span x-text="rangeLabel"></span>
                    </button>
                    <button @click="applyCurrentRange()"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">Apply</button>
                </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <div
                    class="rounded-xl p-4 border bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800/40">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-yellow-700 dark:text-yellow-300 uppercase tracking-wide">
                                Pending</p>
                            <p class="text-xl font-bold text-yellow-900 dark:text-yellow-100 truncate"
                                x-text="stats.pending.toLocaleString()"></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-yellow-200 dark:bg-yellow-800/40 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="clock" class="w-5 h-5 text-yellow-700 dark:text-yellow-300"></i>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl p-4 border bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/40">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-blue-700 dark:text-blue-300 uppercase tracking-wide">
                                Confirmed</p>
                            <p class="text-xl font-bold text-blue-900 dark:text-blue-100 truncate"
                                x-text="stats.confirmed.toLocaleString()"></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-blue-200 dark:bg-blue-800/40 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check-circle" class="w-5 h-5 text-blue-700 dark:text-blue-300"></i>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl p-4 border bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-green-700 dark:text-green-300 uppercase tracking-wide">
                                Completed</p>
                            <p class="text-xl font-bold text-green-900 dark:text-green-100 truncate"
                                x-text="stats.completed.toLocaleString()"></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-green-200 dark:bg-green-800/40 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check-check" class="w-5 h-5 text-green-700 dark:text-green-300"></i>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl p-4 border bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-red-700 dark:text-red-300 uppercase tracking-wide">
                                Cancelled</p>
                            <p class="text-xl font-bold text-red-900 dark:text-red-100 truncate"
                                x-text="stats.cancelled.toLocaleString()"></p>
                        </div>
                        <div
                            class="w-10 h-10 bg-red-200 dark:bg-red-800/40 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i data-lucide="x-circle" class="w-5 h-5 text-red-700 dark:text-red-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Requests</h3>
                        <p class="text-sm text-gray-600 dark:text-white/70">Tap any request to see store details, status
                            timeline, and actions</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" placeholder="Search by store, product..."
                                x-model.debounce.400ms="filters.search" @input="page=1;loadRequests()"
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-secondary text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-white/60">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                        </div>
                        <select x-model="filters.status" @change="page=1;loadRequests()"
                            class="px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg text-sm bg-white dark:bg-secondary text-gray-900 dark:text-white">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button @click="clearFilters()"
                            class="px-4 py-2 text-sm text-gray-600 dark:text-white/80 hover:text-gray-800 dark:hover:text-white border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/10">Clear
                            Filters</button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full min-w-[840px]">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Store</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Product</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Visit Date</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Quantity</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Est. Total</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/80 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        <template x-if="loading && requests.length===0">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                                    <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                                    <div>Loading requests...</div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!loading && requests.length===0">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                                    <i data-lucide="inbox" class="w-6 h-6 mx-auto mb-2"></i>
                                    <div>No requests found</div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="r in requests" :key="r.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/10 transition-colors cursor-pointer"
                                @click="openDetails(r.id)">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                        x-text="r.store_name"></div>
                                    <div class="text-xs text-gray-500 dark:text-white/70 truncate"
                                        x-text="r.region + ', ' + r.district"></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                        x-text="r.product_title"></div>
                                    <div class="text-xs text-gray-500 dark:text-white/70"
                                        x-text="capitalize(r.price_category)+' • UGX '+formatCurrency(r.price)"></div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white"
                                        x-text="formatDate(r.visit_date)"></div>
                                    <div class="text-xs" :class="visitInfo(r).color" x-text="visitInfo(r).text"></div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-white"
                                        x-text="r.quantity"></span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="text-sm font-bold text-green-600 dark:text-green-400"
                                        x-text="'UGX '+formatCurrency(r.total_value)"></span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap" x-html="statusBadge(r.status)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="lg:hidden">
                <template x-if="loading && requests.length===0">
                    <div class="p-4 text-center text-gray-500 dark:text-white/70">
                        <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <div>Loading requests...</div>
                    </div>
                </template>
                <template x-for="r in requests" :key="'m-'+r.id">
                    <div class="p-4 border-t border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 transition-colors cursor-pointer"
                        @click="openDetails(r.id)">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i data-lucide="store" class="w-5 h-5 text-blue-600 dark:text-blue-300"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                        x-text="r.store_name"></h4>
                                    <span class="text-xs" :class="visitInfo(r).color" x-text="visitInfo(r).text"></span>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-white/80 mb-1 truncate"
                                    x-text="r.product_title"></div>
                                <div class="text-xs text-gray-500 dark:text-white/70 mb-2"
                                    x-text="'UGX '+formatCurrency(r.price)+' per unit'"></div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span x-html="statusBadge(r.status)"></span>
                                        <span class="text-xs text-gray-500 dark:text-white/70"
                                            x-text="'Qty: '+r.quantity"></span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600 dark:text-green-400"
                                        x-text="'UGX '+formatCurrency(r.total_value)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="p-4 border-t border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-white/80">Showing <span
                            x-text="showingFrom"></span>-<span x-text="showingTo"></span> of <span
                            x-text="total"></span></div>
                    <div class="flex items-center gap-2">
                        <button @click="prev()" :disabled="page===1"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-white/10 rounded hover:bg-gray-50 dark:hover:bg-white/10 disabled:opacity-50">Previous</button>
                        <span class="px-3 py-1 text-sm text-gray-600 dark:text-white/80"
                            x-text="'Page '+page+' of '+Math.max(1,totalPages)"></span>
                        <button @click="next()" :disabled="page===totalPages || totalPages===0"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-white/10 rounded hover:bg-gray-50 dark:hover:bg-white/10 disabled:opacity-50">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="calendar.open" x-transition.opacity class="fixed inset-0 z-[65]">
        <div class="absolute inset-0 bg-black/50" @click="closeCalendar()"></div>
        <div
            class="relative w-full max-w-lg mx-auto top-1/2 -translate-y-1/2 bg-white dark:bg-secondary rounded-2xl shadow-xl overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900 dark:text-white" x-text="calendarTitle"></div>
                <button class="p-2 rounded hover:bg-gray-100 dark:hover:bg-white/10" @click="closeCalendar()"><i
                        data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-4 max-h-[80vh] overflow-y-auto">
                <template x-if="filters.period==='weekly' || filters.period==='daily'">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <button
                                    class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                    @click="navMonth(-1)"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                                <button
                                    class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                    @click="navMonth(1)"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                            </div>
                            <div class="flex items-center gap-2">
                                <select x-model.number="calendar.viewMonth" @change="syncCalFromControls()"
                                    class="px-2 py-1 border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-secondary text-gray-900 dark:text-white text-sm">
                                    <template x-for="(m, i) in months" :key="'m'+i">
                                        <option :value="i" x-text="m"></option>
                                    </template>
                                </select>
                                <input type="number" x-model.number="calendar.viewYear" @change="syncCalFromControls()"
                                    class="w-24 px-2 py-1 border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-secondary text-gray-900 dark:text-white text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-7 text-xs font-medium text-gray-500 dark:text-white/70 mb-1">
                            <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
                                <div class="text-center py-1" x-text="d"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="cell in monthGrid" :key="cell.key">
                                <button class="w-full aspect-square rounded border text-sm relative transition"
                                    :class="[cell.inMonth?'bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-white/10 border-gray-200 dark:border-white/10 text-gray-900 dark:text-white':'bg-gray-50 dark:bg-white/5 text-gray-400 dark:text-white/50 border-gray-200 dark:border-white/10',isInSelectedRange(cell.date)?'ring-2 ring-primary/60 ring-offset-1 ring-offset-white dark:ring-offset-secondary':'']"
                                    @click="selectCalendarDate(cell.date)">
                                    <span x-text="cell.day"></span>
                                    <span x-show="isSameDate(cell.date, filters.start) && filters.period==='daily'"
                                        class="absolute right-1 top-1 inline-block w-2 h-2 rounded-full bg-primary"></span>
                                </button>
                            </template>
                        </div>
                        <div class="mt-3 flex items-center justify-end gap-2">
                            <button
                                class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded hover:bg-gray-50 dark:hover:bg-white/10 text-sm"
                                @click="goToday()">Today</button>
                            <button class="px-3 py-2 bg-primary text-white rounded text-sm"
                                @click="applyFromCalendar()">Use Range</button>
                        </div>
                    </div>
                </template>

                <template x-if="filters.period==='monthly'">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <button
                                    class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                    @click="calendar.viewYear--; syncCalFromControls()"><i data-lucide="chevron-left"
                                        class="w-4 h-4"></i></button>
                                <div class="text-sm font-medium text-gray-900 dark:text-white"
                                    x-text="calendar.viewYear"></div>
                                <button
                                    class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                    @click="calendar.viewYear++; syncCalFromControls()"><i data-lucide="chevron-right"
                                        class="w-4 h-4"></i></button>
                            </div>
                            <input type="number" x-model.number="calendar.viewYear" @change="syncCalFromControls()"
                                class="w-28 px-2 py-1 border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-secondary text-gray-900 dark:text-white text-sm">
                        </div>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            <template x-for="(m, i) in months" :key="'mm'+i">
                                <button
                                    class="px-3 py-4 border border-gray-200 dark:border-white/10 rounded text-sm bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-white/10"
                                    :class="isSelectedMonth(i)?'ring-2 ring-primary/60 ring-offset-1 ring-offset-white dark:ring-offset-secondary':''"
                                    @click="selectMonth(i)">
                                    <span x-text="m"></span>
                                </button>
                            </template>
                        </div>
                        <div class="mt-4 text-right">
                            <button
                                class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded hover:bg-gray-50 dark:hover:bg-white/10 text-sm"
                                @click="goThisMonth()">Current Month</button>
                        </div>
                    </div>
                </template>

                <template x-if="filters.period==='yearly'">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <button
                                class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                @click="calendar.viewYear--"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <input type="number" x-model.number="calendar.viewYear"
                                class="w-28 px-2 py-1 border border-gray-200 dark:border-white/10 rounded bg-white dark:bg-secondary text-gray-900 dark:text-white text-sm text-center">
                            <button
                                class="p-2 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10"
                                @click="calendar.viewYear++"><i data-lucide="chevron-right"
                                    class="w-4 h-4"></i></button>
                        </div>
                        <div class="text-center">
                            <button class="px-4 py-2 bg-primary text-white rounded"
                                @click="selectYear(calendar.viewYear)">Use <?= htmlspecialchars('Year') ?></button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-cloak x-show="modals.details" x-transition.opacity class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div
            class="relative w-full max-w-6xl mx-auto my-4 sm:my-8 bg-white dark:bg-secondary rounded-xl shadow-lg max-h-[92vh] overflow-hidden">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white" x-text="detailHeader.title">
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-white/70 mt-0.5"
                        x-text="detailHeader.subtitle"></p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-1 rounded-full border border-gray-200 dark:border-white/10"
                        :class="statusPill.bg">
                        <span class="w-2 h-2 rounded-full" :class="statusPill.dot"></span>
                        <span class="text-xs font-medium" :class="statusPill.text"
                            x-text="capitalize(requestDetails.status)"></span>
                    </div>
                    <button @click="closeDetails()"
                        class="text-gray-500 dark:text-white/70 hover:text-gray-700 dark:hover:text-white p-2 rounded-lg hover:bg-white/60 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row max-h-[calc(92vh-88px)] overflow-hidden">
                <aside
                    class="w-full lg:w-80 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 overflow-y-auto max-h-[50vh] lg:max-h-none">
                    <div class="p-4">
                        <template x-if="loadingDetails">
                            <div class="text-center py-6">
                                <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                                <p class="text-gray-500 dark:text-white/70 text-sm">Loading...</p>
                            </div>
                        </template>
                        <template x-if="!loadingDetails">
                            <div class="space-y-4">
                                <div
                                    class="bg-white dark:bg-secondary rounded-lg p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">Store</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Name:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.store_name"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Location:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.region+', '+requestDetails.district"></span>
                                        </div>
                                        <template x-if="requestDetails.status==='confirmed'">
                                            <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-white/10">
                                                <div class="flex justify-between"><span
                                                        class="text-gray-600 dark:text-white/70">Email:</span><span
                                                        class="font-medium text-right break-all"
                                                        x-text="requestDetails.business_email"></span></div>
                                                <div class="flex justify-between"><span
                                                        class="text-gray-600 dark:text-white/70">Phone:</span><span
                                                        class="font-medium text-right"
                                                        x-text="requestDetails.business_phone"></span></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div
                                    class="bg-white dark:bg-secondary rounded-lg p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">Visit</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600 dark:text-white/70">Date:</span>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium"
                                                    x-text="formatDate(requestDetails.visit_date)"></span>
                                                <template
                                                    x-if="requestDetails.status==='confirmed' || requestDetails.status==='pending'">
                                                    <button
                                                        @click="openRescheduleCalendar(requestDetails.id, requestDetails.visit_date)"
                                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-white/70">When:</span>
                                            <span class="font-medium" :class="visitInfo(requestDetails).color"
                                                x-text="visitInfo(requestDetails).text"></span>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="bg-white dark:bg-secondary rounded-lg p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">Product</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Item:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.product_title"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Package:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.package_size+' '+requestDetails.si_unit+' '+requestDetails.package_name"></span>
                                        </div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Price Type:</span><span
                                                class="font-medium text-right"
                                                x-text="capitalize(requestDetails.price_category)"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Quantity:</span><span
                                                class="font-medium" x-text="requestDetails.quantity"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600 dark:text-white/70">Unit Price:</span><span
                                                class="font-medium"
                                                x-text="'UGX '+formatCurrency(requestDetails.price)"></span></div>
                                        <div
                                            class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-2">
                                            <span class="text-gray-900 dark:text-white font-medium">Total:</span><span
                                                class="font-bold text-green-600 dark:text-green-400"
                                                x-text="'UGX '+formatCurrency(requestDetails.total_value)"></span>
                                        </div>
                                        <div class="pt-2">
                                            <a :href="detailProductUrl" target="_blank"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs underline">View
                                                product details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </aside>
                <section class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6">
                    <template x-if="loadingDetails">
                        <div class="text-center py-8">
                            <i data-lucide="loader-2" class="w-7 h-7 mx-auto mb-2 animate-spin"></i>
                            <p class="text-gray-500 dark:text-white/70">Loading request details...</p>
                        </div>
                    </template>
                    <template x-if="!loadingDetails">
                        <div class="space-y-6">
                            <div
                                class="bg-white dark:bg-secondary rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                                <div class="space-y-4">
                                    <div class="relative">
                                        <img :src="detailProductImage" alt="Product Image"
                                            class="w-full h-48 object-cover rounded-lg border border-gray-200 dark:border-white/10">
                                        <a :href="detailProductUrl" target="_blank"
                                            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 hover:opacity-100 transition text-white font-medium rounded-lg">
                                            View product details
                                        </a>
                                    </div>
                                    <div>
                                        <a :href="detailProductUrl" target="_blank" aria-label="View product details"
                                            class="inline-flex items-center gap-2 font-semibold text-blue-600 dark:text-blue-400 underline underline-offset-4 hover:underline hover:text-blue-800 dark:hover:text-blue-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/50 rounded-sm">
                                            <span x-text="requestDetails.product_title"></span>
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white dark:bg-secondary rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                                    <i data-lucide="list" class="w-5 h-5 text-purple-600"></i><span>Status &
                                        Actions</span>
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <span x-html="statusBadge(requestDetails.status)"></span>
                                            <span class="text-sm text-gray-600 dark:text-white/70">Requested on <span
                                                    class="font-medium"
                                                    x-text="formatDate(requestDetails.created_at)"></span> at <span
                                                    class="font-medium"
                                                    x-text="formatTime(requestDetails.created_at)"></span></span>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:textWhite/70 dark:text-white/70">Visit
                                            scheduled for <span class="font-medium"
                                                x-text="formatDate(requestDetails.visit_date)"></span> (<span
                                                :class="visitInfo(requestDetails).color"
                                                x-text="visitInfo(requestDetails).text"></span>)</div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <template
                                            x-if="requestDetails.status==='pending' || requestDetails.status==='confirmed'">
                                            <button
                                                @click="openRescheduleCalendar(requestDetails.id, requestDetails.visit_date)"
                                                class="flex items-center justify-center px-3 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                <i data-lucide="calendar-check" class="w-5 h-5 mr-2"></i>Reschedule
                                            </button>
                                        </template>
                                        <template x-if="requestDetails.status==='confirmed'">
                                            <button @click="callStore(requestDetails.business_phone)"
                                                class="flex items-center justify-center px-3 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                <i data-lucide="phone" class="w-5 h-5 mr-2"></i>Call Store
                                            </button>
                                        </template>
                                        <template x-if="requestDetails.status==='confirmed'">
                                            <button @click="openEmail()"
                                                class="flex items-center justify-center px-3 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                                <i data-lucide="mail" class="w-5 h-5 mr-2"></i>Email Store
                                            </button>
                                        </template>
                                        <template
                                            x-if="requestDetails.status==='pending' || requestDetails.status==='confirmed'">
                                            <button
                                                @click="confirmAction('Cancel this visit request?', ()=>cancelRequest(requestDetails.id,true))"
                                                class="flex items-center justify-center px-3 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                <i data-lucide="x-octagon" class="w-5 h-5 mr-2"></i>Cancel Request
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white dark:bg-secondary rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm">
                                <h4
                                    class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-rose-600"></i><span>Location</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-white/70">Address</p>
                                        <p class="font-medium"
                                            x-text="requestDetails.address || (requestDetails.region+', '+requestDetails.district)">
                                        </p>
                                    </div>
                                    <div class="flex items-end sm:items-center gap-3">
                                        <button
                                            @click="openInMaps(requestDetails.latitude, requestDetails.longitude, requestDetails.store_name)"
                                            class="px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg text-sm bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-white/10 inline-flex items-center gap-2">
                                            <i data-lucide="map" class="w-4 h-4"></i>Open in Maps
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>
            </div>
        </div>
    </div>

    <div x-cloak x-show="modals.email" x-transition.opacity class="fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50" @click="closeEmail"></div>
        <div
            class="relative w-full max-w-2xl mx-auto my-6 bg-white dark:bg-secondary rounded-xl shadow-lg max-h-[90vh] overflow-hidden">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 dark:border-white/10 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-900/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Email Store</h3>
                <button @click="closeEmail"
                    class="text-gray-500 dark:text-white/70 hover:text-gray-700 dark:hover:text-white p-2"><i
                        data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="sendEmail" class="p-4 sm:p-6 space-y-4 overflow-y-auto max-h-[calc(90vh-88px)]">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-2">To</label>
                    <input type="email" x-model="emailForm.to" readonly
                        class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-white/90">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-2">Subject</label>
                    <input type="text" x-model="emailForm.subject"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-secondary text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-2">Message</label>
                    <textarea rows="6" x-model="emailForm.message"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary bg-white dark:bg-secondary text-gray-900 dark:text-white"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeEmail"
                        class="px-4 py-2 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white rounded-lg bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-white/10">Close</button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors inline-flex items-center gap-2"><i
                            data-lucide="send" class="w-4 h-4"></i>Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="modals.confirm" x-transition.opacity class="fixed inset-0 z-[70]">
        <div class="absolute inset-0 bg-black/60" @click="closeConfirm"></div>
        <div
            class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white dark:bg-secondary rounded-xl shadow-lg">
            <div class="p-5">
                <div class="flex items-start gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-700 dark:text-amber-300"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Please Confirm</h3>
                        <p class="text-sm text-gray-700 dark:text-white/80 mt-1" x-text="confirmDialog.message"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeConfirm"
                        class="px-4 py-2 border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white rounded-lg bg-white dark:bg-secondary hover:bg-gray-50 dark:hover:bg-white/10">No</button>
                    <button @click="runConfirm"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="toast.show" x-transition.opacity
        class="fixed inset-x-0 bottom-4 z-[80] flex justify-center px-4">
        <div class="w-full max-w-md rounded-lg shadow-lg border"
            :class="toast.type==='success'?'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800/40':'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/40'">
            <div class="p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center"
                    :class="toast.type==='success'?'bg-green-100 dark:bg-green-800/40':'bg-red-100 dark:bg-red-800/40'">
                    <i :data-lucide="toast.type==='success' ? 'check' : 'x-circle'"
                        :class="toast.type==='success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'"
                        class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <p class="font-medium"
                        :class="toast.type==='success'?'text-green-900 dark:text-green-100':'text-red-900 dark:text-red-100'"
                        x-text="toast.title"></p>
                    <p class="text-sm mt-0.5"
                        :class="toast.type==='success'?'text-green-800 dark:text-green-200':'text-red-800 dark:text-red-200'"
                        x-text="toast.message"></p>
                </div>
                <button @click="toast.show=false" class="p-2 rounded hover:bg-white/60 dark:hover:bg-white/10"><i
                        data-lucide="x" class="w-4 h-4"></i></button>
            </div>
        </div>
    </div>

    <script>
        function userVisitsPage() {
            return {
                requests: [], total: 0, page: 1, perPage: 20, totalPages: 0, loading: true,
                stats: { pending: 0, confirmed: 0, completed: 0, cancelled: 0 },
                filters: { period: 'monthly', start: '', end: '', search: '', status: 'all' },
                calendar: { open: false, viewYear: 0, viewMonth: 0, selectedDate: null, context: 'filter' },
                months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                get calendarTitle() { if (this.filters.period === 'monthly') return 'Select Month'; if (this.filters.period === 'yearly') return 'Select Year'; if (this.filters.period === 'weekly') return this.months[this.calendar.viewMonth] + ' ' + this.calendar.viewYear; return 'Select Date' },
                get monthGrid() { const first = this.tzDate(this.calendar.viewYear, this.calendar.viewMonth, 1); const start = new Date(first); start.setDate(first.getDate() - first.getDay()); const cells = []; for (let i = 0; i < 42; i++) { const d = new Date(start); d.setDate(start.getDate() + i); cells.push({ key: d.toISOString().slice(0, 10), day: d.getDate(), inMonth: d.getMonth() === this.calendar.viewMonth, date: d.toISOString().slice(0, 10) }) } return cells },
                get rangeLabel() { if (this.filters.period === 'daily') return this.formatDate(this.filters.start); if (this.filters.period === 'weekly') return this.formatDateShort(this.filters.start) + ' - ' + this.formatDateShort(this.filters.end); if (this.filters.period === 'monthly') return this.months[new Date(this.filters.start).getMonth()]; if (this.filters.period === 'yearly') return String(new Date(this.filters.start).getFullYear()); return '' },
                modals: { details: false, email: false, confirm: false },
                requestDetails: {}, loadingDetails: false, detailHeader: { title: '', subtitle: '' },
                statusPill: { bg: 'bg-yellow-100 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800/40', dot: 'bg-yellow-500 dark:bg-yellow-400', text: 'text-yellow-700 dark:text-yellow-300' },
                dateForm: { id: null, value: '', note: '' }, todayStr: '', emailForm: { to: '', subject: '', message: '' },
                toast: { show: false, type: 'success', title: '', message: '' }, confirmDialog: { message: '', fn: null },
                detailProductUrl: '#', detailProductImage: 'https://placehold.co/800x480/e2e8f0/1e293b?text=Product',
                get showingFrom() { return this.total === 0 ? 0 : ((this.page - 1) * this.perPage) + 1 },
                get showingTo() { return Math.min(this.page * this.perPage, this.total) },
                dateBtnClass(p) { return (this.filters.period === p) ? 'px-4 py-2 rounded-lg border text-sm bg-primary text-white border-primary' : 'px-4 py-2 rounded-lg border text-sm text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10 bg-white dark:bg-secondary border-gray-300 dark:border-white/10' },
                capitalize(s) { return (s || '').charAt(0).toUpperCase() + (s || '').slice(1) },
                formatCurrency(n) { return (new Intl.NumberFormat('en-UG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })).format(n || 0) },
                formatDate(d) { const date = new Date(d); const m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const day = date.getDate(); const s = (day == 1 || day == 21 || day == 31) ? 'st' : (day == 2 || day == 22) ? 'nd' : (day == 3 || day == 23) ? 'rd' : 'th'; return `${m[date.getMonth()]} ${day}${s}, ${date.getFullYear()}` },
                formatDateShort(d) { const date = new Date(d); return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) },
                formatTime(d) { const date = new Date(d); let h = date.getHours(); const m = ('' + date.getMinutes()).padStart(2, '0'); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; return `${h}:${m}${ap}` },
                tzNow() { return new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Kampala' })) },
                tzDate(y, m, d) { const dt = new Date(Date.UTC(y, m, d || 1)); const local = new Date(dt.toLocaleString('en-US', { timeZone: 'Africa/Kampala' })); return local },
                visitInfo(r) { const visit = new Date(r.visit_date); const today = this.tzNow(); today.setHours(0, 0, 0, 0); visit.setHours(0, 0, 0, 0); const diff = Math.ceil((visit.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)); const s = (r.status || '').toLowerCase(); if (s === 'completed') return { text: 'Completed', color: 'text-green-600 dark:text-green-400' }; if (s === 'cancelled') return { text: 'Cancelled', color: 'text-red-600 dark:text-red-400' }; if (diff > 0) return { text: `In ${diff} day${diff > 1 ? 's' : ''}`, color: 'text-blue-600 dark:text-blue-400' }; if (diff === 0) return { text: 'Today', color: 'text-orange-600 dark:text-orange-400' }; return { text: `${Math.abs(diff)} day${Math.abs(diff) > 1 ? 's' : ''} overdue`, color: 'text-red-600 dark:text-red-400' } },
                statusBadge(s) { s = (s || '').toLowerCase(); if (s === 'pending') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">Pending</span>'; if (s === 'confirmed') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">Confirmed</span>'; if (s === 'completed') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">Completed</span>'; if (s === 'cancelled') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200">Cancelled</span>'; return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-white">Unknown</span>' },
                productDetailsUrl(id) { return `${window.BASE_URL}view/product/${id}` },
                productImagesBase(id) { return `${window.BASE_URL}img/products/${id}/` },
                placeholderImg(title) { return `https://placehold.co/800x480/e2e8f0/1e293b?text=${encodeURIComponent(title || 'Product')}` },
                async resolveProductImage(prodId, title) { if (!prodId) return this.placeholderImg(title); const base = this.productImagesBase(prodId); try { const resp = await fetch(base); if (resp.ok) { const html = await resp.text(); const matches = [...html.matchAll(/href="([^"]+\.(?:jpe?g|png|webp|gif))"/gi)]; if (matches.length) { const rel = matches[0][1]; return rel.startsWith('http') ? rel : base + rel.replace(/^\.?\//, '') } } } catch (e) { } const names = ['1', 'main', 'cover', 'image', 'product', 'thumb', '0']; const exts = ['jpg', 'jpeg', 'png', 'webp', 'gif']; for (const n of names) { for (const ext of exts) { const url = `${base}${n}.${ext}`; try { const r = await fetch(url, { method: 'HEAD' }); if (r.ok) return url } catch (e) { } } } return this.placeholderImg(title) },
                setPeriod(p) { this.filters.period = p; const now = this.tzNow(); if (p === 'daily') this.setDaily(now); if (p === 'weekly') this.setWeek(now); if (p === 'monthly') this.setMonth(now.getFullYear(), now.getMonth()); if (p === 'yearly') this.setYear(now.getFullYear()); this.loadRequests(); this.loadStats() },
                setDaily(date) { const iso = date.toISOString().split('T')[0]; this.filters.start = iso; this.filters.end = iso; this.calendar.viewYear = date.getFullYear(); this.calendar.viewMonth = date.getMonth() },
                setWeek(date) { const start = new Date(date); start.setDate(date.getDate() - date.getDay()); const end = new Date(start); end.setDate(start.getDate() + 6); this.filters.start = start.toISOString().split('T')[0]; this.filters.end = end.toISOString().split('T')[0]; this.calendar.viewYear = date.getFullYear(); this.calendar.viewMonth = date.getMonth() },
                setMonth(year, monthIdx) { const s = this.tzDate(year, monthIdx, 1); const e = this.tzDate(year, monthIdx + 1, 0); this.filters.start = s.toISOString().split('T')[0]; this.filters.end = e.toISOString().split('T')[0]; this.calendar.viewYear = year; this.calendar.viewMonth = monthIdx },
                setYear(year) { const s = this.tzDate(year, 0, 1); const e = this.tzDate(year, 11, 31); this.filters.start = s.toISOString().split('T')[0]; this.filters.end = e.toISOString().split('T')[0]; this.calendar.viewYear = year; this.calendar.viewMonth = 0 },
                openCalendar() { this.calendar.context = 'filter'; this.calendar.open = true; this.calendar.selectedDate = this.filters.start; this.nextTickIcons() },
                openRescheduleCalendar(id, current) { this.dateForm.id = id; this.calendar.context = 'reschedule'; this.filters.period = 'daily'; this.calendar.selectedDate = current; const dt = new Date(current); this.calendar.viewYear = dt.getFullYear(); this.calendar.viewMonth = dt.getMonth(); this.calendar.open = true; this.nextTickIcons() },
                closeCalendar() { this.calendar.open = false },
                navMonth(delta) { let m = this.calendar.viewMonth + delta; let y = this.calendar.viewYear; if (m < 0) { m = 11; y-- } if (m > 11) { m = 0; y++ } this.calendar.viewMonth = m; this.calendar.viewYear = y },
                syncCalFromControls() { if (this.calendar.viewMonth < 0) this.calendar.viewMonth = 0; if (this.calendar.viewMonth > 11) this.calendar.viewMonth = 11 },
                selectCalendarDate(iso) { this.calendar.selectedDate = iso; if (this.calendar.context === 'filter') { if (this.filters.period === 'daily') { this.setDaily(new Date(iso)) } if (this.filters.period === 'weekly') { this.setWeek(new Date(iso)) } } },
                isSameDate(a, b) { return a && b && new Date(a).toISOString().slice(0, 10) === new Date(b).toISOString().slice(0, 10) },
                isInSelectedRange(iso) { const d = new Date(iso); const s = new Date(this.filters.start); const e = new Date(this.filters.end); return d >= s && d <= e },
                isSelectedMonth(idx) { const m = new Date(this.filters.start).getMonth(); const y = new Date(this.filters.start).getFullYear(); return this.filters.period === 'monthly' && m === idx && y === this.calendar.viewYear },
                selectMonth(idx) { this.setMonth(this.calendar.viewYear, idx); this.calendar.open = false },
                selectYear(y) { this.setYear(y); this.calendar.open = false },
                goToday() { const t = this.tzNow(); this.calendar.viewYear = t.getFullYear(); this.calendar.viewMonth = t.getMonth(); this.selectCalendarDate(t.toISOString().split('T')[0]) },
                goThisMonth() { const t = this.tzNow(); this.setMonth(t.getFullYear(), t.getMonth()); this.calendar.open = false },
                applyFromCalendar() { if (this.calendar.context === 'filter') { if (this.filters.period !== 'monthly' && this.filters.period !== 'yearly') { if (this.calendar.selectedDate) this.selectCalendarDate(this.calendar.selectedDate) } } else { if (this.calendar.selectedDate) { this.dateForm.value = this.calendar.selectedDate; this.saveDate() } } this.calendar.open = false },
                applyCurrentRange() { this.page = 1; this.loadRequests(); this.loadStats() },
                clearFilters() { this.filters.search = ''; this.filters.status = 'all'; this.page = 1; this.loadRequests(); this.loadStats() },
                prev() { if (this.page > 1) { this.page--; this.loadRequests() } },
                next() { if (this.page < this.totalPages) { this.page++; this.loadRequests() } },
                async loadStats() { try { const params = new URLSearchParams({ action: 'getStats', start_date: this.filters.start, end_date: this.filters.end }); const r = await fetch(`${USER_ENDPOINT}?` + params.toString()); const j = await r.json(); if (j.success) { this.stats = { pending: parseInt(j.stats.pending || 0), confirmed: parseInt(j.stats.confirmed || 0), completed: parseInt(j.stats.completed || 0), cancelled: parseInt(j.stats.cancelled || 0) }; this.nextTickIcons() } } catch (e) { } },
                async loadRequests() { this.loading = true; try { const params = new URLSearchParams({ action: 'getRequests', start_date: this.filters.start, end_date: this.filters.end, search_term: this.filters.search, status_filter: this.filters.status, page: this.page }); const r = await fetch(`${USER_ENDPOINT}?` + params.toString()); const j = await r.json(); if (j.success) { this.requests = (j.requestData.data || []).map(x => { const pid = x.product_id || x.productId || x.pid || null; return Object.assign({}, x, { _pid: pid, product_url_fixed: pid ? this.productDetailsUrl(pid) : '#', product_image_url: this.placeholderImg(x.product_title) }) }); this.total = parseInt(j.requestData.total || 0); this.page = parseInt(j.requestData.page || 1); this.totalPages = Math.ceil(this.total / this.perPage); for (const it of this.requests) { if (it._pid) { this.resolveProductImage(it._pid, it.product_title).then(u => { it.product_image_url = u; this.nextTickIcons() }) } } } else { this.toastError('Failed', 'Failed to load requests') } } catch (e) { this.toastError('Error', 'An error occurred while loading requests') } finally { this.loading = false; this.nextTickIcons() } },
                async openDetails(id) { this.modals.details = true; this.loadingDetails = true; this.requestDetails = {}; this.detailHeader = { title: 'Visit Request', subtitle: 'Loading...' }; this.detailProductUrl = '#'; this.detailProductImage = this.placeholderImg('Product'); try { const r = await fetch(`${USER_ENDPOINT}?action=getRequestDetails&id=${id}`); const j = await r.json(); if (j.success) { this.requestDetails = j.request; this.detailHeader = { title: this.requestDetails.store_name, subtitle: this.requestDetails.product_title }; const pid = this.requestDetails.product_id || this.requestDetails.productId || this.requestDetails.pid; this.detailProductUrl = pid ? this.productDetailsUrl(pid) : '#'; this.detailProductImage = await this.resolveProductImage(pid, this.requestDetails.product_title); this.computeStatusPill() } else { this.toastError('Failed', 'Failed to load request details') } } catch (e) { this.toastError('Error', 'An error occurred while loading details') } finally { this.loadingDetails = false; this.nextTickIcons() } },
                closeDetails() { this.modals.details = false; this.requestDetails = {} },
                computeStatusPill() { const s = (this.requestDetails.status || '').toLowerCase(); if (s === 'pending') this.statusPill = { bg: 'bg-yellow-100 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800/40', dot: 'bg-yellow-500 dark:bg-yellow-400', text: 'text-yellow-700 dark:text-yellow-300' }; else if (s === 'confirmed') this.statusPill = { bg: 'bg-blue-100 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/40', dot: 'bg-blue-500 dark:bg-blue-400', text: 'text-blue-700 dark:text-blue-300' }; else if (s === 'completed') this.statusPill = { bg: 'bg-green-100 dark:bg-green-900/20 border-green-200 dark:border-green-800/40', dot: 'bg-green-500 dark:bg-green-400', text: 'text-green-700 dark:text-green-300' }; else this.statusPill = { bg: 'bg-red-100 dark:bg-red-900/20 border-red-200 dark:border-red-800/40', dot: 'bg-red-500 dark:bg-red-400', text: 'text-red-700 dark:text-red-300' } },
                async saveDate() { if (!this.dateForm.value) { this.toastError('Error', 'Please select a date'); return } if (this.dateForm.value < this.todayStr) { this.toastError('Error', 'Visit date cannot be in the past'); return } try { const fd = new FormData(); fd.append('request_id', this.dateForm.id); fd.append('visit_date', this.dateForm.value); const r = await fetch(`${USER_ENDPOINT}?action=requestReschedule`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.toastSuccess('Submitted', 'Reschedule request sent'); await this.openDetails(this.dateForm.id); this.loadRequests(); this.loadStats() } else { this.toastError('Failed', j.message || 'Failed to submit reschedule') } } catch (e) { this.toastError('Error', 'An error occurred while submitting') } },
                confirmAction(message, fn) { this.confirmDialog.message = message; this.confirmDialog.fn = fn; this.modals.confirm = true; this.nextTickIcons() },
                runConfirm() { if (typeof this.confirmDialog.fn === 'function') { this.modals.confirm = false; this.confirmDialog.fn() } },
                closeConfirm() { this.modals.confirm = false; this.confirmDialog = { message: '', fn: null } },
                async cancelRequest(id, fromDetails = false) { try { const fd = new FormData(); fd.append('request_id', id); const r = await fetch(`${USER_ENDPOINT}?action=cancelRequest`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.toastSuccess('Cancelled', 'Your request has been cancelled'); if (fromDetails) { await this.openDetails(id) } this.loadRequests(); this.loadStats() } else { this.toastError('Failed', j.message || 'Failed to cancel request') } } catch (e) { this.toastError('Error', 'An error occurred while cancelling') } },
                callStore(phone) { if (!phone) return; window.location.href = `tel:${phone}` },
                openInMaps(lat, lng, label) { if (!lat || !lng) { this.toastError('Location', 'No map coordinates available'); return } const url = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(lat + ',' + lng)}&query_place_id=${encodeURIComponent(label || 'Store')}`; window.open(url, '_blank') },
                openEmail() { if (!this.requestDetails.business_email) return; this.emailForm.to = this.requestDetails.business_email; this.emailForm.subject = `Store Visit: ${this.requestDetails.product_title}`; this.emailForm.message = `Hello ${this.requestDetails.store_name},\n\nI would like to follow up on my store visit request for ${this.requestDetails.product_title} scheduled on ${this.formatDate(this.requestDetails.visit_date)}.\n\nRegards,\n`; this.modals.email = true; this.nextTickIcons() },
                closeEmail() { this.modals.email = false; this.emailForm = { to: '', subject: '', message: '' } },
                toastSuccess(t, m) { this.toast = { show: true, type: 'success', title: t, message: m }; this.nextTickIcons(); setTimeout(() => { this.toast.show = false }, 2500) },
                toastError(t, m) { this.toast = { show: true, type: 'error', title: t, message: m }; this.nextTickIcons(); setTimeout(() => { this.toast.show = false }, 3000) },
                closeAnyModal() { if (this.modals.details) this.closeDetails(); if (this.modals.email) this.closeEmail(); if (this.modals.confirm) this.closeConfirm(); if (this.calendar.open) this.closeCalendar() },
                nextTickIcons() { queueMicrotask(() => { if (window.lucide && typeof window.lucide.createIcons === 'function') { window.lucide.createIcons() } }) },
                async loadAll() { await Promise.all([this.loadRequests(), this.loadStats()]) },
                init() { const t = this.tzNow(); this.todayStr = t.toISOString().split('T')[0]; this.setMonth(t.getFullYear(), t.getMonth()); this.loadAll(); setInterval(() => { this.loadAll() }, 30000); this.nextTickIcons() }
            }
        }
    </script>
</div>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>