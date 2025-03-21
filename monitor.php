<?php

require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Session Monitor</title>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
</head>

<body>
    <h1>Active Sessions Monitor</h1>
    <pre id="sessionOutput">Loading sessions...</pre>
    <script src="<?php echo BASE_URL; ?>track/sessionMonitor.js"></script>
</body>

</html>