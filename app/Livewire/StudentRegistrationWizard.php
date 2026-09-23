<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\Student;
use App\Models\Programme;
use App\Models\Degree;
use App\Models\Intake;
use App\Models\Cohort;
use App\Models\Milestone;
use App\Models\StudentMilestone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentRegistrationWizard extends Component
{
    use WithFileUploads;

    public $step = 1;

    // Step 1: Basic Info
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $matric_no;
    public $place_of_work;
    public $programme_id = '';
    public $degree_id = '';

    // Parsed Data
    public $parsed_year;
    public $parsed_intake_id;
    public $parsed_cohort_id;

    // Proposal details
    public $thesis_title;
    public $thesis_abstract;

    // Presentation Date intercept
    public $milestone_dates = [];
    public $current_intercept_milestone;
    public $presentation_date;

    // Step 2: Questionnaire
    public $questions = [];
    public $currentQuestionIndex = 0;
    
    // Uploads
    public $thesis_file;

    // Step 8: Seminar Grade
    public $seminar_grade;

    // Step 4: Supervisors
    public $supervisors_data = [];
    public $available_supervisors = [];

    // Step 7: Internal Defence
    public $internal_examiner_id;
    public $internal_examiner_name;
    public $internal_examiner_email;
    public $external_examiner_id;
    public $external_examiner_name;
    public $external_examiner_email;
    public $publications_data = [];
    public $internal_thesis_file;
    public $available_examiners = [];

    public function mount()
    {
        // Load milestones except Registered (1) and Completed (9)
        $this->questions = Milestone::whereNotIn('order_index', [1, 9])->orderBy('order_index')->get()->toArray();
    }

    public function validateBasicInfo()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'matric_no' => ['required', 'string', 'unique:students,matric_no', 'regex:/^ACE(\d{2})([12])\d+$/i'],
            'place_of_work' => 'nullable|string|max:255',
            'programme_id' => 'required|exists:programmes,id',
            'degree_id' => 'required|exists:degrees,id',
        ], [
            'matric_no.regex' => 'Matric number format is invalid. Example: ACE26210011',
            'matric_no.unique' => 'This matric number is already registered.',
        ]);

        // Parse Matric Number e.g. ACE26210011
        preg_match('/^ACE(\d{2})([12])\d+$/i', $this->matric_no, $matches);
        $this->parsed_year = '20' . $matches[1];
        
        $intakeName = $matches[2] == '1' ? 'First Intake' : 'Second Intake';
        $intake = Intake::where('name', $intakeName)->first();
        $this->parsed_intake_id = $intake->id;

        // Find or create cohort
        $cohort = Cohort::firstOrCreate([
            'year' => $this->parsed_year,
            'intake_id' => $this->parsed_intake_id
        ]);
        $this->parsed_cohort_id = $cohort->id;

        $this->step = 2;
    }

    // Step 5: Proposal Details

    public function answerQuestion($answer)
    {
        $currentQuestion = $this->questions[$this->currentQuestionIndex];
        
        if ($answer === 'yes') {
            // Mark as completed in session array
            $this->questions[$this->currentQuestionIndex]['completed'] = true;
            
            // If it's Viva, prompt for thesis upload and external examiner
            if ($currentQuestion['name'] === 'Viva') {
                $this->available_examiners = \App\Models\User::role(['Supervisor', 'Coordinator', 'Examiner'])->get();
                $this->step = 3; // Upload Thesis step
                return;
            }

            // If it's Supervisors Assigned, prompt for supervisors
            if ($currentQuestion['name'] === 'Supervisors Assigned') {
                $degree = \App\Models\Degree::find($this->degree_id);
                $required = $degree ? $degree->required_supervisors : 2;
                
                if (count($this->supervisors_data) !== $required) {
                    $this->supervisors_data = array_fill(0, $required, ['supervisor_id' => '', 'name' => '', 'email' => '']);
                }
                
                // Load all available supervisors, NOT filtered by programme
                $this->available_supervisors = \App\Models\Supervisor::with('user')->get();

                $this->step = 4; // Supervisors step
                return;
            }

            // If it's Proposal Defence, prompt for Thesis Title & Abstract
            if ($currentQuestion['name'] === 'Proposal Defence') {
                $this->step = 5; // Proposal details step
                return;
            }

            // If it's Seminar Course, prompt for grade
            if ($currentQuestion['name'] === 'Seminar Course') {
                $this->step = 8;
                return;
            }

            // Intercept for Presentation Dates
            if (in_array($currentQuestion['name'], ['Progress Presentation 1', 'Progress Presentation 2'])) {
                $this->current_intercept_milestone = $currentQuestion['name'];
                $this->presentation_date = null;
                $this->step = 6;
                return;
            }

            // Intercept for Internal Defence
            if ($currentQuestion['name'] === 'Internal Defence') {
                $this->current_intercept_milestone = 'Internal Defence';
                $this->presentation_date = null;
                $this->internal_examiner_id = null;
                $this->internal_examiner_name = '';
                $this->internal_examiner_email = '';
                $this->publications_data = [['title' => '', 'file' => null]];
                $this->internal_thesis_file = null;
                $this->available_examiners = \App\Models\User::role(['Supervisor', 'Coordinator', 'Examiner'])->get();
                $this->step = 7;
                return;
            }

        } else {
            // "No" means stop questionnaire here (this is their current stage)
            $this->questions[$this->currentQuestionIndex]['completed'] = false;
        }

        $this->currentQuestionIndex++;
        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function submitProposalDetails()
    {
        $this->validate([
            'thesis_title' => 'required|string|max:255',
            'thesis_abstract' => 'required|string|max:4000',
        ]);

        $this->step = 2; // Back to questionnaire
        $this->currentQuestionIndex++;
        
        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function submitPresentationDate()
    {
        $this->validate([
            'presentation_date' => 'required|date|before_or_equal:today',
        ], [
            'presentation_date.before_or_equal' => 'The presentation date cannot be in the future.',
            'presentation_date.required' => 'Please provide the date of your presentation.',
        ]);

        $this->milestone_dates[$this->current_intercept_milestone] = $this->presentation_date;

        $this->step = 2; // Back to questionnaire
        $this->currentQuestionIndex++;
        
        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function submitSeminarGrade()
    {
        $this->validate([
            'seminar_grade' => 'required|string|max:50',
        ], [
            'seminar_grade.required' => 'Please provide your seminar course grade.',
        ]);

        $this->step = 2; // Back to questionnaire
        $this->currentQuestionIndex++;
        
        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function goBackToStep2FromSeminar()
    {
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
        $this->step = 2;
    }

    public function goBackToStep2FromPresentation()
    {
        $this->step = 2;
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
    }

    public function submitInternalDefence()
    {
        $this->validate([
            'presentation_date' => 'required|date|before_or_equal:today',
            'internal_examiner_id' => 'required',
            'internal_examiner_name' => 'nullable|required_if:internal_examiner_id,other|max:255',
            'internal_examiner_email' => 'nullable|required_if:internal_examiner_id,other|email',
            'publications_data.*.title' => 'required|string|max:255',
            'publications_data.*.file' => 'required|file|mimes:pdf|max:5120',
            'internal_thesis_file' => 'required|file|mimes:pdf|max:10240',
        ], [
            'internal_examiner_id.required' => 'Please select an internal examiner.',
            'internal_thesis_file.required' => 'Please upload a copy of your thesis.',
            'publications_data.*.title.required' => 'Publication title/DOI is required.',
            'publications_data.*.file.required' => 'Publication file is required.',
            'publications_data.*.file.mimes' => 'Publication must be a PDF.',
        ]);

        $this->milestone_dates['Internal Defence'] = $this->presentation_date;

        $this->step = 2; // Back to questionnaire
        $this->currentQuestionIndex++;
        
        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function addPublication()
    {
        $this->publications_data[] = ['title' => '', 'file' => null];
    }

    public function removePublication($index)
    {
        unset($this->publications_data[$index]);
        $this->publications_data = array_values($this->publications_data);
    }

    public function goBackToStep2FromInternalDefence()
    {
        $this->step = 2;
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
    }

    public function submitSupervisors()
    {
        $this->validate([
            'supervisors_data.*.supervisor_id' => 'required',
            'supervisors_data.*.name' => 'required_if:supervisors_data.*.supervisor_id,other|max:255',
            'supervisors_data.*.email' => 'required_if:supervisors_data.*.supervisor_id,other|email',
        ], [
            'supervisors_data.*.supervisor_id.required' => 'Please select a supervisor.',
            'supervisors_data.*.name.required_if' => 'Please enter the supervisor\'s name.',
            'supervisors_data.*.email.required_if' => 'Please enter the supervisor\'s email.',
        ]);

        // Distinct check for non-other supervisors
        $selectedIds = [];
        foreach ($this->supervisors_data as $index => $sup) {
            if ($sup['supervisor_id'] !== 'other') {
                if (in_array($sup['supervisor_id'], $selectedIds)) {
                    $this->addError("supervisors_data.$index.supervisor_id", 'You cannot select the same supervisor twice.');
                    return;
                }
                $selectedIds[] = $sup['supervisor_id'];
            }
        }

        $this->step = 2; // Back to questionnaire
        $this->currentQuestionIndex++;

        if ($this->currentQuestionIndex >= count($this->questions)) {
            $this->submitRegistration();
        }
    }

    public function goBackToStep2FromSupervisors()
    {
        $this->step = 2;
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
    }

    public function goBackToStep2FromProposal()
    {
        $this->step = 2;
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
    }

    public function goBackToStep1()
    {
        $this->step = 1;
    }

    public function goBackQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->questions[$this->currentQuestionIndex]['completed'] = false;
        } else {
            // If at first question, go back to step 1
            $this->step = 1;
        }
    }

    public function goBackToStep2()
    {
        $this->step = 2;
        // The last question is Viva
        $this->currentQuestionIndex = count($this->questions) - 1;
        $this->questions[$this->currentQuestionIndex]['completed'] = false;
    }

    public function submitThesisAndFinish()
    {
        $this->validate([
            'external_examiner_id' => 'required',
            'external_examiner_name' => 'nullable|required_if:external_examiner_id,other|max:255',
            'external_examiner_email' => 'nullable|required_if:external_examiner_id,other|email',
            'thesis_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ], [
            'external_examiner_id.required' => 'Please select an external examiner.',
        ]);

        $this->submitRegistration(true);
    }

    private function submitRegistration($hasThesis = false)
    {
        DB::transaction(function () use ($hasThesis) {
            // 1. Create User
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole('Student');

            // Resolve Internal Examiner
            $resolvedInternalExaminerId = null;
            if ($this->internal_examiner_id) {
                if ($this->internal_examiner_id === 'other') {
                    $exUser = User::firstOrCreate(
                        ['email' => $this->internal_examiner_email],
                        ['name' => $this->internal_examiner_name, 'password' => Hash::make(\Illuminate\Support\Str::random(12))]
                    );
                    if (!$exUser->hasRole('Examiner')) {
                        $exUser->assignRole('Examiner');
                    }
                    $resolvedInternalExaminerId = $exUser->id;
                } else {
                    $resolvedInternalExaminerId = $this->internal_examiner_id;
                    $exUser = User::find($resolvedInternalExaminerId);
                    if ($exUser && !$exUser->hasRole('Examiner')) {
                        $exUser->assignRole('Examiner');
                    }
                }
            }

            // Resolve External Examiner
            $resolvedExternalExaminerId = null;
            if ($this->external_examiner_id) {
                if ($this->external_examiner_id === 'other') {
                    $exUser = User::firstOrCreate(
                        ['email' => $this->external_examiner_email],
                        ['name' => $this->external_examiner_name, 'password' => Hash::make(\Illuminate\Support\Str::random(12))]
                    );
                    if (!$exUser->hasRole('Examiner')) {
                        $exUser->assignRole('Examiner');
                    }
                    $resolvedExternalExaminerId = $exUser->id;
                } else {
                    $resolvedExternalExaminerId = $this->external_examiner_id;
                    $exUser = User::find($resolvedExternalExaminerId);
                    if ($exUser && !$exUser->hasRole('Examiner')) {
                        $exUser->assignRole('Examiner');
                    }
                }
            }

            // Process Publications
            $pubsJson = [];
            foreach ($this->publications_data as $pub) {
                if (!empty($pub['title']) && !empty($pub['file'])) {
                    $path = $pub['file']->store('publications', 'r2');
                    $pubsJson[] = [
                        'title' => $pub['title'],
                        'file_path' => $path
                    ];
                }
            }

            // 2. Create Student
            $student = Student::create([
                'user_id' => $user->id,
                'matric_no' => strtoupper($this->matric_no),
                'place_of_work' => $this->place_of_work,
                'programme_id' => $this->programme_id,
                'degree_id' => $this->degree_id,
                'cohort_id' => $this->parsed_cohort_id,
                'admission_year' => $this->parsed_year,
                'registration_status' => 'Pending',
                'thesis_title' => $this->thesis_title,
                'thesis_abstract' => $this->thesis_abstract,
                'internal_examiner_id' => $resolvedInternalExaminerId,
                'external_examiner_id' => $resolvedExternalExaminerId,
                'publications' => json_encode($pubsJson),
            ]);

            // 3. Mark Registered Milestone
            $registeredMilestone = Milestone::where('order_index', 1)->first();
            StudentMilestone::create([
                'student_id' => $student->id,
                'milestone_id' => $registeredMilestone->id,
                'status' => 'Verified',
                'completion_date' => now(),
            ]);

            // 4. Save Supervisors if provided
            if (!empty($this->supervisors_data) && !empty($this->supervisors_data[0]['supervisor_id'])) {
                foreach ($this->supervisors_data as $index => $supData) {
                    if (empty($supData['supervisor_id'])) continue;
                    
                    $supervisorId = $supData['supervisor_id'];
                    
                    if ($supervisorId === 'other') {
                        // Create or find user
                        $supUser = User::firstOrCreate(
                            ['email' => $supData['email']],
                            ['name' => $supData['name'], 'password' => Hash::make(\Illuminate\Support\Str::random(12))]
                        );
                        if (!$supUser->hasRole('Supervisor')) {
                            $supUser->assignRole('Supervisor');
                        }
                        
                        // Create or find supervisor profile
                        $supervisor = \App\Models\Supervisor::firstOrCreate([
                            'user_id' => $supUser->id
                        ]);
                        $supervisorId = $supervisor->id;
                    }

                    $role = $index === 0 ? 'Principal' : 'Co-Supervisor';

                    DB::table('student_supervisor')->insert([
                        'student_id' => $student->id,
                        'supervisor_id' => $supervisorId,
                        'role' => $role,
                        'status' => 'Active',
                        'assigned_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 5. Mark Completed Milestones
            foreach ($this->questions as $q) {
                if (isset($q['completed']) && $q['completed']) {
                    $sm = StudentMilestone::create([
                        'student_id' => $student->id,
                        'milestone_id' => $q['id'],
                        'status' => 'Pending', // Pending Coordinator verification
                        'completion_date' => $this->milestone_dates[$q['name']] ?? now(),
                        'remarks' => $q['name'] === 'Seminar Course' ? 'Grade: ' . $this->seminar_grade : null,
                    ]);
                    
                    if ($q['name'] === 'Internal Defence' && $this->internal_thesis_file) {
                        $path = $this->internal_thesis_file->store('theses/internal', 'r2');
                        $sm->documents()->create([
                            'file_name' => 'Internal_Defence_Thesis.pdf',
                            'file_path' => $path,
                        ]);
                    }
                    
                    if ($hasThesis && $q['name'] === 'Viva') {
                        $path = $this->thesis_file->store('theses', 'r2');
                        $sm->documents()->create([
                            'file_name' => 'Final_Thesis.pdf',
                            'file_path' => $path,
                        ]);
                        
                        // Mark Completed milestone too
                        $completedMilestone = Milestone::where('order_index', 9)->first();
                        StudentMilestone::create([
                            'student_id' => $student->id,
                            'milestone_id' => $completedMilestone->id,
                            'status' => 'Pending',
                            'completion_date' => now(),
                        ]);
                    }
                }
            }

            // Auto-login
            \Illuminate\Support\Facades\Auth::login($user);
        });

        $this->step = 99; // Show success state
    }

    public function render()
    {
        return view('livewire.student-registration-wizard', [
            'programmes' => Programme::all(),
            'degrees' => Degree::all(),
        ]);
    }
}
