<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'My Profile';
$activeNav = 'profile';
ob_start();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css">
<style>
    [x-cloak] {
        display: none !important
    }
</style>

<div class="min-h-screen text-gray-900 dark:text-white" id="app-container" x-data="profileApp()" x-init="init()">
    <div class="bg-white dark:bg-secondary border-b border-gray-100 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-6">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Profile
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            x-text="statusText">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap">Account Status
                        </p>
                    </div>
                    <i data-lucide="user-check"
                        class="absolute bottom-3 right-3 w-7 h-7 text-blue-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Email
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap"
                            x-text="emailStatus">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            :title="user.email || 'Not Set'" x-text="user.email || 'Not Set'">Not Set</p>
                    </div>
                    <i data-lucide="mail"
                        class="absolute bottom-3 right-3 w-7 h-7 text-green-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Phone
                            Status</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap"
                            x-text="phoneStatus">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            :title="user.phone || 'Not Set'" x-text="user.phone || 'Not Set'">Not Set</p>
                    </div>
                    <i data-lucide="phone"
                        class="absolute bottom-3 right-3 w-7 h-7 text-purple-600/30 dark:text-white/20"></i>
                </div>

                <div
                    class="relative bg-white dark:bg-secondary rounded-xl p-4 border border-gray-100 dark:border-white/10">
                    <div class="space-y-1 pr-6">
                        <p class="text-xs font-medium text-gray-500 dark:text-white/70 uppercase tracking-wide">Member
                            Since</p>
                        <p class="text-lg font-bold text-secondary dark:text-white whitespace-nowrap truncate max-w-[180px] sm:max-w-[220px] lg:max-w-[160px]"
                            x-text="memberSince">Loading...</p>
                        <p class="text-sm font-medium text-gray-600 dark:text-white/70 whitespace-nowrap"
                            x-text="lastLogin">Last Login</p>
                    </div>
                    <i data-lucide="calendar"
                        class="absolute bottom-3 right-3 w-7 h-7 text-orange-600/30 dark:text-white/20"></i>
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
                        <button id="overview-tab" @click="switchTab('overview')" :class="tabBtnClass('overview')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200">
                            <i data-lucide="user" class="w-4 h-4 mr-2 inline-block"></i>Profile Overview
                        </button>
                        <button id="security-tab" @click="switchTab('security')" :class="tabBtnClass('security')"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200">
                            <i data-lucide="shield" class="w-4 h-4 mr-2 inline-block"></i>Security
                        </button>
                    </nav>
                </div>

                <div class="md:hidden px-4 sm:px-6 py-3">
                    <div class="relative" x-data="{open:false}">
                        <button id="mobile-tab-toggle" @click="open=!open"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                            <div class="flex items-center gap-2">
                                <i :data-lucide="currentTab==='overview' ? 'user' : 'shield'"
                                    class="w-4 h-4 text-primary"></i>
                                <span id="mobile-tab-label" class="font-medium text-secondary dark:text-white"
                                    x-text="currentTab==='overview' ? 'Profile Overview' : 'Security'">Profile
                                    Overview</span>
                            </div>
                            <i data-lucide="chevron-down"
                                class="w-4 h-4 text-gray-400 dark:text-white/50 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div id="mobile-tab-dropdown" x-show="open" @click.outside="open=false" x-transition
                            class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-secondary border border-gray-200 dark:border-white/10 rounded-xl shadow-lg z-50">
                            <div class="py-2">
                                <button
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition-colors"
                                    @click="switchTab('overview'); open=false">
                                    <i data-lucide="user" class="w-4 h-4 text-blue-600"></i>
                                    <span class="text-secondary dark:text-white">Profile Overview</span>
                                </button>
                                <button
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/10 transition-colors"
                                    @click="switchTab('security'); open=false">
                                    <i data-lucide="shield" class="w-4 h-4 text-purple-600"></i>
                                    <span class="text-secondary dark:text-white">Security</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content">
            <div id="overview-content" x-show="currentTab==='overview'">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                            <div class="text-center">
                                <div
                                    class="w-32 h-32 rounded-full overflow-hidden border-4 border-white dark:border-white/10 shadow-md mb-4 mx-auto">
                                    <img x-ref="profilePic"
                                        src="https://api.dicebear.com/7.x/initials/svg?seed=U&size=128&radius=50"
                                        alt="Profile Picture" class="w-full h-full object-cover">
                                </div>
                                <h1 class="text-2xl font-bold text-secondary dark:text-white mb-1 truncate max-w-[240px] mx-auto"
                                    :title="fullName" x-text="fullName">Loading…</h1>
                                <p class="text-gray-600 dark:text-white/70 mb-4 truncate max-w-[240px] mx-auto"
                                    :title="'@'+(user.username||'')" x-text="'@'+(user.username||'')">@loading</p>
                                <div class="space-y-2 text-sm text-gray-600 dark:text-white/70">
                                    <p x-text="'Member since ' + memberSince">Member since …</p>
                                    <p x-text="lastLogin">Last login: …</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div
                            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 bg-user-primary/10 rounded-lg grid place-items-center">
                                    <i data-lucide="info" class="w-4 h-4 text-user-primary"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-secondary dark:text-white">Account Information
                                </h3>
                            </div>

                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                    <div class="relative rounded-xl p-4"
                                        :class="isMissing('first_name') ? 'bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700/50' : 'bg-gray-50 dark:bg-white/5'">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            @click="openEditNamesModal()">
                                            <i data-lucide="edit-2"
                                                class="w-4 h-4 text-gray-600 dark:text-white/80"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-blue-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i data-lucide="user" class="w-4 h-4 text-blue-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Full Name</h4>
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"
                                                x-show="isMissing('first_name')"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate"
                                            :title="fullName || 'Not set'" x-text="fullName || 'Not set'">Loading...</p>
                                    </div>

                                    <div class="relative rounded-xl p-4"
                                        :class="isMissing('email') ? 'bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700/50' : 'bg-gray-50 dark:bg-white/5'">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            @click="editEmail()">
                                            <i data-lucide="edit-2"
                                                class="w-4 h-4 text-gray-600 dark:text-white/80"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-green-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i data-lucide="mail"
                                                    class="w-4 h-4 text-green-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Email Address</h4>
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"
                                                x-show="isMissing('email')"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate"
                                            :title="user.email || 'Not set'" x-text="user.email || 'Not set'">Loading...
                                        </p>
                                    </div>

                                    <div class="relative rounded-xl p-4"
                                        :class="isMissing('phone') ? 'bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700/50' : 'bg-gray-50 dark:bg-white/5'">
                                        <button
                                            class="absolute top-3 right-3 w-9 h-9 rounded-lg bg-white/80 dark:bg-white/10 border border-gray-200 dark:border-white/10 grid place-items-center hover:bg-white dark:hover:bg-white/20 transition"
                                            @click="editPhone()">
                                            <i data-lucide="edit-2"
                                                class="w-4 h-4 text-gray-600 dark:text-white/80"></i>
                                        </button>
                                        <div class="flex items-center gap-3 mb-3 pr-10">
                                            <div
                                                class="w-8 h-8 bg-purple-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i data-lucide="phone"
                                                    class="w-4 h-4 text-purple-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Phone Number</h4>
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"
                                                x-show="isMissing('phone')"></i>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80 truncate"
                                            :title="user.phone || 'Not set'" x-text="user.phone || 'Not set'">Loading...
                                        </p>
                                    </div>

                                    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                                        <div class="flex items-center gap-3 mb-3">
                                            <div
                                                class="w-8 h-8 bg-orange-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                                <i data-lucide="shield"
                                                    class="w-4 h-4 text-orange-600 dark:text-white"></i>
                                            </div>
                                            <h4 class="font-semibold text-secondary dark:text-white">Account Status</h4>
                                        </div>
                                        <p class="text-gray-700 dark:text-white/80">
                                            <span :class="statusClass()" x-text="statusText"></span>
                                        </p>
                                    </div>
                                </div>

                                <div class="hidden md:flex flex-wrap gap-3 pt-2">
                                    <button @click="switchTab('security')"
                                        class="px-6 py-3 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-200 dark:hover:bg-white/20 transition-colors font-medium">
                                        <i data-lucide="shield" class="w-4 h-4 mr-2 inline-block"></i>Security Settings
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="security-content" x-show="currentTab==='security'">
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-orange-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                <i data-lucide="lock" class="w-4 h-4 text-orange-600 dark:text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-secondary dark:text-white">Update Password</h3>
                        </div>

                        <button @click="modals.changePassword=true"
                            class="px-6 py-3 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-all duration-200 font-medium shadow-lg shadow-orange-600/25 hover:shadow-xl hover:shadow-orange-600/30">
                            <i data-lucide="key" class="w-4 h-4 mr-2 inline-block"></i>Update Password
                        </button>
                    </div>

                    <div
                        class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-100 dark:border-white/10 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                <i data-lucide="user-x" class="w-4 h-4 text-red-600 dark:text-white"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-secondary dark:text-white">Account Deletion</h3>
                        </div>

                        <div
                            class="bg-red-50 dark:bg-red-900/20 rounded-xl p-4 mb-4 border border-red-200 dark:border-red-700/50">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 dark:text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-red-900 dark:text-red-200">Danger Zone</h4>
                                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">Once you delete your account,
                                        there is no going back. Please be certain.</p>
                                </div>
                            </div>
                        </div>

                        <button @click="modals.deleteAccount=true"
                            class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200 font-medium shadow-lg shadow-red-600/25 hover:shadow-xl hover:shadow-red-600/30">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2 inline-block"></i>Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="incompleteProfileModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center"
        x-show="modals.incomplete" x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.incomplete=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Complete Your Profile</h3>
                    <button @click="modals.incomplete=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 grid place-items-center">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-700 dark:text-white/80">To access all features and keep your account secure,
                    finish the steps below.</p>
                <ul class="space-y-2 text-sm text-gray-700 dark:text-white/80">
                    <template x-for="f in missingFields" :key="f">
                        <li class="flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-yellow-600"></i>
                            <span x-text="missingLabel(f) + ' is required'"></span>
                        </li>
                    </template>
                </ul>
                <button @click="guidedMode=true; modals.incomplete=false; openNextIncompleteItem()"
                    class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                    <i data-lucide="play" class="w-4 h-4 mr-2 inline-block"></i>Complete Profile Now
                </button>
            </div>
        </div>
    </div>

    <div id="editNamesModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center" x-show="modals.editNames"
        x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.editNames=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Names</h3>
                    <button @click="modals.editNames=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 grid place-items-center">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form @submit.prevent="updateNames" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="firstName"
                                class="block text-sm font-semibold text-secondary dark:text-white mb-2">First Name
                                *</label>
                            <input type="text" id="firstName" x-model.trim="forms.names.first_name"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                        </div>
                        <div>
                            <label for="lastName"
                                class="block text-sm font-semibold text-secondary dark:text-white mb-2">Last
                                Name</label>
                            <input type="text" id="lastName" x-model.trim="forms.names.last_name"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="text-red-500 text-sm" x-show="errors.names" x-text="errors.names"></div>
                    <button type="submit"
                        class="w-full px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium flex items-center justify-center">
                        <i data-lucide="save" class="w-4 h-4 mr-2" x-show="!loading.names"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.names"></i>
                        <span x-text="loading.names ? 'Updating…' : 'Update Names'">Update Names</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="editEmailModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center" x-show="modals.editEmail"
        x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.editEmail=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Email Address</h3>
                    <button @click="modals.editEmail=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div x-show="forms.email.step===1" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                        <p class="text-sm text-gray-700 dark:text-white/80 mb-1">Current Email:</p>
                        <p class="font-medium text-secondary dark:text-white truncate" :title="user.email || 'Not set'"
                            x-text="user.email || 'Not set'">Loading...</p>
                    </div>
                    <div x-show="user.email">
                        <p class="text-sm text-gray-600 dark:text-white/70 mb-3">To change your email, first verify your
                            current email address.</p>
                        <button type="button" @click="sendExistingEmailOTP"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium flex items-center justify-center">
                            <i data-lucide="shield" class="w-4 h-4 mr-2" x-show="!loading.email"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.email"></i>
                            <span x-text="loading.email ? 'Sending…' : 'Verify Current Email'">Verify Current
                                Email</span>
                        </button>
                    </div>
                    <div x-show="!user.email">
                        <p class="text-sm text-gray-600 dark:text-white/70 mb-3">You don't have an email set. Enter your
                            new email address.</p>
                        <div>
                            <label for="newEmailDirect"
                                class="block text-sm font-semibold text-secondary dark:text-white mb-2">Email
                                Address</label>
                            <input type="email" id="newEmailDirect" x-model.trim="forms.email.newEmailDirect"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                        </div>
                        <button type="button" @click="sendDirectEmailOTP"
                            class="w-full mt-3 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium flex items-center justify-center">
                            <i data-lucide="send" class="w-4 h-4 mr-2" x-show="!loading.email"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.email"></i>
                            <span x-text="loading.email ? 'Sending…' : 'Send Verification Code'">Send Verification
                                Code</span>
                        </button>
                    </div>
                </div>

                <div x-show="forms.email.step===2" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your current
                        email.</p>
                    <div class="flex gap-2 justify-center">
                        <template x-for="(d,i) in forms.email.existingOtp" :key="'eo'+i">
                            <input type="text" inputmode="numeric" maxlength="1"
                                class="w-12 h-12 text-center text-xl border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                x-model="forms.email.existingOtp[i]" @input="otpInput($event, 'email','existingOtp')">
                        </template>
                    </div>
                    <button type="button" @click="verifyExistingEmailOTP"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4 mr-2" x-show="!loading.email"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.email"></i>
                        <span x-text="loading.email ? 'Verifying…' : 'Verify Code'">Verify Code</span>
                    </button>
                </div>

                <div x-show="forms.email.step===3" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter your new email address.</p>
                    <div>
                        <label for="newEmail"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">New Email
                            Address</label>
                        <input type="email" id="newEmail" x-model.trim="forms.email.newEmail"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                    </div>
                    <button type="button" @click="sendNewEmailOTP"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2" x-show="!loading.email"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.email"></i>
                        <span x-text="loading.email ? 'Sending…' : 'Send Verification Code'">Send Verification
                            Code</span>
                    </button>
                </div>

                <div x-show="forms.email.step===4" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your new
                        email.</p>
                    <div class="flex gap-2 justify-center">
                        <template x-for="(d,i) in forms.email.newOtp" :key="'ne'+i">
                            <input type="text" inputmode="numeric" maxlength="1"
                                class="w-12 h-12 text-center text-xl border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                x-model="forms.email.newOtp[i]" @input="otpInput($event, 'email','newOtp')">
                        </template>
                    </div>
                    <button type="button" @click="verifyNewEmailOTP"
                        class="w-full px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-2" x-show="!loading.email"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.email"></i>
                        <span x-text="loading.email ? 'Updating…' : 'Complete Update'">Complete Update</span>
                    </button>
                </div>

                <div class="text-red-500 text-sm mt-3" x-show="errors.email" x-text="errors.email"></div>
            </div>
        </div>
    </div>

    <div id="editPhoneModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center" x-show="modals.editPhone"
        x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.editPhone=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Phone Number</h3>
                    <button @click="modals.editPhone=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div x-show="forms.phone.step===1" class="space-y-4">
                    <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4">
                        <p class="text-sm text-gray-700 dark:text-white/80 mb-1">Current Phone:</p>
                        <p class="font-medium text-secondary dark:text-white truncate" :title="user.phone || 'Not set'"
                            x-text="user.phone || 'Not set'">Loading...</p>
                    </div>
                    <div x-show="user.phone">
                        <p class="text-sm text-gray-600 dark:text-white/70 mb-3">To change your phone, first verify your
                            current phone number.</p>
                        <button type="button" @click="sendExistingPhoneOTP"
                            class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium flex items-center justify-center">
                            <i data-lucide="shield" class="w-4 h-4 mr-2" x-show="!loading.phone"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.phone"></i>
                            <span x-text="loading.phone ? 'Sending…' : 'Verify Current Phone'">Verify Current
                                Phone</span>
                        </button>
                    </div>
                    <div x-show="!user.phone">
                        <p class="text-sm text-gray-600 dark:text-white/70 mb-3">You don't have a phone set. Enter your
                            new phone number.</p>
                        <div>
                            <label for="newPhoneDirect"
                                class="block text-sm font-semibold text-secondary dark:text-white mb-2">Phone
                                Number</label>
                            <input type="tel" id="newPhoneDirect" x-ref="newPhoneDirect"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                            <div class="text-red-500 text-xs mt-1" x-show="errors.phoneDirect"
                                x-text="errors.phoneDirect"></div>
                        </div>
                        <button type="button" @click="sendDirectPhoneOTP"
                            class="w-full mt-3 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium flex items-center justify-center">
                            <i data-lucide="send" class="w-4 h-4 mr-2" x-show="!loading.phone"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.phone"></i>
                            <span x-text="loading.phone ? 'Sending…' : 'Send Verification Code'">Send Verification
                                Code</span>
                        </button>
                    </div>
                </div>

                <div x-show="forms.phone.step===2" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your current
                        phone.</p>
                    <div class="flex gap-2 justify-center">
                        <template x-for="(d,i) in forms.phone.existingOtp" :key="'op'+i">
                            <input type="text" inputmode="numeric" maxlength="1"
                                class="w-12 h-12 text-center text-xl border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                x-model="forms.phone.existingOtp[i]" @input="otpInput($event, 'phone','existingOtp')">
                        </template>
                    </div>
                    <button type="button" @click="verifyExistingPhoneOTP"
                        class="w-full px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="check" class="w-4 h-4 mr-2" x-show="!loading.phone"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.phone"></i>
                        <span x-text="loading.phone ? 'Verifying…' : 'Verify Code'">Verify Code</span>
                    </button>
                </div>

                <div x-show="forms.phone.step===3" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter your new phone number.</p>
                    <div>
                        <label for="newPhone"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">New Phone
                            Number</label>
                        <input type="tel" id="newPhone" x-ref="newPhone"
                            class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            autocomplete="off">
                        <div class="text-red-500 text-xs mt-1" x-show="errors.phone" x-text="errors.phone"></div>
                    </div>
                    <button type="button" @click="sendNewPhoneOTP"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 mr-2" x-show="!loading.phone"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.phone"></i>
                        <span x-text="loading.phone ? 'Sending…' : 'Send Verification Code'">Send Verification
                            Code</span>
                    </button>
                </div>

                <div x-show="forms.phone.step===4" class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-white/70">Enter the verification code sent to your new
                        phone.</p>
                    <div class="flex gap-2 justify-center">
                        <template x-for="(d,i) in forms.phone.newOtp" :key="'np'+i">
                            <input type="text" inputmode="numeric" maxlength="1"
                                class="w-12 h-12 text-center text-xl border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary"
                                x-model="forms.phone.newOtp[i]" @input="otpInput($event, 'phone','newOtp')">
                        </template>
                    </div>
                    <button type="button" @click="verifyNewPhoneOTP"
                        class="w-full px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-4 h-4 mr-2" x-show="!loading.phone"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.phone"></i>
                        <span x-text="loading.phone ? 'Updating…' : 'Complete Update'">Complete Update</span>
                    </button>
                </div>

                <div class="text-red-500 text-sm mt-3" x-show="errors.phoneForm" x-text="errors.phoneForm"></div>
            </div>
        </div>
    </div>

    <div id="changePasswordModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center"
        x-show="modals.changePassword" x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.changePassword=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Update Password</h3>
                    <button @click="modals.changePassword=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form @submit.prevent="changePassword" class="space-y-4">
                    <div>
                        <label for="currentPassword"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Current
                            Password</label>
                        <div class="relative">
                            <input :type="forms.password.show.current ? 'text' : 'password'" id="currentPassword"
                                x-model="forms.password.current"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                                @click="forms.password.show.current=!forms.password.show.current">
                                <i :data-lucide="forms.password.show.current ? 'eye-off' : 'eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="newPassword"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">New Password</label>
                        <div class="relative">
                            <input :type="forms.password.show.new ? 'text' : 'password'" id="newPassword"
                                x-model="forms.password.new" @input="updatePasswordStrength"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                                @click="forms.password.show.new=!forms.password.show.new">
                                <i :data-lucide="forms.password.show.new ? 'eye-off' : 'eye'"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="text-xs text-gray-600 dark:text-white/70 mb-1">Password strength:</div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-200" :class="strengthBarColor()"
                                    :style="`width: ${forms.password.strength}%`"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="confirmPassword"
                            class="block text-sm font-semibold text-secondary dark:text-white mb-2">Confirm New
                            Password</label>
                        <div class="relative">
                            <input :type="forms.password.show.confirm ? 'text' : 'password'" id="confirmPassword"
                                x-model="forms.password.confirm"
                                class="w-full px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl bg-white dark:bg-secondary text-secondary dark:text-white focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                autocomplete="off">
                            <button type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white/80"
                                @click="forms.password.show.confirm=!forms.password.show.confirm">
                                <i :data-lucide="forms.password.show.confirm ? 'eye-off' : 'eye'"></i>
                            </button>
                        </div>
                    </div>
                    <div
                        class="text-xs text-gray-600 dark:text-white/70 bg-gray-50 dark:bg-white/5 rounded-lg p-3 border border-gray-100 dark:border-white/10">
                        <p class="font-medium mb-2">Password must:</p>
                        <ul class="space-y-1">
                            <li class="flex items-center gap-2"
                                :class="pwReq.okLen ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-white/60'">
                                <i :data-lucide="pwReq.okLen?'check':'x'" class="w-3 h-3"></i>Be at least 8 characters
                                long</li>
                            <li class="flex items-center gap-2"
                                :class="pwReq.okUp ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-white/60'">
                                <i :data-lucide="pwReq.okUp?'check':'x'" class="w-3 h-3"></i>Include at least one
                                uppercase letter</li>
                            <li class="flex items-center gap-2"
                                :class="pwReq.okLo ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-white/60'">
                                <i :data-lucide="pwReq.okLo?'check':'x'" class="w-3 h-3"></i>Include at least one
                                lowercase letter</li>
                            <li class="flex items-center gap-2"
                                :class="pwReq.okNum ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-white/60'">
                                <i :data-lucide="pwReq.okNum?'check':'x'" class="w-3 h-3"></i>Include at least one
                                number</li>
                            <li class="flex items-center gap-2"
                                :class="pwReq.okSp ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-white/60'">
                                <i :data-lucide="pwReq.okSp?'check':'x'" class="w-3 h-3"></i>Include at least one
                                special character</li>
                        </ul>
                    </div>
                    <div class="text-red-500 text-sm" x-show="errors.password" x-text="errors.password"></div>
                    <button type="submit"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium flex items-center justify-center">
                        <i data-lucide="key" class="w-4 h-4 mr-2" x-show="!loading.password"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.password"></i>
                        <span x-text="loading.password ? 'Updating…' : 'Update Password'">Update Password</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteAccountModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center"
        x-show="modals.deleteAccount" x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.deleteAccount=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Delete Account</h3>
                    <button @click="modals.deleteAccount=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-white/10 rounded-lg grid place-items-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-white"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold text-secondary dark:text-white">Are you absolutely sure?</h4>
                        <p class="text-sm text-gray-600 dark:text-white/70">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-700 dark:text-white/80 mb-6">This will permanently delete your account and remove
                    all of your data from our servers. This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button type="button"
                        class="flex-1 px-4 py-3 border border-gray-200 dark:border-white/10 rounded-xl text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 transition-colors font-medium"
                        @click="modals.deleteAccount=false">Cancel</button>
                    <button type="button"
                        class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium flex items-center justify-center"
                        @click="doDeleteAccount" :disabled="loading.deleteAccount">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2" x-show="!loading.deleteAccount"></i>
                        <i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin" x-show="loading.deleteAccount"></i>
                        <span x-text="loading.deleteAccount ? 'Deleting…' : 'Delete Account'">Delete Account</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="contactAdminModal" class="fixed inset-0 z-50 p-4 flex items-center justify-center"
        x-show="modals.contactAdmin" x-transition x-cloak>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modals.contactAdmin=false"></div>
        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 border border-gray-100 dark:border-white/10">
            <div class="p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Contact Admin</h3>
                    <button @click="modals.contactAdmin=false"
                        class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-4 h-4 text-gray-500 dark:text-white/60"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="text-secondary dark:text-white/90 space-y-4">
                    <p>If you can't access your current email or phone number, please contact our admin team for
                        assistance.</p>
                    <div class="bg-blue-50 dark:bg-white/5 rounded-xl p-4 border border-blue-200 dark:border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-white/10 rounded-lg grid place-items-center">
                                <i data-lucide="headphones" class="w-4 h-4 text-blue-600 dark:text-white"></i>
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
                    <button @click="modals.contactAdmin=false"
                        class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">
                        <i data-lucide="check" class="w-4 h-4 mr-2 inline-block"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="successNotification" x-show="notify.success.show" x-transition
        class="fixed top-4 right-4 bg-green-100 dark:bg-green-900/20 border-l-4 border-green-500 text-green-700 dark:text-green-200 p-4 rounded-xl shadow-lg z-50">
        <div class="flex items-center">
            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i><span x-text="notify.success.message"></span>
        </div>
    </div>

    <div id="errorNotification" x-show="notify.error.show" x-transition
        class="fixed top-4 right-4 bg-red-100 dark:bg-red-900/20 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4 rounded-xl shadow-lg z-50">
        <div class="flex items-center">
            <i data-lucide="alert-circle" class="w-4 h-4 mr-2"></i><span x-text="notify.error.message"></span>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js"></script>

<script>
    function profileApp() {
        return {
            currentTab: 'overview',
            user: {},
            fullName: 'Loading…',
            statusText: 'Loading…',
            emailStatus: 'Loading…',
            phoneStatus: 'Loading…',
            memberSince: 'Loading…',
            lastLogin: 'Last Login',
            missingFields: [],
            guidedMode: false,
            modals: { incomplete: false, editNames: false, editEmail: false, editPhone: false, changePassword: false, deleteAccount: false, contactAdmin: false },
            notify: { success: { show: false, message: '' }, error: { show: false, message: '' } },
            loading: { names: false, email: false, phone: false, password: false, deleteAccount: false },
            errors: { names: '', email: '', phone: '', phoneDirect: '', phoneForm: '', password: '' },
            forms: {
                names: { first_name: '', last_name: '' },
                email: { step: 1, newEmail: '', newEmailDirect: '', existingOtp: Array(6).fill(''), newOtp: Array(6).fill('') },
                phone: { step: 1, newPhone: '', newPhoneDirect: '', existingOtp: Array(6).fill(''), newOtp: Array(6).fill('') },
                password: { current: '', new: '', confirm: '', strength: 0, show: { current: false, new: false, confirm: false } }
            },
            pwReq: { okLen: false, okUp: false, okLo: false, okNum: false, okSp: false },
            tel: null,
            telDirect: null,
            init() {
                this.initIntlTel();
                this.fetchUser();
                this.observeTheme();
                this.$nextTick(() => { lucide.createIcons(); });
            },
            initIntlTel() {
                this.tel = window.intlTelInput(this.$refs.newPhone, { utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js", onlyCountries: ["ug"], separateDialCode: true, autoPlaceholder: "polite", initialCountry: "ug" });
                this.telDirect = window.intlTelInput(this.$refs.newPhoneDirect, { utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/utils.js", onlyCountries: ["ug"], separateDialCode: true, autoPlaceholder: "polite", initialCountry: "ug" });
                this.$nextTick(() => document.querySelectorAll('.iti').forEach(el => el.classList.add('w-full')));
            },
            observeTheme() {
                const obs = new MutationObserver(() => this.updateAvatar());
                obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            },
            switchTab(tab) {
                this.currentTab = tab;
                this.$nextTick(() => lucide.createIcons());
            },
            tabBtnClass(tab) {
                return this.currentTab === tab
                    ? 'border-b-primary text-primary'
                    : 'border-b-transparent text-gray-500 dark:text-white/70 hover:text-primary hover:border-b-primary/30';
            },
            isMissing(field) {
                return this.missingFields.includes(field);
            },
            missingLabel(f) {
                const map = { first_name: 'First Name', email: 'Email Address', phone: 'Phone Number' };
                return map[f] || f;
            },
            statusClass() {
                const s = (this.user.status || '').toLowerCase();
                if (s === 'active') return 'text-green-600 dark:text-green-400';
                if (s === 'inactive') return 'text-yellow-600 dark:text-yellow-300';
                if (s === 'suspended') return 'text-red-600 dark:text-red-400';
                if (s === 'deleted') return 'text-gray-600 dark:text-white/70';
                return 'text-gray-600 dark:text-white/70';
            },
            fetchUser() {
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=getUserDetails')
                    .then(r => r.json())
                    .then(resp => {
                        if (!resp.success) return this.err('Failed to load user details.');
                        this.user = resp.data || {};
                        this.forms.names.first_name = this.user.first_name || '';
                        this.forms.names.last_name = this.user.last_name || '';
                        this.fullName = (this.user.first_name || '') + ' ' + (this.user.last_name || '');
                        this.fullName = this.fullName.trim() || 'User';
                        this.statusText = this.user.status ? this.user.status.charAt(0).toUpperCase() + this.user.status.slice(1) : 'Unknown';
                        this.emailStatus = this.user.email ? 'Verified' : 'Not Set';
                        this.phoneStatus = this.user.phone ? 'Verified' : 'Not Set';
                        const jd = this.user.created_at ? new Date(this.user.created_at) : new Date();
                        this.memberSince = jd.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                        this.lastLogin = this.user.last_login ? this.getTimeAgo(new Date(this.user.last_login)) : 'First login';
                        this.updateAvatar();
                        this.evaluateCompleteness();
                        this.$nextTick(() => lucide.createIcons());
                    })
                    .catch(() => this.err('Failed to load user details.'));
            },
            evaluateCompleteness() {
                this.missingFields = ['first_name', 'email', 'phone'].filter(f => !((this.user[f] || '').toString().trim()));
                if (this.missingFields.length) this.modals.incomplete = true;
            },
            openEditNamesModal() {
                this.errors.names = '';
                this.modals.editNames = true;
                this.$nextTick(() => lucide.createIcons());
            },
            openNextIncompleteItem() {
                const order = ['first_name', 'phone', 'email'];
                const next = order.find(f => this.missingFields.includes(f));
                if (!next) {
                    this.guidedMode = false;
                    this.modals.incomplete = false;
                    this.ok('Profile complete. Thanks!');
                    return;
                }
                if (next === 'first_name') this.openEditNamesModal();
                else if (next === 'phone') this.editPhone();
                else if (next === 'email') this.editEmail();
            },
            updateAvatar() {
                const first = this.user.first_name || '';
                const last = this.user.last_name || '';
                const username = this.user.username || '';
                const seed = (first + ' ' + last).trim() || username || 'U';
                const isDark = document.documentElement.classList.contains('dark');
                const bg = isDark ? '0F172A' : '3B82F6';
                const url = `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(seed)}&size=128&radius=50&backgroundColor=${bg}&fontWeight=700`;
                if (this.$refs.profilePic) {
                    this.$refs.profilePic.src = url;
                    this.$refs.profilePic.alt = `${first || username || 'User'}'s Profile Picture`;
                }
            },
            ok(m) {
                this.notify.success.message = m;
                this.notify.success.show = true;
                setTimeout(() => this.notify.success.show = false, 3000);
            },
            err(m) {
                this.notify.error.message = m;
                this.notify.error.show = true;
                setTimeout(() => this.notify.error.show = false, 3000);
            },
            updateNames() {
                this.errors.names = '';
                if (!this.forms.names.first_name.trim()) {
                    this.errors.names = 'First name is required';
                    return;
                }
                this.loading.names = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=updateNames', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ first_name: this.forms.names.first_name.trim(), last_name: this.forms.names.last_name.trim() })
                }).then(r => r.json()).then(resp => {
                    this.loading.names = false;
                    if (resp.success) {
                        this.modals.editNames = false;
                        this.ok(resp.message || 'Updated');
                        this.fetchUser();
                        if (this.guidedMode) setTimeout(() => this.openNextIncompleteItem(), 600);
                    } else {
                        this.errors.names = resp.message || 'Failed to update';
                    }
                }).catch(() => {
                    this.loading.names = false;
                    this.errors.names = 'Server error. Try again.';
                });
            },
            editEmail() {
                this.errors.email = '';
                this.forms.email = { step: 1, newEmail: '', newEmailDirect: '', existingOtp: Array(6).fill(''), newOtp: Array(6).fill('') };
                this.modals.editEmail = true;
                this.$nextTick(() => lucide.createIcons());
            },
            sendExistingEmailOTP() {
                this.errors.email = '';
                this.loading.email = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingEmailOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                }).then(r => r.json()).then(resp => {
                    this.loading.email = false;
                    if (resp.success) {
                        this.forms.email.step = 2;
                        this.$nextTick(() => {
                            const first = document.querySelector('#editEmailModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.email = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.email = false;
                    this.errors.email = 'Server error. Try again.';
                });
            },
            verifyExistingEmailOTP() {
                this.errors.email = '';
                const otp = this.forms.email.existingOtp.join('').replace(/\D/g, '');
                if (otp.length !== 6) {
                    this.errors.email = 'Please enter a valid 6-digit code';
                    return;
                }
                this.loading.email = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingEmailOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otp })
                }).then(r => r.json()).then(resp => {
                    this.loading.email = false;
                    if (resp.success) this.forms.email.step = 3;
                    else this.errors.email = resp.message || 'Invalid code';
                }).catch(() => {
                    this.loading.email = false;
                    this.errors.email = 'Server error. Try again.';
                });
            },
            sendNewEmailOTP() {
                this.errors.email = '';
                if (!this.forms.email.newEmail.trim()) {
                    this.errors.email = 'New email is required';
                    return;
                }
                this.loading.email = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_email: this.forms.email.newEmail.trim() })
                }).then(r => r.json()).then(resp => {
                    this.loading.email = false;
                    if (resp.success) {
                        this.forms.email.step = 4;
                        this.$nextTick(() => {
                            const first = document.querySelector('#editEmailModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.email = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.email = false;
                    this.errors.email = 'Server error. Try again.';
                });
            },
            verifyNewEmailOTP() {
                this.errors.email = '';
                const otp = this.forms.email.newOtp.join('').replace(/\D/g, '');
                if (otp.length !== 6) {
                    this.errors.email = 'Please enter a valid 6-digit code';
                    return;
                }
                this.loading.email = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewEmailOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otp })
                }).then(r => r.json()).then(resp => {
                    this.loading.email = false;
                    if (resp.success) {
                        this.modals.editEmail = false;
                        this.ok(resp.message || 'Email updated');
                        this.fetchUser();
                        if (this.guidedMode) setTimeout(() => this.openNextIncompleteItem(), 600);
                    } else this.errors.email = resp.message || 'Invalid code';
                }).catch(() => {
                    this.loading.email = false;
                    this.errors.email = 'Server error. Try again.';
                });
            },
            sendDirectEmailOTP() {
                this.errors.email = '';
                if (!this.forms.email.newEmailDirect.trim()) {
                    this.errors.email = 'Email is required';
                    return;
                }
                this.loading.email = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendNewEmailOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_email: this.forms.email.newEmailDirect.trim() })
                }).then(r => r.json()).then(resp => {
                    this.loading.email = false;
                    if (resp.success) {
                        this.forms.email.step = 4;
                        this.forms.email.newOtp = Array(6).fill('');
                        this.$nextTick(() => {
                            const first = document.querySelector('#editEmailModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.email = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.email = false;
                    this.errors.email = 'Server error. Try again.';
                });
            },
            editPhone() {
                this.errors.phone = '';
                this.errors.phoneDirect = '';
                this.errors.phoneForm = '';
                this.forms.phone = { step: 1, newPhone: '', newPhoneDirect: '', existingOtp: Array(6).fill(''), newOtp: Array(6).fill('') };
                this.modals.editPhone = true;
                this.$nextTick(() => lucide.createIcons());
            },
            validateUGPhone(which) {
                const inst = which === 'new' ? this.tel : this.telDirect;
                const errKey = which === 'new' ? 'phone' : 'phoneDirect';
                this.errors[errKey] = '';
                const num = inst.getNumber();
                if (!inst.isValidNumber()) {
                    this.errors[errKey] = 'Invalid phone number';
                    return null;
                }
                if (!num.startsWith('+256')) {
                    this.errors[errKey] = 'Only Uganda (+256) phone numbers are allowed';
                    return null;
                }
                return num;
            },
            sendExistingPhoneOTP() {
                this.errors.phoneForm = '';
                this.loading.phone = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendExistingPhoneOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                }).then(r => r.json()).then(resp => {
                    this.loading.phone = false;
                    if (resp.success) {
                        this.forms.phone.step = 2;
                        this.$nextTick(() => {
                            const first = document.querySelector('#editPhoneModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.phoneForm = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.phone = false;
                    this.errors.phoneForm = 'Server error. Try again.';
                });
            },
            verifyExistingPhoneOTP() {
                this.errors.phoneForm = '';
                const otp = this.forms.phone.existingOtp.join('').replace(/\D/g, '');
                if (otp.length !== 6) {
                    this.errors.phoneForm = 'Please enter a valid 6-digit code';
                    return;
                }
                this.loading.phone = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=verifyExistingPhoneOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otp })
                }).then(r => r.json()).then(resp => {
                    this.loading.phone = false;
                    if (resp.success) this.forms.phone.step = 3;
                    else this.errors.phoneForm = resp.message || 'Invalid code';
                }).catch(() => {
                    this.loading.phone = false;
                    this.errors.phoneForm = 'Server error. Try again.';
                });
            },
            sendNewPhoneOTP() {
                this.errors.phoneForm = '';
                const num = this.validateUGPhone('new');
                if (!num) return;
                this.loading.phone = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_phone: num })
                }).then(r => r.json()).then(resp => {
                    this.loading.phone = false;
                    if (resp.success) {
                        this.forms.phone.step = 4;
                        this.$nextTick(() => {
                            const first = document.querySelector('#editPhoneModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.phoneForm = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.phone = false;
                    this.errors.phoneForm = 'Server error. Try again.';
                });
            },
            verifyNewPhoneOTP() {
                this.errors.phoneForm = '';
                const otp = this.forms.phone.newOtp.join('').replace(/\D/g, '');
                if (otp.length !== 6) {
                    this.errors.phoneForm = 'Please enter a valid 6-digit code';
                    return;
                }
                this.loading.phone = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=verifyNewPhoneOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ otp })
                }).then(r => r.json()).then(resp => {
                    this.loading.phone = false;
                    if (resp.success) {
                        this.modals.editPhone = false;
                        this.ok(resp.message || 'Phone updated');
                        this.fetchUser();
                        if (this.guidedMode) setTimeout(() => this.openNextIncompleteItem(), 600);
                    } else this.errors.phoneForm = resp.message || 'Invalid code';
                }).catch(() => {
                    this.loading.phone = false;
                    this.errors.phoneForm = 'Server error. Try again.';
                });
            },
            sendDirectPhoneOTP() {
                this.errors.phoneDirect = '';
                const num = this.validateUGPhone('direct');
                if (!num) return;
                this.loading.phone = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=sendNewPhoneOTP', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_phone: num })
                }).then(r => r.json()).then(resp => {
                    this.loading.phone = false;
                    if (resp.success) {
                        this.forms.phone.step = 4;
                        this.forms.phone.newOtp = Array(6).fill('');
                        this.$nextTick(() => {
                            const first = document.querySelector('#editPhoneModal [inputmode="numeric"]');
                            if (first) first.focus();
                        });
                    } else this.errors.phoneDirect = resp.message || 'Failed to send code';
                }).catch(() => {
                    this.loading.phone = false;
                    this.errors.phoneDirect = 'Server error. Try again.';
                });
            },
            otpInput(e, group, key) {
                e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
                if (e.target.value && e.target.nextElementSibling) e.target.nextElementSibling.focus();
            },
            changePassword() {
                this.errors.password = '';
                const cur = this.forms.password.current;
                const nw = this.forms.password.new;
                const cf = this.forms.password.confirm;
                if (!cur || !nw || !cf) { this.errors.password = 'All fields are required'; return; }
                if (nw !== cf) { this.errors.password = 'Passwords do not match'; return; }
                if (!(this.pwReq.okLen && this.pwReq.okUp && this.pwReq.okLo && this.pwReq.okNum && this.pwReq.okSp)) {
                    this.errors.password = 'Password does not meet requirements'; return;
                }
                this.loading.password = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=changePassword', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ current_password: cur, new_password: nw })
                }).then(r => r.json()).then(resp => {
                    this.loading.password = false;
                    if (resp.success) {
                        this.forms.password.current = '';
                        this.forms.password.new = '';
                        this.forms.password.confirm = '';
                        this.forms.password.strength = 0;
                        this.modals.changePassword = false;
                        this.ok(resp.message || 'Password updated');
                    } else this.errors.password = resp.message || 'Update failed';
                }).catch(() => {
                    this.loading.password = false;
                    this.errors.password = 'Server error. Try again.';
                });
            },
            updatePasswordStrength() {
                const p = this.forms.password.new || '';
                this.pwReq.okLen = p.length >= 8;
                this.pwReq.okUp = /[A-Z]/.test(p);
                this.pwReq.okLo = /[a-z]/.test(p);
                this.pwReq.okNum = /\d/.test(p);
                this.pwReq.okSp = /[^A-Za-z0-9]/.test(p);
                let s = 0;
                if (this.pwReq.okLen) s += 20;
                if (this.pwReq.okUp) s += 20;
                if (this.pwReq.okLo) s += 20;
                if (this.pwReq.okNum) s += 20;
                if (this.pwReq.okSp) s += 20;
                this.forms.password.strength = s;
            },
            strengthBarColor() {
                if (this.forms.password.strength < 40) return 'bg-red-500';
                if (this.forms.password.strength < 80) return 'bg-yellow-500';
                return 'bg-green-500';
            },
            doDeleteAccount() {
                this.loading.deleteAccount = true;
                fetch(BASE_URL + 'account/fetch/manageProfile.php?action=deleteAccount', { method: 'POST' })
                    .then(r => r.json())
                    .then(resp => {
                        this.loading.deleteAccount = false;
                        if (resp.success) {
                            this.modals.deleteAccount = false;
                            this.ok(resp.message || 'Account deleted');
                            setTimeout(() => window.location.href = BASE_URL, 1500);
                        } else this.err(resp.message || 'Failed to delete account');
                    })
                    .catch(() => {
                        this.loading.deleteAccount = false;
                        this.err('Failed to delete account. Please try again.');
                    });
            },
            getTimeAgo(d) {
                const s = Math.floor((new Date() - d) / 1000);
                let i = Math.floor(s / 31536000); if (i > 1) return i + ' years ago'; if (i === 1) return '1 year ago';
                i = Math.floor(s / 2592000); if (i > 1) return i + ' months ago'; if (i === 1) return '1 month ago';
                i = Math.floor(s / 86400); if (i > 1) return i + ' days ago'; if (i === 1) return '1 day ago';
                i = Math.floor(s / 3600); if (i > 1) return i + ' hours ago'; if (i === 1) return '1 hour ago';
                i = Math.floor(s / 60); if (i > 1) return i + ' minutes ago'; if (i === 1) return '1 minute ago';
                if (s < 10) return 'just now'; return s + ' seconds ago';
            }
        };
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>