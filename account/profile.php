<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';

// Sample user data - in a real application, this would come from a database
$userData = [
    'id' => 1001,
    'username' => 'johnsmith',
    'firstName' => 'John',
    'lastName' => 'Smith',
    'email' => 'john.smith@example.com',
    'phone' => '+256 772 123456',
    'address' => '15 Kampala Road',
    'city' => 'Kampala',
    'district' => 'Central',
    'country' => 'Uganda',
    'avatar' => 5, // Current avatar ID
    'has2FA' => false,
    'joinDate' => '2023-08-15',
    'lastLogin' => strtotime('-2 hours')
];

// Function to format date with suffix
function formatDateWithSuffix($timestamp)
{
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

    return date('F ' . $day . $suffix . ', Y g:iA', $timestamp);
}

// Generate avatar URLs
$avatars = [];
for ($i = 1; $i <= 20; $i++) {
    $avatars[] = "https://placehold.co/200x200/4F46E5/ffffff?text=Avatar+{$i}";
}

// Get formatted dates
$lastLogin = formatDateWithSuffix($userData['lastLogin']);
$joinDate = date('F j, Y', strtotime($userData['joinDate']));

ob_start();
?>

<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="md:flex">
            <div class="md:w-1/3 bg-gradient-to-r from-user-primary to-blue-700 p-6 flex flex-col items-center justify-center text-white">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-md mb-4">
                    <img src="<?= $avatars[$userData['avatar'] - 1] ?>" alt="Profile Avatar" class="w-full h-full object-cover" id="current-avatar">
                </div>
                <h1 class="text-2xl font-bold"><?= htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) ?></h1>
                <p class="text-blue-100">@<?= htmlspecialchars($userData['username']) ?></p>
                <div class="mt-4 text-sm text-center">
                    <p>Member since <?= $joinDate ?></p>
                    <p>Last login: <?= $lastLogin ?></p>
                </div>
            </div>
            <div class="md:w-2/3 p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Account Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Email Address</h3>
                        <p class="text-secondary"><?= htmlspecialchars($userData['email']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Phone Number</h3>
                        <p class="text-secondary"><?= htmlspecialchars($userData['phone']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Address</h3>
                        <p class="text-secondary"><?= htmlspecialchars($userData['address']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Location</h3>
                        <p class="text-secondary"><?= htmlspecialchars($userData['city'] . ', ' . $userData['district']) ?></p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button id="editProfileBtn" class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </button>
                    <button id="changePasswordBtn" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </button>
                    <button id="manage2FABtn" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-shield-alt"></i>
                        <span>Manage 2FA</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Personal Information -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-secondary">Personal Information</h2>
                <p class="text-sm text-gray-text mt-1">Update your personal details</p>
            </div>
            <div class="p-6">
                <form id="personalInfoForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($userData['firstName']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($userData['lastName']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($userData['username']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" id="address" name="address" value="<?= htmlspecialchars($userData['address']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input type="text" id="city" name="city" value="<?= htmlspecialchars($userData['city']) ?>" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                            <select id="district" name="district" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                <option value="">Select District</option>
                                <option value="Central" <?= $userData['district'] === 'Central' ? 'selected' : '' ?>>Central</option>
                                <option value="Eastern" <?= $userData['district'] === 'Eastern' ? 'selected' : '' ?>>Eastern</option>
                                <option value="Northern" <?= $userData['district'] === 'Northern' ? 'selected' : '' ?>>Northern</option>
                                <option value="Western" <?= $userData['district'] === 'Western' ? 'selected' : '' ?>>Western</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="h-10 px-6 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Avatar Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-secondary">Profile Avatar</h2>
                <p class="text-sm text-gray-text mt-1">Choose your profile picture</p>
            </div>
            <div class="p-6">
                <div class="mb-4 flex justify-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-user-primary">
                        <img src="<?= $avatars[$userData['avatar'] - 1] ?>" alt="Current Avatar" class="w-full h-full object-cover" id="avatar-preview">
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($avatars as $index => $avatar): ?>
                        <div class="avatar-option cursor-pointer rounded-lg overflow-hidden border-2 <?= ($index + 1) == $userData['avatar'] ? 'border-user-primary' : 'border-transparent' ?>" data-avatar-id="<?= $index + 1 ?>">
                            <img src="<?= $avatar ?>" alt="Avatar Option <?= $index + 1 ?>" class="w-full h-auto">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4">
                    <button id="saveAvatarBtn" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Save Avatar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-secondary">Security Settings</h2>
            <p class="text-sm text-gray-text mt-1">Manage your account security</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Password Section -->
                <div class="border border-gray-100 rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-lock text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-secondary">Password</h3>
                            <p class="text-xs text-gray-text">Last changed: 30 days ago</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-text mb-4">
                        A strong password helps protect your account from unauthorized access.
                    </p>
                    <button id="changePasswordBtn2" class="w-full h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </button>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="border border-gray-100 rounded-lg p-4">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-shield-alt text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-secondary">Two-Factor Authentication</h3>
                            <p class="text-xs text-gray-text">Status: <?= $userData['has2FA'] ? '<span class="text-green-600">Enabled</span>' : '<span class="text-red-600">Disabled</span>' ?></p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-text mb-4">
                        Add an extra layer of security to your account by enabling two-factor authentication.
                    </p>
                    <button id="manage2FABtn2" class="w-full h-10 <?= $userData['has2FA'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> rounded-lg hover:bg-opacity-80 transition-colors flex items-center justify-center gap-2">
                        <i class="fas <?= $userData['has2FA'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                        <span><?= $userData['has2FA'] ? 'Disable 2FA' : 'Enable 2FA' ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('changePasswordModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Change Password</h3>
                <button onclick="hideModal('changePasswordModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="changePasswordForm">
                <div class="space-y-4">
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <div class="relative">
                            <input type="password" id="currentPassword" name="currentPassword" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" data-target="currentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <input type="password" id="newPassword" name="newPassword" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" data-target="newPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="mt-1">
                            <div class="text-xs text-gray-text">Password strength:</div>
                            <div class="w-full h-1 bg-gray-200 rounded-full mt-1">
                                <div id="passwordStrength" class="h-1 bg-red-500 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" name="confirmPassword" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" data-target="confirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-xs text-gray-text">
                        <p>Password must:</p>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li id="length-check" class="text-gray-400">Be at least 8 characters long</li>
                            <li id="uppercase-check" class="text-gray-400">Include at least one uppercase letter</li>
                            <li id="lowercase-check" class="text-gray-400">Include at least one lowercase letter</li>
                            <li id="number-check" class="text-gray-400">Include at least one number</li>
                            <li id="special-check" class="text-gray-400">Include at least one special character</li>
                        </ul>
                    </div>
                    <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2FA Setup Modal -->
<div id="setup2FAModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('setup2FAModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Set Up Two-Factor Authentication</h3>
                <button onclick="hideModal('setup2FAModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="text-center">
                    <div class="mb-4">
                        <img src="https://placehold.co/200x200/4F46E5/ffffff?text=QR+Code" alt="2FA QR Code" class="mx-auto">
                    </div>
                    <p class="text-sm text-gray-text mb-2">Scan this QR code with your authenticator app</p>
                    <div class="text-xs text-gray-text">
                        Or enter this code manually: <span class="font-mono font-medium">ABCD EFGH IJKL MNOP</span>
                    </div>
                </div>
                <form id="verify2FAForm">
                    <div>
                        <label for="verificationCode" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                        <input type="text" id="verificationCode" name="verificationCode" placeholder="Enter 6-digit code" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center">
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                            Verify and Enable
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div id="disable2FAModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('disable2FAModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Disable Two-Factor Authentication</h3>
                <button onclick="hideModal('disable2FAModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Warning: Disabling two-factor authentication will make your account less secure.
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-600">
                    To disable two-factor authentication, please enter your password and the current verification code from your authenticator app.
                </p>
            </div>
            <form id="disable2FAForm">
                <div class="space-y-4">
                    <div>
                        <label for="password2FA" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password2FA" name="password2FA" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="verificationCode2FA" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                        <input type="text" id="verificationCode2FA" name="verificationCode2FA" placeholder="Enter 6-digit code" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center">
                    </div>
                    <button type="submit" class="w-full h-10 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Disable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Open modals
        $('#editProfileBtn').click(function() {
            $('html, body').animate({
                scrollTop: $('#personalInfoForm').offset().top - 100
            }, 500);
        });

        $('#changePasswordBtn, #changePasswordBtn2').click(function() {
            showModal('changePasswordModal');
        });

        $('#manage2FABtn, #manage2FABtn2').click(function() {
            if (<?= $userData['has2FA'] ? 'true' : 'false' ?>) {
                showModal('disable2FAModal');
            } else {
                showModal('setup2FAModal');
            }
        });

        // Toggle password visibility
        $('.toggle-password').click(function() {
            const target = $(this).data('target');
            const input = $(`#${target}`);

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Avatar selection
        $('.avatar-option').click(function() {
            const avatarId = $(this).data('avatar-id');
            const avatarUrl = $(this).find('img').attr('src');

            // Update preview
            $('#avatar-preview').attr('src', avatarUrl);

            // Update selection
            $('.avatar-option').removeClass('border-user-primary').addClass('border-transparent');
            $(this).removeClass('border-transparent').addClass('border-user-primary');
        });

        // Save avatar
        $('#saveAvatarBtn').click(function() {
            const selectedAvatar = $('.avatar-option.border-user-primary').data('avatar-id');
            const avatarUrl = $('.avatar-option.border-user-primary img').attr('src');

            // Update header avatar
            $('#current-avatar').attr('src', avatarUrl);

            // Show success message
            showSuccessNotification('Profile avatar updated successfully');
        });

        // Password strength checker
        $('#newPassword').on('input', function() {
            const password = $(this).val();
            checkPasswordStrength(password);
        });

        // Form submissions
        $('#personalInfoForm').submit(function(e) {
            e.preventDefault();

            // Simulate form submission
            showSuccessNotification('Profile information updated successfully');
        });

        $('#changePasswordForm').submit(function(e) {
            e.preventDefault();

            const newPassword = $('#newPassword').val();
            const confirmPassword = $('#confirmPassword').val();

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match');
                return;
            }

            // Simulate form submission
            hideModal('changePasswordModal');
            showSuccessNotification('Password changed successfully');
        });

        $('#verify2FAForm').submit(function(e) {
            e.preventDefault();

            // Simulate form submission
            hideModal('setup2FAModal');
            showSuccessNotification('Two-factor authentication enabled successfully');

            // Update UI
            $('#manage2FABtn2').removeClass('bg-green-100 text-green-700').addClass('bg-red-100 text-red-700');
            $('#manage2FABtn2').find('i').removeClass('fa-toggle-off').addClass('fa-toggle-on');
            $('#manage2FABtn2').find('span').text('Disable 2FA');
            $('.text-xs.text-gray-text span').removeClass('text-red-600').addClass('text-green-600').text('Enabled');
        });

        $('#disable2FAForm').submit(function(e) {
            e.preventDefault();

            // Simulate form submission
            hideModal('disable2FAModal');
            showSuccessNotification('Two-factor authentication disabled successfully');

            // Update UI
            $('#manage2FABtn2').removeClass('bg-red-100 text-red-700').addClass('bg-green-100 text-green-700');
            $('#manage2FABtn2').find('i').removeClass('fa-toggle-on').addClass('fa-toggle-off');
            $('#manage2FABtn2').find('span').text('Enable 2FA');
            $('.text-xs.text-gray-text span').removeClass('text-green-600').addClass('text-red-600').text('Disabled');
        });
    });

    // Show modal
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Hide modal
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Show success notification
    function showSuccessNotification(message) {
        document.getElementById('successMessage').textContent = message;
        const notification = document.getElementById('successNotification');
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Check password strength
    function checkPasswordStrength(password) {
        // Initialize strength
        let strength = 0;

        // Check length
        if (password.length >= 8) {
            strength += 20;
            $('#length-check').removeClass('text-gray-400').addClass('text-green-600');
        } else {
            $('#length-check').removeClass('text-green-600').addClass('text-gray-400');
        }

        // Check uppercase
        if (/[A-Z]/.test(password)) {
            strength += 20;
            $('#uppercase-check').removeClass('text-gray-400').addClass('text-green-600');
        } else {
            $('#uppercase-check').removeClass('text-green-600').addClass('text-gray-400');
        }

        // Check lowercase
        if (/[a-z]/.test(password)) {
            strength += 20;
            $('#lowercase-check').removeClass('text-gray-400').addClass('text-green-600');
        } else {
            $('#lowercase-check').removeClass('text-green-600').addClass('text-gray-400');
        }

        // Check numbers
        if (/\d/.test(password)) {
            strength += 20;
            $('#number-check').removeClass('text-gray-400').addClass('text-green-600');
        } else {
            $('#number-check').removeClass('text-green-600').addClass('text-gray-400');
        }

        // Check special characters
        if (/[^A-Za-z0-9]/.test(password)) {
            strength += 20;
            $('#special-check').removeClass('text-gray-400').addClass('text-green-600');
        } else {
            $('#special-check').removeClass('text-green-600').addClass('text-gray-400');
        }

        // Update strength indicator
        const strengthBar = $('#passwordStrength');
        strengthBar.css('width', strength + '%');

        // Update color based on strength
        if (strength < 40) {
            strengthBar.removeClass('bg-yellow-500 bg-green-500').addClass('bg-red-500');
        } else if (strength < 80) {
            strengthBar.removeClass('bg-red-500 bg-green-500').addClass('bg-yellow-500');
        } else {
            strengthBar.removeClass('bg-red-500 bg-yellow-500').addClass('bg-green-500');
        }
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>