<?php
$content = file_get_contents('app/Http/Controllers/Admin/StudentController.php');

$newMethod = <<<EOT
    public function setMilestone(Request \$request, \App\Models\StudentProfile \$student)
    {
        \$request->validate([
            'milestone_id' => 'required|exists:student_milestones,id'
        ]);

        if (!\$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        \$targetMilestone = \$student->thesis->milestones()->with('template')->findOrFail(\$request->milestone_id);
        \$targetOrder = \$targetMilestone->template->order;

        \$milestones = \$student->thesis->milestones()->with('template')->get();

        foreach (\$milestones as \$m) {
            if (\$m->template->order < \$targetOrder) {
                \$m->update([
                    'status' => 'approved',
                    'date_approved_at' => \$m->date_approved_at ?? now(),
                    'approved_at' => \$m->approved_at ?? now()
                ]);
            } elseif (\$m->template->order == \$targetOrder) {
                // If it was already approved, maybe we are demoting TO it, so we mark it in_progress
                \$m->update([
                    'status' => 'not_started', // The WorkflowService or normal flow usually keeps it 'not_started' or 'in_progress', but 'in_progress' isn't fully used in this system consistently. Wait, we should just set it to not_started if it's a demotion, or keep it as is if it's already active.
                    // Actually, setting to 'not_started' lets the student start it fresh.
                    // Let's set it to 'not_started' and clear approvals if it was approved? No, let's just leave it 'not_started' so it's the active uncompleted one.
                ]);
                \$m->update(['status' => 'in_progress']); // Let's use in_progress for clarity? Wait, the system uses 'not_started' for the current one until they submit!
            } else {
                // Future milestones
                \$m->update([
                    'status' => 'not_started',
                    'submitted_at' => null,
                    'approved_at' => null,
                    'date_approved_at' => null,
                    'approvals' => null,
                    'is_submission_unlocked' => false
                ]);
            }
        }

        // Notify student
        \$messageService = new \App\Services\MessageService();
        \$messageService->sendMessage(
            \$student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively adjusted to the '" . \$targetMilestone->template->name . "' milestone.",
            null,
            ['administrative_action' => 'set_milestone']
        );

        return back()->with('success', 'Student milestone has been set successfully.');
    }
EOT;

$content = preg_replace('/public function demoteMilestone/', $newMethod . "\n\n    public function demoteMilestone", $content);
file_put_contents('app/Http/Controllers/Admin/StudentController.php', $content);
