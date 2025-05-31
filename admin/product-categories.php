<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Product Categories';
$activeNav = 'product-categories';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['logged_in']) || !$_SESSION['user']['logged_in'] || !isset($_SESSION['user']['is_admin']) || !$_SESSION['user']['is_admin']) {
    header('Location: ' . BASE_URL . 'login/login.php');
    exit;
}

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Product Categories</h1>
            <p class="text-sm text-gray-text mt-1">Manage product categories and their metadata</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="addCategoryBtn"
                class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add Category</span>
            </button>
            <a href="products"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Products</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Product Categories</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="category-count">0</span> categories found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchCategories" placeholder="Search categories..."
                        class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus"
                        class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="categories-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">#</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Name</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Created</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center">Loading categories...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">0</span> to <span id="showing-end">0</span> of <span
                    id="total-categories">0</span> categories
            </div>
            <div class="flex items-center gap-2"> categories
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                </div>
                <button id="next-page"
                    class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideCategoryModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="modalTitle">Add New Category</h3>
            <button onclick="hideCategoryModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="categoryForm" class="space-y-6">
                <input type="hidden" id="categoryId" name="categoryId" value="">
                <input type="hidden" id="tempImagePath" name="tempImagePath" value="">
                <input type="hidden" id="removeImage" name="removeImage" value="0">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter category name" required>
                    </div>

                    <div>
                        <label for="status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center space-x-3">
                            <div
                                class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                <input type="checkbox" id="status-toggle"
                                    class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    checked>
                                <label for="status-toggle"
                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                            </div>
                            <span id="status-text" class="text-sm font-medium text-gray-700">Active</span>
                            <input type="hidden" id="status" name="status" value="active">
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter category description"></textarea>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">SEO Metadata</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta
                                Title</label>
                            <input type="text" id="meta_title" name="meta_title"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter meta title">
                        </div>

                        <div>
                            <label for="keywords-input" class="block text-sm font-medium text-gray-700 mb-1">Meta
                                Keywords</label>
                            <div class="relative">
                                <input type="text" id="keywords-input"
                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
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
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
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
                                    <img id="cropperImage" src="" alt="Image to crop" class="max-w-full">
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
                                    <img id="imagePreview" src="" alt="Category image preview"
                                        class="w-full h-full object-cover">
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
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCategoryModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitCategory"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Category
            </button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Category</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
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
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired due to inactivity.</p>
                <p class="text-sm text-gray-500 mt-1">Redirecting in <span id="countdown">10</span> seconds...</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Login Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notifications -->
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

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[1000] hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin mb-4"></div>
            <p id="loadingMessage" class="text-gray-700 font-medium text-center">Loading...</p>
        </div>
    </div>
</div>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let categoriesData = [];
    let currentPage = 1;
    let totalPages = 1;
    let itemsPerPage = 10;
    let cropper = null;
    let keywordsList = [];

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize event listeners
        initializeEventListeners();

        // Load categories
        loadCategories();

        // Initialize keywords input
        initializeKeywordsInput();

        // Initialize status toggle
        initializeStatusToggle();
    });

    // Loading overlay functions
    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function initializeEventListeners() {
        // Add category button
        document.getElementById('addCategoryBtn').addEventListener('click', showAddCategoryModal);

        // Submit category button
        document.getElementById('submitCategory').addEventListener('click', submitCategoryForm);

        // Image upload and cropping
        const imageInput = document.getElementById('image');
        imageInput.addEventListener('change', handleImageUpload);

        document.getElementById('cancelCrop').addEventListener('click', cancelCrop);
        document.getElementById('applyCrop').addEventListener('click', applyCrop);

        // Remove image button (renamed to removeImageBtn)
        document.getElementById('removeImageBtn').addEventListener('click', function () {
            // Set the hidden removeImage input value to "1"
            document.getElementById('removeImage').value = "1";
            // Clear any temporary image path
            document.getElementById('tempImagePath').value = '';
            // Hide preview container
            document.getElementById('imagePreviewContainer').classList.add('hidden');
            // Reset the file input
            document.getElementById('image').value = '';
            // Update filename indicator
            document.getElementById('selectedFileName').textContent = 'Image will be removed';
        });

        // Search and filter
        document.getElementById('searchCategories').addEventListener('input', function (e) {
            const query = e.target.value.toLowerCase();
            filterCategories(query, document.getElementById('filterStatus').value);
        });

        document.getElementById('filterStatus').addEventListener('change', function (e) {
            const status = e.target.value;
            filterCategories(document.getElementById('searchCategories').value.toLowerCase(), status);
        });

        // Pagination
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

        // Delete category
        document.getElementById('confirmDelete').addEventListener('click', confirmDelete);
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

        // Reset remove flag since we're uploading a new image
        document.getElementById('removeImage').value = "0";
        document.getElementById('tempImagePath').value = '';

        // Check file type and size
        const fileType = file.type;
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!validTypes.includes(fileType)) {
            showErrorNotification('Invalid file type. Only JPG, PNG, WebP, and GIF files are allowed.');
            resetImageUpload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) { // 5MB
            showErrorNotification('File size too large. Maximum 5MB allowed.');
            resetImageUpload();
            return;
        }

        // Initialize cropper
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

        // Display the cropped image
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const cropperContainer = document.getElementById('cropperContainer');

        imagePreview.src = canvas.toDataURL();
        imagePreviewContainer.classList.remove('hidden');
        cropperContainer.classList.add('hidden');

        // Convert to blob and upload
        canvas.toBlob(function (blob) {
            uploadCroppedImage(blob);
        }, 'image/jpeg', 0.9);

        // Destroy cropper
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
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function showEditCategoryModal(categoryId) {
        resetCategoryForm();
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('submitCategory').textContent = 'Update Category';

        // Fetch category details
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

                    // Set status
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

                    // Set keywords
                    if (category.meta_keywords) {
                        keywordsList = category.meta_keywords.split(',').map(k => k.trim()).filter(k => k);
                        renderKeywords();
                    } else {
                        keywordsList = [];
                        renderKeywords();
                    }

                    // Set image
                    if (category.image_url) {
                        document.getElementById('imagePreview').src = category.image_url;
                        document.getElementById('imagePreviewContainer').classList.remove('hidden');
                        document.getElementById('selectedFileName').textContent = 'Current image';
                        document.getElementById('removeImage').value = "0"; // Reset remove flag
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

        // Reset status toggle
        const statusToggle = document.getElementById('status-toggle');
        const statusText = document.getElementById('status-text');
        const statusInput = document.getElementById('status');

        statusToggle.checked = true;
        statusText.textContent = 'Active';
        statusInput.value = 'active';

        // Reset keywords
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
        tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">Loading categories...</td></tr>';

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
                    totalPages = Math.ceil(categoriesData.length / itemsPerPage);
                    renderPagination();
                    renderCategories(categoriesData);
                } else {
                    showErrorNotification(data.message || 'Failed to load categories');
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error loading categories</td></tr>';
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error loading categories:', error);
                    showErrorNotification('Failed to load categories. Please try again.');
                    tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Failed to load categories</td></tr>';
                }
            });
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
            tableBody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">No categories found</td></tr>';
            return;
        }

        paginatedCategories.forEach((category, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100 hover:bg-gray-50 transition-colors';

            const statusToggle = `
                <div class="flex items-center">
                    <div class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                        <input type="checkbox" class="status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary" data-id="${category.id}" ${category.status === 'active' ? 'checked' : ''}>
                        <label class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                    </div>
                    <span class="ml-2 text-xs font-medium ${category.status === 'active' ? 'text-green-600' : 'text-gray-500'}">${category.status === 'active' ? 'Active' : 'Inactive'}</span>
                </div>
            `;

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${start + index + 1}</td>
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">${escapeHtml(category.name)}</div>
                    <div class="text-xs text-gray-500 mt-1">${category.description ? escapeHtml(truncateText(category.description, 50)) : ''}</div>
                    ${category.image_url ? '<div class="text-xs text-blue-500 mt-1"><i class="fas fa-image mr-1"></i>Has image</div>' : ''}
                </td>
                <td class="px-6 py-4">${statusToggle}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${formatDate(category.created_at)}</td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center gap-2">
                        <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="${category.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete text-red-600 hover:text-red-800" data-id="${category.id}" title="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            `;

            tableBody.appendChild(row);
        });

        // Add event listeners to buttons
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showEditCategoryModal(categoryId);
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function () {
                const categoryId = this.getAttribute('data-id');
                showDeleteModal(categoryId);
            });
        });

        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const categoryId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                updateCategoryStatus(categoryId, newStatus);
            });
        });
    }

    function updateCategoryStatus(categoryId, status) {
        const category = categoriesData.find(c => c.id === categoryId);
        if (!category) return;

        showLoading('Updating category status...');

        const categoryData = {
            id: categoryId,
            name: category.name,
            description: category.description || '',
            meta_title: category.meta_title || '',
            meta_description: category.meta_description || '',
            meta_keywords: category.meta_keywords || '',
            status: status
        };

        fetch(`${BASE_URL}admin/fetch/manageProductCategories/updateCategory`, {
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
                    showSuccessNotification(`Category status updated to ${status}`);
                    loadCategories();
                } else {
                    showErrorNotification(data.message || 'Failed to update category status');
                    loadCategories(); // Reload to reset UI
                }
            })
            .catch(error => {
                hideLoading();
                if (error.message !== 'Session expired') {
                    console.error('Error updating category status:', error);
                    showErrorNotification('Failed to update category status. Please try again.');
                    loadCategories(); // Reload to reset UI
                }
            });
    }

    function filterCategories(query, status) {
        const filteredCategories = categoriesData.filter(category => {
            const text = `${category.name} ${category.description || ''}`.toLowerCase();
            const matchesQuery = text.includes(query);
            const matchesStatus = !status || category.status === status;
            return matchesQuery && matchesStatus;
        });

        currentPage = 1;
        totalPages = Math.ceil(filteredCategories.length / itemsPerPage);
        renderPagination();
        renderCategories(filteredCategories);
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

        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        countdownElement.textContent = countdown;

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
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