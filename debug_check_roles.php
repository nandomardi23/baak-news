<?php

use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking existing roles...\n";

$roles = Role::all();

if ($roles->isEmpty()) {
    echo "No roles found in database.\n";
} else {
    foreach ($roles as $role) {
        echo "- Role: {$role->name} (Guard: {$role->guard_name}, ID: {$role->id})\n";
    }
}

echo "\nChecking for Neo Import targets:\n";
$targets = ['Dosen', 'Mahasiswa', 'Tenaga Kependidikan', 'Administrator PT', 'Kaprodi'];
foreach ($targets as $target) {
    $exists = Role::where('name', $target)->exists();
    echo "- $target: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}
