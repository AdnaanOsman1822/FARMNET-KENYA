<?php
// FK-themed simple form to enter email and send verification email

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Farmnet Kenya - Send Verification Email</title>
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
    width: 90%;
  }
  h1 {
    color: #102D03;
    margin-bottom: 20px;
    font-size: 28px;
  }
  input[type="email"] {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 18px;
    margin-bottom: 20px;
    box-sizing: border-box;
  }
  button {
    background-color: #7CD375;
    color: white;
    padding: 12px 25px;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    cursor: pointer;
  }
  button:hover {
    background-color: #5fae41;
  }
  #message {
    margin-top: 20px;
    font-size: 16px;
  }
</style>
</head>
<body>

<div id="container">
  <h1>Resend Verification Email</h1>
  <form id="verification_form">
    <input type="email" id="email" name="email" placeholder="Enter your email" required />
    <button type="submit">Send Verification Email</button>
  </form>
  <div id="message"></div>
</div>

<script>
document.getElementById('verification_form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const email = document.getElementById('email').value.trim();
  const msgDiv = document.getElementById('message');
  msgDiv.textContent = '';

  if (!email) {
    msgDiv.textContent = 'Please enter your email address.';
    return;
  }

  try {
    const res = await fetch('includes/send_verification_email.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({email})
    });
    const data = await res.json();
    msgDiv.textContent = data.message;
    msgDiv.style.color = data.status === 'success' ? 'green' : 'red';
  } catch (error) {
    msgDiv.textContent = 'An error occurred. Please try again later.';
    msgDiv.style.color = 'red';
  }
});
</script>

</body>
</html>
