<?php
session_start();
require_once("../Classes/autoload.php");
$DB = new Database();

$response = [
    'success' => false,
    'message' => ''
];

// ✅ Check if user is logged in
if (!isset($_SESSION['userid'])) {
    $response['message'] = "You must be logged in.";
    echo json_encode($response);
    exit;
}

$userid = $_SESSION['userid'];
$qualification = trim($_POST['qualification'] ?? '');
$document = $_FILES['document'] ?? null;

// ✅ Validate qualification and file
if (!$qualification || strlen($qualification) < 3 || !$document) {
    $response['message'] = "Missing or invalid input.";
    echo json_encode($response);
    exit;
}

// ✅ Allow only PDF
$allowedTypes = ['application/pdf'];
if (!in_array($document['type'], $allowedTypes)) {
    $response['message'] = "Only PDF files are allowed.";
    echo json_encode($response);
    exit;
}

// ✅ File handling - store under uploads/docs/
$uploadFolder = "../uploads/docs/";
if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0777, true);
}

$extension = strtolower(pathinfo($document['name'], PATHINFO_EXTENSION));
$filename = $userid . "_" . time() . "." . $extension;
$server_path = $uploadFolder . $filename;
$public_path = "uploads/docs/" . $filename; // 👈 This is the relative path saved to DB

if (!move_uploaded_file($document['tmp_name'], $server_path)) {
    $response['message'] = "Failed to move uploaded file.";
    echo json_encode($response);
    exit;
}

// ✅ Insert or Update agronomists table
$query = "INSERT INTO agronomists (userid, qualification, document_path, received, created_at)
          VALUES (:userid, :qualification, :doc, 1, NOW())
          ON DUPLICATE KEY UPDATE
            qualification = :qualification,
            document_path = :doc,
            received = 1,
            created_at = NOW()";

$params = [
    'userid' => $userid,
    'qualification' => $qualification,
    'doc' => $public_path
];

$DB->write($query, $params);

// ✅ Redirect back to dashboard
header("Location: ../index.php");
exit;
