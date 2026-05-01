<?php
session_start();
require_once("../classes/autoload.php");

$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

if (!isset($DATA_OBJ->query)) {
    echo json_encode([]);
    exit;
}

$find = "%" . trim($DATA_OBJ->query) . "%";
$role = isset($DATA_OBJ->role) ? trim($DATA_OBJ->role) : "";

$DB = new Database();

$query = "
    SELECT u.userid, u.first_name, u.last_name, u.username, u.location, u.role, u.image, u.suspended,
           a.is_verified
    FROM users u
    LEFT JOIN agronomists a ON u.userid = a.userid
    WHERE (
        u.first_name LIKE :find 
        OR u.last_name LIKE :find 
        OR u.username LIKE :find 
        OR u.location LIKE :find
    )
";

$params = ['find' => $find];

if ($role != "") {
    $query .= " AND u.role = :role";
    $params['role'] = $role;
}

$query .= " ORDER BY u.first_name LIMIT 20";

$results = $DB->read($query, $params);

$output = [];

if (is_array($results)) {
    foreach ($results as $row) {
        $default_image = "ui/images/user.jpg";
        $image_path = "../" . ($row->image ?: $default_image);
        $exists = file_exists(__DIR__ . "/../" . $row->image);
        $final_image = $exists ? $row->image : $default_image;

        $output[] = [
            'userid' => $row->userid,
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'username' => $row->username,
            'location' => $row->location,
            'role' => $row->role,
            'image' => $final_image,
            'is_verified' => $row->is_verified ?? 0,
            'suspended' => $row->suspended ?? 0
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($output);
