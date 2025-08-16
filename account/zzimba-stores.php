<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Stores';
$activeNav = 'zzimba-stores';
ob_start();
?>

<div class="min-h-screen bg-user-content dark:bg-secondary/10">
    <div class="bg-white dark:bg-secondary border-b border-gray-200 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-5">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-primary/10 rounded-xl grid place-items-center">
                        <i class="fas fa-store text-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-secondary dark:text-white font-rubik">My Zzimba
                            Stores</h1>
                        <p class="text-sm text-gray-text dark:text-white/70">Manage your vendor profiles and store
                            listings</p>
                    </div>
                </div>
                <button id="createStoreBtn" onclick="openStoreModal('create')"
                    class="hidden sm:inline-flex px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 items-center gap-2 font-medium shadow-lg shadow-primary/25">
                    <i class="fas fa-plus"></i><span>Create New Store</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div id="pending-invitations-section"
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 hidden overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-xl font-semibold text-secondary dark:text-white">Pending Store Manager Invitations</h2>
                <p class="text-sm text-gray-text dark:text-white/70">Review and respond to invitations to manage stores
                </p>
            </div>
            <div id="pending-invitations-container" class="p-5 sm:p-6">
                <div class="flex justify-center items-center py-10">
                    <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg grid place-items-center bg-user-primary/10">
                        <i class="fas fa-th-large text-user-primary"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-secondary dark:text-white">All Store Profiles</h2>
                </div>
            </div>
            <div id="all-stores-container" class="p-4 sm:p-6">
                <div class="flex justify-center items-center py-12"></div>
            </div>
        </div>
    </div>

    <button onclick="openStoreModal('create')"
        class="sm:hidden fixed bottom-5 right-5 z-40 rounded-full w-14 h-14 bg-primary text-white grid place-items-center shadow-xl hover:bg-primary/90 active:scale-95 transition">
        <i class="fas fa-plus text-lg"></i>
    </button>
</div>

<div id="storeModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300" onclick="closeStoreModal()">
    </div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-user-primary/10 grid place-items-center">
                        <i class="fas fa-store text-user-primary"></i>
                    </div>
                    <div>
                        <h3 id="storeModalTitle" class="text-lg font-semibold text-secondary dark:text-white"></h3>
                        <p class="text-sm text-gray-text dark:text-white/70">Fill in the details below</p>
                    </div>
                </div>
                <button onclick="closeStoreModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="p-5 sm:p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div id="storeStep1Indicator"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-medium">
                        1</div>
                    <div id="storeStep1to2Line" class="w-12 h-1 bg-gray-200 dark:bg-white/10"></div>
                    <div id="storeStep2Indicator"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70 font-medium">
                        2</div>
                    <div id="storeStep2to3Line" class="w-12 h-1 bg-gray-200 dark:bg-white/10"></div>
                    <div id="storeStep3Indicator"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70 font-medium">
                        3</div>
                </div>
            </div>

            <form id="storeForm" class="grid gap-6">
                <input type="hidden" id="storeMode" value="">
                <input type="hidden" id="storeId" value="">

                <div id="storeStep1" class="grid gap-4">
                    <h4 class="text-center font-medium text-secondary dark:text-white">Basic Store Details</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="storeBusinessName" class="form-label text-secondary dark:text-white">Business
                                Name <span class="text-red-500">*</span></label>
                            <input type="text" id="storeBusinessName" placeholder="Enter business name"
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="storeBusinessEmail" class="form-label text-secondary dark:text-white">Business
                                Email <span class="text-red-500">*</span></label>
                            <input type="email" id="storeBusinessEmail" placeholder="Enter business email"
                                class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="storeContactNumber" class="form-label text-secondary dark:text-white">Main
                                Contact Number <span class="text-red-500">*</span></label>
                            <input type="tel" id="storeContactNumber" class="form-input" placeholder="+256 7XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label for="storeNatureOfBusiness" class="form-label text-secondary dark:text-white">Nature
                                of Business <span class="text-red-500">*</span></label>
                            <select id="storeNatureOfBusiness" class="form-select">
                                <option value="">Select Nature of Business</option>
                            </select>
                        </div>
                        <div class="form-group sm:col-span-2">
                            <label for="storeContactPersonName"
                                class="form-label text-secondary dark:text-white">Contact Person Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="storeContactPersonName" placeholder="Enter contact person name"
                                class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="button" id="storeStep1NextBtn"
                                class="w-full px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all">Next</button>
                        </div>
                    </div>
                </div>

                <div id="storeStep2" class="hidden grid gap-5">
                    <h4 class="text-center font-medium text-secondary dark:text-white">Store Location</h4>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label text-secondary dark:text-white">Select Location on Map <span
                                    class="text-red-500">*</span></label>
                            <div id="storeMapContainer"
                                class="w-full h-64 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 mb-3">
                            </div>
                            <div class="flex items-center gap-2">
                                <button id="storeLocateMeBtn" type="button"
                                    class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">Find
                                    My Location</button>
                                <select id="storeMapStyle" class="form-select w-40">
                                    <option value="osm">OpenStreetMap</option>
                                    <option value="satellite">Satellite</option>
                                    <option value="terrain">Terrain</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mt-3">
                                <div class="form-group">
                                    <label for="storeLatitude"
                                        class="form-label text-secondary dark:text-white">Latitude <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="storeLatitude" readonly
                                        class="form-input bg-gray-50 dark:bg-white/5">
                                </div>
                                <div class="form-group">
                                    <label for="storeLongitude"
                                        class="form-label text-secondary dark:text-white">Longitude <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="storeLongitude" readonly
                                        class="form-input bg-gray-50 dark:bg-white/5">
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-4">
                            <div class="form-group">
                                <label for="storeLevel1"
                                    class="form-label text-secondary dark:text-white">Region/Province <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="storeLevel1" class="form-select">
                                        <option value="">Select Region/Province</option>
                                    </select>
                                    <span id="storeLoading1"
                                        class="hidden absolute right-2 top-2 text-xs text-gray-500 dark:text-white/60">Loading...</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="storeLevel2" class="form-label text-secondary dark:text-white">District
                                    <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="storeLevel2" disabled class="form-select">
                                        <option value="">Select District</option>
                                    </select>
                                    <span id="storeLoading2"
                                        class="hidden absolute right-2 top-2 text-xs text-gray-500 dark:text-white/60">Loading...</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="storeLevel3"
                                    class="form-label text-secondary dark:text-white">Sub-county</label>
                                <div class="relative">
                                    <select id="storeLevel3" disabled class="form-select">
                                        <option value="">Select Sub-county</option>
                                    </select>
                                    <span id="storeLoading3"
                                        class="hidden absolute right-2 top-2 text-xs text-gray-500 dark:text-white/60">Loading...</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="storeLevel4"
                                    class="form-label text-secondary dark:text-white">Parish/Ward</label>
                                <div class="relative">
                                    <select id="storeLevel4" disabled class="form-select">
                                        <option value="">Select Parish/Ward</option>
                                    </select>
                                    <span id="storeLoading4"
                                        class="hidden absolute right-2 top-2 text-xs text-gray-500 dark:text-white/60">Loading...</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="storeAddress" class="form-label text-secondary dark:text-white">Physical
                                    Address <span class="text-red-500">*</span></label>
                                <input type="text" id="storeAddress" placeholder="Detected from map selection" readonly
                                    class="form-input bg-gray-50 dark:bg-white/5">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" id="storeStep2BackBtn"
                            class="px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Back</button>
                        <button type="button" id="storeStep2NextBtn"
                            class="px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90">Next</button>
                    </div>
                </div>

                <div id="storeStep3" class="hidden grid gap-4">
                    <h4 class="text-center font-medium text-secondary dark:text-white">Store Details</h4>
                    <div class="grid gap-4">
                        <div class="form-group">
                            <label for="storeDescription" class="form-label text-secondary dark:text-white">Store
                                Description</label>
                            <textarea id="storeDescription" rows="4" placeholder="Brief description of your store"
                                class="form-textarea"></textarea>
                        </div>
                        <div class="grid gap-3">
                            <label class="form-label text-secondary dark:text-white">Store Logo</label>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-lg flex items-center justify-center">
                                    <i id="storeLogoPlaceholder" class="fas fa-store text-gray-400 text-xl"></i>
                                    <img id="storeLogoPreview" class="w-full h-full object-cover rounded-lg hidden"
                                        src="#" alt="Logo preview">
                                </div>
                                <label for="storeLogo"
                                    class="cursor-pointer px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Upload
                                    Logo</label>
                                <input type="file" id="storeLogo" accept="image/*" class="hidden">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-white/60">Recommended size: 512×512 pixels. Max
                                2MB.</p>
                        </div>
                        <div class="form-group">
                            <label for="storeWebsite" class="form-label text-secondary dark:text-white">Website
                                (Optional)</label>
                            <input type="url" id="storeWebsite" placeholder="https://example.com" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="storeSocialMedia" class="form-label text-secondary dark:text-white">Social Media
                                (Optional)</label>
                            <input type="text" id="storeSocialMedia" placeholder="Facebook, Instagram, etc."
                                class="form-input">
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <button type="button" id="storeStep3BackBtn"
                            class="px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Back</button>
                        <button type="button" id="storeStep3FinishBtn"
                            class="px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="invitationResponseModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeInvitationResponseModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex justify-between items-center mb-3">
                <h3 id="invitationResponseTitle" class="text-lg font-semibold text-secondary dark:text-white">Confirm
                    Response</h3>
                <button onclick="closeInvitationResponseModal()" id="invitationResponseCloseBtn"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div id="invitationResponseContent" class="text-sm text-gray-700 dark:text-white/80 py-2"></div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" id="invitationResponseCancelBtn" onclick="closeInvitationResponseModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button type="button" id="invitationResponseConfirmBtn"
                    class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/90">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white dark:bg-secondary p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700 dark:text-white/80">Processing...</p>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-pip@1.1.0/leaflet-pip.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<style>
    .form-group {
        display: grid;
        gap: .375rem
    }

    .form-label {
        font-size: .875rem;
        font-weight: 600
    }

    .form-input,
    .form-textarea,
    .form-select {
        width: 100%;
        padding: .625rem .75rem;
        font-size: .875rem;
        border: 1px solid rgb(209 213 219);
        border-radius: .75rem;
        background: #fff;
        color: rgb(17 24 39);
        line-height: 1.25rem
    }

    .form-textarea {
        resize: vertical;
        min-height: 2.75rem
    }

    .form-input:focus,
    .form-textarea:focus,
    .form-select:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgb(217 43 19 / .15);
        border-color: rgb(217 43 19)
    }

    .dark .form-input,
    .dark .form-textarea,
    .dark .form-select {
        background: transparent;
        color: #fff;
        border-color: rgba(255, 255, 255, .2)
    }

    .dark .form-input::placeholder,
    .dark .form-textarea::placeholder {
        color: rgba(255, 255, 255, .6)
    }

    .store-card {
        transition: all .2s
    }

    .store-card:hover {
        transform: translateY(-2px)
    }

    .store-type-badge {
        position: absolute;
        top: 0;
        right: 0;
        padding: 2px 8px;
        font-size: .7rem;
        border-radius: 0 .375rem 0 .375rem
    }

    .store-type-owned {
        background-color: rgba(220, 38, 38, .1);
        color: #dc2626
    }

    .store-type-managed {
        background-color: rgba(59, 130, 246, .1);
        color: #3b82f6
    }

    .location-icon {
        background-color: #ef4444;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0, 0, 0, .5)
    }

    .pulse {
        animation: pulse-animation 2s infinite
    }

    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, .7)
        }

        70% {
            box-shadow: 0 0 0 15px rgba(239, 68, 68, 0)
        }

        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0)
        }
    }
</style>

<script>
    const STORE_API_BASE = BASE_URL + 'account/fetch/manageZzimbaStores.php'
    let storeMap = null, storeMarker = null, storeGeoJSONLayer = null, storeCurrentGeoJSON = null, storeBaseLayers = {}
    let storePhoneInput = null, storeContactPhoneInput = null, allStores = [], pendingInvitations = [], currentFilter = 'owned', currentInvitationAction = null, currentInvitationId = null, natureOfBusinessOptions = []

    document.addEventListener('DOMContentLoaded', function () {
        loadAllStores()
        loadPendingInvitations()
        loadNatureOfBusiness()
        initializePhoneInput()
        document.getElementById('storeStep1NextBtn').addEventListener('click', () => goToStep2())
        document.getElementById('storeStep2BackBtn').addEventListener('click', () => backToStep1())
        document.getElementById('storeStep2NextBtn').addEventListener('click', () => goToStep3())
        document.getElementById('storeStep3BackBtn').addEventListener('click', () => backToStep2())
        document.getElementById('storeStep3FinishBtn').addEventListener('click', () => saveStoreData())
        const logoInput = document.getElementById('storeLogo')
        if (logoInput) {
            logoInput.addEventListener('change', e => {
                if (e.target.files && e.target.files[0]) {
                    const r = new FileReader()
                    r.onload = function (ev) {
                        document.getElementById('storeLogoPreview').src = ev.target.result
                        document.getElementById('storeLogoPreview').classList.remove('hidden')
                        document.getElementById('storeLogoPlaceholder').classList.add('hidden')
                    }
                    r.readAsDataURL(e.target.files[0])
                }
            })
        }
        const confirmBtn = document.getElementById('invitationResponseConfirmBtn')
        if (confirmBtn) { confirmBtn.addEventListener('click', function () { if (currentInvitationAction && currentInvitationId) { processInvitationResponse(currentInvitationAction, currentInvitationId) } }) }
    })

    function showModal(id) { const m = document.getElementById(id); m.classList.remove('hidden'); setTimeout(() => { m.classList.remove('opacity-0'); const c = m.querySelector('.transform'); if (c) { c.classList.remove('scale-95'); c.classList.add('scale-100') } }, 10) }
    function hideModal(id) { const m = document.getElementById(id); m.classList.add('opacity-0'); const c = m.querySelector('.transform'); if (c) { c.classList.remove('scale-100'); c.classList.add('scale-95') } setTimeout(() => { m.classList.add('hidden') }, 300) }

    function loadNatureOfBusiness() {
        fetch(STORE_API_BASE + '?action=getNatureOfBusiness').then(r => r.json()).then(resp => {
            if (resp.success) {
                natureOfBusinessOptions = resp.natureOfBusiness || []
                const dd = document.getElementById('storeNatureOfBusiness')
                dd.innerHTML = '<option value="">Select Nature of Business</option>'
                natureOfBusinessOptions.forEach(it => { const o = document.createElement('option'); o.value = it.id; o.textContent = it.name; dd.appendChild(o) })
            }
        })
    }

    function showLoading() { document.getElementById('loadingOverlay').classList.remove('hidden') }
    function hideLoading() { document.getElementById('loadingOverlay').classList.add('hidden') }

    function toast(id, msg, classes) { let n = document.getElementById(id); if (!n) { n = document.createElement('div'); n.id = id; n.className = 'fixed top-4 right-4 z-[60] hidden'; document.body.appendChild(n) } n.innerHTML = '<div class="' + classes + ' rounded-xl shadow-lg px-4 py-3 text-sm flex items-center gap-2">' + msg + '</div>'; n.classList.remove('hidden'); setTimeout(() => { n.classList.add('hidden') }, 3000) }
    function showErrorNotification(m) { toast('errToast', '<i class="fas fa-exclamation-triangle"></i><span>' + m + '</span>', 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-200 dark:border-red-500/20') }
    function showSuccessNotification(m) { toast('okToast', '<i class="fas fa-check-circle"></i><span>' + m + '</span>', 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-500/10 dark:text-green-200 dark:border-green-500/20') }

    function loadAllStores() {
        showLoading()
        fetch(STORE_API_BASE + '?action=getOwnedStores').then(r => r.json()).then(o => {
            if (o.success) {
                const owned = (o.stores || []).map(s => ({ ...s, type: 'owned' }))
                fetch(STORE_API_BASE + '?action=getManagedStores').then(r => r.json()).then(m => {
                    hideLoading()
                    if (m.success) {
                        const managed = (m.stores || []).map(s => ({ ...s, type: 'managed' }))
                        allStores = [...owned, ...managed]
                        renderStores(allStores)
                    } else {
                        allStores = owned
                        renderStores(allStores)
                        showErrorNotification(m.error || 'Failed to load managed stores')
                    }
                }).catch(() => { hideLoading(); allStores = owned; renderStores(allStores); showErrorNotification('Failed to load managed stores. Please try again.') })
            } else { hideLoading(); showErrorNotification(o.error || 'Failed to load stores') }
        }).catch(() => { hideLoading(); showErrorNotification('Failed to load stores. Please try again.') })
    }

    function loadPendingInvitations() {
        fetch(STORE_API_BASE + '?action=getPendingInvitations').then(r => r.json()).then(resp => {
            if (resp.success) {
                pendingInvitations = resp.invitations || []
                const sec = document.getElementById('pending-invitations-section')
                if (pendingInvitations.length > 0) { sec.classList.remove('hidden'); renderPendingInvitations(pendingInvitations) } else { sec.classList.add('hidden') }
            } else { showErrorNotification(resp.error || 'Failed to load invitations') }
        }).catch(() => { showErrorNotification('Failed to load invitations. Please try again.') })
    }

    function renderPendingInvitations(invitations) {
        const container = document.getElementById('pending-invitations-container')
        if (!invitations || invitations.length === 0) {
            container.innerHTML = '<div class="bg-gray-50 dark:bg-white/5 rounded-lg p-6 text-center"><p class="text-gray-500 dark:text-white/70">No pending invitations</p></div>'
            return
        }
        let html = '<div class="grid grid-cols-1 gap-4">'
        invitations.forEach(inv => {
            const logo = inv.logo_url ? BASE_URL + inv.logo_url : 'https://placehold.co/100x100/f0f0f0/808080?text=' + escapeHtml(inv.store_name).substring(0, 2)
            const safeName = escapeJsString(inv.store_name)
            html += `
            <div class="bg-white dark:bg-secondary rounded-xl border border-gray-200 dark:border-white/10 p-4 invitation-card">
                <div class="flex items-start sm:items-center gap-4 flex-col sm:flex-row">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-white/10 grid place-items-center flex-shrink-0">
                        <img src="${logo}" alt="${escapeHtml(inv.store_name)}" class="w-12 h-12 object-cover rounded">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-secondary dark:text-white truncate">${escapeHtml(inv.store_name)}</h3>
                        <p class="text-sm text-gray-600 dark:text-white/70"><span class="font-medium">Role:</span> ${escapeHtml(inv.role_display)}</p>
                        <p class="text-sm text-gray-600 dark:text-white/70"><span class="font-medium">Invited by:</span> ${escapeHtml(inv.owner_name)}</p>
                        <p class="text-xs text-gray-500 dark:text-white/60 mt-1">Invited ${formatTimeAgo(new Date(inv.created_at))}</p>
                    </div>
                    <div class="flex w-full sm:w-auto gap-2 justify-stretch sm:justify-end">
                        <button class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700" onclick="confirmInvitationResponse('approve','${inv.manager_id}','${safeName}')">Approve</button>
                        <button class="px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10" onclick="confirmInvitationResponse('decline','${inv.manager_id}','${safeName}')">Decline</button>
                    </div>
                </div>
            </div>`
        })
        html += '</div>'
        container.innerHTML = html
    }

    function confirmInvitationResponse(action, managerId, storeName) {
        currentInvitationAction = action
        currentInvitationId = managerId
        const title = action === 'approve' ? 'Approve Invitation' : 'Decline Invitation'
        const content = action === 'approve' ?
            `Are you sure you want to approve the invitation to manage <strong>${escapeHtml(storeName)}</strong>? You will gain access to manage this store according to your assigned role.` :
            `Are you sure you want to decline the invitation to manage <strong>${escapeHtml(storeName)}</strong>? This action cannot be undone, and the store owner will be notified.`
        document.getElementById('invitationResponseTitle').textContent = title
        document.getElementById('invitationResponseContent').innerHTML = `<p>${content}</p>`
        const btn = document.getElementById('invitationResponseConfirmBtn')
        btn.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-green-600', 'hover:bg-green-700', 'bg-primary', 'hover:bg-primary/90')
        if (action === 'approve') { btn.classList.add('bg-green-600', 'hover:bg-green-700') } else { btn.classList.add('bg-red-600', 'hover:bg-red-700') }
        showModal('invitationResponseModal')
    }

    function processInvitationResponse(action, managerId) {
        const endpoint = action === 'approve' ? 'approveManagerInvitation' : 'declineManagerInvitation'
        const cBtn = document.getElementById('invitationResponseConfirmBtn')
        const xBtn = document.getElementById('invitationResponseCloseBtn')
        const cancel = document.getElementById('invitationResponseCancelBtn')
        cBtn.disabled = true; xBtn.disabled = true; cancel.disabled = true
        cBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${action === 'approve' ? 'Approving...' : 'Declining...'}`
        fetch(STORE_API_BASE + '?action=' + endpoint, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ managerId }) })
            .then(r => r.json()).then(resp => {
                if (resp.success) {
                    hideModal('invitationResponseModal')
                    showSuccessNotification(resp.message || (action === 'approve' ? 'Invitation approved successfully' : 'Invitation declined successfully'))
                    loadPendingInvitations()
                    if (action === 'approve') { loadAllStores() }
                } else {
                    showErrorNotification(resp.error || 'Failed to process invitation')
                    enableInvitationResponseButtons()
                }
            }).catch(() => { showErrorNotification('Failed to process invitation. Please try again.'); enableInvitationResponseButtons() })
    }

    function enableInvitationResponseButtons() {
        const cBtn = document.getElementById('invitationResponseConfirmBtn')
        const xBtn = document.getElementById('invitationResponseCloseBtn')
        const cancel = document.getElementById('invitationResponseCancelBtn')
        cBtn.disabled = false; xBtn.disabled = false; cancel.disabled = false
        cBtn.textContent = currentInvitationAction === 'approve' ? 'Approve' : 'Decline'
    }

    function closeInvitationResponseModal() { hideModal('invitationResponseModal'); currentInvitationAction = null; currentInvitationId = null; enableInvitationResponseButtons() }

    function renderStores(stores) {
        const container = document.getElementById('all-stores-container')
        if (!stores || stores.length === 0) {
            container.innerHTML = `
            <div class="text-center py-14">
                <div class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-full grid place-items-center mx-auto mb-4">
                    <i class="fas fa-store text-gray-400 dark:text-white/60 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-secondary dark:text-white mb-1">No Stores Found</h3>
                <p class="text-sm text-gray-text dark:text-white/70 mb-4">${currentFilter === 'owned' ? 'You haven’t created any stores yet.' : currentFilter === 'managed' ? 'You don’t manage any stores yet.' : 'No stores found.'}</p>
                <button onclick="openStoreModal('create')" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90">Create Your First Store</button>
            </div>`
            return
        }
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">'
        stores.forEach(store => {
            const statusBadge = getStatusBadge(store.status)
            const logo = store.logo_url ? BASE_URL + store.logo_url : 'https://placehold.co/100x100/f0f0f0/808080?text=' + escapeHtml(store.name).substring(0, 2)
            const typeBadge = store.type === 'owned' ? '<span class="store-type-badge store-type-owned">Owned</span>' : '<span class="store-type-badge store-type-managed">Managed</span>'
            html += `
            <div class="store-card bg-white dark:bg-secondary rounded-2xl border border-gray-100 dark:border-white/10 overflow-hidden relative">
                ${typeBadge}
                <div class="flex">
                    <div class="w-28 sm:w-32 h-28 sm:h-32 bg-gray-50 dark:bg-white/10 grid place-items-center flex-shrink-0">
                        <img src="${logo}" alt="${escapeHtml(store.name)}" class="w-16 h-16 object-cover rounded-lg">
                    </div>
                    <div class="p-4 sm:p-6 flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="font-semibold text-secondary dark:text-white truncate" title="${escapeHtml(store.name)}">${escapeHtml(store.name)}</h3>
                            <div class="shrink-0">${statusBadge}</div>
                        </div>
                        <p class="text-sm text-gray-text dark:text-white/70 mb-3 line-clamp-2" title="${escapeHtml(store.location)}"><i class="fas fa-map-marker-alt mr-1 text-user-primary"></i>${escapeHtml(store.location || '')}</p>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-white/60">Products</p>
                                <p class="font-medium text-secondary dark:text-white">${store.product_count || 0}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-white/60">Categories</p>
                                <p class="font-medium text-secondary dark:text-white">${store.categories ? store.categories.length : 0}</p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button onclick="openStoreModal('edit','${store.uuid_id}')" class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 flex items-center gap-1"><i class="fas fa-edit"></i><span>Edit</span></button>
                            <button onclick="redirectToManageStore('${store.uuid_id}')" class="px-3 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 flex items-center gap-1"><i class="fas fa-cog"></i><span>Manage</span></button>
                        </div>
                    </div>
                </div>
            </div>`
        })
        html += '</div>'
        container.innerHTML = html
    }

    function redirectToManageStore(storeUuid) {
        showLoading()
        fetch(BASE_URL + 'account/fetch/initVendorSession.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ store_uuid: storeUuid }) })
            .then(r => r.json()).then(resp => { hideLoading(); if (resp.success && resp.redirect_url) { window.location.href = resp.redirect_url } else { showErrorNotification(resp.message || 'Failed to initiate store session') } })
            .catch(() => { hideLoading(); showErrorNotification('Server error occurred. Please try again.') })
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'active': return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>Active</span>`
            case 'pending': return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-yellow-500"></span>Pending</span>`
            case 'inactive': return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>Inactive</span>`
            case 'suspended': return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-500"></span>Suspended</span>`
            default: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80"><span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-gray-500"></span>${status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown'}</span>`
        }
    }

    function escapeHtml(t) { const d = document.createElement('div'); d.textContent = String(t || ''); return d.innerHTML }
    function escapeJsString(s) { return String(s).replace(/\\/g, '\\\\').replace(/'/g, "\\'") }
    function formatTimeAgo(date) { const now = new Date(); const s = Math.floor((now - date) / 1000); if (s < 60) return 'just now'; const m = Math.floor(s / 60); if (m < 60) return m + (m === 1 ? ' minute ago' : ' minutes ago'); const h = Math.floor(m / 60); if (h < 24) return h + (h === 1 ? ' hour ago' : ' hours ago'); const d = Math.floor(h / 24); if (d < 30) return d + (d === 1 ? ' day ago' : ' days ago'); const mo = Math.floor(d / 30); if (mo < 12) return mo + (mo === 1 ? ' month ago' : ' months ago'); const y = Math.floor(mo / 12); return y + (y === 1 ? ' year ago' : ' years ago') }

    function openStoreModal(mode, storeId = null) {
        document.getElementById('storeMode').value = mode
        if (mode === 'create') { document.getElementById('storeModalTitle').textContent = 'Create New Store'; resetStoreForm() } else { document.getElementById('storeModalTitle').textContent = 'Edit Store'; resetStoreForm(); populateStoreForm(storeId) }
        showModal('storeModal')
    }
    function closeStoreModal() { hideModal('storeModal') }

    function goToStep2() {
        const name = document.getElementById('storeBusinessName').value.trim()
        const email = document.getElementById('storeBusinessEmail').value.trim()
        const phone = storePhoneInput ? storePhoneInput.getNumber() : ''
        const contactName = document.getElementById('storeContactPersonName').value.trim()
        const nature = document.getElementById('storeNatureOfBusiness').value
        if (!name || !email || !phone || !nature || !contactName) { showErrorNotification('Please fill in all required fields'); return }
        if (storePhoneInput && !storePhoneInput.isValidNumber()) { showErrorNotification('Please enter a valid business phone number'); return }
        document.getElementById('storeStep1').classList.add('hidden')
        document.getElementById('storeStep2').classList.remove('hidden')
        document.getElementById('storeStep1Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white'
        document.getElementById('storeStep1Indicator').innerHTML = '<i class="fas fa-check"></i>'
        document.getElementById('storeStep2Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white'
        document.getElementById('storeStep1to2Line').className = 'w-12 h-1 bg-green-500'
        setTimeout(() => { if (storeMap) storeMap.invalidateSize() }, 120)
    }

    function backToStep1() {
        document.getElementById('storeStep2').classList.add('hidden')
        document.getElementById('storeStep1').classList.remove('hidden')
        document.getElementById('storeStep1Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white'
        document.getElementById('storeStep1Indicator').textContent = '1'
        document.getElementById('storeStep2Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'
        document.getElementById('storeStep2Indicator').textContent = '2'
        document.getElementById('storeStep1to2Line').className = 'w-12 h-1 bg-gray-200 dark:bg-white/10'
    }

    function goToStep3() {
        const lat = document.getElementById('storeLatitude').value
        const lng = document.getElementById('storeLongitude').value
        const lvl1 = document.getElementById('storeLevel1').value
        const lvl2 = document.getElementById('storeLevel2').value
        const addr = document.getElementById('storeAddress').value
        if (!lat || !lng || !lvl1 || !lvl2 || !addr) { showErrorNotification('Please select your location on the map and fill in all required fields'); return }
        document.getElementById('storeStep2').classList.add('hidden')
        document.getElementById('storeStep3').classList.remove('hidden')
        document.getElementById('storeStep2Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white'
        document.getElementById('storeStep2Indicator').innerHTML = '<i class="fas fa-check"></i>'
        document.getElementById('storeStep2to3Line').className = 'w-12 h-1 bg-green-500'
        document.getElementById('storeStep3Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white'
    }

    function backToStep2() {
        document.getElementById('storeStep3').classList.add('hidden')
        document.getElementById('storeStep2').classList.remove('hidden')
        document.getElementById('storeStep2Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white'
        document.getElementById('storeStep2Indicator').textContent = '2'
        document.getElementById('storeStep2to3Line').className = 'w-12 h-1 bg-gray-200 dark:bg-white/10'
        document.getElementById('storeStep3Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'
        document.getElementById('storeStep3Indicator').textContent = '3'
        setTimeout(() => { if (storeMap) storeMap.invalidateSize() }, 120)
    }

    function resetStoreForm() {
        ['storeId', 'storeBusinessName', 'storeBusinessEmail', 'storeContactNumber', 'storeContactPersonName', 'storeLatitude', 'storeLongitude', 'storeAddress', 'storeDescription', 'storeWebsite', 'storeSocialMedia'].forEach(id => { const el = document.getElementById(id); if (el) { el.value = '' } })
        document.getElementById('storeNatureOfBusiness').value = ''
        document.getElementById('storeLevel1').innerHTML = '<option value="">Select Region/Province</option>'
        document.getElementById('storeLevel2').innerHTML = '<option value="">Select District</option>'; document.getElementById('storeLevel2').disabled = true
        document.getElementById('storeLevel3').innerHTML = '<option value="">Select Sub-county</option>'; document.getElementById('storeLevel3').disabled = true
        document.getElementById('storeLevel4').innerHTML = '<option value="">Select Parish/Ward</option>'; document.getElementById('storeLevel4').disabled = true
        document.getElementById('storeLogo').value = ''
        document.getElementById('storeLogoPreview').src = '#'; document.getElementById('storeLogoPreview').classList.add('hidden'); document.getElementById('storeLogoPlaceholder').classList.remove('hidden')
        document.getElementById('storeStep1').classList.remove('hidden'); document.getElementById('storeStep2').classList.add('hidden'); document.getElementById('storeStep3').classList.add('hidden')
        document.getElementById('storeStep1Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white'; document.getElementById('storeStep1Indicator').textContent = '1'
        document.getElementById('storeStep2Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'; document.getElementById('storeStep2Indicator').textContent = '2'
        document.getElementById('storeStep3Indicator').className = 'flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'; document.getElementById('storeStep3Indicator').textContent = '3'
        document.getElementById('storeStep1to2Line').className = 'w-12 h-1 bg-gray-200 dark:bg-white/10'
        document.getElementById('storeStep2to3Line').className = 'w-12 h-1 bg-gray-200 dark:bg-white/10'
        destroyMap(); initStoreMap(); loadRegions()
    }

    function populateStoreForm(storeId) {
        document.getElementById('storeId').value = storeId
        showLoading()
        fetch(STORE_API_BASE + '?action=getStoreDetails&id=' + storeId).then(r => r.json()).then(resp => {
            hideLoading()
            if (!resp.success) { showErrorNotification(resp.error || 'Failed to load store details'); return }
            const s = resp.store
            document.getElementById('storeId').value = s.uuid_id
            document.getElementById('storeBusinessName').value = s.name || ''
            document.getElementById('storeBusinessEmail').value = s.business_email || ''
            if (storePhoneInput) storePhoneInput.setNumber(s.business_phone || '')
            if (s.contact_person_name) document.getElementById('storeContactPersonName').value = s.contact_person_name
            document.getElementById('storeNatureOfBusiness').value = s.nature_of_business || ''
            document.getElementById('storeLatitude').value = s.latitude || ''
            document.getElementById('storeLongitude').value = s.longitude || ''
            document.getElementById('storeAddress').value = s.address || ''
            document.getElementById('storeDescription').value = s.description || ''
            document.getElementById('storeWebsite').value = s.website_url || ''
            document.getElementById('storeSocialMedia').value = s.social_media || ''
            if (s.logo_url) {
                const p = document.getElementById('storeLogoPreview'); p.src = BASE_URL + s.logo_url; p.classList.remove('hidden'); document.getElementById('storeLogoPlaceholder').classList.add('hidden')
            }
            destroyMap(); initStoreMap(s.latitude, s.longitude); loadRegions(s.region, s.district, s.subcounty, s.parish)
        }).catch(() => { hideLoading(); showErrorNotification('Failed to load store details. Please try again.') })
    }

    function saveStoreData() {
        const mode = document.getElementById('storeMode').value
        const storeId = document.getElementById('storeId').value
        const formData = {
            id: storeId,
            name: document.getElementById('storeBusinessName').value,
            business_email: document.getElementById('storeBusinessEmail').value,
            business_phone: storePhoneInput ? storePhoneInput.getNumber() : '',
            contact_person_name: document.getElementById('storeContactPersonName').value,
            nature_of_business: document.getElementById('storeNatureOfBusiness').value,
            region: document.getElementById('storeLevel1').value,
            district: document.getElementById('storeLevel2').value,
            subcounty: document.getElementById('storeLevel3').value,
            parish: document.getElementById('storeLevel4').value,
            address: document.getElementById('storeAddress').value,
            latitude: document.getElementById('storeLatitude').value,
            longitude: document.getElementById('storeLongitude').value,
            description: document.getElementById('storeDescription').value,
            website_url: document.getElementById('storeWebsite').value,
            social_media: document.getElementById('storeSocialMedia').value
        }
        const logo = document.getElementById('storeLogo').files[0]
        if (logo) {
            const fd = new FormData(); fd.append('logo', logo); showLoading()
            fetch(STORE_API_BASE + '?action=uploadLogo', { method: 'POST', body: fd }).then(r => r.json()).then(resp => {
                if (resp.success) { formData.temp_logo_path = resp.temp_path; finishSave(mode, formData) } else { hideLoading(); showErrorNotification(resp.message || 'Failed to upload logo') }
            }).catch(() => { hideLoading(); showErrorNotification('Failed to upload logo. Please try again.') })
        } else { finishSave(mode, formData) }
    }

    function finishSave(mode, formData) {
        const url = mode === 'create' ? STORE_API_BASE + '?action=createStore' : STORE_API_BASE + '?action=updateStore'
        showLoading()
        fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) }).then(r => r.json()).then(resp => {
            hideLoading()
            if (resp.success) { hideModal('storeModal'); showSuccessNotification(resp.message || 'Store saved successfully!'); resetStoreForm(); loadAllStores() } else { showErrorNotification(resp.error || 'Failed to save store') }
        }).catch(() => { hideLoading(); showErrorNotification('Failed to save store. Please try again.') })
    }

    function initStoreMap(lat = 1.3733, lng = 32.2903) {
        if (storeMap) return
        storeMap = L.map('storeMapContainer').setView([lat, lng], 7)
        storeBaseLayers = {
            osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri' }),
            terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { attribution: 'Map data &copy; OpenStreetMap | Style &copy; OpenTopoMap' })
        }
        storeBaseLayers.osm.addTo(storeMap)
        document.getElementById('storeMapStyle').addEventListener('change', e => {
            Object.values(storeBaseLayers).forEach(l => { if (storeMap.hasLayer(l)) storeMap.removeLayer(l) })
            const lay = storeBaseLayers[e.target.value]; if (lay) lay.addTo(storeMap)
        })
        storeMap.on('click', e => {
            if (storeCurrentGeoJSON) {
                let inside = false
                for (const f of storeCurrentGeoJSON.features) { if (leafletPip.pointInLayer([e.latlng.lng, e.latlng.lat], L.geoJSON(f), true).length > 0) { inside = true; break } }
                if (inside) { dropStoreMarker(e.latlng) } else { showErrorNotification('Please place the pin within the highlighted region') }
            } else { showErrorNotification('Please select a region from the dropdowns first') }
        })
        if (lat && lng && lat !== 1.3733 && lng !== 32.2903) { dropStoreMarker(L.latLng(lat, lng)) }
        locateUser('#storeLocateMeBtn', storeMap, 'create')
    }

    function destroyMap() { if (storeMap) { storeMap.remove(); storeMap = null } storeMarker = null; storeGeoJSONLayer = null; storeCurrentGeoJSON = null; storeBaseLayers = {} }

    function dropStoreMarker(latlng) {
        if (storeMarker) storeMap.removeLayer(storeMarker)
        const icon = L.divIcon({ className: 'location-icon pulse', iconSize: [16, 16], iconAnchor: [8, 8] })
        storeMarker = L.marker(latlng, { draggable: true, icon }).addTo(storeMap)
        document.getElementById('storeLatitude').value = latlng.lat.toFixed(6)
        document.getElementById('storeLongitude').value = latlng.lng.toFixed(6)
        reverseGeocode(latlng.lat, latlng.lng)
        storeMarker.on('dragend', function () {
            const np = storeMarker.getLatLng()
            let ok = false
            if (storeCurrentGeoJSON) {
                for (const f of storeCurrentGeoJSON.features) { if (leafletPip.pointInLayer([np.lng, np.lat], L.geoJSON(f), true).length > 0) { ok = true; break } }
            }
            if (ok || !storeCurrentGeoJSON) {
                document.getElementById('storeLatitude').value = np.lat.toFixed(6)
                document.getElementById('storeLongitude').value = np.lng.toFixed(6)
                reverseGeocode(np.lat, np.lng)
            } else {
                storeMarker.setLatLng(latlng)
                showErrorNotification('Please keep the pin within the highlighted region')
            }
        })
    }

    function locateUser(btn, mapObj) {
        const b = document.querySelector(btn)
        if (!b) return
        b.addEventListener('click', function () {
            if (!navigator.geolocation) { showErrorNotification('Geolocation is not supported by your browser'); return }
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude, lng = pos.coords.longitude
                mapObj.setView([lat, lng], 15)
                let inside = false
                if (storeCurrentGeoJSON) {
                    for (const f of storeCurrentGeoJSON.features) { if (leafletPip.pointInLayer([lng, lat], L.geoJSON(f), true).length > 0) { inside = true; break } }
                    if (inside) { dropStoreMarker(L.latLng(lat, lng)) } else { showErrorNotification('Your location is outside the selected region. Please select a location manually.') }
                } else { dropStoreMarker(L.latLng(lat, lng)) }
            }, () => { showErrorNotification('Unable to get your location. Please select your location manually on the map.') }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 })
        })
    }

    function reverseGeocode(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        fetch(url, { headers: { 'User-Agent': 'Zzimba Online Store Location Selector' } }).then(r => r.json()).then(d => { if (d && d.display_name) { document.getElementById('storeAddress').value = d.display_name } }).catch(() => { })
    }

    function loadRegions(selReg = null, selDist = null, selSub = null, selParish = null) {
        fetch('<?= BASE_URL ?>locations/gadm41_UGA_4.json').then(r => r.json()).then(data => {
            if (!data.features) return
            const l1 = {}
            data.features.forEach(f => { const n = f.properties.NAME_1; if (n) l1[n] = n })
            const level1 = document.getElementById('storeLevel1'); level1.innerHTML = '<option value="">Select Region/Province</option>'
            Object.keys(l1).sort().forEach(r => { const o = document.createElement('option'); o.value = r; o.textContent = r; if (selReg === r) o.selected = true; level1.appendChild(o) })
            window.ugGeoData = data
            level1.onchange = function () { const region = this.value; if (region) { updateDistricts(region) } else { resetDropdown('#storeLevel2'); resetDropdown('#storeLevel3'); resetDropdown('#storeLevel4'); clearGeoJSON() } }
            document.getElementById('storeLevel2').onchange = function () { const dist = this.value; const reg = level1.value; if (dist) { updateSubcounties(reg, dist); updateMapGeoJSON({ 1: reg, 2: dist }) } else { resetDropdown('#storeLevel3'); resetDropdown('#storeLevel4'); updateMapGeoJSON({ 1: reg }) } }
            document.getElementById('storeLevel3').onchange = function () { const sub = this.value; const dist = document.getElementById('storeLevel2').value; const reg = level1.value; if (sub) { updateParishes(reg, dist, sub); updateMapGeoJSON({ 1: reg, 2: dist, 3: sub }) } else { resetDropdown('#storeLevel4'); updateMapGeoJSON({ 1: reg, 2: dist }) } }
            document.getElementById('storeLevel4').onchange = function () { const parish = this.value; const sub = document.getElementById('storeLevel3').value; const dist = document.getElementById('storeLevel2').value; const reg = level1.value; if (parish) { updateMapGeoJSON({ 1: reg, 2: dist, 3: sub, 4: parish }) } else { updateMapGeoJSON({ 1: reg, 2: dist, 3: sub }) } }
            if (selReg) { updateDistricts(selReg, selDist, selSub, selParish); const sel = { 1: selReg }; if (selDist) sel[2] = selDist; if (selSub) sel[3] = selSub; if (selParish) sel[4] = selParish; updateMapGeoJSON(sel) }
        }).catch(() => { showErrorNotification('Failed to load administrative regions. Please try again later.') })
    }

    function updateDistricts(region, selDistrict = null, selSub = null, selParish = null) {
        if (!window.ugGeoData) return
        document.getElementById('storeLoading2').classList.remove('hidden')
        const l2 = {}
        window.ugGeoData.features.forEach(f => { if (f.properties.NAME_1 === region) { l2[f.properties.NAME_2] = f.properties.NAME_2 } })
        const d = document.getElementById('storeLevel2'); d.innerHTML = '<option value="">Select District</option>'
        Object.keys(l2).sort().forEach(x => { const o = document.createElement('option'); o.value = x; o.textContent = x; if (selDistrict === x) o.selected = true; d.appendChild(o) })
        d.disabled = false; document.getElementById('storeLoading2').classList.add('hidden')
        if (selDistrict) { updateSubcounties(region, selDistrict, selSub, selParish) } else { resetDropdown('#storeLevel3'); resetDropdown('#storeLevel4') }
    }

    function updateSubcounties(region, dist, selSub = null, selParish = null) {
        if (!window.ugGeoData) return
        document.getElementById('storeLoading3').classList.remove('hidden')
        const l3 = {}
        window.ugGeoData.features.forEach(f => { if (f.properties.NAME_1 === region && f.properties.NAME_2 === dist) { l3[f.properties.NAME_3] = f.properties.NAME_3 } })
        const s = document.getElementById('storeLevel3'); s.innerHTML = '<option value="">Select Sub-county</option>'
        Object.keys(l3).sort().forEach(x => { const o = document.createElement('option'); o.value = x; o.textContent = x; if (selSub === x) o.selected = true; s.appendChild(o) })
        s.disabled = false; document.getElementById('storeLoading3').classList.add('hidden')
        if (selSub) { updateParishes(region, dist, selSub, selParish) } else { resetDropdown('#storeLevel4') }
    }

    function updateParishes(region, dist, sub, selParish = null) {
        if (!window.ugGeoData) return
        document.getElementById('storeLoading4').classList.remove('hidden')
        const l4 = {}
        window.ugGeoData.features.forEach(f => { if (f.properties.NAME_1 === region && f.properties.NAME_2 === dist && f.properties.NAME_3 === sub) { if (f.properties.NAME_4) { l4[f.properties.NAME_4] = f.properties.NAME_4 } } })
        const p = document.getElementById('storeLevel4'); p.innerHTML = '<option value="">Select Parish/Ward</option>'
        Object.keys(l4).sort().forEach(x => { const o = document.createElement('option'); o.value = x; o.textContent = x; if (selParish === x) o.selected = true; p.appendChild(o) })
        p.disabled = false; document.getElementById('storeLoading4').classList.add('hidden')
    }

    function updateMapGeoJSON(selections) {
        if (!window.ugGeoData || !storeMap) return
        if (storeGeoJSONLayer) { storeMap.removeLayer(storeGeoJSONLayer); storeGeoJSONLayer = null }
        const filtered = window.ugGeoData.features.filter(ft => { let match = true; for (const [lvl, val] of Object.entries(selections)) { if (ft.properties['NAME_' + lvl] !== val) { match = false; break } } return match })
        if (filtered.length === 0) { storeCurrentGeoJSON = null; return }
        const gj = { type: 'FeatureCollection', features: filtered }; storeCurrentGeoJSON = gj
        storeGeoJSONLayer = L.geoJSON(gj, { style: { color: '#C00000', weight: 2, opacity: 1, fillColor: '#C00000', fillOpacity: .2 } }).addTo(storeMap)
        storeMap.fitBounds(storeGeoJSONLayer.getBounds(), { padding: [20, 20], maxZoom: 12, animate: true })
        if (storeMarker) {
            const pos = storeMarker.getLatLng(); let inside = false
            for (const f of storeCurrentGeoJSON.features) { if (leafletPip.pointInLayer([pos.lng, pos.lat], L.geoJSON(f), true).length > 0) { inside = true; break } }
            if (!inside) { storeMap.removeLayer(storeMarker); storeMarker = null; document.getElementById('storeLatitude').value = ''; document.getElementById('storeLongitude').value = ''; document.getElementById('storeAddress').value = ''; showErrorNotification('Your marker was outside the new selected region and has been removed') }
        }
    }

    function clearGeoJSON() { if (storeGeoJSONLayer) { storeMap.removeLayer(storeGeoJSONLayer); storeGeoJSONLayer = null } storeCurrentGeoJSON = null }
    function resetDropdown(sel) { const el = document.querySelector(sel); if (el) { el.innerHTML = '<option value="">Select option</option>'; el.disabled = true } }

    function initializePhoneInput() {
        const opts = { utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js', initialCountry: 'ug', separateDialCode: true, autoPlaceholder: 'polite', onlyCountries: ['ug'], preferredCountries: ['ug'] }
        const init = (selector) => { const el = document.querySelector(selector); if (!el) return null; const inst = window.intlTelInput(el, opts); const wrapper = el.closest('.iti'); if (wrapper) wrapper.classList.add('w-full'); return inst }
        storePhoneInput = init('#storeContactNumber')
        storeContactPhoneInput = init('#storeContactPersonPhone')
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>