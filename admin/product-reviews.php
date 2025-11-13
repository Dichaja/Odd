<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Product Reviews Management';
$activeNav = 'products';

ob_start();
?>

<div x-data="reviewsManagement()" x-init="init()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Product Reviews Management</h1>
        <p class="text-gray-600 dark:text-white/70">Manage and moderate product reviews</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Filter By</label>
                <select x-model="filters.filterType" @change="handleFilterTypeChange()" class="w-full px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Reviews</option>
                    <option value="product">By Product</option>
                    <option value="vendor">By Vendor</option>
                </select>
            </div>
            <div x-show="filters.filterType === 'product'">
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Product</label>
                <input type="text" x-model="filters.productSearch" @input.debounce.300ms="searchProducts()" placeholder="Search products..." class="w-full px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <div x-show="productResults.length > 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-white/10 rounded-lg shadow-lg max-h-60 overflow-auto">
                    <template x-for="product in productResults" :key="product.id">
                        <button @click="selectProduct(product)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white" x-text="product.title"></button>
                    </template>
                </div>
            </div>
            <div x-show="filters.filterType === 'vendor'">
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Vendor</label>
                <input type="text" x-model="filters.vendorSearch" @input.debounce.300ms="searchVendors()" placeholder="Search vendors..." class="w-full px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                <div x-show="vendorResults.length > 0" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-white/10 rounded-lg shadow-lg max-h-60 overflow-auto">
                    <template x-for="vendor in vendorResults" :key="vendor.id">
                        <button @click="selectVendor(vendor)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-900 dark:text-white" x-text="vendor.name"></button>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Status</label>
                <select x-model="filters.status" @change="loadReviews()" class="w-full px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-2">Rating</label>
                <select x-model="filters.rating" @change="loadReviews()" class="w-full px-4 py-2 border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Ratings</option>
                    <option value="5">5 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="2">2 Stars</option>
                    <option value="1">1 Star</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="resetFilters()" class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Reset Filters
                </button>
            </div>
        </div>
        <div x-show="filters.selectedProduct || filters.selectedVendor" class="mt-4 flex items-center gap-2">
            <span class="text-sm text-gray-600 dark:text-white/70">Filtering by:</span>
            <span x-show="filters.selectedProduct" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                <span x-text="filters.selectedProduct?.title"></span>
                <button @click="clearProductFilter()" class="ml-2 hover:text-blue-600">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </span>
            <span x-show="filters.selectedVendor" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                <span x-text="filters.selectedVendor?.name"></span>
                <button @click="clearVendorFilter()" class="ml-2 hover:text-green-600">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </button>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-white/70">Total Reviews</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total"></p>
                </div>
                <i data-lucide="message-square" class="w-10 h-10 text-blue-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-white/70">Pending</p>
                    <p class="text-2xl font-bold text-amber-600" x-text="stats.pending"></p>
                </div>
                <i data-lucide="clock" class="w-10 h-10 text-amber-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-white/70">Approved</p>
                    <p class="text-2xl font-bold text-emerald-600" x-text="stats.approved"></p>
                </div>
                <i data-lucide="check-circle" class="w-10 h-10 text-emerald-500"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-secondary rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-white/70">Average Rating</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.avgRating.toFixed(1)"></p>
                </div>
                <i data-lucide="star" class="w-10 h-10 text-amber-500"></i>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="bg-white dark:bg-secondary rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Comment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">

                    </template>
                    <span class="ml-2 text-sm text-gray-600 dark:text-white/70" x-text="review.rating"></span>
                </div>
            </td>

            <!-- Comment -->
            <td class="px-6 py-4">
                <p class="text-sm text-gray-900 dark:text-white line-clamp-2" x-text="review.comment"></p>
            </td>

            <!-- Status -->
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                        'bg-emerald-100 text-emerald-800': review.status === 'approved',
                        'bg-amber-100 text-amber-800': review.status === 'pending',
                        'bg-red-100 text-red-800': review.status === 'rejected'
                    }"
                    x-text="review.status.charAt(0).toUpperCase() + review.status.slice(1)">
                </span>
            </td>

            <!-- Date -->
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-white/70" x-text="formatDate(review.created_at)"></td>

            <!-- Actions -->
            <td class="px-6 py-4">
                <div class="flex items-center space-x-2">
                    <!-- View -->
                    <button @click="viewReview(review)" class="text-blue-600 hover:text-blue-800" title="View">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                    <div class="px-2 py-1 text-xs font-medium rounded-full" @click="updateStatus(review.id, 'rejected')" style="border-radius: 9999px; background-color: #D7D2CB; color: #343434;cursor: pointer;">Reject</div>
                    <div class="px-2 py-1 text-xs font-medium rounded-full" style="border-radius: 9999px; background-color: #D7D2CB; color: #343434;cursor: pointer;" @click="deleteReview(review.id)">Delete</div>
                    <!-- Approve -->
                    <button x-show="review.status?.toLowerCase() !== 'approved'" x-transition.opacity
                        @click="updateStatus(review.id, 'approved')"
                        class="text-emerald-600 hover:text-emerald-800"
                        title="Approve">
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>

                    <!-- Reject -->
                    <button x-show="review.status?.toLowerCase() !== 'rejected'" x-transition.opacity
                        @click="updateStatus(review.id, 'rejected')"
                        class="text-red-600 hover:text-red-800"
                        title="Reject">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>

                    <!-- Delete -->
                    <button  class="text-red-600 hover:text-red-800" title="Delete">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>

        </tr>
    </template>
</tbody>

            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10" x-show="pagination.totalPages > 1">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-white/70">
                    Showing <span x-text="((pagination.currentPage - 1) * pagination.perPage) + 1"></span> to 
                    <span x-text="Math.min(pagination.currentPage * pagination.perPage, pagination.total)"></span> of 
                    <span x-text="pagination.total"></span> results
                </div>
                <div class="flex space-x-2">
                    <button @click="changePage(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1" 
                        class="px-3 py-1 border border-gray-300 dark:border-white/10 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <template x-for="page in paginationPages" :key="page">
                        <button @click="changePage(page)" 
                            :class="page === pagination.currentPage ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-white'"
                            class="px-3 py-1 border border-gray-300 dark:border-white/10 rounded-lg"
                            x-text="page">
                        </button>
                    </template>
                    <button @click="changePage(pagination.currentPage + 1)" :disabled="pagination.currentPage === pagination.totalPages"
                        class="px-3 py-1 border border-gray-300 dark:border-white/10 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Review Modal -->
    <div x-show="viewModal" x-transition.opacity class="fixed inset-0 z-50 overflow-auto bg-black/50 backdrop-blur-sm" @click.self="viewModal = false">
        <div class="bg-white dark:bg-secondary my-[5%] mx-auto p-6 border-none rounded-xl w-[90%] max-w-[600px] shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Review Details</h2>
                <button @click="viewModal = false" class="text-gray-500 hover:text-gray-700 dark:text-white/60 dark:hover:text-white">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <template x-if="selectedReview">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Product</label>
                        <p class="text-gray-900 dark:text-white" x-text="selectedReview.product_title"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">User</label>
                        <p class="text-gray-900 dark:text-white" x-text="selectedReview.username"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Rating</label>
                        <div class="flex items-center">
                            <template x-for="i in 5" :key="i">
                                <i data-lucide="star" class="w-5 h-5" :class="i <= selectedReview.rating ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300'"></i>
                            </template>
                            <span class="ml-2 text-gray-900 dark:text-white" x-text="selectedReview.rating + '/5'"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Comment</label>
                        <p class="text-gray-900 dark:text-white" x-text="selectedReview.comment"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Status</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full" 
                            :class="{
                                'bg-emerald-100 text-emerald-800': selectedReview.status === 'approved',
                                'bg-amber-100 text-amber-800': selectedReview.status === 'pending',
                                'bg-red-100 text-red-800': selectedReview.status === 'rejected'
                            }"
                            x-text="selectedReview.status.charAt(0).toUpperCase() + selectedReview.status.slice(1)">
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Date</label>
                        <p class="text-gray-900 dark:text-white" x-text="formatDate(selectedReview.created_at)"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>

const notifications = {
    success: (message) => {
        alert(message);
    },
    error: (message) => {
        alert('Error: ' + message);
    }
};

function reviewsManagement() {
    return {
        reviews: [],
        stats: {
            total: 0,
            pending: 0,
            approved: 0,
            avgRating: 0
        },
        filters: {
            filterType: '',
            productSearch: '',
            vendorSearch: '',
            selectedProduct: null,
            selectedVendor: null,
            status: '',
            rating: '',
            search: ''
        },
        productResults: [],
        vendorResults: [],
        pagination: {
            currentPage: 1,
            perPage: 20,
            total: 0,
            totalPages: 0
        },
        loading: false,
        viewModal: false,
        selectedReview: null,

        init() {
            this.loadReviews();
            this.loadStats();
            if (window.lucide && lucide.createIcons) lucide.createIcons();
        },

        handleFilterTypeChange() {
            this.filters.selectedProduct = null;
            this.filters.selectedVendor = null;
            this.filters.productSearch = '';
            this.filters.vendorSearch = '';
            this.productResults = [];
            this.vendorResults = [];
            this.loadReviews();
        },

        async searchProducts() {
            if (!this.filters.productSearch || this.filters.productSearch.length < 2) {
                this.productResults = [];
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php?action=search_products&q=${encodeURIComponent(this.filters.productSearch)}`);
                const data = await response.json();
                if (data.success) {
                    this.productResults = data.products;
                }
            } catch (error) {
                console.error('Error searching products:', error);
            }
        },

        async searchVendors() {
            if (!this.filters.vendorSearch || this.filters.vendorSearch.length < 2) {
                this.vendorResults = [];
                return;
            }

            try {
                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php?action=search_vendors&q=${encodeURIComponent(this.filters.vendorSearch)}`);
                const data = await response.json();
                if (data.success) {
                    this.vendorResults = data.vendors;
                }
            } catch (error) {
                console.error('Error searching vendors:', error);
            }
        },

        selectProduct(product) {
            this.filters.selectedProduct = product;
            this.filters.productSearch = product.title;
            this.productResults = [];
            this.loadReviews();
        },

        selectVendor(vendor) {
            this.filters.selectedVendor = vendor;
            this.filters.vendorSearch = vendor.name;
            this.vendorResults = [];
            this.loadReviews();
        },

        clearProductFilter() {
            this.filters.selectedProduct = null;
            this.filters.productSearch = '';
            this.loadReviews();
        },

        clearVendorFilter() {
            this.filters.selectedVendor = null;
            this.filters.vendorSearch = '';
            this.loadReviews();
        },

        async loadReviews() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.pagination.currentPage,
                    per_page: this.pagination.perPage,
                    status: this.filters.status,
                    rating: this.filters.rating,
                    search: this.filters.search
                });

                if (this.filters.selectedProduct) {
                    params.append('product_id', this.filters.selectedProduct.id);
                }

                if (this.filters.selectedVendor) {
                    params.append('vendor_id', this.filters.selectedVendor.id);
                }

                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php?action=list&${params}`);
                const data = await response.json();

                if (data.success) {
                    this.reviews = data.reviews;
                    this.pagination.total = data.pagination.total;
                    this.pagination.totalPages = data.pagination.totalPages;
                    this.$nextTick(() => {
                        if (window.lucide && lucide.createIcons) lucide.createIcons();
                    });
                }
            } catch (error) {
                console.error('Error loading reviews:', error);
                notifications.error('Failed to load reviews');
            } finally {
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php?action=stats`);
                const data = await response.json();

                if (data.success) {
                    this.stats = data.stats;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async updateStatus(reviewId, status) {
            if (!confirm(`Are you sure you want to ${status} this review?`)) return;

            try {
                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'update_status', review_id: reviewId, status })
                });

                const data = await response.json();

                if (data.success) {
                    notifications.success(data.message || `Review ${status} successfully`);
                    this.loadReviews();
                    this.loadStats();
                } else {
                    notifications.error(data.error || 'Failed to update review');
                }
            } catch (error) {
                console.error('Error updating review:', error);
                notifications.error('Failed to update review');
            }
        },

        async deleteReview(reviewId) {
            if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) return;

            try {
                const response = await fetch(`${BASE_URL}admin/fetch/manageProductReviews.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', review_id: reviewId })
                });

                const data = await response.json();
               console.log(data)
                if (data.success) {
                    notifications.success();
                     notifications.success(data.message || 'Review deleted successfully');
                    this.loadReviews();
                    this.loadStats();
                } else {
                    notifications.error(data.error || 'Failed to delete review');
                }
            } catch (error) {
                console.error('Error deleting review:', error);
                notifications.error('Failed to delete review');
            }
        },

        viewReview(review) {
            this.selectedReview = review;
            this.viewModal = true;
            this.$nextTick(() => {
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            });
        },

        changePage(page) {
            if (page < 1 || page > this.pagination.totalPages) return;
            this.pagination.currentPage = page;
            this.loadReviews();
        },

        resetFilters() {
            this.filters = {
                filterType: '',
                productSearch: '',
                vendorSearch: '',
                selectedProduct: null,
                selectedVendor: null,
                status: '',
                rating: '',
                search: ''
            };
            this.productResults = [];
            this.vendorResults = [];
            this.pagination.currentPage = 1;
            this.loadReviews();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        get paginationPages() {
            const pages = [];
            const maxPages = 5;
            let start = Math.max(1, this.pagination.currentPage - Math.floor(maxPages / 2));
            let end = Math.min(this.pagination.totalPages, start + maxPages - 1);

            if (end - start < maxPages - 1) {
                start = Math.max(1, end - maxPages + 1);
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        }
    };
}
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>
