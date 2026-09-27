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
        // 1. Check Panel Members
        if ($this->panelMembers()->where('user_id', $userId)->exists()) {
            return true;
        }

        // 2. Check Thesis Examiners
        $thesis = $this->thesis()->with(['internalExaminerProfile.user', 'externalExaminerProfile.user'])->first();
        
        if ($thesis) {
            if ($thesis->internalExaminerProfile && $thesis->internalExaminerProfile->user_id == $userId) {
                return true;
            }
            if ($thesis->externalExaminerProfile && $thesis->externalExaminerProfile->user_id == $userId) {
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
