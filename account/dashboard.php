<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

// Get user details
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
$formattedLastLogin = $lastLogin
    ? date('M d, Y g:i A', strtotime($lastLogin))
    : 'First login';

$fullName = trim(($userDetails['first_name'] ?? '') . ' ' . ($userDetails['last_name'] ?? ''));
$displayName = $fullName ?: $_SESSION['user']['username'];

// Get active categories with products
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

<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Quotations Card -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full"
                    id="quotationStatus">Loading...</span>
            </div>
            <div class="space-y-2">
                <div class="text-2xl font-bold text-secondary" id="totalQuotations">0</div>
                <div class="text-sm text-gray-text">Total Quotations</div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="text-green-600">✓ <span id="processedQuotations">0</span> Processed</span>
                    <span class="text-yellow-600">⏳ <span id="pendingQuotations">0</span> Pending</span>
                </div>
            </div>
        </div>

        <!-- Wallet Balance Card -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-green-600 text-xl"></i>
                </div>
                <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded-full">Active</span>
            </div>
            <div class="space-y-2">
                <div class="text-2xl font-bold text-secondary">UGX <span id="walletBalance">0</span></div>
                <div class="text-sm text-gray-text">Wallet Balance</div>
                <div class="text-xs text-gray-500">
                    SMS Credits: <span id="smsBalance" class="font-medium">0</span>
                </div>
            </div>
        </div>

        <!-- Stores Card -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
                <span class="text-xs bg-purple-100 text-purple-600 px-2 py-1 rounded-full"
                    id="storeStatus">Loading...</span>
            </div>
            <div class="space-y-2">
                <div class="text-2xl font-bold text-secondary" id="totalStores">0</div>
                <div class="text-sm text-gray-text">My Stores</div>
                <div class="text-xs text-gray-500">
                    Products: <span id="totalProducts" class="font-medium">0</span>
                </div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <span class="text-xs bg-orange-100 text-orange-600 px-2 py-1 rounded-full">This Month</span>
            </div>
            <div class="space-y-2">
                <div class="text-2xl font-bold text-secondary">UGX <span id="monthlyTransactions">0</span></div>
                <div class="text-sm text-gray-text">Monthly Transactions</div>
                <div class="text-xs text-gray-500">
                    Total: <span id="totalTransactionAmount" class="font-medium">UGX 0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-user-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bolt text-user-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary">Quick Actions</h3>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="<?= BASE_URL ?>request-for-quote"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-plus text-blue-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">New Quote</span>
                </a>

                <a href="quotations"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-file-invoice text-green-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">My Quotes</span>
                </a>

                <a href="zzimba-credit"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-credit-card text-purple-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Top Up</span>
                </a>

                <a href="zzimba-stores"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-store text-red-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">My Stores</span>
                </a>

                <a href="profile"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-user text-indigo-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Profile</span>
                </a>

                <a href="order-history"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-history text-yellow-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Orders</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Browse Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-user-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-user-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary">Recent Activity</h3>
                    </div>
                    <button onclick="refreshActivity()" class="text-sm text-user-primary hover:text-user-primary/80">
                        <i class="fas fa-refresh mr-1"></i>Refresh
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="recentActivity" class="space-y-4">
                    <div class="flex items-center justify-center py-8">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-user-primary mb-2"></i>
                            <p class="text-gray-500">Loading recent activity...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Browse Categories -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-user-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-th-large text-user-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary">Browse Categories</h3>
                    </div>
                    <a href="<?= BASE_URL ?>materials-yard"
                        class="text-sm text-user-primary hover:text-user-primary/80">
                        View All
                    </a>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-3">
                    <?php foreach ($categories as $cat): ?>
                        <?php $img = getCategoryImageUrl($cat['id']); ?>
                        <a href="<?= BASE_URL ?>view/category/<?= htmlspecialchars($cat['id']) ?>" target="_blank"
                            class="flex items-center gap-4 p-3 rounded-lg border border-gray-100 hover:border-user-primary hover:shadow-sm transition-all duration-200 bg-gray-50 hover:bg-user-primary/5">
                            <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"
                                    class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-secondary"><?= htmlspecialchars($cat['name']) ?></h4>
                                <p class="text-sm text-gray-text"><?= (int) $cat['products'] ?> Products</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Loading dashboard data...</p>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>account/fetch/manageDashboard.php';
    let refreshInterval;

    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardData();
        loadRecentActivity();

        // Auto-refresh every 5 minutes
        refreshInterval = setInterval(loadDashboardData, 300000);
    });

    async function loadDashboardData() {
        try {
            const response = await fetch(`${API_URL}?action=getDashboardStats`);
            const data = await response.json();

            if (data.success) {
                updateDashboardStats(data.stats);
            } else {
                console.error('Failed to load dashboard data:', data.message);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    function updateDashboardStats(stats) {
        // Update quotations
        document.getElementById('totalQuotations').textContent = stats.quotations.total;
        document.getElementById('processedQuotations').textContent = stats.quotations.processed;
        document.getElementById('pendingQuotations').textContent = stats.quotations.processing + stats.quotations.new;

        const quotationStatus = stats.quotations.total > 0 ?
            (stats.quotations.processing > 0 ? 'Active' : 'Complete') : 'None';
        document.getElementById('quotationStatus').textContent = quotationStatus;

        // Update wallet
        document.getElementById('walletBalance').textContent = formatCurrency(stats.wallet.main_balance);
        document.getElementById('smsBalance').textContent = formatCurrency(stats.wallet.sms_balance);

        // Update stores
        document.getElementById('totalStores').textContent = stats.stores.total_stores;
        document.getElementById('totalProducts').textContent = stats.stores.total_products;

        const storeStatus = stats.stores.active_stores > 0 ? 'Active' :
            (stats.stores.pending_stores > 0 ? 'Pending' : 'None');
        document.getElementById('storeStatus').textContent = storeStatus;

        // Update transactions
        document.getElementById('monthlyTransactions').textContent = formatCurrency(stats.transactions.this_month_amount);
        document.getElementById('totalTransactionAmount').textContent = formatCurrency(stats.transactions.total_amount);
    }

    async function loadRecentActivity() {
        try {
            const response = await fetch(`${API_URL}?action=getRecentActivity`);
            const data = await response.json();

            if (data.success) {
                updateRecentActivity(data.activities);
            } else {
                console.error('Failed to load recent activity:', data.message);
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
        }
    }

    function updateRecentActivity(activities) {
        const container = document.getElementById('recentActivity');

        if (!activities || activities.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500">No recent activity</p>
                </div>
            `;
            return;
        }

        container.innerHTML = activities.map(activity => {
            const icon = activity.type === 'quotation' ? 'fa-file-invoice' : 'fa-credit-card';
            const color = activity.type === 'quotation' ? 'blue' : 'green';
            const statusColor = getStatusColor(activity.status);

            return `
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-10 h-10 bg-${color}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas ${icon} text-${color}-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">${activity.description}</p>
                        <p class="text-sm text-gray-500">${formatDateTime(activity.created_at)}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                        ${activity.status}
                    </span>
                </div>
            `;
        }).join('');
    }

    function getStatusColor(status) {
        const colors = {
            'New': 'bg-blue-100 text-blue-800',
            'Processing': 'bg-yellow-100 text-yellow-800',
            'Processed': 'bg-green-100 text-green-800',
            'Paid': 'bg-purple-100 text-purple-800',
            'SUCCESS': 'bg-green-100 text-green-800',
            'PENDING': 'bg-yellow-100 text-yellow-800',
            'FAILED': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount || 0);
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);

        if (diffInHours < 24) {
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        } else {
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric'
            });
        }
    }

    function refreshActivity() {
        loadRecentActivity();

        // Show brief loading state
        const button = event.target.closest('button');
        const icon = button.querySelector('i');
        icon.classList.add('fa-spin');

        setTimeout(() => {
            icon.classList.remove('fa-spin');
        }, 1000);
    }

    // Cleanup interval on page unload
    window.addEventListener('beforeunload', function () {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>