<?php
require_once("../Classes/autoload.php");
session_start();

header("Content-Type: application/json");

$user_id = $_SESSION['userid'] ?? null;

$response = [
    "is_verified" => false,
    "received" => false
];

if (!$user_id) {
  echo json_encode($response);
  exit;
}

$DB = new Database();
$query = "SELECT is_verified, received FROM agronomists WHERE userid = :userid LIMIT 1";
$result = $DB->read($query, ['userid' => $user_id]);

if ($result && isset($result[0])) {
  $response["is_verified"] = (bool)$result[0]->is_verified;
  $response["received"] = (bool)$result[0]->received;
}

echo json_encode($response);

