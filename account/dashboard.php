<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$stmt = $pdo->prepare("SELECT last_login FROM zzimba_users WHERE id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user']['user_id'], PDO::PARAM_LOB);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$lastLogin = $result['last_login'] ?? '';
$formattedLastLogin = $lastLogin ? date('M d, Y g:i A', strtotime($lastLogin)) : 'First login';

$categories = [
    [
        'id' => 134777,
        'name' => 'Tiles & Accessories',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 41
    ],
    [
        'id' => 845520,
        'name' => 'Hardware Materials',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 35
    ],
    [
        'id' => 9014,
        'name' => 'Paints & Binders',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 26
    ],
    [
        'id' => 4660,
        'name' => 'Earth Materials',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 17
    ],
    [
        'id' => 537406,
        'name' => 'Roofing Materials',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 15
    ],
    [
        'id' => 5707,
        'name' => 'Electrical Supplies',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 14
    ],
    [
        'id' => 7768,
        'name' => 'Plumbing Fittings',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 10
    ],
    [
        'id' => 6294,
        'name' => 'Building Glass Materials',
        'image' => 'https://placehold.co/100x100/f0f0f0/808080?text=100x100',
        'products' => 10
    ]
];

ob_start();
?>

<div class="space-y-6">
    <div class="content-section">
        <div class="content-header p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-secondary">Welcome Back!</h1>
                    <p class="text-sm text-gray-text mt-2">
                        Last Login: <span class="font-medium text-user-primary"><?= $formattedLastLogin ?></span>
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="assignMembership" class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2 justify-center">
                        <i class="fas fa-id-card"></i>
                        <span>Assign Membership</span>
                    </button>
                    <button id="shareLink" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 justify-center">
                        <i class="fas fa-share-alt"></i>
                        <span>Share Link</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="content-header p-6">
            <h2 class="text-xl font-semibold text-secondary">Account Set-Up</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="order-history" class="user-card hover:shadow-md transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <i class="fas fa-history text-user-primary text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">Order History</h3>
                        <p class="text-sm text-gray-text">View your past orders and track current ones</p>
                    </div>
                </a>

                <a href="zzimba-credit" class="user-card hover:shadow-md transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                            <i class="fas fa-credit-card text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">Zzimba Credit</h3>
                        <p class="text-sm text-gray-text">Manage your Zzimba credit balance and transactions</p>
                    </div>
                </a>

                <a href="zzimba-store" class="user-card hover:shadow-md transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <i class="fas fa-store text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">My Zzimba Store</h3>
                        <p class="text-sm text-gray-text">Manage your store profile and products</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center">
                <i class="fas fa-truck-loading text-user-primary text-xl mr-3"></i>
                <h2 class="text-lg font-semibold text-secondary">Cash-in Delivery</h2>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <button id="sendToken" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 justify-center w-full sm:w-auto">
                    <i class="fas fa-coins"></i>
                    <span>Send Token</span>
                </button>
                <a href="order-desk" class="h-10 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors flex items-center gap-2 justify-center w-full sm:w-auto">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Order Desk</span>
                </a>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="p-6">
            <a href="#" target="_blank" class="block">
                <img src="https://placehold.co/1200x200/f0f0f0/808080?text=1200x200" alt="Advertisement" class="w-full h-auto rounded-lg">
            </a>
        </div>
    </div>

    <div class="content-section">
        <div class="content-header p-6">
            <h2 class="text-xl font-semibold text-secondary">Browse By Category</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($categories as $category): ?>
                    <a href="category-<?= $category['id'] ?>" class="flex items-center gap-4 p-4 rounded-lg border border-gray-100 hover:border-user-primary hover:shadow-sm transition-all duration-200 bg-white">
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="<?= $category['image'] ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="font-medium text-secondary"><?= htmlspecialchars($category['name']) ?></h3>
                            <p class="text-sm text-gray-text mt-1"><?= $category['products'] ?> Products</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div id="membershipModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('membershipModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Pay Membership Subscription</h3>
                <button onclick="hideModal('membershipModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-4 text-center">
                <p class="text-sm text-gray-text">
                    Subscription Payment Plan Per Store Location is Only <strong>UGX 30,000.</strong> Every 3 Months.
                    Include UGX 2,000 Transaction Charges When Not Using Your <a href="zzimba-credit" class="text-user-primary font-medium">Zzimba Credit</a> Account
                </p>
            </div>
            <form id="subscriptionForm">
                <div class="space-y-4">
                    <div>
                        <input type="text" id="getVendor" placeholder="Search Vendor..." class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <select id="district" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select District</option>
                            <option value="4765">Jinja</option>
                            <option value="0001">Kampala</option>
                            <option value="5577">Manafwa</option>
                            <option value="7862">Mbale</option>
                            <option value="2604">Mukono</option>
                            <option value="1732">Tororo</option>
                            <option value="7672">Wakiso</option>
                        </select>
                    </div>
                    <div>
                        <select id="town" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select Town</option>
                        </select>
                    </div>
                    <div>
                        <select id="location" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                            <option value="">Select Nearby Location</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" id="address" placeholder="Location Address" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary">
                    </div>
                    <div>
                        <h4 class="text-center font-medium text-secondary mb-3">Preferred Mode of Payment</h4>
                        <div class="flex justify-center gap-4">
                            <div class="payment-option" data-method="credit">
                                <div class="w-16 h-16 rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary transition-colors">
                                    <i class="fas fa-credit-card text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Zzimba Credit</p>
                            </div>
                            <div class="payment-option" data-method="bank">
                                <div class="w-16 h-16 rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary transition-colors">
                                    <i class="fas fa-university text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Bank</p>
                            </div>
                            <div class="payment-option" data-method="mobile">
                                <div class="w-16 h-16 rounded-lg bg-gray-50 flex items-center justify-center mb-2 cursor-pointer hover:bg-user-secondary transition-colors">
                                    <i class="fas fa-mobile-alt text-2xl text-user-primary"></i>
                                </div>
                                <p class="text-xs text-center">Mobile Money</p>
                            </div>
                        </div>
                    </div>
                    <div id="paymentDetails" class="hidden"></div>
                    <div class="text-center text-xs text-gray-text">
                        Account is Activated Upon Payment Confirmation
                    </div>
                    <button type="submit" class="w-full h-10 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors">
                        SUBMIT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="tokenModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideModal('tokenModal')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-secondary">Enter Token No</h3>
                <button onclick="hideModal('tokenModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="tokenMessage" class="mb-4 text-center"></div>
            <form id="tokenForm">
                <div class="mb-4">
                    <input type="text" id="token" placeholder="Token No" class="w-full h-12 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg">
                </div>
                <button type="submit" class="w-full h-12 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors text-lg">
                    Redeem Payment
                </button>
            </form>
        </div>
    </div>
</div>

<div id="loadingOverlay" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-user-primary border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-gray-700">Processing...</p>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#assignMembership').click(function() {
            showModal('membershipModal');
        });

        $('#sendToken').click(function() {
            showModal('tokenModal');
        });

        $('.payment-option').click(function() {
            $('.payment-option').removeClass('active');
            $(this).addClass('active');

            const method = $(this).data('method');
            let detailsHtml = '';

            if (method === 'credit') {
                detailsHtml = `
                    <div class="p-4 bg-user-secondary rounded-lg">
                        <p class="text-sm">Your Zzimba Credit Balance: <strong>UGX 45,000</strong></p>
                    </div>
                `;
            } else if (method === 'bank') {
                detailsHtml = `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm mb-2">Bank: <strong>Stanbic Bank</strong></p>
                        <p class="text-sm mb-2">Account Name: <strong>Zzimba Online Ltd</strong></p>
                        <p class="text-sm">Account Number: <strong>9030012345678</strong></p>
                    </div>
                `;
            } else if (method === 'mobile') {
                detailsHtml = `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm mb-2">Mobile Money Number: <strong>0772 123456</strong></p>
                        <p class="text-sm">Name: <strong>Zzimba Online Ltd</strong></p>
                    </div>
                `;
            }

            $('#paymentDetails').html(detailsHtml).removeClass('hidden');
        });

        $('#subscriptionForm').submit(function(e) {
            e.preventDefault();
            showLoading();

            setTimeout(function() {
                hideLoading();
                hideModal('membershipModal');
                showSuccessNotification('Subscription payment submitted successfully!');
            }, 1500);
        });

        $('#tokenForm').submit(function(e) {
            e.preventDefault();
            showLoading();

            setTimeout(function() {
                hideLoading();
                hideModal('tokenModal');
                showSuccessNotification('Token redeemed successfully!');
            }, 1500);
        });

        $('#district').change(function() {
            const districtId = $(this).val();
            if (!districtId) return;

            $('#town').html('<option value="">Select Town</option>');

            if (districtId === '0001') {
                $('#town').append('<option value="1">Kampala Central</option>');
                $('#town').append('<option value="2">Nakawa</option>');
                $('#town').append('<option value="3">Kawempe</option>');
            } else if (districtId === '7672') {
                $('#town').append('<option value="4">Entebbe</option>');
                $('#town').append('<option value="5">Nansana</option>');
            }
        });

        $('#town').change(function() {
            const townId = $(this).val();
            if (!townId) return;

            $('#location').html('<option value="">Select Nearby Location</option>');

            if (townId === '1') {
                $('#location').append('<option value="1">Nakasero</option>');
                $('#location').append('<option value="2">Kololo</option>');
            } else if (townId === '2') {
                $('#location').append('<option value="3">Bugolobi</option>');
                $('#location').append('<option value="4">Luzira</option>');
            }
        });
    });

    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function showSuccessNotification(message) {
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

        document.getElementById('successMessage').textContent = message;
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