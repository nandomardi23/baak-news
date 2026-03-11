<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t = new App\Services\NeoFeederService();
$res = $t->request('GetListPenugasanDosen', ['limit' => 2]);
print_r($res);
