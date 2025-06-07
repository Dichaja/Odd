<?php
require_once __DIR__ . '/config/config.php';

function getProductImage($productId)
{
    $productDir = __DIR__ . '/img/products/' . $productId . '/';
    $jsonFile = $productDir . 'images.json';

    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $imageData = json_decode($jsonContent, true);

        if (isset($imageData['images']) && !empty($imageData['images'])) {
            $firstImage = $imageData['images'][0];
            $imagePath = $productDir . $firstImage;

            if (file_exists($imagePath)) {
                return BASE_URL . 'img/products/' . $productId . '/' . $firstImage;
            }
        }
    }

    return null;
}

function getCategoryImage($categoryId)
{
    $categoryDir = __DIR__ . '/img/product-categories/' . $categoryId . '/';

    if (is_dir($categoryDir)) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = scandir($categoryDir);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowedExtensions)) {
                    return BASE_URL . 'img/product-categories/' . $categoryId . '/' . $file;
                }
            }
        }
    }

    return null;
}

$categoryId = isset($_GET['categoryId']) ? $_GET['categoryId'] : '';
$pageTitle = "Building Materials";
$activeNav = "materials";

if (!empty($categoryId)) {
    $stmt = $pdo->prepare("
        SELECT id, name, description, meta_title, meta_description, featured
        FROM product_categories 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if ($category) {
        $pageTitle = $category['name'];
    } else {
        $categoryId = '';
    }
}

$categoryImageUrl = null;
if (!empty($categoryId) && isset($category)) {
    $categoryImageUrl = getCategoryImage($categoryId);
}

if (!$categoryImageUrl) {
    $categoryImageUrl = "https://placehold.co/1920x640/334155/f8fafc?text=" . urlencode(!empty($categoryId) && isset($category) ? $category['name'] : 'Building Materials');
}

$allCategoriesStmt = $pdo->prepare("
    SELECT id, name, description
    FROM product_categories 
    WHERE status = 'active'
    ORDER BY CASE WHEN id = ? THEN 0 ELSE 1 END, name ASC
");
$allCategoriesStmt->execute([$categoryId]);
$allCategories = $allCategoriesStmt->fetchAll();

// Fetch products, ordering priced items first
if (!empty($categoryId)) {
    $productsStmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.title, 
            p.description, 
            p.views, 
            (SELECT image_url 
             FROM product_images 
             WHERE product_id = p.id 
               AND is_primary = 1 
             LIMIT 1) AS primary_image,
            EXISTS(
                SELECT 1 
                FROM store_products sp
                JOIN product_pricing pp ON pp.store_products_id = sp.id
                WHERE sp.product_id = p.id
            ) AS has_pricing
        FROM products p
        WHERE p.category_id = ? 
          AND p.status = 'published'
        ORDER BY has_pricing DESC, p.featured DESC, p.views DESC
    ");
    $productsStmt->execute([$categoryId]);
} else {
    $productsStmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.title, 
            p.description, 
            p.views, 
            (SELECT image_url 
             FROM product_images 
             WHERE product_id = p.id 
               AND is_primary = 1 
             LIMIT 1) AS primary_image,
            EXISTS(
                SELECT 1 
                FROM store_products sp
                JOIN product_pricing pp ON pp.store_products_id = sp.id
                WHERE sp.product_id = p.id
            ) AS has_pricing
        FROM products p
        WHERE p.status = 'published'
        ORDER BY has_pricing DESC, p.featured DESC, p.views DESC
    ");
    $productsStmt->execute();
}

$products = [];
while ($row = $productsStmt->fetch()) {
    $productImageUrl = getProductImage($row['id']);

    if ($productImageUrl) {
        $row['primary_image'] = $productImageUrl;
    } elseif (empty($row['primary_image'])) {
        $row['primary_image'] = "https://placehold.co/600x400/e2e8f0/1e293b?text=" . urlencode($row['title']);
    }

    $row['has_pricing'] = (bool) $row['has_pricing'];
    $products[] = $row;
}

ob_start();
?>

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 2;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
        overflow: hidden;
        text-overflow: ellipsis;
        line-clamp: 3;
    }

    .share-container {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .share-label {
        font-size: 12px;
        font-weight: 500;
        color: #4B5563;
    }

    .share-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 9999px;
        color: #DC2626;
        border: 1.5px solid #DC2626;
        background-color: transparent;
        transition: all 0.2s ease;
        position: relative;
    }

    .share-button .fa-solid,
    .share-button .fa-brands {
        font-size: 10px !important;
    }

    .share-button:hover {
        background-color: rgba(220, 38, 38, 0.1);
        transform: translateY(-2px);
    }

    .tooltip {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1F2937;
        color: white;
        padding: 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s, visibility 0.2s;
        z-index: 10;
    }

    .tooltip::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%) rotate(45deg);
        width: 8px;
        height: 8px;
        background-color: #1F2937;
    }

    .share-button:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }

    .product-card {
        position: relative;
        border: 1px solid #E5E7EB;
        border-radius: 0.75rem;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .product-details-btn {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .product-card:hover .product-details-btn {
        opacity: 1;
    }

    .category-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .category-item:hover {
        background-color: #F9FAFB;
    }

    .category-item.active {
        background-color: #FEF2F2;
        color: #DC2626;
    }

    .category-item.active:hover {
        background-color: #FEE2E2;
    }

    .category-item.active .category-text {
        border-bottom: 2px solid #DC2626;
        padding-bottom: 1px;
    }

    .category-item:hover .category-text {
        border-bottom: 2px solid #E5E7EB;
        padding-bottom: 1px;
    }

    .category-item.active:hover .category-text {
        border-bottom: 2px solid #DC2626;
    }

    .checkbox-custom {
        width: 16px;
        height: 16px;
        border: 2px solid #D1D5DB;
        border-radius: 3px;
        position: relative;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .checkbox-custom.checked {
        background-color: #DC2626;
        border-color: #DC2626;
    }

    .checkbox-custom.checked::after {
        content: '✓';
        position: absolute;
        top: -2px;
        left: 1px;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .mobile-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #E5E7EB;
        border-top: none;
        border-radius: 0 0 0.5rem 0.5rem;
        max-height: 200px;
        overflow-y: auto;
        z-index: 50;
        display: none;
    }

    .mobile-search-dropdown.show {
        display: block;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
        border-bottom: 1px solid #F3F4F6;
    }

    .dropdown-item:hover {
        background-color: #F9FAFB;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }
</style>

<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900 to-gray-800 z-10"></div>
    <img src="<?= $categoryImageUrl ?>"
        alt="<?= htmlspecialchars(!empty($categoryId) && isset($category) ? $category['name'] : 'Building Materials') ?> Banner"
        class="w-full h-full object-cover opacity-20">
    <div class="container mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 md:pt-12 z-20">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold text-white mb-4">
                    <?= htmlspecialchars(!empty($categoryId) && isset($category) ? $category['name'] : 'Building Materials') ?>
                </h1>
                <nav class="flex text-xs md:text-sm text-gray-300 overflow-hidden whitespace-nowrap">
                    <a href="<?= BASE_URL ?>" class="hover:text-white transition-colors truncate max-w-[30%]">Zzimba
                        Online</a>
                    <span class="mx-2">/</span>
                    <a href="<?= BASE_URL ?>materials-yard"
                        class="hover:text-white transition-colors truncate max-w-[30%]">Building Materials</a>
                    <?php if (!empty($categoryId) && isset($category)): ?>
                        <span class="mx-2">/</span>
                        <span
                            class="text-white font-medium truncate max-w-[40%]"><?= htmlspecialchars($category['name']) ?></span>
                    <?php endif; ?>
                </nav>
                <?php if (!empty($categoryId) && isset($category) && !empty($category['description'])): ?>
                    <p class="text-gray-200 mt-2 line-clamp-3 max-w-2xl hidden md:block">
                        <?= htmlspecialchars($category['description']) ?>
                    </p>
                <?php elseif (empty($categoryId)): ?>
                    <p class="text-gray-200 mt-2 line-clamp-3 max-w-2xl hidden md:block">
                        Discover a comprehensive range of high-quality building materials for all your construction needs.
                        From foundation materials to finishing touches, find everything you need to bring your construction
                        projects to life.
                    </p>
                <?php endif; ?>
            </div>

            <div class="share-container mt-4 md:mt-0 hidden md:flex">
                <span class="share-label text-white">SHARE</span>
                <div class="share-buttons">
                    <button onclick="copyLink()" class="share-button">
                        <i class="fa-solid fa-link"></i>
                        <span class="tooltip">Copy link to clipboard</span>
                    </button>
                    <button onclick="shareOnWhatsApp()" class="share-button">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span class="tooltip">Share on WhatsApp</span>
                    </button>
                    <button onclick="shareOnFacebook()" class="share-button">
                        <i class="fa-brands fa-facebook-f"></i>
                        <span class="tooltip">Share on Facebook</span>
                    </button>
                    <button onclick="shareOnTwitter()" class="share-button">
                        <i class="fa-brands fa-x-twitter"></i>
                        <span class="tooltip">Post on X</span>
                    </button>
                    <button onclick="shareOnLinkedIn()" class="share-button">
                        <i class="fa-brands fa-linkedin-in"></i>
                        <span class="tooltip">Share on LinkedIn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="w-full lg:w-1/4 order-2 lg:order-1 hidden lg:block">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 sticky top-4">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Categories</h2>

                    <?php if (!empty($categoryId)): ?>
                        <button onclick="clearSelection()"
                            class="category-item flex items-center justify-between px-3 py-2 rounded-md mb-4 bg-gray-50 hover:bg-gray-100 w-full text-left">
                            <span class="font-medium text-gray-600">
                                <i class="fas fa-times-circle mr-2"></i> Clear Selection
                            </span>
                        </button>
                    <?php endif; ?>

                    <div class="relative mb-4">
                        <input type="text" id="categorySearch" placeholder="Search categories..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                    </div>
                </div>

                <div class="p-4 max-h-[500px] overflow-y-auto">
                    <?php foreach ($allCategories as $cat): ?>
                        <a href="<?= BASE_URL ?>view/category/<?= $cat['id'] ?>"
                            class="category-item flex items-center justify-between px-3 py-2 rounded-md mb-1 <?= ($cat['id'] === $categoryId) ? 'active' : '' ?>"
                            data-category-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>"
                            title="<?= htmlspecialchars($cat['name']) ?>">
                            <span class="category-text font-medium flex-1 truncate pr-3">
                                <?= htmlspecialchars($cat['name']) ?>
                            </span>
                            <div class="checkbox-custom ml-2 <?= ($cat['id'] === $categoryId) ? 'checked' : '' ?>"></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-3/4 order-1 lg:order-2">
            <div class="lg:hidden mb-6">
                <div class="relative">
                    <input type="text" id="mobileSearch"
                        placeholder="<?= !empty($categoryId) && isset($category) ? 'Search categories...' : 'Search categories...' ?>"
                        value="<?= !empty($categoryId) && isset($category) ? htmlspecialchars($category['name']) : '' ?>"
                        class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent bg-white">

                    <?php if (!empty($categoryId) && isset($category)): ?>
                        <button onclick="clearSelection()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php else: ?>
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    <?php endif; ?>

                    <div id="mobileDropdown" class="mobile-search-dropdown">
                        <?php foreach ($allCategories as $cat): ?>
                            <div class="dropdown-item" data-category-id="<?= $cat['id'] ?>"
                                data-category-name="<?= htmlspecialchars($cat['name']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 md:p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="text-sm text-gray-500">
                        <?= count($products) ?> products found
                    </div>
                </div>

                <?php if (count($products) === 0): ?>
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-3">
                            <i class="fas fa-box-open text-4xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium">No products found</p>
                        <p class="text-gray-500 text-sm mt-1">Try selecting a different category or check back later</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                        <?php foreach ($products as $product): ?>
                            <div
                                class="product-card transform transition-transform duration-300 hover:-translate-y-1 h-full flex flex-col">
                                <div class="relative">
                                    <img src="<?= $product['primary_image'] ?>" alt="<?= htmlspecialchars($product['title']) ?>"
                                        class="w-full h-40 md:h-48 object-cover">

                                    <div class="product-details-btn">
                                        <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>"
                                            class="bg-white text-gray-800 px-3 md:px-4 py-2 rounded-lg font-medium hover:bg-rose-600 hover:text-white transition-colors text-sm">
                                            View Details
                                        </a>
                                    </div>
                                </div>

                                <div class="p-3 md:p-5 flex flex-col justify-between flex-1">
                                    <div>
                                        <h3 class="font-bold text-gray-800 mb-2 line-clamp-2 text-sm md:text-base">
                                            <?= htmlspecialchars($product['title']) ?>
                                        </h3>

                                        <p class="text-gray-600 text-xs md:text-sm mb-3 line-clamp-2 hidden md:block">
                                            <?= htmlspecialchars($product['description']) ?>
                                        </p>

                                        <div class="flex items-center text-gray-500 text-xs md:text-sm mb-4">
                                            <i class="fas fa-eye mr-1"></i>
                                            <span><?= number_format($product['views']) ?> views</span>
                                        </div>
                                    </div>

                                    <div class="flex space-x-2 mt-auto">
                                        <?php if ($product['has_pricing']): ?>
                                            <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=buy"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                                <i class="fas fa-shopping-cart mr-1"></i> Buy
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>view/product/<?= $product['id'] ?>?action=sell"
                                            class="bg-sky-600 hover:bg-sky-700 text-white px-3 md:px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-xs md:text-sm">
                                            <i class="fas fa-tag mr-1"></i> Sell
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('categorySearch');
        const categoryItems = document.querySelectorAll('.category-item[data-category-name]');
        const mobileSearch = document.getElementById('mobileSearch');
        const mobileDropdown = document.getElementById('mobileDropdown');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();

                categoryItems.forEach(item => {
                    const categoryName = item.getAttribute('data-category-name');
                    item.style.display = categoryName.includes(searchTerm) ? 'flex' : 'none';
                });
            });
        }

        if (mobileSearch && mobileDropdown) {
            mobileSearch.addEventListener('focus', function () {
                mobileDropdown.classList.add('show');
            });

            mobileSearch.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase();
                const dropdownItems = mobileDropdown.querySelectorAll('.dropdown-item');

                dropdownItems.forEach(item => {
                    const categoryName = item.getAttribute('data-category-name').toLowerCase();
                    item.style.display = categoryName.includes(searchTerm) ? 'block' : 'none';
                });

                mobileDropdown.classList.add('show');
            });

            document.addEventListener('click', function (e) {
                if (!mobileSearch.contains(e.target) && !mobileDropdown.contains(e.target)) {
                    mobileDropdown.classList.remove('show');
                }
            });

            const dropdownItems = mobileDropdown.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function () {
                    const categoryId = this.getAttribute('data-category-id');
                    window.location.href = `<?= BASE_URL ?>view/category/${categoryId}`;
                });
            });
        }
    });

    function clearSelection() {
        window.location.href = '<?= BASE_URL ?>materials-yard';
    }

    function copyLink() {
        const currentUrl = window.location.href;
        navigator.clipboard.writeText(currentUrl).then(() => {
            showToast('Link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    function shareOnWhatsApp() {
        const currentUrl = window.location.href;
        const categoryName = "<?= !empty($categoryId) && isset($category) ? addslashes($category['name']) : 'Building Materials' ?>";
        const message = `Check out ${categoryName} products on Zzimba Online: ${currentUrl}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const categoryName = "<?= !empty($categoryId) && isset($category) ? addslashes($category['name']) : 'Building Materials' ?>";
        const message = `Check out ${categoryName} products on Zzimba Online:`;
        window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const categoryName = "<?= !empty($categoryId) && isset($category) ? addslashes($category['name']) : 'Building Materials' ?>";
        const message = `Check out ${categoryName} products on Zzimba Online.`;
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(categoryName)}&summary=${encodeURIComponent(message)}`, '_blank');
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity duration-300`;
        toast.textContent = message;

        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('opacity-100'), 10);

        setTimeout(() => {
            toast.classList.remove('opacity-100');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>