<div id="products-tab" class="tab-pane active">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div class="flex flex-col sm:flex-row gap-4 w-full">
            <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                <option value="">All Categories</option>
            </select>
            <select id="sort-products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                <option value="default">Default Sorting</option>
                <option value="latest">Latest</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
            </select>
            <input type="text" id="search-products" placeholder="Search products..."
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
        </div>
    </div>

    <div id="products-container" class="masonry-grid">
        <div class="col-span-full text-center py-8 text-gray-500">
            No products found for this vendor.
        </div>
    </div>
    <button id="loadMoreBtn"
        class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
        Load More Products
    </button>
</div>

<!-- Buy In Store Modal -->
<div id="buyInStoreModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
        <div class="flex h-full">
            <!-- Left Column - Form -->
            <div class="w-full md:w-1/2 border-r border-gray-100 overflow-y-auto max-h-[90vh]">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 md:hidden" id="buyInStoreTitle">Buy In Store</h3>
                        <h3 class="text-xl font-bold text-gray-800 hidden md:block">Complete Your Request</h3>
                        <button onclick="closeBuyInStoreModal()"
                            class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div id="buyInStoreLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-red-600">
                    </div>
                    <p class="mt-2 text-gray-600">Loading your information...</p>
                </div>

                <form id="buyInStoreForm" class="p-6 space-y-6 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visit Date <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <div id="datepicker-container" class="w-full"></div>
                            <input type="hidden" id="visitDate" required>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Please select a date when you plan to visit our store</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Package <span
                                class="text-red-500">*</span></label>
                        <select id="packageSelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            required>
                            <option value="">Select a package</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity <span
                                class="text-red-500">*</span></label>
                        <div class="flex items-center">
                            <button type="button" id="decreaseQuantity"
                                class="px-3 py-2 border border-gray-300 rounded-l-md bg-gray-100 hover:bg-gray-200">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                            </button>
                            <input type="number" id="quantityInput"
                                class="w-full px-3 py-2 border-t border-b border-gray-300 text-center focus:ring-0 focus:border-gray-300"
                                value="1" min="1">
                            <button type="button" id="increaseQuantity"
                                class="px-3 py-2 border border-gray-300 rounded-r-md bg-gray-100 hover:bg-gray-200">
                                <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="capacityNote">Minimum quantity: 1</p>
                    </div>

                    <div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="showAltContact"
                                class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="showAltContact" class="ml-2 block text-sm text-gray-700">Add alternative contact
                                details (optional)</label>
                        </div>

                        <div id="altContactFields" class="space-y-4 hidden">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label>
                                <input type="text" id="altPhone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Alternative phone number">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alternative Email</label>
                                <input type="email" id="altEmail"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    placeholder="Alternative email address">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Any special requests or notes for your visit"></textarea>
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between">
                            <button type="button" onclick="closeBuyInStoreModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" id="submitBuyInStore"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column - Product & User Details -->
            <div class="hidden md:block md:w-1/2">
                <div id="productDetailPanel" class="h-full flex flex-col">
                    <div class="p-6 flex-1 overflow-y-auto bg-gray-50 max-h-[90vh] overflow-hidden">
                        <div class="mb-6">
                            <h3 id="productName" class="text-xl font-bold text-gray-800 mb-2"></h3>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4">
                                <img id="productImage" src="" alt="Product Image"
                                    class="w-full h-48 object-cover">
                            </div>
                            <p id="productDescription" class="text-gray-600 text-sm line-clamp-2 mb-2"></p>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mb-6">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Order Summary
                            </h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Selected Package:</span>
                                    <span id="summaryPackage" class="font-medium line-clamp-1">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Quantity:</span>
                                    <span id="summaryQuantity" class="font-medium">-</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Visit Date:</span>
                                    <span id="summaryDate" class="font-medium">-</span>
                                </div>
                                <div id="summaryAltContactContainer"
                                    class="hidden space-y-2 pt-2 border-t border-gray-200">
                                    <h5 class="text-sm font-medium text-gray-500">Alternative Contact</h5>
                                    <div id="summaryAltPhone" class="text-sm hidden">
                                        <span class="text-gray-600">Phone:</span>
                                        <span class="ml-2 font-medium">-</span>
                                    </div>
                                    <div id="summaryAltEmail" class="text-sm hidden">
                                        <span class="text-gray-600">Email:</span>
                                        <span class="ml-2 font-medium">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Your Information
                            </h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Name</p>
                                        <p id="summaryName" class="font-medium">-</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Email</p>
                                        <p id="summaryEmail" class="font-medium">-</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-xs text-gray-500">Phone</p>
                                        <p id="summaryPhone" class="font-medium">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error and Success States -->
<div id="buyInStoreError" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700" id="errorMessage">
                        An error occurred. Please try again.
                    </p>
                </div>
            </div>
        </div>
        <button onclick="closeBuyInStoreModal()"
            class="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none">
            Close
        </button>
    </div>
</div>

<div id="buyInStoreSuccess" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Request Submitted!</h3>
        <p class="text-gray-600 mb-6">Your in-store purchase request has been submitted successfully. We'll be
            expecting you on your selected date.</p>
        <button onclick="closeBuyInStoreModal()"
            class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">
            Done
        </button>
    </div>
</div>

<style>
    .masonry-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 640px) {
        .masonry-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .masonry-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .price-container {
        position: relative;
        display: inline-block;
    }

    .price-hidden {
        display: none;
    }

    .view-price-btn {
        color: #2563eb;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: underline;
    }

    .view-price-btn:hover {
        color: #1d4ed8;
    }

    .view-more-prices {
        color: #2563eb;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-align: center;
        padding: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px dashed #e5e7eb;
    }

    .view-more-prices:hover {
        color: #1d4ed8;
        background-color: #f9fafb;
    }

    .product-card {
        break-inside: avoid;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .login-note {
        text-align: center;
        font-size: 0.875rem;
        color: #6b7280;
        padding: 0.5rem;
        margin-top: 0.5rem;
        border-top: 1px dashed #e5e7eb;
    }

    .login-btn {
        display: block;
        text-align: center;
        background-color: #f3f4f6;
        color: #4b5563;
        padding: 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .login-btn:hover {
        background-color: #e5e7eb;
        color: #374151;
    }

    .line-clamp-2 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-1 {
        display: -webkit-box;
        display: box;
        -webkit-line-clamp: 1;
        line-clamp: 1;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
    }

    /* Datepicker styles */
    .datepicker {
        font-family: inherit;
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        width: 100%;
        overflow: hidden;
    }

    .datepicker-header {
        background-color: #f9fafb;
        padding: 1rem;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .datepicker-title {
        font-weight: 600;
        font-size: 1rem;
        color: #111827;
        margin: 0;
    }

    .datepicker-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .datepicker-prev-btn,
    .datepicker-next-btn {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        border-radius: 0.25rem;
    }

    .datepicker-prev-btn:hover,
    .datepicker-next-btn:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    .datepicker-month-year {
        font-weight: 500;
        color: #111827;
    }

    .datepicker-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        padding: 0.5rem;
    }

    .datepicker-day-header {
        text-align: center;
        font-size: 0.75rem;
        font-weight: 500;
        color: #6b7280;
        padding: 0.5rem 0;
    }

    .datepicker-day {
        text-align: center;
        padding: 0.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        color: #1f2937;
        font-size: 0.875rem;
    }

    .datepicker-day:hover:not(.disabled):not(.selected) {
        background-color: #f3f4f6;
    }

    .datepicker-day.selected {
        background-color: #ef4444;
        color: white;
        font-weight: 500;
    }

    .datepicker-day.today:not(.selected) {
        border: 1px solid #ef4444;
        color: #ef4444;
    }

    .datepicker-day.disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }
</style>

<script>
    // Current product being viewed in Buy In Store modal
    let currentProduct = null;

    async function getProductImageUrl(product) {
        const placeholderText = encodeURIComponent((product.name || '').substring(0, 2));
        const placeholder = `https://placehold.co/400x300/f0f0f0/808080?text=${placeholderText}`;

        try {
            const res = await fetch(`${BASE_URL}img/products/${product.id}/images.json`);
            if (!res.ok) return placeholder;
            const json = await res.json();
            if (Array.isArray(json.images) && json.images.length > 0) {
                return `${BASE_URL}img/products/${product.id}/${json.images[0]}`;
            }
        } catch (e) {
        }
        return placeholder;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (vendorId) {
            loadProductsForDisplay(vendorId);
        }

        document.getElementById('filter-category').addEventListener('change', filterProductsByCategory);
        document.getElementById('search-products').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const categoryId = document.getElementById('filter-category').value;
            filterProducts(categoryId, searchTerm);
        });
        document.getElementById('sort-products').addEventListener('change', function (e) {
            const sortValue = e.target.value;
            const container = document.getElementById('products-container');
            const productCards = Array.from(container.children);
            if (productCards.length <= 1) return;
            productCards.sort((a, b) => {
                if (sortValue === 'latest') {
                    return 0;
                } else if (sortValue === 'price-low') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return aPrice - bPrice;
                } else if (sortValue === 'price-high') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return bPrice - aPrice;
                }
                return 0;
            });
            container.innerHTML = '';
            productCards.forEach(card => container.appendChild(card));
        });

        document.getElementById('loadMoreBtn').addEventListener('click', function () {
            if (currentPage < totalPages) {
                loadProductsForDisplay(vendorId, currentPage + 1);
            }
        });

        // Buy In Store form event listeners
        document.getElementById('buyInStoreForm').addEventListener('submit', function (e) {
            e.preventDefault();
            submitBuyInStoreRequest();
        });

        document.getElementById('decreaseQuantity').addEventListener('click', function () {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > parseInt(input.min)) {
                input.value = currentValue - 1;
                updateSummary();
            }
        });

        document.getElementById('increaseQuantity').addEventListener('click', function () {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 9999;
            if (currentValue < max) {
                input.value = currentValue + 1;
                updateSummary();
            }
        });

        document.getElementById('packageSelect').addEventListener('change', function () {
            updateCapacityLimits();
            updateSummary();
        });

        document.getElementById('quantityInput').addEventListener('change', updateSummary);
        document.getElementById('notes').addEventListener('input', updateSummary);

        // Alternative contact fields toggle
        document.getElementById('showAltContact').addEventListener('change', function () {
            const altContactFields = document.getElementById('altContactFields');
            const summaryAltContactContainer = document.getElementById('summaryAltContactContainer');

            if (this.checked) {
                altContactFields.classList.remove('hidden');
                summaryAltContactContainer.classList.remove('hidden');
            } else {
                altContactFields.classList.add('hidden');
                summaryAltContactContainer.classList.add('hidden');
                document.getElementById('altPhone').value = '';
                document.getElementById('altEmail').value = '';
                updateSummary();
            }
        });

        document.getElementById('altPhone').addEventListener('input', updateSummary);
        document.getElementById('altEmail').addEventListener('input', updateSummary);

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('view-price-btn')) {
                <?php if (!$isLoggedIn): ?>
                    if (typeof openAuthModal === 'function') {
                        openAuthModal();
                    }
                    return false;
                <?php else: ?>
                    const priceValue = e.target.nextElementSibling;
                    priceValue.classList.remove('price-hidden');
                    e.target.classList.add('price-hidden');
                <?php endif; ?>
            }

            if (e.target.classList.contains('view-more-prices')) {
                <?php if (!$isLoggedIn): ?>
                    if (typeof openAuthModal === 'function') {
                        openAuthModal();
                    }
                    return false;
                <?php else: ?>
                    const hiddenPrices = e.target.previousElementSibling.querySelectorAll('.hidden-price-row');
                    hiddenPrices.forEach(row => row.classList.remove('hidden'));
                    e.target.classList.add('hidden');
                <?php endif; ?>
            }

            if (e.target.classList.contains('login-btn')) {
                if (typeof openAuthModal === 'function') {
                    openAuthModal();
                }
                return false;
            }
        });
    });

    function ymd(year, month /* 0-based */, day) {
        const mm = String(month + 1).padStart(2, '0');
        const dd = String(day).padStart(2, '0');
        return `${year}-${mm}-${dd}`;
    }

    // Initialize datepicker
    function initDatepicker() {
        const container = document.getElementById('datepicker-container');
        const hiddenInput = document.getElementById('visitDate');

        const eatNow = new Date(new Date().toLocaleString('en-US', { timeZone: 'Africa/Kampala' }));
        eatNow.setHours(0, 0, 0, 0);
        const today = eatNow;
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();

        const todayString = ymd(today.getFullYear(), today.getMonth(), today.getDate());
        hiddenInput.value = todayString;

        // Create datepicker structure
        container.innerHTML = `
            <div class="datepicker">
                <div class="datepicker-controls">
                    <button type="button" class="datepicker-prev-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="datepicker-month-year"></span>
                    <button type="button" class="datepicker-next-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                <div class="datepicker-grid">
                    <div class="datepicker-day-header">Sun</div>
                    <div class="datepicker-day-header">Mon</div>
                    <div class="datepicker-day-header">Tue</div>
                    <div class="datepicker-day-header">Wed</div>
                    <div class="datepicker-day-header">Thu</div>
                    <div class="datepicker-day-header">Fri</div>
                    <div class="datepicker-day-header">Sat</div>
                </div>
            </div>
        `;

        const datepicker = container.querySelector('.datepicker');
        const monthYearDisplay = container.querySelector('.datepicker-month-year');
        const daysGrid = container.querySelector('.datepicker-grid');
        const prevBtn = container.querySelector('.datepicker-prev-btn');
        const nextBtn = container.querySelector('.datepicker-next-btn');

        // Event listeners for navigation
        prevBtn.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });

        nextBtn.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });

        // Render the calendar
        function renderCalendar() {
            // Update month and year display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            monthYearDisplay.textContent = `${monthNames[currentMonth]} ${currentYear}`;

            // Clear previous days
            const dayHeaders = Array.from(daysGrid.querySelectorAll('.datepicker-day-header'));
            daysGrid.innerHTML = '';

            // Add day headers back
            dayHeaders.forEach(header => daysGrid.appendChild(header));

            // Get first day of month and number of days
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

            // Add empty cells for days before first day of month
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'datepicker-day';
                daysGrid.appendChild(emptyDay);
            }

            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'datepicker-day';
                dayElement.textContent = day;

                const date = new Date(currentYear, currentMonth, day);
                date.setHours(0, 0, 0, 0);
                const dateString = ymd(currentYear, currentMonth, day);

                // Check if this is today
                const isToday = date.toDateString() === today.toDateString();
                if (isToday) {
                    dayElement.classList.add('today');
                }

                // Allow selection of today and future dates
                if (date < today && !isToday) {
                    dayElement.classList.add('disabled');
                } else {
                    dayElement.addEventListener('click', () => {
                        // Remove selected class from all days
                        document.querySelectorAll('.datepicker-day.selected').forEach(el => {
                            el.classList.remove('selected');
                        });

                        // Add selected class to clicked day
                        dayElement.classList.add('selected');

                        // Update hidden input
                        hiddenInput.value = dateString;

                        // Update summary
                        updateSummary();
                    });
                }

                // Check if this day is already selected
                if (hiddenInput.value === dateString) {
                    dayElement.classList.add('selected');
                }

                daysGrid.appendChild(dayElement);
            }
        }

        // Initial render
        renderCalendar();

        // Update summary with default date
        updateSummary();
    }

    function loadProductsForDisplay(id, page = 1) {
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreProducts&id=${id}&page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderProductsForDisplay(data.products, page === 1);
                    if (page === 1) {
                        allProducts = data.products;
                    } else {
                        allProducts = [...allProducts, ...data.products];
                    }
                    currentPage = data.pagination?.page || 1;
                    totalPages = data.pagination?.pages || 1;
                    const loadMoreBtn = document.getElementById('loadMoreBtn');
                    if (currentPage < totalPages) {
                        loadMoreBtn.classList.remove('hidden');
                    } else {
                        loadMoreBtn.classList.add('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
    }

    function populateCategoryFilter(categories) {
        if (!categories || categories.length === 0) return;
        const filterSelect = document.getElementById('filter-category');
        filterSelect.innerHTML = '<option value="">All Categories</option>';
        const activeCats = categories.filter(cat => cat.status === 'active');
        activeCats.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            filterSelect.appendChild(option);
        });
        filterSelect.addEventListener('change', filterProductsByCategory);
    }

    function filterProductsByCategory() {
        const categoryId = document.getElementById('filter-category').value;
        const searchTerm = document.getElementById('search-products').value.toLowerCase();
        filterProducts(categoryId, searchTerm);
    }

    function filterProducts(categoryId = '', searchTerm = '') {
        const container = document.getElementById('products-container');
        container.innerHTML = '';
        let filtered = [...allProducts];
        if (categoryId) {
            filtered = filtered.filter(p => p.store_category_id === categoryId);
        }
        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
        }
        if (filtered.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found matching your criteria.</div>`;
            return;
        }
        renderProductsForDisplay(filtered, true);
    }

    async function renderProductsForDisplay(products, clearExisting = true) {
        const container = document.getElementById('products-container');
        if (clearExisting) {
            container.innerHTML = '';
        }
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found for this vendor.</div>`;
            return;
        }

        for (const product of products) {
            const imageUrl = await getProductImageUrl(product);

            let lowestPrice = 0;
            if (product.pricing && product.pricing.length > 0) {
                lowestPrice = Math.min(...product.pricing.map(p => parseFloat(p.price)));
            }

            let filteredPricing = product.pricing || [];
            let hasRetailPrice = false;

            <?php if (!$isLoggedIn): ?>
                filteredPricing = filteredPricing.filter(p => p.price_category === 'retail');
                if (filteredPricing.length > 0) {
                    hasRetailPrice = true;
                }
            <?php endif; ?>

            let pricingLines = '';
            let hasHiddenPrices = false;

            if (filteredPricing.length > 0) {
                filteredPricing.forEach((pr, index) => {
                    const unitParts = pr.unit_name.split(' ');
                    const siUnit = unitParts[0] || '';
                    const packageName = unitParts.slice(1).join(' ') || '';
                    const formattedUnit = `${pr.package_size} ${siUnit} ${packageName}`.trim();

                    let categoryDisplay = '';
                    <?php if ($isLoggedIn): ?>
                        categoryDisplay = pr.price_category.charAt(0).toUpperCase() + pr.price_category.slice(1);
                    <?php endif; ?>

                    let deliveryCapacity = '';
                    <?php if ($isLoggedIn): ?>
                        if (pr.delivery_capacity) {
                            deliveryCapacity = `<span class="ml-2">• ${pr.price_category === 'retail' ? 'Max' : 'Min'} Capacity: ${pr.delivery_capacity}</span>`;
                        }
                    <?php endif; ?>

                    const hiddenClass = index >= 2 ? 'hidden hidden-price-row' : '';
                    if (index >= 2) hasHiddenPrices = true;

                    pricingLines += `
                <div class="flex justify-between items-center mb-2 p-2 bg-gray-50 rounded ${hiddenClass}">
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-700">${escapeHtml(formattedUnit)}</span>
                        ${(categoryDisplay || deliveryCapacity) ? `
                            <div class="flex items-center text-xs text-gray-500">
                                ${categoryDisplay ? `<span>${categoryDisplay}</span>` : ''}
                                ${deliveryCapacity}
                            </div>` : ''}
                    </div>
                    <div class="price-container">
                        <span class="view-price-btn">View Price</span>
                        <span class="price-hidden text-red-600 font-bold">UGX ${formatNumber(pr.price)}</span>
                    </div>
                </div>`;
                });

                if (hasHiddenPrices) {
                    pricingLines = `
                <div class="pricing-rows">
                    ${pricingLines}
                </div>
                <div class="view-more-prices">View More Prices</div>`;
                }

                <?php if (!$isLoggedIn): ?>
                    if (hasRetailPrice) {
                        pricingLines += `<div class="login-note">Login to view more price categories</div>`;
                    }
                <?php endif; ?>
            } else {
                <?php if (!$isLoggedIn): ?>
                    pricingLines = `<button class="login-btn">Login to see Price Categories</button>`;
                <?php else: ?>
                    pricingLines = `<div class="text-sm text-gray-600 italic p-2">No price data</div>`;
                <?php endif; ?>
            }

            const productCard = document.createElement('div');
            productCard.className = 'product-card transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden';

            productCard.innerHTML = `
        <div class="relative group">
            <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-40 md:h-48 object-cover">

            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                <a href="${BASE_URL}view/product/${product.id}" target="_blank"
                   class="bg-white text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-[#D92B13] hover:text-white transition-colors text-sm">
                   View Details
                </a>
            </div>
        </div>

        <div class="p-3 md:p-5 flex flex-col justify-between flex-1">
            <div>
                <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                    ${escapeHtml(product.name)}
                </h3>
                <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                    ${escapeHtml(product.description || '')}
                </p>
                <div class="border-t border-gray-200 pt-3 mb-3">${pricingLines}</div>
            </div>

            <div class="flex space-x-2 mt-auto">
                <?php if ($isLoggedIn): ?>
                <button onclick="buyInStore('${product.store_product_id}')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                    <i class="fas fa-shopping-cart mr-1"></i> Buy in Store
                </button>
                <?php else: ?>
                <button onclick="openAuthModal(); return false;" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                    <i class="fas fa-shopping-cart mr-1"></i> Buy in Store
                </button>
                <?php endif; ?>
                <a href="${BASE_URL}view/product/${product.id}?action=sell"
                   class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                    <i class="fas fa-tag mr-1"></i> Sell
                </a>
            </div>
        </div>`;

            container.appendChild(productCard);
        }
    }

    function showProductDetails(productId) {
        const product = allProducts.find(p => p.store_product_id === productId);
        if (!product) {
            showToast("Product details not found.", "error");
            return;
        }
        showToast(`Showing details for ${product.name}`, "success");
    }

    function buyInStore(productId) {
        const product = allProducts.find(p => p.store_product_id === productId);
        if (!product) {
            showToast("Product details not found.", "error");
            return;
        }

        currentProduct = product;

        // Reset form
        document.getElementById('buyInStoreForm').reset();
        document.getElementById('buyInStoreTitle').textContent = `Complete Your Request`;

        // Show loading state
        document.getElementById('buyInStoreLoading').classList.remove('hidden');
        document.getElementById('buyInStoreForm').classList.add('hidden');
        document.getElementById('buyInStoreError').classList.add('hidden');
        document.getElementById('buyInStoreSuccess').classList.add('hidden');

        // Show modal
        document.getElementById('buyInStoreModal').classList.remove('hidden');

        // Fetch user information
        fetchUserInfo();

        // Populate package options
        populatePackageOptions(product);

        // Initialize datepicker
        initDatepicker();
    }

    function closeBuyInStoreModal() {
        document.getElementById('buyInStoreModal').classList.add('hidden');
        document.getElementById('buyInStoreError').classList.add('hidden');
        document.getElementById('buyInStoreSuccess').classList.add('hidden');
        currentProduct = null;
    }

    function fetchUserInfo() {
        fetch(`${BASE_URL}fetch/manageBuyInStore.php?action=getUserInfo`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate user info in the summary panel
                    const userName = data.user.name || data.user.username || '';
                    const userEmail = data.user.email || '';
                    const userPhone = data.user.phone || '';

                    document.getElementById('summaryName').textContent = userName;
                    document.getElementById('summaryEmail').textContent = userEmail;
                    document.getElementById('summaryPhone').textContent = userPhone;

                    // Populate product details
                    populateProductDetails();

                    // Hide loading, show form
                    document.getElementById('buyInStoreLoading').classList.add('hidden');
                    document.getElementById('buyInStoreForm').classList.remove('hidden');
                } else {
                    showBuyInStoreError(data.message || 'Failed to load user information');
                }
            })
            .catch(error => {
                console.error('Error fetching user info:', error);
                showBuyInStoreError('Network error. Please try again.');
            });
    }

    async function populateProductDetails() {
        if (!currentProduct) return;

        // Get product image
        const imageUrl = await getProductImageUrl(currentProduct);

        // Set product details in the right panel
        document.getElementById('productImage').src = imageUrl;
        document.getElementById('productImage').alt = currentProduct.name;
        document.getElementById('productName').textContent = currentProduct.name;
        document.getElementById('productDescription').textContent = currentProduct.description || 'No description available.';
    }

    function populatePackageOptions(product) {
        const packageSelect = document.getElementById('packageSelect');
        packageSelect.innerHTML = '<option value="">Select a package</option>';

        if (!product.pricing || product.pricing.length === 0) {
            showBuyInStoreError('No package options available for this product');
            return;
        }

        product.pricing.forEach(pricing => {
            const unitParts = pricing.unit_name.split(' ');
            const siUnit = unitParts[0] || '';
            const packageName = unitParts.slice(1).join(' ') || '';
            const formattedUnit = `${pricing.package_size} ${siUnit} ${packageName}`.trim();
            const categoryDisplay = pricing.price_category.charAt(0).toUpperCase() + pricing.price_category.slice(1);

            const option = document.createElement('option');
            option.value = pricing.pricing_id || pricing.id || '';
            option.textContent = `${formattedUnit} (${categoryDisplay}) - UGX ${formatNumber(pricing.price)}`;
            option.dataset.category = pricing.price_category;
            option.dataset.capacity = pricing.delivery_capacity || '1';
            option.dataset.price = pricing.price;
            option.dataset.formattedUnit = formattedUnit;
            option.dataset.categoryDisplay = categoryDisplay;
            packageSelect.appendChild(option);
        });

        // Initialize capacity limits
        updateCapacityLimits();
    }

    function updateCapacityLimits() {
        const packageSelect = document.getElementById('packageSelect');
        const quantityInput = document.getElementById('quantityInput');
        const capacityNote = document.getElementById('capacityNote');

        if (packageSelect.value === '') {
            quantityInput.min = '1';
            quantityInput.value = '1';
            capacityNote.textContent = 'Minimum quantity: 1';
            return;
        }

        const selectedOption = packageSelect.options[packageSelect.selectedIndex];
        const category = selectedOption.dataset.category;
        const capacity = parseInt(selectedOption.dataset.capacity) || 1;

        if (category === 'retail') {
            // For retail, capacity is maximum
            quantityInput.min = '1';
            quantityInput.max = capacity > 0 ? capacity.toString() : '';
            quantityInput.value = '1';
            capacityNote.textContent = `Maximum quantity: ${capacity}`;
        } else {
            // For wholesale/distributor, capacity is minimum
            quantityInput.min = capacity > 0 ? capacity.toString() : '1';
            quantityInput.value = capacity > 0 ? capacity.toString() : '1';
            capacityNote.textContent = `Minimum quantity: ${capacity}`;
        }

        // Update summary
        updateSummary();
    }

    function updateSummary() {
        // Package
        const packageSelect = document.getElementById('packageSelect');
        const summaryPackage = document.getElementById('summaryPackage');

        if (packageSelect.value) {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            const formattedUnit = selectedOption.dataset.formattedUnit;
            const categoryDisplay = selectedOption.dataset.categoryDisplay;
            const price = selectedOption.dataset.price;

            summaryPackage.textContent = `${formattedUnit} (${categoryDisplay}) - UGX ${formatNumber(price)}`;
        } else {
            summaryPackage.textContent = '-';
        }

        // Quantity
        const quantityInput = document.getElementById('quantityInput');
        const summaryQuantity = document.getElementById('summaryQuantity');
        summaryQuantity.textContent = quantityInput.value || '-';

        // Date
        const visitDate = document.getElementById('visitDate').value;
        const summaryDate = document.getElementById('summaryDate');

        if (visitDate) {
            const date = new Date(new Date(visitDate).toLocaleString('en-US', { timeZone: 'Africa/Kampala' }));
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            summaryDate.textContent = date.toLocaleDateString('en-US', options);
        } else {
            summaryDate.textContent = '-';
        }

        // Alternative contact
        const altPhone = document.getElementById('altPhone').value;
        const altEmail = document.getElementById('altEmail').value;
        const summaryAltPhone = document.getElementById('summaryAltPhone');
        const summaryAltEmail = document.getElementById('summaryAltEmail');

        if (altPhone) {
            summaryAltPhone.classList.remove('hidden');
            summaryAltPhone.querySelector('span:last-child').textContent = altPhone;
        } else {
            summaryAltPhone.classList.add('hidden');
        }

        if (altEmail) {
            summaryAltEmail.classList.remove('hidden');
            summaryAltEmail.querySelector('span:last-child').textContent = altEmail;
        } else {
            summaryAltEmail.classList.add('hidden');
        }
    }

    function showBuyInStoreError(message) {
        document.getElementById('buyInStoreModal').classList.add('hidden');
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('buyInStoreError').classList.remove('hidden');
    }

    function submitBuyInStoreRequest() {
        if (!currentProduct) {
            showBuyInStoreError('Product information not found');
            return;
        }

        const visitDate = document.getElementById('visitDate').value;
        const packageId = document.getElementById('packageSelect').value;
        const quantity = document.getElementById('quantityInput').value;
        const altPhone = document.getElementById('altPhone').value;
        const altEmail = document.getElementById('altEmail').value;
        const notes = document.getElementById('notes').value;

        // Validate inputs
        if (!visitDate) {
            showToast('Please select a visit date', 'error');
            return;
        }

        if (!packageId) {
            showToast('Please select a package', 'error');
            return;
        }

        if (!quantity || parseInt(quantity) < 1) {
            showToast('Please enter a valid quantity', 'error');
            return;
        }

        // Disable submit button
        const submitBtn = document.getElementById('submitBuyInStore');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...';

        // Submit request
        fetch(`${BASE_URL}fetch/manageBuyInStore.php?action=submitBuyInStore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                productId: currentProduct.store_product_id,
                visitDate: visitDate,
                packageId: packageId,
                quantity: quantity,
                altContact: altPhone,
                altEmail: altEmail,
                notes: notes
            })
        })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                if (data.success) {
                    // Show success message
                    document.getElementById('buyInStoreModal').classList.add('hidden');
                    document.getElementById('buyInStoreSuccess').classList.remove('hidden');
                } else {
                    showToast(data.message || 'Failed to submit request', 'error');
                }
            })
            .catch(error => {
                console.error('Error submitting request:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showToast('Network error. Please try again.', 'error');
            });
    }
</script>