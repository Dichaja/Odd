<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'System Notifications Management';
$activeNav = 'system-notifications';
ob_start();
?>

<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">System Notifications Management</h1>
                        <div id="connectionStatus"
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-green-700">Live Updates</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor and manage system
                        notifications</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="createNotificationBtn"
                        class="px-3 sm:px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-plus text-sm"></i>
                        <span class="hidden sm:inline">Create</span>
                    </button>
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                    <button id="exportBtn"
                        class="px-3 sm:px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-download text-sm"></i>
                        <span class="hidden sm:inline">Export</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Notifications</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalNotifications">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-bell text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active Today</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="todayNotifications">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-day text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">High Priority</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="highPriorityNotifications">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Unread</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="unreadNotifications">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-envelope text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Notification Controls</h2>
                    <p class="text-sm text-gray-600">Configure your notification view and filters</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="today">
                            <i class="fas fa-calendar-day mr-2"></i>Today
                        </button>
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="week">
                            <i class="fas fa-calendar-week mr-2"></i> Weekly
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="month">
                            <i class="fas fa-calendar-alt mr-2"></i> Monthly
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" id="startDate"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" id="endDate"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <button id="applyCustomRange"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                    <select id="typeFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="all">All Types</option>
                        <option value="system">System</option>
                        <option value="info">Information</option>
                        <option value="signup">User Signup</option>
                        <option value="login">Login Activity</option>
                        <option value="password_reset">Password Reset</option>
                        <option value="store_update">Store Update</option>
                        <option value="visit_request">Visit Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                    <select id="priorityFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="all">All Priorities</option>
                        <option value="high">High Priority</option>
                        <option value="normal">Normal Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="priority_desc">Highest Priority</option>
                        <option value="priority_asc">Lowest Priority</option>
                        <option value="type">By Type</option>
                    </select>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-secondary">Notifications Over Time</h3>
                    <div class="flex items-center gap-2">
                        <button id="chartTypeToggle"
                            class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">
                            <i class="fas fa-chart-line mr-1"></i> Line Chart
                        </button>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="notificationsChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-secondary">Notification Types</h3>
                    <span class="text-sm text-gray-500" id="typeChartPeriod">This Week</span>
                </div>
                <div class="h-80">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">System Notifications</h3>
                        <p class="text-sm text-gray-600">Click on any notification to view details and manage</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search notifications..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <div class="flex gap-2">
                            <button id="markSelectedReadBtn"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                                <i class="fas fa-check mr-1"></i>
                                Mark Read
                            </button>
                            <button id="markSelectedUnreadBtn"
                                class="px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm transition-colors">
                                <i class="fas fa-times mr-1"></i>
                                Mark Unread
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="notificationsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300">
                            </th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Notification</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Type</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Priority</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Triggered By</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Recipients</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Created</th>
                        </tr>
                    </thead>

                    <tbody id="notificationsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading notifications...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> notifications
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>

            <div class="lg:hidden" id="notificationsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading notifications...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div id="notificationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeNotificationModal()"></div>
    <div
        class="relative w-full h-full max-w-4xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div id="modalNotificationIcon"
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-bell text-gray-600"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Notification Details</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">View and manage notification</p>
                </div>
            </div>
            <button onclick="closeNotificationModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-100px)]" id="notificationContent">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading notification details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Notification Modal -->
<div id="createNotificationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeCreateModal()"></div>
    <div
        class="relative w-full h-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-secondary" id="createModalTitle">Create New Notification</h3>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <form id="notificationForm" class="p-4 sm:p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notification Type</label>
                    <select id="notificationType" name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="system">System</option>
                        <option value="info">Information</option>
                        <option value="signup">User Signup</option>
                        <option value="login">Login Activity</option>
                        <option value="password_reset">Password Reset</option>
                        <option value="store_update">Store Update</option>
                        <option value="visit_request">Visit Request</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority Level</label>
                    <select id="notificationPriority" name="priority" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input type="text" id="notificationTitle" name="title" required maxlength="255"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea id="notificationMessage" name="message" required rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Link URL (Optional)</label>
                <input type="url" id="notificationLink" name="link_url" maxlength="512"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Recipients</label>
                <select id="recipientType" name="recipient_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/20">
                    <option value="admin">All Admins</option>
                    <option value="user">All Users</option>
                    <option value="store">All Stores</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeCreateModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Create Notification
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let notifications = [];
    let currentNotificationId = null;
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'week';
    let totalNotifications = 0;
    let notificationsChart = null;
    let typeChart = null;
    let selectedNotifications = new Set();

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        initializeDateFilters();
        loadNotificationsData();
        loadChartData();
    });

    function setupEventListeners() {
        document.getElementById('refreshBtn').addEventListener('click', refreshData);
        document.getElementById('exportBtn').addEventListener('click', exportData);
        document.getElementById('createNotificationBtn').addEventListener('click', openCreateModal);
        document.getElementById('searchFilter').addEventListener('input', debounce(applyFilters, 300));
        document.getElementById('typeFilter').addEventListener('change', applyFilters);
        document.getElementById('priorityFilter').addEventListener('change', applyFilters);
        document.getElementById('sortFilter').addEventListener('change', applyFilters);
        document.getElementById('chartTypeToggle').addEventListener('click', toggleChartType);
        document.getElementById('selectAll').addEventListener('change', toggleSelectAll);
        document.getElementById('notificationForm').addEventListener('submit', handleCreateNotification);
        document.getElementById('markSelectedReadBtn').addEventListener('click', markSelectedAsRead);
        document.getElementById('markSelectedUnreadBtn').addEventListener('click', markSelectedAsUnread);

        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                this.classList.add('active', 'bg-primary', 'text-white', 'border-primary');
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');

                const period = this.dataset.period;
                setDateRangeForPeriod(period);
            });
        });

        document.getElementById('applyCustomRange').addEventListener('click', function () {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                currentPeriod = 'custom';
                currentPage = 1;
                updateTypeChartLabel();
                loadNotificationsData();
                loadChartData();
            }
        });

        ['prevPage', 'nextPage'].forEach(id => {
            document.getElementById(id).addEventListener('click', function () {
                if (id.includes('prev') && currentPage > 1) {
                    currentPage--;
                    loadNotificationsData();
                } else if (id.includes('next')) {
                    currentPage++;
                    loadNotificationsData();
                }
            });
        });
    }

    function applyFilters() {
        currentPage = 1;
        selectedNotifications.clear();
        document.getElementById('selectAll').checked = false;
        loadNotificationsData();
        loadChartData();
    }

    function updateTypeChartLabel() {
        const label = document.getElementById('typeChartPeriod');
        switch (currentPeriod) {
            case 'today':
                label.textContent = 'Today';
                break;
            case 'week':
                label.textContent = 'This Week';
                break;
            case 'month':
                label.textContent = 'This Month';
                break;
            case 'custom':
                label.style.display = 'none';
                return;
        }
        label.style.display = '';
    }

    async function loadNotificationsData() {
        try {
            const params = new URLSearchParams({
                action: 'get_notifications',
                page: currentPage,
                limit: itemsPerPage,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod,
                type: document.getElementById('typeFilter').value,
                priority: document.getElementById('priorityFilter').value,
                sort: document.getElementById('sortFilter').value,
                search: document.getElementById('searchFilter').value
            });

            const response = await fetch(`fetch/manageNotifications.php?${params}`);
            const data = await response.json();

            if (data.success) {
                notifications = data.data;
                totalNotifications = data.total;
                updateStatistics(data.stats);
                renderNotificationsTable();
                renderNotificationsCards();
                updatePagination(data.total, data.page);
            } else {
                showError('Failed to load notifications data');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            showError('Failed to load notifications data');
        }
    }

    async function loadChartData() {
        try {
            const params = new URLSearchParams({
                action: 'get_chart_data',
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod
            });

            const response = await fetch(`fetch/manageNotifications.php?${params}`);
            const data = await response.json();

            if (data.success) {
                updateNotificationsChart(data.timeline);
                updateTypeChart(data.types);
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    function updateStatistics(stats) {
        if (stats) {
            document.getElementById('totalNotifications').textContent = stats.total_notifications.toLocaleString();
            document.getElementById('todayNotifications').textContent = stats.today_notifications.toLocaleString();
            document.getElementById('highPriorityNotifications').textContent = stats.high_priority_notifications.toLocaleString();
            document.getElementById('unreadNotifications').textContent = stats.unread_notifications.toLocaleString();
        }
    }

    function updateNotificationsChart(timelineData) {
        const ctx = document.getElementById('notificationsChart').getContext('2d');

        if (notificationsChart) {
            notificationsChart.destroy();
        }

        const chartType = document.getElementById('chartTypeToggle').textContent.includes('Line') ? 'line' : 'bar';

        notificationsChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: timelineData.labels,
                datasets: [{
                    label: 'Notifications Created',
                    data: timelineData.values,
                    borderColor: '#D92B13',
                    backgroundColor: chartType === 'bar' ? '#D92B13' : 'rgba(217, 43, 19, 0.1)',
                    tension: 0.4,
                    fill: chartType === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function updateTypeChart(typeData) {
        const ctx = document.getElementById('typeChart').getContext('2d');

        if (typeChart) {
            typeChart.destroy();
        }

        typeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: typeData.labels,
                datasets: [{
                    data: typeData.values,
                    backgroundColor: [
                        '#EF4444', '#10B981', '#3B82F6', '#F59E0B', '#8B5CF6',
                        '#EC4899', '#14B8A6', '#F97316', '#84CC16', '#6366F1'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function renderNotificationsTable() {
        const tbody = document.getElementById('notificationsBody');

        if (notifications.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <div>No notifications found</div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = notifications.map(notification => {
            const priorityColors = {
                high: 'bg-red-100 text-red-800',
                normal: 'bg-blue-100 text-blue-800',
                low: 'bg-gray-100 text-gray-800'
            };

            const typeIcons = {
                system: 'fas fa-cog',
                info: 'fas fa-info-circle',
                signup: 'fas fa-user-plus',
                login: 'fas fa-sign-in-alt',
                password_reset: 'fas fa-key',
                store_update: 'fas fa-store',
                visit_request: 'fas fa-calendar-check'
            };

            return `
            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewNotificationDetails('${notification.id}')">
                <td class="px-4 py-3" onclick="event.stopPropagation()">
                    <input type="checkbox" class="notification-checkbox rounded border-gray-300" 
                           value="${notification.id}" ${selectedNotifications.has(notification.id) ? 'checked' : ''}>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <i class="${typeIcons[notification.type] || 'fas fa-bell'} text-gray-600"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-secondary max-w-xs truncate">${notification.title}</div>
                            <div class="text-xs text-gray-500">ID: ${notification.id}</div>
                            ${!notification.is_seen ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">New</span>' : ''}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm text-gray-900 capitalize">${notification.type.replace('_', ' ')}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${priorityColors[notification.priority]}">
                        ${notification.priority}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="text-sm text-gray-900">${notification.triggered_by_name || 'System'}</div>
                    <div class="text-xs text-gray-500">${notification.triggered_by_type || ''}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm font-medium text-gray-900">${notification.recipient_count}</span>
                    <div class="text-xs text-gray-500">${notification.unread_count} unread</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="text-sm text-gray-900">${formatDate(notification.created_at)}</div>
                    <div class="text-xs text-gray-500">${formatTime(notification.created_at)}</div>
                </td>
            </tr>
        `;
        }).join('');

        // Add event listeners for checkboxes
        document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    selectedNotifications.add(this.value);
                } else {
                    selectedNotifications.delete(this.value);
                }
                updateSelectAllState();
            });
        });
    }

    // Add new functions for marking selected notifications
    async function markSelectedAsRead() {
        if (selectedNotifications.size === 0) {
            showError('Please select notifications to mark as read');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'mark_selected_read');
            formData.append('ids', JSON.stringify(Array.from(selectedNotifications)));

            const response = await fetch('fetch/manageNotifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                loadNotificationsData();
                showSuccess(`${selectedNotifications.size} notifications marked as read`);
            } else {
                showError(data.error || 'Failed to mark notifications as read');
            }
        } catch (error) {
            console.error('Error marking as read:', error);
            showError('Failed to mark notifications as read');
        }
    }

    async function markSelectedAsUnread() {
        if (selectedNotifications.size === 0) {
            showError('Please select notifications to mark as unread');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'mark_selected_unread');
            formData.append('ids', JSON.stringify(Array.from(selectedNotifications)));

            const response = await fetch('fetch/manageNotifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                loadNotificationsData();
                showSuccess(`${selectedNotifications.size} notifications marked as unread`);
            } else {
                showError(data.error || 'Failed to mark notifications as unread');
            }
        } catch (error) {
            console.error('Error marking as unread:', error);
            showError('Failed to mark notifications as unread');
        }
    }

    // Update toggleSelectAll to work across all pages
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');

        if (selectAll.checked) {
            // Select all notifications across all pages
            selectAllNotifications();
        } else {
            // Deselect all
            selectedNotifications.clear();
            document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }

    async function selectAllNotifications() {
        try {
            const params = new URLSearchParams({
                action: 'get_all_notification_ids',
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod,
                type: document.getElementById('typeFilter').value,
                priority: document.getElementById('priorityFilter').value,
                search: document.getElementById('searchFilter').value
            });

            const response = await fetch(`fetch/manageNotifications.php?${params}`);
            const data = await response.json();

            if (data.success) {
                selectedNotifications.clear();
                data.ids.forEach(id => selectedNotifications.add(id));

                // Update checkboxes on current page
                document.querySelectorAll('.notification-checkbox').forEach(checkbox => {
                    checkbox.checked = selectedNotifications.has(checkbox.value);
                });

                showSuccess(`Selected ${data.ids.length} notifications across all pages`);
            }
        } catch (error) {
            console.error('Error selecting all notifications:', error);
        }
    }

    function toggleChartType() {
        const button = document.getElementById('chartTypeToggle');
        const isLine = button.textContent.includes('Line');

        button.innerHTML = isLine ?
            '<i class="fas fa-chart-bar mr-1"></i> Bar Chart' :
            '<i class="fas fa-chart-line mr-1"></i> Line Chart';

        loadChartData();
    }

    function refreshData() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        Promise.all([loadNotificationsData(), loadChartData()]).finally(() => {
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }, 1000);
        });
    }

    function exportData() {
        const params = new URLSearchParams({
            action: 'export',
            start_date: document.getElementById('startDate').value,
            end_date: document.getElementById('endDate').value,
            period: currentPeriod,
            type: document.getElementById('typeFilter').value,
            priority: document.getElementById('priorityFilter').value,
            status: document.getElementById('statusFilter').value,
            sort: document.getElementById('sortFilter').value
        });

        window.open(`fetch/manageNotifications.php?${params}`, '_blank');
    }

    function initializeDateFilters() {
        setDateRangeForPeriod('week');
    }

    function setDateRangeForPeriod(period) {
        const now = new Date();
        const kampalaOffset = 3 * 60;
        const kampalaTime = new Date(now.getTime() + (kampalaOffset * 60000));

        let startDate, endDate;

        switch (period) {
            case 'today':
                startDate = new Date(kampalaTime);
                endDate = new Date(kampalaTime);
                break;
            case 'week':
                const dayOfWeek = kampalaTime.getDay();
                startDate = new Date(kampalaTime);
                startDate.setDate(kampalaTime.getDate() - dayOfWeek);
                endDate = new Date(kampalaTime);
                break;
            case 'month':
                startDate = new Date(kampalaTime.getFullYear(), kampalaTime.getMonth(), 1);
                endDate = new Date(kampalaTime);
                break;
            default:
                startDate = new Date(kampalaTime);
                endDate = new Date(kampalaTime);
        }

        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

        currentPeriod = period;
        currentPage = 1;
        updateTypeChartLabel();
        loadNotificationsData();
        loadChartData();
    }

    function showError(message) {
        // You can implement a toast notification system here
        alert('Error: ' + message);
    }

    function showSuccess(message) {
        // You can implement a toast notification system here
        alert('Success: ' + message);
    }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);

        document.getElementById('showingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Add these missing functions before the existing window assignments

    async function viewNotificationDetails(notificationId) {
        currentNotificationId = notificationId;
        document.getElementById('notificationModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        try {
            const params = new URLSearchParams({
                action: 'get_notification_details',
                id: notificationId
            });

            const response = await fetch(`fetch/manageNotifications.php?${params}`);
            const data = await response.json();

            if (data.success) {
                loadNotificationDetails(data.data);
            } else {
                showError('Failed to load notification details');
            }
        } catch (error) {
            console.error('Error loading notification details:', error);
            showError('Failed to load notification details');
        }
    }

    function loadNotificationDetails(notification) {
        document.getElementById('modalTitle').textContent = notification.title;
        document.getElementById('modalSubtitle').textContent = `${notification.type.replace('_', ' ')} • ${notification.priority} priority`;

        const typeIcons = {
            system: 'fas fa-cog',
            info: 'fas fa-info-circle',
            signup: 'fas fa-user-plus',
            login: 'fas fa-sign-in-alt',
            password_reset: 'fas fa-key',
            store_update: 'fas fa-store',
            visit_request: 'fas fa-calendar-check'
        };

        const modalIcon = document.getElementById('modalNotificationIcon');
        modalIcon.innerHTML = `<i class="${typeIcons[notification.type] || 'fas fa-bell'} text-gray-600"></i>`;

        const notificationContent = document.getElementById('notificationContent');

        notificationContent.innerHTML = `
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-2xl font-bold text-blue-900">${notification.recipient_count}</div>
                    <div class="text-sm text-blue-600">Total Recipients</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="text-2xl font-bold text-green-900">${notification.read_count}</div>
                    <div class="text-sm text-green-600">Read</div>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <div class="text-2xl font-bold text-orange-900">${notification.unread_count}</div>
                    <div class="text-sm text-orange-600">Unread</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="text-2xl font-bold text-purple-900">${notification.dismissed_count}</div>
                    <div class="text-sm text-purple-600">Dismissed</div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-secondary mb-4">Notification Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Title</label>
                        <p class="text-gray-900 font-medium">${notification.title}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Type</label>
                        <p class="text-gray-900 font-medium capitalize">${notification.type.replace('_', ' ')}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Priority</label>
                        <p class="text-gray-900 font-medium capitalize">${notification.priority}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Created By</label>
                        <p class="text-gray-900 font-medium">${notification.created_by || 'System'}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Created At</label>
                        <p class="text-gray-900 font-medium">${formatDateTime(notification.created_at)}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Link URL</label>
                        <p class="text-gray-900 font-medium">${notification.link_url || 'None'}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-600">Message</label>
                    <p class="text-gray-900 mt-1">${notification.message}</p>
                </div>
            </div>

            ${notification.recipients && notification.recipients.length > 0 ? `
            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-secondary mb-4">Recipients Status</h4>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    ${notification.recipients.map(recipient => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-900">${recipient.recipient_type}: ${recipient.recipient_id}</div>
                                <div class="text-xs text-gray-500">${recipient.message}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm ${recipient.is_seen ? 'text-green-600' : 'text-orange-600'}">
                                    ${recipient.is_seen ? 'Read' : 'Unread'}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ${recipient.seen_at ? formatDateTime(recipient.seen_at) : 'Not read'}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}

            <div class="flex justify-end gap-3">
                <button onclick="markNotificationAsRead('${notification.id}')" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Mark All as Read
                </button>
            </div>
        </div>
    `;
    }

    function closeNotificationModal() {
        document.getElementById('notificationModal').classList.add('hidden');
        document.body.style.overflow = '';
        currentNotificationId = null;
    }

    function openCreateModal() {
        document.getElementById('createNotificationModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        document.getElementById('notificationForm').reset();
    }

    function closeCreateModal() {
        document.getElementById('createNotificationModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    async function handleCreateNotification(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.append('action', 'create_notification');

        try {
            const response = await fetch('fetch/manageNotifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                closeCreateModal();
                loadNotificationsData();
                showSuccess('Notification created successfully');
            } else {
                showError(data.error || 'Failed to create notification');
            }
        } catch (error) {
            console.error('Error creating notification:', error);
            showError('Failed to create notification');
        }
    }

    async function markNotificationAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('action', 'mark_as_read');
            formData.append('id', notificationId);

            const response = await fetch('fetch/manageNotifications.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                loadNotificationsData();
                if (currentNotificationId === notificationId) {
                    viewNotificationDetails(notificationId);
                }
                showSuccess('Notification marked as read');
            } else {
                showError(data.error || 'Failed to mark as read');
            }
        } catch (error) {
            console.error('Error marking as read:', error);
            showError('Failed to mark as read');
        }
    }

    function renderNotificationsCards() {
        const container = document.getElementById('notificationsCards');

        if (notifications.length === 0) {
            container.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                <div>No notifications found</div>
            </div>
        `;
            return;
        }

        container.innerHTML = notifications.map(notification => {
            const priorityColors = {
                high: 'bg-red-100 text-red-800',
                normal: 'bg-blue-100 text-blue-800',
                low: 'bg-gray-100 text-gray-800'
            };

            const typeIcons = {
                system: 'fas fa-cog',
                info: 'fas fa-info-circle',
                signup: 'fas fa-user-plus',
                login: 'fas fa-sign-in-alt',
                password_reset: 'fas fa-key',
                store_update: 'fas fa-store',
                visit_request: 'fas fa-calendar-check'
            };

            return `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewNotificationDetails('${notification.id}')">
                <div class="flex items-start gap-3">
                    <input type="checkbox" class="notification-checkbox-mobile rounded border-gray-300 mt-1" value="${notification.id}" onclick="event.stopPropagation()">
                    <div class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <i class="${typeIcons[notification.type] || 'fas fa-bell'} text-gray-600"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-medium text-secondary truncate">${notification.title}</h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${priorityColors[notification.priority]}">
                                ${notification.priority}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">${notification.type.replace('_', ' ')} • ${formatDate(notification.created_at)}</div>
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-600">
                                ${notification.recipient_count} recipients • ${notification.unread_count} unread
                            </div>
                            <div class="text-xs text-gray-500">
                                ${notification.triggered_by_name || 'System'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        // Add event listeners for mobile checkboxes
        document.querySelectorAll('.notification-checkbox-mobile').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    selectedNotifications.add(this.value);
                } else {
                    selectedNotifications.delete(this.value);
                }
            });
        });
    }

    function updateSelectAllState() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.notification-checkbox');
        const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');

        selectAll.checked = checkboxes.length > 0 && checkedBoxes.length === checkboxes.length;
        selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
    }

    // Update the window assignments at the end
    window.viewNotificationDetails = viewNotificationDetails;
    window.closeNotificationModal = closeNotificationModal;
    window.openCreateModal = openCreateModal;
    window.closeCreateModal = closeCreateModal;
    window.markNotificationAsRead = markNotificationAsRead;

    window.viewNotificationDetails = viewNotificationDetails;
    window.closeNotificationModal = closeNotificationModal;
    window.deleteNotification = deleteNotification;
    window.markNotificationAsRead = markNotificationAsRead;
</script>

<style>
    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all 0.2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #D92B13;
        color: white;
        border-color: #D92B13;
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>