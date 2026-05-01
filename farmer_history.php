<?php
session_start();
require_once("classes/autoload.php");

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    header("Location: login.php");
    die;
}

// Fetch completed and paid service requests with confirmed payments only
$query = "
    SELECT sr.*, s.title AS service_title, s.price, u.first_name, u.last_name, u.userid AS agronomist_id, pr.mpesa_receipt
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.agronomist_id = u.userid
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
    WHERE sr.farmer_id = :id
    AND sr.status = 'completed'
    ORDER BY sr.requested_at DESC
";

$requests = $DB->read($query, ['id' => $farmer_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service History - Farmnet Kenya</title>
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
    <h2>📄 Completed Services (History)</h2>

    <!-- Debug Output -->
    
    <?php if (is_array($requests) && count($requests) > 0): ?>
        <table>
            <tr>
                <th>Service</th>
                <th>Agronomist</th>
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
                        <a href="profile_view.php?userid=<?= $r->agronomist_id ?>" class="name-link">
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
                            // Check if this service has already been rated
                            $rate_query = "SELECT * FROM ratings WHERE request_id = :rid";
                            $already_rated = $DB->read($rate_query, ['rid' => $r->request_id]);
                        ?>
                        <?php if (!$already_rated): ?>
                            <form method="POST" action="rate.php">
                                <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                                <input type="hidden" name="agronomist_id" value="<?= $r->agronomist_id ?>">
                                <button type="submit" class="download-btn">⭐ Rate</button>
                            </form>
                        <?php else: ?>
                            ✅ Rated
                        <?php endif; ?>
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
