<?php
date_default_timezone_set('Africa/Nairobi');
require_once(__DIR__ . '/../classes/autoload.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

$DB = new Database();
$info = (object)[];
$sender = $_SESSION['userid'] ?? null;
$receiver = null;
$message = "";
$file_path = "";

// ✅ Detect content type: JSON or FormData
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, "application/json") !== false) {
    // JSON input
    $DATA_RAW = file_get_contents("php://input");
    $DATA_OBJ = json_decode($DATA_RAW);

    $receiver = $DATA_OBJ->receiver ?? null;
    $message = trim($DATA_OBJ->message ?? "");
} else {
    // FormData (file + message)
    $receiver = $_POST['receiver'] ?? null;
    $message = trim($_POST['message'] ?? "");

    // ✅ Handle uploaded file
    if (!empty($_FILES['file']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'xlsx', 'zip'];

        if (!in_array($ext, $allowed)) {
            $info->message = "Unsupported file type";
            echo json_encode($info);
            exit;
        }

        $new_filename = uniqid() . "." . $ext;
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            $file_path = "uploads/" . $new_filename;
        } else {
            $info->message = "File upload failed";
            echo json_encode($info);
            exit;
        }
    }
}

// ✅ Validate
if (!$sender || !$receiver || ($message === "" && $file_path === "")) {
    $info->message = "Missing fields.";
    echo json_encode($info);
    exit;
}

// ✅ Prepare message insert
$arr = [
    'msgid' => uniqid(),
    'sender' => $sender,
    'receiver' => $receiver,
    'message' => $message,
    'files' => $file_path,
    'date' => date("Y-m-d H:i:s"),
    'seen' => 0,
    'received' => 1,
    'deleted_sender' => 0,
    'deleted_receiver' => 0
];

$query = "INSERT INTO messages 
          (msgid, sender, receiver, message, files, date, seen, received, deleted_sender, deleted_receiver) 
          VALUES 
          (:msgid, :sender, :receiver, :message, :files, :date, :seen, :received, :deleted_sender, :deleted_receiver)";

$result = $DB->write($query, $arr);

// ✅ Return response
if ($result) {
    $info->message = "Message sent";
    $info->data_type = "send_message";
} else {
    $info->message = "Failed to send message";
}
echo json_encode($info);
