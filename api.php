<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once("classes/autoload.php");
$DB = new Database();

$info = (object)[];

// Handle FormData (used when sending a message or uploading a file)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) || isset($_FILES['file'])) {
    if (!isset($_SESSION['userid'])) {
        $info->logged_in = false;
        echo json_encode($info);
        die;
    }

    include("includes/send_message.php");
    die;
}

// Handle JSON-encoded input
$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

if (!is_object($DATA_OBJ)) {
    $info->message = "Invalid request. No valid JSON found.";
    echo json_encode($info);
    die;
}
file_put_contents("classes/debug_api.txt", 
    "SESSION ID: " . session_id() . "\n" .
    "SESSION CONTENT: " . print_r($_SESSION, true) . "\n" .
    "RAW DATA: " . $DATA_RAW . "\n\n", 
    FILE_APPEND);

if (!isset($_SESSION['userid']) && isset($DATA_OBJ->data_type) && !in_array($DATA_OBJ->data_type, ["login", "signup"])) {
    $info->logged_in = false;
    echo json_encode($info);
    die;
}


switch ($DATA_OBJ->data_type ?? '') {
    case "signup":
        include("includes/signup.php");
        break;
    case "login":
        include("includes/login.php");
        break;
    case "logout":
        include("includes/logout.php");
        break;
    case "user_info":
        include("includes/user_info.php");
        break;
    case "contacts":
        include("includes/contacts.php");
        break;
    case "chats":
    case "chats_refresh":
        include("includes/chats.php");
        break;
    case "profile":
        include("includes/profile.php");
        break;
    case "save_profile":
        include("includes/save_profile.php");
        break;
    case "upload_file":
        include("includes/upload_file.php");
        break;
    case "mark_seen":
        $receiver_id = $_SESSION['userid'];
        $sender_id = $DATA_OBJ->userid;

        $query = "UPDATE messages SET seen = 1 WHERE sender = :sender AND receiver = :receiver";
        $DB->write($query, [
            'sender' => $sender_id,
            'receiver' => $receiver_id
        ]);

        $info->message = "Seen updated";
        echo json_encode($info);
        break;

    case "delete_message":
        if (!isset($DATA_OBJ->message_id) || !is_numeric($DATA_OBJ->message_id)) {
            $info->message = "Message ID missing or invalid.";
            echo json_encode($info);
            die;
        }

        $msg_id = (int)$DATA_OBJ->message_id;
        $user_id = $_SESSION['userid'];
        $is_sender = $DATA_OBJ->is_sender ?? false;

        // Fetch the message
        $query = "SELECT * FROM messages WHERE id = :id LIMIT 1";
        $row = $DB->read($query, ['id' => $msg_id]);

        if ($row && is_array($row)) {
            $msg = $row[0];

            if ($is_sender) {
                // Sender is deleting the message
                $DB->write("UPDATE messages SET deleted_sender = 1, deleted_receiver = 1 WHERE id = :id", ['id' => $msg_id]);
                $info->message = "Message deleted for everyone.";
                $info->status = "deleted_for_everyone";
            } else {
                // Receiver is deleting it for themselves only
                if ($msg->receiver == $user_id) {
                    $DB->write("UPDATE messages SET deleted_receiver = 1 WHERE id = :id", ['id' => $msg_id]);
                    $info->message = "Message deleted for you.";
                    $info->status = "deleted_for_me";
                } else {
                    $info->message = "Permission denied.";
                }
            }

        } else {
            $info->message = "Message not found.";
        }

        echo json_encode($info);
        break;


    default:
        $info->message = "Unknown request.";
        echo json_encode($info);
        break;

}
