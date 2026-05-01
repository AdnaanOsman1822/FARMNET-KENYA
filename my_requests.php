<?php
session_start();
require_once("classes/autoload.php");

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    header("Location: login.php");
    die;
}

// Fetch service requests that are NOT completed + paid
$query = "
    SELECT sr.*, s.title AS service_title, s.price, u.first_name, u.last_name, u.userid AS agronomist_id
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.agronomist_id = u.userid
    WHERE sr.farmer_id = :id
    AND NOT EXISTS (
        SELECT 1 FROM payment_requests pr
        WHERE pr.request_id = sr.request_id
        AND pr.status = 'confirmed'
    )
    ORDER BY sr.requested_at DESC
";

$requests = $DB->read($query, ['id' => $farmer_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Service Requests - Farmnet Kenya</title>
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

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            text-transform: capitalize;
        }

        .status.pending { background: #ffecb3; color: #ff9800; }
        .status.accepted { background: #c8e6c9; color: #388e3c; }
        .status.rejected { background: #ffcdd2; color: #d32f2f; }
        .status.completed { background: #d1c4e9; color: #512da8; }

        .cancel-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 7px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jura', sans-serif;
            font-size: 13px;
        }

        .cancel-btn:hover {
            background: #d32f2f;
        }
        
        .view-btn {
            background: #4CAF50;
            color: white;
            padding: 7px 14px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-family: 'Jura', sans-serif;
            font-size: 13px;
        }

        .view-btn:hover {
            background: #156baf;
            cursor: pointer;
        }

        .no-requests {
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
    <h2>📋 My Service Requests</h2>

    <?php if (is_array($requests) && count($requests) > 0): ?>
        <table>
            <tr>
                <th>Service</th>
                <th>Agronomist</th>
                <th>Price (KES)</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
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
                    <td><span class="status <?= $r->status ?>"><?= $r->status ?></span></td>
                    <td><?= date("d M Y H:i", strtotime($r->requested_at)) ?></td>
                    <td>
                        <?php if ($r->status == 'pending'): ?>
                            <form method="POST" action="includes/cancel_request.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                                <button type="submit" class="cancel-btn" onclick="return confirm('Cancel this request?');">Cancel</button>
                            </form>
                        <?php elseif ($r->status == 'accepted'): ?>
                            <form method="POST" action="prepare_payment.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                                <input type="hidden" name="amount" value="<?= $r->price ?>">
                                <input type="hidden" name="agronomist_id" value="<?= $r->agronomist_id ?>">
                                <button type="submit" class="view-btn" style="background:#2196F3;">💳 Pay</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="no-requests">You haven’t made any service requests yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
