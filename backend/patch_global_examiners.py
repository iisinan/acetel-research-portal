import re

path = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php'
with open(path, 'r') as f:
    content = f.read()

old_func = r"""    public function assignExaminerGlobal(Request $request, $templateId)
    {
        $request->validate([
            'supervisor_profile_id' => 'required|exists:supervisor_profiles,id'
        ]);

        $template = MilestoneTemplate::findOrFail($templateId);
        $supervisor = \App\Models\SupervisorProfile::with('user')->findOrFail($request->supervisor_profile_id);

        $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
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

            \App\Models\PanelMember::create([
                'defence_event_id' => $event->id,
                'user_id' => $supervisor->user_id,
                'role' => 'Examiner',
                'invitation_status' => 'accepted'
            ]);

            $assignedCount++;
        }

        // Notify the examiner once
        $supervisor->user->notify(new \App\Notifications\EventScheduled(
            \App\Models\DefenceEvent::where('type', $template->defence_type ?? 'seminar')
                ->latest()
                ->first()
        ));

        return back()->with('success', "Examiner {$supervisor->user->name} assigned to all {$assignedCount} students successfully.");
    }"""

new_func = r"""    public function assignExaminerGlobal(Request $request, $templateId)
    {
        $request->validate([
            'supervisor_profile_ids' => 'required|array',
            'supervisor_profile_ids.*' => 'exists:supervisor_profiles,id'
        ]);

        $template = MilestoneTemplate::findOrFail($templateId);
        $supervisors = \App\Models\SupervisorProfile::with('user')->whereIn('id', $request->supervisor_profile_ids)->get();

        $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['in_progress', 'submitted', 'revision_required'])
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
        foreach ($supervisors as $supervisor) {
            $supervisor->user->notify(new \App\Notifications\EventScheduled(
                \App\Models\DefenceEvent::where('type', $template->defence_type ?? 'seminar')
                    ->latest()
                    ->first()
            ));
        }
        
        $names = $supervisors->map(fn($s) => $s->user->name)->implode(', ');
        return back()->with('success', "Examiners ($names) assigned to all {$assignedCount} students successfully.");
    }"""

content = content.replace(old_func, new_func)

with open(path, 'w') as f:
    f.write(content)
