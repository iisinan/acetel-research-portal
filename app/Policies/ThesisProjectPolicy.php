<?php

namespace App\Policies;

use App\Models\ThesisProject;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ThesisProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ThesisProject $thesisProject): bool
    {
        if ($user->hasAnyRole(['Admin', 'Director'])) {
            return true;
        }

        if ($user->hasRole('Program Coordinator')) {
            return $user->hasCoordinatorAccess($thesisProject->student);
        }

        if ($user->hasRole('Supervisor')) {
            // Check assignment
            return $thesisProject->assignments()
                ->where('supervisor_profile_id', '=', $user->supervisorProfile?->id)
                ->exists();
        }

        if ($user->hasRole('Internal Examiner')) {
            return $thesisProject->internal_examiner_profile_id === $user->internalExaminerProfile?->id;
        }

        if ($user->hasRole('Student')) {
            // Own thesis
            return $thesisProject->student_profile_id === $user->studentProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Student') && !$user->studentProfile->thesis;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ThesisProject $thesisProject): bool
    {
        if ($user->hasAnyRole(['Admin', 'Director'])) {
            return true;
        }
        
        if ($user->hasRole('Program Coordinator')) {
            return $user->hasCoordinatorAccess($thesisProject->student);
        }

        if ($user->hasRole('Supervisor')) {
            // Check assignment
             return $thesisProject->assignments()
                ->where('supervisor_profile_id', '=', $user->supervisorProfile?->id)
                ->exists();
        }

        if ($user->hasRole('Student')) {
             return $thesisProject->student_profile_id === $user->studentProfile->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ThesisProject $thesisProject): bool
    {
        return $user->hasRole('Admin');
    }

    public function assignSupervisor(User $user, ThesisProject $thesisProject): bool
    {
        if ($user->hasAnyRole(['Admin', 'Director'])) {
            return true;
        }

        if ($user->hasRole('Program Coordinator')) {
            return $user->hasCoordinatorAccess($thesisProject->student);
        }

        return false;
    }
    public function clearForInternal(User $user, ThesisProject $thesisProject): bool
    {
        if ($user->hasRole(['Admin', 'Director'])) {
            return true;
        }

        if ($user->hasRole('Supervisor')) {
            // Check assignment
             return $thesisProject->assignments()
                ->where('supervisor_profile_id', $user->supervisorProfile?->id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }
}
