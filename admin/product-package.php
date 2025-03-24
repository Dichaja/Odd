<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Package Definition';
$activeNav = 'package-definition';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL . 'login/login.php');
    exit;
}

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Package Definition</h1>
            <p class="text-sm text-gray-text mt-1">Define and manage product packaging and units of measure</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <a href="manage-products" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>
        </div>
    </div>

    <!-- Add Package Form Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-primary" id="formTitle">Add New Package Definition</h2>
            <p class="text-sm text-gray-text mt-1">Create a new package type with unit of measure</p>
        </div>

        <div class="p-6">
            <form id="packageForm" class="space-y-4">
                <input type="hidden" id="packageId" name="packageId" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <div id="packageNameContainer">
                            <select id="package_name" name="package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="" selected>Select Package</option>
                                <option value="add">Create New</option>
                                <!-- Unique package names will be populated here -->
                            </select>
                            <input type="text" id="new_package_name" name="new_package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary hidden mt-2" placeholder="Enter new package name">
                        </div>
                    </div>

                    <div>
                        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                        <input type="text" id="unit_of_measure" name="unit_of_measure" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., kg, liter, meter" required>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" id="cancelForm" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2 hidden">
                        Cancel
                    </button>
                    <button type="submit" id="submitButton" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Save Package Definition
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Package List Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Package Definitions</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="package-count">0</span> definitions found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchPackages" placeholder="Search packages..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterPackage" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All Packages</option>
                        <!-- Unique package names will be populated here -->
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="packages-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Package</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Unit of Measure</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="packages-table-body">
                    <!-- Package rows will be populated dynamically -->
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center">Loading packages...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span id="total-packages">0</span> package definitions
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <!-- Pagination numbers will be populated dynamically -->
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deletePackageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Package Definition</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this package definition? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package:</div>
                    <div class="font-medium text-gray-900" id="delete-package-name"></div>
                    <div class="text-gray-500">Unit of Measure:</div>
                    <div class="font-medium text-gray-900" id="delete-unit-measure"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div id="editPackageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideEditModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Edit Package Definition</h3>
            <button onclick="hideEditModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="editPackageForm" class="space-y-4">
                <input type="hidden" id="edit-package-id" value="">

                <div class="mb-4">
                    <label for="edit-package-name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                    <div id="editPackageNameContainer">
                        <select id="edit-package-name" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="" selected>Select Package</option>
                            <option value="add">Create New</option>
                            <!-- Unique package names will be populated here -->
                        </select>
                        <input type="text" id="edit-new-package-name" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary hidden mt-2" placeholder="Enter new package name">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="edit-unit-measure" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                    <input type="text" id="edit-unit-measure" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideEditModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="saveEditPackage" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
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

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let packagesData = [];
    let currentPage = 1;
    let totalPages = 1;
    let itemsPerPage = 10;
    let uniquePackageNames = [];

    document.addEventListener('DOMContentLoaded', function() {
        const packageForm = document.getElementById('packageForm');
        const cancelFormBtn = document.getElementById('cancelForm');
        const packageNameSelect = document.getElementById('package_name');
        const newPackageNameInput = document.getElementById('new_package_name');

        // Package name dropdown change
        packageNameSelect.addEventListener('change', function() {
            const value = this.value;

            if (value === 'add') {
                // Show input field for new package name
                this.classList.add('hidden');
                newPackageNameInput.classList.remove('hidden');
                newPackageNameInput.focus();
                cancelFormBtn.classList.remove('hidden');
            }
        });

        // Cancel add new package
        cancelFormBtn.addEventListener('click', function() {
            resetPackageNameSelection();
        });

        packageForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            const packageId = document.getElementById('packageId').value;

            if (packageId) {
                updatePackage();
            } else {
                createPackage();
            }
        });

        document.getElementById('searchPackages').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterPackages(query, document.getElementById('filterPackage').value);
        });

        document.getElementById('filterPackage').addEventListener('change', function(e) {
            const packageName = e.target.value;
            filterPackages(document.getElementById('searchPackages').value.toLowerCase(), packageName);
        });

        document.getElementById('prev-page').addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderPackages(packagesData);
            }
        });

        document.getElementById('next-page').addEventListener('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderPackages(packagesData);
            }
        });

        document.getElementById('confirmDelete').addEventListener('click', confirmDelete);

        // Edit package name dropdown change
        document.getElementById('edit-package-name').addEventListener('change', function() {
            const value = this.value;
            const newPackageInput = document.getElementById('edit-new-package-name');

            if (value === 'add') {
                // Show input field for new package name
                this.classList.add('hidden');
                newPackageInput.classList.remove('hidden');
                newPackageInput.focus();
            }
        });

        // Save edit package
        document.getElementById('saveEditPackage').addEventListener('click', function() {
            const packageId = document.getElementById('edit-package-id').value;
            const packageSelect = document.getElementById('edit-package-name');
            const newPackageInput = document.getElementById('edit-new-package-name');
            const unitOfMeasure = document.getElementById('edit-unit-measure').value;

            let packageName;
            if (packageSelect.classList.contains('hidden')) {
                packageName = newPackageInput.value.trim();
                if (!packageName) {
                    showErrorNotification('Please enter a package name');
                    return;
                }
            } else {
                packageName = packageSelect.value;
                if (packageName === '' || packageName === 'add') {
                    showErrorNotification('Please select a package name');
                    return;
                }
            }

            if (!unitOfMeasure) {
                showErrorNotification('Please enter a unit of measure');
                return;
            }

            const packageData = {
                id: packageId,
                package_name: packageName,
                unit_of_measure: unitOfMeasure
            };

            fetch(`${BASE_URL}admin/fetch/manageProductPackages/updatePackage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(packageData)
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
                        showSuccessNotification(data.message || 'Package updated successfully!');
                        hideEditModal();
                        loadPackages();
                    } else {
                        showErrorNotification(data.message || 'Failed to update package');
                    }
                })
                .catch(error => {
                    if (error.message !== 'Session expired') {
                        console.error('Error updating package:', error);
                        showErrorNotification('Failed to update package. Please try again.');
                    }
                });
        });

        loadPackages();
    });

    function resetPackageNameSelection() {
        const packageSelect = document.getElementById('package_name');
        const newPackageInput = document.getElementById('new_package_name');
        const cancelButton = document.getElementById('cancelForm');

        packageSelect.value = '';
        packageSelect.classList.remove('hidden');
        newPackageInput.classList.add('hidden');
        newPackageInput.value = '';
        cancelButton.classList.add('hidden');
    }

    function loadPackages() {
        const tableBody = document.getElementById('packages-table-body');
        tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center">Loading packages...</td></tr>';

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/getPackages`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    packagesData = data.packages;

                    // Extract unique package names
                    uniquePackageNames = [...new Set(packagesData.map(pkg => pkg.package_name))].sort();

                    // Populate package name dropdowns
                    populatePackageNameDropdowns(uniquePackageNames);

                    // Populate filter dropdown
                    populateFilterDropdown(uniquePackageNames);

                    totalPages = Math.ceil(packagesData.length / itemsPerPage);
                    renderPagination();
                    renderPackages(packagesData);
                } else {
                    showErrorNotification(data.message || 'Failed to load packages');
                    tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Error loading packages</td></tr>';
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error loading packages:', error);
                    showErrorNotification('Failed to load packages. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500">Failed to load packages</td></tr>';
                }
            });
    }

    function populatePackageNameDropdowns(packageNames) {
        const packageNameSelect = document.getElementById('package_name');
        const editPackageNameSelect = document.getElementById('edit-package-name');

        // Clear existing options except the first two (Select Package and Create New)
        while (packageNameSelect.options.length > 2) {
            packageNameSelect.remove(2);
        }

        while (editPackageNameSelect.options.length > 2) {
            editPackageNameSelect.remove(2);
        }

        // Add unique package names to both dropdowns
        packageNames.forEach(name => {
            const option1 = document.createElement('option');
            option1.value = name;
            option1.textContent = name;
            packageNameSelect.appendChild(option1);

            const option2 = document.createElement('option');
            option2.value = name;
            option2.textContent = name;
            editPackageNameSelect.appendChild(option2);
        });
    }

    function populateFilterDropdown(packageNames) {
        const filterDropdown = document.getElementById('filterPackage');
        filterDropdown.innerHTML = '<option value="">All Packages</option>';

        packageNames.forEach(packageName => {
            const option = document.createElement('option');
            option.value = packageName;
            option.textContent = packageName;
            filterDropdown.appendChild(option);
        });
    }

    function renderPagination() {
        const paginationContainer = document.getElementById('pagination-numbers');
        paginationContainer.innerHTML = '';

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }
        } else {
            paginationContainer.appendChild(createPaginationButton(1));

            if (currentPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }

            if (currentPage < totalPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            if (totalPages > 1) {
                paginationContainer.appendChild(createPaginationButton(totalPages));
            }
        }
    }

    function createPaginationButton(pageNumber) {
        const button = document.createElement('button');
        button.className = pageNumber === currentPage ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;

        button.addEventListener('click', function() {
            currentPage = pageNumber;
            renderPagination();
            renderPackages(packagesData);
        });

        return button;
    }

    function renderPackages(packages) {
        const tableBody = document.getElementById('packages-table-body');
        tableBody.innerHTML = '';

        document.getElementById('package-count').textContent = packages.length;

        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, packages.length);

        document.getElementById('showing-start').textContent = packages.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-packages').textContent = packages.length;

        const paginatedPackages = packages.slice(start, end);

        if (paginatedPackages.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center">No packages found</td></tr>';
            return;
        }

        paginatedPackages.forEach((pkg, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(pkg.package_name)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${escapeHtml(pkg.unit_of_measure)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="${pkg.uuid_id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete text-red-600 hover:text-red-800" data-id="${pkg.uuid_id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const packageId = this.getAttribute('data-id');
                editPackage(packageId);
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const packageId = this.getAttribute('data-id');
                showDeleteModal(packageId);
            });
        });
    }

    function filterPackages(query, packageName) {
        const filteredPackages = packagesData.filter(pkg => {
            const text = `${pkg.package_name} ${pkg.unit_of_measure}`.toLowerCase();
            const matchesQuery = text.includes(query);
            const matchesPackage = !packageName || pkg.package_name === packageName;
            return matchesQuery && matchesPackage;
        });

        currentPage = 1;
        totalPages = Math.ceil(filteredPackages.length / itemsPerPage);
        renderPagination();
        renderPackages(filteredPackages);
    }

    function createPackage() {
        const packageSelect = document.getElementById('package_name');
        const newPackageInput = document.getElementById('new_package_name');
        const unitOfMeasure = document.getElementById('unit_of_measure').value;

        let packageName;
        if (packageSelect.classList.contains('hidden')) {
            packageName = newPackageInput.value.trim();
        } else {
            packageName = packageSelect.value;
        }

        const packageData = {
            package_name: packageName,
            unit_of_measure: unitOfMeasure
        };

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/createPackage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(packageData)
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
                    showSuccessNotification(data.message || 'Package created successfully!');
                    resetForm();
                    loadPackages();
                } else {
                    showErrorNotification(data.message || 'Failed to create package');
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error creating package:', error);
                    showErrorNotification('Failed to create package. Please try again.');
                }
            });
    }

    function updatePackage() {
        const packageId = document.getElementById('packageId').value;
        const packageSelect = document.getElementById('package_name');
        const newPackageInput = document.getElementById('new_package_name');
        const unitOfMeasure = document.getElementById('unit_of_measure').value;

        let packageName;
        if (packageSelect.classList.contains('hidden')) {
            packageName = newPackageInput.value.trim();
        } else {
            packageName = packageSelect.value;
        }

        const packageData = {
            id: packageId,
            package_name: packageName,
            unit_of_measure: unitOfMeasure
        };

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/updatePackage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(packageData)
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
                    showSuccessNotification(data.message || 'Package updated successfully!');
                    resetForm();
                    loadPackages();
                } else {
                    showErrorNotification(data.message || 'Failed to update package');
                }
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error updating package:', error);
                    showErrorNotification('Failed to update package. Please try again.');
                }
            });
    }

    function editPackage(packageId) {
        const pkg = packagesData.find(p => p.uuid_id === packageId);

        if (pkg) {
            document.getElementById('packageId').value = pkg.uuid_id;

            // Set the package name in the dropdown or input field
            const packageSelect = document.getElementById('package_name');
            const newPackageInput = document.getElementById('new_package_name');

            // Find if the package name exists in the dropdown
            let packageExists = false;
            for (let i = 0; i < packageSelect.options.length; i++) {
                if (packageSelect.options[i].value === pkg.package_name) {
                    packageSelect.selectedIndex = i;
                    packageExists = true;
                    break;
                }
            }

            // If package name doesn't exist in dropdown, show input field
            if (!packageExists) {
                packageSelect.classList.add('hidden');
                newPackageInput.classList.remove('hidden');
                newPackageInput.value = pkg.package_name;
                document.getElementById('cancelForm').classList.remove('hidden');
            }

            document.getElementById('unit_of_measure').value = pkg.unit_of_measure;

            document.getElementById('formTitle').textContent = 'Edit Package Definition';
            document.getElementById('submitButton').textContent = 'Update Package Definition';
            document.getElementById('cancelForm').classList.remove('hidden');

            // Scroll to the form
            document.querySelector('.bg-white.rounded-lg').scrollIntoView({
                behavior: 'smooth'
            });
        }
    }

    function showDeleteModal(packageId) {
        const pkg = packagesData.find(p => p.uuid_id === packageId);

        if (pkg) {
            document.getElementById('delete-package-name').textContent = pkg.package_name;
            document.getElementById('delete-unit-measure').textContent = pkg.unit_of_measure;
            document.getElementById('confirmDelete').setAttribute('data-id', packageId);

            document.getElementById('deletePackageModal').classList.remove('hidden');
        }
    }

    function hideDeleteModal() {
        document.getElementById('deletePackageModal').classList.add('hidden');
    }

    function hideEditModal() {
        document.getElementById('editPackageModal').classList.add('hidden');

        // Reset the edit form
        const packageSelect = document.getElementById('edit-package-name');
        const newPackageInput = document.getElementById('edit-new-package-name');

        packageSelect.classList.remove('hidden');
        newPackageInput.classList.add('hidden');
        newPackageInput.value = '';
    }

    function confirmDelete() {
        const packageId = document.getElementById('confirmDelete').getAttribute('data-id');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/deletePackage`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: packageId
                })
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
                    showSuccessNotification(data.message || 'Package deleted successfully!');
                    loadPackages();
                } else {
                    showErrorNotification(data.message || 'Failed to delete package');
                }
                hideDeleteModal();
            })
            .catch(error => {
                if (error.message !== 'Session expired') {
                    console.error('Error deleting package:', error);
                    showErrorNotification('Failed to delete package. Please try again.');
                    hideDeleteModal();
                }
            });
    }

    function resetForm() {
        document.getElementById('packageForm').reset();
        document.getElementById('packageId').value = '';
        resetPackageNameSelection();
        document.getElementById('formTitle').textContent = 'Add New Package Definition';
        document.getElementById('submitButton').textContent = 'Save Package Definition';
        document.getElementById('cancelForm').classList.add('hidden');
    }

    function validateForm() {
        const packageSelect = document.getElementById('package_name');
        const newPackageInput = document.getElementById('new_package_name');
        const unitOfMeasure = document.getElementById('unit_of_measure').value.trim();

        let packageName;
        if (packageSelect.classList.contains('hidden')) {
            packageName = newPackageInput.value.trim();
        } else {
            packageName = packageSelect.value;
        }

        if (!packageName || packageName === '' || packageName === 'add') {
            showErrorNotification('Please select or enter a package name');
            return false;
        }

        if (!unitOfMeasure) {
            showErrorNotification('Please enter a unit of measure');
            return false;
        }

        return true;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showSessionExpiredModal() {
        const modal = document.getElementById('sessionExpiredModal');
        modal.classList.remove('hidden');

        let countdown = 10;
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

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>