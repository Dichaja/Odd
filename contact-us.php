<?php
session_start();
require_once __DIR__ . '/config/config.php';
$pageTitle = 'Contact Us';
$activeNav = 'contact';

function loadContactData()
{
    $filePath = __DIR__ . '/page-data/contact-us/contact-us.json';
    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        return json_decode($json, true) ?: [];
    }
    return [
        'contactInfo' => ['phones' => [], 'emails' => [], 'location' => [], 'social' => []],
        'formSettings' => [
            'title' => 'Get in Touch',
            'description' => 'We would love to hear from you. Send us a message and we will respond as soon as possible.',
            'buttonText' => 'Send Message',
            'mapCoordinates' => [
                'latitude' => '0.31654191425996444',
                'longitude' => '32.629696775378866',
                'zoom' => 15
            ]
        ]
    ];
}

$contactData = loadContactData();
$contactInfo = $contactData['contactInfo'] ?? [];
$formSettings = $contactData['formSettings'] ?? [];

$activePhones = array_filter($contactInfo['phones'] ?? [], fn($p) => $p['active'] ?? false);
usort($activePhones, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));
$activeEmails = array_filter($contactInfo['emails'] ?? [], fn($e) => $e['active'] ?? false);
usort($activeEmails, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));
$activeLocation = array_filter($contactInfo['location'] ?? [], fn($l) => $l['active'] ?? false);
usort($activeLocation, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));
$activeSocial = array_filter($contactInfo['social'] ?? [], fn($s) => $s['active'] ?? false);
usort($activeSocial, fn($a, $b) => (($a['order'] ?? 999) - ($b['order'] ?? 999)));

$user = $_SESSION['user'] ?? [];
$loggedIn = !empty($user['logged_in']);
$isAdmin = !empty($user['is_admin']);
$showFields = !$loggedIn || $isAdmin;

ob_start();
?>

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .contact-info-ribbon {
        background: #fff;
        padding: 1.5rem;
        border-radius: .5rem;
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
        margin: .5rem 1rem .5rem 0;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-content {
        display: flex;
        flex-direction: column;
    }

    .info-title {
        font-weight: bold;
        color: #333;
        margin-bottom: .25rem;
    }

    .info-detail {
        color: #666;
        margin: .1rem 0;
    }

    .social-section {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .social-title {
        font-weight: bold;
        color: #333;
        margin-bottom: .5rem;
    }

    .social-links {
        display: flex;
        align-items: center;
    }

    .social-links a {
        margin: 0 .5rem;
        color: #333;
        text-decoration: none;
        font-size: 1.25rem;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }

        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }

    .alert-enter {
        animation: slideIn 0.3s ease-out;
    }

    .alert-exit {
        animation: fadeOut 0.3s ease-out forwards;
    }

    .btn-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media(max-width:768px) {
        .contact-info-ribbon {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="container">
    <div class="contact-info-ribbon">
        <?php if ($activePhones): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#9370DB"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2
                   19.79 19.79 0 0 1-8.63-3.07
                   19.5 19.5 0 0 1-6-6
                   19.79 19.79 0 0 1-3.07-8.67
                   A2 2 0 0 1 4.11 2h3
                   a2 2 0 0 1 2 1.72
                   12.84 12.84 0 0 0 .7 2.81
                   2 2 0 0 1-.45 2.11
                   L8.09 9.91a16 16 0 0 0 6 6
                   l1.27-1.27a2 2 0 0 1 2.11-.45
                   12.84 12.84 0 0 0 2.81.7
                   A2 2 0 0 1 22 16.92z" />
                        <path d="M14 2C14 4.5 17.5 4.5 18 2" />
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Phone</div>
                    <?php foreach ($activePhones as $p): ?>
                        <div class="info-detail"><?= htmlspecialchars($p['number']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($activeEmails): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#FFA500"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12
                   c0 1.1-.9 2-2 2H4
                   c-1.1 0-2-.9-2-2V6
                   c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Email</div>
                    <?php foreach ($activeEmails as $e): ?>
                        <div class="info-detail"><?= htmlspecialchars($e['address']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($activeLocation): ?>
            <div class="info-section">
                <div class="info-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" stroke="#9370DB"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13
                   a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div class="info-content">
                    <div class="info-title">Location</div>
                    <?php foreach ($activeLocation as $l): ?>
                        <div class="info-detail"><?= htmlspecialchars($l['line']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($activeSocial): ?>
            <div class="social-section">
                <div class="social-title">Follow Us:</div>
                <div class="social-links">
                    <?php
                    $icons = [
                        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
                        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
                        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
                        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>',
                        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
                    ];
                    foreach ($activeSocial as $s):
                        $icon = $icons[$s['platform']] ?? $icons['facebook'];
                        ?>
                        <a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"
                            aria-label="<?= ucfirst(htmlspecialchars($s['platform'])) ?>"><?= $icon ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex flex-wrap gap-8 bg-gray-50 rounded-lg p-8 font-rubik">
        <div class="flex-1 min-w-80">
            <h1 class="text-4xl font-bold text-secondary mb-4"><?= htmlspecialchars($formSettings['title']) ?></h1>
            <p class="text-gray-600 mb-8 text-lg leading-relaxed"><?= htmlspecialchars($formSettings['description']) ?>
            </p>

            <div id="form-alert" class="mb-6"></div>

            <form id="contact-form" class="space-y-6">
                <?php if ($showFields): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                                Full Name <span class="text-primary">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your full name">
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                                Phone Number <span class="text-primary">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your phone number">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                                Email Address <span class="text-primary">*</span>
                            </label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your email address">
                        </div>
                        <div class="space-y-2">
                            <label for="subject" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                                Subject <span class="text-primary">*</span>
                            </label>
                            <input type="text" id="subject" name="subject" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter message subject">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <label for="subject" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                            Subject <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                            placeholder="Enter message subject">
                    </div>
                <?php endif; ?>
                <div class="space-y-2">
                    <label for="message" class="block text-sm font-semibold text-secondary uppercase tracking-wide">
                        Message <span class="text-primary">*</span>
                    </label>
                    <textarea id="message" name="message" required rows="5"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400 resize-y"
                        placeholder="Enter your message here..."></textarea>
                </div>
                <button type="submit" id="submit-btn"
                    class="bg-primary hover:bg-primary/90 text-white font-semibold py-4 px-8 rounded-lg transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3 min-w-48 uppercase tracking-wide">
                    <span id="btn-text"><?= htmlspecialchars($formSettings['buttonText']) ?></span>
                    <div id="btn-spinner" class="hidden">
                        <div class="btn-spinner"></div>
                    </div>
                </button>
            </form>
        </div>

        <div class="flex-1 min-w-80 rounded-lg overflow-hidden shadow-lg">
            <?php
            $lat = $formSettings['mapCoordinates']['latitude'];
            $lng = $formSettings['mapCoordinates']['longitude'];
            $zoom = $formSettings['mapCoordinates']['zoom'];
            $mapUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7570741913936!2d{$lng}!3d{$lat}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMTgnNTkuNiJOIDMywrAzNyc1NC44IkU!5e0!3m2!1sen!2sus&z={$zoom}";
            ?>
            <iframe src="<?= $mapUrl ?>" width="100%" height="100%" style="border:0; min-height: 500px;" allowfullscreen
                loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Our Location"
                class="w-full h-full"></iframe>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('contact-form');
        const alertBox = document.getElementById('form-alert');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');
        const STORAGE_KEY = 'contactSubmissions';

        function showAlert(msg, isSuccess) {
            const alertClass = isSuccess
                ? 'bg-green-50 border border-green-200 text-green-800'
                : 'bg-red-50 border border-red-200 text-red-800';

            const iconSvg = isSuccess
                ? '<svg class="w-5 h-5 text-green-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                : '<svg class="w-5 h-5 text-red-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

            alertBox.innerHTML = `
                <div class="alert-enter ${alertClass} p-4 rounded-lg flex items-start gap-3 font-rubik">
                    ${iconSvg}
                    <div class="flex-1">
                        <p class="font-medium">${msg}</p>
                    </div>
                </div>
            `;

            setTimeout(() => {
                const alertElement = alertBox.querySelector('div');
                if (alertElement) {
                    alertElement.classList.remove('alert-enter');
                    alertElement.classList.add('alert-exit');
                    setTimeout(() => {
                        alertBox.innerHTML = '';
                    }, 300);
                }
            }, 10000);
        }

        function getTimes() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch {
                return [];
            }
        }

        function saveTimes(arr) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(arr));
        }

        function canSubmit() {
            const now = Date.now();
            const cutoff = now - 30 * 60 * 1000;
            let times = getTimes().filter(ts => ts >= cutoff);
            saveTimes(times);
            return times.length < 5;
        }

        function recordSubmit() {
            const now = Date.now();
            let times = getTimes().filter(ts => ts >= now - 30 * 60 * 1000);
            times.push(now);
            saveTimes(times);
        }

        form.addEventListener('submit', e => {
            e.preventDefault();

            if (!canSubmit()) {
                showAlert('You have reached the maximum number of submissions. Please try again after 30 minutes.', false);
                return;
            }

            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');

            recordSubmit();

            const data = new FormData(form);
            fetch('fetch/manageContactUs.php', {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(json => {
                    showAlert(json.message, json.success);
                    if (json.success) {
                        form.reset();
                    }
                })
                .catch(() => {
                    showAlert('We encountered a technical issue while sending your message. Please try again or contact us directly.', false);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    btnText.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                });
        });
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>