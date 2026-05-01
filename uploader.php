<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once("classes/autoload.php");
$DB = new Database();

$info = (object)[];

// Check login
if (!isset($_SESSION['userid'])) {
    $info->logged_in = false;
    echo json_encode($info);
    die;
}

$id = $_SESSION['userid'];
$destination = "";

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $folder = "uploads";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $safe_name = basename($_FILES['file']['name']);
    $unique_name = $id . "_" . time() . "_" . preg_replace("/[^a-zA-Z0-9.\-_]/", "_", $safe_name);
    $destination = $folder . "/" . $unique_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        // Update image in DB
        $query = "UPDATE users SET image = :image WHERE userid = :userid LIMIT 1";
        $DB->write($query, ['image' => $destination, 'userid' => $id]);

        $info->message = "Image uploaded successfully.";
        $info->data_type = "change_profile_image";
    } else {
        $info->message = "Failed to move uploaded file.";
        $info->data_type = "change_profile_image";
    }
} else {
    $info->message = "No valid file uploaded.";
    $info->data_type = "change_profile_image";
}

echo json_encode($info);
