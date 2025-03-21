<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Search Log';
$activeNav = 'search-log';

// Sample search data - in a real application, this would come from a database
$searchLogs = [
    [
        'id' => 1,
        'term' => 'Cement',
        'ip_address' => '192.168.0.1',
        'location' => 'Kampala, Uganda',
        'timestamp' => '2024-03-07 10:15:30',
        'user_id' => null,
        'results_count' => 8
    ],
    [
        'id' => 2,
        'term' => 'Blocks',
        'ip_address' => '172.16.1.25',
        'location' => 'Entebbe, Uganda',
        'timestamp' => '2024-03-07 09:45:22',
        'user_id' => 2,
        'results_count' => 12
    ],
    [
        'id' => 3,
        'term' => 'Timber',
        'ip_address' => '10.0.0.15',
        'location' => 'Jinja, Uganda',
        'timestamp' => '2024-03-06 14:30:15',
        'user_id' => null,
        'results_count' => 5
    ],
    [
        'id' => 4,
        'term' => 'Steel rods',
        'ip_address' => '192.168.1.100',
        'location' => 'Mbale, Uganda',
        'timestamp' => '2024-03-06 11:20:45',
        'user_id' => 1,
        'results_count' => 10
    ],
    [
        'id' => 5,
        'term' => 'Paint',
        'ip_address' => '172.16.0.50',
        'location' => 'Mbarara, Uganda',
        'timestamp' => '2024-03-05 16:10:30',
        'user_id' => null,
        'results_count' => 15
    ],
    [
        'id' => 6,
        'term' => 'Cement',
        'ip_address' => '10.0.0.25',
        'location' => 'Kampala, Uganda',
        'timestamp' => '2024-03-05 09:30:15',
        'user_id' => 3,
        'results_count' => 8
    ],
    [
        'id' => 7,
        'term' => 'Roofing sheets',
        'ip_address' => '192.168.2.15',
        'location' => 'Gulu, Uganda',
        'timestamp' => '2024-03-04 15:45:30',
        'user_id' => null,
        'results_count' => 6
    ]
];

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

// Function to format timestamps for chart labels
function formatChartDate($date)
{
    if (!$date) return '-';
    $timestamp = strtotime($date);
    return date('M d', $timestamp);
}

// Calculate top search terms
function getTopSearchTerms($logs, $limit = 5)
{
    $terms = [];
    foreach ($logs as $log) {
        $term = strtolower($log['term']);
        if (!isset($terms[$term])) {
            $terms[$term] = [
                'term' => $log['term'],
                'count' => 1
            ];
        } else {
            $terms[$term]['count']++;
        }
    }

    // Sort by count (descending)
    usort($terms, function ($a, $b) {
        return $b['count'] - $a['count'];
    });

    // Return only the top N terms
    return array_slice($terms, 0, $limit);
}

// Get top search terms
$topSearchTerms = getTopSearchTerms($searchLogs);

// Get search counts by date
function getSearchCountsByDate($logs)
{
    $counts = [];
    foreach ($logs as $log) {
        $date = date('Y-m-d', strtotime($log['timestamp']));
        if (!isset($counts[$date])) {
            $counts[$date] = 1;
        } else {
            $counts[$date]++;
        }
    }

    // Sort by date (ascending)
    ksort($counts);

    return $counts;
}

// Get search counts by date
$searchCountsByDate = getSearchCountsByDate($searchLogs);

// Get unique search count
$uniqueSearches = count(array_unique(array_column($searchLogs, 'term')));

ob_start();
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.1/dist/chart.umd.min.js"></script>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Search Log</h1>
            <p class="text-sm text-gray-text mt-1">Monitor and analyze user search patterns</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportSearchData" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="refreshSearchData" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-sync-alt"></i>
                <span>Refresh Data</span>
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
                    onclick="loadSearchStats()"
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-text">Total Searches</h3>
                <span class="text-blue-600">
                    <i class="fas fa-search"></i>
                </span>
            </div>
            <p class="text-2xl font-semibold text-secondary" id="totalSearches"><?= count($searchLogs) ?></p>
            <div class="mt-2 text-xs flex items-center">
                <span class="text-green-600 flex items-center" id="searchesChange">
                    <i class="fas fa-arrow-up mr-1"></i> 12%
                </span>
                <span class="text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-text">Unique Search Terms</h3>
                <span class="text-green-600">
                    <i class="fas fa-tag"></i>
                </span>
            </div>
            <p class="text-2xl font-semibold text-secondary" id="uniqueSearches"><?= $uniqueSearches ?></p>
            <div class="mt-2 text-xs flex items-center">
                <span class="text-green-600 flex items-center" id="uniqueChange">
                    <i class="fas fa-arrow-up mr-1"></i> 8%
                </span>
                <span class="text-gray-500 ml-2">vs previous period</span>
            </div>
        </div>
    </div>

    <!-- Search Trends Chart Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-secondary mb-4">Search Trends</h3>
        <div class="w-full h-80">
            <canvas id="searchChart"></canvas>
        </div>
    </div>

    <!-- Top Searches Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-secondary mb-4">Top Searched Terms</h3>
        <div class="space-y-4" id="topTermsList">
            <?php foreach ($topSearchTerms as $index => $term): ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold">
                                <?= $index + 1 ?>
                            </span>
                            <span class="text-secondary font-medium"><?= htmlspecialchars($term['term']) ?></span>
                        </div>
                        <span class="text-gray-text text-sm"><?= $term['count'] ?> searches</span>
                    </div>
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full" style="width: <?= ($term['count'] / $topSearchTerms[0]['count']) * 100 ?>%;"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Search Logs Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Detailed Search Logs</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="logs-count"><?= count($searchLogs) ?></span> searches recorded
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchLogs" placeholder="Filter logs..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Desktop Search Logs Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="searchLogsTable">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Search Term</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">IP Address</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Location</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Results</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchLogs as $log): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($log['term']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($log['ip_address']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($log['location']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= number_format($log['results_count']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($log['timestamp']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Search Logs List -->
        <div class="md:hidden p-4 space-y-4">
            <?php foreach ($searchLogs as $log): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex justify-between items-center mb-3">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($log['term']) ?></p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= number_format($log['results_count']) ?> results
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">IP Address:</span>
                                <span class="text-sm"><?= htmlspecialchars($log['ip_address']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Location:</span>
                                <span class="text-sm"><?= htmlspecialchars($log['location']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-xs text-gray-500">Date & Time:</span>
                                <span class="text-sm"><?= formatDate($log['timestamp']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($searchLogs) ?></span> of <span id="total-logs"><?= count($searchLogs) ?></span> logs
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

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Loading data...</p>
    </div>
</div>

<!-- Success Notification -->
<div id="successNotification" class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage">Data refreshed successfully!</span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default date range (last 7 days)
        const today = new Date();
        const sevenDaysAgo = new Date();
        sevenDaysAgo.setDate(today.getDate() - 7);

        document.getElementById('endDate').value = formatDateForInput(today);
        document.getElementById('startDate').value = formatDateForInput(sevenDaysAgo);

        // Initialize chart
        initChart();

        // Search functionality
        document.getElementById('searchLogs').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();

            // Filter desktop rows
            const rows = document.querySelectorAll('#searchLogsTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            // Filter mobile cards
            const cards = document.querySelectorAll('.md\\:hidden > div');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });

            updatePagination();
        });

        // Refresh button
        document.getElementById('refreshSearchData').addEventListener('click', function() {
            showLoading();

            // Simulate data refresh
            setTimeout(() => {
                hideLoading();
                showSuccessNotification('Search data refreshed successfully!');
            }, 1000);
        });

        // Export button
        document.getElementById('exportSearchData').addEventListener('click', function() {
            showSuccessNotification('Search data exported successfully!');
        });
    });

    // Initialize chart
    function initChart() {
        const ctx = document.getElementById('searchChart').getContext('2d');
        const labels = <?= json_encode(array_map('formatChartDate', array_keys($searchCountsByDate))) ?>;
        const data = <?= json_encode(array_values($searchCountsByDate)) ?>;

        searchChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Searches',
                    data: data,
                    borderColor: '#C00000',
                    backgroundColor: 'rgba(192, 0, 0, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
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

    // Load search stats
    function loadSearchStats() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
            alert('Please select both start and end dates.');
            return;
        }

        showLoading();

        // Simulate API call
        setTimeout(() => {
            // Update chart with random data
            updateChartData(startDate, endDate);

            // Update stats
            const totalSearches = Math.floor(Math.random() * 500) + 100;
            const uniqueSearches = Math.floor(totalSearches * 0.6);

            document.getElementById('totalSearches').textContent = totalSearches.toLocaleString();
            document.getElementById('uniqueSearches').textContent = uniqueSearches.toLocaleString();

            // Update change indicators
            const searchesChange = Math.floor(Math.random() * 30) - 10;
            const uniqueChange = Math.floor(Math.random() * 30) - 10;

            updateChangeIndicator('searchesChange', searchesChange);
            updateChangeIndicator('uniqueChange', uniqueChange);

            hideLoading();
            showSuccessNotification('Search data updated successfully!');
        }, 1000);
    }

    // Update chart data
    function updateChartData(startDate, endDate) {
        if (!searchChart) {
            return;
        }

        // Generate dates between start and end
        const dates = generateDateRange(startDate, endDate);

        // Generate random data
        const searchData = [];

        for (let i = 0; i < dates.length; i++) {
            searchData.push(Math.floor(Math.random() * 50) + 5);
        }

        // Update chart data
        searchChart.data.labels = dates.map(date => formatChartDate(date));
        searchChart.data.datasets[0].data = searchData;

        // Update chart
        searchChart.update();
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
        loadSearchStats();
    }

    // Update change indicator
    function updateChangeIndicator(elementId, changeValue) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');

        element.textContent = '';
        element.appendChild(icon);
        element.appendChild(document.createTextNode(' ' + Math.abs(changeValue) + '%'));

        if (changeValue > 0) {
            icon.className = 'fas fa-arrow-up mr-1';
            element.className = 'text-green-600 flex items-center';
        } else if (changeValue < 0) {
            icon.className = 'fas fa-arrow-down mr-1';
            element.className = 'text-red-600 flex items-center';
        } else {
            icon.className = 'fas fa-minus mr-1';
            element.className = 'text-gray-600 flex items-center';
        }
    }

    // Update pagination
    function updatePagination() {
        const visibleRows = document.querySelectorAll('#searchLogsTable tbody tr:not([style*="display: none"])').length;
        const visibleCards = document.querySelectorAll('.md\\:hidden > div:not([style*="display: none"])').length;

        const visibleCount = window.innerWidth >= 768 ? visibleRows : visibleCards;
        const totalLogs = document.querySelectorAll('#searchLogsTable tbody tr').length;

        document.getElementById('showing-start').textContent = visibleCount > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = visibleCount;
        document.getElementById('total-logs').textContent = totalLogs;
        document.getElementById('logs-count').textContent = visibleCount;
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

    // Helper functions
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatChartDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric'
        });
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