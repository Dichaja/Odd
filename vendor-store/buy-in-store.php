<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Store Visit Requests';
$activeNav = 'buy-in-store';
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
ob_start();
?>
<div class="min-h-screen bg-gray-50" x-data="storeVisitsPage()" x-init="init()" x-on:keydown.escape="closeAnyModal()">
    <style>
        [x-cloak] {
            display: none
        }
    </style>
    <script>
        if (typeof window.BASE_URL === 'undefined') { window.BASE_URL = '<?= BASE_URL ?>'; }
        if (!window.$) { window.$ = {}; }
        if (!window.$.getJSON) { window.$.getJSON = function (u, s) { fetch(u).then(r => r.json()).then(d => { if (typeof s === 'function') s(d); }).catch(() => { }); }; }
    </script>

    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <h1 class="text-lg sm:text-2xl font-bold text-gray-900">Store Visit Requests</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor and manage customer store
                        visit requests</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button @click="refresh()"
                        class="px-3 sm:px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="refresh-ccw" class="w-4 h-4" :class="{'animate-spin': refreshing}"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="rounded-xl p-4 border bg-yellow-50 border-yellow-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-yellow-700 uppercase tracking-wide">Pending</p>
                        <p class="text-xl font-bold text-yellow-900 truncate" x-text="stats.pending.toLocaleString()">
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-yellow-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-700"></i>
                    </div>
                </div>
            </div>
            <div class="rounded-xl p-4 border bg-blue-50 border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-700 uppercase tracking-wide">Confirmed</p>
                        <p class="text-xl font-bold text-blue-900 truncate" x-text="stats.confirmed.toLocaleString()">
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="check-circle" class="w-5 h-5 text-blue-700"></i>
                    </div>
                </div>
            </div>
            <div class="rounded-xl p-4 border bg-green-50 border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-700 uppercase tracking-wide">Completed</p>
                        <p class="text-xl font-bold text-green-900 truncate" x-text="stats.completed.toLocaleString()">
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="check-check" class="w-5 h-5 text-green-700"></i>
                    </div>
                </div>
            </div>
            <div class="rounded-xl p-4 border bg-red-50 border-red-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-red-700 uppercase tracking-wide">Cancelled</p>
                        <p class="text-xl font-bold text-red-900 truncate" x-text="stats.cancelled.toLocaleString()">
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-700"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
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
                    </div>
                    <div class="sm:hidden w-full">
                        <label class="sr-only">Period</label>
                        <select x-model="filters.period" @change="setPeriod(filters.period)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" x-model="filters.start"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" x-model="filters.end"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button @click="applyCustomRange()"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Store Visit Requests</h3>
                        <p class="text-sm text-gray-600">Click on any request to view and manage details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" placeholder="Search requests..." x-model.debounce.400ms="filters.search"
                                @input="page=1;loadRequests()"
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                        </div>
                        <select x-model="filters.status" @change="page=1;loadRequests()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button @click="clearFilters()"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">Clear
                            Filters</button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Customer Details</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Product</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Visit Date</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Quantity</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Total Value</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="loading && requests.length===0">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                                    <div>Loading requests...</div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!loading && requests.length===0">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <i data-lucide="inbox" class="w-6 h-6 mx-auto mb-2"></i>
                                    <div>No requests found</div>
                                </td>
                            </tr>
                        </template>
                        <template x-for="r in requests" :key="r.id">
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" @click="openDetails(r.id)">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 text-sm"
                                        x-text="r.first_name+' '+r.last_name"></div>
                                    <div class="text-xs text-gray-500" x-show="r.status==='confirmed'" x-text="r.email">
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 truncate" x-text="r.product_title">
                                    </div>
                                    <div class="text-xs text-gray-500"
                                        x-text="capitalize(r.price_category)+' - UGX '+formatCurrency(r.price)"></div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="formatDate(r.visit_date)"></div>
                                    <div class="text-xs" :class="visitInfo(r).color" x-text="visitInfo(r).text"></div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                        x-text="r.quantity"></span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="text-sm font-bold text-green-600"
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
                    <div class="p-4 text-center text-gray-500">
                        <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                        <div>Loading requests...</div>
                    </div>
                </template>
                <template x-for="r in requests" :key="'m-'+r.id">
                    <div class="p-4 border-t border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer"
                        @click="openDetails(r.id)">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i data-lucide="calendar-check" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h4 class="text-sm font-medium text-gray-900 truncate"
                                        x-text="r.first_name+' '+r.last_name"></h4>
                                    <span class="text-xs" :class="visitInfo(r).color" x-text="visitInfo(r).text"></span>
                                </div>
                                <div class="text-sm text-gray-600 mb-1 truncate" x-text="r.product_title"></div>
                                <div class="text-xs text-gray-500 mb-2"
                                    x-text="'UGX '+formatCurrency(r.price)+' per unit'"></div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span x-html="statusBadge(r.status)"></span>
                                        <span class="text-xs text-gray-500" x-text="'Qty: '+r.quantity"></span>
                                    </div>
                                    <span class="text-sm font-bold text-green-600"
                                        x-text="'UGX '+formatCurrency(r.total_value)"></span>
                                </div>
                                <div class="mt-1 text-xs text-gray-500" x-show="r.status==='confirmed'"
                                    x-text="r.email"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-sm text-gray-600">Showing <span x-text="showingFrom"></span>-<span
                            x-text="showingTo"></span> of <span x-text="total"></span></div>
                    <div class="flex items-center gap-2">
                        <button @click="prev()" :disabled="page===1"
                            class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50">Previous</button>
                        <span class="px-3 py-1 text-sm text-gray-600"
                            x-text="'Page '+page+' of '+Math.max(1,totalPages)"></span>
                        <button @click="next()" :disabled="page===totalPages || totalPages===0"
                            class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="modals.details" x-transition.opacity class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div
            class="relative w-full h-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg max-h-[95vh] overflow-hidden">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900" x-text="detailHeader.title"></h3>
                    <p class="text-xs sm:text-sm text-gray-600 mt-0.5" x-text="detailHeader.subtitle"></p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-1 rounded-full border" :class="statusPill.bg">
                        <span class="w-2 h-2 rounded-full" :class="statusPill.dot"></span>
                        <span class="text-xs font-medium" :class="statusPill.text"
                            x-text="capitalize(requestDetails.status)"></span>
                    </div>
                    <button @click="closeDetails()"
                        class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-white/60">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <div class="flex flex-col lg:flex-row h-[calc(95vh-96px)]">
                <aside
                    class="w-full lg:w-80 border-b lg:border-b-0 lg:border-r border-gray-200 bg-gray-50 overflow-y-auto">
                    <div class="p-4">
                        <template x-if="loadingDetails">
                            <div class="text-center py-6">
                                <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                                <p class="text-gray-500 text-sm">Loading...</p>
                            </div>
                        </template>
                        <template x-if="!loadingDetails">
                            <div class="space-y-4">
                                <template x-if="requestDetails.status==='confirmed'">
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <h4 class="font-semibold text-gray-900 mb-3 text-sm">Customer Details</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span
                                                    class="text-gray-600">Name:</span><span class="font-medium"
                                                    x-text="requestDetails.first_name+' '+requestDetails.last_name"></span>
                                            </div>
                                            <div class="flex justify-between"><span
                                                    class="text-gray-600">Email:</span><span class="font-medium"
                                                    x-text="requestDetails.email"></span></div>
                                            <div class="flex justify-between"><span
                                                    class="text-gray-600">Phone:</span><span class="font-medium"
                                                    x-text="requestDetails.phone"></span></div>
                                            <template x-if="requestDetails.alt_contact">
                                                <div class="flex justify-between"><span class="text-gray-600">Alt
                                                        Contact:</span><span class="font-medium"
                                                        x-text="requestDetails.alt_contact"></span></div>
                                            </template>
                                            <template x-if="requestDetails.alt_email">
                                                <div class="flex justify-between"><span class="text-gray-600">Alt
                                                        Email:</span><span class="font-medium"
                                                        x-text="requestDetails.alt_email"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Visit Information</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Visit Date:</span>
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium"
                                                    x-text="formatDate(requestDetails.visit_date)"></span>
                                                <template x-if="requestDetails.status==='confirmed'">
                                                    <button
                                                        @click="openDateModal(requestDetails.id, requestDetails.visit_date)"
                                                        class="text-blue-600 hover:text-blue-800 text-xs p-1 rounded hover:bg-blue-50">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Status:</span>
                                            <span class="font-medium" :class="visitInfo(requestDetails).color"
                                                x-text="visitInfo(requestDetails).text"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">Product Details</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span
                                                class="text-gray-600">Product:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.product_title"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600">Package:</span><span
                                                class="font-medium text-right"
                                                x-text="requestDetails.package_size+' '+requestDetails.si_unit+' '+requestDetails.package_name"></span>
                                        </div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600">Category:</span><span
                                                class="font-medium text-right"
                                                x-text="capitalize(requestDetails.price_category)"></span></div>
                                        <div class="flex justify-between"><span
                                                class="text-gray-600">Quantity:</span><span class="font-medium"
                                                x-text="requestDetails.quantity"></span></div>
                                        <div class="flex justify-between"><span class="text-gray-600">Unit
                                                Price:</span><span class="font-medium"
                                                x-text="'UGX '+formatCurrency(requestDetails.price)"></span></div>
                                        <div class="flex justify-between border-t pt-2"><span
                                                class="text-gray-900 font-medium">Total Value:</span><span
                                                class="font-bold text-green-600"
                                                x-text="'UGX '+formatCurrency(requestDetails.total_value)"></span></div>
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
                            <p class="text-gray-500">Loading request details...</p>
                        </div>
                    </template>
                    <template x-if="!loadingDetails">
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                                    <i data-lucide="box" class="w-5 h-5 text-blue-600"></i>
                                    <span>Product Information</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Product</p>
                                        <p class="font-medium" x-text="requestDetails.product_title"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Category</p>
                                        <p class="font-medium" x-text="capitalize(requestDetails.price_category)"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Package</p>
                                        <p class="font-medium"
                                            x-text="requestDetails.package_size+' '+requestDetails.si_unit+' '+requestDetails.package_name">
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Quantity</p>
                                        <p class="font-medium" x-text="requestDetails.quantity"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                                    <i data-lucide="calendar-check" class="w-5 h-5 text-green-600"></i>
                                    <span>Visit Details</span>
                                </h4>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Requested Date</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="font-medium" x-text="formatDate(requestDetails.visit_date)">
                                                </p>
                                                <template x-if="requestDetails.status==='confirmed'">
                                                    <button
                                                        @click="openDateModal(requestDetails.id, requestDetails.visit_date)"
                                                        class="text-blue-600 hover:text-blue-800 text-sm p-1 rounded hover:bg-blue-50">
                                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                    </button>
                                                </template>
                                            </div>
                                            <p class="text-sm mt-1" :class="visitInfo(requestDetails).color"
                                                x-text="visitInfo(requestDetails).text"></p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-600">Request Made</p>
                                            <p class="font-medium mt-1" x-text="formatDate(requestDetails.created_at)">
                                            </p>
                                            <p class="text-sm text-gray-500"
                                                x-text="formatTime(requestDetails.created_at)"></p>
                                        </div>
                                    </div>
                                    <template x-if="requestDetails.notes">
                                        <div class="border-t pt-4">
                                            <p class="text-sm font-medium text-gray-600">Customer Notes</p>
                                            <p class="mt-1 text-gray-900 bg-gray-50 p-3 rounded border"
                                                x-text="requestDetails.notes"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                                        <i data-lucide="phone" class="w-5 h-5 text-purple-600"></i>
                                        <span>Communication</span>
                                    </h4>
                                    <template x-if="requestDetails.status!=='confirmed'">
                                        <div
                                            class="p-4 border border-dashed border-gray-300 rounded-lg text-sm text-gray-600">
                                            Confirm this request to view customer communication options.
                                        </div>
                                    </template>
                                    <template x-if="requestDetails.status==='confirmed'">
                                        <div class="grid grid-cols-1 gap-3">
                                            <button @click="callCustomer(requestDetails.phone)"
                                                class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition-colors">
                                                <div
                                                    class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-3">
                                                    <i data-lucide="phone" class="w-5 h-5 text-white"></i></div>
                                                <div class="flex-1 text-left">
                                                    <p class="font-medium text-green-900">Call Customer</p>
                                                    <p class="text-sm text-green-700" x-text="requestDetails.phone"></p>
                                                </div>
                                                <i data-lucide="chevron-right" class="w-4 h-4 text-green-700"></i>
                                            </button>
                                            <button @click="openEmail()"
                                                class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                                                <div
                                                    class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                                                    <i data-lucide="mail" class="w-5 h-5 text-white"></i></div>
                                                <div class="flex-1 text-left">
                                                    <p class="font-medium text-blue-900">Send Email</p>
                                                    <p class="text-sm text-blue-700" x-text="requestDetails.email"></p>
                                                </div>
                                                <i data-lucide="chevron-right" class="w-4 h-4 text-blue-700"></i>
                                            </button>
                                            <button @click="openSms()"
                                                class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors">
                                                <div
                                                    class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-3">
                                                    <i data-lucide="message-square" class="w-5 h-5 text-white"></i>
                                                </div>
                                                <div class="flex-1 text-left">
                                                    <p class="font-medium text-purple-900">Send SMS</p>
                                                    <p class="text-sm text-purple-700">Text message</p>
                                                </div>
                                                <i data-lucide="chevron-right" class="w-4 h-4 text-purple-700"></i>
                                            </button>
                                            <template x-if="requestDetails.alt_contact">
                                                <button @click="callCustomer(requestDetails.alt_contact)"
                                                    class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                                                    <div
                                                        class="w-10 h-10 bg-gray-700 rounded-lg flex items-center justify-center mr-3">
                                                        <i data-lucide="phone" class="w-5 h-5 text-white"></i></div>
                                                    <div class="flex-1 text-left">
                                                        <p class="font-medium text-gray-900">Call Alt. Number</p>
                                                        <p class="text-sm text-gray-700"
                                                            x-text="requestDetails.alt_contact"></p>
                                                    </div>
                                                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-700"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-3">
                                        <i data-lucide="list-checks" class="w-5 h-5 text-orange-600"></i>
                                        <span>Status Management</span>
                                    </h4>
                                    <div class="grid grid-cols-1 gap-3">
                                        <template x-if="requestDetails.status==='pending'">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <button
                                                    @click="confirmAction('Confirm this request? You acknowledge you have the requested quantity available.', () => updateStatus(requestDetails.id,'confirmed'))"
                                                    class="flex items-center justify-center px-3 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>Confirm
                                                </button>
                                                <button
                                                    @click="confirmAction('Cancel this request? Customer will be notified and a new Vendor will be assigned.', () => updateStatus(requestDetails.id,'cancelled'))"
                                                    class="flex items-center justify-center px-3 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                    <i data-lucide="x-octagon" class="w-5 h-5 mr-2"></i>Cancel
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="requestDetails.status==='confirmed'">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <button
                                                    @click="confirmAction('Mark this visit as Completed?', () => updateStatus(requestDetails.id,'completed'))"
                                                    class="flex items-center justify-center px-3 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                                    <i data-lucide="check-check" class="w-5 h-5 mr-2"></i>Completed
                                                </button>
                                                <button
                                                    @click="confirmAction('Cancel this request? Customer will be notified and a new Vendor will be assigned.', () => updateStatus(requestDetails.id,'cancelled'))"
                                                    class="flex items-center justify-center px-3 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                    <i data-lucide="x-octagon" class="w-5 h-5 mr-2"></i>Cancel
                                                </button>
                                            </div>
                                        </template>
                                        <template
                                            x-if="requestDetails.status==='completed' || requestDetails.status==='cancelled'">
                                            <div class="p-4 border border-dashed rounded-lg text-sm text-gray-600">No
                                                further actions available.</div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </section>
            </div>
        </div>
    </div>

    <div x-cloak x-show="modals.date" x-transition.opacity class="fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50" @click="closeDate()"></div>
        <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Select Visit Date</h3>
                <button @click="closeDate()" class="text-gray-500 hover:text-gray-700 p-2"><i data-lucide="x"
                        class="w-5 h-5"></i></button>
            </div>
            <div class="p-4 sm:p-6 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">New Visit Date</label>
                    <input type="date" x-model="dateForm.value" :min="todayStr"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="closeDate()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button @click="saveDate()"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors inline-flex items-center gap-2"><i
                            data-lucide="calendar-check" class="w-4 h-4"></i>Update Date</button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="modals.email" x-transition.opacity class="fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50" @click="closeEmail()"></div>
        <div class="relative w-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Send Email</h3>
                <button @click="closeEmail()" class="text-gray-500 hover:text-gray-700 p-2"><i data-lucide="x"
                        class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="sendEmail()" class="p-4 sm:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                    <input type="email" x-model="emailForm.to" readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" x-model="emailForm.subject"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea rows="6" x-model="emailForm.message"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeEmail()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors inline-flex items-center gap-2"><i
                            data-lucide="send" class="w-4 h-4"></i>Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="modals.sms" x-transition.opacity class="fixed inset-0 z-[60]">
        <div class="absolute inset-0 bg-black/50" @click="closeSms()"></div>
        <div class="relative w-full max-w-lg mx-auto top-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Send SMS</h3>
                <button @click="closeSms()" class="text-gray-500 hover:text-gray-700 p-2"><i data-lucide="x"
                        class="w-5 h-5"></i></button>
            </div>
            <form @submit.prevent="sendSms()" class="p-4 sm:p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-900">SMS Credits</p>
                        <p class="text-xs text-blue-700">Available balance</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-blue-900" x-text="sms.balance"></p>
                        <p class="text-xs text-blue-700">credits</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To</label>
                    <input type="tel" x-model="sms.to" readonly
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea rows="4" maxlength="160" x-model="sms.message"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"></textarea>
                    <div class="text-right text-sm text-gray-500 mt-1"><span x-text="sms.message.length"></span>/160
                        characters</div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeSms()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" :disabled="sms.balance<1 || sms.sending"
                        class="px-4 py-2 rounded-lg text-white transition-colors inline-flex items-center gap-2"
                        :class="sms.balance<1 ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-primary/90'">
                        <i :data-lucide="sms.sending ? 'loader-2' : 'message-square'"
                            :class="sms.sending ? 'animate-spin' : ''" class="w-4 h-4"></i>
                        <span
                            x-text="sms.balance<1 ? 'Insufficient Credits' : (sms.sending ? 'Sending...' : 'Send SMS')"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="modals.confirm" x-transition.opacity class="fixed inset-0 z-[70]">
        <div class="absolute inset-0 bg-black/60" @click="closeConfirm()"></div>
        <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-xl shadow-lg">
            <div class="p-5">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center"><i
                            data-lucide="alert-triangle" class="w-5 h-5 text-amber-700"></i></div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">Please Confirm</h3>
                        <p class="text-sm text-gray-700 mt-1" x-text="confirmDialog.message"></p>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeConfirm()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">No</button>
                    <button @click="runConfirm()"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div x-cloak x-show="toast.show" x-transition.opacity
        class="fixed inset-x-0 bottom-4 z-[80] flex justify-center px-4">
        <div class="w-full max-w-md rounded-lg shadow-lg border"
            :class="toast.type==='success'?'bg-green-50 border-green-200':'bg-red-50 border-red-200'">
            <div class="p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center"
                    :class="toast.type==='success'?'bg-green-100':'bg-red-100'">
                    <i :data-lucide="toast.type==='success' ? 'check' : 'x-circle'"
                        :class="toast.type==='success' ? 'text-green-700' : 'text-red-700'" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <p class="font-medium" :class="toast.type==='success'?'text-green-900':'text-red-900'"
                        x-text="toast.title"></p>
                    <p class="text-sm mt-0.5" :class="toast.type==='success'?'text-green-800':'text-red-800'"
                        x-text="toast.message"></p>
                </div>
                <button @click="toast.show=false" class="p-2 rounded hover:bg-white/60"><i data-lucide="x"
                        class="w-4 h-4"></i></button>
            </div>
        </div>
    </div>

    <script>
        function storeVisitsPage() {
            return {
                requests: [],
                total: 0,
                page: 1,
                perPage: 20,
                totalPages: 0,
                loading: true,
                refreshing: false,
                stats: { pending: 0, confirmed: 0, completed: 0, cancelled: 0 },
                filters: { period: 'monthly', start: '', end: '', search: '', status: 'all' },
                modals: { details: false, date: false, email: false, sms: false, confirm: false },
                requestDetails: {},
                loadingDetails: false,
                detailHeader: { title: '', subtitle: '' },
                statusPill: { bg: 'bg-yellow-100 border-yellow-200', dot: 'bg-yellow-500', text: 'text-yellow-700' },
                dateForm: { id: null, value: '' },
                todayStr: '',
                emailForm: { to: '', subject: '', message: '' },
                sms: { to: '', message: '', balance: 0, sending: false },
                toast: { show: false, type: 'success', title: '', message: '' },
                confirmDialog: { message: '', fn: null },
                get showingFrom() { return this.total === 0 ? 0 : ((this.page - 1) * this.perPage) + 1 },
                get showingTo() { return Math.min(this.page * this.perPage, this.total) },
                dateBtnClass(p) { return (this.filters.period === p) ? 'px-4 py-2 rounded-lg border text-sm bg-primary text-white border-primary' : 'px-4 py-2 rounded-lg border text-sm text-gray-700 hover:bg-gray-50' },
                capitalize(s) { return (s || '').charAt(0).toUpperCase() + (s || '').slice(1) },
                formatCurrency(n) { return (new Intl.NumberFormat('en-UG', { minimumFractionDigits: 2, maximumFractionDigits: 2 })).format(n || 0) },
                formatDate(d) { const date = new Date(d); const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const day = date.getDate(); const s = (day == 1 || day == 21 || day == 31) ? 'st' : (day == 2 || day == 22) ? 'nd' : (day == 3 || day == 23) ? 'rd' : 'th'; return `${months[date.getMonth()]} ${day}${s}, ${date.getFullYear()}` },
                formatTime(d) { const date = new Date(d); let h = date.getHours(); const m = ('' + date.getMinutes()).padStart(2, '0'); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; return `${h}:${m}${ap}` },
                visitInfo(r) { const visit = new Date(r.visit_date); const today = new Date(); today.setHours(0, 0, 0, 0); visit.setHours(0, 0, 0, 0); const diff = Math.ceil((visit.getTime() - today.getTime()) / (1000 * 60 * 60 * 24)); if ((r.status || '').toLowerCase() === 'completed') return { text: 'Completed', color: 'text-green-600' }; if (diff > 0) return { text: `In ${diff} day${diff > 1 ? 's' : ''}`, color: 'text-blue-600' }; if (diff === 0) return { text: 'Today', color: 'text-orange-600' }; return { text: `${Math.abs(diff)} day${Math.abs(diff) > 1 ? 's' : ''} overdue`, color: 'text-red-600' } },
                statusBadge(s) { s = (s || '').toLowerCase(); if (s === 'pending') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>'; if (s === 'confirmed') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Confirmed</span>'; if (s === 'completed') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>'; if (s === 'cancelled') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>'; return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>' },
                setPeriod(p) { this.filters.period = p; this.setRangeFor(p); this.page = 1; this.loadRequests(); this.loadStats() },
                setRangeFor(p) { const t = new Date(); let s, e; if (p === 'daily') { s = new Date(t); e = new Date(t); } else if (p === 'weekly') { const d = t.getDay(); s = new Date(t); s.setDate(t.getDate() - d); e = new Date(s); e.setDate(s.getDate() + 6); } else { s = new Date(t.getFullYear(), t.getMonth(), 1); e = new Date(t); } this.filters.start = s.toISOString().split('T')[0]; this.filters.end = e.toISOString().split('T')[0] },
                applyCustomRange() { this.filters.period = 'custom'; this.page = 1; this.loadRequests() },
                clearFilters() { this.filters.search = ''; this.filters.status = 'all'; this.page = 1; this.loadRequests() },
                prev() { if (this.page > 1) { this.page--; this.loadRequests() } },
                next() { if (this.page < this.totalPages) { this.page++; this.loadRequests() } },
                async loadStats() { try { const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getStats`); const j = await r.json(); if (j.success) { this.stats = { pending: parseInt(j.stats.pending || 0), confirmed: parseInt(j.stats.confirmed || 0), completed: parseInt(j.stats.completed || 0), cancelled: parseInt(j.stats.cancelled || 0) }; this.nextTickIcons() } } catch (e) { } },
                async loadRequests() { this.loading = true; try { const params = new URLSearchParams({ action: 'getRequests', start_date: this.filters.start, end_date: this.filters.end, search_term: this.filters.search, status_filter: this.filters.status, page: this.page }); const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?` + params.toString()); const j = await r.json(); if (j.success) { this.requests = j.requestData.data || []; this.total = parseInt(j.requestData.total || 0); this.page = parseInt(j.requestData.page || 1); this.totalPages = Math.ceil(this.total / this.perPage); } else { this.toastError('Failed', 'Failed to load requests') } } catch (e) { this.toastError('Error', 'An error occurred while loading requests') } finally { this.loading = false; this.nextTickIcons() } },
                async openDetails(id) { this.modals.details = true; this.loadingDetails = true; this.requestDetails = {}; this.detailHeader = { title: 'Store Visit Request', subtitle: 'Loading...' }; document.documentElement.classList.add('overflow-hidden'); try { const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getRequestDetails&id=${id}`); const j = await r.json(); if (j.success) { this.requestDetails = j.request; this.detailHeader = { title: this.requestDetails.first_name + ' ' + this.requestDetails.last_name, subtitle: 'Visit Request - ' + this.requestDetails.product_title }; this.computeStatusPill(); } else { this.toastError('Failed', 'Failed to load request details') } } catch (e) { this.toastError('Error', 'An error occurred while loading details') } finally { this.loadingDetails = false; this.nextTickIcons() } },
                closeDetails() { this.modals.details = false; this.requestDetails = {}; document.documentElement.classList.remove('overflow-hidden') },
                computeStatusPill() { const s = (this.requestDetails.status || '').toLowerCase(); if (s === 'pending') this.statusPill = { bg: 'bg-yellow-100 border-yellow-200', dot: 'bg-yellow-500', text: 'text-yellow-700' }; else if (s === 'confirmed') this.statusPill = { bg: 'bg-blue-100 border-blue-200', dot: 'bg-blue-500', text: 'text-blue-700' }; else if (s === 'completed') this.statusPill = { bg: 'bg-green-100 border-green-200', dot: 'bg-green-500', text: 'text-green-700' }; else this.statusPill = { bg: 'bg-red-100 border-red-200', dot: 'bg-red-500', text: 'text-red-700' } },
                openDateModal(id, current) { if ((this.requestDetails.status || '') !== 'confirmed') return; this.dateForm.id = id; this.dateForm.value = current; this.modals.date = true; this.nextTickIcons() },
                closeDate() { this.modals.date = false },
                async saveDate() { if (!this.dateForm.value) { this.toastError('Error', 'Please select a date'); return } if (this.dateForm.value < this.todayStr) { this.toastError('Error', 'Visit date cannot be in the past'); return } try { const fd = new FormData(); fd.append('request_id', this.dateForm.id); fd.append('visit_date', this.dateForm.value); const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=updateVisitDate`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.modals.date = false; this.toastSuccess('Success', 'Visit date updated successfully'); await this.openDetails(this.dateForm.id); this.loadRequests(); } else { this.toastError('Failed', j.message || 'Failed to update visit date') } } catch (e) { this.toastError('Error', 'An error occurred while updating visit date') } },
                confirmAction(message, fn) { this.confirmDialog.message = message; this.confirmDialog.fn = fn; this.modals.confirm = true; this.nextTickIcons() },
                runConfirm() { if (typeof this.confirmDialog.fn === 'function') { this.modals.confirm = false; this.confirmDialog.fn() } },
                closeConfirm() { this.modals.confirm = false; this.confirmDialog = { message: '', fn: null } },
                async updateStatus(id, status) { try { const fd = new FormData(); fd.append('request_id', id); fd.append('status', status); const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=updateRequestStatus`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.toastSuccess('Success', 'Status updated successfully'); await this.openDetails(id); this.loadRequests(); this.loadStats() } else { this.toastError('Failed', j.message || 'Failed to update status') } } catch (e) { this.toastError('Error', 'An error occurred while updating status') } },
                callCustomer(phone) { window.location.href = `tel:${phone}` },
                async openSms() { if ((this.requestDetails.status || '') !== 'confirmed') return; await this.fetchSmsBalance(); this.sms.to = this.requestDetails.phone; this.sms.message = `Hello ${this.requestDetails.first_name}, regarding your store visit request for ${this.requestDetails.product_title}.`; this.modals.sms = true; this.nextTickIcons() },
                closeSms() { this.modals.sms = false; this.sms.message = ''; },
                async fetchSmsBalance() { try { const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=getSmsBalance`); const j = await r.json(); if (j.success) { this.sms.balance = j.balance } } catch (e) { } },
                async sendSms() { if (this.sms.balance < 1) { this.toastError('Error', 'Insufficient SMS credits'); return } if (!this.sms.message.trim()) { this.toastError('Error', 'Please enter an SMS message'); return } this.sms.sending = true; try { const fd = new FormData(); fd.append('action', 'sendSms'); fd.append('message', this.sms.message.trim()); fd.append('recipients', JSON.stringify([this.sms.to])); fd.append('send_type', 'single'); fd.append('send_option', 'now'); const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageSmsCenter.php`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.sms.balance = j.data?.new_balance ?? (this.sms.balance - 1); this.toastSuccess('Success', 'SMS sent successfully'); this.closeSms() } else { this.toastError('Failed', j.message || 'Failed to send SMS') } } catch (e) { this.toastError('Error', 'An error occurred while sending SMS') } finally { this.sms.sending = false; this.nextTickIcons() } },
                openEmail() { if ((this.requestDetails.status || '') !== 'confirmed') return; this.emailForm.to = this.requestDetails.email; this.emailForm.subject = `Store Visit Request - ${this.requestDetails.product_title}`; this.emailForm.message = `Dear ${this.requestDetails.first_name} ${this.requestDetails.last_name},\n\nThank you for your store visit request regarding ${this.requestDetails.product_title}.\n\nWe look forward to meeting with you.\n\nBest regards,\nStore Team`; this.modals.email = true; this.nextTickIcons() },
                closeEmail() { this.modals.email = false; this.emailForm = { to: '', subject: '', message: '' } },
                async sendEmail() { try { const fd = new FormData(); fd.append('request_id', this.requestDetails.id); fd.append('subject', this.emailForm.subject); fd.append('message', this.emailForm.message); const r = await fetch(`${window.BASE_URL}vendor-store/fetch/manageBuyInStore.php?action=sendEmail`, { method: 'POST', body: fd }); const j = await r.json(); if (j.success) { this.toastSuccess('Success', 'Email sent successfully'); this.closeEmail() } else { this.toastError('Failed', j.message || 'Failed to send email') } } catch (e) { this.toastError('Error', 'An error occurred while sending email') } },
                refresh() { this.refreshing = true; Promise.all([this.loadRequests(), this.loadStats()]).finally(() => { setTimeout(() => { this.refreshing = false }, 300) }) },
                toastSuccess(t, m) { this.toast = { show: true, type: 'success', title: t, message: m }; this.nextTickIcons(); setTimeout(() => { this.toast.show = false }, 2500) },
                toastError(t, m) { this.toast = { show: true, type: 'error', title: t, message: m }; this.nextTickIcons(); setTimeout(() => { this.toast.show = false }, 3000) },
                closeAnyModal() { if (this.modals.details) this.closeDetails(); if (this.modals.date) this.closeDate(); if (this.modals.email) this.closeEmail(); if (this.modals.sms) this.closeSms(); if (this.modals.confirm) this.closeConfirm() },
                nextTickIcons() { queueMicrotask(() => { if (window.lucide && typeof window.lucide.createIcons === 'function') { window.lucide.createIcons() } }) },
                init() { const t = new Date(); this.todayStr = t.toISOString().split('T')[0]; this.setRangeFor(this.filters.period); this.loadRequests(); this.loadStats(); setInterval(() => { this.loadStats(); if (!this.modals.details) { this.loadRequests() } }, 30000); this.nextTickIcons() }
            }
        }
    </script>
</div>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>