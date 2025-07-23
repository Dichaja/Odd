$(function () {
    /* ───────────────────────── CONSTANTS ───────────────────────── */
    const SESSION_EXPIRY = 30 * 60 * 1000;          // 30 minutes
    const STORAGE_KEY = 'session_event_log';
    const TRACKER_URL = BASE_URL + 'track/s';

    // external endpoints
    const IP_ENDPOINT = 'https://api64.ipify.org?format=json';
    const IP_DETAILS = ip =>
        `https://ipwho.is/${ip}?fields=ip,country,country_code,calling_code,latitude,longitude`;

    /* ───────────────────────── SESSION HELPERS ─────────────────── */
    function checkSessionExpired() {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;

        let s;
        try { s = JSON.parse(raw); } catch { return; }

        const sessionID = s.sessionID;
        if (!sessionID) return;

        $.getJSON(TRACKER_URL, { sessionID })
            .done(resp => {
                if (resp.expired) {
                    console.info('Session expired on server—clearing localStorage');
                    localStorage.removeItem(STORAGE_KEY);
                }
            });
    }
    checkSessionExpired();

    function getBrowserAndDevice() {
        try {
            const p = bowser.getParser(navigator.userAgent);
            return {
                browser: p.getBrowser().name || 'Unknown',
                device: p.getPlatformType(true) || 'Unknown'
            };
        } catch {
            return { browser: 'Unknown', device: 'Unknown' };
        }
    }

    function getSession() {
        let raw = localStorage.getItem(STORAGE_KEY);
        let s = null;
        try { s = raw ? JSON.parse(raw) : null; } catch { s = null; }

        const currentUser = typeof LOGGED_USER !== 'undefined' ? LOGGED_USER : null;

        /* ── handle user switch within same browser tab ── */
        if (s) {
            if (s.loggedUser && currentUser && s.loggedUser.user_id !== currentUser.user_id) {
                localStorage.removeItem(STORAGE_KEY);
                s = null;
            } else if (!s.loggedUser && currentUser) {
                s.loggedUser = currentUser;
                localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
            }
        }

        const lastTsMs = s && s.timestamp ? Date.parse(s.timestamp) : 0;
        const expired = s && (Date.now() - lastTsMs > SESSION_EXPIRY);

        if (!s || expired) {
            const bd = getBrowserAndDevice();
            s = {
                sessionID: typeof SESSION_ULID !== 'undefined' ? SESSION_ULID : null,
                timestamp: new Date().toISOString(),
                ipAddress: 'Fetching…',
                country: 'Fetching…',
                shortName: 'Fetching…',
                phoneCode: 'Fetching…',
                browser: bd.browser,
                device: bd.device,
                coords: { latitude: null, longitude: null },
                loggedUser: currentUser,
                logs: []
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
        }
        return s;
    }

    /* ───────────────────────── LOGGING ─────────────────────────── */
    function logEvent(eventName, details = {}) {
        const s = getSession();
        const e = {
            event: eventName,
            timestamp: new Date().toISOString(),
            referrer: eventName === 'page_load' ? document.referrer : undefined,
            ...details
        };
        s.logs.push(e);
        s.timestamp = new Date().toISOString();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
        return s;
    }

    function logPageLoad() {
        const s = getSession();
        const lastLog = s.logs[s.logs.length - 1];
        const current = location.href;
        const isRefresh =
            lastLog &&
            (lastLog.event === 'page_load' || lastLog.event === 'page_refresh') &&
            lastLog.url === current;

        const eventName = isRefresh ? 'page_refresh' : 'page_load';
        return logEvent(eventName, {
            url: current,
            activeNavigation: typeof ACTIVE_NAV !== 'undefined' ? ACTIVE_NAV : location.pathname,
            pageTitle: typeof PAGE_TITLE !== 'undefined' ? PAGE_TITLE : document.title
        });
    }

    function sendSessionToServer(sessionData) {
        $.ajax({
            url: TRACKER_URL,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(sessionData),
            success(resp) {
                if (resp.status === 'error' && resp.message === 'Invalid session data.') {
                    console.error('Invalid session data from server—resetting session.');
                    localStorage.removeItem(STORAGE_KEY);
                    const fresh = getSession();
                    const init = logPageLoad();
                    sendSessionToServer(init);
                }
            }
        });
    }

    /* ───────────────────────── GEOLOCATION FIRST ───────────────── */
    function updateCoordsAndFlush(useFallback = true) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const { latitude, longitude } = pos.coords;
                    const s = getSession();
                    s.coords = { latitude, longitude };

                    /* ── reverse‑lookup just for nicer country/phone — optional ── */
                    const nominatimUrl =
                        `https://nominatim.openstreetmap.org/reverse?format=json` +
                        `&lat=${latitude}&lon=${longitude}&zoom=3&addressdetails=1`;

                    $.getJSON(nominatimUrl)
                        .done(data => {
                            const country = data.address?.country || 'Unknown';
                            const shortName = data.address?.country_code || 'unknown';
                            s.country = country;
                            s.shortName = shortName;

                            // keep phoneCode if we already got it from IP; otherwise try to resolve quickly
                            if (!s.phoneCode || s.phoneCode === 'Fetching…') {
                                const restUrl =
                                    `https://restcountries.com/v3.1/alpha/${shortName}`;
                                $.getJSON(restUrl)
                                    .done(rc => {
                                        const cd = Array.isArray(rc) ? rc[0] : rc;
                                        s.phoneCode =
                                            cd?.idd?.root
                                                ? cd.idd.root + (cd.idd.suffixes?.[0] || '')
                                                : 'Unknown';
                                        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));

                                        const up = logPageLoad();
                                        sendSessionToServer(up);
                                    })
                                    .fail(() => {
                                        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                                        const up = logPageLoad();
                                        sendSessionToServer(up);
                                    });
                            } else {
                                localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                                const up = logPageLoad();
                                sendSessionToServer(up);
                            }
                        })
                        .fail(() => {
                            const up = logPageLoad();
                            sendSessionToServer(up);
                        });
                },
                /* ── error / denied ── */
                _err => {
                    if (useFallback) fetchIPAndLog();   // will fill coords from IP
                    else {
                        const up = logPageLoad();
                        sendSessionToServer(up);
                    }
                },
                { timeout: 10000 }
            );
        } else if (useFallback) {
            fetchIPAndLog();
        } else {
            const up = logPageLoad();
            sendSessionToServer(up);
        }
    }

    /* ───────────────────────── IP + Fallback Coords ─────────────── */
    function fetchIPAndLog() {
        $.getJSON(IP_ENDPOINT)
            .done(({ ip }) => {
                $.getJSON(IP_DETAILS(ip))
                    .done(loc => {
                        let s = getSession();
                        /* reset session if IP changed mid‑way (rare) */
                        if (s.ipAddress !== 'Fetching…' && s.ipAddress !== ip) {
                            localStorage.removeItem(STORAGE_KEY);
                            s = getSession();
                        }

                        s.ipAddress = ip;
                        s.country = loc.country || 'Unknown';
                        s.shortName = (loc.country_code || 'unk').toLowerCase();
                        s.phoneCode = loc.calling_code || 'Unknown';

                        /* if geolocation later fails, use these coords */
                        if (!s.coords.latitude && loc.latitude && loc.longitude) {
                            s.coords = { latitude: loc.latitude, longitude: loc.longitude };
                        }

                        /* device / browser (re‑evaluate in case UA changed) */
                        const bd = getBrowserAndDevice();
                        s.browser = bd.browser;
                        s.device = bd.device;

                        localStorage.setItem(STORAGE_KEY, JSON.stringify(s));

                        /* now try to refine with real device coords,
                           but pass false to prevent infinite recursion */
                        updateCoordsAndFlush(false);
                    })
                    .fail(() => {
                        /* still have IP, just continue */
                        const up = logPageLoad();
                        sendSessionToServer(up);
                    });
            })
            .fail(() => {
                /* couldn't even get public IP — proceed without it */
                const up = logPageLoad();
                sendSessionToServer(up);
            });
    }

    /* ───────────────────────── STARTUP ──────────────────────────── */
    fetchIPAndLog();   // <‑‑ kicks everything off

    /* ───────────────────────── PUBLIC API ───────────────────────── */
    window.trackUserEvent = function (eventName, details = {}) {
        const s = logEvent(eventName, details);
        sendSessionToServer(s);
    };

    window.sessionTracker = {
        getSession,
        logEvent,
        logPageLoad,
        sendSessionToServer,
        fetchIPAndLog,
        updateCoordsAndFlush,

        trackLoginModalOpen() { sendSessionToServer(logEvent('login_modal_open')); },
        trackLoginModalClose() { sendSessionToServer(logEvent('login_modal_close')); },
        trackFormSwitch(f, t) { sendSessionToServer(logEvent('form_switch', { fromForm: f, toForm: t })); },
        trackLoginIdentifierSubmit(i, t) { sendSessionToServer(logEvent('login_identifier_submit', { identifier: i, identifierType: t })); },
        trackLoginIdentifierSuccess(i, t) { sendSessionToServer(logEvent('login_identifier_success', { identifier: i, identifierType: t })); },
        trackLoginIdentifierFailed(i, t, m) { sendSessionToServer(logEvent('login_identifier_failed', { identifier: i, identifierType: t, errorMessage: m })); },
        trackLoginPasswordSubmit() { sendSessionToServer(logEvent('login_password_submit')); },
        trackLoginPasswordSuccess() { sendSessionToServer(logEvent('login_password_success')); },
        trackLoginPasswordFailed(m) { sendSessionToServer(logEvent('login_password_failed', { errorMessage: m })); },
        trackLoginSuccess() { sendSessionToServer(logEvent('login_success')); },
        trackPasswordResetRequest() { sendSessionToServer(logEvent('password_reset_requested')); },
        trackOtpValidation(s) { sendSessionToServer(logEvent('otp_validation', { status: s })); },
        trackPasswordResetComplete(s) { sendSessionToServer(logEvent('password_reset_completed', { status: s })); },
        trackRegistrationStart() { sendSessionToServer(logEvent('registration_start')); },
        trackRegistrationComplete(s) { sendSessionToServer(logEvent('registration_complete', { status: s })); },
        trackLoginMethodChange(m) { sendSessionToServer(logEvent('login_method_change', { method: m })); }
    };
});
