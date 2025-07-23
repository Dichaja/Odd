<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Active Sessions Monitor';
$activeNav = 'sessions';
ob_start();
?>

<div class="min-h-screen bg-gray-50" id="app-container">
    <div class="bg-white border-b border-gray-200 sm:px-6 lg:px-8 py-3 sm:py-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                <div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h1 class="text-lg sm:text-2xl font-bold text-gray-900">Sessions Monitor</h1>
                        <div id="connectionStatus"
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-green-700">Live Polling</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor real-time user activity
                        and session events</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-blue-600 uppercase tracking-wide">Total Sessions</p>
                        <p class="text-xl font-bold text-blue-900 truncate" id="totalSessions">0</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-green-600 uppercase tracking-wide">Active Sessions</p>
                        <p class="text-xl font-bold text-green-900 truncate" id="activeSessions">0</p>
                    </div>
                    <div class="w-10 h-10 bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-circle text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-purple-600 uppercase tracking-wide">Logged Users</p>
                        <p class="text-xl font-bold text-purple-900 truncate" id="loggedUsers">0</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-check text-purple-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-orange-600 uppercase tracking-wide">Countries</p>
                        <p class="text-xl font-bold text-orange-900 truncate" id="uniqueCountries">0</p>
                    </div>
                    <div class="w-10 h-10 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-globe text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Time Period</h2>
                    <p class="text-sm text-gray-600">Filter sessions by time range</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="daily">
                            <i class="fas fa-calendar-day mr-2"></i>Daily
                        </button>
                        <button
                            class="date-filter-btn flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="weekly">
                            <i class="fas fa-calendar-week mr-2"></i>Weekly
                        </button>
                        <button
                            class="date-filter-btn active flex-1 sm:flex-none px-4 py-2 rounded-lg border transition-colors text-sm"
                            data-period="monthly">
                            <i class="fas fa-calendar-alt mr-2"></i>Monthly
                        </button>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <span class="text-gray-500 text-center sm:text-left">to</span>
                        <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <button id="applyCustomRange"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Sessions Monitor</h3>
                        <p class="text-sm text-gray-600">Click on any session to view detailed activity logs</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="relative">
                            <input type="text" id="searchFilter" placeholder="Search sessions..."
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        </div>
                        <select id="countryFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Countries</option>
                        </select>
                        <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="all">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="expired">Expired Only</option>
                            <option value="logged">Logged Users</option>
                            <option value="guest">Guests</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full" id="sessionsTable">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Session Info</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Location</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Device</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                User Status</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Duration</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Events</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Last Activity</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                <div>Loading sessions...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> sessions
                </div>
                <div class="flex items-center gap-2">
                    <button id="prevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="pageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="nextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>

            <div class="lg:hidden" id="sessionsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading sessions...</div>
                </div>
            </div>

            <div
                class="lg:hidden p-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 text-center sm:text-left">
                    Showing <span id="mobileShowingCount">0</span> of <span id="mobileTotalCount">0</span> sessions
                </div>
                <div class="flex items-center gap-2">
                    <button id="mobilePrevPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Previous
                    </button>
                    <span id="mobilePageInfo" class="px-3 py-1 text-sm text-gray-600">Page 1 of 1</span>
                    <button id="mobileNextPage"
                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50"
                        disabled>
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sessionModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeSessionModal()"></div>
    <div
        class="relative w-full h-full max-w-6xl mx-auto top-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg max-h-[95vh] overflow-hidden">
        <div
            class="p-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Session Activity</h3>
                    <p class="text-sm text-gray-600 mt-1" id="modalSubtitle">Real-time monitoring</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div id="modalStatusIndicator"
                    class="flex items-center gap-2 px-3 py-1 bg-green-100 border border-green-200 rounded-full">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-green-700">Live</span>
                </div>
                <button onclick="closeSessionModal()"
                    class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-white/50">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <div class="flex h-[calc(95vh-120px)]">
            <div class="w-80 border-r border-gray-200 bg-gray-50 overflow-y-auto">
                <div class="p-4" id="sessionInfo">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500 text-sm">Loading...</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                <div id="newEventIndicator" class="hidden bg-blue-50 border-b border-blue-200 p-2 text-center">
                    <button onclick="scrollToBottom()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-arrow-down mr-1"></i>
                        New activity available
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="activityFeed">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading activity feed...</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-4 bg-white" id="chatSection">
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <input type="text" id="chatInput" placeholder="Send a message to the user..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                maxlength="500">
                        </div>
                        <button id="sendChatBtn"
                            class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane text-sm"></i>
                            <span>Send</span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                        <span>Messages are sent in real-time</span>
                        <span id="charCount">0/500</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mobileSessionModal" class="fixed inset-0 z-50 hidden lg:hidden">
    <div class="absolute inset-0 bg-white">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="closeMobileSessionModal()" class="text-white hover:text-blue-100">
                    <i class="fas fa-arrow-left text-lg"></i>
                </button>
                <div>
                    <h3 class="font-bold text-lg" id="mobileModalTitle">Session</h3>
                    <p class="text-blue-100 text-sm" id="mobileModalSubtitle">Live monitoring</p>
                </div>
            </div>
            <div id="mobileModalStatusIndicator" class="flex items-center gap-2 px-2 py-1 bg-green-500 rounded-full">
                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                <span class="text-xs font-medium">Live</span>
            </div>
        </div>

        <div class="flex flex-col h-[calc(100vh-80px)]">
            <div class="flex flex-col h-full bg-gray-50">
                <div id="mobileNewEventIndicator"
                    class="hidden bg-blue-50 border-b border-blue-200 p-2 text-center sticky top-0 z-10">
                    <button onclick="scrollToBottomMobile()"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-arrow-down mr-1"></i>
                        New activity available
                    </button>
                </div>
                <div id="mobileActivityFeed"
                    class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50 h-[calc(100%-120px)]">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading activity feed...</p>
                    </div>
                </div>
                <div class="border-t border-gray-200 p-4 bg-white sticky bottom-0 z-10 h-[120px]"
                    id="mobileChatSection">
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <input type="text" id="mobileChatInput" placeholder="Type a message..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                maxlength="500">
                        </div>
                        <button id="mobileSendChatBtn"
                            class="w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2 text-xs text-gray-500">
                        <span>Real-time messaging</span>
                        <span id="mobileCharCount">0/500</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<audio id="newSessionSound" preload="auto">
    <source src="../sounds/new.wav" type="audio/wav">
</audio>

<audio id="newEventSound" preload="auto">
    <source src="../sounds/chat.wav" type="audio/wav">
</audio>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let sessions = [];
    let currentSessionId = null;
    let sessionEventSource = null;
    let pollingInterval = null;
    let leafletMaps = {};
    let isPolling = false;
    let currentPage = 1;
    let itemsPerPage = 20;
    let currentPeriod = 'monthly';
    let totalSessions = 0;
    let previousSessionIds = new Set();

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        initializeDateFilters();
        startPolling();
    });

    function setupEventListeners() {
        document.getElementById('refreshBtn').addEventListener('click', refreshSessions);
        document.getElementById('searchFilter').addEventListener('input', debounce(filterSessions, 300));
        document.getElementById('countryFilter').addEventListener('change', filterSessions);
        document.getElementById('statusFilter').addEventListener('change', filterSessions);

        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendChatBtn');
        const charCount = document.getElementById('charCount');

        if (chatInput && sendBtn && charCount) {
            chatInput.addEventListener('input', function () {
                const length = this.value.length;
                charCount.textContent = `${length}/500`;
                sendBtn.disabled = length === 0;
            });

            chatInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendChatMessage();
                }
            });

            sendBtn.addEventListener('click', sendChatMessage);
        }

        const mobileChatInput = document.getElementById('mobileChatInput');
        const mobileSendBtn = document.getElementById('mobileSendChatBtn');
        const mobileCharCount = document.getElementById('mobileCharCount');

        if (mobileChatInput && mobileSendBtn && mobileCharCount) {
            mobileChatInput.addEventListener('input', function () {
                const length = this.value.length;
                mobileCharCount.textContent = `${length}/500`;
                mobileSendBtn.disabled = length === 0;
            });

            mobileChatInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMobileChatMessage();
                }
            });

            mobileSendBtn.addEventListener('click', sendMobileChatMessage);
        }

        document.querySelectorAll('.date-filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-blue-600', 'text-white', 'border-blue-600');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                this.classList.add('active', 'bg-blue-600', 'text-white', 'border-blue-600');
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');

                const period = this.dataset.period;
                setDateRangeForPeriod(period);
            });
        });

        document.getElementById('applyCustomRange').addEventListener('click', function () {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (startDate && endDate) {
                document.querySelectorAll('.date-filter-btn').forEach(b => {
                    b.classList.remove('active', 'bg-blue-600', 'text-white', 'border-blue-600');
                    b.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });

                currentPeriod = 'custom';
                currentPage = 1;
                loadSessionsData();
            }
        });

        document.getElementById('prevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadSessionsData();
            }
        });

        document.getElementById('nextPage').addEventListener('click', function () {
            currentPage++;
            loadSessionsData();
        });

        document.getElementById('mobilePrevPage').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadSessionsData();
            }
        });

        document.getElementById('mobileNextPage').addEventListener('click', function () {
            currentPage++;
            loadSessionsData();
        });
    }

    function startPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }

        isPolling = true;
        updateConnectionStatus('polling');

        pollingInterval = setInterval(() => {
            if (!currentSessionId) {
                loadSessionsData();
            }
        }, 5000);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        isPolling = false;
    }

    function startSessionStream(sessionId) {
        if (sessionEventSource) {
            sessionEventSource.close();
        }

        updateConnectionStatus('streaming');

        sessionEventSource = new EventSource(`fetch/manageSessions.php?action=stream&session_id=${sessionId}`);

        sessionEventSource.onopen = function () {
            updateConnectionStatus('streaming');
        };

        sessionEventSource.onmessage = function (event) {
            try {
                const data = JSON.parse(event.data);

                if (data.type === 'session_update' && data.session_id === currentSessionId) {
                    // Store the previous session data for comparison
                    const sessionIndex = sessions.findIndex(s => s.sessionID === data.session_id);
                    const previousSession = sessionIndex !== -1 ? { ...sessions[sessionIndex] } : null;

                    // Update the session in the sessions array
                    if (sessionIndex !== -1) {
                        sessions[sessionIndex] = data.data;
                    } else {
                        // If session not found in array, add it
                        sessions.push(data.data);
                    }

                    // Check for new events and play notification sound
                    if (previousSession && data.data.logs && previousSession.logs) {
                        if (data.data.logs.length > previousSession.logs.length) {
                            playNewEventSound();

                            // Show new event indicator if user is not at bottom
                            const activityFeed = document.getElementById('activityFeed');
                            const mobileActivityFeed = document.getElementById('mobileActivityFeed');

                            if (activityFeed && activityFeed.scrollTop + activityFeed.clientHeight < activityFeed.scrollHeight - 10) {
                                document.getElementById('newEventIndicator').classList.remove('hidden');
                            }

                            if (mobileActivityFeed && mobileActivityFeed.scrollTop + mobileActivityFeed.clientHeight < mobileActivityFeed.scrollHeight - 10) {
                                document.getElementById('mobileNewEventIndicator').classList.remove('hidden');
                            }
                        }
                    }

                    // Load the updated session details directly with the new data
                    if (window.innerWidth < 1024) {
                        loadMobileSessionDetails(currentSessionId, false, data.data);
                    } else {
                        loadSessionDetails(currentSessionId, false, data.data);
                    }
                } else if (data.type === 'session_not_found') {
                    console.log('Session not found:', data.session_id);
                    // Optionally close the modal or show an error
                } else if (data.type === 'heartbeat') {
                    // Update connection status to show it's still alive
                    updateConnectionStatus('streaming');
                }
            } catch (error) {
                console.error('Error parsing session stream data:', error, event.data);
            }
        };

        sessionEventSource.onerror = function (error) {
            console.error('Session stream error for:', sessionId, error);
            updateConnectionStatus('error');

            // Retry connection after a delay
            setTimeout(() => {
                if (currentSessionId === sessionId) {
                    console.log('Retrying session stream for:', sessionId);
                    startSessionStream(sessionId);
                }
            }, 3000);
        };

        sessionEventSource.onclose = function () {
        };
    }

    function stopSessionStream() {
        if (sessionEventSource) {
            sessionEventSource.close();
            sessionEventSource = null;
        }
        updateConnectionStatus('polling');
    }

    function updateConnectionStatus(status) {
        const statusElement = document.getElementById('connectionStatus');

        switch (status) {
            case 'polling':
                statusElement.innerHTML = `
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-xs sm:text-sm font-medium text-green-700">Live Polling</span>
            `;
                statusElement.className = 'flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg';
                break;

            case 'streaming':
                statusElement.innerHTML = `
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-xs sm:text-sm font-medium text-blue-700">Live Stream</span>
            `;
                statusElement.className = 'flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-blue-50 border border-blue-200 rounded-lg';
                break;

            case 'error':
                statusElement.innerHTML = `
                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                <span class="text-xs sm:text-sm font-medium text-red-700">Disconnected</span>
            `;
                statusElement.className = 'flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-red-50 border border-red-200 rounded-lg';
                break;
        }
    }

    async function loadInitialSessions() {
        await loadSessionsData();
    }

    async function loadSessionsData() {
        try {
            const params = new URLSearchParams({
                action: 'get',
                page: currentPage,
                limit: itemsPerPage,
                start_date: document.getElementById('startDate').value,
                end_date: document.getElementById('endDate').value,
                period: currentPeriod
            });

            const response = await fetch(`fetch/manageSessions.php?${params}`);
            const data = await response.json();

            if (data.success) {
                // Check for new sessions and play notification sound
                if (sessions.length > 0) {
                    const currentSessionIds = new Set(data.data.map(s => s.sessionID));
                    const newSessions = data.data.filter(s => !previousSessionIds.has(s.sessionID) && s.isActive);

                    if (newSessions.length > 0 && previousSessionIds.size > 0) {
                        playNewSessionSound();
                    }

                    // Update previous session IDs for next comparison
                    previousSessionIds = currentSessionIds;
                } else {
                    // Initialize with current sessions on first load
                    previousSessionIds = new Set(data.data.map(s => s.sessionID));
                }

                sessions = data.data;
                totalSessions = data.total;
                updateStatistics(data.stats);
                renderSessionsTable();
                renderSessionsCards();
                updateCountryFilter();
                updatePagination(data.total, data.page);
            } else {
                showError('Failed to load sessions');
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            showError('Failed to load sessions');
            updateConnectionStatus('error');
        }
    }

    function updateStatistics(stats = null) {
        if (stats) {
            document.getElementById('totalSessions').textContent = stats.total_sessions.toLocaleString();
            document.getElementById('activeSessions').textContent = stats.active_sessions.toLocaleString();
            document.getElementById('loggedUsers').textContent = stats.logged_users.toLocaleString();
            document.getElementById('uniqueCountries').textContent = stats.unique_countries.toLocaleString();
        } else {
            const totalSessions = sessions.length;
            const activeSessions = sessions.filter(s => s.isActive).length;
            const loggedUsers = sessions.filter(s => s.loggedUser !== null).length;
            const uniqueCountries = [...new Set(sessions.map(s => s.country))].length;

            document.getElementById('totalSessions').textContent = totalSessions.toLocaleString();
            document.getElementById('activeSessions').textContent = activeSessions.toLocaleString();
            document.getElementById('loggedUsers').textContent = loggedUsers.toLocaleString();
            document.getElementById('uniqueCountries').textContent = uniqueCountries.toLocaleString();
        }
    }

    function updateCountryFilter() {
        const countryFilter = document.getElementById('countryFilter');
        const countries = [...new Set(sessions.map(s => s.country))].sort();
        const currentValue = countryFilter.value;

        countryFilter.innerHTML = '<option value="all">All Countries</option>';
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country;
            option.textContent = country;
            countryFilter.appendChild(option);
        });

        if (countries.includes(currentValue)) {
            countryFilter.value = currentValue;
        }
    }

    function renderSessionsTable() {
        const tbody = document.getElementById('sessionsBody');

        if (sessions.length === 0) {
            tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <div>No sessions found</div>
                </td>
            </tr>
        `;
            return;
        }

        let filteredSessions = filterSessionsData();

        tbody.innerHTML = filteredSessions.map(session => {
            let displayName;
            if (session.loggedUser && session.loggedUser.username) {
                displayName = session.loggedUser.username;
            } else {
                displayName = session.sessionID.substring(0, 6) + '...';
            }

            const deviceIcon = getDeviceIcon(session.device);
            const lastActivity = session.isExpired ? formatDate(getLastActivity(session)) : getTimeAgo(getLastActivity(session));
            const lastActivityTime = formatTime(getLastActivity(session));

            return `
            <tr class="hover:bg-gray-50 transition-colors cursor-pointer ${session.isExpired ? 'opacity-60 bg-gray-50' : ''}" onclick="viewSessionDetails('${session.sessionID}')">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full ${session.isActive ? 'bg-green-100' : 'bg-gray-300'} flex items-center justify-center">
                            <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} ${session.isActive ? 'text-green-600' : 'text-gray-600'} text-sm"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${displayName}</div>
                            <div class="text-sm text-gray-500">${session.ipAddress === 'Fetching...' ? 'Unknown' : session.ipAddress}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                        ${session.country === 'Fetching...' || session.country === 'Unknown' ?
                    `<i class="fas fa-globe text-gray-500"></i>
                             <span class="text-sm text-gray-500">Unknown</span>` :
                    `<img src="https://flagcdn.com/16x12/${session.shortName}.png" alt="${session.country}" class="rounded">
                             <span class="text-sm text-gray-900">${session.country}</span>`
                }
                    </div>
                    <div class="text-xs text-gray-500">${session.phoneCode === 'Fetching...' ? 'N/A' : session.phoneCode}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                        <i class="${deviceIcon} text-gray-600"></i>
                        <span class="text-sm text-gray-900">${session.browser === 'Fetching...' ? 'Unknown' : session.browser}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    ${session.loggedUser ?
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-user-check mr-1"></i>Logged In
                        </span>` :
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-globe mr-1"></i>Guest
                        </span>`
                }
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="text-sm ${session.isActive ? 'text-green-600 font-medium' : 'text-gray-500'}">${session.activeDuration}</span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${session.logs ? session.logs.length : 0} events
                    </span>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="text-sm text-gray-900">${lastActivity}</div>
                    <div class="text-xs text-gray-500">${lastActivityTime}</div>
                </td>
            </tr>
        `;
        }).join('');
    }

    function renderSessionsCards() {
        const container = document.getElementById('sessionsCards');

        if (sessions.length === 0) {
            container.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-users text-2xl mb-2"></i>
                <div>No sessions found</div>
            </div>
        `;
            return;
        }

        let filteredSessions = filterSessionsData();

        container.innerHTML = filteredSessions.map(session => {
            const lastActivity = session.isExpired ? formatDate(getLastActivity(session)) : getTimeAgo(getLastActivity(session));
            let displayName;
            if (session.loggedUser && session.loggedUser.username) {
                displayName = session.loggedUser.username;
            } else {
                displayName = session.sessionID.substring(0, 6) + '...';
            }
            const deviceIcon = getDeviceIcon(session.device);

            return `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer ${session.isExpired ? 'opacity-60 bg-gray-50' : ''}" onclick="viewSessionDetails('${session.sessionID}')">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full ${session.isActive ? 'bg-green-100' : 'bg-gray-300'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} ${session.isActive ? 'text-green-600' : 'text-gray-600'} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-medium text-gray-900 truncate">${displayName}</h4>
                            <span class="text-xs text-gray-500">${lastActivity}</span>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            ${session.country === 'Fetching...' || session.country === 'Unknown' ?
                    `<i class="fas fa-globe text-gray-500"></i>
                                 <span class="text-sm text-gray-500">Unknown</span>` :
                    `<img src="https://flagcdn.com/16x12/${session.shortName}.png" alt="${session.country}" class="rounded">
                                 <span class="text-sm text-gray-600">${session.country}</span>`
                }
                            <span class="text-xs text-gray-500">•</span>
                            <span class="text-sm text-gray-600">${session.browser === 'Fetching...' ? 'Unknown' : session.browser}</span>
                            <span class="text-xs text-gray-500">•</span>
                            <i class="${deviceIcon} text-gray-600 text-sm"></i>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                ${session.loggedUser ?
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-user-check mr-1"></i>Logged In
                                    </span>` :
                    `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <i class="fas fa-globe mr-1"></i>Guest
                                    </span>`
                }
                                <span class="text-xs ${session.isActive ? 'text-green-600' : 'text-gray-500'}">${session.activeDuration}</span>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${session.logs ? session.logs.length : 0} events
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    }

    function filterSessionsData() {
        const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
        const countryFilter = document.getElementById('countryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        return sessions.filter(session => {
            const matchesSearch = !searchTerm ||
                session.sessionID.toLowerCase().includes(searchTerm) ||
                (session.ipAddress !== 'Fetching...' && session.ipAddress.includes(searchTerm)) ||
                (session.country !== 'Fetching...' && session.country.toLowerCase().includes(searchTerm)) ||
                (session.browser !== 'Fetching...' && session.browser.toLowerCase().includes(searchTerm)) ||
                (session.loggedUser && session.loggedUser.username && session.loggedUser.username.toLowerCase().includes(searchTerm));

            const matchesCountry = countryFilter === 'all' || session.country === countryFilter;

            let matchesStatus = true;
            switch (statusFilter) {
                case 'active':
                    matchesStatus = session.isActive;
                    break;
                case 'expired':
                    matchesStatus = session.isExpired;
                    break;
                case 'logged':
                    matchesStatus = session.loggedUser !== null;
                    break;
                case 'guest':
                    matchesStatus = session.loggedUser === null;
                    break;
            }

            return matchesSearch && matchesCountry && matchesStatus;
        });
    }

    function filterSessions() {
        renderSessionsTable();
        renderSessionsCards();
    }

    function getLastActivity(session) {
        if (!session.logs || session.logs.length === 0) {
            return new Date(session.timestamp).getTime();
        }

        const lastLog = session.logs[session.logs.length - 1];
        return new Date(lastLog.timestamp).getTime();
    }

    function getTimeAgo(timestamp) {
        const now = Date.now();
        const diff = now - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        return `${days}d ago`;
    }

    function formatDate(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatDateTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    function viewSessionDetails(sessionId) {
        currentSessionId = sessionId;

        stopPolling();
        startSessionStream(sessionId);

        if (window.innerWidth < 1024) {
            document.getElementById('mobileSessionModal').classList.remove('hidden');
            loadMobileSessionDetails(sessionId, true);
        } else {
            document.getElementById('sessionModal').classList.remove('hidden');
            loadSessionDetails(sessionId, true);
        }

        document.body.style.overflow = 'hidden';
    }

    function loadSessionDetails(sessionId, isInitialLoad = false, sessionData = null) {
        // Use provided session data or find it in the sessions array
        const session = sessionData || sessions.find(s => s.sessionID === sessionId);
        if (!session) {
            console.error('Session not found for ID:', sessionId);
            return;
        }

        if (isInitialLoad) {
            const modalTitle = session.loggedUser && session.loggedUser.username ?
                session.loggedUser.username :
                `Session: ${sessionId}`;

            document.getElementById('modalTitle').textContent = modalTitle;
            document.getElementById('modalSubtitle').textContent = `${session.country === 'Fetching...' ? 'Unknown' : session.country} • ${session.browser === 'Fetching...' ? 'Unknown' : session.browser} • ${session.device} • ${session.activeDuration}`;

            const statusIndicator = document.getElementById('modalStatusIndicator');
            if (session.isExpired) {
                statusIndicator.innerHTML = `
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-xs font-medium text-red-700">Expired</span>
                `;
                statusIndicator.className = 'flex items-center gap-2 px-3 py-1 bg-red-100 border border-red-200 rounded-full';
            } else {
                statusIndicator.innerHTML = `
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-green-700">Live</span>
                `;
                statusIndicator.className = 'flex items-center gap-2 px-3 py-1 bg-green-100 border border-green-200 rounded-full';
            }

            const chatSection = document.getElementById('chatSection');
            if (session.isExpired) {
                chatSection.style.display = 'none';
            } else {
                chatSection.style.display = 'block';
            }
        }

        if (isInitialLoad) {
            const sessionInfo = document.getElementById('sessionInfo');
            sessionInfo.innerHTML = `
            <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 mb-3 sm:mb-4">
                <h4 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Session Details</h4>
                <div class="space-y-2 text-xs sm:text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Session ID:</span>
                        <span class="font-mono text-xs">${session.sessionID.substring(0, 8)}...</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">IP Address:</span>
                        <span class="font-medium">${session.ipAddress === 'Fetching...' ? 'Unknown' : session.ipAddress}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Location:</span>
                        <div class="flex items-center gap-1">
                            ${session.country === 'Fetching...' || session.country === 'Unknown' ?
                    `<i class="fas fa-globe text-gray-500"></i>
                                 <span>Unknown</span>` :
                    `<img src="https://flagcdn.com/16x12/${session.shortName}.png" alt="${session.country}" class="rounded">
                                 <span>${session.country}</span>`
                }
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Browser:</span>
                        <span class="font-medium">${session.browser === 'Fetching...' ? 'Unknown' : session.browser}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Device:</span>
                        <div class="flex items-center gap-1">
                            <i class="${getDeviceIcon(session.device)} text-gray-600 text-xs"></i>
                            <span class="font-medium capitalize">${session.device}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Duration:</span>
                        <span class="font-medium ${session.isActive ? 'text-green-600' : 'text-gray-500'}">${session.activeDuration}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium ${session.isActive ? 'text-green-600' : 'text-red-600'}">${session.isActive ? 'Active' : session.isExpired ? 'Expired' : 'Inactive'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">User Status:</span>
                        ${session.loggedUser ?
                    '<span class="text-green-600 font-medium">Logged In</span>' :
                    '<span class="text-gray-600">Guest</span>'
                }
                    </div>
                    ${session.loggedUser ? `
                        <div class="pt-2 border-t border-gray-200">
                            <h5 class="font-medium text-gray-900 mb-2">User Information</h5>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Username:</span>
                                    <span class="font-medium">${session.loggedUser.username}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="font-medium">${session.loggedUser.email}</span>
                                </div>
                                ${session.loggedUser.phone ? `
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Phone:</span>
                                        <span class="font-medium">${session.loggedUser.phone}</span>
                                    </div>
                                ` : ''}
                                ${session.loggedUser.last_login ? `
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Last Login:</span>
                                        <span class="font-medium">${formatDateTime(new Date(session.loggedUser.last_login).getTime())}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>

            ${session.coords ? `
                <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm sm:text-base">Location</h4>
                    <div id="map-${sessionId}" class="w-full h-32 sm:h-48 rounded-lg border border-gray-200"></div>
                    <div class="mt-2 text-xs text-gray-500 text-center">
                        <a href="https://www.google.com/maps?q=${session.coords.latitude},${session.coords.longitude}" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800 underline">
                            ${session.coords.latitude.toFixed(6)}, ${session.coords.longitude.toFixed(6)}
                        </a>
                    </div>
                </div>
            ` : ''}
        `;

            if (session.coords) {
                setTimeout(() => initLeafletMap(sessionId, session.coords), 100);
            }
        }

        const activityFeed = document.getElementById('activityFeed');
        const wasAtBottom = activityFeed.scrollTop + activityFeed.clientHeight >= activityFeed.scrollHeight - 5;

        if (!session.logs || session.logs.length === 0) {
            activityFeed.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-clock text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">No activity recorded yet</p>
            </div>
        `;
            return;
        }

        const sortedLogs = [...session.logs].sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

        activityFeed.innerHTML = sortedLogs.map((log, index) => {
            const isLatest = index === sortedLogs.length - 1;
            return `
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full ${session.loggedUser ? 'bg-blue-500' : 'bg-gray-500'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-500">${formatDateTime(new Date(log.timestamp).getTime())}</span>
                            ${isLatest && !session.isExpired ? '<span class="text-xs text-green-600 font-medium">Latest</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-900">
                            ${formatEventMessage(log)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        if (isInitialLoad || wasAtBottom) {
            setTimeout(() => {
                activityFeed.scrollTop = activityFeed.scrollHeight;
            }, 100);
        } else if (!wasAtBottom && !session.isExpired) {
            document.getElementById('newEventIndicator').classList.remove('hidden');
        }
    }

    function loadMobileSessionDetails(sessionId, isInitialLoad = false, sessionData = null) {
        // Use provided session data or find it in the sessions array
        const session = sessionData || sessions.find(s => s.sessionID === sessionId);
        if (!session) {
            console.error('Mobile session not found for ID:', sessionId);
            return;
        }


        if (isInitialLoad) {
            const mobileTitle = session.loggedUser && session.loggedUser.username ?
                session.loggedUser.username :
                `${sessionId.substring(0, 8)}...`;

            document.getElementById('mobileModalTitle').textContent = mobileTitle;
            document.getElementById('mobileModalSubtitle').textContent = `${session.country === 'Fetching...' ? 'Unknown' : session.country} • ${session.browser === 'Fetching...' ? 'Unknown' : session.browser} • ${session.activeDuration}`;

            const mobileStatusIndicator = document.getElementById('mobileModalStatusIndicator');
            if (session.isExpired) {
                mobileStatusIndicator.innerHTML = `
                    <div class="w-2 h-2 bg-white rounded-full"></div>
                    <span class="text-xs font-medium">Expired</span>
                `;
                mobileStatusIndicator.className = 'flex items-center gap-2 px-2 py-1 bg-red-500 rounded-full';
            } else {
                mobileStatusIndicator.innerHTML = `
                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium">Live</span>
                `;
                mobileStatusIndicator.className = 'flex items-center gap-2 px-2 py-1 bg-green-500 rounded-full';
            }

            const mobileChatSection = document.getElementById('mobileChatSection');
            if (session.isExpired) {
                mobileChatSection.style.display = 'none';
            } else {
                mobileChatSection.style.display = 'block';
            }
        }

        const mobileActivityFeed = document.getElementById('mobileActivityFeed');
        const wasAtBottom = mobileActivityFeed.scrollTop + mobileActivityFeed.clientHeight >= mobileActivityFeed.scrollHeight - 5;

        if (!session.logs || session.logs.length === 0) {
            mobileActivityFeed.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-clock text-2xl text-gray-400 mb-2"></i>
                <p class="text-gray-500">No activity recorded yet</p>
            </div>
        `;
            return;
        }

        const sortedLogs = [...session.logs].sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

        mobileActivityFeed.innerHTML = sortedLogs.map((log, index) => {
            const isLatest = index === sortedLogs.length - 1;
            return `
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full ${session.loggedUser ? 'bg-blue-500' : 'bg-gray-500'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-500">${formatTime(new Date(log.timestamp).getTime())}</span>
                            ${isLatest && !session.isExpired ? '<span class="text-xs text-green-600 font-medium">Latest</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-900">
                            ${formatEventMessage(log)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        if (isInitialLoad || wasAtBottom) {
            setTimeout(() => {
                mobileActivityFeed.scrollTop = mobileActivityFeed.scrollHeight;
            }, 100);
        } else if (!wasAtBottom && !session.isExpired) {
            document.getElementById('mobileNewEventIndicator').classList.remove('hidden');
        }
    }

    function initLeafletMap(sessionId, coords) {
        const mapElement = document.getElementById(`map-${sessionId}`);
        if (!mapElement || typeof L === 'undefined') return;

        try {
            const map = L.map(mapElement).setView([coords.latitude, coords.longitude], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const customIcon = L.divIcon({
                html: '<i class="fas fa-map-marker-alt" style="color: #3B82F6; font-size: 24px;"></i>',
                iconSize: [24, 24],
                iconAnchor: [12, 24],
                className: 'custom-div-icon'
            });

            L.marker([coords.latitude, coords.longitude], { icon: customIcon })
                .addTo(map)
                .bindPopup(`User Location<br>Lat: ${coords.latitude.toFixed(6)}<br>Lng: ${coords.longitude.toFixed(6)}`);

            leafletMaps[sessionId] = map;

            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        } catch (error) {
            console.error('Error initializing Leaflet map:', error);
        }
    }

    function getDeviceIcon(device) {
        const icons = {
            'mobile': 'fas fa-mobile-alt',
            'tablet': 'fas fa-tablet-alt',
            'desktop': 'fas fa-desktop'
        };
        return icons[device] || 'fas fa-desktop';
    }

    function scrollToBottom() {
        const activityFeed = document.getElementById('activityFeed');
        activityFeed.scrollTop = activityFeed.scrollHeight;
        document.getElementById('newEventIndicator').classList.add('hidden');
    }

    function scrollToBottomMobile() {
        const mobileActivityFeed = document.getElementById('mobileActivityFeed');
        mobileActivityFeed.scrollTop = mobileActivityFeed.scrollHeight;
        document.getElementById('mobileNewEventIndicator').classList.add('hidden');
    }

    function formatEventMessage(log) {
        switch (log.event) {
            case 'page_load':
                return `<i class="fas fa-globe text-blue-500 mr-2"></i>Navigated to <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">${log.pageTitle || log.url}</a>`;

            case 'page_refresh':
                return `<i class="fas fa-sync-alt text-orange-500 mr-2"></i>Refreshed page: <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium">${log.pageTitle || log.url}</a>`;

            case 'login_modal_open':
                return '<i class="fas fa-sign-in-alt text-green-500 mr-2"></i>Opened login modal';

            case 'login_modal_close':
                return '<i class="fas fa-times text-gray-500 mr-2"></i>Closed login modal';

            case 'form_switch':
                return `<i class="fas fa-exchange-alt text-purple-500 mr-2"></i>Switched from <strong>${log.fromForm}</strong> to <strong>${log.toForm}</strong> form`;

            case 'login_identifier_submit':
                return `<i class="fas fa-user text-blue-500 mr-2"></i>Submitted identifier: <strong>${log.identifier}</strong> <span class="text-gray-500">(${log.identifierType})</span>`;

            case 'login_identifier_success':
                return `<i class="fas fa-check-circle text-green-500 mr-2"></i>✅ Identifier verified: <strong>${log.identifier}</strong> <span class="text-gray-500">(${log.identifierType})</span>`;

            case 'login_identifier_failed':
                return `<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>❌ Identifier failed: <strong>${log.identifier}</strong> <span class="text-gray-500">(${log.identifierType})</span><br><span class="text-red-600 text-xs">${log.errorMessage}</span>`;

            case 'login_password_submit':
                return '<i class="fas fa-key text-blue-500 mr-2"></i>Submitted password';

            case 'login_password_success':
                return '<i class="fas fa-check-circle text-green-500 mr-2"></i>✅ Password verified successfully';

            case 'login_password_failed':
                return `<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>❌ Password verification failed<br><span class="text-red-600 text-xs">${log.errorMessage}</span>`;

            case 'login_success':
                return '<i class="fas fa-user-check text-green-500 mr-2"></i>🎉 <strong>Login successful!</strong>';

            case 'register_username_submit':
                return `<i class="fas fa-user-plus text-blue-500 mr-2"></i>Submitted username: <strong>${log.username}</strong>`;

            case 'register_username_success':
                return `<i class="fas fa-check-circle text-green-500 mr-2"></i>✅ Username available: <strong>${log.username}</strong>`;

            case 'register_username_failed':
                return `<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>❌ Username unavailable: <strong>${log.username}</strong><br><span class="text-red-600 text-xs">${log.errorMessage}</span>`;

            case 'register_email_submit':
                return `<i class="fas fa-envelope text-blue-500 mr-2"></i>Submitted email: <strong>${log.email}</strong>`;

            case 'register_email_success':
                return `<i class="fas fa-check-circle text-green-500 mr-2"></i>✅ Email available: <strong>${log.email}</strong>`;

            case 'register_email_failed':
            case 'register_email_check_failed':
                return `<i class="fas fa-exclamation-circle text-red-500 mr-2"></i>❌ Email unavailable: <strong>${log.email}</strong><br><span class="text-red-600 text-xs">${log.errorMessage}</span>`;

            case 'password_reset_requested':
                return '<i class="fas fa-unlock-alt text-orange-500 mr-2"></i>Requested password reset';

            case 'otp_validation':
                const otpIcon = log.status === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500';
                const otpStatus = log.status === 'success' ? '✅ OTP verified' : '❌ OTP verification failed';
                return `<i class="${otpIcon} mr-2"></i>${otpStatus}`;

            case 'password_reset_completed':
                const resetIcon = log.status === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500';
                const resetStatus = log.status === 'success' ? '✅ Password reset completed' : '❌ Password reset failed';
                return `<i class="${resetIcon} mr-2"></i>${resetStatus}`;

            case 'registration_start':
                return '<i class="fas fa-user-plus text-blue-500 mr-2"></i>Started registration process';

            case 'registration_complete':
                const regIcon = log.status === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-exclamation-circle text-red-500';
                const regStatus = log.status === 'success' ? '🎉 <strong>Registration completed!</strong>' : '❌ Registration failed';
                return `<i class="${regIcon} mr-2"></i>${regStatus}`;

            case 'login_method_change':
                return `<i class="fas fa-exchange-alt text-purple-500 mr-2"></i>Changed login method to: <strong>${log.method}</strong>`;

            case 'click':
                return log.url ?
                    `<i class="fas fa-mouse-pointer text-gray-500 mr-2"></i>Clicked on "${log.element}" → <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.url}</a>` :
                    `<i class="fas fa-mouse-pointer text-gray-500 mr-2"></i>Clicked on "${log.element}"`;

            case 'navigation':
                return `<i class="fas fa-compass text-blue-500 mr-2"></i>Navigated to <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.pageTitle || log.url}</a>`;

            case 'scroll':
                return `<i class="fas fa-arrows-alt-v text-gray-500 mr-2"></i>Scrolled to position ${log.scrollPosition}px`;

            case 'search_query':
                return `<i class="fas fa-search text-blue-500 mr-2"></i>Searched for: "<strong>${log.query}</strong>"`;

            case 'add_to_cart':
                return `<i class="fas fa-shopping-cart text-green-500 mr-2"></i>Added ${log.quantity} item(s) to cart (Product ID: ${log.productId})`;

            case 'checkout_initiated':
                return `<i class="fas fa-credit-card text-orange-500 mr-2"></i>Started checkout process (Cart value: UGX ${log.cartValue?.toLocaleString()})`;

            case 'order_placed':
                return `<i class="fas fa-check-circle text-green-500 mr-2"></i>🎉 Placed order ${log.orderId} (Amount: UGX ${log.amount?.toLocaleString()})`;

            case 'product_view':
                return `<i class="fas fa-eye text-blue-500 mr-2"></i>Viewed product: <strong>${log.productName}</strong> (ID: ${log.productId})`;

            case 'filter_applied':
                return `<i class="fas fa-filter text-purple-500 mr-2"></i>Applied ${log.filterType} filter: <strong>${log.filterValue}</strong>`;

            case 'form_interaction':
                return `<i class="fas fa-edit text-blue-500 mr-2"></i>Interacted with ${log.formType} form${log.step ? ` (Step: ${log.step})` : ''}${log.action ? ` - ${log.action}` : ''}`;

            case 'payment_method_selected':
                return `<i class="fas fa-credit-card text-green-500 mr-2"></i>Selected payment method: <strong>${log.method.replace('_', ' ')}</strong>`;

            default:
                return `<i class="fas fa-info-circle text-gray-500 mr-2"></i>${log.event.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
        }
    }

    let chatMessages = {};

    function sendChatMessage() {
        const session = sessions.find(s => s.sessionID === currentSessionId);
        if (!session || session.isExpired) return;

        const chatInput = document.getElementById('chatInput');
        const message = chatInput.value.trim();

        if (!message || !currentSessionId) return;

        if (!chatMessages[currentSessionId]) {
            chatMessages[currentSessionId] = [];
        }
        chatMessages[currentSessionId].push({
            message: message,
            timestamp: Date.now(),
            type: 'admin'
        });

        const activityFeed = document.getElementById('activityFeed');
        const adminMessage = `
        <div class="flex gap-3 justify-end">
            <div class="flex-1 min-w-0 max-w-xs">
                <div class="bg-blue-500 text-white rounded-lg p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium opacity-75">Admin</span>
                        <span class="text-xs opacity-75">Just now</span>
                    </div>
                    <div class="text-sm">
                        ${message}
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-xs"></i>
                </div>
            </div>
        </div>
    `;

        activityFeed.insertAdjacentHTML('beforeend', adminMessage);
        activityFeed.scrollTop = activityFeed.scrollHeight;

        chatInput.value = '';
        document.getElementById('charCount').textContent = '0/500';
        document.getElementById('sendChatBtn').disabled = true;


        setTimeout(() => {
            const deliveryConfirmation = `
            <div class="text-center">
                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                    <i class="fas fa-check mr-1"></i>Message delivered
                </span>
            </div>
        `;
            activityFeed.insertAdjacentHTML('beforeend', deliveryConfirmation);
            activityFeed.scrollTop = activityFeed.scrollHeight;
        }, 1000);
    }

    function sendMobileChatMessage() {
        const session = sessions.find(s => s.sessionID === currentSessionId);
        if (!session || session.isExpired) return;

        const mobileChatInput = document.getElementById('mobileChatInput');
        const message = mobileChatInput.value.trim();

        if (!message || !currentSessionId) return;

        if (!chatMessages[currentSessionId]) {
            chatMessages[currentSessionId] = [];
        }

        chatMessages[currentSessionId].push({
            message: message,
            timestamp: Date.now(),
            type: 'admin'
        });

        const mobileActivityFeed = document.getElementById('mobileActivityFeed');
        const adminMessage = `
        <div class="flex gap-3 justify-end">
            <div class="flex-1 min-w-0 max-w-xs">
                <div class="bg-blue-500 text-white rounded-lg p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium opacity-75">Admin</span>
                        <span class="text-xs opacity-75">Just now</span>
                    </div>
                    <div class="text-sm">
                        ${message}
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                    <i class="fas fa-user-shield text-white text-xs"></i>
                </div>
            </div>
        </div>
    `;

        mobileActivityFeed.insertAdjacentHTML('beforeend', adminMessage);
        mobileActivityFeed.scrollTop = mobileActivityFeed.scrollHeight;

        mobileChatInput.value = '';
        document.getElementById('mobileCharCount').textContent = '0/500';
        document.getElementById('mobileSendChatBtn').disabled = true;


        setTimeout(() => {
            const deliveryConfirmation = `
            <div class="text-center">
                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                    <i class="fas fa-check mr-1"></i>Message delivered
                </span>
            </div>
        `;
            mobileActivityFeed.insertAdjacentHTML('beforeend', deliveryConfirmation);
            mobileActivityFeed.scrollTop = mobileActivityFeed.scrollHeight;
        }, 1000);
    }

    function closeSessionModal() {
        document.getElementById('sessionModal').classList.add('hidden');
        document.body.style.overflow = '';

        stopSessionStream();
        currentSessionId = null;
        startPolling();

        document.getElementById('chatInput').value = '';
        document.getElementById('charCount').textContent = '0/500';
        document.getElementById('sendChatBtn').disabled = true;

        Object.keys(leafletMaps).forEach(mapId => {
            if (leafletMaps[mapId]) {
                leafletMaps[mapId].remove();
                delete leafletMaps[mapId];
            }
        });
    }

    function closeMobileSessionModal() {
        document.getElementById('mobileSessionModal').classList.add('hidden');
        document.body.style.overflow = '';

        stopSessionStream();
        currentSessionId = null;
        startPolling();

        document.getElementById('mobileChatInput').value = '';
        document.getElementById('mobileCharCount').textContent = '0/500';
        document.getElementById('mobileSendChatBtn').disabled = true;
    }

    function initializeDateFilters() {
        setDateRangeForPeriod('monthly');
    }

    function setDateRangeForPeriod(period) {
        const today = new Date();
        let startDate, endDate;

        switch (period) {
            case 'daily':
                startDate = new Date(today);
                endDate = new Date(today);
                break;
            case 'weekly':
                const dayOfWeek = today.getDay();
                startDate = new Date(today);
                startDate.setDate(today.getDate() - dayOfWeek);
                endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 6);
                break;
            case 'monthly':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today);
                break;
            default:
                startDate = new Date(today);
                endDate = new Date(today);
        }

        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];

        currentPeriod = period;
        currentPage = 1;
        loadSessionsData();
    }

    function refreshSessions() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadSessionsData().finally(() => {
            setTimeout(() => {
                icon.classList.remove('fa-spin');
                refreshBtn.disabled = false;
            }, 1000);
        });
    }

    function showError(message) {
        const tbody = document.getElementById('sessionsBody');
        const cards = document.getElementById('sessionsCards');

        const errorContent = `
        <div class="px-4 py-8 text-center text-red-500">
            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
            <div>${message}</div>
        </div>
    `;

        tbody.innerHTML = `<tr><td colspan="7">${errorContent}</td></tr>`;
        cards.innerHTML = errorContent;
    }

    function updatePagination(total, page) {
        const totalPages = Math.ceil(total / itemsPerPage);
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, total);

        document.getElementById('showingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('totalCount').textContent = total;
        document.getElementById('pageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === totalPages || totalPages === 0;

        document.getElementById('mobileShowingCount').textContent = `${startIndex + 1}-${endIndex}`;
        document.getElementById('mobileTotalCount').textContent = total;
        document.getElementById('mobilePageInfo').textContent = `Page ${page} of ${Math.max(1, totalPages)}`;

        document.getElementById('mobilePrevPage').disabled = page === 1;
        document.getElementById('mobileNextPage').disabled = page === totalPages || totalPages === 0;
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function playNewSessionSound() {
        try {
            const audio = document.getElementById('newSessionSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(error => {
                    console.log('Could not play new session sound:', error);
                });
            }
        } catch (error) {
            console.error('Error playing new session sound:', error);
        }
    }

    function playNewEventSound() {
        try {
            const audio = document.getElementById('newEventSound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(error => {
                    console.log('Could not play new event sound:', error);
                });
            }
        } catch (error) {
            console.error('Error playing new event sound:', error);
        }
    }

    window.addEventListener('beforeunload', () => {
        if (sessionEventSource) {
            sessionEventSource.close();
        }

        if (pollingInterval) {
            clearInterval(pollingInterval);
        }

        Object.keys(leafletMaps).forEach(mapId => {
            if (leafletMaps[mapId]) {
                leafletMaps[mapId].remove();
                delete leafletMaps[mapId];
            }
        });
    });

    window.viewSessionDetails = viewSessionDetails;
    window.closeSessionModal = closeSessionModal;
    window.closeMobileSessionModal = closeMobileSessionModal;
    window.scrollToBottom = scrollToBottom;
    window.scrollToBottomMobile = scrollToBottomMobile;
</script>

<style>
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .5;
        }
    }

    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }

    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }

    #chatInput:focus,
    #mobileChatInput:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    #activityFeed,
    #mobileActivityFeed {
        scroll-behavior: smooth;
    }

    .leaflet-container {
        border-radius: 0.5rem;
    }

    .custom-div-icon {
        background: transparent;
        border: none;
    }

    .leaflet-default-icon-path {
        background-image: url('https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png');
    }

    #mobileSessionModal {
        background: white;
    }

    #mobileChatInput {
        border-radius: 20px;
    }

    #mobileSendChatBtn {
        border-radius: 50%;
        min-width: 40px;
        min-height: 40px;
    }

    .date-filter-btn {
        border-color: #d1d5db;
        color: #374151;
        transition: all 0.2s ease;
    }

    .date-filter-btn:hover:not(.active) {
        background-color: #f9fafb;
    }

    .date-filter-btn.active {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>