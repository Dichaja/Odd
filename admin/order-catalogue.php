<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Order Catalog';
$activeNav = 'order-catalogue';

// Sample order data array - in a real application, this would come from a database
$orderList = [
    [
        'id' => '263709',
        'product_name' => 'Clean Water delivery 3000 Litre Truck',
        'product_image' => 'img_data/prd/2025-02/665179_1740471607.jpg',
        'product_url' => 'product-665179',
        'token' => 'Z102410',
        'price' => 100000,
        'quantity' => 1,
        'subtotal' => 100000,
        'delivery_cost' => 16667,
        'total' => 116667,
        'status' => 'pending',
        'payment_method' => 'Bank',
        'customer_name' => 'Mutwali',
        'order_date' => '2025-02-24 23:26:00',
        'site_details' => 'Kampala, Kawempe, Jinja, Jinja Kaloli Catholic Church Road',
        'contacts' => '256780850410',
        'order_category' => 'Retail Price Purchase',
        'distance' => 10,
        'actions' => ['confirm', 'supplier']
    ],
    [
        'id' => '371936',
        'product_name' => 'Tororo Cement CEM IV 32.5 Red 50 Kg Bag',
        'product_image' => 'img_data/prd/2024-04/40869_1714302009.png',
        'product_url' => 'product-40869',
        'token' => 'Z100118',
        'price' => 32500,
        'quantity' => 50,
        'subtotal' => 1625000,
        'delivery_cost' => 74000,
        'total' => 1699000,
        'status' => 'pending',
        'payment_method' => 'Bank',
        'customer_name' => 'Kaloli',
        'order_date' => '2025-01-01 02:17:00',
        'site_details' => 'Wakiso, Nabweru, Nabweru court, Nabweru',
        'contacts' => '256705431823',
        'order_category' => 'Retail Price Purchase',
        'distance' => 6,
        'actions' => ['confirm', 'supplier']
    ],
    [
        'id' => '924638',
        'product_name' => 'Polythene Kavera full roll G1000 20 Kg Roll',
        'product_image' => 'img_data/prd/2021-09-01/509658_1630511546.jpg',
        'product_url' => 'product-509658',
        'token' => 'Z100111',
        'price' => 120000,
        'quantity' => 1,
        'subtotal' => 120000,
        'delivery_cost' => 18333,
        'total' => 138333,
        'status' => 'pending',
        'payment_method' => 'Mobile Money',
        'customer_name' => 'Kaloli',
        'order_date' => '2025-01-01 01:53:00',
        'site_details' => 'Wakiso, Nabweru, Nabweru court, Nabweru',
        'contacts' => '256705431823',
        'order_category' => 'Retail Price Purchase',
        'distance' => 11,
        'actions' => ['confirm', 'supplier']
    ],
    [
        'id' => '354365',
        'product_name' => 'Jumper Compactor for Hire Day @ Machine Unit Lease',
        'product_image' => 'img_data/prd/2021-09-07/177685_1631006903.png',
        'product_url' => 'product-177685',
        'token' => 'Z102965',
        'price' => 70000,
        'quantity' => 1,
        'subtotal' => 70000,
        'delivery_cost' => 25000,
        'total' => 95000,
        'status' => 'pending',
        'payment_method' => 'Bank',
        'customer_name' => 'Musaazi',
        'order_date' => '2024-11-29 14:55:00',
        'site_details' => 'Wakiso, Nansana, Kyebando, The actual site is in kyebelese lukwanga though not on your list of places',
        'contacts' => '256777704726',
        'order_category' => 'Retail Price Purchase',
        'distance' => 15,
        'actions' => ['confirm', 'supplier']
    ],
    [
        'id' => '888167',
        'product_name' => 'Uganda Clays maxpan 6 Inch Piece',
        'product_image' => 'img_data/prd/2022-02-18/185388_1645181993.jpg',
        'product_url' => 'product-185388',
        'token' => 'Z101450',
        'price' => 6500,
        'quantity' => 1,
        'subtotal' => 6500,
        'delivery_cost' => 11667,
        'total' => 18167,
        'status' => 'assigned',
        'payment_method' => 'Mobile Money',
        'customer_name' => 'Kafulu',
        'order_date' => '2024-08-14 16:02:00',
        'site_details' => 'Kampala, Kawempe, Kitezi, Kampala road',
        'contacts' => '256700883798',
        'order_category' => 'Retail Price Purchase',
        'distance' => 7,
        'actions' => ['timeline'],
        'timeline' => [
            'approval_date' => '14/08/2024 16:08:00',
            'approval_id' => 'Kafulu1235',
            'paid_via' => 'Mobile Money',
            'account_details' => 'Masika<br>Zzimba online Momo',
            'assigned_to' => '1 Ruth hardware',
            'assigned_date' => '14/08/2024 16:13:12'
        ]
    ],
    [
        'id' => '314003',
        'product_name' => 'SIMBA General Purpose Cement 32.5R 50 Kg Bag',
        'product_image' => 'img_data/prd/2022-06-23/505327_1656021674.png',
        'product_url' => 'product-505327',
        'token' => 'Z101828',
        'price' => 31500,
        'quantity' => 10,
        'subtotal' => 315000,
        'delivery_cost' => 107000,
        'total' => 422000,
        'status' => 'pending',
        'payment_method' => 'Bank',
        'customer_name' => 'juliana',
        'order_date' => '2024-07-18 17:09:00',
        'site_details' => 'Wakiso, Kajjansi, Namulanda, kawuku',
        'contacts' => '256706041455',
        'order_category' => 'Retail Price Purchase',
        'distance' => 6,
        'actions' => ['confirm', 'supplier']
    ],
    [
        'id' => '203414',
        'product_name' => 'Bow saw and blade 1 Complete Set',
        'product_image' => 'img_data/prd/2021-11-11/24013061_1636660542.jpg',
        'product_url' => 'product-68027',
        'token' => 'Z100195',
        'price' => 25000,
        'quantity' => 3,
        'subtotal' => 75000,
        'delivery_cost' => 11667,
        'total' => 86667,
        'status' => 'delivered',
        'payment_method' => 'Mobile Money',
        'customer_name' => 'Ange',
        'order_date' => '2024-07-01 13:57:00',
        'site_details' => 'Kampala, Nakawa, Kisaasi, Market Center',
        'contacts' => '256773089254',
        'order_category' => 'Retail Price Purchase',
        'distance' => 7,
        'actions' => ['timeline'],
        'timeline' => [
            'approval_date' => '01/07/2024 14:05:00',
            'approval_id' => 'Ange7890',
            'paid_via' => 'Mobile Money',
            'account_details' => 'Masika<br>Zzimba online Momo',
            'assigned_to' => '3 Kampala Hardware',
            'assigned_date' => '01/07/2024 14:30:12'
        ]
    ],
    [
        'id' => '287124',
        'product_name' => 'Godrej 2 lever groover lock 1 Complete Set',
        'product_image' => 'img_data/prd/2024-06/06-0545428_1717583721.jpg',
        'product_url' => 'product-06-0545428',
        'token' => 'Z102836',
        'price' => 50000,
        'quantity' => 1,
        'subtotal' => 50000,
        'delivery_cost' => 10000,
        'total' => 60000,
        'status' => 'delivered',
        'payment_method' => 'Bank',
        'customer_name' => 'Kafulu',
        'order_date' => '2024-06-28 13:34:00',
        'site_details' => 'Kampala, Central Division, Nakasero Market, Opposite pride microfinance',
        'contacts' => '256700883798',
        'order_category' => 'Retail Price Purchase',
        'distance' => 6,
        'actions' => ['timeline'],
        'timeline' => [
            'approval_date' => '28/06/2024 14:10:00',
            'approval_id' => 'Kafulu4567',
            'paid_via' => 'Bank',
            'account_details' => 'Zzimba Online<br>Equity Bank',
            'assigned_to' => '5 Central Hardware',
            'assigned_date' => '28/06/2024 15:20:45'
        ]
    ]
];

// Sample supplier data
$supplierList = [
    [
        'id' => 1,
        'name' => 'Hardware World Ltd',
        'verified' => true,
        'location' => 'Kampala, Central',
        'rating' => 4.5,
        'price' => 31000
    ],
    [
        'id' => 2,
        'name' => 'Kampala Cement Suppliers',
        'verified' => true,
        'location' => 'Kampala, Nakawa',
        'rating' => 4.0,
        'price' => 32000
    ],
    [
        'id' => 3,
        'name' => 'Wakiso Building Materials',
        'verified' => true,
        'location' => 'Wakiso, Nansana',
        'rating' => 3.5,
        'price' => 31500
    ]
];

// Function to format date to 12-hour format
function formatDate($dateString)
{
    $date = new DateTime($dateString);
    return $date->format('M d, Y h:i A');
}

// Function to format currency
function formatCurrency($amount)
{
    return number_format($amount, 0, '.', ',');
}

// Function to get status badge class
function getStatusBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'verified':
            return 'bg-blue-100 text-blue-800';
        case 'assigned':
            return 'bg-blue-100 text-blue-800';
        case 'route':
            return 'bg-indigo-100 text-indigo-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Function to get status dot class
function getStatusDotClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-yellow-600';
        case 'verified':
            return 'bg-blue-600';
        case 'assigned':
            return 'bg-blue-600';
        case 'route':
            return 'bg-indigo-600';
        case 'delivered':
            return 'bg-green-600';
        default:
            return 'bg-gray-600';
    }
}

// Function to get status text
function getStatusText($status, $paymentMethod = '')
{
    switch ($status) {
        case 'pending':
            return 'Pending Verification' . ($paymentMethod ? ' <span class="text-xs">via ' . $paymentMethod . '</span>' : '');
        case 'verified':
            return 'Verified Payment';
        case 'assigned':
            return 'Assigned Vendor';
        case 'route':
            return 'En-route';
        case 'delivered':
            return 'Delivered';
        default:
            return 'Unknown Status';
    }
}

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Order Catalog</h1>
            <p class="text-sm text-gray-text mt-1">View and manage all customer orders</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <button id="exportOrders" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-download"></i>
                <span>Export</span>
            </button>
            <button id="filterOrders" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-secondary">Order List</h2>
                <p class="text-sm text-gray-text mt-1">
                    <span id="order-count"><?= count($orderList) ?></span> orders found
                </p>
            </div>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:w-auto">
                    <input type="text" id="searchOrders" placeholder="Search orders..." class="w-full md:w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select id="sortOrders" class="h-10 pl-3 pr-8 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm w-full">
                        <option value="" selected="selected">Select</option>
                        <option value="pending">Pending Payment</option>
                        <option value="verified">Verified Payment</option>
                        <option value="assigned">Assigned</option>
                        <option value="route">En-route</option>
                        <option value="deliver">Delivered</option>
                        <option value="range">Far Range</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Filter Panel (initially hidden) -->
        <div id="filterPanel" class="px-6 py-4 border-b border-gray-100 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filterOrderStatus" class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                    <select id="filterOrderStatus" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending Payment</option>
                        <option value="verified">Verified Payment</option>
                        <option value="assigned">Assigned</option>
                        <option value="route">En-route</option>
                        <option value="deliver">Delivered</option>
                    </select>
                </div>
                <div>
                    <label for="filterPaymentMethod" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select id="filterPaymentMethod" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="">All Methods</option>
                        <option value="bank">Bank</option>
                        <option value="mobile">Mobile Money</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label for="filterDateRange" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select id="filterDateRange" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="md:col-span-3 flex justify-end mt-4">
                    <button id="resetFilters" class="h-10 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors mr-2">
                        Reset Filters
                    </button>
                    <button id="applyFilters" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="orders-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Product</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Token</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Price</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Quantity</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Subtotal</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Status</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderList as $index => $order): ?>
                        <tr class="border-b border-gray-100">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://placehold.co/80x80" alt="<?= htmlspecialchars($order['product_name']) ?>" class="w-16 h-16 object-cover rounded-md">
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($order['product_name']) ?></p>
                                        <p class="text-xs text-gray-500 mt-1">Ordered by: <?= htmlspecialchars($order['customer_name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= formatDate($order['order_date']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-text"><?= htmlspecialchars($order['token']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-text">UGX <?= formatCurrency($order['price']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-text text-center"><?= $order['quantity'] ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">UGX <?= formatCurrency($order['subtotal']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($order['status']) ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= getStatusDotClass($order['status']) ?> mr-1"></span>
                                    <?= getStatusText($order['status'], $order['payment_method']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <?php if (in_array('confirm', $order['actions'])): ?>
                                        <button
                                            class="action-btn text-blue-600 hover:text-blue-800"
                                            data-tippy-content="Confirm Payment"
                                            onclick="showConfirmPaymentModal('<?= $order['id'] ?>', '<?= $order['token'] ?>', '<?= htmlspecialchars($order['product_name']) ?>', '<?= formatCurrency($order['total']) ?>', '<?= htmlspecialchars($order['customer_name']) ?>')">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (in_array('supplier', $order['actions'])): ?>
                                        <button
                                            class="action-btn text-green-600 hover:text-green-800"
                                            data-tippy-content="Assign Supplier"
                                            onclick="showGetSupplierModal('<?= $order['id'] ?>', '<?= $order['token'] ?>', '<?= htmlspecialchars($order['product_name']) ?>', '<?= $order['quantity'] ?>', '<?= htmlspecialchars($order['site_details']) ?>')">
                                            <i class="fas fa-truck"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (in_array('timeline', $order['actions'])): ?>
                                        <button
                                            class="action-btn text-purple-600 hover:text-purple-800"
                                            data-tippy-content="View Timeline"
                                            onclick="showOrderTimelineModal('<?= $order['id'] ?>', '<?= $order['token'] ?>')">
                                            <i class="fas fa-history"></i>
                                        </button>
                                    <?php endif; ?>

                                    <button
                                        class="action-btn text-gray-600 hover:text-gray-800"
                                        data-tippy-content="View Details"
                                        onclick="toggleDetails('details-<?= $index ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="7" class="px-6 py-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Total Bill: </span>
                                        <span class="text-sm font-bold text-primary">UGX <?= formatCurrency($order['total']) ?></span>
                                    </div>
                                    <button class="text-primary hover:text-primary/80 text-sm flex items-center gap-1 toggle-details" data-target="details-<?= $index ?>">
                                        <span>View Details</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div id="details-<?= $index ?>" class="mt-3 hidden details-section">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-500">Subtotal:</p>
                                            <p class="font-medium">UGX <?= formatCurrency($order['subtotal']) ?></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Delivery Cost:</p>
                                            <p class="font-medium">UGX <?= formatCurrency($order['delivery_cost']) ?> <span class="text-xs text-red-600">(Far Range)</span></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Distance:</p>
                                            <p class="font-medium"><?= $order['distance'] ?> Km</p>
                                        </div>
                                        <div class="md:col-span-3">
                                            <p class="text-gray-500">Site Details:</p>
                                            <p class="font-medium"><?= htmlspecialchars($order['site_details']) ?></p>
                                            <p class="text-sm">On-site Contacts: (<?= $order['contacts'] ?>)</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Order Category:</p>
                                            <p class="font-medium"><?= htmlspecialchars($order['order_category']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="responsive-table-mobile p-4 hidden md:hidden">
            <?php foreach ($orderList as $index => $order): ?>
                <div class="mobile-card mb-4">
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="https://placehold.co/80x80" alt="<?= htmlspecialchars($order['product_name']) ?>" class="w-16 h-16 object-cover rounded-md">
                            <div>
                                <p class="font-medium text-gray-900"><?= htmlspecialchars($order['product_name']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">Token: <?= htmlspecialchars($order['token']) ?></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Price:</p>
                                <p class="text-sm font-medium">UGX <?= formatCurrency($order['price']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Quantity:</p>
                                <p class="text-sm font-medium"><?= $order['quantity'] ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Subtotal:</p>
                                <p class="text-sm font-medium">UGX <?= formatCurrency($order['subtotal']) ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Bill:</p>
                                <p class="text-sm font-medium text-primary">UGX <?= formatCurrency($order['total']) ?></p>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusBadgeClass($order['status']) ?>">
                                    <span class="w-1.5 h-1.5 rounded-full <?= getStatusDotClass($order['status']) ?> mr-1"></span>
                                    <?= getStatusText($order['status'], $order['payment_method']) ?>
                                </span>
                            </div>
                            <button class="text-primary hover:text-primary/80 text-sm flex items-center gap-1 toggle-mobile-details" data-target="mobile-details-<?= $index ?>">
                                <span>Details</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    <div id="mobile-details-<?= $index ?>" class="p-4 bg-gray-50 hidden details-section">
                        <div class="mb-3">
                            <p class="text-xs text-gray-500">Ordered by:</p>
                            <p class="text-sm font-medium"><?= htmlspecialchars($order['customer_name']) ?>, <span class="text-xs text-gray-500"><?= formatDate($order['order_date']) ?></span></p>
                        </div>
                        <div class="mb-3">
                            <p class="text-xs text-gray-500">Site Details:</p>
                            <p class="text-sm font-medium"><?= htmlspecialchars($order['site_details']) ?></p>
                            <p class="text-xs text-gray-500">On-site Contacts: (<?= $order['contacts'] ?>)</p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Delivery Cost:</p>
                                <p class="text-sm font-medium">UGX <?= formatCurrency($order['delivery_cost']) ?> <span class="text-xs text-red-600">(Far Range)</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Distance:</p>
                                <p class="text-sm font-medium"><?= $order['distance'] ?> Km</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <?php if (in_array('confirm', $order['actions'])): ?>
                                <button
                                    class="px-3 py-2 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700"
                                    onclick="showConfirmPaymentModal('<?= $order['id'] ?>', '<?= $order['token'] ?>', '<?= htmlspecialchars($order['product_name']) ?>', '<?= formatCurrency($order['total']) ?>', '<?= htmlspecialchars($order['customer_name']) ?>')">
                                    <i class="fas fa-check-circle mr-1"></i> Confirm Payment
                                </button>
                            <?php endif; ?>

                            <?php if (in_array('supplier', $order['actions'])): ?>
                                <button
                                    class="px-3 py-2 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700"
                                    onclick="showGetSupplierModal('<?= $order['id'] ?>', '<?= $order['token'] ?>', '<?= htmlspecialchars($order['product_name']) ?>', '<?= $order['quantity'] ?>', '<?= htmlspecialchars($order['site_details']) ?>')">
                                    <i class="fas fa-truck mr-1"></i> Assign Supplier
                                </button>
                            <?php endif; ?>

                            <?php if (in_array('timeline', $order['actions'])): ?>
                                <button
                                    class="px-3 py-2 bg-purple-600 text-white text-xs rounded-lg hover:bg-purple-700"
                                    onclick="showOrderTimelineModal('<?= $order['id'] ?>', '<?= $order['token'] ?>')">
                                    <i class="fas fa-history mr-1"></i> View Timeline
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-text">
                Showing <span id="showing-start">1</span> to <span id="showing-end"><?= count($orderList) ?></span> of <span id="total-orders"><?= count($orderList) ?></span> orders
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

<!-- Confirm Payment Modal -->
<div id="confirmPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideConfirmPaymentModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Confirm Payment</h3>
            <button onclick="hideConfirmPaymentModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">Are you sure you want to confirm payment for this order?</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500">Order Token:</div>
                    <div class="font-medium text-gray-900" id="confirm-order-token">Z103719</div>
                    <div class="text-gray-500">Product:</div>
                    <div class="font-medium text-gray-900" id="confirm-product-name">Product Name</div>
                    <div class="text-gray-500">Amount:</div>
                    <div class="font-medium text-gray-900" id="confirm-amount">UGX 100,000</div>
                    <div class="text-gray-500">Customer:</div>
                    <div class="font-medium text-gray-900" id="confirm-customer">Customer Name</div>
                </div>
            </div>
            <div class="mb-4">
                <label for="payment-reference" class="block text-sm font-medium text-gray-700 mb-1">Payment Reference</label>
                <input type="text" id="payment-reference" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Enter payment reference">
            </div>
            <div class="mb-4">
                <label for="payment-note" class="block text-sm font-medium text-gray-700 mb-1">Note (Optional)</label>
                <textarea id="payment-note" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Add a note about this payment"></textarea>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideConfirmPaymentModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmPaymentBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Confirm Payment
            </button>
        </div>
    </div>
</div>

<!-- Get Supplier Modal -->
<div id="getSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideGetSupplierModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Assign Supplier</h3>
            <button onclick="hideGetSupplierModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="text-gray-500">Order Token:</div>
                        <div class="font-medium text-gray-900" id="supplier-order-token">Z109246</div>
                        <div class="text-gray-500">Product:</div>
                        <div class="font-medium text-gray-900" id="supplier-product-name">Product Name</div>
                        <div class="text-gray-500">Quantity:</div>
                        <div class="font-medium text-gray-900" id="supplier-quantity">10</div>
                        <div class="text-gray-500">Delivery Location:</div>
                        <div class="font-medium text-gray-900" id="supplier-location">Kampala, Central</div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="supplier-search" class="block text-sm font-medium text-gray-700 mb-1">Search Suppliers</label>
                <div class="relative">
                    <input type="text" id="supplier-search" class="w-full px-3 py-2 pl-10 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Search by name, location, or product category">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Available Suppliers</label>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <div class="max-h-60 overflow-y-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr class="text-left border-b border-gray-100">
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-text">Supplier</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-text">Location</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-text">Rating</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-text">Price</th>
                                    <th class="px-4 py-2 text-xs font-semibold text-gray-text"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($supplierList as $supplier): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm">
                                            <div class="font-medium"><?= htmlspecialchars($supplier['name']) ?></div>
                                            <div class="text-xs text-gray-500"><?= $supplier['verified'] ? 'Verified Supplier' : 'Supplier' ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($supplier['location']) ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex items-center">
                                                <?php
                                                $fullStars = floor($supplier['rating']);
                                                $halfStar = ($supplier['rating'] - $fullStars) >= 0.5;
                                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

                                                for ($i = 0; $i < $fullStars; $i++) {
                                                    echo '<i class="fas fa-star text-yellow-400"></i>';
                                                }

                                                if ($halfStar) {
                                                    echo '<i class="fas fa-star-half-alt text-yellow-400"></i>';
                                                }

                                                for ($i = 0; $i < $emptyStars; $i++) {
                                                    echo '<i class="far fa-star text-yellow-400"></i>';
                                                }
                                                ?>
                                                <span class="ml-1"><?= $supplier['rating'] ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium">UGX <?= formatCurrency($supplier['price']) ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <button class="px-3 py-1 bg-primary text-white text-xs rounded-lg hover:bg-primary/90 select-supplier-btn" data-id="<?= $supplier['id'] ?>">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideGetSupplierModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="assignSupplierBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90" disabled>
                Assign Supplier
            </button>
        </div>
    </div>
</div>

<!-- Order Timeline Modal -->
<div id="orderTimelineModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideOrderTimelineModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Order Timeline</h3>
            <button onclick="hideOrderTimelineModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div style="width:100%;margin: 0 auto;">
                <div class="text-lg font-semibold mb-4">Order Review Process</div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="text-sm text-gray-500">Approval Date</div>
                    <div class="text-sm font-medium" id="timeline-approval-date"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="text-sm text-gray-500">Approval ID</div>
                    <div class="text-sm font-medium" id="timeline-approval-id"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="text-sm text-gray-500">Paid Via</div>
                    <div class="text-sm font-medium" id="timeline-paid-via"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="text-sm text-gray-500">Account Details</div>
                    <div class="text-sm font-medium" id="timeline-account-details"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div></div>
                    <div>
                        <button id="viewReceiptBtn" class="px-3 py-1 border border-red-600 text-red-600 text-xs rounded-lg hover:bg-red-50">
                            View Receipt
                        </button>
                    </div>
                </div>
                <div class="text-sm font-medium mb-2">Assigned To</div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <div class="text-sm" id="timeline-assigned-to"></div>
                    <div class="text-sm" id="timeline-assigned-date"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receiptModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideReceiptModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary">Receipt</h3>
            <button onclick="hideReceiptModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="container invoice">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center">
                        <img src="img_data/favicon.png" alt="Zzimba Online" width="45" height="45" class="mr-2">
                        <h2 class="text-xl font-bold text-gray-900">Zzimba Online!</h2>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">RECEIPT</p>
                    </div>
                </div>
                <hr class="my-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Billed To:</p>
                        <p class="text-sm font-bold" id="receipt-customer"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Site Address:</p>
                        <p class="text-sm" id="receipt-site-address"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Contact:</p>
                        <p class="text-sm" id="receipt-contact"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Date of Transaction:</p>
                        <p class="text-sm" id="receipt-transaction-date"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Date Printed:</p>
                        <p class="text-sm" id="receipt-print-date"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Receipt No:</p>
                        <p class="text-sm" id="receipt-no"></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Payment ID:</p>
                        <p class="text-sm" id="receipt-payment-id"></p>
                    </div>
                </div>
                <hr class="my-4">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">Summary</h3>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-200 px-4 py-2 text-left text-sm">No.</th>
                                <th class="border border-gray-200 px-4 py-2 text-left text-sm">Particular(s)</th>
                                <th class="border border-gray-200 px-4 py-2 text-right text-sm">AMOUNT (UGX)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2 text-sm">01</td>
                                <td class="border border-gray-200 px-4 py-2 text-sm" id="receipt-particulars"></td>
                                <td class="border border-gray-200 px-4 py-2 text-right text-sm" id="receipt-amount"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-gray-200 px-4 py-2 text-right text-sm">Paid</td>
                                <td class="border border-gray-200 px-4 py-2 text-right text-sm" id="receipt-paid"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-gray-200 px-4 py-2 text-right text-sm">Balance</td>
                                <td class="border border-gray-200 px-4 py-2 text-right text-sm">0</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="border border-gray-200 px-4 py-2 text-right text-sm">Paid By</td>
                                <td class="border border-gray-200 px-4 py-2 text-right text-sm" id="receipt-paid-by"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center text-xs text-gray-500 mt-6">
                    A Product of the Engineering Marksmen Ltd, P.O Box 129572 Kampala - Uganda, +256-392-003406, <a href="mailto:accounts@zzimbaonline.com" class="text-blue-600">accounts@zzimbaonline.com</a>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button id="downloadPdf" class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50">
                    <i class="fas fa-download mr-1"></i> Download
                </button>
                <button id="printPdf" class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
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
    }

    .toggle-details i,
    .toggle-mobile-details i {
        transition: transform 0.2s ease;
    }

    .toggle-details.active i,
    .toggle-mobile-details.active i {
        transform: rotate(180deg);
    }

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

    .invoice {
        background: #ffffff;
    }
</style>

<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        tippy('[data-tippy-content]', {
            placement: 'top',
            arrow: true,
            theme: 'light',
        });

        // Toggle filter panel
        document.getElementById('filterOrders').addEventListener('click', function() {
            const filterPanel = document.getElementById('filterPanel');
            filterPanel.classList.toggle('hidden');
        });

        // Toggle details sections - only one open at a time
        function setupToggleButtons(selector) {
            document.querySelectorAll(selector).forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const targetElement = document.getElementById(targetId);

                    // Close all other details sections first
                    document.querySelectorAll('.details-section').forEach(section => {
                        if (section.id !== targetId && !section.classList.contains('hidden')) {
                            section.classList.add('hidden');
                            document.querySelector(`[data-target="${section.id}"]`).classList.remove('active');
                        }
                    });

                    if (targetElement.classList.contains('hidden')) {
                        targetElement.classList.remove('hidden');
                        this.classList.add('active');
                    } else {
                        targetElement.classList.add('hidden');
                        this.classList.remove('active');
                    }
                });
            });
        }

        setupToggleButtons('.toggle-details');
        setupToggleButtons('.toggle-mobile-details');

        // Reset filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterOrderStatus').value = '';
            document.getElementById('filterPaymentMethod').value = '';
            document.getElementById('filterDateRange').value = 'all';
        });

        // Apply filters
        document.getElementById('applyFilters').addEventListener('click', function() {
            // In a real application, this would filter the orders based on the selected criteria
            alert('Filters applied');
            document.getElementById('filterPanel').classList.add('hidden');
        });

        // Search functionality
        document.getElementById('searchOrders').addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            // In a real application, this would filter the orders based on the search query
            console.log('Searching for: ' + query);
        });

        // Sort orders
        document.getElementById('sortOrders').addEventListener('change', function() {
            const sortValue = this.value;
            // In a real application, this would sort the orders based on the selected option
            console.log('Sorting by: ' + sortValue);
        });

        // Export orders
        document.getElementById('exportOrders').addEventListener('click', function() {
            alert('Export functionality would be implemented here');
        });

        // Supplier selection
        document.querySelectorAll('.select-supplier-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove selected class from all rows
                document.querySelectorAll('.select-supplier-btn').forEach(btn => {
                    btn.closest('tr').classList.remove('bg-blue-50');
                });

                // Add selected class to this row
                this.closest('tr').classList.add('bg-blue-50');

                // Enable the assign button
                document.getElementById('assignSupplierBtn').disabled = false;
            });
        });

        // Assign supplier button
        document.getElementById('assignSupplierBtn').addEventListener('click', function() {
            alert('Supplier assigned successfully!');
            hideGetSupplierModal();
        });

        // Confirm payment button
        document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
            const reference = document.getElementById('payment-reference').value;
            if (!reference) {
                alert('Please enter a payment reference');
                return;
            }

            alert('Payment confirmed successfully!');
            hideConfirmPaymentModal();
        });

        // View Receipt button
        document.getElementById('viewReceiptBtn').addEventListener('click', function() {
            hideOrderTimelineModal();
            showReceiptModal();
        });

        // Download PDF button
        document.getElementById('downloadPdf').addEventListener('click', function() {
            const element = document.querySelector('.invoice');
            const receiptNo = document.getElementById('receipt-no').textContent.trim();

            // Options for html2pdf
            const options = {
                margin: 0,
                filename: `receipt-${receiptNo}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            // Use html2pdf to convert the element
            html2pdf().from(element).set(options).save();
        });

        // Print PDF button
        document.getElementById('printPdf').addEventListener('click', function() {
            let printWindow = window.open('', 'PRINT', 'height=700,width=500,top=100,left=150');
            const receiptNo = document.getElementById('receipt-no').textContent.trim();

            const invoiceContent = document.querySelector('.invoice').innerHTML;

            // Build the HTML for the print window
            printWindow.document.write('<html><head><title>receipt-' + receiptNo + '</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">');
            printWindow.document.write('<style>');
            printWindow.document.write('.invoice { max-width: 600px; margin: 20px auto; padding: 20px; background: #ffffff; }');
            printWindow.document.write('table, th, td { border: 1px solid #ddd; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; }');
            printWindow.document.write('th, td { text-align: left; padding: 8px; }');
            printWindow.document.write('th { background-color: #f2f2f2; }');
            printWindow.document.write('td.text-right { text-right: right; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(invoiceContent);
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.focus();

            printWindow.onload = function() {
                printWindow.print();
            };
        });
    });

    // Function to toggle details
    function toggleDetails(targetId) {
        const targetElement = document.getElementById(targetId);
        const button = document.querySelector(`[data-target="${targetId}"]`);

        // Close all other details sections first
        document.querySelectorAll('.details-section').forEach(section => {
            if (section.id !== targetId && !section.classList.contains('hidden')) {
                section.classList.add('hidden');
                document.querySelector(`[data-target="${section.id}"]`).classList.remove('active');
            }
        });

        if (targetElement.classList.contains('hidden')) {
            targetElement.classList.remove('hidden');
            button.classList.add('active');
        } else {
            targetElement.classList.add('hidden');
            button.classList.remove('active');
        }
    }

    // Modal functions
    function showConfirmPaymentModal(orderId, token, productName, amount, customer) {
        document.getElementById('confirm-order-token').textContent = token;
        document.getElementById('confirm-product-name').textContent = productName;
        document.getElementById('confirm-amount').textContent = 'UGX ' + amount;
        document.getElementById('confirm-customer').textContent = customer;

        // Store order ID for submission
        document.getElementById('confirmPaymentBtn').setAttribute('data-order-id', orderId);

        // Show modal
        document.getElementById('confirmPaymentModal').classList.remove('hidden');
    }

    function hideConfirmPaymentModal() {
        document.getElementById('confirmPaymentModal').classList.add('hidden');
        document.getElementById('payment-reference').value = '';
        document.getElementById('payment-note').value = '';
    }

    function showGetSupplierModal(orderId, token, productName, quantity, location) {
        document.getElementById('supplier-order-token').textContent = token;
        document.getElementById('supplier-product-name').textContent = productName;
        document.getElementById('supplier-quantity').textContent = quantity;
        document.getElementById('supplier-location').textContent = location;

        // Store order ID for submission
        document.getElementById('assignSupplierBtn').setAttribute('data-order-id', orderId);

        // Reset supplier selection
        document.querySelectorAll('.select-supplier-btn').forEach(btn => {
            btn.closest('tr').classList.remove('bg-blue-50');
        });
        document.getElementById('assignSupplierBtn').disabled = true;

        // Show modal
        document.getElementById('getSupplierModal').classList.remove('hidden');
    }

    function hideGetSupplierModal() {
        document.getElementById('getSupplierModal').classList.add('hidden');
    }

    function showOrderTimelineModal(orderId, token) {
        // In a real application, you would fetch the timeline data from the server
        // For this example, we'll use the sample data

        // Find the order with the matching ID
        const orderData = <?= json_encode($orderList) ?>;
        const order = orderData.find(o => o.id === orderId);

        if (order && order.timeline) {
            document.getElementById('timeline-approval-date').textContent = order.timeline.approval_date;
            document.getElementById('timeline-approval-id').textContent = order.timeline.approval_id;
            document.getElementById('timeline-paid-via').textContent = order.timeline.paid_via;
            document.getElementById('timeline-account-details').innerHTML = order.timeline.account_details;
            document.getElementById('timeline-assigned-to').textContent = order.timeline.assigned_to;
            document.getElementById('timeline-assigned-date').textContent = '- Assigned <br>(' + order.timeline.assigned_date + ')';

            // Store order data for receipt
            document.getElementById('viewReceiptBtn').setAttribute('data-order-id', orderId);
            document.getElementById('viewReceiptBtn').setAttribute('data-token', token);
        }

        // Show modal
        document.getElementById('orderTimelineModal').classList.remove('hidden');
    }

    function hideOrderTimelineModal() {
        document.getElementById('orderTimelineModal').classList.add('hidden');
    }

    function showReceiptModal() {
        // Get order data from the view receipt button
        const orderId = document.getElementById('viewReceiptBtn').getAttribute('data-order-id');
        const token = document.getElementById('viewReceiptBtn').getAttribute('data-token');

        // Find the order with the matching ID
        const orderData = <?= json_encode($orderList) ?>;
        const order = orderData.find(o => o.id === orderId);

        if (order) {
            // Format the date for receipt
            const transactionDate = new Date(order.order_date);
            const formattedTransactionDate = transactionDate.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            // Format today's date for receipt
            const today = new Date();
            const formattedPrintDate = today.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });

            // Populate receipt data
            document.getElementById('receipt-customer').textContent = order.customer_name;
            document.getElementById('receipt-site-address').textContent = order.site_details;
            document.getElementById('receipt-contact').textContent = '+' + order.contacts;
            document.getElementById('receipt-transaction-date').textContent = formattedTransactionDate;
            document.getElementById('receipt-print-date').textContent = formattedPrintDate;
            document.getElementById('receipt-no').textContent = order.timeline ? order.timeline.approval_id : '';
            document.getElementById('receipt-payment-id').textContent = token;
            document.getElementById('receipt-particulars').textContent = 'Payment for Delivery of ' + order.quantity + ' ' + (order.quantity > 1 ? 'Pieces' : 'Piece') + ' of ' + order.product_name;
            document.getElementById('receipt-amount').textContent = formatCurrency(order.total);
            document.getElementById('receipt-paid').textContent = formatCurrency(order.total);
            document.getElementById('receipt-paid-by').textContent = order.payment_method;
        }

        // Show modal
        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function hideReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG').format(amount);
    }
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>