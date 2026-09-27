<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\ThesisProject;
use App\Models\MilestoneTemplate;
use App\Models\StudentMilestone;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\Cohort;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create()
    {
        $programs = \App\Models\Program::all();
        $levels = Level::all();
        $supervisors = SupervisorProfile::with('user')->get();
        $internalExaminers = \App\Models\InternalExaminerProfile::with('user')->get();
        $externalExaminers = \App\Models\ExternalExaminerProfile::with('user')->get();
        $milestones = MilestoneTemplate::orderBy('order')->get();

        return view('auth.register', compact('programs', 'levels', 'supervisors', 'internalExaminers', 'externalExaminers', 'milestones'));
    }

    public function validateStep1(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'matric_number' => ['required', 'string', 'max:255', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'matric_number' => ['required', 'string', 'max:255', 'unique:student_profiles,student_id_number', new \App\Rules\ValidMatricNumber],
            'program_id' => 'required|exists:programs,id',
            'place_of_work' => 'nullable|string|max:255',
            'thesis_title' => 'nullable|string|max:255',
            'thesis_abstract' => 'nullable|string',
            'supervisor_ids' => 'nullable|array',
            'supervisor_ids.*' => 'nullable|distinct',
            'new_supervisors' => 'nullable|array',
            'completed_milestones' => 'nullable|array',
            'completed_milestones.*' => 'exists:milestone_templates,id',
            'seminar_grade' => 'nullable|string|max:50',
            'proposal_defence_date' => 'nullable|date',
            'progress_presentation_1_date' => 'nullable|date',
            'progress_presentation_2_date' => 'nullable|date',
            'internal_defence_date' => 'nullable|date',
            'publications' => 'nullable|array',
            'publications.*.title' => 'nullable|string|max:255',
            'publications.*.doi' => 'nullable|string|max:255',
            'publications.*.file' => 'nullable|file|mimes:pdf|max:10240',
            'progress_presentation_1_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'progress_presentation_2_ppt' => 'nullable|file|mimes:pdf,ppt,pptx|max:10240',
            'thesis_title' => 'nullable|string|max:255',
            'thesis_abstract' => 'nullable|string|max:5000',
            'internal_examiner_id' => 'nullable',
            'internal_examiner_name' => 'nullable|string|max:255',
            'internal_examiner_email' => 'nullable|email|max:255',
            'external_examiner_id' => 'nullable',
            'external_examiner_name' => 'nullable|string|max:255',
            'external_examiner_email' => 'nullable|email|max:255',
            'final_thesis_file' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        DB::beginTransaction();
        try {
            $uploadedFiles = [];
            
            // 1. Parse Matric Number for Year and Batch (e.g. ACE2310008)
            $matric = strtoupper($request->matric_number);
            
            // Default fallbacks
            $year = (int)date('Y');
            $batch = 1;
            
            if (strlen($matric) >= 6) {
                $yearStr = substr($matric, 3, 2);
                $batchStr = substr($matric, 5, 1);
                
                if (is_numeric($yearStr)) {
                    $year = 2000 + (int)$yearStr;
                }
                if (is_numeric($batchStr)) {
                    $parsedBatch = (int)$batchStr;
                    if ($parsedBatch === 1 || $parsedBatch === 2) {
                        $batch = $parsedBatch;
                    }
                }
            }

            $cohortCode = $year . '-B' . $batch;
            $cohortName = $year . ' - Batch ' . $batch;
            
            $cohort = Cohort::firstOrCreate(
                ['code' => $cohortCode],
                [
                    'name' => $cohortName,
                    'intake_year' => $year,
                    'start_date' => $year . '-01-01',
                    'end_date' => ($year + 1) . '-12-31',
                    'status' => 'active',
                ]
            );

            // 2. Create User
            $user = User::create([
                'name' => trim($request->name),
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);
            
            $role = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);
            $user->assignRole($role);

            $program = \App\Models\Program::find($request->program_id);
            $isPhd = stripos($program->name, 'phd') !== false;
            
            $levelId = null;
            if ($isPhd) {
                $levelId = \App\Models\Level::where('name', 'like', '%PhD%')->value('id');
            } else {
                $levelId = \App\Models\Level::where('name', 'like', '%MSc%')->value('id');
            }

            // 3. Create StudentProfile
            $student = StudentProfile::create([
                'user_id' => $user->id,
                'student_id_number' => $matric,
                'program_id' => $request->program_id,
                'level_id' => $levelId,
                'cohort_id' => $cohort->id,
                'place_of_work' => $request->place_of_work,
            ]);

            // 3b. Handle Internal Examiner
            $internalExaminerProfileId = $request->input('internal_examiner_id');
            if (!$internalExaminerProfileId && $request->filled('internal_examiner_name')) {
                $ieName = $request->internal_examiner_name;
                $ieEmail = $request->filled('internal_examiner_email') 
                            ? $request->internal_examiner_email 
                            : strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', $ieName)) . '-' . uniqid() . '@examiner.acetel.edu.ng';
                
                $ieUser = User::firstOrCreate(
                    ['email' => $ieEmail],
                    [
                        'name' => $ieName,
                        'password' => Hash::make(Str::random(12)),
                    ]
                );
                if (!$ieUser->hasRole('Internal Examiner')) {
                    $ieUser->assignRole('Internal Examiner');
                }
                
                $ieProfile = \App\Models\InternalExaminerProfile::firstOrCreate(
                    ['user_id' => $ieUser->id],
                    [
                        'department' => 'Assigned',
                        'title' => 'Internal Examiner',
                    ]
                );
                $internalExaminerProfileId = $ieProfile->id;
            }

            // 3c. Handle External Examiner
            $externalExaminerProfileId = $request->input('external_examiner_id');
            if (!$externalExaminerProfileId && $request->filled('external_examiner_name')) {
                $eeName = $request->external_examiner_name;
                $eeEmail = $request->filled('external_examiner_email') 
                            ? $request->external_examiner_email 
                            : strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', $eeName)) . '-' . uniqid() . '@external.acetel.edu.ng';
                
                $eeUser = User::firstOrCreate(
                    ['email' => $eeEmail],
                    [
                        'name' => $eeName,
                        'password' => Hash::make(Str::random(12)),
                    ]
                );
                if (!$eeUser->hasRole('External Examiner')) {
                    $eeUser->assignRole('External Examiner');
                }
                
                $eeProfile = \App\Models\ExternalExaminerProfile::firstOrCreate(
                    ['user_id' => $eeUser->id],
                    [
                        'institution' => 'Assigned',
                        'expertise' => 'External Examiner',
                    ]
                );
                $externalExaminerProfileId = $eeProfile->id;
            }

            // 4. Create Thesis Project
            $thesis = ThesisProject::create([
                'student_profile_id' => $student->id,
                'title' => $request->thesis_title ?? 'Untitled Thesis',
                'abstract' => $request->thesis_abstract,
                'status' => 'active', // default status
                'internal_examiner_profile_id' => $internalExaminerProfileId,
                'external_examiner_profile_id' => $externalExaminerProfileId,
            ]);

            // 5. Assign Supervisors
            $finalSups = []; 
            $submittedSupIds = $request->input('supervisor_ids', []);
            $submittedNewSups = $request->input('new_supervisors', []);

            for ($i = 0; $i < 3; $i++) {
                $role = ($i === 0) ? 'primary' : 'secondary';
                $supId = null;

                if (!empty($submittedSupIds[$i])) {
                    $supId = $submittedSupIds[$i];
                } elseif (!empty($submittedNewSups[$i]['name'])) {
                    $newSup = $submittedNewSups[$i];
                    $supEmail = !empty($newSup['email']) 
                        ? $newSup['email'] 
                        : strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', $newSup['name'])) . '-' . uniqid() . '@supervisor.acetel.edu.ng';

                    // Create User
                    $supUser = User::firstOrCreate(
                        ['email' => $supEmail],
                        [
                            'name' => $newSup['name'],
                            'password' => Hash::make(Str::random(12)), // random password
                        ]
                    );
                    if (!$supUser->hasRole('Supervisor')) {
                        $supUser->assignRole('Supervisor');
                    }

                    // Create SupervisorProfile
                    $supProfile = SupervisorProfile::firstOrCreate(
                        ['user_id' => $supUser->id],
                        [
                            'department' => 'Assigned',
                            'title' => 'Supervisor',
                            'max_load' => 5,
                        ]
                    );
                    $supId = $supProfile->id;
                }

                if ($supId) {
                    $finalSups[] = [
                        'id' => $supId,
                        'role' => $role
                    ];
                }
            }

            // Remove duplicate supervisors if user bypasses frontend validation
            $uniqueSups = [];
            $seenIds = [];
            foreach ($finalSups as $sup) {
                if (!in_array($sup['id'], $seenIds)) {
                    $uniqueSups[] = $sup;
                    $seenIds[] = $sup['id'];
                }
            }
            $finalSups = $uniqueSups;

            foreach ($finalSups as $sup) {
                SupervisionAssignment::create([
                    'thesis_project_id' => $thesis->id,
                    'supervisor_profile_id' => $sup['id'],
                    'role' => $sup['role'],
                    'status' => 'active',
                    'assigned_at' => now(),
                ]);
                
                $supProfile = SupervisorProfile::find($sup['id']);
                if ($supProfile) {
                    $supProfile->increment('current_load');
                }
            }

            // 6. Setup Milestones
            $templates = MilestoneTemplate::orderBy('order')->get();
            $completedIds = $request->completed_milestones ?? [];

            // Determine the active milestone: the first template (by order) that is NOT completed.
            // All completed ones → 'approved'. The first non-completed → 'in_progress'. The rest → 'not_started'.
            $activeMilestoneId = null;
            foreach ($templates as $template) {
                if (!in_array($template->id, $completedIds)) {
                    $activeMilestoneId = $template->id;
                    break;
                }
            }

            foreach ($templates as $template) {
                $isCompleted = in_array($template->id, $completedIds);
                $isActive    = ($template->id === $activeMilestoneId);

                if ($isCompleted) {
                    $milestoneStatus = 'approved';
                } elseif ($isActive) {
                    $milestoneStatus = 'in_progress';
                } else {
                    $milestoneStatus = 'not_started';
                }

                $sm = StudentMilestone::updateOrCreate(
                    [
                        'thesis_project_id'       => $thesis->id,
                        'milestone_template_id'   => $template->id,
                    ],
                    [
                        'status'           => $milestoneStatus,
                        'due_date'         => $isCompleted ? null : now()->addDays(30),
                        'submitted_at'     => $isCompleted ? now() : null,
                        'date_approved_at' => $isCompleted ? now() : null,
                    ]
                );

                if ($isCompleted) {
                    if ($template->slug === 'seminar_as_a_course' && $request->has('seminar_grade')) {
                        $sm->update(['remark' => 'Course Grade: ' . $request->seminar_grade]);
                    } elseif ($template->slug === 'proposal_defence' && $request->has('proposal_defence_date')) {
                        $sm->update(['defence_date' => $request->proposal_defence_date]);
                    } elseif ($template->slug === 'progress_presentation_1' && $request->has('progress_presentation_1_date')) {
                        $sm->update(['defence_date' => $request->progress_presentation_1_date]);
                    } elseif ($template->slug === 'progress_presentation_2' && $request->has('progress_presentation_2_date')) {
                        $sm->update(['defence_date' => $request->progress_presentation_2_date]);
                    } elseif ($template->slug === 'internal_defence') {
                        if ($request->has('internal_defence_date')) {
                            $sm->update(['defence_date' => $request->internal_defence_date]);
                        }
                        if ($request->has('publications') && is_array($request->input('publications'))) {
                            foreach ($request->input('publications') as $index => $pubData) {
                                if (!is_array($pubData)) continue;
                                if (empty($pubData['title']) && empty($pubData['doi']) && !$request->hasFile("publications.{$index}.file")) {
                                    continue;
                                }
                                
                                $path = null;
                                if ($request->hasFile("publications.{$index}.file")) {
                                    $path = $request->file("publications.{$index}.file")->store('publications', 'public');
                                    $uploadedFiles[] = $path;
                                }
                                
                                $desc = "Publication";
                                if (!empty($pubData['title'])) $desc .= ": " . $pubData['title'];
                                if (!empty($pubData['doi'])) $desc .= " (DOI: " . $pubData['doi'] . ")";

                                \App\Models\Submission::create([
                                    'student_milestone_id' => $sm->id,
                                    'version' => 1,
                                    'file_url' => $path,
                                    'file_meta' => $request->hasFile("publications.{$index}.file") ? [
                                        'original_name' => $request->file("publications.{$index}.file")->getClientOriginalName(),
                                        'mime_type' => $request->file("publications.{$index}.file")->getMimeType(),
                                        'size' => $request->file("publications.{$index}.file")->getSize(),
                                    ] : null,
                                    'submitted_by' => $user->id,
                                    'description' => $desc,
                                ]);
                            }
                        }
                    } elseif ($template->slug === 'viva') {
                        if ($request->has('viva_date')) {
                            $sm->update(['defence_date' => $request->viva_date]);
                        }
                        if ($request->hasFile('final_thesis_file') && $request->file('final_thesis_file')->getPathname()) {
                            $path = $request->file('final_thesis_file')->store('theses', 'public');
                            $uploadedFiles[] = $path;
                            \App\Models\Submission::create([
                                'student_milestone_id' => $sm->id,
                                'version' => 1,
                                'file_url' => $path,
                                'file_meta' => [
                                    'original_name' => $request->file('final_thesis_file')->getClientOriginalName(),
                                    'mime_type' => $request->file('final_thesis_file')->getMimeType(),
                                    'size' => $request->file('final_thesis_file')->getSize(),
                                ],
                                'submitted_by' => $user->id,
                                'description' => 'Final Thesis Uploaded',
                            ]);
                        }
                    }
                } else {
                    if ($template->slug === 'progress_presentation_1' && $request->hasFile('progress_presentation_1_ppt') && $request->file('progress_presentation_1_ppt')->getPathname()) {
                        $path = $request->file('progress_presentation_1_ppt')->store('presentations', 'public');
                        $uploadedFiles[] = $path;
                        \App\Models\Submission::create([
                            'student_milestone_id' => $sm->id,
                            'version' => 1,
                            'file_url' => $path,
                            'file_meta' => [
                                'original_name' => $request->file('progress_presentation_1_ppt')->getClientOriginalName(),
                                'mime_type' => $request->file('progress_presentation_1_ppt')->getMimeType(),
                                'size' => $request->file('progress_presentation_1_ppt')->getSize(),
                            ],
                            'submitted_by' => $user->id,
                            'description' => 'Presentation slide (PPT) uploaded for scheduling',
                        ]);
                        $sm->update(['status' => 'submitted', 'submitted_at' => now()]);
                    } elseif ($template->slug === 'progress_presentation_2' && $request->hasFile('progress_presentation_2_ppt') && $request->file('progress_presentation_2_ppt')->getPathname()) {
                        $path = $request->file('progress_presentation_2_ppt')->store('presentations', 'public');
                        $uploadedFiles[] = $path;
                        \App\Models\Submission::create([
                            'student_milestone_id' => $sm->id,
                            'version' => 1,
                            'file_url' => $path,
                            'file_meta' => [
                                'original_name' => $request->file('progress_presentation_2_ppt')->getClientOriginalName(),
                                'mime_type' => $request->file('progress_presentation_2_ppt')->getMimeType(),
                                'size' => $request->file('progress_presentation_2_ppt')->getSize(),
                            ],
                            'submitted_by' => $user->id,
                            'description' => 'Presentation slide (PPT) uploaded for scheduling',
                        ]);
                        $sm->update(['status' => 'submitted', 'submitted_at' => now()]);
                    }
                }
            }

            // Sync project status based on highest completed milestone
            $this->syncProjectStatus($thesis, $completedIds, $templates);

            DB::commit();

            Auth::login($user);
            
            session()->flash('success', 'Registration complete! Welcome to your dashboard.');
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'redirect' => route('dashboard')]);
            }
            
            return redirect()->route('dashboard');

        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($uploadedFiles) && is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $file) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                }
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred during registration. Please try again. ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withInput()->withErrors(['error' => 'An error occurred during registration. Please try again. ' . $e->getMessage()]);
        }
    }

    private function syncProjectStatus($thesis, $completedIds, $templates)
    {
        if (empty($completedIds)) return;

        $highestOrder = 0;
        $highestSlug = '';
        foreach ($templates as $template) {
            if (in_array($template->id, $completedIds) && $template->order > $highestOrder) {
                $highestOrder = $template->order;
                $highestSlug = $template->slug;
            }
        }

        switch ($highestSlug) {
            case 'proposal_defence':
                $thesis->update(['status' => 'proposal_passed']);
                break;
            case 'internal_defence':
                $thesis->update(['status' => 'internal_passed']);
                break;
            case 'viva':
                $thesis->update(['status' => 'completed', 'end_date' => now()]);
                break;
            default:
                $thesis->update(['status' => 'active']);
                break;
        }
    }
}
