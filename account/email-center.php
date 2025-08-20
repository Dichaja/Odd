<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Email Center';
$activeNav = 'email-center';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Sent Today</p>
                            <p class="text-lg font-bold text-blue-900 whitespace-nowrap" id="sent-today-count">0</p>
                            <p class="text-sm font-medium text-blue-700 whitespace-nowrap">Emails Delivered</p>
                        </div>
                        <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Opened Today</p>
                            <p class="text-lg font-bold text-green-900 whitespace-nowrap" id="opened-today-count">0</p>
                            <p class="text-sm font-medium text-green-700 whitespace-nowrap" id="open-rate-today">0% Open
                                Rate</p>
                        </div>
                        <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-envelope-open text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Scheduled</p>
                            <p class="text-lg font-bold text-purple-900 whitespace-nowrap" id="scheduled-count">0</p>
                            <p class="text-sm font-medium text-purple-700 whitespace-nowrap">Pending Emails</p>
                        </div>
                        <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Total Sent</p>
                            <p class="text-lg font-bold text-orange-900 whitespace-nowrap" id="total-sent-count">0</p>
                            <p class="text-sm font-medium text-orange-700 whitespace-nowrap" id="overall-open-rate">0%
                                Overall</p>
                        </div>
                        <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-orange-600"></i>
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
                            <i class="fas fa-paper-plane mr-2"></i>Send Email
                        </button>
                        <button id="history-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('history')">
                            <i class="fas fa-history mr-2"></i>Email History
                        </button>
                        <button id="templates-tab"
                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-all duration-200 border-b-transparent text-gray-500 hover:text-primary hover:border-b-primary/30"
                            onclick="switchTab('templates')">
                            <i class="fas fa-file-alt mr-2"></i>Templates
                        </button>
                    </nav>
                </div>

                <div class="md:hidden px-6 py-4">
                    <div class="relative">
                        <button id="mobile-tab-toggle"
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-paper-plane text-primary"></i>
                                <span id="mobile-tab-label" class="font-medium text-gray-900">Send Email</span>
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
                                    <span>Send Email</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="history">
                                    <i class="fas fa-history text-green-600"></i>
                                    <span>Email History</span>
                                </button>
                                <button
                                    class="mobile-tab-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                    data-tab="templates">
                                    <i class="fas fa-file-alt text-purple-600"></i>
                                    <span>Templates</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content">
            <div id="send-content" class="tab-content">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paper-plane text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Compose Email</h3>
                    </div>

                    <form id="email-form" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Send Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                    <input type="radio" name="sendType" value="single" class="sr-only" checked
                                        onchange="toggleSendType()">
                                    <div
                                        class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">Single Email</div>
                                </label>
                                <label
                                    class="relative flex items-center p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary/30 transition-all duration-200 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                    <input type="radio" name="sendType" value="bulk" class="sr-only"
                                        onchange="toggleSendType()">
                                    <div
                                        class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-primary peer-checked:bg-primary flex items-center justify-center mr-2">
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
                                        </div>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900">Bulk Email</div>
                                </label>
                            </div>
                        </div>

                        <div id="single-recipient">
                            <label for="recipient" class="block text-sm font-semibold text-gray-700 mb-2">Recipient
                                Email</label>
                            <input type="email" id="recipient"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="recipient@example.com" autocomplete="off">
                        </div>

                        <div id="bulk-recipients" class="hidden">
                            <label for="bulk-email-input" class="block text-sm font-semibold text-gray-700 mb-2">Add
                                Recipients</label>
                            <div class="flex gap-2">
                                <input type="email" id="bulk-email-input"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    placeholder="recipient@example.com" autocomplete="off">
                                <button type="button" id="add-email-btn"
                                    class="md:hidden px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div id="recipient-tags" class="mt-3 flex flex-wrap gap-2"></div>
                            <p class="text-xs text-gray-500 mt-2">Enter valid email addresses</p>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                            <input type="text" id="subject"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                placeholder="Enter email subject">
                            <div class="flex items-center justify-end mt-2">
                                <button type="button" onclick="showTemplateSelector()"
                                    class="text-xs text-primary hover:text-primary/80 font-medium">Use Template</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div id="editor-toolbar"
                                    class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-2">
                                    <button type="button" onclick="formatText('bold')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Bold">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" onclick="formatText('italic')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Italic">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" onclick="formatText('underline')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Underline">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                    <div class="w-px bg-gray-300 mx-1"></div>
                                    <button type="button" onclick="formatText('justifyLeft')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Left">
                                        <i class="fas fa-align-left"></i>
                                    </button>
                                    <button type="button" onclick="formatText('justifyCenter')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Center">
                                        <i class="fas fa-align-center"></i>
                                    </button>
                                    <button type="button" onclick="formatText('justifyRight')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Right">
                                        <i class="fas fa-align-right"></i>
                                    </button>
                                    <div class="w-px bg-gray-300 mx-1"></div>
                                    <button type="button" onclick="formatText('insertUnorderedList')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Bullet List">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                    <button type="button" onclick="formatText('insertOrderedList')"
                                        class="p-2 rounded hover:bg-gray-200 transition-colors" title="Numbered List">
                                        <i class="fas fa-list-ol"></i>
                                    </button>
                                    <div class="w-px bg-gray-300 mx-1"></div>
                                    <select onchange="formatText('fontSize', this.value)"
                                        class="px-2 py-1 border border-gray-300 rounded text-sm">
                                        <option value="">Font Size</option>
                                        <option value="1">Small</option>
                                        <option value="3">Normal</option>
                                        <option value="5">Large</option>
                                        <option value="7">Extra Large</option>
                                    </select>
                                    <input type="color" onchange="formatText('foreColor', this.value)"
                                        class="w-8 h-8 border border-gray-300 rounded cursor-pointer"
                                        title="Text Color">
                                </div>
                                <div id="email-editor" contenteditable="true"
                                    class="min-h-[200px] p-4 focus:outline-none"
                                    placeholder="Type your message here..."></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Attachments</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-primary/50 transition-colors">
                                <input type="file" id="attachments" multiple class="hidden"
                                    onchange="handleFileSelect(event)">
                                <button type="button" onclick="document.getElementById('attachments').click()"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                    <i class="fas fa-paperclip"></i>
                                    <span>Choose Files</span>
                                </button>
                                <p class="text-sm text-gray-500 mt-2">Or drag and drop files here</p>
                            </div>
                            <div id="attachment-list" class="mt-3 space-y-2"></div>
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
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
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
                                        <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100">
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
                                <span class="text-gray-600">Recipients:</span>
                                <span class="font-semibold text-gray-900" id="recipient-count">0</span>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all duration-200 font-medium shadow-lg shadow-primary/25 hover:shadow-xl hover:shadow-primary/30">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <span id="send-button-text">Send Email</span>
                        </button>
                    </form>
                </div>
            </div>

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
                                        placeholder="Search email history..." oninput="filterHistory()">
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <select id="statusFilter"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    onchange="filterHistory()">
                                    <option value="">All Status</option>
                                    <option value="sent">Sent</option>
                                    <option value="opened">Opened</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="failed">Failed</option>
                                </select>
                                <select id="dateRangeFilter"
                                    class="px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                    onchange="handleDateRangeChange()">
                                    <option value="today">Today</option>
                                    <option value="week" selected>This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <button onclick="resetEmailData()"
                                    class="px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                                    <i class="fas fa-refresh mr-2"></i>Reset Data
                                </button>
                            </div>
                        </div>
                        <div id="custom-date-range" class="hidden mt-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="start-date" class="block text-sm font-semibold text-gray-700 mb-2">Start
                                        Date</label>
                                    <input type="date" id="start-date"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                        onchange="filterHistory()">
                                </div>
                                <div>
                                    <label for="end-date" class="block text-sm font-semibold text-gray-700 mb-2">End
                                        Date</label>
                                    <input type="date" id="end-date"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                                        onchange="filterHistory()">
                                </div>
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
                                            Subject</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Recipients</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Open Rate</th>
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No email history found</h3>
                        <p class="text-gray-500 mb-6">Send your first email to see it here</p>
                        <button onclick="switchTab('send')"
                            class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">Send
                            Email</button>
                    </div>
                </div>
            </div>

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
        </div>
    </div>
</div>

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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" id="template-modal-title">Create Template</h3>
                <button onclick="hideTemplateModal()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
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
                    <label for="template-subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                    <input type="text" id="template-subject"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        placeholder="Enter email subject" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div id="template-editor-toolbar"
                            class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-2">
                            <button type="button" onclick="formatTemplateText('bold')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Bold">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" onclick="formatTemplateText('italic')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Italic">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" onclick="formatTemplateText('underline')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Underline">
                                <i class="fas fa-underline"></i>
                            </button>
                            <div class="w-px bg-gray-300 mx-1"></div>
                            <button type="button" onclick="formatTemplateText('justifyLeft')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Left">
                                <i class="fas fa-align-left"></i>
                            </button>
                            <button type="button" onclick="formatTemplateText('justifyCenter')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Center">
                                <i class="fas fa-align-center"></i>
                            </button>
                            <button type="button" onclick="formatTemplateText('justifyRight')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Align Right">
                                <i class="fas fa-align-right"></i>
                            </button>
                        </div>
                        <div id="template-editor" contenteditable="true" class="min-h-[150px] p-4 focus:outline-none"
                            placeholder="Enter template message..."></div>
                    </div>
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

<div id="emailDetailsModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEmailDetails()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl relative z-10 max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Email Details</h3>
                <button onclick="hideEmailDetails()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="email-details-content">
        </div>
    </div>
</div>

<div id="editScheduledModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideEditScheduled()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Edit Scheduled Email</h3>
                <button onclick="hideEditScheduled()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <form id="edit-scheduled-form" class="space-y-4">
                <input type="hidden" id="edit-email-id">
                <div>
                    <label for="edit-email-subject"
                        class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                    <input type="text" id="edit-email-subject"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div id="edit-editor-toolbar"
                            class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-2">
                            <button type="button" onclick="formatEditText('bold')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Bold">
                                <i class="fas fa-bold"></i>
                            </button>
                            <button type="button" onclick="formatEditText('italic')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Italic">
                                <i class="fas fa-italic"></i>
                            </button>
                            <button type="button" onclick="formatEditText('underline')"
                                class="p-2 rounded hover:bg-gray-200 transition-colors" title="Underline">
                                <i class="fas fa-underline"></i>
                            </button>
                        </div>
                        <div id="edit-email-editor" contenteditable="true" class="min-h-[150px] p-4 focus:outline-none">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit-schedule-date"
                            class="block text-sm font-semibold text-gray-700 mb-2">Date</label>
                        <input type="date" id="edit-schedule-date"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                    </div>
                    <div>
                        <label for="edit-schedule-time"
                            class="block text-sm font-semibold text-gray-700 mb-2">Time</label>
                        <input type="time" id="edit-schedule-time"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                            required>
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="hideEditScheduled()"
                        class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors font-medium">Update
                        Email</button>
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

<div id="confirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="hideConfirmModal()"></div>
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative z-10">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Confirm Action</h3>
                <button onclick="hideConfirmModal()"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <p class="text-gray-900" id="confirm-modal-text"></p>
            </div>
            <div class="flex gap-3">
                <button onclick="hideConfirmModal()"
                    class="flex-1 px-4 py-3 border border-gray-200 rounded-xl text-gray-700 hover:bg-gray-100 transition-colors font-medium">Cancel</button>
                <button id="confirm-modal-action"
                    class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    let emailHistory = [];
    let emailTemplates = [];
    let currentTab = 'send';
    let bulkRecipients = [];
    let attachedFiles = [];

    function initializeDummyData() {
        const savedHistory = localStorage.getItem('email_history');
        const savedTemplates = localStorage.getItem('email_templates');

        if (savedHistory) {
            emailHistory = JSON.parse(savedHistory);
        } else {
            emailHistory = [
                {
                    id: 1,
                    subject: "Welcome to our platform!",
                    message: "<p>Thank you for joining us. We're excited to have you on board!</p>",
                    recipients: ["user1@example.com"],
                    type: "single",
                    status: "opened",
                    sentAt: new Date(Date.now() - 86400000).toISOString(),
                    scheduledAt: null,
                    openedAt: new Date(Date.now() - 82800000).toISOString(),
                    attachments: []
                },
                {
                    id: 2,
                    subject: "Monthly Newsletter - December 2024",
                    message: "<p>Here's what's new this month...</p>",
                    recipients: ["user2@example.com", "user3@example.com"],
                    type: "bulk",
                    status: "sent",
                    sentAt: new Date(Date.now() - 172800000).toISOString(),
                    scheduledAt: null,
                    openedAt: null,
                    attachments: ["newsletter.pdf"]
                },
                {
                    id: 3,
                    subject: "Reminder: Meeting Tomorrow",
                    message: "<p>Don't forget about our meeting scheduled for tomorrow at 2:00 PM.</p>",
                    recipients: ["colleague@example.com"],
                    type: "single",
                    status: "scheduled",
                    sentAt: null,
                    scheduledAt: new Date(Date.now() + 86400000).toISOString(),
                    openedAt: null,
                    attachments: []
                }
            ];
            localStorage.setItem('email_history', JSON.stringify(emailHistory));
        }

        if (savedTemplates) {
            emailTemplates = JSON.parse(savedTemplates);
        } else {
            emailTemplates = [
                {
                    id: 1,
                    name: "Welcome Email",
                    subject: "Welcome to our platform!",
                    message: "<p>Thank you for joining us. We're excited to have you on board!</p><p>Best regards,<br>The Team</p>",
                    createdAt: new Date().toISOString()
                },
                {
                    id: 2,
                    name: "Meeting Reminder",
                    subject: "Reminder: Meeting {DATE}",
                    message: "<p>This is a friendly reminder about our meeting scheduled for {DATE} at {TIME}.</p><p>Looking forward to seeing you there!</p>",
                    createdAt: new Date().toISOString()
                },
                {
                    id: 3,
                    name: "Thank You Note",
                    subject: "Thank you for your business",
                    message: "<p>We wanted to take a moment to thank you for choosing our services.</p><p>Your support means everything to us!</p>",
                    createdAt: new Date().toISOString()
                }
            ];
            localStorage.setItem('email_templates', JSON.stringify(emailTemplates));
        }
    }

    function saveData() {
        localStorage.setItem('email_history', JSON.stringify(emailHistory));
        localStorage.setItem('email_templates', JSON.stringify(emailTemplates));
    }

    function resetEmailData() {
        showConfirmModal('Are you sure you want to reset all email data? This action cannot be undone.', () => {
            localStorage.removeItem('email_history');
            localStorage.removeItem('email_templates');
            initializeDummyData();
            updateStats();
            renderCurrentTab();
            showMessageModal('Success', 'Email data has been reset successfully!', 'success');
        });
    }

    function updateStats() {
        const today = new Date().toDateString();
        const sentToday = emailHistory.filter(email =>
            email.status === 'sent' &&
            new Date(email.sentAt).toDateString() === today
        );
        const openedToday = emailHistory.filter(email =>
            email.openedAt &&
            new Date(email.openedAt).toDateString() === today
        );
        const scheduled = emailHistory.filter(email => email.status === 'scheduled');
        const totalSent = emailHistory.filter(email => email.status === 'sent' || email.status === 'opened');
        const totalOpened = emailHistory.filter(email => email.openedAt);

        const sentTodayCount = sentToday.reduce((sum, email) => sum + email.recipients.length, 0);
        const openedTodayCount = openedToday.length;
        const openRateToday = sentTodayCount > 0 ? Math.round((openedTodayCount / sentTodayCount) * 100) : 0;
        const overallOpenRate = totalSent.length > 0 ? Math.round((totalOpened.length / totalSent.length) * 100) : 0;

        document.getElementById('sent-today-count').textContent = sentTodayCount;
        document.getElementById('opened-today-count').textContent = openedTodayCount;
        document.getElementById('open-rate-today').textContent = `${openRateToday}% Open Rate`;
        document.getElementById('scheduled-count').textContent = scheduled.length;
        document.getElementById('total-sent-count').textContent = totalSent.reduce((sum, email) => sum + email.recipients.length, 0);
        document.getElementById('overall-open-rate').textContent = `${overallOpenRate}% Overall`;
    }

    function formatDateTime(dateString) {
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

    function switchTab(tabName) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-b-primary', 'text-primary');
            btn.classList.add('border-b-transparent', 'text-gray-500');
        });

        const activeTab = document.getElementById(`${tabName}-tab`);
        if (activeTab) {
            activeTab.classList.remove('border-b-transparent', 'text-gray-500');
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
        renderCurrentTab();

        const tabLabels = {
            'send': { label: 'Send Email', icon: 'fas fa-paper-plane' },
            'history': { label: 'Email History', icon: 'fas fa-history' },
            'templates': { label: 'Templates', icon: 'fas fa-file-alt' }
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

    function renderCurrentTab() {
        switch (currentTab) {
            case 'send':
                updateSendFormCalculations();
                break;
            case 'history':
                renderEmailHistory();
                break;
            case 'templates':
                renderTemplates();
                break;
        }
    }

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
            sendButtonText.textContent = 'Schedule Email';
        } else {
            scheduleOptions.classList.add('hidden');
            sendButtonText.textContent = 'Send Email';
        }
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function addBulkRecipient() {
        const input = document.getElementById('bulk-email-input');
        const email = input.value.trim();

        if (!email) return;

        if (!validateEmail(email)) {
            showMessageModal('Invalid Email', 'Please enter a valid email address', 'error');
            return;
        }

        if (bulkRecipients.includes(email)) {
            showMessageModal('Duplicate Email', 'This email has already been added', 'warning');
            return;
        }

        bulkRecipients.push(email);
        input.value = '';
        renderRecipientTags();
        updateSendFormCalculations();
    }

    function removeRecipient(email) {
        bulkRecipients = bulkRecipients.filter(r => r !== email);
        renderRecipientTags();
        updateSendFormCalculations();
    }

    function renderRecipientTags() {
        const container = document.getElementById('recipient-tags');
        container.innerHTML = bulkRecipients.map(email => `
        <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary rounded-lg text-sm">
            ${email}
            <button type="button" onclick="removeRecipient('${email}')" class="hover:bg-primary/20 rounded-full p-1 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </span>
    `).join('');
    }

    function updateSendFormCalculations() {
        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        let recipientCount = 0;

        if (sendType === 'single') {
            const recipient = document.getElementById('recipient').value.trim();
            recipientCount = recipient ? 1 : 0;
        } else {
            recipientCount = bulkRecipients.length;
        }

        document.getElementById('recipient-count').textContent = recipientCount;
    }

    function formatText(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('email-editor').focus();
    }

    function formatTemplateText(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('template-editor').focus();
    }

    function formatEditText(command, value = null) {
        document.execCommand(command, false, value);
        document.getElementById('edit-email-editor').focus();
    }

    function handleFileSelect(event) {
        const files = Array.from(event.target.files);
        attachedFiles = [...attachedFiles, ...files];
        renderAttachmentList();
    }

    function removeAttachment(index) {
        attachedFiles.splice(index, 1);
        renderAttachmentList();
    }

    function renderAttachmentList() {
        const container = document.getElementById('attachment-list');
        if (attachedFiles.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = attachedFiles.map((file, index) => `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-3">
                <i class="fas fa-paperclip text-gray-400"></i>
                <div>
                    <div class="text-sm font-medium text-gray-900">${file.name}</div>
                    <div class="text-xs text-gray-500">${(file.size / 1024).toFixed(1)} KB</div>
                </div>
            </div>
            <button type="button" onclick="removeAttachment(${index})" class="text-red-600 hover:text-red-800 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
    }

    document.getElementById('email-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const sendType = document.querySelector('input[name="sendType"]:checked').value;
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('email-editor').innerHTML.trim();
        const sendOption = document.querySelector('input[name="sendOption"]:checked').value;

        if (!subject) {
            showMessageModal('Missing Subject', 'Please enter an email subject', 'error');
            return;
        }

        if (!message || message === '<br>' || message === '<div><br></div>') {
            showMessageModal('Missing Message', 'Please enter a message', 'error');
            return;
        }

        let recipients = [];
        if (sendType === 'single') {
            const recipient = document.getElementById('recipient').value.trim();
            if (!recipient) {
                showMessageModal('Missing Recipient', 'Please enter a recipient email', 'error');
                return;
            }
            if (!validateEmail(recipient)) {
                showMessageModal('Invalid Email', 'Please enter a valid email address', 'error');
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

            scheduledAt = new Date(`${scheduleDate}T${scheduleTime}`).toISOString();
        }

        const emailRecord = {
            id: Date.now(),
            subject: subject,
            message: message,
            recipients: recipients,
            type: sendType,
            status: sendOption === 'schedule' ? 'scheduled' : 'sent',
            sentAt: sendOption === 'schedule' ? null : new Date().toISOString(),
            scheduledAt: scheduledAt,
            openedAt: null,
            attachments: attachedFiles.map(file => file.name)
        };

        emailHistory.unshift(emailRecord);
        saveData();
        updateStats();

        document.getElementById('email-form').reset();
        document.getElementById('email-editor').innerHTML = '';
        document.querySelector('input[name="sendType"][value="single"]').checked = true;
        document.querySelector('input[name="sendOption"][value="now"]').checked = true;
        bulkRecipients = [];
        attachedFiles = [];
        toggleSendType();
        toggleSchedule();
        renderAttachmentList();

        showMessageModal('Success', `Email ${sendOption === 'schedule' ? 'scheduled' : 'sent'} successfully!`, 'success');
    });

    function showTemplateSelector() {
        const modal = document.getElementById('templateSelectorModal');
        const list = document.getElementById('template-selector-list');

        if (emailTemplates.length === 0) {
            list.innerHTML = '<p class="text-center text-gray-500">No templates available</p>';
        } else {
            list.innerHTML = emailTemplates.map(template => `
            <button onclick="selectTemplate(${template.id})" class="w-full text-left p-4 border border-gray-200 rounded-lg hover:border-primary/30 hover:bg-primary/5 transition-all">
                <div class="font-medium text-gray-900">${template.name}</div>
                <div class="text-sm text-gray-600 mt-1">${template.subject}</div>
                <div class="text-xs text-gray-500 mt-2">${template.message.replace(/<[^>]*>/g, '').substring(0, 100)}...</div>
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

    function selectTemplate(templateId) {
        const template = emailTemplates.find(t => t.id === templateId);
        if (template) {
            document.getElementById('subject').value = template.subject;
            document.getElementById('email-editor').innerHTML = template.message;
        }
        hideTemplateSelector();
    }

    function renderTemplates() {
        const grid = document.getElementById('templates-grid');
        const emptyState = document.getElementById('templates-empty-state');

        if (emailTemplates.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');

        grid.innerHTML = emailTemplates.map(template => `
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-purple-600"></i>
                    </div>
                    <h4 class="font-semibold text-gray-900">${template.name}</h4>
                </div>
                <div class="flex gap-1">
                    <button onclick="editTemplate(${template.id})" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" title="Edit Template">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="deleteTemplate(${template.id})" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" title="Delete Template">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-900 mb-2">${template.subject}</div>
                <div class="text-sm text-gray-600">${template.message.replace(/<[^>]*>/g, '').substring(0, 100)}...</div>
            </div>
            <div class="flex gap-2">
                <button onclick="selectTemplate(${template.id}); switchTab('send')" class="flex-1 px-3 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
                    <i class="fas fa-paper-plane mr-1"></i>Use Template
                </button>
            </div>
        </div>
            `).join('');
    }

    function filterTemplates() {
        const query = document.getElementById('searchTemplates').value.toLowerCase();
        const filteredTemplates = emailTemplates.filter(template =>
            template.name.toLowerCase().includes(query) ||
            template.subject.toLowerCase().includes(query) ||
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
                    <button onclick="editTemplate(${template.id})" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors flex items-center justify-center" title="Edit Template">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button onclick="deleteTemplate(${template.id})" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" title="Delete Template">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <div class="text-sm font-medium text-gray-900 mb-2">${template.subject}</div>
                <div class="text-sm text-gray-600">${template.message.replace(/<[^>]*>/g, '').substring(0, 100)}...</div>
            </div>
            <div class="flex gap-2">
                <button onclick="selectTemplate(${template.id}); switchTab('send')" class="flex-1 px-3 py-2 bg-primary/10 text-primary rounded-lg hover:bg-primary/20 transition-colors text-sm font-medium">
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
        document.getElementById('template-editor').innerHTML = '';
        document.getElementById('templateModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function editTemplate(templateId) {
        const template = emailTemplates.find(t => t.id === templateId);
        if (!template) return;

        document.getElementById('template-modal-title').textContent = 'Edit Template';
        document.getElementById('template-id').value = template.id;
        document.getElementById('template-name').value = template.name;
        document.getElementById('template-subject').value = template.subject;
        document.getElementById('template-editor').innerHTML = template.message;
        document.getElementById('templateModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideTemplateModal() {
        document.getElementById('templateModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('template-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const templateId = document.getElementById('template-id').value;
        const name = document.getElementById('template-name').value.trim();
        const subject = document.getElementById('template-subject').value.trim();
        const message = document.getElementById('template-editor').innerHTML.trim();

        if (!name || !subject || !message) {
            showMessageModal('Missing Fields', 'Please fill in all fields', 'error');
            return;
        }

        if (templateId) {
            const templateIndex = emailTemplates.findIndex(t => t.id === parseInt(templateId));
            if (templateIndex !== -1) {
                emailTemplates[templateIndex] = {
                    ...emailTemplates[templateIndex],
                    name: name,
                    subject: subject,
                    message: message
                };
            }
        } else {
            const newTemplate = {
                id: Date.now(),
                name: name,
                subject: subject,
                message: message,
                createdAt: new Date().toISOString()
            };
            emailTemplates.unshift(newTemplate);
        }

        saveData();
        hideTemplateModal();
        renderTemplates();
        showMessageModal('Success', 'Template saved successfully!', 'success');
    });

    function deleteTemplate(templateId) {
        showConfirmModal('Are you sure you want to delete this template?', () => {
            emailTemplates = emailTemplates.filter(t => t.id !== templateId);
            saveData();
            renderTemplates();
            showMessageModal('Success', 'Template deleted successfully!', 'success');
        });
    }

    function getDateRange(range) {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        switch (range) {
            case 'today':
                return {
                    start: today,
                    end: new Date(today.getTime() + 24 * 60 * 60 * 1000 - 1)
                };
            case 'week':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 7);
                return { start: weekStart, end: weekEnd };
            case 'month':
                const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                const monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0, 23, 59, 59);
                return { start: monthStart, end: monthEnd };
            default:
                return null;
        }
    }

    function handleDateRangeChange() {
        const range = document.getElementById('dateRangeFilter').value;
        const customRange = document.getElementById('custom-date-range');

        if (range === 'custom') {
            customRange.classList.remove('hidden');
        } else {
            customRange.classList.add('hidden');
            filterHistory();
        }
    }

    function renderEmailHistory() {
        const tableBody = document.getElementById('history-table-body');
        const mobileContainer = document.getElementById('history-mobile');
        const emptyState = document.getElementById('history-empty-state');

        let filteredHistory = [...emailHistory];

        const query = document.getElementById('searchHistory').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const dateRange = document.getElementById('dateRangeFilter').value;

        if (query) {
            filteredHistory = filteredHistory.filter(email =>
                email.subject.toLowerCase().includes(query) ||
                email.recipients.some(r => r.toLowerCase().includes(query))
            );
        }

        if (statusFilter) {
            filteredHistory = filteredHistory.filter(email => email.status === statusFilter);
        }

        if (dateRange !== 'custom') {
            const range = getDateRange(dateRange);
            if (range) {
                filteredHistory = filteredHistory.filter(email => {
                    const emailDate = new Date(email.sentAt || email.scheduledAt);
                    return emailDate >= range.start && emailDate < range.end;
                });
            }
        } else {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                end.setHours(23, 59, 59, 999);

                filteredHistory = filteredHistory.filter(email => {
                    const emailDate = new Date(email.sentAt || email.scheduledAt);
                    return emailDate >= start && emailDate <= end;
                });
            }
        }

        if (filteredHistory.length === 0) {
            tableBody.innerHTML = '';
            mobileContainer.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');

        tableBody.innerHTML = filteredHistory.map((email, index) => {
            const statusBadge = getStatusBadge(email.status);
            const recipientText = email.recipients.length === 1 ? email.recipients[0] : `${email.recipients.length} recipients`;
            const dateText = email.status === 'scheduled' ?
                `Scheduled: ${formatDateTime(email.scheduledAt)}` :
                formatDateTime(email.sentAt);
            const openRate = email.recipients.length > 0 && email.openedAt ? '100%' : '0%';

            return `
            <tr class="${index % 2 === 0 ? 'bg-gray-50' : 'bg-white'} hover:bg-blue-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="max-w-xs">
                        <div class="text-sm font-medium text-gray-900 truncate" title="${email.subject}">
                            ${email.subject.substring(0, 50)}${email.subject.length > 50 ? '...' : ''}
                        </div>
                        ${email.attachments.length > 0 ? `<div class="text-xs text-gray-500 mt-1"><i class="fas fa-paperclip mr-1"></i>${email.attachments.length} attachment${email.attachments.length > 1 ? 's' : ''}</div>` : ''}
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">${recipientText}</div>
                    ${email.type === 'bulk' ? `<div class="text-xs text-gray-500 mt-1">Bulk Email</div>` : ''}
                </td>
                <td class="px-4 py-3">${statusBadge}</td>
                <td class="px-4 py-3">
                    <div class="text-sm font-semibold text-gray-900">${openRate}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">${dateText}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick="showEmailDetails(${email.id})" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors flex items-center justify-center" title="View Details">
                            <i class="fas fa-eye text-xs"></i>
                        </button>
                        ${email.status === 'scheduled' ? `
                            <button onclick="editScheduledEmail(${email.id})" class="w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors flex items-center justify-center" title="Edit">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <button onclick="deleteScheduledEmail(${email.id})" class="w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors flex items-center justify-center" title="Delete">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        ` : ''}
                        ${email.status === 'sent' && !email.openedAt ? `
                            <button onclick="markAsOpened(${email.id})" class="w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors flex items-center justify-center" title="Mark as Opened">
                                <i class="fas fa-envelope-open text-xs"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
            `;
        }).join('');

        mobileContainer.innerHTML = filteredHistory.map(email => {
            const statusBadge = getStatusBadge(email.status);
            const recipientText = email.recipients.length === 1 ? email.recipients[0] : `${email.recipients.length} recipients`;
            const dateText = email.status === 'scheduled' ?
                `Scheduled: ${formatDateTime(email.scheduledAt)}` :
                formatDateTime(email.sentAt);
            const openRate = email.recipients.length > 0 && email.openedAt ? '100%' : '0%';

            return `
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 mb-2">${email.subject}</div>
                        ${email.attachments.length > 0 ? `<div class="text-xs text-gray-500"><i class="fas fa-paperclip mr-1"></i>${email.attachments.length} attachment${email.attachments.length > 1 ? 's' : ''}</div>` : ''}
                    </div>
                    ${statusBadge}
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-xs mb-4">
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Recipients</span>
                        <div class="font-medium text-gray-900 mt-1">${recipientText}</div>
                    </div>
                    <div>
                        <span class="text-gray-500 uppercase tracking-wide">Open Rate</span>
                        <div class="font-semibold text-gray-900 mt-1">${openRate}</div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-xs text-gray-500">${dateText}</div>
                    <div class="flex gap-2">
                        <button onclick="showEmailDetails(${email.id})" class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors font-medium">
                            <i class="fas fa-eye mr-1"></i>Details
                        </button>
                        ${email.status === 'scheduled' ? `
                            <button onclick="editScheduledEmail(${email.id})" class="px-3 py-2 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteScheduledEmail(${email.id})" class="px-3 py-2 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-medium">
                                <i class="fas fa-trash-alt mr-1"></i>Delete
                            </button>
                        ` : ''}
                        ${email.status === 'sent' && !email.openedAt ? `
                            <button onclick="markAsOpened(${email.id})" class="px-3 py-2 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors font-medium">
                                <i class="fas fa-envelope-open mr-1"></i>Mark Opened
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    function getStatusBadge(status) {
        const badges = {
            'sent': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Sent</span>',
            'opened': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Opened</span>',
            'scheduled': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Scheduled</span>',
            'failed': '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>'
        };
        return badges[status] || '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Unknown</span>';
    }

    function filterHistory() {
        renderEmailHistory();
    }

    function showEmailDetails(emailId) {
        const email = emailHistory.find(e => e.id === emailId);
        if (!email) return;

        const modal = document.getElementById('emailDetailsModal');
        const content = document.getElementById('email-details-content');

        const dateText = email.status === 'scheduled' ?
            `Scheduled for: ${formatDateTime(email.scheduledAt)}` :
            `Sent on: ${formatDateTime(email.sentAt)}`;

        const openedText = email.openedAt ? `Opened on: ${formatDateTime(email.openedAt)}` : 'Not opened yet';

        content.innerHTML = `
            <div class="space-y-6">
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Subject</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-900 font-medium">${email.subject}</p>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Message</h4>
                <div class="bg-gray-50 rounded-lg p-4 max-h-60 overflow-y-auto">
                    <div class="text-gray-900">${email.message}</div>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Recipients (${email.recipients.length})</h4>
                <div class="bg-gray-50 rounded-lg p-4 max-h-40 overflow-y-auto">
                    ${email.recipients.map(recipient => `
                        <div class="flex items-center gap-2 py-1">
                            <i class="fas fa-envelope text-gray-400 text-xs"></i>
                            <span class="text-gray-900">${recipient}</span>
                        </div>
                    `).join('')}
                </div>
            </div>

            ${email.attachments.length > 0 ? `
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Attachments (${email.attachments.length})</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        ${email.attachments.map(attachment => `
                            <div class="flex items-center gap-2 py-1">
                                <i class="fas fa-paperclip text-gray-400 text-xs"></i>
                                <span class="text-gray-900">${attachment}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Status</h4>
                    ${getStatusBadge(email.status)}
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Open Status</h4>
                    <div class="text-sm text-gray-900">${openedText}</div>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Date & Time</h4>
                <div class="text-gray-900">${dateText}</div>
            </div>
        </div>
            `;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEmailDetails() {
        document.getElementById('emailDetailsModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function editScheduledEmail(emailId) {
        const email = emailHistory.find(e => e.id === emailId);
        if (!email || email.status !== 'scheduled') return;

        const modal = document.getElementById('editScheduledModal');
        const scheduledDate = new Date(email.scheduledAt);

        document.getElementById('edit-email-id').value = email.id;
        document.getElementById('edit-email-subject').value = email.subject;
        document.getElementById('edit-email-editor').innerHTML = email.message;
        document.getElementById('edit-schedule-date').value = scheduledDate.toISOString().split('T')[0];
        document.getElementById('edit-schedule-time').value = scheduledDate.toTimeString().slice(0, 5);

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideEditScheduled() {
        document.getElementById('editScheduledModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('edit-scheduled-form').addEventListener('submit', function (e) {
        e.preventDefault();

        const emailId = parseInt(document.getElementById('edit-email-id').value);
        const subject = document.getElementById('edit-email-subject').value.trim();
        const message = document.getElementById('edit-email-editor').innerHTML.trim();
        const scheduleDate = document.getElementById('edit-schedule-date').value;
        const scheduleTime = document.getElementById('edit-schedule-time').value;

        if (!subject || !message || !scheduleDate || !scheduleTime) {
            showMessageModal('Missing Fields', 'Please fill in all fields', 'error');
            return;
        }

        const emailIndex = emailHistory.findIndex(e => e.id === emailId);
        if (emailIndex !== -1) {
            const scheduledAt = new Date(`${scheduleDate}T${scheduleTime}`).toISOString();

            emailHistory[emailIndex] = {
                ...emailHistory[emailIndex],
                subject: subject,
                message: message,
                scheduledAt: scheduledAt
            };

            saveData();
            hideEditScheduled();
            renderEmailHistory();
            showMessageModal('Success', 'Scheduled email updated successfully!', 'success');
        }
    });

    function deleteScheduledEmail(emailId) {
        showConfirmModal('Are you sure you want to delete this scheduled email?', () => {
            emailHistory = emailHistory.filter(e => e.id !== emailId);
            saveData();
            updateStats();
            renderEmailHistory();
            showMessageModal('Success', 'Scheduled email deleted successfully!', 'success');
        });
    }

    function markAsOpened(emailId) {
        const emailIndex = emailHistory.findIndex(e => e.id === emailId);
        if (emailIndex !== -1) {
            emailHistory[emailIndex].status = 'opened';
            emailHistory[emailIndex].openedAt = new Date().toISOString();
            saveData();
            updateStats();
            renderEmailHistory();
            showMessageModal('Success', 'Email marked as opened!', 'success');
        }
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

    function showConfirmModal(message, onConfirm) {
        const modal = document.getElementById('confirmModal');
        const textEl = document.getElementById('confirm-modal-text');
        const actionBtn = document.getElementById('confirm-modal-action');

        textEl.textContent = message;

        actionBtn.onclick = () => {
            hideConfirmModal();
            onConfirm();
        };

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        initializeDummyData();
        updateStats();
        switchTab('send');

        document.getElementById('mobile-tab-toggle').addEventListener('click', toggleMobileTabDropdown);

        document.querySelectorAll('.mobile-tab-option').forEach(option => {
            option.addEventListener('click', (e) => {
                const tab = e.currentTarget.getAttribute('data-tab');
                switchTab(tab);
                toggleMobileTabDropdown();
            });
        });

        document.getElementById('recipient').addEventListener('input', updateSendFormCalculations);

        document.getElementById('bulk-email-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBulkRecipient();
            }
        });

        document.getElementById('add-email-btn').addEventListener('click', addBulkRecipient);

        document.addEventListener('click', function (event) {
            const mobileDropdown = document.getElementById('mobile-tab-dropdown');
            const mobileToggle = document.getElementById('mobile-tab-toggle');

            if (mobileDropdown && mobileToggle && !mobileDropdown.contains(event.target) && !mobileToggle.contains(event.target)) {
                mobileDropdown.classList.add('hidden');
                document.getElementById('mobile-tab-chevron').classList.remove('rotate-180');
            }
        });

        const emailEditor = document.getElementById('email-editor');
        emailEditor.addEventListener('paste', function (e) {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
        });
    });

    window.switchTab = switchTab;
    window.toggleSendType = toggleSendType;
    window.toggleSchedule = toggleSchedule;
    window.showTemplateSelector = showTemplateSelector;
    window.hideTemplateSelector = hideTemplateSelector;
    window.selectTemplate = selectTemplate;
    window.showCreateTemplateForm = showCreateTemplateForm;
    window.editTemplate = editTemplate;
    window.deleteTemplate = deleteTemplate;
    window.hideTemplateModal = hideTemplateModal;
    window.filterHistory = filterHistory;
    window.showEmailDetails = showEmailDetails;
    window.hideEmailDetails = hideEmailDetails;
    window.filterTemplates = filterTemplates;
    window.resetEmailData = resetEmailData;
    window.handleDateRangeChange = handleDateRangeChange;
    window.editScheduledEmail = editScheduledEmail;
    window.hideEditScheduled = hideEditScheduled;
    window.deleteScheduledEmail = deleteScheduledEmail;
    window.markAsOpened = markAsOpened;
    window.hideMessageModal = hideMessageModal;
    window.hideConfirmModal = hideConfirmModal;
    window.addBulkRecipient = addBulkRecipient;
    window.removeRecipient = removeRecipient;
    window.formatText = formatText;
    window.formatTemplateText = formatTemplateText;
    window.formatEditText = formatEditText;
    window.handleFileSelect = handleFileSelect;
    window.removeAttachment = removeAttachment;
</script>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>