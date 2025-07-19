<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Search Analytics';
$activeNav = 'search-log';
ob_start();

// Generate dummy data for initial design
function generateDummySearchData($days = 30)
{
    $data = [];
    $searchTerms = [
        'cement',
        'steel bars',
        'roofing sheets',
        'tiles',
        'paint',
        'bricks',
        'sand',
        'gravel',
        'pipes',
        'electrical cables',
        'windows',
        'doors',
        'nails',
        'screws',
        'timber',
        'blocks'
    ];

    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $searchCount = rand(50, 200);

        for ($j = 0; $j < $searchCount; $j++) {
            $maxScore = rand(20, 100);
            $minScore = rand(0, $maxScore);
            $avgScore = rand($minScore, $maxScore);
            $resultsCount = rand(0, 50);
            $duration = rand(50, 500);

            $data[] = [
                'id' => generateUlid(),
                'search_query' => $searchTerms[array_rand($searchTerms)] . (rand(0, 1) ? ' ' . $searchTerms[array_rand($searchTerms)] : ''),
                'results_count' => $resultsCount,
                'max_match_score' => $maxScore,
                'min_match_score' => $minScore,
                'average_match_score' => $avgScore,
                'duration_ms' => $duration,
                'created_at' => $date . ' ' . sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59))
            ];
        }
    }

    return $data;
}

$dummyData = generateDummySearchData(30);
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Search Analytics</h1>
                    <p class="text-gray-600 mt-1">Monitor and analyze search performance and user behavior</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button id="exportBtn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-download"></i>
                        <span>Export Data</span>
                    </button>
                    <button id="refreshBtn"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Date Filter Controls -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Time Period</h2>
                    <p class="text-sm text-gray-600">Select the time range for analysis</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="daily">
                            <i class="fas fa-calendar-day mr-2"></i>Daily
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="weekly">
                            <i class="fas fa-calendar-week mr-2"></i>Weekly
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="monthly">
                            <i class="fas fa-calendar-alt mr-2"></i>Monthly
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button id="applyCustomRange"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 sm:p-6 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Searches</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-900 truncate" id="totalSearches">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-search text-blue-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 sm:p-6 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Avg Response Time</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-900 truncate" id="avgResponseTime">0ms</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-clock text-green-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-xl p-4 sm:p-6 border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">Avg Match Score</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-900 truncate" id="avgMatchScore">0%</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-bullseye text-yellow-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 sm:p-6 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Zero Results</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-900 truncate" id="zeroResults">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                        <i class="fas fa-exclamation-triangle text-purple-600 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Log Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Search Log Details</h3>
                        <p class="text-sm text-gray-600">Detailed search activity records</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Filter searches..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="performanceFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Performance</option>
                            <option value="good">Good (70%+)</option>
                            <option value="fair">Fair (50-70%)</option>
                            <option value="poor">Poor (<50%)< /option>
                        </select>
                        <button id="clearFilters"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-full">
                    <table class="min-w-[700px] w-full" id="searchLogTable">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 180px;">
                                    Search Query
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 80px;">
                                    Results
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 100px;">
                                    Match Score
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 90px;">
                                    Performance
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 80px;">
                                    Duration
                                </th>
                                <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                    style="min-width: 120px;">
                                    Timestamp
                                </th>
                            </tr>
                        </thead>
                        <tbody id="searchLogBody" class="divide-y divide-gray-100">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> searches
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-8">
            <!-- Search Performance Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Search Performance</h3>
                        <p class="text-sm text-gray-600">Match score distribution over time</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-gray-600">Good (70%+)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                            <span class="text-gray-600">Fair (50-70%)</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-gray-600">Poor (&lt;50%)< /span>
                        </div>
                    </div>
                </div>
                <div class="h-64 sm:h-80">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Search Volume Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Search Volume</h3>
                        <p class="text-sm text-gray-600">Search activity over time</p>
                    </div>
                    <select id="volumeMetric" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="count">Search Count</option>
                        <option value="unique">Unique Queries</option>
                        <option value="results">Avg Results</option>
                    </select>
                </div>
                <div class="h-64 sm:h-80">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Top Search Terms -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Top Search Terms</h3>
                </div>
                <div class="space-y-3 sm:space-y-4" id="topSearchTerms">
                    <!-- Dynamic content -->
                </div>
            </div>

            <!-- Performance Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Performance Distribution</h3>
                    <i class="fas fa-chart-pie text-gray-400"></i>
                </div>
                <div class="h-48 sm:h-64">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>

            <!-- Response Time Analysis -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Response Time</h3>
                    <span class="text-sm text-gray-500" id="responseTimeLabel">Current period</span>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Fast (&lt;100ms)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-16 sm:w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 65%" id="fastBar">
                                </div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 min-w-[3rem] text-right"
                                id="fastPercent">65%</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Medium (100-300ms)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-16 sm:w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 25%" id="mediumBar"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 min-w-[3rem] text-right"
                                id="mediumPercent">25%</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Slow (>300ms)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-16 sm:w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: 10%" id="slowBar"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 min-w-[3rem] text-right"
                                id="slowPercent">10%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Global variables
    let searchData = <?= json_encode($dummyData) ?>;
    let filteredData = [...searchData];
    let currentPage = 1;
    let itemsPerPage = 20;
    let charts = {};
    let currentPeriod = 'daily';

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function () {
        initializeDateFilters();
        updateStatistics();
        renderSearchLogTable();
        initializeCharts();
        renderTopSearchTerms();
        setupEventListeners();
    });

    function initializeDateFilters() {
        setDateRangeForPeriod('daily');
    }

    function setDateRangeForPeriod(period) {
        const today = new Date();
        let startDate, endDate;

        switch (period) {
            case 'daily':
                startDate = new Date(today);
                endDate = new Date(today);
                break;
            case 'weekly':
                // Get Sunday of current week
                const dayOfWeek = today.getDay();
                startDate = new Date(today);
                startDate.setDate(today.getDate() - dayOfWeek);
                // Get Saturday of current week
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;
            case 'monthly':
                // First day of current month
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                // Current date
                endDate = new Date(today);
                break;
            default:
                startDate = new Date(today);
                endDate = new Date(today);
        }

        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

        currentPeriod = period;
        applyDateFilter(period);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        const day = date.getDate();
        const suffix = day === 1 || day === 21 || day === 31 ? 'st' :
            day === 2 || day === 22 ? 'nd' :
                day === 3 || day === 23 ? 'rd' : 'th';

        const month = months[date.getMonth()];
        const year = date.getFullYear();

        return `${month} ${day}${suffix}, ${year}`;
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        let hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        const minutesStr = minutes < 10 ? '0' + minutes : minutes;

        return `${hours}:${minutesStr}${ampm}`;
    }

    function updateStatistics() {
        const totalSearches = filteredData.length;
        const avgResponseTime = totalSearches > 0 ? Math.round(filteredData.reduce((sum, item) => sum + item.duration_ms, 0) / totalSearches) : 0;
        const avgMatchScore = totalSearches > 0 ? Math.round(filteredData.reduce((sum, item) => sum + item.average_match_score, 0) / totalSearches) : 0;
        const zeroResults = filteredData.filter(item => item.results_count === 0).length;

        document.getElementById('totalSearches').textContent = totalSearches.toLocaleString();
        document.getElementById('avgResponseTime').textContent = avgResponseTime + 'ms';
        document.getElementById('avgMatchScore').textContent = avgMatchScore + '%';
        document.getElementById('zeroResults').textContent = zeroResults.toLocaleString();

        // Update response time analysis
        updateResponseTimeAnalysis();

        // Update response time label
        const label = currentPeriod === 'daily' ? 'Today' :
            currentPeriod === 'weekly' ? 'This week' :
                currentPeriod === 'monthly' ? 'This month' : 'Current period';
        document.getElementById('responseTimeLabel').textContent = label;
    }

    function updateResponseTimeAnalysis() {
        const fast = filteredData.filter(item => item.duration_ms < 100).length;
        const medium = filteredData.filter(item => item.duration_ms >= 100 && item.duration_ms <= 300).length;
        const slow = filteredData.filter(item => item.duration_ms > 300).length;
        const total = filteredData.length;

        if (total > 0) {
            const fastPercent = Math.round((fast / total) * 100);
            const mediumPercent = Math.round((medium / total) * 100);
            const slowPercent = Math.round((slow / total) * 100);

            document.getElementById('fastBar').style.width = fastPercent + '%';
            document.getElementById('mediumBar').style.width = mediumPercent + '%';
            document.getElementById('slowBar').style.width = slowPercent + '%';

            document.getElementById('fastPercent').textContent = fastPercent + '%';
            document.getElementById('mediumPercent').textContent = mediumPercent + '%';
            document.getElementById('slowPercent').textContent = slowPercent + '%';
        }
    }

    function renderSearchLogTable() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageData = filteredData.slice(startIndex, endIndex);

        const tbody = document.getElementById('searchLogBody');
        tbody.innerHTML = pageData.map(item => {
            const performanceBadge = getPerformanceBadge(item.max_match_score);

            return `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-3">
                    <div class="font-medium text-gray-900 break-words text-sm">${item.search_query}</div>
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${item.results_count === 0 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">
                        ${item.results_count}
                    </span>
                </td>
                <td class="px-3 py-3 text-center">
                    <div class="text-xs text-gray-900">
                        <span class="font-medium">${item.max_match_score}%</span> / <span class="text-gray-600">${item.min_match_score}%</span> / <span class="text-gray-600">${item.average_match_score}%</span>
                    </div>
                </td>
                <td class="px-3 py-3 text-center">
                    ${performanceBadge}
                </td>
                <td class="px-3 py-3 text-center">
                    <span class="text-sm font-medium text-gray-900">${item.duration_ms}ms</span>
                </td>
                <td class="px-3 py-3 text-center">
                    <div class="text-xs text-gray-900">${formatDate(item.created_at)}</div>
                    <div class="text-xs text-gray-500">${formatTime(item.created_at)}</div>
                </td>
            </tr>
        `;
        }).join('');

        updatePagination();
    }

    function getChartLabelsAndData() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);

        if (currentPeriod === 'daily') {
            // Show 24 hours with 2-hour intervals
            const labels = [];
            const dataGroups = {};

            for (let hour = 0; hour < 24; hour += 2) {
                const label = `${hour.toString().padStart(2, '0')}:00`;
                labels.push(label);
                dataGroups[hour] = { good: 0, fair: 0, poor: 0, total: 0 };
            }

            filteredData.forEach(item => {
                const itemDate = new Date(item.created_at);
                const hour = Math.floor(itemDate.getHours() / 2) * 2; // Round to nearest 2-hour interval

                if (dataGroups[hour]) {
                    dataGroups[hour].total++;
                    if (item.max_match_score >= 70) {
                        dataGroups[hour].good++;
                    } else if (item.max_match_score >= 50) {
                        dataGroups[hour].fair++;
                    } else {
                        dataGroups[hour].poor++;
                    }
                }
            });

            return {
                labels,
                goodData: labels.map((_, index) => dataGroups[index * 2]?.good || 0),
                fairData: labels.map((_, index) => dataGroups[index * 2]?.fair || 0),
                poorData: labels.map((_, index) => dataGroups[index * 2]?.poor || 0),
                volumeData: labels.map((_, index) => dataGroups[index * 2]?.total || 0)
            };
        } else if (currentPeriod === 'weekly') {
            // Show 7 days
            const labels = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const dataGroups = {};

            labels.forEach((day, index) => {
                dataGroups[index] = { good: 0, fair: 0, poor: 0, total: 0 };
            });

            filteredData.forEach(item => {
                const itemDate = new Date(item.created_at);
                const dayOfWeek = itemDate.getDay();

                dataGroups[dayOfWeek].total++;
                if (item.max_match_score >= 70) {
                    dataGroups[dayOfWeek].good++;
                } else if (item.max_match_score >= 50) {
                    dataGroups[dayOfWeek].fair++;
                } else {
                    dataGroups[dayOfWeek].poor++;
                }
            });

            return {
                labels,
                goodData: labels.map((_, index) => dataGroups[index]?.good || 0),
                fairData: labels.map((_, index) => dataGroups[index]?.fair || 0),
                poorData: labels.map((_, index) => dataGroups[index]?.poor || 0),
                volumeData: labels.map((_, index) => dataGroups[index]?.total || 0)
            };
        } else {
            // Show days between start and end date
            const labels = [];
            const dataGroups = {};

            const currentDate = new Date(startDate);
            while (currentDate <= endDate) {
                const dateStr = currentDate.toISOString().split('T')[0];
                const label = currentDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                labels.push(label);
                dataGroups[dateStr] = { good: 0, fair: 0, poor: 0, total: 0 };
                currentDate.setDate(currentDate.getDate() + 1);
            }

            filteredData.forEach(item => {
                const dateStr = item.created_at.split(' ')[0];
                if (dataGroups[dateStr]) {
                    dataGroups[dateStr].total++;
                    if (item.max_match_score >= 70) {
                        dataGroups[dateStr].good++;
                    } else if (item.max_match_score >= 50) {
                        dataGroups[dateStr].fair++;
                    } else {
                        dataGroups[dateStr].poor++;
                    }
                }
            });

            const dates = Object.keys(dataGroups).sort();
            return {
                labels,
                goodData: dates.map(date => dataGroups[date]?.good || 0),
                fairData: dates.map(date => dataGroups[date]?.fair || 0),
                poorData: dates.map(date => dataGroups[date]?.poor || 0),
                volumeData: dates.map(date => dataGroups[date]?.total || 0)
            };
        }
    }

    function initializeCharts() {
        initializePerformanceChart();
        initializeVolumeChart();
        initializeDistributionChart();
    }

    function initializePerformanceChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const chartData = getChartLabelsAndData();

        if (charts.performance) {
            charts.performance.destroy();
        }

        charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Good (70%+)',
                        data: chartData.goodData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Fair (50-70%)',
                        data: chartData.fairData,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Poor (<50%)',
                        data: chartData.poorData,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function initializeVolumeChart() {
        const ctx = document.getElementById('volumeChart').getContext('2d');
        const chartData = getChartLabelsAndData();

        if (charts.volume) {
            charts.volume.destroy();
        }

        charts.volume = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Search Count',
                    data: chartData.volumeData,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: '#3B82F6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    function initializeDistributionChart() {
        const ctx = document.getElementById('distributionChart').getContext('2d');

        const good = filteredData.filter(item => item.max_match_score >= 70).length;
        const fair = filteredData.filter(item => item.max_match_score >= 50 && item.max_match_score < 70).length;
        const poor = filteredData.filter(item => item.max_match_score < 50).length;

        if (charts.distribution) {
            charts.distribution.destroy();
        }

        charts.distribution = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Good (70%+)', 'Fair (50-70%)', 'Poor (<50%)'],
                datasets: [{
                    data: [good, fair, poor],
                    backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    function renderTopSearchTerms() {
        const termCounts = {};
        filteredData.forEach(item => {
            const term = item.search_query.toLowerCase();
            termCounts[term] = (termCounts[term] || 0) + 1;
        });

        const sortedTerms = Object.entries(termCounts)
            .sort(([, a], [, b]) => b - a)
            .slice(0, 5); // Changed from 10 to 5

        const container = document.getElementById('topSearchTerms');
        container.innerHTML = sortedTerms.map(([term, count], index) => `
        <div class="flex items-center justify-between p-3 rounded-lg ${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'}">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <div class="w-6 h-6 rounded-full bg-primary/10 flex items-center justify-center text-xs font-semibold text-primary flex-shrink-0">
                    ${index + 1}
                </div>
                <span class="font-medium text-gray-900 truncate">${term}</span>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                <span class="text-sm text-gray-600 hidden sm:inline">${count} searches</span>
                <span class="text-sm text-gray-600 sm:hidden">${count}</span>
                <div class="w-12 sm:w-16 bg-gray-200 rounded-full h-2">
                    <div class="bg-primary h-2 rounded-full" style="width: ${(count / sortedTerms[0][1]) * 100}%"></div>
                </div>
            </div>
        </div>
    `).join('');
    }

    function getPerformanceBadge(score) {
        if (score >= 70) {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Good</span>';
        } else if (score >= 50) {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Fair</span>';
        } else {
            return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Poor</span>';
        }
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredData.length / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, filteredData.length);

        document.getElementById('showingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('totalCount').textContent = filteredData.length;
        document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${Math.max(1, totalPages)}`;

        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;
    }

    function applyFilters() {
        const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
        const performanceFilter = document.getElementById('performanceFilter').value;

        filteredData = searchData.filter(item => {
            const matchesSearch = !searchTerm || item.search_query.toLowerCase().includes(searchTerm);

            let matchesPerformance = true;
            if (performanceFilter === 'good') {
                matchesPerformance = item.max_match_score >= 70;
            } else if (performanceFilter === 'fair') {
                matchesPerformance = item.max_match_score >= 50 && item.max_match_score < 70;
            } else if (performanceFilter === 'poor') {
                matchesPerformance = item.max_match_score < 50;
            }

            return matchesSearch && matchesPerformance;
        });

        currentPage = 1;
        updateStatistics();
        updateCharts();
        renderTopSearchTerms();
        renderSearchLogTable();
    }

    function updateCharts() {
        initializeCharts();
    }

    function applyDateFilter(period) {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        endDate.setHours(23, 59, 59, 999); // Include the entire end date

        filteredData = searchData.filter(item => {
            const itemDate = new Date(item.created_at);
            return itemDate >= startDate && itemDate <= endDate;
        });

        currentPage = 1;
        updateStatistics();
        updateCharts();
        renderTopSearchTerms();
        renderSearchLogTable();
    }

    function setupEventListeners() {
        // Date filter buttons
        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                this.classList.add('active', 'bg-primary', 'text-white', 'border-primary');
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');

                const period = this.dataset.period;
                setDateRangeForPeriod(period);
            });
        });

        // Custom date range
        document.getElementById('applyCustomRange').addEventListener('click', function () {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                // Clear active period button
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-primary', 'text-white', 'border-primary');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                currentPeriod = 'custom';
                applyDateFilter('custom');
            }
        });

        // Search and filter inputs
        document.getElementById('searchFilter').addEventListener('input', applyFilters);
        document.getElementById('performanceFilter').addEventListener('change', applyFilters);

        // Clear filters
        document.getElementById('clearFilters').addEventListener('click', function () {
            document.getElementById('searchFilter').value = '';
            document.getElementById('performanceFilter').value = 'all';
            applyFilters();
        });

        // Pagination
        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderSearchLogTable();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderSearchLogTable();
            }
        });

        // Export and refresh buttons
        document.getElementById('exportBtn').addEventListener('click', exportData);
        document.getElementById('refreshBtn').addEventListener('click', refreshData);

        // Volume chart metric selector
        document.getElementById('volumeMetric').addEventListener('change', function () {
            updateVolumeChart(this.value);
        });
    }

    function exportData() {
        // Create CSV content
        const headers = ['Search Query', 'Results Count', 'Max Match Score', 'Min Match Score', 'Average Match Score', 'Duration (ms)', 'Timestamp'];
        const csvContent = [
            headers.join(','),
            ...filteredData.map(item => [
                `"${item.search_query}"`,
                item.results_count,
                item.max_match_score,
                item.min_match_score,
                item.average_match_score,
                item.duration_ms,
                `"${item.created_at}"`
            ].join(','))
        ].join('\n');

        // Download CSV file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `search-analytics-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }

    function refreshData() {
        // In a real application, this would fetch fresh data from the server
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        setTimeout(() => {
            // Simulate data refresh
            updateStatistics();
            updateCharts();
            renderTopSearchTerms();
            renderSearchLogTable();

            icon.classList.remove('fa-spin');
            refreshBtn.disabled = false;
        }, 1000);
    }

    function updateVolumeChart(metric) {
        // This would update the volume chart based on the selected metric
        // For now, we'll just reinitialize the chart
        if (charts.volume) {
            charts.volume.destroy();
        }
        initializeVolumeChart();
    }
</script>

<style>
    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all 0.2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    #searchLogTable tbody tr:hover {
        background-color: #f9fafb;
    }

    .chart-container {
        position: relative;
    }

    /* Enhanced mobile table scrolling */
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    @media (max-width: 640px) {
        .chart-container {
            height: 200px;
        }

        #searchLogTable {
            font-size: 0.875rem;
            min-width: 650px;
        }

        #searchLogTable th,
        #searchLogTable td {
            padding: 0.5rem 0.75rem;
        }
    }

    @media (max-width: 480px) {
        #searchLogTable {
            font-size: 0.8rem;
            min-width: 600px;
        }

        #searchLogTable th,
        #searchLogTable td {
            padding: 0.4rem 0.6rem;
        }
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>