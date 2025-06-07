<?php
// track/index.php
date_default_timezone_set('Africa/Kampala');
require_once __DIR__ . '/../config/config.php';

$activeNav = $activeNav ?? null;
$pageTitle = $pageTitle ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Session Log Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 80vh;
            overflow-y: auto;
        }
    </style>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
</head>

<body>
    <h1>Session Log Viewer</h1>
    <p>Live view of all session logs saved on the server (auto-refreshes every 1 seconds):</p>
    <pre id="logOutput">Loading...</pre>

    <script>
        const outputEl = document.getElementById('logOutput');

        async function fetchSessionLog() {
            try {
                const response = await fetch(BASE_URL + 'track/session_log.json', { cache: "no-store" });
                if (!response.ok) throw new Error('Fetch failed');
                const data = await response.json();
                outputEl.textContent = JSON.stringify(data, null, 4);
            } catch (e) {
                outputEl.textContent = "❌ Failed to load session_log.json: " + e.message;
            }
        }

        // Initial fetch + repeat every 1 seconds
        fetchSessionLog();
        setInterval(fetchSessionLog, 1000);
    </script>
</body>

</html>