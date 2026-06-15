<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/coc_helpers.php';

$accounts = [];
$townhall = isset($_GET['townhall']) ? (int) $_GET['townhall'] : 0;
$dbReady = $pdo instanceof PDO;

if ($dbReady) {
    try {
        $accounts = $pdo->query('SELECT id, name, data, avatar, price FROM coc ORDER BY id DESC')->fetchAll();
        if ($townhall > 0) {
            $accounts = array_values(array_filter($accounts, fn($account) => coc_townhall_level(coc_decode_json($account['data'])) === $townhall));
        }
    } catch (Throwable $e) {
        $dbReady = false;
        $db_error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrot Coc - Shop Acc Clash of Clans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= htmlspecialchars(coc_asset('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark glass-nav">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="index.php">
            <span class="brand-mark">CC</span>
            <span>Carrot Coc</span>
        </a>
        <a class="btn btn-sm btn-outline-light" href="/CarrotAdmin/index.php">Admin</a>
    </div>
</nav>

<main class="container py-5">
    <section class="glass-panel p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7">
                <p class="text-uppercase fw-bold muted-text mb-2">Shop acc Clash of Clans</p>
                <h1 class="display-5 fw-black mb-3">Chọn acc phù hợp, thanh toán PayPal, nhận login sau khi capture thành công.</h1>
                <p class="muted-text mb-0">Danh sách đọc từ bảng <code>coc</code>, cấp nhà chính được suy ra từ JSON Supercell trong trường <code>data</code>.</p>
            </div>
            <div class="col-lg-5">
                <form class="row g-2 justify-content-lg-end" method="get">
                    <div class="col-sm-8">
                        <label class="form-label" for="townhall">Lọc cấp nhà chính</label>
                        <select class="form-select" id="townhall" name="townhall">
                            <option value="0">Tất cả Town Hall</option>
                            <?php for ($i = 1; $i <= 17; $i++): ?>
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
            <p class="muted-text mb-0">Vào trang admin để thêm acc Clash of Clans đầu tiên.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($accounts as $account):
                $data = coc_decode_json($account['data']);
                $th = coc_townhall_level($data);
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
    <?php endif; ?>
</main>
</body>
</html>
