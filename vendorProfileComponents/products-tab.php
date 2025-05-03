<!-- Products Tab -->
<div id="products-tab" class="tab-pane active">
    <!-- Filter and Sort -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div class="flex flex-col sm:flex-row gap-4 w-full">
            <select id="filter-category" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                <option value="">All Categories</option>
            </select>
            <select id="sort-products" class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
                <option value="default">Default Sorting</option>
                <option value="latest">Latest</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
            </select>
            <input type="text" id="search-products" placeholder="Search products..."
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full sm:w-auto">
        </div>
    </div>

    <!-- Products Grid -->
    <div id="products-container" class="masonry-grid">
        <div class="col-span-full text-center py-8 text-gray-500">
            No products found for this vendor.
        </div>
    </div>
    <button id="loadMoreBtn"
        class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
        Load More Products
    </button>
</div>

<style>
    .masonry-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
    }

    @media (min-width: 640px) {
        .masonry-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .masonry-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .price-container {
        position: relative;
        display: inline-block;
    }

    .price-value {
        position: relative;
        z-index: 1;
        transition: opacity 0.3s ease;
        opacity: 0;
    }

    .price-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(200, 200, 200, 0.7);
        backdrop-filter: blur(3px);
        border-radius: 4px;
        cursor: pointer;
        z-index: 2;
        overflow: hidden;
    }

    .price-overlay::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background:
            radial-gradient(white, rgba(255, 255, 255, 0) 2px) 0 0 / 4px 4px,
            radial-gradient(white, rgba(255, 255, 255, 0) 1px) 2px 2px / 4px 4px;
        animation: staticNoise 0.2s infinite alternate;
        opacity: 0.4;
    }

    @keyframes staticNoise {
        0% {
            background-position: 0% 0%, 2px 2px;
        }

        10% {
            background-position: -1px 1px, 1px 3px;
        }

        20% {
            background-position: 1px -1px, 3px 1px;
        }

        30% {
            background-position: -2px -2px, 0px 0px;
        }

        40% {
            background-position: 2px 2px, 4px 4px;
        }

        50% {
            background-position: 0px 2px, 2px 4px;
        }

        60% {
            background-position: -1px -1px, 1px 1px;
        }

        70% {
            background-position: 1px 0px, 3px 2px;
        }

        80% {
            background-position: -2px 1px, 0px 3px;
        }

        90% {
            background-position: 0px -2px, 2px 0px;
        }

        100% {
            background-position: 1px 1px, 3px 3px;
        }
    }

    .price-revealed .price-overlay {
        display: none;
    }

    .price-revealed .price-value {
        opacity: 1;
    }

    .product-card {
        break-inside: avoid;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
</style>

<script>
    // --------------------------------------------------
    //  Helper: get first product image or placeholder
    // --------------------------------------------------
    async function getProductImageUrl(product) {
        const placeholderText = encodeURIComponent((product.name || '').substring(0, 2));
        const placeholder = `https://placehold.co/400x300/f0f0f0/808080?text=${placeholderText}`;

        try {
            const res = await fetch(`${BASE_URL}img/products/${product.id}/images.json`);
            if (!res.ok) return placeholder;
            const json = await res.json();
            if (Array.isArray(json.images) && json.images.length > 0) {
                return `${BASE_URL}img/products/${product.id}/${json.images[0]}`;
            }
        } catch (e) {
            // ignore errors and fall back
        }
        return placeholder;
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (vendorId) {
            loadProductsForDisplay(vendorId);
        }

        document.getElementById('filter-category').addEventListener('change', filterProductsByCategory);
        document.getElementById('search-products').addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const categoryId = document.getElementById('filter-category').value;
            filterProducts(categoryId, searchTerm);
        });
        document.getElementById('sort-products').addEventListener('change', function (e) {
            const sortValue = e.target.value;
            const container = document.getElementById('products-container');
            const productCards = Array.from(container.children);
            if (productCards.length <= 1) return;
            productCards.sort((a, b) => {
                if (sortValue === 'latest') {
                    return 0;
                } else if (sortValue === 'price-low') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return aPrice - bPrice;
                } else if (sortValue === 'price-high') {
                    const aPrice = parseInt((a.dataset.lowestPrice || '0')) || 0;
                    const bPrice = parseInt((b.dataset.lowestPrice || '0')) || 0;
                    return bPrice - aPrice;
                }
                return 0;
            });
            container.innerHTML = '';
            productCards.forEach(card => container.appendChild(card));
        });

        document.getElementById('loadMoreBtn').addEventListener('click', function () {
            if (currentPage < totalPages) {
                loadProductsForDisplay(vendorId, currentPage + 1);
            }
        });

        // Event delegation for price reveal
        document.addEventListener('click', function (e) {
            if (e.target.closest('.price-overlay')) {
                const priceContainer = e.target.closest('.price-container');
                priceContainer.classList.add('price-revealed');
            }
        });
    });

    function loadProductsForDisplay(id, page = 1) {
        fetch(`${BASE_URL}fetch/manageProfile.php?action=getStoreProducts&id=${id}&page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderProductsForDisplay(data.products, page === 1);
                    if (page === 1) {
                        allProducts = data.products;
                    } else {
                        allProducts = [...allProducts, ...data.products];
                    }
                    currentPage = data.pagination?.page || 1;
                    totalPages = data.pagination?.pages || 1;
                    const loadMoreBtn = document.getElementById('loadMoreBtn');
                    if (currentPage < totalPages) {
                        loadMoreBtn.classList.remove('hidden');
                    } else {
                        loadMoreBtn.classList.add('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Error loading products:', error);
            });
    }

    function populateCategoryFilter(categories) {
        if (!categories || categories.length === 0) return;
        const filterSelect = document.getElementById('filter-category');
        filterSelect.innerHTML = '<option value="">All Categories</option>';
        const activeCats = categories.filter(cat => cat.status === 'active');
        activeCats.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            filterSelect.appendChild(option);
        });
        filterSelect.addEventListener('change', filterProductsByCategory);
    }

    function filterProductsByCategory() {
        const categoryId = document.getElementById('filter-category').value;
        const searchTerm = document.getElementById('search-products').value.toLowerCase();
        filterProducts(categoryId, searchTerm);
    }

    function filterProducts(categoryId = '', searchTerm = '') {
        const container = document.getElementById('products-container');
        container.innerHTML = '';
        let filtered = [...allProducts];
        if (categoryId) {
            filtered = filtered.filter(p => p.store_category_id === categoryId);
        }
        if (searchTerm) {
            filtered = filtered.filter(p => p.name.toLowerCase().includes(searchTerm));
        }
        if (filtered.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found matching your criteria.</div>`;
            return;
        }
        renderProductsForDisplay(filtered, true);
    }

    // --------------------------------------------------
    //  Render: now async so we can await image fetch
    // --------------------------------------------------
    async function renderProductsForDisplay(products, clearExisting = true) {
        const container = document.getElementById('products-container');
        if (clearExisting) {
            container.innerHTML = '';
        }
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found for this vendor.</div>`;
            return;
        }

        for (const product of products) {
            // fetch image (or placeholder)
            const imageUrl = await getProductImageUrl(product);

            // Find lowest price for sorting
            let lowestPrice = 0;
            if (product.pricing && product.pricing.length > 0) {
                lowestPrice = Math.min(...product.pricing.map(p => parseFloat(p.price)));
            }

            // build pricing lines
            let pricingLines = '';
            if (product.pricing && product.pricing.length > 0) {
                pricingLines = product.pricing.map(pr => {
                    const unitParts = pr.unit_name.split(' ');
                    const siUnit = unitParts[0] || '';
                    const packageName = unitParts.slice(1).join(' ') || '';
                    const formattedUnit = `${pr.package_size} ${siUnit} ${packageName}`.trim();
                    const categoryDisplay = pr.price_category.charAt(0).toUpperCase() + pr.price_category.slice(1);

                    return `
                        <div class="flex justify-between items-center mb-2 p-2 bg-gray-50 rounded">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-700">${escapeHtml(formattedUnit)}</span>
                                <span class="text-xs text-gray-500">${categoryDisplay}</span>
                                ${pr.delivery_capacity ?
                            `<span class="text-xs text-gray-500">
                                        ${pr.price_category === 'retail' ? 'Max' : 'Min'} Capacity: ${pr.delivery_capacity}
                                    </span>` :
                            ''}
                            </div>
                            <div class="price-container">
                                <span class="price-value text-red-600 font-bold">UGX ${formatNumber(pr.price)}</span>
                                <div class="price-overlay"></div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                pricingLines = `<div class="text-sm text-gray-600 italic p-2">No price data</div>`;
            }

            // create card
            const productCard = document.createElement('div');
            productCard.className = 'product-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow';
            productCard.dataset.lowestPrice = lowestPrice.toString();
            productCard.innerHTML = `
                <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-48 object-cover">
                <div class="p-4 flex flex-col flex-grow">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">${escapeHtml(product.name)}</h3>
                    <p class="text-gray-500 text-sm mb-4">
                        ${escapeHtml(product.description || '').substring(0, 100)}...
                    </p>
                    <div class="border-t border-gray-200 pt-3 mb-3">
                        ${pricingLines}
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-auto">
                        <button class="bg-red-600 text-white py-2 px-3 rounded-md text-sm hover:bg-red-700 transition-colors w-full flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Buy in Store
                        </button>
                        <button onclick="showProductDetails('${product.store_product_id}')" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-md text-sm hover:bg-gray-100 transition-colors w-full flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Shop Now
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(productCard);
        }
    }

    function showProductDetails(productId) {
        const product = allProducts.find(p => p.store_product_id === productId);
        if (!product) {
            showToast("Product details not found.", "error");
            return;
        }
        showToast(`Showing details for ${product.name}`, "success");
    }
</script>