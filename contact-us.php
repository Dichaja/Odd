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
        margin: 0 auto
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
        box-shadow: 0 2px 10px rgba(0, 0, 0, .05)
    }

    .info-section {
        display: flex;
        align-items: center;
        margin: .5rem 1rem .5rem 0
    }

    .info-icon {
        width: 40px;
        height: 40px;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center
    }

    .info-content {
        display: flex;
        flex-direction: column
    }

    .info-title {
        font-weight: bold;
        color: #333;
        margin-bottom: .25rem
    }

    .info-detail {
        color: #666;
        margin: .1rem 0
    }

    .social-section {
        display: flex;
        flex-direction: column;
        align-items: center
    }

    .social-title {
        font-weight: bold;
        color: #333;
        margin-bottom: .5rem
    }

    .social-links {
        display: flex;
        align-items: center
    }

    .social-links a {
        margin: 0 .5rem;
        color: #333;
        text-decoration: none;
        font-size: 1.25rem;
        display: inline-flex;
        align-items: center;
        gap: .25rem
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0)
        }

        to {
            opacity: 0;
            transform: translateY(-20px)
        }
    }

    .alert-enter {
        animation: slideIn .3s ease-out
    }

    .alert-exit {
        animation: fadeOut .3s ease-out forwards
    }

    .btn-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, .3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite
    }

    @keyframes spin {
        to {
            transform: rotate(360deg)
        }
    }

    @media(max-width:768px) {
        .contact-info-ribbon {
            flex-direction: column;
            align-items: flex-start
        }
    }

    [x-cloak] {
        display: none !important
    }
</style>

<div class="container" x-data="contactForm()" x-init="init()" x-cloak>
    <div class="contact-info-ribbon">
        <?php if ($activePhones): ?>
            <div class="info-section">
                <div class="info-icon">
                    <i data-lucide="phone" class="w-6 h-6 text-violet-500"></i>
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
                    <i data-lucide="mail" class="w-6 h-6 text-amber-500"></i>
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
                    <i data-lucide="map-pin" class="w-6 h-6 text-violet-500"></i>
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
                    $map = [
                        'facebook' => 'facebook',
                        'instagram' => 'instagram',
                        'linkedin' => 'linkedin',
                        'twitter' => 'twitter',
                        'whatsapp' => 'message-circle'
                    ];
                    foreach ($activeSocial as $s):
                        $iconName = $map[strtolower($s['platform'])] ?? 'share-2';
                        ?>
                        <a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"
                            aria-label="<?= ucfirst(htmlspecialchars($s['platform'])) ?>">
                            <i data-lucide="<?= htmlspecialchars($iconName) ?>" class="w-5 h-5"></i>
                        </a>
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

            <div id="form-alert" x-show="alertMessage" x-transition.opacity.duration.200ms class="mb-6">
                <div :class="alertSuccess ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'"
                    class="alert-enter p-4 rounded-lg flex items-start gap-3">
                    <i :data-lucide="alertSuccess ? 'check-circle2' : 'alert-circle'" class="w-5 h-5"
                        x-init="$nextTick(() => { if (window.lucide) lucide.createIcons(); })"></i>
                    <div class="flex-1">
                        <p class="font-medium" x-text="alertMessage"></p>
                    </div>
                </div>
            </div>

            <form x-ref="form" @submit.prevent="submit" class="space-y-6">
                <?php if ($showFields): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name"
                                class="block text-sm font-semibold text-secondary uppercase tracking-wide">Full Name <span
                                    class="text-primary">*</span></label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your full name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                        </div>
                        <div class="space-y-2">
                            <label for="phone"
                                class="block text-sm font-semibold text-secondary uppercase tracking-wide">Phone Number
                                <span class="text-primary">*</span></label>
                            <input type="tel" id="phone" name="phone" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your phone number" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="email"
                                class="block text-sm font-semibold text-secondary uppercase tracking-wide">Email Address
                                <span class="text-primary">*</span></label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter your email address"
                                value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        <div class="space-y-2">
                            <label for="subject"
                                class="block text-sm font-semibold text-secondary uppercase tracking-wide">Subject <span
                                    class="text-primary">*</span></label>
                            <input type="text" id="subject" name="subject" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                                placeholder="Enter message subject">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <label for="subject"
                            class="block text-sm font-semibold text-secondary uppercase tracking-wide">Subject <span
                                class="text-primary">*</span></label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400"
                            placeholder="Enter message subject">
                    </div>
                <?php endif; ?>
                <div class="space-y-2">
                    <label for="message"
                        class="block text-sm font-semibold text-secondary uppercase tracking-wide">Message <span
                            class="text-primary">*</span></label>
                    <textarea id="message" name="message" required rows="5"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-secondary placeholder-gray-400 resize-y"
                        placeholder="Enter your message here..."></textarea>
                </div>
                <button type="submit" :disabled="submitting"
                    class="bg-primary hover:bg-primary/90 text-white font-semibold py-4 px-8 rounded-lg transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-3 min-w-48 uppercase tracking-wide">
                    <span x-show="!submitting"><?= htmlspecialchars($formSettings['buttonText']) ?></span>
                    <span x-show="submitting" class="flex items-center gap-3">
                        <div class="btn-spinner"></div>
                        Sending...
                    </span>
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
    function contactForm() {
        return {
            submitting: false,
            alertMessage: '',
            alertSuccess: true,
            STORAGE_KEY: 'contactSubmissions',
            init() { this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); }); },
            getTimes() {
                try { return JSON.parse(localStorage.getItem(this.STORAGE_KEY)) || [] } catch (e) { return [] }
            },
            saveTimes(arr) { localStorage.setItem(this.STORAGE_KEY, JSON.stringify(arr)) },
            canSubmit() {
                const now = Date.now(), cutoff = now - 30 * 60 * 1000;
                const times = this.getTimes().filter(ts => ts >= cutoff);
                this.saveTimes(times);
                return times.length < 5;
            },
            recordSubmit() {
                const now = Date.now(), cutoff = now - 30 * 60 * 1000;
                const times = this.getTimes().filter(ts => ts >= cutoff);
                times.push(now); this.saveTimes(times);
            },
            showAlert(msg, success) {
                this.alertMessage = msg; this.alertSuccess = success;
                this.$nextTick(() => { if (window.lucide && lucide.createIcons) lucide.createIcons(); });
                setTimeout(() => { this.alertMessage = ''; }, 10000);
            },
            submit() {
                if (!this.canSubmit()) { this.showAlert('You have reached the maximum number of submissions. Please try again after 30 minutes.', false); return; }
                this.submitting = true; this.recordSubmit();
                const data = new FormData(this.$refs.form);
                fetch('fetch/manageContactUs.php', { method: 'POST', body: data })
                    .then(r => r.json())
                    .then(j => { this.showAlert(j.message, !!j.success); if (j.success) { this.$refs.form.reset(); } })
                    .catch(() => { this.showAlert('We encountered a technical issue while sending your message. Please try again or contact us directly.', false); })
                    .finally(() => { this.submitting = false; });
            }
        }
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>