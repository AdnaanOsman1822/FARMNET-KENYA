<?php
date_default_timezone_set('Africa/Nairobi');
session_start();
require_once("../classes/autoload.php");

$DB = new Database();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $_SESSION['reset_error'] = "Passwords do not match.";
        header("Location: ../reset_password.php?token=" . urlencode($token));
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['reset_error'] = "Password must be at least 6 characters.";
        header("Location: ../reset_password.php?token=" . urlencode($token));
        exit;
    }

    // Check token
    $user = $DB->read("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1", [$token]);
    if (!$user) {
        $_SESSION['reset_error'] = "Invalid or expired token.";
        header("Location: ../reset_password.php?token=" . urlencode($token));
        exit;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Update password & clear reset token
    $DB->write("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?", [$hashed, $token]);

    // Direct redirect to login
    header("Location: ../login.php?reset=success");
    exit;
}
