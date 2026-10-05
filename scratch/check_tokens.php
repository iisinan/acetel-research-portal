<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tokens = Illuminate\Support\Facades\DB::table('password_reset_tokens')->get();
echo "password_reset_tokens count: " . $tokens->count() . PHP_EOL;
foreach ($tokens as $t) {
    echo "Email: " . $t->email . " | Created at: " . $t->created_at . " | Token (hashed): " . substr($t->token, 0, 15) . "..." . PHP_EOL;
}
