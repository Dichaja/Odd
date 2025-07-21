<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
ob_start();

function formatCurrency($amount)
{
    return 'Sh. ' . number_format($amount, 0) . '/=';
}

function formatDateTime($dateTime)
{
    $date = new DateTime($dateTime);
    return $date->format('M j, Y g:i A');
}
?>

<div class="min-h-screen bg-gray-50">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-primary text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-secondary font-rubik">Dashboard Overview</h1>
                    <p class="text-sm text-gray-text">Real-time system statistics and quick access</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="stats-cards">
                <div
                    class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-4 bg-blue-200 rounded w-24 mb-2"></div>
                            <div class="h-8 bg-blue-300 rounded w-16 mb-1"></div>
                        </div>
                        <div class="w-12 h-12 bg-blue-200 rounded-xl"></div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-4 bg-yellow-200 rounded w-24 mb-2"></div>
                            <div class="h-8 bg-yellow-300 rounded w-16 mb-1"></div>
                            <div class="h-4 bg-yellow-200 rounded w-20"></div>
                        </div>
                        <div class="w-12 h-12 bg-yellow-200 rounded-xl"></div>
                    </div>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="h-4 bg-purple-200 rounded w-24 mb-2"></div>
                            <div class="h-8 bg-purple-300 rounded w-16 mb-1"></div>
                            <div class="h-4 bg-purple-200 rounded w-20"></div>
                        </div>
                        <div class="w-12 h-12 bg-purple-200 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rocket text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Quick Actions</h3>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <a href="<?= BASE_URL ?>admin/quotations"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-file-invoice-dollar text-blue-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Quotations</span>
                    </a>

                    <a href="<?= BASE_URL ?>admin/approve-transactions"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-check-circle text-green-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Transactions</span>
                    </a>

                    <a href="<?= BASE_URL ?>admin/zzimba-credit"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-credit-card text-purple-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Credit</span>
                    </a>

                    <a href="<?= BASE_URL ?>admin/products"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-box text-orange-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Products</span>
                    </a>

                    <a href="<?= BASE_URL ?>admin/vendor-stores"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-store text-red-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Stores</span>
                    </a>

                    <a href="<?= BASE_URL ?>admin/system-users"
                        class="flex flex-col items-center p-4 rounded-xl border-2 border-gray-200 hover:border-primary/50 hover:bg-primary/5 transition-all duration-200 group">
                        <div
                            class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-primary/10 transition-colors">
                            <i class="fas fa-users text-indigo-600 group-hover:text-primary text-xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-900 text-center">Users</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-invoice text-primary"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-secondary font-rubik">Recent Quotes</h3>
                        </div>
                        <a href="<?= BASE_URL ?>admin/quotations"
                            class="text-sm text-primary hover:text-primary/80 font-medium">View All</a>
                    </div>
                </div>

                <div class="p-6">
                    <div id="recent-quotes" class="space-y-4">
                        <div class="animate-pulse">
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                </div>
                                <div class="h-6 bg-gray-200 rounded w-16"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-primary"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">System Status</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div id="system-status" class="space-y-4">
                        <div class="animate-pulse space-y-4">
                            <div class="flex justify-between items-center">
                                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                <div class="h-6 bg-gray-200 rounded w-16"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-6 bg-gray-200 rounded w-20"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <div class="h-4 bg-gray-200 rounded w-2/5"></div>
                                <div class="h-6 bg-gray-200 rounded w-12"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="refresh-indicator"
    class="fixed bottom-4 right-4 bg-white rounded-full shadow-lg border border-gray-200 px-4 py-2 hidden">
    <div class="flex items-center gap-2">
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <span class="text-sm text-gray-600">Auto-refreshing...</span>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageDashboard.php';
    let refreshInterval;
    let isRefreshing = false;

    function formatCurrency(amount) {
        return 'Sh. ' + new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount) + '/=';
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function getStatusBadge(status) {
        const badges = {
            'Processed': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Processed</span>',
            'Processing': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Processing</span>',
            'New': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">New</span>',
            'Cancelled': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelled</span>',
            'Paid': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Paid</span>'
        };
        return badges[status] || `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${status}</span>`;
    }

    async function fetchDashboardData() {
        if (isRefreshing) return;

        isRefreshing = true;
        showRefreshIndicator();

        try {
            const response = await fetch(`${API_URL}?action=getDashboardStats`);
            const data = await response.json();

            if (data.success) {
                updateStatsCards(data.stats);
                updateRecentQuotes(data.recent_quotes);
                updateSystemStatus(data.system_status);
            } else {
                console.error('Failed to fetch dashboard data:', data.message);
            }
        } catch (error) {
            console.error('Error fetching dashboard data:', error);
        } finally {
            isRefreshing = false;
            hideRefreshIndicator();
        }
    }

    function updateStatsCards(stats) {
        const container = document.getElementById('stats-cards');

        container.innerHTML = `
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Quotes</p>
                    <p class="text-2xl font-bold text-blue-900 whitespace-nowrap">${stats.pending_quotes || 0}</p>
                </div>
                <div class="w-12 h-12 bg-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-invoice text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Total Users</p>
                    <p class="text-2xl font-bold text-yellow-900 whitespace-nowrap">${stats.total_users || 0}</p>
                    <p class="text-sm font-medium text-yellow-700 whitespace-nowrap">Active: ${stats.active_users || 0}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Total Vendors</p>
                    <p class="text-2xl font-bold text-purple-900 whitespace-nowrap">${stats.total_vendors || 0}</p>
                    <p class="text-sm font-medium text-purple-700 whitespace-nowrap">Active: ${stats.active_vendors || 0}</p>
                </div>
                <div class="w-12 h-12 bg-purple-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-store text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    `;
    }

    function updateRecentQuotes(quotes) {
        const container = document.getElementById('recent-quotes');

        if (!quotes || quotes.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-invoice text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500">No recent quotes found</p>
                </div>
            `;
            return;
        }

        container.innerHTML = quotes.map(quote => `
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-invoice text-primary"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">Quote #${quote.RFQ_ID || 'N/A'}</p>
                    <p class="text-sm text-gray-500">Location: ${quote.site_location || 'Unknown'}</p>
                    <p class="text-xs text-gray-400">${formatDateTime(quote.created_at)}</p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    ${getStatusBadge(quote.status)}
                    <p class="text-sm font-semibold text-gray-900">${formatCurrency(quote.fee_charged || 0)}</p>
                </div>
            </div>
        `).join('');
    }

    function updateSystemStatus(status) {
        const container = document.getElementById('system-status');

        container.innerHTML = `
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Active Users</span>
                    <span class="font-semibold text-gray-900">${status.active_users || 0}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pending Transactions</span>
                    <span class="font-semibold text-gray-900">${status.pending_transactions || 0}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Products</span>
                    <span class="font-semibold text-gray-900">${status.total_products || 0}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Active Vendors</span>
                    <span class="font-semibold text-gray-900">${status.active_vendors || 0}</span>
                </div>
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <span class="text-sm text-gray-600">System Status</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                        Online
                    </span>
                </div>
            </div>
        `;
    }

    function showRefreshIndicator() {
        document.getElementById('refresh-indicator').classList.remove('hidden');
    }

    function hideRefreshIndicator() {
        setTimeout(() => {
            document.getElementById('refresh-indicator').classList.add('hidden');
        }, 1000);
    }

    function startAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        refreshInterval = setInterval(fetchDashboardData, 60000);
    }

    function stopAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        fetchDashboardData();
        startAutoRefresh();

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
                fetchDashboardData();
            }
        });

        window.addEventListener('beforeunload', stopAutoRefresh);
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>