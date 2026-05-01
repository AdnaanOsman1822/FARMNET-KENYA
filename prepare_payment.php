<?php
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}

// Collect posted data from my_requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['payment_data'] = [
        'request_id' => $_POST['request_id'] ?? null,
        'amount' => $_POST['amount'] ?? null,
        'agronomist_id' => $_POST['agronomist_id'] ?? null
    ];
    header("Location: payment_preview.php");
    die;
}

header("Location: my_requests.php");
