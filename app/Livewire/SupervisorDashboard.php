<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\StudentMilestone;

class SupervisorDashboard extends Component
{
    public $commentText = '';
    public $selectedMilestoneId = null;

    public function openCommentModal($studentMilestoneId)
    {
        $this->selectedMilestoneId = $studentMilestoneId;
        $this->commentText = '';
    }

    public function verifyMilestone($studentMilestoneId)
    {
        $sm = StudentMilestone::findOrFail($studentMilestoneId);
        
        // Ensure this supervisor is assigned to the student
        $supervisor = auth()->user()->supervisor;
        if (!$supervisor->students()->where('students.id', $sm->student_id)->exists()) {
            abort(403);
        }

        $sm->update([
            'status' => 'Verified',
            'verified_by' => auth()->id()
        ]);

        session()->flash('success', 'Milestone verified successfully.');
    }

    public function addComment()
    {
        $this->validate([
            'commentText' => 'required|string|max:1000'
        ]);

        $sm = StudentMilestone::findOrFail($this->selectedMilestoneId);
        
        $supervisor = auth()->user()->supervisor;
        if (!$supervisor->students()->where('students.id', $sm->student_id)->exists()) {
            abort(403);
        }

        // Append comment to remarks
        $newRemarks = $sm->remarks ? $sm->remarks . "\n\n" : "";
        $newRemarks .= "[Supervisor " . auth()->user()->name . "]: " . $this->commentText;

        $sm->update(['remarks' => $newRemarks]);

        $this->selectedMilestoneId = null;
        $this->commentText = '';
        session()->flash('success', 'Comment added successfully.');
    }

    public function render()
    {
        $supervisor = auth()->user()->supervisor;
        
        $students = [];
        if ($supervisor) {
            $students = $supervisor->students()->with(['user', 'programme', 'degree', 'milestones.milestone', 'milestones.documents'])->get();
        }

        return view('livewire.supervisor-dashboard', [
            'students' => $students
        ]);
    }
}
