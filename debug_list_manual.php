<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use GuzzleHttp\Client;
use App\Services\Setting;

$url = App\Models\Setting::getValue('neo_feeder_url', '');
$username = App\Models\Setting::getValue('neo_feeder_username', '');
$password = App\Models\Setting::getValue('neo_feeder_password', '');

$client = new Client(['timeout' => 5]);

try {
    echo "Getting token...\n";
    $response = $client->post($url, [
        'json' => [
            'act' => 'GetToken',
            'username' => $username,
            'password' => $password,
        ],
    ]);
    $data = json_decode($response->getBody()->getContents(), true);
    $token = $data['data']['token'] ?? null;
    
    if (!$token) {
        die("Token failed\n");
    }
    
    echo "Testing ListAction...\n";
    $response = $client->post($url, [
        'json' => [
            'act' => 'ListAction',
            'token' => $token
        ],
    ]);
    $data = json_decode($response->getBody()->getContents(), true);
    if (isset($data['data'])) {
        foreach ($data['data'] as $item) {
            echo $item['act'] . "\n";
        }
    } else {
        echo "ListAction not found. Error: " . ($data['error_desc'] ?? 'Unknown error') . "\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
