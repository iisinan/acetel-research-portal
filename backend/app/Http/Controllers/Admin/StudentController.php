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
        if ($student->thesis) {
            $templatesCount = \App\Models\MilestoneTemplate::whereNull('program_id')
                ->orWhere('program_id', $student->program_id)->count();
                
            if ($student->thesis->milestones()->count() < $templatesCount) {
                $student->thesis->syncMilestones();
            }
        }

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
        public function setMilestone(Request $request, \App\Models\StudentProfile $student)
    {
        $request->validate([
            'milestone_id' => 'required|exists:student_milestones,id'
        ]);

        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $targetMilestone = $student->thesis->milestones()->with('template')->findOrFail($request->milestone_id);
        $targetOrder = $targetMilestone->template->order;

        $milestones = $student->thesis->milestones()->with('template')->get();

        foreach ($milestones as $m) {
            if ($m->template->order < $targetOrder) {
                $m->update([
                    'status' => 'approved',
                    'date_approved_at' => $m->date_approved_at ?? now(),
                    'approved_at' => $m->approved_at ?? now()
                ]);
            } elseif ($m->template->order == $targetOrder) {
                // If it was already approved, maybe we are demoting TO it, so we mark it in_progress
                $m->update([
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(30)
                ]);
            } else {
                // Future milestones
                $m->update([
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
        $messageService = new \App\Services\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively adjusted to the '" . $targetMilestone->template->name . "' milestone.",
            null,
            ['administrative_action' => 'set_milestone']
        );

        return back()->with('success', 'Student milestone has been set successfully.');
    }

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

    /**
     * Completely remove a student and all related thesis/submission/user records from the system.
     */
    private function deleteStudentRecord(StudentProfile $student): void
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $userId = $student->user_id;
            $thesis = $student->thesis;

            if ($thesis) {
                $milestoneIds = $thesis->milestones()->pluck('id')->all();

                if (!empty($milestoneIds)) {
                    // Delete seminar grades
                    if (Schema::hasTable('seminar_grades')) {
                        DB::table('seminar_grades')->whereIn('student_milestone_id', $milestoneIds)->delete();
                    }

                    // Delete submissions, feedback, and storage files
                    $submissions = DB::table('submissions')->whereIn('student_milestone_id', $milestoneIds)->get();
                    foreach ($submissions as $sub) {
                        if (!empty($sub->file_url)) {
                            try {
                                Storage::delete($sub->file_url);
                            } catch (\Throwable $e) {}
                        }
                        if (Schema::hasTable('feedback')) {
                            DB::table('feedback')->where('submission_id', $sub->id)->delete();
                        }
                    }
                    DB::table('submissions')->whereIn('student_milestone_id', $milestoneIds)->delete();
                }

                // Delete any submissions submitted_by this user
                if ($userId) {
                    $userSubs = DB::table('submissions')->where('submitted_by', $userId)->get();
                    foreach ($userSubs as $sub) {
                        if (!empty($sub->file_url)) {
                            try {
                                Storage::delete($sub->file_url);
                            } catch (\Throwable $e) {}
                        }
                        if (Schema::hasTable('feedback')) {
                            DB::table('feedback')->where('submission_id', $sub->id)->delete();
                        }
                    }
                    DB::table('submissions')->where('submitted_by', $userId)->delete();
                }

                // Delete action items
                if (Schema::hasTable('action_items')) {
                    DB::table('action_items')->where('thesis_project_id', $thesis->id)->delete();
                }

                // Defence events, panel members, evaluations
                if (Schema::hasTable('defence_events')) {
                    $events = DB::table('defence_events')->where('thesis_project_id', $thesis->id)->get();
                    foreach ($events as $event) {
                        if (Schema::hasTable('panel_members')) {
                            DB::table('panel_members')->where('defence_event_id', $event->id)->delete();
                        }
                        if (Schema::hasTable('evaluations')) {
                            DB::table('evaluations')->where('defence_event_id', $event->id)->delete();
                        }
                    }
                    DB::table('defence_events')->where('thesis_project_id', $thesis->id)->delete();
                }

                // Supervision and examiner assignments
                if (Schema::hasTable('supervision_assignments')) {
                    DB::table('supervision_assignments')->where('thesis_project_id', $thesis->id)->delete();
                }
                if (Schema::hasTable('examiner_assignments')) {
                    DB::table('examiner_assignments')->where('thesis_project_id', $thesis->id)->delete();
                }

                // Messages & communication channels
                if (Schema::hasTable('messages')) {
                    DB::table('messages')->where('thesis_project_id', $thesis->id)->delete();
                }
                if (Schema::hasTable('communication_channels')) {
                    DB::table('communication_channels')->where('thesis_project_id', $thesis->id)->delete();
                }

                // Delete student milestones
                DB::table('student_milestones')->where('thesis_project_id', $thesis->id)->delete();

                // Delete thesis project
                DB::table('thesis_projects')->where('id', $thesis->id)->delete();
            }

            if ($userId) {
                if (Schema::hasTable('messages')) {
                    DB::table('messages')->where('sender_id', $userId)->orWhere('receiver_id', $userId)->delete();
                }
                if (Schema::hasTable('inbox_messages')) {
                    DB::table('inbox_messages')->where('sender_id', $userId)->orWhere('recipient_id', $userId)->delete();
                }
                if (Schema::hasTable('audit_logs')) {
                    DB::table('audit_logs')->where('user_id', $userId)->delete();
                }
                if (Schema::hasTable('evaluations')) {
                    DB::table('evaluations')->where('evaluator_id', $userId)->delete();
                }
                if (Schema::hasTable('notifications')) {
                    DB::table('notifications')->where('notifiable_id', $userId)->delete();
                }
                if (Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')->where('model_id', $userId)->delete();
                }
                if (Schema::hasTable('model_has_permissions')) {
                    DB::table('model_has_permissions')->where('model_id', $userId)->delete();
                }
            }

            // Delete student profile
            $student->delete();

            // Delete user account
            if ($user) {
                $user->delete();
            } elseif ($userId) {
                DB::table('users')->where('id', $userId)->delete();
            }
        });
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(StudentProfile $student)
    {
        $name = $student->user->name ?? $student->student_id_number ?? 'Student';
        $matric = $student->student_id_number ?? '';

        $this->deleteStudentRecord($student);

        $label = $matric ? "{$name} ({$matric})" : $name;
        return back()->with('success', "Student record for {$label} has been permanently deleted from the system.");
    }

    /**
     * Bulk delete selected students.
     */
    public function bulkDestroy(Request $request)
    {
        $milestoneIds = $request->input('milestone_ids', []);
        $studentIds = $request->input('student_ids', []);

        if (empty($milestoneIds) && empty($studentIds)) {
            return back()->with('error', 'No students were selected for deletion.');
        }

        $studentsToDelete = collect();

        if (!empty($milestoneIds)) {
            $milestones = StudentMilestone::with('thesis.student.user')
                ->whereIn('id', $milestoneIds)
                ->get();
            foreach ($milestones as $sm) {
                if ($sm->thesis && $sm->thesis->student) {
                    $studentsToDelete->push($sm->thesis->student);
                }
            }
        }

        if (!empty($studentIds)) {
            $profiles = StudentProfile::with('user')->whereIn('id', $studentIds)->get();
            foreach ($profiles as $profile) {
                $studentsToDelete->push($profile);
            }
        }

        $studentsToDelete = $studentsToDelete->unique('id');
        $count = $studentsToDelete->count();

        if ($count === 0) {
            return back()->with('error', 'No valid student profiles were found to delete.');
        }

        foreach ($studentsToDelete as $student) {
            $this->deleteStudentRecord($student);
        }

        return back()->with('success', "Successfully deleted {$count} student record(s) from the system.");
    }
}
