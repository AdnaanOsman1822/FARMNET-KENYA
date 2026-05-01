<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

require_once("classes/autoload.php");
$DB = new Database();

$userid = $_SESSION['userid'] ?? null;

// Ensure only agronomists can access this page
$query = "SELECT role FROM users WHERE userid = :userid LIMIT 1";
$row = $DB->read($query, ['userid' => $userid]);

if (!$row || $row[0]->role !== 'agronomist') {
    echo "Unauthorized access.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Apply for Verification - Farmnet Kenya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    @font-face {
      font-family: 'Jura';
      src: url('ui/fonts/jura.ttf') format('truetype');
    }

    body {
      font-family: 'Jura', sans-serif;
      background-color: #f5fff5;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 50px auto;
      padding: 25px;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.08);
      border-radius: 10px;
    }

    h2 {
      text-align: center;
      color: #4CAF50;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    label {
      margin: 10px 0 5px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="file"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button {
      margin-top: 20px;
      padding: 12px;
      background-color: #4CAF50;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 6px;
      cursor: pointer;
    }

    button:hover {
      background-color: #45a049;
    }

    .back-link {
      margin-top: 15px;
      text-align: center;
    }

    .back-link a {
      color: #4CAF50;
      text-decoration: none;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Apply for Verification</h2>
  <form action="includes/save_verification.php" method="POST" enctype="multipart/form-data">
    <label for="qualification">Your Qualification (e.g., BSc. Agronomy):</label>
    <input type="text" id="qualification" name="qualification" required />

    <label for="document">Upload Supporting Document (PDF) must contain identity documents and academic documents that support your profession:</label>
    <input type="file" id="document" name="document" accept=".pdf" required />

    <button type="submit">Submit Application</button>
  </form>

  <div class="back-link">
    <a href="index.php">← Back to Dashboard</a>
  </div>
</div>

</body>
</html>
