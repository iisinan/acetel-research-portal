<?php

namespace App\Policies;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudentProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Director', 'Program Coordinator', 'Admin', 'Supervisor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StudentProfile $studentProfile): bool
    {
        // 1. Student can view their own profile
        if ($user->id === $studentProfile->user_id) {
            return true;
        }

        // 2. Director / Admin can view all
        if ($user->hasAnyRole(['Director', 'Admin'])) {
            return true;
        }

        // 3. Program Coordinator: Scoped by Program + Level
        if ($user->hasRole('Program Coordinator')) {
            return $user->hasCoordinatorAccess($studentProfile);
        }

        // 4. Supervisor: Only assigned students
        if ($user->hasRole('Supervisor')) {
            // Check if supervisor is assigned to ANY active thesis of this student
            // Assuming student has one thesis potentially
            $thesis = $studentProfile->thesis;
            if ($thesis) {
                 return $thesis->assignments()
                        ->where('supervisor_profile_id', $user->supervisorProfile?->id)
                        ->exists();
            }
        }

        return false;
    }

    // ... other methods (create, update, delete) can be implemented similarly
}
