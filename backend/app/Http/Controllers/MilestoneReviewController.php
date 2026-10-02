<?php

namespace App\Http\Controllers;

use App\Models\StudentMilestone;
use App\Models\Feedback;
use App\Models\DefenceEvent;
use App\Models\PanelMember;
use App\Models\InternalExaminerProfile;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\User;
use App\Models\ThesisProject;
use App\Notifications\EventScheduled;
use App\Services\MilestoneWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneReviewController extends Controller
{
    protected $workflowService;

    public function __construct(MilestoneWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function show(StudentMilestone $milestone)
    {
        $this->authorize('view', $milestone);

        $milestone->load(['template', 'submissions.submittedBy', 'thesis.student.user', 'thesis.student.program', 'thesis.student.level']);
        $submission = $milestone->submissions()->latest()->first();

        if (!$this->workflowService->canApprove($milestone, Auth::user())) {
            // Optional: flash message that previous approvals are missing
        }
        
        $internalExaminers = [];
        $externalExaminers = [];
        $availableSupervisors = [];
        
        if (Auth::user()->hasRole('Program Coordinator')) {
            $programId = $milestone->thesis->student->program_id;
            
            if ($milestone->template->order == 4 || $milestone->template->order == 6 || $milestone->template->show_internal_examiner_assignment) {
                $internalExaminers = InternalExaminerProfile::with('user')
                    ->where('program_id', $programId)
                    ->where('active', true)
                    ->get();
            }

            if ($milestone->template->show_external_examiner_assignment) {
                $externalExaminers = \App\Models\ExternalExaminerProfile::with('user')->get();
            }

            if ($milestone->template->order == 2) {
                $availableSupervisors = SupervisorProfile::with('user')
                    ->where('program_id', $programId)
                    ->where('active', true)
                    ->get();
            }
        }
        
        $pendingActionItems = \App\Models\ActionItem::where('thesis_project_id', $milestone->thesis_project_id)
            ->where('status', '!=', 'verified')
            ->get();
        
        return view('milestones.review', compact('milestone', 'submission', 'internalExaminers', 'externalExaminers', 'availableSupervisors', 'pendingActionItems'));
    }

    public function update(Request $request, StudentMilestone $milestone)
    {
        $this->authorize('review', $milestone);


        $rules = [
            'decision' => 'required|in:approved,rejected',
            'remarks' => 'required|string|max:5000',
        ];

        // Conditional validation based on milestone order and coordinator role
        // Conditional validation based on template properties
        if ($milestone->template->allow_defence_date) {
            if (Auth::user()->hasRole('Program Coordinator')) {
                $rules['defence_date'] = 'nullable|date';
                $rules['defence_location'] = 'nullable|string|max:255';
            }
        }

        if ($milestone->template->show_internal_examiner_assignment) {
            if (Auth::user()->hasRole('Program Coordinator')) {
                $rules['internal_examiner_profile_id'] = 'required|exists:internal_examiner_profiles,id';
            }
        }

        if ($milestone->template->show_external_examiner_assignment) {
            if (Auth::user()->hasRole('Program Coordinator')) {
                $rules['external_examiner_profile_id'] = 'required|exists:external_examiner_profiles,id';
            }
        }

        if ($milestone->template->order == 3) {
            $rules['communication_log'] = 'nullable|array';
        }

        $request->validate($rules);

        $user = Auth::user();
        $decision = $request->decision;
        $template = $milestone->template;
        $requiredRoles = $template->required_approvers ?? ['Program Coordinator'];
        
        if ($decision === 'approved') {
            $approvals = $milestone->approvals ?? [];
            
            // Determine which role the user is fulfilling
            $roleFilled = null;
            foreach ($requiredRoles as $role) {
                if ($user->hasRole($role)) {
                    // Check if this user has already approved in this specific role
                    $existingKey = $role . ':' . $user->id;
                    if (isset($approvals[$existingKey])) {
                        continue;
                    }

                    // Extra check for specific authorities
                    if ($role === 'Program Coordinator') {
                        if ($user->coordinatorProfiles()->where('active', true)->where('program_id', $milestone->thesis->student->program_id)->exists()) {
                            $roleFilled = $role;
                            break;
                        }
                    } elseif ($role === 'Supervisor') {
                        if ($user->supervisorProfile && $milestone->thesis->assignments()->where('supervisor_profile_id', $user->supervisorProfile->id)->where('status', 'active')->exists()) {
                            $roleFilled = $role;
                            break;
                        }
                    } elseif ($role === 'Internal Examiner') {
                        if ($user->internalExaminerProfiles()->where('id', $milestone->thesis->internal_examiner_profile_id)->exists()) {
                            $roleFilled = $role;
                            break;
                        }
                    } elseif ($role === 'Admin') {
                        $roleFilled = $role;
                        break;
                    }
                }
            }

            if ($roleFilled) {
                // Requirement: Post Submission Approval must be granted before Institutional Clearance.
                if (!$this->workflowService->canApprove($milestone, Auth::user(), $roleFilled)) {
                    return redirect()->route('dashboard')
                        ->with('error', 'Institutional Clearance cannot be granted until Post Submission Approval has been finalized.');
                }

                $approvalKey = $roleFilled . ':' . $user->id;
                $approvals[$approvalKey] = [
                    'user_id' => $user->id,
                    'role' => $roleFilled,
                    'approved_at' => now()->toDateTimeString(),
                    'remarks' => $request->remarks,
                ];
                
                $milestone->approvals = $approvals;

                // Handle PC specific fields for defence/communication
                if ($roleFilled === 'Program Coordinator') {
                    if ($template->order == 2) {
                        $request->validate([
                            'supervisor_ids' => 'required|array',
                            'supervisor_ids.*' => 'exists:supervisor_profiles,id',
                        ]);

                        $this->workflowService->validateSupervisorAssignment($milestone->thesis, $request->supervisor_ids);

                        // Deactivate old assignments if any
                        $milestone->thesis->assignments()->update(['status' => 'ended', 'ended_at' => now()]);

                        foreach ($request->supervisor_ids as $index => $id) {
                            $assignment = SupervisionAssignment::create([
                                'thesis_project_id' => $milestone->thesis_project_id,
                                'supervisor_profile_id' => $id,
                                'role' => ($index === 0) ? 'primary' : (($index === 1) ? 'secondary' : 'third'),
                                'order_index' => $index + 1,
                                'status' => 'active',
                                'assigned_at' => now(),
                            ]);

                            // Notify Supervisor
                            $assignment->supervisorProfile->user->notify(new \App\Notifications\SupervisorRoleAssigned($assignment));
                        }
                    }
                    if ($template->allow_defence_date) {
                        $milestone->defence_date = $request->defence_date;
                        $milestone->defence_location = $request->defence_location;
                        
                        if ($request->defence_date) {
                            $milestone->date_approved_at = now();
                        }
                        
                        // Create official DefenceEvent if date is provided
                        if ($request->defence_date) {
                            $typeMap = [
                                'proposal' => 'first_seminar',
                                'internal' => 'internal_defence',
                                'external' => 'external_defence'
                            ];
                            $type = $typeMap[$template->defence_type ?? 'proposal'] ?? 'first_seminar';

                            $event = DefenceEvent::updateOrCreate(
                                ['thesis_project_id' => $milestone->thesis_project_id, 'type' => $type],
                                [
                                    'schedule_start' => $request->defence_date . ' 10:00:00', // Default time
                                    'location' => $request->defence_location ?? 'TBD',
                                ]
                            );
                        }
                    }

                    if ($template->show_internal_examiner_assignment) {
                        // Update Thesis Project Internal Examiner
                        $milestone->thesis->update([
                            'internal_examiner_profile_id' => $request->internal_examiner_profile_id
                        ]);

                        // Automatically add Internal Examiner as Panel Member to events if they exist
                        if ($request->internal_examiner_profile_id) {
                            $examinerProfile = InternalExaminerProfile::find($request->internal_examiner_profile_id);
                            $activeEvent = DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)->latest()->first();
                            
                            if ($examinerProfile && $activeEvent) {
                                PanelMember::updateOrCreate(
                                    ['defence_event_id' => $activeEvent->id, 'user_id' => $examinerProfile->user_id],
                                    ['role' => 'internal_examiner', 'invitation_status' => 'accepted']
                                );
                            }
                        }
                    }

                    if ($template->show_external_examiner_assignment) {
                        // Update Thesis Project External Examiner
                        $milestone->thesis->update([
                            'external_examiner_profile_id' => $request->external_examiner_profile_id
                        ]);

                        // Automatically add External Examiner as Panel Member to events if they exist
                        if ($request->external_examiner_profile_id) {
                            $examinerProfile = \App\Models\ExternalExaminerProfile::find($request->external_examiner_profile_id);
                            $activeEvent = DefenceEvent::where('thesis_project_id', $milestone->thesis_project_id)->latest()->first();
                            
                            if ($examinerProfile && $activeEvent) {
                                PanelMember::updateOrCreate(
                                    ['defence_event_id' => $activeEvent->id, 'user_id' => $examinerProfile->user_id],
                                    ['role' => 'external_examiner', 'invitation_status' => 'accepted']
                                );
                            }
                        }
                    }

                    if ($template->order == 3) {
                        $milestone->communication_log = $request->communication_log;
                    }
                }
                
                // Use the workflow service to check if the milestone is now fully approved
                if ($this->workflowService->isApprovalThresholdMet($milestone)) {
                    $milestone->status = 'approved';
                    $milestone->approved_at = now();
                    $this->workflowService->afterApproval($milestone);
                } else {
                    $milestone->status = 'partially_approved'; 
                }
                
                $milestone->remark = $request->remarks;
                $milestone->save();

                // Dispatch real-time update
                $this->workflowService->notifyUpdate($milestone, $user->name . " recorded an approval for: " . $milestone->template->name);

                // Trigger Notification
                $milestone->thesis->student->user->notify(new \App\Notifications\MilestoneStatusUpdated($milestone));
            }
        } else {
            // Rejected / Revision Required
            $milestone->update([
                'status' => 'revision_required',
                'approvals' => null, // Reset partial approvals on rejection
                'remark' => $request->remarks,
            ]);

            // Dispatch real-time update
            $this->workflowService->notifyUpdate($milestone, $user->name . " requested revisions for: " . $milestone->template->name);
        }

        // Record official feedback linked to the latest submission if exists
        $submission = $milestone->submissions()->latest()->first();
        if ($submission) {
             $feedback = Feedback::create([
                'submission_id' => $submission->id,
                'decision' => $decision,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Create Action Items if provided
            if ($request->has('action_items') && is_array($request->action_items)) {
                foreach ($request->action_items as $item) {
                    if (!empty($item['content'])) {
                        \App\Models\ActionItem::create([
                            'feedback_id' => $feedback->id,
                            'thesis_project_id' => $milestone->thesis_project_id,
                            'assigned_to' => $milestone->thesis->student->user_id,
                            'content' => $item['content'],
                            'due_date' => $item['due_date'] ?? null,
                            'status' => 'pending'
                        ]);
                    }
                }
            } elseif ($decision === 'rejected' && !empty($request->remarks)) {
                \App\Models\ActionItem::create([
                    'feedback_id' => $feedback->id,
                    'thesis_project_id' => $milestone->thesis_project_id,
                    'assigned_to' => $milestone->thesis->student->user_id,
                    'content' => $request->remarks,
                    'status' => 'pending'
                ]);
            }
        }

        // Invalidate student dashboard query cache
        $studentUser = $milestone->thesis->student?->user;
        if ($studentUser) {
            \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Institutional evaluation submitted successfully.',
                'status' => $milestone->status,
                'status_label' => ucfirst(str_replace('_', ' ', $milestone->status)),
                'milestone_id' => $milestone->id
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Milestone review submitted successfully.');
    }
}
