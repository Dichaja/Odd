$(function () {
    const SESSION_EXPIRY = 30 * 60 * 1000;
    const STORAGE_KEY = 'session_event_log';
    const TRACKER_URL = BASE_URL + 'track/s';

    function checkSessionExpired() {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;

        let s;
        try {
            s = JSON.parse(raw);
        } catch {
            return;
        }
        const sessionID = s.sessionID;
        if (!sessionID) return;

        $.getJSON(TRACKER_URL, { sessionID })
            .done(resp => {
                if (resp.expired) {
                    console.info('Session expired on server—clearing localStorage');
                    localStorage.removeItem(STORAGE_KEY);
                }
            })
            .fail(() => { });
    }

    checkSessionExpired();

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
        let raw = localStorage.getItem(STORAGE_KEY);
        let s = null;
        try {
            s = raw ? JSON.parse(raw) : null;
        } catch {
            s = null;
        }

        const currentUser = typeof LOGGED_USER !== 'undefined' ? LOGGED_USER : null;

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
                ipAddress: 'Fetching...',
                country: 'Fetching...',
                shortName: 'Fetching...',
                phoneCode: 'Fetching...',
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
        const currentUrl = location.href;
        const isRefresh =
            lastLog &&
            (lastLog.event === 'page_load' || lastLog.event === 'page_refresh') &&
            lastLog.url === currentUrl;

        const eventName = isRefresh ? 'page_refresh' : 'page_load';
        return logEvent(eventName, {
            url: currentUrl,
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

    function updateCoordsAndFlush(useFallback = true) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    const { latitude, longitude } = pos.coords;
                    const s = getSession();
                    s.coords = { latitude, longitude };

                    const nominatimUrl =
                        `https://nominatim.openstreetmap.org/reverse?format=json` +
                        `&lat=${latitude}&lon=${longitude}&zoom=3&addressdetails=1`;

                    $.getJSON(nominatimUrl)
                        .done(data => {
                            const country = data.address?.country || 'Unknown';
                            const shortName = data.address?.country_code || 'unknown';
                            const restCountriesUrl =
                                `https://restcountries.com/v3.1/alpha/${shortName}`;

                            $.getJSON(restCountriesUrl)
                                .done(rcData => {
                                    const cd = Array.isArray(rcData) ? rcData[0] : rcData;
                                    const phoneCode = cd?.idd?.root
                                        ? cd.idd.root + (cd.idd.suffixes?.[0] || '')
                                        : 'Unknown';

                                    s.country = country;
                                    s.shortName = shortName;
                                    s.phoneCode = phoneCode;

                                    const bd = getBrowserAndDevice();
                                    s.browser = bd.browser;
                                    s.device = bd.device;

                                    localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                                    const updated = logPageLoad();
                                    sendSessionToServer(updated);
                                })
                                .fail(() => {
                                    s.country = country;
                                    s.shortName = shortName;
                                    s.phoneCode = 'Unknown';
                                    localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
                                    const updated = logPageLoad();
                                    sendSessionToServer(updated);
                                });
                        })
                        .fail(() => {
                            const updated = logPageLoad();
                            sendSessionToServer(updated);
                        });
                },
                _err => {
                    if (useFallback) fetchIPAndLog();
                    else {
                        const updated = logPageLoad();
                        sendSessionToServer(updated);
                    }
                },
                { timeout: 10000 }
            );
        } else {
            if (useFallback) fetchIPAndLog();
            else {
                const updated = logPageLoad();
                sendSessionToServer(updated);
            }
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

    fetchIPAndLog();

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

        trackLoginModalOpen() {
            const s = this.logEvent('login_modal_open');
            this.sendSessionToServer(s);
        },

        trackLoginModalClose() {
            const s = this.logEvent('login_modal_close');
            this.sendSessionToServer(s);
        },

        trackFormSwitch(fromForm, toForm) {
            const s = this.logEvent('form_switch', { fromForm, toForm });
            this.sendSessionToServer(s);
        },

        trackLoginIdentifierSubmit(identifier, identifierType) {
            const s = this.logEvent('login_identifier_submit', { identifier, identifierType });
            this.sendSessionToServer(s);
        },

        trackLoginIdentifierSuccess(identifier, identifierType) {
            const s = this.logEvent('login_identifier_success', { identifier, identifierType });
            this.sendSessionToServer(s);
        },

        trackLoginIdentifierFailed(identifier, identifierType, errorMessage) {
            const s = this.logEvent('login_identifier_failed', { identifier, identifierType, errorMessage });
            this.sendSessionToServer(s);
        },

        trackLoginPasswordSubmit() {
            const s = this.logEvent('login_password_submit');
            this.sendSessionToServer(s);
        },

        trackLoginPasswordSuccess() {
            const s = this.logEvent('login_password_success');
            this.sendSessionToServer(s);
        },

        trackLoginPasswordFailed(errorMessage) {
            const s = this.logEvent('login_password_failed', { errorMessage });
            this.sendSessionToServer(s);
        },

        trackLoginSuccess() {
            const s = this.logEvent('login_success');
            this.sendSessionToServer(s);
        },

        trackPasswordResetRequest() {
            const s = this.logEvent('password_reset_requested');
            this.sendSessionToServer(s);
        },

        trackOtpValidation(status) {
            const s = this.logEvent('otp_validation', { status });
            this.sendSessionToServer(s);
        },

        trackPasswordResetComplete(status) {
            const s = this.logEvent('password_reset_completed', { status });
            this.sendSessionToServer(s);
        },

        trackRegistrationStart() {
            const s = this.logEvent('registration_start');
            this.sendSessionToServer(s);
        },

        trackRegistrationComplete(status) {
            const s = this.logEvent('registration_complete', { status });
            this.sendSessionToServer(s);
        },

        trackLoginMethodChange(method) {
            const s = this.logEvent('login_method_change', { method });
            this.sendSessionToServer(s);
        }
    };
});
