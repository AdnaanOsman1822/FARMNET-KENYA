<?php
session_start();
require_once("classes/autoload.php");

$DB = new Database();
$farmer_id = $_SESSION['userid'] ?? null;

if (!$farmer_id) {
    header("Location: login.php");
    die;
}

$agronomist_id = $_GET['agronomist_id'] ?? null;
if (!$agronomist_id) {
    echo "No agronomist selected.";
    die;
}

// Fetch agronomist services
$services = $DB->read("SELECT * FROM services WHERE agronomist_id = :id", ['id' => $agronomist_id]);

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $service_id = $_POST['service_id'] ?? null;
    $description = trim($_POST['description'] ?? "");

    if ($service_id) {
        $result = $DB->read("SELECT agronomist_id FROM services WHERE service_id = :sid", ['sid' => $service_id]);
        if ($result && isset($result[0]->agronomist_id)) {
            $agro_id = $result[0]->agronomist_id;

            $DB->write("INSERT INTO service_requests 
                (farmer_id, agronomist_id, service_id, description) 
                VALUES (:f, :a, :s, :d)", [
                'f' => $farmer_id,
                'a' => $agronomist_id,
                's' => $service_id,
                'd' => $description
            ]);

            // ✅ Redirect to contacts after successful request
            header("Location: my_requests.php");
            exit;

        } else {
            $msg = "❌ Invalid service selected.";
        }
    } else {
        $msg = "❌ Please select a service.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Request Service - Farmnet Kenya</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0fff0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-left: 6px solid #4CAF50;
            border-radius: 10px;
            box-shadow: 0 5px 12px rgba(0,0,0,0.1);
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 25px;
        }

        select, textarea, input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            background: #2196F3;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #1976D2;
        }

        .msg {
            margin-top: 15px;
            padding: 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .success {
            background: #e0ffe0;
            border: 1px solid #b2ffb2;
            color: #2e7d32;
        }

        .error {
            background: #ffe0e0;
            border: 1px solid #ffb2b2;
            color: #a94442;
        }
    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="container">
    <h2>📋 Request a Service</h2>

    <?php if ($msg): ?>
        <div class="msg <?= strpos($msg, '❌') !== false ? 'error' : 'success' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($services): ?>
        <form method="POST">
            <label for="service_id">Select a service:</label>
            <select name="service_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($services as $service): ?>
                    <option value="<?= $service->service_id ?>">
                        <?= htmlspecialchars($service->title) ?> - KES <?= number_format($service->price) ?> 
                        (<?= htmlspecialchars($service->description) ?>)
                    </option>
                <?php endforeach; ?>
            </select>


            <label for="description">Additional Notes (optional):</label>
            <textarea name="description" rows="4" placeholder="E.g., Weed control needed on my 2-acre maize farm in Embu."></textarea>

            <input type="submit" value="Submit Request">
        </form>
    <?php else: ?>
        <p>This agronomist has no services listed currently.</p>
    <?php endif; ?>
</div>

</body>
</html>
