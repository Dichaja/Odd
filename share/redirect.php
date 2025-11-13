<?php
require_once __DIR__ . '/../config/config.php';

function q($sql, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function runSilent($sql, $params = [])
{
    try {
        q($sql, $params);
    } catch (Throwable $e) {
    }
}

function dbName()
{
    return q("SELECT DATABASE()")->fetchColumn();
}

function tableExists($table)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=:s AND table_name=:t", [':s' => dbName(), ':t' => $table])->fetchColumn() > 0;
}

function columnExists($table, $column)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=:s AND table_name=:t AND column_name=:c", [':s' => dbName(), ':t' => $table, ':c' => $column])->fetchColumn() > 0;
}

function indexExists($table, $index)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema=:s AND table_name=:t AND index_name=:i", [':s' => dbName(), ':t' => $table, ':i' => $index])->fetchColumn() > 0;
}

function fkExists($name)
{
    return (int) q("SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema=:s AND constraint_name=:n AND constraint_type='FOREIGN KEY'", [':s' => dbName(), ':n' => $name])->fetchColumn() > 0;
}

function ensureShareTables()
{
    q("CREATE TABLE IF NOT EXISTS share_links (
        id VARCHAR(26) PRIMARY KEY,
        code VARCHAR(16) NOT NULL UNIQUE,
        owner_user_id VARCHAR(26) NULL,
        target_type ENUM('product','store','home','category','search','custom','request-for-quote') NOT NULL,
        target_id VARCHAR(26) NULL,
        target_url TEXT NOT NULL,
        campaign VARCHAR(120) NULL,
        note VARCHAR(255) NULL,
        active TINYINT(1) DEFAULT 1,
        expires_at DATETIME NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_owner (owner_user_id),
        INDEX idx_target (target_type, target_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    q("CREATE TABLE IF NOT EXISTS share_link_clicks (
        id VARCHAR(26) PRIMARY KEY,
        share_link_id VARCHAR(26) NOT NULL,
        session_id VARCHAR(64) NULL,
        ip VARCHAR(45) NULL,
        user_agent TEXT NULL,
        referer TEXT NULL,
        utm_source VARCHAR(80) NULL,
        utm_medium VARCHAR(80) NULL,
        utm_campaign VARCHAR(120) NULL,
        utm_content VARCHAR(120) NULL,
        utm_term VARCHAR(120) NULL,
        country VARCHAR(100) NULL,
        country_code VARCHAR(8) NULL,
        phone_code VARCHAR(8) NULL,
        latitude DECIMAL(10,7) NULL,
        longitude DECIMAL(10,7) NULL,
        browser VARCHAR(50) NULL,
        device VARCHAR(30) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_clicks_link (share_link_id),
        INDEX idx_clicks_session (session_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    if (!fkExists('fk_share_clicks_link')) {
        runSilent("ALTER TABLE share_link_clicks ADD CONSTRAINT fk_share_clicks_link FOREIGN KEY (share_link_id) REFERENCES share_links(id) ON UPDATE CASCADE ON DELETE CASCADE");
    }
}

function ensureShareColumnsAndFKs()
{
    $defs = [
        ['t' => 'product_views', 'fk' => 'fk_pv_sl'],
        ['t' => 'store_profile_views', 'fk' => 'fk_spv_sl'],
        ['t' => 'store_contact_views', 'fk' => 'fk_scv_sl'],
        ['t' => 'product_price_views', 'fk' => 'fk_ppv_sl'],
        ['t' => 'request_for_quote', 'fk' => 'fk_rfq_sl'],
        ['t' => 'buy_in_store_requests', 'fk' => 'fk_bis_sl'],
        ['t' => 'pesapal_payments', 'fk' => 'fk_pay_sl'],
    ];
    foreach ($defs as $d) {
        $t = $d['t'];
        if (!tableExists($t))
            continue;
        if (!columnExists($t, 'share_link_id')) {
            runSilent("ALTER TABLE `$t` ADD COLUMN share_link_id VARCHAR(26) NULL");
        }
        $idx = "idx_{$t}_share_link_id";
        if (!indexExists($t, $idx)) {
            runSilent("CREATE INDEX $idx ON `$t` (share_link_id)");
        }
        if (!fkExists($d['fk'])) {
            runSilent("ALTER TABLE `$t` ADD CONSTRAINT {$d['fk']} FOREIGN KEY (share_link_id) REFERENCES share_links(id) ON UPDATE CASCADE ON DELETE SET NULL");
        }
    }
}

function ensureClickExtraColumns()
{
    $t = 'share_link_clicks';
    $need = [
        'country' => "ALTER TABLE `$t` ADD COLUMN country VARCHAR(100) NULL",
        'country_code' => "ALTER TABLE `$t` ADD COLUMN country_code VARCHAR(8) NULL",
        'phone_code' => "ALTER TABLE `$t` ADD COLUMN phone_code VARCHAR(8) NULL",
        'latitude' => "ALTER TABLE `$t` ADD COLUMN latitude DECIMAL(10,7) NULL",
        'longitude' => "ALTER TABLE `$t` ADD COLUMN longitude DECIMAL(10,7) NULL",
        'browser' => "ALTER TABLE `$t` ADD COLUMN browser VARCHAR(50) NULL",
        'device' => "ALTER TABLE `$t` ADD COLUMN device VARCHAR(30) NULL"
    ];
    foreach ($need as $col => $ddl) {
        if (!columnExists($t, $col))
            runSilent($ddl);
    }
}

function ensureMigrations()
{
    ensureShareTables();
    ensureShareColumnsAndFKs();
    ensureClickExtraColumns();
}

function loadHomepageHeroImages()
{
    $filePath = __DIR__ . '/../page-data/homepage/index.json';
    if (!file_exists($filePath))
        return [];
    $data = json_decode(file_get_contents($filePath), true) ?: [];
    $slides = $data['heroSlides'] ?? [];
    $imgs = [];
    foreach ($slides as $s) {
        if (!empty($s['active']) && !empty($s['image'])) {
            $imgs[] = BASE_URL . ltrim($s['image'], '/');
        }
    }
    return $imgs;
}

function firstExistingImage($arr, $fallback = '')
{
    foreach ($arr as $u) {
        if (is_string($u) && $u !== '')
            return $u;
    }
    return $fallback;
}

function getProductPrimaryImage($id)
{
    $dir = __DIR__ . '/../img/products/' . $id;
    $json = $dir . '/images.json';
    if (is_file($json)) {
        $data = json_decode(file_get_contents($json), true);
        $imgs = [];
        foreach (($data['images'] ?? []) as $f) {
            $imgs[] = filter_var($f, FILTER_VALIDATE_URL) ? $f : BASE_URL . "img/products/$id/$f";
        }
        $img = firstExistingImage($imgs);
        if ($img)
            return $img;
    }
    return '';
}

function getCategoryImage($id)
{
    $dir = __DIR__ . '/../img/product-categories/' . $id;
    if (is_dir($dir)) {
        $files = glob($dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [];
        if (!empty($files))
            return BASE_URL . 'img/product-categories/' . $id . '/' . basename($files[0]);
    }
    return '';
}

function buildOgForTarget($row)
{
    $type = $row['target_type'];
    $tid = $row['target_id'];
    $targetUrl = $row['target_url'];
    $site = 'Zzimba Online Uganda';
    $defaultDesc = 'Compare prices, request quotes, and get fast deliveries of building materials across Uganda.';
    $hero = loadHomepageHeroImages();
    $placeholder = 'https://placehold.co/1200x630/e2e8f0/1e293b?text=' . urlencode($site);
    $img = firstExistingImage($hero, $placeholder);
    $og = [
        'title' => $site,
        'description' => $defaultDesc,
        'image' => $img,
        'type' => 'website',
        'url' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ];

    if ($type === 'product' && $tid) {
        $p = q("SELECT p.id, p.title, p.description, c.name AS category_name
                FROM products p
                LEFT JOIN product_categories c ON c.id = p.category_id
                WHERE p.id=:id LIMIT 1", [':id' => $tid])->fetch(PDO::FETCH_ASSOC);
        if ($p) {
            $imgP = getProductPrimaryImage($p['id']);
            $og['title'] = $p['title'] . ' - ' . $site;
            $og['description'] = trim($p['description']) !== '' ? mb_substr(strip_tags($p['description']), 0, 180) : $defaultDesc;
            $og['image'] = $imgP ?: $img;
            $og['type'] = 'product';
        }
    } elseif ($type === 'category' && $tid) {
        $c = q("SELECT id, name, description FROM product_categories WHERE id=:id LIMIT 1", [':id' => $tid])->fetch(PDO::FETCH_ASSOC);
        if ($c) {
            $imgC = getCategoryImage($c['id']);
            $og['title'] = $c['name'] . ' - ' . $site;
            $og['description'] = trim($c['description']) !== '' ? mb_substr(strip_tags($c['description']), 0, 180) : $defaultDesc;
            $og['image'] = $imgC ?: $img;
            $og['type'] = 'website';
        }
    } elseif ($type === 'search') {
        $qstr = '';
        $parts = parse_url($targetUrl);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $qp);
            $qstr = isset($qp['q']) ? trim($qp['q']) : '';
        }
        $og['title'] = ($qstr ? ('Search: ' . $qstr . ' - ') : '') . $site;
        $og['description'] = $defaultDesc;
        $og['image'] = $img;
        $og['type'] = 'website';
    } elseif ($type === 'request-for-quote') {
        $og['title'] = 'Request for Quote - ' . $site;
        $og['description'] = 'Tell us what you need. We will source competitive quotes and coordinate fast delivery.';
        $og['image'] = $img;
        $og['type'] = 'website';
    } elseif ($type === 'store' && $tid) {
        $s = q("SELECT name, description, logo_url, vendor_cover_url FROM vendor_stores WHERE id=:id LIMIT 1", [':id' => $tid])->fetch(PDO::FETCH_ASSOC);
        if ($s) {
            $storeName = $s['name'] ?: 'Vendor Store';
            $og['title'] = $storeName . ' | Zzimba Store';
            $og['description'] = trim($s['description']) !== '' ? htmlspecialchars_decode(mb_substr(strip_tags($s['description']), 0, 180)) : 'Discover quality products and services at ' . $storeName . ' on Zzimba Online.';
            if (!empty($s['logo_url'])) {
                $og['image'] = BASE_URL . ltrim($s['logo_url'], '/');
            } elseif (!empty($s['vendor_cover_url'])) {
                $og['image'] = BASE_URL . ltrim($s['vendor_cover_url'], '/');
            } else {
                $og['image'] = 'https://placehold.co/1200x630?text=' . urlencode($storeName);
            }
            $og['type'] = 'website';
        }
    } elseif ($type === 'home') {
        $og['title'] = $site;
        $og['description'] = $defaultDesc;
        $og['image'] = $img;
        $og['type'] = 'website';
    } elseif ($type === 'custom') {
        $og['title'] = $site;
        $og['description'] = $defaultDesc;
        $og['image'] = $img;
        $og['type'] = 'website';
    }

    return $og;
}

ensureMigrations();

$a = $_GET['a'] ?? null;

if ($a === 'ulid') {
    header('Content-Type: application/json');
    echo json_encode(['id' => generateUlid()]);
    exit;
}

if ($a === 'click') {
    header('Content-Type: application/json');
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: [];
    $shareId = $data['share_link_id'] ?? null;
    if (!$shareId) {
        echo json_encode(['success' => false, 'error' => 'missing_share_link_id']);
        exit;
    }
    $exists = q("SELECT 1 FROM share_links WHERE id=:id LIMIT 1", [':id' => $shareId])->fetchColumn();
    if (!$exists) {
        echo json_encode(['success' => false, 'error' => 'invalid_share_link_id']);
        exit;
    }
    $sessionId = $data['session_id'] ?? null;
    $ip = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
    $ua = $data['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
    $ref = $data['referer'] ?? ($_SERVER['HTTP_REFERER'] ?? null);
    $us = $data['utm_source'] ?? null;
    $um = $data['utm_medium'] ?? null;
    $uc = $data['utm_campaign'] ?? null;
    $uco = $data['utm_content'] ?? null;
    $ut = $data['utm_term'] ?? null;
    $country = $data['country'] ?? null;
    $country_code = $data['country_code'] ?? null;
    $phone_code = $data['phone_code'] ?? null;
    $lat = isset($data['latitude']) ? (string) $data['latitude'] : null;
    $lng = isset($data['longitude']) ? (string) $data['longitude'] : null;
    $browser = $data['browser'] ?? null;
    $device = $data['device'] ?? null;

    q("INSERT INTO share_link_clicks (id, share_link_id, session_id, ip, user_agent, referer, utm_source, utm_medium, utm_campaign, utm_content, utm_term, country, country_code, phone_code, latitude, longitude, browser, device) VALUES (:id,:sid,:sess,:ip,:ua,:ref,:us,:um,:uc,:uco,:ut,:country,:cc,:pc,:lat,:lng,:br,:dv)", [
        ':id' => generateUlid(),
        ':sid' => $shareId,
        ':sess' => $sessionId,
        ':ip' => $ip,
        ':ua' => $ua,
        ':ref' => $ref,
        ':us' => $us,
        ':um' => $um,
        ':uc' => $uc,
        ':uco' => $uco,
        ':ut' => $ut,
        ':country' => $country,
        ':cc' => $country_code,
        ':pc' => $phone_code,
        ':lat' => $lat,
        ':lng' => $lng,
        ':br' => $browser,
        ':dv' => $device
    ]);
    echo json_encode(['success' => true]);
    exit;
}

$code = $_GET['c'] ?? null;
if (!$code) {
    header('Location: ' . rtrim(BASE_URL, '/'));
    exit;
}

$link = q("SELECT id, code, target_type, target_id, target_url, active, expires_at FROM share_links WHERE code=:c LIMIT 1", [':c' => $code])->fetch(PDO::FETCH_ASSOC);
if (!$link) {
    header('Location: ' . rtrim(BASE_URL, '/'));
    exit;
}
if ((int) $link['active'] !== 1) {
    header('Location: ' . rtrim(BASE_URL, '/'));
    exit;
}
if (!empty($link['expires_at']) && strtotime($link['expires_at']) < time()) {
    header('Location: ' . rtrim(BASE_URL, '/'));
    exit;
}

$shareId = $link['id'];
$target = $link['target_url'];
$hasQuery = parse_url($target, PHP_URL_QUERY) !== null;
$glue = $hasQuery ? '&' : '?';
if (strpos($target, 'sl=') === false) {
    $target .= $glue . 'sl=' . rawurlencode($shareId);
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$cookie = 'Path=/; Max-Age=604800; SameSite=Lax';
if ($isHttps)
    $cookie .= '; Secure';
header("Set-Cookie: sl={$shareId}; {$cookie}");

$uaServer = $_SERVER['HTTP_USER_AGENT'] ?? '';
$refServer = $_SERVER['HTTP_REFERER'] ?? '';
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

$og = buildOgForTarget($link);
$title = htmlspecialchars($og['title'], ENT_QUOTES, 'UTF-8');
$desc = htmlspecialchars($og['description'], ENT_QUOTES, 'UTF-8');
$imgUrl = htmlspecialchars($og['image'], ENT_QUOTES, 'UTF-8');
$ogType = htmlspecialchars($og['type'], ENT_QUOTES, 'UTF-8');
$ogUrl = htmlspecialchars($og['url'], ENT_QUOTES, 'UTF-8');

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $desc; ?>">
    <meta property="og:title" content="<?php echo $title; ?>">
    <meta property="og:description" content="<?php echo $desc; ?>">
    <meta property="og:image" content="<?php echo $imgUrl; ?>">
    <meta property="og:type" content="<?php echo $ogType; ?>">
    <meta property="og:url" content="<?php echo $ogUrl; ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="<?php echo $desc; ?>">
    <meta name="twitter:image" content="<?php echo $imgUrl; ?>">
    <link rel="canonical" href="<?php echo $ogUrl; ?>">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-950 text-white">
    <div class="w-full min-h-screen flex items-center justify-center">
        <div class="flex flex-col items-center gap-6">
            <div class="relative w-16 h-16">
                <div class="absolute inset-0 rounded-full border-4 border-gray-800"></div>
                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-white animate-spin">
                </div>
            </div>
            <div class="text-center">
                <p class="text-xl font-semibold">Redirecting</p>
                <p class="text-sm text-gray-400 mt-1">Please wait</p>
            </div>
        </div>
    </div>
    <script>
        const STORAGE_KEY = 'session_event_log';
        const SHARE_ID = <?php echo json_encode($shareId); ?>;
        const TARGET_URL = <?php echo json_encode($target); ?>;
        const SERVER_UA = <?php echo json_encode($uaServer); ?>;
        const SERVER_REF = <?php echo json_encode($refServer); ?>;
        const SERVER_IP = <?php echo json_encode($remoteIp); ?>;
        const BASE_URL = <?php echo json_encode(rtrim(BASE_URL, '/')); ?>;

        function getBrowserAndDevice() {
            const ua = navigator.userAgent || '';
            const isMobile = /Mobi|Android/i.test(ua);
            const isTablet = /Tablet|iPad/i.test(ua);
            let device = 'desktop';
            if (isTablet) device = 'tablet';
            else if (isMobile) device = 'mobile';
            let browser = 'Unknown';
            if (ua.includes('Firefox/')) browser = 'Firefox';
            else if (ua.includes('Edg/')) browser = 'Edge';
            else if (ua.includes('OPR/') || ua.includes('Opera')) browser = 'Opera';
            else if (ua.includes('Chrome/')) browser = 'Chrome';
            else if (ua.includes('Safari/') && !ua.includes('Chrome/')) browser = 'Safari';
            return { browser, device };
        }

        async function fetchUlid() {
            const r = await fetch(window.location.pathname + '?a=ulid', { credentials: 'same-origin' });
            const j = await r.json();
            return j.id;
        }

        async function ensureSessionObject() {
            let objRaw = localStorage.getItem(STORAGE_KEY);
            if (objRaw) {
                try {
                    const parsed = JSON.parse(objRaw);
                    if (parsed && typeof parsed === 'object' && parsed.sessionID) return parsed;
                } catch (e) { }
            }
            const sessionID = await fetchUlid();
            const { browser, device } = getBrowserAndDevice();
            const now = new Date().toISOString();
            const base = {
                sessionID,
                timestamp: now,
                ipAddress: '',
                country: '',
                shortName: '',
                phoneCode: '',
                browser,
                device,
                coords: {},
                loggedUser: null,
                logs: []
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(base));
            return base;
        }

        function saveSession(obj) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(obj));
        }

        async function getPublicIp() {
            try {
                const r = await fetch('https://api.ipify.org?format=json', { cache: 'no-store' });
                const j = await r.json();
                return j.ip || '';
            } catch (e) {
                return SERVER_IP || '';
            }
        }

        async function enrichFromIpwho(ip) {
            try {
                const r = await fetch('https://ipwho.is/' + encodeURIComponent(ip), { cache: 'no-store' });
                const j = await r.json();
                if (j && j.success !== false) {
                    return {
                        country: j.country || '',
                        shortName: (j.country_code || '').toLowerCase(),
                        phoneCode: j.calling_code ? String(j.calling_code).replace('+', '') : '',
                        latitude: typeof j.latitude === 'number' ? j.latitude : null,
                        longitude: typeof j.longitude === 'number' ? j.longitude : null
                    };
                }
            } catch (e) { }
            return { country: '', shortName: '', phoneCode: '', latitude: null, longitude: null };
        }

        function geoPromise(timeoutMs = 3500) {
            return new Promise(resolve => {
                let done = false;
                const timer = setTimeout(() => {
                    if (done) return;
                    done = true;
                    resolve(null);
                }, timeoutMs);
                if (!navigator.geolocation) {
                    clearTimeout(timer);
                    resolve(null);
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        if (done) return;
                        done = true;
                        clearTimeout(timer);
                        resolve({ latitude: pos.coords.latitude, longitude: pos.coords.longitude });
                    },
                    () => {
                        if (done) return;
                        done = true;
                        clearTimeout(timer);
                        resolve(null);
                    },
                    { enableHighAccuracy: false, timeout: timeoutMs, maximumAge: 60000 }
                );
            });
        }

        function parseUtmParams() {
            const u = new URL(window.location.href);
            return {
                utm_source: u.searchParams.get('utm_source'),
                utm_medium: u.searchParams.get('utm_medium'),
                utm_campaign: u.searchParams.get('utm_campaign'),
                utm_content: u.searchParams.get('utm_content'),
                utm_term: u.searchParams.get('utm_term')
            };
        }

        async function logClick(payload) {
            const r = await fetch(window.location.pathname + '?a=click', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            try { await r.json(); } catch (e) { }
        }

        function redirectNow() {
            window.location.replace(TARGET_URL);
        }

        (async () => {
            const sessionObj = await ensureSessionObject();
            if (!sessionObj.sessionID) {
                try { sessionObj.sessionID = await fetchUlid(); } catch (e) { }
            }
            const { browser, device } = getBrowserAndDevice();
            sessionObj.browser = browser;
            sessionObj.device = device;

            let ip = sessionObj.ipAddress && sessionObj.ipAddress.length > 3 ? sessionObj.ipAddress : '';
            if (!ip) {
                ip = await getPublicIp();
                if (ip) sessionObj.ipAddress = ip;
            }

            let country = sessionObj.country || '';
            let shortName = sessionObj.shortName || '';
            let phoneCode = sessionObj.phoneCode || '';
            let latitude = null;
            let longitude = null;

            if (ip && (!country || !shortName || !phoneCode)) {
                const enrich = await enrichFromIpwho(ip);
                country = enrich.country || country;
                shortName = enrich.shortName || shortName;
                phoneCode = enrich.phoneCode || phoneCode;
                latitude = enrich.latitude;
                longitude = enrich.longitude;
                sessionObj.country = country;
                sessionObj.shortName = shortName;
                sessionObj.phoneCode = phoneCode;
            }

            const geo = await geoPromise();
            if (geo && typeof geo.latitude === 'number' && typeof geo.longitude === 'number') {
                sessionObj.coords = { latitude: geo.latitude, longitude: geo.longitude };
                latitude = geo.latitude;
                longitude = geo.longitude;
            } else if (!sessionObj.coords || typeof sessionObj.coords.latitude !== 'number') {
                if (latitude != null && longitude != null) {
                    sessionObj.coords = { latitude, longitude };
                }
            }

            saveSession(sessionObj);

            const utm = parseUtmParams();
            const payload = {
                share_link_id: SHARE_ID,
                session_id: sessionObj.sessionID || null,
                ip: sessionObj.ipAddress || SERVER_IP || null,
                user_agent: navigator.userAgent || SERVER_UA || null,
                referer: document.referrer || SERVER_REF || null,
                utm_source: utm.utm_source,
                utm_medium: utm.utm_medium,
                utm_campaign: utm.utm_campaign,
                utm_content: utm.utm_content,
                utm_term: utm.utm_term,
                country: sessionObj.country || null,
                country_code: sessionObj.shortName || null,
                phone_code: sessionObj.phoneCode || null,
                latitude: (sessionObj.coords && typeof sessionObj.coords.latitude === 'number') ? sessionObj.coords.latitude : null,
                longitude: (sessionObj.coords && typeof sessionObj.coords.longitude === 'number') ? sessionObj.coords.longitude : null,
                browser: sessionObj.browser || browser || null,
                device: sessionObj.device || device || null
            };

            const guard = new Promise(resolve => setTimeout(resolve, 2500));
            try { await Promise.race([logClick(payload), guard]); } catch (e) { }
            redirectNow();
        })();
    </script>
</body>

</html>