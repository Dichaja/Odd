<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';

// Sample user data - in a real application, this would come from a session or database
$currentUser = [
    'id' => '1',
    'username' => 'admin',
    'email' => 'admin@zzimbaonline.com',
    'phone' => '256700123456',
    'role' => 'Super Admin',
    'created_at' => '2023-01-15 08:30:00',
    'last_login' => '2024-03-07 14:22:10',
    'is_active' => true,
    'avatar' => null // null means use initials
];

// Sample user activity data - in a real application, this would come from a database
$userActivities = [
    [
        'id' => '1',
        'action' => 'Login',
        'description' => 'Logged in successfully',
        'ip_address' => '192.168.1.1',
        'timestamp' => '2024-03-07 14:22:10'
    ],
    [
        'id' => '2',
        'action' => 'Update',
        'description' => 'Updated user profile',
        'ip_address' => '192.168.1.1',
        'timestamp' => '2024-03-06 10:15:30'
    ],
    [
        'id' => '3',
        'action' => 'Create',
        'description' => 'Created new user: john.doe',
        'ip_address' => '192.168.1.1',
        'timestamp' => '2024-03-05 16:45:22'
    ],
    [
        'id' => '4',
        'action' => 'Delete',
        'description' => 'Deleted user: test.user',
        'ip_address' => '192.168.1.1',
        'timestamp' => '2024-03-04 09:30:15'
    ],
    [
        'id' => '5',
        'action' => 'Login',
        'description' => 'Logged in successfully',
        'ip_address' => '192.168.1.1',
        'timestamp' => '2024-03-03 08:10:45'
    ]
];

// Function to format date
function formatDate($date)
{
    if (!$date) return '-';
    $timestamp = strtotime($date);

    $day = date('j', $timestamp);
    $suffix = '';

    if ($day % 10 == 1 && $day != 11) {
        $suffix = 'st';
    } elseif ($day % 10 == 2 && $day != 12) {
        $suffix = 'nd';
    } elseif ($day % 10 == 3 && $day != 13) {
        $suffix = 'rd';
    } else {
        $suffix = 'th';
    }

    return date('F ' . $day . $suffix . ', Y g:i A', $timestamp);
}

// Function to format phone numbers
function formatPhoneNumber($phone)
{
    if (!$phone) return '-';
    // Add + prefix if not already present
    return substr($phone, 0, 1) === '+' ? $phone : '+' . $phone;
}

// Function to get user initials
function getUserInitials($name)
{
    $initials = '';
    $nameParts = explode(' ', $name);
    foreach ($nameParts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
    }
    return $initials;
}

// Function to get action badge class
function getActionBadgeClass($action)
{
    switch ($action) {
        case 'Login':
            return 'bg-blue-100 text-blue-800';
        case 'Update':
            return 'bg-yellow-100 text-yellow-800';
        case 'Create':
            return 'bg-green-100 text-green-800';
        case 'Delete':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

ob_start();
?>

<div class="space-y-6">
    <!-- Profile Overview Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="md:flex">
            <!-- Profile Header -->
            <div class="md:w-1/3 bg-gray-50 p-6 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-100">
                <div class="relative group">
                    <?php if ($currentUser['avatar']): ?>
                        <img src="<?= $currentUser['avatar'] ?>" alt="Profile Picture" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md">
                    <?php else: ?>
                        <div class="w-32 h-32 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-bold border-4 border-white shadow-md">
                            <?= getUserInitials($currentUser['username']) ?>
                        </div>
                    <?php endif; ?>
                    <label for="avatar-upload" class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer hover:bg-gray-100 transition-colors">
                        <i class="fas fa-camera text-gray-600"></i>
                        <input type="file" id="avatar-upload" class="hidden" accept="image/*">
                    </label>
                </div>

                <h2 class="text-xl font-semibold mt-4"><?= htmlspecialchars($currentUser['username']) ?></h2>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($currentUser['role']) ?></p>

                <div class="mt-4 text-sm text-gray-600">
                    <p class="flex items-center gap-2 mb-1">
                        <i class="fas fa-envelope w-5 text-center"></i>
                        <?= htmlspecialchars($currentUser['email']) ?>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fas fa-phone w-5 text-center"></i>
                        <?= formatPhoneNumber($currentUser['phone']) ?>
                    </p>
                </div>

                <div class="mt-6 w-full">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Account Status</span>
                        <span class="<?= $currentUser['is_active'] ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $currentUser['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="<?= $currentUser['is_active'] ? 'bg-green-600' : 'bg-red-600' ?> h-2 rounded-full" style="width: <?= $currentUser['is_active'] ? '100%' : '0%' ?>;"></div>
                    </div>
                </div>

                <div class="mt-6 text-xs text-gray-500">
                    <p>Member since: <?= formatDate($currentUser['created_at']) ?></p>
                    <p>Last login: <?= formatDate($currentUser['last_login']) ?></p>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="md:w-2/3 p-6">
                <h3 class="text-lg font-semibold text-secondary mb-4">Edit Profile</h3>

                <form id="profileForm" class="space-y-4">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($currentUser['username']) ?>"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?= htmlspecialchars($currentUser['email']) ?>"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="<?= htmlspecialchars($currentUser['phone']) ?>"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                    </div>

                    <!-- Role (Read-only) -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <input
                            type="text"
                            id="role"
                            value="<?= htmlspecialchars($currentUser['role']) ?>"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 cursor-not-allowed"
                            readonly>
                        <p class="text-xs text-gray-500 mt-1">Role cannot be changed. Contact an administrator for role changes.</p>
                    </div>

                    <!-- Change Password Toggle -->
                    <div class="pt-2">
                        <label for="changePassword" class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" id="changePassword" class="sr-only">
                                <div class="block bg-gray-200 w-10 h-6 rounded-full"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition"></div>
                            </div>
                            <div class="ml-3 text-gray-700 font-medium text-sm">Change Password</div>
                        </label>
                    </div>

                    <!-- Password Fields (Hidden by default) -->
                    <div id="passwordFields" class="space-y-4 hidden">
                        <!-- Current Password -->
                        <div>
                            <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="currentPassword"
                                    name="currentPassword"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Enter current password">
                                <button
                                    type="button"
                                    class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    data-target="currentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="newPassword"
                                    name="newPassword"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Enter new password">
                                <button
                                    type="button"
                                    class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    data-target="newPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input
                                    type="password"
                                    id="confirmPassword"
                                    name="confirmPassword"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Confirm new password">
                                <button
                                    type="button"
                                    class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Recent Activity</h3>
            <p class="text-sm text-gray-text mt-1">Your recent actions and system activities</p>
        </div>

        <!-- Activity List -->
        <div class="overflow-x-auto">
            <table class="w-full hidden md:table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Action</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Description</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">IP Address</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($userActivities as $activity): ?>
                        <tr class="border-b border-gray-100">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getActionBadgeClass($activity['action']) ?>">
                                    <?= htmlspecialchars($activity['action']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($activity['description']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($activity['ip_address']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($activity['timestamp']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Mobile Activity List -->
            <div class="md:hidden p-4 space-y-4">
                <?php foreach ($userActivities as $activity): ?>
                    <div class="border border-gray-100 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 p-3 flex justify-between items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getActionBadgeClass($activity['action']) ?>">
                                <?= htmlspecialchars($activity['action']) ?>
                            </span>
                            <span class="text-xs text-gray-500"><?= formatDate($activity['timestamp']) ?></span>
                        </div>
                        <div class="p-4">
                            <p class="text-sm mb-2"><?= htmlspecialchars($activity['description']) ?></p>
                            <p class="text-xs text-gray-500">IP: <?= htmlspecialchars($activity['ip_address']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- View More Button -->
        <div class="p-4 border-t border-gray-100 text-center">
            <button class="px-4 py-2 text-primary hover:text-primary/80 transition-colors text-sm font-medium">
                View All Activity
            </button>
        </div>
    </div>

    <!-- Security Settings Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Security Settings</h3>
            <p class="text-sm text-gray-text mt-1">Manage your account security preferences</p>
        </div>

        <div class="p-6 space-y-6">
            <!-- Two-Factor Authentication -->
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">Two-Factor Authentication</h4>
                    <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account</p>
                </div>
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="twoFactorAuth" class="sr-only">
                        <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                    </div>
                </label>
            </div>

            <!-- Session Management -->
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">Active Sessions</h4>
                    <p class="text-sm text-gray-500 mt-1">Manage your active login sessions</p>
                </div>
                <button class="text-primary hover:text-primary/80 transition-colors text-sm font-medium">
                    Manage
                </button>
            </div>

            <!-- Account Deletion -->
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-medium text-gray-900">Delete Account</h4>
                    <p class="text-sm text-gray-500 mt-1">Permanently delete your account and all data</p>
                </div>
                <button id="deleteAccountBtn" class="text-red-600 hover:text-red-700 transition-colors text-sm font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div id="deleteAccountModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteAccountModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Are you sure you want to delete your account?</h3>
                <p class="text-sm text-gray-500 mt-2">This action is permanent and cannot be undone. All your data will be permanently deleted.</p>
            </div>

            <div class="mt-4">
                <label for="deleteConfirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                <input
                    type="password"
                    id="deleteConfirmPassword"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                    placeholder="Enter your password">
            </div>

            <div class="flex justify-center gap-3 mt-6">
                <button onclick="hideDeleteAccountModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="confirmDeleteAccount()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage">Profile updated successfully!</span>
    </div>
</div>

<style>
    /* Toggle switch styling */
    input:checked~.dot {
        transform: translateX(100%);
    }

    input:checked~.block {
        background-color: #C00000;
    }

    .dot {
        transition: all 0.3s ease-in-out;
    }

    @media (max-width: 768px) {
        .overflow-x-auto {
            margin: 0 -1rem;
        }

        table {
            min-width: 800px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password change fields
        const changePasswordToggle = document.getElementById('changePassword');
        const passwordFields = document.getElementById('passwordFields');

        changePasswordToggle.addEventListener('change', function() {
            if (this.checked) {
                passwordFields.classList.remove('hidden');
                document.getElementById('currentPassword').setAttribute('required', 'required');
                document.getElementById('newPassword').setAttribute('required', 'required');
                document.getElementById('confirmPassword').setAttribute('required', 'required');
            } else {
                passwordFields.classList.add('hidden');
                document.getElementById('currentPassword').removeAttribute('required');
                document.getElementById('newPassword').removeAttribute('required');
                document.getElementById('confirmPassword').removeAttribute('required');
            }
        });

        // Toggle password visibility
        const togglePasswordButtons = document.querySelectorAll('.toggle-password');

        togglePasswordButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);

                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });

        // Profile form submission
        const profileForm = document.getElementById('profileForm');

        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!validateProfileForm()) {
                return;
            }

            // Get form data
            const formData = new FormData(profileForm);

            // Here you would typically make an API call to update the profile
            console.log('Updating profile:', Object.fromEntries(formData));

            // Show success notification
            showSuccessNotification('Profile updated successfully!');
        });

        // Avatar upload
        const avatarUpload = document.getElementById('avatar-upload');

        avatarUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];

                // Check file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }

                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size should be less than 2MB');
                    return;
                }

                // Here you would typically upload the file to the server
                console.log('Uploading avatar:', file);

                // For demo purposes, show a preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    // If there's an existing avatar, update it
                    const avatarContainer = document.querySelector('.w-32.h-32');

                    if (avatarContainer.tagName === 'IMG') {
                        avatarContainer.src = e.target.result;
                    } else {
                        // Replace the initials div with an image
                        const parentElement = avatarContainer.parentElement;
                        avatarContainer.remove();

                        const avatarImg = document.createElement('img');
                        avatarImg.src = e.target.result;
                        avatarImg.alt = 'Profile Picture';
                        avatarImg.className = 'w-32 h-32 rounded-full object-cover border-4 border-white shadow-md';

                        parentElement.prepend(avatarImg);
                    }

                    showSuccessNotification('Profile picture updated successfully!');
                };
                reader.readAsDataURL(file);
            }
        });

        // Delete account button
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');

        deleteAccountBtn.addEventListener('click', function() {
            document.getElementById('deleteAccountModal').classList.remove('hidden');
        });

        // Two-factor authentication toggle
        const twoFactorAuthToggle = document.getElementById('twoFactorAuth');

        twoFactorAuthToggle.addEventListener('change', function() {
            if (this.checked) {
                // Here you would typically enable 2FA
                console.log('Enabling 2FA');
                showSuccessNotification('Two-factor authentication enabled');
            } else {
                // Here you would typically disable 2FA
                console.log('Disabling 2FA');
                showSuccessNotification('Two-factor authentication disabled');
            }
        });
    });

    // Validate profile form
    function validateProfileForm() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const changePassword = document.getElementById('changePassword').checked;

        // Check required fields
        if (!username || !email || !phone) {
            alert('Please fill in all required fields');
            return false;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return false;
        }

        // Validate phone format
        const phoneRegex = /^\+?\d{10,15}$/;
        if (!phoneRegex.test(phone)) {
            alert('Please enter a valid phone number');
            return false;
        }

        // Validate password fields if change password is checked
        if (changePassword) {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!currentPassword || !newPassword || !confirmPassword) {
                alert('Please fill in all password fields');
                return false;
            }

            if (newPassword.length < 8) {
                alert('New password must be at least 8 characters long');
                return false;
            }

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return false;
            }
        }

        return true;
    }

    // Show success notification
    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageElement = document.getElementById('successMessage');

        messageElement.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Hide delete account modal
    function hideDeleteAccountModal() {
        document.getElementById('deleteAccountModal').classList.add('hidden');
        document.getElementById('deleteConfirmPassword').value = '';
    }

    // Confirm delete account
    function confirmDeleteAccount() {
        const password = document.getElementById('deleteConfirmPassword').value;

        if (!password) {
            alert('Please enter your password to confirm account deletion');
            return;
        }

        // Here you would typically make an API call to delete the account
        console.log('Deleting account with password confirmation');

        // For demo purposes, show an alert
        alert('Your account has been deleted successfully');
        hideDeleteAccountModal();

        // In a real application, you would redirect to the logout page
        // window.location.href = '/logout';
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>