<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Member Subscriptions';
$activeNav = 'member-subscription';

// Sample subscription data array - in a real application, this would come from a database
$subscriptionList = [
    [
        'id' => '76555',
        'user' => 'Paul',
        'activation_code' => '',
        'contacts' => '256764421747',
        'address' => 'Wakiso, Kira, Bweyogerere',
        'location' => 'Bweyogerere TC',
        'activation_date' => null,
        'expiry_date' => null,
        'status' => 'pending',
        'payment_amount' => 32000,
        'payment_method' => 'Mobile Money',
        'account_details' => 'Masika<br>Zzimba online Momo'
    ],
    [
        'id' => '64273',
        'user' => 'TEM',
        'activation_code' => '1672',
        'contacts' => '256700883798',
        'address' => 'Kampala, Nakawa, Kisaasi',
        'location' => 'Ringroad',
        'activation_date' => '2023-12-27 20:16:00',
        'expiry_date' => '2024-01-27 20:16:00',
        'status' => 'expired',
        'payment_amount' => 32000,
        'payment_method' => 'Bank Transfer',
        'account_details' => 'Zzimba Online<br>Equity Bank'
    ],
    [
        'id' => '80716',
        'user' => 'TEM',
        'activation_code' => '',
        'contacts' => '256700883798',
        'address' => 'Mbale, Mbale city, City Center lorry park',
        'location' => 'Kikuubo',
        'activation_date' => null,
        'expiry_date' => null,
        'status' => 'expired',
        'payment_amount' => 32000,
        'payment_method' => 'Mobile Money',
        'account_details' => 'Masika<br>Zzimba online Momo'
    ]
];

// Function to format date
function formatDate($date)
{
    if (!$date) return '-';
    $timestamp = strtotime($date);
    return date('F jS, Y g:iA', $timestamp);
}

// Add a function to format phone numbers
function formatPhoneNumber($phone)
{
    if (!$phone) return '-';
    // Add + prefix if not already present
    return substr($phone, 0, 1) === '+' ? $phone : '+' . $phone;
}

// Function to get status badge class
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'expired':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Member Subscriptions</h1>
            <p class="text-sm text-gray-text mt-1">View and manage member subscriptions</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportSubscriptions" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="filterSubscriptions" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Subscriptions Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Subscription List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="subscription-count"><?= count($subscriptionList) ?></span> subscriptions found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchSubscriptions" placeholder="Search subscriptions..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="sortSubscriptions" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected="selected">Filter by Status</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Subscriptions List -->
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full" id="subscriptions-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">User</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Activation Code</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Contacts</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Address</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Location</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Activation Date</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptionList as $subscription): ?>
                        <tr class="border-b border-gray-100">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($subscription['user']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= $subscription['activation_code'] ?: '-' ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatPhoneNumber($subscription['contacts']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($subscription['address']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= htmlspecialchars($subscription['location']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text">
                                <?= formatDate($subscription['activation_date']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($subscription['status']) ?>">
                                    <?= ucfirst($subscription['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <button
                                        class="action-btn text-blue-600 hover:text-blue-800"
                                        data-tippy-content="Preview Status"
                                        onclick="showSubscriptionPreview('<?= $subscription['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <?php if ($subscription['status'] === 'pending'): ?>
                                        <button
                                            class="action-btn text-green-600 hover:text-green-800"
                                            data-tippy-content="Confirm Subscription"
                                            onclick="confirmSubscription('<?= $subscription['id'] ?>')">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button
                                        class="action-btn text-red-600 hover:text-red-800"
                                        data-tippy-content="Delete Subscription"
                                        onclick="deleteSubscription('<?= $subscription['id'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($subscriptionList) ?></span> of <span id="total-subscriptions"><?= count($subscriptionList) ?></span> subscriptions
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

<!-- Mobile View -->
<div class="md:hidden p-4">
    <?php foreach ($subscriptionList as $subscription): ?>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
            <div class="p-4 border-b border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <p class="font-medium text-gray-900"><?= htmlspecialchars($subscription['user']) ?></p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($subscription['status']) ?>">
                        <?= ucfirst($subscription['status']) ?>
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Activation Code:</span>
                        <span class="text-sm"><?= $subscription['activation_code'] ?: '-' ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Contacts:</span>
                        <span class="text-sm"><?= formatPhoneNumber($subscription['contacts']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Location:</span>
                        <span class="text-sm"><?= htmlspecialchars($subscription['location']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Activation Date:</span>
                        <span class="text-sm"><?= formatDate($subscription['activation_date']) ?></span>
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button
                        class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        onclick="showSubscriptionPreview('<?= $subscription['id'] ?>')">
                        <i class="fas fa-eye mr-1"></i> Preview
                    </button>

                    <?php if ($subscription['status'] === 'pending'): ?>
                        <button
                            class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700"
                            onclick="confirmSubscription('<?= $subscription['id'] ?>')">
                            <i class="fas fa-check-circle mr-1"></i> Confirm
                        </button>
                    <?php endif; ?>

                    <button
                        class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700"
                        onclick="deleteSubscription('<?= $subscription['id'] ?>')">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </div>
            </div>
            <div class="p-4 bg-gray-50">
                <p class="text-xs text-gray-500">Address:</p>
                <p class="text-sm"><?= htmlspecialchars($subscription['address']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Subscription Preview Modal -->
<div id="subscriptionPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideSubscriptionPreviewModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Subscription Preview</h3>
            <button onclick="hideSubscriptionPreviewModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="text-center mb-4">
                <p class="text-gray-600">
                    <strong>Store: </strong>
                    <span id="preview-location"></span><br>
                    <strong>Address: </strong>
                    <span id="preview-address"></span>
                </p>
            </div>

            <div id="preview-status-badge" class="bg-red-600 text-white py-4 rounded-lg mb-4">
                <div class="text-center">
                    <h4 class="text-lg font-medium">Status</h4>
                    <h2 class="text-2xl font-bold mt-1" id="preview-status"></h2>
                </div>
            </div>

            <div id="preview-pending" class="hidden">
                <div class="text-center mb-4">
                    <p>Your Subscription is Being Verified. Account is Activated Upon Successful Payment Transaction.</p>
                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Pay Via</h4>
                        <p id="preview-payment-method"></p>
                        <p class="font-bold mt-2">Amount <span class="text-red-600" id="preview-amount"></span></p>
                    </div>
                </div>
            </div>

            <div id="preview-details" class="hidden">
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-gray-600">Activation Date</div>
                        <div class="font-medium" id="preview-activation-date"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-gray-600">Expiry Date</div>
                        <div class="font-medium" id="preview-expiry-date"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-gray-600">Validity Status</div>
                        <div class="font-medium" id="preview-validity"></div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="contacts.php" class="text-sm text-red-600 hover:text-red-700 underline">
                    Give Us Feedback or Ask for Help
                </a>
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
                <h3 class="text-lg font-semibold text-gray-900">Warning: You're Deleting An Active Subscription!</h3>
            </div>
            <div class="flex justify-center gap-3 mt-6">
                <button onclick="hideDeleteConfirmationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    No
                </button>
                <button onclick="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Yes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Subscription Modal -->
<div id="confirmSubscriptionModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideConfirmSubscriptionModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Payment Confirmation</h3>
            <button onclick="hideConfirmSubscriptionModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="text-center mb-4 text-red-600" id="confirmation-message">
                Please enter the transaction ID to confirm this subscription
            </div>

            <form id="confirmation-form">
                <input type="hidden" id="subscription-id" value="">

                <div class="mb-4">
                    <input
                        type="text"
                        id="transaction-id"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        placeholder="Enter Transaction ID"
                        required>
                </div>

                <div class="mb-4">
                    <label for="payment-method" class="block text-sm font-medium text-gray-700 mb-1">Select Mode of Payment</label>
                    <select
                        id="payment-method"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                        <option value="">Select</option>
                        <option value="momo">Zzimba online Momo</option>
                        <option value="bank">DFCU Bugolobi</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        type="button"
                        onclick="hideConfirmSubscriptionModal()"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Submit
                    </button>
                </div>
            </form>
        </div>
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

        // Search functionality
        document.getElementById('searchSubscriptions').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#subscriptions-table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            // Also filter mobile cards
            const cards = document.querySelectorAll('.md\\:hidden > div');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('sortSubscriptions').addEventListener('change', function(e) {
            const status = e.target.value.toLowerCase();

            if (!status) {
                // Show all if no filter selected
                document.querySelectorAll('#subscriptions-table tbody tr').forEach(row => {
                    row.style.display = '';
                });
                document.querySelectorAll('.md\\:hidden > div').forEach(card => {
                    card.style.display = '';
                });
                return;
            }

            // Filter desktop rows
            document.querySelectorAll('#subscriptions-table tbody tr').forEach(row => {
                const rowStatus = row.querySelector('td:nth-child(7) span').textContent.toLowerCase();
                row.style.display = rowStatus === status ? '' : 'none';
            });

            // Filter mobile cards
            document.querySelectorAll('.md\\:hidden > div').forEach(card => {
                const cardStatus = card.querySelector('.rounded-full').textContent.toLowerCase();
                card.style.display = cardStatus === status ? '' : 'none';
            });
        });

        // Confirmation form submission
        document.getElementById('confirmation-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const subscriptionId = document.getElementById('subscription-id').value;
            const transactionId = document.getElementById('transaction-id').value;
            const paymentMethod = document.getElementById('payment-method').value;

            if (!transactionId || !paymentMethod) {
                alert('Please fill in all required fields');
                return;
            }

            // Here you would typically make an API call to confirm the subscription
            console.log('Confirming subscription:', {
                subscriptionId,
                transactionId,
                paymentMethod
            });

            alert('Subscription confirmed successfully!');
            hideConfirmSubscriptionModal();
        });
    });

    // Subscription data
    const subscriptionData = <?= json_encode($subscriptionList) ?>;

    function showSubscriptionPreview(id) {
        const subscription = subscriptionData.find(s => s.id === id);
        if (!subscription) return;

        // Update modal content
        document.getElementById('preview-location').textContent = subscription.location;
        document.getElementById('preview-address').textContent = subscription.address;
        document.getElementById('preview-status').textContent = subscription.status.toUpperCase();

        // Update status badge color
        const statusBadge = document.getElementById('preview-status-badge');
        statusBadge.className = `text-white py-4 rounded-lg mb-4 ${
        subscription.status === 'pending' ? 'bg-yellow-600' :
        subscription.status === 'active' ? 'bg-green-600' : 'bg-red-600'
    }`;

        // Show/hide sections based on status
        const pendingSection = document.getElementById('preview-pending');
        const detailsSection = document.getElementById('preview-details');

        if (subscription.status === 'pending') {
            pendingSection.classList.remove('hidden');
            detailsSection.classList.add('hidden');
            document.getElementById('preview-payment-method').innerHTML = subscription.payment_method;
            document.getElementById('preview-amount').textContent = `UGX ${subscription.payment_amount.toLocaleString()}`;
        } else {
            pendingSection.classList.add('hidden');
            detailsSection.classList.remove('hidden');
            document.getElementById('preview-activation-date').textContent = formatDate(subscription.activation_date);
            document.getElementById('preview-expiry-date').textContent = formatDate(subscription.expiry_date);
            document.getElementById('preview-validity').textContent = subscription.status.toUpperCase();
        }

        // Show modal
        document.getElementById('subscriptionPreviewModal').classList.remove('hidden');
    }

    function hideSubscriptionPreviewModal() {
        document.getElementById('subscriptionPreviewModal').classList.add('hidden');
    }

    function confirmSubscription(id) {
        // Set the subscription ID in the hidden field
        document.getElementById('subscription-id').value = id;

        // Show the confirmation modal
        document.getElementById('confirmSubscriptionModal').classList.remove('hidden');
    }

    function hideConfirmSubscriptionModal() {
        document.getElementById('confirmSubscriptionModal').classList.add('hidden');
        document.getElementById('transaction-id').value = '';
        document.getElementById('payment-method').value = '';
    }

    function deleteSubscription(id) {
        // Store the ID for confirmation
        document.getElementById('deleteConfirmationModal').setAttribute('data-subscription-id', id);
        document.getElementById('deleteConfirmationModal').classList.remove('hidden');
    }

    function hideDeleteConfirmationModal() {
        document.getElementById('deleteConfirmationModal').classList.add('hidden');
    }

    function confirmDelete() {
        const id = document.getElementById('deleteConfirmationModal').getAttribute('data-subscription-id');
        // Here you would typically make an API call to delete the subscription
        console.log('Deleting subscription:', id);
        alert('Subscription deleted successfully!');
        hideDeleteConfirmationModal();
    }

    // Helper function to format dates
    function formatDate(date) {
        if (!date) return '-';

        const d = new Date(date);
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        const month = months[d.getMonth()];
        const day = d.getDate();
        const year = d.getFullYear();

        // Add ordinal suffix to day
        let suffix = 'th';
        if (day === 1 || day === 21 || day === 31) suffix = 'st';
        else if (day === 2 || day === 22) suffix = 'nd';
        else if (day === 3 || day === 23) suffix = 'rd';

        // Format time
        let hours = d.getHours();
        const minutes = d.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // Convert 0 to 12

        return `${month} ${day}${suffix}, ${year} ${hours}:${minutes}${ampm}`;
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>