<?php



ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../classes/autoload.php');

$DB = new Database();
$id = $_SESSION['userid'] ?? null;

$info = (object)[];
$mydata = "";

if ($id) {
    $sql = "SELECT * FROM users WHERE userid = :userid LIMIT 1";
    $result = $DB->read($sql, ['userid' => $id]);

    if (is_array($result) && isset($result[0])) {
        $data = (array)$result[0];

        $image_path = "ui/images/user.jpg";
        if (!empty($data['image']) && file_exists($data['image'])) {
            $image_path = $data['image'];
        }

       $mydata = '
<style>
    form {
        text-align: left;
        margin: auto;
        padding: 10px;
        width: 100%;
        max-width: 400px;
        font-size: 18px;
    }
    input[type="text"],
    input[type="password"],
    input[type="button"],
    .half_input {
        padding: 12px;
        width: 100%;
        margin: 12px 0;
        border-radius: 10px;
        border: solid 1px grey;
        box-sizing: border-box;
        font-size: 16px;
    }
    .half_row {
        display: flex;
        gap: 4%;
        margin-bottom: 12px;
    }
    .half_input {
        width: 48%;
    }
    input[type="button"] {
        cursor: pointer;
        background-color: #7CD375;
        color: white;
        border: none;
        font-size: 16px;
    }
    #error {
        text-align: center;
        padding: 0.5em;
        background-color: #ecaf91;
        color: white;
        display: none;
    }
    .profile-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .profile-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 20px;
    }
    .profile-section img {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #7CD375;
        margin-bottom: 15px;
    }
    .account-type {
        font-size: 20px;
        font-weight: bold;
        color: #102D03;
        margin-bottom: 15px;
        text-align: center;
    }
    .dragging {
        border: dashed 2px #aaa;
    }
</style>

<div id="error">error</div>
<br>
<div class="profile-container" style="animation: appear 1s ease">
    <div class="profile-section">
        <img 
            ondragover="handle_drag_and_drop(event)"
            ondrop="handle_drag_and_drop(event)" 
            ondragleave="handle_drag_and_drop(event)" 
            src="' . $image_path . '" 
            alt="User Image" 
        />
        <label for="change_image_input" id="change_image_button"
            style="background-color: green;  
                    color: white; 
                    padding: 10px 20px; 
                    border-radius: 10px; 
                    cursor: pointer; 
                    display: inline-block;">
            Change Image
        </label>
        <input id="change_image_input" type="file" onchange="upload_profile_image(this.files)" style="display: none;">
    </div>

    <form id="profile_form">
        <div class="account-type">
            Account Type: <span style="text-transform: capitalize;">' . $data['role'] . '</span>
        </div>

        <input type="text" name="username" placeholder="Username" value="' . htmlspecialchars($data['username']) . '" readonly><br>

        <div class="half_row">
            <input type="text" name="first_name" placeholder="First name" class="half_input" value="' . htmlspecialchars($data['first_name']) . '" readonly>
            <input type="text" name="last_name" placeholder="Last name" class="half_input" value="' . htmlspecialchars($data['last_name']) . '" readonly>
        </div>

        <input type="text" name="phone_number" placeholder="Phone number" value="' . htmlspecialchars($data['phone_number']) . '" readonly><br>
        <input type="text" name="email" placeholder="Email" value="' . htmlspecialchars($data['email']) . '" readonly><br>

        
       <!-- Plain text input for location when NOT editing -->
<input 
    type="text" 
    name="location_text" 
    id="location_text" 
    value="' . htmlspecialchars($data['location']) . '" 
    readonly 
    style="padding:12px; margin:10px 0; border-radius:8px; border:solid 1px grey; font-size:16px;"
/>


<!-- Dropdown for location when editing, hidden initially -->
<select 
    name="location" 
    id="location_select" 
    style="display:none; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #ccc; font-size:16px;"
>
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
  <option value="Muranga">Muranga</option>
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


        <div style="text-align: center;">
            <input type="button" id="edit_btn" value="Edit Profile" onclick="enable_edit()">
            <input type="button" id="save_settings_button" value="Save Changes" onclick="collect_data(event)" style="display:none;">
        </div>
    </form>
</div>
';
if (empty($data['verified']) || $data['verified'] == 0) {
    $mydata .= '
    <div style="text-align:center; margin-top:10px;">
      <form method="post" action="send_verification_email.php" style="display:inline;">
          <input type="hidden" name="userid" value="' . htmlspecialchars($data['userid']) . '">
          <button type="submit" style="
              background:#7CD375;
              color:#fff;
              border:none;
              padding:10px 20px;
              border-radius:8px;
              cursor:pointer;
              font-size:16px;
          ">Send Verification Email</button>
      </form>
    </div>
    ';
}
    } else {
        $mydata = "<div style='padding:20px;text-align:center;'>No user data found.</div>";
    }
} else {
    $mydata = "<div style='padding:20px;text-align:center;'>User not logged in.</div>";
}

$info->message = $mydata;
$info->data_type = "profile";
echo json_encode($info);
