// track/sessionMonitor.js

// Load jQuery if not already loaded
if (typeof jQuery === 'undefined') {
    var script = document.createElement('script');
    script.src = "https://code.jquery.com/jquery-3.6.0.min.js";
    script.type = "text/javascript";
    script.onload = function () { initializeSessionMonitor(); };
    document.getElementsByTagName('head')[0].appendChild(script);
} else {
    initializeSessionMonitor();
}

function initializeSessionMonitor() {
    $(document).ready(function () {
        const TRACKER_URL = BASE_URL + "track/sessionTracker.php";
        const refreshInterval = 5000; // Every 5 seconds

        function fetchSessions() {
            $.getJSON(TRACKER_URL, function (data) {
                console.clear();
                console.log("Active Sessions:", JSON.stringify(data, null, 4));
                if ($("#sessionOutput").length) {
                    $("#sessionOutput").text(JSON.stringify(data, null, 4));
                }
            });
        }
        setInterval(fetchSessions, refreshInterval);
        fetchSessions();
    });
}
