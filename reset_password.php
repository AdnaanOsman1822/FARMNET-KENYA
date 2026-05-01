<?php
$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Farmnet Kenya - Reset Password</title>
<style>
  body {
    font-family: 'Jura', sans-serif;
    background-color: #f4f9f4;
    margin: 0;
    padding: 0;
  }
  header {
    background-color: #2e7d32;
    color: white;
    padding: 15px;
    text-align: center;
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 1px;
  }
  #container {
    max-width: 420px;
    margin: 40px auto;
    padding: 30px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  }
  h1 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 20px;
  }
  .alert {
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
  }
  .alert.error {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #c62828;
  }
  .alert.success {
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #2e7d32;
  }
  input[type=password] {
    width: 100%;
    padding: 12px;
    margin: 8px 0 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
  }
  button {
    width: 100%;
    padding: 12px;
    background-color: #2e7d32;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
  }
  button:hover {
    background-color: #256528;
  }
</style>
</head>
<body>
<header>FARMNET KENYA</header>
<div id="container">
  <h1>Reset Password</h1>

  <?php if ($error): ?>
    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <form method="POST" action="includes/update_password.php">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
    <input type="password" name="password" placeholder="New Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <button type="submit">Update Password</button>
  </form>
</div>
</body>
</html>
