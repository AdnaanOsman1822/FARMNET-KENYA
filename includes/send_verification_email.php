<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../classes/autoload.php';
require __DIR__ . '/../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$info = (object)[];
global $DB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';

    if (!$email) {
        $info->status = "error";
        $info->message = "Email missing.";
        echo json_encode($info);
        exit;
    }

    // Fetch user details by email
    $user = $DB->read("SELECT userid, first_name, verification_token, verified FROM users WHERE email = :email LIMIT 1", ['email' => $email]);

    if (!$user || count($user) === 0) {
        $info->status = "error";
        $info->message = "No user found with that email.";
        echo json_encode($info);
        exit;
    }

    $user = $user[0];

    if ($user->verified == 1) {
        $info->status = "info";
        $info->message = "Email is already verified.";
        echo json_encode($info);
        exit;
    }

    // Generate a new token if none exists
    if (empty($user->verification_token)) {
        $new_token = bin2hex(random_bytes(16));
        $DB->write("UPDATE users SET verification_token = :token WHERE userid = :userid", [
            'token' => $new_token,
            'userid' => $user->userid
        ]);
    } else {
        $new_token = $user->verification_token;
    }

    $verificationLink = "https://6cc556b1b2a8.ngrok-free.app/mychatui/includes/verify.php?token=" . $new_token;

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'farmnet.kenya@gmail.com';
        $mail->Password   = 'cudawircfjwhgcoe';  // your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('farmnet.kenya@gmail.com', 'Farmnet Kenya');
        $mail->addAddress($email, $user->first_name);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Farmnet Kenya';
        $mail->Body    = "
            Hi {$user->first_name},<br><br>
            Please click the link below to verify your email:<br>
            <a href='{$verificationLink}'>Verify Email</a><br><br>
            If you did not request this, please ignore this email.
        ";

        $mail->send();

        $info->status = "success";
        $info->message = "Verification email sent.";
    } catch (Exception $e) {
        $info->status = "error";
        $info->message = "Failed to send email: " . $mail->ErrorInfo;
    }

    echo json_encode($info);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
