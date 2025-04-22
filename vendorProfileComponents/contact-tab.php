<!-- Contact Tab -->
<div id="contact-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl text-red-600 font-bold mb-6">Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-map-marker-alt text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Location</h3>
                    <p class="text-gray-600" id="vendor-location-contact">Loading...</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-envelope text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Email</h3>
                    <p id="email-display" class="text-gray-600">••••••••••</p>
                    <button id="toggle-email" class="text-sm text-blue-600 hover:underline">Show Email</button>
                </div>
            </div>
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-phone-alt text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Contact</h3>
                    <p id="phone-display" class="text-gray-600">••••••••••</p>
                    <button id="toggle-phone" class="text-sm text-blue-600 hover:underline">Show Contact</button>
                </div>
            </div>
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-user text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Owner</h3>
                    <p class="text-gray-600" id="vendor-owner">Loading...</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-calendar-alt text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Registered</h3>
                    <p class="text-gray-600" id="vendor-registered-contact">Loading...</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="text-red-600 mr-3 mt-0.5"><i class="fas fa-clock text-lg"></i></div>
                <div>
                    <h3 class="font-bold mb-1">Last Seen</h3>
                    <p class="text-gray-600" id="vendor-last-seen">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Contact Tab JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        const toggleEmail = document.getElementById('toggle-email');
        const emailDisplay = document.getElementById('email-display');
        let emailVisible = false;
        toggleEmail.addEventListener('click', function () {
            if (emailVisible) {
                emailDisplay.textContent = '••••••••••';
                toggleEmail.textContent = 'Show Email';
            } else {
                emailDisplay.textContent = storeEmail || 'Not provided';
                toggleEmail.textContent = 'Hide Email';
            }
            emailVisible = !emailVisible;
        });

        const togglePhone = document.getElementById('toggle-phone');
        const phoneDisplay = document.getElementById('phone-display');
        let phoneVisible = false;
        togglePhone.addEventListener('click', function () {
            if (phoneVisible) {
                phoneDisplay.textContent = '••••••••••';
                togglePhone.textContent = 'Show Contact';
            } else {
                phoneDisplay.textContent = storePhone || 'Not provided';
                togglePhone.textContent = 'Hide Contact';
            }
            phoneVisible = !phoneVisible;
        });
    });
</script>