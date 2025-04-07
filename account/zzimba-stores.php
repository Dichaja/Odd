<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Zzimba Stores';
$activeNav = 'zzimba-stores';

// Sample data for vendor profiles
// In a real application, this would come from the database
$ownedStores = [
    [
        'id' => 1001,
        'name' => 'Kampala Hardware Supplies',
        'location' => 'Kampala Central, Nakasero',
        'status' => 'active',
        'products' => 42,
        'categories' => ['Hardware Materials', 'Electrical Supplies', 'Plumbing Fittings'],
        'subscription' => 'Valid until: Dec 15, 2023',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=KHS'
    ],
    [
        'id' => 1002,
        'name' => 'Mbale Construction Materials',
        'location' => 'Mbale, Industrial Area',
        'status' => 'pending',
        'products' => 27,
        'categories' => ['Tiles & Accessories', 'Earth Materials'],
        'subscription' => 'Awaiting approval',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=MCM'
    ]
];

$managedStores = [
    [
        'id' => 2001,
        'name' => 'Jinja Roofing Specialists',
        'location' => 'Jinja, Main Street',
        'owner' => 'Sarah Namukasa',
        'status' => 'active',
        'products' => 35,
        'categories' => ['Roofing Materials', 'Hardware Materials'],
        'role' => 'Manager',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=JRS'
    ],
    [
        'id' => 2002,
        'name' => 'Tororo Building Supplies',
        'location' => 'Tororo, Central Market',
        'owner' => 'David Okello',
        'status' => 'active',
        'products' => 18,
        'categories' => ['Building Glass Materials', 'Paints & Binders'],
        'role' => 'Inventory Manager',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=TBS'
    ],
    [
        'id' => 2003,
        'name' => 'Wakiso Electrical Store',
        'location' => 'Wakiso, Trading Center',
        'owner' => 'Michael Ssemanda',
        'status' => 'inactive',
        'products' => 23,
        'categories' => ['Electrical Supplies'],
        'role' => 'Sales Manager',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=WES'
    ]
];

// Sample categories for the form
$storeCategories = [
    'Tiles & Accessories',
    'Hardware Materials',
    'Paints & Binders',
    'Earth Materials',
    'Roofing Materials',
    'Electrical Supplies',
    'Plumbing Fittings',
    'Building Glass Materials'
];

// Sample nature of operations
$natureOfOperations = [
    'Manufacturer',
    'Hardware Store',
    'Earth materials',
    'Plant & Equipment',
    'Transporter',
    'Wholesale Store',
    'Distributor'
];

ob_start();
?>

<div class="space-y-6">
    <div class="content-section">
        <div class="content-header p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-secondary">My Zzimba Stores</h1>
                    <p class="text-sm text-gray-text mt-2">
                        Manage your vendor profiles and store listings
                    </p>
                </div>
                <button id="createStoreBtn" class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2 justify-center">
                    <i class="fas fa-plus"></i>
                    <span>Create New Store</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs Section -->
    <div class="content-section">
        <div class="border-b border-gray-200">
            <div class="flex overflow-x-auto">
                <button id="ownedTabBtn" class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-user-primary text-user-primary">
                    My Owned Stores
                </button>
                <button id="managedTabBtn" class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Stores I Manage
                </button>
            </div>
        </div>

        <!-- Owned Stores Tab Content -->
        <div id="ownedTabContent" class="tab-content p-6">
            <?php if (empty($ownedStores)): ?>
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-store text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-secondary mb-2">No Stores Yet</h3>
                    <p class="text-sm text-gray-text mb-4">You haven't created any stores yet. Create your first store to start selling on Zzimba Online.</p>
                    <button id="createFirstStoreBtn" class="h-10 px-6 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        Create Your First Store
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($ownedStores as $store): ?>
                        <div class="bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                            <div class="flex flex-col sm:flex-row">
                                <div class="w-full sm:w-32 h-32 bg-gray-50 flex items-center justify-center flex-shrink-0">
                                    <img src="<?= $store['image'] ?>" alt="<?= htmlspecialchars($store['name']) ?>" class="w-20 h-20 object-cover rounded-lg">
                                </div>
                                <div class="p-4 sm:p-6 flex-grow">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                        <h3 class="font-semibold text-secondary"><?= htmlspecialchars($store['name']) ?></h3>
                                        <div class="inline-flex items-center">
                                            <?php if ($store['status'] === 'active'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                                    Active
                                                </span>
                                            <?php elseif ($store['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-yellow-500"></span>
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-text mb-3">
                                        <i class="fas fa-map-marker-alt mr-1 text-user-primary"></i>
                                        <?= htmlspecialchars($store['location']) ?>
                                    </p>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-text">Active Products</p>
                                            <p class="font-medium"><?= $store['products'] ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-text">Active Categories</p>
                                            <p class="font-medium"><?= count($store['categories']) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <p class="text-xs text-gray-text">
                                            <i class="fas fa-calendar-check mr-1"></i>
                                            <?= htmlspecialchars($store['subscription']) ?>
                                        </p>
                                        <div class="flex gap-2">
                                            <button onclick="openEditModal(<?= $store['id'] ?>)" class="h-8 px-3 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors flex items-center gap-1 text-sm">
                                                <i class="fas fa-edit"></i>
                                                <span>Edit</span>
                                            </button>
                                            <a href="store-manage-<?= $store['id'] ?>" class="h-8 px-3 bg-user-primary text-white rounded hover:bg-user-primary/90 transition-colors flex items-center gap-1 text-sm">
                                                <i class="fas fa-cog"></i>
                                                <span>Manage</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Managed Stores Tab Content -->
        <div id="managedTabContent" class="tab-content p-6 hidden">
            <?php if (empty($managedStores)): ?>
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-user-tie text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-secondary mb-2">No Managed Stores</h3>
                    <p class="text-sm text-gray-text">You haven't been added as a manager to any stores yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($managedStores as $store): ?>
                        <div class="bg-white rounded-lg border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                            <div class="flex flex-col sm:flex-row">
                                <div class="w-full sm:w-32 h-32 bg-gray-50 flex items-center justify-center flex-shrink-0">
                                    <img src="<?= $store['image'] ?>" alt="<?= htmlspecialchars($store['name']) ?>" class="w-20 h-20 object-cover rounded-lg">
                                </div>
                                <div class="p-4 sm:p-6 flex-grow">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                                        <h3 class="font-semibold text-secondary"><?= htmlspecialchars($store['name']) ?></h3>
                                        <div class="inline-flex items-center">
                                            <?php if ($store['status'] === 'active'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-500"></span>
                                                    Active
                                                </span>
                                            <?php elseif ($store['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-yellow-500"></span>
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-red-500"></span>
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-text mb-1">
                                        <i class="fas fa-map-marker-alt mr-1 text-user-primary"></i>
                                        <?= htmlspecialchars($store['location']) ?>
                                    </p>
                                    <p class="text-sm text-gray-text mb-3">
                                        <i class="fas fa-user mr-1 text-user-primary"></i>
                                        Owner: <?= htmlspecialchars($store['owner']) ?>
                                    </p>
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-text">Active Products</p>
                                            <p class="font-medium"><?= $store['products'] ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-text">Active Categories</p>
                                            <p class="font-medium"><?= count($store['categories']) ?></p>
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="store-manage-<?= $store['id'] ?>" class="h-8 px-3 bg-user-primary text-white rounded hover:bg-user-primary/90 transition-colors flex items-center gap-1 text-sm">
                                            <i class="fas fa-cog"></i>
                                            <span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Store Modal -->
<div id="editStoreModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('editStoreModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Edit Store</h3>
                <button onclick="hideModal('editStoreModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editStoreForm">
                <input type="hidden" id="editStoreId" value="">
                <div class="space-y-4">
                    <div>
                        <label for="editStoreName" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                        <input type="text" id="editStoreName" placeholder="Enter store name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="editStoreDescription" class="block text-sm font-medium text-gray-700 mb-1">Store Description</label>
                        <textarea id="editStoreDescription" rows="3" placeholder="Brief description of your store" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"></textarea>
                    </div>
                    <div>
                        <label for="editStoreDistrict" class="block text-sm font-medium text-gray-700 mb-1">District</label>
                        <select id="editStoreDistrict" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select District</option>
                            <!-- Districts will be loaded dynamically -->
                        </select>
                    </div>
                    <div>
                        <label for="editStoreAddress" class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label>
                        <input type="text" id="editStoreAddress" placeholder="Enter physical address" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location on Map</label>
                        <div id="editMapContainer" class="w-full h-64 rounded-lg border border-gray-200 mb-2"></div>
                        <input type="hidden" id="editLatitude" value="">
                        <input type="hidden" id="editLongitude" value="">
                    </div>
                    <div>
                        <label for="editStorePhone" class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                        <input type="tel" id="editStorePhone" placeholder="Enter contact phone" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="editStoreEmail" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                        <input type="email" id="editStoreEmail" placeholder="Enter contact email" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        SAVE CHANGES
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Store Modal - Multi-step -->
<div id="createStoreModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('createStoreModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Create New Store</h3>
                <button onclick="hideModal('createStoreModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Step indicators -->
            <div class="flex items-center justify-center mb-6">
                <div class="flex items-center">
                    <div id="step1Indicator" class="step-indicator active flex items-center justify-center w-8 h-8 rounded-full bg-user-primary text-white font-medium">1</div>
                    <div class="w-12 h-1 bg-gray-200" id="step1to2Line"></div>
                    <div id="step2Indicator" class="step-indicator flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 font-medium">2</div>
                    <div class="w-12 h-1 bg-gray-200" id="step2to3Line"></div>
                    <div id="step3Indicator" class="step-indicator flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-500 font-medium">3</div>
                </div>
            </div>

            <!-- Step 1: Vendor Details -->
            <div id="step1" class="step-content">
                <h4 class="text-center font-medium text-secondary mb-4">Your Vendor Details</h4>
                <div class="space-y-4">
                    <div>
                        <label for="businessName" class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                        <input type="text" id="businessName" placeholder="Enter business name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="businessEmail" class="block text-sm font-medium text-gray-700 mb-1">Business Email *</label>
                        <input type="email" id="businessEmail" placeholder="Enter business email" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="contactNumber" class="block text-sm font-medium text-gray-700 mb-1">Main Contact Number *</label>
                        <input type="tel" id="contactNumber" placeholder="Enter contact number" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                        <select id="district" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select District</option>
                            <!-- Districts will be loaded dynamically -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Location on Map *</label>
                        <div id="mapContainer" class="w-full h-64 rounded-lg border border-gray-200 mb-2"></div>
                        <p class="text-xs text-gray-500">Click on the map to select your exact location</p>
                        <input type="hidden" id="latitude" value="">
                        <input type="hidden" id="longitude" value="">
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Your Address *</label>
                        <input type="text" id="address" placeholder="Enter your address" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label for="natureOfOperation" class="block text-sm font-medium text-gray-700 mb-1">Nature of Operation *</label>
                        <select id="natureOfOperation" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select Nature of Operation</option>
                            <?php foreach ($natureOfOperations as $operation): ?>
                                <option value="<?= htmlspecialchars($operation) ?>"><?= htmlspecialchars($operation) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" id="step1NextBtn" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        NEXT
                    </button>
                </div>
            </div>

            <!-- Step 2: Categories -->
            <div id="step2" class="step-content hidden">
                <h4 class="text-center font-medium text-secondary mb-4">Select Store Categories</h4>
                <p class="text-sm text-gray-text mb-4 text-center">Choose the categories that best describe your products</p>

                <div class="space-y-3 mb-6">
                    <?php foreach ($storeCategories as $index => $category): ?>
                        <div class="flex items-center">
                            <input type="checkbox" id="category<?= $index ?>" value="<?= htmlspecialchars($category) ?>" class="w-4 h-4 text-user-primary border-gray-300 rounded focus:ring-user-primary">
                            <label for="category<?= $index ?>" class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($category) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-between">
                    <button type="button" id="step2BackBtn" class="w-24 h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        BACK
                    </button>
                    <button type="button" id="step2NextBtn" class="w-24 h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        NEXT
                    </button>
                </div>
            </div>

            <!-- Step 3: Add Products -->
            <div id="step3" class="step-content hidden">
                <h4 class="text-center font-medium text-secondary mb-4">Add Products</h4>
                <p class="text-sm text-gray-text mb-4 text-center">Add your initial products to your store</p>

                <div id="productsList" class="space-y-4 mb-6">
                    <div class="product-item border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-3">
                            <h5 class="font-medium">Product 1</h5>
                            <button type="button" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                <input type="text" placeholder="Enter product name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select class="product-category w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                                <input type="number" placeholder="Enter price" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" id="addProductBtn" class="w-full h-10 mb-6 border border-dashed border-gray-300 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Add Another Product
                </button>

                <div class="flex justify-between">
                    <button type="button" id="step3BackBtn" class="w-24 h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        BACK
                    </button>
                    <button type="button" id="step3FinishBtn" class="w-24 h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        FINISH
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Processing...</p>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>

<script>
    $(document).ready(function() {
        // Load districts from API
        fetchDistricts();

        // Tab functionality
        $('.tab-btn').click(function() {
            $('.tab-btn').removeClass('active border-user-primary text-user-primary').addClass('border-transparent text-gray-500');
            $(this).addClass('active border-user-primary text-user-primary').removeClass('border-transparent text-gray-500');

            $('.tab-content').addClass('hidden');
            if ($(this).attr('id') === 'ownedTabBtn') {
                $('#ownedTabContent').removeClass('hidden');
            } else {
                $('#managedTabContent').removeClass('hidden');
            }
        });

        // Create store button handlers
        $('#createStoreBtn, #createFirstStoreBtn').click(function() {
            showModal('createStoreModal');
            // Initialize map after modal is shown
            setTimeout(() => {
                initMap('mapContainer', null, null);
            }, 300);
        });

        // District change handler for map update
        $('#district, #editStoreDistrict').change(function() {
            const districtId = $(this).val();
            const districtName = $(this).find('option:selected').text();

            if (!districtId) return;

            // Update map with district boundaries
            if ($(this).attr('id') === 'district') {
                updateMapWithDistrict(districtName, 'mapContainer');
            } else {
                updateMapWithDistrict(districtName, 'editMapContainer');
            }
        });

        // Multi-step form navigation
        $('#step1NextBtn').click(function() {
            // Validate step 1
            const businessName = $('#businessName').val();
            const businessEmail = $('#businessEmail').val();
            const contactNumber = $('#contactNumber').val();
            const district = $('#district').val();
            const address = $('#address').val();
            const latitude = $('#latitude').val();
            const longitude = $('#longitude').val();
            const natureOfOperation = $('#natureOfOperation').val();

            if (!businessName || !businessEmail || !contactNumber || !district || !address || !latitude || !longitude || !natureOfOperation) {
                alert('Please fill in all required fields and select your location on the map');
                return;
            }

            // Move to step 2
            $('#step1').addClass('hidden');
            $('#step2').removeClass('hidden');

            // Update indicators
            $('#step1Indicator').addClass('bg-green-500').removeClass('bg-user-primary');
            $('#step1Indicator').html('<i class="fas fa-check"></i>');
            $('#step2Indicator').addClass('bg-user-primary').removeClass('bg-gray-200 text-gray-500').addClass('text-white');
            $('#step1to2Line').addClass('bg-green-500').removeClass('bg-gray-200');
        });

        $('#step2BackBtn').click(function() {
            $('#step2').addClass('hidden');
            $('#step1').removeClass('hidden');

            // Update indicators
            $('#step1Indicator').removeClass('bg-green-500').addClass('bg-user-primary');
            $('#step1Indicator').text('1');
            $('#step2Indicator').removeClass('bg-user-primary text-white').addClass('bg-gray-200 text-gray-500');
            $('#step1to2Line').removeClass('bg-green-500').addClass('bg-gray-200');
        });

        $('#step2NextBtn').click(function() {
            // Validate step 2
            const selectedCategories = $('input[type="checkbox"]:checked').length;

            if (selectedCategories === 0) {
                alert('Please select at least one category');
                return;
            }

            // Populate product categories dropdown
            $('.product-category').html('<option value="">Select Category</option>');
            $('input[type="checkbox"]:checked').each(function() {
                const category = $(this).val();
                $('.product-category').append(`<option value="${category}">${category}</option>`);
            });

            // Move to step 3
            $('#step2').addClass('hidden');
            $('#step3').removeClass('hidden');

            // Update indicators
            $('#step2Indicator').addClass('bg-green-500').removeClass('bg-user-primary');
            $('#step2Indicator').html('<i class="fas fa-check"></i>');
            $('#step3Indicator').addClass('bg-user-primary').removeClass('bg-gray-200 text-gray-500').addClass('text-white');
            $('#step2to3Line').addClass('bg-green-500').removeClass('bg-gray-200');
        });

        $('#step3BackBtn').click(function() {
            $('#step3').addClass('hidden');
            $('#step2').removeClass('hidden');

            // Update indicators
            $('#step2Indicator').removeClass('bg-green-500').addClass('bg-user-primary');
            $('#step2Indicator').text('2');
            $('#step3Indicator').removeClass('bg-user-primary text-white').addClass('bg-gray-200 text-gray-500');
            $('#step2to3Line').removeClass('bg-green-500').addClass('bg-gray-200');
        });

        // Add product button
        $('#addProductBtn').click(function() {
            const productCount = $('.product-item').length + 1;

            const newProduct = `
                <div class="product-item border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="font-medium">Product ${productCount}</h5>
                        <button type="button" class="remove-product text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <input type="text" placeholder="Enter product name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select class="product-category w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                <option value="">Select Category</option>
                                ${$('input[type="checkbox"]:checked').map(function() {
                                    return `<option value="${$(this).val()}">${$(this).val()}</option>`;
                                }).get().join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                            <input type="number" placeholder="Enter price" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        </div>
                    </div>
                </div>
            `;

            $('#productsList').append(newProduct);

            // Add event listener for remove button
            $('.remove-product').off('click').on('click', function() {
                $(this).closest('.product-item').remove();

                // Renumber products
                $('.product-item').each(function(index) {
                    $(this).find('h5').text(`Product ${index + 1}`);
                });
            });
        });

        // Finish button
        $('#step3FinishBtn').click(function() {
            showLoading();

            setTimeout(function() {
                hideLoading();
                hideModal('createStoreModal');
                showSuccessNotification('Store created successfully! Awaiting approval.');

                // Reset form
                resetCreateStoreForm();
            }, 1500);
        });

        // Edit store form submission
        $('#editStoreForm').submit(function(e) {
            e.preventDefault();
            showLoading();

            setTimeout(function() {
                hideLoading();
                hideModal('editStoreModal');
                showSuccessNotification('Store updated successfully!');
            }, 1500);
        });
    });

    // Fetch districts from API
    function fetchDistricts() {
        showLoading();

        // Using Uganda Bureau of Statistics API (example)
        // In a real application, replace with actual API endpoint
        fetch('https://api.example.com/uganda/districts')
            .then(response => {
                // For demo purposes, we'll use mock data
                return {
                    ok: true,
                    json: () => Promise.resolve({
                        districts: [{
                                id: '0001',
                                name: 'Kampala'
                            },
                            {
                                id: '4765',
                                name: 'Jinja'
                            },
                            {
                                id: '5577',
                                name: 'Manafwa'
                            },
                            {
                                id: '7862',
                                name: 'Mbale'
                            },
                            {
                                id: '2604',
                                name: 'Mukono'
                            },
                            {
                                id: '1732',
                                name: 'Tororo'
                            },
                            {
                                id: '7672',
                                name: 'Wakiso'
                            },
                            {
                                id: '3301',
                                name: 'Arua'
                            },
                            {
                                id: '4502',
                                name: 'Gulu'
                            },
                            {
                                id: '5803',
                                name: 'Kabale'
                            },
                            {
                                id: '6104',
                                name: 'Masaka'
                            },
                            {
                                id: '7405',
                                name: 'Mbarara'
                            }
                        ]
                    })
                };
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch districts');
                }
                return response.json();
            })
            .then(data => {
                const districts = data.districts;

                // Populate district dropdowns
                $('#district, #editStoreDistrict').html('<option value="">Select District</option>');

                districts.forEach(district => {
                    $('#district, #editStoreDistrict').append(`<option value="${district.id}">${district.name}</option>`);
                });

                hideLoading();
            })
            .catch(error => {
                console.error('Error fetching districts:', error);
                hideLoading();

                // Fallback to hardcoded districts
                const fallbackDistricts = [{
                        id: '0001',
                        name: 'Kampala'
                    },
                    {
                        id: '4765',
                        name: 'Jinja'
                    },
                    {
                        id: '5577',
                        name: 'Manafwa'
                    },
                    {
                        id: '7862',
                        name: 'Mbale'
                    },
                    {
                        id: '2604',
                        name: 'Mukono'
                    },
                    {
                        id: '1732',
                        name: 'Tororo'
                    },
                    {
                        id: '7672',
                        name: 'Wakiso'
                    }
                ];

                $('#district, #editStoreDistrict').html('<option value="">Select District</option>');

                fallbackDistricts.forEach(district => {
                    $('#district, #editStoreDistrict').append(`<option value="${district.id}">${district.name}</option>`);
                });
            });
    }

    // Initialize Google Map
    function initMap(containerId, lat, lng) {
        const mapContainer = document.getElementById(containerId);
        if (!mapContainer) return;

        // Default to Uganda center if no coordinates provided
        const center = lat && lng ? {
            lat,
            lng
        } : {
            lat: 1.3733,
            lng: 32.2903
        };

        const map = new google.maps.Map(mapContainer, {
            center: center,
            zoom: 7,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false
        });

        // Add marker if coordinates are provided
        if (lat && lng) {
            new google.maps.Marker({
                position: {
                    lat,
                    lng
                },
                map: map,
                draggable: true
            });
        }

        // Allow clicking on map to place marker
        map.addListener('click', function(e) {
            placeMarker(e.latLng, map, containerId);
        });

        // Store map instance for later use
        window[containerId + 'Map'] = map;
    }

    // Place marker on map
    function placeMarker(location, map, containerId) {
        // Remove existing markers
        if (window[containerId + 'Marker']) {
            window[containerId + 'Marker'].setMap(null);
        }

        // Create new marker
        const marker = new google.maps.Marker({
            position: location,
            map: map,
            draggable: true
        });

        // Store marker instance
        window[containerId + 'Marker'] = marker;

        // Update hidden inputs with coordinates
        const lat = location.lat();
        const lng = location.lng();

        if (containerId === 'mapContainer') {
            $('#latitude').val(lat);
            $('#longitude').val(lng);

            // Reverse geocode to get address
            reverseGeocode(lat, lng, 'address');
        } else {
            $('#editLatitude').val(lat);
            $('#editLongitude').val(lng);

            // Reverse geocode to get address
            reverseGeocode(lat, lng, 'editStoreAddress');
        }

        // Add drag event listener to marker
        marker.addListener('dragend', function() {
            const newLat = marker.getPosition().lat();
            const newLng = marker.getPosition().lng();

            if (containerId === 'mapContainer') {
                $('#latitude').val(newLat);
                $('#longitude').val(newLng);

                // Reverse geocode to get address
                reverseGeocode(newLat, newLng, 'address');
            } else {
                $('#editLatitude').val(newLat);
                $('#editLongitude').val(newLng);

                // Reverse geocode to get address
                reverseGeocode(newLat, newLng, 'editStoreAddress');
            }
        });
    }

    // Update map with district boundaries
    function updateMapWithDistrict(districtName, containerId) {
        const map = window[containerId + 'Map'];
        if (!map) {
            // Initialize map if not already done
            setTimeout(() => {
                initMap(containerId, null, null);
                setTimeout(() => {
                    updateMapWithDistrict(districtName, containerId);
                }, 300);
            }, 300);
            return;
        }

        // Clear existing boundaries
        if (window[containerId + 'Boundary']) {
            window[containerId + 'Boundary'].setMap(null);
        }

        // Fetch district boundaries (using mock data for demo)
        fetchDistrictBoundary(districtName)
            .then(boundary => {
                // Create polygon for district boundary
                const districtPolygon = new google.maps.Polygon({
                    paths: boundary,
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.1
                });

                districtPolygon.setMap(map);

                // Store boundary instance
                window[containerId + 'Boundary'] = districtPolygon;

                // Fit map to boundary
                const bounds = new google.maps.LatLngBounds();
                boundary.forEach(point => {
                    bounds.extend(point);
                });
                map.fitBounds(bounds);

                // Restrict marker placement to within boundary
                map.addListener('click', function(e) {
                    if (google.maps.geometry.poly.containsLocation(e.latLng, districtPolygon)) {
                        placeMarker(e.latLng, map, containerId);
                    } else {
                        alert('Please select a location within the selected district');
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching district boundary:', error);
            });
    }

    // Fetch district boundary (mock function)
    function fetchDistrictBoundary(districtName) {
        // In a real application, this would fetch from a GeoJSON API
        return new Promise((resolve) => {
            // Mock boundaries for demo purposes
            const mockBoundaries = {
                'Kampala': [{
                        lat: 0.3476,
                        lng: 32.5825
                    },
                    {
                        lat: 0.3476,
                        lng: 32.6525
                    },
                    {
                        lat: 0.2976,
                        lng: 32.6525
                    },
                    {
                        lat: 0.2976,
                        lng: 32.5825
                    }
                ],
                'Jinja': [{
                        lat: 0.4476,
                        lng: 33.1825
                    },
                    {
                        lat: 0.4476,
                        lng: 33.2525
                    },
                    {
                        lat: 0.3976,
                        lng: 33.2525
                    },
                    {
                        lat: 0.3976,
                        lng: 33.1825
                    }
                ],
                'Mbale': [{
                        lat: 1.0476,
                        lng: 34.1825
                    },
                    {
                        lat: 1.0476,
                        lng: 34.2525
                    },
                    {
                        lat: 0.9976,
                        lng: 34.2525
                    },
                    {
                        lat: 0.9976,
                        lng: 34.1825
                    }
                ],
                'Wakiso': [{
                        lat: 0.4476,
                        lng: 32.4825
                    },
                    {
                        lat: 0.4476,
                        lng: 32.5525
                    },
                    {
                        lat: 0.3976,
                        lng: 32.5525
                    },
                    {
                        lat: 0.3976,
                        lng: 32.4825
                    }
                ]
            };

            // Return boundary for selected district or default square if not found
            const boundary = mockBoundaries[districtName] || [{
                    lat: 1.3733 - 0.5,
                    lng: 32.2903 - 0.5
                },
                {
                    lat: 1.3733 - 0.5,
                    lng: 32.2903 + 0.5
                },
                {
                    lat: 1.3733 + 0.5,
                    lng: 32.2903 + 0.5
                },
                {
                    lat: 1.3733 + 0.5,
                    lng: 32.2903 - 0.5
                }
            ];

            resolve(boundary);
        });
    }

    // Reverse geocode coordinates to address
    function reverseGeocode(lat, lng, inputId) {
        const geocoder = new google.maps.Geocoder();
        const latlng = {
            lat,
            lng
        };

        geocoder.geocode({
            location: latlng
        }, function(results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    document.getElementById(inputId).value = results[0].formatted_address;
                }
            }
        });
    }

    function openEditModal(storeId) {
        // In a real application, you would fetch the store data from the server
        // For this example, we'll just populate with dummy data
        $('#editStoreId').val(storeId);
        $('#editStoreName').val('Store #' + storeId);
        $('#editStoreDescription').val('This is a sample store description.');
        $('#editStoreDistrict').val('0001');
        $('#editStoreAddress').val('123 Main Street');
        $('#editStorePhone').val('0772123456');
        $('#editStoreEmail').val('store' + storeId + '@example.com');

        showModal('editStoreModal');

        // Initialize map after modal is shown
        setTimeout(() => {
            initMap('editMapContainer', 0.3476, 32.5825);
        }, 300);
    }

    function resetCreateStoreForm() {
        // Reset step 1
        $('#businessName, #businessEmail, #contactNumber, #address').val('');
        $('#district, #natureOfOperation').val('');
        $('#latitude, #longitude').val('');

        // Reset step 2
        $('input[type="checkbox"]').prop('checked', false);

        // Reset step 3
        $('#productsList').html(`
            <div class="product-item border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-3">
                    <h5 class="font-medium">Product 1</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" placeholder="Enter product name" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select class="product-category w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select Category</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (UGX)</label>
                        <input type="number" placeholder="Enter price" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                </div>
            </div>
        `);

        // Reset indicators
        $('#step1').removeClass('hidden');
        $('#step2, #step3').addClass('hidden');

        $('#step1Indicator').removeClass('bg-green-500').addClass('bg-user-primary');
        $('#step1Indicator').text('1');
        $('#step2Indicator, #step3Indicator').removeClass('bg-user-primary bg-green-500 text-white').addClass('bg-gray-200 text-gray-500');
        $('#step2Indicator').text('2');
        $('#step3Indicator').text('3');
        $('#step1to2Line, #step2to3Line').removeClass('bg-green-500').addClass('bg-gray-200');

        // Clear map
        if (window.mapContainerMarker) {
            window.mapContainerMarker.setMap(null);
        }
        if (window.mapContainerBoundary) {
            window.mapContainerBoundary.setMap(null);
        }
    }

    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function showSuccessNotification(message) {
        let notification = document.getElementById('successNotification');

        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'successNotification';
            notification.className = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span id="successMessage"></span>
                </div>
            `;
            document.body.appendChild(notification);
        }

        document.getElementById('successMessage').textContent = message;
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