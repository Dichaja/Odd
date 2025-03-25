<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Package Definition';
$activeNav = 'package-definition';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL);
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
            <a href="products" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tab-package-names" class="border-primary text-primary hover:text-primary hover:border-primary px-1 py-4 text-sm font-medium border-b-2" aria-current="page">
                Package Names
            </button>
            <button id="tab-si-units" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 px-1 py-4 text-sm font-medium border-b-2">
                SI Units
            </button>
        </nav>
    </div>

    <!-- Package Names Section -->
    <div id="package-names-section">
        <!-- Add Package Name Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-primary" id="packageNameFormTitle">Add New Package Name</h2>
                <p class="text-sm text-gray-text mt-1">Create a new package type (e.g., Bag, Box, Bottle)</p>
            </div>

            <div class="p-6">
                <form id="packageNameForm" class="space-y-4">
                    <input type="hidden" id="packageNameId" name="packageNameId" value="">

                    <div>
                        <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <input type="text" id="package_name" name="package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., Bag, Box, Bottle" required>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" id="cancelPackageNameForm" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2 hidden">
                            Cancel
                        </button>
                        <button type="submit" id="submitPackageNameButton" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save Package Name
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Package Names List Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mt-6">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Package Names</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="package-name-count">0</span> package names found
                    </p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchPackageNames" placeholder="Search package names..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="package-names-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Package Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="package-names-table-body">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center">Loading package names...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Package Names -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start-package-names">0</span> to <span id="showing-end-package-names">0</span> of <span id="total-package-names">0</span> package names
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page-package-names" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers-package-names" class="flex items-center">
                    </div>
                    <button id="next-page-package-names" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SI Units Section -->
    <div id="si-units-section" class="hidden">
        <!-- Add SI Unit Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-primary" id="siUnitFormTitle">Add New SI Unit</h2>
                <p class="text-sm text-gray-text mt-1">Create a new SI unit for a package type (e.g., kg, liter, meter)</p>
            </div>

            <div class="p-6">
                <form id="siUnitForm" class="space-y-4">
                    <input type="hidden" id="siUnitId" name="siUnitId" value="">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="package_name_id" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                            <select id="package_name_id" name="package_name_id" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                                <option value="" selected>Select Package Name</option>
                            </select>
                        </div>

                        <div>
                            <label for="si_unit" class="block text-sm font-medium text-gray-700 mb-1">SI Unit</label>
                            <input type="text" id="si_unit" name="si_unit" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., kg, liter, meter" required>
                        </div>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="button" id="cancelSIUnitForm" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2 hidden">
                            Cancel
                        </button>
                        <button type="submit" id="submitSIUnitButton" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save SI Unit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SI Units List Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mt-6">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">SI Units</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="si-unit-count">0</span> SI units found
                    </p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchSIUnits" placeholder="Search SI units..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select id="filterPackageName" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                            <option value="" selected>All Package Names</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="si-units-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Package Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">SI Unit</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Unit of Measure</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="si-units-table-body">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">Loading SI units...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for SI Units -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start-si-units">0</span> to <span id="showing-end-si-units">0</span> of <span id="total-si-units">0</span> SI units
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page-si-units" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers-si-units" class="flex items-center">
                    </div>
                    <button id="next-page-si-units" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[999] hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span id="loadingMessage" class="text-gray-700 font-medium">Loading...</span>
    </div>
</div>

<!-- Delete Package Name Modal -->
<div id="deletePackageNameModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeletePackageNameModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Package Name</h3>
            <button onclick="hideDeletePackageNameModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this package name? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="delete-package-name"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeletePackageNameModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDeletePackageName" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Delete SI Unit Modal -->
<div id="deleteSIUnitModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteSIUnitModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete SI Unit</h3>
            <button onclick="hideDeleteSIUnitModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this SI unit? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="delete-si-unit-package-name"></div>
                    <div class="text-gray-500">SI Unit:</div>
                    <div class="font-medium text-gray-900" id="delete-si-unit"></div>
                    <div class="text-gray-500">Unit of Measure:</div>
                    <div class="font-medium text-gray-900" id="delete-unit-of-measure"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteSIUnitModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDeleteSIUnit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
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

    // Package Names variables
    let packageNamesData = [];
    let currentPackageNamesPage = 1;
    let totalPackageNamesPages = 1;
    let itemsPerPage = 10;

    // SI Units variables
    let siUnitsData = [];
    let currentSIUnitsPage = 1;
    let totalSIUnitsPages = 1;

    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabPackageNames = document.getElementById('tab-package-names');
        const tabSIUnits = document.getElementById('tab-si-units');
        const packageNamesSection = document.getElementById('package-names-section');
        const siUnitsSection = document.getElementById('si-units-section');

        tabPackageNames.addEventListener('click', function() {
            tabPackageNames.classList.add('border-primary', 'text-primary');
            tabPackageNames.classList.remove('border-transparent', 'text-gray-500');
            tabSIUnits.classList.add('border-transparent', 'text-gray-500');
            tabSIUnits.classList.remove('border-primary', 'text-primary');
            packageNamesSection.classList.remove('hidden');
            siUnitsSection.classList.add('hidden');
        });

        tabSIUnits.addEventListener('click', function() {
            tabSIUnits.classList.add('border-primary', 'text-primary');
            tabSIUnits.classList.remove('border-transparent', 'text-gray-500');
            tabPackageNames.classList.add('border-transparent', 'text-gray-500');
            tabPackageNames.classList.remove('border-primary', 'text-primary');
            siUnitsSection.classList.remove('hidden');
            packageNamesSection.classList.add('hidden');
        });

        // Package Names Form
        const packageNameForm = document.getElementById('packageNameForm');
        const cancelPackageNameFormBtn = document.getElementById('cancelPackageNameForm');

        packageNameForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const packageNameId = document.getElementById('packageNameId').value;
            const packageName = document.getElementById('package_name').value.trim();

            if (!packageName) {
                showErrorNotification('Please enter a package name');
                return;
            }

            if (packageNameId) {
                updatePackageName(packageNameId, packageName);
            } else {
                createPackageName(packageName);
            }
        });

        cancelPackageNameFormBtn.addEventListener('click', function() {
            resetPackageNameForm();
        });

        // SI Units Form
        const siUnitForm = document.getElementById('siUnitForm');
        const cancelSIUnitFormBtn = document.getElementById('cancelSIUnitForm');

        siUnitForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const siUnitId = document.getElementById('siUnitId').value;
            const packageNameId = document.getElementById('package_name_id').value;
            const siUnit = document.getElementById('si_unit').value.trim();

            if (!packageNameId) {
                showErrorNotification('Please select a package name');
                return;
            }

            if (!siUnit) {
                showErrorNotification('Please enter an SI unit');
                return;
            }

            if (siUnitId) {
                updateSIUnit(siUnitId, packageNameId, siUnit);
            } else {
                createSIUnit(packageNameId, siUnit);
            }
        });

        cancelSIUnitFormBtn.addEventListener('click', function() {
            resetSIUnitForm();
        });

        // Search and Filter
        document.getElementById('searchPackageNames').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterPackageNames(query);
        });

        document.getElementById('searchSIUnits').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterSIUnits(query, document.getElementById('filterPackageName').value);
        });

        document.getElementById('filterPackageName').addEventListener('change', function(e) {
            const packageName = e.target.value;
            filterSIUnits(document.getElementById('searchSIUnits').value.toLowerCase(), packageName);
        });

        // Pagination for Package Names
        document.getElementById('prev-page-package-names').addEventListener('click', function() {
            if (currentPackageNamesPage > 1) {
                currentPackageNamesPage--;
                renderPackageNamesPagination();
                renderPackageNames(packageNamesData);
            }
        });

        document.getElementById('next-page-package-names').addEventListener('click', function() {
            if (currentPackageNamesPage < totalPackageNamesPages) {
                currentPackageNamesPage++;
                renderPackageNamesPagination();
                renderPackageNames(packageNamesData);
            }
        });

        // Pagination for SI Units
        document.getElementById('prev-page-si-units').addEventListener('click', function() {
            if (currentSIUnitsPage > 1) {
                currentSIUnitsPage--;
                renderSIUnitsPagination();
                renderSIUnits(siUnitsData);
            }
        });

        document.getElementById('next-page-si-units').addEventListener('click', function() {
            if (currentSIUnitsPage < totalSIUnitsPages) {
                currentSIUnitsPage++;
                renderSIUnitsPagination();
                renderSIUnits(siUnitsData);
            }
        });

        // Delete confirmations
        document.getElementById('confirmDeletePackageName').addEventListener('click', confirmDeletePackageName);
        document.getElementById('confirmDeleteSIUnit').addEventListener('click', confirmDeleteSIUnit);

        // Load data
        loadPackageNames();
        loadSIUnits();
    });

    // Package Names Functions
    function loadPackageNames() {
        const tableBody = document.getElementById('package-names-table-body');
        tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center">Loading package names...</td></tr>';

        showLoading('Loading package names...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/getPackageNames`)
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
                hideLoading();
                if (data.success) {
                    packageNamesData = data.packageNames;

                    totalPackageNamesPages = Math.ceil(packageNamesData.length / itemsPerPage);
                    renderPackageNamesPagination();
                    renderPackageNames(packageNamesData);

                    // Also update the package name dropdown in the SI Units form
                    populatePackageNameDropdown(packageNamesData);
                } else {
                    showErrorNotification(data.message || 'Failed to load package names');
                    tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Error loading package names</td></tr>';
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading package names:', error);
                    showErrorNotification('Failed to load package names. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center text-red-500">Failed to load package names</td></tr>';
                }
            });
    }

    function renderPackageNames(packageNames) {
        const tableBody = document.getElementById('package-names-table-body');
        tableBody.innerHTML = '';

        document.getElementById('package-name-count').textContent = packageNames.length;

        const start = (currentPackageNamesPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, packageNames.length);

        document.getElementById('showing-start-package-names').textContent = packageNames.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end-package-names').textContent = end;
        document.getElementById('total-package-names').textContent = packageNames.length;

        const paginatedPackageNames = packageNames.slice(start, end);

        if (paginatedPackageNames.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center">No package names found</td></tr>';
            return;
        }

        paginatedPackageNames.forEach((pkg, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            row.innerHTML = `
    <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
    <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(pkg.package_name)}</td>
    <td class="px-6 py-4 text-sm">
        <div class="flex items-center gap-2">
            <button class="btn-edit-package-name text-blue-600 hover:text-blue-800" data-id="${pkg.uuid_id}" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn-delete-package-name text-red-600 hover:text-red-800" data-id="${pkg.uuid_id}" title="Delete">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    </td>
`;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit-package-name').forEach(button => {
            button.addEventListener('click', function() {
                const packageNameId = this.getAttribute('data-id');
                editPackageName(packageNameId);
            });
        });

        document.querySelectorAll('.btn-delete-package-name').forEach(button => {
            button.addEventListener('click', function() {
                const packageNameId = this.getAttribute('data-id');
                showDeletePackageNameModal(packageNameId);
            });
        });
    }

    function renderPackageNamesPagination() {
        const paginationContainer = document.getElementById('pagination-numbers-package-names');
        paginationContainer.innerHTML = '';

        const prevButton = document.getElementById('prev-page-package-names');
        const nextButton = document.getElementById('next-page-package-names');

        prevButton.disabled = currentPackageNamesPage === 1;
        nextButton.disabled = currentPackageNamesPage === totalPackageNamesPages;

        if (totalPackageNamesPages <= 5) {
            for (let i = 1; i <= totalPackageNamesPages; i++) {
                paginationContainer.appendChild(createPaginationButton(i, 'package-names'));
            }
        } else {
            paginationContainer.appendChild(createPaginationButton(1, 'package-names'));

            if (currentPackageNamesPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentPackageNamesPage - 1); i <= Math.min(totalPackageNamesPages - 1, currentPackageNamesPage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i, 'package-names'));
            }

            if (currentPackageNamesPage < totalPackageNamesPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            if (totalPackageNamesPages > 1) {
                paginationContainer.appendChild(createPaginationButton(totalPackageNamesPages, 'package-names'));
            }
        }
    }

    function createPaginationButton(pageNumber, type) {
        const button = document.createElement('button');
        const isActive = type === 'package-names' ?
            pageNumber === currentPackageNamesPage :
            pageNumber === currentSIUnitsPage;

        button.className = isActive ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;

        button.addEventListener('click', function() {
            if (type === 'package-names') {
                currentPackageNamesPage = pageNumber;
                renderPackageNamesPagination();
                renderPackageNames(packageNamesData);
            } else {
                currentSIUnitsPage = pageNumber;
                renderSIUnitsPagination();
                renderSIUnits(siUnitsData);
            }
        });

        return button;
    }

    function filterPackageNames(query) {
        const filteredPackageNames = packageNamesData.filter(pkg => {
            return pkg.package_name.toLowerCase().includes(query);
        });

        currentPackageNamesPage = 1;
        totalPackageNamesPages = Math.ceil(filteredPackageNames.length / itemsPerPage);
        renderPackageNamesPagination();
        renderPackageNames(filteredPackageNames);
    }

    function createPackageName(packageName) {
        showLoading('Creating package name...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/createPackageName`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    package_name: packageName
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Package name created successfully!');
                    resetPackageNameForm();
                    loadPackageNames();
                } else {
                    showErrorNotification(data.message || 'Failed to create package name');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error creating package name:', error);
                    showErrorNotification('Failed to create package name. Please try again.');
                }
            });
    }

    function updatePackageName(packageNameId, packageName) {
        showLoading('Updating package name...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/updatePackageName`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: packageNameId,
                    package_name: packageName
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Package name updated successfully!');
                    resetPackageNameForm();
                    loadPackageNames();
                } else {
                    showErrorNotification(data.message || 'Failed to update package name');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error updating package name:', error);
                    showErrorNotification('Failed to update package name. Please try again.');
                }
            });
    }

    function editPackageName(packageNameId) {
        const pkg = packageNamesData.find(p => p.uuid_id === packageNameId);

        if (pkg) {
            document.getElementById('packageNameId').value = pkg.uuid_id;
            document.getElementById('package_name').value = pkg.package_name;

            document.getElementById('packageNameFormTitle').textContent = 'Edit Package Name';
            document.getElementById('submitPackageNameButton').textContent = 'Update Package Name';
            document.getElementById('cancelPackageNameForm').classList.remove('hidden');

            // Scroll to the form
            document.querySelector('#package-names-section .bg-white.rounded-lg').scrollIntoView({
                behavior: 'smooth'
            });
        }
    }

    function showDeletePackageNameModal(packageNameId) {
        const pkg = packageNamesData.find(p => p.uuid_id === packageNameId);

        if (pkg) {
            document.getElementById('delete-package-name').textContent = pkg.package_name;
            document.getElementById('confirmDeletePackageName').setAttribute('data-id', packageNameId);

            document.getElementById('deletePackageNameModal').classList.remove('hidden');
        }
    }

    function hideDeletePackageNameModal() {
        document.getElementById('deletePackageNameModal').classList.add('hidden');
    }

    function confirmDeletePackageName() {
        const packageNameId = document.getElementById('confirmDeletePackageName').getAttribute('data-id');

        showLoading('Deleting package name...');
        hideDeletePackageNameModal();

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/deletePackageName`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: packageNameId
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Package name deleted successfully!');
                    loadPackageNames();
                    loadSIUnits(); // Reload SI units as well since they depend on package names
                } else {
                    showErrorNotification(data.message || 'Failed to delete package name');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error deleting package name:', error);
                    showErrorNotification('Failed to delete package name. Please try again.');
                }
            });
    }

    function resetPackageNameForm() {
        document.getElementById('packageNameForm').reset();
        document.getElementById('packageNameId').value = '';
        document.getElementById('packageNameFormTitle').textContent = 'Add New Package Name';
        document.getElementById('submitPackageNameButton').textContent = 'Save Package Name';
        document.getElementById('cancelPackageNameForm').classList.add('hidden');
    }

    // SI Units Functions
    function loadSIUnits() {
        const tableBody = document.getElementById('si-units-table-body');
        tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">Loading SI units...</td></tr>';

        showLoading('Loading SI units...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/getSIUnits`)
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
                hideLoading();
                if (data.success) {
                    siUnitsData = data.siUnits;

                    // Extract unique package names for filter dropdown
                    const uniquePackageNames = [...new Set(siUnitsData.map(unit => unit.package_name))].sort();
                    populateFilterDropdown(uniquePackageNames);

                    totalSIUnitsPages = Math.ceil(siUnitsData.length / itemsPerPage);
                    renderSIUnitsPagination();
                    renderSIUnits(siUnitsData);
                } else {
                    showErrorNotification(data.message || 'Failed to load SI units');
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading SI units</td></tr>';
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading SI units:', error);
                    showErrorNotification('Failed to load SI units. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Failed to load SI units</td></tr>';
                }
            });
    }

    function populatePackageNameDropdown(packageNames) {
        const packageNameSelect = document.getElementById('package_name_id');
        const filterDropdown = document.getElementById('filterPackageName');

        // Clear existing options except the first one (Select Package Name)
        while (packageNameSelect.options.length > 1) {
            packageNameSelect.remove(1);
        }

        // Add package names to dropdown
        packageNames.forEach(pkg => {
            const option = document.createElement('option');
            option.value = pkg.uuid_id;
            option.textContent = pkg.package_name;
            packageNameSelect.appendChild(option);
        });
    }

    function populateFilterDropdown(packageNames) {
        const filterDropdown = document.getElementById('filterPackageName');
        filterDropdown.innerHTML = '<option value="">All Package Names</option>';

        packageNames.forEach(packageName => {
            const option = document.createElement('option');
            option.value = packageName;
            option.textContent = packageName;
            filterDropdown.appendChild(option);
        });
    }

    function renderSIUnits(siUnits) {
        const tableBody = document.getElementById('si-units-table-body');
        tableBody.innerHTML = '';

        document.getElementById('si-unit-count').textContent = siUnits.length;

        const start = (currentSIUnitsPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, siUnits.length);

        document.getElementById('showing-start-si-units').textContent = siUnits.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end-si-units').textContent = end;
        document.getElementById('total-si-units').textContent = siUnits.length;

        const paginatedSIUnits = siUnits.slice(start, end);

        if (paginatedSIUnits.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">No SI units found</td></tr>';
            return;
        }

        paginatedSIUnits.forEach((unit, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(unit.package_name)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${escapeHtml(unit.si_unit)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${escapeHtml(unit.unit_of_measure)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit-si-unit text-blue-600 hover:text-blue-800" data-id="${unit.uuid_id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete-si-unit text-red-600 hover:text-red-800" data-id="${unit.uuid_id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit-si-unit').forEach(button => {
            button.addEventListener('click', function() {
                const siUnitId = this.getAttribute('data-id');
                editSIUnit(siUnitId);
            });
        });

        document.querySelectorAll('.btn-delete-si-unit').forEach(button => {
            button.addEventListener('click', function() {
                const siUnitId = this.getAttribute('data-id');
                showDeleteSIUnitModal(siUnitId);
            });
        });
    }

    function renderSIUnitsPagination() {
        const paginationContainer = document.getElementById('pagination-numbers-si-units');
        paginationContainer.innerHTML = '';

        const prevButton = document.getElementById('prev-page-si-units');
        const nextButton = document.getElementById('next-page-si-units');

        prevButton.disabled = currentSIUnitsPage === 1;
        nextButton.disabled = currentSIUnitsPage === totalSIUnitsPages;

        if (totalSIUnitsPages <= 5) {
            for (let i = 1; i <= totalSIUnitsPages; i++) {
                paginationContainer.appendChild(createPaginationButton(i, 'si-units'));
            }
        } else {
            paginationContainer.appendChild(createPaginationButton(1, 'si-units'));

            if (currentSIUnitsPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentSIUnitsPage - 1); i <= Math.min(totalSIUnitsPages - 1, currentSIUnitsPage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i, 'si-units'));
            }

            if (currentSIUnitsPage < totalSIUnitsPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            if (totalSIUnitsPages > 1) {
                paginationContainer.appendChild(createPaginationButton(totalSIUnitsPages, 'si-units'));
            }
        }
    }

    function filterSIUnits(query, packageName) {
        const filteredSIUnits = siUnitsData.filter(unit => {
            const text = `${unit.package_name} ${unit.si_unit} ${unit.unit_of_measure}`.toLowerCase();
            const matchesQuery = text.includes(query);
            const matchesPackage = !packageName || unit.package_name === packageName;
            return matchesQuery && matchesPackage;
        });

        currentSIUnitsPage = 1;
        totalSIUnitsPages = Math.ceil(filteredSIUnits.length / itemsPerPage);
        renderSIUnitsPagination();
        renderSIUnits(filteredSIUnits);
    }

    function createSIUnit(packageNameId, siUnit) {
        showLoading('Creating SI unit...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/createSIUnit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    package_name_id: packageNameId,
                    si_unit: siUnit
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'SI unit created successfully!');
                    resetSIUnitForm();
                    loadSIUnits();
                } else {
                    showErrorNotification(data.message || 'Failed to create SI unit');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error creating SI unit:', error);
                    showErrorNotification('Failed to create SI unit. Please try again.');
                }
            });
    }

    function updateSIUnit(siUnitId, packageNameId, siUnit) {
        showLoading('Updating SI unit...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/updateSIUnit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: siUnitId,
                    package_name_id: packageNameId,
                    si_unit: siUnit
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'SI unit updated successfully!');
                    resetSIUnitForm();
                    loadSIUnits();
                } else {
                    showErrorNotification(data.message || 'Failed to update SI unit');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error updating SI unit:', error);
                    showErrorNotification('Failed to update SI unit. Please try again.');
                }
            });
    }

    function editSIUnit(siUnitId) {
        const unit = siUnitsData.find(u => u.uuid_id === siUnitId);

        if (unit) {
            document.getElementById('siUnitId').value = unit.uuid_id;
            document.getElementById('package_name_id').value = unit.package_name_uuid_id;
            document.getElementById('si_unit').value = unit.si_unit;

            document.getElementById('siUnitFormTitle').textContent = 'Edit SI Unit';
            document.getElementById('submitSIUnitButton').textContent = 'Update SI Unit';
            document.getElementById('cancelSIUnitForm').classList.remove('hidden');

            // Scroll to the form
            document.querySelector('#si-units-section .bg-white.rounded-lg').scrollIntoView({
                behavior: 'smooth'
            });
        }
    }

    function showDeleteSIUnitModal(siUnitId) {
        const unit = siUnitsData.find(u => u.uuid_id === siUnitId);

        if (unit) {
            document.getElementById('delete-si-unit-package-name').textContent = unit.package_name;
            document.getElementById('delete-si-unit').textContent = unit.si_unit;
            document.getElementById('delete-unit-of-measure').textContent = unit.unit_of_measure;
            document.getElementById('confirmDeleteSIUnit').setAttribute('data-id', siUnitId);

            document.getElementById('deleteSIUnitModal').classList.remove('hidden');
        }
    }

    function hideDeleteSIUnitModal() {
        document.getElementById('deleteSIUnitModal').classList.add('hidden');
    }

    function confirmDeleteSIUnit() {
        const siUnitId = document.getElementById('confirmDeleteSIUnit').getAttribute('data-id');

        showLoading('Deleting SI unit...');
        hideDeleteSIUnitModal();

        fetch(`${BASE_URL}admin/fetch/manageProductPackages/deleteSIUnit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: siUnitId
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
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'SI unit deleted successfully!');
                    loadSIUnits();
                } else {
                    showErrorNotification(data.message || 'Failed to delete SI unit');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error deleting SI unit:', error);
                    showErrorNotification('Failed to delete SI unit. Please try again.');
                }
            });
    }

    function resetSIUnitForm() {
        document.getElementById('siUnitForm').reset();
        document.getElementById('siUnitId').value = '';
        document.getElementById('siUnitFormTitle').textContent = 'Add New SI Unit';
        document.getElementById('submitSIUnitButton').textContent = 'Save SI Unit';
        document.getElementById('cancelSIUnitForm').classList.add('hidden');
    }

    // Utility Functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
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