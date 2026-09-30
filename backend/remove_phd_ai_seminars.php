<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;
use App\Models\Program;
use App\Models\StudentProfile;

$programs = Program::where(function($q) {
    $q->where('name', 'ILIKE', '%PhD%')
      ->orWhere('name', 'ILIKE', '%Doctor of Philosophy%');
})->where('name', 'ILIKE', '%Artificial Intelligence%')
  ->get();

if ($programs->isEmpty()) {
    echo "No PhD Artificial Intelligence programs found.\n";
    exit;
}

$programIds = $programs->pluck('id')->toArray();
$students = StudentProfile::whereIn('program_id', $programIds)->get();

if ($students->isEmpty()) {
    echo "No students found in PhD Artificial Intelligence.\n";
    exit;
}

$thesisIds = $students->pluck('thesis_project_id')->filter()->unique()->toArray();

$template = MilestoneTemplate::where('slug', 'seminar_as_a_course')->first();

if (!$template) {
    echo "Template not found for slug: seminar_as_a_course\n";
    exit;
}

$affected = StudentMilestone::where('milestone_template_id', $template->id)
    ->whereIn('thesis_project_id', $thesisIds)
    ->get();

echo "Found {$affected->count()} seminar_as_a_course milestones to remove for PhD AI students.\n";

foreach ($affected as $m) {
    $wasInProgress = ($m->status === 'in_progress');
    $thesisId = $m->thesis_project_id;
    $m->delete();
    echo "  Deleted milestone ID {$m->id} for thesis {$thesisId}\n";
    
    // If this milestone was the active one, we need to promote the next one
    if ($wasInProgress) {
        $nextMilestone = StudentMilestone::where('thesis_project_id', $thesisId)
            ->where('status', 'not_started')
            ->whereHas('template', function($q) use ($template) {
                $q->where('order', '>', $template->order);
            })
            ->with('template')
            ->get()
            ->sortBy('template.order')
            ->first();
            
        if ($nextMilestone) {
            $nextMilestone->update([
                'status' => 'in_progress',
                'due_date' => now()->addDays(30)
            ]);
            echo "  -> Promoted next milestone '{$nextMilestone->template->slug}' to in_progress for thesis {$thesisId}\n";
        }
    }
}

echo "\nDone!\n";
