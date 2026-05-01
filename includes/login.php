<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload DB
require_once(__DIR__ . "/../Classes/autoload.php");

$info = (object)[];
$data = [];
$Error = "";

global $DB;

// Get input
$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

// Email
$data['email'] = isset($DATA_OBJ->email) ? trim($DATA_OBJ->email) : '';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $Error .= "Please enter a valid email.<br>";
}

// Password
$password = $DATA_OBJ->password ?? '';
if (empty($password)) {
    $Error .= "Please enter your password.<br>";
}

// Proceed if no validation errors
if ($Error == "") {
    $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $result = $DB->read($query, $data);

    if (is_array($result)) {
        $user = $result[0];
        // Check if user is suspended
    if ($user->suspended) 
        {
        echo json_encode(["message" => "⛔ Your account has been suspended. Please Contact farmnet.kenya@gmail.com for assistance. "]);
        exit;
    }
        if (password_verify($password, $user->password)) {

            file_put_contents(__DIR__ . "/../Classes/debug_session.txt", "Setting userid: " . $user->userid . "\n", FILE_APPEND);


            $_SESSION['userid'] = $user->userid;
            $_SESSION['role'] = $user->role;

            file_put_contents(__DIR__ . "/../Classes/debug_session.txt", "Session after setting:\n" . print_r($_SESSION, true), FILE_APPEND);

            $info->message = "Login successful.";
            $info->data_type = "info";
            $info->role = $user->role; // ✅ Include role in response
        } else {
            $info->message = "Incorrect password.";
            $info->data_type = "error";
        }
    } else {
        $info->message = "No account found with that email.";
        $info->data_type = "error";
    }
} else {
    $info->message = $Error;
    $info->data_type = "error";
}

echo json_encode($info);
