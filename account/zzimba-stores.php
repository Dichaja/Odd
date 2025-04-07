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

<!-- Create Store Modal - Two-step -->
<div id="createStoreModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('createStoreModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl bg-white rounded-lg shadow-lg max-h-[90vh] overflow-y-auto">
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

            <!-- Step 1: Basic Store Details -->
            <div id="step1" class="step-content">
                <h4 class="text-center font-medium text-secondary mb-4">Basic Store Details</h4>
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

            <!-- Step 2: Location Selection -->
            <div id="step2" class="step-content hidden">
                <h4 class="text-center font-medium text-secondary mb-4">Store Location</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left column: Map -->
                    <div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Location on Map *</label>
                            <div id="mapContainer" class="w-full h-64 rounded-lg border border-gray-200 mb-2"></div>
                            <p class="text-xs text-gray-500">Click within the selected region to drop a pin</p>
                        </div>

                        <div class="flex space-x-2 mb-4">
                            <button id="locateMeBtn" class="px-3 py-1 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition">
                                Find My Location
                            </button>
                            <select id="mapStyle" class="text-sm border rounded-md px-2 py-1">
                                <option value="osm">OpenStreetMap</option>
                                <option value="satellite">Satellite</option>
                                <option value="terrain">Terrain</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                                <input type="text" id="latitude" readonly class="w-full h-10 px-3 rounded-lg border border-gray-200 bg-gray-50">
                            </div>
                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                                <input type="text" id="longitude" readonly class="w-full h-10 px-3 rounded-lg border border-gray-200 bg-gray-50">
                            </div>
                        </div>
                    </div>

                    <!-- Right column: Administrative regions -->
                    <div>
                        <div class="space-y-4">
                            <div>
                                <label for="level1" class="block text-sm font-medium text-gray-700 mb-1">Region/Province *</label>
                                <div class="relative">
                                    <select id="level1" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                        <option value="">Select Region/Province</option>
                                    </select>
                                    <span id="loading1" class="hidden absolute right-2 top-2 text-sm text-gray-500">Loading...</span>
                                </div>
                            </div>

                            <div>
                                <label for="level2" class="block text-sm font-medium text-gray-700 mb-1">District *</label>
                                <div class="relative">
                                    <select id="level2" disabled class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                        <option value="">Select District</option>
                                    </select>
                                    <span id="loading2" class="hidden absolute right-2 top-2 text-sm text-gray-500">Loading...</span>
                                </div>
                            </div>

                            <div>
                                <label for="level3" class="block text-sm font-medium text-gray-700 mb-1">Sub-county</label>
                                <div class="relative">
                                    <select id="level3" disabled class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                        <option value="">Select Sub-county</option>
                                    </select>
                                    <span id="loading3" class="hidden absolute right-2 top-2 text-sm text-gray-500">Loading...</span>
                                </div>
                            </div>

                            <div>
                                <label for="level4" class="block text-sm font-medium text-gray-700 mb-1">Parish/Ward</label>
                                <div class="relative">
                                    <select id="level4" disabled class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                                        <option value="">Select Parish/Ward</option>
                                    </select>
                                    <span id="loading4" class="hidden absolute right-2 top-2 text-sm text-gray-500">Loading...</span>
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Physical Address *</label>
                                <input type="text" id="address" placeholder="Enter physical address" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <button type="button" id="step2BackBtn" class="w-24 h-10 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        BACK
                    </button>
                    <button type="button" id="step2NextBtn" class="w-24 h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        NEXT
                    </button>
                </div>
            </div>

            <!-- Step 3: Store Details -->
            <div id="step3" class="step-content hidden">
                <h4 class="text-center font-medium text-secondary mb-4">Store Details</h4>

                <div class="space-y-6">
                    <div>
                        <label for="storeDescription" class="block text-sm font-medium text-gray-700 mb-1">Store Description</label>
                        <textarea id="storeDescription" rows="4" placeholder="Brief description of your store" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Store Logo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i id="logoPlaceholder" class="fas fa-store text-gray-400 text-xl"></i>
                                <img id="logoPreview" class="w-full h-full object-cover rounded-lg hidden" src="#" alt="Logo preview">
                            </div>
                            <label for="storeLogo" class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                Upload Logo
                            </label>
                            <input type="file" id="storeLogo" accept="image/*" class="hidden">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Recommended size: 512×512 pixels. Max 2MB.</p>
                    </div>

                    <div>
                        <label for="storeWebsite" class="block text-sm font-medium text-gray-700 mb-1">Website (Optional)</label>
                        <input type="url" id="storeWebsite" placeholder="https://example.com" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>

                    <div>
                        <label for="storeSocialMedia" class="block text-sm font-medium text-gray-700 mb-1">Social Media (Optional)</label>
                        <input type="text" id="storeSocialMedia" placeholder="Facebook, Instagram, etc." class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                </div>

                <div class="flex justify-between mt-6">
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

<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>
<!-- Leaflet plugins for point-in-polygon -->
<script src="https://unpkg.com/leaflet-pip@1.1.0/leaflet-pip.js"></script>

<style>
    .location-icon {
        background-color: #ef4444;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    }

    .pulse {
        animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
        }
    }
</style>

<script>
    $(document).ready(function() {
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
                initMap();
                loadRegions();
            }, 300);
        });

        // Multi-step form navigation
        $('#step1NextBtn').click(function() {
            // Validate step 1
            const businessName = $('#businessName').val();
            const businessEmail = $('#businessEmail').val();
            const contactNumber = $('#contactNumber').val();
            const natureOfOperation = $('#natureOfOperation').val();

            if (!businessName || !businessEmail || !contactNumber || !natureOfOperation) {
                alert('Please fill in all required fields');
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

            // Refresh map in case it wasn't properly initialized
            setTimeout(() => {
                if (map) map.invalidateSize();
            }, 100);
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
            const latitude = $('#latitude').val();
            const longitude = $('#longitude').val();
            const level1 = $('#level1').val();
            const level2 = $('#level2').val();
            const address = $('#address').val();

            if (!latitude || !longitude || !level1 || !level2 || !address) {
                alert('Please select your location on the map and fill in all required fields');
                return;
            }

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

            // Refresh map in case it wasn't properly initialized
            setTimeout(() => {
                if (map) map.invalidateSize();
            }, 100);
        });

        $('#step3FinishBtn').click(function() {
            // Validate step 3 if needed
            // For now, we'll just proceed with submission

            showLoading();

            setTimeout(function() {
                hideLoading();
                hideModal('createStoreModal');
                showSuccessNotification('Store created successfully! Awaiting approval.');

                // Reset form
                resetCreateStoreForm();
            }, 1500);
        });

        // Add logo preview functionality
        $('#storeLogo').change(function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    $('#logoPreview').attr('src', e.target.result);
                    $('#logoPreview').removeClass('hidden');
                    $('#logoPlaceholder').addClass('hidden');
                }

                reader.readAsDataURL(e.target.files[0]);
            }
        });
    });

    // Map variables
    let map = null;
    let marker = null;
    let baseLayers = {};
    let currentLocation = null;
    let geoJSONLayer = null;
    let currentGeoJSON = null;
    let locationMarker = null;

    // Initialize map
    function initMap() {
        if (map) return; // Don't initialize if already exists

        // Create map centered on Uganda
        map = L.map('mapContainer').setView([1.3733, 32.2903], 7);

        // Define base layers
        baseLayers = {
            'osm': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }),
            'satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            }),
            'terrain': L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
            })
        };

        // Add default base layer
        baseLayers['osm'].addTo(map);

        // Add click event to map
        map.on('click', function(e) {
            handleMapClick(e);
        });

        // Try to get user's location
        locateUser();
    }

    // Change map style
    function changeMapStyle(style) {
        if (!map) return;

        // Remove all layers
        Object.values(baseLayers).forEach(layer => {
            if (map.hasLayer(layer)) {
                map.removeLayer(layer);
            }
        });

        // Add selected layer
        if (baseLayers[style]) {
            baseLayers[style].addTo(map);
        }
    }

    // Function to check if a point is inside a polygon
    function isPointInPolygon(point, geoJSON) {
        if (!geoJSON || !geoJSON.features || geoJSON.features.length === 0) return false;

        // Create a temporary layer for the point-in-polygon check
        const tempLayer = L.geoJSON(geoJSON);
        const results = leafletPip.pointInLayer([point.lng, point.lat], tempLayer);
        return results.length > 0;
    }

    // Handle map click for pin dropping
    function handleMapClick(e) {
        // Only allow pin dropping if a region is selected
        if (!currentGeoJSON || !currentGeoJSON.features || currentGeoJSON.features.length === 0) {
            alert('Please select a region first before dropping a pin.');
            return;
        }

        const point = e.latlng;

        // Check if the clicked point is inside the selected region
        if (!isPointInPolygon(point, currentGeoJSON)) {
            alert('Please click within the selected region to drop a pin.');
            return;
        }

        // Place marker at the clicked location
        placeMarker(point);
    }

    // Place marker on map
    function placeMarker(latlng) {
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }

        // Create new marker
        marker = L.marker(latlng, {
            draggable: true
        }).addTo(map);

        // Update form fields
        $('#latitude').val(latlng.lat.toFixed(6));
        $('#longitude').val(latlng.lng.toFixed(6));

        // Get address from coordinates
        reverseGeocode(latlng.lat, latlng.lng);

        // Add drag end event
        marker.on('dragend', function() {
            const newPos = marker.getLatLng();

            // Check if the new position is within the selected region
            if (!isPointInPolygon(newPos, currentGeoJSON)) {
                // If not, move the marker back to its previous position
                marker.setLatLng(latlng);
                alert('Please keep the marker within the selected region.');
                return;
            }

            // Update form fields with new position
            $('#latitude').val(newPos.lat.toFixed(6));
            $('#longitude').val(newPos.lng.toFixed(6));
            reverseGeocode(newPos.lat, newPos.lng);
        });
    }

    // Reverse geocode coordinates to address
    function reverseGeocode(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;

        fetch(url, {
                headers: {
                    'User-Agent': 'Zzimba Online Store Location Selector'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    $('#address').val(data.display_name);

                    // Try to find and set administrative regions
                    if (data.address) {
                        // This is a simplified approach - in a real app you'd need to match
                        // the returned address components with your specific region data
                        if (data.address.state || data.address.region) {
                            const region = data.address.state || data.address.region;
                            const regionOption = $(`#level1 option`).filter(function() {
                                return $(this).text().toLowerCase() === region.toLowerCase();
                            });

                            if (regionOption.length) {
                                $('#level1').val(regionOption.val()).trigger('change');
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error in reverse geocoding:', error);
            });
    }

    // Get user's location
    function locateUser() {
        if (!map) return;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Save current location
                    currentLocation = {
                        lat: lat,
                        lng: lng
                    };

                    // Center map on user's location
                    map.setView([lat, lng], 15);

                    // Create a custom icon for the location marker
                    const locationIcon = L.divIcon({
                        className: 'location-icon pulse',
                        html: '',
                        iconSize: [16, 16]
                    });

                    // Create a new marker at the user's location
                    if (locationMarker) {
                        map.removeLayer(locationMarker);
                    }

                    locationMarker = L.marker([lat, lng], {
                        icon: locationIcon,
                        zIndexOffset: 1000 // Ensure it's on top of other markers
                    }).addTo(map);

                    // Get address from coordinates
                    reverseGeocode(lat, lng);
                },
                function(error) {
                    console.error('Error getting location:', error);
                    alert('Unable to get your location. Please select your location manually on the map.');
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            alert('Geolocation is not supported by your browser');
        }
    }

    // Load administrative regions
    function loadRegions() {
        // In a real application, you would fetch this data from the server
        // For this example, we'll use mock data from the GADM dataset
        fetch('<?= BASE_URL ?>locations/gadm41_UGA_4.json')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load regions data');
                }
                return response.json();
            })
            .then(data => {
                // Extract unique level 1 regions (provinces)
                const level1Options = {};
                data.features.forEach(feature => {
                    const name = feature.properties.NAME_1;
                    if (name) {
                        level1Options[name] = name;
                    }
                });

                // Populate level 1 dropdown
                const level1Select = $('#level1');
                level1Select.html('<option value="">Select Region/Province</option>');

                Object.keys(level1Options).sort().forEach(region => {
                    level1Select.append(`<option value="${region}">${region}</option>`);
                });

                // Store the GeoJSON data for later use
                window.gadmData = data;

                // Add change event listeners
                level1Select.change(function() {
                    const region = $(this).val();
                    if (region) {
                        updateLevel2Options(region);
                        updateMap({
                            1: region
                        });
                    } else {
                        resetDropdown('level2');
                        resetDropdown('level3');
                        resetDropdown('level4');
                        clearMap();
                    }
                });

                $('#level2').change(function() {
                    const district = $(this).val();
                    const region = $('#level1').val();
                    if (district) {
                        updateLevel3Options(region, district);
                        updateMap({
                            1: region,
                            2: district
                        });
                    } else {
                        resetDropdown('level3');
                        resetDropdown('level4');
                        updateMap({
                            1: region
                        });
                    }
                });

                $('#level3').change(function() {
                    const subcounty = $(this).val();
                    const district = $('#level2').val();
                    const region = $('#level1').val();
                    if (subcounty) {
                        updateLevel4Options(region, district, subcounty);
                        updateMap({
                            1: region,
                            2: district,
                            3: subcounty
                        });
                    } else {
                        resetDropdown('level4');
                        updateMap({
                            1: region,
                            2: district
                        });
                    }
                });

                $('#level4').change(function() {
                    const parish = $(this).val();
                    const subcounty = $('#level3').val();
                    const district = $('#level2').val();
                    const region = $('#level1').val();
                    if (parish) {
                        updateMap({
                            1: region,
                            2: district,
                            3: subcounty,
                            4: parish
                        });
                    } else {
                        updateMap({
                            1: region,
                            2: district,
                            3: subcounty
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error loading regions:', error);
                alert('Failed to load administrative regions. Please try again later.');
            });
    }

    // Update level 2 options based on selected level 1
    function updateLevel2Options(region) {
        if (!window.gadmData) return;

        // Show loading indicator
        $('#loading2').removeClass('hidden');

        // Extract unique level 2 regions for the selected level 1
        const level2Options = {};
        window.gadmData.features.forEach(feature => {
            if (feature.properties.NAME_1 === region) {
                const name = feature.properties.NAME_2;
                if (name) {
                    level2Options[name] = name;
                }
            }
        });

        // Populate level 2 dropdown
        const level2Select = $('#level2');
        level2Select.html('<option value="">Select District</option>');

        Object.keys(level2Options).sort().forEach(district => {
            level2Select.append(`<option value="${district}">${district}</option>`);
        });

        // Enable the dropdown
        level2Select.prop('disabled', false);

        // Hide loading indicator
        $('#loading2').addClass('hidden');

        // Reset dependent dropdowns
        resetDropdown('level3');
        resetDropdown('level4');
    }

    // Update level 3 options based on selected level 1 and 2
    function updateLevel3Options(region, district) {
        if (!window.gadmData) return;

        // Show loading indicator
        $('#loading3').removeClass('hidden');

        // Extract unique level 3 regions for the selected level 1 and 2
        const level3Options = {};
        window.gadmData.features.forEach(feature => {
            if (feature.properties.NAME_1 === region && feature.properties.NAME_2 === district) {
                const name = feature.properties.NAME_3;
                if (name) {
                    level3Options[name] = name;
                }
            }
        });

        // Populate level 3 dropdown
        const level3Select = $('#level3');
        level3Select.html('<option value="">Select Sub-county</option>');

        Object.keys(level3Options).sort().forEach(subcounty => {
            level3Select.append(`<option value="${subcounty}">${subcounty}</option>`);
        });

        // Enable the dropdown
        level3Select.prop('disabled', false);

        // Hide loading indicator
        $('#loading3').addClass('hidden');

        // Reset dependent dropdown
        resetDropdown('level4');
    }

    // Update level 4 options based on selected level 1, 2, and 3
    function updateLevel4Options(region, district, subcounty) {
        if (!window.gadmData) return;

        // Show loading indicator
        $('#loading4').removeClass('hidden');

        // Extract unique level 4 regions for the selected level 1, 2, and 3
        const level4Options = {};
        window.gadmData.features.forEach(feature => {
            if (feature.properties.NAME_1 === region &&
                feature.properties.NAME_2 === district &&
                feature.properties.NAME_3 === subcounty) {
                const name = feature.properties.NAME_4;
                if (name) {
                    level4Options[name] = name;
                }
            }
        });

        // Populate level 4 dropdown
        const level4Select = $('#level4');
        level4Select.html('<option value="">Select Parish/Ward</option>');

        Object.keys(level4Options).sort().forEach(parish => {
            level4Select.append(`<option value="${parish}">${parish}</option>`);
        });

        // Enable the dropdown
        level4Select.prop('disabled', false);

        // Hide loading indicator
        $('#loading4').addClass('hidden');
    }

    // Update map with selected regions
    function updateMap(selections) {
        if (!window.gadmData || !map) return;

        // Remove existing GeoJSON layer if it exists
        if (geoJSONLayer) {
            map.removeLayer(geoJSONLayer);
            geoJSONLayer = null;
        }

        // Remove existing marker if it exists
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        // Filter features based on selections
        const filteredFeatures = window.gadmData.features.filter(feature => {
            let match = true;

            for (const [level, value] of Object.entries(selections)) {
                if (feature.properties[`NAME_${level}`] !== value) {
                    match = false;
                    break;
                }
            }

            return match;
        });

        if (filteredFeatures.length === 0) {
            currentGeoJSON = null;
            return;
        }

        // Create GeoJSON object with filtered features
        const filteredGeoJSON = {
            type: 'FeatureCollection',
            features: filteredFeatures
        };

        currentGeoJSON = filteredGeoJSON;

        // Add new GeoJSON layer with a distinct style
        geoJSONLayer = L.geoJSON(filteredGeoJSON, {
            style: {
                color: '#C00000',
                weight: 2,
                opacity: 1,
                fillColor: '#C00000',
                fillOpacity: 0.2
            }
        }).addTo(map);

        // Fit map to the bounds of the selected region
        map.fitBounds(geoJSONLayer.getBounds());

        // Check if current location is within the selected region
        if (currentLocation) {
            const isInRegion = isPointInPolygon(currentLocation, filteredGeoJSON);
            if (isInRegion) {
                // If current location is within region, place a marker there
                placeMarker(L.latLng(currentLocation.lat, currentLocation.lng));
            }
        }
    }

    // Clear map
    function clearMap() {
        if (!map) return;

        // Remove existing GeoJSON layer if it exists
        if (geoJSONLayer) {
            map.removeLayer(geoJSONLayer);
            geoJSONLayer = null;
        }

        // Remove existing marker if it exists
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        currentGeoJSON = null;
    }

    // Reset dropdown
    function resetDropdown(id) {
        $(`#${id}`).html('<option value="">Select option</option>').prop('disabled', true);
    }

    function resetCreateStoreForm() {
        // Reset step 1
        $('#businessName, #businessEmail, #contactNumber').val('');
        $('#natureOfOperation').val('');

        // Reset step 2
        $('#latitude, #longitude, #address').val('');
        $('#level1').val('');
        resetDropdown('level2');
        resetDropdown('level3');
        resetDropdown('level4');

        // Reset step 3
        $('#storeDescription').val('');
        $('#storeWebsite').val('');
        $('#storeSocialMedia').val('');
        $('#storeLogo').val('');
        $('#logoPreview').addClass('hidden').attr('src', '#');
        $('#logoPlaceholder').removeClass('hidden');

        // Reset indicators
        $('#step1').removeClass('hidden');
        $('#step2, #step3').addClass('hidden');

        $('#step1Indicator').removeClass('bg-green-500').addClass('bg-user-primary');
        $('#step1Indicator').text('1');
        $('#step2Indicator').removeClass('bg-green-500 bg-user-primary text-white').addClass('bg-gray-200 text-gray-500');
        $('#step2Indicator').text('2');
        $('#step3Indicator').removeClass('bg-user-primary text-white').addClass('bg-gray-200 text-gray-500');
        $('#step3Indicator').text('3');
        $('#step1to2Line, #step2to3Line').removeClass('bg-green-500').addClass('bg-gray-200');

        // Clear map
        clearMap();

        // Remove location marker
        if (locationMarker && map) {
            map.removeLayer(locationMarker);
            locationMarker = null;
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