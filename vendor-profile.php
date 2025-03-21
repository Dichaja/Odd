<?php
$pageTitle = 'Vendor Profile';
$activeNav = 'vendors';
require_once __DIR__ . '/config/config.php';
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

    @media (max-width: 640px) {
        .vendor-avatar {
            width: 80px;
            height: 80px;
        }
    }
</style>

<div class="vendor-container">
    <!-- Vendor Header with Cover Photo and Profile Info -->
    <div class="vendor-header">
        <img src="https://placehold.co/1200x250" alt="Vendor Cover Photo" class="vendor-cover">
        <div class="vendor-profile-info">
            <img src="https://placehold.co/120" alt="Yasin Elf Sekiwunga" class="vendor-avatar">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Yasin Elf Sekiwunga</h1>
                <div>
                    <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm mr-2">Transporter</span>
                    <span class="bg-yellow-300 text-yellow-800 px-3 py-1 rounded-full text-sm">Pending Verification</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Details Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
            <h2 class="text-xl text-red-600 font-bold mb-6">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-map-marker-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Location</h3>
                        <p class="text-gray-600">Wakiso, Kajjansi, Sekiwunga</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-envelope text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Email</h3>
                        <p id="email-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-email" class="text-sm text-blue-600 hover:underline">Show Email</button>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-phone-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Contact</h3>
                        <p id="phone-display" class="text-gray-600">••••••••••</p>
                        <button id="toggle-phone" class="text-sm text-blue-600 hover:underline">Show Contact</button>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Username</h3>
                        <p class="text-gray-600">YasinElf</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-calendar-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Registered</h3>
                        <p class="text-gray-600">Jan 06, 2024</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="text-red-600 mr-3 mt-0.5">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold mb-1">Last Seen</h3>
                        <p class="text-gray-600">1 year ago</p>
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="verification-wrapper">
                <h2 class="text-xl text-red-600 font-bold mb-6">Verification Status</h2>

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">80% Complete</span>
                        <span class="text-sm font-medium text-gray-700">4/5 Steps</span>
                    </div>
                    <div class="verification-track">
                        <div class="verification-indicator" style="width: 80%"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center p-4 rounded-lg bg-green-50">
                        <div class="step-icon completed">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="font-bold">Business Details</div>
                            <div class="text-green-600 text-sm">Completed</div>
                        </div>
                    </div>

                    <div class="flex items-center p-4 rounded-lg bg-green-50">
                        <div class="step-icon completed">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="font-bold">Product Categories</div>
                            <div class="text-green-600 text-sm">Completed</div>
                        </div>
                    </div>

                    <div class="flex items-center p-4 rounded-lg bg-green-50">
                        <div class="step-icon completed">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="font-bold">Products For Sale</div>
                            <div class="text-green-600 text-sm">Completed</div>
                        </div>
                    </div>

                    <div class="flex items-center p-4 rounded-lg bg-green-50">
                        <div class="step-icon completed">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <div class="font-bold">Zzimba Credit Activation</div>
                            <div class="text-green-600 text-sm">Completed</div>
                        </div>
                    </div>

                    <div class="flex items-center p-4 rounded-lg bg-gray-50">
                        <div class="step-icon pending">
                            <span>5</span>
                        </div>
                        <div>
                            <div class="font-bold">Membership Subscription</div>
                            <div class="text-gray-600 text-sm">Pending</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl text-red-600 font-bold mb-6">Account Summary</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Products</span>
                    <span class="text-lg font-bold">2</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Categories</span>
                    <span class="text-lg font-bold">1</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Total Views</span>
                    <span class="text-lg font-bold">95</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <span class="font-medium">Member Since</span>
                    <span class="text-lg font-bold">2024</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="font-medium">Account Status</span>
                    <span class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium">
                        Pending
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="mt-12">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl text-red-600 font-bold">Products (2)</h2>
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                <input type="text" placeholder="Search products..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-full">
                    <option value="default">Default Sorting</option>
                    <option value="latest">Latest</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Product 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1">
                <img src="https://placehold.co/400x200" alt="Tororo Cement" class="w-full h-48 object-cover">
                <div class="p-4 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Tororo Cement CEM II B 32.5 Black</h3>
                    <p class="text-gray-500 text-sm mb-4">Per 50 Kg Bag</p>
                    <p class="text-xl font-bold text-red-600 mb-2">UGX 34,500</p>
                    <p class="text-gray-500 text-sm mb-4">Max Capacity: 50 Units</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-auto">
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-eye"></i> 65 Views
                        </span>
                        <a href="#" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">View Details</a>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform hover:-translate-y-1">
                <img src="https://placehold.co/400x200" alt="Elf Truck for Hire" class="w-full h-48 object-cover">
                <div class="p-4 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Elf Truck for Hire</h3>
                    <p class="text-gray-500 text-sm mb-4">Per 1 Truck @ 5km Unit Lease</p>
                    <p class="text-xl font-bold text-red-600 mb-2">UGX 20,000</p>
                    <p class="text-gray-500 text-sm mb-4">Max Capacity: 1 Unit</p>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-auto">
                        <span class="text-gray-500 text-sm">
                            <i class="fas fa-eye"></i> 30 Views
                        </span>
                        <a href="#" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">View Details</a>
                    </div>
                </div>
            </div>
        </div>

        <button class="mx-auto mt-8 block bg-gray-100 text-gray-600 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-colors" id="loadMoreBtn">
            Load More Products
        </button>
    </div>
</div>

<script>
    // Show/hide contact information
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle email visibility
        const toggleEmail = document.getElementById('toggle-email');
        const emailDisplay = document.getElementById('email-display');
        let emailVisible = false;

        toggleEmail.addEventListener('click', function() {
            if (emailVisible) {
                emailDisplay.textContent = '••••••••••';
                toggleEmail.textContent = 'Show Email';
            } else {
                // In a real application, you would fetch this from the server
                emailDisplay.textContent = 'Not provided';
                toggleEmail.textContent = 'Hide Email';
            }
            emailVisible = !emailVisible;
        });

        // Toggle phone visibility
        const togglePhone = document.getElementById('toggle-phone');
        const phoneDisplay = document.getElementById('phone-display');
        let phoneVisible = false;

        togglePhone.addEventListener('click', function() {
            if (phoneVisible) {
                phoneDisplay.textContent = '••••••••••';
                togglePhone.textContent = 'Show Contact';
            } else {
                // In a real application, you would fetch this from the server
                phoneDisplay.textContent = '+256700883798';
                togglePhone.textContent = 'Hide Contact';
            }
            phoneVisible = !phoneVisible;
        });

        // Load more products
        const loadMoreBtn = document.getElementById('loadMoreBtn');

        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // In a real application, you would load more products from the server
            notifications.info('No more products available', 'Products');
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>