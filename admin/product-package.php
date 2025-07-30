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
            <p class="text-sm text-gray-text mt-1">Define and manage product packaging and SI units</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <a href="products"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px">
            <li class="mr-2">
                <button id="tab-package-names"
                    class="inline-block p-4 border-b-2 border-primary text-primary rounded-t-lg active"
                    aria-current="page">
                    Package Names
                </button>
            </li>
            <li class="mr-2">
                <button id="tab-si-units"
                    class="inline-block p-4 border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300 rounded-t-lg">
                    SI Units
                </button>
            </li>
        </ul>
    </div>

    <!-- Package Names Tab Content -->
    <div id="content-package-names" class="space-y-6">
        <!-- Package Names List Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Package Names</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="package-name-count">0</span> package names found
                    </p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <button id="openPackageNameModal"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Add New</span>
                    </button>
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchPackageNames" placeholder="Search package names..."
                            class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="package-names-table">
                    <thead class="bg-gray-50">
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Package Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text hidden md:table-cell">Created At
                            </th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Updated At</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="package-names-table-body">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">Loading package names...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Package Names -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start-package-names">0</span> to <span
                        id="showing-end-package-names">0</span> of <span id="total-package-names">0</span> package names
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page-package-names"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers-package-names" class="flex items-center">
                    </div>
                    <button id="next-page-package-names"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SI Units Tab Content -->
    <div id="content-si-units" class="space-y-6 hidden">
        <!-- SI Units List Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">SI Units</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="si-unit-count">0</span> SI units found
                    </p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <button id="openSIUnitModal"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Add New</span>
                    </button>
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchSIUnits" placeholder="Search SI units..."
                            class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="si-units-table">
                    <thead class="bg-gray-50">
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">SI Unit</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text hidden md:table-cell">Created At
                            </th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Updated At</th>
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
                    Showing <span id="showing-start-si-units">0</span> to <span id="showing-end-si-units">0</span> of
                    <span id="total-si-units">0</span> SI units
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page-si-units"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers-si-units" class="flex items-center">
                    </div>
                    <button id="next-page-si-units"
                        class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Package Name Modal -->
<div id="packageNameModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hidePackageNameModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="packageNameModalTitle">Add New Package Name</h3>
            <button onclick="hidePackageNameModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="packageNameModalForm" class="space-y-4" autocomplete="off">
                <input type="hidden" id="packageNameModalId" name="packageNameId" value="">
                <div>
                    <label for="package_name_modal" class="block text-sm font-medium text-gray-700 mb-1">Package
                        Name</label>
                    <input type="text" id="package_name_modal" name="package_name"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter package name">
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="hidePackageNameModal()"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2">
                        Cancel
                    </button>
                    <button type="submit" id="submitPackageNameModalButton"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Save Package Name
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SI Unit Modal -->
<div id="siUnitModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideSIUnitModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="siUnitModalTitle">Add New SI Unit</h3>
            <button onclick="hideSIUnitModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="siUnitModalForm" class="space-y-4" autocomplete="off">
                <input type="hidden" id="siUnitModalId" name="siUnitId" value="">
                <div>
                    <label for="si_unit_modal" class="block text-sm font-medium text-gray-700 mb-1">SI Unit</label>
                    <input type="text" id="si_unit_modal" name="si_unit"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter SI unit">
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="hideSIUnitModal()"
                        class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2">
                        Cancel
                    </button>
                    <button type="submit" id="submitSIUnitModalButton"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Save SI Unit
                    </button>
                </div>
            </form>
        </div>
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
            <p class="text-gray-600 mb-4">Are you sure you want to delete this package name? This action cannot be
                undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Package Name:</div>
                    <div class="font-medium text-gray-900" id="delete-package-name"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeletePackageNameModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
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
            <p class="text-gray-600 mb-4">Are you sure you want to delete this SI unit? This action cannot be undone.
            </p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">SI Unit:</div>
                    <div class="font-medium text-gray-900" id="delete-si-unit"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteSIUnitModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
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
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Login Now
                </button>
            </div>
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

<script>
    // Package Names variables
    let packageNamesData = [];
    let currentPackageNamesPage = 1;
    let totalPackageNamesPages = 1;
    let itemsPerPage = 10;
    let filteredPackageNames = [];

    // SI Units variables
    let siUnitsData = [];
    let currentSIUnitsPage = 1;
    let totalSIUnitsPages = 1;
    let filteredSIUnits = [];

    document.addEventListener('DOMContentLoaded', function () {
        /* Tab switching */
        const tabPackageNames = document.getElementById('tab-package-names');
        const tabSIUnits = document.getElementById('tab-si-units');
        const contentPackageNames = document.getElementById('content-package-names');
        const contentSIUnits = document.getElementById('content-si-units');

        tabPackageNames.addEventListener('click', function () {
            tabPackageNames.classList.add('border-primary', 'text-primary');
            tabPackageNames.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            tabSIUnits.classList.remove('border-primary', 'text-primary');
            tabSIUnits.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            contentPackageNames.classList.remove('hidden');
            contentSIUnits.classList.add('hidden');
        });

        tabSIUnits.addEventListener('click', function () {
            tabSIUnits.classList.add('border-primary', 'text-primary');
            tabSIUnits.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            tabPackageNames.classList.remove('border-primary', 'text-primary');
            tabPackageNames.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
            contentSIUnits.classList.remove('hidden');
            contentPackageNames.classList.add('hidden');
        });

        /* Open Modals */
        document.getElementById('openPackageNameModal').addEventListener('click', function () {
            resetPackageNameModalForm();
            showPackageNameModal();
        });
        document.getElementById('openSIUnitModal').addEventListener('click', function () {
            resetSIUnitModalForm();
            showSIUnitModal();
        });

        /* Package Name Modal Form Submission */
        document.getElementById('packageNameModalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('packageNameModalId').value;
            const name = document.getElementById('package_name_modal').value.trim();
            if (!name) {
                showErrorNotification('Please enter a package name');
                return;
            }
            if (id) {
                updatePackageName(id, name);
            } else {
                createPackageName(name);
            }
            hidePackageNameModal();
        });

        /* SI Unit Modal Form Submission */
        document.getElementById('siUnitModalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('siUnitModalId').value;
            const unit = document.getElementById('si_unit_modal').value.trim();
            if (!unit) {
                showErrorNotification('Please enter an SI unit');
                return;
            }
            if (id) {
                updateSIUnit(id, unit);
            } else {
                createSIUnit(unit);
            }
            hideSIUnitModal();
        });

        /* Search Package Names */
        document.getElementById('searchPackageNames').addEventListener('input', function () {
            filterPackageNames();
        });

        /* Search SI Units */
        document.getElementById('searchSIUnits').addEventListener('input', function () {
            filterSIUnits();
        });

        /* Pagination for Package Names */
        document.getElementById('prev-page-package-names').addEventListener('click', function () {
            if (currentPackageNamesPage > 1) {
                currentPackageNamesPage--;
                renderPackageNamesPagination();
                renderPackageNames(filteredPackageNames);
            }
        });
        document.getElementById('next-page-package-names').addEventListener('click', function () {
            if (currentPackageNamesPage < totalPackageNamesPages) {
                currentPackageNamesPage++;
                renderPackageNamesPagination();
                renderPackageNames(filteredPackageNames);
            }
        });

        /* Pagination for SI Units */
        document.getElementById('prev-page-si-units').addEventListener('click', function () {
            if (currentSIUnitsPage > 1) {
                currentSIUnitsPage--;
                renderSIUnitsPagination();
                renderSIUnits(filteredSIUnits);
            }
        });
        document.getElementById('next-page-si-units').addEventListener('click', function () {
            if (currentSIUnitsPage < totalSIUnitsPages) {
                currentSIUnitsPage++;
                renderSIUnitsPagination();
                renderSIUnits(filteredSIUnits);
            }
        });

        /* Delete confirmations */
        document.getElementById('confirmDeletePackageName').addEventListener('click', confirmDeletePackageName);
        document.getElementById('confirmDeleteSIUnit').addEventListener('click', confirmDeleteSIUnit);

        /* Load initial data */
        loadPackageNames();
        loadSIUnits();
    });

    /* ===== Package Names Functions ===== */
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
                    packageNamesData.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                    filteredPackageNames = [...packageNamesData];
                    totalPackageNamesPages = Math.ceil(filteredPackageNames.length / itemsPerPage);
                    renderPackageNamesPagination();
                    renderPackageNames(filteredPackageNames);
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

    function filterPackageNames() {
        const searchQuery = document.getElementById('searchPackageNames').value.toLowerCase();
        filteredPackageNames = packageNamesData.filter(pkg =>
            pkg.package_name.toLowerCase().includes(searchQuery)
        );
        currentPackageNamesPage = 1;
        totalPackageNamesPages = Math.ceil(filteredPackageNames.length / itemsPerPage);
        renderPackageNamesPagination();
        renderPackageNames(filteredPackageNames);
    }

    function renderPackageNamesPagination() {
        const container = document.getElementById('pagination-numbers-package-names');
        container.innerHTML = '';
        const prevButton = document.getElementById('prev-page-package-names');
        const nextButton = document.getElementById('next-page-package-names');
        prevButton.disabled = currentPackageNamesPage === 1;
        nextButton.disabled = currentPackageNamesPage === totalPackageNamesPages || totalPackageNamesPages === 0;

        if (totalPackageNamesPages <= 5) {
            for (let i = 1; i <= totalPackageNamesPages; i++) {
                container.appendChild(createPaginationButton(i, 'package-names'));
            }
        } else {
            container.appendChild(createPaginationButton(1, 'package-names'));

            if (currentPackageNamesPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentPackageNamesPage - 1); i <= Math.min(totalPackageNamesPages - 1, currentPackageNamesPage + 1); i++) {
                container.appendChild(createPaginationButton(i, 'package-names'));
            }

            if (currentPackageNamesPage < totalPackageNamesPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }

            if (totalPackageNamesPages > 1) {
                container.appendChild(createPaginationButton(totalPackageNamesPages, 'package-names'));
            }
        }
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
        const paginated = packageNames.slice(start, end);

        if (paginated.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">No package names found</td></tr>';
            return;
        }

        paginated.forEach((pkg, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const createdDate = new Date(pkg.created_at);
            const updatedDate = new Date(pkg.updated_at);
            const createdAt = createdDate.toLocaleDateString() + ' ' +
                createdDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            const updatedAt = updatedDate.toLocaleDateString() + ' ' +
                updatedDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(pkg.package_name)}</td>
                <td class="px-6 py-4 text-sm text-gray-text hidden md:table-cell">${createdAt}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${updatedAt}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit-package-name text-blue-600 hover:text-blue-800" data-id="${pkg.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete-package-name text-red-600 hover:text-red-800" data-id="${pkg.id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit-package-name').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                editPackageName(id);
                showPackageNameModal();
            });
        });

        document.querySelectorAll('.btn-delete-package-name').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                showDeletePackageNameModal(id);
            });
        });
    }

    function createPackageName(name) {
        showLoading('Creating package name...');
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createPackageName`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ package_name: name })
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

    function updatePackageName(id, name) {
        showLoading('Updating package name...');
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=updatePackageName`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, package_name: name })
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

    function editPackageName(id) {
        const pkg = packageNamesData.find(p => p.id === id);
        if (pkg) {
            document.getElementById('packageNameModalTitle').textContent = 'Edit Package Name';
            document.getElementById('packageNameModalId').value = id;
            document.getElementById('package_name_modal').value = pkg.package_name;
            document.getElementById('submitPackageNameModalButton').textContent = 'Update Package Name';
        }
    }

    function showDeletePackageNameModal(id) {
        const pkg = packageNamesData.find(p => p.id === id);
        if (pkg) {
            document.getElementById('delete-package-name').textContent = pkg.package_name;
            document.getElementById('confirmDeletePackageName').setAttribute('data-id', id);
            document.getElementById('deletePackageNameModal').classList.remove('hidden');
        }
    }

    function hideDeletePackageNameModal() {
        document.getElementById('deletePackageNameModal').classList.add('hidden');
    }

    function confirmDeletePackageName() {
        const id = document.getElementById('confirmDeletePackageName').getAttribute('data-id');
        showLoading('Deleting package name...');
        hideDeletePackageNameModal();
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=deletePackageName`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
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

    function resetPackageNameModalForm() {
        document.getElementById('packageNameModalForm').reset();
        document.getElementById('packageNameModalId').value = '';
        document.getElementById('packageNameModalTitle').textContent = 'Add New Package Name';
        document.getElementById('submitPackageNameModalButton').textContent = 'Save Package Name';
    }

    function showPackageNameModal() {
        document.getElementById('packageNameModal').classList.remove('hidden');
    }

    function hidePackageNameModal() {
        document.getElementById('packageNameModal').classList.add('hidden');
    }

    /* ===== SI Units Functions ===== */
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
                    siUnitsData.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
                    filteredSIUnits = [...siUnitsData];
                    totalSIUnitsPages = Math.ceil(filteredSIUnits.length / itemsPerPage);
                    renderSIUnitsPagination();
                    renderSIUnits(filteredSIUnits);
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

    function filterSIUnits() {
        const query = document.getElementById('searchSIUnits').value.toLowerCase();
        filteredSIUnits = siUnitsData.filter(unit =>
            unit.si_unit.toLowerCase().includes(query)
        );
        currentSIUnitsPage = 1;
        totalSIUnitsPages = Math.ceil(filteredSIUnits.length / itemsPerPage);
        renderSIUnitsPagination();
        renderSIUnits(filteredSIUnits);
    }

    function renderSIUnitsPagination() {
        const container = document.getElementById('pagination-numbers-si-units');
        container.innerHTML = '';
        const prevButton = document.getElementById('prev-page-si-units');
        const nextButton = document.getElementById('next-page-si-units');
        prevButton.disabled = currentSIUnitsPage === 1;
        nextButton.disabled = currentSIUnitsPage === totalSIUnitsPages || totalSIUnitsPages === 0;

        if (totalSIUnitsPages <= 5) {
            for (let i = 1; i <= totalSIUnitsPages; i++) {
                container.appendChild(createPaginationButton(i, 'si-units'));
            }
        } else {
            container.appendChild(createPaginationButton(1, 'si-units'));

            if (currentSIUnitsPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentSIUnitsPage - 1); i <= Math.min(totalSIUnitsPages - 1, currentSIUnitsPage + 1); i++) {
                container.appendChild(createPaginationButton(i, 'si-units'));
            }

            if (currentSIUnitsPage < totalSIUnitsPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                container.appendChild(ellipsis);
            }

            if (totalSIUnitsPages > 1) {
                container.appendChild(createPaginationButton(totalSIUnitsPages, 'si-units'));
            }
        }
    }

    function renderSIUnits(units) {
        const tableBody = document.getElementById('si-units-table-body');
        tableBody.innerHTML = '';
        document.getElementById('si-unit-count').textContent = units.length;
        const start = (currentSIUnitsPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, units.length);
        document.getElementById('showing-start-si-units').textContent = units.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end-si-units').textContent = end;
        document.getElementById('total-si-units').textContent = units.length;
        const paginated = units.slice(start, end);

        if (paginated.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center">No SI units found</td></tr>';
            return;
        }

        paginated.forEach((unit, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';
            const createdDate = new Date(unit.created_at);
            const updatedDate = new Date(unit.updated_at);
            const createdAt = createdDate.toLocaleDateString() + ' ' +
                createdDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
            const updatedAt = updatedDate.toLocaleDateString() + ' ' +
                updatedDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(unit.si_unit)}</td>
                <td class="px-6 py-4 text-sm text-gray-text hidden md:table-cell">${createdAt}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${updatedAt}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit-si-unit text-blue-600 hover:text-blue-800" data-id="${unit.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete-si-unit text-red-600 hover:text-red-800" data-id="${unit.id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-edit-si-unit').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                editSIUnit(id);
                showSIUnitModal();
            });
        });

        document.querySelectorAll('.btn-delete-si-unit').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                showDeleteSIUnitModal(id);
            });
        });
    }

    function createSIUnit(unit) {
        showLoading('Creating SI unit...');
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=createSIUnit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ si_unit: unit })
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

    function updateSIUnit(id, unit) {
        showLoading('Updating SI unit...');
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=updateSIUnit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, si_unit: unit })
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

    function editSIUnit(id) {
        const unit = siUnitsData.find(u => u.id === id);
        if (unit) {
            document.getElementById('siUnitModalTitle').textContent = 'Edit SI Unit';
            document.getElementById('siUnitModalId').value = id;
            document.getElementById('si_unit_modal').value = unit.si_unit;
            document.getElementById('submitSIUnitModalButton').textContent = 'Update SI Unit';
        }
    }

    function showDeleteSIUnitModal(id) {
        const unit = siUnitsData.find(u => u.id === id);
        if (unit) {
            document.getElementById('delete-si-unit').textContent = unit.si_unit;
            document.getElementById('confirmDeleteSIUnit').setAttribute('data-id', id);
            document.getElementById('deleteSIUnitModal').classList.remove('hidden');
        }
    }

    function hideDeleteSIUnitModal() {
        document.getElementById('deleteSIUnitModal').classList.add('hidden');
    }

    function confirmDeleteSIUnit() {
        const id = document.getElementById('confirmDeleteSIUnit').getAttribute('data-id');
        showLoading('Deleting SI unit...');
        hideDeleteSIUnitModal();
        fetch(`${BASE_URL}admin/fetch/manageProductPackages.php?action=deleteSIUnit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
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

    function resetSIUnitModalForm() {
        document.getElementById('siUnitModalForm').reset();
        document.getElementById('siUnitModalId').value = '';
        document.getElementById('siUnitModalTitle').textContent = 'Add New SI Unit';
        document.getElementById('submitSIUnitModalButton').textContent = 'Save SI Unit';
    }

    function showSIUnitModal() {
        document.getElementById('siUnitModal').classList.remove('hidden');
    }

    function hideSIUnitModal() {
        document.getElementById('siUnitModal').classList.add('hidden');
    }

    /* ===== Utility & UI Functions ===== */
    function createPaginationButton(pageNumber, type) {
        const button = document.createElement('button');
        const isActive = pageNumber === (type === 'package-names' ? currentPackageNamesPage : currentSIUnitsPage);
        button.className = isActive ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;
        button.addEventListener('click', function () {
            if (type === 'package-names') {
                currentPackageNamesPage = pageNumber;
                renderPackageNamesPagination();
                renderPackageNames(filteredPackageNames);
            } else {
                currentSIUnitsPage = pageNumber;
                renderSIUnitsPagination();
                renderSIUnits(filteredSIUnits);
            }
        });
        return button;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showLoading(message = 'Loading...') {
        let overlay = document.getElementById('loadingOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loadingOverlay';
            overlay.className = 'fixed inset-0 bg-black/30 flex items-center justify-center z-[999]';
            overlay.innerHTML = `
                <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    <span id="loadingMessage" class="text-gray-700 font-medium">${message}</span>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        document.getElementById('loadingMessage').textContent = message;
        overlay.classList.remove('hidden');
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.add('hidden');
        }
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