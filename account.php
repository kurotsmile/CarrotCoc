<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/coc_helpers.php';
require __DIR__ . '/includes/visit_tracker.php';

visit_track_daily_ip($pdo ?? null);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$account = $pdo instanceof PDO ? coc_fetch_account($pdo, $id) : null;

if (!$account) {
    http_response_code(404);
}

$data = $account ? coc_decode_json($account['data']) : [];
$objectNameMap = coc_object_name_map();
$photos = $account ? array_values(array_filter(coc_decode_photos($account['photos']), fn($photo) => filter_var($photo, FILTER_VALIDATE_URL))) : [];
$th = $account ? coc_account_hall($account) : 0;
$supercellGroups = [
    'heroes' => ['title' => 'Heroes', 'icon' => 'bi-person-badge'],
    'units' => ['title' => 'Troops', 'icon' => 'bi-crosshair'],
    'spells' => ['title' => 'Spells', 'icon' => 'bi-stars'],
    'equipment' => ['title' => 'Equipment', 'icon' => 'bi-gem'],
    'buildings' => ['title' => 'Buildings', 'icon' => 'bi-house-gear'],
    'units2' => ['title' => 'Builder Troops', 'icon' => 'bi-hammer'],
    'skins' => ['title' => 'Skins', 'icon' => 'bi-palette'],
    'pets' => ['title' => 'Pets', 'icon' => 'bi-heart'],
    'obstacles' => ['title' => 'Obstacles', 'icon' => 'bi-tree'],
];
$paypalConfig = require __DIR__ . '/config/paypal.php';
$paypalClientId = $paypalConfig['client_id'] ?? '';
$paypalCurrency = $paypalConfig['currency'] ?? 'USD';
$siteUrl = 'https://coc.carrot28.com';
$seoTitle = $account
    ? $account['name'] . ' - Mua Acc COC Town Hall ' . ($th ?: 'N/A') . ' Giá Rẻ'
    : 'Không tìm thấy acc - COC Shop';
$seoDescription = $account
    ? 'Mua tài khoản Clash of Clans ' . $account['name'] . ', Town Hall ' . ($th ?: 'N/A') . ', giá ' . coc_money($account['price']) . '. Thanh toán PayPal và nhận login nhanh sau khi thanh toán thành công.'
    : 'Tài khoản Clash of Clans không tồn tại hoặc đã được gỡ khỏi COC Shop.';
$canonicalUrl = $account ? $siteUrl . '/account.php?id=' . (int) $account['id'] : $siteUrl . '/';
$seoImage = $account && $account['avatar'] ? $account['avatar'] : $siteUrl . '/assets/coc_logo.png';

function coc_timer_deadline($value, ?string $updatedAt): ?int
{
    $updatedTimestamp = $updatedAt ? strtotime($updatedAt) : false;

    if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
        $duration = (int) $value;
        if ($duration <= 0 || $updatedTimestamp === false) {
            return null;
        }

        if ($duration > 31536000) {
            $duration = (int) floor($duration / 1000);
        }

        return $updatedTimestamp + $duration;
    }

    if (is_string($value) && trim($value) !== '') {
        $timestamp = strtotime($value);
        return $timestamp !== false ? $timestamp : null;
    }

    return null;
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="keywords" content="acc coc giá rẻ, mua tài khoản clash of clan, mua acc clash of clans, acc town hall <?= htmlspecialchars((string) $th) ?>, shop acc coc uy tín">
    <meta name="robots" content="<?= $account ? 'index, follow, max-image-preview:large' : 'noindex, follow' ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <link rel="sitemap" type="application/xml" href="<?= htmlspecialchars($siteUrl . '/sitemap.xml') ?>">
    <meta property="og:type" content="<?= $account ? 'product' : 'website' ?>">
    <meta property="og:site_name" content="COC Shop">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(coc_asset('favicon/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars(coc_asset('favicon/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars(coc_asset('favicon/favicon-16x16.png')) ?>">
    <link rel="icon" href="<?= htmlspecialchars(coc_asset('favicon/favicon.ico')) ?>">
    <link rel="manifest" href="<?= htmlspecialchars(coc_asset('favicon/site.webmanifest')) ?>">
    <meta name="theme-color" content="#071625">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(coc_asset('assets/css/style.css?v11')) ?>" rel="stylesheet">
    <?php if ($account): ?>
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $account['name'],
        'description' => $seoDescription,
        'image' => [$seoImage],
        'brand' => [
            '@type' => 'Brand',
            'name' => 'Clash of Clans',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonicalUrl,
            'priceCurrency' => $paypalCurrency,
            'price' => number_format((float) $account['price'], 2, '.', ''),
            'availability' => 'https://schema.org/InStock',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
    <?php endif; ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark glass-nav">
    <div class="container py-2 nav-inner">
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="index.php">
            <img class="brand-mark" src="<?= htmlspecialchars(coc_asset('favicon/apple-touch-icon.png')) ?>" alt="COC Shop">
            <span>COC Shop</span>
        </a>
        <form class="nav-search" method="get" action="index.php" role="search">
            <span class="nav-search-icon"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input class="form-control" type="search" name="q" placeholder="Tìm acc Clash of Clans" aria-label="Tìm acc Clash of Clans">
        </form>
    </div>
</nav>

<main class="container py-5">
    <?php if (!$account): ?>
        <div class="glass-panel p-5 text-center">
            <h1 class="h3">Không tìm thấy acc</h1>
            <a class="btn btn-coc mt-3" href="index.php">Quay lại trang chủ</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="detail-media p-3">
                    <img class="w-100 rounded-2" src="<?= htmlspecialchars($account['avatar']) ?>" alt="<?= htmlspecialchars($account['name']) ?>">
                </div>
                <?php if ($photos): ?>
                    <div class="photo-strip mt-3">
                        <h2 class="h6 fw-bold mb-3">Ảnh chi tiết</h2>
                        <div class="row g-3">
                        <?php foreach ($photos as $photo): ?>
                            <div class="col-sm-6"><img src="<?= htmlspecialchars($photo) ?>" alt="Ảnh chi tiết"></div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-5">
                <div class="glass-panel p-4 sticky-lg-top" style="top: 1rem;">
                    <p class="muted-text text-uppercase fw-bold mb-2">Town Hall <?= $th ?: 'N/A' ?></p>
                    <h1 class="h2 fw-bold"><?= htmlspecialchars($account['name']) ?></h1>
                    <div class="price-pill fs-5 my-3"><?= coc_money($account['price']) ?></div>

                    <div class="row g-3 my-3">
                        <?php foreach (coc_summary_counts($data) as $label => $count): ?>
                            <div class="col-6">
                                <div class="stat-tile">
                                    <div class="h4 mb-0"><?= (int) $count ?></div>
                                    <div class="muted-text small"><?= htmlspecialchars($label) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($paypalClientId): ?>
                        <div id="paypal-button-container" class="mt-4"></div>
                    <?php else: ?>
                        <div class="alert alert-info mt-4">Cần cấu hình <code>PAYPAL_CLIENT_ID</code> và <code>PAYPAL_CLIENT_SECRET</code> để bật thanh toán PayPal.</div>
                    <?php endif; ?>

                    <div id="secret-box" class="secret-box alert alert-success mt-4">
                        <h2 class="h5">Thanh toán thành công</h2>
                        <p class="mb-1">Username: <strong id="secret-username"></strong></p>
                        <p class="mb-0">Password: <strong id="secret-password"></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <section class="supercell-section glass-panel p-4 mt-4">
            <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
                <div>
                    <p class="text-uppercase fw-bold muted-text mb-2">Account profile</p>
                    <h2 class="h4 mb-0">Dữ liệu Supercell</h2>
                </div>
                <span class="supercell-total"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i><?= array_sum(coc_summary_counts($data)) ?> mục chính</span>
            </div>

            <div class="supercell-overview mb-4">
                <div class="supercell-metric">
                    <i class="bi bi-bank" aria-hidden="true"></i>
                    <span>Town Hall</span>
                    <strong><?= $th ?: 'N/A' ?></strong>
                </div>
                <?php foreach (coc_summary_counts($data) as $label => $count): ?>
                    <div class="supercell-metric">
                        <i class="bi bi-layers" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($label) ?></span>
                        <strong><?= (int) $count ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="supercell-grid">
                <?php foreach ($supercellGroups as $key => $group): ?>
                    <?php
                    $items = $data[$key] ?? [];
                    if (!is_array($items) || !$items) {
                        continue;
                    }
                    ?>
                    <article class="supercell-card">
                        <header class="supercell-card-head">
                            <span class="supercell-icon"><i class="bi <?= htmlspecialchars($group['icon']) ?>" aria-hidden="true"></i></span>
                            <span>
                                <span class="supercell-kicker"><?= htmlspecialchars($key) ?></span>
                                <strong><?= htmlspecialchars($group['title']) ?></strong>
                            </span>
                            <span class="supercell-count"><?= count($items) ?></span>
                        </header>
                        <div class="supercell-items">
                            <?php foreach ($items as $item): ?>
                                <?php if (!is_array($item)) continue; ?>
                                <?php
                                $itemName = coc_object_display_name($item, $objectNameMap);
                                $itemLevel = $item['lvl'] ?? $item['level'] ?? null;
                                $objectId = (string) ($item['data'] ?? $item['id'] ?? '');
                                $objectImage = '';
                                if ($objectId !== '') {
                                    $objectImageCandidates = [
                                        'assets/objects/' . $key . '/' . $objectId . '.png',
                                        'assets/objects/' . $key . '/' . $objectId . '.webp',
                                        'assets/objects/' . $key . '/' . $objectId . '.jpg',
                                        'assets/objects/' . $objectId . '.png',
                                        'assets/objects/' . $objectId . '.webp',
                                        'assets/objects/' . $objectId . '.jpg',
                                    ];
                                    foreach ($objectImageCandidates as $candidate) {
                                        if (is_file(__DIR__ . '/' . $candidate)) {
                                            $objectImage = $candidate;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="supercell-item">
                                    <div class="supercell-item-main">
                                        <span class="supercell-object">
                                            <?php if ($objectImage): ?>
                                                <img src="<?= htmlspecialchars(coc_asset($objectImage)) ?>" alt="<?= htmlspecialchars($itemName) ?>">
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($itemName) ?></span>
                                        </span>
                                        <?php if ($itemLevel !== null): ?>
                                            <strong>Lv <?= htmlspecialchars((string) $itemLevel) ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="supercell-chips">
                                        <?php foreach ($item as $attribute => $value): ?>
                                            <?php if (in_array($attribute, ['name', 'data', 'id', 'lvl', 'level'], true) || is_array($value)) continue; ?>
                                            <?php
                                            $isTimer = strtolower((string) $attribute) === 'timer';
                                            $timerTimestamp = $isTimer ? coc_timer_deadline($value, $account['updated_at'] ?? null) : null;
                                            if ($isTimer && (!$timerTimestamp || $timerTimestamp <= time())) {
                                                continue;
                                            }
                                            ?>
                                            <?php if ($isTimer): ?>
                                                <span class="supercell-timer" data-timer="<?= (int) $timerTimestamp ?>"><i class="bi bi-clock" aria-hidden="true"></i><span data-timer-label></span></span>
                                            <?php else: ?>
                                                <span><?= htmlspecialchars((string) $attribute) ?>: <?= htmlspecialchars((string) $value) ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if (!$data): ?>
                <div class="text-center muted-text py-4">Không có dữ liệu Supercell để hiển thị.</div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php if ($account && $paypalClientId): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= urlencode($paypalClientId) ?>&currency=<?= urlencode($paypalCurrency) ?>"></script>
<script>
paypal.Buttons({
    createOrder: () => fetch('api/paypal_create_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({account_id: <?= (int) $account['id'] ?>})
    }).then(response => response.json()).then(payload => {
        if (!payload.id) throw new Error(payload.error || 'Không tạo được PayPal order');
        return payload.id;
    }),
    onApprove: (data) => fetch('api/paypal_capture_order.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({order_id: data.orderID})
    }).then(response => response.json()).then(payload => {
        if (!payload.success) throw new Error(payload.error || 'Không capture được PayPal order');
        document.getElementById('secret-username').textContent = payload.username;
        document.getElementById('secret-password').textContent = payload.password;
        document.getElementById('secret-box').classList.add('is-visible');
    }).catch(error => alert(error.message))
}).render('#paypal-button-container');
</script>
<?php endif; ?>
<script>
(() => {
    const timers = Array.from(document.querySelectorAll('[data-timer]'));
    if (!timers.length) return;

    const formatRemaining = (seconds) => {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        const clock = [hours, minutes, secs].map((part) => String(part).padStart(2, '0')).join(':');
        return days > 0 ? `${days}d ${clock}` : clock;
    };

    const tick = () => {
        const now = Math.floor(Date.now() / 1000);
        timers.forEach((timer) => {
            const target = Number(timer.dataset.timer || 0);
            const remaining = target - now;
            if (remaining <= 0) {
                timer.hidden = true;
                return;
            }

            const label = timer.querySelector('[data-timer-label]');
            if (label) label.textContent = formatRemaining(remaining);
        });
    };

    tick();
    setInterval(tick, 1000);
})();
</script>
</body>
</html>
