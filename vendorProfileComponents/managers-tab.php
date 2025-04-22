<div id="managers-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
            <div>
                <h2 class="text-xl font-bold mb-2">Store Managers</h2>
                <p class="text-gray-600">Invite and manage users who can help you run your store</p>
            </div>
            <button id="invite-manager-btn"
                class="mt-4 md:mt-0 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Invite Manager
            </button>
        </div>

        <!-- Managers List -->
        <div id="store-managers-container" class="max-h-[600px] overflow-y-auto mb-4">
            <div class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div>
            </div>
        </div>
    </div>
</div>

<!-- Invite Manager Modal -->
<div id="inviteManagerModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Invite Store Manager</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('inviteManagerModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <form id="inviteManagerForm" class="p-6">
                <div class="mb-4">
                    <label for="managerEmail" class="block text-sm font-medium text-gray-700 mb-1">Email Address
                        *</label>
                    <div class="relative">
                        <input type="email" id="managerEmail" name="managerEmail"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter email address"
                            required>
                        <div id="email-validation-indicator" class="absolute right-3 top-2 hidden">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        </div>
                    </div>
                    <p id="email-validation-message" class="text-xs mt-1 hidden"></p>
                </div>

                <div class="mb-4">
                    <label for="managerRole" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select id="managerRole" name="managerRole"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="manager">Manager (Full Access)</option>
                        <option value="inventory_manager">Inventory Manager</option>
                        <option value="sales_manager">Sales Manager</option>
                        <option value="content_manager">Content Manager</option>
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg mr-2"
                        onclick="closeModal('inviteManagerModal')">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        Send Invitation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Manager Confirmation Modal -->
<div id="deleteManagerModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold text-red-600">Remove Manager</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('deleteManagerModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-red-600">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <p class="text-center text-gray-700 mb-2">Are you sure you want to remove this manager?</p>
                <p id="delete-manager-name" class="text-center font-medium text-lg mb-4"></p>
                <p class="text-center text-gray-500 text-sm mb-6">This action cannot be undone.</p>

                <div class="flex justify-center space-x-4">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg"
                        onclick="closeModal('deleteManagerModal')">
                        Cancel
                    </button>
                    <button id="confirm-delete-btn" type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        Remove Manager
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Confirmation Modal -->
<div id="statusChangeModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 id="status-change-title" class="text-xl font-bold">Change Manager Status</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('statusChangeModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6">
                <div id="status-change-icon" class="flex items-center justify-center mb-4">
                    <i class="fas fa-user-check text-4xl"></i>
                </div>
                <p class="text-center text-gray-700 mb-2">Are you sure you want to change this manager's status?</p>
                <p id="status-change-name" class="text-center font-medium text-lg mb-2"></p>
                <p id="status-change-message" class="text-center text-gray-600 mb-4"></p>
                <p class="text-center text-gray-500 text-sm mb-6">An email notification will be sent to the manager.</p>

                <div class="flex justify-center space-x-4">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg"
                        onclick="closeModal('statusChangeModal')">
                        Cancel
                    </button>
                    <button id="confirm-status-change-btn" type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        Confirm Change
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Role Change Confirmation Modal -->
<div id="roleChangeModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold">Change Manager Role</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('roleChangeModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-blue-600">
                    <i class="fas fa-user-tag text-4xl"></i>
                </div>
                <p class="text-center text-gray-700 mb-2">Are you sure you want to change this manager's role?</p>
                <p id="role-change-name" class="text-center font-medium text-lg mb-2"></p>
                <p id="role-change-message" class="text-center text-gray-600 mb-4"></p>
                <p class="text-center text-gray-500 text-sm mb-6">An email notification will be sent to the manager.</p>

                <div class="flex justify-center space-x-4">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg"
                        onclick="closeModal('roleChangeModal')">
                        Cancel
                    </button>
                    <button id="confirm-role-change-btn" type="button"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        Confirm Change
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add a new modal for re-inviting removed managers -->
<div id="reinviteManagerModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <h2 class="text-xl font-bold text-amber-600">Re-invite Former Manager</h2>
                <button type="button" class="text-gray-400 hover:text-gray-500"
                    onclick="closeModal('reinviteManagerModal')">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-center mb-4 text-amber-600">
                    <i class="fas fa-exclamation-triangle text-4xl"></i>
                </div>
                <p class="text-center text-gray-700 mb-2">This user was previously removed as a manager for this store.
                </p>
                <p id="reinvite-manager-name" class="text-center font-medium text-lg mb-4"></p>
                <p class="text-center text-gray-600 mb-6">Do you want to re-invite them as a manager?</p>

                <div class="mb-4">
                    <label for="reinviteManagerRole" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                    <select id="reinviteManagerRole" name="reinviteManagerRole"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        <option value="manager">Manager (Full Access)</option>
                        <option value="inventory_manager">Inventory Manager</option>
                        <option value="sales_manager">Sales Manager</option>
                        <option value="content_manager">Content Manager</option>
                    </select>
                </div>

                <div class="flex justify-center space-x-4 mt-6">
                    <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg"
                        onclick="closeModal('reinviteManagerModal')">
                        Cancel
                    </button>
                    <button id="confirm-reinvite-btn" type="button"
                        class="px-4 py-2 bg-amber-600 text-white rounded-lg">
                        Re-invite Manager
                    </button>
                </div>
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

    function loadStoreManagers() {
        const container = document.getElementById('store-managers-container');
        container.innerHTML = '<div class="flex justify-center items-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600"></div></div>';

        fetch(`${BASE_URL}fetch/storeManagers.php?action=getStoreManagers&store_id=${vendorId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    storeManagers = data.managers || [];
                    storeName = data.store_name || 'Store';
                    renderStoreManagers(storeManagers);
                } else {
                    showToast(data.error || 'Failed to load managers', 'error');
                    container.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load managers.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading managers:', error);
                container.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load managers.</p>';
            });
    }

    function renderStoreManagers(managers) {
        const container = document.getElementById('store-managers-container');

        if (!managers || managers.length === 0) {
            container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                <div class="mb-6 text-gray-300">
                    <i class="fas fa-users-cog text-8xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">No Store Managers Yet</h3>
                <p class="text-gray-500 mb-6 max-w-md">
                    Invite team members to help you manage your store. You can assign different roles based on their responsibilities.
                </p>
                <button id="empty-invite-btn" class="px-6 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 transition flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Invite Your First Store Manager
                </button>
            </div>
        `;

            document.getElementById('empty-invite-btn').addEventListener('click', function () {
                openModal('inviteManagerModal');
            });

            return;
        }

        container.innerHTML = '';
        const managersList = document.createElement('div');
        managersList.className = 'space-y-4';

        managers.forEach(manager => {
            const managerItem = document.createElement('div');
            managerItem.className = 'bg-white border border-gray-200 rounded-lg shadow-sm p-4 transition-all hover:shadow-md';
            managerItem.dataset.id = manager.id;

            const statusClass = manager.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
            const statusText = manager.status === 'active' ? 'Active' : 'Inactive';
            const approvedBadge = manager.approved ? '' : '<span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Pending Approval</span>';

            let roleOptions = '';
            Object.entries(roleLabels).forEach(([value, label]) => {
                roleOptions += `<option value="${value}" ${manager.role === value ? 'selected' : ''}>${label}</option>`;
            });

            managerItem.innerHTML = `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-3 sm:mb-0">
                    <div class="flex items-center">
                        <h3 class="font-bold text-lg">${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}</h3>
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            ${statusText}
                        </span>
                        ${approvedBadge}
                    </div>
                    <p class="text-gray-600 text-sm mt-1">
                        <i class="fas fa-envelope mr-1"></i> ${escapeHtml(manager.email)}
                    </p>
                    <p class="text-gray-600 text-sm mt-1">
                        <i class="fas fa-phone mr-1"></i> ${escapeHtml(manager.phone)}
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-500"><i class="fas fa-user-tag mr-1"></i> Role:</span>
                        <select class="manager-role-select border border-gray-300 rounded px-2 py-1 text-sm" 
                            data-id="${manager.id}" 
                            data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}"
                            data-current="${manager.role}">
                            ${roleOptions}
                        </select>
                        <span class="text-sm text-gray-500 ml-2"><i class="fas fa-calendar mr-1"></i> Added ${formatTimeAgo(new Date(manager.created_at))}</span>
                    </div>
                </div>
                <div class="flex flex-col sm:items-end">
                    <label class="inline-flex items-center cursor-pointer mb-3">
                        <input type="checkbox" class="sr-only peer manager-toggle" 
                            data-id="${manager.id}" 
                            data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}"
                            ${manager.status === 'active' ? 'checked' : ''}>
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        <span class="ml-2 text-sm font-medium text-gray-900">
                            ${manager.status === 'active' ? 'Active' : 'Inactive'}
                        </span>
                    </label>
                    <button class="text-red-600 hover:text-red-800 text-sm font-medium remove-manager-btn" data-id="${manager.id}" data-name="${escapeHtml(manager.first_name)} ${escapeHtml(manager.last_name)}">
                        <i class="fas fa-trash-alt mr-1"></i> Remove
                    </button>
                </div>
            </div>
        `;

            managersList.appendChild(managerItem);
        });

        container.appendChild(managersList);

        document.querySelectorAll('.manager-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const managerId = this.dataset.id;
                const managerName = this.dataset.name;
                const newStatus = this.checked ? 'active' : 'inactive';

                this.checked = !this.checked;

                const statusText = newStatus === 'active' ? 'activate' : 'deactivate';
                const statusColor = newStatus === 'active' ? 'text-green-600' : 'text-yellow-600';
                const statusIcon = newStatus === 'active' ? 'fa-user-check' : 'fa-user-clock';

                document.getElementById('status-change-title').textContent = newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';
                document.getElementById('status-change-name').textContent = managerName;
                document.getElementById('status-change-message').innerHTML = `This will <span class="${statusColor} font-medium">${statusText}</span> the manager's account.`;

                const iconElement = document.getElementById('status-change-icon');
                iconElement.innerHTML = `<i class="fas ${statusIcon} ${statusColor} text-4xl"></i>`;

                const confirmBtn = document.getElementById('confirm-status-change-btn');
                confirmBtn.className = `px-4 py-2 ${newStatus === 'active' ? 'bg-green-600' : 'bg-yellow-600'} text-white rounded-lg`;
                confirmBtn.textContent = newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';

                statusChangeData = {
                    managerId: managerId,
                    newStatus: newStatus,
                    toggle: this
                };

                openModal('statusChangeModal');
            });
        });

        document.querySelectorAll('.manager-role-select').forEach(select => {
            select.addEventListener('change', function () {
                const managerId = this.dataset.id;
                const managerName = this.dataset.name;
                const currentRole = this.dataset.current;
                const newRole = this.value;

                if (currentRole === newRole) {
                    return;
                }

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

                this.value = currentRole;
                openModal('roleChangeModal');
            });
        });

        document.querySelectorAll('.remove-manager-btn').forEach(button => {
            button.addEventListener('click', function () {
                const managerId = this.dataset.id;
                const managerName = this.dataset.name;

                managerToDelete = managerId;
                document.getElementById('delete-manager-name').textContent = managerName;
                openModal('deleteManagerModal');
            });
        });
    }

    function checkEmailAvailability(email) {
        const indicator = document.getElementById('email-validation-indicator');
        const message = document.getElementById('email-validation-message');

        validatedEmail = null;
        reinviteData = null;
        message.className = 'text-xs mt-1 hidden';
        message.textContent = '';

        if (!email || !email.includes('@')) {
            return;
        }

        indicator.classList.remove('hidden');

        if (emailCheckTimeout) {
            clearTimeout(emailCheckTimeout);
        }

        emailCheckTimeout = setTimeout(() => {
            fetch(`${BASE_URL}fetch/storeManagers.php?action=checkEmailAvailability&email=${encodeURIComponent(email)}&store_id=${vendorId}`)
                .then(response => response.json())
                .then(data => {
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
                })
                .catch(error => {
                    console.error('Error checking email:', error);
                    indicator.classList.add('hidden');
                    message.classList.remove('hidden');
                    message.className = 'text-xs mt-1 text-red-600';
                    message.textContent = 'Error checking email';
                    validatedEmail = null;
                    reinviteData = null;
                });
        }, 500);
    }

    function updateManagerStatus(managerId, newStatus, toggle) {
        const statusLabel = toggle.parentElement.querySelector('span');
        const badge = toggle.closest('.flex').parentElement.querySelector('.rounded-full');

        statusLabel.textContent = 'Updating...';
        toggle.disabled = true;

        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        badge.textContent = 'Updating...';

        fetch(`${BASE_URL}fetch/storeManagers.php?action=updateManagerStatus`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                store_id: vendorId,
                manager_id: managerId,
                status: newStatus
            })
        })
            .then(response => response.json())
            .then(data => {
                toggle.disabled = false;

                if (data.success) {
                    toggle.checked = newStatus === 'active';
                    statusLabel.textContent = newStatus === 'active' ? 'Active' : 'Inactive';

                    if (newStatus === 'active') {
                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                        badge.textContent = 'Inactive';
                    }

                    const managerIndex = storeManagers.findIndex(m => m.id === managerId);
                    if (managerIndex !== -1) {
                        storeManagers[managerIndex].status = newStatus;
                    }

                    const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                    showToast(`Manager status updated to ${newStatus} ${emailStatus}`, 'success');
                } else {
                    toggle.checked = newStatus !== 'active';
                    statusLabel.textContent = newStatus !== 'active' ? 'Active' : 'Inactive';

                    if (newStatus !== 'active') {
                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                        badge.textContent = 'Active';
                    } else {
                        badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                        badge.textContent = 'Inactive';
                    }

                    showToast(data.error || 'Failed to update manager status', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating manager status:', error);
                toggle.disabled = false;
                toggle.checked = newStatus !== 'active';
                statusLabel.textContent = newStatus !== 'active' ? 'Active' : 'Inactive';

                if (newStatus !== 'active') {
                    badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    badge.textContent = 'Active';
                } else {
                    badge.className = 'ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                    badge.textContent = 'Inactive';
                }

                showToast('Failed to update manager status. Please try again.', 'error');
            });
    }

    function updateManagerRole(managerId, newRole, select) {
        const originalValue = select.dataset.current;
        select.disabled = true;

        const loadingSpan = document.createElement('span');
        loadingSpan.className = 'text-xs text-gray-500 ml-2';
        loadingSpan.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        select.parentNode.appendChild(loadingSpan);

        fetch(`${BASE_URL}fetch/storeManagers.php?action=updateManagerRole`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                store_id: vendorId,
                manager_id: managerId,
                role: newRole
            })
        })
            .then(response => response.json())
            .then(data => {
                select.disabled = false;
                select.parentNode.removeChild(loadingSpan);

                if (data.success) {
                    select.value = newRole;
                    select.dataset.current = newRole;

                    const managerIndex = storeManagers.findIndex(m => m.id === managerId);
                    if (managerIndex !== -1) {
                        storeManagers[managerIndex].role = newRole;
                    }

                    const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                    showToast(`Manager role updated to ${roleLabels[newRole]} ${emailStatus}`, 'success');
                } else {
                    select.value = originalValue;
                    showToast(data.error || 'Failed to update manager role', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating manager role:', error);
                select.disabled = false;
                select.parentNode.removeChild(loadingSpan);
                select.value = originalValue;
                showToast('Failed to update manager role. Please try again.', 'error');
            });
    }

    function deleteManager(managerId) {
        fetch(`${BASE_URL}fetch/storeManagers.php?action=removeManager`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                store_id: vendorId,
                manager_id: managerId
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    storeManagers = storeManagers.filter(m => m.id !== managerId);
                    renderStoreManagers(storeManagers);

                    const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                    showToast(`Manager removed successfully ${emailStatus}`, 'success');
                } else {
                    showToast(data.error || 'Failed to remove manager', 'error');
                }
            })
            .catch(error => {
                console.error('Error removing manager:', error);
                showToast('Failed to remove manager. Please try again.', 'error');
            });
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
        const emailInput = document.getElementById('managerEmail');
        emailInput.addEventListener('input', function () {
            checkEmailAvailability(this.value);
        });

        document.getElementById('invite-manager-btn').addEventListener('click', function () {
            openModal('inviteManagerModal');
            document.getElementById('inviteManagerForm').reset();
            document.getElementById('email-validation-message').className = 'text-xs mt-1 hidden';
            validatedEmail = null;
            reinviteData = null;
        });

        document.getElementById('inviteManagerForm').addEventListener('submit', function (e) {
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
                showToast('Please enter a valid email address', 'error');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            const closeBtn = document.querySelector('#inviteManagerModal button[onclick="closeModal(\'inviteManagerModal\')"]');
            closeBtn.disabled = true;

            fetch(`${BASE_URL}fetch/storeManagers.php?action=inviteManager`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: vendorId,
                    email: email,
                    role: role
                })
            })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    closeBtn.disabled = false;

                    if (data.success) {
                        closeModal('inviteManagerModal');
                        this.reset();
                        loadStoreManagers();

                        const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                        showToast(`Invitation sent successfully ${emailStatus}`, 'success');
                    } else {
                        if (data.code === 'previously_removed') {
                            document.getElementById('reinvite-manager-name').textContent = `${data.user_info.first_name} ${data.user_info.last_name}`;
                            document.getElementById('reinviteManagerRole').value = role;
                            openModal('reinviteManagerModal');
                            closeModal('inviteManagerModal');
                        } else {
                            showToast(data.error || 'Failed to send invitation', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error inviting manager:', error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    closeBtn.disabled = false;
                    showToast('Failed to send invitation. Please try again.', 'error');
                });
        });

        document.getElementById('confirm-reinvite-btn').addEventListener('click', function () {
            if (!reinviteData) {
                showToast('Invalid reinvitation data', 'error');
                return;
            }

            const email = reinviteData.email;
            const role = document.getElementById('reinviteManagerRole').value;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';

            const closeBtn = document.querySelector('#reinviteManagerModal button[onclick="closeModal(\'reinviteManagerModal\')"]');
            closeBtn.disabled = true;

            fetch(`${BASE_URL}fetch/storeManagers.php?action=inviteManager`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    store_id: vendorId,
                    email: email,
                    role: role,
                    reinvite: true
                })
            })
                .then(response => response.json())
                .then(data => {
                    this.disabled = false;
                    this.innerHTML = 'Re-invite Manager';
                    closeBtn.disabled = false;

                    if (data.success) {
                        closeModal('reinviteManagerModal');
                        loadStoreManagers();

                        const emailStatus = data.email_sent ? 'and notification email sent' : 'but email notification failed';
                        showToast(`Manager re-invited successfully ${emailStatus}`, 'success');
                    } else {
                        showToast(data.error || 'Failed to re-invite manager', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error re-inviting manager:', error);
                    this.disabled = false;
                    this.innerHTML = 'Re-invite Manager';
                    closeBtn.disabled = false;
                    showToast('Failed to re-invite manager. Please try again.', 'error');
                });
        });

        document.getElementById('confirm-delete-btn').addEventListener('click', function () {
            if (managerToDelete) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Removing...';

                const closeBtn = document.querySelector('#deleteManagerModal button[onclick="closeModal(\'deleteManagerModal\')"]');
                closeBtn.disabled = true;

                deleteManager(managerToDelete);

                setTimeout(() => {
                    closeModal('deleteManagerModal');
                    this.disabled = false;
                    this.innerHTML = 'Remove Manager';
                    closeBtn.disabled = false;
                    managerToDelete = null;
                }, 500);
            }
        });

        document.getElementById('confirm-status-change-btn').addEventListener('click', function () {
            if (statusChangeData) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

                const closeBtn = document.querySelector('#statusChangeModal button[onclick="closeModal(\'statusChangeModal\')"]');
                closeBtn.disabled = true;

                updateManagerStatus(
                    statusChangeData.managerId,
                    statusChangeData.newStatus,
                    statusChangeData.toggle
                );

                setTimeout(() => {
                    closeModal('statusChangeModal');
                    this.disabled = false;
                    this.textContent = statusChangeData.newStatus === 'active' ? 'Activate Manager' : 'Deactivate Manager';
                    closeBtn.disabled = false;
                    statusChangeData = null;
                }, 500);
            }
        });

        document.getElementById('confirm-role-change-btn').addEventListener('click', function () {
            if (roleChangeData) {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Updating...';

                const closeBtn = document.querySelector('#roleChangeModal button[onclick="closeModal(\'roleChangeModal\')"]');
                closeBtn.disabled = true;

                updateManagerRole(
                    roleChangeData.managerId,
                    roleChangeData.newRole,
                    roleChangeData.select
                );

                setTimeout(() => {
                    closeModal('roleChangeModal');
                    this.disabled = false;
                    this.textContent = 'Confirm Change';
                    closeBtn.disabled = false;
                    roleChangeData = null;
                }, 500);
            }
        });

        document.querySelector('button[data-tab="managers"]').addEventListener('click', function () {
            loadStoreManagers();
        });
    });
</script>