<?php
session_start();
require_once("classes/autoload.php");

$DB = new Database();
$agronomist_id = $_SESSION['userid'] ?? null;

if (!$agronomist_id) {
    header("Location: login.php");
    die;
}

// Fetch completed and confirmed paid services for agronomist
$query = "
    SELECT sr.*, s.title AS service_title, s.price, u.first_name, u.last_name, u.userid AS farmer_id, pr.mpesa_receipt
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.farmer_id = u.userid
    JOIN (
        SELECT p1.*
        FROM payment_requests p1
        INNER JOIN (
            SELECT request_id, MAX(id) AS max_id
            FROM payment_requests
            WHERE status = 'confirmed'
            GROUP BY request_id
        ) p2 ON p1.id = p2.max_id
    ) pr ON pr.request_id = sr.request_id
    WHERE sr.agronomist_id = :id
    AND sr.status = 'completed'
    ORDER BY sr.requested_at DESC
";

$requests = $DB->read($query, ['id' => $agronomist_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Agronomist Service History - Farmnet Kenya</title>
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
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.05);
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        .download-btn {
            background: #4CAF50;
            color: white;
            padding: 7px 14px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
        }

        .download-btn:hover {
            background: #388e3c;
        }

        .no-history {
            text-align: center;
            color: #888;
            margin-top: 30px;
        }

        a.name-link {
            color: #4CAF50;
            text-decoration: none;
        }

        a.name-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="container">
    <h2>📄 Services You Completed</h2>


    <?php if (is_array($requests) && count($requests) > 0): ?>
        <table>
            <tr>
                <th>Service</th>
                <th>Farmer</th>
                <th>Amount (KES)</th>
                <th>Transaction</th>
                <th>Date</th>
                <th>Receipt</th>
                <th>Rating</th>
            </tr>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r->service_title) ?></td>
                    <td>
                        <a href="profile_view.php?userid=<?= $r->farmer_id ?>" class="name-link">
                            <?= htmlspecialchars($r->first_name . " " . $r->last_name) ?>
                        </a>
                    </td>
                    <td><?= number_format($r->price, 2) ?></td>
                    <td><?= htmlspecialchars($r->mpesa_receipt) ?></td>
                    <td><?= date("d M Y H:i", strtotime($r->requested_at)) ?></td>
                    <td>
                        <form method="POST" action="mpesa/receipt.php" target="_blank">
                            <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                            <button type="submit" class="download-btn">🧾 Download</button>
                        </form>
                    </td>
                    <td>
                        <?php
                            $rate_query = "SELECT * FROM ratings WHERE request_id = :rid";
                            $already_rated = $DB->read($rate_query, ['rid' => $r->request_id]);
                        ?>
                        <?= $already_rated ? "⭐ Rated" : "—" ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="no-history">You have no completed services yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
