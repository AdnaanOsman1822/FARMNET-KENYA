<?php
require_once("../classes/autoload.php");
$DB = new Database();

$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

$keyword = strtolower(trim($DATA_OBJ->keyword ?? ""));
$role = strtolower(trim($DATA_OBJ->role ?? ""));

$params = [];
$query = "
    SELECT 
        u.userid, 
        u.first_name, 
        u.last_name, 
        u.username,
        u.email, 
        u.phone_number, 
        u.location,
        u.role, 
        u.suspended, 
        a.is_verified
    FROM users u
    LEFT JOIN agronomists a ON u.userid = a.userid
    WHERE u.suspended = 0
";

if ($role) {
    $query .= " AND u.role = :role ";
    $params['role'] = $role;
}

if ($keyword) {
    $query .= " 
        AND (
            LOWER(u.first_name) LIKE :kw 
            OR LOWER(u.last_name) LIKE :kw 
            OR LOWER(u.username) LIKE :kw
            OR LOWER(u.email) LIKE :kw 
            OR u.phone_number LIKE :kw
        )
    ";
    $params['kw'] = "%$keyword%";
}

$query .= " ORDER BY u.userid DESC LIMIT 50";

$results = $DB->read($query, $params);

if (is_array($results)) {
    foreach ($results as $row) {
        // ✅ Append tick for verified agronomists
        if ($row->role == 'agronomist' && $row->is_verified == 1) {
            $row->first_name .= " ✅";
        }
    }
}

echo json_encode($results);
