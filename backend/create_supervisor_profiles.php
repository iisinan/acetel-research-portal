<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SupervisorProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    $supervisors = User::role('Supervisor')->get();
    $profilesToInsert = [];

    // Fetch existing profiles to avoid duplicates
    $existingUserIds = SupervisorProfile::pluck('user_id')->toArray();

    foreach ($supervisors as $user) {
        if (!in_array($user->id, $existingUserIds)) {
            $profilesToInsert[] = [
                'id' => Str::uuid()->toString(),
                'user_id' => $user->id,
                'staff_id' => 'STAFF-' . strtoupper(Str::random(8)),
                'max_students' => 10,
                'current_load' => 0,
                'specialization' => 'General',
                'rank' => 'Lecturer',
                'max_load' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    if (!empty($profilesToInsert)) {
        DB::table('supervisor_profiles')->insert($profilesToInsert);
        file_put_contents('profile_insert_result.txt', "Created " . count($profilesToInsert) . " supervisor profiles.\n");
    } else {
        file_put_contents('profile_insert_result.txt', "No profiles needed to be created.\n");
    }
} catch (\Exception $e) {
    file_put_contents('profile_insert_result.txt', "ERROR: " . $e->getMessage() . "\n");
}

