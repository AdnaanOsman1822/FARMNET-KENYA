<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once("../classes/autoload.php");
session_start();

$info = (object)[];
$DB = new Database();

// 1. Check if user is logged in
if (!isset($_SESSION['userid'])) {
    $info->message = "You must be logged in to apply.";
    $info->data_type = "error";
    echo json_encode($info);
    exit;
}

$userid = $_SESSION['userid'];
$qualification = $_POST['qualification'] ?? '';
$file = $_FILES['document'] ?? null;

// 2. Validate qualification
if (!$qualification || strlen($qualification) < 5) {
    $info->message = "Please enter a valid qualification.";
    $info->data_type = "error";
    echo json_encode($info);
    exit;
}

// 3. Validate file
if (!$file || $file['error'] !== 0) {
    $info->message = "Please upload a valid document file.";
    $info->data_type = "error";
    echo json_encode($info);
    exit;
}

// 4. Validate file type
$allowed = ['pdf'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    $info->message = "Only PDF files are allowed.";
    $info->data_type = "error";
    echo json_encode($info);
    exit;
}

// 5. Move uploaded file
$upload_dir = __DIR__ . "/../uploads/docs/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
$filename = uniqid("doc_", true) . "." . $ext;
$server_path = $upload_dir . $filename;
$public_path = "mychatui/uploads/docs/" . $filename;

if (!move_uploaded_file($file["tmp_name"], $server_path)) {
    $info->message = "Failed to upload document.";
    $info->data_type = "error";
    echo json_encode($info);
    exit;
}

// 6. Update agronomists table
$query = "UPDATE agronomists 
          SET qualification = :qualification, document_path = :doc, is_verified = 0 
          WHERE userid = :userid 
          LIMIT 1";

$result = $DB->write($query, [
    'qualification' => $qualification,
    'doc' => $public_path,
    'userid' => $userid
]);

if ($result) {
    $info->message = "Your verification request has been submitted.";
    $info->data_type = "info";
} else {
    $info->message = "Could not submit verification request.";
    $info->data_type = "error";
}

echo json_encode($info);
