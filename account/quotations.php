<?php
$pageTitle = 'My Quotations';
$activeNav = 'quotations';
require_once __DIR__ . '/../config/config.php';
ob_start();
?>

<div class="min-h-screen bg-user-content dark:bg-secondary/10">
    <div class="bg-white dark:bg-secondary border-b border-gray-200 dark:border-white/10 px-4 sm:px-6 lg:px-8 py-5">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-user-primary/10 rounded-xl grid place-items-center">
                        <i class="fas fa-file-invoice text-user-primary text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-secondary dark:text-white font-rubik">My
                            Quotations</h1>
                        <p class="text-sm text-gray-text dark:text-white/70">View and manage your submitted quotation
                            requests</p>
                    </div>
                </div>
                <button id="refreshBtn"
                    class="hidden sm:inline-flex px-5 py-2.5 bg-user-primary text-white rounded-xl hover:bg-user-primary/90 transition-all duration-200 items-center gap-2 font-medium shadow-lg shadow-user-primary/25">
                    <i class="fas fa-sync-alt"></i><span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <div
        class="sticky top-0 z-30 backdrop-blur bg-white/70 dark:bg-secondary/70 border-b border-gray-200 dark:border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_auto] gap-3">
                <div class="relative">
                    <input type="text" id="searchFilter" placeholder="Search requests..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-2xl text-sm bg-white dark:bg-white/5 text-secondary dark:text-white border border-gray-200 dark:border-white/10 focus:outline-none focus:ring-4 focus:ring-user-primary/15">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 dark:text-white/50 text-sm"></i>
                </div>
                <div class="cselect-wrapper">
                    <select id="statusFilter" class="form-select cselect-target" data-cselect>
                        <option value="all">All Status</option>
                        <option value="New">New</option>
                        <option value="Processing">Processing</option>
                        <option value="Processed">Processed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Paid">Paid</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button id="clearFilters"
                        class="w-full sm:w-auto px-4 py-2.5 text-sm rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10">
                        Clear Filters
                    </button>
                    <button id="refreshBtnMobile"
                        class="sm:hidden w-full px-4 py-2.5 bg-user-primary text-white rounded-2xl hover:bg-user-primary/90 transition-all duration-200 font-medium flex items-center justify-center gap-2 shadow-lg shadow-user-primary/25">
                        <i class="fas fa-sync-alt"></i><span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-2 sm:px-2 lg:px-2 py-4 space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div
                class="rounded-xl p-4 sm:p-5 border border-blue-200 dark:border-blue-500/20 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-500/10 dark:to-blue-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-300 uppercase tracking-wide">
                            New</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-900 dark:text-white truncate"
                            id="newRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-200 dark:bg-blue-500/20 rounded-lg grid place-items-center">
                        <i class="fas fa-file-invoice text-blue-600 dark:text-blue-300 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-yellow-200 dark:border-yellow-500/20 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-500/10 dark:to-yellow-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-[11px] font-semibold text-yellow-600 dark:text-yellow-300 uppercase tracking-wide">
                            Processing</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-900 dark:text-white truncate"
                            id="processingRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-200 dark:bg-yellow-500/20 rounded-lg grid place-items-center">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-300 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-green-200 dark:border-green-500/20 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-500/10 dark:to-green-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-green-600 dark:text-green-300 uppercase tracking-wide">
                            Processed</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-900 dark:text-white truncate"
                            id="processedRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-green-200 dark:bg-green-500/20 rounded-lg grid place-items-center">
                        <i class="fas fa-check text-green-600 dark:text-green-300 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-red-200 dark:border-red-500/20 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-500/10 dark:to-red-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-red-600 dark:text-red-300 uppercase tracking-wide">
                            Cancelled</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-900 dark:text-white truncate"
                            id="cancelledRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-red-200 dark:bg-red-500/20 rounded-lg grid place-items-center">
                        <i class="fas fa-times text-red-600 dark:text-red-300 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
            <div
                class="rounded-xl p-4 sm:p-5 border border-purple-200 dark:border-purple-500/20 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-500/10 dark:to-purple-500/5">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-[11px] font-semibold text-purple-600 dark:text-purple-300 uppercase tracking-wide">
                            Paid</p>
                        <p class="text-xl sm:text-2xl font-bold text-purple-900 dark:text-white truncate"
                            id="paidRequests">0</p>
                    </div>
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-200 dark:bg-purple-500/20 rounded-lg grid place-items-center">
                        <i class="fas fa-credit-card text-purple-600 dark:text-purple-300 text-lg sm:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white dark:bg-secondary rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">My Quotation Requests</h3>
                    <p class="hidden sm:block text-sm text-gray-text dark:text-white/70">Tap a row to view and manage
                        quotation details</p>
                </div>
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="w-full min-w-full" id="quotationsTable">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Location</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Items</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Fee Charged</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Delivery Charge</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Total Amount</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Status</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-white/70 uppercase tracking-wider whitespace-nowrap">
                                Created</th>
                        </tr>
                    </thead>
                    <tbody id="quotationsBody" class="divide-y divide-gray-100 dark:divide-white/10">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading quotations...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="quotationsListMobile" class="md:hidden divide-y divide-gray-100 dark:divide-white/10">
                <div class="px-4 py-8 text-center text-gray-500 dark:text-white/70">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading quotations...</div>
                </div>
            </div>

            <div
                class="p-4 border-t border-gray-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-text dark:text-white/70 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> requests
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 disabled:opacity-50"
                        disabled>Previous</button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-text dark:text-white/70">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10 disabled:opacity-50"
                        disabled>Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="quotationModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeQuotationModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-5 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold text-secondary dark:text-white" id="modalTitle">Quotation Details</h3>
                <p class="text-sm text-gray-text dark:text-white/70" id="modalSubtitle">View and update your quotation
                    request</p>
            </div>
            <div class="flex items-center gap-2">
                <button id="printQuotationBtn"
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-white/10"
                    title="Print PDF">
                    <i class="fas fa-print text-lg"></i>
                </button>
                <button onclick="closeQuotationModal()"
                    class="text-gray-400 hover:text-gray-600 dark:text-white/70 dark:hover:text-white">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        <div class="overflow-y-auto max-h-[calc(95vh-120px)]">
            <div id="quotationContent" class="p-5 sm:p-6">
                <div class="flex items-center justify-center py-12">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-3xl text-user-primary mb-4"></i>
                        <p class="text-gray-text dark:text-white/70">Fetching quotation details...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editConfirmModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeEditConfirmModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-edit text-yellow-600 dark:text-yellow-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Edit Quotation</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Are you sure you want to edit this quotation?
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeEditConfirmModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button onclick="confirmEdit()"
                    class="px-4 py-2 bg-user-primary text-white rounded-xl hover:bg-user-primary/90">Yes, Edit</button>
            </div>
        </div>
    </div>
</div>

<div id="saveConfirmModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeSaveConfirmModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-lg relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Save Changes - Important Notice
                    </h3>
                    <p class="text-sm text-gray-text dark:text-white/70 mt-2">You can only edit this quotation once.
                        After saving, you won't be able to edit it again. Ensure all necessary changes are done before
                        proceeding.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeSaveConfirmModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button onclick="confirmSave()"
                    class="px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700">Yes, Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div id="paymentConfirmModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closePaymentConfirmModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-credit-card text-blue-600 dark:text-blue-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Confirm Quote Payment</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Review payment details before proceeding</p>
                </div>
            </div>
            <div id="paymentDetails" class="space-y-3 mb-4"></div>
            <div class="flex justify-end gap-3">
                <button onclick="closePaymentConfirmModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Cancel</button>
                <button id="confirmPaymentBtn"
                    class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700"><i
                        class="fas fa-check mr-2"></i> Confirm Payment</button>
            </div>
        </div>
    </div>
</div>

<div id="cancelConfirmModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeCancelConfirmModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-times text-red-600 dark:text-red-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Cancel Quotation</h3>
                    <p class="text-sm text-gray-text dark:text-white/70">Are you sure you want to cancel this quotation?
                        This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeCancelConfirmModal()"
                    class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">No,
                    Keep It</button>
                <button onclick="confirmCancel()"
                    class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

<div id="successModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300"
        onclick="closeSuccessModal()"></div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-check text-green-600 dark:text-green-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Success</h3>
                    <p class="text-sm text-gray-text dark:text-white/70" id="successMessage">Operation completed
                        successfully.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeSuccessModal()"
                    class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="errorModal"
    class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 transition-all duration-300 opacity-0">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300" onclick="closeErrorModal()">
    </div>
    <div
        class="bg-white dark:bg-secondary rounded-2xl shadow-2xl w-full max-w-md relative z-10 overflow-hidden transform transition-all duration-300 scale-95">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-500/10 rounded-full grid place-items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-secondary dark:text-white">Error</h3>
                    <p class="text-sm text-gray-text dark:text-white/70" id="errorMessage">An error occurred.</p>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeErrorModal()"
                    class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="pdfContent"
    style="position: absolute; left: -9999px; top: -9999px; width: 297mm; background: white; font-family: Arial, sans-serif; color: #333; padding: 10mm;">
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    .cselect {
        position: relative;
        user-select: none
    }

    .cselect>button.cselect-btn {
        width: 100%;
        text-align: left;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        padding: .625rem .75rem;
        font-size: .875rem;
        border: 1px solid rgb(209 213 219);
        border-radius: 1rem;
        background: #fff;
        color: rgb(17 24 39);
        transition: box-shadow .15s, border-color .15s, background .15s, color .15s
    }

    .cselect>button.cselect-btn:focus {
        outline: none;
        box-shadow: 0 0 0 4px rgb(217 43 19 / .15);
        border-color: rgb(217 43 19)
    }

    .dark .cselect>button.cselect-btn {
        background: transparent;
        color: #fff;
        border-color: rgba(255, 255, 255, .2)
    }

    .cselect .cselect-icon {
        pointer-events: none;
        display: inline-flex
    }

    .cselect.open .cselect-menu {
        display: block
    }

    .cselect .cselect-menu {
        display: none;
        position: absolute;
        z-index: 30;
        margin-top: .25rem;
        width: 100%;
        max-height: 16rem;
        overflow: auto;
        border-radius: .75rem;
        border: 1px solid rgb(229 231 235);
        background: #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08)
    }

    .dark .cselect .cselect-menu {
        background: #1f2937;
        border-color: rgba(255, 255, 255, .12);
        color: #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .5)
    }

    .cselect .cselect-option {
        padding: .625rem .75rem;
        font-size: .875rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between
    }

    .cselect .cselect-option:hover {
        background: #f9fafb
    }

    .dark .cselect .cselect-option:hover {
        background: rgba(255, 255, 255, .06)
    }

    .cselect .cselect-option[aria-selected="true"]::after {
        content: "\f00c";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: .8rem
    }

    .cselect[aria-disabled="true"]>button.cselect-btn {
        opacity: .6;
        cursor: not-allowed;
        background: #f9fafb
    }

    .dark .cselect[aria-disabled="true"]>button.cselect-btn {
        background: rgba(255, 255, 255, .04)
    }

    .cselect-wrapper {
        position: relative
    }

    .cselect-wrapper select.cselect-target {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        pointer-events: none
    }

    #quotationsTable tbody tr:hover {
        background-color: rgba(230, 242, 255, .3)
    }

    .dark #quotationsTable tbody tr:hover {
        background-color: rgba(255, 255, 255, .06)
    }

    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0
    }

    #quotationsTable {
        min-width: 800px
    }

    .quantity-input:focus {
        outline: none;
        border-color: #D92B13;
        box-shadow: 0 0 0 3px rgba(217, 43, 19, .1)
    }

    .location-max {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    @media (max-width:768px) {
        #quotationsTable {
            font-size: .875rem
        }

        #quotationsTable th,
        #quotationsTable td {
            padding: .5rem .75rem
        }

        .location-max {
            max-width: 170px
        }
    }
</style>

<script>
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentQuotationId = null;
    let currentQuotationData = null;
    let isEditMode = false;
    let editedItems = [];
    let itemsToRemove = [];
    let walletBalance = 0;

    function buildCSelect(select) { if (select.closest('.cselect-wrapper') && select._cselect) { refreshCSelect(select); return } const w = select.closest('.cselect-wrapper') || (() => { const x = document.createElement('div'); x.className = 'cselect-wrapper'; select.parentNode.insertBefore(x, select); x.appendChild(select); return x })(); const shell = document.createElement('div'); shell.className = 'cselect'; shell.setAttribute('aria-disabled', select.disabled ? 'true' : 'false'); const btn = document.createElement('button'); btn.type = 'button'; btn.className = 'cselect-btn'; btn.setAttribute('aria-haspopup', 'listbox'); btn.setAttribute('aria-expanded', 'false'); const label = document.createElement('span'); label.className = 'truncate'; label.textContent = select.options[select.selectedIndex]?.text || (select.options[0]?.text || 'Select'); const icon = document.createElement('span'); icon.className = 'cselect-icon'; icon.innerHTML = '<i class="fas fa-chevron-down text-xs opacity-70"></i>'; btn.appendChild(label); btn.appendChild(icon); const menu = document.createElement('div'); menu.className = 'cselect-menu'; menu.setAttribute('role', 'listbox'); function populate() { menu.innerHTML = '';[...select.options].forEach(o => { const it = document.createElement('div'); it.className = 'cselect-option'; it.setAttribute('role', 'option'); it.setAttribute('data-value', o.value); it.setAttribute('aria-selected', o.selected ? 'true' : 'false'); it.textContent = o.text; it.addEventListener('click', () => { select.value = o.value; label.textContent = o.text;[...menu.children].forEach(ch => ch.setAttribute('aria-selected', 'false')); it.setAttribute('aria-selected', 'true'); close(); select.dispatchEvent(new Event('change', { bubbles: true })) }); menu.appendChild(it) }) } function open() { if (select.disabled) return; shell.classList.add('open'); btn.setAttribute('aria-expanded', 'true'); document.addEventListener('click', doc); document.addEventListener('keydown', key) } function close() { shell.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); document.removeEventListener('click', doc); document.removeEventListener('keydown', key) } function doc(e) { if (!shell.contains(e.target)) close() } function key(e) { if (e.key === 'Escape') { close(); btn.focus() } } btn.addEventListener('click', () => { shell.classList.contains('open') ? close() : open() }); shell.appendChild(btn); shell.appendChild(menu); w.appendChild(shell); const obs = new MutationObserver(() => { shell.setAttribute('aria-disabled', select.disabled ? 'true' : 'false') }); obs.observe(select, { attributes: true, attributeFilter: ['disabled'] }); populate(); select._cselect = { shell, btn, menu, labelSpan: label, populateMenu: populate } }
    function refreshCSelect(select) { if (!select._cselect) return buildCSelect(select); const { shell, labelSpan, populateMenu } = select._cselect; labelSpan.textContent = select.options[select.selectedIndex]?.text || (select.options[0]?.text || 'Select'); populateMenu(); shell.setAttribute('aria-disabled', select.disabled ? 'true' : 'false') }
    function initCustomSelects(scope = document) { scope.querySelectorAll('select[data-cselect]').forEach(buildCSelect) }

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        loadQuotations();
        checkWalletBalance();
        initCustomSelects(document);
    });

    function formatCurrency(amount) { return new Intl.NumberFormat('en-UG', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount || 0) }

    function checkWalletBalance() { fetch('fetch/manageQuotations.php?action=getWalletBalance').then(r => r.json()).then(d => { if (d.success) { walletBalance = parseFloat(d.balance || 0) } }).catch(() => { }) }

    function showSuccessModal(m) { document.getElementById('successMessage').textContent = m; showModal('successModal') }
    function closeSuccessModal() { hideModal('successModal') }
    function showErrorModal(m) { document.getElementById('errorMessage').textContent = m; showModal('errorModal') }
    function closeErrorModal() { hideModal('errorModal') }
    function showModal(id) { const m = document.getElementById(id); m.classList.remove('hidden'); setTimeout(() => { m.classList.remove('opacity-0'); const c = m.querySelector('.transform'); if (c) { c.classList.remove('scale-95'); c.classList.add('scale-100') } }, 10) }
    function hideModal(id) { const m = document.getElementById(id); m.classList.add('opacity-0'); const c = m.querySelector('.transform'); if (c) { c.classList.remove('scale-100'); c.classList.add('scale-95') } setTimeout(() => { m.classList.add('hidden') }, 300) }

    function loadQuotations() {
        const params = new URLSearchParams({ action: 'getQuotations', search_term: document.getElementById('searchFilter').value, status_filter: document.getElementById('statusFilter').value, page: currentPage, limit: itemsPerPage });
        fetch(`fetch/manageQuotations.php?${params}`).then(r => r.json()).then(d => {
            if (d.success) { renderQuotationsTable(d.quotationData.data, d.quotationData.total, d.quotationData.page); updateStatistics(d.stats) }
            else { showError('Failed to load quotations') }
        }).catch(() => { showError('An error occurred while loading quotations') })
    }

    function showError(message) {
        document.getElementById('quotationsBody').innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-red-500 dark:text-red-300">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <div>${message}</div>
                </td>
            </tr>`;
        document.getElementById('quotationsListMobile').innerHTML = `<div class="px-4 py-8 text-center text-red-500 dark:text-red-300"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><div>${message}</div></div>`;
    }

    function updateStatistics(s) {
        document.getElementById('newRequests').textContent = parseInt(s.new || 0).toLocaleString();
        document.getElementById('processingRequests').textContent = parseInt(s.processing || 0).toLocaleString();
        document.getElementById('processedRequests').textContent = parseInt(s.processed || 0).toLocaleString();
        document.getElementById('cancelledRequests').textContent = parseInt(s.cancelled || 0).toLocaleString();
        document.getElementById('paidRequests').textContent = parseInt(s.paid || 0).toLocaleString();
    }

    function renderQuotationsTable(data, total, page) {
        const tbody = document.getElementById('quotationsBody');
        const mlist = document.getElementById('quotationsListMobile');
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-white/70"><i class="fas fa-inbox text-2xl mb-2"></i><div>No quotations found</div></td></tr>`;
            mlist.innerHTML = `<div class="px-4 py-8 text-center text-gray-500 dark:text-white/70"><i class="fas fa-inbox text-2xl mb-2"></i><div>No quotations found</div></div>`;
            updatePagination(0, 1); return;
        }
        tbody.innerHTML = data.map(item => {
            const badge = getStatusBadge(item.status);
            const totalAmount = calculateTotalAmount(item);
            return `
            <tr class="hover:bg-user-accent/30 dark:hover:bg-white/5 transition-colors cursor-pointer" onclick="viewQuotationDetails('${item.RFQ_ID}')">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-medium text-secondary dark:text-white text-sm location-max" title="${item.site_location}">${item.site_location || ''}</div>
                    <div class="text-xs text-gray-text dark:text-white/70 location-max" title="${item.coordinates || ''}">${item.coordinates || ''}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">${item.items_count}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><span class="text-sm font-medium text-secondary dark:text-white">UGX ${formatCurrency(item.fee_charged)}</span></td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><span class="text-sm font-medium text-secondary dark:text-white">UGX ${formatCurrency(item.transport)}</span></td>
                <td class="px-4 py-3 text-center whitespace-nowrap"><span class="text-sm font-bold text-green-600 dark:text-green-300">UGX ${formatCurrency(totalAmount)}</span></td>
                <td class="px-4 py-3 text-center whitespace-nowrap">${badge}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="text-xs text-secondary dark:text-white">${formatDate(item.created_at)}</div>
                    <div class="text-xs text-gray-text dark:text-white/70">${formatTime(item.created_at)}</div>
                </td>
            </tr>`;
        }).join('');

        mlist.innerHTML = data.map(item => {
            const totalAmount = calculateTotalAmount(item);
            return `
            <button class="w-full text-left px-4 py-3 active:scale-[.99] transition-transform" onclick="viewQuotationDetails('${item.RFQ_ID}')">
                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs text-gray-500 dark:text-white/60 mb-1">${formatDate(item.created_at)} • ${formatTime(item.created_at)}</div>
                            <div class="font-semibold text-secondary dark:text-white location-max" title="${item.site_location}">${item.site_location || ''}</div>
                            <div class="text-xs text-gray-500 dark:text-white/60 location-max" title="${item.coordinates || ''}">${item.coordinates || ''}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] text-gray-500 dark:text-white/60">Total</div>
                            <div class="text-base font-bold text-green-600 dark:text-green-300">UGX ${formatCurrency(totalAmount)}</div>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span class="opacity-70">Items:</span> <span class="font-medium">${item.items_count}</span></div>
                        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span class="opacity-70">Fee:</span> <span class="font-medium">UGX ${formatCurrency(item.fee_charged)}</span></div>
                        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-2 py-1"><span class="opacity-70">Delivery:</span> <span class="font-medium">UGX ${formatCurrency(item.transport)}</span></div>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">${(item.status || '').toUpperCase()}</span>
                        <span class="text-user-primary text-sm font-medium">View <i class="fas fa-chevron-right ml-1 text-xs"></i></span>
                    </div>
                </div>
            </button>`;
        }).join('');

        updatePagination(total, page);
    }

    function calculateTotalAmount(item) { const t = parseFloat(item.items_total || 0); const d = parseFloat(item.transport || 0); return t + d }

    function getStatusBadge(status) {
        const s = (status || '').toLowerCase();
        if (s === 'new') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300">New</span>';
        if (s === 'processing') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300">Processing</span>';
        if (s === 'processed') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300">Processed</span>';
        if (s === 'cancelled') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-300">Cancelled</span>';
        if (s === 'paid') return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-300">Paid</span>';
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white/80">Unknown</span>';
    }

    function formatDate(d) { const date = new Date(d); const m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const day = date.getDate(); const suf = (day === 1 || day === 21 || day === 31) ? 'st' : (day === 2 || day === 22) ? 'nd' : (day === 3 || day === 23) ? 'rd' : 'th'; return `${m[date.getMonth()]} ${day}${suf}, ${date.getFullYear()}` }
    function formatTime(d) { const date = new Date(d); let h = date.getHours(); const min = date.getMinutes(); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; const mm = min < 10 ? '0' + min : min; return `${h}:${mm}${ap}` }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const start = (page - 1) * itemsPerPage;
        const end = Math.min(start + itemsPerPage, total);
        document.getElementById('showingCount').textContent = total > 0 ? `${start + 1}-${end}` : '0';
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;
        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;
    }

    function viewQuotationDetails(id) {
        currentQuotationId = id; isEditMode = false; editedItems = []; itemsToRemove = [];
        showModal('quotationModal');
        document.getElementById('quotationContent').innerHTML = `<div class="flex items-center justify-center py-12"><div class="text-center"><i class="fas fa-spinner fa-spin text-3xl text-user-primary mb-4"></i><p class="text-gray-text dark:text-white/70">Fetching quotation details...</p></div></div>`;
        fetch(`fetch/manageQuotations.php?action=getRFQDetails&id=${id}`).then(r => r.json()).then(d => {
            if (d.success) { currentQuotationData = d; showQuotationModal(d.quotation, d.items) }
            else { document.getElementById('quotationContent').innerHTML = `<div class="flex items-center justify-center py-12"><div class="text-center text-red-500 dark:text-red-300"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>Failed to load quotation details</p></div></div>` }
        }).catch(() => { document.getElementById('quotationContent').innerHTML = `<div class="flex items-center justify-center py-12"><div class="text-center text-red-500 dark:text-red-300"><i class="fas fa-exclamation-triangle text-3xl mb-4"></i><p>An error occurred while loading details</p></div></div>` })
    }

    function createGoogleMapsLink(c) { if (!c) return '#'; const m = c.match(/-?\d+\.?\d*/g); if (m && m.length >= 2) return `https://www.google.com/maps?q=${m[0]},${m[1]}`; return '#' }

    function showQuotationModal(q, items) {
        const content = document.getElementById('quotationContent');
        const status = (q.status || '').toLowerCase();
        const isModified = parseInt(q.modified) === 1;
        const canEdit = status === 'processed' && !isModified;
        const canCancel = ['new', 'processing', 'processed'].includes(status);
        const canPay = status === 'processed';
        let itemsTotal = 0; items.forEach(it => { if (it.unit_price && it.unit_price > 0) { itemsTotal += parseFloat(it.unit_price) * parseInt(it.quantity) } });
        const transport = parseFloat(q.transport || 0);
        const feeCharged = parseFloat(q.fee_charged || 0);
        const grandTotal = itemsTotal + transport;
        const coordinatesLink = q.coordinates ? `<a href="${createGoogleMapsLink(q.coordinates)}" target="_blank" class="text-user-primary hover:text-user-primary/80 underline">${q.coordinates}</a>` : '';
        content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-user-accent/50 dark:bg-white/5 rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-white/10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <h4 class="font-medium text-secondary dark:text-white mb-2">Request Information</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-text dark:text-white/70">Status:</span> ${getStatusBadge(q.status)}</div>
                                <div><span class="text-gray-text dark:text-white/70">Fee Charged:</span> <span class="font-medium text-secondary dark:text-white">UGX ${formatCurrency(feeCharged)}</span></div>
                                ${isModified ? '<div class="text-xs text-orange-600 dark:text-orange-300 font-medium">This quotation has been modified</div>' : ''}
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-secondary dark:text-white mb-2">Location Details</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-text dark:text-white/70">Site Location:</span></div>
                                <div class="font-medium text-secondary dark:text-white">${q.site_location}</div>
                                ${coordinatesLink ? `<div class="text-gray-500 dark:text-white/60">${coordinatesLink}</div>` : ''}
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-secondary dark:text-white mb-2">Dates</h4>
                            <div class="space-y-1 text-sm">
                                <div><span class="text-gray-text dark:text-white/70">Created:</span> <span class="font-medium text-secondary dark:text-white">${formatDate(q.created_at)} ${formatTime(q.created_at)}</span></div>
                                <div><span class="text-gray-text dark:text-white/70">Updated:</span> <span class="font-medium text-secondary dark:text-white">${formatDate(q.updated_at)} ${formatTime(q.updated_at)}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <h4 class="font-medium text-secondary dark:text-white">Requested Items</h4>
                        ${canEdit && !isEditMode ? `<button onclick="showEditConfirmModal()" class="px-3 py-2 bg-user-primary text-white text-sm rounded-xl hover:bg-user-primary/90 flex items-center gap-2 justify-center"><i class="fas fa-edit"></i><span>Edit Items</span></button>` : ''}
                        ${isEditMode ? `<div class="flex gap-2"><button onclick="showSaveConfirmModal()" class="px-3 py-2 bg-green-600 text-white text-sm rounded-xl hover:bg-green-700 flex items-center gap-2 justify-center"><i class="fas fa-save"></i><span>Save Changes</span></button><button onclick="exitEditMode()" class="px-3 py-2 bg-gray-600 text-white text-sm rounded-xl hover:bg-gray-700 flex items-center gap-2 justify-center"><i class="fas fa-times"></i><span>Cancel</span></button></div>` : ''}
                    </div>
                    <div class="overflow-x-auto border border-gray-200 dark:border-white/10 rounded-xl">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-white/70">Brand/Material</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-white/70">Size/Specification</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">Quantity</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">Unit Price (UGX)</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">Total (UGX)</th>
                                    ${isEditMode ? '<th class="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-white/70">Actions</th>' : ''}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10" id="itemsTableBody">
                                ${renderItemsTable(items)}
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-user-accent/50 dark:bg-white/5 rounded-xl p-4 sm:p-5 border border-gray-200 dark:border-white/10">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-secondary dark:text-white mb-3">Delivery Charge</h4>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-text dark:text-white/70">Delivery Charge:</span>
                                <span class="text-sm font-medium text-secondary dark:text-white">${transport > 0 ? 'UGX ' + formatCurrency(transport) : '<span class="text-gray-400 dark:text-white/50 italic">Not set yet</span>'}</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-secondary dark:text-white mb-3">Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-text dark:text-white/70">Items Subtotal:</span>
                                    <span class="font-medium text-secondary dark:text-white" id="itemsSubtotal">${itemsTotal > 0 ? 'UGX ' + formatCurrency(itemsTotal) : '<span class="text-gray-400 dark:text-white/50 italic">Not set yet</span>'}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-text dark:text-white/70">Delivery Charge:</span>
                                    <span class="font-medium text-secondary dark:text-white">${transport > 0 ? 'UGX ' + formatCurrency(transport) : '<span class="text-gray-400 dark:text-white/50 italic">Not set yet</span>'}</span>
                                </div>
                                <div class="flex justify-between border-t dark:border-white/10 pt-2 text-lg font-bold">
                                    <span class="text-secondary dark:text-white">Total Amount:</span>
                                    <span class="text-green-600 dark:text-green-300" id="grandTotal">${grandTotal > 0 ? 'UGX ' + formatCurrency(grandTotal) : '<span class="text-gray-400 dark:text-white/50 italic">Pending pricing</span>'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                    <div class="flex gap-3">
                        ${canCancel ? `<button onclick="showCancelConfirmModal()" class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 flex items-center gap-2"><i class="fas fa-times"></i><span>Cancel Quote</span></button>` : ''}
                        ${canPay && grandTotal > 0 ? `<button onclick="showPaymentConfirmModal(${grandTotal})" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 flex items-center gap-2"><i class="fas fa-credit-card"></i><span>Make Payment</span></button>` : ''}
                    </div>
                    <button onclick="closeQuotationModal()" class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white rounded-xl hover:bg-gray-100 dark:hover:bg-white/10">Close</button>
                </div>
            </div>`;
    }

    function renderItemsTable(items) {
        return items.filter(it => !itemsToRemove.includes(it.RFQD_ID)).map(item => {
            const unitPrice = parseFloat(item.unit_price || 0);
            const quantity = parseInt(item.quantity);
            const total = unitPrice * quantity;
            const remaining = items.filter(i => !itemsToRemove.includes(i.RFQD_ID));
            return `
                <tr data-item-id="${item.RFQD_ID}">
                    <td class="px-4 py-3 text-sm text-secondary dark:text-white">${item.brand_name}</td>
                    <td class="px-4 py-3 text-sm text-secondary dark:text-white">${item.size}</td>
                    <td class="px-4 py-3 text-center">
                        ${isEditMode ? `<input type="number" value="${quantity}" min="1" class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-secondary dark:text-white rounded-xl text-center quantity-input" data-item-id="${item.RFQD_ID}" onchange="updateQuantityInMemory('${item.RFQD_ID}', this.value)">` : `<span class="text-sm font-medium text-secondary dark:text-white">${quantity}</span>`}
                    </td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-medium text-secondary dark:text-white">${unitPrice > 0 ? formatCurrency(unitPrice) : '<span class="text-gray-400 dark:text-white/50 italic">Not set yet</span>'}</span></td>
                    <td class="px-4 py-3 text-sm text-center font-medium item-total text-secondary dark:text-white" data-item-id="${item.RFQD_ID}">${formatCurrency(total)}</td>
                    ${isEditMode ? `<td class="px-4 py-3 text-center">${remaining.length > 1 ? `<button onclick="markItemForRemoval('${item.RFQD_ID}')" class="text-red-600 dark:text-red-300 hover:text-red-800 dark:hover:text-red-400 p-1 rounded" title="Remove item"><i class="fas fa-trash text-sm"></i></button>` : `<span class="text-gray-400 dark:text-white/50 text-xs">Last item</span>`}</td>` : ''}
                </tr>`;
        }).join('');
    }

    function updateQuantityInMemory(id, q) { const qty = parseInt(q) || 1; const i = editedItems.findIndex(it => it.id === id); if (i >= 0) { editedItems[i].quantity = qty } else { editedItems.push({ id, quantity: qty }) } updateItemTotal(id, qty); updateSummaryTotals() }

    function updateItemTotal(id, q) { const it = currentQuotationData.items.find(i => i.RFQD_ID === id); if (it) { const total = (parseFloat(it.unit_price || 0)) * q; const cell = document.querySelector(`.item-total[data-item-id="${id}"]`); if (cell) cell.textContent = formatCurrency(total) } }

    function updateSummaryTotals() {
        let itemsTotal = 0;
        currentQuotationData.items.forEach(it => { if (!itemsToRemove.includes(it.RFQD_ID)) { const e = editedItems.find(x => x.id === it.RFQD_ID); const qty = e ? e.quantity : parseInt(it.quantity); const up = parseFloat(it.unit_price || 0); itemsTotal += up * qty } });
        const transport = parseFloat(currentQuotationData.quotation.transport || 0);
        const grandTotal = itemsTotal + transport;
        document.getElementById('itemsSubtotal').innerHTML = itemsTotal > 0 ? 'UGX ' + formatCurrency(itemsTotal) : '<span class="text-gray-400 dark:text-white/50 italic">Not set yet</span>';
        document.getElementById('grandTotal').innerHTML = grandTotal > 0 ? 'UGX ' + formatCurrency(grandTotal) : '<span class="text-gray-400 dark:text-white/50 italic">Pending pricing</span>';
    }

    function markItemForRemoval(id) {
        const remaining = currentQuotationData.items.filter(it => !itemsToRemove.includes(it.RFQD_ID));
        if (remaining.length <= 1) { showErrorModal('Cannot remove the last item. At least one item must remain.'); return }
        itemsToRemove.push(id);
        const idx = editedItems.findIndex(it => it.id === id); if (idx >= 0) editedItems.splice(idx, 1);
        document.getElementById('itemsTableBody').innerHTML = renderItemsTable(currentQuotationData.items);
        updateSummaryTotals();
    }

    function showSaveConfirmModal() { if (editedItems.length === 0 && itemsToRemove.length === 0) { showErrorModal('No changes to save.'); return } showModal('saveConfirmModal') }
    function closeSaveConfirmModal() { hideModal('saveConfirmModal') }

    function confirmSave() {
        closeSaveConfirmModal();
        const payload = { action: 'updateQuotation', rfq_id: currentQuotationId, items: editedItems, items_to_remove: itemsToRemove };
        fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }).then(r => r.json()).then(d => {
            if (d.success) { showSuccessModal('Changes saved successfully. Status changed to Processing.'); setTimeout(() => { closeSuccessModal(); closeQuotationModal(); loadQuotations() }, 2000) }
            else { showErrorModal('Failed to save changes: ' + (d.error || 'Unknown error')) }
        }).catch(() => { showErrorModal('An error occurred while saving changes') })
    }

    function showEditConfirmModal() { if (!currentQuotationData) { showErrorModal('No quotation data available'); return } showModal('editConfirmModal') }
    function closeEditConfirmModal() { hideModal('editConfirmModal') }
    function confirmEdit() { closeEditConfirmModal(); if (!currentQuotationData) { showErrorModal('No quotation data available'); return } isEditMode = true; editedItems = []; itemsToRemove = []; showQuotationModal(currentQuotationData.quotation, currentQuotationData.items) }
    function exitEditMode() { isEditMode = false; editedItems = []; itemsToRemove = []; showQuotationModal(currentQuotationData.quotation, currentQuotationData.items) }

    function showPaymentConfirmModal(amount) {
        checkWalletBalance();
        setTimeout(() => {
            const insuff = walletBalance < amount; const need = insuff ? amount - walletBalance : 0;
            document.getElementById('paymentDetails').innerHTML = `
                <div class="rounded-xl p-4 border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 space-y-2">
                    <div class="flex justify-between items-center"><span class="text-gray-600 dark:text-white/70">Quote Payment Amount:</span><span class="font-medium text-secondary dark:text-white">UGX ${formatCurrency(amount)}</span></div>
                    <div class="flex justify-between items-center"><span class="text-gray-600 dark:text-white/70">Current Wallet Balance:</span><span class="font-medium ${insuff ? 'text-red-600 dark:text-red-300' : 'text-green-600 dark:text-green-300'}">UGX ${formatCurrency(walletBalance)}</span></div>
                    ${insuff ? `<div class="flex justify-between items-center border-t dark:border-white/10 pt-2"><span class="text-red-600 dark:text-red-300 font-medium">Amount Needed:</span><span class="font-medium text-red-600 dark:text-red-300">UGX ${formatCurrency(need)}</span></div><div class="text-center text-red-600 dark:text-red-300 text-sm mt-3"><i class="fas fa-exclamation-triangle mr-1"></i> Please top up your wallet to continue</div>` : `<div class="flex justify-between items-center border-t dark:border-white/10 pt-2"><span class="text-gray-600 dark:text-white/70">Remaining Balance:</span><span class="font-medium text-green-600 dark:text-green-300">UGX ${formatCurrency(walletBalance - amount)}</span></div>`}
                </div>`;
            const btn = document.getElementById('confirmPaymentBtn');
            if (insuff) { btn.innerHTML = '<i class="fas fa-wallet mr-2"></i> Top Up Wallet'; btn.className = 'px-4 py-2 bg-orange-600 text-white rounded-xl hover:bg-orange-700'; btn.onclick = function () { window.open(`${BASE_URL}account/zzimba-credit`, '_blank') } }
            else { btn.innerHTML = '<i class="fas fa-check mr-2"></i> Confirm Payment'; btn.className = 'px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700'; btn.onclick = function () { confirmPayment(amount) } }
            showModal('paymentConfirmModal');
        }, 100)
    }
    function closePaymentConfirmModal() { hideModal('paymentConfirmModal') }

    function confirmPayment(amount) {
        closePaymentConfirmModal();
        const btn = document.getElementById('confirmPaymentBtn'); const orig = btn.innerHTML; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
        fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'processQuotePayment', quotation_id: currentQuotationId, amount }) }).then(r => r.json()).then(d => {
            if (d.success) { showSuccessModal(`Payment successful! UGX ${formatCurrency(d.amount_paid)} has been deducted from your wallet. Remaining balance: UGX ${formatCurrency(d.remaining_balance)}`); setTimeout(() => { closeSuccessModal(); closeQuotationModal(); loadQuotations(); checkWalletBalance() }, 3000) }
            else { if (d.message === 'Insufficient wallet balance') { showErrorModal(`Insufficient wallet balance. You need UGX ${formatCurrency(d.required - d.balance)} more to complete this payment.`) } else { showErrorModal('Payment failed: ' + (d.message || 'Unknown error')) } }
            btn.disabled = false; btn.innerHTML = orig;
        }).catch(() => { showErrorModal('An error occurred while processing payment'); btn.disabled = false; btn.innerHTML = orig })
    }

    function showCancelConfirmModal() { showModal('cancelConfirmModal') }
    function closeCancelConfirmModal() { hideModal('cancelConfirmModal') }
    function confirmCancel() {
        closeCancelConfirmModal();
        fetch('fetch/manageQuotations.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'cancelQuotation', rfq_id: currentQuotationId }) }).then(r => r.json()).then(d => {
            if (d.success) { showSuccessModal('Quotation cancelled successfully.'); setTimeout(() => { closeSuccessModal(); closeQuotationModal(); loadQuotations() }, 2000) }
            else { showErrorModal('Failed to cancel quotation: ' + (d.error || 'Unknown error')) }
        }).catch(() => { showErrorModal('An error occurred while cancelling quotation') })
    }

    function closeQuotationModal() { hideModal('quotationModal'); currentQuotationId = null; currentQuotationData = null; isEditMode = false; editedItems = []; itemsToRemove = [] }

    function generatePDF() {
        if (!currentQuotationData) return;
        const { quotation, items } = currentQuotationData;
        const pdfContainer = document.getElementById('pdfContent');
        let itemsTotal = 0; items.forEach(it => { if (it.unit_price && it.unit_price > 0) { itemsTotal += parseFloat(it.unit_price) * parseInt(it.quantity) } });
        const transport = parseFloat(quotation.transport || 0);
        const grandTotal = itemsTotal + transport;
        pdfContainer.innerHTML = `
            <div style="font-family: Arial, sans-serif; color: #333; line-height: 1.4; font-size: 12px;">
                <div style="text-align: center; margin-bottom: 20px; page-break-inside: avoid;">
                    <h1 style="font-size: 24px; margin: 0 0 10px 0; color: #D92B13;">My Quotation Report</h1>
                    <p style="font-size: 14px; color: #666; margin: 0;">Generated on ${formatDateReadable(new Date())} at ${formatTimeReadable(new Date())}</p>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px; page-break-inside: avoid;">
                    <div style="width: 30%; min-width: 200px;">
                        <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Request Information</h3>
                        <div style="margin-bottom: 8px;"><strong>Status:</strong> ${quotation.status}</div>
                        <div style="margin-bottom: 8px;"><strong>Fee Charged:</strong> UGX ${formatCurrency(quotation.fee_charged)}</div>
                    </div>
                    <div style="width: 30%; min-width: 250px;">
                        <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Location Details</h3>
                        <div style="margin-bottom: 8px;"><strong>Site Location:</strong></div>
                        <div style="margin-bottom: 8px; word-wrap: break-word;">${quotation.site_location}</div>
                        ${quotation.coordinates ? `<div style="margin-bottom: 8px; color: #666; font-size: 11px;">${quotation.coordinates}</div>` : ''}
                    </div>
                    <div style="width: 30%; min-width: 200px;">
                        <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Dates</h3>
                        <div style="margin-bottom: 8px;"><strong>Created:</strong> ${formatDateReadable(new Date(quotation.created_at))} ${formatTimeReadable(new Date(quotation.created_at))}</div>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; margin: 0 0 15px 0; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; color: #374151;">Requested Items</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto;">
                        <thead>
                            <tr style="background-color: #f3f4f6;">
                                <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Brand/Material</th>
                                <th style="padding: 10px 6px; text-align: left; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Size/Specification</th>
                                <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Quantity</th>
                                <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Unit Price (UGX)</th>
                                <th style="padding: 10px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600; font-size: 12px;">Total (UGX)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map(it => { const up = parseFloat(it.unit_price || 0); const q = parseInt(it.quantity); const t = up * q; return `<tr style="page-break-inside: avoid;"><td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${it.brand_name}</td><td style="padding: 8px 6px; border: 1px solid #d1d5db; word-wrap: break-word; max-width: 150px;">${it.size}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db;">${q}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 500;">${formatCurrency(up)}</td><td style="padding: 8px 6px; text-align: center; border: 1px solid #d1d5db; font-weight: 600;">${formatCurrency(t)}</td></tr>` }).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: flex-end; page-break-inside: avoid;">
                    <div style="width: 300px; border: 2px solid #d1d5db; padding: 15px; background-color: #f9fafb;">
                        <h3 style="font-size: 16px; margin: 0 0 15px 0; color: #374151;">Summary</h3>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 6px;">
                            <span style="font-size: 14px;">Items Subtotal:</span>
                            <span style="font-size: 14px; font-weight: 600;">UGX ${formatCurrency(itemsTotal)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px;">
                            <span style="font-size: 14px;">Delivery Charge:</span>
                            <span style="font-size: 14px; font-weight: 600;">UGX ${formatCurrency(transport)}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid #374151; margin-top: 12px;">
                            <span style="font-size: 16px; font-weight: 700;">Total Amount:</span>
                            <span style="font-size: 16px; font-weight: 700; color: #047857;">UGX ${formatCurrency(grandTotal)}</span>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 30px; text-align: center; color: #666; font-size: 11px; page-break-inside: avoid;">
                    <p style="margin: 0;">This is a computer-generated document. No signature is required.</p>
                </div>
            </div>`;
        function formatDateReadable(date) { const m = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']; const d = date.getDate(); const s = (d === 1 || d === 21 || d === 31) ? 'st' : (d === 2 || d === 22) ? 'nd' : (d === 3 || d === 23) ? 'rd' : 'th'; return `${m[date.getMonth()]} ${d}${s}, ${date.getFullYear()}` }
        function formatTimeReadable(date) { let h = date.getHours(); const mi = date.getMinutes(); const s = date.getSeconds(); const ap = h >= 12 ? 'PM' : 'AM'; h = h % 12; h = h ? h : 12; const mm = mi < 10 ? '0' + mi : mi; const ss = s < 10 ? '0' + s : s; return `${h}:${mm}:${ss} ${ap}` }
        setTimeout(() => { const { jsPDF } = window.jspdf; html2canvas(pdfContainer, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#ffffff', width: pdfContainer.scrollWidth, height: pdfContainer.scrollHeight, scrollX: 0, scrollY: 0 }).then(canvas => { const img = canvas.toDataURL('image/png'); const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' }); const pw = pdf.internal.pageSize.getWidth(); const ph = pdf.internal.pageSize.getHeight(); const prop = pdf.getImageProperties(img); const ih = (prop.height * pw) / prop.width; let left = ih; let pos = 0; pdf.addImage(img, 'PNG', 0, pos, pw, ih); left -= ph; while (left >= 0) { pos = left - ih; pdf.addPage(); pdf.addImage(img, 'PNG', 0, pos, pw, ih); left -= ph } const name = `My_Quotation_${new Date().toISOString().slice(0, 10)}.pdf`; pdf.save(name) }).catch(() => { showErrorModal('Failed to generate PDF. Please try again.') }) }, 300)
    }

    function setupEventListeners() {
        document.getElementById('searchFilter').addEventListener('input', debounce(() => { currentPage = 1; loadQuotations() }, 400));
        document.getElementById('statusFilter').addEventListener('change', () => { currentPage = 1; loadQuotations() });
        document.getElementById('clearFilters').addEventListener('click', () => { document.getElementById('searchFilter').value = ''; document.getElementById('statusFilter').value = 'all'; refreshCSelect(document.getElementById('statusFilter')); currentPage = 1; loadQuotations() });
        document.getElementById('prevPage').addEventListener('click', () => { if (currentPage > 1) { currentPage--; loadQuotations() } });
        document.getElementById('nextPage').addEventListener('click', () => { currentPage++; loadQuotations() });
        const r = document.getElementById('refreshBtn'); if (r) r.addEventListener('click', refreshData);
        const rm = document.getElementById('refreshBtnMobile'); if (rm) rm.addEventListener('click', refreshData);
        document.getElementById('printQuotationBtn').addEventListener('click', generatePDF);
        ['quotationModal', 'editConfirmModal', 'saveConfirmModal', 'paymentConfirmModal', 'cancelConfirmModal', 'successModal', 'errorModal'].forEach(id => { const el = document.getElementById(id); if (!el) return; el.addEventListener('click', function (e) { if (e.target === this) { if (id === 'quotationModal') closeQuotationModal(); if (id === 'editConfirmModal') closeEditConfirmModal(); if (id === 'saveConfirmModal') closeSaveConfirmModal(); if (id === 'paymentConfirmModal') closePaymentConfirmModal(); if (id === 'cancelConfirmModal') closeCancelConfirmModal(); if (id === 'successModal') closeSuccessModal(); if (id === 'errorModal') closeErrorModal(); } }) });
    }

    function debounce(fn, wait) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), wait) } }

    function refreshData() {
        const btn = document.getElementById('refreshBtn') || document.getElementById('refreshBtnMobile'); if (!btn) return;
        const icon = btn.querySelector('i'); icon.classList.add('fa-spin'); btn.disabled = true; loadQuotations(); setTimeout(() => { icon.classList.remove('fa-spin'); btn.disabled = false }, 800)
    }
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>