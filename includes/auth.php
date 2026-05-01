<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once "../classes/autoload.php"; // Adjust path if needed
$DB = new Database();

// If user not logged in, redirect
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

// Load full user data into session if not already set
if (!isset($_SESSION['user'])) {
    $arr['userid'] = $_SESSION['userid'];
    $query = "SELECT * FROM users WHERE userid = :userid LIMIT 1";
    $result = $DB->read($query, $arr);

    if ($result && is_array($result)) {
        $_SESSION['user'] = $result[0];
    } else {
        // Invalid userid in session — force logout
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
