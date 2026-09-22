<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Programme;
use App\Models\Degree;
use App\Models\Student;

class AdminDashboard extends Component
{
    public $activeTab = 'overview';
    
    // User Creation form variables
    public $newUserName = '';
    public $newUserEmail = '';
    public $newUserPassword = '';
    public $newUserRole = '';

    public function createUser()
    {
        $this->validate([
            'newUserName' => 'required|string|max:255',
            'newUserEmail' => 'required|string|email|max:255|unique:users,email',
            'newUserPassword' => 'required|string|min:8',
            'newUserRole' => 'required|string|exists:roles,name',
        ]);

        $user = clone(new User());
        $user->name = $this->newUserName;
        $user->email = $this->newUserEmail;
        $user->password = \Illuminate\Support\Facades\Hash::make($this->newUserPassword);
        $user->save();

        $user->assignRole($this->newUserRole);

        $this->reset(['newUserName', 'newUserEmail', 'newUserPassword', 'newUserRole']);
        session()->flash('success', 'User successfully created and role assigned!');
    }

    public $newProgrammeName = '';
    public $newDegreeName = '';
    public $newCohortName = '';

    public function createProgramme()
    {
        $this->validate(['newProgrammeName' => 'required|string|max:255|unique:programmes,name']);
        Programme::create(['name' => $this->newProgrammeName]);
        $this->reset('newProgrammeName');
        session()->flash('success', 'Programme successfully added!');
    }

    public function createDegree()
    {
        $this->validate(['newDegreeName' => 'required|string|max:255|unique:degrees,name']);
        Degree::create(['name' => $this->newDegreeName]);
        $this->reset('newDegreeName');
        session()->flash('success', 'Degree successfully added!');
    }

    public function createCohort()
    {
        $this->validate(['newCohortName' => 'required|string|max:255|unique:cohorts,name']);
        \App\Models\Cohort::create(['name' => $this->newCohortName]);
        $this->reset('newCohortName');
        session()->flash('success', 'Cohort successfully added!');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->managingStudentId = null;
    }

    public $filterStage = '';
    public $filterYear = '';
    public $filterBatch = '';
    public $managingStudentId = null;

    public function manageStudent($id)
    {
        $this->managingStudentId = $id;
    }

    public function closeManageStudent()
    {
        $this->managingStudentId = null;
    }

    public function demoteStudent($studentId, $milestoneId)
    {
        \App\Models\StudentMilestone::where('student_id', $studentId)
            ->where('milestone_id', $milestoneId)
            ->delete();
        session()->flash('success', 'Student successfully demoted from that stage.');
    }

    public function advanceStudent($studentId, $milestoneId)
    {
        \App\Models\StudentMilestone::updateOrCreate(
            ['student_id' => $studentId, 'milestone_id' => $milestoneId],
            ['status' => 'Verified', 'completion_date' => now()]
        );
        session()->flash('success', 'Student manually advanced to that stage.');
    }

    public function approveStudent($id)
    {
        $student = Student::find($id);
        if ($student) {
            $student->registration_status = 'Active';
            $student->save();
            session()->flash('success', 'Student registration approved successfully!');
        }
    }

    public function exportStudents()
    {
        $query = Student::with(['user', 'programme', 'degree', 'cohort', 'milestones.milestone']);
        
        if ($this->filterYear) {
            $query->where('admission_year', $this->filterYear);
        }
        
        $students = $query->get();

        if ($this->filterBatch) {
            $students = $students->filter(function($s) {
                preg_match('/^ACE\d{2}(\d)/', strtoupper($s->matric_no), $m);
                return ($m[1] ?? 'Unknown') === $this->filterBatch;
            });
        }
        
        if ($this->filterStage) {
            $students = $students->filter(function($s) {
                $lastM = $s->milestones->sortByDesc('milestone.order_index')->first();
                $stage = $lastM ? $lastM->milestone->name : 'Registered';
                return $stage === $this->filterStage;
            });
        }

        $csvData = "Name,Matric No,Programme,Degree,Intake Year,Batch,Current Stage,Status\n";
        foreach ($students as $stu) {
            preg_match('/^ACE\d{2}(\d)/', strtoupper($stu->matric_no), $m);
            $batch = $m[1] ?? 'Unknown';
            $lastM = $stu->milestones->sortByDesc('milestone.order_index')->first();
            $stage = $lastM ? $lastM->milestone->name : 'Registered';
            
            $csvData .= '"'.$stu->user->name.'","'.$stu->matric_no.'","'.($stu->programme->name ?? 'N/A').'","'.($stu->degree->name ?? 'N/A').'","'.$stu->admission_year.'","Batch '.$batch.'","'.$stage.'","'.$stu->registration_status.'"' . "\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'students_export.csv');
    }

    public function render()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'active_students' => Student::where('registration_status', 'Active')->count(),
            'total_programmes' => Programme::count(),
        ];

        $users = [];
        $programmes = [];
        $degrees = [];
        $cohorts = [];
        $availableRoles = [];
        $studentsList = [];
        $managedStudent = null;
        $allMilestones = [];

        if ($this->activeTab === 'users') {
            $users = User::with('roles')->orderBy('created_at', 'desc')->get();
            $availableRoles = \Spatie\Permission\Models\Role::all();
        } elseif ($this->activeTab === 'programmes') {
            $programmes = Programme::withCount('students')->get();
            $degrees = Degree::withCount('students')->get();
            $cohorts = \App\Models\Cohort::withCount('students')->get();
        } elseif ($this->activeTab === 'students') {
            if ($this->managingStudentId) {
                $managedStudent = Student::with(['user', 'programme', 'degree', 'cohort', 'milestones.milestone'])->find($this->managingStudentId);
                $allMilestones = \App\Models\Milestone::orderBy('order_index')->get();
            } else {
                $query = Student::with(['user', 'programme', 'degree', 'cohort', 'milestones.milestone'])->orderBy('created_at', 'desc');
                
                if ($this->filterYear) {
                    $query->where('admission_year', $this->filterYear);
                }
                
                $rawStudents = $query->get();

                if ($this->filterBatch) {
                    $rawStudents = $rawStudents->filter(function($s) {
                        preg_match('/^ACE\d{2}(\d)/', strtoupper($s->matric_no), $m);
                        return ($m[1] ?? 'Unknown') === $this->filterBatch;
                    });
                }
                
                if ($this->filterStage) {
                    $rawStudents = $rawStudents->filter(function($s) {
                        $lastM = $s->milestones->sortByDesc('milestone.order_index')->first();
                        $stage = $lastM ? $lastM->milestone->name : 'Registered';
                        return $stage === $this->filterStage;
                    });
                }

                $studentsList = $rawStudents;
            }
        }

        return view('livewire.admin-dashboard', compact('stats', 'users', 'programmes', 'degrees', 'cohorts', 'availableRoles', 'studentsList', 'managedStudent', 'allMilestones'));
    }
}
