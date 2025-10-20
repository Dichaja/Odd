<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Store Statistics';
$activeNav = 'store-statistics';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
$storeStmt = $pdo->prepare("SELECT id, name, owner_id FROM vendor_stores WHERE id = :sid AND status IN ('active','pending','inactive','suspended')");
$storeStmt->execute([':sid' => $storeId]);
$store = $storeStmt->fetch(PDO::FETCH_ASSOC);
if (!$store) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$isAdmin = !empty($_SESSION['user']['is_admin']);
$isOwner = $store['owner_id'] === $_SESSION['user']['user_id'];
$isManager = false;
if (!$isAdmin && !$isOwner) {
    $mgr = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = :sid AND user_id = :uid AND status = 'active' AND approved = 1 LIMIT 1");
    $mgr->execute([':sid' => $storeId, ':uid' => $_SESSION['user']['user_id']]);
    $isManager = (bool) $mgr->fetchColumn();
}
if (!$isAdmin && !$isOwner && !$isManager) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$sessionUlid = $_SESSION['session_ulid'] ?? (session_id() ?: '');
ob_start();
?>
<script>
    if (!window.$) window.$ = {};
    (function ($) {
        function toQS(obj) {
            if (!obj) return '';
            const p = [];
            for (const k in obj) {
                if (obj[k] === undefined || obj[k] === null) continue;
                p.push(encodeURIComponent(k) + '=' + encodeURIComponent(typeof obj[k] === 'object' ? JSON.stringify(obj[k]) : obj[k]));
            }
            return p.join('&');
        }
        function wrap(promise) {
            const chain = {
                done(fn) { promise.then(resp => fn(resp)); return chain },
                fail(fn) { promise.catch(err => fn(err)); return chain },
                always(fn) { promise.finally(() => fn()); return chain }
            };
            return chain;
        }
        $.ajax = function (opts) {
            const url = opts.url || '';
            const method = (opts.type || opts.method || 'GET').toUpperCase();
            const headers = opts.headers || {};
            let body;
            if (method !== 'GET' && opts.data) {
                if (opts.processData === false || opts.contentType === false) {
                    body = opts.data;
                } else if (opts.contentType && String(opts.contentType).includes('application/json')) {
                    headers['Content-Type'] = 'application/json';
                    body = typeof opts.data === 'string' ? opts.data : JSON.stringify(opts.data);
                } else {
                    headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
                    body = typeof opts.data === 'string' ? opts.data : toQS(opts.data);
                }
            }
            const finalUrl = method === 'GET' && opts.data ? (url + (url.includes('?') ? '&' : '?') + toQS(opts.data)) : url;
            const p = fetch(finalUrl, { method, headers, body, credentials: 'same-origin', cache: 'no-store' }).then(async r => {
                const ct = r.headers.get('content-type') || '';
                if (!r.ok) throw new Error('HTTP ' + r.status);
                if (opts.dataType === 'json' || ct.includes('application/json')) return r.json();
                if (opts.dataType === 'text') return r.text();
                return r.text();
            });
            return wrap(p);
        };
        $.get = function (url, data, cb, type) {
            const w = $.ajax({ url, data, type: 'GET', dataType: type || 'json' });
            if (typeof cb === 'function') w.done(cb);
            return w;
        };
        $.post = function (url, data, cb, type) {
            const w = $.ajax({ url, data, type: 'POST', dataType: type || 'json' });
            if (typeof cb === 'function') w.done(cb);
            return w;
        };
        $.getJSON = function (u, cb) {
            return $.get(u, null, cb, 'json');
        };
    })(window.$);
</script>

<div x-data="statsApp()" x-init="init()" class="space-y-4 md:space-y-6">
    <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 md:p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-user-primary"></i>
                    <h2 class="text-base md:text-lg font-semibold text-gray-900">Analytics</h2>
                </div>
                <div class="flex w-full md:w-auto items-center gap-2">
                    <div class="inline-flex rounded-lg border border-gray-200 overflow-hidden">
                        <button @click="setGranularity('daily')"
                            :class="granularity === 'daily' ? 'bg-user-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-3 md:px-4 py-2 text-sm font-medium transition-colors">Daily</button>
                        <button @click="setGranularity('weekly')"
                            :class="granularity === 'weekly' ? 'bg-user-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-3 md:px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200">Weekly</button>
                        <button @click="setGranularity('monthly')"
                            :class="granularity === 'monthly' ? 'bg-user-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-3 md:px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200">Monthly</button>
                        <button @click="setGranularity('yearly')"
                            :class="granularity === 'yearly' ? 'bg-user-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="px-3 md:px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200">Yearly</button>
                    </div>
                    <div class="flex items-center gap-2 ml-auto">
                        <button @click="navigate(-1)"
                            class="h-9 w-9 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center transition-colors">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <div class="w-40 md:w-48">
                            <input x-show="granularity === 'daily'" x-model="dateDaily" @change="applyFilter()"
                                type="date"
                                class="w-full h-9 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-sm">
                            <input x-show="granularity === 'weekly'" x-model="dateWeekly" @change="applyFilter()"
                                type="date"
                                class="w-full h-9 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-sm">
                            <input x-show="granularity === 'monthly'" x-model="dateMonthly" @change="applyFilter()"
                                type="month"
                                class="w-full h-9 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-sm">
                            <input x-show="granularity === 'yearly'" x-model="dateYearly" @change="applyFilter()"
                                type="number" min="2000" max="2100"
                                class="w-full h-9 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-sm">
                        </div>
                        <button @click="navigate(1)"
                            class="h-9 w-9 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center transition-colors">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                        <button @click="refreshData()" :disabled="loading"
                            class="h-9 w-9 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center transition-colors"
                            :class="loading ? 'opacity-60 cursor-not-allowed' : ''">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-gray-700"
                                :class="loading ? 'animate-spin' : ''"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 md:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Profile</span>
                <i data-lucide="eye" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-semibold text-gray-900" x-text="summaryStats.profile"></div>
                <div class="text-xs text-gray-500">Profile Views</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 md:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Pricing</span>
                <i data-lucide="tag" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-semibold text-gray-900" x-text="summaryStats.price"></div>
                <div class="text-xs text-gray-500">Price Views</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 md:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Contact</span>
                <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
            </div>
            <div class="mt-2">
                <div class="text-2xl font-semibold text-gray-900" x-text="summaryStats.contact"></div>
                <div class="text-xs text-gray-500">Contact Views</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 md:p-5 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-user-primary/10 flex items-center justify-center">
                        <i data-lucide="line-chart" class="w-4 h-4 text-user-primary"></i>
                    </div>
                    <h3 class="text-sm md:text-base font-semibold text-gray-900">Views Overview</h3>
                </div>
            </div>
            <div class="p-4 md:p-5">
                <canvas id="combinedChart" x-ref="combinedChart" class="w-full"
                    style="height: 300px; max-height: 440px;"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 md:p-5 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-user-primary/10 flex items-center justify-center">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-user-primary"></i>
                    </div>
                    <h3 class="text-sm md:text-base font-semibold text-gray-900">Contact Breakdown</h3>
                </div>
            </div>
            <div class="p-4 md:p-5">
                <canvas id="contactBarChart" x-ref="contactBarChart" class="w-full"
                    style="height: 300px; max-height: 440px;"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 overflow-x-auto">
            <div class="flex min-w-max">
                <button @click="activeTab = 'price'"
                    :class="activeTab === 'price' ? 'border-user-primary text-user-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 md:flex-none px-4 md:px-6 py-3 md:py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="tag" class="w-4 h-4 inline-block mr-2"></i>
                    Price Views
                </button>
                <button @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'border-user-primary text-user-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 md:flex-none px-4 md:px-6 py-3 md:py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="eye" class="w-4 h-4 inline-block mr-2"></i>
                    Profile Views
                </button>
                <button @click="activeTab = 'contact'"
                    :class="activeTab === 'contact' ? 'border-user-primary text-user-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 md:flex-none px-4 md:px-6 py-3 md:py-4 text-sm font-medium border-b-2 transition-colors whitespace-nowrap">
                    <i data-lucide="phone" class="w-4 h-4 inline-block mr-2"></i>
                    Contact Views
                </button>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div x-show="activeTab === 'price'">
                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                        Package</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                        User</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                </tr>
                            </thead>
                            <tbody id="priceTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
                <div id="priceCount" class="text-xs text-gray-500 mt-3 px-1"></div>
            </div>

            <div x-show="activeTab === 'profile'">
                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time</th>
                                </tr>
                            </thead>
                            <tbody id="profileTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
                <div id="profileCount" class="text-xs text-gray-500 mt-3 px-1"></div>
            </div>

            <div x-show="activeTab === 'contact'">
                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User</th>
                                    <th
                                        class="px-3 md:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time</th>
                                </tr>
                            </thead>
                            <tbody id="contactTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
                <div id="contactCount" class="text-xs text-gray-500 mt-3 px-1"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .pkg .compound {
        display: inline-flex;
        align-items: flex-start;
        line-height: 1
    }

    .pkg .compound .whole {
        margin-right: 2px;
        font-size: 2rem
    }

    .pkg .compound .frac {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        line-height: 1;
        vertical-align: middle
    }

    .pkg .compound .frac sup,
    .pkg .compound .frac sub {
        font-size: .7em;
        line-height: 1
    }

    [x-cloak] {
        display: none !important
    }

    @media (max-width:768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite
    }
</style>
<script>
    function statsApp() {
        return {
            granularity: 'daily',
            anchorDate: new Date(),
            dateDaily: '',
            dateWeekly: '',
            dateMonthly: '',
            dateYearly: '',
            activeTab: 'price',
            loading: false,
            charts: { combined: null, contactBar: null },
            summaryStats: { profile: 0, price: 0, contact: 0 },
            API_URL: '<?= BASE_URL ?>vendor-store/fetch/manageStoreStatistics.php',
            init() {
                const now = new Date();
                this.dateDaily = this.toYMD(now);
                this.dateWeekly = this.toYMD(this.startOfWeekSunday(now));
                this.dateMonthly = `${this.y(now.getFullYear())}-${this.m(now.getMonth() + 1)}`;
                this.dateYearly = String(now.getFullYear());
                this.$nextTick(() => {
                    this.applyFilter();
                    if (window.lucide && window.lucide.createIcons) window.lucide.createIcons();
                });
                window.addEventListener('beforeunload', () => { this.teardownCharts() });
            },
            setGranularity(g) {
                if (this.granularity !== g) this.granularity = g;
                this.applyFilter();
            },
            navigate(dir) {
                if (this.granularity === 'daily') {
                    const d = new Date(this.dateDaily || this.toYMD(new Date()));
                    d.setDate(d.getDate() + dir);
                    this.dateDaily = this.toYMD(d);
                } else if (this.granularity === 'weekly') {
                    const s = this.startOfWeekSunday(new Date(this.dateWeekly || this.toYMD(new Date())));
                    s.setDate(s.getDate() + (7 * dir));
                    this.dateWeekly = this.toYMD(s);
                } else if (this.granularity === 'monthly') {
                    const parts = (this.dateMonthly || `${this.y(new Date().getFullYear())}-${this.m(new Date().getMonth() + 1)}`).split('-');
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
                    d.setMonth(d.getMonth() + dir, 1);
                    this.dateMonthly = `${this.y(d.getFullYear())}-${this.m(d.getMonth() + 1)}`;
                } else if (this.granularity === 'yearly') {
                    const y = parseInt(this.dateYearly || String(new Date().getFullYear()));
                    this.dateYearly = String(y + dir);
                }
                this.applyFilter();
            },
            async applyFilter() {
                this.loading = true;
                const range = this.computeRange();
                try {
                    await Promise.all([
                        this.loadSeries(range),
                        this.loadPriceViews(range),
                        this.loadProfileViews(range),
                        this.loadContactViews(range)
                    ]);
                } finally {
                    this.loading = false;
                    this.$nextTick(() => { if (window.lucide && window.lucide.createIcons) window.lucide.createIcons() });
                }
            },
            refreshData() { this.applyFilter() },
            computeRange() {
                if (this.granularity === 'daily') {
                    const dt = new Date(this.dateDaily || this.toYMD(new Date()));
                    return { from: `${this.toYMD(dt)} 00:00:00`, to: `${this.toYMD(dt)} 23:59:59` };
                }
                if (this.granularity === 'weekly') {
                    const start = this.startOfWeekSunday(new Date(this.dateWeekly || this.toYMD(new Date())));
                    const end = this.endOfWeekSaturday(start);
                    return { from: `${this.toYMD(start)} 00:00:00`, to: `${this.toYMD(end)} 23:59:59` };
                }
                if (this.granularity === 'monthly') {
                    const parts = (this.dateMonthly || `${this.y(new Date().getFullYear())}-${this.m(new Date().getMonth() + 1)}`).split('-');
                    const dt = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, 1);
                    const s = this.startOfMonth(dt);
                    const e = this.endOfMonth(dt);
                    return { from: `${this.toYMD(s)} 00:00:00`, to: `${this.toYMD(e)} 23:59:59` };
                }
                if (this.granularity === 'yearly') {
                    const y = parseInt(this.dateYearly || String(new Date().getFullYear()));
                    const s = new Date(y, 0, 1);
                    const e = new Date(y, 11, 31);
                    return { from: `${this.toYMD(s)} 00:00:00`, to: `${this.toYMD(e)} 23:59:59` };
                }
                const dt = new Date();
                return { from: `${this.toYMD(dt)} 00:00:00`, to: `${this.toYMD(dt)} 23:59:59` };
            },
            async loadSeries(range) {
                const url = new URL(this.API_URL);
                url.searchParams.set('action', 'series');
                url.searchParams.set('from', range.from);
                url.searchParams.set('to', range.to);
                url.searchParams.set('granularity', this.granularity);
                const res = await fetch(url.toString(), { cache: 'no-store' });
                const json = await res.json().catch(() => ({ success: false }));
                const labels = Array.isArray(json?.data?.labels) ? json.data.labels : [];
                const profile = Array.isArray(json?.data?.profile) ? json.data.profile : [];
                const price = Array.isArray(json?.data?.price) ? json.data.price : [];
                const contact = Array.isArray(json?.data?.contact) ? json.data.contact : [];
                const breakdown = json?.data?.contact_breakdown || {};
                this.summaryStats.profile = profile.reduce((a, b) => a + Number(b || 0), 0);
                this.summaryStats.price = price.reduce((a, b) => a + Number(b || 0), 0);
                this.summaryStats.contact = contact.reduce((a, b) => a + Number(b || 0), 0);
                this.$nextTick(() => {
                    this.renderCombinedChart(labels, profile, price, contact);
                    this.renderContactBreakdown(breakdown);
                });
            },
            teardownCharts() {
                try { if (this.charts.combined) { this.charts.combined.destroy(); this.charts.combined = null; } } catch (e) { }
                try { if (this.charts.contactBar) { this.charts.contactBar.destroy(); this.charts.contactBar = null; } } catch (e) { }
            },
            renderCombinedChart(labels, profile, price, contact) {
                if (!window.Chart) return;
                const canvas = this.$refs.combinedChart || document.getElementById('combinedChart');
                if (!canvas) return;
                const existing = Chart.getChart(canvas);
                const redBorder = 'rgba(248,113,113,1)';
                const redFill = 'rgba(248,113,113,0.12)';
                const blueBorder = 'rgba(96,165,250,1)';
                const blueFill = 'rgba(96,165,250,0.12)';
                const greenBorder = 'rgba(74,222,128,1)';
                const greenFill = 'rgba(74,222,128,0.12)';
                const cfgData = {
                    labels: labels,
                    datasets: [
                        { label: 'Profile Views', data: profile, borderColor: redBorder, backgroundColor: redFill, tension: .4, fill: true },
                        { label: 'Price Views', data: price, borderColor: blueBorder, backgroundColor: blueFill, tension: .4, fill: true },
                        { label: 'Contact Views', data: contact, borderColor: greenBorder, backgroundColor: greenFill, tension: .4, fill: true }
                    ]
                };
                const cfgOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: true, position: 'bottom' }, tooltip: { enabled: true, backgroundColor: 'rgba(0,0,0,.8)', padding: 12, cornerRadius: 8 } }
                };
                if (existing) {
                    existing.config.data = cfgData;
                    existing.options = cfgOptions;
                    existing.update('none');
                    this.charts.combined = existing;
                    return;
                }
                this.charts.combined = new Chart(canvas, { type: 'line', data: cfgData, options: cfgOptions });
            },
            renderContactBreakdown(breakdown) {
                if (!window.Chart) return;
                const canvas = this.$refs.contactBarChart || document.getElementById('contactBarChart');
                if (!canvas) return;
                const existing = Chart.getChart(canvas);
                const vals = [
                    Number(breakdown.location || 0),
                    Number(breakdown.contact || 0),
                    Number(breakdown.email || 0)
                ];
                const colors = [
                    'rgba(248,113,113,0.75)',
                    'rgba(96,165,250,0.75)',
                    'rgba(74,222,128,0.75)'
                ];
                const cfgData = { labels: ['Location', 'Phone', 'Email'], datasets: [{ label: 'Views', data: vals, backgroundColor: colors, borderRadius: 8 }] };
                const cfgOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,.8)', padding: 12, cornerRadius: 8 } }
                };
                if (existing) {
                    existing.config.data = cfgData;
                    existing.options = cfgOptions;
                    existing.update('none');
                    this.charts.contactBar = existing;
                    return;
                }
                this.charts.contactBar = new Chart(canvas, { type: 'bar', data: cfgData, options: cfgOptions });
            },
            async loadProfileViews(range) {
                const body = document.getElementById('profileTableBody');
                if (!body) return;
                body.innerHTML = `<tr><td colspan="2" class="px-3 md:px-4 py-8 text-center text-gray-500"><div class="inline-flex items-center gap-2"><span>Loading...</span></div></td></tr>`;
                try {
                    const url = new URL(this.API_URL);
                    url.searchParams.set('action', 'list_profile_views');
                    url.searchParams.set('from', range.from);
                    url.searchParams.set('to', range.to);
                    const res = await fetch(url.toString(), { cache: 'no-store' });
                    const json = await res.json();
                    if (!json.success) {
                        body.innerHTML = '<tr><td colspan="2" class="px-3 md:px-4 py-8 text-center text-red-600">Failed to load data</td></tr>';
                        document.getElementById('profileCount').textContent = '';
                        return;
                    }
                    const rows = json.data || [];
                    if (!rows.length) {
                        body.innerHTML = '<tr><td colspan="2" class="px-3 md:px-4 py-8 text-center text-gray-500">No profile views in this period</td></tr>';
                        document.getElementById('profileCount').textContent = '';
                        return;
                    }
                    body.innerHTML = rows.map(r => {
                        const username = r.username ? this.escapeHtml(r.username) : '-';
                        const fullname = r.full_name ? this.escapeHtml(r.full_name) : '-';
                        return `<tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 md:px-4 py-3"><div class="font-medium text-gray-900">${fullname}</div><div class="text-xs text-gray-500">${username}</div></td>
                            <td class="px-3 md:px-4 py-3"><div class="text-sm text-gray-900">${this.escapeHtml(r.date)}</div><div class="text-xs text-gray-500">${this.escapeHtml(r.time)}</div></td>
                        </tr>`;
                    }).join('');
                    document.getElementById('profileCount').textContent = `${rows.length} record(s)`;
                } catch (e) {
                    body.innerHTML = '<tr><td colspan="2" class="px-3 md:px-4 py-8 text-center text-red-600">Error loading data</td></tr>';
                    document.getElementById('profileCount').textContent = '';
                }
            },
            async loadPriceViews(range) {
                const body = document.getElementById('priceTableBody');
                if (!body) return;
                body.innerHTML = `<tr><td colspan="5" class="px-3 md:px-4 py-8 text-center text-gray-500"><div class="inline-flex items-center gap-2"><span>Loading...</span></div></td></tr>`;
                try {
                    const url = new URL(this.API_URL);
                    url.searchParams.set('action', 'list_price_views');
                    url.searchParams.set('from', range.from);
                    url.searchParams.set('to', range.to);
                    const res = await fetch(url.toString(), { cache: 'no-store' });
                    const json = await res.json();
                    if (!json.success) {
                        body.innerHTML = '<tr><td colspan="5" class="px-3 md:px-4 py-8 text-center text-red-600">Failed to load data</td></tr>';
                        document.getElementById('priceCount').textContent = '';
                        return;
                    }
                    const rows = json.data || [];
                    if (!rows.length) {
                        body.innerHTML = '<tr><td colspan="5" class="px-3 md:px-4 py-8 text-center text-gray-500">No price views in this period</td></tr>';
                        document.getElementById('priceCount').textContent = '';
                        return;
                    }
                    body.innerHTML = rows.map(r => {
                        const username = r.username ? this.escapeHtml(r.username) : '-';
                        const fullname = r.full_name ? this.escapeHtml(r.full_name) : '-';
                        const pkg = r.package_html ? `<span class="pkg">${r.package_html}</span>` : (r.package ? this.escapeHtml(r.package) : '-');
                        const price = this.fmtPrice(r.price);
                        const cat = r.price_category ? this.escapeHtml(r.price_category) : '-';
                        return `<tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 md:px-4 py-3"><div class="font-medium text-gray-900 text-sm">${this.escapeHtml(r.product_title)}</div></td>
                            <td class="px-3 md:px-4 py-3 hidden md:table-cell"><div class="text-sm text-gray-700">${pkg}</div></td>
                            <td class="px-3 md:px-4 py-3"><div class="font-medium text-gray-900 text-sm">${price}</div><div class="text-[10px] uppercase text-gray-500">${cat}</div></td>
                            <td class="px-3 md:px-4 py-3 hidden lg:table-cell"><div class="text-sm text-gray-900">${fullname}</div><div class="text-xs text-gray-500">${username}</div></td>
                            <td class="px-3 md:px-4 py-3"><div class="text-sm text-gray-900">${this.escapeHtml(r.date)}</div><div class="text-xs text-gray-500">${this.escapeHtml(r.time)}</div></td>
                        </tr>`;
                    }).join('');
                    document.getElementById('priceCount').textContent = `${rows.length} record(s)`;
                } catch (e) {
                    body.innerHTML = '<tr><td colspan="5" class="px-3 md:px-4 py-8 text-center text-red-600">Error loading data</td></tr>';
                    document.getElementById('priceCount').textContent = '';
                }
            },
            async loadContactViews(range) {
                const body = document.getElementById('contactTableBody');
                if (!body) return;
                body.innerHTML = `<tr><td colspan="3" class="px-3 md:px-4 py-8 text-center text-gray-500"><div class="inline-flex items-center gap-2"><span>Loading...</span></div></td></tr>`;
                try {
                    const url = new URL(this.API_URL);
                    url.searchParams.set('action', 'list_contact_views');
                    url.searchParams.set('from', range.from);
                    url.searchParams.set('to', range.to);
                    const res = await fetch(url.toString(), { cache: 'no-store' });
                    const json = await res.json();
                    if (!json.success) {
                        body.innerHTML = '<tr><td colspan="3" class="px-3 md:px-4 py-8 text-center text-red-600">Failed to load data</td></tr>';
                        document.getElementById('contactCount').textContent = '';
                        return;
                    }
                    const rows = json.data || [];
                    if (!rows.length) {
                        body.innerHTML = '<tr><td colspan="3" class="px-3 md:px-4 py-8 text-center text-gray-500">No contact views in this period</td></tr>';
                        document.getElementById('contactCount').textContent = '';
                        return;
                    }
                    body.innerHTML = rows.map(r => {
                        const username = r.username ? this.escapeHtml(r.username) : '-';
                        const fullname = r.full_name ? this.escapeHtml(r.full_name) : '-';
                        const entity = r.entity || '-';
                        return `<tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 md:px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-user-primary/10 text-gray-700">${this.escapeHtml(entity)}</span></td>
                            <td class="px-3 md:px-4 py-3"><div class="font-medium text-gray-900 text-sm">${fullname}</div><div class="text-xs text-gray-500">${username}</div></td>
                            <td class="px-3 md:px-4 py-3"><div class="text-sm text-gray-900">${this.escapeHtml(r.date)}</div><div class="text-xs text-gray-500">${this.escapeHtml(r.time)}</div></td>
                        </tr>`;
                    }).join('');
                    document.getElementById('contactCount').textContent = `${rows.length} record(s)`;
                } catch (e) {
                    body.innerHTML = '<tr><td colspan="3" class="px-3 md:px-4 py-8 text-center text-red-600">Error loading data</td></tr>';
                    document.getElementById('contactCount').textContent = '';
                }
            },
            y(n) { return n.toString().padStart(4, '0') },
            m(n) { return n.toString().padStart(2, '0') },
            d(n) { return n.toString().padStart(2, '0') },
            toYMD(dt) { return `${this.y(dt.getFullYear())}-${this.m(dt.getMonth() + 1)}-${this.d(dt.getDate())}` },
            startOfWeekSunday(dt) { const t = new Date(dt.getFullYear(), dt.getMonth(), dt.getDate()); const day = t.getDay(); t.setDate(t.getDate() - day); return t },
            endOfWeekSaturday(dt) { const s = this.startOfWeekSunday(dt); return new Date(s.getFullYear(), s.getMonth(), s.getDate() + 6) },
            startOfMonth(dt) { return new Date(dt.getFullYear(), dt.getMonth(), 1) },
            endOfMonth(dt) { return new Date(dt.getFullYear(), dt.getMonth() + 1, 0) },
            escapeHtml(str) { if (!str) return ''; return str.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m])) },
            fmtPrice(n) { if (n === null || n === undefined) return '-'; try { return 'UGX ' + new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(Number(n)) } catch (e) { return 'UGX ' + n } }
        }
    }
    document.addEventListener('DOMContentLoaded', function () { if (window.lucide && window.lucide.createIcons) { window.lucide.createIcons() } });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
