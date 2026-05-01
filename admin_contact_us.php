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

// Handle resolve request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_id'])) {
    $report_id = $_POST['resolve_id'];
    $DB->write("UPDATE reports SET status = 'resolved' WHERE id = :id", ['id' => $report_id]);
    header("Location: admin_contact_us.php" . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));
    exit;
}

// Filter by subject
$filter = $_GET['filter'] ?? '';
$allowed_filters = ['Suggestion', 'Report User', 'Other'];
$where_clause = "";
$params = [];

if (in_array($filter, $allowed_filters)) {
    $where_clause = "WHERE r.subject = :filter";
    $params['filter'] = $filter;
}

// Get reports (pending first, then resolved)
$query = "
    SELECT r.id, r.userid, r.reported_userid, r.subject, r.message, r.date, r.status,
           u.username AS sender_username, u.first_name AS sender_fname, u.last_name AS sender_lname,
           ru.username AS reported_username, ru.first_name AS reported_fname, ru.last_name AS reported_lname
    FROM reports r
    LEFT JOIN users u ON r.userid = u.userid
    LEFT JOIN users ru ON r.reported_userid = ru.userid
    $where_clause
    ORDER BY 
        CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END,
        r.date DESC
";

$reports = $DB->read($query, $params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Contact Reports</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 950px; margin: auto; background: white; padding: 20px; margin-top: 20px; border-radius: 8px; }
        h2 { color: #2e7d32; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f0f0f0; }
        tr:hover { background: #fafafa; }
        .status-pending { color: red; font-weight: bold; }
        .status-resolved { color: green; font-weight: bold; }
        .filter-form { margin-bottom: 15px; }
        .filter-form select { padding: 6px; border-radius: 4px; }
        a { color: #2a7ae2; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .resolve-btn { background: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; }
        .resolve-btn:hover { background: #45a049; }
    </style>
</head>
<body>

<?php include("admin_topbar.php"); ?>

<div class="container">
    <h2>Contact Reports</h2>

    <form class="filter-form" method="get">
        <label for="filter">Filter by subject:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="">All</option>
            <?php foreach ($allowed_filters as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>" <?= ($filter === $option) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>From</th>
                <th>Against</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($reports): ?>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?= htmlspecialchars($report->id) ?></td>
                        <td>
                            <?php if ($report->sender_username): ?>
                                <a href="admin_profile_view.php?userid=<?= urlencode($report->userid) ?>">
                                    <?= htmlspecialchars($report->sender_username) ?>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($report->reported_username): ?>
                                <a href="admin_profile_view.php?userid=<?= urlencode($report->reported_userid) ?>">
                                    <?= htmlspecialchars($report->reported_username) ?>
                                </a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($report->subject) ?></td>
                        <td><?= nl2br(htmlspecialchars($report->message)) ?></td>
                        <td><?= htmlspecialchars($report->date) ?></td>
                        <td class="status-<?= strtolower($report->status) ?>"><?= ucfirst($report->status) ?></td>
                        <td>
                            <?php if ($report->status === 'pending'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="resolve_id" value="<?= $report->id ?>">
                                    <button type="submit" class="resolve-btn">Resolve</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;color:gray;">No reports found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
