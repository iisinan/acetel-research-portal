<?php

namespace App\Services;

use App\Models\StudentMilestone;
use App\Models\ThesisProject;
use App\Models\SupervisionAssignment;
use App\Models\CommunicationChannel;
use App\Models\ExaminerAssignment;
use Illuminate\Support\Facades\DB;
use Exception;

class MilestoneWorkflowService
{
    /**
     * Check if a milestone can be transitioned based on business rules.
     */
    public function canApprove(StudentMilestone $milestone, $user, ?string $role = null): bool
    {
        return empty($this->getApprovalBlockReason($milestone, $user, $role));
    }

    /**
     * Get the reason why a milestone cannot be approved yet.
     */
    public function getApprovalBlockReason(StudentMilestone $milestone, $user, ?string $role = null): ?string
    {
        $template = $milestone->template;
        if (!$template) return null;

        // 0. Sequence Enforcement
        $prevMilestone = $milestone->thesis?->milestones()
            ?->select('student_milestones.*')
            ->join('milestone_templates', 'student_milestones.milestone_template_id', '=', 'milestone_templates.id')
            ->where('milestone_templates.order', '<', $template->order)
            ->orderBy('milestone_templates.order', 'desc')
            ->first();

        if ($prevMilestone && !$prevMilestone->progress_track['is_fully_complete']) {
            if (!$user->hasRole('Admin')) {
                return "Sequence Blocked: Milestone " . ($prevMilestone->template?->order ?? '?') . " (" . ($prevMilestone->template?->name ?? 'Previous') . ") must be 100% complete first.";
            }
        }

        // 1. Missing artifact
        if ($template->requires_submission && $milestone->submissions()->count() === 0) {
            return "Documentation Required: Student has not uploaded the required artifacts for this stage.";
        }

        // Supervisor validation check: If milestone requires supervisor approval, supervisor must have accepted the upload
        if (in_array('Supervisor', $template->required_approvers ?? [])) {
            $latestSub = $milestone->submissions()->latest()->first();
            $isUploadAccepted = $milestone->is_supervisor_approved || 
                ($latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'approved');
            
            if (!$isUploadAccepted) {
                return "Supervisor Review Required: Supervisor must review and accept the candidate's uploaded document before Admin clearance.";
            }
        }

        // 2. Submission approval locked
        if ($template->submission_requires_approval && !$milestone->is_submission_unlocked) {
            return "Submission Gated: Post-submission authorization is required before clearance.";
        }

        // Structural Requirements
        if ($template->show_supervisor_assignment && $milestone->thesis->assignments()->where('status', 'active')->count() === 0) {
            return "Structural Block: Supervisors must be assigned before approval.";
        }
        if ($template->show_internal_examiner_assignment && empty($milestone->thesis->internal_examiner_profile_id)) {
            return "Structural Block: Internal Examiner must be assigned before approval.";
        }
        if ($template->show_external_examiner_assignment && empty($milestone->thesis->external_examiner_profile_id)) {
            return "Structural Block: External Examiner must be assigned before approval.";
        }
        if ($template->allow_defence_date && empty($milestone->defence_date)) {
            return "Structural Block: Defence date must be scheduled before approval.";
        }

        // 3. Strict Admin Authorization Check
        if ($role && $role !== 'Admin') {
            return "Institutional Authority Required: Only an Administrator can approve milestones.";
        }

        return null;
    }

    /**
     * Handle the specific logic after a milestone is approved.
     */
    public function afterApproval(StudentMilestone $milestone)
    {
        $template = $milestone->template;
        $project = $milestone->thesis;

        switch ($template->slug) {
            case 'seminar_as_a_course':
                $this->activateCommunicationChannels($project);
                $studentUser = $project->student->user ?? null;
                if ($studentUser) {
                    \Illuminate\Support\Facades\Mail::raw("Your 'Seminar as a course' milestone has been marked as done.", function($msg) use ($studentUser) {
                        $msg->to($studentUser->email)->subject("Milestone Completed: Seminar as a course");
                    });
                }
                break;
            case 'supervisors_assigned':
                $this->activateCommunicationChannels($project);
                $project->update(['status' => 'active']);
                break;
            case 'proposal_defence':
                $project->update(['status' => 'proposal_passed']);
                break;
            case 'progress_presentation_1':
            case 'progress_report_1':
                // no specific status update needed, just progress
                break;
            case 'progress_presentation_2':
            case 'progress_report_2':
                // no specific status update needed
                break;
            case 'internal_defence':
                $project->update(['status' => 'internal_passed']);
                break;
            case 'viva':
                $project->update([
                    'status' => 'completed',
                    'end_date' => now()
                ]);
                
                // End supervisor assignments and free up their load
                $activeAssignments = $project->assignments()->where('status', 'active')->get();
                foreach($activeAssignments as $assignment) {
                    if ($assignment->supervisor) {
                        $assignment->supervisor->decrement('current_load');
                    }
                    $assignment->update([
                        'status' => 'ended',
                        'ended_at' => now()
                    ]);
                }
                break;
        }

        // Automatically activate the next sequential milestone if it was not started
        $nextMilestone = $project->milestones()
            ->select('student_milestones.*')
            ->join('milestone_templates', 'student_milestones.milestone_template_id', '=', 'milestone_templates.id')
            ->where('milestone_templates.order', '>', $template->order)
            ->where('student_milestones.status', '!=', 'approved')
            ->orderBy('milestone_templates.order', 'asc')
            ->first();

        if ($nextMilestone && $nextMilestone->status === 'not_started') {
            $nextMilestone->update(['status' => 'in_progress']);
        }
    }

    /**
     * Validate supervisor counts: MSc=2, PhD=3.
     * Rule 1: PhD must have 3 supervisors, Primary must be a Professor.
     * Rule 2: MSc must have 2 supervisors.
     */
    public function validateSupervisorAssignment(ThesisProject $project, array $supervisorIds): void
    {
        $count = count($supervisorIds);
        $student = $project->student;
        $levelName = strtoupper(optional($student->level)->name ?? '');

        if (str_contains($levelName, 'PHD')) {
             if ($count !== 3) {
                throw new Exception("Institutional PhD Protocol: The supervision panel must consist of exactly 3 authorized members.");
             }
        } else {
             if ($count !== 2) {
                throw new Exception("Institutional MSc/Standard Protocol: The supervision panel must consist of exactly 2 authorized members.");
             }
        }

        // Institutional Hierarchy Rule: Primary Supervisor (index 0) must be a Professor (MSc & PhD)
        $primaryId = $supervisorIds[0];
        $primaryProfile = \App\Models\SupervisorProfile::find($primaryId);
        
        if (!$primaryProfile || strtoupper($primaryProfile->rank ?? '') !== 'PROFESSOR') {
            throw new Exception("Academic Hierarchy Violation: The Lead Supervisor must hold the rank of Professor.");
        }
    }

    /**
     * Check if the required number of approvals (threshold) has been met.
     * Institutional Rule: ONLY Admin approval satisfies milestone completion and student progression.
     */
    public function isApprovalThresholdMet(StudentMilestone $milestone): bool
    {
        $template = $milestone->template;
        $approvals = collect($milestone->approvals ?? []);
        
        // Ensure structural requirements are met before allowing final clearance
        if ($template->show_supervisor_assignment && $milestone->thesis->assignments()->where('status', 'active')->count() === 0) return false;
        if ($template->show_internal_examiner_assignment && empty($milestone->thesis->internal_examiner_profile_id)) return false;
        if ($template->show_external_examiner_assignment && empty($milestone->thesis->external_examiner_profile_id)) return false;
        if ($template->allow_defence_date && empty($milestone->defence_date)) return false;

        // ONLY Admin approval satisfies milestone completion and advancement
        if ($approvals->where('role', 'Admin')->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * Create the communication channels for the project.
     */
    private function activateCommunicationChannels(ThesisProject $project)
    {
        CommunicationChannel::firstOrCreate([
            'thesis_project_id' => $project->id,
            'type' => 'supervision'
        ], [
            'created_by' => auth()->id() ?? $project->student->user_id
        ]);
    }

    /**
     * Notify all relevant parties of a milestone update.
     */
    public function notifyUpdate(StudentMilestone $milestone, string $message)
    {
        $recipients = collect();
        
        // 1. The Student
        if ($milestone->thesis && $milestone->thesis->student) {
            $recipients->push($milestone->thesis->student->user_id);
        }

        // 2. All Assigned Supervisors
        if ($milestone->thesis) {
            foreach ($milestone->thesis->assignments as $assignment) {
                if ($assignment->supervisor) {
                    $recipients->push($assignment->supervisor->user_id);
                }
            }
        }

        // 3. Program Coordinators
        if ($milestone->thesis && $milestone->thesis->student) {
            $coords = \App\Models\CoordinatorProfile::where('program_id', $milestone->thesis->student->program_id)
                ->where('active', true)
                ->pluck('user_id');
            $recipients = $recipients->merge($coords);
        }

        // 4. Admin (if not the one doing the update, but usually admin is the one being asked about)
        // We'll just notify everyone unique
        $recipients = $recipients->unique()->filter();

        foreach ($recipients as $userId) {
            \App\Events\MilestoneUpdated::dispatch($milestone, $message, $userId);
        }
    }
}
