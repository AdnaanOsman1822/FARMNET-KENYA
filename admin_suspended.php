<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}

$userid = $_SESSION['userid'];

include("admin_topbar.php");

$check = $DB->read("SELECT role FROM users WHERE userid = :userid LIMIT 1", ['userid' => $userid]);
if (!$check || $check[0]->role !== 'admin') {
    echo "Access denied.";
    die;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Suspended Accounts</title>
    <style>
        body {
            font-family: 'Jura', sans-serif;
            background: #fff8f8;
            margin: 0;
            padding: 0;
        }

        h2 {
            color: #c0392b;
        }

        .suspended-box {
            background: #fff;
            border-left: 6px solid #e74c3c;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .reactivate-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>



    <br><h2>Suspended Users</h2>
    <div id="suspend_list">Loading...</div>

    <script>
    function fetchSuspended() {
        fetch("includes/get_suspended.php", {
            method: "POST"
        })
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById("suspend_list");
            list.innerHTML = "";

            if (data.length === 0) {
                list.innerHTML = "<p>No suspended users found.</p>";
                return;
            }

            data.forEach(user => {
                list.innerHTML += `
                    <div class="suspended-box">
                        <strong>${user.first_name} ${user.last_name}</strong> - ${user.role}<br>
                        📧 ${user.email}<br>
                        <button class="reactivate-btn" onclick="reactivate('${user.userid}')">🔓 Reactivate</button>
                    </div>
                `;
            });
        });
    }

    function reactivate(userid) {
        fetch("includes/reactivate_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ userid: userid })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            fetchSuspended();
        });
    }

    fetchSuspended();
    </script>

</body>
</html>
