<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'SMS Center';
$activeNav = 'sms-center';
ob_start();

function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">SMS Credit</p>
                            <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="sms-credit-count">
                                Loading...</p>
                            <p class="text-sm font-medium text-blue-700 whitespace-nowrap">Messages Available</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sms text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Sent Today</p>
                            <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="sent-today-count">
                                Loading...</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="sent-today-cost">Sh.
                                0.00</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paper-plane text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Scheduled</p>
                            <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="scheduled-count">
                                Loading...</p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap">Pending Messages</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">SMS Rate</p>
                            <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="sms-rate-display">
                                Loading...</p>
                            <p class="text-sm font-medium text-orange-700 whitespace-nowrap">Per Message</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tag text-orange-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="border-b border-gray-200">
                <div class="hidden md:block">
                    <nav class="flex space-x-8 px-6 overflow-x-auto pb-2" aria-label="Tabs">
                        <button id="send-tab"
                            class="tab-button active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-primary text-primary"
                            onclick="switchTab('send')">
                            <i class="fas fa-paper-plane mr-2"></i>Send SMS
                        </button>
                        <button id="history-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('history')">
                            <i class="fas fa-history mr-2"></i>SMS History
                        </button>
                        <button id="templates-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('templates')">
                            <i class="fas fa-file-alt mr-2"></i>Templates
                        </button>
                        <button id="topup-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('topup')">
                            <i class="fas fa-plus-circle mr-2"></i>Top Up Credit
                        </button>
                    </nav>
                </div>

                <div class="md:hidden px-6 py-4">
                    <div class="relative">
                        <button id="mobile-tab-toggle"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-paper-plane text-primary"></i>
                                <span id="mobile-tab-label" class="font-medium text-gray-900">Send SMS</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                                id="mobile-tab-chevron"></i>
                        </button>

                        <div id="mobile-tab-dropdown"
                            class="hidden absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50">
                            <div class="py-2">
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="send">
                                    <i class="fas fa-paper-plane text-blue-600"></i>
                                    <span>Send SMS</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="history">
                                    <i class="fas fa-history text-green-600"></i>
                                    <span>SMS History</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="templates">
                                    <i class="fas fa-file-alt text-purple-600"></i>
                                    <span>Templates</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="topup">
                                    <i class="fas fa-plus-circle text-orange-600"></i>
                                    <span>Top Up Credit</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="credit">
                                    <i class="fas fa-wallet text-indigo-600"></i>
                                    <span>Credit Status</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content">
            <!-- Send SMS Tab -->
            <div id="send-content" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-paper-plane text-blue-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Send SMS</h3>
                        </div>

                        <form id="sms-form" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Send Type</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label
                                        class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="sendType" value="single" class="sr-only" checked
                                            onchange="toggleSendType()">
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">Single SMS</div>
                                    </label>
                                    <label
                                        class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="sendType" value="bulk" class="sr-only"
                                            onchange="toggleSendType()">
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">Bulk SMS</div>
                                    </label>
                                </div>
                            </div>

                            <div id="single-recipient">
                                <label for="recipient" class="block text-sm font-semibold text-gray-700 mb-2">Recipient
                                    Phone Number</label>
                                <input type="tel" id="recipient"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    placeholder="0700123456" autocomplete="off">
                            </div>

                            <div id="bulk-recipients" class="hidden">
                                <label for="bulk-number-input"
                                    class="block text-sm font-semibold text-gray-700 mb-2">Add Recipients</label>
                                <div class="flex gap-2">
                                    <input type="tel" id="bulk-number-input"
                                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                        placeholder="0700123456" autocomplete="off">
                                    <button type="button" id="add-number-btn"
                                        class="md:hidden px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="recipient-tags" class="mt-3 flex flex-wrap gap-2"></div>
                                <p class="text-xs text-gray-500 mt-2">Enter 10-digit phone numbers (e.g., 0700123456)
                                </p>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="message"
                                        class="block text-sm font-semibold text-gray-700">Message</label>
                                    <span class="text-xs text-gray-500" id="char-count">0/160 characters</span>
                                </div>
                                <textarea id="message" rows="4"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    placeholder="Type your message here..." oninput="updateCharCount()"></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-xs text-gray-500" id="sms-parts">1 SMS part</p>
                                    <button type="button" onclick="showTemplateSelector()"
                                        class="text-xs text-primary hover:text-primary/80 font-medium">Use
                                        Template</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Send Options</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label
                                        class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="sendOption" value="now" class="sr-only" checked
                                            onchange="toggleSchedule()">
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">Send Now</div>
                                    </label>
                                    <label
                                        class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="sendOption" value="schedule" class="sr-only"
                                            onchange="toggleSchedule()">
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900">Schedule</div>
                                    </label>
                                </div>
                            </div>

                            <div id="schedule-options" class="hidden">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="schedule-date"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
                                        <input type="date" id="schedule-date"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                    </div>
                                    <div>
                                        <label for="schedule-time"
                                            class="block text-sm font-semibold text-gray-700 mb-2">Time</label>
                                        <input type="time" id="schedule-time"
                                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Estimated Cost:</span>
                                    <span class="font-semibold text-gray-900" id="estimated-cost">Sh. 0.00</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-2">
                                    <span class="text-gray-600">Recipients:</span>
                                    <span class="font-semibold text-gray-900" id="recipient-count">0</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-2">
                                    <span class="text-gray-600">Credits Needed:</span>
                                    <span class="font-semibold text-gray-900" id="credits-needed">0</span>
                                </div>
                            </div>

                            <button type="submit" id="send-sms-btn"
                                class="w-full px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-paper-plane mr-2"></i>
                                <span id="send-button-text">Send SMS</span>
                            </button>
                        </form>
                    </div>

                    <div class="hidden lg:block">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-wallet text-green-600"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Credit Status</h3>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Available Credits:</span>
                                    <span class="font-semibold text-gray-900" id="available-credits">Loading...</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Credit Value:</span>
                                    <span class="font-semibold text-gray-900" id="credit-value">Sh. 0.00</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-300"
                                        style="width: 0%" id="credit-bar"></div>
                                </div>
                                <button onclick="switchTab('topup')"
                                    class="w-full px-4 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors font-medium">
                                    <i class="fas fa-plus mr-2"></i>Top Up Credits
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Tab -->
            <div id="history-content" class="tab-content hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="w-full lg:w-1/3">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" id="searchHistory"
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                        placeholder="Search SMS history..." oninput="filterHistory()">
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <select id="statusFilter"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    onchange="filterHistory()">
                                    <option value="">All Status</option>
                                    <option value="sent">Sent</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="failed">Failed</option>
                                </select>
                                <input type="date" id="dateFromFilter"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    onchange="filterHistory()" placeholder="From Date">
                                <input type="date" id="dateToFilter"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    onchange="filterHistory()" placeholder="To Date">
                                <button onclick="loadSmsHistory()"
                                    class="px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                                    <i class="fas fa-refresh mr-2"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="hidden lg:block">
                        <div class="overflow-x-auto max-h-[70vh]">
                            <table class="w-full" id="history-table">
                                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Message</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Recipients</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Cost</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body" class="divide-y divide-gray-100">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lg:hidden p-4 space-y-4 max-h-[70vh] overflow-y-auto" id="history-mobile">
                    </div>

                    <div id="history-empty-state" class="hidden text-center py-16">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-history text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No SMS history found</h3>
                        <p class="text-gray-500 mb-6">Send your first SMS to see it here</p>
                        <button onclick="switchTab('send')"
                            class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Send
                            SMS</button>
                    </div>
                </div>
            </div>

            <!-- Templates Tab -->
            <div id="templates-content" class="tab-content hidden">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="w-full lg:w-1/3">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" id="searchTemplates"
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200 bg-gray-50 focus:bg-white"
                                        placeholder="Search templates..." oninput="filterTemplates()">
                                </div>
                            </div>
                            <button onclick="showCreateTemplateForm()"
                                class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-plus"></i>
                                <span>Create Template</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="templates-grid">
                </div>

                <div id="templates-empty-state" class="hidden text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No templates found</h3>
                    <p class="text-gray-500 mb-6">Create your first template to save time</p>
                    <button onclick="showCreateTemplateForm()"
                        class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Create
                        Template</button>
                </div>
            </div>

            <!-- Top Up Tab -->
            <div id="topup-content" class="tab-content hidden">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-plus-circle text-orange-600"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Top Up SMS Credits</h3>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-6 mb-6">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Current SMS Rate</p>
                                <p class="text-3xl font-bold text-gray-900" id="topup-sms-rate">Loading...</p>
                                <p class="text-sm text-gray-500">per SMS</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 rounded-xl p-6 mb-6 border border-blue-200">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-wallet text-blue-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-gray-900">Wallet Balance</h4>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Available Balance</p>
                                    <p class="text-2xl font-bold text-gray-900" id="wallet-balance">Loading...</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Equivalent Credits</p>
                                    <p class="text-2xl font-bold text-blue-600" id="wallet-credits">Loading...</p>
                                </div>
                            </div>
                        </div>

                        <form id="topup-form" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Package</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label
                                        class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="package" value="100" class="sr-only"
                                            onchange="updateTopupCalculation()">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">100 SMS</div>
                                            <div class="text-sm text-gray-500" id="package-100-cost">Calculating...
                                            </div>
                                        </div>
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                    </label>
                                    <label
                                        class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="package" value="500" class="sr-only"
                                            onchange="updateTopupCalculation()">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">500 SMS</div>
                                            <div class="text-sm text-gray-500" id="package-500-cost">Calculating...
                                            </div>
                                        </div>
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                    </label>
                                    <label
                                        class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="package" value="1000" class="sr-only"
                                            onchange="updateTopupCalculation()">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">1,000 SMS</div>
                                            <div class="text-sm text-gray-500" id="package-1000-cost">Calculating...
                                            </div>
                                            <div class="text-xs text-green-600 font-medium">Most Popular</div>
                                        </div>
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                    </label>
                                    <label
                                        class="relative flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio" name="package" value="custom" class="sr-only"
                                            onchange="updateTopupCalculation()">
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">Custom Amount</div>
                                            <div class="text-sm text-gray-500">Enter your amount</div>
                                        </div>
                                        <div
                                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center">
                                            <div
                                                class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="custom-amount-section" class="hidden">
                                <label for="custom-amount" class="block text-sm font-semibold text-gray-700 mb-2">Custom
                                    Amount (Sh.)</label>
                                <input type="number" id="custom-amount"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    placeholder="Enter amount" min="1000" step="100" oninput="updateTopupCalculation()">
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">SMS Credits:</span>
                                    <span class="font-semibold text-gray-900" id="topup-credits">0</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">Total Cost:</span>
                                    <span class="font-semibold text-gray-900" id="topup-cost">Sh. 0.00</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Wallet Balance:</span>
                                    <span class="font-semibold" id="balance-status">Sh. 0.00</span>
                                </div>
                            </div>

                            <div id="insufficient-balance-warning"
                                class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-red-900">Insufficient Wallet Balance</h4>
                                        <p class="text-sm text-red-700 mt-1">You need to top up your wallet first before
                                            purchasing SMS credits.</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <a href="<?php echo BASE_URL; ?>vendor-store/zzimba-credit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                                        <i class="fas fa-plus mr-2"></i>Top Up Wallet
                                    </a>
                                </div>
                            </div>

                            <button type="submit" id="purchase-credits-btn"
                                class="w-full px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                                <i class="fas fa-credit-card mr-2"></i>
                                Purchase Credits
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="templateSelectorModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideTemplateSelector()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[80vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Select Template</h3>
                <button onclick="hideTemplateSelector()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-96">
            <div class="space-y-3" id="template-selector-list">
            </div>
        </div>
    </div>
</div>

<div id="templateModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideTemplateModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg relative z-10">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="template-modal-title">Create Template</h3>
                <button onclick="hideTemplateModal()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="template-form" class="space-y-4">
                <input type="hidden" id="template-id">
                <div>
                    <label for="template-name" class="block text-sm font-semibold text-gray-700 mb-2">Template
                        Name</label>
                    <input type="text" id="template-name"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter template name" required>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="template-message" class="block text-sm font-semibold text-gray-700">Message</label>
                        <span class="text-xs text-gray-500" id="template-char-count">0/160 characters</span>
                    </div>
                    <textarea id="template-message" rows="4"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter template message" required oninput="updateTemplateCharCount()"></textarea>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="hideTemplateModal()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">Save
                        Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="messageModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideMessageModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="message-modal-title">Message</h3>
                <button onclick="hideMessageModal()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" id="message-modal-icon">
                </div>
                <p class="text-gray-900" id="message-modal-text"></p>
            </div>
            <button onclick="hideMessageModal()"
                class="w-full px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">OK</button>
        </div>
    </div>
</div>

<script>
    // Global variables
    let smsStats = {};
    let smsHistory = [];
    let smsTemplates = [];
    let currentTab = 'send';
    let bulkRecipients = [];
    let currentSmsRate = 35;
    let walletBalance = 0;
    let walletCredits = 0;

    // Initialize the application
    document.addEventListener('DOMContentLoaded', function () {
        loadSmsStats();
        switchTab('send');
        setupEventListeners();
    });

    function setupEventListeners() {
        // Mobile tab toggle
        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        // Mobile tab options
        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchTab(tab);
                toggleMobileTabDropdown();
            });
        });

        // Form submissions
        document.getElementById('sms-form').addEventListener('submit', handleSendSms);
        document.getElementById('topup-form').addEventListener('submit', handlePurchaseCredits);
        document.getElementById('template-form').addEventListener('submit', handleSaveTemplate);

        // Input events
        document.getElementById('recipient').addEventListener('input', updateSendFormCalculations);
        document.getElementById('bulk-number-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBulkRecipient();
            }
        });
        document.getElementById('add-number-btn').addEventListener('click', addBulkRecipient);

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (event) {
            const mobileDropdown = document.getElementById('mobile-tab-dropdown');
            const mobileToggle = document.getElementById('mobile-tab-toggle');

            if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
                mobileDropdown.classList.add('hidden');
                document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
            }
        });
    }

    // API Functions
    function makeApiCall(action, data = {}, method = 'POST') {
        const formData = new FormData();
        formData.append('action', action);

        for (const key in data) {
            if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        }

        return fetch('fetch/manageSmsCenter.php', {
            method: method,
            body: formData
        })
            .then(response => response.json())
            .catch(error => {
                console.error('API Error:', error);
                return { success: false, message: 'Network error occurred' };
            });
    }

    function loadSmsStats() {
        makeApiCall('getSmsStats')
            .then(response => {
                if (response.success) {
                    smsStats = response.data;
                    updateStatsDisplay();
                } else {
                    showMessageModal('Error', response.message || 'Failed to load SMS stats', 'error');
                }
            });

        // Load wallet balance
        makeApiCall('getWalletBalance')
            .then(response => {
                if (response.success) {
                    walletBalance = response.data.balance;
                    walletCredits = response.data.equivalent_credits;
                    updateWalletDisplay();
                }
            });
    }

    function updateStatsDisplay() {
        document.getElementById('sms-credit-count').textContent = smsStats.current_credits || 0;
        document.getElementById('sent-today-count').textContent = smsStats.sent_today || 0;
        document.getElementById('sent-today-cost').textContent = `Sh. ${formatCurrency(smsStats.sent_today_cost || 0)}`;
        document.getElementById('scheduled-count').textContent = smsStats.scheduled_count || 0;
        document.getElementById('sms-rate-display').textContent = `Sh. ${formatCurrency(smsStats.sms_rate || 35)}/=`;

        document.getElementById('available-credits').textContent = smsStats.current_credits || 0;
        document.getElementById('credit-value').textContent = `Sh. ${formatCurrency(smsStats.credit_value || 0)}`;

        currentSmsRate = smsStats.sms_rate || 35;

        // Update credit bar
        const creditPercentage = Math.min((smsStats.current_credits / 1000) * 100, 100);
        document.getElementById('credit-bar').style.width = `${creditPercentage}%`;

        // Update topup display
        document.getElementById('topup-sms-rate').textContent = `Sh. ${formatCurrency(currentSmsRate)}/=`;
        updatePackageCosts();
        updateSendFormCalculations();
    }

    function updateWalletDisplay() {
        document.getElementById('wallet-balance').textContent = `Sh. ${formatCurrency(walletBalance)}`;
        document.getElementById('wallet-credits').textContent = walletCredits;
        updateTopupCalculation(); // Refresh the topup calculations
    }

    function updatePackageCosts() {
        document.getElementById('package-100-cost').textContent = `Sh. ${formatCurrency(100 * currentSmsRate)}`;
        document.getElementById('package-500-cost').textContent = `Sh. ${formatCurrency(500 * currentSmsRate)}`;
        document.getElementById('package-1000-cost').textContent = `Sh. ${formatCurrency(1000 * currentSmsRate)}`;
    }

    // Tab Management
    function switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-b-primary', 'text-primary');
            btn.classList.add('border-b-transparent', 'text-gray-500');
        });

        const activeTab = document.getElementById(`${tabName}-tab`);
        if (activeTab) {
            activeTab.classList.remove('border-b-transparent', 'text-gray-500');
            activeTab.classList.add('border-b-primary', 'text-primary');
        }

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        const activeContent = document.getElementById(`${tabName}-content`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }

        currentTab = tabName;

        // Load tab-specific data
        switch (tabName) {
            case 'history':
                loadSmsHistory();
                break;
            case 'templates':
                loadSmsTemplates();
                break;
        }

        // Update mobile tab label
        const tabLabels = {
            'send': { label: 'Send SMS', icon: 'fas fa-paper-plane' },
            'history': { label: 'SMS History', icon: 'fas fa-history' },
            'templates': { label: 'Templates', icon: 'fas fa-file-alt' },
            'topup': { label: 'Top Up Credit', icon: 'fas fa-plus-circle' },
            'credit': { label: 'Credit Status', icon: 'fas fa-wallet' }
        };
        const tabInfo = tabLabels[tabName] || tabLabels['send'];
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

    // Send SMS Functions
    function toggleSendType() {
        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        const singleRecipient = document.getElementById('single-recipient');
        const bulkRecipientsDiv = document.getElementById('bulk-recipients');

        if (sendType === 'single') {
            singleRecipient.classList.remove('hidden');
            bulkRecipientsDiv.classList.add('hidden');
            bulkRecipients = [];
            renderRecipientTags();
        } else {
            singleRecipient.classList.add('hidden');
            bulkRecipientsDiv.classList.remove('hidden');
        }
        updateSendFormCalculations();
    }

    function toggleSchedule() {
        const sendOption = document.querySelector('input[name="sendOption"]:checked').value;
        const scheduleOptions = document.getElementById('schedule-options');
        const sendButtonText = document.getElementById('send-button-text');

        if (sendOption === 'schedule') {
            scheduleOptions.classList.remove('hidden');
            sendButtonText.textContent = 'Schedule SMS';
        } else {
            scheduleOptions.classList.add('hidden');
            sendButtonText.textContent = 'Send SMS';
        }
    }

    function updateCharCount() {
        const message = document.getElementById('message').value;
        const charCount = document.getElementById('char-count');
        const smsParts = document.getElementById('sms-parts');

        charCount.textContent = `${message.length}/160 characters`;

        const parts = Math.ceil(message.length / 160) || 1;
        smsParts.textContent = `${parts} SMS part${parts > 1 ? 's' : ''}`;

        updateSendFormCalculations();
    }

    function validatePhoneNumber(number) {
        const cleaned = number.replace(/\s+/g, '');
        return /^0[7][0-9]{8}$/.test(cleaned);
    }

    function addBulkRecipient() {
        const input = document.getElementById('bulk-number-input');
        const number = input.value.trim();

        if (!number) return;

        if (!validatePhoneNumber(number)) {
            showMessageModal('Invalid Number', 'Please enter a valid 10-digit phone number (e.g., 0700123456)', 'error');
            return;
        }

        if (bulkRecipients.includes(number)) {
            showMessageModal('Duplicate Number', 'This number has already been added', 'warning');
            return;
        }

        bulkRecipients.push(number);
        input.value = '';
        renderRecipientTags();
        updateSendFormCalculations();
    }

    function removeRecipient(number) {
        bulkRecipients = bulkRecipients.filter(r => r !== number);
        renderRecipientTags();
        updateSendFormCalculations();
    }

    function renderRecipientTags() {
        const container = document.getElementById('recipient-tags');
        container.innerHTML = bulkRecipients.map(number => `
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary rounded-lg text-sm">
                ${number}
                <button type="button" onclick="removeRecipient('${number}')" class="hover:bg-primary/20 rounded-full p-1 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }

    function updateSendFormCalculations() {
        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        const message = document.getElementById('message').value;
        const parts = Math.ceil(message.length / 160) || 1;

        let recipientCount = 0;
        if (sendType === 'single') {
            const recipient = document.getElementById('recipient').value.trim();
            recipientCount = recipient ? 1 : 0;
        } else {
            recipientCount = bulkRecipients.length;
        }

        const creditsNeeded = recipientCount * parts;
        const totalCost = creditsNeeded * currentSmsRate;

        document.getElementById('recipient-count').textContent = recipientCount;
        document.getElementById('credits-needed').textContent = creditsNeeded;
        document.getElementById('estimated-cost').textContent = `Sh. ${formatCurrency(totalCost)}`;
    }

    function handleSendSms(e) {
        e.preventDefault();

        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        const message = document.getElementById('message').value.trim();
        const sendOption = document.querySelector('input[name="sendOption"]:checked').value;

        if (!message) {
            showMessageModal('Missing Message', 'Please enter a message', 'error');
            return;
        }

        let recipients = [];
        if (sendType === 'single') {
            const recipient = document.getElementById('recipient').value.trim();
            if (!recipient) {
                showMessageModal('Missing Recipient', 'Please enter a recipient phone number', 'error');
                return;
            }
            if (!validatePhoneNumber(recipient)) {
                showMessageModal('Invalid Number', 'Please enter a valid 10-digit phone number', 'error');
                return;
            }
            recipients = [recipient];
        } else {
            if (bulkRecipients.length === 0) {
                showMessageModal('No Recipients', 'Please add at least one recipient', 'error');
                return;
            }
            recipients = [...bulkRecipients];
        }

        let scheduledAt = null;
        if (sendOption === 'schedule') {
            const scheduleDate = document.getElementById('schedule-date').value;
            const scheduleTime = document.getElementById('schedule-time').value;

            if (!scheduleDate || !scheduleTime) {
                showMessageModal('Missing Schedule', 'Please select schedule date and time', 'error');
                return;
            }

            scheduledAt = `${scheduleDate} ${scheduleTime}:00`;
        }

        const sendBtn = document.getElementById('send-sms-btn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

        const data = {
            message: message,
            recipients: JSON.stringify(recipients),
            send_type: sendType,
            send_option: sendOption,
            scheduled_at: scheduledAt
        };

        makeApiCall('sendSms', data)
            .then(response => {
                if (response.success) {
                    showMessageModal('Success', response.message, 'success');
                    document.getElementById('sms-form').reset();
                    document.querySelector('input[name="sendType"][value="single"]').checked = true;
                    document.querySelector('input[name="sendOption"][value="now"]').checked = true;
                    bulkRecipients = [];
                    toggleSendType();
                    toggleSchedule();
                    updateCharCount();
                    loadSmsStats(); // Refresh stats
                } else {
                    showMessageModal('Error', response.message || 'Failed to send SMS', 'error');
                }
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i><span id="send-button-text">Send SMS</span>';
            });
    }

    // History Functions
    function loadSmsHistory() {
        const search = document.getElementById('searchHistory').value;
        const status = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFromFilter').value;
        const dateTo = document.getElementById('dateToFilter').value;

        const params = new URLSearchParams({
            action: 'getSmsHistory',
            search: search,
            status: status,
            date_from: dateFrom,
            date_to: dateTo,
            page: 1,
            limit: 50
        });

        fetch(`fetch/manageSmsCenter.php?${params}`)
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    smsHistory = response.data.history;
                    renderSmsHistory();
                } else {
                    showMessageModal('Error', response.message || 'Failed to load SMS history', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading SMS history:', error);
                showMessageModal('Error', 'Failed to load SMS history', 'error');
            });
    }

    function renderSmsHistory() {
        const tableBody = document.getElementById('history-table-body');
        const mobileContainer = document.getElementById('history-mobile');
        const emptyState = document.getElementById('history-empty-state');

        if (smsHistory.length === 0) {
            tableBody.innerHTML = '';
            mobileContainer.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');

        // Desktop table
        tableBody.innerHTML = smsHistory.map((sms, index) => {
            const statusBadge = getStatusBadge(sms.status);
            const recipientText = sms.recipient_count === 1 ? sms.recipients[0] : `${sms.recipient_count} recipients`;
            const dateText = sms.status === 'scheduled' ?
                `Scheduled: ${formatDateTime(sms.scheduled_at)}` :
                formatDateTime(sms.sent_at);

            return `
                <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'} hover:bg-blue-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="max-w-xs">
                            <div class="text-sm font-medium text-gray-900 truncate" title="${sms.message}">
                                ${sms.message.substring(0, 50)}${sms.message.length > 50 ? '...' : ''}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                ${sms.sms_parts} SMS part${sms.sms_parts > 1 ? 's' : ''}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-900">${recipientText}</div>
                        ${sms.type === 'bulk' ? `<div class="text-xs text-gray-500 mt-1">Bulk SMS</div>` : ''}
                    </td>
                    <td class="px-4 py-3">${statusBadge}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-900">Sh. ${formatCurrency(sms.total_cost)}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-900">${dateText}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button onclick="showSmsDetails('${sms.id}')" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        // Mobile cards
        mobileContainer.innerHTML = smsHistory.map(sms => {
            const statusBadge = getStatusBadge(sms.status);
            const recipientText = sms.recipient_count === 1 ? sms.recipients[0] : `${sms.recipient_count} recipients`;
            const dateText = sms.status === 'scheduled' ?
                `Scheduled: ${formatDateTime(sms.scheduled_at)}` :
                formatDateTime(sms.sent_at);

            return `
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 mb-2">${sms.message}</div>
                            <div class="text-xs text-gray-500">
                                ${sms.sms_parts} SMS part${sms.sms_parts > 1 ? 's' : ''}
                            </div>
                        </div>
                        ${statusBadge}
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                        <div>
                            <span class="text-gray-500 uppercase tracking-wide">Recipients</span>
                            <div class="font-medium text-gray-900 mt-1">${recipientText}</div>
                        </div>
                        <div>
                            <span class="text-gray-500 uppercase tracking-wide">Cost</span>
                            <div class="font-semibold text-gray-900 mt-1">Sh. ${formatCurrency(sms.total_cost)}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-xs text-gray-500">${dateText}</div>
                        <button onclick="showSmsDetails('${sms.id}')" class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-eye mr-1"></i>Details
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'sent': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Sent</span>',
            'scheduled': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Scheduled</span>',
            'failed': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>',
            'cancelled': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Cancelled</span>'
        };
        return badges[status] || '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function filterHistory() {
        loadSmsHistory();
    }

    function showSmsDetails(smsId) {
        const sms = smsHistory.find(s => s.id === smsId);
        if (!sms) return;

        const dateText = sms.status === 'scheduled' ?
            `Scheduled for: ${formatDateTime(sms.scheduled_at)}` :
            `Sent on: ${formatDateTime(sms.sent_at)}`;

        const modalContent = `
            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Message</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-900">${sms.message}</p>
                    </div>
                    <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                        <span>${sms.message.length} characters</span>
                        <span>${sms.sms_parts} SMS part${sms.sms_parts > 1 ? 's' : ''}</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Recipients (${sms.recipient_count})</h4>
                    <div class="bg-gray-50 rounded-lg p-4 max-h-40 overflow-y-auto">
                        ${sms.recipients.map(recipient => `
                            <div class="flex items-center gap-2 py-1">
                                <i class="fas fa-phone text-gray-400 text-xs"></i>
                                <span class="text-gray-900">${recipient}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Status</h4>
                        ${getStatusBadge(sms.status)}
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Cost</h4>
                        <div class="text-lg font-semibold text-gray-900">Sh. ${formatCurrency(sms.total_cost)}</div>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Date & Time</h4>
                    <div class="text-gray-900">${dateText}</div>
                </div>
            </div>
        `;

        showModal('SMS Details', modalContent);
    }

    // Template Functions
    function loadSmsTemplates() {
        makeApiCall('getSmsTemplates')
            .then(response => {
                if (response.success) {
                    smsTemplates = response.data;
                    renderTemplates();
                } else {
                    showMessageModal('Error', response.message || 'Failed to load templates', 'error');
                }
            });
    }

    function renderTemplates() {
        const grid = document.getElementById('templates-grid');
        const emptyState = document.getElementById('templates-empty-state');

        if (smsTemplates.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');

        grid.innerHTML = smsTemplates.map(template => `
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-purple-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">${template.name}</h4>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editTemplate('${template.id}')" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" title="Edit Template">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="deleteTemplate('${template.id}')" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" title="Delete Template">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">${template.message}</p>
                <div class="flex gap-2">
                    <button onclick="selectTemplate('${template.id}'); switchTab('send')" class="flex-1 px-3 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                        <i class="fas fa-paper-plane mr-1"></i>Use Template
                    </button>
                </div>
            </div>
        `).join('');
    }

    function showCreateTemplateForm() {
        document.getElementById('template-modal-title').textContent = 'Create Template';
        document.getElementById('template-form').reset();
        document.getElementById('template-id').value = '';
        document.getElementById('templateModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateTemplateCharCount();
    }

    function editTemplate(templateId) {
        const template = smsTemplates.find(t => t.id === templateId);
        if (!template) return;

        document.getElementById('template-modal-title').textContent = 'Edit Template';
        document.getElementById('template-id').value = template.id;
        document.getElementById('template-name').value = template.name;
        document.getElementById('template-message').value = template.message;
        document.getElementById('templateModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateTemplateCharCount();
    }

    function deleteTemplate(templateId) {
        if (!confirm('Are you sure you want to delete this template?')) return;

        makeApiCall('deleteTemplate', { template_id: templateId })
            .then(response => {
                if (response.success) {
                    showMessageModal('Success', response.message, 'success');
                    loadSmsTemplates();
                } else {
                    showMessageModal('Error', response.message || 'Failed to delete template', 'error');
                }
            });
    }

    function handleSaveTemplate(e) {
        e.preventDefault();

        const templateId = document.getElementById('template-id').value;
        const name = document.getElementById('template-name').value.trim();
        const message = document.getElementById('template-message').value.trim();

        if (!name || !message) {
            showMessageModal('Missing Fields', 'Please fill in all fields', 'error');
            return;
        }

        const data = {
            template_id: templateId,
            name: name,
            message: message
        };

        makeApiCall('saveTemplate', data)
            .then(response => {
                if (response.success) {
                    showMessageModal('Success', response.message, 'success');
                    hideTemplateModal();
                    loadSmsTemplates();
                } else {
                    showMessageModal('Error', response.message || 'Failed to save template', 'error');
                }
            });
    }

    function selectTemplate(templateId) {
        const template = smsTemplates.find(t => t.id === templateId);
        if (template) {
            document.getElementById('message').value = template.message;
            updateCharCount();
        }
        hideTemplateSelector();
    }

    function showTemplateSelector() {
        const modal = document.getElementById('templateSelectorModal');
        const list = document.getElementById('template-selector-list');

        if (smsTemplates.length === 0) {
            list.innerHTML = '<p class="text-center text-gray-500">No templates available</p>';
        } else {
            list.innerHTML = smsTemplates.map(template => `
                <button onclick="selectTemplate('${template.id}')" class="w-full text-left p-4 border border-gray-200 rounded-lg hover:border-primary/30 hover:bg-primary/5 transition-all">
                    <div class="font-medium text-gray-900">${template.name}</div>
                    <div class="text-sm text-gray-500 mt-1">${template.message}</div>
                </button>
            `).join('');
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideTemplateSelector() {
        document.getElementById('templateSelectorModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function hideTemplateModal() {
        document.getElementById('templateModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function updateTemplateCharCount() {
        const message = document.getElementById('template-message').value;
        const charCount = document.getElementById('template-char-count');
        charCount.textContent = `${message.length}/160 characters`;
    }

    function filterTemplates() {
        const query = document.getElementById('searchTemplates').value.toLowerCase();
        const filteredTemplates = smsTemplates.filter(template =>
            template.name.toLowerCase().includes(query) ||
            template.message.toLowerCase().includes(query)
        );

        const grid = document.getElementById('templates-grid');
        const emptyState = document.getElementById('templates-empty-state');

        if (filteredTemplates.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');

        grid.innerHTML = filteredTemplates.map(template => `
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-purple-600"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900">${template.name}</h4>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editTemplate('${template.id}')" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" title="Edit Template">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="deleteTemplate('${template.id}')" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" title="Delete Template">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-4">${template.message}</p>
                <div class="flex gap-2">
                    <button onclick="selectTemplate('${template.id}'); switchTab('send')" class="flex-1 px-3 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                        <i class="fas fa-paper-plane mr-1"></i>Use Template
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Top Up Functions
    function updateTopupCalculation() {
        const selectedPackage = document.querySelector('input[name="package"]:checked');
        const customAmountSection = document.getElementById('custom-amount-section');
        const customAmountInput = document.getElementById('custom-amount');
        const topupCredits = document.getElementById('topup-credits');
        const topupCost = document.getElementById('topup-cost');
        const balanceStatus = document.getElementById('balance-status');
        const insufficientWarning = document.getElementById('insufficient-balance-warning');
        const purchaseBtn = document.getElementById('purchase-credits-btn');

        if (!selectedPackage) {
            topupCredits.textContent = '0';
            topupCost.textContent = 'Sh. 0.00';
            balanceStatus.textContent = `Sh. ${formatCurrency(walletBalance)}`;
            balanceStatus.className = 'font-semibold text-gray-900';
            insufficientWarning.classList.add('hidden');
            purchaseBtn.disabled = false;
            return;
        }

        const packageValue = selectedPackage.value;
        let cost = 0;
        let credits = 0;

        if (packageValue === 'custom') {
            customAmountSection.classList.remove('hidden');
            const customAmount = parseFloat(customAmountInput.value) || 0;
            cost = customAmount;
            credits = Math.floor(customAmount / currentSmsRate);
        } else {
            customAmountSection.classList.add('hidden');
            credits = parseInt(packageValue);
            cost = credits * currentSmsRate;
        }

        topupCredits.textContent = credits;
        topupCost.textContent = `Sh. ${formatCurrency(cost)}`;
        balanceStatus.textContent = `Sh. ${formatCurrency(walletBalance)}`;

        // Check if wallet balance is sufficient
        if (cost > walletBalance) {
            balanceStatus.className = 'font-semibold text-red-600';
            insufficientWarning.classList.remove('hidden');
            purchaseBtn.disabled = true;
        } else {
            balanceStatus.className = 'font-semibold text-green-600';
            insufficientWarning.classList.add('hidden');
            purchaseBtn.disabled = false;
        }
    }

    function handlePurchaseCredits(e) {
        e.preventDefault();

        const selectedPackage = document.querySelector('input[name="package"]:checked');

        if (!selectedPackage) {
            showMessageModal('Missing Package', 'Please select a package', 'error');
            return;
        }

        let credits = 0;
        let amount = 0;

        if (selectedPackage.value === 'custom') {
            const customAmount = parseFloat(document.getElementById('custom-amount').value) || 0;
            if (customAmount < 1000) {
                showMessageModal('Invalid Amount', 'Minimum top-up amount is Sh. 1,000', 'error');
                return;
            }
            credits = Math.floor(customAmount / currentSmsRate);
            amount = customAmount;
        } else {
            credits = parseInt(selectedPackage.value);
            amount = credits * currentSmsRate;
        }

        // Check wallet balance
        if (amount > walletBalance) {
            showMessageModal('Insufficient Balance', `You need Sh. ${formatCurrency(amount)} but only have Sh. ${formatCurrency(walletBalance)} in your wallet. Please top up your wallet first.`, 'error');
            return;
        }

        // Show confirmation modal instead of alert
        const confirmationContent = `
        <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-credit-card text-blue-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirm Purchase</h3>
            <p class="text-gray-600 mb-6">
                Purchase <strong>${credits} SMS credits</strong> for <strong>Sh. ${formatCurrency(amount)}</strong>?
            </p>
            <div class="flex gap-3">
                <button onclick="hideCustomModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                <button onclick="confirmPurchaseCredits(${amount})" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Confirm Purchase</button>
            </div>
        </div>
    `;
        showModal('Confirm Purchase', confirmationContent);
    }

    function confirmPurchaseCredits(amount) {
        hideCustomModal();

        const purchaseBtn = document.getElementById('purchase-credits-btn');
        purchaseBtn.disabled = true;
        purchaseBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

        const data = {
            amount: amount
        };

        makeApiCall('purchaseSmsCredits', data)
            .then(response => {
                if (response.success) {
                    showMessageModal('Success', response.message, 'success');
                    document.getElementById('topup-form').reset();
                    updateTopupCalculation();
                    loadSmsStats(); // Refresh stats and wallet balance
                } else {
                    if (response.data && response.data.topup_url) {
                        // Show insufficient balance with topup button
                        const modalContent = `
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Insufficient Wallet Balance</h3>
                            <p class="text-gray-600 mb-6">${response.message}</p>
                            <div class="flex gap-3">
                                <button onclick="hideCustomModal()" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                                <a href="${response.data.topup_url}" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors text-center">Top Up Wallet</a>
                            </div>
                        </div>
                    `;
                        showModal('Insufficient Balance', modalContent);
                    } else {
                        showMessageModal('Error', response.message || 'Failed to purchase credits', 'error');
                    }
                }
            })
            .finally(() => {
                purchaseBtn.disabled = false;
                purchaseBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Purchase Credits';
            });
    }

    // Utility Functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-UG', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';

        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.toLocaleString('en-US', { month: 'short' });
        const year = date.getFullYear();
        const time = date.toLocaleString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        const suffix = day === 1 || day === 21 || day === 31 ? 'st' :
            day === 2 || day === 22 ? 'nd' :
                day === 3 || day === 23 ? 'rd' : 'th';

        return `${day}${suffix} ${month}, ${year} ${time}`;
    }

    function showMessageModal(title, message, type = 'info') {
        const modal = document.getElementById('messageModal');
        const titleEl = document.getElementById('message-modal-title');
        const textEl = document.getElementById('message-modal-text');
        const iconEl = document.getElementById('message-modal-icon');

        titleEl.textContent = title;
        textEl.textContent = message;

        const iconClasses = {
            'success': 'bg-green-100 text-green-600',
            'error': 'bg-red-100 text-red-600',
            'warning': 'bg-yellow-100 text-yellow-600',
            'info': 'bg-blue-100 text-blue-600'
        };

        const icons = {
            'success': 'fas fa-check',
            'error': 'fas fa-exclamation-triangle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info'
        };

        iconEl.className = `w-8 h-8 rounded-lg flex items-center justify-center ${iconClasses[type] || iconClasses.info}`;
        iconEl.innerHTML = `<i class="${icons[type] || icons.info}"></i>`;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideMessageModal() {
        document.getElementById('messageModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function showModal(title, content) {
        // Create a temporary modal for custom content
        const modalHtml = `
            <div id="customModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideCustomModal()"></div>
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[80vh] overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                            <button onclick="hideCustomModal()" class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                                <i class="fas fa-times text-gray-500"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 overflow-y-auto max-h-96">
                        ${content}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        document.body.style.overflow = 'hidden';
    }

    function hideCustomModal() {
        const modal = document.getElementById('customModal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }

    // Global function assignments for onclick handlers
    window.switchTab = switchTab;
    window.toggleSendType = toggleSendType;
    window.toggleSchedule = toggleSchedule;
    window.updateCharCount = updateCharCount;
    window.addBulkRecipient = addBulkRecipient;
    window.removeRecipient = removeRecipient;
    window.showTemplateSelector = showTemplateSelector;
    window.hideTemplateSelector = hideTemplateSelector;
    window.selectTemplate = selectTemplate;
    window.showCreateTemplateForm = showCreateTemplateForm;
    window.editTemplate = editTemplate;
    window.deleteTemplate = deleteTemplate;
    window.hideTemplateModal = hideTemplateModal;
    window.updateTemplateCharCount = updateTemplateCharCount;
    window.filterTemplates = filterTemplates;
    window.loadSmsHistory = loadSmsHistory;
    window.filterHistory = filterHistory;
    window.showSmsDetails = showSmsDetails;
    window.updateTopupCalculation = updateTopupCalculation;
    window.hideMessageModal = hideMessageModal;
    window.hideCustomModal = hideCustomModal;
    window.confirmPurchaseCredits = confirmPurchaseCredits;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>