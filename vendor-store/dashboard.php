<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
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
$storeName = $store['name'];
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
$title = isset($pageTitle) ? "{$pageTitle} - {$storeName} | Store Dashboard" : "{$storeName} Store Dashboard";
$activeNav = $activeNav ?? 'dashboard';
$userName = $_SESSION['user']['username'];
$storeInitials = '';
$parts = array_filter(explode(' ', $storeName));
$limitedParts = array_slice($parts, 0, 2);
foreach ($limitedParts as $part) {
    $storeInitials .= strtoupper($part[0]);
}
$sessionUlid = generateUlid();
ob_start();
?>

<div class="space-y-6" id="app-container">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="<?= BASE_URL ?>vendor-store/buy-in-store"
            class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
                </div>
                <span class="text-xs px-2 py-1 rounded-full" id="requestsStatus">—</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary" id="pendingRequests">0</div>
                <div class="text-sm text-gray-text">Pending Requests</div>
                <div class="grid grid-cols-3 gap-2 text-xs mt-2">
                    <div class="text-blue-700">New: <span id="requestsNew">0</span></div>
                    <div class="text-orange-700">Confirmed: <span id="requestsConfirmed">0</span></div>
                    <div class="text-green-700">Completed: <span id="requestsCompleted">0</span></div>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>vendor-store/products"
            class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box-open text-red-600 text-xl"></i>
                </div>
                <span class="text-xs px-2 py-1 rounded-full" id="productsStatus">—</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary" id="totalProducts">0</div>
                <div class="text-sm text-gray-text">Products</div>
                <div class="grid grid-cols-2 gap-2 text-xs mt-2">
                    <div class="text-green-700">Active: <span id="activeProducts">0</span></div>
                    <div class="text-gray-700">Inactive: <span id="inactiveProducts">0</span></div>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>vendor-store/managers"
            class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <span class="text-xs px-2 py-1 rounded-full" id="managersStatus">—</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary" id="totalManagers">0</div>
                <div class="text-sm text-gray-text">Store Managers</div>
                <div class="text-xs text-gray-500">Active & Approved</div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>vendor-store/zzimba-credit"
            class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-indigo-600 text-xl"></i>
                </div>
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">Active</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary">UGX <span id="vendorWalletBalance">0</span></div>
                <div class="text-sm text-gray-text">Zzimba Credit</div>
                <div class="text-xs text-gray-500">SMS Credits: <span id="vendorSmsBalance" class="font-medium">0</span>
                </div>
            </div>
        </a>

        <a href="<?= BASE_URL ?>vendor-store/zzimba-credit#transactions"
            class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full">This Month</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary">UGX <span id="vendorMonthTotal">0</span></div>
                <div class="text-sm text-gray-text">Total Transactions</div>
                <div class="text-xs text-gray-500">Credits: UGX <span id="vendorMonthCredits">0</span> • Debits: UGX
                    <span id="vendorMonthDebits">0</span>
                </div>
            </div>
        </a>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-eye text-blue-600 text-xl"></i>
                </div>
                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Monthly</span>
            </div>
            <div class="space-y-2">
                <div class="text-3xl font-bold text-secondary" id="monthlyStoreViews">0</div>
                <div class="text-sm text-gray-text">Store Profile Views</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-user-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-user-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary"><?= htmlspecialchars($storeName) ?> • Store
                        Overview</h3>
                </div>
                <a href="<?= BASE_URL ?>view/profile/vendor/<?= htmlspecialchars($storeId) ?>" target="_blank"
                    class="h-9 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-2 text-sm">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="hidden sm:inline">View Public Profile</span>
                </a>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <a href="<?= BASE_URL ?>vendor-store/buy-in-store"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-calendar-check text-yellow-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Visit Requests</span>
                </a>
                <a href="<?= BASE_URL ?>vendor-store/orders"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-shopping-bag text-blue-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Orders</span>
                </a>
                <a href="<?= BASE_URL ?>vendor-store/products"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-box text-red-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Products</span>
                </a>
                <a href="<?= BASE_URL ?>vendor-store/zzimba-credit"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-credit-card text-purple-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Zzimba Credit</span>
                </a>
                <a href="<?= BASE_URL ?>vendor-store/sms-center"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-comment-dots text-green-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">SMS Center</span>
                </a>
                <a href="<?= BASE_URL ?>vendor-store/store-profile"
                    class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-user-primary/50 hover:bg-user-primary/5 transition-all duration-200 group">
                    <div
                        class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-user-primary/10 transition-colors">
                        <i class="fas fa-store text-indigo-600 group-hover:text-user-primary text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-900 text-center">Store Profile</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-user-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-fire text-user-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary">Top Products by Views</h3>
                    </div>
                    <a href="<?= BASE_URL ?>vendor-store/products"
                        class="text-sm text-user-primary hover:text-user-primary/80">Manage</a>
                </div>
            </div>
            <div class="p-6">
                <div id="topProductsList" class="space-y-3">
                    <div class="text-center py-6 text-gray-500">Loading products…</div>
                </div>
            </div>
        </div>
    </div>

    <div id="tokenModal" class="fixed inset-0 z-50 hidden !m-0">
        <div class="absolute inset-0 bg-black/20" onclick="hideModal('tokenModal')"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-secondary">Enter Token No</h3>
                    <button onclick="hideModal('tokenModal')" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="tokenMessage" class="mb-4 text-center"></div>
                <form id="tokenForm">
                    <div class="mb-4">
                        <input type="text" id="token" placeholder="Token No"
                            class="w-full h-12 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg">
                    </div>
                    <button type="submit"
                        class="w-full h-12 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors text-lg">Redeem
                        Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Loading dashboard data...</p>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>vendor-store/fetch/manageDashboard.php';
    const STORE_ID = '<?= htmlspecialchars($storeId) ?>';
    let refreshInterval;

    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardData();
        loadRecentActivity();
        refreshInterval = setInterval(loadDashboardData, 300000);
        document.getElementById('tokenForm').addEventListener('submit', function (e) {
            e.preventDefault();
            hideModal('tokenModal');
            alert('Token redeemed successfully!');
        });
    });

    async function loadDashboardData() {
        try {
            showLoading();
            const response = await fetch(`${API_URL}?action=getDashboardStats&store_id=${encodeURIComponent(STORE_ID)}`);
            const data = await response.json();
            if (data.success) updateDashboardStats(data.stats);
        } catch (error) {
        } finally {
            hideLoading();
        }
    }

    function updateDashboardStats(stats) {
        document.getElementById('pendingRequests').textContent = stats.requests.pending;
        document.getElementById('requestsNew').textContent = stats.requests.pending;
        document.getElementById('requestsConfirmed').textContent = stats.requests.confirmed;
        document.getElementById('requestsCompleted').textContent = stats.requests.completed;
        const requestsStatus = stats.requests.pending > 0 ? 'Pending' : (stats.requests.confirmed > 0 ? 'Active' : (stats.requests.completed > 0 ? 'Completed' : 'None'));
        const reqStatusEl = document.getElementById('requestsStatus');
        reqStatusEl.textContent = requestsStatus;
        reqStatusEl.className = 'text-xs px-2 py-1 rounded-full ' + (requestsStatus === 'Pending' ? 'bg-yellow-100 text-yellow-700' : requestsStatus === 'Active' ? 'bg-blue-100 text-blue-700' : requestsStatus === 'Completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700');

        document.getElementById('totalProducts').textContent = stats.products.total_products;
        document.getElementById('activeProducts').textContent = stats.products.active_products;
        document.getElementById('inactiveProducts').textContent = stats.products.inactive_products;
        const prodStatusEl = document.getElementById('productsStatus');
        const prodStatus = stats.products.active_products > 0 ? 'Active' : 'None';
        prodStatusEl.textContent = prodStatus;
        prodStatusEl.className = 'text-xs px-2 py-1 rounded-full ' + (prodStatus === 'Active' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700');

        document.getElementById('totalManagers').textContent = stats.managers.total_managers;
        const manStatusEl = document.getElementById('managersStatus');
        const manStatus = stats.managers.total_managers > 0 ? 'Active' : 'None';
        manStatusEl.textContent = manStatus;
        manStatusEl.className = 'text-xs px-2 py-1 rounded-full ' + (manStatus === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700');

        document.getElementById('vendorWalletBalance').textContent = formatCurrency(stats.wallet.main_balance);
        document.getElementById('vendorSmsBalance').textContent = formatCurrency(stats.wallet.sms_balance);

        document.getElementById('vendorMonthTotal').textContent = formatCurrency(stats.transactions.month_total_amount);
        document.getElementById('vendorMonthCredits').textContent = formatCurrency(stats.transactions.month_credits);
        document.getElementById('vendorMonthDebits').textContent = formatCurrency(stats.transactions.month_debits);

        document.getElementById('monthlyStoreViews').textContent = formatCurrency(stats.monthly_store_views || 0);

        if (stats.top_products) renderTopProducts(stats.top_products);
    }

    async function loadRecentActivity() {
        try {
            const response = await fetch(`${API_URL}?action=getRecentActivity&store_id=${encodeURIComponent(STORE_ID)}`);
            const data = await response.json();
            if (data.success) updateRecentActivity(data.activities);
        } catch (error) {
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
            const icon = activity.type === 'request' ? 'fa-calendar-check' : activity.type === 'transaction' ? 'fa-credit-card' : 'fa-info-circle';
            const color = activity.type === 'request' ? 'yellow' : activity.type === 'transaction' ? 'green' : 'blue';
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

    function renderTopProducts(products) {
        const container = document.getElementById('topProductsList');
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="text-center py-6 text-gray-500">No products found.</div>`;
            return;
        }
        container.innerHTML = products.map(p => `
        <a href="${BASE_URL}view/product/${encodeURIComponent(p.product_id)}" target="_blank" class="flex items-center gap-4 p-3 rounded-lg border border-gray-100 hover:border-user-primary hover:shadow-sm transition-all duration-200 bg-gray-50 hover:bg-user-primary/5">
            <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0">
                <img src="${p.image_url}" alt="${escapeHtml(p.title)}" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-secondary truncate">${escapeHtml(p.title)}</h4>
                <p class="text-xs text-gray-500">Price: UGX <span class="font-medium">${p.price !== null ? formatCurrency(p.price) : '—'}</span> • 30d Views: <span class="font-medium">${formatCurrency(p.views_30d)}</span> • All-time: <span class="font-medium">${formatCurrency(p.views_all)}</span></p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">${formatCurrency(p.views_30d)}</span>
        </a>
    `).join('');
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'confirmed': 'bg-blue-100 text-blue-800',
            'completed': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800',
            'SUCCESS': 'bg-green-100 text-green-800',
            'PENDING': 'bg-yellow-100 text-yellow-800',
            'FAILED': 'bg-red-100 text-red-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0);
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);
        if (diffInHours < 24) {
            return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        } else {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
    }

    function refreshActivity() {
        loadRecentActivity();
        const button = event.target.closest('button');
        const icon = button.querySelector('i');
        icon.classList.add('fa-spin');
        setTimeout(() => icon.classList.remove('fa-spin'), 1000);
    }

    function showModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function hideModal(id) { document.getElementById(id).classList.add('hidden'); }
    function showLoading() { document.getElementById('loadingOverlay').classList.remove('hidden'); }
    function hideLoading() { document.getElementById('loadingOverlay').classList.add('hidden'); }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>"']/g, function (m) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]); });
    }

    window.addEventListener('beforeunload', function () {
        if (refreshInterval) clearInterval(refreshInterval);
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>