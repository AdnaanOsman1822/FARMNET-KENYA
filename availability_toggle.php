<?php
require_once 'classes/autoload.php';


$DB = new Database();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userid = $_SESSION['userid'] ?? null;
$available = null;

// Only for verified agronomists
if ($userid) {
    $query = "SELECT available FROM agronomists WHERE userid = :id AND is_verified = 1 LIMIT 1";
    $result = $DB->read($query, ['id' => $userid]);
    if ($result) {
        $available = $result[0]->available;
    }
}
?>

<?php if ($available !== null): ?>
<style>
#availability_wrapper {
  position: absolute;
  top: 110px; /* ← adjusted from 70px */
  right: 20px;
  background: #b7e9b7;
  padding: 10px 15px;
  border-radius: 20px;
  box-shadow: 0 0 6px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  z-index: 999;
  transition: background-color 0.4s ease;
}

#availabilityText {
  transition: color 0.4s ease;
}
#availability_wrapper span {
  margin-right: 10px;
  font-weight: bold;
  color: #444;
}
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
}
.switch input {display:none;}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 26px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 20px; width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #4CAF50;
}
input:checked + .slider:before {
  transform: translateX(24px);
}
</style>

<div id="availability_wrapper" style="background-color: <?= $available ? '#b7e9b7' : '#fcdede' ?>;">

 <span id="availabilityText" style="color: <?= $available ? '#2e7d32' : '#b71c1c' ?>;">
  <?= $available ? "Available" : "Unavailable" ?>
</span>

  <label class="switch">
    <input type="checkbox" id="availabilitySwitch" <?= $available ? "checked" : "" ?>>
    <span class="slider"></span>
  </label>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const switchEl = document.getElementById("availabilitySwitch");
  if (switchEl) {
    switchEl.addEventListener("change", function () {
      let available = this.checked ? 1 : 0;
      document.getElementById("availabilityText").innerText = this.checked ? "Available" : "Unavailable";
      document.getElementById("availabilityText").style.color = this.checked ? "#2e7d32" : "#b71c1c";
      document.getElementById("availability_wrapper").style.backgroundColor = this.checked ? "#b7e9b7" : "#fcdede";
      fetch("includes/toggle_availability.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ available: available })
      })
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert("❌ Failed to update availability.");
          this.checked = !this.checked;
        }
      })
      .catch(() => {
        alert("⚠️ Error. Try again.");
        this.checked = !this.checked;
      });
    });
  }
});
</script>
<?php endif; ?>
