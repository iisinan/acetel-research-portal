<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MilestoneTemplate;
use App\Models\StudentProfile;
use App\Models\Program;
use App\Models\Cohort;
use App\Models\Level;
use App\Models\StudentMilestone;

class MilestoneController extends Controller
{
    /**
     * Display students categorized by their current active research milestone.
     */
    public function index(Request $request)
    {
        $templates = MilestoneTemplate::whereNull('program_id')
            ->orderBy('order', 'asc')
            ->get();

        if ($templates->isEmpty()) {
            $templates = MilestoneTemplate::orderBy('order', 'asc')->get();
        }

        // Global filter options
        $programs = Program::orderBy('name')->get();
        $cohorts = Cohort::orderBy('intake_year', 'desc')->get();
        $levels = Level::all();

        // 1. Calculate active student count for each milestone
        $milestoneCounts = [];
        foreach ($templates as $tpl) {
            $order = $tpl->order;
            $milestoneCounts[$tpl->id] = StudentProfile::where('enrollment_status', 'active')
                ->whereHas('thesis', function ($tQuery) use ($tpl, $order) {
                    $tQuery->whereHas('milestones', function ($mQuery) use ($tpl, $order) {
                        $mQuery->where(function ($sq) use ($tpl, $order) {
                            $sq->where('milestone_template_id', $tpl->id)
                               ->orWhereHas('template', fn($tq) => $tq->where('order', $order));
                        })->whereNotIn('status', ['completed', 'approved']);
                    })->whereDoesntHave('milestones', function ($mQuery) use ($order) {
                        $mQuery->whereNotIn('status', ['completed', 'approved'])
                               ->whereHas('template', fn($tplQuery) => $tplQuery->where('order', '<', $order));
                    });
                })->count();
        }

        // 2. Identify Selected Milestone
        $selectedMilestoneId = $request->query('milestone_id');
        $selectedMilestone = null;

        if ($selectedMilestoneId) {
            $selectedMilestone = $templates->firstWhere('id', $selectedMilestoneId);
        }

        // Fallback: pick first milestone with students, or first template
        if (!$selectedMilestone) {
            $firstWithStudents = $templates->first(fn($t) => ($milestoneCounts[$t->id] ?? 0) > 0);
            $selectedMilestone = $firstWithStudents ?: $templates->first();
        }

        // 3. Query Students for Selected Milestone
        $students = collect();
        $order = $selectedMilestone?->order ?? 1;

        if ($selectedMilestone) {
            $query = StudentProfile::query()
                ->where('enrollment_status', 'active')
                ->with([
                    'user',
                    'program',
                    'cohort',
                    'level',
                    'thesis.assignments.supervisor.user',
                    'thesis.internalExaminer.user',
                    'thesis.internalExaminer.program',
                    'thesis.externalExaminer.user',
                    'thesis.examinerAssignments.examiner',
                    'thesis.milestones' => function ($q) use ($selectedMilestone, $order) {
                        $q->where('milestone_template_id', $selectedMilestone->id)
                          ->orWhereHas('template', fn($t) => $t->where('order', $order));
                    },
                ]);

            // Filter students currently active at this milestone
            $query->whereHas('thesis', function ($tQuery) use ($selectedMilestone, $order) {
                $tQuery->whereHas('milestones', function ($mQuery) use ($selectedMilestone, $order) {
                    $mQuery->where(function ($sq) use ($selectedMilestone, $order) {
                        $sq->where('milestone_template_id', $selectedMilestone->id)
                           ->orWhereHas('template', fn($tq) => $tq->where('order', $order));
                    })->whereNotIn('status', ['completed', 'approved']);
                })->whereDoesntHave('milestones', function ($mQuery) use ($order) {
                    $mQuery->whereNotIn('status', ['completed', 'approved'])
                           ->whereHas('template', fn($tplQuery) => $tplQuery->where('order', '<', $order));
                });
            });

            // Filters
            if ($request->filled('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->filled('cohort_id')) {
                $query->where('cohort_id', $request->cohort_id);
            }

            if ($request->filled('level_id')) {
                $query->where('level_id', $request->level_id);
            }

            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('student_id_number', 'ilike', "%{$search}%")
                      ->orWhereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%"))
                      ->orWhereHas('thesis', fn($t) => $t->where('title', 'ilike', "%{$search}%"));
                });
            }

            $students = $query->paginate(20)->withQueryString();
        }

        // Format candidate dossiers for fast Alpine.js modal consumption
        $studentDossiers = [];
        if ($students instanceof \Illuminate\Pagination\LengthAwarePaginator || $students instanceof \Illuminate\Support\Collection) {
            foreach ($students as $student) {
                $thesis = $student->thesis;
                $activeMilestone = $thesis?->milestones->first();

                // Format supervisory team
                $supervisors = [];
                if ($thesis) {
                    foreach ($thesis->assignments as $assignment) {
                        $supProfile = $assignment->supervisor;
                        $supervisors[] = [
                            'name' => $supProfile?->user?->name ?? 'Unknown',
                            'email' => $supProfile?->user?->email ?? '',
                            'rank' => $supProfile?->rank ?? 'Supervisor',
                            'role' => $assignment->role === 'main' ? 'Primary Supervisor' : ($assignment->role ? ucwords(str_replace(['_', '-'], ' ', $assignment->role)) : 'Supervisor'),
                            'staff_id' => $supProfile?->staff_id ?? 'N/A',
                            'assigned_at' => $assignment->assigned_at?->format('M d, Y') ?? 'Active',
                        ];
                    }
                }

                // Format Internal Examiner
                $internalExaminer = null;
                if ($thesis) {
                    $intUser = $thesis->internalExaminer?->user 
                        ?? $thesis->examinerAssignments->firstWhere('type', 'internal_examiner')?->examiner;

                    if ($intUser) {
                        $internalExaminer = [
                            'name' => $intUser->name,
                            'email' => $intUser->email,
                            'program' => $thesis->internalExaminer?->program?->name ?? ($student->program?->name ?? 'Internal Faculty'),
                            'role' => 'Internal Examiner',
                        ];
                    }
                }

                // Format External Examiner
                $externalExaminer = null;
                if ($thesis) {
                    $extUser = $thesis->externalExaminer?->user 
                        ?? $thesis->examinerAssignments->firstWhere('type', 'program_examiner')?->examiner;

                    if ($extUser) {
                        $externalExaminer = [
                            'name' => $extUser->name,
                            'email' => $extUser->email,
                            'institution' => $thesis->externalExaminer?->institution 
                                ?? ($extUser->externalExaminerProfiles?->first()?->institution ?? 'External Institution'),
                            'expertise' => $thesis->externalExaminer?->expertise ?? 'External Appraisal',
                            'phone' => $thesis->externalExaminer?->phone ?? 'N/A',
                            'role' => 'External Examiner',
                        ];
                    }
                }

                $studentDossiers[$student->id] = [
                    'id' => $student->id,
                    'name' => $student->user?->name ?? 'N/A',
                    'matric' => $student->student_id_number,
                    'email' => $student->user?->email,
                    'phone' => $student->phone_number ?? 'N/A',
                    'program_name' => $student->program?->name ?? 'N/A',
                    'program_code' => $student->program?->code ?? '',
                    'level' => $student->level?->name ?? ($student->isPhdAI() ? 'PhD' : 'MSc'),
                    'cohort' => $student->cohort?->name ?? 'N/A',
                    'enrollment_status' => $student->enrollment_status,
                    'thesis_id' => $thesis?->id,
                    'thesis_title' => $thesis?->title ?? 'Research Proposal / Topic Pending',
                    'thesis_description' => $thesis?->description ?: 'No proposal abstract provided yet.',
                    'thesis_progress' => $thesis?->progress_percentage ?? 0,
                    'milestone_id' => $selectedMilestone?->id,
                    'milestone_name' => $selectedMilestone?->name ?? 'Milestone',
                    'milestone_status' => $activeMilestone?->status ?? 'not_started',
                    'milestone_defence_date' => $activeMilestone?->defence_date?->format('M d, Y'),
                    'milestone_defence_time' => $activeMilestone?->defence_time,
                    'milestone_defence_location' => $activeMilestone?->defence_location,
                    'meeting_link' => $activeMilestone?->meeting_link,
                    'supervisors' => $supervisors,
                    'internal_examiner' => $internalExaminer,
                    'external_examiner' => $externalExaminer,
                ];
            }
        }

        $totalActiveStudents = array_sum($milestoneCounts);

        return view('director.milestones.index', compact(
            'templates',
            'milestoneCounts',
            'selectedMilestone',
            'students',
            'studentDossiers',
            'programs',
            'cohorts',
            'levels',
            'totalActiveStudents'
        ));
    }
}
