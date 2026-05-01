<?php
require_once("../Classes/autoload.php");
session_start();

$me = $_SESSION['userid'] ?? null;
$search = "";

// Handle search input
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $DATA = json_decode(file_get_contents("php://input"));
    $search = trim($DATA->search ?? "");
}

if (!$me) {
    http_response_code(403);
    exit("Unauthorized");
}

// Base query (exclude myself)
$sql = "SELECT * FROM users WHERE userid != :me";
$params = ['me' => $me];

if ($search != "") {
    $sql .= " AND (first_name LIKE :s OR last_name LIKE :s OR username LIKE :s)";
    $params['s'] = "%$search%";
}

$users = $DB->read($sql, $params);
$chat_users = [];

if (is_array($users)) {
    foreach ($users as $user) {
        // Get latest message between me and this user
        $msg_sql = "SELECT * FROM messages 
                    WHERE (sender = :me AND receiver = :them) 
                       OR (sender = :them AND receiver = :me)
                    ORDER BY id DESC LIMIT 1";
        $msg_data = ['me' => $me, 'them' => $user->userid];
        $last_msg = $DB->read($msg_sql, $msg_data);

        if (is_array($last_msg)) {
            $msg = $last_msg[0];

            // Determine preview using extension if it's a file
            $msg_text = htmlspecialchars($msg->message);
            $ext = strtolower(pathinfo($msg->message, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $preview = "<i>🖼️ Image</i>";
            } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'])) {
                $preview = "<i>📎 File</i>";
            } else {
                $preview = $msg_text;
            }

            $user->last_text = $preview;
            $user->last_time = date("jS M H:i", strtotime($msg->date));
            $user->last_msg_id = $msg->id;
            $user->has_unseen = ($msg->receiver == $me && !$msg->seen);

            $chat_users[] = $user;
        }
    }

    // Sort users by most recent message
    usort($chat_users, fn($a, $b) => $b->last_msg_id - $a->last_msg_id);

    // Output users
    foreach ($chat_users as $user) {
        $default_img = "ui/images/user.jpg";
        $image_path = __DIR__ . "/../" . $user->image;
        $img_src = file_exists($image_path) ? $user->image : $default_img;

        $green_dot = $user->has_unseen
            ? "<span class='unseen-dot' id='unseen_dot_$user->userid' style='color:green;font-size:20px;margin-left:5px;'>&bull;</span>"
            : "";

        echo "
        <div class='chat_user' onclick='startChat(\"$user->userid\")'>
            <img src='$img_src' class='chat_user_img'>
            <div class='info'>
                <div class='name'>{$user->first_name} {$user->last_name} $green_dot</div>
                <div class='last_message'>" . ($user->last_text ?: "<i>No messages</i>") . "</div>
                <div class='last_message_time'>{$user->last_time}</div>
            </div>
        </div>";
    }
} else {
    echo "<div style='padding:20px; color:gray;'>No chats found.</div>";
}
