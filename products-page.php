<?php
$pageTitle = "Retail Polythene Kavera G1000";
$activeNav = "materials";
require_once __DIR__ . '/config/config.php';

// Define related products data in PHP
$relatedProducts = [
    [
        'name' => 'DAMPLAS DPC',
        'price' => 35000,
        'unit' => 'Per Roll',
        'image' => 'https://dummyimage.com/300x200/cccccc/000000&text=DAMPLAS+DPC',
        'rating' => 4.5,
        'reviews' => 12
    ],
    [
        'name' => 'Dr Fixit Powder Waterproof',
        'price' => 45000,
        'unit' => 'Per Bag',
        'image' => 'https://dummyimage.com/300x200/cccccc/000000&text=Dr+Fixit+Powder+Waterproof',
        'rating' => 4.2,
        'reviews' => 8
    ],
    [
        'name' => 'Dr Fixit LW+ Tonic For Cement',
        'price' => 28000,
        'unit' => 'Per Bottle',
        'image' => 'https://dummyimage.com/300x200/cccccc/000000&text=Dr+Fixit+LW%2B+Tonic',
        'rating' => 4.7,
        'reviews' => 15
    ],
    [
        'name' => 'Elephant Barbed Wire',
        'price' => 65000,
        'unit' => 'Per Roll',
        'image' => 'https://dummyimage.com/300x200/cccccc/000000&text=Elephant+Barbed+Wire',
        'rating' => 4.0,
        'reviews' => 6
    ]
];

// Define store outlets
$storeOutlets = [
    [
        'name' => 'Zzimba Online Warehouse (Main Branch)',
        'address' => 'Plot 123, Industrial Area, Kampala',
        'phone' => '+256 700 123456',
        'hours' => 'Mon-Sat: 8:00 AM - 6:00 PM'
    ],
    [
        'name' => 'Kampala Hardware Market',
        'address' => '45 Hardware Avenue, Central Business District',
        'phone' => '+256 700 789012',
        'hours' => 'Mon-Fri: 8:30 AM - 5:30 PM, Sat: 9:00 AM - 3:00 PM'
    ],
    [
        'name' => 'Entebbe Road Outlet',
        'address' => 'Entebbe Road, Next to Fuel Station',
        'phone' => '+256 700 345678',
        'hours' => 'Mon-Sun: 8:00 AM - 7:00 PM'
    ]
];

// Define reviews
$reviews = [
    [
        'name' => 'John Doe',
        'rating' => 4,
        'comment' => 'Great product, exactly what I needed for my foundation work! The quality is consistent and it performs well even in wet conditions.',
        'date' => '2025-01-15',
        'verified' => true
    ],
    [
        'name' => 'Jane Smith',
        'rating' => 5,
        'comment' => 'Excellent quality and quick delivery! This kavera has saved me a lot of trouble with moisture problems. Highly recommended for any construction project.',
        'date' => '2025-02-01',
        'verified' => true
    ]
];

ob_start();
?>

<style>
    .breadcrumb-container {
        background-image: url('https://dummyimage.com/1920x350/aaaaaa/000000&text=Product+Hero+Banner');
        background-size: cover;
        background-position: center;
        position: relative;
        color: white;
        padding: 4rem 0;
    }

    .breadcrumb-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.4));
        z-index: 0;
    }

    .breadcrumb-content {
        position: relative;
        z-index: 1;
    }

    .breadcrumb-links a {
        text-decoration: none;
        color: rgba(255, 255, 255, 0.8);
        transition: color 0.2s ease;
    }

    .breadcrumb-links a:hover {
        color: #fff;
    }

    .product-tabs button.active {
        border-bottom: 3px solid #e53e3e;
        color: #e53e3e;
        font-weight: 600;
    }

    .star-rating {
        display: inline-flex;
        gap: 0.15rem;
        cursor: pointer;
    }

    .star-rating i {
        font-size: 1.25rem;
    }

    .star-rating i.hovered {
        color: #f59e0b;
    }

    .review-item {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-author {
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
    }

    .review-date {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .verified-badge {
        background-color: #10b981;
        color: white;
        font-size: 0.7rem;
        padding: 0.1rem 0.5rem;
        border-radius: 9999px;
        margin-left: 0.5rem;
    }

    /* Product image gallery */
    .product-gallery {
        position: relative;
    }

    .gallery-thumbs {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .gallery-thumb {
        width: 70px;
        height: 70px;
        border: 2px solid transparent;
        border-radius: 0.375rem;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
    }

    .gallery-thumb.active {
        border-color: #e53e3e;
    }

    .gallery-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Related products slider */
    .related-product-card {
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .related-product-card:hover {
        transform: translateY(-5px);
    }

    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #e53e3e;
        color: white;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .product-price {
        font-weight: 700;
        font-size: 1.25rem;
        color: #e53e3e;
    }

    .product-unit {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: normal;
    }

    .product-rating {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }

    .product-rating i {
        color: #f59e0b;
        font-size: 0.875rem;
    }

    .product-rating span {
        color: #6b7280;
        font-size: 0.875rem;
    }

    /* Quantity selector */
    .quantity-selector {
        display: flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        overflow: hidden;
        width: fit-content;
    }

    .quantity-btn {
        background-color: #f3f4f6;
        border: none;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .quantity-btn:hover {
        background-color: #e5e7eb;
    }

    .quantity-input {
        width: 3rem;
        text-align: center;
        border: none;
        padding: 0.5rem 0;
        -moz-appearance: textfield;
    }

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Animation for loading */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }

    /* Slider styles */
    .slider-container {
        position: relative;
        padding: 0 2rem;
    }

    .slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        z-index: 10;
        transition: all 0.2s;
    }

    .slider-arrow:hover {
        background-color: #f3f4f6;
    }

    .slider-arrow.prev {
        left: 0;
    }

    .slider-arrow.next {
        right: 0;
    }

    .slider {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .slider {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .slider {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

<div class="breadcrumb-container relative">
    <div class="breadcrumb-overlay"></div>
    <div class="container mx-auto px-4 breadcrumb-content">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Retail Polythene Kavera G1000</h1>
        <nav class="breadcrumb-links text-sm space-x-2">
            <a href="<?= BASE_URL ?>" class="hover:text-white">Zzimba Online</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>materials-yard" class="hover:text-white">Building Materials</a>
            <span>/</span>
            <span class="text-white font-semibold">Retail Polythene Kavera G1000</span>
        </nav>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="product-gallery">
            <div class="relative rounded-xl overflow-hidden shadow-lg">
                <span class="product-badge">POPULAR</span>
                <img
                    id="main-product-image"
                    src="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+G1000"
                    alt="Retail Polythene Kavera G1000"
                    class="w-full rounded-xl" />
            </div>
            <div class="gallery-thumbs">
                <div class="gallery-thumb active" data-image="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+G1000">
                    <img src="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+G1000" alt="Main view">
                </div>
                <div class="gallery-thumb" data-image="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Side">
                    <img src="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Side" alt="Side view">
                </div>
                <div class="gallery-thumb" data-image="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Close">
                    <img src="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Close" alt="Close-up view">
                </div>
                <div class="gallery-thumb" data-image="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Usage">
                    <img src="https://dummyimage.com/600x400/cccccc/000000&text=Kavera+Usage" alt="Usage example">
                </div>
            </div>
        </div>
        <div>
            <div class="bg-gray-50 px-6 py-2 rounded-lg inline-block mb-3">
                <span class="text-sm font-medium text-gray-500">Category:</span>
                <a href="#" class="text-sm font-semibold text-red-600 hover:underline ml-1">Hardware Materials</a>
            </div>

            <h2 class="text-2xl md:text-3xl font-bold mb-3 text-gray-800">
                Retail Polythene Kavera G1000
            </h2>

            <div class="flex items-center space-x-4 mb-4 text-sm">
                <div class="flex items-center">
                    <div class="star-rating text-yellow-500 mr-1">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="text-gray-600">(<?= count($reviews) ?> Reviews)</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-eye mr-1"></i>
                    <span>55 Views</span>
                </div>
                <div class="flex items-center text-green-600">
                    <i class="fas fa-check-circle mr-1"></i>
                    <span>In Stock</span>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
                <p class="text-gray-700 leading-relaxed mb-4">
                    A piece of GENERIC Polythene Sheeting locally known as kavera measured and cut to lengths per meter as desired. Strong plastic sheet for protective wrapping and damp proofing of foundations.
                </p>

                <div class="flex flex-wrap gap-4 mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-shield-alt text-gray-500 mr-2"></i>
                        <span class="text-sm text-gray-600">1000 Gauge Thickness</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-ruler text-gray-500 mr-2"></i>
                        <span class="text-sm text-gray-600">Sold Per Linear Meter</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-tint text-gray-500 mr-2"></i>
                        <span class="text-sm text-gray-600">Waterproof</span>
                    </div>
                </div>

                <div class="text-3xl text-red-600 font-bold mb-4">
                    UGX 2,000 <span class="text-base font-normal ml-2 text-gray-500">1 LM Piece</span>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn" id="decrease-quantity">
                            <i class="fas fa-minus text-sm"></i>
                        </button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1">
                        <button type="button" class="quantity-btn" id="increase-quantity">
                            <i class="fas fa-plus text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4 mb-6">
                    <button class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center flex-1 md:flex-none">
                        <i class="fas fa-shopping-cart mr-2"></i> Buy Now
                    </button>
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center flex-1 md:flex-none">
                        <i class="fas fa-tag mr-2"></i> Sell
                    </button>
                    <button class="border border-gray-300 bg-white text-gray-700 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-5">
                <h3 class="font-semibold mb-3 text-gray-800">Share this product</h3>
                <div class="flex items-center space-x-3">
                    <button onclick="shareOnSocial('x')" class="w-9 h-9 rounded-full bg-gray-800 text-white flex items-center justify-center hover:bg-gray-700 transition-colors">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button onclick="shareOnSocial('facebook')" class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition-colors">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button onclick="shareOnSocial('whatsapp')" class="w-9 h-9 rounded-full bg-green-500 text-white flex items-center justify-center hover:bg-green-600 transition-colors">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                    <button onclick="shareOnSocial('linkedin')" class="w-9 h-9 rounded-full bg-blue-700 text-white flex items-center justify-center hover:bg-blue-800 transition-colors">
                        <i class="fab fa-linkedin-in"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-8">
    <div class="product-tabs flex space-x-6 border-b border-gray-300 mb-6">
        <button
            class="pb-3 text-gray-700 focus:outline-none active font-medium text-lg"
            data-tab-target="description-tab">
            Description
        </button>
        <button
            class="pb-3 text-gray-700 focus:outline-none font-medium text-lg"
            data-tab-target="reviews-tab">
            Reviews (<?= count($reviews) ?>)
        </button>
        <button
            class="pb-3 text-gray-700 focus:outline-none font-medium text-lg"
            data-tab-target="store-tab">
            Store Outlets
        </button>
    </div>

    <div id="description-tab" class="tab-content block">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">Product Description</h3>
            <div class="text-gray-700 leading-relaxed space-y-4">
                <p>
                    A piece of GENERIC Polythene Sheeting locally known as kavera measured and cut out to lengths per meter as desired. Strong Plastic Sheet, 1000 Gauge Poly Sheeting. Used for protective wrapping, damp proofing of foundations, and more.
                </p>
                <p>
                    This high-quality polythene sheeting is essential for construction projects where moisture protection is required. The G1000 grade offers excellent durability and resistance to tearing, making it ideal for foundation work.
                </p>

                <h4 class="text-lg font-medium mt-6 mb-2 text-gray-800">Key Features:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>1000 gauge thickness for superior durability</li>
                    <li>Waterproof and moisture resistant</li>
                    <li>Flexible and easy to cut to size</li>
                    <li>UV stabilized for longer outdoor life</li>
                    <li>Ideal for damp proofing foundations</li>
                </ul>

                <h4 class="text-lg font-medium mt-6 mb-2 text-gray-800">Applications:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Foundation damp proofing</li>
                    <li>Temporary weather protection</li>
                    <li>Dust and debris control</li>
                    <li>Material protection during construction</li>
                    <li>Moisture barrier for concrete curing</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="reviews-tab" class="tab-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Customer Reviews</h3>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= count($reviews) ?> Reviews
                        </span>
                    </div>

                    <div id="reviews-list" class="mb-6 max-h-[500px] overflow-y-auto pr-2 space-y-6">
                        <?php if (count($reviews) === 0): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="far fa-comment-dots text-4xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No reviews yet</p>
                                <p class="text-gray-500 text-sm mt-1">Be the first to review this product</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item fade-in">
                                    <div class="review-author">
                                        <?= htmlspecialchars($review['name']) ?>
                                        <?php if ($review['verified']): ?>
                                            <span class="verified-badge">Verified Purchase</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="review-date"><?= $review['date'] ?></div>
                                    <div class="mb-2 text-yellow-500">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-gray-700"><?= htmlspecialchars($review['comment']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-xl shadow-sm p-6 sticky top-4">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">Write a Review</h4>
                    <form id="review-form" class="space-y-4">
                        <div>
                            <label for="reviewerName" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                            <input
                                type="text"
                                id="reviewerName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Enter your name"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating</label>
                            <div id="review-stars" class="star-rating text-gray-400">
                                <i class="far fa-star" data-value="1"></i>
                                <i class="far fa-star" data-value="2"></i>
                                <i class="far fa-star" data-value="3"></i>
                                <i class="far fa-star" data-value="4"></i>
                                <i class="far fa-star" data-value="5"></i>
                            </div>
                            <input type="hidden" id="reviewRating" value="0">
                        </div>
                        <div>
                            <label for="reviewComment" class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
                            <textarea
                                id="reviewComment"
                                rows="4"
                                maxlength="200"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                placeholder="Share your experience with this product..."
                                required></textarea>
                            <div class="text-right text-xs text-gray-500 mt-1">
                                <span id="char-count">200</span> characters left
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="w-full bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center">
                            <i class="far fa-paper-plane mr-2"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="store-tab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Available Store Outlets</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($storeOutlets as $outlet): ?>
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                        <h4 class="font-semibold text-lg mb-2 text-gray-800"><?= htmlspecialchars($outlet['name']) ?></h4>
                        <div class="space-y-2 text-gray-600">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-3 text-red-500"></i>
                                <span><?= htmlspecialchars($outlet['address']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone-alt mt-1 mr-3 text-red-500"></i>
                                <span><?= htmlspecialchars($outlet['phone']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-clock mt-1 mr-3 text-red-500"></i>
                                <span><?= htmlspecialchars($outlet['hours']) ?></span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="text-red-600 hover:text-red-700 font-medium flex items-center">
                                <i class="fas fa-directions mr-2"></i> Get Directions
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">You May Also Like</h2>
        <div class="flex space-x-2">
            <div class="slider-arrow prev">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="slider-arrow next">
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </div>

    <div class="slider-container">
        <div class="slider">
            <?php foreach ($relatedProducts as $product): ?>
                <div class="p-2">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden related-product-card h-full">
                        <div class="relative">
                            <img
                                src="<?= $product['image'] ?>"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                                class="w-full h-48 object-cover">
                            <div class="product-badge">HOT</div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-800 mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($product['rating'])): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $product['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span>(<?= $product['reviews'] ?>)</span>
                            </div>
                            <div class="mt-3 mb-4">
                                <span class="product-price">UGX <?= number_format($product['price']) ?></span>
                                <span class="product-unit"> / <?= $product['unit'] ?></span>
                            </div>
                            <div class="flex space-x-2">
                                <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center flex-1 justify-center">
                                    <i class="fas fa-shopping-cart mr-1"></i> Buy
                                </button>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center flex-1 justify-center">
                                    <i class="fas fa-tag mr-1"></i> Sell
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.product-tabs button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('block');
            });
            btn.classList.add('active');
            const targetId = btn.getAttribute('data-tab-target');
            const targetContent = document.getElementById(targetId);
            targetContent.classList.remove('hidden');
            targetContent.classList.add('block');
        });
    });

    // Reviews functionality
    let reviews = <?= json_encode($reviews) ?>;

    function renderReviews() {
        const reviewsList = document.getElementById('reviews-list');
        reviewsList.innerHTML = '';

        if (reviews.length === 0) {
            reviewsList.innerHTML = `
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-3">
                        <i class="far fa-comment-dots text-4xl"></i>
                    </div>
                    <p class="text-gray-600 font-medium">No reviews yet</p>
                    <p class="text-gray-500 text-sm mt-1">Be the first to review this product</p>
                </div>
            `;
            return;
        }

        reviews.forEach(review => {
            const reviewItem = document.createElement('div');
            reviewItem.classList.add('review-item', 'fade-in');

            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    starsHTML += '<i class="fas fa-star"></i> ';
                } else {
                    starsHTML += '<i class="far fa-star"></i> ';
                }
            }

            reviewItem.innerHTML = `
                <div class="review-author">
                    ${review.name}
                    ${review.verified ? '<span class="verified-badge">Verified Purchase</span>' : ''}
                </div>
                <div class="review-date">${review.date}</div>
                <div class="mb-2 text-yellow-500">${starsHTML}</div>
                <p class="text-gray-700">${review.comment}</p>
            `;

            reviewsList.appendChild(reviewItem);
        });
    }

    // Star rating functionality
    const starElements = document.querySelectorAll('#review-stars i');
    const ratingInput = document.getElementById('reviewRating');

    starElements.forEach(star => {
        star.addEventListener('mouseover', () => {
            const val = parseInt(star.getAttribute('data-value'));
            starElements.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-value'));
                s.classList.remove('hovered');
                if (sVal <= val) {
                    s.classList.add('hovered');
                }
            });
        });

        star.addEventListener('mouseout', () => {
            starElements.forEach(s => s.classList.remove('hovered'));
        });

        star.addEventListener('click', () => {
            const val = parseInt(star.getAttribute('data-value'));
            ratingInput.value = val;
            starElements.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-value'));
                if (sVal <= val) {
                    s.classList.remove('far');
                    s.classList.add('fas', 'text-yellow-500');
                } else {
                    s.classList.remove('fas', 'text-yellow-500');
                    s.classList.add('far');
                }
            });
        });
    });

    // Review form submission
    document.getElementById('review-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('reviewerName').value.trim();
        const rating = parseInt(document.getElementById('reviewRating').value);
        const comment = document.getElementById('reviewComment').value.trim();

        if (!name || !comment || rating < 1) {
            alert('Please fill all fields and select a rating.');
            return;
        }

        const newReview = {
            name: name,
            rating: rating,
            comment: comment,
            date: new Date().toISOString().split('T')[0],
            verified: true
        };

        reviews.unshift(newReview);
        renderReviews();

        // Reset form
        document.getElementById('reviewerName').value = '';
        document.getElementById('reviewRating').value = 0;
        document.getElementById('reviewComment').value = '';
        document.getElementById('char-count').textContent = '200';

        starElements.forEach(s => {
            s.classList.remove('fas', 'text-yellow-500', 'hovered');
            s.classList.add('far');
        });

        // Update reviews count in tab
        const reviewsTabButton = document.querySelector('[data-tab-target="reviews-tab"]');
        reviewsTabButton.textContent = `Reviews (${reviews.length})`;

        alert('Thank you for your review!');
    });

    // Character counter for review
    const reviewComment = document.getElementById('reviewComment');
    const charCount = document.getElementById('char-count');

    reviewComment.addEventListener('input', () => {
        const remaining = 200 - reviewComment.value.length;
        charCount.textContent = remaining;
    });

    // Quantity selector
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-quantity');
    const increaseBtn = document.getElementById('increase-quantity');

    decreaseBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });

    increaseBtn.addEventListener('click', () => {
        const currentValue = parseInt(quantityInput.value);
        quantityInput.value = currentValue + 1;
    });

    // Product gallery
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainImage = document.getElementById('main-product-image');

    galleryThumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const imageUrl = thumb.getAttribute('data-image');
            mainImage.src = imageUrl;

            galleryThumbs.forEach(t => t.classList.remove('active'));
            thumb.classList.add('active');
        });
    });

    // Social sharing
    function shareOnSocial(platform) {
        const pageUrl = encodeURIComponent(window.location.href);
        const text = encodeURIComponent("Check out this Retail Polythene Kavera G1000 on Zzimba Online!");
        let shareUrl = "";

        switch (platform) {
            case 'x':
                shareUrl = `https://twitter.com/intent/tweet?url=${pageUrl}&text=${text}`;
                break;
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${pageUrl}`;
                break;
            case 'whatsapp':
                shareUrl = `https://api.whatsapp.com/send?text=${text}%20${pageUrl}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${pageUrl}`;
                break;
        }

        window.open(shareUrl, '_blank');
    }

    // Related products slider
    document.addEventListener('DOMContentLoaded', () => {
        renderReviews();

        // Slider navigation
        const prevArrow = document.querySelector('.slider-arrow.prev');
        const nextArrow = document.querySelector('.slider-arrow.next');
        const slider = document.querySelector('.slider');
        const sliderItems = document.querySelectorAll('.slider .p-2');

        let currentPosition = 0;
        const itemsPerView = window.innerWidth < 640 ? 1 : window.innerWidth < 1024 ? 2 : 4;
        const maxPosition = Math.max(0, sliderItems.length - itemsPerView);

        function updateSliderPosition() {
            // No need to transform in grid layout
        }

        prevArrow.addEventListener('click', () => {
            if (currentPosition > 0) {
                currentPosition--;
                updateSliderPosition();
            }
        });

        nextArrow.addEventListener('click', () => {
            if (currentPosition < maxPosition) {
                currentPosition++;
                updateSliderPosition();
            }
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>