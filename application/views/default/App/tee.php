<?php
global $con;
include get_file("files/sql/get/os_settings");


function sendFCMModern($firebase_token, $title, $body) {
    global $set_logo;

    $serviceAccountPath =  "fcm.json"; // 🔑 المسار الصحيح
    $credentials = json_decode(file_get_contents($serviceAccountPath), true);
    $project_id = $credentials['project_id'];

    // 🔑 إصلاح مشكلة الـ \n
    $privateKey = str_replace("\\n", "\n", $credentials['private_key']);

    // JWT Header + Claim
    $header = ['alg'=>'RS256','typ'=>'JWT'];
    $now = time();
    $claimSet = [
        'iss' => $credentials['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];

    $header_enc = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $claim_enc  = rtrim(strtr(base64_encode(json_encode($claimSet)), '+/', '-_'), '=');
    $data = "$header_enc.$claim_enc";

    openssl_sign($data, $signature, $privateKey, 'SHA256');
    $signature_enc = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $jwt = "$data.$signature_enc";

    // 🟢 Access Token
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]));
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if(!isset($response['access_token'])) {
        return json_encode(["error" => "Failed to get access token", "details" => $response]);
    }
    $access_token = $response['access_token'];

    // 🔗 Logo
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
        || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . $host;
    $logo_path = !empty($set_logo) ? 'uploads/'.$set_logo : 'uploads/default-logo.png';
    $logo_url  = $baseUrl . '/' . $logo_path;

    // 📩 الرسالة
    $message = [
        'message' => [
            'token' => $firebase_token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
                'image' => $logo_url
            ]
        ]
    ];

    // 🚀 إرسال
    $ch = curl_init("https://fcm.googleapis.com/v1/projects/$project_id/messages:send");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    $result = curl_exec($ch);

    if ($result === false) {
        $result = json_encode(["error" => curl_error($ch)]);
    }
    curl_close($ch);

    return $result;
}
