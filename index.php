<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['userid'])) {
    file_put_contents("classes/debug_session.txt", "Session on dashboard (MISSING):\n" . print_r($_SESSION, true), FILE_APPEND);
    header("Location: login.php");
    exit;
} else {
    file_put_contents("classes/debug_session.txt", "Session on dashboard (OK):\n" . print_r($_SESSION, true), FILE_APPEND);
}

include('topbar.php'); 
include('includes/notifications.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Farmnet Kenya - Dashboard</title>

  <style>
    @font-face {
      font-family: 'Jura';
      src: url('ui/fonts/jura.ttf') format('truetype');
    }

    body {
      font-family: 'Jura', sans-serif;
      background-color: #ffffff;
      margin: 0;
      padding: 0;
    }

    #content_area {
      padding: 20px;
    }

    #weather_box {
      padding: 15px;
      background: #f0fff0;
      border-radius: 10px;
      margin-top: 20px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      max-width: 100%;
    }

    .weather_day {
      display: inline-block;
      width: 110px;
      text-align: center;
      margin-right: 10px;
      background-color: #ffffff;
      border-radius: 8px;
      padding: 10px;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
      font-size: 14px;
    }

    #assist_button {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: white;
      border: 2px solid #28a745;
      box-shadow: 0 0 12px rgba(0,0,0,0.2);
      cursor: pointer;
      padding: 0;
      z-index: 1000;
    }

    #assist_button img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    .section-box {
      margin-top: 30px;
      padding: 15px;
      background-color: #f9f9f9;
      border-left: 5px solid #4CAF50;
      border-radius: 6px;
      max-width: 600px;
    }

    .button-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    a.button-link {
      padding: 8px 12px;
      background-color: #4CAF50;
      color: white;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      white-space: nowrap;
      transition: background-color 0.2s ease;
    }

    a.button-link:hover {
      background-color: #45a049;
    }

    .bot-message {
  background: #f1f1f1;
  color: #333;
  padding: 6px 10px;
  margin: 6px 0;
  border-radius: 10px;
  max-width: 80%;
  text-align: left;
  float: left;
  clear: both;
}

.user-message {
  background: #dcf8c6;
  color: #000;
  padding: 6px 10px;
  margin: 6px 0;
  border-radius: 10px;
  max-width: 80%;
  text-align: right;
  float: right;
  clear: both;
}

#weather_alert {
    position: fixed;
    top: 70px; /* Positioned below notifications */
    right: -320px;
    background-color: #4CAF50;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    z-index: 999; /* Just below notifications */
    max-width: 280px;
    cursor: pointer;
    transition: right 0.4s ease-in-out;
}

#weather_alert.show {
    right: 20px;
}

#weather_alert .close-btn {
    position: absolute;
    top: 5px;
    right: 10px;
    font-weight: bold;
    font-size: 18px;
    color: white;
    cursor: pointer;
    display: none;
}

#weather_alert:hover .close-btn {
    display: inline;
}

/* Alert level colors */
#weather_alert.alert-calm {
    background-color: #4CAF50;
}

#weather_alert.alert-moderate {
    background-color: #FFC107;
    color: #000;
}

#weather_alert.alert-severe {
    background-color: #F44336;
}
  </style>
</head>
<body>

<?php include('availability_toggle.php'); ?>

<main id="content_area">
  <h1 id="welcome_name">Welcome to Farmnet Kenya!</h1>
  <p>This is your dashboard.</p>

  <!-- Weather Section -->
  
 <div id="weather_alert">
    <span class="close-btn" onclick="closeWeatherAlert()">✖</span>
    <div id="weather_alert_content">Loading weather alerts...</div>
</div>
  <div id="weather_box">
  
    <strong>Loading weather...</strong>
  </div>

  <!-- Dynamic Role-Based Content -->
  <div id="role_section" style="margin-top: 30px;"></div>
</main>

<!-- Assistance Button -->
<button id="assist_button">
  <img src="ui/icons/chatbot.png" alt="Farmbot">
</button>

<!-- Farmbot Chat Popup -->
<div id="farmbot_popup" style="display:none;position:fixed;bottom:90px;right:20px;width:300px;background:white;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.2);z-index:1000;">
  <div style="background:#4CAF50;color:#fff;padding:10px;border-top-left-radius:10px;border-top-right-radius:10px;">
    Farmbot Assistant
  </div>
  <div id="chat_area" style="height:220px;padding:10px;overflow-y:auto;font-size:14px;">
    <!-- messages appear here -->
  </div>
  <div style="padding:10px;border-top:1px solid #ccc;">
    <input type="text" id="chat_input" placeholder="Ask something..." onkeydown="handleEnter(event)" style="width:70%;padding:5px;border:1px solid #ccc;border-radius:5px;">
    <button onclick="sendToFarmbot()" style="padding:5px 10px;background:#4CAF50;color:#fff;border:none;border-radius:5px;">Send</button>
  </div>
</div>


<script>
let userLocation = null;
let fallbackCity = "Nairobi";
let userDBLocation = fallbackCity;

document.addEventListener("DOMContentLoaded", function () {
 fetch("api.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json"
  },
  body: JSON.stringify({ data_type: "user_info" }),
  credentials: "include"  // ← 🔥🔥 required for session access
})
  .then(res => res.json())
  .then(data => {
    if (!data.logged_in) {
      window.location.href = "login.php";
      return;
    }

    const fname = (data.user.first_name ?? 'User').charAt(0).toUpperCase() + (data.user.first_name ?? '').slice(1).toLowerCase();
    const welcome = document.getElementById("welcome_name");
    if (welcome) welcome.innerText = `Welcome to Farmnet Kenya, ${fname}`;

    const role = data.user.role ?? '';
    const roleSection = document.getElementById("role_section");

    // ✅ Farmer section
if (role === 'farmer') {
  fetch("includes/my_bookings_count.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" }
  })
  .then(res => res.json())
  .then(countData => {
    const bookings = countData.count ?? 0;
    roleSection.innerHTML = `
      <div class="section-box">
        <h3>🌾 Farmer Dashboard</h3>
        <p>Connect with verified agronomists.</p>
        <div class="button-row">
          <a href="contacts.php" class="button-link">📇 Find Agronomists</a>
        </div>
      </div>

      <div class="section-box">
        <h3>📝 My Bookings</h3>
        <p>You have made <strong>${bookings}</strong> service request(s).</p>
        <div class="button-row">
          <a href="my_requests.php" class="button-link">View Requests</a>
        </div>
      </div>`;
  });
}

    // ✅ Agronomist section
    else if (role === 'agronomist') {
      fetch("includes/check_verification.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ data_type: "check_verification" })
      })
      .then(res => res.json())
      .then(verifyData => {
        if (verifyData.is_verified == 1) {
    fetch("includes/request_counts.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" }
    })
    .then(res => res.json())
    .then(countData => {
      const requests = countData.count ?? 0;
      roleSection.innerHTML = `
        <div class="section-box">
          <h3>📬 Requests</h3>
          <p>You have <strong>${requests}</strong> incoming request(s).</p>
          <div class="button-row">
            <a href="agronomist_requests.php" class="button-link">View Requests</a>
          </div>
        </div>

    <div class="section-box">
      <h3>🧰 Services</h3>
      <p>Manage your offered services.</p>
      <div class="button-row">
        <a href="add_service.php" class="button-link">➕ Add Service</a>
        <a href="my_services.php" class="button-link">📄 My Services</a>
      </div>
    </div>`;
});
        } else if (verifyData.received == 1) {
          roleSection.innerHTML = `
            <div class="section-box">
              <h3>🕒 Verification Pending</h3>
              <p>Your application is being reviewed by the admin. Please wait for approval.</p>
              <span style="background:#ffc; padding:8px 12px; display:inline-block; border-radius:6px;">✅ Application Received</span>
            </div>`;
        } else {
          roleSection.innerHTML = `
            <div class="section-box">
              <h3>🛂 Verification Required</h3>
              <p>You need to be verified to start accepting requests.</p>
              <br>
              <a href="apply_verification.php" class="button-link">Apply for Verification</a>
            </div>`;
        }
      })
      .catch(err => {
        console.error("Verification check failed:", err);
        roleSection.innerHTML = "<p>Failed to load verification status. Try again later.</p>";
      });
    }

    if (data.user.city) {
      userDBLocation = data.user.city;
    }

    getWeatherWithLocation();
  })
  .catch(err => {
    console.error("User info fetch error:", err);
    alert("Something went wrong. Please try again.");
  });
});

function getWeatherWithLocation() {
  if ("geolocation" in navigator) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        userLocation = {
          lat: position.coords.latitude,
          lon: position.coords.longitude
        };
        fetchWeather(userLocation);
      },
      (err) => {
        console.warn("Geolocation denied or failed, using fallback.");
        fetchWeather({ city: userDBLocation });
      },
      { timeout: 8000 }
    );
  } else {
    console.warn("Geolocation not supported, using fallback.");
    fetchWeather({ city: userDBLocation });
  }
}

function handleEnter(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    sendToFarmbot();
  }
}


function fetchWeather(location) {
  fetch("includes/weather.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ location: location })
  })
  .then(res => res.json())
.then(data => {
  const box = document.getElementById("weather_box");
  const alertDiv = document.getElementById("weather_alert");

  if (data.error || !data.forecast) {
    box.innerHTML = "<strong>Unable to load weather data.</strong>";
    return;
  }

  const forecastDays = data.forecast.forecastday;
  const today = forecastDays[0];
  const todayCondition = forecastDays[0].day.condition.text.toLowerCase().trim();
  const locationName = data.location.name;

  // Determine alert level
  let alertClass = "alert-calm";
  let alertMessage = "✅ Weather looks good today.";

  if (todayCondition.includes("storm") || todayCondition.includes("thunder") || todayCondition.includes("heavy")) {
    alertClass = "alert-severe";
    alertMessage = "⚠️ Severe weather alert: " + today.day.condition.text;
  } else if (todayCondition.includes("rain") || todayCondition.includes("wind") || todayCondition.includes("drizzle")) {
    alertClass = "alert-moderate";
    alertMessage = "🌦️ Be prepared: " + today.day.condition.text;
  }

showWeatherAlert(alertMessage, alertClass);

  box.innerHTML = `<strong>🌦️ Weekly Weather Forecast - ${locationName}</strong><br><br>`;

  forecastDays.forEach(day => {
    const date = new Date(day.date);
    const shortDay = date.toLocaleDateString("en-US", { weekday: 'short' });
    const temp = day.day.avgtemp_c;
    const condition = day.day.condition.text;
    const icon = day.day.condition.icon;
console.log("Today's weather condition:", todayCondition);
console.log("Selected alert class:", alertClass);
    box.innerHTML += `
      <div class="weather_day">
        <strong>${shortDay}</strong><br>
        ${temp}°C<br>
        <img src="https:${icon}" width="40" /><br>
        ${condition}
      </div>
    `;
  });
})

  .catch(err => {
    console.error("Weather fetch error:", err);
    document.getElementById("weather_box").innerHTML = "<strong>Failed to load weather data.</strong>";
  });
}

document.getElementById("assist_button").addEventListener("click", function (event) {
  event.stopPropagation(); // Prevent this from closing the box
  const popup = document.getElementById("farmbot_popup");
  popup.style.display = popup.style.display === "none" ? "block" : "none";
});

function sendToFarmbot() {
  const input = document.getElementById("chat_input");
  const chatArea = document.getElementById("chat_area");
  const userText = input.value.trim();
  if (!userText) return;

  // Show user message
  chatArea.innerHTML += `<div class="user-message">${userText}</div>`;
  chatArea.scrollTop = chatArea.scrollHeight;

  fetch("includes/chatbot.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ message: userText })
  })
  .then(res => res.json())
  .then(data => {
    chatArea.innerHTML += `<div class="bot-message">${data.reply}</div>`;
    chatArea.scrollTop = chatArea.scrollHeight;
  });

  input.value = "";
}

// Minimize popup when clicking outside
document.addEventListener("click", function (event) {
  const popup = document.getElementById("farmbot_popup");
  const button = document.getElementById("assist_button");
  if (!popup.contains(event.target) && !button.contains(event.target)) {
    popup.style.display = "none";
  }
});

function closeWeatherAlert() {
    document.getElementById("weather_alert").style.right = "-320px";
}

function showWeatherAlert(message, alertClass) {
    let alertDiv = document.getElementById("weather_alert");
    let content = document.getElementById("weather_alert_content");
    
    // Update content and class
    content.innerHTML = message;
    alertDiv.className = alertClass;
    alertDiv.style.right = "20px";

    // Auto-hide after 8 seconds
    setTimeout(() => {
        alertDiv.style.right = "-320px";
    }, 8000);
    
    // Close on click
    alertDiv.addEventListener('click', function() {
        closeWeatherAlert();
    });
}
</script>

</body>
</html>
