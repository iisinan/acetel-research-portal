<?php
$content = file_get_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php");

$method = <<<'PHP'
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
                    $milestone->thesis->student->matric_number ?? '',
                    $milestone->defence_date ?? 'Not Scheduled',
                    $avgScore
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
PHP;

$content = str_replace("public function exportExaminerAttendance", $method . "\n\n    public function exportExaminerAttendance", $content);
file_put_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php", $content);
