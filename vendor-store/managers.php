<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Store Managers';
$activeNav = 'managers';

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

ob_start();
?>
<script>
    const vendorId = '<?= $storeId ?>';
</script>

<div class="space-y-6">
    <div id="alertContainer"></div>

    <div
        class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Store Managers</h2>
            <p class="text-sm text-gray-600 mt-1">Invite and manage users who can help you run your store</p>
        </div>
        <button id="invite-manager-btn"
            class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
            <i class="fas fa-user-plus mr-2"></i>Invite Manager
        </button>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="p-6">
            <div id="loadingIndicator" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-3xl text-gray-400 mb-4"></i>
                <p class="text-gray-600">Loading managers...</p>
            </div>

            <div id="store-managers-container"></div>
        </div>
    </div>
</div>

<div id="inviteManagerModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Invite Store Manager</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors"
                onclick="closeModal('inviteManagerModal')">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="inviteManagerForm" class="p-6 space-y-4">
            <div>
                <label for="managerEmail" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                <div class="relative">
                    <input type="email" id="managerEmail" name="managerEmail"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter email address" required>
                    <div id="email-validation-indicator" class="absolute right-3 top-2 hidden">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </div>
                </div>
                <p id="email-validation-message" class="text-xs mt-1 hidden"></p>
            </div>

            <div>
                <label for="managerRole" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select id="managerRole" name="managerRole"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    required>
                    <option value="manager">Manager (Full Access)</option>
                    <option value="inventory_manager">Inventory Manager</option>
                    <option value="sales_manager">Sales Manager</option>
                    <option value="content_manager">Content Manager</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeModal('inviteManagerModal')">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteManagerModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Remove Manager</h3>
            </div>
            <p class="text-gray-600 mb-2">Are you sure you want to remove this manager?</p>
            <p id="delete-manager-name" class="font-medium text-gray-900 mb-4"></p>
            <p class="text-sm text-gray-500 mb-6">This action cannot be undone.</p>

            <div class="flex justify-end gap-3">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeModal('deleteManagerModal')">
                    Cancel
                </button>
                <button id="confirm-delete-btn" type="button"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash mr-2"></i>Remove Manager
                </button>
            </div>
        </div>
    </div>
</div>

<div id="statusChangeModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div id="status-change-icon" class="w-12 h-12 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <h3 id="status-change-title" class="text-lg font-medium text-gray-900">Change Manager Status</h3>
            </div>
            <p class="text-gray-600 mb-2">Are you sure you want to change this manager's status?</p>
            <p id="status-change-name" class="font-medium text-gray-900 mb-2"></p>
            <p id="status-change-message" class="text-gray-600 mb-4"></p>
            <p class="text-sm text-gray-500 mb-6">An email notification will be sent to the manager.</p>

            <div class="flex justify-end gap-3">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeModal('statusChangeModal')">
                    Cancel
                </button>
                <button id="confirm-status-change-btn" type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<div id="roleChangeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                    <i class="fas fa-user-tag text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Change Manager Role</h3>
            </div>
            <p class="text-gray-600 mb-2">Are you sure you want to change this manager's role?</p>
            <p id="role-change-name" class="font-medium text-gray-900 mb-2"></p>
            <p id="role-change-message" class="text-gray-600 mb-4"></p>
            <p class="text-sm text-gray-500 mb-6">An email notification will be sent to the manager.</p>

            <div class="flex justify-end gap-3">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeModal('roleChangeModal')">
                    Cancel
                </button>
                <button id="confirm-role-change-btn" type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<div id="reinviteManagerModal"
    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-amber-600">Re-invite Former Manager</h2>
            <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors"
                onclick="closeModal('reinviteManagerModal')">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-amber-600 text-xl"></i>
                </div>
            </div>
            <p class="text-center text-gray-700 mb-2">This user was previously removed as a manager for this store.</p>
            <p id="reinvite-manager-name" class="text-center font-medium text-lg mb-4"></p>
            <p class="text-center text-gray-600 mb-6">Do you want to re-invite them as a manager?</p>

            <div class="mb-6">
                <label for="reinviteManagerRole" class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select id="reinviteManagerRole" name="reinviteManagerRole"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                    required>
                    <option value="manager">Manager (Full Access)</option>
                    <option value="inventory_manager">Inventory Manager</option>
                    <option value="sales_manager">Sales Manager</option>
                    <option value="content_manager">Content Manager</option>
                </select>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                    onclick="closeModal('reinviteManagerModal')">
                    Cancel
                </button>
                <button id="confirm-reinvite-btn" type="button"
                    class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Re-invite Manager
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let storeManagers = [];
    let managerToDelete = null;
    let emailCheckTimeout = null;
    let validatedEmail = null;
    let statusChangeData = null;
    let roleChangeData = null;
    let storeName = '';
    let reinviteData = null;

    const roleLabels = {
        'manager': 'Manager (Full Access)',
        'inventory_manager': 'Inventory Manager',
        'sales_manager': 'Sales Manager',
        'content_manager': 'Content Manager'
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
        if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} months ago`;
        return `${Math.floor(diffInSeconds / 31536000)} years ago`;
    }

    function showAlert(type, message) {
        const alertClass = type === 'success'
            ? 'bg-green-50 border-green-200 text-green-800'
            : 'bg-red-50 border-red-200 text-red-800';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        document.getElementById('alertContainer').innerHTML = `
            <div class="${alertClass} border px-4 py-3 rounded-lg mb-4">
                <i class="fas ${iconClass} mr-2"></i>${message}
            </div>`;
        setTimeout(() => { document.getElementById('alertContainer').innerHTML = ''; }, 5000);
    }

    async function loadStoreManagers() {
        const container = document.getElementById('store-managers-container');
        const loading = document.getElementById('loadingIndicator');

        loading.classList.remove('hidden');
        container.innerHTML = '';

        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=getStoreManagers&store_id=${vendorId}`);
            const data = await response.json();

            loading.classList.add('hidden');

            if (data.success) {
                storeManagers = data.managers || [];
                storeName = data.store_name || 'Store';
                renderStoreManagers(storeManagers);
            } else {
                showAlert('error', data.error || 'Failed to load managers');
                container.innerHTML = '<p class="text-center text-red-500 py-8">Failed to load managers.</p>';
            }
        } catch (error) {
            loading.classList.add('hidden');
            console.error('Error loading managers:', error);
            container.innerHTML = '<p class="text-center text-red-500 py-8">Failed to load managers.</p>';
        }
    }

    function renderStoreManagers(managers) {
        const container = document.getElementById('store-managers-container');

        if (!managers || managers.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="mb-6">
                        <i class="fas fa-users-cog text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-500 mb-2">No Store Managers Yet</h3>
                    <p class="text-gray-400 mb-6 max-w-md mx-auto">
                        Invite team members to help you manage your store. You can assign different roles based on their responsibilities.
                    </p>
                    <button id="empty-invite-btn" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Invite Your First Store Manager
                    </button>
                </div>
            `;

            document.getElementById('empty-invite-btn').addEventListener('click', function () {
                openModal('inviteManagerModal');
            });
            return;
        }

        const managersGrid = document.createElement('div');
        managersGrid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';

        managers.forEach(manager => {
            const managerCard = createManagerCard(manager);
            managersGrid.appendChild(managerCard);
        });

        container.appendChild(managersGrid);
    }

    function createManagerCard(manager) {
        const card = document.createElement('div');
        card.className = 'bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 p-6';
        card.dataset.id = manager.id;

        const statusClass = manager.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
        const statusText = manager.status === 'active' ? 'Active' : 'Inactive';
        const approvedBadge = manager.approved ? '' : '<span class="ml-2 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Pending</span>';

        let roleOptions = '';
        Object.entries(roleLabels).forEach(([value, label]) => {
            roleOptions += `<option value="${value}" ${manager.role === value ? 'selected' : ''}>${label}</option>`;
        });

        card.innerHTML = `
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center mb-2 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 flex-shrink-0">
                        <i class="fas fa-user text-gray-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate max-w-full" 
                            title="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}">
                            ${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${statusText}</span>
                            ${approvedBadge}
                        </div>
                    </div>
                </div>
                <button class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-full transition-colors remove-manager-btn" 
                    data-id="${manager.id}" data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>

            <div class="space-y-3 mb-4">
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-envelope w-4 mr-2"></i>
                    <span class="truncate">${escapeHtml(manager.email)}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-phone w-4 mr-2"></i>
                    <span>${escapeHtml(manager.phone)}</span>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-calendar w-4 mr-2"></i>
                    <span>Added ${formatTimeAgo(new Date(manager.created_at))}</span>
                </div>
            </div>

            <div class="space-y-3 pt-3 border-t border-gray-100">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
                    <select class="manager-role-select w-full text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-red-500 focus:border-transparent" 
                        data-id="${manager.id}" 
                        data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}"
                        data-current="${manager.role}">
                        ${roleOptions}
                    </select>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-500">Status</span>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer manager-toggle" 
                            data-id="${manager.id}" 
                            data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}"
                            ${manager.status === 'active' ? 'checked' : ''}>
                        <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                        <span class="ml-2 text-xs font-medium text-gray-700">
                            ${manager.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </label>
                </div>
            </div>
        `;

        // Add event listeners
        const toggle = card.querySelector('.manager-toggle');
        toggle.addEventListener('change', function () {
            const managerId = this.dataset.id;
            const managerName = this.dataset.name;
            const newStatus = this.checked ? 'active' : 'inactive';

            this.checked = !this.checked; // Revert until confirmed

            const statusText = newStatus === 'active' ? 'activate' : 'deactivate';
            const statusColor = newStatus === 'active' ? 'text-green-600' : 'text-yellow-600';
            const statusIcon = newStatus === 'active' ? 'fa-user-check' : 'fa-user-clock';

            document.getElementById('status-change-title').textContent = newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';
            document.getElementById('status-change-name').textContent = managerName;
            document.getElementById('status-change-message').innerHTML = `This will <span class="${statusColor} font-medium">${statusText}</span> the manager's account.`;

            const iconElement = document.getElementById('status-change-icon');
            iconElement.className = `w-12 h-12 rounded-full flex items-center justify-center mr-4 ${newStatus === 'active' ? 'bg-green-100' : 'bg-yellow-100'}`;
            iconElement.innerHTML = `<i class="fas ${statusIcon} ${statusColor} text-xl"></i>`;

            const confirmBtn = document.getElementById('confirm-status-change-btn');
            confirmBtn.className = `px-4 py-2 ${newStatus === 'active' ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700'} text-white rounded-lg transition-colors`;
            confirmBtn.textContent = newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';

            statusChangeData = {
                managerId: managerId,
                newStatus: newStatus,
                toggle: this
            };

            openModal('statusChangeModal');
        });

        const roleSelect = card.querySelector('.manager-role-select');
        roleSelect.addEventListener('change', function () {
            const managerId = this.dataset.id;
            const managerName = this.dataset.name;
            const currentRole = this.dataset.current;
            const newRole = this.value;

            if (currentRole === newRole) return;

            const currentRoleLabel = roleLabels[currentRole] || currentRole;
            const newRoleLabel = roleLabels[newRole] || newRole;

            document.getElementById('role-change-name').textContent = managerName;
            document.getElementById('role-change-message').innerHTML = `Change role from <strong>${currentRoleLabel}</strong> to <strong>${newRoleLabel}</strong>?`;

            roleChangeData = {
                managerId: managerId,
                newRole: newRole,
                select: this,
                originalValue: currentRole
            };

            this.value = currentRole; // Revert until confirmed
            openModal('roleChangeModal');
        });

        const removeBtn = card.querySelector('.remove-manager-btn');
        removeBtn.addEventListener('click', function () {
            const managerId = this.dataset.id;
            const managerName = this.dataset.name;

            managerToDelete = managerId;
            document.getElementById('delete-manager-name').textContent = managerName;
            openModal('deleteManagerModal');
        });

        return card;
    }

    function checkEmailAvailability(email) {
        const indicator = document.getElementById('email-validation-indicator');
        const message = document.getElementById('email-validation-message');

        validatedEmail = null;
        reinviteData = null;
        message.className = 'text-xs mt-1 hidden';
        message.textContent = '';

        if (!email || !email.includes('@')) return;

        indicator.classList.remove('hidden');

        if (emailCheckTimeout) clearTimeout(emailCheckTimeout);

        emailCheckTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=checkEmailAvailability&email=${encodeURIComponent(email)}&store_id=${vendorId}`);
                const data = await response.json();

                indicator.classList.add('hidden');
                message.classList.remove('hidden');

                if (data.success) {
                    if (data.was_removed) {
                        message.className = 'text-xs mt-1 text-amber-600';
                        message.textContent = `User found: ${data.user.first_name} ${data.user.last_name} (previously removed)`;
                        reinviteData = {
                            email: email,
                            firstName: data.user.first_name,
                            lastName: data.user.last_name
                        };
                    } else {
                        message.className = 'text-xs mt-1 text-green-600';
                        message.textContent = `User found: ${data.user.first_name} ${data.user.last_name}`;
                        validatedEmail = email;
                    }
                } else {
                    message.className = 'text-xs mt-1 text-red-600';
                    message.textContent = data.error || 'Invalid email';
                    validatedEmail = null;
                    reinviteData = null;
                }
            } catch (error) {
                console.error('Error checking email:', error);
                indicator.classList.add('hidden');
                message.classList.remove('hidden');
                message.className = 'text-xs mt-1 text-red-600';
                message.textContent = 'Error checking email';
                validatedEmail = null;
                reinviteData = null;
            }
        }, 500);
    }

    async function updateManagerStatus(managerId, newStatus, toggle) {
        const card = toggle.closest('.bg-white');
        const statusSpan = card.querySelector('.px-2.py-1');
        const toggleLabel = toggle.parentElement.querySelector('span');

        statusSpan.textContent = 'Updating...';
        statusSpan.className = 'px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        toggleLabel.textContent = 'Updating...';
        toggle.disabled = true;

        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=updateManagerStatus`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: vendorId,
                    manager_id: managerId,
                    status: newStatus
                })
            });

            const data = await response.json();
            toggle.disabled = false;

            if (data.success) {
                toggle.checked = newStatus === 'active';
                toggleLabel.textContent = newStatus === 'active' ? 'Active' : 'Inactive';

                if (newStatus === 'active') {
                    statusSpan.className = 'px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    statusSpan.textContent = 'Active';
                } else {
                    statusSpan.className = 'px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                    statusSpan.textContent = 'Inactive';
                }

                const managerIndex = storeManagers.findIndex(m => m.id === managerId);
                if (managerIndex !== -1) {
                    storeManagers[managerIndex].status = newStatus;
                }

                const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                showAlert('success', `Manager status updated to ${newStatus} ${emailStatus}`);
            } else {
                toggle.checked = newStatus !== 'active';
                toggleLabel.textContent = newStatus !== 'active' ? 'Active' : 'Inactive';

                if (newStatus !== 'active') {
                    statusSpan.className = 'px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    statusSpan.textContent = 'Active';
                } else {
                    statusSpan.className = 'px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                    statusSpan.textContent = 'Inactive';
                }

                showAlert('error', data.error || 'Failed to update manager status');
            }
        } catch (error) {
            console.error('Error updating manager status:', error);
            toggle.disabled = false;
            toggle.checked = newStatus !== 'active';
            toggleLabel.textContent = newStatus !== 'active' ? 'Active' : 'Inactive';
            showAlert('error', 'Failed to update manager status. Please try again.');
        }
    }

    async function updateManagerRole(managerId, newRole, select) {
        const originalValue = select.dataset.current;
        select.disabled = true;

        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=updateManagerRole`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: vendorId,
                    manager_id: managerId,
                    role: newRole
                })
            });

            const data = await response.json();
            select.disabled = false;

            if (data.success) {
                select.value = newRole;
                select.dataset.current = newRole;

                const managerIndex = storeManagers.findIndex(m => m.id === managerId);
                if (managerIndex !== -1) {
                    storeManagers[managerIndex].role = newRole;
                }

                const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                showAlert('success', `Manager role updated to ${roleLabels[newRole]} ${emailStatus}`);
            } else {
                select.value = originalValue;
                showAlert('error', data.error || 'Failed to update manager role');
            }
        } catch (error) {
            console.error('Error updating manager role:', error);
            select.disabled = false;
            select.value = originalValue;
            showAlert('error', 'Failed to update manager role. Please try again.');
        }
    }

    async function deleteManager(managerId) {
        try {
            const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=removeManager`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: vendorId,
                    manager_id: managerId
                })
            });

            const data = await response.json();

            if (data.success) {
                storeManagers = storeManagers.filter(m => m.id !== managerId);
                renderStoreManagers(storeManagers);

                const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                showAlert('success', `Manager removed successfully ${emailStatus}`);
            } else {
                showAlert('error', data.error || 'Failed to remove manager');
            }
        } catch (error) {
            console.error('Error removing manager:', error);
            showAlert('error', 'Failed to remove manager. Please try again.');
        }
    }

    function resetModalForms() {
        const forms = document.querySelectorAll('#inviteManagerModal form, #reinviteManagerModal form');
        forms.forEach(form => form.reset());

        document.getElementById('email-validation-message').className = 'text-xs mt-1 hidden';
        document.getElementById('email-validation-message').textContent = '';
        validatedEmail = null;
        reinviteData = null;
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        if (modalId === 'inviteManagerModal' || modalId === 'reinviteManagerModal') {
            resetModalForms();
        }
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadStoreManagers();

        const emailInput = document.getElementById('managerEmail');
        emailInput.addEventListener('input', function () {
            checkEmailAvailability(this.value);
        });

        document.getElementById('invite-manager-btn').addEventListener('click', function () {
            openModal('inviteManagerModal');
            resetModalForms();
        });

        document.getElementById('inviteManagerForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = document.getElementById('managerEmail').value;
            const role = document.getElementById('managerRole').value;

            if (reinviteData && reinviteData.email === email) {
                document.getElementById('reinvite-manager-name').textContent = `${reinviteData.firstName} ${reinviteData.lastName}`;
                document.getElementById('reinviteManagerRole').value = role;
                openModal('reinviteManagerModal');
                closeModal('inviteManagerModal');
                return;
            }

            if (email !== validatedEmail) {
                showAlert('error', 'Please enter a valid email address');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

            try {
                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=inviteManager`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        store_id: vendorId,
                        email: email,
                        role: role
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeModal('inviteManagerModal');
                    this.reset();
                    loadStoreManagers();

                    const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                    showAlert('success', `Invitation sent successfully ${emailStatus}`);
                } else {
                    if (data.code === 'previously_removed') {
                        document.getElementById('reinvite-manager-name').textContent = `${data.user_info.first_name} ${data.user_info.last_name}`;
                        document.getElementById('reinviteManagerRole').value = role;
                        openModal('reinviteManagerModal');
                        closeModal('inviteManagerModal');
                    } else {
                        showAlert('error', data.error || 'Failed to send invitation');
                    }
                }
            } catch (error) {
                console.error('Error inviting manager:', error);
                showAlert('error', 'Failed to send invitation. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        document.getElementById('confirm-reinvite-btn').addEventListener('click', async function () {
            if (!reinviteData) {
                showAlert('error', 'Invalid reinvitation data');
                return;
            }

            const email = reinviteData.email;
            const role = document.getElementById('reinviteManagerRole').value;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';

            try {
                const response = await fetch(`${BASE_URL}vendor-store/fetch/manageStoreManagers.php?action=inviteManager`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        store_id: vendorId,
                        email: email,
                        role: role,
                        reinvite: true
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeModal('reinviteManagerModal');
                    loadStoreManagers();

                    const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                    showAlert('success', `Manager re-invited successfully ${emailStatus}`);
                } else {
                    showAlert('error', data.error || 'Failed to re-invite manager');
                }
            } catch (error) {
                console.error('Error re-inviting manager:', error);
                showAlert('error', 'Failed to re-invite manager. Please try again.');
            } finally {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-user-plus mr-2"></i>Re-invite Manager';
            }
        });

        document.getElementById('confirm-delete-btn').addEventListener('click', function () {
            if (managerToDelete) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Removing...';

                deleteManager(managerToDelete);

                setTimeout(() => {
                    closeModal('deleteManagerModal');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-trash mr-2"></i>Remove Manager';
                    managerToDelete = null;
                }, 500);
            }
        });

        document.getElementById('confirm-status-change-btn').addEventListener('click', function () {
            if (statusChangeData) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

                updateManagerStatus(
                    statusChangeData.managerId,
                    statusChangeData.newStatus,
                    statusChangeData.toggle
                );

                setTimeout(() => {
                    closeModal('statusChangeModal');
                    this.disabled = false;
                    this.textContent = statusChangeData.newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';
                    statusChangeData = null;
                }, 500);
            }
        });

        document.getElementById('confirm-role-change-btn').addEventListener('click', function () {
            if (roleChangeData) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

                updateManagerRole(
                    roleChangeData.managerId,
                    roleChangeData.newRole,
                    roleChangeData.select
                );

                setTimeout(() => {
                    closeModal('roleChangeModal');
                    this.disabled = false;
                    this.textContent = 'Confirm Change';
                    roleChangeData = null;
                }, 500);
            }
        });

        // Modal click outside to close
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.addEventListener('click', function (event) {
                if (event.target === this) {
                    this.classList.add('hidden');
                }
            });
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>