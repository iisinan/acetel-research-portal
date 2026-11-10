<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\StudentMilestone;

use Illuminate\Http\Request;
use App\Models\InternalExaminerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Display student details and thesis progress.
     */
    public function show(StudentProfile $student)
    {
        if ($student->thesis) {
            $templatesCount = $student->thesis->getApplicableMilestoneTemplates()->count();
                
            if ($student->thesis->milestones()->count() < $templatesCount) {
                $student->thesis->syncMilestones();
            }
        }

        if (empty($student->level_id) && !empty($student->program_id)) {
            $resolvedLevelId = \App\Models\StudentProfile::resolveLevelIdFromProgram($student->program_id);
            if ($resolvedLevelId) {
                $student->level_id = $resolvedLevelId;
                $student->saveQuietly();
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

        $internalExaminers = InternalExaminerProfile::with(['user', 'program'])
            ->where('active', true)
            ->where(function($q) use ($student) {
                $q->whereNull('program_id')->orWhere('program_id', $student->program_id);
            })
            ->get();

        if ($internalExaminers->isEmpty()) {
            $internalExaminers = InternalExaminerProfile::with(['user', 'program'])
                ->where('active', true)
                ->get();
        }

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

        $examiner = InternalExaminerProfile::with('user')->findOrFail($request->internal_examiner_profile_id);

        $student->thesis->update([
            'internal_examiner_profile_id' => $examiner->id,
        ]);

        // Keep ExaminerAssignment in sync
        \App\Models\ExaminerAssignment::updateOrCreate(
            ['thesis_project_id' => $student->thesis->id, 'type' => 'internal_examiner'],
            [
                'examiner_id' => $examiner->user_id,
                'assigned_by' => auth()->id(),
                'active' => true
            ]
        );

        // If internal defence event exists, attach examiner as panel member
        $event = \App\Models\DefenceEvent::where('thesis_project_id', $student->thesis->id)
            ->where('type', 'internal')
            ->first();
        if ($event && $examiner->user_id) {
            \App\Models\PanelMember::firstOrCreate([
                'defence_event_id' => $event->id,
                'user_id' => $examiner->user_id,
                'role' => 'examiner',
            ], [
                'invitation_status' => 'accepted'
            ]);
        }

        $studentName = $student->user?->name ?? 'Candidate';
        $examinerName = $examiner->user?->name ?? 'Internal Examiner';

        $internalMilestone = $student->thesis->milestones()->whereHas('template', fn($q) => $q->where('slug', 'internal_defence')->orWhere('order', 6))->first();
        if ($internalMilestone) {
            app(\App\Services\MilestoneWorkflowService::class)->notifyUpdate(
                $internalMilestone, 
                "Internal Examiner {$examinerName} was assigned to {$studentName}."
            );
        }

        return back()->with('success', "Internal Examiner ({$examinerName}) assigned successfully to {$studentName}.");
    }

    /**
     * Promote a student to the next milestone.
     */
    public function promoteMilestone(Request $request, StudentProfile $student)
    {
        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $milestones = $student->thesis->milestones()->with('template')->get()->sortBy('template.order')->values();
        $current = $milestones->first(fn($m) => $m->status !== 'approved');

        if (!$current) {
            return back()->with('error', 'All milestones for this student have already been approved.');
        }

        // Approve current milestone
        $current->update([
            'status' => 'approved',
            'date_approved_at' => $current->date_approved_at ?? now(),
            'approved_at' => $current->approved_at ?? now(),
        ]);

        // Find next milestone
        $next = $milestones->first(fn($m) => $m->template->order > $current->template->order);
        if ($next) {
            $next->update([
                'status' => 'in_progress',
                'due_date' => now()->addDays(30)
            ]);
            $msg = "Institutional Notice: Your thesis progress has been administratively advanced. You have been promoted past '" . $current->template->name . "' to Milestone {$next->template->order}: '" . $next->template->name . "'.";
            $success = "Student promoted to Milestone {$next->template->order}: {$next->template->name} successfully.";
        } else {
            $msg = "Institutional Notice: Your final thesis milestone '" . $current->template->name . "' has been approved! All research milestones are now completed.";
            $success = "Final milestone approved. All research milestones for this student are now completed.";
        }

        // Notify student
        $messageService = new \App\Services\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            $msg,
            null,
            ['administrative_action' => 'promotion']
        );

        return back()->with('success', $success);
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
                // Set target milestone as in_progress
                $m->update([
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(30),
                    'approved_at' => null,
                    'date_approved_at' => null
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
            "Institutional Notice: Your thesis progress has been administratively adjusted to Milestone {$targetMilestone->template->order}: '" . $targetMilestone->template->name . "'.",
            null,
            ['administrative_action' => 'set_milestone']
        );

        return back()->with('success', "Student milestone successfully set to Milestone {$targetMilestone->template->order}: {$targetMilestone->template->name}.");
    }

    public function demoteMilestone(Request $request, StudentProfile $student)
    {
        if (!$student->thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        $milestones = $student->thesis->milestones()->with('template')->get()->sortBy('template.order')->values();
        
        $current = $milestones->first(fn($m) => $m->status !== 'approved') ?? $milestones->last();

        if (!$current) {
            return back()->with('error', 'No milestone found to demote.');
        }

        $previous = $milestones->filter(fn($m) => $m->template->order < $current->template->order)->last();

        if (!$previous) {
            return back()->with('error', 'Student is already at Milestone 1 and cannot be demoted further.');
        }

        // Reset current and later milestones
        foreach ($milestones->filter(fn($m) => $m->template->order >= $current->template->order) as $later) {
            $later->update([
                'status' => 'not_started',
                'due_date' => null,
                'submitted_at' => null,
                'approved_at' => null,
                'date_approved_at' => null,
                'approvals' => null,
                'is_submission_unlocked' => false
            ]);
        }

        // Reactivate previous milestone
        $previous->update([
            'status' => 'in_progress',
            'date_approved_at' => null,
            'approved_at' => null,
            'due_date' => now()->addDays(30)
        ]);

        // Notify student
        $messageService = new \App\Services\MessageService();
        $messageService->sendMessage(
            $student->thesis,
            auth()->user(),
            "Institutional Notice: Your thesis progress has been administratively rolled back to Milestone {$previous->template->order}: '" . $previous->template->name . "'.",
            null,
            ['administrative_action' => 'demotion']
        );

        return back()->with('success', "Student demoted back to Milestone {$previous->template->order}: {$previous->template->name} successfully.");
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

            // 1. Delete physical submission files from Storage disk
            if ($thesis) {
                try {
                    $milestoneIds = $thesis->milestones()->pluck('id')->all();
                    if (!empty($milestoneIds)) {
                        $submissions = DB::table('submissions')
                            ->whereIn('student_milestone_id', $milestoneIds)
                            ->whereNotNull('file_url')
                            ->get(['id', 'file_url']);

                        foreach ($submissions as $sub) {
                            if (!empty($sub->file_url)) {
                                try {
                                    Storage::delete($sub->file_url);
                                } catch (\Throwable $e) {}
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Could not delete submission files: " . $e->getMessage());
                }
            }

            if ($userId) {
                try {
                    $userSubs = DB::table('submissions')
                        ->where('submitted_by', $userId)
                        ->whereNotNull('file_url')
                        ->get(['id', 'file_url']);

                    foreach ($userSubs as $sub) {
                        if (!empty($sub->file_url)) {
                            try {
                                Storage::delete($sub->file_url);
                            } catch (\Throwable $e) {}
                        }
                    }
                } catch (\Throwable $e) {}
            }

            // 2. Clear non-cascading relations that could violate foreign key constraints on users
            if ($userId) {
                // Action items assigned to this user
                if (Schema::hasTable('action_items')) {
                    DB::table('action_items')->where('assigned_to', $userId)->delete();
                }
                // Communication channels created by this user
                if (Schema::hasTable('communication_channels')) {
                    DB::table('communication_channels')->where('created_by', $userId)->delete();
                }
                // Feedback created by this user
                if (Schema::hasTable('feedback')) {
                    DB::table('feedback')->where('created_by', $userId)->delete();
                }
                // Examiner assignments where user was examiner or assigner
                if (Schema::hasTable('examiner_assignments')) {
                    DB::table('examiner_assignments')->where('examiner_id', $userId)->orWhere('assigned_by', $userId)->delete();
                }
                // Audit logs referencing this user
                if (Schema::hasTable('audit_logs')) {
                    DB::table('audit_logs')->where('user_id', $userId)->delete();
                }
                // System notifications for this user
                if (Schema::hasTable('notifications')) {
                    DB::table('notifications')->where('notifiable_id', $userId)->delete();
                }
                // Spatie roles and permissions
                if (Schema::hasTable('model_has_roles')) {
                    DB::table('model_has_roles')->where('model_id', $userId)->delete();
                }
                if (Schema::hasTable('model_has_permissions')) {
                    DB::table('model_has_permissions')->where('model_id', $userId)->delete();
                }
            }

            // 3. Delete thesis project if present (cascades milestones, defence events, channels, assignments, etc.)
            if ($thesis) {
                $thesis->delete();
            }

            // 4. Delete user account and student profile
            // Deleting the user account triggers DB-level ON DELETE CASCADE on student_profiles, inbox_messages, etc.
            if ($user) {
                $user->delete();
            } else {
                $student->delete();
            }
        });
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Request $request, StudentProfile $student)
    {
        $name = $student->user->name ?? $student->student_id_number ?? 'Student';
        $matric = $student->student_id_number ?? '';
        $studentId = $student->id;

        $this->deleteStudentRecord($student);

        $label = $matric ? "{$name} ({$matric})" : $name;

        $referer = $request->header('referer') ?? '';
        $previous = url()->previous();

        // If the delete request originated from the student's detail page, redirect to milestone templates
        if (str_contains($referer, "/admin/students/{$studentId}") || str_contains($previous, "/admin/students/{$studentId}")) {
            return redirect()->route('admin.milestone-templates.index')->with('success', "Student record for {$label} has been permanently deleted from the system.");
        }

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
