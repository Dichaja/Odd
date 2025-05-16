<?php
$pageTitle = 'Vendor Profile';
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';

$vendorId = $_GET['id'] ?? null;

if ($vendorId) {
    try {
        $storeId = $vendorId;

        $stmt = $pdo->prepare("SELECT name FROM vendor_stores WHERE id = ?");
        $stmt->execute([$storeId]);
        $store = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($store) {
            $pageTitle = htmlspecialchars($store['name']);
        }
    } catch (Exception $e) {
        error_log("Error fetching vendor name: " . $e->getMessage());
    }
}

// Check if user is logged in
$isLoggedIn = !empty($_SESSION['user']['logged_in']);

ob_start();
?>

<style>
    .vendor-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 2rem;
    }

    .vendor-header {
        position: relative;
        margin-bottom: 2rem;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .vendor-profile-info {
        display: flex;
        align-items: flex-end;
        padding: 2rem;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0) 100%);
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        color: white;
    }

    .vendor-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid white;
        margin-right: 2rem;
        object-fit: cover;
        background-color: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verification-wrapper {
        margin-top: 2rem;
    }

    .verification-track {
        height: 0.5rem;
        background-color: #E5E7EB;
        border-radius: 0.25rem;
        margin-top: 0.5rem;
    }

    .verification-indicator {
        height: 100%;
        background-color: #C00000;
        border-radius: 0.25rem;
        transition: width 0.5s ease-in-out;
    }

    .step-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .step-icon.completed {
        background-color: #10B981;
        color: white;
    }

    .step-icon.pending {
        background-color: #E5E7EB;
        color: #4B5563;
    }

    .loader {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-left-color: #C00000;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .tab-container {
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        background: none;
        position: relative;
    }

    .tab-button.active {
        color: #C00000;
        border-bottom: 2px solid #C00000;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .category-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .category-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .category-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .category-description {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-success {
        background-color: #10B981;
        color: white;
    }

    .badge-warning {
        background-color: #F59E0B;
        color: white;
    }

    .badge-danger {
        background-color: #EF4444;
        color: white;
    }

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

    .view-location-btn {
        display: inline-flex;
        align-items: center;
        color: #DC2626;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .view-location-btn:hover {
        text-decoration: underline;
    }

    .location-hidden {
        display: none;
    }

    .location-visible {
        display: flex;
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

    @media (max-width: 640px) {
        .vendor-avatar {
            width: 80px;
            height: 80px;
            margin: 0 auto;
        }

        .profile-info-mobile-center {
            text-align: center;
        }

        .stats-mobile-center {
            justify-content: center;
        }
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        width: 90%;
        max-width: 500px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    .modal-body {
        margin-bottom: 1rem;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #e5e7eb;
    }

    /* Image cropper styles */
    .cropper-container {
        width: 100%;
        height: 300px;
        margin-bottom: 1rem;
    }

    .avatar-edit-container {
        position: relative;
    }

    /* Edit button styles */
</style>

<div class="relative h-40 md:h-64 w-full bg-gray-100 overflow-hidden" id="vendor-cover-photo">
    <div id="vendor-cover" class="w-full h-full bg-center bg-cover"></div>
    <div id="cover-edit-button"
        class="absolute top-4 right-4 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>">
        <i class="fas fa-camera"></i>
    </div>
</div>

<div id="loading-state" class="flex flex-col items-center justify-center py-12">
    <div class="loader mb-4"></div>
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
                <div id="vendor-avatar-container" class="avatar-edit-container">
                    <div id="vendor-avatar"
                        class="h-32 w-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-white">
                        <i class="fas fa-store text-gray-400 text-4xl"></i>
                    </div>
                    <div id="avatar-edit-button"
                        class="absolute bottom-0 right-0 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm cursor-pointer text-red-600 border border-gray-200 hover:bg-gray-50 transition-colors <?= $isLoggedIn ? '' : 'hidden' ?>">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>

            <div class="mt-6 md:mt-0 md:ml-6 flex-grow profile-info-mobile-center">
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

                <div class="mt-6 flex flex-wrap gap-y-4 stats-mobile-center">
                    <div class="mr-8 flex items-center">
                        <i class="fa-solid fa-calendar-days text-gray-500 mr-2"></i>
                        <span id="vendor-registered" class="text-gray-700">Joined March 2008</span>
                    </div>
                    <div class="mr-8 flex items-center">
                        <div id="location-section">
                            <button id="view-location-btn" class="view-location-btn" onclick="showLocation()">
                                <i class="fa-solid fa-location-dot text-gray-500 mr-2"></i>
                                <span>View Location</span>
                            </button>
                            <div id="location-container" class="location-hidden">
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

        <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-x-8 gap-y-4 stats-mobile-center">
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
            <div class="ml-0 sm:ml-auto share-container">
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
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap <?= $isLoggedIn ? '' : 'hidden' ?>"
                        data-tab="manage">
                        <i class="fa-solid fa-cog mr-2"></i> Manage Products
                    </button>
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap <?= $isLoggedIn ? '' : 'hidden' ?>"
                        data-tab="managers">
                        <i class="fa-solid fa-users-cog mr-2"></i> Store Managers
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
            include_once __DIR__ . '/vendorProfileComponents/manage-tab.php';
            include_once __DIR__ . '/vendorProfileComponents/managers-tab.php';
            ?>
        </div>
    </main>
</div>

<!-- Edit Name Modal -->
<div id="edit-name-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Store Name</h2>
            <span class="close" onclick="closeModal('edit-name-modal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <label for="store-name-input" class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                <input type="text" id="store-name-input"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-name-modal')">Cancel</button>
            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="saveName()">Save</button>
        </div>
    </div>
</div>

<!-- Edit Description Modal -->
<div id="edit-description-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Store Description</h2>
            <span class="close" onclick="closeModal('edit-description-modal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <label for="store-description-input" class="block text-sm font-medium text-gray-700 mb-1">Store
                    Description</label>
                <textarea id="store-description-input" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-description-modal')">Cancel</button>
            <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                onclick="saveDescription()">Save</button>
        </div>
    </div>
</div>

<!-- Edit Logo Modal -->
<div id="edit-logo-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Store Logo</h2>
            <span class="close" onclick="closeModal('edit-logo-modal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <label for="logo-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Logo</label>
                <input type="file" id="logo-file-input" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Select a square image for best results. The image will be cropped
                    to a 1:1 ratio.</p>
            </div>
            <div id="cropper-container" class="cropper-container hidden">
                <img id="cropper-image" src="https://placehold.co/600x400?text=Image+to+Crop" alt="Image to crop">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-logo-modal')">Cancel</button>
            <button type="button" id="crop-button"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 hidden"
                onclick="cropAndSaveLogo()">Crop & Save</button>
        </div>
    </div>
</div>

<!-- Edit Cover Photo Modal -->
<div id="edit-cover-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Edit Cover Photo</h2>
            <span class="close" onclick="closeModal('edit-cover-modal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="mb-4">
                <label for="cover-file-input" class="block text-sm font-medium text-gray-700 mb-1">Upload Cover
                    Photo</label>
                <input type="file" id="cover-file-input" accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                <p class="text-sm text-gray-500 mt-1">Select an image that will be cropped to a 3:1 ratio for best
                    results.</p>
            </div>
            <div id="cover-cropper-container" class="cropper-container hidden">
                <img id="cover-cropper-image" src="https://placehold.co/1200x400?text=Cover+Image+to+Crop"
                    alt="Image to crop">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                onclick="closeModal('edit-cover-modal')">Cancel</button>
            <button type="button" id="crop-cover-button"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 hidden"
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

    // Function to edit the cover photo
    function editCover() {
        openModal('edit-cover-modal');
    }

    // Initialize the cover cropper
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

    // Crop and save the cover photo
    function cropAndSaveCover() {
        if (!coverCropper) {
            showToast('Please select an image first', 'error');
            return;
        }

        // Get the cropped canvas
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

        // Convert canvas to blob
        canvas.toBlob(function (blob) {
            const formData = new FormData();
            formData.append('cover', blob, 'cover.png');

            // First upload the cover
            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadVendorCover`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Then update the store with the new cover path
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
                        // Update the cover in the UI
                        const coverUrl = canvas.toDataURL();
                        const coverContainer = document.getElementById('vendor-cover');
                        coverContainer.style.backgroundImage = `url(${coverUrl})`;

                        closeModal('edit-cover-modal');
                        showToast('Cover photo updated successfully');

                        // Reload the page to see the updated cover
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

    // Show location function - only shows, doesn't hide
    function showLocation() {
        const locationContainer = document.getElementById('location-container');
        const viewLocationBtn = document.getElementById('view-location-btn');

        // Show the location
        locationContainer.classList.remove('location-hidden');
        locationContainer.classList.add('location-visible');

        // Hide the button
        viewLocationBtn.style.display = 'none';
    }

    // Copy link function
    function copyLink() {
        const currentUrl = window.location.href;
        navigator.clipboard.writeText(currentUrl).then(() => {
            showToast('Link copied to clipboard!', 'success');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    // Social sharing functions
    function shareOnWhatsApp() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const vendorDescription = document.getElementById('vendor-description').textContent;
        const message = `Check out ${vendorName}\n\n${vendorDescription}\n\nVisit their profile on Zzimba Online: ${currentUrl}`;
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
        const vendorName = document.getElementById('vendor-name').textContent;
        const vendorDescription = document.getElementById('vendor-description').textContent;
        const message = `Check out ${vendorName}\n\n${vendorDescription}\n\nVisit their profile on Zzimba Online:`;
        const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(message)}&url=${encodeURIComponent(currentUrl)}`;
        window.open(twitterUrl, '_blank');
    }

    function shareOnLinkedIn() {
        const currentUrl = window.location.href;
        const vendorName = document.getElementById('vendor-name').textContent;
        const vendorDescription = document.getElementById('vendor-description').textContent;
        const message = `Check out ${vendorName}\n\n${vendorDescription}\n\nVisit their profile on Zzimba Online.`;
        const linkedinUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(currentUrl)}&title=${encodeURIComponent(vendorName)}&summary=${encodeURIComponent(message)}`;
        window.open(linkedinUrl, '_blank');
    }

    // Edit name functionality
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

    // Edit description functionality
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

    // Edit logo functionality
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

        // Get the cropped canvas
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

        // Convert canvas to blob
        canvas.toBlob(function (blob) {
            const formData = new FormData();
            formData.append('logo', blob, 'logo.png');

            // First upload the logo
            fetch(`${BASE_URL}account/fetch/manageZzimbaStores.php?action=uploadLogo`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Then update the store with the new logo path
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
                        // Update the logo in the UI
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

                        // Reload the page to see the updated logo
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

        // Set up logo file input change event
        document.getElementById('logo-file-input').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    initCropper(e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Set up cover file input change event
        document.getElementById('cover-file-input').addEventListener('change', function (e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    initCoverCropper(e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Set up edit button click events
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
                    // Handle specific error for store not found or not active
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

        // Handle logo placeholder
        if (store.logo_url) {
            const logoImg = document.createElement('img');
            logoImg.src = BASE_URL + store.logo_url;
            logoImg.alt = store.name;
            logoImg.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoImg);
        } else {
            // Use placehold.co for logo placeholder
            const firstWord = store.name.split(' ')[0];
            const logoPlaceholder = document.createElement('img');
            logoPlaceholder.src = `https://placehold.co/400x400/e5e7eb/6b7280?text=${encodeURIComponent(firstWord)}`;
            logoPlaceholder.alt = store.name;
            logoPlaceholder.className = 'w-full h-full object-cover rounded-full';
            const avatarContainer = document.getElementById('vendor-avatar');
            avatarContainer.innerHTML = '';
            avatarContainer.appendChild(logoPlaceholder);
        }

        // Handle cover photo placeholder
        const coverContainer = document.getElementById('vendor-cover');
        if (store.vendor_cover_url) {
            coverContainer.style.backgroundImage = `url(${BASE_URL + store.vendor_cover_url})`;
        } else {
            // Use placehold.co for cover placeholder
            coverContainer.style.backgroundImage = `url(https://placehold.co/1200x400/e5e7eb/6b7280?text=${encodeURIComponent(store.name)})`;
        }

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

        toast.offsetHeight; // Trigger reflow

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
include __DIR__ . '/master.php';
?>