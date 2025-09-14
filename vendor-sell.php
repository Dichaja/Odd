<style>
    [x-cloak] {
        display: none !important
    }
</style>

<div x-data="vendorSell()" x-init="init()">
    <div x-show="isOpen" x-cloak id="vendorSellModal" class="fixed inset-0 z-50" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="close()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-6xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div
                    class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900" x-text="headerTitle"></h3>
                    <button @click="close()"
                        class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-6">
                    <div x-show="loading" class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i data-lucide="loader-2" class="w-10 h-10 text-red-500 mx-auto mb-4 animate-spin"></i>
                            <p class="text-gray-600">Loading your stores...</p>
                        </div>
                    </div>

                    <div x-show="!loading && stores.length===0" class="text-center py-12">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="store" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-2">No Stores Found</h3>
                        <p class="text-gray-600 mb-6 max-w-md mx-auto">You need to create a store before you can sell
                            products. Create your first store to get started.</p>
                        <a href="<?= BASE_URL ?>account/zzimba-stores"
                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>Create Your First Store
                        </a>
                    </div>

                    <div x-show="!loading && stores.length>0 && !selectedStore" class="space-y-6">
                        <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Select Store</h4>
                            <p class="text-sm text-gray-600 mb-6">Choose which store you want to sell this product in
                            </p>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <template x-for="store in stores" :key="store.id">
                                    <div @click="selectStore(store)"
                                        class="bg-white border border-gray-200 rounded-xl p-4 sm:p-6 hover:border-red-300 hover:shadow-md transition-all cursor-pointer">
                                        <div class="flex items-center space-x-4">
                                            <template x-if="store.logo_url">
                                                <img :src="`${BASE_URL}${store.logo_url}`" :alt="store.name"
                                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover">
                                            </template>
                                            <template x-if="!store.logo_url">
                                                <div
                                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <i data-lucide="store" class="w-7 h-7 text-gray-500"></i>
                                                </div>
                                            </template>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-semibold text-gray-900 text-sm sm:text-base truncate"
                                                    x-text="store.name"></h5>
                                                <p class="text-xs sm:text-sm text-gray-600 truncate"
                                                    x-text="store.district"></p>
                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    <span class="text-xs px-2 py-1 rounded-full font-medium"
                                                        :class="store.status==='active' ? 'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'"
                                                        x-text="store.status.charAt(0).toUpperCase()+store.status.slice(1)"></span>
                                                    <span class="text-xs text-gray-500 capitalize"
                                                        x-text="store.role"></span>
                                                </div>
                                            </div>
                                            <i data-lucide="chevron-right"
                                                class="w-6 h-6 text-gray-400 flex-shrink-0"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <template x-if="!loading && selectedStore">
                        <div class="space-y-6">
                            <div class="bg-gray-50 rounded-xl p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <template x-if="selectedStore && selectedStore.logo_url">
                                            <img :src="`${BASE_URL}${selectedStore.logo_url}`"
                                                :alt="selectedStore ? selectedStore.name : ''"
                                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover">
                                        </template>
                                        <template x-if="!(selectedStore && selectedStore.logo_url)">
                                            <div
                                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 flex items-center justify-center">
                                                <i data-lucide="store" class="w-7 h-7 text-gray-500"></i>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base"
                                                x-text="selectedStore ? selectedStore.name : ''"></h4>
                                            <div class="text-xs sm:text-sm text-gray-700 mt-1">
                                                <strong>Product:</strong> <span x-text="product.name"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="goBackToStoreSelection()"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>Change Store
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-xl">
                                <div class="p-4 sm:p-6 border-b border-gray-100">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <h5 class="text-lg font-semibold text-gray-900">Current Pricing</h5>
                                            <p class="text-sm text-gray-600 mt-1">Existing pricing entries for this
                                                product in the selected store</p>
                                        </div>
                                        <button type="button" @click="openPricingEntryModal()"
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center text-sm sm:text-base">
                                            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>Add Pricing
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4 sm:p-6">
                                    <div class="space-y-4">
                                        <template x-if="displayPricing().length===0">
                                            <div class="text-center py-8">
                                                <i data-lucide="tag" class="w-8 h-8 text-gray-300 mx-auto mb-4"></i>
                                                <p class="text-gray-500">No pricing entries for this product in this
                                                    store.</p>
                                                <p class="text-sm text-gray-400 mt-1">Add pricing entries to start
                                                    selling this product.</p>
                                            </div>
                                        </template>

                                        <template x-for="p in displayPricing()" :key="p._key">
                                            <div
                                                :class="(p._pending ? 'bg-blue-50 border-blue-200' : 'bg-gray-50') + ' border border-gray-200 rounded-lg p-4'">
                                                <div class="flex flex-col">
                                                    <div
                                                        class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                                        <div class="flex-1">
                                                            <div class="font-semibold text-gray-900 mb-2">
                                                                <span x-text="formattedUnit(p)"></span>
                                                                <span x-show="p._pending"
                                                                    class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">New</span>
                                                            </div>
                                                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                                                <span
                                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                                    :class="categoryClass(p.price_category)"
                                                                    x-text="categoryLabel(p.price_category)"></span>
                                                                <template x-if="p.delivery_capacity">
                                                                    <span class="text-gray-600"
                                                                        x-text="(p.price_category==='retail' ? 'Max' : 'Min') + ': ' + p.delivery_capacity"></span>
                                                                </template>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                                            <div class="text-right">
                                                                <div class="text-lg font-bold text-red-600"
                                                                    x-text="'UGX ' + formatNumber(p.price)"></div>
                                                            </div>
                                                            <div class="hidden sm:flex flex-col items-end gap-2">
                                                                <template x-if="p._pending">
                                                                    <div class="flex flex-col gap-2">
                                                                        <button @click="editPendingPricing(p)"
                                                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                                                            <i data-lucide="pencil"
                                                                                class="w-4 h-4 mr-1"></i>Edit
                                                                        </button>
                                                                        <button @click="removePendingPricing(p)"
                                                                            class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                                                            <i data-lucide="trash-2"
                                                                                class="w-4 h-4 mr-1"></i>Remove
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                                <template x-if="!p._pending">
                                                                    <div class="flex flex-col gap-2">
                                                                        <button
                                                                            @click="editExistingPricing(p.pricing_id)"
                                                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                                                            <i data-lucide="pencil"
                                                                                class="w-4 h-4 mr-1"></i>Edit
                                                                        </button>
                                                                        <button
                                                                            @click="confirmDeleteExistingPricing(p.pricing_id)"
                                                                            class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center">
                                                                            <i data-lucide="trash-2"
                                                                                class="w-4 h-4 mr-1"></i>Delete
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="flex sm:hidden items-center justify-center gap-5 mt-3 pt-3 border-t border-gray-200">
                                                        <template x-if="p._pending">
                                                            <div class="flex items-center gap-5">
                                                                <button @click="editPendingPricing(p)"
                                                                    class="flex items-center justify-center w-12 h-12 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors">
                                                                    <i data-lucide="pencil" class="w-7 h-7"></i>
                                                                </button>
                                                                <button @click="removePendingPricing(p)"
                                                                    class="flex items-center justify-center w-12 h-12 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors">
                                                                    <i data-lucide="trash-2" class="w-7 h-7"></i>
                                                                </button>
                                                            </div>
                                                        </template>
                                                        <template x-if="!p._pending">
                                                            <div class="flex items-center gap-5">
                                                                <button @click="editExistingPricing(p.pricing_id)"
                                                                    class="flex items-center justify-center w-12 h-12 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors">
                                                                    <i data-lucide="pencil" class="w-7 h-7"></i>
                                                                </button>
                                                                <button
                                                                    @click="confirmDeleteExistingPricing(p.pricing_id)"
                                                                    class="flex items-center justify-center w-12 h-12 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-full transition-colors">
                                                                    <i data-lucide="trash-2" class="w-7 h-7"></i>
                                                                </button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedStore"
                        class="flex flex-col sm:flex-row justify-end p-4 sm:p-6 border-t border-gray-200 gap-3">
                        <button @click="close()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="isPricingOpen" x-cloak id="pricingEntryModal" class="fixed inset-0 z-[60]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="closePricingEntryModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div
                    class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-green-50 to-green-100">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" x-text="pricingEntryTitle"></h3>
                        <p class="text-sm text-gray-600 mt-1">Configure pricing details for this product</p>
                    </div>
                    <button @click="closePricingEntryModal()"
                        class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-4 sm:p-6">
                    <form @submit.prevent="submitPricingEntry" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Package *</label>
                                <div class="relative">
                                    <input type="text" x-model="form.package_search" @focus="pkgOpen=true"
                                        @input="pkgOpen=true" @keydown.escape.stop="pkgOpen=false"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                        placeholder="Search package..." autocomplete="off">
                                    <input type="hidden" x-model="form.package_mapping_id">
                                    <div x-show="pkgOpen" x-cloak @click.outside="pkgOpen=false"
                                        class="absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 z-40">
                                        <template x-if="filteredPackages().length===0">
                                            <div class="p-3 text-center text-gray-500 text-sm">No matching packages
                                            </div>
                                        </template>
                                        <template x-for="m in filteredPackages()" :key="m.id">
                                            <div class="px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 text-sm"
                                                @mousedown.prevent="choosePackage(m)" x-text="m.package_name"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unit of Measure *</label>
                                <div class="relative">
                                    <input type="text" x-model="form.si_search" @focus="siOpen=true"
                                        @input="siOpen=true" @keydown.escape.stop="siOpen=false"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                                        placeholder="Search SI unit..." autocomplete="off">
                                    <input type="hidden" x-model="form.si_unit_id">
                                    <div x-show="siOpen" x-cloak @click.outside="siOpen=false"
                                        class="absolute top-full left-0 right-0 max-h-48 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg mt-1 z-40">
                                        <template x-if="filteredSiUnits().length===0">
                                            <div class="p-3 text-center text-gray-500 text-sm">No matching SI units
                                                found</div>
                                        </template>
                                        <template x-for="u in filteredSiUnits()" :key="u.id">
                                            <div class="px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 text-sm"
                                                @mousedown.prevent="chooseSi(u)" x-text="u.si_unit"></div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Size *</label>
                                <input type="text" x-model="form.package_size" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Price Category *</label>
                                <select x-model="form.price_category" @change="updateCapacityLabel()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                                    <option value="">-- Select Category --</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="factory">Factory</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Price (UGX) *</label>
                                <input type="number" step="any" min="1" x-model.number="form.price" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2"
                                    x-text="capacityLabel"></label>
                                <input type="number" x-model="form.delivery_capacity"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" @click="closePricingEntryModal()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                <i data-lucide="save" class="w-5 h-5 mr-2"></i>Save Entry
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div x-show="isConfirmOpen" x-cloak id="confirmationModal" class="fixed inset-0 z-[70]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="closeConfirmationModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl">
                <div class="p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                        <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 text-center mb-2" x-text="confirmTitle">Confirm
                        Action</h3>
                    <p class="text-sm text-gray-600 text-center mb-6" x-text="confirmMessage"></p>
                    <div class="flex gap-3">
                        <button @click="closeConfirmationModal()"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button @click="confirmAction()"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                            x-text="confirmText">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (!window.BASE_URL) { window.BASE_URL = '<?= BASE_URL ?>'; }
    function vendorSell() {
        return {
            isOpen: false, isPricingOpen: false, isConfirmOpen: false, headerTitle: 'Sell Product', pricingEntryTitle: 'Add Pricing Entry', confirmTitle: '', confirmMessage: '', confirmText: 'Delete', confirmCb: null,
            product: { id: null, name: '' }, stores: [], selectedStore: null, loading: false,
            availablePackages: [], availableSI: [], existingPricing: [], pendingPricing: [], hiddenPricing: [], editingIndex: -1, originalPricing: null,
            form: { package_search: '', package_mapping_id: '', si_search: '', si_unit_id: '', package_size: '1', price_category: '', price: '', delivery_capacity: '' },
            pkgOpen: false, siOpen: false, capacityLabel: 'Capacity',
            init() {
                window.openVendorSellModal = (id, name) => { this.open(id, name) };
                window.closeVendorSellModal = () => { this.close() };
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            },
            refreshIcons() { try { if (window.lucide && lucide.createIcons) lucide.createIcons(); } catch (e) { } },
            async open(productId, productName) {
                const ok = await (typeof checkUserSession === 'function' ? checkUserSession() : true);
                if (!ok) { if (typeof openAuthModal === 'function') openAuthModal(); return; }
                this.reset(); this.product = { id: productId, name: productName }; this.headerTitle = `Sell "${productName}"`; this.isOpen = true; this.loading = true; this.refreshIcons();
                await this.loadUserStores(); this.refreshIcons();
            },
            close() { this.reset(); this.isOpen = false },
            reset() {
                this.loading = false; this.stores = []; this.selectedStore = null; this.availablePackages = []; this.availableSI = [];
                this.existingPricing = []; this.pendingPricing = []; this.hiddenPricing = []; this.editingIndex = -1; this.originalPricing = null;
                this.form = { package_search: '', package_mapping_id: '', si_search: '', si_unit_id: '', package_size: '1', price_category: '', price: '', delivery_capacity: '' };
                this.pkgOpen = false; this.siOpen = false; this.capacityLabel = 'Capacity';
            },
            async loadUserStores() {
                try { const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getUserStores`); const d = await r.json(); this.stores = (d.success && d.stores) ? d.stores : []; }
                catch (e) { this.stores = []; }
                finally { this.loading = false; this.refreshIcons(); }
            },
            async selectStore(store) {
                this.selectedStore = store; this.pendingPricing = []; this.hiddenPricing = []; this.loading = true;
                try { await Promise.all([this.loadExistingPricing(), this.loadPackages(), this.loadSIUnits()]); } catch (e) { }
                this.loading = false; this.refreshIcons();
            },
            async loadExistingPricing() {
                const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getExistingPricing&store_id=${this.selectedStore.id}&product_id=${this.product.id}`);
                const d = await r.json(); this.existingPricing = d.success ? (d.pricing || []) : [];
            },
            async loadPackages() {
                const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getPackageNamesForProduct&product_id=${this.product.id}`);
                const d = await r.json(); if (!d.success) throw new Error(); this.availablePackages = d.mappings || [];
            },
            async loadSIUnits() {
                if (this.availableSI.length) return;
                const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getSIUnits`); const d = await r.json(); if (d.success) this.availableSI = d.siUnits || [];
            },
            goBackToStoreSelection() { this.selectedStore = null; this.existingPricing = []; this.pendingPricing = []; this.hiddenPricing = []; this.editingIndex = -1; this.originalPricing = null; this.refreshIcons(); },
            displayPricing() {
                const visible = this.existingPricing.filter(p => !this.hiddenPricing.some(h => h.pricing_id === p.pricing_id)).map(p => Object.assign({ _pending: false, _key: 'ex-' + p.pricing_id }, p));
                const pending = this.pendingPricing.map((p, i) => Object.assign({ _pending: true, _key: 'pe-' + i }, p));
                return [...visible, ...pending];
            },
            formattedUnit(p) {
                const parts = (p.unit_name || '').split(' '); const si = parts[0] || (p.si_unit || ''); const pkg = parts.slice(1).join(' ') || (p.package_name || ''); const size = p.package_size || '1'; return `${size} ${si} ${pkg}`.trim();
            },
            categoryLabel(c) { return c ? c.charAt(0).toUpperCase() + c.slice(1) : '' },
            categoryClass(c) { if (c === 'retail') return 'bg-blue-100 text-blue-800'; if (c === 'wholesale') return 'bg-green-100 text-green-800'; if (c === 'factory') return 'bg-orange-100 text-orange-800'; return 'bg-gray-100 text-gray-800' },
            formatNumber(n) { return n == null ? '' : Number(n).toLocaleString('en-US') },
            openPricingEntryModal(editIndex = -1, existingId = null) {
                this.pricingEntryTitle = 'Add Pricing Entry'; this.editingIndex = -1; this.originalPricing = null;
                this.form = { package_search: '', package_mapping_id: '', si_search: '', si_unit_id: '', package_size: '1', price_category: '', price: '', delivery_capacity: '' };
                if (editIndex >= 0) { this.pricingEntryTitle = 'Edit Pricing Entry'; this.editingIndex = editIndex; this.originalPricing = Object.assign({}, this.pendingPricing[editIndex]); this.populateForm(this.pendingPricing[editIndex]); }
                else if (existingId) { this.pricingEntryTitle = 'Edit Pricing Entry'; const h = this.hiddenPricing.find(p => p.pricing_id === existingId); if (h) { this.originalPricing = Object.assign({}, h); this.populateForm(h); } }
                this.updateCapacityLabel(); this.isPricingOpen = true; this.refreshIcons();
            },
            closePricingEntryModal() {
                if (this.editingIndex >= 0 && this.originalPricing) { this.pendingPricing[this.editingIndex] = this.originalPricing; }
                else if (this.originalPricing && this.originalPricing.pricing_id) { const i = this.hiddenPricing.findIndex(p => p.pricing_id === this.originalPricing.pricing_id); if (i >= 0) this.hiddenPricing.splice(i, 1); }
                this.isPricingOpen = false; this.editingIndex = -1; this.originalPricing = null; this.refreshIcons();
            },
            populateForm(d) {
                this.form.package_search = d.package_name || ''; this.form.package_mapping_id = d.package_mapping_id || ''; this.form.si_search = d.si_unit || ''; this.form.si_unit_id = d.si_unit_id || '';
                this.form.package_size = d.package_size || '1'; this.form.price_category = d.price_category || ''; this.form.price = d.price || ''; this.form.delivery_capacity = d.delivery_capacity || '';
            },
            updateCapacityLabel() {
                const c = this.form.price_category; this.capacityLabel = c === 'retail' ? 'Max. Capacity' : (c === 'wholesale' || c === 'factory' ? 'Min. Capacity' : 'Capacity');
            },
            filteredPackages() { const q = (this.form.package_search || '').toLowerCase(); return this.availablePackages.filter(m => (m.package_name || '').toLowerCase().includes(q)); },
            filteredSiUnits() { const q = (this.form.si_search || '').toLowerCase(); return this.availableSI.filter(u => (u.si_unit || '').toLowerCase().includes(q)); },
            choosePackage(m) { this.form.package_mapping_id = m.id; this.form.package_search = m.package_name; this.pkgOpen = false; },
            chooseSi(u) { this.form.si_unit_id = u.id; this.form.si_search = u.si_unit; this.siOpen = false; },
            submitPricingEntry() {
                const pmId = this.form.package_mapping_id, siId = this.form.si_unit_id, pkgSize = this.form.package_size, priceCat = this.form.price_category, price = this.form.price, cap = this.form.delivery_capacity;
                if (!pmId || !siId || !price || !priceCat) { if (typeof showToast === 'function') showToast('Please complete all required fields', 'error'); return; }
                const pkg = this.availablePackages.find(p => p.id == pmId), si = this.availableSI.find(s => s.id == siId);
                const entry = { package_mapping_id: pmId, si_unit_id: siId, package_size: pkgSize, price_category: priceCat, price: parseFloat(price), delivery_capacity: cap || null, package_name: pkg ? pkg.package_name : '', si_unit: si ? si.si_unit : '', unit_name: si && pkg ? `${si.si_unit} ${pkg.package_name}` : '' };
                if (this.originalPricing && this.originalPricing.pricing_id) entry.pricing_id = this.originalPricing.pricing_id;
                if (this.editingIndex >= 0) this.pendingPricing[this.editingIndex] = entry; else this.pendingPricing.push(entry);
                this.saveAllPendingPricing(); this.isPricingOpen = false; if (typeof showToast === 'function') showToast('Pricing entry saved successfully', 'success'); this.originalPricing = null;
            },
            editPendingPricing(p) { const i = this.pendingPricing.findIndex(x => x === p); this.openPricingEntryModal(i); },
            removePendingPricing(p) { const i = this.pendingPricing.findIndex(x => x === p); if (i >= 0) { this.pendingPricing.splice(i, 1); } },
            editExistingPricing(pricingId) {
                const pr = this.existingPricing.find(p => p.pricing_id === pricingId); if (!pr) return;
                const entry = { package_mapping_id: pr.package_mapping_id, si_unit_id: pr.si_unit_id, package_size: pr.package_size, price_category: pr.price_category, price: pr.price, delivery_capacity: pr.delivery_capacity, package_name: pr.unit_name ? pr.unit_name.split(' ').slice(1).join(' ') : '', si_unit: pr.unit_name ? pr.unit_name.split(' ')[0] : '', unit_name: pr.unit_name, pricing_id: pricingId };
                this.hiddenPricing.push(pr); this.openPricingEntryModal(-1, pricingId);
            },
            confirmDeleteExistingPricing(id) { this.showConfirm('Delete Pricing Entry', 'Are you sure you want to delete this pricing entry? This action cannot be undone.', () => this.deleteExistingPricing(id), 'Delete'); },
            async deleteExistingPricing(id) {
                try {
                    const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=deletePricing`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pricing_id: id }) });
                    const d = await r.json();
                    if (d.success) { this.existingPricing = this.existingPricing.filter(p => p.pricing_id !== id); if (typeof showToast === 'function') showToast('Pricing deleted successfully', 'success'); }
                    else { if (typeof showToast === 'function') showToast(d.error || 'Failed to delete pricing', 'error'); }
                } catch (e) { if (typeof showToast === 'function') showToast('Error deleting pricing', 'error'); }
                finally { this.closeConfirmationModal(); }
            },
            async saveAllPendingPricing() {
                if (this.pendingPricing.length === 0) { if (typeof showToast === 'function') showToast('No new pricing entries to save', 'error'); return; }
                try {
                    const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=addProductToStore`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ store_id: this.selectedStore.id, product_id: this.product.id, line_items: this.pendingPricing }) });
                    const d = await r.json();
                    if (d.success) {
                        if (typeof showToast === 'function') showToast('Product pricing saved successfully', 'success');
                        for (const e of this.pendingPricing) { if (e.pricing_id) await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=deletePricing`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ pricing_id: e.pricing_id }) }); }
                        await this.loadExistingPricing(); this.pendingPricing = []; this.hiddenPricing = [];
                    } else { if (typeof showToast === 'function') showToast(d.error || 'Failed to save pricing', 'error'); }
                } catch (e) { if (typeof showToast === 'function') showToast('Error saving pricing', 'error'); }
                finally { this.refreshIcons(); }
            },
            showConfirm(t, m, cb, txt = 'Delete') { this.confirmTitle = t; this.confirmMessage = m; this.confirmText = txt; this.confirmCb = cb; this.isConfirmOpen = true; this.refreshIcons(); },
            closeConfirmationModal() { this.isConfirmOpen = false; this.confirmCb = null; },
            confirmAction() { if (typeof this.confirmCb === 'function') this.confirmCb(); }
        }
    }
</script>

<style>
    @media (max-width:640px) {
        .space-y-4>*+* {
            margin-top: 1rem
        }

        button {
            min-height: 44px;
            padding: .75rem 1rem
        }

        input,
        select {
            min-height: 44px;
            padding: .75rem
        }
    }
</style>