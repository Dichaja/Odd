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

    <!-- Unit of Measure Form Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-primary" id="unitOfMeasureFormTitle">Add New Unit of Measure</h2>
            <p class="text-sm text-gray-text mt-1">Create a new unit of measure by selecting or creating a package name and SI unit</p>
        </div>

        <div class="p-6">
            <form id="unitOfMeasureForm" class="space-y-4">
                <input type="hidden" id="unitOfMeasureId" name="unitOfMeasureId" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="package_name_select" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <select id="package_name_select" name="package_name_select" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="" disabled selected>Select Package Name</option>
                            <option value="create_new">Create New</option>
                        </select>
                        <div id="new_package_name_container" class="mt-2 hidden">
                            <input type="text" id="new_package_name" name="new_package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter new package name">
                        </div>
                    </div>

                    <div>
                        <label for="si_unit_select" class="block text-sm font-medium text-gray-700 mb-1">SI Unit</label>
                        <select id="si_unit_select" name="si_unit_select" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="" disabled selected>Select SI Unit</option>
                            <option value="create_new">Create New</option>
                        </select>
                        <div id="new_si_unit_container" class="mt-2 hidden">
                            <input type="text" id="new_si_unit" name="new_si_unit" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter new SI unit">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" id="cancelUnitOfMeasureForm" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2 hidden">
                        Cancel
                    </button>
                    <button type="submit" id="submitUnitOfMeasureButton" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Save Unit of Measure
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Units of Measure List Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mt-6">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Units of Measure</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="unit-of-measure-count">0</span> units of measure found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchUnitsOfMeasure" placeholder="Search units of measure..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterPackageName" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All Package Names</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterSIUnit" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All SI Units</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All Statuses</option>
                        <option value="Approved">Approved</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="units-of-measure-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">SI Unit</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Package Name</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Unit of Measure</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="units-of-measure-table-body">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center">Loading units of measure...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination for Units of Measure -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start-units-of-measure">0</span> to <span id="showing-end-units-of-measure">0</span> of <span id="total-units-of-measure">0</span> units of measure
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page-units-of-measure" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers-units-of-measure" class="flex items-center">
                </div>
                <button id="next-page-units-of-measure" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
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

<!-- Delete Unit of Measure Modal -->
<div id="deleteUnitOfMeasureModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteUnitOfMeasureModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Unit of Measure</h3>
            <button onclick="hideDeleteUnitOfMeasureModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this unit of measure? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="delete-package-name"></div>
                    <div class="text-gray-500">SI Unit:</div>
                    <div class="font-medium text-gray-900" id="delete-si-unit"></div>
                    <div class="text-gray-500">Unit of Measure:</div>
                    <div class="font-medium text-gray-900" id="delete-unit-of-measure"></div>
                    <div class="text-gray-500">Status:</div>
                    <div class="font-medium text-gray-900" id="delete-status"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteUnitOfMeasureModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDeleteUnitOfMeasure" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Change Status Modal -->
<div id="changeStatusModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideChangeStatusModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Change Status</h3>
            <button onclick="hideChangeStatusModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Change the status of this unit of measure:</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="status-package-name"></div>
                    <div class="text-gray-500">SI Unit:</div>
                    <div class="font-medium text-gray-900" id="status-si-unit"></div>
                    <div class="text-gray-500">Unit of Measure:</div>
                    <div class="font-medium text-gray-900" id="status-unit-of-measure"></div>
                </div>
            </div>
            <div class="mt-4">
                <label for="status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Pending</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="status-toggle" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                    <span class="text-sm text-gray-900 font-medium">Approved</span>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideChangeStatusModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmChangeStatus" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Edit Package Name Modal -->
<div id="editPackageNameModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideEditPackageNameModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Edit Package Name</h3>
            <button onclick="hideEditPackageNameModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Update the package name for this unit of measure:</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Current Package Name:</div>
                    <div class="font-medium text-gray-900" id="current-package-name"></div>
                    <div class="text-gray-500">SI Unit:</div>
                    <div class="font-medium text-gray-900" id="edit-package-si-unit"></div>
                </div>
            </div>
            <div class="mt-4">
                <label for="edit-package-name-select" class="block text-sm font-medium text-gray-700 mb-1">New Package Name</label>
                <select id="edit-package-name-select" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="" disabled selected>Select Package Name</option>
                    <option value="create_new">Create New</option>
                </select>
                <div id="edit-new-package-name-container" class="mt-2 hidden">
                    <input type="text" id="edit-new-package-name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter new package name">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideEditPackageNameModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmEditPackageName" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Edit SI Unit Modal -->
<div id="editSIUnitModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideEditSIUnitModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Edit SI Unit</h3>
            <button onclick="hideEditSIUnitModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Update the SI unit for this unit of measure:</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="edit-si-package-name"></div>
                    <div class="text-gray-500">Current SI Unit:</div>
                    <div class="font-medium text-gray-900" id="current-si-unit"></div>
                </div>
            </div>
            <div class="mt-4">
                <label for="edit-si-unit-select" class="block text-sm font-medium text-gray-700 mb-1">New SI Unit</label>
                <select id="edit-si-unit-select" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="" disabled selected>Select SI Unit</option>
                    <option value="create_new">Create New</option>
                </select>
                <div id="edit-new-si-unit-container" class="mt-2 hidden">
                    <input type="text" id="edit-new-si-unit" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter new SI unit">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideEditSIUnitModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmEditSIUnit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
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

    // Units of Measure variables
    let unitsOfMeasureData = [];
    let packageNamesData = [];
    let siUnitsData = [];
    let currentUnitsOfMeasurePage = 1;
    let totalUnitsOfMeasurePages = 1;
    let itemsPerPage = 10;
    let filteredUnitsOfMeasure = [];

    document.addEventListener('DOMContentLoaded', function() {
        // Package Name and SI Unit select change handlers
        const packageNameSelect = document.getElementById('package_name_select');
        const siUnitSelect = document.getElementById('si_unit_select');
        const newPackageNameContainer = document.getElementById('new_package_name_container');
        const newSIUnitContainer = document.getElementById('new_si_unit_container');

        packageNameSelect.addEventListener('change', function() {
            if (this.value === 'create_new') {
                newPackageNameContainer.classList.remove('hidden');
            } else {
                newPackageNameContainer.classList.add('hidden');
            }
        });

        siUnitSelect.addEventListener('change', function() {
            if (this.value === 'create_new') {
                newSIUnitContainer.classList.remove('hidden');
            } else {
                newSIUnitContainer.classList.add('hidden');
            }
        });

        // Edit Package Name Modal
        const editPackageNameSelect = document.getElementById('edit-package-name-select');
        const editNewPackageNameContainer = document.getElementById('edit-new-package-name-container');

        editPackageNameSelect.addEventListener('change', function() {
            if (this.value === 'create_new') {
                editNewPackageNameContainer.classList.remove('hidden');
            } else {
                editNewPackageNameContainer.classList.add('hidden');
            }
        });

        // Edit SI Unit Modal
        const editSIUnitSelect = document.getElementById('edit-si-unit-select');
        const editNewSIUnitContainer = document.getElementById('edit-new-si-unit-container');

        editSIUnitSelect.addEventListener('change', function() {
            if (this.value === 'create_new') {
                editNewSIUnitContainer.classList.remove('hidden');
            } else {
                editNewSIUnitContainer.classList.add('hidden');
            }
        });

        // Unit of Measure Form
        const unitOfMeasureForm = document.getElementById('unitOfMeasureForm');
        const cancelUnitOfMeasureFormBtn = document.getElementById('cancelUnitOfMeasureForm');

        unitOfMeasureForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const packageNameSelectValue = packageNameSelect.value;
            const siUnitSelectValue = siUnitSelect.value;
            let packageNameId = packageNameSelectValue;
            let siUnitId = siUnitSelectValue;
            let newSIUnitName = null;

            // Create new package name if needed
            if (packageNameSelectValue === 'create_new' || packageNameSelectValue === '') {
                const newPackageName = document.getElementById('new_package_name').value.trim();
                if (!newPackageName) {
                    showErrorNotification('Please enter a package name');
                    return;
                }

                try {
                    const response = await createNewPackageName(newPackageName);
                    if (response.success) {
                        packageNameId = response.id;
                    } else {
                        showErrorNotification(response.message || 'Failed to create package name');
                        return;
                    }
                } catch (error) {
                    showErrorNotification('Failed to create package name');
                    return;
                }
            }

            // Handle SI unit
            if (siUnitSelectValue === 'create_new' || siUnitSelectValue === '') {
                newSIUnitName = document.getElementById('new_si_unit').value.trim();
                if (!newSIUnitName) {
                    showErrorNotification('Please enter an SI unit');
                    return;
                }

                // Generate a temporary UUID for the new SI unit
                siUnitId = generateUUID();
            }

            // Create unit of measure
            createUnitOfMeasure(packageNameId, siUnitId, newSIUnitName);
        });

        cancelUnitOfMeasureFormBtn.addEventListener('click', function() {
            resetUnitOfMeasureForm();
        });

        // Search and Filter
        document.getElementById('searchUnitsOfMeasure').addEventListener('input', function(e) {
            filterUnitsOfMeasure();
        });

        document.getElementById('filterPackageName').addEventListener('change', function() {
            filterUnitsOfMeasure();
        });

        document.getElementById('filterSIUnit').addEventListener('change', function() {
            filterUnitsOfMeasure();
        });

        document.getElementById('filterStatus').addEventListener('change', function() {
            filterUnitsOfMeasure();
        });

        // Pagination for Units of Measure
        document.getElementById('prev-page-units-of-measure').addEventListener('click', function() {
            if (currentUnitsOfMeasurePage > 1) {
                currentUnitsOfMeasurePage--;
                renderUnitsOfMeasurePagination();
                renderUnitsOfMeasure(filteredUnitsOfMeasure);
            }
        });

        document.getElementById('next-page-units-of-measure').addEventListener('click', function() {
            if (currentUnitsOfMeasurePage < totalUnitsOfMeasurePages) {
                currentUnitsOfMeasurePage++;
                renderUnitsOfMeasurePagination();
                renderUnitsOfMeasure(filteredUnitsOfMeasure);
            }
        });

        // Delete confirmation
        document.getElementById('confirmDeleteUnitOfMeasure').addEventListener('click', confirmDeleteUnitOfMeasure);

        // Change status confirmation
        document.getElementById('confirmChangeStatus').addEventListener('click', confirmChangeStatus);

        // Edit Package Name confirmation
        document.getElementById('confirmEditPackageName').addEventListener('click', confirmEditPackageName);

        // Edit SI Unit confirmation
        document.getElementById('confirmEditSIUnit').addEventListener('click', confirmEditSIUnit);

        // Load data
        loadPackageNames();
        loadSIUnits();
        loadUnitsOfMeasure();
    });

    // Package Names Functions
    function loadPackageNames() {
        showLoading('Loading package names...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=getPackageNames`)
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
                    populatePackageNameDropdowns(packageNamesData);
                } else {
                    showErrorNotification(data.message || 'Failed to load package names');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading package names:', error);
                    showErrorNotification('Failed to load package names. Please try again.');
                }
            });
    }

    function populatePackageNameDropdowns(packageNames) {
        const packageNameSelect = document.getElementById('package_name_select');
        const filterPackageName = document.getElementById('filterPackageName');
        const editPackageNameSelect = document.getElementById('edit-package-name-select');

        // Sort package names alphabetically
        packageNames.sort((a, b) => a.package_name.localeCompare(b.package_name));

        // Clear existing options except the first two (placeholder and create new)
        while (packageNameSelect.options.length > 2) {
            packageNameSelect.remove(2);
        }

        // Clear filter dropdown except the first option
        while (filterPackageName.options.length > 1) {
            filterPackageName.remove(1);
        }

        // Clear edit dropdown except the first two options
        while (editPackageNameSelect.options.length > 2) {
            editPackageNameSelect.remove(2);
        }

        // Add package names to dropdowns
        packageNames.forEach(pkg => {
            // Add to form select
            const option1 = document.createElement('option');
            option1.value = pkg.id;
            option1.textContent = pkg.package_name;
            packageNameSelect.appendChild(option1);

            // Add to filter select
            const option2 = document.createElement('option');
            option2.value = pkg.package_name;
            option2.textContent = pkg.package_name;
            filterPackageName.appendChild(option2);

            // Add to edit select
            const option3 = document.createElement('option');
            option3.value = pkg.id;
            option3.textContent = pkg.package_name;
            editPackageNameSelect.appendChild(option3);
        });
    }

    async function createNewPackageName(packageName) {
        showLoading('Creating package name...');

        try {
            const response = await fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createPackageName`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    package_name: packageName
                })
            });

            if (response.status === 401) {
                showSessionExpiredModal();
                throw new Error('Session expired');
            }

            const data = await response.json();
            hideLoading();

            if (data.success) {
                // Add to packageNamesData
                packageNamesData.push({
                    id: data.id,
                    package_name: packageName,
                    created_at: new Date().toISOString(),
                    updated_at: new Date().toISOString()
                });

                // Update dropdowns
                populatePackageNameDropdowns(packageNamesData);
            }

            return data;
        } catch (error) {
            hideLoading();
            if (error.message !== 'Session expired') {
                console.error('Error creating package name:', error);
            }
            throw error;
        }
    }

    // SI Units Functions
    function loadSIUnits() {
        showLoading('Loading SI units...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=getSIUnits`)
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
                    populateSIUnitDropdowns(siUnitsData);
                } else {
                    showErrorNotification(data.message || 'Failed to load SI units');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading SI units:', error);
                    showErrorNotification('Failed to load SI units. Please try again.');
                }
            });
    }

    function populateSIUnitDropdowns(siUnits) {
        const siUnitSelect = document.getElementById('si_unit_select');
        const filterSIUnit = document.getElementById('filterSIUnit');
        const editSIUnitSelect = document.getElementById('edit-si-unit-select');

        // Sort SI units alphabetically
        siUnits.sort((a, b) => a.si_unit.localeCompare(b.si_unit));

        // Clear existing options except the first two (placeholder and create new)
        while (siUnitSelect.options.length > 2) {
            siUnitSelect.remove(2);
        }

        // Clear filter dropdown except the first option
        while (filterSIUnit.options.length > 1) {
            filterSIUnit.remove(1);
        }

        // Clear edit dropdown except the first two options
        while (editSIUnitSelect.options.length > 2) {
            editSIUnitSelect.remove(2);
        }

        // Add SI units to dropdowns
        siUnits.forEach(unit => {
            // Add to form select
            const option1 = document.createElement('option');
            option1.value = unit.id;
            option1.textContent = unit.si_unit;
            siUnitSelect.appendChild(option1);

            // Add to filter select
            const option2 = document.createElement('option');
            option2.value = unit.si_unit;
            option2.textContent = unit.si_unit;
            filterSIUnit.appendChild(option2);

            // Add to edit select
            const option3 = document.createElement('option');
            option3.value = unit.id;
            option3.textContent = unit.si_unit;
            editSIUnitSelect.appendChild(option3);
        });
    }

    async function createNewSIUnit(siUnit) {
        showLoading('Creating SI unit...');

        try {
            const response = await fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createSIUnit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    si_unit: siUnit
                })
            });

            if (response.status === 401) {
                showSessionExpiredModal();
                throw new Error('Session expired');
            }

            const data = await response.json();
            hideLoading();

            if (data.success) {
                // Add to siUnitsData
                siUnitsData.push({
                    id: data.id,
                    si_unit: siUnit,
                    created_at: new Date().toISOString(),
                    updated_at: new Date().toISOString()
                });

                // Update dropdowns
                populateSIUnitDropdowns(siUnitsData);
            }

            return data;
        } catch (error) {
            hideLoading();
            if (error.message !== 'Session expired') {
                console.error('Error creating SI unit:', error);
            }
            throw error;
        }
    }

    // Units of Measure Functions
    function loadUnitsOfMeasure() {
        const tableBody = document.getElementById('units-of-measure-table-body');
        tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">Loading units of measure...</td></tr>';

        showLoading('Loading units of measure...');

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=getUnitsOfMeasure`)
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
                    unitsOfMeasureData = data.unitsOfMeasure;
                    filteredUnitsOfMeasure = [...unitsOfMeasureData];

                    totalUnitsOfMeasurePages = Math.ceil(filteredUnitsOfMeasure.length / itemsPerPage);
                    renderUnitsOfMeasurePagination();
                    renderUnitsOfMeasure(filteredUnitsOfMeasure);
                } else {
                    showErrorNotification(data.message || 'Failed to load units of measure');
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading units of measure</td></tr>';
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading units of measure:', error);
                    showErrorNotification('Failed to load units of measure. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Failed to load units of measure</td></tr>';
                }
            });
    }

    function filterUnitsOfMeasure() {
        const searchQuery = document.getElementById('searchUnitsOfMeasure').value.toLowerCase();
        const packageNameFilter = document.getElementById('filterPackageName').value.toLowerCase();
        const siUnitFilter = document.getElementById('filterSIUnit').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;

        filteredUnitsOfMeasure = unitsOfMeasureData.filter(unit => {
            const matchesSearch =
                unit.package_name.toLowerCase().includes(searchQuery) ||
                unit.si_unit.toLowerCase().includes(searchQuery) ||
                unit.unit_of_measure.toLowerCase().includes(searchQuery);

            const matchesPackageName = packageNameFilter === '' || unit.package_name.toLowerCase() === packageNameFilter;
            const matchesSIUnit = siUnitFilter === '' || unit.si_unit.toLowerCase() === siUnitFilter;
            const matchesStatus = statusFilter === '' || unit.status === statusFilter;

            return matchesSearch && matchesPackageName && matchesSIUnit && matchesStatus;
        });

        currentUnitsOfMeasurePage = 1;
        totalUnitsOfMeasurePages = Math.ceil(filteredUnitsOfMeasure.length / itemsPerPage);
        renderUnitsOfMeasurePagination();
        renderUnitsOfMeasure(filteredUnitsOfMeasure);
    }

    function renderUnitsOfMeasurePagination() {
        const paginationContainer = document.getElementById('pagination-numbers-units-of-measure');
        paginationContainer.innerHTML = '';

        const prevButton = document.getElementById('prev-page-units-of-measure');
        const nextButton = document.getElementById('next-page-units-of-measure');

        prevButton.disabled = currentUnitsOfMeasurePage === 1;
        nextButton.disabled = currentUnitsOfMeasurePage === totalUnitsOfMeasurePages || totalUnitsOfMeasurePages === 0;

        if (totalUnitsOfMeasurePages <= 5) {
            for (let i = 1; i <= totalUnitsOfMeasurePages; i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }
        } else {
            paginationContainer.appendChild(createPaginationButton(1));

            if (currentUnitsOfMeasurePage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentUnitsOfMeasurePage - 1); i <= Math.min(totalUnitsOfMeasurePages - 1, currentUnitsOfMeasurePage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }

            if (currentUnitsOfMeasurePage < totalUnitsOfMeasurePages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            if (totalUnitsOfMeasurePages > 1) {
                paginationContainer.appendChild(createPaginationButton(totalUnitsOfMeasurePages));
            }
        }
    }

    function createPaginationButton(pageNumber) {
        const button = document.createElement('button');
        const isActive = pageNumber === currentUnitsOfMeasurePage;

        button.className = isActive ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;

        button.addEventListener('click', function() {
            currentUnitsOfMeasurePage = pageNumber;
            renderUnitsOfMeasurePagination();
            renderUnitsOfMeasure(filteredUnitsOfMeasure);
        });

        return button;
    }

    function createUnitOfMeasure(packageNameId, siUnitId, siUnitName = null) {
        showLoading('Creating unit of measure...');

        const payload = {
            package_name_id: packageNameId,
            si_unit_id: siUnitId,
            status: 'Approved'
        };

        // Add SI unit name if provided (for new SI units)
        if (siUnitName) {
            payload.si_unit_name = siUnitName;
        }

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createUnitOfMeasure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
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
                    showSuccessNotification(data.message || 'Unit of measure created successfully!');
                    resetUnitOfMeasureForm();
                    loadUnitsOfMeasure();
                } else {
                    showErrorNotification(data.message || 'Failed to create unit of measure');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error creating unit of measure:', error);
                    showErrorNotification('Failed to create unit of measure. Please try again.');
                }
            });
    }

    // Generate UUID for new SI units
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function renderUnitsOfMeasure(unitsOfMeasure) {
        const tableBody = document.getElementById('units-of-measure-table-body');
        tableBody.innerHTML = '';

        document.getElementById('unit-of-measure-count').textContent = unitsOfMeasure.length;

        const start = (currentUnitsOfMeasurePage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, unitsOfMeasure.length);

        document.getElementById('showing-start-units-of-measure').textContent = unitsOfMeasure.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end-units-of-measure').textContent = end;
        document.getElementById('total-units-of-measure').textContent = unitsOfMeasure.length;

        const paginatedUnitsOfMeasure = unitsOfMeasure.slice(start, end);

        if (paginatedUnitsOfMeasure.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">No units of measure found</td></tr>';
            return;
        }

        paginatedUnitsOfMeasure.forEach((unit, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const statusClass = unit.status === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800';

            // Format unit of measure with SI unit first
            const formattedUnitOfMeasure = `${unit.si_unit} ${unit.package_name}`;

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(unit.si_unit)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${escapeHtml(unit.package_name)}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${escapeHtml(formattedUnitOfMeasure)}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">
                        ${escapeHtml(unit.status)}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit-package-name text-blue-600 hover:text-blue-800" data-id="${unit.id}" title="Edit Package Name">
                            <i class="fas fa-box"></i>
                        </button>
                        <button class="btn-edit-si-unit text-green-600 hover:text-green-800" data-id="${unit.id}" title="Edit SI Unit">
                            <i class="fas fa-ruler"></i>
                        </button>
                        <button class="btn-change-status text-blue-600 hover:text-blue-800" data-id="${unit.id}" title="Change Status">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button class="btn-delete-unit-of-measure text-red-600 hover:text-red-800" data-id="${unit.id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit-package-name').forEach(button => {
            button.addEventListener('click', function() {
                const unitOfMeasureId = this.getAttribute('data-id');
                showEditPackageNameModal(unitOfMeasureId);
            });
        });

        document.querySelectorAll('.btn-edit-si-unit').forEach(button => {
            button.addEventListener('click', function() {
                const unitOfMeasureId = this.getAttribute('data-id');
                showEditSIUnitModal(unitOfMeasureId);
            });
        });

        document.querySelectorAll('.btn-change-status').forEach(button => {
            button.addEventListener('click', function() {
                const unitOfMeasureId = this.getAttribute('data-id');
                showChangeStatusModal(unitOfMeasureId);
            });
        });

        document.querySelectorAll('.btn-delete-unit-of-measure').forEach(button => {
            button.addEventListener('click', function() {
                const unitOfMeasureId = this.getAttribute('data-id');
                showDeleteUnitOfMeasureModal(unitOfMeasureId);
            });
        });
    }

    function confirmChangeStatus() {
        const unitOfMeasureId = document.getElementById('confirmChangeStatus').getAttribute('data-id');
        const status = document.getElementById('status-toggle').checked ? 'Approved' : 'Pending';

        showLoading('Updating status...');
        hideChangeStatusModal();

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=updateUnitOfMeasure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: unitOfMeasureId,
                    status: status
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
                    showSuccessNotification(data.message || 'Status updated successfully!');
                    loadUnitsOfMeasure();
                } else {
                    showErrorNotification(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error updating status:', error);
                    showErrorNotification('Failed to update status. Please try again.');
                }
            });
    }

    function showChangeStatusModal(unitOfMeasureId) {
        const unit = unitsOfMeasureData.find(u => u.id === unitOfMeasureId);

        if (unit) {
            document.getElementById('status-package-name').textContent = unit.package_name;
            document.getElementById('status-si-unit').textContent = unit.si_unit;
            document.getElementById('status-unit-of-measure').textContent = `${unit.si_unit} ${unit.package_name}`;
            document.getElementById('status-toggle').checked = unit.status === 'Approved';
            document.getElementById('confirmChangeStatus').setAttribute('data-id', unitOfMeasureId);

            document.getElementById('changeStatusModal').classList.remove('hidden');
        }
    }

    function showEditPackageNameModal(unitOfMeasureId) {
        const unit = unitsOfMeasureData.find(u => u.id === unitOfMeasureId);

        if (unit) {
            document.getElementById('current-package-name').textContent = unit.package_name;
            document.getElementById('edit-package-si-unit').textContent = unit.si_unit;
            document.getElementById('confirmEditPackageName').setAttribute('data-id', unitOfMeasureId);
            document.getElementById('confirmEditPackageName').setAttribute('data-si-unit-id', unit.si_unit_id);
            document.getElementById('confirmEditPackageName').setAttribute('data-package-name-id', unit.package_name_id);

            // Reset the select to default
            document.getElementById('edit-package-name-select').value = '';
            document.getElementById('edit-new-package-name-container').classList.add('hidden');
            document.getElementById('edit-new-package-name').value = '';

            document.getElementById('editPackageNameModal').classList.remove('hidden');
        }
    }

    function showEditSIUnitModal(unitOfMeasureId) {
        const unit = unitsOfMeasureData.find(u => u.id === unitOfMeasureId);

        if (unit) {
            document.getElementById('edit-si-package-name').textContent = unit.package_name;
            document.getElementById('current-si-unit').textContent = unit.si_unit;
            document.getElementById('confirmEditSIUnit').setAttribute('data-id', unitOfMeasureId);
            document.getElementById('confirmEditSIUnit').setAttribute('data-si-unit-id', unit.si_unit_id);
            document.getElementById('confirmEditSIUnit').setAttribute('data-package-name-id', unit.package_name_id);

            // Reset the select to default
            document.getElementById('edit-si-unit-select').value = '';
            document.getElementById('edit-new-si-unit-container').classList.add('hidden');
            document.getElementById('edit-new-si-unit').value = '';

            document.getElementById('editSIUnitModal').classList.remove('hidden');
        }
    }

    function showDeleteUnitOfMeasureModal(unitOfMeasureId) {
        const unit = unitsOfMeasureData.find(u => u.id === unitOfMeasureId);

        if (unit) {
            document.getElementById('delete-package-name').textContent = unit.package_name;
            document.getElementById('delete-si-unit').textContent = unit.si_unit;
            document.getElementById('delete-unit-of-measure').textContent = `${unit.si_unit} ${unit.package_name}`;
            document.getElementById('delete-status').textContent = unit.status;
            document.getElementById('confirmDeleteUnitOfMeasure').setAttribute('data-id', unitOfMeasureId);

            document.getElementById('deleteUnitOfMeasureModal').classList.remove('hidden');
        }
    }

    function hideChangeStatusModal() {
        document.getElementById('changeStatusModal').classList.add('hidden');
    }

    function hideEditPackageNameModal() {
        document.getElementById('editPackageNameModal').classList.add('hidden');
    }

    function hideEditSIUnitModal() {
        document.getElementById('editSIUnitModal').classList.add('hidden');
    }

    function hideDeleteUnitOfMeasureModal() {
        document.getElementById('deleteUnitOfMeasureModal').classList.add('hidden');
    }

    async function confirmEditPackageName() {
        const unitOfMeasureId = document.getElementById('confirmEditPackageName').getAttribute('data-id');
        const packageNameSelect = document.getElementById('edit-package-name-select');
        let packageNameId = packageNameSelect.value;

        if (!packageNameId && packageNameSelect.value !== 'create_new') {
            showErrorNotification('Please select a package name');
            return;
        }

        // Create new package name if needed
        if (packageNameSelect.value === 'create_new') {
            const newPackageName = document.getElementById('edit-new-package-name').value.trim();
            if (!newPackageName) {
                showErrorNotification('Please enter a package name');
                return;
            }

            try {
                const response = await createNewPackageName(newPackageName);
                if (response.success) {
                    packageNameId = response.id;
                } else {
                    showErrorNotification(response.message || 'Failed to create package name');
                    return;
                }
            } catch (error) {
                showErrorNotification('Failed to create package name');
                return;
            }
        }

        showLoading('Updating package name...');
        hideEditPackageNameModal();

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=updateUnitOfMeasure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: unitOfMeasureId,
                    package_name_id: packageNameId
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
                    loadUnitsOfMeasure();
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

    async function confirmEditSIUnit() {
        const unitOfMeasureId = document.getElementById('confirmEditSIUnit').getAttribute('data-id');
        const siUnitSelect = document.getElementById('edit-si-unit-select');
        let siUnitId = siUnitSelect.value;
        let siUnitName = null;

        if (!siUnitId && siUnitSelect.value !== 'create_new') {
            showErrorNotification('Please select an SI unit');
            return;
        }

        // Create new SI unit if needed
        if (siUnitSelect.value === 'create_new') {
            siUnitName = document.getElementById('edit-new-si-unit').value.trim();
            if (!siUnitName) {
                showErrorNotification('Please enter an SI unit');
                return;
            }
        }

        showLoading('Updating SI unit...');
        hideEditSIUnitModal();

        const payload = {
            id: unitOfMeasureId,
            si_unit_id: siUnitId
        };

        if (siUnitName) {
            payload.si_unit_name = siUnitName;
        }

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=updateUnitOfMeasure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
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
                    loadUnitsOfMeasure();
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

    function confirmDeleteUnitOfMeasure() {
        const unitOfMeasureId = document.getElementById('confirmDeleteUnitOfMeasure').getAttribute('data-id');

        showLoading('Deleting unit of measure...');
        hideDeleteUnitOfMeasureModal();

        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=deleteUnitOfMeasure`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: unitOfMeasureId
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
                    showSuccessNotification(data.message || 'Unit of measure deleted successfully!');
                    loadUnitsOfMeasure();
                } else {
                    showErrorNotification(data.message || 'Failed to delete unit of measure');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error deleting unit of measure:', error);
                    showErrorNotification('Failed to delete unit of measure. Please try again.');
                }
            });
    }

    function resetUnitOfMeasureForm() {
        document.getElementById('unitOfMeasureForm').reset();
        document.getElementById('unitOfMeasureId').value = '';
        document.getElementById('new_package_name_container').classList.add('hidden');
        document.getElementById('new_si_unit_container').classList.add('hidden');
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