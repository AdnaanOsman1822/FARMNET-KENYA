<?php
require_once("../Classes/autoload.php");
$DB = new Database();
session_start();

$userid = $_SESSION['userid'] ?? null;

if (!$userid) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

// Use object syntax to access ->count
$sql = "SELECT COUNT(*) as count FROM service_requests WHERE farmer_id = :userid AND status = 'pending'";

$result = $DB->read($sql, ['userid' => $userid]);

if (is_array($result) && count($result)) {
    echo json_encode(["count" => $result[0]->count]);
} else {
    echo json_encode(["count" => 0]);
}
