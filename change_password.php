<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}
include('topbar.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password - Farmnet Kenya</title>
    <style>
        body { font-family: Jura, sans-serif; background: #f4f4f4; }
        .container { max-width: 420px; margin: 60px auto; background: white; padding: 22px; border-radius: 12px; box-shadow: 0 6px 30px rgba(0,0,0,0.07); }
        h2 { text-align: center; color: #102D03; margin-bottom: 8px; }
        p.sub { text-align:center; color:#7CD375; margin-top: 0; margin-bottom: 18px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; box-sizing: border-box; }
        button { background: #7CD375; color: #fff; padding: 12px; border: none; width: 100%; border-radius: 8px; font-weight: bold; cursor: pointer; }
        button[disabled] { opacity: 0.6; cursor: not-allowed; }
        .message { text-align: center; margin-top: 12px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>
    <p class="sub">Keep your account secure</p>

    <input type="password" id="current_password" placeholder="Current Password">
    <input type="password" id="new_password" placeholder="New Password (min 6 chars)">
    <input type="password" id="confirm_password" placeholder="Confirm New Password">
    <button id="update_btn" onclick="changePassword()">Update Password</button>

    <div id="message" class="message"></div>
</div>

<script>
async function changePassword() {
    const btn = document.getElementById('update_btn');
    const msg = document.getElementById('message');
    msg.textContent = '';
    msg.style.color = 'black';

    const current = document.getElementById('current_password').value.trim();
    const nw = document.getElementById('new_password').value.trim();
    const confirm = document.getElementById('confirm_password').value.trim();

    if (!current || !nw || !confirm) {
        msg.textContent = 'All fields are required.';
        msg.style.color = 'red';
        return;
    }
    if (nw.length < 6) {
        msg.textContent = 'New password must be at least 6 characters.';
        msg.style.color = 'red';
        return;
    }
    if (nw !== confirm) {
        msg.textContent = 'New passwords do not match.';
        msg.style.color = 'red';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Updating...';

    const formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password', nw);
    formData.append('confirm_password', confirm);

    try {
        const res = await fetch('includes/change_password.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // include session cookie
        });

        // Try to parse JSON; fallback friendly message if not JSON
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
            const txt = await res.text();
            msg.textContent = 'Unexpected server response.';
            msg.style.color = 'red';
            console.error('Unexpected response:', txt);
            btn.disabled = false;
            btn.textContent = 'Update Password';
            return;
        }

        const data = await res.json();

        msg.textContent = data.message || 'No message';
        msg.style.color = (data.data_type === 'info') ? 'green' : 'red';

        if (data.data_type === 'info') {
            // redirect after short delay so user sees the success text
            setTimeout(() => {
                const dest = data.redirect ? data.redirect : '../index.php';
                window.location.href = dest;
            }, 700);
        } else {
            btn.disabled = false;
            btn.textContent = 'Update Password';
        }

    } catch (err) {
        console.error(err);
        msg.textContent = 'Network or server error. Try again later.';
        msg.style.color = 'red';
        btn.disabled = false;
        btn.textContent = 'Update Password';
    }
}
</script>
</body>
</html>
