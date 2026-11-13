<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentMilestone;
use App\Models\MilestoneTemplate;

class PastPresentationController extends Controller
{
    public function index()
    {
        $milestones = StudentMilestone::where('status', 'approved')
            ->whereNotNull('defence_date')
            ->whereHas('template', function($q) {
                $q->where('slug', 'seminar_as_a_course');
            })
            ->whereDoesntHave('thesis.student.program', fn($pq) => $pq->phdAi())
            ->with(['thesis.student.user', 'thesis.student.program', 'template', 'thesis.defenceEvents.evaluations'])
            ->orderBy('defence_date', 'desc')
            ->paginate(20);

        return view('admin.past-presentations.index', compact('milestones'));
    }

    public function exportScores()
    {
        $milestones = StudentMilestone::where('status', 'approved')
            ->whereNotNull('defence_date')
            ->whereHas('template', function($q) {
                $q->where('slug', 'seminar_as_a_course');
            })
            ->with(['thesis.student.user', 'thesis.student.program', 'thesis.student.level', 'template', 'thesis.defenceEvents.evaluations'])
            ->orderBy('defence_date', 'desc')
            ->get()
            ->reject(fn($sm) => $sm->thesis && $sm->template && $sm->thesis->isExemptFromMilestone($sm->template))
            ->values();

        $fileName = 'past_seminar_scores.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Student Name', 'Matric Number', 'Discipline Track', 'Seminar', 'Presentation Date', 'Average Score'];

        $callback = function() use($milestones, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($milestones as $sm) {
                $avgScore = 'N/A';
                $eventType = $sm->template->defence_type ?? ($sm->template->slug === 'seminar_as_a_course' ? 'seminar' : 'first_seminar');
                $event = $sm->thesis->defenceEvents->where('type', $eventType)->first() ?? $sm->thesis->defenceEvents->first();
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
                    $sm->thesis->student->user->name ?? 'Unknown',
                    $sm->thesis->student->student_id_number ?? 'N/A',
                    $sm->thesis->student->discipline_group_label ?? 'General',
                    $sm->template->name ?? 'N/A',
                    $sm->defence_date ? \Carbon\Carbon::parse($sm->defence_date)->format('Y-m-d') : 'N/A',
                    $avgScore
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAttendance()
    {
        $milestones = StudentMilestone::where('status', 'approved')
            ->whereNotNull('defence_date')
            ->whereHas('template', function($q) {
                $q->where('slug', 'seminar_as_a_course');
            })
            ->with(['thesis.defenceEvents.evaluations.evaluator'])
            ->get();

        $events = $milestones->pluck('thesis.defenceEvents')->flatten();

        $attendanceCount = [];
        foreach ($events as $event) {
            $evals = $event->evaluations;
            $uniqueEvaluators = $evals->pluck('evaluator_id')->unique();
            $dateKey = \Carbon\Carbon::parse($event->schedule_start)->format('Y-m-d');

            foreach ($uniqueEvaluators as $evalId) {
                if (!isset($attendanceCount[$evalId])) {
                    $attendanceCount[$evalId] = [];
                }
                $attendanceCount[$evalId][$dateKey] = true;
            }
        }

        $fileName = 'past_examiner_attendance.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['Examiner Name', 'Total Days Present'];

        $callback = function() use($attendanceCount, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $users = \App\Models\User::whereIn('id', array_keys($attendanceCount))->get()->keyBy('id');

            foreach ($attendanceCount as $evalId => $dates) {
                $daysPresent = count($dates);
                $name = $users->get($evalId)?->name ?? 'Unknown User';
                fputcsv($file, [$name, $daysPresent]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
