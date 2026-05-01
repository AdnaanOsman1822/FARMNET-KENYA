<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
error_reporting(E_ALL);


$info = (object)[];
$data = [];
$Error = "";

// Get raw data and decode JSON
$DATA_RAW = file_get_contents("php://input");
$DATA_OBJ = json_decode($DATA_RAW);

// Access $DB
global $DB;

// Generate required data
$data['userid'] = $DB->generate_id(10);
$data['date_created'] = date("Y-m-d H:i:s");

// Username
$data['username'] = trim($DATA_OBJ->username);
if (empty($data['username']) || strlen($data['username']) < 3 || !preg_match("/^[a-zA-Z0-9_]+$/", $data['username'])) {
    $Error .= "Username must be at least 3 characters and only contain letters, numbers, and underscores.<br>";
}

// First Name
$data['first_name'] = trim($DATA_OBJ->first_name);
if (empty($data['first_name']) || !preg_match("/^[a-zA-Z ]+$/", $data['first_name'])) {
    $Error .= "First name must contain only letters and spaces.<br>";
}

// Last Name
$data['last_name'] = trim($DATA_OBJ->last_name);
if (empty($data['last_name']) || !preg_match("/^[a-zA-Z ]+$/", $data['last_name'])) {
    $Error .= "Last name must contain only letters and spaces.<br>";
}

// Email
$data['email'] = trim($DATA_OBJ->email);
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $Error .= "Invalid email address.<br>";
}

// Phone Number
$data['phone_number'] = trim($DATA_OBJ->phone_number);
if (!preg_match("/^[0-9]{10,15}$/", $data['phone_number'])) {
    $Error .= "Phone number must be 10–15 digits.<br>";
}
// Location (optional but trimmed)
$data['location'] = trim($DATA_OBJ->location ?? '');
if (empty($data['location']) || strlen($data['location']) < 2) {
    $Error .= "Please enter a valid location (town or area).<br>";
}


// Role
$data['role'] = $DATA_OBJ->role ?? '';
if (!in_array($data['role'], ['farmer', 'agronomist'])) {
    $Error .= "Invalid or missing role selection.<br>";
}

// Password
$password1 = $DATA_OBJ->password ?? '';
$password2 = $DATA_OBJ->password2 ?? '';
if (empty($password1) || empty($password2)) {
    $Error .= "Both password fields are required.<br>";
} elseif ($password1 !== $password2) {
    $Error .= "Passwords do not match.<br>";
} elseif (strlen($password1) < 8) {
    $Error .= "Password must be at least 8 characters long.<br>";
} else {
    $data['password'] = password_hash($password1, PASSWORD_DEFAULT); // ✅ Secure hash
}

// Check for duplicate username
$checkUser = $DB->read("SELECT userid FROM users WHERE username = :username LIMIT 1", [
    'username' => $data['username']
]);
if ($checkUser) {
    $Error .= "Username is already taken.<br>";
}


// Check for duplicate email
$checkEmail = $DB->read("SELECT userid FROM users WHERE email = :email LIMIT 1", [
    'email' => $data['email']
]);
if ($checkEmail) {
    $Error .= "Email is already in use.<br>";
}



// Insert if valid
if ($Error == "") {
    // Generate email verification token
    $data['verification_token'] = bin2hex(random_bytes(16));
    $data['verified'] = 0;

$query = "INSERT INTO users (userid, username, first_name, last_name, email, role, phone_number, password, location, date_created, verification_token, verified) 
VALUES (:userid, :username, :first_name, :last_name, :email, :role, :phone_number, :password, :location, :date_created, :verification_token, :verified)";


    $result = $DB->write($query, $data);

if ($result) {
    // Send verification email using PHPMailer
  // Load PHPMailer classes
require __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'farmnet.kenya@gmail.com';
    $mail->Password   = 'cudawircfjwhgcoe';  // app password, no spaces
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('farmnet.kenya@gmail.com', 'Farmnet Kenya');
    $mail->addAddress($data['email'], $data['first_name']);
    
$verificationLink = "https://6cc556b1b2a8.ngrok-free.app/mychatui/includes/verify.php?token=" . $data['verification_token'];


    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email - Farmnet Kenya';
    $mail->Body    = "
        Hi {$data['first_name']},<br><br>
        Thank you for signing up on Farmnet Kenya.<br>
        Please click the link below to verify your email:<br>
        <a href='{$verificationLink}'>Verify Email</a><br><br>
        If you didn’t create this account, please ignore this email.
    ";

    $mail->send();
} catch (Exception $e) {
    file_put_contents("email_error.log", "Email sending failed: " . $mail->ErrorInfo . "\n", FILE_APPEND);
}


    // ✅ Use manually generated userid instead of last_insert_id
    $inserted_userid = $data['userid'];

    if ($data['role'] === 'agronomist') {
        $agronomistQuery = "INSERT INTO agronomists (userid, qualification, document_path, is_verified, created_at) 
                            VALUES (:userid, :qualification, :document_path, :is_verified, NOW())";

        $agro_data = [
            'userid' => $inserted_userid, // ✅ Correct ID
            'qualification' => null,
            'document_path' => null,
            'is_verified' => 0
        ];

        $agro_insert = $DB->write($agronomistQuery, $agro_data);

        if (!$agro_insert) {
            file_put_contents("agro_insert_fail.log", "Failed to insert agronomist: " . $inserted_userid . "\n", FILE_APPEND);
        }
    }

    $info->message = "Your account has been created.";
    $info->data_type = "info";
}

else {
        $info->message = "Failed to create account. Please try again.";
        $info->data_type = "error";
    }
} else {
    $info->message = $Error;
    $info->data_type = "error";
}

echo json_encode($info);
