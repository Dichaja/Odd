<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Vendors';
$activeNav = 'manage-vendors';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Manage Vendors</h1>
            <p class="text-sm text-gray-text mt-1">View, verify and manage all vendor accounts</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="sendNotificationBtn" class="h-10 px-4 bg-success text-white rounded-lg hover:bg-success/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-bell"></i>
                <span>Send Notification</span>
            </button>
            <a href="vendor-categories" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
        </div>
    </div>

    <!-- Vendors Filter Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Vendor List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="vendor-count">5</span> vendors found <span class="text-sm text-gray-text">, Transporter</span>
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchVendors" placeholder="Search vendors..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <label for="sortVendors" class="text-sm text-gray-700 whitespace-nowrap">Sort By:</label>
                    <select id="sortVendors" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>Select</option>
                        <option value="last_login">Last Login</option>
                        <option value="latest">Latest</option>
                        <option value="verify">Verified</option>
                        <option value="pending">Pending</option>
                        <option value="suspend">Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div id="filterPanel" class="px-6 py-4 border-b border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filterCategory" class="block text-sm font-medium text-gray-700 mb-1">Vendor Category</label>
                    <select id="filterCategory" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Categories</option>
                        <option value="Transporter" selected>Transporter</option>
                        <option value="Supplier">Supplier</option>
                        <option value="Manufacturer">Manufacturer</option>
                        <option value="Distributor">Distributor</option>
                        <option value="Retailer">Retailer</option>
                    </select>
                </div>
                <div>
                    <label for="filterLocation" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <select id="filterLocation" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Locations</option>
                        <option value="Kampala">Kampala</option>
                        <option value="Wakiso">Wakiso</option>
                        <option value="Mukono">Mukono</option>
                        <option value="Mbale">Mbale</option>
                    </select>
                </div>
                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="filterStatus" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="verified">Verified</option>
                        <option value="pending">Pending</option>
                        <option value="suspended">Suspended</option>
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

        <!-- Select All Action -->
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="selectAll" class="form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700">Select All Vendors</span>
                </label>
                <span class="ml-2 text-xs text-gray-500" id="selectedCount">(0 selected)</span>
            </div>
            <div class="mt-3 md:mt-0">
                <button id="bulkActionBtn" class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors" disabled>
                    Bulk Actions <i class="fas fa-chevron-down ml-1"></i>
                </button>
            </div>
        </div>

        <!-- Vendors List -->
        <div id="vendors-list" class="divide-y divide-gray-100">
            <!-- Vendor 1 -->
            <div class="vendor-item p-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex items-start">
                        <input type="checkbox" name="vendorSelect[]" value="01-066845" class="vendor-checkbox form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary mt-1">
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-secondary">Yasin Elf Sekiwunga</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Transporter
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i> Last login: 1 year(s) ago
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Business Contact:</span>
                                <p class="font-medium">+256700883798</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="font-medium text-gray-400">Not provided</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <p class="font-medium">Wakiso, Kajjansi, Sekiwunga</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Registered:</span>
                                <p class="font-medium">Jan 06, 2024</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Products in Store:</span>
                                <p class="font-medium">2</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <p class="font-medium">YasinElf</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Product Categories:</span>
                                <p class="font-medium">Transport for Hire</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm" onclick="window.open('../vendor-profile?account_no=01-066845', '_blank')">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <button class="px-3 py-1.5 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm vendor-manage-btn" data-id="01-066845" data-status="00">
                                <i class="fas fa-cog mr-1"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor 2 -->
            <div class="vendor-item p-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex items-start">
                        <input type="checkbox" name="vendorSelect[]" value="0222-3766" class="vendor-checkbox form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary mt-1">
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-secondary">Lweera sand suppliers</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Transporter
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i> Last login: 14 day(s) ago
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Business Contact:</span>
                                <p class="font-medium text-gray-400">Not provided</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="font-medium text-gray-400">Not provided</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <p class="font-medium">Kampala, Central Division, Old kampala</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Registered:</span>
                                <p class="font-medium">Feb 22, 2025</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Products in Store:</span>
                                <p class="font-medium">1</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <p class="font-medium">Ssebulim</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Product Categories:</span>
                                <p class="font-medium">Building WARE, Concrete Products, Construction TOOLS, Earth Materials, Stone Products</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm" onclick="window.location.href='vendor_profile.php?account_no=0222-3766'">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <button class="px-3 py-1.5 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm vendor-manage-btn" data-id="0222-3766" data-status="00">
                                <i class="fas fa-cog mr-1"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor 3 -->
            <div class="vendor-item p-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex items-start">
                        <input type="checkbox" name="vendorSelect[]" value="12-218195" class="vendor-checkbox form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary mt-1">
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-secondary">Lwera sand suppliers</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Transporter
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Verified
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i> Last login: 1 year(s) ago
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Business Contact:</span>
                                <p class="font-medium">+256700764136</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="font-medium text-gray-400">Not provided</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <p class="font-medium">Kampala, Rubaga, Namungoona</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Registered:</span>
                                <p class="font-medium">Dec 21, 2023</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Products in Store:</span>
                                <p class="font-medium">0</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <p class="font-medium">Franklamar</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Product Categories:</span>
                                <p class="font-medium">Building WARE, Concrete Products, Construction TOOLS, Hardware Materials, Paints & Binders</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm" onclick="window.location.href='vendor_profile.php?account_no=12-218195'">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <button class="px-3 py-1.5 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm vendor-manage-btn" data-id="12-218195" data-status="02">
                                <i class="fas fa-cog mr-1"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor 4 -->
            <div class="vendor-item p-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex items-start">
                        <input type="checkbox" name="vendorSelect[]" value="1365980" class="vendor-checkbox form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary mt-1">
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-secondary">Wanambwa Geofrey</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Transporter
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i> Last login: 1 year(s) ago
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Business Contact:</span>
                                <p class="font-medium">+256757608013</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="font-medium">wanambwageoffrey@gmail.com</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <p class="font-medium">Mukono, Seeta, Naabuta</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Registered:</span>
                                <p class="font-medium">Sep 26, 2021</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Products in Store:</span>
                                <p class="font-medium">12</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <p class="font-medium">Wanambwa Geofrey</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Product Categories:</span>
                                <p class="font-medium">Earth Materials, Hardware Materials</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm" onclick="window.location.href='vendor_profile.php?account_no=1365980'">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <button class="px-3 py-1.5 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm vendor-manage-btn" data-id="1365980" data-status="00">
                                <i class="fas fa-cog mr-1"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vendor 5 -->
            <div class="vendor-item p-6">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex items-start">
                        <input type="checkbox" name="vendorSelect[]" value="1365983" class="vendor-checkbox form-checkbox h-5 w-5 text-primary rounded border-gray-300 focus:ring-primary mt-1">
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-secondary">Mbale transporters and equipment hire</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Transporter
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Verified
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                <i class="fas fa-clock mr-1"></i> Last login: 1 year(s) ago
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Business Contact:</span>
                                <p class="font-medium">+256700883798</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Email:</span>
                                <p class="font-medium text-gray-400">Not provided</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <p class="font-medium">Mbale, Mbale city, Industrial area</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Registered:</span>
                                <p class="font-medium">Sep 08, 2021</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Products in Store:</span>
                                <p class="font-medium">11</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Username:</span>
                                <p class="font-medium">Freelance Trader</p>
                            </div>
                            <div class="md:col-span-2">
                                <span class="text-gray-500">Product Categories:</span>
                                <p class="font-medium">Transport for Hire, Plant & Equipment for Hire</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm" onclick="window.location.href='vendor_profile.php?account_no=1365983'">
                                <i class="fas fa-eye mr-1"></i> View Details
                            </button>
                            <button class="px-3 py-1.5 bg-white border border-primary text-primary rounded-lg hover:bg-primary/5 transition-colors text-sm vendor-manage-btn" data-id="1365983" data-status="02">
                                <i class="fas fa-cog mr-1"></i> Manage
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="p-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center">
            <div class="text-sm text-gray-500 mb-4 md:mb-0">
                Showing <span id="showing-start">1</span> to <span id="showing-end">5</span> of <span id="total-vendors">5</span> vendors
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div id="notificationModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideNotificationModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Send Notification</h3>
            <button onclick="hideNotificationModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="notificationForm" class="space-y-6">
                <div>
                    <label for="notification-recipients" class="block text-sm font-medium text-gray-700 mb-1">Recipients</label>
                    <select id="notification-recipients" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="selected">Selected Vendors</option>
                        <option value="all">All Vendors</option>
                        <option value="verified">Verified Vendors</option>
                        <option value="pending">Pending Vendors</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="recipient-count">0</span> vendors will receive this notification
                    </p>
                </div>

                <div>
                    <label for="notification-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" id="notification-subject" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter notification subject">
                </div>

                <div>
                    <label for="notification-message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea id="notification-message" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter notification message"></textarea>
                </div>

                <div>
                    <label for="notification-type" class="block text-sm font-medium text-gray-700 mb-1">Notification Type</label>
                    <select id="notification-type" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="info">Information</option>
                        <option value="warning">Warning</option>
                        <option value="important">Important</option>
                        <option value="update">System Update</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideNotificationModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="sendNotification" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Send Notification
            </button>
        </div>
    </div>
</div>

<!-- Vendor Management Modal -->
<div id="vendorManageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideVendorManageModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="manage-vendor-title">Manage Vendor</h3>
            <button onclick="hideVendorManageModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div id="vendor-details" class="mb-6">
                <!-- Vendor details will be populated here -->
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Account Status</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" name="vendor-status" value="pending" class="form-radio h-4 w-4 text-primary">
                            <span class="ml-2 text-sm text-gray-700">Pending</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="vendor-status" value="verified" class="form-radio h-4 w-4 text-primary">
                            <span class="ml-2 text-sm text-gray-700">Verified</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="vendor-status" value="suspended" class="form-radio h-4 w-4 text-primary">
                            <span class="ml-2 text-sm text-gray-700">Suspended</span>
                        </label>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Actions</h4>
                    <div class="space-y-3">
                        <button class="w-full px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm text-left">
                            <i class="fas fa-envelope mr-2"></i> Send Email
                        </button>
                        <button class="w-full px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm text-left">
                            <i class="fas fa-comment-alt mr-2"></i> Send Message
                        </button>
                        <button class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm text-left">
                            <i class="fas fa-ban mr-2"></i> Block Vendor
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <label for="admin-note" class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                <textarea id="admin-note" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Add a note about this vendor"></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideVendorManageModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="saveVendorChanges" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Bulk Action Menu -->
<div id="bulkActionMenu" class="hidden absolute bg-white rounded-lg shadow-lg border border-gray-200 w-48 z-40">
    <ul class="py-2">
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-check-circle mr-2 text-green-500"></i> Verify Selected
        </li>
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-times-circle mr-2 text-yellow-500"></i> Mark as Pending
        </li>
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-ban mr-2 text-red-500"></i> Suspend Selected
        </li>
        <li class="border-t border-gray-100"></li>
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-envelope mr-2 text-blue-500"></i> Send Email
        </li>
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-bell mr-2 text-purple-500"></i> Send Notification
        </li>
        <li class="border-t border-gray-100"></li>
        <li class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
            <i class="fas fa-trash-alt mr-2 text-red-500"></i> Delete Selected
        </li>
    </ul>
</div>

<script>
    // Sample vendor data - in a real application, this would come from an API
    const vendors = [{
            id: "01-066845",
            name: "Yasin Elf Sekiwunga",
            category: "Transporter",
            contact: "+256700883798",
            email: "",
            location: "Wakiso, Kajjansi, Sekiwunga",
            registered: "Jan/06/2024",
            productsCount: 2,
            productCategories: "Transport for Hire",
            lastLogin: "1 year(s)",
            username: "YasinElf",
            status: "00" // Pending
        },
        {
            id: "0222-3766",
            name: "Lweera sand suppliers",
            category: "Transporter",
            contact: "",
            email: "",
            location: "Kampala, Central Division, Old kampala",
            registered: "Feb/22/2025",
            productsCount: 1,
            productCategories: "Building WARE, Concrete Products, Construction TOOLS, Earth Materials, Stone Products",
            lastLogin: "14 day(s)",
            username: "Ssebulim",
            status: "00" // Pending
        },
        {
            id: "12-218195",
            name: "Lwera sand suppliers",
            category: "Transporter",
            contact: "+256700764136",
            email: "",
            location: "Kampala, Rubaga, Namungoona",
            registered: "Dec/21/2023",
            productsCount: 0,
            productCategories: "Building WARE, Concrete Products, Construction TOOLS, Hardware Materials, Paints & Binders",
            lastLogin: "1 year(s)",
            username: "Franklamar",
            status: "02" // Verified
        },
        {
            id: "1365980",
            name: "Wanambwa Geofrey",
            category: "Transporter",
            contact: "+256757608013",
            email: "wanambwageoffrey@gmail.com",
            location: "Mukono, Seeta, Naabuta",
            registered: "Sep/26/2021",
            productsCount: 12,
            productCategories: "Earth Materials, Hardware Materials",
            lastLogin: "1 year(s)",
            username: "Wanambwa Geofrey",
            status: "00" // Pending
        },
        {
            id: "1365983",
            name: "Mbale transporters and equipment hire",
            category: "Transporter",
            contact: "+256700883798",
            email: "",
            location: "Mbale, Mbale city, Industrial area",
            registered: "Sep/08/2021",
            productsCount: 11,
            productCategories: "Transport for Hire, Plant & Equipment for Hire",
            lastLogin: "1 year(s)",
            username: "Freelance Trader",
            status: "02" // Verified
        }
    ];

    // Get vendor status text and badge class
    function getVendorStatusInfo(status) {
        switch (status) {
            case "00":
                return {
                    text: "Pending", badgeClass: "bg-yellow-100 text-yellow-800"
                };
            case "01":
                return {
                    text: "Suspended", badgeClass: "bg-red-100 text-red-800"
                };
            case "02":
                return {
                    text: "Verified", badgeClass: "bg-green-100 text-green-800"
                };
            default:
                return {
                    text: "Unknown", badgeClass: "bg-gray-100 text-gray-800"
                };
        }
    }

    // Show notification modal
    function showNotificationModal() {
        const modal = document.getElementById('notificationModal');
        modal.classList.remove('hidden');

        // Count selected vendors
        const selectedCount = document.querySelectorAll('.vendor-checkbox:checked').length;
        document.getElementById('recipient-count').textContent = selectedCount > 0 ? selectedCount : 0;
    }

    // Hide notification modal
    function hideNotificationModal() {
        const modal = document.getElementById('notificationModal');
        modal.classList.add('hidden');
    }

    // Show vendor manage modal
    function showVendorManageModal(vendorId) {
        const modal = document.getElementById('vendorManageModal');
        const vendor = vendors.find(v => v.id === vendorId);

        if (vendor) {
            document.getElementById('manage-vendor-title').textContent = `Manage Vendor: ${vendor.name}`;

            // Populate vendor details
            const vendorDetails = document.getElementById('vendor-details');
            vendorDetails.innerHTML = `
      <div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex justify-between items-start mb-3">
          <h4 class="text-lg font-semibold text-gray-800">${vendor.name}</h4>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getVendorStatusInfo(vendor.status).badgeClass}">
            ${getVendorStatusInfo(vendor.status).text}
          </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-500">ID:</span>
            <span class="font-medium">${vendor.id}</span>
          </div>
          <div>
            <span class="text-gray-500">Category:</span>
            <span class="font-medium">${vendor.category}</span>
          </div>
          <div>
            <span class="text-gray-500">Contact:</span>
            <span class="font-medium">${vendor.contact || 'Not provided'}</span>
          </div>
          <div>
            <span class="text-gray-500">Username:</span>
            <span class="font-medium">${vendor.username}</span>
          </div>
          <div>
            <span class="text-gray-500">Products:</span>
            <span class="font-medium">${vendor.productsCount}</span>
          </div>
          <div>
            <span class="text-gray-500">Last Login:</span>
            <span class="font-medium">${vendor.lastLogin} ago</span>
          </div>
        </div>
      </div>
    `;

            // Set the radio button based on status
            const statusRadios = document.querySelectorAll('input[name="vendor-status"]');
            for (const radio of statusRadios) {
                if ((vendor.status === "00" && radio.value === "pending") ||
                    (vendor.status === "01" && radio.value === "suspended") ||
                    (vendor.status === "02" && radio.value === "verified")) {
                    radio.checked = true;
                    break;
                }
            }

            modal.classList.remove('hidden');
        }
    }

    // Hide vendor manage modal
    function hideVendorManageModal() {
        const modal = document.getElementById('vendorManageModal');
        modal.classList.add('hidden');
    }

    // Toggle bulk action menu
    function toggleBulkActionMenu() {
        const menu = document.getElementById('bulkActionMenu');
        const button = document.getElementById('bulkActionBtn');
        const buttonRect = button.getBoundingClientRect();

        if (menu.classList.contains('hidden')) {
            menu.style.top = `${buttonRect.bottom + window.scrollY + 5}px`;
            menu.style.left = `${buttonRect.left + window.scrollX}px`;
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Send notification button
        document.getElementById('sendNotificationBtn').addEventListener('click', function() {
            showNotificationModal();
        });

        // Send notification from modal
        document.getElementById('sendNotification').addEventListener('click', function() {
            // In a real application, you would send the notification to the selected vendors
            alert('Notification sent successfully!');
            hideNotificationModal();
        });

        // Vendor manage buttons
        document.querySelectorAll('.vendor-manage-btn').forEach(button => {
            button.addEventListener('click', function() {
                const vendorId = this.getAttribute('data-id');
                showVendorManageModal(vendorId);
            });
        });

        // Save vendor changes
        document.getElementById('saveVendorChanges').addEventListener('click', function() {
            // In a real application, you would update the vendor status and notes
            alert('Vendor changes saved successfully!');
            hideVendorManageModal();
        });

        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            const vendorCheckboxes = document.querySelectorAll('.vendor-checkbox');
            vendorCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });

            updateSelectedCount();
            updateBulkActionButton();
        });

        // Individual checkboxes
        document.querySelectorAll('.vendor-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount();
                updateBulkActionButton();

                // Check/uncheck "Select All" based on individual selections
                const allChecked = document.querySelectorAll('.vendor-checkbox:checked').length === document.querySelectorAll('.vendor-checkbox').length;
                document.getElementById('selectAll').checked = allChecked;
            });
        });

        // Bulk action button
        document.getElementById('bulkActionBtn').addEventListener('click', function() {
            if (!this.disabled) {
                toggleBulkActionMenu();
            }
        });

        // Close bulk action menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('bulkActionMenu');
            const button = document.getElementById('bulkActionBtn');

            if (!menu.contains(event.target) && !button.contains(event.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });

        // Bulk action menu items
        document.querySelectorAll('#bulkActionMenu li').forEach(item => {
            item.addEventListener('click', function() {
                const action = this.textContent.trim();
                const selectedVendors = Array.from(document.querySelectorAll('.vendor-checkbox:checked')).map(cb => cb.value);

                // In a real application, you would perform the selected action on the vendors
                alert(`Action: ${action}\nSelected vendors: ${selectedVendors.join(', ')}`);

                document.getElementById('bulkActionMenu').classList.add('hidden');
            });
        });

        // Search vendors
        document.getElementById('searchVendors').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            // In a real application, you would filter vendors based on the query
            if (query.length > 0) {
                console.log(`Searching for: ${query}`);
            }
        });

        // Apply filters
        document.getElementById('applyFilters').addEventListener('click', function() {
            const category = document.getElementById('filterCategory').value;
            const location = document.getElementById('filterLocation').value;
            const status = document.getElementById('filterStatus').value;

            // In a real application, you would filter vendors based on the selected criteria
            alert(`Filters applied:\nCategory: ${category || 'All'}\nLocation: ${location || 'All'}\nStatus: ${status || 'All'}`);
        });

        // Reset filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterCategory').value = '';
            document.getElementById('filterLocation').value = '';
            document.getElementById('filterStatus').value = '';

            // In a real application, you would reset the vendor list
            alert('Filters reset');
        });
    });

    // Update selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.vendor-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = `(${selectedCount} selected)`;

        // Also update notification recipient count if modal is open
        if (!document.getElementById('notificationModal').classList.contains('hidden')) {
            document.getElementById('recipient-count').textContent = selectedCount;
        }
    }

    // Update bulk action button state
    function updateBulkActionButton() {
        const bulkActionBtn = document.getElementById('bulkActionBtn');
        const selectedCount = document.querySelectorAll('.vendor-checkbox:checked').length;

        if (selectedCount > 0) {
            bulkActionBtn.disabled = false;
            bulkActionBtn.classList.remove('bg-gray-200', 'text-gray-700');
            bulkActionBtn.classList.add('bg-primary', 'text-white', 'hover:bg-primary/90');
        } else {
            bulkActionBtn.disabled = true;
            bulkActionBtn.classList.remove('bg-primary', 'text-white', 'hover:bg-primary/90');
            bulkActionBtn.classList.add('bg-gray-200', 'text-gray-700');
        }
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>