<?php

$url = 'http://103.59.95.161:3003/ws/live2.php';

echo "Getting token...\n";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'act' => 'GetToken',
    'username' => 'BudiPrasetyo',
    'password' => 'jayahangtuah2023!'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res_raw = curl_exec($ch);
$res = json_decode($res_raw, true);
$token = $res['data']['token'] ?? null;

if (!$token) {
    die("Token failed: " . $res_raw . "\n");
}

echo "TOKEN: $token\n";

$acts = ['GetJenisKebutuhanKhusus', 'GetKebutuhanKhusus', 'ListAction'];

foreach ($acts as $act) {
    echo "Testing Action: $act...\n";
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'act' => $act,
        'token' => $token,
        'limit' => 1
    ]));
    $res = curl_exec($ch);
    echo "RES ($act): " . ($res ?: 'TIMEOUT/EMPTY') . "\n";
    echo "-------------------\n";
}

curl_close($ch);
