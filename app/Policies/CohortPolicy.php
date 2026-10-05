<?php

namespace App\Policies;

use App\Models\Cohort;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CohortPolicy
{
    /**
     * Determine whether the user can perform any action.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        return null; // Fall through to specific methods
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['Program Coordinator', 'Director']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cohort $cohort): bool
    {
        // Coordinators can view cohorts (which are global)
        if ($user->hasRole('Program Coordinator')) {
            return true;
        }

        return $user->hasRole('Director');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Handled by `before()`
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cohort $cohort): bool
    {
        return $user->hasRole(['Program Coordinator', 'Director']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cohort $cohort): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cohort $cohort): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cohort $cohort): bool
    {
        return false;
    }
}
