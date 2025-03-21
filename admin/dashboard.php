<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
ob_start();
?>

<div class="space-y-6">
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Total Users</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-users">2,458</h3>
                    <p class="text-xs text-green-600"><i class="fas fa-arrow-up mr-1"></i>12% from last month</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-users text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Active Users</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-active">1,893</h3>
                    <p class="text-xs text-green-600"><i class="fas fa-arrow-up mr-1"></i>8% from last month</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-user-check text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">New Users (30d)</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-new">246</h3>
                    <p class="text-xs text-green-600"><i class="fas fa-arrow-up mr-1"></i>24% from last month</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                    <i class="fas fa-user-plus text-purple-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Inactive Users</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-inactive">565</h3>
                    <p class="text-xs text-red-500"><i class="fas fa-arrow-up mr-1"></i>3% from last month</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                    <i class="fas fa-user-slash text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">User Management</h2>
                <p class="text-sm text-gray-text mt-1">Manage and monitor user accounts</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchUsers" placeholder="Search users..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="sortUsers" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="register_date" selected>Sort by Registration Date</option>
                        <option value="last_login">Sort by Last Login</option>
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
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="selectAllUsers" class="rounded border-gray-300 text-primary focus:ring-primary">
                                <span>Username</span>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Phone</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Email</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Registration Date</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">In Use</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Last Login</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- Table rows will be populated by JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="responsive-table-mobile p-4" id="users-mobile">
            <!-- Mobile cards will be populated by JavaScript -->
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-users">100</span> users
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                    <span class="px-2">...</span>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">10</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideUserDetails()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-2xl bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="modal-user-title">User Details</h3>
                <button onclick="hideUserDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="userDetailsContent"></div>
            <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                <button onclick="hideUserDetails()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                    Close
                </button>
                <button id="editUserBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Edit User
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
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
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
        padding: 0.25rem 0.5rem;
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
</style>

<script>
    // Sample user data - in a real application, this would come from an API
    const users = [{
            id: 1,
            username: "johndoe",
            phone: "+256 701 234 567",
            email: "john.doe@example.com",
            registerDate: "2024-12-15T08:30:00",
            inUse: true,
            lastLogin: "2025-03-07T14:22:10",
            avatar: null
        },
        {
            id: 2,
            username: "janesmith",
            phone: "+256 702 345 678",
            email: "jane.smith@example.com",
            registerDate: "2024-11-20T10:15:00",
            inUse: true,
            lastLogin: "2025-03-08T09:45:30",
            avatar: null
        },
        {
            id: 3,
            username: "robertjohnson",
            phone: "+256 703 456 789",
            email: "robert.j@example.com",
            registerDate: "2025-01-05T15:45:00",
            inUse: true,
            lastLogin: "2025-03-06T16:10:45",
            avatar: null
        },
        {
            id: 4,
            username: "michaelbrown",
            phone: "+256 704 567 890",
            email: "michael.b@example.com",
            registerDate: "2024-10-10T09:20:00",
            inUse: false,
            lastLogin: "2025-02-15T11:30:20",
            avatar: null
        },
        {
            id: 5,
            username: "sarahwilliams",
            phone: "+256 705 678 901",
            email: "sarah.w@example.com",
            registerDate: "2025-02-18T13:10:00",
            inUse: true,
            lastLogin: "2025-03-08T08:15:40",
            avatar: null
        },
        {
            id: 6,
            username: "davidmiller",
            phone: "+256 706 789 012",
            email: "david.m@example.com",
            registerDate: "2024-09-25T11:05:00",
            inUse: false,
            lastLogin: "2025-01-20T14:50:15",
            avatar: null
        },
        {
            id: 7,
            username: "jenniferlee",
            phone: "+256 707 890 123",
            email: "jennifer.l@example.com",
            registerDate: "2025-01-30T16:40:00",
            inUse: true,
            lastLogin: "2025-03-07T17:25:30",
            avatar: null
        },
        {
            id: 8,
            username: "williamtaylor",
            phone: "+256 708 901 234",
            email: "william.t@example.com",
            registerDate: "2024-12-05T10:30:00",
            inUse: true,
            lastLogin: "2025-03-05T09:10:50",
            avatar: null
        },
        {
            id: 9,
            username: "elizabethmoore",
            phone: "+256 709 012 345",
            email: "elizabeth.m@example.com",
            registerDate: "2025-02-10T14:15:00",
            inUse: true,
            lastLogin: "2025-03-08T10:40:25",
            avatar: null
        },
        {
            id: 10,
            username: "jamesanderson",
            phone: "+256 710 123 456",
            email: "james.a@example.com",
            registerDate: "2024-11-15T09:50:00",
            inUse: false,
            lastLogin: "2025-02-28T13:05:35",
            avatar: null
        }
    ];

    // Format date to a readable format
    function formatDate(dateString) {
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
        const date = new Date(dateString);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Calculate time ago
    function timeAgo(dateString) {
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

    // Render user table
    function renderUserTable(userList) {
        const tableBody = document.getElementById('users-table-body');
        const mobileContainer = document.getElementById('users-mobile');

        tableBody.innerHTML = '';
        mobileContainer.innerHTML = '';

        userList.forEach(user => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const initials = getUserInitials(user.username);
            const statusBadge = user.inUse ?
                '<span class="status-badge status-active"><span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1"></span>Active</span>' :
                '<span class="status-badge status-inactive"><span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1"></span>Inactive</span>';

            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="user-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                                ${initials}
                            </div>
                            <span class="font-medium text-gray-900">${user.username}</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">${user.phone}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${user.email}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${formatDateTime(user.registerDate)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${statusBadge}</td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    <div class="flex flex-col">
                        <span>${formatDateTime(user.lastLogin)}</span>
                        <span class="text-xs text-gray-400">${timeAgo(user.lastLogin)}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button onclick="showUserDetails(${user.id})" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(tr);

            // Mobile card
            const mobileCard = document.createElement('div');
            mobileCard.className = 'mobile-card';

            mobileCard.innerHTML = `
                <div class="mobile-card-header">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                            ${initials}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${user.username}</div>
                            <div class="text-xs text-gray-500">${user.email}</div>
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
                            <span class="mobile-value">${user.phone}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Registration</span>
                            <span class="mobile-value">${formatDateTime(user.registerDate)}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Last Login</span>
                            <span class="mobile-value">${formatDateTime(user.lastLogin)}</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Last Seen</span>
                            <span class="mobile-value">${timeAgo(user.lastLogin)}</span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button onclick="showUserDetails(${user.id})" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                        <button class="px-3 py-1.5 text-xs bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;

            mobileContainer.appendChild(mobileCard);
        });

        // Update pagination info
        document.getElementById('showing-start').textContent = '1';
        document.getElementById('showing-end').textContent = userList.length;
        document.getElementById('total-users').textContent = userList.length;
    }

    // Show user details in modal
    function showUserDetails(userId) {
        const user = users.find(u => u.id === userId);
        if (!user) return;

        document.getElementById('modal-user-title').textContent = `User: ${user.username}`;

        const initials = getUserInitials(user.username);
        const statusBadge = user.inUse ?
            '<span class="status-badge status-active"><span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1"></span>Active</span>' :
            '<span class="status-badge status-inactive"><span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1"></span>Inactive</span>';

        const content = `
            <div class="space-y-6">
                <div class="flex flex-col items-center sm:flex-row sm:items-start gap-4">
                    <div class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-medium">
                        ${initials}
                    </div>
                    <div class="text-center sm:text-left">
                        <h4 class="text-xl font-semibold text-gray-900">${user.username}</h4>
                        <p class="text-gray-500">${user.email}</p>
                        <div class="mt-2">${statusBadge}</div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">User Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p class="text-sm font-medium text-gray-900">${user.phone}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Date</p>
                            <p class="text-sm font-medium text-gray-900">${formatDateTime(user.registerDate)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Last Login</p>
                            <p class="text-sm font-medium text-gray-900">${formatDateTime(user.lastLogin)}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Account Status</p>
                            <p class="text-sm font-medium text-gray-900">${user.inUse ? 'Active' : 'Inactive'}</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-100 pt-6">
                    <h5 class="font-medium text-gray-900 mb-4">Login History</h5>
                    <div class="space-y-4">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">Logged in from Chrome on Windows</p>
                            <p class="text-xs text-gray-500 mt-1">${formatDateTime(user.lastLogin)} (${timeAgo(user.lastLogin)})</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">Logged in from Firefox on macOS</p>
                            <p class="text-xs text-gray-500 mt-1">Mar 5, 2025, 09:45:12 AM (3 days ago)</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-sm text-gray-700">Logged in from Safari on iPhone</p>
                            <p class="text-xs text-gray-500 mt-1">Mar 2, 2025, 07:22:30 PM (6 days ago)</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('userDetailsContent').innerHTML = content;

        const modal = document.getElementById('userDetailsModal');
        modal.classList.remove('hidden');
        setTimeout(function() {
            modal.querySelector('.transform').classList.remove('translate-x-full');
        }, 10);
    }

    // Hide user details modal
    function hideUserDetails() {
        const modal = document.getElementById('userDetailsModal');
        modal.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(function() {
            modal.classList.add('hidden');
        }, 300);
    }

    // Sort users
    function sortUsers(field) {
        let sortedUsers = [...users];

        switch (field) {
            case 'username':
                sortedUsers.sort((a, b) => a.username.localeCompare(b.username));
                break;
            case 'register_date':
                sortedUsers.sort((a, b) => new Date(b.registerDate) - new Date(a.registerDate));
                break;
            case 'last_login':
                sortedUsers.sort((a, b) => new Date(b.lastLogin) - new Date(a.lastLogin));
                break;
            case 'status':
                sortedUsers.sort((a, b) => {
                    if (a.inUse === b.inUse) return 0;
                    return a.inUse ? -1 : 1;
                });
                break;
        }

        return sortedUsers;
    }

    // Search users
    function searchUsers(query) {
        if (!query) return users;

        query = query.toLowerCase();
        return users.filter(user =>
            user.username.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            user.phone.toLowerCase().includes(query)
        );
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initial render with default sort by registration date
        const sortedUsers = sortUsers('register_date');
        renderUserTable(sortedUsers);

        // Search functionality
        const searchInput = document.getElementById('searchUsers');
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            const filteredUsers = searchUsers(query);
            const sortField = document.getElementById('sortUsers').value;
            const sortedFilteredUsers = sortUsers(sortField);

            // Apply search filter to sorted users
            const finalUsers = query ?
                sortedFilteredUsers.filter(user =>
                    user.username.toLowerCase().includes(query) ||
                    user.email.toLowerCase().includes(query) ||
                    user.phone.toLowerCase().includes(query)
                ) : sortedFilteredUsers;

            renderUserTable(finalUsers);
        });

        // Sort functionality
        const sortSelect = document.getElementById('sortUsers');
        sortSelect.addEventListener('change', function() {
            const sortField = this.value;
            const sortedUsers = sortUsers(sortField);

            // Apply search filter if there's a search query
            const searchQuery = document.getElementById('searchUsers').value.trim();
            const filteredUsers = searchQuery ? searchUsers(searchQuery) : sortedUsers;

            renderUserTable(filteredUsers);
        });

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllUsers');
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Edit user button
        document.getElementById('editUserBtn').addEventListener('click', function() {
            alert('Edit user functionality would be implemented here');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>