<?php
require __DIR__.'/backend/vendor/autoload.php';
$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'malhassan@noun.edu.ng')->first();
if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make('Password123!');
    $user->save();
    echo "Password reset successful.";
} else {
    echo "User not found.";
}
