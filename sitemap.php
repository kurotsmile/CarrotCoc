<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/coc_helpers.php';

$siteUrl = 'https://coc.carrot28.com';
$today = date('Y-m-d');
$urls = [
    ['loc' => $siteUrl . '/', 'lastmod' => $today, 'changefreq' => 'daily', 'priority' => '1.0'],
    ['loc' => $siteUrl . '/index.php?page=Introduce.php', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
    ['loc' => $siteUrl . '/index.php?page=Policy.php', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.6'],
    ['loc' => $siteUrl . '/index.php?page=Cookie.php', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.5'],
    ['loc' => $siteUrl . '/index.php?page=Support.php', 'lastmod' => $today, 'changefreq' => 'monthly', 'priority' => '0.7'],
];

for ($hall = 1; $hall <= 20; $hall++) {
    $urls[] = [
        'loc' => $siteUrl . '/index.php?townhall=' . $hall,
        'lastmod' => $today,
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ];
}

if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->query('SELECT id, updated_at FROM coc ORDER BY id DESC');
        foreach ($stmt->fetchAll() as $account) {
            $urls[] = [
                'loc' => $siteUrl . '/account.php?id=' . (int) $account['id'],
                'lastmod' => $account['updated_at'] ? date('Y-m-d', strtotime($account['updated_at'])) : $today,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ];
        }
    } catch (Throwable $e) {
        $urls[] = [
            'loc' => $siteUrl . '/',
            'lastmod' => $today,
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
    <url>
        <loc><?= htmlspecialchars($url['loc'], ENT_XML1) ?></loc>
        <lastmod><?= htmlspecialchars($url['lastmod'], ENT_XML1) ?></lastmod>
        <changefreq><?= htmlspecialchars($url['changefreq'], ENT_XML1) ?></changefreq>
        <priority><?= htmlspecialchars($url['priority'], ENT_XML1) ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
