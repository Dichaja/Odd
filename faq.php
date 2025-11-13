<?php
$activeNav = 'faq';
require_once __DIR__ . '/config/config.php';
$pageTitle = 'FAQ & Reviews | Zzimba Online Uganda';

ob_start();
?>
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto
    }

    [x-cloak] {
        display: none !important
    }

    .fade-in-up {
        animation: fade-in-up .35s ease-out both
    }

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(6px)
        }

        to {
            opacity: 1;
            transform: translateY(0)
        }
    }
</style>

<div x-data="faqPage" x-init="init()" class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Frequently Asked Questions</h1>
        <p class="text-gray-600 dark:text-slate-300 max-w-2xl mx-auto">Find answers to common questions about Zzimba Online and share your experience with us</p>
    </div>

    <!-- FAQ Section -->
    <div class="mb-16">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Common Questions</h2>
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                <button @click="toggleFaq(1)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-semibold text-gray-900 dark:text-white">What is Zzimba Online?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="openFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-slate-300">Zzimba Online is Uganda's premier online marketplace for building materials and construction supplies. We connect buyers with trusted vendors across the country, making it easy to find quality materials at competitive prices.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                <button @click="toggleFaq(2)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-semibold text-gray-900 dark:text-white">How do I place an order?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="openFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-slate-300">Browse our products, select the items you need, and click "Buy" to view vendor pricing. You can then contact vendors directly or request a quote for bulk orders. Create an account to track your orders and save your favorite vendors.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                <button @click="toggleFaq(3)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-semibold text-gray-900 dark:text-white">Do you offer delivery services?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="openFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-slate-300">Yes! Most of our vendors offer delivery services. Delivery options and costs vary by vendor and location. You can discuss delivery details directly with the vendor when placing your order.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                <button @click="toggleFaq(4)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-semibold text-gray-900 dark:text-white">How can I become a vendor?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="openFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-slate-300">To become a vendor on Zzimba Online, create an account and navigate to the vendor registration section. Fill out the application form with your business details, and our team will review your application within 2-3 business days.</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-gray-200 dark:border-slate-800 overflow-hidden">
                <button @click="toggleFaq(5)" class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    <span class="font-semibold text-gray-900 dark:text-white">What payment methods do you accept?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 transition-transform" :class="openFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openFaq === 5" x-collapse class="px-6 pb-4">
                    <p class="text-gray-600 dark:text-slate-300">Payment methods vary by vendor. Most vendors accept mobile money (MTN, Airtel), bank transfers, and cash on delivery. Some also accept credit/debit cards. Check with individual vendors for their accepted payment methods.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Form Section -->
    <div class="mb-16">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 md:p-8 border border-gray-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Share Your Experience</h2>
            <p class="text-gray-600 dark:text-slate-300 mb-6">Help us improve by sharing your feedback about Zzimba Online</p>
            
            <template x-if="!auth.loggedIn">
                <div class="text-center py-8">
                    <div class="mb-4">
                        <i data-lucide="lock" class="w-16 h-16 text-gray-300 mx-auto"></i>
                    </div>
                    <p class="text-gray-600 dark:text-slate-300 mb-4">Please log in to write a review</p>
                    <button @click="promptLogin()" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Log In to Review
                    </button>
                </div>
            </template>

            <template x-if="auth.loggedIn">
                <form @submit.prevent="submitReview" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-2">Your Rating</label>
                        <div class="star-rating flex space-x-2">
                            <template x-for="i in 5" :key="i">
                                <button type="button" @click="reviewRating = i" @mouseover="hoverRating=i" @mouseleave="hoverRating=0">
                                    <i data-lucide="star" class="w-8 h-8" :class="(hoverRating ? i <= hoverRating : i <= reviewRating) ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300'"></i>
                                </button>
                            </template>
                        </div>
                        <p x-show="reviewRating > 0" class="text-sm text-gray-600 dark:text-slate-300 mt-2">
                            You rated Zzimba Online <span x-text="reviewRating"></span> out of 5 stars
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-2">Your Review</label>
                        <textarea rows="5" maxlength="500" x-model="reviewComment" class="w-full px-4 py-3 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent resize-none bg-white dark:bg-slate-900 text-gray-900 dark:text-slate-100" placeholder="Tell us about your experience with Zzimba Online... (minimum 10 characters)" required></textarea>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400 mt-1">
                            <span x-show="reviewComment.length > 0 && reviewComment.length < 10" class="text-red-500">
                                Minimum 10 characters required
                            </span>
                            <span class="ml-auto">
                                <span x-text="reviewComment?.length || 0"></span>/500 characters
                            </span>
                        </div>
                    </div>

                    <button type="submit" :disabled="reviewRating < 1 || reviewComment.length < 10 || isSubmitting" :class="reviewRating < 1 || reviewComment.length < 10 || isSubmitting ? 'opacity-50 cursor-not-allowed' : ''" class="w-full md:w-auto bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-lg font-medium transition-colors">
                        <span x-show="!isSubmitting" class="flex items-center justify-center">
                            <i data-lucide="send" class="w-5 h-5 mr-2"></i> 
                            Submit Review
                        </span>
                        <span x-show="isSubmitting" class="flex items-center justify-center">
                            <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>
                            Submitting...
                        </span>
                    </button>
                </form>
            </template>
        </div>
    </div>

    <!-- Reviews Display Section -->
    <div class="mb-16">
        <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm p-6 md:p-8 border border-gray-200 dark:border-slate-800">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Recent Reviews</h2>

            <div x-show="reviewsLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
                <p class="mt-2 text-gray-600 dark:text-slate-300">Loading reviews...</p>
            </div>

            <div x-show="!reviewsLoading && reviews.length === 0" class="text-center py-8">
                <div class="mb-4">
                    <i data-lucide="message-circle" class="w-16 h-16 text-gray-300 mx-auto"></i>
                </div>
                <h4 class="text-xl font-semibold text-gray-600 dark:text-slate-300 mb-2">No Reviews Yet</h4>
                <p class="text-gray-500 dark:text-slate-400">Be the first to share your experience!</p>
            </div>

            <div x-show="!reviewsLoading && reviews.length > 0" class="space-y-6">
                <template x-for="review in reviews" :key="review.id">
                    <div class="border-b border-gray-200 dark:border-slate-800 pb-6 last:border-0 last:pb-0 fade-in-up">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-gray-800 dark:text-white" x-text="review.reviewer_name"></span>
                                    <template x-if="review.is_verified">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                            Verified
                                        </span>
                                    </template>
                                </div>
                                <div class="text-gray-500 dark:text-slate-400 text-sm" x-text="formatDate(review.created_at)"></div>
                            </div>
                            <div class="flex">
                                <template x-for="i in 5" :key="i">
                                    <i data-lucide="star" :class="i <= review.rating ? 'fill-amber-400 stroke-amber-400' : 'stroke-gray-300'" class="w-5 h-5"></i>
                                </template>
                            </div>
                        </div>
                        <p class="text-gray-700 dark:text-slate-300" x-text="review.review_text"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="bg-gradient-to-br from-red-50 to-orange-50 dark:from-slate-800 dark:to-slate-900 rounded-xl p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Still Have Questions?</h2>
        <p class="text-gray-600 dark:text-slate-300 mb-6">Our support team is here to help you</p>
        <a href="<?= BASE_URL ?>contact-us" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-white rounded-lg font-medium transition-colors">
            <i data-lucide="mail" class="w-5 h-5 mr-2"></i>
            Contact Support
        </a>
    </div>
</div>

<script>
function faqPage() {
    return {
        BASE_URL: window.BASE_URL || '<?= BASE_URL ?>',
        openFaq: null,
        reviewRating: 0,
        hoverRating: 0,
        reviewComment: '',
        isSubmitting: false,
        auth: {
            loggedIn: false,
            isAdminOrManager: false
        },
        reviews: [],
        reviewsLoading: false,

        init() {
            this.$nextTick(() => {
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            });
            this.checkAuth();
            this.loadReviews();
        },

        async checkAuth() {
            try {
                const response = await fetch(this.BASE_URL + 'fetch/check-session.php', {
                    credentials: 'include'
                });
                const data = await response.json();
                if (data.success && data.logged_in) {
                    this.auth.loggedIn = true;
                    this.auth.isAdminOrManager = data.is_admin || false;
                }
            } catch (error) {
                console.error('Error checking auth:', error);
            }
        },

        async loadReviews() {
            this.reviewsLoading = true;
            try {
                const response = await fetch(this.BASE_URL + 'fetch/manageProductReviews.php?action=getPlatformReviews&limit=50');
                const data = await response.json();
                if (data.success) {
                    this.reviews = data.reviews || [];
                }
            } catch (error) {
                console.error('Error loading reviews:', error);
            } finally {
                this.reviewsLoading = false;
                this.$nextTick(() => {
                    if (window.lucide && lucide.createIcons) lucide.createIcons();
                });
            }
        },

        toggleFaq(id) {
            this.openFaq = this.openFaq === id ? null : id;
            this.$nextTick(() => {
                if (window.lucide && lucide.createIcons) lucide.createIcons();
            });
        },

        promptLogin() {
            if (typeof openAuthModal === 'function') {
                openAuthModal();
            } else {
                window.location.href = this.BASE_URL + 'login/login.php';
            }
        },

        async submitReview() {
            if (!this.auth.loggedIn) {
                this.promptLogin();
                return;
            }

            if (this.reviewRating < 1) {
                this.showToast('Please select a rating', 'error');
                return;
            }

            if (!this.reviewComment.trim() || this.reviewComment.length < 10) {
                this.showToast('Review must be at least 10 characters long', 'error');
                return;
            }

            if (this.isSubmitting) return;

            this.isSubmitting = true;

            const formData = new FormData();
            formData.append('action', 'submit_platform_review');
            formData.append('rating', this.reviewRating);
            formData.append('comment', this.reviewComment.trim());

            try {
                const response = await fetch(this.BASE_URL + 'fetch/manageProductReviews.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.isSubmitting = false;

                if (data.success) {
                    this.showToast(data.message || 'Thank you for your review!', 'success');
                    this.reviewComment = '';
                    this.reviewRating = 0;
                    this.hoverRating = 0;
                    this.$nextTick(() => {
                        if (window.lucide && lucide.createIcons) lucide.createIcons();
                    });
                    
                    setTimeout(() => {
                        this.loadReviews();
                    }, 1500);
                } else {
                    this.showToast(data.error || 'Failed to submit review', 'error');
                }
            } catch (error) {
                this.isSubmitting = false;
                console.error('Error submitting review:', error);
                this.showToast('Network error. Please check your connection and try again.', 'error');
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diffMs = now - date;
            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            if (diffDays === 0) return 'Today';
            if (diffDays === 1) return 'Yesterday';
            if (diffDays < 7) return `${diffDays} days ago`;
            if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
            if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
            return date.toLocaleDateString();
        },

        showToast(message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                alert(message);
            }
        }
    }
}
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>