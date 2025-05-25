<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Stores';
$activeNav = 'zzimba-stores';

ob_start();
?>

<div class="space-y-6">
    <div class="content-section">
        <div class="content-header p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-secondary">My Zzimba Stores</h1>
                    <p class="text-sm text-gray-text mt-2">
                        Manage your vendor profiles and store listings
                    </p>
                </div>
                <button id="createStoreBtn"
                    class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2 justify-center"
                    onclick="openStoreModal('create')">
                    <i class="fas fa-plus"></i>
                    <span>Create New Store</span>
                </button>
            </div>
        </div>
    </div>

    <div id="pending-invitations-section" class="content-section hidden">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-secondary">Pending Store Manager Invitations</h2>
                <p class="text-sm text-gray-text mt-1">Review and respond to invitations to manage stores</p>
            </div>

            <div id="pending-invitations-container">
                <div class="flex justify-center items-center py-6">
                    <div class="w-8 h-8 border-4 border-user-primary border-t-transparent rounded-full animate-spin">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-secondary">All Store Profiles</h2>
            </div>

            <div id="all-stores-container">
                <div class="flex justify-center items-center py-12">
                    <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="storeModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="closeStoreModal()"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="storeModalTitle" class="text-lg font-semibold text-secondary">
                </h3>
                <button onclick="closeStoreModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div id="storeStep1Indicator"
                        class="step-indicator active flex items-center justify-center w-8 h-8 rounded-full bg-user-primary text-white font-medium">
                        1</div>
                    <div class="w-12 h-1 bg-gray-200" id="storeStep1to2Line"></div>
                    <div id="storeStep2Indicator"
                        class="step-indicator flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 font-medium">
                        2</div>
                    <div class="w-12 h-1 bg-gray-200" id="storeStep2to3Line"></div>
                    <div id="storeStep3Indicator"
                        class="step-indicator flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 font-medium">
                        3</div>
                </div>
            </div>

            <form id="storeForm">
                <input type="hidden" id="storeMode" value="">
                <input type="hidden" id="storeId" value="">

                <div id="storeStep1" class="step-content">
                    <h4 class="text-center font-medium text-secondary mb-4">Basic Store Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="storeBusinessName" class="block text-sm font-medium text-gray-700 mb-1">Business
                                Name *</label>
                            <input type="text" id="storeBusinessName" placeholder="Enter business name"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="storeBusinessEmail"
                                class="block text-sm font-medium text-gray-700 mb-1">Business Email *</label>
                            <input type="email" id="storeBusinessEmail" placeholder="Enter business email"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="storeContactNumber" class="block text-sm font-medium text-gray-700 mb-1">Main
                                Contact Number *</label>
                            <input type="tel" id="storeContactNumber" placeholder="Enter contact number"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label for="storeNatureOfBusiness"
                                class="block text-sm font-medium text-gray-700 mb-1">Nature of Business *</label>
                            <select id="storeNatureOfBusiness"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                <option value="">Select Nature of Business</option>
                            </select>
                        </div>
                        <!-- Contact Person Name Field Only -->
                        <div class="sm:col-span-2">
                            <label for="storeContactPersonName"
                                class="block text-sm font-medium text-gray-700 mb-1">Contact Person Name *</label>
                            <input type="text" id="storeContactPersonName" placeholder="Enter contact person name"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="button" id="storeStep1NextBtn"
                                class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                                NEXT
                            </button>
                        </div>
                    </div>
                </div>

                <div id="storeStep2" class="step-content hidden">
                    <h4 class="text-center font-medium text-secondary mb-4">Store Location</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Location on Map
                                    *</label>
                                <div id="storeMapContainer" class="w-full h-64 rounded-lg border border-gray-200 mb-2">
                                </div>
                                <p class="text-xs text-gray-500">Click within the selected region to drop a pin</p>
                            </div>

                            <div class="flex space-x-2 mb-4">
                                <button id="storeLocateMeBtn" type="button"
                                    class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition">
                                    Find My Location
                                </button>
                                <select id="storeMapStyle" class="text-sm border rounded-md px-2 py-1">
                                    <option value="osm">OpenStreetMap</option>
                                    <option value="satellite">Satellite</option>
                                    <option value="terrain">Terrain</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="storeLatitude"
                                        class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                                    <input type="text" id="storeLatitude" readonly
                                        class="w-full h-10 px-3 rounded-lg border border-gray-200 bg-gray-50">
                                </div>
                                <div>
                                    <label for="storeLongitude"
                                        class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                                    <input type="text" id="storeLongitude" readonly
                                        class="w-full h-10 px-3 rounded-lg border border-gray-200 bg-gray-50">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="space-y-4">
                                <div>
                                    <label for="storeLevel1"
                                        class="block text-sm font-medium text-gray-700 mb-1">Region/Province *</label>
                                    <div class="relative">
                                        <select id="storeLevel1"
                                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                            <option value="">Select Region/Province</option>
                                        </select>
                                        <span id="storeLoading1"
                                            class="hidden absolute right-2 top-2 text-sm text-gray-500">
                                            Loading...
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="storeLevel2"
                                        class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                    <div class="relative">
                                        <select id="storeLevel2" disabled
                                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                            <option value="">Select District</option>
                                        </select>
                                        <span id="storeLoading2"
                                            class="hidden absolute right-2 top-2 text-sm text-gray-500">
                                            Loading...
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="storeLevel3"
                                        class="block text-sm font-medium text-gray-700 mb-1">Sub-county</label>
                                    <div class="relative">
                                        <select id="storeLevel3" disabled
                                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                            <option value="">Select Sub-county</option>
                                        </select>
                                        <span id="storeLoading3"
                                            class="hidden absolute right-2 top-2 text-sm text-gray-500">
                                            Loading...
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="storeLevel4"
                                        class="block text-sm font-medium text-gray-700 mb-1">Parish/Ward</label>
                                    <div class="relative">
                                        <select id="storeLevel4" disabled
                                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                            <option value="">Select Parish/Ward</option>
                                        </select>
                                        <span id="storeLoading4"
                                            class="hidden absolute right-2 top-2 text-sm text-gray-500">
                                            Loading...
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label for="storeAddress"
                                        class="block text-sm font-medium text-gray-700 mb-1">Physical Address *</label>
                                    <input type="text" id="storeAddress" placeholder="Enter physical address" readonly
                                        class="w-full h-10 px-3 rounded-lg border border-gray-200 bg-gray-50">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" id="storeStep2BackBtn"
                            class="w-24 h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            BACK
                        </button>
                        <button type="button" id="storeStep2NextBtn"
                            class="w-24 h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                            NEXT
                        </button>
                    </div>
                </div>

                <div id="storeStep3" class="step-content hidden">
                    <h4 class="text-center font-medium text-secondary mb-4">Store Details</h4>

                    <div class="space-y-6">
                        <div>
                            <label for="storeDescription" class="block text-sm font-medium text-gray-700 mb-1">Store
                                Description</label>
                            <textarea id="storeDescription" rows="4" placeholder="Brief description of your store"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Logo</label>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <i id="storeLogoPlaceholder" class="fas fa-store text-gray-400 text-xl"></i>
                                    <img id="storeLogoPreview" class="w-full h-full object-cover rounded-lg hidden"
                                        src="#" alt="Logo preview">
                                </div>
                                <label for="storeLogo"
                                    class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    Upload Logo
                                </label>
                                <input type="file" id="storeLogo" accept="image/*" class="hidden">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Recommended size: 512×512 pixels. Max 2MB.</p>
                        </div>

                        <div>
                            <label for="storeWebsite" class="block text-sm font-medium text-gray-700 mb-1">Website
                                (Optional)</label>
                            <input type="url" id="storeWebsite" placeholder="https://example.com"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>

                        <div>
                            <label for="storeSocialMedia" class="block text-sm font-medium text-gray-700 mb-1">Social
                                Media (Optional)</label>
                            <input type="text" id="storeSocialMedia" placeholder="Facebook, Instagram, etc."
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <button type="button" id="storeStep3BackBtn"
                            class="w-24 h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            BACK
                        </button>
                        <button type="button" id="storeStep3FinishBtn"
                            class="w-24 h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                            SAVE
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="invitationResponseModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="closeInvitationResponseModal()"></div>
    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 id="invitationResponseTitle" class="text-lg font-semibold text-secondary">Confirm Response</h3>
                <button onclick="closeInvitationResponseModal()" class="text-gray-400 hover:text-gray-500"
                    id="invitationResponseCloseBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="invitationResponseContent" class="py-4">
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" id="invitationResponseCancelBtn"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                    onclick="closeInvitationResponseModal()">
                    Cancel
                </button>
                <button type="button" id="invitationResponseConfirmBtn"
                    class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Processing...</p>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-pip@1.1.0/leaflet-pip.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" />

<style>
    .location-icon {
        background-color: #ef4444;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .pulse {
        animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }

    .store-card {
        transition: all 0.2s ease-in-out;
    }

    .store-card:hover {
        transform: translateY(-2px);
    }

    .store-type-badge {
        position: absolute;
        top: 0;
        right: 0;
        padding: 2px 8px;
        font-size: 0.7rem;
        border-radius: 0 0.375rem 0 0.375rem;
    }

    .store-type-owned {
        background-color: rgba(220, 38, 38, 0.1);
        color: #dc2626;
    }

    .store-type-managed {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .invitation-card {
        transition: all 0.2s ease-in-out;
    }

    .invitation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>

<script>
    const STORE_API_BASE = BASE_URL + 'account/fetch/manageZzimbaStores.php';
    let storeMap = null;
    let storeMarker = null;
    let storeGeoJSONLayer = null;
    let storeCurrentGeoJSON = null;
    let storeBaseLayers = {};
    let storePhoneInput = null;
    let storeContactPhoneInput = null;
    let allStores = [];
    let pendingInvitations = [];
    let currentFilter = 'owned';
    let currentInvitationAction = null;
    let currentInvitationId = null;
    let natureOfBusinessOptions = [];

    $(document).ready(function () {
        loadAllStores();
        loadPendingInvitations();
        loadNatureOfBusiness();

        initializePhoneInput();

        $('#storeStep1NextBtn').click(() => goToStep2());
        $('#storeStep2BackBtn').click(() => backToStep1());
        $('#storeStep2NextBtn').click(() => goToStep3());
        $('#storeStep3BackBtn').click(() => backToStep2());
        $('#storeStep3FinishBtn').click(() => saveStoreData());

        $('#storeLogo').change(function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#storeLogoPreview').attr('src', e.target.result).removeClass('hidden');
                    $('#storeLogoPlaceholder').addClass('hidden');
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        $('#invitationResponseConfirmBtn').click(function () {
            if (currentInvitationAction && currentInvitationId) {
                processInvitationResponse(currentInvitationAction, currentInvitationId);
            }
        });
    });

    function loadNatureOfBusiness() {
        $.ajax({
            url: STORE_API_BASE + '?action=getNatureOfBusiness',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    natureOfBusinessOptions = response.natureOfBusiness || [];
                    const dropdown = $('#storeNatureOfBusiness');
                    dropdown.html('<option value="">Select Nature of Business</option>');
                    natureOfBusinessOptions.forEach(item => {
                        dropdown.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                }
            }
        });
    }

    function showLoading() {
        $('#loadingOverlay').removeClass('hidden');
    }

    function hideLoading() {
        $('#loadingOverlay').addClass('hidden');
    }

    function showErrorNotification(message) {
        let notification = document.getElementById('errorNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'errorNotification';
            notification.className =
                'fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="errorMessage"></span>
                </div>
            `;
            document.body.appendChild(notification);
        }
        document.getElementById('errorMessage').textContent = message;
        notification.classList.remove('hidden');
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showSuccessNotification(message) {
        let notification = document.getElementById('successNotification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'successNotification';
            notification.className =
                'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span id="successMessage"></span>
                </div>
            `;
            document.body.appendChild(notification);
        }
        document.getElementById('successMessage').textContent = message;
        notification.classList.remove('hidden');
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function loadAllStores() {
        showLoading();

        $.ajax({
            url: STORE_API_BASE + '?action=getOwnedStores',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const ownedStores = (response.stores || []).map(s => ({ ...s, type: 'owned' }));

                    $.ajax({
                        url: STORE_API_BASE + '?action=getManagedStores',
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            hideLoading();
                            if (response.success) {
                                const managedStores = (response.stores || []).map(s => ({ ...s, type: 'managed' }));

                                allStores = [...ownedStores, ...managedStores];
                                renderStores(allStores);
                            } else {
                                allStores = ownedStores;
                                renderStores(allStores);
                                showErrorNotification(response.error || 'Failed to load managed stores');
                            }
                        },
                        error: function () {
                            hideLoading();
                            allStores = ownedStores;
                            renderStores(allStores);
                            showErrorNotification('Failed to load managed stores. Please try again.');
                        }
                    });
                } else {
                    hideLoading();
                    showErrorNotification(response.error || 'Failed to load stores');
                }
            },
            error: function () {
                hideLoading();
                showErrorNotification('Failed to load stores. Please try again.');
            }
        });
    }

    function loadPendingInvitations() {
        $.ajax({
            url: STORE_API_BASE + '?action=getPendingInvitations',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    pendingInvitations = response.invitations || [];
                    if (pendingInvitations.length > 0) {
                        $('#pending-invitations-section').removeClass('hidden');
                        renderPendingInvitations(pendingInvitations);
                    } else {
                        $('#pending-invitations-section').addClass('hidden');
                    }
                } else {
                    showErrorNotification(response.error || 'Failed to load invitations');
                }
            },
            error: function () {
                showErrorNotification('Failed to load invitations. Please try again.');
            }
        });
    }

    function renderPendingInvitations(invitations) {
        const container = $('#pending-invitations-container');

        if (!invitations || invitations.length === 0) {
            container.html(`
                <div class="bg-gray-50 rounded-lg p-6 text-center">
                    <p class="text-gray-500">No pending invitations</p>
                </div>
            `);
            return;
        }

        let html = '<div class="grid grid-cols-1 gap-4">';

        invitations.forEach(invitation => {
            const logoUrl = invitation.logo_url ?
                BASE_URL + invitation.logo_url :
                `https://placehold.co/100x100/f0f0f0/808080?text=${invitation.store_name.substring(0, 2)}`;

            html += `
                <div class="invitation-card bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <!-- Logo -->
                            <div class="w-16 h-16 bg-gray-50 flex-shrink-0 rounded-lg flex items-center justify-center mx-auto sm:mx-0">
                                <img src="${logoUrl}" alt="${escapeHtml(invitation.store_name)}" class="w-12 h-12 object-cover rounded">
                            </div>

                            <!-- Content -->
                            <div class="flex-grow text-center sm:text-left">
                                <h3 class="font-medium text-lg text-secondary">${escapeHtml(invitation.store_name)}</h3>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Role:</span> ${escapeHtml(invitation.role_display)}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium">Invited by:</span> ${escapeHtml(invitation.owner_name)}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Invited ${formatTimeAgo(new Date(invitation.created_at))}
                                </p>
                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col sm:flex-row gap-2 sm:justify-end sm:flex-shrink-0 w-full sm:w-auto">
                                <button 
                                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                                    onclick="confirmInvitationResponse('approve', '${invitation.manager_id}', '${escapeHtml(invitation.store_name)}')">
                                    Approve
                                </button>
                                <button 
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
                                    onclick="confirmInvitationResponse('decline', '${invitation.manager_id}', '${escapeHtml(invitation.store_name)}')">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.html(html);
    }

    function confirmInvitationResponse(action, managerId, storeName) {
        currentInvitationAction = action;
        currentInvitationId = managerId;

        const title = action === 'approve' ? 'Approve Invitation' : 'Decline Invitation';
        const content = action === 'approve' ?
            `Are you sure you want to approve the invitation to manage <strong>${escapeHtml(storeName)}</strong>? You will gain access to manage this store according to your assigned role.` :
            `Are you sure you want to decline the invitation to manage <strong>${escapeHtml(storeName)}</strong>? This action cannot be undone, and the store owner will be notified.`;

        const btnClass = action === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700';
        const btnText = action === 'approve' ? 'Approve' : 'Decline';

        $('#invitationResponseTitle').text(title);
        $('#invitationResponseContent').html(`<p class="text-gray-700">${content}</p>`);
        $('#invitationResponseConfirmBtn').removeClass('bg-green-600 bg-red-600 hover:bg-green-700 hover:bg-red-700')
            .addClass(btnClass)
            .text(btnText);

        $('#invitationResponseModal').removeClass('hidden');
    }

    function processInvitationResponse(action, managerId) {
        const endpoint = action === 'approve' ? 'approveManagerInvitation' : 'declineManagerInvitation';

        $('#invitationResponseConfirmBtn, #invitationResponseCancelBtn, #invitationResponseCloseBtn').prop('disabled', true);
        $('#invitationResponseConfirmBtn').html('<i class="fas fa-spinner fa-spin mr-2"></i>' + (action === 'approve' ? 'Approving...' : 'Declining...'));

        $.ajax({
            url: STORE_API_BASE + '?action=' + endpoint,
            type: 'POST',
            data: { managerId: managerId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    closeInvitationResponseModal();
                    showSuccessNotification(response.message || (action === 'approve' ? 'Invitation approved successfully' : 'Invitation declined successfully'));

                    loadPendingInvitations();
                    if (action === 'approve') {
                        loadAllStores();
                    }
                } else {
                    showErrorNotification(response.error || 'Failed to process invitation');
                    enableInvitationResponseButtons();
                }
            },
            error: function () {
                showErrorNotification('Failed to process invitation. Please try again.');
                enableInvitationResponseButtons();
            }
        });
    }

    function enableInvitationResponseButtons() {
        $('#invitationResponseConfirmBtn, #invitationResponseCancelBtn, #invitationResponseCloseBtn').prop('disabled', false);
        $('#invitationResponseConfirmBtn').text(currentInvitationAction === 'approve' ? 'Approve' : 'Decline');
    }

    function closeInvitationResponseModal() {
        $('#invitationResponseModal').addClass('hidden');
        currentInvitationAction = null;
        currentInvitationId = null;
        enableInvitationResponseButtons();
    }

    function filterStores() {
        renderStores(allStores);
    }

    function renderStores(stores) {
        const container = $('#all-stores-container');

        /* ------------- empty-state block ------------- */
        if (!stores || stores.length === 0) {
            container.html(`
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-store text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-secondary mb-2">No Stores Found</h3>
                <p class="text-sm text-gray-text mb-4">
                    ${currentFilter === 'owned'
                    ? "You haven’t created any stores yet."
                    : currentFilter === 'managed'
                        ? "You don’t manage any stores yet."
                        : "No stores found."
                }
                </p>
                ${currentFilter === 'owned' || currentFilter === 'all'
                    ? `
                            <button
                                id="createFirstStoreBtn"
                                class="h-10 px-6 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors"
                                onclick="openStoreModal('create')"
                            >
                                Create Your First Store
                            </button>
                        `
                    : ''
                }
            </div>
        `);
            return;
        }

        /* ------------- cards grid ------------- */
        let html = '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">';
        stores.forEach((store) => {
            const statusBadge = getStatusBadge(store.status);
            const logoUrl = store.logo_url
                ? BASE_URL + store.logo_url
                : `https://placehold.co/100x100/f0f0f0/808080?text=${store.name.substring(0, 2)}`;

            const storeTypeBadge =
                store.type === 'owned'
                    ? '<span class="store-type-badge store-type-owned">Owned</span>'
                    : '<span class="store-type-badge store-type-managed">Managed</span>';

            html += `
            <div class="store-card bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 relative">
                ${storeTypeBadge}
                <div class="flex flex-col sm:flex-row">
                    <!-- logo -->
                    <div class="w-full sm:w-32 h-32 bg-gray-50 flex items-center justify-center flex-shrink-0">
                        <img src="${logoUrl}" alt="${escapeHtml(store.name)}" class="w-20 h-20 object-cover rounded-lg">
                    </div>

                    <!-- content -->
                    <div class="p-4 sm:p-6 flex-grow min-w-0">
                        <!-- top row : name & status -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 min-w-0">
                            <h3
                                class="font-semibold text-secondary truncate"
                                title="${escapeHtml(store.name)}"
                            >
                                ${escapeHtml(store.name)}
                            </h3>
                            <div class="inline-flex items-center flex-shrink-0">
                                ${statusBadge}
                            </div>
                        </div>

                        <!-- location (2-line clamp) -->
                        <p
                            class="text-sm text-gray-text mb-3 overflow-hidden line-clamp-2"
                            title="${escapeHtml(store.location)}"
                        >
                            <i class="fas fa-map-marker-alt mr-1 text-user-primary"></i>
                            ${escapeHtml(store.location)}
                        </p>

                        <!-- stats -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-text">Products</p>
                                <p class="font-medium">${store.product_count || 0}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-text">Categories</p>
                                <p class="font-medium">${store.categories ? store.categories.length : 0}</p>
                            </div>
                        </div>

                        <!-- actions -->
                        <div class="flex justify-end">
                            <button
                                onclick="openStoreModal('edit','${store.uuid_id}')"
                                class="h-8 px-3 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors flex items-center gap-1 text-sm mr-2"
                            >
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </button>
                            <button onclick="redirectToManageStore('${store.uuid_id}')"
                                class="h-8 px-3 bg-user-primary text-white rounded hover:bg-user-primary/90 transition-colors flex items-center gap-1 text-sm">
                                <i class="fas fa-cog"></i>
                                <span>Manage</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        });

        html += '</div>';
        container.html(html);
    }

    function redirectToManageStore(storeUuid) {
        showLoading();
        $.ajax({
            url: BASE_URL + 'account/fetch/initVendorSession.php',
            type: 'POST',
            data: { store_uuid: storeUuid },
            dataType: 'json',
            success: function (response) {
                hideLoading();
                if (response.success && response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    showErrorNotification(response.message || 'Failed to initiate store session');
                }
            },
            error: function () {
                hideLoading();
                showErrorNotification('Server error occurred. Please try again.');
            }
        });
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'active':
                return `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                        Active
                    </span>
                `;
            case 'pending':
                return `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-yellow-500"></span>
                        Pending
                    </span>
                `;
            case 'inactive':
                return `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                        Inactive
                    </span>
                `;
            case 'suspended':
                return `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-500"></span>
                        Suspended
                    </span>
                `;
            default:
                return `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-500"></span>
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>
                `;
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) {
            return 'just now';
        }

        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return diffInMinutes + (diffInMinutes === 1 ? ' minute ago' : ' minutes ago');
        }

        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return diffInHours + (diffInHours === 1 ? ' hour ago' : ' hours ago');
        }

        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return diffInDays + (diffInDays === 1 ? ' day ago' : ' days ago');
        }

        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return diffInMonths + (diffInMonths === 1 ? ' month ago' : ' months ago');
        }

        const diffInYears = Math.floor(diffInMonths / 12);
        return diffInYears + (diffInYears === 1 ? ' year ago' : ' years ago');
    }

    function openStoreModal(mode, storeId = null) {
        $('#storeModal').removeClass('hidden');
        $('#storeMode').val(mode);

        if (mode === 'create') {
            $('#storeModalTitle').text('Create New Store');
            resetStoreForm();
        } else {
            $('#storeModalTitle').text('Edit Store');
            resetStoreForm();
            populateStoreForm(storeId);
        }
    }

    function closeStoreModal() {
        $('#storeModal').addClass('hidden');
    }

    function goToStep2() {
        const name = $('#storeBusinessName').val();
        const email = $('#storeBusinessEmail').val();
        const phone = storePhoneInput.getNumber();
        const contactName = $('#storeContactPersonName').val();
        const nature = $('#storeNatureOfBusiness').val();

        if (!name || !email || !phone || !nature || !contactName) {
            showErrorNotification('Please fill in all required fields');
            return;
        }

        if (!storePhoneInput.isValidNumber()) {
            showErrorNotification('Please enter a valid business phone number');
            return;
        }

        $('#storeStep1').addClass('hidden');
        $('#storeStep2').removeClass('hidden');
        $('#storeStep1Indicator')
            .addClass('bg-green-500')
            .removeClass('bg-user-primary')
            .html('<i class="fas fa-check"></i>');
        $('#storeStep2Indicator')
            .addClass('bg-user-primary text-white')
            .removeClass('bg-gray-200 text-gray-500');
        $('#storeStep1to2Line').addClass('bg-green-500').removeClass('bg-gray-200');

        setTimeout(() => {
            if (storeMap) storeMap.invalidateSize();
        }, 100);
    }

    function backToStep1() {
        $('#storeStep2').addClass('hidden');
        $('#storeStep1').removeClass('hidden');
        $('#storeStep1Indicator')
            .removeClass('bg-green-500')
            .addClass('bg-user-primary')
            .text('1');
        $('#storeStep2Indicator')
            .removeClass('bg-user-primary text-white')
            .addClass('bg-gray-200 text-gray-500')
            .text('2');
        $('#storeStep1to2Line').removeClass('bg-green-500').addClass('bg-gray-200');
    }

    function goToStep3() {
        const lat = $('#storeLatitude').val();
        const lng = $('#storeLongitude').val();
        const lvl1 = $('#storeLevel1').val();
        const lvl2 = $('#storeLevel2').val();
        const addr = $('#storeAddress').val();
        if (!lat || !lng || !lvl1 || !lvl2 || !addr) {
            showErrorNotification('Please select your location on the map and fill in all required fields');
            return;
        }
        $('#storeStep2').addClass('hidden');
        $('#storeStep3').removeClass('hidden');
        $('#storeStep2Indicator')
            .addClass('bg-green-500')
            .removeClass('bg-user-primary')
            .html('<i class="fas fa-check"></i>');
        $('#storeStep3Indicator')
            .addClass('bg-user-primary text-white')
            .removeClass('bg-gray-200 text-gray-500');
        $('#storeStep2to3Line').addClass('bg-green-500').removeClass('bg-gray-200');
    }

    function backToStep2() {
        $('#storeStep3').addClass('hidden');
        $('#storeStep2').removeClass('hidden');
        $('#storeStep2Indicator')
            .removeClass('bg-green-500')
            .addClass('bg-user-primary')
            .text('2');
        $('#storeStep3Indicator')
            .removeClass('bg-user-primary text-white')
            .addClass('bg-gray-200 text-gray-500')
            .text('3');
        $('#storeStep2to3Line').removeClass('bg-green-500').addClass('bg-gray-200');
        setTimeout(() => {
            if (storeMap) storeMap.invalidateSize();
        }, 100);
    }

    function resetStoreForm() {
        $('#storeId').val('');
        $('#storeBusinessName').val('');
        $('#storeBusinessEmail').val('');
        $('#storeContactNumber').val('');
        $('#storeContactPersonName').val('');
        $('#storeNatureOfBusiness').val('');
        $('#storeLatitude').val('');
        $('#storeLongitude').val('');
        $('#storeAddress').val('');
        $('#storeLevel1').html('<option value="">Select Region/Province</option>');
        $('#storeLevel2').html('<option value="">Select District</option>').prop('disabled', true);
        $('#storeLevel3').html('<option value="">Select Sub-county</option>').prop('disabled', true);
        $('#storeLevel4').html('<option value="">Select Parish/Ward</option>').prop('disabled', true);
        $('#storeDescription').val('');
        $('#storeWebsite').val('');
        $('#storeSocialMedia').val('');
        $('#storeLogo').val('');
        $('#storeLogoPreview').attr('src', '#').addClass('hidden');
        $('#storeLogoPlaceholder').removeClass('hidden');

        $('#storeStep1').removeClass('hidden');
        $('#storeStep2, #storeStep3').addClass('hidden');
        $('#storeStep1Indicator')
            .removeClass('bg-green-500')
            .addClass('bg-user-primary')
            .text('1');
        $('#storeStep2Indicator, #storeStep3Indicator')
            .removeClass('bg-user-primary bg-green-500 text-white')
            .addClass('bg-gray-200 text-gray-500');
        $('#storeStep2Indicator').text('2');
        $('#storeStep3Indicator').text('3');
        $('#storeStep1to2Line, #storeStep2to3Line')
            .removeClass('bg-green-500')
            .addClass('bg-gray-200');

        destroyMap();
        initStoreMap();

        loadRegions();
    }

    function populateStoreForm(storeId) {
        $('#storeId').val(storeId);
        showLoading();
        $.ajax({
            url: STORE_API_BASE + '?action=getStoreDetails&id=' + storeId,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                hideLoading();
                if (!response.success) {
                    showErrorNotification(response.error || 'Failed to load store details');
                    return;
                }
                const store = response.store;
                $('#storeId').val(store.uuid_id);
                $('#storeBusinessName').val(store.name);
                $('#storeBusinessEmail').val(store.business_email);
                storePhoneInput.setNumber(store.business_phone);

                // Set contact person name if available
                if (store.contact_person_name) {
                    $('#storeContactPersonName').val(store.contact_person_name);
                }

                $('#storeNatureOfBusiness').val(store.nature_of_business);
                $('#storeLatitude').val(store.latitude);
                $('#storeLongitude').val(store.longitude);
                $('#storeAddress').val(store.address);
                $('#storeDescription').val(store.description);
                $('#storeWebsite').val(store.website_url);
                $('#storeSocialMedia').val(store.social_media);

                if (store.logo_url) {
                    $('#storeLogoPreview')
                        .attr('src', BASE_URL + store.logo_url)
                        .removeClass('hidden');
                    $('#storeLogoPlaceholder').addClass('hidden');
                }

                destroyMap();
                initStoreMap(store.latitude, store.longitude);
                loadRegions(store.region, store.district, store.subcounty, store.parish);
            },
            error: function () {
                hideLoading();
                showErrorNotification('Failed to load store details. Please try again.');
            }
        });
    }

    function saveStoreData() {
        const mode = $('#storeMode').val();
        const storeId = $('#storeId').val();

        const formData = {
            id: storeId,
            name: $('#storeBusinessName').val(),
            business_email: $('#storeBusinessEmail').val(),
            business_phone: storePhoneInput.getNumber(),
            contact_person_name: $('#storeContactPersonName').val(),
            nature_of_business: $('#storeNatureOfBusiness').val(),
            region: $('#storeLevel1').val(),
            district: $('#storeLevel2').val(),
            subcounty: $('#storeLevel3').val(),
            parish: $('#storeLevel4').val(),
            address: $('#storeAddress').val(),
            latitude: $('#storeLatitude').val(),
            longitude: $('#storeLongitude').val(),
            description: $('#storeDescription').val(),
            website_url: $('#storeWebsite').val(),
            social_media: $('#storeSocialMedia').val()
        };

        const logoFile = $('#storeLogo')[0].files[0];

        if (logoFile) {
            const logoFormData = new FormData();
            logoFormData.append('logo', logoFile);
            showLoading();
            $.ajax({
                url: STORE_API_BASE + '?action=uploadLogo',
                type: 'POST',
                data: logoFormData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (resp) {
                    if (resp.success) {
                        formData.temp_logo_path = resp.temp_path;
                        finishSave(mode, formData);
                    } else {
                        hideLoading();
                        showErrorNotification(resp.message || 'Failed to upload logo');
                    }
                },
                error: function () {
                    hideLoading();
                    showErrorNotification('Failed to upload logo. Please try again.');
                }
            });
        } else {
            finishSave(mode, formData);
        }
    }

    function finishSave(mode, formData) {
        const actionUrl = mode === 'create' ?
            STORE_API_BASE + '?action=createStore' :
            STORE_API_BASE + '?action=updateStore';

        showLoading();
        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                hideLoading();
                if (response.success) {
                    closeStoreModal();
                    showSuccessNotification(response.message || 'Store saved successfully!');
                    resetStoreForm();
                    loadAllStores();
                } else {
                    showErrorNotification(response.error || 'Failed to save store');
                }
            },
            error: function () {
                hideLoading();
                showErrorNotification('Failed to save store. Please try again.');
            }
        });
    }

    function initStoreMap(lat = 1.3733, lng = 32.2903) {
        if (storeMap) return;
        storeMap = L.map('storeMapContainer').setView([lat, lng], 7);

        storeBaseLayers = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            satellite: L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping,' +
                    ' Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }
            ),
            terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>' +
                    ' contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style:' +
                    ' &copy; <a href="https://opentopomap.org">OpenTopoMap</a>' +
                    ' (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
            })
        };
        storeBaseLayers.osm.addTo(storeMap);

        $('#storeMapStyle').change(function () {
            const style = $(this).val();
            Object.values(storeBaseLayers).forEach(layer => {
                if (storeMap.hasLayer(layer)) {
                    storeMap.removeLayer(layer);
                }
            });
            if (storeBaseLayers[style]) {
                storeBaseLayers[style].addTo(storeMap);
            }
        });

        storeMap.on('click', function (e) {
            if (storeCurrentGeoJSON) {
                const point = {
                    type: 'Point',
                    coordinates: [e.latlng.lng, e.latlng.lat]
                };

                let isWithinBounds = false;
                for (const feature of storeCurrentGeoJSON.features) {
                    if (leafletPip.pointInLayer([e.latlng.lng, e.latlng.lat], L.geoJSON(feature), true).length > 0) {
                        isWithinBounds = true;
                        break;
                    }
                }

                if (isWithinBounds) {
                    dropStoreMarker(e.latlng);
                } else {
                    showErrorNotification('Please place the pin within the highlighted region');
                }
            } else {
                showErrorNotification('Please select a region from the dropdowns first');
            }
        });

        if (lat && lng && lat !== 1.3733 && lng !== 32.2903) {
            dropStoreMarker(L.latLng(lat, lng));
        }

        locateUser('#storeLocateMeBtn', storeMap, 'create');
    }

    function destroyMap() {
        if (storeMap) {
            storeMap.remove();
            storeMap = null;
        }
        storeMarker = null;
        storeGeoJSONLayer = null;
        storeCurrentGeoJSON = null;
        storeBaseLayers = {};
    }

    function dropStoreMarker(latlng) {
        if (storeMarker) storeMap.removeLayer(storeMarker);

        const icon = L.divIcon({
            className: 'location-icon pulse',
            iconSize: [16, 16],
            iconAnchor: [8, 8]
        });

        storeMarker = L.marker(latlng, {
            draggable: true,
            icon: icon
        }).addTo(storeMap);

        $('#storeLatitude').val(latlng.lat.toFixed(6));
        $('#storeLongitude').val(latlng.lng.toFixed(6));
        reverseGeocode(latlng.lat, latlng.lng);

        storeMarker.on('dragend', function () {
            const newPos = storeMarker.getLatLng();

            let isWithinBounds = false;
            if (storeCurrentGeoJSON) {
                for (const feature of storeCurrentGeoJSON.features) {
                    if (leafletPip.pointInLayer([newPos.lng, newPos.lat], L.geoJSON(feature), true).length > 0) {
                        isWithinBounds = true;
                        break;
                    }
                }
            }

            if (isWithinBounds || !storeCurrentGeoJSON) {
                $('#storeLatitude').val(newPos.lat.toFixed(6));
                $('#storeLongitude').val(newPos.lng.toFixed(6));
                reverseGeocode(newPos.lat, newPos.lng);
            } else {
                storeMarker.setLatLng(latlng);
                showErrorNotification('Please keep the pin within the highlighted region');
            }
        });
    }

    function locateUser(btnSelector, mapObj, usage) {
        $(btnSelector).click(function () {
            if (!navigator.geolocation) {
                showErrorNotification('Geolocation is not supported by your browser');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    mapObj.setView([lat, lng], 15);

                    let isWithinBounds = false;
                    if (storeCurrentGeoJSON) {
                        for (const feature of storeCurrentGeoJSON.features) {
                            if (leafletPip.pointInLayer([lng, lat], L.geoJSON(feature), true).length > 0) {
                                isWithinBounds = true;
                                break;
                            }
                        }

                        if (isWithinBounds) {
                            dropStoreMarker(L.latLng(lat, lng));
                        } else {
                            showErrorNotification('Your location is outside the selected region. Please select a location manually.');
                        }
                    } else {
                        dropStoreMarker(L.latLng(lat, lng));
                    }
                },
                function (error) {
                    console.error(error);
                    showErrorNotification(
                        'Unable to get your location. Please select your location manually on the map.'
                    );
                }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
            );
        });
    }

    function reverseGeocode(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
        fetch(url, {
            headers: {
                'User-Agent': 'Zzimba Online Store Location Selector'
            }
        })
            .then(r => r.json())
            .then(data => {
                if (data && data.display_name) {
                    $('#storeAddress').val(data.display_name);
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    function loadRegions(selectedRegion = null, selectedDistrict = null, selectedSubcounty = null, selectedParish = null) {
        fetch('<?= BASE_URL ?>locations/gadm41_UGA_4.json')
            .then(response => response.json())
            .then(data => {
                if (!data.features) return;
                const level1Options = {};
                data.features.forEach(f => {
                    const name = f.properties.NAME_1;
                    if (name) level1Options[name] = name;
                });
                const storeLevel1 = $('#storeLevel1');
                storeLevel1.html('<option value="">Select Region/Province</option>');
                Object.keys(level1Options)
                    .sort()
                    .forEach(r => {
                        storeLevel1.append(
                            `<option value="${r}" ${r === selectedRegion ? 'selected' : ''}>${r}</option>`
                        );
                    });

                window.ugGeoData = data;
                storeLevel1.change(function () {
                    const region = $(this).val();
                    if (region) {
                        updateDistricts(region);
                        updateMapGeoJSON({
                            1: region
                        });
                    } else {
                        resetDropdown('#storeLevel2');
                        resetDropdown('#storeLevel3');
                        resetDropdown('#storeLevel4');
                        clearGeoJSON();
                    }
                });
                $('#storeLevel2').change(function () {
                    const dist = $(this).val();
                    const reg = $('#storeLevel1').val();
                    if (dist) {
                        updateSubcounties(reg, dist);
                        updateMapGeoJSON({
                            1: reg,
                            2: dist
                        });
                    } else {
                        resetDropdown('#storeLevel3');
                        resetDropdown('#storeLevel4');
                        updateMapGeoJSON({
                            1: reg
                        });
                    }
                });
                $('#storeLevel3').change(function () {
                    const sub = $(this).val();
                    const dist = $('#storeLevel2').val();
                    const reg = $('#storeLevel1').val();
                    if (sub) {
                        updateParishes(reg, dist, sub);
                        updateMapGeoJSON({
                            1: reg,
                            2: dist,
                            3: sub
                        });
                    } else {
                        resetDropdown('#storeLevel4');
                        updateMapGeoJSON({
                            1: reg,
                            2: dist
                        });
                    }
                });
                $('#storeLevel4').change(function () {
                    const parish = $(this).val();
                    const sub = $('#storeLevel3').val();
                    const dist = $('#storeLevel2').val();
                    const reg = $('#storeLevel1').val();
                    if (parish) {
                        updateMapGeoJSON({
                            1: reg,
                            2: dist,
                            3: sub,
                            4: parish
                        });
                    } else {
                        updateMapGeoJSON({
                            1: reg,
                            2: dist,
                            3: sub
                        });
                    }
                });

                if (selectedRegion) {
                    updateDistricts(selectedRegion, selectedDistrict, selectedSubcounty, selectedParish);
                    let selections = {
                        1: selectedRegion
                    };
                    if (selectedDistrict) selections[2] = selectedDistrict;
                    if (selectedSubcounty) selections[3] = selectedSubcounty;
                    if (selectedParish) selections[4] = selectedParish;
                    updateMapGeoJSON(selections);
                }
            })
            .catch(err => {
                console.error(err);
                showErrorNotification('Failed to load administrative regions. Please try again later.');
            });
    }

    function updateDistricts(region, selDistrict = null, selSub = null, selParish = null) {
        if (!window.ugGeoData) return;
        $('#storeLoading2').removeClass('hidden');
        const level2Options = {};
        window.ugGeoData.features.forEach(f => {
            if (f.properties.NAME_1 === region) {
                level2Options[f.properties.NAME_2] = f.properties.NAME_2;
            }
        });
        const storeLevel2 = $('#storeLevel2');
        storeLevel2.html('<option value="">Select District</option>');
        Object.keys(level2Options)
            .sort()
            .forEach(dist => {
                storeLevel2.append(
                    `<option value="${dist}" ${dist === selDistrict ? 'selected' : ''}>${dist}</option>`
                );
            });
        storeLevel2.prop('disabled', false);
        $('#storeLoading2').addClass('hidden');

        if (selDistrict) {
            updateSubcounties(region, selDistrict, selSub, selParish);
        } else {
            resetDropdown('#storeLevel3');
            resetDropdown('#storeLevel4');
        }
    }

    function updateSubcounties(region, dist, selSub = null, selParish = null) {
        if (!window.ugGeoData) return;
        $('#storeLoading3').removeClass('hidden');
        const level3Options = {};
        window.ugGeoData.features.forEach(f => {
            if (f.properties.NAME_1 === region && f.properties.NAME_2 === dist) {
                level3Options[f.properties.NAME_3] = f.properties.NAME_3;
            }
        });
        const storeLevel3 = $('#storeLevel3');
        storeLevel3.html('<option value="">Select Sub-county</option>');
        Object.keys(level3Options)
            .sort()
            .forEach(sc => {
                storeLevel3.append(
                    `<option value="${sc}" ${sc === selSub ? 'selected' : ''}>${sc}</option>`
                );
            });
        storeLevel3.prop('disabled', false);
        $('#storeLoading3').addClass('hidden');
        if (selSub) {
            updateParishes(region, dist, selSub, selParish);
        } else {
            resetDropdown('#storeLevel4');
        }
    }

    function updateParishes(region, dist, sub, selParish = null) {
        if (!window.ugGeoData) return;
        $('#storeLoading4').removeClass('hidden');
        const level4Options = {};
        window.ugGeoData.features.forEach(f => {
            if (
                f.properties.NAME_1 === region &&
                f.properties.NAME_2 === dist &&
                f.properties.NAME_3 === sub
            ) {
                if (f.properties.NAME_4) {
                    level4Options[f.properties.NAME_4] = f.properties.NAME_4;
                }
            }
        });
        const storeLevel4 = $('#storeLevel4');
        storeLevel4.html('<option value="">Select Parish/Ward</option>');
        Object.keys(level4Options)
            .sort()
            .forEach(p => {
                storeLevel4.append(
                    `<option value="${p}" ${p === selParish ? 'selected' : ''}>${p}</option>`
                );
            });
        storeLevel4.prop('disabled', false);
        $('#storeLoading4').addClass('hidden');
    }

    function updateMapGeoJSON(selections) {
        if (!window.ugGeoData || !storeMap) return;
        if (storeGeoJSONLayer) {
            storeMap.removeLayer(storeGeoJSONLayer);
            storeGeoJSONLayer = null;
        }
        const filtered = window.ugGeoData.features.filter(ft => {
            let match = true;
            for (const [level, val] of Object.entries(selections)) {
                if (ft.properties[`NAME_${level}`] !== val) {
                    match = false;
                    break;
                }
            }
            return match;
        });
        if (filtered.length === 0) {
            storeCurrentGeoJSON = null;
            return;
        }
        const newGeoJSON = {
            type: 'FeatureCollection',
            features: filtered
        };
        storeCurrentGeoJSON = newGeoJSON;
        storeGeoJSONLayer = L.geoJSON(newGeoJSON, {
            style: {
                color: '#C00000',
                weight: 2,
                opacity: 1,
                fillColor: '#C00000',
                fillOpacity: 0.2
            }
        }).addTo(storeMap);

        storeMap.fitBounds(storeGeoJSONLayer.getBounds(), {
            padding: [20, 20],
            maxZoom: 12,
            animate: true
        });

        if (storeMarker) {
            const markerPos = storeMarker.getLatLng();
            let isWithinBounds = false;

            for (const feature of storeCurrentGeoJSON.features) {
                if (leafletPip.pointInLayer([markerPos.lng, markerPos.lat], L.geoJSON(feature), true).length > 0) {
                    isWithinBounds = true;
                    break;
                }
            }

            if (!isWithinBounds) {
                storeMap.removeLayer(storeMarker);
                storeMarker = null;
                $('#storeLatitude').val('');
                $('#storeLongitude').val('');
                $('#storeAddress').val('');
                showErrorNotification('Your marker was outside the new selected region and has been removed');
            }
        }
    }

    function clearGeoJSON() {
        if (storeGeoJSONLayer) {
            storeMap.removeLayer(storeGeoJSONLayer);
            storeGeoJSONLayer = null;
        }
        storeCurrentGeoJSON = null;
    }

    function resetDropdown(selector) {
        $(selector).html('<option value="">Select option</option>').prop('disabled', true);
    }

    function initializePhoneInput() {
        const intlTelOptions = {
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js',
            initialCountry: 'ug',
            separateDialCode: true,
            autoPlaceholder: 'polite',
            onlyCountries: ['ug'],
            preferredCountries: ['ug']
        };

        const initIntlTelInput = (selector) => {
            const inputField = document.querySelector(selector);
            if (inputField) {
                const instance = window.intlTelInput(inputField, intlTelOptions);
                $(inputField).closest('.iti').addClass('w-full');
                return instance;
            }
            return null;
        };

        storePhoneInput = initIntlTelInput('#storeContactNumber');
        storeContactPhoneInput = initIntlTelInput('#storeContactPersonPhone');
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>