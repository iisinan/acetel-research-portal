<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;

use Illuminate\Http\Request;
use App\Models\InternalExaminerProfile;

class StudentController extends Controller
{
    /**
     * Display student details and thesis progress.
     */
    public function show(StudentProfile $student)
    {
        $student->load([
            'user', 
            'program', 
            'level', 
            'cohort', 
            'thesis.milestones.template',
            'thesis.milestones.submissions' => fn($q) => $q->latest(),
            'thesis.defenceEvents',
            'thesis.supervisors.supervisor.user',
            'thesis.internalExaminer.user'
        ]);

        $internalExaminers = InternalExaminerProfile::with('user')
            ->where('active', true)
            ->where('program_id', $student->program_id)
            ->get();

        $recent_logs = \App\Models\AuditLog::with('user')
            ->where('user_id', $student->user_id)
            ->latest()
            ->take(8)
            ->get();

        return view('admin.students.show', compact('student', 'internalExaminers', 'recent_logs'));
    }

    /**
     * Assign an internal examiner to the student's thesis.
     */
    public function assignInternalExaminer(Request $request, StudentProfile $student)
    {
        $request->validate([
            'internal_examiner_profile_id' => 'required|exists:internal_examiner_profiles,id',
        ]);

        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $student->thesis->update([
            'internal_examiner_profile_id' => $request->internal_examiner_profile_id,
        ]);

        return back()->with('success', 'Internal Examiner assigned successfully.');
    }

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
        $messageService = new \App\Services\MessageService();
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
        $messageService = new \App\Services\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively rolled back. You have been demoted back to the '" . $previous->template->name . "' milestone.",
            null,
            ['administrative_action' => 'demotion']
        );

        return back()->with('success', 'Student demoted to the previous milestone successfully.');
    }
}
