<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$stmt = $pdo->prepare("
    SELECT 
        first_name,
        last_name,
        email,
        phone,
        last_login,
        created_at
    FROM zzimba_users
    WHERE id = :user_id
");
$stmt->bindParam(':user_id', $_SESSION['user']['user_id'], PDO::PARAM_STR);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
$lastLogin = $userDetails['last_login'] ?? '';
$formattedLastLogin = $lastLogin ? date('M d, Y g:i A', strtotime($lastLogin)) : 'First login';
$fullName = trim(($userDetails['first_name'] ?? '') . ' ' . ($userDetails['last_name'] ?? ''));
$displayName = $fullName ?: $_SESSION['user']['username'];
$stmt = $pdo->prepare("
    SELECT
        c.id,
        c.name,
        COUNT(p.id) AS products
    FROM product_categories c
    INNER JOIN products p ON c.id = p.category_id
    WHERE c.status = 'active' AND p.status = 'published'
    GROUP BY c.id, c.name
    ORDER BY c.name
    LIMIT 6
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
function getCategoryImageUrl(string $categoryId): string
{
    $dir = __DIR__ . '/../img/product-categories/' . $categoryId . '/';
    $webBase = BASE_URL . 'img/product-categories/' . $categoryId . '/';
    if (is_dir($dir)) {
        $files = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return $webBase . basename($files[0]);
        }
    }
    return 'https://placehold.co/80x80/f0f0f0/808080?text=Category';
}
ob_start();
?>
<div x-data="dashboard()" x-init="init()" class="space-y-6 text-gray-900 dark:text-white">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div
            class="relative bg-white dark:bg-secondary rounded-xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-white/10 hover:shadow-md transition-shadow">
            <div class="space-y-1">
                <div class="text-2xl font-bold text-secondary dark:text-white" x-text="fmt(stats.quotations.total)">
                </div>
                <div class="text-sm text-gray-600 dark:text-white/70">Total RFQs</div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="text-green-600 dark:text-green-400">✓ <span
                            x-text="fmt(stats.quotations.processed)"></span> Processed</span>
                    <span class="text-yellow-600 dark:text-yellow-300">⏳ <span
                            x-text="fmt(stats.quotations.processing + stats.quotations.new)"></span> Pending</span>
                </div>
            </div>
            <i data-lucide="file-text"
                class="absolute bottom-3 right-3 w-10 h-10 text-blue-600/20 dark:text-white/20"></i>
        </div>
        <div
            class="relative bg-white dark:bg-secondary rounded-xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-white/10 hover:shadow-md transition-shadow">
            <div class="space-y-1">
                <div class="text-2xl font-bold text-secondary dark:text-white">UGX <span
                        x-text="fmt(stats.wallet.main_balance)"></span></div>
                <div class="text-sm text-gray-600 dark:text-white/70">Wallet Balance</div>
                <div class="text-xs text-gray-500 dark:text-white/70">SMS Credits: <span class="font-medium"
                        x-text="fmt(stats.wallet.sms_balance)"></span></div>
            </div>
            <i data-lucide="wallet"
                class="absolute bottom-3 right-3 w-10 h-10 text-green-600/20 dark:text-white/20"></i>
        </div>
        <div
            class="relative bg-white dark:bg-secondary rounded-xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-white/10 hover:shadow-md transition-shadow">
            <div class="space-y-1">
                <div class="text-2xl font-bold text-secondary dark:text-white" x-text="fmt(stats.stores.total_stores)">
                </div>
                <div class="text-sm text-gray-600 dark:text-white/70">My Stores</div>
                <div class="text-xs text-gray-500 dark:text-white/70">Products: <span class="font-medium"
                        x-text="fmt(stats.stores.total_products)"></span></div>
            </div>
            <i data-lucide="store"
                class="absolute bottom-3 right-3 w-10 h-10 text-purple-600/20 dark:text-white/20"></i>
        </div>
        <div
            class="relative bg-white dark:bg-secondary rounded-xl p-5 sm:p-6 shadow-sm border border-gray-100 dark:border-white/10 hover:shadow-md transition-shadow">
            <div class="space-y-1">
                <div class="text-2xl font-bold text-secondary dark:text-white">UGX <span
                        x-text="fmt(stats.transactions.this_month_amount)"></span></div>
                <div class="text-sm text-gray-600 dark:text-white/70">Monthly Transactions</div>
                <div class="text-xs text-gray-500 dark:text-white/70">Total: <span class="font-medium"
                        x-text="'UGX ' + fmt(stats.transactions.total_amount)"></span></div>
            </div>
            <i data-lucide="line-chart"
                class="absolute bottom-3 right-3 w-10 h-10 text-orange-600/20 dark:text-white/20"></i>
        </div>
    </div>

    <div class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10">
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                <a href="<?= BASE_URL ?>request-for-quote"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-blue-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="plus"
                            class="w-6 h-6 text-blue-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">New RFQ</span>
                </a>
                <a href="quotations"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-green-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="file-text"
                            class="w-6 h-6 text-green-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">My RFQs</span>
                </a>
                <a href="buy-in-store"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-teal-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="shopping-cart"
                            class="w-6 h-6 text-teal-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Buy in Store Sent</span>
                </a>
                <a href="zzimba-credit"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-purple-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="credit-card"
                            class="w-6 h-6 text-purple-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Top Up</span>
                </a>
                <a href="zzimba-stores"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-red-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="store"
                            class="w-6 h-6 text-red-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">My Stores</span>
                </a>
                <a href="profile"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 dark:border-white/10 hover:border-user-primary/60 hover:bg-user-primary/5 dark:hover:bg-white/5 transition-all group">
                    <div
                        class="w-12 h-12 rounded-xl grid place-items-center mb-3 bg-indigo-100 dark:bg-white/10 group-hover:bg-user-primary/10">
                        <i data-lucide="user"
                            class="w-6 h-6 text-indigo-600 dark:text-white group-hover:text-user-primary"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 dark:text-white text-center">Profile</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg grid place-items-center bg-user-primary/10">
                            <i data-lucide="clock" class="w-5 h-5 text-user-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary dark:text-white">Recent Activity</h3>
                    </div>
                    <button @click="refreshActivity"
                        class="text-sm text-user-primary hover:text-user-primary/80 flex items-center">
                        <i data-lucide="refresh-ccw" :class="{'animate-spin': refreshing}"
                            class="w-4 h-4 mr-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div x-show="loadingActivity" class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <i data-lucide="loader" class="w-6 h-6 text-user-primary mx-auto mb-2 animate-spin"></i>
                        <p class="text-gray-500 dark:text-white/70">Loading recent activity...</p>
                    </div>
                </div>
                <div x-show="!loadingActivity" class="space-y-4">
                    <template x-if="activities.length === 0">
                        <div class="text-center py-8">
                            <div
                                class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-full grid place-items-center mx-auto mb-4">
                                <i data-lucide="history" class="w-6 h-6 text-gray-400 dark:text-white/60"></i>
                            </div>
                            <p class="text-gray-500 dark:text-white/70">No recent activity</p>
                        </div>
                    </template>
                    <template x-for="a in activities" :key="a.id || (a.type + a.created_at)">
                        <div
                            class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-white/5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <div class="w-10 h-10 rounded-lg grid place-items-center"
                                :class="a.type==='quotation' ? 'bg-blue-100 dark:bg-white/10' : 'bg-green-100 dark:bg-white/10'">
                                <i :data-lucide="a.type==='quotation' ? 'file-text' : 'credit-card'" class="w-5 h-5"
                                    :class="a.type==='quotation' ? 'text-blue-600 dark:text-white' : 'text-green-600 dark:text-white'"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white truncate" x-text="a.description">
                                </p>
                                <p class="text-sm text-gray-500 dark:text-white/70" x-text="formatWhen(a.created_at)">
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                :class="statusPill(a.status)" x-text="a.status"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg grid place-items-center bg-user-primary/10">
                            <i data-lucide="grid" class="w-5 h-5 text-user-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary dark:text-white">Browse Categories</h3>
                    </div>
                    <a href="<?= BASE_URL ?>materials-yard"
                        class="text-sm text-user-primary hover:text-user-primary/80">View All</a>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div class="space-y-3">
                    <?php foreach ($categories as $cat): ?>
                        <?php $img = getCategoryImageUrl($cat['id']); ?>
                        <a href="<?= BASE_URL ?>view/category/<?= htmlspecialchars($cat['id']) ?>" target="_blank"
                            class="flex items-center gap-4 p-3 rounded-lg border border-gray-100 dark:border-white/10 hover:border-user-primary hover:shadow-sm transition-all bg-gray-50 dark:bg-white/5 hover:bg-user-primary/5 dark:hover:bg-white/10">
                            <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-secondary dark:text-white"><?= htmlspecialchars($cat['name']) ?>
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-white/70"><?= (int) $cat['products'] ?> Products
                                </p>
                            </div>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400 dark:text-white/50"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div x-show="loading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-secondary p-6 rounded-lg shadow-lg flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4">
            </div>
            <p class="text-gray-700 dark:text-white/80">Loading dashboard data...</p>
        </div>
    </div>
</div>

<script>
    function dashboard() {
        return {
            api: '<?= BASE_URL ?>account/fetch/manageDashboard.php',
            stats: { quotations: { total: 0, processed: 0, processing: 0, new: 0 }, wallet: { main_balance: 0, sms_balance: 0 }, stores: { total_stores: 0, total_products: 0 }, transactions: { this_month_amount: 0, total_amount: 0 } },
            activities: [],
            loading: false,
            loadingActivity: true,
            refreshing: false,
            init() {
                this.fetchStats();
                this.fetchActivities();
                this.$nextTick(() => this.renderIcons());
            },
            async fetchStats() {
                try {
                    this.loading = true;
                    const r = await fetch(`${this.api}?action=getDashboardStats`, { cache: 'no-store' });
                    const d = await r.json();
                    if (d && d.success) this.stats = d.stats || this.stats;
                } catch (e) { } finally { this.loading = false; this.$nextTick(() => this.renderIcons()); }
            },
            async fetchActivities() {
                try {
                    this.loadingActivity = true;
                    const r = await fetch(`${this.api}?action=getRecentActivity`, { cache: 'no-store' });
                    const d = await r.json();
                    if (d && d.success) this.activities = Array.isArray(d.activities) ? d.activities : [];
                } catch (e) { this.activities = []; }
                finally { this.loadingActivity = false; this.$nextTick(() => this.renderIcons()); }
            },
            refreshActivity() {
                this.refreshing = true;
                this.fetchActivities().finally(() => setTimeout(() => { this.refreshing = false; this.$nextTick(() => this.renderIcons()); }, 600));
            },
            renderIcons() {
                requestAnimationFrame(() => { if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons(); });
            },
            fmt(n) {
                const v = Number(n || 0);
                return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(v);
            },
            formatWhen(s) {
                const d = new Date((s || '').replace(' ', 'T'));
                const now = new Date();
                const diffH = (now - d) / 36e5;
                if (isNaN(d.getTime())) return '';
                return diffH < 24 ? d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : d.toLocaleDateString([], { month: 'short', day: 'numeric' });
            },
            statusPill(st) {
                const m = {
                    'New': 'bg-blue-100 text-blue-800 dark:text-blue-200',
                    'Processing': 'bg-yellow-100 text-yellow-800 dark:text-yellow-200',
                    'Processed': 'bg-green-100 text-green-800 dark:text-green-200',
                    'Paid': 'bg-purple-100 text-purple-800 dark:text-purple-200',
                    'SUCCESS': 'bg-green-100 text-green-800 dark:text-green-200',
                    'PENDING': 'bg-yellow-100 text-yellow-800 dark:text-yellow-200',
                    'FAILED': 'bg-red-100 text-red-800 dark:text-red-200'
                };
                return m[st] || 'bg-gray-100 text-gray-800 dark:text-gray-200';
            }
        }
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>