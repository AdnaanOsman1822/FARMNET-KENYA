<?php
session_start();
require_once("classes/autoload.php");
$DB = new Database();

// Check if admin is logged in
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['userid'];
$check = $DB->read("SELECT role FROM users WHERE userid = :id LIMIT 1", ['id' => $admin_id]);
if (!$check || $check[0]->role !== 'admin') {
    echo "Access denied.";
    exit;
}

// Get target user
$target_id = $_GET['userid'] ?? null;
if (!$target_id || strlen($target_id) < 3) {
    echo "Invalid user ID.";
    exit;
}

// Suspend/unsuspend logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_suspend'])) {
    $current_status = $_POST['current_status'] ?? 0;
    $new_status = $current_status == 1 ? 0 : 1;

    $DB->write("UPDATE users SET suspended = :status WHERE userid = :id", [
        'status' => $new_status,
        'id' => $target_id
    ]);

    echo "<script>window.location.href = window.location.href;</script>";
    exit;
}

// Get user with possible document_path
$user = $DB->read("
    SELECT 
        u.userid, u.first_name, u.last_name, u.username,
        u.phone_number, u.email, u.location, u.role, u.image, u.suspended,
        a.document_path
    FROM users u 
    LEFT JOIN agronomists a ON u.userid = a.userid 
    WHERE u.userid = :id 
    LIMIT 1
", ['id' => $target_id]);

if (!$user) {
    echo "User not found.";
    exit;
}
$user = $user[0];

// Reports count
$report_count = $DB->read(
    "SELECT COUNT(*) AS total FROM reports WHERE reported_userid = :id",
    ['id' => $target_id]
);
$report_count = $report_count ? $report_count[0]->total : 0;

// Get agronomist details if needed
$agronomist = null;
if ($user->role === "agronomist") {
    $agronomist = $DB->read("SELECT * FROM agronomists WHERE userid = :id LIMIT 1", ['id' => $target_id]);
    $agronomist = $agronomist ? $agronomist[0] : null;

    // Fetch ratings summary
    $rating_data = $DB->read("
        SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
        FROM ratings
        WHERE agronomist_id = :id
    ", ['id' => $target_id]);
    $rating_data = $rating_data ? $rating_data[0] : null;

    // Fetch latest reviews
    $reviews = $DB->read("
        SELECT r.rating, r.review, r.created_at, u.first_name, u.last_name
        FROM ratings r
        JOIN users u ON r.farmer_id = u.userid
        WHERE r.agronomist_id = :id
        ORDER BY r.created_at DESC
        LIMIT 10
    ", ['id' => $target_id]);
    if (!is_array($reviews)) $reviews = [];
} else {
    $rating_data = null;
    $reviews = [];
}

// Profile image fallback
$image_path = (isset($user->image) && file_exists($user->image)) ? $user->image : "ui/images/user.jpg";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - View Profile | Farmnet Kenya</title>
    <style>
        body {
            font-family: 'Jura', sans-serif;
            background: #f4fff4;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            border: 1px solid #cfc;
            border-left: 6px solid #7CD375;
            border-radius: 14px;
            padding: 25px;
        }
        .profile-header {
            text-align: center;
        }
        .profile-header img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .profile-details {
            font-size: 16px;
            margin-top: 20px;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .suspended {
            color: red;
            font-weight: bold;
        }
        .verified {
            color: green;
            font-weight: bold;
        }
        .button {
            padding: 10px 15px;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 10px;
        }
        .suspend-btn {
            background-color: #f44336;
        }
        .unsuspend-btn {
            background-color: #4CAF50;
        }
        a {
            color: #2a7ae2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }

        /* Rating and reviews styles */
        .rating-box {
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            text-align: center;
        }
        .stars {
            font-size: 20px;
            color: #ccc;
        }
        .stars .filled {
            color: #4CAF50;
        }
        .reviews-container {
            background: #f9fff9;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            text-align: left;
        }
        .review {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .review:last-child {
            border-bottom: none;
        }
        .reviewer {
            font-weight: bold;
            color: #333;
        }
        .review-text {
            margin-top: 5px;
            font-size: 15px;
        }
        .review-date {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>

<?php include("admin_topbar.php"); ?>

<div class="profile-container">
    <div class="profile-header">
        <img src="<?= htmlspecialchars($image_path) ?>" alt="Profile Image">
        <h2><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?> (<?= ucfirst($user->role) ?>)</h2>
        <p class="<?= $user->suspended ? 'suspended' : 'verified' ?>">
            <?= $user->suspended ? "⛔ Suspended Account" : "✅ Active Account" ?>
        </p>
    </div>

    <div class="profile-details">
        <div><span class="label">Phone:</span> <?= htmlspecialchars($user->phone_number) ?></div>
        <div><span class="label">Email:</span> <?= htmlspecialchars($user->email) ?></div>
        <div><span class="label">Role:</span> <?= ucfirst($user->role) ?></div>
        <div><span class="label">Location:</span> <?= htmlspecialchars($user->location) ?></div>
        <div><span class="label">Reports Against This User:</span> <?= $report_count ?></div>

        <?php if ($user->role === "agronomist" && $agronomist): ?>
            <p><span class="label">Verified:</span> <?= $agronomist->is_verified ? '✅ Yes' : '❌ No' ?></p>
            <p><span class="label">Academic Docs:</span>
            <?php
            $doc_path = $agronomist->document_path ?? '';
            if ($doc_path && file_exists($doc_path)) {
                echo '<a href="' . htmlspecialchars($doc_path) . '" target="_blank" download>📄 Download</a>';
            } else {
                echo "None uploaded";
            }
            ?>
            </p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="toggle_suspend" value="1">
            <input type="hidden" name="current_status" value="<?= $user->suspended ?>">
            <button type="submit" class="button <?= $user->suspended ? 'unsuspend-btn' : 'suspend-btn' ?>">
                <?= $user->suspended ? '✅ Unsuspend User' : '⛔ Suspend User' ?>
            </button>
        </form>
    </div>

    <?php if ($user->role === 'agronomist' && $rating_data): ?>
    <div class="rating-box">
        <div>
            <?php
            $avg = round($rating_data->avg_rating ?? 0);
            echo "Average Rating: ";
           echo '<div class="stars">';
for ($i = 1; $i <= 5; $i++) {
    $filled = $i <= round($avg);
    echo "<span class='" . ($filled ? "filled" : "") . "'>★</span>";
}
echo '</div>';

            echo " <small>(" . number_format($rating_data->avg_rating ?? 0, 1) . "/5 from $rating_data->total_reviews reviews)</small>";
            ?>
        </div>

        <?php if (is_array($reviews) && count($reviews) > 0): ?>
        <div class="reviews-container">
            <h3>Reviews from farmers</h3>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="reviewer"><?= htmlspecialchars($review->first_name . " " . $review->last_name) ?></div>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= $review->rating ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                        <span class="review-date"> - <?= date("M d, Y", strtotime($review->created_at)) ?></span>
                    </div>
                    <?php if (!empty($review->review)): ?>
                        <div class="review-text"><?= htmlspecialchars($review->review) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
