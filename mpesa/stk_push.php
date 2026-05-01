<?php
session_start();
if (!isset($_SESSION['userid'])) {
    echo "❌ Session lost. Check folder structure or domain mismatch.";
    exit;
}

require_once("../classes/autoload.php");


$DB = new Database();

$farmer_id = $_SESSION['userid'] ?? null;
$phone = $_POST['phone'] ?? null;
$request_id = $_POST['request_id'] ?? null;
$amount = $_POST['amount'] ?? null;
$agronomist_id = $_POST['agronomist_id'] ?? null;

if (!$farmer_id || !$phone || !$request_id || !$amount || !$agronomist_id) {
    die("Missing or unauthorized data.");
}

// Format phone
if (substr($phone, 0, 1) === "0") {
    $phone = "254" . substr($phone, 1);
}

// Credentials
$shortcode = "174379";
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$consumerKey = "ISQXTAGNXTrMPVJ3yqkY1Mzet1Pk1cc9Cfg38G6IKxPMKUSO";
$consumerSecret = "wMQIC0EFpvZevoEltJpHntP5eRldjRjmv5pacPho1Nu79NStA1O43iAt1tGb5dzH";
$timestamp = date("YmdHis");
$password = base64_encode($shortcode . $passkey . $timestamp);
$callbackURL = "https://6cc556b1b2a8.ngrok-free.app/mychatui/mpesa/callback.php";


// Get access token
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode("$consumerKey:$consumerSecret")]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);
$access = json_decode($response);

if (!isset($access->access_token)) {
    die("❌ Failed to get access token.");
}

$token = $access->access_token;

// STK Push
$stk_data = [
    "BusinessShortCode" => $shortcode,
    "Password" => $password,
    "Timestamp" => $timestamp,
    "TransactionType" => "CustomerPayBillOnline",
    "Amount" => (int)$amount,
    "PartyA" => $phone,
    "PartyB" => $shortcode,
    "PhoneNumber" => $phone,
    "CallBackURL" => $callbackURL,
    "AccountReference" => "FarmnetPay",
    "TransactionDesc" => "Pay for Service #$request_id"
];

$curl2 = curl_init();
curl_setopt($curl2, CURLOPT_URL, "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
curl_setopt($curl2, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);
curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl2, CURLOPT_POST, true);
curl_setopt($curl2, CURLOPT_POSTFIELDS, json_encode($stk_data));
$result = curl_exec($curl2);
curl_close($curl2);

$resultData = json_decode($result);

if (isset($resultData->ResponseCode) && $resultData->ResponseCode == "0") {
    $log = [
        'request_id' => $request_id,
        'farmer_id' => $farmer_id,
        'agronomist_id' => $agronomist_id,
        'amount' => $amount,
        'checkout_request_id' => $resultData->CheckoutRequestID,
        'status' => 'initiated',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    $DB->write("INSERT INTO payment_requests 
        (request_id, farmer_id, agronomist_id, amount, checkout_request_id, status, timestamp)
        VALUES (:request_id, :farmer_id, :agronomist_id, :amount, :checkout_request_id, :status, :timestamp)", $log);

    echo "STK push sent.";
    exit;
} else {
    echo "❌ Payment failed.";
    echo "<pre>";
    print_r($resultData);
    echo "</pre>";
}
