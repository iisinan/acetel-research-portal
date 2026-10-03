<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MilestoneTemplate;
use App\Models\Program;
use Illuminate\Http\Request;

class MilestoneTemplateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $templates = MilestoneTemplate::with('program')
            ->with(['studentMilestones' => function($q) use ($isCoordinator, $coordinatorProgramId) { 
                $q->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
                  ->with(['thesis.student.user', 'thesis.student.cohort', 'thesis.defenceEvents.panelMembers.user', 'thesis.defenceEvents.evaluations', 'submissions.feedback']); 
                
                if ($isCoordinator) {
                    $q->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                        $sq->where('program_id', $coordinatorProgramId);
                    });
                }
            }])->orderBy('order')->get();
            
        $supervisors = \App\Models\SupervisorProfile::with('user')->get();
        $cohorts = \App\Models\Cohort::all();
        return view('admin.milestone-templates.index', compact('templates', 'supervisors', 'isCoordinator', 'cohorts'));
    }

    public function create()
    {
        $programs = Program::all();
        $roles = ['Admin', 'Supervisor', 'Program Coordinator', 'Internal Examiner', 'External Examiner'];
        return view('admin.milestone-templates.create', compact('programs', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer',
            'program_id' => 'nullable|exists:programs,id',
            'requires_submission' => 'boolean',
            'submission_requires_approval' => 'boolean',
            'submission_approver_roles' => 'nullable|array',
            'requires_approval' => 'boolean',
            'has_chat' => 'boolean',
            'show_supervisor_details' => 'boolean',
            'required_approvers' => 'nullable|array',
            'approval_threshold' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'submission_type' => 'required|array|min:1',
            'submission_type.*' => 'string|in:file,publication,ppt',
            'allow_defence_date' => 'boolean',
            'defence_type' => 'nullable|string|in:proposal,internal,external',
            'defence_date_role' => 'nullable|string',
            'is_final_archival' => 'boolean',
            'show_supervisor_assignment' => 'boolean',
            'show_internal_examiner_assignment' => 'boolean',
            'show_external_examiner_assignment' => 'boolean',
            'allow_plagiarism_report' => 'boolean',
            'plagiarism_report_role' => 'nullable|string|in:Admin,Program Coordinator',
        ]);

        // Fix for checkboxes
        $validated['requires_submission'] = $request->has('requires_submission');
        $validated['submission_requires_approval'] = $request->has('submission_requires_approval');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['has_chat'] = $request->has('has_chat');
        $validated['show_supervisor_details'] = $request->has('show_supervisor_details');
        $validated['allow_defence_date'] = $request->has('allow_defence_date');
        $validated['is_final_archival'] = $request->has('is_final_archival');
        $validated['show_supervisor_assignment'] = $request->has('show_supervisor_assignment');
        $validated['show_internal_examiner_assignment'] = $request->has('show_internal_examiner_assignment');
        $validated['show_external_examiner_assignment'] = $request->has('show_external_examiner_assignment');
        $validated['allow_plagiarism_report'] = $request->has('allow_plagiarism_report');

        // Order is auto-handled by model boot events (shift or append)
        $template = MilestoneTemplate::create($validated);

        // Sync this new milestone with existing thesis projects
        $projects = \App\Models\ThesisProject::query();
        if ($template->program_id) {
            $projects->whereHas('student', fn($q) => $q->where('program_id', $template->program_id));
        }
        
        $syncCount = 0;
        foreach ($projects->get() as $project) {
            $project->syncMilestones();
            $syncCount++;
        }

        // Create a system announcement to notify users
        \App\Models\Announcement::create([
            'title' => 'New Milestone Requirement: ' . $template->name,
            'content' => 'A new institutional milestone has been added to the graduation track. It has been automatically synchronized with ' . $syncCount . ' active research projects.',
            'type' => 'info',
            'starts_at' => now(),
            'ends_at' => now()->addDays(7),
            'target_role' => null, // null means all roles
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.milestone-templates.index')->with('success', 'Milestone template created and synchronized with ' . $syncCount . ' projects.');
    }

    public function edit(MilestoneTemplate $milestoneTemplate)
    {
        $programs = Program::all();
        $roles = ['Admin', 'Supervisor', 'Program Coordinator', 'Internal Examiner', 'External Examiner'];
        return view('admin.milestone-templates.edit', compact('milestoneTemplate', 'programs', 'roles'));
    }

    public function update(Request $request, MilestoneTemplate $milestoneTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'required|integer',
            'program_id' => 'nullable|exists:programs,id',
            'requires_submission' => 'boolean',
            'submission_requires_approval' => 'boolean',
            'submission_approver_roles' => 'nullable|array',
            'requires_approval' => 'boolean',
            'has_chat' => 'boolean',
            'show_supervisor_details' => 'boolean',
            'required_approvers' => 'nullable|array',
            'approval_threshold' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'submission_type' => 'required|array|min:1',
            'submission_type.*' => 'string|in:file,publication,ppt',
            'allow_defence_date' => 'boolean',
            'defence_type' => 'nullable|string|in:proposal,internal,external',
            'defence_date_role' => 'nullable|string',
            'is_final_archival' => 'boolean',
            'show_supervisor_assignment' => 'boolean',
            'show_internal_examiner_assignment' => 'boolean',
            'show_external_examiner_assignment' => 'boolean',
            'allow_plagiarism_report' => 'boolean',
            'plagiarism_report_role' => 'nullable|string|in:Admin,Program Coordinator',
        ]);

        // Fix for checkboxes
        $validated['requires_submission'] = $request->has('requires_submission');
        $validated['submission_requires_approval'] = $request->has('submission_requires_approval');
        $validated['requires_approval'] = $request->has('requires_approval');
        $validated['has_chat'] = $request->has('has_chat');
        $validated['show_supervisor_details'] = $request->has('show_supervisor_details');
        $validated['allow_defence_date'] = $request->has('allow_defence_date');
        $validated['is_final_archival'] = $request->has('is_final_archival');
        $validated['show_supervisor_assignment'] = $request->has('show_supervisor_assignment');
        $validated['show_internal_examiner_assignment'] = $request->has('show_internal_examiner_assignment');
        $validated['show_external_examiner_assignment'] = $request->has('show_external_examiner_assignment');
        $validated['allow_plagiarism_report'] = $request->has('allow_plagiarism_report');

        $milestoneTemplate->update($validated);

        // Clean up any gaps
        MilestoneTemplate::renumberSequence($milestoneTemplate->program_id);

        // Sync with existing thesis projects (in case it's newly relevant)
        $projects = \App\Models\ThesisProject::query();
        if ($milestoneTemplate->program_id) {
            $projects->whereHas('student', fn($q) => $q->where('program_id', $milestoneTemplate->program_id));
        }
        
        foreach ($projects->get() as $project) {
            $project->syncMilestones();
        }

        return redirect()->route('admin.milestone-templates.index')->with('success', 'Milestone template updated and synchronized.');
    }

    public function destroy(MilestoneTemplate $milestoneTemplate)
    {
        $milestoneTemplate->delete();
        return redirect()->route('admin.milestone-templates.index')->with('success', 'Milestone template deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:milestone_templates,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            $template = MilestoneTemplate::find($id);
            if ($template) {
                // We update without triggering the model events to avoid infinite reordering
                $template->order = $index + 1;
                $template->saveQuietly();
            }
        }

        return response()->json(['success' => true]);
    }

    public function setDate(Request $request, $templateId)
    {
        $template = MilestoneTemplate::findOrFail($templateId);
        
        $validated = $request->validate([
            'global_defence_date' => 'required|date'
        ]);

        $template->update(['global_defence_date' => $validated['global_defence_date']]);

        // Sync to all un-approved student milestones
        \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->where('status', '!=', 'approved')
            ->update(['defence_date' => $validated['global_defence_date']]);

        return redirect()->route('admin.milestone-templates.index')->with('success', "Date scheduled for {$template->name} and synced to all pending students.");
    }

    public function schedule(Request $request)
    {
        $request->validate([
            'milestone_ids' => 'required|array',
            'start_date' => 'required|date',
            'students_per_day' => 'required|integer|min:1'
        ]);

        $ids = $request->milestone_ids;
        $currentDate = \Carbon\Carbon::parse($request->start_date);
        $count = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $milestone = \App\Models\StudentMilestone::findOrFail($id);
                $thesis = $milestone->thesis;
                $template = $milestone->template;
                
                if (in_array('Supervisor', $template->required_approvers ?? []) && !$milestone->is_supervisor_approved) {
                    continue; // Skip if supervisor hasn't approved
                }
                
                $milestone->update([
                    'defence_date' => $currentDate->format('Y-m-d')
                ]);

                $event = \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? 'seminar',
                    ],
                    [
                        'schedule_start' => $currentDate->copy()->setHour(9)->setMinute(0),
                        'schedule_end' => $currentDate->copy()->setHour(10)->setMinute(0),
                    ]
                );
                
                if ($thesis->student && $thesis->student->user) {
                    try {
                        $thesis->student->user->notify(new \App\Notifications\EventScheduled($event));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send EventScheduled notification: " . $e->getMessage());
                    }
                }

                $count++;
                if ($count % $request->students_per_day === 0) {
                    $currentDate->addDay();
                    while ($currentDate->isWeekend()) {
                        $currentDate->addDay();
                    }
                }
            }
            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Scheduled ' . count($ids) . ' presentations successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error scheduling: ' . $e->getMessage());
        }
    }

    public function assignExaminer(Request $request, $milestoneId)
    {
        $request->validate([
            'supervisor_profile_id' => 'required|exists:supervisor_profiles,id'
        ]);

        $milestone = \App\Models\StudentMilestone::findOrFail($milestoneId);
        $supervisor = \App\Models\SupervisorProfile::with('user')->findOrFail($request->supervisor_profile_id);
        $template = $milestone->template;

        $event = \App\Models\DefenceEvent::firstOrCreate(
            [
                'thesis_project_id' => $milestone->thesis_project_id,
                'type' => $template->defence_type ?? 'seminar',
            ],
            [
                'schedule_start' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(9) : now()->addDays(7),
                'schedule_end' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(10) : now()->addDays(7)->addHour(),
            ]
        );

        \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'Examiner')->delete();

        \App\Models\PanelMember::create([
            'defence_event_id' => $event->id,
            'user_id' => $supervisor->user_id,
            'role' => 'Examiner',
            'invitation_status' => 'accepted'
        ]);
        
        $supervisor->user->notify(new \App\Notifications\EventScheduled($event));

        return back()->with('success', 'Examiner assigned successfully.');
    }

    public function assignExaminerGlobal(Request $request, $templateId)
    {
        $request->validate([
            'supervisor_profile_ids' => 'required|array',
            'supervisor_profile_ids.*' => 'exists:supervisor_profiles,id'
        ]);

        $template = MilestoneTemplate::findOrFail($templateId);
        $supervisors = \App\Models\SupervisorProfile::with('user')->whereIn('id', $request->supervisor_profile_ids)->get();

        $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->with('thesis')
            ->get();

        $assignedCount = 0;

        foreach ($milestones as $milestone) {
            $event = \App\Models\DefenceEvent::firstOrCreate(
                [
                    'thesis_project_id' => $milestone->thesis_project_id,
                    'type' => $template->defence_type ?? 'seminar',
                ],
                [
                    'schedule_start' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(9) : now()->addDays(7),
                    'schedule_end' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(10) : now()->addDays(7)->addHour(),
                ]
            );

            \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'Examiner')->delete();

            foreach ($supervisors as $supervisor) {
                \App\Models\PanelMember::create([
                    'defence_event_id' => $event->id,
                    'user_id' => $supervisor->user_id,
                    'role' => 'Examiner',
                    'invitation_status' => 'accepted'
                ]);
            }

            $assignedCount++;
        }

        // Notify the examiners once
        try {
            foreach ($supervisors as $supervisor) {
                $supervisor->user->notify(new \App\Notifications\ExaminerNominated($template, $request->custom_message, $assignedCount));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed in assignExaminerGlobal: ' . $e->getMessage());
        }
        
        $names = $supervisors->map(fn($s) => $s->user->name)->implode(', ');
        return back()->with('success', "Examiners ($names) assigned to all {$assignedCount} students and notified successfully.");
    }

    public function exportStudents(MilestoneTemplate $template)
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $query = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->with(['thesis.student.user', 'thesis.defenceEvents.evaluations', 'submissions.feedback']);
            
        if ($isCoordinator) {
            $query->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                $sq->where('program_id', $coordinatorProgramId);
            });
        }
        
        $milestones = $query->get();
            
        $fileName = 'students_' . $template->slug . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Matric Number', 'Status', 'Date Scheduled', 'Score'];

        $callback = function() use($milestones, $columns, $template) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($milestones as $milestone) {
                $avgScore = 'N/A';
                if ($template->slug === 'seminar_as_a_course') {
                    $event = current($milestone->thesis->defenceEvents->where('type', $template->defence_type ?? 'seminar')->all());
                    if ($event && $event->evaluations->count() > 0) {
                        $total = 0;
                        $count = 0;
                        foreach($event->evaluations as $eval) {
                            if (isset($eval->score['total'])) {
                                $total += $eval->score['total'];
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $avgScore = round($total / $count, 1);
                        }
                    }
                }

                $statusLabel = $milestone->is_supervisor_approved ? 'Approved by Supervisor' : ucfirst(str_replace('_', ' ', $milestone->status));

                fputcsv($file, [
                    $milestone->thesis->student->user->name ?? '',
                    $milestone->thesis->student->matric_number ?? '',
                    $statusLabel,
                    $milestone->defence_date ?? 'Not Scheduled',
                    $avgScore
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}