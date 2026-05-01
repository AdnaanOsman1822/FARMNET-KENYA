<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

$farmer_id = $_SESSION['userid'];
$request_id = $_POST['request_id'];
$agronomist_id = $_POST['agronomist_id'];
$rating = $_POST['rating'];
$review = $_POST['review'] ?? '';

if ($rating >= 1 && $rating <= 5) {
    $query = "INSERT INTO ratings (request_id, farmer_id, agronomist_id, rating, review) 
              VALUES (:rid, :fid, :aid, :rate, :rev)";
    $DB->write($query, [
        'rid' => $request_id,
        'fid' => $farmer_id,
        'aid' => $agronomist_id,
        'rate' => $rating,
        'rev' => $review
    ]);

    $_SESSION['flash'] = "Thank you for rating the service!";
    header("Location: farmer_history.php");
    die;
} else {
    echo "Invalid rating.";
}
