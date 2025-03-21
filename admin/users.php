<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'User Management';
$activeNav = 'users';

// Sample user data array - in a real application, this would come from a database
$usersList = [
    [
        'id' => '1',
        'username' => 'admin',
        'email' => 'admin@zzimbaonline.com',
        'phone' => '256700123456',
        'role' => 'Super Admin',
        'created_at' => '2023-01-15 08:30:00',
        'last_login' => '2024-03-07 14:22:10',
        'is_active' => true
    ],
    [
        'id' => '2',
        'username' => 'john.doe',
        'email' => 'john.doe@zzimbaonline.com',
        'phone' => '256701234567',
        'role' => 'Admin',
        'created_at' => '2023-02-20 10:15:00',
        'last_login' => '2024-03-05 09:45:22',
        'is_active' => true
    ],
    [
        'id' => '3',
        'username' => 'jane.smith',
        'email' => 'jane.smith@zzimbaonline.com',
        'phone' => '256772345678',
        'role' => 'Editor',
        'created_at' => '2023-03-10 14:20:00',
        'last_login' => '2024-02-28 16:30:45',
        'is_active' => false
    ],
    [
        'id' => '4',
        'username' => 'robert.johnson',
        'email' => 'robert.johnson@zzimbaonline.com',
        'phone' => '256783456789',
        'role' => 'Viewer',
        'created_at' => '2023-04-05 09:10:00',
        'last_login' => '2024-03-01 11:15:30',
        'is_active' => true
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

// Available roles
$availableRoles = ['Super Admin', 'Admin', 'Editor', 'Viewer'];

ob_start();
?>

<div class="space-y-6">
    <!-- Page Header -->
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

    <!-- User Form Card -->
    <div id="userFormCard" class="bg-white rounded-lg shadow-sm border border-gray-100 mb-6 hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-secondary" id="formTitle">Add New User</h2>
            <p class="text-sm text-gray-text mt-1">Fill in the details to create a new user</p>
        </div>
        <div class="p-6">
            <form id="userForm" class="space-y-6">
                <input type="hidden" id="userId" name="userId" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
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

                    <!-- Email -->
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

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter phone number"
                            required>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select
                            id="role"
                            name="role"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                            <option value="">Select role</option>
                            <?php foreach ($availableRoles as $role): ?>
                                <option value="<?= $role ?>"><?= $role ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Password -->
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

                    <!-- Confirm Password -->
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

                <!-- Active Status -->
                <div class="flex items-center">
                    <label for="isActive" class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" id="isActive" name="isActive" class="sr-only">
                            <div class="block bg-gray-200 w-14 h-8 rounded-full"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition"></div>
                        </div>
                        <div class="ml-3 text-gray-700 font-medium">Active</div>
                    </label>
                </div>

                <!-- Form Buttons -->
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

    <!-- Users List Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Users List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="users-count"><?= count($usersList) ?></span> users found
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
                        <?php foreach ($availableRoles as $role): ?>
                            <option value="<?= $role ?>"><?= $role ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Users Table -->
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
                <tbody>
                    <?php foreach ($usersList as $user): ?>
                        <tr class="border-b border-gray-100">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatPhoneNumber($user['phone']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($user['role']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($user['last_login']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="action-btn text-blue-600 hover:text-blue-800"
                                        data-tippy-content="Edit User"
                                        onclick="editUser('<?= $user['id'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button
                                        class="action-btn text-red-600 hover:text-red-800"
                                        data-tippy-content="Delete User"
                                        onclick="deleteUser('<?= $user['id'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Users List -->
        <div class="md:hidden p-4">
            <?php foreach ($usersList as $user): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Email:</span>
                                <span class="text-sm"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Phone:</span>
                                <span class="text-sm"><?= formatPhoneNumber($user['phone']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Role:</span>
                                <span class="text-sm"><?= htmlspecialchars($user['role']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Last Login:</span>
                                <span class="text-sm"><?= formatDate($user['last_login']) ?></span>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button
                                class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                onclick="editUser('<?= $user['id'] ?>')">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>

                            <button
                                class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700"
                                onclick="deleteUser('<?= $user['id'] ?>')">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($usersList) ?></span> of <span id="total-users"><?= count($usersList) ?></span> users
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

<!-- Delete Confirmation Modal -->
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

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        tippy('[data-tippy-content]', {
            placement: 'top',
            arrow: true,
            theme: 'light',
        });

        // Toggle user form
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

        // Toggle password visibility
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

        // Form submission
        const userForm = document.getElementById('userForm');

        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            if (!validateForm()) {
                return;
            }

            // Get form data
            const formData = new FormData(userForm);
            const userId = formData.get('userId');

            // Check if it's an edit or create operation
            if (userId) {
                // Update existing user
                updateUser(formData);
            } else {
                // Create new user
                createUser(formData);
            }

            // Hide form and reset
            userFormCard.classList.add('hidden');
            resetForm();
        });

        // Search functionality
        document.getElementById('searchUsers').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterUsers(query, document.getElementById('filterRole').value);
        });

        // Role filter
        document.getElementById('filterRole').addEventListener('change', function(e) {
            const role = e.target.value;
            filterUsers(document.getElementById('searchUsers').value.toLowerCase(), role);
        });
    });

    // User data
    const userData = <?= json_encode($usersList) ?>;

    // Filter users based on search query and role
    function filterUsers(query, role) {
        // Filter desktop rows
        document.querySelectorAll('#users-table tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowRole = row.querySelector('td:nth-child(4)').textContent.trim();

            const matchesQuery = text.includes(query);
            const matchesRole = !role || rowRole === role;

            row.style.display = matchesQuery && matchesRole ? '' : 'none';
        });

        // Filter mobile cards
        document.querySelectorAll('.md\\:hidden > div').forEach(card => {
            const text = card.textContent.toLowerCase();
            const cardRole = card.querySelector('div:nth-child(1) div:nth-child(2) div:nth-child(3) span:nth-child(2)').textContent.trim();

            const matchesQuery = text.includes(query);
            const matchesRole = !role || cardRole === role;

            card.style.display = matchesQuery && matchesRole ? '' : 'none';
        });

        updatePagination();
    }

    // Update pagination info
    function updatePagination() {
        const visibleRows = document.querySelectorAll('#users-table tbody tr:not([style*="display: none"])').length;
        const visibleCards = document.querySelectorAll('.md\\:hidden > div:not([style*="display: none"])').length;

        const visibleCount = window.innerWidth >= 768 ? visibleRows : visibleCards;

        document.getElementById('showing-start').textContent = visibleCount > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = visibleCount;
        document.getElementById('total-users').textContent = userData.length;
    }

    // Edit user
    function editUser(id) {
        const user = userData.find(u => u.id === id);
        if (!user) return;

        // Populate form
        document.getElementById('userId').value = user.id;
        document.getElementById('username').value = user.username;
        document.getElementById('email').value = user.email;
        document.getElementById('phone').value = user.phone;
        document.getElementById('role').value = user.role;
        document.getElementById('isActive').checked = user.is_active;

        // Clear password fields
        document.getElementById('password').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('password').required = false;
        document.getElementById('confirmPassword').required = false;

        // Update form title and button text
        document.getElementById('formTitle').textContent = 'Edit User';
        document.getElementById('submitButtonText').textContent = 'Update User';

        // Show form
        document.getElementById('userFormCard').classList.remove('hidden');
        document.getElementById('username').focus();
    }

    // Delete user
    function deleteUser(id) {
        // Store the ID for confirmation
        document.getElementById('deleteConfirmationModal').setAttribute('data-user-id', id);
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    function hideDeleteConfirmationModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
    }

    function confirmDelete() {
        const id = document.getElementById('deleteConfirmationModal').getAttribute('data-user-id');
        // Here you would typically make an API call to delete the user
        console.log('Deleting user:', id);
        alert('User deleted successfully!');
        hideDeleteConfirmationModal();
    }

    // Create new user
    function createUser(formData) {
        const newUser = {
            id: Math.floor(Math.random() * 1000).toString(),
            username: formData.get('username'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            role: formData.get('role'),
            is_active: formData.get('isActive') === 'on',
            created_at: new Date().toISOString(),
            last_login: null
        };

        console.log('Creating user:', newUser);
        alert('User created successfully!');
    }

    // Update existing user
    function updateUser(formData) {
        const userId = formData.get('userId');
        // Here you would typically make an API call to update the user
        const updatedUser = {
            id: userId,
            username: formData.get('username'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            role: formData.get('role'),
            is_active: formData.get('isActive') === 'on'
        };

        console.log('Updating user:', updatedUser);
        alert('User updated successfully!');
    }

    // Reset form
    function resetForm() {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('confirmPassword').required = true;

        // Reset password toggle icons
        document.querySelector('#togglePassword i').className = 'fas fa-eye';
        document.querySelector('#toggleConfirmPassword i').className = 'fas fa-eye';

        // Reset password input types
        document.getElementById('password').setAttribute('type', 'password');
        document.getElementById('confirmPassword').setAttribute('type', 'password');
    }

    // Validate form
    function validateForm() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const role = document.getElementById('role').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const userId = document.getElementById('userId').value;

        // Check required fields
        if (!username || !email || !phone || !role) {
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

        // Check password only for new users or if password is provided for existing users
        if (!userId || password || confirmPassword) {
            // Check password length
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                return false;
            }

            // Check password match
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }
        }

        return true;
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>