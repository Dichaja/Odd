<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Order History';
$activeNav = 'order-history';

// Sample data for orders
$orders = [
    [
        'id' => 'ORD-2024-001',
        'date' => '2024-02-15 14:30:00',
        'items' => [
            ['name' => 'Product A', 'quantity' => 2, 'price' => '45,000'],
            ['name' => 'Product B', 'quantity' => 1, 'price' => '30,000']
        ],
        'total' => '120,000',
        'status' => 'delivered',
        'payment_method' => 'Mobile Money',
        'shipping_address' => 'Plot 123, Kampala Road, Kampala',
        'tracking_number' => 'TRK123456789'
    ],
    [
        'id' => 'ORD-2024-002',
        'date' => '2024-02-10 09:15:00',
        'items' => [
            ['name' => 'Product C', 'quantity' => 1, 'price' => '75,000']
        ],
        'total' => '75,000',
        'status' => 'processing',
        'payment_method' => 'Bank Transfer',
        'shipping_address' => 'Plot 456, Jinja Road, Kampala',
        'tracking_number' => 'TRK987654321'
    ],
    [
        'id' => 'ORD-2024-003',
        'date' => '2024-02-05 16:45:00',
        'items' => [
            ['name' => 'Product D', 'quantity' => 3, 'price' => '25,000'],
            ['name' => 'Product E', 'quantity' => 2, 'price' => '35,000']
        ],
        'total' => '145,000',
        'status' => 'pending',
        'payment_method' => 'Zzimba Credit',
        'shipping_address' => 'Plot 789, Entebbe Road, Kampala',
        'tracking_number' => 'TRK456789123'
    ]
];

ob_start();
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="content-section bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-user-primary/10 flex items-center justify-center">
                        <i class="fas fa-shopping-bag text-user-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-semibold text-secondary">Order History</h1>
                        <p class="text-sm text-gray-text">View and track your orders</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" id="searchOrders" placeholder="Search orders..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <select id="filterOrders" class="h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                        <option value="all">All Orders</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List Section -->
    <div class="content-section bg-white rounded-lg shadow-sm border border-gray-100">
        <!-- Desktop View -->
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full" id="orders-table">
                    <thead>
                        <tr class="text-left border-b border-gray-100">
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Order ID</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Date</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Items</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Total</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                            <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-secondary"><?= $order['id'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-text"><?= date('M j, Y', strtotime($order['date'])) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-text">
                                    <?= count($order['items']) ?> item(s)
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-secondary">UGX <?= $order['total'] ?></td>
                                <td class="px-6 py-4">
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="text-user-primary hover:text-user-primary/80 transition-colors mr-3"
                                        onclick="showOrderDetails('<?= $order['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($order['status'] !== 'delivered'): ?>
                                        <button class="text-user-primary hover:text-user-primary/80 transition-colors"
                                            onclick="trackOrder('<?= $order['tracking_number'] ?>')">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden">
            <?php foreach ($orders as $order): ?>
                <div class="border-b border-gray-100 p-4">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="font-medium text-secondary"><?= $order['id'] ?></div>
                            <div class="text-xs text-gray-text"><?= date('M j, Y', strtotime($order['date'])) ?></div>
                        </div>
                        <span class="status-badge status-<?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <div class="text-sm text-gray-text"><?= count($order['items']) ?> item(s)</div>
                        <div class="font-medium text-secondary">UGX <?= $order['total'] ?></div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button class="px-3 py-2 text-sm text-user-primary hover:bg-user-primary/10 rounded-lg transition-colors"
                            onclick="showOrderDetails('<?= $order['id'] ?>')">
                            <i class="fas fa-eye mr-1"></i> View Details
                        </button>
                        <?php if ($order['status'] !== 'delivered'): ?>
                            <button class="px-3 py-2 text-sm text-user-primary hover:bg-user-primary/10 rounded-lg transition-colors"
                                onclick="trackOrder('<?= $order['tracking_number'] ?>')">
                                <i class="fas fa-truck mr-1"></i> Track Order
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end">3</span> of <span id="total-orders"><?= count($orders) ?></span> orders
            </div>
            <div class="flex items-center gap-2">
                <button id="prev-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="pagination-numbers" class="flex items-center">
                    <button class="px-3 py-2 rounded-lg bg-user-primary text-white">1</button>
                </div>
                <button id="next-page" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('orderDetailsModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Order Details</h3>
                <button onclick="hideModal('orderDetailsModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="orderDetails" class="space-y-6">
                <!-- Order details will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Order Tracking Modal -->
<div id="trackingModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('trackingModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Track Order</h3>
                <button onclick="hideModal('trackingModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="trackingDetails" class="space-y-6">
                <!-- Tracking details will be populated here -->
            </div>
        </div>
    </div>
</div>

<style>
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-processing {
        background-color: #e0f2fe;
        color: #075985;
    }

    .status-delivered {
        background-color: #dcfce7;
        color: #166534;
    }

    .tracking-step {
        position: relative;
        padding-left: 2rem;
    }

    .tracking-step::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e5e7eb;
    }

    .tracking-step:last-child::before {
        display: none;
    }

    .tracking-step.completed::before {
        background-color: #22c55e;
    }

    .tracking-dot {
        position: absolute;
        left: 0;
        top: 0.25rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background-color: #e5e7eb;
    }

    .tracking-step.completed .tracking-dot {
        background-color: #22c55e;
    }
</style>

<script>
    $(document).ready(function() {
        // Search functionality
        $('#searchOrders').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterOrders(searchTerm);
        });

        // Filter dropdown
        $('#filterOrders').change(function() {
            const filterValue = $(this).val();
            const searchTerm = $('#searchOrders').val().toLowerCase();
            filterOrders(searchTerm, filterValue);
        });

        // Initialize pagination
        updatePagination();
    });

    function showOrderDetails(orderId) {
        // Find order data
        const order = <?= json_encode($orders) ?>.find(o => o.id === orderId);

        if (order) {
            const detailsHtml = `
                <div class="border-b border-gray-100 pb-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Order Date</p>
                            <p class="font-medium">${new Date(order.date).toLocaleDateString('en-US', {                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="font-medium">${order.payment_method}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Shipping Address</p>
                            <p class="font-medium">${order.shipping_address}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <h4 class="font-medium">Order Items</h4>
                    ${order.items.map(item => `
                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                            <div>
                                <p class="font-medium">${item.name}</p>
                                <p class="text-sm text-gray-500">Quantity: ${item.quantity}</p>
                            </div>
                            <div class="font-medium">UGX ${item.price}</div>
                        </div>
                    `).join('')}
                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <div class="font-semibold">Total</div>
                        <div class="font-semibold">UGX ${order.total}</div>
                    </div>
                </div>
            `;

            $('#orderDetails').html(detailsHtml);
            showModal('orderDetailsModal');
        }
    }

    function trackOrder(trackingNumber) {
        // Sample tracking data - in a real application, this would come from your backend
        const trackingSteps = [{
                status: 'Order Placed',
                date: '2024-02-15 14:30:00',
                completed: true
            },
            {
                status: 'Payment Confirmed',
                date: '2024-02-15 15:00:00',
                completed: true
            },
            {
                status: 'Processing',
                date: '2024-02-16 09:00:00',
                completed: true
            },
            {
                status: 'Out for Delivery',
                date: '2024-02-17 08:30:00',
                completed: false
            },
            {
                status: 'Delivered',
                date: '',
                completed: false
            }
        ];

        const trackingHtml = `
            <div class="mb-4">
                <p class="text-sm text-gray-500">Tracking Number</p>
                <p class="font-medium">${trackingNumber}</p>
            </div>
            <div class="space-y-6">
                ${trackingSteps.map(step => `
                    <div class="tracking-step ${step.completed ? 'completed' : ''}">
                        <div class="tracking-dot"></div>
                        <div class="ml-6">
                            <p class="font-medium">${step.status}</p>
                            ${step.date ? `
                                <p class="text-sm text-gray-500">${new Date(step.date).toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}</p>
                            ` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        $('#trackingDetails').html(trackingHtml);
        showModal('trackingModal');
    }

    function filterOrders(searchTerm = '', status = 'all') {
        const rows = $('#orders-table tbody tr');
        rows.each(function() {
            const orderId = $(this).find('td:first').text().toLowerCase();
            const orderStatus = $(this).find('.status-badge').text().toLowerCase();
            const shouldShow =
                orderId.includes(searchTerm) &&
                (status === 'all' || orderStatus === status.toLowerCase());

            $(this).toggle(shouldShow);
        });

        updatePagination();
    }

    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function updatePagination() {
        const itemsPerPage = 5;
        const visibleRows = $('#orders-table tbody tr:visible');
        const totalItems = visibleRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const currentPage = 1;

        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);

        $('#showing-start').text(start);
        $('#showing-end').text(end);
        $('#total-orders').text(totalItems);

        // Update pagination buttons
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);

        // Show/hide rows based on current page
        visibleRows.each(function(index) {
            $(this).toggle(index >= start - 1 && index < end);
        });
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>