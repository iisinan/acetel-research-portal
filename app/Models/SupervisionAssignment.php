<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupervisionAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\SupervisionAssignmentFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'thesis_project_id',
        'supervisor_profile_id',
        'role',
        'status',
        'assigned_at',
        'ended_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class, 'thesis_project_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(SupervisorProfile::class, 'supervisor_profile_id');
    }

    public function supervisorProfile()
    {
        return $this->supervisor();
    }

    protected static function booted()
    {
        static::saved(function ($assignment) {
            if ($assignment->thesis) {
                (new \App\Services\MilestoneWorkflowService())->tryAutoAdvanceSupervisorsAssigned($assignment->thesis);
            }
        });
    }
}
