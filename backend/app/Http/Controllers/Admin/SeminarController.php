<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentMilestone;
use App\Models\SupervisorProfile;
use App\Models\DefenceEvent;
use App\Models\PanelMember;
use Illuminate\Support\Facades\DB;

class SeminarController extends Controller
{
    public function index()
    {
        $milestones = StudentMilestone::whereHas("template", function($q) {
                $q->where("slug", "seminar_as_a_course");
            })
            ->with(["thesis.student.user", "submissions" => function($q) {
                $q->where("description", "LIKE", "%ppt%")->orWhere("description", "LIKE", "%presentation%")->orWhere("file_url", "LIKE", "%.ppt%")->orWhere("file_url", "LIKE", "%.pdf%");
            }, "thesis.defenceEvents" => function($q) {
                $q->where("type", "seminar")->with(["panelMembers.user", "evaluations"]);
            }])
            ->latest()
            ->paginate(50);
            
        $supervisors = SupervisorProfile::with("user")->get();

        return view("admin.seminars.index", compact("milestones", "supervisors"));
    }

    public function schedule(Request $request)
    {
        $request->validate([
            "milestone_ids" => "required|array",
            "start_date" => "required|date",
            "students_per_day" => "required|integer|min:1"
        ]);

        $ids = $request->milestone_ids;
        $currentDate = \Carbon\Carbon::parse($request->start_date);
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $milestone = StudentMilestone::findOrFail($id);
                $thesis = $milestone->thesis;
                
                $milestone->update([
                    "defence_date" => $currentDate->format("Y-m-d")
                ]);

                $event = DefenceEvent::updateOrCreate(
                    [
                        "thesis_project_id" => $thesis->id,
                        "type" => "seminar",
                    ],
                    [
                        "schedule_start" => $currentDate->copy()->setHour(9)->setMinute(0),
                        "schedule_end" => $currentDate->copy()->setHour(10)->setMinute(0),
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
            DB::commit();
            return back()->with("success", "Scheduled " . count($ids) . " seminars successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Error scheduling seminars: " . $e->getMessage());
        }
    }

    public function assignExaminer(Request $request, $milestoneId)
    {
        $request->validate([
            "supervisor_profile_ids" => "required|array",
            "supervisor_profile_ids.*" => "exists:supervisor_profiles,id"
        ]);

        $milestone = StudentMilestone::findOrFail($milestoneId);
        $supervisors = SupervisorProfile::with("user")->whereIn("id", $request->supervisor_profile_ids)->get();

        $event = DefenceEvent::firstOrCreate(
            [
                "thesis_project_id" => $milestone->thesis_project_id,
                "type" => "seminar",
            ],
            [
                "schedule_start" => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(9) : now()->addDays(7),
                "schedule_end" => $milestone->defence_date ? \Carbon\Carbon::parse($milestone->defence_date)->setHour(10) : now()->addDays(7)->addHour(),
            ]
        );

        PanelMember::where("defence_event_id", $event->id)->where("role", "Examiner")->delete();

        foreach ($supervisors as $supervisor) {
            PanelMember::create([
                "defence_event_id" => $event->id,
                "user_id" => $supervisor->user_id,
                "role" => "Examiner",
                "invitation_status" => "accepted"
            ]);
        }

        return back()->with("success", "Examiners assigned successfully.");
    }

    public function storeScore(Request $request, $eventId)
    {
        $request->validate([
            "score" => "required|numeric|min:0|max:100",
            "comments" => "nullable|string"
        ]);

        $event = DefenceEvent::findOrFail($eventId);
        
        if (now()->format("Y-m-d") !== $event->schedule_start->format("Y-m-d")) {
            return back()->with("error", "You can only grade the student on the exact day of their presentation.");
        }

        \App\Models\Evaluation::updateOrCreate(
            [
                "defence_event_id" => $event->id,
                "evaluator_id" => auth()->id()
            ],
            [
                "score" => ["total" => $request->score],
                "comments" => $request->comments,
                "submitted_at" => now(),
            ]
        );

        return back()->with("success", "Marks saved successfully.");
    }

    public function downloadAttendance()
    {
        $events = DefenceEvent::where("type", "seminar")
            ->with(["thesis.student.user", "panelMembers.user", "evaluations"])
            ->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=seminar_attendance_" . date("Y-m-d_H-i-s") . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use($events) {
            $file = fopen("php://output", "w");
            fputcsv($file, ["Date", "Student", "Examiner", "Attendance Status", "Grade Given"]);

            foreach ($events as $event) {
                foreach ($event->panelMembers as $member) {
                    $evaluation = $event->evaluations->where("evaluator_id", $member->user_id)->first();
                    $status = $evaluation ? "Present" : "Absent";
                    $grade = $evaluation ? ($evaluation->score["total"] ?? "N/A") : "N/A";
                    
                    fputcsv($file, [
                        $event->schedule_start ? $event->schedule_start->format("Y-m-d") : "N/A",
                        $event->thesis->student->user->name ?? "N/A",
                        $member->user->name ?? "N/A",
                        $status,
                        $grade
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

