<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Page Activity';
$activeNav = 'page-activity';

// Sample page data - in a real application, this would come from a database
$pages = [
    [
        'id' => 1,
        'name' => 'Homepage Banner',
        'visits_7d' => 234,
        'unique_7d' => 120,
        'last_access' => '2024-02-18 10:12:00'
    ],
    [
        'id' => 2,
        'name' => 'Product Sidebar',
        'visits_7d' => 145,
        'unique_7d' => 90,
        'last_access' => '2024-02-18 09:45:00'
    ],
    [
        'id' => 3,
        'name' => 'Category Listing',
        'visits_7d' => 189,
        'unique_7d' => 105,
        'last_access' => '2024-02-17 14:30:00'
    ],
    [
        'id' => 4,
        'name' => 'Product Details',
        'visits_7d' => 210,
        'unique_7d' => 115,
        'last_access' => '2024-02-18 11:20:00'
    ],
    [
        'id' => 5,
        'name' => 'Checkout Page',
        'visits_7d' => 98,
        'unique_7d' => 75,
        'last_access' => '2024-02-18 08:15:00'
    ]
];

// Function to format date (PHP side)
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

ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.umd.min.js"></script>

<div class="space-y-6">
    <!-- Pages List View -->
    <div id="pagesView" class="space-y-6 transition-all duration-300">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-secondary">Page Activity</h1>
                <p class="text-sm text-gray-text mt-1">Monitor and analyze page visits and user engagement</p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <button id="exportActivity" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </button>
                <button id="refreshData" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                    <i class="fas fa-sync-alt"></i>
                    <span>Refresh Data</span>
                </button>
            </div>
        </div>

        <!-- Pages Table Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Pages</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="pages-count"><?= count($pages) ?></span> pages found
                    </p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="searchPages" placeholder="Search pages..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select id="sortPages" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                            <option value="visits" selected="selected">Sort by Visits</option>
                            <option value="unique">Sort by Unique Visitors</option>
                            <option value="recent">Sort by Recent Activity</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Desktop Pages Table -->
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full" id="pagesTable">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Page Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Visits (7 Days)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Unique (7 Days)</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Last Access</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-page-id="<?= $page['id'] ?>" data-page-name="<?= htmlspecialchars($page['name']) ?>">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($page['name']) ?></p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= number_format($page['visits_7d']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= number_format($page['unique_7d']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= formatDate($page['last_access']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            data-tippy-content="View Details"
                                            onclick="showActivityView(<?= $page['id'] ?>, '<?= htmlspecialchars($page['name']) ?>')">
                                            <i class="fas fa-chart-line"></i>
                                        </button>

                                        <button
                                            class="action-btn text-green-600 hover:text-green-800"
                                            data-tippy-content="Export Data">
                                            <i class="fas fa-file-export"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Pages List -->
            <div class="md:hidden p-4 space-y-4">
                <?php foreach ($pages as $page): ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4" data-page-id="<?= $page['id'] ?>" data-page-name="<?= htmlspecialchars($page['name']) ?>">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex justify-between items-center mb-3">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($page['name']) ?></p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= number_format($page['visits_7d']) ?> visits
                                </span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Unique Visitors:</span>
                                    <span class="text-sm"><?= number_format($page['unique_7d']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Last Access:</span>
                                    <span class="text-sm"><?= formatDate($page['last_access']) ?></span>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end space-x-2">
                                <button
                                    class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                    onclick="showActivityView(<?= $page['id'] ?>, '<?= htmlspecialchars($page['name']) ?>')">
                                    <i class="fas fa-chart-line mr-1"></i> View Details
                                </button>

                                <button
                                    class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">
                                    <i class="fas fa-file-export mr-1"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($pages) ?></span> of <span id="total-pages"><?= count($pages) ?></span> pages
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

    <!-- Activity Detail View -->
    <div id="activityView" class="space-y-6 hidden transition-all duration-300">
        <!-- Back Button and Title -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <button onclick="showPagesView()" class="h-10 w-10 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div>
                    <h1 class="text-2xl font-semibold text-secondary" id="activityTitle">Page Activity</h1>
                    <p class="text-sm text-gray-text mt-1">Detailed analytics for the selected page</p>
                </div>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <button id="exportDetailedActivity" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </button>
                <button id="printReport" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                    <i class="fas fa-print"></i>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- Date Range Filter Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-secondary mb-4">Date Range</h3>
            <div class="flex flex-col md:flex-row items-center gap-4">
                <div class="w-full md:w-auto">
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input
                        type="date"
                        id="startDate"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="w-full md:w-auto">
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input
                        type="date"
                        id="endDate"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="w-full md:w-auto md:self-end">
                    <button
                        onclick="loadStats()"
                        class="w-full md:w-auto h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Apply Filter
                    </button>
                </div>
                <div class="w-full md:w-auto md:self-end">
                    <select
                        id="presetRanges"
                        class="w-full md:w-auto h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        onchange="applyPresetRange()">
                        <option value="">Quick Ranges</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last7">Last 7 Days</option>
                        <option value="last30">Last 30 Days</option>
                        <option value="thisMonth">This Month</option>
                        <option value="lastMonth">Last Month</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-text">Total Visits</h3>
                    <span class="text-blue-600">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <p class="text-2xl font-semibold text-secondary" id="totalVisits">0</p>
                <div class="mt-2 text-xs flex items-center">
                    <span class="text-green-600 flex items-center" id="visitsChange">
                        <i class="fas fa-arrow-up mr-1"></i> 0%
                    </span>
                    <span class="text-gray-500 ml-2">vs previous period</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-text">Unique Visitors</h3>
                    <span class="text-green-600">
                        <i class="fas fa-users"></i>
                    </span>
                </div>
                <p class="text-2xl font-semibold text-secondary" id="uniqueVisits">0</p>
                <div class="mt-2 text-xs flex items-center">
                    <span class="text-green-600 flex items-center" id="uniqueChange">
                        <i class="fas fa-arrow-up mr-1"></i> 0%
                    </span>
                    <span class="text-gray-500 ml-2">vs previous period</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-text">Avg. Time on Page</h3>
                    <span class="text-yellow-600">
                        <i class="fas fa-clock"></i>
                    </span>
                </div>
                <p class="text-2xl font-semibold text-secondary" id="avgTime">0s</p>
                <div class="mt-2 text-xs flex items-center">
                    <span class="text-red-600 flex items-center" id="timeChange">
                        <i class="fas fa-arrow-down mr-1"></i> 0%
                    </span>
                    <span class="text-gray-500 ml-2">vs previous period</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-text">Bounce Rate</h3>
                    <span class="text-red-600">
                        <i class="fas fa-sign-out-alt"></i>
                    </span>
                </div>
                <p class="text-2xl font-semibold text-secondary" id="bounceRate">0%</p>
                <div class="mt-2 text-xs flex items-center">
                    <span class="text-green-600 flex items-center" id="bounceChange">
                        <i class="fas fa-arrow-down mr-1"></i> 0%
                    </span>
                    <span class="text-gray-500 ml-2">vs previous period</span>
                </div>
            </div>
        </div>

        <!-- Activity Chart Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-secondary mb-4">Traffic Overview</h3>
            <div class="flex flex-col md:flex-row items-center justify-end gap-4 mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-sm text-gray-text">Total Visits</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span class="text-sm text-gray-text">Unique Visitors</span>
                </div>
            </div>
            <div class="w-full h-80">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <!-- Visitor IPs Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary">Visitor IPs</h3>
                <p class="text-sm text-gray-text mt-1">IP addresses that visited this page</p>
            </div>

            <!-- Desktop IP Table -->
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full" id="visitorsTable">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">IP Address</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Location</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Visits</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Last Visit</th>
                        </tr>
                    </thead>
                    <tbody id="visitorsTableBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile IP List -->
            <div class="md:hidden p-4 space-y-4" id="visitorsMobileList">
                <!-- Will be populated by JavaScript -->
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-text">
                    Showing <span id="ip-showing-start">1</span> to <span id="ip-showing-end">0</span> of <span id="total-ips">0</span> IPs
                </div>
                <div class="flex items-center gap-2">
                    <button id="ip-prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div id="ip-pagination-numbers" class="flex items-center">
                        <button class="px-3 py-2 rounded-lg bg-primary text-white">1</button>
                    </div>
                    <button id="ip-next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Loading data...</p>
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

    #pagesView,
    #activityView {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.3s ease, transform 0.3s ease;
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

        // Set default date range (last 7 days)
        const today = new Date();
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 7);

        document.getElementById('endDate').value = formatDateForInput(today);
        document.getElementById('startDate').value = formatDateForInput(sevenDaysAgo);

        // Search functionality
        document.getElementById('searchPages').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();

            // Filter desktop rows
            const rows = document.querySelectorAll('#pagesTable tbody tr');
            rows.forEach(row => {
                const pageName = row.querySelector('td:first-child').textContent.toLowerCase();
                row.style.display = pageName.includes(query) ? '' : 'none';
            });

            // Filter mobile cards
            const cards = document.querySelectorAll('.md\\:hidden > div');
            cards.forEach(card => {
                const pageName = card.querySelector('.font-medium').textContent.toLowerCase();
                card.style.display = pageName.includes(query) ? '' : 'none';
            });

            updatePagination();
        });

        // Sort functionality
        document.getElementById('sortPages').addEventListener('change', function(e) {
            const sortBy = e.target.value;
            sortPages(sortBy);
        });

        // Refresh button
        document.getElementById('refreshData').addEventListener('click', function() {
            showLoading();

            // Simulate data refresh
            setTimeout(() => {
                hideLoading();
                showSuccessNotification('Data refreshed successfully!');
            }, 1000);
        });

        // Export buttons
        document.getElementById('exportActivity').addEventListener('click', function() {
            showSuccessNotification('Activity data exported successfully!');
        });

        document.getElementById('exportDetailedActivity').addEventListener('click', function() {
            showSuccessNotification('Detailed activity data exported successfully!');
        });

        // Print report button
        document.getElementById('printReport').addEventListener('click', function() {
            showSuccessNotification('Preparing report for printing...');
        });
    });

    // -------------------------------------------------------------
    // NEW JavaScript function to fix "formatDate is not defined"
    // -------------------------------------------------------------
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        if (isNaN(date)) return '-';

        const day = date.getDate();
        let suffix = '';

        // Determine day suffix like st, nd, rd, th
        if (day % 10 === 1 && day !== 11) {
            suffix = 'st';
        } else if (day % 10 === 2 && day !== 12) {
            suffix = 'nd';
        } else if (day % 10 === 3 && day !== 13) {
            suffix = 'rd';
        } else {
            suffix = 'th';
        }

        // Build full date string, e.g. "February 18th, 2024 9:10 AM"
        const monthName = date.toLocaleString('en-US', {
            month: 'long'
        });
        const year = date.getFullYear();

        let hours = date.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        if (hours === 0) hours = 12;

        let minutes = date.getMinutes();
        if (minutes < 10) minutes = '0' + minutes;

        return `${monthName} ${day}${suffix}, ${year} ${hours}:${minutes} ${ampm}`;
    }

    // Current page ID
    let currentPageId = null;

    // Show activity view
    function showActivityView(pageId, pageName) {
        currentPageId = pageId;

        const pagesView = document.getElementById('pagesView');
        const activityView = document.getElementById('activityView');

        // Animate transition
        pagesView.style.opacity = '0';
        pagesView.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            pagesView.classList.add('hidden');
            activityView.classList.remove('hidden');

            // Update title
            document.getElementById('activityTitle').textContent = pageName;

            // Animate in
            setTimeout(() => {
                activityView.style.opacity = '1';
                activityView.style.transform = 'translateY(0)';

                // Initialize chart if needed
                initChart();

                // Load stats
                loadStats();
            }, 50);
        }, 300);
    }

    // Show pages view
    function showPagesView() {
        const pagesView = document.getElementById('pagesView');
        const activityView = document.getElementById('activityView');

        // Animate transition
        activityView.style.opacity = '0';
        activityView.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            activityView.classList.add('hidden');
            pagesView.classList.remove('hidden');

            // Animate in
            setTimeout(() => {
                pagesView.style.opacity = '1';
                pagesView.style.transform = 'translateY(0)';
            }, 50);
        }, 300);
    }

    // Chart instance
    let activityChart = null;

    // Initialize chart
    function initChart() {
        if (activityChart) {
            return;
        }

        const ctx = document.getElementById('activityChart').getContext('2d');
        activityChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                        label: 'Total Visits',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Unique Visitors',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    }

    // Load stats
    function loadStats() {
        if (!currentPageId) {
            return;
        }

        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        showLoading();

        // Simulate API call
        setTimeout(() => {
            // Sample data - in a real application, this would come from an API
            const totalVisits = Math.floor(Math.random() * 500) + 200;
            const uniqueVisits = Math.floor(totalVisits * 0.6);
            const avgTime = Math.floor(Math.random() * 120) + 30;
            const bounceRate = Math.floor(Math.random() * 40) + 20;

            // Update stats
            document.getElementById('totalVisits').textContent = totalVisits.toLocaleString();
            document.getElementById('uniqueVisits').textContent = uniqueVisits.toLocaleString();
            document.getElementById('avgTime').textContent = formatTime(avgTime);
            document.getElementById('bounceRate').textContent = bounceRate + '%';

            // Update change indicators
            const visitsChange = Math.floor(Math.random() * 30) - 10;
            const uniqueChange = Math.floor(Math.random() * 30) - 10;
            const timeChange = Math.floor(Math.random() * 30) - 10;
            const bounceChange = Math.floor(Math.random() * 30) - 10;

            updateChangeIndicator('visitsChange', visitsChange);
            updateChangeIndicator('uniqueChange', uniqueChange);
            updateChangeIndicator('timeChange', timeChange, true);
            updateChangeIndicator('bounceChange', bounceChange, true);

            // Update chart
            updateChart(startDate, endDate);

            // Update visitor IPs
            updateVisitorIPs();

            hideLoading();
        }, 1000);
    }

    // Update change indicator
    function updateChangeIndicator(elementId, changeValue, invertColors = false) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');

        element.textContent = '';
        element.appendChild(icon);
        element.appendChild(document.createTextNode(' ' + Math.abs(changeValue) + '%'));

        if (changeValue > 0) {
            icon.className = 'fas fa-arrow-up mr-1';
            element.className = invertColors ? 'text-red-600 flex items-center' : 'text-green-600 flex items-center';
        } else if (changeValue < 0) {
            icon.className = 'fas fa-arrow-down mr-1';
            element.className = invertColors ? 'text-green-600 flex items-center' : 'text-red-600 flex items-center';
        } else {
            icon.className = 'fas fa-minus mr-1';
            element.className = 'text-gray-600 flex items-center';
        }
    }

    // Update chart
    function updateChart(startDate, endDate) {
        if (!activityChart) {
            return;
        }

        // Generate dates between start and end
        const dates = generateDateRange(startDate, endDate);

        // Generate random data
        const totalVisitsData = [];
        const uniqueVisitsData = [];

        for (let i = 0; i < dates.length; i++) {
            const visits = Math.floor(Math.random() * 100) + 20;
            totalVisitsData.push(visits);
            uniqueVisitsData.push(Math.floor(visits * (0.5 + Math.random() * 0.3)));
        }

        // Update chart data
        activityChart.data.labels = dates.map(date => formatDateForDisplay(date));
        activityChart.data.datasets[0].data = totalVisitsData;
        activityChart.data.datasets[1].data = uniqueVisitsData;

        // Update chart
        activityChart.update();
    }

    // Update visitor IPs
    function updateVisitorIPs() {
        // Sample data - in a real application, this would come from an API
        const visitorData = [{
                ip: '192.168.0.1',
                location: 'New York, USA',
                visits: 15,
                lastVisit: '2024-02-18 14:30:00'
            },
            {
                ip: '192.168.0.2',
                location: 'London, UK',
                visits: 8,
                lastVisit: '2024-02-18 12:15:00'
            },
            {
                ip: '172.16.1.25',
                location: 'Tokyo, Japan',
                visits: 5,
                lastVisit: '2024-02-17 22:45:00'
            },
            {
                ip: '10.0.0.15',
                location: 'Sydney, Australia',
                visits: 3,
                lastVisit: '2024-02-17 18:20:00'
            },
            {
                ip: '192.168.1.100',
                location: 'Berlin, Germany',
                visits: 7,
                lastVisit: '2024-02-18 09:10:00'
            }
        ];

        // Update desktop table
        const tbody = document.getElementById('visitorsTableBody');
        tbody.innerHTML = '';

        visitorData.forEach(visitor => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-100';
            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-text">${visitor.ip}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${visitor.location}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${visitor.visits}</td>
                <td class="px-6 py-4 text-sm text-gray-text">${formatDate(visitor.lastVisit)}</td>
            `;
            tbody.appendChild(row);
        });

        // Update mobile list
        const mobileList = document.getElementById('visitorsMobileList');
        mobileList.innerHTML = '';

        visitorData.forEach(visitor => {
            const card = document.createElement('div');
            card.className = 'border border-gray-100 rounded-lg overflow-hidden';
            card.innerHTML = `
                <div class="bg-gray-50 p-3 flex justify-between items-center">
                    <span class="font-medium text-gray-900">${visitor.ip}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${visitor.visits} visits
                    </span>
                </div>
                <div class="p-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs text-gray-500">Location:</span>
                        <span class="text-sm">${visitor.location}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Last Visit:</span>
                        <span class="text-sm">${formatDate(visitor.lastVisit)}</span>
                    </div>
                </div>
            `;
            mobileList.appendChild(card);
        });

        // Update pagination info
        document.getElementById('ip-showing-start').textContent = '1';
        document.getElementById('ip-showing-end').textContent = visitorData.length;
        document.getElementById('total-ips').textContent = visitorData.length;
    }

    // Apply preset date range
    function applyPresetRange() {
        const preset = document.getElementById('presetRanges').value;
        if (!preset) {
            return;
        }

        const today = new Date();
        let startDate = new Date();
        let endDate = new Date();

        switch (preset) {
            case 'today':
                // Start and end are both today
                break;
            case 'yesterday':
                startDate.setDate(today.getDate() - 1);
                endDate.setDate(today.getDate() - 1);
                break;
            case 'last7':
                startDate.setDate(today.getDate() - 6);
                break;
            case 'last30':
                startDate.setDate(today.getDate() - 29);
                break;
            case 'thisMonth':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                break;
            case 'lastMonth':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
        }

        document.getElementById('startDate').value = formatDateForInput(startDate);
        document.getElementById('endDate').value = formatDateForInput(endDate);

        // Reset dropdown
        document.getElementById('presetRanges').value = '';

        // Load stats with new date range
        loadStats();
    }

    // Sort pages
    function sortPages(sortBy) {
        const rows = Array.from(document.querySelectorAll('#pagesTable tbody tr'));
        const cards = Array.from(document.querySelectorAll('.md\\:hidden > div'));

        // Sort rows
        rows.sort((a, b) => {
            let valueA, valueB;
            switch (sortBy) {
                case 'visits':
                    valueA = parseInt(a.querySelector('td:nth-child(2)').textContent.replace(/,/g, ''));
                    valueB = parseInt(b.querySelector('td:nth-child(2)').textContent.replace(/,/g, ''));
                    break;
                case 'unique':
                    valueA = parseInt(a.querySelector('td:nth-child(3)').textContent.replace(/,/g, ''));
                    valueB = parseInt(b.querySelector('td:nth-child(3)').textContent.replace(/,/g, ''));
                    break;
                case 'recent':
                    valueA = new Date(a.querySelector('td:nth-child(4)').textContent);
                    valueB = new Date(b.querySelector('td:nth-child(4)').textContent);
                    break;
            }
            return valueB - valueA; // Descending order
        });

        // Reorder rows
        const tbody = document.querySelector('#pagesTable tbody');
        rows.forEach(row => tbody.appendChild(row));

        // Sort cards (mobile view)
        cards.sort((a, b) => {
            let valueA, valueB;
            switch (sortBy) {
                case 'visits':
                    valueA = parseInt(a.querySelector('.rounded-full').textContent);
                    valueB = parseInt(b.querySelector('.rounded-full').textContent);
                    break;
                case 'unique':
                    valueA = parseInt(a.querySelector('div:nth-child(1) div:nth-child(2) div:nth-child(1) span:nth-child(2)').textContent);
                    valueB = parseInt(b.querySelector('div:nth-child(1) div:nth-child(2) div:nth-child(1) span:nth-child(2)').textContent);
                    break;
                case 'recent':
                    valueA = new Date(a.querySelector('div:nth-child(1) div:nth-child(2) div:nth-child(2) span:nth-child(2)').textContent);
                    valueB = new Date(b.querySelector('div:nth-child(1) div:nth-child(2) div:nth-child(2) span:nth-child(2)').textContent);
                    break;
            }
            return valueB - valueA; // Descending order
        });

        // Reorder cards
        const mobileContainer = document.querySelector('.md\\:hidden');
        cards.forEach(card => mobileContainer.appendChild(card));
    }

    // Update pagination
    function updatePagination() {
        const visibleRows = document.querySelectorAll('#pagesTable tbody tr:not([style*="display: none"])').length;
        const visibleCards = document.querySelectorAll('.md\\:hidden > div:not([style*="display: none"])').length;

        const visibleCount = window.innerWidth >= 768 ? visibleRows : visibleCards;
        const totalPages = document.querySelectorAll('#pagesTable tbody tr').length;

        document.getElementById('showing-start').textContent = visibleCount > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = visibleCount;
        document.getElementById('total-pages').textContent = totalPages;
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
        // Create notification element if it doesn't exist
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

        // Update message and show notification
        document.getElementById('successMessage').textContent = message;
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    // Helper functions
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDateForDisplay(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
    }

    function formatTime(seconds) {
        if (seconds < 60) {
            return `${seconds}s`;
        } else {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}m ${remainingSeconds}s`;
        }
    }

    function generateDateRange(startDateStr, endDateStr) {
        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);
        const dates = [];

        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            dates.push(formatDateForInput(currentDate));
            currentDate.setDate(currentDate.getDate() + 1);
        }

        return dates;
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>