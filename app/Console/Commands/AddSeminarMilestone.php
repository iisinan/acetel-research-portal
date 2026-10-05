<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MilestoneTemplate;
use App\Models\ThesisProject;
use App\Models\StudentMilestone;

class AddSeminarMilestone extends Command
{
    protected $signature = 'milestones:add-seminar';
    protected $description = 'Adds Seminar as a course milestone to all existing thesis projects';

    public function handle()
    {
        // 1. Ensure templates are up to date
        $this->call('db:seed', ['--class' => 'MilestoneTemplateSeeder', '--force' => true]);
        
        $template = MilestoneTemplate::where('name', 'Seminar as a course')->first();
        if (!$template) {
            $this->error("Template not found after seeding.");
            return;
        }

        // 2. Add to existing thesis projects if they don't have it
        $projects = ThesisProject::all();
        foreach ($projects as $project) {
            $exists = StudentMilestone::where('thesis_project_id', $project->id)
                ->where('milestone_template_id', $template->id)
                ->exists();

            if (!$exists) {
                StudentMilestone::create([
                    'thesis_project_id' => $project->id,
                    'milestone_template_id' => $template->id,
                    'status' => 'not_started',
                ]);
            }
        }

        $this->info("Seminar milestone added successfully to all projects.");
    }
}
