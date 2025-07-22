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
                            class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                            <span class="text-xs sm:text-sm font-medium text-yellow-700">Connecting...</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base hidden sm:block">Monitor real-time user activity
                        and session events</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <button id="refreshBtn"
                        class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt text-sm"></i>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
        <div class="hidden sm:grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
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

            <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-red-600 uppercase tracking-wide">Total Events</p>
                        <p class="text-xl font-bold text-red-900 truncate" id="totalEvents">0</p>
                    </div>
                    <div class="w-10 h-10 bg-red-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chart-line text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-8">
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Active Sessions</h3>
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

            <div class="lg:hidden" id="sessionsCards">
                <div class="p-4 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <div>Loading sessions...</div>
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
                <div class="flex items-center gap-2 px-3 py-1 bg-green-100 border border-green-200 rounded-full">
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

                <div class="border-t border-gray-200 p-4 bg-white">
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
            <div class="flex items-center gap-2 px-2 py-1 bg-green-500 rounded-full">
                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                <span class="text-xs font-medium">Live</span>
            </div>
        </div>

        <div class="flex flex-col h-[calc(100vh-80px)]">
            <div class="flex flex-col min-h-screen bg-gray-50">
                <div id="mobileNewEventIndicator"
                    class="hidden bg-blue-50 border-b border-blue-200 p-2 text-center sticky top-0 z-10">
                    <button onclick="scrollToBottomMobile()"
                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        <i class="fas fa-arrow-down mr-1"></i>
                        New activity available
                    </button>
                </div>

                <div id="mobileActivityFeed" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">Loading activity feed...</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-4 bg-white sticky bottom-0 z-10">
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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let sessions = [];
    let currentSessionId = null;
    let eventSource = null;
    let leafletMaps = {};
    let isConnected = false;

    document.addEventListener('DOMContentLoaded', function () {
        setupEventListeners();
        initializeEventStream();
        loadInitialSessions();
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
    }

    function initializeEventStream() {
        if (eventSource) {
            eventSource.close();
        }

        updateConnectionStatus('connecting');

        eventSource = new EventSource('fetch/manageSessions.php?action=stream');

        eventSource.onopen = function () {
            updateConnectionStatus('connected');
            isConnected = true;
        };

        eventSource.onmessage = function (event) {
            try {
                const data = JSON.parse(event.data);

                if (data.type === 'sessions_update') {
                    sessions = data.data;
                    updateStatistics();
                    renderSessionsTable();
                    renderSessionsCards();
                    updateCountryFilter();

                    if (currentSessionId) {
                        if (window.innerWidth < 1024) {
                            loadMobileSessionDetails(currentSessionId, false);
                        } else {
                            loadSessionDetails(currentSessionId, false);
                        }
                    }
                }
            } catch (error) {
                console.error('Error parsing SSE data:', error);
            }
        };

        eventSource.onerror = function () {
            updateConnectionStatus('error');
            isConnected = false;

            setTimeout(() => {
                if (!isConnected) {
                    initializeEventStream();
                }
            }, 2000);
        };
    }

    function updateConnectionStatus(status) {
        const statusElement = document.getElementById('connectionStatus');

        switch (status) {
            case 'connecting':
                statusElement.innerHTML = `
                <div class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                <span class="text-xs sm:text-sm font-medium text-yellow-700">Connecting...</span>
            `;
                statusElement.className = 'flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-yellow-50 border border-yellow-200 rounded-lg';
                break;

            case 'connected':
                statusElement.innerHTML = `
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-xs sm:text-sm font-medium text-green-700">Live</span>
            `;
                statusElement.className = 'flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-2 bg-green-50 border border-green-200 rounded-lg';
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
        try {
            const response = await fetch('fetch/manageSessions.php?action=get');
            const data = await response.json();

            if (data.success) {
                sessions = data.data;
                updateStatistics(data.stats);
                renderSessionsTable();
                renderSessionsCards();
                updateCountryFilter();
            } else {
                showError('Failed to load sessions');
            }
        } catch (error) {
            console.error('Error loading sessions:', error);
            showError('Failed to load sessions');
        }
    }

    function updateStatistics(stats = null) {
        if (stats) {
            document.getElementById('totalSessions').textContent = stats.total_sessions.toLocaleString();
            document.getElementById('activeSessions').textContent = stats.active_sessions.toLocaleString();
            document.getElementById('loggedUsers').textContent = stats.logged_users.toLocaleString();
            document.getElementById('uniqueCountries').textContent = stats.unique_countries.toLocaleString();
            document.getElementById('totalEvents').textContent = stats.total_events.toLocaleString();
        } else {
            const totalSessions = sessions.length;
            const activeSessions = sessions.filter(s => s.isActive).length;
            const loggedUsers = sessions.filter(s => s.loggedUser !== null).length;
            const uniqueCountries = [...new Set(sessions.map(s => s.country))].length;
            const totalEvents = sessions.reduce((sum, s) => sum + (s.logs ? s.logs.length : 0), 0);

            document.getElementById('totalSessions').textContent = totalSessions.toLocaleString();
            document.getElementById('activeSessions').textContent = activeSessions.toLocaleString();
            document.getElementById('loggedUsers').textContent = loggedUsers.toLocaleString();
            document.getElementById('uniqueCountries').textContent = uniqueCountries.toLocaleString();
            document.getElementById('totalEvents').textContent = totalEvents.toLocaleString();
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
                    <div>No active sessions found</div>
                </td>
            </tr>
        `;
            return;
        }

        let filteredSessions = filterSessionsData();

        tbody.innerHTML = filteredSessions.map(session => {
            const displayName = session.loggedUser ? session.loggedUser : session.sessionID;
            const deviceIcon = getDeviceIcon(session.device);
            const lastActivity = getLastActivity(session);
            const timeAgo = getTimeAgo(lastActivity);

            return `
            <tr class="hover:bg-gray-50 transition-colors cursor-pointer ${!session.isActive ? 'opacity-60' : ''}" onclick="viewSessionDetails('${session.sessionID}')">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full ${session.isActive ? 'bg-green-100' : 'bg-gray-300'} flex items-center justify-center">
                            <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} ${session.isActive ? 'text-green-600' : 'text-gray-600'} text-sm"></i>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${displayName}</div>
                            <div class="text-sm text-gray-500">${session.ipAddress}</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                        <img src="https://flagcdn.com/16x12/${session.shortName.toLowerCase()}.png" alt="${session.country}" class="rounded">
                        <span class="text-sm text-gray-900">${session.country}</span>
                    </div>
                    <div class="text-xs text-gray-500">${session.phoneCode}</div>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                        <i class="${deviceIcon} text-gray-600"></i>
                        <span class="text-sm text-gray-900">${session.browser}</span>
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
                    <div class="text-sm text-gray-900">${timeAgo}</div>
                    <div class="text-xs text-gray-500">${formatTime(lastActivity)}</div>
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
                <div>No active sessions found</div>
            </div>
        `;
            return;
        }

        let filteredSessions = filterSessionsData();

        container.innerHTML = filteredSessions.map(session => {
            const lastActivity = getLastActivity(session);
            const timeAgo = getTimeAgo(lastActivity);
            const displayName = session.loggedUser ? session.loggedUser : `${session.sessionID.substring(0, 6)}...`;
            const deviceIcon = getDeviceIcon(session.device);

            return `
            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-pointer ${!session.isActive ? 'opacity-60' : ''}" onclick="viewSessionDetails('${session.sessionID}')">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full ${session.isActive ? 'bg-green-100' : 'bg-gray-300'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} ${session.isActive ? 'text-green-600' : 'text-gray-600'} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="text-sm font-medium text-gray-900 truncate">${displayName}</h4>
                            <span class="text-xs text-gray-500">${timeAgo}</span>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <img src="https://flagcdn.com/16x12/${session.shortName.toLowerCase()}.png" alt="${session.country}" class="rounded">
                            <span class="text-sm text-gray-600">${session.country}</span>
                            <span class="text-xs text-gray-500">•</span>
                            <span class="text-sm text-gray-600">${session.browser}</span>
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
                session.ipAddress.includes(searchTerm) ||
                session.country.toLowerCase().includes(searchTerm) ||
                session.browser.toLowerCase().includes(searchTerm) ||
                (session.loggedUser && session.loggedUser.toLowerCase().includes(searchTerm));

            const matchesCountry = countryFilter === 'all' || session.country === countryFilter;

            let matchesStatus = true;
            switch (statusFilter) {
                case 'active':
                    matchesStatus = session.isActive;
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

        if (window.innerWidth < 1024) {
            document.getElementById('mobileSessionModal').classList.remove('hidden');
            loadMobileSessionDetails(sessionId, true);
        } else {
            document.getElementById('sessionModal').classList.remove('hidden');
            loadSessionDetails(sessionId, true);
        }

        document.body.style.overflow = 'hidden';
    }

    function loadSessionDetails(sessionId, isInitialLoad = false) {
        const session = sessions.find(s => s.sessionID === sessionId);
        if (!session) return;

        if (isInitialLoad) {
            document.getElementById('modalTitle').textContent = `Session: ${sessionId}`;
            document.getElementById('modalSubtitle').textContent = `${session.country} • ${session.browser} • ${session.device} • ${session.activeDuration}`;
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
                        <span class="font-medium">${session.ipAddress}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Location:</span>
                        <div class="flex items-center gap-1">
                            <img src="https://flagcdn.com/16x12/${session.shortName.toLowerCase()}.png" alt="${session.country}" class="rounded">
                            <span>${session.country}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Browser:</span>
                        <span class="font-medium">${session.browser}</span>
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
                        <span class="font-medium ${session.isActive ? 'text-green-600' : 'text-red-600'}">${session.isActive ? 'Active' : 'Inactive'}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">User Status:</span>
                        ${session.loggedUser ?
                    '<span class="text-green-600 font-medium">Logged In</span>' :
                    '<span class="text-gray-600">Guest</span>'
                }
                    </div>
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
                    <div class="w-8 h-8 rounded-full ${session.loggedUser ? 'bg-gray-400' : 'bg-gray-500'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-500">${formatDateTime(log.timestamp)}</span>
                            ${isLatest ? '<span class="text-xs text-green-600 font-medium">Latest</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-900">
                            ${formatEventMessage(log)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        if (!isInitialLoad && !wasAtBottom) {
            document.getElementById('newEventIndicator').classList.remove('hidden');
        } else if (wasAtBottom) {
            activityFeed.scrollTop = activityFeed.scrollHeight;
            document.getElementById('newEventIndicator').classList.add('hidden');
        }
    }

    function loadMobileSessionDetails(sessionId, isInitialLoad = false) {
        const session = sessions.find(s => s.sessionID === sessionId);
        if (!session) return;

        if (isInitialLoad) {
            document.getElementById('mobileModalTitle').textContent = session.loggedUser ? session.loggedUser : `${sessionId.substring(0, 8)}...`;
            document.getElementById('mobileModalSubtitle').textContent = `${session.country} • ${session.browser} • ${session.activeDuration}`;
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
                    <div class="w-8 h-8 rounded-full ${session.loggedUser ? 'bg-gray-400' : 'bg-gray-500'} flex items-center justify-center">
                        <i class="${session.loggedUser ? 'fas fa-user' : 'fas fa-globe'} text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-500">${formatTime(new Date(log.timestamp).getTime())}</span>
                            ${isLatest ? '<span class="text-xs text-green-600 font-medium">Latest</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-900">
                            ${formatEventMessage(log)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        if (!isInitialLoad && !wasAtBottom) {
            document.getElementById('mobileNewEventIndicator').classList.remove('hidden');
        } else if (wasAtBottom) {
            mobileActivityFeed.scrollTop = mobileActivityFeed.scrollHeight;
            document.getElementById('mobileNewEventIndicator').classList.add('hidden');
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
                return `Navigated to <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.pageTitle || log.url}</a>`;

            case 'login_modal_open':
                return 'Opened login modal';

            case 'login_identifier':
                return `Entered identifier: ${log.identifier} (${log.status === 'passed' ? '✅ Valid' : '❌ Invalid'})`;

            case 'login_password':
                return `Password attempt: ${log.status === 'passed' ? '✅ Success' : '❌ Failed'}`;

            case 'click':
                return log.url ?
                    `Clicked on "${log.element}" → <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.url}</a>` :
                    `Clicked on "${log.element}"`;

            case 'navigation':
                return `Navigated to <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.pageTitle || log.url}</a>`;

            case 'scroll':
                return `Scrolled to position ${log.scrollPosition}px`;

            case 'search_query':
                return `Searched for: "<strong>${log.query}</strong>"`;

            case 'add_to_cart':
                return `Added ${log.quantity} item(s) to cart (Product ID: ${log.productId})`;

            case 'checkout_initiated':
                return `Started checkout process (Cart value: UGX ${log.cartValue?.toLocaleString()})`;

            case 'order_placed':
                return `Placed order ${log.orderId} (Amount: UGX ${log.amount?.toLocaleString()})`;

            case 'product_view':
                return `Viewed product: <strong>${log.productName}</strong> (ID: ${log.productId})`;

            case 'filter_applied':
                return `Applied ${log.filterType} filter: <strong>${log.filterValue}</strong>`;

            case 'form_interaction':
                return `Interacted with ${log.formType} form${log.step ? ` (Step: ${log.step})` : ''}${log.action ? ` - ${log.action}` : ''}`;

            case 'payment_method_selected':
                return `Selected payment method: <strong>${log.method.replace('_', ' ')}</strong>`;

            case 'page_refresh':
                return `Refreshed page: <a href="${log.url}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">${log.pageTitle || log.url}</a>`;

            default:
                return `${log.event.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
        }
    }

    let chatMessages = {};

    function sendChatMessage() {
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

        console.log(`Sending message to session ${currentSessionId}: ${message}`);

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

        console.log(`Sending mobile message to session ${currentSessionId}: ${message}`);

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
        currentSessionId = null;

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
        currentSessionId = null;

        document.getElementById('mobileChatInput').value = '';
        document.getElementById('mobileCharCount').textContent = '0/500';
        document.getElementById('mobileSendChatBtn').disabled = true;
    }

    function refreshSessions() {
        const refreshBtn = document.getElementById('refreshBtn');
        const icon = refreshBtn.querySelector('i');

        icon.classList.add('fa-spin');
        refreshBtn.disabled = true;

        loadInitialSessions().finally(() => {
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

    window.addEventListener('beforeunload', () => {
        if (eventSource) {
            eventSource.close();
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
</style>

<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>