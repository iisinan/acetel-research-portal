<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Student;
use App\Models\Milestone;
use App\Models\StudentMilestone;

class StudentDashboard extends Component
{
    use WithFileUploads;

    public $evidence_file;
    public $remarks;

    public function submitMilestone($milestoneId)
    {
        $this->validate([
            'evidence_file' => 'required|file|max:10240', // 10MB
        ]);

        $student = auth()->user()->student;

        $sm = StudentMilestone::updateOrCreate([
            'student_id' => $student->id,
            'milestone_id' => $milestoneId,
        ], [
            'status' => 'Pending',
            'completion_date' => now(),
            'remarks' => $this->remarks
        ]);

        if ($this->evidence_file) {
            $path = $this->evidence_file->store('milestone_evidence', 'public');
            $sm->documents()->create([
                'file_name' => $this->evidence_file->getClientOriginalName(),
                'file_path' => $path,
            ]);
        }

        $this->evidence_file = null;
        $this->remarks = '';
        session()->flash('success', 'Milestone evidence submitted successfully! Awaiting coordinator verification.');
    }

    public function render()
    {
        $student = auth()->user()->student;
        
        $allMilestones = Milestone::orderBy('order_index')->get();
        $studentMilestones = $student ? $student->milestones()->pluck('status', 'milestone_id')->toArray() : [];

        // Determine the next required milestone index
        $nextMilestone = null;
        if ($student && $student->registration_status === 'Active') {
            foreach ($allMilestones as $m) {
                if (!isset($studentMilestones[$m->id]) || $studentMilestones[$m->id] === 'Rejected') {
                    $nextMilestone = $m;
                    break;
                }
            }
        }

        return view('livewire.student-dashboard', [
            'student' => $student,
            'allMilestones' => $allMilestones,
            'studentMilestones' => $studentMilestones,
            'nextMilestone' => $nextMilestone
        ]);
    }
}
