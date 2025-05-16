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
        'image' => 'https://placehold.co/600x400/e2e8f0/1e293b?text=DAMPLAS+DPC',
        'rating' => 4.5,
        'reviews' => 12
    ],
    [
        'name' => 'Dr Fixit Powder Waterproof',
        'price' => 45000,
        'unit' => 'Per Bag',
        'image' => 'https://placehold.co/600x400/e2e8f0/1e293b?text=Dr+Fixit+Powder',
        'rating' => 4.2,
        'reviews' => 8
    ],
    [
        'name' => 'Dr Fixit LW+ Tonic For Cement',
        'price' => 28000,
        'unit' => 'Per Bottle',
        'image' => 'https://placehold.co/600x400/e2e8f0/1e293b?text=Dr+Fixit+LW%2B',
        'rating' => 4.7,
        'reviews' => 15
    ],
    [
        'name' => 'Elephant Barbed Wire',
        'price' => 65000,
        'unit' => 'Per Roll',
        'image' => 'https://placehold.co/600x400/e2e8f0/1e293b?text=Elephant+Barbed+Wire',
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

<!-- Only keeping custom CSS that can't be converted to Tailwind -->
<style>
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

    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
    }

    /* Auto-scrolling gallery */
    .gallery-container {
        position: relative;
        overflow: hidden;
    }

    .gallery-scroll {
        display: flex;
        transition: transform 0.5s ease;
    }

    .gallery-scroll img {
        flex-shrink: 0;
    }

    /* Line clamp for description */
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Share buttons styling from vendor-profile.php */
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
</style>

<!-- Hero Banner with Breadcrumbs - Matching vendor-profile.php style -->
<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900 to-gray-800 z-10"></div>
    <img src="https://placehold.co/1920x640/334155/f8fafc?text=Polythene+Kavera+G1000" alt="Product Hero Banner"
        class="w-full h-full object-cover opacity-20">
    <div class="container mx-auto px-4 absolute inset-0 flex flex-col justify-start pt-8 md:pt-12 z-20">
        <h1 class="text-xl md:text-3xl font-bold text-white mb-4">Retail Polythene Kavera G1000</h1>
        <nav class="flex text-xs md:text-sm text-gray-300 overflow-hidden whitespace-nowrap">
            <a href="<?= BASE_URL ?>" class="hover:text-white transition-colors truncate max-w-[30%]">Zzimba Online</a>
            <span class="mx-2">/</span>
            <a href="<?= BASE_URL ?>materials-yard"
                class="hover:text-white transition-colors truncate max-w-[30%]">Building Materials</a>
            <span class="mx-2">/</span>
            <span class="text-white font-medium truncate max-w-[40%]">Retail Polythene Kavera G1000</span>
        </nav>
    </div>
</div>

<!-- Main Product Section - Overlapping the hero with negative margin -->
<div class="container mx-auto px-4 -mt-10 lg:-mt-20 relative z-30">
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Product Gallery with Auto-scroll -->
            <div class="space-y-6">
                <div class="relative rounded-2xl overflow-hidden bg-white shadow-lg">
                    <span
                        class="absolute top-4 right-4 bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded-full z-10">POPULAR</span>
                    <img id="main-product-image" src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+G1000"
                        alt="Retail Polythene Kavera G1000" class="w-full h-auto object-cover rounded-2xl" />
                </div>
                <div class="gallery-container">
                    <div class="gallery-scroll" id="gallery-scroll">
                        <div class="gallery-thumb active cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-rose-600 mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+G1000">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+G1000" alt="Main view"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-rose-600 transition-colors mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Side">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Side" alt="Side view"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-rose-600 transition-colors mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Close">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Close" alt="Close-up view"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-rose-600 transition-colors mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Usage">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Usage" alt="Usage example"
                                class="w-full h-full object-cover">
                        </div>
                        <!-- Duplicate images for continuous scrolling -->
                        <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-rose-600 transition-colors mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+G1000">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+G1000" alt="Main view"
                                class="w-full h-full object-cover">
                        </div>
                        <div class="gallery-thumb cursor-pointer w-20 h-20 rounded-lg overflow-hidden border-2 border-transparent hover:border-rose-600 transition-colors mx-1"
                            data-image="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Side">
                            <img src="https://placehold.co/800x600/e2e8f0/1e293b?text=Kavera+Side" alt="Side view"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <div class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                    <span class="font-medium text-gray-500">Category:</span>
                    <a href="#" class="font-semibold text-rose-600 hover:underline ml-1">Hardware Materials</a>
                </div>

                <h2 class="text-3xl font-bold text-gray-900">
                    Retail Polythene Kavera G1000
                </h2>

                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center">
                        <div class="flex text-amber-400 mr-1">
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
                    <div class="flex items-center text-emerald-600">
                        <i class="fas fa-check-circle mr-1"></i>
                        <span>In Stock</span>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                    <!-- Description with 2-line limit and ellipsis -->
                    <p class="text-gray-700 leading-relaxed mb-6 line-clamp-2">
                        A piece of GENERIC Polythene Sheeting locally known as kavera measured and cut to lengths per
                        meter as desired. Strong plastic sheet for protective wrapping and damp proofing of foundations.
                        This high-quality polythene sheeting is essential for construction projects where moisture
                        protection is required.
                    </p>

                    <!-- Brand name instead of features -->
                    <div class="flex items-center mb-6">
                        <span class="text-sm font-medium text-gray-500 mr-2">Brand:</span>
                        <span class="text-sm font-semibold text-gray-800">GENERIC Construction Materials</span>
                    </div>

                    <div class="text-3xl text-rose-600 font-bold mb-6">
                        UGX 2,000 <span class="text-base font-normal ml-2 text-gray-500">1 LM Piece</span>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden w-fit">
                            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors"
                                id="decrease-quantity">
                                <i class="fas fa-minus text-sm"></i>
                            </button>
                            <input type="number" id="quantity"
                                class="quantity-input w-12 text-center border-none focus:ring-0" value="1" min="1">
                            <button type="button" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 transition-colors"
                                id="increase-quantity">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <button
                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1 md:flex-none">
                            <i class="fas fa-shopping-cart mr-2"></i> Buy Now
                        </button>
                        <button
                            class="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center flex-1 md:flex-none">
                            <i class="fas fa-tag mr-2"></i> Sell
                        </button>
                        <button
                            class="border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 p-3 rounded-lg transition-colors">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>

                <!-- Share buttons styled like vendor-profile.php -->
                <div class="share-container">
                    <span class="share-label">SHARE</span>
                    <div class="share-buttons">
                        <button onclick="copyLink()" class="share-button" title="Copy link">
                            <i class="fa-solid fa-link"></i>
                            <span class="tooltip">Copy link to clipboard</span>
                        </button>
                        <button onclick="shareOnWhatsApp()" class="share-button" title="Share on WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span class="tooltip">Share this profile on WhatsApp</span>
                        </button>
                        <button onclick="shareOnFacebook()" class="share-button" title="Share on Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                            <span class="tooltip">Share this profile on Facebook</span>
                        </button>
                        <button onclick="shareOnTwitter()" class="share-button" title="Share on Twitter/X">
                            <i class="fa-brands fa-x-twitter"></i>
                            <span class="tooltip">Post this on your X</span>
                        </button>
                        <button onclick="shareOnLinkedIn()" class="share-button" title="Share on LinkedIn">
                            <i class="fa-brands fa-linkedin-in"></i>
                            <span class="tooltip">Share on LinkedIn</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Tabs Section -->
<div class="container mx-auto px-4 py-8">
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            <button class="border-rose-600 text-rose-600 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="description-tab">
                <i class="fa-solid fa-circle-info mr-2"></i> Description
            </button>
            <button
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="reviews-tab">
                <i class="fa-solid fa-star mr-2"></i> Reviews (<?= count($reviews) ?>)
            </button>
            <button
                class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap tab-button"
                data-tab="store-tab">
                <i class="fa-solid fa-store mr-2"></i> Store Outlets
            </button>
        </nav>
    </div>

    <!-- Description Tab -->
    <div id="description-tab" class="tab-content block">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Product Description</h3>
            <div class="text-gray-700 leading-relaxed space-y-4">
                <p>
                    A piece of GENERIC Polythene Sheeting locally known as kavera measured and cut out to lengths per
                    meter as desired. Strong Plastic Sheet, 1000 Gauge Poly Sheeting. Used for protective wrapping, damp
                    proofing of foundations, and more.
                </p>
                <p>
                    This high-quality polythene sheeting is essential for construction projects where moisture
                    protection is required. The G1000 grade offers excellent durability and resistance to tearing,
                    making it ideal for foundation work.
                </p>

                <h4 class="text-lg font-medium mt-8 mb-3 text-gray-800">Key Features:</h4>
                <ul class="list-disc list-inside space-y-2 pl-2">
                    <li>1000 gauge thickness for superior durability</li>
                    <li>Waterproof and moisture resistant</li>
                    <li>Flexible and easy to cut to size</li>
                    <li>UV stabilized for longer outdoor life</li>
                    <li>Ideal for damp proofing foundations</li>
                </ul>

                <h4 class="text-lg font-medium mt-8 mb-3 text-gray-800">Applications:</h4>
                <ul class="list-disc list-inside space-y-2 pl-2">
                    <li>Foundation damp proofing</li>
                    <li>Temporary weather protection</li>
                    <li>Dust and debris control</li>
                    <li>Material protection during construction</li>
                    <li>Moisture barrier for concrete curing</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Reviews Tab -->
    <div id="reviews-tab" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-800">Customer Reviews</h3>
                        <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            <?= count($reviews) ?> Reviews
                        </span>
                    </div>

                    <div id="reviews-list" class="mb-6 max-h-[500px] overflow-y-auto pr-2 space-y-6">
                        <?php if (count($reviews) === 0): ?>
                            <div class="text-center py-12">
                                <div class="text-gray-400 mb-3">
                                    <i class="far fa-comment-dots text-4xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No reviews yet</p>
                                <p class="text-gray-500 text-sm mt-1">Be the first to review this product</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-b border-gray-200 pb-6 mb-6 last:border-0 last:pb-0 last:mb-0 fade-in">
                                    <div class="flex items-center mb-1">
                                        <span
                                            class="font-semibold text-gray-800"><?= htmlspecialchars($review['name']) ?></span>
                                        <?php if ($review['verified']): ?>
                                            <span
                                                class="ml-2 bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-0.5 rounded-full">Verified
                                                Purchase</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-gray-500 text-sm mb-2"><?= $review['date'] ?></div>
                                    <div class="flex text-amber-400 mb-3">
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
                <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8 sticky top-4">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">Write a Review</h4>
                    <form id="review-form" class="space-y-4">
                        <div>
                            <label for="reviewerName" class="block text-sm font-medium text-gray-700 mb-1">Your
                                Name</label>
                            <input type="text" id="reviewerName"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                placeholder="Enter your name" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your Rating</label>
                            <div id="review-stars" class="flex text-gray-400">
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="1"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="2"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="3"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="4"></i>
                                <i class="far fa-star cursor-pointer hover:text-amber-400 transition-colors"
                                    data-value="5"></i>
                            </div>
                            <input type="hidden" id="reviewRating" value="0">
                        </div>
                        <div>
                            <label for="reviewComment" class="block text-sm font-medium text-gray-700 mb-1">Your
                                Review</label>
                            <textarea id="reviewComment" rows="4" maxlength="200"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                placeholder="Share your experience with this product..." required></textarea>
                            <div class="text-right text-xs text-gray-500 mt-1">
                                <span id="char-count">200</span> characters left
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center justify-center">
                            <i class="far fa-paper-plane mr-2"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Tab -->
    <div id="store-tab" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm p-6 lg:p-8">
            <h3 class="text-xl font-semibold mb-6 text-gray-800">Available Store Outlets</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($storeOutlets as $outlet): ?>
                    <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow bg-white">
                        <h4 class="font-semibold text-lg mb-3 text-gray-800"><?= htmlspecialchars($outlet['name']) ?></h4>
                        <div class="space-y-3 text-gray-600">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['address']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone-alt mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['phone']) ?></span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-clock mt-1 mr-3 text-rose-500"></i>
                                <span><?= htmlspecialchars($outlet['hours']) ?></span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button
                                class="text-rose-600 hover:text-rose-700 font-medium flex items-center transition-colors">
                                <i class="fas fa-directions mr-2"></i> Get Directions
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<div class="bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl font-bold text-gray-800">You May Also Like</h2>
            <div class="flex space-x-2">
                <button
                    class="slider-arrow prev w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button
                    class="slider-arrow next w-10 h-10 rounded-full bg-white shadow-md flex items-center justify-center hover:bg-gray-50 transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($relatedProducts as $product): ?>
                <div class="group">
                    <div
                        class="bg-white rounded-xl shadow-sm overflow-hidden h-full transform transition-transform duration-300 group-hover:-translate-y-1">
                        <div class="relative">
                            <img src="<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>"
                                class="w-full h-48 object-cover">
                            <div
                                class="absolute top-3 right-3 bg-rose-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                                HOT</div>
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-800 mb-2 line-clamp-2"><?= htmlspecialchars($product['name']) ?>
                            </h3>
                            <div class="flex text-amber-400 mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= floor($product['rating'])): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i - 0.5 <= $product['rating']): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <span class="text-gray-500 text-sm ml-1">(<?= $product['reviews'] ?>)</span>
                            </div>
                            <div class="mt-3 mb-4">
                                <span class="text-xl font-bold text-rose-600">UGX
                                    <?= number_format($product['price']) ?></span>
                                <span class="text-sm text-gray-500"> / <?= $product['unit'] ?></span>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-sm">
                                    <i class="fas fa-shopping-cart mr-1"></i> Buy
                                </button>
                                <button
                                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center flex-1 justify-center text-sm">
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
    // Tab switching functionality - Fixed to work properly
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                // Remove active state from all buttons
                tabButtons.forEach(b => {
                    b.classList.remove('text-rose-600', 'border-rose-600');
                    b.classList.add('border-transparent', 'text-gray-500');
                });

                // Set active state for clicked button
                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('text-rose-600', 'border-rose-600');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                    content.classList.remove('block');
                });

                // Show corresponding tab content
                const tabId = this.getAttribute('data-tab');
                const targetContent = document.getElementById(tabId);
                targetContent.classList.remove('hidden');
                targetContent.classList.add('block');
            });
        });
    });

    // Reviews functionality
    let reviews = <?= json_encode($reviews) ?>;

    function renderReviews() {
        const reviewsList = document.getElementById('reviews-list');
        reviewsList.innerHTML = '';

        if (reviews.length === 0) {
            reviewsList.innerHTML = `
                <div class="text-center py-12">
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
            reviewItem.classList.add('border-b', 'border-gray-200', 'pb-6', 'mb-6', 'last:border-0', 'last:pb-0', 'last:mb-0', 'fade-in');

            let starsHTML = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= review.rating) {
                    starsHTML += '<i class="fas fa-star"></i> ';
                } else {
                    starsHTML += '<i class="far fa-star"></i> ';
                }
            }

            reviewItem.innerHTML = `
                <div class="flex items-center mb-1">
                    <span class="font-semibold text-gray-800">${review.name}</span>
                    ${review.verified ? '<span class="ml-2 bg-emerald-100 text-emerald-800 text-xs font-medium px-2 py-0.5 rounded-full">Verified Purchase</span>' : ''}
                </div>
                <div class="text-gray-500 text-sm mb-2">${review.date}</div>
                <div class="flex text-amber-400 mb-3">${starsHTML}</div>
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
                if (sVal <= val) {
                    s.classList.add('text-amber-400');
                }
            });
        });

        star.addEventListener('mouseout', () => {
            if (ratingInput.value === '0') {
                starElements.forEach(s => s.classList.remove('text-amber-400'));
            } else {
                starElements.forEach(s => {
                    const sVal = parseInt(s.getAttribute('data-value'));
                    if (sVal > parseInt(ratingInput.value)) {
                        s.classList.remove('text-amber-400');
                    }
                });
            }
        });

        star.addEventListener('click', () => {
            const val = parseInt(star.getAttribute('data-value'));
            ratingInput.value = val;
            starElements.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-value'));
                s.classList.remove('fas', 'far', 'text-amber-400');
                if (sVal <= val) {
                    s.classList.add('fas', 'text-amber-400');
                } else {
                    s.classList.add('far');
                }
            });
        });
    });

    // Review form submission
    document.getElementById('review-form').addEventListener('submit', function (e) {
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
            s.classList.remove('fas', 'text-amber-400');
            s.classList.add('far');
        });

        // Update reviews count in tab
        const reviewsTabButton = document.querySelector('[data-tab="reviews-tab"]');
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

    // Product gallery with auto-scroll
    const galleryThumbs = document.querySelectorAll('.gallery-thumb');
    const mainImage = document.getElementById('main-product-image');
    const galleryScroll = document.getElementById('gallery-scroll');
    let currentScrollPosition = 0;
    let scrollInterval;
    let currentThumbIndex = 0;
    const thumbWidth = 84; // Width + margin

    // Manual thumbnail click
    galleryThumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', () => {
            const imageUrl = thumb.getAttribute('data-image');
            mainImage.src = imageUrl;
            currentThumbIndex = index;

            // Update active state
            galleryThumbs.forEach(t => {
                t.classList.remove('active', 'border-rose-600');
                t.classList.add('border-transparent');
            });
            thumb.classList.add('active', 'border-rose-600');
            thumb.classList.remove('border-transparent');

            // Reset auto-scroll timer when manually clicked
            clearInterval(scrollInterval);
            startAutoScroll();
        });
    });

    // Auto-scroll function
    function startAutoScroll() {
        scrollInterval = setInterval(() => {
            // Move to next thumbnail
            currentThumbIndex = (currentThumbIndex + 1) % galleryThumbs.length;

            // If we're at the end of the original set, loop back
            if (currentThumbIndex >= 4) {
                currentThumbIndex = 0;
            }

            const activeThumb = galleryThumbs[currentThumbIndex];

            if (activeThumb) {
                const imageUrl = activeThumb.getAttribute('data-image');
                mainImage.src = imageUrl;

                // Update active state
                galleryThumbs.forEach(t => {
                    t.classList.remove('active', 'border-rose-600');
                    t.classList.add('border-transparent');
                });
                activeThumb.classList.add('active', 'border-rose-600');
                activeThumb.classList.remove('border-transparent');

                // Scroll the gallery if needed
                if (currentThumbIndex > 1) {
                    galleryScroll.style.transform = `translateX(-${thumbWidth}px)`;
                } else {
                    galleryScroll.style.transform = 'translateX(0)';
                }
            }
        }, 5000);
    }

    // Social sharing functions from vendor-profile.php
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
        const productName = "Retail Polythene Kavera G1000";
        const message = `Check out ${productName} on Zzimba Online: ${currentUrl}`;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`;
        window.open(facebookUrl, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const productName = "Retail Polythene Kavera G1000";
        const message = `Check out ${productName} on Zzimba Online:`;
        const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`;
        window.open(twitterUrl, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const productName = "Retail Polythene Kavera G1000";
        const message = `Check out ${productName} on Zzimba Online.`;
        const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(productName)}&summary=${encodeURIComponent(message)}`;
        window.open(linkedinUrl, '_blank');
    }

    // Toast notification function
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

    // Start auto-scroll on page load
    document.addEventListener('DOMContentLoaded', () => {
        renderReviews();
        startAutoScroll();
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>