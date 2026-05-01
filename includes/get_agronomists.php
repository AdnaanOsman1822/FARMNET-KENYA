<?php
require_once("../Classes/autoload.php");
header("Content-Type: application/json");

$DB = new Database();

$data_raw = file_get_contents("php://input");
$data_obj = json_decode($data_raw);

if (!isset($data_obj->data_type) || $data_obj->data_type !== "unverified_agronomists") {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT a.userid, a.qualification, a.document_path, u.first_name, u.last_name, u.email, u.phone_number 
    FROM agronomists a 
    JOIN users u ON a.userid = u.userid 
    WHERE a.is_verified = 0 AND a.received = 1
    ORDER BY a.created_at DESC
";

$result = $DB->read($query);
echo json_encode($result ?: []);
