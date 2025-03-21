<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Session Tracking';
$activeNav = 'sessions';
ob_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .responsive-table-mobile {
        display: none;
    }

    .accordion-content {
        display: none;
    }

    .accordion-arrow {
        transition: transform 0.2s ease;
    }

    .accordion-arrow.active {
        transform: rotate(180deg);
    }

    .date-range-preset {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        background-color: #fff;
        color: #4B5563;
        cursor: pointer;
        transition: all 0.2s;
    }

    .date-range-preset:hover {
        border-color: #C00000;
        color: #C00000;
    }

    .date-range-preset.active {
        background-color: #C00000;
        border-color: #C00000;
        color: white;
    }

    @media (max-width: 768px) {
        .responsive-table-desktop {
            display: none;
        }

        .responsive-table-mobile {
            display: block;
        }

        .mobile-row {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .mobile-row-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .mobile-row-content {
            padding: 0 1rem 1rem;
        }

        .mobile-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .mobile-grid-item {
            display: flex;
            flex-direction: column;
        }

        .mobile-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .mobile-value {
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }

        #filter-form {
            flex-direction: column;
            align-items: flex-end;
        }

        #filter-btn {
            width: 100%;
            text-align: left;
        }

        .responsive-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .responsive-header h2 {
            display: none;
        }

        .responsive-header>div {
            align-items: stretch;
            width: 100%;
            flex-direction: column;
            gap: 4px;
        }

        .responsive-header input,
        .responsive-header select {
            width: 100%;
        }

        .date-range-presets {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .date-range-preset {
            flex: 1 0 calc(50% - 0.5rem);
            justify-content: center;
        }
    }
</style>
<div class="space-y-6">
    <!-- Active Sessions -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between responsive-header">
            <h2 class="text-lg font-semibold text-secondary">Active Sessions</h2>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="text" id="searchActive" placeholder="Search active sessions..." class="w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="active-sessions-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Session ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">IP Address</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Browser</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Device</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Country</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Started</th>
                    </tr>
                </thead>
                <tbody id="active-sessions-body"></tbody>
            </table>
        </div>
        <div class="responsive-table-mobile p-4" id="active-sessions-mobile"></div>
    </div>
    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <form id="filter-form" class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 date-range-presets">
                <button type="button" class="date-range-preset active" data-range="current-week">
                    <i class="fas fa-calendar-week mr-2"></i>Current Week
                </button>
                <button type="button" class="date-range-preset" data-range="last-week">
                    <i class="fas fa-calendar-week mr-2"></i>Last Week
                </button>
                <button type="button" class="date-range-preset" data-range="today">
                    <i class="fas fa-calendar-day mr-2"></i>Today
                </button>
                <button type="button" class="date-range-preset" data-range="yesterday">
                    <i class="fas fa-calendar-day mr-2"></i>Yesterday
                </button>
                <button type="button" class="date-range-preset" data-range="this-month">
                    <i class="fas fa-calendar-alt mr-2"></i>This Month
                </button>
            </div>
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-text">From:</label>
                    <input type="datetime-local" id="startDate" class="h-10 pl-2 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-text">To:</label>
                    <input type="datetime-local" id="endDate" class="h-10 pl-2 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <button type="button" id="filter-btn" class="h-10 px-4 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    <i class="fas fa-filter mr-2"></i>Apply Filter
                </button>
            </div>
        </form>
    </div>
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="stats-overview">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Active Sessions</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-active">0</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-play text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Past Sessions</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-past">0</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-history text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <p class="text-sm text-gray-text">Average Duration</p>
                    <h3 class="text-2xl font-semibold text-secondary" id="stat-avg-duration">--</h3>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <i class="fas fa-clock text-purple-500"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Past Sessions Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100" id="past-sessions-container">
        <div class="p-6 border-b border-gray-100 flex items-center justify-between responsive-header">
            <h2 class="text-lg font-semibold text-secondary">Past Sessions</h2>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <input type="text" id="searchPast" placeholder="Search past sessions..." class="w-64 h-10 pl-10 pr-4 rounded-lg border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        </div>
        <div class="responsive-table-desktop overflow-x-auto">
            <table class="w-full" id="past-sessions-table">
                <thead>
                    <tr class="text-left border-b border-gray-100">
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Session ID</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">IP Address</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Browser</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Device</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Country</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Ended</th>
                        <th class="px-6 py-3 text-sm font-semibold text-gray-text">Duration</th>
                    </tr>
                </thead>
                <tbody id="past-sessions-body"></tbody>
            </table>
        </div>
        <div class="responsive-table-mobile p-4" id="past-sessions-mobile"></div>
        <div class="p-4 border-t border-gray-100 flex justify-center">
            <div class="flex items-center gap-2">
                <button id="load-more" class="px-4 py-2 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                    <i class="fas fa-sync-alt mr-2"></i>Load More
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Offcanvas for Session Details -->
<div id="sessionDetailsOffcanvas" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/20" onclick="hideSessionDetails()"></div>
    <div class="absolute inset-y-0 right-0 w-full max-w-2xl bg-white shadow-lg transform translate-x-full transition-transform duration-300">
        <div class="flex flex-col h-full">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-secondary" id="offcanvas-session-title"></h3>
                <button onclick="hideSessionDetails()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-6" id="sessionDetailsContent"></div>
        </div>
    </div>
</div>
<script>
    // API endpoint for past sessions.
    const API_BASE = "<?php echo BASE_URL; ?>admin/fetch/manageSessions";
    var currentOffcanvasSessionID = null;
    var activeSessions = [];
    var pastSessions = [];
    if (!localStorage.getItem('countryCodes')) {
        var codes = {
            "Uganda": "ug",
            "Kenya": "ke"
        };
        localStorage.setItem('countryCodes', JSON.stringify(codes));
    }

    // Helper function to add ordinal suffix to a day number.
    function getOrdinalSuffix(day) {
        if (day > 3 && day < 21) return "th";
        switch (day % 10) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    }

    // Formats a Date object as "Feb 18th, 2025 11:54:48 PM"
    function formatDateCustom(date) {
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        let month = months[date.getMonth()];
        let day = date.getDate();
        let ordinal = getOrdinalSuffix(day);
        let year = date.getFullYear();
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let seconds = date.getSeconds();
        let ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12;
        hours = hours ? hours : 12; // convert 0 to 12
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        return `${month} ${day}${ordinal}, ${year} ${hours}:${minutes}:${seconds} ${ampm}`;
    }

    // Updates the formatDateFromTimestamp function to use our custom format.
    function formatDateFromTimestamp(ts) {
        return formatDateCustom(new Date(ts));
    }

    // Utility: Format Duration between start and end timestamps (in ms)
    function formatDuration(startMs, endMs) {
        var diffSeconds = Math.floor((endMs - startMs) / 1000);
        if (diffSeconds < 60) {
            return diffSeconds + "s";
        } else if (diffSeconds < 3600) {
            var minutes = Math.floor(diffSeconds / 60);
            var seconds = diffSeconds % 60;
            return minutes + "m" + (seconds > 0 ? " " + seconds + "s" : "");
        } else if (diffSeconds < 86400) {
            var hours = Math.floor(diffSeconds / 3600);
            var minutes = Math.floor((diffSeconds % 3600) / 60);
            return hours + "h" + (minutes > 0 ? " " + minutes + "m" : "");
        } else if (diffSeconds < 2592000) { // less than 30 days
            var days = Math.floor(diffSeconds / 86400);
            var hours = Math.floor((diffSeconds % 86400) / 3600);
            return days + "d" + (hours > 0 ? " " + hours + "h" : "");
        } else {
            var months = Math.floor(diffSeconds / 2592000);
            var days = Math.floor((diffSeconds % 2592000) / 86400);
            return months + "mo" + (days > 0 ? " " + days + "d" : "");
        }
    }

    function getCountryFlagHtml(countryName, shortName) {
        if (shortName) {
            return '<span class="flag-icon flag-icon-' + shortName + '"></span> <span>' + countryName + '</span>';
        } else {
            var countryCodes = JSON.parse(localStorage.getItem('countryCodes') || '{}');
            if (!countryCodes[countryName]) {
                var defaultMapping = {
                    "Uganda": "ug",
                    "Kenya": "ke"
                };
                var code = defaultMapping[countryName] || countryName.slice(0, 2).toLowerCase();
                countryCodes[countryName] = code;
                localStorage.setItem('countryCodes', JSON.stringify(countryCodes));
            }
            var code = countryCodes[countryName];
            return '<span class="flag-icon flag-icon-' + code + '"></span> <span>' + countryName + '</span>';
        }
    }

    function capitalizeDevice(dev) {
        if (!dev) return '';
        return dev.charAt(0).toUpperCase() + dev.slice(1);
    }

    function renderSessions(sessions, desktopTbodyID, mobileContainerID) {
        var isPastSession = (desktopTbodyID === "past-sessions-body");
        var desktopTbody = document.getElementById(desktopTbodyID);
        var mobileContainer = document.getElementById(mobileContainerID);
        desktopTbody.innerHTML = "";
        mobileContainer.innerHTML = "";
        sessions.sort(function(a, b) {
            return b.timestamp - a.timestamp;
        });

        // Calculate average duration for past sessions
        if (isPastSession && sessions.length > 0) {
            let totalDuration = 0;
            let validSessions = 0;

            sessions.forEach(function(session) {
                if (session.logged_at) {
                    const duration = new Date(session.logged_at).getTime() - session.timestamp;
                    if (duration > 0) {
                        totalDuration += duration;
                        validSessions++;
                    }
                }
            });

            if (validSessions > 0) {
                const avgDuration = totalDuration / validSessions;
                $("#stat-avg-duration").text(formatDuration(0, avgDuration));
            } else {
                $("#stat-avg-duration").text("--");
            }
        }

        sessions.forEach(function(session) {
            var countryDisplay = getCountryFlagHtml(session.country, session.shortName);
            var deviceDisplay = capitalizeDevice(session.device);
            var tr = document.createElement('tr');
            tr.className = "border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors";
            tr.onclick = function() {
                showSessionDetails(session.sessionID);
            };
            if (isPastSession) {
                var shortSessionID = session.sessionID.substring(0, 6) + "...";
                var endedTime = formatDateCustom(new Date(session.logged_at));
                var duration = formatDuration(session.timestamp, new Date(session.logged_at).getTime());
                tr.innerHTML = '<td class="px-6 py-4 text-sm text-gray-text">' + shortSessionID + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + session.ipAddress + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + session.browser + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + deviceDisplay + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + countryDisplay + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + endedTime + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + duration + '</td>';
            } else {
                tr.innerHTML = '<td class="px-6 py-4 text-sm text-gray-text">' + session.sessionID + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + session.ipAddress + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + session.browser + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + deviceDisplay + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + countryDisplay + '</td>' +
                    '<td class="px-6 py-4 text-sm text-gray-text">' + formatDateFromTimestamp(session.timestamp) + '</td>';
            }
            desktopTbody.appendChild(tr);
            // Mobile view rendering:
            var mobileRow = document.createElement('div');
            mobileRow.className = "mobile-row";
            mobileRow.setAttribute("data-session-id", session.sessionID);
            if (isPastSession) {
                var shortSessionIDMobile = session.sessionID.substring(0, 6) + "...";
                var endedTimeMobile = formatDateCustom(new Date(session.logged_at));
                var durationMobile = formatDuration(session.timestamp, new Date(session.logged_at).getTime());
                mobileRow.innerHTML = '<div class="mobile-row-header">' +
                    '<div class="font-medium text-secondary">Session ID: ' + shortSessionIDMobile + '</div>' +
                    '<svg class="accordion-arrow w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />' +
                    '</svg>' +
                    '</div>' +
                    '<div class="accordion-content">' +
                    '<div class="mobile-row-content">' +
                    '<div class="mobile-grid">' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Country</span>' +
                    '<span class="mobile-value">' + countryDisplay + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Ended</span>' +
                    '<span class="mobile-value">' + endedTimeMobile + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Duration</span>' +
                    '<span class="mobile-value">' + durationMobile + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Browser</span>' +
                    '<span class="mobile-value">' + session.browser + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<button onclick="showSessionDetails(\'' + session.sessionID + '\')" class="w-full mt-4 bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors text-sm">' +
                    'View Details' +
                    '</button>';
            } else {
                mobileRow.innerHTML = '<div class="mobile-row-header">' +
                    '<div class="font-medium text-secondary">Session ID: ' + session.sessionID + '</div>' +
                    '<svg class="accordion-arrow w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />' +
                    '</svg>' +
                    '</div>' +
                    '<div class="accordion-content">' +
                    '<div class="mobile-row-content">' +
                    '<div class="mobile-grid">' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Country</span>' +
                    '<span class="mobile-value">' + countryDisplay + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Browser</span>' +
                    '<span class="mobile-value">' + session.browser + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Device</span>' +
                    '<span class="mobile-value">' + deviceDisplay + '</span>' +
                    '</div>' +
                    '<div class="mobile-grid-item">' +
                    '<span class="mobile-label">Started</span>' +
                    '<span class="mobile-value">' + formatDateFromTimestamp(session.timestamp) + '</span>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<button onclick="showSessionDetails(\'' + session.sessionID + '\')" class="w-full mt-4 bg-gray-50 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors text-sm">' +
                    'View Details' +
                    '</button>';
            }
            mobileContainer.appendChild(mobileRow);
        });
    }

    // Update the offcanvas with detailed session info.
    function updateOffcanvas(session) {
        var logsHTML = "";
        session.logs.forEach(function(log) {
            logsHTML += '<div class="bg-gray-50 p-4 rounded-lg mb-3">' +
                '<p class="text-sm text-gray-500 mb-1">Event: <span class="text-gray-700 font-medium">' + log.event + '</span></p>' +
                '<p class="text-sm text-gray-500 mb-1">Timestamp: <span class="text-gray-700 font-medium">' + log.timestamp + '</span></p>' +
                '<p class="text-sm text-gray-500 mb-1">Active Nav: <span class="text-gray-700 font-medium">' + log.activeNavigation + '</span></p>' +
                '<p class="text-sm text-gray-500">Page Title: <span class="text-gray-700 font-medium">' + log.pageTitle + '</span></p>' +
                '</div>';
        });

        var sessionInfoHtml = '<div>' +
            '<h4 class="text-lg font-semibold text-secondary">Session Info</h4>';

        if (session.logged_at) {
            sessionInfoHtml += '<p class="text-sm text-gray-text mt-2">Started on: ' + formatDateFromTimestamp(session.timestamp) + '</p>' +
                '<p class="text-sm text-gray-text mt-1">Ended on: ' + formatDateCustom(new Date(session.logged_at)) + '</p>' +
                '<p class="text-sm text-gray-text mt-1">Duration: ' + formatDuration(session.timestamp, new Date(session.logged_at).getTime()) + '</p>';
        } else {
            sessionInfoHtml += '<p class="text-sm text-gray-text mt-2">Started on: ' + formatDateFromTimestamp(session.timestamp) + '</p>';
        }

        sessionInfoHtml += '<p class="text-sm text-gray-text mt-1">Browser: ' + session.browser + '</p>' +
            '<p class="text-sm text-gray-text mt-1">Device: ' + capitalizeDevice(session.device) + '</p>' +
            '<p class="text-sm text-gray-text mt-1">IP Address: ' + session.ipAddress + '</p>' +
            '<p class="text-sm text-gray-text mt-1">Country: ' + getCountryFlagHtml(session.country, session.shortName) + '</p>' +
            '</div>';

        document.getElementById('sessionDetailsContent').innerHTML = '<div class="space-y-6">' +
            sessionInfoHtml +
            '<div class="border-t border-gray-100 pt-6">' +
            '<h4 class="font-medium text-secondary mb-4">Logs</h4>' +
            '<div>' + logsHTML + '</div>' +
            '</div>' +
            '</div>';
    }

    function showSessionDetails(sessionID) {
        currentOffcanvasSessionID = sessionID;
        var session = activeSessions.find(function(s) {
            return s.sessionID === sessionID;
        }) || pastSessions.find(function(s) {
            return s.sessionID === sessionID;
        });
        if (session) {
            // For offcanvas, show full sessionID
            document.getElementById('offcanvas-session-title').innerText = "Session: " + session.sessionID;
            updateOffcanvas(session);
        } else {
            document.getElementById('offcanvas-session-title').innerText = "Session: " + sessionID;
            document.getElementById('sessionDetailsContent').innerHTML = '<div class="text-red-500 italic">Session Ended</div>';
        }
        var offcanvas = document.getElementById('sessionDetailsOffcanvas');
        offcanvas.classList.remove('hidden');
        setTimeout(function() {
            offcanvas.querySelector('.transform').classList.remove('translate-x-full');
        }, 10);
    }

    function hideSessionDetails() {
        currentOffcanvasSessionID = null;
        var offcanvas = document.getElementById('sessionDetailsOffcanvas');
        offcanvas.querySelector('.transform').classList.add('translate-x-full');
        setTimeout(function() {
            offcanvas.classList.add('hidden');
        }, 300);
    }

    function fetchActiveSessions() {
        $.ajax({
            url: "<?= BASE_URL ?>track/session_log.json",
            dataType: "json",
            cache: false,
            success: function(data) {
                activeSessions = data;
                var filtered = activeSessions;
                var searchVal = $("#searchActive").val().toLowerCase();
                if (searchVal) {
                    filtered = activeSessions.filter(function(s) {
                        var combined = s.sessionID + " " + s.ipAddress + " " + s.browser + " " + s.device + " " + s.country;
                        return combined.toLowerCase().indexOf(searchVal) !== -1;
                    });
                }
                renderSessions(filtered, "active-sessions-body", "active-sessions-mobile");
                $("#stat-active").text(activeSessions.length);
                if (currentOffcanvasSessionID) {
                    var sess = activeSessions.find(function(s) {
                        return s.sessionID === currentOffcanvasSessionID;
                    });
                    if (sess) {
                        updateOffcanvas(sess);
                    } else {
                        var content = document.getElementById('sessionDetailsContent');
                        if (content && content.innerHTML.indexOf("Session Ended") === -1) {
                            content.innerHTML += '<div class="text-red-500 italic mt-4">Session Ended</div>';
                        }
                    }
                }
            }
        });
    }

    // Updated: fetchPastSessions now calls the API endpoint with the given date range.
    function fetchPastSessions() {
        var start = $("#startDate").val();
        var end = $("#endDate").val();
        fetch(`${API_BASE}/getPastSessions?start=${start}&end=${end}`)
            .then(response => response.json())
            .then(data => {
                pastSessions = data;
                var filtered = pastSessions;
                var searchVal = $("#searchPast").val().toLowerCase();
                if (searchVal) {
                    filtered = pastSessions.filter(function(s) {
                        var combined = s.sessionID + " " + s.ipAddress + " " + s.browser + " " + s.device + " " + s.country;
                        return combined.toLowerCase().indexOf(searchVal) !== -1;
                    });
                }
                renderSessions(filtered, "past-sessions-body", "past-sessions-mobile");
                $("#stat-past").text(pastSessions.length);
            })
            .catch(err => {
                console.error(err);
            });
    }

    function formatToDateTimeLocal(jsDate) {
        var yyyy = jsDate.getFullYear();
        var mm = String(jsDate.getMonth() + 1).padStart(2, '0');
        var dd = String(jsDate.getDate()).padStart(2, '0');
        var hh = String(jsDate.getHours()).padStart(2, '0');
        var min = String(jsDate.getMinutes()).padStart(2, '0');
        return yyyy + "-" + mm + "-" + dd + "T" + hh + ":" + min;
    }

    // Get the current week's Sunday and Saturday
    function getCurrentWeekDates() {
        const now = new Date();
        const currentDay = now.getDay(); // 0 = Sunday, 1 = Monday, etc.

        // Calculate days to subtract to get to Sunday
        const daysToSunday = currentDay;
        const sunday = new Date(now);
        sunday.setDate(now.getDate() - daysToSunday);
        sunday.setHours(0, 0, 0, 0);

        // Calculate days to add to get to Saturday
        const daysToSaturday = 6 - currentDay;
        const saturday = new Date(now);
        saturday.setDate(now.getDate() + daysToSaturday);
        saturday.setHours(23, 59, 59, 999);

        return {
            start: sunday,
            end: saturday
        };
    }

    // Get last week's Sunday and Saturday
    function getLastWeekDates() {
        const {
            start,
            end
        } = getCurrentWeekDates();
        const lastWeekStart = new Date(start);
        lastWeekStart.setDate(start.getDate() - 7);

        const lastWeekEnd = new Date(end);
        lastWeekEnd.setDate(end.getDate() - 7);

        return {
            start: lastWeekStart,
            end: lastWeekEnd
        };
    }

    // Get today's start and end
    function getTodayDates() {
        const now = new Date();
        const start = new Date(now);
        start.setHours(0, 0, 0, 0);

        const end = new Date(now);
        end.setHours(23, 59, 59, 999);

        return {
            start,
            end
        };
    }

    // Get yesterday's start and end
    function getYesterdayDates() {
        const now = new Date();
        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);

        const start = new Date(yesterday);
        start.setHours(0, 0, 0, 0);

        const end = new Date(yesterday);
        end.setHours(23, 59, 59, 999);

        return {
            start,
            end
        };
    }

    // Get this month's start and end
    function getThisMonthDates() {
        const now = new Date();
        const start = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0, 0);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59, 999);

        return {
            start,
            end
        };
    }

    // Set date range based on preset
    function setDateRange(preset) {
        let dateRange;

        switch (preset) {
            case 'current-week':
                dateRange = getCurrentWeekDates();
                break;
            case 'last-week':
                dateRange = getLastWeekDates();
                break;
            case 'today':
                dateRange = getTodayDates();
                break;
            case 'yesterday':
                dateRange = getYesterdayDates();
                break;
            case 'this-month':
                dateRange = getThisMonthDates();
                break;
            default:
                dateRange = getCurrentWeekDates();
        }

        $("#startDate").val(formatToDateTimeLocal(dateRange.start));
        $("#endDate").val(formatToDateTimeLocal(dateRange.end));

        // Update active class on preset buttons
        $(".date-range-preset").removeClass("active");
        $(`.date-range-preset[data-range="${preset}"]`).addClass("active");
    }

    // Event handlers
    $("#filter-btn").on("click", function() {
        fetchActiveSessions();
        fetchPastSessions();
    });

    $("#searchActive").on("keyup", function(e) {
        if (e.key === "Enter") {
            fetchActiveSessions();
        }
    });

    $("#searchPast").on("keyup", function(e) {
        if (e.key === "Enter") {
            fetchPastSessions();
        }
    });

    $(document).on("click", ".mobile-row-header", function(e) {
        var row = $(this).closest(".mobile-row");
        var content = row.find(".accordion-content");
        var arrow = $(this).find(".accordion-arrow");
        $(".accordion-content").not(content).slideUp();
        $(".accordion-arrow").not(arrow).removeClass("active");
        if (content.is(":visible")) {
            content.slideUp();
            arrow.removeClass("active");
        } else {
            content.slideDown();
            arrow.addClass("active");
        }
    });

    // Date range preset buttons
    $(".date-range-preset").on("click", function() {
        const preset = $(this).data("range");
        setDateRange(preset);
        fetchPastSessions();
    });

    // Load more button
    $("#load-more").on("click", function() {
        // This would typically load more past sessions
        // For now, just refresh the current data
        fetchPastSessions();
        $(this).html('<i class="fas fa-check mr-2"></i>Refreshed');
        setTimeout(() => {
            $(this).html('<i class="fas fa-sync-alt mr-2"></i>Load More');
        }, 2000);
    });

    window.addEventListener("load", function() {
        // Set default date range to current week (Sunday to Saturday)
        setDateRange('current-week');
        fetchActiveSessions();
        fetchPastSessions();
    });

    setInterval(fetchActiveSessions, 5000);
    setInterval(fetchPastSessions, 60000);
</script>
<?php
$mainContent = ob_get_clean();
include __DIR__ . '/master.php';
?>