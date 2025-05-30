<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';
ob_start();
?>

<!-- Add intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<div class="space-y-6">

    <!-- 🔔 Incomplete Profile Banner -->
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

    <!-- Profile Header -->
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
                    <button id="editProfileBtn"
                        class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-user-edit"></i><span>Edit Profile</span>
                    </button>
                    <button id="changePasswordBtn"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-lock"></i><span>Change Password</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Information Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-secondary">Personal Information</h2>
            <p class="text-sm text-gray-text mt-1">Update your personal details</p>
        </div>
        <div class="p-6">
            <form id="personalInfoForm" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                        <div id="phone-error" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit"
                        class="h-10 px-6 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Save Changes
                    </button>
                    <div id="profile-form-error" class="text-red-500 text-sm mt-2 hidden"></div>
                    <div id="profile-form-success" class="text-green-500 text-sm mt-2 hidden"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
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

<!-- Success Notification -->
<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i><span id="successMessage"></span>
    </div>
</div>

<!-- Error Notification -->
<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i><span id="errorMessage"></span>
    </div>
</div>

<!-- intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>

<script>
    const PROFILE_REQUIRED_FIELDS = ['first_name', 'email', 'phone'];

    let phoneInput;

    $(document).ready(function () {
        phoneInput = window.intlTelInput(document.querySelector('#phone'), {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            preferredCountries: ["ug", "ke", "tz", "rw"],
            separateDialCode: true,
            autoPlaceholder: "polite"
        });

        $('.iti').addClass('w-full');
        $("#phone").on('blur', validatePhoneNumber);

        $('#editProfileBtn').click(() => $('html,body').animate({ scrollTop: $('#personalInfoForm').offset().top - 100 }, 500));
        $('#changePasswordBtn').click(() => showModal('changePasswordModal'));

        $('#personalInfoForm').submit(e => { e.preventDefault(); if (validatePhoneNumber()) updateProfile() });
        $('#changePasswordForm').submit(e => { e.preventDefault(); changePassword() });

        fetchUserDetails();
    });

    function showIncompleteBanner() { $('#incompleteBanner').removeClass('hidden') }
    function hideIncompleteBanner() { $('#incompleteBanner').addClass('hidden') }

    function evaluateProfileCompleteness(user) {
        const missing = PROFILE_REQUIRED_FIELDS.filter(f => !(user[f] || '').trim());
        missing.length ? showIncompleteBanner() : hideIncompleteBanner();
    }

    function fetchUserDetails() {
        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=getUserDetails',
            dataType: 'json',
            success(resp) {
                if (!resp.success) { showErrorNotification('Failed to load user details.'); return; }
                const u = resp.data;

                $('#firstName').val(u.first_name || '');
                $('#lastName').val(u.last_name || '');
                $('#email').val(u.email || '');
                if (u.phone) phoneInput.setNumber(u.phone);

                const full = `${u.first_name || ''} ${u.last_name || ''}`.trim() || 'User';
                $('#user-fullname').text(full);
                $('#user-username').text('@' + (u.username || ''));

                let init = '';
                if (u.first_name) init += u.first_name[0].toUpperCase();
                if (u.last_name) init += u.last_name[0].toUpperCase();
                if (!init) init = (u.username || 'U')[0].toUpperCase();
                $('#user-initials span').text(init);

                const jd = new Date(u.created_at);
                $('#user-joined').text('Member since ' + jd.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));

                if (u.last_login) $('#user-lastlogin').text('Last login: ' + getTimeAgo(new Date(u.last_login)));
                else $('#user-lastlogin').text('First login');

                $('#overview-email').text(u.email || 'Not set');
                $('#overview-phone').text(u.phone || 'Not set');

                const map = { active: 'text-green-600', inactive: 'text-yellow-600', suspended: 'text-red-600' };
                const cls = map[u.status] || '';
                $('#overview-status').html(`<span class="${cls}">${u.status ? u.status.charAt(0).toUpperCase() + u.status.slice(1) : 'Unknown'}</span>`);

                evaluateProfileCompleteness(u);
            },
            error() { showErrorNotification('Failed to load user details.') }
        });
    }

    function validatePhoneNumber() {
        const e = $('#phone-error').addClass('hidden');
        const num = phoneInput.getNumber();
        if (!num) return false;
        if (!phoneInput.isValidNumber()) { e.text('Invalid phone number').removeClass('hidden'); return false; }
        return true;
    }

    function updateProfile() {
        $('#profile-form-error,#profile-form-success').addClass('hidden').text('');
        const fd = {
            first_name: $('#firstName').val().trim(), last_name: $('#lastName').val().trim(),
            email: $('#email').val().trim(), phone: phoneInput.getNumber()
        };
        if (!fd.first_name || !fd.email || !fd.phone) {
            $('#profile-form-error').removeClass('hidden').text('First name, email & phone are required');
            return;
        }
        const btn = $('#personalInfoForm button[type="submit"]'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=updateProfile',
            method: 'POST', contentType: 'application/json', data: JSON.stringify(fd), dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#profile-form-success').removeClass('hidden').text(r.message);
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                } else {
                    $('#profile-form-error').removeClass('hidden').text(r.message);
                    showErrorNotification(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg } catch { }
                $('#profile-form-error').removeClass('hidden').text(msg);
                showErrorNotification(msg);
            }
        });
    }

    function changePassword() {
        $('#password-form-error').addClass('hidden').text('');
        const cur = $('#currentPassword').val(), nw = $('#newPassword').val(), cf = $('#confirmPassword').val();
        if (!cur || !nw || !cf) { $('#password-form-error').removeClass('hidden').text('All fields are required'); return }
        if (nw !== cf) { $('#password-form-error').removeClass('hidden').text('Passwords do not match'); return }
        if (!(nw.length >= 8 && /[A-Z]/.test(nw) && /[a-z]/.test(nw) && /\d/.test(nw) && /[^A-Za-z0-9]/.test(nw))) {
            $('#password-form-error').removeClass('hidden').text('Password does not meet requirements');
            return;
        }
        const btn = $('#changePasswordForm button[type="submit"]'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=changePassword',
            method: 'POST', contentType: 'application/json',
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
                    showErrorNotification(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg } catch { }
                $('#password-form-error').removeClass('hidden').text(msg);
                showErrorNotification(msg);
            }
        });
    }

    function showModal(id) { $('#' + id).removeClass('hidden') }
    function hideModal(id) { $('#' + id).addClass('hidden') }

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
        if (p.length >= 8) { s += 20; $('#length-check').removeClass('text-gray-400').addClass('text-green-600') } else { $('#length-check').removeClass('text-green-600').addClass('text-gray-400') }
        if (/[A-Z]/.test(p)) { s += 20; $('#uppercase-check').removeClass('text-gray-400').addClass('text-green-600') } else { $('#uppercase-check').removeClass('text-green-600').addClass('text-gray-400') }
        if (/[a-z]/.test(p)) { s += 20; $('#lowercase-check').removeClass('text-gray-400').addClass('text-green-600') } else { $('#lowercase-check').removeClass('text-green-600').addClass('text-gray-400') }
        if (/\d/.test(p)) { s += 20; $('#number-check').removeClass('text-gray-400').addClass('text-green-600') } else { $('#number-check').removeClass('text-green-600').addClass('text-gray-400') }
        if (/[^A-Za-z0-9]/.test(p)) { s += 20; $('#special-check').removeClass('text-gray-400').addClass('text-green-600') } else { $('#special-check').removeClass('text-green-600').addClass('text-gray-400') }
        const b = $('#passwordStrength'); b.css('width', s + '%');
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