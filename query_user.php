<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::where('name', 'ilike', '%Mukthar%')->orWhere('name', 'ilike', '%alhassan%')->get(['id', 'name', 'email']);
echo json_encode($users);
