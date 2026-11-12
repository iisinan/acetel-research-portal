<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DefenceEvent extends Model
{
    /** @use HasFactory<\Database\Factories\DefenceEventFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'thesis_project_id',
        'type',
        'schedule_start',
        'schedule_end',
        'location',
        'outcome',
        'signed_outcome_form_url'
    ];

    protected $casts = [
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime',
    ];

    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class, 'thesis_project_id');
    }

    public function panelMembers()
    {
        return $this->hasMany(PanelMember::class);
    }
    

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Check if a user is an authorized evaluator for this event.
     * Evaluators include: Panel Members, assigned Internal Examiner, and assigned External Examiner.
     */
    public function isAuthorizedEvaluator($userId)
    {
        if (!$userId) {
            return false;
        }

        // 0. Check if user is admin for presentation/defence milestones
        if (in_array($this->type, ['seminar', 'proposal', 'progress_report_1', 'progress_report_2', 'internal', 'external', 'viva'])) {
            $user = \App\Models\User::find($userId);
            if ($user && $user->hasRole('Admin')) {
                return true;
            }
        }

        // 1. Check Panel Members
        if ($this->panelMembers()->where('user_id', $userId)->exists()) {
            return true;
        }

        // 2. Check MilestoneTemplate assigned examiners (track-specific for seminar, global for other milestones)
        $milestone = $this->thesis?->milestones?->first(function($m) {
            $eventType = $m->template?->defence_type ?? match($m->template?->slug) {
                'seminar_as_a_course' => 'seminar',
                'proposal_defence' => 'proposal',
                'progress_report_1' => 'progress_report_1',
                'progress_report_2' => 'progress_report_2',
                'internal_defence' => 'internal',
                'viva' => 'external',
                default => 'first_seminar',
            };
            return $eventType === $this->type;
        });

        if ($milestone && $milestone->template) {
            $meta = $milestone->template->metadata ?? [];
            $isAssigned = false;
            if ($milestone->template->slug === 'seminar_as_a_course') {
                $trackUserIds = $meta['group_examiners'][$milestone->discipline_group]['user_ids'] ?? [];
                if (in_array($userId, $trackUserIds)) {
                    $isAssigned = true;
                }
            } else {
                $globalUserIds = $meta['examiner_user_ids'] ?? [];
                if (in_array($userId, $globalUserIds)) {
                    $isAssigned = true;
                }
            }

            if ($isAssigned) {
                // Ensure PanelMember record exists so attendance/panel relations work
                \App\Models\PanelMember::firstOrCreate([
                    'defence_event_id' => $this->id,
                    'user_id' => $userId,
                ], [
                    'role' => 'examiner',
                    'invitation_status' => 'accepted'
                ]);
                return true;
            }
        }

        // 3. Check Thesis Internal / External Examiners (including ExaminerAssignment records)
        $thesis = $this->thesis()->with(['internalExaminer.user', 'externalExaminer.user'])->first();
        
        if ($thesis) {
            if ($this->type === 'internal' && $thesis->internalExaminer && $thesis->internalExaminer->user_id == $userId) {
                \App\Models\PanelMember::firstOrCreate([
                    'defence_event_id' => $this->id,
                    'user_id' => $userId,
                ], [
                    'role' => 'examiner',
                    'invitation_status' => 'accepted'
                ]);
                return true;
            }
            if (in_array($this->type, ['external', 'viva']) && $thesis->externalExaminer && $thesis->externalExaminer->user_id == $userId) {
                \App\Models\PanelMember::firstOrCreate([
                    'defence_event_id' => $this->id,
                    'user_id' => $userId,
                ], [
                    'role' => 'examiner',
                    'invitation_status' => 'accepted'
                ]);
                return true;
            }
            if ($this->type === 'internal' && \App\Models\ExaminerAssignment::where('thesis_project_id', $thesis->id)->where('type', 'internal_examiner')->where('examiner_id', $userId)->exists()) {
                \App\Models\PanelMember::firstOrCreate([
                    'defence_event_id' => $this->id,
                    'user_id' => $userId,
                ], [
                    'role' => 'examiner',
                    'invitation_status' => 'accepted'
                ]);
                return true;
            }
            if (in_array($this->type, ['external', 'viva']) && \App\Models\ExaminerAssignment::where('thesis_project_id', $thesis->id)->where('type', 'external_examiner')->where('examiner_id', $userId)->exists()) {
                \App\Models\PanelMember::firstOrCreate([
                    'defence_event_id' => $this->id,
                    'user_id' => $userId,
                ], [
                    'role' => 'examiner',
                    'invitation_status' => 'accepted'
                ]);
                return true;
            }
        }

        return false;
    }


    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
        static::deleting(function ($event) {
            // Delete evaluations
            $event->evaluations()->delete();
            
            // Delete panel members
            $event->panelMembers()->delete();
            
            // Cleanup signed outcome form if exists
            if ($event->signed_outcome_form_url) {
                \Illuminate\Support\Facades\Storage::delete($event->signed_outcome_form_url);
            }
        });
    }
}
