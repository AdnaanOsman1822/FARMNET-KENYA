<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../classes/autoload.php");

$DB = new Database();

// Use POSTed userid if available, otherwise use session
$data = json_decode(file_get_contents("php://input"));
$userid = $data->userid ?? ($_SESSION['userid'] ?? null);

if (!$userid) {
    echo json_encode(['status' => 'error', 'message' => 'No userid provided or session expired']);
    exit;
}

$arr['userid'] = $userid;

$sql = "SELECT userid, username, first_name, last_name, role, image FROM users WHERE userid = :userid LIMIT 1";
$result = $DB->read($sql, $arr);

if (is_array($result)) {
    $user = $result[0];

    $default_image = "ui/images/user.jpg";
    $image_path = __DIR__ . "/../" . $user->image;
    $user->image = (file_exists($image_path) && !empty($user->image)) ? $user->image : $default_image;

    echo json_encode([
        'status' => 'success',
        'logged_in' => true, // ← ✅ Add this line
        'user' => $user
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'logged_in' => false, // ← Also handle this case
        'message' => 'User not found'
    ]);
}
