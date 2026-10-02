<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$students = User::role('Student')->get();
$count = $students->count();

echo "Deleting $count students...\n";

foreach($students as $student) {
    DB::transaction(function() use ($student) {
        DB::table('submissions')->where('submitted_by', $student->id)->delete();
        DB::table('inbox_messages')->where('sender_id', $student->id)->orWhere('recipient_id', $student->id)->delete();
        
        $profile = DB::table('student_profiles')->where('user_id', $student->id)->first();
        if ($profile) {
            DB::table('student_milestones')->where('student_profile_id', $profile->id)->delete();
            DB::table('thesis_projects')->where('student_profile_id', $profile->id)->delete();
            DB::table('supervision_assignments')->where('student_profile_id', $profile->id)->delete();
            DB::table('examiner_assignments')->where('student_profile_id', $profile->id)->delete();
            DB::table('student_profiles')->where('id', $profile->id)->delete();
        }
        
        DB::table('model_has_roles')->where('model_id', $student->id)->delete();
        DB::table('users')->where('id', $student->id)->delete();
    });
}
echo "Finished deleting students.\n";
