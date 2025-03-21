<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Schedule Ads';
$activeNav = 'schedule-ads';

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

// Sample data for ad pages
$pages = [
    [
        'id' => 1,
        'name' => 'Homepage Banner',
        'description' => 'Main banner section on the homepage',
        'active_ads' => 3,
        'url' => '/homepage-banner',
        'next_schedule' => '2024-02-17 10:00:00'
    ],
    [
        'id' => 2,
        'name' => 'Product Sidebar',
        'description' => 'Advertisement space in product listing sidebar',
        'active_ads' => 2,
        'url' => '/product-sidebar',
        'next_schedule' => '2024-02-18 15:30:00'
    ],
    [
        'id' => 3,
        'name' => 'Category Header',
        'description' => 'Advertisement at the top of category pages',
        'active_ads' => 1,
        'url' => '/category-header',
        'next_schedule' => '2024-02-16 12:00:00'
    ],
    [
        'id' => 4,
        'name' => 'Checkout Page Recommendations',
        'description' => 'Product recommendations on the checkout page',
        'active_ads' => 0,
        'url' => '/checkout-recommendations',
        'next_schedule' => null
    ]
];

// Sample data for scheduled ads
$scheduledAds = [
    [
        'id' => 1,
        'title' => 'Summer Sale 2024',
        'start' => '2024-02-17T10:00:00',
        'end' => '2024-02-17T12:00:00',
        'page_id' => 1,
        'color' => '#C00000'
    ],
    [
        'id' => 2,
        'title' => 'New Product Launch',
        'start' => '2024-02-18T15:30:00',
        'end' => '2024-02-18T17:30:00',
        'page_id' => 2,
        'color' => '#3B82F6'
    ],
    [
        'id' => 3,
        'title' => 'Holiday Special Offer',
        'start' => '2024-02-16T12:00:00',
        'end' => '2024-02-16T15:00:00',
        'page_id' => 3,
        'color' => '#10B981'
    ]
];

// Sample data for all ads
$allAds = [
    [
        'id' => 1,
        'title' => 'Summer Sale 2024'
    ],
    [
        'id' => 2,
        'title' => 'New Product Launch'
    ],
    [
        'id' => 3,
        'title' => 'Holiday Special Offer'
    ],
    [
        'id' => 4,
        'title' => 'Year-End Clearance'
    ],
    [
        'id' => 5,
        'title' => 'Spring Collection 2024'
    ]
];

ob_start();
?>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Schedule Ads</h1>
            <p class="text-sm text-gray-text mt-1">Manage and schedule advertisements across your platform</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportSchedule" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="addPageBtn" onclick="showAddPageForm()" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-plus"></i>
                <span>Add New Page</span>
            </button>
        </div>
    </div>

    <!-- Pages View -->
    <div id="pagesView" class="space-y-6 transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Ad Pages</h2>
                    <p class="text-sm text-gray-text mt-1">
                        <span id="pages-count"><?= count($pages) ?></span> pages found
                    </p>
                </div>
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchPages" placeholder="Search pages..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Desktop Pages Table -->
            <div class="overflow-x-auto hidden md:block">
                <table class="w-full" id="pagesTable">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Page Name</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">URL</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Description</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Active Ads</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Next Schedule</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors" data-page-id="<?= $page['id'] ?>" data-search="<?= strtolower($page['name']) ?> <?= strtolower($page['description']) ?> <?= strtolower($page['url']) ?>">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-secondary"><?= htmlspecialchars($page['name']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-sm text-primary">
                                    <?= htmlspecialchars($page['url']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= htmlspecialchars($page['description']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $page['active_ads'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $page['active_ads'] ?> active
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= $page['next_schedule'] ? formatDate($page['next_schedule']) : 'No upcoming schedule' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            data-tippy-content="Schedule Ads"
                                            onclick="showCalendarView(<?= $page['id'] ?>, '<?= htmlspecialchars($page['name']) ?>', '<?= htmlspecialchars($page['url']) ?>')">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>

                                        <button
                                            class="action-btn text-green-600 hover:text-green-800"
                                            data-tippy-content="Edit Page"
                                            onclick="editPage(<?= $page['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <button
                                            class="action-btn text-red-600 hover:text-red-800"
                                            data-tippy-content="Delete Page"
                                            onclick="confirmDeletePage(<?= $page['id'] ?>)">
                                            <i class="fas fa-trash"></i>
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
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4" data-page-id="<?= $page['id'] ?>" data-search="<?= strtolower($page['name']) ?> <?= strtolower($page['description']) ?> <?= strtolower($page['url']) ?>">
                        <div class="p-4 border-b border-gray-100">
                            <div class="flex justify-between items-center mb-3">
                                <p class="font-medium text-secondary"><?= htmlspecialchars($page['name']) ?></p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $page['active_ads'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $page['active_ads'] ?> active
                                </span>
                            </div>
                            <div class="space-y-2 mb-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="text-xs text-gray-500">URL:</span>
                                    <span class="text-sm text-primary"><?= htmlspecialchars($page['url']) ?></span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">Description:</span>
                                    <p class="text-sm text-gray-text"><?= htmlspecialchars($page['description']) ?></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="text-xs text-gray-500">Next Schedule:</span>
                                    <span class="text-sm"><?= $page['next_schedule'] ? formatDate($page['next_schedule']) : 'No upcoming schedule' ?></span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    onclick="showCalendarView(<?= $page['id'] ?>, '<?= htmlspecialchars($page['name']) ?>', '<?= htmlspecialchars($page['url']) ?>')"
                                    class="flex-1 bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-calendar-alt mr-1"></i> Schedule
                                </button>
                                <button
                                    onclick="editPage(<?= $page['id'] ?>)"
                                    class="flex-1 bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button
                                    onclick="confirmDeletePage(<?= $page['id'] ?>)"
                                    class="flex-1 bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-1"></i> Delete
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

    <!-- Calendar View -->
    <div id="calendarView" class="space-y-6 hidden transition-opacity duration-300">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button onclick="showPagesView()" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-semibold text-secondary" id="calendarTitle">Schedule Ads</h2>
                        <p class="text-sm text-gray-text mt-1">
                            <span id="calendar-url" class="text-primary"></span>
                        </p>
                    </div>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select id="intervalSelect" onchange="updateCalendarInterval()" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                            <option value="10">10 min</option>
                            <option value="30" selected>30 min</option>
                            <option value="60">1 hour</option>
                        </select>
                    </div>
                    <div class="flex h-10 rounded-lg border border-gray-200 overflow-hidden divide-x divide-gray-200 w-full md:w-auto">
                        <button onclick="setCalendarView('timeGridDay')" class="viewButton flex-1 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:bg-gray-50 active:bg-primary active:text-white transition-colors">Day</button>
                        <button onclick="setCalendarView('timeGridWeek')" class="viewButton flex-1 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:bg-gray-50 active:bg-primary active:text-white transition-colors">Week</button>
                        <button onclick="setCalendarView('dayGridMonth')" class="viewButton flex-1 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:z-10 focus:bg-gray-50 active:bg-primary active:text-white transition-colors">Month</button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <!-- Calendar will be rendered here -->
                <div id="calendar" class="fc-theme-standard"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add Page Modal -->
<div id="addPageModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideAddPageForm()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="pageModalTitle">Add New Page</h3>
                <button onclick="hideAddPageForm()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="pageForm" class="space-y-6">
                    <input type="hidden" id="pageId" value="">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700" for="pageName">Page Name</label>
                        <input type="text" id="pageName" name="pageName" required class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700" for="pageUrl">Page URL</label>
                        <input type="text" id="pageUrl" name="pageUrl" required class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700" for="pageDescription">Description</label>
                        <textarea id="pageDescription" name="pageDescription" rows="4" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="hideAddPageForm()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" id="pageFormSubmit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                            Add Page
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Ad Modal -->
<div id="scheduleAdModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideScheduleAdModal()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="scheduleModalTitle">Schedule Advertisement</h3>
                <button onclick="hideScheduleAdModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <form id="scheduleAdForm" class="space-y-6">
                    <input type="hidden" id="scheduleId" value="">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700" for="adSelect">Select Advertisement</label>
                        <select id="adSelect" name="adId" required class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">Select an ad...</option>
                            <?php foreach ($allAds as $ad): ?>
                                <option value="<?= $ad['id'] ?>"><?= htmlspecialchars($ad['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700" for="startDateTime">Start Date & Time</label>
                            <input type="datetime-local" id="startDateTime" name="startDateTime" required class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700" for="endDateTime">End Date & Time</label>
                            <input type="datetime-local" id="endDateTime" name="endDateTime" required class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">Duration</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative">
                                <input type="number" id="durationHours" name="durationHours" min="0" placeholder="Hours" class="w-full h-10 pl-3 pr-10 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">hours</span>
                            </div>
                            <div class="relative">
                                <input type="number" id="durationMinutes" name="durationMinutes" min="0" max="59" placeholder="Minutes" class="w-full h-10 pl-3 pr-10 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">min</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <span id="deleteScheduleBtn" class="hidden">
                            <button type="button" onclick="confirmDeleteSchedule()" class="px-4 py-2 border border-red-500 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                                Delete
                            </button>
                        </span>
                        <button type="button" onclick="hideScheduleAdModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" id="scheduleFormSubmit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                            Schedule Ad
                        </button>
                    </div>
                </form>
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
                <h3 class="text-lg font-semibold text-gray-900" id="deleteTitle">Delete Confirmation</h3>
                <p class="text-sm text-gray-500 mt-2" id="deleteMessage">Are you sure you want to delete this item?</p>
            </div>
            <div class="flex justify-center gap-3 mt-6">
                <button onclick="hideDeleteConfirmationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
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

    @media (max-width: 768px) {
        .overflow-x-auto {
            margin: 0 -1rem;
        }

        table {
            min-width: 800px;
        }
    }

    /* FullCalendar Custom Styling */
    .fc {
        --fc-border-color: #e5e7eb;
        --fc-today-bg-color: rgba(192, 0, 0, 0.05);
        --fc-event-border-color: transparent;
        --fc-event-bg-color: #C00000;
        --fc-event-text-color: #fff;
        --fc-page-bg-color: #fff;
        --fc-button-bg-color: #f3f4f6;
        --fc-button-border-color: #e5e7eb;
        --fc-button-text-color: #4b5563;
        --fc-button-active-bg-color: #C00000;
        --fc-button-active-border-color: #C00000;
        --fc-button-hover-bg-color: #f9fafb;
        --fc-button-hover-border-color: #d1d5db;
        font-family: inherit;
    }

    .fc .fc-toolbar-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a1a1a;
    }

    .fc .fc-button {
        font-weight: 500;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        box-shadow: none;
    }

    .fc .fc-button:focus {
        box-shadow: 0 0 0 3px rgba(192, 0, 0, 0.2);
        outline: none;
    }

    .fc .fc-col-header-cell-cushion {
        font-weight: 600;
        padding: 0.75rem 0;
        color: #1a1a1a;
    }

    .fc .fc-daygrid-day-number {
        padding: 0.5rem;
        color: #4b5563;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(192, 0, 0, 0.05);
    }

    .fc .fc-timegrid-slot-label {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .fc .fc-timegrid-axis-cushion {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .fc .fc-event {
        border-radius: 0.25rem;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        border-left: 3px solid var(--fc-event-border-color);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .fc .fc-event-time {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .fc .fc-event-title {
        font-weight: 500;
    }

    .fc-theme-standard .fc-scrollgrid {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .fc-direction-ltr .fc-button-group>.fc-button:not(:last-child) {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .fc-direction-ltr .fc-button-group>.fc-button:not(:first-child) {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        margin-left: -1px;
    }

    /* Active button state for calendar view */
    .viewButton.active {
        background-color: var(--fc-button-active-bg-color);
        color: white;
    }
</style>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    let calendar = null;
    let currentPageId = null;
    let deleteType = null;
    let deleteId = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        tippy('[data-tippy-content]', {
            placement: 'top',
            arrow: true,
            theme: 'light',
        });

        // Initialize calendar
        initializeCalendar();

        // Active view button
        document.querySelectorAll('.viewButton').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.viewButton').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
            });
        });

        // Set the default view button as active
        document.querySelector('.viewButton:nth-child(2)').classList.add('active');

        // Search functionality
        document.getElementById('searchPages').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();

            // Filter desktop rows
            const rows = document.querySelectorAll('#pagesTable tbody tr');
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                row.style.display = searchData.includes(query) ? '' : 'none';
            });

            // Filter mobile cards
            const cards = document.querySelectorAll('.md\\:hidden > div');
            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                card.style.display = searchData.includes(query) ? '' : 'none';
            });

            updatePagination();
        });

        // Form handlers
        document.getElementById('pageForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const pageId = document.getElementById('pageId').value;
            showLoading();

            // Simulate form submission
            setTimeout(() => {
                hideLoading();
                hideAddPageForm();

                if (pageId) {
                    showSuccessNotification('Page updated successfully!');
                } else {
                    showSuccessNotification('Page created successfully!');
                }
            }, 1000);
        });

        document.getElementById('scheduleAdForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const scheduleId = document.getElementById('scheduleId').value;
            showLoading();

            // Simulate form submission
            setTimeout(() => {
                hideLoading();
                hideScheduleAdModal();

                if (scheduleId) {
                    showSuccessNotification('Schedule updated successfully!');
                } else {
                    showSuccessNotification('Ad scheduled successfully!');
                }
            }, 1000);
        });

        // Export button
        document.getElementById('exportSchedule').addEventListener('click', function() {
            showSuccessNotification('Schedule data exported successfully!');
        });

        // Duration and time change handlers
        ['startDateTime', 'endDateTime'].forEach(id => {
            document.getElementById(id).addEventListener('change', updateDuration);
        });

        ['durationHours', 'durationMinutes'].forEach(id => {
            document.getElementById(id).addEventListener('change', updateEndTime);
        });
    });

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            initialDate: new Date(),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            allDaySlot: false,
            nowIndicator: true,
            slotDuration: '00:30:00',
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            firstDay: 0,
            selectable: true,
            selectMirror: true,
            editable: true,
            dayMaxEvents: true,
            navLinks: true,
            businessHours: {
                daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
                startTime: '08:00',
                endTime: '18:00',
            },
            height: 'auto',
            select: function(info) {
                showScheduleAdModal(info.start, info.end);
            },
            eventClick: function(info) {
                showScheduleAdModal(info.event.start, info.event.end, info.event);
            },
            eventOverlap: false,
            events: <?= json_encode($scheduledAds) ?>
        });

        calendar.render();
    }

    function showPagesView() {
        const pagesView = document.getElementById('pagesView');
        const calendarView = document.getElementById('calendarView');

        calendarView.style.opacity = 0;
        setTimeout(() => {
            calendarView.classList.add('hidden');
            pagesView.classList.remove('hidden');
            setTimeout(() => {
                pagesView.style.opacity = 1;
            }, 0);
        }, 300);
    }

    function showCalendarView(pageId, pageName, pageUrl) {
        currentPageId = pageId;

        const pagesView = document.getElementById('pagesView');
        const calendarView = document.getElementById('calendarView');

        pagesView.style.opacity = 0;
        setTimeout(() => {
            pagesView.classList.add('hidden');
            calendarView.classList.remove('hidden');

            document.getElementById('calendarTitle').textContent = `Schedule Ads - ${pageName}`;
            document.getElementById('calendar-url').textContent = pageUrl;

            setTimeout(() => {
                calendarView.style.opacity = 1;

                if (calendar) {
                    // Filter events for the selected page
                    calendar.getEvents().forEach(event => event.remove());

                    const pageEvents = <?= json_encode($scheduledAds) ?>.filter(event => event.page_id === pageId);
                    pageEvents.forEach(event => {
                        calendar.addEvent(event);
                    });

                    calendar.render();
                }
            }, 0);
        }, 300);
    }

    function showAddPageForm() {
        const modal = document.getElementById('addPageModal');
        resetPageForm();

        document.getElementById('pageModalTitle').textContent = 'Add New Page';
        document.getElementById('pageFormSubmit').textContent = 'Add Page';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.transform').classList.remove('translate-x-full');
            document.getElementById('pageName').focus();
        }, 0);
    }

    function hideAddPageForm() {
        const modal = document.getElementById('addPageModal');
        modal.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function editPage(pageId) {
        const page = <?= json_encode($pages) ?>.find(p => p.id === pageId);
        if (!page) return;

        const modal = document.getElementById('addPageModal');

        document.getElementById('pageId').value = page.id;
        document.getElementById('pageName').value = page.name;
        document.getElementById('pageUrl').value = page.url;
        document.getElementById('pageDescription').value = page.description;

        document.getElementById('pageModalTitle').textContent = 'Edit Page';
        document.getElementById('pageFormSubmit').textContent = 'Update Page';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.transform').classList.remove('translate-x-full');
            document.getElementById('pageName').focus();
        }, 0);
    }

    function resetPageForm() {
        document.getElementById('pageForm').reset();
        document.getElementById('pageId').value = '';
    }

    function showScheduleAdModal(start, end, event = null) {
        const modal = document.getElementById('scheduleAdModal');
        const deleteBtn = document.getElementById('deleteScheduleBtn');

        const startInput = document.getElementById('startDateTime');
        const endInput = document.getElementById('endDateTime');
        const durationHours = document.getElementById('durationHours');
        const durationMinutes = document.getElementById('durationMinutes');

        // Convert to local ISO string format for datetime-local input
        startInput.value = moment(start).format('YYYY-MM-DDTHH:mm');
        endInput.value = moment(end).format('YYYY-MM-DDTHH:mm');

        // Calculate duration
        const duration = moment.duration(moment(end).diff(moment(start)));
        durationHours.value = Math.floor(duration.asHours());
        durationMinutes.value = duration.minutes();

        if (event) {
            // Editing existing schedule
            document.getElementById('scheduleId').value = event.id || event.extendedProps.id;
            document.getElementById('adSelect').value = event.id || event.extendedProps.id;
            document.getElementById('scheduleModalTitle').textContent = 'Edit Schedule';
            document.getElementById('scheduleFormSubmit').textContent = 'Update Schedule';
            deleteBtn.classList.remove('hidden');
        } else {
            // Creating new schedule
            document.getElementById('scheduleId').value = '';
            document.getElementById('adSelect').selectedIndex = 0;
            document.getElementById('scheduleModalTitle').textContent = 'Schedule Advertisement';
            document.getElementById('scheduleFormSubmit').textContent = 'Schedule Ad';
            deleteBtn.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.querySelector('.transform').classList.remove('translate-x-full');
        }, 0);
    }

    function hideScheduleAdModal() {
        const modal = document.getElementById('scheduleAdModal');
        modal.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    function updateDuration() {
        const start = moment(document.getElementById('startDateTime').value);
        const end = moment(document.getElementById('endDateTime').value);
        if (!start.isValid() || !end.isValid()) return;

        const duration = moment.duration(end.diff(start));
        document.getElementById('durationHours').value = Math.floor(duration.asHours());
        document.getElementById('durationMinutes').value = duration.minutes();
    }

    function updateEndTime() {
        const start = moment(document.getElementById('startDateTime').value);
        if (!start.isValid()) return;

        const hours = parseInt(document.getElementById('durationHours').value) || 0;
        const minutes = parseInt(document.getElementById('durationMinutes').value) || 0;

        const end = moment(start).add(hours, 'hours').add(minutes, 'minutes');
        document.getElementById('endDateTime').value = end.format('YYYY-MM-DDTHH:mm');
    }

    function updateCalendarInterval() {
        const interval = document.getElementById('intervalSelect').value;
        if (calendar) {
            calendar.setOption('slotDuration', `00:${interval}:00`);
        }
    }

    function setCalendarView(view) {
        if (calendar) {
            calendar.changeView(view);
        }
    }

    function confirmDeletePage(pageId) {
        deleteType = 'page';
        deleteId = pageId;

        document.getElementById('deleteTitle').textContent = 'Delete Page';
        document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this page? This will also remove all scheduled ads for this page.';

        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    function confirmDeleteSchedule() {
        deleteType = 'schedule';
        deleteId = document.getElementById('scheduleId').value;

        document.getElementById('deleteTitle').textContent = 'Delete Schedule';
        document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete this scheduled advertisement?';

        hideScheduleAdModal();
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    function hideDeleteConfirmationModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
        deleteType = null;
        deleteId = null;
    }

    function confirmDelete() {
        showLoading();

        // Simulate deletion
        setTimeout(() => {
            hideLoading();
            hideDeleteConfirmationModal();

            if (deleteType === 'page') {
                showSuccessNotification('Page deleted successfully!');
            } else if (deleteType === 'schedule') {
                showSuccessNotification('Schedule deleted successfully!');
            }

            deleteType = null;
            deleteId = null;
        }, 1000);
    }

    function updatePagination() {
        const visibleRows = document.querySelectorAll('#pagesTable tbody tr:not([style*="display: none"])').length;
        const visibleCards = document.querySelectorAll('.md\\:hidden > div:not([style*="display: none"])').length;

        const visibleCount = window.innerWidth >= 768 ? visibleRows : visibleCards;
        const totalPages = document.querySelectorAll('#pagesTable tbody tr').length;

        document.getElementById('showing-start').textContent = visibleCount > 0 ? '1' : '0';
        document.getElementById('showing-end').textContent = visibleCount;
        document.getElementById('total-pages').textContent = totalPages;
        document.getElementById('pages-count').textContent = visibleCount;
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
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>