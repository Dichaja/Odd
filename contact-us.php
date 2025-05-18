<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Contact Us';
$activeNav = 'contact';

// Load contact page data from JSON file
function loadContactData()
{
    $filePath = __DIR__ . '/page-data/contact-us/contact-us.json';

    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        return json_decode($jsonData, true) ?: [];
    }

    // Return empty default structure if file doesn't exist
    return [
        'contactInfo' => [
            'phones' => [],
            'emails' => [],
            'location' => [],
            'social' => []
        ],
        'formSettings' => [
            'title' => 'Get in Touch',
            'description' => '',
            'buttonText' => 'Send Message',
            'fields' => [],
            'mapCoordinates' => [
                'latitude' => '0.31654191425996444',
                'longitude' => '32.629696775378866',
                'zoom' => 15
            ]
        ]
    ];
}

// Function to handle form submission
function handleFormSubmission($formFields)
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verify reCAPTCHA
        $recaptcha_secret = '6LdtJdcqAAAAAIlRxo2SWUTf1TyyFuBkdtjuR8iw';
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $response_data = json_decode($verify_response);

        if ($recaptcha_response && $response_data->success) {
            // Process the form data
            $formData = [];
            foreach ($formFields as $field) {
                $fieldId = $field['id'];
                $formData[$fieldId] = $_POST[$fieldId] ?? '';
            }

            // Here you would typically send an email or save to database
            // For now, just return a success message
            $name = $formData['name'] ?? 'Customer';
            return [
                'success' => true,
                'message' => "Thank you for your message, $name! We'll get back to you soon."
            ];
        } else {
            return [
                'success' => false,
                'message' => "reCAPTCHA verification failed. Please try again."
            ];
        }
    }
    return null;
}

// Load data from JSON
$contactData = loadContactData();

// Extract data from the loaded JSON
$contactInfo = $contactData['contactInfo'] ?? [];
$formSettings = $contactData['formSettings'] ?? [];

// Filter active phones and sort by order
$activePhones = array_filter($contactInfo['phones'] ?? [], function ($phone) {
    return isset($phone['active']) && $phone['active'] === true;
});
usort($activePhones, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active emails and sort by order
$activeEmails = array_filter($contactInfo['emails'] ?? [], function ($email) {
    return isset($email['active']) && $email['active'] === true;
});
usort($activeEmails, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active location lines and sort by order
$activeLocation = array_filter($contactInfo['location'] ?? [], function ($location) {
    return isset($location['active']) && $location['active'] === true;
});
usort($activeLocation, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Filter active social links and sort by order
$activeSocial = array_filter($contactInfo['social'] ?? [], function ($social) {
    return isset($social['active']) && $social['active'] === true;
});
usort($activeSocial, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Sort form fields by order
$formFields = $formSettings['fields'] ?? [];
usort($formFields, function ($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// reCAPTCHA site key
$recaptcha_site_key = '6LdtJdcqAAAAADWom9IW8lSg7L41BQbAJPrAW-Hf';

// Handle form submission
$submission_result = handleFormSubmission($formFields);

ob_start();
?>

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .contact-info-ribbon {
        background-color: white;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin: 2rem 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .info-section {
        display: flex;
        align-items: center;
        margin: 0.5rem 1rem 0.5rem 0;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-icon svg {
        max-width: 100%;
        max-height: 100%;
    }

    .info-content {
        display: flex;
        flex-direction: column;
    }

    .info-title {
        font-weight: bold;
        color: #333;
        margin-bottom: 0.25rem;
    }

    .info-detail {
        color: #666;
        margin: 0.1rem 0;
    }

    .social-section {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .social-title {
        font-weight: bold;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .social-links {
        display: flex;
        align-items: center;
    }

    .social-links a {
        margin: 0 0.5rem;
        color: #333;
        text-decoration: none;
        font-size: 1.25rem;
    }

    .contact-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        background-color: #f9f9f9;
        border-radius: 0.5rem;
        padding: 2rem;
    }

    .contact-form {
        flex: 1;
        min-width: 300px;
    }

    .map-container {
        flex: 1;
        min-width: 300px;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .contact-title {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 1rem;
    }

    .contact-subtitle {
        font-size: 1rem;
        color: #666;
        margin-bottom: 2rem;
        line-height: 1.5;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }

    .form-label .required {
        color: #C00000;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.375rem;
        font-size: 1rem;
        background-color: #fff;
        margin-bottom: 1rem;
    }

    .form-input:focus {
        outline: none;
        border-color: #C00000;
        box-shadow: 0 0 0 3px rgba(192, 0, 0, 0.1);
    }

    textarea.form-input {
        min-height: 150px;
        resize: vertical;
    }

    .submit-btn {
        background-color: #4CAF50;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.375rem;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-top: 1rem;
    }

    .submit-btn:hover {
        background-color: #45a049;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 0.375rem;
    }

    .alert-success {
        background-color: #DEF7EC;
        color: #03543F;
    }

    .alert-danger {
        background-color: #FDE8E8;
        color: #9B1C1C;
    }

    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-col {
        flex: 1;
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .contact-info-ribbon {
            flex-direction: column;
            align-items: flex-start;
        }

        .social-section {
            margin-top: 1rem;
            align-self: center;
        }

        .map-container {
            aspect-ratio: 1/1;
            height: auto;
        }
    }
</style>

<div class="container">
    <!-- Contact Info Ribbon -->
    <div class="contact-info-ribbon">
        <?php if (!empty($activePhones)): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                        stroke="#9370DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                        <path d="M14 2 C14 4.5 17.5 4.5 18 2"></path>
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Phone</div>
                    <?php foreach ($activePhones as $phone): ?>
                        <div class="info-detail"><?= htmlspecialchars($phone['number']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeEmails)): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                        stroke="#FFA500" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Email</div>
                    <?php foreach ($activeEmails as $email): ?>
                        <div class="info-detail"><?= htmlspecialchars($email['address']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeLocation)): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                        stroke="#9370DB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Location</div>
                    <?php foreach ($activeLocation as $location): ?>
                        <div class="info-detail"><?= htmlspecialchars($location['line']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($activeSocial)): ?>
            <!-- Social section with title above links -->
            <div class="social-section">
                <div class="social-title">Follow Us:</div>
                <div class="social-links">
                    <?php
                    $socialIcons = [
                        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>',
                        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>',
                        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>',
                        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>',
                        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>'
                    ];

                    foreach ($activeSocial as $social):
                        $icon = $socialIcons[$social['platform']] ?? $socialIcons['facebook'];
                        ?>
                        <a href="<?= htmlspecialchars($social['url']) ?>"
                            aria-label="<?= ucfirst(htmlspecialchars($social['platform'])) ?>" target="_blank" rel="noopener">
                            <?= $icon ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="contact-container">
        <div class="contact-form">
            <h1 class="contact-title"><?= htmlspecialchars($formSettings['title'] ?? 'Get in Touch') ?></h1>
            <p class="contact-subtitle"><?= htmlspecialchars($formSettings['description'] ?? '') ?></p>

            <?php if ($submission_result): ?>
                <div class="alert <?= $submission_result['success'] ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($submission_result['message']) ?>
                </div>
            <?php endif; ?>

            <form id="contact-form" method="POST" action="">
                <?php
                // Group fields into pairs for the form rows
                $fieldPairs = array_chunk($formFields, 2);
                foreach ($fieldPairs as $pair):
                    ?>
                    <div class="form-row">
                        <?php foreach ($pair as $field): ?>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="<?= $field['id'] ?>" class="form-label">
                                        <?= htmlspecialchars($field['label']) ?>
                                        <?php if ($field['required']): ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php if ($field['id'] === 'message'): ?>
                                        <textarea id="<?= $field['id'] ?>" name="<?= $field['id'] ?>" class="form-input"
                                            <?= $field['required'] ? 'required' : '' ?>
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>"></textarea>
                                    <?php else: ?>
                                        <input
                                            type="<?= $field['id'] === 'email' ? 'email' : ($field['id'] === 'phone' ? 'tel' : 'text') ?>"
                                            id="<?= $field['id'] ?>" name="<?= $field['id'] ?>" class="form-input"
                                            <?= $field['required'] ? 'required' : '' ?>
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php
                        // If there's only one field in the pair, add an empty column for layout
                        if (count($pair) === 1):
                            ?>
                            <div class="form-col"></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
                <button type="submit"
                    class="submit-btn"><?= htmlspecialchars($formSettings['buttonText'] ?? 'Send Message') ?></button>
            </form>
        </div>

        <div class="map-container">
            <!-- Google Maps Embed with coordinates from JSON -->
            <?php
            $latitude = $formSettings['mapCoordinates']['latitude'] ?? '0.31654191425996444';
            $longitude = $formSettings['mapCoordinates']['longitude'] ?? '32.629696775378866';
            $zoom = $formSettings['mapCoordinates']['zoom'] ?? '15';

            $mapUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7570741913936!2d{$longitude}!3d{$latitude}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMTgnNTkuNiJOIDMywrAzNyc1NC44IkU!5e0!3m2!1sen!2sus!4v1621234567890!5m2!1sen!2sus&z={$zoom}";
            ?>
            <iframe src="<?= $mapUrl ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Zzimba Online Head Office Location">
            </iframe>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_site_key ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Calculate and set map height to match form height on desktop
        function adjustMapHeight() {
            if (window.innerWidth > 768) {
                const formHeight = document.querySelector('.contact-form').offsetHeight;
                const formButton = document.querySelector('.submit-btn').offsetHeight;
                const formButtonMargin = 16; // 1rem top margin
                const mapContainer = document.querySelector('.map-container');

                // Set map height to match form height minus button height and its margin
                mapContainer.style.height = (formHeight - formButton - formButtonMargin) + 'px';
            } else {
                // Reset height on mobile to allow for square aspect ratio
                document.querySelector('.map-container').style.height = 'auto';
            }
        }

        // Run on load and resize
        adjustMapHeight();
        window.addEventListener('resize', adjustMapHeight);

        // Handle reCAPTCHA
        grecaptcha.ready(function () {
            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'contact'
            }).then(function (token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });

        document.getElementById('contact-form').addEventListener('submit', function (e) {
            e.preventDefault();
            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'contact'
            }).then(function (token) {
                document.getElementById('g-recaptcha-response').value = token;
                document.getElementById('contact-form').submit();
            });
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>