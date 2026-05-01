<?php
date_default_timezone_set('Africa/Nairobi');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . "/../classes/autoload.php");
$DB = new Database(); 


$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

$me = $_SESSION['userid'] ?? null;
$them = $DATA_OBJ->userid ?? null;

if (!is_numeric($me) || !is_numeric($them)) {
    http_response_code(400);
    die("Invalid user IDs");
}

// Mark their messages to me as seen
$DB->write("UPDATE messages SET seen = 1 WHERE receiver = :me AND sender = :them", [
    'me' => $me,
    'them' => $them
    
]);

// Fetch conversation
$sql = "SELECT * FROM messages 
        WHERE (sender = :me AND receiver = :them) 
        OR (sender = :them AND receiver = :me)
        ORDER BY id ASC";

$rows = $DB->read($sql, ['me' => $me, 'them' => $them]);

if (is_array($rows)) {
    foreach ($rows as $msg) {
        $align = $msg->sender == $me ? "right" : "left";
        $color = $align == "right" ? "#dcf8c6" : "#ffffff";
        $is_sender = $msg->sender == $me;
        $is_receiver = $msg->receiver == $me;

        // Decide message content based on deletion status
        if ($is_sender && $msg->deleted_sender == 1) {
            $content = "<i style='color:gray;'>You deleted this message.</i>";
        } elseif ($is_receiver && $msg->deleted_receiver == 1) {
            $content = "<i style='color:gray;'>This message was deleted.</i>";
        } else {
            $message = htmlspecialchars($msg->message);
            $content = $message;

            // Handle attached files
            if (!empty($msg->files)) {
                $file_path = $msg->files;
                $file_url = $file_path;
                $file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                if (in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
                    $content .= "<br><img src='$file_url' style='max-width:200px;border-radius:10px;margin-top:5px;cursor:pointer;' onclick='previewImage(\"$file_url\")'>";
                } else {
                    $filename = basename($file_path);
                    $content .= "<br><a href='$file_url' target='_blank' download style='color:blue;'>📎 $filename</a>";
                }
            }
        }

        $time = date("H:i", strtotime($msg->date));
        $seen = ($msg->sender == $me) ? ($msg->seen ? "✔✔" : "✔") : "";

$deleteFlag = $is_sender ? 'true' : 'false';

echo "
<div class='message-container $align'>
    <div class='message-wrapper'>
        <div class='message-bubble' style='background-color: $color'>
            <span class='options-icon' onclick='toggleOptionsMenu(this)'>⋮</span>
            <div class='options-menu'>
                <div onclick='deleteMessage({$msg->id}, $deleteFlag)'>Delete</div>
            </div>
            <div class='message-content'>$content</div>
            <div class='message-meta'>$time $seen</div>
        </div>
    </div>
</div>";

    }
} else {
    echo "<div style='color:gray; text-align:center; margin-top:20px;'>No messages yet.</div>";
}
?>
