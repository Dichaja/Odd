<!-- Verification Tab -->
<div id="verification-tab" class="tab-pane hidden">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="verification-wrapper">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl text-red-600 font-bold">Verification Status</h2>
            </div>
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2"><span
                        class="text-sm font-medium text-gray-700"><span id="completion-percentage">0</span>%
                        Complete</span><span class="text-sm font-medium text-gray-700"><span
                            id="completion-steps">0</span>/4 Steps</span></div>
                <div class="verification-track">
                    <div class="verification-indicator" id="verification-progress" style="width: 0%"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div id="step-basic-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                    <div class="step-icon pending"><span>1</span></div>
                    <div>
                        <div class="font-bold">Basic Store Details</div>
                        <div class="text-gray-600 text-sm" id="basic-details-status">Pending</div>
                    </div>
                </div>
                <div id="step-location-details" class="flex items-center p-4 rounded-lg bg-gray-50">
                    <div class="step-icon pending"><span>2</span></div>
                    <div>
                        <div class="font-bold">Location Details</div>
                        <div class="text-gray-600 text-sm" id="location-details-status">Pending</div>
                    </div>
                </div>
                <div id="step-categories" class="flex items-center p-4 rounded-lg bg-gray-50">
                    <div class="step-icon pending"><span>3</span></div>
                    <div>
                        <div class="font-bold">Product Categories</div>
                        <div class="text-gray-600 text-sm" id="categories-status">Pending</div>
                    </div>
                </div>
                <div id="step-products" class="flex items-center p-4 rounded-lg bg-gray-50">
                    <div class="step-icon pending"><span>4</span></div>
                    <div>
                        <div class="font-bold">Products For Sale</div>
                        <div class="text-gray-600 text-sm" id="products-status">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Verification Tab JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // The verification tab functionality is mostly handled by the updateVerificationProgress function
        // which is called from the main renderVendorProfile function
    });

    // This function is called from the main file but defined here for modularity
    function updateStepStatus(stepId, isCompleted) {
        const stepElement = document.getElementById(`step-${stepId}`);
        const statusElement = document.getElementById(`${stepId}-status`);
        const iconElement = stepElement.querySelector('.step-icon');
        if (isCompleted) {
            stepElement.classList.remove('bg-gray-50');
            stepElement.classList.add('bg-green-50');
            statusElement.textContent = 'Completed';
            statusElement.classList.remove('text-gray-600');
            statusElement.classList.add('text-green-600');
            iconElement.classList.remove('pending');
            iconElement.classList.add('completed');
            iconElement.innerHTML = '<i class="fas fa-check"></i>';
        } else {
            stepElement.classList.remove('bg-green-50');
            stepElement.classList.add('bg-gray-50');
            statusElement.textContent = 'Pending';
            statusElement.classList.remove('text-green-600');
            statusElement.classList.add('text-gray-600');
            iconElement.classList.remove('completed');
            iconElement.classList.add('pending');
            iconElement.innerHTML = stepId === 'basic-details' ? '1' : stepId === 'location-details' ? '2' : stepId === 'categories' ? '3' : '4';
        }
    }
</script>