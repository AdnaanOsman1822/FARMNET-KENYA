<?php
date_default_timezone_set('Africa/Nairobi');
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}
$them = ""; // Prevent undefined variable warning
?>
<!DOCTYPE html>
<html>
<head>
    <title>Farmnet Kenya - Chats</title>
    <style>
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    overflow: hidden;
    font-family: Arial, sans-serif;
    background-color: #f1f1f1;
}

#container {
    display: flex;
    height: calc(100vh - 100px);
}

/* Left Panel (Chat List) */
#left_panel {
    width: 30%;
    background-color: #ffffff;
    border-right: 1px solid #ccc;
    display: flex;
    flex-direction: column;
}

#search_chats {
    padding: 12px;
    margin: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

#chat_contacts {
    flex: 1;
    overflow-y: auto;
}

.chat_user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    background-color: #fff;
    transition: background 0.2s;
}

.chat_user:hover {
    background-color: #f0fff0;
}

.chat_user img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.chat_user .info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.chat_user .name {
    font-weight: bold;
    font-size: 15px;
    color: #333;
}

.chat_user .username, .chat_user .last_message, .chat_user .last_message_time {
    font-size: 13px;
    color: gray;
}

/* Right Panel (Messages) */
#right_panel {
    width: 70%;
    display: flex;
    flex-direction: column;
    background-color: #e5ffe5;
}

.chat_area {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
}

/* Message Input Area */
#message_input_area {
    padding: 10px;
    background-color: #fff;
    border-top: 1px solid #ccc;
    display: flex;
    align-items: center;
    gap: 10px;
}

#message_text {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
}

#send_btn {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
}

#file_label {
    font-size: 13px;
    color: #666;
}



.message-container {
    display: flex;
    margin: 5px 10px;
}

.message-container.left {
    justify-content: flex-start;
}

.message-container.right {
    justify-content: flex-end;
}

.message-wrapper {
    max-width: 70%;
    position: relative;
}

.message-bubble {
    background-color: #ffffff;
    padding: 10px;
    border-radius: 10px;
    position: relative;
    word-wrap: break-word;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.message-container.right .message-bubble {
    background-color: #dcf8c6; /* WhatsApp-style green for sender */
}

.message-meta {
    font-size: 11px;
    color: gray;
    text-align: right;
    margin-top: 5px;
}

/* Dropdown */
.options-icon {
    position: absolute;
    top: 5px;
    right: 8px;
    font-size: 16px;
    color: #888;
    cursor: pointer;
    display: none;
    z-index: 2;
}

.message-bubble:hover .options-icon {
    display: block;
}

.options-menu {
    position: absolute;
    top: 25px;
    right: 5px;
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    display: none;
    z-index: 3;
}

.options-menu div {
    padding: 8px 12px;
    cursor: pointer;
}

.options-menu div:hover {
    background-color: #eee;
}


.active-chat {
    background: #d4f9c3 !important; /* FK green */
    border-left: 5px solid #4CAF50;
}




    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div id="container">
    <!-- LEFT -->
    <div id="left_panel">
        <input type="text" id="search_chats" placeholder="Search chats...">
        <div id="chat_contacts"></div>
    </div>

    <!-- RIGHT -->
    <div id="right_panel">

        <!-- Top Bar -->
        <div id="chat_topbar" style="padding: 10px; background: #d4f5d4; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #ccc;">
            <a id="receiver_link" href="#">
                <img id="receiver_image" src="ui/images/user.jpg" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            </a>
            <a id="receiver_name_link" href="#" style="text-decoration: none; color: #333; font-weight: bold; font-size: 16px;"></a>
        </div>

        <!-- Messages -->
        <div id="chat_messages" class="chat_area">
            <div style="color:gray;text-align:center;margin-top:50px;">No chat selected.</div>
        </div>

        <!-- Input -->
        <div id="message_input_area">
            <input type="file" id="file_input" style="display:none;" onchange="handleFileSelect(this)">
            <img src="ui/icons/file.png" 
                onclick="document.getElementById('file_input').click()" 
                title="Attach file"
                style="width: 24px; height: 24px; cursor: pointer;">
            <span id="file_label"></span>

            <input type="text" id="message_text" placeholder="Type your message..." onkeydown="checkEnter(event)">
            <button id="send_btn">Send</button>
        </div>
    </div>
</div>

<input type="hidden" id="receiver_id" value="<?= $them ?>">


<script>
let CURRENT_USER = null;

function fetchChats(search = "") {
    fetch("includes/chat_users.php", {
        method: "POST",
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ search })
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("chat_contacts").innerHTML = data;
    });
}

function fetchMessages(userid = null) {
    const target = userid || CURRENT_USER;
    if (!target) return;

    fetch("api.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ data_type: "chats", userid: target })
    })
    .then(res => res.text())
    .then(data => {
        const chatArea = document.getElementById("chat_messages");
        let nearBottom = chatArea.scrollHeight - chatArea.scrollTop - chatArea.clientHeight < 100;

        chatArea.innerHTML = data;

        if (nearBottom) {
            setTimeout(() => {
                chatArea.scrollTo(0, chatArea.scrollHeight);
            }, 100);
        }
    });
}



function sendMessage() {
    let fileInput = document.getElementById("file_input");
    let receiver = document.getElementById("receiver_id").value;
    let message = document.getElementById("message_text").value.trim();
        if (message === "" && fileInput.files.length === 0) 
            {
            return; // Block empty messages with no file
        }
    

    let formData = new FormData();
    formData.append("receiver", receiver);
    formData.append("message", message);

    if (fileInput.files.length > 0) {
        formData.append("file", fileInput.files[0]);
    }

    fetch("api.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.message === "Message sent") {
            document.getElementById("message_text").value = "";
            fileInput.value = "";
            document.getElementById("file_label").textContent = "";
            fetchMessages();
            setTimeout(() => {
            let mp = document.getElementById("chat_messages");
            mp.scrollTop = mp.scrollHeight;
            }, 300);
            fetchChats();
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error("Send error:", err);
        alert("Failed to send message.");
    });
}

function checkEnter(e) {
    if (e.keyCode === 13) {
        sendMessage();
    }
}

function startChat(userid) {
    CURRENT_USER = userid;
    document.querySelectorAll(".user").forEach(el => {
    el.classList.remove("active-chat");
    });
    let selected = document.querySelector(`.user[data-id='${userid}']`);
    if (selected) selected.classList.add("active-chat");

    document.getElementById("receiver_id").value = userid;
    loadUserInfo(userid);
    fetchMessages(userid);
    document.getElementById("message_input_area").style.display = "flex";
    document.getElementById("message_text").value = "";
    document.getElementById("file_input").value = "";
    document.getElementById("file_label").textContent = "";

    let dot = document.getElementById("unseen_dot_" + userid);
    if (dot) dot.style.display = "none";

    fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ data_type: "mark_seen", userid })
    });
}

function loadUserInfo(userid) {
    fetch("includes/user_info.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ userid })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const user = data.user;

            // ✅ Profile picture fallback logic
            let image = user.image && user.image.trim() !== "" ? user.image : "ui/images/user.jpg";
            document.getElementById("receiver_image").src = image;

            // ✅ Set receiver's name
            document.getElementById("receiver_name_link").textContent = `${user.first_name} ${user.last_name}`;
            
            // ✅ Set profile link
            let profileUrl = "profile_view.php?userid=" + user.userid;
            document.getElementById("receiver_link").href = profileUrl;
            document.getElementById("receiver_name_link").href = profileUrl;
        } else {
            console.warn("User info not loaded:", data.message);
        }
    })
    .catch(err => console.error("loadUserInfo error:", err));
}


function handleFileSelect(input) {
    if (input.files.length > 0) {
        document.getElementById("file_label").textContent = input.files[0].name;
    } else {
        document.getElementById("file_label").textContent = "";
    }
}

// Initial Setup
document.getElementById("send_btn").addEventListener("click", sendMessage);
document.getElementById("search_chats").addEventListener("input", function () {
    fetchChats(this.value);
});
fetchChats();

let chatWith = localStorage.getItem("chat_with");
if (chatWith) {
    startChat(chatWith);
    localStorage.removeItem("chat_with");
}

setInterval(() => {
    fetch("includes/check_new_messages.php")
    .then(res => res.json())
    .then(data => {
if (data.new_message && data.sender_id) {
    let n = document.getElementById("notification");
    let content = document.getElementById("notification_content");

    content.innerHTML = `<strong>${data.sender_name}</strong>: ${data.message}<br>
                         <a href="#" onclick="startChat('${data.sender_id}'); return false;" style="color:white;text-decoration:underline;">View</a>`;
n.classList.add("show");

setTimeout(() => {
    n.classList.remove("show");
}, 4000);

    fetchChats();
}

    });

    if (CURRENT_USER) {
        fetchMessages(CURRENT_USER);
    }
}, 5000);

function previewImage(src) {
    const overlay = document.getElementById("imgPreviewOverlay");
    const img = document.getElementById("previewImage");
    img.src = src;
    overlay.style.display = "flex";
}

function closePreview() {
    document.getElementById("imgPreviewOverlay").style.display = "none";
    document.getElementById("previewImage").src = "";
}

function deleteMessage(message_id, is_sender) {
    if (!confirm("Are you sure you want to delete this message?")) {
        return;
    }

    let obj = {
        data_type: "delete_message",
        message_id: message_id,
        is_sender: is_sender
    };

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "api.php", true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                let result = JSON.parse(xhr.responseText);
                console.log(result.message);

                // ✅ Reload the current chat messages
                fetchMessages(CURRENT_USER);
            } catch (e) {
                console.error("Invalid JSON: ", xhr.responseText);
            }
        }
    };

    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(JSON.stringify(obj));
}



// Show/hide dropdown menu
function toggleOptionsMenu(el) {
    // Close all other open menus
    document.querySelectorAll('.options-menu').forEach(menu => {
        if (menu !== el.nextElementSibling) {
            menu.style.display = 'none';
        }
    });

    // Toggle current menu
    const menu = el.nextElementSibling;
    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

// Hide all dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.message-wrapper')) {
        document.querySelectorAll('.options-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

</script>
<div id="imgPreviewOverlay" onclick="closePreview()" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
    <img id="previewImage" src="" style="max-width:90%; max-height:90%; border-radius:10px; box-shadow:0 0 10px #000;">
</div>
</body>
</html>
