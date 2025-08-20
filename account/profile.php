<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';
ob_start();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">

<div class="min-h-screen text-gray-900 dark:text-white" id="app-container">
    <div class="bg-white dark:bg-secondary border-b border-gray-100 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-6">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Profile
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            id="profile-status-display">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap">Account Status
                        </p>
                    </div>
                    <i
                        class="fas fa-user-check absolute bottom-3 right-3 text-3xl text-blue-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Email
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap"
                            id="email-status-display">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            id="email-value-display" title="Not Set">Not Set</p>
                    </div>
                    <i
                        class="fas fa-envelope absolute bottom-3 right-3 text-3xl text-green-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Phone
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap"
                            id="phone-status-display">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            id="phone-value-display" title="Not Set">Not Set</p>
                    </div>
                    <i
                        class="fas fa-phone absolute bottom-3 right-3 text-3xl text-purple-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-6">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Member
                            Since</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            id="member-since-display">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap"
                            id="last-login-display">Last Login</p>
                    </div>
                    <i
                        class="fas fa-calendar absolute bottom-3 right-3 text-3xl text-orange-600/30 dark:text-white/20"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 mb-6 md:mb-8">
            <div class="border-b border-gray-100 dark:border-white/10">
                <div class="hidden md:block">
                    <nav class="flex space-x-8 px-6 overflow-x-auto pb-2" aria-label="Tabs">
                        <button id="overview-tab"
                            class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-primary text-primary"
                            onclick="switchTab('overview')"><i class="fas fa-user mr-2"></i>Profile Overview</button>
                        <button id="security-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 dark:text-white/70 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('security')"><i class="fas fa-shield-alt mr-2"></i>Security</button>
                    </nav>
                </div>

                <div class="md:hidden px-4 sm:px-6 py-3">
                    <div class="relative">
                        <button id="mobile-tab-toggle"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user text-primary"></i>
                                <span id="mobile-tab-label" class="font-medium text-secondary dark:text-white">Profile
                                    Overview</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 dark:text-white/50 transition-transform duration-200"
                                id="mobile-tab-chevron"></i>
                        </button>

                        <div id="mobile-tab-dropdown"
                            class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 rounded-xl shadow-lg z-50">
                            <div class="py-2">
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition-colors"
                                    data-tab="overview">
                                    <i class="fas fa-user text-blue-600"></i>
                                    <span class="text-secondary dark:text-white">Profile Overview</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition-colors"
                                    data-tab="security">
                                    <i class="fas fa-shield-alt text-purple-600"></i>
                                    <span class="text-secondary dark:text-white">Security</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content">
            <div id="overview-content" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                            <div class="text-center">
                                <div
                                    class="w-32 h-32 rounded-full overflow-hidden border-4 border-white dark:border-white/10 shadow-md mb-4 mx-auto">
                                    <img id="user-profile-pic"
                                        src="https://api.dicebear.com/7.x/initials/svg?seed=U&size=128&radius=50"
                                        alt="Profile Picture" class="w-full h-full object-cover">
                                </div>
                                <h1 class="text-2xl font-bold text-secondary dark:text-white mb-1 truncate max-w-[240px] mx-auto"
                                    id="user-fullname">Loading…</h1>
                                <p class="text-gray-600 dark:text-white/70 mb-4 truncate max-w-[240px] mx-auto"
                                    id="user-username">@loading</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-white/70">
                                    <p id="user-joined">Member since …</p>
                                    <p id="user-lastlogin">Last login: …</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div
                            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 bg-user-primary/10 rounded-lg grid place-items-center">
                                    <i class="fas fa-info-circle text-user-primary"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-secondary dark:text-white">Account Information
                                </h3>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                    <div class="relative bg-gray-50 dark:bg-white/5 rounded-xl p-4"
                                        id="names-overview-card">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            onclick="openEditNamesModal()">
                                            <i class="fas fa-pen text-gray-600 dark:text-white/80 text-sm"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-blue-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i class="fas fa-user text-blue-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Full Name</h4>
                                            <i class="fas fa-exclamation-circle text-red-500 hidden"
                                                id="names-required-icon" title="Required field"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate" id="overview-names"
                                            title="Loading...">Loading...</p>
                                    </div>

                                    <div class="relative bg-gray-50 dark:bg-white/5 rounded-xl p-4"
                                        id="email-overview-card">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            onclick="editEmail()">
                                            <i class="fas fa-pen text-gray-600 dark:text-white/80 text-sm"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-green-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i class="fas fa-envelope text-green-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Email Address</h4>
                                            <i class="fas fa-exclamation-circle text-red-500 hidden"
                                                id="email-required-icon" title="Required field"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate" id="overview-email"
                                            title="Loading...">Loading...</p>
                                    </div>

                                    <div class="relative bg-gray-50 dark:bg-white/5 rounded-xl p-4"
                                        id="phone-overview-card">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            onclick="editPhone()">
                                            <i class="fas fa-pen text-gray-600 dark:text-white/80 text-sm"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-purple-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i class="fas fa-phone text-purple-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Phone Number</h4>
                                            <i class="fas fa-exclamation-circle text-red-500 hidden"
                                                id="phone-required-icon" title="Required field"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate" id="overview-phone"
                                            title="Loading...">Loading...</p>
                                    </div>

                                    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div
                                                class="w-8 h-8 bg-orange-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i class="fas fa-shield-alt text-orange-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Account Status</h4>
                                        </div>
                                        <p id="overview-status" class="text-gray-700 dark:text-white/80">Loading...</p>
                                    </div>
                                </div>

                                <div class="hidden md:flex flex-wrap gap-3 pt-2">
                                    <button onclick="switchTab('security')"
                                        class="px-6 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-colors font-medium"><i
                                            class="fas fa-shield-alt mr-2"></i>Security Settings</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="security-content" class="tab-content hidden">
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                <i class="fas fa-lock text-orange-600 dark:text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-secondary dark:text-white">Update Password</h3>
                        </div>

                        <button onclick="showModal('changePasswordModal')"
                            class="px-6 py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-all duration-200 font-medium shadow-lg shadow-orange-600/25 hover:shadow-xl hover:shadow-orange-600/30"><i
                                class="fas fa-key mr-2"></i>Udpate Password</button>
                    </div>

                    <div
                        class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                <i class="fas fa-user-slash text-red-600 dark:text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-secondary dark:text-white">Account Deletion</h3>
                        </div>

                        <div
                            class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 mb-4 border border-red-200 dark:border-red-700/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-red-900 dark:text-red-200">Danger Zone</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">Once you delete your account,
                                        there is no going back. Please be certain.</p>
                                </div>
                            </div>
                        </div>

                        <button onclick="showModal('deleteAccountModal')"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium shadow-lg shadow-red-600/25 hover:shadow-xl hover:shadow-red-600/30"><i
                                class="fas fa-trash mr-2"></i>Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="incompleteProfileModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('incompleteProfileModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Complete Your Profile</h3>
                <button onclick="hideModal('incompleteProfileModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 grid place-items-center">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-gray-700 dark:text-white/80">To access all features and keep your account secure,
                finish the steps below.</p>
            <ul id="incomplete-list" class="space-y-2 text-sm text-gray-700 dark:text-white/80"></ul>
            <button id="start-complete-profile-btn"
                class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium"><i
                    class="fas fa-play mr-2"></i>Complete Profile Now</button>
        </div>
    </div>
</div>

<div id="editNamesModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('editNamesModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Names</h3>
                <button onclick="hideModal('editNamesModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 grid place-items-center">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="editNamesForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="firstName"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">First Name *</label>
                        <input type="text" id="firstName" name="first_name"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                    </div>
                    <div>
                        <label for="lastName"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Last Name</label>
                        <input type="text" id="lastName" name="last_name"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                    </div>
                </div>
                <div id="names-form-error" class="text-red-500 text-sm hidden"></div>
                <button type="submit"
                    class="w-full px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium"><i
                        class="fas fa-save mr-2"></i>Update Names</button>
            </form>
        </div>
    </div>
</div>

<div id="editEmailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('editEmailModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Email Address</h3>
                <button onclick="hideModal('editEmailModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="emailStep1" class="space-y-4">
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                    <p class="text-sm text-gray-700 dark:text-white/80 mb-1">Current Email:</p>
                    <p class="font-medium text-secondary dark:text-white truncate" id="current-email-modal"
                        title="Loading...">Loading...</p>
                </div>
                <div id="verifyExistingEmailSection">
                    <p class="text-sm text-gray-600 dark:text-white/70 mb-3">To change your email, first verify your
                        current email address.</p>
                    <button type="button" id="verifyExistingEmailBtn"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium"><i
                            class="fas fa-shield-alt mr-2"></i>Verify Current Email</button>
                </div>
                <div id="noExistingEmailSection" class="hidden">
                    <p class="text-sm text-gray-600 dark:text-white/70 mb-3">You don't have an email set. Enter your new
                        email address.</p>
                    <div>
                        <label for="newEmailDirect"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Email
                            Address</label>
                        <input type="email" id="newEmailDirect"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                    </div>
                    <button type="button" id="sendDirectEmailOTPBtn"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium mt-3"><i
                            class="fas fa-paper-plane mr-2"></i>Send Verification Code</button>
                </div>
            </div>

            <div id="emailStep2" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your current
                    email.</p>
                <div id="otp-existing-email" class="flex gap-2 justify-center"></div>
                <input type="hidden" id="existingEmailOTP">
                <button type="button" id="verifyExistingEmailOTPBtn"
                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium"><i
                        class="fas fa-check mr-2"></i>Verify Code</button>
            </div>

            <div id="emailStep3" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter your new email address.</p>
                <div>
                    <label for="newEmail" class="block text-sm font-semibold text-secondary dark:text-white mb-2">New
                        Email Address</label>
                    <input type="email" id="newEmail"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        autocomplete="off">
                </div>
                <button type="button" id="sendNewEmailOTPBtn"
                    class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium"><i
                        class="fas fa-paper-plane mr-2"></i>Send Verification Code</button>
            </div>

            <div id="emailStep4" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your new email.
                </p>
                <div id="otp-new-email" class="flex gap-2 justify-center"></div>
                <input type="hidden" id="newEmailOTP">
                <button type="button" id="verifyNewEmailOTPBtn"
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium"><i
                        class="fas fa-check-circle mr-2"></i>Complete Update</button>
            </div>

            <div id="email-form-error" class="text-red-500 text-sm mt-3 hidden"></div>
        </div>
    </div>
</div>

<div id="editPhoneModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('editPhoneModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Phone Number</h3>
                <button onclick="hideModal('editPhoneModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="phoneStep1" class="space-y-4">
                <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                    <p class="text-sm text-gray-700 dark:text-white/80 mb-1">Current Phone:</p>
                    <p class="font-medium text-secondary dark:text-white truncate" id="current-phone-modal"
                        title="Loading...">Loading...</p>
                </div>
                <div id="verifyExistingPhoneSection">
                    <p class="text-sm text-gray-600 dark:text-white/70 mb-3">To change your phone, first verify your
                        current phone number.</p>
                    <button type="button" id="verifyExistingPhoneBtn"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium"><i
                            class="fas fa-shield-alt mr-2"></i>Verify Current Phone</button>
                </div>
                <div id="noExistingPhoneSection" class="hidden">
                    <p class="text-sm text-gray-600 dark:text-white/70 mb-3">You don't have a phone set. Enter your new
                        phone number.</p>
                    <div>
                        <label for="newPhoneDirect"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Phone Number</label>
                        <input type="tel" id="newPhoneDirect"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                        <div id="phone-error-direct" class="text-red-500 text-xs mt-1 hidden"></div>
                    </div>
                    <button type="button" id="sendDirectPhoneOTPBtn"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium mt-3"><i
                            class="fas fa-paper-plane mr-2"></i>Send Verification Code</button>
                </div>
            </div>

            <div id="phoneStep2" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your current
                    phone.</p>
                <div id="otp-existing-phone" class="flex gap-2 justify-center"></div>
                <input type="hidden" id="existingPhoneOTP">
                <button type="button" id="verifyExistingPhoneOTPBtn"
                    class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium"><i
                        class="fas fa-check mr-2"></i>Verify Code</button>
            </div>

            <div id="phoneStep3" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter your new phone number.</p>
                <div>
                    <label for="newPhone" class="block text-sm font-semibold text-secondary dark:text-white mb-2">New
                        Phone Number</label>
                    <input type="tel" id="newPhone"
                        class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        autocomplete="off">
                    <div id="phone-error" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>
                <button type="button" id="sendNewPhoneOTPBtn"
                    class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium"><i
                        class="fas fa-paper-plane mr-2"></i>Send Verification Code</button>
            </div>

            <div id="phoneStep4" class="space-y-4 hidden">
                <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your new phone.
                </p>
                <div id="otp-new-phone" class="flex gap-2 justify-center"></div>
                <input type="hidden" id="newPhoneOTP">
                <button type="button" id="verifyNewPhoneOTPBtn"
                    class="w-full px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium"><i
                        class="fas fa-check-circle mr-2"></i>Complete Update</button>
            </div>

            <div id="phone-form-error" class="text-red-500 text-sm mt-3 hidden"></div>
        </div>
    </div>
</div>

<div id="changePasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('changePasswordModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Update Password</h3>
                <button onclick="hideModal('changePasswordModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="changePasswordForm" class="space-y-4">
                <div>
                    <label for="currentPassword"
                        class="block text-sm font-semibold text-secondary dark:text-white mb-2">Current Password</label>
                    <div class="relative">
                        <input type="password" id="currentPassword" name="current_password"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                        <button type="button"
                            class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                            data-target="currentPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="newPassword" class="block text-sm font-semibold text-secondary dark:text-white mb-2">New
                        Password</label>
                    <div class="relative">
                        <input type="password" id="newPassword" name="new_password"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                        <button type="button"
                            class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                            data-target="newPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs text-gray-600 dark:text-white/70 mb-1">Password strength:</div>
                        <div class="w-full h-2 bg-gray-200 dark:bg-white/10 rounded-full">
                            <div id="passwordStrength" class="h-2 bg-red-500 rounded-full transition-all duration-200"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="confirmPassword"
                        class="block text-sm font-semibold text-secondary dark:text-white mb-2">Confirm New
                        Password</label>
                    <div class="relative">
                        <input type="password" id="confirmPassword" name="confirm_password"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                        <button type="button"
                            class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                            data-target="confirmPassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div
                    class="text-xs text-gray-600 dark:text-white/70 bg-gray-50 dark:bg-white/5 rounded-lg p-3 border border-gray-100 dark:border-white/10">
                    <p class="font-medium mb-2">Password must:</p>
                    <ul class="space-y-1">
                        <li id="length-check" class="flex items-center gap-2 text-gray-400 dark:text-white/60"><i
                                class="fas fa-times text-xs"></i>Be at least 8 characters long</li>
                        <li id="uppercase-check" class="flex items-center gap-2 text-gray-400 dark:text-white/60"><i
                                class="fas fa-times text-xs"></i>Include at least one uppercase letter</li>
                        <li id="lowercase-check" class="flex items-center gap-2 text-gray-400 dark:text-white/60"><i
                                class="fas fa-times text-xs"></i>Include at least one lowercase letter</li>
                        <li id="number-check" class="flex items-center gap-2 text-gray-400 dark:text-white/60"><i
                                class="fas fa-times text-xs"></i>Include at least one number</li>
                        <li id="special-check" class="flex items-center gap-2 text-gray-400 dark:text-white/60"><i
                                class="fas fa-times text-xs"></i>Include at least one special character</li>
                    </ul>
                </div>
                <div id="password-form-error" class="text-red-500 text-sm hidden"></div>
                <button type="submit"
                    class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium"><i
                        class="fas fa-key mr-2"></i>Update Password</button>
            </form>
        </div>
    </div>
</div>

<div id="deleteAccountModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('deleteAccountModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Delete Account</h3>
                <button onclick="hideModal('deleteAccountModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-white text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-secondary dark:text-white">Are you absolutely sure?</h4>
                    <p class="text-sm text-gray-600 dark:text-white/70">This action cannot be undone.</p>
                </div>
            </div>
            <p class="text-gray-700 dark:text-white/80 mb-6">This will permanently delete your account and remove all of
                your data from our servers. This action cannot be undone.</p>
            <div class="flex gap-3">
                <button type="button" id="deleteAccountCancelBtn"
                    class="flex-1 px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors font-medium"
                    onclick="hideModal('deleteAccountModal')">Cancel</button>
                <button type="button" id="deleteAccountConfirmBtn"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium"><i
                        class="fas fa-trash mr-2"></i>Delete Account</button>
            </div>
        </div>
    </div>
</div>

<div id="contactAdminModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideModal('contactAdminModal')"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
        <div class="p-6 border-b border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary dark:text-white">Contact Admin</h3>
                <button onclick="hideModal('contactAdminModal')"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500 dark:text-white/60"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="text-secondary dark:text-white/90 space-y-4">
                <p>If you can't access your current email or phone number, please contact our admin team for assistance.
                </p>
                <div class="bg-blue-50 dark:bg-white/5 rounded-xl p-4 border border-blue-200 dark:border-white/10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-white/10 rounded-lg grid place-items-center">
                            <i class="fas fa-headset text-blue-600 dark:text-white"></i>
                        </div>
                        <h4 class="font-semibold text-secondary dark:text-white">Contact Information</h4>
                    </div>
                    <div class="space-y-2 text-sm text-secondary dark:text-white/80">
                        <p><strong>Email:</strong> admin@zzimbaonline.com</p>
                        <p><strong>Phone:</strong> +256 392 003-406</p>
                    </div>
                </div>
                <p class="text-sm text-gray-700 dark:text-white/80">Please provide your username and explain your
                    situation when contacting support.</p>
            </div>
            <div class="mt-6">
                <button onclick="hideModal('contactAdminModal')"
                    class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium"><i
                        class="fas fa-check mr-2"></i>Close</button>
            </div>
        </div>
    </div>
</div>

<div id="successNotification"
    class="fixed top-4 right-4 bg-green-100 dark:bg-green-900/20 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 rounded-xl shadow-lg hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i><span id="successMessage"></span>
    </div>
</div>

<div id="errorNotification"
    class="fixed top-4 right-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 rounded-xl shadow-lg hidden z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i><span id="errorMessage"></span>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>

<script>
    const PROFILE_REQUIRED_FIELDS = ['first_name', 'email', 'phone'];
    let phoneInput, phoneInputDirect;
    let currentUserData = {};
    let currentTab = 'overview';
    let missingFields = [];
    let guidedMode = false;

    $(document).ready(function () {
        phoneInput = window.intlTelInput(document.querySelector('#newPhone'), {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            onlyCountries: ["ug"],
            separateDialCode: true,
            autoPlaceholder: "polite",
            initialCountry: "ug"
        });

        phoneInputDirect = window.intlTelInput(document.querySelector('#newPhoneDirect'), {
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js",
            onlyCountries: ["ug"],
            separateDialCode: true,
            autoPlaceholder: "polite",
            initialCountry: "ug"
        });

        $('.iti').addClass('w-full');

        setupEventListeners();
        setupOtpInputs('otp-existing-email', 'existingEmailOTP');
        setupOtpInputs('otp-new-email', 'newEmailOTP');
        setupOtpInputs('otp-existing-phone', 'existingPhoneOTP');
        setupOtpInputs('otp-new-phone', 'newPhoneOTP');

        switchTab('overview');
        fetchUserDetails();
        observeThemeForAvatar();
    });

    function setupEventListeners() {
        const mobileToggle = document.getElementById('mobile-tab-toggle');
        if (mobileToggle) mobileToggle.addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchTab(tab);
                toggleMobileTabDropdown();
            });
        });

        $('#editNamesForm').submit(e => {
            e.preventDefault();
            updateNames();
        });

        $('#changePasswordForm').submit(e => {
            e.preventDefault();
            changePassword();
        });

        $('#deleteAccountConfirmBtn').click(() => doDeleteAccount());

        $('#newPassword').on('input', function () {
            checkPasswordStrength($(this).val());
        });

        $('.toggle-password').click(function () {
            const target = $(this).data('target');
            const input = $('#' + target);
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        setupEmailFlow();
        setupPhoneFlow();

        $('#start-complete-profile-btn').on('click', function () {
            guidedMode = true;
            hideModal('incompleteProfileModal');
            openNextIncompleteItem();
        });

        document.addEventListener('click', function (event) {
            const mobileDropdown = document.getElementById('mobile-tab-dropdown');
            const mobileToggle = document.getElementById('mobile-tab-toggle');
            if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
                mobileDropdown.classList.add('hidden');
                const chev = document.getElementById('mobile-tab-chevron');
                if (chev) chev.classList.remove('rotate-180');
            }
        });
    }

    function setupEmailFlow() {
        $('#verifyExistingEmailBtn').click(() => sendExistingEmailOTP());
        $('#verifyExistingEmailOTPBtn').click(() => verifyExistingEmailOTP());
        $('#sendNewEmailOTPBtn').click(() => sendNewEmailOTP());
        $('#verifyNewEmailOTPBtn').click(() => verifyNewEmailOTP());
        $('#sendDirectEmailOTPBtn').click(() => sendDirectEmailOTP());
    }

    function setupPhoneFlow() {
        $('#verifyExistingPhoneBtn').click(() => sendExistingPhoneOTP());
        $('#verifyExistingPhoneOTPBtn').click(() => verifyExistingPhoneOTP());
        $('#sendNewPhoneOTPBtn').click(() => sendNewPhoneOTP());
        $('#verifyNewPhoneOTPBtn').click(() => verifyNewPhoneOTP());
        $('#sendDirectPhoneOTPBtn').click(() => sendDirectPhoneOTP());
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-b-primary', 'text-primary');
            btn.classList.add('border-b-transparent', 'text-gray-500', 'dark:text-white/70');
        });

        const activeTab = document.getElementById(`${tabName}-tab`);
        if (activeTab) {
            activeTab.classList.remove('border-b-transparent', 'text-gray-500', 'dark:text-white/70');
            activeTab.classList.add('border-b-primary', 'text-primary');
        }

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        const activeContent = document.getElementById(`${tabName}-content`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        currentTab = tabName;

        const tabLabels = {
            'overview': { label: 'Profile Overview', icon: 'fas fa-user' },
            'security': { label: 'Security', icon: 'fas fa-shield-alt' }
        };
        const tabInfo = tabLabels[tabName] || tabLabels['overview'];
        updateMobileTabLabel(tabInfo.label, tabInfo.icon);
    }

    function updateMobileTabLabel(label, icon) {
        const labelElement = document.getElementById('mobile-tab-label');
        const toggleButton = document.getElementById('mobile-tab-toggle');
        if (labelElement && toggleButton) {
            labelElement.textContent = label;
            const iconElement = toggleButton.querySelector('i');
            if (iconElement) {
                iconElement.className = `${icon} text-primary`;
            }
        }
    }

    function toggleMobileTabDropdown() {
        const dropdown = document.getElementById('mobile-tab-dropdown');
        const chevron = document.getElementById('mobile-tab-chevron');
        dropdown.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }

    function generateUserInitials(firstName, lastName, username) {
        let initials = '';
        if (firstName) initials += firstName[0].toUpperCase();
        if (lastName) initials += lastName[0].toUpperCase();
        if (!initials && username) initials = username[0].toUpperCase();
        if (!initials) initials = 'U';
        return initials;
    }

    function getDicebearUrl(seed) {
        const isDark = document.documentElement.classList.contains('dark');
        const bg = isDark ? '0F172A' : '3B82F6';
        return `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(seed)}&size=128&radius=50&backgroundColor=${bg}&fontWeight=700`;
    }

    function updateProfilePicture(firstName, lastName, username) {
        const initials = generateUserInitials(firstName, lastName, username);
        const seed = `${firstName || ''} ${lastName || ''}`.trim() || (username || initials);
        const profilePic = document.getElementById('user-profile-pic');
        if (profilePic) {
            profilePic.src = getDicebearUrl(seed);
            profilePic.alt = `${firstName || username || 'User'}'s Profile Picture`;
        }
    }

    function observeThemeForAvatar() {
        const observer = new MutationObserver(() => {
            updateProfilePicture(currentUserData.first_name, currentUserData.last_name, currentUserData.username);
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function showIncompleteModal(missing) {
        const modal = $('#incompleteProfileModal');
        const list = $('#incomplete-list');
        const fieldNames = { first_name: 'First Name', email: 'Email Address', phone: 'Phone Number' };
        list.html('');
        missing.forEach(f => {
            const label = fieldNames[f] || f;
            list.append(`<li class="flex items-center gap-2"><i class="fas fa-circle-exclamation text-yellow-600"></i><span>${label} is required</span></li>`);
        });
        modal.removeClass('hidden').addClass('flex');
    }

    function hideIncompleteModal() {
        $('#incompleteProfileModal').addClass('hidden').removeClass('flex');
    }

    function updateRequiredFieldIndicators(missing) {
        $('#names-required-icon, #email-required-icon, #phone-required-icon').addClass('hidden');
        $('#names-overview-card, #email-overview-card, #phone-overview-card').removeClass('border-red-200 bg-red-50 dark:bg-red-900/10');
        if (missing.length > 0) {
            missing.forEach(field => {
                if (field === 'first_name') {
                    $('#names-required-icon').removeClass('hidden');
                    $('#names-overview-card').addClass('border-red-200 bg-red-50 dark:bg-red-900/10');
                } else if (field === 'email') {
                    $('#email-required-icon').removeClass('hidden');
                    $('#email-overview-card').addClass('border-red-200 bg-red-50 dark:bg-red-900/10');
                } else if (field === 'phone') {
                    $('#phone-required-icon').removeClass('hidden');
                    $('#phone-overview-card').addClass('border-red-200 bg-red-50 dark:bg-red-900/10');
                }
            });
        }
    }

    function openEditNamesModal() {
        $('#names-form-error').addClass('hidden').text('');
        $('#firstName').val(currentUserData.first_name || '');
        $('#lastName').val(currentUserData.last_name || '');
        showModal('editNamesModal');
    }

    function openNextIncompleteItem() {
        const order = ['first_name', 'phone', 'email'];
        const next = order.find(f => missingFields.includes(f));
        if (!next) {
            guidedMode = false;
            hideIncompleteModal();
            showSuccessNotification('Profile complete. Thanks!');
            return;
        }
        if (next === 'first_name') {
            openEditNamesModal();
        } else if (next === 'phone') {
            editPhone();
        } else if (next === 'email') {
            editEmail();
        }
    }

    function evaluateProfileCompleteness(user) {
        missingFields = PROFILE_REQUIRED_FIELDS.filter(f => !(user[f] || '').trim());
        updateRequiredFieldIndicators(missingFields);
        if (missingFields.length === 0) {
            hideIncompleteModal();
            if (typeof showReturnModal === 'function') {
                showReturnModal();
            }
        } else {
            showIncompleteModal(missingFields);
        }
    }

    function fetchUserDetails() {
        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=getUserDetails',
            dataType: 'json',
            success(resp) {
                if (!resp.success) {
                    showErrorNotification('Failed to load user details.');
                    return;
                }
                const u = resp.data;
                currentUserData = u;
                updateProfileDisplay(u);
                evaluateProfileCompleteness(u);
            },
            error() {
                showErrorNotification('Failed to load user details.');
            }
        });
    }

    function applyTruncationWithTitle(selector, value, fallback = 'Not set') {
        const v = value || fallback;
        $(selector).text(v).attr('title', v);
    }

    function updateProfileDisplay(u) {
        $('#firstName').val(u.first_name || '');
        $('#lastName').val(u.last_name || '');

        const fullName = `${u.first_name || ''} ${u.last_name || ''}`.trim() || 'User';
        $('#user-fullname').text(fullName).attr('title', fullName);
        $('#user-username').text('@' + (u.username || '')).attr('title', '@' + (u.username || ''));

        updateProfilePicture(u.first_name, u.last_name, u.username);

        const jd = new Date(u.created_at);
        $('#user-joined').text('Member since ' + jd.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));

        if (u.last_login) {
            $('#user-lastlogin').text('Last login: ' + getTimeAgo(new Date(u.last_login)));
        } else {
            $('#user-lastlogin').text('First login');
        }

        applyTruncationWithTitle('#overview-names', fullName === 'User' ? '' : fullName, 'Not set');
        applyTruncationWithTitle('#overview-email', u.email, 'Not set');
        applyTruncationWithTitle('#overview-phone', u.phone, 'Not set');

        applyTruncationWithTitle('#current-email-modal', u.email, 'Not set');
        applyTruncationWithTitle('#current-phone-modal', u.phone, 'Not set');

        applyTruncationWithTitle('#current-email-display', u.email, 'Not set');
        applyTruncationWithTitle('#current-phone-display', u.phone, 'Not set');

        applyTruncationWithTitle('#email-value-display', u.email, 'Not set');
        applyTruncationWithTitle('#phone-value-display', u.phone, 'Not set');

        const statusMap = {
            active: { text: 'Active', class: 'text-green-600 dark:text-green-400' },
            inactive: { text: 'Inactive', class: 'text-yellow-600 dark:text-yellow-300' },
            suspended: { text: 'Suspended', class: 'text-red-600 dark:text-red-400' },
            deleted: { text: 'Deleted', class: 'text-gray-600 dark:text-white/70' }
        };
        const status = statusMap[u.status] || { text: 'Unknown', class: 'text-gray-600 dark:text-white/70' };
        $('#overview-status').html(`<span class="${status.class}">${status.text}</span>`);

        $('#profile-status-display').text(status.text);
        $('#email-status-display').text(u.email ? 'Verified' : 'Not Set');
        $('#phone-status-display').text(u.phone ? 'Verified' : 'Not Set');
        $('#member-since-display').text(jd.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
        $('#last-login-display').text(u.last_login ? getTimeAgo(new Date(u.last_login)) : 'First login');
    }

    function editEmail() {
        resetEmailModal();
        if (currentUserData.email) {
            $('#verifyExistingEmailSection').show();
            $('#noExistingEmailSection').hide();
        } else {
            $('#verifyExistingEmailSection').hide();
            $('#noExistingEmailSection').show();
        }
        showModal('editEmailModal');
    }

    function editPhone() {
        resetPhoneModal();
        if (currentUserData.phone) {
            $('#verifyExistingPhoneSection').show();
            $('#noExistingPhoneSection').hide();
        } else {
            $('#verifyExistingPhoneSection').hide();
            $('#noExistingPhoneSection').show();
        }
        showModal('editPhoneModal');
    }

    function resetEmailModal() {
        $('#emailStep1').show();
        $('#emailStep2, #emailStep3, #emailStep4').hide();
        $('#email-form-error').addClass('hidden').text('');
        $('#existingEmailOTP, #newEmail, #newEmailOTP, #newEmailDirect').val('');
        $('#otp-existing-email, #otp-new-email').find('input').val('');
    }

    function resetPhoneModal() {
        $('#phoneStep1').show();
        $('#phoneStep2, #phoneStep3, #phoneStep4').hide();
        $('#phone-form-error').addClass('hidden').text('');
        $('#phone-error, #phone-error-direct').addClass('hidden');
        $('#existingPhoneOTP, #newPhoneOTP').val('');
        $('#otp-existing-phone, #otp-new-phone').find('input').val('');
        phoneInput.setNumber('');
        phoneInputDirect.setNumber('');
    }

    function updateNames() {
        $('#names-form-error').addClass('hidden').text('');
        const fd = { first_name: $('#firstName').val().trim(), last_name: $('#lastName').val().trim() };
        if (!fd.first_name) {
            $('#names-form-error').removeClass('hidden').text('First name is required');
            return;
        }
        const btn = $('#editNamesForm button[type="submit"]'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=updateNames',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(fd),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editNamesModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                    if (guidedMode) setTimeout(openNextIncompleteItem, 600);
                } else {
                    $('#names-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#names-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendExistingEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const btn = $('#verifyExistingEmailBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep1').hide();
                    $('#emailStep2').show();
                    focusFirstOtp('otp-existing-email');
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyExistingEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const otp = $('#existingEmailOTP').val().trim();
        if (otp.length !== 6) {
            $('#email-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }
        const btn = $('#verifyExistingEmailOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Verifying…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep2').hide();
                    $('#emailStep3').show();
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendNewEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const newEmail = $('#newEmail').val().trim();
        if (!newEmail) {
            $('#email-form-error').removeClass('hidden').text('New email is required');
            return;
        }
        const btn = $('#sendNewEmailOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_email: newEmail }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#emailStep3').hide();
                    $('#emailStep4').show();
                    focusFirstOtp('otp-new-email');
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyNewEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const otp = $('#newEmailOTP').val().trim();
        if (otp.length !== 6) {
            $('#email-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }
        const btn = $('#verifyNewEmailOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editEmailModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                    if (guidedMode) setTimeout(openNextIncompleteItem, 600);
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendDirectEmailOTP() {
        $('#email-form-error').addClass('hidden').text('');
        const newEmail = $('#newEmailDirect').val().trim();
        if (!newEmail) {
            $('#email-form-error').removeClass('hidden').text('Email is required');
            return;
        }
        const btn = $('#sendDirectEmailOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_email: newEmail }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#newEmailOTP').val('');
                    $('#emailStep1').hide();
                    $('#emailStep4').show();
                    focusFirstOtp('otp-new-email');
                } else {
                    $('#email-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#email-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendExistingPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const btn = $('#verifyExistingPhoneBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({}),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep1').hide();
                    $('#phoneStep2').show();
                    focusFirstOtp('otp-existing-phone');
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyExistingPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const otp = $('#existingPhoneOTP').val().trim();
        if (otp.length !== 6) {
            $('#phone-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }
        const btn = $('#verifyExistingPhoneOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Verifying…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep2').hide();
                    $('#phoneStep3').show();
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function validatePhoneNumber(inputId, errorId) {
        const errorEl = $(errorId).addClass('hidden');
        const phoneInputEl = inputId === '#newPhone' ? phoneInput : phoneInputDirect;
        const num = phoneInputEl.getNumber();
        if (!num) return false;
        if (!phoneInputEl.isValidNumber()) {
            errorEl.text('Invalid phone number').removeClass('hidden');
            return false;
        }
        if (!num.startsWith('+256')) {
            errorEl.text('Only Uganda (+256) phone numbers are allowed').removeClass('hidden');
            return false;
        }
        return true;
    }

    function sendNewPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        if (!validatePhoneNumber('#newPhone', '#phone-error')) return;

        const newPhone = phoneInput.getNumber();
        const btn = $('#sendNewPhoneOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_phone: newPhone }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#phoneStep3').hide();
                    $('#phoneStep4').show();
                    focusFirstOtp('otp-new-phone');
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function verifyNewPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        const otp = $('#newPhoneOTP').val().trim();
        if (otp.length !== 6) {
            $('#phone-form-error').removeClass('hidden').text('Please enter a valid 6-digit code');
            return;
        }
        const btn = $('#verifyNewPhoneOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    hideModal('editPhoneModal');
                    showSuccessNotification(r.message);
                    fetchUserDetails();
                    if (guidedMode) setTimeout(openNextIncompleteItem, 600);
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function sendDirectPhoneOTP() {
        $('#phone-form-error').addClass('hidden').text('');
        if (!validatePhoneNumber('#newPhoneDirect', '#phone-error-direct')) return;

        const newPhone = phoneInputDirect.getNumber();
        const btn = $('#sendDirectPhoneOTPBtn'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ new_phone: newPhone }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#newPhoneOTP').val('');
                    $('#phoneStep1').hide();
                    $('#phoneStep4').show();
                    focusFirstOtp('otp-new-phone');
                } else {
                    $('#phone-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#phone-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function changePassword() {
        $('#password-form-error').addClass('hidden').text('');
        const cur = $('#currentPassword').val(), nw = $('#newPassword').val(), cf = $('#confirmPassword').val();
        if (!cur || !nw || !cf) {
            $('#password-form-error').removeClass('hidden').text('All fields are required');
            return;
        }
        if (nw !== cf) {
            $('#password-form-error').removeClass('hidden').text('Passwords do not match');
            return;
        }
        if (!(nw.length >= 8 && /[A-Z]/.test(nw) && /[a-z]/.test(nw) && /\d/.test(nw) && /[^A-Za-z0-9]/.test(nw))) {
            $('#password-form-error').removeClass('hidden').text('Password does not meet requirements');
            return;
        }
        const btn = $('#changePasswordForm button[type="submit"]'), orig = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating…').prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=changePassword',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ current_password: cur, new_password: nw }),
            dataType: 'json',
            success(r) {
                btn.html(orig).prop('disabled', false);
                if (r.success) {
                    $('#changePasswordForm')[0].reset();
                    hideModal('changePasswordModal');
                    showSuccessNotification(r.message);
                } else {
                    $('#password-form-error').removeClass('hidden').text(r.message);
                }
            },
            error(xhr) {
                btn.html(orig).prop('disabled', false);
                let msg = 'Server error. Try again.';
                try { msg = JSON.parse(xhr.responseText).message || msg; } catch { }
                $('#password-form-error').removeClass('hidden').text(msg);
            }
        });
    }

    function doDeleteAccount() {
        const btnConfirm = $('#deleteAccountConfirmBtn'), btnCancel = $('#deleteAccountCancelBtn');
        btnConfirm.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Deleting…');
        btnCancel.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'account/fetch/manageProfile.php?action=deleteAccount',
            method: 'POST',
            dataType: 'json',
            success(r) {
                if (r.success) {
                    hideModal('deleteAccountModal');
                    showSuccessNotification(r.message);
                    setTimeout(() => window.location.href = BASE_URL, 1500);
                } else {
                    showErrorNotification(r.message);
                    btnConfirm.prop('disabled', false).html('<i class="fas fa-trash mr-2"></i>Delete Account');
                    btnCancel.prop('disabled', false);
                }
            },
            error() {
                showErrorNotification('Failed to delete account. Please try again.');
                btnConfirm.prop('disabled', false).html('<i class="fas fa-trash mr-2"></i>Delete Account');
                btnCancel.prop('disabled', false);
            }
        });
    }

    function showModal(id) { $('#' + id).removeClass('hidden').addClass('flex'); }
    function hideModal(id) { $('#' + id).addClass('hidden').removeClass('flex'); }

    function showContactAdminModal() { showModal('contactAdminModal'); }

    function showSuccessNotification(m) {
        const n = $('#successNotification'), t = $('#successMessage');
        t.text(m); n.removeClass('hidden');
        setTimeout(() => n.addClass('hidden'), 3000);
    }

    function showErrorNotification(m) {
        const n = $('#errorNotification'), t = $('#errorMessage');
        t.text(m); n.removeClass('hidden');
        setTimeout(() => n.addClass('hidden'), 3000);
    }

    function setupOtpInputs(containerId, hiddenInputId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        const inputs = [];
        for (let i = 0; i < 6; i++) {
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.inputMode = 'numeric';
            inp.maxLength = 1;
            inp.className = 'w-12 h-12 text-center text-xl border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary';
            container.appendChild(inp);
            inputs.push(inp);
        }
        const hidden = document.getElementById(hiddenInputId);
        function updateHidden() {
            hidden.value = inputs.map(x => x.value.replace(/\D/g, '') || '').join('');
        }
        inputs.forEach((inp, idx) => {
            inp.addEventListener('input', e => {
                e.target.value = e.target.value.replace(/\D/g, '');
                if (e.target.value && idx < inputs.length - 1) inputs[idx + 1].focus();
                updateHidden();
            });
            inp.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !inp.value && idx > 0) {
                    inputs[idx - 1].focus();
                }
            });
            inp.addEventListener('paste', e => {
                e.preventDefault();
                const data = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].value = data[i] || '';
                }
                updateHidden();
                const nextIndex = Math.min(data.length, inputs.length - 1);
                inputs[nextIndex].focus();
            });
        });
    }

    function focusFirstOtp(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const first = container.querySelector('input');
        if (first) first.focus();
    }

    function checkPasswordStrength(p) {
        let s = 0;
        const checks = [
            { id: 'length-check', test: p.length >= 8 },
            { id: 'uppercase-check', test: /[A-Z]/.test(p) },
            { id: 'lowercase-check', test: /[a-z]/.test(p) },
            { id: 'number-check', test: /\d/.test(p) },
            { id: 'special-check', test: /[^A-Za-z0-9]/.test(p) }
        ];

        checks.forEach(check => {
            const el = $('#' + check.id);
            const icon = el.find('i');
            if (check.test) {
                s += 20;
                el.removeClass('text-gray-400 dark:text-white/60').addClass('text-green-600 dark:text-green-400');
                icon.removeClass('fa-times').addClass('fa-check');
            } else {
                el.removeClass('text-green-600 dark:text-green-400').addClass('text-gray-400 dark:text-white/60');
                icon.removeClass('fa-check').addClass('fa-times');
            }
        });

        const b = $('#passwordStrength');
        b.css('width', s + '%');
        b.removeClass('bg-red-500 bg-yellow-500 bg-green-500');
        if (s < 40) b.addClass('bg-red-500');
        else if (s < 80) b.addClass('bg-yellow-500');
        else b.addClass('bg-green-500');
    }

    function getTimeAgo(d) {
        const s = Math.floor((new Date() - d) / 1000);
        let i = Math.floor(s / 31536000); if (i > 1) return i + ' years ago'; if (i === 1) return '1 year ago';
        i = Math.floor(s / 2592000); if (i > 1) return i + ' months ago'; if (i === 1) return '1 month ago';
        i = Math.floor(s / 86400); if (i > 1) return i + ' days ago'; if (i === 1) return '1 day ago';
        i = Math.floor(s / 3600); if (i > 1) return i + ' hours ago'; if (i === 1) return '1 hour ago';
        i = Math.floor(s / 60); if (i > 1) return i + ' minutes ago'; if (i === 1) return '1 minute ago';
        if (s < 10) return 'just now'; return s + ' seconds ago';
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>