<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Vendor Categories';
$activeNav = 'vendor-categories';
ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Vendor Categories</h1>
            <p class="text-sm text-gray-text mt-1">Manage categories for vendor classification</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="addNewCategory" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-success/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add New</span>
            </button>
            <a href="manage-vendors" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Vendors</span>
            </a>
        </div>
    </div>

    <!-- Categories Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Category List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="category-count">6</span> categories found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchCategories" placeholder="Search categories..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected>All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Replace the overflow-x-auto div and table with this responsive implementation -->
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="categories-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Category Name</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Vendors</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Description</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-32">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1001</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Transporter</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                28 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">Transportation service providers</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1001" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1001" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1002</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Supplier</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                42 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">General materials and goods suppliers</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1002" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1002" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1003</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Manufacturer</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                15 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">Product manufacturers and production companies</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1003" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1003" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1004</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Distributor</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                23 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">Distribution and wholesale businesses</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1004" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1004" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1005</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Retailer</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                19 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">Retail shops and stores</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1005" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1005" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-text">1006</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">Consultant</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                7 vendors
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">Professional consultancy services</td>
                        <td class="px-6 py-4 text-sm text-gray-text">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Inactive
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <button class="btn-edit text-blue-600 hover:text-blue-800" data-id="1006" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete text-red-600 hover:text-red-800" data-id="1006" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="responsive-table-mobile p-4" id="categories-mobile">
            <!-- Category 1 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Transporter</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1001</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            28 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">Transportation service providers</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1001">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1001">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category 2 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Supplier</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1002</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            42 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">General materials and goods suppliers</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1002">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1002">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category 3 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Manufacturer</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1003</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            15 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">Product manufacturers and production companies</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1003">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1003">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category 4 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Distributor</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1004</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            23 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">Distribution and wholesale businesses</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1004">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1004">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category 5 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Retailer</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1005</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            19 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">Retail shops and stores</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1005">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1005">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category 6 -->
            <div class="mobile-card mb-4">
                <div class="mobile-card-header">
                    <div>
                        <div class="font-medium text-gray-900">Consultant</div>
                        <div class="text-xs text-gray-500 mt-1">ID: 1006</div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            7 vendors
                        </span>
                    </div>
                </div>
                <div class="mobile-card-content">
                    <div class="mobile-grid mb-3">
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Description</span>
                            <span class="mobile-value">Professional consultancy services</span>
                        </div>
                        <div class="mobile-grid-item">
                            <span class="mobile-label">Status</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Inactive
                            </span>
                        </div>
                    </div>
                    <div class="mobile-actions">
                        <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="1006">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="1006">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">6</span> of <span id="total-categories">6</span> categories
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

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideCategoryModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="modal-title">Add New Category</h3>
            <button onclick="hideCategoryModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="categoryForm" class="space-y-4">
                <input type="hidden" id="category-id" value="">

                <div>
                    <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" id="category-name" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter category name" required>
                </div>

                <div>
                    <label for="category-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="category-description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter category description"></textarea>
                </div>

                <div>
                    <label for="category-slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" id="category-slug" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter category slug (optional)">
                    <p class="text-xs text-gray-500 mt-1">Used in URLs. Leave blank to auto-generate from name.</p>
                </div>

                <div>
                    <label for="category-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="category-status" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="category-icon" class="block text-sm font-medium text-gray-700 mb-1">Icon (Optional)</label>
                    <div class="flex items-center gap-3">
                        <div id="selected-icon" class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <button type="button" id="choose-icon" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                            Choose Icon
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideCategoryModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="saveCategory" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Save Category
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteCategoryModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Delete Category</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-2">Are you sure you want to delete this category?</p>
            <p class="text-yellow-600 text-sm mb-4">Warning: This may affect vendors currently assigned to this category.</p>

            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Category:</div>
                    <div class="font-medium text-gray-900" id="delete-category-name"></div>
                    <div class="text-gray-500">Vendors Affected:</div>
                    <div class="font-medium text-gray-900" id="delete-category-vendors"></div>
                </div>
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" id="delete-confirm" class="form-checkbox h-4 w-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <span class="ml-2 text-sm text-gray-700">I understand this action cannot be undone</span>
                </label>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-danger text-white rounded-lg hover:bg-danger/90 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Icon Selection Modal -->
<div id="iconModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideIconModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Select Icon</h3>
            <button onclick="hideIconModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <input type="text" id="icon-search" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Search icons...">
            </div>

            <div class="grid grid-cols-6 gap-3" id="icon-grid">
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-truck text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-store text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-industry text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-warehouse text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-shopping-basket text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-briefcase text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-box text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-home text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-tools text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-hammer text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-shipping-fast text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-dolly text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-tag text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-tags text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-user-tie text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-people-carry text-xl"></i>
                </div>
                <div class="icon-item p-3 border border-gray-200 rounded-lg flex items-center justify-center h-12 cursor-pointer hover:bg-gray-50 hover:border-primary transition-colors">
                    <i class="fas fa-handshake text-xl"></i>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideIconModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="selectIcon" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                Select
            </button>
        </div>
    </div>
</div>

<style>
    .responsive-table-mobile {
        display: none;
    }

    @media (max-width: 768px) {
        .responsive-table-desktop {
            display: none;
        }

        .responsive-table-mobile {
            display: block;
        }

        .mobile-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .mobile-card-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f3f4f6;
        }

        .mobile-card-content {
            padding: 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        .mobile-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #f3f4f6;
        }
    }
</style>

<script>
    // Sample category data - in a real application, this would come from an API
    const categories = [{
            id: "1001",
            name: "Transporter",
            vendorCount: 28,
            description: "Transportation service providers",
            status: "active",
            icon: "fa-truck"
        },
        {
            id: "1002",
            name: "Supplier",
            vendorCount: 42,
            description: "General materials and goods suppliers",
            status: "active",
            icon: "fa-store"
        },
        {
            id: "1003",
            name: "Manufacturer",
            vendorCount: 15,
            description: "Product manufacturers and production companies",
            status: "active",
            icon: "fa-industry"
        },
        {
            id: "1004",
            name: "Distributor",
            vendorCount: 23,
            description: "Distribution and wholesale businesses",
            status: "active",
            icon: "fa-warehouse"
        },
        {
            id: "1005",
            name: "Retailer",
            vendorCount: 19,
            description: "Retail shops and stores",
            status: "active",
            icon: "fa-shopping-basket"
        },
        {
            id: "1006",
            name: "Consultant",
            vendorCount: 7,
            description: "Professional consultancy services",
            status: "inactive",
            icon: "fa-briefcase"
        }
    ];

    // Show category modal for adding or editing
    function showCategoryModal(categoryId = null) {
        const modal = document.getElementById('categoryModal');
        const modalTitle = document.getElementById('modal-title');
        const form = document.getElementById('categoryForm');

        // Reset form
        form.reset();
        document.getElementById('category-id').value = '';
        document.getElementById('selected-icon').innerHTML = '<i class="fas fa-tag text-gray-400"></i>';

        if (categoryId) {
            // Edit mode
            const category = categories.find(c => c.id === categoryId);
            if (category) {
                modalTitle.textContent = 'Edit Category';

                document.getElementById('category-id').value = category.id;
                document.getElementById('category-name').value = category.name;
                document.getElementById('category-description').value = category.description;
                document.getElementById('category-slug').value = category.name.toLowerCase().replace(/\s+/g, '-');
                document.getElementById('category-status').value = category.status;

                // Set icon
                if (category.icon) {
                    document.getElementById('selected-icon').innerHTML = `<i class="fas ${category.icon} text-primary"></i>`;
                }
            }
        } else {
            // Add mode
            modalTitle.textContent = 'Add New Category';
        }

        modal.classList.remove('hidden');
    }

    // Hide category modal
    function hideCategoryModal() {
        const modal = document.getElementById('categoryModal');
        modal.classList.add('hidden');
    }

    // Show delete confirmation modal
    function showDeleteModal(categoryId) {
        const modal = document.getElementById('deleteCategoryModal');
        const category = categories.find(c => c.id === categoryId);

        if (category) {
            document.getElementById('delete-category-name').textContent = category.name;
            document.getElementById('delete-category-vendors').textContent = `${category.vendorCount} vendor(s)`;
            document.getElementById('confirmDelete').setAttribute('data-id', categoryId);

            // Reset checkbox and button state
            document.getElementById('delete-confirm').checked = false;
            document.getElementById('confirmDelete').disabled = true;

            modal.classList.remove('hidden');
        }
    }

    // Hide delete confirmation modal
    function hideDeleteModal() {
        const modal = document.getElementById('deleteCategoryModal');
        modal.classList.add('hidden');
    }

    // Show icon selection modal
    function showIconModal() {
        const modal = document.getElementById('iconModal');
        modal.classList.remove('hidden');
    }

    // Hide icon selection modal
    function hideIconModal() {
        const modal = document.getElementById('iconModal');
        modal.classList.add('hidden');
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Add New Category button
        document.getElementById('addNewCategory').addEventListener('click', function() {
            showCategoryModal();
        });

        // Edit buttons
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                showCategoryModal(categoryId);
            });
        });

        // Delete buttons
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                showDeleteModal(categoryId);
            });
        });

        // Choose icon button
        document.getElementById('choose-icon').addEventListener('click', function() {
            showIconModal();
        });

        // Icon selection
        document.querySelectorAll('.icon-item').forEach(item => {
            item.addEventListener('click', function() {
                const iconClass = this.querySelector('i').className;
                const selectedIcon = document.getElementById('selected-icon');

                // Set the selected icon
                selectedIcon.innerHTML = `<i class="${iconClass} text-primary"></i>`;

                // Highlight the selected icon
                document.querySelectorAll('.icon-item').forEach(i => i.classList.remove('border-primary', 'bg-primary/10'));
                this.classList.add('border-primary', 'bg-primary/10');
            });
        });

        // Select icon button
        document.getElementById('selectIcon').addEventListener('click', function() {
            hideIconModal();
        });

        // Delete confirmation checkbox
        document.getElementById('delete-confirm').addEventListener('change', function() {
            document.getElementById('confirmDelete').disabled = !this.checked;
        });

        // Save category
        document.getElementById('saveCategory').addEventListener('click', function() {
            const categoryId = document.getElementById('category-id').value;
            const categoryName = document.getElementById('category-name').value;

            if (!categoryName) {
                alert('Category name is required!');
                return;
            }

            // In a real application, you would send the form data to the server
            alert(`Category ${categoryId ? 'updated' : 'created'} successfully!`);
            hideCategoryModal();
        });

        // Confirm delete
        document.getElementById('confirmDelete').addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');

            // In a real application, you would send a delete request to the server
            alert(`Category ${categoryId} would be deleted here`);
            hideDeleteModal();
        });

        // Search categories
        document.getElementById('searchCategories').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            // In a real application, you would filter categories based on the query
            if (query.length > 0) {
                console.log(`Searching for: ${query}`);
            }
        });

        // Filter by status
        document.getElementById('filterStatus').addEventListener('change', function() {
            const status = this.value;

            // In a real application, you would filter categories based on the selected status
            if (status) {
                console.log(`Filtering by status: ${status}`);
            }
        });

        // Icon search
        document.getElementById('icon-search').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            // In a real application, you would filter icons based on the query
            if (query.length > 0) {
                console.log(`Searching for icons: ${query}`);
            }
        });

        // Generate slug from name
        document.getElementById('category-name').addEventListener('blur', function() {
            const slugField = document.getElementById('category-slug');

            // Only generate slug if field is empty
            if (slugField.value === '') {
                const nameValue = this.value.trim();
                if (nameValue) {
                    // Convert to lowercase, replace spaces with hyphens, remove special characters
                    const slug = nameValue.toLowerCase()
                        .replace(/\s+/g, '-')
                        .replace(/[^\w\-]+/g, '');

                    slugField.value = slug;
                }
            }
        });

        // Pagination
        document.getElementById('prev-page').addEventListener('click', function() {
            // In a real application, you would navigate to the previous page
            console.log('Navigating to previous page');
        });

        document.getElementById('next-page').addEventListener('click', function() {
            // In a real application, you would navigate to the next page
            console.log('Navigating to next page');
        });

        // Mobile view edit buttons
        document.querySelectorAll('.responsive-table-mobile .btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                showCategoryModal(categoryId);
            });
        });

        // Mobile view delete buttons
        document.querySelectorAll('.responsive-table-mobile .btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                showDeleteModal(categoryId);
            });
        });

        // Render mobile cards dynamically (for real implementation)
        function renderMobileCards(categoriesList) {
            const mobileContainer = document.getElementById('categories-mobile');
            mobileContainer.innerHTML = '';

            categoriesList.forEach(category => {
                const statusClass = category.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                const statusText = category.status === 'active' ? 'Active' : 'Inactive';

                const card = document.createElement('div');
                card.className = 'mobile-card mb-4';
                card.innerHTML = `
        <div class="mobile-card-header">
          <div>
            <div class="font-medium text-gray-900">${category.name}</div>
            <div class="text-xs text-gray-500 mt-1">ID: ${category.id}</div>
          </div>
          <div class="text-right">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
              ${category.vendorCount} vendors
            </span>
          </div>
        </div>
        <div class="mobile-card-content">
          <div class="mobile-grid mb-3">
            <div class="mobile-grid-item">
              <span class="mobile-label">Description</span>
              <span class="mobile-value">${category.description}</span>
            </div>
            <div class="mobile-grid-item">
              <span class="mobile-label">Status</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                ${statusText}
              </span>
            </div>
          </div>
          <div class="mobile-actions">
            <button class="btn-edit px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100" data-id="${category.id}">
              <i class="fas fa-edit mr-1"></i> Edit
            </button>
            <button class="btn-delete px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100" data-id="${category.id}">
              <i class="fas fa-trash-alt mr-1"></i> Delete
            </button>
          </div>
        </div>
      `;

                mobileContainer.appendChild(card);
            });

            // Re-attach event listeners
            document.querySelectorAll('.responsive-table-mobile .btn-edit').forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-id');
                    showCategoryModal(categoryId);
                });
            });

            document.querySelectorAll('.responsive-table-mobile .btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-id');
                    showDeleteModal(categoryId);
                });
            });
        }
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>