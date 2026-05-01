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
    <title>Farmnet Kenya - Admin Dashboard</title>
    <style>
        body {
            font-family: 'Jura', sans-serif;
            background: #f4fff4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h2 {
            color: #2e7d32;
            margin-bottom: 15px;
        }

        .agronomist-box {
            background: #ffffff;
            border-left: 6px solid #7CD375;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .agronomist-box strong {
            font-size: 20px;
            color: #388e3c;
        }

        .status-label {
            display: inline-block;
            padding: 3px 10px;
            font-size: 13px;
            background-color: #ffc107;
            color: #000;
            border-radius: 4px;
            margin-left: 10px;
        }

        .button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            color: white;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
            margin-right: 10px;
        }

        .verify-btn {
            background-color: #28a745;
        }

        .reject-btn {
            background-color: #dc3545;
        }

        a.doc-link {
            color: #007bff;
            text-decoration: underline;
        }
    </style>
</head>
<body>



    <br><h2>Admin Dashboard - Verify Agronomists</h2>
    <div id="agronomist_list">Loading...</div>

    <script>
    function fetchAgronomists() {
        fetch("includes/get_agronomists.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ data_type: "unverified_agronomists" })
        })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("agronomist_list");
            if (data.length === 0) {
                container.innerHTML = "<p>No pending verification requests.</p>";
                return;
            }

            container.innerHTML = "";
            data.forEach(user => {
                const docLink = user.document_path 
                    ? `<a class="doc-link" href="${user.document_path}" target="_blank">📄 View Document</a>` 
                    : 'Not uploaded';

                container.innerHTML += `
                    <div class="agronomist-box">
                        <strong>${user.first_name} ${user.last_name}</strong>
                        <span class="status-label">Pending</span><br>
                        📧 ${user.email}<br>
                        📞 ${user.phone_number}<br>
                        🎓 Qualification: <b>${user.qualification || 'N/A'}</b><br>
                        📎 Document: ${docLink}<br>
                        <button class="button verify-btn" onclick="verifyUser('${user.userid}')">✅ Verify</button>
                        <button class="button reject-btn" onclick="rejectUser('${user.userid}')">❌ Reject</button>
                    </div>
                `;
            });
        });
    }

    function verifyUser(userid) {
        fetch("includes/verify_agronomist.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ userid: userid })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            fetchAgronomists();
        });
    }

    function rejectUser(userid) {
        if (!confirm("Are you sure you want to reject this agronomist?")) return;

        fetch("includes/reject_agronomist.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ userid: userid })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            fetchAgronomists();
        });
    }

    fetchAgronomists();
    </script>
</body>
</html>
