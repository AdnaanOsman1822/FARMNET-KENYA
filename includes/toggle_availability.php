<?php
require_once __DIR__ . '/../classes/autoload.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB = new Database();
$userid = $_SESSION['userid'] ?? null;
$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

$response = ['success' => false];

if ($userid && isset($DATA_OBJ->available)) {
    $available = $DATA_OBJ->available ? 1 : 0;
    $query = "UPDATE agronomists SET available = :available WHERE userid = :id AND is_verified = 1 LIMIT 1";
    $result = $DB->write($query, ['available' => $available, 'id' => $userid]);

    if ($result) {
        $response['success'] = true;
    }
}

echo json_encode($response);
