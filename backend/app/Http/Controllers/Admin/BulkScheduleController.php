<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DefenceScheduled;
use App\Models\Cohort;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BulkScheduleController extends Controller
{
    public function index()
    {
        // Load all cohorts with their students, programs, and thesis for the server-rendered form
        $cohorts = Cohort::orderByDesc('intake_year')
            ->with([
                'students.user',
                'students.program',
                'students.thesis',
            ])
            ->get();

        return view('admin.bulk-schedule.index', compact('cohorts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'defence_type' => 'required|in:internal,external,proposal,seminar,progress_report_1,progress_report_2',
            'defence_date' => 'required|date',
            'student_ids'  => 'required|array|min:1',
            'student_ids.*' => 'exists:student_profiles,id',
        ]);

        $students = StudentProfile::whereIn('id', $request->student_ids)
            ->with('user', 'thesis.milestones.template')
            ->get();

        $updatedCount  = 0;
        $notifiedUsers = collect(); // track unique users already notified

        foreach ($students as $student) {
            if (!$student->thesis) continue;

            // Find the milestone(s) matching the chosen defence type
            $milestonesToUpdate = $student->thesis->milestones->filter(function ($m) use ($request) {
                // Direct match via database field
                if ($m->template->defence_type === $request->defence_type) {
                    return true;
                }

                // Fallback: keyword match on name for legacy templates
                if (!$m->template->defence_type) {
                    $lowerName = strtolower($m->template->name ?? '');
                    if ($request->defence_type === 'proposal' && str_contains($lowerName, 'proposal'))  return true;
                    if ($request->defence_type === 'internal' && (str_contains($lowerName, 'internal') || $m->template->order == 9)) return true;
                    if ($request->defence_type === 'external' && (str_contains($lowerName, 'external') || $m->template->is_final_archival)) return true;
                    if ($request->defence_type === 'seminar' && str_contains($lowerName, 'seminar')) return true;
                    if ($request->defence_type === 'progress_report_1' && str_contains($lowerName, 'progress report 1')) return true;
                    if ($request->defence_type === 'progress_report_2' && str_contains($lowerName, 'progress report 2')) return true;
                }

                return false;
            });

            foreach ($milestonesToUpdate as $milestone) {
                $milestone->defence_date = $request->defence_date;
                $milestone->save();
                $updatedCount++;
            }

            // Queue one email per unique student (even if multiple milestones matched)
            if ($milestonesToUpdate->isNotEmpty() && $student->user && !$notifiedUsers->contains($student->user->id)) {
                $notifiedUsers->push($student->user->id);

                try {
                    Mail::to($student->user->email)
                        ->queue(new DefenceScheduled($student->user, $request->defence_type, $request->defence_date));
                } catch (\Exception $e) {
                    Log::error("DefenceScheduled email failed for {$student->user->email}: " . $e->getMessage());
                }
            }
        }

        $typeName = match($request->defence_type) {
            'seminar' => 'Seminar',
            'proposal' => 'Proposal',
            'progress_report_1' => 'Progress Report 1',
            'progress_report_2' => 'Progress Report 2',
            'internal' => 'Internal',
            'external' => 'External',
            default    => ucfirst($request->defence_type),
        };

        return redirect()
            ->route('admin.bulk-schedule.index')
            ->with('success', "✅ {$typeName} Defence scheduled for {$updatedCount} milestone(s) across {$notifiedUsers->count()} student(s). Email notifications have been queued.");
    }
}
