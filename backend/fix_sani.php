<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$milestone = App\Models\StudentMilestone::find('01a0fe61-1b58-705e-a927-4aae6849a275');
if ($milestone) {
    $milestone->status = 'partially_approved';
    $milestone->save();
    
    // Also delete any newer milestones that were auto-created (like progress report 2)
    App\Models\StudentMilestone::where('thesis_project_id', $milestone->thesis_project_id)
        ->where('created_at', '>', $milestone->created_at)
        ->delete();
        
    echo "Fixed Sani's milestone.\n";
}
