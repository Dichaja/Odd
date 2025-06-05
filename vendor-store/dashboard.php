<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$storeId = $_SESSION['active_store'] ?? null;
if (!$storeId) {
    header('Location: ' . BASE_URL . 'account/dashboard');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM buy_in_store_requests bisr
        JOIN product_pricing pp ON bisr.pricing_id = pp.id
        JOIN store_products sp  ON pp.store_products_id = sp.id
        JOIN store_categories sc ON sp.store_category_id = sc.id
        WHERE sc.store_id = :sid 
          AND bisr.status = 'pending'
    ");
    $stmt->execute([':sid' => $storeId]);
    $pendingRequests = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM product_pricing pp
        JOIN store_products sp  ON pp.store_products_id = sp.id
        JOIN store_categories sc ON sp.store_category_id = sc.id
        WHERE sc.store_id = :sid
          AND sc.status = 'active'
          AND sp.status = 'active'
    ");
    $stmt->execute([':sid' => $storeId]);
    $totalProducts = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT sc.id)
        FROM store_categories sc
        JOIN store_products sp  ON sc.id = sp.store_category_id
        JOIN product_pricing pp ON pp.store_products_id = sp.id
        WHERE sc.store_id = :sid
          AND sc.status = 'active'
          AND sp.status = 'active'
    ");
    $stmt->execute([':sid' => $storeId]);
    $totalCategories = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM store_managers
        WHERE store_id = :sid 
          AND status = 'active' 
          AND approved = 1
    ");
    $stmt->execute([':sid' => $storeId]);
    $totalManagers = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $pendingRequests = $totalProducts = $totalCategories = $totalManagers = 0;
}

$totalOrders = 128;
$storeCredit = 150000;

ob_start();
?>

<div class="space-y-6">

    <div class="content-section">
        <div class="content-header px-6 py-4 sm:py-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-secondary">Store Overview</h2>
            <a href="<?= BASE_URL ?>view/profile/vendor/<?= $storeId ?>" target="_blank"
                class="h-9 px-4 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition flex items-center gap-2 text-sm">
                <i class="fas fa-external-link-alt"></i>
                <span class="hidden sm:inline">View Profile</span>
            </a>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <a href="<?= BASE_URL ?>vendor-store/orders"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-bag text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= $totalOrders ?></h3>
                    <p class="text-sm text-gray-text">Total Orders</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>vendor-store/requests"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-yellow-100 flex items-center justify-center mb-4">
                        <i class="fas fa-calendar-check text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= $pendingRequests ?></h3>
                    <p class="text-sm text-gray-text">Pending Requests</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>vendor-store/products"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <i class="fas fa-box-open text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= $totalProducts ?></h3>
                    <p class="text-sm text-gray-text">Products</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>vendor-store/categories"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mb-4">
                        <i class="fas fa-tags text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= $totalCategories ?></h3>
                    <p class="text-sm text-gray-text">Categories</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>vendor-store/managers"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= $totalManagers ?></h3>
                    <p class="text-sm text-gray-text">Store Managers</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>vendor-store/zzimba-credit"
                class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="p-6 flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mb-4">
                        <i class="fas fa-credit-card text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-secondary mb-1"><?= number_format($storeCredit) ?> UGX</h3>
                    <p class="text-sm text-gray-text">Zzimba Credit</p>
                </div>
            </a>

        </div>
    </div>

    <!-- Cash-in Delivery Section -->
    <div class="content-section">
        <div class="p-6 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center">
                <i class="fas fa-truck-loading text-user-primary text-xl mr-3"></i>
                <h2 class="text-lg font-semibold text-secondary">Cash-in Delivery</h2>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <button id="sendToken"
                    class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 justify-center w-full sm:w-auto">
                    <i class="fas fa-coins"></i>
                    <span>Send Token</span>
                </button>
            </div>
        </div>
    </div>

    <div class="content-section">
        <div class="p-6">
            <a href="#" target="_blank" class="block">
                <img src="https://placehold.co/1200x200/f0f0f0/808080?text=Vendor+Banner+Here" alt="Vendor Banner"
                    class="w-full h-auto rounded-lg">
            </a>
        </div>
    </div>

    <!-- Token Modal -->
    <div id="tokenModal" class="fixed inset-0 z-50 hidden !m-0">
        <div class="absolute inset-0 bg-black/20" onclick="hideModal('tokenModal')"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md bg-white rounded-lg shadow-lg">
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
                        <input type="text" id="token" placeholder="Token No"
                            class="w-full h-12 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-user-primary focus:ring-1 focus:ring-user-primary text-center text-lg">
                    </div>
                    <button type="submit"
                        class="w-full h-12 bg-user-primary text-white rounded-lg hover:bg-user-primary/90 transition-colors text-lg">
                        Redeem Payment
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function () {

        // Token modal
        $('#sendToken').click(function () {
            showModal('tokenModal');
        });

        $('#tokenForm').submit(function (e) {
            e.preventDefault();
            showLoading();

            // Simulate form submission
            setTimeout(function () {
                hideLoading();
                hideModal('tokenModal');
                showSuccessNotification('Token redeemed successfully!');
            }, 1500);
        });
    });

    // Show modal
    function showModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    // Hide modal
    function hideModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>