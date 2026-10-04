<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cohort;

use App\Models\Program;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\Admin\RegisterStudentRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Cohort::class);

        $query = Cohort::withCount('students')->with(['creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
            });
        }
        


        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('intake_year')) {
            $query->where('intake_year', $request->intake_year);
        }

        $cohorts = $query->latest()->paginate(10)->withQueryString();
        
        return view('admin.cohorts.index', compact('cohorts'));
    }

    public function create()
    {
        $this->authorize('create', Cohort::class);
        return view('admin.cohorts.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Cohort::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:255', Rule::unique('cohorts')],

            'intake_year' => 'nullable|integer|min:2000|max:2100',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $validated['created_by'] = auth()->id();

        Cohort::create($validated);

        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort created successfully.');
    }

    public function show(Cohort $cohort)
    {
        $this->authorize('view', $cohort);
        $cohort->load(['students.user', 'students.program', 'students.level', 'students.thesis', 'creator']);
        return view('admin.cohorts.show', compact('cohort'));
    }

    public function edit(Cohort $cohort)
    {
        $this->authorize('update', $cohort);
        return view('admin.cohorts.edit', compact('cohort'));
    }

    public function update(Request $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:255', Rule::unique('cohorts')->ignore($cohort->id)],

            'intake_year' => 'nullable|integer|min:2000|max:2100',
            'status' => 'required|in:active,inactive,archived',
        ]);

        $cohort->update($validated);

        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort updated successfully.');
    }

    public function toggleStatus(Cohort $cohort)
    {
        $this->authorize('update', $cohort);
        
        $newStatus = $cohort->status === 'active' ? 'inactive' : 'active';
        $cohort->update(['status' => $newStatus]);
        
        return redirect()->back()->with('success', "Cohort status updated to {$newStatus}.");
    }

    public function destroy(Cohort $cohort)
    {
        $this->authorize('delete', $cohort);
        
        $cohort->delete();
        
        return redirect()->route('admin.cohorts.index')->with('success', 'Cohort and all associated student records have been permanently removed.');
    }

    public function registerStudentsForm(Cohort $cohort)
    {
        $this->authorize('update', $cohort);
        $programs = Program::all();
        $levels = Level::all();
        return view('admin.cohorts.register_students', compact('cohort', 'programs', 'levels'));
    }

    /**
     * Alias for registerStudentsForm to support legacy/quick-action routes.
     */
    public function addStudent(Cohort $cohort)
    {
        return $this->registerStudentsForm($cohort);
    }

    public function registerStudents(RegisterStudentRequest $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        if ($request->type === 'single') {
            return $this->registerSingleStudent($request, $cohort);
        }

        // Add Bulk Import logic logic here if needed, or default to a basic implementation for now
        return $this->registerBulkStudents($request, $cohort);
    }

    private function registerSingleStudent(RegisterStudentRequest $request, Cohort $cohort)
    {
        DB::transaction(function () use ($request, $cohort) {
            $password = 'ACETEL-' . rand(100000, 999999);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'is_active' => true,
                'creator_id' => auth()->id(),
            ]);
            
            $user->assignRole('Student');
            
            $program = Program::findOrFail($request->program_id);
            
            $profile = $user->studentProfile()->create([
                'cohort_id' => $cohort->id,
                'program_id' => $request->program_id,
                'level_id' => $program->level_id,
                'student_id_number' => $request->matrix_number,
                'enrollment_status' => 'active',
                'current_semester' => 1,
            ]);

            // Automatically provision research project placeholder to initialize milestones
            $profile->thesis()->create([
                'title' => 'Pending Thesis Initiation',
                'abstract' => 'Candidate has not yet submitted their thesis proposal or supervisor alignment details.',
                'status' => 'proposed',
            ]);
            
            // Dispatch credentials notification
            // Dispatch credentials notification with safety catch
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to queue welcome email for {$user->email}: " . $e->getMessage());
            }
        });



        return redirect()->route('admin.cohorts.show', $cohort)->with('success', 'Academic identity provisioned and notified successfully.');
    }

    private function registerBulkStudents(RegisterStudentRequest $request, Cohort $cohort)
    {
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), "r");
        
        $header = fgetcsv($handle); // Expected: S/N, Surname, Other Names, Program (Serial), Matric Number, Gender, Email Address, Phone Number, Nationality
        
        $processedCount = 0;
        $emailsSent = 0;
        $errors = [];
        
        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) < 7) continue;
                
                $surname = trim($data[1] ?? '');
                $otherNames = trim($data[2] ?? '');
                $programSerial = trim($data[3] ?? '');
                $matrixNumber = trim($data[4] ?? '');
                $gender = trim($data[5] ?? '');
                $email = trim($data[6] ?? '');
                $phoneNumber = trim($data[7] ?? '');
                $nationality = trim($data[8] ?? '');
                
                if (empty($email) || empty($programSerial)) continue;
                
                $fullName = $surname . ' ' . $otherNames;
                
                // Find program STRICTLY by serial_number
                $program = Program::where('serial_number', $programSerial)->first();
                    
                if (!$program) {
                    $errors[] = "Unknown program ($programSerial) for $email";
                    continue;
                }
                
                // Skip if email exists
                if (User::where('email', $email)->exists()) {
                    $errors[] = "Duplicate email ($email) skipped.";
                    continue;
                }

                // Check for Matric Number Format
                if (strlen($matrixNumber) < 6 || !in_array(substr($matrixNumber, 5, 1), ['1', '2'])) {
                    $errors[] = "Invalid Matric Number format ($matrixNumber) for $email skipped. After the year, it must be 1 or 2.";
                    continue;
                }

                // Check for Matric Number duplication
                if (\App\Models\StudentProfile::where('student_id_number', $matrixNumber)->exists()) {
                    $errors[] = "Duplicate Matric Number ($matrixNumber) for $email skipped.";
                    continue;
                }
                
                $password = 'ACETEL-' . rand(100000, 999999);
                
                $user = User::create([
                    'name' => $fullName,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'is_active' => true,
                    'must_change_password' => true,
                    'creator_id' => auth()->id()
                ]);
                
                $user->assignRole('Student');
                
                $profile = $user->studentProfile()->create([
                    'cohort_id' => $cohort->id,
                    'program_id' => $program->id,
                    'student_id_number' => $matrixNumber,
                    'gender' => $gender,
                    'phone_number' => $phoneNumber,
                    'nationality' => $nationality,
                    'enrollment_status' => 'active',
                    'current_semester' => 1,
                ]);

                // Automatically provision research project placeholder to initialize milestones
                $profile->thesis()->create([
                    'title' => 'Pending Thesis Initiation',
                    'abstract' => 'Candidate has not yet submitted their thesis proposal or supervisor alignment details.',
                    'status' => 'proposed',
                ]);
                
                // Dispatch credentials notification
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\WelcomeUser($user, $password));
                $emailsSent++;
                $processedCount++;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Critical failure during ingestion: ' . $e->getMessage());
        }
        
        fclose($handle);
        
        $msg = "Bulk Registration Complete! 📊 Total Students Registered: {$processedCount} | 📧 Total Emails Sent: {$emailsSent}.";
        if (count($errors) > 0) {
            $msg .= " ⚠️ Skipped " . count($errors) . " records due to conflicts or errors: " . implode(' | ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? '...' : '');
            return redirect()->route('admin.cohorts.show', $cohort)->with('warning', $msg);
        }
        
        return redirect()->route('admin.cohorts.show', $cohort)->with('success', $msg);
    }

    public function bulkScheduleDefence(Request $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        // Admins can schedule internal or external
        $request->validate([
            'defence_type' => 'required|in:internal,external,proposal',
            'defence_date' => 'required|date',
            'target' => 'required|in:all,selected',
            'student_ids' => 'required_if:target,selected|array',
            'student_ids.*' => 'exists:student_profiles,id'
        ]);

        $query = $cohort->students();
        if ($request->target === 'selected') {
            $query->whereIn('id', $request->student_ids);
        }

        $students = $query->with('thesis.milestones.template')->get();
        $updatedCount = 0;

        foreach ($students as $student) {
            if (!$student->thesis) continue;

            $milestonesToUpdate = $student->thesis->milestones->filter(function($m) use ($request) {
                // Check direct defence_type
                if ($m->template->defence_type === $request->defence_type) {
                    return true;
                }
                
                // Fallback for unset template defence_type
                if (!$m->template->defence_type) {
                    $lowerName = strtolower($m->template->name);
                    if ($request->defence_type === 'proposal' && str_contains($lowerName, 'proposal')) return true;
                    if ($request->defence_type === 'internal' && (str_contains($lowerName, 'internal') || $m->template->order == 9)) return true;
                    if ($request->defence_type === 'external' && (str_contains($lowerName, 'external') || $m->template->is_final_archival)) return true;
                }
                
                return false;
            });

            foreach ($milestonesToUpdate as $milestone) {
                $milestone->defence_date = $request->defence_date;
                $milestone->save();
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully scheduled {$request->defence_type} defence date for {$updatedCount} thesis milestone(s).");
    }

    public function exportAllEmails()
    {
        $this->authorize('viewAny', Cohort::class);

        $students = User::whereHas('studentProfile', function ($query) {
            $query->whereNotNull('cohort_id');
        })->with('studentProfile.program', 'studentProfile.cohort')->get();

        $csvData = "Name,Email,Student ID,Program,Cohort\n";

        foreach ($students as $student) {
            $profile = $student->studentProfile;
            $csvData .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $student->name,
                $student->email,
                $profile?->student_id_number ?? '',
                $profile?->program?->name ?? '',
                $profile?->cohort?->name ?? ''
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="all_students_emails.csv"');
    }

    public function exportCohortEmails(Cohort $cohort)
    {
        $this->authorize('view', $cohort);

        $students = User::whereHas('studentProfile', function ($query) use ($cohort) {
            $query->where('cohort_id', $cohort->id);
        })->with('studentProfile.program')->get();

        $csvData = "Name,Email,Student ID,Program,Cohort\n";

        foreach ($students as $student) {
            $profile = $student->studentProfile;
            $csvData .= sprintf(
                "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $student->name,
                $student->email,
                $profile?->student_id_number ?? '',
                $profile?->program?->name ?? '',
                $cohort->name
            );
        }

        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="cohort_' . Str::slug($cohort->name) . '_emails.csv"');
    }
}
