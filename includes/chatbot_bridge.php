<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->message) || trim($data->message) === "") {
    echo json_encode(["response" => "No message received"]);
    exit;
}

$message = $data->message;

$ch = curl_init("http://localhost:5000/get-response");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["message" => $message]));

$response = curl_exec($ch);
curl_close($ch);

if ($response === false || empty($response)) {
    echo json_encode(["response" => "🤖 Bot is offline or not responding"]);
} else {
    echo $response;
}
?>
