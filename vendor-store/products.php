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
<div x-data="productsPage()" x-init="init()" class="space-y-6">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <form @submit.prevent="applySearch()" class="flex flex-col sm:flex-row gap-3 mb-6">
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
                <button type="button" @click="openAddProduct()"
                    class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Add Product
                </button>
            </div>
        </form>
        <div x-show="loading" class="text-center py-12">
            <i data-lucide="loader-2" class="w-10 h-10 text-gray-400 mx-auto mb-4 animate-spin"></i>
            <p class="text-gray-600">Loading products...</p>
        </div>
        <div x-show="!loading && filteredProducts().length===0" class="text-center py-12">
            <i data-lucide="package-open" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No products found</h3>
            <p class="text-gray-500">Try adjusting your search or add a product.</p>
        </div>
        <div x-show="!loading && filteredProducts().length>0" id="productsGrid"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="p in pagedProducts()" :key="p.store_product_id ?? p.id">
                <div
                    class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 flex flex-col h-full">
                    <div class="relative">
                        <img :src="p._image || placeholderImg" @error="$event.target.src=placeholderImg" :alt="p.name"
                            class="w-full h-44 object-cover rounded-t-xl bg-gray-100">
                        <button @click="openPricingList(p)"
                            class="absolute top-2 right-2 bg-white p-2 rounded-full shadow-md hover:bg-gray-50 transition text-gray-700">
                            <i data-lucide="pen-line" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="p-4 flex flex-col gap-3 flex-1">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 leading-tight" x-text="p.name"></h3>
                            <p class="text-sm text-gray-600" x-text="p.category_name"></p>
                        </div>
                        <div class="mt-auto">
                            <div class="flex flex-wrap gap-1.5 mb-3">
                                <template x-if="!p.pricing || p.pricing.length===0">
                                    <span class="text-xs text-gray-500">No pricing</span>
                                </template>
                                <template x-for="pr in (p.pricing||[])"
                                    :key="pr.id || pr.package_mapping_id + '-' + pr.price_category">
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
                                <button @click="confirmDelete(p)"
                                    class="p-2 rounded-full text-red-600 hover:bg-red-50 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div x-show="!loading && totalPages>1"
            class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-sm text-gray-700">
                <span
                    x-text="'Showing '+(offset()+1)+' to '+Math.min(offset()+limit, filteredProducts().length)+' of '+filteredProducts().length"></span>
            </div>
            <div class="flex items-center gap-1">
                <button @click="goto(page-1)" x-bind:disabled="page===1"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:pointer-events-none">Previous</button>
                <template x-for="n in pageNumbers()" :key="n">
                    <button @click="goto(n)" class="px-3 py-2 text-sm border rounded-lg transition"
                        :class="n===page ? 'bg-user-primary text-white border-user-primary' : 'border-gray-300 hover:bg-gray-50'">
                        <span x-text="n"></span>
                    </button>
                </template>
                <button @click="goto(page+1)" x-bind:disabled="page===totalPages"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:pointer-events-none">Next</button>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div id="alertContainer" class="fixed top-4 right-4 z-[1100] space-y-2 pointer-events-none"></div>
    </template>

    <template x-teleport="body">
        <div x-show="modals.selectProduct" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="modals.selectProduct=false"></div>
            <div class="fixed inset-0 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <h3 class="text-lg font-semibold text-secondary">Select Product</h3>
                        <button @click="modals.selectProduct=false" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="relative">
                            <i data-lucide="search"
                                class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                            <input x-model="selectSearch" type="text" placeholder="Search products to add"
                                class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary focus:border-transparent">
                        </div>
                        <div class="max-h-[55vh] overflow-y-auto divide-y">
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
        <div x-show="modals.pricingList" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/40" @click="closePricingList()"></div>
            <div class="fixed inset-0 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <div class="flex items-center gap-3">
                            <i data-lucide="tags" class="w-5 h-5 text-user-primary"></i>
                            <h3 class="text-lg font-semibold text-secondary"
                                x-text="pricingProduct?.name ? 'Manage Pricing — '+pricingProduct.name : 'Manage Pricing'">
                            </h3>
                        </div>
                        <button @click="closePricingList()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600" x-text="pricingProduct?.category_name"></div>
                            <button @click="openStepper('new')"
                                class="px-3 py-2 bg-gray-900 text-white rounded-lg hover:bg-black transition flex items-center gap-2">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Add Pricing
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="sm:w-[750px] md:w-[900px] lg:w-auto border rounded-lg overflow-hidden">
                                <div
                                    class="grid grid-cols-12 gap-2 px-4 py-2 bg-gray-50 text-xs font-semibold text-gray-600">
                                    <div class="col-span-3">Package</div>
                                    <div class="col-span-3">Unit & Size</div>
                                    <div class="col-span-2">Category</div>
                                    <div class="col-span-2">Price</div>
                                    <div class="col-span-1 text-center">Capacity</div>
                                    <div class="col-span-1 text-right">Edit</div>
                                </div>
                                <template x-if="pricingList.length===0">
                                    <div class="px-4 py-6 text-center text-gray-500">No pricing entries</div>
                                </template>
                                <template x-for="(pr,idx) in pricingList" :key="idx">
                                    <div class="grid grid-cols-12 gap-2 px-4 py-3 border-t items-center bg-white">
                                        <div class="col-span-3">
                                            <div class="text-sm font-medium"
                                                x-text="pr.package_name || labelForPackage(pr.package_mapping_id)">
                                            </div>
                                        </div>
                                        <div class="col-span-3">
                                            <div class="text-sm"
                                                x-text="(pr.package_size||'-')+' '+(pr.si_unit||labelForUnit(pr.si_unit_id)||'')">
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                                :class="chipColor(pr.price_category)"
                                                x-text="(pr.price_category||'').toUpperCase()"></span>
                                        </div>
                                        <div class="col-span-2">
                                            <div class="text-sm font-semibold" x-text="'UGX '+formatNumber(pr.price)">
                                            </div>
                                        </div>
                                        <div class="col-span-1 text-center">
                                            <div class="text-xs text-gray-600"
                                                x-text="pr.delivery_capacity ? pr.delivery_capacity : '—'"></div>
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
                        <div class="flex justify-end gap-2">
                            <button @click="closePricingList()"
                                class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">Close</button>
                            <button @click="savePricingChanges()"
                                class="px-4 py-2 rounded-lg bg-user-primary text-white hover:bg-user-primary/90"
                                x-text="pricingProduct?.store_product_id ? 'Save Changes' : 'Add To Store'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="modals.stepper" x-transition.opacity class="fixed inset-0 z-[1000] m-0 p-0">
            <div class="fixed inset-0 bg-black/50" @click="closeStepper()"></div>
            <div class="fixed inset-0 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden">
                    <div class="flex items-center justify-between p-5 border-b">
                        <h3 class="text-lg font-semibold text-secondary"
                            x-text="stepper.mode==='new' ? 'Add Pricing' : 'Edit Pricing'"></h3>
                        <button @click="closeStepper()" class="p-2 rounded hover:bg-gray-50">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="px-5 pt-5">
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-3">
                            <template x-for="n in 4" :key="'s'+n">
                                <div class="flex-1 flex items-center">
                                    <div class="w-8 h-8 rounded-full grid place-items-center font-semibold"
                                        :class="n<=stepper.step ? 'bg-user-primary text-white' : 'bg-gray-100 text-gray-500'">
                                        <span x-text="n"></span>
                                    </div>
                                    <div class="h-[2px] flex-1"
                                        :class="n<4 ? (n<stepper.step?'bg-user-primary':'bg-gray-200') : ''"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="p-5 space-y-4">
                        <div x-show="stepper.step===1" class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Package</label>
                            <div class="relative">
                                <input x-model="stepper.packageQuery" @focus="openPkg=true" @input="openPkg=true"
                                    @keydown.escape.stop="openPkg=false" type="text" placeholder="Search package"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                    :class="errors.package ? 'border-red-500 ring-2 ring-red-300' : ''">
                                <div x-show="openPkg" @click.outside="openPkg=false"
                                    class="absolute z-10 mt-1 left-0 right-0 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow"
                                    tabindex="-1">
                                    <template x-if="availablePackages.length===0">
                                        <div class="p-3 text-center text-gray-500">No packages</div>
                                    </template>
                                    <template
                                        x-for="m in availablePackages.filter(x=>x.package_name.toLowerCase().includes((stepper.packageQuery||'').toLowerCase()))"
                                        :key="m.id">
                                        <div @mousedown.prevent="selectPackage(m)"
                                            class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm">
                                            <span x-text="m.package_name"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500"
                                x-text="stepper.package_mapping_id ? 'Selected: '+stepper.package_name : ''"></p>
                        </div>
                        <div x-show="stepper.step===2" class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Unit of Measure</label>
                                <div class="relative">
                                    <input x-model="stepper.unitQuery" @focus="openUnit=true" @input="openUnit=true"
                                        @keydown.escape.stop="openUnit=false" type="text" placeholder="Search unit"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                        :class="errors.unit ? 'border-red-500 ring-2 ring-red-300' : ''">
                                    <div x-show="openUnit" @click.outside="openUnit=false"
                                        class="absolute z-10 mt-1 left-0 right-0 max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow"
                                        tabindex="-1">
                                        <template
                                            x-for="u in availableUnits.filter(x=>x.si_unit.toLowerCase().includes((stepper.unitQuery||'').toLowerCase()))"
                                            :key="u.id">
                                            <div @mousedown.prevent="selectUnit(u)"
                                                class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm">
                                                <span x-text="u.si_unit"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500"
                                    x-text="stepper.si_unit_id ? 'Selected: '+stepper.si_unit : ''"></p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Unit Size</label>
                                <input x-model="stepper.package_size" type="number" min="0" step="any"
                                    placeholder="Enter size"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                    :class="errors.size ? 'border-red-500 ring-2 ring-red-300' : ''" required>
                            </div>
                        </div>
                        <div x-show="stepper.step===3" class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-700">Price Category</label>
                                <select x-model="stepper.price_category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-user-primary"
                                    :class="errors.category ? 'border-red-500 ring-2 ring-red-300' : ''">
                                    <option value="">Select category</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="factory">Factory</option>
                                </select>
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
                    </div>
                    <div class="p-5 border-t flex items-center justify-between">
                        <button @click="prevStep()"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 flex items-center gap-2"
                            :disabled="stepper.step===1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            Back
                        </button>
                        <div class="flex items-center gap-2">
                            <button x-show="stepper.step<4" @click="nextStep()"
                                class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black flex items-center gap-2">
                                Next
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                            <button x-show="stepper.step===4" @click="commitStepper()"
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
            <div class="fixed inset-0 flex items-center justify-center">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 rounded-full bg-red-100 grid place-items-center">
                                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-secondary">Confirm Delete</h3>
                        </div>
                        <p class="text-gray-600 mb-2">Remove this product from your store?</p>
                        <p class="text-sm font-medium text-gray-900" x-text="deleteContext?.name"></p>
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
</div>

<script>
    function productsPage() {
        return {
            vendorId: '<?= $storeId ?>',
            page: 1,
            limit: 12,
            products: [],
            loading: false,
            search: '',
            selectSearch: '',
            selectLoading: false,
            addableAll: [],
            modals: { selectProduct: false, pricingList: false, stepper: false, deleteConfirm: false },
            pricingProduct: null,
            pricingList: [],
            availablePackages: [],
            availableUnits: [],
            openPkg: false,
            openUnit: false,
            errors: { package: false, unit: false, size: false, category: false, price: false, capacity: false },
            stepper: { mode: 'new', step: 1, package_mapping_id: null, package_name: '', packageQuery: '', si_unit_id: null, si_unit: '', unitQuery: '', package_size: '', price_category: '', price: '', delivery_capacity: '' },
            deleteContext: null,
            placeholderImg: 'https://placehold.co/600x400/f3f4f6/9ca3af?text=No+Image',
            async init() { await this.fetchProducts(); this.refreshIcons(); },
            refreshIcons() { if (window.lucide && lucide.createIcons) lucide.createIcons(); },
            offset() { return (this.page - 1) * this.limit },
            filteredProducts() {
                const q = (this.search || '').toLowerCase();
                return this.products.filter(p => (p.name || '').toLowerCase().includes(q) || (p.category_name || '').toLowerCase().includes(q));
            },
            totalPages() { return Math.max(1, Math.ceil(this.filteredProducts().length / this.limit)); },
            pageNumbers() {
                const total = this.totalPages(); const cur = this.page; const out = [];
                const start = Math.max(1, cur - 2); const end = Math.min(total, cur + 2);
                for (let i = start; i <= end; i++) out.push(i);
                return out;
            },
            pagedProducts() { return this.filteredProducts().slice(this.offset(), this.offset() + this.limit); },
            goto(n) { if (n < 1 || n > this.totalPages()) return; this.page = n; this.$nextTick(() => this.refreshIcons()); },
            applySearch() { this.page = 1; this.$nextTick(() => this.refreshIcons()); },
            clearSearch() { this.search = ''; this.page = 1; this.$nextTick(() => this.refreshIcons()); },
            chipColor(cat) {
                if (cat === 'retail') return 'bg-blue-100 text-blue-700';
                if (cat === 'wholesale') return 'bg-green-100 text-green-700';
                if (cat === 'factory') return 'bg-orange-100 text-orange-700';
                return 'bg-gray-100 text-gray-700';
            },
            formatNumber(v) { if (!v && v !== 0) return '0'; return new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(v); },
            formatChip(pr) {
                const pkg = pr.package_name || this.labelForPackage(pr.package_mapping_id) || '';
                const unit = pr.si_unit || this.labelForUnit(pr.si_unit_id) || '';
                const size = pr.package_size ? pr.package_size : '';
                return `${pkg}${size ? (' • ' + size) : ''}${unit ? (' ' + unit) : ''} • UGX ${this.formatNumber(pr.price)}`;
            },
            labelForPackage(id) { const m = this.availablePackages.find(x => String(x.id) === String(id)); return m ? m.package_name : ''; },
            labelForUnit(id) { const u = this.availableUnits.find(x => String(x.id) === String(id)); return u ? u.si_unit : ''; },
            async getImage(productId) {
                try {
                    const r = await fetch(`<?= BASE_URL ?>img/products/${productId}/images.json`, { cache: 'no-store' });
                    const j = await r.json();
                    if (j.images && j.images.length) { const rnd = j.images[Math.floor(Math.random() * j.images.length)]; return `<?= BASE_URL ?>img/products/${productId}/${rnd}`; }
                } catch (e) { }
                return this.placeholderImg;
            },
            async fetchProducts() {
                this.loading = true;
                try {
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=getStoreProducts&id=${encodeURIComponent(this.vendorId)}&page=1&limit=500`, { cache: 'no-store' });
                    const j = await r.json();
                    if (j.success && j.products) {
                        this.products = await Promise.all(j.products.map(async p => { p._image = await this.getImage(p.id); return p; }));
                    } else {
                        this.products = [];
                    }
                } catch (e) {
                    this.products = [];
                } finally {
                    this.loading = false;
                    this.$nextTick(() => this.refreshIcons());
                }
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
                } catch (e) { this.addableAll = []; }
                finally {
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
                this.pricingProduct = { id: p.id, name: p.name, category_id: p.category_id, category_name: p.category_name, store_product_id: null, pricing: [] };
                await this.loadMetaForProduct(p.id);
                this.pricingList = [];
                this.modals.pricingList = true;
                this.$nextTick(() => this.refreshIcons());
            },
            async openPricingList(p) {
                this.pricingProduct = p;
                await this.loadMetaForProduct(p.id);
                this.pricingList = JSON.parse(JSON.stringify(p.pricing || []));
                this.modals.pricingList = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closePricingList() { this.modals.pricingList = false; },
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
            openStepper(mode, idx = null) {
                this.errors = { package: false, unit: false, size: false, category: false, price: false, capacity: false };
                this.stepper = { mode, index: idx, step: 1, package_mapping_id: null, package_name: '', packageQuery: '', si_unit_id: null, si_unit: '', unitQuery: '', package_size: '', price_category: '', price: '', delivery_capacity: '' };
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
                    this.stepper.packageQuery = this.stepper.package_name;
                    this.stepper.unitQuery = this.stepper.si_unit;
                }
                this.openPkg = true;
                this.openUnit = false;
                this.modals.stepper = true;
                this.$nextTick(() => this.refreshIcons());
            },
            closeStepper() { this.modals.stepper = false; },
            selectPackage(m) {
                this.stepper.package_mapping_id = m.id;
                this.stepper.package_name = m.package_name;
                this.stepper.packageQuery = m.package_name;
                this.openPkg = false;
                this.errors.package = false;
            },
            selectUnit(u) {
                this.stepper.si_unit_id = u.id;
                this.stepper.si_unit = u.si_unit;
                this.stepper.unitQuery = u.si_unit;
                this.openUnit = false;
                this.errors.unit = false;
            },
            nextStep() {
                if (this.stepper.step === 1) {
                    this.errors.package = !this.stepper.package_mapping_id;
                    if (this.errors.package) return;
                    this.openUnit = true;
                } else if (this.stepper.step === 2) {
                    this.errors.unit = !this.stepper.si_unit_id;
                    this.errors.size = this.stepper.package_size === '' || Number(this.stepper.package_size) <= 0;
                    if (this.errors.unit || this.errors.size) return;
                } else if (this.stepper.step === 3) {
                    this.errors.category = !this.stepper.price_category;
                    this.errors.price = this.stepper.price === '' || Number(this.stepper.price) < 0;
                    if (this.errors.category || this.errors.price) return;
                }
                if (this.stepper.step < 4) this.stepper.step++;
                this.$nextTick(() => this.refreshIcons());
            },
            prevStep() { if (this.stepper.step > 1) this.stepper.step--; this.$nextTick(() => this.refreshIcons()); },
            commitStepper() {
                this.errors.capacity = this.stepper.delivery_capacity === '' || Number(this.stepper.delivery_capacity) < 0;
                if (this.errors.capacity) return;
                const entry = {
                    package_mapping_id: this.stepper.package_mapping_id,
                    package_name: this.stepper.package_name,
                    si_unit_id: this.stepper.si_unit_id,
                    si_unit: this.stepper.si_unit,
                    package_size: this.stepper.package_size,
                    price_category: this.stepper.price_category,
                    price: this.stepper.price,
                    delivery_capacity: this.stepper.delivery_capacity || null
                };
                if (this.stepper.mode === 'edit' && this.stepper.index !== null) {
                    this.pricingList.splice(this.stepper.index, 1, entry);
                } else {
                    this.pricingList.push(entry);
                }
                this.modals.stepper = false;
                this.$nextTick(() => this.refreshIcons());
            },
            async savePricingChanges() {
                if (this.pricingList.length === 0) { this.showAlert('error', 'Add at least one pricing entry'); return; }
                const payload = new FormData();
                let url = '';
                if (this.pricingProduct.store_product_id) {
                    payload.append('store_product_id', this.pricingProduct.store_product_id);
                    payload.append('line_items', JSON.stringify(this.normalizeLineItems()));
                    url = `<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=updateStoreProduct`;
                } else {
                    payload.append('store_id', this.vendorId);
                    payload.append('product_id', this.pricingProduct.id);
                    payload.append('line_items', JSON.stringify(this.normalizeLineItems()));
                    url = `<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=addStoreProduct`;
                }
                try {
                    const r = await fetch(url, { method: 'POST', body: payload });
                    const j = await r.json();
                    if (j.success) {
                        this.showAlert('success', this.pricingProduct.store_product_id ? 'Updated successfully' : 'Added to store');
                        this.modals.pricingList = false;
                        await this.fetchProducts();
                    } else {
                        this.showAlert('error', j.error || 'Operation failed');
                    }
                } catch (e) { this.showAlert('error', 'Server error'); }
            },
            normalizeLineItems() {
                return this.pricingList.map(pr => ({
                    package_mapping_id: pr.package_mapping_id,
                    si_unit_id: pr.si_unit_id,
                    package_size: pr.package_size,
                    price_category: pr.price_category,
                    price: pr.price,
                    delivery_capacity: pr.delivery_capacity
                }));
            },
            confirmDelete(p) { this.deleteContext = p; this.modals.deleteConfirm = true; this.$nextTick(() => this.refreshIcons()); },
            async performDelete() {
                if (!this.deleteContext?.store_product_id) { this.modals.deleteConfirm = false; return; }
                try {
                    const fd = new FormData(); fd.append('id', this.deleteContext.store_product_id);
                    const r = await fetch(`<?= BASE_URL ?>vendor-store/fetch/manageProducts.php?action=deleteProduct`, { method: 'POST', body: fd });
                    const j = await r.json();
                    if (j.success) {
                        this.showAlert('success', 'Product removed');
                        this.modals.deleteConfirm = false;
                        await this.fetchProducts();
                    } else {
                        this.showAlert('error', j.error || 'Failed to delete');
                    }
                } catch (e) { this.showAlert('error', 'Server error'); }
            }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>