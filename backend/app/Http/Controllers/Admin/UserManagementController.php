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
            $allowedRoles = ['Admin', 'Director', 'Program Coordinator', 'Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        } elseif ($creator->hasRole('Program Coordinator')) {
            $allowedRoles = ['Supervisor', 'Internal Examiner', 'External Examiner', 'Student'];
        }

        // Support both array 'roles' and fallback single 'role'
        $roles = $request->input('roles', []);
        if (empty($roles) && $request->filled('role')) {
            $roles = [$request->input('role')];
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        $roles = array_values(array_filter((array) $roles));

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'roles' => 'required|array|min:1',
            'roles.*' => ['string', Rule::in($allowedRoles)],
        ];

        if (in_array('Student', $roles)) {
            if (count($roles) > 1) {
                return back()->withInput()->withErrors(['roles' => 'The Student role cannot be combined with faculty or administrative roles.']);
            }
            $rules = array_merge($rules, [
                'cohort_id' => 'required|exists:cohorts,id',
                'program_id' => 'required|exists:programs,id',
                'level_id' => 'required|exists:levels,id',
                'student_id_number' => ['required', 'string', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
            ]);
        }

        $request->merge(['roles' => $roles]);
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

            $user->syncRoles($roles);

            // 1. Program Coordinator Profile Assignment
            if (in_array('Program Coordinator', $roles)) {
                $programIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($programIds) && $request->filled('program_id')) {
                    $programIds = [$request->program_id];
                }
                $levels = \App\Models\Level::all();
                foreach ($programIds as $programId) {
                    foreach ($levels as $level) {
                        \App\Models\CoordinatorProfile::firstOrCreate([
                            'user_id' => $user->id,
                            'program_id' => $programId,
                            'level_id' => $level->id,
                        ], [
                            'active' => true,
                        ]);
                    }
                }
            }

            // 2. Supervisor Profile Assignment
            if (in_array('Supervisor', $roles)) {
                $supervisorProfile = $user->supervisorProfile()->firstOrCreate([
                    'user_id' => $user->id,
                ], [
                    'staff_id' => 'STF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                    'max_students' => 10,
                    'current_load' => 0,
                    'rank' => $request->input('rank'),
                ]);

                if ($request->filled('rank')) {
                    $supervisorProfile->update(['rank' => $request->input('rank')]);
                }

                $progIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if ($request->filled('program_id')) {
                    $progIds[] = $request->program_id;
                }
                $progIds = array_unique(array_filter($progIds));
                if (!empty($progIds)) {
                    $supervisorProfile->programs()->syncWithoutDetaching($progIds);
                }
            }

            // 3. Internal Examiner Profile Assignment
            if (in_array('Internal Examiner', $roles)) {
                $internalProgIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($internalProgIds) && $request->filled('program_id')) {
                    $internalProgIds = [$request->program_id];
                }
                if (empty($internalProgIds)) {
                    $firstProg = \App\Models\Program::first();
                    if ($firstProg) $internalProgIds = [$firstProg->id];
                }
                foreach ($internalProgIds as $progId) {
                    \App\Models\InternalExaminerProfile::firstOrCreate([
                        'user_id' => $user->id,
                        'program_id' => $progId,
                    ], [
                        'active' => true,
                    ]);
                }
            }

            // 4. External Examiner Profile Assignment
            if (in_array('External Examiner', $roles)) {
                $externalProgIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($externalProgIds) && $request->filled('program_id')) {
                    $externalProgIds = [$request->program_id];
                }
                if (empty($externalProgIds)) {
                    $firstProg = \App\Models\Program::first();
                    if ($firstProg) $externalProgIds = [$firstProg->id];
                }
                $institution = $request->input('institution', 'External Institution');
                foreach ($externalProgIds as $progId) {
                    \App\Models\ExternalExaminerProfile::firstOrCreate([
                        'user_id' => $user->id,
                        'program_id' => $progId,
                    ], [
                        'institution' => $institution,
                        'active' => true,
                    ]);
                }
            }

            // 5. Student Profile Assignment
            if (in_array('Student', $roles)) {
                $profile = \App\Models\StudentProfile::create([
                    'user_id' => $user->id,
                    'cohort_id' => $validated['cohort_id'],
                    'program_id' => $validated['program_id'],
                    'level_id' => $validated['level_id'],
                    'student_id_number' => $validated['student_id_number'],
                    'enrollment_status' => 'active',
                    'current_semester' => 1,
                ]);

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

        return redirect()->route('admin.users.index')->with('success', 'User created successfully with assigned role(s).');
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

        $userRoles = $user->roles->pluck('name')->toArray();
        foreach ($userRoles as $uRole) {
            if (!in_array($uRole, $allowedRoles)) {
                $allowedRoles[] = $uRole;
            }
        }

        $roles = Role::whereIn('name', $allowedRoles)->pluck('name');
        $cohorts = Cohort::latest()->get();
        $programs = Program::all();
        $levels = Level::all();
        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'cohorts', 'programs', 'levels'));
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
        
        $currentUserRoles = $user->roles->pluck('name')->toArray();
        foreach ($currentUserRoles as $uRole) {
            if (!in_array($uRole, $allowedRoles)) {
                $allowedRoles[] = $uRole;
            }
        }

        // Support both array 'roles' and fallback single 'role'
        $roles = $request->input('roles', []);
        if (empty($roles) && $request->filled('role')) {
            $roles = [$request->input('role')];
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        $roles = array_values(array_filter((array) $roles));

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => 'required|array|min:1',
            'roles.*' => ['string', Rule::in($allowedRoles)],
        ];

        if (in_array('Student', $roles)) {
            if (count($roles) > 1) {
                return back()->withInput()->withErrors(['roles' => 'The Student role cannot be combined with faculty or administrative roles.']);
            }
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

        $request->merge(['roles' => $roles]);
        $validated = $request->validate($rules);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $request->has('is_active'),
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);
            $user->syncRoles($roles);

            // 1. Program Coordinator Profile Sync
            if (in_array('Program Coordinator', $roles)) {
                $user->coordinatorProfiles()->delete();
                $progIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($progIds) && $request->filled('program_id')) {
                    $progIds = [$request->program_id];
                }
                $levels = \App\Models\Level::all();
                foreach ($progIds as $progId) {
                    foreach ($levels as $level) {
                        \App\Models\CoordinatorProfile::create([
                            'user_id' => $user->id,
                            'program_id' => $progId,
                            'level_id' => $level->id,
                            'active' => true,
                        ]);
                    }
                }
            } else {
                // Remove coordinator profiles if role was unassigned
                $user->coordinatorProfiles()->delete();
            }

            // 2. Supervisor Profile Sync
            if (in_array('Supervisor', $roles)) {
                $supervisorProfile = $user->supervisorProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'staff_id' => $user->supervisorProfile?->staff_id ?? 'STF-' . strtoupper(\Illuminate\Support\Str::random(6)),
                        'max_students' => $user->supervisorProfile?->max_students ?? 10,
                        'rank' => $request->input('rank') ?? $user->supervisorProfile?->rank,
                    ]
                );
                
                $progIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if ($request->filled('program_id')) {
                    $progIds[] = $request->program_id;
                }
                $progIds = array_unique(array_filter($progIds));
                if (!empty($progIds)) {
                    $supervisorProfile->programs()->sync($progIds);
                }
            }

            // 3. Internal Examiner Profile Sync
            if (in_array('Internal Examiner', $roles)) {
                $user->internalExaminerProfiles()->delete();
                $progIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($progIds) && $request->filled('program_id')) {
                    $progIds = [$request->program_id];
                }
                if (empty($progIds)) {
                    $firstProg = \App\Models\Program::first();
                    if ($firstProg) $progIds = [$firstProg->id];
                }
                foreach ($progIds as $progId) {
                    \App\Models\InternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $progId,
                        'active' => true,
                    ]);
                }
            } else {
                $user->internalExaminerProfiles()->delete();
            }

            // 4. External Examiner Profile Sync
            if (in_array('External Examiner', $roles)) {
                $user->externalExaminerProfiles()->delete();
                $progIds = array_unique(array_filter($request->input('coordinator_programs', [])));
                if (empty($progIds) && $request->filled('program_id')) {
                    $progIds = [$request->program_id];
                }
                if (empty($progIds)) {
                    $firstProg = \App\Models\Program::first();
                    if ($firstProg) $progIds = [$firstProg->id];
                }
                $institution = $request->input('institution', 'External Institution');
                foreach ($progIds as $progId) {
                    \App\Models\ExternalExaminerProfile::create([
                        'user_id' => $user->id,
                        'program_id' => $progId,
                        'institution' => $institution,
                        'active' => true,
                    ]);
                }
            } else {
                $user->externalExaminerProfiles()->delete();
            }

            // 5. Student Profile Sync
            if (in_array('Student', $roles)) {
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

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('User update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not update user: ' . $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully with synchronized roles and profiles.');
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
