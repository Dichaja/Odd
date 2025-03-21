<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Notifications';
$activeNav = 'notifications';

// Function to format date with suffix
function formatDateWithSuffix($timestamp)
{
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

    return date('F ' . $day . $suffix . ', Y g:iA', $timestamp);
}

// Sample notifications data
$notifications = [
    [
        'id' => 1,
        'title' => 'New Product Promotion',
        'message' => 'Check out our new product promotions! Get up to 30% off on selected items in our store. Limited time offer.',
        'type' => 'promotion',
        'timestamp' => strtotime('-2 hours'),
        'isRead' => false
    ],
    [
        'id' => 2,
        'title' => 'Your Order Has Been Shipped',
        'message' => 'Your order #ORD-2025031001 has been shipped. Estimated delivery time is 3-5 business days. You will receive a tracking number shortly.',
        'type' => 'order',
        'timestamp' => strtotime('-1 day'),
        'isRead' => true
    ],
    [
        'id' => 3,
        'title' => 'Payment Successful',
        'message' => 'We have received your payment of UGX 45,000 for subscription renewal. Your subscription has been extended for 3 months.',
        'type' => 'payment',
        'timestamp' => strtotime('-2 days'),
        'isRead' => false
    ],
    [
        'id' => 4,
        'title' => 'System Maintenance Notice',
        'message' => 'Our system will be undergoing maintenance on March 15th, 2025 from 2:00 AM to
5:00 AM UTC. During this time, our services may be temporarily unavailable. We apologize for any inconvenience this may cause.',
        'type' => 'system',
        'timestamp' => strtotime('-3 days'),
        'isRead' => true
    ],
    [
        'id' => 5,
        'title' => 'Welcome to Zzimba Online',
        'message' => 'Welcome to Zzimba Online! We are excited to have you join our community. Get started by completing your profile and exploring our services.',
        'type' => 'welcome',
        'timestamp' => strtotime('-5 days'),
        'isRead' => true
    ],
    [
        'id' => 6,
        'title' => 'Price Update Notification',
        'message' => 'Due to changes in market conditions, prices for some products have been updated. Please check the updated price list before placing new orders.',
        'type' => 'price',
        'timestamp' => strtotime('-6 days'),
        'isRead' => false
    ],
    [
        'id' => 7,
        'title' => 'Account Security Alert',
        'message' => 'We noticed a login to your account from a new device. If this was you, you can ignore this message. If not, please change your password immediately and contact our support team.',
        'type' => 'security',
        'timestamp' => strtotime('-8 days'),
        'isRead' => false
    ],
    [
        'id' => 8,
        'title' => 'Credit Balance Update',
        'message' => 'Your Zzimba Credit balance has been updated. Current balance: UGX 98,500. Transaction ID: TRX-08318/080359',
        'type' => 'credit',
        'timestamp' => strtotime('-10 days'),
        'isRead' => true
    ],
    [
        'id' => 9,
        'title' => 'New Message from Vendor',
        'message' => 'You have received a new message from vendor "Hardware Supplies Ltd" regarding your inquiry about cement prices. Check your messages to respond.',
        'type' => 'message',
        'timestamp' => strtotime('-12 days'),
        'isRead' => false
    ],
    [
        'id' => 10,
        'title' => 'Rate Your Experience',
        'message' => 'Thank you for your recent purchase! We would appreciate if you could take a moment to rate your experience and provide feedback to help us improve our services.',
        'type' => 'feedback',
        'timestamp' => strtotime('-15 days'),
        'isRead' => true
    ]
];

// Count unread notifications
$unreadCount = 0;
foreach ($notifications as $notification) {
    if (!$notification['isRead']) {
        $unreadCount++;
    }
}

ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Notifications</h1>
            <p class="text-sm text-gray-text mt-1">You have <?= $unreadCount ?> unread notifications</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="markAllRead" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-check-double"></i>
                <span>Mark All Read</span>
            </button>
            <button id="deleteAllRead" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-trash"></i>
                <span>Delete Read</span>
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">All Notifications</h2>
                <p class="text-sm text-gray-text mt-1" id="selected-count">0 selected</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="searchNotifications" placeholder="Search notifications..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <select id="filterNotifications" class="h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    <option value="all">All Notifications</option>
                    <option value="unread">Unread Only</option>
                    <option value="read">Read Only</option>
                </select>
            </div>
        </div>

        <!-- Bulk Actions (Initially hidden) -->
        <div id="bulk-actions" class="p-4 border-b border-gray-100 bg-gray-50 hidden">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-medium text-gray-700">With selected:</span>
                <button id="markSelectedRead" class="h-9 px-3 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-1">
                    <i class="fas fa-check"></i>
                    <span>Mark Read</span>
                </button>
                <button id="markSelectedUnread" class="h-9 px-3 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-1">
                    <i class="fas fa-envelope"></i>
                    <span>Mark Unread</span>
                </button>
                <button id="deleteSelected" class="h-9 px-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-1">
                    <i class="fas fa-trash"></i>
                    <span>Delete</span>
                </button>
            </div>
        </div>

        <!-- Notifications Table -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="notifications-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-user-primary focus:ring-user-primary">
                        </th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Notification</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-48">Date</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-24">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text w-24">Actions</th>
                    </tr>
                </thead>
                <tbody id="notifications-table-body">
                    <?php foreach ($notifications as $index => $notification):
                        $formattedDate = formatDateWithSuffix($notification['timestamp']);
                        $statusClass = $notification['isRead'] ? 'text-gray-500 bg-gray-100' : 'text-blue-600 bg-blue-100 font-medium';
                        $rowClass = $notification['isRead'] ? '' : 'bg-blue-50/30';
                    ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors <?= $rowClass ?>" data-notification-id="<?= $notification['id'] ?>" data-is-read="<?= $notification['isRead'] ? 'true' : 'false' ?>">
                            <td class="px-6 py-4">
                                <input type="checkbox" class="notification-checkbox rounded border-gray-300 text-user-primary focus:ring-user-primary" data-id="<?= $notification['id'] ?>">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <?php
                                    $iconClass = '';
                                    switch ($notification['type']) {
                                        case 'promotion':
                                            $iconClass = 'bg-purple-100 text-purple-600 fas fa-tag';
                                            break;
                                        case 'order':
                                            $iconClass = 'bg-green-100 text-green-600 fas fa-shipping-fast';
                                            break;
                                        case 'payment':
                                            $iconClass = 'bg-blue-100 text-blue-600 fas fa-credit-card';
                                            break;
                                        case 'system':
                                            $iconClass = 'bg-gray-100 text-gray-600 fas fa-cog';
                                            break;
                                        case 'welcome':
                                            $iconClass = 'bg-yellow-100 text-yellow-600 fas fa-hand-peace';
                                            break;
                                        case 'price':
                                            $iconClass = 'bg-red-100 text-red-600 fas fa-dollar-sign';
                                            break;
                                        case 'security':
                                            $iconClass = 'bg-red-100 text-red-600 fas fa-shield-alt';
                                            break;
                                        case 'credit':
                                            $iconClass = 'bg-green-100 text-green-600 fas fa-wallet';
                                            break;
                                        case 'message':
                                            $iconClass = 'bg-indigo-100 text-indigo-600 fas fa-envelope';
                                            break;
                                        case 'feedback':
                                            $iconClass = 'bg-amber-100 text-amber-600 fas fa-star';
                                            break;
                                        default:
                                            $iconClass = 'bg-gray-100 text-gray-600 fas fa-bell';
                                    }
                                    ?>
                                    <div class="w-10 h-10 rounded-full <?= $iconClass ?> flex-shrink-0 flex items-center justify-center"></div>
                                    <div class="cursor-pointer notification-title" onclick="viewNotificationDetails(<?= $index ?>)">
                                        <div class="font-medium text-secondary"><?= htmlspecialchars($notification['title']) ?></div>
                                        <div class="text-xs text-gray-text mt-1 line-clamp-1">
                                            <?= htmlspecialchars(substr($notification['message'], 0, 100)) ?><?= strlen($notification['message']) > 100 ? '...' : '' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text"><?= $formattedDate ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>">
                                    <?= $notification['isRead'] ? 'Read' : 'Unread' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="viewNotificationDetails(<?= $index ?>)" class="text-blue-600 hover:text-blue-800 p-1" title="View Details">
                                        <i class="fas fa-eye"></i>
                                        <span class="sr-only">View Details</span>
                                    </button>
                                    <?php if ($notification['isRead']): ?>
                                        <button onclick="markAsUnread(<?= $notification['id'] ?>)" class="text-gray-600 hover:text-gray-800 p-1" title="Mark as Unread">
                                            <i class="fas fa-envelope"></i>
                                            <span class="sr-only">Mark as Unread</span>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="markAsRead(<?= $notification['id'] ?>)" class="text-green-600 hover:text-green-800 p-1" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                            <span class="sr-only">Mark as Read</span>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteNotification(<?= $notification['id'] ?>)" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                        <span class="sr-only">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden">
            <?php foreach ($notifications as $index => $notification):
                $formattedDate = formatDateWithSuffix($notification['timestamp']);
                $statusClass = $notification['isRead'] ? 'text-gray-500 bg-gray-100' : 'text-blue-600 bg-blue-100 font-medium';

                // Determine icon class based on notification type
                $iconClass = '';
                switch ($notification['type']) {
                    case 'promotion':
                        $iconClass = 'bg-purple-100 text-purple-600 fas fa-tag';
                        break;
                    case 'order':
                        $iconClass = 'bg-green-100 text-green-600 fas fa-shipping-fast';
                        break;
                    case 'payment':
                        $iconClass = 'bg-blue-100 text-blue-600 fas fa-credit-card';
                        break;
                    case 'system':
                        $iconClass = 'bg-gray-100 text-gray-600 fas fa-cog';
                        break;
                    case 'welcome':
                        $iconClass = 'bg-yellow-100 text-yellow-600 fas fa-hand-peace';
                        break;
                    case 'price':
                        $iconClass = 'bg-red-100 text-red-600 fas fa-dollar-sign';
                        break;
                    case 'security':
                        $iconClass = 'bg-red-100 text-red-600 fas fa-shield-alt';
                        break;
                    case 'credit':
                        $iconClass = 'bg-green-100 text-green-600 fas fa-wallet';
                        break;
                    case 'message':
                        $iconClass = 'bg-indigo-100 text-indigo-600 fas fa-envelope';
                        break;
                    case 'feedback':
                        $iconClass = 'bg-amber-100 text-amber-600 fas fa-star';
                        break;
                    default:
                        $iconClass = 'bg-gray-100 text-gray-600 fas fa-bell';
                }
            ?>
                <div class="border-b border-gray-100 p-4" data-notification-id="<?= $notification['id'] ?>">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <input type="checkbox" class="notification-checkbox rounded border-gray-300 text-user-primary focus:ring-user-primary" data-id="<?= $notification['id'] ?>">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-start gap-3 mb-2">
                                <div class="w-10 h-10 rounded-full <?= $iconClass ?> flex-shrink-0 flex items-center justify-center"></div>
                                <div class="flex-1">
                                    <div class="font-medium text-secondary"><?= htmlspecialchars($notification['title']) ?></div>
                                    <div class="text-xs text-gray-text mt-1">
                                        <?= htmlspecialchars(substr($notification['message'], 0, 80)) ?><?= strlen($notification['message']) > 80 ? '...' : '' ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <div class="text-xs text-gray-text"><?= $formattedDate ?></div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>">
                                        <?= $notification['isRead'] ? 'Read' : 'Unread' ?>
                                    </span>
                                    <button onclick="viewNotificationDetails(<?= $index ?>)" class="text-blue-600 hover:text-blue-800 p-1" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($notification['isRead']): ?>
                                        <button onclick="markAsUnread(<?= $notification['id'] ?>)" class="text-gray-600 hover:text-gray-800 p-1" title="Mark as Unread">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="markAsRead(<?= $notification['id'] ?>)" class="text-green-600 hover:text-green-800 p-1" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="deleteNotification(<?= $notification['id'] ?>)" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-notifications"><?= count($notifications) ?></span> notifications
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-user-primary text-white">1</button>
                    <button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50">2</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div id="notificationDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('notificationDetailsModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary" id="modal-title">Notification Details</h3>
                <button onclick="hideModal('notificationDetailsModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="notification-details" class="space-y-4">
                <!-- Notification details will be populated here -->
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button id="modal-mark-button" class="px-4 py-2 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                    Mark as Read
                </button>
                <button onclick="hideModal('notificationDetailsModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('deleteConfirmationModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Confirm Deletion</h3>
                <button onclick="hideModal('deleteConfirmationModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-6">
                <p id="delete-message" class="text-gray-600">Are you sure you want to delete this notification?</p>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="hideModal('deleteConfirmationModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button id="confirm-delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
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
        <span id="successMessage"></span>
    </div>
</div>

<script>
    // Store notifications data in JavaScript
    const notificationsData = <?= json_encode($notifications) ?>;
    let currentPage = 1;
    const itemsPerPage = 5;
    let filteredNotifications = [...notificationsData];

    // Show modal
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Hide modal
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Show notification details
    function viewNotificationDetails(index) {
        const notification = notificationsData[index];
        if (!notification) return;

        $('#modal-title').text(notification.title);

        // Determine icon class based on notification type
        let iconClass = '';
        switch (notification.type) {
            case 'promotion':
                iconClass = 'bg-purple-100 text-purple-600 fas fa-tag';
                break;
            case 'order':
                iconClass = 'bg-green-100 text-green-600 fas fa-shipping-fast';
                break;
            case 'payment':
                iconClass = 'bg-blue-100 text-blue-600 fas fa-credit-card';
                break;
            case 'system':
                iconClass = 'bg-gray-100 text-gray-600 fas fa-cog';
                break;
            case 'welcome':
                iconClass = 'bg-yellow-100 text-yellow-600 fas fa-hand-peace';
                break;
            case 'price':
                iconClass = 'bg-red-100 text-red-600 fas fa-dollar-sign';
                break;
            case 'security':
                iconClass = 'bg-red-100 text-red-600 fas fa-shield-alt';
                break;
            case 'credit':
                iconClass = 'bg-green-100 text-green-600 fas fa-wallet';
                break;
            case 'message':
                iconClass = 'bg-indigo-100 text-indigo-600 fas fa-envelope';
                break;
            case 'feedback':
                iconClass = 'bg-amber-100 text-amber-600 fas fa-star';
                break;
            default:
                iconClass = 'bg-gray-100 text-gray-600 fas fa-bell';
        }

        const formattedDate = formatDateFromTimestamp(notification.timestamp);

        // Create details HTML
        const detailsHtml = `
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-full ${iconClass} flex items-center justify-center"></div>
                <div>
                    <div class="font-medium text-lg text-secondary">${notification.title}</div>
                    <div class="text-xs text-gray-500">${formattedDate}</div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700 whitespace-pre-line">${notification.message}</p>
            </div>
            <div class="text-sm text-gray-500 mt-4">
                <span class="px-2 py-1 rounded-full text-xs ${notification.isRead ? 'text-gray-500 bg-gray-100' : 'text-blue-600 bg-blue-100 font-medium'}">
                    ${notification.isRead ? 'Read' : 'Unread'}
                </span>
            </div>
        `;

        $('#notification-details').html(detailsHtml);

        // Update mark button based on read status
        if (notification.isRead) {
            $('#modal-mark-button').text('Mark as Unread').off('click').on('click', function() {
                markAsUnread(notification.id);
                hideModal('notificationDetailsModal');
            });
        } else {
            $('#modal-mark-button').text('Mark as Read').off('click').on('click', function() {
                markAsRead(notification.id);
                hideModal('notificationDetailsModal');
            });
        }

        showModal('notificationDetailsModal');

        // If notification is unread, mark it as read when viewed
        if (!notification.isRead) {
            // In a real application, you would send an AJAX request here
            // For this example, we'll just update the local data
            notificationsData[index].isRead = true;
            updateNotificationRow(notification.id);
            updateUnreadCount();
        }
    }

    // Mark notification as read
    function markAsRead(id) {
        // Find notification index
        const index = notificationsData.findIndex(n => n.id === id);
        if (index === -1) return;

        // Update notification data
        notificationsData[index].isRead = true;

        // Update UI
        updateNotificationRow(id);
        updateUnreadCount();

        // Show success message
        showSuccessNotification('Notification marked as read');
    }

    // Mark notification as unread
    function markAsUnread(id) {
        // Find notification index
        const index = notificationsData.findIndex(n => n.id === id);
        if (index === -1) return;

        // Update notification data
        notificationsData[index].isRead = false;

        // Update UI
        updateNotificationRow(id);
        updateUnreadCount();

        // Show success message
        showSuccessNotification('Notification marked as unread');
    }

    // Delete notification
    function deleteNotification(id) {
        $('#delete-message').text('Are you sure you want to delete this notification?');

        $('#confirm-delete').off('click').on('click', function() {
            // Find notification index
            const index = notificationsData.findIndex(n => n.id === id);
            if (index === -1) return;

            // Remove from data array
            notificationsData.splice(index, 1);

            // Update UI
            filterNotifications();
            updatePagination();
            updateUnreadCount();

            // Hide modal
            hideModal('deleteConfirmationModal');

            // Show success message
            showSuccessNotification('Notification deleted successfully');
        });

        showModal('deleteConfirmationModal');
    }

    // Format date from timestamp
    function formatDateFromTimestamp(timestamp) {
        const date = new Date(timestamp * 1000);
        const day = date.getDate();
        const month = date.toLocaleString('default', {
            month: 'long'
        });
        const year = date.getFullYear();
        const hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes.toString().padStart(2, '0');

        // Add suffix to day
        let suffix = 'th';
        if (day % 10 === 1 && day !== 11) suffix = 'st';
        else if (day % 10 === 2 && day !== 12) suffix = 'nd';
        else if (day % 10 === 3 && day !== 13) suffix = 'rd';

        return `${month} ${day}${suffix}, ${year} ${formattedHours}:${formattedMinutes}${ampm}`;
    }

    // Show success notification
    function showSuccessNotification(message) {
        document.getElementById('successMessage').textContent = message;
        const notification = document.getElementById('successNotification');
        notification.classList.remove('hidden');

        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    $(document).ready(function() {
        // Initialize pagination
        updatePagination();

        // Select all checkbox
        $('#select-all').change(function() {
            const isChecked = $(this).prop('checked');
            $('.notification-checkbox:visible').prop('checked', isChecked);
            updateBulkActions();
        });

        // Individual checkboxes
        $(document).on('change', '.notification-checkbox', function() {
            updateBulkActions();

            // If any checkbox is unchecked, uncheck "select all"
            if (!$(this).prop('checked')) {
                $('#select-all').prop('checked', false);
            }

            // If all visible checkboxes are checked, check "select all"
            const allChecked = $('.notification-checkbox:visible:not(:checked)').length === 0;
            if (allChecked && $('.notification-checkbox:visible').length > 0) {
                $('#select-all').prop('checked', true);
            }
        });

        // Mark all as read
        $('#markAllRead').click(function() {
            showConfirmationDialog('markAllRead', 'Are you sure you want to mark all notifications as read?');
        });

        // Delete all read
        $('#deleteAllRead').click(function() {
            showConfirmationDialog('deleteAllRead', 'Are you sure you want to delete all read notifications?');
        });

        // Mark selected as read
        $('#markSelectedRead').click(function() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            markNotificationsAsRead(selectedIds);
        });

        // Mark selected as unread
        $('#markSelectedUnread').click(function() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            markNotificationsAsUnread(selectedIds);
        });

        // Delete selected
        $('#deleteSelected').click(function() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length === 0) return;

            const message = selectedIds.length === 1 ?
                'Are you sure you want to delete this notification?' :
                `Are you sure you want to delete these ${selectedIds.length} notifications?`;

            $('#delete-message').text(message);

            $('#confirm-delete').off('click').on('click', function() {
                deleteNotifications(selectedIds);
                hideModal('deleteConfirmationModal');
            });

            showModal('deleteConfirmationModal');
        });

        // Pagination
        $('#prev-page').click(function() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });

        $('#next-page').click(function() {
            const totalPages = Math.ceil(filteredNotifications.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
            }
        });

        // Search functionality
        $('#searchNotifications').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterNotifications();
        });

        // Filter dropdown
        $('#filterNotifications').change(function() {
            filterNotifications();
        });
    });

    // Get selected notification IDs
    function getSelectedIds() {
        const ids = [];
        $('.notification-checkbox:checked').each(function() {
            ids.push(parseInt($(this).data('id')));
        });
        return ids;
    }

    // Update notification row
    function updateNotificationRow(id) {
        const row = $(`tr[data-notification-id="${id}"]`);
        if (!row.length) return;

        const index = notificationsData.findIndex(n => n.id === id);
        if (index === -1) return;

        const notification = notificationsData[index];

        // Update read status
        const statusClass = notification.isRead ? 'text-gray-500 bg-gray-100' : 'text-blue-600 bg-blue-100 font-medium';
        row.find('td:nth-child(4) span').removeClass().addClass(`px-2 py-1 rounded-full text-xs ${statusClass}`).text(notification.isRead ? 'Read' : 'Unread');

        // Update row background
        if (notification.isRead) {
            row.removeClass('bg-blue-50/30');
        } else {
            row.addClass('bg-blue-50/30');
        }

        // Update data attribute
        row.attr('data-is-read', notification.isRead ? 'true' : 'false');

        // Update action buttons
        const actionCell = row.find('td:nth-child(5) .flex');
        actionCell.empty();

        actionCell.append(`
            <button onclick="viewNotificationDetails(${index})" class="text-blue-600 hover:text-blue-800 p-1" title="View Details">
                <i class="fas fa-eye"></i>
                <span class="sr-only">View Details</span>
            </button>
        `);

        if (notification.isRead) {
            actionCell.append(`
                <button onclick="markAsUnread(${notification.id})" class="text-gray-600 hover:text-gray-800 p-1" title="Mark as Unread">
                    <i class="fas fa-envelope"></i>
                    <span class="sr-only">Mark as Unread</span>
                </button>
            `);
        } else {
            actionCell.append(`
                <button onclick="markAsRead(${notification.id})" class="text-green-600 hover:text-green-800 p-1" title="Mark as Read">
                    <i class="fas fa-check"></i>
                    <span class="sr-only">Mark as Read</span>
                </button>
            `);
        }

        actionCell.append(`
            <button onclick="deleteNotification(${notification.id})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                <i class="fas fa-trash-alt"></i>
                <span class="sr-only">Delete</span>
            </button>
        `);
    }

    // Update unread count
    function updateUnreadCount() {
        const unreadCount = notificationsData.filter(n => !n.isRead).length;
        $('h1 + p').text(`You have ${unreadCount} unread notifications`);
    }

    // Update bulk actions visibility
    function updateBulkActions() {
        const selectedCount = $('.notification-checkbox:checked').length;
        $('#selected-count').text(`${selectedCount} selected`);

        if (selectedCount > 0) {
            $('#bulk-actions').removeClass('hidden');
        } else {
            $('#bulk-actions').addClass('hidden');
        }
    }

    // Filter notifications
    function filterNotifications() {
        const searchTerm = $('#searchNotifications').val().toLowerCase();
        const filterType = $('#filterNotifications').val();

        // Reset current page
        currentPage = 1;

        // Filter based on search term and filter type
        filteredNotifications = notificationsData.filter(notification => {
            const matchesSearch = notification.title.toLowerCase().includes(searchTerm) ||
                notification.message.toLowerCase().includes(searchTerm);

            if (filterType === 'unread') {
                return matchesSearch && !notification.isRead;
            } else if (filterType === 'read') {
                return matchesSearch && notification.isRead;
            } else {
                return matchesSearch;
            }
        });

        // Update pagination
        updatePagination();

        // Update "select all" checkbox
        $('#select-all').prop('checked', false);

        // Hide bulk actions
        $('#bulk-actions').addClass('hidden');
    }

    // Update pagination
    function updatePagination() {
        const totalItems = filteredNotifications.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        // Update pagination info
        const start = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        $('#showing-start').text(start);
        $('#showing-end').text(end);
        $('#total-notifications').text(totalItems);

        // Update pagination buttons
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages || totalPages === 0);

        // Update pagination numbers
        let paginationHtml = '';

        if (totalPages <= 5) {
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${i}</button>`;
                } else {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${i})">${i}</button>`;
                }
            }
        } else {
            // First page
            if (currentPage === 1) {
                paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">1</button>`;
            } else {
                paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(1)">1</button>`;
            }

            // Ellipsis or pages
            if (currentPage > 3) {
                paginationHtml += `<span class="px-2">...</span>`;
            }

            // Pages around current
            for (let i = Math.max(2, currentPage - 1); i <= Math.min(totalPages - 1, currentPage + 1); i++) {
                if (i === currentPage) {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${i}</button>`;
                } else {
                    paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${i})">${i}</button>`;
                }
            }

            // Ellipsis or pages
            if (currentPage < totalPages - 2) {
                paginationHtml += `<span class="px-2">...</span>`;
            }

            // Last page
            if (currentPage === totalPages) {
                paginationHtml += `<button class="px-3 py-2 rounded-lg bg-user-primary text-white">${totalPages}</button>`;
            } else {
                paginationHtml += `<button class="px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50" onclick="goToPage(${totalPages})">${totalPages}</button>`;
            }
        }

        $('#pagination-numbers').html(paginationHtml || '');

        // Update table rows
        const tableBody = $('#notifications-table-body');
        tableBody.empty();

        // Update mobile view
        $('.md\\:hidden > div[data-notification-id]').hide();

        // Show empty state if no notifications
        if (filteredNotifications.length === 0) {
            $('#empty-state').removeClass('hidden');
            tableBody.parent().addClass('hidden');
            $('.md\\:hidden').addClass('hidden');
        } else {
            $('#empty-state').addClass('hidden');
            tableBody.parent().removeClass('hidden');
            $('.md\\:hidden').removeClass('hidden');

            // Render current page rows
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, filteredNotifications.length);

            for (let i = startIndex; i < endIndex; i++) {
                const notification = filteredNotifications[i];
                const originalIndex = notificationsData.findIndex(n => n.id === notification.id);
                const formattedDate = formatDateFromTimestamp(notification.timestamp);
                const statusClass = notification.isRead ? 'text-gray-500 bg-gray-100' : 'text-blue-600 bg-blue-100 font-medium';
                const rowClass = notification.isRead ? '' : 'bg-blue-50/30';

                // Determine icon class based on notification type
                let iconClass = '';
                switch (notification.type) {
                    case 'promotion':
                        iconClass = 'bg-purple-100 text-purple-600 fas fa-tag';
                        break;
                    case 'order':
                        iconClass = 'bg-green-100 text-green-600 fas fa-shipping-fast';
                        break;
                    case 'payment':
                        iconClass = 'bg-blue-100 text-blue-600 fas fa-credit-card';
                        break;
                    case 'system':
                        iconClass = 'bg-gray-100 text-gray-600 fas fa-cog';
                        break;
                    case 'welcome':
                        iconClass = 'bg-yellow-100 text-yellow-600 fas fa-hand-peace';
                        break;
                    case 'price':
                        iconClass = 'bg-red-100 text-red-600 fas fa-dollar-sign';
                        break;
                    case 'security':
                        iconClass = 'bg-red-100 text-red-600 fas fa-shield-alt';
                        break;
                    case 'credit':
                        iconClass = 'bg-green-100 text-green-600 fas fa-wallet';
                        break;
                    case 'message':
                        iconClass = 'bg-indigo-100 text-indigo-600 fas fa-envelope';
                        break;
                    case 'feedback':
                        iconClass = 'bg-amber-100 text-amber-600 fas fa-star';
                        break;
                    default:
                        iconClass = 'bg-gray-100 text-gray-600 fas fa-bell';
                }

                const row = `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors ${rowClass}" data-notification-id="${notification.id}" data-is-read="${notification.isRead ? 'true' : 'false'}">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="notification-checkbox rounded border-gray-300 text-user-primary focus:ring-user-primary" data-id="${notification.id}">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full ${iconClass} flex-shrink-0 flex items-center justify-center"></div>
                                <div class="cursor-pointer notification-title" onclick="viewNotificationDetails(${originalIndex})">
                                    <div class="font-medium text-secondary">${notification.title}</div>
                                    <div class="text-xs text-gray-text mt-1 line-clamp-1">
                                        ${notification.message.substring(0, 100)}${notification.message.length > 100 ? '...' : ''}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-text">${formattedDate}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs ${statusClass}">
                                ${notification.isRead ? 'Read' : 'Unread'}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="viewNotificationDetails(${originalIndex})" class="text-blue-600 hover:text-blue-800 p-1" title="View Details">
                                    <i class="fas fa-eye"></i>
                                    <span class="sr-only">View Details</span>
                                </button>
                                ${notification.isRead ? 
                                    `<button onclick="markAsUnread(${notification.id})" class="text-gray-600 hover:text-gray-800 p-1" title="Mark as Unread">
                                        <i class="fas fa-envelope"></i>
                                        <span class="sr-only">Mark as Unread</span>
                                    </button>` : 
                                    `<button onclick="markAsRead(${notification.id})" class="text-green-600 hover:text-green-800 p-1" title="Mark as Read">
                                        <i class="fas fa-check"></i>
                                        <span class="sr-only">Mark as Read</span>
                                    </button>`
                                }
                                <button onclick="deleteNotification(${notification.id})" class="text-red-600 hover:text-red-800 p-1" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                    <span class="sr-only">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;

                tableBody.append(row);

                // Show corresponding mobile item
                $(`.md\\:hidden > div[data-notification-id="${notification.id}"]`).show();
            }
        }
    }

    // Go to specific page
    function goToPage(page) {
        currentPage = page;
        updatePagination();
    }

    // Show confirmation dialog
    function showConfirmationDialog(action, message) {
        $('#delete-message').text(message);

        $('#confirm-delete').off('click').on('click', function() {
            if (action === 'markAllRead') {
                // Mark all as read
                notificationsData.forEach(notification => {
                    notification.isRead = true;
                });

                // Update UI
                filterNotifications();
                updateUnreadCount();

                // Show success message
                showSuccessNotification('All notifications marked as read');
            } else if (action === 'deleteAllRead') {
                // Find all read notifications
                const readIds = notificationsData.filter(n => n.isRead).map(n => n.id);

                if (readIds.length === 0) {
                    showSuccessNotification('No read notifications to delete');
                    hideModal('deleteConfirmationModal');
                    return;
                }

                // Delete them
                deleteNotifications(readIds);
            }

            hideModal('deleteConfirmationModal');
        });

        showModal('deleteConfirmationModal');
    }

    // Delete multiple notifications
    function deleteNotifications(ids) {
        // Remove notifications from data array
        ids.forEach(id => {
            const index = notificationsData.findIndex(n => n.id === id);
            if (index !== -1) {
                notificationsData.splice(index, 1);
            }
        });

        // Update UI
        filterNotifications();
        updatePagination();
        updateUnreadCount();

        // Show success message
        const message = ids.length === 1 ?
            'Notification deleted successfully' :
            `${ids.length} notifications deleted successfully`;

        showSuccessNotification(message);
    }

    // Mark multiple notifications as read
    function markNotificationsAsRead(ids) {
        // Update notification data
        ids.forEach(id => {
            const index = notificationsData.findIndex(n => n.id === id);
            if (index !== -1) {
                notificationsData[index].isRead = true;
            }
        });

        // Update UI
        ids.forEach(id => {
            updateNotificationRow(id);
        });
        updateUnreadCount();

        // Show success message
        const message = ids.length === 1 ?
            'Notification marked as read' :
            `${ids.length} notifications marked as read`;

        showSuccessNotification(message);
    }

    // Mark multiple notifications as unread
    function markNotificationsAsUnread(ids) {
        // Update notification data
        ids.forEach(id => {
            const index = notificationsData.findIndex(n => n.id === id);
            if (index !== -1) {
                notificationsData[index].isRead = false;
            }
        });

        // Update UI
        ids.forEach(id => {
            updateNotificationRow(id);
        });
        updateUnreadCount();

        // Show success message
        const message = ids.length === 1 ?
            'Notification marked as unread' :
            `${ids.length} notifications marked as unread`;

        showSuccessNotification(message);
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>