<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Product Categories';
$activeNav = 'products';

if (
    !isset($_SESSION['user']) ||
    !isset($_SESSION['user']['logged_in']) ||
    !$_SESSION['user']['logged_in'] ||
    !isset($_SESSION['user']['is_admin']) ||
    !$_SESSION['user']['is_admin']
) {
    header('Location: ' . BASE_URL);
    exit;
}

ob_start();
?>

<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-secondary">Product Categories</h1>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Manage product categories and
                        their metadata</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="addCategoryBtn"
                        class="px-3 sm:px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-plus text-sm"></i>
                        <span class="hidden sm:inline">Add Category</span>
                    </button>
                    <a href="products"
                        class="px-3 sm:px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left text-sm"></i>
                        <span class="hidden sm:inline">Back to Products</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Categories</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalCategories">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-tags text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="activeCategories">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Featured</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="featuredCategories">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-star text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Inactive</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="inactiveCategories">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-pause-circle text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-secondary mb-2">Filter & Search</h2>
                    <p class="text-sm text-gray-600">Configure your category view and filters</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Categories</label>
                    <div class="relative">
                        <input type="text" id="searchCategories" placeholder="Search categories..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                    <select id="filterStatus"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Featured Filter</label>
                    <select id="filterFeatured"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                        <option value="">All Categories</option>
                        <option value="featured">Featured Only</option>
                        <option value="not-featured">Not Featured</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="resetFilters"
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-secondary">Categories</h3>
                        <p class="text-sm text-gray-600"><span id="category-count">0</span> categories found</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="categories-table">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Featured</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Name</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Products</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Created</th>
                        </tr>
                    </thead>
                    <tbody id="categories-table-body" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading categories...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span
                        id="total-categories">0</span> categories
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <div id="pagination-numbers" class="flex items-center"></div>
                    <button id="next-page"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
        <div class="lg:hidden" id="categoriesCards">
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <div>Loading categories...</div>
            </div>
        </div>

        <div
            class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600 text-center sm:text-left">
                Showing <span id="mobileShowingStart">0</span> to <span id="mobileShowingEnd">0</span> of <span
                    id="mobileTotalCategories">0</span> categories
            </div>
            <div class="flex items-center gap-2">
                <button id="mobilePrevPage"
                    class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                    disabled>
                    Previous
                </button>
                <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                <button id="mobileNextPage"
                    class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                    disabled>
                    Next
                </button>
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

<div id="categoryModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hideCategoryModal()"></div>
    <div
        class="relative w-full h-full max-w-4xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary/10 to-primary/5">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center">
                    <i class="fas fa-tags text-gray-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary" id="modalTitle">Add New Category</h3>
                    <p class="text-sm text-gray-600 mt-1">Create or edit category information</p>
                </div>
            </div>
            <button onclick="hideCategoryModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-160px)]">
            <form id="categoryForm" class="space-y-6">
                <input type="hidden" id="categoryId" name="categoryId" value="">
                <input type="hidden" id="tempImagePath" name="tempImagePath" value="">
                <input type="hidden" id="removeImage" name="removeImage" value="0">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                            placeholder="Enter category name" required>
                    </div>

                    <div>
                        <label for="status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center h-10">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="status-toggle" class="sr-only peer" checked>
                                <div
                                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:bg-primary peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all">
                                </div>
                                <span id="status-text" class="ml-3 text-sm text-gray-700">Active</span>
                            </label>
                            <input type="hidden" id="status" name="status" value="active">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                        placeholder="Enter category description"></textarea>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">SEO Metadata</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta
                                Title</label>
                            <input type="text" id="meta_title" name="meta_title"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                                placeholder="Enter meta title">
                        </div>

                        <div>
                            <label for="keywords-input" class="block text-sm font-medium text-gray-700 mb-1">Meta
                                Keywords</label>
                            <div class="relative">
                                <input type="text" id="keywords-input"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                                    placeholder="Type and press Enter to add keywords">
                                <input type="hidden" id="meta_keywords" name="meta_keywords" value="">
                            </div>
                            <div id="keywords-container" class="flex flex-wrap gap-2 mt-2"></div>
                            <p class="text-xs text-gray-500 mt-1">Press Enter to add each keyword</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta
                            Description</label>
                        <textarea id="meta_description" name="meta_description" rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                            placeholder="Enter meta description"></textarea>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Category Image (16:9 ratio)</h3>
                    <p class="text-sm text-amber-600 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        We recommend using WebP format for better performance.
                    </p>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Image</label>
                                <div class="flex items-center gap-2">
                                    <label for="image"
                                        class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        <span>Choose File</span>
                                    </label>
                                    <span id="selectedFileName" class="text-sm text-gray-500">No file selected</span>
                                    <input type="file" id="image" name="image"
                                        accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 1200×675 pixels (16:9). Max 5MB.
                                </p>
                            </div>
                            <div id="uploadProgress" class="w-full bg-gray-200 rounded-full h-2.5 mb-4 hidden">
                                <div id="uploadProgressBar" class="bg-primary h-2.5 rounded-full" style="width: 0%">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div id="cropperContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Crop Image (16:9)</label>
                                <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <img id="cropperImage" src="https://placehold.co/600x338/e2e8f0/1e293b?text=Crop"
                                        alt="Image to crop" class="max-w-full">
                                </div>
                                <div class="flex justify-end mt-3 space-x-2">
                                    <button type="button" id="cancelCrop"
                                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="button" id="applyCrop"
                                        class="px-3 py-1.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Apply Crop
                                    </button>
                                </div>
                            </div>

                            <div id="imagePreviewContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image Preview</label>
                                <div class="relative aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <img id="imagePreview" src="https://placehold.co/600x338/e2e8f0/1e293b?text=Preview"
                                        alt="Category image preview" class="w-full h-full object-cover">
                                    <button type="button" id="removeImageBtn"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-6 border-t border-gray-100 flex justify-between">
            <button type="button" id="deleteCategoryBtn"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hidden">
                <i class="fas fa-trash-alt mr-2"></i>Delete Category
            </button>
            <div class="flex gap-3 ml-auto">
                <button type="button" onclick="hideCategoryModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" id="submitCategory"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Save Category</button>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="hideDeleteModal()"></div>
    <div
        class="relative w-full h-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[90vh] overflow-hidden m-4">
        <div
            class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-red-50 to-red-100">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 h-12 w-12 rounded-lg bg-red-100 overflow-hidden flex items-center justify-center">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-secondary">Delete Category</h3>
                    <p class="text-sm text-gray-600 mt-1">This action cannot be undone</p>
                </div>
            </div>
            <button onclick="hideDeleteModal()"
                class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 max-h-[calc(90vh-100px)]">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this category? This action cannot be undone.
            </p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Category:</div>
                    <div class="font-medium text-gray-900" id="delete-category-name"></div>
                    <div class="text-gray-500">Status:</div>
                    <div class="font-medium text-gray-900" id="delete-category-status"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
            <button id="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired.</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">Login Now</button>
            </div>
        </div>
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    let categoriesData = [];
    let currentPage = 1;
    let totalPages = 1;
    let itemsPerPage = 10;
    let cropper = null;
    let keywordsList = [];

    document.addEventListener('DOMContentLoaded', function () {
        initializeEventListeners();
        loadCategories();
        initializeKeywordsInput();
        initializeStatusToggle();
    });

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function initializeEventListeners() {
        document.getElementById('addCategoryBtn').addEventListener('click', showAddCategoryModal);
        document.getElementById('submitCategory').addEventListener('click', submitCategoryForm);

        const imageInput = document.getElementById('image');
        imageInput.addEventListener('change', handleImageUpload);

        document.getElementById('cancelCrop').addEventListener('click', cancelCrop);
        document.getElementById('applyCrop').addEventListener('click', applyCrop);

        document.getElementById('removeImageBtn').addEventListener('click', function () {
            document.getElementById('removeImage').value = "1";
            document.getElementById('tempImagePath').value = '';
            document.getElementById('imagePreviewContainer').classList.add('hidden');
            document.getElementById('image').value = '';
            document.getElementById('selectedFileName').textContent = 'Image will be removed';
        });

        document.getElementById('searchCategories').addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase();
            filterCategories(query, document.getElementById('filterStatus').value, document.getElementById('filterFeatured').value);
        });

        document.getElementById('filterStatus').addEventListener('change', function (e) {
            const status = e.target.value;
            filterCategories(document.getElementById('searchCategories').value.toLowerCase(), status, document.getElementById('filterFeatured').value);
        });

        document.getElementById('filterFeatured').addEventListener('change', function (e) {
            const featured = e.target.value;
            filterCategories(document.getElementById('searchCategories').value.toLowerCase(), document.getElementById('filterStatus').value, featured);
        });

        document.getElementById('resetFilters').addEventListener('click', function () {
            document.getElementById('searchCategories').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterFeatured').value = '';
            filterCategories('', '', '');
        });

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
                renderCategories(categoriesData);
            }
        });

        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
                renderCategories(categoriesData);
            }
        });

        document.getElementById('confirmDelete').addEventListener('click', confirmDelete);
        document.getElementById('deleteCategoryBtn').addEventListener('click', function () {
            const categoryId = document.getElementById('categoryId').value;
            if (categoryId) {
                showDeleteModal(categoryId);
            }
        });

        document.getElementById('mobilePrevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderCategoriesCards(categoriesData);
            }
        });

        document.getElementById('mobileNextPage').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                renderCategoriesCards(categoriesData);
            }
        });
    }

    function initializeKeywordsInput() {
        const keywordsInput = document.getElementById('keywords-input');
        const keywordsContainer = document.getElementById('keywords-container');
        const metaKeywordsInput = document.getElementById('meta_keywords');

        keywordsInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();

                const keyword = this.value.trim();
                if (keyword && !keywordsList.includes(keyword)) {
                    keywordsList.push(keyword);
                    renderKeywords();
                    this.value = '';
                }
            }
        });

        function renderKeywords() {
            keywordsContainer.innerHTML = '';
            metaKeywordsInput.value = keywordsList.join(',');

            keywordsList.forEach((keyword, index) => {
                const keywordElement = document.createElement('div');
                keywordElement.className = 'inline-flex items-center bg-gray-100 rounded-full px-3 py-1 text-sm';
                keywordElement.innerHTML = `
                    <span class="mr-1">${escapeHtml(keyword)}</span>
                    <button type="button" class="text-gray-500 hover:text-gray-700" data-index="${index}">
                        <i class="fas fa-times-circle"></i>
                    </button>
                `;

                keywordElement.querySelector('button').addEventListener('click', function () {
                    const index = parseInt(this.getAttribute('data-index'));
                    keywordsList.splice(index, 1);
                    renderKeywords();
                });

                keywordsContainer.appendChild(keywordElement);
            });
        }
    }

    function initializeStatusToggle() {
        const statusToggle = document.getElementById('status-toggle');
        const statusText = document.getElementById('status-text');
        const statusInput = document.getElementById('status');

        statusToggle.addEventListener('change', function () {
            if (this.checked) {
                statusText.textContent = 'Active';
                statusInput.value = 'active';
            } else {
                statusText.textContent = 'Inactive';
                statusInput.value = 'inactive';
            }
        });
    }

    function handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const selectedFileName = document.getElementById('selectedFileName');
        selectedFileName.textContent = file.name;

        document.getElementById('removeImage').value = "0";
        document.getElementById('tempImagePath').value = '';

        const fileType = file.type;
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!validTypes.includes(fileType)) {
            showErrorNotification('Invalid file type. Only JPG, PNG, WebP, and GIF files are allowed.');
            resetImageUpload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showErrorNotification('File size too large. Maximum 5MB allowed.');
            resetImageUpload();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const cropperImage = document.getElementById('cropperImage');
            const cropperContainer = document.getElementById('cropperContainer');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');

            cropperImage.src = e.target.result;
            cropperContainer.classList.remove('hidden');
            imagePreviewContainer.classList.add('hidden');

            if (cropper) {
                cropper.destroy();
            }

            cropper = new Cropper(cropperImage, {
                aspectRatio: 16 / 9,
                viewMode: 1,
                autoCropArea: 1,
                zoomable: false,
                background: false,
                responsive: true,
                checkOrientation: true
            });
        };

        reader.readAsDataURL(file);
    }

    function cancelCrop() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }

        document.getElementById('cropperContainer').classList.add('hidden');
        document.getElementById('image').value = '';
        document.getElementById('selectedFileName').textContent = 'No file selected';
    }

    function applyCrop() {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 1200,
            height: 675,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            showErrorNotification('Failed to crop image');
            return;
        }

        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const cropperContainer = document.getElementById('cropperContainer');

        imagePreview.src = canvas.toDataURL();
        imagePreviewContainer.classList.remove('hidden');
        cropperContainer.classList.add('hidden');

        canvas.toBlob(function (blob) {
            uploadCroppedImage(blob);
        }, 'image/jpeg', 0.9);

        cropper.destroy();
        cropper = null;
    }

    function uploadCroppedImage(blob) {
        const formData = new FormData();
        formData.append('image', blob, 'cropped-image.jpg');

        const uploadProgress = document.getElementById('uploadProgress');
        const uploadProgressBar = document.getElementById('uploadProgressBar');

        uploadProgress.classList.remove('hidden');
        showLoading('Uploading image...');

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                uploadProgressBar.style.width = percentComplete + '%';
            }
        });

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                uploadProgress.classList.add('hidden');
                hideLoading();

                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('tempImagePath').value = response.temp_path;
                            document.getElementById('removeImage').value = "0";
                            showSuccessNotification('Image uploaded successfully');
                        } else {
                            showErrorNotification(response.message || 'Failed to upload image');
                            resetImageUpload();
                        }
                    } catch (e) {
                        showErrorNotification('Error processing server response');
                        resetImageUpload();
                    }
                } else if (xhr.status === 401) {
                    showSessionExpiredModal();
                } else {
                    showErrorNotification('Error uploading image. Please try again.');
                    resetImageUpload();
                }
            }
        };

        xhr.open('POST', `${BASE_URL}admin/fetch/manageProductCategories/uploadImage`, true);
        xhr.send(formData);
    }

    function resetImageUpload() {
        document.getElementById('image').value = '';
        document.getElementById('selectedFileName').textContent = 'No file selected';
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        document.getElementById('cropperContainer').classList.add('hidden');
        document.getElementById('tempImagePath').value = '';
        document.getElementById('removeImage').value = "0";

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function showAddCategoryModal() {
        resetCategoryForm();
        document.getElementById('modalTitle').textContent = 'Add New Category';
        document.getElementById('submitCategory').textContent = 'Save Category';
        document.getElementById('deleteCategoryBtn').classList.add('hidden');
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function showEditCategoryModal(categoryId) {
        resetCategoryForm();
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('submitCategory').textContent = 'Update Category';
        document.getElementById('deleteCategoryBtn').classList.remove('hidden');

        showLoading('Loading category details...');

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/getCategory?id=${categoryId}`)
            .then(response => {
                if (response.status === 401) {
                    hideLoading();
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    const category = data.data;

                    document.getElementById('categoryId').value = category.id;
                    document.getElementById('name').value = category.name;
                    document.getElementById('description').value = category.description || '';
                    document.getElementById('meta_title').value = category.meta_title || '';
                    document.getElementById('meta_description').value = category.meta_description || '';

                    const statusToggle = document.getElementById('status-toggle');
                    const statusText = document.getElementById('status-text');
                    const statusInput = document.getElementById('status');

                    if (category.status === 'active') {
                        statusToggle.checked = true;
                        statusText.textContent = 'Active';
                        statusInput.value = 'active';
                    } else {
                        statusToggle.checked = false;
                        statusText.textContent = 'Inactive';
                        statusInput.value = 'inactive';
                    }

                    if (category.meta_keywords) {
                        keywordsList = category.meta_keywords.split(',').map(k => k.trim()).filter(k => k);
                        renderKeywords();
                    } else {
                        keywordsList = [];
                        renderKeywords();
                    }

                    if (category.image_url) {
                        document.getElementById('imagePreview').src = category.image_url;
                        document.getElementById('imagePreviewContainer').classList.remove('hidden');
                        document.getElementById('selectedFileName').textContent = 'Current image';
                        document.getElementById('removeImage').value = "0";
                    } else {
                        document.getElementById('imagePreviewContainer').classList.add('hidden');
                        document.getElementById('selectedFileName').textContent = 'No image selected';
                    }

                    document.getElementById('categoryModal').classList.remove('hidden');
                } else {
                    showErrorNotification(data.message || 'Failed to load category details');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading category details:', error);
                    showErrorNotification('Failed to load category details. Please try again.');
                }
            });
    }

    function hideCategoryModal() {
        document.getElementById('categoryModal').classList.add('hidden');
        resetCategoryForm();
    }

    function resetCategoryForm() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('tempImagePath').value = '';
        document.getElementById('removeImage').value = "0";
        document.getElementById('imagePreviewContainer').classList.add('hidden');
        document.getElementById('cropperContainer').classList.add('hidden');
        document.getElementById('selectedFileName').textContent = 'No file selected';

        const statusToggle = document.getElementById('status-toggle');
        const statusText = document.getElementById('status-text');
        const statusInput = document.getElementById('status');

        statusToggle.checked = true;
        statusText.textContent = 'Active';
        statusInput.value = 'active';

        keywordsList = [];
        renderKeywords();

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function renderKeywords() {
        const keywordsContainer = document.getElementById('keywords-container');
        const metaKeywordsInput = document.getElementById('meta_keywords');

        keywordsContainer.innerHTML = '';
        metaKeywordsInput.value = keywordsList.join(',');

        keywordsList.forEach((keyword, index) => {
            const keywordElement = document.createElement('div');
            keywordElement.className = 'inline-flex items-center bg-gray-100 rounded-full px-3 py-1 text-sm';
            keywordElement.innerHTML = `
                <span class="mr-1">${escapeHtml(keyword)}</span>
                <button type="button" class="text-gray-500 hover:text-gray-700" data-index="${index}">
                    <i class="fas fa-times-circle"></i>
                </button>
            `;

            keywordElement.querySelector('button').addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'));
                keywordsList.splice(idx, 1);
                renderKeywords();
            });

            keywordsContainer.appendChild(keywordElement);
        });
    }

    function submitCategoryForm() {
        const name = document.getElementById('name').value.trim();

        if (!name) {
            showErrorNotification('Category name is required');
            return;
        }

        const categoryId = document.getElementById('categoryId').value;

        const categoryData = {
            id: categoryId,
            name: name,
            description: document.getElementById('description').value.trim(),
            meta_title: document.getElementById('meta_title').value.trim(),
            meta_description: document.getElementById('meta_description').value.trim(),
            meta_keywords: document.getElementById('meta_keywords').value,
            temp_image_path: document.getElementById('tempImagePath').value,
            remove_image: document.getElementById('removeImage').value === "1",
            status: document.getElementById('status').value
        };

        const endpoint = categoryId ? 'updateCategory' : 'createCategory';
        const actionText = categoryId ? 'Updating' : 'Creating';

        showLoading(`${actionText} category...`);

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(categoryData)
        })
            .then(response => {
                if (response.status === 401) {
                    hideLoading();
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || (categoryId ? 'Category updated successfully!' : 'Category created successfully!'));
                    hideCategoryModal();
                    loadCategories();
                } else {
                    showErrorNotification(data.message || 'Failed to save category');
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error saving category:', error);
                    showErrorNotification('Failed to save category. Please try again.');
                }
            });
    }

    function loadCategories() {
        const tableBody = document.getElementById('categories-table-body');
        tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><div>Loading categories...</div></td></tr>';

        showLoading('Loading categories...');

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/getCategories`)
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        hideLoading();
                        showSessionExpiredModal();
                        throw new Error('Session expired');
                    }
                    throw new Error(`Server responded with status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    categoriesData = data.categories;
                    updateStatistics();
                    totalPages = Math.ceil(categoriesData.length / itemsPerPage);
                    renderPagination();
                    renderCategories(categoriesData);
                    renderCategoriesCards(categoriesData);
                } else {
                    showErrorNotification(data.message || 'Failed to load categories');
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-red-500">Error loading categories</td></tr>';
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading categories:', error);
                    showErrorNotification('Failed to load categories. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-red-500">Failed to load categories</td></tr>';
                }
            });
    }

    function updateStatistics() {
        const total = categoriesData.length;
        const active = categoriesData.filter(c => c.status === 'active').length;
        const featured = categoriesData.filter(c => c.featured).length;
        const inactive = categoriesData.filter(c => c.status === 'inactive').length;

        document.getElementById('totalCategories').textContent = total.toLocaleString();
        document.getElementById('activeCategories').textContent = active.toLocaleString();
        document.getElementById('featuredCategories').textContent = featured.toLocaleString();
        document.getElementById('inactiveCategories').textContent = inactive.toLocaleString();
    }

    function renderPagination() {
        const paginationContainer = document.getElementById('pagination-numbers');
        paginationContainer.innerHTML = '';

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }
        } else {
            paginationContainer.appendChild(createPaginationButton(1));

            if (currentPage > 3) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                paginationContainer.appendChild(createPaginationButton(i));
            }

            if (currentPage < totalPages - 2) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'px-2';
                ellipsis.textContent = '...';
                paginationContainer.appendChild(ellipsis);
            }

            if (totalPages > 1) {
                paginationContainer.appendChild(createPaginationButton(totalPages));
            }
        }
    }

    function createPaginationButton(pageNumber) {
        const button = document.createElement('button');
        button.className = pageNumber === currentPage ?
            'px-3 py-2 rounded-lg bg-primary text-white' :
            'px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50';
        button.textContent = pageNumber;

        button.addEventListener('click', function () {
            currentPage = pageNumber;
            renderPagination();
            renderCategories(categoriesData);
            renderCategoriesCards(categoriesData);
        });

        return button;
    }

    function renderCategories(categories) {
        const tableBody = document.getElementById('categories-table-body');
        tableBody.innerHTML = '';

        document.getElementById('category-count').textContent = categories.length;

        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, categories.length);

        document.getElementById('showing-start').textContent = categories.length > 0 ? start + 1 : 0;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-categories').textContent = categories.length;

        const paginatedCategories = categories.slice(start, end);

        if (paginatedCategories.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No categories found</div></td></tr>';
            return;
        }

        paginatedCategories.forEach((category, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 transition-colors cursor-pointer';
            row.onclick = () => showEditCategoryModal(category.id);

            const statusBadge = category.status === 'active' ?
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' :
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>';

            row.innerHTML = `
                <td class="px-4 py-3 text-center">
                    <button class="btn-feature" data-id="${category.id}" onclick="event.stopPropagation()">
                        ${category.featured
                    ? '<i class="fas fa-heart text-red-500"></i>'
                    : '<i class="far fa-heart text-gray-400"></i>'}
                    </button>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-900 break-words">${escapeHtml(category.name)}</div>
                    <div class="text-xs text-gray-500 mt-1">${category.description ? escapeHtml(truncateText(category.description, 50)) : ''}</div>
                    ${category.image_url ? '<div class="text-xs text-blue-500 mt-1"><i class="fas fa-image mr-1"></i>Has image</div>' : ''}
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-700">${category.product_count || 0}</td>
                <td class="px-4 py-3 text-center">${statusBadge}</td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">${formatDate(category.created_at)}</td>
            `;

            tableBody.appendChild(row);
        });

        document.querySelectorAll('.btn-feature').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                const category = categoriesData.find(c => c.id === categoryId);
                if (!category) return;

                const oldFeatured = category.featured;
                const newFeatured = oldFeatured ? 0 : 1;

                const icon = this.querySelector('i');
                if (newFeatured) {
                    icon.className = 'fas fa-heart text-red-500';
                } else {
                    icon.className = 'far fa-heart text-gray-400';
                }
                category.featured = newFeatured;

                fetch(`${BASE_URL}admin/fetch/manageProductCategories/updateFeatured`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: categoryId,
                        featured: newFeatured
                    })
                })
                    .then(response => {
                        if (response.status === 401) {
                            showSessionExpiredModal();
                            throw new Error('Session expired');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            category.featured = oldFeatured;
                            if (oldFeatured) {
                                icon.className = 'fas fa-heart text-red-500';
                            } else {
                                icon.className = 'far fa-heart text-gray-400';
                            }
                            showErrorNotification(data.message || 'Failed to update featured');
                        } else {
                            updateStatistics();
                            renderCategoriesCards(categoriesData);
                        }
                    })
                    .catch(err => {
                        if (err.message !== 'Session expired') {
                            category.featured = oldFeatured;
                            if (oldFeatured) {
                                icon.className = 'fas fa-heart text-red-500';
                            } else {
                                icon.className = 'far fa-heart text-gray-400';
                            }
                            showErrorNotification('Failed to update featured. Try again.');
                        }
                    });
            });
        });
    }

    function renderCategoriesCards(categories) {
        const cardsContainer = document.getElementById('categoriesCards');
        cardsContainer.innerHTML = '';

        const start = (currentPage - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, categories.length);

        document.getElementById('mobileShowingStart').textContent = categories.length > 0 ? start + 1 : 0;
        document.getElementById('mobileShowingEnd').textContent = end;
        document.getElementById('mobileTotalCategories').textContent = categories.length;
        document.getElementById('mobilePageInfo').textContent = `Page ${currentPage} of ${totalPages}`;

        const prevButton = document.getElementById('mobilePrevPage');
        const nextButton = document.getElementById('mobileNextPage');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        const paginatedCategories = categories.slice(start, end);

        if (paginatedCategories.length === 0) {
            cardsContainer.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="fas fa-box-open text-2xl mb-2"></i><div>No categories found</div></div>';
            return;
        }

        paginatedCategories.forEach(category => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-2xl shadow-sm border border-gray-200 p-4 mb-4 cursor-pointer';
            card.onclick = () => showEditCategoryModal(category.id);

            const statusBadge = category.status === 'active' ?
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' :
                '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>';

            card.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-secondary break-words">${escapeHtml(category.name)}</h3>
                    <button class="btn-feature" data-id="${category.id}" onclick="event.stopPropagation()">
                        ${category.featured
                    ? '<i class="fas fa-heart text-red-500"></i>'
                    : '<i class="far fa-heart text-gray-400"></i>'}
                    </button>
                </div>
                <p class="text-sm text-gray-600">${category.description ? escapeHtml(truncateText(category.description, 100)) : ''}</p>
                <div class="flex items-center justify-between mt-3">
                    <div class="text-sm text-gray-500">${formatDate(category.created_at)}</div>
                    <div>${statusBadge}</div>
                </div>
            `;

            cardsContainer.appendChild(card);
        });

        document.querySelectorAll('#categoriesCards .btn-feature').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                const category = categoriesData.find(c => c.id === categoryId);
                if (!category) return;

                const oldFeatured = category.featured;
                const newFeatured = oldFeatured ? 0 : 1;

                const icon = this.querySelector('i');
                if (newFeatured) {
                    icon.className = 'fas fa-heart text-red-500';
                } else {
                    icon.className = 'far fa-heart text-gray-400';
                }
                category.featured = newFeatured;

                fetch(`${BASE_URL}admin/fetch/manageProductCategories/updateFeatured`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: categoryId,
                        featured: newFeatured
                    })
                })
                    .then(response => {
                        if (response.status === 401) {
                            showSessionExpiredModal();
                            throw new Error('Session expired');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            category.featured = oldFeatured;
                            if (oldFeatured) {
                                icon.className = 'fas fa-heart text-red-500';
                            } else {
                                icon.className = 'far fa-heart text-gray-400';
                            }
                            showErrorNotification(data.message || 'Failed to update featured');
                        } else {
                            updateStatistics();
                            renderCategoriesCards(categoriesData);
                            renderCategories(categoriesData);
                        }
                    })
                    .catch(err => {
                        if (err.message !== 'Session expired') {
                            category.featured = oldFeatured;
                            if (oldFeatured) {
                                icon.className = 'fas fa-heart text-red-500';
                            } else {
                                icon.className = 'far fa-heart text-gray-400';
                            }
                            showErrorNotification('Failed to update featured. Try again.');
                        }
                    });
            });
        });
    }

    function filterCategories(query, status, featured) {
        let filteredCategories = categoriesData.filter(category => {
            const text = `${category.name} ${category.description || ''}`.toLowerCase();
            const matchesQuery = text.includes(query);
            const matchesStatus = !status || category.status === status;
            let matchesFeatured = true;

            if (featured === 'featured') {
                matchesFeatured = category.featured === 1;
            } else if (featured === 'not-featured') {
                matchesFeatured = category.featured === 0;
            }

            return matchesQuery && matchesStatus && matchesFeatured;
        });

        currentPage = 1;
        totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
        renderPagination();
        renderCategories(filteredCategories);
        renderCategoriesCards(filteredCategories);
    }

    function showDeleteModal(categoryId) {
        const category = categoriesData.find(c => c.id === categoryId);

        if (category) {
            document.getElementById('delete-category-name').textContent = category.name;
            document.getElementById('delete-category-status').textContent = category.status.charAt(0).toUpperCase() + category.status.slice(1);
            document.getElementById('confirmDelete').setAttribute('data-id', categoryId);

            document.getElementById('deleteModal').classList.remove('hidden');
        }
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function confirmDelete() {
        const categoryId = document.getElementById('confirmDelete').getAttribute('data-id');

        showLoading('Deleting category...');

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/deleteCategory`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: categoryId
            })
        })
            .then(response => {
                if (response.status === 401) {
                    hideLoading();
                    showSessionExpiredModal();
                    throw new Error('Session expired');
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(data.message || 'Category deleted successfully!');
                    loadCategories();
                } else {
                    showErrorNotification(data.message || 'Failed to delete category');
                }
                hideDeleteModal();
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error deleting category:', error);
                    showErrorNotification('Failed to delete category. Please try again.');
                    hideDeleteModal();
                }
            });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function showSessionExpiredModal() {
        const modal = document.getElementById('sessionExpiredModal');
        modal.classList.remove('hidden');
    }

    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageEl = document.getElementById('successMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>