<?php

namespace App\Http\Controllers;

use App\Models\ThesisProject;
use App\Models\StudentProfile;
use App\Models\Program;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $programs = collect();
        $cohorts = collect();

        if ($user->hasRole(['Director', 'Admin'])) {
            $programs = Program::all();
            $cohorts = Cohort::latest()->take(5)->get();
        } elseif ($user->hasRole('Program Coordinator')) {
            $scopes = $user->coordinatorScopes();
            $programs = Program::whereIn('id', $scopes->pluck('program_id'))->get();
            // Cohorts that have students in these programs
            $cohorts = Cohort::whereHas('students', function ($q) use ($programs) {
                $q->whereIn('program_id', $programs->pluck('id'));
            })->latest()->take(5)->get();
        }

        return view('reports.index', compact('programs', 'cohorts'));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'thesis_status');
        $format = $request->input('format', 'csv');
        $programId = $request->input('program_id');
        $cohortId = $request->input('cohort_id');
        $user = Auth::user();

        // Data Preparation based on type
        switch ($type) {
            case 'supervisor_workload':
                return $this->exportSupervisorWorkload($format, $programId);
            case 'milestone_velocity':
                return $this->exportMilestoneVelocity($format, $programId);
            default:
                return $this->exportThesisStatus($format, $programId, $cohortId, $user);
        }
    }

    private function exportThesisStatus($format, $programId, $cohortId, $user)
    {
        $query = ThesisProject::with(['student.user', 'student.program', 'student.cohort', 'assignments.supervisor.user']);

        if ($user->hasRole('Program Coordinator')) {
            $query->whereHas('student', function ($q) use ($user) {
                $q->forCoordinator($user);
            });
        }

        if ($programId) {
            $query->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }
        if ($cohortId) {
            $query->whereHas('student', function ($q) use ($cohortId) {
                $q->where('cohort_id', $cohortId);
            });
        }

        $projects = $query->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=thesis_progress_report_" . date('Y-m-d') . ".csv",
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student Name', 'ID Number', 'Program', 'Cohort', 'Thesis Title', 'Status', 'Supervisor(s)', 'Last Updated']);

            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->student->user->name,
                    $project->student->student_id_number,
                    $project->student->program->code,
                    $project->student->cohort->name ?? 'N/A',
                    $project->title,
                    ucfirst($project->status),
                    $project->assignments->map(fn($a) => $a->supervisor->user->name)->implode('; '),
                    $project->updated_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportSupervisorWorkload($format, $programId)
    {
        $supervisors = \App\Models\SupervisorProfile::with(['user', 'program'])
            ->withCount(['assignments' => fn($q) => $q->where('status', 'active')])
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=supervisor_workload_report_" . date('Y-m-d') . ".csv",
        ];

        $callback = function() use ($supervisors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Supervisor Name', 'Department', 'Active Students', 'Max Capacity', 'Load %']);

            foreach ($supervisors as $s) {
                $load = $s->max_students > 0 ? round(($s->assignments_count / $s->max_students) * 100) : 0;
                fputcsv($file, [
                    $s->user->name,
                    $s->program->name ?? 'N/A',
                    $s->assignments_count,
                    $s->max_students,
                    $load . '%'
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportMilestoneVelocity($format, $programId)
    {
        // Placeholder for velocity data
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=milestone_velocity_report_" . date('Y-m-d') . ".csv",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Milestone Name', 'Avg Days to Complete', 'Success Rate', 'Bottleneck Index']);
            fputcsv($file, ['Proposal Defense', '45', '82%', 'Medium']);
            fputcsv($file, ['Internal Defense', '120', '95%', 'Low']);
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
