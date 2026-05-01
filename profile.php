<?php 
include('topbar.php');
include('includes/notifications.php');
?>

<main id="content_area">
    <!-- Profile content will be loaded here -->
</main>

<script>
  // Highlight the profile nav link
  document.querySelectorAll(".nav-link").forEach(link => link.classList.remove("active"));
  const profileNav = document.querySelector('[data-page="profile"]');
  if (profileNav) profileNav.classList.add("active");

  // Load the profile view on page load
  document.addEventListener("DOMContentLoaded", () => {
    load_profile();
    
  });

  // Load profile data
  function load_profile() {
    send_data({ data_type: "profile" });
  }

  // Collect form data and submit to backend
function collect_data(e, data_type = "save_profile") {
  if (e && e.preventDefault) e.preventDefault();

  const form = document.querySelector("#profile_form");
  if (!form) return;

  let inputs = form.querySelectorAll("input");
  let data = {};

  inputs.forEach(input => {
    data[input.name] = input.value;
  });

  // ✅ Add this to include the location select value
  const locationSelect = document.getElementById("location_select");
  if (locationSelect) {
    data.location = locationSelect.value;
  }

  data.data_type = data_type;
  send_data(data);
}


  // Send AJAX request
  function send_data(data = {}) {
    let xml = new XMLHttpRequest();
    xml.onload = function () {
      if (xml.readyState == 4 && xml.status == 200) {
        try {
          handle_result(xml.responseText);
        } catch (err) {
          console.error("Invalid JSON:", xml.responseText);
        }
      }
    };
    xml.open("POST", "api.php", true);
    xml.send(JSON.stringify(data));
  }

  // Handle response from server
  function handle_result(result) {
    if (result.trim() !== "") {
      let obj = JSON.parse(result);
      if (obj.data_type === "profile") {
        document.querySelector("#content_area").innerHTML = obj.message;
      } else if (obj.data_type === "save_profile") {
        alert(obj.message);
        load_profile();
      }
    }
  }

  // Upload profile image
  function upload_profile_image(files) {
    let form = new FormData();
    form.append("file", files[0]);
    form.append("data_type", "change_profile_image");

    let xml = new XMLHttpRequest();
    xml.onload = function () {
      if (xml.readyState == 4 && xml.status == 200) {
        try {
          let obj = JSON.parse(xml.responseText);
          if (obj && obj.data_type === "change_profile_image") {
            
            load_profile();
          }
        } catch (err) {
          console.error("Invalid image upload response:", xml.responseText);
        }
      }
    };

    xml.open("POST", "uploader.php", true);
    xml.send(form);
  }

  // Handle drag and drop image upload
  function handle_drag_and_drop(e) {
    if (e.type === "dragover") {
      e.preventDefault();
      e.target.classList.add("dragging");
    } else if (e.type === "dragleave" || e.type === "drop") {
      e.preventDefault();
      e.target.classList.remove("dragging");
    }

    if (e.type === "drop") {
      let files = e.dataTransfer.files;
      if (files.length > 0) {
        upload_profile_image(files);
      }
    }
  }

// Enable form input editing
function enable_edit() {
  const form = document.getElementById("profile_form");
  if (!form) return;

  const inputs = form.querySelectorAll("input");

  inputs.forEach(input => {
    if (input.name !== "username" && input.name !== "password2" && input.type !== "button") {
      input.removeAttribute("readonly");
    }
  });

  // Handle location field toggle
  const locationText = document.getElementById("location_text");
  const locationSelect = document.getElementById("location_select");

  if (locationText && locationSelect) {
    locationText.style.display = "none";
    locationSelect.style.display = "block";

    // Optional: sync select value with input (in case not already set)
    locationSelect.value = locationText.value;
  }

  const editBtn = document.getElementById("edit_btn");
  const saveBtn = document.getElementById("save_settings_button");

  if (editBtn) editBtn.style.display = "none";
  if (saveBtn) saveBtn.style.display = "inline-block";
}



  // Load user location on page load
    window.addEventListener("load", function () {
    const locationSelect = document.getElementById("location_select");
    if (locationSelect && "<?= $user_data->location ?>") {
      locationSelect.value = "<?= $user_data->location ?>";
    }
  });
</script>

</body>
</html>
