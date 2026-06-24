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

function coc_object_name_map(): array
{
    static $names = null;

    if ($names !== null) {
        return $names;
    }

    $names = [];
    $path = dirname(__DIR__) . '/coc_name_en.json';
    if (!is_file($path)) {
        return $names;
    }

    $collect = function ($value) use (&$collect, &$names): void {
        if (!is_array($value)) {
            return;
        }

        if (isset($value['data'], $value['name']) && !is_array($value['data']) && !is_array($value['name'])) {
            $names[(string) $value['data']] = (string) $value['name'];
        }

        foreach ($value as $child) {
            $collect($child);
        }
    };

    $collect(coc_decode_json(file_get_contents($path) ?: null));

    return $names;
}

function coc_object_display_name(array $item, array $nameMap): string
{
    $objectId = (string) ($item['data'] ?? $item['id'] ?? '');

    if ($objectId !== '' && isset($nameMap[$objectId])) {
        return $nameMap[$objectId];
    }

    return (string) ($item['name'] ?? $item['data'] ?? $item['id'] ?? 'Item');
}

function coc_decode_photos(?string $photos): array
{
    $decoded = coc_decode_json($photos);
    if ($decoded) {
        return array_values(array_filter(array_map('coc_normalize_photo_url', $decoded)));
    }

    if (!$photos) {
        return [];
    }

    return array_values(array_filter(array_map('coc_normalize_photo_url', preg_split('/[\r\n,]+/', $photos))));
}

function coc_photos_to_json(string $photos): string
{
    $items = array_values(array_filter(array_map('coc_normalize_photo_url', preg_split('/[\r\n,]+/', $photos))));
    return json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function coc_normalize_photo_url($photo): string
{
    return trim((string) $photo, " \t\n\r\0\x0B,");
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
