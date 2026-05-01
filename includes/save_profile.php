<?php

require_once(__DIR__ . '/../classes/autoload.php');

$info = (object)[];
$info->data_type = "save_profile";
$info->message = "";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$userid = $_SESSION['userid'] ?? null;

if (!$userid) {
    $info->message = "You must be logged in to update your profile.";
    echo json_encode($info);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (
    !$data ||
    !isset($data->username) ||
    !isset($data->email) ||
    !isset($data->location) // ✅ Check location is present
) {
    $info->message = "Incomplete data provided.";
    echo json_encode($info);
    exit;
}

$DB = new Database();

// Check for duplicate username
$checkUser = $DB->read("SELECT userid FROM users WHERE username = :username AND userid != :userid LIMIT 1", [
    'username' => $data->username,
    'userid' => $userid
]);
if ($checkUser) {
    $info->message = "Username is already taken.";
    echo json_encode($info);
    exit;
}

// Check for duplicate email
$checkEmail = $DB->read("SELECT userid FROM users WHERE email = :email AND userid != :userid LIMIT 1", [
    'email' => $data->email,
    'userid' => $userid
]);
if ($checkEmail) {
    $info->message = "Email is already in use.";
    echo json_encode($info);
    exit;
}

// Prepare values to update
$arr = [
    "username" => $data->username,
    "first_name" => $data->first_name,
    "last_name" => $data->last_name,
    "phone_number" => $data->phone_number,
    "email" => $data->email,
    "location" => $data->location,
    "userid" => $userid,
];

// Update user profile
$query = "UPDATE users SET 
    username = :username,
    first_name = :first_name,
    last_name = :last_name,
    phone_number = :phone_number,
    email = :email,
    location = :location
    WHERE userid = :userid LIMIT 1";

$result = $DB->write($query, $arr);

$info->message = $result ? "Profile updated successfully." : "Profile update failed.";

echo json_encode($info);
