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

    .vendor-cover {
        height: 250px;
        width: 100%;
        object-fit: cover;
        background-color: #f3f4f6;
    }

    @media (max-width: 640px) {
        .vendor-cover {
            height: 150px;
        }
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

    .copy-success {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #10B981;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        z-index: 50;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .copy-success.show {
        opacity: 1;
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
</style>

<div class="relative h-40 md:h-64 w-full bg-gray-200 overflow-hidden" id="vendor-cover-photo">
    <div class="vendor-cover" id="vendor-cover"></div>
</div>

<div id="loading-state" class="flex flex-col items-center justify-center py-12">
    <div class="loader mb-4"></div>
    <p class="text-gray-600">Loading vendor profile...</p>
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
                <div id="vendor-avatar"
                    class="h-32 w-32 rounded-full border-4 border-white shadow-md overflow-hidden bg-white">
                    <i class="fas fa-store text-gray-400 text-4xl"></i>
                </div>
            </div>

            <div class="mt-6 md:mt-0 md:ml-6 flex-grow profile-info-mobile-center">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                    <div>
                        <h1 id="vendor-name" class="text-3xl font-bold text-secondary">Store Name</h1>
                        <p id="vendor-description" class="text-gray-600 mt-1">Premium Construction Materials & Services
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
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap"
                        data-tab="manage">
                        <i class="fa-solid fa-cog mr-2"></i> Manage Products
                    </button>
                    <button
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium py-4 px-1 border-b-2 whitespace-nowrap"
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

<div id="copy-success" class="copy-success">Link copied to clipboard!</div>
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col space-y-4"></div>

<script>
    window.openModal = function (modalId) {
        document.getElementById(modalId).style.display = 'block';
    };
    window.closeModal = function (modalId) {
        document.getElementById(modalId).style.display = 'none';
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
            const copySuccess = document.getElementById('copy-success');
            copySuccess.classList.add('show');
            setTimeout(() => {
                copySuccess.classList.remove('show');
            }, 2000);
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

                if (tabName === 'manage' && isOwner) {
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
    });

    function loadVendorProfile(id) {
        fetch(`${BASE_URL}fetch/manageProfile?action=getStoreDetails&id=${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.store) {
                    storeData = data.store;
                    renderVendorProfile(storeData);
                    populateCategoryFilter(storeData.categories);
                } else {
                    showError(data.error || "Failed to load vendor profile");
                }
            })
            .catch(error => {
                console.error('Error loading vendor profile:', error);
                showError("Failed to load vendor profile");
            });
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
        }
        document.getElementById('vendor-cover').style.backgroundImage = 'linear-gradient(45deg, #f3f4f6 25%, #e5e7eb 25%, #e5e7eb 50%, #f3f4f6 50%, #f3f4f6 75%, #e5e7eb 75%, #e5e7eb 100%)';
        document.getElementById('vendor-cover').style.backgroundSize = '20px 20px';
        const activeCategories = store.categories ? store.categories.filter(cat => cat.status === 'active') : [];
        const activeProductsCount = store.product_count || 0;
        document.getElementById('product-count').textContent = `${activeProductsCount} Products`;
        document.getElementById('product-count-summary').textContent = activeProductsCount;
        document.getElementById('category-count').textContent = `${activeCategories.length} Categories`;
        document.getElementById('category-count-summary').textContent = activeCategories.length;
        document.getElementById('view-count').textContent = '0';
        const createdYear = new Date(store.created_at).getFullYear();
        document.getElementById('member-since').textContent = createdYear;
        document.getElementById('store-description').textContent = store.description || 'No description provided.';
        if (store.website_url) {
            document.getElementById('store-website').textContent = store.website_url;
            document.getElementById('store-website').href = store.website_url.startsWith('http') ? store.website_url : 'https://' + store.website_url;
        } else {
            document.getElementById('website-section').classList.add('hidden');
        }
        isOwner = store.is_owner;

        const manageTab = document.querySelector('button[data-tab="manage"]');
        if (isOwner) {
            manageTab.classList.remove('hidden');
        } else {
            manageTab.classList.add('hidden');
        }

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
        toast.className = `flex items-center p-4 mb-4 w-full max-w-xs rounded-lg shadow ${type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'} transition-opacity duration-300`;

        toast.innerHTML = `
            <div class="inline-flex flex-shrink-0 justify-center items-center w-8 h-8 ${type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'} rounded-lg">
                <i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-xmark'}"></i>
            </div>
            <div class="ml-3 text-sm font-normal">${message}</div>
            <button type="button" class="ml-auto -mx-1.5 -my-1.5 ${type === 'success' ? 'bg-green-50 text-green-500 hover:text-green-700' : 'bg-red-50 text-red-500 hover:text-red-700'} rounded-lg p-1.5 inline-flex h-8 w-8" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        document.getElementById('toast-container').appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0');
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