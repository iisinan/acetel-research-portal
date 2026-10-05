<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = new App\Models\User();
// If we set plain password vs hashed password:
$user->password = 'testpass123';
echo "Plain set: " . substr($user->password, 0, 10) . "..." . PHP_EOL;
$matches1 = Illuminate\Support\Facades\Hash::check('testpass123', $user->password);
echo "Hash::check plain: " . ($matches1 ? 'YES' : 'NO') . PHP_EOL;

$user2 = new App\Models\User();
$user2->password = Illuminate\Support\Facades\Hash::make('testpass123');
echo "Hashed set: " . substr($user2->password, 0, 10) . "..." . PHP_EOL;
$matches2 = Illuminate\Support\Facades\Hash::check('testpass123', $user2->password);
echo "Hash::check hashed: " . ($matches2 ? 'YES' : 'NO') . PHP_EOL;
