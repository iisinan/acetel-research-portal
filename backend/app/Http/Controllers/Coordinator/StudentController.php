<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $query = \App\Models\StudentProfile::forCoordinator($user)
            ->with(['user', 'cohort', 'thesis.assignments.supervisor.user']);

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('student_id_number', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('program', function($p) use ($search) {
                      $p->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                  })
                  ->orWhereHas('thesis', function($t) use ($search) {
                      $t->where('title', 'ilike', "%{$search}%");
                  });
            });
        }

        $students = $query->paginate(15)->withQueryString();
        $userScopes = $user->coordinatorScopes();
        $programs = \App\Models\Program::whereIn('id', $userScopes->pluck('program_id'))->get();
        $levelIds = $userScopes->pluck('level_id')->filter()->unique()->toArray();
        $levels = !empty($levelIds) ? \App\Models\Level::whereIn('id', $levelIds)->get() : \App\Models\Level::all();
        
        return view('coordinator.students.index', compact('students', 'programs', 'levels'));
    }

    public function show(StudentProfile $student)
    {
        $user = Auth::user();
        
        if (!$user->hasCoordinatorAccess($student)) {
             abort(403, 'This student does not belong to your program.');
        }

        $student->load(['user', 'thesis.milestones.template', 'thesis.assignments.supervisor.user', 'program', 'level']);
        
        // Fetch supervisors matching the student's program (Restriction Protocol)
        $availableSupervisors = SupervisorProfile::with('user')
            ->whereHas('programs', function($q) use ($student) {
                $q->where('programs.id', $student->program_id);
            })->get();

        $availableInternalExaminers = \App\Models\InternalExaminerProfile::with('user')
            ->where('program_id', $student->program_id)
            ->where('active', true)
            ->get();
            
        $availableExternalExaminers = \App\Models\ExternalExaminerProfile::with('user')
            ->where('program_id', $student->program_id)
            ->where('active', true)
            ->get();

        return view('coordinator.students.show', compact('student', 'availableSupervisors', 'availableInternalExaminers', 'availableExternalExaminers'));
    }

    public function assignSupervisor(Request $request, StudentProfile $student)
    {
        if ($student->isSeminarCourseLevel()) {
            return back()->with('error', 'Students at the seminar course level do not require a supervisor.');
        }

        $user = Auth::user();
        
        if (!$user->hasCoordinatorAccess($student)) {
             abort(403, 'This student does not belong to your program.');
        }

        $request->validate([
            'supervisor_id' => 'required|exists:supervisor_profiles,id',
            'type' => 'nullable|in:main,co-supervisor',
            'replace_assignment_id' => 'nullable|exists:supervision_assignments,id'
        ]);

        $thesis = $student->thesis;
        
        if (!$thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        DB::beginTransaction();
        try {
            $supervisor = SupervisorProfile::with('user', 'department')->findOrFail($request->supervisor_id);

            // Handle Replacement if requested
            if ($request->has('replace_assignment_id')) {
                $oldAssignment = SupervisionAssignment::findOrFail($request->replace_assignment_id);
                if ($oldAssignment->thesis_project_id !== $thesis->id) {
                    throw new \Exception("Security Alert: Invalid assignment reference protocol.");
                }
                
                // Get the role from the old assignment if type not provided
                $role = $request->input('type', $oldAssignment->role);
                
                // Decommission old assignment
                $oldAssignment->update(['status' => 'ended', 'ended_at' => now()]);
                
                // Create new assignment
                SupervisionAssignment::create([
                    'thesis_project_id' => $thesis->id,
                    'supervisor_profile_id' => $supervisor->id,
                    'role' => $role,
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);
            } else {
                $type = $request->input('type', 'co-supervisor');

                // Count Check
                $levelName = strtolower($student->program->degree_type ?? $student->level->name ?? '');
                $activeAssignmentsCount = $thesis->assignments()->where('status', 'active')->count();
                
                if ($activeAssignmentsCount >= 3) {
                    return back()->with('error', 'This student has reached the maximum institutional panel limit (3 members).');
                }

                // Institutional Rule: Lead/Main Supervisor must always be a Professor (MSc & PhD)
                if ($type === 'main') {
                    $hasMain = $thesis->assignments()->where('status', 'active')->where('role', 'main')->exists();
                    if ($hasMain) {
                        return back()->with('error', 'This student already has a primary supervisor assigned.');
                    }

                    if (strtoupper($supervisor->rank ?? '') !== 'PROFESSOR') {
                        return back()->with('error', 'Academic Hierarchy Violation: The Primary Supervisor must be a Professor.');
                    }
                }

                // Check if already assigned
                $existing = SupervisionAssignment::where('thesis_project_id', $thesis->id)
                    ->where('supervisor_profile_id', $supervisor->id)
                    ->first();
                    
                if ($existing) {
                    if ($existing->status !== 'active') {
                        $existing->update(['status' => 'active', 'role' => $type]);
                    } else {
                        return back()->with('error', 'Supervisor is already assigned.');
                    }
                } else {
                    SupervisionAssignment::create([
                        'thesis_project_id' => $thesis->id,
                        'supervisor_profile_id' => $supervisor->id,
                        'role' => $type,
                        'status' => 'active',
                        'assigned_at' => now(),
                    ]);
                }
            }
            
            // Notifications logic
            $coordinatorId = auth()->id();
            $studentUser = $student->user;
            $supUser = $supervisor->user;
            
            // Notify Student
            if ($studentUser->id) {
                $supervisorDetailsStr = "- Name: {$supUser->name}\n  Email: {$supUser->email}\n  Department: " . ($supervisor->department->name ?? 'N/A') . "\n\n";
                $studentBody = "Dear {$studentUser->name},\n\nA supervisor has been newly allocated to your academic thesis project. Below are the details:\n\n{$supervisorDetailsStr}\nPlease check the portal for more details.\n\nBest Regards,\nProgram Coordinator";
                
                $studentMsg = \App\Models\InboxMessage::create([
                    'sender_id' => $coordinatorId,
                    'subject' => 'Supervisor Assigned to Your Thesis',
                    'body' => $studentBody,
                ]);
                $studentMsg->recipients()->attach($studentUser->id, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to'
                ]);
                \App\Events\MessageReceived::dispatch($studentMsg, $studentUser->id);

                \Illuminate\Support\Facades\Mail::to($studentUser->email)->queue(new \App\Mail\SupervisorAllocated([
                    'subject' => 'Supervisor Assigned to Your Thesis',
                    'greeting' => 'Dear ' . $studentUser->name . ',',
                    'body' => 'A supervisor has been newly allocated to your academic thesis project. Please check the portal to see the names and details of your appointed supervisors.',
                    'user_role' => 'Supervisor',
                    'user_details' => [
                        'name' => $supUser->name,
                        'email' => $supUser->email,
                        'program' => $thesis->student->program->name ?? 'N/A',
                        'thesis_title' => $thesis->title
                    ],
                    'action_text' => 'View Dashboard',
                    'action_url' => route('dashboard')
                ]));
            }

            // Notify Supervisor
            if ($supUser->id) {
                $studentDetailsStr = "- Name: {$studentUser->name}\n- Email: {$studentUser->email}\n- Program: " . ($thesis->student->program->name ?? 'N/A') . "\n- Thesis Title: {$thesis->title}";
                $supBody = "Dear {$supUser->name},\n\nYou have been newly allocated to supervise {$studentUser->name} for their thesis. Student Details:\n\n{$studentDetailsStr}\n\nPlease log in to your dashboard to review the student details.\n\nBest Regards,\nProgram Coordinator";
                
                $supMsg = \App\Models\InboxMessage::create([
                    'sender_id' => $coordinatorId,
                    'subject' => 'New Thesis Student Allocation',
                    'body' => $supBody,
                ]);
                $supMsg->recipients()->attach($supUser->id, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to'
                ]);
                \App\Events\MessageReceived::dispatch($supMsg, $supUser->id);

                \Illuminate\Support\Facades\Mail::to($supUser->email)->queue(new \App\Mail\SupervisorAllocated([
                    'subject' => 'New Thesis Student Allocation',
                    'greeting' => 'Dear ' . $supUser->name . ',',
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

            DB::commit();
            return back()->with('success', 'Supervisor assigned successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update protocol: ' . $e->getMessage());
        }
    }
}
