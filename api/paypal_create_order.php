<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/coc_helpers.php';
require __DIR__ . '/paypal_common.php';

if (!$pdo instanceof PDO) {
    paypal_json_response(['error' => 'Database chưa sẵn sàng'], 500);
}

$input = paypal_input();
$accountId = (int) ($input['account_id'] ?? 0);
$account = coc_fetch_account($pdo, $accountId);

if (!$account) {
    paypal_json_response(['error' => 'Không tìm thấy acc'], 404);
}

$config = paypal_config();
$amount = number_format((float) $account['price'], 2, '.', '');
$order = paypal_request('POST', '/v2/checkout/orders', [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => 'coc-' . $account['id'],
        'description' => $account['name'],
        'amount' => [
            'currency_code' => $config['currency'],
            'value' => $amount,
        ],
    ]],
]);

if (empty($order['id'])) {
    paypal_json_response(['error' => 'PayPal không trả về order id'], 502);
}

$stmt = $pdo->prepare('INSERT INTO coc_orders (coc_id, paypal_order_id, status, amount, paypal_payload) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([
    $account['id'],
    $order['id'],
    $order['status'] ?? 'CREATED',
    $amount,
    json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);

paypal_json_response(['id' => $order['id']]);

