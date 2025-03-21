<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Package Definition';
$activeNav = 'package-definition';
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
            <h2 class="text-lg font-semibold text-primary">Add New Package Definition</h2>
            <p class="text-sm text-gray-text mt-1">Create a new package type with unit of measure</p>
        </div>

        <div class="p-6">
            <form id="packageForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="package_name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <div id="packageNameContainer">
                            <select id="package_name" name="package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="" selected>Select Package</option>
                                <option value="1337">Section</option>
                                <option value="1538">Set</option>
                                <option value="2053">Pole</option>
                                <option value="2056">SinoTruck</option>
                                <option value="2104">Frame</option>
                                <option value="2762">Standard measure</option>
                                <option value="2899">Linear measurement</option>
                                <option value="3112">Forward Truck</option>
                                <option value="3620">Piece</option>
                                <option value="4624">Elf Truck</option>
                                <option value="5174">Can</option>
                                <option value="5330">Unit</option>
                                <option value="6160">Trip</option>
                                <option value="6521">Unit Lease</option>
                                <option value="6650">Truck</option>
                                <option value="7769">Roll</option>
                                <option value="7836">Rebar</option>
                                <option value="8506">Bundle</option>
                                <option value="8623">Bag</option>
                                <option value="8763">Packet</option>
                                <option value="8816">Carton</option>
                                <option value="8890">Carton</option>
                                <option value="8907">Kilogram</option>
                                <option value="9052">Sheet</option>
                                <option value="9981">Magulu kumi</option>
                                <option value="add">Add New</option>
                            </select>
                            <input type="text" id="new_package_name" name="new_package_name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary hidden" placeholder="Enter new package name">
                        </div>
                    </div>

                    <div>
                        <label for="unit_of_measure" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure</label>
                        <input type="text" id="unit_of_measure" name="sUnit[]" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="e.g., kg, liter, meter">
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" id="cancelAddNew" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors mr-2 hidden">
                        Cancel
                    </button>
                    <button type="submit" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
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
                    <span id="package-count">83</span> definitions found
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
                        <option value="Bag">Bag</option>
                        <option value="Bundle">Bundle</option>
                        <option value="Can">Can</option>
                        <option value="Carton">Carton</option>
                        <option value="Elf Truck">Elf Truck</option>
                        <option value="Forward Truck">Forward Truck</option>
                        <option value="Frame">Frame</option>
                        <option value="Kilogram">Kilogram</option>
                        <option value="Linear measurement">Linear measurement</option>
                        <option value="Magulu kumi">Magulu kumi</option>
                        <option value="Packet">Packet</option>
                        <option value="Piece">Piece</option>
                        <option value="Pole">Pole</option>
                        <option value="Rebar">Rebar</option>
                        <option value="Roll">Roll</option>
                        <option value="Section">Section</option>
                        <option value="Set">Set</option>
                        <option value="Sheet">Sheet</option>
                        <option value="SinoTruck">SinoTruck</option>
                        <option value="Standard measure">Standard measure</option>
                        <option value="Trip">Trip</option>
                        <option value="Truck">Truck</option>
                        <option value="Unit">Unit</option>
                        <option value="Unit Lease">Unit Lease</option>
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
                <tbody>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Bag</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Inch</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="47350" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="47350" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">2</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Bag</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Kg</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="20967" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="20967" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">3</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Bundle</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Kg</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="54511" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="54511" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">4</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Bundle</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Ft</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="82442" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="82442" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">5</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Can</td>
                        <td class="px-6 py-4 text-sm text-gray-text">ml</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="34158" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="34158" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">6</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Can</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Ltr</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="37502" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="37502" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">7</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Can</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Kg</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="26571" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="26571" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">8</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Carton</td>
                        <td class="px-6 py-4 text-sm text-gray-text">sm</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="69530" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="69530" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">9</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Carton</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Pcs</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="88925" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="88925" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">10</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Elf Truck</td>
                        <td class="px-6 py-4 text-sm text-gray-text">Ton</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="33592" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="33592" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-packages">83</span> package definitions
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                    <span class="px-2">...</span>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">9</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
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
                            <option value="Bag">Bag</option>
                            <option value="Bundle">Bundle</option>
                            <option value="Can">Can</option>
                            <option value="Carton">Carton</option>
                            <option value="Elf Truck">Elf Truck</option>
                            <option value="Forward Truck">Forward Truck</option>
                            <option value="Frame">Frame</option>
                            <option value="Kilogram">Kilogram</option>
                            <option value="Linear measurement">Linear measurement</option>
                            <option value="Magulu kumi">Magulu kumi</option>
                            <option value="Packet">Packet</option>
                            <option value="Piece">Piece</option>
                            <option value="Pole">Pole</option>
                            <option value="Rebar">Rebar</option>
                            <option value="Roll">Roll</option>
                            <option value="Section">Section</option>
                            <option value="Set">Set</option>
                            <option value="Sheet">Sheet</option>
                            <option value="SinoTruck">SinoTruck</option>
                            <option value="Standard measure">Standard measure</option>
                            <option value="Trip">Trip</option>
                            <option value="Truck">Truck</option>
                            <option value="Unit">Unit</option>
                            <option value="Unit Lease">Unit Lease</option>
                            <option value="add">Add New</option>
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
            <button id="confirmDelete" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
    // Sample package data - in a real application, this would come from an API
    const packageDefinitions = [{
            id: "47350",
            package: "Bag",
            unitOfMeasure: "Inch"
        },
        {
            id: "20967",
            package: "Bag",
            unitOfMeasure: "Kg"
        },
        {
            id: "54511",
            package: "Bundle",
            unitOfMeasure: "Kg"
        },
        {
            id: "82442",
            package: "Bundle",
            unitOfMeasure: "Ft"
        },
        {
            id: "34158",
            package: "Can",
            unitOfMeasure: "ml"
        },
        {
            id: "37502",
            package: "Can",
            unitOfMeasure: "Ltr"
        },
        {
            id: "26571",
            package: "Can",
            unitOfMeasure: "Kg"
        },
        {
            id: "69530",
            package: "Carton",
            unitOfMeasure: "sm"
        },
        {
            id: "88925",
            package: "Carton",
            unitOfMeasure: "Pcs"
        },
        {
            id: "33592",
            package: "Elf Truck",
            unitOfMeasure: "Ton"
        }
    ];

    // Show edit modal
    function showEditModal(packageId) {
        const modal = document.getElementById('editPackageModal');
        const packageDef = packageDefinitions.find(p => p.id === packageId);

        if (packageDef) {
            document.getElementById('edit-package-id').value = packageDef.id;

            // Set the package name in the dropdown
            const packageSelect = document.getElementById('edit-package-name');
            for (let i = 0; i < packageSelect.options.length; i++) {
                if (packageSelect.options[i].value === packageDef.package) {
                    packageSelect.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('edit-unit-measure').value = packageDef.unitOfMeasure;
            modal.classList.remove('hidden');
        }
    }

    // Hide edit modal
    function hideEditModal() {
        const modal = document.getElementById('editPackageModal');
        modal.classList.add('hidden');

        // Reset the edit form
        const packageSelect = document.getElementById('edit-package-name');
        const newPackageInput = document.getElementById('edit-new-package-name');

        packageSelect.classList.remove('hidden');
        newPackageInput.classList.add('hidden');
        newPackageInput.value = '';
    }

    // Show delete confirmation modal
    function showDeleteModal(packageId) {
        const modal = document.getElementById('deletePackageModal');
        const packageDef = packageDefinitions.find(p => p.id === packageId);

        if (packageDef) {
            document.getElementById('delete-package-name').textContent = packageDef.package;
            document.getElementById('delete-unit-measure').textContent = packageDef.unitOfMeasure;
            document.getElementById('confirmDelete').setAttribute('data-id', packageId);
            modal.classList.remove('hidden');
        }
    }

    // Hide delete confirmation modal
    function hideDeleteModal() {
        const modal = document.getElementById('deletePackageModal');
        modal.classList.add('hidden');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Package name dropdown change
        document.getElementById('package_name').addEventListener('change', function() {
            const value = this.value;
            const packageNameContainer = document.getElementById('packageNameContainer');
            const newPackageInput = document.getElementById('new_package_name');
            const cancelButton = document.getElementById('cancelAddNew');

            if (value === 'add') {
                // Show input field for new package name
                this.classList.add('hidden');
                newPackageInput.classList.remove('hidden');
                newPackageInput.focus();
                cancelButton.classList.remove('hidden');
            }
        });

        // Edit package name dropdown change
        document.getElementById('edit-package-name').addEventListener('change', function() {
            const value = this.value;
            const newPackageInput = document.getElementById('edit-new-package-name');

            if (value === 'add') {
                // Show input field for new package name
                this.classList.add('hidden');
                newPackageInput.classList.remove('hidden');
                newPackageInput.focus();
            } else {
                newPackageInput.classList.add('hidden');
            }
        });

        // Cancel add new package
        document.getElementById('cancelAddNew').addEventListener('click', function() {
            const packageSelect = document.getElementById('package_name');
            const newPackageInput = document.getElementById('new_package_name');

            packageSelect.value = '';
            packageSelect.classList.remove('hidden');
            newPackageInput.classList.add('hidden');
            newPackageInput.value = '';
            this.classList.add('hidden');
        });

        // Package form submit
        document.getElementById('packageForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const packageSelect = document.getElementById('package_name');
            const newPackageInput = document.getElementById('new_package_name');
            const unitOfMeasure = document.getElementById('unit_of_measure').value;

            let packageName = packageSelect.value;
            if (packageName === 'add') {
                packageName = newPackageInput.value;
            } else {
                packageName = packageSelect.options[packageSelect.selectedIndex].text;
            }

            if (!packageName || !unitOfMeasure) {
                alert('Please fill in all fields');
                return;
            }

            // In a real application, you would send form data to the server
            alert(`Package definition saved: ${packageName} - ${unitOfMeasure}`);

            // Reset form
            packageSelect.value = '';
            packageSelect.classList.remove('hidden');
            newPackageInput.classList.add('hidden');
            newPackageInput.value = '';
            document.getElementById('unit_of_measure').value = '';
            document.getElementById('cancelAddNew').classList.add('hidden');
        });

        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const packageId = this.getAttribute('data-id');
                showEditModal(packageId);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const packageId = this.getAttribute('data-id');
                showDeleteModal(packageId);
            });
        });

        // Save edit package
        document.getElementById('saveEditPackage').addEventListener('click', function() {
            const packageId = document.getElementById('edit-package-id').value;
            const packageSelect = document.getElementById('edit-package-name');
            const newPackageInput = document.getElementById('edit-new-package-name');
            const unitOfMeasure = document.getElementById('edit-unit-measure').value;

            let packageName;
            if (packageSelect.value === 'add') {
                packageName = newPackageInput.value;
                if (!packageName) {
                    alert('Please enter a package name');
                    return;
                }
            } else {
                packageName = packageSelect.value;
            }

            if (!packageName || !unitOfMeasure) {
                alert('Please fill in all fields');
                return;
            }

            // In a real application, you would send form data to the server
            alert(`Package definition updated: ${packageName} - ${unitOfMeasure}`);
            hideEditModal();
        });

        // Confirm delete
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const packageId = this.getAttribute('data-id');

            // In a real application, you would send a delete request to the server
            alert(`Package definition ${packageId} would be deleted here`);
            hideDeleteModal();
        });

        // Search packages
        document.getElementById('searchPackages').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            // In a real application, you would filter packages based on the query
            if (query.length > 0) {
                console.log(`Searching for: ${query}`);
            }
        });

        // Filter packages
        document.getElementById('filterPackage').addEventListener('change', function() {
            const filterValue = this.value;

            // In a real application, you would filter packages based on the selected option
            if (filterValue) {
                console.log(`Filtering by package: ${filterValue}`);
            }
        });

        // Pagination
        document.querySelectorAll('#pagination-numbers button').forEach(button => {
            button.addEventListener('click', function() {
                // In a real application, you would navigate to the selected page
                console.log(`Navigating to page: ${this.textContent}`);
            });
        });

        document.getElementById('prev-page').addEventListener('click', function() {
            // In a real application, you would navigate to the previous page
            console.log('Navigating to previous page');
        });

        document.getElementById('next-page').addEventListener('click', function() {
            // In a real application, you would navigate to the next page
            console.log('Navigating to next page');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>