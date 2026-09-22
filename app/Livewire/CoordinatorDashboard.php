<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\StudentMilestone;
use App\Models\Milestone;

class CoordinatorDashboard extends Component
{
    public $activeTab = 'pending';

    // Supervisor Assignment Modals
    public $assigningStudentId = null;
    public $principal_supervisor_id = '';
    public $co_supervisor_ids = ['', '']; // For up to 2 co-supervisors

    public function approveStudent($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['registration_status' => 'Active']);
        
        // Verify all their self-reported milestones
        $student->milestones()->where('status', 'Pending')->update([
            'status' => 'Verified',
            'verified_by' => auth()->id()
        ]);
        
        session()->flash('success', "Student {$student->matric_no} approved and historical records verified.");
    }

    public function openSupervisorModal($studentId)
    {
        $this->assigningStudentId = $studentId;
        $this->principal_supervisor_id = '';
        $this->co_supervisor_ids = ['', ''];
    }

    public function closeSupervisorModal()
    {
        $this->assigningStudentId = null;
    }

    public function assignSupervisors()
    {
        $student = Student::with('degree')->findOrFail($this->assigningStudentId);
        
        // Validation could be added here based on $student->degree->required_supervisors
        
        $student->supervisors()->detach();
        
        if ($this->principal_supervisor_id) {
            $student->supervisors()->attach($this->principal_supervisor_id, ['role' => 'Principal', 'status' => 'Active']);
        }
        
        foreach ($this->co_supervisor_ids as $coId) {
            if ($coId) {
                $student->supervisors()->attach($coId, ['role' => 'Co-Supervisor', 'status' => 'Active']);
            }
        }

        // Mark Supervisors Assigned milestone as complete
        $milestone = Milestone::where('name', 'Supervisors Assigned')->first();
        StudentMilestone::updateOrCreate([
            'student_id' => $student->id,
            'milestone_id' => $milestone->id,
        ], [
            'status' => 'Verified',
            'completion_date' => now(),
            'verified_by' => auth()->id()
        ]);

        $this->closeSupervisorModal();
        session()->flash('success', "Supervisors assigned to {$student->matric_no}.");
    }

    public function verifyEvidence($milestoneId)
    {
        $sm = StudentMilestone::findOrFail($milestoneId);
        $sm->update([
            'status' => 'Verified',
            'verified_by' => auth()->id()
        ]);
        session()->flash('success', 'Student evidence verified successfully.');
    }

    public function render()
    {
        $reports = [];
        // ... (reports logic remains same)
        if ($this->activeTab === 'reports') {
            $reports['total_students'] = Student::where('registration_status', 'Active')->count();
            $reports['by_programme'] = Student::where('registration_status', 'Active')
                ->join('programmes', 'students.programme_id', '=', 'programmes.id')
                ->selectRaw('programmes.name, count(*) as count')
                ->groupBy('programmes.name')
                ->get();
            $reports['awaiting_supervisors'] = Student::where('registration_status', 'Active')
                ->doesntHave('supervisors')
                ->with('programme', 'user')
                ->get();
            $reports['by_stage'] = StudentMilestone::where('status', 'Pending')
                ->join('milestones', 'student_milestones.milestone_id', '=', 'milestones.id')
                ->selectRaw('milestones.name, count(*) as count')
                ->groupBy('milestones.name')
                ->get();
        }

        $availableSupervisors = collect();
        if ($this->assigningStudentId) {
            $student = Student::find($this->assigningStudentId);
            if ($student) {
                // Fetch all supervisors, not filtered by programme
                $availableSupervisors = Supervisor::with('user')->withCount(['students' => function ($query) {
                    $query->where('student_supervisor.status', 'Active');
                }])->get();
            }
        }

        return view('livewire.coordinator-dashboard', [
            'pendingStudents' => Student::where('registration_status', 'Pending')->with('user', 'programme')->get(),
            'activeStudents' => Student::where('registration_status', 'Active')->with('user', 'programme', 'supervisors')->get(),
            'pendingEvidence' => StudentMilestone::where('status', 'Pending')
                                    ->whereHas('student', function($q) {
                                        $q->where('registration_status', 'Active');
                                    })
                                    ->with(['student.user', 'milestone', 'documents'])
                                    ->get(),
            'supervisors' => $availableSupervisors,
            'reports' => $reports,
        ]);
    }
}
