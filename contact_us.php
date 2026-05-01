<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

include("topbar.php");    
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Farmnet Kenya</title>
    <style>
        .contact-container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            text-align: center;
        }
        select, textarea, button, input {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background: #2e7d32;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #256027;
        }
        .hidden { display: none; }
        .msg { text-align: center; margin: 10px 0; }
        .search-results { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 5px; }
        .user-card {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }
        .user-card:hover { background: #f9f9f9; }
        .user-card img {
            width: 40px; height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="contact-container">
    <h2>Contact Us</h2>
    <div id="msgBox" class="msg"></div>

    <form id="contactForm">
        <select name="subject" id="subject">
            <option value="Suggestion">Suggestion</option>
            <option value="Report User">Report User</option>
            <option value="Other">Other</option>
        </select>

        <div id="reportUserSection" class="hidden">
            <input type="text" id="searchUser" placeholder="Search user by name or email">
            <div id="searchResults" class="search-results"></div>
            <input type="hidden" name="reported_userid" id="reportedUserId">
        </div>

        <textarea name="message" placeholder="Write your message here..." required></textarea>

        <button type="submit">Send</button>
    </form>
</div>

<script>
const subjectSelect = document.getElementById('subject');
const reportUserSection = document.getElementById('reportUserSection');
const searchInput = document.getElementById('searchUser');
const resultsDiv = document.getElementById('searchResults');
const reportedUserIdInput = document.getElementById('reportedUserId');
const msgBox = document.getElementById('msgBox');

subjectSelect.addEventListener('change', function() {
    reportUserSection.classList.toggle('hidden', this.value !== 'Report User');
    if (this.value !== 'Report User') {
        searchInput.value = '';
        resultsDiv.innerHTML = '';
        reportedUserIdInput.value = '';
    }
});

// Search users live
searchInput.addEventListener('input', function() {
    const keyword = this.value.trim();
    if (keyword.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }

    fetch('includes/search_users.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ keyword })
    })
    .then(res => res.json())
    .then(users => {
        resultsDiv.innerHTML = '';
        if (!users.length) {
            resultsDiv.innerHTML = '<div style="text-align:center;color:gray;">No matching users</div>';
            return;
        }
       users.forEach(user => {
    const div = document.createElement('div');
    div.className = 'user-card';
    div.innerHTML = `
        <div>
            <strong>@${user.username}</strong><br>
            <small>${user.first_name} ${user.last_name}</small>
        </div>
    `;
    div.onclick = () => {
        searchInput.value = `@${user.username}`;
        reportedUserIdInput.value = user.userid;
        resultsDiv.innerHTML = '';
    };
    resultsDiv.appendChild(div);
});


    });
});

// Submit form
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('includes/contact_us.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(res => res.json())
    .then(data => {
        msgBox.textContent = data.message;
        msgBox.style.color = data.success ? 'green' : 'red';
        if (data.success) {
            this.reset();
            resultsDiv.innerHTML = '';
        }
    });
});
</script>

</body>
</html>
