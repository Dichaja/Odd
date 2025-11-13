<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Products';
$activeNav = 'products';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || empty($_SESSION['user']['logged_in'])) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL);
    exit;
}
$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$storeStmt = $pdo->prepare("SELECT id, name, owner_id FROM vendor_stores WHERE id = :sid AND status IN ('active','pending','inactive','suspended')");
$storeStmt->execute([':sid' => $storeId]);
$store = $storeStmt->fetch(PDO::FETCH_ASSOC);
if (!$store) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$storeName = $store['name'];
$isAdmin = !empty($_SESSION['user']['is_admin']);
$isOwner = $store['owner_id'] === $_SESSION['user']['user_id'];
$isManager = false;
if (!$isAdmin && !$isOwner) {
    $mgr = $pdo->prepare("SELECT 1 FROM store_managers WHERE store_id = :sid AND user_id = :uid AND status = 'active' AND approved = 1 LIMIT 1");
    $mgr->execute([':sid' => $storeId, ':uid' => $_SESSION['user']['user_id']]);
    $isManager = (bool) $mgr->fetchColumn();
}
if (!$isAdmin && !$isOwner && !$isManager) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}
$title = isset($pageTitle) ? "{$pageTitle} - {$storeName} | Store Dashboard" : "{$storeName} Store Dashboard";
$activeNav = $activeNav ?? 'products';
$userName = $_SESSION['user']['username'];
$storeInitials = '';
$parts = array_filter(explode(' ', $storeName));
$limitedParts = array_slice($parts, 0, 2);
foreach ($limitedParts as $part) {
    $storeInitials .= strtoupper($part[0]);
}
$sessionUlid = generateUlid();
ob_start();
?>
<style>
    .custom-select {
        position: relative
    }

    .custom-select__btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: .5rem .75rem;
        border: 1px solid #d1d5db;
        border-radius: .5rem;
        background: #fff;
        transition: box-shadow .2s
    }

    .custom-select__btn:focus {
        outline: 0;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .5)
    }

    .custom-select__panel {
        position: absolute;
        inset-inline: 0;
        top: 100%;
        margin-top: .25rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        max-height: 14rem;
        overflow: auto;
        z-index: 50
    }

    .custom-select__opt {
        padding: .5rem .75rem;
        cursor: pointer;
        font-size: .875rem;
        display: flex;
        align-items: center;
        justify-content: space-between
    }

    .custom-select__opt:hover {
        background: #f9fafb
    }

    .custom-select__tag {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .25rem .5rem;
        border-radius: 9999px;
        background: #f3f4f6;
        font-size: .75rem
    }

    .modal-panel {
        display: flex;
        flex-direction: column;
        max-height: calc(100dvh - 2rem);
        overscroll-behavior: contain;
        border-radius: 1rem
    }

    @supports not (height:1dvh) {
        .modal-panel {
            max-height: calc(100vh - 2rem)
        }
    }

    .modal-scroll {
        overflow-y: auto;
        -webkit-overflow-scrolling: touch
    }

    .tab-btn {
        padding: .5rem 1rem;
        border-radius: .5rem;
        border: 1px solid transparent
    }

    .tab-active {
        background: #111827;
        color: #fff;
        border-color: #111827
    }

    .tab-idle {
        background: #fff;
        color: #374151;
        border-color: #d1d5db
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 2.5rem;
        height: 1.25rem
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e5e7eb;
        transition: .2s;
        border-radius: 9999px
    }

    .slider:before {
        position: absolute;
        content: "";
        height: .95rem;
        width: .95rem;
        left: .2rem;
        top: .15rem;
        background-color: #fff;
        transition: .2s;
        border-radius: 9999px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .15)
    }

    input:checked+.slider {
        background-color: #111827
    }

    input:checked+.slider:before {
        transform: translateX(1.25rem)
    }
</style>
<div x-data="productsPage()" x-init="init()" class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="hidden sm:flex items-center gap-2 mb-5">
            <button @click="switchTab('inStore')" :class="activeTab==='inStore'?'tab-active':'tab-idle'"
                class="tab-btn">
                <span>My Store Products</span>
                <sup class="ml-1 text-xs" x-text="formatCountDisplay(store.items.length)"></sup>
            </button>
            <button @click="switchTab('myProducts')" :class="activeTab==='myProducts'?'tab-active':'tab-idle'"
                class="tab-btn">
                <span>My Created Products</span>
                <sup class="ml-1 text-xs" x-text="formatCountDisplay(my.items.length)"></sup>
            </button>
        </div>
        <div class="sm:hidden mb-5">
            <label class="text-sm text-gray-600 mb-1 block">View</label>
            <select x-model="activeTab" @change="onTabSelectChange"
                class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <option value="inStore">My Store Products</option>
                <option value="myProducts">My Created Products</option>
            </select>
        </div>
        <form @submit.prevent="applySearch()" class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <input x-model="search" type="text" placeholder="Search by name or category"
                    class="w-full pl-3 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent">
                <button type="submit"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 h-8 w-8 rounded-md grid place-items-center bg-gray-900 hover:bg-black text-white">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="flex gap-2">
                <button type="button" @click="clearSearch()"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                    <i data-lucide="x" class="w-4 h-4"></i>
                    Clear
                </button>
                <div class="relative" x-show="activeTab==='inStore'">
                    <button type="button" @click="toggleAddMenu()"
                        class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-2">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Add Product
                    </button>
                    <div x-show="addMenuOpen" @click.outside="addMenuOpen=false"
                        class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-10">
                        <button @click="openAddExisting()"
                            class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                            <i data-lucide="boxes" class="w-4 h-4"></i>
                            Select From Existing
                        </button>
                        <button @click="openCreateNew()"
                            class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-2">
                            <i data-lucide="square-plus" class="w-4 h-4"></i>
                            Create New
                        </button>
                    </div>
                </div>
                <div class="relative" x-show="activeTab==='myProducts'">
                    <button type="button" @click="openCreateNew()"
                        class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-2">
                        <i data-lucide="square-plus" class="w-5 h-5"></i>
                        New Product
                    </button>
                </div>
            </div>
        </form>
        <div x-show="activeTab==='inStore'" class="mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <button @click="inStoreFilter='all'" class="px-3 py-1.5 text-sm rounded-full border transition"
                    :class="inStoreFilter==='all'?'bg-gray-900 text-white border-gray-900':'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    All
                    <span class="ml-1 text-xs text-gray-500" x-text="countInStoreAll()"></span>
                </button>
                <button @click="inStoreFilter='with'" class="px-3 py-1.5 text-sm rounded-full border transition"
                    :class="inStoreFilter==='with'?'bg-gray-900 text-white border-gray-900':'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    With pricing
                    <span class="ml-1 text-xs text-gray-500" x-text="countInStoreWithPricing()"></span>
                </button>
                <button @click="inStoreFilter='without'" class="px-3 py-1.5 text-sm rounded-full border transition"
                    :class="inStoreFilter==='without'?'bg-gray-900 text-white border-gray-900':'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                    Without pricing
                    <span class="ml-1 text-xs text-gray-500" x-text="countInStoreWithoutPricing()"></span>
                </button>
            </div>
        </div>
        <div x-show="loading" class="text-center py-12">
            <i data-lucide="loader-2" class="w-10 h-10 text-gray-400 mx-auto mb-4 animate-spin"></i>
            <p class="text-gray-600">Loading products...</p>
        </div>
        <div x-show="!loading && activeList().length===0" class="text-center py-12">
            <i data-lucide="package-open" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No products found</h3>
            <p class="text-sm text-gray-500">Try adjusting your search or add a product.</p>
        </div>
        <div x-show="!loading && activeList().length>0" id="productsGrid"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="p in activeList()" :key="p.store_product_id ?? p.id">
                <div
                    class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 flex flex-col h-full">
                    <div class="relative">
                        <img :src="p._image || placeholderImg" @error="$event.target.src=placeholderImg"
                            :alt="p.name || p.title" class="w-full h-44 object-cover rounded-t-xl bg-gray-100">
                        <template x-if="activeTab==='inStore'">
                            <div class="absolute top-2 right-2 flex gap-2">
                                <button @click="openPricingList(p)"
                                    class="bg-white px-3 py-1.5 rounded-full shadow-md hover:bg-gray-50 transition text-gray-700 text-sm font-medium flex items-center gap-1">
                                    <i data-lucide="pen-line" class="w-4 h-4"></i>
                                    Edit Pricing
                                </button>
                            </div>
                        </template>
                        <template x-if="activeTab==='myProducts'">
                            <div class="absolute top-2 right-2 flex gap-2">
                                <button @click="openPricingForMy(p)"
                                    class="bg-white px-3 py-1.5 rounded-full shadow-md hover:bg-gray-50 transition text-gray-700 text-sm font-medium flex items-center gap-1">
                                    <i data-lucide="tags" class="w-4 h-4"></i>
                                    Edit Pricing
                                </button>
                                <button @click="openEditMyProduct(p)" :disabled="!p.editable"
                                    class="px-3 py-1.5 rounded-full shadow-md transition text-sm font-medium flex items-center gap-1"
                                    :class="p.editable?'bg-white hover:bg-gray-50 text-gray-700':'bg-gray-200 text-gray-400 cursor-not-allowed'">
                                    <i data-lucide="pen-square" class="w-4 h-4"></i>
                                    Edit
                                </button>
                            </div>
                        </template>
                    </div>
                    <div class="p-4 flex flex-col gap-3 flex-1">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 leading-tight" x-text="p.name || p.title"></h3>
                            <p class="text-sm text-gray-600" x-text="p.category_name || ''"></p>
                            <template x-if="activeTab==='myProducts'">
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-xs"
                                        :class="(p.status||'').toLowerCase()==='draft'?'text-yellow-700':'text-green-700'"
                                        x-text="(p.status||'').toUpperCase() || 'DRAFT'"></p>
                                    <button x-show="(p.status||'').toLowerCase()==='draft'"
                                        @click="confirmDeleteMyDraft(p)"
                                        class="text-xs text-red-600 hover:underline">Delete Draft</button>
                                </div>
                            </template>
                        </div>
                        <template x-if="activeTab==='inStore'">
                            <div class="mt-auto">
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    <template x-if="!p.pricing || p.pricing.length===0">
                                        <span class="text-xs text-gray-500">No pricing</span>
                                    </template>
                                    <template x-for="pr in (p.pricing||[])"
                                        :key="(pr.pricing_id||pr.package_mapping_id)+'-'+(pr.price_category||'')">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-medium"
                                            :class="chipColor(pr.price_category)">
                                            <i data-lucide="tags" class="w-3 h-3"></i>
                                            <span x-text="formatChip(pr)"></span>
                                        </span>
                                    </template>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-xs text-gray-500"
                                        x-text="(p.pricing?.length || 0)+' pricing option'+((p.pricing?.length||0)===1?'':'s')"></span>
                                    <button @click="confirmDeleteFromStore(p)"
                                        class="p-2 rounded-full text-red-600 hover:bg-red-50 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <template x-teleport="body">
        <div id="alertContainer" class="fixed top-4 right-4 z-[1100] space-y-2 pointer-events-none"></div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.selectProduct" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="modals.selectProduct=false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-xl w-full max-w-2xl modal-panel overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <h3 class="text-lg font-semibold text-secondary">Select Product</h3>
                        <div class="flex items-center gap-2">
                            <button @click="openCreateNewFromSelect()"
                                class="px-3 py-1.5 text-sm bg-user-primary text-white rounded-lg hover:bg-user-primary/90">Create
                                New</button>
                            <button @click="modals.selectProduct=false" class="p-2 rounded hover:bg-gray-50">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-scroll p-5 space-y-4">
                        <div class="relative">
                            <i data-lucide="search"
                                class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input x-model="selectSearch" type="text" placeholder="Search products to add"
                                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent">
                        </div>
                        <div class="divide-y">
                            <template x-if="selectLoading">
                                <div class="py-10 text-center text-gray-500">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
                                    Loading...
                                </div>
                            </template>
                            <template x-for="p in selectableProducts()" :key="p.id">
                                <div class="flex items-center justify-between py-3">
                                    <div>
                                        <div class="font-medium text-gray-900" x-text="p.name"></div>
                                        <div class="text-xs text-gray-500" x-text="p.category_name"></div>
                                    </div>
                                    <button @click="beginAddPricing(p)"
                                        class="px-3 py-1.5 text-sm bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-1">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        Add
                                    </button>
                                </div>
                            </template>
                            <template x-if="!selectLoading && selectableProducts().length===0">
                                <div class="py-8 text-center text-gray-500">No products available</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.newProduct" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="closeNewProduct()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-2xl w-full max-w-3xl modal-panel overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <h3 class="text-lg font-semibold text-secondary">Create New Product</h3>
                        <button @click="closeNewProduct()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="modal-scroll p-5 space-y-6">
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Title</label>
                                    <input x-model="newProduct.title" type="text" placeholder="Product title"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Description</label>
                                    <textarea x-model="newProduct.description" rows="4"
                                        placeholder="Product description"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"></textarea>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Package Names</label>
                                    <div class="custom-select" x-data="{open:false}" @keydown.escape.stop="open=false">
                                        <button type="button" class="custom-select__btn" @click="open=!open">
                                            <span
                                                x-text="selectedPackages.length ? selectedPackages.map(p=>p.package_name).join(', ') : 'Select package names'"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                        <div x-show="open" @click.outside="open=false" class="custom-select__panel">
                                            <div class="p-2 sticky top-0 bg-white border-b">
                                                <input x-model="pkgSearch" type="text" placeholder="Filter..."
                                                    class="w-full px-2 py-1 border border-gray-300 rounded">
                                            </div>
                                            <template x-if="filteredPackages().length===0">
                                                <div class="p-3 text-center text-gray-500">No packages</div>
                                            </template>
                                            <template x-for="m in filteredPackages()" :key="m.id">
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="toggleSelectPackage(m)">
                                                    <span x-text="m.package_name"></span>
                                                    <i :data-lucide="isSelectedPackage(m.id)?'check-square':'square'"
                                                        class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                            <div class="border-t">
                                                <button class="w-full custom-select__opt"
                                                    @mousedown.prevent="createNewPackageName()">
                                                    <span
                                                        x-text="pkgSearch ? ('Add &quot;' + pkgSearch + '&quot;') : 'Add new package'"></span>
                                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="p in selectedPackages" :key="p.id">
                                            <span class="custom-select__tag">
                                                <span x-text="p.package_name"></span>
                                                <button class="ml-1 text-gray-500 hover:text-red-600"
                                                    @click="removeSelectedPackage(p.id)">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="w-full bg-gray-50 rounded-lg border border-gray-200 p-2">
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="(img, idx) in newProduct.previews" :key="idx">
                                            <div class="relative">
                                                <img :src="img"
                                                    class="w-full aspect-video object-cover rounded-md bg-gray-100">
                                                <button type="button" @click="removeNewImage(idx)"
                                                    class="absolute top-1 right-1 p-1 rounded-full bg-white shadow text-red-600">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="newProduct.previews.length===0">
                                            <div class="col-span-3 text-center text-gray-400 text-sm py-6">No images
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <input type="file" accept="image/*" multiple class="hidden" x-ref="fileInput"
                                    @change="handleNewImages">
                                <button type="button" @click="$refs.fileInput.click()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                                    Upload Images
                                </button>
                                <template x-if="newProduct.uploading">
                                    <div class="text-sm text-gray-500 flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                        Uploading...
                                    </div>
                                </template>
                                <p class="text-xs text-red-600" x-show="newProduct.temp_images.length===0">At least one
                                    image is required</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t flex justify-end gap-2">
                        <button @click="closeNewProduct()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                        <button @click="submitNewProduct()"
                            class="px-4 py-2 rounded-lg bg-user-primary text-white hover:bg-user-primary/90">Create</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.editMyProduct" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="closeEditMyProduct()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-2xl w-full max-w-3xl modal-panel overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <h3 class="text-lg font-semibold text-secondary"
                            x-text="editProduct.id ? 'Edit Product' : 'Edit Product'"></h3>
                        <button @click="closeEditMyProduct()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="modal-scroll p-5 space-y-6">
                        <div class="grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2 space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Title</label>
                                    <input x-model="editProduct.title" type="text" placeholder="Product title"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Description</label>
                                    <textarea x-model="editProduct.description" rows="4"
                                        placeholder="Product description"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"></textarea>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div
                                    class="w-full aspect-video bg-gray-100 rounded-lg overflow-hidden grid place-items-center border border-gray-200">
                                    <img x-show="editProduct.preview" :src="editProduct.preview"
                                        class="w-full h-full object-cover" alt="">
                                    <div x-show="!editProduct.preview" class="text-gray-400 text-sm">No image</div>
                                </div>
                                <input type="file" accept="image/*" class="hidden" x-ref="fileInputEdit"
                                    @change="handleEditImage">
                                <button type="button" @click="$refs.fileInputEdit.click()"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50"
                                    :disabled="!editProduct.editable">
                                    Upload Image
                                </button>
                                <template x-if="editProduct.uploading">
                                    <div class="text-sm text-gray-500 flex items-center gap-2">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                        Uploading...
                                    </div>
                                </template>
                                <p class="text-xs text-gray-500" x-show="!editProduct.editable">Only draft products can
                                    be edited</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t flex justify-end gap-2">
                        <button @click="closeEditMyProduct()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                        <button @click="submitEditMyProduct()"
                            class="px-4 py-2 rounded-lg bg-user-primary text-white hover:bg-user-primary/90"
                            :disabled="!editProduct.editable">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.pricingList" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="closePricingList"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-xl w-full max-w-3xl modal-panel overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <div class="flex items-center gap-3">
                            <img :src="pricingProduct?._image || placeholderImg"
                                class="w-10 h-10 rounded-md object-cover bg-gray-100" alt="">
                            <div>
                                <h3 class="text-lg font-semibold text-secondary"
                                    x-text="pricingProduct?.name ? 'Manage Pricing - '+pricingProduct.name : 'Manage Pricing'">
                                </h3>
                                <p class="text-xs text-gray-500" x-text="pricingProduct?.category_name || ''"></p>
                            </div>
                        </div>
                        <button @click="closePricingList()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="modal-scroll p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <p x-show="isDraftProduct()"
                                class="text-sm text-yellow-800 bg-yellow-50 px-3 py-1.5 rounded">Product awaiting
                                publishing from admin.</p>
                            <button @click="openStepper('new')" :disabled="!canAddPricing()"
                                class="px-3 py-2 rounded-lg transition flex items-center gap-2"
                                :class="canAddPricing() ? 'bg-gray-900 text-white hover:bg-black' : 'bg-gray-200 text-gray-500 cursor-not-allowed'">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Add Pricing
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="sm:w-[900px] lg:w-auto border rounded-lg overflow-hidden">
                                <div
                                    class="grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600">
                                    <div class="col-span-3">Unit & Size</div>
                                    <div class="col-span-3">Package</div>
                                    <div class="col-span-2">Category</div>
                                    <div class="col-span-2">Price</div>
                                    <div class="col-span-1 text-center">Capacity</div>
                                    <div class="col-span-1 text-right">Edit</div>
                                </div>
                                <template x-if="pricingList.length===0">
                                    <div class="px-4 py-6 text-center text-gray-500">No pricing entries</div>
                                </template>
                                <template x-for="(pr,idx) in pricingList" :key="idx">
                                    <div class="grid grid-cols-12 gap-2 px-4 py-3 border-t items-center"
                                        :class="rowClass(pr)">
                                        <div class="col-span-3">
                                            <div class="text-sm"
                                                x-text="(pr.package_size||'-')+' '+(pr.si_unit||labelForUnit(pr.si_unit_id)||'')">
                                            </div>
                                            <div class="text-[11px] text-gray-500"
                                                x-text="pr.created_by_name || pr.created_by || ''"></div>
                                            <div class="text-[11px] text-gray-400" x-show="pr.created_at"
                                                x-text="formatCreatedAt(pr.created_at)"></div>
                                        </div>
                                        <div class="col-span-3">
                                            <div class="text-sm font-medium"
                                                x-text="pr.package_name || labelForPackage(pr.package_mapping_id)">
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                :class="chipColor(pr.price_category)"
                                                x-text="(pr.price_category||'').toUpperCase()"></span>
                                            <div class="mt-1 flex items-center gap-2">
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        :checked="String(pr.status||'active').toLowerCase()==='active'"
                                                        :disabled="isSuspended(pr)"
                                                        @change="togglePricingStatus(idx,$event.target.checked)">
                                                    <span class="slider"></span>
                                                </label>
                                                <span class="text-[11px]" :class="statusTextClass(pr)"
                                                    x-text="statusDisplay(pr)"></span>
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <div class="text-sm font-semibold" x-text="'UGX '+formatNumber(pr.price)">
                                            </div>
                                            <div class="text-[11px] text-gray-500" x-text="formatCommissionMini(pr)">
                                            </div>
                                        </div>
                                        <div class="col-span-1 text-center">
                                            <div class="text-xs" :class="isSuspended(pr)?'text-red-700':'text-gray-600'"
                                                x-text="pr.delivery_capacity ? pr.delivery_capacity : '-'"></div>
                                        </div>
                                        <div class="col-span-1 text-right">
                                            <button @click="openStepper('edit', idx)"
                                                class="p-2 rounded hover:bg-gray-50">
                                                <i data-lucide="pen-line" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t flex justify-end gap-2">
                        <button @click="closePricingList()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Close</button>
                        <button @click="savePricingChanges()"
                            class="px-4 py-2 rounded-lg bg-user-primary text-white hover:bg-user-primary/90"
                            x-text="pricingProduct?.store_product_id ? 'Save Changes' : 'Add To Store'"></button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.stepper" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="closeStepper()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-2xl w-full max-w-4xl modal-panel overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <div class="flex items-center gap-3">
                            <img :src="stepperProductImage || pricingProduct?._image || placeholderImg"
                                class="w-10 h-10 rounded-md object-cover bg-gray-100" alt="">
                            <div>
                                <h3 class="text-lg font-semibold text-secondary"
                                    x-text="stepper.mode==='new' ? 'Add Pricing' : 'Edit Pricing'"></h3>
                                <p class="text-xs text-gray-500" x-text="pricingProduct?.name || ''"></p>
                                <p class="text-xs text-gray-500" x-text="pricingProduct?.category_name || ''"></p>
                                <p class="text-xs text-gray-700 mt-1" x-show="stepper.mode==='edit'"
                                    x-text="currentPricingCreatorText()"></p>
                            </div>
                        </div>
                        <button @click="closeStepper()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="modal-scroll p-5">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div x-show="stepper.step===1" class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Package</label>
                                    <div class="custom-select" x-data="{open:false}">
                                        <button type="button" class="custom-select__btn" @click="open=!open">
                                            <span x-text="stepper.package_name || 'Select a package'"></span>
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                        <div x-show="open" @click.outside="open=false" class="custom-select__panel">
                                            <div class="p-2 sticky top-0 bg-white border-b">
                                                <input x-model="stepper.packageQuery" type="text"
                                                    placeholder="Filter..."
                                                    class="w-full px-2 py-1 border border-gray-300 rounded">
                                            </div>
                                            <template x-if="availablePackages.length===0">
                                                <div class="p-3 text-center text-gray-500">No packages</div>
                                            </template>
                                            <template
                                                x-for="m in availablePackages.filter(x=>x.package_name.toLowerCase().includes((stepper.packageQuery||'').toLowerCase()))"
                                                :key="m.id">
                                                <div class="custom-select__opt" @mousedown.prevent="selectPackage(m)">
                                                    <span x-text="m.package_name"></span>
                                                    <i :data-lucide="stepper.package_mapping_id===m.id?'check':'plus'"
                                                        class="w-4 h-4"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-xs text-red-600" x-show="errors.package">Select a package</p>
                                </div>
                                <div x-show="stepper.step===2" class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Unit of Measure</label>
                                        <div class="custom-select" x-data="{open:false}">
                                            <button type="button" class="custom-select__btn" @click="open=!open">
                                                <span x-text="stepper.si_unit || 'Select a unit'"></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                            <div x-show="open" @click.outside="open=false" class="custom-select__panel">
                                                <div class="p-2 sticky top-0 bg-white border-b">
                                                    <input x-model="stepper.unitQuery" type="text"
                                                        placeholder="Filter..."
                                                        class="w-full px-2 py-1 border border-gray-300 rounded">
                                                </div>
                                                <template
                                                    x-for="u in availableUnits.filter(x=>x.si_unit.toLowerCase().includes((stepper.unitQuery||'').toLowerCase()))"
                                                    :key="u.id">
                                                    <div class="custom-select__opt" @mousedown.prevent="selectUnit(u)">
                                                        <span x-text="u.si_unit"></span>
                                                        <i :data-lucide="stepper.si_unit_id===u.id?'check':'plus'"
                                                            class="w-4 h-4"></i>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <p class="text-xs text-red-600" x-show="errors.unit">Select a unit</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Unit Size</label>
                                        <input x-model="stepper.package_size" @keydown="handlePackageSizeKeydown"
                                            @input="onPackageSizeInput" @paste="handlePackageSizePaste" type="text"
                                            inputmode="text" placeholder="Enter size"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                            :class="errors.size ? 'border-red-500 ring-2 ring-red-300' : ''" required>
                                    </div>
                                </div>
                                <div x-show="stepper.step===3" class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Price Category</label>
                                        <div class="custom-select" x-data="{open:false}">
                                            <button type="button" class="custom-select__btn" @click="open=!open">
                                                <span
                                                    x-text="stepper.price_category ? stepper.price_category.toUpperCase() : 'Select category'"></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                            <div x-show="open" @click.outside="open=false" class="custom-select__panel">
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="stepper.price_category='retail';open=false">
                                                    Retail</div>
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="stepper.price_category='wholesale';open=false">
                                                    Wholesale</div>
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="stepper.price_category='factory';open=false">
                                                    Factory</div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-red-600" x-show="errors.category">Select a category</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Price (UGX)</label>
                                        <input x-model="stepper.price" type="number" min="0" step="any"
                                            placeholder="Enter price"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                            :class="errors.price ? 'border-red-500 ring-2 ring-red-300' : ''">
                                    </div>
                                </div>
                                <div x-show="stepper.step===4" class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700"
                                        x-text="stepper.price_category==='retail' ? 'Max Capacity' : (stepper.price_category ? 'Min Capacity' : 'Capacity')"></label>
                                    <input x-model="stepper.delivery_capacity" type="number" min="0" step="1"
                                        placeholder="Enter capacity"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                        :class="errors.capacity ? 'border-red-500 ring-2 ring-red-300' : ''">
                                </div>
                                <div x-show="stepper.step===5" class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Commission Type</label>
                                        <div class="custom-select" x-data="{open:false}">
                                            <button type="button" class="custom-select__btn" @click="open=!open">
                                                <span
                                                    x-text="stepper.commission_type==='flat' ? 'Flat' : 'Percentage'"></span>
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </button>
                                            <div x-show="open" @click.outside="open=false" class="custom-select__panel">
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="stepper.commission_type='percentage';onCommissionTypeChange();open=false">
                                                    Percentage</div>
                                                <div class="custom-select__opt"
                                                    @mousedown.prevent="stepper.commission_type='flat';onCommissionTypeChange();open=false">
                                                    Flat</div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-red-600" x-show="errors.commissionType">Select type</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700"
                                            x-text="commissionLabel()"></label>
                                        <input x-model="stepper.commission_value" type="number" min="0" step="any"
                                            placeholder="Enter commission"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                            :class="errors.commissionValue ? 'border-red-500 ring-2 ring-red-300' : ''">
                                        <p class="text-xs mt-1"
                                            :class="errors.commissionValue ? 'text-red-600' : 'text-gray-500'"
                                            x-text="commissionHint()"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden md:block">
                                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                                    <div class="relative">
                                        <img :src="stepperProductImage || pricingProduct?._image || placeholderImg"
                                            class="w-full h-40 object-cover bg-gray-100" alt="">
                                        <div class="absolute top-2 right-2">
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-medium"
                                                :class="chipColor(stepper.price_category || 'retail')">
                                                <i data-lucide="tags" class="w-3 h-3"></i>
                                                <span
                                                    x-text="(stepper.package_size?stepper.package_size:'')+(stepper.si_unit?' '+stepper.si_unit:'') + (stepper.package_name?(' • '+stepper.package_name):'')"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-2">
                                        <h4 class="font-semibold text-gray-900"
                                            x-text="pricingProduct?.name || 'Product'"></h4>
                                        <p class="text-sm text-gray-600" x-text="pricingProduct?.category_name || ''">
                                        </p>
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
                                            <div class="text-sm text-gray-500">Price</div>
                                            <div class="text-lg font-bold"
                                                x-text="'UGX '+formatNumber(stepper.price || 0)"></div>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <div class="text-sm text-gray-500">Commission</div>
                                            <div class="text-sm font-medium"
                                                x-text="stepper.commission_type==='flat' ? ('UGX '+formatNumber(stepper.commission_value||0)) : ((stepper.commission_value||0)+'%')">
                                            </div>
                                        </div>
                                        <div class="flex items-baseline justify-between">
                                            <div class="text-sm text-gray-500">Capacity</div>
                                            <div class="text-sm font-medium" x-text="stepper.delivery_capacity || '-'">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 border-t flex items-center justify-between">
                        <button @click="prevStep()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center gap-2"
                            :disabled="stepper.step===1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            Back
                        </button>
                        <div class="flex items-center gap-2">
                            <button x-show="stepper.step<5" @click="nextStep()"
                                class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black flex items-center gap-2">
                                Next
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                            <button x-show="stepper.step===5" @click="commitStepper()"
                                class="px-4 py-2 rounded-lg bg-user-primary text-white hover:bg-user-primary/90 flex items-center gap-2">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.deleteConfirm" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="modals.deleteConfirm=false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-xl w-full max-w-md modal-panel overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full bg-red-100 grid place-items-center">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-secondary"
                                x-text="deleteContextType==='store' ? 'Confirm Remove From Store' : 'Confirm Delete Draft'">
                            </h3>
                        </div>
                        <p class="text-gray-600 mb-2"
                            x-text="deleteContextType==='store' ? 'Remove this product from your store?' : 'Delete this draft product permanently?'">
                        </p>
                        <p class="text-sm font-medium text-gray-900"
                            x-text="deleteContext?.name || deleteContext?.title"></p>
                    </div>
                    <div class="p-6 border-t flex justify-end gap-2">
                        <button @click="modals.deleteConfirm=false"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Cancel</button>
                        <button @click="performDelete()"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <template x-teleport="body">
        <div x-show="modals.notice" x-transition.opacity class="fixed inset-0 z-[1300] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="modals.notice=false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-3">
                <div class="relative bg-white shadow-xl w-full max-w-md modal-panel overflow-hidden">
                    <div class="p-6 flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 grid place-items-center shrink-0">
                            <i data-lucide="info" class="w-6 h-6 text-yellow-700"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-secondary mb-1">Submitted for Review</h3>
                            <p class="text-gray-700" x-text="noticeMessage"></p>
                        </div>
                    </div>
                    <div class="p-6 border-t flex justify-end">
                        <button @click="modals.notice=false"
                            class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    function productsPage() {
        return {
            vendorId: '<?= $storeId ?>',
            loading: false,
            search: '',
            selectSearch: '',
            selectLoading: false,
            addMenuOpen: false,
            activeTab: 'inStore',
            inStoreFilter: 'all',
            store: { items: [], perRequest: 100, total: 0 },
            my: { items: [], perRequest: 100, total: 0 },
            addableAll: [],
            modals: { selectProduct: false, pricingList: false, stepper: false, deleteConfirm: false, newProduct: false, editMyProduct: false, notice: false },
            noticeMessage: '',
            pricingProduct: null,
            pricingList: [],
            availablePackages: [],
            availableUnits: [],
            allPackages: [],
            selectedPackages: [],
            pkgSearch: '',
            errors: { package: false, unit: false, size: false, category: false, price: false, capacity: false, commissionType: false, commissionValue: false },
            stepper: { mode: 'new', index: null, step: 1, package_mapping_id: null, package_name: '', packageQuery: '', si_unit_id: null, si_unit: '', unitQuery: '', package_size: '', price_category: '', price: '', delivery_capacity: '', commission_type: 'percentage', commission_value: 1 },
            deleteContext: null,
            deleteContextType: 'store',
            placeholderImg: 'https://placehold.co/600x400/f3f4f6/9ca3af?text=No+Image',
            stepperProductImage: null,
            newProduct: { title: '', description: '', previews: [], temp_images: [], uploading: false },
            editProduct: { id: '', title: '', description: '', preview: '', temp_path: '', uploading: false, editable: false },
            lastValidPackageSize: '',
            maxImageBytes: 1048576,
            maxImageLabel: '1 MB',
            async init() {
                this.loading = true;
                await Promise.all([this.fetchAllStoreProducts(), this.fetchAllMyProducts(), this.fetchAllPackages()]);
                this.loading = false;
                this.refreshIcons();
            },
            refreshIcons() {
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            },
            formatCountDisplay(v) {
                if (!v) return '0';
                return new Intl.NumberFormat('en-UG', { notation: 'compact' }).format(v);
            },
            onTabSelectChange() {
                this.refreshIcons();
            },
            switchTab(t) {
                this.activeTab = t;
                this.refreshIcons();
            },
            activeList() {
                let list = this.activeTab === 'inStore' ? this.store.items : this.my.items;
                const q = (this.search || '').toLowerCase();
                if (q) {
                    list = list.filter(p => ((p.name || p.title || '').toLowerCase().includes(q)) || ((p.category_name || '').toLowerCase().includes(q)));
                }
                if (this.activeTab === 'inStore') {
                    if (this.inStoreFilter === 'with') list = list.filter(p => Array.isArray(p.pricing) && p.pricing.length > 0);
                    if (this.inStoreFilter === 'without') list = list.filter(p => !p.pricing || p.pricing.length === 0);
                }
                return list;
            },
            applySearch() {
                this.refreshIcons();
            },
            clearSearch() {
                this.search = '';
                this.refreshIcons();
            },
            countInStoreAll() {
                return this.formatCountDisplay(this.store.items.length);
            },
            countInStoreWithPricing() {
                const c = this.store.items.filter(p => Array.isArray(p.pricing) && p.pricing.length > 0).length;
                return this.formatCountDisplay(c);
            },
            countInStoreWithoutPricing() {
                const c = this.store.items.filter(p => !p.pricing || p.pricing.length === 0).length;
                return this.formatCountDisplay(c);
            },
            chipColor(cat) {
                if (cat === 'retail') return 'bg-blue-100 text-blue-700';
                if (cat === 'wholesale') return 'bg-green-100 text-green-700';
                if (cat === 'factory') return 'bg-orange-100 text-orange-700';
                return 'bg-gray-100 text-gray-700';
            },
            formatNumber(v) {
                if (!v && v !== 0) return '0';
                return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(v);
            },
            formatChip(pr) {
                const pkg = pr.package_name || this.labelForPackage(pr.package_mapping_id) || '';
                const unit = pr.si_unit || this.labelForUnit(pr.si_unit_id) || '';
                const size = pr.package_size ? pr.package_size : '';
                return `${size ? size : ''}${unit ? (' ' + unit) : ''}${pkg ? (' • ' + pkg) : ''} • UGX ${this.formatNumber(pr.price)}`;
            },
            formatCommissionMini(pr) {
                if (!pr) return '';
                const t = (pr.commission_type || 'percentage');
                const v = (typeof pr.commission_value === 'number' ? pr.commission_value : parseFloat(pr.commission_value || 1));
                if (t === 'percentage') return `Comm: ${v}%`;
                return `Comm: UGX ${this.formatNumber(v)}`;
            },
            labelForPackage(id) {
                const m = this.availablePackages.find(x => String(x.id) === String(id));
                return m ? m.package_name : '';
            },
            labelForUnit(id) {
                const u = this.availableUnits.find(x => String(x.id) === String(id));
                return u ? u.si_unit : '';
            },
            async getImage(productId) {
                try {
                    const r = await fetch(`<?= BASE_URL ?>img/products/${productId}/images.json`, { cache: 'no-store' });
                    const j = await r.json();
                    if (j.images && j.images.length) {
                        const rnd = j.images[0];
                        return `<?= BASE_URL ?>img/products/${productId}/${rnd}`;
                    }
                } catch (e) { }
                return this.placeholderImg;
            },
            filterNonDeletedPricing(arr) {
                return (Array.isArray(arr) ? arr : []).filter(pr => String(pr.status || 'active').toLowerCase() !== 'deleted');
            },
            isSuspended(pr) {
                return String(pr.status || '').toLowerCase() === 'suspended';
            },
            rowClass(pr) {
                return this.isSuspended(pr) ? 'bg-red-50 border-red-200' : 'bg-white';
            },
            statusDisplay(pr) {
                const s = String(pr.status || 'active').toLowerCase();
                if (s === 'suspended') return 'Suspended';
                return s === 'active' ? 'Active' : 'Inactive';
            },
            statusTextClass(pr) {
                const s = String(pr.status || 'active').toLowerCase();
                if (s === 'suspended') return 'text-red-700';
                return s === 'active' ? 'text-gray-800' : 'text-gray-500';
            },
            async fetchAllStoreProducts() {
                const all = [];
                let page = 1;
                const limit = this.store.perRequest;
                while (true) {
                    try {
                        const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getStoreProducts&id=${encodeURIComponent(this.vendorId)}&page=${page}&limit=${limit}`, { cache: 'no-store' });
                        const j = await r.json();
                        const arr = (j && j.success && Array.isArray(j.products)) ? j.products : [];
                        const mapped = await Promise.all(arr.filter(p => p.store_product_id).map(async p => {
                            p._image = await this.getImage(p.id);
                            p.pricing = this.filterNonDeletedPricing(p.pricing || []);
                            return p;
                        }));
                        all.push(...mapped);
                        if (arr.length < limit) break;
                        page++;
                    } catch (e) {
                        break;
                    }
                }
                this.store.items = all;
                this.store.total = all.length;
            },
            async fetchAllMyProducts() {
                const all = [];
                let page = 1;
                const limit = this.my.perRequest;
                while (true) {
                    try {
                        const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getMyProducts&page=${page}&limit=${limit}`, { cache: 'no-store' });
                        const j = await r.json();
                        const arr = (j && j.success && Array.isArray(j.products)) ? j.products : [];
                        const mapped = await Promise.all(arr.map(async p => {
                            p._image = p.images && p.images.length ? `<?= BASE_URL ?>${p.images[0]}` : await this.getImage(p.id);
                            p.name = p.title || p.name;
                            p.pricing = this.filterNonDeletedPricing(p.pricing || []);
                            return p;
                        }));
                        all.push(...mapped);
                        if (arr.length < limit) break;
                        page++;
                    } catch (e) {
                        break;
                    }
                }
                this.my.items = all;
                this.my.total = all.length;
            },
            toggleAddMenu() {
                this.addMenuOpen = !this.addMenuOpen;
            },
            openAddExisting() {
                this.addMenuOpen = false;
                this.openAddProduct();
            },
            openCreateNew() {
                this.addMenuOpen = false;
                this.showNewProductModal();
            },
            openCreateNewFromSelect() {
                this.modals.selectProduct = false;
                this.showNewProductModal();
            },
            showAlert(type, message) {
                const c = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
                const icon = type === 'success' ? 'check-circle' : 'alert-circle';
                const el = document.getElementById('alertContainer');
                el.innerHTML = `<div class="${c} pointer-events-auto border px-4 py-3 rounded-lg shadow flex items-center gap-2"><i data-lucide="${icon}" class="w-4 h-4"></i><span>${message}</span></div>`;
                this.refreshIcons();
                setTimeout(() => { el.innerHTML = ''; }, 4000);
            },
            async openAddProduct() {
                this.modals.selectProduct = true;
                this.selectSearch = '';
                this.selectLoading = true;
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getProductsNotInStore&store_id=${encodeURIComponent(this.vendorId)}`, { cache: 'no-store' });
                    const j = await r.json();
                    this.addableAll = j.success ? (j.products || []) : [];
                } catch (e) {
                    this.addableAll = [];
                } finally {
                    this.selectLoading = false;
                    this.$nextTick(() => this.refreshIcons());
                }
            },
            selectableProducts() {
                const q = (this.selectSearch || '').toLowerCase();
                return this.addableAll.filter(p => (p.name || '').toLowerCase().includes(q) || (p.category_name || '').toLowerCase().includes(q));
            },
            async beginAddPricing(p) {
                this.modals.selectProduct = false;
                this.pricingProduct = { id: p.id, name: p.name, category_id: p.category_id, category_name: p.category_name, store_product_id: null, pricing: [], _image: await this.getImage(p.id) };
                await this.loadMetaForProduct(p.id);
                this.pricingList = [];
                this.modals.pricingList = true;
                this.$nextTick(() => this.refreshIcons());
            },
            async openPricingForMy(p) {
                const catName = p.category_name || p.category || p.category_title || p.categoryName || '';
                const statusVal = p.status || p.product_status || '';
                const base = { id: p.id, name: p.title || p.name, category_id: p.category_id || p.categoryId || null, category_name: catName, store_product_id: p.store_product_id || null, pricing: this.filterNonDeletedPricing(p.pricing || []), _image: p._image || await this.getImage(p.id), status: statusVal };
                this.pricingProduct = base;
                await this.loadMetaForProduct(p.id);
                this.pricingList = JSON.parse(JSON.stringify(base.pricing || []));
                this.modals.pricingList = true;
                this.$nextTick(() => this.refreshIcons());
            },
            async openPricingList(p) {
                this.pricingProduct = p;
                if (!this.pricingProduct._image) this.pricingProduct._image = await this.getImage(p.id);
                await this.loadMetaForProduct(p.id);
                this.pricingList = JSON.parse(JSON.stringify(this.filterNonDeletedPricing(p.pricing || [])));
                this.modals.pricingList = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closePricingList() {
                this.modals.pricingList = false;
            },
            async loadMetaForProduct(productId) {
                try {
                    const [pkgR, siR] = await Promise.all([
                        fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getPackageNamesForProduct&product_id=${encodeURIComponent(productId)}`),
                        fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getSIUnits`)
                    ]);
                    const [pkgJ, siJ] = await Promise.all([pkgR.json(), siR.json()]);
                    this.availablePackages = pkgJ.success ? (pkgJ.mappings || []) : [];
                    this.availableUnits = siJ.success ? (siJ.siUnits || []) : [];
                } catch (e) {
                    this.availablePackages = [];
                    this.availableUnits = [];
                }
            },
            async fetchAllPackages() {
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getPackageNames`, { cache: 'no-store' });
                    const j = await r.json();
                    this.allPackages = j.success ? (j.packageNames || []) : [];
                } catch (e) {
                    this.allPackages = [];
                }
            },
            filteredPackages() {
                const q = (this.pkgSearch || '').toLowerCase();
                return this.allPackages.filter(p => p.package_name.toLowerCase().includes(q));
            },
            isSelectedPackage(id) {
                return this.selectedPackages.some(x => String(x.id) === String(id));
            },
            toggleSelectPackage(m) {
                if (this.isSelectedPackage(m.id)) {
                    this.selectedPackages = this.selectedPackages.filter(x => String(x.id) !== String(m.id));
                } else {
                    this.selectedPackages.push({ id: m.id, package_name: m.package_name });
                }
                this.$nextTick(() => this.refreshIcons());
            },
            removeSelectedPackage(id) {
                this.selectedPackages = this.selectedPackages.filter(x => String(x.id) !== String(id));
            },
            async createNewPackageName() {
                const name = (this.pkgSearch || '').trim();
                if (!name) {
                    this.showAlert('error', 'Enter a package name');
                    return;
                }
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=createPackageName`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ package_name: name }) });
                    const j = await r.json();
                    if (j.success && j.id) {
                        const obj = { id: j.id, package_name: name };
                        this.allPackages.push(obj);
                        if (!this.isSelectedPackage(j.id)) this.selectedPackages.push(obj);
                        this.pkgSearch = '';
                        this.showAlert('success', 'Package added');
                        this.$nextTick(() => this.refreshIcons());
                    } else {
                        this.showAlert('error', j.message || 'Failed to add');
                    }
                } catch (e) {
                    this.showAlert('error', 'Server error');
                }
            },
            showNewProductModal() {
                this.newProduct = { title: '', description: '', previews: [], temp_images: [], uploading: false };
                this.selectedPackages = [];
                this.pkgSearch = '';
                this.modals.newProduct = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closeNewProduct() {
                this.modals.newProduct = false;
            },
            openEditMyProduct(p) {
                this.editProduct = { id: p.id, title: p.title || p.name || '', description: p.description || '', preview: p._image || this.placeholderImg, temp_path: '', uploading: false, editable: (String(p.status || '').toLowerCase() === 'draft') };
                this.modals.editMyProduct = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closeEditMyProduct() {
                this.modals.editMyProduct = false;
            },
            async uploadTemp(file) {
                const fd = new FormData();
                fd.append('image', file, file.name || 'upload.jpg');
                const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=uploadImage`, { method: 'POST', body: fd });
                const j = await r.json();
                if (!(j.success && j.temp_path)) throw new Error(j.message || 'Upload failed');
                return j.temp_path;
            },
            async handleNewImages(e) {
                const files = Array.from(e.target.files || []);
                e.target.value = '';
                if (!files.length) return;
                const valid = [];
                for (const f of files) {
                    if (f.size > this.maxImageBytes) {
                        this.showAlert('error', `Image ${f.name} is bigger than the max size ${this.maxImageLabel}`);
                    } else {
                        valid.push(f);
                    }
                }
                if (!valid.length) return;
                this.newProduct.uploading = true;
                try {
                    for (const f of valid) {
                        const tempPath = await this.uploadTemp(f);
                        const url = URL.createObjectURL(f);
                        this.newProduct.temp_images.push(tempPath);
                        this.newProduct.previews.push(url);
                    }
                    this.showAlert('success', 'Images ready');
                } catch (err) {
                    this.showAlert('error', 'Upload error');
                } finally {
                    this.newProduct.uploading = false;
                    this.$nextTick(() => this.refreshIcons());
                }
            },
            removeNewImage(idx) {
                this.newProduct.previews.splice(idx, 1);
                this.newProduct.temp_images.splice(idx, 1);
            },
            async handleEditImage(e) {
                const file = e.target.files && e.target.files[0];
                e.target.value = '';
                if (!file) return;
                if (!this.editProduct.editable) return;
                if (file.size > this.maxImageBytes) {
                    this.showAlert('error', `Image ${file.name} is bigger than the max size ${this.maxImageLabel}`);
                    return;
                }
                this.editProduct.uploading = true;
                try {
                    const tempPath = await this.uploadTemp(file);
                    this.editProduct.temp_path = tempPath;
                    this.editProduct.preview = URL.createObjectURL(file);
                    this.showAlert('success', 'Image ready');
                } catch (err) {
                    this.showAlert('error', 'Upload error');
                } finally {
                    this.editProduct.uploading = false;
                }
            },
            async submitNewProduct() {
                const title = (this.newProduct.title || '').trim();
                if (!title) {
                    this.showAlert('error', 'Title is required');
                    return;
                }
                if (!this.newProduct.temp_images.length) {
                    this.showAlert('error', 'Please upload at least one image');
                    return;
                }
                const payload = {
                    title: title,
                    description: this.newProduct.description || '',
                    package_names: this.selectedPackages.map(p => p.id),
                    temp_images: this.newProduct.temp_images
                };
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=createProductMinimal`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const j = await r.json();
                    if (j.success && j.id) {
                        this.modals.newProduct = false;
                        await this.fetchAllMyProducts();
                        this.activeTab = 'myProducts';
                        this.noticeMessage = 'Product has been submitted for review, please come back shortly to continue with pricing.';
                        this.modals.notice = true;
                        this.$nextTick(() => this.refreshIcons());
                    } else {
                        this.showAlert('error', j.message || 'Failed to create');
                    }
                } catch (e) {
                    this.showAlert('error', 'Server error');
                }
            },
            async submitEditMyProduct() {
                const id = this.editProduct.id;
                if (!id) return;
                const payload = { id, title: (this.editProduct.title || '').trim(), description: this.editProduct.description || '', temp_image: this.editProduct.temp_path || '' };
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=updateMyProduct`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const j = await r.json();
                    if (j.success) {
                        this.showAlert('success', 'Product updated');
                        this.modals.editMyProduct = false;
                        await this.fetchAllMyProducts();
                        this.activeTab = 'myProducts';
                    } else {
                        this.showAlert('error', j.message || 'Failed to update');
                    }
                } catch (e) {
                    this.showAlert('error', 'Server error');
                }
            },
            async openStepper(mode, idx = null) {
                this.errors = { package: false, unit: false, size: false, category: false, price: false, capacity: false, commissionType: false, commissionValue: false };
                this.stepper = { mode, index: idx, step: 1, package_mapping_id: null, package_name: '', packageQuery: '', si_unit_id: null, si_unit: '', unitQuery: '', package_size: '', price_category: '', price: '', delivery_capacity: '', commission_type: 'percentage', commission_value: 1 };
                if (!this.pricingProduct?._image) this.pricingProduct._image = await this.getImage(this.pricingProduct.id);
                this.stepperProductImage = this.pricingProduct._image;
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
                this.lastValidPackageSize = this.stepper.package_size || '';
                this.modals.stepper = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closeStepper() {
                this.modals.stepper = false;
            },
            selectPackage(m) {
                this.stepper.package_mapping_id = m.id;
                this.stepper.package_name = m.package_name;
                this.stepper.packageQuery = m.package_name;
                this.errors.package = false;
            },
            selectUnit(u) {
                this.stepper.si_unit_id = u.id;
                this.stepper.si_unit = u.si_unit;
                this.stepper.unitQuery = u.si_unit;
                this.errors.unit = false;
            },
            nextStep() {
                if (this.stepper.step === 1) {
                    this.errors.package = !this.stepper.package_mapping_id;
                    if (this.errors.package) return;
                } else if (this.stepper.step === 2) {
                    this.errors.unit = !this.stepper.si_unit_id;
                    this.errors.size = this.stepper.package_size === '' || Number(this.stepper.package_size) <= 0;
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
                this.$nextTick(() => this.refreshIcons());
            },
            prevStep() {
                if (this.stepper.step > 1) this.stepper.step--;
                this.$nextTick(() => this.refreshIcons());
            },
            commissionLabel() {
                return this.stepper.commission_type === 'flat' ? 'Commission (UGX)' : 'Commission (%)';
            },
            commissionHint() {
                if (this.stepper.commission_type === 'percentage') return 'Allowed: 1% to 5%';
                const p = Number(this.stepper.price || 0);
                const min = Math.max(0, Math.round(p * 0.01 * 100) / 100);
                const max = Math.max(0, Math.round(p * 0.05 * 100) / 100);
                return p > 0 ? `Allowed: UGX ${this.formatNumber(min)} to UGX ${this.formatNumber(max)}` : 'Enter a price to compute allowed range';
            },
            onCommissionTypeChange() {
                if (this.stepper.commission_type === 'percentage') {
                    if (this.stepper.commission_value === '' || this.stepper.commission_value == null) this.stepper.commission_value = 1;
                } else {
                    const p = Number(this.stepper.price || 0);
                    const min = Math.round(p * 0.01 * 100) / 100;
                    if (p > 0 && (this.stepper.commission_value === '' || this.stepper.commission_value == null)) this.stepper.commission_value = min;
                }
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
                const entry = { package_mapping_id: this.stepper.package_mapping_id, package_name: this.stepper.package_name, si_unit_id: this.stepper.si_unit_id, si_unit: this.stepper.si_unit, package_size: this.stepper.package_size, price_category: this.stepper.price_category, price: this.stepper.price, delivery_capacity: this.stepper.delivery_capacity || null, commission_type: ct, commission_value: cv, status: 'active' };
                if (this.stepper.mode === 'edit' && this.stepper.index !== null) {
                    const existing = this.pricingList[this.stepper.index] || {};
                    const existingId = existing && existing.pricing_id ? existing.pricing_id : null;
                    entry.pricing_id = existingId;
                    entry.created_by = existing.created_by || null;
                    entry.created_by_name = existing.created_by_name || null;
                    entry.created_at = existing.created_at || null;
                    entry.status = existing.status || 'active';
                    this.pricingList.splice(this.stepper.index, 1, entry);
                } else {
                    entry.pricing_id = null;
                    this.pricingList.push(entry);
                }
                this.modals.stepper = false;
                this.$nextTick(() => this.refreshIcons());
            },
            currentPricingCreatorText() {
                if (this.stepper.index == null) return '';
                const pr = this.pricingList[this.stepper.index] || {};
                const name = pr.created_by_name || pr.created_by || '';
                if (!name) return '';
                return 'Created by: ' + name;
            },
            togglePricingStatus(idx, checked) {
                const pr = this.pricingList[idx];
                if (!pr) return;
                if (this.isSuspended(pr)) {
                    this.showAlert('error', 'This pricing is suspended and cannot be toggled');
                    return;
                }
                pr.status = checked ? 'active' : 'inactive';
                this.$nextTick(() => this.refreshIcons());
            },
            isDraftProduct() {
                return ((this.pricingProduct?.status || '').toLowerCase() === 'draft');
            },
            canAddPricing() {
                return !this.isDraftProduct();
            },
            computeOutgoingStatus(pr) {
                const s = String(pr.status || 'active').toLowerCase();
                if (s === 'suspended') return 'suspended';
                return s === 'inactive' ? 'inactive' : 'active';
            },
            async savePricingChanges() {
                if (this.pricingList.length === 0) {
                    this.showAlert('error', 'Add at least one pricing entry');
                    return;
                }
                const payload = new FormData();
                let url = '';
                let created = false;
                if (this.pricingProduct.store_product_id) {
                    payload.append('store_product_id', this.pricingProduct.store_product_id);
                    payload.append('line_items', JSON.stringify(this.normalizeLineItems()));
                    url = `<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=updateStoreProduct`;
                } else {
                    payload.append('store_id', this.vendorId);
                    payload.append('product_id', this.pricingProduct.id);
                    payload.append('line_items', JSON.stringify(this.normalizeLineItems()));
                    url = `<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=addStoreProduct`;
                    created = true;
                }
                try {
                    const r = await fetch(url, { method: 'POST', body: payload });
                    const j = await r.json();
                    if (j.success) {
                        this.showAlert('success', created ? 'Added to store. Product has been submitted for approval.' : 'Updated successfully');
                        this.modals.pricingList = false;
                        await this.fetchAllStoreProducts();
                        this.activeTab = 'inStore';
                    } else {
                        this.showAlert('error', j.error || 'Operation failed');
                    }
                } catch (e) {
                    this.showAlert('error', 'Server error');
                }
            },
            normalizeLineItems() {
                return this.pricingList
                    .filter(pr => String(pr.status || 'active').toLowerCase() !== 'deleted')
                    .map(pr => ({
                        pricing_id: pr.pricing_id || null,
                        package_mapping_id: pr.package_mapping_id,
                        si_unit_id: pr.si_unit_id,
                        package_size: pr.package_size,
                        price_category: pr.price_category,
                        price: pr.price,
                        delivery_capacity: pr.delivery_capacity,
                        commission_type: pr.commission_type || 'percentage',
                        commission_value: pr.commission_value == null || pr.commission_value === '' ? 1 : pr.commission_value,
                        status: this.computeOutgoingStatus(pr)
                    }));
            },
            confirmDeleteFromStore(p) {
                this.deleteContext = p;
                this.deleteContextType = 'store';
                this.modals.deleteConfirm = true;
                this.$nextTick(() => this.refreshIcons());
            },
            confirmDeleteMyDraft(p) {
                this.deleteContext = p;
                this.deleteContextType = 'draft';
                this.modals.deleteConfirm = true;
                this.$nextTick(() => this.refreshIcons());
            },
            async performDelete() {
                if (this.deleteContextType === 'store') {
                    if (!this.deleteContext?.store_product_id) {
                        this.modals.deleteConfirm = false;
                        return;
                    }
                    try {
                        const fd = new FormData();
                        fd.append('id', this.deleteContext.store_product_id);
                        const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=deleteProduct`, { method: 'POST', body: fd });
                        const j = await r.json();
                        if (j.success) {
                            this.showAlert('success', 'Product removed');
                            this.modals.deleteConfirm = false;
                            await this.fetchAllStoreProducts();
                        } else {
                            this.showAlert('error', j.error || 'Failed to delete');
                        }
                    } catch (e) {
                        this.showAlert('error', 'Server error');
                    }
                } else {
                    if (!this.deleteContext?.id) {
                        this.modals.deleteConfirm = false;
                        return;
                    }
                    try {
                        const fd = new FormData();
                        fd.append('id', this.deleteContext.id);
                        const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=deleteMyProduct`, { method: 'POST', body: fd });
                        const j = await r.json();
                        if (j.success) {
                            this.showAlert('success', 'Draft deleted');
                            this.modals.deleteConfirm = false;
                            await this.fetchAllMyProducts();
                            this.activeTab = 'myProducts';
                        } else {
                            this.showAlert('error', 'Failed to delete draft');
                        }
                    } catch (e) {
                        this.showAlert('error', 'Server error');
                    }
                }
            },
            isValidFractionInput(v) {
                return /^[0-9./]*$/.test(v);
            },
            handlePackageSizeKeydown(e) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End', 'Enter'];
                if (allowed.includes(e.key)) return;
                if (e.key.length === 1) {
                    if (!/[0-9./]/.test(e.key)) {
                        e.preventDefault();
                        this.showAlert('error', 'Only numbers, / and . are allowed');
                    }
                }
            },
            onPackageSizeInput(e) {
                const v = e.target.value;
                if (this.isValidFractionInput(v)) {
                    this.stepper.package_size = v;
                    this.lastValidPackageSize = v;
                    this.errors.size = (v.trim() === '');
                } else {
                    this.showAlert('error', 'Only numbers, / and . are allowed');
                    e.target.value = this.lastValidPackageSize || '';
                    this.stepper.package_size = e.target.value;
                    this.errors.size = this.stepper.package_size.trim() === '';
                }
            },
            handlePackageSizePaste(e) {
                const text = (e.clipboardData || window.clipboardData).getData('text');
                if (!this.isValidFractionInput(text)) {
                    e.preventDefault();
                    this.showAlert('error', 'Only numbers, / and . are allowed');
                }
            },
            formatCreatedAt(v) {
                if (!v) return '';
                const s = String(v).replace(' ', 'T');
                const d = new Date(s);
                if (isNaN(d.getTime())) return '';
                const day = String(d.getDate()).padStart(2, '0');
                const mon = d.toLocaleString('en-US', { month: 'short' });
                const year = d.getFullYear();
                const time = d.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                return `${day}/${mon}/${year} ${time}`;
            }
        }
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>