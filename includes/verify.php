<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../classes/autoload.php";
global $DB;

$token = $_GET['token'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Farmnet Kenya - Email Verification</title>
<style>
  @font-face {
    font-family: 'Jura';
    src: url('../ui/fonts/jura.ttf') format('truetype');
  }
  body {
    background-color: #f4fef4;
    font-family: 'Jura', sans-serif;
    color: #102D03;
    display: flex;
    height: 100vh;
    margin: 0;
    justify-content: center;
    align-items: center;
  }
  #container {
    background: white;
    border: 2px solid #7CD375;
    padding: 30px 40px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    text-align: center;
    max-width: 400px;
  }
  h1 {
    color: #102D03;
    margin-bottom: 15px;
    font-size: 28px;
  }
  p {
    font-size: 18px;
    margin-bottom: 25px;
  }
  a.button {
    display: inline-block;
    padding: 12px 30px;
    font-weight: bold;
    background-color: #7CD375;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: background-color 0.3s ease;
  }
  a.button:hover {
    background-color: #5fae41;
  }
  .success {
    color: #2d5a09;
  }
  .error {
    color: #b42318;
  }
</style>
</head>
<body>
  <div id="container">
<?php
if (!$token) {
    echo '<h1 class="error">Invalid verification link</h1>';
    echo '<p>Please check your email and try again.</p>';
    echo '<a href="../login.php" class="button">Go to Login</a>';
    exit;
}

$query = "SELECT userid, verified FROM users WHERE verification_token = :token LIMIT 1";
$user = $DB->read($query, ['token' => $token]);

if ($user && count($user) > 0) {
    $user = $user[0];
    if ($user->verified == 1) {
        echo '<h1 class="success">Email Already Verified</h1>';
        echo '<p>Your email has already been verified.</p>';
        echo '<a href="../index.php" class="button">Login Now</a>';
    } else {
        $DB->write("UPDATE users SET verified = 1, verification_token = NULL WHERE userid = :userid", [
            'userid' => $user->userid
        ]);
        echo '<h1 class="success">✅ Email Verified Successfully!</h1>';
        echo '<p>Thank you for verifying your email. You can now log in.</p>';
        echo '<a href="../index.php" class="button">Login Now</a>';
    }
} else {
    echo '<h1 class="error">Invalid or Expired Link</h1>';
    echo '<p>This verification link is not valid anymore.</p>';
    echo '<a href="../index.php" class="button">Go to Login</a>';
}
?>
  </div>
</body>
</html>
