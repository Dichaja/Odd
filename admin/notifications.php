<?php
$pageTitle = 'Notifications';
$activeNav = 'notifications';
ob_start();

// Example notifications array
// In a real application, this data would come from a database query (with read/unread status).
$notifications = [];
for ($i = 1; $i <= 45; $i++) {
    $notifications[] = [
        'id'        => $i,
        'title'     => "Notification Title {$i}",
        'message'   => "This is the detailed message for notification #{$i}.",
        'is_read'   => ($i % 3 === 0), // Just a dummy condition to mark every 3rd as read.
        'timestamp' => date('Y-m-d H:i:s', strtotime("-{$i} hours")),
    ];
}

// Calculate summary stats
$totalCount  = count($notifications);
$readCount   = count(array_filter($notifications, fn($n) => $n['is_read']));
$unreadCount = $totalCount - $readCount;

// Handle pagination
$perPage = 20;
$totalPages = ceil($totalCount / $perPage);
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$startIndex = ($currentPage - 1) * $perPage;
$pagedNotifications = array_slice($notifications, $startIndex, $perPage);
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <!-- Summary & Bulk Actions -->
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-secondary">Notifications</h2>
                    <p class="text-sm text-gray-text mt-2">
                        Total: <span id="totalCount"><?= $totalCount ?></span> &bull;
                        Read: <span id="readCount"><?= $readCount ?></span> &bull;
                        Unread: <span id="unreadCount"><?= $unreadCount ?></span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="bulkMarkAsRead()"
                        class="px-3 py-1 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Mark as Read
                    </button>
                    <button onclick="bulkMarkAsUnread()"
                        class="px-3 py-1 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                        Mark as Unread
                    </button>
                    <button onclick="bulkDelete()"
                        class="px-3 py-1 border border-gray-200 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-colors">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications Table -->
        <div class="overflow-x-auto">
            <table class="w-full" id="notificationsTable">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3">
                            <input type="checkbox" id="selectAll" onclick="toggleAllSelections(this)">
                        </th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Title</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date &amp; Time</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagedNotifications)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                No notifications found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagedNotifications as $notif): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer <?= !$notif['is_read'] ? 'bg-blue-50' : '' ?>"
                                data-id="<?= $notif['id'] ?>"
                                onclick="rowClicked(event, <?= $notif['id'] ?>)">
                                <td class="px-6 py-4 w-12" onclick="event.stopPropagation();">
                                    <input type="checkbox" class="notifCheckbox"
                                        value="<?= $notif['id'] ?>"
                                        onclick="event.stopPropagation();">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-secondary">
                                        <?= htmlspecialchars($notif['title']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= date('M j, Y g:i A', strtotime($notif['timestamp'])) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($notif['is_read']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Read</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Unread</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-6 border-t border-gray-100 flex items-center justify-between">
                <div class="text-sm text-gray-text">
                    Page <?= $currentPage ?> of <?= $totalPages ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>"
                            class="px-3 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?page=<?= $p ?>"
                            class="px-3 py-1 border border-gray-200 rounded-lg text-sm <?= $p == $currentPage ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-50' ?> transition-colors">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>"
                            class="px-3 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors text-sm">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Off-canvas / Modal for Notification Details -->
<div id="notificationDetailsOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideNotificationDetails()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="notifTitle">Notification Details</h3>
                <button onclick="hideNotificationDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div class="space-y-4" id="notifDetailsContent">
                    <!-- Populated by JavaScript -->
                </div>
                <div class="mt-6 flex items-center gap-2">
                    <button id="toggleReadButton"
                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors">
                        Mark as Read
                    </button>
                    <button onclick="deleteCurrent()"
                        class="px-4 py-2 border border-red-500 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors">
                        Delete Notification
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // We store all notifications in a JS object for demonstration.
    // Typically, you'd fetch/update these from a server or DB.
    let allNotifications = <?php echo json_encode($notifications); ?>;
    let currentNotifId = null;

    // Prevent row click if user clicks the checkbox
    function rowClicked(e, notifId) {
        // If user specifically clicks on the checkbox cell, do nothing
        // (this check is done in the table's HTML with "event.stopPropagation()")
        showNotificationDetails(notifId);
    }

    // Show Notification Detail Off-canvas
    function showNotificationDetails(notifId) {
        const notif = allNotifications.find(n => n.id === notifId);
        currentNotifId = notifId;
        if (!notif) return;

        const offcanvas = document.getElementById('notificationDetailsOffcanvas');
        document.getElementById('notifTitle').textContent = notif.title;
        const content = document.getElementById('notifDetailsContent');
        content.innerHTML = `
            <p class="text-sm text-gray-text"><strong>Message:</strong><br>${notif.message}</p>
            <p class="text-sm text-gray-text"><strong>Date/Time:</strong> ${formatDateTime(notif.timestamp)}</p>
            <p class="text-sm text-gray-text"><strong>Status:</strong>
                ${notif.is_read 
                    ? '<span class="text-green-600 font-medium">Read</span>' 
                    : '<span class="text-red-600 font-medium">Unread</span>'
                }
            </p>
        `;
        // Toggle button
        const toggleBtn = document.getElementById('toggleReadButton');
        toggleBtn.textContent = notif.is_read ? 'Mark as Unread' : 'Mark as Read';
        toggleBtn.onclick = () => toggleReadState(notifId);

        offcanvas.classList.remove('hidden');
        setTimeout(() => {
            offcanvas.querySelector('.transform').classList.remove('translate-x-full');
        }, 0);
    }

    // Hide Notification Detail Off-canvas
    function hideNotificationDetails() {
        const offcanvas = document.getElementById('notificationDetailsOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(() => {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    // Toggle read/unread
    function toggleReadState(notifId) {
        const notifIndex = allNotifications.findIndex(n => n.id === notifId);
        if (notifIndex === -1) return;
        const notif = allNotifications[notifIndex];
        notif.is_read = !notif.is_read;
        showNotificationDetails(notifId);
        updateTableRow(notif);
        updateSummary();
    }

    // Update read/unread summary at the top
    function updateSummary() {
        const totalCountEl = document.getElementById('totalCount');
        const readCountEl = document.getElementById('readCount');
        const unreadCountEl = document.getElementById('unreadCount');
        const totalCount = allNotifications.length;
        const readCount = allNotifications.filter(n => n.is_read).length;
        const unreadCount = totalCount - readCount;
        totalCountEl.textContent = totalCount;
        readCountEl.textContent = readCount;
        unreadCountEl.textContent = unreadCount;
    }

    // Delete current notification from off-canvas
    function deleteCurrent() {
        if (!currentNotifId) return;
        deleteNotification(currentNotifId);
        hideNotificationDetails();
    }

    // Delete a notification from the array and remove its row
    function deleteNotification(notifId) {
        allNotifications = allNotifications.filter(n => n.id !== notifId);
        removeTableRow(notifId);
        updateSummary();
    }

    function updateTableRow(notif) {
        const row = document.querySelector(`#notificationsTable tbody tr[data-id="${notif.id}"]`);
        if (!row) return;
        row.classList.toggle('bg-blue-50', !notif.is_read);
        const statusCell = row.querySelectorAll('td')[3];
        if (notif.is_read) {
            statusCell.innerHTML = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    Read
                </span>
            `;
        } else {
            statusCell.innerHTML = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Unread
                </span>
            `;
        }
    }

    // Remove a row from the table by ID
    function removeTableRow(notifId) {
        const row = document.querySelector(`#notificationsTable tbody tr[data-id="${notifId}"]`);
        if (row) {
            row.remove();
        }
    }

    // Format date/time for display
    function formatDateTime(dt) {
        const dateObj = new Date(dt);
        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        };
        return dateObj.toLocaleString(undefined, options);
    }

    // Select all toggler
    function toggleAllSelections(masterCheckbox) {
        const checkboxes = document.querySelectorAll('#notificationsTable .notifCheckbox');
        checkboxes.forEach(chk => {
            chk.checked = masterCheckbox.checked;
        });
    }

    // Bulk Mark as Read
    function bulkMarkAsRead() {
        const checkedIds = getSelectedIds();
        if (!checkedIds.length) return;
        checkedIds.forEach(id => {
            const notif = allNotifications.find(n => n.id === id);
            if (notif && !notif.is_read) {
                notif.is_read = true;
                updateTableRow(notif);
            }
        });
        updateSummary();
        document.getElementById('selectAll').checked = false;
    }

    // Bulk Mark as Unread
    function bulkMarkAsUnread() {
        const checkedIds = getSelectedIds();
        if (!checkedIds.length) return;
        checkedIds.forEach(id => {
            const notif = allNotifications.find(n => n.id === id);
            if (notif && notif.is_read) {
                notif.is_read = false;
                updateTableRow(notif);
            }
        });
        updateSummary();
        document.getElementById('selectAll').checked = false;
    }

    // Bulk Delete
    function bulkDelete() {
        const checkedIds = getSelectedIds();
        if (!checkedIds.length) return;
        checkedIds.forEach(id => {
            deleteNotification(id);
        });
        document.getElementById('selectAll').checked = false;
    }

    // Get array of IDs of selected notifications
    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('#notificationsTable .notifCheckbox:checked');
        return Array.from(checkboxes).map(chk => parseInt(chk.value, 10));
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>