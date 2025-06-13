<?php
require 'vendor/autoload.php'; // Install Razorpay PHP SDK via composer

use Razorpay\Api\Api;

$api = new Api('rzp_live_5Frzcq3BYdZAWL', 'YOUR_RAZORPAY_SECRET_KEY');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $order = $api->order->create(array(
        'receipt'         => 'rcpt_'.time(),
        'amount'          => $data['amount'],
        'currency'        => $data['currency'],
        'payment_capture' => 1
    ));

    echo json_encode([
        'id' => $order->id,
        'amount' => $order->amount,
        'currency' => $order->currency
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}