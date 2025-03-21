<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = $pageTitle ?? 'Zzimba Online Uganda';
$activeNav = $activeNav ?? 'home';

// Define data arrays in PHP section
$heroSlides = [
    [
        'image' => 'https://placehold.co/1000x600',
        'title' => 'Buy Online<br>Deliver On-site',
        'subtitle' => 'Quality building materials delivered to your doorstep',
        'buttonText' => 'Buy Now',
        'buttonUrl' => '#'
    ],
    [
        'image' => 'https://placehold.co/1000x600',
        'title' => 'Construction Procurement<br>Made Easy',
        'subtitle' => 'Discover our wide range of products',
        'buttonText' => 'Order Now',
        'buttonUrl' => '#'
    ],
    [
        'image' => 'https://placehold.co/1000x600',
        'title' => '100s of Vendors<br>1000s of Supplies',
        'subtitle' => 'Latest trends in building materials',
        'buttonText' => 'Join Now',
        'buttonUrl' => '#'
    ]
];

$featuredProducts = [
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Premium Cement',
        'description' => 'High-quality Portland cement',
        'price' => 'UGX 29,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Reinforced Steel',
        'description' => 'Strong and durable steel',
        'price' => 'UGX 49,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Concrete Blocks',
        'description' => 'Reliable concrete blocks',
        'price' => 'UGX 19,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Bricks',
        'description' => 'Red clay bricks',
        'price' => 'UGX 9,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Roofing Sheets',
        'description' => 'Durable metal roofing',
        'price' => 'UGX 39,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'PVC Pipes',
        'description' => 'High-quality plumbing pipes',
        'price' => 'UGX 15,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Paint Buckets',
        'description' => 'Premium interior paint',
        'price' => 'UGX 24,999'
    ],
    [
        'image' => 'https://placehold.co/300x200',
        'title' => 'Electrical Wiring',
        'description' => 'Safe and reliable wiring',
        'price' => 'UGX 12,999'
    ]
];

$categories = [
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Cement & Concrete'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Bricks & Blocks'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Steel & Metals'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Tiles & Flooring'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Plumbing Supplies'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Electrical Materials'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Paints & Finishes'
    ],
    [
        'image' => 'https://placehold.co/400x300',
        'title' => 'Tools & Equipment'
    ]
];

$partners = [
    ['name' => 'Rutungu Investments', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Jonik Hardware Supplies', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Picaso Hardware', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Ug Martyrs Construction', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Shule Electricals', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Cheap General Hardware', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'A&C Concrete', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'SEMU Hardware', 'logo' => 'https://placehold.co/100x40'],
    ['name' => "God's Mercy Hardware", 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Rehoboth Plumbing', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Mirage Tiles', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'HW Hardware', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Kiwa Paints Uganda Ltd', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'STOTA Africa', 'logo' => 'https://placehold.co/100x40'],
    ['name' => 'Utopia', 'logo' => 'https://placehold.co/100x40']
];

ob_start();
?>
<div class="swiper hero-slider">
    <div class="swiper-wrapper" id="hero-slider-wrapper">
        <?php foreach ($heroSlides as $slide): ?>
            <div class="swiper-slide relative h-[600px]">
                <div class="hero-image absolute inset-0 bg-cover bg-center" style="background-image: url('<?= $slide['image'] ?>')"></div>
                <div class="absolute inset-0 bg-black bg-opacity-50"></div>
                <div class="container mx-auto px-4 h-full flex items-center relative z-10">
                    <div class="text-white max-w-2xl">
                        <h1 class="text-5xl font-bold mb-6"><?= $slide['title'] ?></h1>
                        <p class="text-xl mb-8"><?= $slide['subtitle'] ?></p>
                        <a href="<?= $slide['buttonUrl'] ?>" class="bg-primary text-white px-8 py-3 rounded-lg text-lg hover:bg-red-600 transition-colors"><?= $slide['buttonText'] ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
</div>
<div class="bg-gray-50 py-8">
    <div class="container mx-auto px-4 text-center">
        <a href="<?= BASE_URL ?>request-for-quote"
            class="inline-flex items-center px-6 py-3 border border-transparent text-lg font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200 shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Request a Quote Now
        </a>
        <p class="mt-3 text-gray-600">Get personalized quotes for your construction needs</p>
    </div>
</div>
<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
            <div class="text-4xl mb-4">🏗️</div>
            <h3 class="text-xl font-semibold mb-2">Quality Materials</h3>
            <p class="text-gray-600">Premium construction supplies from trusted manufacturers</p>
        </div>
        <div class="text-center">
            <div class="text-4xl mb-4">🚚</div>
            <h3 class="text-xl font-semibold mb-2">Fast Delivery</h3>
            <p class="text-gray-600">Next-day delivery available on most items</p>
        </div>
        <div class="text-center">
            <div class="text-4xl mb-4">💪</div>
            <h3 class="text-xl font-semibold mb-2">Expert Support</h3>
            <p class="text-gray-600">Professional advice from industry experts</p>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold mb-8">Featured Products</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8" id="featured-products-container">
        <?php
        $initialProductCount = 4;
        for ($i = 0; $i < $initialProductCount; $i++):
            $product = $featuredProducts[$i];
        ?>
            <div class="bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="relative">
                    <img src="<?= $product['image'] ?>" alt="<?= $product['title'] ?>" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT</div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-lg mb-2"><?= $product['title'] ?></h3>
                    <p class="text-gray-600 mb-4 h-12"><?= $product['description'] ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-primary font-bold text-xl"><?= $product['price'] ?></span>
                        <div class="flex space-x-2">
                            <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                <i class="fas fa-shopping-cart mr-1"></i> Buy
                            </button>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                <i class="fas fa-tag mr-1"></i> Sell
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <div class="text-center mt-10">
        <button id="load-more-products" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
            <span>Load More</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </button>
    </div>
</div>
<div class="container mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold mb-8">Shop by Category</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8" id="categories-container">
        <?php
        $initialCategoryCount = 4;
        for ($i = 0; $i < $initialCategoryCount; $i++):
            $category = $categories[$i];
        ?>
            <div class="relative rounded-xl overflow-hidden group cursor-pointer shadow-lg">
                <img src="<?= $category['image'] ?>" alt="<?= $category['title'] ?>" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <h3 class="text-white text-xl font-bold mb-2"><?= $category['title'] ?></h3>
                        <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                        <button class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100">
                            Explore <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <div class="text-center mt-10">
        <button id="load-more-categories" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors inline-flex items-center">
            <span>Load More</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </button>
    </div>
</div>

<!-- New Clients & Partners Section -->
<div class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Some Clients & Partners</h2>
        <div class="relative">
            <!-- The slider container uses Tailwind for overflow and whitespace handling -->
            <div id="partners-slider-container" class="flex whitespace-nowrap overflow-hidden relative">
                <div id="partners-slider" class="flex items-center space-x-8">
                    <?php foreach ($partners as $partner): ?>
                        <div class="flex flex-col items-center text-center space-y-2 px-4 py-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow min-w-[260px]">
                            <img src="<?= $partner['logo'] ?>" alt="<?= $partner['name'] ?>" class="h-12 w-12 object-contain">
                            <span class="text-gray-700 font-medium break-words"><?= $partner['name'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes zoomInOut {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }
    }

    .hero-image {
        animation: zoomInOut 20s infinite alternate;
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Swiper for Hero
        var heroSwiper = new Swiper('.hero-slider', {
            loop: true,
            autoplay: {
                delay: 30000
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true
            }
        });

        // Load More Products Functionality
        const loadMoreProductsBtn = document.getElementById('load-more-products');
        let productsLoaded = <?= $initialProductCount ?>;
        const totalProducts = <?= count($featuredProducts) ?>;

        loadMoreProductsBtn.addEventListener('click', function() {
            const productsContainer = document.getElementById('featured-products-container');
            const productsToLoad = Math.min(4, totalProducts - productsLoaded);

            if (productsToLoad <= 0) {
                loadMoreProductsBtn.disabled = true;
                loadMoreProductsBtn.classList.add('opacity-50', 'cursor-not-allowed');
                loadMoreProductsBtn.querySelector('span').textContent = 'No More Products';
                loadMoreProductsBtn.querySelector('svg').classList.remove('animate-bounce');
                return;
            }

            // Show loading animation
            loadMoreProductsBtn.querySelector('span').textContent = 'Loading...';

            setTimeout(() => {
                for (let i = productsLoaded; i < productsLoaded + productsToLoad; i++) {
                    const product = <?= json_encode($featuredProducts) ?>[i];
                    const productElement = document.createElement('div');
                    productElement.className = 'bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 opacity-0';
                    productElement.innerHTML = `
                        <div class="relative">
                            <img src="${product.image}" alt="${product.title}" class="w-full h-48 object-cover">
                            <div class="absolute top-0 right-0 bg-red-500 text-white px-3 py-1 rounded-bl-lg font-semibold">HOT</div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-lg mb-2">${product.title}</h3>
                            <p class="text-gray-600 mb-4 h-12">${product.description}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-primary font-bold text-xl">${product.price}</span>
                                <div class="flex space-x-2">
                                    <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center">
                                        <i class="fas fa-shopping-cart mr-1"></i> Buy
                                    </button>
                                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                                        <i class="fas fa-tag mr-1"></i> Sell
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    productsContainer.appendChild(productElement);

                    // Trigger animation after a small delay for each product
                    setTimeout(() => {
                        productElement.classList.add('fade-in');
                    }, (i - productsLoaded) * 150);
                }

                productsLoaded += productsToLoad;

                // Update button text
                loadMoreProductsBtn.querySelector('span').textContent = 'Load More';

                // Disable button if all products are loaded
                if (productsLoaded >= totalProducts) {
                    loadMoreProductsBtn.disabled = true;
                    loadMoreProductsBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    loadMoreProductsBtn.querySelector('span').textContent = 'No More Products';
                    loadMoreProductsBtn.querySelector('svg').classList.remove('animate-bounce');
                }
            }, 800); // Simulate loading delay
        });

        // Load More Categories Functionality
        const loadMoreCategoriesBtn = document.getElementById('load-more-categories');
        let categoriesLoaded = <?= $initialCategoryCount ?>;
        const totalCategories = <?= count($categories) ?>;

        loadMoreCategoriesBtn.addEventListener('click', function() {
            const categoriesContainer = document.getElementById('categories-container');
            const categoriesToLoad = Math.min(4, totalCategories - categoriesLoaded);

            if (categoriesToLoad <= 0) {
                loadMoreCategoriesBtn.disabled = true;
                loadMoreCategoriesBtn.classList.add('opacity-50', 'cursor-not-allowed');
                loadMoreCategoriesBtn.querySelector('span').textContent = 'No More Categories';
                loadMoreCategoriesBtn.querySelector('svg').classList.remove('animate-bounce');
                return;
            }

            // Show loading animation
            loadMoreCategoriesBtn.querySelector('span').textContent = 'Loading...';

            setTimeout(() => {
                for (let i = categoriesLoaded; i < categoriesLoaded + categoriesToLoad; i++) {
                    const category = <?= json_encode($categories) ?>[i];
                    const categoryElement = document.createElement('div');
                    categoryElement.className = 'relative rounded-xl overflow-hidden group cursor-pointer shadow-lg opacity-0';
                    categoryElement.innerHTML = `
                        <img src="${category.image}" alt="${category.title}" class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent opacity-80 group-hover:opacity-90 transition-all">
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <h3 class="text-white text-xl font-bold mb-2">${category.title}</h3>
                                <div class="w-10 h-1 bg-primary mb-4 transform transition-all duration-300 group-hover:w-20"></div>
                                <button class="text-white bg-primary bg-opacity-0 group-hover:bg-opacity-100 px-4 py-2 rounded-lg transition-all duration-300 opacity-0 group-hover:opacity-100">
                                    Explore <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    categoriesContainer.appendChild(categoryElement);

                    // Trigger animation after a small delay for each category
                    setTimeout(() => {
                        categoryElement.classList.add('fade-in');
                    }, (i - categoriesLoaded) * 150);
                }

                categoriesLoaded += categoriesToLoad;

                // Update button text
                loadMoreCategoriesBtn.querySelector('span').textContent = 'Load More';

                // Disable button if all categories are loaded
                if (categoriesLoaded >= totalCategories) {
                    loadMoreCategoriesBtn.disabled = true;
                    loadMoreCategoriesBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    loadMoreCategoriesBtn.querySelector('span').textContent = 'No More Categories';
                    loadMoreCategoriesBtn.querySelector('svg').classList.remove('animate-bounce');
                }
            }, 800); // Simulate loading delay
        });

        // Partners (Dynamic Marquee with pause on hover)
        // Duplicate the partners for a seamless loop
        const partnersSlider = document.getElementById('partners-slider');
        const partnersClone = partnersSlider.innerHTML;
        partnersSlider.innerHTML += partnersClone;

        // Scrolling logic with pause on hover
        let speed = 1.5; // pixels per frame
        let position = 0;
        let paused = false;
        const sliderContainer = document.getElementById('partners-slider-container');

        sliderContainer.addEventListener('mouseenter', () => {
            paused = true;
        });
        sliderContainer.addEventListener('mouseleave', () => {
            paused = false;
        });

        function scrollPartners() {
            if (!paused) {
                position -= speed;
                partnersSlider.style.transform = `translateX(${position}px)`;
            }
            if (Math.abs(position) >= partnersSlider.scrollWidth / 2) {
                position = 0;
            }
            requestAnimationFrame(scrollPartners);
        }
        scrollPartners();
    });
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>