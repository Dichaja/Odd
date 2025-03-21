<?php
// track/index.php
date_default_timezone_set('Africa/Kampala');
require_once __DIR__ . '/../config/config.php';

// These variables are defined on a per‑page basis. If not set, default to NULL.
$activeNav = isset($activeNav) ? $activeNav : NULL;
$pageTitle = isset($pageTitle) ? $pageTitle : NULL;

$js_url = BASE_URL . "track/eventLog.js";

// Fetch the eventLog.js code via cURL for injection.
$ch = curl_init($js_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$js_code = curl_exec($ch);
curl_close($ch);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tracking Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
    <!-- Inject BASE_URL for all JS usage -->
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
        // Pass PHP page identifiers to JavaScript (default to NULL if not set)
        const ACTIVE_NAV = <?php echo ($activeNav !== NULL) ? json_encode($activeNav) : "null"; ?>;
        const PAGE_TITLE = <?php echo ($pageTitle !== NULL) ? json_encode($pageTitle) : "null"; ?>;
    </script>
    <!-- Load Bowser for browser detection (used for summarized browser and device info) -->
    <script src="https://cdn.jsdelivr.net/npm/bowser@2.11.0/es5.min.js"></script>
</head>

<body>
    <h1>Tracking Page</h1>
    <p>
        Page load events are logged (with Africa/Kampala time) and immediately sent to the server.
    </p>
    <pre id="logOutput">Local session log (from localStorage): Loading...</pre>
    <script>
        <?php echo $js_code; ?>

        // Display the locally stored session log (for debugging).
        function displayLocalSessionLog() {
            let sessionData = JSON.parse(localStorage.getItem("session_event_log"));
            if (sessionData) {
                document.getElementById("logOutput").textContent = JSON.stringify(sessionData, null, 4);
            } else {
                document.getElementById("logOutput").textContent = "No session data stored.";
            }
        }
        displayLocalSessionLog();
    </script>
</body>

</html>