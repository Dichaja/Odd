<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Products';
$activeNav = 'manage-products';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Manage Products</h1>
            <p class="text-sm text-gray-text mt-1">View, edit and manage all products in your inventory</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="addNewProduct" class="h-10 px-4 bg-success bg-primary text-white rounded-lg hover:bg-success/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add New</span>
            </button>
            <a href="product-package" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-box"></i>
                <span>Package Definition</span>
            </a>
        </div>
    </div>

    <!-- Products Filter Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Product List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="product-count">300</span> products found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchProducts" placeholder="Search products..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label for="sortProducts" class="text-sm text-gray-700 whitespace-nowrap">Sort By:</label>
                    <select id="sortProducts" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>Select</option>
                        <option value="latest">Latest</option>
                        <option value="verify">Verified</option>
                        <option value="pending">Pending</option>
                        <option value="usr">User Entries</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div id="filterPanel" class="px-6 py-4 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="filterCategory" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Categories</option>
                        <option value="Tiles & Accessories">Tiles & Accessories</option>
                        <option value="Earth Materials">Earth Materials</option>
                        <option value="Electrical Supplies">Electrical Supplies</option>
                        <option value="TIMBER Supplies">TIMBER Supplies</option>
                        <option value="Roofing Materials">Roofing Materials</option>
                        <option value="STOTA Products">STOTA Products</option>
                        <option value="Solar implements">Solar implements</option>
                        <option value="Paints & Binders">Paints & Binders</option>
                        <option value="Hardware Materials">Hardware Materials</option>
                        <option value="Transport for Hire">Transport for Hire</option>
                    </select>
                </div>
                <div>
                    <label for="filterPackaging" class="block text-sm font-medium text-gray-700 mb-1">Packaging</label>
                    <select id="filterPackaging" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Packaging</option>
                        <option value="Carton">Carton</option>
                        <option value="Piece">Piece</option>
                        <option value="Unit">Unit</option>
                        <option value="Bag">Bag</option>
                        <option value="Roll">Roll</option>
                        <option value="Trip">Trip</option>
                        <option value="Set">Set</option>
                        <option value="Unit Lease">Unit Lease</option>
                    </select>
                </div>
                <div>
                    <label for="filterFeatured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                    <select id="filterFeatured" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Products</option>
                        <option value="featured">Featured Only</option>
                        <option value="not-featured">Not Featured</option>
                    </select>
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button id="resetFilters" class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                        Reset Filters
                    </button>
                    <button id="applyFilters" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6" id="products-container">
                <!-- Product items will be populated here -->

                <!-- Product Item 1 -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 p-4">
                            <div class="product-image-slider relative rounded-lg overflow-hidden">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=GG44548+Tile" alt="GG44548 40x40 Floor Tile" class="w-full h-64 object-cover">
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Tile+Side+View" alt="Tile Side View" class="w-full h-64 object-cover">
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Tile+Installation" alt="Tile Installation" class="w-full h-64 object-cover">
                                        </div>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        </div>
                        <div class="md:w-2/3 p-4">
                            <div class="flex justify-between items-start">
                                <div class="category-badge">
                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer">
                                        Tiles & Accessories
                                    </span>
                                </div>
                                <div class="publish-status">
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                        Published
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mt-2">GG44548 40x40 Floor Tile</h3>

                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-gray-700">Description:</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    GOODWILL 40cmx40cm floor tile available in a 12pc carton covering 1.92sm floor space
                                </p>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-3">
                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Activity Usage:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">2</span>
                                </div>

                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Packaging:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">Carton</span>
                                </div>

                                <div class="feature-badge px-3 py-1.5 rounded-lg border border-gray-200">
                                    Feature
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5" data-id="609162">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button class="btn-delete px-4 py-2 bg-white border border-danger text-danger rounded-lg hover:bg-danger/5" data-id="609162">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Item 2 -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 p-4">
                            <div class="product-image-slider relative rounded-lg overflow-hidden">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=GS44506+Tile" alt="GS44506 40x40 Floor Tile" class="w-full h-64 object-cover">
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Glossy+Tile" alt="Glossy Tile" class="w-full h-64 object-cover">
                                        </div>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        </div>
                        <div class="md:w-2/3 p-4">
                            <div class="flex justify-between items-start">
                                <div class="category-badge">
                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer">
                                        Tiles & Accessories
                                    </span>
                                </div>
                                <div class="publish-status">
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                        Published
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mt-2">GS44506 40x40 Floor Tile</h3>

                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-gray-700">Description:</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    GOODWILL 40cmx40cm Glossy Floor Tile Available In A 12pc Carton Covering 1.92sm Floor Space
                                </p>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-3">
                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Activity Usage:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">1</span>
                                </div>

                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Packaging:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">Carton</span>
                                </div>

                                <div class="feature-badge px-3 py-1.5 rounded-lg border border-gray-200">
                                    Feature
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5" data-id="787105">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button class="btn-delete px-4 py-2 bg-white border border-danger text-danger rounded-lg hover:bg-danger/5" data-id="787105">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Item 3 (Featured) -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-1/3 p-4">
                            <div class="product-image-slider relative rounded-lg overflow-hidden">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Solar+Garden+Light" alt="SOLAR GARDEN LIGHT ZC-CPD5004" class="w-full h-64 object-cover">
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Solar+Light+Night" alt="Solar Light at Night" class="w-full h-64 object-cover">
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://placehold.co/600x400?text=Solar+Light+Components" alt="Solar Light Components" class="w-full h-64 object-cover">
                                        </div>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        </div>
                        <div class="md:w-2/3 p-4">
                            <div class="flex justify-between items-start">
                                <div class="category-badge">
                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full cursor-pointer">
                                        STOTA Products
                                    </span>
                                </div>
                                <div class="publish-status">
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                        Published
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mt-2">SOLAR GARDEN LIGHT ZC-CPD5004</h3>

                            <div class="mt-3">
                                <h4 class="text-sm font-medium text-gray-700">Description:</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    Stota SOLAR GARDEN LIGHT ZC-CPD5004 fitted with Solar panel: Monocrystalline (5v/5W) with the following Specs; Battery: Lithium iron phosphate 3.2v/8AH, Material: PC+Alluminium, IP Rating: IP65
                                </p>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-3">
                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Activity Usage:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">3</span>
                                </div>

                                <div class="bg-gray-50 px-3 py-1.5 rounded-lg">
                                    <span class="text-xs text-gray-500">Packaging:</span>
                                    <span class="text-sm font-medium text-gray-700 ml-1">Unit</span>
                                </div>

                                <div class="feature-badge featured px-3 py-1.5 rounded-lg bg-gray-300 text-gray-700">
                                    Featured
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5" data-id="05-3091563">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button class="btn-delete px-4 py-2 bg-white border border-danger text-danger rounded-lg hover:bg-danger/5" data-id="05-3091563">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-500 mb-4 md:mb-0">
                    Showing <span id="showing-start">1</span> to <span id="showing-end">20</span> of <span id="total-products">300</span> products
                </div>
                <div class="flex items-center gap-2">
                    <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="pagination-numbers" class="flex items-center">
                        <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">3</button>
                        <span class="px-2">...</span>
                        <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">15</button>
                    </div>
                    <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <a href="?limit=300" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 ml-2">
                        View All
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideProductModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="modal-title">Add New Product</h3>
            <button onclick="hideProductModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="productForm" class="space-y-6">
                <input type="hidden" id="product-id" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product-title" class="block text-sm font-medium text-gray-700 mb-1">Product Title</label>
                        <input type="text" id="product-title" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter product title">
                    </div>

                    <div>
                        <label for="product-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select id="product-category" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Select Category</option>
                            <option value="Tiles & Accessories">Tiles & Accessories</option>
                            <option value="Earth Materials">Earth Materials</option>
                            <option value="Electrical Supplies">Electrical Supplies</option>
                            <option value="TIMBER Supplies">TIMBER Supplies</option>
                            <option value="Roofing Materials">Roofing Materials</option>
                            <option value="STOTA Products">STOTA Products</option>
                            <option value="Solar implements">Solar implements</option>
                            <option value="Paints & Binders">Paints & Binders</option>
                            <option value="Hardware Materials">Hardware Materials</option>
                            <option value="Transport for Hire">Transport for Hire</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="product-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="product-description" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter product description"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="product-packaging" class="block text-sm font-medium text-gray-700 mb-1">Packaging</label>
                        <select id="product-packaging" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Select Packaging</option>
                            <option value="Carton">Carton</option>
                            <option value="Piece">Piece</option>
                            <option value="Unit">Unit</option>
                            <option value="Bag">Bag</option>
                            <option value="Roll">Roll</option>
                            <option value="Trip">Trip</option>
                            <option value="Set">Set</option>
                            <option value="Unit Lease">Unit Lease</option>
                        </select>
                    </div>

                    <div>
                        <label for="product-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="product-status" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="published">Published</option>
                            <option value="pending">Pending</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>

                    <div>
                        <label for="product-featured" class="block text-sm font-medium text-gray-700 mb-1">Featured</label>
                        <div class="flex items-center h-10">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="product-featured" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700">Mark as featured</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Images</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="product-images-container">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:bg-gray-50 transition-colors" id="add-image-btn">
                            <div class="flex flex-col items-center justify-center h-32">
                                <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                                <span class="text-sm text-gray-500">Add Image</span>
                            </div>
                        </div>

                        <!-- Image preview items will be added here -->
                        <div class="relative border border-gray-200 rounded-lg overflow-hidden image-preview-item">
                            <img src="https://placehold.co/600x400?text=Product+Image" class="w-full h-32 object-cover">
                            <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center remove-image-btn">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" id="image-upload" class="hidden" accept="image/*" multiple>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideProductModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="saveProduct" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Product
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Product</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to delete this product? This action cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-1 gap-2 text-sm">
                    <div class="text-gray-500">Product:</div>
                    <div class="font-medium text-gray-900" id="delete-product-name"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-danger/90">
                Delete
            </button>
        </div>
    </div>
</div>

<style>
    .feature-badge.featured {
        background-color: #ccc;
        color: #fff;
    }

    .swiper-container {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        text-align: center;
        background: #f8f8f8;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #fff;
        background: rgba(0, 0, 0, 0.3);
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper-button-next:after,
    .swiper-button-prev:after {
        font-size: 14px;
    }

    .swiper-pagination-bullet {
        background: #fff;
        opacity: 0.7;
    }

    .swiper-pagination-bullet-active {
        opacity: 1;
        background: #fff;
    }
</style>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

<script>
    // Sample product data - in a real application, this would come from an API
    const products = [{
            id: "609162",
            title: "GG44548 40x40 Floor Tile",
            category: "Tiles & Accessories",
            description: "GOODWILL 40cmx40cm floor tile available in a 12pc carton covering 1.92sm floor space",
            packaging: "Carton",
            status: "published",
            featured: false,
            activityUsage: 2,
            images: [
                "https://placehold.co/600x400?text=GG44548+Tile",
                "https://placehold.co/600x400?text=Tile+Side+View",
                "https://placehold.co/600x400?text=Tile+Installation"
            ]
        },
        {
            id: "787105",
            title: "GS44506 40x40 Floor Tile",
            category: "Tiles & Accessories",
            description: "GOODWILL 40cmx40cm Glossy Floor Tile Available In A 12pc Carton Covering 1.92sm Floor Space",
            packaging: "Carton",
            status: "published",
            featured: false,
            activityUsage: 1,
            images: [
                "https://placehold.co/600x400?text=GS44506+Tile",
                "https://placehold.co/600x400?text=Glossy+Tile"
            ]
        },
        {
            id: "05-3091563",
            title: "SOLAR GARDEN LIGHT ZC-CPD5004",
            category: "STOTA Products",
            description: "Stota SOLAR GARDEN LIGHT ZC-CPD5004 fitted with Solar panel: Monocrystalline (5v/5W) with the following Specs; Battery: Lithium iron phosphate 3.2v/8AH, Material: PC+Alluminium, IP Rating: IP65",
            packaging: "Unit",
            status: "published",
            featured: true,
            activityUsage: 3,
            images: [
                "https://placehold.co/600x400?text=Solar+Garden+Light",
                "https://placehold.co/600x400?text=Solar+Light+Night",
                "https://placehold.co/600x400?text=Solar+Light+Components"
            ]
        }
    ];

    // Initialize Swiper sliders
    function initSwipers() {
        document.querySelectorAll('.swiper-container').forEach((swiperContainer, index) => {
            new Swiper(swiperContainer, {
                loop: true,
                pagination: {
                    el: swiperContainer.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: swiperContainer.querySelector('.swiper-button-next'),
                    prevEl: swiperContainer.querySelector('.swiper-button-prev')
                }
            });
        });
    }

    // Show product modal
    function showProductModal(productId = null) {
        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modal-title');
        const form = document.getElementById('productForm');

        // Reset form
        form.reset();
        document.getElementById('product-id').value = '';

        if (productId) {
            // Edit mode
            modalTitle.textContent = 'Edit Product';
            const product = products.find(p => p.id === productId);

            if (product) {
                document.getElementById('product-id').value = product.id;
                document.getElementById('product-title').value = product.title;
                document.getElementById('product-category').value = product.category;
                document.getElementById('product-description').value = product.description;
                document.getElementById('product-packaging').value = product.packaging;
                document.getElementById('product-status').value = product.status;
                document.getElementById('product-featured').checked = product.featured;

                // TODO: Handle image previews for edit mode
            }
        } else {
            // Add mode
            modalTitle.textContent = 'Add New Product';
        }

        modal.classList.remove('hidden');
    }

    // Hide product modal
    function hideProductModal() {
        const modal = document.getElementById('productModal');
        modal.classList.add('hidden');
    }

    // Show delete confirmation modal
    function showDeleteModal(productId) {
        const modal = document.getElementById('deleteModal');
        const product = products.find(p => p.id === productId);

        if (product) {
            document.getElementById('delete-product-name').textContent = product.title;
            document.getElementById('confirmDelete').setAttribute('data-id', productId);
            modal.classList.remove('hidden');
        }
    }

    // Hide delete confirmation modal
    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Swiper sliders
        initSwipers();

        // Add New Product button
        document.getElementById('addNewProduct').addEventListener('click', function() {
            showProductModal();
        });

        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                showProductModal(productId);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                showDeleteModal(productId);
            });
        });

        // Save product
        document.getElementById('saveProduct').addEventListener('click', function() {
            // In a real application, you would send form data to the server
            alert('Product saved successfully!');
            hideProductModal();
        });

        // Confirm delete
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            // In a real application, you would send a delete request to the server
            alert(`Product ${productId} would be deleted here`);
            hideDeleteModal();
        });

        // Add image button
        document.getElementById('add-image-btn').addEventListener('click', function() {
            document.getElementById('image-upload').click();
        });

        // Image upload
        document.getElementById('image-upload').addEventListener('change', function(e) {
            const files = e.target.files;

            if (files.length > 0) {
                // In a real application, you would upload the files to the server
                // For demo purposes, we'll just show a preview
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const container = document.getElementById('product-images-container');
                        const addBtn = document.getElementById('add-image-btn');

                        const previewItem = document.createElement('div');
                        previewItem.className = 'relative border border-gray-200 rounded-lg overflow-hidden image-preview-item';
                        previewItem.innerHTML = `
              <img src="${e.target.result}" class="w-full h-32 object-cover">
              <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center remove-image-btn">
                <i class="fas fa-times text-xs"></i>
              </button>
            `;

                        container.insertBefore(previewItem, addBtn.nextSibling);

                        // Add remove button event listener
                        previewItem.querySelector('.remove-image-btn').addEventListener('click', function() {
                            previewItem.remove();
                        });
                    };

                    reader.readAsDataURL(file);
                }
            }
        });

        // Remove image buttons
        document.querySelectorAll('.remove-image-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.image-preview-item').remove();
            });
        });

        // Filter toggle
        document.getElementById('applyFilters').addEventListener('click', function() {
            // In a real application, you would filter products based on selected criteria
            alert('Filters would be applied here');
        });

        // Reset filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterCategory').value = '';
            document.getElementById('filterPackaging').value = '';
            document.getElementById('filterFeatured').value = '';

            // In a real application, you would reset the product list
            alert('Filters would be reset here');
        });

        // Search products
        document.getElementById('searchProducts').addEventListener('input', function() {
            const query = this.value.trim();

            // In a real application, you would search products based on the query
            if (query.length > 0) {
                console.log(`Searching for: ${query}`);
            }
        });

        // Sort products
        document.getElementById('sortProducts').addEventListener('change', function() {
            const sortBy = this.value;

            // In a real application, you would sort products based on the selected option
            if (sortBy) {
                console.log(`Sorting by: ${sortBy}`);
            }
        });

        // Pagination
        document.querySelectorAll('#pagination-numbers button').forEach(button => {
            button.addEventListener('click', function() {
                // In a real application, you would navigate to the selected page
                console.log(`Navigating to page: ${this.textContent}`);
            });
        });

        document.getElementById('prev-page').addEventListener('click', function() {
            // In a real application, you would navigate to the previous page
            console.log('Navigating to previous page');
        });

        document.getElementById('next-page').addEventListener('click', function() {
            // In a real application, you would navigate to the next page
            console.log('Navigating to next page');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>