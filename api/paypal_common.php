<?php

function paypal_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function paypal_config(): array
{
    $fileConfig = require __DIR__ . '/../config/paypal.php';
    $mode = $fileConfig['mode'] ?? 'sandbox';

    return [
        'client_id' => $fileConfig['client_id'] ?? '',
        'secret' => $fileConfig['client_secret'] ?? '',
        'currency' => $fileConfig['currency'] ?? 'USD',
        'base_url' => $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com',
    ];
}

function paypal_access_token(array $config): string
{
    if (!$config['client_id'] || !$config['secret']) {
        paypal_json_response(['error' => 'Thiếu PAYPAL_CLIENT_ID hoặc PAYPAL_CLIENT_SECRET'], 500);
    }

    $ch = curl_init($config['base_url'] . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_USERPWD => $config['client_id'] . ':' . $config['secret'],
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
    ]);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $body = json_decode((string) $response, true);
    if ($status >= 400 || empty($body['access_token'])) {
        paypal_json_response(['error' => 'Không lấy được PayPal access token', 'detail' => $error ?: $body], 502);
    }

    return $body['access_token'];
}

function paypal_request(string $method, string $path, array $body = null): array
{
    $config = paypal_config();
    $token = paypal_access_token($config);
    $ch = curl_init($config['base_url'] . $path);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $decoded = json_decode((string) $response, true) ?: [];
    if ($status >= 400) {
        paypal_json_response(['error' => 'PayPal API lỗi', 'detail' => $error ?: $decoded], 502);
    }

    return $decoded;
}

function paypal_input(): array
{
    $raw = file_get_contents('php://input');
    $input = json_decode($raw ?: '{}', true);
    return is_array($input) ? $input : [];
}
