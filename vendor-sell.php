<!-- Vendor Sell Modal -->
<div id="vendorSellModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl max-h-[95vh] overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100">
            <div>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900" id="vendorSellModalTitle">Sell Product</h3>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="closeVendorSellModal()" 
                        class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div id="vendorSellContent" class="p-4 sm:p-6">
                <!-- Loading State -->
                <div id="vendorSellLoading" class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-red-500 mb-4"></i>
                        <p class="text-gray-600">Loading your stores...</p>
                    </div>
                </div>

                <!-- No Stores Message -->
                <div id="noStoresMessage" class="hidden text-center py-12">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-store text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Stores Found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">You need to create a store before you can sell products. Create your first store to get started.</p>
                    <a href="<?= BASE_URL ?>account/zzimba-stores" 
                       class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Your First Store
                    </a>
                </div>

                <!-- Store Selection Section -->
                <div id="storeSelectionSection" class="hidden space-y-6">
                    <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Select Store</h4>
                        <p class="text-sm text-gray-600 mb-6">Choose which store you want to sell this product in</p>
                        <div id="storesList" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                    </div>
                </div>

                <!-- Pricing Management Section -->
                <div id="pricingManagementSection" class="hidden space-y-6">
                    <!-- Store Info Header -->
                    <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div id="selectedStoreInfo" class="flex items-center space-x-4"></div>
                            <button onclick="goBackToStoreSelection()" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>Change Store
                            </button>
                        </div>
                    </div>

                    <!-- Current Pricing Section -->
                    <div id="existingPricingSection" class="bg-white border border-gray-200 rounded-xl">
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <h5 class="text-lg font-semibold text-gray-900">Current Pricing</h5>
                            <p class="text-sm text-gray-600 mt-1">Existing pricing entries for this product in the selected store</p>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div id="existingPricingList" class="space-y-4"></div>
                        </div>
                    </div>

                    <!-- Add New Pricing Section -->
                    <div class="bg-white border border-gray-200 rounded-xl">
                        <div class="p-4 sm:p-6 border-b border-gray-100">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h5 class="text-lg font-semibold text-gray-900">Add New Pricing</h5>
                                    <p class="text-sm text-gray-600 mt-1">Configure pricing options for this product</p>
                                </div>
                                <button type="button" id="addPricingLineBtn"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Add Pricing Entry
                                </button>
                            </div>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div id="pricingLineItemsWrapper" class="space-y-6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-col sm:flex-row justify-end p-4 sm:p-6 border-t border-gray-200 gap-3">
            <button onclick="closeVendorSellModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button id="saveVendorSellBtn" class="hidden px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-save mr-2"></i>Save Pricing
            </button>
        </div>
    </div>
</div>

<!-- Pricing Entry Modal -->
<div id="pricingEntryModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative w-full max-w-2xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl max-h-[95vh] overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-50 to-green-100">
            <div>
                <h3 class="text-lg font-semibold text-gray-900" id="pricingEntryModalTitle">Add Pricing Entry</h3>
                <p class="text-sm text-gray-600 mt-1">Configure pricing details for this product</p>
            </div>
            <button onclick="closePricingEntryModal()" 
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="overflow-y-auto max-h-[calc(95vh-200px)]">
            <div class="p-4 sm:p-6">
                <form id="pricingEntryForm" class="space-y-6">
                    <input type="hidden" id="editingPricingId" name="editing_pricing_id">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Package *</label>
                            <div class="relative">
                                <input type="text" id="pricingPkgSearchInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm" placeholder="Search package..." autocomplete="off"/>
                                <input type="hidden" id="pricingPkgMappingId" name="package_mapping_id"/>
                                <div id="pricingPkgDropdown" class="absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-40"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit of Measure *</label>
                            <div class="relative">
                                <input type="text" id="pricingSiSearchInput" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm" placeholder="Search SI unit..." autocomplete="off"/>
                                <input type="hidden" id="pricingSiUnitId" name="si_unit_id"/>
                                <div id="pricingSiDropdown" class="absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 hidden z-40"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Size *</label>
                            <input type="text" id="pricingPackageSize" name="package_size" value="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Price Category *</label>
                            <select id="pricingPriceCategory" name="price_category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                <option value="">-- Select Category --</option>
                                <option value="retail">Retail</option>
                                <option value="wholesale">Wholesale</option>
                                <option value="factory">Factory</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Price (UGX) *</label>
                            <input type="number" step="any" min="1" id="pricingPrice" name="price" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2" id="pricingDeliveryCapacityLabel">Capacity</label>
                            <input type="number" id="pricingDeliveryCapacity" name="delivery_capacity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closePricingEntryModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="savePricingEntryBtn"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Save Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let vendorSellCurrentProduct = null;
let vendorSellSelectedStore = null;
let vendorSellAvailablePackageMappings = [];
let vendorSellAvailableSIUnits = [];
let vendorSellExistingPricing = [];
let vendorSellPendingPricingEntries = [];
let editingPricingIndex = -1;

async function openVendorSellModal(productId, productName) {
    const sessionActive = await checkUserSession();
    
    if (!sessionActive) {
        if (typeof openAuthModal === 'function') {
            openAuthModal();
        }
        return;
    }

    vendorSellCurrentProduct = { id: productId, name: productName };
    document.getElementById('vendorSellModalTitle').textContent = `Sell "${productName}"`;
    
    // Reset modal state
    document.getElementById('vendorSellLoading').classList.remove('hidden');
    document.getElementById('noStoresMessage').classList.add('hidden');
    document.getElementById('storeSelectionSection').classList.add('hidden');
    document.getElementById('pricingManagementSection').classList.add('hidden');
    document.getElementById('saveVendorSellBtn').classList.add('hidden');
    
    document.getElementById('vendorSellModal').classList.remove('hidden');
    
    await loadUserStores();
}

function closeVendorSellModal() {
    document.getElementById('vendorSellModal').classList.add('hidden');
    vendorSellCurrentProduct = null;
    vendorSellSelectedStore = null;
    vendorSellExistingPricing = [];
    vendorSellPendingPricingEntries = [];
    editingPricingIndex = -1;
}

async function loadUserStores() {
    try {
        const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getUserStores`);
        const data = await response.json();
        
        document.getElementById('vendorSellLoading').classList.add('hidden');
        
        if (data.success && data.stores && data.stores.length > 0) {
            renderStoresList(data.stores);
            document.getElementById('storeSelectionSection').classList.remove('hidden');
        } else {
            document.getElementById('noStoresMessage').classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading user stores:', error);
        document.getElementById('vendorSellLoading').classList.add('hidden');
        document.getElementById('noStoresMessage').classList.remove('hidden');
    }
}

function renderStoresList(stores) {
    const container = document.getElementById('storesList');
    container.innerHTML = '';
    
    stores.forEach(store => {
        const storeCard = document.createElement('div');
        storeCard.className = 'bg-white border border-gray-200 rounded-xl p-4 sm:p-6 hover:border-red-300 hover:shadow-md transition-all cursor-pointer';
        storeCard.onclick = () => selectStore(store);
        
        const logoHtml = store.logo_url 
            ? `<img src="${BASE_URL}${store.logo_url}" alt="${store.name}" class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover">`
            : `<div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 flex items-center justify-center">
                 <i class="fas fa-store text-gray-500 text-lg sm:text-xl"></i>
               </div>`;
        
        const statusBadgeClass = store.status === 'active' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-yellow-100 text-yellow-800';
        
        storeCard.innerHTML = `
            <div class="flex items-center space-x-4">
                ${logoHtml}
                <div class="flex-1 min-w-0">
                    <h5 class="font-semibold text-gray-900 text-sm sm:text-base truncate">${escapeHtml(store.name)}</h5>
                    <p class="text-xs sm:text-sm text-gray-600 truncate">${escapeHtml(store.district)}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="text-xs px-2 py-1 rounded-full font-medium ${statusBadgeClass}">
                            ${store.status.charAt(0).toUpperCase() + store.status.slice(1)}
                        </span>
                        <span class="text-xs text-gray-500 capitalize">${store.role}</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400 flex-shrink-0"></i>
            </div>
        `;
        
        container.appendChild(storeCard);
    });
}

async function selectStore(store) {
    vendorSellSelectedStore = store;
    vendorSellPendingPricingEntries = [];
    
    // Show loading
    document.getElementById('storeSelectionSection').classList.add('hidden');
    document.getElementById('vendorSellLoading').classList.remove('hidden');
    
    try {
        // Load all required data
        await Promise.all([
            loadExistingPricing(),
            loadPackageMappingsForProduct(),
            ensureVendorSellSIUnits()
        ]);
        
        renderSelectedStoreInfo();
        renderExistingPricing();
        
        document.getElementById('vendorSellLoading').classList.add('hidden');
        document.getElementById('pricingManagementSection').classList.remove('hidden');
        document.getElementById('saveVendorSellBtn').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error loading store data:', error);
        showToast('Error loading store information', 'error');
        goBackToStoreSelection();
    }
}

async function loadExistingPricing() {
    const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getExistingPricing&store_id=${vendorSellSelectedStore.id}&product_id=${vendorSellCurrentProduct.id}`);
    const data = await response.json();
    
    if (data.success) {
        vendorSellExistingPricing = data.pricing || [];
    }
}

async function loadPackageMappingsForProduct() {
    const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getPackageNamesForProduct&product_id=${vendorSellCurrentProduct.id}`);
    const data = await response.json();
    
    if (data.success) {
        vendorSellAvailablePackageMappings = data.mappings;
    } else {
        throw new Error('Failed to load package mappings');
    }
}

async function ensureVendorSellSIUnits() {
    if (!vendorSellAvailableSIUnits || !vendorSellAvailableSIUnits.length) {
        const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getSIUnits`);
        const data = await response.json();
        if (data.success) {
            vendorSellAvailableSIUnits = data.siUnits;
        }
    }
}

function renderSelectedStoreInfo() {
    const container = document.getElementById('selectedStoreInfo');
    const store = vendorSellSelectedStore;
    
    const logoHtml = store.logo_url 
        ? `<img src="${BASE_URL}${store.logo_url}" alt="${store.name}" class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover">`
        : `<div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 flex items-center justify-center">
             <i class="fas fa-store text-gray-500 text-lg sm:text-xl"></i>
           </div>`;
    
    container.innerHTML = `
        <div class="flex items-center space-x-4">
            ${logoHtml}
            <div class="min-w-0">
                <h4 class="font-semibold text-gray-900 text-sm sm:text-base">${escapeHtml(store.name)}</h4>
                <div class="text-xs sm:text-sm text-gray-700 mt-1">
                    <strong>Product:</strong> ${escapeHtml(vendorSellCurrentProduct.name)}
                </div>
            </div>
        </div>
    `;
}

function renderExistingPricing() {
    const container = document.getElementById('existingPricingList');
    
    const allPricing = [...vendorSellExistingPricing, ...vendorSellPendingPricingEntries];
    
    if (allPricing.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-tag text-3xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No pricing entries for this product in this store.</p>
                <p class="text-sm text-gray-400 mt-1">Add pricing entries to start selling this product.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = '';
    
    allPricing.forEach((pricing, index) => {
        const pricingCard = document.createElement('div');
        const isPending = !pricing.pricing_id;
        pricingCard.className = `${isPending ? 'bg-blue-50 border-blue-200' : 'bg-gray-50'} border border-gray-200 rounded-lg p-4`;
        
        const unitParts = pricing.unit_name ? pricing.unit_name.split(' ') : [];
        const siUnit = unitParts[0] || '';
        const packageName = unitParts.slice(1).join(' ') || '';
        const formattedUnit = `${pricing.package_size} ${siUnit} ${packageName}`.trim();
        
        const categoryColors = {
            'retail': 'bg-blue-100 text-blue-800',
            'wholesale': 'bg-green-100 text-green-800',
            'factory': 'bg-orange-100 text-orange-800'
        };
        
        const categoryDisplay = pricing.price_category.charAt(0).toUpperCase() + pricing.price_category.slice(1);
        const categoryClass = categoryColors[pricing.price_category] || 'bg-gray-100 text-gray-800';
        
        pricingCard.innerHTML = `
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="font-semibold text-gray-900 mb-2">
                        ${escapeHtml(formattedUnit)}
                        ${isPending ? '<span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">New</span>' : ''}
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${categoryClass}">
                            ${categoryDisplay}
                        </span>
                        ${pricing.delivery_capacity ? `
                            <span class="text-gray-600">
                                ${pricing.price_category === 'retail' ? 'Max' : 'Min'}: ${pricing.delivery_capacity}
                            </span>
                        ` : ''}
                    </div>
                </div>
                <div class="flex items-center justify-between sm:justify-end gap-4">
                    <div class="text-right">
                        <div class="text-lg font-bold text-red-600">UGX ${formatNumber(pricing.price)}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        ${isPending ? `
                            <button onclick="editPendingPricing(${vendorSellPendingPricingEntries.indexOf(pricing)})" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="removePendingPricing(${vendorSellPendingPricingEntries.indexOf(pricing)})" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        ` : `
                            <button onclick="editExistingPricing('${pricing.pricing_id}')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteExistingPricing('${pricing.pricing_id}')" 
                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(pricingCard);
    });
}

function goBackToStoreSelection() {
    document.getElementById('pricingManagementSection').classList.add('hidden');
    document.getElementById('saveVendorSellBtn').classList.add('hidden');
    document.getElementById('storeSelectionSection').classList.remove('hidden');
    
    vendorSellSelectedStore = null;
    vendorSellExistingPricing = [];
    vendorSellPendingPricingEntries = [];
    editingPricingIndex = -1;
}

function openPricingEntryModal(editingIndex = -1) {
    editingPricingIndex = editingIndex;
    
    const modal = document.getElementById('pricingEntryModal');
    const title = document.getElementById('pricingEntryModalTitle');
    const form = document.getElementById('pricingEntryForm');
    
    if (editingIndex >= 0) {
        title.textContent = 'Edit Pricing Entry';
        populatePricingForm(vendorSellPendingPricingEntries[editingIndex]);
    } else {
        title.textContent = 'Add Pricing Entry';
        form.reset();
        document.getElementById('pricingPackageSize').value = '1';
        document.getElementById('pricingDeliveryCapacityLabel').textContent = 'Capacity';
    }
    
    modal.classList.remove('hidden');
    initPricingFormDropdowns();
}

function closePricingEntryModal() {
    document.getElementById('pricingEntryModal').classList.add('hidden');
    document.getElementById('pricingEntryForm').reset();
    editingPricingIndex = -1;
}

function initPricingFormDropdowns() {
    initPricingPackageDropdown();
    initPricingSiDropdown();
    
    // Setup price category change handler
    document.getElementById('pricingPriceCategory').addEventListener('change', function() {
        const capacityLabel = document.getElementById('pricingDeliveryCapacityLabel');
        capacityLabel.textContent = this.value === 'retail' ? 'Max. Capacity' :
            (this.value === 'wholesale' || this.value === 'factory' ? 'Min. Capacity' : 'Capacity');
    });
}

function initPricingPackageDropdown() {
    const input = document.getElementById('pricingPkgSearchInput');
    const hiddenInput = document.getElementById('pricingPkgMappingId');
    const dropdown = document.getElementById('pricingPkgDropdown');
    
    function showList(filter = '') {
        dropdown.innerHTML = '';
        let found = false;
        
        vendorSellAvailablePackageMappings.forEach(mapping => {
            if (mapping.package_name.toLowerCase().includes(filter.toLowerCase())) {
                found = true;
                const option = document.createElement('div');
                option.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 text-sm';
                option.textContent = mapping.package_name;
                option.addEventListener('click', () => {
                    hiddenInput.value = mapping.id;
                    input.value = mapping.package_name;
                    dropdown.classList.add('hidden');
                });
                dropdown.appendChild(option);
            }
        });
        
        if (!found) {
            dropdown.innerHTML = '<div class="p-3 text-center text-gray-500 text-sm">No matching packages</div>';
        }
    }
    
    input.addEventListener('focus', () => {
        dropdown.classList.remove('hidden');
        showList(input.value);
    });
    
    input.addEventListener('input', () => {
        dropdown.classList.remove('hidden');
        showList(input.value);
    });
    
    document.addEventListener('click', (e) => {
        if (!document.getElementById('pricingEntryModal').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

function initPricingSiDropdown() {
    const input = document.getElementById('pricingSiSearchInput');
    const hiddenInput = document.getElementById('pricingSiUnitId');
    const dropdown = document.getElementById('pricingSiDropdown');
    
    function showList(filter = '') {
        dropdown.innerHTML = '';
        let found = false;
        
        vendorSellAvailableSIUnits.forEach(unit => {
            if (unit.si_unit.toLowerCase().includes(filter.toLowerCase())) {
                found = true;
                const option = document.createElement('div');
                option.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 text-sm';
                option.textContent = unit.si_unit;
                option.addEventListener('click', () => {
                    hiddenInput.value = unit.id;
                    input.value = unit.si_unit;
                    dropdown.classList.add('hidden');
                });
                dropdown.appendChild(option);
            }
        });
        
        if (!found) {
            dropdown.innerHTML = '<div class="p-3 text-center text-gray-500 text-sm">No matching SI units found</div>';
        }
    }
    
    input.addEventListener('focus', () => {
        dropdown.classList.remove('hidden');
        showList(input.value);
    });
    
    input.addEventListener('input', () => {
        dropdown.classList.remove('hidden');
        showList(input.value);
    });
    
    document.addEventListener('click', (e) => {
        if (!document.getElementById('pricingEntryModal').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

function populatePricingForm(pricingData) {
    document.getElementById('pricingPkgSearchInput').value = pricingData.package_name || '';
    document.getElementById('pricingPkgMappingId').value = pricingData.package_mapping_id || '';
    document.getElementById('pricingSiSearchInput').value = pricingData.si_unit || '';
    document.getElementById('pricingSiUnitId').value = pricingData.si_unit_id || '';
    document.getElementById('pricingPackageSize').value = pricingData.package_size || '1';
    document.getElementById('pricingPriceCategory').value = pricingData.price_category || '';
    document.getElementById('pricingPrice').value = pricingData.price || '';
    document.getElementById('pricingDeliveryCapacity').value = pricingData.delivery_capacity || '';
    
    // Update capacity label
    const capacityLabel = document.getElementById('pricingDeliveryCapacityLabel');
    if (pricingData.price_category === 'retail') {
        capacityLabel.textContent = 'Max. Capacity';
    } else if (pricingData.price_category === 'wholesale' || pricingData.price_category === 'factory') {
        capacityLabel.textContent = 'Min. Capacity';
    }
}

function editPendingPricing(index) {
    openPricingEntryModal(index);
}

function removePendingPricing(index) {
    vendorSellPendingPricingEntries.splice(index, 1);
    renderExistingPricing();
}

async function editExistingPricing(pricingId) {
    const pricing = vendorSellExistingPricing.find(p => p.pricing_id === pricingId);
    if (!pricing) return;
    
    // Convert to pending entry format
    const pendingEntry = {
        package_mapping_id: pricing.package_mapping_id,
        si_unit_id: pricing.si_unit_id,
        package_size: pricing.package_size,
        price_category: pricing.price_category,
        price: pricing.price,
        delivery_capacity: pricing.delivery_capacity,
        package_name: pricing.unit_name ? pricing.unit_name.split(' ').slice(1).join(' ') : '',
        si_unit: pricing.unit_name ? pricing.unit_name.split(' ')[0] : '',
        unit_name: pricing.unit_name,
        original_pricing_id: pricingId
    };
    
    vendorSellPendingPricingEntries.push(pendingEntry);
    
    // Remove from existing pricing (will be re-added when saved)
    await deleteExistingPricing(pricingId, false);
}

async function deleteExistingPricing(pricingId, showConfirm = true) {
    if (showConfirm && !confirm('Are you sure you want to delete this pricing entry?')) {
        return;
    }
    
    try {
        const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=deletePricing`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ pricing_id: pricingId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove from local array
            vendorSellExistingPricing = vendorSellExistingPricing.filter(p => p.pricing_id !== pricingId);
            renderExistingPricing();
            
            if (showConfirm) {
                showToast('Pricing deleted successfully', 'success');
            }
        } else {
            showToast(data.error || 'Failed to delete pricing', 'error');
        }
    } catch (error) {
        console.error('Error deleting pricing:', error);
        showToast('Error deleting pricing', 'error');
    }
}

async function saveVendorSellPricing() {
    if (vendorSellPendingPricingEntries.length === 0) {
        showToast('No new pricing entries to save', 'error');
        return;
    }
    
    const btn = document.getElementById('saveVendorSellBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        const response = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=addProductToStore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                store_id: vendorSellSelectedStore.id,
                product_id: vendorSellCurrentProduct.id,
                line_items: vendorSellPendingPricingEntries
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Product pricing saved successfully', 'success');
            closeVendorSellModal();
        } else {
            showToast(data.error || 'Failed to save pricing', 'error');
        }
    } catch (error) {
        console.error('Error saving pricing:', error);
        showToast('Error saving pricing', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addPricingLineBtn').addEventListener('click', () => openPricingEntryModal());
    document.getElementById('saveVendorSellBtn').addEventListener('click', saveVendorSellPricing);
    
    // Pricing entry form submission
    document.getElementById('pricingEntryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const pmId = document.getElementById('pricingPkgMappingId').value;
        const siId = document.getElementById('pricingSiUnitId').value;
        const pkgSize = formData.get('package_size');
        const priceCat = formData.get('price_category');
        const price = formData.get('price');
        const cap = formData.get('delivery_capacity');
        
        if (!pmId || !siId || !price || !priceCat) {
            showToast('Please complete all required fields', 'error');
            return;
        }
        
        const pkg = vendorSellAvailablePackageMappings.find(p => p.id === pmId);
        const si = vendorSellAvailableSIUnits.find(s => s.id === siId);
        
        const pricingEntry = {
            package_mapping_id: pmId,
            si_unit_id: siId,
            package_size: pkgSize,
            price_category: priceCat,
            price: parseFloat(price),
            delivery_capacity: cap || null,
            package_name: pkg ? pkg.package_name : '',
            si_unit: si ? si.si_unit : '',
            unit_name: si && pkg ? `${si.si_unit} ${pkg.package_name}` : ''
        };
        
        if (editingPricingIndex >= 0) {
            vendorSellPendingPricingEntries[editingPricingIndex] = pricingEntry;
        } else {
            vendorSellPendingPricingEntries.push(pricingEntry);
        }
        
        renderExistingPricing();
        closePricingEntryModal();
        showToast('Pricing entry added successfully', 'success');
    });
    
    // Close modals when clicking outside
    document.getElementById('vendorSellModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeVendorSellModal();
        }
    });
    
    document.getElementById('pricingEntryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closePricingEntryModal();
        }
    });
});
</script>

<style>
#pricingPkgSearchInput:focus,
#pricingSiSearchInput:focus,
#pricingPackageSize:focus,
#pricingPrice:focus,
#pricingDeliveryCapacity:focus,
#pricingPriceCategory:focus {
    outline: none;
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

@media (max-width: 640px) {
    #vendorSellModal .relative,
    #pricingEntryModal .relative {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
    }
    
    #vendorSellModal .overflow-y-auto,
    #pricingEntryModal .overflow-y-auto {
        max-height: calc(100vh - 200px);
    }
}
</style>
