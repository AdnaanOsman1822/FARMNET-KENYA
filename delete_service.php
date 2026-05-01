<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

// Ensure user is logged in
$userid = $_SESSION['userid'] ?? null;
if (!$userid) {
    header("Location: login.php");
    die;
}

// Get service ID from URL
$service_id = $_GET['id'] ?? null;
if (!$service_id || !is_numeric($service_id)) {
    die("❌ Invalid service ID.");
}

// Confirm ownership before deleting
$service = $DB->read("SELECT * FROM services WHERE service_id = :id AND agronomist_id = :userid LIMIT 1", [
    'id' => $service_id,
    'userid' => $userid
]);

if ($service) {
    $DB->write("DELETE FROM services WHERE service_id = :id", ['id' => $service_id]);
    header("Location: my_services.php?deleted=1");
    exit;
} else {
    die("❌ You are not authorized to delete this service.");
}
