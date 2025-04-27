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
    <div id="products-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="col-span-full text-center py-8 text-gray-500">
            No products found for this vendor.
        </div>
    </div>
    <button id="loadMoreBtn"
        class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors hidden">
        Load More Products
    </button>
</div>

<script>
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
                    const aPrice = parseInt((a.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    const bPrice = parseInt((b.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    return aPrice - bPrice;
                } else if (sortValue === 'price-high') {
                    const aPrice = parseInt((a.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
                    const bPrice = parseInt((b.querySelector('.border-t')?.textContent || '0').replace(/\D+/g, '')) || 0;
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

    function renderProductsForDisplay(products, clearExisting = true) {
        const container = document.getElementById('products-container');
        if (clearExisting) {
            container.innerHTML = '';
        }
        if (!products || products.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center py-8 text-gray-500">No products found for this vendor.</div>`;
            return;
        }
        products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1 flex flex-col h-full';
            const productNameShort = encodeURIComponent(product.name.substring(0, 2));
            const imageUrl = `https://placehold.co/400x300/f0f0f0/808080?text=${productNameShort}`;

            let pricingLines = '';
            if (product.pricing && product.pricing.length > 0) {
                pricingLines = product.pricing.map(pr => {
                    const unitParts = pr.unit_name.split(' ');
                    const siUnit = unitParts[0] || '';
                    const packageName = unitParts.slice(1).join(' ') || '';
                    const formattedUnit = `${pr.package_size} ${siUnit} ${packageName}`.trim();

                    return `
                    <div class="text-sm text-gray-700 my-1">
                        <strong>${escapeHtml(formattedUnit)}</strong>: <span class="text-red-600 font-bold">UGX ${formatNumber(pr.price)}</span>
                        <span class="text-xs text-gray-500">/ ${escapeHtml(pr.price_category)}</span>
                        ${pr.delivery_capacity ? `<span class="ml-2 text-xs text-gray-400">Cap: ${pr.delivery_capacity}</span>` : ''}
                    </div>
                    `;
                }).join('');
            } else {
                pricingLines = `<div class="text-sm text-gray-600 italic">No price data</div>`;
            }

            productCard.innerHTML = `
            <img src="${imageUrl}" alt="${escapeHtml(product.name)}" class="w-full h-48 object-cover">
            <div class="p-4 flex flex-col flex-grow">
                <h3 class="text-lg font-bold text-gray-800 mb-2">${escapeHtml(product.name)}</h3>
                <p class="text-gray-500 text-sm mb-4 flex-grow">
                    ${escapeHtml(product.description || '').substring(0, 100)}...
                </p>
                <div class="border-t border-gray-200 pt-2">
                    ${pricingLines}
                </div>
                <div class="grid grid-cols-2 gap-2 pt-4 mt-auto">
                    <button class="bg-red-600 text-white py-2 rounded-md text-sm hover:bg-red-700 transition-colors w-full">Buy</button>
                    <button onclick="showProductDetails('${product.store_product_id}')" class="bg-white border border-gray-300 text-gray-700 py-2 rounded-md text-sm hover:bg-gray-100 transition-colors w-full">Details</button>
                </div>
            </div>
            `;
            container.appendChild(productCard);
        });
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