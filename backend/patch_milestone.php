<?php
$file = '/Users/sinan/Herd/Thesis Monotoring system/backend/app/Http/Controllers/Admin/MilestoneTemplateController.php';
$content = file_get_contents($file);

$methods = <<<'EOT'

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
                
                $milestone->update([
                    'defence_date' => $currentDate->format('Y-m-d')
                ]);

                \App\Models\DefenceEvent::updateOrCreate(
                    [
                        'thesis_project_id' => $thesis->id,
                        'type' => $template->defence_type ?? 'seminar',
                    ],
                    [
                        'schedule_start' => $currentDate->copy()->setHour(9)->setMinute(0),
                        'schedule_end' => $currentDate->copy()->setHour(10)->setMinute(0),
                    ]
                );

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

        return back()->with('success', 'Examiner assigned successfully.');
    }

    public function exportStudents(MilestoneTemplate $template)
    {
        $milestones = \App\Models\StudentMilestone::where('milestone_template_id', $template->id)
            ->whereIn('status', ['ongoing', 'pending_submission', 'pending_review', 'needs_revision', 'pending_defence'])
            ->with('thesisProject.student.user')
            ->get();
            
        $fileName = 'students_' . $template->slug . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Name', 'Matric Number', 'Status', 'Date Scheduled'];

        $callback = function() use($milestones, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($milestones as $milestone) {
                fputcsv($file, [
                    $milestone->thesisProject->student->user->name ?? '',
                    $milestone->thesisProject->student->matric_number ?? '',
                    $milestone->status,
                    $milestone->defence_date ?? 'Not Scheduled'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
EOT;

$content = preg_replace('/}[ \n]*$/', $methods, $content);
file_put_contents($file, $content);
