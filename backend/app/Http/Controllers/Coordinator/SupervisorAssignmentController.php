<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\ThesisProject;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\StudentMilestone;
use App\Services\MilestoneWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorAssignmentController extends Controller
{
    protected $workflowService;

    public function __construct(MilestoneWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function store(Request $request, ThesisProject $thesis)
    {
        if ($thesis->student && $thesis->student->isSeminarCourseLevel()) {
            return redirect()->back()->with('error', 'Students at the seminar course level do not require a supervisor.');
        }

        $request->validate([
            'supervisor_ids' => 'required|array',
            'supervisor_ids.*' => 'exists:supervisor_profiles,id',
        ]);

        try {
            DB::beginTransaction();

            $this->workflowService->validateSupervisorAssignment($thesis, $request->supervisor_ids);

            // Deactivate old assignments if any
            $thesis->assignments()->update(['status' => 'ended', 'ended_at' => now()]);

            foreach ($request->supervisor_ids as $index => $id) {
                SupervisionAssignment::create([
                    'thesis_project_id' => $thesis->id,
                    'supervisor_profile_id' => $id,
                    'role' => ($index === 0) ? 'primary' : (($index === 1) ? 'secondary' : 'third'),
                    'order_index' => $index + 1,
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);
            }

            // If the acting user is an Admin, approve Milestone 2 and advance student
            $m2 = $thesis->milestones()->whereHas('template', fn($q) => $q->where('order', 2))->first();
            if ($m2 && auth()->user()->hasRole('Admin')) {
                $m2->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approvals' => ['Admin:' . auth()->id() => ['user_id' => auth()->id(), 'role' => 'Admin', 'approved_at' => now()->toDateTimeString()]]
                ]);
                $this->workflowService->afterApproval($m2);
            }

            $studentUser = $thesis->student->user;
            $studentUserId = $studentUser->id;
            
            $supervisors = \App\Models\SupervisorProfile::with('user', 'department')->whereIn('id', $request->supervisor_ids)->get();
            $coordinatorId = auth()->id();

            // Prepare Supervisor Details String for Student
            $supervisorDetailsStr = "";
            foreach($supervisors as $sup) {
                $supervisorDetailsStr .= "- Name: {$sup->user->name}\n  Email: {$sup->user->email}\n  Department: " . ($sup->department->name ?? 'N/A') . "\n\n";
            }

            // Send message and email to Student
            if ($studentUserId) {
                $studentBody = "Dear {$studentUser->name},\n\nSupervisors have been newly allocated to your academic thesis project. Below are their details:\n\n{$supervisorDetailsStr}\nPlease check the portal for more details.\n\nBest Regards,\nProgram Coordinator";
                
                $studentMsg = \App\Models\InboxMessage::create([
                    'sender_id' => $coordinatorId,
                    'subject' => 'Supervisors Assigned to Your Thesis',
                    'body' => $studentBody,
                ]);
                $studentMsg->recipients()->attach($studentUserId, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to'
                ]);
                \App\Events\MessageReceived::dispatch($studentMsg, $studentUserId);

                // Send Email
                \Illuminate\Support\Facades\Mail::to($studentUser->email)->queue(new \App\Mail\SupervisorAllocated([
                    'subject' => 'Supervisors Assigned to Your Thesis',
                    'greeting' => 'Dear ' . $studentUser->name . ',',
                    'body' => 'Supervisors have been newly allocated to your academic thesis project. Please check the portal to see the names and details of your appointed supervisors.',
                    'user_role' => 'Supervisors',
                    'user_details' => [
                        'name' => "Multiple Supervisors Assigned",
                        'email' => "Check portal for details",
                        'program' => $thesis->student->program->name ?? 'N/A',
                        'thesis_title' => $thesis->title
                    ],
                    'action_text' => 'View Dashboard',
                    'action_url' => route('dashboard')
                ]));
            }

            // Prepare Student Details String for Supervisor
            $studentDetailsStr = "- Name: {$studentUser->name}\n- Email: {$studentUser->email}\n- Program: " . ($thesis->student->program->name ?? 'N/A') . "\n- Thesis Title: {$thesis->title}";

            // Send message and email to each Supervisor
            foreach ($supervisors as $sup) {
                $supUserId = $sup->user_id;
                if ($supUserId) {
                    $supBody = "Dear {$sup->user->name},\n\nYou have been newly allocated to supervise {$studentUser->name} for their thesis. Student Details:\n\n{$studentDetailsStr}\n\nPlease log in to your dashboard to review the student details.\n\nBest Regards,\nProgram Coordinator";
                    
                    $supMsg = \App\Models\InboxMessage::create([
                        'sender_id' => $coordinatorId,
                        'subject' => 'New Thesis Student Allocation',
                        'body' => $supBody,
                    ]);
                    $supMsg->recipients()->attach($supUserId, [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'recipient_type' => 'to'
                    ]);
                    \App\Events\MessageReceived::dispatch($supMsg, $supUserId);

                    // Send Email
                    \Illuminate\Support\Facades\Mail::to($sup->user->email)->queue(new \App\Mail\SupervisorAllocated([
                        'subject' => 'New Thesis Student Allocation',
                        'greeting' => 'Dear ' . $sup->user->name . ',',
                        'body' => 'You have been newly allocated to supervise a student for their thesis project.',
                        'user_role' => 'Student',
                        'user_details' => [
                            'name' => $studentUser->name,
                            'email' => $studentUser->email,
                            'program' => $thesis->student->program->name ?? 'N/A',
                            'thesis_title' => $thesis->title
                        ],
                        'action_text' => 'Review Student',
                        'action_url' => route('dashboard')
                    ]));
                }
            }

            DB::commit();

            return redirect()->back()->with('success', auth()->user()->hasRole('Admin') ? 'Supervisors assigned and milestone approved successfully.' : 'Supervisors assigned successfully. Awaiting Administrator approval to advance milestone.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
