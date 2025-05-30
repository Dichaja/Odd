<?php
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

// 1. Fetch last login for current user
$stmt = $pdo->prepare("
    SELECT last_login
      FROM zzimba_users
     WHERE id = :user_id
");
$stmt->bindParam(':user_id', $_SESSION['user']['user_id'], PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$lastLogin = $result['last_login'] ?? '';
$formattedLastLogin = $lastLogin
    ? date('M d, Y g:i A', strtotime($lastLogin))
    : 'First login';

// 2. Fetch only active categories that have at least one published product
$stmt = $pdo->prepare("
    SELECT
      c.id,
      c.name,
      p.count AS products
    FROM product_categories c
    INNER JOIN (
        SELECT
          category_id,
          COUNT(*) AS count
        FROM products
       WHERE status = 'published'
       GROUP BY category_id
    ) p
      ON c.id = p.category_id
    WHERE c.status = 'active'
    ORDER BY c.name
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Helper to pick a category image or fallback to placeholder
function getCategoryImageUrl(string $categoryId): string
{
    $dir = __DIR__ . '/../img/product-categories/' . $categoryId . '/';
    $webBase = BASE_URL . 'img/product-categories/' . $categoryId . '/';

    if (is_dir($dir)) {
        $files = glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (!empty($files)) {
            return $webBase . basename($files[0]);
        }
    }

    return 'https://placehold.co/100x100/f0f0f0/808080?text=100x100';
}

// 4. Render page content
ob_start();
?>

<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="content-section">
        <div class="content-header px-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-semibold text-secondary">
                        Welcome Back, <?= htmlspecialchars($_SESSION['user']['username']) ?>!
                    </h1>
                    <p class="text-sm text-gray-text mt-2">
                        Last Login:
                        <span class="font-medium text-user-primary"><?= htmlspecialchars($formattedLastLogin) ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Set-Up Cards -->
    <div class="content-section">
        <div class="content-header px-6">
            <h2 class="text-xl font-semibold text-secondary">Account Set-Up</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="order-history"
                    class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <i class="fas fa-history text-user-primary text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">Order History</h3>
                        <p class="text-sm text-gray-text">View your past orders and track current ones</p>
                    </div>
                </a>

                <a href="zzimba-credit"
                    class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-4">
                            <i class="fas fa-credit-card text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">Zzimba Credit</h3>
                        <p class="text-sm text-gray-text">Manage your Zzimba credit balance and transactions</p>
                    </div>
                </a>

                <a href="zzimba-stores"
                    class="user-card bg-white rounded-lg border border-gray-100 shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="p-6 flex flex-col items-center text-center">
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                            <i class="fas fa-store text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-secondary mb-2">My Zzimba Stores</h3>
                        <p class="text-sm text-gray-text">Manage and create store profiles and products</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Advertisement Banner -->
    <div class="content-section">
        <div class="">
            <a href="#" target="_blank" class="block">
                <img src="https://placehold.co/1200x200/f0f0f0/808080?text=1200x200" alt="Advertisement"
                    class="w-full h-auto rounded-lg">
            </a>
        </div>
    </div>

    <!-- Browse By Category -->
    <div class="content-section">
        <div class="content-header p-6">
            <h2 class="text-xl font-semibold text-secondary">Browse By Category</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($categories as $cat): ?>
                    <?php $img = getCategoryImageUrl($cat['id']); ?>
                    <a href="<?= BASE_URL ?>view/category/<?= htmlspecialchars($cat['id']) ?>" target="_blank"
                        class="flex items-center gap-4 p-4 rounded-lg border border-gray-100 hover:border-user-primary hover:shadow-sm transition-all duration-200 bg-white">
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"
                                class="w-full h-full object-cover">
                        </div>
                        <div>
                            <h3 class="font-medium text-secondary"><?= htmlspecialchars($cat['name']) ?></h3>
                            <p class="text-sm text-gray-text mt-1"><?= (int) $cat['products'] ?> Products</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
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
    $(document).ready(function () {
        $('#sendToken').click(function () {
            showModal('tokenModal');
        });

        $('.payment-option').click(function () {
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
    });

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