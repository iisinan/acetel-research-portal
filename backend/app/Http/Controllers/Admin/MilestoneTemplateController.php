<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MilestoneTemplate;
use App\Models\Program;
use Illuminate\Http\Request;
use App\Notifications\EventRescheduled;
use App\Notifications\ExaminerBatchScheduleRescheduled;
use App\Notifications\EventScheduled;

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
                  ->with([
                      'thesis.student.user', 
                      'thesis.student.cohort', 
                      'thesis.student.level',
                      'thesis.student.program',
                      'thesis.defenceEvents.panelMembers.user', 
                      'thesis.defenceEvents.evaluations', 
                      'thesis.milestones:id,thesis_project_id,milestone_template_id',
                      'thesis.assignments.supervisor.user',
                      'submissions.feedback'
                  ]); 
                
                if ($isCoordinator) {
                    $q->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                        $sq->where('program_id', $coordinatorProgramId);
                    });
                }
            }])->orderBy('order')->get();
            
        // Preload examiners mapped by id to prevent N+1 queries in the Blade view
        $examinerUserIds = collect();
        foreach ($templates as $t) {
            if (!empty($t->metadata['group_examiners'])) {
                foreach ($t->metadata['group_examiners'] as $grp) {
                    if (!empty($grp['user_ids'])) {
                        $examinerUserIds = $examinerUserIds->merge($grp['user_ids']);
                    }
                }
            }
            if (!empty($t->metadata['examiner_user_ids'])) {
                $examinerUserIds = $examinerUserIds->merge($t->metadata['examiner_user_ids']);
            }
        }
        $examinerUsersMap = $examinerUserIds->isNotEmpty()
            ? \App\Models\User::whereIn('id', $examinerUserIds->unique()->filter())->get()->keyBy('id')
            : collect();

        $supervisors = \App\Models\SupervisorProfile::with(['user', 'programs'])->get();
        $cohorts = \App\Models\Cohort::orderBy('name')->get();
        $programs = Program::orderBy('name')->get();
        return view('admin.milestone-templates.index', compact('templates', 'supervisors', 'isCoordinator', 'cohorts', 'programs', 'examinerUsersMap'));
    }

    public function show($id = null)
    {
        return redirect()->route('admin.milestone-templates.index');
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
            'students_per_day' => 'required|integer|min:1',
            'active_days' => 'nullable|array',
            'active_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'excluded_dates' => 'nullable|array',
            'excluded_dates.*' => 'nullable|string',
            'degree_type' => 'nullable|string|in:all,PhD,MSc,phd,msc',
        ]);

        // Active days of the week (default to Monday-Friday if not supplied or empty)
        $activeDays = $request->input('active_days');
        if (empty($activeDays) || !is_array($activeDays)) {
            $activeDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        } else {
            $activeDays = array_values(array_unique(array_map('strtolower', $activeDays)));
        }

        // Specific excluded dates (e.g. '2026-10-04', '2026-10-05')
        $excludedDates = [];
        if ($request->filled('excluded_dates')) {
            $rawExcluded = is_array($request->excluded_dates) ? $request->excluded_dates : explode(',', (string)$request->excluded_dates);
            foreach ($rawExcluded as $d) {
                $d = trim((string)$d);
                if ($d !== '') {
                    try {
                        $excludedDates[] = \Carbon\Carbon::parse($d)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        // ignore malformed date
                    }
                }
            }
            $excludedDates = array_values(array_unique($excludedDates));
        }

        $isDateValid = function(\Carbon\Carbon $date) use ($activeDays, $excludedDates) {
            $dayName = strtolower($date->format('l'));
            if (!in_array($dayName, $activeDays, true)) {
                return false;
            }
            if (in_array($date->format('Y-m-d'), $excludedDates, true)) {
                return false;
            }
            return true;
        };

        $advanceToValidDate = function(\Carbon\Carbon $date) use ($isDateValid) {
            $safety = 0;
            while (!$isDateValid($date) && $safety < 1000) {
                $date->addDay();
                $safety++;
            }
            return $date;
        };

        $ids = $request->milestone_ids;
        $currentDate = \Carbon\Carbon::parse($request->start_date);
        
        // Ensure starting date begins on a valid scheduled day
        $advanceToValidDate($currentDate);

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

        $targetDegree = strtolower((string)$request->input('degree_type', 'all'));

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $milestone = \App\Models\StudentMilestone::findOrFail($id);
                $thesis = $milestone->thesis;
                $template = $milestone->template;
                
                if (in_array('Supervisor', $template->required_approvers ?? []) && !$milestone->is_supervisor_approved) {
                    continue; // Skip if supervisor hasn't approved
                }

                // If filtering by degree type (PhD or MSc)
                if ($targetDegree !== 'all' && in_array($targetDegree, ['phd', 'msc'])) {
                    $student = $thesis?->student;
                    $rawDegree = $student?->level?->name 
                        ?? $student?->program?->degree_type 
                        ?? ($student?->program?->name ?? '');
                    $isPhd = str_contains(strtolower($rawDegree), 'phd') || str_contains(strtolower($rawDegree), 'doctor');
                    $studentDegree = $isPhd ? 'phd' : 'msc';
                    if ($studentDegree !== $targetDegree) {
                        continue;
                    }
                }
                
                $slotIndexOnDay = $count % $request->students_per_day;
                $slotDuration = 30; // 30 minutes interval per presenter
                $slotStartTime = $currentDate->copy()->setHour($hour)->setMinute($minute)->addMinutes($slotIndexOnDay * $slotDuration);
                $slotEndTime = $slotStartTime->copy()->addMinutes($slotDuration);
                $slotTimeStr = $slotStartTime->format('H:i');

                $updateData = [
                    'defence_date' => $currentDate->format('Y-m-d'),
                    'defence_time' => $slotTimeStr,
                ];
                if ($request->filled('meeting_link')) {
                    $updateData['meeting_link'] = $request->meeting_link;
                }
                $milestone->update($updateData);

                $eventData = [
                    'schedule_start' => $slotStartTime,
                    'schedule_end' => $slotEndTime,
                    'outcome' => 'pending',
                ];
                if ($request->filled('meeting_link')) {
                    $eventData['location'] = $request->meeting_link;
                }

                $event = \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? match($template->slug) {
                            'seminar_as_a_course' => 'seminar',
                            'proposal_defence' => 'proposal',
                            'progress_report_1' => 'progress_report_1',
                            'progress_report_2' => 'progress_report_2',
                            'internal_defence' => 'internal',
                            'viva' => 'external',
                            default => 'first_seminar',
                        },
                    ],
                    $eventData
                );

                // If milestone was previously failed (repeated), reset old evaluations and attendance for the fresh attempt
                if (in_array($event->outcome, ['fail', 'failed']) || $milestone->status === 'revision_required') {
                    \App\Models\Evaluation::where('defence_event_id', $event->id)->delete();
                    \App\Models\PanelMember::where('defence_event_id', $event->id)->update(['is_present' => false]);
                    $event->update(['outcome' => 'pending']);
                }

                // Auto-attach examiners: check track/group-specific first, then global
                // Institutional rule: Seminar as a Course is track-exclusive and must NEVER fall back to legacy global examiners.
                $discGroup = $milestone->discipline_group;
                $groupExaminers = $template->metadata['group_examiners'][$discGroup]['user_ids'] ?? null;
                $examinersToAttach = !empty($groupExaminers) 
                    ? $groupExaminers 
                    : ($template->slug === 'seminar_as_a_course' ? [] : ($template->metadata['examiner_user_ids'] ?? []));

                if ($event->panelMembers()->where('role', 'examiner')->count() === 0 && !empty($examinersToAttach)) {
                    foreach ($examinersToAttach as $uId) {
                        \App\Models\PanelMember::firstOrCreate([
                            'defence_event_id' => $event->id,
                            'user_id' => $uId,
                            'role' => 'examiner',
                        ], [
                            'invitation_status' => 'accepted'
                        ]);
                    }
                }
                
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
                    $advanceToValidDate($currentDate);
                }
            }
            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', 'Scheduled ' . $count . ' presentation(s) successfully with date, time, and Zoom link.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error scheduling: ' . $e->getMessage());
        }
    }

    /**
     * Resolve all unique examiner User models assigned to evaluate this milestone presentation.
     */
    protected function getExaminersForMilestone(\App\Models\StudentMilestone $sm, ?MilestoneTemplate $template = null, ?\App\Models\DefenceEvent $event = null): \Illuminate\Support\Collection
    {
        $template = $template ?? $sm->template;
        $thesis = $sm->thesis;
        $studentUserId = $thesis?->student?->user_id;

        $examiners = collect();

        // 1. From DefenceEvent panel members
        if ($event) {
            $panelUsers = $event->panelMembers()
                ->where('role', 'examiner')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter();
            $examiners = $examiners->merge($panelUsers);
        }

        // 2. From Template metadata (discipline group or template-level)
        if ($template) {
            $discGroup = $sm->discipline_group;
            $groupExaminerIds = $template->metadata['group_examiners'][$discGroup]['user_ids'] ?? null;
            $templateExaminerIds = !empty($groupExaminerIds)
                ? $groupExaminerIds
                : ($template->slug === 'seminar_as_a_course' ? [] : ($template->metadata['examiner_user_ids'] ?? []));

            if (!empty($templateExaminerIds)) {
                $tplUsers = \App\Models\User::whereIn('id', $templateExaminerIds)->get();
                $examiners = $examiners->merge($tplUsers);

                // Auto-attach to panelMembers for this event if not already present
                if ($event) {
                    foreach ($templateExaminerIds as $uId) {
                        \App\Models\PanelMember::firstOrCreate([
                            'defence_event_id' => $event->id,
                            'user_id' => $uId,
                            'role' => 'examiner',
                        ], [
                            'invitation_status' => 'accepted'
                        ]);
                    }
                }
            }
        }

        // 3. From Thesis assigned internal / external examiners
        if ($thesis) {
            if ($thesis->internalExaminer && $thesis->internalExaminer->user) {
                $examiners->push($thesis->internalExaminer->user);
            }
            if ($thesis->externalExaminer && $thesis->externalExaminer->user) {
                $examiners->push($thesis->externalExaminer->user);
            }
        }

        return $examiners
            ->filter(fn($u) => $u && $u->id !== $studentUserId && !empty($u->email))
            ->unique('id')
            ->values();
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

        $type = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };
        $updated = 0;
        $removed = 0;
        $examinerRescheduledMap = [];
        $examinerScheduledMap = [];

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($request->input('entries', []) as $smId => $entry) {
                $sm = \App\Models\StudentMilestone::with(['thesis.student.user', 'thesis.student.program', 'thesis.internalExaminer.user', 'thesis.externalExaminer.user', 'template'])
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

                $isRescheduled = !empty($oldDate);

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
                            if ($isRescheduled) {
                                $studentUser->notify(new EventRescheduled(
                                    $event,
                                    $oldDate,
                                    $oldTime,
                                    $template->name
                                ));
                            } else {
                                $studentUser->notify(new EventScheduled($event));
                            }
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send schedule notification: ' . $e->getMessage());
                        }
                    }

                    // Collect examiners for notification
                    $examiners = $this->getExaminersForMilestone($sm, $template, $event);
                    if ($isRescheduled) {
                        $prevSlotStr = $oldDate ? \Carbon\Carbon::parse($oldDate)->format('M d, Y') . ($oldTime ? ' at ' . \Carbon\Carbon::parse($oldTime)->format('g:i A') : '') : 'Not set';
                        $newSlotStr = \Carbon\Carbon::parse($date)->format('M d, Y') . ($newTime ? ' at ' . \Carbon\Carbon::parse($newTime)->format('g:i A') : '');

                        foreach ($examiners as $ex) {
                            $examinerRescheduledMap[$ex->id]['user'] = $ex;
                            $examinerRescheduledMap[$ex->id]['items'][] = [
                                'candidate_name' => $studentUser?->name ?? 'Candidate',
                                'student_id' => $sm->thesis?->student?->student_id_number ?? 'N/A',
                                'program' => $sm->thesis?->student?->program?->code ?? ($sm->thesis?->student?->program?->name ?? 'N/A'),
                                'previous_slot' => $prevSlotStr,
                                'new_slot' => $newSlotStr,
                                'meeting_link' => $link ?: ($sm->meeting_link ?: 'Online / Portal'),
                                'event' => $event,
                                'previous_date' => $oldDate,
                                'previous_time' => $oldTime,
                            ];
                        }
                    } else {
                        foreach ($examiners as $ex) {
                            $examinerScheduledMap[$ex->id]['user'] = $ex;
                            $examinerScheduledMap[$ex->id]['events'][] = $event;
                        }
                    }
                }

                $updated++;
            }

            if ($template->slug === 'seminar_as_a_course') {
                $meta = $template->metadata ?? [];
                $trackLinks = $meta['track_zoom_links'] ?? [];
                $activeSms = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                    ->whereNotNull('defence_date')
                    ->with('thesis.student.program')
                    ->get();
                foreach (['cybersecurity', 'artificial_intelligence', 'management_information_systems'] as $grp) {
                    $foundLink = $activeSms->first(fn($m) => $m->discipline_group === $grp && !empty($m->meeting_link))?->meeting_link;
                    if ($foundLink) {
                        $trackLinks[$grp] = $foundLink;
                    }
                }
                $meta['track_zoom_links'] = $trackLinks;
                $template->update(['metadata' => $meta]);
            }

            \Illuminate\Support\Facades\DB::commit();

            // Notify examiners who have rescheduled candidates
            foreach ($examinerRescheduledMap as $exId => $data) {
                try {
                    $exUser = $data['user'];
                    $items = $data['items'];
                    if (count($items) === 1) {
                        $first = $items[0];
                        $exUser->notify(new EventRescheduled(
                            $first['event'],
                            $first['previous_date'],
                            $first['previous_time'],
                            $template->name
                        ));
                    } else {
                        $exUser->notify(new ExaminerBatchScheduleRescheduled(
                            $template,
                            $items,
                            $template->name
                        ));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify examiner {$exId} on batch reschedule: " . $e->getMessage());
                }
            }

            // Notify examiners of newly scheduled presentations
            foreach ($examinerScheduledMap as $exId => $data) {
                try {
                    $exUser = $data['user'];
                    foreach ($data['events'] as $ev) {
                        $exUser->notify(new EventScheduled($ev));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify examiner {$exId} on initial schedule: " . $e->getMessage());
                }
            }

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

    public function rescheduleDay(Request $request, MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can reschedule presentation days.');
        }

        $request->validate([
            'from_date' => 'nullable|string',
            'to_date' => 'required|date',
            'start_time' => 'nullable|string|max:20',
            'meeting_link' => 'nullable|url|max:500',
            'discipline_group' => 'nullable|string|in:cybersecurity,artificial_intelligence,management_information_systems',
        ]);

        $isAllDates = ($request->from_date === 'all' || empty($request->from_date));
        $toDate = \Carbon\Carbon::parse($request->to_date);
        $toDateStr = $toDate->format('Y-m-d');
        $fromDateStr = !$isAllDates ? \Carbon\Carbon::parse($request->from_date)->format('Y-m-d') : null;

        $query = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereNotNull('defence_date')
            ->where('status', '!=', 'approved')
            ->with(['thesis.student.user', 'thesis.student.program']);

        if (!$isAllDates) {
            $query->whereDate('defence_date', $fromDateStr);
        }

        $milestones = $query->get();

        if ($request->filled('discipline_group')) {
            $milestones = $milestones->filter(fn($m) => $m->discipline_group === $request->discipline_group);
        }

        if ($milestones->isEmpty()) {
            $targetDesc = $fromDateStr ? \Carbon\Carbon::parse($fromDateStr)->format('M d, Y') : 'the selected filter';
            return back()->with('error', "No presentations found scheduled on {$targetDesc}.");
        }

        $type = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };

        $count = 0;
        $examinerRescheduledMap = [];
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // If track meeting link is provided, also sync it to template group_examiners metadata
            if ($request->filled('meeting_link') && $request->filled('discipline_group') && $template->slug === 'seminar_as_a_course') {
                $meta = $template->metadata ?? [];
                if (isset($meta['group_examiners'][$request->discipline_group])) {
                    $meta['group_examiners'][$request->discipline_group]['meeting_link'] = $request->meeting_link;
                }
                if (isset($meta['track_zoom_links'][$request->discipline_group])) {
                    $meta['track_zoom_links'][$request->discipline_group] = $request->meeting_link;
                }
                $template->metadata = $meta;
                $template->save();
            }

            foreach ($milestones as $sm) {
                $thesis = $sm->thesis;
                $studentUser = $thesis?->student?->user;
                $previousDate = $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d') : ($fromDateStr ?? $toDateStr);
                $previousTime = $sm->defence_time;

                $updateData = ['defence_date' => $toDateStr];
                if ($request->filled('start_time')) {
                    $updateData['defence_time'] = $request->start_time;
                }
                if ($request->filled('meeting_link')) {
                    $updateData['meeting_link'] = $request->meeting_link;
                }
                $sm->update($updateData);

                if ($thesis) {
                    $time = $sm->defence_time;
                    $start = $toDate->copy();
                    if ($time) {
                        $timeParts = explode(':', $time);
                        $start->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
                    } else {
                        $start->setTime(10, 0);
                    }

                    $eventData = [
                        'schedule_start' => $start,
                        'schedule_end' => $start->copy()->addHour(),
                    ];
                    if ($sm->meeting_link) {
                        $eventData['location'] = $sm->meeting_link;
                    }

                    $event = \App\Models\DefenceEvent::updateOrCreate(
                        ['thesis_project_id' => $thesis->id, 'type' => $type],
                        $eventData
                    );

                    if ($studentUser) {
                        \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                        try {
                            $studentUser->notify(new EventRescheduled(
                                $event,
                                $previousDate,
                                $previousTime,
                                $template->name
                            ));
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::error('Reschedule notification failed: ' . $e->getMessage());
                        }
                    }

                    // Collect examiners for notification
                    $examiners = $this->getExaminersForMilestone($sm, $template, $event);
                    $prevSlotStr = \Carbon\Carbon::parse($previousDate)->format('M d, Y') . ($previousTime ? ' at ' . \Carbon\Carbon::parse($previousTime)->format('g:i A') : '');
                    $newSlotStr = $toDate->format('M d, Y') . ($sm->defence_time ? ' at ' . \Carbon\Carbon::parse($sm->defence_time)->format('g:i A') : '');

                    foreach ($examiners as $ex) {
                        $examinerRescheduledMap[$ex->id]['user'] = $ex;
                        $examinerRescheduledMap[$ex->id]['items'][] = [
                            'candidate_name' => $studentUser?->name ?? 'Candidate',
                            'student_id' => $sm->thesis?->student?->student_id_number ?? 'N/A',
                            'program' => $sm->thesis?->student?->program?->code ?? ($sm->thesis?->student?->program?->name ?? 'N/A'),
                            'previous_slot' => $prevSlotStr,
                            'new_slot' => $newSlotStr,
                            'meeting_link' => $sm->meeting_link ?: 'Online / Portal',
                            'event' => $event,
                            'previous_date' => $previousDate,
                            'previous_time' => $previousTime,
                        ];
                    }
                }
                $count++;
            }
            \Illuminate\Support\Facades\DB::commit();

            // Notify examiners who have rescheduled candidates
            foreach ($examinerRescheduledMap as $exId => $data) {
                try {
                    $exUser = $data['user'];
                    $items = $data['items'];
                    if (count($items) === 1) {
                        $first = $items[0];
                        $exUser->notify(new EventRescheduled(
                            $first['event'],
                            $first['previous_date'],
                            $first['previous_time'],
                            $template->name
                        ));
                    } else {
                        $exUser->notify(new ExaminerBatchScheduleRescheduled(
                            $template,
                            $items,
                            $template->name,
                            $request->discipline_group
                        ));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify examiner {$exId} on rescheduleDay: " . $e->getMessage());
                }
            }

            $trackLabel = $request->filled('discipline_group') ? match($request->discipline_group) {
                'cybersecurity' => ' (Cybersecurity Track)',
                'artificial_intelligence' => ' (AI Track)',
                'management_information_systems' => ' (MIS Track)',
                default => '',
            } : '';

            $msg = $isAllDates 
                ? "Rescheduled {$count} presenter(s){$trackLabel} to " . $toDate->format('M d, Y') . " successfully."
                : "Rescheduled {$count} presenter(s){$trackLabel} from " . \Carbon\Carbon::parse($request->from_date)->format('M d, Y') . " to " . $toDate->format('M d, Y') . " successfully.";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error rescheduling day: ' . $e->getMessage());
        }
    }

    public function updateStudentSlot(Request $request, MilestoneTemplate $template, \App\Models\StudentMilestone $milestone)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can edit student schedule slots.');
        }

        if ($milestone->milestone_template_id !== $template->id) {
            abort(404, 'Milestone does not belong to this template.');
        }

        $request->validate([
            'action' => 'required|in:schedule,remove',
            'defence_date' => 'required_if:action,schedule|nullable|date',
            'defence_time' => 'nullable|string|max:20',
            'meeting_link' => 'nullable|url|max:500',
            'discipline_group' => 'nullable|string|in:cybersecurity,artificial_intelligence,management_information_systems',
        ]);

        $thesis = $milestone->thesis;
        $studentUser = $thesis?->student?->user;
        $studentName = $studentUser?->name ?? 'Candidate';

        $type = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if ($request->input('action') === 'remove') {
                $milestone->update([
                    'defence_date' => null,
                    'defence_time' => null,
                    'meeting_link' => null,
                ]);

                if ($thesis) {
                    \App\Models\DefenceEvent::where('thesis_project_id', $thesis->id)->where('type', $type)->delete();
                }
                if ($studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                }

                \Illuminate\Support\Facades\DB::commit();
                \App\Models\MilestoneTemplate::clearPresentationCache();
                return back()->with('success', "Cancelled presentation for {$studentName} and removed them from the schedule.");
            }

            // Schedule or change date
            $previousDate = $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->format('Y-m-d') : null;
            $previousTime = $milestone->defence_time;
            $newDate = \Carbon\Carbon::parse($request->defence_date)->format('Y-m-d');
            $newTime = $request->defence_time ?: null;
            $newLink = $request->meeting_link ?: null;
            $isMoved = !empty($previousDate) && ($previousDate !== $newDate || $previousTime !== $newTime || ($milestone->meeting_link ?: null) !== $newLink);

            $updateData = [
                'defence_date' => $newDate,
                'defence_time' => $newTime,
                'meeting_link' => $newLink,
            ];
            $milestone->update($updateData);

            if ($thesis) {
                $start = \Carbon\Carbon::parse($newDate);
                if ($newTime) {
                    $parts = explode(':', $newTime);
                    $start->setTime((int)$parts[0], (int)($parts[1] ?? 0));
                } else {
                    $start->setTime(10, 0);
                }

                $event = \App\Models\DefenceEvent::updateOrCreate(
                    ['thesis_project_id' => $thesis->id, 'type' => $type],
                    [
                        'schedule_start' => $start,
                        'schedule_end' => $start->copy()->addHour(),
                        'location' => $newLink,
                        'outcome' => 'pending',
                    ]
                );

                // Auto-attach template examiners if needed (track-specific for seminar course)
                $singleDiscGroup = $milestone->discipline_group;
                $singleGroupExaminers = $template->metadata['group_examiners'][$singleDiscGroup]['user_ids'] ?? null;
                $singleExaminersToAttach = !empty($singleGroupExaminers)
                    ? $singleGroupExaminers
                    : ($template->slug === 'seminar_as_a_course' ? [] : ($template->metadata['examiner_user_ids'] ?? []));

                if ($event->panelMembers()->where('role', 'examiner')->count() === 0 && !empty($singleExaminersToAttach)) {
                    foreach ($singleExaminersToAttach as $uId) {
                        \App\Models\PanelMember::firstOrCreate([
                            'defence_event_id' => $event->id,
                            'user_id' => $uId,
                            'role' => 'examiner',
                        ], [
                            'invitation_status' => 'accepted'
                        ]);
                    }
                }

                if ($studentUser) {
                    \Illuminate\Support\Facades\Cache::forget('user_thesis_' . $studentUser->id);
                    try {
                        if ($isMoved) {
                            $studentUser->notify(new EventRescheduled(
                                $event,
                                $previousDate,
                                $previousTime,
                                $template->name
                            ));
                        } else {
                            $studentUser->notify(new EventScheduled($event));
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error('Event notification error: ' . $e->getMessage());
                    }
                }

                // Notify assigned examiners as well
                $examiners = $this->getExaminersForMilestone($milestone, $template, $event);
                foreach ($examiners as $ex) {
                    try {
                        if ($isMoved) {
                            $ex->notify(new EventRescheduled(
                                $event,
                                $previousDate,
                                $previousTime,
                                $template->name
                            ));
                        } else {
                            $ex->notify(new EventScheduled($event));
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to notify examiner {$ex->id} in updateStudentSlot: " . $e->getMessage());
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            \App\Models\MilestoneTemplate::clearPresentationCache();
            return back()->with('success', "Updated presentation schedule for {$studentName} to " . \Carbon\Carbon::parse($newDate)->format('M d, Y') . ($newTime ? " at " . \Carbon\Carbon::parse($newTime)->format('g:i A') : "") . ".");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error updating student slot: ' . $e->getMessage());
        }
    }

    public function endSchedule(Request $request, MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can end presentation sessions.');
        }

        $discGroup = $request->input('discipline_group');

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->with(['template', 'thesis.student.user', 'thesis.student.program']);

            $milestones = $query->get();

            if ($discGroup && in_array($discGroup, ['cybersecurity', 'artificial_intelligence', 'management_information_systems'])) {
                $milestones = $milestones->filter(fn($m) => $m->discipline_group === $discGroup);
            }

            $workflow = new \App\Services\MilestoneWorkflowService();
            $advanced = 0;
            $skipped = [];

            foreach ($milestones as $sm) {
                $reason = $workflow->getEndSessionBlockReason($sm);
                if ($reason) {
                    $skipped[] = ($sm->thesis?->student?->user?->name ?? 'Unknown student') . ' — ' . $reason;
                    continue;
                }

                $gradingOutcome = $workflow->getAverageGradingOutcome($sm);
                if ($gradingOutcome === 'fail') {
                    $workflow->failAndRepeatMilestone($sm, "Average examination grade was FAIL. Candidate must repeat milestone.");
                    $skipped[] = ($sm->thesis?->student?->user?->name ?? 'Unknown student') . ' — Failed grading (marked for milestone repetition)';
                    continue;
                }

                $workflow->approveAndAdvance($sm, "Presentation session ended for {$template->name}.");
                $advanced++;
            }

            \Illuminate\Support\Facades\DB::commit();
            \App\Models\MilestoneTemplate::clearPresentationCache();

            $trackLabel = match($discGroup) {
                'cybersecurity' => ' (Cybersecurity Track)',
                'artificial_intelligence' => ' (AI Track)',
                'management_information_systems' => ' (MIS Track)',
                default => '',
            };
            $sessionTitle = "{$template->name}{$trackLabel}";

            if ($advanced === 0) {
                return redirect()->back()
                    ->with('error', "No student was advanced from {$sessionTitle}. None of the scheduled students met the requirements.")
                    ->with('skipped_students', $skipped);
            }

            $msg = "Presentation session for {$sessionTitle} ended: {$advanced} student(s) advanced to the next milestone.";
            if (count($skipped) > 0) {
                $msg .= ' ' . count($skipped) . ' student(s) remain scheduled or were marked to repeat because of requirements or grading.';
            }

            if ($discGroup) {
                return redirect()->back()
                    ->with('success', $msg)
                    ->with('skipped_students', $skipped);
            }

            if ($template->slug === 'seminar_as_a_course') {
                // Set a flag to trigger the auto-download in the past presentations view
                return redirect()->route('admin.past-presentations.index')
                    ->with('success', $msg)
                    ->with('skipped_students', $skipped)
                    ->with('auto_download_scores', route('admin.past-presentations.export-scores'))
                    ->with('auto_download_attendance', route('admin.past-presentations.export-attendance'));
            }

            return redirect()->route('admin.milestone-templates.index')
                ->with('success', $msg)
                ->with('skipped_students', $skipped);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to end schedule: ' . $e->getMessage());
        }
    }

    public function cancelSchedule(Request $request, MilestoneTemplate $template)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Institutional authority required. Only an Administrator can cancel presentation schedules.');
        }

        $discGroup = $request->input('discipline_group');
        if ($discGroup === 'all' || empty($discGroup)) {
            $discGroup = null;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $query = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereNotNull('defence_date')
                ->where('status', '!=', 'approved')
                ->with(['thesis.student.user', 'thesis.student.program']);

            $scheduledMilestones = $query->get();

            if ($discGroup && in_array($discGroup, ['cybersecurity', 'artificial_intelligence', 'management_information_systems'])) {
                $scheduledMilestones = $scheduledMilestones->filter(fn($m) => $m->discipline_group === $discGroup);
            }

            if ($scheduledMilestones->isEmpty()) {
                return back()->with('info', 'No scheduled presentations found to cancel.');
            }

            $thesisIds = $scheduledMilestones->pluck('thesis_project_id')->filter()->unique();

            foreach ($scheduledMilestones as $sm) {
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

            $type = $template->defence_type ?? match($template->slug) {
                'seminar_as_a_course' => 'seminar',
                'proposal_defence' => 'proposal',
                'progress_report_1' => 'progress_report_1',
                'progress_report_2' => 'progress_report_2',
                'internal_defence' => 'internal',
                'viva' => 'external',
                default => 'first_seminar',
            };

            if ($thesisIds->isNotEmpty()) {
                \App\Models\DefenceEvent::whereIn('thesis_project_id', $thesisIds)
                    ->where('type', $type)
                    ->delete();
            }

            // Only clear global examiner metadata if cancelling globally (not group-specific)
            if (!$discGroup) {
                $metadata = $template->metadata ?? [];
                if (isset($metadata['examiner_profile_ids'])) {
                    unset($metadata['examiner_profile_ids']);
                    $template->metadata = $metadata;
                    $template->save();
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            \App\Models\MilestoneTemplate::clearPresentationCache();

            $groupLabel = match($discGroup) {
                'cybersecurity' => 'Cybersecurity Track',
                'artificial_intelligence' => 'AI Track',
                'management_information_systems' => 'MIS Track',
                default => null,
            };

            $msg = $groupLabel
                ? "Presentation schedule for {$groupLabel} ({$template->name}) has been cancelled successfully. All assigned dates, times, and Zoom links for {$scheduledMilestones->count()} student(s) in this group have been cleared."
                : "Presentation schedule for {$template->name} has been cancelled successfully. All assigned dates, times, and Zoom links have been cleared.";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    public function assignExaminer(Request $request, $milestoneId)
    {
        $request->validate([
            'supervisor_profile_id' => 'nullable|exists:supervisor_profiles,id',
            'supervisor_profile_ids' => 'nullable|array',
            'supervisor_profile_ids.*' => 'exists:supervisor_profiles,id',
        ]);

        $milestone = $milestoneId instanceof \App\Models\StudentMilestone ? $milestoneId : \App\Models\StudentMilestone::findOrFail($milestoneId);
        $template = $milestone->template;

        $profileIds = $request->supervisor_profile_ids ?? ($request->supervisor_profile_id ? [$request->supervisor_profile_id] : []);
        $thesisId = $milestone->thesis_project_id ?? $milestone->thesis?->id;
        if (!$thesisId) {
            return back()->with('error', 'No linked research thesis project found for this milestone.');
        }

        $type = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };

        $candidateName = $milestone->thesis?->student?->user?->name ?? 'candidate';

        if (empty($profileIds)) {
            $event = \App\Models\DefenceEvent::where('thesis_project_id', $thesisId)->where('type', $type)->first();
            if ($event) {
                \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'examiner')->delete();
            }
            return back()->with('success', "Examiner assignments cleared for {$candidateName}.");
        }

        $supervisors = \App\Models\SupervisorProfile::with(['user', 'programs'])->whereIn('id', $profileIds)->get();

        $event = \App\Models\DefenceEvent::firstOrCreate(
            [
                'thesis_project_id' => $thesisId,
                'type' => $type,
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
            try {
                if ($supervisor->user && $template) {
                    $supervisor->user->notify(new \App\Notifications\ExaminerNominated($template, null, 1, $milestone->discipline_group));
                }
                $supervisor->user->notify(new \App\Notifications\EventScheduled($event));
            } catch (\Throwable $e) {}
        }

        $names = $supervisors->map(fn($s) => $s->user->name)->implode(', ');
        return back()->with('success', "Examiner(s) ({$names}) assigned successfully for {$candidateName}.");
    }

    public function assignExaminerGlobal(Request $request, $templateId)
    {
        $request->validate([
            'supervisor_profile_ids' => 'nullable|array',
            'supervisor_profile_ids.*' => 'exists:supervisor_profiles,id',
            'discipline_group' => 'nullable|string|in:cybersecurity,artificial_intelligence,management_information_systems',
            'custom_message' => 'nullable|string|max:1000'
        ]);

        $disciplineGroup = $request->input('discipline_group');
        $template = MilestoneTemplate::findOrFail($templateId);
        $profileIds = $request->input('supervisor_profile_ids', []);
        $supervisors = !empty($profileIds) 
            ? \App\Models\SupervisorProfile::with(['user', 'programs'])->whereIn('id', $profileIds)->get()
            : collect();

        $isCoordinator = auth()->user()->hasRole('Program Coordinator') && !auth()->user()->hasRole('Admin');
        
        $milestonesQuery = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->where('status', '!=', 'approved')
            ->where('status', '!=', 'withdrawn')
            ->with(['thesis.student.program', 'thesis.defenceEvents.panelMembers']);

        if ($isCoordinator) {
            $coordinatorProfile = auth()->user()->coordinatorProfiles()->where('active', true)->first();
            if ($coordinatorProfile && $coordinatorProfile->program_id) {
                $milestonesQuery->whereHas('thesis.student', fn($sq) => $sq->where('program_id', $coordinatorProfile->program_id));
            }
        }

        $milestones = $milestonesQuery->get();

        $type = $template->defence_type ?? match($template->slug) {
            'seminar_as_a_course' => 'seminar',
            'proposal_defence' => 'proposal',
            'progress_report_1' => 'progress_report_1',
            'progress_report_2' => 'progress_report_2',
            'internal_defence' => 'internal',
            'viva' => 'external',
            default => 'first_seminar',
        };

        // Track/Discipline Group specific examiner assignment
        if ($disciplineGroup) {
            $metadata = $template->metadata ?? [];
            $groupExaminers = $metadata['group_examiners'] ?? [];
            $groupLabel = match($disciplineGroup) {
                'cybersecurity' => 'Cybersecurity',
                'artificial_intelligence' => 'Artificial Intelligence',
                'management_information_systems' => 'Management Information Systems',
                default => ucfirst($disciplineGroup),
            };

            if ($supervisors->isEmpty()) {
                unset($groupExaminers[$disciplineGroup]);
                $metadata['group_examiners'] = $groupExaminers;
                $template->update(['metadata' => $metadata]);

                $clearedCount = 0;
                foreach ($milestones as $milestone) {
                    if ($milestone->discipline_group !== $disciplineGroup) continue;
                    $thesisId = $milestone->thesis_project_id ?? $milestone->thesis?->id;
                    if (!$thesisId) continue;

                    $event = \App\Models\DefenceEvent::where('thesis_project_id', $thesisId)->where('type', $type)->first();
                    if ($event) {
                        \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'examiner')->delete();
                        $clearedCount++;
                    }
                }
                return back()->with('success', "Examiner assignments cleared for the {$groupLabel} track in {$template->name}.");
            }

            $groupExaminers[$disciplineGroup] = [
                'profile_ids' => $supervisors->pluck('id')->values()->all(),
                'user_ids' => $supervisors->pluck('user_id')->values()->all(),
            ];
            $metadata['group_examiners'] = $groupExaminers;
            $template->update(['metadata' => $metadata]);

            $assignedCount = 0;
            foreach ($milestones as $milestone) {
                if ($milestone->discipline_group !== $disciplineGroup) continue;
                $thesisId = $milestone->thesis_project_id ?? $milestone->thesis?->id;
                if (!$thesisId) continue;

                $event = \App\Models\DefenceEvent::firstOrCreate(
                    [
                        'thesis_project_id' => $thesisId,
                        'type' => $type,
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

            try {
                foreach ($supervisors as $supervisor) {
                    $supervisor->user->notify(new \App\Notifications\ExaminerNominated($template, $request->custom_message, $assignedCount, $disciplineGroup));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Notification failed in assignExaminerGlobal: ' . $e->getMessage());
            }

            $names = $supervisors->map(fn($s) => $s->user->name)->implode(', ');
            return back()->with('success', "Examiner(s) ({$names}) assigned to {$assignedCount} candidate(s) in the {$groupLabel} track for {$template->name} and notified successfully.");
        }

        // If no supervisors selected, clear/unassign all examiners
        if ($supervisors->isEmpty()) {
            $metadata = $template->metadata ?? [];
            unset($metadata['examiner_profile_ids'], $metadata['examiner_user_ids']);
            $template->update(['metadata' => $metadata]);

            $clearedCount = 0;
            foreach ($milestones as $milestone) {
                $thesisId = $milestone->thesis_project_id ?? $milestone->thesis?->id;
                if (!$thesisId) continue;

                $event = \App\Models\DefenceEvent::where('thesis_project_id', $thesisId)->where('type', $type)->first();
                if ($event) {
                    \App\Models\PanelMember::where('defence_event_id', $event->id)->where('role', 'examiner')->delete();
                    $clearedCount++;
                }
            }

            return back()->with('success', "All examiner assignments removed successfully for {$template->name}.");
        }

        // 1. Store on template metadata permanently
        $metadata = $template->metadata ?? [];
        $metadata['examiner_profile_ids'] = $supervisors->pluck('id')->values()->all();
        $metadata['examiner_user_ids'] = $supervisors->pluck('user_id')->values()->all();
        $template->update(['metadata' => $metadata]);

        // 2. Assign to all active student milestones for this template
        $assignedCount = 0;
        foreach ($milestones as $milestone) {
            $thesisId = $milestone->thesis_project_id ?? $milestone->thesis?->id;
            if (!$thesisId) {
                continue;
            }

            $event = \App\Models\DefenceEvent::firstOrCreate(
                [
                    'thesis_project_id' => $thesisId,
                    'type' => $type,
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

        // 3. Notify the examiners once
        try {
            foreach ($supervisors as $supervisor) {
                $supervisor->user->notify(new \App\Notifications\ExaminerNominated($template, $request->custom_message, $assignedCount));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Notification failed in assignExaminerGlobal: ' . $e->getMessage());
        }
        
        $names = $supervisors->map(fn($s) => $s->user->name)->implode(', ');
        return back()->with('success', "Examiner(s) ({$names}) assigned to all {$assignedCount} candidate(s) for {$template->name} and notified successfully.");
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
                } elseif (in_array($template->slug, ['proposal_defence', 'progress_report_1', 'progress_report_2'])) {
                    $eventType = $template->defence_type ?? match($template->slug) {
                        'proposal_defence' => 'proposal',
                        'progress_report_1' => 'progress_report_1',
                        'progress_report_2' => 'progress_report_2',
                        'internal_defence' => 'internal',
                        'viva' => 'external',
                        default => null,
                    };
                    $event = $eventType ? current($milestone->thesis->defenceEvents->where('type', $eventType)->all()) : null;
                    if ($event && $event->evaluations->count() > 0) {
                        $passCount = 0;
                        $failCount = 0;
                        foreach($event->evaluations as $eval) {
                            $verdict = strtolower($eval->verdict ?? $eval->recommendation ?? '');
                            if ($verdict === 'pass') $passCount++;
                            elseif ($verdict === 'fail') $failCount++;
                        }
                        if ($passCount + $failCount > 0) {
                            $avgScore = ($passCount >= $failCount ? 'PASS' : 'FAIL') . " ({$passCount}/" . ($passCount + $failCount) . ")";
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

        $isSeminar = $template->slug === 'seminar_as_a_course';
        $isPassFail = in_array($template->slug, ['proposal_defence', 'progress_report_1', 'progress_report_2']);
        $columns = $isSeminar
            ? ['Student Name', 'Matric Number', 'Discipline Track', 'Presentation Date', 'Slot Time', 'Meeting Link', 'Average Score']
            : ($isPassFail
                ? ['Student Name', 'Matric Number', 'Presentation Date', 'Grade / Result']
                : ['Student Name', 'Matric Number', 'Presentation Date', 'Average Score']);

        $callback = function() use($milestones, $columns, $template, $isPassFail, $isSeminar) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $eventType = $template->defence_type ?? match($template->slug) {
                'seminar_as_a_course' => 'seminar',
                'proposal_defence' => 'proposal',
                'progress_report_1' => 'progress_report_1',
                'progress_report_2' => 'progress_report_2',
                'internal_defence' => 'internal',
                'viva' => 'external',
                default => 'first_seminar',
            };

            foreach ($milestones as $milestone) {
                $avgScore = 'N/A';
                
                $event = current($milestone->thesis->defenceEvents->where('type', $eventType)->all());
                if ($event && $event->evaluations->count() > 0) {
                    if ($isPassFail) {
                        $passCount = 0;
                        $failCount = 0;
                        foreach($event->evaluations as $eval) {
                            $verdict = strtolower($eval->verdict ?? $eval->recommendation ?? '');
                            if ($verdict === 'pass') $passCount++;
                            elseif ($verdict === 'fail') $failCount++;
                        }
                        if ($passCount + $failCount > 0) {
                            $avgScore = ($passCount >= $failCount ? 'PASS' : 'FAIL') . " ({$passCount}/" . ($passCount + $failCount) . ")";
                        }
                    } else {
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

                $row = [
                    $milestone->thesis->student->user->name ?? '',
                    $milestone->thesis->student->student_id_number ?? '',
                ];
                if ($isSeminar) {
                    $row[] = $milestone->discipline_group_label ?? 'General';
                }
                $row[] = $milestone->defence_date ?? 'Not Scheduled';
                if ($isSeminar) {
                    $row[] = $milestone->defence_time ?? 'N/A';
                    $row[] = $milestone->meeting_link ?? 'N/A';
                }
                $row[] = $avgScore;

                fputcsv($file, $row);
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

    public function exportScheduledPdf(MilestoneTemplate $template)
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $query = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereNotNull('defence_date')
            ->where('status', '!=', 'approved')
            ->with([
                'thesis.student.user',
                'thesis.student.program',
                'thesis.student.cohort'
            ]);

        if ($isCoordinator) {
            $query->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                $sq->where('program_id', $coordinatorProgramId);
            });
        }

        $milestones = $query->get();

        $selectedGroup = request()->query('discipline_group');
        if ($selectedGroup && in_array($selectedGroup, ['cybersecurity', 'artificial_intelligence', 'management_information_systems'])) {
            $milestones = $milestones->filter(fn($m) => $m->discipline_group === $selectedGroup)->values();
        }

        // Arranged according to their presentation date (ascending)
        $scheduledMilestones = $milestones->sortBy(function($m) {
            $date = $m->defence_date ? \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d') : '9999-12-31';
            $time = $m->defence_time ? \Carbon\Carbon::parse($m->defence_time)->format('H:i:s') : '00:00:00';
            return $date . ' ' . $time;
        })->values();

        $metadata = $template->metadata ?? [];
        $trackZoomLinks = $metadata['track_zoom_links'] ?? [];

        $trackDefs = [
            'cybersecurity' => [
                'name' => 'Cybersecurity',
                'badge' => 'Cybersecurity',
                'icon' => '🛡️',
                'color' => '#0e7490',
                'border_color' => '#0891b2',
                'bg_color' => '#f0fdfa',
                'header_bg' => '#0e7490',
            ],
            'artificial_intelligence' => [
                'name' => 'Artificial Intelligence',
                'badge' => 'Artificial Intelligence',
                'icon' => '🤖',
                'color' => '#6d28d9',
                'border_color' => '#7c3aed',
                'bg_color' => '#f5f3ff',
                'header_bg' => '#6d28d9',
            ],
            'management_information_systems' => [
                'name' => 'Management Information Systems',
                'badge' => 'Management Information Systems',
                'icon' => '📊',
                'color' => '#b45309',
                'border_color' => '#d97706',
                'bg_color' => '#fffbeb',
                'header_bg' => '#b45309',
            ],
            'other' => [
                'name' => 'General / Other Disciplines',
                'badge' => 'General',
                'icon' => '🎓',
                'color' => '#1e293b',
                'border_color' => '#334155',
                'bg_color' => '#f8fafc',
                'header_bg' => '#1e293b',
            ],
        ];

        // Organize into groups
        $groups = [];
        if ($template->slug === 'seminar_as_a_course') {
            $tracksToIterate = $selectedGroup ? [$selectedGroup] : ['cybersecurity', 'artificial_intelligence', 'management_information_systems'];
            
            $otherCount = $scheduledMilestones->where('discipline_group', 'other')->count();
            if ($otherCount > 0 && !$selectedGroup) {
                $tracksToIterate[] = 'other';
            }

            foreach ($tracksToIterate as $tKey) {
                $groupItems = $scheduledMilestones->filter(fn($m) => $m->discipline_group === $tKey)->values();
                if ($groupItems->isEmpty() && !$selectedGroup) {
                    continue;
                }
                $def = $trackDefs[$tKey] ?? $trackDefs['other'];
                $grpZoom = $trackZoomLinks[$tKey] ?? $groupItems->first(fn($m) => !empty($m->meeting_link))?->meeting_link;

                // Group examiners
                $examinerUserIds = $metadata['group_examiners'][$tKey]['user_ids'] ?? [];
                $examinerNames = !empty($examinerUserIds) 
                    ? \App\Models\User::whereIn('id', $examinerUserIds)->pluck('name')->all() 
                    : [];

                $groups[] = [
                    'key' => $tKey,
                    'name' => $def['name'],
                    'badge' => $def['badge'],
                    'icon' => $def['icon'],
                    'color' => $def['color'],
                    'border_color' => $def['border_color'],
                    'bg_color' => $def['bg_color'],
                    'header_bg' => $def['header_bg'],
                    'zoom_link' => $grpZoom,
                    'examiners' => $examinerNames,
                    'items' => $groupItems,
                    'count' => $groupItems->count(),
                    'start_date' => $groupItems->first()?->defence_date ? \Carbon\Carbon::parse($groupItems->first()->defence_date)->format('D, M d, Y') : 'N/A',
                    'end_date' => $groupItems->last()?->defence_date ? \Carbon\Carbon::parse($groupItems->last()->defence_date)->format('D, M d, Y') : 'N/A',
                ];
            }
        } else {
            $groups[] = [
                'key' => 'all',
                'name' => $template->name,
                'badge' => $template->name,
                'icon' => '🎓',
                'color' => '#065f46',
                'border_color' => '#047857',
                'bg_color' => '#f0fdf4',
                'header_bg' => '#065f46',
                'zoom_link' => $scheduledMilestones->first(fn($m) => !empty($m->meeting_link))?->meeting_link,
                'examiners' => !empty($metadata['examiner_user_ids']) 
                    ? \App\Models\User::whereIn('id', $metadata['examiner_user_ids'])->pluck('name')->all() 
                    : [],
                'items' => $scheduledMilestones,
                'count' => $scheduledMilestones->count(),
                'start_date' => $scheduledMilestones->first()?->defence_date ? \Carbon\Carbon::parse($scheduledMilestones->first()->defence_date)->format('D, M d, Y') : 'N/A',
                'end_date' => $scheduledMilestones->last()?->defence_date ? \Carbon\Carbon::parse($scheduledMilestones->last()->defence_date)->format('D, M d, Y') : 'N/A',
            ];
        }

        // Title
        if ($selectedGroup && isset($trackDefs[$selectedGroup])) {
            $title = 'Seminar presentation scheduled — ' . $trackDefs[$selectedGroup]['name'] . ' Group';
        } else {
            $title = ($template->slug === 'seminar_as_a_course') 
                ? 'Seminar presentation scheduled' 
                : ($template->name . ' Presentation Scheduled');
        }

        // Overall Starting Date & Ending Date
        $startingDate = $scheduledMilestones->first()?->defence_date 
            ? \Carbon\Carbon::parse($scheduledMilestones->first()->defence_date)->format('D, M d, Y') 
            : 'N/A';
        $endingDate = $scheduledMilestones->last()?->defence_date 
            ? \Carbon\Carbon::parse($scheduledMilestones->last()->defence_date)->format('D, M d, Y') 
            : 'N/A';

        // Cohorts
        $cohorts = $scheduledMilestones->map(function($m) {
            return $m->thesis?->student?->cohort?->name;
        })->filter()->unique()->values()->all();

        $cohortsDisplay = !empty($cohorts) ? implode(', ', $cohorts) : 'All Cohorts';

        $groupBreakdownParts = [];
        foreach ($groups as $g) {
            $groupBreakdownParts[] = $g['count'] . ' ' . $g['name'];
        }
        $groupBreakdown = implode(' • ', $groupBreakdownParts);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.scheduled-presentation', [
            'template' => $template,
            'title' => $title,
            'groups' => $groups,
            'scheduledMilestones' => $scheduledMilestones,
            'startingDate' => $startingDate,
            'endingDate' => $endingDate,
            'cohortsDisplay' => $cohortsDisplay,
            'groupBreakdown' => $groupBreakdown,
            'selectedGroup' => $selectedGroup,
            'generatedAt' => now()->format('M d, Y h:i A'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        $fileName = str_replace([' ', '—', '-'], '_', strtolower($title)) . '_' . now()->format('Ymd_His') . '.pdf';
        $fileName = preg_replace('/_+/', '_', $fileName);

        return $pdf->download($fileName);
    }

    /**

    /**
     * Broadcast an inbox message and/or email to students on this milestone.
     */
    public function broadcastMessage(Request $request, MilestoneTemplate $template)
    {
        $validated = $request->validate([
            'target' => 'required|in:selected,all,single',
            'student_milestone_id' => 'nullable|exists:student_milestones,id',
            'milestone_ids' => 'nullable|array',
            'milestone_ids.*' => 'exists:student_milestones,id',
            'channels' => 'required|array|min:1',
            'channels.*' => 'in:inbox,email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'priority' => 'nullable|in:normal,important,urgent',
        ]);

        $channels = $validated['channels'];
        $hasInbox = in_array('inbox', $channels);
        $hasEmail = in_array('email', $channels);
        $deliveryMethod = ($hasInbox && $hasEmail) ? 'both' : ($hasInbox ? 'in_app' : 'email');

        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator') && !$user->hasRole('Admin');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        // Fetch candidate student milestones
        $target = $validated['target'];
        if ($target === 'single') {
            if (empty($validated['student_milestone_id'])) {
                return back()->with('error', 'No candidate specified for single student message.');
            }
            $milestones = \App\Models\StudentMilestone::where('id', $validated['student_milestone_id'])
                ->with('thesis.student.user')
                ->get();
        } elseif ($target === 'selected') {
            $ids = $request->input('milestone_ids', []);
            if (empty($ids)) {
                return back()->with('error', 'No students were selected. Please select at least one student or choose to message all.');
            }
            $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereIn('id', $ids)
                ->with('thesis.student.user')
                ->get();
        } else {
            // Target is 'all'
            $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                ->whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
                ->with('thesis.student.user')
                ->get();
            
            // If empty under active statuses, fallback to all student milestones for this template
            if ($milestones->isEmpty()) {
                $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
                    ->with('thesis.student.user')
                    ->get();
            }
        }

        if ($isCoordinator && $coordinatorProgramId) {
            $milestones = $milestones->filter(function($sm) use ($coordinatorProgramId) {
                return $sm->thesis?->student?->program_id == $coordinatorProgramId;
            });
        }

        // Extract student User models
        $studentUsers = $milestones->map(function($sm) {
            return $sm->thesis?->student?->user;
        })->filter()->unique('id')->values();

        if ($studentUsers->isEmpty()) {
            return back()->with('error', 'No valid student user accounts were found for the chosen recipient selection.');
        }

        // Format subject with priority tag if needed
        $subject = trim($validated['subject']);
        $priority = $validated['priority'] ?? 'normal';
        if ($priority === 'urgent' && !str_starts_with(strtoupper($subject), '[URGENT]')) {
            $subject = '[URGENT] ' . $subject;
        } elseif ($priority === 'important' && !str_starts_with(strtoupper($subject), '[IMPORTANT]')) {
            $subject = '[IMPORTANT] ' . $subject;
        }

        // Create InboxMessage record
        $inboxMessage = \App\Models\InboxMessage::create([
            'sender_id' => $user->id,
            'subject' => $subject,
            'body' => $validated['body'],
            'delivery_method' => $deliveryMethod,
        ]);

        // Attach recipients in bulk
        $attachData = [];
        $recipientIds = [];
        foreach ($studentUsers as $stUser) {
            $attachData[$stUser->id] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'recipient_type' => 'to',
                'read_at' => null,
                'is_starred' => false,
                'is_archived' => false,
            ];
            $recipientIds[] = $stUser->id;
        }
        foreach (array_chunk($attachData, 500, true) as $chunk) {
            $inboxMessage->recipients()->attach($chunk);
        }

        // Dispatch notifications via ProcessMessageDelivery
        try {
            \App\Jobs\ProcessMessageDelivery::dispatch($inboxMessage, $recipientIds);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("ProcessMessageDelivery dispatch error: " . $e->getMessage());
        }

        // Construct descriptive success message
        $channelDesc = ($hasInbox && $hasEmail) ? 'System Inbox & Email' : ($hasInbox ? 'System Inbox' : 'Direct Email');
        $count = $studentUsers->count();
        $studentLabel = $count === 1 ? 'student candidate' : 'student candidates';

        return back()->with('success', "Dispatched message to {$count} {$studentLabel} on {$template->name} via {$channelDesc}.");
    }

    /**
     * Build base query for all students in ongoing milestones.
     */
    protected function getOngoingMilestonesQuery(Request $request)
    {
        $user = auth()->user();
        $isCoordinator = $user->hasRole('Program Coordinator');
        $coordinatorProgramId = null;

        if ($isCoordinator) {
            $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
            $coordinatorProgramId = $coordinatorProfile ? $coordinatorProfile->program_id : -1;
        }

        $query = \App\Models\StudentMilestone::whereIn('status', ['in_progress', 'submitted', 'revision_required', 'partially_approved'])
            ->with([
                'template',
                'thesis.student.user',
                'thesis.student.program',
                'thesis.student.level',
                'thesis.student.cohort',
                'thesis.assignments.supervisor.user',
                'thesis.internalExaminer.user',
                'submissions' => fn($q) => $q->latest(),
            ]);

        if ($isCoordinator) {
            $query->whereHas('thesis.student', function($sq) use ($coordinatorProgramId) {
                $sq->where('program_id', $coordinatorProgramId);
            });
        } elseif ($request->filled('program_id')) {
            $query->whereHas('thesis.student', function($sq) use ($request) {
                $sq->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('cohort_id')) {
            $query->whereHas('thesis.student', function($sq) use ($request) {
                $sq->where('cohort_id', $request->cohort_id);
            });
        }

        if ($request->filled('template_id')) {
            $query->where('milestone_template_id', $request->template_id);
        }

        return $query;
    }

    /**
     * Download all students ongoing milestone records as Excel (CSV).
     */
    public function exportOngoingExcel(Request $request)
    {
        $milestones = $this->getOngoingMilestonesQuery($request)->get();

        $sortedMilestones = $milestones->sortBy(function($m) {
            $order = $m->template?->order ?? 999;
            $program = $m->thesis?->student?->program?->code ?? '';
            $name = $m->thesis?->student?->user?->name ?? '';
            return sprintf('%04d-%s-%s', $order, $program, $name);
        })->values();

        $fileName = 'ongoing_students_milestones_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            '#',
            'Milestone Order',
            'Ongoing Milestone',
            'Student Name',
            'Matric Number',
            'Email',
            'Program',
            'Level',
            'Cohort',
            'Thesis Title',
            'Milestone Status',
            'Supervisor Approval',
            'Supervisor(s)',
            'Internal Examiner',
            'Scheduled Defence Date',
            'Defence Time',
            'Meeting Link / Venue',
            'Last Submission Date'
        ];

        $callback = function() use ($sortedMilestones, $columns) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens with proper encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($sortedMilestones as $idx => $m) {
                $student = $m->thesis?->student;
                $user = $student?->user;
                $supervisors = implode('; ', $m->thesis?->getSupervisorNames() ?? []);

                $statusText = match($m->status) {
                    'in_progress' => 'In Progress',
                    'submitted' => 'Submitted',
                    'revision_required' => 'Revision Required',
                    'partially_approved' => 'Partially Approved',
                    default => ucfirst(str_replace('_', ' ', $m->status))
                };

                fputcsv($file, [
                    $idx + 1,
                    $m->template?->order ?? 'N/A',
                    $m->template?->name ?? 'N/A',
                    $user?->name ?? 'N/A',
                    $student?->student_id_number ?? 'N/A',
                    $user?->email ?? 'N/A',
                    $student?->program?->code ?? ($student?->program?->name ?? 'N/A'),
                    $student?->level?->name ?? 'N/A',
                    $student?->cohort?->name ?? 'N/A',
                    $m->thesis?->title ?? 'Untitled Thesis',
                    $statusText,
                    $m->is_supervisor_approved ? 'Approved' : 'Pending',
                    $supervisors ?: 'None Assigned',
                    $m->thesis?->internalExaminer?->user?->name ?? 'None Assigned',
                    $m->defence_date ? \Carbon\Carbon::parse($m->defence_date)->format('Y-m-d') : 'Not Scheduled',
                    $m->defence_time ? \Carbon\Carbon::parse($m->defence_time)->format('h:i A') : 'N/A',
                    $m->meeting_link ?: ($m->defence_location ?: 'N/A'),
                    $m->submissions->first()?->created_at ? $m->submissions->first()->created_at->format('Y-m-d H:i') : 'No Submission'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download all students ongoing milestone records as formatted PDF report.
     */
    public function exportOngoingPdf(Request $request)
    {
        $milestones = $this->getOngoingMilestonesQuery($request)->get();

        $groupedMilestones = [];
        $byTemplate = $milestones->groupBy('milestone_template_id');

        $allTemplates = MilestoneTemplate::whereIn('id', $byTemplate->keys())->orderBy('order')->get();
        foreach ($allTemplates as $tpl) {
            $list = $byTemplate->get($tpl->id);
            if ($list && $list->count() > 0) {
                $sortedList = $list->sortBy(function($m) {
                    $prog = $m->thesis?->student?->program?->code ?? '';
                    $name = $m->thesis?->student?->user?->name ?? '';
                    return $prog . ' ' . $name;
                })->values();

                $groupedMilestones[] = [
                    'template' => $tpl,
                    'milestones' => $sortedList
                ];
            }
        }

        $programScope = 'All Programs';
        if ($request->filled('program_id')) {
            $p = Program::find($request->program_id);
            if ($p) $programScope = $p->name . ' (' . $p->code . ')';
        } elseif (auth()->user()->hasRole('Program Coordinator')) {
            $coord = auth()->user()->coordinatorProfiles()->where('active', true)->first();
            if ($coord && $coord->program) {
                $programScope = $coord->program->name . ' (' . $coord->program->code . ')';
            }
        }

        $cohortScope = 'All Cohorts';
        if ($request->filled('cohort_id')) {
            $c = \App\Models\Cohort::find($request->cohort_id);
            if ($c) $cohortScope = $c->name;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ongoing-milestones', [
            'title' => 'Students Ongoing Milestones Report',
            'groupedMilestones' => $groupedMilestones,
            'totalStudents' => $milestones->count(),
            'scheduledCount' => $milestones->whereNotNull('defence_date')->count(),
            'programScope' => $programScope,
            'cohortScope' => $cohortScope,
            'generatedAt' => now()->format('M d, Y h:i A'),
            'generatedBy' => auth()->user()->name ?? 'Administrator',
        ]);

        $pdf->setPaper('a4', 'landscape');

        $fileName = 'ongoing_students_report_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }
}
