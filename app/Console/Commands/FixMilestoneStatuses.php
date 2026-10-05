<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ThesisProject;
use App\Models\StudentMilestone;
use App\Models\MilestoneTemplate;

class FixMilestoneStatuses extends Command
{
    protected $signature = 'milestones:fix-statuses';
    protected $description = 'Correct milestone statuses so each student has exactly one in_progress milestone, with past ones approved and future ones not_started.';

    public function handle()
    {
        $templates = MilestoneTemplate::orderBy('order')->get();
        $theses    = ThesisProject::whereIn('status', ['active', 'proposal_passed', 'internal_passed'])->get();

        $fixed = 0;

        foreach ($theses as $thesis) {
            $milestones = StudentMilestone::where('thesis_project_id', $thesis->id)
                ->get()
                ->keyBy('milestone_template_id');

            $foundActive = false;

            foreach ($templates as $template) {
                $sm = $milestones->get($template->id);
                if (!$sm) continue;

                $isApproved = $sm->status === 'approved';

                if ($isApproved) {
                    // Already correct — keep approved
                    continue;
                }

                if (!$foundActive) {
                    // First non-approved → this is the active one
                    if ($sm->status !== 'in_progress') {
                        $sm->update(['status' => 'in_progress']);
                        $fixed++;
                    }
                    $foundActive = true;
                } else {
                    // All subsequent → must be not_started
                    if ($sm->status !== 'not_started') {
                        $sm->update(['status' => 'not_started']);
                        $fixed++;
                    }
                }
            }
        }

        $this->info("Done. Fixed {$fixed} milestone records across {$theses->count()} thesis projects.");
    }
}
