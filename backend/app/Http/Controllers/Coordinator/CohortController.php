<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\StudentProfile;
use App\Models\MilestoneTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CohortController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $scopes = $user->coordinatorScopes();

        if ($scopes->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No active coordinator profile found.');
        }

        $programIds = $scopes->pluck('program_id')->unique()->toArray();
        $levelIds = $scopes->pluck('level_id')->filter()->unique()->toArray();

        // Fetch all cohorts so coordinators can register new students into them
        $cohorts = Cohort::withCount(['students' => function ($q) use ($user) {
            $q->forCoordinator($user);
        }])->orderBy('intake_year', 'desc')->get();

        $milestoneTemplates = MilestoneTemplate::where(function($q) use ($programIds) {
                $q->whereNull('program_id')->orWhereIn('program_id', $programIds);
            })
            ->orderBy('order')
            ->get();

        // Calculate progress for each cohort
        foreach ($cohorts as $cohort) {
            $studentsCount = $cohort->students_count;
            if ($studentsCount === 0) {
                $cohort->average_milestone = 0;
                $cohort->average_progress = 0;
                $cohort->progress_distribution = [];
                continue;
            }

            // Get all student milestones for this cohort
            $studentIds = StudentProfile::where('cohort_id', $cohort->id)
                ->forCoordinator($user)
                ->pluck('id');

            $milestones = \App\Models\StudentMilestone::whereIn('thesis_project_id', function($query) use ($studentIds) {
                $query->select('id')->from('thesis_projects')->whereIn('student_profile_id', $studentIds);
            })->with('template')->get();

            $distribution = [];
            foreach ($milestoneTemplates as $tm) {
                $count = $milestones->where('milestone_template_id', $tm->id)->where('status', 'approved')->count();
                $distribution[$tm->order] = [
                    'name' => $tm->name,
                    'count' => $count,
                    'percentage' => round(($count / $studentsCount) * 100)
                ];
            }
            $cohort->progress_distribution = $distribution;
            
            // Average progress (using the highest approved milestone for each student)
            $totalProgress = 0;
            foreach ($studentIds as $sid) {
                $highestApproved = $milestones->where('thesis.student_profile_id', $sid)
                    ->where('status', 'approved')
                    ->sortByDesc('template.order')
                    ->first();
                $totalProgress += $highestApproved ? $highestApproved->template->order : 0;
            }
            $maxOrder = $milestoneTemplates->max('order') ?: 10;
            $cohort->average_progress = round(($totalProgress / ($studentsCount * $maxOrder)) * 100);
        }

        return view('coordinator.cohorts.index', compact('cohorts', 'milestoneTemplates'));
    }

    public function show(Cohort $cohort)
    {
        $user = Auth::user();
        if ($user->hasRole('Program Coordinator')) {
            $user->ensureCoordinatorProfiles();
        }
        $activeProfiles = $user->coordinatorProfiles()->where('active', true)->get();

        if ($activeProfiles->isEmpty() && !$user->hasAnyRole(['Admin', 'Director'])) {
            return redirect()->route('dashboard')->with('error', 'No active coordinator profile found.');
        }

        if ($user->hasAnyRole(['Admin', 'Director']) && $activeProfiles->isEmpty()) {
            $programIds = \App\Models\Program::pluck('id')->toArray();
            $levelIds = \App\Models\Level::pluck('id')->toArray();
        } else {
            $programIds = $activeProfiles->pluck('program_id')->unique()->toArray();
            $levelIds = $activeProfiles->pluck('level_id')->unique()->toArray();
        }

        $students = StudentProfile::where('cohort_id', $cohort->id)
            ->whereIn('program_id', $programIds)
            ->whereIn('level_id', $levelIds)
            ->with(['user', 'thesis.milestones.template', 'thesis.assignments.supervisor.user'])
            ->get();

        $milestones = MilestoneTemplate::where(function($q) use ($programIds) {
                $q->whereNull('program_id')->orWhereIn('program_id', $programIds);
            })
            ->orderBy('order')
            ->get();

        $categorizedStudents = [];
        foreach ($milestones as $m) {
            $categorizedStudents[$m->order] = [
                'name' => $m->name,
                'students' => collect()
            ];
        }

        foreach ($students as $student) {
            if (!$student->thesis) continue;
            
            $currentMilestone = $student->thesis->milestones
                ->filter(fn($m) => in_array($m->milestone_template_id, $milestones->pluck('id')->toArray()))
                ->sortBy('template.order')
                ->first(fn($m) => $m->status !== 'approved');

            if ($currentMilestone) {
                // Determine missing approvals
                $requiredApprovers = $currentMilestone->template->required_approvers ?? ['Program Coordinator'];
                $approvals = $currentMilestone->approvals ?? [];
                $checklist = [];
                
                foreach ($requiredApprovers as $role) {
                    if ($role === 'Supervisor') {
                        $activeSups = $student->thesis->assignments->where('status', 'active');
                        foreach ($activeSups as $index => $sup) {
                            $supRole = "Supervisor " . ($index + 1);
                            $checklist[$supRole] = [
                                'name' => $sup->supervisor->user->name,
                                'status' => isset($approvals["Supervisor:".$sup->supervisor_profile_id]) ? 'Approved' : 'Pending'
                            ];
                        }
                    } else {
                        $checklist[$role] = [
                            'name' => $role,
                            'status' => isset($approvals[$role]) ? 'Approved' : 'Pending'
                        ];
                    }
                }

                $categorizedStudents[$currentMilestone->template->order]['students']->push([
                    'profile' => $student,
                    'milestone' => $currentMilestone,
                    'checklist' => $checklist,
                    'submission' => $currentMilestone->submissions()->latest()->first()
                ]);
            }
        }

        return view('coordinator.cohorts.show', compact('cohort', 'categorizedStudents', 'milestones'));
    }

    public function registerStudentsForm(Cohort $cohort)
    {
        $this->authorize('update', $cohort);
        $user = Auth::user();
        $programIds = $user->coordinatorProfiles()->where('active', true)->pluck('program_id')->unique();
        $programs = \App\Models\Program::whereIn('id', $programIds)->get();
        $levels = \App\Models\Level::all();
        
        return view('admin.cohorts.register_students', compact('cohort', 'programs', 'levels'));
    }

    public function registerStudents(\App\Http\Requests\Admin\RegisterStudentRequest $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        if ($request->type === 'single') {
            return $this->registerSingleStudent($request, $cohort);
        }

        return $this->registerBulkStudents($request, $cohort);
    }

    private function registerSingleStudent(\App\Http\Requests\Admin\RegisterStudentRequest $request, Cohort $cohort)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $cohort) {
            $password = 'ACETEL-' . rand(100000, 999999);
            
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'is_active' => true,
                'creator_id' => auth()->id(),
            ]);
            
            $user->assignRole('Student');
            
            $profile = $user->studentProfile()->create([
                'cohort_id' => $cohort->id,
                'program_id' => $request->program_id,
                'student_id_number' => $request->matrix_number,
                'enrollment_status' => 'active',
                'current_semester' => 1,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'place_of_work' => $request->place_of_work,
            ]);

            $profile->thesis()->create([
                'title' => 'Pending Thesis Initiation',
                'abstract' => 'Candidate has not yet submitted their thesis proposal or supervisor alignment details.',
                'status' => 'proposed',
            ]);
            
            \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
        });

        return redirect()->route('coordinator.cohorts.show', $cohort)->with('success', 'Student registered successfully.');
    }

    private function registerBulkStudents(\App\Http\Requests\Admin\RegisterStudentRequest $request, Cohort $cohort)
    {
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), "r");
        
        $header = fgetcsv($handle); 
        
        $processedCount = 0;
        $errors = [];
        
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) < 4) continue;
                
                $name = trim($data[0] ?? '');
                $email = trim($data[1] ?? '');
                $programSearch = trim($data[2] ?? '');
                $matrixNumber = trim($data[3] ?? '');
                $gender = trim($data[4] ?? '');
                $phone = trim($data[5] ?? '');
                $nationality = trim($data[6] ?? '');
                $placeOfWork = trim($data[7] ?? '');
                
                if (empty($email) || empty($programSearch)) continue;
                
                $program = \App\Models\Program::where('name', 'like', "%{$programSearch}%")
                    ->orWhere('code', 'like', "%{$programSearch}%")
                    ->first();
                    
                if (!$program) {
                    $errors[] = "Unknown program: {$programSearch} for {$email}";
                    continue;
                }
                if (strlen($matrixNumber) < 6 || !in_array(substr($matrixNumber, 5, 1), ['1', '2'])) {
                    $errors[] = "Invalid Matric Number format: {$matrixNumber} for {$email}. After the year, it must be 1 or 2.";
                    continue;
                }

                if (\App\Models\User::where('email', $email)->exists()) {
                    $errors[] = "Duplicate email: {$email}";
                    continue;
                }
                
                $password = 'ACETEL-' . rand(100000, 999999);
                
                $user = \App\Models\User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make($password),
                    'is_active' => true,
                    'creator_id' => auth()->id()
                ]);
                
                $user->assignRole('Student');
                
                $profile = $user->studentProfile()->create([
                    'cohort_id' => $cohort->id,
                    'program_id' => $program->id,
                    'student_id_number' => $matrixNumber,
                    'enrollment_status' => 'active',
                    'current_semester' => 1,
                    'gender' => !empty($gender) ? $gender : null,
                    'phone_number' => !empty($phone) ? $phone : null,
                    'nationality' => !empty($nationality) ? $nationality : null,
                    'place_of_work' => !empty($placeOfWork) ? $placeOfWork : null,
                ]);

                $profile->thesis()->create([
                    'title' => 'Pending Thesis Initiation',
                    'abstract' => 'Candidate has not yet submitted their thesis proposal or supervisor alignment details.',
                    'status' => 'proposed',
                ]);
                
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
                $processedCount++;
            }
            
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Failure: ' . $e->getMessage());
        }
        
        fclose($handle);
        return redirect()->route('coordinator.cohorts.show', $cohort)->with('success', "Imported {$processedCount} students.");
    }

    public function bulkScheduleDefence(Request $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        // Coordinators can only schedule proposal dates
        $request->validate([
            'defence_type' => 'required|in:proposal',
            'defence_date' => 'required|date',
            'target' => 'required|in:all,selected',
            'student_ids' => 'required_if:target,selected|array',
            'student_ids.*' => 'exists:student_profiles,id'
        ]);

        $query = \App\Models\StudentProfile::where('cohort_id', $cohort->id);
        if ($request->target === 'selected') {
            $query->whereIn('id', $request->student_ids);
        }

        $students = $query->with('thesis.milestones.template')->get();
        $updatedCount = 0;

        foreach ($students as $student) {
            if (!$student->thesis) continue;

            $milestonesToUpdate = $student->thesis->milestones->filter(function($m) {
                // Check direct defence_type
                if ($m->template->defence_type === 'proposal') {
                    return true;
                }
                
                // Fallback for unset template defence_type
                if (!$m->template->defence_type) {
                    $lowerName = strtolower($m->template->name);
                    if (str_contains($lowerName, 'proposal')) return true;
                }
                
                return false;
            });

            foreach ($milestonesToUpdate as $milestone) {
                $milestone->defence_date = $request->defence_date;
                $milestone->save();
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully scheduled Proposal defence date for {$updatedCount} thesis milestone(s).");
    }
}
