<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL);
    exit;
}
$pageTitle = 'Products Pricing';
$activeNav = 'products-pricing';
ob_start();
?>
<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Products Pricing</h1>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">View products with pricing and
                        which vendors sell them</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="products"
                        class="px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-box"></i>
                        <span class="hidden sm:inline">Products</span>
                    </a>
                    <a href="product-categories"
                        class="px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-tags"></i>
                        <span class="hidden sm:inline">Categories</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Priced Products</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="statPricedProducts">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-dollar-sign text-blue-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Vendors Selling</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="statVendors">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-green-600"></i>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Pricing Options</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="statPricingOptions">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-list text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Filter & Search</h2>
                    <p class="text-sm text-gray-600">Find priced products and drill into vendor pricing</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" id="searchProducts" placeholder="Search product or vendor..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="filterCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Category</label>
                    <select id="filterPriceCategory"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                        <option value="factory">Factory</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortProducts"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">Select</option>
                        <option value="last_update">Last Pricing Update</option>
                        <option value="vendors">Vendor Count</option>
                        <option value="min_price">Lowest Price</option>
                        <option value="max_price">Highest Price</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="filterStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All</option>
                        <option value="published">Published</option>
                        <option value="pending">Pending</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Priced Products</h3>
                        <p class="text-sm text-gray-600"><span id="productCount">0</span> products found</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button id="resetFilters"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Reset
                            Filters</button>
                        <button id="applyFilters"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Apply
                            Filters</button>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="productsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Product</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Category</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Vendors</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Price Range</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Updated</th>
                        </tr>
                    </thead>
                    <tbody id="productsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading priced products...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span
                        id="totalPricedProductsFooter">0</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Previous</button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>

            <div class="lg:hidden" id="productsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading priced products...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingStart">0</span> to <span id="mobileShowingEnd">0</span> of <span
                        id="mobileTotalPricedProducts">0</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Previous</button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pricingModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hidePricingModal()"></div>
    <div class="relative z-10 min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-5xl bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
            <div
                class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
                <div class="flex items-center gap-3">
                    <div
                        class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                        <i class="fas fa-tags text-gray-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Product Pricing</h3>
                        <p class="text-sm text-gray-600 mt-1">Vendors and pricing options</p>
                    </div>
                </div>
                <button onclick="hidePricingModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-4 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="col-span-2">
                        <h4 class="text-base font-semibold text-secondary" id="modalProductName"></h4>
                        <div class="text-sm text-gray-600" id="modalCategoryName"></div>
                    </div>
                    <div class="flex items-center gap-2 md:justify-end">
                        <select id="modalFilterPriceCategory"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                            <option value="">All price categories</option>
                            <option value="retail">Retail</option>
                            <option value="wholesale">Wholesale</option>
                            <option value="factory">Factory</option>
                        </select>
                        <input id="modalSearchStore" type="text" placeholder="Search store..."
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                    </div>
                </div>
                <div id="vendorPricingContainer" class="space-y-4"></div>
            </div>
        </div>
    </div>
</div>

<div id="vendorEditModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeVendorModal()"></div>
    <div class="relative z-10 min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-secondary" id="vendorModalTitle">Edit Vendor
                        Pricing</h3>
                    <p class="text-xs text-gray-600 mt-1 truncate" id="vendorModalSub"></p>
                </div>
                <button onclick="closeVendorModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 p-4 sm:p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-secondary truncate" id="vendorStoreName"></div>
                        <div class="text-xs text-gray-500 truncate" id="vendorProductName"></div>
                    </div>
                </div>
                <div id="vendorPricingList" class="grid grid-cols-1 md:grid-cols-2 gap-3"></div>
                <div class="mt-6 flex items-center justify-end gap-2">
                    <button onclick="reloadVendorPricing()"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reset</button>
                    <button onclick="saveVendorChanges()"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pricingStepperModal" class="fixed inset-0 z-[70] hidden">
    <div class="absolute inset-0 bg-black/60" onclick="closeStepper()"></div>
    <div class="relative z-10 min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100">
                <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-secondary" id="stepperTitle">Edit Pricing</h3>
                    <p class="text-xs text-gray-500 truncate" id="stepperSub"></p>
                </div>
                <button onclick="closeStepper()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4 sm:p-5">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-5">
                        <div class="space-y-2" data-step="1">
                            <div class="text-sm font-medium text-gray-700">Package</div>
                            <select id="stPackage" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></select>
                            <div id="errPackage" class="text-xs text-red-600 hidden">Select a package</div>
                        </div>
                        <div class="space-y-4 hidden" data-step="2">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Unit of Measure</div>
                                <select id="stUnit" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></select>
                                <div id="errUnit" class="text-xs text-red-600 hidden">Select a unit</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Unit Size</div>
                                <input id="stSize" type="text" placeholder="e.g. 1/2, 1 1/2, 2.5"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <div id="errSize" class="text-xs text-red-600 hidden">Enter a valid size</div>
                            </div>
                        </div>
                        <div class="space-y-4 hidden" data-step="3">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Price Category</div>
                                <select id="stPriceCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Select</option>
                                    <option value="retail">Retail</option>
                                    <option value="wholesale">Wholesale</option>
                                    <option value="factory">Factory</option>
                                </select>
                                <div id="errCategory" class="text-xs text-red-600 hidden">Select a category</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-700">Price (UGX)</div>
                                <input id="stPrice" type="number" min="0" step="any"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <div id="errPrice" class="text-xs text-red-600 hidden">Enter a valid price</div>
                            </div>
                        </div>
                        <div class="space-y-2 hidden" data-step="4">
                            <div class="text-sm font-medium text-gray-700" id="stCapacityLabel">Capacity</div>
                            <input id="stCapacity" type="number" min="0" step="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <div id="errCapacity" class="text-xs text-red-600 hidden">Enter capacity</div>
                        </div>
                        <div class="space-y-4 hidden" data-step="5">
                            <div>
                                <div class="text-sm font-medium text-gray-700">Commission Type</div>
                                <select id="stCommissionType"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="percentage">Percentage</option>
                                    <option value="flat">Flat</option>
                                </select>
                                <div id="errCommType" class="text-xs text-red-600 hidden">Select type</div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-gray-700" id="stCommissionLabel">Commission (%)
                                    </div>
                                    <div class="text-xs text-gray-500" id="stCommissionHint">Allowed: 1% to 5%</div>
                                </div>
                                <input id="stCommissionValue" type="number" min="0" step="any"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <div id="errCommValue" class="text-xs text-red-600 hidden">Enter a valid commission
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="border rounded-xl overflow-hidden">
                            <div class="p-4 border-b">
                                <div class="text-sm text-gray-500">Preview</div>
                                <div class="mt-1 text-base font-semibold text-secondary" id="pvProductName"></div>
                            </div>
                            <div class="p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">Package</div>
                                    <div class="text-sm font-medium" id="pvPkg">-</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">Category</div>
                                    <div class="text-sm font-medium" id="pvCat">-</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">Price</div>
                                    <div class="text-sm font-semibold" id="pvPrice">UGX 0</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">Commission</div>
                                    <div class="text-sm font-medium" id="pvComm">1%</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">Capacity</div>
                                    <div class="text-sm font-medium" id="pvCap">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-5 border-t flex items-center justify-between">
                <button id="btnPrevStep"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Back</button>
                <div class="flex items-center gap-2">
                    <button id="btnNextStep"
                        class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-black">Next</button>
                    <button id="btnSaveStep"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 hidden">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[999] hidden">
    <div class="bg-white p-5 rounded-lg shadow-lg flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        <span id="loadingMessage" class="text-gray-700 font-medium">Loading...</span>
    </div>
</div>

<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>

<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<style>
    .vendor-card {
        border: 1px solid rgba(229, 231, 235, 1);
        border-radius: 0.75rem;
    }

    .vendor-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(243, 244, 246, 1);
        background: rgba(249, 250, 251, 1);
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .vendor-body {
        padding: 0.75rem 1rem;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-green {
        background: rgba(220, 252, 231, 1);
        color: rgba(21, 128, 61, 1);
    }

    .badge-yellow {
        background: rgba(254, 249, 195, 1);
        color: rgba(133, 77, 14, 1);
    }

    .badge-purple {
        background: rgba(237, 233, 254, 1);
        color: rgba(91, 33, 182, 1);
    }

    .badge-gray {
        background: rgba(229, 231, 235, 1);
        color: rgba(55, 65, 81, 1);
    }

    .badge-orange {
        background: rgba(255, 237, 213, 1);
        color: rgba(154, 52, 18, 1);
    }

    .badge-red {
        background: rgba(254, 226, 226, 1);
        color: rgba(185, 28, 28, 1);
    }
</style>

<script>
    let pricedProducts = [];
    let categoriesList = [];
    let currentPage = 1;
    let itemsPerPage = 20;
    let totalPages = 1;
    let filterData = { search: '', category: '', priceCategory: '', status: '', sort: '' };
    let modalData = { product: null, rows: [], filtered: [], product_id: null };
    let vendorState = {
        product_id: null,
        product_name: '',
        store_id: null,
        store_name: '',
        region: '',
        district: '',
        items: [],
        original: [],
        packages: [],
        units: [],
        stepper: { mode: 'edit', index: null, step: 1, package_mapping_id: '', package_name: '', si_unit_id: '', si_unit: '', package_size: '', price_category: '', price: '', delivery_capacity: '', commission_type: 'percentage', commission_value: 1, status: 'active' }
    };

    document.addEventListener('DOMContentLoaded', function () {
        loadCategories();
        loadPricedProducts();

        document.getElementById('searchProducts').addEventListener('input', function (e) {
            filterData.search = e.target.value;
            applyFilters();
        });
        document.getElementById('filterCategory').addEventListener('change', function (e) {
            filterData.category = e.target.value;
        });
        document.getElementById('filterPriceCategory').addEventListener('change', function (e) {
            filterData.priceCategory = e.target.value;
        });
        document.getElementById('filterStatus').addEventListener('change', function (e) {
            filterData.status = e.target.value;
        });
        document.getElementById('sortProducts').addEventListener('change', function (e) {
            filterData.sort = e.target.value;
        });
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('resetFilters').addEventListener('click', resetFilters);

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderProducts(pricedProducts);
            }
        });
        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderProducts(pricedProducts);
            }
        });
        document.getElementById('mobilePrevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderProducts(pricedProducts);
            }
        });
        document.getElementById('mobileNextPage').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderProducts(pricedProducts);
            }
        });

        document.getElementById('modalFilterPriceCategory').addEventListener('change', function () {
            filterModalRows();
        });
        document.getElementById('modalSearchStore').addEventListener('input', function () {
            filterModalRows();
        });

        document.addEventListener('click', function (e) {
            const openPricing = e.target.closest('[data-open-pricing]');
            if (openPricing) {
                const pid = openPricing.getAttribute('data-product-id');
                if (pid) {
                    openPricingModal(pid);
                }
                return;
            }
            const openVendorBtn = e.target.closest('[data-open-vendor-editor]');
            if (openVendorBtn) {
                openVendorEditor(
                    openVendorBtn.getAttribute('data-product-id') || '',
                    openVendorBtn.getAttribute('data-store-id') || '',
                    openVendorBtn.getAttribute('data-store-name') || '',
                    openVendorBtn.getAttribute('data-region') || '',
                    openVendorBtn.getAttribute('data-district') || ''
                );
                return;
            }
            const editPricingBtn = e.target.closest('[data-edit-pricing-index]');
            if (editPricingBtn) {
                const idx = Number(editPricingBtn.getAttribute('data-edit-pricing-index'));
                openStepper('edit', idx);
                return;
            }
        });

        document.getElementById('vendorPricingList').addEventListener('change', function (e) {
            const sel = e.target.closest('[data-status-index]');
            if (sel) {
                const idx = Number(sel.getAttribute('data-status-index'));
                const val = sel.value;
                if (vendorState.items[idx]) {
                    vendorState.items[idx].status = val;
                }
            }
        });

        document.getElementById('btnPrevStep').addEventListener('click', function () {
            prevStep();
        });
        document.getElementById('btnNextStep').addEventListener('click', function () {
            nextStep();
        });
        document.getElementById('btnSaveStep').addEventListener('click', function () {
            commitStepper();
        });
        document.getElementById('stPriceCategory').addEventListener('change', function () {
            onCapacityLabel();
            updatePreview();
        });
        document.getElementById('stCommissionType').addEventListener('change', function () {
            onCommissionTypeChange();
            updatePreview();
        });
        ['stPackage', 'stUnit', 'stSize', 'stPrice', 'stCapacity', 'stCommissionValue', 'stPriceCategory'].forEach(function (id) {
            var el = document.getElementById(id);
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        });
    });

    function loadCategories() {
        showLoading('Loading categories...');
        fetch(`${BASE_URL}admin/fetch/manageProductCategories.php?action=getCategories`)
            .then(function (res) {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('unauth');
                }
                return res.json();
            })
            .then(function (data) {
                hideLoading();
                if (data.success) {
                    categoriesList = data.categories || [];
                    var catFilter = document.getElementById('filterCategory');
                    catFilter.innerHTML = '<option value="">All Categories</option>';
                    categoriesList.forEach(function (cat) {
                        var opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        catFilter.appendChild(opt);
                    });
                }
            })
            .catch(function () {
                hideLoading();
            });
    }

    function loadPricedProducts() {
        showLoading('Loading priced products...');
        fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getPricedProducts`)
            .then(function (res) {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('unauth');
                }
                return res.json();
            })
            .then(function (data) {
                hideLoading();
                if (data.success) {
                    pricedProducts = (data.products || []).map(function (p) {
                        return normalizeRow(p);
                    });
                    updateStats(pricedProducts);
                    currentPage = 1;
                    renderProducts(pricedProducts);
                    renderPagination();
                } else {
                    showErrorNotification(data.message || 'Failed to load priced products');
                }
            })
            .catch(function () {
                hideLoading();
                showErrorNotification('Failed to load priced products');
            });
    }

    function normalizeRow(r) {
        var o = Object.assign({}, r);
        o.min_price = parseFloat(r.min_price || 0);
        o.max_price = parseFloat(r.max_price || 0);
        o.stores_count = parseInt(r.stores_count || 0, 10);
        o.pricing_count = parseInt(r.pricing_count || 0, 10);
        o.last_pricing_update = r.last_pricing_update || r.updated_at || r.created_at || null;
        o.category = r.category_id || r.category || null;
        return o;
    }

    function updateStats(rows) {
        var total = rows.length;
        var vendorSum = rows.reduce(function (a, b) {
            return a + (b.stores_count || 0);
        }, 0);
        var pricingSum = rows.reduce(function (a, b) {
            return a + (b.pricing_count || 0);
        }, 0);
        document.getElementById('statPricedProducts').textContent = total.toLocaleString();
        document.getElementById('statVendors').textContent = vendorSum.toLocaleString();
        document.getElementById('statPricingOptions').textContent = pricingSum.toLocaleString();
    }

    function applyFilters() {
        currentPage = 1;
        renderProducts(pricedProducts);
        renderPagination();
    }

    function resetFilters() {
        filterData = { search: '', category: '', priceCategory: '', status: '', sort: '' };
        document.getElementById('searchProducts').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterPriceCategory').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('sortProducts').value = '';
        applyFilters();
    }

    function filterRows(rows) {
        var out = rows.slice();

        if (filterData.search) {
            var q = filterData.search.toLowerCase();
            out = out.filter(function (r) {
                var t = (r.title || '').toLowerCase();
                var cat = (r.category_name || '').toLowerCase();
                var vendors = (r.vendor_names_join || '').toLowerCase();
                return t.includes(q) || cat.includes(q) || vendors.includes(q);
            });
        }

        if (filterData.category) {
            out = out.filter(function (r) {
                return (r.category || '') == filterData.category;
            });
        }

        if (filterData.status) {
            out = out.filter(function (r) {
                return (r.status || '') === filterData.status;
            });
        }

        if (filterData.priceCategory) {
            out = out.filter(function (r) {
                var list = r.price_categories || [];
                return list.includes(filterData.priceCategory);
            });
        }

        switch (filterData.sort) {
            case 'last_update':
                out.sort(function (a, b) {
                    return new Date(b.last_pricing_update || 0) - new Date(a.last_pricing_update || 0);
                });
                break;
            case 'vendors':
                out.sort(function (a, b) {
                    return (b.stores_count || 0) - (a.stores_count || 0);
                });
                break;
            case 'min_price':
                out.sort(function (a, b) {
                    return (a.min_price || 0) - (b.min_price || 0);
                });
                break;
            case 'max_price':
                out.sort(function (a, b) {
                    return (b.max_price || 0) - (a.max_price || 0);
                });
                break;
        }

        return out;
    }

    function renderProducts(rows) {
        var filtered = filterRows(rows);
        var tbody = document.getElementById('productsBody');
        var cards = document.getElementById('productsCards');
        var total = filtered.length;

        totalPages = Math.ceil(total / itemsPerPage);
        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }
        var start = (currentPage - 1) * itemsPerPage;
        var end = Math.min(start + itemsPerPage, total);

        document.getElementById('productCount').textContent = total;
        document.getElementById('showingStart').textContent = total ? start + 1 : 0;
        document.getElementById('showingEnd').textContent = end;
        document.getElementById('totalPricedProductsFooter').textContent = total;
        document.getElementById('mobileShowingStart').textContent = total ? start + 1 : 0;
        document.getElementById('mobileShowingEnd').textContent = end;
        document.getElementById('mobileTotalPricedProducts').textContent = total;
        document.getElementById('mobilePageInfo').textContent = "Page " + Math.max(1, currentPage) + " of " + Math.max(1, totalPages);
        document.getElementById('mobilePrevPage').disabled = currentPage === 1;
        document.getElementById('mobileNextPage').disabled = currentPage === totalPages || totalPages === 0;

        if (end - start <= 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No priced products found</div></td></tr>';
            cards.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No priced products found</div></div>';
            renderPagination();
            return;
        }

        var pageRows = filtered.slice(start, end);

        tbody.innerHTML = pageRows.map(function (r) {
            var img = r.main_image || 'https://placehold.co/48x48/e2e8f0/1e293b?text=P';
            var range = priceRangeBlock(r.min_price, r.max_price);
            var updated = r.last_pricing_update ? formatDateTimeBlock(r.last_pricing_update) : '';
            return '<tr class="hover:bg-gray-50 transition-colors cursor-pointer" data-open-pricing data-product-id="' + escapeHtml(String(r.id)) + '">' +
                '<td class="px-4 py-3 whitespace-nowrap"><div class="flex items-center"><div class="flex-shrink-0 h-10 w-10 rounded-lg overflow-hidden bg-gray-100"><img src="' + img + '" alt="' + escapeHtml(r.title) + '" class="w-full h-full object-cover"></div><div class="ml-4"><div class="text-sm font-medium text-secondary max-w-xs hover:text-primary"><span class="hidden sm:block truncate">' + escapeHtml(r.title) + '</span><span class="sm:hidden break-words">' + escapeHtml(r.title) + '</span></div><div class="text-xs text-gray-500 hidden sm:block">ID: ' + escapeHtml(String(r.id)) + '</div></div></div></td>' +
                '<td class="px-4 py-3 text-center"><span class="text-sm text-gray-900">' + escapeHtml(r.category_name || '(Uncategorized)') + '</span></td>' +
                '<td class="px-4 py-3 text-center"><span class="text-sm text-gray-900">' + (r.stores_count || 0) + '</span></td>' +
                '<td class="px-4 py-3 text-center"><div class="text-sm text-gray-900 leading-tight">' + range + '</div></td>' +
                '<td class="px-4 py-3 text-center"><div class="text-xs text-gray-600 leading-tight">' + updated + '</div></td>' +
                '</tr>';
        }).join('');

        cards.innerHTML = pageRows.map(function (r) {
            var img = r.main_image || 'https://placehold.co/64x64/e2e8f0/1e293b?text=P';
            var range = priceRangeBlock(r.min_price, r.max_price);
            var updated = r.last_pricing_update ? formatDateTimeBlock(r.last_pricing_update) : '';
            return '<div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer" data-open-pricing data-product-id="' + escapeHtml(String(r.id)) + '">' +
                '<div class="flex items-start gap-3">' +
                '<div class="flex-shrink-0 h-12 w-12 rounded-lg overflow-hidden bg-gray-100"><img src="' + img + '" alt="' + escapeHtml(r.title) + '" class="w-full h-full object-cover"></div>' +
                '<div class="flex-1 min-w-0">' +
                '<div class="mb-1"><div class="flex items-center gap-1 mb-1"><span class="badge badge-green">' + (r.stores_count || 0) + '</span></div>' +
                '<h4 class="text-sm font-medium text-secondary hover:text-primary pr-2 break-words">' + escapeHtml(r.title) + '</h4></div>' +
                '<div class="text-xs text-gray-500 mb-1">' + escapeHtml(r.category_name || '(Uncategorized)') + '</div>' +
                '<div class="text-sm text-gray-900 leading-tight">' + range + '</div>' +
                '<div class="text-xs text-gray-500 mt-1 leading-tight">' + updated + '</div>' +
                '</div></div></div>';
        }).join('');

        renderPagination();
    }

    function renderPagination() {
        var prevBtn = document.getElementById('prev-page');
        var nextBtn = document.getElementById('next-page');
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        var pagNums = document.getElementById('pagination-numbers');
        pagNums.innerHTML = '';

        if (totalPages <= 5) {
            for (var i = 1; i <= totalPages; i++) {
                pagNums.appendChild(createPagButton(i));
            }
        } else {
            pagNums.appendChild(createPagButton(1));
            if (currentPage > 3) {
                var e1 = document.createElement('span');
                e1.textContent = '...';
                e1.classList.add('px-2');
                pagNums.appendChild(e1);
            }
            for (var j = Math.max(2, currentPage - 1); j <= Math.min(totalPages - 1, currentPage + 1); j++) {
                pagNums.appendChild(createPagButton(j));
            }
            if (currentPage < totalPages - 2) {
                var e2 = document.createElement('span');
                e2.textContent = '...';
                e2.classList.add('px-2');
                pagNums.appendChild(e2);
            }
            if (totalPages > 1) {
                pagNums.appendChild(createPagButton(totalPages));
            }
        }
    }

    function createPagButton(page) {
        var btn = document.createElement('button');
        btn.className = (page === currentPage) ? 'px-3 py-2 rounded-lg bg-primary text-white' : 'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        btn.textContent = page;
        btn.addEventListener('click', function () {
            currentPage = page;
            renderPagination();
            renderProducts(pricedProducts);
        });
        return btn;
    }

    function priceRangeBlock(minP, maxP) {
        if (minP == null || typeof minP === 'undefined' || maxP == null || typeof maxP === 'undefined') {
            return '';
        }
        return '<div>Min - ' + formatMoneyClean(minP) + '</div><div>Max - ' + formatMoneyClean(maxP) + '</div>';
    }

    function formatMoneyClean(n) {
        var v = Number(n);
        if (!isFinite(v)) {
            return '';
        }
        if (Math.abs(v - Math.round(v)) < 1e-9) {
            return Math.round(v).toLocaleString();
        }
        var s = v.toFixed(2).replace(/\.?0+$/, '');
        var parts = s.split('.');
        parts[0] = Number(parts[0]).toLocaleString();
        return parts.length > 1 ? parts[0] + '.' + parts[1] : parts[0];
    }

    function capFirst(s) {
        return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
    }

    function statusBadgeClass(st) {
        switch ((st || '').toLowerCase()) {
            case 'active':
                return 'badge-green';
            case 'inactive':
                return 'badge-gray';
            case 'suspended':
                return 'badge-orange';
            case 'deleted':
                return 'badge-red';
            default:
                return 'badge-gray';
        }
    }

    function statusLabel(st) {
        return capFirst(st || 'inactive');
    }

    function formatDateTimeBlock(s) {
        if (!s) {
            return '';
        }
        var d = new Date(s);
        if (isNaN(d.getTime())) {
            return '';
        }
        var dd = String(d.getDate()).padStart(2, '0');
        var mmmArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var mmm = mmmArr[d.getMonth()];
        var yyyy = d.getFullYear();
        var h = d.getHours();
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) {
            h = 12;
        }
        var mm = String(d.getMinutes()).padStart(2, '0');
        var datePart = dd + '/' + mmm + '/' + yyyy;
        var timePart = h + ':' + mm + ' ' + ampm;
        return '<div>' + datePart + '</div><div>' + timePart + '</div>';
    }

    function openPricingModal(productId) {
        showLoading('Loading pricing details...');
        fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getProductPricing&product_id=${encodeURIComponent(productId)}`)
            .then(function (res) {
                if (res.status === 401) {
                    showSessionExpiredModal();
                    throw new Error('unauth');
                }
                return res.json();
            })
            .then(function (data) {
                hideLoading();
                if (!data.success) {
                    showErrorNotification(data.message || 'Failed to load pricing');
                    return;
                }
                modalData.product = data.product || {};
                modalData.product_id = productId;
                modalData.rows = data.rows || [];
                document.getElementById('modalTitle').textContent = 'Product Pricing';
                document.getElementById('modalProductName').textContent = modalData.product.title || '';
                document.getElementById('modalCategoryName').textContent = modalData.product.category_name || '(Uncategorized)';
                document.getElementById('modalFilterPriceCategory').value = '';
                document.getElementById('modalSearchStore').value = '';
                filterModalRows();
                document.getElementById('pricingModal').classList.remove('hidden');
            })
            .catch(function () {
                hideLoading();
                showErrorNotification('Failed to load pricing details');
            });
    }

    function hidePricingModal() {
        document.getElementById('pricingModal').classList.add('hidden');
    }

    function filterModalRows() {
        var pc = document.getElementById('modalFilterPriceCategory').value;
        var q = document.getElementById('modalSearchStore').value.toLowerCase();
        var filtered = modalData.rows.filter(function (r) {
            var okPC = pc ? r.price_category === pc : true;
            var okStore = q ? (r.store_name || '').toLowerCase().includes(q) : true;
            return okPC && okStore;
        });
        renderVendorPricing(filtered);
    }

    function formatPackageSize(val) {
        var t = String(val || '').trim();
        var mixed = /^(\d+)\s+(\d+)\/(\d+)$/;
        var frac = /^(\d+)\/(\d+)$/;
        if (mixed.test(t)) {
            var m1 = t.match(mixed);
            return '<span class="whitespace-nowrap">' + m1[1] + ' <span class="align-text-top text-[11px]">' + m1[2] + '</span>/<span class="align-text-bottom text-[11px]">' + m1[3] + '</span></span>';
        } else if (frac.test(t)) {
            var m2 = t.match(frac);
            return '<span class="whitespace-nowrap"><span class="align-text-top text-[11px]">' + m2[1] + '</span>/<span class="align-text-bottom text-[11px]">' + m2[2] + '</span></span>';
        }
        return escapeHtml(t);
    }

    function renderVendorPricing(rows) {
        var container = document.getElementById('vendorPricingContainer');

        if (!rows.length) {
            container.innerHTML = '<div class="p-4 text-center text-gray-500">No pricing matched the filters</div>';
            return;
        }

        var grouped = {};
        rows.forEach(function (r) {
            var sid = r.store_id;
            if (!grouped[sid]) {
                grouped[sid] = { store_id: sid, store_name: r.store_name, region: r.region, district: r.district, items: [] };
            }
            grouped[sid].items.push(r);
        });

        var blocks = Object.values(grouped).map(function (g) {
            var itemsHtml = g.items.map(function (it) {
                var commission = it.commission_type === 'percentage' ? formatMoneyClean(it.commission_value) + '%' : formatMoneyClean(it.commission_value);
                var pkgHTML = [escapeHtml(it.package_name || ''), formatPackageSize(it.package_size), escapeHtml(it.si_unit || '')].filter(Boolean).join(' ');
                var stClass = statusBadgeClass(it.status);
                var stLabel = statusLabel(it.status);
                return '<tr class="border-b last:border-b-0 align-top">' +
                    '<td class="px-3 py-2"><span class="' + (it.price_category === 'retail' ? 'badge badge-green' : it.price_category === 'wholesale' ? 'badge badge-yellow' : 'badge badge-purple') + '">' + capFirst(it.price_category) + '</span></td>' +
                    '<td class="px-3 py-2">' + formatMoneyClean(it.price) + '</td>' +
                    '<td class="px-3 py-2">' + pkgHTML + '</td>' +
                    '<td class="px-3 py-2">' + commission + '</td>' +
                    '<td class="px-3 py-2">' + (it.delivery_capacity !== null ? it.delivery_capacity : '') + '</td>' +
                    '<td class="px-3 py-2"><span class="badge ' + stClass + '">' + stLabel + '</span></td>' +
                    '</tr>';
            }).join('');
            return '' +
                '<div class="vendor-card">' +
                '<div class="vendor-header">' +
                '<div class="text-sm font-semibold text-secondary">' + escapeHtml(g.store_name || 'Store') + '</div>' +
                '<div class="flex items-center gap-2">' +
                '<div class="text-xs text-gray-500 hidden md:block">' + escapeHtml([g.region, g.district].filter(Boolean).join(', ')) + '</div>' +
                '<button class="px-3 py-1.5 text-xs bg-gray-900 text-white rounded hover:bg-black" ' +
                'data-open-vendor-editor ' +
                'data-product-id="' + escapeHtml(String(modalData.product_id || '')) + '" ' +
                'data-store-id="' + escapeHtml(String(g.store_id || '')) + '" ' +
                'data-store-name="' + escapeHtml(String(g.store_name || '')) + '" ' +
                'data-region="' + escapeHtml(String(g.region || '')) + '" ' +
                'data-district="' + escapeHtml(String(g.district || '')) + '"' +
                '>' +
                '<i class="fas fa-pen mr-2"></i>Edit' +
                '</button>' +
                '</div>' +
                '</div>' +
                '<div class="vendor-body overflow-x-auto">' +
                '<table class="w-full text-sm">' +
                '<thead class="bg-gray-50"><tr>' +
                '<th class="text-left px-3 py-2">Category</th>' +
                '<th class="text-left px-3 py-2">Price</th>' +
                '<th class="text-left px-3 py-2">Package</th>' +
                '<th class="text-left px-3 py-2">Commission</th>' +
                '<th class="text-left px-3 py-2">Capacity</th>' +
                '<th class="text-left px-3 py-2">Status</th>' +
                '</tr></thead>' +
                '<tbody>' + itemsHtml + '</tbody>' +
                '</table>' +
                '</div>' +
                '</div>';
        }).join('');

        container.innerHTML = blocks;
    }

    function openVendorEditor(product_id, store_id, store_name, region, district) {
        vendorState.product_id = product_id;
        vendorState.product_name = modalData.product.title || '';
        vendorState.store_id = store_id;
        vendorState.store_name = store_name || '';
        vendorState.region = region || '';
        vendorState.district = district || '';
        document.getElementById('vendorModalTitle').textContent = 'Edit Vendor Pricing';
        document.getElementById('vendorModalSub').textContent = vendorState.region || vendorState.district ? [vendorState.region, vendorState.district].filter(Boolean).join(', ') : '';
        document.getElementById('vendorStoreName').textContent = vendorState.store_name;
        document.getElementById('vendorProductName').textContent = vendorState.product_name;
        showLoading('Loading vendor pricing...');
        Promise.all([
            fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getVendorProductPricing&product_id=${encodeURIComponent(product_id)}&store_id=${encodeURIComponent(store_id)}`).then(function (r) { return r.json(); }),
            fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getPackageNamesForProduct&product_id=${encodeURIComponent(product_id)}`).then(function (r) { return r.json(); }),
            fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=getSIUnits`).then(function (r) { return r.json(); })
        ])
            .then(function (arr) {
                hideLoading();
                var a = arr[0];
                var b = arr[1];
                var c = arr[2];
                vendorState.items = (a.success ? a.items : []).map(function (x) {
                    return normalizeVendorItem(x);
                });
                vendorState.original = JSON.parse(JSON.stringify(vendorState.items));
                vendorState.packages = b.success ? (b.mappings || []) : [];
                vendorState.units = c.success ? (c.siUnits || []) : [];
                renderVendorItems();
                fillSelectOptions(document.getElementById('stPackage'), vendorState.packages.map(function (p) {
                    return { value: String(p.id), label: p.package_name };
                }));
                fillSelectOptions(document.getElementById('stUnit'), vendorState.units.map(function (u) {
                    return { value: String(u.id), label: u.si_unit };
                }));
                document.getElementById('pricingStepperModal').classList.add('hidden');
                document.getElementById('vendorEditModal').classList.remove('hidden');
            })
            .catch(function () {
                hideLoading();
                showErrorNotification('Failed to load vendor pricing');
            });
    }

    function normalizeVendorItem(pr) {
        return {
            pricing_id: pr.pricing_id || pr.id || null,
            package_mapping_id: pr.package_mapping_id || null,
            package_name: pr.package_name || '',
            si_unit_id: pr.si_unit_id || null,
            si_unit: pr.si_unit || '',
            package_size: pr.package_size != null ? String(pr.package_size) : '',
            price_category: pr.price_category || '',
            price: pr.price != null ? Number(pr.price) : '',
            delivery_capacity: pr.delivery_capacity != null ? Number(pr.delivery_capacity) : '',
            commission_type: pr.commission_type || 'percentage',
            commission_value: pr.commission_value != null ? Number(pr.commission_value) : 1,
            status: (pr.status || 'active')
        };
    }

    function renderVendorItems() {
        var list = document.getElementById('vendorPricingList');
        if (vendorState.items.length === 0) {
            list.innerHTML = '<div class="p-6 text-center text-gray-500 border rounded-lg col-span-1 md:col-span-2">No pricing entries for this vendor</div>';
            return;
        }
        list.innerHTML = vendorState.items.map(function (pr, idx) {
            var categoryBadge = pr.price_category === 'retail' ? 'badge-green' : pr.price_category === 'wholesale' ? 'badge-yellow' : 'badge-purple';
            var pkgName = pr.package_name || labelForPackage(pr.package_mapping_id);
            var unitName = pr.si_unit || labelForUnit(pr.si_unit_id);
            var commission = pr.commission_type === 'flat' ? 'UGX ' + formatMoneyClean(pr.commission_value) : formatMoneyClean(pr.commission_value) + '%';
            var stClass = statusBadgeClass(pr.status);
            var stLabel = statusLabel(pr.status);
            return '' +
                '<div class="border rounded-lg p-4 bg-white">' +
                '<div class="flex items-start justify-between gap-3">' +
                '<div>' +
                '<div class="text-[11px] text-gray-500">Unit • Size</div>' +
                '<div class="text-sm font-medium text-secondary">' + escapeHtml(pr.package_size || '-') + ' ' + escapeHtml(unitName || '') + '</div>' +
                '</div>' +
                '<button class="p-2 rounded hover:bg-gray-50" data-edit-pricing-index="' + idx + '"><i class="fas fa-pen text-gray-700"></i></button>' +
                '</div>' +
                '<div class="grid grid-cols-2 gap-3 mt-3">' +
                '<div>' +
                '<div class="text-[11px] text-gray-500">Package</div>' +
                '<div class="text-sm font-semibold text-secondary">' + escapeHtml(pkgName || '') + '</div>' +
                '</div>' +
                '<div class="text-right">' +
                '<div class="text-[11px] text-gray-500">Price</div>' +
                '<div class="text-base font-bold text-secondary">UGX ' + formatMoneyClean(pr.price || 0) + '</div>' +
                '<div class="text-[11px] text-gray-500">Comm: ' + commission + '</div>' +
                '</div>' +
                '</div>' +
                '<div class="flex items-center justify-between mt-3">' +
                '<span class="badge ' + categoryBadge + '">' + capFirst(pr.price_category || '') + '</span>' +
                '<div class="flex items-center gap-2">' +
                '<span class="badge ' + stClass + '">' + stLabel + '</span>' +
                '<select data-status-index="' + idx + '" class="px-2 py-1 border border-gray-300 rounded text-xs">' +
                '<option value="active"' + (String(pr.status).toLowerCase() === 'active' ? ' selected' : '') + '>Active</option>' +
                '<option value="inactive"' + (String(pr.status).toLowerCase() === 'inactive' ? ' selected' : '') + '>Inactive</option>' +
                '<option value="suspended"' + (String(pr.status).toLowerCase() === 'suspended' ? ' selected' : '') + '>Suspended</option>' +
                '<option value="deleted"' + (String(pr.status).toLowerCase() === 'deleted' ? ' selected' : '') + '>Deleted</option>' +
                '</select>' +
                '</div>' +
                '</div>' +
                '</div>';
        }).join('');
    }

    function reloadVendorPricing() {
        openVendorEditor(vendorState.product_id, vendorState.store_id, vendorState.store_name, vendorState.region, vendorState.district);
    }

    function saveVendorChanges() {
        var diff = diffVendorChanges();
        if (diff.changed.length === 0) {
            closeVendorModal();
            return;
        }
        showLoading('Saving changes...');
        var payloadItems = diff.changed.map(function (p) {
            return normalizeOutbound(p);
        });
        fetch(`${BASE_URL}admin/fetch/manageProductsPricing.php?action=saveVendorPricings`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: vendorState.product_id, store_id: vendorState.store_id, line_items: payloadItems })
        })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                hideLoading();
                if (j.success) {
                    showSuccessNotification('Changes saved');
                    closeVendorModal();
                    loadPricedProducts();
                    if (!document.getElementById('pricingModal').classList.contains('hidden')) {
                        openPricingModal(vendorState.product_id);
                    }
                } else {
                    showErrorNotification(j.message || 'Failed to save changes');
                }
            })
            .catch(function () {
                hideLoading();
                showErrorNotification('Server error');
            });
    }

    function normalizeOutbound(pr) {
        return {
            pricing_id: pr.pricing_id || null,
            package_mapping_id: pr.package_mapping_id || null,
            si_unit_id: pr.si_unit_id || null,
            package_size: pr.package_size,
            price_category: pr.price_category,
            price: Number(pr.price || 0),
            delivery_capacity: pr.delivery_capacity === '' ? null : Number(pr.delivery_capacity),
            commission_type: pr.commission_type || 'percentage',
            commission_value: Number(pr.commission_value || 1),
            status: pr.status || 'active'
        };
    }

    function diffVendorChanges() {
        var byId = new Map(vendorState.original.filter(function (x) {
            return x.pricing_id;
        }).map(function (x) {
            return [String(x.pricing_id), x];
        }));
        var changed = [];
        vendorState.items.forEach(function (p) {
            if (p.pricing_id) {
                var orig = byId.get(String(p.pricing_id));
                if (!isSamePricing(p, orig)) {
                    changed.push(p);
                }
            }
        });
        return { changed: changed };
    }

    function isSamePricing(a, b) {
        if (!a || !b) {
            return false;
        }
        function asNum(v) {
            if (v === '' || v == null) {
                return null;
            }
            return Number(v);
        }
        function asStr(v) {
            if (v == null) {
                return '';
            }
            return String(v);
        }
        return asStr(a.package_mapping_id) === asStr(b.package_mapping_id) &&
            asStr(a.si_unit_id) === asStr(b.si_unit_id) &&
            asStr(a.package_size) === asStr(b.package_size) &&
            asStr(a.price_category) === asStr(b.price_category) &&
            asNum(a.price) === asNum(b.price) &&
            asNum(a.delivery_capacity) === asNum(b.delivery_capacity) &&
            asStr(a.commission_type) === asStr(b.commission_type) &&
            asNum(a.commission_value) === asNum(b.commission_value) &&
            asStr((a.status || 'active').toLowerCase()) === asStr((b.status || 'active').toLowerCase());
    }

    function labelForPackage(id) {
        var x = vendorState.packages.find(function (p) {
            return String(p.id) === String(id);
        });
        return x ? x.package_name : '';
    }

    function labelForUnit(id) {
        var x = vendorState.units.find(function (u) {
            return String(u.id) === String(id);
        });
        return x ? x.si_unit : '';
    }

    function fillSelectOptions(sel, list) {
        sel.innerHTML = '<option value="">Select</option>' + list.map(function (o) {
            return '<option value="' + escapeHtml(o.value) + '">' + escapeHtml(o.label) + '</option>';
        }).join('');
    }

    function closeVendorModal() {
        document.getElementById('vendorEditModal').classList.add('hidden');
    }

    function openStepper(mode, index) {
        vendorState.stepper = {
            mode: 'edit',
            index: index != null ? index : null,
            step: 1,
            package_mapping_id: '',
            package_name: '',
            si_unit_id: '',
            si_unit: '',
            package_size: '',
            price_category: '',
            price: '',
            delivery_capacity: '',
            commission_type: 'percentage',
            commission_value: 1,
            status: 'active'
        };
        if (index != null) {
            var pr = vendorState.items[index];
            vendorState.stepper.package_mapping_id = pr.package_mapping_id || '';
            vendorState.stepper.package_name = pr.package_name || labelForPackage(pr.package_mapping_id) || '';
            vendorState.stepper.si_unit_id = pr.si_unit_id || '';
            vendorState.stepper.si_unit = pr.si_unit || labelForUnit(pr.si_unit_id) || '';
            vendorState.stepper.package_size = pr.package_size || '';
            vendorState.stepper.price_category = pr.price_category || '';
            vendorState.stepper.price = pr.price || '';
            vendorState.stepper.delivery_capacity = pr.delivery_capacity || '';
            vendorState.stepper.commission_type = pr.commission_type || 'percentage';
            vendorState.stepper.commission_value = pr.commission_value != null ? pr.commission_value : 1;
            vendorState.stepper.status = pr.status || 'active';
        }
        document.getElementById('pvProductName').textContent = vendorState.product_name;
        bindStepperFields();
        setStep(1);
        updatePreview();
        document.getElementById('pricingStepperModal').classList.remove('hidden');
    }

    function bindStepperFields() {
        document.getElementById('stPackage').value = vendorState.stepper.package_mapping_id || '';
        document.getElementById('stUnit').value = vendorState.stepper.si_unit_id || '';
        document.getElementById('stSize').value = vendorState.stepper.package_size || '';
        document.getElementById('stPriceCategory').value = vendorState.stepper.price_category || '';
        document.getElementById('stPrice').value = vendorState.stepper.price || '';
        document.getElementById('stCapacity').value = vendorState.stepper.delivery_capacity || '';
        document.getElementById('stCommissionType').value = vendorState.stepper.commission_type || 'percentage';
        document.getElementById('stCommissionValue').value = vendorState.stepper.commission_value || 1;
        onCommissionTypeChange();
        onCapacityLabel();
    }

    function setStep(n) {
        vendorState.stepper.step = n;
        Array.from(document.querySelectorAll('#pricingStepperModal [data-step]')).forEach(function (el) {
            var show = Number(el.getAttribute('data-step')) === n;
            el.classList.toggle('hidden', !show);
        });
        document.getElementById('btnPrevStep').disabled = n === 1;
        document.getElementById('btnNextStep').classList.toggle('hidden', n === 5);
        document.getElementById('btnSaveStep').classList.toggle('hidden', n !== 5);
        document.getElementById('stepperTitle').textContent = 'Edit Pricing';
        document.getElementById('stepperSub').textContent = vendorState.store_name;
    }

    function nextStep() {
        if (!validateCurrentStep()) {
            return;
        }
        if (vendorState.stepper.step < 5) {
            setStep(vendorState.stepper.step + 1);
        }
    }

    function prevStep() {
        if (vendorState.stepper.step > 1) {
            setStep(vendorState.stepper.step - 1);
        }
    }

    function validateCurrentStep() {
        hideErrors();
        var step = vendorState.stepper.step;

        if (step === 1) {
            var v = document.getElementById('stPackage').value;
            if (!v) {
                document.getElementById('errPackage').classList.remove('hidden');
                return false;
            }
            vendorState.stepper.package_mapping_id = v;
            vendorState.stepper.package_name = labelForPackage(v) || '';
            return true;
        }

        if (step === 2) {
            var u = document.getElementById('stUnit').value.trim();
            var s = document.getElementById('stSize').value.trim();
            var ok = true;
            if (!u) {
                document.getElementById('errUnit').classList.remove('hidden');
                ok = false;
            }
            if (!isValidSize(s)) {
                document.getElementById('errSize').classList.remove('hidden');
                ok = false;
            }
            if (!ok) {
                return false;
            }
            vendorState.stepper.si_unit_id = u;
            vendorState.stepper.si_unit = labelForUnit(u) || '';
            vendorState.stepper.package_size = s;
            return true;
        }

        if (step === 3) {
            var c = document.getElementById('stPriceCategory').value;
            var p = Number(document.getElementById('stPrice').value);
            var ok2 = true;
            if (!c) {
                document.getElementById('errCategory').classList.remove('hidden');
                ok2 = false;
            }
            if (!(p >= 0)) {
                document.getElementById('errPrice').classList.remove('hidden');
                ok2 = false;
            }
            if (!ok2) {
                return false;
            }
            vendorState.stepper.price_category = c;
            vendorState.stepper.price = p;
            return true;
        }

        if (step === 4) {
            var cap = document.getElementById('stCapacity').value;
            if (cap === '' || Number(cap) < 0) {
                document.getElementById('errCapacity').classList.remove('hidden');
                return false;
            }
            vendorState.stepper.delivery_capacity = Number(cap);
            return true;
        }

        if (step === 5) {
            var t = document.getElementById('stCommissionType').value;
            var v2 = Number(document.getElementById('stCommissionValue').value);
            if (!t) {
                document.getElementById('errCommType').classList.remove('hidden');
                return false;
            }
            if (t === 'percentage') {
                if (!(v2 >= 1 && v2 <= 5)) {
                    document.getElementById('errCommValue').classList.remove('hidden');
                    return false;
                }
            } else {
                var price = Number(document.getElementById('stPrice').value || 0);
                var min = round2(price * 0.01);
                var max = round2(price * 0.05);
                if (!(price > 0 && v2 >= min && v2 <= max)) {
                    document.getElementById('errCommValue').classList.remove('hidden');
                    return false;
                }
            }
            vendorState.stepper.commission_type = t;
            vendorState.stepper.commission_value = v2;
            return true;
        }

        return true;
    }

    function commitStepper() {
        if (!validateCurrentStep()) {
            return;
        }
        if (vendorState.stepper.index == null) {
            closeStepper();
            return;
        }
        var existingStatus = vendorState.items[vendorState.stepper.index].status || 'active';
        var entry = {
            pricing_id: vendorState.items[vendorState.stepper.index].pricing_id || null,
            package_mapping_id: vendorState.stepper.package_mapping_id,
            package_name: vendorState.stepper.package_name,
            si_unit_id: vendorState.stepper.si_unit_id,
            si_unit: vendorState.stepper.si_unit,
            package_size: vendorState.stepper.package_size,
            price_category: vendorState.stepper.price_category,
            price: Number(vendorState.stepper.price || 0),
            delivery_capacity: vendorState.stepper.delivery_capacity === '' ? null : Number(vendorState.stepper.delivery_capacity || 0),
            commission_type: vendorState.stepper.commission_type,
            commission_value: Number(vendorState.stepper.commission_value || 1),
            status: existingStatus
        };
        vendorState.items.splice(vendorState.stepper.index, 1, entry);
        renderVendorItems();
        closeStepper();
    }

    function closeStepper() {
        document.getElementById('pricingStepperModal').classList.add('hidden');
    }

    function hideErrors() {
        ['errPackage', 'errUnit', 'errSize', 'errCategory', 'errPrice', 'errCapacity', 'errCommType', 'errCommValue'].forEach(function (id) {
            document.getElementById(id).classList.add('hidden');
        });
    }

    function isValidSize(v) {
        var t = (v || '').trim();
        if (!t) {
            return false;
        }
        return /^[0-9./xX ]+$/.test(t);
    }

    function onCommissionTypeChange() {
        var t = document.getElementById('stCommissionType').value;
        var price = Number(document.getElementById('stPrice').value || 0);
        var lbl = document.getElementById('stCommissionLabel');
        var hint = document.getElementById('stCommissionHint');
        if (t === 'flat') {
            lbl.textContent = 'Commission (UGX)';
            var min = round2(price * 0.01);
            var max = round2(price * 0.05);
            hint.textContent = price > 0 ? 'Allowed: UGX ' + formatMoneyClean(min) + ' to UGX ' + formatMoneyClean(max) : 'Enter price first';
        } else {
            lbl.textContent = 'Commission (%)';
            hint.textContent = 'Allowed: 1% to 5%';
        }
    }

    function onCapacityLabel() {
        var c = document.getElementById('stPriceCategory').value;
        var label = document.getElementById('stCapacityLabel');
        label.textContent = c === 'retail' ? 'Max Capacity' : (c ? 'Min Capacity' : 'Capacity');
    }

    function round2(n) {
        return Math.round(n * 100) / 100;
    }

    function updatePreview() {
        var pkg = labelForPackage(document.getElementById('stPackage').value) || vendorState.stepper.package_name || '-';
        var size = document.getElementById('stSize').value || vendorState.stepper.package_size || '-';
        var unit = labelForUnit(document.getElementById('stUnit').value) || vendorState.stepper.si_unit || '';
        var cat = (document.getElementById('stPriceCategory').value || vendorState.stepper.price_category || '').toUpperCase();
        var price = Number(document.getElementById('stPrice').value || vendorState.stepper.price || 0);
        var ctype = document.getElementById('stCommissionType').value || vendorState.stepper.commission_type || 'percentage';
        var cval = Number(document.getElementById('stCommissionValue').value || vendorState.stepper.commission_value || 1);
        var cap = document.getElementById('stCapacity').value || vendorState.stepper.delivery_capacity || '-';
        document.getElementById('pvPkg').textContent = size + ' ' + unit + ' - ' + pkg;
        document.getElementById('pvCat').textContent = cat || '-';
        document.getElementById('pvPrice').textContent = 'UGX ' + formatMoneyClean(price);
        document.getElementById('pvComm').textContent = ctype === 'flat' ? 'UGX ' + formatMoneyClean(cval) : formatMoneyClean(cval) + '%';
        document.getElementById('pvCap').textContent = cap;
        onCommissionTypeChange();
    }

    function showLoading(message) {
        document.getElementById('loadingMessage').textContent = message || 'Loading...';
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function showSuccessNotification(message) {
        var notif = document.getElementById('successNotification');
        var msgEl = document.getElementById('successMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(function () {
            notif.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        var notif = document.getElementById('errorNotification');
        var msgEl = document.getElementById('errorMessage');
        msgEl.textContent = message;
        notif.classList.remove('hidden');
        setTimeout(function () {
            notif.classList.add('hidden');
        }, 5000);
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function showSessionExpiredModal() {
        window.location.href = BASE_URL;
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>