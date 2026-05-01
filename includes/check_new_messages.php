<?php
date_default_timezone_set('Africa/Nairobi');
session_start();
require_once("../classes/autoload.php");

$DB = new Database();
$myid = $_SESSION['userid'] ?? null;

if (!$myid) {
    echo json_encode(['new_message' => false]);
    exit;
}

$sql = "SELECT messages.*, users.first_name, users.last_name FROM messages 
        JOIN users ON users.userid = messages.sender
        WHERE receiver = :myid AND seen = 0 
        ORDER BY id DESC LIMIT 1";

$rows = $DB->read($sql, ['myid' => $myid]);

if (is_array($rows)) {
    $msg = $rows[0];

    // Handle message preview
    $message_preview = " Sent a message";

    if (!empty($msg->file)) {
        $ext = strtolower(pathinfo($msg->file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $message_preview = "Sent an image";
        } else {
            $message_preview = "Sent a file";
        }
    } elseif (!empty($msg->message)) {
        $message_preview = trim($msg->message);
        if (strlen($message_preview) > 30) {
            $message_preview = mb_substr($message_preview, 0, 30) . "...";
        }
    }

    echo json_encode([
        'new_message' => true,
        'sender_id' => $msg->sender,
        'sender_name' => $msg->first_name . ' ' . $msg->last_name,
        'message' => $message_preview
        // You can add 'time' => $msg->date if needed
    ]);
} else {
    echo json_encode(['new_message' => false]);
}
