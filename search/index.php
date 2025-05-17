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
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#D92B13',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
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
            box-shadow: 0 0 0 3px rgba(217, 43, 19, 0.3);
        }

        .search-input {
            transition: all 0.2s ease;
        }

        .category-pill {
            transition: all 0.2s ease;
        }

        .product-card {
            transition: transform 0.2s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
        }

        .loader {
            border-top-color: #D92B13;
            animation: spinner 0.6s linear infinite;
        }

        @keyframes spinner {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col font-rubik">
    <header class="bg-white shadow-md sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 py-4 md:py-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-primary-600">Marketplace</h1>
                <div class="text-sm text-gray-500">Advanced Search</div>
            </div>
            <form id="searchForm" class="relative">
                <div class="relative flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input id="searchInput" type="search" autocomplete="off" placeholder="Search products or categories"
                        class="search-input w-full border border-gray-300 rounded-full pl-10 pr-4 py-3 focus:outline-none focus:border-primary-600 bg-gray-50" />
                    <button type="submit"
                        class="absolute right-2 bg-primary-600 text-white rounded-full p-2 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
                <ul id="dropdown"
                    class="absolute left-0 right-0 mt-2 max-h-60 overflow-y-auto bg-white border rounded-lg shadow-lg hidden z-20">
                </ul>
            </form>
        </div>
    </header>

    <div id="loader" class="hidden flex justify-center items-center py-12">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"></div>
    </div>

    <main id="resultsArea" class="flex-1 max-w-6xl mx-auto px-4 py-8 space-y-8"></main>

    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm">
            <p>© 2024 Marketplace. All rights reserved.</p>
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
        const resultsArea = document.getElementById('resultsArea');
        const dropdown = document.getElementById('dropdown');
        const searchInput = document.getElementById('searchInput');
        const loader = document.getElementById('loader');

        function showLoader() {
            loader.classList.remove('hidden');
            resultsArea.classList.add('hidden');
        }

        function hideLoader() {
            loader.classList.add('hidden');
            resultsArea.classList.remove('hidden');
        }

        showLoader();
        fetch('?ajax=data').then(r => r.json()).then(json => {
            DATA = json;
            buildIndexes();
            hideLoader();

            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            if (query) {
                searchInput.value = query;
                renderResults(query);
            }
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
                html += '<li class="px-4 py-2 text-xs text-gray-500 font-medium uppercase tracking-wider bg-gray-50">Suggestions</li>';
                sug.forEach(w => {
                    html += `<li class="px-4 py-3 cursor-pointer hover:bg-gray-50 suggestion flex items-center"
                    data-word="${w}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        ${w}
                    </li>`;
                });
                html += '<li class="border-t my-1"></li>';
            }

            titles.forEach(res => {
                const p = res.item;
                html += `
       <li class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center"
           data-id="${p.id}" data-type="product" data-label="${escapeHtml(p.title)}">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
         <span>${escapeHtml(p.title)}</span> <span class="ml-2 text-xs text-gray-400 font-medium">(product)</span>
       </li>`;
            });

            fuseCategories.search(q, { limit: 5 }).forEach(res => {
                const c = res.item;
                html += `
       <li class="px-4 py-3 cursor-pointer hover:bg-gray-50 flex items-center"
           data-id="${c.id}" data-type="category" data-label="${escapeHtml(c.name)}">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
         <span>${escapeHtml(c.name)}</span> <span class="ml-2 text-xs text-gray-400 font-medium">(category)</span>
       </li>`;
            });

            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        }

        function renderResults(q, forceCategory = false) {
            q = q.trim().toLowerCase();
            resultsArea.innerHTML = '';
            if (!q) return;

            if (forceCategory) {
                const list = DATA.products.filter(p => p.category_name.toLowerCase() === q);
                renderProductSection(list, q);
                if (!list.length) resultsArea.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 text-lg">No products found in this category.</p>
                        <button onclick="searchInput.value = ''; searchInput.focus();" class="mt-4 text-primary-600 hover:text-primary-700 font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Start a new search
                        </button>
                    </div>`;
                return;
            }

            const prodHits = fuseProducts.search(q, { limit: 60 });
            const catHits = fuseCategories.search(q, { limit: 30 });

            resultsArea.insertAdjacentHTML('beforeend', `
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Search Results</h2>
                    <div class="text-sm text-gray-500">Found ${prodHits.length + catHits.length} results for "${escapeHtml(q)}"</div>
                </div>
            `);

            if (catHits.length) renderCategorySection(catHits.map(h => h.item));
            if (prodHits.length) renderProductSection(prodHits.map(h => h.item), q);

            if (!prodHits.length && !catHits.length)
                resultsArea.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-600 text-lg">No results found for "${escapeHtml(q)}"</p>
                        <button onclick="searchInput.value = ''; searchInput.focus();" class="mt-4 text-primary-600 hover:text-primary-700 font-medium flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Start a new search
                        </button>
                    </div>`;
        }

        function renderProductSection(list, query = '') {
            const title = query ? `Products matching "${escapeHtml(query)}"` : 'Products';

            resultsArea.insertAdjacentHTML('beforeend', `
    <section class="mb-12">
      <h3 class="text-xl font-semibold mb-6 text-gray-800 border-b pb-2">${title} <span class="text-sm font-normal text-gray-500">(${list.length} items)</span></h3>
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        ${list.map(p => `
           <div class="bg-white rounded-lg shadow-sm overflow-hidden product-card">
             <div class="aspect-video bg-gray-100 relative">
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
             </div>
             <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium px-2 py-1 bg-primary-50 text-primary-700 rounded-full">${escapeHtml(p.category_name)}</span>
                    <button class="text-gray-400 hover:text-primary-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                </div>
                <h4 class="font-semibold text-gray-800 mb-1 line-clamp-1">${escapeHtml(p.title)}</h4>
                <p class="text-sm text-gray-600 mb-3 line-clamp-2 h-10">
                   ${escapeHtml(p.description || 'No description available')}
                </p>
                <div class="flex items-center justify-between mt-2">
                    <button class="text-primary-600 hover:text-primary-700 text-sm font-medium">View Details</button>
                    <button class="bg-primary-600 text-white rounded-full p-2 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
             </div>
           </div>`).join('')}
      </div>
    </section>`);
        }

        function renderCategorySection(list) {
            resultsArea.insertAdjacentHTML('beforeend', `
    <section class="mb-8">
      <h3 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Categories <span class="text-sm font-normal text-gray-500">(${list.length} items)</span></h3>
      <div class="flex flex-wrap gap-3">
        ${list.map(c => `
           <a class="px-4 py-2 bg-primary-50 text-primary-700 rounded-full text-sm hover:bg-primary-100 hover:text-primary-800
                     cursor-pointer category-pill flex items-center"
              data-name="${escapeHtml(c.name)}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                ${escapeHtml(c.name)}
            </a>`).join('')}
      </div>
    </section>`);
        }

        const debounce = (fn, ms = 300) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

        searchInput.addEventListener('input', debounce(e => renderDropdown(e.target.value), 200));

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
            const query = searchInput.value.trim();
            if (query) {
                history.pushState({}, '', `?q=${encodeURIComponent(query)}`);
                renderResults(query);
            }
        });

        document.addEventListener('click', e => {
            if (!dropdown.contains(e.target) && e.target !== searchInput)
                dropdown.classList.add('hidden');
        });

        resultsArea.addEventListener('click', e => {
            const pill = e.target.closest('.category-pill');
            if (!pill) return;
            const cname = pill.dataset.name;
            searchInput.value = cname;
            history.pushState({}, '', `?q=${encodeURIComponent(cname)}&type=category`);
            renderResults(cname, true);
        });

        function escapeHtml(s) {
            if (!s) return '';
            return s.toString().replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        window.addEventListener('popstate', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            const type = urlParams.get('type');

            if (query) {
                searchInput.value = query;
                renderResults(query, type === 'category');
            } else {
                searchInput.value = '';
                resultsArea.innerHTML = '';
            }
        });
    </script>
</body>

</html>