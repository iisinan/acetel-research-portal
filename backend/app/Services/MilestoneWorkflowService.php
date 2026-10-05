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
        if ($template->requires_submission && $milestone->submissions()->count() === 0 && !$user->hasRole('Admin')) {
            return "Documentation Required: Student has not uploaded the required artifacts for this stage.";
        }

        // Supervisor validation check: If milestone requires supervisor approval, supervisor must have accepted the upload
        if (in_array('Supervisor', $template->required_approvers ?? [])) {
            $latestSub = $milestone->submissions()->latest()->first();
            $isUploadAccepted = $milestone->is_supervisor_approved || 
                ($latestSub && $latestSub->feedback && $latestSub->feedback->decision === 'approved');
            
            if (!$isUploadAccepted && !$user->hasRole('Admin')) {
                return "Supervisor Review Required: Supervisor must review and accept the candidate's uploaded document before Admin clearance.";
            }
        }

        // 2. Submission approval locked
        if ($template->submission_requires_approval && !$milestone->is_submission_unlocked && !$user->hasRole('Admin')) {
            return "Submission Gated: Post-submission authorization is required before clearance.";
        }

        // Supervisors Assigned validation: tentative proposal + supervisor allocation
        if ($template->slug === 'supervisors_assigned') {
            $hasProposal = $milestone->submissions()->exists();
            $hasSupervisors = $milestone->thesis->assignments()->where('status', 'active')->exists();
            if (!$hasProposal && !$user->hasRole('Admin')) {
                return "Tentative Proposal Required: Candidate must upload their tentative proposal before clearance.";
            }
            if (!$hasSupervisors && !$user->hasRole('Admin')) {
                return "Supervision Required: Programme coordinator must assign supervisors before clearance.";
            }
        }

        // Structural Requirements
        if ($template->show_supervisor_assignment && $milestone->thesis->assignments()->where('status', 'active')->count() === 0 && !$user->hasRole('Admin')) {
            return "Structural Block: Supervisors must be assigned before approval.";
        }
        if ($template->show_internal_examiner_assignment && empty($milestone->thesis->internal_examiner_profile_id) && !$user->hasRole('Admin')) {
            return "Structural Block: Internal Examiner must be assigned before approval.";
        }
        if ($template->show_external_examiner_assignment && empty($milestone->thesis->external_examiner_profile_id) && !$user->hasRole('Admin')) {
            return "Structural Block: External Examiner must be assigned before approval.";
        }
        if ($template->allow_defence_date && empty($milestone->defence_date) && !$user->hasRole('Admin')) {
            return "Structural Block: Defence date must be scheduled before approval.";
        }

        // 3. Presentation Gated Milestones (Requirement 5): Average grading must be pass
        if (in_array($template->slug, self::PRESENTATION_GATED_SLUGS)) {
            $gradingOutcome = $this->getAverageGradingOutcome($milestone);
            if ($gradingOutcome === 'fail') {
                return "Grading Outcome is FAIL: Candidate received a FAIL verdict from examiners and must repeat this milestone.";
            }
        }

        // 4. Strict Admin Authorization Check
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
                    try {
                        \Illuminate\Support\Facades\Mail::raw("Your 'Seminar as a course' milestone has been marked as done.", function($msg) use ($studentUser) {
                            $msg->to($studentUser->email)->subject("Milestone Completed: Seminar as a course");
                        });
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Seminar completion mail failed: ' . $e->getMessage());
                    }
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

        // Ensure all milestones exist for this project
        $project->syncMilestones();

        // Automatically activate the next sequential milestone if not already approved
        $nextMilestone = $project->milestones()
            ->select('student_milestones.*')
            ->join('milestone_templates', 'student_milestones.milestone_template_id', '=', 'milestone_templates.id')
            ->where('milestone_templates.order', '>', $template->order)
            ->where('student_milestones.status', '!=', 'approved')
            ->orderBy('milestone_templates.order', 'asc')
            ->first();

        if ($nextMilestone && $nextMilestone->status !== 'approved') {
            $nextMilestone->update(['status' => 'in_progress']);
        }

        // Supervisors Assigned -> Proposal Defence may already be satisfied
        if ($template->slug !== 'supervisors_assigned') {
            $this->tryAutoAdvanceSupervisorsAssigned($project->fresh());
        }
    }

    /**
     * Milestones that advance via the admin "End Presentation Session" button.
     */
    public const PRESENTATION_GATED_SLUGS = [
        'seminar_as_a_course',
        'proposal_defence',
        'progress_report_1',
        'progress_report_2',
    ];

    /**
     * Has at least one examiner graded this student's presentation for the milestone?
     */
    public function hasBeenGraded(StudentMilestone $milestone): bool
    {
        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null,
        };
        $events = $milestone->thesis?->defenceEvents()
            ->when($type, fn($q) => $q->where('type', $type))
            ->withCount('evaluations')
            ->get() ?? collect();

        if ($events->sum('evaluations_count') > 0) {
            return true;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('seminar_grades')) {
            if (\Illuminate\Support\Facades\DB::table('seminar_grades')->where('student_milestone_id', $milestone->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has the supervisor approved/accepted the candidate's upload for this milestone?
     */
    public function isSupervisorApproved(StudentMilestone $milestone): bool
    {
        if ($milestone->is_supervisor_approved) {
            return true;
        }

        // Also check if any submission has approved feedback from a supervisor or admin
        $hasApprovedFeedback = $milestone->submissions()
            ->whereHas('feedback', fn($q) => $q->where('decision', 'approved'))
            ->exists();

        if ($hasApprovedFeedback) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('student_milestones', 'is_supervisor_approved')) {
                $milestone->update(['is_supervisor_approved' => true]);
            }
            return true;
        }

        return false;
    }

    /**
     * Has the student uploaded the presentation artifact (PPT) or document for this milestone?
     */
    public function hasUploadedPresentation(StudentMilestone $milestone): bool
    {
        // 1. Direct type match
        if ($milestone->submissions()->whereIn('type', ['ppt', 'presentation'])->exists()) {
            return true;
        }

        // 2. Scan submissions case-insensitively
        $subs = $milestone->submissions()->get();
        foreach ($subs as $sub) {
            $desc = strtolower($sub->description ?? '');
            $url = strtolower($sub->file_url ?? '');
            $orig = strtolower($sub->file_meta['original_name'] ?? '');

            if (str_contains($desc, 'ppt') || str_contains($desc, 'presentation') || str_contains($desc, 'slide')) {
                return true;
            }
            if (str_contains($url, '.ppt') || str_contains($orig, '.ppt')) {
                return true;
            }
        }

        // 3. Fallback: Any submission uploaded for this milestone (manuscript, proposal document, etc.)
        if ($milestone->submissions()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Returns null if the student may be advanced when admin ends the presentation
     * session for this milestone, otherwise a human-readable reason.
     */
    public function getEndSessionBlockReason(StudentMilestone $milestone): ?string
    {
        $slug = $milestone->template?->slug;

        if (empty($milestone->defence_date)) {
            return 'not scheduled for presentation';
        }

        if ($slug === 'seminar_as_a_course') {
            if (!$this->hasBeenGraded($milestone)) {
                return 'not yet graded by any examiner';
            }
            return null;
        }

        if (in_array($slug, ['proposal_defence', 'progress_report_1', 'progress_report_2'])) {
            if (!$this->hasUploadedPresentation($milestone)) {
                return 'required document/presentation not uploaded';
            }
            if (!$this->isSupervisorApproved($milestone)) {
                return 'upload not yet approved by supervisor';
            }
            if (!$this->hasBeenGraded($milestone)) {
                return 'not yet evaluated by examiner(s)';
            }
            $presented = \Carbon\Carbon::parse($milestone->defence_date)->startOfDay()->lte(now()->startOfDay())
                || $this->hasBeenGraded($milestone);
            if (!$presented) {
                return 'presentation date (' . \Carbon\Carbon::parse($milestone->defence_date)->format('d M Y') . ') has not been reached';
            }
            return null;
        }

        // Internal defence / Viva rules are not yet defined — keep previous behaviour.
        return null;
    }

    /**
     * Compute average grading outcome from examiners for presentation defence milestones.
     * Returns 'pass', 'fail', or null if not yet graded.
     */
    public function getAverageGradingOutcome(StudentMilestone $milestone): ?string
    {
        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null,
        };

        $events = \App\Models\DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id);
        if ($type) {
            $events->where('type', $type);
        }
        $events = $events->with('evaluations')->get();

        $passCount = 0;
        $failCount = 0;

        foreach ($events as $event) {
            foreach ($event->evaluations as $eval) {
                $verdict = strtolower($eval->score['verdict'] ?? $eval->recommendation ?? '');
                if ($verdict === 'pass') {
                    $passCount++;
                } elseif ($verdict === 'fail') {
                    $failCount++;
                } elseif (isset($eval->score['total'])) {
                    if ($eval->score['total'] >= 50) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                } elseif (in_array($verdict, ['minor_revisions', 'major_revisions'])) {
                    $passCount++;
                }
            }
        }

        if ($passCount === 0 && $failCount === 0) {
            return null;
        }

        return ($passCount >= $failCount) ? 'pass' : 'fail';
    }

    /**
     * Mark a milestone as failed, requiring candidate to repeat this milestone.
     * Status is set to revision_required, schedule cleared, and notifications sent.
     */
    public function failAndRepeatMilestone(StudentMilestone $milestone, string $reason): void
    {
        $approvals = $milestone->approvals ?? [];
        $approvals[] = [
            'user_id' => auth()->id(),
            'role' => 'ExaminerPanel',
            'note' => $reason,
            'approved_at' => now()->toDateTimeString(),
            'verdict' => 'fail',
        ];

        $updateData = [
            'status' => 'revision_required',
            'defence_date' => null,
            'defence_time' => null,
            'meeting_link' => null,
            'approvals' => $approvals,
            'remark' => "Milestone evaluation outcome: FAIL. The candidate is required to repeat this stage: {$reason}",
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('student_milestones', 'is_supervisor_approved')) {
            $updateData['is_supervisor_approved'] = false;
        }

        $milestone->update($updateData);

        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null,
        };

        if ($type && $milestone->thesis_project_id) {
            \App\Models\DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)
                ->where('type', $type)
                ->update(['outcome' => 'fail']);
        }

        $studentUserId = $milestone->thesis?->student?->user_id;
        if ($studentUserId) {
            \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUserId);

            // Deliver official outcome notice directly to candidate's personal inbox (Requirements 3 & 5)
            try {
                $milestoneTitle = $milestone->template?->title ?? $milestone->template?->name ?? 'Milestone Defence';
                $studentName = $milestone->thesis?->student?->user?->name ?? 'Candidate';
                $inboxMsg = \App\Models\InboxMessage::create([
                    'sender_id' => auth()->id() ?? $studentUserId,
                    'subject' => "Milestone Outcome: {$milestoneTitle} - FAIL (Repeat Required)",
                    'body' => "Dear {$studentName},\n\n"
                            . "Following the examiner panel evaluation for your \"{$milestoneTitle}\", your average grading outcome was recorded as FAIL.\n\n"
                            . "Feedback / Reason: {$reason}\n\n"
                            . "In accordance with institutional guidelines, you are required to repeat this milestone stage. Please liaise with your supervisor to make necessary revisions and re-submit your materials for approval.\n\n"
                            . "Academic Portal Administration",
                    'delivery_method' => 'in_app',
                ]);

                $inboxMsg->recipients()->attach($studentUserId, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to',
                ]);

                \App\Events\MessageReceived::dispatch($inboxMsg, $studentUserId);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to create inbox message on milestone failure: " . $e->getMessage());
            }
        }

        $this->notifyUpdate($milestone, "Milestone evaluation outcome: FAIL. The candidate is required to repeat this stage: {$reason}");
    }

    /**
     * Mark a milestone approved by the system/admin and run the post-approval workflow.
     */
    public function approveAndAdvance(StudentMilestone $milestone, string $note): void
    {
        // Requirement 5: Average grading must be pass. If fail, candidate repeats this milestone.
        $gradingOutcome = $this->getAverageGradingOutcome($milestone);
        if ($gradingOutcome === 'fail') {
            $this->failAndRepeatMilestone($milestone, 'Candidate received a FAIL grade on presentation evaluation and must repeat this milestone.');
            return;
        }

        $approvals = $milestone->approvals ?? [];
        $approvals[] = [
            'user_id' => auth()->id(),
            'role' => 'Admin',
            'note' => $note,
            'approved_at' => now()->toDateTimeString(),
        ];

        $milestone->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approvals' => $approvals,
        ]);

        $this->afterApproval($milestone->fresh());

        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null,
        };

        if ($type && $milestone->thesis_project_id) {
            \App\Models\DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)
                ->where('type', $type)
                ->where(function($q) {
                    $q->whereNull('outcome')->orWhereIn('outcome', ['pending', 'retry']);
                })
                ->update(['outcome' => 'pass']);
        }

        $studentUserId = $milestone->thesis?->student?->user_id;
        if ($studentUserId) {
            \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUserId);
        }

        $this->notifyUpdate($milestone, $note);
    }

    /**
     * Supervisors Assigned -> Proposal Defence.
     * Advances automatically once (a) the student has uploaded a tentative proposal on the
     * Supervisors Assigned milestone and (b) supervisors have been assigned.
     */
    public function tryAutoAdvanceSupervisorsAssigned(?ThesisProject $project): bool
    {
        if (!$project) return false;

        $milestone = $project->milestones()
            ->whereHas('template', fn($q) => $q->where('slug', 'supervisors_assigned'))
            ->where('status', '!=', 'approved')
            ->with('template')
            ->first();

        if (!$milestone) return false;

        // All earlier milestones (e.g. Seminar course) must be completed first.
        $pendingEarlier = $project->milestones()
            ->whereHas('template', fn($q) => $q->where('order', '<', $milestone->template->order))
            ->where('status', '!=', 'approved')
            ->exists();
        if ($pendingEarlier) return false;

        $hasProposal = $milestone->submissions()->exists();
        $hasSupervisors = $project->assignments()->where('status', 'active')->exists();

        if (!$hasProposal || !$hasSupervisors) return false;

        $this->approveAndAdvance($milestone, 'Auto-advanced: tentative proposal uploaded and supervisors assigned.');
        $this->notifyUpdate($milestone, 'Supervisors assigned and tentative proposal received — advanced to Proposal Defence.');

        return true;
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
