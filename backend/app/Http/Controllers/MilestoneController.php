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
                if ($thesis->student && $user->coordinatorProfiles()->where('program_id', '=', $thesis->student->program_id)->exists()) {
                    $isAuthorized = true;
                }
            }

            if (!$isAuthorized) {
                return redirect()->route('dashboard')->with('error', 'Unauthorized access to this thesis.');
            }
        }

        if ($thesis) {
            $thesis->load(['student.user', 'assignments.supervisor.user', 'internalExaminer.user']);
            
            $milestones = $thesis->milestones()
                ->with(['template', 'submissions.submittedBy', 'messages.sender', 'unlockedBy'])
                ->get()
                ->sortBy('template.order');

            if ($milestones->isEmpty()) {
                $thesis->syncMilestones();
                $milestones = $thesis->milestones()
                    ->with(['template', 'submissions.submittedBy', 'messages.sender', 'unlockedBy'])
                    ->get()
                    ->sortBy('template.order');
            }
            
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

            // All supervisors for assignment (Coordinators/Admins only) - Restricted by Program Scope
            $allSupervisors = collect();
            if ($user->hasAnyRole(['Program Coordinator', 'Admin']) && $thesis->student) {
                $allSupervisors = \App\Models\SupervisorProfile::with('user')
                    ->whereHas('programs', function($q) use ($thesis) {
                        $q->where('programs.id', $thesis->student->program_id);
                    })
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

            return view('milestones.index', compact('milestones', 'supervisors', 'coordinators', 'thesis', 'internalExaminer', 'allSupervisors', 'ongoingMilestoneId'));
        }
        
        return redirect()->route('dashboard');
    }

    /**
     * Institutional Override/Quick Approval for specific clearance tasks.
     */
    public function quickApprove(Request $request, StudentMilestone $milestone)
    {
        $user = Auth::user();
        // Allow staff to override
        if (!$user->hasAnyRole(['Admin', 'Director', 'Program Coordinator', 'Supervisor'])) {
            return response()->json(['success' => false, 'message' => 'Institutional authority required.'], 403);
        }

        $type = $request->type;
        $approvals = $milestone->approvals ?? [];

        if ($type === 'clear_supervisor') {
            $targetUserId = $request->user_id;
            $key = 'Supervisor:' . $targetUserId;
            if (!isset($approvals[$key])) {
                $approvals[$key] = [
                    'user_id' => $targetUserId,
                    'role' => 'Supervisor',
                    'approved_at' => now()->toDateTimeString(),
                    'overridden_by' => $user->id,
                    'overridden_name' => $user->name,
                    'remarks' => 'Institutional Override',
                ];
            }
        } elseif ($type === 'clear_role') {
            $role = $request->role;
            // Key based on role to allow one approval per role
            $key = 'Role:' . $role;
            if (!isset($approvals[$key])) {
                $approvals[$key] = [
                    'user_id' => $user->id,
                    'role' => $role,
                    'approved_at' => now()->toDateTimeString(),
                    'remarks' => 'Institutional Approval',
                ];
            }
        } elseif ($type === 'approve_date') {
            $milestone->date_approved_at = now();
            $milestone->date_approved_by = $user->id;
        } elseif ($type === 'clear_milestone') {
            $milestone->status = 'approved';
            $milestone->approved_at = now();
            $workflowService = app(\App\Services\MilestoneWorkflowService::class);
            $workflowService->afterApproval($milestone);
        }

        $milestone->approvals = $approvals;
        
        // Use Workflow Service to check if total threshold met
        $workflowService = app(\App\Services\MilestoneWorkflowService::class);
        if ($workflowService->isApprovalThresholdMet($milestone)) {
            $milestone->status = 'approved';
            $milestone->approved_at = now();
            $workflowService->afterApproval($milestone);
        } else {
            // Milestone is still active but partially cleared
            if ($milestone->status !== 'approved') {
                $milestone->status = 'partially_approved';
            }
        }
        
        $milestone->save();

        return response()->json([
            'success' => true,
            'message' => 'Clearance granted successfully.',
            'milestone_id' => $milestone->id,
            'is_fully_complete' => $milestone->progress_track['is_fully_complete']
        ]);
    }

    /**
     * Handle the chat-based milestone approval.
     */
    public function approve(Request $request, StudentMilestone $milestone)
    {
        $this->authorize('review', $milestone);
        $user = Auth::user();
        $template = $milestone->template;
        $requiredRoles = $template->required_approvers ?? [];
        
        // Determine which role the user is fulfilling for this specific milestone
        $roleFilled = null;
        if ($user->hasRole('Program Coordinator') && in_array('Program Coordinator', $requiredRoles)) {
            if ($user->coordinatorProfiles()->where('active', true)->where('program_id', $milestone->thesis->student->program_id)->exists()) {
                $roleFilled = 'Program Coordinator';
            }
        } elseif ($user->hasRole('Supervisor') && in_array('Supervisor', $requiredRoles)) {
            if ($user->supervisorProfile && $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->where('status', 'active')->exists()) {
                $roleFilled = 'Supervisor';
            }
        } elseif ($user->hasRole('Internal Examiner') && in_array('Internal Examiner', $requiredRoles)) {
            if ($user->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists()) {
                $roleFilled = 'Internal Examiner';
            }
        } elseif ($user->hasRole('External Examiner') && in_array('External Examiner', $requiredRoles)) {
            if ($user->externalExaminerProfiles()->where('program_id', $milestone->thesis->student->program_id)->exists()) {
                $roleFilled = 'External Examiner';
            }
        } elseif ($user->hasRole('Director') && in_array('Director', $requiredRoles)) {
            $roleFilled = 'Director';
        } elseif ($user->hasRole('Admin')) {
            $roleFilled = !empty($requiredRoles) ? $requiredRoles[0] : 'Admin';
        }

        if (!$roleFilled) {
            return back()->with('error', 'You do not have approval authority for this milestone.');
        }

        // Institutional Sequence Guard: Actions only permitted on Ongoing Milestone
        $ongoing = $milestone->thesis->milestones()->get()->sortBy(fn($m) => $m->template->order ?? 999)->first(fn($m) => $m->status !== 'approved');
        if ($ongoing && $ongoing->id !== $milestone->id && !auth()->user()->hasAnyRole(['Admin', 'Director'])) {
             return back()->with('error', 'Workflow Violation: This milestone is currently locked. Actions must be performed on the ongoing node: ' . $ongoing->template->name);
        }

        // Requirement: Post Submission Approval must be granted before Institutional Clearance.
        if (!(new \App\Services\MilestoneWorkflowService())->canApprove($milestone, $user, $roleFilled)) {
            return back()->with('error', 'Post Submission Approval must be granted before Institutional Clearance can be authorized.');
        }

        // Record the approval
        $approvals = $milestone->approvals ?? [];
        
        // Key by Role + User ID to handle multiple people in same role (e.g. Supervisors)
        $approvalKey = $roleFilled . ':' . $user->id;
        
        $approvals[$approvalKey] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $roleFilled,
            'approved_at' => now()->toDateTimeString(),
        ];
        
        $milestone->approvals = $approvals;

        $workflow = new \App\Services\MilestoneWorkflowService();
        if ($workflow->isApprovalThresholdMet($milestone)) {
            $milestone->status = 'approved';
            $milestone->approved_at = now();
            
            // Trigger after-approval hooks if any
            $workflow->afterApproval($milestone);
        } else {
            $milestone->status = 'partially_approved';
        }

        $milestone->save();

        // Dispatch real-time updates to all parties
        (new \App\Services\MilestoneWorkflowService())->notifyUpdate($milestone, $user->name . " recorded an approval for: " . $milestone->template->name);

        // Log a message in the chat about the approval
        /** @var \App\Models\User $user */
        $user = Auth::user();
        (new \App\Services\MessageService())->sendMessage(
            $milestone->thesis,
            $user,
            "✅ Approved this milestone.",
            $milestone->id,
            ['system' => true, 'action' => 'approval']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Approval recorded successfully.',
            ]);
        }

        return back()->with('success', 'Approval recorded successfully.');
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

        if (in_array('ppt', $subTypes)) {
            $rules['ppt'] = ['required', 'file', 'mimes:pdf', 'max:51200'];
        }

        if (in_array('file', $subTypes)) {
            $rules['file'] = ['required', 'file', 'mimes:pdf', 'max:51200'];
        }

        if (in_array('publication', $subTypes)) {
            $existingCount = $milestone->submissions()->where('type', 'publication')->count();
            $maxAllowed = 5 - $existingCount;
            $rules['publications'] = [($existingCount > 0 ? 'nullable' : 'required'), 'array', 'min:1', "max:{$maxAllowed}"];
            $rules['publications.*'] = 'file|mimes:pdf|max:51200';
        }

        if ($isFinalArchival) {
            $rules['title'] = 'required|string|max:500';
            $rules['abstract'] = 'required|string|max:5000';
            $rules['keywords'] = 'nullable|string|max:500';
        }

        \Illuminate\Support\Facades\Log::info("Request Data: ", $request->all());
        
        try {
            $request->validate($rules);
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
            $pptPath = $pptFile->store('submissions/' . $milestone->thesis_project_id . '/ppt', 'public');

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
                'description' => 'Presentation Slide Deck',
                'version' => $milestone->submissions()->where('type', 'ppt')->count() + 1,
            ]);
        }

        // Handle Manuscript
        if (in_array('file', $subTypes) && $request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('submissions/' . $milestone->thesis_project_id, 'public');

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

        // Handle Publications
        if (in_array('publication', $subTypes) && $request->hasFile('publications')) {
            foreach ($request->file('publications') as $pubFile) {
                $pubPath = $pubFile->store('submissions/' . $milestone->thesis_project_id . '/publications', 'public');
                
                $milestone->submissions()->create([
                    'submitted_by' => Auth::id(),
                    'type' => 'publication',
                    'file_url' => $pubPath,
                    'file_meta' => [
                        'original_name' => $pubFile->getClientOriginalName(),
                        'mime_type' => $pubFile->getMimeType(),
                        'size' => $pubFile->getSize(),
                    ],
                    'checksum' => md5_file($pubFile->getRealPath()),
                    'description' => 'Institutional Publication',
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

        if ($status === 'approved') {
            (new \App\Services\MilestoneWorkflowService())->afterApproval($milestone);
        }

        // Dispatch Real-time events to supervisors and coordinators
        $recipients = collect();
        // Supervisors
        foreach ($milestone->thesis->assignments as $assignment) {
            if ($assignment->supervisor) $recipients->push($assignment->supervisor->user_id);
        }
        // Program Coordinators
        $coords = \App\Models\CoordinatorProfile::where('program_id', $milestone->thesis->student->program_id)->where('active', true)->pluck('user_id');
        $recipients = $recipients->merge($coords)->unique();

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
            'defence_date' => 'required|date'
        ]);

        if (!Auth::user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator'])) {
            \Illuminate\Support\Facades\Log::warning("Unauthorized attempt to set defence date by: " . Auth::user()->name);
            abort(403);
        }

        $milestone->defence_date = $request->defence_date;
        
        // Auto-approve the date if set by an authorized official
        if (Auth::user()->hasAnyRole(['Admin', 'Director', 'Program Coordinator'])) {
            $milestone->date_approved_at = now();
            $milestone->date_approved_by = Auth::id();
        }
        
        $milestone->save();

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
            $path = $request->file('report_file')->store('plagiarism_reports', 'public');
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
        
        $path = $request->file('plagiarism_report')->store('plagiarism_reports/' . $milestone->thesis_project_id, 'public');
        
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
