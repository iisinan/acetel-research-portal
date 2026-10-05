<?php

namespace App\Services;

use App\Models\DefenceEvent;
use App\Models\PanelMember;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class EventService
{
    /**
     * Schedule a new defence event.
     */
    public function scheduleEvent(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Basic conflict check could go here
            
            $event = DefenceEvent::create([
                'thesis_project_id' => $data['thesis_project_id'],
                'type' => $data['type'], // proposal, final, etc.
                'schedule_start' => $data['schedule_start'],
                'schedule_end' => $data['schedule_end'],
                'location' => $data['location'],
                'outcome' => 'pending',
            ]);

            // Notify Student & Supervisors
            $event->load('thesis.student.user', 'thesis.assignments.supervisor.user');
            
            // Notify Student
            $event->thesis->student->user->notify(new \App\Notifications\EventScheduled($event));

            // Notify Supervisors
            foreach ($event->thesis->assignments as $assignment) {
                 if ($assignment->status === 'active') {
                    $assignment->supervisor->user->notify(new \App\Notifications\EventScheduled($event));
                 }
            }

            \App\Models\AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'schedule_defence',
                'entity_type' => DefenceEvent::class,
                'entity_id' => $event->id,
                'new_values' => $event->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $event;
        });
    }

    /**
     * Add a panel member to an event.
     */
    public function addPanelMember(DefenceEvent $event, string $userId, string $role = 'panelist')
    {
        // Check if already exists
        $exists = PanelMember::where('defence_event_id', $event->id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            throw new Exception("User is already a panel member for this event.");
        }

        return PanelMember::create([
            'defence_event_id' => $event->id,
            'user_id' => $userId,
            'role' => $role,
            'invitation_status' => 'pending', // could implement email invite later
        ]);
    }

    /**
     * Submit an evaluation for an event.
     */
    public function submitEvaluation(DefenceEvent $event, User $evaluator, array $data)
    {
        // Check if evaluator is a panel member
        $isPanel = PanelMember::where('defence_event_id', $event->id)
            ->where('user_id', $evaluator->id)
            ->exists();

        if (!$isPanel) {
            throw new Exception("User is not a panel member for this event.");
        }

        $evaluation = Evaluation::create([
            'defence_event_id' => $event->id,
            'evaluator_id' => $evaluator->id,
            'score' => $data['score'], // JSON or numeric depending on schema, schema said JSON
            'recommendation' => $data['recommendation'],
            'comments' => $data['comments'] ?? null,
            'submitted_at' => now(),
        ]);

        \App\Models\AuditLog::create([
            'user_id' => $evaluator->id,
            'action' => 'submit_evaluation',
            'entity_type' => Evaluation::class,
            'entity_id' => $evaluation->id,
            'new_values' => $evaluation->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $evaluation;
    }
}
