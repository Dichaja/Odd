<!-- About Tab -->
<div id="about-tab" class="tab-pane hidden">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6 md:col-span-2">
            <h2 class="text-xl text-red-600 font-bold mb-6">About Us</h2>
            <div id="store-about" class="text-gray-600">
                <p id="store-description" class="mb-4">Loading...</p>
            </div>
            <div id="website-section" class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-semibold mb-3">Website</h3>
                <a id="store-website" href="#" target="_blank"
                    class="text-blue-600 hover:underline text-sm break-all">Loading...</a>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl text-red-600 font-bold mb-6">Account Summary</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span
                        class="font-medium">Products</span><span class="text-lg font-bold"
                        id="product-count-summary">0</span></div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span
                        class="font-medium">Categories</span><span class="text-lg font-bold"
                        id="category-count-summary">0</span></div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span
                        class="font-medium">Total Views</span><span class="text-lg font-bold" id="view-count">0</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-200"><span
                        class="font-medium">Member Since</span><span class="text-lg font-bold"
                        id="member-since">2024</span></div>
                <div class="flex justify-between items-center"><span class="font-medium">Account Status</span><span
                        id="account-status"
                        class="inline-block bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-sm font-medium">Pending</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // About Tab JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // The about tab functionality is mostly handled by the main renderVendorProfile function
        // which populates the data for this tab
    });
</script>