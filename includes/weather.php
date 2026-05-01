<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once("../classes/autoload.php");
$DB = new Database();

// Default fallback
$lat = null;
$lon = null;
$county_name = "Nairobi"; // default if everything fails

// Try JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (is_array($data)) {
    // If browser gave GPS
    if (isset($data['location']['lat']) && isset($data['location']['lon'])) {
        $lat = floatval($data['location']['lat']);
        $lon = floatval($data['location']['lon']);
    }

    // If fallback to DB location 
    if (isset($data['location']['city'])) {
        $county_name = $data['location']['city'];
    }
}

// If no lat/lon, try to get from DB
if (!$lat || !$lon) {
    $userid = $_SESSION['userid'] ?? null;
    if ($userid) {
        $query = "SELECT location FROM users WHERE userid = :uid LIMIT 1";
        $result = $DB->read($query, ['uid' => $userid]);

        if ($result && isset($result[0]->location)) {
            $county_name = $result[0]->location;
        }
    }
}

// Build API URL
$use_coords = ($lat && $lon);
$api_key = "8839c461ef2e422b9d1191547252207"; 
$api_url = $use_coords
    ? "https://api.weatherapi.com/v1/forecast.json?key={$api_key}&q={$lat},{$lon}&days=7"
    : "https://api.weatherapi.com/v1/forecast.json?key={$api_key}&q=" . urlencode($county_name) . "&days=7";

// Fetch from Weather API
$response = file_get_contents($api_url);
if ($response === FALSE) {
    echo json_encode(['error' => 'Failed to fetch weather']);
    die;
}

echo $response;
