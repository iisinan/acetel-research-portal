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
            
        $supervisors = \App\Models\SupervisorProfile::with(['user', 'programs'])->get();
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
            'start_time' => 'nullable|string|max:20',
            'meeting_link' => 'nullable|url|max:500',
            'students_per_day' => 'required|integer|min:1'
        ]);

        $ids = $request->milestone_ids;
        $currentDate = \Carbon\Carbon::parse($request->start_date);
        $count = 0;

        $hour = 10;
        $minute = 0;
        if ($request->filled('start_time')) {
            $timeParts = explode(':', $request->start_time);
            if (count($timeParts) >= 2) {
                $hour = (int)$timeParts[0];
                $minute = (int)$timeParts[1];
            }
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $milestone = \App\Models\StudentMilestone::findOrFail($id);
                $thesis = $milestone->thesis;
                $template = $milestone->template;
                
                if (in_array('Supervisor', $template->required_approvers ?? []) && !$milestone->is_supervisor_approved) {
                    continue; // Skip if supervisor hasn't approved
                }
                
                $updateData = [
                    'defence_date' => $currentDate->format('Y-m-d')
                ];
                if ($request->filled('start_time')) {
                    $updateData['defence_time'] = $request->start_time;
                }
                if ($request->filled('meeting_link')) {
                    $updateData['meeting_link'] = $request->meeting_link;
                }
                $milestone->update($updateData);

                $eventData = [
                    'schedule_start' => $currentDate->copy()->setHour($hour)->setMinute($minute),
                    'schedule_end' => $currentDate->copy()->setHour($hour + 1)->setMinute($minute),
                ];
                if ($request->filled('meeting_link')) {
                    $eventData['location'] = $request->meeting_link;
                }

                $event = \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? 'first_seminar',
                    ],
                    $eventData
                );
                
                if ($thesis->student && $thesis->student->user) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $thesis->student->user->id);
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
            return back()->with('success', 'Scheduled ' . $count . ' presentation(s) successfully with date, time, and Zoom link.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error scheduling: ' . $e->getMessage());
        }
    }

    public function updateSchedule(Request $request, MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can edit presentation schedules.');
        }

        $request->validate([
            'entries' => 'required|array',
            'entries.*.defence_date' => 'nullable|date',
            'entries.*.defence_time' => 'nullable|string|max:20',
            'entries.*.meeting_link' => 'nullable|url|max:500',
            'entries.*.remove' => 'nullable|boolean',
            'apply_time_all' => 'nullable|string|max:20',
            'apply_link_all' => 'nullable|url|max:500',
        ]);

        $type = $template->defence_type ?? 'first_seminar';
        $updated = 0;
        $removed = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($request->input('entries', []) as $smId => $entry) {
                $sm = \App\Models\StudentMilestone::with('thesis.student.user')
                    ->where('milestone_template_id', $template->id)
                    ->find($smId);
                if (!$sm) {
                    continue;
                }

                $thesis = $sm->thesis;
                $studentUser = $thesis?->student?->user;

                // Remove from schedule
                if (!empty($entry['remove'])) {
                    $sm->update(['defence_date' => null, 'defence_time' => null, 'meeting_link' => null]);
                    if ($thesis) {
                        \App\Models\DefenceEvent::where('thesis_project_id', $thesis->id)->where('type', $type)->delete();
                    }
                    if ($studentUser) {
                        \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                    }
                    $removed++;
                    continue;
                }

                $date = $entry['defence_date'] ?? null;
                if (!$date) {
                    continue; // date is required to remain scheduled
                }
                $time = $request->filled('apply_time_all') ? $request->apply_time_all : ($entry['defence_time'] ?? null);
                $link = $request->filled('apply_link_all') ? $request->apply_link_all : ($entry['meeting_link'] ?? null);

                $oldDate = $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->toDateString() : null;
                $oldTime = $sm->defence_time ? \Carbon\Carbon::parse($sm->defence_time)->format('H:i') : null;
                $newTime = $time ? \Carbon\Carbon::parse($time)->format('H:i') : null;
                $changed = $oldDate !== \Carbon\Carbon::parse($date)->toDateString()
                    || $oldTime !== $newTime
                    || ($sm->meeting_link ?: null) !== ($link ?: null);

                if (!$changed) {
                    continue;
                }

                $sm->update([
                    'defence_date' => \Carbon\Carbon::parse($date)->format('Y-m-d'),
                    'defence_time' => $time ?: null,
                    'meeting_link' => $link ?: null,
                ]);

                if ($thesis) {
                    $start = \Carbon\Carbon::parse($date);
                    if ($newTime) {
                        [$h, $m] = array_map('intval', explode(':', $newTime));
                        $start->setTime($h, $m);
                    } else {
                        $start->setTime(10, 0);
                    }

                    $event = \App\Models\DefenceEvent::updateOrCreate(
                        ['thesis_project_id' => $thesis->id, 'type' => $type],
                        [
                            'schedule_start' => $start,
                            'schedule_end' => $start->copy()->addHour(),
                            'location' => $link ?: null,
                        ]
                    );

                    if ($studentUser) {
                        \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                        try {
                            $studentUser->notify(new \App\Notifications\EventScheduled($event));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send EventScheduled notification: ' . $e->getMessage());
                        }
                    }
                }

                $updated++;
            }

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }

        $msg = "Schedule for {$template->name} updated: {$updated} student(s) rescheduled";
        if ($removed > 0) {
            $msg .= ", {$removed} removed from the schedule";
        }
        return back()->with('success', $msg . '.');
    }

    public function endSchedule(MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->get();

            foreach ($milestones as $sm) {
                // Update to approved so it counts as completed and disappears from active schedules
                $sm->update([
                    'status' => 'approved'
                ]);

                $studentUser = $sm->thesis?->student?->user;
                if ($studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.milestone-templates.index')->with('success', "Presentation session for {$template->name} has been marked as ended. The students have been approved and the active schedule has been cleared.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to end schedule: ' . $e->getMessage());
        }
    }

    public function cancelSchedule(MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can cancel presentation schedules.');
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $today = now()->toDateString();
            $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->get();

            $thesisIds = $milestones->pluck('thesis_project_id')->filter()->unique();

            foreach ($milestones as $sm) {
                $sm->update([
                    'defence_date' => null,
                    'defence_time' => null,
                    'meeting_link' => null,
                ]);

                $studentUser = $sm->thesis?->student?->user;
                if ($studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                }
            }

            $type = $template->defence_type ?? 'first_seminar';
            \App\Models\DefenceEvent::whereIn('thesis_project_id', $thesisIds)
                ->where('type', $type)
                ->delete();

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('admin.milestone-templates.index')->with('success', "Presentation schedule for {$template->name} has been cancelled successfully. All assigned dates, times, and Zoom links have been cleared.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    public function assignExaminer(Request $request, $milestoneId)
    {
        $request->validate([
            'supervisor_profile_id' => 'required|exists:supervisor_profiles,id'
        ]);

        $milestone = \App\Models\StudentMilestone::findOrFail($milestoneId);
        $supervisor = \App\Models\SupervisorProfile::with(['user', 'programs'])->findOrFail($request->supervisor_profile_id);
        $template = $milestone->template;

        $event = \App\Models\DefenceEvent::firstOrCreate(
            [
                'thesis_project_id' => $milestone->thesis_project_id,
                'type' => $template->defence_type ?? 'first_seminar',
            ],
            [
                'schedule_start' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(9) : now()->addDays(7),
                'schedule_end' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(10) : now()->addDays(7)->addHour(),
            ]
        );

        \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'examiner')->delete();

        \App\Models\PanelMember::create([
            'defence_event_id' => $event->id,
            'user_id' => $supervisor->user_id,
            'role' => 'examiner',
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
        $supervisors = \App\Models\SupervisorProfile::with(['user', 'programs'])->whereIn('id', $request->supervisor_profile_ids)->get();

        $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->whereNotNull('defence_date')
            ->with('thesis')
            ->get();

        $assignedCount = 0;

        foreach ($milestones as $milestone) {
            $event = \App\Models\DefenceEvent::firstOrCreate(
                [
                    'thesis_project_id' => $milestone->thesis_project_id,
                    'type' => $template->defence_type ?? 'first_seminar',
                ],
                [
                    'schedule_start' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(9) : now()->addDays(7),
                    'schedule_end' => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(10) : now()->addDays(7)->addHour(),
                ]
            );

            \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'examiner')->delete();

            foreach ($supervisors as $supervisor) {
                \App\Models\PanelMember::create([
                    'defence_event_id' => $event->id,
                    'user_id' => $supervisor->user_id,
                    'role' => 'examiner',
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
                    $event = current($milestone->thesis->defenceEvents->where('type', $template->defence_type ?? 'first_seminar')->all());
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
                    $milestone->thesis->student->student_id_number ?? '',
                    $statusLabel,
                    $milestone->defence_date ?? 'Not Scheduled',
                    $avgScore
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

        public function exportScheduledScores(MilestoneTemplate $template)
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
            ->whereNotNull('defence_date')
            ->with(['thesis.student.user', 'thesis.defenceEvents.evaluations', 'submissions.feedback']);
            
        if ($isCoordinator) {
            $query->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                $sq->where('program_id', $coordinatorProgramId);
            });
        }
        
        $milestones = $query->get();
            
        $fileName = 'scheduled_scores_' . $template->slug . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Student Name', 'Matric Number', 'Presentation Date', 'Average Score'];

        $callback = function() use($milestones, $columns, $template) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($milestones as $milestone) {
                $avgScore = 'N/A';
                
                $event = current($milestone->thesis->defenceEvents->where('type', $template->defence_type ?? 'first_seminar')->all());
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

                fputcsv($file, [
                    $milestone->thesis->student->user->name ?? '',
                    $milestone->thesis->student->student_id_number ?? '',
                    $milestone->defence_date ?? 'Not Scheduled',
                    $avgScore
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExaminerAttendance(MilestoneTemplate $template)
    {
        $events = \App\Models\DefenceEvent::whereHas("thesis.milestones", function ($q) use ($template) {
                $q->where("milestone_template_id", $template->id);
            })
            ->with(["panelMembers.user", "evaluations"])
            ->get();
            
        $fileName = "examiner_attendance_" . $template->slug . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ["Examiner Name", "Examiner Email", "Days Present"];

        $callback = function() use($events, $columns) {
            $file = fopen("php://output", "w");
            fputcsv($file, $columns);

            $examinerStats = [];
            $today = \Carbon\Carbon::today()->endOfDay();

            foreach ($events as $event) {
                if (!$event->schedule_start || $event->schedule_start > $today) {
                    continue;
                }

                $dateStr = $event->schedule_start->format("Y-m-d");

                foreach ($event->panelMembers as $member) {
                    $userId = $member->user_id;
                    if (!isset($examinerStats[$userId])) {
                        $examinerStats[$userId] = [
                            "name" => $member->user->name ?? "Unknown",
                            "email" => $member->user->email ?? "Unknown",
                            "present_dates" => []
                        ];
                    }

                    $hasEvaluated = $event->evaluations->where("evaluator_id", $userId)->count() > 0;
                    if ($hasEvaluated) {
                        $examinerStats[$userId]["present_dates"][$dateStr] = true;
                    }
                }
            }

            foreach ($examinerStats as $stat) {
                fputcsv($file, [
                    $stat["name"],
                    $stat["email"],
                    count($stat["present_dates"])
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

