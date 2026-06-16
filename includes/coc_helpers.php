<?php

function coc_asset(string $path): string
{
    return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/' . ltrim($path, '/');
}

function coc_decode_json(?string $json): array
{
    if (!$json) {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function coc_decode_photos(?string $photos): array
{
    $decoded = coc_decode_json($photos);
    if ($decoded) {
        return array_values(array_filter(array_map('trim', $decoded)));
    }

    if (!$photos) {
        return [];
    }

    return array_values(array_filter(array_map('trim', preg_split('/\R+/', $photos))));
}

function coc_photos_to_json(string $photos): string
{
    $items = array_values(array_filter(array_map('trim', preg_split('/\R+/', $photos))));
    return json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function coc_townhall_level(array $data): int
{
    $levels = [];

    foreach (($data['buildings'] ?? []) as $building) {
        if (($building['data'] ?? null) === 1000000 && isset($building['lvl'])) {
            $levels[] = (int) $building['lvl'];
        }
    }

    return $levels ? max($levels) : 0;
}

function coc_account_hall(array $account): int
{
    $hall = (int) ($account['hall'] ?? 0);
    return $hall > 0 ? $hall : coc_townhall_level(coc_decode_json($account['data'] ?? null));
}

function coc_summary_counts(array $data): array
{
    return [
        'Heroes' => count($data['heroes'] ?? []),
        'Troops' => count($data['units'] ?? []),
        'Spells' => count($data['spells'] ?? []),
        'Equipment' => count($data['equipment'] ?? []),
        'Builder troops' => count($data['units2'] ?? []),
    ];
}

function coc_money($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

function coc_fetch_account(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM coc WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}
