<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::whereHas('roles', function($q) { $q->where('name', 'Supervisor'); })->get(['id', 'name', 'email']);
foreach($users as $user) {
    echo $user->name . " -> " . $user->email . "\n";
}
