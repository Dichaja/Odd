<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (isset($_GET['ajax']) && $_GET['ajax'] === 'data') {
    header('Content-Type: application/json');

    $products = $pdo->query("
        SELECT
            p.id,
            p.title,
            p.description,
            p.meta_title,
            p.meta_description,
            p.meta_keywords,
            p.category_id,
            c.name AS category_name
        FROM products p
        JOIN product_categories c ON c.id = p.category_id
        WHERE p.status = 'published'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query("
        SELECT
            id,
            name,
            description,
            meta_title,
            meta_description,
            meta_keywords
        FROM product_categories
        WHERE status = 'active'
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['products' => $products, 'categories' => $categories]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Search</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/fuse.js@6.6.2"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'rubik': ['Rubik', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fef2f0',
                            100: '#fde6e2',
                            200: '#fbd0c9',
                            300: '#f7b0a3',
                            400: '#f1836f',
                            500: '#e85c43',
                            600: '#d92b13',
                            700: '#b6230f',
                            800: '#952012',
                            900: '#7b2013',
                        },
                    },
                    animation: {
                        'pulse-fast': 'pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Rubik', sans-serif;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(217, 43, 19, 0.2);
        }

        .search-input::placeholder {
            color: #9ca3af;
        }

        .category-pill:hover {
            background-color: rgba(217, 43, 19, 0.1);
            color: #d92b13;
        }

        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .loader {
            border-top-color: #d92b13;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }

        .skeleton {
            background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Mobile product card styles */
        @media (max-width: 640px) {
            .product-card-content {
                display: flex;
            }

            .product-card-image {
                aspect-ratio: 1/1 !important;
                width: 120px;
                flex-shrink: 0;
            }

            .product-card-details {
                flex: 1;
                padding-left: 1rem;
                display: flex;
                flex-direction: column;
            }

            .product-card-actions {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .product-card-description {
                display: none;
            }

            .product-card-title {
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .header-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                width: 100%;
                margin: 0.5rem 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col font-rubik">
    <header class="bg-white shadow-md sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between header-container">
                <div class="flex-shrink-0">
                    <h1 class="text-2xl font-bold text-primary-600">Marketplace</h1>
                </div>
                <div class="w-full max-w-xl search-container">
                    <form id="searchForm" class="relative">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input id="searchInput" type="search" autocomplete="off"
                                placeholder="Search products or categories"
                                class="search-input w-full pl-10 pr-4 py-3 rounded-full border border-gray-200 focus:outline-none focus:border-primary-600 transition-colors" />
                            <div id="loader" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                                <div class="loader w-5 h-5 border-2 border-gray-200 rounded-full"></div>
                            </div>
                        </div>
                        <ul id="dropdown"
                            class="absolute left-0 right-0 mt-2 max-h-80 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg hidden z-20">
                        </ul>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div id="activeFilters" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 hidden">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-500">Active filters:</span>
            <div id="filterTags" class="flex flex-wrap gap-2"></div>
            <button id="clearFilters" class="text-sm text-primary-600 hover:text-primary-800 ml-2">Clear all</button>
        </div>
    </div>

    <div class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div id="initialState" class="text-center py-16">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h2 class="text-xl font-medium text-gray-600 mb-2">Search for products or categories</h2>
            <p class="text-gray-500 max-w-md mx-auto">Type in the search box above to find products and categories in
                our marketplace.</p>
        </div>

        <div id="resultsContainer" class="hidden">
            <div id="categoriesSection" class="mb-10 hidden"></div>

            <div id="productsHeader" class="mb-6 hidden">
                <h2 id="productsTitle" class="text-xl font-semibold text-gray-800"></h2>
            </div>

            <div id="skeletonGrid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 hidden"></div>

            <div id="productGrid" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 hidden"></div>
        </div>

        <div id="loadingMore" class="hidden py-8 text-center">
            <div class="loader mx-auto w-8 h-8 border-4 border-gray-200 rounded-full"></div>
            <p class="mt-4 text-gray-500">Loading more products...</p>
        </div>

        <div id="noResults" class="text-center py-16 hidden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-xl font-medium text-gray-600 mb-2">No results found</h2>
            <p class="text-gray-500 max-w-md mx-auto">We couldn't find any matches for your search. Try different
                keywords or check for typos.</p>
            <div id="suggestions" class="mt-6"></div>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Marketplace</h3>
                    <p class="text-gray-400 text-sm">Find the best products from verified sellers in our marketplace.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Categories</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Sellers</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Email: info@marketplace.com</li>
                        <li>Phone: +1 (555) 123-4567</li>
                        <li>Address: 123 Market St, City</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-sm text-gray-400">
                <p>&copy; 2023 Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function ld(a, b) {
            if (a === b) return 0;
            if (!a.length || !b.length) return Math.max(a.length, b.length);
            const v = Array(b.length + 1).fill(0).map((_, i) => i);
            for (let i = 0; i < a.length; i++) {
                let prev = i + 1;
                for (let j = 0; j < b.length; j++) {
                    const val = a[i] === b[j] ? v[j] : Math.min(v[j], v[j + 1], prev) + 1;
                    v[j] = prev; prev = val;
                }
                v[b.length] = prev;
            }
            return v[b.length];
        }

        let DATA = { products: [], categories: [] };
        let fuseProducts = null, fuseCategories = null, fuseWords = null;
        const resultsContainer = document.getElementById('resultsContainer');
        const categoriesSection = document.getElementById('categoriesSection');
        const productsHeader = document.getElementById('productsHeader');
        const productsTitle = document.getElementById('productsTitle');
        const skeletonGrid = document.getElementById('skeletonGrid');
        const productGrid = document.getElementById('productGrid');
        const dropdown = document.getElementById('dropdown');
        const searchInput = document.getElementById('searchInput');
        const initialState = document.getElementById('initialState');
        const noResults = document.getElementById('noResults');
        const loader = document.getElementById('loader');
        const activeFilters = document.getElementById('activeFilters');
        const filterTags = document.getElementById('filterTags');
        const clearFilters = document.getElementById('clearFilters');
        const suggestions = document.getElementById('suggestions');
        const loadingMore = document.getElementById('loadingMore');

        let activeCategory = null;
        let currentResults = [];
        let currentPage = 1;
        let isLoading = false;
        let hasMoreItems = true;
        let isMobile = window.innerWidth < 640;

        // Calculate items per row based on screen size
        function getItemsPerRow() {
            if (window.innerWidth >= 1280) return 4; // xl
            if (window.innerWidth >= 1024) return 3; // lg
            if (window.innerWidth >= 640) return 2; // sm
            return 1; // mobile
        }

        // Calculate items per page (3 rows)
        function getItemsPerPage() {
            return getItemsPerRow() * 3;
        }

        // Create product skeleton template that exactly matches the product card
        function getProductSkeletonTemplate() {
            if (isMobile) {
                return `
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="product-card-content">
                        <div class="product-card-image aspect-square bg-gray-100 skeleton"></div>
                        <div class="product-card-details p-4">
                            <div>
                                <div class="skeleton h-4 w-20 rounded mb-2"></div>
                                <div class="skeleton h-5 w-32 rounded"></div>
                            </div>
                            <div class="product-card-actions mt-auto">
                                <div class="skeleton h-5 w-16 rounded mb-2"></div>
                                <div class="skeleton h-8 w-24 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            } else {
                return `
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="product-card-content">
                        <div class="product-card-image aspect-video bg-gray-100 skeleton"></div>
                        <div class="product-card-details p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="skeleton h-5 w-32 rounded mb-2"></div>
                                    <div class="skeleton h-4 w-20 rounded"></div>
                                </div>
                                <div class="skeleton h-5 w-5 rounded-full"></div>
                            </div>
                            <div class="mt-3">
                                <div class="skeleton h-4 w-full rounded mb-1"></div>
                                <div class="skeleton h-4 w-3/4 rounded"></div>
                            </div>
                            <div class="product-card-actions mt-4 flex items-center justify-between">
                                <div class="skeleton h-5 w-16 rounded"></div>
                                <div class="skeleton h-8 w-24 rounded-lg"></div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }
        }

        // Generate multiple skeleton templates
        function generateSkeletons(count) {
            skeletonGrid.innerHTML = '';
            for (let i = 0; i < count; i++) {
                skeletonGrid.insertAdjacentHTML('beforeend', getProductSkeletonTemplate());
            }
        }

        // Show skeleton loader
        function showSkeletons() {
            const itemsPerPage = getItemsPerPage();
            generateSkeletons(itemsPerPage);
            skeletonGrid.classList.remove('hidden');
            productGrid.classList.add('hidden');
        }

        // Hide skeleton loader
        function hideSkeletons() {
            skeletonGrid.classList.add('hidden');
            productGrid.classList.remove('hidden');
        }

        // Check if device is mobile
        function checkMobile() {
            isMobile = window.innerWidth < 640;
        }

        loader.classList.remove('hidden');
        fetch('?ajax=data').then(r => r.json()).then(json => {
            DATA = json;
            buildIndexes();
            loader.classList.add('hidden');
        }).catch(err => {
            console.error('Failed to load data:', err);
            loader.classList.add('hidden');
        });

        function buildIndexes() {
            fuseProducts = new Fuse(
                DATA.products.map(p => ({ ...p })),
                {
                    includeScore: true,
                    threshold: .4,
                    ignoreLocation: true,
                    keys: [
                        { name: 'title', weight: .4 },
                        { name: 'meta_title', weight: .3 },
                        { name: 'description', weight: .2 },
                        { name: 'meta_description', weight: .2 },
                        { name: 'meta_keywords', weight: .2 },
                        { name: 'category_name', weight: .1 }
                    ]
                });

            fuseCategories = new Fuse(
                DATA.categories.map(c => ({ ...c })),
                {
                    includeScore: true,
                    threshold: .4,
                    ignoreLocation: true,
                    keys: [
                        { name: 'name', weight: .5 },
                        { name: 'meta_title', weight: .3 },
                        { name: 'description', weight: .2 },
                        { name: 'meta_description', weight: .2 },
                        { name: 'meta_keywords', weight: .2 }
                    ]
                });

            const bag = new Set();
            const tok = s => s.toLowerCase().split(/\W+/).filter(w => w.length > 2);
            DATA.products.forEach(p => {
                [...tok(p.title), ...tok(p.description || ''), ...tok(p.meta_title || ''),
                ...tok(p.meta_description || ''), ...tok(p.meta_keywords || '')]
                    .forEach(w => bag.add(w));
            });
            DATA.categories.forEach(c => {
                [...tok(c.name), ...tok(c.description || ''), ...tok(c.meta_title || ''),
                ...tok(c.meta_description || ''), ...tok(c.meta_keywords || '')]
                    .forEach(w => bag.add(w));
            });
            fuseWords = new Fuse([...bag].map(w => ({ word: w })),
                { keys: ['word'], includeScore: true, threshold: .4, distance: 60 });
        }

        function renderDropdown(q) {
            q = q.trim().toLowerCase();
            if (!q || !fuseProducts) { dropdown.classList.add('hidden'); return; }

            const sug = fuseWords.search(q, { limit: 5 })
                .map(x => x.item.word)
                .filter(w => w !== q);

            const titles = fuseProducts.search(q, { limit: 8 });

            let html = '';
            if (sug.length) {
                html += '<li class="px-4 py-2 text-xs text-gray-500 font-medium">Suggestions</li>';
                sug.forEach(w => {
                    html += `<li class="px-4 py-3 cursor-pointer hover:bg-gray-50 suggestion flex items-center"
                    data-word="${w}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        ${w}
                    </li>`;
                });
                html += '<li class="border-t my-1"></li>';
            }

            if (titles.length) {
                html += '<li class="px-4 py-2 text-xs text-gray-500 font-medium">Products</li>';
                titles.forEach(res => {
                    const p = res.item;
                    html += `
                    <li class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center"
                        data-id="${p.id}" data-type="product" data-label="${escapeHtml(p.title)}">
                        <img src="https://placehold.co/60x60?text=No+Image" alt="Product thumbnail" class="w-10 h-10 rounded-md mr-3 flex-shrink-0 object-cover">
                        <div>
                            <div class="font-medium">${escapeHtml(p.title)}</div>
                            <div class="text-xs text-gray-500">${escapeHtml(p.category_name)}</div>
                        </div>
                    </li>`;
                });
            }

            const catResults = fuseCategories.search(q, { limit: 5 });
            if (catResults.length) {
                html += '<li class="border-t my-1"></li>';
                html += '<li class="px-4 py-2 text-xs text-gray-500 font-medium">Categories</li>';
                catResults.forEach(res => {
                    const c = res.item;
                    html += `
                    <li class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center"
                        data-id="${c.id}" data-type="category" data-label="${escapeHtml(c.name)}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        ${escapeHtml(c.name)}
                    </li>`;
                });
            }

            if (!html) {
                dropdown.classList.add('hidden');
                return;
            }

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        }

        function renderResults(q, forceCategory = false) {
            q = q.trim().toLowerCase();

            // Reset state
            productGrid.innerHTML = '';
            categoriesSection.innerHTML = '';
            currentPage = 1;
            hasMoreItems = true;

            if (!q) {
                initialState.classList.remove('hidden');
                resultsContainer.classList.add('hidden');
                noResults.classList.add('hidden');
                activeFilters.classList.add('hidden');
                return;
            }

            initialState.classList.add('hidden');
            resultsContainer.classList.remove('hidden');

            // Prepare the results in the background
            if (forceCategory) {
                activeCategory = q;
                updateActiveFilters();

                currentResults = DATA.products.filter(p => p.category_name.toLowerCase() === q);

                // Show header immediately
                productsTitle.textContent = `Products in "${activeCategory}" (${currentResults.length})`;
                productsHeader.classList.remove('hidden');

                if (currentResults.length) {
                    // Show skeleton loader for products
                    showSkeletons();

                    // Load products after a short delay (800ms)
                    setTimeout(() => {
                        loadProductBatch();
                        noResults.classList.add('hidden');
                    }, 800);
                } else {
                    hideSkeletons();
                    productGrid.classList.add('hidden');
                    noResults.classList.remove('hidden');
                    renderSuggestions();
                }

                return;
            }

            const prodHits = fuseProducts.search(q, { limit: 100 });
            const catHits = fuseCategories.search(q, { limit: 30 });

            if (prodHits.length || catHits.length) {
                currentResults = prodHits.map(h => h.item);

                // Show categories immediately if available
                if (catHits.length) {
                    renderCategorySection(catHits.map(h => h.item));
                    categoriesSection.classList.remove('hidden');
                } else {
                    categoriesSection.classList.add('hidden');
                }

                // Show product header immediately
                if (prodHits.length) {
                    productsTitle.textContent = `Products (${currentResults.length})`;
                    productsHeader.classList.remove('hidden');

                    // Show skeleton loader for products
                    showSkeletons();

                    // Load products after a short delay (800ms)
                    setTimeout(() => {
                        loadProductBatch();
                    }, 800);
                } else {
                    productsHeader.classList.add('hidden');
                }

                noResults.classList.add('hidden');
            } else {
                resultsContainer.classList.add('hidden');
                noResults.classList.remove('hidden');
                renderSuggestions();
            }

            updateActiveFilters();
        }

        function loadProductBatch() {
            if (isLoading || !hasMoreItems) return;

            isLoading = true;

            const itemsPerPage = getItemsPerPage();
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, currentResults.length);

            if (startIndex >= currentResults.length) {
                hasMoreItems = false;
                isLoading = false;
                loadingMore.classList.add('hidden');
                return;
            }

            const batch = currentResults.slice(startIndex, endIndex);

            if (currentPage === 1) {
                // First batch - replace skeletons with actual content
                hideSkeletons();
                renderProductBatch(batch);
                isLoading = false;
                currentPage++;

                // Setup infinite scroll if there are more items
                if (currentResults.length > itemsPerPage) {
                    setupInfiniteScroll();
                }
            } else {
                // Show loading indicator at the bottom
                loadingMore.classList.remove('hidden');

                // Load next batch with delay
                setTimeout(() => {
                    loadingMore.classList.add('hidden');
                    appendProductBatch(batch);
                    isLoading = false;
                    currentPage++;

                    if (endIndex >= currentResults.length) {
                        hasMoreItems = false;
                    }
                }, 800);
            }
        }

        function renderProductBatch(products) {
            productGrid.innerHTML = renderProductCards(products);
            productGrid.classList.remove('hidden');
        }

        function appendProductBatch(products) {
            // Create a document fragment to avoid multiple reflows
            const fragment = document.createDocumentFragment();
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = renderProductCards(products);

            // Add the fade-in class to each product card
            Array.from(tempContainer.children).forEach(child => {
                child.classList.add('fade-in');
                fragment.appendChild(child);
            });

            productGrid.appendChild(fragment);
        }

        function setupInfiniteScroll() {
            window.addEventListener('scroll', checkScrollPosition);
        }

        function checkScrollPosition() {
            if (isLoading || !hasMoreItems) return;

            const scrollPosition = window.innerHeight + window.scrollY;
            const bodyHeight = document.body.offsetHeight;

            // Load more when user scrolls to 80% of the page
            if (scrollPosition >= bodyHeight * 0.8) {
                loadProductBatch();
            }
        }

        function updateActiveFilters() {
            if (activeCategory || searchInput.value.trim()) {
                activeFilters.classList.remove('hidden');

                let html = '';
                if (activeCategory) {
                    html += `
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-800">
                        Category: ${escapeHtml(activeCategory)}
                        <button class="ml-1 text-primary-600 hover:text-primary-800" data-remove="category">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>`;
                }

                if (searchInput.value.trim() && !activeCategory) {
                    html += `
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800">
                        Search: ${escapeHtml(searchInput.value.trim())}
                        <button class="ml-1 text-gray-600 hover:text-gray-800" data-remove="search">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>`;
                }

                filterTags.innerHTML = html;
            } else {
                activeFilters.classList.add('hidden');
            }
        }

        function renderSuggestions() {
            const q = searchInput.value.trim().toLowerCase();
            if (!q || !fuseWords) return;

            const sug = fuseWords.search(q, { limit: 5 })
                .map(x => x.item.word)
                .filter(w => w !== q);

            if (sug.length) {
                let html = '<p class="text-sm text-gray-600 mb-3">Did you mean:</p>';
                html += '<div class="flex flex-wrap gap-2">';
                sug.forEach(w => {
                    html += `<button class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-full text-sm text-gray-800 transition-colors suggestion" data-word="${w}">${w}</button>`;
                });
                html += '</div>';
                suggestions.innerHTML = html;
            } else {
                suggestions.innerHTML = '';
            }
        }

        function renderProductCards(list) {
            checkMobile();

            if (isMobile) {
                return list.map(p => `
                    <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="product-card-content">
                            <div class="product-card-image aspect-square bg-gray-100 relative">
                                <img src="https://placehold.co/600x400?text=No+Image" alt="${escapeHtml(p.title)}" class="w-full h-full object-cover">
                            </div>
                            <div class="product-card-details p-4">
                                <div>
                                    <p class="text-xs text-primary-600 mb-1 category-link" data-name="${escapeHtml(p.category_name)}">${escapeHtml(p.category_name)}</p>
                                    <h3 class="product-card-title text-sm text-gray-900">${escapeHtml(p.title)}</h3>
                                </div>
                                <div class="product-card-actions mt-auto">
                                    <span class="font-semibold text-gray-900 block mb-2">$${(Math.random() * 100 + 10).toFixed(2)}</span>
                                    <button class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1 rounded-lg transition-colors w-full text-center">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`).join('');
            } else {
                return list.map(p => `
                    <div class="product-card bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="product-card-content">
                            <div class="product-card-image aspect-video bg-gray-100 relative">
                                <img src="https://placehold.co/600x400?text=No+Image" alt="${escapeHtml(p.title)}" class="w-full h-full object-cover">
                            </div>
                            <div class="product-card-details p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-900">${escapeHtml(p.title)}</h3>
                                        <p class="text-xs text-primary-600 mt-1 cursor-pointer hover:underline category-link" data-name="${escapeHtml(p.category_name)}">${escapeHtml(p.category_name)}</p>
                                    </div>
                                    <button class="text-gray-400 hover:text-primary-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="product-card-description text-sm text-gray-600 mt-2 line-clamp-2">${escapeHtml(p.description || 'No description available')}</p>
                                <div class="product-card-actions mt-4 flex items-center justify-between">
                                    <span class="font-semibold text-gray-900">$${(Math.random() * 100 + 10).toFixed(2)}</span>
                                    <button class="bg-primary-600 hover:bg-primary-700 text-white text-sm px-3 py-1 rounded-lg transition-colors">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`).join('');
            }
        }

        function renderCategorySection(list) {
            categoriesSection.innerHTML = `
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Categories (${list.length})</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                ${list.map(c => `
                <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer category-card" data-name="${escapeHtml(c.name)}">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center mr-4 flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">${escapeHtml(c.name)}</h3>
                            <p class="text-sm text-gray-500 mt-1">${escapeHtml(c.description || 'Browse products in this category')}</p>
                        </div>
                    </div>
                </div>`).join('')}
            </div>`;
        }

        const debounce = (fn, ms = 300) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

        searchInput.addEventListener('input', debounce(e => {
            renderDropdown(e.target.value);
            if (!e.target.value.trim()) {
                renderResults('');
            }
        }, 200));

        dropdown.addEventListener('mousedown', e => {
            const sug = e.target.closest('.suggestion');
            if (sug) {
                searchInput.value = sug.dataset.word;
                dropdown.classList.add('hidden');
                renderResults(sug.dataset.word);
                return;
            }

            const li = e.target.closest('li[data-id]');
            if (!li) return;

            const label = li.dataset.label;
            searchInput.value = label;
            dropdown.classList.add('hidden');

            if (li.dataset.type === 'category') {
                renderResults(label, true);
            } else {
                renderResults(label);
            }
        });

        document.getElementById('searchForm').addEventListener('submit', e => {
            e.preventDefault();
            dropdown.classList.add('hidden');
            renderResults(searchInput.value);
        });

        document.addEventListener('click', e => {
            if (!dropdown.contains(e.target) && e.target !== searchInput) {
                dropdown.classList.add('hidden');
            }
        });

        document.addEventListener('click', e => {
            const categoryLink = e.target.closest('.category-link');
            if (categoryLink) {
                const cname = categoryLink.dataset.name;
                searchInput.value = cname;
                renderResults(cname, true);
                return;
            }

            const categoryCard = e.target.closest('.category-card');
            if (categoryCard) {
                const cname = categoryCard.dataset.name;
                searchInput.value = cname;
                renderResults(cname, true);
            }
        });

        suggestions.addEventListener('click', e => {
            const sug = e.target.closest('.suggestion');
            if (sug) {
                searchInput.value = sug.dataset.word;
                renderResults(sug.dataset.word);
            }
        });

        filterTags.addEventListener('click', e => {
            const removeBtn = e.target.closest('[data-remove]');
            if (!removeBtn) return;

            const type = removeBtn.dataset.remove;
            if (type === 'category') {
                activeCategory = null;
                renderResults(searchInput.value);
            } else if (type === 'search') {
                searchInput.value = '';
                renderResults('');
            }
        });

        clearFilters.addEventListener('click', () => {
            activeCategory = null;
            searchInput.value = '';
            renderResults('');

            // Remove infinite scroll listener when clearing results
            window.removeEventListener('scroll', checkScrollPosition);
        });

        // Handle window resize to adjust items per page and check mobile status
        window.addEventListener('resize', debounce(() => {
            checkMobile();
            if (currentResults.length > 0) {
                // Only regenerate skeletons if they're currently visible
                if (!skeletonGrid.classList.contains('hidden')) {
                    generateSkeletons(getItemsPerPage());
                }
            }
        }, 200));

        // Initial mobile check
        checkMobile();

        // Clean up event listener when navigating away
        window.addEventListener('beforeunload', () => {
            window.removeEventListener('scroll', checkScrollPosition);
        });

        function escapeHtml(s) {
            return (s || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }
    </script>
</body>

</html>