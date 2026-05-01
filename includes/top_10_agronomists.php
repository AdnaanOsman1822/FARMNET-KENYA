<?php
require_once("../classes/autoload.php");
$DB = new Database();

$query = "
    SELECT u.userid, u.first_name, u.last_name, u.username, u.location, u.role, u.image,
           AVG(r.rating) as avg_rating,
           COUNT(r.rating) as rating_count,
           a.is_verified
    FROM users u
    JOIN agronomists a ON u.userid = a.userid
    JOIN ratings r ON a.userid = r.agronomist_id
    WHERE u.role = 'agronomist' AND u.suspended = 0 AND a.is_verified = 1
    GROUP BY u.userid
    HAVING rating_count > 0
    ORDER BY avg_rating DESC
    LIMIT 10
";

$results = $DB->read($query);

if (is_array($results) && count($results) > 0) {
    echo "<h3 style='text-align:center; margin-bottom:10px;'>🏆 Top 10 Agronomists</h3>";
    echo '<div class="horizontal-scroll">';

$rank = 1;
foreach ($results as $row) {
    $default_image = "ui/images/user.jpg";
    $image_src = file_exists(__DIR__ . "/../" . $row->image) ? $row->image : $default_image;
    $verified_tick = $row->is_verified ? ' <span title="Verified" style="color:green;">&#x2705;</span>' : '';

    echo '
    <div class="user-card" onclick="go_to_profile(\'' . $row->userid . '\')">
        <div class="rank-number">' . $rank . '</div>
        <img src="' . $image_src . '" class="user-image" />
        <div class="user-info">
            <div class="username">@' . htmlspecialchars($row->username) . '</div>
            <div class="name-username"><strong>' . htmlspecialchars($row->first_name . ' ' . $row->last_name) . $verified_tick . '</strong></div>
            <div class="location">📍 ' . htmlspecialchars($row->location) . '</div>
            <div class="location"><strong>⭐ ' . number_format($row->avg_rating, 1) . '</strong></div>
        </div>
    </div>';
    $rank++;
}

    echo "</div>";
}
?>
