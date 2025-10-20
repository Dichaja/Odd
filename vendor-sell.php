<style>
    [x-cloak] {
        display: none !important;
    }

    .custom-select {
        position: relative;
    }

    .custom-select__btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        background: #fff;
        transition: box-shadow 0.2s;
    }

    .custom-select__btn:focus {
        outline: 0;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    .custom-select__panel {
        position: absolute;
        inset-inline: 0;
        top: 100%;
        margin-top: 0.25rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        max-height: 14rem;
        overflow: auto;
        z-index: 50;
    }

    .custom-select__opt {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .custom-select__opt:hover {
        background: #f9fafb;
    }

    .modal-panel {
        display: flex;
        flex-direction: column;
        max-height: calc(100dvh - 2rem);
        overscroll-behavior: contain;
        border-radius: 1rem;
    }

    @supports not (height:1dvh) {
        .modal-panel {
            max-height: calc(100vh - 2rem);
        }
    }

    .modal-scroll {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .tab-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid transparent;
    }

    .tab-active {
        background: #111827;
        color: #fff;
        border-color: #111827;
    }

    .tab-idle {
        background: #fff;
        color: #374151;
        border-color: #d1d5db;
    }

    @media (max-width: 640px) {
        .space-y-4>*+* {
            margin-top: 1rem;
        }

        button {
            min-height: 44px;
            padding: 0.75rem 1rem;
        }

        input,
        select {
            min-height: 44px;
            padding: 0.75rem;
        }
    }

    html.dark .custom-select__btn {
        background: #111827;
        border-color: #374151;
        color: #e5e7eb;
    }

    html.dark .custom-select__panel {
        background: #0f172a;
        border-color: #1f2937;
        color: #e5e7eb;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    html.dark .custom-select__opt:hover {
        background: #111827;
    }
</style>

<div x-data="vendorSell()" x-init="init()" x-cloak>
    <div id="alertContainer" class="fixed top-4 right-4 z-[1500] space-y-2 pointer-events-none"></div>

    <div x-show="isOpen" id="vendorSellModal" class="fixed inset-0 z-[1200]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 flex items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[94%] lg:max-w-6xl bg-white dark:bg-slate-900 rounded-none sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="p-4 sm:p-6 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100 dark:from-slate-800 dark:to-slate-900">
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 dark:text-slate-100 whitespace-nowrap overflow-hidden text-ellipsis max-w-[calc(100%-3rem)]"
                        x-text="headerTitle"></h3>
                    <button @click="close()"
                        class="text-gray-500 dark:text-slate-300 hover:text-gray-700 dark:hover:text-white p-2 rounded-full hover:bg-white/60 dark:hover:bg-white/10">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="p-0 sm:p-6 modal-scroll">
                    <div x-show="loading" class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <i data-lucide="loader-2" class="w-10 h-10 text-red-500 mx-auto mb-4 animate-spin"></i>
                            <p class="text-gray-600 dark:text-slate-300">Loading your stores...</p>
                        </div>
                    </div>

                    <div x-show="!loading && stores.length===0" class="text-center py-12 px-4">
                        <div
                            class="w-16 h-16 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="store" class="w-8 h-8 text-red-600 dark:text-red-400"></i>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-slate-100 mb-2">No Stores Found</h3>
                        <p class="text-gray-600 dark:text-slate-300 mb-6 max-w-md mx-auto">
                            You need to create a store before you can sell products. Create your first store to get
                            started.
                        </p>
                        <a href="<?= BASE_URL ?>account/zzimba-stores"
                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                            Create Your First Store
                        </a>
                    </div>

                    <div x-show="!loading && stores.length>0 && !selectedStore" class="space-y-6 p-4 sm:p-0">
                        <div class="bg-gray-50 dark:bg-slate-800 rounded-none sm:rounded-xl p-4 sm:p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-2">Select Store</h4>
                            <p class="text-sm text-gray-600 dark:text-slate-300 mb-4 sm:mb-6">
                                Choose which store you want to sell this product in
                            </p>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                                <template x-for="store in stores" :key="store.id">
                                    <button type="button" @click="selectStore(store)"
                                        class="text-left bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl p-4 sm:p-6 hover:border-red-300 dark:hover:border-red-400 hover:shadow-md transition-all cursor-pointer">
                                        <div class="flex items-center space-x-4">
                                            <template x-if="store.logo_url">
                                                <img :src="`${BASE_URL}${store.logo_url}`" :alt="store.name"
                                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover" />
                                            </template>
                                            <template x-if="!store.logo_url">
                                                <div
                                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 dark:bg-slate-700 flex items-center justify-center">
                                                    <i data-lucide="store"
                                                        class="w-7 h-7 text-gray-500 dark:text-slate-300"></i>
                                                </div>
                                            </template>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-semibold text-gray-900 dark:text-slate-100 text-sm sm:text-base truncate"
                                                    x-text="store.name"></h5>
                                                <p class="text-xs sm:text-sm text-gray-600 dark:text-slate-300 truncate"
                                                    x-text="store.district"></p>
                                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                                    <span class="text-xs px-2 py-1 rounded-full font-medium"
                                                        :class="store.status==='active' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300'"
                                                        x-text="store.status.charAt(0).toUpperCase()+store.status.slice(1)"></span>
                                                    <span class="text-xs text-gray-500 dark:text-slate-400 capitalize"
                                                        x-text="store.role"></span>
                                                </div>
                                            </div>
                                            <i data-lucide="chevron-right"
                                                class="w-6 h-6 text-gray-400 dark:text-slate-400 flex-shrink-0"></i>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <template x-if="!loading && selectedStore">
                        <div class="space-y-6 p-4 sm:p-0 pb-24 md:pb-0">
                            <div class="bg-gray-50 dark:bg-slate-800 rounded-none sm:rounded-xl p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <template x-if="selectedStore && selectedStore.logo_url">
                                            <img :src="`${BASE_URL}${selectedStore.logo_url}`"
                                                :alt="selectedStore ? selectedStore.name : ''"
                                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-full object-cover" />
                                        </template>
                                        <template x-if="!(selectedStore && selectedStore.logo_url)">
                                            <div
                                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-200 dark:bg-slate-700 flex items-center justify-center">
                                                <i data-lucide="store"
                                                    class="w-7 h-7 text-gray-500 dark:text-slate-300"></i>
                                            </div>
                                        </template>
                                        <div class="min-w-0">
                                            <h4 class="font-semibold text-gray-900 dark:text-slate-100 text-sm sm:text-base truncate"
                                                x-text="selectedStore ? selectedStore.name : ''"></h4>
                                            <div class="text-xs sm:text-sm text-gray-700 dark:text-slate-300 mt-1">
                                                <strong>Product:</strong>
                                                <span x-text="product.name"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="goBackToStoreSelection()"
                                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm font-medium flex items-center">
                                        <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
                                        Change Store
                                    </button>
                                </div>
                            </div>

                            <div
                                class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-none sm:rounded-xl">
                                <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-slate-800">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div>
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-slate-100">
                                                Manage Pricing - <span x-text="product.name"></span>
                                            </h5>
                                            <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                                Create and edit pricing entries for this product in this store
                                            </p>
                                        </div>
                                        <button type="button" @click="openStepper('new')"
                                            class="px-4 py-2 bg-gray-900 dark:bg-black text-white rounded-lg hover:bg-black dark:hover:bg-black/80 transition-colors flex items-center text-sm sm:text-base mx-auto sm:mx-0">
                                            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                                            Add Pricing
                                        </button>
                                    </div>
                                </div>

                                <div class="p-4 sm:p-6">
                                    <div class="md:hidden space-y-3">
                                        <template x-if="pricingList.length===0">
                                            <div class="px-4 py-6 text-center text-gray-500 dark:text-slate-300">No
                                                pricing entries</div>
                                        </template>

                                        <template x-for="(pr,idx) in pricingList"
                                            :key="(pr.pricing_id||'new')+'-m-'+idx">
                                            <div
                                                class="rounded-2xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="text-xs text-gray-500 dark:text-slate-400">Unit -
                                                            Size</div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100"
                                                            x-text="(pr.package_size||'-')+' '+(pr.si_unit||labelForUnit(pr.si_unit_id)||'')">
                                                        </div>
                                                    </div>
                                                    <button @click="openStepper('edit', idx)"
                                                        class="p-2 rounded-lg bg-gray-100 dark:bg-slate-8 00 hover:bg-gray-200 dark:hover:bg-slate-700">
                                                        <i data-lucide="pen-line"
                                                            class="w-4 h-4 text-gray-700 dark:text-slate-200"></i>
                                                    </button>
                                                </div>

                                                <div class="mt-3 grid grid-cols-2 gap-3">
                                                    <div>
                                                        <div class="text-xs text-gray-500 dark:text-slate-400">Package
                                                        </div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100"
                                                            x-text="pr.package_name || labelForPackage(pr.package_mapping_id)">
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-xs text-gray-500 dark:text-slate-400">Price
                                                        </div>
                                                        <div class="text-base font-bold text-gray-900 dark:text-slate-100"
                                                            x-text="'UGX '+formatNumber(pr.price)"></div>
                                                        <div class="text-[11px] text-gray-500 dark:text-slate-400"
                                                            x-text="formatCommissionMini(pr)"></div>
                                                    </div>
                                                </div>

                                                <div class="mt-3 flex items-center justify-between">
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium"
                                                        :class="chipColor(pr.price_category)"
                                                        x-text="(pr.price_category||'').toUpperCase()"></span>
                                                    <div class="text-xs text-gray-600 dark:text-slate-300">
                                                        <span class="opacity-70">Capacity:</span>
                                                        <span
                                                            x-text="pr.delivery_capacity ? pr.delivery_capacity : '-'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="hidden md:block">
                                        <div class="overflow-x-auto">
                                            <div
                                                class="sm:w-[750px] md:w-[900px] lg:w-auto border rounded-lg overflow-hidden border-gray-200 dark:border-slate-700">
                                                <div
                                                    class="grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 dark:bg-slate-800 text-xs font-semibold text-gray-600 dark:text-slate-200">
                                                    <div class="col-span-3">Unit - Size</div>
                                                    <div class="col-span-3">Package</div>
                                                    <div class="col-span-2">Category</div>
                                                    <div class="col-span-2">Price</div>
                                                    <div class="col-span-1 text-center">Capacity</div>
                                                    <div class="col-span-1 text-right">Edit</div>
                                                </div>

                                                <template x-if="pricingList.length===0">
                                                    <div
                                                        class="px-4 py-6 text-center text-gray-500 dark:text-slate-300">
                                                        No pricing entries</div>
                                                </template>

                                                <template x-for="(pr,idx) in pricingList"
                                                    :key="(pr.pricing_id||'new')+'-'+idx">
                                                    <div
                                                        class="grid grid-cols-12 gap-2 px-4 py-3 border-t items-center bg-white dark:bg-slate-900 border-gray-200 dark:border-slate-700">
                                                        <div class="col-span-3">
                                                            <div class="text-sm text-gray-900 dark:text-slate-100"
                                                                x-text="(pr.package_size||'-')+' '+(pr.si_unit||labelForUnit(pr.si_unit_id)||'')">
                                                            </div>
                                                        </div>
                                                        <div class="col-span-3">
                                                            <div class="text-sm font-medium text-gray-900 dark:text-slate-100"
                                                                x-text="pr.package_name || labelForPackage(pr.package_mapping_id)">
                                                            </div>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <span
                                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                                :class="chipColor(pr.price_category)"
                                                                x-text="(pr.price_category||'').toUpperCase()"></span>
                                                        </div>
                                                        <div class="col-span-2">
                                                            <div class="text-sm font-semibold text-gray-900 dark:text-slate-100"
                                                                x-text="'UGX '+formatNumber(pr.price)"></div>
                                                            <div class="text-[11px] text-gray-500 dark:text-slate-400"
                                                                x-text="formatCommissionMini(pr)"></div>
                                                        </div>
                                                        <div class="col-span-1 text-center">
                                                            <div class="text-xs text-gray-600 dark:text-slate-300"
                                                                x-text="pr.delivery_capacity ? pr.delivery_capacity : '-'">
                                                            </div>
                                                        </div>
                                                        <div class="col-span-1 text-right">
                                                            <button @click="openStepper('edit', idx)"
                                                                class="p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-800">
                                                                <i data-lucide="pen-line"
                                                                    class="w-4 h-4 text-gray-700 dark:text-slate-200"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex items-center justify-end gap-2">
                                            <button @click="reloadExisting()"
                                                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800">
                                                Reset
                                            </button>
                                            <button @click="savePricingChanges()"
                                                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="!selectedStore"
                        class="hidden sm:flex flex-col sm:flex-row justify-end p-4 sm:p-6 border-t border-gray-200 dark:border-slate-800 gap-3">
                        <button @click="close()"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                            Cancel
                        </button>
                    </div>

                    <div x-show="selectedStore"
                        class="md:hidden sticky bottom-0 inset-x-0 z-[5] bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="close()"
                                class="px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200">
                                Cancel
                            </button>
                            <button @click="savePricingChanges()" class="px-3 py-2 rounded-lg bg-red-600 text-white">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.stepper" id="pricingStepperModal" class="fixed inset-0 z-[1300]" x-transition.opacity>
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="fixed inset-0 flex items-center justify-center p-0 sm:p-4">
            <div
                class="w-full sm:w-[94%] lg:max-w-4xl bg-white dark:bg-slate-900 rounded-none sm:rounded-2xl shadow-2xl overflow-hidden modal-panel">
                <div
                    class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-200 dark:border-slate-800">
                    <div class="flex items-center gap-3 min-w-0">
                        <img :src="productImage || placeholderImg"
                            class="w-10 h-10 rounded-md object-cover bg-gray-100 dark:bg-slate-800" alt="" />
                        <div class="min-w-0">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-slate-100"
                                x-text="stepper.mode==='new' ? 'Add Pricing' : 'Edit Pricing'"></h3>
                            <p class="text-xs text-gray-500 dark:text-slate-400 truncate" x-text="product.name || ''">
                            </p>
                        </div>
                    </div>
                    <button @click="closeStepper()" class="p-2 rounded hover:bg-gray-50 dark:hover:bg-slate-800">
                        <i data-lucide="x" class="w-5 h-5 text-gray-700 dark:text-slate-200"></i>
                    </button>
                </div>

                <div class="modal-scroll p-4 sm:p-5">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div x-show="stepper.step===1" class="space-y-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Package</label>
                                <div class="custom-select" x-data="floatingSelect({
                                        getLabel: () => stepper.package_name || 'Select a package',
                                        search: true,
                                        getQuery: () => stepper.packageQuery,
                                        setQuery: v => stepper.packageQuery = v,
                                        options: () => availablePackages,
                                        optionLabel: o => o.package_name,
                                        optionKey: o => o.id,
                                        isSelected: o => stepper.package_mapping_id === o.id,
                                        onSelect: o => selectPackage(o)
                                    })" x-init="init()">
                                    <button type="button" class="custom-select__btn" x-ref="btn" @click="toggle()">
                                        <span class="truncate" x-text="label"></span>
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </button>
                                    <div x-ref="panel" class="custom-select__panel" x-show="open">
                                        <div class="p-2 sticky top-0 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700"
                                            x-show="searchable">
                                            <input x-model="search" type="text" placeholder="Filter..."
                                                class="w-full px-2 py-1 border border-gray-300 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100" />
                                        </div>
                                        <template x-if="filteredOptions.length===0">
                                            <div class="p-3 text-center text-gray-500 dark:text-slate-300">No items
                                            </div>
                                        </template>
                                        <template x-for="m in filteredOptions" :key="optionKey(m)">
                                            <div class="custom-select__opt" @mousedown.prevent="select(m)">
                                                <span x-text="optionLabel(m)"></span>
                                                <i :data-lucide="isSelected(m)?'check':'plus'" class="w-4 h-4"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs text-red-600" x-show="errors.package">Select a package</p>
                            </div>

                            <div x-show="stepper.step===2" class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Unit of
                                        Measure</label>
                                    <div class="custom-select" x-data="floatingSelect({
                                            getLabel: () => stepper.si_unit || 'Select a unit',
                                            search: true,
                                            getQuery: () => stepper.unitQuery,
                                            setQuery: v => stepper.unitQuery = v,
                                            options: () => availableUnits,
                                            optionLabel: o => o.si_unit,
                                            optionKey: o => o.id,
                                            isSelected: o => stepper.si_unit_id === o.id,
                                            onSelect: o => selectUnit(o)
                                        })" x-init="init()">
                                        <button type="button" class="custom-select__btn" x-ref="btn" @click="toggle()">
                                            <span class="truncate" x-text="label"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                        <div x-ref="panel" class="custom-select__panel" x-show="open">
                                            <div class="p-2 sticky top-0 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700"
                                                x-show="searchable">
                                                <input x-model="search" type="text" placeholder="Filter..."
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100" />
                                            </div>
                                            <template x-for="u in filteredOptions" :key="optionKey(u)">
                                                <div class="custom-select__opt" @mousedown.prevent="select(u)">
                                                    <span x-text="optionLabel(u)"></span>
                                                    <i :data-lucide="isSelected(u)?'check':'plus'" class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-600" x-show="errors.unit">Select a unit</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Unit
                                        Size</label>
                                    <input x-model="stepper.package_size" @keydown="onUnitSizeKeydown"
                                        @paste="onUnitSizePaste" @input="onUnitSizeInput" type="text" inputmode="text"
                                        autocomplete="off" placeholder="Enter size"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-red-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                        :class="errors.size ? 'border-red-500 ring-2 ring-red-300' : ''" required />
                                </div>
                            </div>

                            <div x-show="stepper.step===3" class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Price
                                        Category</label>
                                    <div class="custom-select" x-data="floatingSelect({
                                            getLabel: () => stepper.price_category ? stepper.price_category.toUpperCase() : 'Select category',
                                            search: false,
                                            options: () => ['retail','wholesale','factory'],
                                            optionLabel: o => String(o).toUpperCase(),
                                            optionKey: o => String(o),
                                            isSelected: o => stepper.price_category === o,
                                            onSelect: o => { stepper.price_category = o }
                                        })" x-init="init()">
                                        <button type="button" class="custom-select__btn" x-ref="btn" @click="toggle()">
                                            <span x-text="label"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                        <div x-ref="panel" class="custom-select__panel" x-show="open">
                                            <template x-for="c in filteredOptions" :key="optionKey(c)">
                                                <div class="custom-select__opt" @mousedown.prevent="select(c)">
                                                    <span x-text="optionLabel(c)"></span>
                                                    <i :data-lucide="isSelected(c)?'check':'plus'" class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-600" x-show="errors.category">Select a category</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Price
                                        (UGX)</label>
                                    <input x-model="stepper.price" type="number" min="0" step="any"
                                        placeholder="Enter price"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-red-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                        :class="errors.price ? 'border-red-500 ring-2 ring-red-300' : ''" />
                                </div>
                            </div>

                            <div x-show="stepper.step===4" class="space-y-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-slate-200"
                                    x-text="stepper.price_category==='retail' ? 'Max Capacity' : (stepper.price_category ? 'Min Capacity' : 'Capacity')"></label>
                                <input x-model="stepper.delivery_capacity" type="number" min="0" step="1"
                                    placeholder="Enter capacity"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-red-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                    :class="errors.capacity ? 'border-red-500 ring-2 ring-red-300' : ''" />
                            </div>

                            <div x-show="stepper.step===5" class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200">Commission
                                        Type</label>
                                    <div class="custom-select" x-data="floatingSelect({
                                            getLabel: () => stepper.commission_type==='flat' ? 'Flat' : 'Percentage',
                                            search: false,
                                            options: () => ['percentage','flat'],
                                            optionLabel: o => o==='flat' ? 'Flat' : 'Percentage',
                                            optionKey: o => o,
                                            isSelected: o => stepper.commission_type === o,
                                            onSelect: o => { stepper.commission_type = o; onCommissionTypeChange() }
                                        })" x-init="init()">
                                        <button type="button" class="custom-select__btn" x-ref="btn" @click="toggle()">
                                            <span x-text="label"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                        <div x-ref="panel" class="custom-select__panel" x-show="open">
                                            <template x-for="t in filteredOptions" :key="optionKey(t)">
                                                <div class="custom-select__opt" @mousedown.prevent="select(t)">
                                                    <span x-text="optionLabel(t)"></span>
                                                    <i :data-lucide="isSelected(t)?'check':'plus'" class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-600" x-show="errors.commissionType">Select type</p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-700 dark:text-slate-200"
                                        x-text="commissionLabel"></label>
                                    <input x-model="stepper.commission_value" type="number" min="0" step="any"
                                        placeholder="Enter commission"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-red-500 bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100"
                                        :class="errors.commissionValue ? 'border-red-500 ring-2 ring-red-300' : ''" />
                                    <p class="text-xs mt-1"
                                        :class="errors.commissionValue ? 'text-red-600' : 'text-gray-500 dark:text-slate-400'"
                                        x-text="commissionHint"></p>
                                </div>
                            </div>
                        </div>

                        <div class="hidden md:block">
                            <div
                                class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                                <div class="relative">
                                    <img :src="productImage || placeholderImg"
                                        class="w-full h-40 object-cover bg-gray-100 dark:bg-slate-800" alt="" />
                                    <div class="absolute top-2 right-2">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-medium"
                                            :class="chipColor(stepper.price_category || 'retail')">
                                            <i data-lucide="tags" class="w-3 h-3"></i>
                                            <span
                                                x-text="(stepper.package_size?stepper.package_size:'')+(stepper.si_unit?' '+stepper.si_unit:'') + (stepper.package_name?(' - '+stepper.package_name):'')"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4 space-y-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-slate-100"
                                        x-text="product.name || 'Product'"></h4>

                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-medium"
                                            :class="chipColor(stepper.price_category || '')">
                                            <i data-lucide="tag" class="w-3 h-3"></i>
                                            <span
                                                x-text="(stepper.price_category||'').toUpperCase() || 'CATEGORY'"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-baseline justify-between mt-2">
                                        <div class="text-sm text-gray-500 dark:text-slate-400">Price</div>
                                        <div class="text-lg font-bold text-gray-900 dark:text-slate-100"
                                            x-text="'UGX '+formatNumber(stepper.price || 0)"></div>
                                    </div>

                                    <div class="flex items-baseline justify-between">
                                        <div class="text-sm text-gray-500 dark:text-slate-400">Commission</div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100"
                                            x-text="stepper.commission_type==='flat' ? ('UGX '+formatNumber(stepper.commission_value||0)) : ((stepper.commission_value||0)+'%')">
                                        </div>
                                    </div>

                                    <div class="flex items-baseline justify-between">
                                        <div class="text-sm text-gray-500 dark:text-slate-400">Capacity</div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-slate-100"
                                            x-text="stepper.delivery_capacity || '-'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="p-4 sm:p-5 border-t border-gray-200 dark:border-slate-800 hidden md:flex items-center justify-between">
                    <button @click="prevStep"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-800 flex items-center gap-2"
                        :disabled="stepper.step===1">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        Back
                    </button>
                    <div class="flex items-center gap-2">
                        <button x-show="stepper.step<5" @click="nextStep"
                            class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black flex items-center gap-2">
                            Next
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                        <button x-show="stepper.step===5" @click="commitStepper"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            Save
                        </button>
                    </div>
                </div>

                <div
                    class="md:hidden sticky bottom-0 inset-x-0 z-[6] bg-white/95 dark:bg-slate-900/95 backdrop-blur border-t border-gray-200 dark:border-slate-800 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <button @click="prevStep"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-200"
                            :disabled="stepper.step===1">
                            Back
                        </button>
                        <button x-show="stepper.step<5" @click="nextStep"
                            class="px-3 py-2 rounded-lg bg-gray-900 dark:bg-black text-white">
                            Next
                        </button>
                        <button x-show="stepper.step===5" @click="commitStepper"
                            class="px-3 py-2 rounded-lg bg-red-600 text-white">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    if (!window.BASE_URL) {
        window.BASE_URL = '<?= BASE_URL ?>';
    }

    function floatingSelect(cfg) {
        return {
            open: false,
            btnEl: null,
            panelEl: null,
            placeholder: null,
            portal: null,
            search: '',
            cfg: cfg || {},
            get searchable() { return this.cfg.search !== false },
            get label() { return typeof this.cfg.getLabel === 'function' ? this.cfg.getLabel() : '' },
            optionLabel(o) { return this.cfg.optionLabel ? this.cfg.optionLabel(o) : String(o) },
            optionKey(o) { return this.cfg.optionKey ? this.cfg.optionKey(o) : JSON.stringify(o) },
            isSelected(o) { return this.cfg.isSelected ? this.cfg.isSelected(o) : false },
            optionsRaw() { return this.cfg.options ? (this.cfg.options() || []) : [] },
            get filteredOptions() {
                const list = this.optionsRaw();
                if (!this.searchable) return list;
                const q = (this.search || '').toLowerCase();
                if (!q) return list;
                return list.filter(o => this.optionLabel(o).toLowerCase().includes(q));
            },
            init() {
                this.btnEl = this.$refs.btn;
                this.panelEl = this.$refs.panel;
                if (this.cfg.getQuery) {
                    try { this.search = this.cfg.getQuery() || '' } catch (e) { }
                }
                this.$watch('search', v => { if (this.cfg.setQuery) this.cfg.setQuery(v) });
                this.portal = document.getElementById('selectDropdownLayer');
                if (!this.portal) {
                    this.portal = document.createElement('div');
                    this.portal.id = 'selectDropdownLayer';
                    this.portal.style.position = 'fixed';
                    this.portal.style.inset = '0';
                    this.portal.style.zIndex = '2000';
                    this.portal.style.pointerEvents = 'none';
                    document.body.appendChild(this.portal);
                }
            },
            toggle() { this.open ? this.close() : this.openPanel() },
            openPanel() {
                if (this.open) return;
                this.placeholder = document.createComment('select-panel');
                this.panelEl.after(this.placeholder);
                this.panelEl.style.position = 'fixed';
                this.panelEl.style.marginTop = '0';
                this.panelEl.style.zIndex = '2001';
                this.panelEl.style.pointerEvents = 'auto';
                this.portal.appendChild(this.panelEl);
                this.position();
                this.addListeners();
                this.open = true;
            },
            close() {
                if (!this.open) return;
                this.removeListeners();
                if (this.placeholder && this.placeholder.parentNode) {
                    this.placeholder.parentNode.insertBefore(this.panelEl, this.placeholder);
                    this.placeholder.remove();
                }
                this.panelEl.style.position = '';
                this.panelEl.style.left = '';
                this.panelEl.style.top = '';
                this.panelEl.style.width = '';
                this.panelEl.style.pointerEvents = '';
                this.open = false;
            },
            select(o) {
                if (this.cfg.onSelect) this.cfg.onSelect(o);
                this.close();
            },
            position() {
                const rect = this.btnEl.getBoundingClientRect();
                const vw = window.innerWidth || document.documentElement.clientWidth;
                const vh = window.innerHeight || document.documentElement.clientHeight;
                const maxH = 224;
                let top = rect.bottom + 6;
                let left = rect.left;
                const width = rect.width;
                this.panelEl.style.visibility = 'hidden';
                this.panelEl.style.display = 'block';
                const panelH = Math.min(this.panelEl.scrollHeight, maxH);
                if (top + panelH > vh - 8) {
                    top = Math.max(8, rect.top - panelH - 6);
                }
                if (left + width > vw - 8) {
                    left = Math.max(8, vw - width - 8);
                }
                this.panelEl.style.left = left + 'px';
                this.panelEl.style.top = top + 'px';
                this.panelEl.style.width = width + 'px';
                this.panelEl.style.maxHeight = maxH + 'px';
                this.panelEl.style.visibility = 'visible';
            },
            addListeners() {
                this._onScroll = () => this.position();
                this._onResize = () => this.position();
                this._onKey = e => { if (e.key === 'Escape') this.close() };
                this._onClick = e => {
                    if (this.btnEl.contains(e.target) || this.panelEl.contains(e.target)) return;
                    this.close();
                };
                window.addEventListener('resize', this._onResize, { passive: true });
                window.addEventListener('scroll', this._onScroll, { passive: true });
                document.addEventListener('keydown', this._onKey);
                document.addEventListener('mousedown', this._onClick);
                this.scrollParents = this.getScrollParents(this.btnEl);
                this.scrollParents.forEach(p => p.addEventListener('scroll', this._onScroll, { passive: true }));
            },
            removeListeners() {
                window.removeEventListener('resize', this._onResize);
                window.removeEventListener('scroll', this._onScroll);
                document.removeEventListener('keydown', this._onKey);
                document.removeEventListener('mousedown', this._onClick);
                if (this.scrollParents) this.scrollParents.forEach(p => p.removeEventListener('scroll', this._onScroll));
                this.scrollParents = [];
            },
            getScrollParents(el) {
                const parents = [];
                let p = el.parentElement;
                while (p) {
                    const s = getComputedStyle(p);
                    const oy = s.overflowY, ox = s.overflowX;
                    if ((oy && (oy.includes('auto') || oy.includes('scroll'))) || (ox && (ox.includes('auto') || ox.includes('scroll')))) {
                        parents.push(p);
                    }
                    p = p.parentElement;
                }
                return parents;
            }
        };
    }

    function vendorSell() {
        return {
            isOpen: false,
            headerTitle: '',
            product: { id: null, name: '', category_name: '' },
            stores: [],
            selectedStore: null,
            loading: false,
            pricingList: [],
            originalPricing: [],
            availablePackages: [],
            availableUnits: [],
            placeholderImg: 'https://placehold.co/600x400/f3f4f6/9ca3af?text=No+Image',
            productImage: null,
            modals: { stepper: false },
            errors: {
                package: false,
                unit: false,
                size: false,
                category: false,
                price: false,
                capacity: false,
                commissionType: false,
                commissionValue: false
            },
            stepper: {
                mode: 'new',
                index: null,
                step: 1,
                package_mapping_id: null,
                package_name: '',
                packageQuery: '',
                si_unit_id: null,
                si_unit: '',
                unitQuery: '',
                package_size: '',
                price_category: '',
                price: '',
                delivery_capacity: '',
                commission_type: 'percentage',
                commission_value: 1
            },

            async refreshIcons() {
                try {
                    if (this.$nextTick) {
                        await this.$nextTick();
                    }
                    if (window.lucide && lucide.createIcons) {
                        lucide.createIcons();
                    }
                } catch (e) { }
            },

            init() {
                window.openVendorSellModal = (id, name, img) => {
                    this.open(id, name, img);
                };
                window.closeVendorSellModal = () => {
                    this.close();
                };
                this.$watch('isOpen', v => {
                    document.documentElement.style.overflowY = v ? 'hidden' : '';
                });
                this.$watch('modals.stepper', v => {
                    document.documentElement.style.overflowY = (v || this.isOpen) ? 'hidden' : '';
                });
                this.refreshIcons();
                try {
                    document.addEventListener('DOMContentLoaded', () => {
                        try {
                            if (window.Swiper) {
                                document.querySelectorAll('.swiper').forEach(el => {
                                    const inst = el.swiper;
                                    if (!inst) return;
                                    const slides = inst.slides ? inst.slides.length : el.querySelectorAll('.swiper-slide').length;
                                    const perView = typeof inst.params.slidesPerView === 'number' ? inst.params.slidesPerView : 1;
                                    const perGroup = inst.params.slidesPerGroup || 1;
                                    const minSlides = Math.max(perView, perGroup) + (inst.params.centeredSlides ? 1 : 0);
                                    if (slides < minSlides && inst.params.loop) {
                                        inst.params.loop = false;
                                        if (typeof inst.loopDestroy === 'function') inst.loopDestroy();
                                        if (typeof inst.update === 'function') inst.update();
                                    }
                                });
                            }
                        } catch (e) { }
                    });
                } catch (e) { }
            },

            showAlert(type, message) {
                const c = type === 'success' ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200' : 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200';
                const icon = type === 'success' ? 'check-circle' : 'alert-circle';
                const el = document.getElementById('alertContainer');
                el.innerHTML = `
                    <div class="${c} pointer-events-auto border px-4 py-3 rounded-lg shadow flex items-center gap-2">
                        <i data-lucide="${icon}" class="w-4 h-4"></i>
                        <span>${message}</span>
                    </div>
                `;
                this.refreshIcons();
                setTimeout(() => {
                    el.innerHTML = '';
                }, 4000);
            },

            onUnitSizeKeydown(e) {
                const allowedChars = '0123456789./xX ';
                const ctrl = e.ctrlKey || e.metaKey;
                const navKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'Tab'];
                if (navKeys.includes(e.key) || (ctrl && ['a', 'c', 'v', 'x', 'z', 'y'].includes(e.key.toLowerCase()))) {
                    return;
                }
                if (e.key === ' ') {
                    const el = e.target;
                    const start = el.selectionStart ?? 0;
                    const end = el.selectionEnd ?? start;
                    const val = el.value || '';
                    const prev = start > 0 ? val[start - 1] : '';
                    const next = end < val.length ? val[end] : '';
                    if (start === 0 || prev === ' ' || next === ' ') {
                        e.preventDefault();
                        this.showAlert('error', 'Only single spaces between characters are allowed');
                        return;
                    }
                    return;
                }
                if (e.key.length === 1 && !allowedChars.includes(e.key)) {
                    e.preventDefault();
                    this.showAlert('error', 'Only numbers, x, /, . and single spaces are allowed');
                }
            },

            onUnitSizePaste(e) {
                const data = (e.clipboardData || window.clipboardData).getData('text');
                const trimmed = (data || '').trim();
                const validChars = /^[0-9./xX ]+$/;
                const hasDoubleSpace = / {2,}/.test(trimmed);
                if (!validChars.test(trimmed) || hasDoubleSpace || trimmed === '') {
                    e.preventDefault();
                    this.showAlert('error', 'Only numbers, x, /, . and single spaces are allowed');
                }
            },

            onUnitSizeInput(e) {
                let val = e.target.value || '';
                val = val.replace(/[^0-9./xX ]/g, '');
                val = val.replace(/ {2,}/g, ' ').trim();
                if (val !== e.target.value) {
                    this.showAlert('error', 'Only numbers, x, /, . and single spaces are allowed');
                    e.target.value = val;
                }
                this.stepper.package_size = val;
            },

            unitSizeToNumber(v) {
                v = (v ?? '').toString().replace(/ {2,}/g, ' ').trim();
                if (v === '' || !/^[0-9./xX ]+$/.test(v)) return NaN;
                const mixed = /^(\d+)\s+(\d+)\s*\/\s*(\d+)$/;
                const simple = /^(\d+)\s*\/\s*(\d+)$/;
                if (mixed.test(v)) {
                    const m = v.match(mixed);
                    const whole = parseFloat(m[1]);
                    const num = parseFloat(m[2]);
                    const den = parseFloat(m[3]);
                    if (!isFinite(whole) || !isFinite(num) || !isFinite(den) || den === 0) return NaN;
                    return whole + num / den;
                }
                if (simple.test(v)) {
                    const m = v.match(simple);
                    const num = parseFloat(m[1]);
                    const den = parseFloat(m[2]);
                    if (!isFinite(num) || !isFinite(den) || den === 0) return NaN;
                    return num / den;
                }
                const f = parseFloat(v);
                return isFinite(f) ? f : NaN;
            },

            isValidUnitSize(v) {
                v = (v ?? '').toString().replace(/ {2,}/g, ' ').trim();
                if (v === '') return false;
                if (!/^[0-9./xX ]+$/.test(v)) return false;
                if (!/[0-9]/.test(v)) return false;
                return true;
            },

            async open(productId, productName, img) {
                const ok = await (typeof checkUserSession === 'function' ? checkUserSession() : true);
                if (!ok) {
                    if (typeof openAuthModal === 'function') openAuthModal();
                    return;
                }
                this.reset();
                this.product = { id: productId, name: productName };
                this.headerTitle = productName;
                this.isOpen = true;
                this.loading = true;
                if (img) {
                    this.productImage = img;
                } else {
                    this.productImage = await this.resolveProductImage(productId);
                }
                await this.loadUserStores();
                await this.refreshIcons();
            },

            close() {
                this.reset();
                this.isOpen = false;
            },

            reset() {
                this.loading = false;
                this.stores = [];
                this.selectedStore = null;
                this.pricingList = [];
                this.originalPricing = [];
                this.availablePackages = [];
                this.availableUnits = [];
                this.productImage = null;
                this.errors = {
                    package: false,
                    unit: false,
                    size: false,
                    category: false,
                    price: false,
                    capacity: false,
                    commissionType: false,
                    commissionValue: false
                };
                this.stepper = {
                    mode: 'new',
                    index: null,
                    step: 1,
                    package_mapping_id: null,
                    package_name: '',
                    packageQuery: '',
                    si_unit_id: null,
                    si_unit: '',
                    unitQuery: '',
                    package_size: '',
                    price_category: '',
                    price: '',
                    delivery_capacity: '',
                    commission_type: 'percentage',
                    commission_value: 1
                };
            },

            async tryUrl(url) {
                try {
                    const r = await fetch(url, { method: 'HEAD', cache: 'no-store' });
                    return r.ok ? url : null;
                } catch (e) {
                    return null;
                }
            },

            async resolveFromImagesJson(pid) {
                try {
                    const jurl = `${BASE_URL}img/products/${encodeURIComponent(pid)}/images.json`;
                    const r = await fetch(jurl, { cache: 'no-store' });
                    if (!r.ok) return null;
                    const j = await r.json();
                    const arr = Array.isArray(j?.images) ? j.images : [];
                    if (arr.length === 0) return null;
                    const first = arr[0];
                    if (typeof first !== 'string' || first.trim() === '') return null;
                    if (/^https?:\/\//i.test(first)) {
                        return await this.tryUrl(first) || null;
                    }
                    const built = `${BASE_URL}img/products/${encodeURIComponent(pid)}/${first.replace(/^\/+/, '')}`;
                    return await this.tryUrl(built) || null;
                } catch (e) {
                    return null;
                }
            },

            async resolveFromCommonNames(pid) {
                const base = `${BASE_URL}img/products/${encodeURIComponent(pid)}/`;
                const names = ['1', '01', 'cover', 'main', 'image', 'photo', 'a', 'b', 'c'];
                const exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                for (const n of names) {
                    for (const ext of exts) {
                        const u = await this.tryUrl(base + n + '.' + ext);
                        if (u) return u;
                    }
                }
                return null;
            },

            async resolveProductImage(pid) {
                const viaJson = await this.resolveFromImagesJson(pid);
                if (viaJson) return viaJson;
                const viaCommon = await this.resolveFromCommonNames(pid);
                if (viaCommon) return viaCommon;
                return this.placeholderImg;
            },

            async loadUserStores() {
                try {
                    const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getUserStores`);
                    const d = await r.json();
                    this.stores = (d.success && d.stores) ? d.stores : [];
                } catch (e) {
                    this.stores = [];
                } finally {
                    this.loading = false;
                    await this.refreshIcons();
                }
            },

            async selectStore(store) {
                this.selectedStore = store;
                this.loading = true;
                try {
                    await Promise.all([this.loadExistingPricing(), this.loadMeta()]);
                } catch (e) { }
                this.loading = false;
                await this.refreshIcons();
            },

            async reloadExisting() {
                await this.loadExistingPricing();
                await this.refreshIcons();
            },

            async loadExistingPricing() {
                try {
                    const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getExistingPricing&store_id=${this.selectedStore.id}&product_id=${this.product.id}`);
                    const d = await r.json();
                    const rows = d.success ? (d.pricing || []) : [];
                    this.pricingList = rows.map(pr => ({
                        pricing_id: pr.pricing_id || null,
                        package_mapping_id: pr.package_mapping_id || null,
                        si_unit_id: pr.si_unit_id || null,
                        package_size: pr.package_size != null ? String(pr.package_size) : '1',
                        price_category: pr.price_category || 'retail',
                        price: pr.price || 0,
                        delivery_capacity: pr.delivery_capacity != null ? pr.delivery_capacity : null,
                        package_name: pr.package_name || ((pr.unit_name || '').split(' ').slice(1).join(' ') || ''),
                        si_unit: pr.si_unit || ((pr.unit_name || '').split(' ')[0] || ''),
                        commission_type: pr.commission_type || 'percentage',
                        commission_value: (typeof pr.commission_value === 'number') ? pr.commission_value : 1
                    }));
                    this.originalPricing = this.pricingList.map(x => JSON.parse(JSON.stringify(x)));
                    await this.refreshIcons();
                } catch (e) {
                    this.pricingList = [];
                    this.originalPricing = [];
                }
            },

            async loadMeta() {
                try {
                    const [pkgR, siR] = await Promise.all([
                        fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getPackageNamesForProduct&product_id=${this.product.id}`),
                        fetch(`${BASE_URL}fetch/manageVendorSell.php?action=getSIUnits`)
                    ]);
                    const [pkgJ, siJ] = await Promise.all([pkgR.json(), siR.json()]);
                    this.availablePackages = pkgJ.success ? (pkgJ.mappings || []) : [];
                    this.availableUnits = siJ.success ? (siJ.siUnits || []) : [];
                    await this.refreshIcons();
                } catch (e) {
                    this.availablePackages = [];
                    this.availableUnits = [];
                }
            },

            chipColor(cat) {
                if (cat === 'retail') return 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
                if (cat === 'wholesale') return 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300';
                if (cat === 'factory') return 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300';
                return 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-slate-200';
            },

            formatNumber(v) {
                if (!v && v !== 0) return '0';
                return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(v);
            },

            labelForPackage(id) {
                const m = this.availablePackages.find(x => String(x.id) === String(id));
                return m ? m.package_name : '';
            },

            labelForUnit(id) {
                const u = this.availableUnits.find(x => String(x.id) === String(id));
                return u ? u.si_unit : '';
            },

            formatCommissionMini(pr) {
                if (!pr) return '';
                const t = (pr.commission_type || 'percentage');
                const v = (typeof pr.commission_value === 'number' ? pr.commission_value : parseFloat(pr.commission_value || 1));
                if (t === 'percentage') return `Comm: ${v}%`;
                return `Comm: UGX ${this.formatNumber(v)}`;
            },

            openStepper(mode, idx = null) {
                this.errors = {
                    package: false,
                    unit: false,
                    size: false,
                    category: false,
                    price: false,
                    capacity: false,
                    commissionType: false,
                    commissionValue: false
                };
                this.stepper = {
                    mode,
                    index: idx,
                    step: 1,
                    package_mapping_id: null,
                    package_name: '',
                    packageQuery: '',
                    si_unit_id: null,
                    si_unit: '',
                    unitQuery: '',
                    package_size: '',
                    price_category: '',
                    price: '',
                    delivery_capacity: '',
                    commission_type: 'percentage',
                    commission_value: 1
                };
                if (mode === 'edit' && idx !== null) {
                    const pr = this.pricingList[idx];
                    this.stepper.package_mapping_id = pr.package_mapping_id || null;
                    this.stepper.package_name = pr.package_name || this.labelForPackage(pr.package_mapping_id) || '';
                    this.stepper.si_unit_id = pr.si_unit_id || null;
                    this.stepper.si_unit = pr.si_unit || this.labelForUnit(pr.si_unit_id) || '';
                    this.stepper.package_size = pr.package_size ?? '';
                    this.stepper.price_category = pr.price_category || '';
                    this.stepper.price = pr.price ?? '';
                    this.stepper.delivery_capacity = pr.delivery_capacity ?? '';
                    this.stepper.commission_type = pr.commission_type || 'percentage';
                    this.stepper.commission_value = pr.commission_value != null ? pr.commission_value : 1;
                    this.stepper.packageQuery = this.stepper.package_name;
                    this.stepper.unitQuery = this.stepper.si_unit;
                }
                this.modals.stepper = true;
                this.refreshIcons();
            },

            closeStepper() {
                this.modals.stepper = false;
                this.refreshIcons();
            },

            selectPackage(m) {
                this.stepper.package_mapping_id = m.id;
                this.stepper.package_name = m.package_name;
                this.stepper.packageQuery = m.package_name;
                this.errors.package = false;
                this.refreshIcons();
            },

            selectUnit(u) {
                this.stepper.si_unit_id = u.id;
                this.stepper.si_unit = u.si_unit;
                this.stepper.unitQuery = u.si_unit;
                this.errors.unit = false;
                this.refreshIcons();
            },

            nextStep() {
                if (this.stepper.step === 1) {
                    this.errors.package = !this.stepper.package_mapping_id;
                    if (this.errors.package) return;
                } else if (this.stepper.step === 2) {
                    this.errors.unit = !this.stepper.si_unit_id;
                    this.errors.size = !this.isValidUnitSize(this.stepper.package_size);
                    if (this.errors.unit || this.errors.size) return;
                } else if (this.stepper.step === 3) {
                    this.errors.category = !this.stepper.price_category;
                    this.errors.price = this.stepper.price === '' || Number(this.stepper.price) < 0;
                    if (this.errors.category || this.errors.price) return;
                } else if (this.stepper.step === 4) {
                    this.errors.capacity = this.stepper.delivery_capacity === '' || Number(this.stepper.delivery_capacity) < 0;
                    if (this.errors.capacity) return;
                }
                if (this.stepper.step < 5) this.stepper.step++;
                this.refreshIcons();
            },

            prevStep() {
                if (this.stepper.step > 1) this.stepper.step--;
                this.refreshIcons();
            },

            get commissionLabel() {
                return this.stepper.commission_type === 'flat' ? 'Commission (UGX)' : 'Commission (%)';
            },

            get commissionHint() {
                if (this.stepper.commission_type === 'percentage') {
                    return 'Allowed: 1% to 5%';
                }
                const p = Number(this.stepper.price || 0);
                const min = Math.max(0, Math.round(p * 0.01 * 100) / 100);
                const max = Math.max(0, Math.round(p * 0.05 * 100) / 100);
                return p > 0 ? `Allowed: UGX ${this.formatNumber(min)} to UGX ${this.formatNumber(max)}` : 'Enter a price to compute allowed range';
            },

            onCommissionTypeChange() {
                if (this.stepper.commission_type === 'percentage') {
                    if (this.stepper.commission_value === '' || this.stepper.commission_value == null) {
                        this.stepper.commission_value = 1;
                    }
                } else {
                    const p = Number(this.stepper.price || 0);
                    const min = Math.round(p * 0.01 * 100) / 100;
                    if (p > 0 && (this.stepper.commission_value === '' || this.stepper.commission_value == null)) {
                        this.stepper.commission_value = min;
                    }
                }
                this.refreshIcons();
            },

            commitStepper() {
                this.errors.commissionType = false;
                const ct = this.stepper.commission_type || 'percentage';
                let cv = this.stepper.commission_value;
                if (ct === 'percentage') {
                    cv = (cv === '' || cv == null) ? 1 : Number(cv);
                    this.errors.commissionValue = !(cv >= 1 && cv <= 5);
                } else {
                    const p = Number(this.stepper.price || 0);
                    const min = Math.round(p * 0.01 * 100) / 100;
                    const max = Math.round(p * 0.05 * 100) / 100;
                    cv = (cv === '' || cv == null) ? min : Number(cv);
                    this.errors.commissionValue = !(p > 0 && cv >= min && cv <= max);
                }
                if (this.errors.commissionValue) return;
                const entry = {
                    package_mapping_id: this.stepper.package_mapping_id,
                    package_name: this.stepper.package_name,
                    si_unit_id: this.stepper.si_unit_id,
                    si_unit: this.stepper.si_unit,
                    package_size: this.stepper.package_size,
                    price_category: this.stepper.price_category,
                    price: this.stepper.price,
                    delivery_capacity: this.stepper.delivery_capacity || null,
                    commission_type: ct,
                    commission_value: cv
                };
                if (this.stepper.mode === 'edit' && this.stepper.index !== null) {
                    entry.pricing_id = this.pricingList[this.stepper.index] && this.pricingList[this.stepper.index].pricing_id ? this.pricingList[this.stepper.index].pricing_id : null;
                    this.pricingList.splice(this.stepper.index, 1, entry);
                } else {
                    entry.pricing_id = null;
                    this.pricingList.push(entry);
                }
                this.modals.stepper = false;
                this.refreshIcons();
            },

            normalizeLineItem(pr) {
                return {
                    pricing_id: pr.pricing_id || null,
                    package_mapping_id: pr.package_mapping_id,
                    si_unit_id: pr.si_unit_id,
                    package_size: pr.package_size,
                    price_category: pr.price_category,
                    price: pr.price,
                    delivery_capacity: pr.delivery_capacity,
                    commission_type: pr.commission_type || 'percentage',
                    commission_value: pr.commission_value == null || pr.commission_value === '' ? 1 : pr.commission_value
                };
            },

            isSame(a, b) {
                if (!a || !b) return false;
                const num = v => v == null || v === '' ? null : Number(v);
                const str = v => v == null ? '' : String(v);
                return (
                    str(a.package_mapping_id) === str(b.package_mapping_id) &&
                    str(a.si_unit_id) === str(b.si_unit_id) &&
                    str(a.package_size) === str(b.package_size) &&
                    str(a.price_category) === str(b.price_category) &&
                    num(a.price) === num(b.price) &&
                    num(a.delivery_capacity) === num(b.delivery_capacity) &&
                    str(a.commission_type) === str(b.commission_type) &&
                    num(a.commission_value) === num(b.commission_value)
                );
            },

            diffChanges() {
                const byId = new Map(this.originalPricing.filter(x => x.pricing_id).map(x => [x.pricing_id, x]));
                const added = [];
                const changed = [];
                for (const pr of this.pricingList) {
                    if (!pr.pricing_id) {
                        added.push(pr);
                    } else {
                        const orig = byId.get(pr.pricing_id);
                        if (!this.isSame(pr, orig)) changed.push(pr);
                    }
                }
                return { added, changed };
            },

            async savePricingChanges() {
                if (!this.selectedStore || !this.product?.id) {
                    this.showAlert('error', 'Missing context');
                    return;
                }
                const { added, changed } = this.diffChanges();
                if (added.length === 0 && changed.length === 0) {
                    this.close();
                    return;
                }
                try {
                    const payloadItems = [
                        ...added.map(p => this.normalizeLineItem({ ...p, pricing_id: null })),
                        ...changed.map(p => this.normalizeLineItem(p))
                    ];
                    const r = await fetch(`${BASE_URL}fetch/manageVendorSell.php?action=addProductToStore`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            store_id: this.selectedStore.id,
                            product_id: this.product.id,
                            line_items: payloadItems
                        })
                    });
                    const j = await r.json();
                    if (j.success) {
                        this.close();
                    } else {
                        this.showAlert('error', j.error || 'Failed to save');
                    }
                } catch (e) {
                    this.showAlert('error', 'Server error');
                }
            },

            goBackToStoreSelection() {
                this.selectedStore = null;
                this.pricingList = [];
                this.originalPricing = [];
            }
        };
    }
</script>