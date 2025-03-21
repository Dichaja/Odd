<?php
$pageTitle = 'Request for Quote';
$activeNav = 'quote';
require_once __DIR__ . '/config/config.php';
ob_start();

$recaptcha_site_key = '6LdtJdcqAAAAADWom9IW8lSg7L41BQbAJPrAW-Hf';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">

<style>
    .form-group {
        position: relative;
    }

    .floating-label {
        position: absolute;
        left: 1rem;
        top: 0.8rem;
        padding: 0 0.25rem;
        background-color: white;
        transition: all 0.2s ease-in-out;
        pointer-events: none;
    }

    .form-input:focus~.floating-label,
    .form-input:not(:placeholder-shown)~.floating-label {
        transform: translateY(-1.4rem) scale(0.85);
        background-color: white;
        color: #000000;
    }

    .form-input:focus {
        border-color: #ef4444;
    }

    .iti {
        width: 100%;
    }

    /* Enhanced styling */
    .page-header {
        background-image: linear-gradient(to right, rgba(239, 68, 68, 0.9), rgba(185, 28, 28, 0.8)),
            url('https://dummyimage.com/1920x350/e3e3e3/ffffff&text=Request+Quote');
        background-size: cover;
        background-position: center;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }

    .form-card {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .form-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .item-row {
        transition: all 0.3s ease;
    }

    .item-row:hover {
        background-color: #f9fafb;
    }

    .add-btn {
        background-image: linear-gradient(to right, #10b981, #059669);
    }

    .add-btn:hover {
        background-image: linear-gradient(to right, #059669, #047857);
    }

    .submit-btn {
        background-image: linear-gradient(to right, #ef4444, #dc2626);
    }

    .submit-btn:hover {
        background-image: linear-gradient(to right, #dc2626, #b91c1c);
    }

    .info-card {
        background-color: #f9fafb;
        border-left: 4px solid #ef4444;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        transform: translateY(-5px);
    }

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

    .required-star {
        color: #ef4444;
        font-weight: bold;
    }

    .float-label-input {
        position: relative;
    }

    .float-label-input input:focus,
    .float-label-input input:not(:placeholder-shown) {
        border-color: #ef4444;
    }
</style>

<!-- Page Header -->
<div class="page-header relative text-white">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">Request for Quote</h1>
        <p class="text-white text-opacity-90 max-w-2xl">Get accurate pricing and availability information for your construction needs. Complete the form below for a detailed quote.</p>
        <nav class="text-sm mt-4 space-x-2">
            <a href="<?= BASE_URL ?>" class="hover:underline text-white text-opacity-80">Zzimba Online</a>
            <span>/</span>
            <span class="font-semibold">Request for Quote</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
        <div class="lg:col-span-2">
            <div class="form-card p-6 md:p-8 fade-in">
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Request Details</h2>
                <p class="text-gray-600 mb-6">Fields marked with <span class="required-star">*</span> are required</p>

                <form id="rfq-form" class="space-y-6" novalidate autocomplete="off">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="form-group">
                            <input type="text" id="company" name="company" placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-company" data-field-id="<?= uniqid('company_') ?>">
                            <label for="company" class="floating-label text-gray-500">Company Name (optional)</label>
                        </div>

                        <div class="form-group">
                            <input type="text" id="contact" name="contact" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-name" data-field-id="<?= uniqid('contact_') ?>">
                            <label for="contact" class="floating-label text-gray-500">Contact Person <span class="required-star">*</span></label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="form-group">
                            <input type="email" id="email" name="email" required placeholder=" "
                                class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                autocomplete="new-email" data-field-id="<?= uniqid('email_') ?>">
                            <label for="email" class="floating-label text-gray-500">Email <span class="required-star">*</span></label>
                        </div>

                        <div class="form-group">
                            <input
                                type="tel"
                                id="phone-whatsapp"
                                name="phone"
                                required
                                placeholder="Phone/WhatsApp Contact *"
                                class="block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                autocomplete="new-phone"
                                data-field-id="<?= uniqid('phone_') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" id="location" name="location" required placeholder=" "
                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                            autocomplete="new-address" data-field-id="<?= uniqid('location_') ?>">
                        <label for="location" class="floating-label text-gray-500">Site Location <span class="required-star">*</span></label>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-800">List of Items <span class="required-star">*</span></h2>
                            <button type="button" id="add-item"
                                class="add-btn inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md focus:outline-none transition-colors">
                                <i class="fas fa-plus mr-2"></i> Add Item
                            </button>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-6 gap-2 items-center text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-2">
                                <div class="col-span-3">Brand/Material</div>
                                <div class="col-span-2">Size/Specification</div>
                                <div class="col-span-1">Quantity</div>
                            </div>
                            <div id="items-container" class="space-y-4">
                                <div class="grid grid-cols-6 gap-2 items-center item-row rounded-lg">
                                    <div class="col-span-3 form-group relative">
                                        <input type="text" name="items[0][brand]" required placeholder=" "
                                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                            autocomplete="new-brand" data-field-id="<?= uniqid('brand_') ?>">
                                        <label class="floating-label text-gray-500">Brand Name <span class="required-star">*</span></label>
                                        <button type="button" class="remove-item hidden w-6 h-6 flex items-center justify-center text-red-500 hover:text-red-700 bg-white rounded-full shadow-sm absolute -top-2 -left-2 text-xs">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="col-span-2 form-group">
                                        <input type="text" name="items[0][size]" required placeholder=" "
                                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                            autocomplete="new-size" data-field-id="<?= uniqid('size_') ?>">
                                        <label class="floating-label text-gray-500">Size <span class="required-star">*</span></label>
                                    </div>
                                    <div class="form-group">
                                        <input type="number" name="items[0][quantity]" required placeholder=" "
                                            class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                                            autocomplete="new-quantity" data-field-id="<?= uniqid('qty_') ?>">
                                        <label class="floating-label text-gray-500">Qty <span class="required-star">*</span></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="reset" id="reset-form"
                            class="px-5 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none transition-colors">
                            <i class="fas fa-times-circle mr-2"></i> Cancel
                        </button>
                        <button type="submit"
                            class="submit-btn px-5 py-3 text-sm font-medium text-white rounded-md focus:outline-none transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1" style="z-index: -50;">
            <div class="info-card rounded-lg p-6 fade-in mb-6">
                <div class="flex items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Instructions</h2>
                </div>
                <div class="space-y-4 text-sm text-gray-600">
                    <p class="flex items-start">
                        <i class="fas fa-clipboard-list mt-1 text-red-500 mr-3"></i>
                        <span>Fill in all required fields with accurate information.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-building mt-1 text-red-500 mr-3"></i>
                        <span>Company Name is optional if you do not have one.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-mobile-alt mt-1 text-red-500 mr-3"></i>
                        <span>Include a valid phone number or WhatsApp contact.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-map-marker-alt mt-1 text-red-500 mr-3"></i>
                        <span>Specify the exact site location for delivery purposes.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-plus-circle mt-1 text-red-500 mr-3"></i>
                        <span>Use the "Add Item" button to request multiple items.</span>
                    </p>
                    <p class="flex items-start">
                        <i class="fas fa-times-circle mt-1 text-red-500 mr-3"></i>
                        <span>Click the X button to remove unwanted items.</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 fade-in">
                <div class="flex items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Item Details</h2>
                </div>
                <div class="space-y-3 text-sm text-gray-600">
                    <p class="font-medium">For each item, specify:</p>
                    <ul class="space-y-2 pl-6">
                        <li class="flex items-start">
                            <i class="fas fa-trademark mt-1 text-gray-400 mr-3"></i>
                            <span>Brand name or specifications</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-ruler-combined mt-1 text-gray-400 mr-3"></i>
                            <span>Required size or dimensions</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-sort-amount-up mt-1 text-gray-400 mr-3"></i>
                            <span>Quantity needed</span>
                        </li>
                    </ul>
                    <div class="mt-6 p-4 bg-yellow-50 rounded-md border-l-4 border-yellow-400">
                        <p class="text-yellow-700 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span>All fields marked with <span class="required-star">*</span> are required.</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_site_key ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_BASE = "<?php echo BASE_URL; ?>fetch/handleRFQ";

        const phoneInputField = document.querySelector("#phone-whatsapp");
        const iti = window.intlTelInput(phoneInputField, {
            preferredCountries: ["ug", "rw", "ke", "tz"],
            initialCountry: "ug",
            separateDialCode: true,
            allowDropdown: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        // Additional autofill prevention
        const formInputs = document.querySelectorAll('input');
        formInputs.forEach(input => {
            const randomAttr = Math.random().toString(36).substring(2);
            input.setAttribute('data-random', randomAttr);
        });

        let itemCount = 1;
        const itemsContainer = document.getElementById('items-container');
        const addItemButton = document.getElementById('add-item');
        const form = document.getElementById('rfq-form');

        addItemButton.addEventListener('click', function() {
            const randomIdBrand = Math.random().toString(36).substring(2);
            const randomIdSize = Math.random().toString(36).substring(2);
            const randomIdQty = Math.random().toString(36).substring(2);

            const newItem = document.createElement('div');
            newItem.className = 'grid grid-cols-6 gap-2 items-center item-row rounded-lg fade-in';
            newItem.innerHTML = `
                <div class="col-span-3 form-group relative">
                    <input type="text" name="items[${itemCount}][brand]" required placeholder=" "
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="new-brand-${itemCount}" data-field-id="brand_${randomIdBrand}">
                    <label class="floating-label text-gray-500">Brand Name <span class="required-star">*</span></label>
                    <button type="button" class="remove-item w-6 h-6 flex items-center justify-center text-red-500 hover:text-red-700 bg-white rounded-full shadow-sm absolute -top-2 -left-2 text-xs">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="col-span-2 form-group">
                    <input type="text" name="items[${itemCount}][size]" required placeholder=" "
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="new-size-${itemCount}" data-field-id="size_${randomIdSize}">
                    <label class="floating-label text-gray-500">Size <span class="required-star">*</span></label>
                </div>
                <div class="form-group">
                    <input type="number" name="items[${itemCount}][quantity]" required placeholder=" "
                        class="form-input block w-full px-3 py-3 border border-gray-200 rounded-md focus:outline-none"
                        autocomplete="new-quantity-${itemCount}" data-field-id="qty_${randomIdQty}">
                    <label class="floating-label text-gray-500">Qty <span class="required-star">*</span></label>
                </div>
            `;
            itemsContainer.appendChild(newItem);
            itemCount++;
            if (itemCount > 1) {
                const firstItemRemoveBtn = itemsContainer.querySelector('.remove-item');
                if (firstItemRemoveBtn) {
                    firstItemRemoveBtn.classList.remove('hidden');
                }
            }
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const item = e.target.closest('.grid');
                item.remove();
                itemCount--;
                if (itemCount === 1) {
                    const firstItemRemoveBtn = itemsContainer.querySelector('.remove-item');
                    if (firstItemRemoveBtn) {
                        firstItemRemoveBtn.classList.add('hidden');
                    }
                }
            }
        });

        document.getElementById('reset-form').addEventListener('click', function(e) {
            e.preventDefault();
            form.reset();
            iti.setNumber('');
            while (itemsContainer.children.length > 1) {
                itemsContainer.removeChild(itemsContainer.lastChild);
            }
            itemCount = 1;
            const firstItemRemoveBtn = itemsContainer.querySelector('.remove-item');
            if (firstItemRemoveBtn) {
                firstItemRemoveBtn.classList.add('hidden');
            }

            notifications.info('Form has been reset', 'Form Reset');
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            let hasError = false;
            const contactInput = document.getElementById('contact');
            const emailInput = document.getElementById('email');
            const locationInput = document.getElementById('location');
            const itemRows = document.querySelectorAll('#items-container .grid');

            if (contactInput.value.trim() === "") {
                notifications.error('Contact person is required.', 'Input Required');
                hasError = true;
            }

            if (emailInput.value.trim() === "") {
                notifications.error('Email is required.', 'Input Required');
                hasError = true;
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value.trim())) {
                    notifications.error('Please enter a valid email address.', 'Input Required');
                    hasError = true;
                }
            }

            if (phoneInputField.value.trim() === "" || !iti.isValidNumber()) {
                notifications.error('Please enter a valid phone number.', 'Input Required');
                hasError = true;
            }

            if (locationInput.value.trim() === "") {
                notifications.error('Site location is required.', 'Input Required');
                hasError = true;
            }

            if (itemRows.length === 0) {
                notifications.error('Please add at least one item.', 'Input Required');
                hasError = true;
            } else {
                itemRows.forEach((row) => {
                    const brandInput = row.querySelector('input[name*="[brand]"]');
                    const sizeInput = row.querySelector('input[name*="[size]"]');
                    const quantityInput = row.querySelector('input[name*="[quantity]"]');
                    if (!brandInput.value.trim()) {
                        notifications.error('Brand name is required.', 'Input Required');
                        hasError = true;
                    }
                    if (!sizeInput.value.trim()) {
                        notifications.error('Size is required.', 'Input Required');
                        hasError = true;
                    }
                    if (!quantityInput.value.trim() || parseInt(quantityInput.value) <= 0) {
                        notifications.error('Please enter a valid quantity.', 'Input Required');
                        hasError = true;
                    }
                });
            }

            if (hasError) return;

            const items = [];
            itemRows.forEach((row) => {
                const brandInput = row.querySelector('input[name*="[brand]"]');
                const sizeInput = row.querySelector('input[name*="[size]"]');
                const quantityInput = row.querySelector('input[name*="[quantity]"]');
                items.push({
                    brand: brandInput.value.trim(),
                    size: sizeInput.value.trim(),
                    quantity: quantityInput.value.trim()
                });
            });

            const payload = {
                company: document.getElementById('company').value.trim(),
                contact: contactInput.value.trim(),
                email: emailInput.value.trim(),
                phone: iti.getNumber(),
                location: locationInput.value.trim(),
                items: items,
                "g-recaptcha-response": document.getElementById('g-recaptcha-response').value
            };

            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Submitting...';

            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'submit_rfq'
            }).then(function(token) {
                payload["g-recaptcha-response"] = token;

                fetch(`${API_BASE}/submitRFQ`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notifications.success('Thank you! Your quote request has been received. We will contact you shortly.', 'RFQ Submitted');
                            form.reset();
                            iti.setNumber('');
                            while (itemsContainer.children.length > 1) {
                                itemsContainer.removeChild(itemsContainer.lastChild);
                            }
                            itemCount = 1;
                            const firstItemRemoveBtn = itemsContainer.querySelector('.remove-item');
                            if (firstItemRemoveBtn) {
                                firstItemRemoveBtn.classList.add('hidden');
                            }
                        } else {
                            notifications.error('Submission failed. Please try again.', 'RFQ Error');
                        }
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    })
                    .catch(error => {
                        notifications.error('Submission failed. Please try again.', 'RFQ Error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    });
            });
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>