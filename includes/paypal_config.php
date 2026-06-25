<?php

function coc_paypal_config_from_db(?PDO $pdo, string $site = 'coc'): array
{
    $defaults = [
        'enabled' => false,
        'client_id' => '',
        'client_secret' => '',
        'secret' => '',
        'currency' => 'USD',
        'mode' => 'sandbox',
        'base_url' => 'https://api-m.sandbox.paypal.com',
    ];

    if (!$pdo instanceof PDO) {
        return $defaults;
    }

    try {
        $stmt = $pdo->prepare('SELECT * FROM paypal_config WHERE site = ? LIMIT 1');
        $stmt->execute([$site]);
        $row = $stmt->fetch();
        if (!$row) {
            return $defaults;
        }

        $mode = ($row['active_mode'] ?? 'sandbox') === 'live' ? 'live' : 'sandbox';
        $prefix = $mode === 'live' ? 'live' : 'sandbox';
        $clientId = (string) ($row[$prefix . '_client_id'] ?? '');
        $clientSecret = (string) ($row[$prefix . '_client_secret'] ?? '');

        return [
            'enabled' => !empty($row['enabled']),
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'secret' => $clientSecret,
            'currency' => (string) ($row['currency'] ?? 'USD'),
            'mode' => $mode,
            'base_url' => $mode === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com',
        ];
    } catch (Throwable $e) {
        return $defaults;
    }
}
