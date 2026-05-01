<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Farmnet Kenya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Jura Font -->
  <style>
    @font-face {
      font-family: 'Jura';
      src: url('ui/fonts/jura.ttf') format('truetype');
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Jura', sans-serif;
    }

    body {
      background-color: #f4fef4;
    }

    #wrapper {
      max-width: 400px;
      margin: 60px auto;
      padding: 20px;
      background-color: white;
      border: 2px solid #7CD375;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    #header {
      text-align: center;
      margin-bottom: 20px;
    }

    #header h1 {
      color: #102D03;
      font-size: 32px;
    }

    #header p {
      font-size: 18px;
      color: #7CD375;
      margin-top: 5px;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    input[type="text"],
    input[type="password"],
    input[type="button"] {
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    input[type="button"] {
      background-color: #102D03;
      color: #fff;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    input[type="button"]:hover {
      background-color: #163f05;
    }

    #error {
      text-align: center;
      background-color: #ecaf91;
      color: white;
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 10px;
      display: none;
    }

    .signup-link {
      margin-top: 10px;
      text-align: center;
    }

    .signup-link a {
      color: #102D03;
      text-decoration: none;
      font-weight: bold;
    }

    .signup-link a:hover {
      text-decoration: underline;
    }

    @media(max-width: 500px) {
      #wrapper {
        margin: 20px;
        padding: 15px;
      }
    }
  </style>
</head>
<body>

  <div id="wrapper">
    <div id="header">
      <h1>Farmnet Kenya</h1>
      <p>Login</p>
    </div>
<?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
    <p style="color: green; text-align: center;">
        Your password has been reset. Please log in.
    </p>
<?php endif; ?>

    <div id="error">Some error message</div>

    <form id="login_form">
      <input type="text" name="email" placeholder="Email">
      <input type="password" name="password" placeholder="Password">

    <input type="button" value="Login" id="login_button">

    <div style="text-align: center; margin-top: 10px;">
      <a href="forgot_password.php" style="color: #7CD375; font-weight: bold; text-decoration: none;">
        Forgot Password?
      </a>
    </div>

    <div class="signup-link">
      <a href="signup.php">Don't have an account? Sign Up</a>
    </div>


  <script>
    function _(el) {
      return document.getElementById(el);
    }

    const login_button = _("login_button");
    login_button.addEventListener("click", collect_data);

    function collect_data(e) {
      login_button.disabled = true;
      login_button.value = "Loading...";

      const form = _("login_form");
      const inputs = form.getElementsByTagName("input");

      let data = {};
      for (let i = 0; i < inputs.length; i++) {
        const key = inputs[i].name;
        if (key !== "") {
          data[key] = inputs[i].value;
        }
      }

      send_data(data, "login");
    }

    function send_data(data, type) {
      const xml = new XMLHttpRequest();
      xml.onload = function () {
        if (xml.readyState === 4 || xml.status === 200) {
          handle_result(xml.responseText);
          login_button.disabled = false;
          login_button.value = "Login";
        }
      };
      data.data_type = type;
      xml.open("POST", "api.php", true);
      xml.send(JSON.stringify(data));
    }

    function handle_result(result) {
      const data = JSON.parse(result);
      if (data.data_type === "info") {
        // ✅ Redirect based on user role
        if (data.role === "admin") {
          window.location = "admin_dashboard.php";
        } else {
          window.location = "index.php";
        }
      } else {
        const error = _("error");
        error.innerHTML = data.message;
        error.style.display = "block";
      }
    }
  </script>

</body>
</html>
