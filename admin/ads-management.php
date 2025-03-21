<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Ads Management';
$activeNav = 'ads-management';

// Function to format date
function formatDate($date)
{
    if (!$date) return '-';
    $timestamp = strtotime($date);

    $day = date('j', $timestamp);
    $suffix = '';

    if ($day % 10 == 1 && $day != 11) {
        $suffix = 'st';
    } elseif ($day % 10 == 2 && $day != 12) {
        $suffix = 'nd';
    } elseif ($day % 10 == 3 && $day != 13) {
        $suffix = 'rd';
    } else {
        $suffix = 'th';
    }

    return date('F ' . $day . $suffix . ', Y g:i A', $timestamp);
}

// Sample data - Replace with actual data from your database
$ads = [
    [
        'id' => 1,
        'title' => 'Summer Sale 2024',
        'status' => 'active',
        'created' => '2024-02-15 10:00:00',
        'expires' => '2024-03-15 23:59:59',
        'impressions' => 15420,
        'clicks' => 385,
        'redirect_url' => 'https://example.com/summer-sale',
        'image' => '/placeholder.svg?height=200&width=600',
        'description' => 'Promote our summer sale with special discounts on all products.'
    ],
    [
        'id' => 2,
        'title' => 'New Product Launch',
        'status' => 'scheduled',
        'created' => '2024-02-14 15:30:00',
        'expires' => '2024-04-01 23:59:59',
        'impressions' => 8750,
        'clicks' => 243,
        'redirect_url' => 'https://example.com/new-product',
        'image' => '/placeholder.svg?height=200&width=600',
        'description' => 'Announcing our latest product with exclusive features.'
    ],
    [
        'id' => 3,
        'title' => 'Holiday Special Offer',
        'status' => 'active',
        'created' => '2024-02-10 09:15:00',
        'expires' => '2024-03-10 23:59:59',
        'impressions' => 12680,
        'clicks' => 320,
        'redirect_url' => 'https://example.com/holiday-special',
        'image' => '/placeholder.svg?height=200&width=600',
        'description' => 'Special holiday discounts on selected items.'
    ],
];

ob_start();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Ads Management</h1>
            <p class="text-sm text-gray-text mt-1">Create and manage advertisements for your platform</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportAds" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="createAdBtn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Create New Ad</span>
            </button>
        </div>
    </div>

    <!-- Create Ad Form Card -->
    <div id="createAdCard" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 hidden">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-secondary">Create New Advertisement</h2>
            <button id="closeAdForm" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="createAdForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-text" for="adTitle">Advertisement Title</label>
                    <input type="text" id="adTitle" name="title" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter title" required>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-text" for="redirectUrl">Redirect URL</label>
                    <input type="url" id="redirectUrl" name="redirectUrl" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="https://" required>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-text" for="expiryDate">Expiry Date</label>
                    <input type="datetime-local" id="expiryDate" name="expiryDate" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" required>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-text mb-2 block">Status</label>
                    <div class="flex items-center space-x-2">
                        <button type="button" role="switch" aria-checked="true" id="activeStatus" class="relative inline-flex h-6 w-11 items-center rounded-full bg-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
                        </button>
                        <span class="text-sm text-gray-text" id="statusLabel">Active</span>
                    </div>
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-gray-text" for="adDescription">Description</label>
                    <textarea id="adDescription" name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter advertisement description"></textarea>
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-gray-text" for="adImage">Advertisement Image</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-lg">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="file-upload" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB (Recommended size: 600×200)</p>
                            <div id="image-preview" class="hidden mt-4">
                                <img src="/placeholder.svg" alt="Preview" class="mx-auto max-h-40 rounded">
                                <button type="button" id="remove-image" class="mt-2 text-xs text-red-600 hover:text-red-800">Remove image</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" id="cancelAdForm" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                    Create Advertisement
                </button>
            </div>
        </form>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-text">Total Impressions</h3>
                <span class="text-blue-600">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            <p class="text-2xl font-semibold text-secondary">
                <?= number_format(array_sum(array_column($ads, 'impressions'))) ?>
            </p>
            <div class="mt-2 text-xs flex items-center">
                <span class="text-green-600 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> 12%
                </span>
                <span class="text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-text">Total Clicks</h3>
                <span class="text-green-600">
                    <i class="fas fa-mouse-pointer"></i>
                </span>
            </div>
            <p class="text-2xl font-semibold text-secondary">
                <?= number_format(array_sum(array_column($ads, 'clicks'))) ?>
            </p>
            <div class="mt-2 text-xs flex items-center">
                <span class="text-green-600 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> 8%
                </span>
                <span class="text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-text">Average CTR</h3>
                <span class="text-yellow-600">
                    <i class="fas fa-percentage"></i>
                </span>
            </div>
            <?php
            $totalImpressions = array_sum(array_column($ads, 'impressions'));
            $totalClicks = array_sum(array_column($ads, 'clicks'));
            $avgCTR = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
            ?>
            <p class="text-2xl font-semibold text-secondary"><?= $avgCTR ?>%</p>
            <div class="mt-2 text-xs flex items-center">
                <span class="text-green-600 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> 5%
                </span>
                <span class="text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>
    </div>

    <!-- Ads Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Active Advertisements</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="ads-count"><?= count($ads) ?></span> advertisements found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchAds" placeholder="Search ads..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="filterStatus" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected="selected">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Ads Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="adsTable">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Title</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Created</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Expires</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Impressions</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Clicks</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">CTR</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ads as $ad):
                        $ctr = $ad['impressions'] > 0 ? round(($ad['clicks'] / $ad['impressions']) * 100, 2) : 0;
                    ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-ad-id="<?= $ad['id'] ?>">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="<?= BASE_URL . $ad['image'] ?>" alt="" class="w-12 h-8 rounded object-cover">
                                    <span class="font-medium text-secondary"><?= htmlspecialchars($ad['title']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $ad['status'] === 'active' ? 'bg-green-100 text-green-800' : ($ad['status'] === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst(htmlspecialchars($ad['status'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($ad['created']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($ad['expires']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= number_format($ad['impressions']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= number_format($ad['clicks']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= $ctr ?>%
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="action-btn text-blue-600 hover:text-blue-800"
                                        data-tippy-content="View Details"
                                        onclick="showAdDetails(<?= $ad['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <button
                                        class="action-btn text-green-600 hover:text-green-800"
                                        data-tippy-content="Edit Ad"
                                        onclick="editAd(<?= $ad['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button
                                        class="action-btn text-red-600 hover:text-red-800"
                                        data-tippy-content="Delete Ad"
                                        onclick="confirmDeleteAd(<?= $ad['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Ads List -->
        <div class="md:hidden p-4 space-y-4">
            <?php foreach ($ads as $ad):
                $ctr = $ad['impressions'] > 0 ? round(($ad['clicks'] / $ad['impressions']) * 100, 2) : 0;
            ?>
                <div class="mobile-row bg-white rounded-lg shadow-sm border border-gray-100" data-ad-id="<?= $ad['id'] ?>">
                    <div class="mobile-row-header p-4 flex justify-between items-center cursor-pointer">
                        <div class="flex items-center gap-3">
                            <img src="<?= BASE_URL . $ad['image'] ?>" alt="" class="w-12 h-8 rounded object-cover">
                            <span class="font-medium text-secondary"><?= htmlspecialchars($ad['title']) ?></span>
                        </div>
                        <svg class="accordion-arrow w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="accordion-content hidden">
                        <div class="p-4 pt-0">
                            <div class="mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $ad['status'] === 'active' ? 'bg-green-100 text-green-800' : ($ad['status'] === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= ucfirst(htmlspecialchars($ad['status'])) ?>
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div>
                                    <span class="text-xs text-gray-500">Created</span>
                                    <p class="text-sm"><?= formatDate($ad['created']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Expires</span>
                                    <p class="text-sm"><?= formatDate($ad['expires']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Impressions</span>
                                    <p class="text-sm"><?= number_format($ad['impressions']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Clicks</span>
                                    <p class="text-sm"><?= number_format($ad['clicks']) ?></p>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">CTR</span>
                                    <p class="text-sm"><?= $ctr ?>%</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="showAdDetails(<?= $ad['id'] ?>)" class="flex-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-eye mr-1"></i> View
                                </button>
                                <button onclick="editAd(<?= $ad['id'] ?>)" class="flex-1 bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button onclick="confirmDeleteAd(<?= $ad['id'] ?>)" class="flex-1 bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($ads) ?></span> of <span id="total-ads"><?= count($ads) ?></span> advertisements
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

<!-- Ad Details Offcanvas -->
<div id="adDetailsOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideAdDetails()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-2xl bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Advertisement Details</h3>
                <button onclick="hideAdDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div id="adDetailsContent" class="space-y-6"></div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteConfirmationModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-exclamation-triangle text-4xl text-red-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Are you sure you want to delete this advertisement?</h3>
                <p class="text-sm text-gray-500 mt-2">This action cannot be undone.</p>
            </div>
            <div class="flex justify-center gap-3 mt-6">
                <button onclick="hideDeleteConfirmationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="deleteAd()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage">Operation completed successfully!</span>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Processing...</p>
    </div>
</div>

<style>
    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }

    .action-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .accordion-arrow {
        transition: transform 0.2s ease;
    }

    .accordion-arrow.active {
        transform: rotate(180deg);
    }

    @media (max-width: 768px) {
        .overflow-x-auto {
            margin: 0 -1rem;
        }

        table {
            min-width: 800px;
        }
    }
</style>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        tippy('[data-tippy-content]', {
            placement: 'top',
            arrow: true,
            theme: 'light',
        });

        // Set default expiry date (7 days from now)
        const defaultExpiry = new Date();
        defaultExpiry.setDate(defaultExpiry.getDate() + 7);
        document.getElementById('expiryDate').value = formatDateTimeForInput(defaultExpiry);

        // Toggle create ad form
        const createAdBtn = document.getElementById('createAdBtn');
        const createAdCard = document.getElementById('createAdCard');
        const closeAdForm = document.getElementById('closeAdForm');
        const cancelAdForm = document.getElementById('cancelAdForm');

        createAdBtn.addEventListener('click', function() {
            createAdCard.classList.remove('hidden');
            document.getElementById('adTitle').focus();
        });

        closeAdForm.addEventListener('click', function() {
            createAdCard.classList.add('hidden');
            resetForm();
        });

        cancelAdForm.addEventListener('click', function() {
            createAdCard.classList.add('hidden');
            resetForm();
        });

        // Toggle status switch
        const toggleButton = document.getElementById('activeStatus');
        const statusLabel = document.getElementById('statusLabel');

        toggleButton.addEventListener('click', function() {
            const isActive = toggleButton.getAttribute('aria-checked') === 'true';
            toggleButton.setAttribute('aria-checked', String(!isActive));
            toggleButton.querySelector('span').classList.toggle('translate-x-6');
            toggleButton.classList.toggle('bg-gray-200');
            toggleButton.classList.toggle('bg-primary');
            statusLabel.textContent = isActive ? 'Inactive' : 'Active';
        });

        // File upload preview
        const fileUpload = document.getElementById('file-upload');
        const imagePreview = document.getElementById('image-preview');
        const previewImage = imagePreview.querySelector('img');
        const removeImageBtn = document.getElementById('remove-image');

        fileUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };

                reader.readAsDataURL(file);
            }
        });

        removeImageBtn.addEventListener('click', function() {
            fileUpload.value = '';
            imagePreview.classList.add('hidden');
        });

        // Form submission
        const createAdForm = document.getElementById('createAdForm');

        createAdForm.addEventListener('submit', function(e) {
            e.preventDefault();

            showLoading();

            // Simulate form submission
            setTimeout(() => {
                hideLoading();
                createAdCard.classList.add('hidden');
                resetForm();
                showSuccessNotification('Advertisement created successfully!');
            }, 1500);
        });

        // Mobile accordion
        const mobileRows = document.querySelectorAll('.mobile-row-header');

        mobileRows.forEach(row => {
            row.addEventListener('click', function() {
                const content = this.nextElementSibling;
                const arrow = this.querySelector('.accordion-arrow');

                if (content.classList.contains('hidden')) {
                    // Close all other accordions
                    document.querySelectorAll('.accordion-content').forEach(item => {
                        if (item !== content) {
                            item.classList.add('hidden');
                        }
                    });

                    document.querySelectorAll('.accordion-arrow').forEach(item => {
                        if (item !== arrow) {
                            item.classList.remove('active');
                        }
                    });

                    // Open this accordion
                    content.classList.remove('hidden');
                    arrow.classList.add('active');
                } else {
                    content.classList.add('hidden');
                    arrow.classList.remove('active');
                }
            });
        });

        // Search functionality
        document.getElementById('searchAds').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            filterAds(query, document.getElementById('filterStatus').value);
        });

        // Status filter
        document.getElementById('filterStatus').addEventListener('change', function(e) {
            const status = e.target.value;
            filterAds(document.getElementById('searchAds').value.toLowerCase(), status);
        });

        // Export button
        document.getElementById('exportAds').addEventListener('click', function() {
            showSuccessNotification('Ads data exported successfully!');
        });
    });

    // Current ad ID for deletion
    let currentAdId = null;

    // Show ad details
    function showAdDetails(adId) {
        const offcanvas = document.getElementById('adDetailsOffcanvas');
        const content = document.getElementById('adDetailsContent');

        // Get ad data - in a real app, you would fetch this from the server
        const ad = getAdById(adId);

        if (!ad) return;

        const ctr = ad.impressions > 0 ? ((ad.clicks / ad.impressions) * 100).toFixed(2) : '0.00';

        content.innerHTML = `
        <div class="space-y-6">
            <img src="${BASE_URL + ad.image}" alt="Ad Preview" class="w-full h-48 object-cover rounded-lg">
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-sm text-gray-text">Title</label>
                    <div class="font-medium text-secondary">${ad.title}</div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-gray-text">Status</label>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            ad.status === 'active' ? 'bg-green-100 text-green-800' : 
                            (ad.status === 'scheduled' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')
                        }">
                            ${ad.status.charAt(0).toUpperCase() + ad.status.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-gray-text">Created</label>
                    <div class="font-medium text-secondary">${formatDate(ad.created)}</div>
                </div>
                <div class="space-y-1">
                    <label class="text-sm text-gray-text">Expires</label>
                    <div class="font-medium text-secondary">${formatDate(ad.expires)}</div>
                </div>
                <div class="space-y-1 col-span-2">
                    <label class="text-sm text-gray-text">Redirect URL</label>
                    <div class="font-medium text-primary break-all">${ad.redirect_url}</div>
                </div>
                <div class="space-y-1 col-span-2">
                    <label class="text-sm text-gray-text">Description</label>
                    <div class="font-medium text-secondary">${ad.description || 'No description provided'}</div>
                </div>
            </div>
            <div class="border-t border-gray-100 pt-6">
                <h4 class="font-medium text-secondary mb-4">Performance Metrics</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-text">Impressions</div>
                        <div class="text-xl font-semibold text-secondary mt-1">${ad.impressions.toLocaleString()}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-text">Clicks</div>
                        <div class="text-xl font-semibold text-secondary mt-1">${ad.clicks.toLocaleString()}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm text-gray-text">CTR</div>
                        <div class="text-xl font-semibold text-secondary mt-1">${ctr}%</div>
                    </div>
                </div>
            </div>
            <div class="flex gap-4">
                <button onclick="editAd(${ad.id})" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors">
                    Edit Advertisement
                </button>
                <button onclick="confirmDeleteAd(${ad.id})" class="flex-1 border border-red-500 text-red-500 px-4 py-2 rounded-lg hover:bg-red-50 transition-colors">
                    Delete Advertisement
                </button>
            </div>
        </div>
    `;

        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            offcanvas.querySelector('.transform').classList.remove('translate-x-full');
        }, 0);
    }

    // Hide ad details
    function hideAdDetails() {
        const offcanvas = document.getElementById('adDetailsOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(() => {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    // Edit ad
    function editAd(adId) {
        // Hide details panel if open
        hideAdDetails();

        // Get ad data
        const ad = getAdById(adId);
        if (!ad) return;

        // Show form and populate with ad data
        const createAdCard = document.getElementById('createAdCard');
        document.getElementById('adTitle').value = ad.title;
        document.getElementById('redirectUrl').value = ad.redirect_url;
        document.getElementById('expiryDate').value = formatDateTimeForInput(new Date(ad.expires));
        document.getElementById('adDescription').value = ad.description || '';

        // Set status toggle
        const toggleButton = document.getElementById('activeStatus');
        const statusLabel = document.getElementById('statusLabel');

        if (ad.status === 'active') {
            toggleButton.setAttribute('aria-checked', 'true');
            toggleButton.querySelector('span').classList.add('translate-x-6');
            toggleButton.classList.add('bg-primary');
            toggleButton.classList.remove('bg-gray-200');
            statusLabel.textContent = 'Active';
        } else {
            toggleButton.setAttribute('aria-checked', 'false');
            toggleButton.querySelector('span').classList.remove('translate-x-6');
            toggleButton.classList.remove('bg-primary');
            toggleButton.classList.add('bg-gray-200');
            statusLabel.textContent = 'Inactive';
        }

        // Update form title and button
        document.querySelector('#createAdCard h2').textContent = 'Edit Advertisement';
        document.querySelector('#createAdForm button[type="submit"]').textContent = 'Update Advertisement';

        // Show form
        createAdCard.classList.remove('hidden');
        document.getElementById('adTitle').focus();
    }

    // Confirm delete ad
    function confirmDeleteAd(adId) {
        currentAdId = adId;
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    // Hide delete confirmation modal
    function hideDeleteConfirmationModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
        currentAdId = null;
    }

    // Delete ad
    function deleteAd() {
        if (!currentAdId) return;

        showLoading();

        // Simulate deletion
        setTimeout(() => {
            hideLoading();
            hideDeleteConfirmationModal();
            hideAdDetails();
            showSuccessNotification('Advertisement deleted successfully!');

            // In a real app, you would remove the ad from the DOM or refresh the page
        }, 1500);
    }

    // Filter ads
    function filterAds(query, status) {
        // Filter desktop rows
        const rows = document.querySelectorAll('#adsTable tbody tr');

        rows.forEach(row => {
            const title = row.querySelector('td:first-child').textContent.toLowerCase();
            const rowStatus = row.querySelector('td:nth-child(2) span').textContent.toLowerCase();

            const matchesQuery = title.includes(query);
            const matchesStatus = !status || rowStatus === status.toLowerCase();

            row.style.display = matchesQuery && matchesStatus ? '' : 'none';
        });

        // Filter mobile cards
        const cards = document.querySelectorAll('.mobile-row');

        cards.forEach(card => {
            const title = card.querySelector('.mobile-row-header').textContent.toLowerCase();
            const cardStatus = card.querySelector('.accordion-content .inline-flex').textContent.toLowerCase();

            const matchesQuery = title.includes(query);
            const matchesStatus = !status || cardStatus === status.toLowerCase();

            card.style.display = matchesQuery && matchesStatus ? '' : 'none';
        });

        updatePagination();
    }

    // Update pagination
    function updatePagination() {
        const visibleRows = document.querySelectorAll('#adsTable tbody tr:not([style*="display: none"])').length;
        const visibleCards = document.querySelectorAll('.mobile-row:not([style*="display: none"])').length;

        const visibleCount = window.innerWidth >= 768 ? visibleRows : visibleCards;
        const totalAds = document.querySelectorAll('#adsTable tbody tr').length;

        document.getElementById('showing-start').textContent = visibleCount > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = visibleCount;
        document.getElementById('total-ads').textContent = totalAds;
        document.getElementById('ads-count').textContent = visibleCount;
    }

    // Reset form
    function resetForm() {
        document.getElementById('createAdForm').reset();

        // Reset image preview
        document.getElementById('image-preview').classList.add('hidden');

        // Reset status toggle
        const toggleButton = document.getElementById('activeStatus');
        toggleButton.setAttribute('aria-checked', 'true');
        toggleButton.querySelector('span').classList.add('translate-x-6');
        toggleButton.classList.add('bg-primary');
        toggleButton.classList.remove('bg-gray-200');
        document.getElementById('statusLabel').textContent = 'Active';

        // Reset form title and button
        document.querySelector('#createAdCard h2').textContent = 'Create New Advertisement';
        document.querySelector('#createAdForm button[type="submit"]').textContent = 'Create Advertisement';

        // Set default expiry date (7 days from now)
        const defaultExpiry = new Date();
        defaultExpiry.setDate(defaultExpiry.getDate() + 7);
        document.getElementById('expiryDate').value = formatDateTimeForInput(defaultExpiry);
    }

    // Show loading overlay
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    // Hide loading overlay
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Show success notification
    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageElement = document.getElementById('successMessage');

        messageElement.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Get ad by ID
    function getAdById(id) {
        // In a real app, you would fetch this from the server
        const ads = <?= json_encode($ads) ?>;
        return ads.find(ad => ad.id === id);
    }

    // Format date for display
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Format date for datetime-local input
    function formatDateTimeForInput(date) {
        return date.toISOString().slice(0, 16);
    }

    // Base URL
    const BASE_URL = '<?= BASE_URL ?>';
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>