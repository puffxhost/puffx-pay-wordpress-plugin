<?php
// Auto-detect WordPress root & load environment
$root = dirname(__FILE__, 4);
$wp_load = $root . '/wp-load.php';

if (!file_exists($wp_load)) {
    http_response_code(500);
    echo 'Error: Cannot find wp-load.php. Please check file path.';
    exit;
}
require_once($wp_load);

// Make sure WooCommerce is active
if (!function_exists('wc_get_order')) {
    http_response_code(500);
    echo 'Error: WooCommerce is not active.';
    exit;
}

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo 'Only POST requests are allowed';
    exit;
}

// Sanitize POST data
$status           = sanitize_text_field($_POST['status'] ?? '');
$order_id         = intval($_POST['order_id'] ?? 0);
$customer_mobile  = sanitize_text_field($_POST['customer_mobile'] ?? '');
$amount           = sanitize_text_field($_POST['amount'] ?? '');
$remark1          = sanitize_text_field($_POST['remark1'] ?? '');
$remark2          = sanitize_text_field($_POST['remark2'] ?? '');

// Debug log for webhook
$log_file = __DIR__ . '/webhook-log.txt';
$log_entry = sprintf(
    "[%s] Webhook Received - OrderID: %s | Status: %s | Amount: %s | Mobile: %s | Remark1: %s | Remark2: %s\n",
    date('Y-m-d H:i:s'),
    $order_id,
    $status,
    $amount,
    $customer_mobile,
    $remark1,
    $remark2
);
file_put_contents($log_file, $log_entry, FILE_APPEND);

// Validate data
if (!$order_id || !$status) {
    wp_send_json(['message' => 'Invalid webhook data received.']);
}

$order = wc_get_order($order_id);

if (!$order) {
    wp_send_json(['message' => 'Order not found.']);
}

// Process based on status
if (strtolower($status) === 'success') {
    if (!in_array($order->get_status(), ['processing', 'completed'])) {
        $order->payment_complete();
        $order->update_status('processing', 'Payment confirmed via Puffx Pay Webhook.');
    }
    $order->add_order_note('Payment successful. Verified via Puffx Pay Webhook.');
    wp_send_json(['message' => 'Order marked as processing (payment success).']);

} elseif (strtolower($status) === 'failed') {
    $order->update_status('failed', 'Payment failed via Puffx Pay Webhook.');
    $order->add_order_note('Payment failed. Verified via Puffx Pay Webhook.');
    wp_send_json(['message' => 'Order marked as failed (payment failed).']);

} else {
    wp_send_json(['message' => 'Unknown payment status received.']);
}
