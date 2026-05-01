<?php
session_start();
require_once("classes/autoload.php");

$DB = new Database();
$agronomist_id = $_SESSION['userid'] ?? null;

if (!$agronomist_id) {
    header("Location: login.php");
    die;
}

// Fetch service requests received by this agronomist
$query = "
    SELECT sr.*, s.title AS service_title, s.price, u.first_name, u.last_name, u.userid AS farmer_id
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.service_id
    JOIN users u ON sr.farmer_id = u.userid
    WHERE sr.agronomist_id = :id AND sr.status != 'completed'
    ORDER BY sr.requested_at DESC
";

$requests = $DB->read($query, ['id' => $agronomist_id]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Requests - Farmnet Kenya</title>
    <style>
        body {
            font-family: Jura, sans-serif;
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

        .action-btn {
            padding: 6px 10px;
            border-radius: 4px;
            border: none;
            font-family: Jura;
            cursor: pointer;
        }

        .accept-btn {
            background-color: #4CAF50;
            color: white;
        }

        .reject-btn {
            background-color: #f44336;
            color: white;
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
<?php if (isset($_SESSION['flash'])): ?>
    <div style="background: #e0ffe0; padding: 10px; border-left: 5px solid #4CAF50; margin-bottom: 15px;">
        <?= $_SESSION['flash'] ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="container">
    <h2>📨 Service Requests to You</h2>

    <?php if (is_array($requests) && count($requests) > 0): ?>
        <table>
        <tr>
            <th>Farmer</th>
            <th>Service</th>
            <th>Price (KES)</th>
            <th>Notes</th> 
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

            <?php foreach ($requests as $r): ?>
                <tr>
                    <td>
                        <a href="profile_view.php?userid=<?= $r->farmer_id ?>" class="name-link">
                            <?= htmlspecialchars($r->first_name . " " . $r->last_name) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($r->service_title) ?></td>
                    <td><?= number_format($r->price, 2) ?></td>
                    <td><?= nl2br(htmlspecialchars($r->description)) ?></td>
                    <td><span class="status <?= $r->status ?>"><?= $r->status ?></span></td>
                    <td><?= date("d M Y H:i", strtotime($r->requested_at)) ?></td>
                    <td>
                        <?php if ($r->status == 'pending'): ?>
                            <form method="POST" action="includes/handle_request_action.php" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                                <button name="action" value="accept" class="action-btn accept-btn" onclick="return confirm('Accept this request?');">Accept</button>
                                <br>
                                <br>
                                <button name="action" value="reject" class="action-btn reject-btn" onclick="return confirm('Reject this request?');">Reject</button>
                            </form>
                        <?php elseif ($r->status == 'accepted'): ?>
                            <?php
                            // Check if payment is confirmed
                            $payment = $DB->read("
                                SELECT status 
                                FROM payment_requests 
                                WHERE request_id = :rid 
                                ORDER BY id DESC 
                                LIMIT 1
                            ", ['rid' => $r->request_id]);

                            $paid = isset($payment[0]->status) && $payment[0]->status === 'confirmed';

                            ?>
                            
                            <?php if ($paid): ?>
                                <form method="POST" action="includes/complete_service.php" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= $r->request_id ?>">
                                    <button class="action-btn accept-btn" onclick="return confirm('Mark this service as completed?');">✅ Complete</button>
                                </form>
                            <?php else: ?>
                                <span style="color: #999;">⏳ Waiting for Payment</span>
                            <?php endif; ?>
                        <?php elseif ($r->status == 'completed'): ?>
                            ✅ Completed
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <div class="no-requests">No incoming requests at the moment.</div>
    <?php endif; ?>
</div>

</body>
</html>
