<?php
require_once("../Classes/autoload.php");
$DB = new Database();
session_start();

$userid = $_SESSION['userid'] ?? null;

if (!$userid) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$query = "
    SELECT 
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
    FROM service_requests
    WHERE agronomist_id = :userid
";

$result = $DB->read($query, ['userid' => $userid]);

// Handle object result properly
$data = $result && isset($result[0]) ? $result[0] : (object)[
    'pending' => 0,
    'accepted' => 0,
    'completed' => 0,
    'cancelled' => 0
];

echo json_encode([
    'status' => 'success',
    'count' => (int)$data->pending,  
    'pending' => (int)$data->pending,
    'accepted' => (int)$data->accepted,
    'completed' => (int)$data->completed,
    'cancelled' => (int)$data->cancelled
]);
