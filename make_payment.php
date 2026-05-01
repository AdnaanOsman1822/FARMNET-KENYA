<?php
session_start();
require_once("classes/autoload.php");

$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    die("Unauthorized access.");
}

$request_id     = $_POST['request_id'] ?? null;
$amount         = $_POST['amount'] ?? null;
$agronomist_id  = $_POST['agronomist_id'] ?? null;

if (!$request_id || !$amount || !$agronomist_id) {
    die("Missing required fields.");
}

// Store payment info temporarily in session
$_SESSION['payment_data'] = [
    'request_id'     => $request_id,
    'amount'         => $amount,
    'agronomist_id'  => $agronomist_id
];

// Redirect to preview page
header("Location: payment_preview.php");
exit;
