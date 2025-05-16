(function loadDependencies() {
    function loadULID() {
        if (typeof ulid === 'undefined') {
            var ulidScript = document.createElement('script');
            ulidScript.src = '';
            ulidScript.onload = initializeEventLog;
            document.head.appendChild(ulidScript);
        } else {
            initializeEventLog();
        }
    }

    if (typeof jQuery === 'undefined') {
        var jqScript = document.createElement('script');
        jqScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        jqScript.onload = loadULID;
        document.head.appendChild(jqScript);
    } else {
        loadULID();
    }
})();

function initializeEventLog() {
    $(function () {
        const SESSION_EXPIRY = 2 * 60 * 1000;
        const STORAGE_KEY = 'session_event_log';
        const TRACKER_URL = BASE_URL + 'track/sessionTracker.php';

        function getBrowserAndDevice() {
            const parser = bowser.getParser(window.navigator.userAgent);
            const browser = parser.getBrowser().name || 'Unknown';
            const deviceType = parser.getPlatformType(true) || 'Unknown';
            return { browser, device: deviceType };
        }

        function getSession() {
            let sessionData = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (!sessionData || (Date.now() - sessionData.timestamp) > SESSION_EXPIRY) {
                const bd = getBrowserAndDevice();
                sessionData = {
                    sessionID: ulid(),
                    timestamp: Date.now(),
                    ipAddress: 'Fetching...',
                    country: 'Fetching...',
                    shortName: 'Fetching...',
                    phoneCode: 'Fetching...',
                    browser: bd.browser,
                    device: bd.device,
                    logs: []
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
            }
            return sessionData;
        }

        function restartSession() {
            localStorage.removeItem(STORAGE_KEY);
            return getSession();
        }

        function getKampalaTime() {
            return new Intl.DateTimeFormat('en-UG', {
                timeZone: 'Africa/Kampala',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            }).format(new Date());
        }

        function logPageLoad() {
            let sessionData = getSession();
            sessionData.logs.push({
                event: 'page_load',
                timestamp: getKampalaTime(),
                activeNavigation: typeof ACTIVE_NAV !== 'undefined' && ACTIVE_NAV ? ACTIVE_NAV : location.pathname,
                pageTitle: typeof PAGE_TITLE !== 'undefined' && PAGE_TITLE ? PAGE_TITLE : document.title
            });
            sessionData.timestamp = Date.now();
            localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
            return sessionData;
        }

        function sendSessionToServer(sessionData) {
            $.ajax({
                url: TRACKER_URL,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(sessionData)
            });
        }

        function fetchIPAndLog() {
            $.getJSON('https://api64.ipify.org?format=json')
                .done(function (data) {
                    const fetchedIP = data.ip;
                    $.getJSON(`https://ipapi.co/${fetchedIP}/json/`)
                        .done(function (loc) {
                            let sessionData = getSession();
                            if (sessionData.ipAddress !== 'Fetching...' && sessionData.ipAddress !== fetchedIP) {
                                sessionData = restartSession();
                            }
                            sessionData.ipAddress = fetchedIP;
                            sessionData.country = loc.country_name || 'Unknown';
                            sessionData.shortName = loc.country_code ? loc.country_code.toLowerCase() : 'Unknown';
                            sessionData.phoneCode = loc.country_calling_code || 'Unknown';
                            const bd = getBrowserAndDevice();
                            sessionData.browser = bd.browser;
                            sessionData.device = bd.device;
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
                            const updated = logPageLoad();
                            sendSessionToServer(updated);
                        })
                        .fail(function () {
                            console.error('Failed to fetch country details.');
                            const updated = logPageLoad();
                            sendSessionToServer(updated);
                        });
                })
                .fail(function () {
                    console.error('Failed to fetch IP address.');
                    const updated = logPageLoad();
                    sendSessionToServer(updated);
                });
        }

        fetchIPAndLog();
    });
}
