// Load jQuery if not already loaded
if (typeof jQuery === 'undefined') {
    var script = document.createElement('script');
    script.src = "https://code.jquery.com/jquery-3.6.0.min.js";
    script.type = "text/javascript";
    script.onload = function () { initializeEventLog(); };
    document.getElementsByTagName('head')[0].appendChild(script);
} else {
    initializeEventLog();
}

function initializeEventLog() {
    $(document).ready(function () {
        const SESSION_EXPIRY = 2 * 60 * 1000; // 2 minutes (in ms)
        const STORAGE_KEY = "session_event_log";
        const TRACKER_URL = BASE_URL + "track/sessionTracker.php";

        // Generate a simplified UUIDv7
        function generateUUIDv7() {
            const now = new Date().getTime();
            return 'xxxxxxxx-xxxx-7xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = (now + Math.random() * 16) % 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        }

        // Get summarized browser and device info using Bowser.
        function getBrowserAndDevice() {
            const parser = bowser.getParser(window.navigator.userAgent);
            const browserInfo = parser.getBrowser();
            const deviceType = parser.getPlatformType(true); // e.g., "desktop", "mobile"
            return {
                browser: browserInfo.name || "Unknown",
                device: deviceType || "Unknown"
            };
        }

        // Get or create the session header from localStorage.
        function getSession() {
            let sessionData = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (!sessionData || (Date.now() - sessionData.timestamp) > SESSION_EXPIRY) {
                const bd = getBrowserAndDevice();
                sessionData = {
                    sessionID: generateUUIDv7(),
                    timestamp: Date.now(),
                    ipAddress: "Fetching...",
                    country: "Fetching...",
                    shortName: "Fetching...",  // Added shortName field
                    phoneCode: "Fetching...",
                    browser: bd.browser,
                    device: bd.device,
                    logs: []
                };
                localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
            }
            return sessionData;
        }

        // Ends the current session and starts a new one.
        function restartSession() {
            localStorage.removeItem(STORAGE_KEY);
            return getSession();
        }

        // Get current time in Africa/Kampala timezone as a formatted string.
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

        // Log a page load event.
        function logPageLoad() {
            let sessionData = getSession();
            let newLog = {
                event: "page_load",
                timestamp: getKampalaTime(),
                activeNavigation: (typeof ACTIVE_NAV !== "undefined" && ACTIVE_NAV !== null) ? ACTIVE_NAV : window.location.pathname,
                pageTitle: (typeof PAGE_TITLE !== "undefined" && PAGE_TITLE !== null) ? PAGE_TITLE : document.title
            };
            sessionData.logs.push(newLog);
            sessionData.timestamp = Date.now();
            localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));
            return sessionData;
        }

        // Immediately send the current session data to the server via POST.
        function sendSessionToServer(sessionData) {
            $.ajax({
                url: TRACKER_URL,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(sessionData)
            });
        }

        // Fetch IP details and update session data.
        function fetchIPAndLog() {
            $.getJSON("https://api64.ipify.org?format=json", function (data) {
                let fetchedIP = data.ip;
                $.getJSON(`https://ipapi.co/${fetchedIP}/json/`, function (locationData) {
                    let sessionData = getSession();

                    // If IP changed, restart session
                    if (sessionData.ipAddress !== "Fetching..." && sessionData.ipAddress !== fetchedIP) {
                        console.log("IP changed from", sessionData.ipAddress, "to", fetchedIP, "- restarting session.");
                        sessionData = restartSession();
                    }

                    // Update session with country data
                    sessionData.ipAddress = fetchedIP;
                    sessionData.country = locationData.country_name || "Unknown";
                    sessionData.shortName = locationData.country_code ? locationData.country_code.toLowerCase() : "Unknown"; // Fetch shortName
                    sessionData.phoneCode = locationData.country_calling_code ? locationData.country_calling_code : "Unknown";

                    // Update browser and device info
                    const bd = getBrowserAndDevice();
                    sessionData.browser = bd.browser;
                    sessionData.device = bd.device;

                    // Save updated session
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(sessionData));

                    // Log page load event and send to server
                    let updatedSession = logPageLoad();
                    sendSessionToServer(updatedSession);
                }).fail(function () {
                    console.error("Failed to fetch country details.");
                    let updatedSession = logPageLoad();
                    sendSessionToServer(updatedSession);
                });
            }).fail(function () {
                console.error("Failed to fetch IP address.");
                let updatedSession = logPageLoad();
                sendSessionToServer(updatedSession);
            });
        }

        // On page load, fetch IP details and log the event.
        fetchIPAndLog();
    });
}
