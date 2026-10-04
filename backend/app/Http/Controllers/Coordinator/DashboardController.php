<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;
use App\Models\StudentMilestone;
use App\Models\DefenceEvent;
use App\Models\ThesisProject;
use App\Models\MilestoneTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $scopes = $user->coordinatorScopes();

        if ($scopes->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No active coordinator profile found.');
        }

        $allProgramIds = $scopes->pluck('program_id')->unique()->toArray();
        $allLevelIds = $scopes->pluck('level_id')->unique()->toArray();

        $programs = \App\Models\Program::whereIn('id', $allProgramIds)->get();
        $levels = \App\Models\Level::whereIn('id', $allLevelIds)->get();

        // Active Context Filters
        $programId = $request->get('program_id');
        $levelId = $request->get('level_id');
        
        // Ensure filters are within scope
        if ($programId && !in_array($programId, $allProgramIds)) $programId = null;
        if ($levelId && !in_array($levelId, $allLevelIds)) $levelId = null;

        // Base Query Scopes
        $studentQuery = StudentProfile::forCoordinator($user)->where('enrollment_status', 'active');
        $supervisorQuery = SupervisorProfile::query();
        $thesisQuery = ThesisProject::whereHas('student', function($q) use ($user) { $q->forCoordinator($user); });

        if ($programId) {
            $studentQuery->where('program_id', $programId);
            $supervisorQuery->whereHas('programs', function($q) use ($programId) { $q->where('programs.id', $programId); });
            $thesisQuery->whereHas('student', function($q) use ($programId) { $q->where('program_id', $programId); });
        } else {
            $supervisorQuery->whereHas('programs', function($q) use ($allProgramIds) { $q->whereIn('programs.id', $allProgramIds); });
        }

        if ($levelId) {
            $studentQuery->where('level_id', $levelId);
            $thesisQuery->whereHas('student', function($q) use ($levelId) { $q->where('level_id', $levelId); });
        }

        // Stats Calculation
        $stats = [
            'total_students' => $studentQuery->count(),
            'total_supervisors' => $supervisorQuery->count(),
            'active_theses' => $thesisQuery->whereIn('status', ['active', 'proposed'])->count(),
        ];

        // Clearance Metrics (Filtered)
        $totalStudents = $stats['total_students'] ?: 1;
        $clearanceMetrics = [
            'm1' => (StudentMilestone::whereHas('thesis.student', function($q) use ($user, $programId, $levelId) {
                    $q->forCoordinator($user);
                    if ($programId) $q->where('program_id', $programId);
                    if ($levelId) $q->where('level_id', $levelId);
                })
                ->whereHas('template', function($q) { $q->where('order', 1); })
                ->where('status', 'approved')->count() / $totalStudents) * 100,
            
            'm2' => (StudentMilestone::whereHas('thesis.student', function($q) use ($user, $programId, $levelId) {
                    $q->forCoordinator($user);
                    if ($programId) $q->where('program_id', $programId);
                    if ($levelId) $q->where('level_id', $levelId);
                })
                ->whereHas('template', function($q) { $q->where('order', 2); })
                ->where('status', 'approved')->count() / $totalStudents) * 100,
            
            'm6' => (StudentMilestone::whereHas('thesis.student', function($q) use ($user, $programId, $levelId) {
                    $q->forCoordinator($user);
                    if ($programId) $q->where('program_id', $programId);
                    if ($levelId) $q->where('level_id', $levelId);
                })
                ->whereHas('template', function($q) { $q->where('order', 6); })
                ->where('status', 'approved')->count() / $totalStudents) * 100,
        ];

        $students = $studentQuery->with(['user', 'program', 'level', 'thesis.milestones.template', 'thesis.assignments.supervisor.user'])->get();

        $pending_reviews = StudentMilestone::whereHas('thesis.student', function($q) use ($user, $programId, $levelId) {
                $q->forCoordinator($user);
                if ($programId) $q->where('program_id', $programId);
                if ($levelId) $q->where('level_id', $levelId);
            })
            ->where(function($q) {
                $q->whereIn('status', ['submitted', 'partially_approved', 'revision_required'])
                  ->orWhere(function($sub) {
                      $sub->where('status', 'pending')
                          ->whereHas('template', function($t) {
                              $t->where('requires_submission', false);
                          });
                  });
            })
            ->with(['template', 'thesis.student.user'])
            ->latest('updated_at')
            ->get();

        $upcoming_events = DefenceEvent::whereHas('thesis.student', function($q) use ($user, $programId, $levelId) {
                $q->forCoordinator($user);
                if ($programId) $q->where('program_id', $programId);
                if ($levelId) $q->where('level_id', $levelId);
            })
            ->where('schedule_start', '>=', now())
            ->orderBy('schedule_start')
            ->take(5)
            ->get();

        // System Alerts: Filter to items specifically requiring Coordinator attention
        $milestoneAlerts = $pending_reviews->filter(function($m) use ($user) {
            $requiredRoles = $m->template->required_approvers ?? [];
            return in_array('Program Coordinator', $requiredRoles);
        });

        $inactiveSupervisorsQuery = SupervisorProfile::whereHas('programs', function($q) use ($allProgramIds, $programId) {
            if ($programId) {
                $q->where('programs.id', $programId);
            } else {
                $q->whereIn('programs.id', $allProgramIds);
            }
        })->whereDoesntHave('assignments', function($q) {
            $q->where('status', 'active');
        })->with('user')->take(5)->get();

        return view('coordinator.dashboard', [
            'user' => $user,
            'students' => $students,
            'pending_reviews' => $pending_reviews,
            'upcoming_events' => $upcoming_events,
            'totalStudents' => $stats['total_students'],
            'totalSupervisors' => $stats['total_supervisors'],
            'activeTheses' => $stats['active_theses'],
            'clearanceMetrics' => $clearanceMetrics,
            'programs' => $programs,
            'levels' => $levels,
            'unreadMessages' => \App\Models\MessageReadState::where('user_id', $user->id)->whereNull('read_at')->count(),
            'milestoneAlerts' => $milestoneAlerts,
            'inactiveSupervisors' => $inactiveSupervisorsQuery,
        ]);
    }
}
