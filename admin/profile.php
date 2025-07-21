<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'My Profile';
$activeNav = 'profile';

$userName = $_SESSION['user']['username'];

ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                <p class="text-gray-600 mt-1">Manage your account information and security settings</p>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Profile Information</h3>
                        <p class="text-sm text-gray-600">Update your personal information and contact details</p>
                    </div>
                    <button id="editProfileBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Profile</span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="profileContent">
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                            <p class="text-gray-600">Loading profile information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                        <p class="text-sm text-gray-600">Update your password to keep your account secure</p>
                    </div>
                    <button id="changePasswordBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Account Activity</h3>
                    <p class="text-sm text-gray-600">View your recent account activity and login history</p>
                </div>
            </div>

            <div class="p-6">
                <div id="activityContent">
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-4"></i>
                            <p class="text-gray-600">Loading activity information...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editProfileModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div
        class="relative w-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Edit Profile</h3>
                <p class="text-sm text-gray-600 mt-1">Update your personal information</p>
            </div>
            <button onclick="closeEditProfileModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div class="p-6">
                <form id="editProfileForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                            <input type="text" id="editUsername" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" id="editEmail" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="editFirstName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="editLastName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-r-0 border-gray-300 rounded-l-lg">+256</span>
                                <input type="text" id="editPhone" maxlength="9" pattern="[0-9]{9}"
                                    placeholder="7XXXXXXXX"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeEditProfileModal()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="changePasswordModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-50 to-green-100">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Change Password</h3>
                <p class="text-sm text-gray-600 mt-1">Update your account password</p>
            </div>
            <button onclick="closeChangePasswordModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="p-6">
            <form id="passwordForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                    <div class="relative">
                        <input type="password" id="currentPassword" required
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                            data-target="currentPassword">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                    <div class="relative">
                        <input type="password" id="newPassword" required minlength="6"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                            data-target="newPassword">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters long</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" required minlength="6"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                            data-target="confirmPassword">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeChangePasswordModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
    let currentProfileData = null;

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        loadProfile();
    });

    function setupEventListeners() {
        document.getElementById('editProfileBtn').addEventListener('click', showEditProfileModal);
        document.getElementById('changePasswordBtn').addEventListener('click', showChangePasswordModal);
        document.getElementById('editProfileForm').addEventListener('submit', handleProfileSubmit);
        document.getElementById('passwordForm').addEventListener('submit', handlePasswordSubmit);

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        document.getElementById('editProfileModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeEditProfileModal();
            }
        });

        document.getElementById('changePasswordModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeChangePasswordModal();
            }
        });

        document.getElementById('messageModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });
    }

    function loadProfile() {
        fetch('fetch/manageProfile.php?action=getProfile')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentProfileData = data.profile;
                    showProfileView(data.profile);
                    showActivityView(data.profile);
                } else {
                    showError('profileContent', 'Failed to load profile: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('profileContent', 'An error occurred while loading profile');
            });
    }

    function showError(containerId, message) {
        document.getElementById(containerId).innerHTML = `
            <div class="flex items-center justify-center py-12">
                <div class="text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                    <p>${message}</p>
                </div>
            </div>
        `;
    }

    function showProfileView(profile) {
        const content = document.getElementById('profileContent');

        const emailLink = profile.email && profile.email !== 'N/A' ?
            `<a href="mailto:${profile.email}" class="text-blue-600 hover:text-blue-800 underline">${profile.email}</a>` :
            (profile.email || 'N/A');

        const phoneLink = profile.phone && profile.phone !== 'N/A' ?
            `<a href="tel:${profile.phone}" class="text-blue-600 hover:text-blue-800 underline">${profile.phone}</a>` :
            (profile.phone || 'N/A');

        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <div class="mt-1 text-sm text-gray-900">${profile.username || 'N/A'}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <div class="mt-1 text-sm text-gray-900">${profile.first_name || 'N/A'}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <div class="mt-1 text-sm text-gray-900">${profile.last_name || 'N/A'}</div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 text-sm text-gray-900">${emailLink}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <div class="mt-1 text-sm text-gray-900">${phoneLink}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1">
                            ${getRoleBadge(profile.role)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function showActivityView(profile) {
        const content = document.getElementById('activityContent');

        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Created</label>
                        <div class="mt-1 text-sm text-gray-900">${formatDateTime(profile.created_at)}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <div class="mt-1 text-sm text-gray-900">${formatDateTime(profile.updated_at)}</div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Login</label>
                        <div class="mt-1 text-sm text-gray-900">${formatDateTime(profile.current_login)}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Login</label>
                        <div class="mt-1 text-sm text-gray-900">${formatDateTime(profile.last_login)}</div>
                    </div>
                </div>
            </div>
        `;
    }

    function showEditProfileModal() {
        if (!currentProfileData) return;

        document.getElementById('editUsername').value = currentProfileData.username || '';
        document.getElementById('editEmail').value = currentProfileData.email || '';
        document.getElementById('editFirstName').value = currentProfileData.first_name || '';
        document.getElementById('editLastName').value = currentProfileData.last_name || '';
        document.getElementById('editPhone').value = currentProfileData.phone ? currentProfileData.phone.replace('+256', '') : '';

        document.getElementById('editProfileModal').classList.remove('hidden');
    }

    function closeEditProfileModal() {
        document.getElementById('editProfileModal').classList.add('hidden');
        document.getElementById('editProfileForm').reset();
    }

    function showChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        document.getElementById('changePasswordModal').classList.add('hidden');
        document.getElementById('passwordForm').reset();

        document.querySelectorAll('.toggle-password').forEach(button => {
            const targetId = button.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = button.querySelector('i');

            targetInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        });
    }

    function handleProfileSubmit(e) {
        e.preventDefault();

        const phone = document.getElementById('editPhone').value;
        if (phone && !/^[0-9]{9}$/.test(phone)) {
            showMessage('error', 'Error', 'Phone number must be exactly 9 digits');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'updateProfile');
        formData.append('username', document.getElementById('editUsername').value);
        formData.append('first_name', document.getElementById('editFirstName').value);
        formData.append('last_name', document.getElementById('editLastName').value);
        formData.append('email', document.getElementById('editEmail').value);
        formData.append('phone', phone ? '+256' + phone : '');

        fetch('fetch/manageProfile.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'Profile updated successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        closeEditProfileModal();
                        loadProfile();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to update profile');
            });
    }

    function handlePasswordSubmit(e) {
        e.preventDefault();

        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword !== confirmPassword) {
            showMessage('error', 'Error', 'New passwords do not match');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'changePassword');
        formData.append('current_password', document.getElementById('currentPassword').value);
        formData.append('new_password', newPassword);

        fetch('fetch/manageProfile.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', 'Success', 'Password changed successfully');
                    setTimeout(() => {
                        closeMessageModal();
                        closeChangePasswordModal();
                    }, 1500);
                } else {
                    showMessage('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('error', 'Error', 'Failed to change password');
            });
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
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>