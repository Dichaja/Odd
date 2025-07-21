<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'System Users Management';
$activeNav = 'system-users';

ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">System Users Management</h1>
                <p class="text-gray-600 mt-1">Manage user accounts and their store relationships</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Users</p>
                        <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="totalUsers">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active Users</p>
                        <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="activeUsers">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Store Owners</p>
                        <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="storeOwners">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-store text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Store Managers</p>
                        <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="storeManagers">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">User Accounts</h3>
                        <p class="text-sm text-gray-600">View and manage user details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search users..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="sortFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="current_login:DESC">Last Login (Recent First)</option>
                            <option value="current_login:ASC">Last Login (Oldest First)</option>
                            <option value="created_at:DESC">Date Created (Newest First)</option>
                            <option value="created_at:ASC">Date Created (Oldest First)</option>
                            <option value="username:ASC">Username (A-Z)</option>
                            <option value="username:DESC">Username (Z-A)</option>
                            <option value="email:ASC">Email (A-Z)</option>
                            <option value="email:DESC">Email (Z-A)</option>
                            <option value="stores_owned:DESC">Most Stores Owned</option>
                            <option value="stores_managed:DESC">Most Stores Managed</option>
                        </select>
                        <button id="refreshBtn"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-sync-alt"></i>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-full" id="usersTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                User Details
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Contact
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Stores Owned
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Stores Managed
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Last Login
                            </th>
                        </tr>
                    </thead>
                    <tbody id="usersBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading users...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> users
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
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="userModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div
        class="relative w-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">User Report</h3>
                <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Manage user details and store relationships</p>
            </div>
            <div class="flex items-center gap-3">
                <button id="editUserBtn" class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white/50"
                    title="Edit User">
                    <i class="fas fa-edit text-lg"></i>
                </button>
                <button onclick="closeUserModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div id="userContent" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Fetching user details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-question-circle text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900" id="confirmTitle">Confirm Action</h3>
                    <p class="text-sm text-gray-600" id="confirmText">Are you sure?</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeConfirmModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div id="messageIcon" class="w-10 h-10 rounded-full flex items-center justify-center">
                    <i id="messageIconClass"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900" id="messageTitle">Message</h3>
                    <p class="text-sm text-gray-600" id="messageText">Message content</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeMessageModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentUserData = null;
    let currentUserId = null;
    let isEditMode = false;

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        loadUsers();
    });

    function setupEventListeners() {
        document.getElementById('searchFilter').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadUsers();
        }, 500));

        document.getElementById('sortFilter').addEventListener('change', () => {
            currentPage = 1;
            loadUsers();
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadUsers();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadUsers();
        });

        document.getElementById('refreshBtn').addEventListener('click', refreshData);

        document.getElementById('userModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeUserModal();
            }
        });

        document.getElementById('confirmModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        document.getElementById('messageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });

        document.getElementById('editUserBtn').addEventListener('click', toggleEditMode);
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

    function loadUsers() {
        const sortValue = document.getElementById('sortFilter').value.split(':');
        const sortBy = sortValue[0];
        const sortOrder = sortValue[1];

        const params = new URLSearchParams({
            action: 'getUsers',
            search: document.getElementById('searchFilter').value,
            sortBy: sortBy,
            sortOrder: sortOrder,
            page: currentPage,
            limit: itemsPerPage
        });

        fetch(`fetch/manageSystemUsers.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsersTable(data.users || [], data.total || 0, data.page || 1);
                    updateStatistics(data.stats || {});
                } else {
                    console.error('Error loading users:', data.message);
                    showError('Failed to load users: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred while loading users');
            });
    }

    function showError(message) {
        const tbody = document.getElementById('usersBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <div>${message}</div>
                </td>
            </tr>
        `;
    }

    function updateStatistics(stats) {
        document.getElementById('totalUsers').textContent = parseInt(stats.totalUsers || 0).toLocaleString();
        document.getElementById('activeUsers').textContent = parseInt(stats.activeUsers || 0).toLocaleString();
        document.getElementById('storeOwners').textContent = parseInt(stats.storeOwners || 0).toLocaleString();
        document.getElementById('storeManagers').textContent = parseInt(stats.storeManagers || 0).toLocaleString();
    }

    function renderUsersTable(users, total, page) {
        const tbody = document.getElementById('usersBody');

        if (!users || users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <div>No users found</div>
                    </td>
                </tr>
            `;
            updatePagination(0, 1);
            return;
        }

        tbody.innerHTML = users.map(user => {
            const statusBadge = getStatusBadge(user.status);
            const lastLogin = formatLastLogin(user.current_login);

            return `
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewUserDetails('${user.id}')">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-gray-600"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 text-sm">${user.username || 'N/A'}</div>
                                <div class="text-xs text-gray-500">${(user.first_name || '') + ' ' + (user.last_name || '')}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="text-sm">
                            <div class="text-gray-900">${user.email || 'N/A'}</div>
                            <div class="text-gray-500">${user.phone || 'N/A'}</div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${user.stores_owned || 0}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            ${user.stores_managed || 0}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${statusBadge}
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${lastLogin}
                    </td>
                </tr>
            `;
        }).join('');

        updatePagination(total, page);
    }

    function getStatusBadge(status) {
        const statusLower = (status || '').toLowerCase();
        if (statusLower === 'active') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>';
        } else if (statusLower === 'inactive') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>';
        } else if (statusLower === 'suspended') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Suspended</span>';
        }
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function formatLastLogin(dateString) {
        if (!dateString) {
            return '<div class="text-xs text-gray-900">Never</div>';
        }

        const date = new Date(dateString);
        const dateStr = date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
        const timeStr = date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        return `
            <div class="text-xs">
                <div class="text-gray-900">${dateStr}</div>
                <div class="text-gray-500">${timeStr}</div>
            </div>
        `;
    }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);

        document.getElementById('showingCount').textContent = total > 0 ? `${startIndex + 1}-${endIndex}` : '0';
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;
    }

    function viewUserDetails(userId) {
        currentUserId = userId;
        isEditMode = false;
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('editUserBtn').innerHTML = '<i class="fas fa-edit text-lg"></i>';

        document.getElementById('userContent').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                    <p class="text-gray-600">Fetching user details...</p>
                </div>
            </div>
        `;

        fetch(`fetch/manageSystemUsers.php?action=getUserDetails&id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentUserData = data;
                    showUserModal(data.user);
                } else {
                    document.getElementById('userContent').innerHTML = `
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center text-red-500">
                                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                                <p>Failed to load user details</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('userContent').innerHTML = `
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                            <p>An error occurred while loading details</p>
                        </div>
                    </div>
                `;
            });
    }

    function showUserModal(user) {
        const content = document.getElementById('userContent');
        const canEdit = user.status !== 'deleted';

        const emailLink = user.email && user.email !== 'N/A' ?
            `<a href="mailto:${user.email}" class="text-blue-600 hover:text-blue-800 underline">${user.email}</a>` :
            (user.email || 'N/A');

        const phoneLink = user.phone && user.phone !== 'N/A' ?
            `<a href="tel:${user.phone}" class="text-blue-600 hover:text-blue-800 underline">${user.phone}</a>` :
            (user.phone || 'N/A');

        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">User Information</h4>
                            <div class="space-y-3 text-sm" id="userInfoSection">
                                <div>
                                    <span class="text-gray-600">Username:</span> 
                                    <span class="font-medium" id="usernameDisplay">${user.username || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">First Name:</span> 
                                    <span class="font-medium" id="firstNameDisplay">${user.first_name || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Name:</span> 
                                    <span class="font-medium" id="lastNameDisplay">${user.last_name || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Email:</span> 
                                    <span class="font-medium" id="emailDisplay">${emailLink}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Phone:</span> 
                                    <span class="font-medium" id="phoneDisplay">${phoneLink}</span>
                                </div>
                                <div><span class="text-gray-600">Status:</span> ${getStatusBadge(user.status)}</div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Store Relationships</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Stores Owned:</span> <span class="font-medium">${user.stores_owned || 0}</span></div>
                                <div><span class="text-gray-600">Stores Managed:</span> <span class="font-medium">${user.stores_managed || 0}</span></div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Account Activity</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Created:</span> <span class="font-medium">${formatDateTime(user.created_at)}</span></div>
                                <div><span class="text-gray-600">Last Login:</span> <span class="font-medium">${formatDateTime(user.current_login)}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${user.owned_stores && user.owned_stores.length > 0 ? `
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Owned Stores</h4>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Store Name</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Location</th>
                                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Status</th>
                                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Created</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${user.owned_stores.map(store => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${store.name || 'N/A'}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">${(store.district || '') + ', ' + (store.region || '')}</td>
                                            <td class="px-4 py-3 text-center">${getStatusBadge(store.status)}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-center">${formatDateTime(store.created_at).split(' at')[0]}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}

                ${user.managed_stores && user.managed_stores.length > 0 ? `
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Managed Stores</h4>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Store Name</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Role</th>
                                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Status</th>
                                        <th class="px-4 py-3 text-center text-sm font-medium text-gray-600">Added</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    ${user.managed_stores.map(store => `
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${store.store_name || 'N/A'}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">${store.role || 'N/A'}</td>
                                            <td class="px-4 py-3 text-center">${getStatusBadge(store.status)}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600 text-center">${formatDateTime(store.created_at).split(' at')[0]}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-3">
                        ${canEdit ? `
                            <button onclick="confirmUserAction('${user.id}', '${user.status}')" 
                                class="px-4 py-2 ${user.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'} text-white rounded-lg transition-colors">
                                ${user.status === 'active' ? 'Suspend User' : 'Activate User'}
                            </button>
                        ` : ''}
                    </div>
                    <button onclick="closeUserModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        `;
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }) + ' at ' + date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function toggleEditMode() {
        if (!currentUserData || !currentUserData.user) return;

        const user = currentUserData.user;
        isEditMode = !isEditMode;
        const editBtn = document.getElementById('editUserBtn');

        if (isEditMode) {
            editBtn.innerHTML = '<i class="fas fa-save text-lg"></i>';
            showEditForm(user);
        } else {
            editBtn.innerHTML = '<i class="fas fa-edit text-lg"></i>';
            showUserModal(user);
        }
    }

    function showEditForm(user) {
        const userInfoSection = document.getElementById('userInfoSection');

        userInfoSection.innerHTML = `
            <form id="editUserForm" class="space-y-3">
                <div>
                    <label class="text-gray-600 text-sm">Username:</label>
                    <input type="text" id="editUsername" value="${user.username || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">First Name:</label>
                    <input type="text" id="editFirstName" value="${user.first_name || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Last Name:</label>
                    <input type="text" id="editLastName" value="${user.last_name || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Email:</label>
                    <input type="email" id="editEmail" value="${user.email || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Phone:</label>
                    <input type="text" id="editPhone" value="${user.phone || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="pt-3">
                    <button type="button" onclick="saveUserChanges()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 mr-2">
                        Save Changes
                    </button>
                    <button type="button" onclick="toggleEditMode()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </form>
        `;
    }

    function saveUserChanges() {
        const formData = new FormData();
        formData.append('action', 'updateUser');
        formData.append('id', currentUserId);
        formData.append('username', document.getElementById('editUsername').value);
        formData.append('first_name', document.getElementById('editFirstName').value);
        formData.append('last_name', document.getElementById('editLastName').value);
        formData.append('email', document.getElementById('editEmail').value);
        formData.append('phone', document.getElementById('editPhone').value);

        fetch('fetch/manageSystemUsers.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'User updated successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        viewUserDetails(currentUserId);
                        loadUsers();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to update user');
            });
    }

    function confirmUserAction(userId, currentStatus) {
        const action = currentStatus === 'active' ? 'suspend' : 'activate';
        const actionText = currentStatus === 'active' ? 'suspend' : 'activate';

        document.getElementById('confirmTitle').textContent = 'Confirm Action';
        document.getElementById('confirmText').textContent = `Are you sure you want to ${actionText} this user?`;

        document.getElementById('confirmActionBtn').onclick = function () {
            toggleUserStatus(userId, currentStatus);
        };

        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function toggleUserStatus(userId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'suspend';

        closeConfirmModal();

        fetch(`fetch/manageSystemUsers.php?action=${action}&id=${userId}`, {
            method: 'POST'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', `User ${action}d successfully`);
                    setTimeout(() => {
                        closeMessageModal();
                        if (currentUserId === userId) {
                            viewUserDetails(userId);
                        }
                        loadUsers();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', `Failed to ${action} user`);
            });
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.add('hidden');
        currentUserId = null;
        currentUserData = null;
        isEditMode = false;
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    function showMessage(type, title, message) {
        const modal = document.getElementById('messageModal');
        const icon = document.getElementById('messageIcon');
        const iconClass = document.getElementById('messageIconClass');
        const titleEl = document.getElementById('messageTitle');
        const textEl = document.getElementById('messageText');

        if (type === 'success') {
            icon.className = 'w-10 h-10 bg-green-100 rounded-full flex items-center justify-center';
            iconClass.className = 'fas fa-check text-green-600';
        } else if (type === 'error') {
            icon.className = 'w-10 h-10 bg-red-100 rounded-full flex items-center justify-center';
            iconClass.className = 'fas fa-exclamation-triangle text-red-600';
        } else {
            icon.className = 'w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center';
            iconClass.className = 'fas fa-info-circle text-blue-600';
        }

        titleEl.textContent = title;
        textEl.textContent = message;

        modal.classList.remove('hidden');
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
    }

    function refreshData() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadUsers();

        setTimeout(() => {
            icon.classList.remove('fa-spin');
            refreshBtn.disabled = false;
        }, 1000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>