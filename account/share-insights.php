<?php
$pageTitle = 'Share Insights';
$activeNav = 'share-insights';
require_once __DIR__ . '/../config/config.php';
$baseUrl = $_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
    ? 'http://' . $_SERVER['HTTP_HOST']
    : 'https://' . $_SERVER['HTTP_HOST'];
ob_start();
?>
<div x-data="shareInsights()" x-init="init()" x-cloak class="min-h-screen bg-user-content dark:bg-secondary/10">
    <style>
        [x-cloak] {
            display: none
        }

        .copy-success {
            animation: copyPulse 0.5s ease-out;
        }

        @keyframes copyPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>

    <div class="bg-white dark:bg-secondary border-b border-gray-200 dark:border-white/10 px-3 sm:px-6 py-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-user-primary/10 rounded-xl grid place-items-center flex-shrink-0">
                        <i data-lucide="share-2" class="w-5 h-5 text-user-primary"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-secondary dark:text-white">Share Insights</h1>
                        <p class="text-xs sm:text-sm text-gray-text dark:text-white/70">Track your links performance</p>
                    </div>
                </div>
                <button @click="refresh()"
                    class="w-full sm:w-auto px-4 py-2.5 bg-user-primary text-white rounded-xl hover:bg-user-primary/90 inline-flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"
                        :class="{'animate-spin': loading.any}"></i><span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-3 sm:px-4 py-3 sm:py-4 space-y-3 sm:space-y-4">

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Clicks
                </div>
                <div class="text-lg sm:text-xl font-bold text-secondary dark:text-white mt-1"
                    x-text="fmt(overview.clicks)"></div>
                <div class="text-[10px] text-green-600 dark:text-green-300" x-text="trendLabel(overview.clicks_delta)">
                </div>
            </div>
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Unique
                </div>
                <div class="text-lg sm:text-xl font-bold text-secondary dark:text-white mt-1"
                    x-text="fmt(overview.unique_clicks)"></div>
                <div class="text-[10px] text-green-600 dark:text-green-300" x-text="trendLabel(overview.unique_delta)">
                </div>
            </div>
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Links
                </div>
                <div class="text-lg sm:text-xl font-bold text-secondary dark:text-white mt-1"
                    x-text="fmt(overview.total_links)"></div>
                <div class="text-[10px] text-gray-500 dark:text-white/60"
                    x-text="`${fmt(overview.active_links)} active`"></div>
            </div>
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Expired
                </div>
                <div class="text-lg sm:text-xl font-bold text-secondary dark:text-white mt-1"
                    x-text="fmt(overview.expired_links)"></div>
                <div class="text-[10px] text-gray-500 dark:text-white/60">lifetime</div>
            </div>
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Top
                    Country</div>
                <div class="text-sm sm:text-base font-semibold text-secondary dark:text-white truncate mt-1"
                    x-text="overview.top_country||'-'"></div>
                <div class="text-[10px] text-gray-500 dark:text-white/60" x-text="overview.top_country_code||''"></div>
            </div>
            <div class="rounded-xl p-3 border border-gray-200 dark:border-white/10 bg-white dark:bg-secondary">
                <div class="text-[10px] sm:text-[11px] uppercase tracking-wide text-gray-500 dark:text-white/60">Top
                    Source</div>
                <div class="text-sm sm:text-base font-semibold text-secondary dark:text-white truncate mt-1"
                    x-text="overview.top_source||'-'"></div>
                <div class="text-[10px] text-gray-500 dark:text-white/60" x-text="overview.top_medium||''"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10 p-3 sm:p-4">
            <div class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    <button @click="setFilter('daily')" :class="filterBtn('daily')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm">Daily</button>
                    <button @click="setFilter('weekly')" :class="filterBtn('weekly')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm">Weekly</button>
                    <button @click="setFilter('monthly')" :class="filterBtn('monthly')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm">Monthly</button>
                    <button @click="setFilter('yearly')" :class="filterBtn('yearly')"
                        class="px-3 py-1.5 rounded-lg text-xs sm:text-sm">Yearly</button>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                    <input type="date" x-model="filters.start_date" @change="reloadAll()"
                        class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-xs sm:text-sm">
                    <span class="hidden sm:inline text-gray-400 dark:text-white/50">to</span>
                    <input type="date" x-model="filters.end_date" @change="reloadAll()"
                        class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-xs sm:text-sm">
                </div>
            </div>

            <div class="mt-4">
                <div class="h-32 sm:h-44 w-full relative bg-gray-50 dark:bg-white/5 rounded-lg p-2 overflow-hidden">
                    <div class="absolute inset-0 flex items-end gap-[1px] p-2" x-ref="bars">
                        <template x-for="(d,i) in chartData" :key="`b-${i}`">
                            <div class="flex-1 relative group min-w-0">
                                <div class="w-full rounded-t bg-user-primary/40 dark:bg-user-primary/50 transition-all hover:bg-user-primary/60"
                                    :style="`height:${barHeight(d)}%`" :title="`${chartLabels[i]}: ${fmt(d)} clicks`">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div
                    class="mt-2 flex justify-between text-[10px] sm:text-[11px] text-gray-500 dark:text-white/60 overflow-hidden">
                    <div x-text="chartLabels[0]||''"></div>
                    <div x-text="chartLabels[chartLabels.length-1]||''"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
            <div
                class="lg:col-span-2 bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div
                    class="p-3 sm:p-4 border-b border-gray-100 dark:border-white/10 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <h3 class="text-base font-semibold text-secondary dark:text-white">Top Links</h3>
                    <input x-model.debounce.300ms="top.search" @input="loadTopLinks(true)" type="text"
                        placeholder="Search..."
                        class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-xs sm:text-sm">
                    <select x-model="top.limit" @change="loadTopLinks(true)"
                        class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-xs sm:text-sm">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-96 overflow-y-auto">
                    <template x-for="row in top.rows" :key="`t-${row.id}`">
                        <div class="px-3 sm:px-4 py-3 space-y-2">
                            <div>
                                <div class="text-sm sm:text-base font-medium text-secondary dark:text-white truncate">
                                    <span x-text="row.code"></span> • <span
                                        x-text="row.target_name || row.target_type"></span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-white/60 truncate"
                                    x-text="row.campaign || row.note || row.target_url"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="text-right">
                                    <div class="font-semibold text-secondary dark:text-white" x-text="fmt(row.clicks)">
                                    </div>
                                    <div class="text-gray-500 dark:text-white/60">clicks</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-secondary dark:text-white" x-text="fmt(row.uniques)">
                                    </div>
                                    <div class="text-gray-500 dark:text-white/60">unique</div>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 dark:bg-white/5 rounded col-span-3">
                                    <div class="h-1.5 rounded bg-user-primary" :style="`width:${row.share||0}%;`"></div>
                                </div>
                            </div>
                            <div class="flex gap-1 flex-wrap">
                                <button @click="copyLink(row)" :data-copied="row.id === copiedId"
                                    class="flex-1 px-2 py-1.5 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-xs transition"
                                    :class="row.id === copiedId ? 'bg-green-50 border-green-300 dark:bg-green-500/10 dark:border-green-500/30' : ''">
                                    <i :data-lucide="row.id === copiedId ? 'check' : 'copy'" class="w-3 h-3 inline"></i>
                                </button>
                                <a :href="row.target_url" target="_blank"
                                    class="flex-1 px-2 py-1.5 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-xs text-center">
                                    <i data-lucide="external-link" class="w-3 h-3 inline"></i>
                                </a>
                                <button @click="openLink(row.id)"
                                    class="flex-1 px-2 py-1.5 rounded border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-xs">
                                    <i data-lucide="bar-chart-3" class="w-3 h-3 inline"></i>
                                </button>
                                <button @click="toggleActive(row)" class="flex-1 px-2 py-1.5 rounded text-xs"
                                    :class="row.active ? 'bg-green-50 border border-green-200 dark:bg-green-500/10 dark:border-green-500/30' : 'bg-gray-50 border border-gray-200 dark:bg-white/10 dark:border-white/10'">
                                    <i :data-lucide="row.active ? 'toggle-right' : 'toggle-left'"
                                        class="w-3 h-3 inline"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div class="px-3 sm:px-4 py-4 text-center text-gray-500 dark:text-white/70 text-sm"
                        x-show="!top.loading && top.rows.length===0">No data</div>
                    <div class="px-3 sm:px-4 py-3 text-center" x-show="top.has_more">
                        <button @click="loadTopLinks()" class="text-sm text-user-primary hover:underline">Load
                            more</button>
                    </div>
                </div>
            </div>

            <div class="space-y-3 sm:space-y-4">
                <div class="bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10">
                    <div
                        class="p-3 sm:p-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-secondary dark:text-white">Breakdown</h3>
                        <select x-model="breakdown.tab" @change="loadBreakdowns()"
                            class="px-2 py-1 rounded text-xs border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5">
                            <option value="geo">Geo</option>
                            <option value="device">Device</option>
                            <option value="utm">UTM</option>
                            <option value="ref">Referrers</option>
                        </select>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-80 overflow-y-auto">
                        <template x-for="b in breakdown.rows" :key="`b-${b.k}`">
                            <div class="px-3 sm:px-4 py-3">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="text-xs sm:text-sm font-medium text-secondary dark:text-white truncate"
                                        x-text="b.k||'Unknown'"></div>
                                    <div class="text-right text-xs">
                                        <div class="font-semibold text-secondary dark:text-white" x-text="fmt(b.total)">
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 dark:bg-white/5 rounded">
                                    <div class="h-1.5 rounded bg-user-primary" :style="`width:${b.share||0}%;`"></div>
                                </div>
                            </div>
                        </template>
                        <div class="px-3 sm:px-4 py-4 text-center text-gray-500 dark:text-white/70 text-sm"
                            x-show="breakdown.rows.length===0">No data</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10">
                    <div class="p-3 sm:p-4 border-b border-gray-100 dark:border-white/10">
                        <h3 class="text-base font-semibold text-secondary dark:text-white">Recent Activity</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-80 overflow-y-auto">
                        <template x-for="c in stream.rows" :key="`c-${c.id}`">
                            <div class="px-3 sm:px-4 py-2 text-xs">
                                <div class="text-gray-600 dark:text-white/80 truncate"
                                    x-text="`${c.code} • ${c.target_type}`"></div>
                                <div class="text-gray-500 dark:text-white/60 truncate"
                                    x-text="`${c.country||'Unknown'} • ${c.browser||'-'}`"></div>
                                <div class="text-gray-400 dark:text-white/50 text-[10px]"
                                    x-text="timeFmt(c.created_at)"></div>
                            </div>
                        </template>
                        <div class="px-3 sm:px-4 py-4 text-center text-gray-500 dark:text-white/70 text-sm"
                            x-show="stream.rows.length===0">No clicks yet</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.link" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeLink()"></div>
        <div class="bg-white dark:bg-secondary rounded-2xl w-full max-w-2xl relative z-10 overflow-auto max-h-[90vh]">
            <div
                class="p-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between sticky top-0 bg-white dark:bg-secondary">
                <div class="min-w-0">
                    <h3 class="font-bold text-secondary dark:text-white truncate" x-text="link.details?.code||'Link'">
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-white/60 truncate" x-text="link.details?.target_url||''">
                    </p>
                </div>
                <button @click="closeLink()" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-white/10 flex-shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div class="rounded-lg p-2 border border-gray-200 dark:border-white/10">
                        <div class="text-[10px] text-gray-500 uppercase">Clicks</div>
                        <div class="text-lg font-bold text-secondary dark:text-white"
                            x-text="fmt(link.metrics?.clicks||0)"></div>
                    </div>
                    <div class="rounded-lg p-2 border border-gray-200 dark:border-white/10">
                        <div class="text-[10px] text-gray-500 uppercase">Unique</div>
                        <div class="text-lg font-bold text-secondary dark:text-white"
                            x-text="fmt(link.metrics?.uniques||0)"></div>
                    </div>
                    <div class="rounded-lg p-2 border border-gray-200 dark:border-white/10">
                        <div class="text-[10px] text-gray-500 uppercase">Active</div>
                        <div class="text-lg font-bold" :class="link.details?.active?'text-green-600':'text-gray-500'"
                            x-text="link.details?.active?'Yes':'No'"></div>
                    </div>
                    <div class="rounded-lg p-2 border border-gray-200 dark:border-white/10">
                        <div class="text-[10px] text-gray-500 uppercase">Campaign</div>
                        <div class="text-sm font-semibold text-secondary dark:text-white truncate"
                            x-text="link.details?.campaign||'–'"></div>
                    </div>
                </div>
                <div class="h-28 w-full relative bg-gray-50 dark:bg-white/5 rounded-lg p-2">
                    <div class="absolute inset-0 flex items-end gap-[1px] p-2">
                        <template x-for="(d,i) in link.series" :key="`ls-${i}`">
                            <div class="flex-1">
                                <div class="w-full rounded-t bg-user-primary/40"
                                    :style="`height:${barHeight(d.total)}%`"></div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 dark:border-white/10">
                        <div class="p-3 font-semibold text-secondary dark:text-white text-sm">Countries</div>
                        <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-40 overflow-y-auto">
                            <template x-for="g in link.geo" :key="`lg-${g.k}`">
                                <div class="px-3 py-2 text-xs flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-secondary dark:text-white truncate" x-text="g.k||'Unknown'">
                                        </div>
                                        <div class="w-20 h-1 bg-gray-100 dark:bg-white/5 rounded mt-0.5">
                                            <div class="h-1 rounded bg-user-primary" :style="`width:${g.share||0}%;`">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-secondary dark:text-white ml-2" x-text="fmt(g.total)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 dark:border-white/10">
                        <div class="p-3 font-semibold text-secondary dark:text-white text-sm">Sources</div>
                        <div class="divide-y divide-gray-100 dark:divide-white/10 max-h-40 overflow-y-auto">
                            <template x-for="u in link.utm" :key="`lu-${u.k}`">
                                <div class="px-3 py-2 text-xs flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-secondary dark:text-white truncate" x-text="u.k||'Unknown'">
                                        </div>
                                        <div class="w-20 h-1 bg-gray-100 dark:bg-white/5 rounded mt-0.5">
                                            <div class="h-1 rounded bg-user-primary" :style="`width:${u.share||0}%;`">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-secondary dark:text-white ml-2" x-text="fmt(u.total)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-white/60 mb-2">Note</label>
                    <textarea x-model="link.note" @keydown.escape="closeLink()"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-sm resize-none h-20"
                        placeholder="Add a note..."></textarea>
                    <button @click="saveNote()"
                        class="mt-2 w-full px-4 py-2 bg-user-primary text-white rounded-lg text-sm font-medium hover:bg-user-primary/90 disabled:opacity-50"
                        :disabled="link.saving">
                        <span x-show="!link.saving">Save Note</span>
                        <span x-show="link.saving" class="inline-flex items-center gap-2">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 p-3 sm:hidden">
        <button @click="refresh()"
            class="w-full px-4 py-3 rounded-2xl bg-user-primary text-white font-medium active:scale-95 transition">
            <span x-show="!loading.any">Refresh</span>
            <span x-show="loading.any" class="inline-flex items-center gap-2">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>Loading
            </span>
        </button>
    </div>
</div>

<script>
    function shareInsights() {
        const BASE_URL = '<?php echo $baseUrl; ?>';
        return {
            loading: { any: false },
            filters: { preset: 'monthly', start_date: '', end_date: '' },
            overview: {},
            chartLabels: [],
            chartData: [],
            top: { rows: [], cursor: null, limit: 10, has_more: false, search: '', loading: false },
            breakdown: { tab: 'geo', rows: [], loading: false },
            stream: { rows: [], loading: false },
            link: { id: null, details: null, metrics: null, series: [], geo: [], utm: [], note: '', saving: false },
            copiedId: null,
            pendingRequests: {},
            lastRequestTime: {},
            init() {
                const d = new Date(), e = new Date();
                d.setDate(d.getDate() - 29);
                this.filters.start_date = this.iso(d);
                this.filters.end_date = this.iso(e);
                this.reloadAll();
                this.$nextTick(() => this.icons());
            },
            icons() {
                if (window.lucide) window.lucide.createIcons();
            },
            fmt(n) {
                return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(parseFloat(n || 0));
            },
            iso(dt) {
                const y = dt.getFullYear(), m = String(dt.getMonth() + 1).padStart(2, '0'), d = String(dt.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            },
            timeFmt(d) {
                const dt = new Date(d);
                let h = dt.getHours();
                const min = dt.getMinutes(), ap = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return `${h}:${String(min).padStart(2, '0')}${ap}`;
            },
            barHeight(v) {
                const max = Math.max(...this.chartData.filter(x => !isNaN(x)), 1);
                return v > 0 ? Math.round((v / max) * 100) : 0;
            },
            trendLabel(x) {
                const v = parseFloat(x || 0);
                if (v > 0) return `+${this.fmt(v)} vs prev`;
                if (v < 0) return `${this.fmt(v)} vs prev`;
                return 'no change';
            },
            filterBtn(k) {
                return this.filters.preset === k ? 'bg-user-primary text-white' : 'border border-gray-200 dark:border-white/10 text-secondary dark:text-white';
            },
            setFilter(k) {
                this.filters.preset = k;
                const end = new Date();
                let start = new Date();
                if (k === 'daily') start = new Date();
                else if (k === 'weekly') start.setDate(end.getDate() - 6);
                else if (k === 'monthly') start.setDate(end.getDate() - 29);
                else if (k === 'yearly') { start.setFullYear(end.getFullYear()); start.setMonth(0); start.setDate(1); }
                this.filters.start_date = this.iso(start);
                this.filters.end_date = this.iso(end);
                this.reloadAll();
            },
            params(extra = {}) {
                const p = new URLSearchParams({ start_date: this.filters.start_date, end_date: this.filters.end_date, period: this.filters.preset });
                for (const k in extra) {
                    if (extra[k] !== undefined && extra[k] !== null) p.set(k, extra[k]);
                }
                return p.toString();
            },
            cachedFetch(key, url) {
                const now = Date.now();
                if (this.lastRequestTime[key] && now - this.lastRequestTime[key] < 500) {
                    return this.pendingRequests[key] || Promise.resolve(null);
                }
                if (this.pendingRequests[key]) return this.pendingRequests[key];

                this.lastRequestTime[key] = now;
                const promise = fetch(url)
                    .then(r => r.json())
                    .catch(() => null)
                    .finally(() => {
                        delete this.pendingRequests[key];
                    });
                this.pendingRequests[key] = promise;
                return promise;
            },
            refresh() {
                this.reloadAll();
            },
            reloadAll() {
                this.loadOverview();
                this.loadSeries();
                this.loadTopLinks(true);
                this.loadBreakdowns();
                this.loadStream();
            },
            async loadOverview() {
                this.loading.any = true;
                const d = await this.cachedFetch('overview', `fetch/manageShareInsights.php?action=overview&${this.params()}`);
                if (d?.success) this.overview = d.data;
                this.loading.any = false;
                this.$nextTick(() => this.icons());
            },
            async loadSeries() {
                const d = await this.cachedFetch('series', `fetch/manageShareInsights.php?action=timeseries&${this.params()}`);
                if (d?.success) { this.chartLabels = d.labels || []; this.chartData = d.data || []; }
                this.$nextTick(() => this.icons());
            },
            async loadTopLinks(reset = false) {
                if (reset) { this.top.rows = []; this.top.cursor = null; }
                this.top.loading = true;
                const url = `fetch/manageShareInsights.php?action=top_links&${this.params({ cursor: this.top.cursor, limit: this.top.limit, search: this.top.search })}`;
                const d = await this.cachedFetch(`toplinks-${this.filters.start_date}-${this.filters.end_date}-${this.top.search}`, url);
                if (d?.success) {
                    this.top.rows = reset ? (d.rows || []) : this.top.rows.concat(d.rows || []);
                    this.top.cursor = d.next_cursor || null;
                    this.top.has_more = !!d.next_cursor;
                }
                this.$nextTick(() => this.icons());
                this.top.loading = false;
            },
            async loadBreakdowns() {
                this.breakdown.loading = true;
                const act = this.breakdown.tab === 'geo' ? 'geo_breakdown' : this.breakdown.tab === 'device' ? 'device_breakdown' : this.breakdown.tab === 'utm' ? 'utm_breakdown' : 'referrers';
                const d = await this.cachedFetch(act, `fetch/manageShareInsights.php?action=${act}&${this.params()}`);
                this.breakdown.rows = d?.rows || [];
                this.breakdown.loading = false;
                this.$nextTick(() => this.icons());
            },
            async loadStream() {
                this.stream.loading = true;
                const d = await this.cachedFetch('stream', `fetch/manageShareInsights.php?action=click_stream&${this.params({ limit: 50 })}`);
                this.stream.rows = d?.rows || [];
                this.stream.loading = false;
                this.$nextTick(() => this.icons());
            },
            async openLink(id) {
                this.modals.link = true;
                this.link = { id, details: null, metrics: null, series: [], geo: [], utm: [], note: '', saving: false };
                const a = await fetch(`fetch/manageShareInsights.php?action=link_details&${this.params({ link_id: id })}`).then(r => r.json()).catch(() => null);
                if (a?.success) {
                    this.link.details = a.details;
                    this.link.metrics = a.metrics;
                    this.link.series = a.series || [];
                    this.link.geo = a.geo || [];
                    this.link.utm = a.utm || [];
                    this.link.note = a.details?.note || '';
                }
                this.$nextTick(() => this.icons());
            },
            closeLink() {
                this.modals.link = false;
            },
            async toggleActive(row) {
                const d = await fetch(`fetch/manageShareInsights.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toggle_active', link_id: row.id, active: row.active ? 0 : 1 })
                }).then(r => r.json()).catch(() => null);
                if (d?.success) {
                    row.active = d.active;
                    alert('Link ' + (d.active ? 'activated' : 'deactivated'));
                }
            },
            async saveNote() {
                this.link.saving = true;
                const d = await fetch(`fetch/manageShareInsights.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'update_note', link_id: this.link.id, note: this.link.note })
                }).then(r => r.json()).catch(() => null);
                this.link.saving = false;
                if (d?.success) {
                    const t = this.top.rows.find(x => x.id === this.link.id);
                    if (t) t.note = this.link.note;
                    alert('Note saved successfully');
                }
            },
            copyLink(row) {
                const url = `${BASE_URL}/r/${row.code}`;
                navigator.clipboard.writeText(url);
                this.copiedId = row.id;
                setTimeout(() => { this.copiedId = null; }, 2000);
            },
            modals: { link: false }
        };
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>