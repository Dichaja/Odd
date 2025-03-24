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

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="products-container">
                <!-- Product Item 1 -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative bg-gray-100 h-64">
                        <div class="product-image-slider h-full">
                            <div class="swiper-container h-full">
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
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev custom-nav-btn"></div>
                            <div class="swiper-button-next custom-nav-btn"></div>
                        </div>
                        <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-primary transition-colors z-10 toggle-featured" data-id="609162" data-featured="false">
                            <i class="far fa-heart text-lg"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">0.0 (0)</span>
                        </div>

                        <h3 class="text-xl font-semibold mb-2">GG44548 40x40 Floor Tile</h3>

                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="fas fa-tag mr-2"></i>
                            <span>Tiles & Accessories</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-box mr-2"></i>
                                <span>Carton</span>
                            </div>
                            <div class="text-xl font-bold text-primary">USh 120,000</div>
                        </div>

                        <div class="flex justify-between">
                            <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center" data-id="609162">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="btn-details px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center">
                                <i class="fas fa-home mr-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Item 2 -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative bg-gray-100 h-64">
                        <div class="product-image-slider h-full">
                            <div class="swiper-container h-full">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="https://placehold.co/600x400?text=GS44506+Tile" alt="GS44506 40x40 Floor Tile" class="w-full h-64 object-cover">
                                    </div>
                                    <div class="swiper-slide">
                                        <img src="https://placehold.co/600x400?text=Glossy+Tile" alt="Glossy Tile" class="w-full h-64 object-cover">
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev custom-nav-btn"></div>
                            <div class="swiper-button-next custom-nav-btn"></div>
                        </div>
                        <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-primary transition-colors z-10 toggle-featured" data-id="787105" data-featured="false">
                            <i class="far fa-heart text-lg"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">0.0 (0)</span>
                        </div>

                        <h3 class="text-xl font-semibold mb-2">GS44506 40x40 Floor Tile</h3>

                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="fas fa-tag mr-2"></i>
                            <span>Tiles & Accessories</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-box mr-2"></i>
                                <span>Carton</span>
                            </div>
                            <div class="text-xl font-bold text-primary">USh 135,000</div>
                        </div>

                        <div class="flex justify-between">
                            <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center" data-id="787105">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="btn-details px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center">
                                <i class="fas fa-home mr-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Item 3 (Featured) -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative bg-gray-100 h-64">
                        <div class="product-image-slider h-full">
                            <div class="swiper-container h-full">
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
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev custom-nav-btn"></div>
                            <div class="swiper-button-next custom-nav-btn"></div>
                        </div>
                        <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center text-red-500 hover:text-red-600 transition-colors z-10 toggle-featured" data-id="05-3091563" data-featured="true">
                            <i class="fas fa-heart text-lg"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">0.0 (0)</span>
                        </div>

                        <h3 class="text-xl font-semibold mb-2">SOLAR GARDEN LIGHT ZC-CPD5004</h3>

                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="fas fa-tag mr-2"></i>
                            <span>STOTA Products</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-box mr-2"></i>
                                <span>Unit</span>
                            </div>
                            <div class="text-xl font-bold text-primary">USh 250,000</div>
                        </div>

                        <div class="flex justify-between">
                            <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center" data-id="05-3091563">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="btn-details px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center">
                                <i class="fas fa-home mr-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Item 4 -->
                <div class="product-item bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="relative bg-gray-100 h-64">
                        <div class="product-image-slider h-full">
                            <div class="swiper-container h-full">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <img src="https://placehold.co/600x400?text=Cement" alt="Portland Cement" class="w-full h-64 object-cover">
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-pagination"></div>
                            <div class="swiper-button-prev custom-nav-btn"></div>
                            <div class="swiper-button-next custom-nav-btn"></div>
                        </div>
                        <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-primary transition-colors z-10 toggle-featured" data-id="45-2091" data-featured="false">
                            <i class="far fa-heart text-lg"></i>
                        </button>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center mb-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm text-gray-500 ml-2">0.0 (0)</span>
                        </div>

                        <h3 class="text-xl font-semibold mb-2">Portland Cement</h3>

                        <div class="flex items-center text-gray-500 mb-2">
                            <i class="fas fa-tag mr-2"></i>
                            <span>Earth Materials</span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-box mr-2"></i>
                                <span>Bag</span>
                            </div>
                            <div class="text-xl font-bold text-primary">USh 32,000</div>
                        </div>

                        <div class="flex justify-between">
                            <button class="btn-edit px-4 py-2 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 flex items-center" data-id="45-2091">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button class="btn-details px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 flex items-center">
                                <i class="fas fa-home mr-1"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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

<div id="productModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideProductModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="product-meta-title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title (SEO)</label>
                        <input type="text" id="product-meta-title" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter SEO meta title">
                        <p class="text-xs text-gray-500 mt-1">Recommended length: 50-60 characters</p>
                    </div>
                    <div>
                        <label for="product-meta-description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description (SEO)</label>
                        <textarea id="product-meta-description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter SEO meta description"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Recommended length: 150-160 characters</p>
                    </div>
                </div>

                <div>
                    <label for="product-keywords" class="block text-sm font-medium text-gray-700 mb-1">SEO Keywords</label>
                    <div class="relative">
                        <input type="text" id="product-keywords-input" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Type keyword and press Enter">
                        <button type="button" id="add-keyword" class="absolute right-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-primary text-white rounded text-xs">Add</button>
                    </div>
                    <div id="keywords-container" class="flex flex-wrap gap-2 mt-2"></div>
                    <input type="hidden" id="product-keywords" value="">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Product Images (16:9 ratio)</label>
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-gray-600">Upload and arrange product images. Drag to reorder.</p>
                            <button type="button" id="add-image-btn" class="px-3 py-1 bg-primary text-white rounded-lg text-sm">
                                <i class="fas fa-plus mr-1"></i> Add Image
                            </button>
                        </div>
                        <div id="product-images-container" class="grid grid-cols-1 md:grid-cols-4 gap-4 sortable-images">
                            <div class="image-preview-item relative border border-gray-200 rounded-lg overflow-hidden">
                                <img src="https://placehold.co/600x400?text=Product+Image" class="w-full h-32 object-cover">
                                <div class="absolute top-0 right-0 p-2 flex gap-2">
                                    <button type="button" class="bg-primary text-white rounded-full w-6 h-6 flex items-center justify-center edit-image-btn">
                                        <i class="fas fa-crop-alt text-xs"></i>
                                    </button>
                                    <button type="button" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center remove-image-btn">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-1 text-center">
                                    <span class="image-order">1</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="file" id="image-upload" class="hidden" accept="image/*">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Product Packaging & Pricing</label>
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div id="product-packages" class="space-y-4">
                            <div class="package-item grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Package Type</label>
                                    <select class="package-type w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                        <option value="">Select Package</option>
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
                                    <label class="block text-xs text-gray-500 mb-1">Price (USh)</label>
                                    <input type="number" class="package-price w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter price">
                                </div>
                                <div class="flex items-end">
                                    <button type="button" class="remove-package px-3 py-2 text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-package" class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Another Package
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
            <button id="confirmDelete" class="px-4 py-2 bg-danger text-white rounded-lg hover:bg-danger/90">
                Delete
            </button>
        </div>
    </div>
</div>

<div id="cropperModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="hideCropperModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Crop Image (16:9)</h3>
            <button onclick="hideCropperModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <div id="image-cropper-container" class="max-h-[60vh] overflow-hidden">
                    <img id="image-to-crop" src="/placeholder.svg" alt="Image to crop">
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCropperModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="cropImage" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Crop & Save
            </button>
        </div>
    </div>
</div>

<style>
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

    .custom-nav-btn {
        width: 40px !important;
        height: 40px !important;
        background: #2196F3 !important;
        border-radius: 50% !important;
        color: white !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
        opacity: 0.9 !important;
        transition: all 0.3s ease !important;
    }

    .custom-nav-btn:hover {
        background: #1976D2 !important;
        transform: scale(1.05) !important;
    }

    .custom-nav-btn:after {
        font-size: 18px !important;
        font-weight: bold !important;
    }

    .swiper-pagination-bullet {
        width: 8px !important;
        height: 8px !important;
        background: #ccc !important;
        opacity: 0.7 !important;
    }

    .swiper-pagination-bullet-active {
        background: #2196F3 !important;
        opacity: 1 !important;
        width: 10px !important;
        height: 10px !important;
    }

    .keyword-tag {
        display: inline-flex;
        align-items: center;
        background-color: #e9f3ff;
        color: #2196F3;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .keyword-tag .remove-keyword {
        margin-left: 0.25rem;
        cursor: pointer;
        color: #2196F3;
    }

    .sortable-images .image-preview-item {
        cursor: grab;
    }

    .sortable-images .image-preview-item:active {
        cursor: grabbing;
    }

    .cropper-container {
        width: 100%;
        height: 100%;
    }
</style>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

<script>
    const products = [{
            id: "609162",
            title: "GG44548 40x40 Floor Tile",
            category: "Tiles & Accessories",
            description: "GOODWILL 40cmx40cm floor tile available in a 12pc carton covering 1.92sm floor space",
            packages: [{
                    type: "Carton",
                    price: 120000
                },
                {
                    type: "Piece",
                    price: 12000
                }
            ],
            status: "published",
            featured: false,
            activityUsage: 2,
            metaTitle: "GG44548 40x40 Floor Tile - Premium Quality Tiles",
            metaDescription: "Buy GG44548 40x40 Floor Tile for your home or office. High-quality GOODWILL tiles available in cartons or individual pieces.",
            keywords: ["floor tile", "40x40 tile", "GOODWILL", "ceramic tile", "home improvement"],
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
            packages: [{
                    type: "Carton",
                    price: 135000
                },
                {
                    type: "Piece",
                    price: 13500
                }
            ],
            status: "published",
            featured: false,
            activityUsage: 1,
            metaTitle: "GS44506 40x40 Glossy Floor Tile - Premium Quality",
            metaDescription: "Buy GS44506 40x40 Glossy Floor Tile for your home or office. High-quality GOODWILL glossy tiles available in cartons or individual pieces.",
            keywords: ["glossy tile", "40x40 tile", "GOODWILL", "ceramic tile", "home improvement"],
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
            packages: [{
                    type: "Unit",
                    price: 250000
                },
                {
                    type: "Set",
                    price: 1200000
                }
            ],
            status: "published",
            featured: true,
            activityUsage: 3,
            metaTitle: "Solar Garden Light ZC-CPD5004 - Eco-friendly Outdoor Lighting",
            metaDescription: "Buy STOTA Solar Garden Light ZC-CPD5004 with Monocrystalline panel. Energy-efficient outdoor lighting solution with IP65 rating.",
            keywords: ["solar light", "garden light", "outdoor lighting", "STOTA", "eco-friendly", "solar panel"],
            images: [
                "https://placehold.co/600x400?text=Solar+Garden+Light",
                "https://placehold.co/600x400?text=Solar+Light+Night",
                "https://placehold.co/600x400?text=Solar+Light+Components"
            ]
        },
        {
            id: "45-2091",
            title: "Portland Cement",
            category: "Earth Materials",
            description: "High-quality Portland cement for construction projects",
            packages: [{
                type: "Bag",
                price: 32000
            }],
            status: "published",
            featured: false,
            activityUsage: 5,
            metaTitle: "Portland Cement - High Quality Construction Material",
            metaDescription: "Buy high-quality Portland cement for your construction projects. Available in standard bags at competitive prices.",
            keywords: ["portland cement", "construction material", "building supplies", "cement bag"],
            images: [
                "https://placehold.co/600x400?text=Cement"
            ]
        }
    ];

    let cropper;
    let currentImageIndex = null;

    function initSwipers() {
        document.querySelectorAll('.swiper-container').forEach((swiperContainer) => {
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

    function initSortable() {
        const sortableContainer = document.querySelector('.sortable-images');
        if (sortableContainer) {
            new Sortable(sortableContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                onEnd: function() {
                    updateImageOrder();
                }
            });
        }
    }

    function updateImageOrder() {
        document.querySelectorAll('.image-preview-item').forEach((item, index) => {
            item.querySelector('.image-order').textContent = index + 1;
        });
    }

    function showProductModal(productId = null) {
        const modal = document.getElementById('productModal');
        const modalTitle = document.getElementById('modal-title');
        const form = document.getElementById('productForm');
        const packagesContainer = document.getElementById('product-packages');
        const keywordsContainer = document.getElementById('keywords-container');
        const imagesContainer = document.getElementById('product-images-container');

        form.reset();
        document.getElementById('product-id').value = '';
        keywordsContainer.innerHTML = '';
        imagesContainer.innerHTML = '';

        while (packagesContainer.children.length > 1) {
            packagesContainer.removeChild(packagesContainer.lastChild);
        }

        if (productId) {
            modalTitle.textContent = 'Edit Product';
            const product = products.find(p => p.id === productId);

            if (product) {
                document.getElementById('product-id').value = product.id;
                document.getElementById('product-title').value = product.title;
                document.getElementById('product-category').value = product.category;
                document.getElementById('product-description').value = product.description;
                document.getElementById('product-meta-title').value = product.metaTitle || '';
                document.getElementById('product-meta-description').value = product.metaDescription || '';
                document.getElementById('product-status').value = product.status;
                document.getElementById('product-featured').checked = product.featured;

                if (product.keywords && product.keywords.length > 0) {
                    product.keywords.forEach(keyword => {
                        addKeywordTag(keyword);
                    });
                }

                if (product.packages && product.packages.length > 0) {
                    packagesContainer.innerHTML = '';

                    product.packages.forEach((pkg, index) => {
                        const packageItem = createPackageItem();
                        const typeSelect = packageItem.querySelector('.package-type');
                        const priceInput = packageItem.querySelector('.package-price');

                        typeSelect.value = pkg.type;
                        priceInput.value = pkg.price;

                        packagesContainer.appendChild(packageItem);
                    });
                }

                if (product.images && product.images.length > 0) {
                    product.images.forEach((image, index) => {
                        addImagePreview(image, index + 1);
                    });
                }
            }
        } else {
            modalTitle.textContent = 'Add New Product';
        }

        modal.classList.remove('hidden');
        initSortable();
    }

    function hideProductModal() {
        const modal = document.getElementById('productModal');
        modal.classList.add('hidden');
    }

    function showDeleteModal(productId) {
        const modal = document.getElementById('deleteModal');
        const product = products.find(p => p.id === productId);

        if (product) {
            document.getElementById('delete-product-name').textContent = product.title;
            document.getElementById('confirmDelete').setAttribute('data-id', productId);
            modal.classList.remove('hidden');
        }
    }

    function hideDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
    }

    function showCropperModal(imageUrl, index) {
        const modal = document.getElementById('cropperModal');
        const imageElement = document.getElementById('image-to-crop');

        currentImageIndex = index;
        imageElement.src = imageUrl;

        modal.classList.remove('hidden');

        if (cropper) {
            cropper.destroy();
        }

        cropper = new Cropper(imageElement, {
            aspectRatio: 16 / 9,
            viewMode: 1,
            autoCropArea: 1,
            zoomable: true,
            scalable: true,
            movable: true,
            guides: true
        });
    }

    function hideCropperModal() {
        const modal = document.getElementById('cropperModal');
        modal.classList.add('hidden');

        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    function addImagePreview(imageUrl, order) {
        const container = document.getElementById('product-images-container');

        const previewItem = document.createElement('div');
        previewItem.className = 'image-preview-item relative border border-gray-200 rounded-lg overflow-hidden';
        previewItem.innerHTML = `
            <img src="${imageUrl}" class="w-full h-32 object-cover">
            <div class="absolute top-0 right-0 p-2 flex gap-2">
                <button type="button" class="bg-primary text-white rounded-full w-6 h-6 flex items-center justify-center edit-image-btn">
                    <i class="fas fa-crop-alt text-xs"></i>
                </button>
                <button type="button" class="bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center remove-image-btn">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-1 text-center">
                <span class="image-order">${order}</span>
            </div>
        `;

        container.appendChild(previewItem);

        previewItem.querySelector('.edit-image-btn').addEventListener('click', function() {
            const imageUrl = previewItem.querySelector('img').src;
            const index = Array.from(container.children).indexOf(previewItem);
            showCropperModal(imageUrl, index);
        });

        previewItem.querySelector('.remove-image-btn').addEventListener('click', function() {
            previewItem.remove();
            updateImageOrder();
        });
    }

    function createPackageItem() {
        const packageItem = document.createElement('div');
        packageItem.className = 'package-item grid grid-cols-1 md:grid-cols-3 gap-4 items-center';
        packageItem.innerHTML = `
            <div>
                <label class="block text-xs text-gray-500 mb-1">Package Type</label>
                <select class="package-type w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <option value="">Select Package</option>
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
                <label class="block text-xs text-gray-500 mb-1">Price (USh)</label>
                <input type="number" class="package-price w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter price">
            </div>
            <div class="flex items-end">
                <button type="button" class="remove-package px-3 py-2 text-red-500 hover:text-red-700">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;

        packageItem.querySelector('.remove-package').addEventListener('click', function() {
            packageItem.remove();
        });

        return packageItem;
    }

    function addKeywordTag(keyword) {
        const container = document.getElementById('keywords-container');
        const keywordsInput = document.getElementById('product-keywords');

        const tag = document.createElement('div');
        tag.className = 'keyword-tag';
        tag.innerHTML = `
            ${keyword}
            <span class="remove-keyword ml-2">
                <i class="fas fa-times"></i>
            </span>
        `;

        container.appendChild(tag);

        tag.querySelector('.remove-keyword').addEventListener('click', function() {
            tag.remove();
            updateKeywordsInput();
        });

        updateKeywordsInput();
    }

    function updateKeywordsInput() {
        const container = document.getElementById('keywords-container');
        const keywordsInput = document.getElementById('product-keywords');

        const keywords = Array.from(container.querySelectorAll('.keyword-tag')).map(tag => {
            return tag.textContent.trim();
        });

        keywordsInput.value = JSON.stringify(keywords);
    }

    function toggleFeatured(productId, featured) {
        const product = products.find(p => p.id === productId);
        if (product) {
            product.featured = featured;

            const button = document.querySelector(`.toggle-featured[data-id="${productId}"]`);
            button.setAttribute('data-featured', featured);

            if (featured) {
                button.classList.remove('text-gray-400');
                button.classList.add('text-red-500');
                button.querySelector('i').classList.remove('far');
                button.querySelector('i').classList.add('fas');
            } else {
                button.classList.add('text-gray-400');
                button.classList.remove('text-red-500');
                button.querySelector('i').classList.add('far');
                button.querySelector('i').classList.remove('fas');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initSwipers();
        initSortable();

        document.getElementById('addNewProduct').addEventListener('click', function() {
            showProductModal();
        });

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                showProductModal(productId);
            });
        });

        document.querySelectorAll('.toggle-featured').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const currentFeatured = this.getAttribute('data-featured') === 'true';
                toggleFeatured(productId, !currentFeatured);
            });
        });

        document.getElementById('add-package').addEventListener('click', function() {
            const packagesContainer = document.getElementById('product-packages');
            const packageItem = createPackageItem();
            packagesContainer.appendChild(packageItem);
        });

        document.getElementById('add-keyword').addEventListener('click', function() {
            const keywordInput = document.getElementById('product-keywords-input');
            const keyword = keywordInput.value.trim();

            if (keyword) {
                addKeywordTag(keyword);
                keywordInput.value = '';
            }
        });

        document.getElementById('product-keywords-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('add-keyword').click();
            }
        });

        document.getElementById('add-image-btn').addEventListener('click', function() {
            document.getElementById('image-upload').click();
        });

        document.getElementById('image-upload').addEventListener('change', function(e) {
            const files = e.target.files;

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const container = document.getElementById('product-images-container');
                        const order = container.children.length + 1;
                        showCropperModal(e.target.result, null);
                    };

                    reader.readAsDataURL(file);
                }
            }
        });

        document.getElementById('cropImage').addEventListener('click', function() {
            if (cropper) {
                const croppedCanvas = cropper.getCroppedCanvas({
                    width: 1600,
                    height: 900,
                    minWidth: 800,
                    minHeight: 450,
                    maxWidth: 1920,
                    maxHeight: 1080,
                    fillColor: '#fff'
                });

                const croppedImageUrl = croppedCanvas.toDataURL('image/jpeg');

                if (currentImageIndex !== null) {
                    const container = document.getElementById('product-images-container');
                    const imageItems = container.querySelectorAll('.image-preview-item');

                    if (currentImageIndex < imageItems.length) {
                        imageItems[currentImageIndex].querySelector('img').src = croppedImageUrl;
                    } else {
                        const order = container.children.length + 1;
                        addImagePreview(croppedImageUrl, order);
                    }
                } else {
                    const container = document.getElementById('product-images-container');
                    const order = container.children.length + 1;
                    addImagePreview(croppedImageUrl, order);
                }

                hideCropperModal();
            }
        });

        document.getElementById('saveProduct').addEventListener('click', function() {
            const productId = document.getElementById('product-id').value;
            const title = document.getElementById('product-title').value;
            const category = document.getElementById('product-category').value;
            const description = document.getElementById('product-description').value;
            const metaTitle = document.getElementById('product-meta-title').value;
            const metaDescription = document.getElementById('product-meta-description').value;
            const status = document.getElementById('product-status').value;
            const featured = document.getElementById('product-featured').checked;

            const keywordsInput = document.getElementById('product-keywords').value;
            const keywords = keywordsInput ? JSON.parse(keywordsInput) : [];

            const packages = [];
            document.querySelectorAll('.package-item').forEach(item => {
                const type = item.querySelector('.package-type').value;
                const price = item.querySelector('.package-price').value;

                if (type && price) {
                    packages.push({
                        type: type,
                        price: parseInt(price)
                    });
                }
            });

            const images = [];
            document.querySelectorAll('.image-preview-item img').forEach(img => {
                images.push(img.src);
            });

            if (productId) {
                const productIndex = products.findIndex(p => p.id === productId);
                if (productIndex !== -1) {
                    products[productIndex] = {
                        ...products[productIndex],
                        title,
                        category,
                        description,
                        metaTitle,
                        metaDescription,
                        status,
                        featured,
                        keywords,
                        packages,
                        images
                    };
                }
            } else {
                const newId = Date.now().toString();
                products.push({
                    id: newId,
                    title,
                    category,
                    description,
                    metaTitle,
                    metaDescription,
                    status,
                    featured,
                    keywords,
                    packages,
                    images,
                    activityUsage: 0
                });
            }

            alert('Product saved successfully!');
            hideProductModal();
        });

        document.getElementById('confirmDelete').addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const productIndex = products.findIndex(p => p.id === productId);

            if (productIndex !== -1) {
                products.splice(productIndex, 1);
            }

            alert(`Product deleted successfully!`);
            hideDeleteModal();
        });

        document.getElementById('applyFilters').addEventListener('click', function() {
            alert('Filters would be applied here');
        });

        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterCategory').value = '';
            document.getElementById('filterPackaging').value = '';
            document.getElementById('filterFeatured').value = '';
            alert('Filters would be reset here');
        });
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>