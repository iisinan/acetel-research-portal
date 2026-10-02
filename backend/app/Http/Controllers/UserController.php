<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Program;
use App\Models\Level;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function importForm()
    {
        $cohorts = Cohort::orderBy('start_date', 'desc')->get();
        return view('admin.users.import', compact('cohorts'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'cohort_id' => 'required|exists:cohorts,id',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Assuming first row is header
        
        // Normalize headers to lowercase
        $header = array_map('strtolower', $header);
        
        $count = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Map row to header
            if (count($header) !== count($row)) continue;
            $data = array_combine($header, $row);

            try {
                // Determine headers
                $surname = trim($data['surname'] ?? '');
                $otherNames = trim($data['other names'] ?? '');
                $fullName = $surname . ' ' . $otherNames;
                $email = trim($data['email address'] ?? '');
                $programSerial = trim($data['program'] ?? '');
                $matricNumber = trim($data['matric number'] ?? '');
                $gender = trim($data['gender'] ?? '');
                $phone = trim($data['phone number'] ?? '');
                $nationality = trim($data['nationality'] ?? '');

                // Find Program & Level
                $program = Program::where('serial_number', $programSerial)->first();
                $level = Level::where('name', $data['level'] ?? 'MSc')->first(); // Default to MSc if level not in CSV

                if (!$program) {
                     $errors[] = "Row for $email: Invalid Program Serial ($programSerial).";
                     continue;
                }

                // Create User
                $password = 'ACETEL-' . rand(100000, 999999);
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName,
                        'password' => Hash::make($password),
                        'is_active' => true,
                        'must_change_password' => true,
                    ]
                );
                
                if ($user->wasRecentlyCreated) {
                    $user->assignRole('Student');
                    \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
                }

                // Create/Update Profile
                $user->studentProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'program_id' => $program->id,
                        'level_id' => $level?->id ?? $program->degree_type, // Fallback logic
                        'cohort_id' => $request->cohort_id,
                        'student_id_number' => $matricNumber,
                        'gender' => $gender,
                        'phone_number' => $phone,
                        'nationality' => $nationality,
                        'enrollment_status' => 'active',
                    ]
                );

                $count++;
            } catch (\Exception $e) {
                $errors[] = "Row for $email: " . $e->getMessage();
            }
        }

        fclose($handle);

        $msg = "Imported $count students successfully.";
        if (count($errors) > 0) {
            return redirect()->back()->with('success', $msg)->with('error', 'Some rows failed: ' . implode(' | ', array_slice($errors, 0, 5)));
        }

        return redirect()->route('users.index')->with('success', $msg);
    }

    public function index()
    {
        $users = User::with(['roles', 'supervisorProfile.assignments' => function ($q) {
            $q->where('status', 'active');
        }])->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $programs = Program::all();
        $levels = Level::all();
        $cohorts = Cohort::orderBy('start_date', 'desc')->get();
        return view('admin.users.create', compact('roles', 'programs', 'levels', 'cohorts'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'exists:roles,name'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'cohort_id' => ['nullable', 'exists:cohorts,id'],
            'student_id_number' => ['nullable', 'string', 'max:50', new \App\Rules\ValidMatricNumber],
            'coordinator_programs' => ['nullable', 'array'],
            'coordinator_programs.*' => ['nullable', 'exists:programs,id'],
        ];

        $request->validate($rules);

        $password = 'ACETEL-' . rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'is_active' => $request->boolean('is_active', true),
            'must_change_password' => true
        ]);

        \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));

        $user->assignRole($request->role);

        // Handle Profile Creation based on Role
        if ($request->role === 'Student') {
             if (!$request->cohort_id || !$request->student_id_number) {
                 $user->delete(); 
                 return back()->withInput()->with('error', 'Session and Matric Number are required for Students.');
             }

             $user->studentProfile()->create([
                 'program_id' => $request->program_id,
                 'level_id' => $request->level_id, // Now nullable per migration
                 'cohort_id' => $request->cohort_id,
                 'enrollment_status' => 'active',
                 'student_id_number' => $request->student_id_number,
             ]);
        } elseif ($request->role === 'Supervisor') {
             $user->supervisorProfile()->create([
                 'program_id' => $request->program_id,
                 'staff_id' => 'SUP-' . rand(1000, 9999)
             ]);
        } elseif ($request->role === 'Program Coordinator') {
             $programs = $request->input('coordinator_programs', []);
             
             foreach ($programs as $progId) {
                if (!$progId) continue;
                $user->coordinatorProfiles()->create([
                    'program_id' => $progId,
                    'level_id' => null, // Level removed per institutional directive
                    'active' => true
                ]);
             }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $programs = Program::all();
        $levels = Level::all();
        return view('admin.users.edit', compact('user', 'roles', 'programs', 'levels'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
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
