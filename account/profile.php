<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';

ob_start();
?>

<!-- Add intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
        <div class="md:flex">
            <div
                class="md:w-1/3 bg-gradient-to-r from-user-primary to-blue-700 p-6 flex flex-col items-center justify-center text-white">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-md mb-4 flex items-center justify-center bg-white text-user-primary text-5xl font-bold"
                    id="user-initials">
                    <span>...</span>
                </div>
                <h1 class="text-2xl font-bold" id="user-fullname">Loading...</h1>
                <p class="text-blue-100" id="user-username">@loading</p>
                <div class="mt-4 text-sm text-center">
                    <p id="user-joined">Member since ...</p>
                    <p id="user-lastlogin">Last login: ...</p>
                </div>
            </div>
            <div class="md:w-2/3 p-6">
                <h2 class="text-xl font-semibold text-secondary mb-4">Account Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Email Address</h3>
                        <p class="text-secondary" id="overview-email">Loading...</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Phone Number</h3>
                        <p class="text-secondary" id="overview-phone">Loading...</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-text">Account Status</h3>
                        <p class="text-secondary" id="overview-status">Loading...</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap gap-3">
                    <button id="editProfileBtn"
                        class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-user-edit"></i>
                        <span>Edit Profile</span>
                    </button>
                    <button id="changePasswordBtn"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Sections -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-secondary">Personal Information</h2>
            <p class="text-sm text-gray-text mt-1">Update your personal details</p>
        </div>
        <div class="p-6">
            <form id="personalInfoForm" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
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
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"
                            autocomplete="off">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
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
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>

<!-- Error Notification -->
<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<!-- Add intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>

<script>
    // Add session expiration modal
    const sessionExpiredModalHTML = `
    <div id="sessionExpiredModal" class="fixed inset-0 z-[1000] hidden">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg p-6">
            <div class="text-center">
                <i class="fas fa-exclamation-circle text-red-500 text-5xl mb-4"></i>
                <h3 class="text-xl font-semibold text-secondary mb-2">Session Expired</h3>
                <p class="text-gray-600 mb-4">Your session has expired due to inactivity. Please log in again.</p>
                <p class="text-gray-600 mb-6">Redirecting in <span id="countdown">10</span> seconds...</p>
                <button onclick="redirectToLogin()" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                    Login Now
                </button>
            </div>
        </div>
    </div>`;

    document.body.insertAdjacentHTML('beforeend', sessionExpiredModalHTML);

    function handleSessionExpiration() {
        const modal = document.getElementById('sessionExpiredModal');
        const countdownEl = document.getElementById('countdown');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        let countdown = 10;
        countdownEl.textContent = countdown;

        const timer = setInterval(() => {
            countdown--;
            countdownEl.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
    }

    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    $(document).ajaxError(function (event, jqXHR) {
        if (jqXHR.status === 401 && jqXHR.responseJSON && jqXHR.responseJSON.session_expired) {
            handleSessionExpiration();
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>

<script>
    let phoneInput;

    $(document).ready(function () {
        // Initialize the international telephone input
        const phoneInputField = document.querySelector("#phone");
        phoneInput = window.intlTelInput(phoneInputField, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            preferredCountries: ["ug", "ke", "tz", "rw"],
            separateDialCode: true,
            autoPlaceholder: "polite"
        });

        $('.iti').addClass('w-full');

        phoneInputField.addEventListener("blur", function () {
            validatePhoneNumber();
        });

        fetchUserDetails();

        $('#editProfileBtn').click(function () {
            $('html, body').animate({
                scrollTop: $('#personalInfoForm').offset().top - 100
            }, 500);
        });

        $('#changePasswordBtn').click(function () {
            showModal('changePasswordModal');
        });

        $('.toggle-password').click(function () {
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

        $('#newPassword').on('input', function () {
            checkPasswordStrength($(this).val());
        });

        $('#personalInfoForm').submit(function (e) {
            e.preventDefault();
            if (validatePhoneNumber()) {
                updateProfile();
            }
        });

        $('#changePasswordForm').submit(function (e) {
            e.preventDefault();
            changePassword();
        });
    });

    function validatePhoneNumber() {
        const phoneError = document.getElementById("phone-error");
        phoneError.classList.add("hidden");

        if (phoneInput.getNumber()) {
            if (!phoneInput.isValidNumber()) {
                phoneError.textContent = "Invalid phone number for the selected country";
                phoneError.classList.remove("hidden");
                return false;
            }
            return true;
        }
        return false;
    }

    function fetchUserDetails() {
        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=getUserDetails',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const user = response.data;

                    $('#firstName').val(user.first_name || '');
                    $('#lastName').val(user.last_name || '');
                    $('#email').val(user.email || '');
                    if (user.phone) {
                        phoneInput.setNumber(user.phone);
                    }

                    const fullName = `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User';
                    $('#user-fullname').text(fullName);
                    $('#user-username').text('@' + (user.username || ''));

                    let initials = '';
                    if (user.first_name) initials += user.first_name.charAt(0).toUpperCase();
                    if (user.last_name) initials += user.last_name.charAt(0).toUpperCase();
                    if (!initials) initials = (user.username || 'U').charAt(0).toUpperCase();
                    $('#user-initials span').text(initials);

                    const joinDate = new Date(user.created_at);
                    $('#user-joined').text('Member since ' + joinDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }));

                    if (user.last_login) {
                        const lastLogin = new Date(user.last_login);
                        $('#user-lastlogin').text('Last login: ' + getTimeAgo(lastLogin));
                    } else {
                        $('#user-lastlogin').text('First login');
                    }

                    $('#overview-email').text(user.email || 'Not set');
                    $('#overview-phone').text(user.phone || 'Not set');

                    let statusHtml = '';
                    if (user.status === 'active') {
                        statusHtml = '<span class="text-green-600">Active</span>';
                    } else if (user.status === 'inactive') {
                        statusHtml = '<span class="text-yellow-600">Inactive</span>';
                    } else if (user.status === 'suspended') {
                        statusHtml = '<span class="text-red-600">Suspended</span>';
                    } else {
                        statusHtml = user.status || 'Unknown';
                    }
                    $('#overview-status').html(statusHtml);
                }
            },
            error: function (xhr) {
                console.error('Error fetching user details:', xhr);
                showErrorNotification('Failed to load user details. Please refresh the page.');
            }
        });
    }

    function updateProfile() {
        $('#profile-form-error, #profile-form-success').addClass('hidden').text('');

        const formData = {
            first_name: $('#firstName').val().trim(),
            last_name: $('#lastName').val().trim(),
            email: $('#email').val().trim(),
            phone: phoneInput.getNumber()
        };

        if (!formData.first_name || !formData.last_name || !formData.email) {
            $('#profile-form-error').removeClass('hidden').text('First name, last name, and email are required');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            $('#profile-form-error').removeClass('hidden').text('Please enter a valid email address');
            return;
        }

        const submitBtn = $('#personalInfoForm button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=updateProfile',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            success: function (response) {
                submitBtn.html(originalBtnText).prop('disabled', false);

                if (response.success) {
                    $('#profile-form-success').removeClass('hidden').text(response.message);
                    showSuccessNotification(response.message);
                    fetchUserDetails();
                } else {
                    $('#profile-form-error').removeClass('hidden').text(response.message);
                    showErrorNotification(response.message);
                }
            },
            error: function (xhr) {
                submitBtn.html(originalBtnText).prop('disabled', false);
                let errMsg = 'Server error. Please try again later.';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    errMsg = resp.message || errMsg;
                } catch { }
                $('#profile-form-error').removeClass('hidden').text(errMsg);
                showErrorNotification(errMsg);
            }
        });
    }

    function changePassword() {
        $('#password-form-error').addClass('hidden').text('');

        const currentPassword = $('#currentPassword').val();
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();

        if (!currentPassword || !newPassword || !confirmPassword) {
            $('#password-form-error').removeClass('hidden').text('All fields are required');
            return;
        }

        if (newPassword !== confirmPassword) {
            $('#password-form-error').removeClass('hidden').text('Passwords do not match');
            return;
        }

        if (!(newPassword.length >= 8 &&
            /[A-Z]/.test(newPassword) &&
            /[a-z]/.test(newPassword) &&
            /\d/.test(newPassword) &&
            /[^A-Za-z0-9]/.test(newPassword))) {
            $('#password-form-error').removeClass('hidden').text('Password does not meet the requirements');
            return;
        }

        const submitBtn = $('#changePasswordForm button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=changePassword',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword
            }),
            dataType: 'json',
            success: function (response) {
                submitBtn.html(originalBtnText).prop('disabled', false);

                if (response.success) {
                    $('#changePasswordForm')[0].reset();
                    hideModal('changePasswordModal');
                    showSuccessNotification(response.message);
                } else {
                    $('#password-form-error').removeClass('hidden').text(response.message);
                    showErrorNotification(response.message);
                }
            },
            error: function (xhr) {
                submitBtn.html(originalBtnText).prop('disabled', false);
                let errMsg = 'Server error. Please try again later.';
                try {
                    const resp = JSON.parse(xhr.responseText);
                    errMsg = resp.message || errMsg;
                } catch { }
                $('#password-form-error').removeClass('hidden').text(errMsg);
                showErrorNotification(errMsg);
            }
        });
    }

    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageEl = document.getElementById('successMessage');
        messageEl.textContent = message;
        notification.classList.remove('hidden');
        setTimeout(() => notification.classList.add('hidden'), 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');
        messageEl.textContent = message;
        notification.classList.remove('hidden');
        setTimeout(() => notification.classList.add('hidden'), 3000);
    }

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) { strength += 20; $('#length-check').removeClass('text-gray-400').addClass('text-green-600'); }
        else { $('#length-check').removeClass('text-green-600').addClass('text-gray-400'); }

        if (/[A-Z]/.test(password)) { strength += 20; $('#uppercase-check').removeClass('text-gray-400').addClass('text-green-600'); }
        else { $('#uppercase-check').removeClass('text-green-600').addClass('text-gray-400'); }

        if (/[a-z]/.test(password)) { strength += 20; $('#lowercase-check').removeClass('text-gray-400').addClass('text-green-600'); }
        else { $('#lowercase-check').removeClass('text-green-600').addClass('text-gray-400'); }

        if (/\d/.test(password)) { strength += 20; $('#number-check').removeClass('text-gray-400').addClass('text-green-600'); }
        else { $('#number-check').removeClass('text-green-600').addClass('text-gray-400'); }

        if (/[^A-Za-z0-9]/.test(password)) { strength += 20; $('#special-check').removeClass('text-gray-400').addClass('text-green-600'); }
        else { $('#special-check').removeClass('text-green-600').addClass('text-gray-400'); }

        const strengthBar = $('#passwordStrength');
        strengthBar.css('width', strength + '%');
        if (strength < 40) {
            strengthBar.removeClass('bg-yellow-500 bg-green-500').addClass('bg-red-500');
        } else if (strength < 80) {
            strengthBar.removeClass('bg-red-500 bg-green-500').addClass('bg-yellow-500');
        } else {
            strengthBar.removeClass('bg-red-500 bg-yellow-500').addClass('bg-green-500');
        }
    }

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = Math.floor(seconds / 31536000);
        if (interval > 1) return interval + ' years ago';
        if (interval === 1) return '1 year ago';
        interval = Math.floor(seconds / 2592000);
        if (interval > 1) return interval + ' months ago';
        if (interval === 1) return '1 month ago';
        interval = Math.floor(seconds / 86400);
        if (interval > 1) return interval + ' days ago';
        if (interval === 1) return '1 day ago';
        interval = Math.floor(seconds / 3600);
        if (interval > 1) return interval + ' hours ago';
        if (interval === 1) return '1 hour ago';
        interval = Math.floor(seconds / 60);
        if (interval > 1) return interval + ' minutes ago';
        if (interval === 1) return '1 minute ago';
        if (seconds < 10) return 'just now';
        return Math.floor(seconds) + ' seconds ago';
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>