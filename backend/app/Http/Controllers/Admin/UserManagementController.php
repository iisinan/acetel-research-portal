<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('roles')
            ->whereDoesntHave('roles', function($q) {
                $q->where('name', 'Student');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role != '') {
            $role = $request->role; // Assuming role name passed
            $query->role($role);
        }

        if ($request->has('status') && $request->status != '') { // active/inactive
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->has('cohort') && $request->cohort != '') {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('cohort_id', $request->cohort);
            });
        }

        $users = $query->latest()->paginate(10);
        $roles = Role::pluck('name'); // For filter dropdown
        $cohorts = Cohort::latest()->get(); // For filter dropdown

        return view('admin.users.index', compact('users', 'roles', 'cohorts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $allowedRoles = [];

        if ($user->hasAnyRole(['Admin', 'Director'])) {
            $allowedRoles = ['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        } elseif ($user->hasRole('Program Coordinator')) {
            $allowedRoles = ['Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        }

        $roles = Role::whereIn('name', $allowedRoles)->pluck('name');
        $cohorts = Cohort::latest()->get();
        $programs = Program::all();
        $levels = Level::all();
        return view('admin.users.create', compact('roles', 'cohorts', 'programs', 'levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $creator = auth()->user();
        $allowedRoles = [];

        if ($creator->hasAnyRole(['Admin', 'Director'])) {
            // Admin/Director can create everything including themselves
            $allowedRoles = ['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        } elseif ($creator->hasRole('Program Coordinator')) {
            $allowedRoles = ['Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => ['required', 'string', Rule::in($allowedRoles)],
        ];

        if ($request->input('role') === 'Student') {
            $rules = array_merge($rules, [
                'cohort_id' => 'required|exists:cohorts,id',
                'program_id' => 'required|exists:programs,id',
                'level_id' => 'required|exists:levels,id',
                'student_id_number' => ['required', 'string', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
            ]);
        }

        $validated = $request->validate($rules);
        
        $password = 'ACETEL-' . rand(100000, 999999);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'is_active' => $request->has('is_active'),
            'must_change_password' => true,
        ]);

        $user->assignRole($validated['role']);

        // Handle Coordinator Assignments
        if ($validated['role'] === 'Program Coordinator' && $request->has('coordinator_programs')) {
            $levels = \App\Models\Level::all();
            $programIds = array_unique(array_filter($request->input('coordinator_programs')));
            foreach ($programIds as $programId) {
                foreach ($levels as $level) {
                    \App\Models\CoordinatorProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $programId,
                        'level_id' => $level->id,
                        'active' => true,
                    ]);
                }
            }
        }

        if ($validated['role'] === 'Supervisor') {
            $profile = $user->supervisorProfile()->create([
                'staff_id' => 'STF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'max_students' => 10,
                'current_load' => 0,
                'rank' => $request->input('rank'),
            ]);
            
            if ($request->filled('program_id')) {
                $profile->programs()->sync([$request->program_id]);
            }
        }

        // Handle Examiner & Supervisor Profile Assignments (Multi-Program)
        $programIds = [];
        if (in_array($validated['role'], ['Internal Examiner', 'External Examiner', 'Supervisor']) && $request->has('coordinator_programs')) {
            $programIds = array_unique(array_filter($request->input('coordinator_programs', [])));
            
            foreach ($programIds as $programId) {
                if ($validated['role'] === 'Internal Examiner') {
                    \App\Models\InternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $programId,
                        'active' => true,
                    ]);
                } elseif ($validated['role'] === 'Supervisor') {
                    $profile = $user->supervisorProfile()->firstOrCreate([
                        'user_id' => $user->id
                    ], [
                        'staff_id' => 'STF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'max_students' => 10,
                        'current_load' => 0,
                        'rank' => $request->input('rank'),
                    ]);
                    $profile->programs()->syncWithoutDetaching([$programId]);
                } else {
                    \App\Models\ExternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $programId,
                        'institution' => 'External Institution',
                        'active' => true,
                    ]);
                }
            }
        }

        // Backward compatibility support for single select (if any)
        if ($validated['role'] === 'Internal Examiner' && empty($programIds) && $request->filled('program_id')) {
             \App\Models\InternalExaminerProfile::create([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'active' => true,
            ]);
        }

        if ($validated['role'] === 'External Examiner' && empty($programIds) && $request->filled('program_id')) {
             \App\Models\ExternalExaminerProfile::create([
                'user_id' => $user->id,
                'program_id' => $request->program_id,
                'institution' => 'External Institution',
                'active' => true,
            ]);
        }

        // Handle Student Profile
        if ($validated['role'] === 'Student') {
            $profile = \App\Models\StudentProfile::create([
                'user_id' => $user->id,
                'cohort_id' => $validated['cohort_id'],
                'program_id' => $validated['program_id'],
                'level_id' => $validated['level_id'],
                'student_id_number' => $validated['student_id_number'],
                'enrollment_status' => 'active',
                'current_semester' => 1,
            ]);

            // Create initial thesis project for each student safely
            if ($profile) {
                $profile->thesis()->create([
                    'title' => 'Pending Project Initiation',
                    'abstract' => 'Student has not yet submitted their project proposal details.',
                    'status' => 'proposed',
                ]);
            }
        }        
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('User creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not create user: ' . $e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
        } catch (\Throwable $e) {
             \Illuminate\Support\Facades\Log::error("Mail sending failed for user {$user->email}: " . $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $creator = auth()->user();
        $allowedRoles = [];

        if ($creator->hasAnyRole(['Admin', 'Director'])) {
            $allowedRoles = ['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        } elseif ($creator->hasRole('Program Coordinator')) {
            $allowedRoles = ['Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        }

        // Maintain role if it's already set to something else (e.g. legacy or other admin edits)
        $currentRole = $user->roles->first()?->name;
        if ($currentRole && !in_array($currentRole, $allowedRoles)) {
            $allowedRoles[] = $currentRole;
        }

        $roles = Role::whereIn('name', $allowedRoles)->pluck('name');
        $cohorts = Cohort::latest()->get();
        $programs = Program::all();
        $levels = Level::all();
        return view('admin.users.edit', compact('user', 'roles', 'cohorts', 'programs', 'levels'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'studentProfile.program', 'studentProfile.cohort', 'supervisorProfile', 'coordinatorProfiles.program', 'internalExaminerProfiles', 'externalExaminerProfiles']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $creator = auth()->user();
        $allowedRoles = [];

        if ($creator->hasAnyRole(['Admin', 'Director'])) {
            $allowedRoles = ['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        } elseif ($creator->hasRole('Program Coordinator')) {
            $allowedRoles = ['Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        }
        
        // Allow the current role to be validated even if it's not in the restricted set (for students/legacy)
        $currentRole = $user->roles->first()?->name;
        if ($currentRole && !in_array($currentRole, $allowedRoles)) {
            $allowedRoles[] = $currentRole;
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
        ];

        if ($request->input('role') === 'Student') {
            $rules = array_merge($rules, [
                'cohort_id' => 'required|exists:cohorts,id',
                'program_id' => 'required|exists:programs,id',
                'level_id' => 'required|exists:levels,id',
                'student_id_number' => ['required', 'string', Rule::unique('student_profiles')->ignore($user->studentProfile?->id), new \App\Rules\ValidMatricNumber],
            ]);
        }

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);
        $user->syncRoles([$validated['role']]);

        // Update Coordinator Assignments if applicable
        if ($validated['role'] === 'Program Coordinator') {
            // Remove old assignments
            $user->coordinatorProfiles()->delete();

            // Add new assignments
            if ($request->has('coordinator_programs')) {
                $levels = \App\Models\Level::all();
                $programIds = array_unique(array_filter($request->input('coordinator_programs')));
                foreach ($programIds as $programId) {
                    foreach ($levels as $level) {
                        \App\Models\CoordinatorProfile::create([
                            'user_id' => $user->id,
                            'program_id' => $programId,
                            'level_id' => $level->id,
                            'active' => true,
                        ]);
                    }
                }
            }
        }

        // Update Student Profile Details if applicable (Role Student)
        if ($validated['role'] === 'Student') {
            $user->studentProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cohort_id' => $validated['cohort_id'],
                    'program_id' => $validated['program_id'],
                    'level_id' => $validated['level_id'],
                    'student_id_number' => $validated['student_id_number'],
                ]
            );
        }

        // Handle Supervisor Profile
        if ($validated['role'] === 'Supervisor') {
            $profile = $user->supervisorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_id' => $user->supervisorProfile?->staff_id ?? 'STF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'max_students' => $user->supervisorProfile?->max_students ?? 10,
                    'rank' => $request->input('rank'),
                ]
            );
            
            if ($request->filled('program_id')) {
                $profile->programs()->sync([$request->program_id]);
            }
        }

        // Update Examiner & Supervisor Profile Assignments
        $programIds = [];
        if (in_array($validated['role'], ['Internal Examiner', 'External Examiner', 'Supervisor'])) {
             // Remove old profiles for examiners (handled by deletion of entire profile record for clean slate)
             if ($validated['role'] !== 'Supervisor') {
                $user->internalExaminerProfiles()->delete();
                $user->externalExaminerProfiles()->delete();
             }

            $programIds = array_unique(array_filter($request->input('coordinator_programs', [])));
            
            foreach ($programIds as $programId) {
                if ($validated['role'] === 'Internal Examiner') {
                    \App\Models\InternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $programId,
                        'active' => true,
                    ]);
                } else {
                    \App\Models\ExternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $programId,
                        'institution' => 'External Institution',
                        'active' => true,
                    ]);
                }
            }

            // support for single select compatibility if multi is empty
            if (empty($programIds) && $request->filled('program_id')) {
                if ($validated['role'] === 'Internal Examiner') {
                    \App\Models\InternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $request->program_id,
                        'active' => true,
                    ]);
                } else {
                    \App\Models\ExternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $request->program_id,
                        'institution' => 'External Institution',
                        'active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
             return back()->with('error', 'You cannot deactivate yourself.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User has been {$status}.");
    }

    public function importForm()
    {
        return view('admin.users.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        if (count($data) < 2) {
            return back()->with('error', 'CSV file is empty or invalid.');
        }

        $header = array_map(function($val) { 
            return trim(strtolower(str_replace([' ', '-'], '_', $val))); 
        }, $data[0]);

        // Expected headers: name, email, program (code or name), matric_number (student_id_number)
        
        $latestCohort = Cohort::latest()->first();
        $defaultLevel = Level::first(); // Or use a logic to find default level

        if (!$latestCohort) {
            return back()->with('error', 'No Academic Cohort defined. Please create a cohort first.');
        }

        $importedCount = 0;
        $errors = [];

        for ($i = 1; $i < count($data); $i++) {
            $row = $data[$i];
            
            if (count($row) !== count($header)) continue;
            
            $rowData = array_combine($header, $row);
            
            // Basic validation
            if (empty($rowData['email']) || empty($rowData['name']) || empty($rowData['program']) || empty($rowData['matric_number'])) {
                $errors[] = "Row $i: Missing required fields (name, email, program, matric_number).";
                continue;
            }

            if (User::where('email', $rowData['email'])->exists()) {
                $errors[] = "Row $i: Email {$rowData['email']} already exists.";
                continue;
            }

            $studentIdNumber = trim($rowData['matric_number']);
            
            if (strlen($studentIdNumber) < 6 || !in_array(substr($studentIdNumber, 5, 1), ['1', '2'])) {
                $errors[] = "Row $i: Invalid Matric Number format. After the year, it must be 1 or 2.";
                continue;
            }

            if (\App\Models\StudentProfile::where('student_id_number', $studentIdNumber)->exists()) {
                 $errors[] = "Row $i: Matric Number '{$studentIdNumber}' already in use.";
                 continue;
            }

            // Find Program
            $program = Program::where('code', trim($rowData['program']))
                ->orWhere('name', 'like', '%' . trim($rowData['program']) . '%')
                ->first();

            if (!$program) {
                $errors[] = "Row $i: Program '{$rowData['program']}' not found.";
                continue;
            }

            try {
                $password = \Illuminate\Support\Str::random(10);
                
                $user = User::create([
                    'name' => trim($rowData['name']),
                    'email' => trim($rowData['email']),
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'must_change_password' => true,
                ]);

                $user->assignRole('Student');

                $user->studentProfile()->create([
                    'cohort_id' => $latestCohort->id,
                    'program_id' => $program->id,
                    'level_id' => $defaultLevel ? $defaultLevel->id : null,
                    'student_id_number' => $studentIdNumber,
                    'enrollment_status' => 'active',
                ]);

                // Dispatch welcome email with the raw password
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));

                $importedCount++;
            } catch (\Throwable $e) {
                $errors[] = "Row $i: Failed to create user. " . $e->getMessage();
            }
        }

        $message = "Ingested $importedCount students successfully. login details sent via email.";
        if (count($errors) > 0) {
            return redirect()->route('admin.users.index')->with('success', $message)->with('error', "Issues with " . count($errors) . " rows. Check file formatting.");
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function resetPassword(User $user)
    {
        $password = 'ACETEL-' . rand(100000, 999999);
        
        $user->update([
            'password' => Hash::make($password),
            'must_change_password' => true,
        ]);

        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetDispatched($user, $password));

        return redirect()->back()->with('success', 'User password has been reset to default and credentials dispatched via email.');
    }
}
