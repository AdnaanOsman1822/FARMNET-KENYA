<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->message) || trim($data->message) == "") {
    echo json_encode(["reply" => "❌ No message received. Please ask something."]);
    exit;
}

$message = trim($data->message);

$ch = curl_init("http://127.0.0.1:5000/get-response");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["message" => $message]));

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_status !== 200) {
    echo json_encode(["reply" => "🤖 Farmbot is currently offline. Please try again later."]);
    exit;
}

$bot_response = json_decode($response, true);
$reply = $bot_response["response"] ?? "⚠️ Farmbot didn't return a valid response.";

echo json_encode(["reply" => $reply]);
