<?php
session_start();
require_once("classes/autoload.php");

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}

if (!isset($_GET['userid'])) {
    echo "User ID not provided.";
    die;
}

$userid = $_GET['userid'];
$DB = new Database();

$query = "SELECT * FROM users WHERE userid = :userid LIMIT 1";
$params = ['userid' => $userid];
$results = $DB->read($query, $params);

if (is_array($results) && count($results) > 0) {
    $user = $results[0];
} else {
    echo "User not found.";
    die;
}

$image = file_exists($user->image) ? $user->image : "ui/images/user.jpg";

// Rating info
$rating_query = "
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
    FROM ratings
    WHERE agronomist_id = :id
";
$rating_data = $DB->read($rating_query, ['id' => $userid])[0] ?? null;

// Latest reviews
$reviews_query = "
    SELECT r.rating, r.review, r.created_at, u.first_name, u.last_name
    FROM ratings r
    JOIN users u ON r.farmer_id = u.userid
    WHERE r.agronomist_id = :id
    ORDER BY r.created_at DESC
    LIMIT 10
";
$reviews = $DB->read($reviews_query, ['id' => $userid]);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($user->first_name . " " . $user->last_name); ?> - Profile</title>
    <style>
        .profile-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 12px;
            text-align: center;
            background-color: #f8fff8;
            font-family: 'Arial', sans-serif;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4CAF50;
            margin-bottom: 15px;
        }

        .profile-name {
            font-size: 24px;
            font-weight: bold;
        }

        .profile-username-with-rating {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .profile-username {
            color: #888;
            font-size: 16px;
        }

        .inline-stars .star {
            font-size: 16px;
            color: #ccc;
        }

        .inline-stars .star.filled {
            color: #4CAF50;
        }

        .profile-details {
            margin-top: 15px;
            text-align: left;
            padding-left: 20px;
        }

        .profile-details div {
            margin-bottom: 8px;
            font-size: 16px;
        }

        .label {
            font-weight: bold;
            color: #555;
        }

        #chat_btn, .service-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
        }

        #chat_btn:hover, .service-btn:hover {
            background-color: #45a049;
        }

        .service-btn {
            background-color: #2196F3;
        }

        .service-btn:hover {
            background-color: #1976D2;
        }

        .rating-box {
            text-align: center;
            margin: 20px auto;
            font-size: 16px;
        }

        .star {
            color: #ccc;
            font-size: 20px;
        }

        .star.filled {
            color: #4CAF50;
        }

        .reviews-container {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin: 20px auto;
            max-width: 600px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
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

        .see-more {
            background: none;
            border: none;
            color: #4CAF50;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<?php include("topbar.php"); ?>

<div class="profile-container">
    <img src="<?php echo $image; ?>" class="profile-image" alt="Profile picture of <?php echo htmlspecialchars($user->first_name); ?>" />
<div class="profile-name">
    <?php
        echo htmlspecialchars($user->first_name . " " . $user->last_name);
        if ($user->role === 'agronomist') {
            $verified_check = $DB->read("SELECT is_verified FROM agronomists WHERE userid = :id LIMIT 1", ['id' => $user->userid]);
            if (is_array($verified_check) && $verified_check[0]->is_verified == 1) {
                echo ' <span title="Verified Agronomist" style="color:green;">&#x2705;</span>';
            }
        }
    ?>
</div>


    <div class="profile-username-with-rating"><br>
        <span class="profile-username">@<?php echo htmlspecialchars($user->username); ?></span><br>

        <?php if ($user->role === 'agronomist' && $rating_data): ?>
            <span class="inline-stars">
                <?php
                $avg = round($rating_data->avg_rating ?? 0, 1);
                for ($i = 1; $i <= 5; $i++) {
                    $filled = $i <= round($avg);
                    echo "<span class='star " . ($filled ? "filled" : "") . "'>★</span>";
                }
                ?>
                <span style="font-size: 14px; margin-left: 5px; color: #666;"><?= $avg ?>/5</span>
            </span>
        <?php endif; ?>
    </div>

    <div class="profile-details">
        <div><span class="label">Phone:</span> <?php echo htmlspecialchars($user->phone_number); ?></div>
        <div><span class="label">Email:</span> <?php echo htmlspecialchars($user->email); ?></div>
        <div><span class="label">Role:</span> <?php echo ucfirst($user->role); ?></div>
        <div><span class="label">Location:</span> <?php echo htmlspecialchars($user->location); ?></div>

        <?php if ($_SESSION['userid'] != $user->userid): ?>
            <button id="chat_btn">💬 Chat</button>

            <?php
            $viewer = $DB->read("SELECT role FROM users WHERE userid = :id LIMIT 1", ['id' => $_SESSION['userid']]);
            $is_agronomist_data = $DB->read("SELECT is_verified, available FROM agronomists WHERE userid = :id LIMIT 1", ['id' => $user->userid]);

            if (
                is_array($viewer) && $viewer[0]->role === 'farmer' &&
                $user->role === 'agronomist' &&
                is_array($is_agronomist_data) &&
                $is_agronomist_data[0]->is_verified == 1 &&
                $is_agronomist_data[0]->available == 1
            ):
            ?>
                <br><br>
                <a href="request_service.php?agronomist_id=<?= $user->userid ?>" class="btn service-btn">📄 Request a Service</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<?php if ($user->role === 'agronomist' && $rating_data): ?>
    <div class="rating-box">
        <?php if (is_array($reviews) && count($reviews) > 0): ?>
        <div class="reviews-container">
            <h3 style="color: #4CAF50; margin-bottom: 10px;">What farmers say:</h3>

            <?php foreach ($reviews as $index => $review): ?>
                <div class="review" style="<?= $index >= 3 ? 'display:none;' : '' ?>" data-review>
                    <div class="reviewer"><?= htmlspecialchars($review->first_name . " " . $review->last_name) ?></div>
                    <div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $review->rating ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                        <span style="font-size: 12px; color: #888;"> - <?= date("M d, Y", strtotime($review->created_at)) ?></span>
                    </div>
                    <?php if (!empty($review->review)): ?>
                        <div class="review-text"><?= htmlspecialchars($review->review) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (count($reviews) > 3): ?>
                <button class="see-more" onclick="toggleReviews()">See more reviews</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div>

<script>
    document.getElementById("chat_btn")?.addEventListener("click", function () {
        const receiverId = "<?php echo $user->userid; ?>";
        localStorage.setItem("chat_with", receiverId);
        window.location.href = "chats.php";
    });

    let expanded = false;
    function toggleReviews() {
        document.querySelectorAll('[data-review]').forEach((el, index) => {
            if (index >= 3) el.style.display = expanded ? 'none' : 'block';
        });
        document.querySelector('.see-more').textContent = expanded ? "See more reviews" : "Show less";
        expanded = !expanded;
    }
</script>
</body>
</html>
