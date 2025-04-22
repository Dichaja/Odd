<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL . 'login/login.php');
    exit;
}

ob_start();
?>

<div class="space-y-8">
    <!-- Welcome Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Welcome,
                    <?= htmlspecialchars($_SESSION['user']['username']) ?></h1>
                <p class="text-gray-500 mt-1">Here's what's happening with your users today.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500"><?= date('l, F j, Y') ?></span>
                <div class="h-6 w-px bg-gray-200"></div>
                <span class="text-sm text-gray-500" id="live-time">00:00:00</span>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <h3 class="text-3xl font-bold text-gray-900" id="stat-users">0</h3>
                </div>
                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-users text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500">Active Users</p>
                    <h3 class="text-3xl font-bold text-gray-900" id="stat-active">0</h3>
                </div>
                <div class="w-14 h-14 rounded-full bg-green-50 flex items-center justify-center">
                    <i class="fas fa-user-check text-green-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500">New Users (30d)</p>
                    <h3 class="text-3xl font-bold text-gray-900" id="stat-new">0</h3>
                </div>
                <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center">
                    <i class="fas fa-user-plus text-purple-500 text-xl"></i>
                </div>
            </div>
        </div>

        <div
            class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-gray-500">Inactive Users</p>
                    <h3 class="text-3xl font-bold text-gray-900" id="stat-inactive">0</h3>
                </div>
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                    <i class="fas fa-user-slash text-red-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">User Management</h2>
                <p class="text-sm text-gray-500 mt-1">Manage and monitor user accounts</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchUsers" placeholder="Search users..."
                        class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus"
                        class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <select id="sortUsers"
                        class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="created_at" selected>Sort by Registration Date</option>
                        <option value="current_login">Sort by Last Login</option>
                        <option value="username">Sort by Username</option>
                        <option value="status">Sort by Status</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="users-table">
                <thead>
                    <tr class="text-left bg-gray-50">
                        <th
                            class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[180px]">
                            Username</th>
                        <th
                            class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[220px]">
                            Contact</th>
                        <th
                            class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[180px]">
                            Registration Date</th>
                        <th
                            class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[120px]">
                            Status</th>
                        <th
                            class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider min-w-[180px]">
                            Last Login</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="divide-y divide-gray-100">
                    <!-- Table rows will be populated by JavaScript -->
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <div class="flex items-center justify-center">
                                <div
                                    class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mr-2">
                                </div>
                                Loading users...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="responsive-table-mobile p-4" id="users-mobile">
            <!-- Mobile cards will be populated by JavaScript -->
            <div class="flex items-center justify-center py-4 text-gray-500">
                <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mr-2"></div>
                Loading users...
            </div>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-500">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span
                    id="total-users">0</span> users
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <!-- Pagination numbers will be populated by JavaScript -->
                </div>
                <button id="next-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" onclick="hideUserDetails()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-2xl bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-user-title">User Details</h3>
                <button onclick="hideUserDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="userDetailsContent">
                <div class="flex items-center justify-center h-full">
                    <div class="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                </div>
            </div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button onclick="hideUserDetails()"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired due to inactivity.</p>
                <p class="text-sm text-gray-500 mt-1">Redirecting in <span id="countdown">10</span> seconds...</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Login Now
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .responsive-table-mobile {
        display: none;
    }

    @media (max-width: 768px) {
        .responsive-table-desktop {
            display: none;
        }

        .responsive-table-mobile {
            display: block;
        }

        .mobile-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .mobile-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
            background-color: #f9fafb;
        }

        .mobile-card-content {
            padding: 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .mobile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-active {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .status-suspended {
        background-color: #fef3c7;
        color: #92400e;
    }

    /* Table hover effects */
    #users-table tbody tr {
        transition: all 0.2s ease;
    }

    #users-table tbody tr:hover {
        background-color: #f9fafb;
    }

    /* Table responsiveness */
    .responsive-table-desktop {
        overflow-x: auto;
    }

    /* Text truncation */
    .truncate-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        display: block;
    }

    /* Clickable rows */
    #users-table tbody tr {
        cursor: pointer;
    }

    .mobile-card {
        cursor: pointer;
    }
</style>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let currentPage = 1;
    let totalPages = 1;
    let itemsPerPage = 10;
    let currentSort = 'created_at';
    let currentOrder = 'desc';
    let currentSearch = '';
    let currentFilter = '';
    let userGrowthChart = null;
    let userStatusChart = null;

    // Format date to a readable format
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Format date and time to a readable format
    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Calculate time ago
    function timeAgo(dateString) {
        if (!dateString) return '-';

        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return `${diffInSeconds} seconds ago`;
        }

        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
        }

        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
        }

        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
        }

        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;
        }

        const diffInYears = Math.floor(diffInMonths / 12);
        return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`;
    }

    // Generate user initials for avatar
    function getUserInitials(username) {
        if (!username) return '';

        // Split by non-word characters and get first letter of each part
        const parts = username.split(/[^a-zA-Z0-9]/);
        let initials = '';

        for (let i = 0; i < Math.min(parts.length, 2); i++) {
            if (parts[i].length > 0) {
                initials += parts[i][0].toUpperCase();
            }
        }

        // If we couldn't get 2 initials, just use the first 2 letters of the username
        if (initials.length < 2 && username.length >= 2) {
            initials = username.substring(0, 2).toUpperCase();
        }

        return initials;
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Update live time
    function updateLiveTime() {
        const now = new Date();
        const timeElement = document.getElementById('live-time');
        timeElement.textContent = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }

    // Load dashboard statistics
    function loadStats() {
        fetch(`${BASE_URL}admin/fetch/manageDashboard.php?action=getStats`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const stats = data.stats;

                    // Update stats in the UI
                    document.getElementById('stat-users').textContent = stats.total_users.toLocaleString();
                    document.getElementById('stat-active').textContent = stats.active_users.toLocaleString();
                    document.getElementById('stat-inactive').textContent = stats.inactive_users.toLocaleString();
                    document.getElementById('stat-new').textContent = stats.new_users.toLocaleString();

                    // Update change percentages
                    document.getElementById('total-change').textContent = `${stats.total_change || 0}%`;
                    document.getElementById('active-change').textContent = `${stats.active_change || 0}%`;
                    document.getElementById('new-change').textContent = `${stats.new_change || 0}%`;
                    document.getElementById('inactive-change').textContent = `${stats.inactive_change || 0}%`;

                } else {
                    console.error('Error loading stats:', data.error);
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error loading stats:', error);
                }
            });
    }

    // Load users with pagination, sorting, and filtering
    function loadUsers() {
        const tableBody = document.getElementById('users-table-body');
        const mobileContainer = document.getElementById('users-mobile');

        // Show loading indicators
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex items-center justify-center">
                        <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mr-2"></div>
                        Loading users...
                    </div>
                </td>
            </tr>
        `;
        mobileContainer.innerHTML = `
            <div class="flex items-center justify-center py-4 text-gray-500">
                <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin mr-2"></div>
                Loading users...
            </div>
        `;

        const url = new URL(`${BASE_URL}admin/fetch/manageDashboard.php?action=getUsers`);
        url.searchParams.append('page', currentPage);
        url.searchParams.append('limit', itemsPerPage);
        url.searchParams.append('sort', currentSort);
        url.searchParams.append('order', currentOrder);

        if (currentSearch) {
            url.searchParams.append('search', currentSearch);
        }

        if (currentFilter) {
            url.searchParams.append('status', currentFilter);
        }

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    renderUsers(data.users);
                    renderPagination(data.pagination);
                } else {
                    console.error('Error loading users:', data.error);
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading users</td></tr>';
                    mobileContainer.innerHTML = '<div class="text-center py-4 text-red-500">Error loading users</div>';
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error loading users:', error);
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Failed to load users</td></tr>';
                    mobileContainer.innerHTML = '<div class="text-center py-4 text-red-500">Failed to load users</div>';
                }
            });
    }

    // Render users table and mobile cards
    function renderUsers(users) {
        const tableBody = document.getElementById('users-table-body');
        const mobileContainer = document.getElementById('users-mobile');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">No users found</td></tr>';
            mobileContainer.innerHTML = '<div class="text-center py-4">No users found</div>';
            return;
        }

        users.forEach(user => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';
            tr.onclick = function () { showUserDetails(user.id); };

            const initials = getUserInitials(user.username);

            let statusBadge = '';
            if (user.status === 'active') {
                statusBadge = '<span class="status-badge status-active"><span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1"></span>Active</span>';
            } else if (user.status === 'inactive') {
                statusBadge = '<span class="status-badge status-inactive"><span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1"></span>Inactive</span>';
            } else {
                statusBadge = '<span class="status-badge status-suspended"><span class="w-1.5 h-1.5 rounded-full bg-amber-600 mr-1"></span>Suspended</span>';
            }

            tr.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                        ${initials}
                    </div>
                    <span class="font-medium text-gray-900 truncate-text">${escapeHtml(user.username)}</span>
                </div>
            </td>
            <td class="px-6 py-4">
                <div class="flex flex-col">
                    <span class="text-sm text-gray-500 truncate-text">${escapeHtml(user.email)}</span>
                    <span class="text-sm text-gray-500 truncate-text">${escapeHtml(user.phone || '-')}</span>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 truncate-text">${formatDateTime(user.created_at)}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${statusBadge}</td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex flex-col">
                    <span class="truncate-text">${formatDateTime(user.current_login)}</span>
                    <span class="text-xs text-gray-400 truncate-text">${timeAgo(user.current_login)}</span>
                </div>
            </td>
        `;

            tableBody.appendChild(tr);

            // Mobile card
            const mobileCard = document.createElement('div');
            mobileCard.className = 'mobile-card';
            mobileCard.onclick = function () { showUserDetails(user.id); };

            mobileCard.innerHTML = `
            <div class="mobile-card-header">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                        ${initials}
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">${escapeHtml(user.username)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(user.email)}</div>
                    </div>
                </div>
                <div>
                    ${statusBadge}
                </div>
            </div>
            <div class="mobile-card-content">
                <div class="mobile-grid">
                    <div class="mobile-grid-item">
                        <span class="mobile-label">Phone</span>
                        <span class="mobile-value">${escapeHtml(user.phone || '-')}</span>
                    </div>
                    <div class="mobile-grid-item">
                        <span class="mobile-label">Registration</span>
                        <span class="mobile-value">${formatDateTime(user.created_at)}</span>
                    </div>
                    <div class="mobile-grid-item">
                        <span class="mobile-label">Last Login</span>
                        <span class="mobile-value">${formatDateTime(user.current_login)}</span>
                    </div>
                    <div class="mobile-grid-item">
                        <span class="mobile-label">Last Seen</span>
                        <span class="mobile-value">${timeAgo(user.current_login)}</span>
                    </div>
                </div>
            </div>
        `;

            mobileContainer.appendChild(mobileCard);
        });
    }

    // Render pagination controls
    function renderPagination(pagination) {
        const paginationContainer = document.getElementById('pagination-numbers');
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');

        // Update pagination info
        document.getElementById('showing-start').textContent = pagination.total > 0 ? ((pagination.page - 1) * pagination.limit) + 1 : 0;
        document.getElementById('showing-end').textContent = Math.min(pagination.page * pagination.limit, pagination.total);
        document.getElementById('total-users').textContent = pagination.total;

        // Update current page and total pages
        currentPage = pagination.page;
        totalPages = pagination.pages;

        // Enable/disable prev/next buttons
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        // Clear pagination container
        paginationContainer.innerHTML = '';

        // Generate pagination numbers
        if (totalPages <= 5) {
            // Show all pages if 5 or fewer
            for (let i = 1; i <= totalPages; i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }
        } else {
            // Show first page
            paginationContainer.appendChild(createPaginationButton(1));

            // Show ellipsis if current page is more than 3
            if (currentPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            // Show pages around current page
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }

            // Show ellipsis if current page is less than totalPages - 2
            if (currentPage < totalPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            // Show last page
            paginationContainer.appendChild(createPaginationButton(totalPages));
        }
    }

    // Create pagination button
    function createPaginationButton(pageNumber) {
        const button = document.createElement('button');
        button.className = pageNumber === currentPage ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;

        button.addEventListener('click', function () {
            if (pageNumber !== currentPage) {
                currentPage = pageNumber;
                loadUsers();
            }
        });

        return button;
    }

    // Show user details in modal
    function showUserDetails(userId) {
        const modal = document.getElementById('userDetailsModal');
        const contentContainer = document.getElementById('userDetailsContent');

        // Show loading indicator
        contentContainer.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin"></div>
            </div>
        `;

        // Show modal
        modal.classList.remove('hidden');
        setTimeout(function () {
            modal.querySelector('.transform').classList.remove('translate-x-full');
        }, 10);

        // Fetch user details
        fetch(`${BASE_URL}admin/fetch/manageDashboard.php?action=getUserDetails&id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        hideUserDetails();
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const user = data.data;
                    renderUserDetails(user);
                } else {
                    contentContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                            <p class="text-gray-700">Error loading user details: ${data.error || 'Unknown error'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error fetching user details:', error);
                    contentContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                            <p class="text-gray-700">Failed to load user details. Please try again.</p>
                        </div>
                    `;
                }
            });
    }

    // Render user details in modal
    function renderUserDetails(user) {
        document.getElementById('modal-user-title').textContent = `User: ${user.username}`;

        const initials = getUserInitials(user.username);

        let statusBadge = '';
        if (user.status === 'active') {
            statusBadge = '<span class="status-badge status-active"><span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1"></span>Active</span>';
        } else if (user.status === 'inactive') {
            statusBadge = '<span class="status-badge status-inactive"><span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1"></span>Inactive</span>';
        } else {
            statusBadge = '<span class="status-badge status-suspended"><span class="w-1.5 h-1.5 rounded-full bg-amber-600 mr-1"></span>Suspended</span>';
        }

        const content = `
            <div class="space-y-6">
                <div class="flex flex-col items-center sm:flex-row sm:items-start gap-4">
                    <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-medium">
                        ${initials}
                    </div>
                    <div class="text-center sm:text-left">
                        <h4 class="text-xl font-semibold text-gray-900">${escapeHtml(user.username)}</h4>
                        <p class="text-gray-500">${escapeHtml(user.email)}</p>
                        <div class="mt-2">${statusBadge}</div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">User Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(user.phone || '-')}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Date</p>
                            <p class="text-sm font-medium text-gray-900">${formatDateTime(user.created_at)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Last Login</p>
                            <p class="text-sm font-medium text-gray-900">${formatDateTime(user.current_login)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Account Status</p>
                            <p class="text-sm font-medium text-gray-900">${user.status.charAt(0).toUpperCase() + user.status.slice(1)}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">Login History</h5>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">Current Login</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDateTime(user.current_login)} (${timeAgo(user.current_login)})</p>
                        </div>
                        ${user.last_login ? `
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">Previous Login</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDateTime(user.last_login)} (${timeAgo(user.last_login)})</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">Actions</h5>
                    <div class="flex flex-wrap gap-3">
                        <button class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-envelope mr-2"></i> Send Email
                        </button>
                        ${user.status === 'active' ? `
                        <button class="px-4 py-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors">
                            <i class="fas fa-ban mr-2"></i> Suspend User
                        </button>
                        ` : `
                        <button class="px-4 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition-colors">
                            <i class="fas fa-check-circle mr-2"></i> Activate User
                        </button>
                        `}
                        <button class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                            <i class="fas fa-trash-alt mr-2"></i> Delete User
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('userDetailsContent').innerHTML = content;
    }

    // Hide user details modal
    function hideUserDetails() {
        const modal = document.getElementById('userDetailsModal');
        modal.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(function () {
            modal.classList.add('hidden');
        }, 300);
    }

    // Show session expired modal
    function showSessionExpiredModal() {
        const modal = document.getElementById('sessionExpiredModal');
        modal.classList.remove('hidden');

        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        countdownElement.textContent = countdown;

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
    }

    // Redirect to login page
    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function () {
        // Update live time
        updateLiveTime();
        setInterval(updateLiveTime, 1000);

        // Load initial data
        loadStats();
        loadUsers();

        // Set up event listeners
        document.getElementById('searchUsers').addEventListener('input', function () {
            currentSearch = this.value.trim();
            currentPage = 1;
            loadUsers();
        });

        document.getElementById('sortUsers').addEventListener('change', function () {
            currentSort = this.value;
            currentPage = 1;
            loadUsers();
        });

        document.getElementById('filterStatus').addEventListener('change', function () {
            currentFilter = this.value;
            currentPage = 1;
            loadUsers();
        });

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadUsers();
            }
        });

        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                loadUsers();
            }
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>