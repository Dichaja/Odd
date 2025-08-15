<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'System Settings';
$activeNav = 'settings';
ob_start();

function fetchServerIp()
{
    $ch = curl_init("https://api.ipify.org?format=json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    if ($result) {
        $json = json_decode($result, true);
        return $json['ip'] ?? 'Unavailable';
    }
    return 'Unavailable';
}
$publicIp = fetchServerIp();
?>
<div class="min-h-screen bg-gray-50 font-rubik" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold text-secondary">System Settings</h1>
                <p class="text-gray-text">Configure SMS and Email providers</p>
                <div class="mt-3">
                    <div
                        class="inline-flex items-center gap-2 bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 text-sm">
                        <span class="text-gray-text">Server Public IP:</span>
                        <span id="serverIp" class="font-mono text-secondary"><?= htmlspecialchars($publicIp) ?></span>
                        <button id="copyIpBtn"
                            class="ml-1 px-2 py-1 rounded-lg bg-primary text-white text-xs hover:opacity-90 active:scale-95">Copy</button>
                        <span id="copyIpToast" class="ml-2 hidden text-green-600 text-xs">Copied</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="w-full lg:hidden mb-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-3">
                <div class="relative">
                    <button id="mobile-tab-toggle"
                        class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-sms text-primary"></i>
                            <span id="mobile-tab-label" class="font-medium text-secondary">SMS</span>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400" id="mobile-tab-chevron"></i>
                    </button>
                    <div id="mobile-tab-dropdown"
                        class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                        <div class="py-1">
                            <button
                                class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50"
                                data-tab="sms">
                                <i class="fas fa-sms text-primary"></i>
                                <span>SMS</span>
                            </button>
                            <button
                                class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50"
                                data-tab="email">
                                <i class="fas fa-envelope text-secondary"></i>
                                <span>Email</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-8">
            <div class="hidden lg:block w-64 flex-shrink-0">
                <nav class="space-y-2">
                    <button id="sms-tab"
                        class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl bg-primary/10 text-primary border border-primary/20"
                        onclick="switchTab('sms')">
                        <i class="fas fa-sms"></i>
                        <span>SMS</span>
                    </button>
                    <button id="email-tab"
                        class="tab-button w-full flex items-center gap-3 px-4 py-3 text-left rounded-xl text-gray-text hover:bg-gray-50 hover:text-secondary"
                        onclick="switchTab('email')">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </button>
                </nav>
            </div>

            <div class="flex-1">
                <div id="tab-sms" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                        <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-primary uppercase tracking-wide">Configured</p>
                                    <p id="statConfigured"
                                        class="text-xl sm:text-2xl font-bold text-secondary truncate">0</p>
                                </div>
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-primary/20 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                                    <i class="fas fa-cog text-primary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-secondary uppercase tracking-wide">Active
                                        Provider</p>
                                    <p id="statActive" class="text-xl sm:text-2xl font-bold text-secondary truncate">—
                                    </p>
                                </div>
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-secondary/10 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                                    <i class="fas fa-bolt text-secondary"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-secondary uppercase tracking-wide">Last Updated
                                    </p>
                                    <p id="statUpdated" class="text-xl sm:text-2xl font-bold text-secondary truncate">—
                                    </p>
                                </div>
                                <div
                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-secondary/10 rounded-lg flex items-center justify-center flex-shrink-0 ml-3">
                                    <i class="fas fa-clock text-secondary"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-5 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-secondary">SMS Providers</h2>
                                    <p class="text-sm text-gray-text">Manage credentials and select the active provider
                                    </p>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-text">Active:</span>
                                    <span id="activeProviderBadge"
                                        class="ml-2 inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs">—</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div class="rounded-2xl border border-gray-200 overflow-hidden bg-gray-50">
                                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                                <i class="fas fa-plug text-primary"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-secondary truncate">Collecto</div>
                                                <div class="text-xs text-gray-text whitespace-nowrap">CISSY Collecto API
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="inline-flex items-center gap-2 text-sm whitespace-nowrap">
                                                <input type="radio" name="activeProvider" value="collecto"
                                                    class="w-4 h-4 text-primary" id="radio-collecto">
                                                <span class="text-secondary">Set Active</span>
                                            </label>
                                            <button id="edit-collecto"
                                                class="px-3 py-2 text-sm bg-primary/10 text-primary rounded-lg hover:bg-primary/20">Edit</button>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-2 text-sm text-secondary">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">Username</span>
                                            <span id="collecto-username-mask"
                                                class="font-mono text-right truncate max-w-[60%] sm:max-w-[70%]">—</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">API Key</span>
                                            <span id="collecto-api-mask"
                                                class="font-mono text-right whitespace-nowrap">**********</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">Base URL</span>
                                            <span id="collecto-base-url"
                                                class="text-right truncate max-w-[60%] sm:max-w-[70%]"> </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-gray-200 overflow-hidden bg-gray-50">
                                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                                                <i class="fas fa-bolt text-secondary"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-secondary truncate">Speedamobile</div>
                                                <div class="text-xs text-gray-text whitespace-nowrap">Speedamobile SMS
                                                    API</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <label class="inline-flex items-center gap-2 text-sm whitespace-nowrap">
                                                <input type="radio" name="activeProvider" value="speedamobile"
                                                    class="w-4 h-4 text-primary" id="radio-speedamobile">
                                                <span class="text-secondary">Set Active</span>
                                            </label>
                                            <button id="edit-speedamobile"
                                                class="px-3 py-2 text-sm bg-secondary/10 text-secondary rounded-lg hover:bg-secondary/20">Edit</button>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-2 text-sm text-secondary">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">API ID</span>
                                            <span id="speed-apiid-mask"
                                                class="font-mono text-right truncate max-w-[60%] sm:max-w-[70%]">—</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">API Password</span>
                                            <span id="speed-apipwd-mask"
                                                class="font-mono text-right whitespace-nowrap">**********</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">Sender ID</span>
                                            <span id="speed-sender-id"
                                                class="font-mono text-right truncate max-w-[60%] sm:max-w-[70%]"></span>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-gray-text whitespace-nowrap">API URL</span>
                                            <span id="speed-api-url"
                                                class="text-right truncate max-w-[60%] sm:max-w-[70%]"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-email" class="space-y-6 hidden">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                        <div class="p-5 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-secondary">Email Settings</h2>
                            <p class="text-sm text-gray-text">SMTP configuration</p>
                        </div>
                        <div class="p-5">
                            <form class="space-y-4 max-w-xl" autocomplete="off" autocapitalize="off" autocorrect="off"
                                spellcheck="false">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1">Host</label>
                                        <input type="text"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                                            value="" disabled autocomplete="off">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1">Port</label>
                                        <input type="text"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                                            value="" disabled autocomplete="off">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1">Username</label>
                                        <input type="text"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                                            value="" disabled autocomplete="off">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1">From Name</label>
                                        <input type="text"
                                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                                            value="" disabled autocomplete="off">
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button type="button" class="px-4 py-2 bg-gray-200 text-secondary rounded-xl"
                                        disabled>Save</button>
                                    <span class="text-sm text-gray-text">To be configured later</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 hidden px-4 py-2 rounded-lg text-white">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-collecto" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('collecto')"></div>
    <div class="relative z-10 w-full max-w-md mx-auto top-20 bg-white rounded-2xl border border-gray-200 shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-plug text-primary"></i>
                </div>
                <div class="text-secondary font-semibold">Edit Collecto</div>
            </div>
            <button class="w-9 h-9 rounded-lg hover:bg-gray-100 flex items-center justify-center"
                onclick="closeModal('collecto')">
                <i class="fas fa-times text-gray-text"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="form-collecto" class="space-y-4" autocomplete="off" autocapitalize="off" autocorrect="off"
                spellcheck="false">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Username</label>
                    <input type="text" id="cissy_username"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">API Key</label>
                    <input type="password" id="cissy_api_key"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Base URL</label>
                    <input type="text" id="cissy_base_url"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button class="px-4 py-2 border border-gray-200 rounded-xl text-secondary"
                onclick="closeModal('collecto')">Cancel</button>
            <button id="save-collecto"
                class="px-4 py-2 bg-primary text-white rounded-xl hover:opacity-90 active:scale-95">Save</button>
        </div>
    </div>
</div>

<div id="modal-speedamobile" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('speedamobile')"></div>
    <div class="relative z-10 w-full max-w-md mx-auto top-20 bg-white rounded-2xl border border-gray-200 shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-secondary/10 flex items-center justify-center">
                    <i class="fas fa-bolt text-secondary"></i>
                </div>
                <div class="text-secondary font-semibold">Edit Speedamobile</div>
            </div>
            <button class="w-9 h-9 rounded-lg hover:bg-gray-100 flex items-center justify-center"
                onclick="closeModal('speedamobile')">
                <i class="fas fa-times text-gray-text"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="form-speedamobile" class="space-y-4" autocomplete="off" autocapitalize="off" autocorrect="off"
                spellcheck="false">
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">API ID</label>
                    <input type="text" id="speed_api_id"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">API Password</label>
                    <input type="password" id="speed_api_password"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">Sender ID</label>
                    <input type="text" id="speed_sender_id"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary mb-1">API URL</label>
                    <input type="text" id="speed_api_url"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded item-size-input"
                        autocomplete="new-password">
                </div>
            </form>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button class="px-4 py-2 border border-gray-200 rounded-xl text-secondary"
                onclick="closeModal('speedamobile')">Cancel</button>
            <button id="save-speedamobile"
                class="px-4 py-2 bg-primary text-white rounded-xl hover:opacity-90 active:scale-95">Save</button>
        </div>
    </div>
</div>

<div id="confirmActiveModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div
        class="relative w-full max-w-md mx-auto top-1/2 -translate-y-1/2 bg-white rounded-2xl border border-gray-200 shadow-2xl">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary">Confirm Update</h3>
                    <p class="text-sm text-gray-text" id="confirmActiveText">Switch active provider?</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button id="cancelActiveBtn"
                    class="px-4 py-2 border border-gray-300 text-secondary rounded-xl hover:bg-gray-50">Cancel</button>
                <button id="confirmActiveBtn"
                    class="px-4 py-2 bg-primary text-white rounded-xl hover:bg-primary/90">Yes, Update</button>
            </div>
        </div>
    </div>
</div>

<script>
    const API_BASE_URL = '<?= BASE_URL ?>admin/fetch/manageSettings.php';
    let currentTab = 'sms';
    let currentActiveProvider = null;
    let pendingActiveProvider = null;

    function switchTab(tab) {
        document.querySelectorAll('.tab-button').forEach(b => {
            b.classList.remove('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
            b.classList.add('text-gray-text', 'hover:bg-gray-50', 'hover:text-secondary');
        });
        document.getElementById(`${tab}-tab`).classList.add('bg-primary/10', 'text-primary', 'border', 'border-primary/20');
        document.getElementById('tab-sms').classList.add('hidden');
        document.getElementById('tab-email').classList.add('hidden');
        document.getElementById(`tab-${tab}`).classList.remove('hidden');
        currentTab = tab;
        const labels = { sms: { label: 'SMS', icon: 'fas fa-sms' }, email: { label: 'Email', icon: 'fas fa-envelope' } };
        const tabInfo = labels[tab] || labels.sms;
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);
    }

    function updateMobileTabLabel(label, icon) {
        const labelEl = document.getElementById('mobile-tab-label');
        const toggleBtn = document.getElementById('mobile-tab-toggle');
        if (labelEl && toggleBtn) {
            labelEl.textContent = label;
            const iconEl = toggleBtn.querySelector('i');
            if (iconEl) iconEl.className = `${icon} text-primary`;
        }
    }

    function toggleMobileTabDropdown() {
        const dd = document.getElementById('mobile-tab-dropdown');
        const ch = document.getElementById('mobile-tab-chevron');
        dd.classList.toggle('hidden');
        ch.classList.toggle('rotate-180');
    }

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 px-4 py-2 rounded-lg text-white';
        t.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 1800);
    }

    function copyIp() {
        const ip = document.getElementById('serverIp').textContent.trim();
        navigator.clipboard.writeText(ip).then(() => {
            const toast = document.getElementById('copyIpToast');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 1200);
        });
    }

    function openModal(which) {
        document.getElementById(`modal-${which}`).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(which) {
        document.getElementById(`modal-${which}`).classList.add('hidden');
        document.body.style.overflow = '';
        if (which === 'collecto') document.getElementById('form-collecto').reset();
        if (which === 'speedamobile') document.getElementById('form-speedamobile').reset();
    }

    function openConfirmActive(provider) {
        pendingActiveProvider = provider;
        const name = provider === 'collecto' ? 'Collecto' : 'Speedamobile';
        document.getElementById('confirmActiveText').textContent = `Switch active provider to ${name}?`;
        document.getElementById('confirmActiveModal').classList.remove('hidden');
    }
    function closeConfirmActive() {
        document.getElementById('confirmActiveModal').classList.add('hidden');
        pendingActiveProvider = null;
        document.getElementById('radio-collecto').checked = currentActiveProvider === 'collecto';
        document.getElementById('radio-speedamobile').checked = currentActiveProvider === 'speedamobile';
    }

    function setButtonLoading(btn, isLoading, idleText) {
        if (!btn) return;
        if (isLoading) {
            btn.dataset.idleText = idleText || btn.textContent.trim();
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        } else {
            btn.disabled = false;
            btn.textContent = btn.dataset.idleText || 'Save';
        }
    }

    function formatReadable(dtString) {
        if (!dtString) return '—';
        const d = new Date(dtString.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '—';
        const day = d.getDate();
        const ord = (n) => (n % 10 === 1 && n % 100 !== 11) ? 'st' : (n % 10 === 2 && n % 100 !== 12) ? 'nd' : (n % 10 === 3 && n % 100 !== 13) ? 'rd' : 'th';
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        let h = d.getHours();
        const m = d.getMinutes().toString().padStart(2, '0');
        const ampm = h >= 12 ? 'pm' : 'am';
        h = h % 12; if (h === 0) h = 12;
        return `${day}${ord(day)} ${months[d.getMonth()]}, ${d.getFullYear()} ${h}:${m} ${ampm}`;
    }

    async function loadSmsSettings() {
        try {
            const r = await fetch(`${API_BASE_URL}?action=getSmsSettings`);
            const d = await r.json();
            if (!d.success) { showToast(d.message || 'Failed to load SMS settings', 'error'); return; }

            currentActiveProvider = d.active_provider || null;
            document.getElementById('activeProviderBadge').textContent = currentActiveProvider ? (currentActiveProvider === 'collecto' ? 'Collecto' : 'Speedamobile') : '—';
            document.getElementById('radio-collecto').checked = currentActiveProvider === 'collecto';
            document.getElementById('radio-speedamobile').checked = currentActiveProvider === 'speedamobile';

            document.getElementById('collecto-username-mask').textContent = d.mask.collecto.username || '—';
            document.getElementById('collecto-api-mask').textContent = d.mask.collecto.api_key || '**********';
            document.getElementById('collecto-base-url').textContent = d.mask.collecto.base_url || '';

            document.getElementById('speed-apiid-mask').textContent = d.mask.speedamobile.api_id || '—';
            document.getElementById('speed-apipwd-mask').textContent = d.mask.speedamobile.api_password || '**********';
            document.getElementById('speed-sender-id').textContent = d.mask.speedamobile.sender_id || '';
            document.getElementById('speed-api-url').textContent = d.mask.speedamobile.api_url || '';

            document.getElementById('radio-collecto').disabled = !d.providers.collecto.configured;
            document.getElementById('radio-speedamobile').disabled = !d.providers.speedamobile.configured;

            document.getElementById('statConfigured').textContent = d.meta.configured_count || 0;
            document.getElementById('statActive').textContent = currentActiveProvider ? (currentActiveProvider === 'collecto' ? 'Collecto' : 'Speedamobile') : '—';
            document.getElementById('statUpdated').textContent = formatReadable(d.meta.last_updated);
        } catch (e) {
            showToast('Failed to load SMS settings', 'error');
        }
    }

    async function saveCollecto() {
        const btn = document.getElementById('save-collecto');
        setButtonLoading(btn, true, 'Save');
        const payload = {
            username: document.getElementById('cissy_username').value.trim() || null,
            api_key: document.getElementById('cissy_api_key').value.trim() || null,
            base_url: document.getElementById('cissy_base_url').value.trim() || null
        };
        try {
            const r = await fetch(`${API_BASE_URL}?action=saveCollecto`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const d = await r.json();
            if (!d.success) { showToast(d.message || 'Save failed', 'error'); setButtonLoading(btn, false); return; }
            showToast('Collecto saved');
            closeModal('collecto');
            await loadSmsSettings();
        } catch (e) {
            showToast('Save failed', 'error');
        } finally {
            setButtonLoading(btn, false);
        }
    }

    async function saveSpeedamobile() {
        const btn = document.getElementById('save-speedamobile');
        setButtonLoading(btn, true, 'Save');
        const payload = {
            api_id: document.getElementById('speed_api_id').value.trim() || null,
            api_password: document.getElementById('speed_api_password').value.trim() || null,
            sender_id: document.getElementById('speed_sender_id').value.trim() || null,
            api_url: document.getElementById('speed_api_url').value.trim() || null
        };
        try {
            const r = await fetch(`${API_BASE_URL}?action=saveSpeedamobile`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            const d = await r.json();
            if (!d.success) { showToast(d.message || 'Save failed', 'error'); setButtonLoading(btn, false); return; }
            showToast('Speedamobile saved');
            closeModal('speedamobile');
            await loadSmsSettings();
        } catch (e) {
            showToast('Save failed', 'error');
        } finally {
            setButtonLoading(btn, false);
        }
    }

    async function applyActive(provider, confirmBtn) {
        try {
            setButtonLoading(confirmBtn, true, 'Yes, Update');
            const r = await fetch(`${API_BASE_URL}?action=setActiveProvider`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ provider }) });
            const d = await r.json();
            if (!d.success) { showToast(d.message || 'Failed to set active', 'error'); closeConfirmActive(); return; }
            showToast('Active provider updated');
            closeConfirmActive();
            await loadSmsSettings();
        } catch (e) {
            showToast('Failed to set active', 'error');
            closeConfirmActive();
        } finally {
            setButtonLoading(confirmBtn, false);
        }
    }

    document.addEventListener('DOMContentLoaded', async () => {
        document.getElementById('copyIpBtn').addEventListener('click', copyIp);
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);
        document.querySelectorAll('.mobile-tab-option').forEach(opt => opt.addEventListener('click', e => { switchTab(e.currentTarget.getAttribute('data-tab')); toggleMobileTabDropdown(); }));
        document.getElementById('edit-collecto').addEventListener('click', () => openModal('collecto'));
        document.getElementById('edit-speedamobile').addEventListener('click', () => openModal('speedamobile'));
        document.getElementById('save-collecto').addEventListener('click', saveCollecto);
        document.getElementById('save-speedamobile').addEventListener('click', saveSpeedamobile);

        document.getElementById('radio-collecto').addEventListener('change', (e) => {
            if (e.target.checked && currentActiveProvider !== 'collecto') openConfirmActive('collecto');
        });
        document.getElementById('radio-speedamobile').addEventListener('change', (e) => {
            if (e.target.checked && currentActiveProvider !== 'speedamobile') openConfirmActive('speedamobile');
        });

        document.getElementById('cancelActiveBtn').addEventListener('click', closeConfirmActive);
        document.getElementById('confirmActiveBtn').addEventListener('click', async () => {
            if (!pendingActiveProvider) return closeConfirmActive();
            await applyActive(pendingActiveProvider, document.getElementById('confirmActiveBtn'));
        });

        switchTab('sms');
        await loadSmsSettings();
    });
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';