<?php
$content = file_get_contents('app/Http/Controllers/MilestoneController.php');

$search = <<<EOT
            if (\$milestones->isEmpty()) {
                \$thesis->syncMilestones();
                \$milestones = \$thesis->milestones()
                    ->with(['template', 'submissions.submittedBy', 'messages.sender', 'unlockedBy'])
                    ->get()
                    ->sortBy('template.order');
            }
EOT;

$replace = <<<EOT
            \$templatesCount = \\App\\Models\\MilestoneTemplate::whereNull('program_id')
                ->orWhere('program_id', \$thesis->student->program_id ?? null)
                ->count();

            if (\$milestones->count() < \$templatesCount || \$milestones->isEmpty()) {
                \$thesis->syncMilestones();
                \$milestones = \$thesis->milestones()
                    ->with(['template', 'submissions.submittedBy', 'messages.sender', 'unlockedBy'])
                    ->get()
                    ->sortBy('template.order');
            }
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/MilestoneController.php', $content);
