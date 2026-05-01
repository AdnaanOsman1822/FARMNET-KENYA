<?php
require_once("../Classes/autoload.php");
session_start();

header("Content-Type: application/json");

$admin_id = $_SESSION['userid'] ?? null;
$data_raw = file_get_contents("php://input");
$data_obj = json_decode($data_raw);
$userid = $data_obj->userid ?? null;

if (!$admin_id || !$userid) {
    echo json_encode(["message" => "Invalid request."]);
    exit;
}

$DB = new Database();

// Reset the application fields
$query = "UPDATE agronomists 
          SET 
            received = 0, 
            document_path = NULL, 
            qualification = NULL, 
            is_verified = 0, 
            verified_by = NULL, 
            verified_at = NULL,
            created_at = NOW() 
          WHERE userid = :userid";

$result = $DB->write($query, ['userid' => $userid]);

if ($result) {
    echo json_encode(["message" => "Agronomist rejected and application reset."]);
} else {
    echo json_encode(["message" => "Failed to reject."]);
}
