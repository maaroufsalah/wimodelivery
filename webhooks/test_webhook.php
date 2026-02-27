<?php
header('Content-Type: application/json; charset=utf-8');
$payload = json_encode([
    "reference" => "WMD-2438",
    "status"    => "REFUSE",
    "reporter"  => "26-02-2026",
    "comment"   => "Test local"
], JSON_UNESCAPED_UNICODE);

$secret = "WIMO_WH_7a3f8k2m9x4q1n6p0r5s8t3w2v9z4b1c";

$ch = curl_init('https://wimodelivery.com/webhooks/chamelexpress.php');

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Special-Token: ' . $secret
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>HTTP Code: " . $httpCode . "</h3>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";
echo "<hr><small>Voir logs/webhook_chamel.log pour le détail</small>";
