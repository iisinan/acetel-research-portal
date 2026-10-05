<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\SupervisionAssignment;
use App\Models\MilestoneTemplate;
use App\Models\Program;
use App\Models\Cohort;
use App\Models\InboxMessage;
use App\Services\ThesisService;

class AssignedSupervisorController extends Controller
{
    /**
     * Display a listing of students on the 'supervisors_assigned' milestone.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $query = StudentProfile::forCoordinator($user)
            ->whereHas('thesis', function ($tQuery) {
                $tQuery->whereHas('milestones', function ($mQuery) {
                    $mQuery->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
                           ->whereNotIn('status', ['completed', 'approved']);
                })->whereDoesntHave('milestones', function ($mQuery) {
                    $mQuery->whereNotIn('status', ['completed', 'approved'])
                           ->whereHas('template', fn($t) => $t->where('order', '<', 2));
                });
            })
            ->with([
                'user',
                'program',
                'level',
                'cohort',
                'thesis.assignments.supervisor.user',
                'thesis.milestones' => function ($q) {
                    $q->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
                      ->with(['submissions', 'template']);
                }
            ]);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id_number', 'ilike', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%"))
                  ->orWhereHas('thesis', fn($t) => $t->where('title', 'ilike', "%{$search}%"));
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('cohort_id')) {
            $query->where('cohort_id', $request->cohort_id);
        }

        if ($request->filled('batch')) {
            $batch = trim($request->batch);
            $query->where(function($q) use ($batch) {
                $q->where('student_id_number', 'like', "_____{$batch}%")
                  ->orWhereHas('cohort', function($c) use ($batch) {
                      $c->where('name', 'ilike', "%Batch {$batch}%")
                        ->orWhere('name', 'ilike', "%Batch-{$batch}%")
                        ->orWhere('code', 'ilike', "%-B{$batch}%");
                  });
            });
        }

        // Proposal upload status filter
        if ($request->filled('proposal_status')) {
            $status = $request->proposal_status;
            if ($status === 'uploaded') {
                $query->whereHas('thesis.milestones', function ($m) {
                    $m->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
                      ->whereHas('submissions');
                });
            } elseif ($status === 'awaiting') {
                $query->whereHas('thesis.milestones', function ($m) {
                    $m->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
                      ->whereDoesntHave('submissions');
                });
            }
        }

        // Assignment status filter
        if ($request->filled('assignment_status')) {
            $status = $request->assignment_status;
            if ($status === 'assigned') {
                $query->whereHas('thesis.assignments', fn($a) => $a->where('status', 'active'));
            } elseif ($status === 'pending') {
                $query->whereDoesntHave('thesis.assignments', fn($a) => $a->where('status', 'active'));
            }
        }

        // Statistics
        $statsBase = StudentProfile::forCoordinator($user)
            ->whereHas('thesis', function ($tQuery) {
                $tQuery->whereHas('milestones', function ($mQuery) {
                    $mQuery->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
                           ->whereNotIn('status', ['completed', 'approved']);
                })->whereDoesntHave('milestones', function ($mQuery) {
                    $mQuery->whereNotIn('status', ['completed', 'approved'])
                           ->whereHas('template', fn($t) => $t->where('order', '<', 2));
                });
            });

        $totalCount = (clone $statsBase)->count();

        $uploadedCount = (clone $statsBase)->whereHas('thesis.milestones', function ($m) {
            $m->whereHas('template', fn($t) => $t->where('slug', 'supervisors_assigned')->orWhere('order', 2))
              ->whereHas('submissions');
        })->count();

        $awaitingCount = max(0, $totalCount - $uploadedCount);

        $assignedCount = (clone $statsBase)->whereHas('thesis.assignments', function ($a) {
            $a->where('status', 'active');
        })->count();

        $students = $query->paginate(15)->withQueryString();

        $userScopes = $user->coordinatorScopes();
        $programIds = $userScopes ? $userScopes->pluck('program_id')->filter()->unique()->toArray() : [];
        $programs = !empty($programIds) ? Program::whereIn('id', $programIds)->get() : Program::all();
        $cohorts = Cohort::orderBy('intake_year', 'desc')->orderBy('name', 'asc')->get();

        // Supervisors available
        $supervisors = SupervisorProfile::with(['user', 'programs'])
            ->withCount(['assignments' => fn($q) => $q->where('status', 'active')])
            ->get();

        $supervisorsData = $supervisors->map(function ($s) {
            $name = $s->user?->name ?? 'Supervisor';
            $rank = $s->rank ?? '';
            $isProf = (stripos($rank, 'prof') !== false) || (stripos($name, 'prof') !== false);
            $currentLoad = (int) ($s->assignments_count ?? $s->current_load ?? 0);
            $maxStudents = (int) ($s->max_students ?? 5);
            $deptName = $s->programs->first()?->name ?? ($s->specialization ?: 'Academic Staff');

            return [
                'id' => $s->id,
                'name' => $name,
                'email' => $s->user?->email ?? '',
                'rank' => $rank ?: ($isProf ? 'Professor' : 'Lecturer'),
                'is_professor' => $isProf,
                'current_load' => $currentLoad,
                'max_students' => $maxStudents,
                'department' => $deptName,
                'program_ids' => $s->programs->pluck('id')->toArray(),
            ];
        })->values();

        return view('coordinator.assigned-supervisors.index', compact(
            'students',
            'programs',
            'cohorts',
            'supervisorsData',
            'totalCount',
            'uploadedCount',
            'awaitingCount',
            'assignedCount'
        ));
    }

    /**
     * Authorize and save supervisor assignments for a student.
     */
    public function assign(Request $request, StudentProfile $student)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        if (!$user->hasCoordinatorAccess($student)) {
            abort(403, 'Unauthorized access to student.');
        }

        $thesis = $student->thesis;
        if (!$thesis) {
            return back()->with('error', 'Student does not have an active thesis project.');
        }

        if ($student->isSeminarCourseLevel()) {
            return back()->with('error', 'Students at the seminar course level do not require supervisors.');
        }

        $isPhD = str_contains(strtolower($student->level->name ?? ''), 'phd');
        $requiredCount = $isPhD ? 3 : 2;

        $request->validate([
            'supervisors' => 'required|array|min:' . $requiredCount . '|max:' . $requiredCount,
            'supervisors.*' => 'required|exists:supervisor_profiles,id',
        ]);

        $ids = $request->input('supervisors');

        // Check for duplicates
        if (count($ids) !== count(array_unique($ids))) {
            return back()->with('error', 'A supervisor cannot be assigned more than once to the same student.');
        }

        // Validate that first supervisor is a Professor
        $leadSupervisor = SupervisorProfile::with('user')->findOrFail($ids[0]);
        $isProf = (stripos($leadSupervisor->rank ?? '', 'prof') !== false) || 
                  ($leadSupervisor->user && stripos($leadSupervisor->user->name, 'prof') !== false);

        if (!$isProf) {
            return back()->with('error', "Academic Hierarchy Violation: The Main Supervisor ({$leadSupervisor->user->name}) must hold the rank of Professor.");
        }

        try {
            app(ThesisService::class)->replaceSupervisors($thesis, $ids);

            // Optional: send notifications to assigned supervisors & student
            $this->notifyParties($student, $ids);

            return back()->with('success', "Supervision committee successfully assigned for {$student->user->name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Helper to send notification messages upon assignment.
     */
    private function notifyParties(StudentProfile $student, array $supervisorIds): void
    {
        try {
            $coordinatorId = Auth::id();
            $studentUser = $student->user;
            $supervisors = SupervisorProfile::with('user')->whereIn('id', $supervisorIds)->get();

            $namesList = $supervisors->map(fn($s, $idx) => ($idx === 0 ? "• Main Supervisor: " : "• Co-Supervisor: ") . $s->user?->name)->implode("\n");

            if ($studentUser) {
                $body = "Dear {$studentUser->name},\n\nYour supervisory committee has been authorized by your Program Coordinator:\n\n{$namesList}\n\nPlease check your portal to view further details.\n\nBest Regards,\nProgram Coordinator";
                $msg = InboxMessage::create([
                    'sender_id' => $coordinatorId,
                    'subject' => 'Supervisory Committee Assigned',
                    'body' => $body,
                ]);
                $msg->recipients()->attach($studentUser->id, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'recipient_type' => 'to'
                ]);
            }
        } catch (\Throwable $e) {
            // Non-critical notification failure ignored
        }
    }
}
