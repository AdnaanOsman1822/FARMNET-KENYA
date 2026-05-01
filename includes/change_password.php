<?php
// includes/change_password.php
session_start();

header('Content-Type: application/json');

// adjust path if your project uses different casing
require_once __DIR__ . '/../classes/autoload.php';

$DB = new Database();

// Ensure user is logged in
if (!isset($_SESSION['userid'])) {
    echo json_encode(["data_type" => "error", "message" => "You must be logged in to change your password."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["data_type" => "error", "message" => "Invalid request method."]);
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current) || empty($new) || empty($confirm)) {
    echo json_encode(["data_type" => "error", "message" => "All fields are required."]);
    exit;
}

if ($new !== $confirm) {
    echo json_encode(["data_type" => "error", "message" => "New passwords do not match."]);
    exit;
}

if (strlen($new) < 6) {
    echo json_encode(["data_type" => "error", "message" => "New password must be at least 6 characters."]);
    exit;
}

// Get user from DB
$userRow = $DB->read("SELECT * FROM users WHERE userid = ? LIMIT 1", [$_SESSION['userid']]);
if (!$userRow || count($userRow) === 0) {
    echo json_encode(["data_type" => "error", "message" => "User not found."]);
    exit;
}

$user = $userRow[0];

// Verify current password
if (!password_verify($current, $user->password)) {
    echo json_encode(["data_type" => "error", "message" => "Current password is incorrect."]);
    exit;
}
// Prevent reusing the same password
if (password_verify($new, $user->password)) {
    die(json_encode(["data_type" => "error", "message" => "New password cannot be the same as the current password."]));
}
// Update password
$hashed = password_hash($new, PASSWORD_DEFAULT);
$ok = $DB->write("UPDATE users SET password = ? WHERE userid = ?", [$hashed, $_SESSION['userid']]);

if ($ok) {
    echo json_encode([
        "data_type" => "info",
        "message" => "Password changed successfully. Redirecting...",
        "redirect" => "/mychatui/index.php" // absolute path avoids /dashboard issue
    ]);
    exit;
}
else {
    echo json_encode(["data_type" => "error", "message" => "Failed to update password. Try again later."]);
    exit;
}
