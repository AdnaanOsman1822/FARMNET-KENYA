<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../classes/autoload.php");

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    header("Location: ../my_requests.php?error=unauthorized");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'] ?? null;

    if (!$request_id || !is_numeric($request_id)) {
        header("Location: ../my_requests.php?error=invalid");
        exit;
    }

    // Check ownership and status
    $query = "SELECT * FROM service_requests WHERE request_id = :rid AND farmer_id = :fid AND status = 'pending' LIMIT 1";
    $result = $DB->read($query, ['rid' => $request_id, 'fid' => $farmer_id]);

    if ($result && count($result) > 0) {
        $agronomist_id = $result[0]->agronomist_id;

        // Delete the request
        $DB->write("DELETE FROM service_requests WHERE request_id = :rid", ['rid' => $request_id]);


        header("Location: ../my_requests.php?cancelled=1");
        exit;
    } else {
        header("Location: ../my_requests.php?error=unauthorized");
        exit;
    }
} else {
    header("Location: ../my_requests.php");
    exit;
}
