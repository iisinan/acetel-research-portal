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

        // 3b. Internal Defence Post-Presentation Correction Gate
        if ($this->isInternalDefence($milestone)) {
            if (!$this->isPostDefencePhase($milestone)) {
                return "Presentation Session Required: The Internal Defence presentation must be graded as PASS and ended by Admin before post-defence correction clearance.";
            }
            if (!$this->hasPostDefenceSubmission($milestone)) {
                return "Post-Defence Correction Required: Candidate must upload their corrected thesis manuscript after the presentation before advancing to Viva.";
            }
        }

        // 4. Authorization Check: Admin can approve all; Internal Examiner can approve internal_defence
        $isInternalExaminerForThis = $this->isInternalDefence($milestone) &&
            $this->isAssignedInternalExaminer($milestone, $user);

        if ($role && $role !== 'Admin' && !$isInternalExaminerForThis) {
            return "Institutional Authority Required: Only an Administrator or assigned Internal Examiner can approve this milestone.";
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
        'internal_defence',
    ];

    /**
     * Is this the Internal Defence milestone?
     */
    public function isInternalDefence(StudentMilestone $milestone): bool
    {
        return ($milestone->template?->slug === 'internal_defence' || (int) ($milestone->template?->order ?? 0) === 6);
    }

    /**
     * Has the Internal Defence live presentation been concluded with a PASS grade by Admin,
     * placing the student in the Post-Defence Correction stage?
     */
    public function isPostDefencePhase(StudentMilestone $milestone): bool
    {
        if (!$this->isInternalDefence($milestone) || $milestone->status === 'approved') {
            return false;
        }

        $approvals = $milestone->approvals ?? [];
        if (is_string($approvals)) {
            $approvals = json_decode($approvals, true) ?: [];
        }

        if (!empty($approvals['presentation_concluded'])) {
            return true;
        }

        // Fallback: check if the internal DefenceEvent already has outcome = 'pass'
        if ($milestone->relationLoaded('thesis') && $milestone->thesis && $milestone->thesis->relationLoaded('defenceEvents')) {
            $internalEvent = $milestone->thesis->defenceEvents->where('type', 'internal')->first();
            if ($internalEvent && $internalEvent->outcome === 'pass') {
                return true;
            }
        } elseif ($milestone->thesis_project_id) {
            if (\App\Models\DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)->where('type', 'internal')->where('outcome', 'pass')->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the latest manuscript uploaded by the student AFTER the Internal Defence presentation ended.
     */
    public function getPostDefenceSubmission(StudentMilestone $milestone): ?\App\Models\Submission
    {
        if (!$this->isPostDefencePhase($milestone)) {
            return null;
        }

        $approvals = $milestone->approvals ?? [];
        if (is_string($approvals)) {
            $approvals = json_decode($approvals, true) ?: [];
        }

        $subs = $milestone->relationLoaded('submissions')
            ? $milestone->submissions->whereIn('type', ['manuscript', 'file'])->sortByDesc('created_at')
            : $milestone->submissions()->whereIn('type', ['manuscript', 'file'])->latest()->get();

        if ($subs->isEmpty()) {
            return null;
        }

        $postSubId = $approvals['post_defence_submission_id'] ?? null;
        if ($postSubId) {
            $found = $subs->firstWhere('id', $postSubId);
            if ($found) {
                return $found;
            }
        }

        $concludedAt = $approvals['presentation_concluded']['concluded_at'] ?? null;
        $lastPreSubId = $approvals['presentation_concluded']['last_pre_defence_submission_id'] ?? null;
        if ($concludedAt) {
            $concludedTime = \Carbon\Carbon::parse($concludedAt);
            $afterConcluded = $subs->first(fn($s) => ($lastPreSubId ? $s->id !== $lastPreSubId : true) && $s->created_at && $s->created_at->gte($concludedTime));
            if ($afterConcluded) {
                return $afterConcluded;
            }
        }

        // Legacy fallback: if description explicitly says Post-Defence or if uploaded after defence_date when >1 manuscript exists
        $explicitPost = $subs->first(fn($s) => str_contains(strtolower($s->description ?? ''), 'post-defence') || str_contains(strtolower($s->description ?? ''), 'corrected'));
        if ($explicitPost) {
            return $explicitPost;
        }

        if ($subs->count() > 1 && $milestone->defence_date) {
            $defDate = \Carbon\Carbon::parse($milestone->defence_date)->startOfDay();
            $afterDef = $subs->first(fn($s) => $s->created_at && $s->created_at->gte($defDate));
            if ($afterDef) {
                return $afterDef;
            }
        }

        return null;
    }

    /**
     * Has the student uploaded their corrected thesis manuscript after the Internal Defence presentation ended?
     */
    public function hasPostDefenceSubmission(StudentMilestone $milestone): bool
    {
        return !is_null($this->getPostDefenceSubmission($milestone));
    }

    /**
     * Check whether the given user is an assigned Internal Examiner for the given thesis.
     */
    public function isAssignedInternalExaminerForThesis(?ThesisProject $thesis, ?\App\Models\User $user): bool
    {
        if (!$thesis || !$user) {
            return false;
        }

        if ($thesis->internal_examiner_profile_id) {
            if ($user->internalExaminerProfiles()->where('id', $thesis->internal_examiner_profile_id)->exists()) {
                return true;
            }
        }

        if (\App\Models\ExaminerAssignment::where('thesis_project_id', $thesis->id)
            ->where('type', 'internal_examiner')
            ->where('examiner_id', $user->id)
            ->exists()) {
            return true;
        }

        if (\App\Models\DefenceEvent::where('thesis_project_id', $thesis->id)
            ->where('type', 'internal')
            ->whereHas('panelMembers', fn($q) => $q->where('user_id', $user->id)->where('role', 'examiner'))
            ->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the given user is an assigned Internal Examiner for the milestone's thesis.
     */
    public function isAssignedInternalExaminer(StudentMilestone $milestone, ?\App\Models\User $user): bool
    {
        return $this->isAssignedInternalExaminerForThesis($milestone->thesis, $user);
    }

    /**
     * Conclude the Internal Defence live presentation session when the candidate scored PASS.
     * Does NOT advance to Viva; transitions candidate to Post-Defence Correction stage.
     */
    public function concludeInternalDefencePresentation(StudentMilestone $milestone, ?\App\Models\User $admin = null): void
    {
        $approvals = $milestone->approvals ?? [];
        if (is_string($approvals)) {
            $approvals = json_decode($approvals, true) ?: [];
        }

        $lastPreSubId = $milestone->submissions()->whereIn('type', ['manuscript', 'file'])->latest()->value('id');
        $approvals['presentation_concluded'] = [
            'concluded_by' => $admin?->id ?? auth()->id(),
            'concluded_by_name' => $admin?->name ?? auth()->user()?->name ?? 'Admin',
            'concluded_at' => now()->toDateTimeString(),
            'grading_outcome' => 'pass',
            'last_pre_defence_submission_id' => $lastPreSubId,
        ];
        unset($approvals['post_defence_submission_id'], $approvals['post_defence_submitted_at'], $approvals['internal_examiner_review']);

        $milestone->update([
            'status' => 'in_progress',
            'approvals' => $approvals,
            'remark' => 'Internal defence presentation passed and concluded. Please upload your corrected thesis manuscript incorporating defence feedback for Internal Examiner approval.',
        ]);

        if ($milestone->thesis_project_id) {
            \App\Models\DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)
                ->where('type', 'internal')
                ->update(['outcome' => 'pass']);
        }

        $studentUserId = $milestone->thesis?->student?->user_id;
        if ($studentUserId) {
            \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUserId);

            try {
                $milestoneTitle = $milestone->template?->title ?? $milestone->template?->name ?? 'Internal Defence';
                $studentName = $milestone->thesis?->student?->user?->name ?? 'Candidate';
                $examinerName = $milestone->thesis?->internalExaminer?->user?->name ?? 'your assigned Internal Examiner';

                $inboxMsg = \App\Models\InboxMessage::create([
                    'sender_id' => $admin?->id ?? auth()->id() ?? $studentUserId,
                    'subject' => "Internal Defence Presentation Passed — Upload Post-Defence Corrected Thesis",
                    'body' => "Dear {$studentName},\n\n"
                            . "Your \"{$milestoneTitle}\" presentation session has been concluded with a PASS grade.\n\n"
                            . "Next Step Before Advancing to Viva:\n"
                            . "Please incorporate all corrections and observations raised during your presentation and upload your corrected thesis manuscript on your Internal Defence milestone page.\n\n"
                            . "Once uploaded, {$examinerName} will review your corrections. Upon their approval, you will automatically advance to the Viva milestone.\n\n"
                            . "Academic Portal Administration",
                    'delivery_method' => 'in_app',
                ]);

                $inboxMsg->recipients()->attach($studentUserId, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to',
                ]);

                \App\Events\MessageReceived::dispatch($inboxMsg, $studentUserId);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to create inbox message on internal defence conclusion: " . $e->getMessage());
            }
        }

        $this->notifyUpdate(
            $milestone,
            "Internal defence presentation session concluded (PASS). Candidate must now upload the corrected thesis manuscript for Internal Examiner review and approval to advance to Viva."
        );
    }

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
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => null,
        };

        // 1. Check in-memory eager loaded defenceEvents to avoid N+1 queries
        if ($milestone->relationLoaded('thesis') && $milestone->thesis && $milestone->thesis->relationLoaded('defenceEvents')) {
            $events = $milestone->thesis->defenceEvents;
            if ($type) {
                $events = $events->where('type', $type);
            }
            $hasEvals = $events->contains(function ($e) {
                if ($e->relationLoaded('evaluations')) {
                    return $e->evaluations->isNotEmpty();
                }
                return $e->evaluations()->exists();
            });
            if ($hasEvals) {
                return true;
            }
        } else {
            $events = $milestone->thesis?->defenceEvents()
                ->when($type, fn($q) => $q->where('type', $type))
                ->withCount('evaluations')
                ->get() ?? collect();

            if ($events->sum('evaluations_count') > 0) {
                return true;
            }
        }

        // 2. Check seminar_grades table if present
        static $hasSeminarGradesTable = null;
        if ($hasSeminarGradesTable === null) {
            $hasSeminarGradesTable = \Illuminate\Support\Facades\Schema::hasTable('seminar_grades');
        }
        if ($hasSeminarGradesTable) {
            if (\Illuminate\Support\Facades\DB::table('seminar_grades')->where('student_milestone_id', $milestone->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has the supervisor approved/accepted the candidate's upload for this milestone?
     * Enforces the institutional multi-supervisor policy:
     * - Any active rejection or pending re-review blocks eligibility.
     * - At least one supervisor must approve (if 1 of 3 approves and 0 reject, candidate is eligible).
     * - Rejection by another supervisor revokes eligibility until resolved.
     * - Approvals given by a supervisor stand across student re-uploads.
     */
    public function isSupervisorApproved(StudentMilestone $milestone): bool
    {
        if ($milestone->status === 'approved') {
            return true;
        }

        // Once Internal Defence presentation is concluded with a pass, pre-defence supervisor approval is already complete
        if ($this->isPostDefencePhase($milestone)) {
            return true;
        }

        if ($milestone->status === 'revision_required') {
            return false;
        }

        return $this->computeSupervisorApprovalEligibility($milestone);
    }

    /**
     * Compute supervisor approval eligibility based on multi-supervisor policy.
     */
    public function computeSupervisorApprovalEligibility(StudentMilestone $milestone): bool
    {
        if ($milestone->status === 'approved' || $this->isPostDefencePhase($milestone)) {
            return true;
        }

        if ($milestone->status === 'revision_required') {
            return false;
        }

        $summary = $this->getSupervisorReviewSummary($milestone);
        return $summary['is_eligible'];
    }

    /**
     * Get a comprehensive summary of all assigned supervisor reviews for this milestone.
     */
    public function getSupervisorReviewSummary(StudentMilestone $milestone): array
    {
        $approvals = $milestone->approvals ?? [];
        $reviews = $approvals['supervisor_reviews'] ?? [];

        // Active supervisor assignments for this thesis
        $assignments = $milestone->thesis?->relationLoaded('assignments')
            ? $milestone->thesis->assignments->where('status', 'active')
            : ($milestone->thesis?->assignments()->where('status', 'active')->with(['supervisor.user'])->get() ?? collect());

        if ($assignments->isEmpty()) {
            $hasAnyFeedbackApproved = false;
            if ($milestone->relationLoaded('submissions')) {
                foreach ($milestone->submissions as $sub) {
                    if ($sub->relationLoaded('feedbacks')) {
                        if ($sub->feedbacks->contains(fn($f) => $f->decision === 'approved')) { $hasAnyFeedbackApproved = true; break; }
                    } elseif ($sub->relationLoaded('feedback') && $sub->feedback) {
                        if ($sub->feedback->decision === 'approved') { $hasAnyFeedbackApproved = true; break; }
                    } else {
                        if ($sub->feedbacks()->where('decision', 'approved')->exists()) { $hasAnyFeedbackApproved = true; break; }
                    }
                }
            } else {
                $hasAnyFeedbackApproved = $milestone->submissions()->whereHas('feedbacks', fn($q) => $q->where('decision', 'approved'))->exists();
            }

            $isDirectlyApproved = !empty($milestone->getRawOriginal('is_supervisor_approved'))
                || $hasAnyFeedbackApproved;

            return [
                'has_supervisors' => false,
                'total_assigned' => 0,
                'approved_count' => $isDirectlyApproved ? 1 : 0,
                'rejected_count' => 0,
                'pending_rereview_count' => 0,
                'pending_count' => 0,
                'is_eligible' => $isDirectlyApproved,
                'status_label' => $isDirectlyApproved ? 'Approved' : 'Pending',
                'supervisors' => [],
            ];
        }

        $supervisorsList = [];
        $approvedCount = 0;
        $rejectedCount = 0;
        $pendingRereviewCount = 0;
        $pendingCount = 0;

        foreach ($assignments as $assignment) {
            $supUser = $assignment->supervisor?->user;
            if (!$supUser) continue;

            $userId = (string) $supUser->id;
            $review = $reviews[$userId] ?? null;

            $status = 'pending';
            $decision = $review['decision'] ?? null;
            $reviewedAt = $review['reviewed_at'] ?? null;
            $remarks = $review['remarks'] ?? null;

            if ($decision === 'approved') {
                $status = 'approved';
                $approvedCount++;
            } elseif ($decision === 'rejected') {
                $status = 'rejected';
                $rejectedCount++;
            } elseif ($decision === 'pending_re_review') {
                $status = 'pending_re_review';
                $pendingRereviewCount++;
            } else {
                // Fallback: check historical feedback for this user
                $hasFeedbackApproved = false;
                $hasFeedbackRejected = false;

                if ($milestone->relationLoaded('submissions')) {
                    foreach ($milestone->submissions as $sub) {
                        if ($sub->relationLoaded('feedbacks')) {
                            if ($sub->feedbacks->contains(fn($f) => $f->created_by == $supUser->id && $f->decision === 'approved')) { $hasFeedbackApproved = true; }
                            if ($sub->feedbacks->contains(fn($f) => $f->created_by == $supUser->id && $f->decision === 'revision_required')) { $hasFeedbackRejected = true; }
                        } elseif ($sub->relationLoaded('feedback') && $sub->feedback) {
                            $fb = $sub->feedback;
                            if ($fb->created_by == $supUser->id && $fb->decision === 'approved') { $hasFeedbackApproved = true; }
                            if ($fb->created_by == $supUser->id && $fb->decision === 'revision_required') { $hasFeedbackRejected = true; }
                        } else {
                            if ($sub->feedbacks()->where('created_by', $supUser->id)->where('decision', 'approved')->exists()) { $hasFeedbackApproved = true; }
                            if ($sub->feedbacks()->where('created_by', $supUser->id)->where('decision', 'revision_required')->exists()) { $hasFeedbackRejected = true; }
                        }
                    }
                } else {
                    $hasFeedbackApproved = $milestone->submissions()
                        ->whereHas('feedbacks', fn($q) => $q->where('created_by', $supUser->id)->where('decision', 'approved'))
                        ->exists();

                    $hasFeedbackRejected = $milestone->submissions()
                        ->whereHas('feedbacks', fn($q) => $q->where('created_by', $supUser->id)->where('decision', 'revision_required'))
                        ->exists();
                }

                if ($hasFeedbackRejected && $milestone->status === 'revision_required') {
                    $status = 'rejected';
                    $rejectedCount++;
                } elseif ($hasFeedbackApproved) {
                    $status = 'approved';
                    $approvedCount++;
                } else {
                    $pendingCount++;
                }
            }

            $supervisorsList[] = [
                'user_id' => $supUser->id,
                'name' => $supUser->name,
                'role' => ucfirst($assignment->role ?? 'Supervisor'),
                'status' => $status,
                'reviewed_at' => $reviewedAt,
                'remarks' => $remarks,
            ];
        }

        // Also count any Admin override review recorded in supervisor_reviews
        $assignedUserIds = collect($supervisorsList)->pluck('user_id')->map(fn($id) => (string) $id)->all();
        foreach ($reviews as $rUserId => $review) {
            if (!in_array((string) $rUserId, $assignedUserIds, true) && ($review['decision'] ?? null) === 'approved') {
                $approvedCount++;
            }
        }

        // Policy rules:
        // 1. Any active rejection or pending re-review blocks eligibility.
        // 2. At least one supervisor must approve.
        $hasBlocking = ($rejectedCount > 0) || ($pendingRereviewCount > 0);
        $isEligible = ($approvedCount >= 1) && !$hasBlocking;

        $statusLabel = 'Awaiting Review';
        if ($rejectedCount > 0) {
            $statusLabel = 'Revision Required';
        } elseif ($pendingRereviewCount > 0) {
            $statusLabel = 'Awaiting Re-Review';
        } elseif ($isEligible) {
            $statusLabel = 'Approved (' . $approvedCount . '/' . count($supervisorsList) . ')';
        }

        return [
            'has_supervisors' => true,
            'total_assigned' => count($supervisorsList),
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'pending_rereview_count' => $pendingRereviewCount,
            'pending_count' => $pendingCount,
            'is_eligible' => $isEligible,
            'status_label' => $statusLabel,
            'supervisors' => $supervisorsList,
        ];
    }

    /**
     * Record a supervisor's review (approved or rejected) and recompute milestone eligibility.
     */
    public function recordSupervisorReview(StudentMilestone $milestone, \App\Models\User $user, string $decision, ?string $remarks = null): array
    {
        $submission = $milestone->submissions()->latest()->first();

        // 1. Record feedback per supervisor
        if ($submission) {
            \App\Models\Feedback::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'created_by' => $user->id,
                ],
                [
                    'decision' => ($decision === 'approved') ? 'approved' : 'revision_required',
                    'remarks' => $remarks,
                ]
            );
        }

        // 2. Store supervisor review in milestone approvals JSON
        $approvals = $milestone->approvals ?? [];
        $reviews = $approvals['supervisor_reviews'] ?? [];
        $reviews[(string) $user->id] = [
            'user_id' => $user->id,
            'supervisor_name' => $user->name,
            'decision' => ($decision === 'approved') ? 'approved' : 'rejected',
            'remarks' => $remarks,
            'reviewed_at' => now()->toDateTimeString(),
            'submission_id' => $submission?->id,
        ];
        $approvals['supervisor_reviews'] = $reviews;
        $milestone->approvals = $approvals;

        // 3. Update status & eligibility based on multi-supervisor policy
        if ($decision === 'approved') {
            $summary = $this->getSupervisorReviewSummary($milestone);
            $milestone->is_supervisor_approved = $summary['is_eligible'];
            if ($summary['is_eligible'] && $milestone->status === 'revision_required') {
                $milestone->status = 'submitted';
            }
        } else {
            // Rejection / Revision Required
            $milestone->is_supervisor_approved = false;
            $milestone->status = 'revision_required';
            $milestone->remark = $remarks;
        }

        $milestone->save();

        // Invalidate student dashboard query cache
        $studentUser = $milestone->thesis?->student?->user;
        if ($studentUser) {
            \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
        }

        return $this->getSupervisorReviewSummary($milestone);
    }

    /**
     * When a student uploads a revised document:
     * - Any supervisor who previously APPROVED keeps their approval ('approved' stands).
     * - Any supervisor who REJECTED now has their status set to 'pending_re_review'.
     * - Candidate is NOT eligible until the rejecting supervisor(s) re-reviews and approves.
     */
    public function handleStudentReUpload(StudentMilestone $milestone): void
    {
        $approvals = $milestone->approvals ?? [];
        $reviews = $approvals['supervisor_reviews'] ?? [];

        $hasChanges = false;
        foreach ($reviews as $userId => $review) {
            // Any supervisor who previously APPROVED remains APPROVED (approval stands!)
            // Any supervisor who previously REJECTED now needs to review the new upload:
            if (($review['decision'] ?? '') === 'rejected') {
                $reviews[$userId]['decision'] = 'pending_re_review';
                $reviews[$userId]['re_review_requested_at'] = now()->toDateTimeString();
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $approvals['supervisor_reviews'] = $reviews;
            $milestone->approvals = $approvals;
        }

        // Re-compute eligibility
        $milestone->is_supervisor_approved = $this->computeSupervisorApprovalEligibility($milestone);
        $milestone->save();
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

        if ($this->isPostDefencePhase($milestone)) {
            return 'presentation session already concluded — awaiting Internal Examiner approval of post-defence corrections';
        }

        if (empty($milestone->defence_date)) {
            return 'not scheduled for presentation';
        }

        if ($slug === 'seminar_as_a_course') {
            if (!$this->hasUploadedPresentation($milestone)) {
                return 'presentation slides (PPT/PDF) not uploaded';
            }
            if (!$this->hasBeenGraded($milestone)) {
                return 'not yet graded by any examiner';
            }
            return null;
        }

        if (in_array($slug, ['proposal_defence', 'progress_report_1', 'progress_report_2', 'internal_defence'])) {
            if (!$this->hasUploadedPresentation($milestone)) {
                return 'required document/presentation not uploaded';
            }
            if (!$this->isSupervisorApproved($milestone)) {
                $summary = $this->getSupervisorReviewSummary($milestone);
                if ($summary['rejected_count'] > 0) {
                    return 'upload has active revision request from supervisor(s)';
                }
                if ($summary['pending_rereview_count'] > 0) {
                    return 'revised upload awaiting re-review by supervisor(s)';
                }
                return 'upload awaiting supervisor approval';
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

        // Viva rules are handled separately.
        return null;
    }

    /**
     * Compute average grading outcome from examiners for presentation defence milestones.
     * Policy: Majority vote decides. Tie = Pass (benefit of doubt).
     * Returns 'pass', 'fail', or null if not yet graded.
     */
    public function getAverageGradingOutcome(StudentMilestone $milestone): ?string
    {
        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
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
                if (in_array($verdict, ['pass', 'minor_revisions', 'major_revisions'])) {
                    $passCount++;
                } elseif ($verdict === 'fail') {
                    $failCount++;
                } elseif (isset($eval->score['total'])) {
                    if ($eval->score['total'] >= 50) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
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
            'internal_defence' => 'internal',
            'viva' => 'external',
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
            'internal_defence' => 'internal',
            'viva' => 'external',
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

        // 4. Assigned Internal Examiner
        if ($milestone->thesis && $milestone->thesis->internalExaminer && $milestone->thesis->internalExaminer->user_id) {
            $recipients->push($milestone->thesis->internalExaminer->user_id);
        }

        // Admin / All unique recipients
        $recipients = $recipients->unique()->filter();

        foreach ($recipients as $userId) {
            \App\Events\MilestoneUpdated::dispatch($milestone, $message, $userId);
        }
    }
}
