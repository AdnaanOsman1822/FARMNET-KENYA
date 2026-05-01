<?php
require_once("../Classes/autoload.php");
session_start();

$me = $_SESSION['userid'] ?? null;

if (!$me || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid request']);
    exit;
}

$receiver = $_POST['receiver'] ?? null;

if (!is_numeric($receiver)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid receiver']);
    exit;
}

$message = trim($_POST['message'] ?? "");
$file_path = "";

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $folder = "../uploads/chat_files/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $original = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx', 'mp4'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
        exit;
    }

    $filename = "file_" . time() . "_" . rand(1000, 9999) . "." . $ext;
    $target = $folder . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $file_path = "uploads/chat_files/" . $filename;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move file']);
        exit;
    }
}

// Save to DB
$sql = "INSERT INTO messages (sender, receiver, message, files, date, seen)
        VALUES (:sender, :receiver, :message, :files, NOW(), 0)";
$data = [
    'sender' => $me,
    'receiver' => $receiver,
    'message' => $message,
    'files' => $file_path
];

$success = $DB->write($sql, $data);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'File sent']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send file']);
}
