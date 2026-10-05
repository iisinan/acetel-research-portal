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
        'is_supervisor_approved',
        'date_approved_at',
        'date_approved_by'
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_submission_unlocked' => 'boolean',
        'is_supervisor_approved' => 'boolean',
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
        if ($this->status === 'approved') {
            return true;
        }

        if ($this->status === 'revision_required') {
            return false;
        }

        $workflow = app(\App\Services\MilestoneWorkflowService::class);
        return $workflow->isSupervisorApproved($this);
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
        $slug = $this->template?->slug;

        // Custom tracks for explicit institutional milestone steps
        if ($slug === 'seminar_as_a_course') {
            $isScheduled = !empty($this->defence_date);
            $workflow = app(\App\Services\MilestoneWorkflowService::class);
            $isGraded = $workflow->hasBeenGraded($this);
            $isApproved = $this->status === 'approved';

            $tasks = [
                [
                    'id' => 'seminar_schedule',
                    'name' => 'Presentation Scheduled',
                    'completed' => $isScheduled || $isApproved,
                    'details' => $isScheduled ? 'Scheduled for ' . \Carbon\Carbon::parse($this->defence_date)->format('M d, Y') : 'Awaiting admin schedule',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'seminar_graded',
                    'name' => 'Presentation Graded',
                    'completed' => $isGraded || $isApproved,
                    'details' => $isGraded ? 'Graded by examiner' : 'Awaiting presentation examination',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'seminar_session_end',
                    'name' => 'End Presentation Session',
                    'completed' => $isApproved,
                    'details' => $isApproved ? 'Session concluded by Admin' : 'Admin presentation clearance',
                    'action_type' => 'none',
                ],
            ];
            $completedTasks = count(array_filter($tasks, fn($t) => $t['completed']));
            $percentage = $isApproved ? 100 : floor(($completedTasks / count($tasks)) * 100);

            return [
                'tasks' => $tasks,
                'total' => count($tasks),
                'completed' => $completedTasks,
                'percentage' => $percentage,
                'is_fully_complete' => $percentage == 100,
            ];
        }

        if ($slug === 'supervisors_assigned') {
            $hasProposal = $this->submissions()->exists();
            $hasSupervisors = $this->thesis?->assignments()->where('status', 'active')->exists();
            $isApproved = $this->status === 'approved';

            $tasks = [
                [
                    'id' => 'tentative_proposal',
                    'name' => 'Upload Tentative Proposal',
                    'completed' => $hasProposal || $isApproved,
                    'details' => $hasProposal ? 'Tentative proposal received' : 'Awaiting tentative proposal upload (PDF)',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'assign_supervisors',
                    'name' => 'Supervisors Assigned',
                    'completed' => $hasSupervisors || $isApproved,
                    'details' => $hasSupervisors ? 'Supervisors allocated by coordinator' : 'Awaiting supervisor allocation',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'auto_advance',
                    'name' => 'Advancement to Proposal Defence',
                    'completed' => $isApproved,
                    'details' => $isApproved ? 'Advanced to Proposal Defence' : 'Automatic upon upload & assignment',
                    'action_type' => 'none',
                ],
            ];
            $completedTasks = count(array_filter($tasks, fn($t) => $t['completed']));
            $percentage = $isApproved ? 100 : floor(($completedTasks / count($tasks)) * 100);

            return [
                'tasks' => $tasks,
                'total' => count($tasks),
                'completed' => $completedTasks,
                'percentage' => $percentage,
                'is_fully_complete' => $percentage == 100,
            ];
        }

        if (in_array($slug, ['proposal_defence', 'progress_report_1', 'progress_report_2'])) {
            $workflow = app(\App\Services\MilestoneWorkflowService::class);
            $hasUploaded = $workflow->hasUploadedPresentation($this) || $this->submissions()->exists();
            $isSupervisorApproved = $workflow->isSupervisorApproved($this);
            $isScheduled = !empty($this->defence_date);
            $presented = ($isScheduled && \Carbon\Carbon::parse($this->defence_date)->startOfDay()->lte(now()->startOfDay()))
                || $workflow->hasBeenGraded($this);
            $isApproved = $this->status === 'approved';

            $taskName = match($slug) {
                'proposal_defence' => 'Upload Proposal Document / Presentation',
                'progress_report_1', 'progress_report_2' => 'Upload Progress Report / Presentation',
                default => 'Upload Presentation / Document',
            };

            $tasks = [
                [
                    'id' => 'upload_ppt',
                    'name' => $taskName,
                    'completed' => $hasUploaded || $isApproved,
                    'details' => $hasUploaded ? 'Document / presentation uploaded' : 'Awaiting document upload',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'supervisor_approval',
                    'name' => 'Supervisor Approval',
                    'completed' => $isSupervisorApproved || $isApproved,
                    'details' => $isSupervisorApproved ? 'Upload approved by supervisor' : 'Awaiting supervisor review & acceptance',
                    'action_type' => 'none',
                ],
                [
                    'id' => 'presentation_done',
                    'name' => 'Presentation Conducted',
                    'completed' => $presented || $isApproved,
                    'details' => $presented ? 'Presentation conducted' : ($isScheduled ? 'Scheduled for ' . \Carbon\Carbon::parse($this->defence_date)->format('M d, Y') : 'Awaiting schedule'),
                    'action_type' => 'none',
                ],
                [
                    'id' => 'admin_end_presentation',
                    'name' => 'End Presentation Clearance',
                    'completed' => $isApproved,
                    'details' => $isApproved ? 'Presentation session ended by Admin' : 'Admin concludes presentation session',
                    'action_type' => 'none',
                ],
            ];
            $completedTasks = count(array_filter($tasks, fn($t) => $t['completed']));
            $percentage = $isApproved ? 100 : floor(($completedTasks / count($tasks)) * 100);

            return [
                'tasks' => $tasks,
                'total' => count($tasks),
                'completed' => $completedTasks,
                'percentage' => $percentage,
                'is_fully_complete' => $percentage == 100,
            ];
        }

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
            $isTentative = ($this->template->slug === 'supervisors_assigned');
            $tasks[] = [
                'id' => 'student_submission',
                'name' => $isTentative ? 'Tentative Proposal Submission' : 'Student Submission',
                'completed' => $hasSubmission,
                'action_type' => 'none',
                'details' => $isTentative ? ($hasSubmission ? 'Tentative proposal received' : 'Awaiting tentative proposal upload') : 'Awaiting student upload'
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
