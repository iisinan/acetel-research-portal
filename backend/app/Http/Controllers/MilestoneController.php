<?php

namespace App\Http\Controllers;

use App\Models\StudentMilestone;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $thesis = null;

        if ($user->hasRole('Student')) {
            $student = $user->studentProfile;
            if (!$student || !$student->thesis) {
                  return redirect()->route('dashboard')->with('error', 'No active thesis found.');
            }
            $thesis = $student->thesis;
        } elseif ($user->hasRole(['Supervisor', 'Program Coordinator', 'Internal Examiner', 'Admin'])) {
            $thesisId = $request->query('thesis_id');
            if (!$thesisId) {
                return redirect()->route('dashboard')->with('error', 'No thesis specified.');
            }
            $thesis = \App\Models\ThesisProject::findOrFail($thesisId);
            
            // Basic security check: Is the user authorized for this thesis?
            // (In a real app, use a Policy, but for now we'll check relations)
            $isAuthorized = false;
            if ($user->hasRole('Admin')) $isAuthorized = true;
            if ($user->hasRole('Supervisor') && $thesis->assignments()->where('supervisor_profile_id', '=', $user->supervisorProfile?->id)->exists()) $isAuthorized = true;
            if ($user->hasRole('Internal Examiner') && $user->internalExaminerProfiles()->where('id', $thesis->internal_examiner_profile_id)->exists()) $isAuthorized = true;
            if ($user->hasRole('Program Coordinator')) {
                if ($thesis->student && ($user->hasCoordinatorAccess($thesis->student) || $user->coordinatorProfiles()->where('program_id', '=', $thesis->student->program_id)->exists())) {
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access to this thesis.');
            }
        }

        if ($thesis) {
            try {
                $thesis->load(['student.user', 'assignments.supervisor.user', 'internalExaminer.user']);
                
                $milestones = $thesis->milestones()
                    ->with(['template', 'submissions.submittedBy', 'submissions.feedback', 'submissions.feedbacks', 'messages.sender', 'unlockedBy'])
                    ->get()
                    ->sortBy('template.order');

                $templatesCount = $thesis->getApplicableMilestoneTemplates()->count();

                if ($milestones->count() < $templatesCount || $milestones->isEmpty()) {
                    $thesis->syncMilestones();
                    $milestones = $thesis->milestones()
                        ->with(['template', 'submissions.submittedBy', 'submissions.feedback', 'submissions.feedbacks', 'messages.sender', 'unlockedBy'])
                        ->get()
                        ->sortBy('template.order');
                }

                // Ensure any exempt milestone is not included in student roadmap
                $milestones = $milestones->reject(function ($m) use ($thesis) {
                    return $thesis->isExemptFromMilestone($m->template);
                })->values();
                
                // Get supervisors
                $supervisors = $thesis->assignments->map(function ($assignment) {
                    return $assignment->supervisor;
                })->filter();

                // Always get program coordinators
                $coordinators = collect();
                if ($thesis->student && $thesis->student->program_id) {
                    $coordinators = \App\Models\CoordinatorProfile::where('program_id', '=', $thesis->student->program_id)
                        ->where('active', '=', true)
                        ->with('user')
                        ->get();
                }

                // Internal Examiner
                $internalExaminer = $thesis->internalExaminer;

                // All supervisors and internal examiners for assignment (Coordinators/Admins only) - Restricted by Program Scope
                $allSupervisors = collect();
                $allInternalExaminers = collect();
                if ($user->hasAnyRole(['Program Coordinator', 'Admin']) && $thesis->student) {
                    $allSupervisors = \App\Models\SupervisorProfile::with('user')
                        ->whereHas('programs', function($q) use ($thesis) {
                            $q->where('programs.id', $thesis->student->program_id);
                        })
                        ->get();

                    $allInternalExaminers = \App\Models\InternalExaminerProfile::with('user')
                        ->where('program_id', $thesis->student->program_id)
                        ->where('active', true)
                        ->get();
                }

                // Identify the "Ongoing" milestone (first one that's not 100% complete)
                $ongoingMilestoneId = null;
                foreach ($milestones as $m) {
                    if (!$m->progress_track['is_fully_complete']) {
                        $ongoingMilestoneId = $m->id;
                        break;
                    }
                }

                return view('milestones.index', compact('milestones', 'supervisors', 'coordinators', 'thesis', 'internalExaminer', 'allSupervisors', 'allInternalExaminers', 'ongoingMilestoneId'));
            } catch (\Throwable $e) {
                \Log::error('Milestones index failed: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
                if ($user->hasRole('Admin')) {
                    return response('<pre style="white-space:pre-wrap;font-size:12px;background:#fef2f2;color:#991b1b;padding:16px;border-radius:12px;">' . e(get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine()) . '</pre>', 500);
                }
                throw $e;
            }
        }
        
        return redirect()->route('dashboard');
    }

    /**
     * Institutional Override/Quick Approval for specific clearance tasks.
     */
    public function acceptUpload(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $isAssignedSupervisor = $user->hasRole('Supervisor') && 
            $user->supervisorProfile && 
            $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->where('status', 'active')->exists();

        $isAssignedInternalExaminer = $user->hasRole('Internal Examiner') && 
            ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6) &&
            $user->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists();

        if (!$user->hasRole('Admin') && !$isAssignedSupervisor && !$isAssignedInternalExaminer) {
            abort(403, 'Unauthorized. Supervisors can only review uploads for their assigned supervisees, and internal examiners review post-defence corrected manuscripts.');
        }

        $request->validate([
            'remarks' => 'required|string|max:2000'
        ], [
            'remarks.required' => 'A comment is required to explain your decision.'
        ]);

        $workflowService = app(\App\Services\MilestoneWorkflowService::class);

        // When Internal Examiner (or Admin) accepts the corrected thesis for internal defence, approve the milestone and advance to Viva Voce!
        if ($isAssignedInternalExaminer || ($user->hasRole('Admin') && ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6))) {
            $latestSub = $milestone->submissions()->latest()->first();
            if ($latestSub) {
                \App\Models\Feedback::updateOrCreate(
                    ['submission_id' => $latestSub->id],
                    [
                        'user_id' => $user->id,
                        'decision' => 'approved',
                        'remarks' => $request->input('remarks'),
                    ]
                );
            }

            $approverRole = $isAssignedInternalExaminer ? 'Internal Examiner' : 'Admin';
            $approvals = $milestone->approvals ?? [];
            $approvalKey = $approverRole . ':' . $user->id;
            $approvals[$approvalKey] = [
                'user_id' => $user->id,
                'role' => $approverRole,
                'approved_at' => now()->toDateTimeString(),
                'remarks' => $request->input('remarks'),
            ];
            $milestone->approvals = $approvals;
            $milestone->status = 'approved';
            $milestone->approved_at = now();
            $milestone->date_approved_at = now();
            $milestone->save();

            // Advance candidate to next milestone (Milestone 7: Viva)
            $workflowService->afterApproval($milestone);

            $workflowService->notifyUpdate($milestone, "{$approverRole} {$user->name} officially approved the corrected thesis for: {$milestone->template->name}. Candidate advanced to Viva Voce.");

            return back()->with('success', 'Corrected thesis approved successfully. Candidate has been officially cleared and advanced to Milestone 7 (Viva Voce).');
        }

        $summary = $workflowService->recordSupervisorReview(
            $milestone,
            $user,
            'approved',
            $request->input('remarks')
        );

        $workflowService->notifyUpdate($milestone, "Supervisor {$user->name} accepted the uploaded document for: {$milestone->template->name}");

        $msg = $summary['is_eligible']
            ? 'Upload accepted successfully. All active reviews are approved — candidate is eligible to present.'
            : 'Upload accepted. Your approval stands. Note: Candidate still has pending or revision requests from co-supervisor(s).';

        return back()->with('success', $msg);
    }

    public function rejectUpload(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $isAssignedSupervisor = $user->hasRole('Supervisor') && 
            $user->supervisorProfile && 
            $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->where('status', 'active')->exists();

        $isAssignedInternalExaminer = $user->hasRole('Internal Examiner') && 
            ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6) &&
            $user->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists();

        if (!$user->hasRole('Admin') && !$isAssignedSupervisor && !$isAssignedInternalExaminer) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'remarks' => 'required|string|max:2000'
        ], [
            'remarks.required' => 'A comment is required to explain your decision.'
        ]);

        $workflowService = app(\App\Services\MilestoneWorkflowService::class);

        if ($isAssignedInternalExaminer) {
            $latestSub = $milestone->submissions()->latest()->first();
            if ($latestSub) {
                \App\Models\Feedback::updateOrCreate(
                    ['submission_id' => $latestSub->id],
                    [
                        'user_id' => $user->id,
                        'decision' => 'revision_required',
                        'remarks' => $request->input('remarks'),
                    ]
                );
            }
            $milestone->update(['status' => 'revision_required']);
            $workflowService->notifyUpdate($milestone, "Internal Examiner {$user->name} requested corrections for: {$milestone->template->name}");

            return back()->with('success', 'Corrections requested. The student has been notified to revise the thesis manuscript.');
        }

        $workflowService->recordSupervisorReview(
            $milestone,
            $user,
            'rejected',
            $request->input('remarks')
        );

        $workflowService->notifyUpdate($milestone, "Supervisor {$user->name} requested revisions for: {$milestone->template->name}");

        return back()->with('success', 'Upload rejected successfully. The student has been notified to revise it and is not eligible to present until revised.');
    }

    public function quickApprove(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();

        // Institutional Rule: ONLY Admin can approve a milestone and advance a student
        if (!$user->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Institutional authority required. Only an Administrator can approve milestones.'], 403);
        }

        $workflowService = app(\App\Services\MilestoneWorkflowService::class);
        $error = $workflowService->getApprovalBlockReason($milestone, $user, 'Admin');
        if ($error) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        $approvals = $milestone->approvals ?? [];
        $approvalKey = 'Admin:' . $user->id;
        $approvals[$approvalKey] = [
            'user_id' => $user->id,
            'role' => 'Admin',
            'approved_at' => now()->toDateTimeString(),
            'remarks' => $request->remarks ?? 'Approved by Administrator via quick approval.',
        ];

        $milestone->approvals = $approvals;
        $milestone->status = 'approved';
        $milestone->approved_at = now();
        $milestone->remark = $request->remarks ?? 'Approved by Administrator via quick approval.';
        $milestone->save();

        // Advance student to next milestone
        $workflowService->afterApproval($milestone);

        // Notify student & dispatch updates
        $workflowService->notifyUpdate($milestone, $user->name . " officially approved: " . $milestone->template->name);
        $milestone->thesis->student->user->notify(new \App\Notifications\MilestoneStatusUpdated($milestone));

        return response()->json([
            'success' => true,
            'message' => 'Milestone approved successfully. Student advanced to the next milestone.',
            'milestone_id' => $milestone->id,
            'is_fully_complete' => true
        ]);
    }

    /**
     * Handle the chat-based milestone approval.
     */
    public function approve(Request $request, StudentMilestone $milestone)
    {
        $this->authorize('review', $milestone);
        $user = Auth::user();

        // Institutional Rule: ONLY Admin can approve milestones
        if (!$user->hasRole('Admin')) {
            return back()->with('error', 'Only an Administrator can approve milestones.');
        }

        $workflow = app(\App\Services\MilestoneWorkflowService::class);
        $error = $workflow->getApprovalBlockReason($milestone, $user, 'Admin');
        if ($error) {
            return back()->with('error', $error);
        }

        // Record the approval
        $approvals = $milestone->approvals ?? [];
        $approvalKey = 'Admin:' . $user->id;
        $approvals[$approvalKey] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => 'Admin',
            'approved_at' => now()->toDateTimeString(),
            'remarks' => $request->remarks ?? 'Approved by Administrator',
        ];
        
        $milestone->approvals = $approvals;
        $milestone->status = 'approved';
        $milestone->approved_at = now();
        $milestone->save();

        // Advance student to next milestone
        $workflow->afterApproval($milestone);

        // Dispatch real-time updates to all parties
        $workflow->notifyUpdate($milestone, $user->name . " officially approved: " . $milestone->template->name);

        // Log a message in the chat about the approval
        (new \App\Services\MessageService())->sendMessage(
            $milestone->thesis,
            $user,
            "✅ Officially approved this milestone.",
            $milestone->id,
            ['system' => true, 'action' => 'approval']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Milestone approved successfully.',
            ]);
        }

        return back()->with('success', 'Milestone approved successfully.');
    }

    /**
     * Conclude presentation session for an individual milestone card.
     * Evaluates whether all presentation requirements are met:
     * - Seminar: scheduled, graded by examiner.
     * - Proposal Defence, Progress Report 1 & 2: scheduled, PPT uploaded, supervisor approved upload, presentation completed.
     */
    public function endPresentation(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        if ($milestone->status === 'approved') {
            return back()->with('info', 'This milestone has already been officially approved.');
        }

        $workflow = app(\App\Services\MilestoneWorkflowService::class);
        $reason = $workflow->getEndSessionBlockReason($milestone);

        $studentName = $milestone->thesis?->student?->user?->name ?? 'Candidate';

        if ($reason) {
            $msg = "Cannot end presentation for {$studentName}: {$reason}.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $gradingOutcome = $workflow->getAverageGradingOutcome($milestone);
        if ($gradingOutcome === 'fail') {
            $workflow->failAndRepeatMilestone($milestone, "Average evaluation grade was FAIL. Candidate must repeat this milestone.");
            $failMsg = "Presentation ended for {$studentName}. Candidate received a FAIL grade from the examination panel and must repeat this milestone.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $failMsg, 'repeated' => true]);
            }
            return back()->with('warning', $failMsg);
        }

        $isInternalDefence = ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6);
        if ($isInternalDefence) {
            // Internal defence: Conclude presentation session and transition candidate to post-defence corrections!
            // Candidate must upload corrected thesis, which must be approved by the Internal Examiner to advance.
            $milestone->update([
                'status' => 'revision_required',
            ]);
            $workflow->notifyUpdate($milestone, "Internal defence presentation session concluded. Candidate must upload corrected thesis incorporating defence comments for Internal Examiner review.");

            $successMsg = "Internal defence presentation session concluded for {$studentName}. Candidate transitioned to Post-Defence Correction stage and notified to upload corrected thesis for Internal Examiner review.";
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $successMsg]);
            }
            return back()->with('success', $successMsg);
        }

        $workflow->approveAndAdvance($milestone, "Presentation session ended and approved by Admin ({$user->name}).");

        $successMsg = "Presentation ended successfully for {$studentName}. Candidate advanced to the next milestone.";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentMilestone $milestone)
    {
        return redirect()->route('milestones.index', [
            'expanded' => $milestone->id,
            'thesis_id' => $milestone->thesis_project_id
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, StudentMilestone $milestone)
    {
        $this->authorize('submit', $milestone);

        // Institutional Sequence Guard: Actions only permitted on Ongoing Milestone
        $ongoing = $milestone->thesis->milestones()->get()->sortBy(fn($m) => $m->template->order ?? 999)->first(fn($m) => $m->status !== 'approved');
        if ($ongoing && $ongoing->id !== $milestone->id && !auth()->user()->hasAnyRole(['Admin', 'Director'])) {
             return back()->with('error', 'Workflow Violation: This milestone is currently locked. Actions must be performed on the ongoing node: ' . $ongoing->template->name);
        }

        $isFinalArchival = $milestone->template->is_final_archival;
        \Illuminate\Support\Facades\Log::info("Submitting Milestone: {$milestone->id}, Order: {$milestone->template->order}, isFinalArchival: " . ($isFinalArchival ? 'YES' : 'NO'));

        $subTypes = $milestone->template->submission_type ?? ['file'];

        $rules = [
            'description' => 'nullable|string|max:1000',
        ];

        $hasPptSub = $milestone->submissions()->where('type', 'ppt')->exists();
        $hasFileSub = $milestone->submissions()->whereIn('type', ['file', 'manuscript'])->exists();

        $isInternalDefence = ($milestone->template?->slug === 'internal_defence' || $milestone->template?->order == 6);
        $defenceDate = $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date) : null;
        $isPostDefenceStage = $isInternalDefence && $defenceDate && ($defenceDate->isPast() || $defenceDate->isToday());

        if (in_array('ppt', $subTypes) && in_array('file', $subTypes)) {
            $requirePpt = !$hasPptSub && !$request->hasFile('file') && !$isPostDefenceStage;
            $requireFile = (!$hasFileSub && !$request->hasFile('ppt')) || $isPostDefenceStage;
            $rules['ppt'] = [($requirePpt ? 'required' : 'nullable'), 'file', 'mimes:pdf', 'max:51200'];
            $rules['file'] = [($requireFile ? 'required' : 'nullable'), 'file', 'mimes:pdf', 'max:51200'];
        } else {
            if (in_array('ppt', $subTypes)) {
                $rules['ppt'] = [($hasPptSub ? 'nullable' : 'required'), 'file', 'mimes:pdf', 'max:51200'];
            }
            if (in_array('file', $subTypes)) {
                $requireFile = !$hasFileSub || $isPostDefenceStage;
                $rules['file'] = [($requireFile ? 'required' : 'nullable'), 'file', 'mimes:pdf', 'max:51200'];
            }
        }

        $student = $milestone->thesis?->student;
        $isPhd = $student ? $student->isPhd() : false;
        $requiredPubsCount = $student ? $student->requiredPublicationsCount() : 1;
        
        $assignedSupervisors = $milestone->thesis?->getSupervisorNames() ?? [];

        // Only verified Scopus-indexed publications co-authored with assigned supervisor count towards institutional milestone requirement
        $existingCompliantPubsCount = $milestone->submissions()
            ->where('type', 'publication')
            ->where('is_scopus_indexed', true)
            ->where(function ($q) use ($assignedSupervisors) {
                if (!empty($assignedSupervisors)) {
                    $q->where('has_supervisor_coauthor', true);
                }
            })
            ->count();

        // Parse structured publications input: publications[i][file], publications[i][title], publications[i][doi]
        $parsedPubs = [];
        $verifiedPubData = [];
        if (in_array('publication', $subTypes) || in_array('publications', $subTypes)) {
            $rawPubs = $request->input('publications', []);
            $rawPubFiles = $request->file('publications', []);

            // Check if uploaded as flat files array: publications[]
            if (is_array($rawPubFiles) && !empty($rawPubFiles) && is_a(reset($rawPubFiles), \Illuminate\Http\UploadedFile::class)) {
                foreach ($rawPubFiles as $f) {
                    $parsedPubs[] = [
                        'file' => $f,
                        'title' => $f->getClientOriginalName(),
                        'doi' => null,
                    ];
                }
            } elseif (is_array($rawPubs) || is_array($rawPubFiles)) {
                $indices = array_unique(array_merge(
                    is_array($rawPubs) ? array_keys($rawPubs) : [],
                    is_array($rawPubFiles) ? array_keys($rawPubFiles) : []
                ));

                foreach ($indices as $idx) {
                    $file = $request->file("publications.{$idx}.file");
                    $title = is_array($rawPubs[$idx] ?? null) ? trim($rawPubs[$idx]['title'] ?? '') : '';
                    $doi = is_array($rawPubs[$idx] ?? null) ? trim($rawPubs[$idx]['doi'] ?? '') : '';

                    if ($file || !empty($title) || !empty($doi)) {
                        $parsedPubs[] = [
                            'index' => $idx,
                            'file' => $file,
                            'title' => $title,
                            'doi' => $doi,
                        ];
                    }
                }
            }

            // Publication upload is optional for internal defence.
            // If the candidate chooses to attach publications, we validate Scopus indexing and supervisor co-authorship.

            $scopusVerifier = app(\App\Services\ScopusVerificationService::class);

            // For each submitted publication, validate file, title, DOI, Scopus indexing, and supervisor co-authorship
            foreach ($parsedPubs as $pIdx => $pubItem) {
                $slotNum = $pIdx + 1;
                if (empty($pubItem['file'])) {
                    return back()->withInput()->withErrors([
                        'publications' => "Please upload the PDF manuscript for Publication #{$slotNum}."
                    ]);
                }
                if (!$pubItem['file']->isValid() || strtolower($pubItem['file']->getClientOriginalExtension()) !== 'pdf') {
                    return back()->withInput()->withErrors([
                        'publications' => "Publication #{$slotNum} file must be a valid PDF document."
                    ]);
                }
                if ($pubItem['file']->getSize() > 51200 * 1024) {
                    return back()->withInput()->withErrors([
                        'publications' => "Publication #{$slotNum} file exceeds the maximum size limit of 50MB."
                    ]);
                }
                if (empty($pubItem['title'])) {
                    return back()->withInput()->withErrors([
                        'publications' => "Please enter the title for Publication #{$slotNum}."
                    ]);
                }
                if (empty($pubItem['doi'])) {
                    return back()->withInput()->withErrors([
                        'publications' => "Please provide the DOI for Publication #{$slotNum} (e.g. 10.1016/j...)."
                    ]);
                }

                // Strict Scopus Indexing and Supervisor Co-authorship Verification
                try {
                    $scopusInfo = $scopusVerifier->verify($pubItem['doi'], $assignedSupervisors);
                } catch (\Throwable $e) {
                    return back()->withInput()->withErrors([
                        'publications' => "Error verifying Publication #{$slotNum} DOI against Scopus: " . $e->getMessage()
                    ]);
                }

                if (empty($scopusInfo['valid'])) {
                    $doiErr = $scopusInfo['error'] ?? 'Invalid or unrecognized DOI.';
                    return back()->withInput()->withErrors([
                        'publications' => "Publication #{$slotNum} DOI verification failed: {$doiErr}"
                    ]);
                }

                // Institutional Gate 1: ONLY Scopus-indexed publications are allowed!
                if (empty($scopusInfo['is_scopus_indexed'])) {
                    return back()->withInput()->withErrors([
                        'publications' => "Institutional Requirement: Every publication for Internal Defence must be indexed in Scopus. Publication #{$slotNum} with DOI '{$pubItem['doi']}' is not indexed in Scopus. You cannot submit this milestone until all required publications are verified as Scopus-indexed."
                    ]);
                }

                // Institutional Gate 2: Publication MUST include assigned supervisor as co-author!
                if (!empty($assignedSupervisors) && empty($scopusInfo['has_supervisor_coauthor'])) {
                    $supNamesStr = implode(', ', $assignedSupervisors);
                    $authNamesStr = !empty($scopusInfo['authors']) ? implode(', ', $scopusInfo['authors']) : 'None found in DOI registry';
                    return back()->withInput()->withErrors([
                        'publications' => "Institutional Requirement: For a publication to be accepted, your assigned supervisor ({$supNamesStr}) must be an author. Publication #{$slotNum} authors ({$authNamesStr}) do not match any of your assigned supervisors. You cannot submit this milestone until your publication is co-authored with your supervisor."
                    ]);
                }

                $verifiedPubData[] = [
                    'file' => $pubItem['file'],
                    'title' => !empty($pubItem['title']) ? $pubItem['title'] : ($scopusInfo['title'] ?? 'Institutional Publication'),
                    'doi' => $scopusInfo['doi'] ?? $pubItem['doi'],
                    'is_scopus_indexed' => true,
                    'has_supervisor_coauthor' => $scopusInfo['has_supervisor_coauthor'] ?? false,
                    'matched_supervisor_name' => $scopusInfo['matched_supervisor_name'] ?? null,
                    'publication_authors' => $scopusInfo['authors'] ?? [],
                    'scopus_data' => $scopusInfo['scopus_data'] ?? null,
                ];
            }
        }

        if ($isFinalArchival) {
            $rules['title'] = 'required|string|max:500';
            $rules['abstract'] = 'required|string|max:5000';
            $rules['keywords'] = 'nullable|string|max:500';
        }

        $messages = [
            'ppt.mimes' => 'The presentation slide deck must be in PDF format. PowerPoint (PPT/PPTX) files are not accepted.',
            'file.mimes' => 'The document must be in PDF format. Word documents (DOC/DOCX) are not accepted.',
        ];

        \Illuminate\Support\Facades\Log::info("Request Data: ", $request->all());
        
        try {
            $request->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error("Validation Failed: ", $e->errors());
            throw $e;
        }

        if ($isFinalArchival) {
            $milestone->thesis->update([
                'title' => $request->title,
                'abstract' => $request->abstract,
                'keywords' => $request->keywords,
            ]);
        }

        // Handle PPT Upload
        if (in_array('ppt', $subTypes) && $request->hasFile('ppt')) {
            $pptFile = $request->file('ppt');
            $pptPath = $pptFile->store('submissions/' . $milestone->thesis_project_id . '/ppt');

            $milestone->submissions()->create([
                'submitted_by' => Auth::id(),
                'type' => 'ppt',
                'file_url' => $pptPath,
                'file_meta' => [
                    'original_name' => $pptFile->getClientOriginalName(),
                    'mime_type' => $pptFile->getMimeType(),
                    'size' => $pptFile->getSize(),
                ],
                'checksum' => md5_file($pptFile->getRealPath()),
                'description' => 'Presentation Slide Deck (PDF)',
                'version' => $milestone->submissions()->where('type', 'ppt')->count() + 1,
            ]);
        }

        // Handle Manuscript
        if (in_array('file', $subTypes) && $request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('submissions/' . $milestone->thesis_project_id);

            $submission = $milestone->submissions()->create([
                'submitted_by' => Auth::id(),
                'type' => 'manuscript',
                'file_url' => $path,
                'file_meta' => [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ],
                'checksum' => md5_file($file->getRealPath()),
                'description' => $request->description,
                'version' => $milestone->submissions()->where('type', 'manuscript')->count() + 1,
            ]);

            // Automate Plagiarism Analysis (Turnitin Integration)
            try {
                $plagiarismData = (new \App\Services\TurnitinService())->checkPlagiarism($path);
                $submission->update(['plagiarism_data' => $plagiarismData]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Plagiarism check failed: ' . $e->getMessage());
            }
        }

        // Handle Publications (Only verified Scopus-indexed & supervisor co-authored publications are stored)
        if ((in_array('publication', $subTypes) || in_array('publications', $subTypes)) && !empty($verifiedPubData)) {
            foreach ($verifiedPubData as $pubItem) {
                /** @var \Illuminate\Http\UploadedFile $pubFile */
                $pubFile = $pubItem['file'];
                $finalTitle = $pubItem['title'];
                $finalDoi = $pubItem['doi'];
                $isScopusIndexed = $pubItem['is_scopus_indexed'];
                $hasSupervisorCoauthor = $pubItem['has_supervisor_coauthor'] ?? false;
                $matchedSupervisorName = $pubItem['matched_supervisor_name'] ?? null;
                $publicationAuthors = $pubItem['publication_authors'] ?? [];
                $scopusData = $pubItem['scopus_data'];

                $pubPath = $pubFile->store('submissions/' . $milestone->thesis_project_id . '/publications');

                $milestone->submissions()->create([
                    'submitted_by' => Auth::id(),
                    'type' => 'publication',
                    'publication_title' => $finalTitle ?: 'Institutional Publication',
                    'doi' => $finalDoi ?: null,
                    'is_scopus_indexed' => $isScopusIndexed,
                    'has_supervisor_coauthor' => $hasSupervisorCoauthor,
                    'matched_supervisor_name' => $matchedSupervisorName,
                    'publication_authors' => $publicationAuthors,
                    'scopus_data' => $scopusData,
                    'file_url' => $pubPath,
                    'file_meta' => [
                        'original_name' => $pubFile->getClientOriginalName(),
                        'mime_type' => $pubFile->getMimeType(),
                        'size' => $pubFile->getSize(),
                    ],
                    'checksum' => md5_file($pubFile->getRealPath()),
                    'description' => 'Scopus-Indexed Publication' . ($matchedSupervisorName ? " (Co-authored with {$matchedSupervisorName})" : ''),
                    'version' => $milestone->submissions()->where('type', 'publication')->count() + 1,
                ]);
            }
        }

        // Determine if auto-approval is needed
        $status = 'submitted';
        $approvedAt = null;
        if (!$milestone->template->requires_approval) {
            $status = 'approved';
            $approvedAt = now();
        }

        // Update milestone status
        $milestone->update([
            'status' => $status,
            'submitted_at' => now(),
            'approved_at' => $approvedAt,
        ]);

        // Multi-supervisor re-upload policy:
        // Standing approvals are preserved; rejecting supervisors are set to pending re-review.
        $workflowService = app(\App\Services\MilestoneWorkflowService::class);
        $workflowService->handleStudentReUpload($milestone);

        if ($status === 'approved') {
            $workflowService->afterApproval($milestone);
        }

        // Check if Supervisors Assigned milestone is now eligible to auto-advance
        (new \App\Services\MilestoneWorkflowService())->tryAutoAdvanceSupervisorsAssigned($milestone->thesis);

        // Dispatch Real-time events to supervisors and coordinators
        $recipients = collect();
        // Supervisors
        foreach ($milestone->thesis->assignments as $assignment) {
            if ($assignment->supervisor) $recipients->push($assignment->supervisor->user_id);
        }
        // Program Coordinators
        $coords = \App\Models\CoordinatorProfile::where('program_id', $milestone->thesis->student->program_id)->where('active', true)->pluck('user_id');
        $recipients = $recipients->merge($coords);

        // Internal Examiner (for internal defence / milestones with assigned examiner)
        if ($milestone->thesis->internalExaminer && $milestone->thesis->internalExaminer->user_id) {
            $recipients->push($milestone->thesis->internalExaminer->user_id);
        }

        $recipients = $recipients->unique();

        foreach ($recipients as $userId) {
            \App\Events\MilestoneSubmitted::dispatch($milestone, $userId);
        }

        // Invalidate student dashboard query cache
        \Illuminate\Support\Facades\Cache::forget('user_thesis_' . Auth::id());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Milestone submitted successfully.',
                'milestone_id' => $milestone->id,
            ]);
        }

        return redirect()->route('milestones.index')
            ->with('success', 'Milestone submitted successfully.');
    }

    /**
     * Unlock the milestone for final submission.
     */
    public function unlock(Request $request, StudentMilestone $milestone)
    {
        $this->authorize('unlock', $milestone);

        // Institutional Sequence Guard: Actions only permitted on Ongoing Milestone
        $ongoing = $milestone->thesis->milestones()->get()->sortBy(fn($m) => $m->template->order ?? 999)->first(fn($m) => $m->status !== 'approved');
        if ($ongoing && $ongoing->id !== $milestone->id && !auth()->user()->hasAnyRole(['Admin', 'Director'])) {
             return back()->with('error', 'Workflow Violation: This milestone is currently locked. Actions must be performed on the ongoing node: ' . $ongoing->template->name);
        }

        $milestone->update([
            'is_submission_unlocked' => true,
            'submission_unlocked_at' => now(),
            'submission_unlocked_by' => Auth::id(),
        ]);

        // Log a system message
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Dispatch real-time update
        (new \App\Services\MilestoneWorkflowService())->notifyUpdate($milestone, $currentUser->name . " unlocked the submission gate for: " . $milestone->template->name);

        (new \App\Services\MessageService())->sendMessage(
            $milestone->thesis,
            $currentUser,
            "🔓 Unlocked the protocol node for final submission.",
            $milestone->id,
            ['system' => true, 'action' => 'unlock']
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Submission gate cleared. Student can now proceed with final upload.');
    }

    public function deleteSubmission(Submission $submission)
    {
        $milestone = $submission->milestone;
        $user = Auth::user();

        // 1. Authorization: Only the owner (Student) can delete their own submission
        if (!$user->hasRole('Student') || $submission->submitted_by !== $user->id) {
            return back()->with('error', 'Unauthorized to delete this artifact.');
        }

        // 2. Restriction: Cannot delete if milestone is already approved
        if ($milestone->status === 'approved') {
            return back()->with('error', 'Cannot delete artifacts after institutional clearance has been granted.');
        }

        // 3. Delete file from storage
        if ($submission->file_url && Storage::disk('public')->exists($submission->file_url)) {
            Storage::disk('public')->delete($submission->file_url);
        }

        // 4. Delete the submission record
        $submission->delete();

        // 5. Update milestone status if no submissions left
        if ($milestone->submissions()->count() === 0) {
            $milestone->update([
                'status' => 'not_started',
                'submitted_at' => null,
            ]);
        } else {
            // Recalculate milestone version or just leave it (the UI handles it via version field)
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Artifact deleted successfully.',
            ]);
        }
        
        return back()->with('success', 'Artifact deleted successfully.');
    }

    public function setDefenceDate(Request $request, StudentMilestone $milestone)
    {
        \Illuminate\Support\Facades\Log::info("Attempting to set defence date for milestone: {$milestone->id} by user: " . Auth::user()->name);

        $request->validate([
            'defence_date' => 'required|date',
            'defence_time' => 'nullable|string|max:20',
            'meeting_link' => 'nullable|url|max:500',
        ]);

        if (!Auth::user()->hasAnyRole(['Admin', 'Director'])) {
            \Illuminate\Support\Facades\Log::warning("Unauthorized attempt to set defence date by: " . Auth::user()->name);
            abort(403, 'Unauthorized: Only administrators can schedule presentations.');
        }

        $milestone->defence_date = $request->defence_date;
        $milestone->defence_time = $request->defence_time;
        $milestone->meeting_link = $request->meeting_link;
        
        // Auto-approve the date if set by an authorized official
        if (Auth::user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator'])) {
            $milestone->date_approved_at = now();
            $milestone->date_approved_by = Auth::id();
        }
        
        $milestone->save();

        $type = $milestone->template?->defence_type ?? match($milestone->template?->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            default => null,
        };

        if ($type && $milestone->thesis_project_id) {
            $start = \Carbon\Carbon::parse($request->defence_date);
            if ($request->filled('defence_time')) {
                $timeParts = explode(':', $request->defence_time);
                if (count($timeParts) >= 2) {
                    $start->setTime((int)$timeParts[0], (int)$timeParts[1]);
                }
            } else {
                $start->setTime(10, 0);
            }

            $eventData = [
                'schedule_start' => $start,
                'schedule_end' => $start->copy()->addHour(),
                'outcome' => 'pending',
            ];
            if ($request->filled('meeting_link')) {
                $eventData['location'] = $request->meeting_link;
            }

            $event = \App\Models\DefenceEvent::updateOrCreate(
                [
                    'thesis_project_id' => $milestone->thesis_project_id,
                    'type' => $type,
                ],
                $eventData
            );

            // If milestone was previously failed (repeated), reset old evaluations and attendance for the fresh attempt
            if (in_array($event->outcome, ['fail', 'failed']) || $milestone->status === 'revision_required') {
                \App\Models\Evaluation::where('defence_event_id', $event->id)->delete();
                \App\Models\PanelMember::where('defence_event_id', $event->id)->update(['is_present' => false]);
                $event->update(['outcome' => 'pending']);
            }

            // Auto-attach template examiners if none exist on this event yet
            $examinersToAutoAttach = [];
            if ($milestone->template?->slug === 'seminar_as_a_course') {
                $examinersToAutoAttach = $milestone->template->metadata['group_examiners'][$milestone->discipline_group]['user_ids'] ?? [];
            } elseif (!empty($milestone->template?->metadata['examiner_user_ids'])) {
                $examinersToAutoAttach = $milestone->template->metadata['examiner_user_ids'];
            }

            if ($event->panelMembers()->where('role', 'examiner')->count() === 0 && !empty($examinersToAutoAttach)) {
                foreach ($examinersToAutoAttach as $uId) {
                    \App\Models\PanelMember::firstOrCreate([
                        'defence_event_id' => $event->id,
                        'user_id' => $uId,
                        'role' => 'examiner',
                    ], [
                        'invitation_status' => 'accepted'
                    ]);
                }
            }
        }

        \Illuminate\Support\Facades\Log::info("Defence date set successfully for milestone: {$milestone->id}");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Defence date has been scheduled and authorized successfully.',
                'milestone_id' => $milestone->id,
                'defence_date' => $milestone->defence_date->format('Y-m-d')
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Defence date updated and stakeholders notified.',
            ]);
        }

        return back()->with('success', 'Defence date updated and stakeholders notified.');
    }

    public function approveDate(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();
        
        // Ensure the milestone actually has a date set
        if (!$milestone->defence_date) {
            return back()->with('error', 'No defence date has been scheduled to approve.');
        }

        // Must be a responsible party (supervisors, coordinators, examiners, etc.)
        if ($user->hasRole('Student')) {
            return back()->with('error', 'Students cannot approve defence dates.');
        }

        $milestone->date_approved_at = now();
        $milestone->date_approved_by = $user->id;
        
        // Also check if now we can fully approve the milestone
        $workflow = new \App\Services\MilestoneWorkflowService();
        if ($workflow->isApprovalThresholdMet($milestone)) {
            $milestone->status = 'approved';
            $milestone->approved_at = now();
            $workflow->afterApproval($milestone);
        }

        $milestone->save();

        (new \App\Services\MessageService())->sendMessage(
            $milestone->thesis,
            $user,
            "✅ Approved the scheduled defence date.",
            $milestone->id,
            ['system' => true, 'action' => 'date_approval']
        );

        return back()->with('success', 'Defence date approved successfully.');
    }

    public function uploadPlagiarism(Request $request, \App\Models\Submission $submission)
    {
        $request->validate([
            'similarity_score' => 'required|numeric|min:0|max:100',
            'report_file' => 'nullable|file|mimes:pdf'
        ]);

        if (!Auth::user()->hasAnyRole(['Admin', 'Program Coordinator'])) {
            abort(403);
        }

        $data = $submission->plagiarism_data ?? [];
        $data['similarity_score'] = $request->similarity_score;
        $data['uploaded_by'] = Auth::user()->name;
        $data['uploaded_at'] = now()->toDateTimeString();
        
        if ($request->hasFile('report_file')) {
            $path = $request->file('report_file')->store('plagiarism_reports');
            $data['report_url'] = $path;
        }

        $submission->plagiarism_data = $data;
        $submission->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plagiarism result uploaded successfully.',
            ]);
        }

        return back()->with('success', 'Plagiarism result uploaded successfully.');
    }

    public function uploadMilestonePlagiarism(Request $request, \App\Models\StudentMilestone $milestone)
    {
        $request->validate([
            'plagiarism_report' => 'required|file|mimes:pdf|max:51200',
            'similarity_score' => 'required|numeric|min:0|max:100'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        // Allow Admin OR the specific role defined in template
        $requiredRole = $milestone->template->plagiarism_report_role ?? 'Admin';
        if (!$user->hasRole('Admin') && !$user->hasRole($requiredRole)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized authority: Only ' . $requiredRole . ' can certify similarity.'], 403);
        }

        // Find the student's latest manuscript submission to link it
        $manuscript = $milestone->submissions()->where('type', 'manuscript')->latest()->first();
        
        $path = $request->file('plagiarism_report')->store('plagiarism_reports/' . $milestone->thesis_project_id);
        
        // 1. Create a dedicated "Plagiarism Report" submission entry (so it shows in the docs list)
        $plagiarismSubmission = $milestone->submissions()->create([
            'submitted_by' => $user->id,
            'type' => 'plagiarism_report',
            'file_url' => $path,
            'file_meta' => [
                'original_name' => $request->file('plagiarism_report')->getClientOriginalName(),
                'mime_type' => $request->file('plagiarism_report')->getMimeType(),
                'size' => $request->file('plagiarism_report')->getSize(),
                'similarity_score' => $request->similarity_score,
                'certifier_role' => $user->getRoleNames()->first(),
                'certifier_name' => $user->name,
            ],
            'description' => 'Institutional Similarity Certification (' . $request->similarity_score . '%)',
            'version' => $milestone->submissions()->where('type', 'plagiarism_report')->count() + 1,
        ]);

        // 2. Also update the manuscript metadata for quick access/tracker checks
        if ($manuscript) {
            $data = $manuscript->plagiarism_data ?? [];
            $data['report_path'] = $path;
            $data['similarity_score'] = $request->similarity_score;
            $data['status'] = 'certified';
            $manuscript->update(['plagiarism_data' => $data]);
        }

        return response()->json([
            'success' => true, 
            'message' => 'Institutional Similarity Protocol Certified. Report has been added to the institutional record.',
            'path' => $path
        ]);
    }

    /**
     * Securely serve a submission artifact.
     */
    public function viewSubmission(\App\Models\Submission $submission)
    {
        $milestone = $submission->milestone;
        
        // Use the same policy we just hardened
        $this->authorize('view', $milestone);

        if (!Storage::disk('public')->exists($submission->file_url)) {
            abort(404, 'Artifact not found in institutional repository.');
        }

        return Storage::disk('public')->response($submission->file_url, $submission->file_meta['original_name'] ?? 'artifact');
    }
}
