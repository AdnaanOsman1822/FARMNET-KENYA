<?php
require_once("../classes/autoload.php");
$DB = new Database();

$RAW = file_get_contents("php://input");
$OBJ = json_decode($RAW);

$userid = $OBJ->userid ?? 0;
$DB->write("UPDATE users SET suspended = 0 WHERE userid = :id", ['id' => $userid]);

echo json_encode(["message" => "✅ User reactivated."]);
