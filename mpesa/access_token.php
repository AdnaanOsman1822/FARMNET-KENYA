<?php
function getAccessToken() {
    $consumer_key = "ISQXTAGNXTrMPVJ3yqkY1Mzet1Pk1cc9Cfg38G6IKxPMKUSO";
    $consumer_secret = "wMQIC0EFpvZevoEltJpHntP5eRldjRjmv5pacPho1Nu79NStA1O43iAt1tGb5dzH";

    $credentials = base64_encode($consumer_key . ':' . $consumer_secret);
    $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic $credentials"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response);
    return $result->access_token ?? null;
}
