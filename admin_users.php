<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}

$userid = $_SESSION['userid'];

$check = $DB->read("SELECT role FROM users WHERE userid = :userid LIMIT 1", ['userid' => $userid]);
if (!$check || $check[0]->role !== 'admin') {
    echo "Access denied.";
    die;
}

include("admin_topbar.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - User Management | Farmnet Kenya</title>
    <style>
        body {
            font-family: 'Jura', sans-serif;
            background: #f4fff4;
            margin: 0;
            padding: 0;
        }

        #search_wrapper {
            margin: 20px auto;
            max-width: 700px;
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        #search_input, #role_filter {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        #search_input {
            flex: 2;
        }

        #role_filter {
            flex: 1;
        }

        #user_list {
            max-width: 700px;
            margin: 20px auto;
        }

        .user-card {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border: 1px solid #cfc;
            border-left: 6px solid #7CD375;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .user-card:hover {
            background-color: #eaffea;
        }

        .user-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }

        .user-info .username {
            font-size: 14px;
            color: #888;
            margin-bottom: 3px;
        }

        .user-info .name-username {
            font-size: 18px;
            font-weight: bold;
        }

        .account-type {
            font-size: 14px;
            color: #444;
        }

        .location {
            font-size: 14px;
            color: #444;
            margin-top: 3px;
        }
    </style>
</head>
<body>

<!-- Unified Search Bar with Role Filter -->
<div id="search_wrapper">
    <input type="text" id="search_input" placeholder="Search by name, username, or location...">
    <select id="role_filter">
        <option value="">All Roles</option>
        <option value="farmer">Farmer</option>
        <option value="agronomist">Agronomist</option>
        <option value="admin">Admin</option>
    </select>
</div>

<!-- User list container -->
<div id="user_list"></div>

<script>
    const searchInput = document.getElementById("search_input");
    const roleFilter = document.getElementById("role_filter");
    const userList = document.getElementById("user_list");

    function fetchUsers() {
        const searchText = searchInput.value.trim();
        const role = roleFilter.value;

        if (searchText.length === 0) {
            userList.innerHTML = "";
            return;
        }

fetch("includes/admin_search_users.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
    },
    body: JSON.stringify({
        query: searchText,
        role: role
    })
})
.then(async (res) => {
    const contentType = res.headers.get("content-type");
    if (contentType && contentType.includes("application/json")) {
        return res.json();
    } else {
        const text = await res.text();
        throw new Error("Expected JSON but got: " + text);
    }
})
.then(data => {
    userList.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
        userList.innerHTML = "<p style='text-align:center;'>No matching users found.</p>";
        return;
    }

    data.forEach(user => {
        const imagePath = user.image && user.image !== "" ? user.image : "ui/images/user.jpg";
        userList.innerHTML += `
            <div class="user-card" onclick="goToProfile('${user.userid}')">
                <img class="user-image" src="${imagePath}" alt="Profile">
                <div class="user-info">
                    <div class="username">@${user.username}</div>
                    <div class="name-username">${user.first_name} ${user.last_name}</div>
                    <div class="account-type"> ${user.role}</div>
                    <div class="location"> ${user.location || "N/A"}</div>
                </div>
            </div>
        `;
    });
})
.catch(err => {
    console.error("Search error:", err);
    userList.innerHTML = `<p style='text-align:center;color:red;'>❌ Search failed: ${err.message}</p>`;
});

    }

    function goToProfile(userid) {
           window.location.href = "admin_profile_view.php?userid=" + userid;
}



    searchInput.addEventListener("input", fetchUsers);
    roleFilter.addEventListener("change", fetchUsers);
</script>

</body>
</html>


    