<?php
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

$vendorId = $_GET['id'] ?? null;
$storeData = null;

function generateSeoMetaTags($store)
{
    $title = htmlspecialchars($store['name'] ?? 'Vendor Store') . ' | Zzimba Store';
    $description = htmlspecialchars($store['description'] ?? 'Discover quality products and services at ' . ($store['name'] ?? 'this vendor store') . ' on Zzimba Online.');

    // Determine OG image with fallback mechanism
    $ogImage = '';
    if (!empty($store['logo_url'])) {
        $ogImage = BASE_URL . $store['logo_url'];
    } elseif (!empty($store['vendor_cover_url'])) {
        $ogImage = BASE_URL . $store['vendor_cover_url'];
    } else {
        $storeName = urlencode($store['name'] ?? 'Vendor Store');
        $ogImage = "https://placehold.co/1200x630?text={$storeName}";
    }

    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    return [
        'title' => $title,
        'description' => $description,
        'og_title' => $title,
        'og_description' => $description,
        'og_image' => $ogImage,
        'og_url' => $currentUrl,
        'og_type' => 'website'
    ];
}

if ($vendorId) {
    try {
        $storeId = $vendorId;

        $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $stmt->execute([$storeId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($store) {
            $stmt = $pdo->prepare("SELECT * FROM vendor_stores WHERE id = ?");
            $stmt->execute([$storeId]);
            $storeData = $stmt->fetch(PDO::FETCH_ASSOC);

            $seoTags = generateSeoMetaTags($storeData);
            $pageTitle = $seoTags['title'];
        }
    } catch (Exception $e) {
        error_log("Error fetching vendor data: " . $e->getMessage());
    }
}

$isLoggedIn = !empty($_SESSION['user']['logged_in']);

ob_start();
?>

<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden" id="vendor-cover-photo">
    <div id="vendor-cover" class="w-full h-full bg-center bg-cover"></div>
    <div id="cover-edit-button"
        class="absolute top-4 right-4 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>">
        <i class="fas fa-camera"></i>
    </div>
</div>

<div id="loading-state" class="flex flex-col items-center justify-center py-12">
    <div class="border-4 border-gray-200 border-l-red-600 rounded-full w-10 h-10 animate-spin mb-4"></div>
    <p class="text-gray-600">Loading vendor profile...</p>
</div>

<div id="not-found-state"
    class="hidden bg-red-50 border border-red-200 text-red-700 p-8 rounded-lg text-center max-w-2xl mx-auto my-12">
    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
    <h2 class="text-xl font-bold mb-2">Store Not Found or Not Active</h2>
    <p class="mb-4">This store may not exist or has not been activated by an administrator yet.</p>
    <p class="mb-6">If you believe this is an error, please contact the system administrator for assistance.</p>
    <a href="<?= BASE_URL ?>"
        class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Return to Home
    </a>
</div>

<div id="error-state"
    class="hidden bg-red-50 border border-red-200 text-red-700 p-8 rounded-lg text-center max-w-2xl mx-auto my-12">
    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
    <h2 class="text-xl font-bold mb-2">Profile Not Found</h2>
    <p class="mb-4">Sorry, we couldn't find the vendor profile you're looking for.</p>
    <a href="<?= BASE_URL ?>"
        class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors">
        Return to Home
    </a>
</div>

<div id="content-state" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 md:-mt-16 relative z-10">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex flex-col md:flex-row">
            <div class="flex-shrink-0 flex md:block justify-center">
                <div id="vendor-avatar-container" class="relative">
                    <div id="vendor-avatar"
                        class="h-32 w-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-white flex items-center justify-center">
                        <i class="fas fa-store text-gray-400 text-4xl"></i>
                    </div>
                    <div id="avatar-edit-button"
                        class="absolute bottom-0 right-0 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <div class="mt-6 md:mt-0 md:ml-6 flex-grow text-center md:text-left">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h1 id="vendor-name-container" class="text-3xl font-bold text-secondary">
                            <span id="vendor-name">Store Name</span>
                            <i id="edit-name-button"
                                class="fas fa-pen ml-2 text-gray-500 hover:text-red-600 text-sm cursor-pointer transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>"></i>
                        </h1>
                        <p id="vendor-description-container" class="text-gray-600 mt-1">
                            <span id="vendor-description">Premium Construction Materials & Services</span>
                            <i id="edit-description-button"
                                class="fas fa-pen ml-2 text-gray-500 hover:text-red-600 text-sm cursor-pointer transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>"></i>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-y-4 justify-center md:justify-start">
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-calendar-days text-gray-500 mr-2"></i>
                        <span id="vendor-registered" class="text-gray-700">Joined March 2008</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <div id="location-section">
                            <button id="view-location-btn"
                                class="flex items-center text-red-600 text-sm font-medium hover:underline transition-all"
                                onclick="showLocation()">
                                <i class="fa-solid fa-location-dot text-gray-500 mr-2"></i>
                                <span>View Location</span>
                            </button>
                            <div id="location-container" class="hidden">
                                <span id="vendor-location" class="text-gray-700">Building City, BC 12345</span>
                            </div>
                        </div>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-box text-gray-500 mr-2"></i>
                        <span id="product-count" class="text-gray-700">0 Products</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-tags text-gray-500 mr-2"></i>
                        <span id="category-count" class="text-gray-700">0 Categories</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-x-8 gap-y-4 justify-center md:justify-start">
            <div class="flex items-center">
                <div id="vendor-status" class="bg-yellow-300 text-yellow-800 px-3 py-1 rounded-full text-sm">Status
                </div>
                <div id="vendor-operation-type" class="ml-2 bg-red-600 text-white px-3 py-1 rounded-full text-sm">
                    Operation Type</div>
            </div>
            <div class="flex items-center">
                <div class="text-xl font-bold text-secondary">4.8</div>
                <div class="ml-2 flex">
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                    <span class="ml-1 text-sm text-gray-600">(128 reviews)</span>
                </div>
            </div>
            <div class="ml-0 sm:ml-auto flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500">SHARE</span>
                <div class="flex gap-2">
                    <button onclick="copyLink()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-solid fa-link text-xs"></i>
                        <span
                            class="absolute bottom-[-40px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10 before:content-[''] before:absolute before:top-[-4px] before:left-1/2 before:transform before:-translate-x-1/2 before:rotate-45 before:w-2 before:h-2 before:bg-gray-800">Copy
                            link to clipboard</span>
                    </button>
                    <button onclick="shareOnWhatsApp()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-whatsapp text-xs"></i>
                        <span
                            class="absolute bottom-[-40px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10 before:content-[''] before:absolute before:top-[-4px] before:left-1/2 before:transform before:-translate-x-1/2 before:rotate-45 before:w-2 before:h-2 before:bg-gray-800">Share
                            this profile on WhatsApp</span>
                    </button>
                    <button onclick="shareOnFacebook()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-facebook-f text-xs"></i>
                        <span
                            class="absolute bottom-[-40px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10 before:content-[''] before:absolute before:top-[-4px] before:left-1/2 before:transform before:-translate-x-1/2 before:rotate-45 before:w-2 before:h-2 before:bg-gray-800">Share
                            this profile on Facebook</span>
                    </button>
                    <button onclick="shareOnTwitter()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-x-twitter text-xs"></i>
                        <span
                            class="absolute bottom-[-40px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10 before:content-[''] before:absolute before:top-[-4px] before:left-1/2 before:transform before:-translate-x-1/2 before:rotate-45 before:w-2 before:h-2 before:bg-gray-800">Post
                            this on your X</span>
                    </button>
                    <button onclick="shareOnLinkedIn()"
                        class="flex items-center justify-center w-6 h-6 rounded-full text-red-600 border-[1.5px] border-red-600 bg-transparent hover:bg-red-50 hover:-translate-y-0.5 transition-all relative">
                        <i class="fa-brands fa-linkedin-in text-xs"></i>
                        <span
                            class="absolute bottom-[-40px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-opacity z-10 before:content-[''] before:absolute before:top-[-4px] before:left-1/2 before:transform before:-translate-x-1/2 before:rotate-45 before:w-2 before:h-2 before:bg-gray-800">Share
                            on LinkedIn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <main class="py-8">
        <div class="mb-2">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 overflow-x-auto">
                    <button class="border-primary text-primary font-medium py-4 px-1 border-b-2 whitespace-nowrap"
                        data-tab="products">
                        <i class="fa-solid fa-box-open mr-2"></i> Products
                    </button>
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap"
                        data-tab="about">
                        <i class="fa-solid fa-circle-info mr-2"></i> About
                    </button>
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap"
                        data-tab="verification">
                        <i class="fa-solid fa-check-circle mr-2"></i> Verification
                    </button>
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap"
                        data-tab="contact">
                        <i class="fa-solid fa-address-card mr-2"></i> Contact
                    </button>
                </nav>
            </div>
        </div>

        <div id="tab-content">
            <?php
            include_once __DIR__ . '/vendorProfileComponents/products-tab.php';
            include_once __DIR__ . '/vendorProfileComponents/about-tab.php';
            include_once __DIR__ . '/vendorProfileComponents/verification-tab.php';
            include_once __DIR__ . '/vendorProfileComponents/contact-tab.php';
            ?>
        </div>
    </main>
</div>

<!-- Edit Name Modal -->
<div id="edit-name-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
        <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit Store Name</h2>
            <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                onclick="closeModal('edit-name-modal')">&times;</span>
        </div>
        <div class="mb-4">
            <div class="mb-4">
                <label for="store-name-input" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                <input type="text" id="store-name-input"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-name-modal')">Cancel</button>
            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="saveName()">Save</button>
        </div>
    </div>
</div>

<!-- Edit Description Modal -->
<div id="edit-description-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
        <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit Store Description</h2>
            <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                onclick="closeModal('edit-description-modal')">&times;</span>
        </div>
        <div class="mb-4">
            <div class="mb-4">
                <label for="store-description-input" class="block text-sm font-medium text-gray-700 mb-1">Store
                    Description</label>
                <textarea id="store-description-input" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-description-modal')">Cancel</button>
            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="saveDescription()">Save</button>
        </div>
    </div>
</div>

<!-- Edit Logo Modal -->
<div id="edit-logo-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
        <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit Store Logo</h2>
            <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                onclick="closeModal('edit-logo-modal')">&times;</span>
        </div>
        <div class="mb-4">
            <div class="mb-4">
                <label for="logo-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Logo</label>
                <input type="file" id="logo-file-input" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Select a square image for best results. The image will be cropped
                    to a 1:1 ratio.</p>
            </div>
            <div id="cropper-container" class="hidden h-[300px] mb-4">
                <img id="cropper-image" src="https://placehold.co/600x400?text=Image+to+Crop" alt="Image to crop">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-logo-modal')">Cancel</button>
            <button type="button" id="crop-button"
                class="hidden px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="cropAndSaveLogo()">Crop & Save</button>
        </div>
    </div>
</div>

<!-- Edit Cover Photo Modal -->
<div id="edit-cover-modal" class="fixed inset-0 z-[1000] hidden bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-md mx-auto my-8 overflow-y-auto max-h-screen p-5">
        <div class="flex justify-between items-center pb-2 mb-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Edit Cover Photo</h2>
            <span class="text-2xl font-bold text-gray-400 hover:text-gray-900 cursor-pointer"
                onclick="closeModal('edit-cover-modal')">&times;</span>
        </div>
        <div class="mb-4">
            <div class="mb-4">
                <label for="cover-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Cover
                    Photo</label>
                <input type="file" id="cover-file-input" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Select an image that will be cropped to a 3:1 ratio for best
                    results.</p>
            </div>
            <div id="cover-cropper-container" class="hidden h-[300px] mb-4">
                <img id="cover-cropper-image" src="https://placehold.co/1200x400?text=Cover+Image+to+Crop"
                    alt="Image to crop">
            </div>
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-gray-200">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-cover-modal')">Cancel</button>
            <button type="button" id="crop-cover-button"
                class="hidden px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="cropAndSaveCover()">Crop & Save</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">

<script>
    window.openModal = function (modalId) {
        document.getElementById(modalId).style.display = 'block';
    };

    let vendorId = '<?= $vendorId ?>';
    let storeData = null;
    let isOwner = false;
    let storeEmail = '';
    let storePhone = '';
    let currentPage = 1;
    let totalPages = 1;
    let selectedCategories = [];
    let allProducts = [];
    let availableUnits = [];
    let lineItemCount = 0;
    let pendingDeleteId = null;
    let pendingDeleteType = null;
    let categoryStatusChanges = {};
    let cropper = null;
    let coverCropper = null;
    let isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;

    function editCover() {
        openModal('edit-cover-modal');
    }

    function initCoverCropper(image) {
        if (coverCropper) {
            coverCropper.destroy();
        }

        const cropperContainer = document.getElementById('cover-cropper-container');
        cropperContainer.classList.remove('hidden');

        const cropperImage = document.getElementById('cover-cropper-image');
        cropperImage.src = image;

        coverCropper = new Cropper(cropperImage, {
            aspectRatio: 3 / 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false
        });

        document.getElementById('crop-cover-button').classList.remove('hidden');
    }

    function cropAndSaveCover() {
        if (!coverCropper) {
            showToast('Please select an image first', 'error');
            return;
        }

        const canvas = coverCropper.getCroppedCanvas({
            width: 1200,
            height: 400,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            showToast('Failed to crop image', 'error');
            return;
        }

        canvas.toBlob(function (blob) {
            const formData = new FormData();
            formData.append('cover', blob, 'cover.png');

            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadVendorCover`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const updateData = {
                            id: vendorId,
                            temp_cover_path: data.temp_path
                        };

                        return fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(updateData)
                        });
                    } else {
                        throw new Error(data.message || 'Failed to upload cover photo');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const coverUrl = canvas.toDataURL();
                        const coverContainer = document.getElementById('vendor-cover');
                        coverContainer.style.backgroundImage = `url(${coverUrl})`;

                        closeModal('edit-cover-modal');
                        showToast('Cover photo updated successfully');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Failed to update cover photo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating cover photo:', error);
                    showToast('Failed to update cover photo', 'error');
                });
        }, 'image/png');
    }

    function showLocation() {
        const locationContainer = document.getElementById('location-container');
        const viewLocationBtn = document.getElementById('view-location-btn');

        locationContainer.classList.remove('hidden');
        locationContainer.classList.add('animate-fadeIn');

        viewLocationBtn.style.display = 'none';
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
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `*${vendorName}* is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.\n\n${currentUrl}`;
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
        window.open(whatsappUrl, '_blank');
    }

    function shareOnFacebook() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`;
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}&quote=${encodeURIComponent(message)}`;
        window.open(facebookUrl, '_blank');
    }

    function shareOnTwitter() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online!\n\nFollow the link to view our profile and offer of the day.`;
        const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`;
        window.open(twitterUrl, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const message = `${vendorName} is now on Zzimba Online! Follow the link to view our profile and offer of the day.`;
        const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(vendorName)}&summary=${encodeURIComponent(message)}`;
        window.open(linkedinUrl, '_blank');
    }

    function editName() {
        const nameInput = document.getElementById('store-name-input');
        nameInput.value = document.getElementById('vendor-name').textContent;
        openModal('edit-name-modal');
    }

    function saveName() {
        const newName = document.getElementById('store-name-input').value.trim();
        if (!newName) {
            showToast('Store name cannot be empty', 'error');
            return;
        }

        const data = {
            id: vendorId,
            name: newName
        };

        fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('vendor-name').textContent = newName;
                    closeModal('edit-name-modal');
                    showToast('Store name updated successfully');
                } else {
                    showToast(data.error || 'Failed to update store name', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating store name:', error);
                showToast('Failed to update store name', 'error');
            });
    }

    function editDescription() {
        const descriptionInput = document.getElementById('store-description-input');
        descriptionInput.value = document.getElementById('vendor-description').textContent;
        openModal('edit-description-modal');
    }

    function saveDescription() {
        const newDescription = document.getElementById('store-description-input').value.trim();

        const data = {
            id: vendorId,
            description: newDescription
        };

        fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('vendor-description').textContent = newDescription || 'No description provided.';
                    document.getElementById('store-description').textContent = newDescription || 'No description provided.';
                    closeModal('edit-description-modal');
                    showToast('Store description updated successfully');
                } else {
                    showToast(data.error || 'Failed to update store description', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating store description:', error);
                showToast('Failed to update store description', 'error');
            });
    }

    function editLogo() {
        openModal('edit-logo-modal');
    }

    function initCropper(image) {
        if (cropper) {
            cropper.destroy();
        }

        const cropperContainer = document.getElementById('cropper-container');
        cropperContainer.classList.remove('hidden');

        const cropperImage = document.getElementById('cropper-image');
        cropperImage.src = image;

        cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false
        });

        document.getElementById('crop-button').classList.remove('hidden');
    }

    function cropAndSaveLogo() {
        if (!cropper) {
            showToast('Please select an image first', 'error');
            return;
        }

        const canvas = cropper.getCroppedCanvas({
            width: 512,
            height: 512,
            fillColor: '#fff',
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            showToast('Failed to crop image', 'error');
            return;
        }

        canvas.toBlob(function (blob) {
            const formData = new FormData();
            formData.append('logo', blob, 'logo.png');

            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadLogo`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const updateData = {
                            id: vendorId,
                            temp_logo_path: data.temp_path
                        };

                        return fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=updateStore`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(updateData)
                        });
                    } else {
                        throw new Error(data.message || 'Failed to upload logo');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const logoUrl = canvas.toDataURL();
                        const avatarContainer = document.getElementById('vendor-avatar');
                        avatarContainer.innerHTML = '';
                        const logoImg = document.createElement('img');
                        logoImg.src = logoUrl;
                        logoImg.alt = document.getElementById('vendor-name').textContent;
                        logoImg.className = 'w-full h-full object-cover rounded-full';
                        avatarContainer.appendChild(logoImg);

                        closeModal('edit-logo-modal');
                        showToast('Store logo updated successfully');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.error || 'Failed to update store logo', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error updating store logo:', error);
                    showToast('Failed to update store logo', 'error');
                });
        }, 'image/png');
    }

    window.closeModal = function (modalId) {
        document.getElementById(modalId).style.display = 'none';
        document.body.style.overflow = 'auto';

        if (modalId === 'edit-logo-modal') {
            if (window.cropper) {
                window.cropper.destroy();
                window.cropper = null;
            }
            document.getElementById('cropper-container').classList.add('hidden');
            document.getElementById('crop-button').classList.add('hidden');
            document.getElementById('logo-file-input').value = '';
        }

        if (modalId === 'edit-cover-modal') {
            if (window.coverCropper) {
                window.coverCropper.destroy();
                window.coverCropper = null;
            }
            document.getElementById('cover-cropper-container').classList.add('hidden');
            document.getElementById('crop-cover-button').classList.add('hidden');
            document.getElementById('cover-file-input').value = '';
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (vendorId) {
            loadVendorProfile(vendorId);
        } else {
            showError("No vendor ID provided");
        }

        const tabs = document.querySelectorAll('nav button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => {
                    t.classList.remove('border-primary', 'text-primary');
                    t.classList.add('border-transparent', 'text-gray-500');
                });

                this.classList.remove('border-transparent', 'text-gray-500');
                this.classList.add('border-primary', 'text-primary');

                tabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                });

                const tabName = this.getAttribute('data-tab');
                document.getElementById(tabName + '-tab').classList.remove('hidden');

                if (tabName === 'manage') {
                    if (document.querySelector('.manage-subtab-btn[data-subtab="categories"]').classList.contains('border-primary')) {
                        loadCategoriesForManagement();
                    } else {
                        loadProductsForManagement();
                    }
                }
            });
        });

        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function () {
                const tabId = this.getAttribute('data-tab');
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabId).classList.add('active');
            });
        });

        window.addEventListener('click', function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });

        document.getElementById('logo-file-input').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    initCropper(e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('cover-file-input').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    initCoverCropper(e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('edit-name-button').addEventListener('click', editName);
        document.getElementById('edit-description-button').addEventListener('click', editDescription);
        document.getElementById('avatar-edit-button').addEventListener('click', editLogo);
        document.getElementById('cover-edit-button').addEventListener('click', editCover);
    });

    function loadVendorProfile(id) {
        fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=getStoreDetails&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;
                    renderVendorProfile(storeData);
                    populateCategoryFilter(storeData.categories);
                } else {
                    if (data.error === "Store not found or not active") {
                        showNotFoundOrInactive();
                    } else {
                        showError(data.error || "Failed to load vendor profile");
                    }
                }
            })
            .catch(error => {
                console.error('Error loading vendor profile:', error);
                showError("Failed to load vendor profile");
            });
    }

    function showNotFoundOrInactive() {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('not-found-state').classList.remove('hidden');
    }

    function renderVendorProfile(store) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('content-state').classList.remove('hidden');
        document.getElementById('vendor-name').textContent = store.name;
        document.getElementById('vendor-operation-type').textContent = store.nature_of_business_name;

        const statusBadge = document.getElementById('vendor-status');
        const accountStatus = document.getElementById('account-status');

        if (store.status === 'active') {
            statusBadge.className = 'bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = 'Active';
            accountStatus.className = 'inline-block bg-green-100 text-green-800 px-2 py-1 rounded-full text-sm font-medium';
            accountStatus.textContent = 'Active';
        } else if (store.status === 'pending') {
            statusBadge.className = 'bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = 'Pending Verification';
            accountStatus.className = 'inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium';
            accountStatus.textContent = 'Pending';
        } else {
            statusBadge.className = 'bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm';
            statusBadge.textContent = store.status.charAt(0).toUpperCase() + store.status.slice(1);
            accountStatus.className = 'inline-block bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-medium';
            accountStatus.textContent = store.status.charAt(0).toUpperCase() + store.status.slice(1);
        }

        document.getElementById('vendor-location').textContent = `${store.district}, ${store.address}`;
        document.getElementById('vendor-location-contact').textContent = `${store.district}, ${store.address}`;
        document.getElementById('vendor-owner').textContent = store.owner_username;
        document.getElementById('vendor-description').textContent = store.description || 'No description provided.';
        storeEmail = store.business_email;
        storePhone = store.business_phone;

        const regDate = new Date(store.created_at);
        const formattedDate = regDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('vendor-registered').textContent = `Joined ${formattedDate}`;
        document.getElementById('vendor-registered-contact').textContent = formattedDate;

        if (store.owner_current_login) {
            const lastSeen = new Date(store.owner_current_login);
            document.getElementById('vendor-last-seen').textContent = formatTimeAgo(lastSeen);
        } else {
            document.getElementById('vendor-last-seen').textContent = 'Not available';
        }

        if (store.logo_url) {
            const logoImg = document.createElement('img');
            logoImg.src = BASE_URL + store.logo_url;
            logoImg.alt = store.name;
            logoImg.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoImg);
        } else {
            const firstWord = store.name.split(' ')[0];
            const logoPlaceholder = document.createElement('img');
            logoPlaceholder.src = `https://placehold.co/400x400/e5e7eb/6b7280?text=${encodeURIComponent(firstWord)}`;
            logoPlaceholder.alt = store.name;
            logoPlaceholder.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoPlaceholder);
        }

        const coverContainer = document.getElementById('vendor-cover');
        if (store.vendor_cover_url) {
            coverContainer.style.backgroundImage = `url(${BASE_URL + store.vendor_cover_url})`;
        } else {
            coverContainer.style.backgroundImage = `url(https://placehold.co/1200x400/e5e7eb/6b7280?text=${encodeURIComponent(store.name)})`;
        }

        const productCount = Number(store.product_count) || 0;
        const categoryCount = Number(store.category_count) || 0;
        const productLabel = productCount === 1 ? 'Product' : 'Products';
        const categoryLabel = categoryCount === 1 ? 'Category' : 'Categories';
        document.getElementById('product-count').textContent = `${productCount} ${productLabel}`;
        document.getElementById('category-count').textContent = `${categoryCount} ${categoryLabel}`;

        isOwner = store.is_owner;

        updateVerificationProgress(store);
    }

    function updateVerificationProgress(store) {
        let completedSteps = 0;
        const totalSteps = 4;
        const hasBasicDetails = store.name && store.business_email && store.business_phone && store.nature_of_business;
        completedSteps += hasBasicDetails ? 1 : 0;
        updateStepStatus('basic-details', hasBasicDetails);
        const hasLocationDetails = store.region && store.district && store.address && store.latitude && store.longitude;
        completedSteps += hasLocationDetails ? 1 : 0;
        updateStepStatus('location-details', hasLocationDetails);
        const activeCats = store.categories ? store.categories.filter(cat => cat.status === 'active') : [];
        completedSteps += activeCats.length > 0 ? 1 : 0;
        updateStepStatus('categories', activeCats.length > 0);
        completedSteps += (store.product_count && store.product_count > 0) ? 1 : 0;
        updateStepStatus('products', store.product_count && store.product_count > 0);
        const percentage = Math.round((completedSteps / totalSteps) * 100);
        document.getElementById('completion-percentage').textContent = percentage;
        document.getElementById('completion-steps').textContent = completedSteps;
        document.getElementById('verification-progress').style.width = `${percentage}%`;
    }

    function updateStepStatus(id, completed) {
        const stepElement = document.getElementById(`step-${id}`);
        if (stepElement) {
            const iconElement = stepElement.querySelector('.step-icon');
            if (completed) {
                iconElement.classList.remove('pending');
                iconElement.classList.add('completed');
                iconElement.innerHTML = '<i class="fas fa-check"></i>';
            } else {
                iconElement.classList.remove('completed');
                iconElement.classList.add('pending');
                iconElement.innerHTML = '<i class="fas fa-times"></i>';
            }
        }
    }

    function showError(message) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        console.error(message);
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatTimeAgo(date) {
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);
        if (diffInSeconds < 60) {
            return `${diffInSeconds} seconds ago`;
        }
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) {
            return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} ago`;
        }
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) {
            return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} ago`;
        }
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays < 30) {
            return `${diffInDays} day${diffInDays > 1 ? 's' : ''} ago`;
        }
        const diffInMonths = Math.floor(diffInDays / 30);
        if (diffInMonths < 12) {
            return `${diffInMonths} month${diffInMonths > 1 ? 's' : ''} ago`;
        }
        const diffInYears = Math.floor(diffInMonths / 12);
        return `${diffInYears} year${diffInYears > 1 ? 's' : ''} ago`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-4 py-2 rounded-md shadow-md z-[10000] opacity-0 transition-opacity duration-300`;
        toast.textContent = message;

        document.body.appendChild(toast);

        toast.offsetHeight;

        toast.classList.add('opacity-100');

        setTimeout(() => {
            toast.classList.remove('opacity-100');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    }
</script>

<?php
$mainContent = ob_get_clean();

// Generate SEO meta tags for the master template
$seoTags = [];
if ($storeData) {
    $seoTags = generateSeoMetaTags($storeData);
}

include __DIR__ . '/master.php';
?>