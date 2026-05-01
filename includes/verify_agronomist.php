<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../Classes/autoload.php");
session_start();
header("Content-Type: application/json");

$DB = new Database();

// Read raw input
$data_raw = file_get_contents("php://input");
$data_obj = json_decode($data_raw);

// Get user IDs
$userid = $data_obj->userid ?? null;
$admin_id = $_SESSION['userid'] ?? null;

// Validate input
if (!$admin_id || !$userid) {
    echo json_encode(["message" => "❌ Invalid request."]);
    exit;
}

// Check if agronomist exists
$check = $DB->read("SELECT * FROM agronomists WHERE userid = :userid", ['userid' => $userid]);

if (!$check) {
    echo json_encode(["message" => "❌ No such agronomist found."]);
    exit;
}

// ✅ Safely check that `received` is 1 (TINYINT)
if ((int)$check[0]->received !== 1) {
    echo json_encode(["message" => "⚠️ Cannot verify. Agronomist is not marked as received."]);
    exit;
}
file_put_contents(__DIR__ . "/../verify_debug.txt", "userid: " . $userid . "\n", FILE_APPEND);

// Perform update
$query = "UPDATE agronomists 
          SET is_verified = 1, 
              verified_by = :admin_id, 
              verified_at = NOW() 
          WHERE userid = :userid AND received = 1";

$params = [
    'admin_id' => $admin_id,
    'userid'   => $userid
];

$success = $DB->write($query, $params);

// Return result
if ($success) {
    echo json_encode(["message" => "✅ Agronomist verified successfully."]);
} else {
    echo json_encode(["message" => "❌ Failed to verify agronomist."]);
}
