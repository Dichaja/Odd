<?php
// Process form submission
$result = '';
$numbers = [];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $_POST['message'] ?? '';
    $phoneNumbers = $_POST['phone_numbers'] ?? [];

    // Filter out empty phone numbers
    $phoneNumbers = array_filter($phoneNumbers, function ($num) {
        return !empty($num);
    });

    if (!empty($phoneNumbers) && !empty($message)) {
        $results = [];
        foreach ($phoneNumbers as $number) {
            // Prepend +256 to each 9-digit number
            $fullNumber = "+256" . $number;
            $results[] = messenger($fullNumber, $message);
        }
        $result = implode("<br>", $results);
    }
}

// Messenger function for SMS sending
function messenger($contact, $message)
{
    $api_id = 'API57063841167';
    $pwd = 'sample123';
    $sender_id = 'bulksms';

    $data = array(
        'api_id' => $api_id,
        'api_password' => $pwd,
        'sms_type' => 'P',
        'encoding' => 'T',
        'sender_id' => $sender_id,
        'phonenumber' => $contact,
        'textmessage' => $message,
        'templateid' => 'null',
        'V1' => 'null',
        'V2' => 'null',
        'V3' => 'null',
        'V4' => 'null',
        'V5' => 'null',
    );

    $data_string = json_encode($data);

    $context_options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $data_string,
        ),
    );

    $context = stream_context_create($context_options);
    $url = 'http://apidocs.speedamobile.com/api/SendSMS';
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        $result = 'err';
    }

    return $result . ' - ' . $message . ' (to ' . $contact . ')';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk SMS Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cloudflare-turnstile/1.0.1/turnstile.min.js"></script>
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        body {
            background-image: url('data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"%3E%3Cpath d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z" fill="%239C92AC" fill-opacity="0.1" fill-rule="evenodd"/%3E%3C/svg%3E');
            background-color: #f0f4f8;
        }
        
        /* Toast notification styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast-success {
            background-color: rgba(52, 211, 153, 0.95);
            color: white;
        }
        
        .toast-error {
            background-color: rgba(239, 68, 68, 0.95);
            color: white;
        }
        
        .phone-input-group {
            display: flex;
            align-items: center;
        }
        
        .country-code {
            background-color: rgba(243, 244, 246, 0.8);
            padding: 0.5rem;
            border: 1px solid #e5e7eb;
            border-right: none;
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
            font-weight: 500;
        }
        
        .phone-input {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="glass rounded-xl shadow-lg p-6 md:p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Uganda Bulk SMS Tester</h1>
            
            <form method="POST" class="space-y-6" id="smsForm">
                <div id="phone-numbers-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Numbers (Uganda)</label>
                    
                    <div class="phone-number-entry mb-2">
                        <div class="phone-input-group">
                            <span class="country-code">+256</span>
                            <input 
                                type="text" 
                                name="phone_numbers[]" 
                                class="phone-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="9-digit number"
                                pattern="[0-9]{9}"
                                maxlength="9"
                                required
                            >
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Enter 9 digits only (e.g., 770123456)</div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button 
                        type="button" 
                        id="add-number" 
                        class="text-sm text-blue-600 hover:text-blue-800 flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add another number
                    </button>
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea 
                        id="message" 
                        name="message" 
                        rows="4" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter your message"
                        required
                    ><?php echo htmlspecialchars($message); ?></textarea>
                    <div class="flex justify-between mt-1">
                        <span class="text-xs text-gray-500" id="char-count">0 characters</span>
                        <span class="text-xs text-gray-500">Max: 160 characters per SMS</span>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-medium py-2 px-4 rounded-lg hover:opacity-90 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                >
                    Send Bulk SMS
                </button>
            </form>
            
            <!-- Toast notification container -->
            <div id="toast" class="toast">
                <div id="toast-message"></div>
            </div>
            
            <div class="mt-6 text-xs text-gray-500 text-center">
                <p>This form sends SMS messages to Ugandan numbers (+256).</p>
                <p class="mt-1">API ID: API57063841167 | Sender ID: bulksms</p>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Phone number validation
            const validatePhoneNumber = (input) => {
                const value = input.value.replace(/\D/g, '');
                const isValid = value.length === 9;
                
                if (isValid) {
                    input.classList.remove('border-red-500');
                    input.classList.add('border-gray-300');
                } else {
                    input.classList.remove('border-gray-300');
                    input.classList.add('border-red-500');
                }
                
                return isValid;
            };
            
            // Add phone number field
            const addPhoneNumberField = () => {
                const container = document.getElementById('phone-numbers-container');
                const newField = document.createElement('div');
                newField.className = 'phone-number-entry mt-3 flex items-center';
                newField.innerHTML = `
                    <div class="phone-input-group flex-grow">
                        <span class="country-code">+256</span>
                        <input 
                            type="text" 
                            name="phone_numbers[]" 
                            class="phone-input w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="9-digit number"
                            pattern="[0-9]{9}"
                            maxlength="9"
                            required
                        >
                    </div>
                    <button type="button" class="remove-number ml-2 text-red-500 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;
                container.appendChild(newField);
                
                // Add event listener to the new remove button
                newField.querySelector('.remove-number').addEventListener('click', function() {
                    container.removeChild(newField);
                });
                
                // Add validation to the new input
                const newInput = newField.querySelector('input');
                newInput.addEventListener('input', function() {
                    validatePhoneNumber(this);
                });
            };
            
            // Add event listener to the "Add another number" button
            document.getElementById('add-number').addEventListener('click', addPhoneNumberField);
            
            // Character counter for message
            const messageTextarea = document.getElementById('message');
            const charCount = document.getElementById('char-count');
            
            messageTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count + ' characters';
                
                if (count > 160) {
                    charCount.classList.add('text-amber-600');
                } else {
                    charCount.classList.remove('text-amber-600');
                }
            });
            
            // Form submission
            document.getElementById('smsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate all phone numbers
                const phoneInputs = document.querySelectorAll('input[name="phone_numbers[]"]');
                let allValid = true;
                
                phoneInputs.forEach(input => {
                    if (!validatePhoneNumber(input)) {
                        allValid = false;
                    }
                });
                
                if (!allValid) {
                    showToast('Please enter valid 9-digit phone numbers', 'error');
                    return;
                }
                
                // Submit the form if all validations pass
                const formData = new FormData(this);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    // Create a temporary element to parse the HTML response
                    const tempElement = document.createElement('div');
                    tempElement.innerHTML = html;
                    
                    // Check if the response contains success or error indicators
                    const responseText = tempElement.textContent;
                    
                    if (responseText.includes('Success') || responseText.includes('Submitted Sucessfully')) {
                        showToast('SMS sent successfully!', 'success');
                        // Reset form after successful submission
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showToast('Error sending SMS. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                });
            });
            
            // Toast notification function
            function showToast(message, type) {
                const toast = document.getElementById('toast');
                const toastMessage = document.getElementById('toast-message');
                
                // Set message and type
                toastMessage.textContent = message;
                toast.className = 'toast';
                toast.classList.add(type === 'success' ? 'toast-success' : 'toast-error');
                
                // Show toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);
                
                // Hide toast after 3 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
            
            <?php if (!empty($result)): ?>
                // Show toast based on PHP result
                window.onload = function() {
                    <?php if (strpos($result, 'err') !== false): ?>
                        showToast('Error sending SMS. Please try again.', 'error');
                    <?php else: ?>
                        showToast('SMS sent successfully!', 'success');
                    <?php endif; ?>
                };
            <?php endif; ?>
            
            // Initialize validation for the first phone input
            const initialPhoneInput = document.querySelector('input[name="phone_numbers[]"]');
            initialPhoneInput.addEventListener('input', function() {
                validatePhoneNumber(this);
            });
        });
    </script>
</body>
</html>