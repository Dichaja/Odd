<?php
$pageTitle = 'My Quotations';
$activeNav = 'quotations';
require_once __DIR__ . '/../config/config.php';
ob_start();
?>
<div x-data="quotations()" x-init="init()" x-cloak class="min-h-screen bg-user-content dark:bg-secondary/10">
    <style>
        [x-cloak] {
            display: none
        }
    </style>
    <div
        class="bg-white dark:bg-secondary border-b border-gray-200 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-5 hidden sm:block">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-user-primary/10 rounded-xl grid place-items-center">
                        <i data-lucide="file-text" class="w-6 h-6 text-user-primary"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-secondary dark:text-white font-rubik">My
                            Quotations</h1>
                        <p class="text-sm text-gray-text dark:text-white/70">View and manage your submitted quotation
                            requests</p>
                    </div>
                </div>
                <button @click="refresh()"
                    class="hidden sm:inline-flex px-5 py-2.5 bg-user-primary text-white rounded-xl hover:bg-user-primary/90 items-center gap-2 font-medium shadow-lg shadow-user-primary/25">
                    <i data-lucide="refresh-cw" class="w-4 h-4"
                        :class="{'animate-spin': list.loading}"></i><span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-2 sm:px-2 lg:px-2 py-4 space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div
                class="rounded-xl p-4 sm:p-5 border border-blue-200 dark:border-blue-500/20 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-500/10 dark:to-blue-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wide">
                            New</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-900 dark:text-white truncate"
                            x-text="stats.new.toLocaleString()"></p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-200 dark:bg-blue-500/20 rounded-lg grid place-items-center">
                        <i data-lucide="file-plus-2" class="w-5 h-5 text-blue-600 dark:text-blue-300"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-yellow-200 dark:border-yellow-500/20 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-500/10 dark:to-yellow-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-[11px] font-semibold text-yellow-600 dark:text-yellow-300 uppercase tracking-wide">
                            Processing</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-900 dark:text-white truncate"
                            x-text="stats.processing.toLocaleString()"></p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-200 dark:bg-yellow-500/20 rounded-lg grid place-items-center">
                        <i data-lucide="clock" class="w-5 h-5 text-yellow-600 dark:text-yellow-300"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-green-200 dark:border-green-500/20 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-500/10 dark:to-green-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-green-600 dark:text-green-300 uppercase tracking-wide">
                            Processed</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-900 dark:text-white truncate"
                            x-text="stats.processed.toLocaleString()"></p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-green-200 dark:bg-green-500/20 rounded-lg grid place-items-center">
                        <i data-lucide="check" class="w-5 h-5 text-green-600 dark:text-green-300"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-red-200 dark:border-red-500/20 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-500/10 dark:to-red-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-red-600 dark:text-red-300 uppercase tracking-wide">
                            Cancelled</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-900 dark:text-white truncate"
                            x-text="stats.cancelled.toLocaleString()"></p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-red-200 dark:bg-red-500/20 rounded-lg grid place-items-center">
                        <i data-lucide="x" class="w-5 h-5 text-red-600 dark:text-red-300"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-purple-200 dark:border-purple-500/20 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-500/10 dark:to-purple-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-[11px] font-semibold text-purple-600 dark:text-purple-300 uppercase tracking-wide">
                            Paid</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-900 dark:text-white truncate"
                            x-text="stats.paid.toLocaleString()"></p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-200 dark:bg-purple-500/20 rounded-lg grid place-items-center">
                        <i data-lucide="credit-card" class="w-5 h-5 text-purple-600 dark:text-purple-300"></i>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-white/10 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">My Quotation Requests</h3>
                    <p class="hidden sm:block text-sm text-gray-text dark:text-white/70">Tap a row to view and manage
                        quotation details</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3">
                    <div class="relative">
                        <input type="text" x-model.debounce.400ms="filters.search" @input="onFilterChange()"
                            placeholder="Search requests..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-sm bg-white dark:bg-white/5 text-secondary dark:text-white border border-gray-200 dark:border-white/10 focus:outline-none focus:ring-4 focus:ring-user-primary/15">
                        <i data-lucide="search"
                            class="w-4 h-4 absolute left-3 top-3 text-gray-400 dark:text-white/50"></i>
                    </div>
                    <div>
                        <select x-model="filters.status" @change="onFilterChange()"
                            class="w-full px-3 py-2.5 rounded-2xl text-sm bg-white dark:bg-white/5 text-secondary dark:text-white border border-gray-200 dark:border-white/10">
                            <option value="all">All Status</option>
                            <option value="New">New</option>
                            <option value="Processing">Processing</option>
                            <option value="Processed">Processed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Paid">Paid</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button @click="clearFilters()"
                            class="hidden sm:inline-flex px-4 py-2.5 text-sm rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Clear
                            Filters</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="w-full min-w-full" id="quotationsTable">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Location</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Items</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Fee Charged</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Delivery Charge</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Total Amount</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Status</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        <tr x-show="list.loading">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                                <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
                                <div>Loading quotations...</div>
                            </td>
                        </tr>
                        <template x-for="item in list.data" :key="item.RFQ_ID">
                            <tr class="hover:bg-user-accent/30 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                @click="openQuotation(item.RFQ_ID)">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-secondary dark:text-white text-sm location-max"
                                        :title="item.site_location" x-text="item.site_location || ''"></div>
                                    <div class="text-xs text-gray-text dark:text-white/70 location-max"
                                        :title="item.coordinates || ''" x-text="item.coordinates || ''"></div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300"
                                        x-text="item.items_count"></span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><span
                                        class="text-sm font-medium text-secondary dark:text-white"
                                        x-text="`UGX ${money(item.fee_charged)}`"></span></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><span
                                        class="text-sm font-medium text-secondary dark:text-white"
                                        x-text="`UGX ${money(item.transport)}`"></span></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap"><span
                                        class="text-sm font-bold text-green-600 dark:text-green-300"
                                        x-text="`UGX ${money(totalAmount(item))}`"></span></td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="statusPill(item.status).bg">
                                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full"
                                            :class="statusPill(item.status).dot"></span>
                                        <span x-text="statusPill(item.status).label"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="text-xs text-secondary dark:text-white"
                                        x-text="dateFmt(item.created_at)"></div>
                                    <div class="text-xs text-gray-text dark:text-white/70"
                                        x-text="timeFmt(item.created_at)"></div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!list.loading && list.data.length===0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                                <i data-lucide="inbox" class="w-6 h-6 mx-auto mb-2"></i>
                                <div>No quotations found</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="quotationsListMobile" class="md:hidden divide-y divide-gray-100 dark:divide-white/10">
                <div class="px-4 py-8 text-center text-gray-500 dark:text-white/70" x-show="list.loading">
                    <i data-lucide="loader-2" class="w-7 h-7 mx-auto mb-2 animate-spin"></i>
                    <div>Loading quotations...</div>
                </div>
                <template x-for="item in list.data" :key="`m-${item.RFQ_ID}`">
                    <button class="w-full text-left px-4 py-3 active:scale-[.99] transition-transform"
                        @click="openQuotation(item.RFQ_ID)">
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500 dark:text-white/60 mb-1"
                                        x-text="`${dateFmt(item.created_at)} • ${timeFmt(item.created_at)}`"></div>
                                    <div class="font-semibold text-secondary dark:text-white location-max"
                                        :title="item.site_location" x-text="item.site_location || ''"></div>
                                    <div class="text-xs text-gray-500 dark:text-white/60 location-max"
                                        :title="item.coordinates || ''" x-text="item.coordinates || ''"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] text-gray-500 dark:text-white/60">Total</div>
                                    <div class="text-base font-bold text-green-600 dark:text-green-300"
                                        x-text="`UGX ${money(totalAmount(item))}`"></div>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span
                                        class="opacity-70">Items:</span> <span class="font-medium"
                                        x-text="item.items_count"></span></div>
                                <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span
                                        class="opacity-70">Fee:</span> <span class="font-medium"
                                        x-text="`UGX ${money(item.fee_charged)}`"></span></div>
                                <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span
                                        class="opacity-70">Delivery:</span> <span class="font-medium"
                                        x-text="`UGX ${money(item.transport)}`"></span></div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                    :class="statusPill(item.status).bg" x-text="statusPill(item.status).label"></span>
                                <span class="text-user-primary text-sm font-medium inline-flex items-center">View <i
                                        data-lucide="chevron-right" class="w-4 h-4 ml-1"></i></span>
                            </div>
                        </div>
                    </button>
                </template>
                <div class="px-4 py-8 text-center text-gray-500 dark:text-white/70"
                    x-show="!list.loading && list.data.length===0">
                    <i data-lucide="inbox" class="w-7 h-7 mx-auto mb-2"></i>
                    <div>No quotations found</div>
                </div>
            </div>

            <div
                class="p-4 border-t border-gray-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-text dark:text-white/70 text-center sm:text-left">
                    Showing <span x-text="pagination.showing"></span> of <span x-text="pagination.total"></span>
                    requests
                </div>
                <div class="flex items-center gap-2">
                    <button @click="prevPage()" :disabled="pagination.page<=1"
                        class="px-3 py-1 text-sm rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 disabled:opacity-50">Previous</button>
                    <span class="px-3 py-1 text-sm text-gray-text dark:text-white/70"
                        x-text="`Page ${pagination.page} of ${Math.max(1, pagination.pages)}`"></span>
                    <button @click="nextPage()" :disabled="pagination.page>=pagination.pages"
                        class="px-3 py-1 text-sm rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 disabled:opacity-50">Next</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.quotation" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeQuotation()"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] relative z-10 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-secondary dark:text-white">Quotation Details</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">View and update your quotation request</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="generatePDF()"
                        class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10"
                        title="Print PDF">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </button>
                    <button @click="closeQuotation()"
                        class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
                <div class="p-5 sm:p-6" x-show="quotation.loading">
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-user-primary animate-spin"></i>
                            <p class="text-gray-text dark:text-white/70">Fetching quotation details...</p>
                        </div>
                    </div>
                </div>

                <template x-if="!quotation.loading && quotation.data">
                    <div class="p-5 sm:p-6">
                        <div class="space-y-6">
                            <div
                                class="bg-user-accent/50 dark:bg-white/5 rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-white/10">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <h4 class="font-medium text-secondary dark:text-white mb-2">Request Information
                                        </h4>
                                        <div class="space-y-1 text-sm">
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-text dark:text-white/70">Status:</span>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                    :class="statusPill(q().status).bg">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full"
                                                        :class="statusPill(q().status).dot"></span>
                                                    <span x-text="statusPill(q().status).label"></span>
                                                </span>
                                            </div>
                                            <div><span class="text-gray-text dark:text-white/70">Fee Charged:</span>
                                                <span class="font-medium text-secondary dark:text-white"
                                                    x-text="`UGX ${money(q().fee_charged)}`"></span></div>
                                            <div class="text-xs text-orange-600 dark:text-orange-300 font-medium"
                                                x-show="parseInt(q().modified)===1">This quotation has been modified
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-secondary dark:text-white mb-2">Location Details
                                        </h4>
                                        <div class="space-y-1 text-sm">
                                            <div><span class="text-gray-text dark:text-white/70">Site Location:</span>
                                            </div>
                                            <div class="font-medium text-secondary dark:text-white"
                                                x-text="q().site_location"></div>
                                            <template x-if="q().coordinates">
                                                <div class="text-gray-500 dark:text-white/60">
                                                    <a :href="mapsLink(q().coordinates)" target="_blank"
                                                        class="text-user-primary hover:text-user-primary/80 underline"
                                                        x-text="q().coordinates"></a>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-secondary dark:text-white mb-2">Dates</h4>
                                        <div class="space-y-1 text-sm">
                                            <div><span class="text-gray-text dark:text-white/70">Created:</span> <span
                                                    class="font-medium text-secondary dark:text-white"
                                                    x-text="`${dateFmt(q().created_at)} ${timeFmt(q().created_at)}`"></span>
                                            </div>
                                            <div><span class="text-gray-text dark:text-white/70">Updated:</span> <span
                                                    class="font-medium text-secondary dark:text-white"
                                                    x-text="`${dateFmt(q().updated_at)} ${timeFmt(q().updated_at)}`"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                                    <h4 class="font-medium text-secondary dark:text-white">Requested Items</h4>
                                    <template x-if="canEdit() && !edit.mode">
                                        <button @click="modals.editConfirm=true"
                                            class="px-3 py-2 bg-user-primary text-white text-sm rounded-xl hover:bg-user-primary/90 flex items-center gap-2 justify-center">
                                            <i data-lucide="pencil" class="w-4 h-4"></i><span>Edit Items</span>
                                        </button>
                                    </template>
                                    <template x-if="edit.mode">
                                        <div class="flex gap-2">
                                            <button @click="openSaveConfirm()"
                                                class="px-3 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 flex items-center gap-2 justify-center">
                                                <i data-lucide="save" class="w-4 h-4"></i><span>Save Changes</span>
                                            </button>
                                            <button @click="exitEdit()"
                                                class="px-3 py-2 bg-gray-600 text-white text-sm rounded-xl hover:bg-gray-700 flex items-center gap-2 justify-center">
                                                <i data-lucide="x" class="w-4 h-4"></i><span>Cancel</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-xl">
                                    <table class="w-full">
                                        <thead class="bg-gray-50 dark:bg-white/5">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-white/70">
                                                    Brand/Material</th>
                                                <th
                                                    class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-white/70">
                                                    Size/Specification</th>
                                                <th
                                                    class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">
                                                    Quantity</th>
                                                <th
                                                    class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">
                                                    Unit Price (UGX)</th>
                                                <th
                                                    class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">
                                                    Total (UGX)</th>
                                                <th class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70"
                                                    x-show="edit.mode">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                            <template x-for="it in visibleItems()" :key="it.RFQD_ID">
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-secondary dark:text-white"
                                                        x-text="it.brand_name"></td>
                                                    <td class="px-4 py-3 text-sm text-secondary dark:text-white"
                                                        x-text="it.size"></td>
                                                    <td class="px-4 py-3 text-center">
                                                        <template x-if="edit.mode">
                                                            <input type="number" min="1"
                                                                class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-secondary dark:text-white rounded-xl text-center focus:outline-none focus:ring-2 focus:ring-user-primary/30"
                                                                :value="qtyFor(it.RFQD_ID, it.quantity)"
                                                                @change="changeQty(it.RFQD_ID, $event.target.value)">
                                                        </template>
                                                        <template x-if="!edit.mode">
                                                            <span
                                                                class="text-sm font-medium text-secondary dark:text-white"
                                                                x-text="it.quantity"></span>
                                                        </template>
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        <span class="text-sm font-medium text-secondary dark:text-white"
                                                            x-html="it.unit_price>0 ? money(it.unit_price) : '<span class=\'text-gray-400 dark:text-white/50 italic\'>Not set yet</span>'"></span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-center font-medium text-secondary dark:text-white"
                                                        x-text="money((parseFloat(it.unit_price||0))*qtyFor(it.RFQD_ID, it.quantity))">
                                                    </td>
                                                    <td class="px-4 py-3 text-center" x-show="edit.mode">
                                                        <template x-if="visibleItems().length>1">
                                                            <button @click="removeItem(it.RFQD_ID)"
                                                                class="text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-400 p-1 rounded"
                                                                title="Remove item">
                                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </template>
                                                        <template x-if="visibleItems().length<=1">
                                                            <span class="text-gray-400 dark:text-white/50 text-xs">Last
                                                                item</span>
                                                        </template>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div
                                class="bg-user-accent/50 dark:bg-white/5 rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-white/10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="font-medium text-secondary dark:text-white mb-3">Delivery Charge</h4>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm text-gray-text dark:text-white/70">Delivery
                                                Charge:</span>
                                            <span class="text-sm font-medium text-secondary dark:text-white"
                                                x-html="transport()>0 ? 'UGX '+money(transport()) : '<span class=\'text-gray-400 dark:text-white/50 italic\'>Not set yet</span>'"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-secondary dark:text-white mb-3">Summary</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-text dark:text-white/70">Items Subtotal:</span>
                                                <span class="font-medium text-secondary dark:text-white"
                                                    x-html="itemsSubtotal()>0 ? 'UGX '+money(itemsSubtotal()) : '<span class=\'text-gray-400 dark:text-white/50 italic\'>Not set yet</span>'"></span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-text dark:text-white/70">Delivery Charge:</span>
                                                <span class="font-medium text-secondary dark:text-white"
                                                    x-html="transport()>0 ? 'UGX '+money(transport()) : '<span class=\'text-gray-400 dark:text-white/50 italic\'>Not set yet</span>'"></span>
                                            </div>
                                            <div
                                                class="flex justify-between border-t dark:border-white/10 pt-2 text-lg font-bold">
                                                <span class="text-secondary dark:text-white">Total Amount:</span>
                                                <span class="text-green-600 dark:text-green-300"
                                                    x-html="grandTotal()>0 ? 'UGX '+money(grandTotal()) : '<span class=\'text-gray-400 dark:text-white/50 italic\'>Pending pricing</span>'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                                <div class="flex gap-3">
                                    <template x-if="canCancel()">
                                        <button @click="modals.cancel=true"
                                            class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 flex items-center gap-2">
                                            <i data-lucide="x" class="w-4 h-4"></i><span>Cancel Quote</span>
                                        </button>
                                    </template>
                                    <template x-if="canPay() && grandTotal()>0">
                                        <button @click="openPayment()"
                                            class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 flex items-center gap-2">
                                            <i data-lucide="credit-card" class="w-4 h-4"></i><span>Make Payment</span>
                                        </button>
                                    </template>
                                </div>
                                <button @click="closeQuotation()"
                                    class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Close</button>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="p-5 sm:p-6" x-show="!quotation.loading && !quotation.data">
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center text-red-500 dark:text-red-300">
                            <i data-lucide="alert-triangle" class="w-8 h-8 mx-auto mb-4"></i>
                            <p>Failed to load quotation details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.editConfirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.editConfirm=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-500/10 rounded-full grid place-items-center">
                        <i data-lucide="pencil" class="w-5 h-5 text-yellow-600 dark:text-yellow-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Quotation</h3>
                        <p class="text-sm text-gray-text dark:text-white/70">Are you sure you want to edit this
                            quotation?</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="modals.editConfirm=false"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                    <button @click="enterEdit()"
                        class="px-4 py-2 bg-user-primary text-white rounded-xl hover:bg-user-primary/90">Yes,
                        Edit</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.saveConfirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.saveConfirm=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/10 rounded-full grid place-items-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-orange-600 dark:text-orange-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Save Changes - Important Notice
                        </h3>
                        <p class="text-sm text-gray-text dark:text-white/70 mt-2">You can only edit this quotation once.
                            After saving, you won't be able to edit it again.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="modals.saveConfirm=false"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                    <button @click="confirmSave()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700">Yes, Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.payment" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.payment=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-full grid place-items-center">
                        <i data-lucide="credit-card" class="w-5 h-5 text-blue-600 dark:text-blue-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Confirm Quote Payment</h3>
                        <p class="text-sm text-gray-text dark:text-white/70">Review payment details before proceeding
                        </p>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    <div
                        class="rounded-xl p-4 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 space-y-2">
                        <div class="flex justify-between items-center"><span
                                class="text-gray-600 dark:text-white/70">Quote Payment Amount:</span><span
                                class="font-medium text-secondary dark:text-white"
                                x-text="`UGX ${money(grandTotal())}`"></span></div>
                        <div class="flex justify-between items-center"><span
                                class="text-gray-600 dark:text-white/70">Current Wallet Balance:</span><span
                                class="font-medium"
                                :class="walletBalance<grandTotal() ? 'text-red-600 dark:text-red-300' : 'text-green-600 dark:text-green-300'"
                                x-text="`UGX ${money(walletBalance)}`"></span></div>
                        <template x-if="walletBalance<grandTotal()">
                            <div>
                                <div class="flex justify-between items-center border-t dark:border-white/10 pt-2"><span
                                        class="text-red-600 dark:text-red-300 font-medium">Amount Needed:</span><span
                                        class="font-medium text-red-600 dark:text-red-300"
                                        x-text="`UGX ${money(grandTotal()-walletBalance)}`"></span></div>
                                <div
                                    class="text-center text-red-600 dark:text-red-300 text-sm mt-3 inline-flex items-center gap-2">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i> Please top up your wallet to
                                    continue</div>
                            </div>
                        </template>
                        <template x-if="walletBalance>=grandTotal()">
                            <div class="flex justify-between items-center border-t dark:border-white/10 pt-2"><span
                                    class="text-gray-600 dark:text-white/70">Remaining Balance:</span><span
                                    class="font-medium text-green-600 dark:text-green-300"
                                    x-text="`UGX ${money(walletBalance-grandTotal())}`"></span></div>
                        </template>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="modals.payment=false"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                    <button x-show="walletBalance<grandTotal()" @click="topUp()"
                        class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700 inline-flex items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4"></i> Top Up Wallet
                    </button>
                    <button x-show="walletBalance>=grandTotal()" @click="confirmPayment()"
                        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 inline-flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.cancel" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.cancel=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-full grid place-items-center">
                        <i data-lucide="x" class="w-5 h-5 text-red-600 dark:text-red-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Cancel Quotation</h3>
                        <p class="text-sm text-gray-text dark:text-white/70">Are you sure you want to cancel this
                            quotation? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="modals.cancel=false"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">No,
                        Keep It</button>
                    <button @click="confirmCancel()"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.success" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.success=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-full grid place-items-center">
                        <i data-lucide="check" class="w-5 h-5 text-green-600 dark:text-green-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Success</h3>
                        <p class="text-sm text-gray-text dark:text-white/70"
                            x-text="messages.success || 'Operation completed successfully.'"></p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button @click="modals.success=false"
                        class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.error" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.error=false"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-full grid place-items-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 dark:text-red-300"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white">Error</h3>
                        <p class="text-sm text-gray-text dark:text-white/70"
                            x-text="messages.error || 'An error occurred.'"></p>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button @click="modals.error=false"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">OK</button>
                </div>
            </div>
        </div>
    </div>

    <div id="pdfContent"
        style="position: absolute; left: -9999px; top: -9999px; width: 297mm; background: white; font-family: Arial, sans-serif; color: #333; padding: 10mm;">
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
    #quotationsTable tbody tr:hover {
        background-color: rgba(230, 242, 255, .3)
    }

    .dark #quotationsTable tbody tr:hover {
        background-color: rgba(255, 255, 255, .06)
    }

    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0
    }

    #quotationsTable {
        min-width: 800px
    }

    .location-max {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    @media (max-width:768px) {
        #quotationsTable {
            font-size: .875rem
        }

        #quotationsTable th,
        #quotationsTable td {
            padding: .5rem .75rem
        }

        .location-max {
            max-width: 170px
        }
    }
</style>

<script>
    function quotations() {
        return {
            list: { data: [], loading: false },
            pagination: { page: 1, limit: 20, total: 0, pages: 1, showing: '0' },
            filters: { search: '', status: 'all' },
            stats: { new: 0, processing: 0, processed: 0, cancelled: 0, paid: 0 },
            walletBalance: 0,
            modals: { quotation: false, editConfirm: false, saveConfirm: false, payment: false, cancel: false, success: false, error: false },
            messages: { success: '', error: '' },
            quotation: { id: null, loading: false, data: null },
            edit: { mode: false, edited: [], removed: [] },

            init() { this.loadQuotations(); this.getWallet(); this.$nextTick(() => this.icons()); },
            icons() { if (window.lucide) { window.lucide.createIcons(); } },
            onFilterChange() { this.pagination.page = 1; this.loadQuotations(); },
            clearFilters() { this.filters.search = ''; this.filters.status = 'all'; this.pagination.page = 1; this.loadQuotations(); },
            refresh() { this.loadQuotations(); },

            async loadQuotations() {
                try {
                    this.list.loading = true;
                    const params = new URLSearchParams({ action: 'getQuotations', search_term: this.filters.search, status_filter: this.filters.status, page: this.pagination.page, limit: this.pagination.limit });
                    const d = await fetch(`fetch/manageQuotations.php?${params}`).then(r => r.json());
                    if (d?.success) {
                        this.list.data = d.quotationData.data || [];
                        this.pagination.total = d.quotationData.total || 0;
                        this.pagination.page = d.quotationData.page || 1;
                        this.pagination.pages = Math.max(1, Math.ceil(this.pagination.total / this.pagination.limit));
                        const start = (this.pagination.page - 1) * this.pagination.limit + 1;
                        const end = Math.min(this.pagination.page * this.pagination.limit, this.pagination.total);
                        this.pagination.showing = this.pagination.total ? `${start}-${end}` : '0';
                        const s = d.stats || {};
                        this.stats.new = parseInt(s.new || 0);
                        this.stats.processing = parseInt(s.processing || 0);
                        this.stats.processed = parseInt(s.processed || 0);
                        this.stats.cancelled = parseInt(s.cancelled || 0);
                        this.stats.paid = parseInt(s.paid || 0);
                    } else {
                        this.list.data = []; this.pagination.total = 0; this.pagination.pages = 1; this.pagination.showing = '0';
                        this.error('Failed to load quotations');
                    }
                } catch (e) {
                    this.list.data = []; this.pagination.total = 0; this.pagination.pages = 1; this.pagination.showing = '0';
                    this.error('An error occurred while loading quotations');
                } finally {
                    this.list.loading = false; this.$nextTick(() => this.icons());
                }
            },
            prevPage() { if (this.pagination.page > 1) { this.pagination.page--; this.loadQuotations(); } },
            nextPage() { if (this.pagination.page < this.pagination.pages) { this.pagination.page++; this.loadQuotations(); } },

            money(a) { return new Intl.NumberFormat('en-UG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(parseFloat(a || 0)); },
            dateFmt(d) { const dt = new Date(d); const m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const day = dt.getDate(); const suf = (day === 1 || day === 21 || day === 31) ? 'st' : (day === 2 || day === 22) ? 'nd' : (day === 3 || day === 23) ? 'rd' : 'th'; return `${m[dt.getMonth()]} ${day}${suf}, ${dt.getFullYear()}`; },
            timeFmt(d) { const dt = new Date(d); let h = dt.getHours(); const min = dt.getMinutes(); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; const mm = min < 10 ? `0${min}` : min; return `${h}:${mm}${ap}`; },
            timeFull(date) { const dt = new Date(date); let h = dt.getHours(); const mi = dt.getMinutes(); const s = dt.getSeconds(); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; const mm = mi < 10 ? `0${mi}` : mi; const ss = s < 10 ? `0${s}` : s; return `${h}:${mm}:${ss} ${ap}`; },

            statusPill(s) {
                const x = (s || '').toLowerCase();
                if (x === 'new') return { bg: 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300', dot: 'bg-blue-500', label: 'New' };
                if (x === 'processing') return { bg: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300', dot: 'bg-yellow-500', label: 'Processing' };
                if (x === 'processed') return { bg: 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300', dot: 'bg-green-500', label: 'Processed' };
                if (x === 'cancelled') return { bg: 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300', dot: 'bg-red-500', label: 'Cancelled' };
                if (x === 'paid') return { bg: 'bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300', dot: 'bg-purple-500', label: 'Paid' };
                return { bg: 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80', dot: 'bg-gray-500', label: 'Unknown' };
            },
            totalAmount(item) { return parseFloat(item.items_total || 0) + parseFloat(item.transport || 0); },

            async openQuotation(id) {
                this.quotation = { id, loading: true, data: null }; this.modals.quotation = true; this.edit = { mode: false, edited: [], removed: [] };
                this.$nextTick(() => this.icons());
                try {
                    const d = await fetch(`fetch/manageQuotations.php?action=getRFQDetails&id=${id}`).then(r => r.json());
                    if (d?.success) { this.quotation.data = d; } else { this.quotation.data = null; }
                } catch (e) { this.quotation.data = null; }
                finally { this.quotation.loading = false; this.$nextTick(() => this.icons()); }
            },
            closeQuotation() { this.modals.quotation = false; this.quotation = { id: null, loading: false, data: null }; this.edit = { mode: false, edited: [], removed: [] }; },

            q() { return this.quotation.data?.quotation || {}; },

            mapsLink(c) { if (!c) return '#'; const m = String(c).match(/-?\d+\.?\d*/g); if (m && m.length >= 2) return `https://www.google.com/maps?q=${m[0]},${m[1]}`; return '#'; },

            canEdit() { const s = (this.q().status || '').toLowerCase(); const modified = parseInt(this.q().modified || 0) === 1; return s === 'processed' && !modified; },
            canCancel() { const s = (this.q().status || '').toLowerCase(); return ['new', 'processing', 'processed'].includes(s); },
            canPay() { const s = (this.q().status || '').toLowerCase(); return s === 'processed'; },

            enterEdit() { this.modals.editConfirm = false; this.edit.mode = true; this.$nextTick(() => this.icons()); },
            exitEdit() { this.edit.mode = false; this.edit.edited = []; this.edit.removed = []; this.$nextTick(() => this.icons()); },

            visibleItems() { if (!this.quotation.data) return []; return this.quotation.data.items.filter(it => !this.edit.removed.includes(it.RFQD_ID)); },
            qtyFor(id, fallback) { const e = this.edit.edited.find(x => x.id === id); return e ? e.quantity : parseInt(fallback || 1); },
            changeQty(id, val) { const q = Math.max(1, parseInt(val || 1)); const i = this.edit.edited.findIndex(x => x.id === id); if (i >= 0) this.edit.edited[i].quantity = q; else this.edit.edited.push({ id, quantity: q }); },
            removeItem(id) { if (this.visibleItems().length <= 1) { this.error('Cannot remove the last item. At least one item must remain.'); return; } if (!this.edit.removed.includes(id)) this.edit.removed.push(id); const idx = this.edit.edited.findIndex(x => x.id === id); if (idx >= 0) this.edit.edited.splice(idx, 1); },

            itemsSubtotal() {
                if (!this.quotation.data) return 0;
                let sum = 0;
                this.quotation.data.items.forEach(it => {
                    if (this.edit.removed.includes(it.RFQD_ID)) return;
                    const e = this.edit.edited.find(x => x.id === it.RFQD_ID);
                    const qty = e ? e.quantity : parseInt(it.quantity);
                    const up = parseFloat(it.unit_price || 0);
                    sum += up * qty;
                });
                return sum;
            },
            transport() { return parseFloat(this.q().transport || 0); },
            grandTotal() { return this.itemsSubtotal() + this.transport(); },

            openSaveConfirm() { if (this.edit.edited.length === 0 && this.edit.removed.length === 0) { this.error('No changes to save.'); return; } this.modals.saveConfirm = true; },
            async confirmSave() {
                this.modals.saveConfirm = false;
                try {
                    const payload = { action: 'updateQuotation', rfq_id: this.quotation.id, items: this.edit.edited, items_to_remove: this.edit.removed };
                    const d = await fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }).then(r => r.json());
                    if (d?.success) {
                        this.success('Changes saved successfully. Status changed to Processing.');
                        setTimeout(() => { this.modals.success = false; this.closeQuotation(); this.loadQuotations(); }, 1500);
                    } else {
                        this.error('Failed to save changes: ' + (d?.error || 'Unknown error'));
                    }
                } catch (e) { this.error('An error occurred while saving changes'); }
            },

            async getWallet() { try { const d = await fetch('fetch/manageQuotations.php?action=getWalletBalance').then(r => r.json()); if (d?.success) { this.walletBalance = parseFloat(d.balance || 0); } } catch (e) { } },
            openPayment() { this.getWallet(); this.modals.payment = true; this.$nextTick(() => this.icons()); },
            topUp() { const base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/'); window.open(`${base}account/zzimba-credit`, '_blank'); },
            async confirmPayment() {
                try {
                    const d = await fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'processQuotePayment', quotation_id: this.quotation.id, amount: this.grandTotal() }) }).then(r => r.json());
                    if (d?.success) {
                        this.success(`Payment successful! UGX ${this.money(d.amount_paid)} has been deducted from your wallet. Remaining balance: UGX ${this.money(d.remaining_balance)}`);
                        this.modals.payment = false;
                        setTimeout(() => { this.modals.success = false; this.closeQuotation(); this.loadQuotations(); this.getWallet(); }, 2000);
                    } else {
                        if (d?.message === 'Insufficient wallet balance') { this.error(`Insufficient wallet balance. You need UGX ${this.money((d.required || 0) - (d.balance || 0))} more to complete this payment.`); }
                        else { this.error('Payment failed: ' + (d?.message || 'Unknown error')); }
                    }
                } catch (e) { this.error('An error occurred while processing payment'); }
            },

            async confirmCancel() {
                try {
                    const d = await fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cancelQuotation', rfq_id: this.quotation.id }) }).then(r => r.json());
                    if (d?.success) {
                        this.success('Quotation cancelled successfully.');
                        this.modals.cancel = false;
                        setTimeout(() => { this.modals.success = false; this.closeQuotation(); this.loadQuotations(); }, 1500);
                    } else {
                        this.error('Failed to cancel quotation: ' + (d?.error || 'Unknown error'));
                    }
                } catch (e) { this.error('An error occurred while cancelling quotation'); }
            },

            success(msg) { this.messages.success = msg; this.modals.success = true; this.$nextTick(() => this.icons()); },
            error(msg) { this.messages.error = msg; this.modals.error = true; this.$nextTick(() => this.icons()); },

            async generatePDF() {
                if (!this.quotation.data) return;
                const q = this.q(), items = this.quotation.data.items;
                const pdfContainer = document.getElementById('pdfContent');
                let itemsTotal = 0; items.forEach(it => { if (it.unit_price && it.unit_price > 0) { itemsTotal += parseFloat(it.unit_price) * parseInt(it.quantity); } });
                const transport = this.transport(), grand = itemsTotal + transport;
                pdfContainer.innerHTML = `
                <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.4; font-size: 12px;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-size: 24px; margin: 0 0 10px 0; color: #D92B13;">My Quotation Report</h1>
                        <p style="font-size: 14px; color: #666; margin: 0;">Generated on ${this.dateFmt(new Date())} at ${this.timeFull(new Date())}</p>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                        <div style="width: 30%; min-width: 200px;">
                            <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Request Information</h3>
                            <div style="margin-bottom: 8px;"><strong>Status:</strong> ${q.status || ''}</div>
                            <div style="margin-bottom: 8px;"><strong>Fee Charged:</strong> UGX ${this.money(q.fee_charged || 0)}</div>
                        </div>
                        <div style="width: 30%; min-width: 250px;">
                            <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Location Details</h3>
                            <div style="margin-bottom: 8px;"><strong>Site Location:</strong></div>
                            <div style="margin-bottom: 8px; word-wrap: break-word;">${q.site_location || ''}</div>
                            ${q.coordinates ? `<div style="margin-bottom: 8px; color: #666; font-size: 11px;">${q.coordinates}</div>` : ''}
                        </div>
                        <div style="width: 30%; min-width: 200px;">
                            <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Dates</h3>
                            <div style="margin-bottom: 8px;"><strong>Created:</strong> ${this.dateFmt(q.created_at || new Date())} ${this.timeFmt(q.created_at || new Date())}</div>
                        </div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Requested Items</h3>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                            <thead>
                                <tr style="background-color: #f3f4f6;">
                                    <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Brand/Material</th>
                                    <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Size/Specification</th>
                                    <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Quantity</th>
                                    <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Unit Price (UGX)</th>
                                    <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Total (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(it => { const up = parseFloat(it.unit_price || 0); const qn = parseInt(it.quantity || 0); const t = up * qn; return `<tr><td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${it.brand_name}</td><td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${it.size}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db;">${qn}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 500;">${this.money(up)}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600;">${this.money(t)}</td></tr>`; }).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <div style="width: 300px; border: 2px solid #d1d5db; padding: 15px; background-color: #f9fafb;">
                            <h3 style="font-size: 16px; margin: 0 0 15px 0; color: #374151;">Summary</h3>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 6px;">
                                <span style="font-size: 14px;">Items Subtotal:</span>
                                <span style="font-size: 14px; font-weight: 600;">UGX ${this.money(itemsTotal)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px;">
                                <span style="font-size: 14px;">Delivery Charge:</span>
                                <span style="font-size: 14px; font-weight: 600;">UGX ${this.money(transport)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid #374151; margin-top: 12px;">
                                <span style="font-size: 16px; font-weight: 700;">Total Amount:</span>
                                <span style="font-size: 16px; font-weight: 700; color: #047857;">UGX ${this.money(grand)}</span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 30px; text-align: center; color: #666; font-size: 11px;">
                        <p style="margin: 0;">This is a computer-generated document. No signature is required.</p>
                    </div>
                </div>`;
                const { jsPDF } = window.jspdf;
                const canvas = await html2canvas(pdfContainer, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff', width: pdfContainer.scrollWidth, height: pdfContainer.scrollHeight, scrollX: 0, scrollY: 0 });
                const img = canvas.toDataURL('image/png');
                const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
                const pw = pdf.internal.pageSize.getWidth(); const ph = pdf.internal.pageSize.getHeight();
                const prop = pdf.getImageProperties(img); const ih = (prop.height * pw) / prop.width;
                let left = ih; let pos = 0; pdf.addImage(img, 'PNG', 0, pos, pw, ih); left -= ph;
                while (left >= 0) { pos = left - ih; pdf.addPage(); pdf.addImage(img, 'PNG', 0, pos, pw, ih); left -= ph; }
                const name = `My_Quotation_${new Date().toISOString().slice(0, 10)}.pdf`; pdf.save(name);
            }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>