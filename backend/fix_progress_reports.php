<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;

// Fix ALL progress_report_1 and progress_report_2 milestones that are not yet approved
// (catches both 'not_started' and 'in_progress' statuses)
$slugs = ['progress_report_1', 'progress_report_2'];

foreach ($slugs as $slug) {
    $template = MilestoneTemplate::where('slug', $slug)->first();
    if (!$template) {
        echo "Template not found for slug: {$slug}\n";
        continue;
    }

    $affected = StudentMilestone::where('milestone_template_id', $template->id)
        ->whereIn('status', ['not_started', 'in_progress', 'pending'])
        ->get();

    echo "Found {$affected->count()} non-approved milestones for {$slug}\n";

    foreach ($affected as $m) {
        $m->update([
            'status'           => 'approved',
            'submitted_at'     => now(),
            'date_approved_at' => now(),
            'due_date'         => null,
        ]);
        echo "  Fixed milestone ID {$m->id} (was: {$m->getOriginal('status')}) -> approved\n";
    }
}

// Also recalculate the active milestone for all affected thesis projects:
// Find any thesis project where progress_report_1 is now approved but a later milestone
// is stuck as 'in_progress' that should now move to 'in_progress'
echo "\nRecalculating active milestone for affected thesis projects...\n";

$pr1 = MilestoneTemplate::where('slug', 'progress_report_1')->first();
if ($pr1) {
    $affectedTheses = StudentMilestone::where('milestone_template_id', $pr1->id)
        ->where('status', 'approved')
        ->pluck('thesis_project_id');

    foreach ($affectedTheses as $thesisId) {
        // Find all milestones for this thesis ordered by template order
        $milestones = StudentMilestone::where('thesis_project_id', $thesisId)
            ->with('template')
            ->get()
            ->sortBy('template.order');

        $foundActive = false;
        foreach ($milestones as $m) {
            if ($m->status !== 'approved') {
                if (!$foundActive) {
                    // This should be the active/in_progress milestone
                    if ($m->status !== 'in_progress') {
                        $m->update(['status' => 'in_progress', 'due_date' => now()->addDays(30)]);
                        echo "  Set thesis {$thesisId} milestone '{$m->template->slug}' to in_progress\n";
                    }
                    $foundActive = true;
                } else {
                    // These should be not_started
                    if ($m->status === 'in_progress') {
                        $m->update(['status' => 'not_started']);
                        echo "  Set thesis {$thesisId} milestone '{$m->template->slug}' back to not_started\n";
                    }
                }
            }
        }
    }
}

echo "\nDone!\n";
