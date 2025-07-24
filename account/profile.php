<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';
ob_start();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<div class="space-y-6">

    <div id="incompleteBanner"
        class="hidden bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 px-4 py-3 rounded" role="alert">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle mt-0.5"></i>
            <p class="text-sm leading-relaxed">
                Your profile is incomplete. Please set
                <strong>First Name</strong>, <strong>Email</strong> and
                <strong>Phone Number</strong> before you can continue using
                all system features.
            </p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="md:flex">
            <div
                class="md:w-1/3 bg-gradient-to-r from-user-primary to-blue-700 p-6 flex flex-col items-center justify-center text-white">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-md mb-4 flex items-center justify-center bg-white text-user-primary text-5xl font-bold"
                    id="user-initials"><span>…</span></div>
                <h1 class="text-2xl font-bold" id="user-fullname">Loading…</h1>
                <p class="text-blue-100" id="user-username">@loading</p>
                <div class="mt-4 text-sm text-center">
                    <p id="user-joined">Member since …</p>
                    <p id="user-lastlogin">Last login: …</p>
                </div>
            </div>
            <div class="md:w-2/3 p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Account Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Email Address</h3>
                        <p class="text-secondary" id="overview-email">Loading…</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Phone Number</h3>
                        <p class="text-secondary" id="overview-phone">Loading…</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Account Status</h3>
                        <p class="text-secondary" id="overview-status">Loading…</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button id="changePasswordBtn"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-lock"></i><span>Change Password</span>
                    </button>
                    <button id="deleteAccountBtn"
                        class="h-10 px-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-user-slash"></i><span>Opt-Out</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-secondary">Profile Details</h2>
            <p class="text-sm text-gray-text mt-1">Manage your personal information</p>
        </div>
        <div class="p-6 space-y-4">

            <div
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Full Name</h3>
                            <p class="text-sm text-gray-600" id="display-names">Loading...</p>
                        </div>
                    </div>
                </div>
                <button onclick="editNames()" class="text-gray-400 hover:text-user-primary transition-colors">
                    <i class="fas fa-edit text-lg"></i>
                </button>
            </div>

            <div
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-envelope text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Email Address</h3>
                            <p class="text-sm text-gray-600" id="display-email">Loading...</p>
                        </div>
                    </div>
                </div>
                <button onclick="editEmail()" class="text-gray-400 hover:text-user-primary transition-colors">
                    <i class="fas fa-edit text-lg"></i>
                </button>
            </div>

            <div
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-phone text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Phone Number</h3>
                            <p class="text-sm text-gray-600" id="display-phone">Loading...</p>
                        </div>
                    </div>
                </div>
                <button onclick="editPhone()" class="text-gray-400 hover:text-user-primary transition-colors">
                    <i class="fas fa-edit text-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="editNamesModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('editNamesModal')"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Edit Names</h3>
                <button onclick="hideModal('editNamesModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="namesForm" autocomplete="off">
                <div class="space-y-4">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                        <input type="text" id="firstName" name="first_name"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="lastName" name="last_name"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                    </div>
                    <div id="names-form-error" class="text-red-500 text-sm hidden"></div>
                    <button type="submit"
                        class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Update Names
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editEmailModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('editEmailModal')"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Edit Email Address</h3>
                <button onclick="hideModal('editEmailModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="emailStep1" class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">Current Email:</p>
                    <p class="font-medium" id="current-email-display">Loading...</p>
                </div>
                <div id="verifyExistingEmailSection">
                    <p class="text-sm text-gray-600 mb-3">To change your email, first verify your current email address.
                    </p>
                    <button type="button" id="verifyExistingEmailBtn"
                        class="w-full h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Verify Current Email
                    </button>
                </div>
                <div id="noExistingEmailSection" class="hidden">
                    <p class="text-sm text-gray-600 mb-3">You don't have an email set. Enter your new email address.</p>
                    <div>
                        <label for="newEmailDirect" class="block text-sm font-medium text-gray-700 mb-1">Email
                            Address</label>
                        <input type="email" id="newEmailDirect"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                    </div>
                    <button type="button" id="sendDirectEmailOTPBtn"
                        class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors mt-3">
                        Send Verification Code
                    </button>
                </div>
            </div>

            <div id="emailStep2" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter the verification code sent to your current email.</p>
                <div>
                    <label for="existingEmailOTP" class="block text-sm font-medium text-gray-700 mb-1">Verification
                        Code</label>
                    <input type="text" id="existingEmailOTP" maxlength="6"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg tracking-widest"
                        autocomplete="off" placeholder="000000">
                </div>
                <button type="button" id="verifyExistingEmailOTPBtn"
                    class="w-full h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Verify Code
                </button>
            </div>

            <div id="emailStep3" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter your new email address.</p>
                <div>
                    <label for="newEmail" class="block text-sm font-medium text-gray-700 mb-1">New Email Address</label>
                    <input type="email" id="newEmail"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                        autocomplete="off">
                </div>
                <button type="button" id="sendNewEmailOTPBtn"
                    class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                    Send Verification Code
                </button>
            </div>

            <div id="emailStep4" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter the verification code sent to your new email.</p>
                <div>
                    <label for="newEmailOTP" class="block text-sm font-medium text-gray-700 mb-1">Verification
                        Code</label>
                    <input type="text" id="newEmailOTP" maxlength="6"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg tracking-widest"
                        autocomplete="off" placeholder="000000">
                </div>
                <button type="button" id="verifyNewEmailOTPBtn"
                    class="w-full h-10 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Complete Update
                </button>
            </div>

            <div id="email-form-error" class="text-red-500 text-sm mt-3 hidden"></div>
        </div>
    </div>
</div>

<div id="editPhoneModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('editPhoneModal')"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Edit Phone Number</h3>
                <button onclick="hideModal('editPhoneModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="phoneStep1" class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-700">Current Phone:</p>
                    <p class="font-medium" id="current-phone-display">Loading...</p>
                </div>
                <div id="verifyExistingPhoneSection">
                    <p class="text-sm text-gray-600 mb-3">To change your phone, first verify your current phone number.
                    </p>
                    <button type="button" id="verifyExistingPhoneBtn"
                        class="w-full h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Verify Current Phone
                    </button>
                </div>
                <div id="noExistingPhoneSection" class="hidden">
                    <p class="text-sm text-gray-600 mb-3">You don't have a phone set. Enter your new phone number.</p>
                    <div>
                        <label for="newPhoneDirect" class="block text-sm font-medium text-gray-700 mb-1">Phone
                            Number</label>
                        <input type="tel" id="newPhoneDirect"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                        <div id="phone-error-direct" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                    <button type="button" id="sendDirectPhoneOTPBtn"
                        class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors mt-3">
                        Send Verification Code
                    </button>
                </div>
            </div>

            <div id="phoneStep2" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter the verification code sent to your current phone.</p>
                <div>
                    <label for="existingPhoneOTP" class="block text-sm font-medium text-gray-700 mb-1">Verification
                        Code</label>
                    <input type="text" id="existingPhoneOTP" maxlength="6"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg tracking-widest"
                        autocomplete="off" placeholder="000000">
                </div>
                <button type="button" id="verifyExistingPhoneOTPBtn"
                    class="w-full h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Verify Code
                </button>
            </div>

            <div id="phoneStep3" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter your new phone number.</p>
                <div>
                    <label for="newPhone" class="block text-sm font-medium text-gray-700 mb-1">New Phone Number</label>
                    <input type="tel" id="newPhone"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                        autocomplete="off">
                    <div id="phone-error" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>
                <button type="button" id="sendNewPhoneOTPBtn"
                    class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                    Send Verification Code
                </button>
            </div>

            <div id="phoneStep4" class="space-y-4 hidden">
                <p class="text-sm text-gray-600">Enter the verification code sent to your new phone.</p>
                <div>
                    <label for="newPhoneOTP" class="block text-sm font-medium text-gray-700 mb-1">Verification
                        Code</label>
                    <input type="text" id="newPhoneOTP" maxlength="6"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg tracking-widest"
                        autocomplete="off" placeholder="000000">
                </div>
                <button type="button" id="verifyNewPhoneOTPBtn"
                    class="w-full h-10 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Complete Update
                </button>
            </div>

            <div id="phone-form-error" class="text-red-500 text-sm mt-3 hidden"></div>
        </div>
    </div>
</div>

<div id="changePasswordModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('changePasswordModal')"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Change Password</h3>
                <button onclick="hideModal('changePasswordModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="changePasswordForm" autocomplete="off">
                <div class="space-y-4">
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700 mb-1">Current
                            Password</label>
                        <div class="relative">
                            <input type="password" id="currentPassword" name="current_password"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                                autocomplete="off">
                            <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                data-target="currentPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">New
                            Password</label>
                        <div class="relative">
                            <input type="password" id="newPassword" name="new_password"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                                autocomplete="off">
                            <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                data-target="newPassword">
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
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm New
                            Password</label>
                        <div class="relative">
                            <input type="password" id="confirmPassword" name="confirm_password"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                                autocomplete="off">
                            <button type="button"
                                class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                data-target="confirmPassword">
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
                    <div id="password-form-error" class="text-red-500 text-sm hidden"></div>
                    <button type="submit"
                        class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="deleteAccountModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('deleteAccountModal')"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Delete Account</h3>
                <button onclick="hideModal('deleteAccountModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-700 mb-6">
                Are you sure you want to delete your account? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" id="deleteAccountCancelBtn"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                    onclick="hideModal('deleteAccountModal')">
                    Cancel
                </button>
                <button type="button" id="deleteAccountConfirmBtn"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i><span id="successMessage"></span>
    </div>
</div>

<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i><span id="errorMessage"></span>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>

<script>
    const PROFILE_REQUIRED_FIELDS = ['first_name', 'email', 'phone'];
    let phoneInput, phoneInputDirect;
    let currentUserData = {};

    $(document).ready(function () {
        phoneInput = window.intlTelInput(document.querySelector('#newPhone'), {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            onlyCountries: ["ug"],
            separateDialCode: true,
            autoPlaceholder: "polite",
            initialCountry: "ug"
        });

        phoneInputDirect = window.intlTelInput(document.querySelector('#newPhoneDirect'), {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            onlyCountries: ["ug"],
            separateDialCode: true,
            autoPlaceholder: "polite",
            initialCountry: "ug"
        });

        $('.iti').addClass('w-full');

        $('#changePasswordBtn').click(() => showModal('changePasswordModal'));
        $('#deleteAccountBtn').click(() => showModal('deleteAccountModal'));

        $('#namesForm').submit(e => {
            e.preventDefault();
            updateNames();
        });

        $('#changePasswordForm').submit(e => {
            e.preventDefault();
            changePassword();
        });

        $('#deleteAccountCancelBtn').click(() => hideModal('deleteAccountModal'));
        $('#deleteAccountConfirmBtn').click(() => doDeleteAccount());

        $('#newPassword').on('input', function () {
            checkPasswordStrength($(this).val());
        });

        $('.toggle-password').click(function () {
            const target = $(this).data('target');
            const input = $('#' + target);
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        setupEmailFlow();
        setupPhoneFlow();

        fetchUserDetails();
    });

    function setupEmailFlow() {
        $('#verifyExistingEmailBtn').click(() => {
            sendExistingEmailOTP();
        });

        $('#verifyExistingEmailOTPBtn').click(() => {
            verifyExistingEmailOTP();
        });

        $('#sendNewEmailOTPBtn').click(() => {
            sendNewEmailOTP();
        });

        $('#verifyNewEmailOTPBtn').click(() => {
            verifyNewEmailOTP();
        });

        $('#sendDirectEmailOTPBtn').click(() => {
            sendDirectEmailOTP();
        });
    }

    function setupPhoneFlow() {
        $('#verifyExistingPhoneBtn').click(() => {
            sendExistingPhoneOTP();
        });

        $('#verifyExistingPhoneOTPBtn').click(() => {
            verifyExistingPhoneOTP();
        });

        $('#sendNewPhoneOTPBtn').click(() => {
            sendNewPhoneOTP();
        });

        $('#verifyNewPhoneOTPBtn').click(() => {
            verifyNewPhoneOTP();
        });

        $('#sendDirectPhoneOTPBtn').click(() => {
            sendDirectPhoneOTP();
        });
    }

    function showIncompleteBanner() { $('#incompleteBanner').removeClass('hidden'); }
    function hideIncompleteBanner() { $('#incompleteBanner').addClass('hidden'); }

    function evaluateProfileCompleteness(user) {
        const missing = PROFILE_REQUIRED_FIELDS.filter(f => !(user[f] || '').trim());
        missing.length ? showIncompleteBanner() : hideIncompleteBanner();
    }

    function fetchUserDetails() {
        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=getUserDetails',
            dataType: 'json',
            success(resp) {
                if (!resp.success) {
                    showErrorNotification('Failed to load user details.');
                    return;
                }
                const u = resp.data;
                currentUserData = u;

                $('#firstName').val(u.first_name || '');
                $('#lastName').val(u.last_name || '');

                const fullName = `${u.first_name || ''} ${u.last_name || ''}`.trim() || 'Not set';
                $('#display-names').text(fullName);
                $('#display-email').text(u.email || 'Not set');
                $('#display-phone').text(u.phone || 'Not set');

                $('#user-fullname').text(fullName === 'Not set' ? 'User' : fullName);
                $('#user-username').text('@' + (u.username || ''));

                let init = '';
                if (u.first_name) init += u.first_name[0].toUpperCase();
                if (u.last_name) init += u.last_name[0].toUpperCase();
                if (!init) init = (u.username || 'U')[0].toUpperCase();
                $('#user-initials span').text(init);

                const jd = new Date(u.created_at);
                $('#user-joined').text('Member since ' + jd.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }));

                if (u.last_login) {
                    $('#user-lastlogin').text('Last login: ' + getTimeAgo(new Date(u.last_login)));
                } else {
                    $('#user-lastlogin').text('First login');
                }

                $('#overview-email').text(u.email || 'Not set');
                $('#overview-phone').text(u.phone || 'Not set');

                const map = {
                    active: 'text-green-600',
                    inactive: 'text-yellow-600',
                    suspended: 'text-red-600',
                    deleted: 'text-gray-600'
                };
                const cls = map[u.status] || '';
                $('#overview-status').html(
                    `<span class="${cls}">${u.status ? u.status.charAt(0).toUpperCase() + u.status.slice(1) : 'Unknown'}</span>`
                );

                evaluateProfileCompleteness(u);
            },
            error() {
                showErrorNotification('Failed to load user details.');
            }
        });
    }

    function editNames() {
        $('#firstName').val(currentUserData.first_name || '');
        $('#lastName').val(currentUserData.last_name || '');
        showModal('editNamesModal');
    }

    function editEmail() {
        resetEmailModal();
        $('#current-email-display').text(currentUserData.email || 'Not set');

        if (currentUserData.email) {
            $('#verifyExistingEmailSection').show();
            $('#noExistingEmailSection').hide();
        } else {
            $('#verifyExistingEmailSection').hide();
            $('#noExistingEmailSection').show();
        }

        showModal('editEmailModal');
    }

    function editPhone() {
        resetPhoneModal();
        $('#current-phone-display').text(currentUserData.phone || 'Not set');

        if (currentUserData.phone) {
            $('#verifyExistingPhoneSection').show();
            $('#noExistingPhoneSection').hide();
        } else {
            $('#verifyExistingPhoneSection').hide();
            $('#noExistingPhoneSection').show();
        }

        showModal('editPhoneModal');
    }

    function resetEmailModal() {
        $('#emailStep1').show();
        $('#emailStep2, #emailStep3, #emailStep4').hide();
        $('#email-form-error').addClass('hidden').text('');
        $('#existingEmailOTP, #newEmail, #newEmailOTP, #newEmailDirect').val('');
    }

    function resetPhoneModal() {
        $('#phoneStep1').show();
        $('#phoneStep2, #phoneStep3, #phoneStep4').hide();
        $('#phone-form-error').addClass('hidden').text('');
        $('#phone-error, #phone-error-direct').addClass('hidden');
        $('#existingPhoneOTP, #newPhoneOTP').val('');
        phoneInput.setNumber('');
        phoneInputDirect.setNumber('');
    }

    function updateNames() {
        $('#names-form-error').addClass('hidden').text('');
        const fd = {
            first_name: $('#firstName').val().trim(),
            last_name: $('#lastName').val().trim()
        };
        if (!fd.first_name) {
            $('#names-form-error').removeClass('hidden').text('First name is required');
            return;
        }
        const btn = $('#namesForm button[type="submit"]'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=updateNames',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(fd),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editNamesModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                } else {
                    $('#names-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#names-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendExistingEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const btn = $('#verifyExistingEmailBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep1').hide();
                    $('#emailStep2').show();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyExistingEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const otp = $('#existingEmailOTP').val().trim();
        if (!otp || otp.length !== 6) {
            $('#email-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }

        const btn = $('#verifyExistingEmailOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Verifying…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp: otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep2').hide();
                    $('#emailStep3').show();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendNewEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const newEmail = $('#newEmail').val().trim();
        if (!newEmail) {
            $('#email-form-error').removeClass('hidden').text('New email is required');
            return;
        }

        const btn = $('#sendNewEmailOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_email: newEmail }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep3').hide();
                    $('#emailStep4').show();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyNewEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const otp = $('#newEmailOTP').val().trim();
        if (!otp || otp.length !== 6) {
            $('#email-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }

        const btn = $('#verifyNewEmailOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp: otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editEmailModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendDirectEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const newEmail = $('#newEmailDirect').val().trim();
        if (!newEmail) {
            $('#email-form-error').removeClass('hidden').text('Email is required');
            return;
        }

        const btn = $('#sendDirectEmailOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_email: newEmail }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#newEmailOTP').val('');
                    $('#emailStep1').hide();
                    $('#emailStep4').show();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendExistingPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const btn = $('#verifyExistingPhoneBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep1').hide();
                    $('#phoneStep2').show();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyExistingPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const otp = $('#existingPhoneOTP').val().trim();
        if (!otp || otp.length !== 6) {
            $('#phone-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }

        const btn = $('#verifyExistingPhoneOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Verifying…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp: otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep2').hide();
                    $('#phoneStep3').show();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function validatePhoneNumber(inputId, errorId) {
        const errorEl = $(errorId).addClass('hidden');
        const phoneInputEl = inputId === '#newPhone' ? phoneInput : phoneInputDirect;
        const num = phoneInputEl.getNumber();
        if (!num) return false;
        if (!phoneInputEl.isValidNumber()) {
            errorEl.text('Invalid phone number').removeClass('hidden');
            return false;
        }
        if (!num.startsWith('+256')) {
            errorEl.text('Only Uganda (+256) phone numbers are allowed').removeClass('hidden');
            return false;
        }
        return true;
    }

    function sendNewPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        if (!validatePhoneNumber('#newPhone', '#phone-error')) {
            return;
        }

        const newPhone = phoneInput.getNumber();
        const btn = $('#sendNewPhoneOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_phone: newPhone }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep3').hide();
                    $('#phoneStep4').show();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyNewPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const otp = $('#newPhoneOTP').val().trim();
        if (!otp || otp.length !== 6) {
            $('#phone-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }

        const btn = $('#verifyNewPhoneOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp: otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editPhoneModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendDirectPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        if (!validatePhoneNumber('#newPhoneDirect', '#phone-error-direct')) {
            return;
        }

        const newPhone = phoneInputDirect.getNumber();
        const btn = $('#sendDirectPhoneOTPBtn'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_phone: newPhone }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#newPhoneOTP').val('');
                    $('#phoneStep1').hide();
                    $('#phoneStep4').show();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function changePassword() {
        $('#password-form-error').addClass('hidden').text('');
        const cur = $('#currentPassword').val(),
            nw = $('#newPassword').val(),
            cf = $('#confirmPassword').val();
        if (!cur || !nw || !cf) {
            $('#password-form-error').removeClass('hidden').text('All fields are required');
            return;
        }
        if (nw !== cf) {
            $('#password-form-error').removeClass('hidden').text('Passwords do not match');
            return;
        }
        if (!(nw.length >= 8 && /[A-Z]/.test(nw) && /[a-z]/.test(nw) && /\d/.test(nw) && /[^A-Za-z0-9]/.test(nw))) {
            $('#password-form-error').removeClass('hidden').text('Password does not meet requirements');
            return;
        }
        const btn = $('#changePasswordForm button[type="submit"]'),
            orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=changePassword',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ current_password: cur, new_password: nw }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#changePasswordForm')[0].reset();
                    hideModal('changePasswordModal');
                    showSuccessNotification(r.message);
                } else {
                    $('#password-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#password-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function doDeleteAccount() {
        const btnConfirm = $('#deleteAccountConfirmBtn'),
            btnCancel = $('#deleteAccountCancelBtn');
        btnConfirm.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Deleting…');
        btnCancel.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=deleteAccount',
            method: 'POST',
            dataType: 'json',
            success(r) {
                if (r.success) {
                    hideModal('deleteAccountModal');
                    showSuccessNotification(r.message);
                    setTimeout(() => window.location.href = BASE_URL, 1500);
                } else {
                    showErrorNotification(r.message);
                    btnConfirm.prop('disabled', false).html('Delete Account');
                    btnCancel.prop('disabled', false);
                }
            },
            error() {
                showErrorNotification('Failed to delete account. Please try again.');
                btnConfirm.prop('disabled', false).html('Delete Account');
                btnCancel.prop('disabled', false);
            }
        });
    }

    function showModal(id) { $('#' + id).removeClass('hidden'); }
    function hideModal(id) { $('#' + id).addClass('hidden'); }

    function showSuccessNotification(m) {
        const n = $('#successNotification'), t = $('#successMessage');
        t.text(m); n.removeClass('hidden');
        setTimeout(() => n.addClass('hidden'), 3000);
    }

    function showErrorNotification(m) {
        const n = $('#errorNotification'), t = $('#errorMessage');
        t.text(m); n.removeClass('hidden');
        setTimeout(() => n.addClass('hidden'), 3000);
    }

    function checkPasswordStrength(p) {
        let s = 0;
        if (p.length >= 8) { s += 20; $('#length-check').removeClass('text-gray-400').addClass('text-green-600'); } else { $('#length-check').removeClass('text-green-600').addClass('text-gray-400'); }
        if (/[A-Z]/.test(p)) { s += 20; $('#uppercase-check').removeClass('text-gray-400').addClass('text-green-600'); } else { $('#uppercase-check').removeClass('text-green-600').addClass('text-gray-400'); }
        if (/[a-z]/.test(p)) { s += 20; $('#lowercase-check').removeClass('text-gray-400').addClass('text-green-600'); } else { $('#lowercase-check').removeClass('text-green-600').addClass('text-gray-400'); }
        if (/\d/.test(p)) { s += 20; $('#number-check').removeClass('text-gray-400').addClass('text-green-600'); } else { $('#number-check').removeClass('text-green-600').addClass('text-gray-400'); }
        if (/[^A-Za-z0-9]/.test(p)) { s += 20; $('#special-check').removeClass('text-gray-400').addClass('text-green-600'); } else { $('#special-check').removeClass('text-green-600').addClass('text-gray-400'); }

        const b = $('#passwordStrength');
        b.css('width', s + '%');
        b.removeClass('bg-red-500 bg-yellow-500 bg-green-500');
        if (s < 40) b.addClass('bg-red-500'); else if (s < 80) b.addClass('bg-yellow-500'); else b.addClass('bg-green-500');
    }

    function getTimeAgo(d) {
        const s = Math.floor((new Date() - d) / 1000);
        let i = Math.floor(s / 31536000); if (i > 1) return i + ' years ago'; if (i === 1) return '1 year ago';
        i = Math.floor(s / 2592000); if (i > 1) return i + ' months ago'; if (i === 1) return '1 month ago';
        i = Math.floor(s / 86400); if (i > 1) return i + ' days ago'; if (i === 1) return '1 day ago';
        i = Math.floor(s / 3600); if (i > 1) return i + ' hours ago'; if (i === 1) return '1 hour ago';
        i = Math.floor(s / 60); if (i > 1) return i + ' minutes ago'; if (i === 1) return '1 minute ago';
        if (s < 10) return 'just now'; return s + ' seconds ago';
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>