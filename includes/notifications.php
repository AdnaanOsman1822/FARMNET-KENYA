<?php

if (!isset($_SESSION['userid'])) {
    die;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <style>
        #notification {
            position: fixed;
            top: 20px;
            right: -320px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            max-width: 280px;
            cursor: pointer;
            transition: right 0.4s ease-in-out;
        }

        #notification.show {
            right: 20px;
        }

        #notification .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            font-weight: bold;
            font-size: 18px;
            color: white;
            cursor: pointer;
            display: none;
        }

        #notification:hover .close-btn {
            display: inline;
        }
    </style>
</head>
<body>

<div id="notification">
    <span class="close-btn" onclick="closeNotification()">✖</span>
    <div id="notification_content">No new notifications</div>
</div>

<script>
function closeNotification() {
    document.getElementById("notification").style.right = "-300px";
}

function showNotification(sender_name, message, sender_id) {
    let n = document.getElementById("notification");
    let content = document.getElementById("notification_content");

    content.innerHTML = `<strong>${sender_name}</strong>: ${message}<br>
                         <a href="chats.php" style="color:white;text-decoration:underline;">Open Chat</a>`;
    n.classList.add("show");

    setTimeout(() => {
        n.classList.remove("show");
    }, 5000);
}

setInterval(() => {
    fetch("includes/check_new_messages.php")
    .then(res => res.json())
    .then(data => {
        if (data.new_message && data.sender_id) {
            showNotification(data.sender_name, data.message, data.sender_id);
        }
    });
}, 5000);
</script>

</body>
</html>
