<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup - Farmnet Kenya</title>
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
      max-width: 450px;
      margin: 40px auto;
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
    input[type="email"],
    input[type="tel"],
    input[type="button"] {
      padding: 12px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    .half_input_group {
      display: flex;
      gap: 4%;
    }

    .half_input_group input {
      flex: 1;
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

    input[type="radio"] {
      transform: scale(1.2);
      margin-right: 10px;
      accent-color: #7CD375;
      cursor: pointer;
    }

    .role_group {
      margin: 10px 0;
      font-size: 16px;
    }

    .role_group label {
      margin-right: 20px;
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

    .login-link {
      margin-top: 10px;
      text-align: center;
    }

    .login-link a {
      color: #102D03;
      text-decoration: none;
      font-weight: bold;
    }

    .login-link a:hover {
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
      <p>Sign Up</p>
    </div>

    <div id="error">Some error message</div>

    <form id="signup_form">
      <input type="text" name="username" placeholder="Username">
      
      <div class="half_input_group">
        <input type="text" name="first_name" placeholder="First Name">
        <input type="text" name="last_name" placeholder="Last Name">
      </div>

      <input type="tel" name="phone_number" placeholder="Phone Number">
      <input type="email" name="email" placeholder="Email">
      <select name="location" class="half_input" style="padding:12px; margin:10px 0; border-radius:8px; border:1px solid #ccc; font-size:16px;">
          <option value="">-- Select County --</option>
          <option value="Baringo">Baringo</option>
          <option value="Bomet">Bomet</option>
          <option value="Bungoma">Bungoma</option>
          <option value="Busia">Busia</option>
          <option value="Elgeyo-Marakwet">Elgeyo-Marakwet</option>
          <option value="Embu">Embu</option>
          <option value="Garissa">Garissa</option>
          <option value="Homa Bay">Homa Bay</option>
          <option value="Isiolo">Isiolo</option>
          <option value="Kajiado">Kajiado</option>
          <option value="Kakamega">Kakamega</option>
          <option value="Kericho">Kericho</option>
          <option value="Kiambu">Kiambu</option>
          <option value="Kilifi">Kilifi</option>
          <option value="Kirinyaga">Kirinyaga</option>
          <option value="Kisii">Kisii</option>
          <option value="Kisumu">Kisumu</option>
          <option value="Kitui">Kitui</option>
          <option value="Kwale">Kwale</option>
          <option value="Laikipia">Laikipia</option>
          <option value="Lamu">Lamu</option>
          <option value="Machakos">Machakos</option>
          <option value="Makueni">Makueni</option>
          <option value="Mandera">Mandera</option>
          <option value="Marsabit">Marsabit</option>
          <option value="Meru">Meru</option>
          <option value="Migori">Migori</option>
          <option value="Mombasa">Mombasa</option>
          <option value="Murang'a">Murang'a</option>
          <option value="Nairobi">Nairobi</option>
          <option value="Nakuru">Nakuru</option>
          <option value="Nandi">Nandi</option>
          <option value="Narok">Narok</option>
          <option value="Nyamira">Nyamira</option>
          <option value="Nyandarua">Nyandarua</option>
          <option value="Nyeri">Nyeri</option>
          <option value="Samburu">Samburu</option>
          <option value="Siaya">Siaya</option>
          <option value="Taita-Taveta">Taita-Taveta</option>
          <option value="Tana River">Tana River</option>
          <option value="Tharaka-Nithi">Tharaka-Nithi</option>
          <option value="Trans Nzoia">Trans Nzoia</option>
          <option value="Turkana">Turkana</option>
          <option value="Uasin Gishu">Uasin Gishu</option>
          <option value="Vihiga">Vihiga</option>
          <option value="Wajir">Wajir</option>
          <option value="West Pokot">West Pokot</option>
        </select>



      <div class="role_group">
        Role:<br>
        <label><input type="radio" name="role" value="farmer"> Farmer</label>
        <label><input type="radio" name="role" value="agronomist"> Agronomist</label>
      </div>

      <input type="password" name="password" placeholder="Password">
      <input type="password" name="password2" placeholder="Retype Password">

      <input type="button" value="Sign Up" id="signup_button">

      <div class="login-link">
        <a href="login.php">Already have an account? Login</a>
      </div>
    </form>
  </div>

  <script>
    function _(el) {
      return document.getElementById(el);
    }

    const signup_button = _("signup_button");
    signup_button.addEventListener("click", collect_data);

function collect_data(e) {
  signup_button.disabled = true;
  signup_button.value = "Loading...";

  const form = _("signup_form");
  const inputs = form.getElementsByTagName("input");

  let data = {};
  for (let i = 0; i < inputs.length; i++) {
    const key = inputs[i].name;
    const val = inputs[i].value;

    if (key === "role" && inputs[i].checked) {
      data[key] = val;
    } else if (key !== "role" && key !== "") {
      data[key] = val;
    }
  }

  // ✅ Add this line to collect location value
  data["location"] = form.querySelector("select[name='location']").value;

  send_data(data, "signup");
}


    function send_data(data, type) {
      const xml = new XMLHttpRequest();
      xml.onload = function () {
        if (xml.readyState === 4 || xml.status === 200) {
          handle_result(xml.responseText);
          signup_button.disabled = false;
          signup_button.value = "Sign Up";
        }
      };
      data.data_type = type;
      xml.open("POST", "api.php", true);
      xml.send(JSON.stringify(data));
    }

    function handle_result(result) {
      const data = JSON.parse(result);
      if (data.data_type === "info") {
        window.location = "index.php";
      } else {
        const error = _("error");
        error.innerHTML = data.message;
        error.style.display = "block";
      }
    }
  </script>

</body>
</html>
