<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentMilestone extends Model
{
    /** @use HasFactory<\Database\Factories\StudentMilestoneFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'thesis_project_id',
        'milestone_template_id',
        'status',
        'is_submission_unlocked',
        'submission_unlocked_at',
        'submission_unlocked_by',
        'due_date',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'approvals',
        'remark',
        'defence_date',
        'defence_time',
        'defence_location',
        'meeting_link',
        'communication_log',
        'date_approved_at',
        'date_approved_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_submission_unlocked' => 'boolean',
        'submission_unlocked_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'approvals' => 'array',
        'defence_date' => 'date',
        'date_approved_at' => 'datetime',
        'communication_log' => 'array'
    ];

    public function thesis()
    {
        return $this->belongsTo(ThesisProject::class, 'thesis_project_id');
    }

    public function template()
    {
        return $this->belongsTo(MilestoneTemplate::class, 'milestone_template_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_milestone_id')->latest();
    }

        public function getIsSupervisorApprovedAttribute()
    {
        if ($this->status === 'approved') return true;
        $approvals = collect($this->approvals ?? []);
        return $approvals->where('role', 'Supervisor')->isNotEmpty();
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'student_milestone_id');
    }

    public function unlockedBy()
    {
        return $this->belongsTo(User::class, 'submission_unlocked_by');
    }

    public function getProgressTrackAttribute()
    {
        $tasks = [];
        $completedTasks = 0;
        
        // 1. Supervisor Assignment
        if ($this->template?->show_supervisor_assignment) {
            $hasAssignments = $this->thesis?->assignments->where('status', 'active')->count() > 0;
            $tasks[] = [
                'id' => 'supervisor_allocation',
                'name' => 'Supervisor Allocation',
                'completed' => $hasAssignments,
                'action_type' => 'link',
                'action_label' => 'Assign Supervisors',
                'action_data' => ['admin_panel' => true]
            ];
            if ($hasAssignments) $completedTasks++;
        }

        // 2. Submit Authorization (Unlock)
        if ($this->template?->submission_requires_approval) {
            $tasks[] = [
                'id' => 'submission_authorization',
                'name' => 'Submission Authorization',
                'completed' => $this->is_submission_unlocked,
                'action_type' => 'unlock',
                'action_label' => 'Unlock form'
            ];
            if ($this->is_submission_unlocked) $completedTasks++;
        }

        // 3. Student Submission
        if ($this->template?->requires_submission) {
            $hasSubmission = $this->submissions()->count() > 0;
            $tasks[] = [
                'id' => 'student_submission',
                'name' => 'Student Submission',
                'completed' => $hasSubmission,
                'action_type' => 'none',
                'details' => 'Awaiting student upload'
            ];
            if ($hasSubmission) $completedTasks++;
        }
        
        // 4. Set Defence Date
        if ($this->template?->allow_defence_date) {
            $hasDate = !empty($this->defence_date);
            $tasks[] = [
                'id' => 'schedule_defence',
                'name' => 'Schedule Defence',
                'completed' => $hasDate,
                'action_type' => 'link',
                'action_label' => 'Set Date',
                'action_data' => ['admin_panel' => true]
            ];
            if ($hasDate) $completedTasks++;
        }

        // 5. Internal Examiner Assignment
        if ($this->template?->show_internal_examiner_assignment) {
            $hasExaminer = !empty($this->thesis?->internal_examiner_profile_id);
            $tasks[] = [
                'id' => 'assign_examiner',
                'name' => 'Assign Internal Examiner',
                'completed' => $hasExaminer,
                'action_type' => 'link',
                'action_label' => 'Assign Examiner',
                'action_data' => ['admin_panel' => true]
            ];
            if ($hasExaminer) $completedTasks++;
        }

        // 6. Defence Date Scheduled
        if ($this->template?->allow_defence_date) {
            $isDateSet = !is_null($this->defence_date);
            $tasks[] = [
                'id' => 'date_authorization',
                'name' => 'Defence Date Scheduled',
                'completed' => $isDateSet,
                'action_type' => 'none',
            ];
            if ($isDateSet) $completedTasks++;
        }

        // 7. Clearance Approvals
        $requiredRoles = collect($this->template?->required_approvers ?? []);
        if ($requiredRoles->isNotEmpty()) {
            $userApprovals = collect($this->approvals ?? []);
            
            foreach($requiredRoles as $role) {
                if ($role === 'Supervisor') {
                    $activeSupervisors = $this->thesis->assignments->where('status', 'active');

                    if ($activeSupervisors->count() > 0) {
                        foreach ($activeSupervisors as $assignment) {
                            $isApproved = $userApprovals->where('user_id', $assignment->supervisor?->user_id)->isNotEmpty() || $this->status === 'approved';
                            $tasks[] = [
                                'id' => 'supervisor_clearance_' . $assignment->id,
                                'name' => "Clearance: " . ($assignment->supervisor?->user?->name ?? 'Supervisor'),
                                'completed' => $isApproved,
                                'details' => 'Supervisor Approval',
                                'action_type' => 'clear_supervisor',
                                'action_data' => ['user_id' => $assignment->supervisor?->user_id]
                            ];
                            if ($isApproved) $completedTasks++;
                        }
                    } else {
                        $tasks[] = [
                            'id' => 'supervisor_clearance_generic',
                            'name' => 'Clearance: Thesis Supervisor',
                            'completed' => false,
                            'details' => 'Awaiting Supervisor Assignment',
                            'action_type' => 'none'
                        ];
                    }
                } else {
                    $isApproved = $userApprovals->where('role', $role)->isNotEmpty() || $this->status === 'approved';
                    $tasks[] = [
                        'id' => 'role_clearance_' . strtolower(str_replace(' ', '_', $role)),
                        'name' => "Clearance: $role",
                        'completed' => $isApproved,
                        'details' => 'Institutional Clearer',
                        'action_type' => 'clear_role',
                        'action_data' => ['role' => $role]
                    ];
                    if ($isApproved) $completedTasks++;
                }
            }
        } else {
            if ($this->template?->requires_approval) {
                $isApproved = $this->status === 'approved';
                $tasks[] = [
                    'id' => 'general_approval',
                    'name' => 'Committee Approval',
                    'completed' => $isApproved,
                    'action_type' => 'clear_milestone'
                ];
                if ($isApproved) {
                    $completedTasks++;
                }
            }
        }

        if (count($tasks) === 0) {
            $isApproved = $this->status === 'approved';
            $tasks[] = [
                'id' => 'generic_completion',
                'name' => 'General Completion',
                'completed' => $isApproved,
                'action_type' => 'none'
            ];
            if ($isApproved) {
                $completedTasks++;
            }
        }

        $percentage = count($tasks) > 0 ? floor(($completedTasks / count($tasks)) * 100) : 100;
        
        if ($this->status === 'approved') {
            $percentage = 100;
            $completedTasks = count($tasks);
            foreach ($tasks as &$task) {
                $task['completed'] = true;
            }
        }

        return [
            'tasks' => $tasks,
            'total' => count($tasks),
            'completed' => $completedTasks,
            'percentage' => $percentage,
            'is_fully_complete' => $percentage == 100,
        ];
    }

    /**
     * Handle cascade deletions.
     */
    protected static function booted()
    {
        static::deleting(function ($milestone) {
            // Delete submissions (each will trigger file cleanup)
            $milestone->submissions->each->delete();
            
            // Delete messages
            $milestone->messages()->delete();
        });
    }
}
