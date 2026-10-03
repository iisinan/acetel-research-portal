<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = new \App\Models\User();
$user->name = 'Sinan Ismaila';
$user->email = 'sinanismailaidris@gmail.com';

try {
    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeUser($user, 'TestPass123!'));
    echo "Email sent successfully!\n";
} catch (\Exception $e) {
    echo "Error sending email: " . $e->getMessage() . "\n";
}
