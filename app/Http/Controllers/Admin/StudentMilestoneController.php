<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentMilestone;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class StudentMilestoneController extends Controller
{
    public function edit(StudentProfile $student, StudentMilestone $milestone)
    {
        $milestone->load('template');
        return view('admin.student-milestones.edit', compact('student', 'milestone'));
    }

    public function update(Request $request, StudentProfile $student, StudentMilestone $milestone)
    {
        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,submitted,approved,rejected',
            'due_date' => 'nullable|date',
            'remark' => 'nullable|string',
        ]);

        // Admin manual override logic
        if ($validated['status'] === 'approved' && $milestone->status !== 'approved') {
            $milestone->approved_at = now();
            
            $approvals = $milestone->approvals ?? [];
            $approvals['Admin'] = [
                'name' => auth()->user()->name,
                'at' => now()->toDateTimeString(),
                'note' => 'Manual Admin Override'
            ];
            $milestone->approvals = $approvals;
        }

        $milestone->fill($validated);
        $milestone->save();

        $workflowService = app(\App\Services\MilestoneWorkflowService::class);

        // Notify all relevant parties of the manual change
        $workflowService->notifyUpdate($milestone, "Admistrator updated the milestone: " . $milestone->template->name . " to status: " . $milestone->status);

        // Trigger any workflow effects (like activating channels) if it became approved
        if ($milestone->wasChanged('status') && $milestone->status === 'approved') {
             $workflowService->afterApproval($milestone);
        }

        return redirect()->route('admin.students.show', $student->id)->with('success', 'Milestone updated successfully.');
    }

    public function sync(StudentProfile $student)
    {
        if ($student->thesis) {
            $student->thesis->syncMilestones();
            return redirect()->back()->with('success', 'Milestones synchronized with current templates.');
        }
        
        return redirect()->back()->with('error', 'Student has no active thesis project.');
    }
}
