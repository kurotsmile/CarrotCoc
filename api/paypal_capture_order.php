<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/coc_helpers.php';
require __DIR__ . '/paypal_common.php';

if (!$pdo instanceof PDO) {
    paypal_json_response(['error' => 'Database chưa sẵn sàng'], 500);
}

$input = paypal_input();
$orderId = trim((string) ($input['order_id'] ?? ''));
if ($orderId === '') {
    paypal_json_response(['error' => 'Thiếu PayPal order id'], 422);
}

$stmt = $pdo->prepare('SELECT o.*, c.username, c.password FROM coc_orders o INNER JOIN coc c ON c.id = o.coc_id WHERE o.paypal_order_id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    paypal_json_response(['error' => 'Order không tồn tại trong hệ thống'], 404);
}

$capture = paypal_request('POST', '/v2/checkout/orders/' . rawurlencode($orderId) . '/capture');
$status = $capture['status'] ?? 'UNKNOWN';
$payerEmail = $capture['payer']['email_address'] ?? null;

$update = $pdo->prepare('UPDATE coc_orders SET status = ?, payer_email = ?, paypal_payload = ?, paid_at = IF(? = "COMPLETED", NOW(), paid_at) WHERE paypal_order_id = ?');
$update->execute([
    $status,
    $payerEmail,
    json_encode($capture, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    $status,
    $orderId,
]);

if ($status !== 'COMPLETED') {
    paypal_json_response(['success' => false, 'error' => 'PayPal chưa hoàn tất thanh toán', 'status' => $status], 402);
}

paypal_json_response([
    'success' => true,
    'username' => $order['username'],
    'password' => $order['password'],
]);

