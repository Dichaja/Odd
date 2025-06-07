$(function () {
    const SESSION_EXPIRY = 2 * 60 * 1000;           // 2 minutes
    const STORAGE_KEY = 'session_event_log';
    const TRACKER_URL = BASE_URL + 'track/sessionTracker.php';

    function getBrowserAndDevice() {
        try {
            const parser = bowser.getParser(navigator.userAgent);
            return {
                browser: parser.getBrowser().name || 'Unknown',
                device: parser.getPlatformType(true) || 'Unknown'
            };
        } catch {
            return { browser: 'Unknown', device: 'Unknown' };
        }
    }

    function getSession() {
        let s = null;
        try {
            s = JSON.parse(localStorage.getItem(STORAGE_KEY));
        } catch {
            s = null;
        }

        const expired = s && (Date.now() - s.timestamp > SESSION_EXPIRY);

        if (!s || expired) {
            const bd = getBrowserAndDevice();
            s = {
                sessionID: typeof SESSION_ULID !== 'undefined' ? SESSION_ULID : null,
                timestamp: Date.now(),
                ipAddress: 'Fetching...',
                country: 'Fetching...',
                shortName: 'Fetching...',
                phoneCode: 'Fetching...',
                browser: bd.browser,
                device: bd.device,
                coords: { latitude: null, longitude: null },
                loggedUser: typeof LOGGED_USER !== 'undefined' ? LOGGED_USER : null,
                logs: []
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
        }

        return s;
    }

    function logEvent(eventName, details = {}) {
        const s = getSession();
        const e = {
            event: eventName,
            timestamp: new Date().toISOString(),
            referrer: eventName === 'page_load' ? document.referrer : undefined,
            ...details
        };
        s.logs.push(e);
        s.timestamp = Date.now();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
        return s;
    }

    function logPageLoad() {
        return logEvent('page_load', {
            url: location.href,
            activeNavigation: typeof ACTIVE_NAV !== 'undefined' ? ACTIVE_NAV : location.pathname,
            pageTitle: typeof PAGE_TITLE !== 'undefined' ? PAGE_TITLE : document.title
        });
    }

    function sendSessionToServer(sessionData) {
        $.ajax({
            url: TRACKER_URL,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(sessionData)
        });
    }

    function updateCoordsAndFlush() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const s = getSession();
                    s.coords = {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    };
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                    const updated = logPageLoad();
                    sendSessionToServer(updated);
                },
                _err => {
                    // user denied or error
                    const updated = logPageLoad();
                    sendSessionToServer(updated);
                },
                { timeout: 10000 }
            );
        } else {
            const updated = logPageLoad();
            sendSessionToServer(updated);
        }
    }

    function fetchIPAndLog() {
        $.getJSON('https://api64.ipify.org?format=json')
            .done(data => {
                const ip = data.ip;
                $.getJSON(`https://ipapi.co/${ip}/json/`)
                    .done(loc => {
                        let s = getSession();
                        if (s.ipAddress !== 'Fetching...' && s.ipAddress !== ip) {
                            localStorage.removeItem(STORAGE_KEY);
                            s = getSession();
                        }
                        s.ipAddress = ip;
                        s.country = loc.country_name || 'Unknown';
                        s.shortName = loc.country_code?.toLowerCase() || 'Unknown';
                        s.phoneCode = loc.country_calling_code || 'Unknown';
                        const bd = getBrowserAndDevice();
                        s.browser = bd.browser;
                        s.device = bd.device;
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                        updateCoordsAndFlush();
                    })
                    .fail(() => updateCoordsAndFlush());
            })
            .fail(() => updateCoordsAndFlush());
    }

    // Kick off initial tracking
    fetchIPAndLog();

    // Expose for logging other events, e.g. trackUserEvent('login', { method: 'sms' });
    window.trackUserEvent = function (eventName, details = {}) {
        const s = logEvent(eventName, details);
        sendSessionToServer(s);
    };
});
