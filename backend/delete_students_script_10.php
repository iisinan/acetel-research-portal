<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

$profiles = StudentProfile::all();
$count = $profiles->count();

echo "Deleting $count students based on profiles...\n";

foreach($profiles as $profile) {
    try {
        $studentId = $profile->user_id;
        DB::transaction(function() use ($profile, $studentId) {
            DB::table('audit_logs')->where('user_id', $studentId)->delete();
            DB::table('submissions')->where('submitted_by', $studentId)->delete();
            DB::table('evaluations')->where('evaluator_id', $studentId)->delete();

            // Find thesis project
            $project = DB::table('thesis_projects')->where('student_profile_id', $profile->id)->first();
            if ($project) {
                DB::table('supervision_assignments')->where('thesis_project_id', $project->id)->delete();
                DB::table('student_milestones')->where('thesis_project_id', $project->id)->delete();
                DB::table('examiner_assignments')->where('thesis_project_id', $project->id)->delete(); 
                DB::table('thesis_projects')->where('id', $project->id)->delete();
            }

            DB::table('student_profiles')->where('id', $profile->id)->delete();
            
            DB::table('model_has_roles')->where('model_id', $studentId)->delete();
            DB::table('users')->where('id', $studentId)->delete();
        });
        echo "Deleted student: {$studentId}\n";
    } catch (\Exception $e) {
        echo "Failed to delete {$studentId}: " . $e->getMessage() . "\n";
    }
}
