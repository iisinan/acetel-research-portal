<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;

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
