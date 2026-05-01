<?php
session_start();
require_once("../classes/autoload.php");

$DB = new Database();

// Only allow access if logged in and request ID is posted
if (!isset($_SESSION['userid']) || !isset($_POST['request_id'])) {
    die("Unauthorized access.");
}

$request_id = $_POST['request_id'];

// Fetch receipt data
$query = "
    SELECT sr.*, s.title AS service_title, s.price, u.first_name AS agron_first, u.last_name AS agron_last,
           f.first_name AS farmer_first, f.last_name AS farmer_last,
           pr.mpesa_receipt, pr.amount, pr.timestamp
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.agronomist_id = u.userid
    JOIN users f ON sr.farmer_id = f.userid
    JOIN payment_requests pr ON pr.request_id = sr.request_id
    WHERE sr.request_id = :request_id
    AND pr.status = 'confirmed'
";

$row = $DB->read($query, ['request_id' => $request_id]);

if (!$row || !is_array($row)) {
    die("Receipt not found.");
}

$r = $row[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt - Farmnet Kenya</title>
    <style>
        @font-face {
            font-family: 'Jura';
            src: url('../ui/fonts/jura.ttf') format('truetype');
        }

        body {
            font-family: 'Jura', sans-serif;
            background: #f9fff9;
            padding: 30px;
            color: #333;
        }

        .receipt-box {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        td {
            padding: 10px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }

        .print-btn {
            margin-top: 20px;
            display: block;
            text-align: center;
        }

        .print-btn button {
            padding: 10px 20px;
            font-family: 'Jura', sans-serif;
            background: #4CAF50;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .print-btn button:hover {
            background: #388e3c;
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <h2>🧾 Payment Receipt</h2>

    <table>
        <tr>
            <td><strong>Service Title:</strong></td>
            <td><?= htmlspecialchars($r->service_title) ?></td>
        </tr>
        <tr>
            <td><strong>Farmer:</strong></td>
            <td><?= htmlspecialchars($r->farmer_first . ' ' . $r->farmer_last) ?></td>
        </tr>
        <tr>
            <td><strong>Agronomist:</strong></td>
            <td><?= htmlspecialchars($r->agron_first . ' ' . $r->agron_last) ?></td>
        </tr>
        <tr>
            <td><strong>Amount Paid:</strong></td>
            <td>KES <?= number_format($r->amount, 2) ?></td>
        </tr>
        <tr>
            <td><strong>M-Pesa Receipt:</strong></td>
            <td><?= htmlspecialchars($r->mpesa_receipt) ?></td>
        </tr>
        <tr>
            <td><strong>Date Paid:</strong></td>
            <td><?= date("d M Y H:i", strtotime($r->timestamp)) ?></td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td>✅ Confirmed</td>
        </tr>
    </table>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print or Save as PDF</button>
    </div>

    <div class="footer">
        &copy; <?= date("Y") ?> Farmnet Kenya — Empowering Farmers & Agronomists
    </div>
</div>

</body>
</html>
