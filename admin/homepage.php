<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Manage Index Page';
$activeNav = 'homepage';

// Load homepage data from JSON file
function loadHomepageData()
{
    $filePath = __DIR__ . '/../page-data/homepage/index.json';

    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?: [];
    }

    // Return empty default structure if file doesn't exist
    return [
        'heroSlides' => [],
        'requestQuoteSection' => [
            'buttonText' => 'Request a Quote Now',
            'buttonUrl' => 'request-for-quote',
            'description' => 'Get personalized quotes for your construction needs',
            'active' => true
        ],
        'keyFeatures' => [],
        'featuredProductsSection' => [
            'title' => 'Featured Products',
            'linkText' => 'View All Products →',
            'linkUrl' => '#',
            'defaultRows' => 1,
            'productsPerRow' => 4,
            'loadMoreButtonText' => 'Load More',
            'active' => true
        ],
        'categoriesSection' => [
            'title' => 'Shop by Category',
            'linkText' => 'View All Categories →',
            'linkUrl' => '#',
            'defaultRows' => 1,
            'categoriesPerRow' => 4,
            'loadMoreButtonText' => 'Load More',
            'active' => true
        ],
        'partnersSection' => [
            'title' => 'Our Trusted Partners',
            'description' => 'We collaborate with leading suppliers and vendors in the construction industry to bring you the best products and services.',
            'ctaButtonText' => 'Become a Partner',
            'ctaButtonUrl' => '#',
            'active' => true
        ],
        'partners' => []
    ];
}

// Load data from JSON
$homepageData = loadHomepageData();

// Extract data from the loaded JSON
$heroSlides = $homepageData['heroSlides'] ?? [];
$requestQuoteSection = $homepageData['requestQuoteSection'] ?? [];
$keyFeatures = $homepageData['keyFeatures'] ?? [];
$featuredProductsSection = $homepageData['featuredProductsSection'] ?? [];
$categoriesSection = $homepageData['categoriesSection'] ?? [];
$partnersSection = $homepageData['partnersSection'] ?? [];
$partners = $homepageData['partners'] ?? [];

// Handle active tab
$activeTab = $_GET['tab'] ?? 'hero';

ob_start();
?>

<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-secondary">Homepage Management</h1>
            <p class="text-sm text-gray-text mt-1">Manage your website homepage content and layout</p>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3">
            <a href="dashboard"
                class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
            <nav class="flex overflow-x-auto py-2" aria-label="Tabs">
                <a href="?tab=hero"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap <?= $activeTab === 'hero' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Hero Slider
                </a>
                <a href="?tab=quote"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap ml-2 <?= $activeTab === 'quote' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Request Quote
                </a>
                <a href="?tab=features"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap ml-2 <?= $activeTab === 'features' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Key Benefits
                </a>
                <a href="?tab=products"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap ml-2 <?= $activeTab === 'products' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Featured Products
                </a>
                <a href="?tab=categories"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap ml-2 <?= $activeTab === 'categories' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Categories
                </a>
                <a href="?tab=partners"
                    class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap ml-2 <?= $activeTab === 'partners' ? 'bg-primary text-white' : 'text-gray-text hover:text-primary hover:bg-gray-50' ?>">
                    Partners
                </a>
            </nav>
        </div>

        <div class="p-6">
            <?php if ($activeTab === 'hero'): ?>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary">Hero Slider Management</h2>
                        <p class="text-sm text-gray-text mt-1">
                            <span id="hero-count"><?= count($heroSlides) ?></span> slides found
                        </p>
                    </div>
                    <button id="addHeroBtn"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-plus"></i>
                        <span>Add New Slide</span>
                    </button>
                </div>

                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-amber-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-amber-700">
                                For best results, use images with dimensions 1800×600 pixels (3:1 ratio). Images will be
                                displayed at 16:9 ratio on mobile devices.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="hero-slides-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($heroSlides as $slide): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                            data-id="<?= $slide['id'] ?>" data-order="<?= $slide['order'] ?>">
                            <div class="relative aspect-[3/1] bg-gray-100">
                                <?php if ($slide['image']): ?>
                                    <img src="<?= BASE_URL . $slide['image'] ?>" alt="Slide" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <img src="https://placehold.co/1800x600/?text=Hero+Slide" alt="Placeholder"
                                        class="w-full h-full object-cover">
                                <?php endif; ?>
                                <div class="absolute top-2 right-2 flex space-x-1">
                                    <span
                                        class="bg-white text-gray-700 rounded-full h-6 w-6 flex items-center justify-center shadow-md">
                                        <?= $slide['order'] ?>
                                    </span>
                                    <button
                                        class="btn-edit-hero bg-white text-blue-600 rounded-full h-6 w-6 flex items-center justify-center shadow-md hover:text-blue-800"
                                        data-id="<?= $slide['id'] ?>">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button
                                        class="btn-delete-hero bg-white text-red-600 rounded-full h-6 w-6 flex items-center justify-center shadow-md hover:text-red-800"
                                        data-id="<?= $slide['id'] ?>">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3">
                                    <h3 class="text-white text-sm font-bold truncate"><?= strip_tags($slide['title']) ?></h3>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="mb-2">
                                    <p class="text-sm text-gray-500 line-clamp-2"><?= $slide['subtitle'] ?></p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div
                                            class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                            <input type="checkbox"
                                                class="hero-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                data-id="<?= $slide['id'] ?>" <?= $slide['active'] ? 'checked' : '' ?>>
                                            <label
                                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                        </div>
                                        <span
                                            class="ml-2 text-xs font-medium <?= $slide['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                            <?= $slide['active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-700">
                                        Button: <?= $slide['buttonText'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($activeTab === 'quote'): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-secondary">Request Quote Section</h2>
                    <p class="text-sm text-gray-text mt-1">Configure the request quote section on the homepage</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                This section allows customers to request personalized quotes for their construction needs.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="quoteForm" class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="button-text" class="block text-sm font-medium text-gray-700 mb-1">Button Text <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="button-text" name="button-text"
                                value="<?= $requestQuoteSection['buttonText'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                required>
                        </div>
                        <div>
                            <label for="button-url" class="block text-sm font-medium text-gray-700 mb-1">Button URL <span
                                    class="text-red-500">*</span></label>
                            <input type="text" id="button-url" name="button-url"
                                value="<?= $requestQuoteSection['buttonUrl'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                required>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"><?= $requestQuoteSection['description'] ?? '' ?></textarea>
                    </div>
                    <div>
                        <label for="quote-status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center space-x-3">
                            <div
                                class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                <input type="checkbox" id="quote-status-toggle"
                                    class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    <?= ($requestQuoteSection['active'] ?? true) ? 'checked' : '' ?>>
                                <label for="quote-status-toggle"
                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                            </div>
                            <span id="quote-status-text"
                                class="text-sm font-medium text-gray-700"><?= ($requestQuoteSection['active'] ?? true) ? 'Active' : 'Inactive' ?></span>
                            <input type="hidden" id="quote-status" name="status"
                                value="<?= ($requestQuoteSection['active'] ?? true) ? 'active' : 'inactive' ?>">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" id="saveQuoteBtn"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>

            <?php elseif ($activeTab === 'features'): ?>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary">Key Benefits Management</h2>
                        <p class="text-sm text-gray-text mt-1">
                            <span id="benefits-count"><?= count($keyFeatures) ?></span> benefits found
                        </p>
                    </div>
                    <button id="addBenefitBtn"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-plus"></i>
                        <span>Add New Benefit</span>
                    </button>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                These key benefits highlight the main advantages of using our platform. You can use emoji or
                                icons for visual representation.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="benefits-container" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($keyFeatures as $feature): ?>
                        <div class="bg-white border rounded-lg shadow-sm p-6 relative" data-id="<?= $feature['id'] ?>"
                            data-order="<?= $feature['order'] ?>">
                            <div class="absolute top-3 right-3 flex space-x-2">
                                <button class="btn-edit-benefit text-blue-600 hover:text-blue-900"
                                    data-id="<?= $feature['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete-benefit text-red-600 hover:text-red-900"
                                    data-id="<?= $feature['id'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="text-4xl mb-4"><?= $feature['icon'] ?></div>
                            <h3 class="text-xl font-semibold mb-2"><?= $feature['title'] ?></h3>
                            <p class="text-gray-600"><?= $feature['description'] ?></p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div
                                        class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                        <input type="checkbox"
                                            class="benefit-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                            data-id="<?= $feature['id'] ?>" <?= $feature['active'] ? 'checked' : '' ?>>
                                        <label
                                            class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                    </div>
                                    <span
                                        class="ml-2 text-xs font-medium <?= $feature['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                        <?= $feature['active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                                <div class="flex items-center">
                                    <span
                                        class="bg-gray-100 text-gray-700 rounded-full h-6 w-6 flex items-center justify-center">
                                        <?= $feature['order'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($activeTab === 'products'): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-secondary">Featured Products Section</h2>
                    <p class="text-sm text-gray-text mt-1">Configure how featured products are displayed on the homepage</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Configure how featured products are displayed on the homepage. Products themselves are
                                managed in the Products section.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="productsForm" class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="products-title" class="block text-sm font-medium text-gray-700 mb-1">Section Title
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="products-title" name="title"
                                value="<?= $featuredProductsSection['title'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                required>
                        </div>
                        <div>
                            <label for="products-link-text" class="block text-sm font-medium text-gray-700 mb-1">Link
                                Text</label>
                            <input type="text" id="products-link-text" name="linkText"
                                value="<?= $featuredProductsSection['linkText'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="products-link-url" class="block text-sm font-medium text-gray-700 mb-1">Link
                                URL</label>
                            <input type="text" id="products-link-url" name="linkUrl"
                                value="<?= $featuredProductsSection['linkUrl'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="products-button-text" class="block text-sm font-medium text-gray-700 mb-1">Load More
                                Button Text</label>
                            <input type="text" id="products-button-text" name="loadMoreButtonText"
                                value="<?= $featuredProductsSection['loadMoreButtonText'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="products-default-rows" class="block text-sm font-medium text-gray-700 mb-1">Default
                                Rows</label>
                            <input type="number" id="products-default-rows" name="defaultRows"
                                value="<?= $featuredProductsSection['defaultRows'] ?? 1 ?>" min="1" max="5"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <p class="mt-1 text-xs text-gray-500">Number of rows to show initially</p>
                        </div>
                        <div>
                            <label for="products-per-row" class="block text-sm font-medium text-gray-700 mb-1">Products Per
                                Row</label>
                            <select id="products-per-row" name="productsPerRow"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="1" <?= ($featuredProductsSection['productsPerRow'] ?? 4) == 1 ? 'selected' : '' ?>>1
                                    product</option>
                                <option value="2" <?= ($featuredProductsSection['productsPerRow'] ?? 4) == 2 ? 'selected' : '' ?>>2
                                    products</option>
                                <option value="3" <?= ($featuredProductsSection['productsPerRow'] ?? 4) == 3 ? 'selected' : '' ?>>3
                                    products</option>
                                <option value="4" <?= ($featuredProductsSection['productsPerRow'] ?? 4) == 4 ? 'selected' : '' ?>>4
                                    products</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">On mobile, this will automatically adjust</p>
                        </div>
                    </div>
                    <div>
                        <label for="products-status-toggle"
                            class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center space-x-3">
                            <div
                                class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                <input type="checkbox" id="products-status-toggle"
                                    class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    <?= ($featuredProductsSection['active'] ?? true) ? 'checked' : '' ?>>
                                <label for="products-status-toggle"
                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                            </div>
                            <span id="products-status-text"
                                class="text-sm font-medium text-gray-700"><?= ($featuredProductsSection['active'] ?? true) ? 'Active' : 'Inactive' ?></span>
                            <input type="hidden" id="products-status" name="status"
                                value="<?= ($featuredProductsSection['active'] ?? true) ? 'active' : 'inactive' ?>">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" id="saveProductsBtn"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>

            <?php elseif ($activeTab === 'categories'): ?>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-secondary">Categories Section</h2>
                    <p class="text-sm text-gray-text mt-1">Configure how categories are displayed on the homepage</p>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Configure how categories are displayed on the homepage. Categories themselves are managed in
                                the Products section.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="categoriesForm" class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="categories-title" class="block text-sm font-medium text-gray-700 mb-1">Section Title
                                <span class="text-red-500">*</span></label>
                            <input type="text" id="categories-title" name="title"
                                value="<?= $categoriesSection['title'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                required>
                        </div>
                        <div>
                            <label for="categories-link-text" class="block text-sm font-medium text-gray-700 mb-1">Link
                                Text</label>
                            <input type="text" id="categories-link-text" name="linkText"
                                value="<?= $categoriesSection['linkText'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="categories-link-url" class="block text-sm font-medium text-gray-700 mb-1">Link
                                URL</label>
                            <input type="text" id="categories-link-url" name="linkUrl"
                                value="<?= $categoriesSection['linkUrl'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="categories-button-text" class="block text-sm font-medium text-gray-700 mb-1">Load
                                More Button Text</label>
                            <input type="text" id="categories-button-text" name="loadMoreButtonText"
                                value="<?= $categoriesSection['loadMoreButtonText'] ?? '' ?>"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label for="categories-default-rows"
                                class="block text-sm font-medium text-gray-700 mb-1">Default Rows</label>
                            <input type="number" id="categories-default-rows" name="defaultRows"
                                value="<?= $categoriesSection['defaultRows'] ?? 1 ?>" min="1" max="5"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <p class="mt-1 text-xs text-gray-500">Number of rows to show initially</p>
                        </div>
                        <div>
                            <label for="categories-per-row" class="block text-sm font-medium text-gray-700 mb-1">Categories
                                Per Row</label>
                            <select id="categories-per-row" name="categoriesPerRow"
                                class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="1" <?= ($categoriesSection['categoriesPerRow'] ?? 4) == 1 ? 'selected' : '' ?>>1
                                    category</option>
                                <option value="2" <?= ($categoriesSection['categoriesPerRow'] ?? 4) == 2 ? 'selected' : '' ?>>2
                                    categories</option>
                                <option value="3" <?= ($categoriesSection['categoriesPerRow'] ?? 4) == 3 ? 'selected' : '' ?>>3
                                    categories</option>
                                <option value="4" <?= ($categoriesSection['categoriesPerRow'] ?? 4) == 4 ? 'selected' : '' ?>>4
                                    categories</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">On mobile, this will automatically adjust</p>
                        </div>
                    </div>
                    <div>
                        <label for="categories-status-toggle"
                            class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center space-x-3">
                            <div
                                class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                <input type="checkbox" id="categories-status-toggle"
                                    class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    <?= ($categoriesSection['active'] ?? true) ? 'checked' : '' ?>>
                                <label for="categories-status-toggle"
                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                            </div>
                            <span id="categories-status-text"
                                class="text-sm font-medium text-gray-700"><?= ($categoriesSection['active'] ?? true) ? 'Active' : 'Inactive' ?></span>
                            <input type="hidden" id="categories-status" name="status"
                                value="<?= ($categoriesSection['active'] ?? true) ? 'active' : 'inactive' ?>">
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end">
                        <button type="submit" id="saveCategoriesBtn"
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>

            <?php elseif ($activeTab === 'partners'): ?>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-secondary">Partners Section</h2>
                        <p class="text-sm text-gray-text mt-1">
                            <span id="partners-count"><?= count($partners) ?></span> partners found
                        </p>
                    </div>
                    <button id="addPartnerBtn"
                        class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 w-full md:w-auto justify-center">
                        <i class="fas fa-plus"></i>
                        <span>Add New Partner</span>
                    </button>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Manage partners and their display on the homepage. For best results, use logo images with
                                dimensions 200×100 pixels (2:1 ratio) or 100×100 pixels (1:1 ratio).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Section Settings</h3>
                    <form id="partnersSettingsForm"
                        class="space-y-6 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="partners-title" class="block text-sm font-medium text-gray-700 mb-1">Section
                                    Title <span class="text-red-500">*</span></label>
                                <input type="text" id="partners-title" name="title"
                                    value="<?= $partnersSection['title'] ?? '' ?>"
                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    required>
                            </div>
                            <div>
                                <label for="partners-cta-text" class="block text-sm font-medium text-gray-700 mb-1">CTA
                                    Button Text</label>
                                <input type="text" id="partners-cta-text" name="ctaButtonText"
                                    value="<?= $partnersSection['ctaButtonText'] ?? '' ?>"
                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                            <div>
                                <label for="partners-cta-url" class="block text-sm font-medium text-gray-700 mb-1">CTA
                                    Button URL</label>
                                <input type="text" id="partners-cta-url" name="ctaButtonUrl"
                                    value="<?= $partnersSection['ctaButtonUrl'] ?? '' ?>"
                                    class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                        </div>
                        <div>
                            <label for="partners-description" class="block text-sm font-medium text-gray-700 mb-1">Section
                                Description</label>
                            <textarea id="partners-description" name="description" rows="3"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"><?= $partnersSection['description'] ?? '' ?></textarea>
                        </div>
                        <div>
                            <label for="partners-status-toggle"
                                class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="flex items-center space-x-3">
                                <div
                                    class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                    <input type="checkbox" id="partners-status-toggle"
                                        class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                        <?= ($partnersSection['active'] ?? true) ? 'checked' : '' ?>>
                                    <label for="partners-status-toggle"
                                        class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                </div>
                                <span id="partners-status-text"
                                    class="text-sm font-medium text-gray-700"><?= ($partnersSection['active'] ?? true) ? 'Active' : 'Inactive' ?></span>
                                <input type="hidden" id="partners-status" name="status"
                                    value="<?= ($partnersSection['active'] ?? true) ? 'active' : 'inactive' ?>">
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end">
                            <button type="submit" id="savePartnersSettingsBtn"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                Save Section Settings
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mt-10">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Partners List</h3>
                    <div id="partners-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <?php foreach ($partners as $partner): ?>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                                data-id="<?= $partner['id'] ?>" data-order="<?= $partner['order'] ?>">
                                <div class="relative aspect-[2/1] bg-gray-100 flex items-center justify-center p-4">
                                    <?php if ($partner['logo']): ?>
                                        <img src="<?= BASE_URL . $partner['logo'] ?>" alt="<?= $partner['name'] ?>"
                                            class="max-h-full max-w-full object-contain">
                                    <?php else: ?>
                                        <img src="https://placehold.co/200x100/text=<?= urlencode($partner['name']) ?>"
                                            alt="<?= $partner['name'] ?>" class="max-h-full max-w-full object-contain">
                                    <?php endif; ?>
                                    <div class="absolute top-2 right-2 flex space-x-1">
                                        <span
                                            class="bg-white text-gray-700 rounded-full h-6 w-6 flex items-center justify-center shadow-md">
                                            <?= $partner['order'] ?>
                                        </span>
                                        <button
                                            class="btn-edit-partner bg-white text-blue-600 rounded-full h-6 w-6 flex items-center justify-center shadow-md hover:text-blue-800"
                                            data-id="<?= $partner['id'] ?>">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button
                                            class="btn-delete-partner bg-white text-red-600 rounded-full h-6 w-6 flex items-center justify-center shadow-md hover:text-red-800"
                                            data-id="<?= $partner['id'] ?>">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900"><?= $partner['name'] ?></h3>
                                    <div class="mt-2 flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div
                                                class="relative inline-block w-10 h-5 transition duration-200 ease-in-out rounded-full cursor-pointer">
                                                <input type="checkbox"
                                                    class="partner-status-toggle absolute w-5 h-5 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                                    data-id="<?= $partner['id'] ?>" <?= $partner['active'] ? 'checked' : '' ?>>
                                                <label
                                                    class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                                            </div>
                                            <span
                                                class="ml-2 text-xs font-medium <?= $partner['active'] ? 'text-green-600' : 'text-gray-500' ?>">
                                                <?= $partner['active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </div>
                                        <?php if ($partner['hasLink']): ?>
                                            <span class="text-xs text-blue-600">
                                                <i class="fas fa-link mr-1"></i> Has Link
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">
                                                <i class="fas fa-unlink mr-1"></i> No Link
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hero Slide Modal -->
<div id="heroModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideHeroModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="heroModalTitle">Add New Slide</h3>
            <button onclick="hideHeroModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="heroForm" class="space-y-6">
                <input type="hidden" id="heroId" name="heroId" value="">
                <input type="hidden" id="heroTempImagePath" name="tempImagePath" value="">
                <input type="hidden" id="heroRemoveImage" name="removeImage" value="0">

                <div class="border-b border-gray-100 pb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Slide Image (3:1 ratio)</h3>
                    <p class="text-sm text-amber-600 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        For best results, use images with dimensions 1800×600 pixels (3:1 ratio). Images will be
                        displayed at 16:9 ratio on mobile devices.
                    </p>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Image</label>
                                <div class="flex items-center gap-2">
                                    <label for="heroImage"
                                        class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        <span>Choose File</span>
                                    </label>
                                    <span id="heroSelectedFileName" class="text-sm text-gray-500">No file
                                        selected</span>
                                    <input type="file" id="heroImage" name="image"
                                        accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 1800×600 pixels (3:1). Max 5MB.
                                </p>
                            </div>
                            <div id="heroUploadProgress" class="w-full bg-gray-200 rounded-full h-2.5 mb-4 hidden">
                                <div id="heroUploadProgressBar" class="bg-primary h-2.5 rounded-full" style="width: 0%">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div id="heroCropperContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Crop Image (3:1)</label>
                                <div class="relative aspect-[3/1] bg-gray-100 rounded-lg overflow-hidden">
                                    <img id="heroCropperImage" src="https://placehold.co/1800x600" alt="Image to crop"
                                        class="max-w-full">
                                </div>
                                <div class="flex justify-end mt-3 space-x-2">
                                    <button type="button" id="heroCancelCrop"
                                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="button" id="heroApplyCrop"
                                        class="px-3 py-1.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Apply Crop
                                    </button>
                                </div>
                            </div>

                            <div id="heroImagePreviewContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image Preview</label>
                                <div class="relative aspect-[3/1] bg-gray-100 rounded-lg overflow-hidden">
                                    <img id="heroImagePreview" src="https://placehold.co/1800x600"
                                        alt="Hero slide preview" class="w-full h-full object-cover">
                                    <button type="button" id="heroRemoveImageBtn"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="heroTitle" class="block text-sm font-medium text-gray-700 mb-1">Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="heroTitle" name="title"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                        <p class="mt-1 text-xs text-gray-500">You can use &lt;br&gt; for line breaks</p>
                    </div>
                    <div>
                        <label for="heroSubtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                        <input type="text" id="heroSubtitle" name="subtitle"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label for="heroButtonText" class="block text-sm font-medium text-gray-700 mb-1">Button
                            Text</label>
                        <input type="text" id="heroButtonText" name="buttonText"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div>
                        <label for="heroButtonUrl" class="block text-sm font-medium text-gray-700 mb-1">Button
                            URL</label>
                        <input type="text" id="heroButtonUrl" name="buttonUrl"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label for="hero-status-toggle" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="hero-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="hero-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="hero-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="hero-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideHeroModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitHero"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Slide
            </button>
        </div>
    </div>
</div>

<!-- Benefit Modal -->
<div id="benefitModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideBenefitModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="benefitModalTitle">Add New Benefit</h3>
            <button onclick="hideBenefitModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="benefitForm" class="space-y-6">
                <input type="hidden" id="benefitId" name="benefitId" value="">

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label for="benefitIcon" class="block text-sm font-medium text-gray-700 mb-1">Icon/Emoji <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="benefitIcon" name="icon" placeholder="🏗️"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                    </div>
                    <div>
                        <label for="benefitTitle" class="block text-sm font-medium text-gray-700 mb-1">Title <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="benefitTitle" name="title" placeholder="Quality Materials"
                            class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            required>
                    </div>
                </div>
                <div>
                    <label for="benefitDescription"
                        class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="benefitDescription" name="description" rows="3"
                        placeholder="Premium construction supplies from trusted manufacturers"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                </div>
                <div>
                    <label for="benefit-status-toggle"
                        class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="benefit-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="benefit-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="benefit-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="benefit-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideBenefitModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitBenefit"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Benefit
            </button>
        </div>
    </div>
</div>

<!-- Partner Modal -->
<div id="partnerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hidePartnerModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="partnerModalTitle">Add New Partner</h3>
            <button onclick="hidePartnerModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="partnerForm" class="space-y-6">
                <input type="hidden" id="partnerId" name="partnerId" value="">
                <input type="hidden" id="partnerTempImagePath" name="tempImagePath" value="">
                <input type="hidden" id="partnerRemoveImage" name="removeImage" value="0">

                <div>
                    <label for="partnerName" class="block text-sm font-medium text-gray-700 mb-1">Partner Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="partnerName" name="name" placeholder="Company Name"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                        required>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Logo Image</h3>
                    <p class="text-sm text-amber-600 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        For best results, use logo images with dimensions 200×100 pixels (2:1 ratio) or 100×100 pixels
                        (1:1
                        ratio).
                    </p>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Logo</label>
                                <div class="flex items-center gap-2">
                                    <label for="partnerLogo"
                                        class="cursor-pointer px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        <i class="fas fa-upload mr-2"></i>
                                        <span>Choose File</span>
                                    </label>
                                    <span id="partnerSelectedFileName" class="text-sm text-gray-500">No file
                                        selected</span>
                                    <input type="file" id="partnerLogo" name="logo"
                                        accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Recommended size: 200×100 pixels (2:1) or 100×100
                                    pixels
                                    (1:1). Max 5MB.</p>
                            </div>
                            <div id="partnerUploadProgress" class="w-full bg-gray-200 rounded-full h-2.5 mb-4 hidden">
                                <div id="partnerUploadProgressBar" class="bg-primary h-2.5 rounded-full"
                                    style="width: 0%">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-center space-x-4 mb-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="logo-ratio" value="2:1"
                                        class="h-4 w-4 border-gray-300 text-primary focus:ring-primary" checked>
                                    <span class="ml-2 text-sm text-gray-700">2:1 ratio</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="logo-ratio" value="1:1"
                                        class="h-4 w-4 border-gray-300 text-primary focus:ring-primary">
                                    <span class="ml-2 text-sm text-gray-700">1:1 ratio</span>
                                </label>
                            </div>

                            <div id="partnerCropperContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Crop Logo</label>
                                <div class="relative bg-gray-100 rounded-lg overflow-hidden" id="partnerCropperWrapper">
                                    <img id="partnerCropperImage" src="https://placehold.co/200x100" alt="Logo to crop"
                                        class="max-w-full">
                                </div>
                                <div class="flex justify-end mt-3 space-x-2">
                                    <button type="button" id="partnerCancelCrop"
                                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="button" id="partnerApplyCrop"
                                        class="px-3 py-1.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                        Apply Crop
                                    </button>
                                </div>
                            </div>

                            <div id="partnerImagePreviewContainer" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Logo Preview</label>
                                <div class="relative bg-gray-100 rounded-lg overflow-hidden" id="partnerPreviewWrapper">
                                    <img id="partnerImagePreview" src="https://placehold.co/200x100"
                                        alt="Partner logo preview" class="max-w-full mx-auto">
                                    <button type="button" id="partnerRemoveImageBtn"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center mb-4">
                    <input type="checkbox" id="partner-has-link" name="hasLink"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                    <label for="partner-has-link" class="ml-2 block text-sm text-gray-900">Has Redirect Link</label>
                </div>

                <div id="redirect-link-container" class="hidden">
                    <label for="partnerRedirectLink" class="block text-sm font-medium text-gray-700 mb-1">Redirect
                        Link</label>
                    <input type="text" id="partnerRedirectLink" name="redirectLink"
                        placeholder="https://partner-website.com"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="partner-status-toggle"
                        class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <div class="flex items-center space-x-3">
                        <div
                            class="relative inline-block w-12 h-6 transition duration-200 ease-in-out rounded-full cursor-pointer">
                            <input type="checkbox" id="partner-status-toggle"
                                class="absolute w-6 h-6 transition duration-200 ease-in-out transform bg-white border rounded-full appearance-none cursor-pointer peer border-gray-300 checked:right-0 checked:border-primary checked:bg-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                checked>
                            <label for="partner-status-toggle"
                                class="block h-full overflow-hidden rounded-full cursor-pointer bg-gray-300 peer-checked:bg-primary/30"></label>
                        </div>
                        <span id="partner-status-text" class="text-sm font-medium text-gray-700">Active</span>
                        <input type="hidden" id="partner-status" name="status" value="active">
                    </div>
                </div>
            </form>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hidePartnerModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="submitPartner"
                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                Save Partner
            </button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/20" onclick="hideDeleteModal()"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-secondary" id="deleteModalTitle">Delete Item</h3>
            <button onclick="hideDeleteModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4" id="deleteModalMessage">Are you sure you want to delete this item? This action
                cannot be undone.</p>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="text-gray-500" id="deleteItemTypeLabel">Item:</div>
                    <div class="font-medium text-gray-900" id="deleteItemName"></div>
                    <div class="text-gray-500">Status:</div>
                    <div class="font-medium text-gray-900" id="deleteItemStatus"></div>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="hideDeleteModal()"
                class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                Delete
            </button>
        </div>
    </div>
</div>

<!-- Session Expired Modal -->
<div id="sessionExpiredModal" class="fixed inset-0 z-[1000] flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-4 relative z-10">
        <div class="p-6">
            <div class="text-center mb-4">
                <i class="fas fa-clock text-4xl text-amber-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900">Session Expired</h3>
                <p class="text-sm text-gray-500 mt-2">Your session has expired due to inactivity.</p>
                <p class="text-sm text-gray-500 mt-1">Redirecting in <span id="countdown">10</span> seconds...</p>
            </div>
            <div class="flex justify-center mt-6">
                <button onclick="redirectToLogin()"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Login Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notifications -->
<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span id="successMessage"></span>
    </div>
</div>

<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span id="errorMessage"></span>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black/30 flex items-center justify-center z-[1000] hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-primary/30 border-t-primary rounded-full animate-spin mb-4"></div>
            <p id="loadingMessage" class="text-gray-700 font-medium text-center">Loading...</p>
        </div>
    </div>
</div>

<!-- Include Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    let heroCropper = null;
    let partnerCropper = null;
    let deleteItemType = '';
    let deleteItemId = '';

    document.addEventListener('DOMContentLoaded', function () {
        initializeEventListeners();
        initializeSortable();
        initializeStatusToggles();
    });

    function showLoading(message = 'Loading...') {
        document.getElementById('loadingMessage').textContent = message;
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    function initializeEventListeners() {
        document.getElementById('addHeroBtn')?.addEventListener('click', () => showHeroModal());
        document.querySelectorAll('.btn-edit-hero').forEach(btn => {
            btn.addEventListener('click', function () {
                const slideId = this.getAttribute('data-id');
                showHeroModal(slideId);
            });
        });
        document.querySelectorAll('.btn-delete-hero').forEach(btn => {
            btn.addEventListener('click', function () {
                const slideId = this.getAttribute('data-id');
                showDeleteModal('hero', slideId);
            });
        });
        document.getElementById('submitHero')?.addEventListener('click', submitHeroForm);

        const heroImageInput = document.getElementById('heroImage');
        if (heroImageInput) {
            heroImageInput.addEventListener('change', handleHeroImageUpload);
            document.getElementById('heroCancelCrop').addEventListener('click', cancelHeroCrop);
            document.getElementById('heroApplyCrop').addEventListener('click', applyHeroCrop);
            document.getElementById('heroRemoveImageBtn').addEventListener('click', function () {
                document.getElementById('heroRemoveImage').value = "1";
                document.getElementById('heroImagePreviewContainer').classList.add('hidden');
                document.getElementById('heroSelectedFileName').textContent = 'Image will be removed';
            });
        }

        document.getElementById('addBenefitBtn')?.addEventListener('click', () => showBenefitModal());
        document.querySelectorAll('.btn-edit-benefit').forEach(btn => {
            btn.addEventListener('click', function () {
                const benefitId = this.getAttribute('data-id');
                showBenefitModal(benefitId);
            });
        });
        document.querySelectorAll('.btn-delete-benefit').forEach(btn => {
            btn.addEventListener('click', function () {
                const benefitId = this.getAttribute('data-id');
                showDeleteModal('benefit', benefitId);
            });
        });
        document.getElementById('submitBenefit')?.addEventListener('click', submitBenefitForm);

        document.getElementById('addPartnerBtn')?.addEventListener('click', () => showPartnerModal());
        document.querySelectorAll('.btn-edit-partner').forEach(btn => {
            btn.addEventListener('click', function () {
                const partnerId = this.getAttribute('data-id');
                showPartnerModal(partnerId);
            });
        });
        document.querySelectorAll('.btn-delete-partner').forEach(btn => {
            btn.addEventListener('click', function () {
                const partnerId = this.getAttribute('data-id');
                showDeleteModal('partner', partnerId);
            });
        });
        document.getElementById('submitPartner')?.addEventListener('click', submitPartnerForm);

        const partnerLogoInput = document.getElementById('partnerLogo');
        if (partnerLogoInput) {
            partnerLogoInput.addEventListener('change', handlePartnerLogoUpload);
            document.getElementById('partnerCancelCrop').addEventListener('click', cancelPartnerCrop);
            document.getElementById('partnerApplyCrop').addEventListener('click', applyPartnerCrop);
            document.getElementById('partnerRemoveImageBtn').addEventListener('click', function () {
                document.getElementById('partnerRemoveImage').value = "1";
                document.getElementById('partnerImagePreviewContainer').classList.add('hidden');
                document.getElementById('partnerSelectedFileName').textContent = 'Logo will be removed';
            });
        }

        const partnerHasLink = document.getElementById('partner-has-link');
        if (partnerHasLink) {
            partnerHasLink.addEventListener('change', toggleRedirectLink);
        }

        document.getElementById('confirmDelete')?.addEventListener('click', confirmDelete);

        document.getElementById('quoteForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitQuoteForm();
        });

        document.getElementById('productsForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitProductsForm();
        });

        document.getElementById('categoriesForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitCategoriesForm();
        });

        document.getElementById('partnersSettingsForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            submitPartnersSettingsForm();
        });
    }

    function initializeSortable() {
        const heroSlidesContainer = document.getElementById('hero-slides-container');
        if (heroSlidesContainer) {
            new Sortable(heroSlidesContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.bg-white.text-gray-700.rounded-full.h-6.w-6',
                onEnd: function () {
                    updateHeroSlidesOrder();
                }
            });
        }

        const benefitsContainer = document.getElementById('benefits-container');
        if (benefitsContainer) {
            new Sortable(benefitsContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.bg-gray-100.text-gray-700.rounded-full.h-6.w-6',
                onEnd: function () {
                    updateBenefitsOrder();
                }
            });
        }

        const partnersContainer = document.getElementById('partners-container');
        if (partnersContainer) {
            new Sortable(partnersContainer, {
                animation: 150,
                ghostClass: 'bg-gray-100',
                handle: '.bg-white.text-gray-700.rounded-full.h-6.w-6',
                onEnd: function () {
                    updatePartnersOrder();
                }
            });
        }
    }

    function updateHeroSlidesOrder() {
        const slides = document.querySelectorAll('#hero-slides-container > div');
        const updatedSlides = [];

        slides.forEach((slide, index) => {
            const slideId = slide.dataset.id;
            const newOrder = index + 1;

            slide.dataset.order = newOrder;
            slide.querySelector('.absolute.top-2.right-2 span').textContent = newOrder;

            updatedSlides.push({
                id: slideId,
                order: newOrder
            });
        });

        // Save the updated order to the server
        saveHeroSlidesOrder(updatedSlides);
    }

    function saveHeroSlidesOrder(updatedSlides) {
        showLoading('Saving slide order...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'heroSlidesOrder',
                content: updatedSlides
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Slide order updated successfully');
                } else {
                    showErrorNotification('Error updating slide order: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating slide order: ' + error.message);
            });
    }

    function updateBenefitsOrder() {
        const benefits = document.querySelectorAll('#benefits-container > div');
        const updatedBenefits = [];

        benefits.forEach((benefit, index) => {
            const benefitId = benefit.dataset.id;
            const newOrder = index + 1;

            benefit.dataset.order = newOrder;
            benefit.querySelector('.bg-gray-100.text-gray-700.rounded-full.h-6.w-6').textContent = newOrder;

            updatedBenefits.push({
                id: benefitId,
                order: newOrder
            });
        });

        // Save the updated order to the server
        saveBenefitsOrder(updatedBenefits);
    }

    function saveBenefitsOrder(updatedBenefits) {
        showLoading('Saving benefits order...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'keyFeaturesOrder',
                content: updatedBenefits
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Benefits order updated successfully');
                } else {
                    showErrorNotification('Error updating benefits order: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating benefits order: ' + error.message);
            });
    }

    function updatePartnersOrder() {
        const partners = document.querySelectorAll('#partners-container > div');
        const updatedPartners = [];

        partners.forEach((partner, index) => {
            const partnerId = partner.dataset.id;
            const newOrder = index + 1;

            partner.dataset.order = newOrder;
            partner.querySelector('.absolute.top-2.right-2 span').textContent = newOrder;

            updatedPartners.push({
                id: partnerId,
                order: newOrder
            });
        });

        // Save the updated order to the server
        savePartnersOrder(updatedPartners);
    }

    function savePartnersOrder(updatedPartners) {
        showLoading('Saving partners order...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'partnersOrder',
                content: updatedPartners
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Partners order updated successfully');
                } else {
                    showErrorNotification('Error updating partners order: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating partners order: ' + error.message);
            });
    }

    function initializeStatusToggles() {
        const heroStatusToggle = document.getElementById('hero-status-toggle');
        if (heroStatusToggle) {
            heroStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('hero-status-text');
                const statusInput = document.getElementById('hero-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const benefitStatusToggle = document.getElementById('benefit-status-toggle');
        if (benefitStatusToggle) {
            benefitStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('benefit-status-text');
                const statusInput = document.getElementById('benefit-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const partnerStatusToggle = document.getElementById('partner-status-toggle');
        if (partnerStatusToggle) {
            partnerStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('partner-status-text');
                const statusInput = document.getElementById('partner-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const quoteStatusToggle = document.getElementById('quote-status-toggle');
        if (quoteStatusToggle) {
            quoteStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('quote-status-text');
                const statusInput = document.getElementById('quote-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const productsStatusToggle = document.getElementById('products-status-toggle');
        if (productsStatusToggle) {
            productsStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('products-status-text');
                const statusInput = document.getElementById('products-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const categoriesStatusToggle = document.getElementById('categories-status-toggle');
        if (categoriesStatusToggle) {
            categoriesStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('categories-status-text');
                const statusInput = document.getElementById('categories-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        const partnersStatusToggle = document.getElementById('partners-status-toggle');
        if (partnersStatusToggle) {
            partnersStatusToggle.addEventListener('change', function () {
                const statusText = document.getElementById('partners-status-text');
                const statusInput = document.getElementById('partners-status');

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusInput.value = 'active';
                } else {
                    statusText.textContent = 'Inactive';
                    statusInput.value = 'inactive';
                }
            });
        }

        document.querySelectorAll('.hero-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const slideId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updateHeroSlideStatus(slideId, newStatus);
            });
        });

        document.querySelectorAll('.benefit-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const benefitId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updateBenefitStatus(benefitId, newStatus);
            });
        });

        document.querySelectorAll('.partner-status-toggle').forEach(toggle => {
            toggle.addEventListener('change', function () {
                const partnerId = this.getAttribute('data-id');
                const newStatus = this.checked ? 'active' : 'inactive';
                const statusText = this.parentElement.nextElementSibling;

                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-gray-500');
                    statusText.classList.add('text-green-600');
                } else {
                    statusText.textContent = 'Inactive';
                    statusText.classList.remove('text-green-600');
                    statusText.classList.add('text-gray-500');
                }

                updatePartnerStatus(partnerId, newStatus);
            });
        });
    }

    function updateHeroSlideStatus(slideId, status) {
        showLoading('Updating slide status...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'heroSlideStatus',
                content: {
                    id: slideId,
                    active: status === 'active'
                }
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Slide status updated successfully');
                } else {
                    showErrorNotification('Error updating slide status: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating slide status: ' + error.message);
            });
    }

    function updateBenefitStatus(benefitId, status) {
        showLoading('Updating benefit status...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'keyFeatureStatus',
                content: {
                    id: benefitId,
                    active: status === 'active'
                }
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Benefit status updated successfully');
                } else {
                    showErrorNotification('Error updating benefit status: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating benefit status: ' + error.message);
            });
    }

    function updatePartnerStatus(partnerId, status) {
        showLoading('Updating partner status...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'partnerStatus',
                content: {
                    id: partnerId,
                    active: status === 'active'
                }
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Partner status updated successfully');
                } else {
                    showErrorNotification('Error updating partner status: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error updating partner status: ' + error.message);
            });
    }

    function showHeroModal(slideId = null) {
        resetHeroForm();

        if (slideId) {
            document.getElementById('heroModalTitle').textContent = 'Edit Hero Slide';
            document.getElementById('submitHero').textContent = 'Update Slide';
            document.getElementById('heroId').value = slideId;

            showLoading('Loading slide data...');

            fetch('fetch/manageHomepage.php?action=get_hero_slide&id=' + slideId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.slide) {
                        const slide = data.slide;

                        document.getElementById('heroTitle').value = slide.title || '';
                        document.getElementById('heroSubtitle').value = slide.subtitle || '';
                        document.getElementById('heroButtonText').value = slide.buttonText || '';
                        document.getElementById('heroButtonUrl').value = slide.buttonUrl || '';
                        document.getElementById('hero-status-toggle').checked = slide.active;
                        document.getElementById('hero-status').value = slide.active ? 'active' : 'inactive';
                        document.getElementById('hero-status-text').textContent = slide.active ? 'Active' : 'Inactive';

                        if (slide.image) {
                            document.getElementById('heroImagePreview').src = BASE_URL + slide.image;
                            document.getElementById('heroImagePreviewContainer').classList.remove('hidden');
                            document.getElementById('heroSelectedFileName').textContent = 'Current image';
                        }
                    } else {
                        showErrorNotification('Error loading slide data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading slide data: ' + error.message);
                });
        } else {
            document.getElementById('heroModalTitle').textContent = 'Add New Slide';
            document.getElementById('submitHero').textContent = 'Save Slide';
        }

        document.getElementById('heroModal').classList.remove('hidden');
    }

    function hideHeroModal() {
        document.getElementById('heroModal').classList.add('hidden');
        resetHeroForm();
    }

    function resetHeroForm() {
        document.getElementById('heroForm').reset();
        document.getElementById('heroId').value = '';
        document.getElementById('heroTempImagePath').value = '';
        document.getElementById('heroRemoveImage').value = '0';
        document.getElementById('heroImagePreviewContainer').classList.add('hidden');
        document.getElementById('heroCropperContainer').classList.add('hidden');
        document.getElementById('heroSelectedFileName').textContent = 'No file selected';

        document.getElementById('hero-status-toggle').checked = true;
        document.getElementById('hero-status-text').textContent = 'Active';
        document.getElementById('hero-status').value = 'active';

        if (heroCropper) {
            heroCropper.destroy();
            heroCropper = null;
        }
    }

    function handleHeroImageUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const selectedFileName = document.getElementById('heroSelectedFileName');
        selectedFileName.textContent = file.name;

        document.getElementById('heroRemoveImage').value = "0";

        const fileType = file.type;
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!validTypes.includes(fileType)) {
            showErrorNotification('Invalid file type. Only JPG, PNG, WebP, and GIF files are allowed.');
            resetHeroImageUpload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showErrorNotification('File size too large. Maximum 5MB allowed.');
            resetHeroImageUpload();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const cropperImage = document.getElementById('heroCropperImage');
            const cropperContainer = document.getElementById('heroCropperContainer');
            const imagePreviewContainer = document.getElementById('heroImagePreviewContainer');

            cropperImage.src = e.target.result;
            cropperContainer.classList.remove('hidden');
            imagePreviewContainer.classList.add('hidden');

            if (heroCropper) {
                heroCropper.destroy();
            }

            heroCropper = new Cropper(cropperImage, {
                aspectRatio: 3 / 1,
                viewMode: 1,
                autoCropArea: 1,
                zoomable: false,
                background: false,
                responsive: true,
                checkOrientation: true
            });
        };

        reader.readAsDataURL(file);
    }

    function cancelHeroCrop() {
        if (heroCropper) {
            heroCropper.destroy();
            heroCropper = null;
        }

        document.getElementById('heroCropperContainer').classList.add('hidden');
        document.getElementById('heroImage').value = '';
        document.getElementById('heroSelectedFileName').textContent = 'No file selected';
    }

    function applyHeroCrop() {
        if (!heroCropper) return;

        const canvas = heroCropper.getCroppedCanvas({
            width: 1800,
            height: 600,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            showErrorNotification('Failed to crop image');
            return;
        }

        const imagePreview = document.getElementById('heroImagePreview');
        const imagePreviewContainer = document.getElementById('heroImagePreviewContainer');
        const cropperContainer = document.getElementById('heroCropperContainer');

        imagePreview.src = canvas.toDataURL();
        imagePreviewContainer.classList.remove('hidden');
        cropperContainer.classList.add('hidden');

        canvas.toBlob(function (blob) {
            uploadHeroCroppedImage(blob);
        }, 'image/jpeg', 0.9);

        heroCropper.destroy();
        heroCropper = null;
    }

    function uploadHeroCroppedImage(blob) {
        const formData = new FormData();
        formData.append('action', 'upload_page_image');
        formData.append('page', 'homepage');
        formData.append('section', 'hero');
        formData.append('image', blob, 'hero-image-' + Date.now() + '.jpg');

        const heroId = document.getElementById('heroId').value;
        if (heroId) {
            formData.append('id', heroId);
        } else {
            formData.append('id', Date.now().toString());
        }

        const uploadProgress = document.getElementById('heroUploadProgress');
        const uploadProgressBar = document.getElementById('heroUploadProgressBar');

        uploadProgress.classList.remove('hidden');
        showLoading('Uploading image...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                uploadProgress.classList.add('hidden');
                hideLoading();

                if (data.success) {
                    document.getElementById('heroTempImagePath').value = data.imagePath;
                    document.getElementById('heroRemoveImage').value = "0";
                    showSuccessNotification('Image uploaded successfully');
                } else {
                    showErrorNotification('Error uploading image: ' + data.message);
                }
            })
            .catch(error => {
                uploadProgress.classList.add('hidden');
                hideLoading();
                showErrorNotification('Error uploading image: ' + error.message);
            });
    }

    function resetHeroImageUpload() {
        document.getElementById('heroImage').value = '';
        document.getElementById('heroSelectedFileName').textContent = 'No file selected';
        document.getElementById('heroImagePreviewContainer').classList.add('hidden');
        document.getElementById('heroCropperContainer').classList.add('hidden');
        document.getElementById('heroTempImagePath').value = '';
        document.getElementById('heroRemoveImage').value = "0";

        if (heroCropper) {
            heroCropper.destroy();
            heroCropper = null;
        }
    }

    function submitHeroForm() {
        const title = document.getElementById('heroTitle').value.trim();

        if (!title) {
            showErrorNotification('Title is required');
            return;
        }

        showLoading('Saving hero slide...');

        const heroId = document.getElementById('heroId').value || Date.now().toString();
        const formData = {
            id: heroId,
            title: document.getElementById('heroTitle').value,
            subtitle: document.getElementById('heroSubtitle').value,
            buttonText: document.getElementById('heroButtonText').value,
            buttonUrl: document.getElementById('heroButtonUrl').value,
            active: document.getElementById('hero-status-toggle').checked,
            order: document.getElementById('heroId').value ? parseInt(document.querySelector(`[data-id="${heroId}"]`)?.dataset.order || 1) : document.querySelectorAll('#hero-slides-container > div').length + 1,
            image: document.getElementById('heroTempImagePath').value || null,
            removeImage: document.getElementById('heroRemoveImage').value === "1"
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'heroSlide',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Hero slide saved successfully');
                    hideHeroModal();

                    // Reload the page to see the changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showErrorNotification('Error saving hero slide: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving hero slide: ' + error.message);
            });
    }

    function showBenefitModal(benefitId = null) {
        resetBenefitForm();

        if (benefitId) {
            document.getElementById('benefitModalTitle').textContent = 'Edit Benefit';
            document.getElementById('submitBenefit').textContent = 'Update Benefit';
            document.getElementById('benefitId').value = benefitId;

            showLoading('Loading benefit data...');

            fetch('fetch/manageHomepage.php?action=get_benefit&id=' + benefitId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.benefit) {
                        const benefit = data.benefit;

                        document.getElementById('benefitIcon').value = benefit.icon || '';
                        document.getElementById('benefitTitle').value = benefit.title || '';
                        document.getElementById('benefitDescription').value = benefit.description || '';
                        document.getElementById('benefit-status-toggle').checked = benefit.active;
                        document.getElementById('benefit-status').value = benefit.active ? 'active' : 'inactive';
                        document.getElementById('benefit-status-text').textContent = benefit.active ? 'Active' : 'Inactive';
                    } else {
                        showErrorNotification('Error loading benefit data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading benefit data: ' + error.message);
                });
        } else {
            document.getElementById('benefitModalTitle').textContent = 'Add New Benefit';
            document.getElementById('submitBenefit').textContent = 'Save Benefit';
        }

        document.getElementById('benefitModal').classList.remove('hidden');
    }

    function hideBenefitModal() {
        document.getElementById('benefitModal').classList.add('hidden');
        resetBenefitForm();
    }

    function resetBenefitForm() {
        document.getElementById('benefitForm').reset();
        document.getElementById('benefitId').value = '';

        document.getElementById('benefit-status-toggle').checked = true;
        document.getElementById('benefit-status-text').textContent = 'Active';
        document.getElementById('benefit-status').value = 'active';
    }

    function submitBenefitForm() {
        const icon = document.getElementById('benefitIcon').value.trim();
        const title = document.getElementById('benefitTitle').value.trim();

        if (!icon) {
            showErrorNotification('Icon is required');
            return;
        }

        if (!title) {
            showErrorNotification('Title is required');
            return;
        }

        showLoading('Saving benefit...');

        const benefitId = document.getElementById('benefitId').value || Date.now().toString();
        const formData = {
            id: benefitId,
            icon: document.getElementById('benefitIcon').value,
            title: document.getElementById('benefitTitle').value,
            description: document.getElementById('benefitDescription').value,
            active: document.getElementById('benefit-status-toggle').checked,
            order: document.getElementById('benefitId').value ? parseInt(document.querySelector(`[data-id="${benefitId}"]`)?.dataset.order || 1) : document.querySelectorAll('#benefits-container > div').length + 1
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'keyFeature',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Benefit saved successfully');
                    hideBenefitModal();

                    // Reload the page to see the changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showErrorNotification('Error saving benefit: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving benefit: ' + error.message);
            });
    }

    function showPartnerModal(partnerId = null) {
        if (typeof partnerId === 'function') {
            partnerId = null;
        }

        resetPartnerForm();

        if (partnerId) {
            document.getElementById('partnerModalTitle').textContent = 'Edit Partner';
            document.getElementById('submitPartner').textContent = 'Update Partner';
            document.getElementById('partnerId').value = partnerId;

            showLoading('Loading partner data...');

            fetch('fetch/manageHomepage.php?action=get_partner&id=' + partnerId)
                .then(response => response.json())
                .then(data => {
                    hideLoading();

                    if (data.success && data.partner) {
                        const partner = data.partner;

                        document.getElementById('partnerName').value = partner.name || '';
                        document.getElementById('partner-has-link').checked = partner.hasLink;
                        toggleRedirectLink();

                        if (partner.hasLink) {
                            document.getElementById('partnerRedirectLink').value = partner.redirectLink || '';
                        }

                        document.getElementById('partner-status-toggle').checked = partner.active;
                        document.getElementById('partner-status').value = partner.active ? 'active' : 'inactive';
                        document.getElementById('partner-status-text').textContent = partner.active ? 'Active' : 'Inactive';

                        if (partner.logo) {
                            document.getElementById('partnerImagePreview').src = BASE_URL + partner.logo;
                            document.getElementById('partnerImagePreviewContainer').classList.remove('hidden');
                            document.getElementById('partnerSelectedFileName').textContent = 'Current logo';
                        }
                    } else {
                        showErrorNotification('Error loading partner data: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    hideLoading();
                    showErrorNotification('Error loading partner data: ' + error.message);
                });
        } else {
            document.getElementById('partnerModalTitle').textContent = 'Add New Partner';
            document.getElementById('submitPartner').textContent = 'Save Partner';
        }

        document.getElementById('partnerModal').classList.remove('hidden');
    }

    function hidePartnerModal() {
        document.getElementById('partnerModal').classList.add('hidden');
        resetPartnerForm();
    }

    function resetPartnerForm() {
        document.getElementById('partnerForm').reset();
        document.getElementById('partnerId').value = '';
        document.getElementById('partnerTempImagePath').value = '';
        document.getElementById('partnerRemoveImage').value = '0';
        document.getElementById('partnerImagePreviewContainer').classList.add('hidden');
        document.getElementById('partnerCropperContainer').classList.add('hidden');
        document.getElementById('partnerSelectedFileName').textContent = 'No file selected';
        document.getElementById('redirect-link-container').classList.add('hidden');

        document.getElementById('partner-status-toggle').checked = true;
        document.getElementById('partner-status-text').textContent = 'Active';
        document.getElementById('partner-status').value = 'active';

        if (partnerCropper) {
            partnerCropper.destroy();
            partnerCropper = null;
        }
    }

    function toggleRedirectLink() {
        const hasLink = document.getElementById('partner-has-link').checked;
        const redirectLinkContainer = document.getElementById('redirect-link-container');

        if (hasLink) {
            redirectLinkContainer.classList.remove('hidden');
        } else {
            redirectLinkContainer.classList.add('hidden');
        }
    }

    function handlePartnerLogoUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const selectedFileName = document.getElementById('partnerSelectedFileName');
        selectedFileName.textContent = file.name;

        document.getElementById('partnerRemoveImage').value = "0";

        const fileType = file.type;
        const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!validTypes.includes(fileType)) {
            showErrorNotification('Invalid file type. Only JPG, PNG, WebP, and GIF files are allowed.');
            resetPartnerLogoUpload();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showErrorNotification('File size too large. Maximum 5MB allowed.');
            resetPartnerLogoUpload();
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const cropperImage = document.getElementById('partnerCropperImage');
            const cropperContainer = document.getElementById('partnerCropperContainer');
            const imagePreviewContainer = document.getElementById('partnerImagePreviewContainer');

            cropperImage.src = e.target.result;
            cropperContainer.classList.remove('hidden');
            imagePreviewContainer.classList.add('hidden');

            if (partnerCropper) {
                partnerCropper.destroy();
            }

            const logoRatio = document.querySelector('input[name="logo-ratio"]:checked').value;
            const aspectRatio = logoRatio === '2:1' ? 2 / 1 : 1 / 1;

            const cropperWrapper = document.getElementById('partnerCropperWrapper');
            cropperWrapper.style.aspectRatio = logoRatio === '2:1' ? '2/1' : '1/1';

            partnerCropper = new Cropper(cropperImage, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                autoCropArea: 1,
                zoomable: false,
                background: false,
                responsive: true,
                checkOrientation: true
            });
        };

        reader.readAsDataURL(file);
    }

    function cancelPartnerCrop() {
        if (partnerCropper) {
            partnerCropper.destroy();
            partnerCropper = null;
        }

        document.getElementById('partnerCropperContainer').classList.add('hidden');
        document.getElementById('partnerLogo').value = '';
        document.getElementById('partnerSelectedFileName').textContent = 'No file selected';
    }

    function applyPartnerCrop() {
        if (!partnerCropper) return;

        const logoRatio = document.querySelector('input[name="logo-ratio"]:checked').value;
        const width = logoRatio === '2:1' ? 200 : 100;
        const height = 100;

        const canvas = partnerCropper.getCroppedCanvas({
            width: width,
            height: height,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high'
        });

        if (!canvas) {
            showErrorNotification('Failed to crop logo');
            return;
        }

        const imagePreview = document.getElementById('partnerImagePreview');
        const imagePreviewContainer = document.getElementById('partnerImagePreviewContainer');
        const cropperContainer = document.getElementById('partnerCropperContainer');
        const previewWrapper = document.getElementById('partnerPreviewWrapper');

        previewWrapper.style.aspectRatio = logoRatio === '2:1' ? '2/1' : '1/1';

        imagePreview.src = canvas.toDataURL();
        imagePreviewContainer.classList.remove('hidden');
        cropperContainer.classList.add('hidden');

        canvas.toBlob(function (blob) {
            uploadPartnerCroppedLogo(blob);
        }, 'image/jpeg', 0.9);

        partnerCropper.destroy();
        partnerCropper = null;
    }

    function uploadPartnerCroppedLogo(blob) {
        const formData = new FormData();
        formData.append('action', 'upload_page_image');
        formData.append('page', 'homepage');
        formData.append('section', 'partner');

        const partnerName = document.getElementById('partnerName').value.trim();
        const fileName = partnerName ? partnerName.toLowerCase().replace(/\s+/g, '-') : 'partner-' + Date.now();

        formData.append('image', blob, fileName + '.jpg');

        const partnerId = document.getElementById('partnerId').value;
        if (partnerId) {
            formData.append('id', partnerId);
        } else {
            formData.append('id', Date.now().toString());
        }

        formData.append('name', partnerName);

        const uploadProgress = document.getElementById('partnerUploadProgress');
        const uploadProgressBar = document.getElementById('partnerUploadProgressBar');

        uploadProgress.classList.remove('hidden');
        showLoading('Uploading logo...');

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                uploadProgress.classList.add('hidden');
                hideLoading();

                if (data.success) {
                    document.getElementById('partnerTempImagePath').value = data.imagePath;
                    document.getElementById('partnerRemoveImage').value = "0";
                    showSuccessNotification('Logo uploaded successfully');
                } else {
                    showErrorNotification('Error uploading logo: ' + data.message);
                }
            })
            .catch(error => {
                uploadProgress.classList.add('hidden');
                hideLoading();
                showErrorNotification('Error uploading logo: ' + error.message);
            });
    }

    function resetPartnerLogoUpload() {
        document.getElementById('partnerLogo').value = '';
        document.getElementById('partnerSelectedFileName').textContent = 'No file selected';
        document.getElementById('partnerImagePreviewContainer').classList.add('hidden');
        document.getElementById('partnerCropperContainer').classList.add('hidden');
        document.getElementById('partnerTempImagePath').value = '';
        document.getElementById('partnerRemoveImage').value = "0";

        if (partnerCropper) {
            partnerCropper.destroy();
            partnerCropper = null;
        }
    }

    function submitPartnerForm() {
        const name = document.getElementById('partnerName').value.trim();

        if (!name) {
            showErrorNotification('Partner name is required');
            return;
        }

        const hasLink = document.getElementById('partner-has-link').checked;
        if (hasLink) {
            const redirectLink = document.getElementById('partnerRedirectLink').value.trim();
            if (!redirectLink) {
                showErrorNotification('Redirect link is required when "Has Redirect Link" is checked');
                return;
            }
        }

        showLoading('Saving partner...');

        const partnerId = document.getElementById('partnerId').value || Date.now().toString();
        const formData = {
            id: partnerId,
            name: name,
            hasLink: hasLink,
            redirectLink: hasLink ? document.getElementById('partnerRedirectLink').value : '',
            active: document.getElementById('partner-status-toggle').checked,
            order: document.getElementById('partnerId').value ? parseInt(document.querySelector(`[data-id="${partnerId}"]`)?.dataset.order || 1) : document.querySelectorAll('#partners-container > div').length + 1,
            logo: document.getElementById('partnerTempImagePath').value || null,
            removeImage: document.getElementById('partnerRemoveImage').value === "1"
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'partner',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Partner saved successfully');
                    hidePartnerModal();

                    // Reload the page to see the changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showErrorNotification('Error saving partner: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving partner: ' + error.message);
            });
    }

    function submitQuoteForm() {
        const buttonText = document.getElementById('button-text').value.trim();
        const buttonUrl = document.getElementById('button-url').value.trim();

        if (!buttonText) {
            showErrorNotification('Button text is required');
            return;
        }

        if (!buttonUrl) {
            showErrorNotification('Button URL is required');
            return;
        }

        showLoading('Saving quote section...');

        const formData = {
            buttonText: buttonText,
            buttonUrl: buttonUrl,
            description: document.getElementById('description').value.trim(),
            active: document.getElementById('quote-status-toggle').checked
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'requestQuoteSection',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Quote section saved successfully');
                } else {
                    showErrorNotification('Error saving quote section: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving quote section: ' + error.message);
            });
    }

    function submitProductsForm() {
        const title = document.getElementById('products-title').value.trim();

        if (!title) {
            showErrorNotification('Section title is required');
            return;
        }

        showLoading('Saving products section...');

        const formData = {
            title: title,
            linkText: document.getElementById('products-link-text').value.trim(),
            linkUrl: document.getElementById('products-link-url').value.trim(),
            loadMoreButtonText: document.getElementById('products-button-text').value.trim(),
            defaultRows: parseInt(document.getElementById('products-default-rows').value) || 1,
            productsPerRow: parseInt(document.getElementById('products-per-row').value) || 4,
            active: document.getElementById('products-status-toggle').checked
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'featuredProductsSection',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Products section saved successfully');
                } else {
                    showErrorNotification('Error saving products section: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving products section: ' + error.message);
            });
    }

    function submitCategoriesForm() {
        const title = document.getElementById('categories-title').value.trim();

        if (!title) {
            showErrorNotification('Section title is required');
            return;
        }

        showLoading('Saving categories section...');

        const formData = {
            title: title,
            linkText: document.getElementById('categories-link-text').value.trim(),
            linkUrl: document.getElementById('categories-link-url').value.trim(),
            loadMoreButtonText: document.getElementById('categories-button-text').value.trim(),
            defaultRows: parseInt(document.getElementById('categories-default-rows').value) || 1,
            categoriesPerRow: parseInt(document.getElementById('categories-per-row').value) || 4,
            active: document.getElementById('categories-status-toggle').checked
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'categoriesSection',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Categories section saved successfully');
                } else {
                    showErrorNotification('Error saving categories section: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving categories section: ' + error.message);
            });
    }

    function submitPartnersSettingsForm() {
        const title = document.getElementById('partners-title').value.trim();

        if (!title) {
            showErrorNotification('Section title is required');
            return;
        }

        showLoading('Saving partners section settings...');

        const formData = {
            title: title,
            ctaButtonText: document.getElementById('partners-cta-text').value.trim(),
            ctaButtonUrl: document.getElementById('partners-cta-url').value.trim(),
            description: document.getElementById('partners-description').value.trim(),
            active: document.getElementById('partners-status-toggle').checked
        };

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: 'partnersSection',
                content: formData
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification('Partners section settings saved successfully');
                } else {
                    showErrorNotification('Error saving partners section settings: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification('Error saving partners section settings: ' + error.message);
            });
    }

    function showDeleteModal(type, id) {
        deleteItemType = type;
        deleteItemId = id;

        let itemName = '';
        let itemStatus = '';
        let typeLabel = '';

        if (type === 'hero') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Hero Slide';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this hero slide? This action cannot be undone.';
            typeLabel = 'Slide:';

            const slideElement = document.querySelector(`#hero-slides-container [data-id="${id}"]`);
            if (slideElement) {
                itemName = slideElement.querySelector('.text-white.text-sm.font-bold').textContent;
                itemStatus = slideElement.querySelector('.hero-status-toggle').checked ? 'Active' : 'Inactive';
            }
        } else if (type === 'benefit') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Benefit';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this benefit? This action cannot be undone.';
            typeLabel = 'Benefit:';

            const benefitElement = document.querySelector(`#benefits-container [data-id="${id}"]`);
            if (benefitElement) {
                itemName = benefitElement.querySelector('.text-xl.font-semibold').textContent;
                itemStatus = benefitElement.querySelector('.benefit-status-toggle').checked ? 'Active' : 'Inactive';
            }
        } else if (type === 'partner') {
            document.getElementById('deleteModalTitle').textContent = 'Delete Partner';
            document.getElementById('deleteModalMessage').textContent = 'Are you sure you want to delete this partner? This action cannot be undone.';
            typeLabel = 'Partner:';

            const partnerElement = document.querySelector(`#partners-container [data-id="${id}"]`);
            if (partnerElement) {
                itemName = partnerElement.querySelector('.font-medium.text-gray-900').textContent;
                itemStatus = partnerElement.querySelector('.partner-status-toggle').checked ? 'Active' : 'Inactive';
            }
        }

        document.getElementById('deleteItemTypeLabel').textContent = typeLabel;
        document.getElementById('deleteItemName').textContent = itemName;
        document.getElementById('deleteItemStatus').textContent = itemStatus;

        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteItemType = '';
        deleteItemId = '';
    }

    function confirmDelete() {
        if (!deleteItemType || !deleteItemId) {
            hideDeleteModal();
            return;
        }

        showLoading(`Deleting ${deleteItemType}...`);

        let section = '';
        if (deleteItemType === 'hero') {
            section = 'heroSlide';
        } else if (deleteItemType === 'benefit') {
            section = 'keyFeature';
        } else if (deleteItemType === 'partner') {
            section = 'partner';
        }

        fetch('fetch/manageHomepage.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_section_content',
                page: 'homepage',
                section: section + 'Delete',
                content: {
                    id: deleteItemId
                }
            })
        })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    showSuccessNotification(`${capitalizeFirstLetter(deleteItemType)} deleted successfully`);
                    hideDeleteModal();

                    // Reload the page to see the changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showErrorNotification(`Error deleting ${deleteItemType}: ` + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                showErrorNotification(`Error deleting ${deleteItemType}: ` + error.message);
            });
    }

    function showSessionExpiredModal() {
        const modal = document.getElementById('sessionExpiredModal');
        modal.classList.remove('hidden');

        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        countdownElement.textContent = countdown;

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                redirectToLogin();
            }
        }, 1000);
    }

    function redirectToLogin() {
        window.location.href = BASE_URL;
    }

    function showSuccessNotification(message) {
        const notification = document.getElementById('successNotification');
        const messageEl = document.getElementById('successMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function showErrorNotification(message) {
        const notification = document.getElementById('errorNotification');
        const messageEl = document.getElementById('errorMessage');

        messageEl.textContent = message;
        notification.classList.remove('hidden');

        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    document.querySelectorAll('input[name="logo-ratio"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (partnerCropper) {
                const aspectRatio = this.value === '2:1' ? 2 / 1 : 1 / 1;
                partnerCropper.setAspectRatio(aspectRatio);

                const cropperWrapper = document.getElementById('partnerCropperWrapper');
                cropperWrapper.style.aspectRatio = this.value === '2:1' ? '2/1' : '1/1';
            }
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>