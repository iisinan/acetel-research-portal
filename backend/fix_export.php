<?php
$content = file_get_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php");

$pattern = '/public function exportExaminerAttendance.*?return response\(\)->stream\(\$callback, 200, \$headers\);\s*}/s';

$new_export = 'public function exportExaminerAttendance(MilestoneTemplate $template)
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
    }';

$content = preg_replace($pattern, $new_export, $content);
file_put_contents("app/Http/Controllers/Admin/MilestoneTemplateController.php", $content);
