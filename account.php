<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/coc_helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$account = $pdo instanceof PDO ? coc_fetch_account($pdo, $id) : null;

if (!$account) {
    http_response_code(404);
}

$data = $account ? coc_decode_json($account['data']) : [];
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
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $account ? htmlspecialchars($account['name']) : 'Không tìm thấy acc' ?> - COC Shop</title>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(coc_asset('favicon/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars(coc_asset('favicon/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars(coc_asset('favicon/favicon-16x16.png')) ?>">
    <link rel="icon" href="<?= htmlspecialchars(coc_asset('favicon/favicon.ico')) ?>">
    <link rel="manifest" href="<?= htmlspecialchars(coc_asset('favicon/site.webmanifest')) ?>">
    <meta name="theme-color" content="#071625">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(coc_asset('assets/css/style.css?v8')) ?>" rel="stylesheet">
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
                                $itemName = (string) ($item['name'] ?? $item['data'] ?? $item['id'] ?? 'Item');
                                $itemLevel = $item['lvl'] ?? $item['level'] ?? null;
                                ?>
                                <div class="supercell-item">
                                    <div class="supercell-item-main">
                                        <span><?= htmlspecialchars($itemName) ?></span>
                                        <?php if ($itemLevel !== null): ?>
                                            <strong>Lv <?= htmlspecialchars((string) $itemLevel) ?></strong>
                                        <?php endif; ?>
                                    </div>
                                    <div class="supercell-chips">
                                        <?php foreach ($item as $attribute => $value): ?>
                                            <?php if (in_array($attribute, ['name', 'data', 'id', 'lvl', 'level'], true) || is_array($value)) continue; ?>
                                            <span><?= htmlspecialchars((string) $attribute) ?>: <?= htmlspecialchars((string) $value) ?></span>
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
</body>
</html>
