<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Zzimba Credit Settings';
$activeNav = 'zzimba-credit-settings';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Zzimba Credit Settings</h1>
                <p class="text-gray-600 mt-1">Configure charges, commissions, and system pricing</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">
            <div class="hidden lg:block w-64 flex-shrink-0">
                <div id="desktop-nav">
                    <nav class="space-y-2" aria-label="Settings Navigation">
                        <button id="sms-tab"
                            class="tab-button active w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 bg-primary/10 text-primary border border-primary/20"
                            onclick="switchSettingsTab('sms')">
                            <i class="fas fa-sms"></i>
                            <span>SMS Settings</span>
                            <div id="sms-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="bonus-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('bonus')">
                            <i class="fas fa-gift"></i>
                            <span>Welcome Bonus</span>
                            <div id="bonus-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="access-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('access')">
                            <i class="fas fa-eye"></i>
                            <span>Access Charges</span>
                            <div id="access-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="commission-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('commission')">
                            <i class="fas fa-percentage"></i>
                            <span>Commission</span>
                            <div id="commission-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="transfer-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('transfer')">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transfer</span>
                            <div id="transfer-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="withdrawal-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('withdrawal')">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Withdrawal</span>
                            <div id="withdrawal-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                        <button id="subscription-tab"
                            class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl transition-all duration-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                            onclick="switchSettingsTab('subscription')">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Subscription</span>
                            <div id="subscription-warning" class="ml-auto hidden">
                                <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                            </div>
                        </button>
                    </nav>
                </div>
            </div>

            <div class="flex-1">
                <div class="lg:hidden mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
                        <div class="relative">
                            <button id="mobile-tab-toggle"
                                class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-sms text-primary"></i>
                                    <span id="mobile-tab-label" class="font-medium text-gray-900">SMS Settings</span>
                                    <div id="mobile-current-warning" class="ml-2 hidden">
                                        <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                    id="mobile-tab-chevron"></i>
                            </button>

                            <div id="mobile-tab-dropdown"
                                class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                                <div class="py-2">
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="sms">
                                        <i class="fas fa-sms text-blue-600"></i>
                                        <span>SMS Settings</span>
                                        <div id="mobile-sms-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="bonus">
                                        <i class="fas fa-gift text-green-600"></i>
                                        <span>Welcome Bonus</span>
                                        <div id="mobile-bonus-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="access">
                                        <i class="fas fa-eye text-purple-600"></i>
                                        <span>Access Charges</span>
                                        <div id="mobile-access-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="commission">
                                        <i class="fas fa-percentage text-orange-600"></i>
                                        <span>Commission</span>
                                        <div id="mobile-commission-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="transfer">
                                        <i class="fas fa-exchange-alt text-red-600"></i>
                                        <span>Transfer</span>
                                        <div id="mobile-transfer-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="withdrawal">
                                        <i class="fas fa-money-bill-wave text-indigo-600"></i>
                                        <span>Withdrawal</span>
                                        <div id="mobile-withdrawal-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                    <button
                                        class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        data-tab="subscription">
                                        <i class="fas fa-calendar-alt text-pink-600"></i>
                                        <span>Subscription</span>
                                        <div id="mobile-subscription-warning" class="ml-auto hidden">
                                            <i class="fas fa-exclamation-triangle text-orange-500 text-sm"></i>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="settings-content" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200" id="settings-container">
                        <div class="p-6 border-b border-gray-100">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="w-full lg:w-1/3">
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" id="searchSettings"
                                            class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                            placeholder="Search settings...">
                                    </div>
                                </div>

                                <div
                                    class="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                    <button id="create-setting-btn"
                                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30 w-full sm:w-auto">
                                        <i class="fas fa-plus"></i>
                                        <span>Add Setting</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="category-warning"
                            class="hidden p-4 bg-orange-50 border-l-4 border-orange-400 mx-6 mt-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-orange-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-orange-700" id="warning-message">
                                        Configuration incomplete. Please ensure there are active settings for both Users
                                        and Vendors.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6" id="settings-grid">
                            <div class="flex items-center justify-center py-16">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="createSettingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCreateSettingForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-plus text-primary"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Add New Setting</h3>
            </div>
            <button onclick="hideCreateSettingForm()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="createSettingForm" class="space-y-6">
                <div>
                    <label for="settingName" class="block text-sm font-semibold text-gray-700 mb-2">Setting Name</label>
                    <input type="text" id="settingName" name="settingName"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter setting name" required oninput="updateSettingKey()">
                </div>

                <div>
                    <label for="settingKeyPreview" class="block text-sm font-semibold text-gray-700 mb-2">Setting Key
                        (Auto-generated)</label>
                    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 font-mono text-sm"
                        id="settingKeyPreview">setting_key_will_appear_here</div>
                </div>

                <div>
                    <label for="settingDescription"
                        class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="settingDescription" name="settingDescription" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter setting description"></textarea>
                </div>

                <div>
                    <label for="settingCategory" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                    <select id="settingCategory" name="settingCategory"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                        <option value="">Select Category</option>
                        <option value="sms">SMS Settings</option>
                        <option value="bonus">Welcome Bonus</option>
                        <option value="access">Access Charges</option>
                        <option value="commission">Commission</option>
                        <option value="transfer">Transfer</option>
                        <option value="withdrawal">Withdrawal</option>
                        <option value="subscription">Subscription</option>
                    </select>
                </div>

                <div>
                    <label for="settingType" class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                    <select id="settingType" name="settingType"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required onchange="toggleValueInput()">
                        <option value="">Select Type</option>
                        <option value="flat">Flat Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>

                <div>
                    <label for="settingValue" class="block text-sm font-semibold text-gray-700 mb-2">
                        <span id="valueLabel">Value</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm" id="valueSymbol">Sh.</span>
                        </div>
                        <input type="number" id="settingValue" name="settingValue" step="0.01" min="0"
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
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideCreateSettingForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="submitSettingForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Create
                    Setting</button>
            </div>
        </div>
    </div>
</div>

<div id="editSettingModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEditSettingForm()"></div>
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-secondary font-rubik">Edit Setting</h3>
            </div>
            <button onclick="hideEditSettingForm()"
                class="w-8 h-8 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            <form id="editSettingForm" class="space-y-6">
                <input type="hidden" id="editSettingId">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Setting Name</label>
                    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700"
                        id="editSettingNameDisplay"></div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Setting Key</label>
                    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700 font-mono text-sm"
                        id="editSettingKeyDisplay"></div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-700"
                        id="editSettingDescriptionDisplay"></div>
                </div>

                <div>
                    <label for="editSettingType" class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                    <select id="editSettingType" name="editSettingType"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required onchange="toggleEditValueInput()">
                        <option value="flat">Flat Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>

                <div>
                    <label for="editSettingValue" class="block text-sm font-semibold text-gray-700 mb-2">
                        <span id="editValueLabel">Value</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm" id="editValueSymbol">Sh.</span>
                        </div>
                        <input type="number" id="editSettingValue" name="editSettingValue" step="0.01" min="0"
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
                        <option value="users">Users Only</option>
                        <option value="vendors">Vendors Only</option>
                        <option value="all">All Users</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex gap-3">
                <button onclick="hideEditSettingForm()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="updateSettingForm"
                    class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium shadow-lg shadow-primary/25">Update
                    Setting</button>
            </div>
        </div>
    </div>
</div>

<div id="messageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideMessageModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div id="messageIcon" class="w-12 h-12 rounded-xl flex items-center justify-center">
                    <i id="messageIconClass" class="text-xl"></i>
                </div>
                <div>
                    <h3 id="messageTitle" class="text-lg font-semibold text-gray-900"></h3>
                    <p id="messageText" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="hideMessageModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_BASE_URL = '<?= BASE_URL ?>admin/fetch/manageZzimbaCreditSettings.php';
    let settings = [];
    let currentSettingsTab = 'sms';

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function generateSettingKey(name) {
        let key = name.toLowerCase();
        key = key.replace(/[^a-z0-9\s]/g, '');
        key = key.replace(/\s+/g, '_');
        return key;
    }

    function updateSettingKey() {
        const name = document.getElementById('settingName').value;
        const keyPreview = document.getElementById('settingKeyPreview');
        if (name.trim()) {
            keyPreview.textContent = generateSettingKey(name);
        } else {
            keyPreview.textContent = 'setting_key_will_appear_here';
        }
    }

    function getSettingTypeBadge(type) {
        if (type === 'flat') {
            return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-coins mr-1"></i>Flat</span>`;
        } else {
            return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-percentage mr-1"></i>Percentage</span>`;
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
            'users': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"><i class="fas fa-user mr-1"></i>Users</span>',
            'vendors': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800"><i class="fas fa-store mr-1"></i>Vendors</span>',
            'all': '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"><i class="fas fa-users mr-1"></i>All</span>'
        };
        return badges[applicableTo] || '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function formatValue(type, value) {
        if (type === 'percentage') {
            return `${value}%`;
        } else {
            return `Sh. ${formatCurrency(value)}`;
        }
    }

    async function loadSettings(category = '', search = '') {
        try {
            const params = new URLSearchParams({ action: 'getSettings' });
            if (category) params.append('category', category);
            if (search) params.append('search', search);

            const response = await fetch(`${API_BASE_URL}?${params}`);
            const data = await response.json();

            if (data.success) {
                settings = data.settings;
                renderFilteredSettings();
                checkAllCategoriesConfiguration();
            } else {
                showMessage('error', 'Error', data.message || 'Failed to load settings');
            }
        } catch (error) {
            console.error('Error loading settings:', error);
            showMessage('error', 'Error', 'Failed to load settings');
        }
    }

    function checkCategoryConfiguration(category) {
        const categorySettings = settings.filter(s => s.category === category && s.status === 'active');

        const settingsByKey = {};
        categorySettings.forEach(setting => {
            const key = setting.setting_key;
            if (!settingsByKey[key]) {
                settingsByKey[key] = [];
            }
            settingsByKey[key].push(setting.applicable_to);
        });

        let hasIncompleteKey = false;
        for (const [key, applicableToList] of Object.entries(settingsByKey)) {
            const hasAll = applicableToList.includes('all');
            const hasUsers = applicableToList.includes('users');
            const hasVendors = applicableToList.includes('vendors');

            if (!hasAll && (!hasUsers || !hasVendors)) {
                hasIncompleteKey = true;
                break;
            }
        }

        const warningElement = document.getElementById('category-warning');
        const warningMessage = document.getElementById('warning-message');
        const tabWarning = document.getElementById(`${category}-warning`);
        const mobileTabWarning = document.getElementById(`mobile-${category}-warning`);
        const mobileCurrentWarning = document.getElementById('mobile-current-warning');

        if (!hasIncompleteKey) {
            if (currentSettingsTab === category) {
                warningElement.classList.add('hidden');
                mobileCurrentWarning.classList.add('hidden');
            }
            tabWarning.classList.add('hidden');
            mobileTabWarning.classList.add('hidden');
            return true;
        } else {
            if (currentSettingsTab === category) {
                warningElement.classList.remove('hidden');
                mobileCurrentWarning.classList.remove('hidden');
                warningMessage.textContent = 'Configuration incomplete. Some setting keys do not have proper coverage for all user groups.';
            }
            tabWarning.classList.remove('hidden');
            mobileTabWarning.classList.remove('hidden');
            return false;
        }
    }

    function checkAllCategoriesConfiguration() {
        const categories = ['sms', 'bonus', 'access', 'commission', 'transfer', 'withdrawal', 'subscription'];
        categories.forEach(category => {
            checkCategoryConfiguration(category);
        });
    }

    function updateTabHeights() {
        const desktopNav = document.getElementById('desktop-nav');
        const settingsContainer = document.getElementById('settings-container');

        if (desktopNav && settingsContainer && window.innerWidth >= 1024) {
            const settingsHeight = settingsContainer.offsetHeight;
            desktopNav.style.height = settingsHeight + 'px';
        } else if (desktopNav) {
            desktopNav.style.height = 'auto';
        }
    }

    function switchSettingsTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            btn.classList.add('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
        });

        const tabId = `${tabName}-tab`;
        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.remove('text-gray-600', 'hover:bg-gray-50', 'hover:text-gray-900');
            activeTab.classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
        }

        currentSettingsTab = tabName;
        renderFilteredSettings();
        checkCategoryConfiguration(tabName);

        const tabLabels = {
            'sms': { label: 'SMS Settings', icon: 'fas fa-sms' },
            'bonus': { label: 'Welcome Bonus', icon: 'fas fa-gift' },
            'access': { label: 'Access Charges', icon: 'fas fa-eye' },
            'commission': { label: 'Commission', icon: 'fas fa-percentage' },
            'transfer': { label: 'Transfer', icon: 'fas fa-exchange-alt' },
            'withdrawal': { label: 'Withdrawal', icon: 'fas fa-money-bill-wave' },
            'subscription': { label: 'Subscription', icon: 'fas fa-calendar-alt' }
        };
        const tabInfo = tabLabels[tabName] || tabLabels['sms'];
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);

        setTimeout(updateTabHeights, 100);
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

    function renderFilteredSettings() {
        let filteredSettings = settings.filter(s => s.category === currentSettingsTab);

        const query = document.getElementById('searchSettings').value.trim().toLowerCase();
        if (query) {
            filteredSettings = filteredSettings.filter(s =>
                s.setting_name.toLowerCase().includes(query) ||
                s.description.toLowerCase().includes(query) ||
                s.setting_key.toLowerCase().includes(query)
            );
        }

        renderSettingsGrid(filteredSettings);
    }

    function renderSettingsGrid(list) {
        const grid = document.getElementById('settings-grid');
        grid.innerHTML = '';

        if (list.length === 0) {
            grid.innerHTML = `
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-cog text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No settings found</h3>
                <p class="text-gray-500 mb-6">Create a new setting or adjust your search filters</p>
                <button onclick="showCreateSettingForm()" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Add Setting</button>
            </div>
        `;
            return;
        }

        const gridHtml = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            ${list.map(setting => `
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">${setting.setting_name}</h3>
                            <p class="text-sm text-gray-600 mb-2">${setting.description}</p>
                            <div class="text-xs text-gray-500 font-mono bg-gray-200 px-2 py-1 rounded">${setting.setting_key}</div>
                        </div>
                        ${getStatusBadge(setting.status)}
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Value:</span>
                            <span class="font-semibold text-lg text-gray-900">${formatValue(setting.setting_type, setting.setting_value)}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Type:</span>
                            ${getSettingTypeBadge(setting.setting_type)}
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Applies to:</span>
                            ${getApplicableToBadge(setting.applicable_to)}
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200">
                        <button onclick="editSetting('${setting.id}')" class="flex-1 px-3 py-2 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <button onclick="toggleSettingStatus('${setting.id}')" class="flex-1 px-3 py-2 text-sm ${setting.status === 'active' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200'} rounded-lg transition-colors font-medium">
                            <i class="fas ${setting.status === 'active' ? 'fa-pause' : 'fa-play'} mr-1"></i>${setting.status === 'active' ? 'Disable' : 'Enable'}
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

        grid.innerHTML = gridHtml;
        setTimeout(updateTabHeights, 100);
    }

    function showCreateSettingForm() {
        const modal = document.getElementById('createSettingModal');
        const form = document.getElementById('createSettingForm');
        form.reset();

        document.getElementById('settingCategory').value = currentSettingsTab;
        document.getElementById('settingKeyPreview').textContent = 'setting_key_will_appear_here';

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideCreateSettingForm() {
        document.getElementById('createSettingModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showEditSettingForm(setting) {
        document.getElementById('editSettingId').value = setting.id;
        document.getElementById('editSettingNameDisplay').textContent = setting.setting_name;
        document.getElementById('editSettingKeyDisplay').textContent = setting.setting_key;
        document.getElementById('editSettingDescriptionDisplay').textContent = setting.description;
        document.getElementById('editSettingType').value = setting.setting_type;
        document.getElementById('editSettingValue').value = setting.setting_value;
        document.getElementById('editApplicableTo').value = setting.applicable_to;

        toggleEditValueInput();

        const modal = document.getElementById('editSettingModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditSettingForm() {
        document.getElementById('editSettingModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showMessage(type, title, message) {
        const modal = document.getElementById('messageModal');
        const icon = document.getElementById('messageIcon');
        const iconClass = document.getElementById('messageIconClass');
        const titleEl = document.getElementById('messageTitle');
        const textEl = document.getElementById('messageText');

        if (type === 'success') {
            icon.className = 'w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-check text-green-600 text-xl';
        } else if (type === 'error') {
            icon.className = 'w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-exclamation-triangle text-red-600 text-xl';
        } else {
            icon.className = 'w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center';
            iconClass.className = 'fas fa-info-circle text-blue-600 text-xl';
        }

        titleEl.textContent = title;
        textEl.textContent = message;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function toggleValueInput() {
        const settingType = document.getElementById('settingType').value;
        const valueLabel = document.getElementById('valueLabel');
        const valueSymbol = document.getElementById('valueSymbol');

        if (settingType === 'percentage') {
            valueLabel.textContent = 'Percentage';
            valueSymbol.textContent = '%';
        } else {
            valueLabel.textContent = 'Amount';
            valueSymbol.textContent = 'Sh.';
        }
    }

    function toggleEditValueInput() {
        const settingType = document.getElementById('editSettingType').value;
        const valueLabel = document.getElementById('editValueLabel');
        const valueSymbol = document.getElementById('editValueSymbol');

        if (settingType === 'percentage') {
            valueLabel.textContent = 'Percentage';
            valueSymbol.textContent = '%';
        } else {
            valueLabel.textContent = 'Amount';
            valueSymbol.textContent = 'Sh.';
        }
    }

    async function createSetting() {
        const name = document.getElementById('settingName').value.trim();
        const description = document.getElementById('settingDescription').value.trim();
        const category = document.getElementById('settingCategory').value;
        const type = document.getElementById('settingType').value;
        const value = parseFloat(document.getElementById('settingValue').value);
        const applicableTo = document.getElementById('applicableTo').value;

        if (!name || !category || !type || !value || !applicableTo) {
            showMessage('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}?action=createSetting`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    setting_name: name,
                    description: description,
                    category: category,
                    setting_type: type,
                    setting_value: value,
                    applicable_to: applicableTo
                })
            });

            const data = await response.json();

            if (data.success) {
                hideCreateSettingForm();
                await loadSettings();
                showMessage('success', 'Setting Created', data.message);
            } else {
                showMessage('error', 'Error', data.message);
            }
        } catch (error) {
            console.error('Error creating setting:', error);
            showMessage('error', 'Error', 'Failed to create setting');
        }
    }

    function editSetting(id) {
        const setting = settings.find(s => s.id === id);
        if (!setting) return;

        showEditSettingForm(setting);
    }

    async function updateSetting() {
        const id = document.getElementById('editSettingId').value;
        const type = document.getElementById('editSettingType').value;
        const value = parseFloat(document.getElementById('editSettingValue').value);
        const applicableTo = document.getElementById('editApplicableTo').value;

        if (!type || !value || !applicableTo) {
            showMessage('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }

        try {
            const response = await fetch(`${API_BASE_URL}?action=updateSetting`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    setting_type: type,
                    setting_value: value,
                    applicable_to: applicableTo
                })
            });

            const data = await response.json();

            if (data.success) {
                hideEditSettingForm();
                await loadSettings();
                showMessage('success', 'Setting Updated', data.message);
            } else {
                showMessage('error', 'Error', data.message);
            }
        } catch (error) {
            console.error('Error updating setting:', error);
            showMessage('error', 'Error', 'Failed to update setting');
        }
    }

    async function toggleSettingStatus(id) {
        const setting = settings.find(s => s.id === id);
        if (!setting) return;

        const newStatus = setting.status === 'active' ? 'inactive' : 'active';

        try {
            const response = await fetch(`${API_BASE_URL}?action=updateSettingStatus`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: newStatus
                })
            });

            const data = await response.json();

            if (data.success) {
                await loadSettings();
                showMessage('success', 'Status Updated', `Setting has been ${newStatus === 'active' ? 'enabled' : 'disabled'}`);
            } else {
                showMessage('error', 'Error', data.message);
            }
        } catch (error) {
            console.error('Error updating setting status:', error);
            showMessage('error', 'Error', 'Failed to update setting status');
        }
    }

    function filterSettings() {
        renderFilteredSettings();
    }

    document.addEventListener('click', function (event) {
        const mobileDropdown = document.getElementById('mobile-tab-dropdown');
        const mobileToggle = document.getElementById('mobile-tab-toggle');

        if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
            mobileDropdown.classList.add('hidden');
            document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        await loadSettings();
        switchSettingsTab('sms');

        document.getElementById('create-setting-btn').addEventListener('click', showCreateSettingForm);
        document.getElementById('submitSettingForm').addEventListener('click', createSetting);
        document.getElementById('updateSettingForm').addEventListener('click', updateSetting);
        document.getElementById('searchSettings').addEventListener('input', filterSettings);
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchSettingsTab(tab);
                toggleMobileTabDropdown();
            });
        });

        window.addEventListener('resize', updateTabHeights);
        setTimeout(updateTabHeights, 500);
    });

    window.switchSettingsTab = switchSettingsTab;
    window.showCreateSettingForm = showCreateSettingForm;
    window.hideCreateSettingForm = hideCreateSettingForm;
    window.hideEditSettingForm = hideEditSettingForm;
    window.hideMessageModal = hideMessageModal;
    window.toggleValueInput = toggleValueInput;
    window.toggleEditValueInput = toggleEditValueInput;
    window.editSetting = editSetting;
    window.toggleSettingStatus = toggleSettingStatus;
    window.updateSettingKey = updateSettingKey;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>