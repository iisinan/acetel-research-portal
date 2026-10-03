<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ThesisController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\MilestoneReviewController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\ThesisProject;
use App\Models\StudentMilestone;
use App\Models\Program;
use App\Models\Cohort;
use App\Models\AuditLog;
use App\Models\Announcement;

// ── Public routes ──────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ── Health / keep-alive ping (prevents Render cold starts) ─────────
Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'ts' => now()->toISOString()]);
});

// ── Protected routes ───────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // ── Current user ────────────────────────────────────────────────
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load(['roles']);
        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->getRoleNames()->first(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Dashboard (cached per user, 10-min TTL) ─────────────────────
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        $role = $user->getRoleNames()->first();
        $key  = "dashboard_{$user->id}";

        return Cache::remember($key, 600, function () use ($user, $role) {
            if ($role === 'Student') {
                $student = StudentProfile::where('user_id', $user->id)
                    ->with(['thesis.milestones.template', 'thesis.assignments.supervisor.user', 'program', 'level', 'cohort'])
                    ->first();

                if (!$student) return ['role' => $role, 'student' => null, 'milestones' => [], 'stats' => []];

                $thesis     = $student->thesis;
                $milestones = $thesis ? $thesis->milestones->sortBy('template.order')->values() : collect();
                $total      = $milestones->count();
                $completed  = $milestones->where('status', 'approved')->count();

                return [
                    'role'       => $role,
                    'student'    => $student,
                    'thesis'     => $thesis,
                    'milestones' => $milestones->values(),
                    'stats' => [
                        'total_milestones'     => $total,
                        'completed_milestones' => $completed,
                        'pending_milestones'   => $milestones->whereIn('status', ['not_started','pending','revision_required'])->count(),
                        'overall_progress'     => $total > 0 ? round(($completed / $total) * 100) : 0,
                    ],
                ];
            }

            if ($role === 'Supervisor') {
                $supervisor = SupervisorProfile::where('user_id', $user->id)
                    ->with(['assignments.thesis.student.user','assignments.thesis.milestones'])
                    ->first();

                $students = collect();
                $pending  = collect();

                if ($supervisor) {
                    $students = $supervisor->assignments->map(function ($a) {
                        $s = $a->thesis->student ?? null;
                        if ($s) $s->overall_progress = $a->thesis->progress_percentage ?? 0;
                        return $s;
                    })->filter()->unique('id')->values();

                    $pending = StudentMilestone::whereHas('thesis.assignments', fn ($q) =>
                        $q->where('supervisor_profile_id', $supervisor->id)->where('status', 'active')
                    )->where('status', 'submitted')->whereNotNull('submitted_at')
                     ->with(['thesis.student.user','template'])->latest()->get();
                }

                return [
                    'role'            => $role,
                    'students'        => $students,
                    'pending_reviews' => $pending,
                    'stats' => [
                        'assigned_students' => $students->count(),
                        'pending_reviews'   => $pending->count(),
                    ],
                ];
            }

            if ($role === 'Program Coordinator') {
                $students = StudentProfile::forCoordinator($user)
                    ->with(['user','program','level','thesis'])->latest()->take(20)->get();
                return [
                    'role'     => $role,
                    'students' => $students,
                    'stats' => [
                        'total_students'    => StudentProfile::forCoordinator($user)->count(),
                        'total_supervisors' => SupervisorProfile::count(),
                        'active_theses'     => ThesisProject::where('status','active')->count(),
                        'pending_reviews'   => StudentMilestone::where('status','submitted')->count(),
                    ],
                ];
            }

            if (in_array($role, ['Admin','Director'])) {
                return [
                    'role'        => $role,
                    'stats' => [
                        'total_users'     => User::count(),
                        'total_theses'    => ThesisProject::count(),
                        'active_students' => StudentProfile::where('enrollment_status','active')->count(),
                        'program_count'   => Program::count(),
                        'student_count'   => User::role('Student')->count(),
                        'staff_count'     => User::role(['Admin','Director','Program Coordinator','Supervisor'])->count(),
                    ],
                    'projects'    => ThesisProject::with('student.user','student.program')->latest()->take(10)->get(),
                    'recent_logs' => AuditLog::with('user')->latest()->take(6)->get(),
                ];
            }

            return ['role' => $role, 'stats' => []];
        });
    });

    // ── Student thesis + milestones (cached 5 min) ──────────────────
    Route::get('/thesis', function (Request $request) {
        $user = $request->user();
        $key  = 'user_thesis_' . $user->id;
        $data = Cache::remember($key, 300, function () use ($user) {
            $student = $user->studentProfile;
            if (!$student) return null;
            return $student->load(['thesis.milestones.template','thesis.assignments.supervisor.user','program','level'])->toArray();
        });
        if (!$data) return response()->json(['message' => 'Profile not found'], 404);
        return response()->json($data);
    });

    Route::get('/milestones', function (Request $request) {
        $user    = $request->user();
        $student = StudentProfile::where('user_id', $user->id)->first();
        if (!$student || !$student->thesis) return response()->json(['milestones' => [], 'thesis' => null]);

        $key    = 'milestones_' . $student->id;
        $result = Cache::remember($key, 300, function () use ($student) {
            $thesis     = $student->thesis;
            $milestones = StudentMilestone::where('thesis_project_id', $thesis->id)
                ->with(['template','submissions'])->get()->sortBy('template.order')->values();
            return ['milestones' => $milestones, 'thesis' => $thesis];
        });
        return response()->json($result);
    });

    Route::post('/thesis', [ThesisController::class, 'store']);
    Route::post('/thesis/{thesis}/assign-supervisor', [ThesisController::class, 'assignSupervisor']);

    Route::post('/milestones/{milestone}/submit', function (Request $request, $milestoneId) {
        $user    = $request->user();
        $student = StudentProfile::where('user_id', $user->id)->first();

        // Invalidate student caches
        Cache::forget('dashboard_' . $user->id);
        Cache::forget('user_thesis_' . $user->id);
        if ($student) Cache::forget('milestones_' . $student->id);

        return app(MilestoneController::class)->store($request, \App\Models\StudentMilestone::findOrFail($milestoneId));
    });

    Route::post('/milestones/{milestone}/accept-upload', function (Request $request, $milestoneId) {
        $milestone = \App\Models\StudentMilestone::findOrFail($milestoneId);
        $submission = $milestone->submissions()->latest()->first();
        if ($submission) {
            \App\Models\Feedback::create([
                'submission_id' => $submission->id,
                'decision' => 'approved',
                'remarks' => 'Upload accepted by supervisor.',
                'created_by' => Auth::id(),
            ]);
        }
        Cache::flush();
        return response()->json(['success' => true]);
    });

    Route::post('/milestones/{milestone}/review', function (Request $request, $milestoneId) {
        // Invalidate all dashboard caches (reviewer could be anyone)
        Cache::flush(); // safe — file cache is cheap to rebuild
        return app(MilestoneReviewController::class)->update($request, \App\Models\StudentMilestone::findOrFail($milestoneId));
    });

    // ── Supervisor students (cached 5 min) ──────────────────────────
    Route::get('/supervisor/students', function (Request $request) {
        $user = $request->user();
        $key  = 'supervisor_students_' . $user->id;

        return response()->json(Cache::remember($key, 300, function () use ($user) {
            $supervisor = SupervisorProfile::where('user_id', $user->id)
                ->with(['assignments.thesis.student.user','assignments.thesis.milestones'])->first();

            if (!$supervisor) return ['students' => [], 'pending_reviews' => []];

            $students = $supervisor->assignments->map(function ($a) {
                $s = $a->thesis->student ?? null;
                if ($s) {
                    $s->overall_progress = $a->thesis->progress_percentage ?? 0;
                    $s->thesis_title     = $a->thesis->title;
                    $s->thesis_status    = $a->thesis->status;
                    $s->milestones_done  = $a->thesis->milestones->where('status','approved')->count();
                    $s->milestones_total = $a->thesis->milestones->count();
                }
                return $s;
            })->filter()->unique('id')->values();

            $pending = StudentMilestone::whereHas('thesis.assignments', fn ($q) =>
                $q->where('supervisor_profile_id', $supervisor->id)->where('status','active')
            )->whereIn('status',['submitted', 'partially_approved'])->whereNotNull('submitted_at')
             ->with(['thesis.student.user','template', 'submissions.feedback'])->latest()->get();

            return ['students' => $students, 'pending_reviews' => $pending];
        }));
    });

    // ── Coordinator routes (cached 5 min) ───────────────────────────
    Route::get('/coordinator/students', function (Request $request) {
        $user = $request->user();
        $key  = 'coord_students_' . $user->id;
        return response()->json(Cache::remember($key, 300, function () use ($user) {
            return StudentProfile::forCoordinator($user)
                ->with(['user','program','level','thesis.milestones'])
                ->latest()->paginate(30);
        }));
    });

    Route::get('/coordinator/dashboard-stats', function (Request $request) {
        $user = $request->user();
        $key  = 'coord_stats_' . $user->id;
        return response()->json(Cache::remember($key, 300, function () use ($user) {
            return [
                'stats' => [
                    'total_students'    => StudentProfile::forCoordinator($user)->count(),
                    'total_supervisors' => SupervisorProfile::count(),
                    'active_theses'     => ThesisProject::where('status','active')->count(),
                    'pending_reviews'   => StudentMilestone::where('status','submitted')->count(),
                ],
            ];
        }));
    });

    // ── Admin routes (cached 2 min) ──────────────────────────────────
    Route::get('/admin/users', function (Request $request) {
        $search = $request->get('search','');
        $role   = $request->get('role','');
        $page   = $request->get('page', 1);
        $key    = "admin_users_{$search}_{$role}_{$page}";

        return response()->json(Cache::remember($key, 120, function () use ($search, $role) {
            $q = User::with('roles')->latest();
            if ($search) $q->where(fn ($q2) => $q2->where('name','ilike',"%$search%")->orWhere('email','ilike',"%$search%"));
            if ($role)   $q->role($role);
            return $q->paginate(20);
        }));
    });

    Route::get('/admin/stats', function () {
        return response()->json(Cache::remember('admin_stats', 120, function () {
            return [
                'total_users'     => User::count(),
                'total_theses'    => ThesisProject::count(),
                'active_students' => StudentProfile::where('enrollment_status','active')->count(),
                'program_count'   => Program::count(),
                'student_count'   => User::role('Student')->count(),
                'staff_count'     => User::role(['Admin','Director','Program Coordinator','Supervisor'])->count(),
                'recent_logs'     => AuditLog::with('user')->latest()->take(8)->get(),
                'projects'        => ThesisProject::with('student.user','student.program')->latest()->take(10)->get(),
            ];
        }));
    });

    Route::get('/admin/theses', function (Request $request) {
        $search = $request->get('search','');
        $status = $request->get('status','');
        $page   = $request->get('page', 1);
        $key    = "admin_theses_{$search}_{$status}_{$page}";

        return response()->json(Cache::remember($key, 120, function () use ($search, $status) {
            $q = ThesisProject::with('student.user','student.program')->latest();
            if ($search) $q->where('title','ilike',"%$search%");
            if ($status) $q->where('status',$status);
            return $q->paginate(20);
        }));
    });

    Route::get('/admin/programs',   fn () => response()->json(Cache::remember('admin_programs',  300, fn () => Program::withCount(['students','coordinators'])->get())));
    Route::get('/admin/cohorts',    fn () => response()->json(Cache::remember('admin_cohorts',   300, fn () => Cohort::withCount('students')->orderBy('intake_year','desc')->get())));
    Route::get('/admin/audit-logs', fn () => response()->json(AuditLog::with('user')->latest()->paginate(30)));

    // ── Shared routes (cached) ─────────────────────────────────────
    Route::get('/announcements', function (Request $request) {
        $role = $request->user()->getRoleNames()->first();
        return response()->json(Cache::remember("announcements_{$role}", 300, fn () =>
            Announcement::active()->forRole($role)->latest()->take(5)->get()
        ));
    });

    Route::get('/repository', fn () => response()->json(Cache::remember('repository', 600, fn () =>
        ThesisProject::with('student.user','student.program')->where('status','completed')->latest()->get()
    )));

    Route::get('/notifications',       [NotificationController::class, 'index']);
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead']);
    Route::get('/inbox',               [InboxController::class,        'index']);
    Route::post('/messages',           [MessageController::class,       'store']);
    Route::post('/events',                        [EventController::class, 'schedule']);
    Route::post('/events/{event}/evaluate',       [EventController::class, 'evaluate']);
});

Route::post('/test-upload', function (Illuminate\Http\Request $request) {
    try {
        $path = $request->file('test')->store('presentations', 'public');
        return response()->json(['success' => true, 'path' => $path]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});
