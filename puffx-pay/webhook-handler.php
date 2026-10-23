<?php
// Auto-detect WordPress root & load environment.
$root = dirname(__FILE__, 4);
$wp_load = $root . '/wp-load.php';

if (!file_exists($wp_load)) {
    http_response_code(500);
    echo 'WordPress could not be loaded.';
    exit;
}
require_once $wp_load;

if (!function_exists('wc_get_order')) {
    http_response_code(503);
    echo 'WooCommerce is not available.';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo 'Only POST requests are allowed.';
    exit;
}

$order_id = isset($_POST['order_id']) ? absint(wp_unslash($_POST['order_id'])) : 0;
$callback_status = isset($_POST['status'])
    ? strtoupper(sanitize_text_field(wp_unslash($_POST['status'])))
    : '';

if (!$order_id || !in_array($callback_status, ['SUCCESS', 'COMPLETED'], true)) {
    wp_send_json(['message' => 'Invalid payment notification.'], 400);
}

$order = wc_get_order($order_id);
if (!$order) {
    wp_send_json(['message' => 'Order not found.'], 404);
}

$settings = get_option('woocommerce_puffx_pay_settings', []);
$api_key = is_array($settings) && isset($settings['api_key'])
    ? trim((string) $settings['api_key'])
    : '';

if ($api_key === '') {
    wp_send_json(['message' => 'Payment verification is not configured.'], 503);
}

// Never trust the public callback's status or amount. Ask the gateway directly
// for the status of this merchant's order before changing WooCommerce state.
$verification = wp_remote_post('https://pay.x-server.in/api/check-order-status', [
    'timeout' => 15,
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/x-www-form-urlencoded',
    ],
    'body' => [
        'user_token' => $api_key,
        'order_id' => (string) $order_id,
    ],
]);

if (is_wp_error($verification)) {
    wp_send_json(['message' => 'Payment status could not be verified.'], 503);
}

$http_code = (int) wp_remote_retrieve_response_code($verification);
$verified = json_decode(wp_remote_retrieve_body($verification), true);
if ($http_code < 200 || $http_code >= 300 || !is_array($verified) || !isset($verified['result']) || !is_array($verified['result'])) {
    wp_send_json(['message' => 'Payment status could not be verified.'], 503);
}

$result = $verified['result'];
$verified_order_id = isset($result['orderId']) ? (string) $result['orderId'] : '';
$verified_status = strtoupper((string) ($result['status'] ?? $result['txnStatus'] ?? ''));

if ($verified_order_id !== (string) $order_id) {
    wp_send_json(['message' => 'The gateway returned a different order.'], 409);
}

if (!in_array($verified_status, ['SUCCESS', 'COMPLETED'], true)) {
    // Pending/failed notifications are acknowledged, but cannot change the
    // order state based on an unauthenticated callback.
    wp_send_json(['message' => 'Payment is not confirmed by the gateway.'], 202);
}

if (!isset($result['amount']) || !is_numeric($result['amount'])) {
    wp_send_json(['message' => 'The gateway returned an invalid amount.'], 503);
}

$verified_amount = wc_format_decimal($result['amount'], wc_get_price_decimals());
$order_amount = wc_format_decimal($order->get_total(), wc_get_price_decimals());
if ($verified_amount === '' || $verified_amount !== $order_amount) {
    wp_send_json(['message' => 'The verified payment amount does not match the order.'], 409);
}

if (!$order->is_paid()) {
    $transaction_id = isset($result['utr']) ? sanitize_text_field((string) $result['utr']) : '';
    $order->payment_complete($transaction_id);
    $order->add_order_note('Payment confirmed by server-side Puffx Pay status verification.');
}

wp_send_json(['message' => 'Payment verified.']);
