<?php 
session_start();

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contacts - Farmnet Kenya</title>

    <style>
    #search_wrapper {
        margin: 20px auto;
        max-width: 600px;
        display: flex;
        flex-direction: row;
        gap: 10px;
        align-items: center;
        justify-content: center;
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
        max-width: 600px;
        margin: 20px auto;
    }

    .user-card {
        display: flex;
        align-items: center;
        background-color: #f8fff8;
        border: 1px solid #ccc;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out;
    }

    .user-card:hover {
        background-color: #e5ffe5;
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
    }

    .account-type {
        font-size: 14px;
        color: #444;
    }

    .user-info .location {
        margin-top: 5px;
        font-size: 14px;
        color: #444;
    }

    /* 🔽 New styles for horizontal scroll area */
    #top_agronomists {
        max-width: 100%;
        overflow-x: auto;
        padding: 15px;
    }

    .horizontal-scroll {
        display: flex;
        gap: 15px;
        padding-bottom: 10px;
        scroll-snap-type: x mandatory;
    }

    .horizontal-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .horizontal-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }

.horizontal-scroll .user-card {
    flex: 0 0 auto;
    width: 220px;
    display: block;
    text-align: center;
    margin-bottom: 0;
    position: relative; /* required for rank-number to position inside it */
}

    .horizontal-scroll .user-image {
        width: 60px;
        height: 60px;
        margin: 0 auto 10px;
    }

    .horizontal-scroll .user-info {
        font-size: 14px;
    }
    .rank-number {
    position: absolute;
    top: -10px;
    left: -10px;
    background: #28a745;
    color: white;
    font-weight: bold;
    font-size: 20px;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
</style>

</head>

<body>
<?php
 include("topbar.php");
include('includes/notifications.php');
?>

<!-- Unified Search Bar with Role Filter -->
<div id="search_wrapper">
    <input
        type="text"
        id="search_input"
        placeholder="Search username, first name ,last name location..."
    />
    <select id="role_filter">
        <option value="">All Roles</option>
        <option value="farmer">Farmer</option>
        <option value="agronomist">Agronomist</option>
    </select>
</div>

<!-- User list container -->
<div id="user_list"></div>
<!-- Top Rated Agronomists -->
<div id="top_agronomists"></div>

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

        fetch("includes/contacts.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                find: searchText,
                role: role
            })
        })
        .then(res => res.text())
        .then(data => {
            userList.innerHTML = data;
        })
        .catch(err => {
            console.error("Search error:", err);
        });
    }

    function fetchTopAgronomists() {
    fetch("includes/top_10_agronomists.php")
        .then(res => res.text())
        .then(data => {
            document.getElementById("top_agronomists").innerHTML = data;
        })
        .catch(err => {
            console.error("Top agronomists fetch error:", err);
        });
    }


    searchInput.addEventListener("input", fetchUsers);
    roleFilter.addEventListener("change", fetchUsers);

    function go_to_profile(userid) {
        window.location.href = "profile_view.php?userid=" + userid;
    }

    // 🔽 Load top 10 agronomists when page finishes loading
    document.addEventListener("DOMContentLoaded", () => {
        fetchTopAgronomists();
    });
</script>

</body>
</html>
