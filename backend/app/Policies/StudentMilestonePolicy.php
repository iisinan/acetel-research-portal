<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StudentMilestone;

class StudentMilestonePolicy
{
    /**
     * Determine whether the user can view the milestone.
     */
    public function view(User $user, StudentMilestone $milestone): bool
    {
        // Director and Admin can view all
        if ($user->hasAnyRole(['Admin', 'Director']) || $user->can('milestones.configure_program')) { 
            return true; 
        }

        // Student can view their own milestones, BUT only if they are approved OR the current ongoing one
        if ($user->hasRole('Student')) {
            if ($milestone->thesis->student_profile_id !== $user->studentProfile?->id) {
                return false;
            }

            // If milestone is already approved, it's always viewable (history)
            if ($milestone->status === 'approved') {
                return true;
            }

            // Institutional Sequence Guard: Future milestones are LOCKED and cannot be opened
            $ongoing = $milestone->thesis->milestones()->get()->sortBy(fn($m) => $m->template?->order ?? 999)->first(fn($m) => $m->status !== 'approved');
            
            return $ongoing && $ongoing->id === $milestone->id;
        }

        // Supervisor can view if assigned to the thesis
        if ($user->hasRole('Supervisor')) {
            return $milestone->thesis->assignments()
                ->where('supervisor_profile_id', $user->supervisorProfile?->id)
                ->whereIn('status', ['active', 'ended']) 
                ->exists();
        }

        if ($user->hasRole('Internal Examiner')) {
            return $user->internalExaminerProfiles()
                ->where('id', $milestone->thesis->internal_examiner_profile_id)
                ->exists();
        }

        if ($user->hasRole('External Examiner')) {
            return $user->externalExaminerProfiles()
                ->where('id', $milestone->thesis->external_examiner_profile_id)
                ->exists();
        }

        // Program Coordinator can view if assigned to the student's program
        if ($user->hasRole('Program Coordinator')) {
            $student = $milestone->thesis->student;
            return $user->coordinatorProfiles()
                ->where('active', true)
                ->where('program_id', $student->program_id)
                ->exists();
        }

        return false;
    }

    public function submit(User $user, StudentMilestone $milestone): bool
    {
        \Illuminate\Support\Facades\Log::info("Evaluating StudentMilestonePolicy@submit", [
            'user_id' => $user->id,
            'has_role' => $user->hasRole('Student'),
            'user_profile' => $user->studentProfile?->id,
            'thesis_profile' => $milestone->thesis->student_profile_id,
            'allow_date' => $milestone->template->allow_defence_date,
            'defence_date' => $milestone->defence_date,
            'is_past' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->endOfDay()->isPast() : null,
            'req_approval' => $milestone->template->submission_requires_approval,
            'unlocked' => $milestone->is_submission_unlocked
        ]);

        if (!$user->hasRole('Student') || $milestone->thesis->student_profile_id !== $user->studentProfile?->id) {
            \Illuminate\Support\Facades\Log::warning("Policy Failed: Not a student or wrong profile.");
            return false;
        }

        if ($milestone->template->allow_defence_date) {
            if ($milestone->defence_date && \Carbon\Carbon::parse($milestone->defence_date)->endOfDay()->isPast() && $milestone->status !== 'approved') {
                \Illuminate\Support\Facades\Log::warning("Policy Failed: Defence date past.");
                return false;
            }
        }

        if ($milestone->template->submission_requires_approval && !$milestone->is_submission_unlocked) {
            \Illuminate\Support\Facades\Log::warning("Policy Failed: Submission locked.");
            return false;
        }

        return true;
    }

    public function unlock(User $user, StudentMilestone $milestone): bool
    {
        $template = $milestone->template;
        if (!$template || !$template->submission_requires_approval) {
            return false;
        }

        // 1. Admin/Director usually have master clearance, but we'll check the list first if provided
        $approverRoles = $template->submission_approver_roles ?? [];

        // 2. If no specific roles selected, use institutional defaults (PC/Supervisor)
        if (empty($approverRoles)) {
            // Default: Admins, Directors, Program Coordinators, and assigned Supervisors
            if ($user->hasAnyRole(['Admin', 'Director'])) {
                return true;
            }

            if ($user->hasRole('Program Coordinator')) {
                $student = $milestone->thesis->student;
                if ($user->coordinatorProfiles()->where('active', true)->where('program_id', $student->program_id)->exists()) {
                    return true;
                }
            }

            if ($user->hasRole('Supervisor')) {
                if ($user->supervisorProfile && $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->whereIn('status', ['active', 'ended'])->exists()) {
                    return true;
                }
            }
            
            return false;
        }

        // 3. Strict mode: ONLY the selected roles (plus Admin)
        if ($user->hasRole('Admin')) {
            return true;
        }

        foreach ($approverRoles as $role) {
            if (!$user->hasRole($role)) {
                continue;
            }

            // Role-specific scope checks
            if ($role === 'Program Coordinator') {
                $student = $milestone->thesis->student;
                if ($user->coordinatorProfiles()->where('active', true)->where('program_id', $student->program_id)->exists()) {
                    return true;
                }
            } elseif ($role === 'Supervisor') {
                if ($user->supervisorProfile && $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->whereIn('status', ['active', 'ended'])->exists()) {
                    return true;
                }
            } elseif ($role === 'Internal Examiner') {
                if ($user->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists()) {
                    return true;
                }
            } elseif ($role === 'External Examiner') {
                if ($user->externalExaminerProfiles()->where('id', $milestone->thesis->external_examiner_profile_id)->exists()) {
                    return true;
                }
            } elseif ($user->hasRole($role)) {
                // Generic role check if no specific scope logic defined
                return true;
            }
        }

        return false;
    }

    public function review(User $user, StudentMilestone $milestone): bool
    {
        $template = $milestone->template;
        if (!$template || !$template->requires_approval) {
            return false;
        }

        // Requirement: Post Submission Approval must be granted before Institutional Clearance.
        if ($template->submission_requires_approval && !$milestone->is_submission_unlocked) {
            return false;
        }

        $requiredRoles = $template->required_approvers ?? ['Program Coordinator'];
        $currentApprovals = collect($milestone->approvals ?? []);

        // Check if this specific user has already approved
        if ($currentApprovals->where('user_id', $user->id)->isNotEmpty()) {
            return false;
        }

        foreach ($requiredRoles as $role) {
            // Admin can fulfill any required role check
            $isAuthorized = $user->hasRole('Admin');
            
            if (!$isAuthorized && !$user->hasRole($role)) {
                continue;
            }

            // Authorization logic per role
            if (!$isAuthorized) {
                if ($role === 'Program Coordinator') {
                    $student = $milestone->thesis->student;
                    if ($user->coordinatorProfiles()
                        ->where('active', true)
                        ->where('program_id', $student->program_id)
                        ->exists()) {
                        $isAuthorized = true;
                    }
                } elseif ($role === 'Supervisor') {
                    if ($user->supervisorProfile && $milestone->thesis->assignments()
                        ->where('supervisor_profile_id', $user->supervisorProfile->id)
                        ->whereIn('status', ['active', 'ended'])
                        ->exists()) {
                        $isAuthorized = true;
                    }
                } elseif ($role === 'Internal Examiner') {
                    if ($user->internalExaminerProfiles()
                        ->where('id', $milestone->thesis->internal_examiner_profile_id)
                        ->exists()) {
                        $isAuthorized = true;
                    }
                } elseif ($role === 'External Examiner') {
                    if ($user->externalExaminerProfiles()
                        ->where('id', $milestone->thesis->external_examiner_profile_id)
                        ->exists()) {
                        $isAuthorized = true;
                    }
                }
            }

            if ($isAuthorized) {
                // Check if it's this role's turn in the sequence
                $workflowService = app(\App\Services\MilestoneWorkflowService::class);
                if ($workflowService->canApprove($milestone, $user, $role)) {
                    return true;
                }
            }
        }

        return false;
    }
}
