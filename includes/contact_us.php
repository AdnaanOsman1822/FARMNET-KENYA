<?php
session_start();
require_once("../classes/autoload.php");

header('Content-Type: application/json');

if (!isset($_SESSION['userid'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$DB = new Database();

$userid = $_SESSION['userid'];
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$reported_userid = trim($_POST['reported_userid'] ?? '');

// Validation
if ($subject === '' || $message === '') {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if ($subject === 'Report User' && $reported_userid === '') {
    echo json_encode(["success" => false, "message" => "Please select a user to report"]);
    exit;
}

// Insert into reports table
$query = "INSERT INTO reports (userid, reported_userid, subject, message, date) 
          VALUES (:userid, :reported_userid, :subject, :message, NOW())";

$params = [
    'userid' => $userid,
    'reported_userid' => $reported_userid ?: null,
    'subject' => $subject,
    'message' => $message
];

$result = $DB->write($query, $params);

if ($result) {
    echo json_encode(["success" => true, "message" => "Your message has been sent"]);
} else {
    echo json_encode(["success" => false, "message" => "Something went wrong"]);
}
