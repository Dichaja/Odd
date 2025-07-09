<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Approve Transactions';
$activeNav = 'approve-transactions';
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
                    <i class="fas fa-check-circle text-primary text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-secondary font-rubik">Transaction Approvals</h1>
                    <p class="text-sm text-gray-text">Review and approve pending cash transactions</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Pending Transactions
                            </p>
                            <p class="text-2xl font-bold text-yellow-900 whitespace-nowrap" id="pending-count">0</p>
                            <p class="text-sm font-medium text-yellow-700 whitespace-nowrap" id="pending-total">UGX 0.00
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Bank Transfers</p>
                            <p class="text-2xl font-bold text-green-900 whitespace-nowrap" id="bank-count">0</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="bank-total">UGX 0.00</p>
                        </div>
                        <div class="w-12 h-12 bg-green-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-university text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Mobile Money</p>
                            <p class="text-2xl font-bold text-purple-900 whitespace-nowrap" id="mobile-count">0</p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap" id="mobile-total">UGX 0.00
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-purple-200 rounded-xl flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="w-full lg:w-1/3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="searchTransactions"
                                class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                placeholder="Search transactions...">
                        </div>
                    </div>

                    <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <select id="filterTransactions"
                            class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium w-full sm:w-auto">
                            <option value="all">All Methods</option>
                            <option value="BANK">Bank Transfers</option>
                            <option value="MOBILE_MONEY">Mobile Money</option>
                        </select>

                        <select id="filterAccountType"
                            class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white text-sm font-medium w-full sm:w-auto">
                            <option value="all">All Account Types</option>
                            <option value="user">User Accounts</option>
                            <option value="platform">Platform Accounts</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="transactions-table">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Date/Time</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Amount</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Payment Method</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Cash</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Account Type</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Account Details</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-900 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody id="transactions-table-body" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="lg:hidden p-4 space-y-4" id="transactions-mobile">
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No pending transactions</h3>
                <p class="text-gray-500">All transactions have been processed</p>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div id="transactionDetailsModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl relative z-10 overflow-hidden max-h-[95vh] flex flex-col transform transition-all duration-300 scale-95">
        <!-- Header -->
        <div class="p-6 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-receipt text-primary text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-secondary font-rubik">Transaction Details</h3>
                        <p class="text-sm text-gray-500" id="transactionReference"></p>
                    </div>
                </div>
                <button onclick="hideTransactionDetailsModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <div id="transactionDetailsContent" class="space-y-6">
            </div>
        </div>

        <!-- Footer -->
        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideTransactionDetailsModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Close
                </button>
                <button id="rejectTransactionBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                    Reject Transaction
                </button>
                <button id="approveTransactionBtn"
                    class="flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium">
                    Approve Transaction
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Admin Password Modal -->
<div id="adminPasswordModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Admin Authorization Required</h3>
                    <p class="text-sm text-gray-500">Enter your password to authorize this action</p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-shield text-blue-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-blue-900">Admin:</p>
                        <p class="text-sm text-blue-700">
                            <?= htmlspecialchars($_SESSION['user']['username'] ?? 'Unknown') ?>
                        </p>
                    </div>
                </div>
            </div>

            <form id="adminPasswordForm" onsubmit="handleAdminPasswordSubmit(event)">
                <div class="mb-6">
                    <label for="adminPassword" class="block text-sm font-semibold text-gray-700 mb-2">
                        Enter Your Password
                    </label>
                    <div class="relative">
                        <input type="password" id="adminPassword" autocomplete="current-password"
                            class="w-full px-4 py-3 pr-12 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 placeholder-gray-400"
                            placeholder="Enter your password..." required>
                        <button type="button" onclick="toggleAdminPasswordVisibility()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye" id="adminPasswordToggleIcon"></i>
                        </button>
                    </div>
                    <div id="adminPasswordError" class="hidden mt-1 text-sm text-red-600"></div>
                    <div id="adminAttemptsWarning" class="hidden mt-1 text-sm text-orange-600"></div>
                </div>

                <div id="adminActionSummary" class="bg-gray-50 rounded-xl p-4 mb-6"></div>

                <div class="flex gap-3">
                    <button type="button" onclick="hideAdminPasswordModal()"
                        class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button type="submit" id="verifyAdminPasswordBtn"
                        class="flex-1 px-4 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-all duration-200 font-medium">
                        Verify Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Blocked Modal -->
<div id="adminBlockedModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-ban text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Admin Access Blocked</h3>
                <p class="text-sm text-gray-500">Too many failed password attempts</p>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mb-3"></i>
                    <p class="text-red-800 font-medium mb-2">Transaction Approval Temporarily Blocked</p>
                    <p class="text-red-700 text-sm mb-3">
                        You have exceeded the maximum number of password verification attempts (3).
                    </p>
                    <p class="text-red-600 text-sm">
                        Please contact the system administrator to restore access to transaction approval features.
                    </p>
                </div>
            </div>

            <div class="flex justify-center">
                <button onclick="hideAdminBlockedModal()"
                    class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="confirmationIcon" class="w-12 h-12 rounded-xl flex items-center justify-center">
                    <i id="confirmationIconClass" class="text-xl"></i>
                </div>
                <div>
                    <h3 id="confirmationTitle" class="text-lg font-semibold text-gray-900">Confirm Action</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p id="confirmationMessage" class="text-gray-600 mb-6">Are you sure you want to proceed?</p>
            <div class="flex gap-3">
                <button onclick="hideConfirmationModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">
                    Cancel
                </button>
                <button id="confirmActionBtn" class="flex-1 px-4 py-3 rounded-xl transition-colors font-medium">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCashTransactions.php';
    let pendingTransactions = [];
    let currentTransaction = null;
    let pendingAction = null;
    let adminSecurityToken = null;
    const ADMIN_ATTEMPTS_KEY = 'admin_approval_attempts';
    const MAX_ADMIN_ATTEMPTS = 3;

    function getAdminPasswordAttempts() {
        const stored = localStorage.getItem(ADMIN_ATTEMPTS_KEY);
        if (!stored) return { count: 0, timestamp: Date.now() };
        return JSON.parse(stored);
    }

    function incrementAdminPasswordAttempts() {
        const attempts = getAdminPasswordAttempts();
        attempts.count += 1;
        attempts.timestamp = Date.now();
        localStorage.setItem(ADMIN_ATTEMPTS_KEY, JSON.stringify(attempts));
        return attempts;
    }

    function resetAdminPasswordAttempts() {
        localStorage.removeItem(ADMIN_ATTEMPTS_KEY);
    }

    function isAdminBlocked() {
        const attempts = getAdminPasswordAttempts();
        return attempts.count >= MAX_ADMIN_ATTEMPTS;
    }

    // Modal Animation Functions
    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.transform').classList.remove('scale-95');
            modal.querySelector('.transform').classList.add('scale-100');
        }, 10);
    }

    function hideModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-100');
        modal.querySelector('.transform').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function showAdminBlockedModal() {
        showModal('adminBlockedModal');
    }

    function hideAdminBlockedModal() {
        hideModal('adminBlockedModal');
    }

    function showAdminPasswordModal(action, transaction) {
        if (isAdminBlocked()) {
            showAdminBlockedModal();
            return;
        }

        const targetTransaction = transaction || currentTransaction;
        if (!targetTransaction) {
            console.error('No transaction available for admin password modal');
            return;
        }

        pendingAction = action;

        const actionText = action === 'approve' ? 'Approve' : 'Reject';
        const actionColor = action === 'approve' ? 'text-green-700' : 'text-red-700';
        const amountFormatted = formatCurrency(targetTransaction.amount_total);

        document.getElementById('adminActionSummary').innerHTML = `
            <div class="space-y-3">
                <div class="text-center pb-3 border-b border-gray-200">
                    <p class="text-lg font-bold text-gray-900">${amountFormatted}</p>
                    <p class="text-sm ${actionColor} font-medium">${actionText} Transaction</p>
                </div>
                
                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-gray-600">Transaction ID:</span>
                    <div class="text-right">
                        <p class="text-sm font-mono text-gray-900">${targetTransaction.transaction_id.substring(0, 12)}...</p>
                    </div>
                </div>

                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-gray-600">Payment Method:</span>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">${targetTransaction.payment_method === 'BANK' ? 'Bank Transfer' : 'Mobile Money'}</p>
                    </div>
                </div>

                <div class="flex justify-between items-start">
                    <span class="text-sm font-medium text-gray-600">Account Type:</span>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">${targetTransaction.account_type === 'platform' ? 'Platform' : 'User'}</p>
                    </div>
                </div>
            </div>
        `;

        const attempts = getAdminPasswordAttempts();
        if (attempts.count > 0) {
            const attemptsWarning = document.getElementById('adminAttemptsWarning');
            const remaining = MAX_ADMIN_ATTEMPTS - attempts.count;
            attemptsWarning.textContent = `Warning: ${remaining} attempt${remaining !== 1 ? 's' : ''} remaining before access is blocked.`;
            attemptsWarning.classList.remove('hidden');
        }

        showModal('adminPasswordModal');
        setTimeout(() => {
            document.getElementById('adminPassword').focus();
        }, 100);
    }

    function hideAdminPasswordModal() {
        hideModal('adminPasswordModal');
        document.getElementById('adminPassword').value = '';
        document.getElementById('adminPasswordError').classList.add('hidden');
        document.getElementById('adminAttemptsWarning').classList.add('hidden');
        pendingAction = null;
    }

    function toggleAdminPasswordVisibility() {
        const passwordInput = document.getElementById('adminPassword');
        const toggleIcon = document.getElementById('adminPasswordToggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    function handleAdminPasswordSubmit(event) {
        event.preventDefault();
        verifyAdminPassword();
    }

    async function verifyAdminPassword() {
        const passwordInput = document.getElementById('adminPassword');
        const password = passwordInput.value.trim();

        document.getElementById('adminPasswordError').classList.add('hidden');

        if (!password) {
            showAdminPasswordError('Please enter your password');
            return;
        }

        const verifyBtn = document.getElementById('verifyAdminPasswordBtn');
        const originalText = verifyBtn.innerHTML;
        verifyBtn.innerHTML = '<i class="fas fa-spinner animate-spin"></i><span class="ml-2">Verifying...</span>';
        verifyBtn.disabled = true;

        try {
            const payload = new FormData();
            payload.append('action', 'verifyAdminPassword');
            payload.append('password', password);

            const response = await fetch(API_URL, {
                method: 'POST',
                body: payload
            });

            const data = await response.json();

            if (data.success) {
                adminSecurityToken = data.token;
                resetAdminPasswordAttempts();
                const actionToConfirm = pendingAction;
                hideAdminPasswordModal();
                showConfirmation(actionToConfirm);
            } else {
                const attempts = incrementAdminPasswordAttempts();

                if (attempts.count >= MAX_ADMIN_ATTEMPTS) {
                    showAdminPasswordError('Too many failed attempts. Access blocked.');
                    setTimeout(() => {
                        hideAdminPasswordModal();
                        setTimeout(() => {
                            showAdminBlockedModal();
                        }, 500);
                    }, 2000);
                } else {
                    const remaining = MAX_ADMIN_ATTEMPTS - attempts.count;
                    showAdminPasswordError(`Incorrect password. ${remaining} attempt${remaining !== 1 ? 's' : ''} remaining.`);

                    const attemptsWarning = document.getElementById('adminAttemptsWarning');
                    attemptsWarning.textContent = `Warning: ${remaining} attempt${remaining !== 1 ? 's' : ''} remaining before access is blocked.`;
                    attemptsWarning.classList.remove('hidden');
                }

                passwordInput.value = '';
            }

        } catch (error) {
            console.error('Admin password verification error:', error);
            showAdminPasswordError('Error verifying password. Please try again.');
        } finally {
            verifyBtn.innerHTML = originalText;
            verifyBtn.disabled = false;
        }
    }

    function showAdminPasswordError(message) {
        const input = document.getElementById('adminPassword');
        const errorDiv = document.getElementById('adminPasswordError');

        input.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');

        setTimeout(() => {
            input.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
        }, 3000);

        input.focus();
    }

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

    function getPaymentMethodBadge(method) {
        const badges = {
            'BANK': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-university mr-2"></i>Bank Transfer</span>',
            'MOBILE_MONEY': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-mobile-alt mr-2"></i>Mobile Money</span>'
        };
        return badges[method] || `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${method}</span>`;
    }

    function getAccountTypeBadge(accountType) {
        const badges = {
            'platform': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-cogs mr-2"></i>Platform</span>',
            'user': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"><i class="fas fa-user mr-2"></i>User</span>'
        };
        return badges[accountType] || `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${accountType}</span>`;
    }

    async function fetchPendingTransactions() {
        try {
            const formData = new FormData();
            formData.append('action', 'listPending');

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                pendingTransactions = data.pending || [];
                renderTransactionsTable(pendingTransactions);
                updateQuickStats();
            }
        } catch (error) {
            console.error('Error fetching pending transactions:', error);
        }
    }

    function updateQuickStats() {
        const bankTransactions = pendingTransactions.filter(t => t.payment_method === 'BANK');
        const mobileTransactions = pendingTransactions.filter(t => t.payment_method === 'MOBILE_MONEY');

        const bankTotal = bankTransactions.reduce((sum, t) => sum + parseFloat(t.amount_total || 0), 0);
        const mobileTotal = mobileTransactions.reduce((sum, t) => sum + parseFloat(t.amount_total || 0), 0);
        const totalAmount = bankTotal + mobileTotal;

        document.getElementById('pending-count').textContent = pendingTransactions.length;
        document.getElementById('pending-total').textContent = formatCurrency(totalAmount);

        document.getElementById('bank-count').textContent = bankTransactions.length;
        document.getElementById('bank-total').textContent = formatCurrency(bankTotal);

        document.getElementById('mobile-count').textContent = mobileTransactions.length;
        document.getElementById('mobile-total').textContent = formatCurrency(mobileTotal);
    }

    function renderTransactionsTable(list) {
        const tbody = document.getElementById('transactions-table-body');
        const mobile = document.getElementById('transactions-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach((transaction, index) => {
            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'} hover:bg-gray-100 transition-colors`;

            const accountDetails = getAccountDetails(transaction);
            const transactionDateTime = transaction.external_metadata?.btDateTime || transaction.external_metadata?.mmDateTime || transaction.created_at;

            tr.innerHTML = `
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${formatDateTime(transactionDateTime)}</div>
                    <div class="text-xs text-gray-500">Submitted: ${formatDateTime(transaction.created_at)}</div>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                    ${formatCurrency(transaction.amount_total)}
                </td>
                <td class="px-6 py-4">
                    ${getPaymentMethodBadge(transaction.payment_method)}
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${transaction.cash_account_name}</div>
                </td>
                <td class="px-6 py-4">
                    ${getAccountTypeBadge(transaction.account_type)}
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">${accountDetails.name}</div>
                    <div class="text-xs text-gray-500">${accountDetails.subtitle}</div>
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick="showTransactionDetails('${transaction.transaction_id}')" 
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-primary hover:bg-primary/90 transition-colors">
                        <i class="fas fa-eye mr-2"></i>Details
                    </button>
                </td>`;
            tbody.appendChild(tr);

            // Mobile card
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl p-4 border border-gray-200 shadow-sm';
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center ${transaction.payment_method === 'BANK' ? 'bg-green-100' : 'bg-purple-100'}">
                            <i class="${transaction.payment_method === 'BANK' ? 'fas fa-university text-green-600' : 'fas fa-mobile-alt text-purple-600'}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-gray-900">${formatCurrency(transaction.amount_total)}</div>
                            <div class="text-xs text-gray-500">${formatDateTime(transactionDateTime)}</div>
                        </div>
                    </div>
                    ${getPaymentMethodBadge(transaction.payment_method)}
                </div>
                
                <div class="grid grid-cols-1 gap-3 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Account:</span>
                        <span class="font-medium text-gray-900">${transaction.cash_account_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Type:</span>
                        ${getAccountTypeBadge(transaction.account_type)}
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Details:</span>
                        <div class="text-right">
                            <div class="font-medium text-gray-900">${accountDetails.name}</div>
                            <div class="text-xs text-gray-500">${accountDetails.subtitle}</div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-center">
                    <button onclick="showTransactionDetails('${transaction.transaction_id}')" 
                        class="px-4 py-2 text-sm bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium">
                        <i class="fas fa-eye mr-2"></i>Details
                    </button>
                </div>`;
            mobile.appendChild(card);
        });
    }

    function getAccountDetails(transaction) {
        if (transaction.account_type === 'platform') {
            return {
                name: transaction.platform_account?.wallet_name || 'Platform',
                subtitle: transaction.platform_account?.type || 'Platform'
            };
        } else if (transaction.user) {
            return {
                name: `${transaction.user.first_name} ${transaction.user.last_name}`,
                subtitle: transaction.user.email || ''
            };
        } else if (transaction.vendor) {
            return {
                name: transaction.vendor.vendor_name,
                subtitle: transaction.vendor.email || ''
            };
        }
        return { name: 'N/A', subtitle: '' };
    }

    function showTransactionDetails(transactionId) {
        currentTransaction = pendingTransactions.find(t => t.transaction_id === transactionId);
        if (!currentTransaction) return;

        const referenceLabel = currentTransaction.payment_method === 'BANK' ? 'Bank Ref' : 'MM TXN ID';
        document.getElementById('transactionReference').textContent = `${referenceLabel}: ${currentTransaction.external_reference}`;

        const content = document.getElementById('transactionDetailsContent');
        const metadata = currentTransaction.external_metadata;

        let detailsHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Transaction Information</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">System Transaction ID</label>
                            <div class="text-sm font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">${currentTransaction.transaction_id}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Amount</label>
                            <div class="text-lg font-semibold text-gray-900">${formatCurrency(currentTransaction.amount_total)}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Payment Method</label>
                            <div class="mt-1">${getPaymentMethodBadge(currentTransaction.payment_method)}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Cash</label>
                            <div class="text-sm font-medium text-gray-900">${currentTransaction.cash_account_name}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Account Type</label>
                            <div class="mt-1">${getAccountTypeBadge(currentTransaction.account_type)}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Transaction Date/Time</label>
                            <div class="text-sm font-medium text-gray-900">${formatDateTime(metadata?.btDateTime || metadata?.mmDateTime || currentTransaction.created_at)}</div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Submitted</label>
                            <div class="text-sm text-gray-600">${formatDateTime(currentTransaction.created_at)}</div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Account Information</h4>
                    
                    <div class="space-y-3">`;

        if (currentTransaction.account_type === 'platform') {
            detailsHTML += `
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Platform</label>
                    <div class="text-sm font-medium text-gray-900">${currentTransaction.platform_account?.wallet_name || 'Platform'}</div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Account Type</label>
                    <div class="text-sm text-gray-600">${currentTransaction.platform_account?.type || 'Platform'}</div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Wallet Number</label>
                    <div class="text-sm font-mono text-gray-600">${currentTransaction.platform_account?.wallet_number || 'N/A'}</div>
                </div>`;
        } else if (currentTransaction.user) {
            detailsHTML += `
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">User Name</label>
                    <div class="text-sm font-medium text-gray-900">${currentTransaction.user.first_name} ${currentTransaction.user.last_name}</div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</label>
                    <div class="text-sm text-gray-600">${currentTransaction.user.email}</div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Phone</label>
                    <div class="text-sm text-gray-600">${currentTransaction.user.phone}</div>
                </div>`;
        } else if (currentTransaction.vendor) {
            detailsHTML += `
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Vendor Name</label>
                    <div class="text-sm font-medium text-gray-900">${currentTransaction.vendor.vendor_name}</div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</label>
                    <div class="text-sm text-gray-600">${currentTransaction.vendor.email}</div>
                </div>`;
        }

        detailsHTML += `
                    </div>
                </div>
            </div>`;

        // Add payment method specific details
        if (currentTransaction.payment_method === 'BANK' && metadata?.btDepositorName) {
            detailsHTML += `
                <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                    <h5 class="text-sm font-semibold text-green-800 mb-3">Bank Transfer Details</h5>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-green-600 uppercase tracking-wide">Bank Reference/Receipt Number</label>
                            <div class="text-sm font-mono text-green-800 bg-green-100 px-2 py-1 rounded mt-1">${currentTransaction.external_reference}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-green-600 uppercase tracking-wide">Depositor Name</label>
                            <div class="text-sm font-medium text-green-800">${metadata.btDepositorName}</div>
                        </div>
                    </div>
                </div>`;
        } else if (currentTransaction.payment_method === 'MOBILE_MONEY' && metadata?.mmPhoneNumber) {
            detailsHTML += `
                <div class="mt-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <h5 class="text-sm font-semibold text-purple-800 mb-3">Mobile Money Details</h5>
                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-purple-600 uppercase tracking-wide">Mobile Money Transaction ID</label>
                            <div class="text-sm font-mono text-purple-800 bg-purple-100 px-2 py-1 rounded mt-1">${currentTransaction.external_reference}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-purple-600 uppercase tracking-wide">Sender Phone Number</label>
                            <div class="text-sm font-medium text-purple-800">${metadata.mmPhoneNumber}</div>
                        </div>
                    </div>
                </div>`;
        }

        if (currentTransaction.note) {
            detailsHTML += `
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h5 class="text-sm font-semibold text-gray-800 mb-2">Transaction Note</h5>
                    <div class="text-sm text-gray-700">${currentTransaction.note}</div>
                </div>`;
        }

        content.innerHTML = detailsHTML;

        document.getElementById('approveTransactionBtn').onclick = () => showAdminPasswordModal('approve', currentTransaction);
        document.getElementById('rejectTransactionBtn').onclick = () => showAdminPasswordModal('reject', currentTransaction);

        showModal('transactionDetailsModal');
    }

    function hideTransactionDetailsModal() {
        hideModal('transactionDetailsModal');
        currentTransaction = null;
        pendingAction = null;
        adminSecurityToken = null;
    }

    function showConfirmation(action) {
        const modal = document.getElementById('confirmationModal');
        const title = document.getElementById('confirmationTitle');
        const message = document.getElementById('confirmationMessage');
        const icon = document.getElementById('confirmationIcon');
        const iconClass = document.getElementById('confirmationIconClass');
        const confirmBtn = document.getElementById('confirmActionBtn');

        if (action === 'approve') {
            title.textContent = 'Approve Transaction';
            message.textContent = 'Are you sure you want to approve this transaction? This will credit the account and mark the transaction as successful.';
            icon.className = 'w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-check text-green-600 text-xl';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium';
            confirmBtn.textContent = 'Approve';
            confirmBtn.onclick = () => processTransaction('SUCCESS');
        } else if (action === 'reject') {
            title.textContent = 'Reject Transaction';
            message.textContent = 'Are you sure you want to reject this transaction? This indicates the information provided is insufficient or incorrect.';
            icon.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-times text-red-600 text-xl';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium';
            confirmBtn.textContent = 'Reject';
            confirmBtn.onclick = () => processTransaction('FAILED');
        }

        showModal('confirmationModal');
    }

    function hideConfirmationModal() {
        hideModal('confirmationModal');
    }

    async function processTransaction(status) {
        if (!currentTransaction || !adminSecurityToken) {
            showNotification('Security verification required', 'error');
            return;
        }

        hideConfirmationModal();

        try {
            const formData = new FormData();
            formData.append('action', 'acknowledge');
            formData.append('transaction_id', currentTransaction.transaction_id);
            formData.append('status', status);
            formData.append('admin_security_token', adminSecurityToken);

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                hideTransactionDetailsModal();
                fetchPendingTransactions();

                const actionText = status === 'SUCCESS' ? 'approved' : 'rejected';
                showNotification(`Transaction has been ${actionText} successfully`, 'success');
            } else {
                showNotification(data.message || 'Failed to process transaction', 'error');
            }
        } catch (error) {
            console.error('Error processing transaction:', error);
            showNotification('An error occurred while processing the transaction', 'error');
        } finally {
            adminSecurityToken = null;
        }
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function filterTransactions() {
        const query = document.getElementById('searchTransactions').value.trim().toLowerCase();
        const method = document.getElementById('filterTransactions').value;
        const accountType = document.getElementById('filterAccountType').value;

        let filtered = pendingTransactions;

        if (method !== 'all') {
            filtered = filtered.filter(t => t.payment_method === method);
        }

        if (accountType !== 'all') {
            filtered = filtered.filter(t => t.account_type === accountType);
        }

        if (query) {
            filtered = filtered.filter(t =>
                t.cash_account_name.toLowerCase().includes(query) ||
                (t.user && `${t.user.first_name} ${t.user.last_name}`.toLowerCase().includes(query)) ||
                (t.vendor && t.vendor.vendor_name.toLowerCase().includes(query)) ||
                (t.platform_account && t.platform_account.wallet_name.toLowerCase().includes(query)) ||
                t.external_reference.toLowerCase().includes(query) ||
                (t.note && t.note.toLowerCase().includes(query))
            );
        }

        renderTransactionsTable(filtered);
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchPendingTransactions();

        document.getElementById('searchTransactions').addEventListener('input', filterTransactions);
        document.getElementById('filterTransactions').addEventListener('change', filterTransactions);
        document.getElementById('filterAccountType').addEventListener('change', filterTransactions);

        // Refresh data every 30 seconds
        setInterval(fetchPendingTransactions, 30000);
    });

    // Global functions
    window.showTransactionDetails = showTransactionDetails;
    window.hideTransactionDetailsModal = hideTransactionDetailsModal;
    window.hideConfirmationModal = hideConfirmationModal;
    window.hideAdminPasswordModal = hideAdminPasswordModal;
    window.hideAdminBlockedModal = hideAdminBlockedModal;
    window.toggleAdminPasswordVisibility = toggleAdminPasswordVisibility;
    window.handleAdminPasswordSubmit = handleAdminPasswordSubmit;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>