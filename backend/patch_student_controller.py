import re

with open('backend/app/Http/Controllers/Admin/StudentController.php', 'r') as f:
    content = f.read()

new_methods = """
    /**
     * Promote a student to the next milestone.
     */
    public function promoteMilestone(Request $request, StudentProfile $student)
    {
        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $milestones = $student->thesis->milestones()->with('template')->get()->sortBy('template.order');
        $current = $milestones->firstWhere('status', 'in_progress');

        if (!$current) {
            // Find first not_started
            $current = $milestones->firstWhere('status', 'not_started');
        }

        if (!$current) {
            return back()->with('error', 'No active milestone found to promote.');
        }

        // Approve current
        $current->update([
            'status' => 'approved',
            'date_approved_at' => now()
        ]);

        // Find next
        $next = $milestones->where('template.order', '>', $current->template->order)->first();
        if ($next) {
            $next->update([
                'status' => 'in_progress',
                'due_date' => now()->addDays(30)
            ]);
        }

        // Notify student
        $messageService = new \\App\\Services\\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively advanced. You have been promoted past the '" . $current->template->name . "' milestone.",
            null,
            ['administrative_action' => 'promotion']
        );

        return back()->with('success', 'Student promoted to the next milestone successfully.');
    }

    /**
     * Demote a student to the previous milestone.
     */
    public function demoteMilestone(Request $request, StudentProfile $student)
    {
        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $milestones = $student->thesis->milestones()->with('template')->get()->sortByDesc('template.order');
        
        $current = $milestones->firstWhere('status', 'in_progress');
        if (!$current) {
            // If none in progress, maybe they are all approved or all not_started
            // Just find the last approved one to demote
            $current = null;
        }

        if ($current) {
            $current->update([
                'status' => 'not_started',
                'due_date' => null
            ]);
            $targetOrder = $current->template->order;
            $previous = $milestones->where('template.order', '<', $targetOrder)->first();
        } else {
            // Find the last approved milestone
            $previous = $milestones->firstWhere('status', 'approved');
        }

        if (!$previous) {
            return back()->with('error', 'No previous milestone found to demote to.');
        }

        $previous->update([
            'status' => 'in_progress',
            'date_approved_at' => null,
            'due_date' => now()->addDays(30)
        ]);

        // Notify student
        $messageService = new \\App\\Services\\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively rolled back. You have been demoted back to the '" . $previous->template->name . "' milestone.",
            null,
            ['administrative_action' => 'demotion']
        );

        return back()->with('success', 'Student demoted to the previous milestone successfully.');
    }
"""

content = content.replace("}\n", new_methods + "\n}\n")

with open('backend/app/Http/Controllers/Admin/StudentController.php', 'w') as f:
    f.write(content)
