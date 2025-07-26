<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Contact Messages';
$activeNav = 'contact-us-messages';
ob_start();
?>

<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Contact Messages</h1>
                        <div id="connectionStatus"
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-green-700">Live Updates</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Manage and respond to customer
                        inquiries</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Messages</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalMessages">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-envelope text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Today's Messages</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="todayMessages">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-calendar-day text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Registered Users</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="registeredUsers">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-check text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Weekly</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="weekMessages">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chart-line text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Time Period</h2>
                    <p class="text-sm text-gray-600">Filter messages by date range</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="today">
                            <i class="fas fa-calendar-day mr-2"></i>Today
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="week">
                            <i class="fas fa-calendar-week mr-2"></i> Weekly
                        </button>
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
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
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Contact Messages</h3>
                        <p class="text-sm text-gray-600">Click on any message to view details</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search messages..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="statusFilter"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                            <option value="all">All Messages</option>
                            <option value="registered">Registered Users</option>
                            <option value="guests">Guest Messages</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="messagesTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Contact Info</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Subject</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Message Preview</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                User Type</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Date</th>
                        </tr>
                    </thead>

                    <tbody id="messagesBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading messages...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> messages
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

            <div class="lg:hidden" id="messagesCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading messages...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingCount">0</span> of <span id="mobileTotalCount">0</span> messages
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="messageModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeMessageModal()"></div>
    <div
        class="relative w-full h-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div id="modalAvatar"
                    class="flex-shrink-0 h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Message Details</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Contact inquiry</p>
                </div>
            </div>
            <button onclick="closeMessageModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-100px)]" id="messageContent">
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">Loading message content...</p>
            </div>
        </div>
    </div>
</div>

<script>
    let messages = [];
    let currentMessageId = null;
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'month';
    let totalMessages = 0;

    // Add auto-refresh functionality
    let autoRefreshInterval;

    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            loadMessagesData();
        }, 5000); // Refresh every 5 seconds
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }

    // Start auto-refresh when page loads
    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        initializeDateFilters();
        loadMessagesData();
        startAutoRefresh();
    });

    // Stop auto-refresh when page is hidden
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });

    function setupEventListeners() {
        document.getElementById('refreshBtn').addEventListener('click', refreshMessages);
        document.getElementById('searchFilter').addEventListener('input', debounce(filterMessages, 300));
        document.getElementById('statusFilter').addEventListener('change', filterMessages);

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
                loadMessagesData();
            }
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadMessagesData();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadMessagesData();
        });

        document.getElementById('mobilePrevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadMessagesData();
            }
        });

        document.getElementById('mobileNextPage').addEventListener('click', function () {
            currentPage++;
            loadMessagesData();
        });
    }

    async function loadMessagesData() {
        try {
            const params = new URLSearchParams({
                action: 'get',
                page: currentPage,
                limit: itemsPerPage,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod
            });

            const response = await fetch(`fetch/manageContactusMessages.php?${params}`);
            const data = await response.json();

            if (data.success) {
                messages = data.data;
                totalMessages = data.total;
                updateStatistics(data.stats);
                renderMessagesTable();
                renderMessagesCards();
                updatePagination(data.total, data.page);
            } else {
                showError('Failed to load messages');
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            showError('Failed to load messages');
        }
    }

    function updateStatistics(stats) {
        if (stats) {
            document.getElementById('totalMessages').textContent = stats.total_messages.toLocaleString();
            document.getElementById('todayMessages').textContent = stats.today_messages.toLocaleString();
            document.getElementById('registeredUsers').textContent = stats.registered_users.toLocaleString();
            document.getElementById('weekMessages').textContent = stats.week_messages.toLocaleString();
        }
    }

    function renderMessagesTable() {
        const tbody = document.getElementById('messagesBody');

        if (messages.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <div>No messages found</div>
                    </td>
                </tr>
            `;
            return;
        }

        let filteredMessages = filterMessagesData();

        tbody.innerHTML = filteredMessages.map(message => {
            const messagePreview = message.message.length > 100 ? message.message.substring(0, 100) + '...' : message.message;
            const isRegistered = message.user_id !== null;

            return `
                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewMessageDetails('${message.id}')">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full ${isRegistered ? 'bg-green-100' : 'bg-blue-100'} flex items-center justify-center">
                                <i class="${isRegistered ? 'fas fa-user' : 'fas fa-envelope'} ${isRegistered ? 'text-green-600' : 'text-blue-600'} text-sm"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-secondary">${message.name}</div>
                                <div class="text-sm text-gray-500">${message.email}</div>
                                ${message.phone ? `<div class="text-xs text-gray-400">${message.phone}</div>` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-secondary">${message.subject}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-900">${messagePreview}</div>
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        ${isRegistered ?
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-user-check mr-1"></i>Registered
                            </span>` :
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-envelope mr-1"></i>Guest
                            </span>`
                }
                    </td>
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        <div class="text-sm text-gray-900">${formatDate(message.created_at)}</div>
                        <div class="text-xs text-gray-500">${formatTime(message.created_at)}</div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderMessagesCards() {
        const container = document.getElementById('messagesCards');

        if (messages.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <div>No messages found</div>
                </div>
            `;
            return;
        }

        let filteredMessages = filterMessagesData();

        container.innerHTML = filteredMessages.map(message => {
            const messagePreview = message.message.length > 80 ? message.message.substring(0, 80) + '...' : message.message;
            const isRegistered = message.user_id !== null;

            return `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" onclick="viewMessageDetails('${message.id}')">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full ${isRegistered ? 'bg-green-100' : 'bg-blue-100'} flex items-center justify-center">
                            <i class="${isRegistered ? 'fas fa-user' : 'fas fa-envelope'} ${isRegistered ? 'text-green-600' : 'text-blue-600'} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="text-sm font-medium text-secondary truncate">${message.name}</h4>
                                <span class="text-xs text-gray-500">${formatDate(message.created_at)}</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900 mb-1">${message.subject}</div>
                            <div class="text-sm text-gray-600 mb-2">${messagePreview}</div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    ${isRegistered ?
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-user-check mr-1"></i>Registered
                                        </span>` :
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-envelope mr-1"></i>Guest
                                        </span>`
                }
                                </div>
                                <span class="text-xs text-gray-500">${formatTime(message.created_at)}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function filterMessagesData() {
        const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;

        return messages.filter(message => {
            const matchesSearch = !searchTerm ||
                message.name.toLowerCase().includes(searchTerm) ||
                message.email.toLowerCase().includes(searchTerm) ||
                message.subject.toLowerCase().includes(searchTerm) ||
                message.message.toLowerCase().includes(searchTerm) ||
                (message.phone && message.phone.includes(searchTerm));

            let matchesStatus = true;
            switch (statusFilter) {
                case 'registered':
                    matchesStatus = message.user_id !== null;
                    break;
                case 'guests':
                    matchesStatus = message.user_id === null;
                    break;
            }

            return matchesSearch && matchesStatus;
        });
    }

    function filterMessages() {
        renderMessagesTable();
        renderMessagesCards();
    }

    async function viewMessageDetails(messageId) {
        currentMessageId = messageId;
        document.getElementById('messageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        try {
            const response = await fetch(`fetch/manageContactusMessages.php?action=get_single&id=${messageId}`);
            const data = await response.json();

            if (data.success) {
                loadMessageDetails(data.data);
            } else {
                showError('Failed to load message details');
            }
        } catch (error) {
            console.error('Error loading message details:', error);
            showError('Failed to load message details');
        }
    }

    function loadMessageDetails(message) {
        const isRegistered = message.user_id !== null;

        document.getElementById('modalTitle').textContent = message.name;
        document.getElementById('modalSubtitle').textContent = `${message.email} • ${formatDateTime(message.created_at)}`;

        // Update avatar
        const modalAvatar = document.getElementById('modalAvatar');
        if (isRegistered) {
            modalAvatar.innerHTML = '<i class="fas fa-user text-green-600"></i>';
            modalAvatar.className = 'flex-shrink-0 h-12 w-12 rounded-full bg-green-100 flex items-center justify-center';
        } else {
            modalAvatar.innerHTML = '<i class="fas fa-envelope text-blue-600"></i>';
            modalAvatar.className = 'flex-shrink-0 h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center';
        }

        const messageContent = document.getElementById('messageContent');
        messageContent.innerHTML = `
        <div class="space-y-6">
            <!-- Contact Information Card -->
            <div class="bg-gray-50 rounded-lg p-4 sm:p-6">
                <h4 class="text-lg font-semibold text-secondary mb-4 flex items-center gap-2">
                    <i class="fas fa-address-card text-primary"></i>
                    Contact Information
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Full Name</label>
                        <p class="text-gray-900 font-medium">${message.name}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Email Address</label>
                        <p class="text-gray-900 font-medium">${message.email}</p>
                    </div>
                    ${message.phone ? `
                        <div>
                            <label class="text-sm font-medium text-gray-600">Phone Number</label>
                            <p class="text-gray-900 font-medium">${message.phone}</p>
                        </div>
                    ` : ''}
                    <div>
                        <label class="text-sm font-medium text-gray-600">Message Date</label>
                        <p class="text-gray-900 font-medium">${formatDateTime(message.created_at)}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">User Type</label>
                        ${isRegistered ?
                '<p class="text-green-600 font-medium flex items-center gap-1"><i class="fas fa-user-check"></i> Registered User</p>' :
                '<p class="text-blue-600 font-medium flex items-center gap-1"><i class="fas fa-envelope"></i> Guest</p>'
            }
                    </div>
                    ${isRegistered && message.user_info && message.user_info.last_login ? `
                        <div>
                            <label class="text-sm font-medium text-gray-600">Last Login</label>
                            <p class="text-gray-900 font-medium">${formatDateTime(message.user_info.last_login)}</p>
                        </div>
                    ` : ''}
                </div>
                
                ${isRegistered && message.user_info ? `
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h5 class="text-md font-medium text-secondary mb-3">User Account Details</h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Username</label>
                                <p class="text-gray-900 font-medium">${message.user_info.username}</p>
                            </div>
                            ${message.user_info.first_name || message.user_info.last_name ? `
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Full Name</label>
                                    <p class="text-gray-900 font-medium">${(message.user_info.first_name || '') + ' ' + (message.user_info.last_name || '')}</p>
                                </div>
                            ` : ''}
                            <div>
                                <label class="text-sm font-medium text-gray-600">Account Status</label>
                                <p class="text-gray-900 font-medium capitalize">${message.user_info.status}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Member Since</label>
                                <p class="text-gray-900 font-medium">${formatDate(message.user_info.created_at)}</p>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>

            <!-- Subject Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-secondary mb-3 flex items-center gap-2">
                    <i class="fas fa-tag text-primary"></i>
                    Subject
                </h4>
                <p class="text-gray-900 text-base">${message.subject}</p>
            </div>

            <!-- Message Card -->
            <div class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200">
                <h4 class="text-lg font-semibold text-secondary mb-3 flex items-center gap-2">
                    <i class="fas fa-comment-alt text-primary"></i>
                    Message
                </h4>
                <div class="prose prose-sm max-w-none">
                    <p class="text-gray-900 whitespace-pre-wrap leading-relaxed text-base">${message.message}</p>
                </div>
            </div>
        </div>
    `;
    }

    function closeMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.body.style.overflow = '';
        currentMessageId = null;
    }

    function initializeDateFilters() {
        setDateRangeForPeriod('month');
    }

    function setDateRangeForPeriod(period) {
        // Get current date in Africa/Kampala timezone
        const now = new Date();
        const kampalaOffset = 3 * 60; // UTC+3 in minutes
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
        loadMessagesData();
    }

    function refreshMessages() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadMessagesData().finally(() => {
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }, 1000);
        });
    }

    function showError(message) {
        const tbody = document.getElementById('messagesBody');
        const cards = document.getElementById('messagesCards');

        const errorContent = `
            <div class="px-4 py-8 text-center text-red-500">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <div>${message}</div>
            </div>
        `;

        tbody.innerHTML = `<tr><td colspan="5">${errorContent}</td></tr>`;
        cards.innerHTML = errorContent;
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

        document.getElementById('mobileShowingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('mobileTotalCount').textContent = total;
        document.getElementById('mobilePageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('mobilePrevPage').disabled = page === 1;
        document.getElementById('mobileNextPage').disabled = page === totalPages || totalPages === 0;
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

    window.viewMessageDetails = viewMessageDetails;
    window.closeMessageModal = closeMessageModal;
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

    .quick-response-btn:hover {
        background-color: #e5e7eb;
    }

    .prose p {
        margin-bottom: 1rem;
    }

    .prose p:last-child {
        margin-bottom: 0;
    }

    #responseInput:focus {
        outline: none;
        border-color: #D92B13;
        box-shadow: 0 0 0 3px rgba(217, 43, 19, 0.1);
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>