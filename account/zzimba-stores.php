<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Stores';
$activeNav = 'zzimba-stores';
ob_start();
?>
<div x-data="zzimbaStores()" x-init="init()" x-cloak class="min-h-screen bg-user-content dark:bg-secondary/10">
    <style>
        [x-cloak] {
            display: none
        }
    </style>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div x-show="pendingInvitations.length" x-transition
            class="bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <h2 class="text-xl font-semibold text-secondary dark:text-white">Pending Store Manager Invitations</h2>
                <p class="text-sm text-gray-text dark:text-white/70">Review and respond to invitations to manage stores
                </p>
            </div>
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4">
                    <template x-for="inv in pendingInvitations" :key="inv.manager_id">
                        <div
                            class="bg-white dark:bg-secondary rounded-xl border border-gray-200 dark:border-white/10 p-4">
                            <div class="flex items-start sm:items-center gap-4 flex-col sm:flex-row">
                                <div
                                    class="w-16 h-16 rounded-lg overflow-hidden bg-gray-100 dark:bg-white/10 grid place-items-center flex-shrink-0">
                                    <img :src="inv.logo_url ? (BASE_URL + inv.logo_url) : `https://placehold.co/100x100/f0f0f0/808080?text=${escapeText(inv.store_name).slice(0,2)}`"
                                        :alt="inv.store_name" class="w-12 h-12 object-cover rounded"
                                        :onerror="`this.onerror=null;this.src='https://placehold.co/100x100/f0f0f0/808080?text=${escapeText(inv.store_name).slice(0,2)}';`">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-secondary dark:text-white break-words"
                                        x-text="inv.store_name"></h3>
                                    <p class="text-sm text-gray-600 dark:text-white/70"><span
                                            class="font-medium">Role:</span> <span x-text="inv.role_display"></span></p>
                                    <p class="text-sm text-gray-600 dark:text-white/70"><span
                                            class="font-medium">Invited by:</span> <span x-text="inv.owner_name"></span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-white/60 mt-1"
                                        x-text="`Invited ${timeAgo(inv.created_at)}`"></p>
                                </div>
                                <div class="flex w-full sm:w-auto gap-2 justify-stretch sm:justify-end">
                                    <button
                                        class="px-4 py-2 rounded-xl bg-green-600 text-white hover:bg-green-700 inline-flex items-center gap-2"
                                        @click="openInvitation('approve', inv.manager_id, inv.store_name)">
                                        <i data-lucide="check" class="w-4 h-4"></i>Approve
                                    </button>
                                    <button
                                        class="px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 inline-flex items-center gap-2"
                                        @click="openInvitation('decline', inv.manager_id, inv.store_name)">
                                        <i data-lucide="x" class="w-4 h-4"></i>Decline
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-secondary rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg grid place-items-center bg-user-primary/10">
                            <i data-lucide="grid-2x2" class="w-4 h-4 text-user-primary"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-secondary dark:text-white">Profiles</h2>
                    </div>
                    <button
                        class="px-4 sm:px-5 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90 items-center gap-2 font-medium shadow-lg shadow-primary/25 inline-flex"
                        @click="openStoreModal('create')">
                        <i data-lucide="plus" class="w-4 h-4"></i><span>Create New</span>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <template x-if="!stores.length">
                    <div class="text-center py-14">
                        <div
                            class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-full grid place-items-center mx-auto mb-4">
                            <i data-lucide="store" class="w-8 h-8 text-gray-400 dark:text-white/60"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-secondary dark:text-white mb-1">No Stores Found</h3>
                        <p class="text-sm text-gray-text dark:text-white/70 mb-4">Create your first store to get
                            started.</p>
                        <button @click="openStoreModal('create')"
                            class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 inline-flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>Create Your First Store
                        </button>
                    </div>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6" x-show="stores.length">
                    <template x-for="store in stores" :key="store.uuid_id">
                        <div
                            class="bg-white dark:bg-secondary rounded-2xl border border-gray-100 dark:border-white/10 overflow-hidden relative">
                            <span class="absolute top-0 right-0 px-2 py-1 text-[11px] rounded-bl-md rounded-tr-md"
                                :class="store.type==='owned'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'"
                                x-text="store.type==='owned'?'Owned':'Managed'"></span>
                            <div class="flex flex-col sm:flex-row">
                                <div
                                    class="relative w-full sm:w-32 h-40 sm:h-32 bg-gray-50 dark:bg-white/10 grid place-items-center flex-shrink-0">
                                    <img :src="store.logo_url ? (BASE_URL + store.logo_url) : `https://placehold.co/256x256/f0f0f0/808080?text=${escapeText(store.name).slice(0,2)}`"
                                        :alt="store.name" class="w-24 h-24 sm:w-16 sm:h-16 object-cover rounded-lg"
                                        :onerror="`this.onerror=null;this.src='https://placehold.co/256x256/f0f0f0/808080?text=${escapeText(store.name).slice(0,2)}';`">
                                    <div class="absolute bottom-2 left-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="statusBadge(store.status).bg">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full"
                                                :class="statusBadge(store.status).dot"></span>
                                            <span x-text="statusBadge(store.status).label"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4 sm:p-6 flex-1 min-w-0">
                                    <div class="mb-2">
                                        <h3 class="font-semibold text-secondary dark:text-white break-words"
                                            x-text="store.name" :title="store.name"></h3>
                                    </div>
                                    <p class="text-sm text-gray-text dark:text-white/70 mb-3" :title="store.location">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-user-primary"></i>
                                            <span x-text="store.location || ''"></span>
                                        </span>
                                    </p>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-white/60">Products</p>
                                            <p class="font-medium text-secondary dark:text-white"
                                                x-text="store.product_count || 0"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-white/60">Categories</p>
                                            <p class="font-medium text-secondary dark:text-white"
                                                x-text="(store.categories ? store.categories.length : 0)"></p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button @click="openStoreModal('edit', store.uuid_id)"
                                            class="px-3 py-2 border border-gray-200 dark:border-white/10 rounded-lg text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 inline-flex items-center gap-1">
                                            <i data-lucide="pencil" class="w-4 h-4"></i><span>Edit</span>
                                        </button>
                                        <button @click="manageStore(store.uuid_id)"
                                            class="px-3 py-2 bg-primary text-white rounded-lg text-sm hover:bg-primary/90 inline-flex items-center gap-1">
                                            <i data-lucide="settings" class="w-4 h-4"></i><span>Manage</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.store" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeStoreModal"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] relative z-10 overflow-hidden mx-auto transform transition-all">
            <div class="p-5 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-user-primary/10 grid place-items-center">
                            <i data-lucide="store" class="w-5 h-5 text-user-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-secondary dark:text-white"
                                x-text="storeForm.mode==='create'?'Create New Store':'Edit Store'"></h3>
                            <p class="text-sm text-gray-text dark:text-white/70">Fill in the details below</p>
                        </div>
                    </div>
                    <button @click="closeStoreModal"
                        class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <div class="p-5 sm:p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <div class="flex items-center justify-center mb-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-white font-medium"
                            :class="storeForm.step>=1?'bg-primary':'bg-gray-300 dark:bg-white/10'">
                            <template x-if="storeForm.step>1"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="storeForm.step===1"><span>1</span></template>
                        </div>
                        <div class="w-12 h-1" :class="storeForm.step>=2?'bg-primary':'bg-gray-200 dark:bg-white/10'">
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 rounded-full font-medium"
                            :class="storeForm.step>=2?'bg-primary text-white':'bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'">
                            <template x-if="storeForm.step>2"><i data-lucide="check" class="w-4 h-4"></i></template>
                            <template x-if="storeForm.step===2"><span>2</span></template>
                            <template x-if="storeForm.step<2"><span>2</span></template>
                        </div>
                        <div class="w-12 h-1" :class="storeForm.step>=3?'bg-primary':'bg-gray-200 dark:bg-white/10'">
                        </div>
                        <div class="flex items-center justify-center w-8 h-8 rounded-full font-medium"
                            :class="storeForm.step>=3?'bg-primary text-white':'bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-white/70'">
                            <span>3</span>
                        </div>
                    </div>
                </div>

                <form class="grid gap-6" @submit.prevent>
                    <div x-show="storeForm.step===1" x-transition class="grid gap-4">
                        <h4 class="text-center font-medium text-secondary dark:text-white">Basic Store Details</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Business Name <span
                                        class="text-red-500">*</span></label>
                                <input type="text" x-model.trim="storeForm.name" placeholder="Enter business name"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                            </div>
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Business Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" x-model.trim="storeForm.business_email"
                                    placeholder="Enter business email"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                            </div>
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Main Contact Number
                                    <span class="text-red-500">*</span></label>
                                <div class="flex items-stretch gap-2">
                                    <div
                                        class="shrink-0 px-3 flex items-center rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 text-secondary dark:bg-white/10 dark:text-white">
                                        <span class="text-sm font-medium">+256</span>
                                    </div>
                                    <input type="tel" x-model.trim="storeForm.phone_local" placeholder="7XX XXX XXX"
                                        inputmode="numeric"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                </div>
                                <p class="text-xs text-gray-500 dark:text-white/60">Format: 7XX XXX XXX (no leading 0)
                                </p>
                            </div>
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Nature of Business
                                    <span class="text-red-500">*</span></label>
                                <select x-model="storeForm.nature_of_business"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                    <option value="">Select Nature of Business</option>
                                    <template x-for="opt in natureOfBusiness" :key="opt.id">
                                        <option :value="opt.id" x-text="opt.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="grid gap-1 sm:col-span-2">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Contact Person Name
                                    <span class="text-red-500">*</span></label>
                                <input type="text" x-model.trim="storeForm.contact_person_name"
                                    placeholder="Enter contact person name"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="button" @click="goStep2"
                                    class="w-full px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90">Next</button>
                            </div>
                        </div>
                    </div>

                    <div x-show="storeForm.step===2" x-transition class="grid gap-5">
                        <h4 class="text-center font-medium text-secondary dark:text-white">Store Location</h4>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <label class="text-sm font-semibold text-secondary dark:text-white">Select Location on
                                    Map <span class="text-red-500">*</span></label>
                                <div id="storeMapContainer"
                                    class="w-full h-64 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 mb-3">
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button"
                                        class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 inline-flex items-center gap-2"
                                        @click="locateMe">
                                        <i data-lucide="crosshair" class="w-4 h-4"></i>My Location
                                    </button>
                                    <div
                                        class="inline-flex rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                                        <button type="button" @click="storeForm.mapStyle='osm'; applyMapStyle()"
                                            :class="storeForm.mapStyle==='osm'?'bg-gray-900 text-white dark:bg-white/10':'bg-white dark:bg-transparent text-gray-700 dark:text-white'"
                                            class="px-3 py-2 text-sm">OpenStreetMap</button>
                                        <button type="button" @click="storeForm.mapStyle='satellite'; applyMapStyle()"
                                            :class="storeForm.mapStyle==='satellite'?'bg-gray-900 text-white dark:bg-white/10':'bg-white dark:bg-transparent text-gray-700 dark:text-white'"
                                            class="px-3 py-2 text-sm">Satellite</button>
                                    </div>
                                    <div class="relative" @click.outside="mapSearchOpen=false">
                                        <div class="flex items-stretch gap-2">
                                            <div class="relative w-72">
                                                <input type="text" x-model.trim="mapSearchQuery" @input="onSearchInput"
                                                    placeholder="Search place or address in Uganda"
                                                    class="w-72 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white pl-9 pr-3 py-2.5 text-sm">
                                                <i data-lucide="search"
                                                    class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                            </div>
                                            <button type="button"
                                                class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10"
                                                @click="clearSearch">Clear</button>
                                        </div>
                                        <div x-show="mapSearchOpen"
                                            class="absolute mt-2 w-96 max-w-[22rem] bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 rounded-xl shadow-lg overflow-hidden z-10">
                                            <template x-if="mapSearchLoading">
                                                <div class="p-3 text-sm text-gray-600 dark:text-white/80">Searching...
                                                </div>
                                            </template>
                                            <template
                                                x-if="!mapSearchLoading && mapSearchResults.length===0 && mapSearchQuery.length>=3">
                                                <div class="p-3 text-sm text-gray-600 dark:text-white/80">No results
                                                </div>
                                            </template>
                                            <ul>
                                                <template x-for="res in mapSearchResults" :key="res.key">
                                                    <li>
                                                        <button type="button"
                                                            class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/10 text-sm text-gray-800 dark:text-white flex items-start gap-2"
                                                            @click="selectSearchResult(res)">
                                                            <i data-lucide="map-pin"
                                                                class="w-4 h-4 mt-0.5 text-user-primary"></i>
                                                            <span x-text="res.name"></span>
                                                        </button>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-3">
                                    <div class="grid gap-1">
                                        <label class="text-sm font-semibold text-secondary dark:text-white">Latitude
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="storeForm.latitude" readonly
                                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                    </div>
                                    <div class="grid gap-1">
                                        <label class="text-sm font-semibold text-secondary dark:text-white">Longitude
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="storeForm.longitude" readonly
                                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                    </div>
                                </div>
                            </div>
                            <div class="grid gap-4">
                                <div class="grid gap-1">
                                    <label class="text-sm font-semibold text-secondary dark:text-white">Region/Province
                                        <span class="text-red-500">*</span></label>
                                    <select x-model="storeForm.region" @change="onRegionChange"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                        <option value="">Select Region/Province</option>
                                        <template x-for="opt in regions" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid gap-1">
                                    <label class="text-sm font-semibold text-secondary dark:text-white">District <span
                                            class="text-red-500">*</span></label>
                                    <select x-model="storeForm.district" @change="onDistrictChange"
                                        :disabled="!districts.length"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                        <option value="">Select District</option>
                                        <template x-for="opt in districts" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid gap-1">
                                    <label
                                        class="text-sm font-semibold text-secondary dark:text-white">Sub-county</label>
                                    <select x-model="storeForm.subcounty" @change="onSubcountyChange"
                                        :disabled="!subcounties.length"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                        <option value="">Select Sub-county</option>
                                        <template x-for="opt in subcounties" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid gap-1">
                                    <label
                                        class="text-sm font-semibold text-secondary dark:text-white">Parish/Ward</label>
                                    <select x-model="storeForm.parish" @change="onParishChange"
                                        :disabled="!parishes.length"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                        <option value="">Select Parish/Ward</option>
                                        <template x-for="opt in parishes" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid gap-1">
                                    <label class="text-sm font-semibold text-secondary dark:text-white">Physical Address
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" x-model="storeForm.address" readonly
                                        placeholder="Detected from map selection"
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" @click="backStep1"
                                class="px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Back</button>
                            <button type="button" @click="goStep3"
                                class="px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90">Next</button>
                        </div>
                    </div>

                    <div x-show="storeForm.step===3" x-transition class="grid gap-4">
                        <h4 class="text-center font-medium text-secondary dark:text-white">Store Details</h4>
                        <div class="grid gap-4">
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Store
                                    Description</label>
                                <textarea rows="4" x-model.trim="storeForm.description"
                                    placeholder="Brief description of your store"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm resize-vertical"></textarea>
                            </div>
                            <div class="grid gap-2">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Store Logo</label>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-16 h-16 bg-gray-100 dark:bg-white/10 rounded-lg flex items-center justify-center overflow-hidden">
                                        <template x-if="!storeForm.logo_preview">
                                            <i data-lucide="store" class="w-6 h-6 text-gray-400"></i>
                                        </template>
                                        <img x-show="storeForm.logo_preview" :src="storeForm.logo_preview"
                                            alt="Logo preview" class="w-full h-full object-cover rounded-lg">
                                    </div>
                                    <label
                                        class="cursor-pointer px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">
                                        <input type="file" class="hidden" accept="image/*" @change="onLogoChange">
                                        Upload Logo
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-white/60">Recommended size: 512x512 pixels.
                                    Max 2MB.</p>
                            </div>
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Website
                                    (Optional)</label>
                                <input type="url" x-model.trim="storeForm.website_url" placeholder="https://example.com"
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                            </div>
                            <div class="grid gap-1">
                                <label class="text-sm font-semibold text-secondary dark:text-white">Social Media
                                    (Optional)</label>
                                <input type="text" x-model.trim="storeForm.social_media"
                                    placeholder="Facebook, Instagram, etc."
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-transparent text-gray-900 dark:text-white px-3 py-2.5 text-sm">
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button type="button" @click="backStep2"
                                class="px-4 py-2.5 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Back</button>
                            <button type="button" @click="saveStore"
                                class="px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-primary/90">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="modals.invite" class="fixed inset-0 z-50 p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeInviteModal"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden mx-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white" x-text="inviteModal.title"></h3>
                    <button @click="closeInviteModal"
                        class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:bg-transparent">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="text-sm text-gray-700 dark:text-white/80 py-2" x-html="inviteModal.content"></div>
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" @click="closeInviteModal"
                        class="px-4 py-2 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                    <button type="button" @click="confirmInvite"
                        :class="inviteModal.action==='approve'?'bg-green-600 hover:bg-green-700':'bg-red-600 hover:bg-red-700'"
                        class="px-4 py-2 text-white rounded-xl">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modals.location" class="fixed inset-0 z-[60] p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.location=false"></div>
        <div class="relative z-10 max-w-lg w-full mx-auto bg-white dark:bg-secondary rounded-2xl shadow-xl">
            <div class="p-5 border-b border-gray-100 dark:border-white/10">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Enable location to use My Location</h3>
                <p class="text-sm text-gray-600 dark:text-white/70 mt-1" x-text="locationHelp.subtitle"></p>
            </div>
            <div class="p-5 grid gap-3">
                <template x-if="locationHelp.type==='browser'">
                    <ol class="list-decimal list-inside text-sm text-gray-800 dark:text-white/80 space-y-1">
                        <li><span class="font-medium">Click My Location again</span> to trigger the permission prompt.
                        </li>
                        <li>When your browser shows the location prompt, choose <span class="font-medium">Allow</span>.
                        </li>
                        <li>If you previously blocked it, open <span class="font-medium"
                                x-text="locationHelp.siteSettingsLabel"></span> and set Location to Allow.</li>
                    </ol>
                </template>
                <template x-if="locationHelp.type==='ios'">
                    <ol class="list-decimal list-inside text-sm text-gray-800 dark:text-white/80 space-y-1">
                        <li>Open Settings.</li>
                        <li>Privacy and Security.</li>
                        <li>Location Services.</li>
                        <li>Safari Websites or your browser app.</li>
                        <li>Set Allow Location Access to While Using the App.</li>
                    </ol>
                </template>
                <template x-if="locationHelp.type==='android'">
                    <ol class="list-decimal list-inside text-sm text-gray-800 dark:text-white/80 space-y-1">
                        <li>Open Settings.</li>
                        <li>Apps.</li>
                        <li>Your browser app.</li>
                        <li>Permissions.</li>
                        <li>Location and set to Allow only while using.</li>
                    </ol>
                </template>
                <div class="flex flex-wrap gap-2 mt-2">
                    <button type="button"
                        class="px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 inline-flex items-center gap-2"
                        @click="retryLocation">
                        <i data-lucide="crosshair" class="w-4 h-4"></i>Try Again Now
                    </button>
                    <button type="button"
                        class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10"
                        @click="openSiteSettings">
                        Open site settings
                    </button>
                    <button type="button"
                        class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10"
                        @click="modals.location=false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="loading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-secondary p-6 rounded-lg shadow-lg flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
            <p class="text-gray-700 dark:text-white/80">Processing...</p>
        </div>
    </div>

    <div x-show="modals.toast" class="fixed top-4 right-4 z-[60]">
        <div class="rounded-xl shadow-lg px-4 py-3 text-sm flex items-center gap-2" :class="toast.style">
            <i :data-lucide="toast.icon" class="w-4 h-4"></i><span x-html="toast.msg"></span>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-pip@1.1.0/leaflet-pip.js"></script>

<script>
    function zzimbaStores() {
        return {
            stores: [],
            pendingInvitations: [],
            natureOfBusiness: [],
            loading: false,
            modals: { store: false, invite: false, toast: false, location: false },
            toast: { msg: '', style: 'bg-blue-100 text-blue-700 border border-blue-200', icon: 'info' },
            inviteModal: { action: null, managerId: null, title: '', content: '' },
            locationHelp: { type: 'browser', subtitle: 'Your browser will ask for access when you press Try Again', siteSettingsLabel: 'Site settings' },
            storeForm: {
                mode: 'create', step: 1, id: '', name: '', business_email: '', phone_local: '', contact_person_name: '',
                nature_of_business: '', mapStyle: 'osm', latitude: '', longitude: '', address: '', region: '', district: '',
                subcounty: '', parish: '', description: '', website_url: '', social_media: '', logo_file: null, logo_preview: ''
            },
            map: null, marker: null, mapLayers: {}, ugGeo: null, ugGeoSel: null, _geoLayer: null, _allGeoLayer: null, _markerIcon: null,
            regions: [], districts: [], subcounties: [], parishes: [],
            mapSearchQuery: '', mapSearchResults: [], mapSearchLoading: false, mapSearchOpen: false, _searchDebounce: null, _searchAbort: null,
            _prevMarkerLatLng: null, _geoPermStatus: null,

            init() { this.loadAll(); this.$nextTick(() => this.renderIcons()); },
            renderIcons() { if (window.lucide) { window.lucide.createIcons(); } },

            async loadAll() {
                this.loading = true;
                await Promise.all([this.loadStores(), this.loadInvites(), this.loadNature()]);
                this.loading = false;
                this.$nextTick(() => this.renderIcons());
            },
            async loadStores() {
                try {
                    const owned = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=getOwnedStores').then(r => r.json());
                    const managed = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=getManagedStores').then(r => r.json());
                    const o = (owned?.success ? (owned.stores || []) : []).map(s => ({ ...s, type: 'owned' }));
                    const m = (managed?.success ? (managed.stores || []) : []).map(s => ({ ...s, type: 'managed' }));
                    this.stores = [...o, ...m];
                } catch { this.stores = []; }
            },
            async loadInvites() {
                try {
                    const resp = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=getPendingInvitations').then(r => r.json());
                    this.pendingInvitations = resp?.success ? (resp.invitations || []) : [];
                } catch { this.pendingInvitations = []; }
            },
            async loadNature() {
                try {
                    const resp = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=getNatureOfBusiness').then(r => r.json());
                    this.natureOfBusiness = resp?.success ? (resp.natureOfBusiness || []) : [];
                } catch { this.natureOfBusiness = []; }
            },

            openStoreModal(mode, id = null) {
                this.resetForm();
                this.storeForm.mode = mode;
                this.modals.store = true;
                this.$nextTick(() => { this.renderIcons(); this.initMap(); this.loadRegionData(); if (mode === 'edit' && id) { this.fetchStore(id); } });
            },
            closeStoreModal() { this.modals.store = false; this.destroyMap(); },
            closeInviteModal() { this.modals.invite = false; this.inviteModal = { action: null, managerId: null, title: '', content: '' }; },

            resetForm() {
                Object.assign(this.storeForm, {
                    mode: 'create', step: 1, id: '', name: '', business_email: '', phone_local: '', contact_person_name: '',
                    nature_of_business: '', mapStyle: 'osm', latitude: '', longitude: '', address: '', region: '', district: '', subcounty: '', parish: '',
                    description: '', website_url: '', social_media: '', logo_file: null, logo_preview: ''
                });
                this.districts = []; this.subcounties = []; this.parishes = [];
                this.mapSearchQuery = ''; this.mapSearchResults = []; this.mapSearchOpen = false; this.mapSearchLoading = false;
                this._prevMarkerLatLng = null;
            },

            async fetchStore(id) {
                this.loading = true;
                try {
                    const resp = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=getStoreDetails&id=' + id).then(r => r.json());
                    if (resp?.success) {
                        const s = resp.store;
                        this.storeForm.id = s.uuid_id || '';
                        this.storeForm.name = s.name || '';
                        this.storeForm.business_email = s.business_email || '';
                        this.storeForm.phone_local = (s.business_phone || '').replace(/^\+?256/, '') || '';
                        this.storeForm.contact_person_name = s.contact_person_name || '';
                        this.storeForm.nature_of_business = s.nature_of_business || '';
                        this.storeForm.latitude = s.latitude || '';
                        this.storeForm.longitude = s.longitude || '';
                        this.storeForm.address = s.address || '';
                        this.storeForm.description = s.description || '';
                        this.storeForm.website_url = s.website_url || '';
                        this.storeForm.social_media = s.social_media || '';
                        if (s.logo_url) { this.storeForm.logo_preview = BASE_URL + s.logo_url; }
                        await this.loadRegionData(s.region, s.district, s.subcounty, s.parish);
                        if (s.latitude && s.longitude) {
                            const ll = L.latLng(parseFloat(s.latitude), parseFloat(s.longitude));
                            if (this.isInsideUganda(ll)) {
                                this.dropMarker(ll);
                                this.map.setView([ll.lat, ll.lng], 12);
                            }
                        }
                    } else {
                        this.toastMsg('Error', resp?.error || 'Failed to load store details', 'error');
                    }
                } catch { this.toastMsg('Error', 'Failed to load store details', 'error'); }
                this.loading = false;
                this.$nextTick(() => this.renderIcons());
            },

            goStep2() {
                if (!this.storeForm.name.trim() || !this.storeForm.business_email.trim() || !this.storeForm.phone_local.trim() || !this.storeForm.nature_of_business || !this.storeForm.contact_person_name.trim()) {
                    this.toastMsg('Missing Info', 'Please fill in all required fields', 'error'); return;
                }
                if (!/^([2375]\d{8})$/.test(this.storeForm.phone_local.replace(/\s+/g, ''))) {
                    this.toastMsg('Invalid Phone', 'Enter a valid UG phone: 7XX XXX XXX (no leading 0)', 'error'); return;
                }
                this.storeForm.step = 2; this.$nextTick(() => { if (this.map) this.map.invalidateSize(); this.renderIcons(); });
            },
            backStep1() { this.storeForm.step = 1; this.$nextTick(() => this.renderIcons()); },
            goStep3() {
                if (!this.storeForm.latitude || !this.storeForm.longitude || !this.storeForm.region || !this.storeForm.district || !this.storeForm.address) {
                    this.toastMsg('Missing Location', 'Select your location and address', 'error'); return;
                }
                this.storeForm.step = 3; this.$nextTick(() => this.renderIcons());
            },
            backStep2() { this.storeForm.step = 2; this.$nextTick(() => { if (this.map) this.map.invalidateSize(); this.renderIcons(); }); },

            onLogoChange(e) {
                const f = e.target.files?.[0]; this.storeForm.logo_file = null; this.storeForm.logo_preview = '';
                if (!f) return; this.storeForm.logo_file = f;
                const r = new FileReader(); r.onload = ev => { this.storeForm.logo_preview = ev.target.result; }; r.readAsDataURL(f);
            },

            async saveStore() {
                const mode = this.storeForm.mode;
                const phoneFull = this.storeForm.phone_local ? ('+256' + this.storeForm.phone_local.replace(/\s+/g, '')) : '';
                let formData = {
                    id: this.storeForm.id,
                    name: this.storeForm.name,
                    business_email: this.storeForm.business_email,
                    business_phone: phoneFull,
                    contact_person_name: this.storeForm.contact_person_name,
                    nature_of_business: this.storeForm.nature_of_business,
                    region: this.storeForm.region,
                    district: this.storeForm.district,
                    subcounty: this.storeForm.subcounty,
                    parish: this.storeForm.parish,
                    address: this.storeForm.address,
                    latitude: this.storeForm.latitude,
                    longitude: this.storeForm.longitude,
                    description: this.storeForm.description,
                    website_url: this.storeForm.website_url,
                    social_media: this.storeForm.social_media
                };
                if (this.storeForm.logo_file) {
                    try {
                        const fd = new FormData(); fd.append('logo', this.storeForm.logo_file);
                        this.loading = true;
                        const up = await fetch(BASE_URL + 'account/fetch/manageZzimbaStores.php?action=uploadLogo', { method: 'POST', body: fd }).then(r => r.json());
                        if (up?.success) { formData.temp_logo_path = up.temp_path; }
                        else { this.loading = false; this.toastMsg('Error', up?.message || 'Failed to upload logo', 'error'); return; }
                    } catch { this.loading = false; this.toastMsg('Error', 'Failed to upload logo', 'error'); return; }
                }
                try {
                    this.loading = true;
                    const url = BASE_URL + 'account/fetch/manageZzimbaStores.php?action=' + (mode === 'create' ? 'createStore' : 'updateStore');
                    const resp = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(formData) }).then(r => r.json());
                    this.loading = false;
                    if (resp?.success) { this.modals.store = false; this.toastMsg('Success', resp.message || 'Store saved', 'success'); await this.loadStores(); }
                    else { this.toastMsg('Error', resp?.error || 'Failed to save store', 'error'); }
                } catch { this.loading = false; this.toastMsg('Error', 'Failed to save store', 'error'); }
            },

            manageStore(uuid) {
                this.loading = true;
                fetch(BASE_URL + 'account/fetch/initVendorSession.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ store_uuid: uuid }) })
                    .then(r => r.json())
                    .then(resp => { this.loading = false; if (resp?.success && resp.redirect_url) { window.location.href = resp.redirect_url; } else { this.toastMsg('Error', resp?.message || 'Failed to initiate store session', 'error'); } })
                    .catch(() => { this.loading = false; this.toastMsg('Error', 'Server error occurred', 'error'); });
            },

            openInvitation(action, id, storeName) {
                this.inviteModal.action = action;
                this.inviteModal.managerId = id;
                this.inviteModal.title = action === 'approve' ? 'Approve Invitation' : 'Decline Invitation';
                this.inviteModal.content = action === 'approve'
                    ? `Are you sure you want to approve the invitation to manage <strong>${this.escapeText(storeName)}</strong>?`
                    : `Are you sure you want to decline the invitation to manage <strong>${this.escapeText(storeName)}</strong>? This cannot be undone.`;
                this.modals.invite = true;
                this.$nextTick(() => this.renderIcons());
            },

            initMap(lat = 1.3733, lng = 32.2903) {
                if (this.map) return;
                this.map = L.map('storeMapContainer').setView([lat, lng], 7);
                this.mapLayers = {
                    osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }),
                    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri' })
                };
                this.mapLayers.osm.addTo(this.map);
                this._markerIcon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    tooltipAnchor: [16, -28],
                    shadowSize: [41, 41]
                });
                this.map.on('click', e => {
                    if (!this.ugGeo) { this.toastMsg('Please wait', 'Loading Uganda map data', 'warning'); return; }
                    const ll = e.latlng;
                    if (!this.isInsideUganda(ll)) { this.toastMsg('Restricted', 'Select a location inside Uganda', 'error'); return; }
                    this.dropMarker(ll);
                    this.setAdminFromPoint(ll);
                });
            },
            destroyMap() {
                if (this.map) { this.map.remove(); this.map = null; }
                this.marker = null;
                if (this._geoLayer) { this._geoLayer.remove(); this._geoLayer = null; }
                this._allGeoLayer = null;
            },
            applyMapStyle() {
                if (!this.map) return;
                Object.values(this.mapLayers).forEach(l => { if (this.map.hasLayer(l)) this.map.removeLayer(l); });
                const lay = this.mapLayers[this.storeForm.mapStyle]; if (lay) lay.addTo(this.map);
            },
            dropMarker(latlng) {
                if (this.marker) this.map.removeLayer(this.marker);
                this.marker = L.marker(latlng, { draggable: true, icon: this._markerIcon, zIndexOffset: 1000 }).addTo(this.map);
                this._prevMarkerLatLng = L.latLng(latlng.lat, latlng.lng);
                this.storeForm.latitude = latlng.lat.toFixed(6);
                this.storeForm.longitude = latlng.lng.toFixed(6);
                this.reverseGeocode(latlng.lat, latlng.lng);
                this.marker.on('dragend', () => {
                    const p = this.marker.getLatLng();
                    if (!this.isInsideUganda(p)) {
                        this.marker.setLatLng(this._prevMarkerLatLng);
                        this.toastMsg('Restricted', 'Select a location inside Uganda', 'error');
                        return;
                    }
                    this._prevMarkerLatLng = L.latLng(p.lat, p.lng);
                    this.storeForm.latitude = p.lat.toFixed(6);
                    this.storeForm.longitude = p.lng.toFixed(6);
                    this.reverseGeocode(p.lat, p.lng);
                    this.setAdminFromPoint(p);
                });
            },
            async locateMe() {
                const info = this.browserInfo();
                this.prepareLocationHelp(info);
                const request = () => {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude, lng = pos.coords.longitude;
                        const ll = L.latLng(lat, lng);
                        if (!this.isInsideUganda(ll)) { this.toastMsg('Restricted', 'Your location is outside Uganda. Select a location inside Uganda', 'error'); return; }
                        this.map.setView([lat, lng], 15);
                        this.dropMarker(ll);
                        this.setAdminFromPoint(ll);
                        this.modals.location = false;
                    }, err => {
                        if (err.code === 1) {
                            this.toastMsg('Location required', 'Allow location access to use My Location, then press Try Again', 'warning');
                            this.modals.location = true;
                        } else if (err.code === 3) {
                            this.toastMsg('Timeout', 'Could not get your location in time. Try again', 'error');
                        } else {
                            this.toastMsg('Error', 'Unable to get your location', 'error');
                        }
                    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
                };
                if (!navigator.geolocation) { this.toastMsg('Geolocation', 'Not supported by your browser', 'warning'); return; }
                if (navigator.permissions && navigator.permissions.query) {
                    try {
                        const status = await navigator.permissions.query({ name: 'geolocation' });
                        this._geoPermStatus = status;
                        status.onchange = () => {
                            if (status.state === 'granted' || status.state === 'prompt') request();
                        };
                        if (status.state === 'granted' || status.state === 'prompt') {
                            request();
                        } else {
                            this.modals.location = true;
                            request();
                        }
                    } catch {
                        this.modals.location = false;
                        request();
                    }
                } else {
                    request();
                }
            },
            retryLocation() {
                this.modals.location = false;
                this.locateMe();
            },
            openSiteSettings() {
                const info = this.browserInfo();
                const host = window.location.host;
                if (info.browser === 'Chrome') {
                    try { window.open('chrome://settings/content/location', '_blank'); } catch { }
                } else if (info.browser === 'Edge') {
                    try { window.open('edge://settings/content/location', '_blank'); } catch { }
                } else if (info.browser === 'Opera') {
                    try { window.open('opera://settings/content/location', '_blank'); } catch { }
                } else if (info.browser === 'Firefox') {
                    try { window.open('about:preferences#privacy', '_blank'); } catch { }
                }
                this.toastMsg('Tip', `Click the padlock near ${host} and allow Location, then press Try Again`, 'info');
            },
            prepareLocationHelp(info) {
                if (info.platform === 'iOS') this.locationHelp = { type: 'ios', subtitle: 'Enable location for your browser in Settings', siteSettingsLabel: 'Site settings' };
                else if (info.platform === 'Android') this.locationHelp = { type: 'android', subtitle: 'Enable location permission for your browser app', siteSettingsLabel: 'App settings' };
                else this.locationHelp = { type: 'browser', subtitle: 'Your browser will ask for access when you press Try Again', siteSettingsLabel: info.browser === 'Firefox' ? 'Preferences' : 'Site settings' };
            },
            browserInfo() {
                const ua = navigator.userAgent || '';
                const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
                const isAndroid = /Android/.test(ua);
                const platform = isIOS ? 'iOS' : (isAndroid ? 'Android' : 'Desktop');
                let browser = 'Other';
                if (/Edg\//.test(ua)) browser = 'Edge';
                else if (/OPR\//.test(ua)) browser = 'Opera';
                else if (/Chrome\//.test(ua) && !/Edg\//.test(ua) && !/OPR\//.test(ua)) browser = 'Chrome';
                else if (/Firefox\//.test(ua)) browser = 'Firefox';
                else if (/Safari\//.test(ua) && !/Chrome\//.test(ua)) browser = 'Safari';
                return { platform, browser };
            },
            reverseGeocode(lat, lng) {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                fetch(url, { headers: { 'User-Agent': 'Zzimba Store Locator' } })
                    .then(r => r.json())
                    .then(d => { if (d?.display_name) { this.storeForm.address = d.display_name; } })
                    .catch(() => { });
            },

            onSearchInput() {
                this.mapSearchOpen = true;
                if (this._searchDebounce) clearTimeout(this._searchDebounce);
                this._searchDebounce = setTimeout(() => { this.searchMap(); }, 350);
            },
            clearSearch() {
                this.mapSearchQuery = '';
                this.mapSearchResults = [];
                this.mapSearchOpen = false;
                this.mapSearchLoading = false;
                if (this._searchAbort) this._searchAbort.abort();
            },
            async searchMap() {
                const q = this.mapSearchQuery.trim();
                if (q.length < 3) { this.mapSearchResults = []; this.mapSearchLoading = false; return; }
                if (this._searchAbort) this._searchAbort.abort();
                const ctl = new AbortController(); this._searchAbort = ctl;
                this.mapSearchLoading = true;
                try {
                    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&addressdetails=1&countrycodes=ug&limit=8`;
                    const resp = await fetch(url, { headers: { 'User-Agent': 'Zzimba Store Locator' }, signal: ctl.signal });
                    const data = await resp.json();
                    this.mapSearchResults = (data || []).map((d, i) => ({ key: `${d.place_id}-${i}`, lat: parseFloat(d.lat), lon: parseFloat(d.lon), name: d.display_name }));
                } catch { }
                this.mapSearchLoading = false;
            },
            selectSearchResult(res) {
                this.mapSearchOpen = false;
                if (!this.map) return;
                const ll = L.latLng(res.lat, res.lon);
                if (!this.isInsideUganda(ll)) { this.toastMsg('Restricted', 'Select a location inside Uganda', 'error'); return; }
                this.map.setView(ll, 16);
                this.dropMarker(ll);
                this.storeForm.address = res.name || this.storeForm.address;
                this.setAdminFromPoint(ll);
            },

            async loadRegionData(selReg = null, selDist = null, selSub = null, selParish = null) {
                try {
                    const data = await fetch('<?= BASE_URL ?>locations/gadm41_UGA_4.json').then(r => r.json());
                    this.ugGeo = data; this.ugGeoSel = null;
                    const set = new Set();
                    data.features.forEach(f => { if (f.properties.NAME_1) set.add(f.properties.NAME_1); });
                    this.regions = [...set].sort();
                    if (selReg) { this.storeForm.region = selReg; await this.onRegionChange(selDist, selSub, selParish); }
                } catch { this.toastMsg('Error', 'Failed to load regions data', 'error'); }
            },
            onRegionChange(selDist = null, selSub = null, selParish = null) {
                const region = this.storeForm.region;
                this.districts = []; this.subcounties = []; this.parishes = [];
                if (!region) { this.updateGeoLayer(); return; }
                const set = new Set();
                this.ugGeo.features.forEach(f => { if (f.properties.NAME_1 === region) set.add(f.properties.NAME_2); });
                this.districts = [...set].sort();
                this.storeForm.district = selDist || '';
                this.storeForm.subcounty = ''; this.storeForm.parish = '';
                this.updateGeoLayer({ 1: region });
                if (selDist) this.onDistrictChange(selSub, selParish);
            },
            onDistrictChange(selSub = null, selParish = null) {
                const r = this.storeForm.region, d = this.storeForm.district;
                this.subcounties = []; this.parishes = [];
                if (!r || !d) { this.updateGeoLayer(r ? { 1: r } : {}); return; }
                const set = new Set();
                this.ugGeo.features.forEach(f => { if (f.properties.NAME_1 === r && f.properties.NAME_2 === d) set.add(f.properties.NAME_3); });
                this.subcounties = [...set].sort();
                this.storeForm.subcounty = selSub || '';
                this.storeForm.parish = '';
                this.updateGeoLayer({ 1: r, 2: d });
                if (selSub) this.onSubcountyChange(selParish);
            },
            onSubcountyChange(selParish = null) {
                const r = this.storeForm.region, d = this.storeForm.district, s = this.storeForm.subcounty;
                this.parishes = [];
                if (!r || !d || !s) { this.updateGeoLayer({ 1: r, 2: d }); return; }
                const set = new Set();
                this.ugGeo.features.forEach(f => { if (f.properties.NAME_1 === r && f.properties.NAME_2 === d && f.properties.NAME_3 === s && f.properties.NAME_4) set.add(f.properties.NAME_4); });
                this.parishes = [...set].sort();
                this.storeForm.parish = selParish || '';
                this.updateGeoLayer({ 1: r, 2: d, 3: s });
                if (selParish) this.onParishChange();
            },
            onParishChange() {
                const r = this.storeForm.region, d = this.storeForm.district, s = this.storeForm.subcounty, p = this.storeForm.parish;
                const sel = { 1: r, 2: d }; if (s) sel[3] = s; if (p) sel[4] = p;
                this.updateGeoLayer(sel);
            },
            updateGeoLayer(selections = {}) {
                if (this._geoLayer) { this._geoLayer.remove(); this._geoLayer = null; }
                if (!this.map || !this.ugGeo) { this.ugGeoSel = null; return; }
                const filtered = this.ugGeo.features.filter(ft => {
                    for (const [lvl, val] of Object.entries(selections)) { if (ft.properties['NAME_' + lvl] !== val) return false; }
                    return true;
                });
                if (!filtered.length) { this.ugGeoSel = null; return; }
                const gj = { type: 'FeatureCollection', features: filtered };
                this.ugGeoSel = gj;
                this._geoLayer = L.geoJSON(gj, { style: { color: '#C00000', weight: 2, opacity: 1, fillColor: '#C00000', fillOpacity: .2 } }).addTo(this.map);
                this.map.fitBounds(this._geoLayer.getBounds(), { padding: [20, 20], maxZoom: 12, animate: true });
            },
            isInsideUganda(latlng) {
                if (!this.ugGeo) return false;
                if (!this._allGeoLayer) this._allGeoLayer = L.geoJSON(this.ugGeo);
                return leafletPip.pointInLayer([latlng.lng, latlng.lat], this._allGeoLayer, true).length > 0;
            },
            setAdminFromPoint(latlng) {
                if (!this.ugGeo) return;
                if (!this._allGeoLayer) this._allGeoLayer = L.geoJSON(this.ugGeo);
                const hits = leafletPip.pointInLayer([latlng.lng, latlng.lat], this._allGeoLayer, true);
                if (hits.length) {
                    const props = hits[0].feature.properties || {};
                    const r = props.NAME_1 || '';
                    const d = props.NAME_2 || '';
                    const s = props.NAME_3 || '';
                    const p = props.NAME_4 || '';
                    if (r && r !== this.storeForm.region) {
                        this.storeForm.region = r;
                        this.onRegionChange(d, s, p);
                    } else if (d && d !== this.storeForm.district) {
                        this.storeForm.district = d;
                        this.onDistrictChange(s, p);
                    } else if (s && s !== this.storeForm.subcounty) {
                        this.storeForm.subcounty = s;
                        this.onSubcountyChange(p);
                    } else if (p) {
                        this.storeForm.parish = p;
                        this.onParishChange();
                    }
                }
            },

            statusBadge(s) {
                if (s === 'active') return { bg: 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300', dot: 'bg-green-500', label: 'Active' };
                if (s === 'pending') return { bg: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300', dot: 'bg-yellow-500', label: 'Pending' };
                if (s === 'inactive') return { bg: 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300', dot: 'bg-red-500', label: 'Inactive' };
                if (s === 'suspended') return { bg: 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80', dot: 'bg-gray-500', label: 'Suspended' };
                return { bg: 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80', dot: 'bg-gray-500', label: (s ? s[0].toUpperCase() + s.slice(1) : 'Unknown') };
            },

            toastMsg(title, html, type = 'info') {
                const map = { success: ['bg-green-100 text-green-700 border border-green-200', 'check'], error: ['bg-red-100 text-red-700 border border-red-200', 'x'], warning: ['bg-amber-100 text-amber-700 border border-amber-200', 'alert-triangle'], info: ['bg-blue-100 text-blue-700 border border-blue-200', 'info'] };
                const [style, icon] = map[type] || map.info;
                this.toast = { msg: `<strong>${this.escapeText(title)}</strong> - ${html}`, style, icon };
                this.modals.toast = true; this.$nextTick(() => this.renderIcons());
                setTimeout(() => { this.modals.toast = false; }, 3000);
            },
            timeAgo(dt) {
                const date = new Date(dt); const s = Math.floor((new Date() - date) / 1000);
                if (s < 60) return 'just now'; const m = Math.floor(s / 60); if (m < 60) return m + ' minute' + (m === 1 ? '' : 's') + ' ago';
                const h = Math.floor(m / 60); if (h < 24) return h + ' hour' + (h === 1 ? '' : 's') + ' ago';
                const d = Math.floor(h / 24); if (d < 30) return d + ' day' + (d === 1 ? '' : 's') + ' ago';
                const mo = Math.floor(d / 30); if (mo < 12) return mo + ' month' + (mo === 1 ? '' : 's') + ' ago';
                const y = Math.floor(mo / 12); return y + ' year' + (y === 1 ? '' : 's') + ' ago';
            },
            escapeText(s) { return String(s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[m])); }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>