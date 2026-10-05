<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DefenceEvent;
use App\Models\PanelMember;

class DefenceEventPolicy
{
    /**
     * Determine whether the user can create (schedule) models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['Program Coordinator', 'Admin', 'Director']);
    }

    /**
     * Determine whether the user can evaluate the event.
     */
    public function evaluate(User $user, DefenceEvent $event): bool
    {
        // Must be a panel member
        return PanelMember::where('defence_event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();
    }
    
    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, DefenceEvent $event): bool
    {
         // Student owner, Supervisors, Panel Members, Coordinators, Admins
         if ($user->hasRole(['Admin', 'Director', 'Program Coordinator'])) {
             return true;
         }
         
         if ($user->hasRole('Student')) {
             return $event->thesis->student_profile_id === $user->studentProfile->id;
         }
         
         if ($user->hasRole('Supervisor')) {
             // Assigned supervisor or Panel member
             $isAssigned = $event->thesis->assignments()
                ->where('supervisor_profile_id', $user->supervisorProfile->id)
                ->exists();
             if ($isAssigned) return true;
         }

         // Check panel member (User ID based)
         return PanelMember::where('defence_event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
