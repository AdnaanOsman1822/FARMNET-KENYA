<?php
require_once("../classes/autoload.php");
$DB = new Database();

$rows = $DB->read("SELECT * FROM users WHERE suspended = 1 ORDER BY userid DESC LIMIT 50");
echo json_encode($rows ?: []);
