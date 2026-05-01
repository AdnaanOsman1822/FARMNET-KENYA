<?php
require_once("../classes/autoload.php");
session_start();

$DB = new Database();
$agronomist_id = $_SESSION['userid'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id']) && $agronomist_id) {
    $request_id = (int)$_POST['request_id'];

    // Check the request belongs to this agronomist
    $check = $DB->read("SELECT * FROM service_requests WHERE request_id = :rid AND agronomist_id = :aid", [
        'rid' => $request_id,
        'aid' => $agronomist_id
    ]);

    if ($check) {
        // Confirm payment status is 'confirmed'
        $pay = $DB->read("
            SELECT status 
            FROM payment_requests 
            WHERE request_id = :rid 
            ORDER BY id DESC 
            LIMIT 1
        ", ['rid' => $request_id]);

        if (isset($pay[0]->status) && $pay[0]->status === 'confirmed') {
            $DB->write("UPDATE service_requests SET status = 'completed' WHERE request_id = :rid", ['rid' => $request_id]);
            $_SESSION['flash'] = "✅ Service marked as completed!";
        } else {
            $_SESSION['flash'] = "⚠️ Payment not yet confirmed. Cannot complete.";
        }
    }
}

header("Location: ../agronomist_requests.php");
die;
