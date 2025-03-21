<?php
$pageTitle = "Materials Yard";
$activeNav = "materials";
require_once __DIR__ . '/config/config.php';

// Define categories and products data in PHP
$categoriesData = [
    [
        'id' => 1,
        'name' => "Branded Steel Materials",
        'icon' => 'fas fa-hammer',
        'products' => [
            [
                'name' => "Uganda Baati Binding wire",
                'views' => 67,
                'price' => 75000,
                'unit' => "Per Roll",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Uganda+Wire"
            ],
            [
                'name' => "Steel and Tube Ring bars",
                'views' => 51,
                'price' => 25000,
                'unit' => "Per Rebar",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Steel+Ring"
            ],
            [
                'name' => "SMILE Hoop iron Bundle",
                'views' => 94,
                'price' => 145000,
                'unit' => "Per Bundle",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Hoop+Iron"
            ]
        ]
    ],
    [
        'id' => 2,
        'name' => "Building Glass Materials",
        'icon' => 'fas fa-glass-martini-alt',
        'products' => [
            [
                'name' => "GENERIC Blue One way Glass",
                'views' => 94,
                'price' => 175000,
                'unit' => "Per Sheet",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Blue+Glass"
            ],
            [
                'name' => "GENERIC one way Brown Glass",
                'views' => 69,
                'price' => 120000,
                'unit' => "Per Sheet",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Brown+Glass"
            ],
            [
                'name' => "Perfect Putty glass seal",
                'views' => 41,
                'price' => 80000,
                'unit' => "Per Can",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Putty+Seal"
            ]
        ]
    ],
    [
        'id' => 3,
        'name' => "Utility Supplies",
        'icon' => 'fas fa-tools',
        'products' => [
            [
                'name' => "Water Hose Pipe",
                'views' => 23,
                'price' => 45000,
                'unit' => "Per Roll",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Hose+Pipe"
            ],
            [
                'name' => "Generic PVC Pipes",
                'views' => 12,
                'price' => 55000,
                'unit' => "Per Bundle",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=PVC+Pipes"
            ],
            [
                'name' => "Dr Fixit Liquid",
                'views' => 30,
                'price' => 60000,
                'unit' => "Per Container",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Fixit+Liquid"
            ]
        ]
    ],
    [
        'id' => 4,
        'name' => "Construction Tools",
        'icon' => 'fas fa-wrench',
        'products' => [
            [
                'name' => "Ladder 10ft",
                'views' => 65,
                'price' => 85000,
                'unit' => "Per Piece",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Ladder"
            ],
            [
                'name' => "Hammer Heavy Duty",
                'views' => 40,
                'price' => 25000,
                'unit' => "Each",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Hammer"
            ],
            [
                'name' => "Wheelbarrow",
                'views' => 55,
                'price' => 78000,
                'unit' => "Each",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Wheelbarrow"
            ]
        ]
    ],
    [
        'id' => 5,
        'name' => "Plumbing Implements",
        'icon' => 'fas fa-faucet',
        'products' => [
            [
                'name' => "Copper Taps",
                'views' => 22,
                'price' => 40000,
                'unit' => "Each",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Copper+Taps"
            ],
            [
                'name' => "Drain Pipe Filter",
                'views' => 10,
                'price' => 15000,
                'unit' => "Each",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Drain+Filter"
            ],
            [
                'name' => "Stop Valve",
                'views' => 38,
                'price' => 29000,
                'unit' => "Each",
                'image' => "https://dummyimage.com/200x150/e3e3e3/ffffff&text=Stop+Valve"
            ]
        ]
    ]
];

// Calculate total products
$totalProducts = 0;
foreach ($categoriesData as $category) {
    $totalProducts += count($category['products']);
}

ob_start();
?>

<style>
    .skeleton-item {
        background-color: #e2e8f0;
    }

    .skeleton-animate {
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }

        100% {
            opacity: 1;
        }
    }

    .product-card {
        transition: all 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .category-dropdown {
        display: none;
    }

    @media (max-width: 768px) {
        .category-sidebar {
            display: none;
        }

        .category-dropdown {
            display: block;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .category-item {
        border-left: 5px solid #e53e3e;
    }

    .category-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #f3f4f6;
        color: #374151;
        border-radius: 9999px;
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .category-badge:hover {
        background-color: #e53e3e;
        color: white;
    }

    .view-counter {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .category-sidebar-item {
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }

    .category-sidebar-item:hover,
    .category-sidebar-item.active {
        border-left-color: #e53e3e;
        background-color: #f9fafb;
    }
</style>

<div class="breadcrumb-container relative bg-cover bg-center" style="background-image: url('https://dummyimage.com/1920x350/e3e3e3/ffffff&text=Materials+Yard')">
    <div class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="container mx-auto px-4 py-10 relative z-10 text-white">
        <h1 class="text-4xl md:text-5xl font-bold">Building Materials</h1>
        <nav class="text-sm mt-4 space-x-2">
            <a href="<?= BASE_URL ?>" class="hover:underline text-gray-200">Zzimba Online</a>
            <span>/</span>
            <span class="font-semibold">Building Materials</span>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 py-12">
    <div class="flex flex-col md:flex-row md:space-x-8">
        <!-- Enhanced Category Sidebar -->
        <aside class="category-sidebar md:w-1/4 w-full mb-8 md:mb-0">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4">
                    <h2 class="text-xl font-bold">Product Categories</h2>
                    <p class="text-sm mt-1 text-white text-opacity-80">Browse our extensive collection</p>
                </div>
                <div class="p-4">
                    <div class="relative">
                        <input type="text" id="category-search" placeholder="Search categories..." class="w-full p-2 pl-8 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                <ul id="side-categories" class="divide-y">
                    <!-- Categories will be populated here -->
                </ul>
                <div class="p-4 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Total Products</span>
                        <span class="text-sm font-bold text-red-600"><?= $totalProducts ?> items</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Category Dropdown -->
        <div class="category-dropdown w-full mb-8">
            <select id="category-select" class="w-full p-3 border rounded-lg shadow-sm bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="all">All Categories</option>
                <!-- Options will be populated here -->
            </select>
        </div>

        <!-- Main Content Area -->
        <div class="md:w-3/4 w-full">
            <!-- Loading Skeleton -->
            <div id="loading-skeleton" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php for ($i = 0; $i < 6; $i++) : ?>
                    <div class="border p-4 rounded-lg shadow skeleton-item skeleton-animate h-80"></div>
                <?php endfor; ?>
            </div>

            <!-- Categories and Products -->
            <div id="category-list" class="space-y-12 hidden">
                <!-- Categories will be populated here -->
            </div>

            <!-- End of Content Message -->
            <div id="end-of-content" class="text-center mt-12 hidden">
                <div class="inline-block p-6 bg-gray-50 rounded-lg shadow-sm">
                    <i class="fas fa-check-circle text-green-500 text-3xl mb-3"></i>
                    <p class="text-gray-600 font-medium">You've seen all available categories</p>
                    <p class="text-sm text-gray-500 mt-1">Need something specific? Contact our support team</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Get categories data from PHP
    let categoriesData = <?= json_encode($categoriesData) ?>;
    let filteredCategories = [];
    let loadedCount = 0;
    let loadStep = 1;
    let activeCategory = 'all';

    function loadCategories() {
        let sideContainer = document.getElementById('side-categories');
        let dropdownContainer = document.getElementById('category-select');
        let totalProducts = <?= $totalProducts ?>;

        // Populate sidebar categories
        sideContainer.innerHTML = `
            <li class="category-sidebar-item p-4 cursor-pointer ${activeCategory === 'all' ? 'active' : ''}" onclick="filterCategory('all')">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-th-large mr-3 text-red-500"></i>
                        <span class="font-medium">All Categories</span>
                    </div>
                    <span class="category-badge">${totalProducts}</span>
                </div>
            </li>
            ${categoriesData.map(c => `
                <li class="category-sidebar-item p-4 cursor-pointer ${activeCategory == c.id ? 'active' : ''}" onclick="filterCategory(${c.id})">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="${c.icon} mr-3 text-red-500"></i>
                            <span class="font-medium">${c.name}</span>
                        </div>
                        <span class="category-badge">${c.products.length}</span>
                    </div>
                </li>
            `).join('')}
        `;

        // Populate dropdown for mobile
        dropdownContainer.innerHTML = `
            <option value="all">All Categories (${totalProducts})</option>
            ${categoriesData.map(c => `
                <option value="${c.id}">${c.name} (${c.products.length})</option>
            `).join('')}
        `;
    }

    function showSkeleton(show) {
        let skeleton = document.getElementById('loading-skeleton');
        let categoryList = document.getElementById('category-list');
        if (show) {
            skeleton.classList.remove('hidden');
            categoryList.classList.add('hidden');
        } else {
            skeleton.classList.add('hidden');
            categoryList.classList.remove('hidden');
        }
    }

    function renderCategories(startIndex, endIndex) {
        let container = document.getElementById('category-list');
        for (let i = startIndex; i < endIndex; i++) {
            if (i >= filteredCategories.length) break;
            let cat = filteredCategories[i];
            let catHTML = `
                <div class="bg-white rounded-xl shadow-lg overflow-hidden category-item fade-in">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 p-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <i class="${cat.icon} mr-3"></i>
                                ${cat.name}
                            </h2>
                            <p class="text-white text-opacity-80 mt-1">Premium quality materials</p>
                        </div>
                        <span class="bg-white text-red-600 px-4 py-1 rounded-full font-bold text-sm">
                            ${cat.products.length} Products
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                        ${cat.products.map(p => `
                            <div class="bg-white rounded-xl overflow-hidden product-card flex flex-col justify-between shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                <div class="relative">
                                    <img src="${p.image}" alt="${p.name}" class="w-full h-48 object-cover">
                                    <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT</div>
                                </div>
                                <div class="p-5">
                                    <h3 class="font-bold text-lg mb-2">${p.name}</h3>
                                    <div class="view-counter mb-3">
                                        <i class="fas fa-eye text-gray-400"></i>
                                        <span>${p.views} Views</span>
                                    </div>
                                    <p class="text-gray-700 mb-4">Price ${p.unit} - <span class="text-red-600 font-bold text-xl">UGX ${p.price.toLocaleString()}</span></p>
                                    <div class="flex space-x-2">
                                        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center flex-1 justify-center">
                                            <i class="fas fa-shopping-cart mr-1"></i> Buy
                                        </button>
                                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center flex-1 justify-center">
                                            <i class="fas fa-tag mr-1"></i> Sell
                                        </button>
                                    </div>
                                    <button class="mt-3 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm px-4 py-2 rounded-lg transition-colors duration-300 w-full flex items-center justify-center">
                                        <i class="fas fa-info-circle mr-1"></i> Product Details
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="bg-gray-50 p-6 text-center">
                        <button onclick="viewMoreInCategory(${cat.id})" class="bg-white border border-red-600 text-red-600 hover:bg-red-600 hover:text-white font-medium py-2 px-6 rounded-lg transition-colors duration-300 inline-flex items-center">
                            <span>View All in ${cat.name}</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            `;
            let wrapper = document.createElement('div');
            wrapper.innerHTML = catHTML;
            container.appendChild(wrapper);
        }
    }

    function loadMore() {
        if (loadedCount < filteredCategories.length) {
            showSkeleton(true);
            setTimeout(() => {
                let nextIndex = loadedCount + loadStep;
                renderCategories(loadedCount, nextIndex);
                loadedCount = nextIndex;
                showSkeleton(false);
                if (loadedCount >= filteredCategories.length) {
                    document.getElementById('end-of-content').classList.remove('hidden');
                }
            }, 1000);
        }
    }

    function filterCategory(categoryId) {
        activeCategory = categoryId;
        showSkeleton(true);
        document.getElementById('category-list').innerHTML = '';
        document.getElementById('end-of-content').classList.add('hidden');

        // Update active class in sidebar
        document.querySelectorAll('.category-sidebar-item').forEach(item => {
            item.classList.remove('active');
        });

        // Find the clicked item and add active class
        if (categoryId === 'all') {
            document.querySelector('.category-sidebar-item:first-child').classList.add('active');
            filteredCategories = [...categoriesData];
        } else {
            document.querySelector(`.category-sidebar-item:nth-child(${parseInt(categoryId) + 1})`).classList.add('active');
            filteredCategories = categoriesData.filter(cat => cat.id === parseInt(categoryId));
        }

        loadedCount = 0;
        setTimeout(() => {
            renderCategories(0, loadStep);
            loadedCount = loadStep;
            showSkeleton(false);
        }, 1000);
    }

    function viewMoreInCategory(categoryId) {
        filterCategory(categoryId);
        // Scroll to top of results
        document.getElementById('category-list').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function handleScroll() {
        let scrollTop = window.scrollY;
        let viewportHeight = window.innerHeight;
        let fullHeight = document.documentElement.scrollHeight;
        if (scrollTop + viewportHeight >= fullHeight - 100) {
            loadMore();
        }
    }

    // Search functionality for categories
    function setupCategorySearch() {
        const searchInput = document.getElementById('category-search');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const categoryItems = document.querySelectorAll('.category-sidebar-item');

            categoryItems.forEach(item => {
                const categoryName = item.querySelector('.font-medium').textContent.toLowerCase();
                if (categoryName.includes(searchTerm) || searchTerm === '') {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        filteredCategories = [...categoriesData];
        loadCategories();
        setupCategorySearch();
        showSkeleton(true);
        setTimeout(() => {
            renderCategories(0, 3); // Initially show 3 categories
            loadedCount = 3;
            showSkeleton(false);
            setTimeout(() => {
                loadMore(); // Load remaining categories after 6 seconds
            }, 6000);
        }, 1000);
        window.addEventListener('scroll', handleScroll);

        // Add event listener for dropdown
        document.getElementById('category-select').addEventListener('change', function() {
            filterCategory(this.value);
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>