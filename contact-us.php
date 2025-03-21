<?php
$pageTitle = 'Contact Us';
$activeNav = 'contact';
require_once __DIR__ . '/config/config.php';
ob_start();

// reCAPTCHA site key
$recaptcha_site_key = '6LdtJdcqAAAAADWom9IW8lSg7L41BQbAJPrAW-Hf';

// Function to handle form submission
function handleFormSubmission()
{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verify reCAPTCHA
        $recaptcha_secret = '6LdtJdcqAAAAAIlRxo2SWUTf1TyyFuBkdtjuR8iw';
        $recaptcha_response = $_POST['g-recaptcha-response'];

        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $response_data = json_decode($verify_response);

        if ($response_data->success) {
            // Process the form data
            $name = $_POST['name'];
            $email = $_POST['email'];
            $message = $_POST['message'];

            // Here you would typically send an email or save to database
            return "Thank you for your message, $name! We'll get back to you soon.";
        } else {
            return "reCAPTCHA verification failed. Please try again.";
        }
    }
    return null;
}

$submission_result = handleFormSubmission();
?>

<style>
    .contact-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 2rem;
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .contact-form {
        flex: 1;
        min-width: 300px;
    }

    .contact-info {
        flex: 1;
        min-width: 300px;
    }

    .contact-title {
        font-size: 2.5rem;
        color: #C00000;
        margin-bottom: 1rem;
    }

    .contact-subtitle {
        font-size: 1.25rem;
        color: #4B5563;
        margin-bottom: 2rem;
    }

    .form-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #D1D5DB;
        border-radius: 0.375rem;
        font-size: 1rem;
        padding-top: 1.25rem;
        padding-bottom: 0.625rem;
    }

    .form-input:focus {
        outline: none;
        border-color: #C00000;
        box-shadow: 0 0 0 3px rgba(192, 0, 0, 0.1);
    }

    .floating-label {
        position: absolute;
        left: 0.75rem;
        top: 0.75rem;
        font-size: 1rem;
        color: #6B7280;
        pointer-events: none;
        transition: all 0.2s ease-out;
        background-color: white;
        padding: 0 0.25rem;
    }

    .form-input:focus ~ .floating-label,
    .form-input:not(:placeholder-shown) ~ .floating-label {
        transform: translateY(-1.4rem) scale(0.85);
        color: #C00000;
    }

    .form-input:focus ~ .floating-label {
        color: #C00000;
    }

    .submit-btn {
        background-color: #C00000;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.375rem;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
        background-color: #A00000;
    }

    .contact-details {
        background-color: #F3F4F6;
        padding: 2rem;
        border-radius: 0.5rem;
        height: 100%;
    }

    .contact-details h3 {
        font-size: 1.5rem;
        color: #1F2937;
        margin-bottom: 1rem;
    }

    .contact-details p {
        margin-bottom: 0.5rem;
        color: #4B5563;
    }

    .contact-details a {
        color: #C00000;
        text-decoration: none;
    }

    .contact-details a:hover {
        text-decoration: underline;
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
</style>

<div class="contact-container">
    <div class="contact-form">
        <h1 class="contact-title">Love to Hear From You</h1>
        <p class="contact-subtitle">Ask a question or give us feedback!</p>

        <?php if ($submission_result): ?>
            <div class="alert <?= strpos($submission_result, 'Thank you') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= $submission_result ?>
            </div>
        <?php endif; ?>

        <form id="contact-form" method="POST" action="">
            <div class="form-group">
                <input type="text" id="name" name="name" class="form-input" required placeholder=" ">
                <label for="name" class="floating-label">Name</label>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" class="form-input" required placeholder=" ">
                <label for="email" class="floating-label">Email</label>
            </div>
            <div class="form-group">
                <textarea id="message" name="message" class="form-input" rows="5" required placeholder=" "></textarea>
                <label for="message" class="floating-label">Message</label>
            </div>
            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
            <button type="submit" class="submit-btn">Send Message</button>
        </form>
    </div>

    <div class="contact-info">
        <div class="contact-details">
            <h3>Contact Information</h3>
            <p><a href="mailto:halo@zzimbaonline.com">halo@zzimbaonline.com</a></p>
            <p><a href="tel:+256392003406">+256 392 003-406</a></p>
            <p>📍 Plaza Building Luzira</p>
            <p>📱 The Engineering Marksmen Ltd.</p>
            <p>📧 P.O Box 129572 Kampala - Uganda</p>
        </div>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptcha_site_key ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        notifications.info('We\'d love to hear from you!', 'Contact Us');

        grecaptcha.ready(function() {
            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'contact'
            }).then(function(token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });

        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault();
            grecaptcha.execute('<?= $recaptcha_site_key ?>', {
                action: 'contact'
            }).then(function(token) {
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