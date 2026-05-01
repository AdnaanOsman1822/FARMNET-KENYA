<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Africa/Nairobi');


require_once __DIR__ . '/../classes/autoload.php';
require __DIR__ . '/../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$DB = new Database();

// Accept email from JSON or normal form POST
$input = json_decode(file_get_contents('php://input'), true);
if (isset($input['email'])) {
    $email = trim($input['email']);
} elseif (isset($_POST['email'])) {
    $email = trim($_POST['email']);
} else {
    $email = '';
}

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../forgot_password.php?status=error&msg=" . urlencode('Please provide a valid email address.'));
    exit;
}


// Find user by email
$user = $DB->read("SELECT userid, first_name, verified, reset_token FROM users WHERE email = :email LIMIT 1", ['email' => $email]);

// Always respond with a generic success to avoid user/email enumeration
$genericSuccess = ['status' => 'success', 'message' => 'If this email is registered, a password reset link has been sent.'];

if (!$user || count($user) === 0) {
    header("Location: ../forgot_password.php?status=success&msg=" . urlencode('If this email is registered, a password reset link has been sent.'));
    exit;
}


$user = $user[0];

// If user not verified
if ($user->verified != 1) {
    header("Location: ../forgot_password.php?status=error&msg=" . urlencode('Please verify your email before requesting a password reset.'));
    exit;
}


// Generate token & expiry
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));


// Save token to DB
$update = $DB->write(
    "UPDATE users SET reset_token = :token, reset_expires = :expires WHERE userid = :userid",
    ['token' => $token, 'expires' => $expires, 'userid' => $user->userid]
);

if (!$update) {
    header("Location: ../forgot_password.php?status=error&msg=" . urlencode('Server error saving reset token.'));
    exit;
}


// Build reset link
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$resetLink = $protocol . $host . '/mychatui/reset_password.php?token=' . $token;

// Send email via PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'farmnet.kenya@gmail.com';
    $mail->Password   = 'cudawircfjwhgcoe'; // your app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('farmnet.kenya@gmail.com', 'Farmnet Kenya');
    $mail->addAddress($email, $user->first_name);

    $mail->isHTML(true);
    $mail->Subject = 'Farmnet Kenya — Password Reset Request';
        $mail->Body    = "
        <div style='font-family: Jura, sans-serif; color:#102D03;'>
            <div style='max-width:600px;margin:0 auto;background:#fff;border:2px solid #7CD375;padding:24px;border-radius:12px;text-align:center;'>
            <h2 style='margin-top:0;'>Reset your Farmnet Kenya password</h2>
            <p>Hi " . htmlspecialchars($user->first_name) . ",</p>
            <p>We received a request to reset your password. Click the button below to set a new password. This link expires in 1 hour.</p>
            <a href='$resetLink' style='display:inline-block;padding:12px 24px;background:#7CD375;color:#fff;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:12px;'>Reset Password</a>
            <p style='margin-top:18px;color:#666;font-size:13px;'>If you didn't request this, ignore this message.</p>
            </div>
        </div>
        ";
        $mail->AltBody = "Reset your Farmnet Kenya password: $resetLink (expires in 1 hour)";


$mail->send();
header("Location: ../forgot_password.php?status=success&msg=" . urlencode('If this email is registered, a password reset link has been sent.'));
exit;
} 
catch (Exception $e) {
    file_put_contents(__DIR__ . '/../email_error.log', date('c') . " - Reset mail error: " . $mail->ErrorInfo . PHP_EOL, FILE_APPEND);
    header("Location: ../forgot_password.php?status=success&msg=" . urlencode('If this email is registered, a password reset link has been sent.'));
    exit;
}

