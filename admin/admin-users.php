<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Admin Users Management';
$activeNav = 'admin-users';

ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Admin Users Management</h1>
                    <p class="text-gray-600 mt-1">Manage admin accounts and their permissions</p>
                </div>
                <button id="addUserBtn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span>Add Admin User</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Admins</p>
                        <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="totalAdmins">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users-cog text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active Admins</p>
                        <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="activeAdmins">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Super Admins</p>
                        <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="superAdmins">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Editors</p>
                        <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="editors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Admin Accounts</h3>
                        <p class="text-sm text-gray-600">Click on any row to view and manage admin details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search admins..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="sortFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="last_login:DESC">Last Login (Recent First)</option>
                            <option value="last_login:ASC">Last Login (Oldest First)</option>
                            <option value="created_at:DESC">Date Created (Newest First)</option>
                            <option value="created_at:ASC">Date Created (Oldest First)</option>
                            <option value="username:ASC">Username (A-Z)</option>
                            <option value="username:DESC">Username (Z-A)</option>
                            <option value="email:ASC">Email (A-Z)</option>
                            <option value="email:DESC">Email (Z-A)</option>
                            <option value="role:ASC">Role (A-Z)</option>
                            <option value="role:DESC">Role (Z-A)</option>
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
                <table class="w-full min-w-full" id="adminsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Admin Details
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Contact
                            </th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                Role
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
                    <tbody id="adminsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading admin users...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> admin users
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

<!-- Add/Edit Admin User Modal -->
<div id="adminModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div
        class="relative w-full max-w-4xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Admin User Details</h3>
                <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Manage admin user information and permissions
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button id="editAdminBtn" class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-white/50"
                    title="Edit Admin">
                    <i class="fas fa-edit text-lg"></i>
                </button>
                <button onclick="closeAdminModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div id="adminContent" class="p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Loading admin details...</p>
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
    let currentAdminData = null;
    let currentAdminId = null;
    let isEditMode = false;
    let isAddMode = false;

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        loadAdmins();
    });

    function setupEventListeners() {
        document.getElementById('searchFilter').addEventListener('input', debounce(() => {
            currentPage = 1;
            loadAdmins();
        }, 500));

        document.getElementById('sortFilter').addEventListener('change', () => {
            currentPage = 1;
            loadAdmins();
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadAdmins();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadAdmins();
        });

        document.getElementById('refreshBtn').addEventListener('click', refreshData);
        document.getElementById('addUserBtn').addEventListener('click', showAddAdminModal);

        document.getElementById('adminModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeAdminModal();
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

        document.getElementById('editAdminBtn').addEventListener('click', toggleEditMode);
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

    function loadAdmins() {
        const sortValue = document.getElementById('sortFilter').value.split(':');
        const sortBy = sortValue[0];
        const sortOrder = sortValue[1];

        const params = new URLSearchParams({
            action: 'getAdmins',
            search: document.getElementById('searchFilter').value,
            sortBy: sortBy,
            sortOrder: sortOrder,
            page: currentPage,
            limit: itemsPerPage
        });

        fetch(`fetch/manageAdminUsers.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAdminsTable(data.admins || [], data.total || 0, data.page || 1);
                    updateStatistics(data.stats || {});
                } else {
                    console.error('Error loading admins:', data.message);
                    showError('Failed to load admin users: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred while loading admin users');
            });
    }

    function showError(message) {
        const tbody = document.getElementById('adminsBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <div>${message}</div>
                </td>
            </tr>
        `;
    }

    function updateStatistics(stats) {
        document.getElementById('totalAdmins').textContent = parseInt(stats.totalAdmins || 0).toLocaleString();
        document.getElementById('activeAdmins').textContent = parseInt(stats.activeAdmins || 0).toLocaleString();
        document.getElementById('superAdmins').textContent = parseInt(stats.superAdmins || 0).toLocaleString();
        document.getElementById('editors').textContent = parseInt(stats.editors || 0).toLocaleString();
    }

    function renderAdminsTable(admins, total, page) {
        const tbody = document.getElementById('adminsBody');

        if (!admins || admins.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-users-cog text-2xl mb-2"></i>
                        <div>No admin users found</div>
                    </td>
                </tr>
            `;
            updatePagination(0, 1);
            return;
        }

        tbody.innerHTML = admins.map(admin => {
            const statusBadge = getStatusBadge(admin.status);
            const roleBadge = getRoleBadge(admin.role);
            const lastLogin = formatLastLogin(admin.last_login);

            return `
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewAdminDetails('${admin.id}')">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user-shield text-gray-600"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 text-sm">${admin.username || 'N/A'}</div>
                                <div class="text-xs text-gray-500">${(admin.first_name || '') + ' ' + (admin.last_name || '')}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="text-sm">
                            <div class="text-gray-900">${admin.email || 'N/A'}</div>
                            <div class="text-gray-500">${admin.phone || 'N/A'}</div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${roleBadge}
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

    function getRoleBadge(role) {
        const roleLower = (role || '').toLowerCase();
        if (roleLower === 'super_admin') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Super Admin</span>';
        } else if (roleLower === 'admin') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Admin</span>';
        } else if (roleLower === 'editor') {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Editor</span>';
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

    function showAddAdminModal() {
        currentAdminId = null;
        isEditMode = true;
        isAddMode = true;
        document.getElementById('adminModal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Add New Admin User';
        document.getElementById('modalSubtitle').textContent = 'Create a new admin user account';
        document.getElementById('editAdminBtn').style.display = 'none';

        showAddForm();
    }

    function viewAdminDetails(adminId) {
        currentAdminId = adminId;
        isEditMode = false;
        isAddMode = false;
        document.getElementById('adminModal').classList.remove('hidden');
        document.getElementById('modalTitle').textContent = 'Admin User Details';
        document.getElementById('modalSubtitle').textContent = 'Manage admin user information and permissions';
        document.getElementById('editAdminBtn').style.display = 'block';
        document.getElementById('editAdminBtn').innerHTML = '<i class="fas fa-edit text-lg"></i>';

        document.getElementById('adminContent').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                    <p class="text-gray-600">Fetching admin details...</p>
                </div>
            </div>
        `;

        fetch(`fetch/manageAdminUsers.php?action=getAdminDetails&id=${adminId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentAdminData = data;
                    showAdminModal(data.admin);
                } else {
                    document.getElementById('adminContent').innerHTML = `
                        <div class="flex items-center justify-center py-12">
                            <div class="text-center text-red-500">
                                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                                <p>Failed to load admin details</p>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('adminContent').innerHTML = `
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center text-red-500">
                            <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                            <p>An error occurred while loading details</p>
                        </div>
                    </div>
                `;
            });
    }

    function showAdminModal(admin) {
        const content = document.getElementById('adminContent');

        const emailLink = admin.email && admin.email !== 'N/A' ?
            `<a href="mailto:${admin.email}" class="text-blue-600 hover:text-blue-800 underline">${admin.email}</a>` :
            (admin.email || 'N/A');

        const phoneLink = admin.phone && admin.phone !== 'N/A' ?
            `<a href="tel:${admin.phone}" class="text-blue-600 hover:text-blue-800 underline">${admin.phone}</a>` :
            (admin.phone || 'N/A');

        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Admin Information</h4>
                            <div class="space-y-3 text-sm" id="adminInfoSection">
                                <div>
                                    <span class="text-gray-600">Username:</span> 
                                    <span class="font-medium" id="usernameDisplay">${admin.username || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">First Name:</span> 
                                    <span class="font-medium" id="firstNameDisplay">${admin.first_name || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Last Name:</span> 
                                    <span class="font-medium" id="lastNameDisplay">${admin.last_name || 'N/A'}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Email:</span> 
                                    <span class="font-medium" id="emailDisplay">${emailLink}</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Phone:</span> 
                                    <span class="font-medium" id="phoneDisplay">${phoneLink}</span>
                                </div>
                                <div><span class="text-gray-600">Role:</span> ${getRoleBadge(admin.role)}</div>
                                <div><span class="text-gray-600">Status:</span> ${getStatusBadge(admin.status)}</div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Account Activity</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-600">Created:</span> <span class="font-medium">${formatDateTime(admin.created_at)}</span></div>
                                <div><span class="text-gray-600">Last Login:</span> <span class="font-medium">${formatDateTime(admin.last_login)}</span></div>
                                <div><span class="text-gray-600">Current Login:</span> <span class="font-medium">${formatDateTime(admin.current_login)}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-3">
                        <button onclick="confirmAdminAction('${admin.id}', '${admin.status}')" 
                            class="px-4 py-2 ${admin.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'} text-white rounded-lg transition-colors">
                            ${admin.status === 'active' ? 'Suspend Admin' : 'Activate Admin'}
                        </button>
                        <button onclick="confirmDeleteAdmin('${admin.id}')" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Delete Admin
                        </button>
                    </div>
                    <button onclick="closeAdminModal()" 
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
        if (!currentAdminData || !currentAdminData.admin) return;

        const admin = currentAdminData.admin;
        isEditMode = !isEditMode;
        const editBtn = document.getElementById('editAdminBtn');

        if (isEditMode) {
            editBtn.innerHTML = '<i class="fas fa-save text-lg"></i>';
            showEditForm(admin);
        } else {
            editBtn.innerHTML = '<i class="fas fa-edit text-lg"></i>';
            showAdminModal(admin);
        }
    }

    function showAddForm() {
        const content = document.getElementById('adminContent');

        content.innerHTML = `
            <div class="space-y-6">
                <form id="addAdminForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                            <input type="text" id="addUsername" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" id="addEmail" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="addFirstName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="addLastName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-lg">+256</span>
                                <input type="text" id="addPhone" maxlength="9" pattern="[0-9]{9}" required
                                    placeholder="7XXXXXXXX"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                            <select id="addRole" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                            <input type="password" id="addPassword" required minlength="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                            <input type="password" id="addConfirmPassword" required minlength="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeAdminModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="button" onclick="saveNewAdmin()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Create Admin
                        </button>
                    </div>
                </form>
            </div>
        `;
    }

    function showEditForm(admin) {
        const adminInfoSection = document.getElementById('adminInfoSection');

        adminInfoSection.innerHTML = `
            <form id="editAdminForm" class="space-y-3">
                <div>
                    <label class="text-gray-600 text-sm">Username:</label>
                    <input type="text" id="editUsername" value="${admin.username || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">First Name:</label>
                    <input type="text" id="editFirstName" value="${admin.first_name || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Last Name:</label>
                    <input type="text" id="editLastName" value="${admin.last_name || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Email:</label>
                    <input type="email" id="editEmail" value="${admin.email || ''}" 
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Phone:</label>
                    <div class="flex mt-1">
                        <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-lg">+256</span>
                        <input type="text" id="editPhone" value="${admin.phone ? admin.phone.replace('+256', '') : ''}" maxlength="9" pattern="[0-9]{9}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">Role:</label>
                    <select id="editRole" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="admin" ${admin.role === 'admin' ? 'selected' : ''}>Admin</option>
                        <option value="editor" ${admin.role === 'editor' ? 'selected' : ''}>Editor</option>
                        <option value="super_admin" ${admin.role === 'super_admin' ? 'selected' : ''}>Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-gray-600 text-sm">New Password (leave blank to keep current):</label>
                    <input type="password" id="editPassword" minlength="6"
                        class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="pt-3">
                    <button type="button" onclick="saveAdminChanges()" 
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

    function saveNewAdmin() {
        const password = document.getElementById('addPassword').value;
        const confirmPassword = document.getElementById('addConfirmPassword').value;

        if (password !== confirmPassword) {
            showMessage('error', 'Error', 'Passwords do not match');
            return;
        }

        const phone = document.getElementById('addPhone').value;
        if (phone && !/^[0-9]{9}$/.test(phone)) {
            showMessage('error', 'Error', 'Phone number must be exactly 9 digits');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'createAdmin');
        formData.append('username', document.getElementById('addUsername').value);
        formData.append('first_name', document.getElementById('addFirstName').value);
        formData.append('last_name', document.getElementById('addLastName').value);
        formData.append('email', document.getElementById('addEmail').value);
        formData.append('phone', phone ? '+256' + phone : '');
        formData.append('role', document.getElementById('addRole').value);
        formData.append('password', password);

        fetch('fetch/manageAdminUsers.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'Admin user created successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        closeAdminModal();
                        loadAdmins();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to create admin user');
            });
    }

    function saveAdminChanges() {
        const phone = document.getElementById('editPhone').value;
        if (phone && !/^[0-9]{9}$/.test(phone)) {
            showMessage('error', 'Error', 'Phone number must be exactly 9 digits');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'updateAdmin');
        formData.append('id', currentAdminId);
        formData.append('username', document.getElementById('editUsername').value);
        formData.append('first_name', document.getElementById('editFirstName').value);
        formData.append('last_name', document.getElementById('editLastName').value);
        formData.append('email', document.getElementById('editEmail').value);
        formData.append('phone', phone ? '+256' + phone : '');
        formData.append('role', document.getElementById('editRole').value);

        const password = document.getElementById('editPassword').value;
        if (password) {
            formData.append('password', password);
        }

        fetch('fetch/manageAdminUsers.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'Admin user updated successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        viewAdminDetails(currentAdminId);
                        loadAdmins();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to update admin user');
            });
    }

    function confirmAdminAction(adminId, currentStatus) {
        const action = currentStatus === 'active' ? 'suspend' : 'activate';
        const actionText = currentStatus === 'active' ? 'suspend' : 'activate';

        document.getElementById('confirmTitle').textContent = 'Confirm Action';
        document.getElementById('confirmText').textContent = `Are you sure you want to ${actionText} this admin user?`;

        document.getElementById('confirmActionBtn').onclick = function () {
            toggleAdminStatus(adminId, currentStatus);
        };

        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function confirmDeleteAdmin(adminId) {
        document.getElementById('confirmTitle').textContent = 'Confirm Delete';
        document.getElementById('confirmText').textContent = 'Are you sure you want to delete this admin user? This action cannot be undone.';

        document.getElementById('confirmActionBtn').onclick = function () {
            deleteAdmin(adminId);
        };

        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function toggleAdminStatus(adminId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'suspend';

        closeConfirmModal();

        fetch(`fetch/manageAdminUsers.php?action=${action}&id=${adminId}`, {
            method: 'POST'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', `Admin user ${action}d successfully`);
                    setTimeout(() => {
                        closeMessageModal();
                        if (currentAdminId === adminId) {
                            viewAdminDetails(adminId);
                        }
                        loadAdmins();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', `Failed to ${action} admin user`);
            });
    }

    function deleteAdmin(adminId) {
        closeConfirmModal();

        fetch(`fetch/manageAdminUsers.php?action=deleteAdmin&id=${adminId}`, {
            method: 'POST'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'Admin user deleted successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        closeAdminModal();
                        loadAdmins();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to delete admin user');
            });
    }

    function closeAdminModal() {
        document.getElementById('adminModal').classList.add('hidden');
        currentAdminId = null;
        currentAdminData = null;
        isEditMode = false;
        isAddMode = false;
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

        loadAdmins();

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