<?php
// /mpesa/check_payment.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../classes/autoload.php");

header('Content-Type: application/json');

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    echo json_encode([
        'status' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw);

$request_id = $data->request_id ?? null;
$amount = $data->amount ?? null;
$agronomist_id = $data->agronomist_id ?? null;

if (!$request_id || !$amount || !$agronomist_id) {
    echo json_encode([
        'status' => false,
        'message' => 'Missing payment data'
    ]);
    exit;
}

$query = "SELECT * FROM payment_requests 
          WHERE request_id = :rid 
          AND farmer_id = :fid 
          AND agronomist_id = :aid 
          AND amount = :amt 
          AND status = 'confirmed' 
          LIMIT 1";

$params = [
    'rid' => $request_id,
    'fid' => $farmer_id,
    'aid' => $agronomist_id,
    'amt' => $amount
];

$result = $DB->read($query, $params);

if ($result && count($result) > 0) {
    unset($_SESSION['payment_data']);
    echo json_encode([
        'status' => true,
        'message' => '✔️ Payment confirmed'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => '❌ Payment not completed yet. Please check your phone and try again.'
    ]);
}
