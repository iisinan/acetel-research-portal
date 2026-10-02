<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$json = file_get_contents('/Users/sinan/.gemini/antigravity/brain/78467d7d-74b5-407c-a6d0-69e95f786748/scratch/clean_supervisors.json');
$supervisors = json_decode($json, true);

// Fetch existing emails
$existingEmails = User::pluck('email')->toArray();
$existingEmails = array_map('strtolower', $existingEmails);

$usersToInsert = [];
$emailsToFetchLater = [];
$now = now();
$password = Hash::make('AcetelSupervisor123#');

$skipped = 0;

foreach ($supervisors as $sup) {
    $name = trim($sup['name']);
    $email = strtolower(trim($sup['email']));
    $email = rtrim($email, ';)');
    
    if (empty($email)) {
        $slug = Str::slug(str_replace(['Prof', 'Dr', 'Miss', 'Mr', 'Mrs'], '', $name));
        if (empty($slug)) { $slug = 'supervisor_' . uniqid(); }
        $email = $slug . '@acetel.dummy.edu.ng';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $skipped++;
        continue;
    }
    
    if (!in_array($email, $existingEmails)) {
        $usersToInsert[] = [
            'id' => Str::uuid()->toString(),
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $existingEmails[] = $email;
        $emailsToFetchLater[] = $email;
    }
}

if (!empty($usersToInsert)) {
    User::insert($usersToInsert);
    echo "Inserted " . count($usersToInsert) . " users.\n";
    
    // Now fetch them
    $newUsers = User::whereIn('email', $emailsToFetchLater)->get();
    
    $rolesToInsert = [];
    foreach ($newUsers as $u) {
        $rolesToInsert[] = [
            'role_id' => 4, // Supervisor
            'model_type' => 'App\Models\User',
            'model_id' => $u->id
        ];
    }
    
    DB::table('model_has_roles')->insertOrIgnore($rolesToInsert);
    echo "Assigned roles to " . count($rolesToInsert) . " users.\n";
} else {
    echo "No new users to insert.\n";
}

echo "Skipped: $skipped\n";

