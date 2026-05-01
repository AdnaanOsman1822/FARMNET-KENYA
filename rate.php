<?php
session_start();
if (!isset($_SESSION['userid'])) die("Login first.");

$request_id = $_POST['request_id'] ?? null;
$agronomist_id = $_POST['agronomist_id'] ?? null;

if (!$request_id || !$agronomist_id) die("Invalid access.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rate Service - Farmnet Kenya</title>
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
            max-width: 600px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .stars {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .star {
            font-size: 32px;
            cursor: pointer;
            transition: color 0.3s;
            color: #ccc;
        }

        .star.selected,
        .star:hover,
        .star:hover ~ .star {
            color: #4CAF50;
        }

        textarea {
            width: 100%;
            padding: 10px;
            font-family: 'Jura', sans-serif;
            margin-top: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-family: 'Jura', sans-serif;
            margin-top: 20px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #388e3c;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>⭐ Rate This Service</h2>
    <form method="POST" action="submit_rating.php">
        <input type="hidden" name="request_id" value="<?= $request_id ?>">
        <input type="hidden" name="agronomist_id" value="<?= $agronomist_id ?>">
        <input type="hidden" name="rating" id="rating" value="">

        <div class="stars" id="star-container">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star" data-value="<?= $i ?>">★</span>
            <?php endfor; ?>
        </div>

        <textarea name="review" rows="4" placeholder="Write a short review (optional)..."></textarea>
        <br>
        <button type="submit" class="submit-btn">Submit Rating</button>
    </form>
</div>

<script>
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating');

    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            ratingInput.value = star.dataset.value;

            stars.forEach((s, i) => {
                s.classList.toggle('selected', i < star.dataset.value);
            });
        });

        star.addEventListener('mouseover', () => {
            stars.forEach((s, i) => {
                s.style.color = (i < star.dataset.value) ? '#4CAF50' : '#ccc';
            });
        });

        star.addEventListener('mouseout', () => {
            let selected = parseInt(ratingInput.value);
            stars.forEach((s, i) => {
                s.style.color = (i < selected) ? '#4CAF50' : '#ccc';
            });
        });
    });
</script>

</body>
</html>
