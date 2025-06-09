<?php
$pageTitle = 'Terms & Conditions';
$activeNav = 'terms';
require_once __DIR__ . '/config/config.php';
ob_start();
?>

<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Custom scrollbar for better UX */
    .terms-content::-webkit-scrollbar {
        width: 6px;
    }

    .terms-content::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }

    .terms-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .terms-content::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<!-- Hero Section -->
<div class="bg-gradient-to-br from-red-600 via-red-700 to-red-800 text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-10"></div>
    <div class="container relative z-10">
        <div class="max-w-4xl mx-auto text-center px-4">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-6">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2a1 1 0 01-1 1H6a1 1 0 01-1-1v-2h10z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Terms & Conditions</h1>
            <p class="text-xl text-red-100 mb-6 max-w-2xl mx-auto">
                Please read these terms carefully before using Zzimba Online services
            </p>
            <div class="inline-flex items-center bg-white bg-opacity-20 rounded-full px-6 py-3">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                        clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Effective as of: March 01, 2024</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-gray-50 min-h-screen py-12">
    <div class="container">
        <div class="max-w-4xl mx-auto">
            <!-- Table of Contents -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Table of Contents
                </h2>
                <nav class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <a href="#introduction"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">1</span>
                        Introduction
                    </a>
                    <a href="#definitions"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">2</span>
                        Definitions
                    </a>
                    <a href="#about-us"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">3</span>
                        About Us
                    </a>
                    <a href="#access-use"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">4</span>
                        Access & Use
                    </a>
                    <a href="#age-restrictions"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">5</span>
                        Age Restrictions
                    </a>
                    <a href="#marketplace"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">6</span>
                        Our Marketplace
                    </a>
                    <a href="#usage-policy"
                        class="flex items-center p-3 text-gray-700 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <span
                            class="w-6 h-6 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-sm font-medium mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">7</span>
                        Usage Policy
                    </a>
                </nav>
            </div>

            <!-- Terms Sections -->
            <div class="space-y-8">
                <!-- Section 1: Introduction -->
                <section id="introduction" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">1</span>
                            Introduction
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="prose prose-gray max-w-none">
                            <p class="text-gray-700 leading-relaxed">
                                These User Terms, together with any and all other documents referred to herein, set out
                                the terms under
                                which Users ("Principal") appoint Us as their "Agent" to order goods on their behalf
                                through Our
                                Marketplace. Please read these User Terms carefully and ensure that you understand them
                                before ordering
                                anything under Zzimba Online on Our Marketplace. You will be required to read and accept
                                these User
                                Terms when ordering any product under Zzimba Online via Our Marketplace. If you do not
                                agree to comply
                                with and be bound by these User Terms, you will not be able to transact nor purchase
                                anything on Our
                                Marketplace. These User Terms, as well as any and all contracts, are in the English
                                language only.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Definitions -->
                <section id="definitions" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">2</span>
                            Definitions and Interpretation
                        </h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-6">
                            In these User Terms, unless the context otherwise requires, the following expressions have
                            the following meanings:
                        </p>
                        <div class="grid gap-4">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Account:</dt>
                                <dd class="text-gray-700">means an account required to access and/or use certain parts
                                    of Our Site, on Our Marketplace.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Principal:</dt>
                                <dd class="text-gray-700">means a User who makes an order under Zzimba Online on Our
                                    Marketplace.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Content:</dt>
                                <dd class="text-gray-700">means any and all text, images, audio, video, scripts, code,
                                    software, databases, and any other form of information capable of being stored on a
                                    computer that appears on, or forms part of, Our Site.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Third Party:</dt>
                                <dd class="text-gray-700">A supplier of specific items specified by the Principal.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Marketplace:</dt>
                                <dd class="text-gray-700">means our platform for User services on Our Site.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">Our Site:</dt>
                                <dd class="text-gray-700">means this website, zzimbaonline.com.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">User:</dt>
                                <dd class="text-gray-700">means a user of Our Site.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">User Content:</dt>
                                <dd class="text-gray-700">means any Content added to Our Site by a User.</dd>
                            </div>
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition-colors">
                                <dt class="font-semibold text-gray-900 mb-2">We/Us/Our/Agent:</dt>
                                <dd class="text-gray-700">means The Engineering Marksmen Limited, a Private limited
                                    liability company registered in the Republic of Uganda- reg No. 206761, whose
                                    registered address is P.O. Box 129572 Kampala, Uganda.</dd>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 3: About Us -->
                <section id="about-us" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">3</span>
                            Information About Us
                        </h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Our Site is owned and operated by Us.</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-700">Our TIN number is shared on request.</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Section 4: Access and Use -->
                <section id="access-use" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">4</span>
                            Access to and Use of Our Site
                        </h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <div class="w-2 h-2 bg-red-600 rounded-full mt-2 mr-4 flex-shrink-0"></div>
                                <span class="text-gray-700">Access to Our Site is free of charge.</span>
                            </li>
                            <li class="flex items-start">
                                <div class="w-2 h-2 bg-red-600 rounded-full mt-2 mr-4 flex-shrink-0"></div>
                                <span class="text-gray-700">It is your responsibility to make any and all arrangements
                                    necessary in order to access Our Site.</span>
                            </li>
                            <li class="flex items-start">
                                <div class="w-2 h-2 bg-red-600 rounded-full mt-2 mr-4 flex-shrink-0"></div>
                                <span class="text-gray-700">Access to Our Site is provided "as is" and on an "as
                                    available" basis. We may alter, suspend, or discontinue Our Site (or any part of it)
                                    at any time and without notice. Subject to the remainder of these User Terms. We
                                    will not be liable to you in any way if Our Site (or any part of it) is unavailable
                                    at any time and for any period.</span>
                            </li>
                            <li class="flex items-start">
                                <div class="w-2 h-2 bg-red-600 rounded-full mt-2 mr-4 flex-shrink-0"></div>
                                <span class="text-gray-700">Use of Our Site is also subject to Our Terms of Use and
                                    Terms for Buyers. as well as the Terms of Sale. Please ensure that you have read
                                    them carefully and that you understand them.</span>
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- Section 5: Age Restrictions -->
                <section id="age-restrictions"
                    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">5</span>
                            Age Restrictions
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-amber-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-amber-800 font-medium">You may only make orders on Our Marketplace if you
                                    are at least 18 years of age.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 6: Marketplace -->
                <section id="marketplace" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">6</span>
                            Our Marketplace
                        </h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-700 mb-6">Our Market place offers these types of services:</p>

                        <div class="grid gap-6 mb-8">
                            <div class="border border-gray-200 rounded-lg p-6 hover:border-red-300 transition-colors">
                                <div class="flex items-start">
                                    <div
                                        class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Zzimba Online Virtual Store</h3>
                                        <p class="text-gray-700">Digital storefront for construction supplies where a
                                            vendor sells and get them delivered to a user's preferred location in our
                                            areas of service anywhere in Uganda. The listed products on Our Site are
                                            only available on order.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6 hover:border-red-300 transition-colors">
                                <div class="flex items-start">
                                    <div
                                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                            <path fill-rule="evenodd"
                                                d="M4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 1a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Zzimba Online Request for a Quote
                                            (RFQ)</h3>
                                        <p class="text-gray-700">Used for bulk orders, get a live quote for building
                                            materials on and off our materials yard at the best price in your delivery
                                            area. This can be stock or items for the User's projects.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6 hover:border-red-300 transition-colors">
                                <div class="flex items-start">
                                    <div
                                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Fundi Centre</h3>
                                        <p class="text-gray-700">Showcasing Industry-Professionals and specialists,
                                            experts both as certified professionals and experienced masons with a track
                                            record of professionalism and reliability.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6 hover:border-red-300 transition-colors">
                                <div class="flex items-start">
                                    <div
                                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                            <path fill-rule="evenodd"
                                                d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 mb-2">Zzimba Credit</h3>
                                        <p class="text-gray-700">Create a save to build account redeemed at maturity as
                                            building materials of user's choice, anytime in accordance to Our Terms of
                                            service.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 space-y-4">
                            <p class="text-blue-900">
                                <strong>Important Notice:</strong> You are not making a purchase from Us and are not
                                entering into a contract with Us. You are merely assigning Us your rights to purchase
                                specific goods from our verified vendors on your behalf.
                            </p>
                            <p class="text-blue-900">
                                We will not be a party to any dispute between you and any Third party. Any claims must
                                be made directly against the party concerned.
                            </p>
                            <p class="text-blue-900">
                                We do not pre-screen items that the Principal might request to be ordered through Our
                                Marketplace. We are not, therefore, in any way responsible for any items procured or for
                                the content of those items. The Principal shall verify the condition of items delivered
                                prior to acknowledging receipt.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Section 7: Usage Policy -->
                <section id="usage-policy" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white flex items-center">
                            <span
                                class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-sm font-bold mr-3">7</span>
                            Principal Rules and Acceptable Usage Policy
                        </h2>
                    </div>
                    <div class="p-6 space-y-8">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Required Compliance</h3>
                            <p class="text-gray-700 mb-4">When using Our Marketplace, you must do so lawfully, fairly,
                                and in a manner that complies with the provisions of this Clause 6. Specifically:</p>
                            <ul class="space-y-3">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">you must ensure that you comply fully with all local,
                                        national, or international laws and/or regulations;</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">you must not use Our Marketplace in any way, or for any
                                        purpose, that is unlawful or fraudulent;</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">you must not use Our Marketplace to knowingly send,
                                        upload, or in any other way transmit data that contains any form of virus or
                                        other malware, or any other code designed to adversely affect computer hardware,
                                        software, or data of any kind;</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">you must not use Our Marketplace in any way, or for any
                                        purpose, that is intended to harm any person or persons in any way;</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">you must always provide accurate, honest information to
                                        Us on Our Marketplace;</span>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Prohibited Content and Activities</h3>
                            <p class="text-gray-700 mb-4">When using Our Marketplace, you must not submit anything, or
                                otherwise do anything that:</p>
                            <div class="grid gap-3">
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is sexually explicit;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is obscene, deliberately offensive, hateful, or otherwise
                                        inflammatory;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">promotes violence;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">promotes or assists in any form of unlawful
                                        activity;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">discriminates against, or is in any way defamatory of,
                                        any person, group, or class of persons; race; gender; religion; nationality;
                                        disability; sexual orientation; or age;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is intended or otherwise likely to threaten, harass,
                                        alarm, inconvenience, upset, or embarrass another person;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is calculated or is otherwise likely to deceive;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is intended or otherwise likely to infringe (or threaten
                                        to infringe) another person's right to privacy or otherwise uses their personal
                                        data in a way that you do not have a right to;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">misleadingly impersonates any person or otherwise
                                        misrepresents your identity or affiliation in a way that is calculated to
                                        deceive (obvious parodies are not included within this definition provided that
                                        they do not fall within any of the other provisions of this sub-Clause
                                        6.2);</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">implies any form of affiliation with Us where none
                                        exists;</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">infringes, or assists in the infringement of, the
                                        intellectual property rights (including, but not limited to, copyright,
                                        trademarks, patents, and database rights) of any other party; or</span>
                                </div>
                                <div class="flex items-start bg-red-50 border border-red-200 rounded-lg p-3">
                                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-red-800">is in breach of any legal duty owed to a third party
                                        including, but not limited to, contractual duties and duties of
                                        confidence.</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Enforcement Actions</h3>
                            <p class="text-gray-700 mb-4">We reserve the right to suspend or terminate your access to
                                Our Marketplace if you materially breach the provisions of this Clause 6 or any of the
                                other provisions of these Agent Terms. Further actions We may take include, but are not
                                limited to:</p>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                                <ul class="space-y-3">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">removing any offending material from Our
                                            Marketplace;</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">issuing you with a written warning;</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">legal proceedings against you for reimbursement of
                                            any and all relevant costs resulting from your breach on an indemnity
                                            basis;</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">further legal action against you as
                                            appropriate;</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">disclosing such information to law enforcement
                                            authorities as required or as We deem reasonably necessary; and/or</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-gray-700">any other actions which We deem reasonably
                                            necessary, appropriate, and lawful.</span>
                                    </li>
                                </ul>
                            </div>
                            <p class="text-gray-700 mt-4">
                                We hereby exclude any and all liability arising out of any actions that We may take in
                                response to breaches of these Agent Terms.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Footer CTA -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center mt-8">
                <div class="max-w-2xl mx-auto">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Questions About These Terms?</h3>
                    <p class="text-gray-600 mb-6">
                        If you have any questions about these Terms & Conditions, please don't hesitate to contact us.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="<?= BASE_URL ?>contact-us"
                            class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                            Contact Us
                        </a>
                        <a href="<?= BASE_URL ?>"
                            class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>