<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\ThesisProject;
use App\Models\ExaminerAssignment;
use App\Models\StudentMilestone;
use App\Models\User;
use App\Services\MilestoneWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExaminerController extends Controller
{
    protected $workflowService;

    public function __construct(MilestoneWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function assignInternal(Request $request, ThesisProject $thesis)
    {
        $request->validate([
            'examiner_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $assignment = ExaminerAssignment::updateOrCreate(
                ['thesis_project_id' => $thesis->id, 'type' => 'internal_examiner'],
                [
                    'examiner_id' => $request->examiner_id,
                    'assigned_by' => auth()->id(),
                    'active' => true
                ]
            );

            // Backwards compatibility with the thesis->internal_examiner_profile_id
            $profile = \App\Models\InternalExaminerProfile::where('user_id', $request->examiner_id)->first();
            if ($profile) {
                $thesis->update(['internal_examiner_profile_id' => $profile->id]);
            }

            // M7 clearance logic
            $m7 = $thesis->milestones()->whereHas('template', fn($q) => $q->where('order', 7))->first();
            if ($m7 && $m7->status !== 'approved') {
                $m7->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approvals' => ['Program Coordinator' => ['user_id' => auth()->id(), 'approved_at' => now()->toDateTimeString()]]
                ]);
                $this->workflowService->afterApproval($m7);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Internal Examiner assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function assignExternal(Request $request, ThesisProject $thesis)
    {
        $request->validate([
            'examiner_id' => 'required|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $assignment = ExaminerAssignment::updateOrCreate(
                ['thesis_project_id' => $thesis->id, 'type' => 'external_examiner'],
                [
                    'examiner_id' => $request->examiner_id,
                    'assigned_by' => auth()->id(),
                    'active' => true
                ]
            );

            // Keep ThesisProject column synced
            $profile = \App\Models\ExternalExaminerProfile::where('user_id', $request->examiner_id)->first();
            if ($profile) {
                $thesis->update(['external_examiner_profile_id' => $profile->id]);
            }

            // Optional: Auto-approve M10 if needed
            $m10 = $thesis->milestones()->whereHas('template', fn($q) => $q->where('order', 10))->first();
            if ($m10 && $m10->status !== 'approved') {
                $m10->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approvals' => ['Program Coordinator' => ['user_id' => auth()->id(), 'approved_at' => now()->toDateTimeString()]]
                ]);
                $this->workflowService->afterApproval($m10);
            }

            DB::commit();
            return redirect()->back()->with('success', 'External Examiner assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
