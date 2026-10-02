<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\StudentProfile;
use App\Models\User;

$profiles = StudentProfile::all();
foreach($profiles as $profile) {
    $user = User::find($profile->user_id);
    echo "Profile ID: {$profile->id}, User ID: {$profile->user_id}, Name: " . ($user ? $user->name : 'N/A') . ", Roles: " . ($user ? $user->roles->pluck('name')->join(', ') : 'N/A') . "\n";
}

