<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Wallets';
$activeNav = 'zzimba-wallets';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <!-- Wallets View -->
    <div id="wallets-view" class="view-container active">
        <!-- Header Section -->
        <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-wallet text-primary text-lg"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-secondary font-rubik">Zzimba Wallets</h1>
                                <p class="text-sm text-gray-text">Manage user, vendor, and platform wallets</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">User Wallets
                                    </p>
                                    <p class="text-lg font-bold text-green-900" id="user-wallets-count">0</p>
                                </div>
                                <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user text-green-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Vendor
                                        Wallets</p>
                                    <p class="text-lg font-bold text-purple-900" id="vendor-wallets-count">0</p>
                                </div>
                                <div class="w-8 h-8 bg-purple-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-purple-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-cyan-50 to-cyan-100 rounded-xl p-4 border border-cyan-200 col-span-2 lg:col-span-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-cyan-600 uppercase tracking-wide">Total Balance
                                    </p>
                                    <p class="text-lg font-bold text-cyan-900" id="total-balance">UGX 0</p>
                                </div>
                                <div class="w-8 h-8 bg-cyan-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-coins text-cyan-600 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Controls Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-secondary font-rubik">Wallet Management</h2>
                            <p class="text-sm text-gray-text mt-1">View and manage all Zzimba wallets</p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <!-- Search -->
                            <div class="relative flex-1 sm:w-80">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchWallets"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Search wallets...">
                            </div>

                            <!-- Filter -->
                            <select id="filterWallets"
                                class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium">
                                <option value="all">All Types</option>
                                <option value="USER">User Wallets</option>
                                <option value="VENDOR">Vendor Wallets</option>
                                <option value="PLATFORM">Platform Wallets</option>
                            </select>

                            <!-- Create Button -->
                            <button id="create-wallet-btn"
                                class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-plus"></i>
                                <span>Create Wallet</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallets Grid/Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full" id="wallets-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Wallet Details</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Owner Type</th>
                                <th id="statusHeader"
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider cursor-pointer">
                                    Status
                                    <i id="statusSortIcon" class="fas fa-sort text-gray-400 ml-1"></i>
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Balance</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="wallets-table-body" class="divide-y divide-gray-100">
                            <!-- Populated via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden p-4 space-y-4" id="wallets-mobile">
                    <!-- Populated via JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-wallet text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No wallets found</h3>
                    <p class="text-gray-500 mb-6">Create a new wallet or adjust your search filters</p>
                    <button onclick="showCreateWalletForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                        Create Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Wallet Modal -->
<div id="createWalletOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideCreateWalletForm()">
    </div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Create Platform Wallet</h3>
                </div>
                <button onclick="hideCreateWalletForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <!-- Form -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="createWalletForm" class="space-y-6" onsubmit="return false;">
                    <div>
                        <label for="walletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet
                            Name</label>
                        <input type="text" id="walletName" name="walletName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter a descriptive name" required>
                        <p class="mt-2 text-sm text-gray-500">This wallet will be created as a platform wallet.</p>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideCreateWalletForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="submitWalletForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Create Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Wallet Modal -->
<div id="editWalletOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideEditWalletForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Wallet</h3>
                </div>
                <button onclick="hideEditWalletForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <!-- Form -->
            <div class="flex-1 overflow-y-auto p-6">
                <form id="editWalletForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editWalletId">

                    <!-- Owner Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Owner Type</label>
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700">
                            <span id="editWalletOwnerTypeDisplay" class="font-medium"></span>
                        </div>
                        <input type="hidden" id="editWalletOwnerType">
                    </div>

                    <!-- Wallet Name -->
                    <div>
                        <label for="editWalletName" class="block text-sm font-semibold text-gray-700 mb-2">Wallet
                            Name</label>
                        <input type="text" id="editWalletName" name="editWalletName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter wallet name" required>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="editWalletStatus"
                            class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editWalletStatus" value="active" class="sr-only peer">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Active</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editWalletStatus" value="inactive" class="sr-only peer">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Inactive</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200">
                                <input type="radio" name="editWalletStatus" value="suspended" class="sr-only peer">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Suspended</div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideEditWalletForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                        Cancel
                    </button>
                    <button id="updateWalletForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">
                        Update Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteWalletModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideDeleteConfirm()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this wallet? All associated data will be
                permanently removed.</p>
            <div class="flex gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmDeleteWalletBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    Delete Wallet
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .view-container {
        transition: all 0.3s ease-in-out;
    }

    .view-container.hidden {
        display: none;
        opacity: 0;
    }

    .view-container.active {
        display: block;
        opacity: 1;
    }

    .mobile-card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1rem;
        transition: all 0.2s;
    }

    .mobile-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -2px rgba(0, 0, 0, 0.1);
    }

    .mobile-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .mobile-card-content {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .mobile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .mobile-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .mobile-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        margin-top: 0.25rem;
    }

    .mobile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f3f4f6;
    }

    .wallet-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-USER {
        background-color: #bbf7d0;
        color: #065f46;
    }

    .badge-VENDOR {
        background-color: #f3e8ff;
        color: #6b21a8;
    }

    .badge-PLATFORM {
        background-color: #cffafe;
        color: #155e75;
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
        background-color: #bbf7d0;
        color: #065f46;
    }

    .status-inactive {
        background-color: #f3f4f6;
        color: #1f2937;
    }

    .status-suspended {
        background-color: #fee2e2;
        color: #991b1b;
    }

    input[type="radio"]:checked+div {
        border-color: #8c5e2a;
        background-color: #8c5e2a;
    }

    input[type="radio"]:checked+div>div {
        opacity: 1;
    }
</style>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageZzimbaWallets.php';
    let wallets = [];
    let statusSortAsc = true;

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getOwnerTypeBadge(type) {
        const badges = {
            'USER': '<span class="wallet-badge badge-USER"><i class="fas fa-user mr-2"></i>User</span>',
            'VENDOR': '<span class="wallet-badge badge-VENDOR"><i class="fas fa-store mr-2"></i>Vendor</span>',
            'PLATFORM': '<span class="wallet-badge badge-PLATFORM"><i class="fas fa-building mr-2"></i>Platform</span>'
        };
        return badges[type] || '<span class="wallet-badge">Unknown</span>';
    }

    function getStatusBadge(status) {
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        return `<span class="status-badge status-${status}">${statusText}</span>`;
    }

    function updateQuickStats() {
        const userWallets = wallets.filter(w => w.owner_type === 'USER').length;
        const vendorWallets = wallets.filter(w => w.owner_type === 'VENDOR').length;
        const totalBalance = wallets.reduce((sum, w) => sum + parseFloat(w.current_balance || 0), 0);

        document.getElementById('user-wallets-count').textContent = userWallets;
        document.getElementById('vendor-wallets-count').textContent = vendorWallets;
        document.getElementById('total-balance').textContent = `UGX ${formatCurrency(totalBalance)}`;
    }

    async function fetchWallets() {
        try {
            const res = await fetch(`${API_URL}?action=getZzimbaWallets`);
            const data = await res.json();
            if (data.success) {
                wallets = data.wallets || [];
                renderWalletsTable(wallets);
                updateQuickStats();
            }
        } catch (err) {
            console.error('Error fetching wallets:', err);
        }
    }

    function renderWalletsTable(list) {
        const tbody = document.getElementById('wallets-table-body');
        const mobile = document.getElementById('wallets-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach(wallet => {
            // Desktop row
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center ${wallet.owner_type === 'USER' ? 'bg-green-100' :
                    wallet.owner_type === 'VENDOR' ? 'bg-purple-100' : 'bg-cyan-100'
                }">
                            <i class="${wallet.owner_type === 'USER' ? 'fas fa-user text-green-600' :
                    wallet.owner_type === 'VENDOR' ? 'fas fa-store text-purple-600' : 'fas fa-building text-cyan-600'
                }"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">${wallet.wallet_name}</div>
                            <div class="text-sm text-gray-500">ID: ${wallet.wallet_id}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">${getOwnerTypeBadge(wallet.owner_type)}</td>
                <td class="px-6 py-4">${getStatusBadge(wallet.status)}</td>
                <td class="px-6 py-4 text-right">
                    <div class="font-bold text-lg text-gray-900">UGX ${formatCurrency(wallet.current_balance)}</div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="showEditWalletForm('${wallet.wallet_id}')" 
                            class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                            title="Edit Wallet">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        ${wallet.owner_type === 'PLATFORM' ? `
                            <button onclick="showDeleteConfirm('${wallet.wallet_id}')" 
                                class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                                title="Delete Wallet">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'mobile-card';
            card.innerHTML = `
                <div class="mobile-card-header">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center ${wallet.owner_type === 'USER' ? 'bg-green-100' :
                    wallet.owner_type === 'VENDOR' ? 'bg-purple-100' : 'bg-cyan-100'
                }">
                            <i class="${wallet.owner_type === 'USER' ? 'fas fa-user text-green-600' :
                    wallet.owner_type === 'VENDOR' ? 'fas fa-store text-purple-600' : 'fas fa-building text-cyan-600'
                }"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">${wallet.wallet_name}</div>
                            <div class="text-xs text-gray-500">ID: ${wallet.wallet_id}</div>
                        </div>
                    </div>
                    ${getOwnerTypeBadge(wallet.owner_type)}
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid">
                        <div>
                            <span class="mobile-label">Status</span>
                            <div class="mt-1">${getStatusBadge(wallet.status)}</div>
                        </div>
                        <div>
                            <span class="mobile-label">Balance</span>
                            <span class="mobile-value">UGX ${formatCurrency(wallet.current_balance)}</span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button onclick="showEditWalletForm('${wallet.wallet_id}')" 
                            class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        ${wallet.owner_type === 'PLATFORM' ? `
                            <button onclick="showDeleteConfirm('${wallet.wallet_id}')" 
                                class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                <i class="fas fa-trash-alt mr-1"></i>Delete
                            </button>
                        ` : ''}
                    </div>
                </div>`;
            mobile.appendChild(card);
        });
    }

    function sortByStatus() {
        wallets.sort((a, b) => {
            const sa = a.status.toLowerCase();
            const sb = b.status.toLowerCase();
            if (sa < sb) return statusSortAsc ? -1 : 1;
            if (sa > sb) return statusSortAsc ? 1 : -1;
            return 0;
        });
        statusSortAsc = !statusSortAsc;
        document.getElementById('statusSortIcon').className = statusSortAsc
            ? 'fas fa-sort-up text-gray-400 ml-1'
            : 'fas fa-sort-down text-gray-400 ml-1';
        renderWalletsTable(wallets);
    }

    function showCreateWalletForm() {
        const offcanvas = document.getElementById('createWalletOffcanvas');
        const form = document.getElementById('createWalletForm');

        // Reset form
        form.reset();

        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideCreateWalletForm() {
        const offcanvas = document.getElementById('createWalletOffcanvas');
        const panel = offcanvas.querySelector('.transform');

        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function createWallet() {
        const name = document.getElementById('walletName').value.trim();

        if (!name) {
            alert('Please enter a wallet name');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=createZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ owner_type: 'PLATFORM', wallet_name: name })
            });
            const data = await res.json();

            if (data.success) {
                hideCreateWalletForm();
                fetchWallets();
            } else {
                alert(data.message || 'Failed to create wallet');
            }
        } catch (err) {
            console.error('Error creating wallet:', err);
            alert('Error creating wallet');
        }
    }

    function showEditWalletForm(id) {
        const wallet = wallets.find(w => w.wallet_id === id);
        if (!wallet) return;

        document.getElementById('editWalletId').value = wallet.wallet_id;
        document.getElementById('editWalletOwnerType').value = wallet.owner_type;
        document.getElementById('editWalletOwnerTypeDisplay').textContent = wallet.owner_type === 'USER' ? 'User Wallet' :
            wallet.owner_type === 'VENDOR' ? 'Vendor Wallet' : 'Platform Wallet';
        document.getElementById('editWalletName').value = wallet.wallet_name;

        // Set status radio button
        const statusRadio = document.querySelector(`input[name="editWalletStatus"][value="${wallet.status}"]`);
        if (statusRadio) statusRadio.checked = true;

        const offcanvas = document.getElementById('editWalletOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideEditWalletForm() {
        const offcanvas = document.getElementById('editWalletOffcanvas');
        const panel = offcanvas.querySelector('.transform');

        if (panel) panel.classList.add('translate-x-full');
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function updateWallet() {
        const id = document.getElementById('editWalletId').value;
        const name = document.getElementById('editWalletName').value.trim();
        const status = document.querySelector('input[name="editWalletStatus"]:checked')?.value;

        if (!name || !status) {
            alert('Please fill in all required fields');
            return;
        }

        try {
            const res = await fetch(`${API_URL}?action=updateZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_id: id, wallet_name: name, status: status })
            });
            const data = await res.json();

            if (data.success) {
                hideEditWalletForm();
                fetchWallets();
            } else {
                alert(data.message || 'Failed to update wallet');
            }
        } catch (err) {
            console.error('Error updating wallet:', err);
            alert('Error updating wallet');
        }
    }

    function showDeleteConfirm(id) {
        const modal = document.getElementById('deleteWalletModal');
        modal.classList.remove('hidden');
        document.getElementById('confirmDeleteWalletBtn').setAttribute('data-wallet-id', id);
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteWalletModal').classList.add('hidden');
    }

    async function deleteWallet() {
        const id = document.getElementById('confirmDeleteWalletBtn').getAttribute('data-wallet-id');

        try {
            const res = await fetch(`${API_URL}?action=deleteZzimbaWallet`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ wallet_id: id })
            });
            const data = await res.json();

            if (data.success) {
                hideDeleteConfirm();
                fetchWallets();
            } else {
                alert(data.message || 'Failed to delete wallet');
            }
        } catch (err) {
            console.error('Error deleting wallet:', err);
            alert('Error deleting wallet');
        }
    }

    function filterWallets() {
        const query = document.getElementById('searchWallets').value.trim().toLowerCase();
        const type = document.getElementById('filterWallets').value;

        let filtered = wallets;

        if (type !== 'all') {
            filtered = filtered.filter(w => w.owner_type === type);
        }

        if (query) {
            filtered = filtered.filter(w =>
                w.wallet_name.toLowerCase().includes(query) ||
                w.wallet_id.toLowerCase().includes(query)
            );
        }

        renderWalletsTable(filtered);
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        fetchWallets();

        // Main buttons
        document.getElementById('create-wallet-btn').addEventListener('click', showCreateWalletForm);
        document.getElementById('statusHeader').addEventListener('click', sortByStatus);

        // Form submissions
        document.getElementById('submitWalletForm').addEventListener('click', createWallet);
        document.getElementById('updateWalletForm').addEventListener('click', updateWallet);
        document.getElementById('confirmDeleteWalletBtn').addEventListener('click', deleteWallet);

        // Search and filter
        document.getElementById('filterWallets').addEventListener('change', filterWallets);
        document.getElementById('searchWallets').addEventListener('input', filterWallets);
    });

    // Global functions for onclick handlers
    window.showEditWalletForm = showEditWalletForm;
    window.showDeleteConfirm = showDeleteConfirm;
    window.hideDeleteConfirm = hideDeleteConfirm;
    window.hideCreateWalletForm = hideCreateWalletForm;
    window.hideEditWalletForm = hideEditWalletForm;
    window.showCreateWalletForm = showCreateWalletForm;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>