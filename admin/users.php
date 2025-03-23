<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'User Management';
$activeNav = 'users';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL . 'login/login.php');
    exit;
}

$currentAdminId = $_SESSION['user']['id'] ?? '';

$roleMapping = [
    'super_admin' => 'Super Admin',
    'admin' => 'Admin',
    'editor' => 'Editor'
];

$availableRoles = ['super_admin', 'admin', 'editor'];
$displayRoles = ['Super Admin', 'Admin', 'Editor'];

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

function formatPhoneNumber($phone)
{
    if (!$phone) return '-';
    return substr($phone, 0, 1) === '+' ? $phone : '+' . $phone;
}

ob_start();
?>

<!-- Add intl-tel-input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">User Management</h1>
            <p class="text-sm text-gray-text mt-1">Create and manage system users</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportUsers" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="toggleUserForm" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add New User</span>
            </button>
        </div>
    </div>

    <div id="userFormCard" class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6 hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-secondary" id="formTitle">Add New User</h2>
            <p class="text-sm text-gray-text mt-1">Fill in the details to create a new user</p>
        </div>
        <div class="p-6">
            <form id="userForm" class="space-y-6">
                <input type="hidden" id="userId" name="userId" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter username"
                            required>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter email address"
                            required>
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter phone number"
                            required>
                        <div id="phone-error" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select
                            id="role"
                            name="role"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                            <option value="">Select role</option>
                            <?php foreach ($availableRoles as $index => $role): ?>
                                <option value="<?= $role ?>"><?= $displayRoles[$index] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter password"
                                required>
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <input
                                type="password"
                                id="confirmPassword"
                                name="confirmPassword"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Confirm password"
                                required>
                            <button
                                type="button"
                                id="toggleConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <label for="isActive" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="isActive" name="isActive" class="sr-only" checked>
                            <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                        </div>
                        <div class="ml-3 text-gray-700 font-medium">Active</div>
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        id="cancelForm"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                        <span id="submitButtonText">Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Users List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="users-count">0</span> users found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchUsers" placeholder="Search users..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterRole" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected="selected">Filter by Role</option>
                        <?php foreach ($availableRoles as $index => $role): ?>
                            <option value="<?= $role ?>"><?= $displayRoles[$index] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="users-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Username</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Email</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Phone</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Role</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Last Login</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
                    <!-- User rows will be populated dynamically -->
                </tbody>
            </table>
        </div>

        <div class="md:hidden p-4" id="mobile-users-list">
            <!-- Mobile user cards will be populated dynamically -->
        </div>

        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span id="total-users">0</span> users
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="deleteConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteConfirmationModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Are you sure you want to delete this user?</h3>
                <p class="text-sm text-gray-500 mt-2">This action cannot be undone.</p>
            </div>
            <div class="flex justify-center gap-3 mt-6">
                <button onclick="hideDeleteConfirmationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired due to inactivity.</p>
                <p class="text-sm text-gray-500 mt-1">Redirecting in <span id="countdown">10</span> seconds...</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Login Now
                </button>
            </div>
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

<!-- Error Notification -->
<div id="errorNotification" class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<style>
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

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

<!-- Add jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Add intl-tel-input JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let usersData = [];
    let phoneInput;

    // Function to prevent form autocomplete
    function preventFormAutocomplete() {
        // Find all forms
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            // Add autocomplete="off" to the form
            form.setAttribute('autocomplete', 'off');

            // Add autocomplete="off" to all input fields
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.setAttribute('autocomplete', 'off');

                // For password fields, use a more aggressive approach
                if (input.type === 'password') {
                    input.setAttribute('autocomplete', 'new-password');
                }
            });

            // Add a hidden input to trick browsers
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'text';
            hiddenInput.style.display = 'none';
            form.appendChild(hiddenInput);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the international telephone input
        const phoneInputField = document.querySelector("#phone");
        phoneInput = window.intlTelInput(phoneInputField, {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            preferredCountries: ["ug", "ke", "tz", "rw"],
            separateDialCode: true,
            autoPlaceholder: "polite"
        });

        // Style the country selector to match our design
        $('.iti').addClass('w-full');

        // Validate phone on blur
        phoneInputField.addEventListener("blur", function() {
            validatePhoneNumber();
        });

        // Prevent form autocomplete
        preventFormAutocomplete();

        tippy('[data-tippy-content]', {
            placement: 'top',
            arrow: true,
            theme: 'light',
        });

        const userFormCard = document.getElementById('userFormCard');
        const toggleUserFormBtn = document.getElementById('toggleUserForm');
        const cancelFormBtn = document.getElementById('cancelForm');

        toggleUserFormBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('formTitle').textContent = 'Add New User';
            document.getElementById('submitButtonText').textContent = 'Create User';
            userFormCard.classList.remove('hidden');
            document.getElementById('username').focus();
        });

        cancelFormBtn.addEventListener('click', function() {
            userFormCard.classList.add('hidden');
            resetForm();
        });

        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        toggleConfirmPassword.addEventListener('click', function() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        const userForm = document.getElementById('userForm');

        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            const formData = new FormData(userForm);
            const userId = formData.get('userId');

            if (userId) {
                updateUser(formData);
            } else {
                createUser(formData);
            }
        });

        document.getElementById('searchUsers').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterUsers(query, document.getElementById('filterRole').value);
        });

        document.getElementById('filterRole').addEventListener('change', function(e) {
            const role = e.target.value;
            filterUsers(document.getElementById('searchUsers').value.toLowerCase(), role);
        });

        // Load users on page load
        loadUsers();
    });

    // Validate phone number
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
        return true; // Allow empty phone number
    }

    function loadUsers() {
        // Show a loading indicator
        const tableBody = document.getElementById('users-table-body');
        tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center">Loading users...</td></tr>';

        fetch(`${BASE_URL}admin/fetch/manageUsers/getUsers`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.text(); // Get the raw text first for debugging
            })
            .then(text => {
                // Try to parse the JSON, if it fails, we can see the raw response
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        usersData = data.users;
                        renderUsers(usersData);
                    } else {
                        console.error('Error loading users:', data.error);
                        showErrorNotification(data.error || 'Failed to load users');
                        // Show empty state
                        tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading users</td></tr>';
                    }
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    console.error('Raw Response:', text);
                    showErrorNotification('Invalid response from server');
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error parsing server response</td></tr>';
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error loading users:', error);
                    showErrorNotification('Failed to load users. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Failed to load users</td></tr>';
                }
            });
    }

    function renderUsers(users) {
        const tableBody = document.getElementById('users-table-body');
        const mobileList = document.getElementById('mobile-users-list');

        // Clear existing content
        tableBody.innerHTML = '';
        mobileList.innerHTML = '';

        // Update counts
        document.getElementById('users-count').textContent = users.length;
        document.getElementById('showing-start').textContent = users.length > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = users.length;
        document.getElementById('total-users').textContent = users.length;

        // Render desktop table rows
        users.forEach(user => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100';

            const roleDisplay = getRoleDisplay(user.role);
            const statusClass = user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

            row.innerHTML = `
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-900">${escapeHtml(user.username)}</p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    ${escapeHtml(user.email)}
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    ${formatPhoneNumber(user.phone)}
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    ${roleDisplay}
                </td>
                <td class="px-6 py-4 text-sm text-gray-text">
                    ${formatDate(user.current_login)}
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                        ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                        <button
                            class="action-btn text-blue-600 hover:text-blue-800"
                            data-tippy-content="Edit User"
                            onclick="editUser('${user.uuid_id}')">
                            <i class="fas fa-edit"></i>
                        </button>

                        <button
                            class="action-btn text-red-600 hover:text-red-800"
                            data-tippy-content="Delete User"
                            onclick="deleteUser('${user.uuid_id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);

            // Initialize tooltips for the new buttons
            tippy(row.querySelectorAll('[data-tippy-content]'), {
                placement: 'top',
                arrow: true,
                theme: 'light',
            });
        });

        // Render mobile cards
        users.forEach(user => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-sm border border-gray-100 mb-4';

            const roleDisplay = getRoleDisplay(user.role);
            const statusClass = user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

            card.innerHTML = `
                <div class="p-4 border-b border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <p class="font-medium text-gray-900">${escapeHtml(user.username)}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Email:</span>
                            <span class="text-sm">${escapeHtml(user.email)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Phone:</span>
                            <span class="text-sm">${formatPhoneNumber(user.phone)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Role:</span>
                            <span class="text-sm">${roleDisplay}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xs text-gray-500">Last Login:</span>
                            <span class="text-sm">${formatDate(user.current_login)}</span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button
                            class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            onclick="editUser('${user.uuid_id}')">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>

                        <button
                            class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700"
                            onclick="deleteUser('${user.uuid_id}')">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            `;

            mobileList.appendChild(card);
        });
    }

    function getRoleDisplay(role) {
        const roleMap = {
            'super_admin': 'Super Admin',
            'admin': 'Admin',
            'editor': 'Editor'
        };
        return roleMap[role] || role;
    }

    function filterUsers(query, role) {
        const filteredUsers = usersData.filter(user => {
            const text = `${user.username} ${user.email} ${user.phone} ${getRoleDisplay(user.role)}`.toLowerCase();
            const matchesQuery = text.includes(query);
            const matchesRole = !role || user.role === role;
            return matchesQuery && matchesRole;
        });

        renderUsers(filteredUsers);
    }

    function formatDate(date) {
        if (!date) return '-';
        const timestamp = new Date(date).getTime();
        if (isNaN(timestamp)) return '-';

        const dateObj = new Date(timestamp);
        const day = dateObj.getDate();
        const month = dateObj.toLocaleString('default', {
            month: 'long'
        });
        const year = dateObj.getFullYear();
        const hours = dateObj.getHours();
        const minutes = dateObj.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const formattedHours = hours % 12 || 12;

        let suffix = 'th';
        if (day % 10 === 1 && day !== 11) suffix = 'st';
        else if (day % 10 === 2 && day !== 12) suffix = 'nd';
        else if (day % 10 === 3 && day !== 13) suffix = 'rd';

        return `${month} ${day}${suffix}, ${year} ${formattedHours}:${minutes.toString().padStart(2, '0')} ${ampm}`;
    }

    function formatPhoneNumber(phone) {
        if (!phone) return '-';
        return phone.startsWith('+') ? phone : `+${phone}`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function editUser(id) {
        fetch(`${BASE_URL}admin/fetch/manageUsers/getUser?id=${id}`)
            .then(response => {
                if (response.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(response => {
                // Check if the response is successful and contains data
                if (!response.success || !response.data) {
                    showErrorNotification(response.error || 'Failed to fetch user data');
                    return;
                }

                // Get the user data from the response
                const user = response.data;

                // Populate the form fields
                document.getElementById('userId').value = user.uuid_id;
                document.getElementById('username').value = user.username;
                document.getElementById('email').value = user.email;

                // Set phone number with country code
                if (user.phone) {
                    phoneInput.setNumber(user.phone);
                } else {
                    phoneInput.setNumber('');
                }

                document.getElementById('role').value = user.role;
                document.getElementById('isActive').checked = user.status === 'active';

                // Clear password fields
                document.getElementById('password').value = '';
                document.getElementById('confirmPassword').value = '';
                document.getElementById('password').required = false;
                document.getElementById('confirmPassword').required = false;

                // Update form title and button text
                document.getElementById('formTitle').textContent = 'Edit User';
                document.getElementById('submitButtonText').textContent = 'Update User';

                // Show the form
                document.getElementById('userFormCard').classList.remove('hidden');
                document.getElementById('username').focus();
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error fetching user:', error);
                    showErrorNotification('Failed to fetch user data. Please try again.');
                }
            });
    }

    function deleteUser(id) {
        document.getElementById('deleteConfirmationModal').setAttribute('data-user-id', id);
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    function hideDeleteConfirmationModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
    }

    function confirmDelete() {
        const id = document.getElementById('deleteConfirmationModal').getAttribute('data-user-id');

        fetch(`${BASE_URL}admin/fetch/manageUsers/deleteUser`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
            .then(response => {
                if (response.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessNotification(data.message || 'User deleted successfully!');
                    loadUsers();
                } else {
                    showErrorNotification(data.error || 'Failed to delete user');
                }
                hideDeleteConfirmationModal();
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error deleting user:', error);
                    showErrorNotification('Failed to delete user. Please try again.');
                    hideDeleteConfirmationModal();
                }
            });
    }

    function createUser(formData) {
        // Get the phone number from the intl-tel-input
        const phoneNumber = phoneInput.getNumber();

        const userData = {
            username: formData.get('username'),
            email: formData.get('email'),
            phone: phoneNumber,
            role: formData.get('role'),
            password: formData.get('password'),
            status: formData.get('isActive') === 'on' ? 'active' : 'inactive'
        };

        fetch(`${BASE_URL}admin/fetch/manageUsers/createUser`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            })
            .then(response => {
                if (response.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessNotification(data.message || 'User created successfully!');
                    loadUsers();
                    document.getElementById('userFormCard').classList.add('hidden');
                    resetForm();
                } else {
                    showErrorNotification(data.error || 'Failed to create user');
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error creating user:', error);
                    showErrorNotification('Failed to create user. Please try again.');
                }
            });
    }

    function updateUser(formData) {
        // Get the phone number from the intl-tel-input
        const phoneNumber = phoneInput.getNumber();

        const userId = formData.get('userId');
        const userData = {
            id: userId,
            username: formData.get('username'),
            email: formData.get('email'),
            phone: phoneNumber,
            role: formData.get('role'),
            status: formData.get('isActive') === 'on' ? 'active' : 'inactive'
        };

        if (formData.get('password')) {
            userData.password = formData.get('password');
        }

        fetch(`${BASE_URL}admin/fetch/manageUsers/updateUser`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(userData)
            })
            .then(response => {
                if (response.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showSuccessNotification(data.message || 'User updated successfully!');
                    loadUsers();
                    document.getElementById('userFormCard').classList.add('hidden');
                    resetForm();
                } else {
                    showErrorNotification(data.error || 'Failed to update user');
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error updating user:', error);
                    showErrorNotification('Failed to update user. Please try again.');
                }
            });
    }

    function resetForm() {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('confirmPassword').required = true;

        // Reset phone input
        phoneInput.setNumber('');
        document.getElementById('phone-error').classList.add('hidden');

        document.querySelector('#togglePassword i').className = 'fas fa-eye';
        document.querySelector('#toggleConfirmPassword i').className = 'fas fa-eye';

        document.getElementById('password').setAttribute('type', 'password');
        document.getElementById('confirmPassword').setAttribute('type', 'password');
    }

    function validateForm() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const role = document.getElementById('role').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const userId = document.getElementById('userId').value;

        if (!username || !email || !role) {
            showErrorNotification('Please fill in all required fields');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showErrorNotification('Please enter a valid email address');
            return false;
        }

        // Validate phone number
        if (!validatePhoneNumber()) {
            return false;
        }

        if (!userId || password || confirmPassword) {
            if (password.length < 8) {
                showErrorNotification('Password must be at least 8 characters long');
                return false;
            }

            if (password !== confirmPassword) {
                showErrorNotification('Passwords do not match');
                return false;
            }
        }

        return true;
    }

    function showSessionExpiredModal() {
        const modal = document.getElementById('sessionExpiredModal');
        modal.classList.remove('hidden');

        let countdown = 10; // Changed from 5 to 10
        const countdownElement = document.getElementById('countdown');
        countdownElement.textContent = countdown;

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
    }

    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageEl = document.getElementById('successMessage');

        // Set message
        messageEl.textContent = message;

        // Show notification
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');

        // Set message
        messageEl.textContent = message;

        // Show notification
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>