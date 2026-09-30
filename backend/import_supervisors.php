<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$json = file_get_contents('/Users/sinan/.gemini/antigravity/brain/78467d7d-74b5-407c-a6d0-69e95f786748/scratch/clean_supervisors.json');
$supervisors = json_decode($json, true);

$count = 0;
$skipped = 0;

foreach ($supervisors as $sup) {
    $name = trim($sup['name']);
    $email = strtolower(trim($sup['email']));
    
    // Clean up email
    $email = rtrim($email, ';)');
    
    if (empty($email)) {
        $slug = Str::slug(str_replace(['Prof', 'Dr', 'Miss', 'Mr', 'Mrs'], '', $name));
        if (empty($slug)) { $slug = 'supervisor_' . uniqid(); }
        $email = $slug . '@acetel.dummy.edu.ng';
    }
    
    // Validate email format just in case
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Skipping invalid email: $email\n";
        $skipped++;
        continue;
    }
    
    $user = User::where('email', $email)->first();
    if (!$user) {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('AcetelSupervisor123#'),
            'email_verified_at' => now(),
        ]);
        
        $user->assignRole('Supervisor');
        $count++;
    } else {
        if (!$user->hasRole('Supervisor')) {
            $user->assignRole('Supervisor');
            $count++;
        }
    }
}

echo "Successfully added/updated $count supervisors. Skipped: $skipped\n";
