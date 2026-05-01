<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

// Check if logged in
$userid = $_SESSION['userid'] ?? null;
if (!$userid) {
    header("Location: login.php");
    die;
}

// Check role & verification
$user = $DB->read("SELECT role FROM users WHERE userid = :id LIMIT 1", ['id' => $userid]);
$verify = $DB->read("SELECT is_verified FROM agronomists WHERE userid = :id LIMIT 1", ['id' => $userid]);

if (!$user || $user[0]->role !== 'agronomist' || !$verify || $verify[0]->is_verified != 1) {
    echo "Access denied.";
    die;
}

// Fetch services
$services = $DB->read("SELECT * FROM services WHERE agronomist_id = :id ORDER BY created_at DESC", ['id' => $userid]);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Services - Farmnet Kenya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        @font-face {
            font-family: 'Jura';
            src: url('ui/fonts/jura.ttf') format('truetype');
        }

        body {
            font-family: 'Jura', sans-serif;
            background-color: #f2f9f2;
            margin: 0;
            padding: 0;
        }

        .container {

            padding: 100px 20px 40px;
            max-width: 800px;
            margin: auto;
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 30px;
        }

        .service-box {
            background: #fff;
            border-left: 5px solid #4CAF50;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .service-box h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .service-box p {
            margin: 10px 0;
            color: #555;
        }

        .service-box .actions {
            margin-top: 10px;
        }

        .btn {
            text-decoration: none;
            display: inline-block;
            padding: 10px 14px;
            border-radius: 6px;
            margin-right: 10px;
            font-weight: bold;
            font-size: 14px;
            font-family: 'Jura', sans-serif;
        }

        .btn-edit {
            background: #4CAF50;
            color: white;
        }

        .btn-edit:hover {
            background: #45a049;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
        }

        .no-services {
            padding: 20px;
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            border-radius: 8px;
            color: #856404;
        }
       
        .msg {
            margin-bottom: 20px;
            padding: 12px;
            background-color: #e7ffe7;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            color: #2e7d32;
        }

    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="container">
    <h2>📋 My Services</h2>

    <?php if ($services): ?>
        <?php foreach ($services as $service): ?>
            <div class="service-box">
                <h3><?= htmlspecialchars($service->title) ?></h3>
                <p><?= nl2br(htmlspecialchars($service->description)) ?></p>
                <p><strong>Price:</strong> KES <?= number_format($service->price) ?></p>
                <p><em>Posted on <?= date("jS M Y", strtotime($service->created_at)) ?></em></p>
                <div class="actions">
                    <a href="edit_service.php?id=<?= $service->service_id ?>" class="btn btn-edit">✏️ Edit</a>
                    <a href="delete_service.php?id=<?= $service->service_id ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this service?');">🗑️ Delete</a>
                    <!-- You can add a delete option later here -->
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-services">⚠️ You don't have any services yet.</div>
    <?php endif; ?>
</div>

</body>
</html>
