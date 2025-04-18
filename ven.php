<?php
$pageTitle = 'Vendor Profile';
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

// Define product categories array (these would typically come from the database)
$systemCategories = [
    ['id' => 1, 'name' => 'Building Materials', 'icon' => 'fa-solid fa-brick', 'status' => 'active', 'product_count' => 15],
    ['id' => 2, 'name' => 'Tools & Equipment', 'icon' => 'fa-solid fa-tools', 'status' => 'active', 'product_count' => 23],
    ['id' => 3, 'name' => 'Electrical', 'icon' => 'fa-solid fa-bolt', 'status' => 'active', 'product_count' => 8],
    ['id' => 4, 'name' => 'Plumbing', 'icon' => 'fa-solid fa-faucet', 'status' => 'active', 'product_count' => 12],
    ['id' => 5, 'name' => 'Safety Gear', 'icon' => 'fa-solid fa-hard-hat', 'status' => 'active', 'product_count' => 7],
    ['id' => 6, 'name' => 'Concrete & Cement', 'icon' => 'fa-solid fa-cubes', 'status' => 'active', 'product_count' => 5],
    ['id' => 7, 'name' => 'Lumber', 'icon' => 'fa-solid fa-tree', 'status' => 'active', 'product_count' => 9],
    ['id' => 8, 'name' => 'Roofing', 'icon' => 'fa-solid fa-home', 'status' => 'active', 'product_count' => 6],
    ['id' => 9, 'name' => 'Flooring', 'icon' => 'fa-solid fa-border-all', 'status' => 'inactive', 'product_count' => 0],
    ['id' => 10, 'name' => 'Paint & Supplies', 'icon' => 'fa-solid fa-fill-drip', 'status' => 'inactive', 'product_count' => 0],
];

// Vendor's selected categories (these would typically come from the database)
$vendorCategories = [1, 2, 3, 4, 5, 6, 7, 8];

// Define account managers array (these would typically come from the database)
$accountManagers = [
    [
        'id' => 1,
        'name' => 'John Smith',
        'email' => 'john.smith@example.com',
        'role' => 'Admin',
        'status' => 'active',
        'avatar' => 'https://placehold.co/100x100',
        'joined_date' => '2020-05-15'
    ],
    [
        'id' => 2,
        'name' => 'Sarah Johnson',
        'email' => 'sarah.j@example.com',
        'role' => 'Inventory Manager',
        'status' => 'active',
        'avatar' => 'https://placehold.co/100x100',
        'joined_date' => '2021-02-10'
    ],
    [
        'id' => 3,
        'name' => 'Michael Brown',
        'email' => 'michael.b@example.com',
        'role' => 'Sales Manager',
        'status' => 'active',
        'avatar' => 'https://placehold.co/100x100',
        'joined_date' => '2021-07-22'
    ],
    [
        'id' => 4,
        'name' => 'Emily Davis',
        'email' => 'emily.d@example.com',
        'role' => 'Customer Support',
        'status' => 'inactive',
        'avatar' => 'https://placehold.co/100x100',
        'joined_date' => '2022-01-05'
    ]
];

// Define available roles
$availableRoles = [
    'Admin' => 'Full access to all features',
    'Inventory Manager' => 'Manage products and inventory',
    'Sales Manager' => 'Handle orders and sales',
    'Customer Support' => 'Respond to customer inquiries',
    'Content Manager' => 'Manage product descriptions and images',
    'Finance Manager' => 'Handle payments and invoices'
];

// Define package definitions (these would typically come from the database)
$packageDefinitions = [
    ['id' => 1, 'name' => 'Standard Bag', 'si_unit' => 'kg', 'value' => 50],
    ['id' => 2, 'name' => 'Small Bag', 'si_unit' => 'kg', 'value' => 25],
    ['id' => 3, 'name' => 'Meter', 'si_unit' => 'm', 'value' => 1],
    ['id' => 4, 'name' => 'Roll', 'si_unit' => 'm', 'value' => 100],
    ['id' => 5, 'name' => 'Box', 'si_unit' => 'pcs', 'value' => 100],
    ['id' => 6, 'name' => 'Unit', 'si_unit' => 'pcs', 'value' => 1],
    ['id' => 7, 'name' => 'Pair', 'si_unit' => 'pcs', 'value' => 2],
    ['id' => 8, 'name' => 'Bulk Pack', 'si_unit' => 'pcs', 'value' => 1000],
];

// Define products array
$products = [
    [
        'id' => 1,
        'name' => 'Premium Cement',
        'description' => 'High-quality cement for all construction needs',
        'price' => 45000,
        'unit' => 'bag',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 6,
        'featured' => true,
        'badge' => 'FEATURED',
        'badge_color' => 'bg-primary',
        'status' => 'active',
        'package_definition_id' => 1
    ],
    [
        'id' => 2,
        'name' => 'Steel Rebar',
        'description' => 'Reinforcement bars for concrete structures',
        'price' => 30000,
        'unit' => 'meter',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 1,
        'featured' => true,
        'badge' => 'BEST SELLER',
        'badge_color' => 'bg-green-500',
        'status' => 'active',
        'package_definition_id' => 4
    ],
    [
        'id' => 3,
        'name' => 'Brick Set',
        'description' => 'Premium quality bricks for construction',
        'price' => 700000,
        'unit' => '1000 pcs',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 1,
        'featured' => true,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 3
    ],
    [
        'id' => 4,
        'name' => 'Power Drill',
        'description' => 'Professional-grade power drill with accessories',
        'price' => 520000,
        'unit' => 'unit',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 2,
        'featured' => true,
        'badge' => 'NEW',
        'badge_color' => 'bg-blue-500',
        'status' => 'active',
        'package_definition_id' => 5
    ],
    [
        'id' => 5,
        'name' => 'Safety Helmet',
        'description' => 'OSHA-approved construction safety helmet',
        'price' => 85000,
        'unit' => 'unit',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 5,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 5
    ],
    [
        'id' => 6,
        'name' => 'Measuring Tape',
        'description' => 'Professional 25ft retractable measuring tape',
        'price' => 55000,
        'unit' => 'unit',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 2,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 5
    ],
    [
        'id' => 7,
        'name' => 'Work Gloves',
        'description' => 'Heavy-duty construction work gloves',
        'price' => 65000,
        'unit' => 'pair',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 5,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 6
    ],
    [
        'id' => 8,
        'name' => 'Claw Hammer',
        'description' => 'Professional-grade steel claw hammer',
        'price' => 78000,
        'unit' => 'unit',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 2,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 5
    ],
    [
        'id' => 9,
        'name' => 'PVC Pipes',
        'description' => 'High-quality PVC pipes for plumbing',
        'price' => 35000,
        'unit' => '10 ft',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 4,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 8
    ],
    [
        'id' => 10,
        'name' => 'Electrical Wire',
        'description' => 'Copper electrical wire for safe installations',
        'price' => 120000,
        'unit' => '100 ft',
        'image' => 'https://placehold.co/400x300',
        'category_id' => 3,
        'featured' => false,
        'badge' => '',
        'badge_color' => '',
        'status' => 'active',
        'package_definition_id' => 4
    ],
];

function getPackageDefinitionById($packageDefinitions, $id)
{
    foreach ($packageDefinitions as $package) {
        if ($package['id'] == $id) {
            return $package;
        }
    }
    return null;
}

// Filter featured products
$featuredProducts = array_filter($products, function ($product) {
    return $product['featured'] === true;
});

// Filter regular products (non-featured or all depending on your preference)
$regularProducts = array_filter($products, function ($product) {
    return $product['featured'] === false;
});

// Get vendor's category count
$vendorCategoryCount = count($vendorCategories);

// Get vendor's product count
$vendorProductCount = count($products);

// Category management functions
function getCategoryById($categories, $id)
{
    foreach ($categories as $category) {
        if ($category['id'] == $id) {
            return $category;
        }
    }
    return null;
}

function getPackageById($packages, $id)
{
    foreach ($packages as $package) {
        if ($package['id'] == $id) {
            return $package;
        }
    }
    return null;
}

function formatPrice($price)
{
    return 'UGX ' . number_format($price, 0, '.', ',');
}

// AJAX endpoint simulation
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Invalid action'];

    // This would be handled by a proper AJAX endpoint in a real application
    echo json_encode($response);
    exit;
}

ob_start();
?>

<!-- Cover Photo -->
<div class="relative h-64 w-full bg-gray-200 overflow-hidden">
    <img src="https://placehold.co/1600x400/D92B13/ffffff?text=ABC+Construction+Supplies" alt="Cover Photo" class="w-full h-full object-cover">
</div>

<!-- Profile Header -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 relative z-10">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col md:flex-row">
            <!-- Profile Picture -->
            <div class="flex-shrink-0">
                <div class="h-32 w-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-white">
                    <img src="https://placehold.co/300x300" alt="Vendor Logo" class="h-full w-full object-cover">
                </div>
            </div>

            <!-- Profile Info -->
            <div class="mt-6 md:mt-0 md:ml-6 flex-grow">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-secondary">ABC Construction Supplies</h1>
                        <p class="text-gray-600 mt-1">Premium Construction Materials & Services</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <button class="bg-primary hover:bg-red-700 text-white font-medium py-2 px-6 rounded-md transition duration-150 ease-in-out flex items-center">
                            <i class="fa-solid fa-user-plus mr-2"></i> Follow
                        </button>
                        <button class="border border-gray-300 hover:bg-gray-50 text-secondary font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                            <i class="fa-solid fa-envelope"></i>
                        </button>
                    </div>
                </div>

                <!-- Stats Bar -->
                <div class="mt-6 flex flex-wrap gap-y-4">
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-calendar-days text-gray-500 mr-2"></i>
                        <span class="text-gray-700">Joined March 2008</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-location-dot text-gray-500 mr-2"></i>
                        <span class="text-gray-700">Building City, BC 12345</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-box text-gray-500 mr-2"></i>
                        <span class="text-gray-700"><?= $vendorProductCount ?> Products</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-tags text-gray-500 mr-2"></i>
                        <span class="text-gray-700"><?= $vendorCategoryCount ?> Categories</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Stats -->
        <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-x-8 gap-y-4">
            <div class="flex items-center">
                <div class="text-xl font-bold text-secondary">4.8</div>
                <div class="ml-2 flex">
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                    <span class="ml-1 text-sm text-gray-600">(128 reviews)</span>
                </div>
            </div>
            <div class="flex items-center">
                <div class="text-xl font-bold text-secondary">1.2K</div>
                <div class="ml-2 text-gray-600">Followers</div>
            </div>
            <div class="flex items-center">
                <div class="text-xl font-bold text-secondary">100+</div>
                <div class="ml-2 text-gray-600">Orders Completed</div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Navigation Tabs -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 overflow-x-auto">
                <button class="border-primary text-primary font-medium py-4 px-1 border-b-2 whitespace-nowrap" data-tab="products">
                    <i class="fa-solid fa-box-open mr-2"></i> Products
                </button>
                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap" data-tab="categories-products">
                    <i class="fa-solid fa-tags mr-2"></i> Categories & Products
                </button>
                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap" data-tab="about">
                    <i class="fa-solid fa-circle-info mr-2"></i> About
                </button>
                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap" data-tab="gallery">
                    <i class="fa-solid fa-image mr-2"></i> Gallery
                </button>
                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap" data-tab="managers">
                    <i class="fa-solid fa-users-gear mr-2"></i> Account Managers
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="tab-content">
        <!-- Products Tab (Default Active) -->
        <div id="products-tab" class="tab-pane active">
            <!-- Filter and Sort -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                    <div class="relative">
                        <select id="category-filter" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="0">All Categories</option>
                            <?php foreach ($systemCategories as $category): ?>
                                <?php if (in_array($category['id'], $vendorCategories) && $category['status'] === 'active'): ?>
                                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <div class="relative">
                        <select id="price-sort" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="default">Price: All</option>
                            <option value="low-to-high">Price: Low to High</option>
                            <option value="high-to-low">Price: High to Low</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                <div class="relative w-full sm:w-64">
                    <input type="text" id="product-search" placeholder="Search products..." class="w-full bg-white border border-gray-300 rounded-md pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Featured Products -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-secondary">Featured Products</h2>
                    <a href="#" class="text-primary hover:text-red-700 font-medium text-sm flex items-center">
                        View All <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden product-card" data-category="<?= $product['category_id'] ?>">
                            <div class="h-48 w-full bg-gray-200 overflow-hidden relative">
                                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="h-full w-full object-cover">
                                <?php if (!empty($product['badge'])): ?>
                                    <div class="absolute top-2 left-2 <?= $product['badge_color'] ?> text-white text-xs font-bold px-2 py-1 rounded">
                                        <?= $product['badge'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-secondary"><?= $product['name'] ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?= $product['description'] ?></p>
                                <div class="mt-2 flex items-center">
                                    <span class="text-primary font-bold"><?= formatPrice($product['price']) ?></span>
                                    <span class="text-xs text-gray-500 ml-1">/ <?= $product['unit'] ?></span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <button class="bg-primary hover:bg-red-700 text-white text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                                        Buy
                                    </button>
                                    <button class="border border-gray-300 hover:bg-gray-50 text-secondary text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- All Products -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-secondary mb-4">All Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="products-container">
                    <?php foreach ($regularProducts as $product): ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden product-card" data-category="<?= $product['category_id'] ?>">
                            <div class="h-48 w-full bg-gray-200 overflow-hidden">
                                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-secondary"><?= $product['name'] ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?= $product['description'] ?></p>
                                <div class="mt-2 flex items-center">
                                    <span class="text-primary font-bold"><?= formatPrice($product['price']) ?></span>
                                    <span class="text-xs text-gray-500 ml-1">/ <?= $product['unit'] ?></span>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <button class="bg-primary hover:bg-red-700 text-white text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                                        Buy
                                    </button>
                                    <button class="border border-gray-300 hover:bg-gray-50 text-secondary text-sm font-medium py-2 px-3 rounded transition duration-150 ease-in-out">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-center mb-8">
                <nav class="inline-flex rounded-md shadow">
                    <a href="#" class="inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-primary hover:bg-gray-50">
                        1
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        2
                    </a>
                    <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        3
                    </a>
                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                    </span>
                    <a href="#" class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        8
                    </a>
                    <a href="#" class="inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Categories & Products Tab -->
        <div id="categories-products-tab" class="tab-pane hidden">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-secondary">Categories & Products Management</h2>
                    <div class="flex space-x-3">
                        <button id="add-category-btn" class="bg-primary hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-150 ease-in-out flex items-center">
                            <i class="fa-solid fa-folder-plus mr-2"></i> Add Category
                        </button>
                        <button id="add-product-btn" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-150 ease-in-out flex items-center">
                            <i class="fa-solid fa-plus mr-2"></i> Add Product
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Category Management Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Management</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="categories-table-body">
                                    <?php foreach ($systemCategories as $category): ?>
                                        <?php if (in_array($category['id'], $vendorCategories)): ?>
                                            <tr data-category-id="<?= $category['id'] ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                                                            <i class="<?= $category['icon'] ?> text-primary"></i>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"><?= $category['name'] ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900"><?= $category['product_count'] ?> products</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <label class="inline-flex relative items-center cursor-pointer">
                                                            <input type="checkbox" class="sr-only peer category-status-toggle" <?= $category['status'] === 'active' ? 'checked' : '' ?> data-category-id="<?= $category['id'] ?>">
                                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                            <span class="ml-3 text-sm font-medium text-gray-700 category-status-text"><?= $category['status'] === 'active' ? 'Active' : 'Inactive' ?></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button class="text-blue-600 hover:text-blue-900 mr-3 view-products-btn" data-category-id="<?= $category['id'] ?>">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                    <button class="text-primary hover:text-red-700 mr-3 edit-category-btn" data-category-id="<?= $category['id'] ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button class="text-red-600 hover:text-red-900 delete-category-btn" data-category-id="<?= $category['id'] ?>">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Product Management Section -->
                    <div class="border-t border-gray-200 pt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Product Management</h3>

                        <div class="mb-4 flex flex-wrap gap-4">
                            <div class="relative">
                                <select id="product-category-filter" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($systemCategories as $category): ?>
                                        <?php if (in_array($category['id'], $vendorCategories) && $category['status'] === 'active'): ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="relative">
                                <select id="product-status-filter" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                                    <option value="all">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <div class="relative flex-grow sm:max-w-xs">
                                <input type="text" id="product-management-search" placeholder="Search products..." class="w-full bg-white border border-gray-300 rounded-md pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-primary focus:border-primary">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="products-table-body">
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                        $category = getCategoryById($systemCategories, $product['category_id']);
                                        $packageDef = getPackageDefinitionById($packageDefinitions, $product['package_definition_id']);
                                        ?>
                                        <tr data-product-id="<?= $product['id'] ?>" data-category-id="<?= $product['category_id'] ?>" data-status="<?= $product['status'] ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded">
                                                        <img class="h-10 w-10 rounded object-cover" src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?= $product['name'] ?></div>
                                                        <div class="text-xs text-gray-500 truncate max-w-xs"><?= $product['description'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= $category ? $category['name'] : 'Unknown' ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= formatPrice($product['price']) ?></div>
                                                <div class="text-xs text-gray-500">per <?= $product['unit'] ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= $packageDef ? $packageDef['name'] : 'Custom' ?></div>
                                                <div class="text-xs text-gray-500"><?= $packageDef ? $packageDef['value'] . ' ' . $packageDef['si_unit'] : $product['unit'] ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <label class="inline-flex relative items-center cursor-pointer">
                                                        <input type="checkbox" class="sr-only peer product-status-toggle" <?= $product['status'] === 'active' ? 'checked' : '' ?> data-product-id="<?= $product['id'] ?>">
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                        <span class="ml-3 text-sm font-medium text-gray-700 product-status-text"><?= $product['status'] === 'active' ? 'Active' : 'Inactive' ?></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button class="text-primary hover:text-red-700 mr-3 edit-product-btn" data-product-id="<?= $product['id'] ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="text-red-600 hover:text-red-900 delete-product-btn" data-product-id="<?= $product['id'] ?>">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Managers Tab -->
        <div id="managers-tab" class="tab-pane hidden">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-secondary">Account Managers</h2>
                    <button id="add-manager-btn" class="bg-primary hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded transition duration-150 ease-in-out flex items-center">
                        <i class="fa-solid fa-user-plus mr-2"></i> Add Manager
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Managers</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="managers-table-body">
                                    <?php foreach ($accountManagers as $manager): ?>
                                        <tr data-manager-id="<?= $manager['id'] ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10 rounded-full" src="<?= $manager['avatar'] ?>" alt="<?= $manager['name'] ?>">
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?= $manager['name'] ?></div>
                                                        <div class="text-sm text-gray-500"><?= $manager['email'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= $manager['role'] ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= date('M j, Y', strtotime($manager['joined_date'])) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <label class="inline-flex relative items-center cursor-pointer">
                                                        <input type="checkbox" class="sr-only peer manager-status-toggle" <?= $manager['status'] === 'active' ? 'checked' : '' ?> data-manager-id="<?= $manager['id'] ?>">
                                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                        <span class="ml-3 text-sm font-medium text-gray-700 manager-status-text"><?= $manager['status'] === 'active' ? 'Active' : 'Inactive' ?></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button class="text-primary hover:text-red-700 mr-3 edit-manager-btn" data-manager-id="<?= $manager['id'] ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="text-red-600 hover:text-red-900 delete-manager-btn" data-manager-id="<?= $manager['id'] ?>">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Permissions</h3>
                        <p class="text-sm text-gray-600 mb-4">Define what each role can access and manage in your vendor profile.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($availableRoles as $role => $description): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-900"><?= $role ?></h4>
                                    <p class="text-sm text-gray-600 mt-1"><?= $description ?></p>
                                    <button class="mt-3 text-primary hover:text-red-700 text-sm font-medium edit-role-btn" data-role="<?= $role ?>">
                                        Edit Permissions
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="product-modal-title">Add Product</h3>
            <button class="text-gray-400 hover:text-gray-500 close-modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="product-form">
            <input type="hidden" id="product-id" value="">
            <input type="hidden" id="product-category-id" value="">

            <div class="mb-4">
                <label for="product-name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                <input type="text" id="product-name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
            </div>

            <div class="mb-4">
                <label for="product-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="product-description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"></textarea>
            </div>

            <div class="mb-4">
                <label for="product-price" class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                <input type="number" id="product-price" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Package</label>
                <div class="flex flex-col space-y-4">
                    <div class="flex items-center">
                        <input type="radio" id="existing-package" name="package-type" value="existing" class="mr-2" checked>
                        <label for="existing-package" class="text-sm text-gray-700">Use existing package</label>
                    </div>

                    <div id="existing-package-section" class="pl-6">
                        <select id="product-package" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            <option value="bag">Bag</option>
                            <option value="meter">Meter</option>
                            <option value="unit">Unit</option>
                            <option value="pair">Pair</option>
                            <option value="1000 pcs">1000 pcs</option>
                            <option value="10 ft">10 ft</option>
                            <option value="100 ft">100 ft</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="radio" id="new-package" name="package-type" value="new" class="mr-2">
                        <label for="new-package" class="text-sm text-gray-700">Create new package</label>
                    </div>

                    <div id="new-package-section" class="pl-6 hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="new-package-name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                                <input type="text" id="new-package-name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label for="new-package-unit" class="block text-sm font-medium text-gray-700 mb-1">SI Unit</label>
                                <input type="text" id="new-package-unit" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" placeholder="e.g., kg, m, pcs">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="product-image" class="block text-sm font-medium text-gray-700 mb-1">Product Image URL</label>
                <input type="text" id="product-image" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary" value="https://placehold.co/400x300">
                <p class="text-xs text-gray-500 mt-1">Enter a URL or use the default placeholder</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="flex items-center">
                    <label class="inline-flex relative items-center cursor-pointer">
                        <input type="checkbox" id="product-status" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700" id="product-status-text">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 close-modal">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Product Confirmation Modal -->
<div id="delete-product-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Confirm Deletion</h3>
            <button class="text-gray-400 hover:text-gray-500 close-modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="mb-6">
            <p class="text-gray-600">Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 close-modal">Cancel</button>
            <button id="confirm-delete-product" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete</button>
        </div>
    </div>
</div>

<!-- Delete Manager Confirmation Modal -->
<div id="delete-manager-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Confirm Removal</h3>
            <button class="text-gray-400 hover:text-gray-500 close-modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="mb-6">
            <p class="text-gray-600">Are you sure you want to remove this manager? They will no longer have access to manage your vendor profile.</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 close-modal">Cancel</button>
            <button id="confirm-delete-manager" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Remove</button>
        </div>
    </div>
</div>

<!-- Add/Edit Manager Modal -->
<div id="manager-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900" id="manager-modal-title">Add Manager</h3>
            <button class="text-gray-400 hover:text-gray-500 close-modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="manager-form">
            <input type="hidden" id="manager-id" value="">
            <div class="mb-4">
                <label for="manager-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="manager-name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div class="mb-4">
                <label for="manager-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="manager-email" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div class="mb-4">
                <label for="manager-role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="manager-role" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    <?php foreach ($availableRoles as $role => $description): ?>
                        <option value="<?= $role ?>"><?= $role ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="flex items-center">
                    <label class="inline-flex relative items-center cursor-pointer">
                        <input type="checkbox" id="manager-status" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700" id="manager-status-text">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 close-modal">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-red-700">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notifications -->
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col space-y-4"></div>

<!-- JavaScript for tab switching, modals, and AJAX operations -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = document.querySelectorAll('nav button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('border-primary', 'text-primary');
                    t.classList.add('border-transparent', 'text-gray-500');
                });

                // Add active class to clicked tab
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-primary', 'text-primary');

                // Hide all tab panes
                tabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                });

                // Show the selected tab pane
                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-tab').classList.remove('hidden');
            });
        });

        // Product filtering by category
        const categoryFilter = document.getElementById('category-filter');
        const productCards = document.querySelectorAll('.product-card');

        categoryFilter.addEventListener('change', function() {
            const selectedCategory = parseInt(this.value);

            productCards.forEach(card => {
                const cardCategory = parseInt(card.getAttribute('data-category'));

                if (selectedCategory === 0 || cardCategory === selectedCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Product sorting by price
        const priceSort = document.getElementById('price-sort');
        const productsContainer = document.getElementById('products-container');

        priceSort.addEventListener('change', function() {
            const sortValue = this.value;
            const products = Array.from(productsContainer.children);

            products.sort((a, b) => {
                const priceA = parseInt(a.querySelector('.text-primary').textContent.replace(/[^0-9]/g, ''));
                const priceB = parseInt(b.querySelector('.text-primary').textContent.replace(/[^0-9]/g, ''));

                if (sortValue === 'low-to-high') {
                    return priceA - priceB;
                } else if (sortValue === 'high-to-low') {
                    return priceB - priceA;
                }

                return 0;
            });

            // Remove all products
            while (productsContainer.firstChild) {
                productsContainer.removeChild(productsContainer.firstChild);
            }

            // Add sorted products
            products.forEach(product => {
                productsContainer.appendChild(product);
            });
        });

        // Product search
        const productSearch = document.getElementById('product-search');

        productSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            productCards.forEach(card => {
                const productName = card.querySelector('h3').textContent.toLowerCase();
                const productDesc = card.querySelector('p').textContent.toLowerCase();

                if (productName.includes(searchTerm) || productDesc.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Product management filtering
        const productCategoryFilter = document.getElementById('product-category-filter');
        const productStatusFilter = document.getElementById('product-status-filter');
        const productManagementSearch = document.getElementById('product-management-search');
        const productRows = document.querySelectorAll('#products-table-body tr');

        function filterProductRows() {
            const selectedCategory = parseInt(productCategoryFilter.value);
            const selectedStatus = productStatusFilter.value;
            const searchTerm = productManagementSearch.value.toLowerCase();

            productRows.forEach(row => {
                const rowCategory = parseInt(row.getAttribute('data-category-id'));
                const rowStatus = row.getAttribute('data-status');
                const productName = row.querySelector('.text-sm.font-medium').textContent.toLowerCase();
                const productDesc = row.querySelector('.text-xs.text-gray-500').textContent.toLowerCase();

                const categoryMatch = selectedCategory === 0 || rowCategory === selectedCategory;
                const statusMatch = selectedStatus === 'all' || rowStatus === selectedStatus;
                const searchMatch = productName.includes(searchTerm) || productDesc.includes(searchTerm);

                if (categoryMatch && statusMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        productCategoryFilter.addEventListener('change', filterProductRows);
        productStatusFilter.addEventListener('change', filterProductRows);
        productManagementSearch.addEventListener('input', filterProductRows);

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} transition-opacity duration-300`;

            toast.innerHTML = `
                <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 ${type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'} rounded-lg">
                    <i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-xmark'}"></i>
                </div>
                <div class="ml-3 text-sm font-normal">${message}</div>
                <button type="button" class="ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:text-green-700' : 'bg-red-50 text-red-500 hover:text-red-700'} rounded-lg p-1.5 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            document.getElementById('toast-container').appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 5000);
        }

        // Modal handling
        const modals = document.querySelectorAll('[id$="-modal"]');
        const closeModalButtons = document.querySelectorAll('.close-modal');

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeAllModals() {
            modals.forEach(modal => {
                modal.classList.add('hidden');
            });
        }

        closeModalButtons.forEach(button => {
            button.addEventListener('click', closeAllModals);
        });

        // Category status toggle
        const categoryStatusToggles = document.querySelectorAll('.category-status-toggle');

        categoryStatusToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const categoryId = this.getAttribute('data-category-id');
                const statusText = this.closest('td').querySelector('.category-status-text');
                const isActive = this.checked;

                // AJAX request would go here
                // For demo, we'll just update the UI
                statusText.textContent = isActive ? 'Active' : 'Inactive';

                showToast(`Category status updated to ${isActive ? 'active' : 'inactive'}.`, 'success');
            });
        });

        // Product status toggle
        const productStatusToggles = document.querySelectorAll('.product-status-toggle');

        productStatusToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const productId = this.getAttribute('data-product-id');
                const statusText = this.closest('td').querySelector('.product-status-text');
                const isActive = this.checked;
                const row = this.closest('tr');

                // AJAX request would go here
                // For demo, we'll just update the UI
                statusText.textContent = isActive ? 'Active' : 'Inactive';
                row.setAttribute('data-status', isActive ? 'active' : 'inactive');

                showToast(`Product status updated to ${isActive ? 'active' : 'inactive'}.`, 'success');
            });
        });

        // Manager status toggle
        const managerStatusToggles = document.querySelectorAll('.manager-status-toggle');

        managerStatusToggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const managerId = this.getAttribute('data-manager-id');
                const statusText = this.closest('td').querySelector('.manager-status-text');
                const isActive = this.checked;

                // AJAX request would go here
                // For demo, we'll just update the UI
                statusText.textContent = isActive ? 'Active' : 'Inactive';

                showToast(`Manager status updated to ${isActive ? 'active' : 'inactive'}.`, 'success');
            });
        });

        // Delete category
        const deleteCategoryButtons = document.querySelectorAll('.delete-category-btn');
        let categoryToDelete = null;

        deleteCategoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                categoryToDelete = this.getAttribute('data-category-id');
                openModal('delete-category-modal');
            });
        });

        document.getElementById('confirm-delete-category').addEventListener('click', function() {
            if (categoryToDelete) {
                // AJAX request would go here
                // For demo, we'll just update the UI
                const categoryRow = document.querySelector(`tr[data-category-id="${categoryToDelete}"]`);
                if (categoryRow) {
                    categoryRow.remove();
                }

                showToast('Category deleted successfully.', 'success');
                closeAllModals();
                categoryToDelete = null;
            }
        });

        // Delete product
        const deleteProductButtons = document.querySelectorAll('.delete-product-btn');
        let productToDelete = null;

        deleteProductButtons.forEach(button => {
            button.addEventListener('click', function() {
                productToDelete = this.getAttribute('data-product-id');
                openModal('delete-product-modal');
            });
        });

        document.getElementById('confirm-delete-product').addEventListener('click', function() {
            if (productToDelete) {
                // AJAX request would go here
                // For demo, we'll just update the UI
                const productRow = document.querySelector(`tr[data-product-id="${productToDelete}"]`);
                if (productRow) {
                    productRow.remove();
                }

                showToast('Product deleted successfully.', 'success');
                closeAllModals();
                productToDelete = null;
            }
        });

        // Add/Edit category
        const addCategoryBtn = document.getElementById('add-category-btn');
        const editCategoryButtons = document.querySelectorAll('.edit-category-btn');
        const categoryForm = document.getElementById('category-form');
        const categoryStatusCheckbox = document.getElementById('category-status');
        const categoryStatusText = document.getElementById('category-status-text');

        addCategoryBtn.addEventListener('click', function() {
            document.getElementById('category-modal-title').textContent = 'Add Category';
            document.getElementById('category-id').value = '';
            document.getElementById('category-name').value = '';
            document.getElementById('category-icon').value = 'fa-solid fa-brick';
            categoryStatusCheckbox.checked = true;
            categoryStatusText.textContent = 'Active';

            openModal('category-modal');
        });

        editCategoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryRow = document.querySelector(`tr[data-category-id="${categoryId}"]`);

                if (categoryRow) {
                    const categoryName = categoryRow.querySelector('.text-sm.font-medium').textContent;
                    const categoryIcon = categoryRow.querySelector('.flex-shrink-0 i').className;
                    const isActive = categoryRow.querySelector('.category-status-toggle').checked;

                    document.getElementById('category-modal-title').textContent = 'Edit Category';
                    document.getElementById('category-id').value = categoryId;
                    document.getElementById('category-name').value = categoryName;
                    document.getElementById('category-icon').value = categoryIcon;
                    categoryStatusCheckbox.checked = isActive;
                    categoryStatusText.textContent = isActive ? 'Active' : 'Inactive';

                    openModal('category-modal');
                }
            });
        });

        categoryStatusCheckbox.addEventListener('change', function() {
            categoryStatusText.textContent = this.checked ? 'Active' : 'Inactive';
        });

        // Add/Edit product
        const addProductBtn = document.getElementById('add-product-btn');
        const editProductButtons = document.querySelectorAll('.edit-product-btn');
        const productForm = document.getElementById('product-form');
        const productStatusCheckbox = document.getElementById('product-status');
        const productStatusText = document.getElementById('product-status-text');
        const packageDefinitionSelect = document.getElementById('package-definition');

        // Package definition change handler
        packageDefinitionSelect.addEventListener('change', function() {
            const customPackageInputs = document.querySelectorAll('#package-name, #package-value, #package-unit');
            if (this.value) {
                customPackageInputs.forEach(input => {
                    input.disabled = true;
                    input.classList.add('bg-gray-100');
                });
            } else {
                customPackageInputs.forEach(input => {
                    input.disabled = false;
                    input.classList.remove('bg-gray-100');
                });
            }
        });

        addProductBtn.addEventListener('click', function() {
            document.getElementById('product-modal-title').textContent = 'Add Product';
            document.getElementById('product-id').value = '';
            document.getElementById('product-name').value = '';
            document.getElementById('product-description').value = '';
            document.getElementById('product-price').value = '';
            document.getElementById('product-image').value = 'https://placehold.co/400x300';
            document.getElementById('product-category').value = document.getElementById('product-category').options[0].value;
            document.getElementById('package-definition').value = '';
            document.getElementById('package-name').value = '';
            document.getElementById('package-value').value = '';
            document.getElementById('package-unit').value = 'unit';
            document.getElementById('product-featured').checked = false;
            document.getElementById('product-badge').value = '';
            document.getElementById('product-badge-color').value = 'bg-primary';
            productStatusCheckbox.checked = true;
            productStatusText.textContent = 'Active';

            // Enable custom package inputs
            const customPackageInputs = document.querySelectorAll('#package-name, #package-value, #package-unit');
            customPackageInputs.forEach(input => {
                input.disabled = false;
                input.classList.remove('bg-gray-100');
            });

            openModal('product-modal');
        });

        editProductButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);

                if (productRow) {
                    const productName = productRow.querySelector('.text-sm.font-medium').textContent;
                    const productDesc = productRow.querySelector('.text-xs.text-gray-500').textContent;
                    const categoryId = productRow.getAttribute('data-category-id');
                    const price = productRow.querySelector('td:nth-child(3) .text-sm').textContent.replace(/[^0-9]/g, '');
                    const packageName = productRow.querySelector('td:nth-child(4) .text-sm').textContent;
                    const packageInfo = productRow.querySelector('td:nth-child(4) .text-xs').textContent;
                    const isActive = productRow.querySelector('.product-status-toggle').checked;

                    document.getElementById('product-modal-title').textContent = 'Edit Product';
                    document.getElementById('product-id').value = productId;
                    document.getElementById('product-name').value = productName;
                    document.getElementById('product-description').value = productDesc;
                    document.getElementById('product-price').value = price;
                    document.getElementById('product-category').value = categoryId;

                    // For demo purposes, we'll just set a placeholder image
                    document.getElementById('product-image').value = 'https://placehold.co/400x300';

                    // For demo, we'll just select the first package definition
                    document.getElementById('package-definition').value = '1';

                    // Disable custom package inputs since we selected an existing one
                    const customPackageInputs = document.querySelectorAll('#package-name, #package-value, #package-unit');
                    customPackageInputs.forEach(input => {
                        input.disabled = true;
                        input.classList.add('bg-gray-100');
                    });

                    // For demo, we'll just set featured to false
                    document.getElementById('product-featured').checked = false;

                    // For demo, we'll just clear the badge
                    document.getElementById('product-badge').value = '';
                    document.getElementById('product-badge-color').value = 'bg-primary';

                    productStatusCheckbox.checked = isActive;
                    productStatusText.textContent = isActive ? 'Active' : 'Inactive';

                    openModal('product-modal');
                }
            });
        });

        productStatusCheckbox.addEventListener('change', function() {
            productStatusText.textContent = this.checked ? 'Active' : 'Inactive';
        });

        productForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const productId = document.getElementById('product-id').value;
            const productName = document.getElementById('product-name').value;
            const productDesc = document.getElementById('product-description').value;
            const productPrice = document.getElementById('product-price').value;
            const productCategory = document.getElementById('product-category').value;
            const isActive = productStatusCheckbox.checked;

            // Validate form
            if (!productName.trim()) {
                showToast('Product name is required.', 'error');
                return;
            }

            if (!productPrice.trim() || isNaN(productPrice) || parseInt(productPrice) <= 0) {
                showToast('Valid price is required.', 'error');
                return;
            }

            // AJAX request would go here
            // For demo, we'll just update the UI
            if (productId) {
                // Edit existing product
                const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);
                if (productRow) {
                    productRow.querySelector('.text-sm.font-medium').textContent = productName;
                    productRow.querySelector('.text-xs.text-gray-500').textContent = productDesc;
                    productRow.querySelector('td:nth-child(3) .text-sm').textContent = `UGX ${parseInt(productPrice).toLocaleString()}`;
                    productRow.querySelector('.product-status-toggle').checked = isActive;
                    productRow.querySelector('.product-status-text').textContent = isActive ? 'Active' : 'Inactive';
                    productRow.setAttribute('data-category-id', productCategory);
                    productRow.setAttribute('data-status', isActive ? 'active' : 'inactive');

                    filterProductRows();

                    showToast('Product updated successfully.', 'success');
                }
            } else {
                // Add new product
                const newProductId = Date.now(); // Generate a temporary ID
                const productsTableBody = document.getElementById('products-table-body');

                const newRow = document.createElement('tr');
                newRow.setAttribute('data-product-id', newProductId);
                newRow.setAttribute('data-category-id', productCategory);
                newRow.setAttribute('data-status', isActive ? 'active' : 'inactive');
                newRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded">
                                <img class="h-10 w-10 rounded object-cover" src="https://placehold.co/400x300" alt="${productName}">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${productName}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">${productDesc}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${document.getElementById('product-category').options[document.getElementById('product-category').selectedIndex].text}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">UGX ${parseInt(productPrice).toLocaleString()}</div>
                        <div class="text-xs text-gray-500">per unit</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">Single Unit</div>
                        <div class="text-xs text-gray-500">1 unit</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <label class="inline-flex relative items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer product-status-toggle" ${isActive ? 'checked' : ''} data-product-id="${newProductId}">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700 product-status-text">${isActive ? 'Active' : 'Inactive'}</span>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-primary hover:text-red-700 mr-3 edit-product-btn" data-product-id="${newProductId}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 delete-product-btn" data-product-id="${newProductId}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                `;

                productsTableBody.appendChild(newRow);

                // Add event listeners to new buttons
                newRow.querySelector('.product-status-toggle').addEventListener('change', function() {
                    const statusText = this.closest('td').querySelector('.product-status-text');
                    const isActive = this.checked;
                    const row = this.closest('tr');
                    statusText.textContent = isActive ? 'Active' : 'Inactive';
                    row.setAttribute('data-status', isActive ? 'active' : 'inactive');
                    filterProductRows();
                    showToast(`Product status updated to ${isActive ? 'active' : 'inactive'}.`, 'success');
                });

                newRow.querySelector('.edit-product-btn').addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productRow = document.querySelector(`tr[data-product-id="${productId}"]`);

                    if (productRow) {
                        const productName = productRow.querySelector('.text-sm.font-medium').textContent;
                        const productDesc = productRow.querySelector('.text-xs.text-gray-500').textContent;
                        const categoryId = productRow.getAttribute('data-category-id');
                        const price = productRow.querySelector('td:nth-child(3) .text-sm').textContent.replace(/[^0-9]/g, '');
                        const packageName = productRow.querySelector('td:nth-child(4) .text-sm').textContent;
                        const packageInfo = productRow.querySelector('td:nth-child(4) .text-xs').textContent;
                        const isActive = productRow.querySelector('.product-status-toggle').checked;

                        document.getElementById('product-modal-title').textContent = 'Edit Product';
                        document.getElementById('product-id').value = productId;
                        document.getElementById('product-name').value = productName;
                        document.getElementById('product-description').value = productDesc;
                        document.getElementById('product-price').value = price;
                        document.getElementById('product-category').value = categoryId;

                        // For demo purposes, we'll just set a placeholder image
                        document.getElementById('product-image').value = 'https://placehold.co/400x300';

                        // For demo, we'll just select the first package definition
                        document.getElementById('package-definition').value = '1';

                        // Disable custom package inputs since we selected an existing one
                        const customPackageInputs = document.querySelectorAll('#package-name, #package-value, #package-unit');
                        customPackageInputs.forEach(input => {
                            input.disabled = true;
                            input.classList.add('bg-gray-100');
                        });

                        // For demo, we'll just set featured to false
                        document.getElementById('product-featured').checked = false;

                        // For demo, we'll just clear the badge
                        document.getElementById('product-badge').value = '';
                        document.getElementById('product-badge-color').value = 'bg-primary';

                        productStatusCheckbox.checked = isActive;
                        productStatusText.textContent = isActive ? 'Active' : 'Inactive';

                        openModal('product-modal');
                    }
                });

                newRow.querySelector('.delete-product-btn').addEventListener('click', function() {
                    productToDelete = this.getAttribute('data-product-id');
                    openModal('delete-product-modal');
                });

                filterProductRows();

                showToast('Product added successfully.', 'success');
            }

            closeAllModals();
        });

        // Delete manager
        const deleteManagerButtons = document.querySelectorAll('.delete-manager-btn');
        let managerToDelete = null;

        deleteManagerButtons.forEach(button => {
            button.addEventListener('click', function() {
                managerToDelete = this.getAttribute('data-manager-id');
                openModal('delete-manager-modal');
            });
        });

        document.getElementById('confirm-delete-manager').addEventListener('click', function() {
            if (managerToDelete) {
                // AJAX request would go here
                // For demo, we'll just update the UI
                const managerRow = document.querySelector(`tr[data-manager-id="${managerToDelete}"]`);
                if (managerRow) {
                    managerRow.remove();
                }

                showToast('Manager removed successfully.', 'success');
                closeAllModals();
                managerToDelete = null;
            }
        });

        // Add/Edit manager
        const addManagerBtn = document.getElementById('add-manager-btn');
        const editManagerButtons = document.querySelectorAll('.edit-manager-btn');
        const managerForm = document.getElementById('manager-form');
        const managerStatusCheckbox = document.getElementById('manager-status');
        const managerStatusText = document.getElementById('manager-status-text');

        addManagerBtn.addEventListener('click', function() {
            document.getElementById('manager-modal-title').textContent = 'Add Manager';
            document.getElementById('manager-id').value = '';
            document.getElementById('manager-name').value = '';
            document.getElementById('manager-email').value = '';
            document.getElementById('manager-role').value = 'Admin';
            managerStatusCheckbox.checked = true;
            managerStatusText.textContent = 'Active';

            openModal('manager-modal');
        });

        editManagerButtons.forEach(button => {
            button.addEventListener('click', function() {
                const managerId = this.getAttribute('data-manager-id');
                const managerRow = document.querySelector(`tr[data-manager-id="${managerId}"]`);

                if (managerRow) {
                    const managerName = managerRow.querySelector('.text-sm.font-medium').textContent;
                    const managerEmail = managerRow.querySelector('.text-sm.text-gray-500').textContent;
                    const managerRole = managerRow.querySelector('td:nth-child(2) .text-sm').textContent;
                    const isActive = managerRow.querySelector('.manager-status-toggle').checked;

                    document.getElementById('manager-modal-title').textContent = 'Edit Manager';
                    document.getElementById('manager-id').value = managerId;
                    document.getElementById('manager-name').value = managerName;
                    document.getElementById('manager-email').value = managerEmail;
                    document.getElementById('manager-role').value = managerRole;
                    managerStatusCheckbox.checked = isActive;
                    managerStatusText.textContent = isActive ? 'Active' : 'Inactive';

                    openModal('manager-modal');
                }
            });
        });

        managerStatusCheckbox.addEventListener('change', function() {
            managerStatusText.textContent = this.checked ? 'Active' : 'Inactive';
        });

        managerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const managerId = document.getElementById('manager-id').value;
            const managerName = document.getElementById('manager-name').value;
            const managerEmail = document.getElementById('manager-email').value;
            const managerRole = document.getElementById('manager-role').value;
            const isActive = managerStatusCheckbox.checked;

            // Validate form
            if (!managerName.trim()) {
                showToast('Manager name is required.', 'error');
                return;
            }

            if (!managerEmail.trim() || !managerEmail.includes('@')) {
                showToast('Valid email is required.', 'error');
                return;
            }

            // AJAX request would go here
            // For demo, we'll just update the UI
            if (managerId) {
                // Edit existing manager
                const managerRow = document.querySelector(`tr[data-manager-id="${managerId}"]`);
                if (managerRow) {
                    managerRow.querySelector('.text-sm.font-medium').textContent = managerName;
                    managerRow.querySelector('.text-sm.text-gray-500').textContent = managerEmail;
                    managerRow.querySelector('td:nth-child(2) .text-sm').textContent = managerRole;
                    managerRow.querySelector('.manager-status-toggle').checked = isActive;
                    managerRow.querySelector('.manager-status-text').textContent = isActive ? 'Active' : 'Inactive';
                }

                showToast('Manager updated successfully.', 'success');
            } else {
                // Add new manager
                const newManagerId = Date.now(); // Generate a temporary ID
                const managersTableBody = document.getElementById('managers-table-body');

                const newRow = document.createElement('tr');
                newRow.setAttribute('data-manager-id', newManagerId);
                newRow.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="https://placehold.co/100x100" alt="${managerName}">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${managerName}</div>
                                <div class="text-sm text-gray-500">${managerEmail}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${managerRole}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <label class="inline-flex relative items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer manager-status-toggle" ${isActive ? 'checked' : ''} data-manager-id="${newManagerId}">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ml-3 text-sm font-medium text-gray-700 manager-status-text">${isActive ? 'Active' : 'Inactive'}</span>
                            </label>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button class="text-primary hover:text-red-700 mr-3 edit-manager-btn" data-manager-id="${newManagerId}">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-900 delete-manager-btn" data-manager-id="${newManagerId}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                `;

                managersTableBody.appendChild(newRow);

                // Add event listeners to new buttons
                newRow.querySelector('.manager-status-toggle').addEventListener('change', function() {
                    const statusText = this.closest('td').querySelector('.manager-status-text');
                    const isActive = this.checked;
                    statusText.textContent = isActive ? 'Active' : 'Inactive';
                    showToast(`Manager status updated to ${isActive ? 'active' : 'inactive'}.`, 'success');
                });

                newRow.querySelector('.edit-manager-btn').addEventListener('click', function() {
                    const managerId = this.getAttribute('data-manager-id');
                    const managerRow = document.querySelector(`tr[data-manager-id="${managerId}"]`);

                    if (managerRow) {
                        const managerName = managerRow.querySelector('.text-sm.font-medium').textContent;
                        const managerEmail = managerRow.querySelector('.text-sm.text-gray-500').textContent;
                        const managerRole = managerRow.querySelector('td:nth-child(2) .text-sm').textContent;
                        const isActive = managerRow.querySelector('.manager-status-toggle').checked;

                        document.getElementById('manager-modal-title').textContent = 'Edit Manager';
                        document.getElementById('manager-id').value = managerId;
                        document.getElementById('manager-name').value = managerName;
                        document.getElementById('manager-email').value = managerEmail;
                        document.getElementById('manager-role').value = managerRole;
                        managerStatusCheckbox.checked = isActive;
                        managerStatusText.textContent = isActive ? 'Active' : 'Inactive';

                        openModal('manager-modal');
                    }
                });

                newRow.querySelector('.delete-manager-btn').addEventListener('click', function() {
                    managerToDelete = this.getAttribute('data-manager-id');
                    openModal('delete-manager-modal');
                });

                showToast('Manager added successfully.', 'success');
            }

            closeAllModals();
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>