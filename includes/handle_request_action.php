<?php
session_start();
require_once("../classes/autoload.php");

$DB = new Database();

$agronomist_id = $_SESSION['userid'] ?? null;

if (!$agronomist_id) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($request_id && in_array($action, ['accept', 'reject'])) {
        // Make sure this request belongs to the current agronomist
        $check_query = "SELECT * FROM service_requests WHERE request_id = :rid AND agronomist_id = :aid LIMIT 1";
        $check = $DB->read($check_query, ['rid' => $request_id, 'aid' => $agronomist_id]);

        if (is_array($check) && count($check) > 0) {
            $new_status = $action === 'accept' ? 'accepted' : 'rejected';

            $update_query = "UPDATE service_requests SET status = :status WHERE request_id = :rid";
            $DB->write($update_query, ['status' => $new_status, 'rid' => $request_id]);

            $_SESSION['flash'] = "✅ Request has been " . $new_status . ".";
        } else {
            $_SESSION['flash'] = "❌ Unauthorized or invalid request.";
        }
    } else {
        $_SESSION['flash'] = "❌ Invalid request action.";
    }

    header("Location: ../agronomist_requests.php");
    exit;
}
?>
