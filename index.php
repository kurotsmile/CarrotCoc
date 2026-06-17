<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/coc_helpers.php';

$accounts = [];
$townhall = isset($_GET['townhall']) ? (int) $_GET['townhall'] : 0;
$search = trim((string) ($_GET['q'] ?? ''));
$currentPage = max(1, (int) ($_GET['page_no'] ?? 1));
$accountsPerPage = 12;
$totalAccounts = 0;
$totalPages = 1;
$page = basename((string) ($_GET['page'] ?? ''));
$staticPages = [
    'Introduce.php' => ['title' => 'Introduce', 'file' => __DIR__ . '/page/Introduce.php'],
    'Policy.php' => ['title' => 'Policy', 'file' => __DIR__ . '/page/Policy.php'],
    'Cookie.php' => ['title' => 'Cookie', 'file' => __DIR__ . '/page/Cookie.php'],
    'Support.php' => ['title' => 'Support', 'file' => __DIR__ . '/page/Support.php'],
];
$staticPage = $staticPages[$page] ?? null;
$dbReady = $pdo instanceof PDO;
$siteUrl = 'https://coc.carrot28.com';
$seoTitle = 'COC Shop - Mua Acc Clash of Clans Giá Rẻ, Uy Tín';
$seoDescription = 'Mua tài khoản Clash of Clans, acc COC giá rẻ, nhiều Town Hall, giao dịch uy tín, thanh toán PayPal và nhận login nhanh sau khi thanh toán thành công.';
$seoKeywords = 'acc coc giá rẻ, mua tài khoản clash of clan, mua acc clash of clans, shop acc coc, tài khoản clash of clans, acc clash of clan uy tín, mua acc coc paypal';
$canonicalUrl = $siteUrl . '/';

if (!$staticPage && $dbReady) {
    try {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = 'name LIKE ?';
            $params[] = '%' . $search . '%';
        }

        if ($townhall > 0) {
            $where[] = 'hall = ?';
            $params[] = $townhall;
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM coc' . $whereSql);
        $countStmt->execute($params);
        $totalAccounts = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalAccounts / $accountsPerPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $accountsPerPage;

        $stmt = $pdo->prepare('SELECT id, name, hall, data, avatar, price FROM coc' . $whereSql . ' ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmtParams = array_merge($params, [$accountsPerPage, $offset]);
        foreach ($stmtParams as $index => $value) {
            $stmt->bindValue($index + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $accounts = $stmt->fetchAll();
    } catch (Throwable $e) {
        $dbReady = false;
        $db_error = $e->getMessage();
    }
}

if ($staticPage) {
    $seoTitle = $staticPage['title'] . ' - COC Shop';
    $seoDescription = 'Thông tin ' . strtolower($staticPage['title']) . ' của COC Shop, nơi mua tài khoản Clash of Clans uy tín, nhanh chóng và an toàn.';
    $canonicalUrl = $siteUrl . '/index.php?page=' . rawurlencode($page);
} elseif ($townhall > 0) {
    $seoTitle = 'Mua Acc COC Town Hall ' . $townhall . ' Giá Rẻ - COC Shop';
    $seoDescription = 'Danh sách tài khoản Clash of Clans Town Hall ' . $townhall . ' giá tốt, thông tin rõ ràng, thanh toán PayPal và nhận login nhanh.';
    $canonicalUrl = $siteUrl . '/index.php?townhall=' . $townhall;
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($seoTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($seoKeywords) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="COC Shop">
    <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($siteUrl . '/assets/coc_logo.png') ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($siteUrl . '/assets/coc_logo.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= htmlspecialchars(coc_asset('favicon/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars(coc_asset('favicon/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= htmlspecialchars(coc_asset('favicon/favicon-16x16.png')) ?>">
    <link rel="icon" href="<?= htmlspecialchars(coc_asset('favicon/favicon.ico')) ?>">
    <link rel="manifest" href="<?= htmlspecialchars(coc_asset('favicon/site.webmanifest')) ?>">
    <meta name="theme-color" content="#071625">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(coc_asset('assets/css/style.css?v12')) ?>" rel="stylesheet">
    <script type="application/ld+json">
    <?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Store',
        'name' => 'COC Shop',
        'url' => $siteUrl . '/',
        'description' => $seoDescription,
        'image' => $siteUrl . '/assets/coc_logo.png',
        'paymentAccepted' => 'PayPal',
        'sameAs' => [
            'https://tiktok.com/@kurotsmilethanh',
            'https://linkedin.com/in/tranthienthanh',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
    </script>
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
            <input class="form-control" type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm acc Clash of Clans" aria-label="Tìm acc Clash of Clans">
            <?php if ($townhall > 0): ?>
                <input type="hidden" name="townhall" value="<?= (int) $townhall ?>">
            <?php endif; ?>
        </form>
    </div>
</nav>

<main class="container py-5">
    <?php if ($staticPage): ?>
        <?php require $staticPage['file']; ?>
    <?php else: ?>
    <section class="glass-panel hero-panel p-4 p-lg-5 mb-4">
        <img class="hero-logo" src="<?= htmlspecialchars(coc_asset('assets/coc_logo.png')) ?>" alt="Clash of Clans">
        <img class="hero-banner" src="<?= htmlspecialchars(coc_asset('assets/banner_top.png')) ?>" alt="COC Shop banner">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7">
                <p class="text-uppercase fw-bold muted-text mb-2">Shop acc Clash of Clans</p>
                <h1 class="hero-title mb-3">Chúng tôi cung cấp nhiều tài Game khoản chất lượng , giao dịch uy tín , Hy vọng bạn sẽ hài lòng với việc mua hàng của mình.</h1>
                <p class="muted-text mb-0"><code>Cảm ơn</code> bạn đã là khách hàng thân thiết của <code>shop</code> chúng tôi !.</p>
            </div>
            <div class="col-lg-5">
                <form class="row g-2 justify-content-lg-end" method="get">
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <div class="col-sm-8">
                        <label class="form-label" for="townhall">Lọc cấp nhà chính</label>
                        <select class="form-select" id="townhall" name="townhall">
                            <option value="0">Tất cả Town Hall</option>
                            <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?= $i ?>" <?= $townhall === $i ? 'selected' : '' ?>>Town Hall <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-sm-4 d-flex align-items-end">
                        <button class="btn btn-coc w-100" type="submit">Lọc</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php if (!$dbReady): ?>
        <div class="alert alert-warning">Không thể kết nối hoặc đọc database: <?= htmlspecialchars($db_error ?? 'unknown error') ?></div>
    <?php elseif (!$accounts): ?>
        <div class="glass-panel p-5 text-center">
            <h2 class="h4">Chưa có acc phù hợp</h2>
            <p class="muted-text mb-0">Hiện chưa có acc Clash of Clans phù hợp với bộ lọc.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($accounts as $account):
                $th = coc_account_hall($account);
                ?>
                <div class="col-md-6 col-xl-4">
                    <a class="account-card d-block h-100 text-decoration-none text-white" href="account.php?id=<?= (int) $account['id'] ?>">
                        <img class="account-avatar" src="<?= htmlspecialchars($account['avatar'] ?: 'https://images.unsplash.com/photo-1614294148960-9aa740632a87?auto=format&fit=crop&w=900&q=80') ?>" alt="<?= htmlspecialchars($account['name']) ?>">
                        <div class="p-4">
                            <div class="d-flex justify-content-between gap-3 mb-3">
                                <h2 class="h5 fw-bold mb-0"><?= htmlspecialchars($account['name']) ?></h2>
                                <span class="price-pill"><?= coc_money($account['price']) ?></span>
                            </div>
                            <p class="muted-text mb-0">Town Hall <?= $th ?: 'N/A' ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="mt-5" aria-label="Phân trang tài khoản">
                <ul class="pagination coc-pagination justify-content-center flex-wrap">
                    <?php
                    $prevPageParams = ['page_no' => max(1, $currentPage - 1)];
                    $nextPageParams = ['page_no' => min($totalPages, $currentPage + 1)];
                    if ($search !== '') {
                        $prevPageParams['q'] = $search;
                        $nextPageParams['q'] = $search;
                    }
                    if ($townhall > 0) {
                        $prevPageParams['townhall'] = $townhall;
                        $nextPageParams['townhall'] = $townhall;
                    }
                    ?>
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars('index.php?' . http_build_query($prevPageParams)) ?>" aria-label="Trang trước">
                            <i class="bi bi-chevron-left" aria-hidden="true"></i>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $pageParams = ['page_no' => $i];
                        if ($search !== '') {
                            $pageParams['q'] = $search;
                        }
                        if ($townhall > 0) {
                            $pageParams['townhall'] = $townhall;
                        }
                        ?>
                        <li class="page-item <?= $currentPage === $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars('index.php?' . http_build_query($pageParams)) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars('index.php?' . http_build_query($nextPageParams)) ?>" aria-label="Trang sau">
                            <i class="bi bi-chevron-right" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
