<?php
session_start();
require_once("../classes/autoload.php");

$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

if (!isset($DATA_OBJ->find)) {
    echo "";
    return;
}

$find = "%" . trim($DATA_OBJ->find) . "%";
$role = isset($DATA_OBJ->role) ? trim($DATA_OBJ->role) : "";

$DB = new Database();

// Join services and exclude admin + suspended users
$query = "
    SELECT u.userid, u.first_name, u.last_name, u.username, u.location, u.role, u.image, 
           a.is_verified,
           GROUP_CONCAT(DISTINCT s.title SEPARATOR ', ') AS services
    FROM users u
    LEFT JOIN agronomists a ON u.userid = a.userid
    LEFT JOIN services s ON u.userid = s.agronomist_id
    WHERE (
        u.first_name LIKE :find 
        OR u.last_name LIKE :find 
        OR u.username LIKE :find 
        OR u.location LIKE :find 
        OR s.title LIKE :find
    )
    AND u.role != 'admin' AND u.suspended = 0
";


$params = ['find' => $find];

if ($role != "") {
    $query .= " AND u.role = :role";
    $params['role'] = $role;
}

$query .= " GROUP BY u.userid ORDER BY u.first_name LIMIT 10";

$results = $DB->read($query, $params);

if (is_array($results)) {
    foreach ($results as $row) {
        $default_image = "ui/images/user.jpg";
        $image_src = file_exists(__DIR__ . "/../" . $row->image) ? $row->image : $default_image;
        $services = $row->services ? htmlspecialchars($row->services) : "";

        // ✅ Green tick for verified agronomists
        $verified_tick = ($row->role == 'agronomist' && $row->is_verified == 1)
            ? ' <span title="Verified Agronomist" style="color:green;">&#x2705;</span>'
            : '';

        echo '
        <div class="user-card" onclick="go_to_profile(\'' . $row->userid . '\')">
            <img src="' . $image_src . '" class="user-image" />
            <div class="user-info">
                <div class="username">@' . htmlspecialchars($row->username) . '</div>
                <div class="name-username"><strong>' . htmlspecialchars($row->first_name . ' ' . $row->last_name) . $verified_tick . '</strong></div>
                <div class="account-type">' . ucfirst($row->role) . '</div>
                <div class="location">📍 ' . htmlspecialchars($row->location) . '</div>';
        
        if (!empty($services)) {
            echo '<div class="location" style="margin-top:5px;"><strong>Services:</strong> ' . $services . '</div>';
        }

        echo '</div></div>';
    }
} else {
    echo "<div style='text-align:center; color:gray;'>No matching users found</div>";
}
