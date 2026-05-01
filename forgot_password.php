<?php
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Farmnet Kenya - Forgot Password</title>
<style>
  @font-face {
    font-family: 'Jura';
    src: url('ui/fonts/jura.ttf') format('truetype');
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
  h2 {
    margin-top: 0;
    color: #7CD375;
    font-size: 20px;
  }
  p {
    font-size: 18px;
    margin-bottom: 25px;
  }
  input, button {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-family: 'Jura', sans-serif;
    font-size: 16px;
  }
  button {
    background-color: #7CD375;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
  }
  button:hover {
    background-color: #5fae41;
  }
  .message {
    margin-top: 15px;
    font-size: 14px;
  }
  .success { color: #2d5a09; }
  .error { color: #b42318; }
</style>
</head>
<body>
  <div id="container">
    <h2>FARMNET KENYA</h2>
    <h1>Forgot Password</h1>
    <p>Enter your email address to reset your password.</p>

    <?php if ($message): ?>
      <div class="message <?= htmlspecialchars($status) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="includes/send_password_reset.php">
      <input type="email" name="email" placeholder="Your Email" required />
      <button type="submit">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
