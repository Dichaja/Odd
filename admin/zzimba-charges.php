<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Charge Management';
$activeNav = 'zzimba-charges';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Access Charges</p>
                            <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="access-charges-count">0</p>
                            <p class="text-sm font-medium text-blue-700 whitespace-nowrap" id="access-charges-revenue">
                                UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-eye text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Commission Charges</p>
                            <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="commission-charges-count">
                                0</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap"
                                id="commission-charges-revenue">UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-percentage text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Transfer Charges</p>
                            <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="transfer-charges-count">0
                            </p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap"
                                id="transfer-charges-revenue">UGX 0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Total Revenue</p>
                            <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="total-revenue">UGX 0.00
                            </p>
                            <p class="text-sm font-medium text-orange-700 whitespace-nowrap" id="total-charges-count">0
                                Charges</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="border-b border-gray-200">
                <div class="hidden md:block">
                    <nav class="flex space-x-8 px-6 overflow-x-auto pb-2" aria-label="Tabs">
                        <button id="access-tab"
                            class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-primary text-primary"
                            onclick="switchChargeTab('access')">
                            <i class="fas fa-eye mr-2"></i>Access Charges
                        </button>
                        <button id="commission-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchChargeTab('commission')">
                            <i class="fas fa-percentage mr-2"></i>Commission Charges
                        </button>
                        <button id="transfer-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchChargeTab('transfer')">
                            <i class="fas fa-exchange-alt mr-2"></i>Transfer Charges
                        </button>
                        <button id="subscription-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchChargeTab('subscription')">
                            <i class="fas fa-calendar-alt mr-2"></i>Subscription Charges
                        </button>
                    </nav>
                </div>

                <div class="md:hidden px-6 py-4">
                    <div class="relative">
                        <button id="mobile-tab-toggle"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-eye text-primary"></i>
                                <span id="mobile-tab-label" class="font-medium text-gray-900">Access Charges</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                id="mobile-tab-chevron"></i>
                        </button>

                        <div id="mobile-tab-dropdown"
                            class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                            <div class="py-2">
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="access">
                                    <i class="fas fa-eye text-blue-600"></i>
                                    <span>Access Charges</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="commission">
                                    <i class="fas fa-percentage text-green-600"></i>
                                    <span>Commission Charges</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="transfer">
                                    <i class="fas fa-exchange-alt text-purple-600"></i>
                                    <span>Transfer Charges</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="subscription">
                                    <i class="fas fa-calendar-alt text-orange-600"></i>
                                    <span>Subscription Charges</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="charges-content" class="tab-content block">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="w-full lg:w-1/3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchCharges"
                                    class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                    placeholder="Search charges...">
                            </div>
                        </div>

                        <div class="hidden lg:block lg:flex-1"></div>

                        <div class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative">
                                <button id="viewColumnsBtn" onclick="toggleColumnSelector()"
                                    class="hidden lg:flex px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 text-sm flex items-center gap-2 hover:bg-gray-50">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>View</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>

                                <div id="columnSelector"
                                    class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-48">
                                    <div class="p-3 border-b border-gray-100">
                                        <h4 class="text-sm font-semibold text-gray-900">Show Columns</h4>
                                        <p class="text-xs text-gray-500 mt-1">Select at least 3 columns</p>
                                    </div>
                                    <div class="p-2 space-y-1" id="columnCheckboxes">
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="details" checked>
                                            <span class="text-sm text-gray-700">Charge Details</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="type" checked>
                                            <span class="text-sm text-gray-700">Charge Type</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="amount" checked>
                                            <span class="text-sm text-gray-700">Amount</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="applicable" checked>
                                            <span class="text-sm text-gray-700">Applicable To</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="status" checked>
                                            <span class="text-sm text-gray-700">Status</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox"
                                                class="column-checkbox rounded border-gray-300 text-primary focus:ring-primary"
                                                data-column="actions" checked>
                                            <span class="text-sm text-gray-700">Actions</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button id="create-charge-btn"
                                class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                <i class="fas fa-plus"></i>
                                <span>Create Charge</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="hidden lg:block">
                    <div class="overflow-x-auto max-h-[70vh]">
                        <table class="w-full" id="charges-table">
                            <thead class="bg-user-accent border-b border-gray-200 sticky top-0">
                                <tr>
                                    <th data-column="details"
                                        class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                        Charge Details</th>
                                    <th data-column="type"
                                        class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                        Type</th>
                                    <th data-column="amount"
                                        class="px-3 py-2 text-right text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                        Amount</th>
                                    <th data-column="applicable"
                                        class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                        Applicable To</th>
                                    <th data-column="status" id="statusHeader"
                                        class="px-3 py-2 text-left text-xs font-semibold text-secondary uppercase tracking-wider cursor-pointer whitespace-nowrap">
                                        Status
                                        <i id="statusSortIcon" class="fas fa-sort text-gray-400 ml-1"></i>
                                    </th>
                                    <th data-column="actions"
                                        class="px-3 py-2 text-center text-xs font-semibold text-secondary uppercase tracking-wider whitespace-nowrap">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="charges-table-body" class="divide-y divide-gray-100">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="lg:hidden p-4 space-y-4 max-h-[70vh] overflow-y-auto" id="charges-mobile">
                </div>

                <div id="empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-dollar-sign text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No charges found</h3>
                    <p class="text-gray-500 mb-6">Create a new charge or adjust your search filters</p>
                    <button onclick="showCreateChargeForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Create
                        Charge</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="createChargeOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideCreateChargeForm()">
    </div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-primary"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Create New Charge</h3>
                </div>
                <button onclick="hideCreateChargeForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="createChargeForm" class="space-y-6" onsubmit="return false;">
                    <div>
                        <label for="chargeName" class="block text-sm font-semibold text-gray-700 mb-2">Charge
                            Name</label>
                        <input type="text" id="chargeName" name="chargeName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter charge name" required>
                    </div>

                    <div>
                        <label for="chargeDescription"
                            class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="chargeDescription" name="chargeDescription" rows="3"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter charge description"></textarea>
                    </div>

                    <div>
                        <label for="chargeCategory"
                            class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <select id="chargeCategory" name="chargeCategory"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Category</option>
                            <option value="access">Access Charges</option>
                            <option value="commission">Commission Charges</option>
                            <option value="transfer">Transfer Charges</option>
                            <option value="subscription">Subscription Charges</option>
                        </select>
                    </div>

                    <div>
                        <label for="chargeType" class="block text-sm font-semibold text-gray-700 mb-2">Charge
                            Type</label>
                        <select id="chargeType" name="chargeType"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required onchange="toggleAmountInput()">
                            <option value="">Select Type</option>
                            <option value="flat">Flat Fee</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div>
                        <label for="chargeAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span id="amountLabel">Amount</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" id="currencySymbol">UGX</span>
                            </div>
                            <input type="number" id="chargeAmount" name="chargeAmount" step="0.01" min="0"
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="0.00" required>
                        </div>
                    </div>

                    <div>
                        <label for="applicableTo" class="block text-sm font-semibold text-gray-700 mb-2">Applicable
                            To</label>
                        <select id="applicableTo" name="applicableTo"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Applicable To</option>
                            <option value="users">Users Only</option>
                            <option value="vendors">Vendors Only</option>
                            <option value="all">All Users</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="chargeStatus" value="active" class="sr-only" checked>
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Active</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="chargeStatus" value="inactive" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Inactive</div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideCreateChargeForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                    <button id="submitChargeForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Create
                        Charge</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editChargeOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="hideEditChargeForm()"></div>
    <div
        class="absolute inset-y-0 right-0 w-full max-w-lg bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-out">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Charge</h3>
                </div>
                <button onclick="hideEditChargeForm()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form id="editChargeForm" class="space-y-6" onsubmit="return false;">
                    <input type="hidden" id="editChargeId">

                    <div>
                        <label for="editChargeName" class="block text-sm font-semibold text-gray-700 mb-2">Charge
                            Name</label>
                        <input type="text" id="editChargeName" name="editChargeName"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter charge name" required>
                    </div>

                    <div>
                        <label for="editChargeDescription"
                            class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                        <textarea id="editChargeDescription" name="editChargeDescription" rows="3"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            placeholder="Enter charge description"></textarea>
                    </div>

                    <div>
                        <label for="editChargeCategory"
                            class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <select id="editChargeCategory" name="editChargeCategory"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Category</option>
                            <option value="access">Access Charges</option>
                            <option value="commission">Commission Charges</option>
                            <option value="transfer">Transfer Charges</option>
                            <option value="subscription">Subscription Charges</option>
                        </select>
                    </div>

                    <div>
                        <label for="editChargeType" class="block text-sm font-semibold text-gray-700 mb-2">Charge
                            Type</label>
                        <select id="editChargeType" name="editChargeType"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required onchange="toggleEditAmountInput()">
                            <option value="">Select Type</option>
                            <option value="flat">Flat Fee</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div>
                        <label for="editChargeAmount" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span id="editAmountLabel">Amount</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" id="editCurrencySymbol">UGX</span>
                            </div>
                            <input type="number" id="editChargeAmount" name="editChargeAmount" step="0.01" min="0"
                                class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="0.00" required>
                        </div>
                    </div>

                    <div>
                        <label for="editApplicableTo" class="block text-sm font-semibold text-gray-700 mb-2">Applicable
                            To</label>
                        <select id="editApplicableTo" name="editApplicableTo"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                            <option value="">Select Applicable To</option>
                            <option value="users">Users Only</option>
                            <option value="vendors">Vendors Only</option>
                            <option value="all">All Users</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="editChargeStatus" value="active" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Active</div>
                            </label>

                            <label
                                class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="editChargeStatus" value="inactive" class="sr-only">
                                <div
                                    class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="text-sm font-medium text-gray-900">Inactive</div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-200 bg-gray-50">
                <div class="flex gap-3">
                    <button onclick="hideEditChargeForm()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                    <button id="updateChargeForm"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Update
                        Charge</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideDeleteConfirm()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-gray-600 mb-6" id="deleteMessage">Are you sure you want to delete this charge?</p>
            <div class="flex gap-3">
                <button onclick="hideDeleteConfirm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="confirmDeleteBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_URL = '<?= BASE_URL ?>admin/fetch/manageCharges.php';
    let charges = [];
    let statusSortAsc = true;
    let currentChargeTab = 'access';

    const CHARGES_COLUMNS_STORAGE_KEY = 'charges_columns';
    let visibleChargeColumns = ['details', 'type', 'amount', 'applicable', 'status', 'actions'];

    // Dummy data for design purposes
    const dummyCharges = [
        {
            id: 1,
            name: 'Price Viewing Fee',
            description: 'Fee charged to users for viewing product prices',
            category: 'access',
            type: 'flat',
            amount: 500,
            applicable_to: 'users',
            status: 'active',
            created_at: '2024-01-15T10:30:00Z',
            updated_at: '2024-01-15T10:30:00Z'
        },
        {
            id: 2,
            name: 'Vendor Location Access',
            description: 'Fee for accessing vendor location information',
            category: 'access',
            type: 'flat',
            amount: 1000,
            applicable_to: 'users',
            status: 'active',
            created_at: '2024-01-16T14:20:00Z',
            updated_at: '2024-01-16T14:20:00Z'
        },
        {
            id: 3,
            name: 'Sales Commission',
            description: 'Commission charged on vendor sales',
            category: 'commission',
            type: 'percentage',
            amount: 5.5,
            applicable_to: 'vendors',
            status: 'active',
            created_at: '2024-01-17T09:15:00Z',
            updated_at: '2024-01-17T09:15:00Z'
        },
        {
            id: 4,
            name: 'Wallet Transfer Fee',
            description: 'Fee charged for transfers between vendor wallets',
            category: 'transfer',
            type: 'percentage',
            amount: 2.0,
            applicable_to: 'vendors',
            status: 'active',
            created_at: '2024-01-18T16:45:00Z',
            updated_at: '2024-01-18T16:45:00Z'
        },
        {
            id: 5,
            name: 'Premium Subscription',
            description: 'Monthly premium subscription for enhanced features',
            category: 'subscription',
            type: 'flat',
            amount: 25000,
            applicable_to: 'all',
            status: 'active',
            created_at: '2024-01-19T11:30:00Z',
            updated_at: '2024-01-19T11:30:00Z'
        },
        {
            id: 6,
            name: 'Product Listing Fee',
            description: 'Fee for listing products on the platform',
            category: 'access',
            type: 'flat',
            amount: 2000,
            applicable_to: 'vendors',
            status: 'inactive',
            created_at: '2024-01-20T08:00:00Z',
            updated_at: '2024-01-20T08:00:00Z'
        },
        {
            id: 7,
            name: 'High-Value Transaction Fee',
            description: 'Additional fee for transactions above UGX 1M',
            category: 'commission',
            type: 'percentage',
            amount: 1.5,
            applicable_to: 'all',
            status: 'active',
            created_at: '2024-01-21T13:20:00Z',
            updated_at: '2024-01-21T13:20:00Z'
        },
        {
            id: 8,
            name: 'Express Transfer Fee',
            description: 'Fee for express wallet transfers (under 5 minutes)',
            category: 'transfer',
            type: 'flat',
            amount: 1500,
            applicable_to: 'all',
            status: 'active',
            created_at: '2024-01-22T15:10:00Z',
            updated_at: '2024-01-22T15:10:00Z'
        }
    ];

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function getChargeTypeBadge(type, amount) {
        if (type === 'flat') {
            return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-coins mr-2"></i>Flat Fee</span>`;
        } else {
            return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-percentage mr-2"></i>Percentage</span>`;
        }
    }

    function getStatusBadge(status) {
        const statusText = status.charAt(0).toUpperCase() + status.slice(1);
        const statusClasses = {
            'active': 'bg-green-100 text-green-800',
            'inactive': 'bg-gray-100 text-gray-800'
        };
        const className = statusClasses[status] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${className}">${statusText}</span>`;
    }

    function getApplicableToBadge(applicableTo) {
        const badges = {
            'users': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-user mr-2"></i>Users</span>',
            'vendors': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-store mr-2"></i>Vendors</span>',
            'all': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"><i class="fas fa-users mr-2"></i>All Users</span>'
        };
        return badges[applicableTo] || '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function formatAmount(type, amount) {
        if (type === 'percentage') {
            return `${amount}%`;
        } else {
            return `UGX ${formatCurrency(amount)}`;
        }
    }

    function switchChargeTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-b-primary', 'text-primary');
            btn.classList.add('border-b-transparent', 'text-gray-500');
        });

        const tabId = `${tabName}-tab`;
        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.remove('border-b-transparent', 'text-gray-500');
            activeTab.classList.add('border-b-primary', 'text-primary');
        }

        currentChargeTab = tabName;
        renderFilteredCharges();

        const tabLabels = {
            'access': { label: 'Access Charges', icon: 'fas fa-eye' },
            'commission': { label: 'Commission Charges', icon: 'fas fa-percentage' },
            'transfer': { label: 'Transfer Charges', icon: 'fas fa-exchange-alt' },
            'subscription': { label: 'Subscription Charges', icon: 'fas fa-calendar-alt' }
        };
        const tabInfo = tabLabels[tabName] || tabLabels['access'];
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);
    }

    function updateMobileTabLabel(label, icon) {
        const labelElement = document.getElementById('mobile-tab-label');
        const toggleButton = document.getElementById('mobile-tab-toggle');
        if (labelElement && toggleButton) {
            labelElement.textContent = label;
            const iconElement = toggleButton.querySelector('i');
            if (iconElement) {
                iconElement.className = `${icon} text-primary`;
            }
        }
    }

    function toggleMobileTabDropdown() {
        const dropdown = document.getElementById('mobile-tab-dropdown');
        const chevron = document.getElementById('mobile-tab-chevron');
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    function updateQuickStats() {
        const accessCharges = charges.filter(c => c.category === 'access');
        const commissionCharges = charges.filter(c => c.category === 'commission');
        const transferCharges = charges.filter(c => c.category === 'transfer');

        // Calculate estimated revenue (dummy calculation for design)
        const accessRevenue = accessCharges.filter(c => c.type === 'flat').reduce((sum, c) => sum + (c.amount * 100), 0);
        const commissionRevenue = commissionCharges.reduce((sum, c) => sum + (c.type === 'flat' ? c.amount * 50 : c.amount * 1000), 0);
        const transferRevenue = transferCharges.reduce((sum, c) => sum + (c.type === 'flat' ? c.amount * 75 : c.amount * 500), 0);
        const totalRevenue = accessRevenue + commissionRevenue + transferRevenue;

        document.getElementById('access-charges-count').textContent = accessCharges.length;
        document.getElementById('access-charges-revenue').textContent = `UGX ${formatCurrency(accessRevenue)}`;

        document.getElementById('commission-charges-count').textContent = commissionCharges.length;
        document.getElementById('commission-charges-revenue').textContent = `UGX ${formatCurrency(commissionRevenue)}`;

        document.getElementById('transfer-charges-count').textContent = transferCharges.length;
        document.getElementById('transfer-charges-revenue').textContent = `UGX ${formatCurrency(transferRevenue)}`;

        document.getElementById('total-revenue').textContent = `UGX ${formatCurrency(totalRevenue)}`;
        document.getElementById('total-charges-count').textContent = `${charges.length} Charges`;
    }

    function loadCharges() {
        // Using dummy data for design purposes
        charges = dummyCharges;
        renderFilteredCharges();
        updateQuickStats();
        adjustTableFontSize();
    }

    function renderFilteredCharges() {
        let filteredCharges = charges.filter(c => c.category === currentChargeTab);

        const query = document.getElementById('searchCharges').value.trim().toLowerCase();
        if (query) {
            filteredCharges = filteredCharges.filter(c =>
                c.name.toLowerCase().includes(query) ||
                c.description.toLowerCase().includes(query)
            );
        }

        renderChargesTable(filteredCharges);
        adjustTableFontSize();
    }

    function renderChargesTable(list) {
        const tbody = document.getElementById('charges-table-body');
        const mobile = document.getElementById('charges-mobile');
        const emptyState = document.getElementById('empty-state');

        tbody.innerHTML = '';
        mobile.innerHTML = '';

        if (list.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else {
            emptyState.classList.add('hidden');
        }

        list.forEach((charge, index) => {
            const tr = document.createElement('tr');
            tr.className = `${index % 2 === 0 ? 'bg-user-content' : 'bg-white'} hover:bg-user-secondary/20 transition-colors`;

            const maxDetailsLength = 30;
            let displayName = charge.name;
            if (displayName.length > maxDetailsLength) {
                displayName = displayName.substring(0, maxDetailsLength) + '...';
            }

            tr.innerHTML = `
                <td data-column="details" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center ${charge.category === 'access' ? 'bg-blue-100' :
                    charge.category === 'commission' ? 'bg-green-100' :
                        charge.category === 'transfer' ? 'bg-purple-100' : 'bg-orange-100'
                }">
                            <i class="${charge.category === 'access' ? 'fas fa-eye text-blue-600' :
                    charge.category === 'commission' ? 'fas fa-percentage text-green-600' :
                        charge.category === 'transfer' ? 'fas fa-exchange-alt text-purple-600' : 'fas fa-calendar-alt text-orange-600'
                } text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs font-medium text-gray-900 leading-tight" title="${charge.name}">${displayName}</div>
                            <div class="text-xs text-gray-500 mt-0.5" title="${charge.description}">${charge.description.substring(0, 40)}${charge.description.length > 40 ? '...' : ''}</div>
                        </div>
                    </div>
                </td>
                <td data-column="type" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    ${getChargeTypeBadge(charge.type, charge.amount)}
                </td>
                <td data-column="amount" class="px-3 py-2 text-right text-xs font-semibold text-gray-900 whitespace-nowrap ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    ${formatAmount(charge.type, charge.amount)}
                </td>
                <td data-column="applicable" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    ${getApplicableToBadge(charge.applicable_to)}
                </td>
                <td data-column="status" class="px-3 py-2 text-xs ${index % 2 === 0 ? 'bg-user-accent/30' : 'bg-user-secondary/10'}">
                    ${getStatusBadge(charge.status)}
                </td>
                <td data-column="actions" class="px-3 py-2 ${index % 2 === 0 ? 'bg-user-secondary/5' : 'bg-user-accent/20'}">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="showEditChargeForm(${charge.id})" 
                            class="w-6 h-6 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" 
                            title="Edit Charge">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="showDeleteConfirm(${charge.id})" 
                            class="w-6 h-6 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" 
                            title="Delete Charge">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </td>`;
            tbody.appendChild(tr);

            const card = document.createElement('div');
            card.className = 'bg-gray-50 rounded-xl p-4 border border-gray-100';
            card.innerHTML = `
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center ${charge.category === 'access' ? 'bg-blue-100' :
                    charge.category === 'commission' ? 'bg-green-100' :
                        charge.category === 'transfer' ? 'bg-purple-100' : 'bg-orange-100'
                }">
                            <i class="${charge.category === 'access' ? 'fas fa-eye text-blue-600' :
                    charge.category === 'commission' ? 'fas fa-percentage text-green-600' :
                        charge.category === 'transfer' ? 'fas fa-exchange-alt text-purple-600' : 'fas fa-calendar-alt text-orange-600'
                }"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-medium text-gray-900 text-sm truncate" title="${charge.name}">${charge.name}</div>
                            <div class="text-xs text-gray-500 mt-1">${charge.description}</div>
                        </div>
                    </div>
                    ${getChargeTypeBadge(charge.type, charge.amount)}
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Amount</span>
                        <div class="font-semibold text-gray-900 mt-1">${formatAmount(charge.type, charge.amount)}</div>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Status</span>
                        <div class="mt-1">${getStatusBadge(charge.status)}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <span class="text-gray-500 uppercase tracking-wide text-xs">Applicable To</span>
                    <div class="mt-2">${getApplicableToBadge(charge.applicable_to)}</div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button onclick="showEditChargeForm(${charge.id})" 
                        class="px-3 py-2 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button onclick="showDeleteConfirm(${charge.id})" 
                        class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                        <i class="fas fa-trash-alt mr-1"></i>Delete
                    </button>
                </div>`;
            mobile.appendChild(card);
        });

        applyColumnVisibility('charges-table', visibleChargeColumns);
    }

    function loadColumnVisibility() {
        const savedChargeColumns = localStorage.getItem(CHARGES_COLUMNS_STORAGE_KEY);

        if (savedChargeColumns) {
            visibleChargeColumns = JSON.parse(savedChargeColumns);
        }

        updateColumnCheckboxes();
    }

    function saveColumnVisibility() {
        localStorage.setItem(CHARGES_COLUMNS_STORAGE_KEY, JSON.stringify(visibleChargeColumns));
    }

    function updateColumnCheckboxes() {
        document.querySelectorAll('.column-checkbox').forEach(checkbox => {
            const column = checkbox.getAttribute('data-column');
            checkbox.checked = visibleChargeColumns.includes(column);
        });
    }

    function applyColumnVisibility(tableId, visibleColumns) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const headers = table.querySelectorAll('thead th[data-column]');
        headers.forEach(header => {
            const column = header.getAttribute('data-column');
            if (visibleColumns.includes(column)) {
                header.style.display = '';
            } else {
                header.style.display = 'none';
            }
        });

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td[data-column]');
            cells.forEach(cell => {
                const column = cell.getAttribute('data-column');
                if (visibleColumns.includes(column)) {
                    cell.style.display = '';
                } else {
                    cell.style.display = 'none';
                }
            });
        });
    }

    function toggleColumnSelector() {
        const selector = document.getElementById('columnSelector');
        selector.classList.toggle('hidden');
    }

    function disableBackgroundScroll() {
        document.body.style.overflow = 'hidden';
    }

    function enableBackgroundScroll() {
        document.body.style.overflow = '';
    }

    function toggleAmountInput() {
        const chargeType = document.getElementById('chargeType').value;
        const amountLabel = document.getElementById('amountLabel');
        const currencySymbol = document.getElementById('currencySymbol');

        if (chargeType === 'percentage') {
            amountLabel.textContent = 'Percentage';
            currencySymbol.textContent = '%';
        } else {
            amountLabel.textContent = 'Amount';
            currencySymbol.textContent = 'UGX';
        }
    }

    function toggleEditAmountInput() {
        const chargeType = document.getElementById('editChargeType').value;
        const amountLabel = document.getElementById('editAmountLabel');
        const currencySymbol = document.getElementById('editCurrencySymbol');

        if (chargeType === 'percentage') {
            amountLabel.textContent = 'Percentage';
            currencySymbol.textContent = '%';
        } else {
            amountLabel.textContent = 'Amount';
            currencySymbol.textContent = 'UGX';
        }
    }

    function adjustTableFontSize() {
        const table = document.getElementById('charges-table');
        if (!table) return;

        const container = table.parentElement;
        let fontSize = 14;

        table.style.fontSize = fontSize + 'px';

        while ((table.scrollWidth > container.clientWidth || hasOverflowingContent(table)) && fontSize > 8) {
            fontSize -= 0.5;
            table.style.fontSize = fontSize + 'px';
        }

        if (fontSize < 10) {
            table.style.fontSize = '10px';
        }
    }

    function hasOverflowingContent(table) {
        const cells = table.querySelectorAll('td');
        for (let cell of cells) {
            if (cell.scrollHeight > cell.clientHeight || cell.scrollWidth > cell.clientWidth) {
                return true;
            }
        }
        return false;
    }

    function sortByStatus() {
        charges.sort((a, b) => {
            const sa = a.status.toLowerCase();
            const sb = b.status.toLowerCase();
            if (sa < sb) return statusSortAsc ? -1 : 1;
            if (sa > sb) return statusSortAsc ? 1 : -1;
            return 0;
        });
        statusSortAsc = !statusSortAsc;
        document.getElementById('statusSortIcon').className = statusSortAsc
            ? 'fas fa-sort-up text-gray-400 ml-1'
            : 'fas fa-sort-down text-gray-400 ml-1';
        renderFilteredCharges();
        adjustTableFontSize();
    }

    function showCreateChargeForm() {
        const offcanvas = document.getElementById('createChargeOffcanvas');
        const form = document.getElementById('createChargeForm');
        form.reset();

        // Set default category based on current tab
        document.getElementById('chargeCategory').value = currentChargeTab;

        offcanvas.classList.remove('hidden');
        disableBackgroundScroll();
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideCreateChargeForm() {
        const offcanvas = document.getElementById('createChargeOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        enableBackgroundScroll();
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function createCharge() {
        const name = document.getElementById('chargeName').value.trim();
        const description = document.getElementById('chargeDescription').value.trim();
        const category = document.getElementById('chargeCategory').value;
        const type = document.getElementById('chargeType').value;
        const amount = parseFloat(document.getElementById('chargeAmount').value);
        const applicableTo = document.getElementById('applicableTo').value;
        const status = document.querySelector('input[name="chargeStatus"]:checked')?.value;

        if (!name || !category || !type || !amount || !applicableTo || !status) {
            alert('Please fill in all required fields');
            return;
        }

        // For demo purposes, add to dummy data
        const newCharge = {
            id: charges.length + 1,
            name: name,
            description: description,
            category: category,
            type: type,
            amount: amount,
            applicable_to: applicableTo,
            status: status,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };

        charges.push(newCharge);
        hideCreateChargeForm();
        renderFilteredCharges();
        updateQuickStats();
        adjustTableFontSize();
    }

    function showEditChargeForm(id) {
        const charge = charges.find(c => c.id === id);
        if (!charge) return;

        document.getElementById('editChargeId').value = charge.id;
        document.getElementById('editChargeName').value = charge.name;
        document.getElementById('editChargeDescription').value = charge.description;
        document.getElementById('editChargeCategory').value = charge.category;
        document.getElementById('editChargeType').value = charge.type;
        document.getElementById('editChargeAmount').value = charge.amount;
        document.getElementById('editApplicableTo').value = charge.applicable_to;

        const statusRadio = document.querySelector(`input[name="editChargeStatus"][value="${charge.status}"]`);
        if (statusRadio) statusRadio.checked = true;

        toggleEditAmountInput();

        const offcanvas = document.getElementById('editChargeOffcanvas');
        offcanvas.classList.remove('hidden');
        disableBackgroundScroll();
        setTimeout(() => {
            const panel = offcanvas.querySelector('.translate-x-full');
            if (panel) panel.classList.remove('translate-x-full');
        }, 10);
    }

    function hideEditChargeForm() {
        const offcanvas = document.getElementById('editChargeOffcanvas');
        const panel = offcanvas.querySelector('.transform');
        if (panel) panel.classList.add('translate-x-full');
        enableBackgroundScroll();
        setTimeout(() => offcanvas.classList.add('hidden'), 300);
    }

    async function updateCharge() {
        const id = parseInt(document.getElementById('editChargeId').value);
        const name = document.getElementById('editChargeName').value.trim();
        const description = document.getElementById('editChargeDescription').value.trim();
        const category = document.getElementById('editChargeCategory').value;
        const type = document.getElementById('editChargeType').value;
        const amount = parseFloat(document.getElementById('editChargeAmount').value);
        const applicableTo = document.getElementById('editApplicableTo').value;
        const status = document.querySelector('input[name="editChargeStatus"]:checked')?.value;

        if (!name || !category || !type || !amount || !applicableTo || !status) {
            alert('Please fill in all required fields');
            return;
        }

        // For demo purposes, update dummy data
        const chargeIndex = charges.findIndex(c => c.id === id);
        if (chargeIndex !== -1) {
            charges[chargeIndex] = {
                ...charges[chargeIndex],
                name: name,
                description: description,
                category: category,
                type: type,
                amount: amount,
                applicable_to: applicableTo,
                status: status,
                updated_at: new Date().toISOString()
            };
        }

        hideEditChargeForm();
        renderFilteredCharges();
        updateQuickStats();
        adjustTableFontSize();
    }

    function showDeleteConfirm(id) {
        const modal = document.getElementById('deleteModal');
        const message = document.getElementById('deleteMessage');
        const confirmBtn = document.getElementById('confirmDeleteBtn');

        const charge = charges.find(c => c.id === id);
        if (charge) {
            message.textContent = `Are you sure you want to delete "${charge.name}"? This action cannot be undone.`;
        }

        confirmBtn.setAttribute('data-id', id);
        modal.classList.remove('hidden');
        disableBackgroundScroll();
    }

    function hideDeleteConfirm() {
        document.getElementById('deleteModal').classList.add('hidden');
        enableBackgroundScroll();
    }

    async function confirmDelete() {
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        const id = parseInt(confirmBtn.getAttribute('data-id'));

        // For demo purposes, remove from dummy data
        const chargeIndex = charges.findIndex(c => c.id === id);
        if (chargeIndex !== -1) {
            charges.splice(chargeIndex, 1);
        }

        hideDeleteConfirm();
        renderFilteredCharges();
        updateQuickStats();
        adjustTableFontSize();
    }

    function filterCharges() {
        renderFilteredCharges();
    }

    document.addEventListener('click', function (event) {
        const columnSelector = document.getElementById('columnSelector');
        const columnBtn = document.getElementById('viewColumnsBtn');
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (columnSelector && columnBtn && !columnSelector.contains(event.target) && !columnBtn.contains(event.target)) {
            columnSelector.classList.add('hidden');
        }

        if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
            mobileDropdown.classList.add('hidden');
            document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('column-checkbox')) {
            const column = event.target.getAttribute('data-column');
            const isChecked = event.target.checked;

            if (isChecked) {
                if (!visibleChargeColumns.includes(column)) {
                    visibleChargeColumns.push(column);
                }
            } else {
                if (visibleChargeColumns.length <= 3) {
                    event.target.checked = true;
                    return;
                }
                visibleChargeColumns = visibleChargeColumns.filter(col => col !== column);
            }

            applyColumnVisibility('charges-table', visibleChargeColumns);
            saveColumnVisibility();
            adjustTableFontSize();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadColumnVisibility();
        loadCharges();
        switchChargeTab('access');

        document.getElementById('create-charge-btn').addEventListener('click', showCreateChargeForm);
        document.getElementById('statusHeader').addEventListener('click', sortByStatus);

        document.getElementById('submitChargeForm').addEventListener('click', createCharge);
        document.getElementById('updateChargeForm').addEventListener('click', updateCharge);
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);

        document.getElementById('searchCharges').addEventListener('input', filterCharges);

        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchChargeTab(tab);
                toggleMobileTabDropdown();
            });
        });

        window.addEventListener('resize', () => {
            adjustTableFontSize();
        });
    });

    window.switchChargeTab = switchChargeTab;
    window.showEditChargeForm = showEditChargeForm;
    window.showDeleteConfirm = showDeleteConfirm;
    window.hideDeleteConfirm = hideDeleteConfirm;
    window.hideCreateChargeForm = hideCreateChargeForm;
    window.hideEditChargeForm = hideEditChargeForm;
    window.showCreateChargeForm = showCreateChargeForm;
    window.toggleColumnSelector = toggleColumnSelector;
    window.toggleAmountInput = toggleAmountInput;
    window.toggleEditAmountInput = toggleEditAmountInput;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>