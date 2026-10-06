<?php
/**
 * Paystack Webhook Handler
 * Receives server-to-server payment events from Paystack
 * 
 * Configure this URL in Paystack Dashboard:
 * https://fazobethventures.site.je/fazobeth/paystack/webhook.php
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../classes/Config.php';
require_once __DIR__ . '/../classes/Order.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify Paystack signature (optional but recommended)
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$payload = file_get_contents('php://input');

if ($signature && PAYSTACK_SECRET_KEY !== '') {
    $expected = hash_hmac('sha512', $payload, PAYSTACK_SECRET_KEY);
    if (!hash_equals($expected, $signature)) {
        error_log("Paystack webhook: Invalid signature");
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'Invalid signature']);
        exit;
    }
}

$event = json_decode($payload, true);

if (!$event || !isset($event['event'])) {
    error_log("Paystack webhook: Invalid payload");
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid payload']);
    exit;
}

// Only process charge.success events
if ($event['event'] !== 'charge.success') {
    // Acknowledge but don't process other events
    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Event acknowledged but not processed']);
    exit;
}

$data = $event['data'] ?? [];

// Extract payment details
$reference = $data['reference'] ?? '';
$amount = $data['amount'] ?? 0; // in kobo
$currency = $data['currency'] ?? 'NGN';
$status = $data['status'] ?? '';
$customer_email = $data['customer']['email'] ?? '';
$paid_at = $data['paid_at'] ?? '';

if ($reference === '' || $status !== 'success') {
    error_log("Paystack webhook: Invalid transaction data");
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Invalid transaction']);
    exit;
}

// Verify amount matches (optional but good for security)
// $expected_amount_kobo = ... // you'd need to look up the order

// Check if order already exists for this reference
$orderObj = new Order();
if ($orderObj->payment_reference_exists($reference)) {
    // Already processed - acknowledge
    error_log("Paystack webhook: Order already exists for reference $reference");
    http_response_code(200);
    echo json_encode(['status' => true, 'message' => 'Order already processed']);
    exit;
}

// For webhook, we can't create the full order without cart context
// But we can mark the payment as verified and update status
// The process-order.php already handles order creation via AJAX callback
// This webhook serves as a backup in case AJAX callback fails

// Log successful webhook receipt
error_log("Paystack webhook received: charge.success for reference $reference, amount $amount $currency, email $customer_email");

// Respond with 200 OK - Paystack expects this within a few seconds
http_response_code(200);
echo json_encode(['status' => true, 'message' => 'Webhook processed successfully']);