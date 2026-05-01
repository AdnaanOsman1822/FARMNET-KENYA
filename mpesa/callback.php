<?php
// /mpesa/callback.php
require_once("../classes/autoload.php");

$DB = new Database();

// 👇 Allow GET requests for health check or pings (no 400 error)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    file_put_contents("callback_log.txt", "✅ GET received at " . date("Y-m-d H:i:s") . "\n", FILE_APPEND);
    http_response_code(200);
    echo "✅ Callback URL working (GET)";
    exit;
}

// ✅ Continue only if POST
$raw_data = file_get_contents('php://input');
file_put_contents("callback_log.txt", $raw_data . "\n", FILE_APPEND); // Save raw callback for review

$data = json_decode($raw_data);

if (!$data || !isset($data->Body->stkCallback)) {
    file_put_contents("callback_debug.txt", "❌ Invalid JSON or missing stkCallback\n", FILE_APPEND);
    http_response_code(400);
    echo "Invalid data.";
    exit;
}

$callback = $data->Body->stkCallback;
$checkoutRequestID = $callback->CheckoutRequestID ?? null;
$resultCode = $callback->ResultCode ?? null;

if ($resultCode == 0 && isset($callback->CallbackMetadata)) {
    // ✅ Successful payment
    $items = $callback->CallbackMetadata->Item;
    $receipt = "";
    $amount = 0;
    $phone = "";
    $timestamp = date("Y-m-d H:i:s");

    foreach ($items as $item) {
        if ($item->Name == "MpesaReceiptNumber") {
            $receipt = $item->Value;
        } elseif ($item->Name == "Amount") {
            $amount = $item->Value;
        } elseif ($item->Name == "PhoneNumber") {
            $phone = $item->Value;
        }
    }

    // ✅ Update payment status (fixed confirmed_at → timestamp)
    $update = "
        UPDATE payment_requests
        SET status = 'confirmed',
            mpesa_receipt = :receipt,
            timestamp = :timestamp
        WHERE checkout_request_id = :checkout_id
        LIMIT 1
    ";

    file_put_contents("callback_debug.txt", "Attempting DB update for success:\n", FILE_APPEND);
    file_put_contents("callback_debug.txt", "Checkout ID: $checkoutRequestID\n", FILE_APPEND);
    file_put_contents("callback_debug.txt", "Receipt: $receipt\n", FILE_APPEND);
    file_put_contents("callback_debug.txt", "Confirmed At: $timestamp\n", FILE_APPEND);

    $success = $DB->write($update, [
        'receipt' => $receipt,
        'timestamp' => $timestamp,
        'checkout_id' => $checkoutRequestID
    ]);

    if ($success) {
        file_put_contents("callback_debug.txt", "✅ DB update SUCCESS\n", FILE_APPEND);
    } else {
        file_put_contents("callback_debug.txt", "❌ DB update FAILED\n", FILE_APPEND);
    }

    http_response_code(200);
    echo "✔️ Payment confirmed.";
} else {
    // ❌ Failed or cancelled
    $update = "
        UPDATE payment_requests
        SET status = 'failed'
        WHERE checkout_request_id = :checkout_id
        LIMIT 1
    ";

    file_put_contents("callback_debug.txt", "❌ Payment failed or cancelled. Checkout ID: $checkoutRequestID\n", FILE_APPEND);

    $fail = $DB->write($update, ['checkout_id' => $checkoutRequestID]);

    if ($fail) {
        file_put_contents("callback_debug.txt", "✅ Marked as failed in DB\n", FILE_APPEND);
    } else {
        file_put_contents("callback_debug.txt", "❌ Failed to update DB for failed status\n", FILE_APPEND);
    }

    http_response_code(200);
    echo "❌ Payment failed or cancelled.";
}
