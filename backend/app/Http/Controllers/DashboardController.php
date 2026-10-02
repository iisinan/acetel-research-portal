<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentProfile;
use App\Models\SupervisorProfile;

class DashboardController extends Controller
{
    protected $analytics;

    public function index(Request $request)
    {
        $user = Auth::user();
        $data = [];

        // Instantiate analytics safely
        try {
            $this->analytics = app(\App\Services\DirectorAnalyticsService::class);
        } catch (\Exception $e) {
            $this->analytics = null;
        }
        
        // Fetch global active announcements and document templates
        $data['announcements'] = \App\Models\Announcement::active()
            ->forRole($user->getRoleNames()->first()) 
            ->latest()
            ->get();

        $data['document_templates'] = \App\Models\DocumentTemplate::where('is_active', true)->latest()->get();

        if ($user->hasRole('Student')) {
            return $this->studentDashboard($user, $data);
        } elseif ($user->hasRole('Supervisor')) {
            return $this->supervisorDashboard($user, $data);
        } elseif ($user->hasRole('Program Coordinator')) {
            return (new \App\Http\Controllers\Coordinator\DashboardController())->index($request);
        } elseif ($user->hasRole('Internal Examiner') || $user->hasRole('External Examiner')) {
            return $this->examinerDashboard($user, $data);
        } elseif ($user->hasRole('Director')) {
            return $this->directorDashboard($user, $data);
        } elseif ($user->hasRole('Admin')) {
            return $this->adminDashboard($user, $data);
        }

        // Fallback for other roles or unassigned
        return view('dashboard', ['stats' => []]);
    }

    private function getUnreadMessagesCount($user)
    {
        // 1. Chat System Messages (from milestones)
        $chatUnread = \App\Models\MessageReadState::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // 2. Inbox System Messages (Direct Messages)
        $inboxUnread = \Illuminate\Support\Facades\DB::table('inbox_message_recipients')
            ->where('user_id', '=', $user->id)
            ->whereNull('read_at')
            ->where('is_archived', '=', false)
            ->count();

        return [
            'chat' => $chatUnread,
            'inbox' => $inboxUnread,
            'total' => $chatUnread + $inboxUnread
        ];
    }

    public function resources()
    {
        $document_templates = \App\Models\DocumentTemplate::where('is_active', true)->latest()->get();
        return view('resources.index', compact('document_templates'));
    }

    private function studentDashboard($user, $data = [])
    {
        $student = StudentProfile::where('user_id', $user->id)
            ->with(['thesis.milestones.template', 'thesis.assignments.supervisor.user', 'program', 'cohort', 'level'])
            ->first();
        
        $data['student'] = $student;
        $active_thesis = $student ? $student->thesis : null;
        $data['active_thesis'] = $active_thesis;
        $data['milestones'] = $active_thesis ? $active_thesis->milestones->sortBy('template.order') : collect();
        $data['supervisors'] = $active_thesis ? $active_thesis->assignments : collect();
        
        $total_milestones = $data['milestones']->count();
        $completed_milestones = $data['milestones']->where('status', 'approved')->count();
        
        $unreadCounts = $this->getUnreadMessagesCount($user);
        $data['stats'] = [
            'total_milestones' => $total_milestones,
            'completed_milestones' => $completed_milestones,
            'pending_milestones' => $data['milestones']->whereIn('status', ['not_started', 'revision_required', 'pending'])->count(),
            'unread_messages' => $unreadCounts['total'],
            'unread_chat' => $unreadCounts['chat'],
            'unread_inbox' => $unreadCounts['inbox'],
            'overall_progress' => $total_milestones > 0 ? round(($completed_milestones / $total_milestones) * 100) : 0,
        ];

        $data['action_items'] = $active_thesis ? \App\Models\ActionItem::where('thesis_project_id', $active_thesis->id)
            ->where('status', '!=', 'verified')
            ->orderBy('due_date', 'asc')
            ->get() : collect();

        $data['completed_thesis'] = \App\Models\ThesisProject::where('student_profile_id', $student->id)
            ->where('status', 'completed')
            ->first();

        return view('dashboard.student', $data);
    }

    private function supervisorDashboard($user, $data = [])
    {
        $supervisor = SupervisorProfile::where('user_id', $user->id)
            ->with(['assignments.thesis.student.user', 'assignments.thesis.student.program', 'assignments.thesis.milestones'])
            ->first();
        
        $data['supervisor'] = $supervisor;
        $data['assignments'] = $supervisor ? $supervisor->assignments : collect();
        
        // Prepare students collection with progress for the view
        $data['students'] = $data['assignments']->map(function($a) {
            $s = $a->thesis->student;
            if ($s) {
                $s->overall_progress = $a->thesis->progress_percentage;
            }
            return $s;
        })->filter()->unique('id');

        // Fetch milestones pending supervisor Review
        $data['pending_reviews'] = \App\Models\StudentMilestone::whereHas('thesis.assignments', function($q) use ($supervisor) {
                $q->where('supervisor_profile_id', $supervisor?->id)->where('status', 'active');
            })
            ->where('status', '!=', 'approved')
            ->whereHas('template', function($q) {
                $q->whereJsonContains('required_approvers', 'Supervisor');
            })
            ->where(function($q) use ($user) {
                $q->whereNull('approvals')
                  ->orWhereRaw("NOT EXISTS (
                      SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                      WHERE value->>'user_id' = ?
                  )", [$user->id]);
            })
            ->whereNotNull('submitted_at')
            ->with(['thesis.student.user', 'template'])
            ->latest()
            ->get();

        $data['pending_evaluations'] = collect();
        // Check if user has examiner profiles for defence evaluations
        $internal = \App\Models\InternalExaminerProfile::where('user_id', $user->id)->first();
        if ($internal) {
            $data['pending_evaluations'] = \App\Models\DefenceEvent::whereHas('panelMembers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->whereDoesntHave('evaluations', function($q) use ($user) {
                    $q->where('evaluator_id', $user->id);
                })->with('thesis.student.user')->get();
        }

        $unreadCounts = $this->getUnreadMessagesCount($user);
        $data['stats'] = [
            'assigned_students' => $data['students']->count(),
            'pending_reviews' => $data['pending_reviews']->count(),
            'total_theses' => $data['assignments']->unique('thesis_project_id')->count(),
            'unread_messages' => $unreadCounts['total'],
            'unread_chat' => $unreadCounts['chat'],
            'unread_inbox' => $unreadCounts['inbox'],
            'pending_evals' => $data['pending_evaluations']->count(),
        ];

        return view('dashboard.supervisor', $data);
    }

    private function examinerDashboard($user, $data = [])
    {
        $roleName = $user->hasRole('External Examiner') ? 'External Examiner' : 'Internal Examiner';
        
        if ($roleName === 'External Examiner') {
            $examiner = \App\Models\ExternalExaminerProfile::where('user_id', $user->id)->first();
            $data['theses'] = $examiner ? \App\Models\ThesisProject::where('external_examiner_profile_id', $examiner->id)->with('student.user')->get() : collect();
            $thesisColumn = 'external_examiner_profile_id';
        } else {
            $examiner = \App\Models\InternalExaminerProfile::where('user_id', $user->id)->first();
            $data['theses'] = $examiner ? \App\Models\ThesisProject::where('internal_examiner_profile_id', $examiner->id)->with('student.user')->get() : collect();
            $thesisColumn = 'internal_examiner_profile_id';
        }
        
        $data['examiner'] = $examiner;
        
        // Fetch milestones pending internal/external examiner Review
        $data['pending_reviews'] = \App\Models\StudentMilestone::whereHas('thesis', function($q) use ($examiner, $thesisColumn) {
                $q->where($thesisColumn, $examiner?->id);
            })
            ->where('status', '!=', 'approved')
            ->whereHas('template', function($q) use ($roleName) {
                $q->whereJsonContains('required_approvers', $roleName);
            })
            ->where(function($q) use ($user) {
                $q->whereNull('approvals')
                  ->orWhereRaw("NOT EXISTS (
                      SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                      WHERE value->>'user_id' = ?
                  )", [$user->id]);
            })
            ->whereNotNull('submitted_at')
            ->with(['thesis.student.user', 'template'])
            ->latest()
            ->get();

        // Fetch pending defence evaluations
        $data['pending_evaluations'] = \App\Models\DefenceEvent::whereHas('panelMembers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereDoesntHave('evaluations', function($q) use ($user) {
                $q->where('evaluator_id', $user->id);
            })->with('thesis.student.user')->get();

        $unreadCounts = $this->getUnreadMessagesCount($user);
        $data['stats'] = [
            'assigned_theses' => $data['theses']->count(),
            'pending_milestone_reviews' => $data['pending_reviews']->count(),
            'unread_messages' => $unreadCounts['total'],
            'unread_chat' => $unreadCounts['chat'],
            'unread_inbox' => $unreadCounts['inbox'],
            'pending_evaluations' => $data['pending_evaluations']->count(),
        ];

        return view('dashboard.examiner', $data);
    }

    private function coordinatorDashboard($user, $data = [])
    {
        $coordinatorProfile = $user->coordinatorProfiles()->where('active', true)->first();
        
        if ($coordinatorProfile) {
            $data['students'] = StudentProfile::forCoordinator($user)
                ->with('user', 'program', 'level', 'thesis')
                ->latest()
                ->take(20)
                ->get();

            // Fetch milestones pending coordinator Review
            $data['pending_reviews'] = \App\Models\StudentMilestone::whereHas('thesis.student', function($q) use ($user) {
                    $q->forCoordinator($user);
                })
                ->where('status', '!=', 'approved')
                ->whereHas('template', function($q) {
                    $q->whereJsonContains('required_approvers', 'Program Coordinator');
                })
                ->where(function($q) use ($user) {
                    $q->whereNull('approvals')
                      ->orWhereRaw("NOT EXISTS (
                          SELECT 1 FROM jsonb_each(COALESCE(approvals, '{}'::jsonb)) 
                          WHERE value->>'user_id' = ?
                      )", [$user->id]);
                })
                ->where(function($q) {
                    $q->whereNotNull('submitted_at')
                      ->orWhereHas('template', function($sq) {
                          $sq->where('requires_submission', false);
                      });
                })
                ->with(['thesis.student.user', 'template'])
                ->latest()
                ->get();

            $unreadCounts = $this->getUnreadMessagesCount($user);
            $data['stats'] = [
                'my_students' => StudentProfile::forCoordinator($user)->where('enrollment_status', 'active')->count(),
                'active_supervisors' => SupervisorProfile::whereHas('programs', function($q) use ($coordinatorProfile) {
                    $q->where('programs.id', $coordinatorProfile->program_id);
                })->count(),
                'active_theses' => \App\Models\ThesisProject::whereHas('student', function ($query) use ($user) {
                    $query->forCoordinator($user);
                })->where('status', 'active')->count(),
                'pending_milestone_reviews' => $data['pending_reviews']->count(),
                'unread_messages' => $unreadCounts['total'],
                'unread_chat' => $unreadCounts['chat'],
                'unread_inbox' => $unreadCounts['inbox'],
            ];
            
            $data['program_name'] = $coordinatorProfile->program->code . ($coordinatorProfile->level ? ' (' . $coordinatorProfile->level->name . ')' : '');
        } else {
             $data['stats'] = ['error' => 'No Program Assigned'];
        }

        return view('dashboard.coordinator', $data);
    }

    private function directorDashboard($user, $data = [])
    {
        $request = request();
        $filters = [
            'program_id' => $request->get('program_id'),
            'level_id'   => $request->get('level_id'),
            'cohort_id'  => $request->get('cohort_id'),
            'year'       => $request->get('year'),
            'search_student' => $request->get('search_student'),
        ];

        $unreadCounts = $this->getUnreadMessagesCount($user);
        $data['stats'] = $this->analytics->getInstitutionalMetrics($filters);
        $data['stats']['unread_messages'] = $unreadCounts['total'];
        $data['stats']['unread_chat'] = $unreadCounts['chat'];
        $data['stats']['unread_inbox'] = $unreadCounts['inbox'];

        $data['programs_performance'] = $this->analytics->getProgramPerformance($filters);
        $data['milestone_pipeline'] = $this->analytics->getMilestonePipeline($filters);
        $data['upcoming_defences'] = $this->analytics->getUpcomingDefences($filters);
        $data['delayed_students'] = $this->analytics->getDelayedStudents($filters)->take(5)->get();
        $data['supervisor_workload'] = $this->analytics->getSupervisorWorkload($filters);
        $data['examiner_workload'] = $this->analytics->getExaminerWorkload($filters);
        $data['plagiarism_alerts'] = $this->analytics->getPlagiarismAlerts($filters);
        $data['comm_health'] = $this->analytics->getCommunicationHealth($filters);
        $data['cohort_monitoring'] = $this->analytics->getCohortMonitoring($filters);
        $data['recent_logs'] = $this->analytics->getSystemActivity();
        $data['stalled_students'] = $this->analytics->getStalledStudents($filters)->take(5)->get();
        $data['faculty_leaderboard'] = $this->analytics->getFacultyLeaderboard($filters);
        
        // Granular Student Registry
        $data['students'] = $this->analytics->getStudentStatusList($filters);

        // Specific requirements for Section 10: Reports data visibility
        $data['available_programs'] = \App\Models\Program::all();
        $data['available_cohorts'] = \App\Models\Cohort::orderBy('intake_year', 'desc')->get();

        return view('dashboard.director', $data);
    }

    private function adminDashboard($user, $data = [], $view = 'admin.dashboard')
    {
        // 1. Core Data
        $data['recent_logs'] = \App\Models\AuditLog::with('user')->latest()->take(6)->get();
        
        $data['projects'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_projects', 300, function() {
            return \App\Models\ThesisProject::with('student.user', 'student.program')->latest()->take(10)->get();
        });
        
        $data['programs'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_programs', 3600, function() {
            return \App\Models\Program::all();
        });
        
        $data['project_count'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_project_count', 300, function() {
            return \App\Models\ThesisProject::count();
        });
        
        $data['ready_for_defense_projects'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_ready_defense', 300, function() {
            return \App\Models\ThesisProject::with(['student.user', 'student.program', 'student.level'])
                ->whereNotNull('cleared_for_internal_at')
                ->latest('cleared_for_internal_at')
                ->take(5)
                ->get();
        });
            
        // M9 Alerts (Students who just reached or submitted Milestone 9)
        $data['m9Alerts'] = \App\Models\StudentMilestone::with(['thesis.student.user', 'template'])
            ->whereHas('template', function ($q) {
                $q->where('order', 9)->orWhere('is_final_archival', true);
            })
            ->whereIn('status', ['submitted', 'in_progress', 'revision_required'])
            ->orderByDesc('updated_at')
            ->get();
         
        $unreadCounts = $this->getUnreadMessagesCount($user);
         
        // 2. Statistics
        $data['stats'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_stats', 300, function() use ($data) {
            return [
                'total_users' => \App\Models\User::count(),
                'total_theses' => $data['project_count'],
                'active_students' => \App\Models\StudentProfile::where('enrollment_status', 'active')->count(),
                'cleared_theses' => \App\Models\ThesisProject::whereNotNull('cleared_for_internal_at')->count(),
                'active_users_24h' => \App\Models\User::where('last_login_at', '>=', now()->subDay())->count(),
                'student_count' => \App\Models\User::role('Student')->count(),
                'program_count' => \App\Models\Program::count(),
                'staff_count' => \App\Models\User::role(['Director', 'Admin', 'Program Coordinator', 'Supervisor'])->count(),
                'failed_jobs' => \Illuminate\Support\Facades\DB::table('failed_jobs')->count(),
                'pending_jobs' => \Illuminate\Support\Facades\DB::table('jobs')->count(),
            ];
        });
        
        $data['stats']['unread_messages'] = $unreadCounts['total'];
        $data['stats']['unread_chat'] = $unreadCounts['chat'];
        $data['stats']['unread_inbox'] = $unreadCounts['inbox'];

        // 3. Activity Intelligence
        $data['recentLogins'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_recent_logins', 60, function() {
            return \App\Models\LoginActivity::with('user')
                ->latest('login_at')
                ->take(15)
                ->get();
        });

        $data['usersWithLastLogin'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_users_with_logins', 300, function() {
            return \App\Models\User::select('users.*')
                ->with('roles')
                ->leftJoin('login_activities', function ($join) {
                    $join->on('users.id', '=', 'login_activities.user_id')
                        ->whereRaw('login_activities.login_at = (SELECT MAX(la2.login_at) FROM login_activities la2 WHERE la2.user_id = users.id)');
                })
                ->addSelect([
                    'last_session_ip' => \App\Models\LoginActivity::select('ip_address')
                        ->whereColumn('user_id', 'users.id')
                        ->latest('login_at')
                        ->take(1),
                    'last_session_browser' => \App\Models\LoginActivity::select('browser')
                        ->whereColumn('user_id', 'users.id')
                        ->latest('login_at')
                        ->take(1),
                    'total_logins' => \App\Models\LoginActivity::selectRaw('COUNT(*)')
                        ->whereColumn('user_id', 'users.id'),
                ])
                ->orderByDesc('last_login_at')
                ->take(10)
                ->get();
        });

        $data['activityStats'] = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_activity_stats', 60, function() {
            return [
                'logins_today' => \App\Models\LoginActivity::whereDate('login_at', today())->count(),
                'logins_this_week' => \App\Models\LoginActivity::where('login_at', '>=', now()->startOfWeek())->count(),
                'unique_users_today' => \App\Models\LoginActivity::whereDate('login_at', today())->distinct('user_id')->count('user_id'),
                'active_sessions' => \App\Models\LoginActivity::whereNull('logout_at')
                    ->where('login_at', '>=', now()->subHours(24))->count(),
            ];
        });
        
        return view($view, $data);
    }
}
