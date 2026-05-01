<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("classes/autoload.php");

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id || !isset($_SESSION['payment_data'])) {
    header("Location: login.php");
    die;
}

$payment = $_SESSION['payment_data'];
$request_id = $payment['request_id'];

$already_paid = false;
$check_payment = $DB->read("SELECT * FROM payment_requests WHERE request_id = :rid AND status = 'confirmed' LIMIT 1", [
    'rid' => $request_id
]);

if ($check_payment) {
    $already_paid = true;
}
$amount = $payment['amount'];
$agronomist_id = $payment['agronomist_id'];

$farmer = $DB->read("SELECT phone_number FROM users WHERE userid = :id LIMIT 1", ['id' => $farmer_id]);
$phone = $farmer && isset($farmer[0]->phone_number) ? $farmer[0]->phone_number : "";

$details = $DB->read("
    SELECT s.title AS service_title, u.first_name, u.last_name
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.agronomist_id = u.userid
    WHERE sr.request_id = :rid AND sr.farmer_id = :fid
    LIMIT 1
", ['rid' => $request_id, 'fid' => $farmer_id]);

if (!$details || count($details) == 0) {
    echo "Invalid request.";
    die;
}

$service_title = $details[0]->service_title;
$agro_name = $details[0]->first_name . " " . $details[0]->last_name;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Preview - Farmnet Kenya</title>
    <style>
        @font-face {
            font-family: 'Jura';
            src: url('ui/fonts/jura.ttf') format('truetype');
        }
        body {
            font-family: 'Jura', sans-serif;
            background: #f0fff0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4CAF50;
            margin-bottom: 20px;
            font-size: 22px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
        .btn {
            margin-top: 15px;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn:hover {
            background: #45a049;
        }
        .complete-btn {
            background: #2196F3;
        }
        .complete-btn:hover {
            background: #1976D2;
        }
        #status {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="container">
    <h2>🔍 Confirm Payment Details</h2>

    <form id="paymentForm">
        <label>Service</label>
        <input type="text" value="<?= htmlspecialchars($service_title) ?>" disabled>

        <label>Agronomist</label>
        <input type="text" value="<?= htmlspecialchars($agro_name) ?>" disabled>

        <label>Amount (KES)</label>
        <input type="number" value="<?= htmlspecialchars($amount) ?>" disabled>

        <label>Your Phone Number (M-Pesa)</label>
        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>

        <input type="hidden" id="request_id" value="<?= htmlspecialchars($request_id) ?>">
        <input type="hidden" id="amount" value="<?= htmlspecialchars($amount) ?>">
        <input type="hidden" id="agronomist_id" value="<?= htmlspecialchars($agronomist_id) ?>">

        <button type="button" class="btn" id="promptBtn" onclick="sendPrompt()">📲 Prompt Payment (STK Push)</button>

        <button type="button" class="btn complete-btn" onclick="checkPayment()">✅ Complete Payment</button>
    </form>

    <div id="status"></div>
</div>

<script>
function sendPrompt() {
    const data = new FormData();
    data.append('phone', document.getElementById('phone').value);
    data.append('request_id', document.getElementById('request_id').value);
    data.append('amount', document.getElementById('amount').value);
    data.append('agronomist_id', document.getElementById('agronomist_id').value);

    fetch('mpesa/stk_push.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.text())
    .then(res => {
        document.getElementById('status').style.color = 'green';
        document.getElementById('status').innerText = "📲 STK push sent. Check your phone.";
    })
    .catch(err => {
        document.getElementById('status').style.color = 'red';
        document.getElementById('status').innerText = "❌ Failed to send payment prompt.";
    });
}

function checkPayment() {
    const payload = {
        request_id: document.getElementById('request_id').value,
        amount: document.getElementById('amount').value,
        agronomist_id: document.getElementById('agronomist_id').value
    };

    fetch('mpesa/check_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === true) {
            window.location.href = "my_requests.php?msg=✅ Payment successful.";
        } else {
            alert(data.message || "❌ Payment not yet confirmed.");
        }
    })
    .catch(() => {
        alert("⚠️ Error checking payment status.");
    });
}

<?php if ($already_paid): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('promptBtn');
        btn.disabled = true;
        btn.style.background = "#ccc";
        btn.innerText = "✅ Already Paid";
        btn.title = "Payment already confirmed";
    });
<?php endif; ?>
</script>

</body>
</html>
