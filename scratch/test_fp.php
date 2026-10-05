<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default mailer: " . config('mail.default') . PHP_EOL;
echo "Mail host: " . config('mail.mailers.smtp.host') . PHP_EOL;
echo "Mail port: " . config('mail.mailers.smtp.port') . PHP_EOL;
echo "Mail encryption: " . config('mail.mailers.smtp.encryption') . PHP_EOL;
echo "Mail username: " . config('mail.mailers.smtp.username') . PHP_EOL;
echo "Mail from address: " . config('mail.from.address') . PHP_EOL;
echo "App URL: " . config('app.url') . PHP_EOL;
echo "Has password_reset_tokens table? " . (Illuminate\Support\Facades\Schema::hasTable('password_reset_tokens') ? 'YES' : 'NO') . PHP_EOL;

$admin = App\Models\User::first();
echo "First user: " . ($admin ? $admin->email : 'NONE') . PHP_EOL;

if ($admin) {
    try {
        $status = Illuminate\Support\Facades\Password::sendResetLink(['email' => $admin->email]);
        echo "Password::sendResetLink status: " . $status . PHP_EOL;
    } catch (\Throwable $e) {
        echo "Password::sendResetLink EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
}
