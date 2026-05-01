<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

$userid = $_SESSION['userid'] ?? null;
if (!$userid) {
    header("Location: login.php");
    die;
}

$user = $DB->read("SELECT role FROM users WHERE userid = :id LIMIT 1", ['id' => $userid]);
$verify = $DB->read("SELECT is_verified FROM agronomists WHERE userid = :id LIMIT 1", ['id' => $userid]);

if (!$user || $user[0]->role !== 'agronomist' || !$verify || $verify[0]->is_verified != 1) {
    echo "Access denied. Only verified agronomists can post services.";
    die;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');

    if ($title && $description && is_numeric($price)) {
        $DB->write("INSERT INTO services (agronomist_id, title, description, price, created_at) 
                    VALUES (:id, :title, :desc, :price, NOW())", [
            'id' => $userid,
            'title' => $title,
            'desc' => $description,
            'price' => $price
        ]);
        $msg = "✅ Service added successfully!";
    } else {
        $msg = "❌ Please fill all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Service - Farmnet Kenya</title>
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

        /* Space below topbar */
        .page-container {
            padding: 100px 20px 40px;
            max-width: 600px;
            margin: auto;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            border-left: 6px solid #4CAF50;
            box-shadow: 0 4px 10px rgba(0,0,0,0.07);
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 20px;
            color: #333;
        }

        input, textarea {
            width: 100%;
            padding: 14px 16px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'Jura', sans-serif;
            font-size: 15px;
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            border-color: #4CAF50;
            outline: none;
            background: #f6fff6;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            margin-top: 25px;
            font-weight: bold;
            font-family: 'Jura', sans-serif;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .msg {
            margin-top: 20px;
            padding: 12px;
            background-color: #e7ffe7;
            border: 1px solid #c8e6c9;
            border-radius: 6px;
            color: #2e7d32;
        }

        @media (max-width: 600px) {
            .page-container {
                padding-top: 90px;
                padding-left: 15px;
                padding-right: 15px;
            }

            .form-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="page-container">
    <div class="form-box">
        <h2>➕ Add a Service</h2>

        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="title">Service Title:</label>
            <input type="text" name="title" id="title" placeholder="e.g. Soil Testing" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="5" placeholder="Describe the service in detail..." required></textarea>

            <label for="price">Price (KES):</label>
            <input type="number" name="price" id="price" placeholder="e.g. 500" required>

            <button type="submit">Add Service</button>
        </form>
    </div>
</div>

</body>
</html>
