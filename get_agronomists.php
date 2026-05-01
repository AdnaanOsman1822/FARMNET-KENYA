<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../classes/autoload.php");
$DB = new Database();

header('Content-Type: application/json');

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['userid'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Optional: Check if the logged-in user is actually an admin
$admin_id = $_SESSION['userid'];
$user = $DB->read("SELECT role FROM users WHERE userid = :id LIMIT 1", ['id' => $admin_id]);
if (!$user || $user[0]->role !== 'admin') {
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Get agronomists joined with users table
$query = "SELECT 
            a.id AS agronomist_id,
            u.userid,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.phone_number,
            u.location,
            u.image,
            a.qualification,
            a.document_path,
            a.is_verified,
            a.created_at,
            a.verified_by,
            a.verified_at
          FROM agronomists a
          JOIN users u ON a.userid = u.userid
          WHERE a.is_verified = 0 AND a.received = 1
          ORDER BY a.created_at DESC";

$rows = $DB->read($query);

if ($rows) {
    echo json_encode(['agronomists' => $rows]);
} else {
    echo json_encode(['agronomists' => []]);
}
